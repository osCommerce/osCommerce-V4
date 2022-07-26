<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class OrdersComments extends ActiveRecord
{
    public static function tableName()
    {
        return 'orders_comments';
    }
    
    public static function primaryKey(){
        return ['orders_comments_id'];
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added'],
                ],
                 'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    
    public function getAdmin(){
        return $this->hasOne(Admin::className(), ['admin_id' => 'admin_id']);
    }

    /**
     * Create Order|invoice comment
     * @param type $order_id
     * @param type $admin_id
     * @param type $order_comment
     * @param type $toInvoice
     * @param type $visible - array('owner' => '') 
     * @return \self
     */
    public static function create($order_id, $admin_id, $order_comment, $toInvoice = false, $visible = array()){
        $comment = null;
        if ($toInvoice){
            $comment = self::find()->where(['orders_id' => $order_id, 'for_invoice' => 1])->one();
        }
        if (!$comment)
            $comment = new self();
        $comment->setAttributes([
            'orders_id' => (int)$order_id,
            'comments' => strval($order_comment),
            'admin_id' => $admin_id,
            'for_invoice' => (int)$toInvoice,
            'visible' => json_encode($visible)
        ], false);
        $comment->save(false);
        return $comment;
    }
    
    public static function findInvoiceComment($order_id){
        return self::find()->where(['orders_id' => $order_id, 'for_invoice' => 1])->one();
    }
}
