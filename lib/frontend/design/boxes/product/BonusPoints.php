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
use common\helpers\Points;

class BonusPoints extends Widget
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
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $params = Yii::$app->request->get();
    
        if (!$params['products_id']){
            return '';
        }

        $container = Yii::$container->get('products');
        $product = $container->getProduct($params['products_id']);
        if ($product /*&& !$container->checkAttachedDetails($params['products_id'], 'bonus_points_price')/**/){
            $price = (float)($product['price'] ?? ($product['special_price'] ?: $product['products_price']));
            $bonuses = \common\helpers\Product::getBonuses($params['products_id'], $customer_groups_id, $price);
            if ($bonuses) {
                $container->attachDetails($params['products_id'], $bonuses);
            }
        }

        if (!$product['bonus_points_price'] && !$product['bonus_points_cost']) {
            return '';
        }

        return IncludeTpl::widget(['file' => 'boxes/product/bonus-points.tpl', 'params' => [
            'settings' => $this->settings,
            'bonus_points_price' => floor($product['bonus_points_price']),
            'bonus_points_cost' => floor($product['bonus_points_cost']),
            'bonus_coefficient' => $product['bonus_coefficient'] ?? false,
            'bonus_price_cost_currency_formatted' => $product['bonus_price_cost_currency_formatted'] ?? '',
        ]]);
    }
}
