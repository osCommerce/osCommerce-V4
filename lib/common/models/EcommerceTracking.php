<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ecommerce_tracking".
 *
 * @property int $orders_id
 * @property string $services
 * @property string $message_type
 * @property string $via
 * @property string $date_added
 * @property string $extra_info
 */
class EcommerceTracking extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ecommerce_tracking';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orders_id'], 'integer'],
            [['date_added', 'extra_info'], 'required'],
            [['date_added'], 'safe'],
            [['extra_info'], 'string'],
            [['services', 'message_type'], 'string', 'max' => 127],
            [['via'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orders_id' => 'Orders ID',
            'services' => 'Services',
            'message_type' => 'Message Type',
            'via' => 'Via',
            'date_added' => 'Date Added',
            'extra_info' => 'Extra Info',
        ];
    }
}
