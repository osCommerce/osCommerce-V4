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

class ot_paid extends ModuleTotal {

    var $title, $output;

    protected $visibility = [
        'admin',
        'shop_order',
    ];

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_PAID_TITLE' => 'Amount Paid',
        'MODULE_ORDER_TOTAL_PAID_DESCRIPTION' => 'Amount Paid'
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'ot_paid';
        $this->title = MODULE_ORDER_TOTAL_PAID_TITLE;
        $this->description = MODULE_ORDER_TOTAL_PAID_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_PAID_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_PAID_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_PAID_SORT_ORDER;

        $this->output = array();
    }

    function process($replacing_value = -1) {
        $order = $this->manager->getOrderInstance();
        \common\helpers\Php8::nullArrProps($order->info, ['total_paid_exc_tax', 'total_paid_inc_tax', 'currency', 'currency_value']);
        $currencies = \Yii::$container->get('currencies');
        $this->output = [];

        if ($replacing_value != -1) {
            if (is_array($replacing_value)) {
                $order->info['total_paid_exc_tax'] = $replacing_value['ex'];
                $order->info['total_paid_inc_tax'] = $replacing_value['in'];
            } else {
                $replacing_value = [];
                $replacing_value['ex'] = $order->info['total_paid_exc_tax'];
                $replacing_value['in'] = $order->info['total_paid_inc_tax'];
            }
        }

        /*$orderId = (int)$order->order_id;
        if ($orderId == 0) {
            if ($this->manager->hasCart()) {
                $cart = $this->manager->getCart();
                $orderId = (int)$cart->order_id;
                unset($cart);
            }
        }
        $order->info['total_paid_exc_tax'] = 0;
        $order->info['total_paid_inc_tax'] = 0;
        $order->info['total_refund_exc_tax'] = 0;
        $order->info['total_refund_exc_tax'] = 0;
        $orderPaymentStatusArray = \common\helpers\OrderPayment::getArrayStatusByOrderIdTotal($orderId, $order->info['total_inc_tax']);
        if (is_array($orderPaymentStatusArray)) {
            $order->info['total_paid_exc_tax'] = $orderPaymentStatusArray['paid'];
            $order->info['total_paid_inc_tax'] = $orderPaymentStatusArray['paid'];
            $order->info['total_refund_exc_tax'] = $orderPaymentStatusArray['credit'];
            $order->info['total_refund_inc_tax'] = $orderPaymentStatusArray['credit'];
        }
        unset($orderPaymentStatusArray);*/

        $this->output[] = array('title' => $this->title . ':',
            'text' => $currencies->format($order->info['total_paid_inc_tax'], true, $order->info['currency'], $order->info['currency_value']),
            'value' => $order->info['total_paid_inc_tax'],
            'text_exc_tax' => $currencies->format($order->info['total_paid_exc_tax'], true, $order->info['currency'], $order->info['currency_value']),
            'text_inc_tax' => $currencies->format($order->info['total_paid_inc_tax'], true, $order->info['currency'], $order->info['currency_value']),
            'sort_order' => $this->sort_order,
            'code' => $this->code,
            'tax_class_id' => 0,
            'value_exc_vat' => $order->info['total_paid_exc_tax'],
            'value_inc_tax' => $order->info['total_paid_inc_tax'],
        );
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_PAID_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_PAID_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_PAID_STATUS' =>
            array(
                'title' => 'Display Admount Paid',
                'value' => 'true',
                'description' => 'Do you want to display the Admount Paid?',
                'sort_order' => '140',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_PAID_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '2',
                'sort_order' => '140',
                'description' => 'Sort order of display.',
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