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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class DeliveryDay extends Widget
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
        //$this->settings[0]['show_day'] = '', 'today', 'next_day', 'today_next_day', 'today_and_next_day'
        
        $deliveryEnds = [];
        
        $cutOffTime = new \common\classes\CutOffTime();
        $showTodayDayDelivery = $cutOffTime->isTodayDelivery();
        $showNexDayDelivery = $cutOffTime->isNextDayDelivery();

        if (in_array($this->settings[0]['show_day'], ['today', 'today_next_day', 'today_and_next_day'])) {
            if ($showTodayDayDelivery) {
                $deliveryEnds[] = [
                    'title' => TEXT_TODAY_DELIVERY,
                    'interval' => \common\helpers\Date::getLeftIntervalTo($cutOffTime->getTodayDeliveryDate())
                ];
                if ($this->settings[0]['show_day'] == 'today_next_day') {
                    $showNexDayDelivery = false;
                }
            }
        }
        
        if (in_array($this->settings[0]['show_day'], ['today_next_day', 'today_and_next_day'])) {
            if ($showNexDayDelivery) {
                $deliveryEnds[] = [
                    'title' => TEXT_NEXT_DAY_DELIVERY,
                    'interval' => \common\helpers\Date::getLeftIntervalTo($cutOffTime->getNextDayDeliveryDate())
                ];
            }
        }
        
        if (count($deliveryEnds) == 0) {
            return '';
        }
        
        return IncludeTpl::widget(['file' => 'boxes/delivery-day.tpl', 'params' => [
            'deliveryEnds' => $deliveryEnds,
            'id'=> $this->id
        ]]);
    }
}