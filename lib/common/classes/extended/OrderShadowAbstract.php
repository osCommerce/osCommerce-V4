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

use common\classes\shipping;
use common\models\Customers;
use common\classes\events\common\order\OrderSetParentEvent;
use yii\helpers\ArrayHelper;
use yii\web\Session;
use common\classes\platform;
use common\helpers\Address;

abstract class OrderShadowAbstract implements OrderInterface {

    public function prepareOrderInfo() {

        $cart = $this->manager->getCart();

        $currencies = \Yii::$container->get('currencies');

        $shipping = $this->manager->getShipping();

        $delivery_option_inc = 0;
        $delivery_option_ex = 0;
        if (!($shipping['no_cost'] ?? null)) {
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryOptions', 'allowed')) {
                $delivery_option = $ext::calcCost($this->manager);
                if (is_array($delivery_option)) {
                    $delivery_option_inc = $delivery_option['cost_inc'];
                    $delivery_option_ex = $delivery_option['cost_ex'];
                }
            }
            
        }
        
        $payment = $this->manager->getPayment();

        $this->info = [
            'order_status' => DEFAULT_ORDERS_STATUS_ID,
            'platform_id' => $cart->platform_id,
            'currency' => $cart->currency,
            'currency_value' => $currencies->currencies[$cart->currency]['value'] ?? null,
            'language_id' => $cart->language_id,
            'admin_id' => @$cart->admin_id,
            'payment_method' => $payment,
            'payment_class' => $payment,
            'shipping_class' => @$shipping['id'],
            'shipping_weight' => $cart->show_weight(),
            'shipping_method' => @$shipping['title'],
            'shipping_cost' => @$shipping['cost'] + $delivery_option_inc,
            'shipping_no_cost' => @$shipping['no_cost'],
            'shipping_cost_inc_tax' => @$shipping['cost_inc_tax'] + $delivery_option_inc,
            'shipping_cost_exc_tax' => @$shipping['cost'] + $delivery_option_ex,
            'subtotal' => 0,
            'subtotal_inc_tax' => 0,
            'subtotal_exc_tax' => 0,
            'total_paid_exc_tax' => 0,
            'total_paid_inc_tax' => 0,
            'tax' => 0,
            'tax_groups' => array(),
            'comments' => $this->manager->has('comments') ? $this->manager->get('comments') : '',
            'greet_card' => $this->manager->get('greet_card'),
            'pointto' => $this->manager->get('pointto'),
            'delivery_date' => $this->manager->get('order_delivery_date'),
            'purchase_order' =>isset($_SESSION['purchase_order']) ? $_SESSION['purchase_order'] : '',
            'basket_id' => (int) $cart->basketID
        ];

        $this->setPaymentStatus($payment);

