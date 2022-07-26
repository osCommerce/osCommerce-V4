<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "warehouses_open_hours".
 *
 * @property integer $warehouses_open_hours_id
 * @property integer $warehouse_id
 * @property string $open_days
 * @property string $open_time_from
 * @property string $open_time_to
 */
class WarehousesOpenHours extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'warehouses_open_hours';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_id', 'open_days', 'open_time_from', 'open_time_to'], 'required'],
            [['warehouse_id'], 'integer'],
            [['open_days'], 'string', 'max' => 32],
            [['open_time_from', 'open_time_to'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'warehouses_open_hours_id' => 'Warehouses Open Hours ID',
            'warehouse_id' => 'Warehouse ID',
            'open_days' => 'Open Days',
            'open_time_from' => 'Open Time From',
            'open_time_to' => 'Open Time To',
        ];
    }
}
