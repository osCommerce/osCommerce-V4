<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "themes_settings".
 *
 * @property integer $id
 * @property string $theme_name
 * @property string $setting_group
 * @property string $setting_name
 * @property string $setting_value
 */
class ThemesSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'themes_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['theme_name'], 'string', 'max' => 128],
            [['setting_group', 'setting_name'], 'string', 'max' => 255],
            [['setting_value'], 'string']
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
            'setting_group' => 'Setting Group',
            'setting_name' => 'Setting Name',
            'setting_value' => 'Setting Value',
        ];
    }
}
