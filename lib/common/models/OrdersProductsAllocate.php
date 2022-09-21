<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_products_allocate".
 *
 * @property integer $orders_products_id
 * @property integer $warehouse_id
 * @property integer $suppliers_id
 * @property integer $location_id
 * @property integer $layers_id
 * @property integer $batch_id
 * @property integer $platform_id
 * @property integer $orders_id
 * @property integer $prid
 * @property string $products_id
 * @property integer $allocate_received
 * @property integer $allocate_dispatched
 * @property integer $allocate_delivered
 */
class OrdersProductsAllocate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_products_allocate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders_products_id', 'warehouse_id', 'suppliers_id', 'location_id', 'layers_id', 'batch_id', 'platform_id', 'orders_id', 'prid', 'products_id', 'allocate_received'], 'required'],
            [['orders_products_id', 'warehouse_id', 'suppliers_id', 'location_id', 'layers_id', 'batch_id', 'platform_id', 'orders_id', 'prid', 'allocate_received', 'allocate_dispatched', 'allocate_delivered'], 'integer'],
            [['products_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orders_products_id' => 'Orders Products ID',
            'warehouse_id' => 'Warehouse ID',
            'suppliers_id' => 'Suppliers ID',
            'location_id' => 'Location ID',
            'layers_id' => 'Layers ID',
            'batch_id' => 'Batch ID',
            'platform_id' => 'Platform ID',
            'orders_id' => 'Orders ID',
            'prid' => 'Prid',
            'products_id' => 'Products ID',
            'allocate_received' => 'Allocate Received',
            'allocate_dispatched' => 'Allocate Dispatched',
            'allocate_delivered' => 'Allocate Delivered',
        ];
    }
    
    public function getOrdersProduct(){
        return $this->hasOne(OrdersProducts::className(), ['orders_products_id' => 'orders_products_id']);
    }
    
    public function getProduct(){
        return $this->hasOne(Products::className(), ['products_id' => 'prid']);
    }
    
    public function getInventory(){
        return $this->hasOne(Inventory::className(), ['products_id' => 'products_id']);
    }

    public function beforeSave($insert)
    {
        if ( $insert ) {
            if ( is_null($this->suppliers_price) ) $this->suppliers_price = 0;
        }
        if (!parent::beforeSave($insert)) {
            return false;
        }

        return true;
    }

}
