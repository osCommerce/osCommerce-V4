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
use frontend\design\CartDecorator;

class ot_coupon extends ModuleTotal {

    var $title, $output;

    protected $visibility = [
        'admin',
        'shop_order',
    ];

    protected $config;
    protected $products_in_order; // VL strange idea to save 2 arrays
    protected $valid_products;
    protected $validProducts; // same purpose but with all details from cart (tax etc)

    protected $defaultTranslationArray = [
        'MODULE_ORDER_TOTAL_COUPON_TITLE' => 'Discount Coupons',
        'MODULE_ORDER_TOTAL_COUPON_TOTAL' => 'Total discount',
        'MODULE_ORDER_TOTAL_COUPON_HEADER' => 'Gift Vouchers/Discount Coupons',
        'MODULE_ORDER_TOTAL_COUPON_DESCRIPTION' => 'Discount Coupon',
        'SHIPPING_NOT_INCLUDED' => ' [Shipping not included]',
        'TAX_NOT_INCLUDED' => ' [Tax not included]',
        'MODULE_ORDER_TOTAL_COUPON_USER_PROMPT' => '',
        'ERROR_NO_INVALID_REDEEM_COUPON' => 'Invalid Coupon Code',
        'ERROR_INVALID_STARTDATE_COUPON' => 'This coupon is not available yet',
        'ERROR_INVALID_FINISDATE_COUPON' => 'This coupon has expired',
        'ERROR_INVALID_USES_COUPON' => 'This coupon could only be used ',
        'TIMES' => ' times.',
        'ERROR_INVALID_USES_USER_COUPON' => 'You have used the coupon the maximum number of times allowed per customer.',
        'REDEEMED_COUPON' => 'a coupon worth ',
        'REDEEMED_MIN_ORDER' => 'on orders over ',
        'REDEEMED_RESTRICTIONS' => ' [Product-Category restrictions apply]',
        'TEXT_ENTER_COUPON_CODE' => 'Enter Redeem Code&nbsp;&nbsp;'
    ];
    
    protected $deduction;

    function __construct() {
        parent::__construct();

        $this->code = 'ot_coupon';
        $this->header = MODULE_ORDER_TOTAL_COUPON_HEADER;
        $this->title = MODULE_ORDER_TOTAL_COUPON_TITLE;
        $this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
        if (!defined('MODULE_ORDER_TOTAL_COUPON_STATUS')) {
            $this->enabled = false;
            return false;
        }
        $this->user_prompt = '';
        $this->enabled = (defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && MODULE_ORDER_TOTAL_COUPON_STATUS == 'true');
        $this->sort_order = MODULE_ORDER_TOTAL_COUPON_SORT_ORDER;

        $this->include_tax = 0;
        $this->calculate_tax = false;
        $this->tax_class = 0;
        $this->credit_class = true;
        $this->output = array();
        $this->config = array(
            'ONE_PAGE_CHECKOUT' => defined('ONE_PAGE_CHECKOUT') ? ONE_PAGE_CHECKOUT : 'False',
            'ONE_PAGE_SHOW_TOTALS' => defined('ONE_PAGE_SHOW_TOTALS') ? ONE_PAGE_SHOW_TOTALS : 'false',
        );
        $this->products_in_order = [];
        $this->valid_products = [];
        $this->validProducts = [];

        $this->tax_info = [];
    }

    function config($data) {
        if (is_array($data)) {
            $this->config = array_merge($this->config, $data);
        }
    }

