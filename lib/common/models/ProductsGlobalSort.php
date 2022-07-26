<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_global_sort".
 *
 * @property int $products_id
 * @property int $platform_id
 * @property int $sort_order
 */
class ProductsGlobalSort extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_global_sort';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_id', 'platform_id', 'sort_order'], 'required'],
            [['products_id', 'platform_id', 'sort_order'], 'integer'],
            [['products_id', 'platform_id', 'sort_order'], 'unique', 'targetAttribute' => ['products_id', 'platform_id', 'sort_order']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_id' => 'Products ID',
            'platform_id' => 'Platform ID',
            'sort_order' => 'Sort Order',
        ];
    }
}
