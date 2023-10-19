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

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\web\Session;
use common\models\AdminShoppingCarts;

class AdminCarts extends Admin {

    protected $info;
    private $carts = [];
    private $currentCart = null;
    public $newCartCreated = false;

    public function __construct($id = 0) {
        parent::__construct($id);
    }

    public function loadCustomersBasketsShort($type = 'cart') {
        return $this->loadCustomersBaskets($type, false);
    }

    public function createCart($instanceType, $order, $basket_id, $customers_id = null) {
        try {
            $orders_id = $order->orders_id ?? null;
            $cart = new $instanceType($orders_id);
            if ($orders_id) {
                $cartType = $this->getCartType($cart);
                $aCart = AdminShoppingCarts::find()
                                ->where(['admin_id' => $this->info['admin_id'], 'cart_type' => $cartType, 'order_id' => $orders_id])->one();
                if ($aCart) {
                    $index = $cartType . '|' . (int) $aCart->customers_id . '-' . (int) $aCart->basket_id;
                    $cart = $this->getCartById($index);
                    if ($cart) {
                        if (!$cart->admin_id) $cart->setAdmin($this->info['admin_id']);
                        $this->setCurrentCartID($index, ($orders_id ? false : true));
                        return $cart;
                    }
                }
            }
            if ($order) {
                $customer = $order->getCustomer()->one();
                if ($customer) {
                    $cart->setCustomer($customer->customers_id);
                }
            } elseif (!empty($customers_id) && !empty(\common\models\Customers::findOne($customers_id))) {
                $cart->setCustomer($customers_id);
            }
            $cart->setBasketId($basket_id);
            $cart->setAdmin($this->info['admin_id']);
            $index = $this->getCartType($cart) . '|' . (int) $cart->customer_id . '-' . (int) $cart->basketID;
            $this->carts[$index] = [
                'customers_id' => $cart->customers_id ?? null,
                'basket_id' => $cart->basketID ?? null,
                'order_id' => (int) $orders_id,
                'updated_at' => '',
                'cart_details' => $cart,
                'checkout_details' => '',
                'status' => 1,
            ];
            $this->setCurrentCartID($index, ($orders_id ? false : true));
            if ($basket_id != $cart->basketID) {
                $this->newCartCreated = true;
            }
        } catch (Exception $ex) {
            throw new \Exception('incorrent class instance');
        }
        return $cart;
    }

    private function _getCartById($cartId) {
        if ($details = \common\helpers\Cart::decodeId($cartId)) {
            $details['admin_id'] = $this->info['admin_id'];
            return AdminShoppingCarts::find()->where($details)->one();
        }
        return false;
    }

    public function getCartById($cartId) {
        if (isset($this->carts[$cartId])) {
            if ($this->carts[$cartId]['cart_details']) {
                return $this->carts[$cartId]['cart_details'];
            }
        }
        $cart = $this->_getCartById($cartId);
        if ($cart) {
            $this->carts[$cartId] = [
                'customers_id' => $cart->customers_id,
                'basket_id' => $cart->basket_id,
                'order_id' => $cart->order_id,
                'updated_at' => $cart->updated_at,
                'cart_details' => unserialize(base64_decode($cart->customer_basket)),
                'checkout_details' => unserialize(base64_decode($cart->checkout_details)),
                'status' => $cart->status,
            ];
            return $this->carts[$cartId]['cart_details'];
        }
        return false;
    }

    public function hasCheckoutDetails() {
        return isset($this->carts[$this->currentCart]['checkout_details']) && !empty($this->carts[$this->currentCart]['checkout_details']);
    }

    public function getCheckoutDetails() {
        return $this->carts[$this->currentCart]['checkout_details'];
    }

    public function loadCustomersBaskets($type = 'cart', $full = true, $superUserPlatformId = false) {
        $this->carts = [];
        $cartsQuery = AdminShoppingCarts::find()
                        ->where(['cart_type' => $type])->orderBy('updated_at DESC');
        if (!empty($superUserPlatformId)) {
            $cartsQuery->andWhere(['platform_id' => $superUserPlatformId]);
        } else {
            $cartsQuery->andWhere(['admin_id' => $this->info['admin_id']]);
        }
        foreach ($cartsQuery->all() as $cart) {
            $this->carts[$type . '|' . $cart->customers_id . '-' . $cart->basket_id] = [
                'admin_id' => $cart->admin_id,
                'my_id' => $this->info['admin_id'],
                'platform_id' => $cart->platform_id,
                'customers_id' => $cart->customers_id,
                'basket_id' => $cart->basket_id,
                'order_id' => $cart->order_id,
                'updated_at' => $cart->updated_at,
                'cart_details' => [],
                'checkout_details' => [],
                'status' => $cart->status,
            ];
            if ($full) {
                $this->carts[$type . '|' . $cart->customers_id . '-' . $cart->basket_id]['cart_details'] = unserialize(base64_decode($cart->customer_basket));
                $this->carts[$type . '|' . $cart->customers_id . '-' . $cart->basket_id]['checkout_details'] = unserialize(base64_decode($cart->checkout_details));
            }
        }
        return $this;
    }

