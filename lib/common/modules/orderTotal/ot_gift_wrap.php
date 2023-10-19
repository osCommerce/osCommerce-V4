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
use common\helpers\Tax;

class ot_gift_wrap extends ModuleTotal {

    var $title, $output;

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_GIFT_WRAP_TITLE' => 'Gift wrap',
        'MODULE_ORDER_TOTAL_GIFT_WRAP_DESCRIPTION' => 'Gift wrap'
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'ot_gift_wrap';
        $this->title = MODULE_ORDER_TOTAL_GIFT_WRAP_TITLE;
        $this->description = MODULE_ORDER_TOTAL_GIFT_WRAP_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_GIFT_WRAP_SORT_ORDER;
        $this->allow_message = defined('MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE') && (MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE == 'True') ? true : false;
        $this->cost = floatval(defined('MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE_COST')?MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE_COST:0);
        $this->max_words = intval(defined('MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE_WORDS')? MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE_WORDS :0);
        $this->max_chars = intval(defined('MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE_CHARS')? MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE_CHARS :0);

        $this->output = array();
    }

    function process($replacing_value = -1, $visible = false) {

        $order = $this->manager->getOrderInstance();

        if (!is_object($order) || !is_array($order->products))
            return;
        $currencies = \Yii::$container->get('currencies');
        $have_gift_wrapped_products = false;
        $gift_wrap_amount = 0;
        $_tax_class_rates = [];
        
        if (\Yii::$app->request->isPost){
            $this->collect_posts(\Yii::$app->request->post());
        }

        foreach ($order->products as $ordered_product) {
            if (isset($ordered_product['gift_wrapped']) && $ordered_product['gift_wrapped']) {
                $have_gift_wrapped_products = true;
                $gift_wrap_amount += $ordered_product['gift_wrap_price'];
                $_tax_class_rates[] = [
                    'value' => Tax::roundTax(\common\helpers\Tax::calculate_tax($ordered_product['gift_wrap_price'], $ordered_product['tax'])),
                    'class' => $ordered_product['tax_class_id'],
                    'taxed' => \common\helpers\Tax::add_tax($ordered_product['gift_wrap_price'], $ordered_product['tax'])
                ];
            }
        }

        if ($have_gift_wrapped_products == true || $replacing_value != -1 || $visible || ($this->hasMessage() && $this->cost>0)) {

            $taxation = $this->getTaxValues(MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS, $order);

            $tax_class_id = $taxation['tax_class_id'];
            $tax = $taxation['tax'];
            $tax_description = $taxation['tax_description'];
            $gift_wrap_tax_amount = array_sum(\yii\helpers\ArrayHelper::getColumn($_tax_class_rates, 'value'));
            $gift_wrap_amount_inc = array_sum(\yii\helpers\ArrayHelper::getColumn($_tax_class_rates, 'taxed')); //$gift_wrap_amount  $gift_wrap_tax_amount;

            if ($this->cost > 0 && $this->hasMessage()) {
              //add extra
              //nice to have - mode (add, max, replace)
              $gift_wrap_amount += $this->cost;
              $gift_wrap_tax_amount += Tax::roundTax(Tax::calculate_tax($this->cost, $tax));
              $gift_wrap_amount_inc += Tax::add_tax($this->cost, $tax);
            }

            if ($replacing_value != -1) {
                $cart = $this->manager->getCart();
                if (is_array($replacing_value)) {
                    $gift_wrap_amount = (float) $replacing_value['ex'];
                    $gift_wrap_amount_inc = (float) $replacing_value['in'];
                    $gift_wrap_tax_amount = $gift_wrap_amount_inc - $gift_wrap_amount;
                }
                $cart->setTotalKey($this->code, $replacing_value);
            }
            \common\helpers\Php8::nullArrProps($order->info['tax_groups'], ["$tax_description"]);
            $order->info['tax'] += $gift_wrap_tax_amount;
            $order->info['tax_groups']["$tax_description"] += $gift_wrap_tax_amount;
            $order->info['total'] += ($gift_wrap_amount+$gift_wrap_tax_amount);
            $order->info['total_inc_tax'] += ($gift_wrap_amount+$gift_wrap_tax_amount);
            $order->info['total_exc_tax'] += $gift_wrap_amount;
	
            parent::$adjusting += $currencies->format_clear($gift_wrap_amount, true, $order->info['currency'], $order->info['currency_value']);

            $this->output[] = array(
                'title' => $this->title . ':',
                'text' => $currencies->format($gift_wrap_amount_inc, true, $order->info['currency'], $order->info['currency_value']), //$currencies->format(\common\helpers\Tax::add_tax($gift_wrap_amount, $tax), true, $order->info['currency'], $order->info['currency_value']),
                'value' => $gift_wrap_amount_inc, //\common\helpers\Tax::add_tax($gift_wrap_amount, $tax),
                'text_exc_tax' => $currencies->format($gift_wrap_amount, true, $order->info['currency'], $order->info['currency_value']),
                'text_inc_tax' => $currencies->format(($gift_wrap_amount+$gift_wrap_tax_amount), true, $order->info['currency'], $order->info['currency_value']),
// {{
                'tax_class_id' => $tax_class_id,
                'value_exc_vat' => $gift_wrap_amount,
                'value_inc_tax' => ($gift_wrap_amount+$gift_wrap_tax_amount),
// }}
                'prefix' => '+',
            );
        }
    }
	
    public function collect_posts($collect_data) {
        global $error, $opc_coupon_pool;
        $result = $this->_collect_posts($collect_data);
        return $result;
    }

    private function _collect_posts($collect_data) {
      // gift_message flag
      // gift_message_text
        if (isset($collect_data['gift_message_text'])){
            if (!empty($collect_data['gift_message_text'])) {
                if (!empty($collect_data['gift_message_text']) && ($this->max_words > 0 || $this->max_chars > 0)) {
                    $collect_data['gift_message_text'] = strip_tags($collect_data['gift_message_text']);
                    $wordsCount = count(preg_split('/[^\p{L}\p{N}\']+/u', $collect_data['gift_message_text']));
                    if (($wordsCount > $this->max_words && $this->max_words > 0) || (mb_strlen($collect_data['gift_message_text']) > $this->max_chars && $this->max_chars > 0) ) {
                        return array('error' => true, 'message' => ERROR_TOO_LONG_MESSAGE);
                    }
                }
                $this->manager->set('gift_message', $collect_data['gift_message_text']);
            } elseif ($this->manager->has('gift_message')) {
                $this->manager->remove('gift_message');
            }
        }
    }

    private function hasMessage() {
        return $this->manager->has('gift_message');
    }
    
    public function getMessage() {
        return $this->hasMessage()? $this->manager->get('gift_message'):false;
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_GIFT_WRAP_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_GIFT_WRAP_STATUS' =>
            array(
                'title' => 'Display Gift Wrap Order Fee',
                'value' => 'true',
                'description' => 'Do you want to display the Gift Wrap Order Fee?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GIFT_WRAP_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '4',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_GIFT_WRAP_EACH_PRODUCT' =>
            array(
                'title' => 'Gift Wrap cost for each product',
                'description' => 'Multiply gift wrap cost on products quantity',
                'value' => 'False',
                'sort_order' => '3',
                'set_function' => "tep_cfg_select_option(array('True', 'False'),",
            ),
            'MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE' =>
            array(
                'title' => 'Allow Gift Message for order',
                'description' => 'Allow Gift Message for whole order',
                'value' => 'False',
                'sort_order' => '4',
                'set_function' => "tep_cfg_select_option(array('True', 'False'),",
            ),
            'MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE_COST' =>
            array(
                'title' => 'Gift Message price',
                'description' => 'Gift Message price',
                'value' => '',
                'sort_order' => '5',
            ),
            'MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE_WORDS' =>
            array(
                'title' => 'Gift Message max words count',
                'description' => 'Gift Message max words count, live empty to ignore',
                'value' => '',
                'sort_order' => '5',
            ),
            'MODULE_ORDER_TOTAL_GIFT_WRAP_MESSAGE_CHARS' =>
            array(
                'title' => 'Gift Message max characters count',
                'description' => 'Gift Message max characters count, live empty to ignore',
                'value' => '',
                'sort_order' => '6',
            ),
            'MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS' =>
            array(
                'title' => 'Tax Class',
                'value' => '0',
                'description' => 'Use the following tax class on the Gift Wrap Order Fee.',
                'sort_order' => '7',
                'use_function' => '\\common\\helpers\\Tax::get_tax_class_title',
                'set_function' => 'tep_cfg_pull_down_tax_classes(',
            ),
        );
    }

}