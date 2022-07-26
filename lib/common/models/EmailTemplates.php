<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "email_templates".
 *
 * @property int $email_templates_id
 * @property string $email_templates_key
 * @property string $email_template_type
 * @property int $type_id
 */
class EmailTemplates extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'email_templates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email_templates_key'], 'required'],
            [['type_id'], 'integer'],
            [['email_templates_key'], 'string', 'max' => 255],
            [['email_template_type'], 'string', 'max' => 20],
            [['email_templates_key', 'email_template_type'], 'unique', 'targetAttribute' => ['email_templates_key', 'email_template_type']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'email_templates_id' => 'Email Templates ID',
            'email_templates_key' => 'Email Templates Key',
            'email_template_type' => 'Email Template Type',
            'type_id' => 'Type ID',
        ];
    }
}
