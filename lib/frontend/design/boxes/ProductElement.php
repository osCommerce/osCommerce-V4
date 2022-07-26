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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\StockIndication;
use common\classes\Images;

class ProductElement extends Widget
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
        $products_id = $this->params['products_id'];

        if (!$products_id) {
            return '';
        }

        $currencies = \Yii::$container->get('currencies');

        $products = Yii::$container->get('products');
        $product = $products
            ->loadProducts(['products_id' => $products_id])
            ->getProduct($products_id);


        if ($this->settings[0]['element'] == 'price') {
            $special_price = \common\helpers\Product::get_products_special_price($product['products_id']);
            if ($special_price) {
                $product['old'] = $currencies->display_price(
                    \common\helpers\Product::get_products_price($product['products_id'], 1, $product['products_price']),
                    \common\helpers\Tax::get_tax_rate($product['products_tax_class_id'])
                );
                $product['special'] = $currencies->display_price(
                    $special_price,
                    \common\helpers\Tax::get_tax_rate($product['products_tax_class_id'])
                );
            } else {
                $product['price'] = $currencies->display_price(
                    \common\helpers\Product::get_products_price($product['products_id'], 1, $product['products_price']),
                    \common\helpers\Tax::get_tax_rate($product['products_tax_class_id'])
                );
            }
        }

        if ($this->settings[0]['element'] == 'stock') {
            if (!$product->checkAttachedDetails($products::TYPE_STOCK)){
                $products_quantity = \common\helpers\Product::get_products_stock($products_id);
                $product['stock'] = StockIndication::product_info(array(
                    'products_id' => $products_id,
                    'products_quantity' => $products_quantity,
                ));
            } else {
                $product['stock'] = $product[$products::TYPE_STOCK];
            }
        }

        if (in_array($this->settings[0]['element'], ['image', 'image_med', 'image_lrg'])) {
            if ($this->settings[0]['element'] == 'image') {
                $product['image'] = Images::getImageUrl($product['products_id'], 'Small');
            } elseif ($this->settings[0]['element'] == 'image_med') {
                $product['image'] = Images::getImageUrl($product['products_id'], 'Medium');
            } elseif ($this->settings[0]['element'] == 'image_lrg') {
                $product['image'] = Images::getImageUrl($product['products_id'], 'Large');
            }
            $image_tags_arr = Images::getImageTags($product['products_id']);
            $product['image_alt'] = $image_tags_arr['alt_tag'];
            $product['image_title'] = $image_tags_arr['title_tag'];
        }

        if ($this->settings[0]['add_link']) {
            $product['link'] = Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $products_id]);
        }

        if ($this->settings[0]['element'] == 'properties') {
            $product['properties'] = \frontend\design\Info::getProductProperties($products_id);
        }
        
        return IncludeTpl::widget([
            'file' => 'boxes/product-element.tpl',
            'params' => [
                'product' => $product,
                'settings' => $this->settings[0]
            ]
        ]);
    }
}