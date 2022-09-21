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

namespace common\models;

use Yii;

/**
 * This is the model class for table "warehouses_products".
 *
 * @property int $warehouse_id
 * @property string $products_id
 * @property int $prid
 * @property int $products_quantity
 * @property int $allocated_stock_quantity 
 * @property int $temporary_stock_quantity
 * @property int $warehouse_stock_quantity
 * @property int $ordered_stock_quantity
 * @property int $suppliers_id
 * @property int $location_id
 * @property int $layers_id
 * @property int $batch_id
 */
class WarehousesProducts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'warehouses_products';
    }
    
    public static function primaryKey() {
        return ['warehouse_id', 'products_id', 'suppliers_id', 'location_id', 'layers_id', 'batch_id'];
    }
    
    public function getWarehouse(){
        return $this->hasOne(Warehouses::className(), ['warehouse_id' => 'warehouse_id']);
    }
    
    public function getWarehousePlatform(){
        return $this->hasOne(WarehousesPlatforms::className(), ['warehouse_id' => 'warehouse_id'])->via('warehouse');
    }

/**
 * list of warehouses linked to current platform which have product.
 * @return type
 */
    public function getPlatformWarehouses() {
        return $this->hasOne(Warehouses::className(), ['warehouse_id' => 'warehouse_id'])->alias('w')->andWhere(['w.status' => 1])->orderBy('w.sort_order')
                            ->joinWith('warehousePlatform wp', true, 'inner join')->andWhere(['wp.status' => 1, 'platform_id' => \common\classes\platform::currentId()]);
    }

    public function getWarehouseAddress() {
        return $this->hasOne(WarehousesAddressBook::className(), ['warehouse_id' => 'warehouse_id'])->with('country');
    }
/* not exists yet
    public function getWarehouseTime() {
        return $this->hasOne(WarehousesTime::className(), ['warehouse_id' => 'warehouse_id']);
    }
    */
    public function getSupplierProduct(){
        return $this->hasOne(SuppliersProducts::className(), ['suppliers_id' => 'suppliers_id', 'products_id' => 'prid', 'uprid' => 'products_id']);
    }
    
    public function getSupplier(){
        return $this->hasOne(Suppliers::className(), ['suppliers_id' => 'suppliers_id']);
    }

    public function getProduct(){
        return $this->hasOne(Products::className(), ['products_id' => 'prid']);
    }

    public function getLocation(){
        return $this->hasOne(Locations::className(), ['location_id' => 'location_id']);
    }

}
