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
use \PayPal;

require (__DIR__ . "/lib/paypal.v2.php");

class paypal_partner extends lib\PaypalMiddleWare {

    const PARTNER_ATTRIBUTION_ID = 'HOLBIGROUPLTD_SP_OSCOMMERCE';
    const SANDBOX_PARTNER_ATTRIBUTION_ID = 'FLAVORsb-mstny8200048_MP';
    const PARTNER_DEFAULT_FEE = 0; //percent, 0 to be editable in admin
    const PARTNER_APP_CLIENT_ID = 'AVC9I0Kbo0YkVYIx0LHY91HJKBGosc8-t5PMW-Pzve369xVbcf68_4MUw9cu3BC3zvz9UYqGRnk_u903';
    const PARTNER_APP_CLIENT_SECRET = '';
    /* ppcp */
    const PARTNER_MERCHANT_ID = '9TZHDRH8BP3US'; 
    const PARTNER_MERCHANT_SANDBOX_ID = '88LZN7N3UWLF8'; 
    const PARTNER_APP_SANDBOX_CLIENT_ID = 'Aax41DjEyTorujP5KPKaGr2J_hnez5hmh7W46Wvs81LWr2un124l2THRnJ7XlZPUiPZgLXzImbc0Zcg7';
    const PARTNER_APP_SANDBOX_CLIENT_SECRET =  '';

    /**/
    const BOARDING_MODE = 1; //1 or 3 party

    use lib\PaypalPartnerTrait;
    use lib\PaypalPartnerTraitAPM;
    use lib\PaypalPartnerTraitFraudNet;

    var $code, $title, $description, $enabled;
    private $jsIncluded = false;
    private $debug = false;
    private $sendExVat = true; //true to send items See comment in patch webhook processing
    private $requireItemVat = false; //DE pay upon invoice /purchase_units/0/items/0/tax is required for provided payment source.
    private $pp_commit = 'true'; //string! false - order total could be changed after confirmation. Incomaptible with APMs (is Eligable == false)
    private $jsLibAdded = false;
    private $onlySiteAddress = false;
    
    protected $webHooks1party = [
      'CHECKOUT.ORDER.APPROVED',
      'CHECKOUT.PAYMENT-APPROVAL.REVERSED'
      ];
    protected $webHooks3party = [
      'MERCHANT.ONBOARDING.COMPLETED',
      'MERCHANT.PARTNER-CONSENT.REVOKED',
      'CUSTOMER.DISPUTE.CREATED',
      'CUSTOMER.DISPUTE.UPDATED',
      'CUSTOMER.DISPUTE.RESOLVED'
      ];
    protected $webHooks = [
      'PAYMENT.AUTHORIZATION.CREATED',
      'PAYMENT.AUTHORIZATION.VOIDED',
      'PAYMENT.CAPTURE.COMPLETED',
      'PAYMENT.CAPTURE.DENIED',
      'PAYMENT.CAPTURE.REFUNDED',
      'PAYMENT.CAPTURE.REVERSED',
      'PAYMENT.REFERENCED-PAYOUT-ITEM.COMPLETED',
      'PAYMENT.REFERENCED-PAYOUT-ITEM.FAILED',
    ];
    
    protected static $threeDSDefaults = [
        'status' => 1,
        'contingencies' => 'SCA_WHEN_REQUIRED',
        '3dsa_y_y_1' => 1,
        '3dsa_y_n_2' => 0,
        '3dsa_y_r_2' => 0,
        '3dsa_y_a_1' => 1,
        '3dsa_y_u_3' => 0,
        '3dsa_y_u_2' => 0,
        '3dsa_y_c_3' => 0,
        '3dsa_y__2' => 0,
        '3dsa_n__2' => 1,
        '3dsa_u__2' => 1,
        '3dsa_u__3' => 0,
        '3dsa_b__2' => 1,
        '3dsa___3' => 0
    ];
    
