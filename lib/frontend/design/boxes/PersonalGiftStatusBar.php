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

class PersonalGiftStatusBar extends Widget
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
        if (Yii::$app->user->isGuest || !\common\helpers\Acl::checkExtensionAllowed('Promotions')) return '';
        
        $customer = Yii::$app->user->getIdentity();
        
        $conditions = \common\models\promotions\PromotionService::getPesonalizedPromo($customer);
        if ($conditions && $conditions['time_line']) {
            
            end($conditions['time_line']);
            $end  = floatval(key($conditions['time_line']));
            $bar = [
                'start' => 0,
                'current' => floatval($conditions['ordered']),
                'end' => $end,
            ];
            $bar['next'] = $this->_getNext($bar['current'], $conditions['time_line']);
            if (is_null($bar['next']) || !$bar['next']) return;
            
            $bar['time_end'] = ($conditions['promo']->promo_date_expired ? \common\helpers\Date::formatDateTime($conditions['promo']->promo_date_expired):'');
            return IncludeTpl::widget(['file' => 'boxes/personal-gift-status-bar.tpl', 'params' => [
                'settings' => $this->settings,
                'params' => $this->params,
                'bar' => $bar,
                'currencies' => Yii::$container->get('currencies'),
                'currency' => Yii::$app->settings->get('currency'),
            ]]);
        }
    }
    
    private function _getNext($current, $timeLine){
        
        $nextAmount = null;
        foreach($timeLine as $amount => $product){
            if ($current < $amount && is_null($nextAmount)) {
                $nextAmount = floatval($amount);
            }
        }
        return $nextAmount;
    }
}