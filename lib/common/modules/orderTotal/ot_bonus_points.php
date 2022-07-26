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
use common\classes\Bonuses;
use common\helpers\Points;

class ot_bonus_points extends ModuleTotal {

    var $title, $output, $bonuses;

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_BONUS_POINTS_TITLE' => 'Points Redeemed',
        'MODULE_ORDER_TOTAL_BONUS_POINTS_DESCRIPTION' => 'Bonus Points',
        'ERROR_TEXT_NOT_ENOUGH_BONUS_POINTS' => 'It is not enough bonus points to buy all selected products.'
    ];

    public function __construct() {
        parent::__construct();

        $this->code = 'ot_bonus_points';
        $this->title = MODULE_ORDER_TOTAL_BONUS_POINTS_TITLE;
        $this->description = MODULE_ORDER_TOTAL_BONUS_POINTS_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_BONUS_POINTS_STATUS')) {
            $this->enabled = false;
            return;
        }
        $this->enabled = ((MODULE_ORDER_TOTAL_BONUS_POINTS_STATUS == 'true') ? true : false);
        $this->sort_order = MODULE_ORDER_TOTAL_BONUS_POINTS_SORT_ORDER;
        $this->credit_class = true;
        $this->output = array();
        $this->bonuses = false;
        $this->bonus_points_redeem = 0;
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

    function process($replacing_value = -1, $visible = false)
    {
        $order = $this->manager->getOrderInstance();
        if (!$this->enabled || !Points::allowApplyBonusPoints((float)$order->info['total_exc_tax'])) {
            $this->manager->remove('bonus_apply');
            $this->manager->remove('customer_bonus_points');
            return;
        }
        $order = $this->manager->getOrderInstance();
        $taxation = $this->getTaxValues(MODULE_ORDER_TOTAL_PAYMENTFEE_TAX_CLASS, $order);

        $tax_class_id = $taxation['tax_class_id'];
        $tax = $taxation['tax'];
        $tax_description = $taxation['tax_description'];


        $currencies = \Yii::$container->get('currencies');

        $order = $this->manager->getOrderInstance();
        if (!is_object($this->bonuses)) {
            $this->bonuses = $this->manager->getBonuses()->getBonusesFormInstance($order);
        }
        $this->bonuses->calculateBonuses($this->manager->get('bonus_apply'));
        if ($this->manager->get('bonus_apply')) {
            $final_price = 0;
            $list = $this->bonuses->getBonusList();

            if (!count($list)) {
                return;
            }

            foreach ($list as $products_id => $product) {
                $order->info['tax'] = round($order->info['tax'], 6);
                $order->info['tax_groups'][$product['tax_description']] = round(
                    $order->info['tax_groups'][$product['tax_description']],
                    6
                );
                $order->info['total_inc_tax'] = round($order->info['total_inc_tax'], 6);

                $final_price += $product['final_price'];
                $order->info['total_inc_tax'] -= ($product['final_price'] + $product['tax']);
                $order->info['total_exc_tax'] -= $product['final_price'];
                $order->info['total'] = $order->info['total_inc_tax'];
                $order->info['tax'] -= $product['tax'];
                if (isset($order->info['tax_groups'][$product['tax_description']])) {
                    $order->info['tax_groups'][$product['tax_description']] -= $product['tax'];
                }
            }

            $bonus_points_redeem = $this->bonuses->getRedeemValue();
            $bonus_points_left = $this->bonuses->getLeftValue();

            $order->info['bonus_points_redeem'] = $bonus_points_redeem;

            if ($replacing_value != -1) {
                $cart = $this->manager->getCart();
                if (is_array($replacing_value) && $bonus_points_redeem > $replacing_value['in']) {
                    $bonus_points_redeem = $replacing_value['in'];
                }
                $cart->setTotalKey($this->code, $replacing_value);
            }

            if ($bonus_points_redeem > 0 || $visible) {
                parent::$adjusting -= $final_price;

                $this->bonus_points_redeem = $bonus_points_redeem;
                $bonusPointsRedeemCost = false;
                $bonusPointsRedeemCostTax = 0.00;
                $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
                if (Points::getCurrencyCoefficient($customer_groups_id)) {
                    $bonusPointsRedeemCost = round(Points::getBonusPointsRedeemCost($bonus_points_redeem, $customer_groups_id), 2);
                    $bonusPointsRedeemCostTax = $bonusPointsRedeemCost + \common\helpers\Tax::roundTax(
                            \common\helpers\Tax::calculate_tax($bonusPointsRedeemCost, $tax)
                        );
                }

                $this->output[] = [
                    'title' => $this->title . "(left: {$bonus_points_left} points):",
                    'text' => '-' . ($bonusPointsRedeemCost
                            ? $currencies->format(
                                \common\helpers\Tax::add_tax($bonusPointsRedeemCost, $tax),
                                true,
                                $order->info['currency'],
                                $order->info['currency_value']
                            )
                            : $bonus_points_redeem
                        ),
                    'value' => -($bonusPointsRedeemCost
                        ? \common\helpers\Tax::add_tax($bonusPointsRedeemCost, $tax)
                        : $bonus_points_redeem
                    ),
                    'text_exc_tax' => '-' . ($bonusPointsRedeemCost
                            ? $currencies->format(
                                $bonusPointsRedeemCost,
                                true,
                                $order->info['currency'],
                                $order->info['currency_value']
                            )
                            : $bonus_points_redeem
                        ),
                    'text_inc_tax' => '-' . ($bonusPointsRedeemCost
                            ? $currencies->format(
                                $bonusPointsRedeemCostTax,
                                true,
                                $order->info['currency'],
                                $order->info['currency_value']
                            )
                            : $bonus_points_redeem),
                    'tax_class_id' => $tax_class_id,
                    'value_exc_vat' => ($bonusPointsRedeemCost ?: $bonus_points_redeem),
                    'value_inc_tax' => ($bonusPointsRedeemCost ? $bonusPointsRedeemCostTax : $bonus_points_redeem),
                ];
            }
        }
    }

    function pre_confirmation_check(){
        $deduction = 0;
        if ($this->enabled && $this->manager->get('bonus_apply')) {
            $order = $this->manager->getOrderInstance();

            if (!is_object($this->bonuses)){
               $this->bonuses = $this->manager->getBonuses()->getBonusesFormInstance($order);
            }
            $this->bonuses->calculateBonuses($this->manager->get('bonus_apply'));

            foreach($this->bonuses->getBonusList() as $products_id => $product){
                $deduction += $product['final_price'] + $product['tax'];
            }
        }
        return $deduction;
    }

    function collect_posts($collect_data) {
        $this->_collect_posts($collect_data);
    }

    private function _collect_posts($collect_data){
        //global $bonus_points_cost, $customer_bonus_points, $customer_id, $bonus_apply;

        if (isset($collect_data['bonus_apply']) && $collect_data['bonus_apply'] =='y'){
            $this->bonuses = $this->manager->getBonuses()->getBonusesFormInstance($this->manager->getOrderInstance());

            $this->manager->set('bonus_apply', true);

            $customer_bonus_points = $this->bonuses->customer_bonus_points_earn;

            if (isset($collect_data['bonus_points_amount']) && is_numeric($collect_data['bonus_points_amount'])){
                $customer_bonus_points = (int)($collect_data['bonus_points_amount'] <= $customer_bonus_points ? $collect_data['bonus_points_amount'] : $customer_bonus_points);
            }elseif ($collect_data['bonus_points_amount'] === '') {
                $customer_bonus_points = 0;
            }
            $this->manager->set('customer_bonus_points', $customer_bonus_points);
            $order = $this->manager->getOrderInstance();
            if (
                $customer_bonus_points < 0.01 ||
                !Points::allowApplyBonusPoints((float)$order->info['total_exc_tax'])
            ) {
                $this->manager->remove('bonus_apply');
                $this->manager->remove('customer_bonus_points');
                return;
            }
            $this->bonuses->setPointsToRedeem($customer_bonus_points);

            if (\frontend\design\Info::isTotallyAdmin()){
                $cart = $this->manager->getCart();
                $cart->setTotalKey('ot_bonus_points', ['ex' => $customer_bonus_points, 'in' => $customer_bonus_points]);
            }

        } else if(isset($collect_data['bonus_apply']) && $collect_data['bonus_apply'] =='n'){
            $this->manager->remove('bonus_apply');
            $this->manager->remove('customer_bonus_points');
        }
    }

    function config(){}

    function update_credit_account($i) {
        return false;
    }

    function apply_credit(){

        $order = $this->manager->getOrderInstance();
        if (!is_object($this->bonuses)){
            $this->bonuses = $this->manager->getBonuses()->getBonusesFormInstance($order);
        }
        $bonus_apply = $this->manager->get('bonus_apply');

        $data = [
            'comments' => 'Order #'. $order->order_id,
            'customer_notified' => 1,
            'customers_id' => $order->customer['customer_id'],
        ];

        if ($this->bonus_points_redeem && $bonus_apply) {
            $data['prefix'] = '-';
            $data['credit_amount'] = $this->bonus_points_redeem;
            \common\classes\Bonuses::updateCustomerBonuses($data);
        }
        $statuses = explode(", ", MODULE_ORDER_TOTAL_BONUS_POINTS_ORDER_STATUS_ID);
        if (in_array($order->info['order_status'], $statuses)) {
            $data['prefix'] = '+';
            $data['credit_amount'] = $this->bonuses->getBonusesCost();
            \common\classes\Bonuses::updateCustomerBonuses($data);
            \common\classes\Bonuses::setAppliedBonuses($order->order_id);
        }
        if (!\frontend\design\Info::isTotallyAdmin()){
            $this->manager->remove('bonus_apply');
            $this->manager->remove('customer_bonus_points');
        }
        return false;
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_BONUS_POINTS_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_BONUS_POINTS_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_BONUS_POINTS_STATUS' =>
            array(
                'title' => 'Display Bonus Points',
                'value' => 'true',
                'description' => 'Do you want this module to display?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_BONUS_POINTS_ORDER_STATUS_ID' =>
            array('title' => 'Order Status for Points applying',
                'desc' => 'Select Order Status for Earn Points applying',
                'value' => '',
                'use_function' => '\\common\\helpers\\Order::get_status_name',
                'set_function' => 'tep_cfg_select_multioption_order_statuses(',),
            'MODULE_ORDER_TOTAL_BONUS_POINTS_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '9',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_BONUS_POINTS_MINIMUM_CART' => [
                'title' => 'Bonus apply minimum cart',
                'description' => 'Subtotal for allow to apply bonus points',
                'value' => '0',
                'sort_order' => '4',
            ],
            'MODULE_ORDER_TOTAL_BONUS_POINTS_ORDER_STATUS_ID_COVERS' => array(
                'title' => 'Set status for covered order',
                'value' => 0,
                'description' => 'Covered order',
                'sort_order' => '0',
                'set_function' => 'tep_cfg_pull_down_order_statuses(',
                'use_function' => '\\common\\helpers\\Order::get_order_status_name',
            ),
            'MODULE_ORDER_TOTAL_BONUS_POINTS_TAX_CLASS' =>
                array(
                    'title' => 'Tax Class',
                    'value' => '0',
                    'description' => 'Use the following tax class on the payment fee.',
                    'sort_order' => '7',
                    'use_function' => '\\common\\helpers\\Tax::get_tax_class_title',
                    'set_function' => 'tep_cfg_pull_down_tax_classes(',
                ),
        );
    }

}
