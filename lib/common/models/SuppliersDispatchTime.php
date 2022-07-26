<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "suppliers_dispatch_time".
 *
 * @property int $suppliers_dispatch_time_id
 * @property int $suppliers_id
 * @property int $days
 * @property int $time_from
 * @property int $time_to
 */
class SuppliersDispatchTime extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'suppliers_dispatch_time';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['suppliers_id'], 'required'],
            [['suppliers_id', 'days', 'time_from', 'time_to'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'suppliers_dispatch_time_id' => 'Suppliers Dispatch Time ID',
            'suppliers_id' => 'Suppliers ID',
            'days' => 'Days',
            'time_from' => 'Time From',
            'time_to' => 'Time To',
        ];
    }
}
