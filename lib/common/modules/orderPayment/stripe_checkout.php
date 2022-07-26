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
///2do paymentintent option (preauth)

namespace common\modules\orderPayment;

require_once('lib/stripe.php');

use common\classes\modules\ModulePayment;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;
use lib\Stripe\Stripe;
use lib\Stripe\Error\InvalidRequest;
use common\helpers\Output;
use common\helpers\Zones;
use common\classes\modules\TransactionalInterface;
use common\classes\modules\PaymentTokensInterface;
use common\helpers\OrderPayment as OrderPaymentHelper;
use common\helpers\Html;

class stripe_checkout extends ModulePayment implements TransactionalInterface {
//2do PaymentTokensInterface, \common\classes\modules\TransactionSearchInterface
    var $code, $title, $description, $enabled;
    var $paid_status;
    private $debug = true;

    protected $encrypted_keys = ['MODULE_PAYMENT_STRIPE_CHECKOUT_SECRET_KEY'];

    protected $defaultTranslationArray = [
        'MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_TITLE' => 'Stripe Checkout',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_PUBLIC_TITLE' => 'Stripe Checkout',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_DESCRIPTION' => '<a href="https://www.stripe.com" target="_blank" style="text-decoration: underline; font-weight: bold;">Visit Stripe Website</a>',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_ERROR_ADMIN_CURL' => 'This module requires cURL to be enabled in PHP and will not load until it has been enabled on this webserver.',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_ERROR_ADMIN_CONFIGURATION' => 'This module will not load until the Publishable Key and Secret Key parameters have been configured. Please edit and configure the settings of this module.',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_ERROR_TITLE' => 'There has been an error processing your credit card',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_ERROR_GENERAL' => 'Please try again and if problems persist, please try another payment method.',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_ERROR_CARDSTORED' => 'The stored card could not be found. Please try again and if problems persist, please try another payment method.',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_DIALOG_CONNECTION_LINK_TITLE' => 'Test API Server Connection',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_DIALOG_CONNECTION_TITLE' => 'API Server Connection Test',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_DIALOG_CONNECTION_GENERAL_TEXT' => 'Testing connection to server..',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_DIALOG_CONNECTION_BUTTON_CLOSE' => 'Close',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_DIALOG_CONNECTION_TIME' => 'Connection Time:',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_DIALOG_CONNECTION_SUCCESS' => 'Success!',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_DIALOG_CONNECTION_FAILED' => 'Failed! Please review the Verify SSL Certificate settings and try again.',
        'MODULE_PAYMENT_STRIPE_CHECKOUT_DIALOG_CONNECTION_ERROR' => 'An error occurred. Please refresh the page, review your settings, and try again.'
    ];

    public function __construct() {
        parent::__construct();

        $this->signature = 'stripe|stripe|7.71|3.3';
        $this->api_version = '2021-02-05';

        $this->code = 'stripe_checkout';
        $this->title = MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_TITLE;
        $this->public_title = MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_PUBLIC_TITLE;
        $this->description = MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_STRIPE_CHECKOUT_SORT_ORDER') ? MODULE_PAYMENT_STRIPE_CHECKOUT_SORT_ORDER : 0;
        $this->enabled = defined('MODULE_PAYMENT_STRIPE_CHECKOUT_STATUS') && (MODULE_PAYMENT_STRIPE_CHECKOUT_STATUS == 'True') ? true : false;
        $this->order_status = defined('MODULE_PAYMENT_STRIPE_CHECKOUT_ORDER_STATUS_ID') && ((int) MODULE_PAYMENT_STRIPE_CHECKOUT_ORDER_STATUS_ID > 0) ? (int) MODULE_PAYMENT_STRIPE_CHECKOUT_ORDER_STATUS_ID : 0;
        $this->paid_status = defined('MODULE_PAYMENT_STRIPE_CHECKOUT_COMPLETED_ORDER_STATUS_ID') && ((int) MODULE_PAYMENT_STRIPE_CHECKOUT_COMPLETED_ORDER_STATUS_ID > 0) ? (int) MODULE_PAYMENT_STRIPE_CHECKOUT_COMPLETED_ORDER_STATUS_ID : 0;

        if (defined('MODULE_PAYMENT_STRIPE_CHECKOUT_STATUS')) {
            if (strpos(MODULE_PAYMENT_STRIPE_CHECKOUT_PUBLISHABLE_KEY, 'test') != false) {
                $this->title .= ' [Test]';
                $this->public_title .= ' (' . $this->title . ')';
            }
        }

        if ($this->enabled === true) {
            if (!tep_not_null(MODULE_PAYMENT_STRIPE_CHECKOUT_PUBLISHABLE_KEY) || !tep_not_null($this->getAPISecret())) {
                $this->description = '<div class="secWarning">' . MODULE_PAYMENT_STRIPE_CHECKOUT_ERROR_ADMIN_CONFIGURATION . '</div>' . $this->description;

                $this->enabled = false;
            }
        }

        if ($this->enabled === true) {
            $this->update_status();
        }

        $this->dont_update_stock = ((defined('MODULE_PAYMENT_STRIPE_CHECKOUT_UPDATE_STOCK_BEFORE_PAYMENT') &&  MODULE_PAYMENT_STRIPE_CHECKOUT_UPDATE_STOCK_BEFORE_PAYMENT != 'True') ? true : false);
        $this->dont_send_email = true;
    }

