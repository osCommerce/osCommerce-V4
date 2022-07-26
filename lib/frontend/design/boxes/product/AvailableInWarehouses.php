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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\helpers\Address;

class AvailableInWarehouses extends Widget
{

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    $params = Yii::$app->request->get();
    $ret = '';
    
    $prid = $params['products_id'];

    $warehousesStockQuery = \common\models\WarehousesProducts::find()
        ->alias('p')
        ->joinWith(['platformWarehouses'])
        ->select(['p.warehouse_id', 'sum(warehouse_stock_quantity) as warehouse_stock_quantity'])
        ->groupBy(['p.warehouse_id'])
        ->having('warehouse_stock_quantity>0');
    /*
     *         (
            [show_address] => 1
            [show_time] => 1
            [show_qty] => 1
            [show_qty_less] => 5
            [show_qty_level1] => 3
            [show_qty_level2] => 10
     */

    if ($this->settings[0]['show_address'] == 1) {
      $warehousesStockQuery->with(['warehouseAddress']);
    }
    
    if (false && $this->settings[0]['show_time'] == 1) { ///not exists yet
      $warehousesStockQuery->with(['warehouseTime']);
    }

    if (strpos($prid, '{') !== false) {
        $warehousesStockQuery->andWhere(['products_id' => tep_db_input($prid)]);
    } else {
        $warehousesStockQuery->andWhere(['products_id' => (int) $prid]);
    }
    $warehousesStock = $warehousesStockQuery->asArray()->all();

    if ($warehousesStock) {
      $wInfo = [];
      foreach ($warehousesStock as $w ) {
        
        $address_format_id = $w['warehouseAddress']['country']['address_format_id'];
        $w['warehouseAddress']['country'] = $w['warehouseAddress']['country']['countries_name'];
        $aBook = array_pop(Address::skipEntryKey([$w['warehouseAddress']]));
        unset($aBook['company']);
        unset($aBook['company_vat']);
        unset($aBook['company_reg_number']);

        $wInfo[] = ['address' => Address::address_format($address_format_id, $aBook, true, ' ', '<br>'),
                    'name' => $w['platformWarehouses']['warehouse_name'],
                    'quantity' => $w['warehouse_stock_quantity'],
            ];
      }
      $ret = IncludeTpl::widget(['file' => 'boxes/product/warehouses.tpl', 'params' => [
        'warehousesStock' => $wInfo,
        'params'=> $this->params,
        'settings'=> $this->settings,
        'id' => $this->id
      ]]);
    }

    
    return $ret;
    
  }
}