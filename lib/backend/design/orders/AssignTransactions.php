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

class AssignTransactions extends Widget {
    
    public $manager;
    public $orders_id;
            
    public function init(){
        parent::init();
    }
    
    public function run(){
        $modules = $this->manager->getPaymentCollection()->getTransactionSearchModules();
        if ($this->manager->isInstance() ) {
          $order = $this->manager->getOrderInstance();
        }
        $list = [];
        if ($modules){
            foreach($modules as $module){
                $list[$module->code] = $module->title;
            }
        }
        return $this->render('assign-transaction',[
            'list' => $list,
            'url' => Yii::$app->urlManager->createUrl(['orders/transactions', 'orders_id' => $this->orders_id, 'platform_id' => ($order->info['platform_id']??0)])
        ]);
    }
}
