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
use common\helpers\Product;
use common\classes\StockIndication;

class Quantity extends Widget {

    public $file;
    public $params;
    public $settings;

    public function init() {
        parent::init();
    }

    public function run() {
        global $cart;
        $params = Yii::$app->request->get();
        $post = Yii::$app->request->post();

        /** @var \common\extensions\PackUnits\PackUnits $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
            return $ext::renderQuantity($post, $params);
        } else {
            if ($params['products_id'] && !GROUPS_DISABLE_CART) {

//				$stock = false;
//				if (STOCK_CHECK == 'true' && STOCK_ALLOW_CHECKOUT != 'true'){
//					$stock = \common\helpers\Product::get_products_stock($params['products_id']);
//				}
                $show_quantity_input = true;
                $products = Yii::$container->get('products');
                $product = $products->getProduct($params['products_id']);
                if ($product && $product['settings']->show_attributes_quantity) return;
                if (!$product->checkAttachedDetails($products::TYPE_STOCK)) {
                    $product_qty = Product::get_products_stock($params['products_id']);
                    $stock_info = StockIndication::product_info(array(
                                'products_id' => $params['products_id'],
                                'products_quantity' => $product_qty,
                    ));
                    $stock_info['quantity_max'] = Product::filter_product_order_quantity($params['products_id'], $stock_info['max_qty'], true);
                    $product->attachDetails([$products::TYPE_STOCK => $stock_info]);
                } else {
                    $stock_info = $product[$products::TYPE_STOCK];
                    if (!isset($stock_info['quantity_max'])) {
                        $stock_info['quantity_max'] = Product::filter_product_order_quantity($params['products_id'], $stock_info['max_qty'], true);
                    }
                }
                if ($stock_info['flags']['request_for_quote'] || !$stock_info['flags']['can_add_to_cart'] || !$stock_info['flags']['add_to_cart'] ) {
                    $show_quantity_input = false;
                }
                
                if (Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login')) {
                    $product['settings']->show_attributes_quantity = true;
                }

                return IncludeTpl::widget(['file' => 'boxes/product/quantity.tpl', 'params' => [
                                'qty' => Product::filter_product_order_quantity($params['products_id'], $post['qty']),
                                'stock' => $product_qty ?? null,
                                'stock_info' => $stock_info,
                                'quantity_max' => $stock_info['quantity_max'],
                                'show_quantity_input' => $show_quantity_input,
                                'disapear_quantity_input' => $product['settings']->show_attributes_quantity, //? was cProduct c/p from pack/units always false....
                                'order_quantity_data' => Product::get_product_order_quantity($params['products_id']),
                                'product_in_cart' => $cart->in_cart($params['products_id']), //Info::checkProductInCart($params['products_id']),
                    'show_in_cart_button' => Info::themeSetting('show_in_cart_button'),
                ]]);
            } else {
                return '';
            }
        }
    }

}
