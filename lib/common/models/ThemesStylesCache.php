<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "themes_styles_cache".
 *
 * @property int $id
 * @property string $theme_name
 * @property string $accessibility
 * @property string $css
 */
class ThemesStylesCache extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'themes_styles_cache';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['css'], 'required'],
            [['css'], 'string'],
            [['theme_name'], 'string', 'max' => 256],
            [['accessibility'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'theme_name' => 'Theme Name',
            'accessibility' => 'Accessibility',
            'css' => 'Css',
        ];
    }
}
