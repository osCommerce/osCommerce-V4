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

namespace common\classes\modules;
use Yii;
use yii\db\Query;


abstract class ModuleDropShipping extends Module{

  public function process($params = []){
      
  }
  
  public function prepareShippingTable(){
      $migration = new \yii\db\Migration();
      if ($migration){
          if ( Yii::$app->db->schema->getTableSchema('dropshipping_ships') === null) {
              tep_db_query("CREATE TABLE IF NOT EXISTS `dropshipping_ships` (
  `dropshipping_ships_id` int(11) NOT NULL AUTO_INCREMENT,
  `platform_id` int(11) NOT NULL,
  `dropshipping_module` varchar(64) DEFAULT NULL,
  `shipping_code` varchar(64) DEFAULT NULL,
  `dropshipping_code`varchar(64) DEFAULT NULL,
  `dropshipping_desc`varchar(255) DEFAULT NULL,  
  PRIMARY KEY (`dropshipping_ships_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
          }
      }
  }
  
  public function getShippingCode($code){
      $platform_id = Yii::$app->get('platform')->config()->getId();
      if (!$platform_id){
          $platform_id = \common\classes\platform::firstId();
      }
      $ds = (new Query())->select('dropshipping_code')->from('dropshipping_ships')
              ->where('platform_id =:plid and dropshipping_module = :dm and shipping_code = :sc', [
                  ':plid' => $platform_id,
                  ':dm' => $this->code,
                  ':sc' => $code
                  ])->one();
      if ($ds) {
          return $ds['dropshipping_code'];
      } else {
          return false;
      }
  }
  
}