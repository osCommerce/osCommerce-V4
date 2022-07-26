<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "products_images_external_url".
 *
 * @property int $products_images_id
 * @property int $image_types_id
 * @property int $language_id
 * @property string $image_url
 */
class ProductsImagesExternalUrl extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products_images_external_url';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['products_images_id', 'image_types_id', 'language_id'], 'required'],
            [['products_images_id', 'image_types_id', 'language_id'], 'integer'],
            [['image_url'], 'string', 'max' => 1024],
            [['products_images_id', 'image_types_id', 'language_id'], 'unique', 'targetAttribute' => ['products_images_id', 'image_types_id', 'language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'products_images_id' => 'Products Images ID',
            'image_types_id' => 'Image Types ID',
            'language_id' => 'Language ID',
            'image_url' => 'Image Url',
        ];
    }
}
