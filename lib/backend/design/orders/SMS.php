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
use common\helpers\Acl;

class SMS extends Widget {
    
    public $order;
    public $manager;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
       if (Acl::checkExtensionAllowed('SMS','showOnOrderPage') && ($sms = Acl::checkExtensionAllowed('SMS', 'allowed')) ){
            $smsBlock = $sms::viewOrder($this->order);
            if ($smsBlock){
                echo $smsBlock;
            }
        }
    }
}
