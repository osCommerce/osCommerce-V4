<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "orders_label_to_orders_products".
 *
 * @property int $orders_label_id
 * @property int $orders_id
 * @property int $orders_products_id
 * @property int $products_quantity
 */
class OrdersLabelToOrdersProducts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_label_to_orders_products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orders_label_id', 'orders_id', 'orders_products_id', 'products_quantity'], 'integer'],
            [['orders_label_id', 'orders_products_id'], 'unique', 'targetAttribute' => ['orders_label_id', 'orders_products_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orders_label_id' => 'Orders Label ID',
            'orders_id' => 'Orders ID',
            'orders_products_id' => 'Orders Products ID',
            'products_quantity' => 'Products Quantity',
        ];
    }
}
