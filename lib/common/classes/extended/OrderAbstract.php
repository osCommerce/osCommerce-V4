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

namespace common\classes\extended;

use Yii;
use yii\helpers\ArrayHelper;
use backend\models\ProductNameDecorator;
use common\classes\events\common\order\OrderSaveEvent;
use common\classes\events\common\order\ProductsSaveToOrderEvent;
use common\classes\modules\ModuleShipping;
use yii\db\Expression;
use common\classes\platform;
use common\helpers\Country as CountryHelper;

abstract class OrderAbstract extends OrderShadowAbstract{

    public $table_prefix = '';

    public $content_type;
    public $subscription;

    public $info = [];
    public $totals = [];
    public $products = [];
    public $customer = [];
    public $delivery = [];
    public $billing = [];
    public $tax_address = [];
    private $data;
    public $status;
    public $migrated;
    public $order_id;

    public $withDelivery = null;

    function __construct($order_id = null) {
        $this->order_id = (int) $order_id;
        if (!is_null($this->order_id)) {
            $this->query($order_id);
        } else {
            $this->cart();
        }
    }

    function prepareDetails($order_id) {
        $this->data = $this->getARModel()->where(['orders_id' => $order_id ])->asArray()->one();
        if (empty($this->data)) {
            $keys = array_keys($this->getARModel(true)->attributes);
            \common\helpers\Php8::nullProps($this->data, $keys);
        }
        return $this;
    }

    function getDetails() {
        return $this->data;
    }

    public function getReferenceId() {
        if (isset($this->data['reference_id'])) {
            return $this->data['reference_id'];
        } else {
            return false;
        }
    }

    public function getOrderId() {
        return isset($this->info['orders_id'])?$this->info['orders_id']:(isset($this->order_id)?$this->order_id:'');
    }

/**
 * get assigned (saved) order number for the order (or order id if string field is empty)
 * @return string
 */
    public function getOrderNumber() {
      return (!empty($this->info['order_number']))?$this->info['order_number']: $this->getOrderId();
    }

/**
 * get assigned (saved) invoice number for the order
 * @return string
 */
    public function getInvoiceNumber() {
        /** @var \common\extensions\InvoiceNumberFormat\InvoiceNumberFormat $serverExt */
        $serverExt = \common\helpers\Acl::checkExtensionAllowed('InvoiceNumberFormat', 'allowed');
        return (!empty($this->info['invoice_number']))?$this->info['invoice_number']: (!empty($serverExt) && $serverExt::hasInvoiceNumber($this->info['platform_id'])?'':$this->getOrderNumber());
    }

    function overloadAddressDetails($type = 'delivery') {

        $country = CountryHelper::get_country_info_by_name($this->data[$type . '_country'], $this->data['language_id']);
        if (!is_array($country)){
            $pCountry = \Yii::$app->get('platform')->country($this->data['platform_id']);
            if ($pCountry){
                $country = CountryHelper::get_country_info_by_iso($pCountry->countries_iso_code_2, 'iso-2', $this->data['language_id']);
            }
            if (!is_array($country)) $country = [];
        }
        $this->$type = array('name' => $this->data[$type . '_name'],
            'gender' => $this->data[$type . '_gender'],
            'firstname' => $this->data[$type . '_firstname'],
            'lastname' => $this->data[$type . '_lastname'],
            'company' => $this->data[$type . '_company'],
            'company_vat' => $this->data[$type .'_company_vat'],
            'company_vat_status' => $this->data[$type .'_company_vat_status'],
            'customs_number' => $this->data[$type .'_customs_number'],
            'customs_number_status' => $this->data[$type .'_customs_number_status'],
            'telephone' => isset($this->data[$type . '_telephone'])?$this->data[$type . '_telephone']:'',
            'email_address' => isset($this->data[$type . '_email_address'])?$this->data[$type . '_email_address']:'',
            'street_address' => $this->data[$type . '_street_address'],
            'suburb' => $this->data[$type . '_suburb'],
            'city' => $this->data[$type . '_city'],
            'postcode' => $this->data[$type . '_postcode'],
            'state' => $this->data[$type . '_state'],
            'country' => $country,
            'address_book_id' => $this->data[$type . '_address_book_id'],
            'format_id' => $this->data[$type . '_address_format_id'],
            'zone_id' => isset($country['id'])?\common\helpers\Zones::get_zone_id($country['id'], $this->data[$type . '_state']):0,
            'country_id' => isset($country['id'])?$country['id']:0,
        );
    }

    function query($order_id) {
        global $languages_id;

        //$cart = $this->getCart();

        $order = $this->prepareDetails($order_id)->getDetails();
        $order_total = false;
        $shipping_method = false;
        $total_inc_tax = $total_exc_tax = null;
        $total_paid_inc_tax = $total_paid_exc_tax = null;
        $total_refund_inc_tax = $total_refund_exc_tax = null;
        $totals_query = tep_db_query("select * from " . $this->table_prefix . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $order_id . "' order by sort_order");
        while ($totals = tep_db_fetch_array($totals_query)) {
            $this->totals[$totals['sort_order']] = array('title' => $totals['title'],
                'value' => $totals['value'],
                'class' => $totals['class'],
                'code' => $totals['class'],
                'text' => $totals['text'],
                'text_exc_tax' => $totals['text_exc_tax'],
                'text_inc_tax' => $totals['text_inc_tax'],
                'tax_class_id' => $totals['tax_class_id'],
                'value_exc_vat' => $totals['value_exc_vat'],
                'value_inc_tax' => $totals['value_inc_tax'],
                'sort_order' => $totals['sort_order'],
            );
            /* if ($totals['class'] == 'ot_subtotal') {
              $this->info['subtotal_inc_tax'] = $totals['value_inc_tax'];
              $this->info['subtotal_exc_tax'] = $totals['value_exc_vat'];
              } else if ($totals['class'] == 'ot_shipping') {
              $this->info['shipping_cost_inc_tax'] = $totals['value_inc_tax'];
              $this->info['shipping_cost_exc_tax'] = $totals['value_exc_vat'];
              } */
            if ($totals['class'] == 'ot_total') {
                $total_inc_tax = $totals['value_inc_tax'];
                $total_exc_tax = $totals['value_exc_vat'];
            }
            if ($totals['class'] == 'ot_paid') {
                $total_paid_inc_tax = $totals['value_inc_tax'];
                $total_paid_exc_tax = $totals['value_exc_vat'];
            }
            if ($totals['class'] == 'ot_refund') {
                $total_refund_inc_tax = $totals['value_inc_tax'];
                $total_refund_exc_tax = $totals['value_exc_vat'];
            }
            if ( $order_total===false && $totals['class']=='ot_total' ){
                $order_total = $totals;
            }
            if ( $shipping_method === false && $totals['class']=='ot_shipping' ){
                $shipping_method = $totals;
            }
        }

        $this->info = array('currency' => $order['currency'],
            'currency_value' => $order['currency_value'],
            'platform_id' => $order['platform_id'],
            'department_id' => $order['department_id'],
            'language_id' => $order['language_id'],
            'admin_id' => $order['admin_id'],
            'orders_id' => $order['orders_id'],
            'payment_method' => $order['payment_method'],
            'cc_type' => $order['cc_type'],
            'cc_owner' => $order['cc_owner'],
            'cc_number' => $order['cc_number'],
            'cc_expires' => $order['cc_expires'],
            'date_purchased' => $order['date_purchased'],
            'tracking_number' => (!empty($order['tracking_number'])?explode(";", $order['tracking_number']):[]),
            'orders_status_name' => \common\helpers\Order::get_order_status_name($order['orders_status'], $languages_id ),
            'order_status' => $order['orders_status'],
            'last_modified' => $order['last_modified'],
            'total' => $order_total['value_inc_tax'] ?? null,
            'payment_class' => $order['payment_class'],
            'transaction_id' => $order['transaction_id'],
            'shipping_class' => $order['shipping_class'],
            'shipping_method' => ((substr($shipping_method['title']??null, -1) == ':') ? substr(strip_tags($shipping_method['title']??null), 0, -1) : strip_tags($shipping_method['title']??null)),
            'shipping_cost' => (is_array($shipping_method)?$shipping_method['value_exc_vat']:0),
            'shipping_cost_inc_tax' => (is_array($shipping_method)?$shipping_method['value_inc_tax']:0),
            'shipping_cost_exc_tax' => (is_array($shipping_method)?$shipping_method['value_exc_vat']:0),
            'subtotal' => 0, //new added
            'subtotal_inc_tax' => 0, //new added
            'subtotal_exc_tax' => 0, //new added
            'tax' => 0, //new added
            'tax_groups' => array(), //new added
            'comments' => (isset($_POST['comments']) ? filter_var(\Yii::$app->request->post('comments', ''), FILTER_SANITIZE_STRING) : ($_SESSION['comments'] ?? '')), //new added
            'external_orders_id' => $order['external_orders_id'],
            'basket_id' => (int) $order['basket_id'], //new added
            'pointto' => $order['pointto'],
            'shipping_weight' => $order['shipping_weight'],
            'total_paid_inc_tax' => $total_paid_inc_tax,
            'total_paid_exc_tax' => $total_paid_exc_tax,
            'total_refund_inc_tax' => floatval($total_refund_inc_tax),
            'total_refund_exc_tax' => floatval($total_refund_exc_tax),
            'delivery_date' => $order['delivery_date'],
            'products_price_qty_round' => $order['products_price_qty_round'],
            'cash_data_summ' => isset($order['cash_data_summ'])?$order['cash_data_summ']:0.00,
            'cash_data_change' => isset($order['cash_data_change'])?$order['cash_data_change']:0.00,
            'card_reference_id' => isset($order['card_reference_id'])?$order['card_reference_id']:'',
            'label_class' => $order['label_class'] ?? null,
            'purchase_order' => $order['purchase_order'] ?? null,
        );

        if (!empty($order['order_number'])) {
           $this->info['order_number'] = $order['order_number'];
        }
        /** @var \common\extensions\InvoiceNumberFormat\InvoiceNumberFormat $serverExt */
        $serverExt = \common\helpers\Acl::checkExtensionAllowed('InvoiceNumberFormat', 'allowed');
        if (!empty($serverExt) && $serverExt::hasInvoiceNumber($order['platform_id']) && !empty($order['invoice_number'])) {
           $this->info['invoice_number'] = $order['invoice_number'];
        }


        $this->subscription = [
            'subtotal' => 0,
            'subtotal_inc_tax' => 0,
            'subtotal_exc_tax' => 0,
            'shipping_cost' => 0,
            'shipping_cost_inc_tax' => 0,
            'shipping_cost_exc_tax' => 0,
            'tax' => 0,
            'tax_groups' => array(),
            'total' => 0,
            'total_inc_tax' => 0,
            'total_exc_tax' => 0,
        ];

        if (!tep_not_null($this->info['comments'])) {
            $check = tep_db_fetch_array(tep_db_query("select comments from " . $this->table_prefix . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int) $order_id . "' order by orders_status_history_id asc limit 1"));
            $this->info['comments'] = $check['comments'] ?? null;
        }