    protected static $possibleFundings = [
      'paypal' => ['default_translation' => 'PayPal', 'apm' => 0, 'countries' => [], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_PAYPAL_TITLE'],
      'card' => ['default_translation' => 'Credit or debit cards', 'apm' => 0, 'countries' => [], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_CARD_TITLE'],
      'credit' => ['default_translation' => 'PayPal Credit', 'apm' => 0, 'countries' => [], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_CREDIT_TITLE'],
      'paylater' => ['default_translation' => 'Pay Later', 'apm' => 0, 'countries' => ['au', 'de', 'fr', 'it', 'sp', 'gb', 'us'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_PAYLATER_TITLE'],
      /**/
      //'venmo' => ['default_translation' => 'Venmo', 'apm' => 1, 'countries' => ['US'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_VENMO_TITLE'],
      'pui' => ['default_translation' => 'Pay upon invoice', 'apm' => 1, 'countries' => ['de'], 'translation_key' => 'TEXT_PAY_UPON_INVOICE'],
      'bancontact' => ['default_translation' => 'Bancontact', 'apm' => 1, 'countries' => ['be'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_BANCONTACT_TITLE'],
      'blik' => ['default_translation' => 'BLIK', 'apm' => 1, 'countries' => ['pl'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_BLIK_TITLE'],
      'eps' => ['default_translation' => 'EPS', 'apm' => 1, 'countries' => ['at'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_EPS_TITLE'],
      'giropay' => ['default_translation' => 'GIROPAY', 'apm' => 1, 'countries' => ['de'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_GIROPAY_TITLE'],
      'ideal' => ['default_translation' => 'iDEAL', 'apm' => 1, 'countries' => ['nl'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_IDEAL_TITLE'],
      //'mercadopago' => ['default_translation' => 'Mercado Pago', 'apm' => 1, 'countries' => ['br', 'mx'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_MERCADOPAGO_TITLE'],
      'mybank' => ['default_translation' => 'MyBank', 'apm' => 1, 'countries' => ['it'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_MYBANK_TITLE'],
      'p24' => ['default_translation' => 'Przelewy24', 'apm' => 1, 'countries' => ['pl'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_P24_TITLE'],
      'sepa' => ['default_translation' => 'SEPA-Lastschrift', 'apm' => 0, 'countries' => [], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_SEPA_TITLE'],
      'sofort' => ['default_translation' => 'Sofort', 'apm' => 1, 'countries' => ['at', 'be', 'de', 'sp', 'it', 'nl', 'gb'], 'translation_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_SOFORT_TITLE'],
      
        //'applepay' => 'ApplePay',
        /*'itau' => 'Itau',
        'zimpler' => 'Zimpler',
        'wechatpay' => 'WeChatPay',
        'payu' => 'PayU',
        'verkkopankki' => 'Verkkopankki',
        'trustly' => 'Trustly',
        'oxxo' => 'Oxxo',
        'boleto' => 'Boleto',
        'boletobancario' => 'BoletoBancario',
        'maxima' => 'Maxima',
        'multibanco' => 'Multibanco',
        'satispay' => 'Satispay',
      /**/
    ];

    protected $defaultTranslationArray = [
      'MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_TITLE' => 'PayPal, PayLater, Credit and Debit Cards',
      'MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_PUBLIC_TITLE' => 'PayPal (including PayLater, Credit and Debit Cards)',
      'MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_DESCRIPTION' => 'You need PayPal <a href="https://www.paypal.com/gb/welcome/signup" target="_blank">account</a> and your own <a href="https://developer.paypal.com/developer/applications/" target="_blank">API access details</a>',
      'MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_DESCRIPTION_3P' => 'Create PayPal account, link existing or enter your own API access details',
      'MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_BUTTON' => 'Check Out with PayPal',
      'MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_COMMENTS' => 'Comments:',
      'MODULE_PAYMENT_PAYPAL_PARTNER_EMAIL_PASSWORD' => 'An account has been created for you with the following e-mail address and password:' . "\n\n" . 'E-Mail Address: %s' . "\n" . 'Password: %s' . "\n\n",
      'MODULE_PAYMENT_PAYPAL_PARTNER_ERROR_ADMIN_CONFIGURATION' => 'This module will not load until the API Credential parameters are configured. Please update settings of the module.',
      'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_STATIC' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-medium.png',
      'MODULE_PAYMENT_PAYPAL_PARTNER_LANGUAGE_LOCALE' => 'en_UK',
      'MODULE_PAYMENT_PAYPAL_PARTNER_ERROR_NO_SHIPPING_AVAILABLE_TO_SHIPPING_ADDRESS' => 'Shipping is currently not available for the selected shipping address. Please select or create a new shipping address to use with your purchase.',
      'MODULE_PAYMENT_PAYPAL_PARTNER_WARNING_LOCAL_LOGIN_REQUIRED' => 'Please log into your account to verify the order.',
      'MODULE_PAYMENT_PAYPAL_PARTNER_NOTICE_CHECKOUT_CONFIRMATION' => 'Please review and confirm your order below. Your order will not be processed until it has been confirmed.',
      'PAYPAL_PARTNER_SAVE_TO_CREATE_ACCOUNT' => 'To create new PayPal account please check and save your details. Then process on boarding.',
      'PAYPAL_PARTNER_ONBOARD' => 'Process on boarding',
      'PAYPAL_PARTNER_CHECK_ONBOARD' => 'Check onBoard Status',
      'PAYPAL_PARTNER_DELETE_SELLER' => 'Delete seller',
      'PAYPAL_PARTNER_SELLER_EMAIL' => 'Seller E-Mail Address',
      'PAYPAL_PARTNER_SELLER_MERCHANT_ID' => 'Seller Merchant Id',
      'PAYPAL_PARTNER_SELLER_TRACKING_ID' => 'Seller Unique Tracking Id',
      'PAYPAL_PARTNER_LOGIN' => 'Login to existing PayPal account',
      'PAYPAL_PARTNER_SELLER_NOT_BOARDED' => 'Seller NOT Boarded',
      'PAYPAL_PARTNER_SELLER_BOARDED' => 'Seller Boarded',
      'PAYPAL_PARTNER_SELLER_BOARDED_ERROR' => 'OnBoarding process could not be continued.',
      'PAYPAL_PARTNER_SELLER_BOARDED_ERROR_PERMISSIONS' => "Seller hasn't accepted permissions to make payments or primary email is not confirmed",
      'PAYPAL_PARTNER_GRANT_PERMISSIONS' => 'Please grant permissions to get payments over Holbi Paypal account on PayPal',
      'PAYPAL_PARTNER_CONTINUE_PAYPAL' => 'Continue to PayPal',
      'PAYPAL_PARTNER_FEE' => 'Fee percent',
      'PAYPAL_PARTNER_SAME_DOMAIN' => 'Important! Please login to admin using same domain as selected platform',
      'PAYPAL_PARTNER_CHECK_DETAILS_AND_BOARDING' => 'Please check/update your details and then process on boarding',
      'PAYPAL_PARTNER_OWN_CLIENT_ID' => 'PayPal API Client ID',
      'PAYPAL_PARTNER_OWN_CLIENT_SECRET' => 'PayPal API Client secret',
      'PAYPAL_PARTNER_GO_LIVE' => 'PayPal Go Live',
      'PAYPAL_PARTNER_RESTART_GENERAL' => 'Error during comunication with PayPal. Please try to pay once again or choose a different payment method.',
      'PAYPAL_PARTNER_RESTART' => 'Error during comunication with PayPal (incorrect total). Please login to PayPal once again',
      'PAYPAL_PARTNER_RESTART_AUTHORIZE' => 'Error during comunication with PayPal (payment not authorized). Please login to PayPal once again',
      'PAYPAL_PARTNER_RESTART_CAPTURE' => 'Error during comunication with PayPal (payment not captured). Please login to PayPal once again',
      'PAYPAL_PARTNER_ORDER_DETAILS_ERROR' => 'Order is not completed',
      'PAYPAL_PARTNER_ORDER_ID_ERROR' => 'Invalid Order Id',

    ];

    public function getQuickTranslationKeys() {
        return [
          'MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_PUBLIC_TITLE' => 'PayPal (including Credit and Debit Cards)',
        ];
    }

    function __construct() {
        parent::__construct();
        if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
            $this->sendExVat = false; //can't update products on adress update (patch order)
        }
        $configs = [
          'log.LogEnabled' => true,
          'log.FileName' => \Yii::getAlias('@frontend') . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'pp.log',
          'cache.FileName' => \Yii::getAlias('@frontend') . DIRECTORY_SEPARATOR . 'runtime' . DIRECTORY_SEPARATOR . 'auth.cache' . DIRECTORY_SEPARATOR . 'paypal.json',
          'log.LogLevel' => 'DEBUG',
          'cache.enabled' => 1,
          'mode' => defined('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER')? strtolower(MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER):'test'
          ];
        if ($tmp = \common\modules\orderPayment\paypal_partner::getAttributionId()) {
            $configs['http.headers.PayPal-Partner-Attribution-Id'] = $tmp;
        }
        $conf = PayPal\Core\PayPalConfigManager::getInstance()->addConfigs($configs);
        
        $this->code = 'paypal_partner';
        $this->title = MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_TITLE;
        $this->public_title = MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_PUBLIC_TITLE;
        $this->description = MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_PAYPAL_PARTNER_SORT_ORDER') ? MODULE_PAYMENT_PAYPAL_PARTNER_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_PAYPAL_PARTNER_STATUS') && (MODULE_PAYMENT_PAYPAL_PARTNER_STATUS == 'True') ? true : false;
        $this->order_status = defined('MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_STATUS_ID') && ((int) MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_STATUS_ID > 0) ? (int) MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_STATUS_ID : 0;
        $this->paid_status = defined('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTIONS_ORDER_STATUS_ID') && ((int) MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTIONS_ORDER_STATUS_ID > 0) ? (int) MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTIONS_ORDER_STATUS_ID : 0;

        $this->refund_status = defined('MODULE_PAYMENT_PAYPAL_PARTNER_CANCEL_ORDER_STATUS_ID') && ((int) MODULE_PAYMENT_PAYPAL_PARTNER_CANCEL_ORDER_STATUS_ID > 0) ? (int) MODULE_PAYMENT_PAYPAL_PARTNER_CANCEL_ORDER_STATUS_ID : 0;

        $this->online = true;

        if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_STATUS')) {
            if (MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER == 'Sandbox') {
                $this->title .= ' [Sandbox]';
                $this->public_title .= ' (' . $this->code . '; Sandbox)';
            }
        }

        if ($this->enabled === true && !$this->_isReady()) {
            $this->description = '<div class="secWarning">' . MODULE_PAYMENT_PAYPAL_PARTNER_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;
            $this->enabled = false;
        }

        if ( $this->enabled  && \Yii::$app->id=='app-frontend' && $this->currentPageButtonsLayout() == 'False') { //|| \Yii::$app->controller->id == 'payer')
            $this->enabled = $this->checkMessageOnProduct();//PP Paylater message could be displayed on all pages :(
        }

        if ( in_array(\Yii::$app->controller->id, ['callback', 'checkout', 'payer'])) {
            $this->pp_commit = 'true';
        }


        if ($this->enabled === true 
            //&& \Yii::$app->id != 'app-pos'&& \Yii::$app->id != 'app-console'
            //&& \Yii::$app->controller->id != 'modules'
            && \Yii::$app->id == 'app-frontend'
            && \Yii::$app->controller->id != 'callback') {
            //$this->doCheckoutInitializationOnInactive = true;
            //load JS libraries etc regardless selected address
            $this->checkout_initialization_method_js();

            $this->update_status();
            if ($this->enabled === false) {
                $this->doCheckoutInitializationOnInactive = true;
            }
        }
    }

    private function currentPageButtonsLayout() {
        $ret = 'False';
        if (\Yii::$app->controller->id == 'callback' || (\Yii::$app->controller->id == 'checkout' && \Yii::$app->controller->action->id == 'process')) {
            $ret = 'True';
        } elseif (defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUY_IMMEDIATELLY') && \Yii::$app->controller->id == 'catalog') {
            $ret = MODULE_PAYMENT_PAYPAL_PARTNER_BUY_IMMEDIATELLY;
        } elseif (defined('MODULE_PAYMENT_PAYPAL_PARTNER_SHOPPING_CART') && \Yii::$app->controller->id == 'shopping-cart') {
            $ret = MODULE_PAYMENT_PAYPAL_PARTNER_SHOPPING_CART;
        } elseif (defined('MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT_LOGIN') && \Yii::$app->controller->id == 'checkout' && \Yii::$app->controller->action->id == 'login') {
            $ret = MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT_LOGIN;
        } elseif (defined('MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT') &&  
                    ((\Yii::$app->controller->id == 'checkout' && \Yii::$app->controller->action->id != 'login') ||
                    (\Yii::$app->controller->id == 'payer') )
                )
        {
            $ret = MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT;
        }
        if ($ret == 'Fields') {
            $ret = 'Vertical';
        }
        return $ret;
    }

    function update_status() {
        $check_flag = true;
        if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_ZONE') && ((int) MODULE_PAYMENT_PAYPAL_PARTNER_ZONE > 0)) {
            $get = \Yii::$app->request->get();
            //callback could change address.
            //check it here
            if (\Yii::$app->controller->id == 'callback' && \Yii::$app->controller->action->id == 'webhooks' && $get['action'] == 'patchOrder') {
                try {
                    $tmp = file_get_contents("php://input");
                    $request = json_decode($tmp, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
                } catch (\Exception $e) {
                   \Yii::warning(" pppPatchOrderEmptyData #### " .print_r($e->getMessage(), true), 'TLDEBUG');
                }
                if (!empty($request['shipping_address'])) {
                    $country = \common\helpers\Country::get_country_info_by_iso($request['shipping_address']['country_code']);
                    if (is_array($country)) {
                        $zone = \common\helpers\Zones::lookupZone($country['id'], $request['shipping_address']['state']);
                        $zone_id = empty($zone['zone_id'])?0:$zone['zone_id'];
                        
                        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYPAL_PARTNER_ZONE . "' and zone_country_id = '" . $country['id'] . "' order by zone_id");
                        while ($check = tep_db_fetch_array($check_query)) {
                            if ($check['zone_id'] < 1) {
                                $check_flag = true;
                                break;
                            } elseif ($check['zone_id'] == $zone_id) {
                                $check_flag = true;
                                break;
                            }
                        }
                    }
                }
            } else {
                $check_flag = parent::checkStatusByZone(MODULE_PAYMENT_PAYPAL_PARTNER_ZONE);
            }

        }

        if ($check_flag == false) {
            $this->enabled = false;
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_VIRTUAL_GUEST') && MODULE_PAYMENT_PAYPAL_PARTNER_VIRTUAL_GUEST == 'True'
            && \Yii::$app->controller->id == 'shopping-cart'
            && is_object($this->manager) && !$this->manager->isShippingNeeded() && !$this->manager->getCustomerAssigned()) {
            $this->enabled = 0;
        }

    }

    public function updateTitle($platformId = 0) {
        $mode = $this->get_config_key((int) $platformId, 'MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER');
        if ($mode !== false) {
            $mode = strtolower($mode);
            $title = (defined('MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_TITLE') ? constant('MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_TITLE') : '');
            if ($title != '') {
                $this->title = $title;
                if ($mode == 'sandbox') {
                    $this->title .= ' [Sandbox]';
                }
            }
            $titlePublic = (defined('MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_PUBLIC_TITLE') ? constant('MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_PUBLIC_TITLE') : '');
            if ($titlePublic != '') {
                $this->public_title = $titlePublic;
                if ($mode == 'sandbox') {
                    $this->public_title .= " [{$this->code}; Sandbox]";
                }
            }
            return true;
        }
        return false;
    }

    public function checkButtonOnProduct($only=false) {
        return (
                \Yii::$app->controller->id != 'catalog' ||
                (defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUY_IMMEDIATELLY') && MODULE_PAYMENT_PAYPAL_PARTNER_BUY_IMMEDIATELLY != 'False')
            ) || (!$only && $this->checkMessageOnProduct());
    }

    public function checkCheckoutFields() {
        return (
                \Yii::$app->id == 'app-frontend' &&
                \Yii::$app->controller->id != 'catalog' &&
                defined('MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT') && MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT == 'Fields'
            );
    }

    protected function checkMessageOnProduct() {
        return defined('MODULE_PAYMENT_PAYPAL_PARTNER_PAY_LATER') && MODULE_PAYMENT_PAYPAL_PARTNER_PAY_LATER == 'True';
    }

    /**
     * returns required HTML containers for buttons/messages.
     * @staticvar int $idx on product listing there could be several pay buttons
     * @param int $index rudiment??
     * @return string  - HTML - payment buttons container
     */
    function checkout_initialization_method($index = 0) {
        $ret = '';
        static $idx = 0;
        //$this->checkout_initialization_method_js($index);
        if ($this->checkButtonOnProduct(true)) {
            $idx++;
            $ret = '<div class="paypal-button-container" id="paypal-button-container-' . $idx . '"></div>';
        }
        return $ret ;
    }
    private function isCheckout() {
        return \Yii::$app->controller->id == 'checkout' || \Yii::$app->controller->id == 'payer';
    }

/**
 * JS to render PP buttons and messages.
 * @return string JS
 */
    public function getJavascript() {
        $locale = \Yii::$app->settings->get('locale');
        if ($this->manager->has('ppartner_total_check') && \Yii::$app->controller->id == 'checkout') return;// returned after paypal to confirm final amount

        if (\Yii::$app->controller->id != 'catalog' || $this->checkButtonOnProduct(true)) {
//apm + CC
            if ($this->checkCheckoutFields() && $this->isCheckout()) {
                $size = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SIZE') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SIZE : 'small';
                $color = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_COLOR') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_COLOR : 'gold';
                $shape = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SHAPE') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SHAPE : 'pill';
                $label = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LABEL') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LABEL : 'checkout';
                $layout = strtolower($this->currentPageButtonsLayout());
                $generalError = defined('MODULE_PAYMENT_PAYPAL_PARTNER_GENERAL_ERROR') ? MODULE_PAYMENT_PAYPAL_PARTNER_GENERAL_ERROR : "Can't create order";
                $generalError = htmlspecialchars($generalError);
                $createUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'createOrder', 'partlypaid' => $this->isPartlyPaid()]); //must return OrderId (from paypal)
                $retrieveUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'retrieveOrder', 'partlypaid' => $this->isPartlyPaid()]);
                $shippingUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'patchOrder']);
                $currency = \Yii::$app->settings->get('currency');
                $customersDetails = '';
                //$puoi_agreement = '';
                //if ($this->billing['country']['countries_iso_code_2'] == 'DE') {                }
                $puoi_agreement = str_replace(["\'", "\n"], ["\\'", ''], MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_UPON_INVOCE);


                //'frmCheckoutConfirm' : 'frmCheckout'
                try {
                    $form = $this->manager->getShippingForm();
                    if (!$this->manager->getCustomerAssigned() || !$form || !$form->customerAddressIsReady() || !$form->hasErrors()) {
                    //if (!$this->manager->getCustomerAssigned()) {
                        $customersDetails = ",{
                                            method: 'POST',
                                            headers: {'Content-Type':'application/x-www-form-urlencoded'},
                                            body: $('#frmCheckout').serialize()
                                        }";
                    }
                } catch (\Exception $e ) {
                    \Yii::warning(print_r($e->getMessage() . ' ' . $e-> getTraceAsString(), true), 'TLDEBUG');
                }
                return <<<EOD
            if ($('.paypal-button-container').length>0){

                var pppButtonsParams = {
                    locale: '{$locale}',
                    style: {
                        size: '{$size}',
                        color: '{$color}',
                        shape: '{$shape}',
                        label: '{$label}',
                        layout: '{$layout}',
                    },
                    createOrder: function (data, actions) {
                                return fetch('{$createUrl}' {$customersDetails})
                          .then(function(res) {
                                return res.json();
                          }).then(function(data) {

                                try {
                                    if (data.hasOwnProperty('error') && data.hasOwnProperty('message') ) {
                                        alertMessage(data.message);
                                    }
                                    //return '';
                                } catch ( e ) {
                                  console.log(e);
                                  return false
                                }
                                return data.id;
                          });
                    },
                    onApprove: function (data, actions) {
                        $('body').append('<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="preloader"></div></div>');
                        return fetch('{$retrieveUrl}&id=' + data.orderID, {
                            method: 'POST'
                        }).then(function(res) {
                            return res.json();
                        }).then(function(res) {
                            if (!res.ok) {
                                $('.popup-box-wrap').remove();
                                alertMessage(res.error);
                            } else {
                                window.location.href = res.url;
                            }
                        });
                    },
                    onShippingChange: function(data,actions) {
                        return fetch('{$shippingUrl}', {
                                        method: 'POST',
                                        headers: {
                                           'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: JSON.stringify(data)
                                    }).then(function(res) {
                                        return res.json();
                                    }).then(function(data) {
                                        if (data.length == 0) {
                                          return actions.reject();
                                        } else if (data.same == 1) {
                                          return actions.resolve();
                                        }
                                        return actions.order.patch(data);
                                    });
                    },
/*product page                    onClick: function(data, actions) {
                        var pppBtn = $('.paypal-button-container');
                        if (pppBtn && pppBtn.length>0){
                            var  that = pppBtn[0];
                            var theForm = $(that).parents('form');
                            if (theForm.attr('name') == 'cart_quantity' || theForm.hasClass('form-buy')){

                                var _data = theForm.serializeArray();
                                _data.push({name: 'popup', value: 'true'});
                                _data.push({name: 'purchase', value: '{$this->code}'});

                                $.ajax({
                                    url: theForm.attr('action'),
                                    data: _data,
                                    async: false,
                                    type: theForm.attr('method'),
                                    success: function(data){
                                        //just wait for adding product to cart
                                        }
                                });
                            }
                        }
                    },/*product page  */
                    onError: function(err){
                            if (false && err != '') {
                                 alert(err);
                            } else {
                                 alert("{$generalError}");
                            }
                    },
                    onCancel: function(err){

                    }
                };

                document.querySelectorAll('.paypal-button-container').forEach(function(selector) {
                    paypal.getFundingSources().forEach(function(fundingSource) {
                        var pbParams = pppButtonsParams;
                        if (fundingSource != 'paypal') {
                            pbParams.style = {};
                        }
                        pbParams.fundingSource =  fundingSource;
                        var button = paypal.Buttons(pbParams);
                        if (button.isEligible()) {
                            var container;
                            container = document.createElement('span');
                            container.classList.add('paypal-button-{$this->code}_' + fundingSource);
                            selector.appendChild(container);
                            button.render(container);
                        }
                    });
                    if ($('.item-payment.paypal_partner_pui').length>0) {
                        var container;
                        container = document.createElement('span');
                        container.classList.add('paypal-button-{$this->code}_pui');
                        var agreement = btncontainer = '';
                        agreement = document.createElement('span');
                        btncontainer = document.createElement('span');
                        container.classList.add('paypal-button-{$this->code}-agreement');
                        agreement.innerHTML = '{$puoi_agreement}';

                        container.appendChild(agreement);
                        container.appendChild(btncontainer);

                        selector.appendChild(container);
                        container = btncontainer;
                    }

                });
            }
            $(window).on('checkout_worker_complete', function (event) {
                var price = 0;
                try {
                    price = parseFloat($('.price-row.total.ot_total input.ot_total_clear').val()) || 0;
                    price = price.toFixed(2);
                } catch (e) { price = 0; }

                $('.pp-pay-later-message').attr('data-pp-amount', price);
            });

function paypalFieldsCallback(){

    if (paymentCollection.form) {

        var bodyData = '';
        if (paymentCollection.form) {
            bodyData = $(paymentCollection.form).serialize();
        }
        var method = paymentCollection.getCurrentPayment();
        method = method.substring(method.lastIndexOf('_')+1);

        if (typeof window['{$this->code}_callback_' + method] == 'function'){
            try {
                window['{$this->code}_callback_' + method]();
            } catch (e ) {
                console.log(e);
            }
        }


    }
    return false;

};

EOD;
/*
        fetch("$whSaveTmpOrderURL", {
                    method: "POST",
                    headers: {
                      "Content-Type": "application/json"
                    },
                    body: bodyData
                }).then(function(result) {
                    return result.json();
                })
                .then(function(data) {
                    //!payWithCard finish calback
                    if (!data || !data.clientSecret) {
                        if (data && data.error) {
                            showError(data.error);
                        } else {
                            showError('$general_error');
                        }
                    } else {
                        payWithCard(paymentCollection.stripes.stripe, paymentCollection.stripes.card, data.clientSecret, data.token);
                    }

                });*/
            } else {

                $size = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SIZE') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SIZE : 'small';
                $color = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_COLOR') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_COLOR : 'gold';
                $shape = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SHAPE') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SHAPE : 'pill';
                $label = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LABEL') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LABEL : 'checkout';
                $layout = strtolower($this->currentPageButtonsLayout());
                $generalError = defined('MODULE_PAYMENT_PAYPAL_PARTNER_GENERAL_ERROR') ? MODULE_PAYMENT_PAYPAL_PARTNER_GENERAL_ERROR : "Can't create order";
                $generalError = htmlspecialchars($generalError);
                $createUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'createOrder', 'partlypaid' => $this->isPartlyPaid()]); //must return OrderId (from paypal)
                $retrieveUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'retrieveOrder', 'partlypaid' => $this->isPartlyPaid()]);
                $shippingUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'patchOrder']);
                $currency = \Yii::$app->settings->get('currency');
                $customersDetails = '';
                //'frmCheckoutConfirm' : 'frmCheckout'
                try {
                    $form = $this->manager->getShippingForm();
                    if (!$this->manager->getCustomerAssigned() || !$form || !$form->customerAddressIsReady() || !$form->hasErrors()) {
                    //if (!$this->manager->getCustomerAssigned()) {
                        $customersDetails = ",{
                                            method: 'POST',
                                            headers: {'Content-Type':'application/x-www-form-urlencoded'},
                                            body: $('#frmCheckout').serialize()
                                        }";
                    }

                } catch (\Exception $e ) {
                    \Yii::warning(print_r($e->getMessage() . ' ' . $e-> getTraceAsString(), true), 'TLDEBUG');
                }
                return <<<EOD
                if ($('.paypal-button-container').length>0){
                //paypal.Buttons({
                var pppButtonsParams = {
                    locale: '{$locale}',
                    style: {
                        size: '{$size}',
                        color: '{$color}',
                        shape: '{$shape}',
                        label: '{$label}',
                        layout: '{$layout}',
                    },
                    createOrder: function (data, actions) {
                                return fetch('{$createUrl}' {$customersDetails})
                          .then(function(res) {
                                return res.json();
                          }).then(function(data) {

                                try {
                                    if (data.hasOwnProperty('error') && data.hasOwnProperty('message') ) {
                                        alertMessage(data.message);
                                    }
                                    //return '';
                                } catch ( e ) {
                                  console.log(e);
                                  return false
                                }
                                return data.id;
                          });
                    },
                    onApprove: function (data, actions) {
                        $('body').append('<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="preloader"></div></div>');
                        return fetch('{$retrieveUrl}&id=' + data.orderID, {
                            method: 'POST'
                        }).then(function(res) {
                            return res.json();
                        }).then(function(res) {
                            if (!res.ok) {
                                $('.popup-box-wrap').remove();
                                alertMessage(res.error);
                            } else {
                                window.location.href = res.url;
                            }
                        });
                    },
                    onShippingChange: function(data,actions) {
    //console.log("onShippingChange", data, actions);
                        return fetch('{$shippingUrl}', {
                                        method: 'POST',
                                        headers: {
                                           'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: JSON.stringify(data)
                                    }).then(function(res) {
                                        return res.json();
                                    }).then(function(data) {
                                        if (data.length == 0) {
                                          return actions.reject();
                                        } else if (data.same == 1) {
                                          return actions.resolve();
                                        }
                                        return actions.order.patch(data);
                                    });
                    },
                    onClick: function(data, actions) {
                        var pppBtn = $('.paypal-button-container');
                        if (pppBtn && pppBtn.length>0){
                            var  that = pppBtn[0];
                        var theForm = $(that).parents('form');
                        if (theForm.attr('name') == 'cart_quantity' || theForm.hasClass('form-buy')){
                        /*
                            //theForm.removeClass('set-popup');
                            theForm.append('<input type="hidden" name="purchase" value="{$this->code}">').submit();
                            $(".popup-box:last").trigger("popup.close");$(".popup-box-wrap").remove();
                        */
                            var _data = theForm.serializeArray();
                            _data.push({name: 'popup', value: 'true'});
                            _data.push({name: 'purchase', value: '{$this->code}'});

                            $.ajax({
                                url: theForm.attr('action'),
                                data: _data,
                                async: false,
                                type: theForm.attr('method'),
                                success: function(data){
                                    //just wait for adding product to cart
                                    }
                            });
                            }
                        }
                    },
                    onError: function(err){
                            if (false && err != '') {
                                 alert(err);
                            } else {
                                 alert("{$generalError}");
                            }
                    },
                    onCancel: function(err){

                    }
                //});
                };

                document.querySelectorAll('.paypal-button-container').forEach(function(selector) {
                    paypal.Buttons(pppButtonsParams).render(selector);
                });
            }
            $(window).on('checkout_worker_complete', function (event) {
                var price = 0;
                try {
                    price = parseFloat($('.price-row.total.ot_total input.ot_total_clear').val()) || 0;
                    price = price.toFixed(2);
                } catch (e) { price = 0; }

                $('.pp-pay-later-message').attr('data-pp-amount', price);
            });
EOD;
            }
        }
    }

    public function directPayment() {//to use callback???
        return ($this->checkCheckoutFields() && $this->isCheckout());
    }


/**
 * register JS libs and functions in view (added to <head>)
 * @param int $index rudiment
 */
    function checkout_initialization_method_js($index = 0) {
        if (empty($this->jsIncluded)) {
            //$clid = MODULE_PAYMENT_PAYPAL_PARTNER_API_APP_CLIENT_ID;
            $this->jsIncluded = true;
            $clid = $this->_getClientId();
            $seller = $this->getSeller(\common\classes\platform::currentId());
            if ($seller->isOnBoarded()) {
                $currency = \Yii::$app->settings->get('currency');
                $locale = \Yii::$app->settings->get('locale');
                if ($this->hasOwnKeys()) {
                    $mid = '';
                } else {
                    $mid = '&merchant-id=' . $seller->payer_id;
                }

                $df = $ef = '';
                $tmp = $this->getFundings();

                if (!empty($tmp)) {
                    if (!empty($tmp['disabled'])) {
                        $_disabled = preg_replace(['/^paypal,/', '/,paypal,/', '/^pui,/', '/,pui,/', '/,pui$/'], ['', ',','', ',',''], $tmp['disabled']);
                        $df = '&disable-funding=' . $_disabled;
                    }
                    if (!empty($tmp['enabled'])) {
                        // paylater option is not shown in test more if paypal in allowed fundings :(
                        $_enabled = preg_replace(['/^paypal,/', '/,paypal,/', '/^pui,/', '/,pui,/', '/,pui$/'], ['', ',','', ',',''], $tmp['enabled']);
                        $ef = '&enable-funding=' . $_enabled;
                    }
                }
                $_comp = []; $components = '';
                if ($this->checkMessageOnProduct()) {
                    $_comp[] = 'messages';
                }
                if ($this->checkButtonOnProduct(true)) {
                    $_comp[] = 'buttons';
                }

                $clientToken = [];
                if ($this->checkCheckoutFields() && $this->isCheckout()) {
                    $_comp = array_merge($_comp, ['buttons', 'payment-fields', 'marks', 'funding-eligibility']);
                    if ($cToken = $this->generateClientToken()) {
                        $clientToken = ['data-client-token' => $cToken];
                        $_comp[] = 'hosted-fields';
                    }
                    \Yii::$app->getView()->registerCss('.payment-method .item-radio .inline-block { display: inline-block;vertical-align: middle;padding: 0 5px 0 0;} .payment-method .item-radio .paypal-mark span {overflow: visible;display: inline-block;padding-left: 5px;} .paypal-button-paypal_partner-agreement {text-align: justify; display: inline-block;} .payment-method .payment_class_paypal_partner .item-radio label {display: inline-block; }');
                }

                if (!empty($_comp)) {
                    $_comp = array_unique($_comp);
                    $components = '&components=' . implode(',', $_comp);
                }
                $commit = '&commit=' . $this->pp_commit;
                $buyerCountry = '';
                if ($this->getMode() !== 'Live') {
                    if (!$this->manager->getCustomerAssigned() && defined('STORE_COUNTRY') && intval(STORE_COUNTRY)>0) {
                        $tmp = \common\helpers\Country::get_country_info_by_id(STORE_COUNTRY);
                        $buyerCountry = '&buyer-country=' . $tmp['countries_iso_code_2'];

                    } elseif(!empty($this->manager->getBillingAddress())) {

                        $tmp = $this->manager->getBillingAddress();
                        if (isset($tmp['country']['iso_code_2'])) {
                            $buyerCountry = '&buyer-country=' . $tmp['country']['iso_code_2'];
                        }
                    }
                }

//debug
/** /
$df = $ef = '';
$ef = '&enable-funding=sofort';
//debug eom */

                \Yii::$app->getView()->registerJsFile("https://www.paypal.com/sdk/js?client-id={$clid}{$mid}{$ef}{$components}{$commit}{$buyerCountry}&intent=" . ($this->_getIntent() == 'authorize' ? "authorize" : "capture") . '&currency=' . $currency . $df .'&locale=' . $locale . (0 && $this->debug ? '&debug=true' : ''), ['position' => \common\components\View::POS_HEAD, 'data-partner-attribution-id' => $this->getAttributionId() ] + $clientToken);

                \Yii::$app->getView()->registerJs($this->getJavascript());
                //pos_end to wrap with 'tl()'
                \Yii::$app->getView()->registerJsFile(\frontend\design\Info::themeFile('/js/bootstrap-datepicker.js'), ['position' => \common\components\View::POS_END]);
                \Yii::$app->getView()->registerCssFile(\frontend\design\Info::themeFile('/css/bootstrap-datepicker.css'));
                
                \Yii::$app->getView()->registerJs($this->getJS());
            }
        }
    }

    function javascript_validation() {
        return false;
    }

    /**
     * checkout - functions for onChange payment event (depends on current state etc)
     * @return string JS
     */
    public function getJS() {
        if (strpos(\Yii::$app->controller->id, 'checkout') === false && \Yii::$app->controller->id != 'payer') return; // not needed on shopping cart and product pages
        if ( \Yii::$app->controller->id == 'quote-checkout') // 2do correct according quote checkout settings
        {

            $quoteCheckout = 1; // in JS also
            $unCheckedJSString = '';
        } else {

            $quoteCheckout = 0;
            $unCheckedJSString = <<<EOD
                $('#frmCheckout button[type="submit"]').show();
                $('#frmCheckout button[type="submit"]').prop('disabled', false);
                $('[data-name^="checkout"] .paypal-button-container').hide();
                $('.paypal-button-container').parents('.add-buttons').prev('.or-text').hide();
EOD;
        }

        if (
            ($this->manager->has('ppartner_total_check') && \Yii::$app->controller->id == 'checkout') ||
            $quoteCheckout
            ) {
            //final payment confirmation after return from PP
            //change button text, don't show PP buton(s)
            $btnConfirm = str_replace(['"'], ['\"'], defined('IMAGE_BUTTON_CONTINUE')?constant('IMAGE_BUTTON_CONTINUE'):'Continue');
            $btnPay = str_replace(['"'], ['\"'], defined('TEXT_CONFIRM_AND_PAY')?constant('TEXT_CONFIRM_AND_PAY'):'Pay With Card');
            $skipQuoteCheckout = !$quoteCheckout;
            return <<<EOD
window.toggleSubFields_{$this->code} = function () {
        $unCheckedJSString
        if ({$skipQuoteCheckout} && $('input[name=payment][value="{$this->code}"]').is(':checked')){
            $('#frmCheckout button[type="submit"] .btn-title').html("{$btnConfirm}");
        } else {
            $('#frmCheckout button[type="submit"] .btn-title').html("{$btnPay}");
        }

    }
    if (typeof tl == 'function'){
        tl(function(){
            window.toggleSubFields_{$this->code}();
            try {
                checkout_payment_changed.set('window.toggleSubFields_{$this->code}');
            }catch (e ) {}
        })
    }
EOD;
        } else {
            //usual checkout
            if (defined('EXPRESS_PAYMENTS_HIDE_CHECKOUT') && EXPRESS_PAYMENTS_HIDE_CHECKOUT == 'True') {
                $checkedJSString = <<<EOD
                    $('#frmCheckout button[type="submit"]').hide();
                    $('#frmCheckout button[type="submit"]').prop('disabled', true);
                    $('.paypal-button-container').show();
                    //$('.paypal-button-container').parents('.add-buttons').prev('.or-text').show();
EOD;
                $unCheckedJSString = <<<EOD
                    $('#frmCheckout button[type="submit"]').show();
                    $('#frmCheckout button[type="submit"]').prop('disabled', false);
                    $('[data-name^="checkout"] .paypal-button-container').hide();
                    $('.paypal-button-container').parents('.add-buttons').prev('.or-text').hide();
EOD;

            } else {
                $checkedJSString = <<<EOD
                    $(window).trigger('disable-checkout-button', { name: 'paypal_partner', value: true})
                    $('#frmCheckout button[type="submit"]').prop('disabled', true);//VL2 check
                    $('.paypal-button-container').css('opacity', '1');
EOD;
                $unCheckedJSString = <<<EOD
                    $(window).trigger('disable-checkout-button', { name: 'paypal_partner', value: false})
                    $('#frmCheckout button[type="submit"]').prop('disabled', false);//VL2 check
                    $('[data-name^="checkout"] .paypal-button-container').css('opacity', '0.5');
EOD;

            }
            $apmsWithFields = array_keys(array_filter($this->getAPMTemplates()));
            $apmsWithFieldsStr = 'var apmsWithFields = ["' . implode('","', $apmsWithFields) . '"];';
            $apmsWithoutOwnButton = 'var apmsWithoutOwnButton = ["' . implode('","', $this->apmsWithoutOwnButton()) . '"];';
            $apmsWithAgreement = 'var apmsWithAgreement = ["' . implode('","', $this->apmsWithAgreement()) . '"];';

            $apmsWithCallbacks = array_keys(array_filter($this->getAPMJSCallbacks()));
            $apmsWithCallbacksStr = 'var apmsWithCallbacks = ["' . implode('","', $apmsWithCallbacks) . '"];';
            if ($this->checkCheckoutFields() && $this->isCheckout()) {
///2do visibility of APM extra fields
                return <<<EOD
        //APM show available APMs and render Marks (icons)
        //By default all APM's radio buttons are hidden and then is displayed according allowed fundingSource
        window.paypalShowElegableOptions = function () {
            paypal.getFundingSources().forEach(function(fundingSource) {

                var button = paypal.Buttons({
                  fundingSource: fundingSource
                });
                if (button.isEligible()) {
                    $('.item-radio.item-payment.{$this->code}_' + fundingSource).show();
                } else {
//                    console.log('>> fundingSource ' + fundingSource + ' not eligible');
                }

                var mark = paypal.Marks({
                    fundingSource: fundingSource,
                });
                if (mark.isEligible() && $('.paypal-marks-icon-' + fundingSource).length) {
                    mark.render('.paypal-marks-icon-' + fundingSource);
                }

            });
        }
        window.toggleSubFields_{$this->code} = function () {
            {$apmsWithFieldsStr}{$apmsWithCallbacksStr}{$apmsWithoutOwnButton}{$apmsWithAgreement}
            $('input[name=payment][value^="{$this->code}_"]').not(':checked').each(function() {
                if ($('.paypal-fields-container.'+$(this).val()).length)
                    $('.paypal-fields-container.'+$(this).val()).hide();
                $('.paypal-button-container .paypal-button-'+$(this).val()).hide();
            });
            var fundingSource = '';
            try {
                fundingSource = $('input[name=payment]:checked').val();
                if (fundingSource.indexOf('paypal_partner_')<0) {
                    fundingSource = '';
                } else {
                    fundingSource = fundingSource.replace('paypal_partner_', '');
                }
            } catch ( e ) {
            }
            if (typeof window['paypal_render_subfields_' + fundingSource] == 'function'){
                try {
                    window['paypal_render_subfields_' + fundingSource]();
                } catch (e ) {
                    console.log(e);
                }
            } else {
//console.log('not a function ' + 'paypal_render_subfields_' + fundingSource);
            }

            //custom fields - use standard osc checkout button, else - provided by PayPal
            if ($('input[name=payment][value^="{$this->code}_"]').is(':checked')){
                if ($('.paypal-fields-container.'+$('input[name=payment]:checked').val()).length) {
                    $('.paypal-fields-container.'+$('input[name=payment]:checked').val()).show();
                }
                //$('.paypal-button-container .paypal-button-'+$('input[name=payment]:checked').val()).show();
            }
            //if ($('input[name=payment][value="{$this->code}_paypal"]').is(':checked')){
            //if (fundingSource != '' && apmsWithCallbacks.indexOf(fundingSource) < 0 ) {
            if (fundingSource != '' && apmsWithoutOwnButton.indexOf(fundingSource) < 0 ) {
                {$checkedJSString}
                $('[data-name^="checkout"] .paypal-button-container').removeClass('button-hide');
                $('#frmCheckout button[type="submit"]').addClass('button-hide');
                $('.paypal-button-container .paypal-button-'+$('input[name=payment]:checked').val()).show();
            } else {
                {$unCheckedJSString}
                if (fundingSource != '' && apmsWithAgreement.indexOf(fundingSource) != -1 ) { //has agreement and no button there
                    $('.paypal-button-container').show();
                    $('[data-name^="checkout"] .paypal-button-container').removeClass('button-hide');
                    $('.paypal-button-container .paypal-button-'+$('input[name=payment]:checked').val()).show();
                } else {
                    $('[data-name^="checkout"] .paypal-button-container').addClass('button-hide');
                }
                $('#frmCheckout button[type="submit"]').removeClass('button-hide');
            }

        }
        if (typeof tl == 'function'){
            tl(function(){
                try {
                    paypalShowElegableOptions();
                    checkout_payment_changed.set('window.toggleSubFields_{$this->code}'); //set callback
                }catch (e ) {console.log(e ); }
                window.toggleSubFields_{$this->code}();
            })
        }
EOD;
            } else {

                return <<<EOD
        window.toggleSubFields_{$this->code} = function () {
            if ($('input[name=payment][value="{$this->code}"]').is(':checked')){
            {$checkedJSString}
                $('[data-name^="checkout"] .paypal-button-container').removeClass('button-hide');
                $('#frmCheckout button[type="submit"]').addClass('button-hide');
            } else {
            {$unCheckedJSString}
                $('[data-name^="checkout"] .paypal-button-container').addClass('button-hide');
                $('#frmCheckout button[type="submit"]').removeClass('button-hide');
            }

        }
        if (typeof tl == 'function'){
            tl(function(){
                window.toggleSubFields_{$this->code}();
                try {
                    checkout_payment_changed.set('window.toggleSubFields_{$this->code}');
                }catch (e ) {console.log(e ); }
            })
        }
EOD;
            }
        }
    }

    function selection() {

        \Yii::$app->getView()->registerCss('#frmCheckout button[type="submit"]:disabled {opacity:0.5;cursor: not-allowed;}');
        if (defined('EXPRESS_PAYMENTS_HIDE_CHECKOUT') && EXPRESS_PAYMENTS_HIDE_CHECKOUT == 'True') {
            \Yii::$app->getView()->registerCss('#frmCheckout button[type="submit"]:disabled,  .w-checkout-continue-btn .or-text:first-of-type {display:none}');
        }

        if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT') && MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT !== 'False')
        {
            if ($this->checkCheckoutFields()) {

                $this->registerCallback("paypalFieldsCallback");// common callback for all APMs, it'll call callback of selected method (if any)
                
                $methods = $apms = [];
                $apms = $this->getAPMTemplates();

                foreach ($apms as $method => $apmTemplate) {
                    $extra = '';
                    $_title = self::getFundingTitle($method);
                    if (!empty($apms)) {
                        \Yii::$app->getView()->registerJs($this->getAPMJSCallback($method));
                        $extra .= '<div class="paypal-fields-container paypal-fields-' . $method . ' ' . $this->code . '_' . $method . '" style="display:none">' . $apmTemplate . '</div>';
                        $tmp = $this->getAPMJS($method);
                        if (!empty($tmp)) {
                            $extra .= '<script>' . $tmp . '</script>';
                        }
                    }
                    if ($method=='pui' && empty($apmTemplate)) {
                        continue;
                        $_title = defined('TEXT_PAY_UPON_INVOICE')?TEXT_PAY_UPON_INVOICE:'Kauf auf Rechnung';
                    }

                    $methods[] = [
                        'id' => $this->code . '_' . $method,
                        'module' => '<span class="inline-block"><span class="inline-block paypal-marks-icon paypal-marks-icon-' . $method . '"></span><span class="inline-block method">' .  $_title . '</span></span>' . $extra,
                        'hide' => (!in_array($method, ['paypal', 'pui'])), // show by JS as a lot could be not available for selected country
                    ];
                }
                if (count($methods)) {
                    $methods[count($methods)-1]['module'] .= '<script>if (typeof window.paypalShowElegableOptions == "function") window.paypalShowElegableOptions();</script>';
                    return array(
                        'id' => $this->code,
                        'module' => $this->public_title,
                        'methods' => $methods,
                    );
                }

            } else {
                return array('id' => $this->code,
                             'module' => $this->public_title);
            }
        }
        return false;
    }

    function pre_confirmation_check() {

    }

    function confirmation() {

        $comments = $this->manager->get('comments');

        if (!isset($comments)) {
            $comments = null;
        }

        $confirmation = false;

        if (empty($comments)) {
            $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_PAYPAL_PARTNER_TEXT_COMMENTS,
                  'field' => tep_draw_textarea_field('ppecomments', 'soft', '60', '5', $comments))));
        }

        return $confirmation;
    }

    function process_button() {
        return false;
    }

    function before_process() {

        if (!$this->manager->has('partner_order_id')) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
        }

        /** @var \common\classes\Order $order*/
        $order = $this->manager->getOrderInstance();

        $response = $this->getOrder($this->manager->get('partner_order_id'));

        $direct = false;//direct payment from site - save not paid order with "redirected to " status
        if ($this->manager->has('partner_order_pending')) {
            $tmp = $this->manager->get('partner_order_pending');
        }
        if (!empty($response->result->status) && in_array(strtoupper($response->result->status), ['PENDING_APPROVAL','COMPLETED', 'CREATED']) && $tmp == 'pay_upon_invoice') {
            $direct = true;
        }

        if (!empty($response->result->status) && strtoupper($response->result->status) == 'APPROVED') {
            if ($this->formatRaw($order->info['total_inc_tax']) != $response->result->purchase_units[0]->amount->value && !$this->manager->has('ppartner_total_check')) {
\Yii::warning("pppbeforeprocess total changed ## " .print_r($this->formatRaw($order->info['total_inc_tax']) . '!= '. $response->result->purchase_units[0]->amount->value, true), 'TLDEBUG');
                $this->manager->set('ppartner_total_check', true);

                tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
            }

            if ($this->manager->has('ppartner_total_check')) {
                try {
                    $response = $this->updateOrder($this->manager->get('partner_order_id'));
                    $this->manager->remove('ppartner_total_check');
                    if (!$response) {
                        //order wasn't updates on PP - restart (else incorrect amount will be authorized/captured
                        tep_redirect($this->getCheckoutUrl(['error_message' => PAYPAL_PARTNER_RESTART], self::PAYMENT_PAGE));
                    }
                } catch (\Exception $ex) {
                    $this->sendDebugEmail($ex);
                    tep_redirect($this->getCheckoutUrl(['error_message' => PAYPAL_PARTNER_RESTART], self::PAYMENT_PAGE));
                }
            }
        } elseif (!$direct && !$this->successCardPayment($response)) {
            tep_redirect($this->getCheckoutUrl(['error_message' => PAYPAL_PARTNER_RESTART_GENERAL], self::PAYMENT_PAGE));
        }
        $_paid = false;
        //ssie save order to get ID
        if ($direct || $this->saveOrderBefore() == 'Order') {
            $invoiceId = $orderId = $this->saveOrderBySettings($direct);
        } else {
            $orderId = $this->estimateOrderId();
            $invoiceId = $orderId . '-e-' . date('ymdHis');
        }

        //updateOrderInvoiceId on PayPal
        if (!empty($orderId) && ($orderId != intval($response->result->purchase_units[0]->invoice_id) || $invoiceId == $orderId) ) {
            try {
                $this->updateOrderInvoiceId($this->manager->get('partner_order_id'), $invoiceId);
            } catch (\Exception $e) {
                //if not $direct not critical at all - estimated invoice id on PP Direct - no approval WH
                \Yii::warning(" #### " .print_r($e->getMessage() . ' ' . $e->getTraceAsString(), true), 'exception_' . $this->code);
            }
        }

        if ($direct && $orderId) {
            $this->no_process($order);
            $order->notify_customer($order->getProductsHtmlForEmail(),[]);
            $this->no_process_after($order);
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'order_id=' . $orderId, 'SSL'));
        }

