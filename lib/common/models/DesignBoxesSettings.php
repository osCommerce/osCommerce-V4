<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "design_boxes_settings".
 *
 * @property int $id
 * @property int|null $box_id
 * @property string $microtime
 * @property string $theme_name
 * @property string|null $setting_name
 * @property string|null $setting_value
 * @property int $language_id
 * @property string $visibility
 */
class DesignBoxesSettings extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'design_boxes_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['box_id', 'language_id'], 'integer'],
            [['setting_value', 'microtime', 'theme_name'], 'string'],
            [['setting_name', 'visibility'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'microtime' => 'microtime',
            'theme_name' => 'theme_name',
            'box_id' => 'Box ID',
            'setting_name' => 'Setting Name',
            'setting_value' => 'Setting Value',
            'language_id' => 'Language ID',
            'visibility' => 'Visibility',
        ];
    }
}