    function update_status() {
        if (($this->enabled == true) && ((int) MODULE_PAYMENT_STRIPE_CHECKOUT_ZONE > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_STRIPE_CHECKOUT_ZONE . "' and zone_country_id = '" . $this->billing['country']['id'] . "' order by zone_id");
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

    public function updateTitle($platformId = 0)
    {
        $mode = $this->get_config_key((int)$platformId, 'MODULE_PAYMENT_STRIPE_CHECKOUT_PUBLISHABLE_KEY');
        if ($mode !== false) {
            $mode = strtolower($mode);
            $title = (defined('MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_TITLE') ? constant('MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_TITLE') : '');
            if ($title != '') {
                $this->title = $title;
                if (strpos($mode, 'test') !== false) {
                    $this->title .= ' [Test]';
                }
            }
            $titlePublic = (defined('MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_PUBLIC_TITLE') ? constant('MODULE_PAYMENT_STRIPE_CHECKOUT_TEXT_PUBLIC_TITLE') : '');
            if ($titlePublic != '') {
                $this->public_title = $titlePublic;
                if (strpos($mode, 'test') !== false) {
                    $this->public_title .= " [{$this->code}; Test]";
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
        $this->manager->remove('stripe_checkout_session');
        $this->checkWebhook();
        if ($this->isWithoutConfirmation()) {
            \Yii::$app->getView()->registerJs($this->getSubmitCheckoutJavascript());
        }
        return array('id' => $this->code,
            'module' => $this->public_title,
        );
    }

    function pre_confirmation_check() {

    }

    function confirmation() {
        if (!$this->isWithoutConfirmation()) {
            \Yii::$app->getView()->registerJs($this->getSubmitCheckoutJavascript());
        }
        $confirmation = array(
            'fields' => array(array('title' => $this->title,)
        ));
        return $confirmation;
    }

    function process_button() {
        //return \yii\helpers\Html::hiddenInput('skip', false);
    }

    public function popUpMode() {
        return true;
    }

    function before_process() {
        $this->manager->remove('stripe_checkout_session');
        $order = $this->manager->getOrderInstance();
        if ((int) MODULE_PAYMENT_STRIPE_CHECKOUT_ORDER_STATUS_ID > 0) {
            $order->info['order_status'] = (int) MODULE_PAYMENT_STRIPE_CHECKOUT_ORDER_STATUS_ID; // Before Stripe Checkout
        } else {
            $order->info['order_status'] = (int) DEFAULT_ORDERS_STATUS_ID;
        }
        $order->isPaidUpdated = true;
        if ($this->isPartlyPaid()) {
            if ($this->manager->get('payer_payment') == 'stripe_checkout') {
                $this->manager->remove('payer_payment');
            }
            $this->after_process();
        }
    }

    function after_process() {
        $order = $this->manager->getOrderInstance();

        $stripe_checkout_session = array();
        if ($this->isPartlyPaid()) {
            $invoice = $this->manager->getOrderSplitter()->getInvoiceInstance();
            if ($invoice) {
                $order_amount = $invoice->info['total_inc_tax'];
                $orders_id = $invoice->parent_id;
            } else {
                $order_amount = $order->info['total_inc_tax'];
                $orders_id = $order->order_id;
            }
            if ($orders_id > 0) {
                $stripe_checkout_session['pay_order_id'] = $orders_id;
            }
        } else {
            $order_amount = $order->info['total_inc_tax'];
            $orders_id = $order->order_id;
        }

        try {
            \Stripe\Stripe::setApiKey($this->getAPISecret());
            //theme name and logo filename could have whitespaces. Stripe library doesn't encode them but through exception.
            $site_logo = \frontend\design\Info::themeSetting('logo', 'hide');
            if (!empty($site_logo)) {
              $parts = explode('/', $site_logo);
              if (is_array($parts)) {
                foreach ($parts as $i => $part) {
                  $parts[$i] = rawurlencode($part);
                }
                $site_logo = implode('/', $parts);
              }
            }

            $checkout_session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($order->info['currency']),
                        'unit_amount' => $this->format_raw($order_amount),
                        'product_data' => [
                            'name' => (defined('STORE_NAME') ? STORE_NAME . ' - ' : '') . 'Order #' . $orders_id,
                            'images' => [tep_href_link($site_logo)],
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'order_id' => $orders_id,
                    'store' => (defined('STORE_NAME') ? STORE_NAME : '')
                ],
                'customer_email' => $order->customer['email_address'],
                'success_url' => tep_href_link('callback/webhooks.payment.' . $this->code, 'action=success&orders_id=' . $orders_id, 'SSL'),
                'cancel_url' => tep_href_link('callback/webhooks.payment.' . $this->code, 'action=cancel&orders_id=' . $orders_id, 'SSL'),
            ]);

            \common\helpers\OrderPayment::createDebitFromOrder($order, $order->info['total_inc_tax'], false, [
                'id' => $checkout_session->id,
                'payment_class' => $this->code,
                'payment_method' => $this->title,
            ]);

            $stripe_checkout_session['session_id'] = $checkout_session->id;
            $this->manager->set('stripe_checkout_session', $stripe_checkout_session);

            echo json_encode(['id' => $checkout_session->id]);
        } catch (\Exception $e) {
            $this->sendDebugEmail($e);
            \Yii::warning(" #### " .print_r($e, true), 'TLDEBUG');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    public function isPartlyPaid() {
        $stripe_checkout_session = $this->manager->get('stripe_checkout_session');
        return (parent::isPartlyPaid() || $stripe_checkout_session['pay_order_id'] > 0);
    }

    public function getCheckoutUrl(array $params, int $checkoutPage = 0) {
        $stripe_checkout_session = $this->manager->get('stripe_checkout_session');
        if (!isset($params['order_id']) && $stripe_checkout_session['pay_order_id'] > 0) {
            $params['order_id'] = $stripe_checkout_session['pay_order_id'];
        }
        return parent::getCheckoutUrl($params, $checkoutPage);
    }

    function get_error() {
        global $stripe_error;

        $message = MODULE_PAYMENT_STRIPE_CHECKOUT_ERROR_GENERAL;

        if (tep_session_is_registered('stripe_error')) {
            $message = $stripe_error . ' ' . $message;

            tep_session_unregister('stripe_error');
        }

        if (isset($_GET['error']) && !empty($_GET['error'])) {
            switch ($_GET['error']) {
                case 'cardstored':
                    $message = MODULE_PAYMENT_STRIPE_CHECKOUT_ERROR_CARDSTORED;
                    break;
            }
        }

        $error = array('title' => MODULE_PAYMENT_STRIPE_CHECKOUT_ERROR_TITLE,
            'error' => $message);

        return $error;
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_PAYMENT_STRIPE_CHECKOUT_STATUS', 'True', 'False');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_PAYMENT_STRIPE_CHECKOUT_SORT_ORDER');
    }

    public function configure_keys() {
        $status_id_b = defined('MODULE_PAYMENT_STRIPE_CHECKOUT_ORDER_STATUS_ID') ? MODULE_PAYMENT_STRIPE_CHECKOUT_ORDER_STATUS_ID : $this->getDefaultOrderStatusId();
        $status_id_s = defined('MODULE_PAYMENT_STRIPE_CHECKOUT_COMPLETED_ORDER_STATUS_ID') ? MODULE_PAYMENT_STRIPE_CHECKOUT_COMPLETED_ORDER_STATUS_ID : $this->getDefaultOrderStatusId();
        $status_id_c = defined('MODULE_PAYMENT_STRIPE_CHECKOUT_CANCELLED_ORDER_STATUS_ID') ? MODULE_PAYMENT_STRIPE_CHECKOUT_CANCELLED_ORDER_STATUS_ID : $this->getDefaultOrderStatusId();
        $params = array('MODULE_PAYMENT_STRIPE_CHECKOUT_STATUS' => array('title' => 'Enable Stripe Module',
                'description' => 'Do you want to accept Stripe payments?',
                'value' => 'True',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), '),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_PUBLISHABLE_KEY' => array('title' => 'Publishable API Key',
                'description' => 'The Stripe account publishable API key to use.',
                'value' => ''),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_SECRET_KEY' => array('title' => 'Secret API Key',
                'description' => 'The Stripe account secret API key to use with the publishable key.',
                'set_function' => "setConf(",
                'use_function' => '\\common\\modules\\orderPayment\\sage_pay_server::useConf',
                'value' => ''),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_WEBHOOK_SECRET' => array('title' => 'Webhook Secret',
                'description' => 'The Stripe account webhook signing secret.',
                'value' => ''),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_ORDER_STATUS_ID' => array('title' => 'Set Order Status Before Payment',
                'description' => 'Set the status of orders before redirect to Gateway',
                'value' => $status_id_b,
                'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                'set_function' => 'tep_cfg_pull_down_order_statuses('),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_COMPLETED_ORDER_STATUS_ID' => array('title' => 'Set Order Status Completed Payment',
                'description' => 'Set the status of orders completed successfully',
                'value' => $status_id_s,
                'set_function' => 'tep_cfg_pull_down_order_statuses(',
                'use_function' => '\\common\\helpers\\Order::get_order_status_name'),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_CANCELLED_ORDER_STATUS_ID' => array('title' => 'Set Order Status Cancelled Payment',
                'description' => 'Set the status of orders cancelled',
                'value' => $status_id_c,
                'set_function' => 'tep_cfg_pull_down_order_statuses(',
                'use_function' => '\\common\\helpers\\Order::get_order_status_name'),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_UPDATE_STOCK_BEFORE_PAYMENT' => array('title' => 'Update Stock Before Payment',
                'value' => 'False',
                'description' => 'Should Products Stock be updated even when the payment is not yet COMPLETED?',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
            ),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_ZONE' => array('title' => 'Payment Zone',
                'description' => 'If a zone is selected, only enable this payment method for that zone.',
                'value' => '0',
                'use_function' => '\\common\\helpers\\Zones::get_zone_class_title',
                'set_function' => 'tep_cfg_pull_down_zone_classes('),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_DEBUG_EMAIL' => array('title' => 'Debug E-Mail Address',
                'description' => 'All parameters of an invalid transaction will be sent to this email address.'),
            'MODULE_PAYMENT_STRIPE_CHECKOUT_SORT_ORDER' => array('title' => 'Sort order of display.',
                'description' => 'Sort order of display. Lowest is displayed first.',
                'value' => '0'));

