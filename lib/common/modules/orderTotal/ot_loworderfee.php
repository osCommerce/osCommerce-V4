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

use common\classes\modules\ModuleTotal;
use common\classes\modules\ModuleStatus;
use common\classes\modules\ModuleSortOrder;

class ot_loworderfee extends ModuleTotal {

    var $title, $output;

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_LOWORDERFEE_TITLE' => 'Low Order Fee',
        'MODULE_ORDER_TOTAL_LOWORDERFEE_DESCRIPTION' => 'Low Order Fee'
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'ot_loworderfee';
        $this->title = MODULE_ORDER_TOTAL_LOWORDERFEE_TITLE;
        $this->description = MODULE_ORDER_TOTAL_LOWORDERFEE_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER;

        $this->output = array();
    }

    function process($replacing_value = -1, $visible = false) {

        $order = $this->manager->getOrderInstance();

        if (MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE == 'true' || $replacing_value != -1) {
          /** @var \common\classes\Currencies $currencies */
            $currencies = \Yii::$container->get('currencies');
            switch (MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION) {
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

            if ((($pass == true) && ( ($order->info['total'] - $order->info['shipping_cost']) < MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER)) || $replacing_value != -1 || $visible) {
                $low_fee = MODULE_ORDER_TOTAL_LOWORDERFEE_FEE;

                $taxation = $this->getTaxValues(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS, $order);

                $tax_class_id = $taxation['tax_class_id'];
                $tax = $taxation['tax'];
                $tax_description = $taxation['tax_description'];

                $low_fee_with_tax = \common\helpers\Tax::add_tax_always($low_fee, $tax);

                if ($replacing_value != -1) {
                    $cart = $this->manager->getCart();
                    if (is_array($replacing_value)) {
                        $low_fee = $replacing_value['ex'];
                        $low_fee_with_tax = $replacing_value['in'];
                    } else {
                        $replacing_value = [];
                        $replacing_value['ex'] = $low_fee;
                        $replacing_value['in'] = $low_fee_with_tax;
                    }
                    $cart->setTotalKey($this->code, $replacing_value);
                }
                $_tax = $low_fee_with_tax - $low_fee;

                $order->info['tax'] += $_tax;
                if (!isset($order->info['tax_groups']["$tax_description"])) {
                    $order->info['tax_groups']["$tax_description"] = 0;
                }
                $order->info['tax_groups']["$tax_description"] += $_tax;
                $order->info['total'] += $low_fee_with_tax;
                $order->info['total_inc_tax'] += $low_fee_with_tax;
                $order->info['total_exc_tax'] += $low_fee;

                parent::$adjusting += $currencies->format_clear($low_fee, true, $order->info['currency'], $order->info['currency_value']);

                $_val = \common\helpers\Tax::add_tax($low_fee, $tax);

                $this->output[] = array('title' => $this->title . ':',
                    'text' => $currencies->format($_val, true, $order->info['currency'], $order->info['currency_value']),
                    'value' => $_val,
                    'text_exc_tax' => $currencies->format($low_fee, true, $order->info['currency'], $order->info['currency_value']),
                    'text_inc_tax' => $currencies->format($low_fee_with_tax, true, $order->info['currency'], $order->info['currency_value']),
// {{
                    'tax_class_id' => $tax_class_id,
                    'value_exc_vat' => $low_fee,
                    'value_inc_tax' => $low_fee_with_tax,
// }}
                );
            }
        }
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS' =>
            array(
                'title' => 'Display Low Order Fee',
                'value' => 'true',
                'description' => 'Do you want to display the low order fee?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_LOWORDERFEE_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '4',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_LOWORDERFEE_LOW_ORDER_FEE' =>
            array(
                'title' => 'Allow Low Order Fee',
                'value' => 'false',
                'description' => 'Do you want to allow low order fees?',
                'sort_order' => '3',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER' =>
            array(
                'title' => 'Order Fee For Orders Under',
                'value' => '50',
                'description' => 'Add the low order fee to orders under this amount.',
                'sort_order' => '4',
                'use_function' => 'currencies->format',
            ),
            'MODULE_ORDER_TOTAL_LOWORDERFEE_FEE' =>
            array(
                'title' => 'Order Fee',
                'value' => '5',
                'description' => 'Low order fee.',
                'sort_order' => '5',
                'use_function' => 'currencies->format',
            ),
            'MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION' =>
            array(
                'title' => 'Attach Low Order Fee On Orders Made',
                'value' => 'both',
                'description' => 'Attach low order fee for orders sent to the set destination.',
                'sort_order' => '6',
                'set_function' => 'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ',
            ),
            'MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS' =>
            array(
                'title' => 'Tax Class',
                'value' => '0',
                'description' => 'Use the following tax class on the low order fee.',
                'sort_order' => '7',
                'use_function' => '\\common\\helpers\\Tax::get_tax_class_title',
                'set_function' => 'tep_cfg_pull_down_tax_classes(',
            ),
        );
    }

}