    function process($replacing_value = -1, $visible = false) {

        $currencies = \Yii::$container->get('currencies');
        $order = $this->manager->getOrderInstance();

        $this->tax_info = [];

        $this->tax_before_calculation = $order->info['tax'] ?? null;

        $cc_array = [];
        $cart = $this->manager->getCart();
        if (is_array($cart->cc_array)) {
            $cc_array = $cart->cc_array;
        }

        $discountSumm = 0;
        $taxDiscountSumm = 0;

        foreach ($cc_array as $id => $code) {

            $order_total = $this->get_order_total($id);
            $result = $this->calculate_credit($order_total, $id, $code);

            $this->deduction = $result['deduct'];
            $result['tax'] = ($result['deduct'] > 0 ? $this->calculate_tax_deduction($id, $order_total, $this->deduction) : 0);
            
            $discountSumm += $result['deduct'];
            $taxDiscountSumm += $result['tax'];
            $method = $result['method'];

            if (defined('DISPLAY_PRICE_WITH_TAX') && DISPLAY_PRICE_WITH_TAX == 'true') {
                $_od_amount = $result['deduct'] + $result['tax'];
            } else {
                $_od_amount = $result['deduct'];
            }

            $_code_str = '&nbsp;(' . $code . ')';
            $this->output[] = array(
                'title' => ($method == 'free_shipping' ? TEXT_FREE_SHIPPING : $this->title) . $_code_str . ':',
                'text' => '-' . $currencies->format($_od_amount),
                'value' => $_od_amount,
                'text_exc_tax' => '-' . $currencies->format($result['deduct']),
                'text_inc_tax' => '-' . $currencies->format($result['deduct'] + $result['tax']),
                'tax_class_id' => $this->tax_class,
                'value_exc_vat' => $result['deduct'],
                'value_inc_tax' => $result['deduct'] + $result['tax'],
            );
        }

        if (isset($this->tax_info['tax'])) {
            $order->info['tax'] += $this->tax_info['tax'];
        }
        if (isset($this->tax_info['tax_groups']) && is_array($this->tax_info['tax_groups'])) {
            foreach ($this->tax_info['tax_groups'] as $tax_desc => $tax_value) {
                $order->info['tax_groups'][$tax_desc] += $tax_value;
            }
        }

        if ($PersonalDiscount = \common\helpers\Acl::checkExtensionAllowed('PersonalDiscount', 'allowed')) {
            $data = $PersonalDiscount::getPersonalDiscountData($discountSumm, $taxDiscountSumm, $order, $this->manager->getCustomerAssigned());
            if (is_array($data)) {
                if (defined('DISPLAY_PRICE_WITH_TAX') && DISPLAY_PRICE_WITH_TAX == 'true') {
                    $_od_amount = $data['value'] + $data['tax'];
                } else {
                    $_od_amount = $data['value'];
                }
                $this->output[] = array(
                    'title' => $data['title'] . ':',
                    'text' => '-' . $currencies->format($_od_amount),
                    'value' => $_od_amount,
                    'text_exc_tax' => '-' . $currencies->format($data['value']),
                    'text_inc_tax' => '-' . $currencies->format($data['value'] + $data['tax']),
                    'tax_class_id' => $this->tax_class,
                    'value_exc_vat' => $data['value'],
                    'value_inc_tax' => $data['value'] + $data['tax'],
                );

                $discountSumm += $data['value'];
                $taxDiscountSumm += $data['tax'];
            }
        }

        if ($discountSumm > 0 || $visible) {

            if (defined('DISPLAY_PRICE_WITH_TAX') && DISPLAY_PRICE_WITH_TAX == 'true') {
                $order->info['total'] = $order->info['total'] - ($discountSumm + $taxDiscountSumm);
            } else {
                $order->info['total'] = $order->info['total'] - $discountSumm;
            }
            $order->info['total_inc_tax'] = $order->info['total_inc_tax'] - ($discountSumm + $taxDiscountSumm);
            $order->info['total_exc_tax'] = $order->info['total_exc_tax'] - $discountSumm;
            if ($order->info['total'] < 0) {
                $order->info['total']=0;
            }
            if ($order->info['total_inc_tax'] < 0) {
                $order->info['total_inc_tax']=0;
            }
            if ($order->info['total_exc_tax'] < 0) {
                $order->info['total_exc_tax']=0;
            }

            if (DISPLAY_PRICE_WITH_TAX == 'true') {
                $_od_amount = $discountSumm + $taxDiscountSumm;
            } else {
                $_od_amount = $discountSumm;
            }

            $this->output[] = array(
                'title' => MODULE_ORDER_TOTAL_COUPON_TOTAL . ':',
                'text' => '-' . $currencies->format($_od_amount),
                'value' => $_od_amount,
                'text_exc_tax' => '-' . $currencies->format($discountSumm),
                'text_inc_tax' => '-' . $currencies->format($discountSumm + $taxDiscountSumm),
                'tax_class_id' => $this->tax_class,
                'value_exc_vat' => $discountSumm,
                'value_inc_tax' => $discountSumm + $taxDiscountSumm,
            );
        }
    }

    function selection_test() {
        return false;
    }

    function pre_confirmation_check($order_total) {
        $result = $this->calculate_credit($order_total);
        return $result['deduct'];
    }

    function collect_posts($collect_data) {
      $result = $this->_collect_posts($collect_data);
      return $result;
    }

