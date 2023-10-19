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
        if (!method_exists($this->manager, 'getCombineShippingsDefault')) { // for old projects
        Yii::configure($this->manager, [
            'combineShippings' => ((!defined('SHIPPING_SEPARATELY') || defined('SHIPPING_SEPARATELY') && SHIPPING_SEPARATELY == 'false') ? true : false),
        ]);
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
                $_tmp = $pc->getPlatformData();
                if ($_tmp['default_platform_id'] > 0 && $_tmp['default_platform_id']!=$pl['id']) {
                    $get_platform_data_r = tep_db_query("SELECT platform_url, platform_url_secure, ssl_enabled FROM ".TABLE_PLATFORMS." WHERE platform_id='" . $pl['id'] . "'");
                    if ($get_platform_data = tep_db_fetch_array($get_platform_data_r)) {
                        if (empty($get_platform_data['platform_url_secure'])) {
                            $get_platform_data['platform_url_secure'] = $get_platform_data['platform_url'];
                        }
                        $catalog_base = 'https://' . $get_platform_data['platform_url_secure'];
                        $parsed = parse_url($catalog_base);
                        $ret[] = 'http://' . $parsed['host'] . (!empty($parsed['port']) && ! in_array($parsed['port'], ['80', '443'])?':'.$parsed['port']:'');
                        $ret[] = 'https://' . $parsed['host'] . (!empty($parsed['port']) && ! in_array($parsed['port'], ['80', '443'])?':'.$parsed['port']:'');
                    }
                }

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
                    'Access-Control-Request-Headers'    => ['*'],
                    'Access-Control-Allow-Credentials' => false,
                    'Access-Control-Max-Age'           => 10,                 // Cache (seconds)
                ],
            ],

        ]);

        return $ret;
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
