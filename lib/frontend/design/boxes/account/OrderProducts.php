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

class OrderProducts extends Widget
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
        $customer = Yii::$app->user->getIdentity();

        $order_id = (int)Yii::$app->request->get('order_id');
        $manager = \common\services\OrderManager::loadManager();
        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $order_id);
        $tax_groups = sizeof($order->info['tax_groups']);

        $order_product = array();
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            $order_info['orders_products_id'] = $order->products[$i]['orders_products_id'];
            $order_img = tep_db_fetch_array(tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int) $order->products[$i]['id'] . "'"));
            $order_info['products_image'] = Images::getImageUrl($order->products[$i]['id'], 'Small');
            $order_info['order_product_qty'] = \common\helpers\Product::getVirtualItemQuantity($order->products[$i]['id'], $order->products[$i]['qty']);
            $order_info['order_product_name'] = $order->products[$i]['name'];
            $order_info['product_info_link'] = '';
            $order_info['id'] = $order->products[$i]['id'];
            if (\common\helpers\Product::check_product($order->products[$i]['id'])) {
                $order_info['product_info_link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . \common\helpers\Inventory::get_prid($order->products[$i]['id']));
            }
            $order_info_attr = array();
            if ((isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0)) {
                //$order_info_attr['size'] = sizeof($order->products[$i]['attributes']);
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

            if (isset($order->products[$i]['model']) && $order->products[$i]['model'] == 'VIRTUAL_GIFT_CARD' && isset($order_info_attr[0]['order_pr_value'])) {
                $giftCard = \common\models\VirtualGiftCardInfo::find()->where([
                    'virtual_gift_card_code' => $order_info_attr[0]['order_pr_value'],
                    'customers_id' => $customer->customers_id
                ])->asArray()->one();
                if ($giftCard) {
                    $order_info['gift_card_pdf'] = Yii::$app->urlManager->createUrl(['account/gift-card-pdf', 'gift_card_id' => $giftCard['virtual_gift_card_info_id']]);
                }
            }

            $order_product[] = $order_info;

        }


        return IncludeTpl::widget(['file' => 'boxes/account/order-products.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'tax_groups' => $tax_groups,
            'order_product' => $order_product,
        ]]);
    }
}