    /**
     * validate coupon against all restrictions
     * @param \common\models\Coupons $coupon
     * @return bool|string true(ok) or error message
     */
    private function _validate($coupon) {
      $ret = true;

      if (!$coupon) {
        $ret = ERROR_NO_INVALID_REDEEM_COUPON;
      }

      elseif (!empty($coupon->pos_only) && !\frontend\design\Info::isTotallyAdmin() ){
        $ret = ERROR_NO_INVALID_REDEEM_COUPON;
      }

      elseif ((!empty($coupon->check_platforms)) && \common\classes\platform::currentId() &&
          (!\common\models\CouponsToPlatform::find()->andWhere([
            'platform_id' => \common\classes\platform::currentId(),
            'coupon_id' => $coupon->coupon_id,
          ])->exists())
          ){
        $ret = ERROR_COUPON_PLATFORM;
      }

      elseif ($coupon->isStartDateInvalid()){
        $ret = ERROR_INVALID_STARTDATE_COUPON;
      }

      elseif ($coupon->isEndDateExpired()){
        $ret = ERROR_INVALID_FINISDATE_COUPON;
      }

      elseif ($coupon->uses_per_coupon>0
          && $coupon->uses_per_coupon <= \common\models\CouponRedeemTrack::find()->where(['spend_flag' => 0])->andWhere(['coupon_id' => $coupon->coupon_id])->count()) {
        $ret = ERROR_INVALID_USES_COUPON . $coupon->uses_per_coupon . TIMES;
      }

      elseif ($this->isRestrictedByCountry($coupon)){
        $ret = ERROR_INVALID_COUNTRY_COUPON;
      }

      elseif ($this->manager->isCustomerAssigned() && (
            (!empty($coupon->restrict_to_customers) && strtolower(trim($this->manager->getCustomersIdentity()->customers_email_address)) != strtolower(trim($coupon->restrict_to_customers)))
          ||
            (!empty($coupon->couponsCustomerCodesList) && is_array($coupon->couponsCustomerCodesList) &&
            !in_array(strtolower(trim($this->manager->getCustomersIdentity()->customers_email_address)), array_map('strtolower', \yii\helpers\ArrayHelper::getColumn($coupon->couponsCustomerCodesList, 'only_for_customer'))) )
          )){

        $ret = ERROR_COUPON_FOR_OTHER_CUSTOMER;
      }

      elseif ($this->manager->isCustomerAssigned() && $coupon->uses_per_user>0
          && $coupon->uses_per_user <= \common\models\CouponRedeemTrack::find()->alias('crt')
          ->innerJoin(TABLE_ORDERS . ' o', 'crt.order_id = o.orders_id')
          ->andWhere(['coupon_id' => $coupon->coupon_id])
          ->andWhere([ 'or',
            ['crt.customer_id' => (int)$this->manager->getCustomerAssigned()],
            ['o.customers_email_address' => $this->manager->getCustomersIdentity()->customers_email_address]
              ])
            ->andWhere(['spend_flag' => 0])
          ->count()
          ) {

            $ret = ERROR_INVALID_USES_USER_COUPON . $coupon->uses_per_user . TIMES;
      }

      if ($ret === true && $coupon->spend_partly == 1) {
          $redeem = \common\models\CouponRedeemTrack::find()
                  ->select('sum(spend_amount) as spend_amount')
                  ->where(['coupon_id' => $coupon->coupon_id, 'customer_id' => (int)$this->manager->getCustomerAssigned()])
                  ->asArray()
                  ->one();
          if ($redeem['spend_amount'] >= $coupon->coupon_amount) {
              $ret = ERROR_INVALID_USES_USER_COUPON;
          }
      }

      return $ret;

    }

