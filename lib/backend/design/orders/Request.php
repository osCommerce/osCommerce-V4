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

class Request extends Widget {
    
    public $order;
    public $manager;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        if (!\common\helpers\Acl::rule(['ACL_ORDER', 'TEXT_SEND_CUSTOMER_REQUEST'])) {
            return '';
        }
        
        $ot_paid_exist = false;
        $paid = $this->manager->getTotalCollection()->get('ot_paid');
        if ($paid){
            $totals = \yii\helpers\ArrayHelper::map($this->order->totals, 'class', 'value_inc_tax');
            $ot_paid_exist = number_format($totals['ot_total'], 2) > number_format($totals['ot_paid'], 2) || (isset($totals['ot_due']) && (float)$totals['ot_due'] > 0 );
        }
        
        if ($ot_paid_exist && \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
            \common\helpers\Translation::init('admin/orders/order-edit');
            return $this->render('request',[
                'order_id' => $this->order->order_id
            ]);            
        }
    }
}
