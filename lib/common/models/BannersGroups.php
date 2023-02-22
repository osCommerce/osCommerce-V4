<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "banners_groups".
 *
 * @property int $id
 * @property string $banners_group
 * @property int $width_from
 * @property int $width_to
 * @property int $image_width
 * @property int $image_height
 */
class BannersGroups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banners_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['banners_group'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'banners_group' => 'Banners Group',
        ];
    }
}