    private function _collect_posts($collect_data) {
        if (isset($collect_data['gv_redeem_code']) && !empty($collect_data['gv_redeem_code'])) {

            $cc_array = [];
            $cart = $this->manager->getCart();
            if (is_array($cart->cc_array)
                && defined('MODULE_ORDER_TOTAL_COUPON_ONE_PER_ORDER_ONLY') && MODULE_ORDER_TOTAL_COUPON_ONE_PER_ORDER_ONLY == 'True'
                && !in_array($collect_data['gv_redeem_code'], $cart->cc_array)
                ) {
                $cart->clearCcItems();
            }
            if (is_array($cart->cc_array) ) {
                $cc_array = $cart->cc_array;
            }

            $newCode = true;
            foreach ($cc_array as $code) {
                if ($code == $collect_data['gv_redeem_code']) {
                    $newCode = false;
                }
            }

            if ($newCode) {

                $coupon_result = \common\models\Coupons::getCouponByCode($collect_data['gv_redeem_code'], true);
                $check = $this->_validate($coupon_result);
                if ($check !== true) {
                    return array('error' => true, 'message' => $check);
                }

                if ($coupon_result->coupon_type != 'G') {
                    if ($coupon_result->coupon_type == 'S') {
                        $coupon_amount = TEXT_FREE_SHIPPING;
                    } else {
                        $coupon_amount = TEXT_COUPON_ACCEPTED;
                    }

                    $cart->addCcItem($coupon_result->coupon_code);

                    return array('error' => false, 'message' => $coupon_amount, 'description' => $coupon_result->description->coupon_description);
                }
                if (!$collect_data['gv_redeem_code']) {
                    return array('error' => true, 'message' => ERROR_NO_REDEEM_CODE);
                }
            }
        }
    }

    /**
     * calculates discount for specified amount
     * @param number $order_total
     * @return array ['deduct' => nn.nn method=><free_shipping|standard> ]
     */
    function calculate_credit($order_total, $coupon_id, $coupon_code) {
        $order = $this->manager->getOrderInstance();
        $currencies = \Yii::$container->get('currencies');
        $od_amount = 0;
        $result = [];
        if (tep_not_null($coupon_code)) {
            $where = ['coupon_id' => (int) $coupon_id];
            $get_result = \common\models\Coupons::find()->active()->andWhere($where)->one();
            if ($get_result) {
                if ($this->_validate($get_result) === true) {
                    $this->coupon_code = $get_result['coupon_code'];
                    $this->tax_class = $get_result['tax_class_id'];
                    
                    if ($get_result->spend_partly == 1) {
                        $customer_id = (isset($order->customer['customer_id']) ? (int)$order->customer['customer_id'] : 0);
                        $redeem = \common\models\CouponRedeemTrack::find()->select('sum(spend_amount) as spend_amount')->andWhere(['coupon_id' => $get_result->coupon_id, 'customer_id' => $customer_id])->asArray()->one();
                        $coupon_amount = $get_result['coupon_amount'] - $redeem['spend_amount'];
                        if ($coupon_amount < 0) {
                            $coupon_amount = 0;
                        }
                        $c_deduct = $coupon_amount * $currencies->get_market_price_rate($get_result['coupon_currency'], DEFAULT_CURRENCY);
                    } else {
                        $coupon_amount = $get_result['coupon_amount'];
                    }
                    $c_deduct = $coupon_amount * $currencies->get_market_price_rate($get_result['coupon_currency'], DEFAULT_CURRENCY);
                    
                    $result['method'] = 'standard';
                    $get_result['coupon_minimum_order'] *= $currencies->get_market_price_rate($get_result['coupon_currency'], DEFAULT_CURRENCY);
                    if ($get_result['coupon_type'] == 'S') {
                        $od_amount = $order->info['shipping_cost_exc_tax'];
                        if ($get_result['restrict_to_products'] || $get_result['restrict_to_categories']) {
                            $od_amount = 0;
                            if (is_array($this->validProducts) && count($this->validProducts) > 0) {
                                $od_amount = $order->info['shipping_cost_exc_tax'];
                            }
                        }
                        $result['method'] = 'free_shipping';
                    } else {

                        if ($get_result['coupon_minimum_order'] <= $order_total) {

                            if ($get_result['coupon_type'] != 'P') { // fixed discount  tax specified in coupon
                                if ($get_result['flag_with_tax']) {
                                    $taxation = $this->getTaxValues($this->tax_class);
                                    $tax_rate = $taxation['tax'];
                                    $c_deduct = \common\helpers\Tax::get_untaxed_value($c_deduct, $tax_rate);
                                }
                                $od_amount = $c_deduct;
                            } else {
                                $od_amount = $order_total * $get_result['coupon_amount'] / 100;
                            }
                        }
                    }
                }
            }
            if ($od_amount > $order_total && $result['method'] != 'free_shipping') {
                $od_amount = $order_total;
            }
        }
        $result['deduct'] = $od_amount;
        return $result;
    }

