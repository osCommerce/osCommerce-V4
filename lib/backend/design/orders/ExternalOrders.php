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
        if ($this->order->info['external_orders_id']){
            return $this->render('external-orders', [
                'extra' => $this->order->info['external_orders_id'],
            ]);
        }
    }
}
