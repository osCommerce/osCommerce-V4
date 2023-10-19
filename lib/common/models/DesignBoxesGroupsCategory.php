<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "design_boxes_groups_category".
 *
 * @property int $boxes_group_category_id
 * @property string $parent_category
 * @property string $name
 */
class DesignBoxesGroupsCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'design_boxes_groups_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_category', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'boxes_group_category_id' => 'Boxes Group Category ID',
            'parent_category' => 'Parent Category',
            'name' => 'Name',
        ];
    }
}
