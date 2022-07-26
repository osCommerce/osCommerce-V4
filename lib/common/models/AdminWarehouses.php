<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_warehouses".
 *
 * @property int $admin_id
 * @property int $warehouse_id
 */
class AdminWarehouses extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_warehouses';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id', 'warehouse_id'], 'required'],
            [['admin_id', 'warehouse_id'], 'integer'],
            [['admin_id', 'warehouse_id'], 'unique', 'targetAttribute' => ['admin_id', 'warehouse_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'admin_id' => 'Admin ID',
            'warehouse_id' => 'Warehouse ID',
        ];
    }
}