        try {            

            if ($this->_getIntent() == 'authorize') {
                $presponse = $this->authorizeOrder($this->manager->get('partner_order_id'));
                $rv = $presponse->result->purchase_units[0]->payments->authorizations[0]->id;
            } else {
                $presponse = $this->captureOrder($this->manager->get('partner_order_id'));
                $rv = $presponse->result->purchase_units[0]->payments->captures[0]->id;
                $_paid = true;
            }
        } catch (\Exception $e) {
            \Yii::warning(" #### " .print_r($e->getMessage() . ' ' . $e->getTraceAsString(), true), 'exception_' . $this->code);
        }

        if (!empty($presponse->result->status) && strtoupper($presponse->result->status) == "COMPLETED") {
            //completed request could have rejected payment - check additionally
            $payments = false;
            if (!empty($presponse->result->purchase_units[0]->payments->authorizations) && is_array($presponse->result->purchase_units[0]->payments->authorizations)) {
                $payments = $presponse->result->purchase_units[0]->payments->authorizations;
            } elseif (!empty($presponse->result->purchase_units[0]->payments->captures) && is_array($presponse->result->purchase_units[0]->payments->captures)) {
                $payments = $presponse->result->purchase_units[0]->payments->captures;
            }
            if ($payments) {
                $anyOk = false;
                foreach ($payments as $t) {
                    if (!empty($t->status) && !in_array(strtoupper($t->status), array('DENIED'))) {
                        $anyOk = true;
                    }
                }
                if (!$anyOk) {
                    tep_redirect($this->getCheckoutUrl(['error_message' => (($this->_getIntent() == 'authorize')?PAYPAL_PARTNER_RESTART_AUTHORIZE:PAYPAL_PARTNER_RESTART_CAPTURE)], self::PAYMENT_PAGE));
                }
            }

            $this->manager->set('partner_transaction_id', $rv);
            if ($_paid) { //captured - set paid order status w/o delay.
                $this->order_status = $this->paidOrderStatus();
                $order->setPaymentStatus($this->code);
            }

            if (!empty($orderId) && $this->saveOrderBefore() == 'Order') {
                $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $orderId);
                if ($order) {
                    if ($this->order_status) {
                        \common\helpers\Order::setStatus($orderId, $this->order_status);
                        $order->info['order_status'] = $this->order_status;
                    }
                    $order->update_piad_information(true);

                    $order->save_details();
                    $order->info['comments'] = '';

                    $order->notify_customer($order->getProductsHtmlForEmail(),[]);

                    $this->no_process($order);
                    $this->no_process_after($order);
                    /*
                    $this->trackCredits();
                    $this->after_process();

                    $this->manager->clearAfterProcess();

                    if ($ext = \common\helpers\Acl::checkExtension('ReferFriend', 'rf_after_order_placed')) {
                        $ext::rf_after_order_placed($order->order_id);
                    }

                    if ($ext = \common\helpers\Acl::checkExtension('Affiliate', 'CheckSales')) {
                        $ext::CheckSales($order);
                    }*/

                    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'order_id=' . $order->order_id, 'SSL'));

                }
            }
        } else {
            tep_redirect($this->getCheckoutUrl(['error_message' => (($this->_getIntent() == 'authorize')?PAYPAL_PARTNER_RESTART_AUTHORIZE:PAYPAL_PARTNER_RESTART_CAPTURE)], self::PAYMENT_PAGE));
            //tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'error_message=' . stripslashes("Amount could not be captured"), 'SSL'));
        }
    }
    
    private function successCardPayment($response) {
        $ret = false;
        if (!empty($response->result->payment_source->card)) {
/*3ds
    [result] => stdClass Object
        (
            [intent] => CAPTURE
            [status] => CREATED
            [payment_source] => stdClass Object
                (
                    [card] => stdClass Object
                        (
                            [last_digits] => 0349
                            [brand] => MASTERCARD
                            [type] => CREDIT
                            [authentication_result] => stdClass Object
                                (
                                    [liability_shift] => POSSIBLE
                                    [three_d_secure] => stdClass Object
                                        (
                                            [enrollment_status] => Y
                                            [authentication_status] => Y
                                        )
                                    [authentication_flow] => STEPUP
                                )
                        )
                )
  */


            $ret = true;
        }
        return $ret;
    }

    function after_process() {

//save auth/capture details in status history...
        $transactionID = $this->manager->get('partner_transaction_id');
        $deferred = 0;
        /** @var PayPalHttp\HttpResponse $transaction */
        if ($this->_getIntent() == 'authorize') {
            $transaction = $this->getAuthorization($transactionID);
            $deferred = 1;
        } else {
            $transaction = $this->getCapture($transactionID);
        }
        if ($transaction) {
            $order = $this->manager->getOrderInstance();

            $ppOrder = new \stdClass();
            if ($this->manager->has('partner_order_id')) {
                $ppId = $this->manager->get('partner_order_id');
            } elseif (!empty($transaction->result->supplementary_data->related_ids->order_id) ) {
                $ppId = $transaction->result->supplementary_data->related_ids->order_id;
            }
            if (!empty($ppId)) {
                $ppOrder = $this->getOrder($ppId);
            }
            $pp_result = $this->extractComments($ppOrder, $transaction);

            $invoice_id = $this->manager->getOrderSplitter()->getInvoiceId();
            $tm = $this->manager->getTransactionManager($this);

            $sk = $this->getStatusCode($transaction);
            if (!empty($transaction->result->status_details->reason)) {
                $comment = $transaction->result->status_details->reason;
            } else {
                $comment = 'Customer\'s payment ' . $transaction->result->amount->value . $transaction->result->amount->currency_code;
            }
            //$comment .= "\n" . implode("\n", $pp_result);

            $ret = $tm->updatePaymentTransaction($transactionID,
                [
                  'fulljson' => json_encode($transaction),
                  'status_code' => $sk,
                  'status' => $transaction->result->status,
                  'amount' => $transaction->result->amount->value,
                  'comments' => $comment . "\n" . implode("\n", $pp_result),
                  'date' => date('Y-m-d H:i:s', strtotime($transaction->result->update_time)),
                  'suborder_id' => $invoice_id,
                  'orders_id' => $order->order_id,
                  'deferred' => $deferred,
                // parent_transaction_id orders_id
            ]);

            $sql_data_array = array('orders_id' => $order->order_id,
              'orders_status_id' => $order->info['order_status'],
              'date_added' => 'now()',
              'customer_notified' => '0',
              'comments' => $comment . "($transactionID)");

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
        $this->manager->remove('partner_capture_id');
        $this->manager->remove('partner_order_id');

        if ($this->onBehalf()) {
            \Yii::$app->settings->set('from_admin', false);
            if (!\Yii::$app->user->isGuest) {
                \Yii::$app->user->getIdentity()->logoffCustomer();
            }
            echo '<script> window.parent.document.body.innerHTML = "<br><br><center>' . htmlspecialchars(TEXT_ON_BEHALF_PAYMENT_SUCCESSFUL) .  '</center>"; </script>';
            die;
        }
    }

    function get_error() {
        return false;
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_PAYMENT_PAYPAL_PARTNER_STATUS', 'True', 'False');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_PAYMENT_PAYPAL_PARTNER_SORT_ORDER');
    }

    public function configure_keys() {
        $status_id = defined('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTIONS_ORDER_STATUS_ID') ? MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTIONS_ORDER_STATUS_ID : $this->paidOrderStatus();
        $status_id_ch = defined('MODULE_PAYMENT_PAYPAL_PARTNER_CANCEL_ORDER_STATUS_ID') ? MODULE_PAYMENT_PAYPAL_PARTNER_CANCEL_ORDER_STATUS_ID : $this->refundOrderStatus();
        $status_id_o = defined('MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_STATUS_ID') ? MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_STATUS_ID : $this->getDefaultOrderStatusId();

        $params = array('MODULE_PAYMENT_PAYPAL_PARTNER_STATUS' => array('title' => 'Enable PayPal Checkout v2',
            'description' => 'Do you want to accept PayPal Checkout payments?',
            'value' => 'True',
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
          
          'MODULE_PAYMENT_PAYPAL_PARTNER_VIRTUAL_GUEST' => array('title' => 'Disable PayPal for guest if only virtual products in cart',
            'description' => 'PayPal does NOT provide billing address. Guest (express) order won\'t have any address',
            'value' => 'True',
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),

          'MODULE_PAYMENT_PAYPAL_PARTNER_BUY_IMMEDIATELLY' => array('title' => 'Show PayPal buttons on product',
            'description' => 'Allow to make PayPal purchase from product page',
            'value' => 'False',
            'set_function' => 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'False\'), '),
          'MODULE_PAYMENT_PAYPAL_PARTNER_SHOPPING_CART' => array('title' => 'Show PayPal button(s) on Shopping Cart',
            'description' => 'Show PayPal buttons on Shopping Cart Page',
            'value' => 'Vertical',
            'set_function' => 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'False\'), '),

          'MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT_LOGIN' => array('title' => 'Show PayPal button on Checkout Login',
            'description' => 'Show PayPal buttons on Checkout Login Page',
            'value' => 'False',
            'set_function' => 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'False\'), '),

          'MODULE_PAYMENT_PAYPAL_PARTNER_AT_CHECKOUT' => array('title' => 'Show PayPal button on Checkout',
            'description' => 'Show PayPal buttons on Checkout Page',
            'value' => 'Vertical',
            'set_function' => 'multiOption(\'dropdown\', array(\'Horizontal\', \'Vertical\', \'Fields\', \'False\'), '),



          'MODULE_PAYMENT_PAYPAL_PARTNER_PAY_LATER' => array('title' => 'Show PayPal Pay later info',
            'description' => 'Displays Pay Later messaging for available offers. Restrictions apply. See terms and learn more on PayPal <a target=\'blank\' href=\'https://developer.paypal.com/docs/commerce-platforms/admin-panel/\'>PayPal</a>',
            'value' => 'False',
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
          'MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_BEFORE_PAYMENT' => array('title' => 'Save order before payment',
            'description' => 'Save order before payment (slower checkout, exact invoice ID on PayPal, could be required if you have several orders a minute)',
            'value' => 'False',
            'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
          'MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_METHOD' => array('title' => 'Transaction Method',
            'description' => 'The processing method to use for each transaction.',
            'value' => 'sale',
            'set_function' => 'tep_cfg_select_option(array(\'authorize\', \'sale\'), '),
          'MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_STATUS_ID' => array('title' => 'Set Pending Order Status',
            'description' => 'Set the status of pending orders made with this payment module to this value',
            'value' => $status_id_o,
            'use_func' => '\\common\\helpers\\Order::get_order_status_name',
            'set_function' => 'tep_cfg_pull_down_order_statuses('),
          'MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTIONS_ORDER_STATUS_ID' => array('title' => 'Set Paid Order Status',
            'description' => 'Set the paid status of orders made with this payment module to this value',
            'value' => $status_id,
            'use_func' => '\\common\\helpers\\Order::get_order_status_name',
            'set_function' => 'tep_cfg_pull_down_order_statuses('),
          'MODULE_PAYMENT_PAYPAL_PARTNER_CANCEL_ORDER_STATUS_ID' => array('title' => 'Set Cancelled Order Status',
            'description' => 'Set the cancelled status of orders made with this payment module to this value',
            'value' => $status_id_ch,
            'use_func' => '\\common\\helpers\\Order::get_order_status_name',
            'set_function' => 'tep_cfg_pull_down_order_statuses('),
          'MODULE_PAYMENT_PAYPAL_PARTNER_ZONE' => array('title' => 'Payment Zone',
            'description' => 'If a zone is selected, only enable this payment method for that zone.',
            'value' => '0',
            'use_func' => '\\common\\helpers\\Zones::get_zone_class_title',
            'set_function' => 'tep_cfg_pull_down_zone_classes('),
          'MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER' => array('title' => 'Transaction Server',
            'description' => 'Use the live or testing (sandbox) gateway server to process transactions?',
            'value' => 'Live',
            'set_function' => 'tep_cfg_select_option(array(\'Live\', \'Sandbox\'), '),
          'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_COLOR' => array('title' => 'Dynamic Button Color',
            'description' => 'Color for Dynamic Button',
            'value' => 'gold',
            'set_function' => 'multiOption(\'dropdown\', array(\'gold\', \'blue\', \'silver\', \'white\', \'black\'), '),
          'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SHAPE' => array('title' => 'Dynamic Button Shape',
            'description' => 'Shape for Dynamic Button',
            'value' => 'pill',
            'set_function' => 'multiOption(\'dropdown\', array(\'pill\', \'rect\'), '),
          'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SIZE' => array('title' => 'Dynamic Button Size',
            'description' => 'Size for Dynamic Button',
            'value' => 'small',
            'set_function' => 'multiOption(\'dropdown\', array(\'small\', \'medium\', \'large\', \'responsive\'), '),
          'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LABEL' => array('title' => 'Dynamic Button Label',
            'description' => 'Label for Dynamic Button',
            'value' => 'checkout',
            'set_function' => 'multiOption(\'dropdown\', array(\'checkout\', \'pay\', \'buynow\', \'paypal\'), '),
/** /
          'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LAYOUT' => array('title' => 'Dynamic Button Layout',
            'description' => 'Layout for Dynamic Button',
            'value' => 'vertical',
            'set_function' => 'multiOption(\'dropdown\', array(\'horizontal\', \'vertical\'), '),
          'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_LAYOUT_PRODUCT' => array('title' => 'Product Page Dynamic Button Layout',
            'description' => 'Product Page: Layout for Dynamic Button',
            'value' => 'vertical',
            'set_function' => 'multiOption(\'dropdown\', array(\'horizontal\', \'vertical\'), '),
/**/
          'MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_FUNDING' => array('title' => 'Enable payment methods',
            'description' => 'Enable funding payment methods',
            'value' => '',
            'set_function' => 'multiOption(\'checkbox\', \common\modules\orderPayment\paypal_partner::possibleFundingArray(), '),

          /*'MODULE_PAYMENT_PAYPAL_PARTNER_FRAUDNET_SI' => array('title' => 'PayPal Fraudnet Source Identifier',
            'description' => 'Activate PayPal Fraudnet in case you were explicitly requested by PayPal to do so. PayPal will
provide you with a "Source Identifier" for every PayPal account used. Do not make up own values.',
            'value' => ''
            ),*/
          'MODULE_PAYMENT_PAYPAL_PARTNER_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
            'description' => 'All parameters of an invalid transaction will be sent to this email address.'),
          'MODULE_PAYMENT_PAYPAL_PARTNER_SORT_ORDER' => array('title' => 'Sort order of display',
            'description' => 'Sort order of display. Lowest is displayed first.',
            'value' => '0'));

        return $params;
    }

    public function install($platform_id) {
        parent::install($platform_id);
        $this->getInstaller()->install();
    }

    public function remove($platform_id) {
        parent::remove($platform_id);
        if (\Yii::$app->controller->action->id == 'change') {
            $this->getInstaller()->remove($platform_id);
        }
    }

    function sendDebugEmail($response = array()) {

        if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_DEBUG_EMAIL') && tep_not_null(MODULE_PAYMENT_PAYPAL_PARTNER_DEBUG_EMAIL)) {
            $email_body = '';

            if (!empty($response)) {
                $email_body .= 'RESPONSE:' . "\n\n" . print_r($response, true) . "\n\n";
            }

            if (!empty($_POST)) {
                $email_body .= '$_POST:' . "\n\n" . print_r($_POST, true) . "\n\n";
            }

            if (!empty($_GET)) {
                $email_body .= '$_GET:' . "\n\n" . print_r($_GET, true) . "\n\n";
            }

            if (!empty($email_body)) {
                if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_DEBUG_EMAIL') && \common\helpers\Validations::validate_email(MODULE_PAYMENT_PAYPAL_PARTNER_DEBUG_EMAIL)) {
                    \common\helpers\Mail::send('', MODULE_PAYMENT_PAYPAL_PARTNER_DEBUG_EMAIL, 'PayPal Partner Debug E-Mail', trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                } else {
                    \Yii::warning($email_body, 'paypal_partner');
                }
            }
        } elseif ($this->debug) {
            \Yii::warning(print_r($response, true), 'paypal_partner');
        }
    }

    function isOnline() {
        return true;
    }


    public static function getMode() {//admin (default) and requested for boarding platform could be different
        $ret = '';
        if (\Yii::$app->request->get('action', false) == 'processOnBoard' &&
            \Yii::$app->request->get('platform_id', 0) > 0) {
            $platform_config = new \common\classes\platform_config(\Yii::$app->request->get('platform_id', 0));
            $ret = $platform_config->const_value('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER', 'Live');
        } elseif (!defined('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER') && \Yii::$app->request->get('platform_id', 0)) {
            $platform_config = new \common\classes\platform_config(\Yii::$app->request->get('platform_id', 0));
            $ret = $platform_config->const_value('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER', 'Live');
        } elseif (!defined('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER') ) {
            if (!(isset($this) && $this instanceof self) ){
                $platform_id = self::getPlatformId();
            } else {
                $platform_id = $this->getPlatformId();
            }
            $platform_config = new \common\classes\platform_config($platform_id);
            $ret = $platform_config->const_value('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER', 'Live');
        } else {
            $ret = MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER;
        }
        return $ret;
    }

    /**
     * seller is boarded with current partner OR boarding process
     * @return bool
     */
    protected function _isReady() {
        if (!\Yii::$app->db->getTableSchema('paypal_seller_info', true)) {
            return false;
        }
        if (in_array(\Yii::$app->request->get('action', false), ['processOnBoard', 'checkOnBoarded', 'sellerDetails', 'deleteSeller'])) {
            return true;
        }
        $platformId = $this->getPlatformId();
        $seller = $this->getSeller($platformId);
        return ($seller->isOnBoarded() && $this->getPartnerId() == $seller->partner_id);
    }

    public function getHttpClient() {
        if ($this->getMode() == 'Live') {
            $environment = new \PayPalCheckoutSdk\Core\ProductionEnvironment($this->_getClientId(), $this->_getClientSecret());
        } else {
            $environment = new \PayPalCheckoutSdk\Core\SandboxEnvironment($this->_getClientId(), $this->_getClientSecret());
        }
        $client = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);
        return $client;
    }

    public function getFee($amount) {
        try {
            $seller = $this->getSeller(\common\classes\platform::currentId());
            $value = $amount * floatval($seller->fee_percent) / 100;
            return $this->formatRaw($value);
        } catch (\Exception $e) {
            \Yii::error($e->getMessage(), $this->code);
        } catch (\Error $e) {
            \Yii::error($e->getMessage(), $this->code);
        }
        return 0;
    }

    public function getCartDetails() {
        $items = [];
        $order = $this->manager->getOrderInstance();
        $currency = \Yii::$app->settings->get('currency');
        /** @var \common\helpers\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');

        if (!$this->isPartlyPaid()) {

            $masterTotal = $this->formatRaw($order->info['total_inc_tax']);
            $totalPayPal = $totalPayPalTax = 0;
            $ii = 0;
            // ex VAT prices and tax to avoid items patch (export orders) - not working via JS :(.
            foreach ($order->products as $product) {

                if ($this->sendExVat) {
                    $_val = $product['final_price'];
                    if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True' && $product['tax']>0) {
                        $_val = \common\helpers\Tax::reduce_tax_always($product['final_price'], $product['tax']);
                    }
                } else {
                    $_val = \common\helpers\Tax::add_tax($product['final_price'], $product['tax']);
                }
                $val = $this->formatRaw($_val);
                $qty = $product['qty'];
                $virtual_qty = '';
                $tmpQty = \common\helpers\Product::getVirtualItemQuantityValue($product['id']);
                if ($tmpQty>1) {
                    if ($qty/$tmpQty == round($qty/$tmpQty)) {
                        $qty = round($qty/$tmpQty);
                        $val = $this->formatRaw($_val*$tmpQty);
                    } else {
                        $virtual_qty = '/' . ($tmpQty) . ' ';
                    }
                }

                $items[$ii] = [
                  'name' => substr($virtual_qty . $product['name'], 0, 127),
                  'unit_amount' => [
                    'currency_code' => $currency,
                    'value' => $val,
                  ],
                  'quantity' => $qty,
                ];

                if ($this->requireItemVat) {
                    //PayPal /USA aproach could give huge descrepancy in TAX/Total values because of floating math.
                    // on site for EU we need price inc Tax and the same price inc VAT should be for 1 and 10 pcs
                    // it means net priceEach is changed for 1 and 10 products (EU). USA approach don't allow marketing (NN.99) prices.
                    // ex VAT 20% priceEachGross 0.15 NetPriceEach 0.12 VAT 0.03 (USA approach Net 0.12 Tax 0.02 Gross 0.14)
                    // 10 pcs price Gross 1.50 (Net 1.25 Tax 0.25)  USA approach Net 1.20 Tax 0.20 Gross 1.40
                    $_tax = 0;
                    if ($product['tax']>0 && $qty>0) {
                        if ($this->sendExVat) {
                            $_eachGross = $val+\common\helpers\Tax::calculate_tax($val, $product['tax']);
                        } else {
                            $_eachGross = $val;
                        }
                        $_gross = $this->formatRaw($_eachGross * $qty, $currency, 1);
                        $_nett = $this->formatRaw(\common\helpers\Tax::get_untaxed_value($_gross, $product['tax']), $currency, 1);
                        $_tax = $this->formatRaw(($_gross - $_nett)/$qty, $currency, 1);
                        $totalPayPalTax += $this->formatRaw($_tax*$qty, $currency, 1);
                    }
                    $items[$ii]['tax'] = [
                        'currency_code' => $currency,
                        'value' => $_tax,
                    ];
                    $items[$ii]['tax_rate'] = round($product['tax'], 2);
                    $items[$ii]['category'] = 'PHYSICAL_GOODS';//DIGITAL_GOODS
                }
                $ii++;
            }
            $shipping = [];
            if ($this->manager->isShippingNeeded()) {
                if (!$this->requireItemVat) { //shipping as total - else as product
                    $shipping = [
                      'currency_code' => $currency,
                      'value' =>
                        $this->formatRaw(
                            $currencies->display_price_clear($this->sendExVat ? $order->info['shipping_cost_exc_tax'] : $order->info['shipping_cost_inc_tax'], 0, 1)
                                                      , $currency, 1
                        )
                    ];
                    $totalPayPal += $shipping['value'];
                } else {// no shipping tax and Should equal sum of (tax * quantity) across all items for a given purchase_unit (/purchase_units/@reference_id=='default'/amount/breakdown/tax_total/value )
                    $_tax = $this->formatRaw($this->formatRaw(
                            $currencies->display_price_clear($order->info['shipping_cost_inc_tax'], 0, 1), $currency, 1
                        ) - $this->formatRaw(
                            $currencies->display_price_clear($order->info['shipping_cost_exc_tax'], 0, 1), $currency, 1
                        ), $currency, 1);
                    $items[$ii] = [
                        'name' => TEXT_SHIPPING,
                        'unit_amount' => [
                            'currency_code' => $currency,
                            'value' => $this->formatRaw(
                                $currencies->display_price_clear($order->info['shipping_cost_exc_tax'], 0, 1), $currency, 1
                            )
                        ],
                        'quantity' => 1,
                        'tax' => [
                            'currency_code' => $currency,
                            'value' => $_tax,
                        ]
                    ];
                    $items[$ii]['tax_rate'] = round($_tax/$this->formatRaw(
                            $currencies->display_price_clear($order->info['shipping_cost_exc_tax'], 0, 1), $currency, 1
                        ), 2);
                    $items[$ii]['category'] = 'PHYSICAL_GOODS';
                    $ii++;
                    $totalPayPalTax += $_tax;
                }
            }

            $details = [
              'totals' => [
                'item_total' => [
                  'currency_code' => $currency,
                  'value' => 0
                //'value' => $this->formatRaw($order->info['subtotal_cost_inc_tax']),
                ],
              ]
            ];
            //if ($this->sendExVat) {
                $details['items'] = $items;
            //}

            if (!empty($shipping)) {
                $details['totals']['shipping'] = $shipping;
            }

            $handlingValue = 0;
            $discountValue = 0;
            $tax = 0;
            $totalCollection = $this->manager->getTotalCollection();
            foreach ($this->manager->getTotalOutput(false) as $total) {
                // calculate tax total - could be several
                if ($total['code'] == 'ot_tax') {
                    $tax +=  $total['value'];

                } elseif (!in_array($total['code'], array_merge($totalCollection->readonly, ['ot_shipping']))) {
                    if ($totalCollection->get($total['code'])->credit_class) {
                        $_tmpTitle = defined('MODULE_ORDER_TOTAL_COUPON_TOTAL')?constant('MODULE_ORDER_TOTAL_COUPON_TOTAL'):'';
                        if ($total['code']=='ot_coupon' && $total['title'] == $_tmpTitle . ':') {
                            continue; //multicoupon with total discount line dirty hack;
                        }
                        $discountValue += ($this->sendExVat ? $total['value_exc_vat'] : $total['value_inc_tax']);
                    } else {
                        $handlingValue += ($this->sendExVat ? $total['value_exc_vat'] : $total['value_inc_tax']);
                    }
                }
            }

            $tax = $this->formatRaw($tax);
            if ($this->requireItemVat) {
                $tax = $this->formatRaw($totalPayPalTax, $currency, 1);
            }
            if ($this->sendExVat) {
                $totalPayPal += $tax;
            }

            foreach ($items as $item) {
                $details['totals']['item_total']['value'] += $this->formatRaw($item['unit_amount']['value'] * $item['quantity'], $currency, 1); //already multiplied currency value
            }
            $details['totals']['item_total']['value'] = $this->formatRaw($details['totals']['item_total']['value'], $currency, 1);

            $totalPayPal += $details['totals']['item_total']['value'];

            if ($handlingValue) {
                $details['totals']['handling'] = [
                  'currency_code' => $currency,
                  'value' => $this->formatRaw($handlingValue),
                ];
            }
            $totalPayPal += $details['totals']['handling']['value'] ?? 0;
            $totalPayPal = $this->formatRaw($totalPayPal, $currency, 1);


            if ($discountValue) {
                //escape few penny rounding issue with discounts
                if (abs($totalPayPal - $this->formatRaw($discountValue) - $masterTotal)<0.05) {
                    if ( ($totalPayPal - $this->formatRaw($discountValue) < $masterTotal) ) {
                        $discountValue -= abs($totalPayPal - $this->formatRaw($discountValue) - $masterTotal);
                    } else {
                        $discountValue += abs($totalPayPal - $this->formatRaw($discountValue) - $masterTotal);
                    }
                }
                $details['totals']['discount'] = [
                  'currency_code' => $currency,
                  'value' => $this->formatRaw($discountValue),
                ];
            }

    ///rounding issues
            $totalPayPal -= $details['totals']['discount']['value'] ?? 0;
            $totalPayPal = $this->formatRaw($totalPayPal, $currency, 1);
            if ($totalPayPal > $masterTotal) {
                $dscnt = $details['totals']['discount']['value']??0;
                $details['totals']['discount'] = [ //shipping_discount
                  'currency_code' => $currency,
                  'value' => $this->formatRaw($dscnt + $totalPayPal - $masterTotal, $currency, 1),
                ];
            } else if ($totalPayPal < $masterTotal) {
                $handling = $details['totals']['handling']['value']??0;
                $details['totals']['handling'] = [//insurance
                  'currency_code' => $currency,
                  'value' => $this->formatRaw($handling + $masterTotal - $totalPayPal, $currency, 1),
                ];
            }

            if ($this->sendExVat && $tax > 0) {
                $details['totals']['tax_total'] = [
                  'currency_code' => $currency,
                  'value' => $tax,
                ];
            }
        }

        return $details;
    }

    public function createOrder($data = []) {
        global $cart;
        if ($cart->count_contents() < 1 && !$this->manager->isInstance()) {
            return false;
        }
        $post = \Yii::$app->request->post();

        $this->manager->remove('estimate_ship');

        /** @var \common\classes\Order $order */
        if (!$this->manager->isInstance()) {
            $order = $this->manager->createOrderInstance('\common\classes\Order');
        } else {
            $order = $this->manager->getOrderInstance();
        }
        $this->manager->getShippingQuotesByChoice();
        
        $this->manager->checkoutOrderWithAddresses();
        $this->manager->totalProcess();

        $request = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();
        $request->prefer('return=representation');
        if ($tmp = self::getAttributionId()) {
            $request->payPalPartnerAttributionId($tmp);
        }
        if (!empty($post['payment']) ) {
            $this->setAPMSetting($post);
        }

        $request->body = $this->ppBuildOrderDetails($order, $data);

        if (!empty($post['payment']) ) {
            $this->addAPMDetails($request, $order, $post);
        }
//echo "#### <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($request, true) ."</PRE>"; die;

        if (!empty($request->body['payment_source'])) {
            $request->headers['PayPal-Request-Id'] = $this->getPayPalOrderAPIRequestId();
            $request->headers['PayPal-Client-Metadata-Id'] = $this->getFraudNetSessionId();
        }

        if ($this->debug) {
            \Yii::warning("createOrderRequest #### " .print_r($request, true), 'TLDEBUG' . $this->code);
        }

        try {
            return $this->getHttpClient()->execute($request);
        } catch (\Exception $ex) {

            \Yii::error('body ' . print_r($request->body, true), 'paypal_partner_exception');
            \Yii::error('message ' . $ex->getMessage(), 'paypal_partner_exception');
            if (!empty($request->body['payment_source'])) {
                $this->resetFraudNetSessionId();
            }
            if ($tmp = $this->parseJsonMessage($ex->getMessage())) {
                return ['error' => 1, 'message' => $tmp];
            }
        }
        return false;
    }

    private function parseJsonMessage($msg) {
        $ret = false;
        try {
            $tmp = json_decode($msg, true);
            if (is_array($tmp['details'])) {
                $ret = '';
                foreach ( $tmp['details'] as $err) {
                    if (!empty($err['description'])) {
                        $ret .= $err['description'];
                        if (!empty($err['field'])) {
                            $ret .= ' (' . $err['field'];
                            if (!empty($err['value'])) {
                                $ret .= ' - ' . $err['value'];
                            }
                            $ret .= ')';
                        }
                        $ret .= "\n";
                    }
                }
            }
        } catch (\Exception $ex) {
            \Yii::error('parseJsonMessage: ' . $ex->getMessage(), 'paypal_partner_exception');
        }
        return $ret;
    }

