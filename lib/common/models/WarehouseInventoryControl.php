<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "warehouse_inventory_control".
 *
 * @property string $products_id
 * @property integer $platform_id
 * @property integer $warehouse_id
 */
class WarehouseInventoryControl extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'warehouse_inventory_control';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['products_id', 'platform_id'], 'required'],
            [['platform_id', 'warehouse_id'], 'integer'],
            [['products_id'], 'string', 'max' => 160]
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
