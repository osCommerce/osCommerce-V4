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
use frontend\design\Info;

class BatchSelectedProducts extends Widget
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
        $selectedProducts = tep_db_prepare_input(Yii::$app->request->get('products', []));

        $this->settings['listing_type'] = 'batchSelectedProducts' . $this->id;
        if (\frontend\design\Info::themeSetting('group_product_by_product_group')) {
            $products = Info::getListProductsDetails($selectedProducts, array_merge($this->settings, ['groupProductGroups' => false]));
            foreach (array_keys($products) as $idx) {
                unset($products[$idx]['products_groups_name']);
            }
        }else {
            $products = Info::getListProductsDetails($selectedProducts, $this->settings);
        }

        $total_amount = false;
        if (!$this->settings[0]['hide_total_price']) {
            $currencies = \Yii::$container->get('currencies');
            $total = 0;
            foreach ($products as $products_arr) {
                $priceInstance = \common\models\Product\Price::getInstance($products_arr['products_id']);
                $product_price = $priceInstance->getInventoryPrice(['qty' => 1]);
                $special_price = $priceInstance->getInventorySpecialPrice(['qty' => 1]);
                if ($special_price) {
                    $product_price = $special_price;
                }
                $total += $currencies->calculate_price($product_price, \common\helpers\Tax::get_tax_rate($priceInstance->getTaxClassId()));
            }
            $total_amount = $currencies->format($total);
        }

        return IncludeTpl::widget([
            'file' => 'boxes/batch-selected-products.tpl',
            'params' => [
                'products' => $products,
                'total_amount' => $total_amount,
                'settings' => $this->settings,
                'id' => $this->id
            ]
        ]);
    }
}