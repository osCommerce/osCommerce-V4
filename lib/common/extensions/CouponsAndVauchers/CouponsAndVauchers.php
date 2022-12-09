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

namespace common\extensions\CouponsAndVauchers;

use Yii;
use common\classes\order_total;

// Coupons and Vauchers
class CouponsAndVauchers extends \common\classes\modules\ModuleExtensions
{

    public static function getDescription() {
        return 'This extension makes it possible to use coupons and vouchers while purchasing.';
    }
    
    public static function allowed() {
        return self::enabled();
    }
    
    public static function orderCouponVoucher($gv_redeem_code) {
        if (!self::allowed()) {
            return '';
        }
        return \common\extensions\CouponsAndVauchers\Render::widget(['template' => 'coupons-and-vauchers-order.tpl', 'params' => [
                    'gv_redeem_code' => $gv_redeem_code,
        ]]);
    }
    
    public static function orderEditCouponVoucher() {
        if (!self::allowed()) {
            return '';
        }
      /** @var order_total $order_total_modules */
        global $order_total_modules, $cart;

        if ((isset($_POST['action']) && $_POST['action'] == 'apply_coupon')) {
            if (isset($_POST['gv_redeem_code']) && isset($_POST['gv_redeem_code']['coupon']) && !empty($_POST['gv_redeem_code']['coupon'])) {
                $couponCode = $_POST['gv_redeem_code']['coupon'];
                $_POST['gv_redeem_code'] = $couponCode;
                $cart->clearTotalKey('ot_coupon');
                $cart->clearHiddenModule('ot_coupon');
                $order_total_modules->collect_posts('ot_coupon');
                if(isset($_POST['pos'])){
                    $_POST['gv_redeem_code'] = $couponCode;
                    $order_total_modules->collect_posts('ot_gv');
                }
            } else if (isset($_POST['gv_redeem_code']) && isset($_POST['gv_redeem_code']['gv']) && !empty($_POST['gv_redeem_code']['gv'])) {
                $_POST['gv_redeem_code'] = $_POST['gv_redeem_code']['gv'];
                $order_total_modules->collect_posts('ot_gv');
            }
            $cart->setAdjusted();
        } else {
            if (isset($_POST['gv_redeem_code']) && isset($_POST['gv_redeem_code']['coupon'])) {
                $_POST['gv_redeem_code'] = '';
            }
            if (isset($_POST['gv_redeem_code']) && isset($_POST['gv_redeem_code']['gv'])) {
                $_POST['gv_redeem_code'] = '';
            }
            $order_total_modules->collect_posts();
        }
    }

    public static function cartDiscountCoupon($manager) {
        if (!self::allowed()) {
            return '';
        }
        //global $cc_id;
        
        $messageStack = \Yii::$container->get('message_stack');
        $message_discount_coupon = '';
        if ($messageStack->size('cart_discount_coupon') > 0) {
            $message_discount_coupon = $messageStack->output('cart_discount_coupon');
        }
        $coupon = Yii::$app->request->get('coupon', '');
        return \common\extensions\CouponsAndVauchers\Render::widget(['template' => 'discount-coupon.tpl', 'params' => [
                    'message_discount_coupon' => $message_discount_coupon,
                    'popup' => ($coupon == 'applying' && mb_strlen($message_discount_coupon)),
                    'gv_redeem_code' => '',//$manager->getCouponName(),
        ]]);
    }

    public static function cartGiftCertificate($manager) {
        if (!self::allowed()) {
            return '';
        }
        $messageStack = \Yii::$container->get('message_stack');
        $message_discount_gv = '';
        if ($messageStack->size('cart_discount_gv') > 0) {
            $message_discount_gv = $messageStack->output('cart_discount_gv');
        }
        $ot_gv_data = array(
            'message_discount_gv' => $message_discount_gv,
        );

        return \common\extensions\CouponsAndVauchers\Render::widget(['template' => 'gift-certificate.tpl', 'params' => [$ot_gv_data]]);
    }

