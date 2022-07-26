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
use yii\db\ActiveRecord;
use common\models\queries\InventoryQuery;

/**
 * This is the model class for table "inventory".
 *
 * @property integer $inventory_id
 * @property string $products_id
 * @property integer $prid
 * @property string $products_name
 * @property string $products_model
 * @property integer $products_quantity
 * @property integer $allocated_stock_quantity
 * @property integer $temporary_stock_quantity
 * @property integer $warehouse_stock_quantity
 * @property integer $ordered_stock_quantity
 * @property integer $send_notification
 * @property string $products_ean
 * @property string $products_asin
 * @property string $products_isbn
 * @property integer $non_existent
 * @property string $inventory_price
 * @property string $inventory_discount_price
 * @property string $price_prefix
 * @property string $inventory_full_price
 * @property string $inventory_discount_full_price
 * @property string $products_upc
 * @property string $inventory_weight
 * @property integer $stock_indication_id
 * @property integer $stock_delivery_terms_id
 */
class Inventory extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'inventory';
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'inventory_id' => 'Inventory ID',
            'products_id' => 'Products ID',
            'prid' => 'Prid',
            'products_name' => 'Products Name',
            'products_model' => 'Products Model',
            'products_quantity' => 'Products Quantity',
            'allocated_stock_quantity' => 'Allocated Stock Quantity',
            'temporary_stock_quantity' => 'Temporary Stock Quantity',
            'warehouse_stock_quantity' => 'Warehouse Stock Quantity',
            'ordered_stock_quantity' => 'Ordered Stock Quantity',
            'send_notification' => 'Send Notification',
            'products_ean' => 'Products Ean',
            'products_asin' => 'Products Asin',
            'products_isbn' => 'Products Isbn',
            'non_existent' => 'Non Existent',
            'inventory_price' => 'Inventory Price',
            'inventory_discount_price' => 'Inventory Discount Price',
            'price_prefix' => 'Price Prefix',
            'inventory_full_price' => 'Inventory Full Price',
            'inventory_discount_full_price' => 'Inventory Discount Full Price',
            'products_upc' => 'Products Upc',
            'inventory_weight' => 'Inventory Weight',
            'stock_indication_id' => 'Stock Indication ID',
            'stock_delivery_terms_id' => 'Stock Delivery Terms ID',
        ];
    }
    /** Deprecated
     * one-to-one why???
     * @return object
     */
    public function getSuppliersProduct()
    {
        return $this->hasOne(SuppliersProducts::class, ['uprid' => 'products_id']);
    }
    
    /**
     *
     * @param integer $excludeSupplier
     * @return activeQuery
     */
    public function getActiveSuppliersProducts($excludeSupplier=false) {
      $ret = $this->getSuppliersProducts()->andWhere(['status' => 1]);
      if ( $excludeSupplier ) {
        $ret->andWhere('suppliers_id <> :suppliers_id', [':suppliers_id' => $excludeSupplier]);
      }
      return $ret;
    }

    /**
     * one-to-one
     * @return object
     */
    public function getProduct()
    {
        return $this->hasOne(Products::class, ['products_id' => 'prid']);
    }
    
    public function getSuppliersProducts()
    {
        return $this->hasMany(SuppliersProducts::class, ['uprid' => 'products_id']);
    }

    /**
     * @return InventoryQuery
     */
    public static function find(){
        return new InventoryQuery(static::class);
    }
}
