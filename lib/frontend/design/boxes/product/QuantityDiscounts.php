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

class QuantityDiscounts extends Widget
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
        $currencies = \Yii::$container->get('currencies');
        $get = Yii::$app->request->get();

        if (!$get['products_id']) {
            return '';
        }

        $products = Yii::$container->get('products');
        $product = $products->getProduct($get['products_id']);

        $discounts = [];
        $dt = \common\helpers\Product::get_products_discount_table($get['products_id']);
        if (!is_array($dt)) {
            return '';
        }

        $products = Yii::$container->get('products');
        $product = $products->getProduct($get['products_id']);
        $discounts[] = [
            'count' => '1',
            'price' => $currencies->display_price($product['special_price'] ? $product['special_price'] : $product['products_price'], $product['tax_rate'])
        ];
        $counter = 1;
        for ($i=0, $n=sizeof($dt); $i<$n; $i=$i+2) {
            if ($dt[$i] > 0) {
                $discounts[] = [
                    'count' => $dt[$i],
                    'price' => $currencies->display_price($dt[$i+1], $product['tax_rate'])
                ];
                if ($discounts[$counter - 1]) {
                    $discounts[$counter - 1]['count'] = $discounts[$counter - 1]['count'] . ' - ' . ($discounts[$counter]['count'] - 1);
                }
                $counter++;
            }
        }

        if ($discounts[$counter - 1]) {
            $discounts[$counter - 1]['count'] = $discounts[$counter - 1]['count'] . ' + ';
        }

        if (count($discounts)>0) {
            return IncludeTpl::widget(['file' => 'boxes/product/quantity-discounts.tpl', 'params' => [
                'discounts' => $discounts,
            ]]);
        }
    }
}