        $country = CountryHelper::get_country_info_by_name($order['customers_country'], $order['language_id']);
        $newsletter = 0;
        if (!empty($order['customers_id']) ) {
            try {
                $newsletter = \common\models\Customers::find()->select('customers_newsletter')
                    ->andWhere(['customers_id' => $order['customers_id'] , 'platform_id' => $order['platform_id']]) ->asArray()->scalar();

            } catch (\Exception $ex) {
                \Yii::warning(print_r($ex->getMessage(), true), 'TLDEBUG');
            }
        }
        /** @var \common\extensions\Subscribers\Subscribers $subscr  */
        if ($subscr = \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed')) {
            if (empty($newsletter)) {
                try {
                    $newsletter = \common\extensions\Subscribers\models\Subscribers::find()
                        ->andWhere([ 'platform_id' => $order['platform_id'], 'subscribers_status' => 1])
                        ->andWhere(['like',  'subscribers_email_address', $order['customers_email_address'], false])
                        ->asArray()->count();
                        ;
                } catch (\Exception $ex) {
                    \Yii::warning(print_r($ex->getMessage(), true), 'TLDEBUG');
                }
            }
        }
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
            'newsletter' => $newsletter,
            'zone_id' => isset($country['id'])?\common\helpers\Zones::get_zone_id($country['id'], $order['customers_state']):0,
            'country_id' => isset($country['id'])?$country['id']:0,
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
        $orders_products_list = $this->getProductsARModel()->select(['*', 'if(length(uprid),uprid, products_id) as products_id'])
                ->where(['orders_id' => $order_id])->asArray()->orderBy('sort_order, orders_products_id')->all();

        $subtotal = $tax = 0;
        foreach($orders_products_list as $orders_products){
            $this->products[$index] = array('qty' => $orders_products['products_quantity'],
                'id' => \common\helpers\Inventory::normalize_id($orders_products['products_id']),
                'name' => $orders_products['products_name'],
                'model' => $orders_products['products_model'] ?? null,
                'tax' => $orders_products['products_tax'] ?? null,
                'ga' => $orders_products['is_giveaway'] ?? null,
                'props' => $orders_products['props'] ?? null,
                'propsData' => (!empty($orders_products['props'])? Yii::$app->get('PropsHelper')::XmlToParams($orders_products['props']):''),
                'is_virtual' => (int) $orders_products['is_virtual'],
                'gv_state' => $orders_products['gv_state'] ?? null,
                'gift_wrap_price' => $orders_products['gift_wrap_price'] ?? null,
                'gift_wrapped' => !!$orders_products['gift_wrapped'] ?? null,
                'price' => $orders_products['products_price'] ?? null,
                'final_price' => $orders_products['final_price'] ?? null,
                'sets_array' => (!empty($orders_products['sets_array'])?unserialize($orders_products['sets_array']):false),
                /* PC configurator addon begin */
                'template_uprid' => $orders_products['template_uprid'],
                'parent_product' => $orders_products['parent_product'],
                'sub_products' => (tep_not_null($orders_products['sub_products']) ? explode(',', $orders_products['sub_products']) : ''),
                'relation_type' => strval($orders_products['relation_type'] ?? null),
                //'configurator_price' => $cart->configurator_price($products[$i]['id'], $products), // TODO
                /* PC configurator addon end */
                'sort_order' => $orders_products['sort_order'] ?? null,
                'weight' => $orders_products['products_weight'] ?? null,
                'status' => $orders_products['orders_products_status'] ?? null,
                'status_manual' => $orders_products['orders_products_status_manual'] ?? null,
                'orders_products_id' => $orders_products['orders_products_id'],
                'promo_id' => $orders_products['promo_id'] ?? null,
                'specials_id' => $orders_products['specials_id'] ?? null,
                'discount_description' => $orders_products['discount_description']  ?? null,
                'qty_cnld' => $orders_products['qty_cnld'] ?? null,
                'qty_rcvd' => $orders_products['qty_rcvd'] ?? null,
                'qty_dspd' => $orders_products['qty_dspd'] ?? null,
                'qty_dlvd' => $orders_products['qty_dlvd'] ?? null
            );
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                $this->products[$index] = array_merge($ext::queryOrderFrontend($order_id, $orders_products, $this->table_prefix), $this->products[$index]);
            }

            /*if (is_object($cart) && $cart->existOwerwritten($this->products[$index]['id'])) {
                $this->overWrite($this->products[$index]['id'], $this->products[$index]);
            }*/
            $subtotal += $orders_products['final_price'] * $orders_products['products_quantity'];
            $selected_tax = "";

            static $tax_rates = false;
            if ( !is_array($tax_rates) ) {
                $tax_rates = [];
                $result_tax_class_id = tep_db_query("select tax_class_id, sum(tax_rate) as rate from " . TABLE_TAX_RATES . " group by tax_class_id, tax_zone_id order by tax_priority");
                if (tep_db_num_rows($result_tax_class_id) > 0) {
                    while ($array_tax_class_id = tep_db_fetch_array($result_tax_class_id)) {
                        $tax_rates[] = $array_tax_class_id;
                    }
                }
            }

            $selected_tax = '';
            if (count($tax_rates)>0) {
                foreach($tax_rates as $array_tax_class_id ) {
                    if ($array_tax_class_id['rate'] == $this->products[$index]['tax']) {
                        $tax_class_id = $array_tax_class_id['tax_class_id'];
                        $selected_tax = \common\helpers\Tax::get_tax_description($tax_class_id, $this->delivery['country_id'] ?? null, $this->delivery['zone_id'] ?? null);
                        $this->products[$index]['tax_description'] = $selected_tax;
                        break;
                    }
                }
            }
            