    function calculate_tax_deduction($coupon_id, $amount, $od_amount) {
        $taxDiscountSumm = 0;
        $this->tax_info['tax'] = $this->tax_info['tax'] ?? null;
        $get_result = \common\models\Coupons::find()->active()->andWhere(['coupon_id' => (int) $coupon_id])->one();
        if ($get_result) {
            if ($this->_validate($get_result) === true) {
                $order = $this->manager->getOrderInstance();
                $this->tax_class = $get_result['tax_class_id'];

                $shipping_tax_class_id = 0;
                if ($get_result['uses_per_shipping'] || $get_result['coupon_type'] == 'S') {
                    $shipping = $this->manager->getShipping();
                    if (is_array($shipping)) {
                        $sModule = $this->manager->getShippingCollection()->get($shipping['module']);
                        if ($sModule) {
                            $shipping_tax_class_id = $sModule->tax_class;
                        }
                    }
                    $taxation = $this->getTaxValues($shipping_tax_class_id);
                    $shipping_tax = $taxation['tax'];
                    $shipping_tax_desc = $taxation['tax_description'];
                }
                $taxDiscountSumm = 0;
                $DISABLE_FOR_SPECIAL = defined('MODULE_ORDER_TOTAL_COUPON_DISABLE_FOR_SPECIAL') && MODULE_ORDER_TOTAL_COUPON_DISABLE_FOR_SPECIAL == 'True';
                if (!empty($get_result->disable_for_special)) {
                    $DISABLE_FOR_SPECIAL = true;
                }

                if ($DISABLE_FOR_SPECIAL || $get_result['restrict_to_products'] || $get_result['restrict_to_categories'] || (int) $get_result->products_max_allowed_qty > 0 || (int) $get_result->products_id_per_coupon > 0 || $get_result['exclude_categories'] || $get_result['exclude_products']) {

                    if (is_array($this->validProducts) && count($this->validProducts) > 0 && ($get_result['coupon_type'] == 'F' || $get_result['coupon_type'] == 'P')) {
                        if ($get_result['coupon_type'] == 'F') {
                            $taxation = $this->getTaxValues($this->tax_class);
                            $tax_rate = $taxation['tax'];
                            if (!isset($order->info['tax_groups'][$taxation['tax_description']])) {
                                $tax_rate = 0;
                            }
                            $taxDiscountSumm = \common\helpers\Tax::calculate_tax($od_amount, $tax_rate);
                            if (isset($order->info['tax_groups'][$taxation['tax_description']])) {
                                $order->info['tax_groups'][$taxation['tax_description']] -= $taxDiscountSumm;
                            }
                            $this->tax_info['tax'] -= $taxDiscountSumm;
                        } else {
                            foreach ($this->validProducts as $key => $value) {
                                if ($get_result['coupon_type'] == 'P') {
                                    $taxation = $this->getTaxValues($value['tax_class_id']);
                                    $tax_rate = $taxation['tax'];
                                }

                                if ($tax_rate) {
                                    $prodTaxDiscount = $value['final_price_exc'] * $tax_rate / 100 * $get_result['coupon_amount'] / 100;
                                    $taxDiscountSumm += $prodTaxDiscount;
                                    if (!empty($order->info['tax_groups'][$taxation['tax_description']])) {
                                        $order->info['tax_groups'][$taxation['tax_description']] -= $prodTaxDiscount;
                                    }
                                    $this->tax_info['tax'] -= $prodTaxDiscount;
                                }
                            }
                        }
                    }

                    if ($get_result['uses_per_shipping']) {
                        $shippingTaxDiscountSumm = 0;
                        if ($get_result['coupon_type'] == 'P') {
                            $shipping_tax_calculated = \common\helpers\Tax::calculate_tax($order->info['shipping_cost_exc_tax'], $shipping_tax);
                            $shippingTaxDiscountSumm = $shipping_tax_calculated / 100 * $get_result['coupon_amount'];
                        } else { //fixed
                            //calculate discount on shipping: $amount contains shipping and max allowed discount is $od_amount;
                            if ($amount <= $od_amount) {
                                $shippingDiscount = $order->info['shipping_cost_exc_tax'];
                            } else {
                                $shippingDiscount = $amount - $od_amount;
                            }
                            $shippingDiscount = min($shippingDiscount, $order->info['shipping_cost_exc_tax']);
                            $shippingTaxDiscountSumm = \common\helpers\Tax::calculate_tax($shippingDiscount, $shipping_tax);
                        }
                        if ($shipping_tax_calculated && $shippingTaxDiscountSumm > 0) {
                            if (isset($order->info['tax_groups'][$shipping_tax_desc])) {
                                $order->info['tax_groups'][$shipping_tax_desc] = $order->info['tax_groups'][$shipping_tax_desc] - $shippingTaxDiscountSumm;
                            }
                            $this->tax_info['tax'] -= $shippingTaxDiscountSumm;
                            $taxDiscountSumm += $shippingTaxDiscountSumm;
                        }
                    } elseif ($get_result['coupon_type'] == 'S' && is_array($this->validProducts) && count($this->validProducts) > 0) {
                        $shippingDiscount = $order->info['shipping_cost_exc_tax'];
                        $shippingTaxDiscountSumm = \common\helpers\Tax::calculate_tax($shippingDiscount, $shipping_tax);
                        if ($shippingTaxDiscountSumm > 0) {
                            if (isset($order->info['tax_groups'][$shipping_tax_desc])) {
                                $order->info['tax_groups'][$shipping_tax_desc] = $order->info['tax_groups'][$shipping_tax_desc] - $shippingTaxDiscountSumm;
                            }
                            $this->tax_info['tax'] -= $shippingTaxDiscountSumm;
                            $taxDiscountSumm += $shippingTaxDiscountSumm;
                        }
                    }

                } else {
                    if ($get_result['coupon_type'] == 'P') {
                        if ($this->tax_class) {
                            $taxation = $this->getTaxValues($this->tax_class, $order);
                            $tax_rate = $taxation['tax'];
                            if ($tax_rate) {

                                if (is_array($order->info['tax_groups']))
                                    foreach ($order->info['tax_groups'] as $key => $value) {
                                        $god_amount = $value / 100 * $get_result['coupon_amount'];
                                        $order->info['tax_groups'][$key] = $order->info['tax_groups'][$key] - $god_amount;
                                        $taxDiscountSumm += $god_amount;
                                    }
                                if ($get_result['uses_per_shipping']) {
                                    $shipping_tax_calculated = \common\helpers\Tax::calculate_tax($order->info['shipping_cost_exc_tax'], $shipping_tax);
                                    $god_amount = $shipping_tax_calculated / 100 * $get_result['coupon_amount'];

                                    if ($shipping_tax_calculated) {
                                        if (isset($order->info['tax_groups'][$shipping_tax_desc]))
                                            $order->info['tax_groups'][$shipping_tax_desc] = $order->info['tax_groups'][$shipping_tax_desc] - $god_amount;
                                    }
                                    $taxDiscountSumm += $god_amount;
                                }
                                $this->tax_info['tax'] -= $taxDiscountSumm;
                            }
                        }
                    } else { //F or S
                        $taxation = $this->getTaxValues($this->tax_class, $order);
                        $tax_rate = $taxation['tax'];
                        $tax_desc = $taxation['tax_description'];
                        $taxDiscountSumm = \common\helpers\Tax::calculate_tax($od_amount, $tax_rate);
                        if ($get_result['coupon_type'] != 'S') {
                            //$taxDiscountSumm = $od_amount / 100  * (100 + $tax_rate) - $od_amount;//$od_amount / (100 + $tax_rate) * $tax_rate;
                        }
                        $shipping_tax_calculated = 0;
                        if ($get_result['uses_per_shipping'] || $get_result['coupon_type'] == 'S') {
                            if (DISPLAY_PRICE_WITH_TAX != 'true'){
                                $shipping_tax_calculated = \common\helpers\Tax::calculate_tax($order->info['shipping_cost'], $shipping_tax);
                            } else {
                                $shipping_tax_calculated = $order->info['shipping_cost_inc_tax'] - $order->info['shipping_cost_exc_tax'];
                            }
                            if ($get_result['coupon_type'] == 'S') {
                                $taxDiscountSumm = $shipping_tax_calculated;
                            } else {
                                $taxDiscountSumm = min($taxDiscountSumm, (float) $order->info['tax_groups'][$tax_desc] + $shipping_tax_calculated);
                            }
                        } else {
                            $taxDiscountSumm = min($taxDiscountSumm, (float) $order->info['tax_groups'][$tax_desc]);
                        }

                        $this->tax_info['tax'] -= $taxDiscountSumm;
                        $this->tax_info['tax_groups'][$tax_desc] -= $taxDiscountSumm;
                    }
                }
            }
        }
        return $taxDiscountSumm;
    }

