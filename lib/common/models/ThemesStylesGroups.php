<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "themes_styles_groups".
 *
 * @property string $theme_name
 * @property string $group_id
 * @property string $group_name
 * @property string $tab
 * @property int $sort_order
 */
class ThemesStylesGroups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'themes_styles_groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sort_order', 'group_id'], 'integer'],
            [['theme_name', 'group_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'theme_name' => 'Theme Name',
            'group_id' => 'Group ID',
            'group_name' => 'Group Name',
            'sort_order' => 'Sort Order',
            'tab' => 'Tab',
        ];
    }
}
