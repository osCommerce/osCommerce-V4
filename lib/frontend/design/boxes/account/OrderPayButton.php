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

class OrderPayButton extends Widget
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

        $pay_link = false;
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
            $pay_link = $ext::payLink($order_id);
        }
        if (!$pay_link) {
            return '';
        }

        $text = $this->settings[0]['text'];
        if (defined($text)) {
            $text = constant($text);
        }
        if (!$text) {
            $text = $this->settings[0]['link'];
            if (!$this->settings[0]['link']) {
                $text = PAY;
            }
        }
        $page = \common\classes\design::pageName($this->settings[0]['link']);
        if ($page) {
            $url = Yii::$app->urlManager->createUrl(['account', 'page_name' => $page, 'order_id' => $order_id]);
        }  else {
            $url = Yii::$app->urlManager->createUrl(['account', 'order_id' => $order_id]);
        }

        $active = false;
        if (Yii::$app->request->get('page_name') == $page) {
            $active = true;
        }
        
        
        $orderStatus = \common\models\OrdersStatus::getDefaultByOrderEvaluationState(\common\helpers\Order::OES_CANCELLED);
        if (is_object($orderStatus)) {
            $order = \common\helpers\Order::getRecord($order_id);
            if (is_object($order)) {
                if ($orderStatus->orders_status_id == $order->orders_status ) {
                    return '';
                }
            }
        }
        
        
        

        return IncludeTpl::widget(['file' => 'boxes/account/order-pay-button.tpl', 'params' => [
            'settings' => $this->settings,
            'text' => $text,
            'url' => $pay_link,
            'active' => $active,
        ]]);
    }
}