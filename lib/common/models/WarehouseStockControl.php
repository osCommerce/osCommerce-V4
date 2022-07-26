<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "warehouse_stock_control".
 *
 * @property integer $products_id
 * @property integer $platform_id
 * @property integer $warehouse_id
 */
class WarehouseStockControl extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'warehouse_stock_control';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['products_id', 'platform_id'], 'required'],
            [['products_id', 'platform_id', 'warehouse_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'products_id' => 'Products ID',
            'platform_id' => 'Platform ID',
            'warehouse_id' => 'Warehouse ID',
        ];
    }
}
