<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_login_expire".
 *
 * @property int $ale_id
 * @property int $ale_language_id
 * @property string $ale_title
 * @property int $ale_expire_minutes
 * @property int $ale_order
 */
class AdminLoginExpire extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_login_expire';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ale_id', 'ale_language_id', 'ale_title', 'ale_expire_minutes', 'ale_order'], 'required'],
            [['ale_id', 'ale_language_id', 'ale_expire_minutes', 'ale_order'], 'integer'],
            [['ale_title'], 'string', 'max' => 64],
            [['ale_id', 'ale_language_id'], 'unique', 'targetAttribute' => ['ale_id', 'ale_language_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ale_id' => 'Ale ID',
            'ale_language_id' => 'Ale Language ID',
            'ale_title' => 'Ale Title',
            'ale_expire_minutes' => 'Ale Expire Minutes',
            'ale_order' => 'Ale Order',
        ];
    }
}
