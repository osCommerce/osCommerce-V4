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

class MultiQuantity extends Widget {

    public $file;
    public $params;
    public $settings;
    public $option;

    public function init() {
        parent::init();
    }

    public function run() {
        global $cart;
        $params = Yii::$app->request->get();
        $post = Yii::$app->request->post();

        if ($params['products_id'] && !GROUPS_DISABLE_CART) {

            $stock_info = $this->option['mix'];
            $show_quantity_input = true;
            if ($stock_info['flags']['request_for_quote'] || !$stock_info['flags']['can_add_to_cart']) {
                $show_quantity_input = false;
            }
            
            $stock_info['quantity_max'] = (($stock_info['flags']['add_to_cart'])? Product::filter_product_order_quantity($params['products_id'], $stock_info['max_qty'], true) :0) ;
            if ($stock_info['flags']['request_for_quote'] && !$stock_info['quantity_max']){
                $stock_info['quantity_max'] = 99999;
            }
            
            return IncludeTpl::widget(['file' => 'boxes/product/multi-quantity.tpl', 'params' => [
                            'qty' => 0,//$stock_info['quantity_max'] ? 1 : 0,
                            'stock_info' => $stock_info,
                            'quantity_max' => $stock_info['quantity_max'],
                            'show_quantity_input' => $show_quantity_input,
                            'products_id' => $params['products_id'],
                            'order_quantity_data' => Product::get_product_order_quantity($params['products_id']),
            ]]);
        }        
    }

}
