<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_products_status_manual_matrix".
 *
 * @property integer $orders_products_status_manual_id
 * @property integer $orders_products_status_id
 */
class OrdersProductsStatusManualMatrix extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'orders_products_status_manual_matrix';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orders_products_status_manual_id', 'orders_products_status_id'], 'required'],
            [['orders_products_status_manual_id', 'orders_products_status_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orders_products_status_manual_id' => 'Orders Products Status Manual ID',
            'orders_products_status_id' => 'Orders Products Status ID',
        ];
    }
}