        if (($new_status = $cart->getStatusAfterPaid()) !== false) {
            $this->info['order_status'] = $new_status;
        }
    }
    
    public function setPaymentStatus($payment){
        if ($this->manager->getPaymentCollection()->isPaymentSelected()) {
            $pModule = $this->manager->getPaymentCollection()->getSelectedPayment();
            if (method_exists($pModule, 'getTitle')) {
                $this->info['payment_method'] = $pModule->getTitle($payment);
            } else {
                $this->info['payment_method'] = $pModule->title;
            }
            //$this->info['payment_class'] = $pModule->code;
            if ( $this->manager->has('admin_edit_order') && $this->manager->get('admin_edit_order') && \frontend\design\Info::isTotallyAdmin() ) {
                // prevent set default payment status to order on save - edit order
            }else{
                if (isset($pModule->order_status) && is_numeric($pModule->order_status) && ($pModule->order_status > 0)) {
                    $this->info['order_status'] = $pModule->order_status;
                }
            }
        }
    }

    public function prepareProducts() {
        $cart = $this->manager->getCart();

        $this->manager->defineOrderTaxAddress();

        $currencies = \Yii::$container->get('currencies');
        /*
        if (\common\helpers\Address::isEmpty($this->tax_address) ) {
            $this->tax_address = $this->manager->getTaxAddress();
        }*/

        $_defCountry = false;
        \common\helpers\Php8::nullArrProps($this->tax_address, ['entry_country_id', 'entry_zone_id']);
        $this->tax_address['entry_country_id'] = $this->tax_address['entry_country_id'] ?? null;
        if (!$this->tax_address['entry_country_id']) {
            $this->tax_address['entry_country_id'] = $this->manager->getTaxCountry();
            $_defCountry = true;
        }

        if (!$this->tax_address['entry_zone_id'] && $_defCountry) {
            $this->tax_address['entry_zone_id'] = $this->manager->getTaxZone();
        }

        $index = 0;
        $normilizeSP = false;
        if ($extSP = \common\helpers\Acl::checkExtension('SupplierPurchase', 'allowed')) {
            if ($extSP::allowed()) {
                $normilizeSP = true;
            }
        }
        $products = $cart->get_products();

        $_products = [];
        foreach ($products as $product) {
            if ( isset($product['linked_products']) && is_array($product['linked_products']) ){
                $linked_products = $product['linked_products'];
                unset($product['linked_products']);
                $_products[] = $product;
                foreach ($linked_products as $linked_product){
                    $_products[] = $linked_product;
                }
            }else{
                $_products[] = $product;
            }
        }
        $products = $_products;

        for ($i = 0, $n = sizeof($products); $i < $n; $i++) {
            $tax_values = $this->getTaxValues($products[$i]['tax_class_id']);
            $this->products[$index] = array('qty' => $products[$i]['quantity'],
                'reserved_qty' => $products[$i]['reserved_qty'],
                'name' => $products[$i]['name'],
                'model' => $products[$i]['model'],
                'stock_info' => $products[$i]['stock_info'],
                'products_file' => $products[$i]['products_file'],
                'is_virtual' => isset($products[$i]['is_virtual']) ? intval($products[$i]['is_virtual']) : 0,
                'gv_state' => (preg_match('/^GIFT/', $products[$i]['model']) ? 'pending' : 'none'),
                'tax' => /*$products[$i]['overwritten']['tax_rate'] ??*/ $tax_values['tax'],
                'tax_class_id' => $products[$i]['tax_class_id'],
                'tax_description' => $tax_values['tax_description'],
                'props' => ArrayHelper::getValue($products, [$i, 'props']),
                'propsData' => (isset($products[$i]['propsData'])?$products[$i]['propsData']:''),
                'ga' => $products[$i]['ga'],
                'price' => $products[$i]['price'],
                'final_price' => $products[$i]['final_price'],
                /* PC configurator addon begin */
                'template_uprid' => $products[$i]['id'],
                'parent_product' => ArrayHelper::getValue($products, [$i, 'parent']),
                'sub_products' => ArrayHelper::getValue($products, [$i, 'sub_products']),
                'relation_type' => isset($products[$i]['relation_type'])?$products[$i]['relation_type']:'',
                'configurator_price' => $cart->configurator_price($products[$i]['id'], $products),
                /* PC configurator addon end */
                'sort_order' => (isset($products[$i]['sort_order'])?$products[$i]['sort_order']:$index),
                'weight' => $products[$i]['weight'],
                'gift_wrap_price' => $products[$i]['gift_wrap_price'],
                'gift_wrapped' => $products[$i]['gift_wrapped'],
                'gift_wrap_allowed' => $products[$i]['gift_wrap_allowed'] ?? false,
                'virtual_gift_card' => $products[$i]['virtual_gift_card'] ?? false,
                'id' => ($normilizeSP ? $extSP::get_uprid($products[$i]['id']) : \common\helpers\Inventory::normalize_id($products[$i]['id'])),
                'subscription' => $products[$i]['subscription'],
                'subscription_code' => $products[$i]['subscription_code'],
                'promo_id' => $products[$i]['promo_id'] ?? 0,
                'specials_id' => (!empty($products[$i]['special_price'])? ($products[$i]['specials_id']??0) : 0),
// {{ Bonus Points
                'bonus_points_price' => ArrayHelper::getValue($products, [$i,'bonus_points_price']),
                'bonus_points_cost' => ArrayHelper::getValue($products, [$i,'bonus_points_cost']),
// }}                
                'overwritten' => $products[$i]['overwritten']);
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                $this->products[$index] = array_merge($ext::cartOrderFrontend($index, $cart, $products[$i]), $this->products[$index]);
            }
            if (!$products[$i]['ga'] && $cart->existOwerwritten($this->products[$index]['id'])) {
                $cart->overWrite($this->products[$index]['id'], $this->products[$index]);
            }
            $subindex = 0;

            $bundle_prods_options = array();
            $bundle_prods_options_array = array();
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                list($bundle_prods_options_array, $bundle_prods_options) = $ext::cartOrder($products[$i], ($this->manager->has('customer_groups_id') ? $this->manager->get('customer_groups_id') : DEFAULT_USER_GROUP));
            }

            if ($products[$i]['attributes']) {
                reset($products[$i]['attributes']);
// {{ Virtual Gift Card
                if (ArrayHelper::getValue($products, [$i,'virtual_gift_card']) && $products[$i]['attributes'][0] > 0) {
                    global $languages_id;
                    $virtual_gift_card = tep_db_fetch_array(tep_db_query("select vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.platform_id = '" . intval(\Yii::$app->get('platform')->config()->getPlatformToDescription()) . "' where length(vgcb.virtual_gift_card_code) = 0 and vgcb.virtual_gift_card_basket_id = '" . (int) $products[$i]['attributes'][0] . "' and p.products_id = vgcb.products_id and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and " . (!\Yii::$app->user->isGuest ? " vgcb.customers_id = '" . (int) \Yii::$app->user->getId() . "'" : " vgcb.session_id = '" . tep_session_id() . "'")));
                    $products_options_values_name = "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_recipients_name']))
                        $products_options_values_name .= TEXT_GIFT_CARD_RECIPIENTS_NAME . ' ' . $virtual_gift_card['virtual_gift_card_recipients_name'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_recipients_email']))
                        $products_options_values_name .= TEXT_GIFT_CARD_RECIPIENTS_EMAIL . ' ' . $virtual_gift_card['virtual_gift_card_recipients_email'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_message']))
                        $products_options_values_name .= TEXT_GIFT_CARD_MESSAGE . ' ' . $virtual_gift_card['virtual_gift_card_message'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_senders_name']))
                        $products_options_values_name .= TEXT_GIFT_CARD_SENDERS_NAME . ' ' . $virtual_gift_card['virtual_gift_card_senders_name'] . "\n";
                    $this->products[$index]['attributes'][$subindex] = array('option' => TEXT_GIFT_CARD_DETAILS,
                        'value' => $products_options_values_name,
                        'option_id' => 0,
                        'value_id' => $products[$i]['attributes'][0]);
                } else
// }}
                if (is_array($products[$i]['attributes'])) {
                    $attrText = \common\classes\PropsWorkerAttrText::getAttrText($products[$i]['props'] ?? null);
                    foreach ($products[$i]['attributes'] as $option => $value) {
// {{ Products Bundle Sets
                        if (in_array((string) $option, $bundle_prods_options))
                            continue;
// }}
                        $attributes_query = tep_db_query("select pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int) $products[$i]['id'] . "' and pa.options_id = '" . (int) $option . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int) $value . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int) $this->info['language_id'] . "' and poval.language_id = '" . (int) $this->info['language_id'] . "'");
                        $attributes = tep_db_fetch_array($attributes_query);
                        $attributes['options_values_price'] = \common\helpers\Attributes::get_options_values_price($attributes['products_attributes_id']??null, $products[$i]['quantity'] ?? 0);

                        $this->products[$index]['attributes'][$subindex] = array('option' => $attributes['products_options_name'],
                            'value' => ((isset($attrText[$option]) && !empty($attrText[$option])) ? $attrText[$option] : ($attributes['products_options_values_name']??null)),
                            'option_id' => $option,
                            'value_id' => $value,
                            'prefix' => $attributes['price_prefix']??null,
                            'price' => $attributes['options_values_price']??null);

                        $subindex++;
                    }
                }
            }

// {{ Products Bundle Sets
            foreach ($bundle_prods_options_array as $bundle_prods_option) {
                $this->products[$index]['attributes'][$subindex] = $bundle_prods_option;
                $subindex++;
            }
// }}
            if ($products[$i]['gift_wrapped']) {
                if (!is_array($this->products[$index]))
                    $this->products[$index] = array();
                $this->products[$index]['attributes'][] = array(
                    'option' => GIFT_WRAP_OPTION,
                    'value' => GIFT_WRAP_VALUE_YES,
                    'option_id' => -2,
                    'value_id' => -2);
            }

            $index++;
        }
        $this->compactLinkedProducts();
    }

    public function _setAddress($address) {

        $vat_status = 0;
        /** @var \common\extensions\VatOnOrder\VatOnOrder $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('VatOnOrder', 'allowed')) {
            $vat_status = $ext::check_vat_status($address);
            if ($vat_status > 1) {
              $address['entry_company_vat'] = \common\helpers\Validations::sanitizeVatId($address['entry_company_vat']);
            }
        }
        
        $customs_number_status = (!empty($address['entry_customs_number']) || empty($address['entry_company']));

        if ($this->manager->get('is_multi') == 1) {
            $address['entry_firstname'] = $this->manager->get('customer_first_name');
            $address['entry_lastname'] = $this->manager->get('customer_last_name');
        }
        return array(
            'address_book_id' => $address['address_book_id'] ?? null,
            'gender' => $address['entry_gender'] ?? null,
            'firstname' => $address['entry_firstname'] ?? null,
            'lastname' => $address['entry_lastname'] ?? null,
            'telephone' => ($address['entry_telephone'] ?? null) == '' ? $this->customer['telephone'] : $address['entry_telephone'],
            'company' => $address['entry_company'] ?? null,
            'company_vat' => $address['entry_company_vat'] ?? null,
            'company_vat_status' => $vat_status,
            'customs_number' => $address['entry_customs_number'] ?? null,
            'customs_number_status' => $customs_number_status,
            'street_address' => $address['entry_street_address'] ?? null,
            'suburb' => $address['entry_suburb'] ?? null,
            'city' => $address['entry_city'] ?? null,
            'postcode' => $address['entry_postcode'] ?? null,
            'state' => ((tep_not_null($address['entry_state'] ?? null)) ? $address['entry_state'] : \common\helpers\Zones::get_zone_name($address['entry_country_id'] ?? null, $address['entry_zone_id'] ?? null, '')),
            'zone_id' => $address['entry_zone_id'] ?? null,
            'country' => array('id' => $address['country']['countries_id'] ?? null, 'title' => $address['country']['countries_name'] ?? null, 'iso_code_2' => $address['country']['countries_iso_code_2'] ?? null, 'iso_code_3' => $address['country']['countries_iso_code_3'] ?? null),
            'country_id' => $address['entry_country_id'] ?? null,
            'format_id' => $address['country']['address_format_id'] ?? null);
    }

    public function prepareOrderAddresses() {
        global $languages_id;

        $this->customer = [];
        $this->delivery = [];
        $this->billing = [];
        if ($this->manager->isCustomerAssigned()) {
            $customer = $this->manager->getCustomersIdentity();

            if ($customer) {
                $this->customer = [
                    'id' => $customer->customers_id,
                    'customer_id' => $customer->customers_id,
                    'gender' => $customer->customers_gender,
                    'name' => $customer->customers_firstname . ' ' . $customer->customers_lastname,
                    'firstname' => $customer->customers_firstname,
                    'lastname' => $customer->customers_lastname,
                    'telephone' => $customer->customers_telephone,
                    'landline' => $customer->customers_landline,
                    'email_address' => $customer->customers_email_address
                ];
                if ($this->manager->get('is_multi') == 1) {
                    $this->customer['email_address'] = $this->manager->get('customer_email_address');
                }
                $address = $customer->getDefaultAddress()->asArray()->one();
                if ($address) {
                    $this->customer = array_merge($this->customer, [
                        'address_book_id' => $address['address_book_id'],
                        'street_address' => $address['entry_street_address'],
                        'suburb' => $address['entry_suburb'],
                        'city' => $address['entry_city'],
                        'postcode' => $address['entry_postcode'],
                        'state' => ((tep_not_null($address['entry_state'])) ? $address['entry_state'] : \common\helpers\Zones::get_zone_name($address['entry_country_id'], $address['entry_zone_id'], '')),
                        'zone_id' => $address['entry_zone_id'],
                        'country' => array('id' => $address['country']['countries_id'], 'title' => $address['country']['countries_name'], 'iso_code_2' => $address['country']['countries_iso_code_2'], 'iso_code_3' => $address['country']['countries_iso_code_3']),
                        'format_id' => $address['country']['address_format_id'],
                        'company' => $address['entry_company'],
                        'company_vat' => $address['entry_company_vat'],
                        'customs_number' => $address['entry_customs_number'],
                    ]);
                }
                $sendto = $this->manager->get('sendto');
                if (is_array($sendto)) {
                    $address = $sendto;
                } else {
                    $address = $customer->getAddressBook($sendto, true);
                }
                if ($address) {
                    $this->delivery = $this->_setAddress($address);
                }
                $billto = $this->manager->get('billto');
                if (is_array($billto)) {
                    $address = $billto;
                } else {
                    $address = $customer->getAddressBook($this->manager->get('billto'), true);
                }
                if ($address) {
                    $this->billing = $this->_setAddress($address);
                }
            }
        } else {
            if (is_array($this->manager->get('sendto'))) {
                $this->delivery = $this->manager->get('sendto');
                if ($this->delivery['country_iso_code_2'] ?? null) {
                    $_country_info = \common\helpers\Country::get_country_info_by_iso($this->delivery['country_iso_code_2']);
                    if ($_country_info) {
                        $this->delivery['country'] = $_country_info;
                        $this->delivery['format_id'] = $_country_info['address_format_id'];
                    }
                }
            }
            if (is_array($this->manager->get('billto'))) {
                $this->billing = $this->manager->get('billto');
                if ($this->billing['country_iso_code_2'] ?? null) {
                    $_country_info = \common\helpers\Country::get_country_info_by_iso($this->billing['country_iso_code_2']);
                    if ($_country_info) {
                        $this->billing['country'] = $_country_info;
                        $this->billing['format_id'] = $_country_info['address_format_id'];
                    }
                }
            }
            //$this->customer = $this->billing ? $this->billing : $this->delivery;
        }
    }
    /**
     * return cancelled product quantity
     * @param array $product - item from orders products
     * @return int
     */
    protected function getCancelledQty($product){
        $cancelled = 0;
        $cart = $this->manager->getCart();
        if (is_object($cart) && $cart->order_id){//in admin
            $query = $this->getProductsARModel()->where(['orders_id' => $cart->order_id, 'uprid' => (string)$product['id'], 'products_id' => (int)$product['id']]);
            if($query->exists()){
                $pModel = $query->one();
                if ($pModel->hasAttribute('qty_cnld')){
                    $cancelled = $pModel->qty_cnld;
                }
            }
        }
        return $cancelled;
    }

    private static function isPricesWithTax()
    {
        // right function is Tax::displayTaxable() but old code still used DISPLAY_PRICE_WITH_TAX without checking taxable widget
        // so DISPLAY_PRICE_WITH_TAX here to correct taxable price if widget is turned off and DISPLAY_PRICE_WITH_TAX == true
        return DISPLAY_PRICE_WITH_TAX == 'true' || \common\helpers\Tax::displayTaxable();
    }

    public function prepareOrderInfoTotals() {

        if (!$this->products) {
            $this->prepareProducts();
        }

        if (is_array($this->products)) {

            $currencies = \Yii::$container->get('currencies');
            $currency = \Yii::$app->settings->get('currency');
            if (\frontend\design\Info::isTotallyAdmin() && empty($currency)) {
              $currency = DEFAULT_CURRENCY;
              \Yii::$app->settings->set('currency', $currency);
            }
            $_roundTo = $currencies->currencies[$currency]['decimal_places'];

            for ($index = 0; $index < count($this->products); $index++) {
                $cancelledQty = $this->getCancelledQty($this->products[$index]);
                // double rounding compensation (1st to 6 digits) in $currencies->calculate_price()
                // fix error when $this->info['subtotal'] was not eq to $this->info['subtotal_exc_tax']
                $_price = round($this->products[$index]['final_price'], 6);
                $_tax = $this->products[$index]['tax'];
                $_qty = $this->products[$index]['qty'] - $cancelledQty;
                $shown_price = $currencies->calculate_price($_price, $_tax, $_qty);
                $this->info['subtotal'] += $shown_price;

                if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                    if ($_tax>0) {
                        $this->info['subtotal_exc_tax'] += \common\helpers\Tax::reduce_tax_always($_price * $_qty, $_tax);
                        $this->info['subtotal_inc_tax'] += $_price * $_qty;
                    } else {
                        $this->info['subtotal_exc_tax'] += \common\helpers\Tax::reduce_tax_always($_price * $_qty, abs($_tax));
                        $this->info['subtotal_inc_tax'] += \common\helpers\Tax::reduce_tax_always($_price * $_qty, abs($_tax));
                    }
                } else {
                  if (PRODUCTS_PRICE_QTY_ROUND == 'true') {
                    $this->info['subtotal_exc_tax'] += round($_price, $_roundTo) * $_qty;
                    $this->info['subtotal_inc_tax'] += round(\common\helpers\Tax::add_tax_always($_price, $_tax), $_roundTo) * $_qty;
                  } else {
                    $this->info['subtotal_exc_tax'] += round($_price * $_qty, $_roundTo);
                    $this->info['subtotal_inc_tax'] += round(\common\helpers\Tax::add_tax_always($_price, $_tax) * $_qty, $_roundTo);
                  }
                }

                $products_tax = abs($this->products[$index]['tax']);
                $products_tax_description = $this->products[$index]['tax_description'];
                if (self::isPricesWithTax()) {
                    if ($_tax>0) {
                        $this->info['tax'] += \common\helpers\Tax::roundTax($shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax))));
                        if (isset($this->info['tax_groups']["$products_tax_description"])) {
                            $this->info['tax_groups']["$products_tax_description"] += \common\helpers\Tax::roundTax($shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax))));
                        } else {
                            $this->info['tax_groups']["$products_tax_description"] = \common\helpers\Tax::roundTax($shown_price - ($shown_price / (($products_tax < 10) ? "1.0" . str_replace('.', '', $products_tax) : "1." . str_replace('.', '', $products_tax))));
                        }
                    }

                } else {
                    $this->info['tax'] += \common\helpers\Tax::roundTax(($products_tax / 100) * $shown_price);
                    if (isset($this->info['tax_groups']["$products_tax_description"])) {
                        $this->info['tax_groups']["$products_tax_description"] += \common\helpers\Tax::roundTax(($products_tax / 100) * $shown_price);
                    } else {
                        $this->info['tax_groups']["$products_tax_description"] = \common\helpers\Tax::roundTax(($products_tax / 100) * $shown_price);
                    }
                }
            }


            $this->info['total_inc_tax'] = $this->info['subtotal_inc_tax'] + $this->info['shipping_cost_exc_tax']; //$this->info['shipping_cost_exc_tax'];
            $this->info['total_exc_tax'] = $this->info['subtotal_exc_tax'] + $this->info['shipping_cost_exc_tax'];

            /* if (($values = $cart->getTotalKey('ot_paid')) !== false) {
              if (is_array($values)) {
              $this->info['total_paid_exc_tax'] = $values['ex'];
              $this->info['total_paid_inc_tax'] = $values['in'];
              }
              } */

            /*if (PRICE_WITH_BACK_TAX == 'True') {
                $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
            } elseif (self::isPricesWithTax()) {
                $this->info['total'] = $this->info['subtotal'] + $this->info['shipping_cost'];
            } else {
                $this->info['total'] = $this->info['subtotal'] + $this->info['tax'] + $this->info['shipping_cost'];
            }*/
            $this->info['total'] = $this->info['total_inc_tax'];
        }
    }
    /**
     * Save parent class type to real Order
     * @param type $order_id
     */
    public function setParent($order_id){
        if ($order_id){
            $opModel = new \common\models\OrdersParent();
            $opModel->orders_id = $order_id;
            $opModel->owner_class = get_called_class();
            if($opModel->save(false)){
                \Yii::$container->get('eventDispatcher')->dispatch(new OrderSetParentEvent($this, $order_id));
            }
        }
    }
        
    public function addLegend($comment, $admin_id){
        if ($this->order_id && ($comment || $admin_id)){
            $sql_data_array = array(
                'orders_id' => $this->order_id,
                'comments' => $comment,
                'admin_id' => (int)$admin_id,
                'date_added' => 'now()'
            );
            tep_db_perform($this->table_prefix . TABLE_ORDERS_HISTORY, $sql_data_array);
        }
    }

    function getTaxValues($tax_class_id) {

      if (defined('TAX_ADDRESS_OPTION') && (int)TAX_ADDRESS_OPTION == 1) { // by shipping address
        if ($this->manager->isShippingNeeded()) {
          $check_delivery = $this->manager->getDeliveryAddress();
        } else {
          $check_delivery = $this->manager->getBillingAddress();
        }
        $delivery_tax_values = \common\helpers\Tax::getTaxValues($this->info['platform_id'], $tax_class_id, Address::extractCountryId($check_delivery), $check_delivery['zone_id']);

      } elseif (defined('TAX_ADDRESS_OPTION') && (int)TAX_ADDRESS_OPTION == 0) { // by billing address
        $check_billing = $this->manager->getBillingAddress();
        $delivery_tax_values = \common\helpers\Tax::getTaxValues($this->info['platform_id'], $tax_class_id, Address::extractCountryId($check_billing), $check_billing['zone_id']);

      } else {
        // Seems DAA specific - any of (on checkout only)

        $check_delivery = $this->manager->getDeliveryAddress();
        $delivery_tax_values = \common\helpers\Tax::getTaxValues($this->info['platform_id'], $tax_class_id, Address::extractCountryId($check_delivery), $check_delivery['zone_id']);
        if ($delivery_tax_values['tax'] > 0) {
        } else { 
            $check_billing = $this->manager->getBillingAddress();
            $delivery_tax_values = \common\helpers\Tax::getTaxValues($this->info['platform_id'], $tax_class_id, Address::extractCountryId($check_billing), $check_billing['zone_id']);
        }
        
      }
      return $delivery_tax_values;

    }
    
    public function hasTransactions(){
        return false;
    }
    
    public function maintainSplittering(){
        return false;
    }
    /**
     * get default values for new (O)rders model
     * @param \yii\db\ActiveRecord $arModel
     * @return \yii\db\ActiveRecord
     */
    public static function getARModelNew(\yii\db\ActiveRecord $arModel){
        if ($arModel){
            if ($arModel->isNewRecord) $arModel->loadDefaultValues();
        }
        return $arModel;
    }

}