    public function checkCartOwnerClear($cart) {
        $check = AdminShoppingCarts::find()->where(['and',
                    ['<>', 'admin_id', $this->info['admin_id']],
                    ['customers_id' => $cart->customer_id ?? null],
                    ['order_id' => $cart->order_id ?? null],
                    ['cart_type' => $this->getCartType($cart)]
                ])->one();
        if ($check && $check->status) {
            return false;
        }
        return true;
    }

    public function getCartType($cart) {
        $cart_prefix = $cart->table_prefix ?? null;
        if ($cart_prefix == 'sample_') {
            return 'sample';
        } else if ($cart_prefix == 'quote_') {
            return 'quote';
        } else {
            return 'cart';
        }
    }

    public function updateCustomersBasket($cart) {
        if (!isset($this->carts[$cart->customer_id . '-' . $cart->basketID])) {
            if (!$this->checkCartOwnerClear($cart))
                return false;
            $this->saveCustomerBasket($cart);

            $this->setCurrentCartID($cart->customer_id . '-' . $cart->basketID);
        } else {
            $this->setCurrentCartID($cart->customer_id . '-' . $cart->basketID);
        }
    }

    public function saveCustomerBasket($cart) {
        $adCart = $this->findCart($cart);

        $adCart->customer_basket = base64_encode(serialize($cart));
        $adCart->status = 1;
        $adCart->order_id = (int) $cart->order_id;
        return $adCart->save(false);
    }

    public function saveCheckoutDetails($cart, \common\services\storages\StorageInterface $storage) {
        $adCart = $this->_getCartById($this->getCurrentCartID());
        if (!$adCart) {
            $adCart = AdminShoppingCarts::find()
                    ->where(['admin_id' => $this->info['admin_id'], 'basket_id' => $cart->basketID, 'customers_id' => (int)$cart->customer_id])
                    ->one();
        }
        if (!$adCart) {
            $adCart = new AdminShoppingCarts();
            $adCart->admin_id = $this->info['admin_id'];
            $adCart->platform_id = (int) $cart->platform_id;
            $adCart->customers_id = (int) $cart->customer_id;
            $adCart->basket_id = $cart->basketID;
            $adCart->cart_type = $this->getCartType($cart);
        }
        if ($adCart) {
            $data = $storage->getAll();
            unset($data['cart']);
            $adCart->checkout_details = base64_encode(serialize($data));
            $adCart->status = 1;
            $adCart->customers_id = (int) $cart->customer_id;
            $adCart->order_id = (int) $cart->order_id;

            $index = $this->getCartType($cart) . '|' . (int) $cart->customer_id . '-' . (int) $cart->basketID;
            $this->setCurrentCartID($index);
            if ($adCart->save(false)) {
                return $this->saveCustomerBasket($cart);
            }
        }
        return false;
    }

    private function findCart($cart) {
        $adCart = AdminShoppingCarts::find()->where([
                    'admin_id' => $this->info['admin_id'],
                    'customers_id' => (int) $cart->customer_id,
                    'basket_id' => $cart->basketID,
                    'cart_type' => $this->getCartType($cart)
                ])->one();
        if (!$adCart) {
            $adCart = new AdminShoppingCarts();
            $adCart->admin_id = $this->info['admin_id'];
            $adCart->platform_id = (int) $cart->platform_id;
            $adCart->customers_id = (int) $cart->customer_id;
            $adCart->basket_id = $cart->basketID;
            $adCart->cart_type = $this->getCartType($cart);
        }
        return $adCart;
    }

    public function removeCart($cartId) {
        $AdCart = $this->_getCartById($cartId);
        if ($AdCart) {
            $AdCart->delete();
        }
    }

