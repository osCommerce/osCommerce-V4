<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_stock_delivery_terms_text".
 *
 * @property int $stock_delivery_terms_id
 * @property int $language_id
 * @property string $stock_delivery_terms_text
 * @property string $stock_delivery_terms_short_text
 */
class ProductsStockDeliveryTermsText extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_stock_delivery_terms_text';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['stock_delivery_terms_id', 'language_id'], 'required'],
            [['stock_delivery_terms_id', 'language_id'], 'integer'],
            [['stock_delivery_terms_text', 'stock_delivery_terms_short_text'], 'string', 'max' => 64],
            [['stock_delivery_terms_id', 'language_id'], 'unique', 'targetAttribute' => ['stock_delivery_terms_id', 'language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'stock_delivery_terms_id' => 'Stock Delivery Terms ID',
            'language_id' => 'Language ID',
            'stock_delivery_terms_text' => 'Stock Delivery Terms Text',
            'stock_delivery_terms_short_text' => 'Stock Delivery Terms Short Text',
        ];
    }
}
