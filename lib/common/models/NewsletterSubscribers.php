<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "newsletter_subscribers".
 *
 * @property int $customers_id
 * @property string $provider
 * @property int $customer_type 0-customer, 1-subscriber
 */
class NewsletterSubscribers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'newsletter_subscribers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customers_id', 'provider'], 'required'],
            [['customers_id', 'customer_type'], 'integer'],
            [['provider'], 'string', 'max' => 96],
            [['customers_id', 'provider'], 'unique', 'targetAttribute' => ['customers_id', 'provider']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'customers_id' => 'Customers ID',
            'provider' => 'Provider',
            'customer_type' => 'Customer Type',
        ];
    }
}
