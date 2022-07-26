<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_images_inventory".
 *
 * @property int $products_images_id
 * @property int $inventory_id
 */
class ProductsImagesInventory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_images_inventory';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_images_id', 'inventory_id'], 'required'],
            [['products_images_id', 'inventory_id'], 'integer'],
            [['products_images_id', 'inventory_id'], 'unique', 'targetAttribute' => ['products_images_id', 'inventory_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_images_id' => 'Products Images ID',
            'inventory_id' => 'Inventory ID',
        ];
    }
}
