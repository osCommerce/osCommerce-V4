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
namespace common\modules\orderTotal;

use yii\helpers\ArrayHelper;
use common\classes\modules\ModuleTotal;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

class ot_shipping extends ModuleTotal {

    var $title, $output;

    protected $visibility = [
        'admin',
        'shop_order',
    ];

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_SHIPPING_TITLE' => 'Shipping',
        'MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION' => 'Order Shipping Cost',
        'FREE_SHIPPING_TITLE' => 'Free Shipping',
        'FREE_SHIPPING_DESCRIPTION' => 'Free shipping for orders over %s'
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'ot_shipping';
        $this->title = MODULE_ORDER_TOTAL_SHIPPING_TITLE;
        $this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_SHIPPING_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_SHIPPING_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;

        $this->output = array();
    }

    function process($replacing_value = -1, $visible = false) {
        $this->output = [];

        $currencies = \Yii::$container->get('currencies');
        $order = $this->manager->getOrderInstance();
        if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                case 'national':
                    if ($this->delivery['country_id'] == STORE_COUNTRY)
                        $pass = true;
                    break;
                case 'international':
                    if ($this->delivery['country_id'] != STORE_COUNTRY)
                        $pass = true;
                    break;
                case 'both':
                    $pass = true;
                    break;
                default:
                    $pass = false;
                    break;
            }

            if ((($pass == true) && ( ($order->info['total'] - $order->info['shipping_cost']) >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER))) {

                $order->info['shipping_method'] = $this->title;
                $order->info['total'] -= $order->info['shipping_cost'];
                $order->info['total_inc_tax'] -= $order->info['shipping_cost'];
                $order->info['total_exc_tax'] -= $order->info['shipping_cost'];
                $order->info['shipping_cost'] = 0;
                $order->info['shipping_cost_exc_tax'] = 0;
                $order->info['shipping_cost_inc_tax'] = 0;
            }
        }

        $shipping_module = false;
        $selected = $this->manager->getShipping();
        if ($selected){
            $shipping_module = $this->manager->getShippingCollection()->get($selected['module']);
        }

        $shipping_tax_calculated = 0;
        $shipping_tax_description = null;
        if (tep_not_null($order->info['shipping_method'] ?? null)) {
            $tax_class = 0;
            if ($shipping_module && is_object($shipping_module) && $shipping_module->tax_class > 0 
                && abs(\common\helpers\Tax::get_tax_rate( $shipping_module->tax_class, $this->delivery['country']['id']??STORE_COUNTRY, $this->delivery['zone_id']??STORE_ZONE ))>0 ) {

                if (!$shipping_module->useDelivery()){
                    $this->delivery = ['country'=> ['id' => STORE_COUNTRY], 'zone_id' => STORE_ZONE];
                }
                $taxation = $this->getTaxValues($shipping_module->tax_class, $order);

                $tax_class = $taxation['tax_class_id'];
                $shipping_tax = $taxation['tax'];
                $shipping_tax_description = $taxation['tax_description'];
                if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                    if ($shipping_tax>0) {
                        $order->info['shipping_cost_exc_tax'] = \common\helpers\Tax::roundTax(\common\helpers\Tax::get_untaxed_value($order->info['shipping_cost'], $shipping_tax));
                        $shipping_tax_calculated = $order->info['shipping_cost'] - $order->info['shipping_cost_exc_tax'];
                    } else {
                        $order->info['shipping_cost_exc_tax'] = \common\helpers\Tax::roundTax(\common\helpers\Tax::get_untaxed_value($order->info['shipping_cost'], abs($shipping_tax)));
                        $shipping_tax_calculated =  0;
                    }
                } else {
                    $order->info['shipping_cost_exc_tax'] = $order->info['shipping_cost'];
                    $shipping_tax_calculated = \common\helpers\Tax::roundTax(\common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $shipping_tax));
                }

                if (!isset($order->info['tax'])) {
                    $order->info['tax'] = 0;
                }
                if (!isset($order->info['tax_groups'][$shipping_tax_description])) {
                    $order->info['tax_groups'][$shipping_tax_description] = 0;
                }
                if (!isset($order->info['total'])) {
                    $order->info['total'] = 0;
                }
                if (!isset($order->info['total_inc_tax'])) {
                    $order->info['total_inc_tax'] = 0;
                }

                foreach (\common\helpers\Hooks::getList('ot-shipping/process') as $filename) {
                    include($filename);
                }

                $order->info['tax'] += $shipping_tax_calculated;
                $order->info['tax_groups'][$shipping_tax_description] += $shipping_tax_calculated;
                if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                    if ( $shipping_tax > 0 ) {
                        $order->info['shipping_cost_inc_tax'] = $order->info['shipping_cost'];
                    } else {
                        $subTax = $order->info['shipping_cost'] - $order->info['shipping_cost_exc_tax'];
                        $order->info['shipping_cost'] = $order->info['shipping_cost_inc_tax'] = $order->info['shipping_cost_exc_tax'];
                        $order->info['total'] -= $subTax;
                        $order->info['total_inc_tax'] -= $subTax;
                        $order->info['total_exc_tax'] -= $subTax;
                    }
                } else {
                    //$order->info['total'] += $shipping_tax_calculated; //total must include tax, but correct total if new shipping is posted
                    //$order->info['total_inc_tax'] += $shipping_tax_calculated;
                    $order->info['shipping_cost_inc_tax'] = $order->info['shipping_cost'] + $shipping_tax_calculated;
                    if (DISPLAY_PRICE_WITH_TAX == 'true') {
                        $order->info['shipping_cost'] += $shipping_tax_calculated;
                    }
                }

                 

            }

            if ($replacing_value != -1) {
                $cart = $this->manager->getCart();
                $order->info['total_inc_tax'] -= $order->info['shipping_cost_inc_tax'];
                $order->info['total_exc_tax'] -= $order->info['shipping_cost_exc_tax'];
                $order->info['total'] -= $order->info['shipping_cost'];
                if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                } else {
                    if (DISPLAY_PRICE_WITH_TAX != 'true'){
                      $order->info['total'] -= $shipping_tax_calculated;
                    }
                }

                if (is_array($replacing_value)) {
                    $order->info['shipping_cost_exc_tax'] = $replacing_value['ex'];
                    $order->info['shipping_cost_inc_tax'] = $replacing_value['in'];
                } else {
                    $order->info['shipping_cost_inc_tax'] = $order->info['shipping_cost_exc_tax'] = $replacing_value;
                }
                if (DISPLAY_PRICE_WITH_TAX == 'true'){
                    $order->info['shipping_cost'] = $order->info['shipping_cost_inc_tax'];
                } else {
                    $order->info['shipping_cost'] = $order->info['shipping_cost_exc_tax'];
                }
                $order->info['tax'] = $order->info['tax'] - $shipping_tax_calculated + ($order->info['shipping_cost_inc_tax'] - $order->info['shipping_cost_exc_tax']);
                $order->info['tax_groups']["$shipping_tax_description"] = ($order->info['tax_groups']["$shipping_tax_description"]??0) - $shipping_tax_calculated + ($order->info['shipping_cost_inc_tax'] - $order->info['shipping_cost_exc_tax']);
                //$order->info['total'] = $order->info['total'] - $shipping_tax_calculated + ($order->info['shipping_cost_inc_tax'] - $order->info['shipping_cost_exc_tax']);
                $order->info['total'] += $order->info['shipping_cost_inc_tax']; // total always incl shipping tax, but shipping_cost could be w/o tax
                $order->info['total_inc_tax'] += $order->info['shipping_cost_inc_tax'];
                $order->info['total_exc_tax'] += $order->info['shipping_cost_exc_tax'];

                $cart->setTotalKey($this->code, $replacing_value);
            }

            parent::$adjusting += $currencies->format_clear($order->info['shipping_cost_exc_tax'], true, $order->info['currency'], $order->info['currency_value']);
            $this->output[] = array('title' => $order->info['shipping_method'] . ($order->info['shipping_no_cost']?'':':'),
                'text' => $shipping_module && $shipping_module->costUserCaption() !== false ? $shipping_module->costUserCaption() : $currencies->format($order->info['shipping_cost'], true, $order->info['currency'], $order->info['currency_value']),
                'value' => $order->info['shipping_cost'],
                'text_exc_tax' => $shipping_module && $shipping_module->costUserCaption() !== false ? $shipping_module->costUserCaption() : $currencies->format($order->info['shipping_cost_exc_tax'], true, $order->info['currency'], $order->info['currency_value']),
                'text_inc_tax' => $shipping_module && $shipping_module->costUserCaption() !== false ? $shipping_module->costUserCaption() : $currencies->format($order->info['shipping_cost_inc_tax'], true, $order->info['currency'], $order->info['currency_value']),
// {{
                'tax_class_id' => $tax_class,
                'value_exc_vat' => $order->info['shipping_cost_exc_tax'],
                'value_inc_tax' => $order->info['shipping_cost_inc_tax'],
// }}
            );
        }
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_SHIPPING_STATUS' =>
            array(
                'title' => 'Display Shipping',
                'value' => 'true',
                'description' => 'Do you want to display the order shipping cost?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '2',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING' =>
            array(
                'title' => 'Allow Free Shipping',
                'value' => 'false',
                'description' => 'Do you want to allow free shipping?',
                'sort_order' => '3',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER' =>
            array(
                'title' => 'Free Shipping For Orders Over',
                'value' => '50',
                'description' => 'Provide free shipping for orders over the set amount.',
                'sort_order' => '4',
                'use_function' => 'currencies->format',
            ),
            'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION' =>
            array(
                'title' => 'Provide Free Shipping For Orders Made',
                'value' => 'national',
                'description' => 'Provide free shipping for orders sent to the set destination.',
                'sort_order' => '5',
                'set_function' => 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ',
            ),
            'MODULE_ORDER_TOTAL_SHIPPING_RESULT_COUNT' =>
                array(
                    'title' => 'Number of shipping results',
                    'value' => '',
                    'description' => 'Limit results (if empty - unlimited).',
                    'sort_order' => '6',
                ),
            'MODULE_ORDER_TOTAL_SHIPPING_LIMIT_RESULT_SORT' =>
                array(
                    'title' => 'Limit shipping results sort mode',
                    'value' => 'Cheapest first',
                    'description' => '',
                    'sort_order' => '7',
                    'set_function' => 'tep_cfg_select_option(array(\'Cheapest first\', \'Modules sort order\'), ',
                ),
        );
    }

}
