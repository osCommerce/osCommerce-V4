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

class OrderData extends Widget
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

        $order_delivery_address = '';
        $order_shipping_method = '';
        if ($order->delivery != false) {
            $order_delivery_address = \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>');
            if (tep_not_null($order->info['shipping_method'])) {
                $order_shipping_method = $order->info['shipping_method'];
            }
        }
        $order_billing = \common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>');
        $payment_method = $order->info['payment_method'] . \common\helpers\Order::getPurchaseOrderId($order);

        return IncludeTpl::widget(['file' => 'boxes/account/order-data.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'order_customer' => $order->customer,
            'order_delivery_address' => $order_delivery_address,
            'order_shipping_method' => $order_shipping_method,
            'order_billing' => $order_billing,
            'payment_method' => $payment_method,
        ]]);
    }
}