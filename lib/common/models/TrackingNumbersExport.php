<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tracking_numbers_export".
 *
 * @property int $id
 * @property int $tracking_numbers_id
 * @property int $payments_id
 * @property int $orders_id
 * @property string $classname
 * @property string $external_id
 * @property int $status
 * @property string $message
 * @property string $date_added
 */
class TrackingNumbersExport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tracking_numbers_export';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tracking_numbers_id', 'orders_id', 'payments_id', 'status'], 'integer'],
            [['message'], 'string'],
            [['date_added'], 'safe'],
            [['classname', 'external_id'], 'string', 'max' => 96],
            [['classname', 'external_id'], 'unique', 'targetAttribute' => ['classname', 'external_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tracking_numbers_id' => 'Tracking Numbers ID',
            'orders_id' => 'Orders ID',
            'payments_id' => 'Payments ID',
            'classname' => 'Classname',
            'external_id' => 'External ID',
            'status' => 'Status',
            'message' => 'Message',
            'date_added' => 'Date Added',
        ];
    }


    public function getPayments(){
        return $this->hasMany(OrdersPayment::className(), ['orders_payment_id' => 'orders_payment_id']);
    }

}
