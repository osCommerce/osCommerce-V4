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

class PriceFrom extends Widget
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

        $dt = \common\helpers\Product::get_products_discount_table($get['products_id']);
        $minPrice = 0;
        if (!is_array($dt)) {
            return '';
        }
        for ($i=1, $n=sizeof($dt); $i<=$n; $i=$i+2) {
            if ($dt[$i] && $dt[$i] < $minPrice || !$minPrice) {
                $minPrice = $dt[$i];
            }
        }

        if (!$minPrice) {
            return '';
        }

        return IncludeTpl::widget(['file' => 'boxes/product/price-from.tpl', 'params' => [
            'price' => $currencies->display_price($minPrice, $product['tax_rate']),
        ]]);
    }
}