        return $params;
    }

    function format_raw($number, $currency_code = '', $currency_value = '') {
        $currencies = \Yii::$container->get('currencies');

        if (empty($currency_code) || !$currencies->is_set($currency_code)) {
            $currency_code = \Yii::$app->settings->get('currency');
        }

        if (empty($currency_value) || !is_numeric($currency_value)) {
            $currency_value = $currencies->currencies[$currency_code]['value'];
        }

        return number_format(self::round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '', '');
    }

    function getSubmitCheckoutJavascript() {
        $order = $this->manager->getOrderInstance();
        $stripe_publishable_key = MODULE_PAYMENT_STRIPE_CHECKOUT_PUBLISHABLE_KEY;

        \Yii::$app->getView()->registerJsFile("https://js.stripe.com/v3/");
        $this->registerCallback("stripeCheckoutCallback");
        $checkoutURL = $this->getCheckoutUrl([], self::PROCESS_PAGE);

        $js = <<<EOD
    /**/
function init{$this->code}(){
    if (!paymentCollection.hasOwnProperty('stripeCheckout')){
        var stripe = Stripe('{$stripe_publishable_key}');
        paymentCollection.stripeCheckout = {
            stripe: stripe,
        };
    }
}
function stripeCheckoutCallback(){
    init{$this->code}();
    fetch("{$checkoutURL}", {
        method: "POST",
    })
    .then(function (response) {
        return response.json();
    })
    .then(function (session) {
        if (session.id) {
            return paymentCollection.stripeCheckout.stripe.redirectToCheckout({ sessionId: session.id });
        } else if (session.error) {
            alert(session.error);
        }
    })
    .then(function (result) {
        // If redirectToCheckout fails due to a browser or network
        // error, you should display the localized error message to your
        // customer using error.message.
        if (result.error) {
            alert(result.error.message);
        }
    })
    .catch(function (error) {
        console.error("Error:", error);
    });
}
    /**/
EOD;
        return $js;
    }

    function checkWebhook() {
        if (tep_not_null(MODULE_PAYMENT_STRIPE_CHECKOUT_PUBLISHABLE_KEY) && tep_not_null($this->getAPISecret())) {
            try {
                $configurationKey = \common\models\PlatformsConfiguration::findOne(['configuration_key' => 'MODULE_PAYMENT_STRIPE_CHECKOUT_WEBHOOK_SECRET', 'platform_id' => intval($this->getPlatformId())]);
                if (!tep_not_null($configurationKey->configuration_value)) {
                    $stripe = new \Stripe\StripeClient($this->getAPISecret());
                    $webhookEndpoints = $stripe->webhookEndpoints->all(['limit' => 10]);
                    if (is_array($webhookEndpoints->data)) {
                        foreach ($webhookEndpoints->data as $webhook) {
                            if ($webhook['url'] == tep_href_link('callback/webhooks.payment.' . $this->code, '', 'SSL') &&
                                    $webhook['enabled_events'][0] == 'checkout.session.completed') {
                                $stripe->webhookEndpoints->delete($webhook['id'], []);
                            }
                        }
                    }
                    $result = $stripe->webhookEndpoints->create([
                        'url' => tep_href_link('callback/webhooks.payment.' . $this->code, '', 'SSL'),
                        'enabled_events' => [
                            'checkout.session.completed',
                        ],
                    ]);
                    $configurationKey->configuration_value = $result->secret;
                    $configurationKey->save();
                }
            } catch (\Exception $e) {
                \Yii::warning($e->getMessage(), 'TLMODULES');
            }
        }
    }

    function sendDebugEmail($response = array()) {
        global $_POST, $_GET;

        if (tep_not_null(MODULE_PAYMENT_STRIPE_CHECKOUT_DEBUG_EMAIL)) {
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
                \common\helpers\Mail::send('', MODULE_PAYMENT_STRIPE_CHECKOUT_DEBUG_EMAIL, 'Stripe Debug E-Mail', trim($email_body), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }
        }
    }

    function isOnline() {
        return true;
    }

    public function canVoid($transaction_id) {
        return false;
    }

    public function void($transaction_id) {
        return false;
    }

    public function canCapture($transaction_id) {
        return false;
    }

    public function capture($transaction_id, $amount = 0) {
        return false;
    }

    public function canReauthorize($transaction_id) {
        return false;
    }

    public function reauthorize($transaction_id, $amount = 0) {
        return false;
    }

    protected function getAPISecret() {
        return $this->decryptConst('MODULE_PAYMENT_STRIPE_CHECKOUT_SECRET_KEY');
    }

    protected function getEncryptionKey() {
        $key = parent::getEncryptionKey();
        if (!$key) {
            $key = 'qhs8R4.!^kdvJ,Er].HUC=#G}v.9qEeE';
        }
        return $key;
    }

    /**
     * get transaction Status code according transaction details and module settings
     * @param array $transactionDetails
     * @return int one of OrderPaymentHelper constants
     */
    public function getStatusCode($transactionDetails) {
        $statusCode = OrderPaymentHelper::OPYS_PENDING;
        if (is_array($transactionDetails)) {
            if ($transactionDetails['object'] == 'checkout.session' && $transactionDetails['status'] == 'complete' && $transactionDetails['mode'] == 'payment') {
                $statusCode = OrderPaymentHelper::OPYS_SUCCESSFUL;
            }
        } else
        if ($transactionDetails->object == 'charge' && $transactionDetails->status == 'succeeded') {
            if ($transactionDetails->captured) {
                $statusCode = OrderPaymentHelper::OPYS_SUCCESSFUL;
            } else {
                $statusCode = OrderPaymentHelper::OPYS_PROCESSING;
            }
        }

        return $statusCode;
    }

    public function canRefund($transaction_id) {

        $ret = false;
        $orderPayment = $this->searchRecord($transaction_id);
        if (!$orderPayment) {
            $orderPayment = $this->getTransactionDetails($transaction_id);
        }

        if ($orderPayment && OrderPaymentHelper::getAmountAvailable($orderPayment)) {
            $ret = true;
        }
        return $ret;
    }

    public function refund($transaction_id, $amount = 0) {
        $ret = false;

        $transaction = $this->getTransactionDetails($transaction_id);

        if (!empty($transaction['orders_payment_transaction_full']) || !empty($transaction['object'])) {
            $params = [];
            if ($amount > 0) {
                $params['amount'] = $this->format_raw($amount);
            }
            if (empty($transaction['object'])) {
                $transaction = \json_decode($transaction['orders_payment_transaction_full'], true);
            }
            switch ($transaction['object']) {
                case 'checkout.session':
                    $params['payment_intent'] = $transaction['payment_intent'];
                    break;
                case 'payment_intent':
                    $params['payment_intent'] = $transaction['orders_payment_transaction_id'];
                    break;
                default:
                    $params['charge'] = $transaction['orders_payment_transaction_id'];
                    break;
            }

            try {
                $stripe = new \Stripe\StripeClient($this->getAPISecret());
                $response = $stripe->refunds->create($params);
                $response = \json_decode(\json_encode($response), true);
            } catch (\Exception $e) {
                $ret = $e->getMessage();
                \Yii::warning(" #### " . $e->getMessage() . ' ' . print_r($params, true), 'STRIPE_REFUND_ERROR');
            }

            if ($this->debug) {
                \Yii::warning(print_r($response, 1), 'STRIPE_REFUND');
            }


            if (!empty($response['status']) && $response['status'] == 'succeeded') {
                $tm = $this->manager->getTransactionManager($this);
                $comment = self::getComment($response);

                $res = $tm->updatePaymentTransaction($response['id'], [
                    'fulljson' => json_encode($response),
                    'status_code' => \common\helpers\OrderPayment::OPYS_REFUNDED,
                    'status' => $response['status'],
                    'amount' => (float) $amount,
                    'comments' => $comment,
                    'date' => date('Y-m-d H:i:s', $response['created']),
                    'payment_class' => $this->code,
                    'payment_method' => $this->title,
                    'parent_transaction_id' => $transaction_id,
                    'orders_id' => 0
                ]);
                if ($res) {
                    $ret = true;
                }
                parent::updatePaidTotalsAndNotify();

                $this->getTransactionDetails($transaction_id);
            }
        }
        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function getTransactionDetails($op_transaction_id, \common\services\PaymentTransactionManager $tManager = null) {
        $res = $ret = false;
        if (empty($this->_transactionDetails) || $op_transaction_id != $this->_transactionDetails['id']) {
            $orderPayment = $this->searchRecord($op_transaction_id);

            if (!empty($orderPayment['orders_payment_transaction_id'])) {
                $transaction_id = $orderPayment['orders_payment_transaction_id'];

                if (!empty($orderPayment['orders_payment_transaction_full'])) {
                    $tmp = \json_decode($orderPayment['orders_payment_transaction_full'], true);
                    $type = $tmp['object'];
                } else {
                    switch (substr($transaction_id, 0, 2)) {
                        case 'pi':
                            $type = 'payment_intent';
                            break;
                        case 'cs':
                            $type = 'checkout.session';
                            break;
                        case 'ch':
                            $type = 'charge';
                            break;
                    }

                }

                try {
                    $stripe = new \Stripe\StripeClient($this->getAPISecret());
                    switch ($type) {
                        case 'refund':
                            $ret = $stripe->refunds->retrieve($transaction_id, []);
                            break;
                        case 'payment_intent':
                            $ret = $stripe->setupIntents->retrieve($transaction_id, []);
                            break;
                        case 'checkout.session':
                            $ret = $stripe->checkout->sessions->retrieve($transaction_id, []);
                            break;
                        case 'setup_intent':
                            $ret = $stripe->paymentIntents->retrieve($transaction_id, []);
                            break;
                        case 'balance_transaction':
                            $ret = $stripe->balanceTransactions->retrieve($transaction_id, []);
                            break;
                        default:
                            $ret = $stripe->charges->retrieve($transaction_id, []);
                            break;
                    }

                    if ($this->debug) {
                        \Yii::warning(print_r($ret, true), 'STRIPE_GET_TRANSACTION ret');
                    }

                    if (!empty($ret)) {
                        $res = \json_decode(\json_encode($ret), true);
/*                        if (is_null($tManager)) {
                            $tManager = $this->manager->getTransactionManager($this);
                        }
                        $statusCode = [];
                        if ($type == 'checkout.session' && $ret['status'] == 'complete') {
                            $statusCode = ['status_code' => OrderPaymentHelper::OPYS_SUCCESSFUL];
                        }

                        $tManager->updatePaymentTransaction($ret['id'],
                            $statusCode +
                            [
                            'status' => $ret['status'],
                            'comments' => self::getComment($ret),
                            'last_updated' => date('Y-m-d H:i:s'),
                            'payment_class' => $this->code,
                            'payment_method' => $this->title,
                                //'parent_transaction_id' => $transaction_id,
                        ]);
*/
                    }
                } catch (\Exception $e) {
                    \Yii::error(" #### " . print_r($e->getMessage(), true), 'STRIPE_GETCHARGEDETAILS_EXCEPTION');
                }
                if ($this->debug) {
                    \Yii::warning(print_r($ret, true), 'STRIPE_RESPONSE_DETAILS');
                }

                if (is_array($res)) {
                    $res = array_merge($orderPayment->attributes, $res);
                }
            }
            // doesn't contain new full json - for update only
            $this->_transactionDetails = $res;
        }
        return $this->_transactionDetails;
    }

    public function parseTransactionDetails($transactionDetails) {
        $this->transactionInfo = [];
        if (is_array($transactionDetails)) {
            $this->transactionInfo['status'] = $transactionDetails['status'];
            $this->transactionInfo['status_code'] = $this->getStatusCode($transactionDetails);
            $this->transactionInfo['transaction_id'] = $transactionDetails['id'];
            $this->transactionInfo['amount'] = $this->formatRaw($transactionDetails['amount_total']/100);
            $this->transactionInfo['fulljson'] = json_encode($transactionDetails);
            $this->transactionInfo['comments'] = self::getComment($transactionDetails);
        }

        return $this->transactionInfo;
    }


    public static function getComment($source) {
        $comment = '';
        foreach (array_unique([
            'object', 'amount', 'amount_captured', 'amount_refunded',
            'application', 'application_fee', 'application_fee_amount',
            'captured', 'created', 'currency', 'description', 'disputed',
            'failure_code', 'failure_message', 'livemode', 'paid',
            'payment_method', 'receipt_email', 'receipt_number', 'refunded',
            'status'
            , "amount", "amount_total", "balance_transaction", "charge", "created", "currency",
            "metadata", "payment_intent", "payment_status", "reason", "receipt_number",
            "source_transfer_reversal", "transfer_reversal"
                //, 'source->card'
        ]) as $k) {
            if (!empty($source[$k])) {
                if (is_array($source[$k])) {
                    $comment .= "$k: " . implode(' ' , $source[$k]) . "; \n";
                } else {
                    $comment .= "$k: {$source[$k]}; \n";
                }
            }
        }

        return $comment;
    }

    public function call_webhooks() {
        $action = \Yii::$app->request->get('action', '');
        $orders_id = \Yii::$app->request->get('orders_id', 0);
        if (!empty($action) && $orders_id > 0) {
            if ($action == 'success') {
                $this->manager->clearAfterProcess();
                $this->manager->remove('stripe_checkout_session');
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'orders_id=' . $orders_id, 'SSL'));
            } elseif ($action == 'cancel') {
                $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);
                $stripe_checkout_session = $this->manager->get('stripe_checkout_session');
                if ($stripe_checkout_session['session_id']) {
                    if ($orderPayment = $this->searchRecord($stripe_checkout_session['session_id'])) {
                        $orderPayment->orders_payment_order_id = $order->order_id;
                        $orderPayment->orders_payment_snapshot = json_encode(OrderPaymentHelper::getOrderPaymentSnapshot($order));
                        $orderPayment->orders_payment_transaction_commentary = 'Cancelled by customer';
                        $orderPayment->orders_payment_transaction_date = new \yii\db\Expression('now()');
                        $orderPayment->orders_payment_status = OrderPaymentHelper::OPYS_CANCELLED;
                        $orderPayment->orders_payment_transaction_status = 'cancelled';
                        $orderPayment->save(false);
                    }
                }
                if (!$this->isPartlyPaid()) {
                    if (is_numeric(MODULE_PAYMENT_STRIPE_CHECKOUT_CANCELLED_ORDER_STATUS_ID) && (MODULE_PAYMENT_STRIPE_CHECKOUT_CANCELLED_ORDER_STATUS_ID > 0)) {
                        $order_status = MODULE_PAYMENT_STRIPE_CHECKOUT_CANCELLED_ORDER_STATUS_ID;
                        \common\helpers\Order::setStatus($order->order_id, (int)$order_status, [
                            'comments' => 'Cancelled by customer',
                            'customer_notified' => 0,
                        ]);
                    }
                }
                tep_redirect($this->getCheckoutUrl([], self::PAYMENT_PAGE));
            }
            exit();
        }

        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($this->getAPISecret());

        // You can find your endpoint's secret in your webhook settings
        $endpoint_secret = MODULE_PAYMENT_STRIPE_CHECKOUT_WEBHOOK_SECRET;

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            \Yii::warning(" #### " .print_r($e->getMessage(), true), 'TLDEBUG');
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            \Yii::warning(" #### " .print_r($e->getMessage(), true), 'TLDEBUG');
            http_response_code(400);
            exit();
        } catch (\Exception $e) {
            \Yii::warning(" #### " .print_r($e->getMessage(), true), 'TLDEBUG_' . $this->code);
            http_response_code(400);
            exit();
        }

        // Handle the checkout.session.completed event
        if ($event->type == 'checkout.session.completed') {
            $session = $event->data->object;

            // Fulfill the purchase...
            if (is_object($session) && $session instanceof \Stripe\Checkout\Session) {
                $response = [];
                $currencies = \Yii::$container->get('currencies');
                $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $session->metadata->order_id);

                if ($order->order_id > 0 /*&& $order->info['platform_id'] == \common\classes\platform::currentId()*/) {
                    $stock_updated = false;
                    if (MODULE_PAYMENT_STRIPE_CHECKOUT_UPDATE_STOCK_BEFORE_PAYMENT == 'False' && !\common\helpers\Order::is_stock_updated((int) $order->order_id)) {
                        for ($i = 0, $n = sizeof($order->products); $i < $n; $i ++) {
                            // Stock Update - Joao Correia
                            if (STOCK_LIMITED == 'true') {
                                \common\helpers\Warehouses::update_stock_of_order($order->order_id, (strlen($order->products[$i]['template_uprid']) > 0 ? $order->products[$i]['template_uprid'] : $order->products[$i]['id']), $order->products[$i]['qty'], 0, 0, $order->info['platform_id']);
                                $stock_updated = true;
                            }
                        }
                    }

                    $sql_data_array = [];
                    if ($stock_updated === true) {
                        $sql_data_array['stock_updated'] = 1;
                        tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', 'orders_id=' . $order->order_id);
                    }

                    $status_comment = array('Transaction ID: ' . $session->id);
                    $response['id'] = $session->id;
                    $status_comment[] = 'Status: ' . $session->payment_status;
                    $status_comment[] = 'Transaction Amount: ' . $currencies->format(($session->amount_total / 100), false, $order->info['currency'], $order->info['currency_value']);
                    $response['status'] = $session->payment_status;
                    $response['amount'] = $currencies->format_clear(($session->amount_total / 100), false, $order->info['currency'], $order->info['currency_value']);

                    if (is_numeric(MODULE_PAYMENT_STRIPE_CHECKOUT_COMPLETED_ORDER_STATUS_ID) && (MODULE_PAYMENT_STRIPE_CHECKOUT_COMPLETED_ORDER_STATUS_ID > 0)) {
                        $order_status = MODULE_PAYMENT_STRIPE_CHECKOUT_COMPLETED_ORDER_STATUS_ID;
                    } else {
                        $order_status = DEFAULT_ORDERS_STATUS_ID;
                    }
                    $order->info['order_status'] = $order_status;

                    \common\helpers\Order::setStatus($order->order_id, (int)$order_status, [
                        'comments' => implode("\n", $status_comment),
                        'customer_notified' => 0,
                    ]);

                    $order->update_piad_information(true);
                    $order->save_details();

                    $order->notify_customer($order->getProductsHtmlForEmail(), []);

                    //{{ transactions
                    /** @var \common\services\PaymentTransactionManager $tManager */
                    $tManager = $this->manager->getTransactionManager($this);
                    $invoice_id = $this->manager->getOrderSplitter()->getInvoiceId();
                    if ($session->payment_status == 'paid') {
                        $statusCode = OrderPaymentHelper::OPYS_SUCCESSFUL;
                    } else {
                        $statusCode = OrderPaymentHelper::OPYS_PROCESSING;
                    }

                    $comment = self::getComment(\json_decode(\json_encode($session), true));

                    if ($this->debug) {
                        \Yii::warning('id ' . $response['id'] . ' ' . print_r([
                            'fulljson' => json_encode($session),
                            'status_code' => $statusCode,
                            'status' => $response['status'],
                            'amount' => (float) $response['amount'],
                            'comments' => $comment,
                            //'date' => date('Y-m-d H:i:s', strtotime($response['started'])),
                            'date' => date('Y-m-d H:i:s'),
                            'suborder_id' => $invoice_id,
                            'orders_id' => $order->order_id,
                        ], 1), 'STRIPE_TRANSACTION_DETAILS');
                    }

                    $ret = $tManager->updatePaymentTransaction($response['id'], [
                        'fulljson' => json_encode($session),
                        'status_code' => $statusCode,
                        'status' => $response['status'],
                        'amount' => (float) $response['amount'],
                        'comments' => $comment,
                        //'date' => date('Y-m-d H:i:s', strtotime($response['started'])),
                        'date' => date('Y-m-d H:i:s'),
                        'suborder_id' => $invoice_id,
                        'orders_id' => $order->order_id
                    ]);
                }
            }
        }

        http_response_code(200);
    }

}
