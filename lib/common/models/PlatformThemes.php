<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "platforms_to_themes".
 *
 * @property integer $platform_id
 * @property integer $theme_id
 * @property integer $is_default
 */
class PlatformThemes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'platforms_to_themes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id', 'theme_id', 'is_default'], 'required'],
            [['platform_id', 'theme_id', 'is_default'], 'integer'],
            [['platform_id', 'theme_id'], 'unique', 'targetAttribute' => ['platform_id', 'theme_id'], 'message' => 'The combination of Platform ID and Theme ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'platform_id' => 'Platform ID',
            'theme_id' => 'Theme ID',
            'is_default' => 'Is Default',
        ];
    }
}
