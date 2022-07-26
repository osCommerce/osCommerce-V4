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

$dir = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
require_once($dir . "/mspcheckout/include/MultiSafepay.combined.php");

use common\classes\extended\OrderAbstract;
use Yii;
use common\classes\modules\ModulePayment;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;
use common\classes\order_total;
use \MultiSafepayAPI;

if (!class_exists('multisafepay')) {

    class multisafepay extends ModulePayment {

        var $code;
        var $title;
        var $description;
        var $enabled;
        var $sort_order;
        var $plugin_name;
        var $icon = "msp.gif";
        var $api_url;
        var $order_id;
        var $public_title;
        var $status;
        var $shipping_methods = array();
        var $taxes = array();
        var $msp;

        /*
         * Constructor
         */
        protected $defaultTranslationArray = [
            'MODULE_PAYMENT_MULTISAFEPAY_LOCALE' => 'en',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE' => 'MultiSafepay',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_PUBLIC_TITLE' => 'MultiSafepay (+ iDEAL, Credit Card and Mister Cash)',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_DESCRIPTION' => '<img src="images/icon_popup.gif" border="0">&nbsp;<a href="http://www.multisafepay.com/" style="text-decoration: underline; font-weight: bold;">Visit MultiSafepay Website</a>',
            'MODULE_PAYMENT_MULTISAFEPAY_EMAIL_TEXT_ORDER_STATUS' => 'Order Status:',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_RETURN_TO_SHOP' => 'Return to %s',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR' => 'An error has occurred',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_REDIRECT' => 'Unable to perform redirect',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_STATUS' => 'Unable to retrieve order status',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_UNKNOWN' => 'No details available',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1000' => 'Invalid request received',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1001' => 'Invalid amount',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1002' => 'Invalid currency',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1003' => 'Invalid merchant ID',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1004' => 'Invalid merchant account ID',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1005' => 'Invalid merchant security code',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1006' => 'Invalid transaction ID',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1007' => 'Invalid IP address',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1008' => 'Invalid description',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1009' => 'Invalid transaction type',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1010' => 'Invalid user-definable variable (var1/var2/var3)',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1011' => 'Invalid customer account ID',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1012' => 'Invalid customer security code',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1013' => 'MD5 mismatch',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1014' => 'Back-end: unspecified error',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1015' => 'Back-end: account not found',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1016' => 'Back-end: missing data',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_1017' => 'Back-end: insufficient funds',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_2000' => 'HTTP request failed',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_2001' => 'Invalid HTTP response code',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_2002' => 'Invalid HTTP content type',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_6666' => 'Merchant error',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_9999' => 'No details available',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_UNCLEARED' => 'Transaction is not cleared',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_RESERVED' => 'Transaction is reserved',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_VOID' => 'Transaction is cancelled',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_DECLINED' => 'Transaction is declined',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_REVERSED' => 'Transaction is reversed',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_REFUNDED' => 'Transaction is refunded',
            'MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_EXPIRED' => 'Transaction is expired'
        ];

        function __construct($order_id = -1) {
            parent::__construct();

            $this->code = 'multisafepay';
            $this->title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE);
            $this->description = MODULE_PAYMENT_MULTISAFEPAY_TEXT_DESCRIPTION;
            if (!defined('MODULE_PAYMENT_MULTISAFEPAY_STATUS')) {
                $this->enabled = false;
                return false;
            }
            $this->enabled = MODULE_PAYMENT_MULTISAFEPAY_STATUS == 'True';
            $this->sort_order = MODULE_PAYMENT_MULTISAFEPAY_SORT_ORDER;
            $this->plugin_name = 'Plugin 2.0.2 (' . PROJECT_VERSION . ')';

            $this->update_status();

            // new configuration value
            if (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER == 'Live' || MODULE_PAYMENT_MULTISAFEPAY_API_SERVER == 'Live account') {
                $this->api_url = 'https://api.multisafepay.com/ewx/';
            } else {
                $this->api_url = 'https://testapi.multisafepay.com/ewx/';
            }

            $this->order_id = $order_id;
            $this->public_title = $this->getTitle(MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE);
            $this->status = null;
        }

        /*
         * Check whether this payment module is available
         */

        function update_status() {

            if (($this->enabled == true) && ((int) MODULE_PAYMENT_MULTISAFEPAY_ZONE > 0)) {
                $check_flag = false;
                $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MULTISAFEPAY_ZONE . "' and zone_country_id = '" . $this->billing['country']['id'] . "' order by zone_id");
                while ($check = tep_db_fetch_array($check_query)) {
                    if ($check['zone_id'] < 1) {
                        $check_flag = true;
                        break;
                    } elseif ($check['zone_id'] == $this->delivery['zone_id']) {
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
            $mode = $this->get_config_key((int)$platformId, 'MODULE_PAYMENT_MULTISAFEPAY_API_SERVER');
            if ($mode !== false) {
                $mode = strtolower($mode);
                $title = $this->getTitle(defined('MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE') ? constant('MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE') : '');
                if ($title != '') {
                    $this->title = $title;
                    if (strpos($mode, 'live') === false) {
                        $this->title .= ' [Test]';
                    }
                }
                $titlePublic = $this->getTitle(defined('MODULE_PAYMENT_MULTISAFEPAY_TEXT_PUBLIC_TITLE') ? constant('MODULE_PAYMENT_MULTISAFEPAY_TEXT_PUBLIC_TITLE') : '');
                if ($titlePublic != '') {
                    $this->public_title = $titlePublic;
                    if (strpos($mode, 'live') === false) {
                        $this->public_title .= " [{$this->code}; Test]";
                    }
                }
                return true;
            }
            return false;
        }

        // ---- select payment module ----

        /*
         * Client side javascript that will verify any input fields you use in the
         * payment method selection page
         */
        function javascript_validation() {
            return false;
        }

        /*
         * Outputs the payment method title/text and if required, the input fields
         */

        function selection() {
            $selection = array('id' => $this->code,
                'module' => $this->public_title,
                'fields' => array());
            return $selection;
        }

        /*
         * Any checks of any conditions after payment method has been selected
         */

        function pre_confirmation_check() {
            if (defined('MODULE_PAYMENT_MULTISAFEPAY_GATEWAY_SELECTION') && MODULE_PAYMENT_MULTISAFEPAY_GATEWAY_SELECTION == 'True') {
                $gatewaytest = $_POST['multisafepay_gateway_selection'];
                if (!$gatewaytest) {
                    //$error = 'Selecteer een Gateway';
                    //$payment_error_return = 'payment_error=' . $this->code . '&error=' . urlencode($error);
                    //tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, $payment_error_return, 'SSL', true, false));
                }
                $this->gateway_selection = $_POST['multisafepay_gateway_selection'];
            } else {
                return false;
            }
        }

        // ---- confirm order ----

        /*
         * Any checks or processing on the order information before proceeding to
         * payment confirmation
         */
        function confirmation() {
            return false;
        }

        /*
         * Outputs the html form hidden elements sent as POST data to the payment
         * gateway
         */

        function process_button() {
            if (defined('MODULE_PAYMENT_MULTISAFEPAY_GATEWAY_SELECTION') && MODULE_PAYMENT_MULTISAFEPAY_GATEWAY_SELECTION == 'True') {
                $fields = tep_draw_hidden_field('multisafepay_gateway_selection', $_POST['multisafepay_gateway_selection']);
                return $fields;
            } else {
                return false;
            }
        }

        // ---- process payment ----

        /*
         * Payment verification
         */
        function before_process() {
            $this->_save_order();
            tep_redirect($this->_start_transaction());
        }

        /*
         * Post-processing of the payment/order after the order has been finalised
         */

        function after_process() {
            return false;
        }

        // ---- error handling ----

        /*
         * Advanced error handling
         */
        function output_error() {
            return false;
        }

        function get_error() {
            $error = array(
                'title' => MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR,
                'error' => $this->_get_error_message($_GET['error'])
            );

            return $error;
        }

        // ---- MultiSafepay ----

        /*
         * Starts a new transaction and returns the redirect URL
         */
        function _start_transaction() {

            /** @var \common\classes\Currencies $currencies */
            $currencies = \Yii::$container->get('currencies');
            /** @var OrderAbstract $order */
            $order = $this->manager->getOrderInstance();

            $amount = round($currencies->format_clear($order->info['total_inc_tax'], true, $order->info['currency']), 2) * 100;
            //echo $amount;exit;
            // generate items list
            $items = "<ul>\n";
            foreach ($order->products as $product) {
                $items .= "<li>" . $product['name'] . "</li>\n";
            }
            $items .= "</ul>\n";

            // start transaction
            $this->msp = new MultiSafepayAPI();
            $this->msp->plugin_name = 'Plugin 2.0.2 (' . PROJECT_VERSION . ')';
            $this->msp->test = (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live account');
            $this->msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID;
            $this->msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_ID;
            $this->msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE;
            $this->msp->merchant['notification_url'] = tep_href_link('callback/multisafe', 'action=multi-notify&type=initial', 'SSL');
            $this->msp->merchant['cancel_url'] = tep_href_link('callback/multisafe', 'action=cancel', 'SSL');


            if ($_POST['msp_paymentmethod']) {
                $this->msp->transaction['gateway'] = $_POST['msp_paymentmethod'];
            }

            if ($_POST["msp_issuer"]) {
                $this->msp->extravars = $_POST["msp_issuer"];
            }

            if (MODULE_PAYMENT_MULTISAFEPAY_AUTO_REDIRECT == "True") {
                $this->msp->merchant['redirect_url'] = tep_href_link('callback/multisafe', 'action=success', 'SSL');
            }

            $this->msp->customer['locale'] = strtolower($order->delivery['country']['iso_code_2']) . '_' . $order->delivery['country']['iso_code_2'];
            $this->msp->customer['firstname'] = $order->customer['firstname'];
            $this->msp->customer['lastname'] = $order->customer['lastname'];
            $this->msp->customer['zipcode'] = $order->customer['postcode'];
            $this->msp->customer['city'] = $order->customer['city'];
            $this->msp->customer['country'] = $order->customer['country']['iso_code_2'];
            $this->msp->customer['phone'] = $order->customer['telephone'];
            $this->msp->customer['email'] = $order->customer['email_address'];
            $this->msp->parseCustomerAddress($order->customer['street_address']);

            $this->msp->transaction['id'] = $this->order_id;
            $this->msp->transaction['currency'] = $order->info['currency'];
            $this->msp->transaction['amount'] = $amount;
            $this->msp->transaction['description'] = 'Order #' . $this->order_id . ' at ' . STORE_NAME;
            $this->msp->transaction['items'] = $items;


            if ($_POST["msp_issuer"]) {
                $this->msp->extravars = $_POST["msp_issuer"];
                $url = $this->msp->startDirectXMLTransaction();
            } else {
                $url = $this->msp->startTransaction();
            }


            if ($this->msp->error) {
                if ($order->getOrderId()) {
                    \common\helpers\Order::doCancel((int)$order->getOrderId());
                }
                $this->_error_redirect($this->msp->error_code . ": " . $this->msp->error);
                exit();
            }

            return $url;
        }

        function check_transaction() {
            $this->msp = new MultiSafepayAPI();
            $this->msp->plugin_name = $this->plugin_name;
            $this->msp->test = (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live account');
            $this->msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID;
            $this->msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_ID;
            $this->msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE;
            $this->msp->transaction['id'] = $this->order_id;
            $status = $this->msp->getStatus();

            if ($this->msp->error) {
                return $this->msp->error_code;
            }


            return $status;
        }

        function cancel() {

        }

        /*
         * Checks current order status and updates the database
         */

        private function updateQty(){
            //rewrite to new warehouse way
            $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "'");

            while ($order = tep_db_fetch_array($order_query)) {
                tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int) $order['products_id'] . "'");
            }
        }

        function checkout_notify($order) {
            $this->msp = new MultiSafepayAPI();
            $this->msp->plugin_name = 'Plugin 2.0.2 (' . PROJECT_VERSION . ')';
            $this->msp->test = (MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live' && MODULE_PAYMENT_MULTISAFEPAY_API_SERVER != 'Live account');
            $this->msp->merchant['account_id'] = MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID;
            $this->msp->merchant['site_id'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_ID;
            $this->msp->merchant['site_code'] = MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE;
            $this->msp->transaction['id'] = $this->order_id;
            $status = $this->msp->getStatus();

            if ($this->msp->error) {
                return $this->msp->error_code;
            }

            // determine status
            $reset_cart = false;
            $notify_customer = false;

            $current_order = tep_db_query("SELECT orders_status FROM " . TABLE_ORDERS . " WHERE orders_id = " . $this->order_id);
            $current_order = tep_db_fetch_array($current_order);
            $old_order_status = $current_order['orders_status'];
            $new_stat = $old_order_status;

            switch ($status) {
                case "initialized":
                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED;
                    $reset_cart = true;
                    break;
                case "completed":
                    if (in_array($old_order_status, array(MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED, DEFAULT_ORDERS_STATUS_ID, MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED))) {
                        $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED;
                        $reset_cart = true;
                        if ($old_order_status != $order->info['order_status']) {
                            // $notify_customer = true;
                        }
                        $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED;
                    }
                    break;
                case "uncleared":
                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED;
                    break;
                case "reserved":
                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED;
                    break;
                case "void":
                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID) {
                        // $this->updateQty();
                    }
                    break;
                case "cancelled":
                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID) {
                        // $this->updateQty();
                    }
                    break;
                case "declined":

                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED) {
                        // $this->updateQty();
                    }
                    break;
                case "reversed":
                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED;
                    break;
                case "refunded":
                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED;
                    break;
                case "partial_refunded":
                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED;
                    break;
                case "expired":
                    $order->info['order_status'] = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED;
                    $new_stat = MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED;
                    if ($old_order_status != MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED) {
                        // $this->updateQty();
                    }
                    break;
                default:
                    $order->info['order_status'] = DEFAULT_ORDERS_STATUS_ID;
            }

            // if ($old_order_status != $new_stat) {
            // update order
            // tep_db_query("UPDATE " . TABLE_ORDERS . " SET orders_status = " . $new_stat . " WHERE orders_id = " . $this->order_id);

            $comment = [
                (isset($this->msp->details['ewallet']['status']) ? "Status: {$this->msp->details['ewallet']['status']}":''),
                (isset($this->msp->details['transaction']['amount']) ? "Amount: {$this->msp->details['transaction']['amount']}":''),
                (isset($this->msp->details['transaction']['currency']) ? "Currency: {$this->msp->details['transaction']['currency']}":''),
                (isset($this->msp->details['transaction']['cost']) ? "Cost: {$this->msp->details['transaction']['cost']}":''),
                (isset($this->msp->details['ewallet']['reason']) ? "Reason: {$this->msp->details['ewallet']['reason']}":''),
            ];

            // }

            if ($notify_customer) {

                $this->_notify_customer($new_stat);
            }
            $order->save_products(false);

            \common\helpers\Order::setStatus($this->order_id, (int)$new_stat, [
                'comments' => implode("\n", $comment),
                'customer_notified' => 0,
            ]);

            // reset cart
            if ($reset_cart) {
                tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET . " WHERE customers_id = '" . (int) $order->customer['id'] . "'");
                tep_db_query("DELETE FROM " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " WHERE customers_id = '" . (int) $order->customer['id'] . "'");
            }

            return $status;
        }

        function _get_error_message($code) {
            if (is_numeric($code)) {
                $message = constant(sprintf("MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_%04d", $code));

                if (!$message) {
                    $message = MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_UNKNOWN;
                }
            } else {
                $const = sprintf("MODULE_PAYMENT_MULTISAFEPAY_TEXT_ERROR_%s", strtoupper($code));
                if (defined($const)) {
                    $message = constant($const);
                } else {
                    $message = $code;
                }
            }
            return $message;
        }

        function _error_redirect($error) {
            tep_redirect(tep_href_link(
                FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&error=' . $error, 'NONSSL', true
            ));
        }

        // ---- Ripped from checkout_process.php ----

        /*
         * Store the order in the database, and set $this->order_id
         */
        function _save_order() {
            global $languages_id;

            if (!empty($this->order_id) && $this->order_id > 0) {
                return;
            }

            $order = $this->manager->getOrderInstance();

            $order->save_order();

            $order->save_details();

            $order->save_products(false);

            $stock_updated = false;

            $this->order_id = $order->order_id;
        }

        function _notify_customer($new_order_status = null) {

            $order = $this->manager->getOrderInstance();

            $products_ordered = \frontend\design\boxes\email\OrderProducts::widget(['params' => ['products' => $order->products, 'platform_id' => $order->info['platform_id']]]);

            $order->notify_customer($products_ordered);
        }


        function _output_string($string, $translate = false, $protected = false) {
            if ($protected == true) {
                return htmlspecialchars($string);
            } else {
                if ($translate == false) {
                    return $this->_parse_input_field_data($string, array('"' => '&quot;'));
                } else {
                    return $this->_parse_input_field_data($string, $translate);
                }
            }
        }

        function _output_string_protected($string) {
            return $this->_output_string($string, false, true);
        }

        function _parse_input_field_data($data, $parse) {
            return strtr(trim($data), $parse);
        }

        // ---- installation & configuration ----

        public function configure_keys()
        {
            $status_id = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED : $this->getDefaultOrderStatusId();
            $status_id_compl = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED : $this->getDefaultOrderStatusId();
            $status_id_un = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED : $this->getDefaultOrderStatusId();
            $status_id_res = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED : $this->getDefaultOrderStatusId();
            $status_id_void = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID : $this->getDefaultOrderStatusId();
            $status_id_dec = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED : $this->getDefaultOrderStatusId();
            $status_id_res1 = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED : $this->getDefaultOrderStatusId();
            $status_id_ref = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED : $this->getDefaultOrderStatusId();
            $status_id_ex = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED : $this->getDefaultOrderStatusId();
            $status_id_pref = defined('MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED') ? MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED : $this->getDefaultOrderStatusId();


            return array(
                'MODULE_PAYMENT_MULTISAFEPAY_STATUS' => array(
                    'title' => 'MultiSafepay enabled',
                    'value' => 'True',
                    'description' => 'Enable MultiSafepay payments for this website',
                    'sort_order' => '20',
                    'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_API_SERVER' => array(
                    'title' => 'Type account',
                    'value' => 'Live account',
                    'description' => '<a href="http://www.multisafepay.com/nl/klantenservice-zakelijk/open-een-testaccount.html" target="_blank" style="text-decoration:underline;font-weight:bold;color:#696916;">Sign up for a free test account!</a>',
                    'sort_order' => '21',
                    'set_function' => 'tep_cfg_select_option(array(\'Live account\', \'Test account\'), ',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ACCOUNT_ID' => array(
                    'title' => 'Account ID',
                    'value' => '',
                    'description' => 'Your merchant account ID',
                    'sort_order' => '22',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_SITE_ID' => array(
                    'title' => 'Site ID',
                    'value' => '',
                    'description' => 'ID of this site',
                    'sort_order' => '23',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_SITE_SECURE_CODE' => array(
                    'title' => 'Site Code',
                    'value' => '',
                    'description' => 'Site code for this site',
                    'sort_order' => '24',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_AUTO_REDIRECT' => array(
                    'title' => 'Auto Redirect',
                    'value' => 'True',
                    'description' => 'Enable auto redirect after payment',
                    'sort_order' => '20',
                    'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ZONE' => array(
                    'title' => 'Payment Zone',
                    'value' => '0',
                    'description' => 'If a zone is selected, only enable this payment method for that zone.',
                    'sort_order' => '25',
                    'use_function' => '\\common\\helpers\\Zones::get_zone_class_title',
                    'set_function' => 'tep_cfg_pull_down_zone_classes(',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_SORT_ORDER' => array(
                    'title' => 'Sort order of display.',
                    'value' => '0',
                    'description' => 'Sort order of display. Lowest is displayed first.',
                    'sort_order' => '0',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_INITIALIZED' => array (
                    'title' => 'Set Initialized Order Status',
                    'value' => $status_id,
                    'description' => 'In progress',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),

                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_COMPLETED' => array(
                    'title' => 'Set Completed Order Status',
                    'value' => $status_id_compl,
                    'description' => 'Completed successfully',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_UNCLEARED' => array(
                    'title' => 'Set Uncleared Order Status',
                    'value' => $status_id_un,
                    'description' => 'Not yet cleared',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_RESERVED' => array(
                    'title' => 'Set Reserved Order Status',
                    'value' => $status_id_res,
                    'description' => 'Reserved',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_VOID' => array(
                    'title' => 'Set Voided Order Status',
                    'value' => $status_id_void,
                    'description' => 'Cancelled',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_DECLINED' => array(
                    'title' => 'Set Declined Order Status',
                    'value' => $status_id_dec,
                    'description' => 'Declined (e.g. fraud, not enough balance)',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REVERSED' => array(
                    'title' => 'Set Reversed Order Status',
                    'value' => $status_id_res1,
                    'description' => 'Undone',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_REFUNDED' => array(
                    'title' => 'Set Refunded Order Status',
                    'value' => $status_id_ref,
                    'description' => 'refunded',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_EXPIRED' => array(
                    'title' => 'Set Expired Order Status',
                    'value' => $status_id_ex,
                    'description' => 'Expired',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_ORDER_STATUS_ID_PARTIAL_REFUNDED' => array(
                    'title' => 'Set Partial refunded Order Status',
                    'value' => $status_id_pref,
                    'description' => 'Partial Refunded',
                    'sort_order' => '0',
                    'set_function' => 'tep_cfg_pull_down_order_statuses(',
                    'use_function' => '\\common\\helpers\\Order::get_order_status_name',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_TITLES_ENABLER' => array(
                    'title' => 'Enable gateway titles in checkout',
                    'value' => 'True',
                    'description' => 'Enable the gateway title in checkout',
                    'sort_order' => '20',
                    'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
                ),
                'MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED' => array(
                    'title' => 'Enable icons in gateway titles. If disabled it will overrule option above.',
                    'value' => 'True',
                    'description' => 'Enable the icon in the checkout title for the gateway',
                    'sort_order' => '20',
                    'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
                ),
            );
        }


        public function describe_status_key()
        {
            return new ModuleStatus('MODULE_PAYMENT_MULTISAFEPAY_STATUS', 'True', 'False');
        }

        public function describe_sort_key()
        {
            return new ModuleSortOrder('MODULE_PAYMENT_MULTISAFEPAY_SORT_ORDER');
        }

        function getScriptName() {

            global $PHP_SELF;

            if (class_exists('\Yii') && is_object(\Yii::$app)) {
                return \Yii::$app->controller->id;
            } else {
                return basename($PHP_SELF);
            }
        }

        function getTitle($admin = 'title') {

            if (defined('MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED') && MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED != 'False') {
                $title = ($this->checkView() == "checkout") ? $this->generateIcon($this->getIcon()) . " " : "";
            } else {
                $title = "";
            }

//            $title .= ($this->checkView() == "admin") ? "MultiSafepay - " : "";
            if ($admin && $this->checkView() == "admin") {
                $title .= $admin;
            } else {
                $title .= $this->getLangStr($admin);
            }
            return $title;
        }

        function getLangStr($str) {
            if (MODULE_PAYMENT_MULTISAFEPAY_TITLES_ENABLER == "True" || MODULE_PAYMENT_MULTISAFEPAY_TITLES_ICON_DISABLED == 'False') {
                switch ($str) {
                    case "title":
                        return MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE;
                    case "iDEAL":
                        return MODULE_PAYMENT_MSP_IDEAL_TEXT_TITLE;
                    case "Bank transfer":
                        return MODULE_PAYMENT_MSP_BANKTRANS_TEXT_TITLE;
                    case "GiroPay":
                        return MODULE_PAYMENT_MSP_GIROPAY_TEXT_TITLE;
                    case "VISA":
                        return MODULE_PAYMENT_MSP_VISA_TEXT_TITLE;
                    case "AMEX":
                        return MODULE_PAYMENT_MSP_AMEX_TEXT_TITLE;
                    case "DirectDebit":
                        return MODULE_PAYMENT_MSP_DIRDEB_TEXT_TITLE;
                    case "Bancontact/Mistercash":
                        return MODULE_PAYMENT_MSP_MISTERCASH_TEXT_TITLE;
                    case "MasterCard":
                        return MODULE_PAYMENT_MSP_MASTERCARD_TEXT_TITLE;
                    case "PAYPAL":
                        return MODULE_PAYMENT_MSP_PAYPAL_TEXT_TITLE;
                    case "Maestro":
                        return MODULE_PAYMENT_MSP_MAESTRO_TEXT_TITLE;
                    case "SOFORT Banking":
                        return MODULE_PAYMENT_MSP_DIRECTBANK_TEXT_TITLE;
                    case "BABYGIFTCARD":
                        return MODULE_PAYMENT_MSP_BABYGIFTCARD_TEXT_TITLE;
                    case "BOEKENBON":
                        return MODULE_PAYMENT_MSP_BOEKENBON_TEXT_TITLE;
                    case "DEGROTESPEELGOEDWINKEL":
                        return MODULE_PAYMENT_MSP_DEGROTESPEELGOEDWINKEL_TEXT_TITLE;
                    case "EBON":
                        return MODULE_PAYMENT_MSP_EBON_TEXT_TITLE;
                    case "EROTIEKBON":
                        return MODULE_PAYMENT_MSP_EROTIEKBON_TEXT_TITLE;
                    case "LIEF":
                        return MODULE_PAYMENT_MSP_LIEF_TEXT_TITLE;
                    case "WEBSHOPGIFTCARD":
                        return MODULE_PAYMENT_MSP_WEBSHOPGIFTCARD_TEXT_TITLE;
                    case "PARFUMNL":
                        return MODULE_PAYMENT_MSP_PARFUMNL_TEXT_TITLE;
                    case "PARFUMCADEAUKAART":
                        return MODULE_PAYMENT_MSP_PARFUMCADEAUKAART_TEXT_TITLE;
                    case "GEZONDHEIDSBON":
                        return MODULE_PAYMENT_MSP_GEZONDHEIDSBON_TEXT_TITLE;
                    case "FASHIONCHEQUE":
                        return MODULE_PAYMENT_MSP_FASHIONCHEQUE_TEXT_TITLE;
                    default:
                        return MODULE_PAYMENT_MULTISAFEPAY_TEXT_TITLE;
                        break;
                }
            }
        }

        function checkView() {
            $view = "admin";

            if (tep_session_name() != 'tlAdminID') {
                if ($this->getScriptName() == 'checkout' /* FILENAME_CHECKOUT_PAYMENT */) {
                    $view = "checkout";
                } else {
                    $view = "frontend";
                }
            }
            return $view;
        }

        function generateIcon($icon) {
            return tep_image($icon);
        }

        function getIcon() {
            $icon = DIR_WS_IMAGES . "multisafepay/en/" . $this->icon;

            if (file_exists(DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon)) {
                $icon = DIR_WS_IMAGES . "multisafepay/" . strtolower($this->getUserLanguage("DETECT")) . "/" . $this->icon;
            }
            return $icon;
        }

        function getUserLanguage($savedSetting) {
            if ($savedSetting != "DETECT") {
                return $savedSetting;
            }

            global $languages_id;

            $query = tep_db_query("select languages_id, name, code, image from " . TABLE_LANGUAGES . " where languages_id = " . (int) $languages_id . " limit 1");
            if ($languages = tep_db_fetch_array($query)) {
                return strtoupper($languages['code']);
            }

            return "EN";
        }

        function getlocale($lang) {
            switch ($lang) {
                case "dutch":
                    $lang = 'nl_NL';
                    break;
                case "spanish":
                    $lang = 'es_ES';
                    break;
                case "french":
                    $lang = 'fr_FR';
                    break;
                case "german":
                    $lang = 'de_DE';
                    break;
                case "english":
                    $lang = 'en_EN';
                    break;
                default:
                    $lang = 'en_EN';
                    break;
            }
            return $lang;
        }

        function getcountry($country) {
            if (empty($country)) {
                $langcode = explode(";", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                $langcode = explode(",", $langcode['0']);
                return strtoupper($langcode['1']);
            } else {
                return strtoupper($country);
            }
        }

        public function isOnline() {
            return true;
        }
    }

}
