<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tmp_orders_status_history".
 *
 * @property int $orders_status_history_id
 * @property int $orders_id
 * @property int $orders_status_id
 * @property string $date_added
 * @property int|null $customer_notified
 * @property string|null $comments
 * @property int $admin_id
 * @property string|null $smscomments
 */
class TmpOrdersStatusHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tmp_orders_status_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orders_id', 'orders_status_id', 'customer_notified', 'admin_id'], 'integer'],
            [['date_added'], 'safe'],
            [['comments', 'smscomments'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orders_status_history_id' => 'Orders Status History ID',
            'orders_id' => 'Orders ID',
            'orders_status_id' => 'Orders Status ID',
            'date_added' => 'Date Added',
            'customer_notified' => 'Customer Notified',
            'comments' => 'Comments',
            'admin_id' => 'Admin ID',
            'smscomments' => 'Smscomments',
        ];
    }


    public function getStatus() {
        return $this->hasOne(OrdersStatus::className(), ['orders_status_id' => 'orders_status_id']);
    }

    public function getGroup() {
        return $this->hasOne(OrdersStatusGroups::className(), ['orders_status_groups_id' => 'orders_status_groups_id'])->via('status');
    }
}
