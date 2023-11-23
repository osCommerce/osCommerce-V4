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

namespace frontend\controllers;

use common\models\repositories\CouponRepository;
use frontend\design\boxes\cart\OrderTotal;
use frontend\design\boxes\cart\ShippingEstimator;
use frontend\design\boxes\packingslip\Products;
use frontend\design\Info;
use Yii;

/**
 * Site controller
 */
class ShoppingCartController extends Sceleton {

    public function actionIndex() {
        global $cart, $breadcrumb;

        if (GROUPS_DISABLE_CART) {
            tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

        if (Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')) {
            tep_redirect(tep_href_link(FILENAME_LOGIN));
        }

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'cart_for_logged_only')) {
            tep_redirect(tep_href_link(FILENAME_LOGIN));
        }

        $popupMode = (Yii::$app->request->isAjax && (int)Yii::$app->request->get('popup') && Info::themeSetting('after_add') == 'popup');
        if ($cart->notEmpty() && defined("SKIP_CART_PAGE") && SKIP_CART_PAGE == 'True' && !$popupMode) {
            $this->redirect('checkout');
        }

        //--- I don't know if this is safe enough?
        if (!Yii::$app->user->isGuest) {
            if( $multiCart = \common\helpers\Extensions::isAllowed('MultiCart') ) {
                $customer_id = Yii::$app->user->getId();
                $multiCart::restoreCarts($cart, $customer_id);
                $uid = $multiCart::getCurrentCartKey();
                $currentCart = $multiCart::getCart($uid);
                if (is_object($currentCart)) {
                    $multiCart::setCurrentCart($uid);
                    $cart = $currentCart;
                }
            }
        }
        //---

        $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_SHOPPING_CART));
        $messageStack = \Yii::$container->get('message_stack');
        $currencies = \Yii::$container->get('currencies');

        if (!Yii::$app->user->isGuest) {
            $this->manager->remove('estimate_ship');
            $this->manager->remove('estimate_bill');
            $this->manager->set('shipping', false);
        }

        if (Yii::$app->request->isPost && isset($_POST['ajax_estimate'])) {
            return $this->actionEstimate();
        }
        if (!$this->manager->hasCart()){
            $this->manager->loadCart($cart);
        }
        $this->manager->createOrderInstance('\common\classes\Order');

        $render_data = array(
            'action' => tep_href_link(FILENAME_SHOPPING_CART, 'action=update_product'),
            'manager' => $this->manager
        );
        if (!$popupMode) {
            $render_data = array_merge($render_data, $this->manager->prepareEstimateData());
        }

        $message_discount_coupon = '';
        if ($messageStack->size('cart_discount_coupon') > 0) {
            $message_discount_coupon = $messageStack->output('cart_discount_coupon');
        }
        $message_discount_gv = '';
        if ($messageStack->size('cart_discount_gv') > 0) {
            $message_discount_gv = $messageStack->output('cart_discount_gv');
        }
        $ot_gv_data = array(
            'can_apply_gv_credit' => false,
            'message_discount_gv' => $message_discount_gv,
            'credit_amount' => '',
            'credit_gv_in_use' => $this->manager->has('cot_gv'),
            'message_discount_coupon' => $message_discount_coupon,
            'message_shopping_cart' => ( $messageStack->size('shopping_cart') > 0 ? $messageStack->output('shopping_cart') : '' ),
        );

        $render_data = array_merge($render_data, $ot_gv_data);

        foreach (\common\helpers\Hooks::getList('shopping-cart/index') as $filename) {
            include($filename);
        }

        if ($popupMode) {
            return $this->render('popup.tpl', $render_data);
        } else {
            \common\components\google\widgets\GoogleTagmanger::setEvent('shoppingCart');
            return $this->render('index.tpl', $render_data);
        }
    }

    public function actionEstimate() {
        $this->layout = false;
        global $cart;

        $this->manager->loadCart($cart);
        $this->manager->createOrderInstance('\common\classes\Order');

        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('estimate');
            if (isset($post['country_id'])) {
                $post['country_id'] = (int) $post['country_id'];
            }

            if ($this->manager->isCustomerAssigned()){
                if ($post['sendto']){
                    $this->manager->changeCustomerAddressSelection('shipping', $post['sendto']);
                    $this->manager->resetDeliveryAddress();
                    $this->manager->changeCustomerAddressSelection('billing', $post['sendto']);
                    $this->manager->resetBillingAddress();
                    $this->manager->set('shipping', false);
                }
            } elseif ($post['country_id']) {
               Yii::$app->storage->set('customer_country_id', $post['country_id']);
               if ($this->manager->has('estimate_ship')){
                    $estimate = $this->manager->get('estimate_ship');
                    if ($estimate['country_id'] != $post['country_id']){
                        $this->manager->set('estimate_ship', ['country_id' => $post['country_id'], 'postcode' => $post['post_code']]);
                        $this->manager->resetDeliveryAddress();
                        $this->manager->set('estimate_bill', ['country_id' => $post['country_id'], 'postcode' => $post['post_code']]);
                        $this->manager->resetBillingAddress();
                        $post['shipping'] = null;
                        $this->manager->set('shipping', false);
                    }
                } else {
                    $this->manager->set('estimate_ship', ['country_id' => $post['country_id'], 'postcode' => $post['post_code']]);
                    $this->manager->resetDeliveryAddress();
                    $this->manager->set('estimate_bill', ['country_id' => $post['country_id'], 'postcode' => $post['post_code']]);
                    $this->manager->resetBillingAddress();
                }
            }
            if ($post['shipping']??null){
                $this->manager->setSelectedShipping($post['shipping']);
            }
        }

        return json_encode(array('estimate' => ShippingEstimator::widget(['params' =>['manager' => $this->manager]]), 'total' => OrderTotal::widget(['params' =>['manager' => $this->manager]])));
    }


    private function seadate($day) {
        $rawtime = strtotime("-" . $day . " days");
        $ndate = date("Ymd", $rawtime);

        return $ndate;
    }

    /**
     * @note Not used. This actions from MiltiCart extension frontend controller
     */
