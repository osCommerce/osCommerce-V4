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

namespace common\modules\orderPayment;

use common\classes\modules\ModulePayment;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;
use common\classes\modules\TransactionalInterface;
use common\classes\modules\PaymentTokensInterface;
use common\helpers\OrderPayment as OrderPaymentHelper;
use common\helpers\Html;

class sage_pay_server extends ModulePayment implements TransactionalInterface, PaymentTokensInterface, \common\classes\modules\TransactionSearchInterface {

    var $code, $title, $description, $enabled;
    private $debug = false;
    private $referrer = '0014H00004B1ikG'; //osc,  'E57C3C9C-DB7F-4EA1-9AE7-252EEBE28626' PC by holbi;
    protected $defaultTranslationArray = [
      'MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_TITLE' => 'Opayo Server',
      'MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_PUBLIC_TITLE' => 'Credit Card or Bank Card (Processed by Opayo)',
        //https://support.sagepay.com/apply/default.aspx?PartnerID=E57C3C9C-DB7F-4EA1-9AE7-252EEBE28626
      'MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_DESCRIPTION' => '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="https://referrals.elavon.co.uk/?partner_id=0014H00004B1ikG" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Opayo Website</a>',
      'MODULE_PAYMENT_SAGE_PAY_SERVER_ERROR_TITLE' => 'There has been an error processing your credit card',
      'MODULE_PAYMENT_SAGE_PAY_SERVER_ERROR_GENERAL' => 'Please try again and if problems persist, please try another payment method.'
    ];
    protected $encrypted_keys = ['MODULE_PAYMENT_SAGE_PAY_SERVER_ACCOUNT', 'MODULE_PAYMENT_SAGE_PAY_SERVER_ACCOUNT_PASSWORD'];

    public static function getVersionHistory()
    {
        return [
            '1.0.1' => 'fix missed tracking, new ref code',
        ];
    }

// class constructor
    function __construct() {
        parent::__construct();

        $this->signature = 'sage_pay|sage_pay_server|2.0|2.3';
        $this->api_version = '3.00';
    if (defined('MODULE_PAYMENT_SAGE_PAY_SERVER_API_VERSION')) {
        $this->api_version = MODULE_PAYMENT_SAGE_PAY_SERVER_API_VERSION;
    }

        $this->code = 'sage_pay_server';
        $this->title = MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_TITLE;
        $this->public_title = MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_PUBLIC_TITLE;
        $this->description = MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_DESCRIPTION;

//$this->description  .= $this->getAPIUser() . ' !!' . $this->getAPIPassword();

        if (!defined('MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS')) {
            $this->enabled = false;
            return;
        }
        $this->sort_order = MODULE_PAYMENT_SAGE_PAY_SERVER_SORT_ORDER;
        $this->enabled = ((MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS == 'True') ? true : false);
        $this->online = true;

// {{
//      if (IS_TRADE_SITE == 'True') $this->enabled = false;
// }}

        if ((int) MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID > 0) {
            if (!defined('MODULE_PAYMENT_SAGE_PAY_SERVER_T3M_VERIFICATION') || MODULE_PAYMENT_SAGE_PAY_SERVER_T3M_VERIFICATION != 'Required') {
                $this->order_status = MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID;
            }
        }

        $this->update_status();
    }

// class methods
    function update_status() {

        if (($this->enabled == true) && ((int) MODULE_PAYMENT_SAGE_PAY_SERVER_ZONE > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_SAGE_PAY_SERVER_ZONE . "' and zone_country_id = '" . $this->billing['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $this->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
    }

    public function updateTitle($platformId = 0) {
        $mode = $this->get_config_key((int) $platformId, 'MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER');
        if ($mode !== false) {
            $mode = strtolower($mode);
            $title = (defined('MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_TITLE') ? constant('MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_TITLE') : '');
            if ($title != '') {
                $this->title = $title;
                if ($mode == 'test') {
                    $this->title .= ' [Test]';
                } elseif ($mode == 'simulator') {
                    $this->title .= ' [Simulator]';
                }
            }
            $titlePublic = (defined('MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_PUBLIC_TITLE') ? constant('MODULE_PAYMENT_SAGE_PAY_SERVER_TEXT_PUBLIC_TITLE') : '');
            if ($titlePublic != '') {
                $this->public_title = $titlePublic;
                if ($mode == 'test') {
                    $this->public_title .= " [{$this->code}; Test]";
                } elseif ($mode == 'simulator') {
                    $this->public_title .= " [{$this->code}; Simulator]";
                }
            }
            return true;
        }
        return false;
    }

    function javascript_validation() {
        return false;
    }

    function selection() {
        $this->manager->remove('ptoken');
        $this->manager->remove('use_token');
        $selection = array('id' => $this->code,
          'module' => $this->public_title);

        $fields = $this->renderTokenSelection((int) $this->manager->getCustomerAssigned());
        if (!empty($fields)) {
            $selection ['fields'] = $fields;
        }
        if ($this->popUpMode()) {
            if (isset($selection ['fields'][0]['title'])) {
                $selection ['fields'][0]['title'] .= $this->tlPopupJS();
            } else {
                $selection ['fields'][] = ['title' => $this->tlPopupJS()];
            }
        }
        return $selection;
    }

    protected function tlPopupJS(): string {
    $this->registerCallback("popUpIframe{$this->code}");
        \Yii::$app->getView()->registerJs(parent::tlPopupJS());
        return '';
    }

    function pre_confirmation_check() {
        $order = $this->manager->getOrderInstance();
        //\Yii::$app->request->post()
        if (!empty($_POST[$this->code . 'ptoken']) && $this->checkToken((int) $this->manager->getCustomerAssigned(), $_POST[$this->code . 'ptoken'])) {
            //$order->info['use_token'] = isset($_POST['use_token'])?$_POST['use_token']:false;
            //$_SESSION['use_token'] = $_POST['ptoken'];
            $this->manager->set('ptoken', $_POST[$this->code . 'ptoken']);
        } elseif (!empty($_POST[$this->code . 'use_token'])) {
            //$order->info['use_token'] = isset($_POST['use_token'])?$_POST['use_token']:false;
            //$_SESSION['use_token'] = isset($_POST['use_token'])?$_POST['use_token']:false;
            $this->manager->set('use_token', $_POST[$this->code . 'use_token']);
        }
        if (!empty($_POST['set_default_token'])) {
            $this->manager->set('update_default_token', $_POST['set_default_token']);
            $this->saveToken((int) $this->manager->getCustomerAssigned(),
                [
                  'old_payment_token' => $this->manager->get('ptoken'),
                  'token' => $this->manager->get('ptoken')
            ]);
        }

        return true;
    }

    function confirmation() {
        return false;
        if ($this->isWithoutConfirmation()) {
            return false;
        }
        //no popup, never !!!! return ['title' => $this->tlPopupJS()];
    }

    function process_button() {
        return false;
    }

  /**
   * Validate vpsSignature in POST against saved in session
   */
  public function validateResponse() {
    $ret = false;
    $sig_string = '';
    $post = \Yii::$app->request->post();
    if ($this->api_version == '3.00') {
        $keys = [
              'VPSTxId',
              'VendorTxCode',
              'Status',
              'TxAuthNo',
              'VendorName',
              'AVSCV2',
              'SecurityKey',
              'AddressResult',
              'PostCodeResult',
              'CV2Result',
              'GiftAid',
              '3DSecureStatus',
              'CAVV',
              'AddressStatus',
              'PayerStatus',
              'CardType',
              'Last4Digits',
              'DeclineCode',
              'ExpiryDate',
              'FraudResponse',
              'BankAuthCode',
            ];
    } else {
        $keys = [
              'VPSTxId',
              'VendorTxCode',
              'Status',
              'TxAuthNo',
              'VendorName',
              'AVSCV2',
              'SecurityKey',
              'AddressResult',
              'PostCodeResult',
              'CV2Result',
              'GiftAid',
              '3DSecureStatus',
              'CAVV',
              'AddressStatus',
              'PayerStatus',
              'CardType',
              'Last4Digits',
              'DeclineCode',
              'ExpiryDate',
              'FraudResponse',
              'BankAuthCode',
              'ACSTransID',
              'DSTransID',
              'SchemeTraceID',
            ];
    }
    foreach ($keys as $key) {
            if ($key == 'VendorName') {
                // Please ensure the VendorName is lower case prior to hashing.
                $sig_string .= strtolower(substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15));
            } elseif ($key == 'SecurityKey') {
                $sig_string .= $this->manager->get('sage_pay_server_securitykey');
            } elseif (isset($post[$key])) {
                // If a field is returned without a value this should not be included in the string.
                $sig_string .= $post[$key];
            }
        }
        if (isset($post['VPSSignature']) && ($post['VPSSignature'] == strtoupper(md5($sig_string)))) {
            //MD5 value is returned in UPPER CASE.
            $ret = true;
        }

        return $ret;
    }

    public function safeServer() {

        $post = \Yii::$app->request->post();
        if ($this->validateResponse()) {
            $paymentStatus = \Yii::$app->request->post('Status', false);

            if (!in_array($paymentStatus, ['OK', 'AUTHENTICATED', 'REGISTERED'])) {
                //payment error
                $this->manager->remove('sage_pay_server_securitykey');
                $this->manager->remove('sage_pay_server_nexturl');
                $this->manager->remove('sage_pay_server_tmp_order');
                //cancell?? $this->manager->remove('sage_pay_server_order_before');

                $error = \Yii::$app->request->post('StatusDetail', false);

                $error_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : '') . '&' . tep_session_name() . '=' . tep_session_id(), 'SSL', false);
                if ($this->popUpMode()) {
                    $error_url = \Yii::$app->urlManager->createAbsoluteUrl(['callback/webhooks', 'set' => 'payment', 'action' => 'error', 'module' => $this->code, 'redirect' => $error_url]);
                }

                $result = 'Status=OK' . chr(13) . chr(10) . 'RedirectURL=' . $error_url;
            } else {
                //payment OK
                if ($this->debug) {
                    \Yii::warning(print_r(\Yii::$app->request->post(), 1), 'SAGEPAY_SERVER_RESPONSE');
                }

                $sage_pay_server_additional_info = $post;
                $sage_pay_server_additional_info['SecurityKey'] = $this->manager->get('sage_pay_server_securitykey');

                $token = \Yii::$app->request->post('Token', false);
                if (!empty($token)) {
                    $expDate = \Yii::$app->request->post('ExpiryDate');
                    if (strlen($expDate) == 4) {
                        $expYear = '20' . substr($expDate, 2);
                        $expMonth = substr($expDate, 0, 2);
                    }
                    $this->saveToken((int) $this->manager->getCustomerAssigned(),
                        [
                          'token' => $token,
                          'cardType' => \Yii::$app->request->post('CardType', ''),
                          'lastDigits' => \Yii::$app->request->post('Last4Digits', ''),
                          'expDate' => (!empty($expMonth) ? date('Y-m-t', mktime(23, 59, 59, intval($expMonth), 1, $expYear)) : '')
                    ]);
                }

                $this->manager->set('sage_pay_server_additional_info', $sage_pay_server_additional_info);
                $params = [
                  'check' => 'PROCESS',
                  'key' => md5($this->manager->get('sage_pay_server_securitykey')),
                  tep_session_name() => tep_session_id()
                ];
                if ($this->manager->has('pay_order_id') && is_numeric($this->manager->get('pay_order_id'))) {
                    $params['order_id'] = $this->manager->get('pay_order_id');
                }

                $result = 'Status=OK' . chr(13) . chr(10) .
                    'RedirectURL=' . $this->getCheckoutUrl($params, self::PROCESS_PAGE);
            }
        } else {
            $this->manager->remove('sage_pay_server_securitykey');
            $this->manager->remove('sage_pay_server_nexturl');
            $this->manager->remove('sage_pay_server_tmp_order');
            //cancel ?? 
            $this->manager->remove('sage_pay_server_order_before');

            $error = $post['StatusDetail'];

            $error_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : '') . '&' . tep_session_name() . '=' . tep_session_id(), 'SSL', false);

