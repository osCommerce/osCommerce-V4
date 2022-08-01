<?php
/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace frontend\controllers;

use backend\services\OrdersService;
use common\models\Orders;
use common\models\OrdersStatusHistory;
use common\modules\orderPayment\multisafepay;
use frontend\design\Info;
use frontend\services\OrderManageService;
use Yii;
use common\helpers\Translation;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;

class CallbackController extends Sceleton {

    public $enableCsrfValidation = false;

    /**
     * @var OrderManageService
     */
    private $orderManageService;

    /** @var \common\services\OrderManager */
    private $manager;

    public function __construct($id, $module, OrderManageService $service, $config = []) {

        parent::__construct($id, $module, $config);
        $this->orderManageService = $service;
        $this->manager = new \common\services\OrderManager(Yii::$app->get('storage'));
        if (!Yii::$app->user->isGuest && !$this->manager->isCustomerAssigned()) {
            $this->manager->assignCustomer(Yii::$app->user->getId());
        }
    }
/**/
    public static function allowedDomains()
    {
        $ret = [];
        $pls = \common\classes\platform::getList(false);

        if (is_array($pls)) {
            foreach ($pls as $pl) {
                $pc = new \common\classes\platform_config($pl['id']);
                $parsed = parse_url($pc->getCatalogBaseUrl(true, false));
                //$ret[] = $parsed['scheme'] . '://' . $parsed['host'] . (!empty($parsed['port']) && ! in_array($parsed['port'], ['80', '443'])?':'.$parsed['port']:'');
                $ret[] = 'http://' . $parsed['host'] . (!empty($parsed['port']) && ! in_array($parsed['port'], ['80', '443'])?':'.$parsed['port']:'');
                $ret[] = 'https://' . $parsed['host'] . (!empty($parsed['port']) && ! in_array($parsed['port'], ['80', '443'])?':'.$parsed['port']:'');
            }
        }
        return $ret;
    }


    public function behaviors()
    {
        $ret = array_merge(parent::behaviors(), [

            // For cross-domain AJAX request
            'corsFilter'  => [
                'class' => \yii\filters\Cors::className(),
                'cors'  => [
                    // restrict access to domains:
                    'Origin'                           => static::allowedDomains(),
                    //'Access-Control-Allow-Origin' => static::allowedDomains(),
                    'Access-Control-Request-Method'    => ['POST', 'GET', 'OPTIONS'],
                    //'Access-Control-Request-Headers'    => ['X-Wsse'],
                    //'Access-Control-Request-Headers'    => ['*'],
                    'Access-Control-Allow-Credentials' => false,
                    'Access-Control-Max-Age'           => 10,                 // Cache (seconds)
                ],
            ],

        ]);

        return $ret;
    }
/* */

    public function actionPaypalNotify() { // for paypalipn
        
        $currencies = \Yii::$container->get('currencies');

        $req = 'cmd=_notify-validate';
        
        foreach ($_POST as $key => $value) {
            $req .= '&' . $key . '=' . urlencode(stripslashes($value));
            $$key = $value;
        }

        if (isset($_POST['item_number'])) {
            $item_number = $_POST['item_number'];
            if (strpos($_POST['item_number'], 'recurr_') !== false) {
                $is_subscription = true;
                $item_number = str_replace('recurr_', '', $_POST['item_number']);
            }
            elseif (strpos($_POST['item_number'], 'pay_') !== false) {
                $is_pay = true;
                $item_number = str_replace('pay_', '', $_POST['item_number']);
            }
        } else {
            for ($i = 1; $i <= 12; $i ++) {
                if (isset($_POST['item_number' . $i])) {
                    $item_number = $_POST['item_number' . $i];
                    if (strpos($_POST['item_number' . $i], 'recurr_') !== false) {
                        $is_subscription = true;
                        $item_number = str_replace('recurr_', '', $_POST['item_number' . $i]);
                    }
                    break;
                }
            }
        }

        $status_check_query = tep_db_query("select * from " . TABLE_ORDERS . " where orders_id='" . trim($item_number) . "'");
        if (tep_db_num_rows($status_check_query) == 0) {
            die();
        }
        
        $payment = 'paypalipn';
        $payment_modules = $this->manager->getPaymentCollection($payment);
        $paypalipn = $payment_modules->get($payment, true);
        /** @var \common\classes\Order $order */
        $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $item_number);
        
        $order_total_modules = $this->manager->getTotalCollection();
        
        $order_totals = $order->totals;
        $paid_key = - 1;
        if (is_array($order->totals)) {
            foreach ($order->totals as $key => $total) {
                $order_totals[$key]['sort_order'] = $order_total_modules->get($total['class'])->sort_order;
                if ($total['class'] == 'ot_paid') {
                    $paid_key = $key;
                }
                if ($total['class'] == 'ot_due') {
                    $order->info['total'] = $total['value_inc_tax'];

                    if ($paid_key != - 1) {
                        $order->info['total_inc_tax'] = $order_totals[$paid_key]['value_inc_tax'] + $total['value_inc_tax'];
                        $order->info['total_exc_tax'] = $order_totals[$paid_key]['value_exc_vat'] + $total['value_exc_vat'];
                    }
                    break;
                }
            }
        }

        $response_verified = '';
        $paypal_response = '';

        /*if (MODULE_PAYMENT_PAYPALIPN_TEST_MODE == 'True') {

            if ($item_number) {
                $paypal_response = $_POST['ipnstatus'];
                echo 'TEST IPN Processed for order #' . $item_number;
            } else {
                echo 'You need to specify an order #';
            };
        } else*/
        if (true /*MODULE_PAYMENT_PAYPALIPN_CURL == 'True'*/) { // IF CURL IS ON, SEND DATA USING CURL (SECURE MODE, TO https://)
            $ch = curl_init();
            if ($paypalipn->test_mode){
                curl_setopt($ch, CURLOPT_URL, "https://ipnpb.sandbox.paypal.com/cgi-bin/webscr");
            } else {
                curl_setopt($ch, CURLOPT_URL, "https://ipnpb.paypal.com/cgi-bin/webscr");
            }
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            //Your best bet is to not set this and let it use the default. Setting it to 2 or 3 is very dangerous given the known vulnerabilities in SSLv2 and SSLv3.
            //http://php.net/manual/en/function.curl-setopt.php
            //curl_setopt( $ch, CURLOPT_SSLVERSION, 3 );

            $paypal_response = curl_exec($ch);
            curl_close($ch);
        } else { // ELSE, SEND IT WITH HEADERS (STANDARD MODE, TO http://)
            $header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $header .= "Content-Length: " . strlen($req) . "\r\n";
            $header .= "Host: ipnpb.paypal.com\r\n";
            $header .= "Connection: close\r\n\r\n";
            if ($fp = fsockopen("ssl://ipnpb.paypal.com", 443, $errno, $errstr, 30)){
                fputs($fp, $header . $req);
                while (!feof($fp)) {
                    $paypal_response .= fgets($fp, 1024);
                };

                fclose($fp);
            }
        };

        if (preg_match('/VERIFIED/', $paypal_response)) {
            $response_verified = 1;
            $ipn_result = 'VERIFIED';
        } else if (preg_match('/INVALID/', $paypal_response)) {
            $response_invalid = 1;
            $ipn_result = 'INVALID';
        } else {
            echo 'Error: no valid $paypal_response received.';
        };

        if ($txn_id && ( $response_verified == 1 || $response_invalid == 1 || true )) {

            $txn_check = tep_db_query("select txn_id from " . TABLE_PAYPALIPN_TXN . " where txn_id='$txn_id'");
            if (tep_db_num_rows($txn_check) == 0) { // If txn no previously registered, we should register it
                $sql_data_array = array(
                    'txn_id' => $txn_id,
                    'ipn_result' => $ipn_result,
                    'receiver_email' => $receiver_email,
                    'business' => $business,
                    'item_name' => $item_name,
                    'item_number' => $item_number,
                    'quantity' => $quantity,
                    'invoice' => $invoice,
                    'custom' => $custom,
                    'option_name1' => $option_name1,
                    'option_selection1' => $option_selection1,
                    'option_name2' => $option_name2,
                    'option_selection2' => $option_selection2,
                    'num_cart_items' => $num_cart_items,
                    'payment_status' => $payment_status,
                    'pending_reason' => $pending_reason,
                    'payment_date' => $payment_date,
                    'settle_amount' => $settle_amount,
                    'settle_currency' => $settle_currency,
                    'exchange_rate' => $exchange_rate,
                    'payment_gross' => $payment_gross,
                    'payment_fee' => $payment_fee,
                    'mc_gross' => $mc_gross,
                    'mc_fee' => $mc_fee,
                    'mc_currency' => $mc_currency,
                    'tax' => $tax,
                    'txn_type' => $txn_type,
                    'memo' => $memo,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'address_street' => $address_street,
                    'address_city' => $address_city,
                    'address_state' => $address_state,
                    'address_zip' => $address_zip,
                    'address_country' => $address_country,
                    'address_status' => $address_status,
                    'payer_email' => $payer_email,
                    'payer_id' => $payer_id,
                    'payer_status' => $payer_status,
                    'payment_type' => $payment_type,
                    'notify_version' => $notify_version,
                    'verify_sign' => $verify_sign,
                    'payment_class' => $paypalipn->code,
                    'is_assigned' => 1,
                );

                tep_db_perform(TABLE_PAYPALIPN_TXN, $sql_data_array);
            } else { // else we update it to the new status
                $sql_data_array = array(
                    'payment_status' => $payment_status,
                    'pending_reason' => $pending_reason,
                    'ipn_result' => $ipn_result,
                    'payer_email' => $payer_email,
                    'payer_id' => $payer_id,
                    'payer_status' => $payer_status,
                    'payment_type' => $payment_type
                );

                tep_db_perform(TABLE_PAYPALIPN_TXN, $sql_data_array, 'update', 'txn_id=\'' . $txn_id . '\'');
            };
        };

