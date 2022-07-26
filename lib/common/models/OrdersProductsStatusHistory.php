<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_products_status_history".
 *
 * @property integer $orders_products_history_id
 * @property integer $orders_id
 * @property integer $orders_products_id
 * @property integer $orders_products_status_id
 * @property integer $orders_products_status_manual_id
 * @property string $date_added
 * @property string $comments
 * @property integer $admin_id
 */
class OrdersProductsStatusHistory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_products_status_history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders_id', 'orders_products_id', 'orders_products_status_id', 'orders_products_status_manual_id', 'admin_id'], 'required'],
            [['orders_id', 'orders_products_id', 'orders_products_status_id', 'orders_products_status_manual_id', 'admin_id'], 'integer'],
            [['date_added'], 'safe'],
            [['comments'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orders_products_history_id' => 'Orders Products History ID',
            'orders_id' => 'Orders ID',
            'orders_products_id' => 'Orders Products ID',
            'orders_products_status_id' => 'Orders Products Status ID',
            'orders_products_status_manual_id' => 'Orders Products Status Manual ID',
            'date_added' => 'Date Added',
            'comments' => 'Comments',
            'admin_id' => 'Admin ID',
        ];
    }
}
