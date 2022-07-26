<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "themes_groups".
 *
 * @property int $themes_group_id
 * @property string $title
 */
class ThemesGroups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'themes_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'themes_group_id' => 'Themes Group ID',
            'title' => 'Title',
        ];
    }
}
