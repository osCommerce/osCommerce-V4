<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "newsletter_products".
 *
 * @property int $products_id
 * @property string|null $uprid
 * @property string $provider
 */
class NewsletterProducts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'newsletter_products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_id', 'provider'], 'required'],
            [['products_id'], 'integer'],
            [['uprid'], 'string', 'max' => 256],
            [['provider'], 'string', 'max' => 96],
            [['products_id', 'provider'], 'unique', 'targetAttribute' => ['products_id', 'provider']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_id' => 'Products ID',
            'uprid' => 'Uprid',
            'provider' => 'Provider',
        ];
    }
}