            if ($this->popUpMode()) {
                $error_url = \Yii::$app->urlManager->createAbsoluteUrl(['callback/webhooks', 'set' => 'payment', 'action' => 'error', 'module' => $this->code, 'redirect' => $error_url]);
            }

            $result = 'Status=INVALID' . chr(13) . chr(10) . 'RedirectURL=' . $error_url;
        }

        echo $result;
        exit;
    }

    public function isPartlyPaid() {
        $ret = parent::isPartlyPaid() || \Yii::$app->request->get('partlypaid');
        if ($ret && $this->manager->has('pay_order_id') && is_numeric($this->manager->get('pay_order_id'))) {
            if ($this->manager->isInstance()) {
                $order = $this->manager->getOrderInstance();
                $order->order_id = $this->manager->get('pay_order_id');
            } else {
                $this->manager->getOrderInstanceWithId('\common\classes\Order', $this->manager->get('pay_order_id'));
            }
        }
        return $ret;
    }

    function before_process() {

        if ($this->debug) {
//            \Yii::warning(print_r(\Yii::$app->request->post(), 1), 'SAGEPAY_RESPONSE POST');
        }

        $error = null;
        $order = $this->manager->getOrderInstance();
        $customer_id = $this->manager->getCustomerAssigned();
        $cartID = (string) $this->manager->get('cartID');

        $mode = \Yii::$app->request->get('check', false);

        if ($mode == 'SERVER') {
            //step2 confirm (acknowledge) payment notification with redirect URL
            $this->safeServer();//interupts with exit()

        } elseif ($mode == 'PROCESS') {
            //step3 redirect URL OK
            if (\Yii::$app->request->get('key', false) == md5($this->manager->get('sage_pay_server_securitykey'))) {
                $this->manager->remove('sage_pay_server_securitykey');
                $this->manager->remove('sage_pay_server_nexturl');
                $orderClass = $this->saveOrderBefore();
                if ( ($orderClass=='TmpOrder' && $this->manager->has('sage_pay_server_tmp_order') && !empty($this->manager->get('sage_pay_server_tmp_order')))
                     || ($orderClass=='Order'&& $this->manager->has('sage_pay_server_order_before') && !empty($this->manager->get('sage_pay_server_order_before')))
                    ) {

                    //order either saved  or should be saved from TMP
                    if ($orderClass=='TmpOrder') {
                        //create from temp
                        $tmpOid = substr($this->manager->get('sage_pay_server_tmp_order'), 3);
                        /** @var \common\classes\TmpOrder $order */
                        $order = $this->manager->getParentToInstanceWithId('\common\classes\TmpOrder', $tmpOid);
                        if ($order) {
                            if ($this->order_status) {
                                $order->info['order_status'] = $this->order_status;
                            }
                            $order->save_order($tmpOid);
                            $orderId = $order->createOrder();
                            if (!$orderId) {
                                \Yii::warning("tmporder is incorrect or processed $tmpOid", 'TLDEBUG_' . $this->code);
                                return true; //create new order from cart
                            }
                        } else {
                            \Yii::warning("tmporder is incorrect $tmpOid", 'TLDEBUG_' . $this->code);
                            return true; //create new order from cart
                        }
                    }
                    else {
                        //update status
                        $oId = $this->manager->get('sage_pay_server_order_before');
                        //$oId = substr($this->manager->get('sage_pay_server_order_before'), 1);
                        /** @var \common\classes\Order $order */
                        $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $oId);
                        if ($order) {
                            if ($this->order_status) {
                                \common\helpers\Order::setStatus($oId, $this->order_status);
                                $order->info['order_status'] = $this->order_status;
                            }
//                            $order->info['comments'] = $pp_result;

                            $order->update_piad_information(true);

                            $order->save_details();
                            $order->info['comments'] = '';

                            $order->notify_customer($order->getProductsHtmlForEmail(),[]);
                            $this->trackCredits();
                        } else {
                            \Yii::warning("order is incorrect $oId", 'TLDEBUG_' . $this->code);
                            return true;
                        }

                    }
                    $this->after_process();//interrupts with exit;

                }

                //save order in checkoutController
                return true;
            }
        } else {
            //step1 init payment
            $partlyPaid = '';
            $_amount = $this->formatRaw(($order->info['total_inc_tax'] ? $order->info['total_inc_tax'] : $order->info['total']));
            $_cur = \Yii::$app->settings->get('currency');

            $VendorData = '';
            $transId = false;
            if ($this->isPartlyPaid()) {
                $partlyPaid = '&partlypaid=1';
                if ($order->info['currency'] != $_cur) {
                    $_cur = $order->info['currency'];
                    $_amount = $this->formatRaw(($order->info['total_inc_tax'] ? $order->info['total_inc_tax'] : $order->info['total']), $_cur, $order->info['currency_value']);
                }
                $VendorData = 'N' . $order->info['orders_id'];
                $transId = $order->info['orders_id'];
            } else {
                $tmp = $this->saveOrderBySettings();
                if (empty($tmp) || substr($tmp, 0, 3) == 'tmp') {
                    $VendorData = 'exp' . $this->estimateOrderId() . ' ' . $tmp;
                } else {
                    $VendorData = 'N' . $tmp;
                    $transId = $tmp;
                }
            }
      $params = array('VPSProtocol' => $this->api_version,
        'ReferrerID' => $this->referrer,
        'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15),
        'VendorTxCode' => substr(date('YmdHis') . '-' . ($transId ? $transId : $customer_id) . '-' . $cartID, 0, 40),
        'Amount' => $_amount,
        'Currency' => $_cur,
        'Description' => substr(STORE_NAME, 0, 100),
        'NotificationURL' => tep_href_link('callback/sage-server', 'check=SERVER&' . tep_session_name() . '=' . tep_session_id() . $partlyPaid, 'SSL', false),
        'BillingSurname' => substr($order->billing['lastname'], 0, 20),
        'BillingFirstnames' => substr($order->billing['firstname'], 0, 20),
        'BillingAddress1' => substr($order->billing['street_address'], 0, ($this->api_version == '3.00'?100:50)),
        'BillingAddress2' => substr(trim(substr($order->billing['street_address'], ($this->api_version == '3.00'?100:50)) . ' ' . $order->billing['suburb']??''), 0, ($this->api_version == '3.00'?100:50)),
        'BillingAddress3' => substr(trim(substr( trim(substr($order->billing['street_address'], ($this->api_version == '3.00'?100:50)) . ' ' . $order->billing['suburb']??''), ($this->api_version == '3.00'?100:50))), 0, ($this->api_version == '3.00'?100:50)),

        'BillingCity' => substr($order->billing['city'], 0, 40),
        'BillingPostCode' => substr($order->billing['postcode'], 0, 10),
        'BillingCountry' => $order->billing['country']['iso_code_2'],
        'BillingPhone' => substr($order->customer['telephone'], 0, 20),
        'DeliverySurname' => substr($order->delivery['lastname'], 0, 20),
        'DeliveryFirstnames' => substr($order->delivery['firstname'], 0, 20),
        'DeliveryAddress1' => substr($order->delivery['street_address'], 0, ($this->api_version == '3.00'?100:50)),
        'DeliveryAddress2' => substr(trim(substr($order->delivery['street_address'], ($this->api_version == '3.00'?100:50)) . ' ' . $order->delivery['suburb']??''), 0, ($this->api_version == '3.00'?100:50)),
        'DeliveryAddress3' => substr(trim(substr( trim(substr($order->delivery['street_address'], ($this->api_version == '3.00'?100:50)) . ' ' . $order->delivery['suburb']??''), ($this->api_version == '3.00'?100:50))), 0, ($this->api_version == '3.00'?100:50)),
        'DeliveryCity' => substr($order->delivery['city'], 0, 40),
        'DeliveryPostCode' => substr($order->delivery['postcode'], 0, 10),
        'DeliveryCountry' => @$order->delivery['country']['iso_code_2'],
        'DeliveryPhone' => substr($order->customer['telephone'], 0, 20),
        'CustomerEMail' => substr($order->customer['email_address'], 0, ($this->api_version == '3.00'?80:255)),
        //'ApplyAVSCV2' => '2',
        'Apply3DSecure' => '0');
        if (!empty($VendorData)) {
            $params['VendorData'] = $VendorData;
        }
      $ip_address = \common\helpers\System::get_ip_address();
      if ($this->manager->has('ptoken') && !empty($this->manager->get('ptoken'))) {
        $params['Token'] = $this->manager->get('ptoken');
        $params['StoreToken'] = 1;
        if ($this->api_version != '3.00') {
            $params['COFUsage'] = 'SUBSEQUENT';
            $params['InitiatedType'] = 'CIT';
        }

        if (defined('MODULE_PAYMENT_SAGE_PAY_3DS_SKIP') && (float) MODULE_PAYMENT_SAGE_PAY_3DS_SKIP >= $params['Amount']) {
          $params['Apply3DSecure'] = '2';
          if ($this->api_version != '3.00') {
            $params['ThreeDSExemptionIndicator'] = '01';
            /*
                01 = Low Value Transaction (LVT)
                02 = TRA exemption
                03 = Trusted beneficiaries exemption
                04 = Secure corporate payment
                05 = Delegated authentication
                06 – 99 Reserved for future use
             */
          }
        }
      }

            if ($this->onBehalf()) {
                $params['Apply3DSecure'] = '2';
                $params['AccountType'] = 'M'; // by default 'E' so not set.
            }

      if ($this->manager->has('use_token') && !empty($this->manager->get('use_token'))) {
        $params['CreateToken'] = 1;
        if ($this->api_version != '3.00'
             && (!$this->manager->has('ptoken') || empty($this->manager->get('ptoken')))  ) {
            $params['COFUsage'] = 'FIRST';
            $params['InitiatedType'] = 'CIT';
        }
      }

            if ((ip2long($ip_address) != -1) && (ip2long($ip_address) != false)) {
                $params['ClientIPAddress'] = $ip_address;
            }

            if (MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_METHOD == 'Payment') {
                $params['TxType'] = 'PAYMENT';
            } elseif (MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_METHOD == 'Deferred') {
                $params['TxType'] = 'DEFERRED';
            } else {
                $params['TxType'] = 'AUTHENTICATE';
            }

            if ($params['BillingCountry'] == 'US') {
                $params['BillingState'] = \common\helpers\Zones::get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], '');
            }

            if ($params['DeliveryCountry'] == 'US') {
                $params['DeliveryState'] = \common\helpers\Zones::get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], '');
            }

            /* doesn't work now as no separate page/popup mode - only different layout of sage pay page - 1 step with card details only. */
            if (MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE != 'Normal') {
                $params['Profile'] = 'LOW';
            }
            /*         */

            $contents = array();

            foreach ($order->products as $product) {
                $product_name = $product['name'];

                if (isset($product['attributes'])) {
                    foreach ($product['attributes'] as $att) {
                        $product_name .= '; ' . $att['option'] . '=' . $att['value'];
                    }
                }

                $contents[] = $this->filterNonXmlItemName($product_name) . ':' . $product['qty'] . ':' . $this->formatRaw($product['final_price']) . ':' . $this->formatRaw(($product['tax'] / 100) * $product['final_price']) . ':' . $this->formatRaw((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) . ':' . $this->formatRaw(((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) * $product['qty']);
            }

            $order_totals = ($order->totals && $order->order_id ? $order->totals : $this->manager->getTotalOutput(true, 'TEXT_CHECKOUT'));
            foreach ($order_totals as $ot) {
                $contents[] = $this->filterNonXmlItemName(strip_tags($ot['title'])) . ':---:---:---:---:' . $this->formatRaw($ot['value']);
            }

            //$params['Basket'] = substr(sizeof($contents) . ':' . implode(':', $contents), 0, 7500);
            $params['Basket'] = sizeof($contents) . ':' . implode(':', $contents);
            if (strlen($params['Basket']) > 7500) {
                // for big basket need smart cut, otherwise we get error "3021 : The Basket format is invalid."
                // Basket string cut inside separators and first value (rows count) not recalculated
                // currently post simple totals
                $minimal_content = [];
                foreach ($order_totals as $ot) {
                    $minimal_content[] = $this->filterNonXmlItemName(strip_tags($ot['title'])) . ':---:---:---:---:' . $this->formatRaw($ot['value']);
                }
                $params['Basket'] = sizeof($minimal_content) . ':' . implode(':', $minimal_content);
            }

            $return = $this->prepareSendRequest('transaction', $params);

            if (!empty($return['Status']) && $return['Status'] == 'OK') {
                $this->manager->set('sage_pay_server_securitykey', $return['SecurityKey']);
                $this->manager->set('sage_pay_server_nexturl', $return['NextURL']);

                tep_redirect($return['NextURL']);

                /* if ( MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE == 'Normal' ) {
                  tep_redirect($return['NextURL']);
                  } else {
                  tep_redirect(tep_href_link('checkout_sage_pay.php', '', 'SSL'));
                  } */
            } else {
                $error = $return['StatusDetail']['description'];
            }
        }

        $this->manager->remove('sage_pay_server_securitykey');
        $this->manager->remove('sage_pay_server_nexturl');

        $error_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : ''), 'SSL');
        if ($this->popUpMode()) {
            $result = <<<EOD
                <html><body style="text-align:center">
                    <script>window.top.location.href = '{$error_url}';</script>
                    <a href="{$error_url}" target="_top">Click here if form is not redirected automatically.</a>
                    </body></html>
EOD;
            echo $result;
            exit;
        } else
            tep_redirect($error_url);
    }

    function after_process() {

        $response = $this->manager->get('sage_pay_server_additional_info');
        /*
          [VPSProtocol] => 3.00
          [TxType] => PAYMENT
          [VendorTxCode] => 20190812140709-532-70277
          [VPSTxId] => {015B3E87-0692-4DE9-36B8-711CD784B749}
          [Status] => OK
          [StatusDetail] => 0000 : The Authorisation was Successful.
          [TxAuthNo] => 2147027
          [AVSCV2] => SECURITY CODE MATCH ONLY
          [AddressResult] => NOTMATCHED
          [PostCodeResult] => NOTMATCHED
          [CV2Result] => MATCHED
          [GiftAid] => 0
          [3DSecureStatus] => NOTCHECKED
          [CardType] => AMEX
          [Last4Digits] => 0004
          [VPSSignature] => 1C36F8E702B99C28CB16AFEF1AE389DD
          [DeclineCode] => 00
          [ExpiryDate] => 1222
          [Token] => {3C533492-2F60-6956-F1C7-53BFBA401945}
          [BankAuthCode] => 99972
         */
        $this->manager->remove('sage_pay_server_additional_info');
        $this->manager->remove('ptoken');
        $this->manager->remove('use_token');

        $this->manager->clearAfterProcess();

        $order = $this->manager->getOrderInstance();

        $response['orderId'] = $response['VPSTxId'];
        $trans = $this->getTransactionDetails($response['VPSTxId'], null);
        $comment = $oComment = '';
        if (is_array($trans)) {
            $statusCode = $this->getStatusCode(array_merge($response, $trans));
            /*
              [transactiontype] => Payment
              [status] => Successfully authorised transaction.
              [amount] => 83.40
              [currency] => GBP
              [started] => 14/08/2020 18:55:29.837
              [completed] => 14/08/2020 18:57:34.397
              [paymentsystem] => MC
              [expirydate] => 0424
              [last4digits] => 8241
              [refunded] => NO
              [repeated] => NO
              [cv2result] => MATCHED
              [addressresult] => NOTCHECKED
              [postcoderesult] => NOTCHECKED
              [threedresult] => OK
              [t3mscore] => -2
              [t3maction] => OK
             */
            foreach (
                        array_intersect_key(
                            array_merge(array_change_key_case($response, CASE_LOWER), $trans),
                            array_flip([
                                'transactiontype', 'status', 'amount', 'currency',
                                'cv2result', 'addressresult', 'postcoderesult', 'threedresult',
                                't3mscore', 't3maction',
                                'paymentsystem'
                        ])) as $k => $v) {
                $oComment .= "$k: $v; \n";
            }
            $comment = $oComment;
            foreach (
                    array_intersect_key(
                            array_merge(array_change_key_case($response, CASE_LOWER), $trans),
                            array_flip([
                                'expirydate', 'last4digits',
                                'started', 'completed',
                                'refunded', 'repeated'
                    ])) as $k => $v) {
                $comment .= "$k: $v; \n";
            }
            $response = array_merge($response, $trans);
        } else {
            $statusCode = $this->getStatusCode($response);
            $response['amount'] = $order->info['total']; // suppose pay in full at once
            foreach (
                        array_intersect_key(
                            $response,
                            array_flip([
                                'TxType', 'Status', 'StatusDetail',
                                'AVSCV2', 'AddressResult', 'PostCodeResult', 'CV2Result', '3DSecureStatus',
                                'CardType'
                        ])) as $k => $v) {
                $oComment .= "$k: $v; \n";
            }
            $comment = $oComment;
        }

        //{{ transactions
        /** @var \common\services\PaymentTransactionManager $tManager */
        $tManager = $this->manager->getTransactionManager($this);
        $invoice_id = $this->manager->getOrderSplitter()->getInvoiceId();

        if ($this->debug) {
            \Yii::warning('id ' . trim($response['orderId'], '{}') . ' ' . print_r([
                  'fulljson' => json_encode($response),
                  'status_code' => $statusCode,
                  'status' => (!empty($response['Status']) ? $response['Status'] : (!empty($response['status']) ? $response['status'] : '')),
                  'amount' => (float) $response['amount'],
                  'comments' => $comment,
                  //'date' => date('Y-m-d H:i:s', strtotime($response['started'])),
                  'date' => date('Y-m-d H:i:s'),
                  'suborder_id' => $invoice_id,
                  'orders_id' => $order->order_id,
                    ], 1), 'SAGEPAY_SERVER_TRANSACTION_DETAILS');
        }
        $deferred = 0;
        if (
            ( !empty($response['TxType']) && in_array($response['TxType'], ['DEFERRED', 'AUTHENTICATE'])) ||
            ( !empty($response['transactiontype']) && in_array($response['transactiontype'], ['Deferred', 'Authenticate'])) ||
            ( !empty($response['txstateid']) && in_array($response['txstateid'], [14, 15])) //28
           ){
            $deferred = 1;
        }

        if (!empty($oComment) && !empty($order->order_id)) {
            try {
                $m = new \common\models\OrdersStatusHistory();
                $m->loadDefaultValues();
                $m->setAttributes([
                  'orders_id' => $order->order_id,
                  'orders_status_id' => $order->info['order_status'],
                  'comments' => $oComment,
                  'date_added' => date(\common\helpers\Date::DATABASE_DATETIME_FORMAT),
                ], false);
                $m->save(false);
            } catch (\Exception $ex) {
                \Yii::warning(print_r($ex->getMessage(), true), 'TLDEBUG_' . $this->code);
            }
        }
        $ret = $tManager->updatePaymentTransaction(trim($response['orderId'], '{}'),
            [
              'fulljson' => json_encode($response),
              'status_code' => $statusCode,
              'status' => (!empty($response['Status']) ? $response['Status'] : (!empty($response['status']) ? $response['status'] : '')),
              'amount' => (float) $response['amount'],
              'comments' => $comment,
              //'date' => date('Y-m-d H:i:s', strtotime($response['started'])),
              'date' => date('Y-m-d H:i:s'),
              'suborder_id' => $invoice_id,
              'orders_id' => $order->order_id,
              'deferred' => $deferred,
            // parent_transaction_id orders_id
        ]);
        if ($this->debug) {
            \Yii::warning(var_export($ret, true));
        }

        $this->no_process_after($order, false);

        if ($this->onBehalf()) {
            \Yii::$app->settings->set('from_admin', false);
            if (!\Yii::$app->user->isGuest) {
                \Yii::$app->user->getIdentity()->logoffCustomer();
            }
            echo TEXT_ON_BEHALF_PAYMENT_SUCCESSFUL;
            die;
        }

        tep_redirect(tep_href_link('callback/redirect-by-js', '', 'SSL')); // JS redirect
    }

    protected function filterNonXmlItemName($name) {
        $standardChars = '0-9a-zA-Z';
        $allowedSpecialChars = " +'/\\,.-{};_@()^\"~$=!#?|[]";
        $pattern = '`[^' . $standardChars . preg_quote($allowedSpecialChars, '/') . ']`';
        $name = trim(substr(preg_replace($pattern, '', html_entity_decode($name)), 0, 100));

        return $name;
    }

    function get_error() {

        $error = \Yii::$app->request->get('error', '');
        $message = \Yii::$app->request->get('message', '');

        if (!empty($message)) {
            $error = stripslashes(urldecode($message));
        } else {
            $error = stripslashes(urldecode($error));
        }

        $error = array('title' => MODULE_PAYMENT_SAGE_PAY_SERVER_ERROR_TITLE,
          'error' => MODULE_PAYMENT_SAGE_PAY_SERVER_ERROR_GENERAL . ' ' . strip_tags($error));

        return $error;
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS', 'True', 'False');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_PAYMENT_SAGE_PAY_SERVER_SORT_ORDER');
    }

    public function configure_keys() {
        $status_id = defined('MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID') ? MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID : $this->getDefaultOrderStatusId();

    return array(
      'MODULE_PAYMENT_SAGE_PAY_SERVER_STATUS' => array(
        'title' => 'Enable Sage Pay Server Module',
        'value' => 'False',
        'description' => 'Do you want to accept Sage Pay Server payments?',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME' => array(
        'title' => 'Vendor Login Name',
        'value' => '',
        'description' => 'The vendor login name to connect to the gateway with.',
        'sort_order' => '0',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_ACCOUNT' => array(
        'title' => 'Account login',
        'value' => '',
        'description' => 'Account login to get transaction details',
        'sort_order' => '0',
        'use_function' => '\\common\\modules\\orderPayment\\sage_pay_server::useConf',
        'set_function' => 'setConf(',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_ACCOUNT_PASSWORD' => array(
        'title' => 'Account Password',
        'value' => '',
        'description' => 'Account password to get transaction details',
        'sort_order' => '0',
        'use_function' => '\\common\\modules\\orderPayment\\sage_pay_server::useConf',
        'set_function' => 'setConf(',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_API_VERSION' => array(
        'title' => 'API Version',
        'value' => '3.00',
        'description' => '3DS version 2 (SCA) requires API 4.00',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'4.00\', \'3.00\'), ',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE' => array(
        'title' => 'Profile Payment Page',
        'value' => 'Normal',
        'description' => 'Profile page to use for the payment page.',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Normal\', \'Low\'), ',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_METHOD' => array(
        'title' => 'Transaction Method',
        'value' => 'Authenticate',
        'description' => 'The processing method to use for each transaction.',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Authenticate\', \'Deferred\', \'Payment\'), ',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER' => array(
        'title' => 'Transaction Server',
        'value' => 'Simulator',
        'description' => 'Perform transactions on the production server or on the testing server.',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Live\', \'Test\', \'Simulator\'), ',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_T3M_VERIFICATION' => array(
        'title' => 'T3M verification',
        'value' => 'Info',
        'description' => 'Info - ignore if not available instantly, Optional - check for updates for up to 3 days, required - do not accept transaction if none t3m info available',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'Info\', \'Optional\', \'Required\'), ',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_USE_TOKENS' => array(
        'title' => 'Allow tokens',
        'value' => 'False',
        'description' => 'Allow to save tokens.',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_ZONE' => array(
        'title' => 'Payment Zone',
        'value' => '0',
        'description' => 'If a zone is selected, only enable this payment method for that zone.',
        'sort_order' => '2',
        'use_function' => '\\common\\helpers\\Zones::get_zone_class_title',
        'set_function' => 'tep_cfg_pull_down_zone_classes(',
      ),
      'MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_STATUS_ID' => array(
        'title' => 'Set Order Status',
        'value' => $status_id,
        'description' => 'Set the status of orders made with this payment module to this value',
        'sort_order' => '0',
        'set_function' => 'tep_cfg_pull_down_order_statuses(',
        'use_function' => '\\common\\helpers\\Order::get_order_status_name',
      ),
          'MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_BEFORE_PAYMENT' => array(
            'title' => 'Save order before payment',
            'value' => 'False',
            'description' => 'Save order before redirect to payment gateway',
            'sort_order' => '0',
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\', \'Temp\'), ',
          ),
          'MODULE_PAYMENT_SAGE_PAY_3DS_SKIP' => array(
            'title' => 'Skip 3D secure amount',
            'value' => '',
            'description' => 'Skip 3D secure verification when paid by token on orders below',
            'sort_order' => '100',
          ),
          /* 'MODULE_PAYMENT_SAGE_PAY_SERVER_CURL' => array(
            'title' => 'cURL Program Location',
            'value' => '/usr/bin/curl',
            'description' => 'The location to the cURL program application.',
            'sort_order' => '0',
            ), */
          'MODULE_PAYMENT_SAGE_PAY_SERVER_SORT_ORDER' => array(
            'title' => 'Sort order of display.',
            'value' => '0',
            'description' => 'Sort order of display. Lowest is displayed first.',
            'sort_order' => '0',
          ),
        );
    }

    function isOnline() {
        return true;
    }

    /**
     * checks whether the module supports token system and tokens allowed on the site.
     * @return bool
     */
    public function hasToken(): bool {
        return true && parent::tokenAllowed();
    }

    /**
     * checks whether the module hasToken and its enabled on the module.
     * @return bool
     */
    public function useToken(): bool {
        return ($this->hasToken() && defined('MODULE_PAYMENT_SAGE_PAY_SERVER_USE_TOKENS') && MODULE_PAYMENT_SAGE_PAY_SERVER_USE_TOKENS == 'True');
    }

    /**
     * parse array of strings [ 0 => 'k1=v1', 1=>''] to associative array [k1=>v1, k2=>v2]
     * @param array $res
     * @return array associative array  [k1=>v1, k2=>v2]
     */
    private function parseResponce($res) {
        $ret = [];
        if (!is_array($res)) {
            $res = [$res];
        }
        foreach ($res as $string) {
            if (strpos($string, '=') != false) {
                $parts = explode('=', $string, 2);
                $key = trim($parts[0]);
                if ($key == 'StatusDetail') {
                    $val = [
                      'code' => trim(substr($parts[1], 0, strpos($parts[1], ':'))),
                      'description' => trim($parts[1])
                    ];
                } else {
                    $val = trim($parts[1]);
                }
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    /**
     *
     * @param string $action transaction | token | remove-token |token-remove | refund |void
     * @param string $mode Live | Test else Simulator
     * @return string gateway URL
     */
    private function getApiUrl($action, $mode) {
        $gateway_url = '';
        switch ($mode) {
            case 'Live':
                $gateway_url = 'https://live.sagepay.com';
                break;

            case 'Test':
                $gateway_url = 'https://test.sagepay.com';
                break;

            default:
                $gateway_url = 'https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRegisterTx';
                $action = '';
                break;
        }

        $action = strtolower($action);

        switch ($action) {
            case 'transaction':
                $gateway_url .= '/gateway/service/vspserver-register.vsp';
                break;
            case 'token':
                $gateway_url .= '/gateway/service/token.vsp';
                break;
            case 'refund':
                $gateway_url .= '/gateway/service/refund.vsp';
                break;
            case 'repeat':
                $gateway_url .= '/gateway/service/repeat.vsp';
                break;
            case 'manualpayment':
            case 'manual-payment':
            case 'manual':
                $gateway_url .= '/gateway/service/manualpayment.vsp';
                break;
            case 'directrefund':
                $gateway_url .= '/gateway/service/directrefund.vsp';
                break;
            case 'release':
                $gateway_url .= '/gateway/service/release.vsp';
                break;
            case 'abort':
                $gateway_url .= '/gateway/service/abort.vsp';
                break;
            case 'authorise':
                $gateway_url .= '/gateway/service/authorise.vsp';
                break;
            case 'void':
                $gateway_url .= '/gateway/service/void.vsp';
                break;
            case 'cancel':
                $gateway_url .= '/gateway/service/cancel.vsp';
                break;
            case 'status':
                $gateway_url .= '/access/access.htm';
                break;
            case 'token-remove':
            case 'remove-token':
                $gateway_url .= '/gateway/service/removetoken.vsp';
                break;
        }

        return $gateway_url;
    }

    /**
     * Note it doesn't try to delete token at gateway again in case of any error
     * @param int $customersId
     * @param string  $token
     * @return int number of token deleted in DB
     */
    public function deleteToken($customersId, $token) {
        if ($ret = parent::deleteToken($customersId, $token)) {
            $params = ['VPSProtocol' => $this->api_version,
              'Token' => $token,
              'TxType' => 'REMOVETOKEN',
              'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15)
            ];

            $return = $this->prepareSendRequest('remove-token', $params);
            if (!empty($return['Status']) && $return['Status'] == 'OK') {
                
            } else {
                \Yii::warning('token wasn\'t removed ' . print_r($return, 1), 'SAGEPAY_SERVER_TOKEN');
            }
        }

        return $ret;
    }

    public function canRefund($transaction_id) {

        $ret = false;
        $orderPayment = $this->searchRecord($transaction_id);

        if ($orderPayment && !empty($orderPayment->orders_payment_id) && OrderPaymentHelper::getAmountAvailable($orderPayment)) {
            $ret = true;
        }
        return $ret;
    }

    public function refund($transaction_id, $amount = 0) {
        $ret = false;

        $transaction = $this->getTransactionDetails($transaction_id);

        /**
         *     [vpstxid] => B90FFFC3-DA13-BC45-FE1A-7FA6FE2F10C8
          [vendortxcode] => PAYMENT-1565361506-811596439
          [transactiontype] => Payment
          [txstateid] => 8
          [status] => Transaction CANCELLED by Sage Pay after 15 minutes of inactivity.  This is normally because the customer closed their browser.
          [description] => Barcall shopping
          [amount] => 412.27
          [currency] => GBP
          [started] => 09/08/2019 15:38:26.167
          [completed] => 09/08/2019 15:59:29.140
          [securitykey] => TLQKBDDAMW
         */
        $params = array('VPSProtocol' => $this->api_version,
          'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15),
          'VendorTxCode' => substr(date('YmdHis') . '-' . $transaction['vpstxid'], 0, 40),
          'Amount' => $amount,
          'Currency' => $transaction['currency'],
          'Description' => substr(STORE_NAME, 0, 100),
          'RelatedVPSTxId' => $transaction_id,
          'RelatedVendorTxCode' => $transaction['vendortxcode'],
          'RelatedSecurityKey' => $transaction['securitykey'],
          'RelatedTxAuthNo' => (is_array($transaction['vpsauthcode']) ? $transaction['vpsauthcode'][0] : $transaction['vpsauthcode']),
          'TxType' => 'REFUND');

        try {
            $response = $this->prepareSendRequest('refund', $params);
        } catch (\Exception $e) {
            $ret = $e->getMessage();
        }

        /*
          [VPSProtocol] => 3.00
          [Status] => OK
          [StatusDetail] => Array
          (
          [code] => 0000
          [description] => 0000 : The Authorisation was Successful.
          )

          [SecurityKey] => EK2PHXBULY
          [TxAuthNo] => 2602459
          [VPSTxId] => {2D5AACEE-C6CD-0AD5-FA51-1CC429A64F03}
         */
        if (!empty($response['Status']) && $response['Status'] == 'OK') {
            $tm = $this->manager->getTransactionManager($this);
            $res = $tm->updatePaymentTransaction(trim($response['VPSTxId'], '{}'),
                [
                  'fulljson' => json_encode($response),
                  'status_code' => \common\helpers\OrderPayment::OPYS_REFUNDED,
                  'status' => $response['Status'],
                  'amount' => (float) $amount,
                  'comments' => "Refund State: " . $response['Status'] . "\n" . "Refund Amount: " . $amount . ' ' . $response['description'],
                  'date' => date('Y-m-d H:i:s' /* , strtotime($res->update_time) */),
                  'payment_class' => $this->code,
                  'payment_method' => $this->title,
                  'parent_transaction_id' => $transaction_id,
                  'orders_id' => 0
            ]);
            if ($res) {
                $ret = true;
            }
            parent::updatePaidTotalsAndNotify();

            /*
              $this->manager->getTransactionManager($this)
              ->addTransactionChild($transaction_id, $response['VPSTxId'], $response['Status'], $amount, ($amount ? 'Refund' : 'Refund'));
              $currencies = \Yii::$container->get('currencies');
              $order = $this->manager->getOrderInstance();
              $order->info['comments'] = "Refund State: " . $response['Status'] . "\n" .
              "Refund Amount: " . $currencies->format($amount, true, $order->info['currency'], $order->info['currency_value']);
              $this->_savePaymentTransactionRefund(['transaction_id' => $response['VPSTxId'], 'status' => $response['Status'], 'amount' => $amount], $transaction_id);

             */
        }
        return $ret;
    }

    /**
     * @deprecated use getTransactionManager->updatePaymentTransaction() instead
     * @param type $response
     * @param type $transaction_id
     */
    private function _savePaymentTransactionRefund($response, $transaction_id) {
        $orderPaymentParentRecord = $this->searchRecord($transaction_id);
        if ($orderPaymentParentRecord) {
            $orderPaymentRecord = $this->searchRecord($response['transaction_id']);
            if ($orderPaymentRecord) {
                $order = $this->manager->getOrderInstance();
                $orderPaymentRecord->orders_payment_id_parent = (int) $orderPaymentParentRecord->orders_payment_id;
                $orderPaymentRecord->orders_payment_order_id = (int) $order->order_id;
                $orderPaymentRecord->orders_payment_is_credit = 1;
                $orderPaymentRecord->orders_payment_status = \common\helpers\OrderPayment::OPYS_REFUNDED;
                $orderPaymentRecord->orders_payment_amount = (float) $response['amount'];
                $orderPaymentRecord->orders_payment_currency = trim($order->info['currency']);
                $orderPaymentRecord->orders_payment_currency_rate = (float) $order->info['currency_value'];
                $orderPaymentRecord->orders_payment_snapshot = json_encode(\common\helpers\OrderPayment::getOrderPaymentSnapshot($order));
                $orderPaymentRecord->orders_payment_transaction_status = trim($response['status']);
                $orderPaymentRecord->orders_payment_transaction_date = date('Y-m-d H:i:s');
                $orderPaymentRecord->orders_payment_date_create = date('Y-m-d H:i:s');
                $orderPaymentRecord->save();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getTransactionDetails($transaction_id, \common\services\PaymentTransactionManager $tManager = null) {
        if (empty($this->_transactionDetails) || $transaction_id != $this->_transactionDetails['vpstxid']) {
            $ret = false;
            $orderPayment = $this->searchRecord($transaction_id);

            $vendor = strtolower(substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15));
            $user = $this->getAPIUser();
            $xml = '<command>getTransactionDetail</command><vendor>' . $vendor . '</vendor><user>' . $user . '</user><vpstxid>' . $transaction_id . '</vpstxid>';
            $signature = $this->md5hash($xml);
            $xml = '<vspaccess>' . $xml . '<signature>' . $signature . '</signature></vspaccess>';
            try {
                if ($vendor && $signature) {
                    $url = $this->getApiUrl('status', MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER);
                    $client = new \yii\httpclient\Client([
                      'baseUrl' => $url,
                      'parsers' => [
                        'xml' => '\yii\httpclient\XmlParser',
                      ]
                    ]);
                    $response = $client->post('', 'XML=' . $xml)->send();
                    if ($this->debug) {
                        \Yii::warning(print_r($response, 1), 'SAGEPAY_SERVER_RESPONSE');
                    }
                    $ret = $response->getData();
                    if (is_array($ret) && $orderPayment && is_array($orderPayment->getAttributes())) {
                        $ret = array_merge($orderPayment->getAttributes(), $ret);
                    }
                }
            } catch (\Exception $e) {
                \Yii::warning(" #### " .print_r($e->getMessage(), true), 'TLDEBUG_' . $this->code);
            }
            $this->_transactionDetails = $ret;
        }

        return $this->_transactionDetails;

        /*
          [errorcode] => 0000
          [timestamp] => 19/08/2020 21:49:34
          [vpstxid] => 6FBFB5E6-67AF-183C-3797-12D2F083373E
          [vendortxcode] => 20200814185528-57544-54021
          [transactiontype] => Payment (Authenticate, Authorise, Deferred, Manual, Payment, PreAuth, Refund, Repeat, RepeatDeferred)
          [txstateid] => 16
          [status] => Successfully authorised transaction.
          [description] => ghjghj
          [amount] => 83.40
          [currency] => GBP
          [started] => 14/08/2020 18:55:29.837
          [completed] => 14/08/2020 18:57:34.397
          [securitykey] => 3MEBBGXDTA
          [clientip] => 95.174.67.156
          [giftaid] => NO
          [paymentsystem] => MC
          [paymentsystemdetails] => Credit Card - Ing Bank N.V., NL
          [expirydate] => 0424
          [last4digits] => 8241
          [authprocessor] => NatWest Streamline
          [merchantnumber] => 76028372
          [accounttype] => E
          [vpsauthcode] => 400025055
          [bankauthcode] => 471853
          [batchid] => 25450
          [billingfirstnames] => Jan
          [billingsurname] => vjg
          [billingaddress] => ghjg
          [billingcity] => ghj
          [billingpostcode] => 5662VT
          [billingcountry] => NL
          [billingphone] => +65765
          [deliveryfirstnames] => Jan
          [deliverysurname] => vaj
          [deliveryaddress] => hjgh
          [deliverycity] => Geldrop
          [deliverypostcode] => 5662VT
          [deliverycountry] => NL
          [deliveryphone] => +316
          [cardholder] => Jdfgdiel
          [cardaddress] => gggg
          [cardcity] => Geldrop
          [cardpostcode] => 5662VT
          [cardcountry] => NL
          [customeremail] => jeg@hotmail.com
          [systemused] => S
          [vpsprotocol] => 3.00
          [callbackurl] => https://www.san.co.uk/callback/sage-server?check=SERVER&tlSID=0i3ueva10e35sq1hmohm5oq8a2
          [refunded] => NO
          [repeated] => NO
          [basket] => 4:Brown With Orange Classic Wallet With Coin Purse; =:1:62.50:12.50:75.00:75.00:UK Next Day / Internationalnbsp; (Courier):---:---:---:---:8.40:VAT 20%:---:---:---:---:13.90:Grand Total        (inc VAT):---:---:---:---:83.40
          [applyavscv2] => 0
          [apply3dsecure] => 0
          [authattempt] => 1
          [cv2result] => MATCHED
          [addressresult] => NOTCHECKED
          [postcoderesult] => NOTCHECKED
          [threedattempt] => 1
          [threedresult] => OK
          [eci] => 2
          [cavv] => jEGozOgrPxeKCBAem91FBRgAAAA=
          [t3mscore] => -2
          [t3maction] => OK
          [t3mid] => 6453081673
          [locale] => nl
          [xid] => A5xy4vRRARkfy4APrIfBsX5lHd8=
          [declinecode] => 00
          [surcharge] => 0.00

         */
    }

    private function md5hash($xml) {
        $password = $this->getAPIPassword();
        $signature = false;
        if ($password) {
            $signature = md5($xml . '<password>' . $password . '</password>');
        }
        return $signature;
    }

    /* too many issues  - not logged in etc
      public function popUpMode() {
      $ret = false;
      if ($this->isWithoutConfirmation() && MODULE_PAYMENT_SAGE_PAY_SERVER_PROFILE_PAGE != 'Normal' && !(bool) \Yii::$app->settings->get('from_admin')) {
      $ret = true;
      }
      return $ret;
      }
     */

    public function call_webhooks() {
        $error_url = \Yii::$app->request->get('redirect', '');
        $result = false;
        if (!empty($error_url)) {
            $result = <<<EOD
                <html><body style="text-align:center">
                    <script>window.top.location.href = '{$error_url}';</script>
                    <a href="{$error_url}">Click here if form is not redirected automatically.</a>
                    </body></html>
EOD;
        }
        echo $result;
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function search($queryParams) {
        $found = [];
        $vendor = strtolower(substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15));
        $user = $this->getAPIUser();
        $xml = '<command>getTransactionList</command><vendor>' . $vendor . '</vendor><user>' . $user . '</user><sorttype>ByDate</sorttype><sortorder>DESC</sortorder>';
        if ($queryParams['START_DATE']) {
            $xml .= '<startdate>' . gmdate("d/m/Y H:i:s", strtotime($queryParams['START_DATE'])) . '</startdate>';
        }
        if ($queryParams['END_DATE']) {
            $xml .= '<enddate>' . gmdate("d/m/Y H:i:s", strtotime($queryParams['END_DATE'])) . '</enddate>';
        }
        if ($queryParams['TRANSACTION_ID']) {
            $xml .= '<relatedtransactionid>' . $queryParams['TRANSACTION_ID'] . '</relatedtransactionid>';
        }
        if ($queryParams['SEARCH_PHRASE']) {
            $xml .= '<searchphrase>' . $queryParams['SEARCH_PHRASE'] . '</searchphrase>';
        }


        $signature = $this->md5hash($xml);
        $xml = '<vspaccess>' . $xml . '<signature>' . $signature . '</signature></vspaccess>';
        if ($vendor && $signature) {
            $url = $this->getApiUrl('status', MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER);
            $client = new \yii\httpclient\Client([
              'baseUrl' => $url,
              'parsers' => [
                'xml' => '\yii\httpclient\XmlParser',
              ]
            ]);
            $response = $client->post('', 'XML=' . $xml)->send();
            if ($this->debug) {
                \Yii::warning(" #### " . print_r($response, 1), 'sagepay-TLDEBUG-search');
            }
            $response = $response->getData();
            if ($this->debug) {
                \Yii::warning(" #### " . print_r($response, 1), 'sagepay-TLDEBUG-search-data');
            }
        }
        if ($response['errorcode'] == '0000') {
            $ret = $response['transactions'];
            $found[] = [
              'id' => 0,
              'date' => '',
              'amount' => '',
              'negative' => 0,
              'name' => 'Shown ' . $ret['endrow'] . ' of ' . $ret['totalrows'],
              'status' => 0,
            ];
            $currencies = \Yii::$container->get('currencies');
            if (is_array($ret['transaction'])) {
                foreach ($ret['transaction'] as $t) {
                    $t = (array) $t;
                    $parsed = $this->parseTransactionDetails($t);
                    $found[] = [
                      'id' => $t['vpstxid'],
                      //'date' => \common\helpers\Date::formatDateTimeJS($t['started']),
                      'date' => $t['started'],
                      'amount' => $currencies->format($t['amount'], true, $t['currency']),
                      'negative' => (!in_array(strtolower($t['transactiontype']), ['payment', 'deferred'])),
                      'name' => $t['cardholder'],
                      'disabled' => (!in_array(strtolower($t['result']), ['success'])),
                      'status' => $t['result'],
                    ];
                }
            }
        } else {
            $found[] = ['name' => $response['error']];
        }

        return $found;
    }

    /**
     * parse getTransactionDetails into $this->transactionInfo
     * @param array $transactionDetails
     */
    public function parseTransactionDetails($transactionDetails) {
        $this->transactionInfo = [];
        if (is_array($transactionDetails)) {
            $this->transactionInfo['status'] = $transactionDetails['status'];
            $this->transactionInfo['status_code'] = $this->getStatusCode($transactionDetails);
            $this->transactionInfo['transaction_id'] = $transactionDetails['vpstxid'];
            $this->transactionInfo['amount'] = $transactionDetails['amount'];
            $this->transactionInfo['fulljson'] = json_encode($transactionDetails);

            $comment = '';
            foreach (
                array_intersect_key(
                    $transactionDetails,
                    array_flip([
                  'transactiontype', 'status', 'amount', 'currency',
                  'started', 'completed',
                  'paymentsystem', 'expirydate', 'last4digits',
                  'refunded', 'repeated',
                  'cv2result', 'addressresult', 'postcoderesult', 'threedresult',
                  't3mscore', 't3maction'
                ])) as $k => $v) {
                    $comment .= "$k: $v; \n";
            }
            $this->transactionInfo['comments'] = $comment;
        }

        return $this->transactionInfo;
    }

    /**
     * get transaction Status code according transaction details and module settings
     * @param array $transactionDetails
     * @return int one of OrderPaymentHelper constants
     */
    public function getStatusCode($transactionDetails) {

        $statusCode = OrderPaymentHelper::OPYS_PENDING;

        if (defined('MODULE_PAYMENT_SAGE_PAY_SERVER_T3M_VERIFICATION') && MODULE_PAYMENT_SAGE_PAY_SERVER_T3M_VERIFICATION == 'Required' && $transactionDetails['t3maction'] == 'NORESULT') {
            $statusCode = OrderPaymentHelper::OPYS_PENDING;
        } else {
            if (!empty($transactionDetails['transactiontype']) && !empty($transactionDetails['txstateid'])) {
// report API response
                /*
                 * 1 Transaction failed registration. Either an INVALID or MALFORMED response was returned.
                  2 User on Card Selection page.
                  3 User on the Card Details Entry Page.
                  4 User on Confirmation Page.
                  5 Transaction at 3D-Secure Authentication Stage.
                  6 Transaction sent for Authorisation
                  7 Vendor Notified of transaction state at their NotificationURL. Awaiting response.
                  8 Transaction CANCELLED by Sage Pay after 15 minutes of inactivity. This is normally because the customer closed their browser.
                  9 Transaction completed but Vendor systems returned INVALID or ERROR in response to notification POST. Transaction CANCELLED by the Vendor.
                  10 Transaction REJECTED by the Fraud Rules you have in place.
                  11 Transaction ABORTED by the Customer on the Payment Pages.
                  12 Transaction DECLINED by the bank (NOTAUTHED).
                  13 An ERROR occurred at Sage Pay which cancelled this transaction.
                  14 Successful DEFERRED transaction, awaiting RELEASE.
                  15 Successful AUTHENTICATED transaction, awaiting AUTHORISE.
                  16 Successfully authorised transaction.
                  17 Transaction Timed Out at Authorisation Stage.
                  18 Transaction VOIDed by the Vendor.
                  19 Successful DEFERRED transaction ABORTED by the Vendor.
                  20 Transaction has been timed out by Sage Pay.
                  21 Successfully REGISTERED transaction, awaiting AUTHORISE.
                  22 AUTHENTICATED or REGISTERED transaction CANCELLED by the Vendor.
                  23 Transaction could not be settled with the bank and has been failed by the Sage Pay systems
                  24 PayPal Transaction Registered
                  25 Token Registered
                  26 AUTHENTICATE transaction that can no longer be AUTHORISED against. It has either expired, or been fully authorised.
                  27 DEFERRED transaction that expired before it was RELEASEd or ABORTed.
                  28 Transaction waiting for authorisation.
                  29 Successfully authorised transaction.
                  30 The transaction failed.
                  31 The transaction failed due to invalid or incomplete data.
                  32 The transaction was aborted by the customer.
                  33 Transaction timed out at authorisation stage.
                  34 A remote ERROR occurred at Sage Pay which cancelled this transaction.
                  35 A local ERROR occurred at Sage Pay which cancelled this transaction.
                  36 The transaction could not be sent to the bank and has been failed by the Sage Pay systems.
                  37 The transaction was declined by the bank.
                  38 User at bank details page
                  39 User at Token Details page
                 */
                if (in_array(intval($transactionDetails['txstateid']), [16])) {
                    $statusCode = OrderPaymentHelper::OPYS_SUCCESSFUL;
                } elseif (strtoupper($transactionDetails['transactiontype']) == 'PAYMENT' && in_array(intval($transactionDetails['txstateid']), [16, 29])) {
                    $statusCode = OrderPaymentHelper::OPYS_SUCCESSFUL;
                } else {
                    if (in_array(intval($transactionDetails['txstateid']), [14, 15, 21, 24])) {
                        $statusCode = OrderPaymentHelper::OPYS_PROCESSING;
                    } else {
                        $statusCode = OrderPaymentHelper::OPYS_PENDING;
                    }
                }
            } else {

// server payment notification
                if (in_array($transactionDetails['TxType'], ['PAYMENT']) && $transactionDetails['Status'] == 'OK') {
                    $statusCode = OrderPaymentHelper::OPYS_SUCCESSFUL;
                } else {
                    if (in_array($transactionDetails['Status'], ['AUTHENTICATED', 'REGISTERED'])) {
                        $statusCode = OrderPaymentHelper::OPYS_PROCESSING;
                    } else {
                        $statusCode = OrderPaymentHelper::OPYS_PENDING;
                    }
                }
            }
        }
        return $statusCode;
    }

    /**
     * @inheritdoc
     */
    public function getFields() {
        return [
          [['START_DATE'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],
          [['END_DATE'], 'datetime', 'format' => 'yyyy-MM-dd HH:mm:ss'],
          ['TRANSACTION_ID', 'string'],
          ['SEARCH_PHRASE', 'string'],
        ];
    }
    
    
    public function canVoid($transaction_id) {
        $ret = false;
        $response = $this->getTransactionDetails($transaction_id);
        if ($response
             && !empty($response['transactiontype']) && !empty($response['txstateid'])
            && in_array($response['txstateid'], [14, 15])
            ) {
            /*
            14 Successful DEFERRED transaction, awaiting RELEASE.
            28 Transaction waiting for authorisation.
            15 Successful AUTHENTICATED transaction, awaiting AUTHORISE.*/
            if ($response['txstateid'] == 14) {
                $ret = 2;
            } else {
                $ret = 1;
            }
        }
        return $ret;
    }

/**
 * void (not in terms of sage pay) - abort DEFERRED /cancel AUTHENTICATE. SagePay's void is not implemented (allowed within 1 day only, before payment settlement)
 * @param string $transaction_id
 * @return boolean
 */
    public function void($transaction_id) {
        $response = $this->getTransactionDetails($transaction_id);
        $amt = $response['amount'];

        if (!in_array($response['txstateid'], [14, 15])) {
            $ret = (defined('TEXT_MESSAGE_ERROR_INCORRECT_TRANSACTION_STATE')?TEXT_MESSAGE_ERROR_INCORRECT_TRANSACTION_STATE:'Incorrect transaction state');
        } elseif ($amt>0) {
            $amount = $this->formatRaw($amt);
            /*
            14 Successful DEFERRED transaction, awaiting RELEASE.
            15 Successful AUTHENTICATED transaction, awaiting AUTHORISE.*/
            if ($response['txstateid'] == 14) {
                $ret = $this->abortOrder($response, $amount);
            } else {
                $ret = $this->cancelOrder($response, $amount);
            }

            if (!$ret) { // not a big problem - autocancel in 30-90 days
                if (!empty($this->_transactionDetails['StatusDetail']['description'])) {
                    $ret = $this->_transactionDetails['StatusDetail']['description'];
                }
                $this->_transactionDetails = false; //for batch - reset details
            } else {
                $this->getUpdateTransaction($transaction_id);
                $ret = true;
            }
        }
        return $ret;
    }
    
/**
 *
 * @param type $transaction_id
 * @return int|false
 */
    public function canCapture($transaction_id) {
        $ret = false;
        $response = $this->getTransactionDetails($transaction_id);
        if ($response
             && !empty($response['transactiontype']) && !empty($response['txstateid'])
             && in_array($response['txstateid'], [14, 15])
            ) {
            /*
            28 Transaction waiting for authorisation.
            14 Successful DEFERRED transaction, awaiting RELEASE.
            15 Successful AUTHENTICATED transaction, awaiting AUTHORISE.*/
            if ($response['txstateid'] == 14) {
                $ret = 2;
            } else {
                $ret = 1;
            }
        }
        return $ret;
    }

    public function release($transaction_id, $status = 0) {
        $response = $this->getTransactionDetails($transaction_id); // array or false
        if (!empty($response['amount'])) {
            $amt = $response['amount'];
        }
        if (!in_array(($response['txstateid']??-1), [14, 15])) {
            $ret = (defined('TEXT_MESSAGE_ERROR_INCORRECT_TRANSACTION_STATE')?TEXT_MESSAGE_ERROR_INCORRECT_TRANSACTION_STATE:'Incorrect transaction state');
        } elseif ($amt>0) {
            $amount = $this->formatRaw($amt);
            /*
            14 Successful DEFERRED transaction, awaiting RELEASE.
            15 Successful AUTHENTICATED transaction, awaiting AUTHORISE.*/
            if ($response['txstateid'] == 14) {
                $ret = $this->releaseOrder($response, $amount);
                if (!empty($ret['Status']) && $ret['Status'] == 'OK') { // update status of transaction status of order already updated
                    $tm = $this->manager->getTransactionManager($this);
                    $this->_transactionDetails = false;
                    $details = $this->getTransactionDetails($transaction_id);
                    $response = $this->parseTransactionDetails($details);
                    $response['deferred'] = 2;
                    $ret = $tm->updatePaymentTransaction($response['transaction_id'], $response);
                    $orderPaymentRecord = $this->searchRecord($transaction_id);
                    if (!empty($response['comments']) && $orderPaymentRecord && !empty($orderPaymentRecord->orders_payment_order_id)) {
                        global $login_id;
                        tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments, admin_id, ip_address, file, line) values ('" . (int)$orderPaymentRecord->orders_payment_order_id . "', '" . $status . "', now(), '" . '0' . "', '" . tep_db_input($response['comments'])  . "', '".$login_id."', '".get_ip_address()."', '".__FILE__."', '".__LINE__."')");
                    }
                }
            } else {
                $ret = $this->authoriseOrder($response, $amount);
                if ($response['status'] != 'REGISTERED' && !empty($ret['Status']) && $ret['Status'] == 'OK') {
                    $tm = $this->manager->getTransactionManager($this);

                    $res = $tm->updatePaymentTransaction(trim($ret['VPSTxId'], '{}'),
                        [
                          'fulljson' => json_encode($ret),
                          'status_code' => \common\helpers\OrderPayment::OPYS_SUCCESSFUL,
                          'status' => $ret['Status'],
                          'amount' => (float) $amount,
                          'comments' => "Auth State: " . ($ret['StatusDetail']??'') . "\n" . "Auth Amount: " . $amount,
                          'date' => date('Y-m-d H:i:s' /* , strtotime($res->update_time) */),
                          'payment_class' => $this->code,
                          'payment_method' => $this->title,
                          'parent_transaction_id' => $transaction_id,
                          'deferred' => 2,
                          'orders_id' => 0
                    ]);
                    $transaction_id = trim($ret['VPSTxId'], '{}');
                }
            }

            if (!$ret) {
                $orderPaymentRecord = $this->searchRecord($transaction_id);
                $info = '';
                if (!empty($this->_transactionDetails['StatusDetail']['description'])) {
                    $ret = $info = $this->_transactionDetails['StatusDetail']['description'];
                }
                if (SEND_EXTRA_ORDER_EMAILS_TO == '') {
                    \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, 'Release Failed ' . ($orderPaymentRecord->orders_payment_order_id ?? '') . ' ' . $amt, ' Release of ' . $amt . ' failed transaction: ' . $transaction_id . ' ' . $info, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                } else {
                    \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, 'Release Failed ' . ($orderPaymentRecord->orders_payment_order_id ?? '') . ' ' . $amt, ' Release of ' . $amt . ' failed transaction: ' . $transaction_id . ' ' . $info, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array(), 'CC: ' . SEND_EXTRA_ORDER_EMAILS_TO);
                }

                $this->_transactionDetails = false; //for batch - reset details
            } else {
                $ret = true;
            }
        }
        return $ret;

    }

    public function capture($transaction_id, $amt = 0) {
        $response = $this->getTransactionDetails($transaction_id);
        if (empty($amt)) {
            if (!empty($response['amount'])) {
                $amt = $response['amount'];
            }
        } elseif (!empty($response['amount'])
                && (
                (ceil($response['amount']*1.15)<$amt && $response['txstateid']==14)//auth coud be 15% more than origianl
                || ($response['amount']<$amt && $response['txstateid']==15) //release could be same or less
                )
            ) {
            $amt = -1 * $amt; //don't  send to gateway too big amount
        }

        if (!in_array(($response['txstateid']??null), [14, 15])) {
            $ret = (defined('TEXT_MESSAGE_ERROR_INCORRECT_TRANSACTION_STATE')?TEXT_MESSAGE_ERROR_INCORRECT_TRANSACTION_STATE:'Incorrect transaction state');
        } elseif ($amt>0) {
            $amount = $this->formatRaw($amt);
            /*
            14 Successful DEFERRED transaction, awaiting RELEASE.
            15 Successful AUTHENTICATED transaction, awaiting AUTHORISE.*/
            if ($response['txstateid'] == 14) {
                $ret = $this->releaseOrder($response, $amount);
            } else {
                //2check - no account with auth enabled
                $ret = $this->authoriseOrder($response, $amount);
                if ($response['status'] != 'REGISTERED' && !empty($ret['Status']) && $ret['Status'] == 'OK') {
                    $tm = $this->manager->getTransactionManager($this);
                    $res = $tm->updatePaymentTransaction(trim($ret['VPSTxId'], '{}'),
                        [
                          'fulljson' => json_encode($ret),
                          'status_code' => \common\helpers\OrderPayment::OPYS_SUCCESSFUL,
                          'status' => $ret['Status'],
                          'amount' => (float) $amount,
                          'comments' => "Auth State: " . ($ret['StatusDetail']??'') . "\n" . "Auth Amount: " . $amount,
                          'date' => date('Y-m-d H:i:s' /* , strtotime($res->update_time) */),
                          'payment_class' => $this->code,
                          'payment_method' => $this->title,
                          'parent_transaction_id' => $transaction_id,
                          'orders_id' => 0
                    ]);
                    $transaction_id = trim($ret['VPSTxId'], '{}');
                }
            }

            if (!$ret) {
                $orderPaymentRecord = $this->searchRecord($transaction_id);
                $info = '';
                if (!empty($this->_transactionDetails['StatusDetail']['description'])) {
                    $ret = $info = $this->_transactionDetails['StatusDetail']['description'];
                }
                if (SEND_EXTRA_ORDER_EMAILS_TO == '') {
                    \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, 'Capture Failed ' . ($orderPaymentRecord->orders_payment_order_id ?? '') . ' ' . $amt, ' Capture of ' . $amt . ' failed transaction: ' . $transaction_id . ' ' . $info, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                } else {
                    \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, 'Capture Failed ' . ($orderPaymentRecord->orders_payment_order_id ?? '') . ' ' . $amt, ' Capture of ' . $amt . ' failed transaction: ' . $transaction_id . ' ' . $info, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array(), 'CC: ' . SEND_EXTRA_ORDER_EMAILS_TO);
                }

                $this->_transactionDetails = false; //for batch - reset details
            } else {
                $this->getUpdateTransaction($transaction_id);
                $ret = true;
            }
        } else {
            $ret = (defined('TEXT_MESSAGE_ERROR_INCORRECT_AMOUNT')?TEXT_MESSAGE_ERROR_INCORRECT_AMOUNT:'Incorrect amount') . ' ' . abs($amt) . ' ' . ($response['amount']?'(' . $response['amount'] . ')':'');
        }
        return $ret;
    }


    /**
     * 2do (can't test)- should be saved as new transaction - several authorisation and each of them could be voided.
     * @param type $transaction
     * @param type $amount
     * @return boolean
     */
    private function authoriseOrder($transaction, $amount) {
        $ret = false;
        $params = ['VPSProtocol' => $this->api_version,
                'TxType' => 'AUTHORISE',
                'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15),
                'VendorTxCode' => substr(date('YmdHis') . '-' . $transaction['vpstxid'], 0, 40),
                'Amount' => $amount,
                'Description' => substr(STORE_NAME, 0, 100),
                'RelatedVPSTxId' => $transaction['vpstxid'],
                'RelatedVendorTxCode' => $transaction['vendortxcode'],
                'RelatedSecurityKey' => $transaction['securitykey'],
                'RelatedTxAuthNo' => (is_array($transaction['vpsauthcode']) ? $transaction['vpsauthcode'][0] : $transaction['vpsauthcode'])
            //ApplyAVSCV2
            ];


        $return = $this->prepareSendRequest('authorise', $params);
        if (!empty($return['Status']) && $return['Status'] == 'OK') {
            $ret = true;
        } else {
            $this->_transactionDetails = $return;
        }
        return $ret;
    }

    private function releaseOrder($transaction, $amount) {
        $ret = false;
        $params = ['VPSProtocol' => $this->api_version,
              'TxType' => 'RELEASE',
              'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15),
              'VendorTxCode' => $transaction['vendortxcode']??($transaction['VendorTxCode']??''),
              'VPSTxId' => $transaction['vpstxid'],
              'SecurityKey' => $transaction['securitykey'],
              'TxAuthNo' => (is_array($transaction['vpsauthcode']) ? $transaction['vpsauthcode'][0] : $transaction['vpsauthcode']),
              //'TxAuthNo' => (is_array($transaction['txauthno']) ? $transaction['txauthno'][0] : (isset($transaction['txauthno'])?$transaction['txauthno']:($transaction['TxAuthNo']??''))),
              'ReleaseAmount' => $amount
            ];
        $return = $this->prepareSendRequest('release', $params);
        if (!empty($return['Status']) && $return['Status'] == 'OK') {
            $ret = true;
        } else {
            $this->_transactionDetails = $return;
        }
        return $ret;
    }
    
    private function cancelOrder($transaction, $amount) {
        $ret = false;
        $params = ['VPSProtocol' => $this->api_version,
              'TxType' => 'CANCEL',
              'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15),
              'VendorTxCode' => $transaction['vendortxcode']??($transaction['VendorTxCode']??''),
              'VPSTxId' => $transaction['vpstxid'],
              'SecurityKey' => $transaction['securitykey']
            ];

        $return = $this->prepareSendRequest('cancel', $params);
        if (!empty($return['Status']) && $return['Status'] == 'OK') {
            $ret = true;
        } else {
            $this->_transactionDetails = $return;
        }
        return $ret;
    }
    
    private function abortOrder($transaction, $amount) {
        $ret = false;
        $params = ['VPSProtocol' => $this->api_version,
              'TxType' => 'ABORT',
              'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_SERVER_VENDOR_LOGIN_NAME, 0, 15),
              'VendorTxCode' => $transaction['vendortxcode']??($transaction['VendorTxCode']??''),
              'VPSTxId' => $transaction['vpstxid'],
              'SecurityKey' => $transaction['securitykey'],
              'TxAuthNo' => (is_array($transaction['vpsauthcode']) ? $transaction['vpsauthcode'][0] : $transaction['vpsauthcode'])
              //'TxAuthNo' => (is_array($transaction['txauthno']) ? $transaction['txauthno'][0] : (isset($transaction['txauthno'])?$transaction['txauthno']:($transaction['TxAuthNo']??'')))
            ];

        $return = $this->prepareSendRequest('abort', $params);
        if (!empty($return['Status']) && $return['Status'] == 'OK') {
            $ret = true;
        } else {
            $this->_transactionDetails = $return;
        }
        return $ret;
    }

    private function prepareSendRequest($type, $params) {
        $url = $this->getApiUrl($type, MODULE_PAYMENT_SAGE_PAY_SERVER_TRANSACTION_SERVER);
        $post_string = '';
        foreach ($params as $key => $value) {
            $post_string .= $key . '=' . urlencode(trim($value)) . '&';
        }
        if ($this->debug) {
            \Yii::warning($url . ' post  => ' . $post_string, 'SAGEPAY_SERVER_REQUEST');
        }

        try {
            $transaction_response = parent::sendRequest($url, ['post' => $post_string, 'headerOut' => 1]);
            $return = $this->parseResponce($transaction_response['headers']);
        } catch (\Exception $ex) {
            $return = $ex->getMessage();
        }

        if ($this->debug) {
            \Yii::warning(print_r($return, 1), 'SAGEPAY_SERVER_RESPONCE');
        }
        return $return;

    }

    public function canReauthorize($transaction_id) {
        return false;
    }

    public function reauthorize($transaction_id, $amount = 0) {
        return false;
    }

    protected function getAPIUser() {
        return $this->decryptConst('MODULE_PAYMENT_SAGE_PAY_SERVER_ACCOUNT');
    }

    protected function getAPIPassword() {
        $ret = $this->decryptConst('MODULE_PAYMENT_SAGE_PAY_SERVER_ACCOUNT_PASSWORD');
        return $ret;
    }

    protected function getEncryptionKey() {
        $key = parent::getEncryptionKey();
        if (!$key) {
            $key = 'p=UOd%RWRTp::k=@D)_M#4Mi^a+SF?h5ai6RGsdC]`j';
        }
        return $key;
    }
/**
 *
 * @return string|false
 */
    public function saveOrderBefore() {
        $orderClass = false;
        if (defined('MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_BEFORE_PAYMENT') && MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_BEFORE_PAYMENT != 'False') {
            if (MODULE_PAYMENT_SAGE_PAY_SERVER_ORDER_BEFORE_PAYMENT == 'True') {
                $orderClass = 'Order';
            } else {
                $orderClass = 'TmpOrder';
            }
        }
        return $orderClass;
    }

    public function saveOrderBySettings() {
        $ret = false;
        $orderClass = $this->saveOrderBefore();
        if ($orderClass) {
            if ($orderClass != 'TmpOrder') {
                $order = $this->manager->getOrderInstance();
                $order->info['order_status'] = $this->getDefaultOrderStatusId();

            }
            $ret = $this->saveOrder($orderClass);
            if (!empty($ret)) {
                if ($orderClass == 'TmpOrder') {
                    $ret = 'tmp' . $ret;
                    $key = 'sage_pay_server_tmp_order';
                } else {
                    $key = 'sage_pay_server_order_before';
                }
                $this->manager->set($key, $ret);
            }
        }
        return $ret;
    }

}
