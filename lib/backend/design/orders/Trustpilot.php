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

class Trustpilot extends Widget {
    
    public $order;
    public $manager;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){
       if ($TrustpilotClass = \common\helpers\Acl::checkExtensionAllowed('Trustpilot', 'allowed')) {
            return $this->render('trustpilot', [
                'block' => $TrustpilotClass::viewOrder($this->order)
            ]);
        }
    }
}