/**
 * get order details from PayPal by id
 * @param string $orderId
 * @return stdClass|false
 */
    public function getOrder($orderId) {
        $request = new \PayPalCheckoutSdk\Orders\OrdersGetRequest($orderId);

        try {
            return $this->getHttpClient()->execute($request);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), 'paypal_partner');
        }
        return false;
    }

/**
 * patch order at PayPal
 * @param string $orderId PayPal order Id
 * @return bool
 */
    public function updateOrder($orderId) {
        $request = new \PayPalCheckoutSdk\Orders\OrdersPatchRequest($orderId);
        $order = $this->manager->getOrderInstance(); //?? VL2check wasn't init value 0

        //$cartDetails = $this->getCartDetails();
        $details = $this->ppBuildOrderDetails($order);
        $data = [[
                'op' => 'replace',
                'path' => '/purchase_units/@reference_id==\'default\'',
                'value' => $details['purchase_units'][0]
                ]
        ];

        $request->body = $data;

        try {
            return $this->getHttpClient()->execute($request);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), 'paypal_partner');
        }
        return false;
    }

/**
 * patch order at PayPal with invoice id (local order id)
 * @param string $orderId PayPal order Id
 * @return bool
 */
    public function updateOrderInvoiceId($orderId, $invoiceId) {
        $request = new \PayPalCheckoutSdk\Orders\OrdersPatchRequest($orderId);
        $data = [[
                'op' => 'replace',
                'path' => '/purchase_units/@reference_id==\'default\'/invoice_id',
                'value' => $invoiceId
                ]
        ];

        $request->body = $data;

        try {
            return $this->getHttpClient()->execute($request);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), 'paypal_partner');
        }
        return false;
    }
    
    private function ppBuildOrderDetails($order, $post_data = []) {
        $currency_code = \Yii::$app->settings->get('currency');
        if (!empty($order->info['currency'])) {
            $currency_code = $order->info['currency'];
        }
        /** @var \common\helpers\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');
        
        $cartDetails = $this->getCartDetails();
        $applicationContext = [
          'return_url' => tep_href_link("callback/webhooks.payment.{$this->code}", 'action=return', 'SSL', true, false),
          'cancel_url' => tep_href_link("callback/webhooks.payment.{$this->code}", 'action=cancel', 'SSL', true, false),
        ];

        if (!$this->manager->isShippingNeeded()) {
            $applicationContext['shipping_preference'] = 'NO_SHIPPING';
        }

        if (true) { //checkout page/logged in customer: all details are available.
            if (!empty($order->billing)) {
                $tmp = $order->billing;
                if (!empty($tmp['street_address']) && !empty($tmp['postcode']) && !empty($tmp['country']['iso_code_2'])) {
                } else {
                    $tmp = $this->manager->getBillingAddress();
                }
            } else {
                $tmp = $this->manager->getBillingAddress();
            }
            if ($tmp['zone_id'] > 0) {
                if ($tmp['country']['iso_code_2'] == 'US') {
                    $_state = \common\helpers\Zones::get_zone_code($tmp['country']['id'], $tmp['zone_id'], '');
                } else {
                    $_state = \common\helpers\Zones::get_zone_name($tmp['country']['id'], $tmp['zone_id'], $tmp['state']);
                }
                if (!empty($_state)) {
                    $tmp['state'] = $_state;
                }
            }
            if (!empty($post_data['Billing_address'])) {
                foreach (['firstname', 'lastname', 'street_address', 'city', 'postcode', 'suburb'] as $k) {
                    if (empty($tmp[$k]) && !empty($post_data['Billing_address'][$k])) {
                        $tmp[$k] = strip_tags($post_data['Billing_address'][$k]);
                    }
                }
                if ($post_data['Billing_address']['country'] != $tmp['country']['id']) {
                    $_country = \common\helpers\Country::get_country_info_by_id($post_data['Billing_address']['country']);
                    if (!empty($_country['iso_code_2'])) {
                        $tmp['country'] = $_country;
                    }
                }
            } elseif (empty($tmp['firstname']) && empty($tmp['lastname'])) {
                $tmp['firstname'] = $order->customer['firstname']??'';
                $tmp['lastname'] = $order->customer['lastname']??'';
            }
            $payer = [];
            if (!empty($tmp['firstname']) && !empty($tmp['lastname'])) {
                $payer['name'] = [
                  'given_name' => substr($tmp['firstname'], 0, 140),
                  'surname' => substr($tmp['lastname'], 0, 140),
                ];
            }
            if (!empty($tmp['firstname']) && !empty($tmp['lastname'])) {
                if (!empty($order->customer['email_address'])) {
                    $payer['email_address'] = $order->customer['email_address'];
                } elseif (!empty($post_data['checkout']['email_address'])) {
                    $payer['email_address'] = $post_data['checkout']['email_address'];
                }
                if (!empty($post_data['checkout']['telephone'])) {
                    $payer['telephone'] = $post_data['checkout']['telephone'];
                } elseif (!empty($order->customer['telephone'])) {
                    $payer['telephone'] = $order->customer['telephone'];
                }
                
            }
            if (!empty($tmp['street_address']) && !empty($tmp['postcode']) && !empty($tmp['country']['iso_code_2'])) {
                $payer['address'] = [//billing
                  'address_line_1' => substr($tmp['street_address'], 0, 300),
                  'address_line_2' => substr($tmp['suburb'], 0, 300),
                  'admin_area_2' => substr($tmp['city'], 0, 120),
                  'admin_area_1' => substr($tmp['state'], 0, 300),
                  'postal_code' => substr($tmp['postcode'], 0, 60),
                  'country_code' => substr($tmp['country']['iso_code_2'], 0, 60),
                ];
            }
        }
        $invoice_id = $this->estimateOrderId() . '-e-' . date('ymdHis');
        if (!empty($order->info['orders_id'])) {
            $invoice_id = $order->info['orders_id'];
        }

        $purchaseUnits = [[
        //"reference_id" => "default",
/*            "payee" => [
              "email_address" => $seller->email_address
            ],*/
            
            'description' => (strlen(defined('STORE_NAME')?constant('STORE_NAME'):'')>127  ?
                  substr((defined('STORE_NAME')?constant('STORE_NAME'):''), 0, 123) . ' ...' :
                  (defined('STORE_NAME')?constant('STORE_NAME'):'')),
            'invoice_id' => $invoice_id,
// custom_id to hide from customer
        ]];

        if (!empty($cartDetails['items'])) {
            $purchaseUnits[0]["items"] = $cartDetails['items'];
            $purchaseUnits[0]["amount"] = [
              "value" => $this->formatRaw($order->info['total_inc_tax']),
              "currency_code" => $currency_code,
              "breakdown" => $cartDetails['totals'],
            ];

        } else {

            $purchaseUnits[0]["amount"] = [
              "value" => $this->formatRaw($this->getChargeFromOrder($order)),
              "currency_code" => $currency_code,
            ];

        }


        //if (true) { //checkout page/logged in customer: all details are available.
        if ($this->manager->isShippingNeeded()) {
            //$applicationContext['user_action'] = 'PAY_NOW';
            //$applicationContext['shipping_preference'] = 'SET_PROVIDED_ADDRESS';
            if (!empty($this->onlySiteAddress)) {
                $applicationContext['shipping_preference'] = 'SET_PROVIDED_ADDRESS';
            }
            //$tmp = $order->delivery;
            $tmp = $this->manager->getDeliveryAddress();
            if ($tmp['zone_id'] > 0) {
                if ($tmp['country']['iso_code_2'] == 'US') {
                    $_state = \common\helpers\Zones::get_zone_code($tmp['country']['id'], $tmp['zone_id'], '');
                } else {
                    $_state = \common\helpers\Zones::get_zone_name($tmp['country']['id'], $tmp['zone_id'], $tmp['state']);
                }
                if (!empty($_state)) {
                    $tmp['state'] = $_state;
                }
            }
            if (!empty($post_data['Shipping_address'])) {
                foreach (['firstname', 'lastname', 'street_address', 'city', 'postcode'] as $k) {
                    if (empty($tmp[$k]) && !empty($post_data['Shipping_address'][$k])) {
                        $tmp[$k] = strip_tags($post_data['Shipping_address'][$k]);
                    }
                }
                if ($post_data['Shipping_address']['country'] != $tmp['country']['id']) {
                    $_country = \common\helpers\Country::get_country_info_by_id($post_data['Shipping_address']['country']);
                    if (!empty($_country['iso_code_2'])) {
                        $tmp['country'] = $_country;
                    }
                }
            }
            //if ($this->manager->isShippingNeeded()) {
            $options = $this->getShippingOptions($currencies, $currency_code);
            if (!empty($options)) {
                $purchaseUnits[0]['shipping']['options'] = $options;
            }
            if (isset($purchaseUnits[0]['shipping']['options'][0]['type']) && $purchaseUnits[0]['shipping']['options'][0]['type'] == 'PICKUP') {
                $purchaseUnits[0]['shipping']['name'] = [];
                foreach ($purchaseUnits[0]['shipping']['options'] as $option) {
                    if (!empty($option['selected']) || count($purchaseUnits[0]['shipping']['options'])==1) {
                        $addr = \common\helpers\Warehouses::get_warehouse_address(1);
                        $purchaseUnits[0]['shipping'] = [
                            'name' => [
                              'full_name' => substr('S2S ' . $option['label'], 0, 300),
                            ],
                            //'type' => 'PICKUP_IN_PERSON',
                            'address' => [
                              'address_line_1' => substr($addr['street_address']??'', 0, 300),
                              'address_line_2' => substr($addr['suburb']??'', 0, 300),
                              'admin_area_2' => substr($addr['city']??'', 0, 120),
                              'admin_area_1' => substr($addr['state']??'', 0, 300),
                              'postal_code' => substr($addr['postcode']??'', 0, 60),
                              'country_code' => substr($addr['country_iso_code_2']??'', 0, 60),
                            ]
                        ];
                        $applicationContext['shipping_preference'] = 'NO_SHIPPING';
                        break;
                    }
                }


            } elseif (!empty($tmp['street_address']) && !empty($tmp['postcode']) && !empty($tmp['country']['iso_code_2'])) {
                $purchaseUnits[0]['shipping'] = [
                  'name' => [
                    'full_name' => substr($tmp['firstname'] . ' ' . $tmp['lastname'], 0, 300),
                  ],
                  //'type' => 'SHIPPING',
                  'address' => [
                    'address_line_1' => substr($tmp['street_address'], 0, 300),
                    'address_line_2' => substr($tmp['suburb'], 0, 300),
                    'admin_area_2' => substr($tmp['city'], 0, 120),
                    'admin_area_1' => substr($tmp['state'], 0, 300),
                    'postal_code' => substr($tmp['postcode'], 0, 60),
                    'country_code' => substr($tmp['country']['iso_code_2'], 0, 60),
                  ]
                ];
            } 
            //}
        }


        if (!$this->hasOwnKeys() && $this->_getIntent() != 'authorize') {
            $purchaseUnits[0]["payment_instruction"] = [//only in capture mode
                "disbursement_mode" => "INSTANT",
                "platform_fees" => [[
                    "amount" => [
                      "currency_code" => $currency_code,
                      "value" => $this->getFee($order->info['total_inc_tax']),
                    ]
                ]]
            ];
        }
        
        $seller = $this->getSeller(\common\classes\platform::currentId());
        if (!empty($seller->email_address)) {
            $purchaseUnits[0]['payee'] =
                [
                  "email_address" => $seller->email_address
                ];
        }
        $locale = str_replace('_', '-', \Yii::$app->settings->get('locale'));
        $applicationContext['locale'] = $locale;

        $ret = [
                "intent" => ($this->_getIntent() == 'authorize' ? "AUTHORIZE" : "CAPTURE"),
                "purchase_units" => $purchaseUnits,
                "application_context" => $applicationContext
            ];
        
        if (!empty($payer)) {
            $ret['payer'] = $payer;
        }

        return $ret;
    }



    public function patchOrder($orderId, $data) {
        $request = new \PayPalCheckoutSdk\Orders\OrdersPatchRequest($orderId);
        $request->body = $data;
        try {
            return $this->getHttpClient()->execute($request);
        } catch (\Exception $ex) {
            \Yii::error($data, 'paypal_partner');
            \Yii::error($ex->getMessage(), 'paypal_partner');
        }
        return false;
    }

    public function captureOrder($orderId) {

        $request = new \PayPalCheckoutSdk\Orders\OrdersCaptureRequest($orderId);
        if (self::getFraudnetSI()) {
            $metaData = $this->getFraudNetSessionId();
            if (!empty($metaData) ) {
                $request->payPalClientMetadataId($metaData);
            }
        }

        try {
            return $this->getHttpClient()->execute($request);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), 'paypal_partner');
        }
        return false;
    }

    public function generateClientToken() {
        $cid = $this->manager->getCustomerAssigned();
        //in session for 2 min
        global $ppp_token_cid, $ppp_token_key, $ppp_token_until;
        foreach(['ppp_token_cid', 'ppp_token_key', 'ppp_token_until'] as $k) {
            if (!tep_session_is_registered($k)) {
                tep_session_register($k);
            }
        }

        if (!empty($ppp_token_until) && $ppp_token_until<time()) {
            $ppp_token_until = false;
        }
        //active and the same customer
        if (!empty($ppp_token_key) && !empty($ppp_token_until) && 
            (empty($cid) || $cid==$ppp_token_cid)) {
            return $ppp_token_key;
        }

        if (empty($cid)) {
            $cid = substr(sha1(uniqid()), 0, 20);
        }
        $request = new \PayPalCheckoutSdk\Core\ClientTokenRequest($cid);
        try {
            $res = $this->getHttpClient()->execute($request);
            if ($res->statusCode == 200 && !empty($res->result->client_token)) {
                $ppp_token_cid = $cid;
                $ppp_token_key = $res->result->client_token;
                $ppp_token_until = time()+120;//$res->result->expires_in
                return $ppp_token_key;
            } else {
                \Yii::error(print_r($res, true), 'paypal_partner');
            }
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), 'paypal_partner');
        }
        return false;
    }

    public function authorizeOrder($orderId) {

        $request = new \PayPalCheckoutSdk\Orders\OrdersAuthorizeRequest($orderId);
        $request->prefer('return=representation');
        if (self::getFraudnetSI()) {
            $metaData = $this->getFraudNetSessionId();
            if (!empty($metaData) ) {
                $request->payPalClientMetadataId($metaData);
            }
        }

        try {
            return $this->getHttpClient()->execute($request);
        } catch (\Exception $ex) {
            \Yii::error($ex->getMessage(), 'paypal_partner');
        }
        return false;
    }

    public function onBoardingProcess() {
        $platformId = $this->getPlatformId();

        $seller = $this->getSeller($platformId);

        $ppexists = \Yii::$app->request->get('ppexists', 0);
        $seller_type = \Yii::$app->request->get('seller_type', 'b');
        try {
            if ($ppexists) {
                $partner = $this->signinPartner($seller);
            } else {
                $partner = $this->signupPartner($seller, $seller_type);
            }
        } catch (\Exception $ex) {
            $this->sendDebugEmail($ex);
            return PAYPAL_PARTNER_SELLER_BOARDED_ERROR;
        }
        if (!is_object($partner)) {
            return PAYPAL_PARTNER_SELLER_BOARDED_ERROR;
        }

        $urlParams = [
          'platform_id' => $platformId, 'set' => 'payment', 'module' => $this->code,
        ];

        if (\Yii::$app->id == 'app-backend') {
            $returnUrl = \Yii::$app->urlManager->createAbsoluteUrl($urlParams+ ($this::BOARDING_MODE == 3?['modules/edit', 'action' => 'checkOnBoarded'] : ['modules/edit']) );
        } else {
            $returnUrl = \Yii::$app->urlManager->createAbsoluteUrl($urlParams+ ($this::BOARDING_MODE == 3?['admin/modules/edit', 'action' => 'checkOnBoarded'] : ['admin/modules/edit']));
        }
        $partner->getPartnerConfigOverride()->setReturnUrl($returnUrl);

        $logoUrl = \Yii::$app->urlManager->createAbsoluteUrl(['/'], 'https', true);
        if (strpos($logoUrl, '127.0.0.1') || strpos($logoUrl, 'localhost')) {
            $logoUrl = 'https://www.trueloaded.co.uk';
        }
        //2do check params
        $logoUrl .= '/admin/themes/basic/img/logo_color.png';
        $partner->getPartnerConfigOverride()->setPartnerLogoUrl($logoUrl);

// ##########
//$partner->getPartnerConfigOverride()->setReturnUrl('https://paypal.tllab.co.uk/index/log'); // debug

        if ($this->boardViaLink()) {
            $tmp = \common\helpers\Country::get_country_info_by_id($seller->entry_country_id??STORE_COUNTRY);
            $extra = $this->getExtraConfigClass();
            return $extra::widget([
                    'mode' => 'signupLink',
                    'module' => $this,
                    'link_params' => [
                        'partnerClientId' => $this->_getClientId(),
                        'partnerId' => $this->getPartnerId(),
                        'sellerNonce' => $seller->tracking_id,
                        'country' => $tmp['countries_iso_code_2']??'',
                        'locale' => str_replace('_', '-', \Yii::$app->settings->get('locale')),
                        'return_url' => rawurlencode($returnUrl),
                        'partnerLogoUrl' => rawurlencode($logoUrl)
                      ]
            ]);
        } else {
            try {
                $response = $partner->create($this->getApiContext());

                if (is_array($response->links) && count($response->links) > 0) {
                    $extra = $this->getExtraConfigClass();
                    foreach ($response->links as $link) {
                        if ($link->rel == 'action_url') {
                            $toUrl = $link->getHref();
                        } elseif ($link->rel == 'self') {
                            //useless - just to check
                        }
                    }

                    return $extra::widget([
                          'mode' => 'signup', //?? suppose grant permissions
                          'module' => $this,
                          'url' => $toUrl, //$response->links[1]->getHref()
                          'module' => $this,
                    ]);
                } else {
                    $this->sendDebugEmail($response);
                }
            } catch (\Exception $ex) {
                $this->sendDebugEmail($ex);
            }
        }
        return PAYPAL_PARTNER_SELLER_BOARDED_ERROR;
    }

    public $errors = [];
    public $messages = [];

    /**
     * get current webhooks for application
     */
    public function getNeededWebHooks() {
        if (self::BOARDING_MODE == 1) {
            $neededWebHooks = array_merge($this->webHooks, $this->webHooks1party);
        } else {
            $neededWebHooks = array_merge($this->webHooks, $this->webHooks3party);
        }
        return $neededWebHooks;
    }
    
    public function getSubscribedWebHooks() {
        //get webhooks (could be several, groupped by listener URL)
        $whList = $this->getWebHooks();
        $whListNames = []; // subscribed names only
        $url = $this->getWebHookUrl();

        if (!empty($whList->webhooks) && is_array($whList->webhooks)) {
            $whListNames = array_reduce(array_map(
                    function ($el) use ($url) {
                        $ret = [];
                        if ($url == $el->url && is_array($el->event_types)) {
                            foreach ($el->event_types as $et) {
                                $ret[] = $et->name;
                            }
                        }
                        return $ret;
                    }, $whList->webhooks), 'array_merge', array());
        }
        return $whListNames;
    }
    /**
     * subscribe to required webhooks for application
     */
    public function addWebHooks() {
        if (!empty($this->webHooks)) {
            $whListNames = $this->getSubscribedWebHooks();
            $neededWebHooks = $this->getNeededWebHooks();

            if (is_array($whListNames) && !empty($whListNames) && is_array($whListNames)) {
                $subEvents = array_diff($neededWebHooks, $whListNames);
            } else {
                $subEvents = $neededWebHooks;
            }

            if (is_array($subEvents) && !empty($subEvents)) {
                try {
                    $this->setWebHooks($subEvents);
                } catch (\Exception $ex) {
                    \Yii::warning("Subscribe webhooks exception " . $ex->getMessage(), $this->code);
                }
            }
        }
    }

    protected function validateCredentials($data) {
        $ret = ['html' => '...'];
        if (!empty($data['checkVal']) && $data['checkVal'] == md5(\Yii::$app->params['secKey.global'])) {
            $mode = 'Live';
            if (in_array($data['configuration']['MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER'], ['Live', 'Sandbox'])) {
                $mode = $data['configuration']['MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER'];
            }
            $auth = new PayPal\Auth\OAuthTokenCredential($data['SellerInfo']['own_client_id'], $data['SellerInfo']['own_client_secret']);
            $apiContext = new PayPal\Rest\ApiContext($auth);
            $apiContext->setConfig(['mode' => $mode]);
            $merchant = $this->getMerchant();
            if ($response = $merchant::checkStatus($this->getPartnerId(), $data['SellerInfo']['payer_id'], $apiContext)) {
                $ret = $this->parseBoardingDetails($response);
                if (!empty($response) && $response->getMerhantId() == $data['SellerInfo']['payer_id']) {
                    $ret['html'] = MODULE_PAYMENT_PAYPAL_PARTNER_API_OK;
                } else {
                    $ret['html'] = MODULE_PAYMENT_PAYPAL_PARTNER_API_FAIL_DATA;
                }

            } else {
                $ret['html'] = MODULE_PAYMENT_PAYPAL_PARTNER_API_FAIL;
            }
        }
        return $ret;
    }


    public function getBoardingDetails(int $platformId, lib\PaypalPartner\models\SellerInfo $seller) {
        $ret = [];

        if (!empty($seller->boarding_json) && !$this->hasOwnKeys() ) {
            $merchant = new lib\PaypalPartner\api\Merchant();
            $ret = $this->parseBoardingDetails($merchant->fromJson($seller->boarding_json), $seller->boarding_date);
        } elseif($this->_isReady()) {
            $merchant = $this->getMerchant();
            $get = \Yii::$app->request->get();
            if (empty($seller->payer_id) && !empty($get['merchantIdInPayPal'])) {
                $seller->payer_id = $get['merchantIdInPayPal'];
            }
            if ($response = $merchant::checkStatus($this->getPartnerId(), $seller->payer_id, $this->getApiContext())) {
                $ret = $this->parseBoardingDetails($response);
                try {
                    $sEmail = $response->getPrimaryEmail();
                    if (!empty($sEmail) && $sEmail != $seller->email_address) {
                        $seller->email_address = $response->getPrimaryEmail();
                    }
                    $seller->boarding_json = $response->json;
                    $seller->boarding_date = date(\common\helpers\Date::DATABASE_DATE_FORMAT);
                    $seller->save(false);
                } catch (\Exception $e) {
                    \Yii::warning(print_r($e->getMessage(), true), 'TLDEBUG_' . $this->code);
                }
            } else {
                $ret['errors'][] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR;
            }
        }
        return $ret;
    }

