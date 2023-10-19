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

class ot_due extends ModuleTotal {

    var $title, $output;

    protected $visibility = [
        'admin',
        'shop_order',
    ];

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_DUE_TITLE' => 'Amount Due',
        'MODULE_ORDER_TOTAL_DUE_DESCRIPTION' => 'Amount Due'
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'ot_due';
        $this->title = MODULE_ORDER_TOTAL_DUE_TITLE;
        $this->description = MODULE_ORDER_TOTAL_DUE_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_DUE_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_DUE_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_DUE_SORT_ORDER;

        $this->output = array();
    }

    function process() {
        $currencies = \Yii::$container->get('currencies');
        $order = $this->manager->getOrderInstance();
        $this->output = [];
        $amount = $order->info['total_exc_tax'] - @$order->info['total_paid_exc_tax'] + @$order->info['total_refund_exc_tax'] - @$order->info['total_commission_exc_tax'];
        $amount_tax = $order->info['total_inc_tax'] - @$order->info['total_paid_inc_tax'] + @$order->info['total_refund_inc_tax'] - @$order->info['total_commission_inc_tax'];

        if (!empty($order->info['total_refund_inc_tax']) && @$order->info['total_refund_inc_tax'] >= ($order->info['total_paid_inc_tax'] - 0.01) ){
            $amount_tax = $amount = 0;
        }

        $this->output[] = array('title' => $this->title . ':',
            'text' => $currencies->format($amount_tax, true, $order->info['currency'], $order->info['currency_value']),
            'value' => $amount_tax,
            'text_exc_tax' => $currencies->format($amount, true, $order->info['currency'], $order->info['currency_value']),
            'text_inc_tax' => $currencies->format($amount_tax, true, $order->info['currency'], $order->info['currency_value']),
            'sort_order' => $this->sort_order,
            'code' => $this->code,
// {{
            'tax_class_id' => 0,
            'value_exc_vat' => $amount,
            'value_inc_tax' => $amount_tax,
// }}
        );
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_DUE_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_DUE_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_DUE_STATUS' =>
            array(
                'title' => 'Display Admount Due',
                'value' => 'true',
                'description' => 'Do you want to display the Admount Due?',
                'sort_order' => '150',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_DUE_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '2',
                'description' => 'Sort order of display.',
                'sort_order' => '150',
            ),
        );
    }

    function getDefaultTitle() {
        return '';
    }

    function getDefault($visibility_id = 0, $checked = false) {
        return '';
    }

    function getIncVAT($visibility_id = 0, $checked = false) {
        return tep_draw_radio_field('visibility_vat[' . $visibility_id . ']', 1, true);
    }

    function getExcVATTitle() {
        return '';
    }

    function getExcVAT($visibility_id = 0, $checked = false) {
        return '';
    }

}