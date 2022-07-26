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

class ot_subtax extends ModuleTotal {

    var $title, $output;

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_SUBTAX_TITLE' => 'Subtotal for taxation',
        'MODULE_ORDER_TOTAL_SUBTAX_DESCRIPTION' => 'Subtotal for taxation'
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'ot_subtax';
        $this->title = MODULE_ORDER_TOTAL_SUBTAX_TITLE;
        $this->description = MODULE_ORDER_TOTAL_SUBTAX_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_SUBTAX_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_SUBTAX_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_SUBTAX_SORT_ORDER;

        $this->output = array();
    }

    function process() {
        $order = $this->manager->getOrderInstance();
        $currencies = \Yii::$container->get('currencies');
        $this->output[] = array('title' => $this->title . ':',
            'text' => $currencies->format($order->info['total_exc_tax'], true, $order->info['currency'], $order->info['currency_value']),
            'value' => $order->info['total_exc_tax'],
            'text_exc_tax' => $currencies->format($order->info['total_exc_tax'], true, $order->info['currency'], $order->info['currency_value']),
            'text_inc_tax' => $currencies->format($order->info['total_inc_tax'], true, $order->info['currency'], $order->info['currency_value']),
// {{
            'tax_class_id' => 0,
            'value_exc_vat' => $order->info['total_exc_tax'],
            'value_inc_tax' => $order->info['total_inc_tax'],
// }}
        );
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_SUBTAX_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_SUBTAX_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_SUBTAX_STATUS' =>
            array(
                'title' => 'Display Subtotal for taxation',
                'value' => 'true',
                'description' => 'Do you want to display the subtotal for taxation value?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_SUBTAX_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '2',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
        );
    }

}