    public static function cartCreditAmount($manager) {
        if (!self::allowed()) {
            return '';
        }
        $messageStack = \Yii::$container->get('message_stack');
        $currencies = \Yii::$container->get('currencies');
        $message_discount_gv = '';
        if ($messageStack->size('cart_discount_gv') > 0) {
            $message_discount_gv = $messageStack->output('cart_discount_gv');
        }
        $ot_gv_data = array(
            'can_apply_gv_credit' => false,
            'message_discount_gv' => $message_discount_gv,
            'credit_amount' => '',
            'credit_gv_in_use' => $manager->has('cot_gv'),
            'cot_gv_amount' => $manager->getCreditModules()['custom_gv_amount'],
        );
        if (defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true' && $manager->isCustomerAssigned()) {
            $customer = $manager->getCustomersIdentity();
            if ($customer->credit_amount){
                $ot_gv_data['can_apply_gv_credit'] = true;
                $ot_gv_data['credit_amount'] = $currencies->format($customer->credit_amount);
            }
        }

        return \common\extensions\CouponsAndVauchers\Render::widget(['template' => 'credit-amount.tpl', 'params' => $ot_gv_data]);
    }

    public static function checkoutCouponVoucher($credit_modules, $id = 0) {
        if (!self::allowed()) {
            return '';
        }
        return \common\extensions\CouponsAndVauchers\Render::widget(['template' => 'checkout-discount-coupon.tpl', 'params' => [
                    'credit_modules' => $credit_modules,
                    'id' => $id,
        ]]);
    }
    
    public static function updateCartFactory($goto) {
        if (!self::allowed()) {
            return '';
        }
        $post = $_POST; // not Yii::$app->request->post() because of manual settings of $_POST[] for apply_coupon link

        $manager = \common\services\OrderManager::loadManager();
        if ( strpos(Yii::$app->controller->id, 'quote')===0 ) {
            global $quote;
            $manager->loadCart($quote);
        }
        if (Yii::$app->request->post('gv_redeem_code') === ''){
            $manager->remove('cot_gv');
            $manager->remove('cc_id');
            $manager->remove('cc_code');
        }
        $credit_modules_result = $manager->totalCollectPosts($post);
        
        if ( isset($credit_modules_result['ot_coupon']) && isset($credit_modules_result['ot_coupon']['message']) ){
            $messageStack = \Yii::$container->get('message_stack');
            $_msg = '<span class="msb-message">' . $credit_modules_result['ot_coupon']['message'] . '</span>';
            if (!empty($credit_modules_result['ot_coupon']['description'])) {
              $_msg = '<span class="msb-desc msb-coupon-desc" style="display:none">' . $credit_modules_result['ot_coupon']['description'] . ' </span>'. $_msg;
            }
            $messageStack->add_session($_msg, 'cart_discount_coupon', $credit_modules_result['ot_coupon']['error']?'error':'success' );
        }

        $credit_apply = tep_db_prepare_input($_POST['credit_apply'] ?? 0);

        if (isset($_POST['credit_apply']) && is_array($_POST['credit_apply'])) {

            if (isset($credit_apply['gv']) && is_array($credit_apply['gv'])) {

                if (isset($credit_apply['gv']['cot_gv_present']) && !isset($credit_apply['gv']['cot_gv'])) {
                    $manager->remove('cot_gv');
                }
                $manager->addEvent(['before'=> 'prepareEstimateData', 'method' => 'collect_posts', 'module' => 'ot_gv', 'data' => $credit_apply['gv'], 'message_class' => 'cart_discount_gv'  ]);

            }

            if (isset($credit_apply['coupon']) && isset($credit_apply['coupon']['gv_redeem_code']) && !empty($credit_apply['coupon']['gv_redeem_code'])) {

                $manager->addEvent(['before'=> 'prepareEstimateData', 'method' => 'collect_posts', 'module' => 'ot_coupon', 'data' => $credit_apply['coupon'], 'message_class' => 'cart_discount_coupon' ]);

            }

        }

        if (isset($post['bonus_points_amount'])) {

            $data = [
                'bonus_points_amount' => $post['bonus_points_amount'],
                'use_bonus_points' => $post['use_bonus_points'],
                'bonus_apply' => $post['use_bonus_points'] ? 'y' : 'n',
            ];

            $manager->addEvent(['before'=> 'prepareEstimateData', 'method' => 'collect_posts', 'module' => 'ot_bonus_points', 'data' => $data, 'message_class' => 'cart_bonus_points' ]);

        }
        
        if (isset($_GET['code']) && $_GET['action'] == 'remove_cart_total') {
            $cart = $manager->getCart();
            $cart->removeCcItem($_GET['code']);
        }
    }
    
    public static function resetCoupons() {
        if (!self::allowed()) {
            return '';
        }
        $manager = \common\services\OrderManager::loadManager();
        $cart = $manager->getCart();
        $cart->clearCcItems();
    }
    