    public function setCurrentCartID($cartID, $is_virtual = false) {
        $this->currentCart = $cartID;
        if ($is_virtual) {
            $this->setLastVirtualID($cartID);
        }
    }

    public function setLastVirtualID($cartID) {
        Yii::$app->session->set('lastVirtual', $cartID);
    }

    public function getLastVirtualID($set_main = false) {
        if ($set_main) {
            $this->currentCart = Yii::$app->session->get('lastVirtual');
        }
        return Yii::$app->session->get('lastVirtual');
    }

    public function getCurrentCartID() {
        return $this->currentCart;
    }

    public function getVirtualCartIDs() {
        $ids = [];
        if (is_array($this->carts)) {
            $ids = array_keys($this->carts);
            //foreach ($this->carts as $_id => $_cart) {
            //    $ids[] = $_id;
                /* if (!$_cart['order_id'] || $_cart['order_id'] <= 0) {
                  $ids[] = $_id;
                  } */
            //}
            return (count($ids) ? $ids : false);
        }
        return false;
    }

    public function loadCurrentCart() {
        /* global $cart, $quote, $payment, $shipping, $select_shipping, $adress_details, $sendto, $billto, $cot_gv, $cc_id;

          if (!is_null($this->currentCart)) {
          if (is_array($this->carts[$this->currentCart]['cart_details'])) {
          foreach ($this->carts[$this->currentCart]['cart_details'] as $item => $value) {
          if (!tep_session_is_registered($item))
          tep_session_register($item);
          unset($GLOBALS[$item]);
          $_SESSION[$item] = $value;
          $GLOBALS[$item] = &$_SESSION[$item];
          }
          }
          } */
    }

    public function getAdminByCart($cart) {
        $name = '';
        $type = $this->getCartType($cart);
        $admin = tep_db_fetch_array(tep_db_query("select admin_id from " . TABLE_ADMIN_SHOPPING_CARTS . " where customers_id ='" . (int) $cart->customer_id . "' and order_id = '" . (int) $cart->order_id . "' and cart_type='{$type}'"));
        if ($admin) {
            $_admin = new Admin($admin['admin_id']);
            $name = $_admin->getInfo('admin_firstname') . ' ' . $_admin->getInfo('admin_lastname');
        }
        return $name;
    }

    public function relocateCart($basket_id, $customer_id, $type = 'cart') {
        if ($basket_id && $customer_id) {
            tep_db_query("update " . TABLE_ADMIN_SHOPPING_CARTS . " set admin_id = '" . (int) $this->info['admin_id'] . "' where customers_id ='" . (int) $customer_id . "' and basket_id = '" . (int) $basket_id . "' and cart_type = '{$type}'");
        }
    }
    

    public function reassignMe($basket_id, $customer_id, $type = 'cart') {
        tep_db_query("update " . TABLE_ADMIN_SHOPPING_CARTS . " set admin_id = '" . (int) $this->info['admin_id'] . "' where customers_id ='" . (int) $customer_id . "' and basket_id = '" . (int) $basket_id . "' and cart_type = '{$type}'");
    }

    public function reassignCart($cart){
        if ($cart) {
            tep_db_query("update " . TABLE_ADMIN_SHOPPING_CARTS . " set admin_id = '" . (int) $this->info['admin_id'] . "' where customers_id ='" . (int)$cart->customer_id . "' and order_id = '" . (int) $cart->order_id . "' and cart_type = '{$this->getCartType($cart)}'");
            return true;
        }
        return false;
    }

    public function deleteCartByOrder($orders_id) {
        tep_db_query("delete from " . TABLE_ADMIN_SHOPPING_CARTS . " where order_id = '" . (int) $orders_id . "'");
    }

    public function deleteCartByBC($customer_id, $basket_id) {
        tep_db_query("delete from " . TABLE_ADMIN_SHOPPING_CARTS . " where basket_id = '" . (int) $basket_id . "' and customers_id = '" . (int) $customer_id . "'");
        $this->loadCustomersBaskets();
        return true;
    }

    public function getCarts() {
        return $this->carts;
    }

    public function isCartSaved($cartId) {
        if (preg_match("/(.*)\|([\d]*)\-([\d]*)/", $cartId, $mas)) {
            if (is_array($mas) && isset($mas[1])) {
                $cart = AdminShoppingCarts::find()->where(['cart_type' => $mas[1], 'customers_id' => $mas[2], 'basket_id' => $mas[3]])->one();
                if ($cart)
                    return true;
            }
        }
        return false;
    }

}