//    public function actionSaveCart() {
//        if ($ext = \common\helpers\Extensions::isAllowed('MultiCart')) {
//            $ext::saveCart();
//        }
//        return $this->redirect('index');
//    }
//
//    public function actionApplyCart($uid) {
//        if ($ext = \common\helpers\Extensions::isAllowed('MultiCart')) {
//            $ext::applyCart($uid);
//        }
//        return $this->redirect('index');
//    }

    public $manager;

    public function __construct($id, $module, $config = []) {
        parent::__construct($id, $module, $config);
        $this->manager = new \common\services\OrderManager(Yii::$app->get('storage'));
        $this->manager->setModulesVisibility(['shop_order']);
        Yii::configure($this->manager, [
            'combineShippings' => true,
        ]);
    }

    /**
     * cron 'Abandoned Cart Notification' shopping-cart/notify-cart
     */
    public function actionNotifyCart() {
        $sql = "SELECT cb.customers_id cid,
                cb.products_id pid,
                cb.customers_basket_quantity qty,
                cb.customers_basket_date_added bdate,
                cb.currency,
                cus.admin_id,
                cus.customers_firstname fname,
                cus.customers_lastname lname,
                cus.customers_telephone phone,
                cus.customers_newsletter newsletter,
                cus.customers_fax fax,
                cus.customers_email_address email,
                cb.basket_id, cb.platform_id,
                ci.token,
                cus.customers_gender,
                ci.time_long,
                TIMESTAMPDIFF(HOUR, ci.time_long,NOW() ) as hoursago,
                cb.language_id,
                st.offered_discount,
                if(ISNULL(st.offered_discount),0,st.offered_discount) as offered_discount_data
           FROM " . TABLE_CUSTOMERS_BASKET . " AS cb
           INNER JOIN " . TABLE_CUSTOMERS . " AS cus ON  cb.customers_id = cus.customers_id and cus.opc_temp_account=0
           LEFT JOIN " . TABLE_CUSTOMERS_INFO . " ci ON ci.customers_info_id = cus.customers_id
           LEFT JOIN " . TABLE_SCART . " st ON cus.customers_id=st.customers_id AND cb.basket_id=st.basket_id
           WHERE  cus.admin_id > 0 AND (st.offered_discount < 3 or  ISNULL(st.offered_discount)) and (st.workedout = 0 or  ISNULL(st.workedout))  and (st.contacted = 0 or  ISNULL(st.contacted)) AND TIMESTAMPDIFF(HOUR, ci.time_long,NOW() ) > 0
           GROUP BY cb.customers_id
           ORDER BY ci.time_long";

        $query = tep_db_query($sql);
        $recovery_carts = [];
        if (tep_db_num_rows($query)) {
            while ($recovery_cart = tep_db_fetch_array($query)) {
                $recovery_carts[] = $recovery_cart;
            }
        }

        $currencies = \Yii::$container->get('currencies');
        $admins = [];

        $mline = '';
        foreach ($recovery_carts as $recovery_cart) {
            $cid = (int) $recovery_cart['cid'];
            if ($cid === 0) {
                continue;
            }

            $admin_id = (int) $recovery_cart['admin_id'];
            if ($admin_id === 0) {
                continue;
            }

            /*if ($admin_id != 94) {
                continue;//TODO stub
            }*/

            if (!isset($admins[$admin_id])) {
                $admins[$admin_id] = \common\models\Admin::find()->where(['admin_id' => $admin_id])->asArray()->one();
            }

            $platform = new \common\classes\platform_config($recovery_cart['platform_id']);

            $sql = "select cb.products_id pid, cb.customers_basket_quantity qty, p.products_price price,
                p.products_tax_class_id taxclass,p.products_id pidd,
                p.products_model model,
                pd.products_name name,
                cb.final_price as final_price
                from " . TABLE_CUSTOMERS_BASKET . " cb, " . TABLE_CUSTOMERS . " cus, " . TABLE_PRODUCTS . " p
                LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id=p.products_id
                  WHERE cb.customers_id = cus.customers_id AND cus.customers_id = '" . $cid . "'
                        AND p.products_id = CONVERT(cb.products_id, UNSIGNED INTEGER )
                        AND pd.products_id = p.products_id and pd.platform_id = '".(int)Yii::$app->get('platform')->config()->getPlatformToDescription()."' AND pd.language_id = " . (int) $recovery_cart['language_id'];

            $query2 = tep_db_query($sql);

            $ptoduct = '';
            $ptoductArr = [];
            $columns = 3;
            while ($inrec2 = tep_db_fetch_array($query2)) {

                $sprice = $inrec2['final_price'];
                $sprice += ( $sprice * \common\helpers\Tax::get_tax_rate($inrec2['taxclass']) / 100 );

                $tprice = $tprice + ( $inrec2['qty'] * $sprice );
                $pprice_formated = $currencies->format($sprice, false, $recovery_cart['currency'], $recovery_cart['currency']);
                $tpprice_formated = $currencies->format(( $inrec2['qty'] * $sprice), false, $recovery_cart['currency'], $recovery_cart['currency']);
                $image = '';

                $product_link = Yii::$app->urlManager->createAbsoluteUrl([
                    'catalog/product',
                    'products_id' => $inrec2['pid']
                        ]);
                if (EMAIL_USE_HTML == 'true') {
                    $image = \common\classes\Images::getImage($inrec2['pidd'], 'Small');

                    $ptoductArr[] = '
<div style="text-align: center; padding: 20px;">
    <div>' . ( $image ? '<a href="' . $product_link . '">' . $image . '</a>' : '' ) . '</div>
    <div style="margin-bottom: 10px"><a href="' . $product_link . '" style="font-size: 16px; font-weight: bold; color: #444444; text-decoration:none;">' . $inrec2['qty'] . ' x ' . $inrec2['name'] . '</a></div>
    <div style="font-size: 24px">' . $pprice_formated . '</div>
</div>';
                } else {
                    $mline .= $recovery_cart['qty'] . ' x  ' . $inrec2['name'] . "-" . $pprice_formated . "\n";
                }
            }

            if (EMAIL_USE_HTML == 'true') {
                $count = count($ptoductArr);
                $last = $count % $columns;
                $ptoduct .= '<table  cellpadding="0" cellspacing="0" width="100%" border="0"><tr style="vertical-align: top">';
                for ($i = 0; $i < ( $count - $last ); $i ++) {
                    if ($i != 0 && $i % $columns == 0) {
                        $ptoduct .= '</tr><tr style="vertical-align: top">';
                    }
                    $ptoduct .= '<td width="' . floor(100 / $columns) . '%">' . $ptoductArr[$i] . '</td>';
                }
                $ptoduct .= '</tr></table>';

                $ptoduct .= '<table  cellpadding="0" cellspacing="0" width="100%" border="0"><tr style="vertical-align: top">';
                for ($i; $i < $count; $i ++) {
                    $ptoduct .= '<td width="' . floor(100 / $last) . '%">' . $ptoductArr[$i] . '</td>';
                }
                $ptoduct .= '</tr></table>';
            }

            $mline = $ptoduct;
            $custname = $recovery_cart['fname'] . " " . $recovery_cart['lname'] . ' &lt;' . $recovery_cart['email'] . '&gt;';

            $email_params = array();
            $email_params['STORE_NAME'] = $platform->const_value('STORE_NAME');
            $email_params['STORE_OWNER'] = $platform->const_value('STORE_OWNER');
            $email_params['STORE_OWNER_EMAIL_ADDRESS'] = $platform->const_value('STORE_OWNER_EMAIL_ADDRESS');
            $email_params['CUSTOMER_DETAILS'] = $custname;
            $email_params['PRODUCTS_ORDERED'] = $mline;

            [$email_subject, $email_text] = \common\helpers\Mail::get_parsed_email_template('Abandoned Cart Notification', $email_params, $recovery_cart['language_id'], $recovery_cart['platform_id']);
            \common\helpers\Mail::send($admins[$admin_id]['admin_firstname'] . ' ' . $admins[$admin_id]['admin_lastname'], $admins[$admin_id]['admin_email_address'], $email_subject, $email_text, $email_params['STORE_OWNER'], $email_params['STORE_OWNER_EMAIL_ADDRESS']);

        }


    }
}
