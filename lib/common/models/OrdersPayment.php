<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_payment".
 *
 * @property int $orders_payment_id
 * @property int $orders_payment_id_parent
 * @property int $orders_payment_order_id
 * @property string $orders_payment_module
 * @property string $orders_payment_module_name
 * @property int $orders_payment_is_credit
 * @property int $orders_payment_status
 * @property string $orders_payment_amount
 * @property string $orders_payment_currency
 * @property string $orders_payment_currency_rate
 * @property string $orders_payment_snapshot
 * @property string $orders_payment_transaction_id
 * @property string $orders_payment_transaction_status
 * @property string $orders_payment_transaction_commentary
 * @property string $orders_payment_transaction_date
 * @property int $orders_payment_admin_create
 * @property string $orders_payment_date_create
 * @property int $orders_payment_admin_update
 * @property string $orders_payment_date_update
 * @property text $orders_payment_transaction_full
 * @property int $deferred
 */
class OrdersPayment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_payment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orders_payment_id_parent', 'orders_payment_order_id', 'orders_payment_is_credit', 'orders_payment_status', 'orders_payment_admin_create', 'orders_payment_admin_update', 'credit_note_id', 'deferred'], 'integer'],
            [['orders_payment_order_id', 'orders_payment_module', 'orders_payment_module_name', 'orders_payment_amount'], 'required'], //orders_payment_snapshot
            [['orders_payment_amount', 'orders_payment_currency_rate'], 'number'],
            [['orders_payment_snapshot', 'orders_payment_transaction_commentary', 'payment_type'], 'string'],
            [['orders_payment_transaction_date', 'orders_payment_date_create', 'orders_payment_date_update', 'orders_payment_transaction_full',  'credit_note_id'], 'safe'],
            [['orders_payment_module', 'orders_payment_transaction_status'], 'string', 'max' => 100],
            [['orders_payment_module_name'], 'string', 'max' => 250],
            [['payment_type'], 'string', 'max' => 30],
            [['orders_payment_currency'], 'string', 'max' => 10],
            [['orders_payment_transaction_id'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orders_payment_id' => 'Orders Payment ID',
            'orders_payment_id_parent' => 'Orders Payment Id Parent',
            'orders_payment_order_id' => 'Orders Payment Order ID',
            'orders_payment_module' => 'Orders Payment Module',
            'orders_payment_module_name' => 'Orders Payment Module Name',
            'orders_payment_is_credit' => 'Orders Payment Is Credit',
            'orders_payment_status' => 'Orders Payment Status',
            'orders_payment_amount' => 'Orders Payment Amount',
            'orders_payment_currency' => 'Orders Payment Currency',
            'orders_payment_currency_rate' => 'Orders Payment Currency Rate',
            'orders_payment_snapshot' => 'Orders Payment Snapshot',
            'orders_payment_transaction_id' => 'Orders Payment Transaction ID',
            'orders_payment_transaction_status' => 'Orders Payment Transaction Status',
            'orders_payment_transaction_commentary' => 'Orders Payment Transaction Commentary',
            'orders_payment_transaction_date' => 'Orders Payment Transaction Date',
            'orders_payment_admin_create' => 'Orders Payment Admin Create',
            'orders_payment_date_create' => 'Orders Payment Date Create',
            'orders_payment_admin_update' => 'Orders Payment Admin Update',
            'orders_payment_date_update' => 'Orders Payment Date Update',
            'deferred' => 'Deferred',
        ];
    }

    public function getOrder() {
      return $this->hasOne(Orders::class, ['orders_id' => 'orders_payment_order_id']);
    }
}