    function update_credit_account($i) {
        return false;
    }

    function apply_credit() {
        if ($this->deduction != 0 && $this->manager->has('cc_id')) {
            $coupon = \common\models\Coupons::findOne($this->manager->get('cc_id'));
            if ($coupon){
                $coupon->addRedeemTrack( $this->manager->getCustomerAssigned(), $this->manager->getOrderInstance()->order_id);
            }
        }
        $this->manager->remove('cc_id');
        $this->manager->remove('cc_code');
    }

    function get_order_total($coupon_id) {

        $order = $this->manager->getOrderInstance();
        $order_total = 0;
        $where = ['coupon_id' => (int) $coupon_id];
        $get_result  = \common\models\Coupons::find()->active()->andWhere($where)->one();
        if ($get_result && $this->_validate($get_result) === true){
            $order_total = $order->info['subtotal_exc_tax'];

            $cart = $this->manager->getCart();
/** @var \common\classes\shopping_cart $cartDecorator */
            $cartDecorator = new CartDecorator($cart);
            $products_in_order = $cartDecorator->getProducts();
            $this->products_in_order = $products_in_order;

            if (is_array($products_in_order) && count($products_in_order)) {
              //'quantity', 'final_price', 'id'
              $DISABLE_FOR_SPECIAL =  defined('MODULE_ORDER_TOTAL_COUPON_DISABLE_FOR_SPECIAL') && MODULE_ORDER_TOTAL_COUPON_DISABLE_FOR_SPECIAL=='True';
              if (!empty($get_result->disable_for_special)) {
                $DISABLE_FOR_SPECIAL =  true;
              }

              if ($DISABLE_FOR_SPECIAL || $get_result['restrict_to_products'] || $get_result['restrict_to_categories']
                  || (int)$get_result->products_max_allowed_qty>0 || (int)$get_result->products_id_per_coupon>0
                  || $get_result['exclude_categories']  || $get_result['exclude_products'] ) {

                $valid_product_count = 0;
                if ( (int)$get_result->products_id_per_coupon>0 ){
                    usort($products_in_order,function($a,$b){
                       return $a['price']>$b['price']?1:-1;
                    });
                    $this->products_in_order = $products_in_order;
                }

                $coupon_include_pids = array_map('intval',preg_split('/,/',$get_result['restrict_to_products'],-1,PREG_SPLIT_NO_EMPTY));
                $coupon_include_cids = array_map('intval',preg_split('/,/',$get_result['restrict_to_categories'],-1,PREG_SPLIT_NO_EMPTY));
                $coupon_exclude_pids = array_map('intval',preg_split('/,/',$get_result['exclude_products'],-1,PREG_SPLIT_NO_EMPTY));
                $coupon_exclude_cids = array_map('intval',preg_split('/,/',$get_result['exclude_categories'],-1,PREG_SPLIT_NO_EMPTY));

                $have_white_list = count($coupon_include_pids)>0 || count($coupon_include_cids)>0;
                $have_black_list = count($coupon_exclude_pids)>0 || count($coupon_exclude_cids)>0;
                $total = 0;
                //unset products which don't match restrictions
                foreach( $products_in_order as $_idx=>$product_info ) {

                  if (!empty($product_info['parent'])) {
                    $_pid = intval(\common\helpers\Inventory::get_prid($product_info['parent']));
                  } else {
                    $_pid = intval(\common\helpers\Inventory::get_prid($product_info['id']));
                  }
                  /*if (!empty($product_info['sub_products'])) {
                    $is_valid = false;
                  } else*/
                  if ($product_info['ga']) {
                    $is_valid = false;
                  } elseif ($DISABLE_FOR_SPECIAL && !empty($product_info['special_price']) && $product_info['special_price']>0 ) {
                    $is_valid = false;
                  } else {
                    $is_valid = true;
                    if (!empty($coupon_exclude_cids) || !empty($coupon_include_cids)) {
                      $product_categories = \common\helpers\Product::getCategoriesIdListWithParents($_pid);
                    } else {
                      $product_categories = [];
                    }

                    //whitelist lower prio
                    if ( $have_white_list ) {
                      $is_valid = false;
                      if (count($coupon_include_pids) > 0 && in_array($_pid, $coupon_include_pids)) {
                        $is_valid = true;
                      }
                      if ($is_valid==false && count($coupon_include_cids) > 0 ) {
                        $is_valid = count(array_intersect($product_categories, $coupon_include_cids)) > 0;
                      }
                    }

                    if ( $have_black_list && $is_valid ) {
                      if (count($coupon_exclude_pids) > 0 && in_array($_pid, $coupon_exclude_pids)) {
                        $is_valid = false;
                      }
                      if ($is_valid && count($coupon_exclude_cids) > 0 ) {
                        if ( count(array_intersect($product_categories, $coupon_exclude_cids)) > 0 ) {
                          $is_valid = false;
                        }
                      }
                    }

                  }
                  if ( (int)$get_result->products_id_per_coupon>0 && $valid_product_count>=(int)$get_result->products_id_per_coupon ){
                      $is_valid = false;
                  }

                  if ( !$is_valid ) {
                    unset($products_in_order[$_idx]);
                  } else {
                    $valid_product_count++;
                    if ( intval($get_result['products_max_allowed_qty'])>0 && intval($product_info['quantity'])>intval($get_result['products_max_allowed_qty']) ) {
                        $product_info['quantity'] = intval($get_result['products_max_allowed_qty']);
                        $currencies = \Yii::$container->get('currencies');
                        $product_info['final_price_exc'] = $currencies->calculate_price($product_info['price'], 0, $product_info['quantity']);
                    }
                    $quantity = $product_info['quantity'];
                    $final_price = $product_info['final_price_exc'];

                    //$total += $final_price * $quantity;
                    $total += $final_price;
                    $this->valid_products[$_pid] = [$quantity => $final_price/$quantity];
                    $this->validProducts[] = $product_info;
                  }
                }


                /*
                    if ($get_result['restrict_to_categories']) {
                        $get_result['restrict_to_categories'] = trim($get_result['restrict_to_categories']);
                        if(substr($get_result['restrict_to_categories'], -1) == ','){
                            $get_result['restrict_to_categories'] = trim(substr($get_result['restrict_to_categories'], 0, -1));
                        }
                        foreach ($products_in_order as $products_id => $details) {
                            $cat_query = tep_db_query("select distinct  products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "' and categories_id IN (" . $get_result['restrict_to_categories'] . ")");
                            if (tep_db_num_rows($cat_query) != 0) {
                                $quantity = key($details);
                                $final_price = current($details);
                                $total += ($final_price * $quantity);
                                $this->valid_products[$products_id] = $products_in_order[$products_id];
                            }
                        }

                    }
                    if ($get_result['restrict_to_products']) {
                        $pr_ids = explode(",", $get_result['restrict_to_products']);

                        foreach ($products_in_order as $pid => $details) {
                            $quantity = key($details);
                            $final_price = current($details);
                            if (in_array(\common\helpers\Inventory::get_prid($pid), $pr_ids)) {
                                $total += ($final_price * $quantity);
                                $this->valid_products[$pid] = $products_in_order[$pid];
                            }
                        }
                    }
                    */
                    $order_total = $total;
                }
            }
        }

        if ($get_result['uses_per_shipping']) {
            $order_total += $order->info['shipping_cost_exc_tax'];
        }

        return $order_total;
    }

