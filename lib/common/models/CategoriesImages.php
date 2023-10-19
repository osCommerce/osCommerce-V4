<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "categories_images".
 *
 * @property int $categories_images_id
 * @property int $categories_id
 * @property string $image
 * @property int $sort_order
 * @property int $platform_id
 * @property int $image_types_id
 * @property int $position
 * @property int $fit
 */
class CategoriesImages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categories_images';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categories_id', 'image'], 'required'],
            [['categories_id', 'sort_order', 'platform_id', 'image_types_id'], 'integer'],
            [['image'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'categories_images_id' => 'Categories Images ID',
            'categories_id' => 'Categories ID',
            'image' => 'Image',
            'sort_order' => 'Sort Order',
            'platform_id' => 'Platform ID',
            'image_types_id' => 'Image types id',
        ];
    }
}
