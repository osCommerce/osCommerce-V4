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

use Yii;
use common\extensions\MultiCart\MultiCart;
use common\models\Products;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MultiCartController extends Sceleton {

    public function actionCart() {
        $uid  = (int) \Yii::$app->request->get('uid');
        global $cart;
        
        if (!Yii::$app->user->isGuest) {
            if( $multiCart = \common\helpers\Acl::checkExtension( 'MultiCart', 'allowed' ) ) {
                if ($multiCart::allowed()){
                    $customer_id = Yii::$app->user->getId();
                    $multiCart::restoreCarts($cart, $customer_id);
                }
            }
        }

        $currentCart = MultiCart::getCart($uid);

        if (!$currentCart) {
            // check if uid exists?
            if (MultiCart::createCart('Copy of ' . $uid)) { //create new cart
                $to_cart = MultiCart::getLastCreatedKey();
                $currentCart = MultiCart::getCart($to_cart);
                if (is_object($currentCart)) {
                    $currentCart->restoreContentProducts($uid);
                    $uid = $to_cart;
                }
            }
        }
            
        if (!$currentCart) {
            //throw new NotFoundHttpException(); do not generate Exception
            return $this->redirect(\Yii::$app->urlManager->createUrl('shopping-cart'));
        }
        if (is_object($currentCart)) {
            MultiCart::setCurrentCart($uid);
            $cart = $currentCart;
        }

        if (Yii::$app->request->isAjax) {
            return Yii::$app->runAction('get-widget/one', Yii::$app->request->get());
        }

        return $this->redirect(\Yii::$app->urlManager->createUrl('shopping-cart'));
    }

    public function actionSaveCart() {
        if (\Yii::$app->request->isAjax) {
            MultiCart::saveCart();
            return 'reload';
        }
        throw new NotFoundHttpException();
    }
    
    public function actionProcessSaveCart() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $create_new = \Yii::$app->request->post('create_new', '');
        $dest_cart = \Yii::$app->request->post('dcart', '');
        $merge_option = \Yii::$app->request->post('merge_option', '');
        $cart_action = \Yii::$app->request->post('cart_action', '');
        $products_id = \Yii::$app->request->post('products_id', '');
        $from_cart = \Yii::$app->request->post('from_uid', null);
        if (!$from_cart) $from_cart = null;

        if ($dest_cart == 'create_new' && $create_new) {
            if (MultiCart::createCart($create_new)){ //create new cart
                $to_cart = MultiCart::getLastCreatedKey();
            }
        } else if (!empty($dest_cart) && $dest_cart != 'create_new'){
            $to_cart = $dest_cart;
        }
        
        $result = false;
        
        if ($to_cart){
            if ($cart_action == 'copy'){
                $result = MultiCart::copyProduct($to_cart, $products_id, $merge_option == 'replace', $from_cart);
            } else {
                $result = MultiCart::moveProduct($to_cart, $products_id, $merge_option == 'replace', $from_cart);
            }
        }
        
        return ['success' => $result];
    }
    
    public function actionProductDelete(){
        global $cart;
        $products_id = \Yii::$app->request->get('products_id', '');
        $ga = \Yii::$app->request->get('ga', 0);
        $from_uid = \Yii::$app->request->get('uid', null);
        if (!$from_uid) $from_uid = null;
        if($products_id){
            if (!is_null($from_uid)){
                MultiCart::removeItem($from_uid, $products_id);
            } else {
                if ($ga){
                    $cart->remove_giveaway($products_id);
                } else{
                    $cart->remove($products_id);
                }
            }
            
            MultiCart::mergeCart(MultiCart::getCurrentCartKey());
        }
        if (\Yii::$app->request->get('no_redirect', false)) {
            return '';
        }
        return $this->redirect($this->getMultiRoute());
        //return $this->redirect(\Yii::$app->urlManager->createUrl('shopping-cart'));
    }

    public function actionDeleteCart() {
        $uid  = (int) \Yii::$app->request->get('uid');
        if (!$uid) $uid = MultiCart::getCurrentCartKey();
                
        if (MultiCart::deleteCart($uid)) {
            return $this->redirect($this->getMultiRoute());
        }
    }

    public function actionClearCart() {
        $uid  = (int) \Yii::$app->request->get('uid');
        if (!$uid) $uid = MultiCart::getCurrentCartKey();
        
        if (MultiCart::clearCart($uid)) {
            return $this->redirect(\Yii::$app->urlManager->createUrl('shopping-cart'));
        }
    }

    public function actionCompare() {
        global $navigation;

        if (is_object($navigation) && method_exists($navigation, 'set_snapshot')){
            $navigation->set_snapshot();
        }
        
        $carts = MultiCart::compareCarts();
        
        return $this->render('compare.tpl', ['carts' => $carts]);
    }
    
    public function actionChangeCartName(){
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->isPost){
                $cart_name = strip_tags(\Yii::$app->request->post('cart_name', ''));
                $params = [
                            'success' => false,
                            'message' => ERROR_MULTICART_CART_NAME_NOT_CHANGED,
                        ];
                if (!empty($cart_name)){
                    if (MultiCart::setCartName($cart_name)){
                        $params = [
                            'success' => true,
                            'message' => TEXT_CART_NAME_CHANGED
                        ];
                    }
                }
                echo json_encode($params);
                exit;
            } else {
                $link = \Yii::$app->urlManager->createUrl('multi-cart/change-cart-name');
                return $this->renderAjax('change-name-cart-dialog.tpl', ['current_name' => MultiCart::getCurrentCartName(), 'link' => $link, 'type' => 'change']);
            }
        }
        throw new NotFoundHttpException();
    }
    
    public function actionCreateCart()
    {
        if (\Yii::$app->request->isPost){
            $cart_name = strip_tags(\Yii::$app->request->post('cart_name'));

            if ($cart_name){
                MultiCart::createCart($cart_name);
            } else {
                MultiCart::createCart();
            }
            return json_encode(['success' => true, 'message' => 'Cart created']);
        } else {
            if (\Yii::$app->request->isAjax) {
                $link = \Yii::$app->urlManager->createUrl('multi-cart/create-cart');
                return $this->renderAjax('change-name-cart-dialog.tpl', [
                    'current_name' => '',
                    'link' => $link,
                    'type' => 'new'
                ]);
            }
            return $this->redirect(\Yii::$app->urlManager->createUrl('shopping-cart'));
        }
    }
    
    public function actionCopyCart() {
        $currencies = \Yii::$container->get('currencies');
        if (\Yii::$app->request->isPost){

            $create_new = \Yii::$app->request->post('create_new', '');
            $dest_cart = \Yii::$app->request->post('dcart', '');
            $merge_option = \Yii::$app->request->post('merge_option', '');
            $cart_action = \Yii::$app->request->post('cart_action', '');
            $products_id = \Yii::$app->request->post('products_id', '');

            if ($dest_cart == 'create_new'){
                if (!empty($create_new)){
                    if (MultiCart::createCart($create_new)){ //create new cart
                        $to_cart = MultiCart::getLastCreatedKey();
                    }
                } else {
                    echo json_encode(['success' => 'false', 'message' => ERROR_MULTICART_EMPTY_CART_NAME]);
                    exit();
                }
            } else if (!empty($dest_cart) && $dest_cart != 'create_new'){
                $to_cart = $dest_cart;
            }
            
            $result = false;

            if ($to_cart){
                $result = MultiCart::replaceCart($to_cart);
            }

            if ($result){
                echo json_encode(['success' => 'true', 'message' => TEXT_CART_COPIED]);
            } else {
                echo json_encode(['success' => 'false', 'message' => ERROR_MULTICART_CART_NOT_COPIED]);
            }
            
            exit;
        } else {
            $link = \Yii::$app->urlManager->createUrl('multi-cart/copy-cart');

            $carts = MultiCart::getCarts(true);
            unset($carts[MultiCart::getCurrentCartKey()]);
            $action = \Yii::$app->request->get('action');
            $text_action = ($action != 'copy'? TEXT_MOVE_TO_CART : TEXT_COPY_TO_CART);            
            $first = (count($carts)? key($carts): null);

            return $this->renderAjax('choose-cart-dialog.tpl', [
                'carts' => $carts,
                'link' => $link,
                'currencies' => $currencies,
                'action' => $action,
                'text_action' => $text_action,
                'first' => $first,
            ]);
            //return $this->renderAjax('change-name-cart-dialog.tpl', ['current_name' => '', 'link' => $link, 'type' => 'new']);
        }
    }
    
    public function actionProductApply(){
        $currencies = \Yii::$container->get('currencies');
        $products_id = \Yii::$app->request->get('products_id');
        if (!$products_id)
            return $this->redirect(\Yii::$app->urlManager->createUrl('shopping-cart'));
        
        if (\Yii::$app->request->isAjax){
            
            $carts = MultiCart::getCarts(true);
            $uid = \Yii::$app->request->get('uid', null);
            if (is_null($uid)){
                unset($carts[MultiCart::getCurrentCartKey()]);
            } else {
                unset($carts[$uid]);
            }            
            $link = \Yii::$app->urlManager->createUrl(['multi-cart/process-save-cart']);
            $first = null;
            if (count($carts)) $first = key($carts);
            $action = \Yii::$app->request->get('action');
            $text_action = ($action == 'copy'? TEXT_COPY_TO_CART : TEXT_MOVE_TO_CART);
            $from_uid = \Yii::$app->request->get('uid', null);
            if (!$from_uid) $from_uid = null;
                    
            return $this->renderAjax('choose-cart-dialog.tpl', [
                'carts' => $carts, 
                'link' => $link, 
                'currencies' => $currencies, 
                'action' => $action, 
                'text_action' => $text_action,
                'first' => $first, 
                'products_id' => $products_id,
                'from_uid' => $from_uid,
            ]);
        }
        
        return $this->redirect(\Yii::$app->urlManager->createUrl('shopping-cart'));
    }
    
    public function actionCheckout($uid){
        global $cart;

        $currentCart = MultiCart::getCart($uid);

        if (!$currentCart) {
            //throw new NotFoundHttpException(); do not generate Exception
            return $this->redirect(\Yii::$app->urlManager->createUrl('shopping-cart'));
        }
        if (is_object($currentCart)) {
            MultiCart::setCurrentCart($uid);
            $cart = $currentCart;
        }        

        return $this->redirect(\Yii::$app->urlManager->createUrl('checkout/index'));
        
    }
    
    public function getMultiRoute() {
        global $navigation;
        
        if (sizeof($navigation->snapshot) > 0) {
            if (is_array($navigation->snapshot['get'])){
                $origin_href = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
            } else {
                $origin_href = tep_href_link($navigation->snapshot['page'], $navigation->snapshot['get'], $navigation->snapshot['mode']);
            }
            $navigation->clear_snapshot();
            return $origin_href;
        }
        return \Yii::$app->urlManager->createUrl('shopping-cart');
    }

}