/**
 * 
 */
    public function fetchOwnKeys() {
        $get = \Yii::$app->request->get();
        $post = \Yii::$app->request->post();
        if (empty($post)) {
            $payload = @file_get_contents('php://input');
            if (!empty($payload )) {
               // parse_str($payload, $post);
                $post = json_decode($payload, true);
            }
        }
        $platformId = $post['platform_id']??$this->getPlatformId();
        try {
            if (!empty($post['ppp_mode'])) {
                $mode = $post['ppp_mode'];
                $seller = $this->getSeller($platformId, $mode, true);
                $partner_id = $this->getPartnerId($mode);
            }
            if (!$seller) {
                $seller = $this->getSeller($platformId);
                $mode = $this->getMode();
                $partner_id =$this->getPartnerId();
            }

            $details = lib\PaypalPartner\api\Merchant::getCustomerToken($post['authCode'], $post['sharedId'], $seller->tracking_id, $partner_id, $mode);
            if (!empty($details['client_id'])) {
                $seller->own_client_id = $details['client_id'];
                $seller->own_client_secret = $details['client_secret'];
                $seller->payer_id = $details['payer_id'];
                $seller->is_onboard = 1;
                $seller->save(false);
            }
        } catch (\Exception $ex) {
            $this->sendDebugEmail($ex);
            $this->errors[] = $ex->getMessage();
        }
    }

    public function checkOnBoarded() {
        $get = \Yii::$app->request->get();

        $platformId = $this->getPlatformId();

        try {

            $seller = $this->getSeller($platformId);
            if ($get['merchantIdInPayPal']) {
                if (!empty($get['merchantId']) && \common\helpers\Validations::validate_email($get['merchantId'])) {
                    $seller->email_address = $get['merchantId'];
                    $seller->save(false);
                }
                if (!$seller->isNewRecord) {
                    $seller->updateMerchantId($get['merchantIdInPayPal']);
                }
            } else {
                //if required 2do  get merchant_id by tracking_id /v1/customer/partners/{partner-id}/merchant-integrations?tracking_id={tracking-id}
            }
            $merchant = $this->getMerchant();
            if ($response = $merchant::checkStatus($this->getPartnerId(), $seller->payer_id, $this->getApiContext())) { //MODULE_PAYMENT_PAYPAL_PARTNER_API_APP_MERCHANT_ID
//        echo "#### <PRE>" . __FILE__ .':' . __LINE__ . ' ' . print_r($response, 1) ."</PRE>";
//        die;
                /**
                 *
                  "payments_receivable": true,
                  "primary_email_confirmed": true
                  "products[name==PPCP_CUSTOM].vetting_status:":SUBSCRIBED
                  "capabilities[name==CUSTOM_CARD_PROCESSING].status": ACTIVE
                  "capabilities[name==CUSTOM_CARD_PROCESSING].limits": undefined

                  Scopes object is NOT empty and matches the permissions passed from the Partner Referrals API request
                 */
                try {
                    if (!$seller->isNewRecord) {
                        if ($response->getPrimaryEmail() != $seller->email_address) {
                            $seller->email_address = $response->getPrimaryEmail();
                        }
                        $seller->boarding_json = $response->json;
                        $seller->boarding_date = date(\common\helpers\Date::DATABASE_DATE_FORMAT);
                        $seller->save(false);
                    }
                } catch (\Exception $e) {
                    \Yii::warning(print_r($e->getMessage(), true), 'TLDEBUG_' . $this->code);
                }

                if ($response->getPaymentsReceivable() && $response->getPrimaryEmailConfirmed() && !empty($response->getOauthIntegrations())) {
                    $oAuth = $response->getOauthIntegrations();
                    $scopes = $oAuth[0]->oauth_third_party[0]->scopes;
                    if (!empty($scopes) && $this->checkScopes($scopes) === true) {
                        if ($seller->setOnBoarded()) {
                            $this->addWebHooks();
                            $_missedRecommendedPermissions = '';
                            $_tmp = $this->checkScopes($scopes, true);
                            if (is_array($_tmp) && !empty($_tmp['recommended'])) {
                                $_missedRecommendedPermissions = " <br>\n" . implode("<br>\n", $_tmp['recommended']);
                            }
                            $this->messages[] = PAYPAL_PARTNER_SELLER_BOARDED . $_missedRecommendedPermissions;
                        }
                    } else {
                        $_missedPermissions = '';
                        $_tmp = $this->checkScopes($scopes);
                        if (is_array($_tmp) && !empty($_tmp['missed'])) {
                            $_missedPermissions = ' ' . implode("<br>\n", $_tmp['missed']);
                        }
                        $this->errors[] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR_PERMISSIONS . $_missedPermissions; //"Undefined scopes";
                    }
                } else {
                    if ($response->getPaymentsReceivable()) {
                        $this->errors[] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR_RECEIVABLE;
                    } elseif ($response->getPrimaryEmailConfirmed()) {
                        $this->errors[] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR_EMAIL;
                    } else {
                        $this->errors[] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR_PERMISSIONS;
                        // "Seller hasn't accepted permissions to make payments or primary email is not confirmed";
                    }
                }
            } else {
                $this->errors[] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR;
            }
        } catch (\Exception $ex) {
            $this->sendDebugEmail($ex);
            $this->errors[] = $ex->getMessage();
        }
    }

    private function checkScopes(array $scopes, $notes = false) {
        $ret = false;
        $required = [
          'https://uri.paypal.com/services/payments/realtimepayment' => TEXT_PAYPAL_PARTNER_GRANT_PAYMENT,
          'https://uri.paypal.com/services/payments/payment/authcapture' => TEXT_PAYPAL_PARTNER_GRANT_AUTH,
        ];
        $recommended = []; // right now seems useless as seller could axcept or decline *all* requested scopes (permissions) Probably will change in future and/or depends on seller country (according PayPal policies)
        if ($notes) {
            $recommended = [
              'https://uri.paypal.com/services/payments/refund' => TEXT_PAYPAL_PARTNER_GRANT_REFUND,
              'https://uri.paypal.com/services/customer/merchant-integrations/read' => TEXT_PAYPAL_PARTNER_GRANT_INTEGRATION,
            ];
        }

        $platformId = $this->getPlatformId();
        $seller = $this->getSeller($platformId);
        if (!empty($seller->fee_percent)) {
            $required['https://uri.paypal.com/services/payments/delay-funds-disbursement'] = TEXT_PAYPAL_PARTNER_GRANT_DISBURSEMENT;
            $required['https://uri.paypal.com/services/payments/partnerfee'] = TEXT_PAYPAL_PARTNER_GRANT_PARTNERFEE;
        }
        $required = array_filter($required);

        if (count(array_intersect($scopes, array_merge(array_keys($required), array_keys($recommended)))) == (count($required) + count($recommended))) {
            $ret = true;
        } else {
            $ret = ['missed' => [], 'recommended' => []];
            foreach ($required as $k => $v) {
                if (!in_array($k, $scopes)) {
                    $ret['missed'][] = $v;
                }
            }
            foreach ($recommended as $k => $v) {
                if (!in_array($k, $scopes)) {
                    $ret['recommended'][] = $v;
                }
            }
        }
        return $ret;
    }

    public function extra_params() {
        $platform_id = (int) \Yii::$app->request->get('platform_id');
        if ($platform_id == 0) {
            $platform_id = (int) \Yii::$app->request->post('platform_id');
        }

        $this->call_webhooks(); //?
        /** @var lib\PaypalPartner\ExtraConfig $extra */
        $extra = $this->getExtraConfigClass();
        $psi_id =  \Yii::$app->request->post('psi_id', \Yii::$app->request->get('psi_id', false));
        if ($psi_id) {
            $seller = $this->getSellerById($platform_id, (int)$psi_id);
        } else {
            $seller = $this->getSeller($platform_id);
        }

        $address = new \common\forms\AddressForm(['scenario' => \common\forms\AddressForm::BILLING_ADDRESS]);
        $messageStack = \Yii::$container->get('message_stack');
        if (\Yii::$app->request->isPost) {
            if ($seller->isNewRecord) {
                $seller->loadDefaultValues();
            }
            $seller->load(\Yii::$app->request->post());
            if (!empty($seller->own_client_id) && !empty($seller->own_client_secret) && !empty($seller->payer_id)) {
                $seller->is_onboard = 1;
                $seller->status = 1;
            }

            $a3dsOn = \Yii::$app->request->post('paypal_partner_ccp_status', 0);
            $a3dsSettings = [];
            if ($a3dsOn) {
                $a3dsSettings['status'] = 0;
                foreach (self::$threeDSDefaults as $k => $v) {
                    $a3dsSettings[$k] = \Yii::$app->request->post('paypal_partner_3ds_' . $k, 0);
                }
            }
            $seller->paypal_partner_ccp_status = $a3dsOn;
            $seller->three_ds_settings = json_encode($a3dsSettings);

            if (true || $this->boardViaLink()) {
                $seller->save(false);
                $seller->refresh();
                // cleanup
                lib\PaypalPartner\models\SellerInfo::deleteAll(
                  ' partner_id="' . $seller->partner_id . '"' .
                  ' and platform_id=' . $seller->platform_id .
                  ' and psi_id<>' . $seller->psi_id
                );

            } else {
//adddress generally is not required (only 1 time - to register new PP account)
                if ($address->load(\Yii::$app->request->post()) /*&& $address->validate()*/) {
                    $book = [];
                    foreach ($address->getAttributes() as $key => $name) {
                        $book['entry_' . $key] = $name;
                    }
                    $book['entry_country_id'] = $address->country;
                        if ($seller->load($book, '') /*&& $seller->validate()*/) {
                        $seller->save();
                    } else {
                        $err = '';
                        foreach ($address->getErrors() as $error) {
                            $err .= (is_array($error) ? implode("<br>", $error) : $error) . '<br>';
                        }
                        if (!empty($err)) {
                            $messageStack->add($err);
                        }
                    }
                } else {
                    $err = '';
                    foreach ($address->getErrors() as $error) {
                        $err .= (is_array($error) ? implode("<br>", $error) : $error) . '<br>';
                    }
                    if (!empty($err)) {
                        $messageStack->add($err);
                    }
                }
            }
        } else {
            if ($seller->isNewRecord) {
                try {
                    $seller->save(false); // save tracking id else won't be able to get boarding results.
                } catch (\Exception $e) {
                    \Yii::error('seller tracking id is not saved ' . $e->getMessage(), 'paypal_partner');
                }
                $platformConf = new \common\classes\platform_config($platform_id);
                $tmp = explode(' ', $platformConf->getPlatformDataField('platform_owner'), 2);

                if (($platformConf->is_default_address ?? null) && $platformConf->platform_id != $platformConf->default_platform_id) {
                    $platformConf = new \common\classes\platform_config($platformConf->default_platform_id);
                }
                $defAddr = $platformConf->getPlatformAddress();
                $addr = \common\helpers\Address::skipEntryKey([$defAddr]);

                $defAddr['firstname'] = $addr['firstname'] = $tmp[0];
                $defAddr['lastname'] = $addr['lastname'] = $tmp[1];
                $defAddr['telephone'] = $addr['telephone'] = $platformConf->getPlatformDataField('platform_telephone');
                $seller->email_address = $platformConf->getPlatformDataField('platform_email_address');
                try {
                    $book = [];
                    foreach ($defAddr as $key => $name) {
                        if (strpos($key, 'entry_') === false) {
                            $key = 'entry_' . $key;
                        }
                        $book[$key] = $name;
                    }
                    $seller->load($book, '');
                    $seller->save(false);
                } catch (\Exception $e) { //not important if platform values don't match requirements
                }
            } else {
                $addr = \common\helpers\Address::skipEntryKey([$seller->toArray()]);
            }
            $address->preload($addr);
        }
        if (!$this->errors) {
            if (!\Yii::$app->request->isPost) {
                $this->errors = $messageStack->output();
            }
        }

        return @$extra::widget([
              'module' => $this,
              'seller' => $seller,
              'address' => $address,
              'errors' => $this->errors,
        ]);
    }

    function call_webhooks() {
        $get = \Yii::$app->request->get();
        $post = \Yii::$app->request->post();
        /** @var \common\helpers\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');
        if (!empty($post['order_id']) && $this->manager->has('pay_order_id') ) {
            $order_id = $this->manager->get('pay_order_id');
            if ($order_id != $post['order_id']) {
                $order_id = $post['order_id'];
                $this->manager->set('pay_order_id', $order_id);
            }
            if ($this->isPartlyPaid() ){
                $this->onlySiteAddress = true; // update and pay - don't allow to change addresses
            }
        }

        switch ($get['action'] ?? null) {
            case 'returnAPM':
                echo $this->returnAPM($get['method']);
                break;
            case 'cancelAPM':
                echo $this->cancelAPM($get['method']);
                break;
            case 'processWebhook':
                echo $this->onProcessWebhook();
                break;
            case 'processOnBoard':
                if (($get['subaction']??'')=='checkDetails') {
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return $this->validateCredentials(\Yii::$app->request->post());

                } elseif (($get['subaction']??'')=='links') {
                    $psi_id = \Yii::$app->request->post('psi_id', false);
                    if (!($platformId = \Yii::$app->request->post('platform_id', false))) {
                        $platformId = $this->getPlatformId();
                    }
                    if (\Yii::$app->request->isPost) {
                        $retUrl = \Yii::$app->request->post('curUrl', false);
                        $check = false;
                        try {
                            $url_parts = parse_url($retUrl);
                            if (!empty($url_parts['host'])) {
                                $check = \common\models\Platforms::find()->andWhere([
                                  'or',
                                  ['like', 'platform_url', $url_parts['host']],
                                  ['like', 'platform_url_secure', $url_parts['host']],
                                ])->count();
                            }
                        } catch (\Exception $e) { 
                            \Yii::warning(" #### " .print_r($e->getMessage() . $e->getTraceAsString(), true), 'TLDEBUG');
                        }
                        if (!$check) {
                            $retUrl = false;
                        }
                    }
                    if ($psi_id) {
                        $tmp = $this->getInstallOptions($platformId, false, $retUrl);
                    } else {
                        $tmp = $this->getInstallOptions($platformId, false, $retUrl);
                    }
                    $ret = [
                      'live' => 'https://www.paypal.com/bizsignup/partner/entry?partnerClientId=' . $tmp['link_params']['partnerClientId'] . '&partnerId=' . $tmp['link_params']['partnerId'] . '&displayMode=minibrowser&partnerLogoUrl=' . $tmp['link_params']['partnerLogoUrl'] . '&returnToPartnerUrl=' . $tmp['link_params']['return_url'] . '&integrationType=FO&features=PAYMENT&country.x=' . $tmp['link_params']['country'] . '&locale.x=' . $tmp['link_params']['locale'] . '&product=ppcp&sellerNonce=' . $tmp['link_params']['sellerNonce'],
                      'sandbox' => 'https://www.sandbox.paypal.com/bizsignup/partner/entry?partnerClientId=' . $tmp['link_params_sandbox']['partnerClientId'] . '&partnerId=' . $tmp['link_params_sandbox']['partnerId'] . '&displayMode=minibrowser&partnerLogoUrl=' . $tmp['link_params_sandbox']['partnerLogoUrl'] . '&returnToPartnerUrl=' . $tmp['link_params_sandbox']['return_url'] . '&integrationType=FO&features=PAYMENT&country.x=' . $tmp['link_params_sandbox']['country'] . '&locale.x=' . $tmp['link_params_sandbox']['locale'] . '&product=ppcp&sellerNonce=' . $tmp['link_params_sandbox']['sellerNonce'],
                    ];
                    unset($tmp['link_params']);
                    unset($tmp['link_params_sandbox']);
                    $ret = $tmp + $ret;
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return $ret;
                } else {
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    $ret = $this->onBoardingProcess();
                    return ['html' => $ret];
                    
                }
                break;
            case 'sellerDetails':
                $this->fetchOwnKeys();
                $platformId = $this->getPlatformId();
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['reload' => 1, 'params' => 'platform_id=' . $platformId . '&set=payment&module=' . $this->code];
                break;
            case 'checkOnBoarded':
                $this->checkOnBoarded();
                if ($get['result'] == 'show') {
                    $extra = $this->getExtraConfigClass();
                    echo $extra::widget([
                      'module' => $this,
                      'mode' => 'info',
                      'errors' => $this->errors,
                      'messages' => $this->messages,
                    ]);
                }
                break;
            case 'deleteSeller':
                $platformId = $platform_id = (int)$this->getPlatformId();
                $seller = $this->getSeller($platformId);
                $ck = \Yii::$app->request->get('ck', '');
                if (!$seller->isNewRecord && $ck == $seller->tracking_id) {

                    //delete seller after new details save.
                    //$seller->delete();
                    //echo "<div classs='popup-wrap'>Deleted</div><script>window.location.reload()</script>";

                    //cleanup temp sellers (could be finished connection)
                    lib\PaypalPartner\models\SellerInfo::deleteAll('status=-1 and own_client_secret<>"" and own_client_id<>"" and platform_id=' . (int)$platformId);
                    $seller = $this->getSeller($platformId, false, true);
                    $extra = $this->getExtraConfigClass();
    /*              $platformConf = new \common\classes\platform_config($platform_id);
                    $tmp = explode(' ', $platformConf->getPlatformDataField('platform_owner'), 2);

                    if (($platformConf->is_default_address ?? null) && $platformConf->platform_id != $platformConf->default_platform_id) {
                        $platformConf = new \common\classes\platform_config($platformConf->default_platform_id);
                    }
                    $defAddr = $platformConf->getPlatformAddress();
                    $addr = \common\helpers\Address::skipEntryKey([$defAddr]);

                    $defAddr['firstname'] = $addr['firstname'] = $tmp[0];
                    $defAddr['lastname'] = $addr['lastname'] = $tmp[1];
                    $defAddr['telephone'] = $addr['telephone'] = $platformConf->getPlatformDataField('platform_telephone');
                    $seller->email_address = $platformConf->getPlatformDataField('platform_email_address');
                    try {
                        $book = [];
                        foreach ($defAddr as $key => $name) {
                            if (strpos($key, 'entry_') === false) {
                                $key = 'entry_' . $key;
                            }
                            $book[$key] = $name;
                        }
                        $seller->load($book, '');
                        $seller->save(false);
                    } catch (\Exception $e) { //not important if platform values don't match requirements
                    }*/
                    try {
                        $seller->save(false);
                        $seller->refresh();
                    } catch (\Exception $e) {
                        \Yii::error('seller tracking id is not saved ' . $e->getMessage(), 'paypal_partner');
                    }
                    $html = $extra::widget([
                          'module' => $this,
                          'seller' => $seller,
                    ]);
                }
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['reload' => 1, 'params' => 'platform_id=' . $platformId . '&set=payment&module=' . $this->code . '&setup=1&psi_id=' . $seller->psi_id];
                //return ['preload' => 1, 'html' => $html, 'block' => 'module-extra', 'params' => 'platform_id=' . $platformId . '&set=payment&module=' . $this->code];
                break;
            case 'subscribeSeller':
                $platformId = $this->getPlatformId();
                $seller = $this->getSeller($platformId);
                $ck = \Yii::$app->request->get('ck', '');
                if (!$seller->isNewRecord && $ck == $seller->tracking_id) {
                    $this->addWebHooks();
                }
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['reload' => 1, 'params' => 'platform_id=' . $platformId . '&set=payment&module=' . $this->code];
                break;
                
            case 'customerDetails':
                $this->manager->setPayment($this->code);
                $ret = [];
                if (!empty($get['option']) && $get['option']=='card') {
                    $ret = $this->generateCCCustomerDetails();
                }
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return $ret;
                break;
            case 'createOrder':
                $this->manager->setPayment($this->code);
                //customer assigned, but addresses could be in post only
                $data = \Yii::$app->request->post();
                $this->manager->remove($this->code . 'UseAddresses');
                if ($this->isPartlyPaid()) {

                } elseif (!empty($data)) {
                    //checkout - fill/check addresses
                    //incorrect address could be ignored (except virtual products in cart):
                    //customer changed his mind and chose PP express on checkout same way as on shopping cart
                    $error = '';
                    try {
                        ///which address forms are filled in 
                        $_forms = [];
                        $_forms[] = $this->manager->getBillingForm(false);
                        $_forms[] = $this->manager->getShippingForm(false);
                        foreach ($_forms as $key => $addressForm) {
                            if (isset($data[$addressForm->formName()])) {
                                $addressForm->load($data);
                                if (!$addressForm->notEmpty()) {
                                    unset($_forms[$key]);
                                }
                            } else {
                                unset($_forms[$key]);
                            }
                        }

                        // download - address should be filled in
                        // validate any filled in address
                        if (!$this->manager->isShippingNeeded() || !empty($_forms)) {
                            $shipAsBill = \Yii::$app->request->post('ship_as_bill', false) && true;
                            $shipAsBill = $shipAsBill || (\Yii::$app->request->post('bill_as_ship', false) && true);

                            $valid = $this->manager->validateAddressForms(\Yii::$app->request->post(), '', $shipAsBill);
                            if (!$valid) {
                                $messageStack = \Yii::$container->get('message_stack');
                                $errors = $messageStack->asArray('one_page_checkout');
                                $resp = [];
                                if (!empty($errors) && is_array($errors)) {
                                    $error = implode('<BR>', \yii\helpers\ArrayHelper::getColumn($errors['messages'], 'text'));
                                    $resp = ['error' => 1, 'message' => $error];
                                }
                                echo json_encode($resp);
                                exit();
                                break;

                            } elseif (!empty($_forms)) {
                                $tmp = [];
                                foreach ($_forms as $addressForm) {
                                    $tmp[] = $addressForm->formName();
                                }
                                $this->manager->set($this->code . 'UseAddresses', $tmp);

                            } elseif (!empty($data['billing_ab_id'])) {
                                $tmp = [];
                                $tmp[] = 'Billing_address';
                                $this->manager->set($this->code . 'UseAddresses', $tmp);
                            }

                        } else {
                            $tmp = [];
                            if (!empty($data['billing_ab_id'])) {
                                $tmp[] = 'Billing_address';
                            }
                            if (!empty($data['shipping_ab_id'])) {
                                $tmp[] = 'Shipping_address';
                            }
                            if (!empty($tmp)) {
                                $this->manager->set($this->code . 'UseAddresses', $tmp);
                            }
                        }
                        if (\Yii::$app->user->isGuest && !empty($data['checkout']['opc_temp_account']) && !empty($data['checkout']['email_address'])) {
                            if ($this->manager->validateContactForm($data)) {
                                $this->manager->registerCustomerAccount(0);
                            } else {
                                $messageStack = \Yii::$container->get('message_stack');
                                $errors = $messageStack->asArray('one_page_checkout');
                                $resp = [];
                                if (!empty($errors) && is_array($errors)) {
                                    $error = implode('<BR>', \yii\helpers\ArrayHelper::getColumn($errors['messages'], 'text'));
                                    $resp = ['error' => 1, 'message' => $error];
                                }
                                echo json_encode($resp);
                                exit();
                            }
                        }
                    } catch (\Exception $e ) {
                        // exit here if missed/empty address is critical
                        \Yii::warning(print_r($e->getMessage() . ' ' . $e-> getTraceAsString(), true), 'TLDEBUG');
                        if (!$this->manager->isShippingNeeded() ) {
                            $resp = ['error' => 1];
                            echo json_encode($resp);
                            exit();
                            break;
                        }
                    }
                }

                $pRes = $this->createOrder($data);
                $response = [];
                if (is_object($pRes) && is_object($pRes->result)) {
                    $response['id'] = $pRes->result->id;
                    if (!empty($get['option'])) {
                        $response = $this->validateAPMCreateOrderResponse($get['option'], $pRes);
                    }

                } elseif (is_array($pRes) && !empty($pRes['error']) ) {
                    $response = $pRes;
                }
                echo json_encode($response);
                exit();
                break;
            case 'retrieveOrder':
                //if PP order is OK -  login/create customer, select/add addresses, save details and go to checkout/process
                // pickup: reset shipping address in the PP order (it's set after login by default) and cause error during auth/capture.
                $orderId = \Yii::$app->request->get('id');
                $this->manager->setPayment($this->code);
                $this->manager->set('partner_order_pending', false);
                $json = [];
                if ($orderId) {
                    if (!$this->manager->isInstance()) {
                        if (!$this->isPartlyPaid()) {
                            $order = $this->manager->createOrderInstance('\common\classes\Order');
                        }
                    }
                    $response = $this->getOrder($orderId);
                    if (!empty($response->result->status) && 
                        (in_array(strtoupper($response->result->status), ['CREATED', 'APPROVED'])
                        ||
                        ( strtoupper($response->result->status) == 'PENDING_APPROVAL' && !empty($response->result->payment_source->pay_upon_invoice)
                            && !empty($response->result->processing_instruction) && $response->result->processing_instruction == 'ORDER_COMPLETE_ON_PAYMENT_APPROVAL'
                            )
                        )
                        ) {
                        if ( strtoupper($response->result->status) == 'PENDING_APPROVAL' && !empty($response->result->payment_source->pay_upon_invoice)
                            && !empty($response->result->processing_instruction) && $response->result->processing_instruction == 'ORDER_COMPLETE_ON_PAYMENT_APPROVAL'
                            ) {
                            $this->manager->get('partner_order_pending');
                            $this->manager->set('partner_order_pending', 'pay_upon_invoice');
                        }
                        $updateAddress = false;
///2DO no $payer in card payments!!
                        $payer = $response->result->payer;
                        if (\Yii::$app->user->isGuest) {
                            $customer = \common\components\Customer::find()->where(['customers_email_address' => $payer->email_address, 'opc_temp_account' => 0, 'customers_status' => 1])->limit(1)->one();
                            if ($customer) {
                                $customer->setLoginType(\common\components\Customer::LOGIN_WITHOUT_CHECK);
                                global $cart;
                                $cartNow = clone $cart;
                                if ($customer->loginCustomer($payer->email_address, $customer->customers_id)) {
                                    $customer = \Yii::$app->user->getIdentity();
                                    $cart = $cartNow;
                                    unset($cartNow);
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
                                $model->email_address = $payer->email_address;
                                $model->firstname = $payer->name->given_name;
                                $model->lastname = $payer->name->surname;

                                $country = \common\helpers\Country::get_country_info_by_iso($payer->address->country_code);
                                $model->country = $country['id'] ?? 0;

                                $customer = new \common\components\Customer();
                                $customer->registerCustomer($model);
                                $updateAddress = true;
                            }
                        } else {
                            $customer = \Yii::$app->user->getIdentity();
                        }

                        if (isset($customer->customers_telephone) && trim($customer->customers_telephone) == '' && isset($payer->phone) && isset($payer->phone->phone_number) && isset($payer->phone->phone_number->national_number) && trim($payer->phone->phone_number->national_number) != '') {
                            $customer->customers_telephone = $payer->phone->phone_number->national_number;
                            $customer->save(false);
                        }

                        $payerAddresses = [];
                        $payerAddresses['billto'] = $response->result->payer->address??(new \stdClass());
                        if (!empty($payerAddresses['billto']) && count((array)$payerAddresses['billto'])>2) {
                            $payerAddresses['billto']->firstname = $payer->name->given_name;
                            $payerAddresses['billto']->lastname = $payer->name->surname;
                        }

                        $payerAddresses['sendto'] = $response->result->purchase_units[0]->shipping->address??(new \stdClass());
                        if (!empty($response->result->purchase_units[0]->shipping->name->full_name)) {
                            $_tmp = explode(' ', $response->result->purchase_units[0]->shipping->name->full_name, 2);
                            $payerAddresses['sendto']->firstname = $_tmp[0]??'';
                            $payerAddresses['sendto']->lastname = $_tmp[1]??'';
                        } else {
                            $payerAddresses['sendto']->firstname = $payer->name->given_name;
                            $payerAddresses['sendto']->lastname = $payer->name->surname;
                        }
                        $sendto = $billto = false;

//2test dont override billing address if selected.
                        $hasAddresses = [];
                        if ($this->manager->has($this->code . 'UseAddresses')){
                            $useAddresses = $this->manager->get($this->code . 'UseAddresses');
                            if (is_array($useAddresses)) {
                                $addressForm = $this->manager->getBillingForm(null, false);
                                if (in_array($addressForm->formName(), $useAddresses) && $this->manager->has('billto')) {
                                    $savedBillto = $this->manager->get('billto');
                                    $savedBilltoEst = $this->manager->get('estimate_bill');
                                    if (!$payerAddresses && is_numeric($savedBillto) && $savedBillto > 0) {
                                        unset($payerAddresses['billto']);
                                        $billto = $savedBillto;

                                    } elseif (is_array($savedBillto) || is_array($savedBilltoEst)) {
                                        if (!is_array($savedBillto)) {
                                            $savedBillto = $savedBilltoEst;
                                        }
                                        foreach( [
                                            'firstname' => 'firstname',
                                            'lastname' => 'lastname',
                                            'postcode' => 'postal_code',
                                            'street_address' => 'address_line_1',
                                            'suburb' => 'address_line_2',
                                            'city' => 'admin_area_2',
                                            'state' => 'state',
                                            'country_iso_code_2' => 'country_code',

                                            'company' => 'company',
                                            'company_vat' => 'company_vat',
                                            'customs_number' => 'customs_number',
                                            'gender' => 'gender'] as $saved => $pp) {
                                            if (!empty($savedBillto[$saved])) {
                                                $payerAddresses['billto']->$pp = $savedBillto[$saved];
                                            }
                                        }
                                    } 
                                }
                                $addressForm = $this->manager->getShippingForm(null, false);
                                if (in_array($addressForm->formName(), $useAddresses) && $this->manager->has('sendto')) {
                                    $savedSendto = $this->manager->get('sendto');
                                    if (is_numeric($savedSendto) && $savedSendto>0) { // shipping address could change on PP
                                        $addressForm = $this->manager->getShippingForm($savedSendto);
                                        $savedSendto = $addressForm->attributes;
                                        $tmp = \common\helpers\Country::get_country_info_by_id($savedSendto['country']);
                                        $savedSendto['country_iso_code_2'] = $tmp['countries_iso_code_2']??'';

                                    }
                                    if (is_array($savedSendto)) {
                                    //"city":"ship City","state":"","country_code":"ZW","postal_code":"shipCode"
                                        //if address "same" update with some saved details  else - leave as is
                                        if (
                                               $savedSendto['postcode'] == $payerAddresses['sendto']->postal_code
                                            && $savedSendto['city'] == $payerAddresses['sendto']->admin_area_2
                                            && $savedSendto['country_iso_code_2'] == $payerAddresses['sendto']->country_code
                                            ) {
                                            foreach( [
                                                'firstname' => 'firstname',
                                                'lastname' => 'lastname',
                                                /*'postcode' => 'postal_code',
                                                'street_address' => 'address_line_1',
                                                'suburb' => 'address_line_2',
                                                'city' => 'admin_area_2',
                                                'state' => 'state',
                                                'country_iso_code_2' => 'country_code',*/

                                                'company' => 'company',
                                                'company_vat' => 'company_vat',
                                                'customs_number' => 'customs_number',
                                                'gender' => 'gender'] as $saved => $pp) {
                                                if (!empty($savedSendto[$saved])) {
                                                    $payerAddresses['sendto']->$pp = $savedSendto[$saved];
                                                }
                                            }

                                        }
                                    }
                                } elseif (empty($payerAddresses['sendto'])) {
                                    // downloadable
                                }
                            }
                        }
                        foreach ($payerAddresses as $type => $payerAddress) {
                            if (!empty($payerAddress) && count((array)$payerAddress)>2) {
                                $country = \common\helpers\Country::get_country_info_by_iso($payerAddress->country_code);
                                $ship_zone_id = 0;
                                $ship_zone = $payerAddress->admin_area_1;
                                $zone = \common\models\Zones::find()->where(['zone_country_id' => $country['id']])
                                        ->andWhere(['or', ['zone_name' => $payerAddress->admin_area_1], ['zone_code' => $payerAddress->admin_area_1]])
                                        ->limit(1)->one();
                                if ($zone) {
                                    $ship_zone_id = $zone->zone_id;
                                }

                                $ab = \common\models\AddressBook::find()
                                        ->where(['and',
                                          ['customers_id' => $customer->customers_id],
                                          ['entry_firstname' => $payerAddress->firstname??''],
                                          ['entry_lastname' => $payerAddress->lastname??''],
                                          ['entry_street_address' => $payerAddress->address_line_1??''],
                                          ['entry_postcode' => $payerAddress->postal_code??''],
                                          ['entry_city' => $payerAddress->admin_area_2??''],
                                          ['entry_country_id' => $country['id']]
                                        ])->limit(1)->one();
                                if ($ab) {
                                    $$type = $ab->address_book_id;
                                } else {
                                    $sql_data_array = array(
                                        'customers_id' => $customer->customers_id,
                                        'entry_firstname' => $payerAddress->firstname??'',
                                        'entry_lastname' => $payerAddress->lastname??'',
                                        'entry_street_address' => $payerAddress->address_line_1??'',
                                        'entry_suburb' => $payerAddress->address_line_2??'',
                                        'entry_postcode' => $payerAddress->postal_code??'',
                                        'entry_city' => $payerAddress->admin_area_2??'',
                                        'entry_company' => $payerAddress->company??'',
                                        'entry_company_vat' => $payerAddress->company_vat??'',
                                        'entry_customs_number' => $payerAddress->customs_number??'',
                                        'entry_gender' => $payerAddress->gender??'',
                                        'entry_country_id' => $country['id']
                                    );

                                    if ($ship_zone_id > 0) {
                                        $sql_data_array['entry_zone_id'] = $ship_zone_id;
                                        $sql_data_array['entry_state'] = '';
                                    } else {
                                        $sql_data_array['entry_zone_id'] = '0';
                                        $sql_data_array['entry_state'] = $ship_zone;
                                    }

                                    if ($updateAddress) {
                                        $aBook = $customer->updateAddress($customer->customers_default_address_id, $sql_data_array);
                                    } else {
                                        $aBook = $customer->addAddress($sql_data_array);
                                    }
                                    $$type = $aBook->address_book_id;
                                }

                                if ($type == 'sendto' && empty($billto)) {
                                    $this->manager->get('billto');
                                    $this->manager->set('billto', $sendto);
                                } elseif ($type == 'billto' && empty($sendto)) {
                                    $this->manager->get('sendto');
                                    $this->manager->set('sendto', $billto);
                                }
                                
                                $this->manager->get($type);
                                $this->manager->set($type, $$type);
                            }
                        }
                        if ($this->manager->isShippingNeeded()) {
                            $this->manager->getShippingQuotesByChoice();
                        }

                        if ($customer->customers_id) {
                            $this->manager->assignCustomer($customer->customers_id);
                        }

                        $this->manager->set('partner_order_id', $orderId);

                        $json['ok'] = true;
                        //$json['url'] = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
                        $json['url'] = $this->getCheckoutUrl([], self::PROCESS_PAGE);
                        if ($this->debug) {
                            \Yii::warning(print_r($response, true), 'TLDEBUGretrievepp');
                        }
                    } else {
                        $json['error'] = PAYPAL_PARTNER_ORDER_DETAILS_ERROR;
                    }
                } else {
                    $json['error'] = PAYPAL_PARTNER_ORDER_ID_ERROR;
                }
                echo json_encode($json);
                exit();
                break;
            case 'patchOrder'://for js
                try {
                    $tmp = file_get_contents("php://input");
                    $request = json_decode($tmp, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
                } catch (\Exception $e) {
                   \Yii::warning(" pppPatchOrderEmptyData #### " .print_r($e->getMessage(), true), 'TLDEBUG');
                }
                if (!$request['orderID']) {
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return [];
                    //throw new \Exception('Invalid Order ID');
                }

                if ($this->debug) {
                    \Yii::warning("patchOrder \$request " . print_r($request, true), 'TLDEBUG');
                }

                if (!$this->manager->isInstance()) {
                    $order = $this->manager->createOrderInstance('\common\classes\Order');
                }
                //$pOrder = $this->getOrder($request['orderID']);
                $currency_code = \Yii::$app->settings->get('currency');
                if (!empty($order->info['currency'])) {
                    $currency_code = $order->info['currency'];
                }

                $estimateShippingChanged = true;
                if (!empty($request['shipping_address'])) {
                    $country = \common\helpers\Country::get_country_info_by_iso($request['shipping_address']['country_code']);
                    if ($this->manager->has('estimate_ship')) {
                        $old = $this->manager->get('estimate_ship');
                        //2check - old is empty
                    }
                    if (!empty($old) &&
                        !empty($old['country_id']) && isset($old['postcode']) && isset($old['zone']) &&
                        $old['country_id'] == $country['id'] &&
                        $old['postcode'] == $request['shipping_address']['postal_code'] &&
                        $old['zone'] == $request['shipping_address']['state']
                    ) {
                        $estimateShippingChanged = false;
                    } 
                    $this->manager->set('estimate_ship', ['country_id' => $country['id'], 'postcode' => $request['shipping_address']['postal_code'], 'zone' => $request['shipping_address']['state']]);
                    $this->manager->set('estimate_bill', ['country_id' => $country['id'], 'postcode' => $request['shipping_address']['postal_code'], 'zone' => $request['shipping_address']['state']]);
                } else {
                    $estimateShippingChanged = false;
                }

                if ($this->manager->isShippingNeeded()) {
                    $this->manager->resetDeliveryAddress();
                }
                if (\Yii::$app->user->isGuest) {
                    $this->manager->resetBillingAddress();
                }


                if (!empty($request['selected_shipping_option']['id'])) {
                    $shipping = tep_db_input(tep_db_prepare_input($request['selected_shipping_option']['id']));
                    if ($shipping) {
                        $this->manager->setSelectedShipping($shipping);
                    }
                    $this->manager->checkoutOrder();
                    $_shipping = $this->manager->getShipping();
                    if ($_shipping) {
                        $module = $this->manager->getShippingCollection()->get($_shipping['module']);
                        /*if (is_object($module) && method_exists($module, 'setAdditionalParams')) {
                            $module->setAdditionalParams(Yii::$app->request->post());
                        } else {
                            $this->manager->remove('shippingparam');
                        }*/
                        $this->manager->remove('shippingparam');
                    }
                }

                $this->manager->getShippingQuotesByChoice();
                $this->manager->checkoutOrderWithAddresses();

                $this->manager->totalProcess();

                $order = $this->manager->getOrderInstance();

                $details = $this->getCartDetails();
                
                $resp = [];
                $resp[] = [
                            'op' => 'replace',
                            'path' => '/purchase_units/@reference_id==\'default\'/amount',
                            'value' => [
                                    'currency_code' => $currency_code,
                                    'value' => $this->formatRaw($order->info['total_inc_tax']),
                                    'breakdown' => $details['totals']
                            ]
                ];


                $options = [];
                if (($estimateShippingChanged 
                    || ( isset($request['selected_shipping_option']['type']) && $request['selected_shipping_option']['type'] == 'PICKUP')
                    )
                    && $this->manager->isShippingNeeded()) {
                    //$this->manager->prepareEstimateData();
                    $options = $this->getShippingOptions($currencies, $currency_code);
                    /*
                    if (isset($options[0]['type']) && $options[0]['type'] == 'PICKUP') {
                        $_label = '';
                        foreach ($options as $option) {
                            if (!empty($option['selected'])) {
                                $_label = $option['label'];
                                break;
                            }
                        }
                        $resp[] = [
                            'op' => (!empty($request['shipping_details'])?'replace':'add'),
                            'path' => '/purchase_units/@reference_id==\'default\'/shipping/name',
                            'value' =>  ['full_name' => substr('S2S ' . $_label, 0, 300)],
                        ];
                    }*/
                    
                    if (!empty($options) && $estimateShippingChanged) {
                        $resp[] = [
                            'op' => (!empty($request['selected_shipping_option'])?'replace':'add'),
                            'path' => '/purchase_units/@reference_id==\'default\'/shipping/options',
                            'value' => $options
                        ];
                    } elseif ($this->manager->isShippingNeeded()) {
                        //shipping is needed but not available
                        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                        return [];
                    }
                }
                /* PP docs is shit
                 https://developer.paypal.com/docs/business/javascript-sdk/javascript-sdk-reference/#onshippingchange
                 While the buyer is on the PayPal site, you can update their shopping cart to reflect the shipping address they chose on PayPal. You can use the callback to:
                 ....
                 Change the line items in the cart.
                 *
                 https://developer.paypal.com/docs/api/orders/v2/#orders_patch
                 Patchable attributes or objects:
                 *
                 no items in the list :(
                 but patchable according
                 https://www.paypal-community.com/t5/REST-APIs/v2-update-patch-Order-INVALID-JSON-POINTER-FORMAT/td-p/1833325

                 but seems only via REST (not JS) API
                 *
                * /
                if ($oldSubtotal != $this->formatRaw($order->info['subtotal_inc_tax'])) {
                    // (not) Taxable could be selected, items subtotal changed - patch items with prices.
                    $data = [[
                                'op' => 'replace',
                                'path' => '/purchase_units/@reference_id==\'default\'',
                                'value' => [
                                    'items' => $details['items'],
                                    'amount' => [
                                              'currency_code' => $currency_code,
                                              'value' => $this->formatRaw($order->info['total_inc_tax']),
                                              'breakdown' => $details['totals']
                                    ]
                                ]
                        ]];
                    if (!empty($options)) {
                        $data[0]['value']['shipping'] =  [
                          'options' => $options
                        ];
                    }
                    $this->patchOrder($request['orderID'], $data);
                    //$resp = [$resp[0]];
                    $resp = $data;
                    //$estimateShippingChanged = false;
                } /* products change */

                if ($this->debug ) {
                    \Yii::warning("patchOrder resp " . print_r($resp, true), 'TLDEBUG');
                }
                /* tax total changed - reload
                 if (!$estimateShippingChanged) {
                    $resp = ['same' => 1];
                }*/
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return $resp;
        }
    }

    public function hasOwnKeys() {
        $platformId = $this->getPlatformId();
        $seller = $this->getSeller($platformId);
        return (!empty($seller->own_client_id) && !empty($seller->own_client_secret));
    }

    public function getWebHookUrl() {
        if (function_exists('tep_catalog_href_link')) {
            $url = tep_catalog_href_link('callback/webhooks.payment.' . $this->code, http_build_query(['action' => 'processWebhook']));
        } else {
            $url = \Yii::$app->urlManager->createAbsoluteUrl(['callback/webhooks.payment.' . $this->code, 'action' => 'processWebhook']);
        }

//$url = 'https://dev5.trueloaded.co.uk/callback/webhooks.payment.paypal_partner?action=processWebhook&v=1';
        // debug
        return $url;
    }

    public function getStatusCode($transaction) {
        $type = $this->transactionType($transaction);
        $sk = \common\helpers\OrderPayment::OPYS_PENDING;
        if (!empty($transaction->result->status))
          switch (strtoupper($transaction->result->status)) {
            case 'COMPLETED':
            case 'PARTIALLY_REFUNDED':
                if (in_array($type, ['authorize', 'autorization'])) { //always pending
                    $sk = \common\helpers\OrderPayment::OPYS_PENDING;
                } elseif (in_array($type, ['refund'])) {
                    $sk = \common\helpers\OrderPayment::OPYS_REFUNDED;
                } else {
                    $sk = \common\helpers\OrderPayment::OPYS_SUCCESSFUL;
                }
                break;
            case 'DECLINED':
                $sk = \common\helpers\OrderPayment::OPYS_REFUSED;
                break;
            case 'VOIDED':
                $sk = \common\helpers\OrderPayment::OPYS_CANCELLED;
                break;
            case 'REFUNDED':
                if (in_array($type, ['authorize', 'autorization'])) { //always pending
                    $sk = \common\helpers\OrderPayment::OPYS_PENDING;
                } elseif (in_array($type, ['refund'])) { 
                    $sk = \common\helpers\OrderPayment::OPYS_REFUNDED;
                } else {
                    $sk = \common\helpers\OrderPayment::OPYS_SUCCESSFUL;
                }
                break;
            case 'PENDING':
            default:
                $sk = \common\helpers\OrderPayment::OPYS_PENDING;
                break;
        }
        return $sk;
    }

    private function getShippingOptions($currencies, $currency_code) {

        $options = [];
        if ($this->manager->isShippingNeeded()) {
            if ( $this->manager->combineShippings ) {
                $quotes = $this->manager->getAllShippingQuotes();
            } else {
                $quotes = $this->manager->getShippingQuotesByChoice();
            }
            foreach ($quotes as $shipping_quote_item ) {
                if (empty($shipping_quote_item['error'])) {

                    foreach ($shipping_quote_item['methods'] as $shipping_quote_item_method) {
                        if (!isset($shipping_quote_item_method['code'])) {
                            $shipping_quote_item_method['code'] = $shipping_quote_item['id'] . '_' . $shipping_quote_item_method['id'];
                        }
                        if (!isset($shipping_quote_item_method['selected'])) {
                            $shipping_quote_item_method['selected'] = ($this->manager->getSelectedShipping() == $shipping_quote_item_method['code']);
                        }
                        if (strlen($shipping_quote_item_method['code']) > 125) continue;
                        $label = html_entity_decode(strip_tags($shipping_quote_item['module'] . ' '. $shipping_quote_item_method['title']));
                        $mxl = 125;
                        if (strlen($label) > $mxl) {
                            $label = substr($label, 0, $mxl-3) . '...';
                        }
                        if ($this->sendExVat) {
                            if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                                if ($shipping_quote_item['tax']>0) {
                                   $_val = $shipping_quote_item_method['cost_ex']?? \common\helpers\Tax::reduce_tax_always($shipping_quote_item_method['cost'], $shipping_quote_item['tax']);
                                } else {
                                   $_val = $shipping_quote_item_method['cost'];
                                }
                                $cost = $currencies->display_price_clear($_val, 0 , 1);
                            } else {
                                $cost = $currencies->display_price_clear($shipping_quote_item_method['cost'], 0 , 1);
                            }
                        } else {
                            $cost = $currencies->display_price_clear($shipping_quote_item_method['cost'], $shipping_quote_item['tax'], 1);
                        }
                        $row = ['id' => ($shipping_quote_item['id'] != 'collect'?$shipping_quote_item_method['code']:'collect_' . $shipping_quote_item_method['id']),
                                'label' => $label,
                                'type' => ($shipping_quote_item['id'] != 'collect'? "SHIPPING" :"PICKUP"),
                                'selected' => $shipping_quote_item_method['selected']? true : false,
                                'amount' => [
                                  'value' =>  $this->formatRaw(
                                      $cost
                                      , $currency_code, 1
                                          ),
                                  'currency_code' => $currency_code
                                  ]
                               ];
                        $options[] = $row;
                    }
                }
            }
        }
        return $options;
    }

