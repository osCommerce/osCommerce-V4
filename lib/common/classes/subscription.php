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

namespace common\classes;

use yii\web\Session;
use common\classes\platform;

class subscription {

    var $info, $totals, $products, $customer, $delivery, $content_type;
    private $data;

    function __construct($order_id = '') {
        $this->info = array();
        $this->totals = array();
        $this->products = array();
        $this->customer = array();
        $this->delivery = array();
        $this->billing = array();
        $this->tax_address = array();
        $this->order_id = (int) $order_id;

        if (tep_not_null($order_id)) {
            $this->query($order_id);
        }
    }

    function prepareDetails($order_id) {
        $order_id = tep_db_prepare_input($order_id);
        $order_query = tep_db_query("select * from " . TABLE_SUBSCRIPTION . " where subscription_id = '" . (int) $order_id . "'");
        $this->data = tep_db_fetch_array($order_query);
        return $this;
    }

    function getDetails() {
        return $this->data;
    }

    function overloadAddressDetails($type = 'delivery') {

        $country = \common\helpers\Country::get_country_info_by_name($this->data[$type . '_country'], $this->data['language_id']);
        $this->$type = array('name' => $this->data[$type . '_name'],
            'gender' => $this->data[$type . '_gender'],
            'firstname' => $this->data[$type . '_firstname'],
            'lastname' => $this->data[$type . '_lastname'],
            'company' => $this->data[$type . '_company'],
            'street_address' => $this->data[$type . '_street_address'],
            'suburb' => $this->data[$type . '_suburb'],
            'city' => $this->data[$type . '_city'],
            'postcode' => $this->data[$type . '_postcode'],
            'state' => $this->data[$type . '_state'],
            'country' => $country,
            'address_book_id' => $this->data[$type . '_address_book_id'],
            'format_id' => $this->data[$type . '_address_format_id'],
            'zone_id' => \common\helpers\Zones::get_zone_id($country['id'], $this->data[$type . '_state']),
            'country_id' => $country['id'],);
    }

