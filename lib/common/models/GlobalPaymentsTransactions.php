<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "global_payments_transactions".
 *
 * @property integer $transaction_id
 * @property string $store
 * @property string $srd
 * @property integer $order_id
 * @property string $gp_order_id
 * @property integer $customer_id
 * @property integer $amount
 * @property string $customer_name
 * @property string $card_holder_name
 * @property string $card_details
 * @property string $card_type
 * @property string $exp_date
 * @property string $customer_ref
 * @property string $card_ref
 * @property string $code
 * @property string $raw
 */
class GlobalPaymentsTransactions extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'global_payments_transactions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'customer_id', 'amount'], 'integer'],
            [['raw', 'srd'], 'string'],
            [['store', 'card_type'], 'string', 'max' => 50],
            [['gp_order_id', 'customer_name', 'card_holder_name', 'card_details', 'customer_ref', 'card_ref'], 'string', 'max' => 255],
            [['exp_date'], 'string', 'max' => 10],
            [['code'], 'string', 'max' => 2]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'transaction_id' => 'Transaction ID',
            'srd' => 'Scheme Reference Data',
            'store' => 'Store',
            'order_id' => 'Order ID',
            'gp_order_id' => 'Gp Order ID',
            'amount' => 'Amount',
            'customer_id' => 'Customer ID',
            'customer_name' => 'Customer Name',
            'card_holder_name' => 'Card Holder Name',
            'card_details' => 'Card Details',
            'card_type' => 'Card Type',
            'exp_date' => 'Exp Date',
            'customer_ref' => 'Customer Ref',
            'card_ref' => 'Card Ref',
            'code' => 'Code',
            'raw' => 'Raw',
        ];
    }
}