        if ($response_verified == 1) {
            if (strtolower($receiver_email) == strtolower(MODULE_PAYMENT_PAYPALIPN_ID) || strtolower($business) == strtolower(MODULE_PAYMENT_PAYPALIPN_ID)) {
                if ($payment_status == 'Completed') {
                    $stock_updated = false;
                    if (MODULE_PAYMENT_PAYPALIPN_UPDATE_STOCK_BEFORE_PAYMENT == 'False' && !\common\helpers\Order::is_stock_updated((int) $item_number)) {
                        for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
                            // Stock Update - Joao Correia
                            if (STOCK_LIMITED == 'true') {
                                \common\helpers\Warehouses::update_stock_of_order($item_number, (strlen($order->products[$i]['template_uprid']) > 0 ? $order->products[$i]['template_uprid'] : $order->products[$i]['id']), $order->products[$i]['qty']);
                                $stock_updated = true;
                            }
                        }
                    }

                    if ($_POST['txn_type'] == 'subscr_payment') { //subscription payment
                    }
                    $sql_data_array = [];
                    if ($stock_updated === true) {
                        $sql_data_array['stock_updated'] = 1;
                        tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', 'orders_id=' . $item_number);
                    }
                    
                    if (is_numeric(MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID) && ( MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID > 0 )) {
                        $order_status = MODULE_PAYMENT_PAYPALIPN_ORDER_STATUS_ID;
                    } else {
                        $order_status = DEFAULT_ORDERS_STATUS_ID;
                    }
                    $order->info['order_status'] = $order_status;

                    $pp_result = 'Transaction ID: ' . \common\helpers\Output::output_string_protected($txn_id) . "\n" .
                            'Payer Status: ' . \common\helpers\Output::output_string_protected($payer_status) . "\n" .
                            'Address Status: ' . \common\helpers\Output::output_string_protected($address_status) . "\n" .
                            'Payment Status: ' . \common\helpers\Output::output_string_protected($payment_status) . "\n" .
                            'Payment Type: ' . \common\helpers\Output::output_string_protected($payment_type);
                    
                    \common\helpers\Order::setStatus($item_number, $order_status);
                    
                    $order->info['comments'] = $pp_result;

                    $order->update_piad_information(true);

                    $order->save_details();
                    $order->info['comments'] = '';

                    $order->notify_customer($order->getProductsHtmlForEmail(),[]);
                    
                    if ($paypalipn){
                        
                      if (false && $is_pay) {
                        $splitter = $this->manager->getOrderSplitter();
                        $splinters = $splitter->getInstancesFromSplinters($order->order_id, \common\services\SplitterManager::STATUS_PENDING);

                        if (/*$this->use_splinters && */ count($splinters)){
                            $this->manager->replaceOrderInstance(array_shift($splinters));
                        }
                      }

                        $invoice_id = $this->manager->getOrderSplitter()->getInvoiceId();
                        $this->manager->getTransactionManager($paypalipn)->updateTransaction($txn_id, $payment_status, $mc_gross, $invoice_id, 'Customer\'s payment');
                        
                        $pRecord = \common\helpers\OrderPayment::searchRecord($payment, md5($order->order_id."_".urlencode($order->customer['firstname'])));
                        if ($pRecord){
                            if ( empty($pRecord->orders_payment_order_id) ) {
                                $pRecord->orders_payment_order_id = $order->order_id;
                            }
                            $pRecord->orders_payment_status = \common\helpers\OrderPayment::OPYS_SUCCESSFUL;
                            $pRecord->orders_payment_amount = (float)$mc_gross;
                            $pRecord->orders_payment_snapshot = json_encode(\common\helpers\OrderPayment::getOrderPaymentSnapshot($order));
                            $pRecord->orders_payment_transaction_id = trim($txn_id);
                            $pRecord->orders_payment_transaction_status = trim($payment_status);
                            $pRecord->orders_payment_transaction_commentary = trim($pp_result);
                            $pRecord->orders_payment_transaction_date = new \yii\db\Expression("now()");
                            $pRecord->save();
                        }
                    }

                    try {
                        if ( is_object($paypalipn) && method_exists($paypalipn,'trackCredits') ) {
                            $paypalipn->trackCredits();
                        }
                    }catch (\Exception $ex){}

                    tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id=" . (int) $order->customer['id']);
                    tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id=" . (int) $order->customer['id']);

                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ReferFriend', 'allowed')) {
                        $ext::rf_after_order_placed($item_number);
                    }

                    //{{ push google analytics data
                    $provider = (new \common\components\GoogleTools())->getModulesProvider();
                    $installed_modules = $provider->getInstalledModules($order->info['platform_id']);
                    if (isset($installed_modules['ecommerce'])) {
                      $installed_modules['ecommerce']->forceServerSide($order);
                    }
                    //}}
                } elseif ($_POST['subscr_id'] != '') {
                    $status = \common\helpers\Subscription::getStatus('Canceled');
                    if ($_POST['txn_type'] == 'subscr_cancel') {
                        tep_db_query("update " . TABLE_ORDERS . " set orders_status='" . $status . "' where orders_id='" . (int) $item_number . "'");
                        $sql_data_array = array(
                            'orders_id' => (int) $item_number,
                            'orders_status_id' => $status,
                            'date_added' => 'now()',
                            'comments' => "Subscription Cancelled",
                            'customer_notified' => 0
                        );
                        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                    } elseif ($_POST['txn_type'] == 'subscr_failed') {
                        tep_db_query("update " . TABLE_ORDERS . " set orders_status='" . $status . "' where orders_id='" . (int) $item_number . "'");
                        $sql_data_array = array(
                            'orders_id' => (int) $item_number,
                            'orders_status_id' => $status,
                            'date_added' => 'now()',
                            'comments' => "Subscription Failed",
                            'customer_notified' => 0
                        );
                        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                    }
                }
            };
        };
        exit();
    }

    public function actionRedirectByJs() {
        global $navigation, $request_type;
        if (!$this->manager->isCustomerAssigned()) {
            $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }
        $method = "get";
        if (isset($_GET['payment_error']) && tep_not_null($_GET['payment_error'])) {
            $redirect_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL');
            $hidden_params = '<input type="hidden" name="payment_error" value="' . str_replace('"', "'", strip_tags($_GET['payment_error'])) . '">';
            $hidden_params .= '<input type="hidden" name="error" value="' . str_replace('"', "'", strip_tags($_GET['error'])) . '">';
        } else {
            $hidden_params = '';

            $orderId = \Yii::$app->request->get('order_id');
            $refs = \Yii::$app->request->get('refs', 0);
            $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $orderId);

            $this->manager->clearAfterProcess();
            
            if ($refs && !empty($order->customer['customer_id']) && $order->customer['customer_id'] == $refs) {
              if ($ext = \common\helpers\Acl::checkExtensionAllowed('ReferFriend', 'allowed')) {
                  $ext::rf_after_order_placed($order->order_id);
              }

              if ($ext = \common\helpers\Acl::checkExtensionAllowed('Affiliate', 'allowed')) {
                  $ext::CheckSales($order);
              }

            }

            //$redirect_url = \Yii::$app->urlManager->createAbsoluteUrl(['checkout/success', 'order_id' => $orderId]);
            if ($orderId) {
              $hidden_params = '<input type="hidden" name="order_id" value="' . $orderId . '">';
            }
            $redirect_url = tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');
        }

        $this->layout = false;

        Translation::init('checkout/confirmation');
        ?>
        <!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title><?php echo STORE_NAME; ?></title>
                <base href="<?php echo ( ( $request_type == 'SSL' ) ? HTTPS_SERVER : HTTP_SERVER ) . DIR_WS_CATALOG; ?>">
                <link rel="stylesheet" type="text/css" href="stylesheet.css">
            </head>
            <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
                <form name="redirect" action="<?php echo $redirect_url; ?>" method="<?= $method ?>"
                      target="_top"><?php echo $hidden_params; ?>
                    <noscript>
                        <p align="center" class="main">The transaction is being finalized. Please click continue to finalize
                            your order.</p>
                        <p align="center" class="main"><input type="submit" value="Continue"/></p>
                    </noscript>
                </form>
                <script language="javascript">
                    document.redirect.submit();
                </script>
            </body>
        </html>
        <?php
        exit();
    }

    public function actionSageServer() {
        if (isset($_GET['check']) && ($_GET['check'] == 'SERVER')) {
            $payment = $this->manager->getPaymentCollection('sage_pay_server')->getSelectedPayment();
            if (is_object($payment)) {
                $payment->safeServer();
            }
        }
    }

    public function actionWebhooks($set, $module) {
      if ($set=='shipping') {
        $payment = $this->manager->getShippingCollection()->get($module, false);
      } elseif ($set=='payment') {
          $payment = $this->manager->getPaymentCollection($module)->getSelectedPayment();
            } else {
        throw new \yii\web\BadRequestHttpException();
      }
        if (is_object($payment) && method_exists($payment, 'call_webhooks')){
            $this->manager->setSelectedPayment($payment->code);
            return $payment->call_webhooks(); //CORS - only via return to includeallowed domains in header!!!!
        }
        // interrupts w/o headers etc
        exit();
    }

    public function actionCheckoutLiqpay() {

        $order_id = Yii::$app->request->get('order_id');
        $data = Yii::$app->request->post('data');
        $signature = Yii::$app->request->post('signature');

        $liqpay = $this->manager->getPaymentCollection('liqpay')->getSelectedPayment();

        if (is_object($liqpay)){
            ob_start();
            echo "<pre>";
            print_r("Order_id: " . $order_id);
            echo "</pre>";
            echo "<pre>";
            print_r("data");
            echo "</pre>";
            echo "<pre>";
            print_r(json_decode(base64_decode($data)));
            echo "</pre>";
            echo "<pre>";
            print_r("Signature: " . $signature);
            echo "</pre>";
            echo "<pre>";
            print_r("validResponse: " . (bool) $liqpay->validResponse($data, $signature));
            echo "</pre>";

            if (!empty($order_id) && !empty($signature) && $liqpay->validResponse($data, $signature)) {
                $objData = json_decode(base64_decode($data));
                if ($objData->status == 'success' || $objData->status == 'sandbox') {
                    $order = Orders::findOne($order_id);
                    if (is_object($order)) {
                        $ordersStatusHistory = OrdersStatusHistory::create($order_id, $liqpay->paid_status);
                        $order->orders_status = $liqpay->paid_status;
                        $order->save();
                        $ordersStatusHistory->link('order', $order);
                    }
                }
            }

            $content = ob_get_clean();
            file_put_contents(DIR_WS_MODULES . "payment/liqpay_callback.log", $content);
        }
           
        exit;
    }

    public function actionPaypalExpress() { //for paypal express
        //global $order, $sendto, $billto, $total_count, $total_weight, $payment; 
        global $cart, $navigation;
        //global $shipping, $customer_country_id, $customer_zone_id, $customer_first_name;
        global $ppe_token, $ppe_secret, $ppe_payerid, $ppe_order_total_check, $response_array, $ppe_payerstatus, $ppe_addressstatus;
        $messageStack = \Yii::$container->get('message_stack');
        $currencies = \Yii::$container->get('currencies');
        $currency = \Yii::$app->settings->get('currency');
        Translation::init('payment');
        Translation::init('account/create');
        
        $this->manager->combineShippings = true;
        
        $this->manager->loadCart($cart);

        $paypal_express = $this->manager->getPaymentCollection('paypal_express')->getSelectedPayment();

        if (!is_object($paypal_express) || !$paypal_express->check(PLATFORM_ID) || !$paypal_express->enabled) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
        }

        // register a random ID in the session to check throughout the checkout procedure
        // against alterations in the shopping cart contents
        
        $this->manager->set('cartID', $cart->cartID);
        
        switch ($_GET['osC_Action']) {
            case 'cancel':
                tep_session_unregister('ppe_token');
                tep_session_unregister('ppe_secret');

                tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));

                break;
            case 'callbackSet':
                if (MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE == 'True') {
                    $counter = 0;

                    if (isset($_POST['CURRENCYCODE']) && $currencies->is_set($_POST['CURRENCYCODE']) && ( $currency != $_POST['CURRENCYCODE'] )) {
                        $currency = $_POST['CURRENCYCODE'];
                        \Yii::$app->settings->set('currency', $currency);
                    }

                    while (true) {
                        if (isset($_POST['L_NUMBER' . $counter])) {
                            $cart->add_cart($_POST['L_NUMBER' . $counter], $_POST['L_QTY' . $counter]);
                        } else {
                            break;
                        }

                        $counter ++;
                    }

                    // exit if there is nothing in the shopping cart
                    if ($cart->count_contents() < 1) {
                        exit;
                    }

                    $_sendto = array(
                        'firstname' => '',
                        'lastname' => '',
                        'company' => '',
                        'street_address' => $_POST['SHIPTOSTREET'],
                        'suburb' => $_POST['SHIPTOSTREET2'],
                        'postcode' => $_POST['SHIPTOZIP'],
                        'city' => $_POST['SHIPTOCITY'],
                        'zone_id' => '',
                        'zone' => $_POST['SHIPTOSTATE'],
                        'country_id' => '',
                        'country' => [],
                        'country_name' => $_POST['SHIPTOCOUNTRY'],
                        'country_iso_code_2' => '',
                        'country_iso_code_3' => '',
                        'format_id' => ''
                    );

                    $country = \common\helpers\Country::get_country_info_by_iso($_sendto['country_name']);
                    if (is_array($country)){
                        $_sendto['country_id'] = $country['id'];
                        $_sendto['country'] = [
                            'id' => $country['id'],
                            'title' => $country['title'],
                            'iso_code_2' => $country['iso_code_2'],
                            'iso_code_3' => $country['iso_code_3'],
                        ];
                        $_sendto['format_id'] = $country['address_format_id'];
                    }
                    if ($_sendto['country_id'] > 0) {
                        $zone_query = tep_db_query("select * from " . TABLE_ZONES . " where zone_country_id = '" . (int) $_sendto['country_id'] . "' and (zone_name = '" . tep_db_input($_sendto['zone']) . "' or zone_code = '" . tep_db_input($_sendto['zone']) . "') limit 1");
                        if (tep_db_num_rows($zone_query)) {
                            $zone = tep_db_fetch_array($zone_query);

                            $_sendto['zone_id'] = $zone['zone_id'];
                            $_sendto['zone'] = $zone['zone_name'];
                        }
                    }

                    $this->manager->set('estimate_ship', $_sendto);
                    $this->manager->set('estimate_bill', $_sendto);
                    $this->manager->resetDeliveryAddress();

                    $quotes_array = array();

                    $order = $this->manager->createOrderInstance('\common\classes\Order');
                    $this->manager->checkoutOrder();
                    
                    if ($this->manager->isShippingNeeded()) {
                        $quotes = $this->manager->getShippingQuotesByChoice();
                        
                        foreach ($quotes as $quote) {
                            if (!isset($quote['error']) && is_array($quote['methods'])) {
                                foreach ($quote['methods'] as $rate) {
                                    $quotes_array[] = array(
                                        'id' => $quote['id'] . '_' . $rate['id'],
                                        'name' => $quote['module'],
                                        'label' => $rate['title'],
                                        'cost' => $rate['cost'],
                                        'tax' => isset($quote['tax']) ? $quote['tax'] : '0'
                                    );
                                }
                            }
                        }
                        
                    } else {
                        $quotes_array[] = array(
                            'id' => 'null',
                            'name' => 'No Shipping',
                            'label' => '',
                            'cost' => '0',
                            'tax' => '0'
                        );
                    }
                    
                    $this->manager->totalProcess();

                    $params = array(
                        'METHOD' => 'CallbackResponse',
                        'CALLBACKVERSION' => $paypal_express->api_version
                    );

                    if (!empty($quotes_array)) {
                        $params['CURRENCYCODE'] = $currency;
                        $params['OFFERINSURANCEOPTION'] = 'false';

                        $counter = 0;
                        $cheapest_rate = null;
                        $cheapest_counter = $counter;
                        $blockCheap = false;

                        foreach ($quotes_array as $quote) {
                            $shipping_rate = $paypal_express->format_raw($quote['cost'] + \common\helpers\Tax::calculate_tax($quote['cost'], $quote['tax']));

                            $params['L_SHIPPINGOPTIONNAME' . $counter] = $quote['name'];
                            $params['L_SHIPPINGOPTIONLABEL' . $counter] = $quote['label'];
                            $params['L_SHIPPINGOPTIONAMOUNT' . $counter] = $shipping_rate;
                            $params['L_SHIPPINGOPTIONISDEFAULT' . $counter] = 'false';

                            if (DISPLAY_PRICE_WITH_TAX == 'false') {
                                $params['L_TAXAMT' . $counter] = $paypal_express->format_raw($order->info['tax']);
                            }

                            if (isset($_GET['shippingMethod']) && $_GET['shippingMethod'] == $quote['id']){
                                $cheapest_counter = $counter;
                                $blockCheap = true;
                            } else if (is_null($cheapest_rate) || ( $shipping_rate < $cheapest_rate )) {
                                $cheapest_rate = $shipping_rate;
                                if (!$blockCheap){
                                    $cheapest_counter = $counter;
                                }
                            }

                            $counter ++;
                        }

                        $params['L_SHIPPINGOPTIONISDEFAULT' . $cheapest_counter] = 'true';
                    } else {
                        $params['NO_SHIPPING_OPTION_DETAILS'] = '1';
                    }

                    $post_string = '';

                    foreach ($params as $key => $value) {
                        $post_string .= $key . '=' . urlencode(utf8_encode(trim($value))) . '&';
                    }

                    $post_string = substr($post_string, 0, - 1);

                    echo $post_string;
                }

                tep_session_destroy();

                exit;

                break;
            case 'retrieve':
                // if there is nothing in the customers cart, redirect them to the shopping cart page
                if ($cart->count_contents() < 1) {
                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                }

                $response_array = $paypal_express->getExpressCheckoutDetails($_GET['token']);

                if (( $response_array['ACK'] == 'Success' ) || ( $response_array['ACK'] == 'SuccessWithWarning' )) {
                    if (!tep_session_is_registered('ppe_secret') || ( $response_array['PAYMENTREQUEST_0_CUSTOM'] != $ppe_secret )) {
                        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                    }

                    $this->manager->setPayment($paypal_express->code);                    
                    //$payment = $paypal_express->code;

                    if (!tep_session_is_registered('ppe_token')) {
                        tep_session_register('ppe_token');
                    }
                    $ppe_token = $response_array['TOKEN'];

                    if (!tep_session_is_registered('ppe_payerid')) {
                        tep_session_register('ppe_payerid');
                    }
                    $ppe_payerid = $response_array['PAYERID'];

                    if (!tep_session_is_registered('ppe_payerstatus')) {
                        tep_session_register('ppe_payerstatus');
                    }
                    $ppe_payerstatus = $response_array['PAYERSTATUS'];

                    if (!tep_session_is_registered('ppe_addressstatus')) {
                        tep_session_register('ppe_addressstatus');
                    }
                    $ppe_addressstatus = $response_array['ADDRESSSTATUS'];

                    // check if paypal shipping address exists in the address book
                    $ship_firstname = tep_db_prepare_input(substr($response_array['PAYMENTREQUEST_0_SHIPTONAME'], 0, strpos($response_array['PAYMENTREQUEST_0_SHIPTONAME'], ' ')));
                    $ship_lastname = tep_db_prepare_input(substr($response_array['PAYMENTREQUEST_0_SHIPTONAME'], strpos($response_array['PAYMENTREQUEST_0_SHIPTONAME'], ' ') + 1));
                    $ship_address = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOSTREET']);
                    $ship_address2 = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOSTREET2']);
                    $ship_city = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOCITY']);
                    $ship_zone = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOSTATE']);
                    $ship_zone_id = 0;
                    $ship_postcode = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOZIP']);
                    $ship_country = tep_db_prepare_input($response_array['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE']);
                    $ship_country_id = 0;
                    $ship_address_format_id = 1;

                    $country_query = tep_db_query("select countries_id, address_format_id from " . TABLE_COUNTRIES . " where countries_iso_code_2 = '" . tep_db_input($ship_country) . "' limit 1");
                    if (tep_db_num_rows($country_query)) {
                        $country = tep_db_fetch_array($country_query);

                        $ship_country_id = $country['countries_id'];
                        $ship_address_format_id = $country['address_format_id'];
                    }

                    if ($ship_country_id > 0) {
                        $zone_query = tep_db_query("select zone_id from " . TABLE_ZONES . " where zone_country_id = '" . (int) $ship_country_id . "' and (zone_name = '" . tep_db_input($ship_zone) . "' or zone_code = '" . tep_db_input($ship_zone) . "') limit 1");
                        if (tep_db_num_rows($zone_query)) {
                            $zone = tep_db_fetch_array($zone_query);

                            $ship_zone_id = $zone['zone_id'];
                        }
                    }
                    
                    $updateAddress = false;
                    // check if e-mail address exists in database and login or create customer account
                    if (Yii::$app->user->isGuest) {

                        $email_address = tep_db_prepare_input($response_array['EMAIL']);

                        $check_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' limit 1");
                        if (tep_db_num_rows($check_query)) {
                            $check = tep_db_fetch_array($check_query);

                            // Force the customer to log into their local account if payerstatus is unverified and a local password is set
                            
                            $customer = new \common\components\Customer(\common\components\Customer::LOGIN_WITHOUT_CHECK);
                            if ($customer->loginCustomer($email_address, $check['customers_id'])){
                                $customer = Yii::$app->user->getIdentity();
                            }
                            
                        } else {

                            $model = new \frontend\forms\registration\CustomerRegistration();

                            if (!defined("DEFAULT_USER_LOGIN_GROUP")) {
                                $model->group = 0;
                            } else {
                                $model->group = DEFAULT_USER_LOGIN_GROUP;
                            }
                            $model->password = \common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
                            $model->newsletter = 0;
                            $model->email_address = $email_address;
                            $model->firstname = $response_array['FIRSTNAME'];
                            $model->lastname = $response_array['LASTNAME'];

                            $model->country = (int) $ship_country_id;
                            $model->zone_id = (int) $ship_zone_id;

                            if (isset($response_array['PHONENUM']) && tep_not_null($response_array['PHONENUM'])) {
                                $model->telephone = tep_db_prepare_input($response_array['PHONENUM']);
                            }
                            $customer = new \common\components\Customer();
                            $customer->registerCustomer($model);
                            $updateAddress = true;
                        }
                        // reset session token
                        $sessiontoken = md5(\common\helpers\Password::rand() . \common\helpers\Password::rand() . \common\helpers\Password::rand() . \common\helpers\Password::rand());
                    } else {                        
                        $customer = Yii::$app->user->getIdentity();
                    }
                    
                    if ($customer->customers_id){
                        $this->manager->assignCustomer($customer->customers_id);
                        $this->manager->set('cartID', $cart->cartID);
                    }
                    
                    $check_query = tep_db_query("select address_book_id from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customer->customers_id . "' and entry_firstname = '" . tep_db_input($ship_firstname) . "' and entry_lastname = '" . tep_db_input($ship_lastname) . "' and entry_street_address = '" . tep_db_input($ship_address) . "' and entry_postcode = '" . tep_db_input($ship_postcode) . "' and entry_city = '" . tep_db_input($ship_city) . "' and (entry_state = '" . tep_db_input($ship_zone) . "' or entry_zone_id = '" . (int) $ship_zone_id . "') and entry_country_id = '" . (int) $ship_country_id . "' limit 1");
                    if (tep_db_num_rows($check_query)) {
                        $check = tep_db_fetch_array($check_query);

                        $sendto = $check['address_book_id'];
                    } else {
                        $sql_data_array = array(
                            'customers_id' => $customer->customers_id,
                            'entry_firstname' => $ship_firstname,
                            'entry_lastname' => $ship_lastname,
                            'entry_street_address' => $ship_address,
                            'entry_suburb' => $ship_address2,
                            'entry_postcode' => $ship_postcode,
                            'entry_city' => $ship_city,
                            'entry_country_id' => $ship_country_id
                        );
                        
                        if ($ship_zone_id > 0) {
                            $sql_data_array['entry_zone_id'] = $ship_zone_id;
                            $sql_data_array['entry_state'] = '';
                        } else {
                            $sql_data_array['entry_zone_id'] = '0';
                            $sql_data_array['entry_state'] = $ship_zone;
                        }
                        
                        if ($updateAddress){
                            $aBook = $customer->updateAddress($customer->customers_default_address_id, $sql_data_array);
                        } else {
                            $aBook = $customer->addAddress($sql_data_array);
                        }
                        $sendto = $aBook->address_book_id;
                    }

                    $billto = $sendto;
                    $this->manager->set('sendto', $sendto);
                    $this->manager->set('billto', $billto);
                    
                    $order = $this->manager->createOrderInstance('\common\classes\Order');
                    $this->manager->checkoutOrderWithAddresses();
                    //$order = new \common\classes\Order();
                    
                    $shipping = false;
                    
                    if ($this->manager->isShippingNeeded()){
                        $quotes = $this->manager->getShippingQuotesByChoice();
                        
                        if ($quotes){
                            $shipping_set = false;

                            // if available, set the selected shipping rate from PayPals order review page
                            if (isset($response_array['SHIPPINGOPTIONNAME']) && isset($response_array['SHIPPINGOPTIONAMOUNT'])) {
                                foreach ($quotes as $quote) {
                                    if (!isset($quote['error'])) {
                                        foreach ($quote['methods'] as $rate) {
                                            if ($response_array['SHIPPINGOPTIONNAME'] == trim($quote['module'] . ' ' . $rate['title'])) {
                                                $shipping_rate = $paypal_express->format_raw($rate['cost'] + \common\helpers\Tax::calculate_tax($rate['cost'], $quote['tax']));

                                                if ($response_array['SHIPPINGOPTIONAMOUNT'] == $shipping_rate) {
                                                    $shipping = $quote['id'] . '_' . $rate['id'];
                                                    $shipping_set = true;
                                                    break 2;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            if ($shipping_set == false) {
                                // select cheapest shipping method
                                $shipping_set = $this->manager->getShippingCollection()->cheapest();
                                if ($shipping_set){
                                    $shipping = $shipping_set['id'];
                                }                                
                            }
                        } else {
                            if (defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && ( SHIPPING_ALLOW_UNDEFINED_ZONES == 'False' )) {
                                $this->manager->remove('shipping');

                                $messageStack->add_session(MODULE_PAYMENT_PAYPAL_EXPRESS_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS, 'checkout_address', 'error');

                                tep_session_register('ppec_right_turn');
                                $ppec_right_turn = true;

                                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
                            }
                        }
                        
                        if ($shipping && strpos($shipping, '_')) {                            
                            $this->manager->setSelectedShipping($shipping);                            
                        } else {
                            $this->manager->remove('shipping');
                            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
                        }
                        
                        
                    } else {
                        // In checkout process check sendto if not pickup
                        // $this->manager->remove('shipping');
                        // $this->manager->remove('sendto');
                    }

                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
                } else {
                    $messageStack->add_session(stripslashes($response_array['L_LONGMESSAGE0']), 'header', 'error');

                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                }

                break;

            default:
                // if there is nothing in the customers cart, redirect them to the shopping cart page
                if ($cart->count_contents() < 1) {
                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                }

                $paypal_url = $paypal_express->getUrl();
                
                $order = $this->manager->createOrderInstance('\common\classes\Order');
                $this->manager->checkoutOrderWithAddresses();
                

                $params = array(
                    'PAYMENTREQUEST_0_CURRENCYCODE' => $order->info['currency'],
                    'ALLOWNOTE' => 0
                );

                // A billing address is required for digital orders so we use the shipping address PayPal provides
                //      if ($order->content_type == 'virtual') {
                //        $params['NOSHIPPING'] = '1';
                //      }

                $item_params = array();

                $line_item_no = 0;
                
                foreach ($order->products as $product) {
                    // {{
                    if ($product['wristband_count'] == 1) {
                        $product['final_price'] *= $product['qty'];
                        $product['name'] = $product['qty'] . ' ' . $product['name'];
                        $product['qty'] = 1;
                    }
                    // }}
                    if (DISPLAY_PRICE_WITH_TAX == 'true') {
                        $product_price = $paypal_express->format_raw($product['final_price'] + \common\helpers\Tax::calculate_tax($product['final_price'], $product['tax']));
                    } else {
                        $product_price = $paypal_express->format_raw($product['final_price']);
                    }

                    $item_params['L_PAYMENTREQUEST_0_NAME' . $line_item_no] = $product['name'];
                    $item_params['L_PAYMENTREQUEST_0_AMT' . $line_item_no] = $product_price;
                    $item_params['L_PAYMENTREQUEST_0_NUMBER' . $line_item_no] = $product['id'];
                    $item_params['L_PAYMENTREQUEST_0_QTY' . $line_item_no] = $product['qty'];
                    $item_params['L_PAYMENTREQUEST_0_ITEMURL' . $line_item_no] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $product['id'], 'NONSSL', false);

                    if (( DOWNLOAD_ENABLED == 'true' ) && isset($product['attributes'])) {
                        $item_params['L_PAYMENTREQUEST_0_ITEMCATEGORY' . $line_item_no] = $paypal_express->getProductType($product['id'], $product['attributes']);
                    } else {
                        $item_params['L_PAYMENTREQUEST_0_ITEMCATEGORY' . $line_item_no] = 'Physical';
                    }
                    // Not All Customers can processed digital products
                    /*
                    if ($product['model'] === 'VIRTUAL_GIFT_CARD'){
                        $item_params['L_PAYMENTREQUEST_0_ITEMCATEGORY' . $line_item_no] = 'Digital';
                    }
                    /**/
                    $line_item_no ++;
                }

                if (tep_not_null($order->delivery['street_address'])) {
                    $params['PAYMENTREQUEST_0_SHIPTONAME'] = $order->delivery['firstname'] . ' ' . $order->delivery['lastname'];
                    $params['PAYMENTREQUEST_0_SHIPTOSTREET'] = $order->delivery['street_address'];
                    $params['PAYMENTREQUEST_0_SHIPTOSTREET2'] = $order->delivery['suburb'];
                    $params['PAYMENTREQUEST_0_SHIPTOCITY'] = $order->delivery['city'];
                    $params['PAYMENTREQUEST_0_SHIPTOSTATE'] = \common\helpers\Zones::get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], $order->delivery['state']);
                    $params['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $order->delivery['country']['iso_code_2'];
                    $params['PAYMENTREQUEST_0_SHIPTOZIP'] = $order->delivery['postcode'];
                }

                $quotes_array = array();
                
                if ($this->manager->isShippingNeeded()) {
                    
                    $quotes = $this->manager->getShippingQuotesByChoice();
                    
                    if (!$quotes){
                        if (defined('SHIPPING_ALLOW_UNDEFINED_ZONES') && ( SHIPPING_ALLOW_UNDEFINED_ZONES == 'False' )) {
                            $this->manager->remove('shipping');

                            $messageStack->add_session(MODULE_PAYMENT_PAYPAL_EXPRESS_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS, 'checkout_address');

                            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL'));
                        }
                    }
                    
                    foreach ($quotes as $quote) {
                        if (!isset($quote['error'])) {
                            foreach ($quote['methods'] as $rate) {
                                $quotes_array[] = array(
                                    'module' => $quote['module'],
                                    'id' => $quote['id'] . '_' . $rate['id'],
                                    'name' => $quote['module'],
                                    'label' => $rate['title'],
                                    'cost' => $rate['cost'],
                                    'tax' => $quote['tax']
                                );
                            }
                        }
                    }
                }
                
                $counter = 0;
                $cheapest_rate = null;
                $expensive_rate = 0;
                $cheapest_counter = $counter;
                $default_shipping = null;
                $default_shippingMethod = null;
                foreach ($quotes_array as $quote) {
                    $shipping_rate = $paypal_express->format_raw($quote['cost'] + \common\helpers\Tax::calculate_tax($quote['cost'], $quote['tax']));

                    $item_params['L_SHIPPINGOPTIONNAME' . $counter] = trim($quote['name'] . ' ' . $quote['label']);
                    $item_params['L_SHIPPINGOPTIONAMOUNT' . $counter] = $shipping_rate;
                    $item_params['L_SHIPPINGOPTIONISDEFAULT' . $counter] = 'false';

                    if (is_null($cheapest_rate) || ( $shipping_rate < $cheapest_rate )) {
                        $cheapest_rate = $shipping_rate;
                        $cheapest_counter = $counter;
                    }

                    if ($shipping_rate > $expensive_rate) {
                        $expensive_rate = $shipping_rate;
                    }

                    if ( $this->manager->has('shipping')){
                        $shipping = $this->manager->getShipping();
                        if ( $shipping['id'] == $quote['id']  ) {
                            $default_shipping = $counter;
                            $default_shippingMethod = $shipping['id'];
                        }
                    }
                    $counter ++;
                }
                
                if (!is_null($default_shipping)) {
                    $cheapest_rate = $item_params['L_SHIPPINGOPTIONAMOUNT' . $default_shipping];
                    $cheapest_counter = $default_shipping;
                } else {
                    if (!empty($quotes_array)) {
                        $shipping = array(
                            'module' => $quotes_array[$cheapest_counter]['module'],
                            'id' => $quotes_array[$cheapest_counter]['id'],
                            'title' => $item_params['L_SHIPPINGOPTIONNAME' . $cheapest_counter],
                            'cost' => $paypal_express->format_raw($quotes_array[$cheapest_counter]['cost']),
                            'cost_inc_tax' => \common\helpers\Tax::add_tax_always($quotes_array[$cheapest_counter]['cost'], (isset($quotes_array[$cheapest_counter]['tax']) ? $quotes_array[$cheapest_counter]['tax'] : 0))
                        );

                        $default_shipping = $cheapest_counter;
                        $this->manager->setShipping($shipping);
                    } else {
                        $shipping = false;
                        $this->manager->remove('shipping');
                    }
                    
                }
                
                // set shipping for order total calculations; shipping in $item_params includes taxes
                if (!is_null($default_shipping)) {
                    $order->info['shipping_method'] = $item_params['L_SHIPPINGOPTIONNAME' . $default_shipping];
                    $order->info['shipping_cost'] = $item_params['L_SHIPPINGOPTIONAMOUNT' . $default_shipping]; // TODO check for non default currency:  * $currencies->get_market_price_rate($order->info['currency'], DEFAULT_CURRENCY);

                    $order->info['total'] = $order->info['subtotal'] + $order->info['shipping_cost'];

                    if (DISPLAY_PRICE_WITH_TAX == 'false') {
                        $order->info['total'] += $order->info['tax'];
                    }
                }
                
                if (!is_null($cheapest_rate)) {
                    $item_params['PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED'] = 'false';
                    $item_params['L_SHIPPINGOPTIONISDEFAULT' . $cheapest_counter] = 'true';
                }
                
                if (!empty($quotes_array) && ( MODULE_PAYMENT_PAYPAL_EXPRESS_INSTANT_UPDATE == 'True' ) && ( ( MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER != 'Live' ) || ( ( MODULE_PAYMENT_PAYPAL_EXPRESS_TRANSACTION_SERVER == 'Live' ) && ( ENABLE_SSL == true ) ) )) { // Live server requires SSL to be enabled
                    $shippingMethod = '';
                    if (!is_null($default_shippingMethod)){
                        $shippingMethod = "&shippingMethod=".$default_shippingMethod;
                    }
                    $item_params['CALLBACK'] = tep_href_link('callback/paypal-express', 'osC_Action=callbackSet'.$shippingMethod, 'SSL', false, false);
                    $item_params['CALLBACKTIMEOUT'] = '6';
                    $item_params['CALLBACKVERSION'] = $paypal_express->api_version;
                }

                $order_totals = $this->manager->getTotalOutput(false);

                // Remove shipping tax from total that was added again in ot_shipping
                if (DISPLAY_PRICE_WITH_TAX == 'true') {
                    $order->info['shipping_cost'] = $order->info['shipping_cost'] / ( 1.0 + ( $quotes_array[$default_shipping]['tax'] / 100 ) );
                }
                if ($shipping){
                    //$module = substr($shipping['id'], 0, strpos($shipping['id'], '_'));
                    $order->info['tax'] -= \common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']);
                    $tax_desc = \common\helpers\Tax::get_tax_description($this->manager->getShippingCollection()->get($shipping['module'])->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
                    $order->info['tax_groups'][$tax_desc] -= \common\helpers\Tax::roundTax(\common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']));
                    $order->info['total'] -= \common\helpers\Tax::roundTax(\common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $quotes_array[$default_shipping]['tax']));
                }

                $items_total = $paypal_express->format_raw($order->info['subtotal']);

                foreach ($order_totals as $ot) {
                    if (!in_array($ot['code'], array(
                                'ot_subtotal',
                                'ot_shipping',
                                'ot_tax',
                                'ot_total',
                                'ot_refund',
                                'ot_subtax',
                                'ot_paid',// correct?
                                'ot_due',// correct?
                            ))) {
                        $item_params['L_PAYMENTREQUEST_0_NAME' . $line_item_no] = $ot['title'];
                        $item_params['L_PAYMENTREQUEST_0_AMT' . $line_item_no] = $paypal_express->format_raw($ot['value']);

                        $items_total += $paypal_express->format_raw($ot['value']);

                        $line_item_no ++;
                    }
                }                

                $params['PAYMENTREQUEST_0_AMT'] = $paypal_express->format_raw($order->info['total']);

                $item_params['MAXAMT'] = $paypal_express->format_raw($params['PAYMENTREQUEST_0_AMT'] + $expensive_rate + 100, '', 1); // safely pad higher for dynamic shipping rates (eg, USPS express)
                $item_params['PAYMENTREQUEST_0_ITEMAMT'] = $items_total;
                $item_params['PAYMENTREQUEST_0_SHIPPINGAMT'] = $paypal_express->format_raw($order->info['shipping_cost']);

                $paypal_item_total = $item_params['PAYMENTREQUEST_0_ITEMAMT'] + $item_params['PAYMENTREQUEST_0_SHIPPINGAMT'];

                if (DISPLAY_PRICE_WITH_TAX == 'false') {
                    $item_params['PAYMENTREQUEST_0_TAXAMT'] = $paypal_express->format_raw($order->info['tax']);

                    $paypal_item_total += $item_params['PAYMENTREQUEST_0_TAXAMT'];
                }

                if ($paypal_express->format_raw($paypal_item_total) == $params['PAYMENTREQUEST_0_AMT']) {
                    $params = array_merge($params, $item_params);
                }

                if (tep_not_null(MODULE_PAYMENT_PAYPAL_EXPRESS_PAGE_STYLE)) {
                    $params['PAGESTYLE'] = MODULE_PAYMENT_PAYPAL_EXPRESS_PAGE_STYLE;
                }
                
                $ppe_secret = \common\helpers\Password::create_random_value(16, 'digits');

                if (!tep_session_is_registered('ppe_secret')) {
                    tep_session_register('ppe_secret');
                }

                $params['PAYMENTREQUEST_0_CUSTOM'] = $ppe_secret;

                // Log In with PayPal token for seamless checkout
                if (tep_session_is_registered('paypal_login_access_token')) {
                    $params['IDENTITYACCESSTOKEN'] = $paypal_login_access_token;
                }
                
                $response_array = $paypal_express->setExpressCheckout($params);
                //      print_r($response_array); exit;

                if (( $response_array['ACK'] == 'Success' ) || ( $response_array['ACK'] == 'SuccessWithWarning' )) {
                    tep_redirect($paypal_url . 'token=' . $response_array['TOKEN'] . '&useraction=commit');
                } else {
                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes($response_array['L_LONGMESSAGE0']), 'SSL'));
                }

                break;
        }

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
    }
    
    public function actionPaypalExpressApp(){
        global $cart, $navigation, $languages_id;
        $messageStack = \Yii::$container->get('message_stack');
        $currencies = \Yii::$container->get('currencies');
        $currency = \Yii::$app->settings->get('currency');
        Translation::init('payment');
        Translation::init('account/create');
        
        $this->manager->combineShippings = true;
        
        $this->manager->loadCart($cart);

        $paypal_express = $this->manager->getPaymentCollection('paypal_express_app')->getSelectedPayment();
        
        if (!is_object($paypal_express) || !$paypal_express->check(PLATFORM_ID) || !$paypal_express->enabled) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
        }
        $this->manager->set('cartID', $cart->cartID);
        
        switch ($_GET['osC_Action']) {
            case 'cancel':
                $this->manager->remove('ppe_paymentId');
                $this->manager->remove('ppe_payerid');

                tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));

                break;
            case 'retrieve':
                
                if ($cart->count_contents() < 1) {
                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                }

                $ppe_paymentId = Yii::$app->request->get('paymentId');
                $this->manager->set('ppe_paymentId', $ppe_paymentId);
                
                $ppe_payerid = Yii::$app->request->get('PayerID');
                $this->manager->set('ppe_payerid', $ppe_payerid);
                
                try{
                    $response = $paypal_express->getPaymentDetails($ppe_paymentId);
                    
                    if ($response){
                        $payer = $response->getPayer();
                        $this->manager->setPayment($paypal_express->code);
                        $info = $payer->getPayerInfo();
                        $updateAddress = false;
                        // check if e-mail address exists in database and login or create customer account
                        if (Yii::$app->user->isGuest) {

                            $email_address = $info->getEmail();

                            $customer = \common\components\Customer::find()->where(['customers_email_address' => $email_address, 'opc_temp_account' => 0, 'customers_status' => 1])->limit(1)->one();
                            if ($customer){
                                $customer->setLoginType(\common\components\Customer::LOGIN_WITHOUT_CHECK);
                                if ($customer->loginCustomer($email_address, $customer->customers_id)){
                                    $customer = Yii::$app->user->getIdentity();
                                } else {
                                    trigger_error('Customer could not be authentificated');
                                }
                            } else {

                                $model = new \frontend\forms\registration\CustomerRegistration();

                                if (!defined("DEFAULT_USER_LOGIN_GROUP")) {
                                    $model->group = 0;
                                } else {
                                    $model->group = DEFAULT_USER_LOGIN_GROUP;
                                }
                                $model->password = \common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
                                $model->newsletter = 0;
                                $model->email_address = $email_address;
                                $model->firstname = $info->getFirstName();
                                $model->lastname = $info->getLastName();

                                $model->country = (int) STORE_COUNTRY;
                                $model->zone_id = (int) STORE_ZONE;

                                if ($info->getPhone()) {
                                    $model->telephone = $info->getPhone();
                                }
                                $customer = new \common\components\Customer();
                                $customer->registerCustomer($model);
                                $updateAddress = true;
                            }
                        } else {
                            $customer = Yii::$app->user->getIdentity();
                        }
                        
                        if ($customer->customers_id){
                            $this->manager->assignCustomer($customer->customers_id);
                            $this->manager->set('cartID', $cart->cartID);
                            
                            $address = $info->getShippingAddress();
                            if ($address){
                                $recipient = $address->getRecipientName();
                                $ship_firstname = substr($recipient, 0, strpos($recipient, ' '));
                                $ship_lastname = substr($recipient, strpos($recipient, ' ') + 1);
                                $ship_address = $address->getLine1();
                                $ship_address2 = $address->getLine2();
                                $ship_city = $address->getCity();
                                $ship_zone = $address->getState();
                                $ship_zone_id = 0;
                                $ship_postcode = $address->getPostalCode();
                                $ship_country = $address->getCountryCode();
                                $ship_country_id = 0;
                                $ship_address_format_id = 1;
                                if ($country = \common\models\Countries::find()->where(['countries_iso_code_2' => $ship_country, 'language_id' => $languages_id])->limit(1)->one()){
                                    $ship_country_id = $country->countries_id;
                                    $ship_address_format_id = $country->address_format_id;
                                }

                                if ($ship_country_id){
                                    $zone = \common\models\Zones::find()->where(['zone_country_id' => $ship_country_id])
                                            ->andWhere(['or', ['zone_name' => $ship_zone], ['zone_code' => $ship_zone]])
                                            ->limit(1)->one();
                                    if ($zone){
                                        $ship_zone_id = $zone->zone_id;
                                    }
                                }

                                $ab = \common\models\AddressBook::find()
                                        ->where(['and',
                                            ['customers_id' => $customer->customers_id],
                                            ['entry_firstname' => $ship_firstname],
                                            ['entry_lastname' => $ship_lastname],
                                            ['entry_street_address' => $ship_address],
                                            ['entry_postcode' => $ship_postcode],
                                            ['entry_city' => $ship_city],
                                            ['or', 
                                                ['entry_state' => $ship_zone],
                                                ['entry_zone_id' => $ship_zone_id]
                                                ],
                                            ['entry_country_id' => $ship_country_id]
                                            ])->limit(1)->one();
                                if ( $ab ){
                                    $sendto = $ab->address_book_id;
                                } else {
                                    $sql_data_array = array(
                                        'customers_id' => $customer->customers_id,
                                        'entry_firstname' => $ship_firstname,
                                        'entry_lastname' => $ship_lastname,
                                        'entry_street_address' => $ship_address,
                                        'entry_suburb' => $ship_address2,
                                        'entry_postcode' => $ship_postcode,
                                        'entry_city' => $ship_city,
                                        'entry_country_id' => $ship_country_id
                                    );

                                    if ($ship_zone_id > 0) {
                                        $sql_data_array['entry_zone_id'] = $ship_zone_id;
                                        $sql_data_array['entry_state'] = '';
                                    } else {
                                        $sql_data_array['entry_zone_id'] = '0';
                                        $sql_data_array['entry_state'] = $ship_zone;
                                    }

                                    if ($updateAddress){
                                        $aBook = $customer->updateAddress($customer->customers_default_address_id, $sql_data_array);
                                    } else {
                                        $aBook = $customer->addAddress($sql_data_array);
                                    }
                                    $sendto = $aBook->address_book_id;
                                }
                                $billto = $sendto;
                                $this->manager->set('sendto', $sendto);
                                $this->manager->set('billto', $billto);
                            }
                            
                            $this->manager->resetDeliveryAddress();
                            
                            $order = $this->manager->createOrderInstance('\common\classes\Order');
                            
                            if (!$this->manager->isShippingNeeded()){
                                $this->manager->remove('shipping');
                                $this->manager->remove('sendto');
                            } else {
                                $this->manager->getShippingQuotesByChoice();//paypal can return another address
                            }
                            
                            $this->manager->checkoutOrderWithAddresses();
                            
                            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'));
                        } else {
                            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error=undefined+error', 'SSL'));
                        }
                    }
                } catch (\Exception $ex) {
                    $messageStack->add_session(stripslashes($ex->getMessage()), 'header', 'error');
                }

                break;

            default:
                // if there is nothing in the customers cart, redirect them to the shopping cart page
                if ($cart->count_contents() < 1) {
                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                }
                
                $this->manager->remove('estimate_ship');
                
                $order = $this->manager->createOrderInstance('\common\classes\Order');
                $this->manager->checkoutOrderWithAddresses();
                
                if (!$paypal_express->createPayment()){//new api version
                    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
                }
                break;
        }

        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
    }

    public function actionCheckoutPxpay() {

        $order_id = Yii::$app->request->get('order_id'); // get ID from trnasaction (vulnerable here)
        $result = Yii::$app->request->get('result');

        $module = $this->manager->getPaymentCollection('pxpay')->getSelectedPayment();
        
        //$order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $order_id);
        //$oModel = $order->getARModel()->where(['orders_id' => $order_id])->one();
        
/*debug      $outputXml = '<root><Success>1</Success><MerchantReference>59</MerchantReference><AmountSettlement>77.77</AmountSettlement><ResponseText>ResponseText__ResponseText</ResponseText><CardHolderName>TEST PAyer</CardHolderName><TxnId>12345qa67q1</TxnId></root>';
        $response = new \PxPayResponse($outputXml);
/**/
        $response = $module->getResponse($result);
        $Success = $response->getSuccess();
        $ResponseText = $response->getResponseText();
        $txDetails = '';
        $txDetails .= $response->getCardHolderName() . "\n";
        $txDetails .= $response->getCardName() . "\n";
        $txDetails .= $response->getCardNumber() . "\n";
        $txDetails .= $response->getTxnType() . "\n";
        $txDetails .= $response->getAmountSettlement() . ' ' . $response->getCurrencySettlement() . ' ' . $response->getCurrencyInput() . "\n";
        $txDetails .= $response->getEmailAddress() . "\n";
        $txDetails .= $response->getClientInfo() . "\n";
        $txDetails .= $response->getTxnId() . "\n";
        /*
  $AmountSettlement  = $rsp->getAmountSettlement();
  $AuthCode          = $rsp->getAuthCode();  # from bank
  $CardName          = $rsp->getCardName();  # e.g. "Visa"
  $CardNumber        = $rsp->getCardNumber(); # Truncated card number
  $DateExpiry        = $rsp->getDateExpiry(); # in mmyy format
  $DpsBillingId      = $rsp->getDpsBillingId();
  $BillingId    	 = $rsp->getBillingId();
  $CardHolderName    = $rsp->getCardHolderName();
  $DpsTxnRef	     = $rsp->getDpsTxnRef();
  $TxnType           = $rsp->getTxnType();
  $TxnData1          = $rsp->getTxnData1();
  $TxnData2          = $rsp->getTxnData2();
  $TxnData3          = $rsp->getTxnData3();
  $CurrencySettlement= $rsp->getCurrencySettlement();
  $ClientInfo        = $rsp->getClientInfo(); # The IP address of the user who submitted the transaction
  $TxnId             = $rsp->getTxnId();
  $CurrencyInput     = $rsp->getCurrencyInput();
  $EmailAddress      = $rsp->getEmailAddress();
  $MerchantReference = $rsp->getMerchantReference();
  $ResponseText		 = $rsp->getResponseText();
  $TxnMac            = $rsp->getTxnMac(); # An indication as to the uniqueness of a card used in relation to others
         */
        $tmp_id = $response->getMerchantReference();
        if (!empty($tmp_id) && (int)$tmp_id > 0 && $tmp_id != $order_id) {
          //hack attempt??
          \Yii::warning('getMerchantReference ' . $tmp_id  . ' != ' . $order_id, 'PXPayCallback');
          $order_id = $tmp_id;
        }

        $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $order_id);
        $oModel = $order->getARModel()->where(['orders_id' => $order_id])->one();

        if ($Success) {
          if (!empty($oModel->transaction_id) ) {
            $tList = preg_split('/\|/', $oModel->transaction_id, -1, PREG_SPLIT_NO_EMPTY);
          } else {
            $tList = [];
          }

          if (empty($tList) || !in_array(trim($response->getTxnId()), $tList) ){
            $order->info['comments'] = str_replace(["\n\n", "\r"], ["\n", ''], $txDetails . $ResponseText);
            $order->info['order_status'] = $module->paid_status;

            /* 2do
// not fully paid
            if (abs($order->info['total_inc_tax'] - $order->info['total_paid_inc_tax'] - floatval($response->getAmountSettlement())) > 0.01) {
              $order->info['total_paid_inc_tax'] = $order->info['total_inc_tax'] - floatval($response->getAmountSettlement());
            }
            */
            $oModel->transaction_id = implode('|', array_merge([trim($response->getTxnId())], $tList));
            $oModel->orders_status = $module->paid_status;
            $oModel->update(false);
            
/**  2do (to replace when special method exists in order class */
            if (isset($order->products) && is_array($order->products)) {
              foreach ($order->products as $p) {
                if (!empty($p['orders_products_id'])) {
                  \common\helpers\OrderProduct::doAllocateAutomatic($p['orders_products_id'], true);
                } else {
                  \Yii::warning('Product stock allocation failed - no orders_products_id Order# ' . $order_id, 'stock allocation');
                }
              }
            }
    /** 2do eof */
            $order->update_piad_information(true);
            $order->save_details();
            
            $order->notify_customer($order->getProductsHtmlForEmail(),[]);

            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ReferFriend', 'allowed')) {
                $ext::rf_after_order_placed($order_id);
            }

              //{{ push google analytics data
              /* Class 'Google\Google_Service_Exception' not found
               *  in  lib/vendor/Google/Http/REST.php at line 119*/
            try {
              $provider = (new \common\components\GoogleTools())->getModulesProvider();
              $installed_modules = $provider->getInstalledModules($order->info['platform_id']);
              if (isset($installed_modules['ecommerce'])) {
                $installed_modules['ecommerce']->forceServerSide($order);
              }

            } catch (\Exception $e) {}
              //}}
          }
          
          $module->after_process();
          return $this->redirect(['checkout/success']);
        } else {
            $this->orderManageService->changeStatus($order_id, $module->fail_paid_status, $ResponseText);
            return $this->redirect([ '/checkout', 'returned_order'=> $order_id, 'error_message' => $ResponseText]);
        }
    }

    public function actionAmazonipn() {
        $operation = Yii::$app->request->post('operation', '');
        $ref = Yii::$app->request->post('ref', '');

        $module = $this->manager->getPaymentCollection('amazon_payment')->getSelectedPayment();
        if ($module){
            $module->processIPN();
        }
    }

    public function actionAmazonUpdate() {
        
        $operation = Yii::$app->request->post('operation', '');
        $ref = Yii::$app->request->post('ref', '');

        $module = $this->manager->getPaymentCollection('amazon_payment')->getSelectedPayment();

        switch ($operation) {
            case 'getOrderDetails':
                if ($ad = tep_db_fetch_array(tep_db_query("select * from amazon_payment_orders where amazon_order_id='" . tep_db_input($ref) . "'"))) {
                    $result = $module->getOrderReferenceDetails($ref);
                    if (!$module::hasError($result)) {
                        $ret = ['error' => 0];
                    } else {
                        $ret = ['error' => 1];
                        if (isset($result['Error']['Code'])) {
                            $ret['msg'] = $result['Error']['Code'] . ': ' . $result['Error']['Message'];
                        }
                    }
                }
                break;

            case 'getAuthDetails':
                if ($ad = tep_db_fetch_array(tep_db_query("select * from amazon_payment_orders where amazon_auth_id='" . tep_db_input($ref) . "'"))) {
                    $result = $module->getAuthDetails($ref);
                    if (!$module::hasError($result)) {
                        $ret = ['error' => 0];
                    } else {
                        $ret = ['error' => 1];
                        if (isset($result['Error']['Code'])) {
                            $ret['msg'] = $result['Error']['Code'] . ': ' . $result['Error']['Message'];
                        }
                    }
                }
                break;

            case 'getCaptureDetails':
                if ($ad = tep_db_fetch_array(tep_db_query("select * from amazon_payment_orders where amazon_capture_id='" . tep_db_input($ref) . "'"))) {
                    $result = $module->getCaptureDetails($ref);
                    if (!$module::hasError($result)) {
                        $ret = ['error' => 0];
                    } else {
                        $ret = ['error' => 1];
                        if (isset($result['Error']['Code'])) {
                            $ret['msg'] = $result['Error']['Code'] . ': ' . $result['Error']['Message'];
                        }
                    }
                }
                break;

            case 'getRefundDetails':
                if ($ad = tep_db_fetch_array(tep_db_query("select * from amazon_payment_orders where amazon_refund_id='" . tep_db_input($ref) . "'"))) {
                    $result = $module->getRefundDetails($ref);
                    if (!$module::hasError($result)) {
                        $ret = ['error' => 0];
                    } else {
                        $ret = ['error' => 1];
                        if (isset($result['Error']['Code'])) {
                            $ret['msg'] = $result['Error']['Code'] . ': ' . $result['Error']['Message'];
                        }
                    }
                }
                break;

            case 'close':
                if ($ad = tep_db_fetch_array(tep_db_query("select * from amazon_payment_orders where amazon_order_id='" . tep_db_input($ref) . "'"))) {
                    $result = $module->closeOrder($ref);
                    if (!$module::hasError($result)) {
                        $ret = ['error' => 0];
                    } else {
                        $ret = ['error' => 1];
                        if (isset($result['Error']['Code'])) {
                            $ret['msg'] = $result['Error']['Code'] . ': ' . $result['Error']['Message'];
                        }
                    }
                }
                break;

            case 'capture':
                if ($ad = tep_db_fetch_array(tep_db_query("select * from amazon_payment_orders where amazon_auth_id='" . tep_db_input($ref) . "'"))) {
                    $param = [];
                    
                    $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $ad['orders_id']);
                    $param['AmazonAuthorizationId'] = $ref;
                    $param['total'] = Yii::$app->request->post('amount', $order->info['total_inc_tax']);
                    $param['currency'] = $order->info['currency'];
                    $param['order_id'] = $ad['orders_id'];
                    $param['order_status'] = $order->info['order_status'];
                    $result = $module->capture($param);
                    if (!$module::hasError($result)) {
                        $ret = ['error' => 0];
                    } else {
                        $ret = ['error' => 1];
                        if (isset($result['Error']['Code'])) {
                            $ret['msg'] = $result['Error']['Code'] . ': ' . $result['Error']['Message'];
                        }
                    }
                }
                break;

            case 'refund':
                if ($ad = tep_db_fetch_array(tep_db_query("select * from amazon_payment_orders where amazon_capture_id='" . tep_db_input($ref) . "'"))) {
                    $param = [];
                    $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $ad['orders_id']);                    
                    $amount = Yii::$app->request->post('amount', $order->info['subtotal_inc_tax']);
                    $param['AmazonCaptureId'] = $ref;
                    $param['total'] = $amount;
                    $param['currency'] = $order->info['currency'];
                    $param['order_id'] = $ad['orders_id'];
                    $param['order_status'] = $order->info['order_status'];
                    if ($amount <= 0) {
                        $ret = ['error' => 1];
                    } else {
                        $result = $module->refund($param);
                        if (!$module::hasError($result)) {
                            $ret = ['error' => 0];
                        } else {
                            $ret = ['error' => 1];
                            if (isset($result['Error']['Code'])) {
                                $ret['msg'] = $result['Error']['Code'] . ': ' . $result['Error']['Message'];
                            }
                        }
                    }
                }
                break;

            default:
                $ret = ['error' => 1];
                break;
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $ret;
    }

    public function actionMultisafe(){
        $action = \Yii::$app->request->get('action');
        if ($action){
            $multisafepay = $this->manager->getPaymentCollection('multisafepay')->getSelectedPayment();
            if ($multisafepay){
                switch ($action){
                    case 'multi-notify':
                        \common\helpers\Translation::init('checkout/process');
                        $initial_request = \Yii::$app->request->get('type') == 'initial';
                        if (empty($_GET['transactionid'])) {
                            $message = "No transaction ID supplied";
                            $url = tep_href_link(
                                FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $multisafepay->code . '&error=' . urlencode($message), 'NONSSL', true, false
                            );
                        } else {

                            $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $_GET['transactionid']);
                            $order->save_products(false);
                            if ($_GET['type'] != 'shipping') {
                                //print_r($order);exit;
                            }

                            $this->manager->assignCustomer($order->customer['id']);

                            // update order status
                            /** @var multisafepay $multisafepay */
                            $multisafepay->order_id = $_GET['transactionid'];
                            $transdata = $multisafepay->check_transaction();


                            if ($multisafepay->msp->details['ewallet']['fastcheckout'] == "NO") {
                                $status = $multisafepay->checkout_notify($order);
                            } else {
                                $multisafepay = $this->manager->setSelectedPaymentModule('multisafepay_fastcheckout')->getSelectedPayment();
                                if ($multisafepay && method_exists($multisafepay, 'checkout_notify')){
                                    $status = $multisafepay->checkout_notify($order);
                                }
                            }

                            switch ($status) {
                                case "initialized":
                                case "completed":
                                    $message = "OK";
                                    $parameters = "action=success";
                                    $order->update_piad_information(true);
                                    $order->save_details();
                                    $order->save_products(false);
                                    if ($multisafepay->_customer_id) {
                                        $hash = $multisafepay->get_hash($multisafepay->order_id, $multisafepay->_customer_id);
                                        $parameters = '&customer_id=' . $multisafepay->_customer_id . '&hash=' . $hash;
                                    }
                                    $url = tep_href_link('callback/multisafe', $parameters, 'SSL');
                                    break;
                                default:
                                    $message = "OK"; //"Error #" . $status;
                                    $url = tep_href_link(
                                        FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $multisafepay->code . '&error=' . urlencode($status), 'NONSSL', true, false
                                    );
                            }
                        }

                        if ($initial_request) {
                            echo "<p><a href=\"" . $url . "\">" . sprintf(MODULE_PAYMENT_MULTISAFEPAY_TEXT_RETURN_TO_SHOP, htmlspecialchars(STORE_NAME)) . "</a></p>";
                        } else {
                            header("Content-type: text/plain");
                            echo $message;
                        }
                        break;
                    case 'cancel':

                        $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $_GET['transactionid']);
                        $order->save_products(false);
                        // update order status
                        $multisafepay->order_id = \Yii::$app->request->get('transactionid', '');
                        $transdata = $multisafepay->checkout_notify($order);

                        \common\helpers\Order::doCancel((int)$_GET['transactionid']);
                        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));

                        break;
                    case 'success':
                        Translation::init('checkout/process');
                        $orderId = (int)\Yii::$app->request->get('transactionid', 0);
                        if (Yii::$app->user->isGuest){
                            if ($_GET['multisafepay_order_id'] && $_GET['customer_id'] && $_GET['hash']) {
                                if (md5($_GET['multisafepay_order_id'] . $_GET['customer_id']) == $_GET['hash']) {
                                    $customer_id = $_GET['customer_id'];
                                    $customer = new \common\components\Customer();
                                    $customer->loadCustomer($customer_id);
                                    if ($customer->customers_id){
                                        Yii::$app->user->login($customer);
                                        $customer_id = $customer->customers_id;
                                    }
                                }
                            }
                        } else {
                            $customer_id =  Yii::$app->user->getId();
                        }
                        $this->manager->clearAfterProcess();

                        if ($customer_id) {
                            /** @var OrdersService $ordersService */
                            //$ordersService = \Yii::createObject(OrdersService::class);
                            //$orderAR =$ordersService->getById($orderId);
                            //$ordersService->changeStatus($orderAR, (int)MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED, 'MultiSafepay Successfully Initialized');
                            $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $orderId);
                            $order->save_details();
                            $order->save_products(false);
                            $order->info['order_status'] = (int)MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED;
                            \common\helpers\Order::setStatus($orderId, (int)MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED, [
                                'comments' => 'MultiSafepay Completed Pay',
                                'customer_notified' => 1,
                            ]);/**/
                            $order->notify_customer($order->getProductsHtmlForEmail(),[]);
                            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS));
                        } else {
                            tep_redirect(tep_href_link(FILENAME_DEFAULT));
                        }
                        break;
                }
            }

        }
    }

    /*
    public function actionTestApi() {
      $ess = new \common\components\google\GoogleEcommerceSS('45076');
      if (!$ess->isOrderPlacedToAnalytics()){
      $result = $ess->pushDataToAnalytics(new \common\classes\Order('45076'));
      var_dump($result);
      }
      } */
    public function actionTestApi() {
        $ess = new \common\components\GooglePrinters(GAPI_SETTINGS);      
        echo '<pre>';print_r($ess->searchPrinters('6dbdcc39-f095-d0bc-1fe2-b2af69c7a1633'));
    }
    
    public function actionReady() {
        if (!defined('SUPERADMIN_ENABLED')) {
            return;
        }
        if (SUPERADMIN_ENABLED != true) {
            return;
        }
        $key = Yii::$app->request->get('key');
        $department_query = tep_db_query("select * from departments where api_key='" . tep_db_input($key) . "'");
        if (tep_db_num_rows($department_query)) {
            tep_db_query("update departments set locked='0', departments_status='1' where api_key='" . tep_db_input($key) . "'");
            $department = tep_db_fetch_array($department_query);
            
            $name = $department['departments_firstname'] . ' ' . $department['departments_lastname'];
            $email_address = $department['departments_email_address'];
            $email_subject = 'Created new department';
            $email_text = 'Created store ' . $department['departments_https_server'];
            
            \common\helpers\Mail::send($name, $email_address, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            
        }
    }
    
    public function actionThemes() {
        $root_path = \Yii::getAlias('@webroot');
        $xml = simplexml_load_file($root_path . DIRECTORY_SEPARATOR . "setup.xml") or die("Configuration file is missing");
/*
TRUNCATE `design_backups`;
TRUNCATE `design_boxes`;
TRUNCATE `design_boxes_cache`;
TRUNCATE `design_boxes_settings`;
TRUNCATE `design_boxes_settings_tmp`;
TRUNCATE `design_boxes_tmp`;
TRUNCATE `themes_settings`;
TRUNCATE `themes_steps`;
TRUNCATE `themes_styles`;
TRUNCATE `themes_styles_cache`;
TRUNCATE `themes_styles_tmp`;
 */

        set_time_limit(0);
        // import themes
        \backend\design\Theme::import('watch', $root_path . '/uploads/watch.zip');
        \backend\design\Theme::import('furniture', $root_path . '/uploads/furniture.zip');
        \backend\design\Theme::import('printshop', $root_path . '/uploads/printshop.zip');
        \backend\design\Theme::import('deals', $root_path . '/uploads/deals.zip');
    }
}
