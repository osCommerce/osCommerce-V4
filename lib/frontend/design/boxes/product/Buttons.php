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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;

class Buttons extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $cart;
    $params = Yii::$app->request->get();

    if (Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')) {
        return '';
    }

    if ($params['products_id'] && !GROUPS_DISABLE_CART) {

      $externalPayments = '';
      if (empty($this->settings[0]['hide_express_buttons'])) {
        $payment_modules = \common\services\OrderManager::loadManager($cart)->getPaymentCollection();
        $arr = $payment_modules->showPaynowButton();
        if (count($arr)) {
          $externalPayments = implode("\n", $arr);
        }
      }

      $compare_link = '';
      $wishlist_link = '';
      $products = Yii::$container->get('products');
      $product = $products->getProduct($params['products_id']);
      if (!$product->checkAttachedDetails($products::TYPE_STOCK)){
        $product_qty = \common\helpers\Product::get_products_stock($params['products_id']);
        $stock_info = \common\classes\StockIndication::product_info(array(
          'products_id' => $params['products_id'],
          'products_quantity' => $product_qty,
        ));
        $stock_info['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($params['products_id'], $stock_info['max_qty'], true);
        $product->attachDetails([$products::TYPE_STOCK => $stock_info]);
      } else {
          $stock_info = $product[$products::TYPE_STOCK];
      }

        if (!isset($stock_info['flags'])) {
            $stock_info['flags'] = [];
        }
        if (!isset($stock_info['flags']['out_stock_action'])) {
            $stock_info['flags']['out_stock_action'] = 1;
            if ($product->out_stock_action) {
                $stock_info['flags']['out_stock_action'] = $product->out_stock_action;
            } elseif (defined('ACTION_ON_OUT_OF_STOCK') && ACTION_ON_OUT_OF_STOCK == 'Contact form') {
                $stock_info['flags']['out_stock_action'] = 2;
            }
        }

        $add_to_cart = $stock_info['flags']['add_to_cart'];
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $cart_for_logged_only = \common\helpers\Customer::check_customer_groups($customer_groups_id, 'cart_for_logged_only');
        if ($cart_for_logged_only > 0) {
            if (!$add_to_cart) {
                return '';
            }
            return sprintf(TEXT_PLEASE_LOGIN_CART, tep_href_link(FILENAME_LOGIN,'','SSL'));
        }
        $buttonArray = [
            $params['products_id'] => [
                'buttonId' => ('b_atc_' . preg_replace('/[^\d]/', '_', $params['products_id'])),
                'quantity' => '1',
            ]
        ];
      $cart_button = isset($product->cart_button) ? $product->cart_button : 1;
      return IncludeTpl::widget(['file' => 'boxes/product/buttons.tpl', 'params' => [
        'compare_link' => $compare_link,
        'wishlist_link' => $wishlist_link,
        'product_qty' => $stock_info['products_quantity'],//\common\helpers\Product::get_products_stock($params['products_id']),
        'product_has_attributes' => \common\helpers\Attributes::has_product_attributes($params['products_id']),
        'stock_info' => $stock_info,
        'product_in_cart' => $cart->in_cart($params['products_id']),//Info::checkProductInCart($params['products_id']),
        'customer_is_logged' => !Yii::$app->user->isGuest,
        'paypal_block' => $externalPayments,
        'cart_button' => $cart_button,
        'settings' => $this->settings[0],
        'products_carousel' => Info::themeSetting('products_carousel'),
        'show_in_cart_button' => Info::themeSetting('show_in_cart_button'),
        'products_id' => (int)$params['products_id'],
          'id' => $this->id,
          'buttonArray' => $buttonArray
      ]]);
    } else {
      return '';
    }
  }
}