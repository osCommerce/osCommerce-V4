<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "temporary_stock".
 *
 * @property integer $temporary_stock_id
 * @property integer $warehouse_id
 * @property integer $suppliers_id
 * @property integer $location_id
 * @property integer $layers_id
 * @property integer $batch_id
 * @property string $session_id
 * @property integer $customers_id
 * @property integer $prid
 * @property integer $parent_id
 * @property string $products_id
 * @property string $normalize_id
 * @property integer $temporary_stock_quantity
 * @property integer $not_available_quantity
 * @property string $temporary_stock_datetime
 */
class OrdersProductsTemporaryStock extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'temporary_stock';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'session_id', 'prid', 'products_id', 'normalize_id'], 'required'],
            [['warehouse_id', 'suppliers_id', 'location_id', 'layers_id', 'batch_id', 'customers_id', 'prid', 'parent_id', 'temporary_stock_quantity', 'not_available_quantity', 'specials_id'], 'integer'],
            [['specials_id'], 'default', 0],
            [['products_id', 'normalize_id'], 'string'],
            [['temporary_stock_datetime'], 'safe'],
            [['session_id'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'temporary_stock_id' => 'Temporary Stock ID',
            'warehouse_id' => 'Warehouse ID',
            'suppliers_id' => 'Suppliers ID',
            'location_id' => 'Location ID',
            'layers_id' => 'Layers ID',
            'batch_id' => 'Batch ID',
            'session_id' => 'Session ID',
            'customers_id' => 'Customers ID',
            'prid' => 'Prid',
            'parent_id' => 'Parent ID',
            'products_id' => 'Products ID',
            'normalize_id' => 'Normalize ID',
            'temporary_stock_quantity' => 'Temporary Stock Quantity',
            'not_available_quantity' => 'Not Available Quantity',
            'temporary_stock_datetime' => 'Temporary Stock Datetime',
        ];
    }
}