    function isRestrictedByCountry($get_result){
        if (tep_not_null($get_result['restrict_to_countries'])) {
            if  ($this->billing['country']['id']){
                return !in_array($this->billing['country']['id'], explode(',', $get_result['restrict_to_countries']));
            } else if ($this->delivery['country']['id']){
                return !in_array($this->delivery['country']['id'], explode(',', $get_result['restrict_to_countries']));
            }
        }
        return false;
    }

    public function describe_status_key() {
        return new ModuleStatus('MODULE_ORDER_TOTAL_COUPON_STATUS', 'true', 'false');
    }

    public function describe_sort_key() {
        return new ModuleSortOrder('MODULE_ORDER_TOTAL_COUPON_SORT_ORDER');
    }

    public function configure_keys() {
        return array(
            'MODULE_ORDER_TOTAL_COUPON_STATUS' =>
            array(
                'title' => 'Display Total',
                'value' => 'true',
                'description' => 'Do you want to display the Discount Coupon value?',
                'sort_order' => '1',
                'set_function' => 'tep_cfg_select_option(array(\'true\', \'false\'), ',
            ),
            'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER' =>
            array(
                'title' => 'Sort Order',
                'value' => '9',
                'description' => 'Sort order of display.',
                'sort_order' => '2',
            ),
            'MODULE_ORDER_TOTAL_COUPON_ONE_PER_ORDER_ONLY' =>
            array(
                'title' => 'One coupon per order only.',
                'value' => 'False',
                'description' => 'Allow only 1 coupon per order.',
                'sort_order' => '3',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
            ),
        );
    }

}
