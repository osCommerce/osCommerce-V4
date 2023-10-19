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

class ot_tax extends ModuleTotal {

    var $title, $output;

    protected $visibility = [
        'admin',
        'shop_order',
    ];

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_TAX_TITLE' => 'Tax',
        'MODULE_ORDER_TOTAL_TAX_DESCRIPTION' => 'Order Tax'
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'ot_tax';
        $this->title = MODULE_ORDER_TOTAL_TAX_TITLE;
        $this->description = MODULE_ORDER_TOTAL_TAX_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_TAX_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_TAX_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_TAX_SORT_ORDER;

        $this->output = array();
    }

    function getIncVATTitle() {
        return '';
    }

    function getIncVAT($visibility_id = 0, $checked = false) {
        return '';
    }

    function getExcVATTitle() {
        return '';
    }

    function getExcVAT($visibility_id = 0, $checked = false) {
        return '';
    }

    function process($replacing_value = -1) {
        $this->output = [];
        $currencies = \Yii::$container->get('currencies');
        $order = $this->manager->getOrderInstance();

        if (is_array($order->info['tax_groups'] ?? null)) {
            $_value = 0;
            $_key = TABLE_HEADING_TAX;
            $step = 0;
            foreach ($order->info['tax_groups'] as $key => $value) {
                $value = number_format($value, 6,'.','');
                if ($value > 0) {
                    //if (tep_not_null($key)) $_key = $key;
                    $this->output[] = array('title' => $key . ':',
                        'text' => $currencies->format($value, true, $order->info['currency'], $order->info['currency_value']),
                        'value' => $value,
                        'text_exc_tax' => $currencies->format($value, true, $order->info['currency'], $order->info['currency_value']),
                        'text_inc_tax' => $currencies->format($value, true, $order->info['currency'], $order->info['currency_value']),
                        'tax_class_id' => 0,
                        'sort_order' => $this->sort_order + $step,
                        'value_exc_vat' => $value,
                        'value_inc_tax' => $value,
                    );
                    $_value += $value;
                    $step++;
                }
            }

            /*if (\frontend\design\Info::isTotallyAdmin()) {
                $cart = $this->manager->getCart();
                $ex = $_value;
                $in = $_value;
                $text_ex = $currencies->format($ex, true, $order->info['currency'], $order->info['currency_value']);
                $text_in = $currencies->format($in, true, $order->info['currency'], $order->info['currency_value']);
                $adjusted = false;
                $cart->setAdjusted(0);

                if ($replacing_value != -1 && is_array($replacing_value) && !$cart->isAdjusted()) {
                    if ($cart->getTotalTaxPrefix() == '+') {
                        $text_in = $currencies->format(($currencies->format_clear($in, true, $order->info['currency'], $order->info['currency_value']) + $replacing_value['in']), false, $order->info['currency'], $order->info['currency_value']);
                        $in += $replacing_value['in'] * $currencies->get_market_price_rate($order->info['currency'], DEFAULT_CURRENCY);
                    } else if ($cart->getTotalTaxPrefix() == '-') {
                        $text_in = $currencies->format(($currencies->format_clear($in, true, $order->info['currency'], $order->info['currency_value']) - $replacing_value['in']), false, $order->info['currency'], $order->info['currency_value']);
                        $in -= $replacing_value['in'] * $currencies->get_market_price_rate($order->info['currency'], DEFAULT_CURRENCY);
                    }
                    $adjusted = true;
                    $cart->setAdjusted($adjusted);
                }

                $this->output = [];
                $this->output[] = array('title' => $_key . ':',
                    'text' => $currencies->format($in, true, $order->info['currency'], $order->info['currency_value']),
                    'value' => $in,
                    'text_exc_tax' => $text_ex,
                    'text_inc_tax' => $text_in,
                    'adjusted' => $cart->isAdjusted(),
// {{
                    'tax_class_id' => 0,
                    'value_exc_vat' => $ex,
                    'value_inc_tax' => $in,
                    'difference' => number_format($currencies->format_clear($order->info['total_inc_tax'], true, $order->info['currency'], $order->info['currency_value']) - (parent::$adjusting + $currencies->format_clear($in, true, $order->info['currency'], $order->info['currency_value'])), 2),
// }}
                );
                //echo '<pre>';print_r($this->output);
            }*/
        }
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_TAX_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_TAX_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_TAX_STATUS' =>
            array(
                'title' => 'Display Tax',
                'value' => 'true',
                'description' => 'Do you want to display the order tax value?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_TAX_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '3',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
        );
    }

}