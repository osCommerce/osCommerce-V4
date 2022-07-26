<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class OrdersTransactions extends ActiveRecord
{
    public $currency_id;
    
    public static function tableName()
    {
        return 'orders_transactions';
    }
    
    public static function primaryKey(){
        return ['orders_transactions_id'];
    }
    
    public function rules() {
        return [
            [['orders_id', 'payment_class', 'transaction_id', 'transaction_status'], 'required'],
            [['transaction_amount', 'transaction_currency', 'comments', 'admin_id', 'splinters_suborder_id'], 'safe'],
            [['transaction_id'], 'uniqueTransaction']
        ];
    }
    
    public function uniqueTransaction($attribute, $param){
        if (self::hasLinked($this->payment_class, $this->transaction_id, $this->orders_id)){
            $this->addError($attribute, 'Duplicate transaction');
        }
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
    
    public function getTransactionChildren(){
        return $this->hasMany(OrdersTransactionsChildren::className(), ['orders_transactions_id' => 'orders_transactions_id']);
    }
    
    public static function searchUnlinked($payment_class, $transaction_id){
        return self::find()->where(['and',['payment_class' => $payment_class, 'transaction_id' => $transaction_id], ['orders_id' => 0 ]])->one();
    }
    
    public static function hasLinked($payment_class, $transaction_id, $order_id = null){
        $query = self::find()->where(['and',['payment_class' => $payment_class, 'transaction_id' => $transaction_id], ['>', 'orders_id', 0 ]]);
        if (!is_null($order_id)){
            $query->andWhere(['<>', 'orders_id', $order_id]);
        }
        return $query->exists();
    }

    public static function create($order_id, $payment_class, $transaction_id, $status, $amount, $suborder_id /*= null*/, $curerncy, $comments = '', $admin_id= 0){
        $transaction = self::searchUnlinked($payment_class, $transaction_id);
        if (!$transaction){
            $transaction = new self();
        }
        $transaction->orders_id = $order_id;
        $transaction->payment_class = $payment_class;
        $transaction->transaction_id = $transaction_id;
        $transaction->transaction_amount = floatval($amount);
        $transaction->transaction_status = $status;
        $transaction->transaction_currency = $curerncy;
        $transaction->splinters_suborder_id = $suborder_id;
        $transaction->comments = $comments;
        $transaction->admin_id = $admin_id;
        if ($transaction->validate()){
            return $transaction->save();
        }
        return false;
    }
    
    public function getTransactionChild($child_transaction_id){
        if ($this->orders_transactions_id){
            return $this->getTransactionChildren()->where(['transaction_id' => $child_transaction_id])->one();
        }
        return null;
    }

    public function addTransactionChild($transaction_id, $status, $amount, $comments = '', $admin_id = 0){
        if ($this->orders_transactions_id){
            return \common\models\OrdersTransactionsChildren::create($this->orders_transactions_id, $transaction_id, $this->orders_id, $status, $amount, $this->transaction_currency, $comments, $admin_id);
        }
        return null;
    }
    
    public function updateTransactionChild($transaction_id, $status, $amount, $comments = '', $admin_id = 0){
        if ($this->orders_transactions_id){
            $child = $this->getTransactionChild($transaction_id);
            if (!$child){
                return $this->addTransactionChild($transaction_id, $status, $amount, $comments, $admin_id);
            } else {
                $child->transaction_amount = abs($amount);
                $child->transaction_status = $status;
                $child->transaction_currency =  $this->transaction_currency;
                $child->comments = $comments;
                $child->admin_id = $admin_id;
                if ($child->validate()){
                    $child->save();
                }
            }
            return $child;
        }
        return null;
    }
    
    public function getLastChildTransaction(){
        if ($this->orders_transactions_id){
            return \common\models\OrdersTransactionsChildren::find()->where(['orders_transactions_id' => $this->orders_transactions_id ])->max('orders_transactions_child_id');
        }
        return null;
    }
}
