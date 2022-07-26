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
use frontend\design\SplitPageResults;
use common\helpers\Date as DateHelper;

class OrderHeading extends Widget
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
        $orderId = (int)Yii::$app->request->get('order_id');
        $manager = \common\services\OrderManager::loadManager();
        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $orderId);

        return IncludeTpl::widget(['file' => 'boxes/account/order-heading.tpl', 'params' => [
            'settings' => $this->settings,
            'orderId' => method_exists($order, 'getOrderNumber')?$order->getOrderNumber():$orderId,
            'status' => $order->info['orders_status_name'],
            'date' => DateHelper::date_long($order->info['date_purchased']),
        ]]);
    }
}