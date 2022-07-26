<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sms_templates_texts".
 *
 * @property int $sms_templates_id
 * @property string $sms_templates_body
 * @property int $platform_id
 * @property int $language_id
 * @property int $affiliate_id
 */
class SmsTemplatesTexts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sms_templates_texts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sms_templates_id', 'platform_id', 'language_id', 'affiliate_id'], 'required'],
            [['sms_templates_id', 'platform_id', 'language_id', 'affiliate_id'], 'integer'],
            [['sms_templates_body'], 'string'],
            [['sms_templates_id', 'platform_id', 'language_id', 'affiliate_id'], 'unique', 'targetAttribute' => ['sms_templates_id', 'platform_id', 'language_id', 'affiliate_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sms_templates_id' => 'Sms Templates ID',
            'sms_templates_body' => 'Sms Templates Body',
            'platform_id' => 'Platform ID',
            'language_id' => 'Language ID',
            'affiliate_id' => 'Affiliate ID',
        ];
    }
}
