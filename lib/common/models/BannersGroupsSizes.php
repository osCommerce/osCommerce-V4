<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "banners_groups_sizes".
 *
 * @property int $id
 * @property int $group_id
 * @property string $banners_group
 * @property int $width_from
 * @property int $width_to
 * @property int $image_width
 * @property int $image_height
 */
class BannersGroupsSizes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners_groups_sizes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'width_from', 'width_to', 'image_width', 'image_height'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Group ID',
            'width_from' => 'Width From',
            'width_to' => 'Width To',
            'image_width' => 'Image Width',
            'image_height' => 'Image Height',
        ];
    }
}
