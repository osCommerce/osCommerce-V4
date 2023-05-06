<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "design_boxes_groups_images".
 *
 * @property int $boxes_group_image_id
 * @property int $boxes_group_id
 * @property string $file
 */
class DesignBoxesGroupsImages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'design_boxes_groups_images';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['boxes_group_id'], 'integer'],
            [['file'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'boxes_group_image_id' => 'Boxes Group Image ID',
            'boxes_group_id' => 'Boxes Group ID',
            'file' => 'File',
        ];
    }
}
