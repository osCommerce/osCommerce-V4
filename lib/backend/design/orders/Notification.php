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

class Notification extends Widget {
    
    public $manager;
            
    public function init(){
        parent::init();
    }
    
    public function run(){
        $messages = [];
        if ($this->manager->getOrderSplitter()->hasUnclosedRma($this->manager->getOrderInstance()->order_id)){
            $messages[] = ['type' => 'danger', 'message' => TEXT_CHECK_UNCLOSED_CREDITNNOTE];
        }
        global $login_id;
        $check = \common\models\AdminShoppingCarts::find()->where(['and',
                    ['<>', 'admin_id', $login_id],
                    ['customers_id' => $this->manager->getOrderInstance()->customer['customer_id']],
                    ['order_id' => $this->manager->getOrderInstance()->order_id],
                    ['cart_type' => 'cart']
                ])->one();
        if ($check && $check->status) {
            
            $name = "Admin";
            $admin = \common\models\Admin::findOne($check->admin_id);
            if (is_object($admin)) {
                $name = $admin->admin_firstname . " " . $admin->admin_lastname;
            }
            $messages[] = ['type' => 'danger', 'message' => sprintf(WARNING_ORDER_BUSSY, $name, $check->updated_at)];
        }
        
        if ($messages){
            return $this->render('notification', [
                'messages' => $messages
            ]);
        }
    }
}
