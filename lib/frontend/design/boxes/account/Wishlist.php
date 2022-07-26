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

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images;

class Wishlist extends Widget
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
        global $wish_list, $cart;
        $messageStack = \Yii::$container->get('message_stack');
        $currencies = \Yii::$container->get('currencies');
        $message_wish_list = '';
        if ( $messageStack->size('wishlist')>0 ){
            $message_wish_list = $messageStack->output('wishlist');
        }

        $maxItems = (isset($this->settings[0]['max_items']) ? $this->settings[0]['max_items'] : false);

        $products = $wish_list->get_products($maxItems);
        for ($i=0, $n=sizeof($products); $i<$n; $i++) {
            $products[$i]['image'] = Images::getImageUrl($products[$i]['id'], 'Small');
            $products[$i]['link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']);
            $products[$i]['final_price_formatted'] = $currencies->display_price($products[$i]['final_price'], \common\helpers\Tax::get_tax_rate($products[$i]['tax_class_id']));
            $products[$i]['remove_link'] = tep_href_link(FILENAME_WISHLIST,'products_id=' . $products[$i]['id'].'&action=remove_wishlist','SSL');
            $products[$i]['move_in_cart'] = tep_href_link(FILENAME_WISHLIST,'products_id=' . $products[$i]['id'].'&action=wishlist_move_to_cart','SSL');
            $products[$i]['order_quantity_data'] = \common\helpers\Product::get_product_order_quantity($products[$i]['id'], $products[$i]);
            if ( is_object($cart) ) {
                $products[$i]['product_in_cart'] = $cart->in_cart($products[$i]['id']);
            }

            $products[$i]['oos'] = false;
            if (STOCK_ALLOW_CHECKOUT != 'true' && !(\common\helpers\Product::get_products_stock($products[$i]['id']) > 0)) {
                $products[$i]['oos'] = true;
            }
        }
        
        $this->settings[0]['hide_parents'] = (isset($this->settings[0]['hide_parents']) ? $this->settings[0]['hide_parents'] : 0);

        return IncludeTpl::widget(['file' => 'boxes/account/wishlist.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'message_wish_list' => $message_wish_list,
            'link_back_href' => tep_href_link(FILENAME_ACCOUNT,'','NONSSL'),
            'products' => $products,
        ]]);
    }
}