<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "design_boxes_groups_languages".
 *
 * @property int $boxes_group_languages_id
 * @property int $language_id
 * @property string $title
 * @property string $description
 */
class DesignBoxesGroupsLanguages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'design_boxes_groups_languages';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language_id', 'description'], 'required'],
            [['language_id'], 'integer'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'boxes_group_id' => 'Boxes Group Languages ID',
            'language_id' => 'Language ID',
            'title' => 'Title',
            'description' => 'Description',
        ];
    }
}
