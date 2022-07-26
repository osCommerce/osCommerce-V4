<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sms_templates".
 *
 * @property int $sms_templates_id
 * @property string $sms_templates_key
 * @property int $sms_templates_type_id
 */
class SmsTemplates extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sms_templates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sms_templates_key'], 'required'],
            [['sms_templates_type_id'], 'integer'],
            [['sms_templates_key'], 'string', 'max' => 255],
            [['sms_templates_key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sms_templates_id' => 'Sms Templates ID',
            'sms_templates_key' => 'Sms Templates Key',
            'sms_templates_type_id' => 'Sms Templates Type ID',
        ];
    }
}
