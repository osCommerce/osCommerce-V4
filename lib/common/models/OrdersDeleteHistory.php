<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_delete_history".
 *
 * @property int $orders_history_id
 * @property int $orders_id
 * @property string|null $comments
 * @property int $admin_id
 * @property string $date_added
 */
class OrdersDeleteHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_delete_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orders_id', 'admin_id', 'date_added'], 'required'],
            [['orders_id', 'admin_id'], 'integer'],
            [['comments'], 'string'],
            [['date_added'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orders_history_id' => 'Orders History ID',
            'orders_id' => 'Orders ID',
            'comments' => 'Comments',
            'admin_id' => 'Admin ID',
            'date_added' => 'Date Added',
        ];
    }
}
