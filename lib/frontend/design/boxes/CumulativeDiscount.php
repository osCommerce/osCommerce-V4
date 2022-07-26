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

class CumulativeDiscount extends Widget
{

  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    global $customer_id;
    $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
    $group_id = 0;
    
    if (defined('DEFAULT_USER_GROUP') && DEFAULT_USER_GROUP){
        $group_id = intval(DEFAULT_USER_GROUP);
    }
    
    if ($customer_groups_id){
        $group_id = $customer_groups_id;
    }
    
    if (isset($this->params['group_id']) && $this->params['group_id']){
        $group_id = $this->params['group_id'];
    }
    
    if ($group_id){
        
        $currencies = \Yii::$container->get('currencies');
        
        $discount_ncs = \common\models\Groups::find()->where('groups_id =:id', [':id' => $group_id])->with('additionalDiscountsNCS')->one();
        $discount_cs = \common\models\Groups::find()->where('groups_id =:id', [':id' => $group_id])->with('additionalDiscountsCS')->one();
        $group = \common\models\Groups::findOne($group_id);
        $current_discount = 0;
        if ($customer_id){
            $current_discount = \common\helpers\Customer::get_additional_discount($group_id, $customer_id) + $group->groups_discount;
        }
        if ($discount_ncs->additionalDiscountsNCS || $discount_cs->additionalDiscountsCS){
            
            return IncludeTpl::widget([
                'file' => 'boxes/cumulative-discount.tpl',
                'params' => [
                    'discount_cs' => $discount_cs,
                    'discount_ncs' => $discount_ncs,
                    'current_discount' => $current_discount,
                    'group' => $group,
                    'currencies' => $currencies,
                    'settings' => $this->settings,
                    'id' => $this->id
                ]
            ]);
        }
    }
    return '';
    
  }
}