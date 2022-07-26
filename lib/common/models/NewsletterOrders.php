<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "newsletter_orders".
 *
 * @property int $orders_id
 * @property string $provider
 */
class NewsletterOrders extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'newsletter_orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orders_id', 'provider'], 'required'],
            [['orders_id'], 'integer'],
            [['provider'], 'string', 'max' => 96],
            [['orders_id', 'provider'], 'unique', 'targetAttribute' => ['orders_id', 'provider']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orders_id' => 'Orders ID',
            'provider' => 'Provider',
        ];
    }
}