    public static function restoreCartFactory($goto) {
        if (!self::allowed()) {
            return '';
        }
        
        $messageStack = \Yii::$container->get('message_stack');
        $credit_apply = tep_db_prepare_input($_GET['credit_apply']);
        if (is_array($credit_apply)) {
            \common\helpers\Translation::init('ordertotal');
            $manager = \common\services\OrderManager::loadManager();
            if (isset($credit_apply['coupon']) && isset($credit_apply['coupon']['gv_redeem_code']) && !empty($credit_apply['coupon']['gv_redeem_code'])) {
                
                $manager->addEvent(['before'=> 'prepareEstimateData', 'method' => 'collect_posts', 'module' => 'ot_coupon', 'data' => $credit_apply['coupon'], 'message_class' => 'cart_discount_coupon' ]);
                /*
                $gv_query = tep_db_query("select c.coupon_id, c.coupon_amount from " . TABLE_COUPONS . " c, " . TABLE_COUPON_EMAIL_TRACK . " et where coupon_code = '" . tep_db_input($credit_apply['coupon']['gv_redeem_code']) . "' and c.coupon_id = et.coupon_id and et.customer_id_sent = '" . (int) Yii::$app->user->getId() . "'");
                if (tep_db_num_rows($gv_query) > 0) {
                    $coupon = tep_db_fetch_array($gv_query);
                    //if (tep_db_num_rows($redeem_query) == 0 ) {

                    foreach ($credit_apply['coupon'] as $_key => $_val) {
                        $_POST[$_key] = $_val;
                    }
                    $order = new \common\classes\Order();

                    $order_total_modules = new order_total(array(
                        'ONE_PAGE_CHECKOUT' => 'True',
                        'ONE_PAGE_SHOW_TOTALS' => 'false',
                        'COUPON_SUCCESS_APPLY' => 'true',
                    ));

                    if (is_object($GLOBALS['ot_coupon'])) {
                        $order_total_modules->collect_posts('ot_coupon');
                        $order_total_modules->pre_confirmation_check();
                    }

                    if ($messageStack->size('one_page_checkout') > 0) {
                        if ($goto == FILENAME_SHOPPING_CART) {
                            $messageStack->convert_to_session('one_page_checkout', 'cart_discount_coupon');
                        }
                        $messageStack->reset();
                    }
                    foreach ($credit_apply['coupon'] as $_key => $_val) {
                        unset($_POST[$_key]);
                    }
                   
                } else {
                    $messageStack->add_session(ERROR_NO_INVALID_REDEEM_COUPON, 'shopping_cart', 'error');
                }*/
            }

            if (isset($credit_apply['gv']) && isset($credit_apply['gv']['gv_redeem_code']) && !empty($credit_apply['gv']['gv_redeem_code'])) {
                
                if (isset($credit_apply['gv']['cot_gv_present']) && !isset($credit_apply['gv']['cot_gv'])) {
                    $manager->remove('cot_gv');
                }
                
                $manager->addEvent(['before'=> 'prepareEstimateData', 'method' => 'collect_posts', 'module' => 'ot_gv', 'data' => $credit_apply['gv'], 'message_class' => 'cart_discount_gv'  ]);
                
                /*foreach ($credit_apply['gv'] as $_key => $_val) {
                    $_POST[$_key] = $_val;
                }

                if (isset($credit_apply['gv']['cot_gv_present']) && !isset($credit_apply['gv']['cot_gv'])) {
                    if (tep_session_is_registered('cot_gv'))
                        tep_session_unregister('cot_gv');
                }

                $order = new \common\classes\Order();

                $order_total_modules = new order_total(array(
                    'ONE_PAGE_CHECKOUT' => 'True',
                    'ONE_PAGE_SHOW_TOTALS' => 'false',
                    'GV_SOLO_APPLY' => 'true',
                ));

                if (is_object($GLOBALS['ot_gv'])) {
                    $order_total_modules->collect_posts('ot_gv');
                    $order_total_modules->pre_confirmation_check();
                }

                if ($messageStack->size('one_page_checkout') > 0) {
                    if ($goto == FILENAME_SHOPPING_CART) {
                        $messageStack->convert_to_session('one_page_checkout', 'cart_discount_gv');
                    }
                    $messageStack->reset();
                }
                foreach ($credit_apply['gv'] as $_key => $_val) {
                    unset($_POST[$_key]);
                }*/
            }
        }
    }

    public static function getWidgets($type = 'general') {
        if (!self::allowed()) {
            return '';
        }
        if ($type == 'checkout')
            return [[
                'name' => 'CouponsAndVauchers\Checkout', 'title' => 'Coupons And Vouchers', 'description' => '', 'type' => 'checkout',
            ]];
    }

}