            $_calc_tax = 0;
            if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                $_calc_tax = \common\helpers\Tax::roundTax($this->products[$index]['final_price'] * $this->products[$index]['qty'] * $this->products[$index]['tax'] / (100+$this->products[$index]['tax']) );
            } else {
                $_calc_tax = \common\helpers\Tax::roundTax($this->products[$index]['final_price'] * $this->products[$index]['qty'] * $this->products[$index]['tax'] / 100);
            }

            if (!isset($tax_groups[$selected_tax])) {
                $tax_groups[$selected_tax] = $_calc_tax;
            } else {
                $tax_groups[$selected_tax] += $_calc_tax;
            }
            $tax += $_calc_tax;

            $this->products[$index]['attributes'] = [];
            $subindex = 0;
            $attributes_query = tep_db_query("select products_options, products_options_values, options_values_price, price_prefix, products_options_id, products_options_values_id from " . $this->table_prefix . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $order_id . "' and orders_products_id = '" . (int) $orders_products['orders_products_id'] . "'");
            if (tep_db_num_rows($attributes_query)) {
                while ($attributes = tep_db_fetch_array($attributes_query)) {
                    $this->products[$index]['attributes'][$subindex] = array('option' => $attributes['products_options'],
                        'value' => $attributes['products_options_values'],
                        'prefix' => $attributes['price_prefix'],
                        'price' => $attributes['options_values_price'],
                        'option_id' => $attributes['products_options_id'],
                        'value_id' => $attributes['products_options_values_id']);

                    $subindex++;
                }
            }
            if (!empty($orders_products['props'])) {
                Yii::$app->get('PropsHelper')::describeOrderProduct($this->products[$index]);
            }

            $index++;
        }
        // {{ group linked
        $this->compactLinkedProducts();
        // }} group linked
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
            if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                $this->info['subtotal'] = round($subtotal, 2);
            } else {
                $this->info['subtotal'] = round($subtotal + $tax, 2);
            }
        } else {
            $this->info['subtotal'] = round($subtotal, 2);
        }
        if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
            $this->info['subtotal_inc_tax'] = round($subtotal, 2);
            $this->info['total'] = round($subtotal, 2);
        } else {
            $this->info['subtotal_inc_tax'] = round($subtotal + $tax, 2);
            $this->info['total'] = round($subtotal + $tax, 2);
        }

        $this->info['subtotal_exc_tax'] = round($subtotal, 2);

        $this->info['total_inc_tax'] = round($total_inc_tax, 2);
        $this->info['total_exc_tax'] = round($total_exc_tax, 2);
        $this->info['tax'] = round($tax, 2);
        $this->info['tax_groups'] = $tax_groups;

        foreach (\common\helpers\Hooks::getList('order/query/after') as $filename) {
            include($filename);
        }
    }

    public function compactLinkedProducts()
    {
        if ($ext = \common\helpers\Extensions::isAllowed('LinkedProducts')) {
            $this->products = $ext::hookCompactLinkedProducts($this->products)??$this->products;
        }
    }


    /**
     * @param string $scope admin_order_detail | packing_slip
     * @return array
     */
    public function getOrderedProducts($scope='')
    {
        $products = $this->products;
        //ProductNameDecorator::instance()->decorateOrder($this);
        if ($pl = \common\helpers\Extensions::isAllowed('LinkedProducts'))
        {
            $products = !empty($pl::hookGetOrderedProducts($scope, $products)) ? $pl::hookGetOrderedProducts($scope, $products) : $this->products;
        }
        switch ($scope){
            case 'packing_slip':
                if (ProductNameDecorator::instance()->useInternalNameForPackingSlip()){
                    $products = ProductNameDecorator::instance()->getUpdatedOrderProducts($products, $this->info['language_id'], $this->info['platform_id']);
                }
                break;
            case 'invoice':
                if (ProductNameDecorator::instance()->useInternalNameForInvoice()){
                    $products = ProductNameDecorator::instance()->getUpdatedOrderProducts($products, $this->info['language_id'], $this->info['platform_id']);
                }
                break;
            case 'admin_order_detail':
                if (ProductNameDecorator::instance()->useInternalNameForOrder()){
                    $products = ProductNameDecorator::instance()->getUpdatedOrderProducts($products, $this->info['language_id'], $this->info['platform_id']);
                }
                break;
        }

        return $products;
    }

    function _billing_address() {
        return false;
    }

    function _shipping_address() {
        return false;
    }

    function change_shipping($new_shipping) {
        if ((get_class($this) == 'common\classes\Order' || get_class($this) == 'common\extensions\Quotations\Quotation') && \frontend\design\Info::isTotallyAdmin()) {
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
                $this->info['shipping_cost_exc_tax'] = (isset($new_shipping['cost_exc_tax'])?$new_shipping['cost_exc_tax']:$new_shipping['cost']);
                $this->info['total'] = $this->info['subtotal'] + $new_shipping['cost'];
                $this->info['total_inc_tax'] = $this->info['subtotal_inc_tax'] + $new_shipping['cost'];
                $this->info['total_exc_tax'] = $this->info['subtotal_exc_tax'] + $new_shipping['cost'];
            }
        }
        return false;
    }

    function cart() {

        $this->prepareOrderInfo();

        $this->prepareOrderAddresses();

        $this->prepareProducts();

        $this->prepareOrderInfoTotals();

        foreach (\common\helpers\Hooks::getList('order/cart/after') as $filename) {
            include($filename);
        }
    }

    public function overWrite($uprid, &$product) {
        $cart = $this->getCart();
        $details = $cart->getOwerwritten($uprid);
        if (is_array($details) && count($details)) {
            foreach ($details as $key => $value) {
                $product[$key] = $value;
            }
        }
    }

    public function save_subscription($subscription_id = 0, $order_id = 0, $i = 0, $uuid = '', $info = '') {
        if($ext = \common\helpers\Acl::checkExtensionAllowed('Subscriptions', 'allowed')){
            return $ext::saveSubscription($this, $subscription_id, $order_id, $i, $uuid, $info);
        }
    }

    public function save_order($order_id = 0) {
        $currencies = \Yii::$container->get('currencies');

        $cart = $this->getCart();

        if (($new_status = $cart->getStatusAfterPaid()) !== false) {
            $this->info['order_status'] = $new_status;
        }

        if($this->info['shipping_class'] === 'no_shipping_no_shipping' ){
            foreach ($this->delivery as $key => $delivery) {
                if(is_array($delivery)) {
                    continue;
                }elseif(is_numeric($delivery)) {
                    $this->delivery[$key] = 0;
                }else{
                    $this->delivery[$key] = '';
                }
            }
        }

// BOF: WebMakers.com Added: Downloads Controller
        $sql_data_array = array(
            'customers_id' => $this->customer['customer_id'] ?? 0,
            'basket_id' => $this->info['basket_id'] ?? 0,
            'customers_name' => ($this->customer['firstname'] ?? '') . ' ' . ($this->customer['lastname'] ?? ''),
            //{{ BEGIN FISTNAME
            'customers_firstname' => $this->customer['firstname'] ?? '',
            'customers_lastname' => $this->customer['lastname'] ?? '',
            //}} END FIRSTNAME
            'customers_company' => $this->customer['company'] ?? '',
            'customers_company_vat' => $this->customer['company_vat'] ?? '',
            'customers_company_vat_status' => $this->customer['company_vat_status'] ?? '',
            'customers_customs_number' => $this->customer['customs_number'] ?? '',
            'customers_customs_number_status' => $this->customer['customs_number_status'] ?? '',
            'customers_street_address' => $this->customer['street_address'] ?? '',
            'customers_suburb' => $this->customer['suburb'] ?? '',
            'customers_city' => $this->customer['city'] ?? '',
            'customers_postcode' => $this->customer['postcode'] ?? '',
            'customers_state' => $this->customer['state'] ?? '',
            'customers_country' => $this->customer['country']['title'] ?? '',
            'customers_telephone' => $this->customer['telephone'] ?? '',
            'customers_landline' => $this->customer['landline'] ?? '',
            'customers_email_address' => $this->customer['email_address'] ?? '',
            'customers_address_format_id' => $this->customer['format_id'] ?? 6,
            'billing_address_book_id' => $this->billing['address_book_id'] ?? 0,
            'billing_gender' => $this->billing['gender'] ?? '',
            'billing_name' => ($this->billing['firstname'] ?? '') . ' ' . ($this->billing['lastname'] ?? ''),
            'billing_firstname' => $this->billing['firstname'] ?? '',
            'billing_lastname' => $this->billing['lastname'] ?? '',
            'billing_telephone' => $this->billing['telephone'] ?? '',
            'billing_email_address' => $this->billing['email_address'] ?? '',
            'billing_street_address' => $this->billing['street_address'] ?? '',
            'billing_suburb' => $this->billing['suburb'] ?? '',
            'billing_city' => $this->billing['city'] ?? '',
            'billing_company' => isset($this->billing['company']) ? $this->billing['company'] : '',
            'billing_company_vat' => $this->billing['company_vat'] ?? '',
            'billing_company_vat_status' => $this->billing['company_vat_status'] ?? '',
            'billing_customs_number' => $this->billing['customs_number'] ?? '',
            'billing_customs_number_status' => $this->billing['customs_number_status'] ?? '',
            'billing_postcode' => $this->billing['postcode'] ?? '',
            'billing_state' => $this->billing['state'] ?? '',
            'billing_country' => isset($this->billing['country']['title']) ? $this->billing['country']['title'] : '',
            'billing_address_format_id' => $this->billing['format_id'] ?? 6,
            'platform_id' => $this->info['platform_id'] ?? 0,
            'department_id' => $this->info['department_id'] ?? 0,
            'payment_method' => strip_tags($this->info['payment_method'] ?? ''),
// BOF: Lango Added for print order mod
            'payment_info' => $this->manager->getPaymentCollection()->getConfirmationTitle(),//['payment_info'],
// EOF: Lango Added for print order mod
            'cc_type' => $this->info['cc_type'] ?? '',
            'cc_owner' => $this->info['cc_owner'] ?? '',
            'cc_number' => $this->info['cc_number'] ?? '',
            'cc_expires' => $this->info['cc_expires'] ?? '',
            'language_id' => $this->info['language_id'], //(int)$languages_id,
            'payment_class' => $this->info['payment_class'] ?? '',
            'shipping_class' => $this->info['shipping_class'] ?? '',
            'shipping_method' => strip_tags($this->info['shipping_method']),
            //'date_purchased' => 'now()',
            'last_modified' => new \yii\db\Expression('now()'),
            /* start search engines statistics */
            'search_engines_id' => isset($_SESSION['search_engines_id']) ? (int) $_SESSION['search_engines_id'] : 0,
            'search_words_id' => isset($_SESSION['search_words_id']) ? (int) $_SESSION['search_words_id'] : 0,
            /* end search engines statistics */
            'orders_status' => $this->info['order_status'] ?? 0,
            'currency' => $this->info['currency'] ?? '',
            'currency_value' => $this->info['currency_value'] ?? '',
            'shipping_weight' => $this->info['shipping_weight'] ?? 0,
            'adjusted' => 0,
            'external_orders_id' => strval($this->info['external_orders_id'] ?? ''),
            'reference_id' => $cart->getReference(),
            'bonus_points_redeem' => (float)($this->info['bonus_points_redeem'] ?? 0),
            'products_price_qty_round' => (defined('PRODUCTS_PRICE_QTY_ROUND') && PRODUCTS_PRICE_QTY_ROUND == 'true'),
            'pointto' => $this->info['pointto'] ?? 0,
            'cash_data_summ' => isset($this->info['cash_data_summ'])?$this->info['cash_data_summ']:0.00,
            'cash_data_change' => isset($this->info['cash_data_change'])?$this->info['cash_data_change']:0.00,
            'card_reference_id' => isset($this->info['card_reference_id'])?$this->info['card_reference_id']:'',
            'admin_id' => (int) $cart->admin_id ?? 0,
            'purchase_order' => $this->info['purchase_order'] ?? '',
        );

        $_delivery = [
            'delivery_address_book_id' => (int)($this->delivery['address_book_id'] ?? 0),
            'delivery_gender' => $this->delivery['gender'] ?? '',
            'delivery_name' => ($this->delivery['firstname'] ?? '') . ' ' . ($this->delivery['lastname'] ?? ''),
            'delivery_firstname' => $this->delivery['firstname'] ?? '',
            'delivery_lastname' => $this->delivery['lastname'] ?? '',
            'delivery_telephone' => $this->delivery['telephone'] ?? '',
            'delivery_email_address' => $this->delivery['email_address'] ?? '',
            'delivery_street_address' => $this->delivery['street_address'] ?? '',
            'delivery_suburb' => $this->delivery['suburb'] ?? '',
            'delivery_city' => $this->delivery['city'] ?? '',
            'delivery_postcode' => $this->delivery['postcode'] ?? '',
            'delivery_state' => $this->delivery['state'] ?? '',
            'delivery_company' => $this->delivery['company'] ?? '',
            'delivery_company_vat' => $this->delivery['company_vat'] ?? '',
            'delivery_company_vat_status' => $this->delivery['company_vat_status'] ?? '',
            'delivery_customs_number' => $this->delivery['customs_number'] ?? '',
            'delivery_customs_number_status' => $this->delivery['customs_number_status'] ?? '',
            'delivery_country' => $this->delivery['country']['title'] ?? '' ,
            'delivery_address_format_id' => $this->delivery['format_id'] ?? 6,
        ];
        if ((is_object($this->manager) && ($this->manager->isDeliveryUsed() || $this->withDelivery ) ) || (!is_object($this->manager))){
            $sql_data_array = array_merge($sql_data_array, $_delivery);
        }

        if ($order_id == 0) {
            if (!empty($this->info['date_purchased']) &&
                ((is_object($this->info['date_purchased']) && $this->info['date_purchased'] instanceof Expression) || preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/',$this->info['date_purchased']) )
            ) {
                $sql_data_array['date_purchased'] = $this->info['date_purchased'];
            }else{
                $sql_data_array['date_purchased'] = 'now()';
            }
        }

        // deprecated: pageArea is used only for .tpl files. Use the 'order/before-save' hook
        foreach (\common\helpers\Hooks::getList('Order', 'save_order/before') as $filename) {
            include($filename);
        }
        foreach (\common\helpers\Hooks::getList('order/before-save') as $filename) {
            include($filename);
        }

        if (tep_session_is_registered('platform_code')) {
            global $platform_code;
            if (!empty($platform_code)) {
                $sql_data_array['platform_id'] = \Yii::$app->get('platform')->config()->getSattelitePlatformId($platform_code);
            }
        }

// EOF: WebMakers.com Added: Downloads Controller
        if (USE_MARKET_PRICES == 'True') {
            $sql_data_array['currency_value_default'] = $currencies->currencies[DEFAULT_CURRENCY]['value'];
        }

        if ($order_id) {
            $this->status = 'update';
            $model = $this->getARModel()->where(['orders_id' => $order_id])->one();
            if (!$model) $model = $this->getARModel(true);
            $model->setAttributes($sql_data_array, false);
        } else {
            $this->status = 'new';
            $model = $this->getARModel(true);
            yii_setup_model($model, $sql_data_array);
        }

        $model->save(false);

        $this->order_id = (int)$model->orders_id;

        if (!empty($model->order_number)) {
          $this->info['order_number'] = $model->order_number;
        }
        if (!empty($model->invoice_number)) {
          $this->info['invoice_number'] = $model->invoice_number;
        }

        // deprecated: pageArea is used only for .tpl files. Use the 'order/after-save' hook
        foreach (\common\helpers\Hooks::getList('Order', 'save_order/after') as $filename) {
            include($filename);
        }
        foreach (\common\helpers\Hooks::getList('order/after-save') as $filename) {
            include($filename);
        }

        return $this->order_id;
    }

    public function get_private_info() {
        $cart = $this->getCart();
        $info = '';
        if (!tep_not_null($this->status))
            return $info;
        $skip = false;
        switch ($this->status) {
            case 'new':
                $info = 'Created';
                break;
            case 'update':
                if ($cart->admin_id) {
                    $skip = true;

                    $info = 'Edited by ';
                    try {
                        $admin = new \backend\models\Admin();
                        if (is_object($admin)) {
                            $info .= $admin->getInfo('admin_firstname') . ' ' . $admin->getInfo('admin_lastname');
                        }
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                    }
                } else {
                    $info = 'Updated';
                }
                break;
            case 'migrated':
                $info = 'Migrated from '. $this->migrated;
                return $info;
                    break;
            default:
                break;
        }
        if ($cart->admin_id) {
            if (!$skip)
                $info .= ' from backend for ';
        } else {
            $info .= ' from store ';
        }
        if (!$skip)
            $info .= platform::name($cart->platform_id);
        return $info;
    }

    public function total_replace($classes, $totals)
    {
        if ( !$this->order_id ) return;
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $this->order_id . "' AND class IN ('".implode("','", $classes)."')");
        foreach ($totals as $total){
            $row_class = (isset($total['class']) ? $total['class'] : $total['code']);
            if ( !in_array($row_class, $classes) ) continue;
            $sql_data_array = array(
                'orders_id' => $this->order_id,
                'title' => $total['title'],
                'text' => $total['text'],
                'value' => $total['value'],
                'class' => $row_class,
                'sort_order' => $this->manager->getTotalCollection()->get($total['code'])->sort_order,
                'text_exc_tax' => $total['text_exc_tax'],
                'text_inc_tax' => $total['text_inc_tax'],
                'tax_class_id' => $total['tax_class_id'],
                'value_exc_vat' => $total['value_exc_vat'],
                'value_inc_tax' => $total['value_inc_tax'],
                'is_removed' => 0,
                'currency' => $this->info['currency'],
                'currency_value' => $this->info['currency_value'],
            );
            tep_db_perform($this->table_prefix . TABLE_ORDERS_TOTAL, $sql_data_array);
        }
    }

    public function save_totals(){
        $this->totals = $this->manager->getTotalOutput(false);
        if ($this->totals){
            tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $this->order_id . "'");
        }

        if (is_array($this->totals)){
            foreach($this->totals as $total){
                $sql_data_array = array(
                    'orders_id' => $this->order_id,
                    'title' => $total['title'],
                    'text' => $total['text'],
                    'value' => $total['value'],
                    'class' => (isset($total['class']) ? $total['class'] : $total['code']),
                    'sort_order' => (isset($total['sort_order']) ? $total['sort_order'] : $this->manager->getTotalCollection()->get($total['code'])->sort_order),
                    'text_exc_tax' => $total['text_exc_tax'],
                    'text_inc_tax' => $total['text_inc_tax'],
                    'tax_class_id' => $total['tax_class_id'],
                    'value_exc_vat' => $total['value_exc_vat'],
                    'value_inc_tax' => $total['value_inc_tax'],
                    'is_removed' => 0,
                    'currency' => $this->info['currency'],
                    'currency_value' => $this->info['currency_value'],
                );
                tep_db_perform($this->table_prefix . TABLE_ORDERS_TOTAL, $sql_data_array);
                if (isset($total['adjusted']) && $total['adjusted']) {
                    tep_db_query("update " . $this->table_prefix . TABLE_ORDERS . " set adjusted ='1' where orders_id = '" . (int) $this->order_id . "'");
                }
            }
        }
    }

    protected function saveCartExtensions(){
        $cart = $this->getCart();
        if (is_object($cart)){
            $removed = $cart->getHiddenModules();
            if (count($removed)) {
                for ($i = 0, $n = sizeof($removed); $i < $n; $i++) {
                    $sql_data_array = array(
                        'orders_id' => $this->order_id,
                        'title' => '',
                        'text' => '',
                        'value' => 0,
                        'class' => $removed[$i],
                        'sort_order' => 0,
                        'text_exc_tax' => '',
                        'text_inc_tax' => '',
                        'tax_class_id' => 0,
                        'value_exc_vat' => 0,
                        'value_inc_tax' => 0,
                        'is_removed' => 1,
                        'currency' => $this->info['currency'],
                        'currency_value' => $this->info['currency_value'],
                    );
                    tep_db_perform($this->table_prefix . TABLE_ORDERS_TOTAL, $sql_data_array);
                }
            }
        }
    }

    public function save_details($notified = true) {

        $cart = $this->getCart();
        if (!$this->order_id)
            return false;
        //if(!\frontend\design\Info::isTotallyAdmin()) tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_HISTORY . " where orders_id = '" . (int)$this->order_id . "'");

        $customer_notification = (SEND_EMAILS == 'true' && $notified) ? '1' : '0';

        if (is_object($cart)){
            if (($paid_info = $cart->getPaidInfo()) !== false) {
                if (is_array($paid_info['info'])) {
                    foreach ($paid_info['info'] as $pi) {
                        $sql_data_array = array(
                            'orders_id' => $this->order_id,
                            'orders_status_id' => ($paid_info['status'] ? $paid_info['status'] : $this->info['order_status']),
                            'comments' => $pi['comment'],
                            'customer_notified' => $customer_notification,
                            'admin_id' => (int) $cart->admin_id,
                            'date_added' => 'now()'
                        );
                        tep_db_perform($this->table_prefix . TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
                    }
                }
            } else {
                $this->addLegend($this->get_private_info(), $cart->admin_id);
            }
        } else {
            $this->addLegend($this->get_private_info(), 0);
        }

        $this->save_totals();

        $this->saveCartExtensions();

        //if(!\frontend\design\Info::isTotallyAdmin()) tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$this->order_id . "'");

        $sql_data_array = array('orders_id' => $this->order_id,
            'orders_status_id' => $this->info['order_status'],
            'date_added' => 'now()',
            'customer_notified' => $customer_notification,
            'comments' => $this->info['comments'],
            'admin_id' => (int) $cart->admin_id,
        );
        tep_db_perform($this->table_prefix . TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        $ref_id = $cart->getReference();
        if (!is_null($ref_id)) {
            $this->addLegend(TEXT_REORDER_FROM . $ref_id, $cart->admin_id);

            foreach ($this->collectAdminComments($ref_id) as $_comment) {
                $sql_data_array = array(
                    'orders_id' => $this->order_id,
                    'orders_status_id' => $this->info['order_status'],
                    'comments' => TEXT_COMMENT_FROM_REORDERED . "\n" . $_comment['comments'],
                    'customer_notified' => 0,
                    'admin_id' => (int) $cart->admin_id,
                    'date_added' => 'now()'
                );
                tep_db_perform($this->table_prefix . TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
            }
        }


        try {
            \Yii::$container->get('eventDispatcher')->dispatch(new OrderSaveEvent($this));
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }

        $this->processAdditionalShippingParams($ref_id);

        return;
    }

    public function getStatusHistory()
    {
        $status_history = [];
        if ( $this->order_id ) {
            $get_history_r = tep_db_query(
                "SELECT * ".
                "FROM " . $this->table_prefix . TABLE_ORDERS_STATUS_HISTORY . " ".
                "WHERE orders_id = '".$this->order_id."' ".
                "ORDER BY date_added, orders_status_history_id"
            );
            if ( tep_db_num_rows($get_history_r)>0 ) {
                while( $_history = tep_db_fetch_array($get_history_r) ) {
                    $_history['orders_status_name'] = \common\helpers\Order::get_order_status_name($_history['orders_status_id'], $this->info['language_id']);
                    $status_history[] = $_history;
                }
            }
        }

        return $status_history;
    }

    private function processAdditionalShippingParams(?int $refId = null){

    	if( isset($this->info['shipping_class'])){
            $moduleName = reset(explode('_', $this->info['shipping_class']));
            if(!$moduleName) {
                return;
            }
            $module = $this->manager->getShippingCollection()->get($moduleName);
            if ($refId > 0 && $refId !== $this->order_id && method_exists($module, 'reorderShippingData')) {
                $module->reorderShippingData($this->order_id, $refId, $this->table_prefix);
            }
            if(!empty($module) && method_exists($module, 'setAdditionalOrderParams')){
                $params = [];
                if ($this->manager->has('shippingparam')){
                    $value = $this->manager->get('shippingparam');
                    if (isset($value[$moduleName])){
                        $params = $value[$moduleName];
                    }
                }
                $module->setAdditionalOrderParams($this->order_id, $params);
            }

        }

    }

    public function addAdminComment($comment, $adminId=null)
    {
        $this->addLegend($comment, $adminId);
    }

    public function collectAdminComments($ref_id) {
        $cart = $this->getCart();
        $comments = [];
        $query = tep_db_query("select * from " . $this->table_prefix . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = {$ref_id} and admin_id > 0");
        if (tep_db_num_rows($query)) {
            $tracking = \common\helpers\Translation::getTranslationValue('TEXT_TRACKING_NUMBER', 'admin/orders', $cart->language_id);
            while ($row = tep_db_fetch_array($query)) {
                if (empty($row['comments']))
                    continue;
                if ($tracking && strpos($row['comments'], $tracking))
                    continue;
                $comments[] = $row;
            }
        }
        return $comments;
    }

    public function clear_products() {
        \common\helpers\Order::restock($this->order_id);
        //tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $this->order_id . "'");do not delete, may has product status
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $this->order_id . "'");
    }

    public function searchOrderProduct(array $array_data){
        if (isset($array_data['template_uprid']) && !empty($array_data['template_uprid'])){
            $opQuery = $this->getProductsARModel()->where(['orders_id' => $this->order_id, 'template_uprid' => (string)($array_data['template_uprid'] ?? ''), 'is_giveaway' => (int)($array_data['is_giveaway'] ?? 0)]);
        } else if (isset($array_data['uprid']) && !empty($array_data['uprid'])){
            $opQuery = $this->getProductsARModel()->where(['orders_id' => $this->order_id, 'uprid' => (string)($array_data['uprid'] ?? ''), 'is_giveaway' => (int)($array_data['is_giveaway'] ?? 0)]);
        } else if (isset($array_data['products_id']) && !empty($array_data['products_id'])){
            $opQuery = $this->getProductsARModel()->where(['orders_id' => $this->order_id, 'products_id' => $array_data['products_id'], 'is_giveaway' => (int)($array_data['is_giveaway'] ?? 0)]);
        }
        if (is_object($opQuery)){
            $oProduct = $opQuery->one();
            if ($oProduct){
                return $oProduct->orders_products_id;
            }
        }
        return false;
    }

    protected function removeHusk(array $leave){
        if ($this->order_id){
            foreach($this->getProductsARModel()->where(['and', ['not in', 'orders_products_id', $leave], ['orders_id' => $this->order_id]])->all() as $product)
                    $product->delete();
        }
    }

    public function save_products($notify = true) {
        global $languages_id;

        $currencies = \Yii::$container->get('currencies');

        $cart = $this->getCart();

        $products_ordered = '';

        $stock_update_flag = false;

        $this->clear_products();
        $container = \Yii::$container->get('products');
        $normilizeSP = false;
        if ($extSP = \common\helpers\Extensions::isAllowed('SupplierPurchase')){
            $normilizeSP = true;
        }

        if  ($pl = \common\helpers\Extensions::isAllowed('LinkedProducts')) {
            $this->products = $pl::expandLinkedProducts($this->products)??$this->products;
        }

        $dontUpdateStock = false;
        $sPayment = $this->manager->getPaymentCollection()->getSelectedPayment();
        $dontUpdateStock = (isset($sPayment->dont_update_stock) ? $sPayment->dont_update_stock: $dontUpdateStock);
        $opInOrder = [];
        for ($i = 0, $n = sizeof($this->products); $i < $n; $i++) {
            $stock_products_id = \common\helpers\Inventory::normalizeInventoryId($this->products[$i]['id']);
            if (defined('TEMPORARY_STOCK_ENABLE') && TEMPORARY_STOCK_ENABLE == 'true' && $this->table_prefix != 'tmp_') {
                \common\helpers\Warehouses::remove_customers_temporary_stock_quantity(strlen($this->products[$i]['template_uprid']) > 0 ? $this->products[$i]['template_uprid'] : $this->products[$i]['id']);
            }
            if ((STOCK_LIMITED == 'true' && !$dontUpdateStock && $this->table_prefix != 'purchase_') || \common\helpers\Order::is_stock_updated(intval($this->order_id))) {
                global $login_id;
                \common\helpers\Warehouses::update_stock_of_order($this->order_id, (strlen($this->products[$i]['template_uprid']) > 0 ? $this->products[$i]['template_uprid'] : $this->products[$i]['id']), $this->products[$i]['qty'],0, 0, $this->info['platform_id']);
                $stock_update_flag = true;
            }

// Update products_ordered (for bestsellers list)
            tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $this->products[$i]['qty']) . " where products_id = '" . \common\helpers\Inventory::get_prid($this->products[$i]['id']) . "'");
            $sql_data_array = array('orders_id' => $this->order_id,
                'products_id' => \common\helpers\Inventory::get_prid($stock_products_id),
                'products_model' => $this->products[$i]['model'],
                'products_name' => $this->products[$i]['name'],
                'products_price' => $this->products[$i]['price'],
                'final_price' => $this->products[$i]['final_price'],
                'products_tax' => $this->products[$i]['tax'],
                'products_quantity' => $this->products[$i]['qty'],
                'props' => $this->products[$i]['props'],
                'is_giveaway' => $this->products[$i]['ga'],
                'is_virtual' => $this->products[$i]['is_virtual'],
                'gift_wrap_price' => $this->products[$i]['gift_wrap_price'],
                'gift_wrapped' => $this->products[$i]['gift_wrapped'] ? 1 : 0,
                'gv_state' => $this->products[$i]['gv_state'],
                'uprid' => ($normilizeSP ? $extSP::normalize_id($this->products[$i]['id']): $stock_products_id),
                /* PC configurator addon begin */
                'template_uprid' => $this->products[$i]['template_uprid'],
                'parent_product' => $this->products[$i]['parent_product'],
                'sub_products' => (is_array($this->products[$i]['sub_products']) ? implode(',', $this->products[$i]['sub_products']) : ''),
                'relation_type' => (!empty($this->products[$i]['relation_type']) ? $this->products[$i]['relation_type'] : ''),
                'bonus_points_cost' => $this->products[$i]['bonus_points_cost'] ?? null,
                /* PC configurator addon end */
                'sort_order' => $this->products[$i]['sort_order'],
                'products_weight' => $this->products[$i]['weight'],
                'promo_id' => $this->products[$i]['promo_id'],
                'specials_id' => $this->products[$i]['specials_id'],
                'discount_description' => \common\helpers\Specials::getDescription($this->products[$i]['specials_id']),
                'overwritten' => serialize($this->products[$i]['overwritten'] ?? null));

            if (is_object($cart) && $cart->in_cart($this->products[$i]['id'])) { // for edit order, after saving to know how match was saved
                $cart->contents[$this->products[$i]['id']]['reserved_qty'] = $this->products[$i]['qty'];
                $this->products[$i]['reserved_qty'] = $this->products[$i]['qty'];
            }

            if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                $sql_data_array = array_merge($ext::saveProductsOrderFrontend($this->products, $i), $sql_data_array);
            }

            $cProduct = $container->getProduct($this->products[$i]['id']);
            if ($cProduct){
                if (isset($cProduct['promo_id']) && $cProduct['promo_id']){
                    $sql_data_array['promo_id'] = $cProduct['promo_id'];
                }
            }

            //import PO in received status - no stock updates required.
            if (!empty($this->products[$i]['qty_rcvd'])) {
              $sql_data_array['qty_rcvd'] = $this->products[$i]['qty_rcvd'];
            }


            $order_products_id = $this->searchOrderProduct($sql_data_array);
            if ($order_products_id){
              //import PO in received status - no stock updates required.
                if (!empty($this->products[$i]['orders_products_status'])) {
                  $sql_data_array['orders_products_status'] = $this->products[$i]['orders_products_status'];
                }
                tep_db_perform($this->table_prefix . TABLE_ORDERS_PRODUCTS, $sql_data_array, 'update', "orders_products_id = {$order_products_id}");
            } else {
              //import PO in received status - no stock updates required.
              if (empty($sql_data_array['orders_products_status'])) {
                $sql_data_array['orders_products_status'] = \common\helpers\OrderProduct::OPS_QUOTED;
              }
                tep_db_perform($this->table_prefix . TABLE_ORDERS_PRODUCTS, $sql_data_array);
                $order_products_id = tep_db_insert_id();

                if ($ext = \common\helpers\Acl::checkExtensionAllowed('Rma', 'allowed')) {
                    $ext::setOrderProductReturnDate($order_products_id);
                }
            }
            $opInOrder[] = $order_products_id;

            $this->manager->getTotalCollection()->update_credit_account($i); //ICW ADDED FOR CREDIT CLASS SYSTEM

            //------insert customer choosen option to order--------
            $attributes_exist = '0';
            $products_ordered_attributes = [];

            if ((DOWNLOAD_ENABLED == 'true') && tep_not_null($this->products[$i]['products_file'] ?? null)) {
                $sql_data_array = array('orders_id' => $this->order_id,
                    'orders_products_id' => $order_products_id,
                    'orders_products_name' => $this->products[$i]['name'],
                    'orders_products_filename' => $this->products[$i]['products_file'],
                    'download_maxdays' => DOWNLOAD_MAX_DAYS,
                    'download_count' => DOWNLOAD_MAX_COUNT);
                tep_db_perform($this->table_prefix . TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
            }

            // {{ Products Bundle Sets
            $sets_array = array();
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                list($sets_array, $sets_products_ordered_attributes) = $ext::cartOrderProducts($this->products[$i], $this->order_id, $order_products_id);
                //$products_ordered_attributes .= $sets_products_ordered_attributes;
            }
            // }}
            // {{ Virtual Gift Card
            if (($this->products[$i]['virtual_gift_card'] ?? null) && $this->products[$i]['attributes'][0]['value_id'] > 0) {
                $virtual_gift_card_code = \common\helpers\Gifts::virtual_gift_card_process($this->products[$i]['attributes'][0]['value_id'], $this->customer['email_address'], $this->customer['firstname'] . ' ' . $this->customer['lastname']);
                if (tep_not_null($virtual_gift_card_code)) {
                    $sql_data_array = array('orders_id' => $this->order_id,
                        'orders_products_id' => $order_products_id,
                        'products_options_id' => 0,
                        'products_options_values_id' => $this->products[$i]['attributes'][0]['value_id'],
                        'products_options' => TEXT_GIFT_CARD_CODE,
                        'products_options_values' => $virtual_gift_card_code);
                    tep_db_perform($this->table_prefix . TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);
                }
            } else
            // }}
            if (isset($this->products[$i]['attributes'])) {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($this->products[$i]['attributes']); $j < $n2; $j++) {
                    // {{ Products Bundle Sets
                    if ($this->products[$i]['attributes'][$j]['option_id'] == 0 && $this->products[$i]['attributes'][$j]['value_id'] == 0)
                        continue;
                    // }}
                    if ($this->products[$i]['attributes'][$j]['option_id'] == -2) {
                        $attributes_values = array(
                            'products_options_name' => $this->products[$i]['attributes'][$j]['option'],
                            'products_options_values_name' => $this->products[$i]['attributes'][$j]['value'],
                        );
                    } else
                    if (DOWNLOAD_ENABLED == 'true') {
                        $attributes_query = "select pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_maxdays, pa.products_attributes_maxcount , pa.products_attributes_filename
                                   from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                   where pa.products_id = '" . $this->products[$i]['id'] . "'
                                    and pa.options_id = '" . $this->products[$i]['attributes'][$j]['option_id'] . "'
                                    and pa.options_id = popt.products_options_id
                                    and pa.options_values_id = '" . $this->products[$i]['attributes'][$j]['value_id'] . "'
                                    and pa.options_values_id = poval.products_options_values_id
                                    and popt.language_id = '" . $languages_id . "'
                                    and poval.language_id = '" . $languages_id . "'";
                        $attributes = tep_db_query($attributes_query);
                        $attributes_values = tep_db_fetch_array($attributes);
                    } else {
                        $attributes = tep_db_query("select pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $this->products[$i]['id'] . "' and pa.options_id = '" . $this->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $this->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
                        $attributes_values = tep_db_fetch_array($attributes);
                    }

                    $attributes_values['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attributes_values['products_attributes_id']);
                    $sql_data_array = array('orders_id' => $this->order_id,
                        'orders_products_id' => $order_products_id,
                        'products_options' => $attributes_values['products_options_name'],
                        'products_options_values' => $this->products[$i]['attributes'][$j]['value'],
                        'options_values_price' => $attributes_values['options_values_price'],
                        'price_prefix' => $attributes_values['price_prefix'],
                        'products_options_id' => $this->products[$i]['attributes'][$j]['option_id'],
                        'products_options_values_id' => $this->products[$i]['attributes'][$j]['value_id']);
                    tep_db_perform($this->table_prefix . TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                        $sql_data_array = array('orders_id' => $this->order_id,
                            'orders_products_id' => $order_products_id,
                            'orders_products_name' => $this->products[$i]['name'],
                            'orders_products_filename' => $attributes_values['products_attributes_filename'],
                            'download_maxdays' => ($attributes_values['products_attributes_maxdays'] ? $attributes_values['products_attributes_maxdays'] : DOWNLOAD_MAX_DAYS),
                            'download_count' => ($attributes_values['products_attributes_maxcount'] ? $attributes_values['products_attributes_maxcount'] : DOWNLOAD_MAX_COUNT));
                        tep_db_perform($this->table_prefix . TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                    }
                    $products_ordered_attributes[] = htmlspecialchars($attributes_values['products_options_name']) . ': ' . htmlspecialchars($this->products[$i]['attributes'][$j]['value']);
                }
            }

            // {{ Products Bundle Sets
            if (count($sets_array) > 0) {
                tep_db_query("update " . $this->table_prefix . TABLE_ORDERS_PRODUCTS . " set sets_array = '" . tep_db_input(serialize($sets_array)) . "' where orders_products_id = '" . (int) $order_products_id . "'");
            }
            // }}
            /** @var \common\extensions\Quotations\Quotations $ext */
            //$products_ordered .= $this->products[$i]['qty'] . ' x ' . $this->products[$i]['name'] . (($this->products[$i]['model'] != '') ? ' (' . $this->products[$i]['model'] . ')' : '') . ' = ' . $currencies->display_price($this->products[$i]['final_price'], $this->products[$i]['tax'], $this->products[$i]['qty']) . $products_ordered_attributes . "\n";
            $this->products[$i]['tpl_attributes'] = ($products_ordered_attributes ? implode("\n\t", $products_ordered_attributes) :'');
            if (($this->table_prefix != 'quote_' ||  ( ($ext = \common\helpers\Extensions::isAllowed('Quotations')) && $ext::optionIsPriceShow() )) && !(defined('GROUPS_IS_SHOW_PRICE') && GROUPS_IS_SHOW_PRICE==false)) {
                $this->products[$i]['tpl_price'] = $currencies->display_price($this->products[$i]['final_price'], $this->products[$i]['tax'], $this->products[$i]['qty']);
            }

            /*if ($this->table_prefix == '' AND $order_products_id > 0) {
                \common\helpers\OrderProduct::doAllocateAutomatic($order_products_id, true);
            }*/
        }
        try {
            \Yii::$container->get('eventDispatcher')->dispatch(new ProductsSaveToOrderEvent($this));
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }
        $this->removeHusk($opInOrder);
        if ($stock_update_flag) {
            tep_db_query("UPDATE " . $this->table_prefix . TABLE_ORDERS . " SET stock_updated=1 WHERE orders_id='" . intval($this->order_id) . "'");
        }

        $cart->emptyReference();

        $this->compactLinkedProducts();

        if ($notify) {
            $products_ordered = \frontend\design\boxes\email\OrderProducts::widget(['params' => ['products' => $this->products, 'platform_id' => $this->info['platform_id'], 'order' => $this]]);
            $this->notify_customer($products_ordered);
        }

        if ($this->table_prefix == '' AND $this->order_id > 0) {
            \common\helpers\Order::doRefresh($this->order_id);
        }
    }

    protected function getHistoryLink(){
        if (\frontend\design\Info::isTotallyAdmin()) {
            $TEXT_VIEW = \common\helpers\Translation::getTranslationValue('IMAGE_VIEW', 'admin/main', $this->info['language_id']);
            $link = \common\helpers\Output::get_clickable_link(tep_catalog_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->order_id, 'SSL', false), $TEXT_VIEW);
        } else {
            $link = \common\helpers\Output::get_clickable_link(tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $this->order_id, 'SSL', false));
        }
        return $link;
    }

    public function notify_customer($products_ordered,$emailParams = [], $emailTemplate = '') {

        $ostatus = tep_db_fetch_array(tep_db_query("select orders_status_template_confirm, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $this->info['language_id'] . "' and orders_status_id='" . (int) $this->info['order_status'] . "' LIMIT 1 "));
        if (empty($emailTemplate)){
            if (!empty($ostatus['orders_status_template_confirm'])) {
                $get_template_r = tep_db_query("select * from " . TABLE_EMAIL_TEMPLATES . " where email_templates_key='" . tep_db_input($ostatus['orders_status_template_confirm']) . "'");
                if (tep_db_num_rows($get_template_r) > 0) {
                    $emailTemplate = $ostatus['orders_status_template_confirm'];
                }
            }
        }
        $email_params = array();
        $email_params['NEW_ORDER_STATUS'] = $ostatus['orders_status_name'] ?? '';

        $showLink = true;
        $customer = $this->manager->getCustomersIdentity();
        if($customer && $customer->opc_temp_account) {
            $showLink = false;
        }

        $email_params['STORE_NAME'] = STORE_NAME;
        if (\frontend\design\Info::isTotallyAdmin()) {
            $email_params['STORE_URL'] = rtrim(tep_catalog_href_link('/', '', 'SSL', false),'/').'/';
        } else {
            $email_params['STORE_URL'] = rtrim(tep_href_link('/', '', 'SSL', false),'/').'/';
        }
        $email_params['ORDER_NUMBER'] = method_exists($this, 'getOrderNumber')?$this->getOrderNumber():$this->order_id;
        $email_params['ORDER_DATE_SHORT'] = strftime(DATE_FORMAT_SHORT);

        if($showLink){
            $email_params['ORDER_INVOICE_URL'] = $this->getHistoryLink();
        }else{
            $email_params['ORDER_INVOICE_URL'] = LINK_SEND_ADMINSTRATOR;
            if (\frontend\design\Info::isTotallyAdmin()) {
                $email_params['ORDER_INVOICE_URL'] = \common\helpers\Translation::getTranslationValue('LINK_SEND_ADMINSTRATOR', 'admin/main', $this->info['language_id']);
            }
        }

        $email_params['ORDER_DATE_LONG'] = strftime(DATE_FORMAT_LONG);
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('DelayedDespatch', 'allowed')) {
            $email_params['ORDER_DATE_LONG'] .= $ext::mailInfo($this->info['delivery_date']);
        }
        $email_params['PRODUCTS_ORDERED'] = $products_ordered;

        $email_params['ORDER_TOTALS'] = $this->getOrderTotalsHtmlForEmail();

        $email_params['BILLING_ADDRESS'] = '';
        if ($this->manager->isChargedOrder()){
            if( $this->manager->get('billto') && is_int($this->manager->get('billto')) ){
                $email_params['BILLING_ADDRESS'] = \common\helpers\Address::address_label($customer->customers_id, $this->manager->get('billto'), 0, '', "<br>");
            }elseif(!empty($this->billing['format_id'])){
                $email_params['BILLING_ADDRESS'] = \common\helpers\Address::address_format($this->billing['format_id'],$this->billing,0, '', "<br>");
            }
        }

        !empty($this->delivery['format_id']) && $this->withDelivery = true;

        $email_params['DELIVERY_ADDRESS'] = '';
        $arr_shipping_class = explode('_', $this->info['shipping_class']);
        $class = $arr_shipping_class[0] ?? null;
        $method = $arr_shipping_class[1] ?? null;
        $collect = false;
        $shipping = $this->manager->getShippingCollection()->get($class);
        if (is_object($shipping)) {
            $collect = $shipping->toCollect($method);
        }
        if ($collect && method_exists($shipping, 'getAdditionalOrderParams')) {
            $email_params['DELIVERY_ADDRESS'] = $shipping->getAdditionalOrderParams([], $this->order_id, $this->table_prefix);
        }elseif( $this->manager->isDeliveryUsed() || $this->withDelivery || !$this->manager->isChargedOrder()){
            if($this->manager->get('sendto') && is_int($this->manager->get('sendto'))){
                $email_params['DELIVERY_ADDRESS'] = \common\helpers\Address::address_label($customer->customers_id, $this->manager->get('sendto'), 0, '', "<br>");
            }elseif(!empty($this->delivery['format_id'])){
                $email_params['DELIVERY_ADDRESS'] = \common\helpers\Address::address_format($this->delivery['format_id'],$this->delivery,0, '', "<br>");
            }
        } else {
            $email_params['DELIVERY_ADDRESS'] = TEXT_WITHOUT_SHIPPING_ADDRESS;
        }
        $footer = '';
        $email_params['PAYMENT_METHOD'] = '';

        if ($this->manager->isChargedOrder()){
            $selectedPayment = $this->manager->getPaymentCollection()->getSelectedPayment();
            if ($selectedPayment){
                if (isset($selectedPayment->email_footer) && !empty($selectedPayment->email_footer)) {
                    $footer = "\n\n" . $selectedPayment->email_footer;
                }
            }
            $email_params['PAYMENT_METHOD'] = $this->info['payment_method'] . \common\helpers\Order::getPurchaseOrderId($this) . $footer;//$payment_method;
        }

        $email_params['SHIPPING_METHOD'] = $this->info['shipping_method'];

        $email_params['CUSTOMER_FIRSTNAME'] = $this->customer['firstname'];
        $email_params['CUSTOMER_LASTNAME'] = $this->customer['lastname'];
        $email_params['CUSTOMERS_TELEPHONE'] = $this->customer['telephone'];
        $email_params['CUSTOMERS_EMAIL_ADDRESS'] = $this->customer['email_address'];

        $email_params['ORDER_COMMENTS'] = tep_db_output($this->info['comments']);

        // deprecated: pageArea is used only for .tpl files. Use the 'order/notify_customer' hook
        foreach (\common\helpers\Hooks::getList('Order', 'notify_customer') as $filename) {
            include($filename);
        }
        foreach (\common\helpers\Hooks::getList('order/notify_customer') as $filename) {
            include($filename);
        }

        $emailDesignTemplate = '';
        if ($this->info['order_status'] && $this->info['platform_id']) {
            $emailDesignTemplate = \common\models\OrdersStatusToDesignTemplate::findOne([
                'orders_status_id' => $this->info['order_status'],
                'platform_id' => $this->info['platform_id'],
            ])->email_design_template ?? '';
        }

        if(is_array($emailParams) && count($emailParams) > 0 ) {
            $email_params = array_merge($email_params,$emailParams);
        }

        $customer_notified = 0;
        $platform_config = \Yii::$app->get('platform')->config($this->info['platform_id']);
        if (!empty($emailTemplate)) {
            list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template($emailTemplate, $email_params, (int)$this->info['language_id'], $this->info['platform_id'], -1, $emailDesignTemplate);

            $attachment = false;
            if (defined('ATTACH_PDF_INVOICE') && ATTACH_PDF_INVOICE =='True' && in_array($this->info['order_status'], \common\helpers\Order::extractStatuses(ATTACH_PDF_STATUS) ) ) {
              $attachment[] = ['name' => str_replace(' ', '_', TEXT_INVOICE) . $this->getOrderId() . '.pdf',
                              'file' => $this->getPdfInvoice()];
            }
            if ($this->customer['email_address']){
                \common\helpers\Mail::send(
                    $this->customer['firstname'] . ' ' . $this->customer['lastname'], $this->customer['email_address'], $email_subject, $email_text, $platform_config->const_value('STORE_OWNER'), $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS'), [], '', $attachment, ['add_br' => 'no', 'platform_id' => $this->info['platform_id']]
                );
            }

            if (SEND_EXTRA_ORDER_EMAILS_TO == '') {
                \common\helpers\Mail::send($platform_config->const_value('STORE_OWNER'), $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS'), $email_subject, $email_text, $platform_config->const_value('STORE_OWNER'), $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS'), [], '', $attachment, ['add_br' => 'no', 'platform_id' => $this->info['platform_id']]);
            } else {
                \common\helpers\Mail::send($platform_config->const_value('STORE_OWNER'), $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS'), $email_subject, $email_text, $platform_config->const_value('STORE_OWNER'), $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS'), array(), 'CC: ' . SEND_EXTRA_ORDER_EMAILS_TO, $attachment, ['add_br' => 'no', 'platform_id' => $this->info['platform_id']]);
            }
            $customer_notified = 1;
        }

        $this->notify_customer_sms($email_params);

        return $customer_notified;
    }

    public function notify_customer_sms($parameterArray = [])
    {
        if (defined('PLATFORM_SMS_SERVICE') AND (PLATFORM_SMS_SERVICE != '')) {
            $orderStatusRecord = \common\models\OrdersStatus::findOne(['orders_status_id' => (int)$this->info['order_status'], 'language_id' => (int)$this->info['language_id']]);
            if (is_object($orderStatusRecord) AND trim($orderStatusRecord->orders_status_template_sms) != '') {
                if ($smsService = \common\helpers\Acl::checkExtensionAllowed('SmsService', 'allowed')) {
                    $parameterArray = (is_array($parameterArray) ? $parameterArray : array());
                    $smsMessage = \common\helpers\Mail::get_sms_template_parsed(trim($orderStatusRecord->orders_status_template_sms), $parameterArray, (int)$this->info['language_id'], (int)$this->info['platform_id'], -1);
                    $parameterArray = array(
                        'phone' => trim($this->billing['telephone']),
                        'message' => $smsMessage,
                        'sender' => null
                    );
                    return $smsService::sendSms(PLATFORM_SMS_SERVICE, $parameterArray);
                }
            }
        }
        return false;
    }

    public function send_status_notify($notify_comments='', $extra_template_params=[], $email_headers=[])
    {
        $customer_notified = 0;

        $status = $this->info['order_status'];
        $languages_id = $this->info['language_id'];
        $platform_id = $this->info['platform_id'];

        $emailTemplate = '';
        $ostatus = tep_db_fetch_array(tep_db_query("select orders_status_template from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "' and orders_status_id='" . (int) $status . "'"));
        if (!empty($ostatus['orders_status_template'])) {
            $get_template_r = tep_db_query("select * from " . TABLE_EMAIL_TEMPLATES . " where email_templates_key='" . tep_db_input($ostatus['orders_status_template']) . "'");
            if (tep_db_num_rows($get_template_r) > 0) {
                $emailTemplate = $ostatus['orders_status_template'];
            }
        }

        if(!empty($emailTemplate)) {

            $emailDesignTemplate = '';
            if ($status && $platform_id) {
                $emailDesignTemplate = \common\models\OrdersStatusToDesignTemplate::findOne([
                    'orders_status_id' => $status,
                    'platform_id' => $platform_id,
                ])->email_design_template ?? null;
            }

            $_keep_platform_id = \Yii::$app->get('platform')->config()->getId();
            $platform_config = \Yii::$app->get('platform')->config($platform_id);
            $_keep_currency = \Yii::$app->settings->get('currency');
            \Yii::$app->settings->set('currency', $this->info['currency']);

            $email_params = \common\helpers\Mail::emailParamsFromOrder($this);
            $email_params['ORDER_INVOICE_URL'] = $this->getHistoryLink();
            if ( is_array($extra_template_params) ){
                $email_params = array_merge($email_params, $extra_template_params);
            }

            $email_params['ORDER_COMMENTS'] = str_replace(array("\r\n", "\n", "\r"), '<br>', $notify_comments);

            $email_params['AMOUNT_REFUNDED'] = '';
            if (!empty($this->info['total_refund_inc_tax']) && $this->info['total_refund_inc_tax']>0.01) {
                $tmp = ArrayHelper::map($this->totals, 'class', 'text_inc_tax');
                if (!empty($tmp['ot_refund'])) {
                    $email_params['AMOUNT_REFUNDED'] = sprintf(AMOUNT_REFUNDED, $tmp['ot_refund']);
                }
            }
            list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template($emailTemplate, $email_params, $languages_id, $platform_id, -1, $emailDesignTemplate);

            $attachment = false;

            if (defined('ATTACH_PDF_INVOICE') && ATTACH_PDF_INVOICE == 'True' && in_array($status, \common\helpers\Order::extractStatuses(ATTACH_PDF_STATUS))) {
                $attachment[] = ['name' => str_replace(' ', '_', TEXT_INVOICE) . $this->getOrderId() . '.pdf',
                    'file' => $this->getPdfInvoice()];
            }

            \common\helpers\Mail::send(
                $this->customer['name'], $this->customer['email_address'],
                $email_subject, $email_text,
                $platform_config->const_value('STORE_OWNER'), $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS'),
                [], $email_headers, $attachment, ['add_br' => 'no', 'platform_id' => $platform_id]
            );
            $customer_notified = 1;

            \Yii::$app->settings->set('currency', $_keep_currency);
            \Yii::$app->get('platform')->config($_keep_platform_id);
        }
        return $customer_notified;
    }

    protected function getEmailContext(){
        return 'TEXT_EMAIL';
    }

    public function haveSubscription() {
        if (is_array($this->products)) {
            foreach ($this->products as $value) {
                if ($value['subscription']??null == 1) {
                    return true;
                }
            }
        }
        return false;
    }

    public function stockAllowCheckout() {
        $checkout_allowed = true;
        if (STOCK_CHECK == 'true') {
            foreach ($this->products as $ordered_product) {
                if (!isset($ordered_product['stock_info']))
                    continue;

                if (!ArrayHelper::getValue($ordered_product, 'stock_info.allow_out_of_stock_checkout') || ArrayHelper::getValue($ordered_product, 'stock_info.order_instock_bound')) {
                    $checkout_allowed = false;
                    break;
                }
            }
        }
        return $checkout_allowed;
    }

    public $isPaidUpdated = false;

    /**
     * to future: may check summ of payment transactions...
     * @return bool
     */
    protected function isReadyUpdatePaid(){
        return !$this->isPaidUpdated;
    }

/**
 * update paid amount by OrderPayment details (new transaction table)
 * intent - save transaction, update paid totals, update status (send email)
 * @param bool $update_db default true
 * @return null|array changed [paid, refund, details => [] ]
 */
    public function updatePaidTotals($update_db = true) {
      $changed = null;
      $orderPaymentStatusArray = \common\helpers\OrderPayment::getTotalStatusArray($this->getOrderId(), round($this->info['total_inc_tax'],2));

      if (is_array($orderPaymentStatusArray)) {
        if (round($this->info['total_paid_inc_tax'],2) != round($orderPaymentStatusArray['debit'],2) /*&& $orderPaymentStatusArray['debit']>0 */) {
            if (round($orderPaymentStatusArray['debit'],2)==0 && round($orderPaymentStatusArray['due']??0,2)>0) {//void
                $changed['refund'] = 'refund';
            } else {
                $changed['paid'] = 'paid';
            }
          $this->info['total_paid_exc_tax'] = $this->info['total_paid_inc_tax'] = $orderPaymentStatusArray['debit'];
        }
        if (round($this->info['total_refund_inc_tax'],2) != round($orderPaymentStatusArray['credit'],2) /*&& $orderPaymentStatusArray['credit']>0*/) {
          $changed['refund'] = 'refund';
          $this->info['total_refund_exc_tax'] = $this->info['total_refund_inc_tax'] = $orderPaymentStatusArray['credit'];
        }
      }

      if (!is_null($changed)) {
        $totalCollection = $this->manager->getTotalCollection();
        $order_total_array = $totalCollection->process(['ot_paid', 'ot_due', 'ot_refund', 'ot_preorder_pay']);
        foreach ($order_total_array as $sort_order_idx=>$total_data){
          $this->totals[$sort_order_idx] = $total_data;
        }

        if ( $update_db && $this->getOrderId() ){
          $this->total_replace(['ot_paid', 'ot_due', 'ot_refund', 'ot_preorder_pay'], $order_total_array);
        }
        $this->isPaidUpdated = true;

        $changed['details'] = $orderPaymentStatusArray;
      }

      return $changed;
    }

    /**
     *
     * @param int $status
     * @param bool $force do not check current and new status groups
     * @param string $notify_comments
     * @param array $extra_template_params
     * @param array $email_headers
     */
    public function update_status_and_notify($status, $force=false, $notify_comments='', $extra_template_params=[], $email_headers=[], $isNotify = true) {
        // do not switch if status group is not changed.
        $check = \common\models\OrdersStatus::find()->where(['orders_status_id' => [$status, $this->info['order_status']]]);
        if ($force || $check->count('distinct orders_status_groups_id')) {
            $customer_notified_status = 0;
            if ((int)$isNotify > 0) {
                $this->info['order_status'] = $status;
                try {
                    // pdf exceptions :(
                    $customer_notified_status = $this->send_status_notify($notify_comments, $extra_template_params, $email_headers);
                } catch (\Exception $e) {
                    \Yii::warning(print_r($e, 1), 'order-update_status_and_notify');
                }
            }
            $this->info['order_status'] = \common\helpers\Order::setStatus($this->getOrderId(), $status, [
                'comments' => $notify_comments,
                //'smscomments' => $smscomments,
                'customer_notified' => $customer_notified_status
            ]);
        }
    }

    /**
     * VL Seems if your online payment uses authorization the pre-auth amount is saved as paid ....
     * @param bool $without_check
     */
    public function update_piad_information($without_check = false) {
        if ($this->isReadyUpdatePaid() && ($this->manager->getPaymentCollection()->isOnline() || $without_check)) {
            $this->info['total_paid_inc_tax'] = $this->info['total_inc_tax'];
            $this->info['total_paid_exc_tax'] = $this->info['total_exc_tax'];
            /* @var $totalCollection \common\classes\order_total */
            $totalCollection = $this->manager->getTotalCollection();
            $totalCollection->process(['ot_paid', 'ot_due']);
            $this->isPaidUpdated = true;
        }
    }
    /**
     * reverse paid amount
     * @param type $amount_inc_vat
     * @param type $amount_exc_vat
     */
    public function return_paid($amount_inc_vat, $amount_exc_vat) {
        $this->info['total_refund_inc_tax'] += (float)$amount_inc_vat;
        $this->info['total_refund_exc_tax'] += (float)$amount_exc_vat;

        //$this->info['total_paid_inc_tax'] -= (float)$amount_inc_vat;
        //$this->info['total_paid_exc_tax'] -= (float)$amount_exc_vat;

        $totalCollection = $this->manager->getTotalCollection();
        $totalCollection->process(['ot_refund', 'ot_due', 'ot_paid']);
        $this->isPaidUpdated = true;
    }

    public function removeOrder() {
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_HISTORY . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int) $this->order_id . "'");
        tep_db_query("delete from " . $this->table_prefix . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $this->order_id . "'");
    }

    public function getCart() {
        global $cart;
        return (is_object($this->manager) && $this->manager->hasCart() ? $this->manager->getCart() : $cart);
    }

    public function isSendEmailAfterPaypalTransaction() {
        $sql = "SELECT paypal_notify FROM " . $this->table_prefix . TABLE_ORDERS . " WHERE orders_id = '" . (int) $this->order_id . "'";
        $order_query = tep_db_query($sql);
        if (tep_db_num_rows($order_query) > 0) {
            $notify = tep_db_fetch_array($order_query);
            if((int)$notify['paypal_notify']===1){
                return true;
            }
        }
        return false;
    }
    public function setFlagSendEmailAfterPaypalTransaction($flag) {
        $sql_data_array = [
            'paypal_notify' => $flag
        ];
        return tep_db_perform($this->table_prefix . TABLE_ORDERS, $sql_data_array, 'update', 'orders_id=' . (int) $this->order_id);
    }


  public function getPdfInvoice() {
    global $languages_id;

    $currencies = \Yii::$container->get('currencies');

    $pages = [['name' => 'invoice', 'params' => [
        'orders_id' => $this->getOrderId(),
        'platform_id' => $this->info['platform_id'],
        'language_id' => $languages_id,
        'order' => $this,
        'currencies' => $currencies,
        'oID' => $this->getOrderId(),
    ]]];

    if (defined("THEME_NAME")) {
        $theme_name = THEME_NAME;
    } else {
        $theme_name = \common\models\PlatformsToThemes::find()
            ->joinWith('themes t', false)
            ->select('theme_name')
            ->andWhere(['platform_id' => $this->info['platform_id']])
            ->asArray()->scalar();
    }

    return  \backend\design\PDFBlock::widget([
        'pages' => $pages,
        'params' => [
            'theme_name' => $theme_name,
            'document_name' => str_replace(' ', '_', TEXT_INVOICE) . $this->getOrderId() . '.pdf',
            'title' => TEXT_INVOICE . ' ' . $this->getOrderId(),
            'subject' => TEXT_INVOICE . ' ' . $this->getOrderId(),
            'destination' => 'S',
        ]
    ]);

  }

  /**
   * default is false
   * use isPaid and is Unpaid
   * imported order could not have paid info
   */
  public function isPaid () {
    $ret = false;
    if (is_array($this->totals)) {
      foreach ($this->totals as $total) {
        if ($total['class'] == 'ot_due' || $total['code'] == 'ot_due') {
          $ret = ($total['value'] < 0.00769); //0.01 - 30% VAT
          break;
        }
      }
    }
    return $ret;
  }

  /**
   * default is false
   * use isPaid and is Unpaid
   * imported order could not have paid info
   */
  public function isUnpaid () {
    $ret = false;
    if (is_array($this->totals)) {
      foreach ($this->totals as $total) {
        if ($total['class'] == 'ot_due' || $total['code'] == 'ot_due') {
          $ret = ($total['value'] > 0.00769); //0.01 - 30% VAT
          break;
        }
      }
    }
    return $ret;
  }

  /**
   * from totals or info
   * @return number
   */
  public function getDueAmount() {
    $ret = false;
    if (is_array($this->totals)) {
        foreach ($this->totals as $total) {
            if ($total['class'] == 'ot_due' || $total['code'] == 'ot_due') {
                $ret = $total['value'];
                break;
            }
        }
    }
    if ($ret === false) {
        $ret = 0;
        if (isset($this->info['total_due_inc_tax'])) {
            $ret = $this->info['total_due_inc_tax'];
        } elseif (isset($this->info['total_paid_inc_tax'])) {
            $ret = $this->info['total_inc_tax']??0;
            $ret -= $this->info['total_paid_inc_tax'];
            if (!empty($this->info['total_refund_inc_tax'])) {
                $ret += $this->info['total_refund_inc_tax'];
            }
        }
    }
    return $ret;
  }

    public function getProductsHtmlForEmail()
    {
        $currencies = \Yii::$container->get('currencies');
        foreach ($this->products as $i => $product) {
            $products_ordered_attributes = '';
            if (!empty($product['attributes']) && is_array($product['attributes'])) {
                foreach ($product['attributes'] as $attribute){
                    $products_ordered_attributes .= "\n\t" . htmlspecialchars($attribute['option']) . ': ' . htmlspecialchars($attribute['value']);
                }
            }
            /** @var \common\extensions\Quotations\Quotations $ext */
            $hidePrice = ($this->table_prefix == 'quote_' && ( ($ext = \common\helpers\Extensions::isAllowed('Quotations')) && !$ext::optionIsPriceShow() )) ||
                (defined('GROUPS_IS_SHOW_PRICE') && GROUPS_IS_SHOW_PRICE ==false);
            if (!$hidePrice) {
                $this->products[$i]['tpl_price'] = $currencies->display_price($product['final_price'], $product['tax'], $product['qty']);
            }
            $this->products[$i]['tpl_attributes'] = $products_ordered_attributes;
        }

        return \frontend\design\boxes\email\OrderProducts::widget(['params' => ['products' => $this->products, 'platform_id' => $this->info['platform_id']]]);
    }

    public function getOrderTotalsHtmlForEmail()
    {
        return \frontend\design\boxes\email\OrderTotals::widget(['params' => ['order_total_output' => $this->manager->getTotalOutput(true, $this->getEmailContext()), 'platform_id' => $this->info['platform_id']]]);
    }

    /**
     * Order valid for get tracking and shipping label
     *
     * @return bool
     */
    public function canBeDelivered()
    {
        return true;
        /**
         * Are you serious??? Move the method to @see ModuleShipping.
         **/
        return !empty($this->delivery['postcode']);
    }
}
