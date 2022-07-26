<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "categories_cache".
 *
 * @property int $categories_id
 * @property int $platform_id
 * @property int $groups_id
 * @property int $products
 */
class CategoriesCache extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categories_cache';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categories_id', 'platform_id', 'groups_id'], 'required'],
            [['categories_id', 'platform_id', 'groups_id', 'products'], 'integer'],
            [['categories_id', 'platform_id', 'groups_id'], 'unique', 'targetAttribute' => ['categories_id', 'platform_id', 'groups_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'categories_id' => 'Categories ID',
            'platform_id' => 'Platform ID',
            'groups_id' => 'Groups ID',
            'products' => 'Products',
        ];
    }
}
