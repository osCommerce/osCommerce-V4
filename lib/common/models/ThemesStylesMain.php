<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "themes_styles_main".
 *
 * @property string $theme_name
 * @property string $name
 * @property string $value
 * @property string $type
 * @property int $sort_order
 * @property int $main_style
 */
class ThemesStylesMain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'themes_styles_main';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['theme_name', 'name'], 'required'],
            [['sort_order', 'main_style'], 'integer'],
            [['theme_name', 'name', 'value', 'type'], 'string', 'max' => 255],
            [['theme_name', 'name'], 'unique', 'targetAttribute' => ['theme_name', 'name']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'theme_name' => 'Theme Name',
            'name' => 'Name',
            'value' => 'Value',
            'type' => 'Type',
            'sort_order' => 'Sort Order',
            'main_style' => 'Main Style',
        ];
    }
}
