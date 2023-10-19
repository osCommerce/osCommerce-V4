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

namespace frontend\design\boxes\order;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images;

class Products extends Widget
{
    public $file;
    public $params;
    public $settings;
    public $manager;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if ($this->params['order_product']){
            if (is_array($this->params['order_product'])) {
                foreach ($this->params['order_product'] as &$opRecord) {
                    if (isset($opRecord['id']) AND isset($opRecord['order_product_qty'])) {
                        $opRecord['order_product_qty'] = \common\helpers\Product::getVirtualItemQuantity($opRecord['id'], $opRecord['order_product_qty']);
                    }
                }
                unset($opRecord);
            }
            return IncludeTpl::widget(['file' => 'boxes/order/products.tpl', 'params' => [
                'order_product' => $this->params['order_product'],
            ]]);
        }

        $order_info = [];
        $order_product = [];
        $currencies = Yii::$container->get('currencies');
        $order = $this->params['order'];
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            $order_info['products_image'] = Images::getImageUrl($order->products[$i]['id'], 'Small');
            $order_info['order_product_qty'] = \common\helpers\Product::getVirtualItemQuantity($order->products[$i]['id'], $order->products[$i]['qty']);
            $order_info['order_product_name'] = $order->products[$i]['name'];
            $order_info['parent_product'] = $order->products[$i]['parent_product'];
            $order_info['product_info_link'] = '';
            $order_info['id'] = $order->products[$i]['id'];
            if (\common\helpers\Product::check_product($order->products[$i]['id'])) {
                $order_info['product_info_link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . \common\helpers\Inventory::get_prid($order->products[$i]['id']));
            }
            $order_info_attr = [];
            if ((isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0)) {
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                    $order_info_attr[$j]['order_pr_option'] = str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($order->products[$i]['attributes'][$j]['option']));
                    $order_info_attr[$j]['order_pr_value'] = ($order->products[$i]['attributes'][$j]['value'] ? htmlspecialchars($order->products[$i]['attributes'][$j]['value']) : '');
                }
            }
            $order_info['attr_array'] = $order_info_attr;
            if (sizeof($order->info['tax_groups']) > 1) {
                $order_info['order_products_tax'] = \common\helpers\Tax::display_tax_value($order->products[$i]['tax']) . '%';
            }
            $order_info['final_price'] = $currencies->format($currencies->calculate_price_in_order($order->info, $order->products[$i]['final_price'], (DISPLAY_PRICE_WITH_TAX == 'true' ? $order->products[$i]['tax'] : 0), $order->products[$i]['qty']), true, $order->info['currency'], $order->info['currency_value']);
            $order_product[] = $order_info;
        }

        return IncludeTpl::widget(['file' => 'boxes/order/products.tpl', 'params' => [
            'order_product' => $order_product,
        ]]);
    }
}