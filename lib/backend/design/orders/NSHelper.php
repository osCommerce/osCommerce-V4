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
//use backend\models\EP\Provider\NetSuite\Helper as NS;

class NSHelper extends Widget {
    
    public $order_id;
            
    public function init(){
        parent::init();        
    }
    
    public function run(){

      $directories = \backend\models\EP\DataSources::orderViewExport($this->order_id);
      // only not synced yet
      $ret = '';
      if (!empty($directories) && is_array($directories)) {
        foreach($directories as $directory) {
          //$ret .= $this->renderExchangeInfo($directory['directory_id'], $this->order_id);
          $ret .= \backend\controllers\OrdersController::renderExchangeInfo($directory['directory_id'], $this->order_id);
        }
      }

        /*$directoryId = NS::anyConfigured();
        if ($directoryId > 0) {
            return $this->renderExchangeInfo($directoryId, $this->order_id);
        }*/
      return $ret;
    }

//    protected function renderExchangeInfo($directoryId, $orderId)
    
}
