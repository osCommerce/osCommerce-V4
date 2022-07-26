<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "customers_wishlist".
 *
 * @property string $products_id
 * @property integer $customers_id
 * @property string $products_model
 * @property string $products_name
 * @property string $products_price
 * @property string $final_price
 * @property integer $products_quantity
 * @property string $wishlist_name
 * @property string $date_added
 */
class CustomersWishlist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customers_wishlist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['products_id', 'products_name'], 'required'],
            [['products_id'], 'string'],
            [['customers_id', 'products_quantity'], 'integer'],
            [['products_price', 'final_price'], 'number'],
            [['date_added'], 'safe'],
            [['products_model'], 'string', 'max' => 13],
            [['products_name', 'wishlist_name'], 'string', 'max' => 64],
        ];
    }
    public function getProducts()
    {
        return $this->hasOne(Products::className(), ['products_id' => 'products_id']);
    }
    public function getProductsDescriptions()
    {
        return $this->hasOne(ProductsDescription::className(), ['products_id' => 'products_id'])
                    ->viaTable('products', ['products_id' => 'products_id',]);
    }
    public function getCustomers()
    {
        return $this->hasOne(Customers::className(), ['customers_id' => 'customers_id']);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'products_id' => 'Products ID',
            'customers_id' => 'Customers ID',
            'products_model' => 'Products Model',
            'products_name' => 'Products Name',
            'products_price' => 'Products Price',
            'final_price' => 'Final Price',
            'products_quantity' => 'Products Quantity',
            'wishlist_name' => 'Wishlist Name',
            'date_added' => 'Date Added',
        ];
    }
}
