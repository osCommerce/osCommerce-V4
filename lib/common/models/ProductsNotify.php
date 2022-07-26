<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_notify".
 *
 * @property int $products_notify_id
 * @property string $products_notify_products_id
 * @property string $products_notify_email
 * @property string $products_notify_name
 * @property int $products_notify_customers_id
 * @property string $products_notify_date
 * @property string $products_notify_sent
 * @property int $suppliers_id
 */
class ProductsNotify extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_notify';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_notify_customers_id', 'suppliers_id'], 'integer'],
            [['products_notify_date', 'products_notify_sent'], 'safe'],
            [['products_notify_products_id'], 'string', 'max' => 255],
            [['products_notify_email', 'products_notify_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_notify_id' => 'Products Notify ID',
            'products_notify_products_id' => 'Products Notify Products ID',
            'products_notify_email' => 'Products Notify Email',
            'products_notify_name' => 'Products Notify Name',
            'products_notify_customers_id' => 'Products Notify Customers ID',
            'products_notify_date' => 'Products Notify Date',
            'products_notify_sent' => 'Products Notify Sent',
            'suppliers_id' => 'Suppliers ID',
        ];
    }
}
