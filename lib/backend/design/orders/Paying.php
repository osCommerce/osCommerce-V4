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

class Paying extends Widget {
    
    public $order;
    public $manager;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        $ot_paid_exist = false;
        $paid = $this->manager->getTotalCollection()->get('ot_paid');
        if ($paid){
            $totals = \yii\helpers\ArrayHelper::map($this->order->totals, 'class', 'value_inc_tax');
            $ot_paid_exist = round($totals['ot_total'], 2) > round($totals['ot_paid'], 2);
        }
        if ($ot_paid_exist){
            return $this->render('paying', [
              'order' => $this->order,
              'amt' => (round($totals['ot_total'], 2) - round($totals['ot_paid'], 2)),
              'trId' => $this->order->info['payment_class'] . date('_ymd_') . $this->order->getOrderId()  . date('_His'),
        
            ]);
        }
    }
}
