<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "languages_data".
 *
 * @property int $language_data_id
 * @property string $language_code
 * @property string $icon
 * @property string $language_iso
 * @property string $language_name
 */
class LanguagesData extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'languages_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['language_code', 'icon', 'language_iso', 'language_name'], 'required'],
            [['language_code'], 'string', 'max' => 5],
            [['icon'], 'string', 'max' => 64],
            [['language_iso'], 'string', 'max' => 15],
            [['language_name'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'language_data_id' => 'Language Data ID',
            'language_code' => 'Language Code',
            'icon' => 'Icon',
            'language_iso' => 'Language Iso',
            'language_name' => 'Language Name',
        ];
    }
}
