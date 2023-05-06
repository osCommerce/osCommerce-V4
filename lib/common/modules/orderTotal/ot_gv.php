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

class ot_gv extends ModuleTotal {

    var $title, $output;
    protected $config = [];

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_GV_TITLE' => 'Credit Amount',
        'MODULE_ORDER_TOTAL_GV_HEADER' => 'Gift Vouchers/Credit Amount/Discount Coupons',
        'MODULE_ORDER_TOTAL_GV_DESCRIPTION' => 'Gift Vouchers/Credit Amount',
        'SHIPPING_NOT_INCLUDED' => ' [Shipping not included]',
        'TAX_NOT_INCLUDED' => ' [Tax not included]',
        'MODULE_ORDER_TOTAL_GV_USER_PROMPT' => 'Tick to use Credit Amount balance ->&nbsp;',
        'TEXT_ENTER_GV_CODE' => 'Enter Redeem Code&nbsp;&nbsp;',
        'ERROR_NO_INVALID_REDEEM_GV' => 'Invalid Gift Voucher Code',
        'ERROR_NO_CUSTOMER' => 'Login first. Only logged user can use credit amount',
        'ERROR_REDEEMED_AMOUNT' => 'Congratulations, you have redeemed '
    ];

    function __construct() {
        parent::__construct();

        $this->code = 'ot_gv';
        $this->title = MODULE_ORDER_TOTAL_GV_TITLE;
        $this->header = MODULE_ORDER_TOTAL_GV_HEADER;
        $this->description = MODULE_ORDER_TOTAL_GV_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_GV_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->user_prompt = MODULE_ORDER_TOTAL_GV_USER_PROMPT;
        $this->enabled = (defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true');
        $this->sort_order = MODULE_ORDER_TOTAL_GV_SORT_ORDER;
        $this->include_shipping = MODULE_ORDER_TOTAL_GV_INC_SHIPPING;
        $this->include_tax = MODULE_ORDER_TOTAL_GV_INC_TAX;
        $this->calculate_tax = MODULE_ORDER_TOTAL_GV_CALC_TAX;
        $this->credit_tax = MODULE_ORDER_TOTAL_GV_CREDIT_TAX;
        $this->tax_class = MODULE_ORDER_TOTAL_GV_TAX_CLASS;
        $this->show_redeem_box = '';//MODULE_ORDER_TOTAL_GV_REDEEM_BOX;
        $this->credit_class = true;
        $this->checkbox = '<input type="checkbox" onClick="submitFunction()" name="' . 'c' . $this->code . '">';
        $this->output = [];
        $this->config = [
            'ONE_PAGE_CHECKOUT' => defined('ONE_PAGE_CHECKOUT') ? ONE_PAGE_CHECKOUT : 'False',
            'ONE_PAGE_SHOW_TOTALS' => defined('ONE_PAGE_SHOW_TOTALS') ? ONE_PAGE_SHOW_TOTALS : 'false',
        ];
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

    function config($data) {
        if (is_array($data)) {
            $this->config = array_merge($this->config, $data);
        }
    }

    public function checkUnsavedVoucher(){
        global $REMOTE_ADDR;
        if ($this->manager->isCustomerAssigned()){
            if ($this->manager->has('gv_id')) {
                $gv_id = $this->manager->get('gv_id');
                $gv_query = tep_db_query("insert into  " . TABLE_COUPON_REDEEM_TRACK . " (coupon_id, customer_id, redeem_date, redeem_ip) values ('" . (int)$gv_id . "', '" . (int) $this->manager->getCustomerAssigned() . "', now(),'" . tep_db_input($REMOTE_ADDR) . "')");
                \common\helpers\Coupon::gv_account_update($this->manager->getCustomerAssigned(), $gv_id);
                $gv_update = tep_db_query("update " . TABLE_COUPONS . " set coupon_active = 'N' where coupon_id = '" . (int) $gv_id . "'");
                $this->manager->remove('gv_id');
            }
        }
    }

    function process($replacing_value = -1, $visible = false) {

        $order = $this->manager->getOrderInstance();

        if ($ccExt = \common\helpers\Acl::checkExtensionAllowed('CustomerCredit', 'allowed')) {
            $currencies = \Yii::$container->get('currencies');
            foreach ($ccExt::getDiscountArray($this->manager) as $discountRecord) {
                $discountAmount = ($discountRecord['value'] + $discountRecord['tax']);
                parent::$adjusting -= $currencies->format_clear($discountAmount, true, $order->info['currency'], $order->info['currency_value']);
                $order->info['total'] = ($order->info['total'] - $discountAmount);
                $order->info['total_inc_tax'] = ($order->info['total_inc_tax'] - $discountAmount);
                $order->info['total_exc_tax'] = ($order->info['total_exc_tax'] - $discountAmount);
                if ($order->info['total'] < 0) {
                    $order->info['total'] = 0;
                }
                if ($order->info['total_inc_tax'] < 0) {
                    $order->info['total_inc_tax'] = 0;
                }
                if ($order->info['total_exc_tax'] < 0) {
                    $order->info['total_exc_tax'] = 0;
                }
                $this->output[] = array(
                    'title' => ($discountRecord['title'] . ':'),
                    'text' => ('-' . $currencies->format($discountAmount)),
                    'value' => $discountAmount,
                    'text_exc_tax' => ('-' . $currencies->format($discountAmount)),
                    'text_inc_tax' => ('-' . $currencies->format($discountAmount)),
                    'tax_class_id' => $discountRecord['tax_class_id'],
                    'value_exc_vat' => $discountAmount,
                    'value_inc_tax' => $discountAmount,
                );
                unset($discountAmount);
            }
            unset($discountRecord);
        }

        $this->checkUnsavedVoucher();
        if ($this->manager->has('cot_gv') || $replacing_value != -1 || $visible) {
            $cot_gv = $this->manager->get('cot_gv');
            $tod_amount = 0;
            $order_total = $this->get_order_total();
            $od_amount = $this->calculate_credit($order_total);

            if ($replacing_value != -1) {
                if (is_array($replacing_value)) {
                    if (DISPLAY_PRICE_WITH_TAX == 'true') {
                        $od_amount = $replacing_value['in'];
                    } else {
                        $od_amount = $replacing_value['ex'];
                    }
                    $tod_amount = $replacing_value['in'] - $replacing_value['ex'];
                }
            }

            if ($this->calculate_tax != "None") {
                $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
                $od_amount = $this->calculate_credit($order_total);
            }
            if (is_numeric($cot_gv)) {
                $od_amount = min($od_amount, $cot_gv);
            }
            $this->deduction = $od_amount;

            if ($replacing_value != -1) {
                $cart = $this->manager->getCart();
                if (is_array($replacing_value)) {
                    if (DISPLAY_PRICE_WITH_TAX == 'true') {
                        $od_amount = $replacing_value['in'];
                    } else {
                        $od_amount = $replacing_value['ex'];
                    }
                    $tod_amount = $replacing_value['in'] - $replacing_value['ex'];
                }
                $cart->setTotalKey($this->code, $replacing_value);
            }

            if (DISPLAY_PRICE_WITH_TAX == 'true') {
                $order->info['total'] = $order->info['total'] - $od_amount;
                $order->info['total_inc_tax'] = $order->info['total_inc_tax'] - $od_amount;
                $order->info['total_exc_tax'] = $order->info['total_exc_tax'] - $od_amount + $tod_amount;
            } else {
                $order->info['total'] = $order->info['total'] - $od_amount - $tod_amount;
                $order->info['total_inc_tax'] = $order->info['total_inc_tax'] - $od_amount - $tod_amount;
                $order->info['total_exc_tax'] = $order->info['total_exc_tax'] - $od_amount;
            }

            if ($order->info['total'] < 0) {
                $order->info['total'] = 0;
            }
            if ($order->info['total_inc_tax'] < 0) {
                $order->info['total_inc_tax'] = 0;
            }
            if ($order->info['total_exc_tax'] < 0) {
                $order->info['total_exc_tax'] = 0;
            }

            if (abs($od_amount) > 0 || $visible) {
                $currencies = \Yii::$container->get('currencies');
                parent::$adjusting -= $currencies->format_clear($od_amount, true, $order->info['currency'], $order->info['currency_value']);

                if (DISPLAY_PRICE_WITH_TAX == 'true') {
                    $this->output[] = array('title' => $this->title . ':',
                        'text' => '-' . $currencies->format($od_amount),
                        'value' => $od_amount,
                        'text_exc_tax' => ($od_amount >0? '-' : '+') . $currencies->format(abs($od_amount - $tod_amount)),
                        'text_inc_tax' => ($od_amount >0? '-' : '+') . $currencies->format(abs($od_amount)),
                        'tax_class_id' => $this->tax_class,
                        'value_exc_vat' => $od_amount - $tod_amount,
                        'value_inc_tax' => $od_amount,
                    );
                } else {
                    $this->output[] = array('title' => $this->title . ':',
                        'text' => '-' . $currencies->format($od_amount),
                        'value' => $od_amount,
                        'text_exc_tax' => ($od_amount >0? '-' : '+') . $currencies->format(abs($od_amount)),
                        'text_inc_tax' => ($od_amount >0? '-' : '+') . $currencies->format(abs($od_amount + $tod_amount)),
                        'tax_class_id' => $this->tax_class,
                        'value_exc_vat' => $od_amount,
                        'value_inc_tax' => $od_amount + $tod_amount,
                    );
                }
            }
        }
    }

    function selection_test() {
        global $customer_id;
        if ($this->user_has_gv_account($customer_id)) {
            return true;
        } else {
            return false;
        }
    }

    function pre_confirmation_check($order_total) {

        $od_amount = 0; // set the default amount we will send back
        if ($this->manager->get('cot_gv')) {
            $order = $this->manager->getOrderInstance();
// pre confirmation check doesn't do a true order process. It just attempts to see if
// there is enough to handle the order. But depending on settings it will not be shown
// all of the order so this is why we do this runaround jane. What do we know so far.
// nothing. Since we need to know if we process the full amount we need to call get order total
// if there has been something before us then

            if ($this->include_tax == 'false') {
                $order_total = $order_total - $order->info['tax'];
            }
            if ($this->include_shipping == 'false') {
                $order_total = $order_total - $order->info['shipping_cost'];
            }
            $od_amount = $this->calculate_credit($order_total);


            if ($this->calculate_tax != "None") {
                $tod_amount = $this->calculate_tax_deduction($order_total, $od_amount, $this->calculate_tax);
                $od_amount = $this->calculate_credit($order_total) + $tod_amount;
            }
        }
        return $od_amount;
    }

    // original code
    /* function pre_confirmation_check($order_total) {
      if ($SESSION['cot_gv']) {
      $gv_payment_amount = $this->calculate_credit($order_total);
      }
      return $gv_payment_amount;
      } */


    function update_credit_account($i) {
        /* handled in apply credit */
        return;
    }


    function apply_credit() {
        $gv_payment_amount = 0;
        //global $insert_id, $customer_id;
        $order_id = $this->manager->getOrderInstance()->order_id;
        if ($this->manager->has('cot_gv')) {
            $currencies = \Yii::$container->get('currencies');
            $customer = $this->manager->getCustomersIdentity();
            $gv_payment_amount = $this->deduction;
            $customer->credit_amount -= $gv_payment_amount;
            $customer->save();
            $customer->saveCreditHistory($customer->customers_id, $gv_payment_amount, '-', DEFAULT_CURRENCY, $currencies->currencies[DEFAULT_CURRENCY]['value'],  'Order #' . $order_id);
            $this->manager->remove('cot_gv');
        }
        if (method_exists('\common\helpers\Coupon', 'credit_order_check_state')){
            \common\helpers\Coupon::credit_order_check_state((int) $order_id);
        }
        if ($ccExt = \common\helpers\Acl::checkExtensionAllowed('CustomerCredit', 'allowed')) {
            $ccExt::onOrderSave($this->manager, $_null);
        }
        return $gv_payment_amount;
    }

    function collect_posts($collect_data) {
        global $error, $opc_coupon_pool;
        $result = $this->_collect_posts($collect_data);
        return $result;
        if (is_array($result)) {
            if ($this->config['ONE_PAGE_CHECKOUT'] == 'True') {
                if ($this->config['ONE_PAGE_SHOW_TOTALS'] == 'true') {
                    if (!strstr($opc_coupon_pool['message'], ERROR_REDEEMED_AMOUNT))
                        $opc_coupon_pool = $result;
                } else {
                    \Yii::$container->get('message_stack')->add($result['message'], 'one_page_checkout', ($result['error'] ? 'error' : 'success'));
                    $error = true;
                }
                if ($result['error']) {
                    $error = true;
                }
                return;
            } else {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, \common\helpers\Output::get_all_get_params(array('error_message')) . 'error_message=' . urlencode($result['message']), 'SSL'));
            }
        }
    }

    function _collect_posts($collect_data) {
        $currencies = \Yii::$container->get('currencies');
        if (isset($collect_data['cot_gv']) && $collect_data['cot_gv']) {
            $this->manager->set('cot_gv', true);
        }
        if (isset($collect_data['gv_redeem_code']) && !empty($collect_data['gv_redeem_code'])) {
            $gv_result = \common\models\Coupons::getCouponByCode($collect_data['gv_redeem_code'], true);
            if($gv_result){
                if ( $gv_result->redeemTrack && ($gv_result->coupon_type == 'G')) {
                    return array('error' => true, 'message' => ERROR_NO_INVALID_REDEEM_GV);
                }
            } else {
                if (isset($this->config['GV_SOLO_APPLY']) && $this->config['GV_SOLO_APPLY'] == 'true') {
                    return array('error' => true, 'message' => ERROR_NO_INVALID_REDEEM_GV);
                }
            }

            if ($gv_result && $gv_result->coupon_type == 'G') {
                if (!$this->manager->isCustomerAssigned()) {
                    $this->manager->set('gv_id', $gv_result->coupon_id);
                    return array('error' => false, 'message' => ERROR_NO_CUSTOMER);
                }
                // Things to set
                // ip address of claimant
                // customer id of claimant
                // date
                // redemption flag
                // now update customer account with gv_amount

                $this->manager->getCustomersIdentity()->increaseCreditAmount($gv_result);

                $gv_result->setAttribute('coupon_active', 'N');

                $gv_result->addRedeemTrack($this->manager->getCustomerAssigned())
                        ->update(false);

                $customer = $this->manager->getCustomersIdentity();
                return array(
                    'error' => false,
                    'message' => ERROR_REDEEMED_AMOUNT . $currencies->format($gv_result->coupon_amount, false, $gv_result->coupon_currency),
                    'amount' => $currencies->format($customer->credit_amount)
                );
            }
        }
        /*if ($gv_result->coupon_type == 'G') {
            return array('error' => true, 'message' => ERROR_NO_REDEEM_CODE);
        }*/
        if ($this->manager->has('cot_gv') && isset($collect_data['cot_gv_amount']) && is_numeric($collect_data['cot_gv_amount'])) {
            if ($this->manager->isCustomerAssigned()) {
                $hasAmount = $this->user_has_gv_account($this->manager->getCustomerAssigned());
                if ($hasAmount){
                    $new_gv_amount = number_format(($collect_data['cot_gv_amount'] * $currencies->get_market_price_rate(\Yii::$app->settings->get('currency'), DEFAULT_CURRENCY)), 2, '.', '');
                    if ($hasAmount < $new_gv_amount) $new_gv_amount = $hasAmount;
                    if ($new_gv_amount > 0) {
                        $this->manager->set('cot_gv', $new_gv_amount);
                    }
                }
            }
        }
    }

    function calculate_credit($amount) {
        $gv_payment_amount = 0;
        if ($this->manager->isCustomerAssigned()){
            $customer = $this->manager->getCustomersIdentity();
            if ($this->manager->has('cot_gv') && is_numeric($this->manager->get('cot_gv'))){
                $gv_payment_amount = $this->manager->get('cot_gv');
            } else {
                $gv_payment_amount = $customer->credit_amount;
            }
            $save_total_cost = $amount;
            $full_cost = $save_total_cost - $gv_payment_amount;
            if ($full_cost <= 0) {
                $full_cost = 0;
                $gv_payment_amount = $save_total_cost;
            }
        }

        return round($gv_payment_amount, 2);
    }

    function calculate_tax_deduction($amount, $od_amount, $method) {
        $order = $this->manager->getOrderInstance();
        switch ($method) {
            case 'Standard':
                $ratio1 = $od_amount / $amount;
                $tod_amount = 0;
                if (is_array($order->info['tax_groups'])) foreach ($order->info['tax_groups'] as $key => $value) {
                    $tax_rate = \common\helpers\Tax::get_tax_rate_from_desc($key);
                    $total_net += $tax_rate * $order->info['tax_groups'][$key];
                }
                if ($od_amount > $total_net)
                    $od_amount = $total_net;
                if (is_array($order->info['tax_groups'])) foreach ($order->info['tax_groups'] as $key => $value) {
                    $tax_rate = \common\helpers\Tax::get_tax_rate_from_desc($key);
                    $net = $tax_rate * $order->info['tax_groups'][$key];
                    if ($net > 0) {
                        $god_amount = $order->info['tax_groups'][$key] * $ratio1;
                        $tod_amount += $god_amount;
                        $order->info['tax_groups'][$key] = $order->info['tax_groups'][$key] - $god_amount;
                    }
                }
                //$order->info['tax'] -= $tod_amount;
                //$order->info['total'] -= $tod_amount;
                break;
            case 'Credit Note':
                $taxation = $this->getTaxValues($this->tax_class, $order);
                $tax_rate = $taxation['tax'];
                $tax_desc = $taxation['tax_description'];
                $tod_amount = $od_amount / (100 + $tax_rate) * $tax_rate;
                $order->info['tax_groups'][$tax_desc] -= $tod_amount;
//          $order->info['total'] -= $tod_amount;   //// ????? Strider
                break;
            default:
        }
        return $tod_amount;
    }

    function user_has_gv_account($c_id) {
        $gv_query = tep_db_query("select credit_amount as amount from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $c_id . "'");
        if ($gv_result = tep_db_fetch_array($gv_query)) {
            return $gv_result['amount'];
        }
        return false;
    }

    function get_order_total() {
        $order = $this->manager->getOrderInstance();
        $order_total = $order->info['total_inc_tax'];
        if ($this->include_tax == 'false')
            $order_total = $order->info['total_exc_tax']; //$order_total - $order->info['tax'];
        if ($this->include_shipping == 'false') {
            if ($this->include_tax == 'false') {
                $order_total = $order_total - $order->info['shipping_cost_exc_tax'];
            } else {
                $order_total = $order_total - $order->info['shipping_cost_inc_tax'];
            }
        }


        return $order_total;
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_GV_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_GV_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_GV_STATUS' =>
            array(
                'title' => 'Display Total',
                'value' => 'true',
                'description' => 'Do you want to display the Gift Voucher value?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '740',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_GV_QUEUE' =>
            array(
                'title' => 'Queue Purchases',
                'value' => 'true',
                'description' => 'Do you want to queue purchases of the Gift Voucher?',
                'sort_order' => '3',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_INC_SHIPPING' =>
            array(
                'title' => 'Include Shipping',
                'value' => 'true',
                'description' => 'Include Shipping in calculation',
                'sort_order' => '5',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_INC_TAX' =>
            array(
                'title' => 'Include Tax',
                'value' => 'true',
                'description' => 'Include Tax in calculation.',
                'sort_order' => '6',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_CALC_TAX' =>
            array(
                'title' => 'Re-calculate Tax',
                'value' => 'None',
                'description' => 'Re-Calculate Tax',
                'sort_order' => '7',
                'set_function' => 'tep_cfg_select_option(array(\'None\', \'Standard\', \'Credit Note\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_TAX_CLASS' =>
            array(
                'title' => 'Tax Class',
                'value' => '0',
                'description' => 'Use the following tax class when treating Gift Voucher as Credit Note.',
                'sort_order' => '0',
                'use_function' => '\\common\\helpers\\Tax::get_tax_class_title',
                'set_function' => 'tep_cfg_pull_down_tax_classes(',
            ),
            'MODULE_ORDER_TOTAL_GV_CREDIT_TAX' =>
            array(
                'title' => 'Credit including Tax',
                'value' => 'false',
                'description' => 'Add tax to purchased Gift Voucher when crediting to Account',
                'sort_order' => '8',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID' =>
            array(
                'title' => 'Allowed Order Statuses',
                'value' => '2, 3, 4',
                'description' => 'Select the status of orders to be processed.',
                'sort_order' => '13',
                'set_function' => 'tep_cfg_select_multioption_order_statuses(',
            ),
            'MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID_COVERS' => array(
                'title' => 'Set status for covered order',
                'value' => 0,
                'description' => 'Covered order',
                'sort_order' => '0',
                'set_function' => 'tep_cfg_pull_down_order_statuses(',
                'use_function' => '\\common\\helpers\\Order::get_order_status_name',
            ),
        );
    }

}