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

class ot_total extends ModuleTotal {

    var $title, $output;

    protected $visibility = [
        'admin',
        'shop_order',
    ];

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_TOTAL_TITLE' => 'Total',
        'MODULE_ORDER_TOTAL_TOTAL_DESCRIPTION' => 'Order Total'
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'ot_total';
        $this->title = MODULE_ORDER_TOTAL_TOTAL_TITLE;
        $this->description = MODULE_ORDER_TOTAL_TOTAL_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_TOTAL_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_TOTAL_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER;

        $this->output = array();
    }

    function process() {
        $this->output = [];
        $order = $this->manager->getOrderInstance();
        \common\helpers\Php8::nullArrProps($order->info, ['total_exc_tax', 'total_inc_tax', 'currency', 'currency_value', 'total']);
        $currencies = \Yii::$container->get('currencies');
        $this->output[] = array('title' => $this->title . ':',
            'text' => '<b>' . $currencies->format($order->info['total'], true, $order->info['currency'], $order->info['currency_value']) . '</b>'
                 . '<input type="hidden" class="ot_total_clear" value="' . $currencies->format_clear($order->info['total'], true, $order->info['currency'], $order->info['currency_value']) . '" />',
            'value' => $order->info['total'],
            'text_exc_tax' => '<b>' . $currencies->format($order->info['total_exc_tax'], true, $order->info['currency'], $order->info['currency_value']) . '</b>'. '<input type="hidden" class="ot_total_clear" value="' . $currencies->format_clear($order->info['total_exc_tax'], true, $order->info['currency'], $order->info['currency_value']) . '" />',
            'text_inc_tax' => '<b>' . $currencies->format($order->info['total_inc_tax'], true, $order->info['currency'], $order->info['currency_value']) . '</b>'. '<input type="hidden" class="ot_total_clear" value="' . $currencies->format_clear($order->info['total_inc_tax'], true, $order->info['currency'], $order->info['currency_value']) . '" />',
// {{
            'tax_class_id' => 0,
            'value_exc_vat' => $order->info['total_exc_tax'],
            'value_inc_tax' => $order->info['total_inc_tax'],
// }}
        );
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_TOTAL_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_TOTAL_STATUS' =>
            array(
                'title' => 'Display Total',
                'value' => 'true',
                'description' => 'Do you want to display the total order value?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '4',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
        );
    }

}