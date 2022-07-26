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
use frontend\design\Info;

class OrderTracking extends Widget
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
        
        $order_id = (int)Yii::$app->request->get('order_id');
        $manager = \common\services\OrderManager::loadManager();
        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $order_id);
        Info::addBoxToCss('tracking-numbers');



        $customer_info_query = tep_db_query("select customers_id, tracking_number from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
        $customer_info = tep_db_fetch_array($customer_info_query);

        $trackingsArr = [];
        $customers_id = $customer_info['customers_id'];
        if ($customer_info && tep_not_null($customer_info['tracking_number'])) {
            $trackings = explode(";", $customer_info['tracking_number']);
            foreach ($trackings as $i => $track) {
                if ($track) {
                    $tracking_data = \common\helpers\Order::parse_tracking_number($track);
                    $trackingsArr[] = [
                        'url' => $tracking_data['url'],
                        'number' => $tracking_data['number'],
                        'qr_code_url' => Yii::$app->urlManager->createUrl([
                            'account/order-qrcode',
                            'oID' => $order_id,
                            'cID' => $customers_id,
                            'tracking' => '1',
                            'tracking_number' => $track
                        ]),
                    ];
                }
            }
        }
// {{
        $get_trackings_query = tep_db_query("select trn.tracking_numbers_id, trn.tracking_carriers_id, trn.tracking_number from " . TABLE_ORDERS . " o left join " . TABLE_TRACKING_NUMBERS . " trn on o.orders_id = trn.orders_id where o.orders_id = '" . (int) $order_id . "'");
        if (tep_db_num_rows($get_trackings_query) > 0) {
            $trackingsArr = [];
            while ($get_trackings = tep_db_fetch_array($get_trackings_query)) {
                $productsArr = [];
                $tracking_products_query = tep_db_query("select orders_products_id, products_quantity from " . TABLE_TRACKING_NUMBERS_TO_ORDERS_PRODUCTS . " where tracking_numbers_id = '" . (int) $get_trackings['tracking_numbers_id'] . "' and orders_id = '" . (int) $order_id . "'");
                while ($tracking_products = tep_db_fetch_array($tracking_products_query)) {
                    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                        if ($order->products[$i]['orders_products_id'] == $tracking_products['orders_products_id']) {
                            $productsArr[] = $order->products[$i];
                            $productsArr[count($productsArr)-1]['qty'] = $tracking_products['products_quantity'];
                        }
                    }
                }
                $tracking_data = \common\helpers\Order::parse_tracking_number($get_trackings['tracking_number']);
                if ( $get_trackings['tracking_number']) {
                    $trackingsArr[] = [
                        'url' => $tracking_data['url'],
                        'number' => $tracking_data['number'],
                        'qr_code_url' => Yii::$app->urlManager->createUrl([
                            'account/order-qrcode',
                            'oID' => $order_id,
                            'cID' => $customers_id,
                            'tracking' => '1',
                            'tracking_number' => $get_trackings['tracking_number']
                        ]),
                        'products' => $productsArr,
                    ];
                }
            }
        }

        return IncludeTpl::widget(['file' => 'boxes/account/order-tracking.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'trackings' => $trackingsArr,
        ]]);
    }
}