    function query($order_id) {
        global $languages_id;

        $order = $this->prepareDetails($order_id)->getDetails();



        $order_status_query = tep_db_query("select orders_status_name from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . $order['subscription_status'] . "' and language_id = '" . (int) $languages_id . "'");
        $order_status = tep_db_fetch_array($order_status_query);

        $this->info = array('currency' => $order['currency'],
            'currency_value' => $order['currency_value'],
            'platform_id' => $order['platform_id'],
            'language_id' => $order['language_id'],
            'admin_id' => $order['admin_id'],
            'payment_method' => $order['payment_method'],
            'cc_type' => $order['cc_type'],
            'cc_owner' => $order['cc_owner'],
            'cc_number' => $order['cc_number'],
            'cc_expires' => $order['cc_expires'],
            'date_purchased' => $order['date_purchased'],
            'tracking_number' => $order['tracking_number'],
            'orders_status' => $order_status['orders_status_name'],
            'orders_status_id' => $order['subscription_status'],
            'last_modified' => $order['last_modified'],
            'total' => 0,
            'payment_class' => $order['payment_class'],
            'shipping_class' => $order['shipping_class'],
            'shipping_method' => $order['shipping_method'],
            'shipping_cost' => 0, //new added
            'subtotal' => 0, //new added
            'subtotal_inc_tax' => 0, //new added
            'subtotal_exc_tax' => 0, //new added
            'tax' => 0, //new added
            'tax_groups' => array(), //new added
            'comments' => (isset($_POST['comments']) ? $_POST['comments'] : $_SESSION['comments']), //new added
            'basket_id' => (int) $order['basket_id'], //new added
            'shipping_weight' => $order['shipping_weight'],
            'transaction_id' => $order['transaction_id'],
        );

        $country = \common\helpers\Country::get_country_info_by_name($order['customers_country'], $order['language_id']);
        $this->customer = array('id' => $order['customers_id'],
            'customer_id' => $order['customers_id'],
            'name' => $order['customers_name'],
            'firstname' => $order['customers_firstname'],
            'lastname' => $order['customers_lastname'],
            'company' => $order['customers_company'],
            'company_vat' => $order['customers_company_vat'],
            'company_vat_status' => $order['customers_company_vat_status'],
            'customs_number' => $order['customers_customs_number'],
            'customs_number_status' => $order['customers_customs_number_status'],
            'street_address' => $order['customers_street_address'],
            'suburb' => $order['customers_suburb'],
            'city' => $order['customers_city'],
            'postcode' => $order['customers_postcode'],
            'state' => $order['customers_state'],
            'country' => $country,
            'zone_id' => \common\helpers\Zones::get_zone_id($country['id'], $order['customers_state']),
            'country_id' => $country['id'],
            'format_id' => $order['customers_address_format_id'],
            'telephone' => $order['customers_telephone'],
            'landline' => $order['customers_landline'],
            'email_address' => $order['customers_email_address']);

        $this->overloadAddressDetails('delivery');

        if (empty($this->delivery['name']) && empty($this->delivery['street_address'])) {
            $this->delivery = false;
        }

        $this->overloadAddressDetails('billing');

        $index = 0;
        $tax_groups = [];
        $orders_products_query = tep_db_query("select subscription_products_id, " .
                "is_virtual, " .
                "gv_state, " .
                "gift_wrap_price, gift_wrapped, " .
                "if(length(uprid),uprid, products_id) as products_id, products_name, products_model, products_price, products_tax, products_quantity, final_price, is_giveaway from " . TABLE_SUBSCRIPTION_PRODUCTS . " where subscription_id = '" . (int) $order_id . "'");
        while ($orders_products = tep_db_fetch_array($orders_products_query)) {
            $this->products[$index] = array('qty' => $orders_products['products_quantity'],
                'id' => $orders_products['products_id'],
                'name' => $orders_products['products_name'],
                'model' => $orders_products['products_model'],
                'tax' => $orders_products['products_tax'],
                'ga' => $orders_products['is_giveaway'],
                'is_virtual' => (int) $orders_products['is_virtual'],
                'gv_state' => $orders_products['gv_state'],
                'gift_wrap_price' => $orders_products['gift_wrap_price'],
                'gift_wrapped' => !!$orders_products['gift_wrapped'],
                'price' => $orders_products['products_price'],
                'final_price' => $orders_products['final_price'],
                'subscription_products_id' => $orders_products['subscription_products_id']
            );

            $subtotal += $orders_products['final_price'] * $orders_products['products_quantity'];
            $tax += $orders_products['final_price'] * $orders_products['products_quantity'] * $orders_products['products_tax'] / 100;
            $selected_tax = "";
            $query_tax_class_id = "select tax_class_id, sum(tax_rate) as rate from " . TABLE_TAX_RATES . " group by tax_class_id, tax_zone_id order by tax_priority";
            $result_tax_class_id = tep_db_query($query_tax_class_id);
            if (tep_db_num_rows($result_tax_class_id) > 0) {
                while ($array_tax_class_id = tep_db_fetch_array($result_tax_class_id)) {
                    if ($array_tax_class_id['rate'] == $orders_products['products_tax']) {
                        $tax_class_id = $array_tax_class_id['tax_class_id'];
                        $selected_tax = \common\helpers\Tax::get_tax_description($tax_class_id, $this->delivery['country_id'], $this->delivery['zone_id']);
                        break;
                    }
                }
            }
            if (!isset($tax_groups[$selected_tax])) {
                $tax_groups[$selected_tax] = \common\helpers\Tax::roundTax($orders_products['final_price'] * $orders_products['products_quantity'] * $orders_products['products_tax'] / 100);
            } else {
                $tax_groups[$selected_tax] += \common\helpers\Tax::roundTax($orders_products['final_price'] * $orders_products['products_quantity'] * $orders_products['products_tax'] / 100);
            }
            $index++;
        }
        // {{ content type
        $this->content_type = 'physical';
        $count_virtual = 0;
        $count_physical = 0;
        foreach ($this->products as $__product) {
            if ($__product['is_virtual'] != 0) {
                $count_virtual++;
            } else {
                $count_physical++;
            }
        }
        if ($count_physical > 0 && $count_virtual == 0) {
            $this->content_type = 'physical';
        } elseif ($count_physical > 0 && $count_virtual > 0) {
            $this->content_type = 'mixed';
        } elseif ($count_physical == 0 && $count_virtual > 0) {
            $this->content_type = 'virtual';
        } else {
            $this->content_type = 'physical';
        }
        // }} content type

        if (DISPLAY_PRICE_WITH_TAX == 'true') {
            $this->info['subtotal'] = round($subtotal + $tax, 2);
        } else {
            $this->info['subtotal'] = round($subtotal, 2);
        }
        $this->info['total'] = round($subtotal + $tax, 2);
        $this->info['tax'] = round($tax, 2);
        $this->info['tax_groups'] = $tax_groups;
        $totals_query = tep_db_query("select * from " . TABLE_SUBSCRIPTION_TOTAL . " where subscription_id = '" . (int) $order_id . "' order by sort_order");
        while ($totals = tep_db_fetch_array($totals_query)) {
            $this->totals[] = array('title' => $totals['title'],
                'value' => $totals['value'],
                'class' => $totals['class'],
                'text' => $totals['text'],
                'text_exc_tax' => $totals['text_exc_tax'],
                'text_inc_tax' => $totals['text_inc_tax'],
// {{
                'tax_class_id' => $totals['tax_class_id'],
                'value_exc_vat' => $totals['value_exc_vat'],
                'value_inc_tax' => $totals['value_inc_tax'],
// }}
            );
            if ($totals['class'] == 'ot_subtotal') {
                $this->info['subtotal_inc_tax'] = $totals['value_inc_tax'];
                $this->info['subtotal_exc_tax'] = $totals['value_exc_vat'];
            } else if ($totals['class'] == 'ot_shipping') {
                $this->info['shipping_cost_inc_tax'] = $totals['value_inc_tax'];
                $this->info['shipping_cost_exc_tax'] = $totals['value_exc_vat'];
            } elseif ($totals['class'] == 'ot_total') {
                $this->info['total_inc_tax'] = $totals['value_inc_tax'];
                $this->info['total_exc_tax'] = $totals['value_exc_vat'];
            }
        }
    }

// recalc stubs
    function _billing_address() {
        return false;
    }

    function _shipping_address() {
        return false;
    }

    function change_shipping($new_shipping) {
        if (get_class($this) == 'common\classes\Order' && \frontend\design\Info::isTotallyAdmin()) {
            if (!is_array($new_shipping)) {
                $this->info['shipping_class'] = '';
                $this->info['shipping_method'] = '';
                $this->info['shipping_cost'] = '0';
                $this->info['shipping_cost_inc_tax'] = '0';
                $this->info['shipping_cost_exc_tax'] = '0';
            } else {
                $this->info['shipping_class'] = $new_shipping['id'];
                $this->info['shipping_method'] = $new_shipping['title'];
                $this->info['shipping_cost'] = $new_shipping['cost'];
                $this->info['shipping_cost_inc_tax'] = $new_shipping['cost_inc_tax'];
                $this->info['shipping_cost_exc_tax'] = $new_shipping['cost'];
                $this->info['total'] = $this->info['subtotal'] + $new_shipping['cost'];
                $this->info['total_inc_tax'] = $this->info['subtotal_inc_tax'] + $new_shipping['cost'];
                $this->info['total_exc_tax'] = $this->info['subtotal_exc_tax'] + $new_shipping['cost'];
            }
        }
        return false;
    }

    public function notify_customer($products_ordered) {
        global $order_totals, $sendto, $billto, $payment;

        $emailTemplate = '';
        $ostatus = tep_db_fetch_array(tep_db_query("select orders_status_template_confirm from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $this->info['language_id'] . "' and orders_status_id='" . (int) $this->info['order_status'] . "' LIMIT 1 "));
        if (!empty($ostatus['orders_status_template_confirm'])) {
            $get_template_r = tep_db_query("select * from " . TABLE_EMAIL_TEMPLATES . " where email_templates_key='" . tep_db_input($ostatus['orders_status_template_confirm']) . "'");
            if (tep_db_num_rows($get_template_r) > 0) {
                $emailTemplate = $ostatus['orders_status_template_confirm'];
            }
        }
        if (empty($emailTemplate)) {
            return;
        }

        $email_params = array();
        $email_params['STORE_NAME'] = STORE_NAME;
        $email_params['ORDER_NUMBER'] = method_exists($this, 'getOrderNumber')?$this->getOrderNumber():$this->order_id;
        $email_params['ORDER_DATE_SHORT'] = strftime(DATE_FORMAT_SHORT);
        if (\frontend\design\Info::isTotallyAdmin()) {
            $email_params['ORDER_INVOICE_URL'] = \common\helpers\Output::get_clickable_link(tep_catalog_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->order_id, 'SSL', false));
        } else {
            $email_params['ORDER_INVOICE_URL'] = \common\helpers\Output::get_clickable_link(tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->order_id, 'SSL', false));
        }
        $email_params['ORDER_DATE_LONG'] = strftime(DATE_FORMAT_LONG);
        $email_params['PRODUCTS_ORDERED'] = substr($products_ordered, 0, -1);

        $email_params['ORDER_TOTALS'] = '';

        $order_total_output = [];
        foreach ($order_totals as $total) {
            if (class_exists($total['code'])) {
                if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                    if (true == $GLOBALS[$total['code']]->visibility(PLATFORM_ID, 'TEXT_EMAIL')) {
                        if (method_exists($GLOBALS[$total['code']], 'visibility')) {
                            $order_total_output[] = $GLOBALS[$total['code']]->displayText(PLATFORM_ID, 'TEXT_EMAIL', $total);
                        } else {
                            $order_total_output[] = $total;
                        }
                    }
                }
            }
        }
        for ($i = 0, $n = sizeof($order_total_output); $i < $n; $i++) {
            $email_params['ORDER_TOTALS'] .= ($order_total_output[$i]['show_line'] ? '<div style="border-top:1px solid #ccc"></div>' : '') . strip_tags($order_total_output[$i]['title']) . ' ' . strip_tags($order_total_output[$i]['text']) . "\n";
        }
        $email_params['ORDER_TOTALS'] = substr($email_params['ORDER_TOTALS'], 0, -1);
        $email_params['BILLING_ADDRESS'] = \common\helpers\Address::address_label(\Yii::$app->user->getId(), $billto, 0, '', "\n");
        $email_params['DELIVERY_ADDRESS'] = ($this->content_type != 'virtual' ? \common\helpers\Address::address_label(\Yii::$app->user->getId(), $sendto, 0, '', "\n") : '');
        $payment_method = '';
        if (!empty($payment) && is_object($GLOBALS[$payment])) {
            $payment_method = $GLOBALS[$payment]->title;
            if ($GLOBALS[$payment]->email_footer) {
                $payment_method .= "\n\n" . $GLOBALS[$payment]->email_footer;
            }
        }
        $email_params['PAYMENT_METHOD'] = $payment_method;

        $email_params['ORDER_COMMENTS'] = tep_db_output($this->info['comments']);

        list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template($emailTemplate, $email_params, -1, $this->info['platform_id']);

// {{
        if (!$GLOBALS[$payment]->dont_send_email)
// }}
            \common\helpers\Mail::send(
                    $this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address'], $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS
            );
// {{
        if (!$GLOBALS[$payment]->dont_send_email)
// }}
            if (SEND_EXTRA_ORDER_EMAILS_TO == '') {
                \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            } else {
//            tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, array(), 'CC: ' . SEND_EXTRA_ORDER_EMAILS_TO);
            }
    }

}
