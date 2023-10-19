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

/**
 * VL: if several PERCENT discount is applied then each next discount is applied on original total (ignore previous discount detail) 20%+30%+50% = 100% i.e. free
 * ot_shipping could update total_inc total_exc, total, and tax tax_group so these keys could be negative in order->info
 * nice2have Calc mode - on original total or on discounted: 20+(80*0.3=24)+28 = 72%
 */
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
    /** @prop $discountsDetails cumulative details of  inc/ext per product, coupon and shipping for current order */
    protected $discountsDetails = [];
    /** @prop $totalDetails order total detail: inc/ext, shipping per coupon */
    protected $totalDetails = [];  
    protected $tax_info = [];

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

        $this->tax_info = ['tax' => 0, 'tax_groups' => []];
    }

    function config($data) {
        if (is_array($data)) {
            $this->config = array_merge($this->config, $data);
        }
    }

    protected function getCouponById(int $coupon_id)
    {
        static $_result=[];
        if (isset($_result[$coupon_id])) 
          return $_result[$coupon_id];
        $where = ['coupon_id' => (int) $coupon_id];
        $get_result = \common\models\Coupons::find()->active()->andWhere($where)->one();
        foreach (\common\helpers\Hooks::getList('ot-coupon/get-coupon-by-id') as $filename) {
            include($filename);
        }
        $_result[$coupon_id]=$get_result;
        return $get_result;
    }

    function process($replacing_value = -1, $visible = false) {

        $currencies = \Yii::$container->get('currencies');
        $order = $this->manager->getOrderInstance();

        $this->tax_info = ['tax' => 0, 'tax_groups' => []];

        $this->tax_before_calculation = $order->info['tax'] ?? null;

        $cc_array = [];
        $cart = $this->manager->getCart();
        if (is_array($cart->cc_array)) {
            $cc_array = $cart->cc_array;
            //important reorder $cc_array start with percent with free shipping,  then percent,  then free shipping then fixed
            // do not change else mix coupon (something + free shipping) could get incorrect result
            if (!empty($cc_array)) {
                $q = \common\models\Coupons::find()->select('coupon_code, coupon_id')
                    ->andWhere(['coupon_id' => array_keys($cc_array)])
                    ->orderBy(new \yii\db\Expression('coupon_type="F", free_shipping desc, uses_per_shipping desc, coupon_amount'));
                $cc_array = $q->indexBy('coupon_id')->column();
            }
        }

        $discountSumm = 0;
        $taxDiscountSumm = 0;

        foreach ($cc_array as $id => $code) {
            $this->valid_products = [];
            $this->validProducts = [];
            $this->totalDetails = [];

            $order_total = $this->get_order_total($id);
            $result = $this->calculate_credit($order_total, $id, $code);

            $this->deduction = $result['deduct'];
            $result['tax'] = ($result['deduct'] > 0 ? $this->calculate_tax_deduction($id, $order_total, $this->deduction) : 0);

            if ($result['tax']>0) {
                $result['deduct'] -= $result['tax'];
            }

            $discountSumm += $result['deduct'];
            $taxDiscountSumm += $result['tax'];
            $method = $result['method'];
/*
            if ($method == 'standard-inc') {
                $result['deduct'] -= $result['tax'];
                $discountSumm -= $result['tax'];
            }
  */
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

        if (!empty($this->discountsDetails['tax_groups']) && is_array($this->discountsDetails['tax_groups'])) {
            foreach ($this->discountsDetails['tax_groups'] as $tax_desc => $tax_value) {
                $order->info['tax_groups'][$tax_desc] -= $tax_value;
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

            //if (defined('DISPLAY_PRICE_WITH_TAX') && DISPLAY_PRICE_WITH_TAX == 'true') {
                $order->info['total'] = $order->info['total'] - ($discountSumm + $taxDiscountSumm);
            //} else {
            //    $order->info['total'] = $order->info['total'] - $discountSumm;
            //}
            $order->info['total_inc_tax'] = $order->info['total_inc_tax'] - ($discountSumm + $taxDiscountSumm);
            $order->info['total_exc_tax'] = $order->info['total_exc_tax'] - $discountSumm;

            //VL KOSTYL total didn't have shipping tax :(
            if ((isset($this->discountsDetails['shipping_exc']) && isset($this->discountsDetails['shipping']) &&
                    $this->discountsDetails['shipping_exc'] != $this->discountsDetails['shipping'])) {

                if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                    $order->info['total'] += $this->discountsDetails['shipping'] - $this->discountsDetails['shipping_exc'];
                    $order->info['total_inc_tax'] += $this->discountsDetails['shipping'] - $this->discountsDetails['shipping_exc'];
                }

                /*elseif ((isset($this->processing_order['ot_shipping']) && $this->processing_order['ot_coupon'] < $this->processing_order['ot_shipping'])
                        && (!defined('PRICE_WITH_BACK_TAX') || PRICE_WITH_BACK_TAX != 'True')) {
                    $order->info['total'] -= $this->discountsDetails['shipping'] - $this->discountsDetails['shipping_exc'];
                    $order->info['total_inc_tax'] -= $this->discountsDetails['shipping'] - $this->discountsDetails['shipping_exc'];

                }*/
            }

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
      if (\frontend\design\Info::isTotallyAdmin()) {
        // skip validation as coupon was already applied.

        $orders_id = \Yii::$app->request->get('orders_id', 0);
        if ($orders_id) {
            $chk = \common\models\CouponRedeemTrack::find()->select('order_id')
                    ->andWhere(['coupon_id' => $coupon->coupon_id,
                                'order_id' => $orders_id])
                    ->asArray()->limit(1)->one()
                ;
            if (!empty($chk) ) {
                return $ret;
            }
        }
      }

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

      elseif ($this->isRestrictedByGroup($coupon)){
        $ret = ERROR_NO_INVALID_REDEEM_COUPON;
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
            $gv_redeem_code = trim($collect_data['gv_redeem_code']);
            if (empty($gv_redeem_code)) {
                return array('error' => true, 'message' => ERROR_NO_REDEEM_CODE);
            }

            $cc_array = [];
            $cart = $this->manager->getCart();
            if (is_array($cart->cc_array)
                && defined('MODULE_ORDER_TOTAL_COUPON_ONE_PER_ORDER_ONLY') && MODULE_ORDER_TOTAL_COUPON_ONE_PER_ORDER_ONLY == 'True'
                && !in_array($gv_redeem_code, $cart->cc_array)
                ) {
                $cart->clearCcItems();
            }
            if (is_array($cart->cc_array) ) {
                $cc_array = $cart->cc_array;
            }

            $newCode = true;
            foreach ($cc_array as $code) {
                if ($code == $gv_redeem_code) {
                    $newCode = false;
                }
            }

            if ($newCode) {
                $coupon_result = \common\models\Coupons::getCouponByCode($gv_redeem_code, true);
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
            }
        }
    }

    /**
     * calculates discount for specified amount
     * @param number $order_total applicable part of sub-total (according restriction)
     * @return array ['deduct' => nn.nn method=><free_shipping|standard|standard-inc> ]
     */
    function calculate_credit($order_total, $coupon_id, $coupon_code) {
//debug echo __FILE__ .':' . __LINE__ . "###  " . "\$order_total, $order_total, \$coupon_code $coupon_code <br>\n";
        $order = $this->manager->getOrderInstance();
        $currencies = \Yii::$container->get('currencies');
        $od_amount = 0;
        $result = [];
        if (tep_not_null($coupon_code)) {
            $get_result=$this->getCouponById($coupon_id);
            if ($get_result) {
                if ($this->_validate($get_result) === true) {
                    $this->coupon_code = $get_result['coupon_code'];
                    $this->tax_class = $get_result['tax_class_id'];

                    $order_total_amount = $order->info['subtotal_exc_tax']; // for min order amount check
                    if ($get_result['uses_per_shipping'] || $get_result['free_shipping']) {
                        $order_total_amount += $order->info['shipping_cost_exc_tax'];
                    }

                    if ($get_result->spend_partly == 1) {
                        $customer_id = (isset($order->customer['customer_id']) ? (int)$order->customer['customer_id'] : 0);
                        $redeem = \common\models\CouponRedeemTrack::find()->select('sum(spend_amount) as spend_amount')->andWhere(['coupon_id' => $get_result->coupon_id, 'customer_id' => $customer_id])->asArray()->one();
                        $coupon_amount = $get_result['coupon_amount'] - $redeem['spend_amount'];
                        if ($coupon_amount < 0) {
                            $coupon_amount = 0;
                        }
                    } else {
                        $coupon_amount = $get_result['coupon_amount'];
                    }
                    /** @var numeric $c_deduct fixed discount (left) amount */
                    $c_deduct = $coupon_amount * $currencies->get_market_price_rate($get_result['coupon_currency'], DEFAULT_CURRENCY);//2check - seems double conversion
                    
                    $result['method'] = 'standard';
                    $get_result['coupon_minimum_order'] *= $currencies->get_market_price_rate($get_result['coupon_currency'], DEFAULT_CURRENCY);

                    if ($get_result['coupon_minimum_order'] <= $order_total_amount /*$order_total*/) {

                        $od_amount = 0;
                        if ($get_result['coupon_type'] != 'P') {
                            /*if ($get_result['flag_with_tax'] && $this->tax_class>0 ) {
                                $taxation = $this->getTaxValues($this->tax_class);
                                $tax_rate = $taxation['tax'];
                                $c_deduct = \common\helpers\Tax::get_untaxed_value($c_deduct, $tax_rate);

                            } else*/
                            if ($get_result['flag_with_tax'] && $this->tax_class!=0) { //fixed amount - cant calculate ex vat amount
                                $result['method'] = 'standard-inc';
                            }
                            
                            $od_amount = $c_deduct;
                        } else { // percent ($get_result['coupon_amount'])
                            if ($get_result['free_shipping']) {
                                // mix - discount on subtotal + shipping

                                $od_amount = max(0, $order_total-$order->info['shipping_cost_exc_tax']) * $get_result['coupon_amount'] / 100;
/*
                                if ($get_result['coupon_type'] == 'P') {
                                    $od_amount = max(0, $order_total-$order->info['shipping_cost_inc_tax']) * $get_result['coupon_amount'] / 100;
                                } else {
                                    $od_amount = max(0, $order_total-$this->totalDetails['ship_inc']) * $get_result['coupon_amount'] / 100;
                                }
*/
                            } else {
                                $od_amount = $order_total * $get_result['coupon_amount'] / 100;
                            }
                        }

                        if ( $get_result['free_shipping'] ) {
                            $od_amount += $this->totalDetails['ship_inc'];
                        }
                    }

                }
            }

            //check max discount
            if ($result['method'] == 'standard-inc' && !empty($this->totalDetails['inc'])) {
                $_max_amount = $this->totalDetails['inc'];
            } else {
                $_max_amount =  $order_total;
            }
            //2do  total discount/$_max_amount  > order total

            if ($od_amount > $_max_amount) {
                $od_amount = $_max_amount;
            }
            if (!isset($this->discountsDetails['inc_total'])) {
                $this->discountsDetails['inc_total'] = 0;
                $this->discountsDetails['exc_total'] = 0;
                $this->discountsDetails['tax_groups'] = [];
                $this->discountsDetails['coupons'][$coupon_id] = ['inc' => 0, 'exc' => 0];
            }

            //to check mix in/exc tax totalDetails


            $this->discountsDetails['inc_total'] += $od_amount;
            $this->discountsDetails['exc_total'] += $od_amount;
            $this->discountsDetails['coupons'][$coupon_id] = [
                    'inc' => $od_amount,
                    'exc' => $od_amount,
                    'tax_groups' => [],
                    'type' => $get_result['coupon_type']
                ];

            // update all discount details

            $_percent = $left = 0;
            if ($get_result['coupon_type'] != 'P') {
                $left = $od_amount;
            } else {
                $_percent = $get_result['coupon_amount'] / 100;
            }

            $ship_taxation = false;
            if (isset($get_result['tax_class_id']) && $get_result['tax_class_id'] != 0) {
                $shipping = $this->manager->getShipping();
                if (is_array($shipping)) {
                    $sModule = $this->manager->getShippingCollection()->get($shipping['module']);
                    if ($sModule) {
                        $shipping_tax_class_id = $sModule->tax_class;
                    }
                    $ship_taxation = $this->getTaxValues($shipping_tax_class_id);
                }
            }

            // tax - deduct free shipping (else % coupon will have incorrect amount
            if ($get_result['free_shipping'] && isset($get_result['tax_class_id']) && $get_result['tax_class_id'] != 0) {
                if (!empty($ship_taxation['tax_description'])) {
                    $shipping_tax_desc = $ship_taxation['tax_description'];
                    if ($get_result['coupon_type'] == 'P') {
                        $shipping_tax_total = $order->info['shipping_cost_inc_tax'] - $order->info['shipping_cost_exc_tax'];
                    } else {
                        $shipping_tax_total = $this->totalDetails['ship_inc'] - $this->totalDetails['ship_exc'];
                    }
//php8 init
                    if (!isset($this->discountsDetails['shipping'])) {
                        $this->discountsDetails['shipping'] = 0;
                        $this->discountsDetails['shipping_exc'] = 0;
                        $this->discountsDetails['shipping_tax'] = [];
                    }
                    if (!isset($this->discountsDetails['coupons'][$coupon_id]['tax_groups'][$shipping_tax_desc])) {
                        $this->discountsDetails['coupons'][$coupon_id]['tax_groups'][$shipping_tax_desc] = 0;
                    }
                    if (!isset($this->discountsDetails['tax_groups'][$shipping_tax_desc])) {
                        $this->discountsDetails['tax_groups'][$shipping_tax_desc] = 0;
                    }
//php8 init

                    $this->discountsDetails['shipping'] = $this->totalDetails['ship_inc'];
                    $this->discountsDetails['shipping_exc'] = $this->totalDetails['ship_exc'];
                    $this->discountsDetails['shipping_tax'][$shipping_tax_desc] = $shipping_tax_total;

                    $this->discountsDetails['coupons'][$coupon_id]['exc'] -= $shipping_tax_total;
                    $this->discountsDetails['exc_total'] -= $shipping_tax_total;


                    $this->discountsDetails['tax_groups'][$shipping_tax_desc] += $shipping_tax_total;
                    $this->discountsDetails['coupons'][$coupon_id]['tax_groups'][$shipping_tax_desc] += $shipping_tax_total;
                    // shipping is added to total discount, but shouldn't be calculated as part of FIXED coupon amount
                    if ($left) {
                        $left -= $this->discountsDetails['shipping'];
                    }

                }
            }

            if ($get_result['coupon_amount']>0) {
                if ($this->totalDetails['restricted'] && is_array($this->validProducts)) {
                    $prods = $this->validProducts;

                } else {
                    $prods = $this->products_in_order;
                    if (is_array($prods)) {
                        foreach ($prods as $k => $product_info) {
                            $coupon_tax = [];
                            $tax_desc = '';
                            if (!empty($product_info['tax_class_id'])) {
                                $taxation = $this->getTaxValues($product_info['tax_class_id']);
                                $tax_desc = $taxation['tax_description'];
                            }

                            if ($product_info['tax_rate'] > 0 && defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                                $final_inc = $product_info['final_price_inc'];
                                $final_price = \common\helpers\Tax::reduce_tax_always($final_inc, $product_info['tax_rate']);
                            } elseif ($product_info['tax_rate'] > 0 && defined('DISPLAY_PRICE_WITH_TAX') && DISPLAY_PRICE_WITH_TAX == 'false') {
                                $final_price = $product_info['final_price_exc'];
                                $final_inc = \common\helpers\Tax::add_tax_always($final_price, $product_info['tax_rate']);
                            } elseif ($product_info['tax_class_id'] > 0 && !empty($taxation['tax'])) {
                                $final_price = $product_info['final_price_exc'];
                                $final_inc = \common\helpers\Tax::add_tax_always($final_price, $taxation['tax']);
                            } else {
                                $final_price = $product_info['final_price_exc'];
                                $final_inc = $product_info['final_price_inc'];
                            }
//echo "#### \$product_info \$final_price $final_price \$final_inc $final_inc <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($product_info, true) ."</PRE>";
                            $prods[$k]['coupon_total_inc'] = $final_inc;
                            if (($get_result['coupon_type'] != 'P') && !empty($this->discountsDetails['products'][$product_info['id']]['inc'])) {
                                $prods[$k]['coupon_total_inc'] -= $this->discountsDetails['products'][$product_info['id']]['inc'];
                            }
                            $prods[$k]['coupon_total_exc'] = $final_price;
                            if (($get_result['coupon_type'] != 'P') && !empty($this->discountsDetails['products'][$product_info['id']]['exc'])) {
                                $prods[$k]['coupon_total_exc'] -= $this->discountsDetails['products'][$product_info['id']]['exc'];
                            }

                            if (!empty($product_info['tax_class_id']) && !empty($tax_desc)) {
                                $coupon_tax[$tax_desc] += $prods[$k]['coupon_total_inc'] - $prods[$k]['coupon_total_exc'];
                            }
                            $prods[$k]['coupon_tax_groups'] = $coupon_tax;
                        }
                    }
                }
                if (is_array($prods)) {
                    foreach ($prods as $p) {
                        if (!isset($this->discountsDetails['products'][$p['id']])) {
                            $this->discountsDetails['products'][$p['id']]= ['inc' => 0, 'exc' => 0, 'tax_groups' => []];
                        }
                        if ($left) {
                            $this->discountsDetails['products'][$p['id']]['inc'] += ($left>$p['coupon_total_inc']? $p['coupon_total_inc']:$left);
                            $this->discountsDetails['products'][$p['id']]['exc'] += ($left>$p['coupon_total_inc']? $p['coupon_total_inc']:$left);
                        } else {
                            $this->discountsDetails['products'][$p['id']]['inc'] += $p['coupon_total_inc']*$_percent;
                            $this->discountsDetails['products'][$p['id']]['exc'] += $p['coupon_total_inc']*$_percent;
                        }

                        if (!empty($p['coupon_tax_groups']) && is_array($p['coupon_tax_groups'])) {
                            foreach ($p['coupon_tax_groups'] as $k => $v) {
                                if (!isset($this->discountsDetails['products'][$p['id']]['tax_groups'][$k])) {
                                    $this->discountsDetails['products'][$p['id']]['tax_groups'][$k] = 0;
                                }
                                if (!isset($this->discountsDetails['coupons'][$coupon_id]['tax_groups'][$k])) {
                                    $this->discountsDetails['coupons'][$coupon_id]['tax_groups'][$k] = 0;
                                }
                                if (!isset($this->discountsDetails['tax_groups'][$k])) {
                                    $this->discountsDetails['tax_groups'][$k] = 0;
                                }
                                if ($left) {
                                    $_tmp = (($p['coupon_total_inc']<=0 || $left>$p['coupon_total_inc']) ? $v:
                                        $v*$left/$p['coupon_total_inc']);
                                    $this->discountsDetails['products'][$p['id']]['tax_groups'][$k] += $_tmp;
                                    $this->discountsDetails['products'][$p['id']]['exc'] -= $_tmp;
                                    
                                    $this->discountsDetails['coupons'][$coupon_id]['tax_groups'][$k] += $_tmp;
                                    $this->discountsDetails['coupons'][$coupon_id]['exc'] -= $_tmp;
                                    $this->discountsDetails['tax_groups'][$k] += $_tmp;
                                    $this->discountsDetails['exc_total'] -= $_tmp;
                                } else {
                                    if (true) {
                                        $_tmp = $v * $_percent;
                                    }

                                    $this->discountsDetails['products'][$p['id']]['tax_groups'][$k] += $_tmp;
                                    $this->discountsDetails['products'][$p['id']]['exc'] -= $_tmp;

                                    $this->discountsDetails['coupons'][$coupon_id]['tax_groups'][$k] += $_tmp;
                                    $this->discountsDetails['coupons'][$coupon_id]['exc'] -= $_tmp;
                                    $this->discountsDetails['tax_groups'][$k] += $_tmp;
                                    $this->discountsDetails['exc_total'] -= $_tmp;
                                }
                            }
                        }

                        if ($left) {
                            $left -= $p['coupon_total_inc'];
                        }
                        if ($get_result['coupon_type'] != 'P' && $left<=0) {
                            break;
                        }
                    }
                }
            }
            //shipping
            if (($left>0 || $_percent>0) && ($get_result['uses_per_shipping'] || $get_result['free_shipping']) && $order->info['shipping_cost_inc_tax']>0) {
                if (!isset($this->discountsDetails['shipping'])) {
                    $this->discountsDetails['shipping'] = 0;
                    $this->discountsDetails['shipping_exc'] = 0;
                    $this->discountsDetails['shipping_tax'] = [];
                }
                if ($left) {
                    $this->discountsDetails['shipping'] += ($left>$order->info['shipping_cost_inc_tax']? $order->info['shipping_cost_inc_tax']:$left);
                    $this->discountsDetails['shipping_exc'] += ($left>$order->info['shipping_cost_inc_tax']? $order->info['shipping_cost_inc_tax']:$left);
                } else {
                    $this->discountsDetails['shipping'] += $order->info['shipping_cost_inc_tax'] * $_percent;
                    $this->discountsDetails['shipping_exc'] += $order->info['shipping_cost_inc_tax'] * $_percent;
                }

                if (isset($get_result['tax_class_id']) && $get_result['tax_class_id'] != 0) {
                    $shipping = $this->manager->getShipping();
                    if (is_array($shipping)) {
                        $sModule = $this->manager->getShippingCollection()->get($shipping['module']);
                        if ($sModule) {
                            $shipping_tax_class_id = $sModule->tax_class;
                        }
                        $taxation = $this->getTaxValues($shipping_tax_class_id);
                        if (!empty($taxation['tax_description'])) {
                            $shipping_tax_desc = $taxation['tax_description'];
                            $shipping_tax_total = $order->info['shipping_cost_inc_tax'] - $order->info['shipping_cost_exc_tax'];
                            //$shipping_tax_total = $this->totalDetails['ship_inc'] - $this->totalDetails['ship_exc'];// percent - on original amount
                            if (!isset($this->discountsDetails['shipping_tax'][$shipping_tax_desc])) {
                                $this->discountsDetails['shipping_tax'][$shipping_tax_desc] = 0;
                            }
                            if ($left) {
                                $_tmp = (
                                    ($left > $order->info['shipping_cost_inc_tax']) ?
                                        $shipping_tax_total :
                                        $shipping_tax_total * $left / $order->info['shipping_cost_inc_tax']);
                                $this->discountsDetails['shipping_tax'][$shipping_tax_desc] += $_tmp;
                                $this->discountsDetails['shipping_exc'] -= $_tmp;

                                $this->discountsDetails['coupons'][$coupon_id]['tax_groups'][$shipping_tax_desc] += $_tmp;
                                $this->discountsDetails['coupons'][$coupon_id]['exc'] -= $_tmp;
                                $this->discountsDetails['tax_groups'][$shipping_tax_desc] += $_tmp;
                                $this->discountsDetails['exc_total'] -= $_tmp;

                            } else {
                                $this->discountsDetails['shipping_tax'][$shipping_tax_desc] += $shipping_tax_total * $_percent;
                                $this->discountsDetails['shipping_exc'] -= $shipping_tax_total * $_percent;

                                $this->discountsDetails['coupons'][$coupon_id]['tax_groups'][$shipping_tax_desc] += $shipping_tax_total * $_percent;
                                $this->discountsDetails['coupons'][$coupon_id]['exc'] -= $shipping_tax_total * $_percent;
                                $this->discountsDetails['tax_groups'][$shipping_tax_desc] += $shipping_tax_total * $_percent;
                                $this->discountsDetails['exc_total'] -= $shipping_tax_total * $_percent;
                            }
                        }

                    }
                }
                $left -= $order->info['shipping_cost_inc_tax'];
            }


        }
        $result['deduct'] = $od_amount;
//debug echo "#### \$this->discountsDetails \$od_amount $od_amount \$left $left <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($this->discountsDetails, true) ."</PRE>";

        return $result;
    }

/**
 *
 * @param int $coupon_id
 * @param numeric $amount total EXC TAX amount (only discount applicable)
 * @param numeric $od_amount discount amount (could be inc VAT)
 * @return type
 */
    function calculate_tax_deduction($coupon_id, $amount, $od_amount) {
        $ret = 0;
        if (!empty($this->discountsDetails['coupons'][$coupon_id])) {
            $ret = $this->discountsDetails['coupons'][$coupon_id]['inc'] - $this->discountsDetails['coupons'][$coupon_id]['exc'];
        }

        return $ret;
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

/**
 * total according categories, products,  and q-ty restriction. Fill in products_in_order, validProducts, totalDetails class properties.
 * @param int $coupon_id
 * @return numeric
 */
    function get_order_total($coupon_id) {

        $order = $this->manager->getOrderInstance();
        $order_total = 0;
        $order_totals = [
          'exc' => 0,
          'inc' => 0,
          'ship_exc' => 0,
          'ship_inc' => 0,
          'tax_groups' => [],
        ];
        $get_result=$this->getCouponById($coupon_id);
        if ($get_result && $this->_validate($get_result) === true) {
            $order_total = $order->info['subtotal_exc_tax']; //rudiment

            $cart = $this->manager->getCart();
            /** @var \common\classes\shopping_cart $cartDecorator */
            $cartDecorator = new CartDecorator($cart);
            $products_in_order = $cartDecorator->getProducts();
            $this->products_in_order = $products_in_order;
            $restricted = false;

            //Apply discount only to the first cheapest products and other product related restriction
            if (is_array($products_in_order) && count($products_in_order)) {
                $DISABLE_FOR_SPECIAL = defined('MODULE_ORDER_TOTAL_COUPON_DISABLE_FOR_SPECIAL') && MODULE_ORDER_TOTAL_COUPON_DISABLE_FOR_SPECIAL == 'True';
                if (!empty($get_result->disable_for_special)) {
                    $DISABLE_FOR_SPECIAL = true;
                }

                if ($DISABLE_FOR_SPECIAL || $get_result['restrict_to_products'] || $get_result['restrict_to_categories'] || $get_result['restrict_to_manufacturers'] || (int) $get_result->products_max_allowed_qty > 0 || (int) $get_result->products_id_per_coupon > 0 || $get_result['exclude_categories'] || $get_result['exclude_products']) {

                    $valid_product_count = 0;
                    $restricted = true;
                    if ((int) $get_result->products_id_per_coupon > 0) {
                        usort($products_in_order, function ($a, $b) {
                            return $a['price'] > $b['price'] ? 1 : -1;
                        });
                        $this->products_in_order = $products_in_order;
                    }

                    $coupon_include_pids = array_map('intval', preg_split('/,/', $get_result['restrict_to_products'], -1, PREG_SPLIT_NO_EMPTY));
                    $coupon_include_cids = array_map('intval', preg_split('/,/', $get_result['restrict_to_categories'], -1, PREG_SPLIT_NO_EMPTY));
                    $coupon_include_mids = array_map('intval', preg_split('/,/', $get_result['restrict_to_manufacturers'], -1, PREG_SPLIT_NO_EMPTY));
                    $coupon_exclude_pids = array_map('intval', preg_split('/,/', $get_result['exclude_products'], -1, PREG_SPLIT_NO_EMPTY));
                    $coupon_exclude_cids = array_map('intval', preg_split('/,/', $get_result['exclude_categories'], -1, PREG_SPLIT_NO_EMPTY));

                    $have_white_list = count($coupon_include_pids) > 0 || count($coupon_include_cids) > 0 || count($coupon_include_mids) > 0;
                    $have_black_list = count($coupon_exclude_pids) > 0 || count($coupon_exclude_cids) > 0;
                    $total = 0;
                    $currencies = \Yii::$container->get('currencies');
                    //unset products which don't match restrictions, calculate totals
                    foreach ($products_in_order as $_idx => $product_info) {
                        //check the product is valid
                        if (!empty($product_info['parent'])) {
                            $_pid = intval(\common\helpers\Inventory::get_prid($product_info['parent']));
                        } else {
                            $_pid = intval(\common\helpers\Inventory::get_prid($product_info['id']));
                        }
                        /* if (!empty($product_info['sub_products'])) {
                          $is_valid = false;
                          } else */
                        if ($product_info['ga']) {
                            $is_valid = false;
                        } elseif ($DISABLE_FOR_SPECIAL && !empty($product_info['special_price']) && $product_info['special_price'] > 0) {
                            $is_valid = false;
                        } else {
                            $is_valid = true;
                            if (!empty($coupon_exclude_cids) || !empty($coupon_include_cids)) {
                                $product_categories = \common\helpers\Product::getCategoriesIdListWithParents($_pid);
                            } else {
                                $product_categories = [];
                            }

                            //whitelist lower prio
                            if ($have_white_list) {
                                $is_valid = false;
                                if (count($coupon_include_pids) > 0 && in_array($_pid, $coupon_include_pids)) {
                                    $is_valid = true;
                                }
                                if ($is_valid == false && count($coupon_include_cids) > 0) {
                                    $is_valid = count(array_intersect($product_categories, $coupon_include_cids)) > 0;
                                }
                                if ($is_valid == false && count($coupon_include_mids) > 0 && in_array(\common\helpers\Product::get_products_info($_pid, 'manufacturers_id'), $coupon_include_mids)) {
                                    $is_valid = true;
                                }
                            }

                            if ($have_black_list && $is_valid) {
                                if (count($coupon_exclude_pids) > 0 && in_array($_pid, $coupon_exclude_pids)) {
                                    $is_valid = false;
                                }
                                if ($is_valid && count($coupon_exclude_cids) > 0) {
                                    if (count(array_intersect($product_categories, $coupon_exclude_cids)) > 0) {
                                        $is_valid = false;
                                    }
                                }
                            }
                        }

                        if ((int) $get_result->products_id_per_coupon > 0 && $valid_product_count >= (int) $get_result->products_id_per_coupon) {
                            $is_valid = false;
                        }

                        if (!$is_valid) {
                            unset($products_in_order[$_idx]);
                        } else {

                            $valid_product_count++;
                            $product_info['full_quantity'] = $product_info['quantity'];
                            $final_price = $orig_qty_final_price = $product_info['final_price_exc'];
                            if (intval($get_result['products_max_allowed_qty']) > 0 && intval($product_info['quantity']) > intval($get_result['products_max_allowed_qty'])) {
                                $product_info['quantity'] = intval($get_result['products_max_allowed_qty']);
                                $final_price = $currencies->calculate_price($product_info['price'], 0, $product_info['quantity']);
                            }
                            $quantity = $product_info['quantity'];
                            

                            if ($product_info['tax_rate'] > 0) {
                                if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                                    $final_inc = $final_price;
                                    $final_price = \common\helpers\Tax::reduce_tax_always($final_price, $product_info['tax_rate']);
                                    $orig_qty_final_price_inc = $orig_qty_final_price;
                                    $orig_qty_final_price = \common\helpers\Tax::reduce_tax_always($orig_qty_final_price, $product_info['tax_rate']);
                                } else {
                                    $final_inc = $currencies->calculate_price($final_price, $product_info['tax_rate'], 1);
                                    $orig_qty_final_price_inc = $currencies->calculate_price($orig_qty_final_price, $product_info['tax_rate'], 1);
                                }
                            } else {
                                $final_inc = $final_price;
                            }
                            // check total discount by prev. coupon - for fixed coupon only
                            if ($get_result['coupon_type']!='P' && !empty($this->discountsDetails['products'][$product_info['id']]) ) {

                                //if ($orig_qty_final_price-$final_price < $this->discountsDetails['products'][$product_info['id']]['exc']) {}
                                if ($orig_qty_final_price_inc-$final_inc < $this->discountsDetails['products'][$product_info['id']]['inc']) {
                                    $final_inc = $orig_qty_final_price_inc - $this->discountsDetails['products'][$product_info['id']]['inc'];
                                    $final_price = $orig_qty_final_price - $this->discountsDetails['products'][$product_info['id']]['exc'];
                                }
                            }
                            if ($final_inc<=0) {
                                unset($products_in_order[$_idx]);
                                continue;
                            }
///echo __FILE__ .':' . __LINE__ . " !!! " . $this->discountsDetails['products'][$product_info['id']]['exc'].  " rest \$final_inc $final_inc \$final_price $final_price \$orig_qty_final_price_inc $orig_qty_final_price_inc \$orig_qty_final_price $orig_qty_final_price <br>\n";
                            $order_totals['exc'] += $final_price;
                            $order_totals['inc'] += $final_inc;

                            $coupon_tax = [];

                            if (!empty($product_info['tax_class_id'])) {
                                $taxation = $this->getTaxValues($product_info['tax_class_id']);
                                $tax_desc = $taxation['tax_description'];
                                if (!isset($order_totals['tax_groups'][$tax_desc])) {
                                    $order_totals['tax_groups'][$tax_desc] = 0;
                                }
                                $order_totals['tax_groups'][$tax_desc] += $final_inc - $final_price;
                                $coupon_tax[$tax_desc] = $final_inc - $final_price;
                            }
                            $product_info['coupon_total_inc'] = $final_inc;
                            $product_info['coupon_total_exc'] = $final_price;
                            $product_info['coupon_tax_groups'] = $coupon_tax;

                            //$total += $final_price * $quantity;
                            $total += $final_price;
                            $this->valid_products[$_pid] = [$quantity => $final_price / $quantity];
                            $this->validProducts[] = $product_info;

                        }
                    }

                    $order_total = $total;

                    if (($get_result['uses_per_shipping'] || $get_result['free_shipping']) && empty($this->validProducts)) {
                        $get_result['uses_per_shipping'] = 0;
                        $get_result['free_shipping'] = 0;
                    }

                } else { // no restrictions
                    if ($get_result['coupon_type']!='P' && !empty($this->discountsDetails['inc_total']) ) {
                        $order_totals = [
                          'exc' => $order->info['subtotal_exc_tax'] - $this->discountsDetails['exc_total'],
                          'inc' => $order->info['subtotal_inc_tax'] - $this->discountsDetails['inc_total']??0,
                          'tax_groups' => $order->info['tax_groups'],
                        ];
                        if (is_array($order->info['tax_groups']) && is_array($this->discountsDetails['tax_groups'])) {
                            foreach ($order->info['tax_groups'] as $k => $v) {
                                if (!empty($this->discountsDetails['tax_groups'][$k])) {
                                    $order_totals['tax_groups'][$k] -= $this->discountsDetails['tax_groups'][$k];
                                }
                            }
                        }
/* 2test */
                        if (!empty($this->discountsDetails['shipping'])) {
                            $order_totals['inc'] += $this->discountsDetails['shipping'];
                            $order_totals['exc'] += $this->discountsDetails['shipping_exc'];
                            if (!empty($this->discountsDetails['shipping_tax']) && is_array($this->discountsDetails['shipping_tax'])) {
                                foreach ($this->discountsDetails['shipping_tax'] as $k => $v) {
                                    if (!empty($order_totals['tax_groups'][$k])) {
                                        $order_totals['tax_groups'][$k] += $this->discountsDetails['tax_groups'][$k];
                                    } else {
                                        $order_totals['tax_groups'][$k] = $this->discountsDetails['tax_groups'][$k];
                                    }
                                }
                            }
                        }
/* 2test eof */
                    } else {
                        $order_totals = [
                          'exc' => $order->info['subtotal_exc_tax'],
                          'inc' => $order->info['subtotal_inc_tax'],
                          'tax_groups' => $order->info['tax_groups'],
                        ];
                    }
                }
            }

        }
//if ($restricted)echo "#### \$order_totals before shipping <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($order_totals, true) ."</PRE>";

        if ($get_result['uses_per_shipping'] || $get_result['free_shipping']) {
            $order_total += $order->info['shipping_cost_exc_tax'];

            $order_totals['exc'] += $order->info['shipping_cost_exc_tax'];
            $order_totals['inc'] += $order->info['shipping_cost_inc_tax'];
            $order_totals['ship_exc'] = $order->info['shipping_cost_exc_tax'];
            $order_totals['ship_inc'] = $order->info['shipping_cost_inc_tax'];

            if ($get_result['coupon_type']!='P' && !empty($this->discountsDetails['shipping']) ) {
                $order_totals['ship_exc'] -= $this->discountsDetails['shipping_exc'];
                $order_totals['exc'] -= $this->discountsDetails['shipping_exc'];
                if ($order_totals['ship_exc']<0) {
                    $order_totals['exc'] -= $order_totals['ship_exc'];
                    $order_totals['ship_exc'] = 0;
                }
                $order_totals['ship_inc'] -= $this->discountsDetails['shipping'];
                $order_totals['inc'] -= $this->discountsDetails['shipping'];
                if ($order_totals['ship_inc']<0) {
                    $order_totals['inc'] -= $order_totals['ship_inc'];
                    $order_totals['ship_inc'] = 0;
                }

            }

            $shipping = $this->manager->getShipping();
            if (is_array($shipping)
                && (
                (isset($this->processing_order['ot_shipping']) && $this->processing_order['ot_coupon'] < $this->processing_order['ot_shipping'])
                    //ot_shipping adds shipping tax to 'tax_groups' so if not yet processed - we add shipping tax to appropriate group.
                    // no restriction tax group are taken from order info directly
                || $restricted )
                ) {

                $sModule = $this->manager->getShippingCollection()->get($shipping['module']);
                if ($sModule) {
                    $shipping_tax_class_id = $sModule->tax_class;
                }
                $taxation = $this->getTaxValues($shipping_tax_class_id);
                $shipping_tax = $taxation['tax'];
                $shipping_tax_desc = $taxation['tax_description'];
                $order_totals['tax_groups'][$shipping_tax_desc] += $order_totals['ship_inc'] - $order_totals['ship_exc'];
            }

        }

        $this->totalDetails = $order_totals;
        $this->totalDetails['restricted'] = $restricted;
        if (!empty($get_result['flag_with_tax']) && isset($get_result['tax_class_id']) && $get_result['tax_class_id']!=0) {
            $order_total = $order_totals['inc'];
        } else {
            $order_total = $order_totals['exc'];
        }
//debug echo "#### \$order_total $order_total \$order_totals<PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($order_totals, true) ."</PRE>";
        return $order_total;
    }

    function isRestrictedByGroup($coupon_result) {
        if (!empty($coupon_result['coupon_groups'])) {
            $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
            return ($customer_groups_id > 0 && !in_array($customer_groups_id, explode(',', $coupon_result['coupon_groups'])));
        }
        return false;
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