/**
 *  check possible options to get PP account and keys
 * @param int $platform_id
 * @param bool $only
 * @param int $for_seller
 * @return int|array $code: 1 - sandbox 2 - live 3 - both partner's keys 4 - only own keys
 */
    public function getInstallOptions($platform_id, $only = false, $retUrl = false) {
        $ret = [];
        $code = 0;//rudiment
        if (!empty(self::PARTNER_APP_CLIENT_ID) && !empty(self::PARTNER_MERCHANT_ID)) {
           $code += 2;
        }
        if (!empty(self::PARTNER_APP_SANDBOX_CLIENT_ID) && !empty(self::PARTNER_MERCHANT_SANDBOX_ID) ) {
           $code += 1;
        }
        if (!$code) {
            $code = 4;
        }
        if ($only) {
            return $code;
        }

        $ppp = \common\models\PlatformsConfiguration::findOne([
              'configuration_key' => 'MODULE_PAYMENT_PAYPAL_PARTNER_STATUS',
              'platform_id' => $platform_id
        ]);

        $ret['activePPP'] = 0;
        $installed = false;
        if (!empty($ppp)) {
            $ret['activePPP'] = (int)($ppp->configuration_value == 'True');
            $installed = (new \common\classes\Migration())->isTableExists('paypal_seller_info');
        }

        $for_seller = true; // always new sellers;
        $wh_urlParams = ['callback/webhooks.payment.' . $this->code, 'platform_id' => $platform_id];
        $boardingOptionsUrl = \Yii::$app->urlManager->createAbsoluteUrl($wh_urlParams + ['action' => 'processOnBoard', 'subaction' => 'links'], null, true);
        $ret['titlePPP'] = defined('ADD_PAYPAL')?ADD_PAYPAL:'PayPal Quick Setup';
        $ret['installPPP'] = $code;// in any mode
        $ret['golivePPP'] = false; // sandbox exists
        $ret['boardingOptionsUrl'] = $boardingOptionsUrl;

        if ($installed) {
            $urlParams = [
              'platform_id' => $platform_id, 'set' => 'payment', 'module' => $this->code,
            ];
            if (\Yii::$app->id == 'app-backend') {
                $urlParams +=
                    ($this::BOARDING_MODE == 3?['modules/edit', 'action' => 'checkOnBoarded'] : ['modules/edit']) ;
            } else {
                $urlParams +=
                    ($this::BOARDING_MODE == 3?['admin/modules/edit', 'action' => 'checkOnBoarded'] : ['admin/modules/edit']);
            }
            if (\Yii::$app->controller->action->id == 'edit' && \Yii::$app->request->get('psi_id', false) > 0) {
                $ret['cancelUrl'] =  \Yii::$app->urlManager->createAbsoluteUrl($urlParams);
            }

            
            if ($this->liveConfigurationExists($platform_id)) {
                $ret['installPPP'] = 0;

            } else {

                if ($this->ownSandboxConfigExists($platform_id) 
                    && !(\Yii::$app->controller->action->id == 'edit' && $this->getMode() == 'Sandbox' && \Yii::$app->request->get('psi_id', false) > 0)
                    ) { //
                    $ret['titlePPP'] = defined('PAYPAL_PARTNER_GO_LIVE')?PAYPAL_PARTNER_GO_LIVE:'PayPal Go Live';
                    $ret['golivePPP'] = true;// allow own live account only
                } else {
                    $ret['titlePPP'] = defined('ADD_PAYPAL')?ADD_PAYPAL:'PayPal Quick Setup';
                }

                if ($this->boardViaLink()) {

                    $logoUrl = \Yii::$app->urlManager->createAbsoluteUrl(['/'], 'https', true);
                    if (strpos($logoUrl, '127.0.0.1') || strpos($logoUrl, 'localhost')) {
                        $logoUrl = 'https://paypal.oscommerce.host';
                    }
                    $logoUrl = rtrim($logoUrl, '/') . '/admin/themes/basic/img/logo_color.png';
                    $seller = $this->getSeller($platform_id, 'Live', $for_seller);
                    $seller->save();
                    $seller->refresh();
                    $sSeller = $this->getSeller($platform_id, 'Sandbox', $for_seller);
                    $sSeller->save();
                    $sSeller->refresh();

                    if (!empty($retUrl) && strpos($retUrl, '/edit') === false) {
                        $retUrl .= '/edit';
                    }

                    $tmp = \common\helpers\Country::get_country_info_by_id($seller->entry_country_id??STORE_COUNTRY);
                    $fetchKeysUrl = \Yii::$app->urlManager->createAbsoluteUrl($wh_urlParams + ['action' => 'sellerDetails'], null, true);
                    if ($seller) {
                        if (empty($retUrl)) {
                            $returnUrl = \Yii::$app->urlManager->createAbsoluteUrl($urlParams+['psi_id' => $seller->psi_id]);
                        } else {
                            $returnUrl = $retUrl . '?set=payment&module=paypal_partner&platform_id=' . $platform_id . '&psi_id=' . $seller->psi_id;
                        }
                        $ret['link_params'] = [
                                'partnerClientId' => self::PARTNER_APP_CLIENT_ID,
                                'partnerId' => self::PARTNER_MERCHANT_ID,
                                'sellerNonce' => $seller->tracking_id,
                                'country' => $tmp['countries_iso_code_2']??'',
                                'locale' => str_replace('_', '-', \Yii::$app->settings->get('locale')),
                                'return_url' => rawurlencode($returnUrl),
                                'partnerLogoUrl' => rawurlencode($logoUrl)
                              ];
                        $ret['psi_id'] = $seller->psi_id;
                    }
                    if ($sSeller) {
                        if (empty($retUrl)) {
                            $returnUrl = \Yii::$app->urlManager->createAbsoluteUrl($urlParams+['psi_id' => $sSeller->psi_id]);
                        } else {
                            $returnUrl = $retUrl . '?set=payment&module=paypal_partner&platform_id=' . $platform_id . '&psi_id=' . $sSeller->psi_id;
                        }
                        $ret['link_params_sandbox'] = [
                                'partnerClientId' => self::PARTNER_APP_SANDBOX_CLIENT_ID,
                                'partnerId' => self::PARTNER_MERCHANT_SANDBOX_ID,
                                'sellerNonce' => $sSeller->tracking_id,
                                'country' => $tmp['countries_iso_code_2']??'',
                                'locale' => str_replace('_', '-', \Yii::$app->settings->get('locale')),
                                'return_url' => rawurlencode($returnUrl),
                                'partnerLogoUrl' => rawurlencode($logoUrl)
                              ];
                        $ret['spsi_id'] = $sSeller->psi_id;
                    }
                    $ret['fetchKeysUrl'] = $fetchKeysUrl;
                }

            }
        }
        return $ret;
    }

