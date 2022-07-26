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

class OrderCancelAndReorder extends Widget
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

        $cancel_and_restart = false;
        //1 is new orders, 5 - cancelled orders - imho:it is not good idea to use ids
        $orderModel = \common\models\Orders::find()->where(['orders_id' => $order_id])->joinWith(['ordersStatusGroup sg'])
            ->where(['in', 'sg.orders_status_groups_id', [1,5]])->andWhere(['orders_id' => $order_id])->one();

        if (!$orderModel){
            return '';
        }
        $cancel_and_restart = Yii::$app->urlManager->createUrl(['checkout/restart', 'order_id' => $order_id]);

        $pay_link = false;
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
            $pay_link = $ext::payLink($order_id);
        }

        return IncludeTpl::widget(['file' => 'boxes/account/order-cancel-and-reorder.tpl', 'params' => [
            'settings' => $this->settings,
            'cancel_and_restart' => $cancel_and_restart,
            'pay_link' => $pay_link,
            'id' => $this->id,
        ]]);
    }
}