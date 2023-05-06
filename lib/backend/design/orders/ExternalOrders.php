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

namespace backend\design\orders;


use Yii;
use yii\base\Widget;

class ExternalOrders extends Widget {
    
    public $order;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        if ( defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED==true ){
            $departments = \common\classes\department::getList(false);
            $departments = \yii\helpers\ArrayHelper::map($departments, 'departments_id', 'text');
            $departmentInfo = TEXT_FROM . ' ' . ($departments[$this->order->info['department_id']]??TEXT_NONE);
            $api_client_order_id = '';
            if ( $this->order instanceof \common\classes\Order ) {
                $api_client_order_id = Yii::$app->getDb()->createCommand(
                    "SELECT api_client_order_id FROM " . TABLE_ORDERS . " WHERE orders_id = :orders_id",
                    [':orders_id' => $this->order->order_id]
                )->queryScalar();
            }
            if ( $api_client_order_id ) {
                $departmentInfo .= ' (#'.$api_client_order_id.')';
            }
            return $this->render('external-orders', [
                'extra' => $departmentInfo,
            ]);
        }else
        if ($this->order->info['external_orders_id']){
            return $this->render('external-orders', [
                'extra' => $this->order->info['external_orders_id'],
            ]);
        }
    }
}
