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

class Customer extends Widget {
    
    public $manager;
    public $order;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        
        $customer_id = $this->order->customer['customer_id'];
        $customer_exists = \common\models\Customers::findOne($customer_id);
        $customerLink = '';
        if ($customer_exists){
            if ($this->order->customer){
                $customerLink = \common\helpers\Address::address_format($this->order->customer['format_id'], $this->order->customer, 1, '', '<br>');
            } else {
                $customerLink = $this->order->customer['name'];
            }
        }
        
        $admin_name = '';
        if (!$customer_id){
            $admin_id = $this->order->info['admin_id'];
            if ($admin_id){
                $admin = new \backend\models\Admin($admin_id);
                if ($admin){
                    $admin_name = $admin->getInfo('admin_firstname') . ' ' . $admin->getInfo('admin_lastname');
                }
            }
        }
        
        return $this->render('customer', [
            'order' => $this->order,           
            'customer_id' => $customer_id,
            'customerExists' => $customer_exists,
            'customerLink' => $customerLink,
            'admin_name' => $admin_name,
            ]);
    }
}
