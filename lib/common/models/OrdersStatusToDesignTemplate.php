<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_status_to_design_template".
 *
 * @property integer $id
 * @property integer $orders_status_id
 * @property integer $platform_id
 * @property string $email_design_template
 */
class OrdersStatusToDesignTemplate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_status_to_design_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders_status_id', 'platform_id', 'email_design_template'], 'required'],
            [['orders_status_id', 'platform_id'], 'integer'],
            [['email_design_template'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'orders_status_id' => 'Orders Status ID',
            'platform_id' => 'Platform ID',
            'email_design_template' => 'Email Design Template',
        ];
    }
}
