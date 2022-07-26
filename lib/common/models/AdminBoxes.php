<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_boxes".
 *
 * @property integer $box_id
 * @property integer $parent_id
 * @property integer $sort_order
 * @property string $acl_check
 * @property string $config_check
 * @property integer $box_type
 * @property string $path
 * @property string $title
 * @property string $filename
 */
class AdminBoxes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_boxes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'sort_order', 'box_type', 'path', 'title'], 'required'],
            [['parent_id', 'sort_order', 'box_type'], 'integer'],
            [['acl_check', 'config_check', 'path', 'title', 'filename'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'box_id' => 'Box ID',
            'parent_id' => 'Parent ID',
            'sort_order' => 'Sort Order',
            'acl_check' => 'Acl Check',
            'config_check' => 'Config Check',
            'box_type' => 'Box Type',
            'path' => 'Path',
            'title' => 'Title',
            'filename' => 'Filename',
        ];
    }
}
