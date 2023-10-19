<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "design_boxes_groups".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $file
 * @property string|null $page_type
 * @property int $status
 * @property string|null $comment
 * @property int $sort_order
 * @property string|null $date_added
 * @property string|null $category
 */
class DesignBoxesGroups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'design_boxes_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'sort_order'], 'integer'],
            [['comment'], 'string'],
            [['date_added'], 'safe'],
            [['name', 'file', 'page_type', 'category'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'file' => 'File',
            'page_type' => 'Page Type',
            'status' => 'Status',
            'comment' => 'Comment',
            'sort_order' => 'Sort Order',
            'date_added' => 'Date Added',
            'category' => 'Category',
        ];
    }
}
