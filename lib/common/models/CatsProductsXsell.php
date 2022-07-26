<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "cats_products_xsell".
 *
 * @property int $cats_products_xsell_id
 * @property int $categories_id
 * @property int $xsell_products_id
 * @property int $sort_order
 * @property int $xsell_type_id
 */
class CatsProductsXsell extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cats_products_xsell';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categories_id', 'xsell_products_id', 'sort_order', 'xsell_type_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'cats_products_xsell_id' => 'Cats Products Xsell ID',
            'categories_id' => 'Categories ID',
            'xsell_products_id' => 'Xsell Products ID',
            'sort_order' => 'Sort Order',
            'xsell_type_id' => 'Xsell Type ID',
        ];
    }
}
