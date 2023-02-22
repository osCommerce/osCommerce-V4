<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "banners_groups_images".
 *
 * @property int $id
 * @property int $banners_id
 * @property int $image_width
 * @property string $image
 */
class BannersGroupsImages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners_groups_images';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['banners_id', 'image_width', 'language_id'], 'integer'],
            [['image'], 'string', 'max' => 255],
            [['fit', 'position'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'banners_id' => 'Banners ID',
            'language_id' => 'Language ID',
            'image_width' => 'Image Width',
            'image' => 'Image',
            'fit' => 'fit',
            'position' => 'position',
        ];
    }
}
