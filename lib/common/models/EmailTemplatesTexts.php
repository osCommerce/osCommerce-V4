<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "email_templates_texts".
 *
 * @property int $email_templates_id
 * @property int $platform_id
 * @property int $language_id
 * @property int $affiliate_id
 * @property string $email_templates_subject
 * @property string $email_templates_body
 */
class EmailTemplatesTexts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_templates_texts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email_templates_id', 'platform_id', 'language_id', 'affiliate_id'], 'required'],
            [['email_templates_id', 'platform_id', 'language_id', 'affiliate_id'], 'integer'],
            [['email_templates_body'], 'string'],
            [['email_templates_subject'], 'string', 'max' => 255],
            [['email_templates_id', 'platform_id', 'language_id', 'affiliate_id'], 'unique', 'targetAttribute' => ['email_templates_id', 'platform_id', 'language_id', 'affiliate_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email_templates_id' => 'Email Templates ID',
            'platform_id' => 'Platform ID',
            'language_id' => 'Language ID',
            'affiliate_id' => 'Affiliate ID',
            'email_templates_subject' => 'Email Templates Subject',
            'email_templates_body' => 'Email Templates Body',
        ];
    }
}
