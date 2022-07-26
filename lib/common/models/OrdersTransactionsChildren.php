<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class OrdersTransactionsChildren extends ActiveRecord
{
    public static function tableName()
    {
        return 'orders_transactions_children';
    }
    
    public static function primaryKey(){
        return ['orders_transactions_child_id'];
    }
    
    public function rules() {
        return [
            [['orders_transactions_id', 'transaction_id', 'transaction_status', 'orders_id'], 'required'],
            [['transaction_amount', 'transaction_currency', 'comments', 'admin_id'], 'safe'],
        ];
    }

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_created'],
                ],
                 'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    
    public static function create($id, $transaction_id, $orders_id, $status, $amount, $currency, $comments, $admin_id){
        if ($id && $transaction_id){
            $child = new self();
            $child->orders_transactions_id = $id;
            $child->transaction_id = $transaction_id;
            $child->orders_id = $orders_id;
            $child->transaction_amount = abs($amount);
            $child->transaction_status = $status;
            $child->transaction_currency = $currency;
            $child->comments = $comments;
            $child->admin_id = $admin_id;
            if ($child->validate()){
                $child->save();
                return $child;
            }
        }
        return false;
    }
    
    public function getOrdersTransaction(){
        return $this->hasOne(OrdersTransactions::className(), ['orders_transactions_id' => 'orders_transactions_id']);
    }
}
