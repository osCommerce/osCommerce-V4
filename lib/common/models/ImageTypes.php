<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "image_types".
 *
 * @property int $image_types_id
 * @property string $image_types_name
 * @property int $image_types_x
 * @property int $image_types_y
 * @property int $width_from
 * @property int $width_to
 * @property int $parent_id
 */
class ImageTypes extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'image_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image_types_name', 'image_types_x', 'image_types_y'], 'required'],
            [['image_types_x', 'image_types_y', 'width_from', 'width_to', 'parent_id'], 'integer'],
            [['image_types_name'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'image_types_id' => 'Image Types ID',
            'image_types_name' => 'Image Types Name',
            'image_types_x' => 'Image Types X',
            'image_types_y' => 'Image Types Y',
            'width_from' => 'Width From',
            'width_to' => 'Width To',
            'parent_id' => 'Parent ID',
        ];
    }
}
