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

namespace backend\design\orders\payments;


use Yii;
use yii\base\Widget;

class Amazone extends Widget {
    
    public $order;
    public $manager;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
        
        if ($amazonInfo = tep_db_fetch_array(tep_db_query("select * from amazon_payment_orders where orders_id ='" . (int)$this->order->order_id . "'"))){
            $allowClose = in_array($amazonInfo['amazon_status'], ['Open']);
            $allowCapture = in_array($amazonInfo['amazon_auth_status'], ['Open']);
            $allowRefund = in_array($amazonInfo['amazon_capture_status'], ['Completed']);
            $amazonLog = array_map('unserialize', explode("#\n\n#", $amazonInfo['custom_data']));
        
            return $this->render('amazone', [
                'manager' => $this->manager,
                'order' => $this->order,
                'allowClose' => $allowClose,
                'allowCapture' => $allowCapture,
                'allowRefund' => $allowRefund,
                'amazonLog' => $amazonLog,
                'amazonInfo' => $amazonInfo,
            ]);
        }
    }
}
