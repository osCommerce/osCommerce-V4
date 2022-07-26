<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "themes_styles".
 *
 * @property integer $id
 * @property string $theme_name
 * @property string $selector
 * @property string $attribute
 * @property string $value
 * @property string $visibility
 * @property string $media
 * @property string $accessibility
 */
class ThemesStyles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'themes_styles';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['theme_name', 'selector', 'attribute', 'value'], 'string', 'max' => 256],
            [['visibility', 'accessibility'], 'string', 'max' => 64],
            [['media'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'theme_name' => 'Theme Name',
            'selector' => 'Selector',
            'attribute' => 'Attribute',
            'value' => 'Value',
            'visibility' => 'Visibility',
            'media' => 'Media',
            'accessibility' => 'Accessibility',
        ];
    }
}
