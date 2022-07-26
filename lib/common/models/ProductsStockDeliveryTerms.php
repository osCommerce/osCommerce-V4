<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_stock_delivery_terms".
 *
 * @property int $stock_delivery_terms_id
 * @property int $sort_order
 * @property int $is_default
 * @property string $stock_code
 * @property string $text_stock_code
 * @property int $delivery_delay
 */
class ProductsStockDeliveryTerms extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_stock_delivery_terms';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_order', 'is_default', 'delivery_delay'], 'integer'],
            [['stock_code', 'text_stock_code'], 'string', 'max' => 16],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'stock_delivery_terms_id' => 'Stock Delivery Terms ID',
            'sort_order' => 'Sort Order',
            'is_default' => 'Is Default',
            'stock_code' => 'Stock Code',
            'text_stock_code' => 'Text Stock Code',
            'delivery_delay' => 'Delivery Delay',
        ];
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        foreach (ProductsStockStatusesCrossLink::find()->where(['stock_delivery_terms_id'=>$this->stock_delivery_terms_id])->all() as $link){
            $link->delete();
        }

        return true;
    }


}