/**
 *
 * @return string|false
 */
    public function saveOrderBefore($force = false) {
        if (is_string($force) && in_array($force, ['Order', 'TmpOrder']))   {
            return $force;
        } elseif ($force) {
            $orderClass = 'Order';
        } else {
            $orderClass = false;
            if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_BEFORE_PAYMENT') && MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_BEFORE_PAYMENT != 'False') {
                if (MODULE_PAYMENT_PAYPAL_PARTNER_ORDER_BEFORE_PAYMENT == 'True') {
                    $orderClass = 'Order';
                } else {
                    $orderClass = 'TmpOrder';
                }
            }
        }
        return $orderClass;
    }

    public function checkSaveTmpOrder() {
        $ret = false;
        if (!$this->saveOrderBefore()) {
            $ret = $this->saveOrderBySettings('TmpOrder');
        }
        return $ret;
    }

    public function saveOrderBySettings($force = false) {
        $ret = false;
        $orderClass = $this->saveOrderBefore($force);
        if ($orderClass) {
            if ($orderClass != 'TmpOrder') {
                $order = $this->manager->getOrderInstance();
                $order->info['order_status'] = $this->getDefaultOrderStatusId();

            }
            $ret = $this->saveOrder($orderClass);
            if (!empty($ret)) {
                if ($orderClass == 'TmpOrder') {
                    $ret = 'tmp' . $ret;
                    $key = 'ppp_tmp_order';
                } else {
                    $key = 'ppp_order_before';
                }
                $this->manager->set($key, $ret);
            }
        }
        return $ret;
    }

    public function boardViaLink() {
        if ($this->getMode() == 'Live') {
            $ret = self::PARTNER_APP_CLIENT_SECRET;
        } else {
            $ret = self::PARTNER_APP_SANDBOX_CLIENT_SECRET;
        }
        return empty($ret);
    }
    
    function get_extra_params($platform_id) {
        $response = [];
        foreach ((new \yii\db\Query())
                ->from('paypal_seller_info')
                ->where('platform_id = ' . (int)$platform_id)
                ->all() as $info ) {
            //not unique per platform - saved both live and sandbox values
            unset($info['platform_id']);
            unset($info['psi_id']);
            unset($info['tracking_id']);
            $response['seller_info'][] = base64_encode(json_encode($info));
        }
        return $response;
    }

    function set_extra_params($platform_id, $data) {
        $ship_options_ids = $ship_zone_ids = $zone_table_ids = [];
        \Yii::$app->db->createCommand('DELETE FROM paypal_seller_info WHERE platform_id='. $platform_id)->execute();

        if (isset($data['seller_info']) && is_array($data['seller_info'])) {
            foreach ($data['seller_info'] as $d) {
                $attr = json_decode(base64_decode($d), true);
                if (is_array($attr)) {
                    $attr['platform_id'] = (int)$platform_id;
                    $m = new lib\PaypalPartner\models\SellerInfo($attr);
                    $m->save(false);
                }
            }
        }

    }

    public function delete_tracking($data) {
        try {
            $tn = \common\models\TrackingNumbers::findOne($data['tracking_numbers_id']);
            $pm = \common\models\OrdersPayment::findOne($data['orders_payment_id']);
            if ($tn && $pm) {
                $res = $this->cancelTracking($data['external_id'], [
                  'transaction_id' => $pm->orders_payment_transaction_id,
                  'tracking_number' => $tn->tracking_number,
                ]);
            }
        } catch (\Exception $e) {
            \Yii::warning(" #### " .print_r($e->getMessage() . $e->getTraceAsString(), true), 'TLDEBUG');
        }

    }

    public function add_tracking($data) {

        $res = $this->addTracking([$data]);

        $ret = TEXT_ERROR_TRACKING_NOT_ADDED;
        if (is_array($res) && !empty($res['error'])) {
            $ret = $res['message']??TEXT_ERROR_TRACKING_NOT_ADDED;
        } elseif (!empty($res)) {

            
            $log = \common\models\TrackingNumbersExport::findOne([
              'classname' => $this->code, 
              'tracking_numbers_id' => $data['tracking_numbers_id'],
              'orders_payment_id' => $data['orders_payment_id']
              ]);
            if (!$log) {
                $log = new \common\models\TrackingNumbersExport([
                    'classname' => $this->code,
                    'tracking_numbers_id' => $data['tracking_numbers_id'],
                    'orders_payment_id' => $data['orders_payment_id'],
                    'orders_id' => $data['orders_id']
                ]);
            }
            if ($log) {
                $log->loadDefaultValues();
                if (!empty($res->result->tracker_identifiers[0])) {
                    $tn = $res->result->tracker_identifiers[0]->tracking_number??'NOTRACKER';
                    $log->external_id = $data['transaction_id'] . '-' . $tn;
                    if (!empty($res->result->errors[0])) {
                        $log->message = $res->result->errors[0]->message . ' ' . $res->result->errors[0]->details[0]['issue']??'';
                        $log->status = 0;
                    } else {
                        $log->status = 1;
                    }
                }

                try {
                    \Yii::warning(" save#### " .print_r($log, true), 'TLDEBUG');
                    $log->save(false);
                } catch (\Exception $e) {
                    \Yii::warning(" #### " .print_r($e->getMessage() . $e->getTraceAsString(), true), 'TLDEBUG');
                }
            }
        }
    }

    private function convertKey($k) {
        $ret = $k;
        if ($k == 'three_d_secure')  {
            $ret = '3DS';

        } else {
            $ret = ucwords(str_replace('_', ' ', $ret));
        }
        return $ret;
    }

    private function convertObject($val, $pad='&nbsp;&nbsp;') {
        $ret = [];
        if (!empty($val) ) {
            foreach ($val as $k => $v) {
                if (is_scalar($v)) {
                    $ret[] =  $pad . $this->convertKey($k) . ': ' . \common\helpers\Output::output_string_protected($v);
                } else {
                    $ret[] =  $pad . $this->convertKey($k) . ': ';
                    $ret = array_merge($ret, $this->convertObject($v, $pad . '&nbsp;&nbsp;'));
                }
            }
        }
        return $ret;
    }

    protected function extractComments($ppOrder, $transaction) {
        $currencies = \Yii::$container->get('currencies');
        $transactionID = $transaction->result->id;
        $ppOrderId = '';
        if (!empty($ppOrder->result->id)) {
            $ppOrderId = $ppOrder->result->id;
        }
        $ccDetails = [];
        if (!empty($ppOrder->result->payment_source->card)) {
            foreach($ppOrder->result->payment_source->card as $k => $v)  {
                if (is_scalar($v)) {
                    $ccDetails[] =  $this->convertKey($k) . ': ' . \common\helpers\Output::output_string_protected($v);
                } else {
                    $ccDetails[] =  $this->convertKey($k) . ':';
                    $ccDetails = array_merge($ccDetails, $this->convertObject($v));
                }
            }
        }
        if (!empty($transaction->result->processor_response)) {
            foreach($transaction->result->processor_response as $k => $v)  {
                if (is_scalar($v)) {
                    $ccDetails[] =  $this->convertKey($k) . ': ' . \common\helpers\Output::output_string_protected($v);
                } else {
                    $ccDetails[] =  $this->convertKey($k) . ':';
                    $ccDetails = array_merge($ccDetails, $this->convertObject($v));
                }
            }
        }

        $ret = [
              'Internal Order ID: ' . \common\helpers\Output::output_string_protected($ppOrderId),
              'Transaction ID: ' . \common\helpers\Output::output_string_protected($transactionID),
              'Transactin Amount: ' . \common\helpers\Output::output_string_protected($transaction->result->amount->value . ' ' . $transaction->result->amount->currency_code),
              'Payment Status: ' . \common\helpers\Output::output_string_protected($transaction->result->status),
              'Seller Protection: ' . \common\helpers\Output::output_string_protected($transaction->result->seller_protection->status??'') .
              (is_array($transaction->result->seller_protection->dispute_categories)? ' - ' . implode(', ', $transaction->result->seller_protection->dispute_categories) :''),
            ];
        return array_merge($ret, $ccDetails);
    }

    public function isPartlyPaid() {
        $ret = parent::isPartlyPaid() || \Yii::$app->request->get('partlypaid');
        if ($ret && $this->manager->has('pay_order_id') && is_numeric($this->manager->get('pay_order_id'))) {
            if ($this->manager->isInstance()) {
                $order = $this->manager->getOrderInstance();
                $order->info['orders_id'] = $order->order_id = $this->manager->get('pay_order_id');

            } else {
                $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $this->manager->get('pay_order_id'));
            }
        }
        return $ret;
    }
/*
    public function hasGuestCheckout() {
        return true;
    }*/

    // 2d0  add 2 methods for own API keys:
    // check webhooks
    // assign webhooks

}
