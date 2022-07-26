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

class OrderSubTotals extends Widget
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

        $order_info_ar = $manager->getTotalOutput(true, 'TEXT_ACCOUNT');
        
        /*foreach($order->totals as $ot) {

            if (file_exists( DIR_WS_MODULES . 'order_total/' . $ot['class'] . '.php')) {
                include_once( DIR_WS_MODULES . 'order_total/' . $ot['class'] . '.php');
            }

            if (class_exists($ot['class'])) {
                $orderClass = $ot['class'];
                $object = new $orderClass;
                if (method_exists($object, 'visibility')) {
                    if (true == $object->visibility(\common\classes\platform::defaultId(), 'TEXT_ACCOUNT')) {
                        if (method_exists($object, 'visibility')) {
                            $order_info_ar[] = $object->displayText(\common\classes\platform::defaultId(), 'TEXT_ACCOUNT', $ot);
                        } else {
                            $order_info_ar[] = '';
                        }
                    }
                }
            }
        }*/

        return IncludeTpl::widget(['file' => 'boxes/account/order-sub-totals.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'order_info_ar' => $order_info_ar,
        ]]);
    }
}