<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_login_session_logoff".
 *
 * @property string $alsl_hash
 * @property int $alsl_admin_id
 * @property string $alsl_device_id
 * @property string $alsl_date_expire
 */
class AdminLoginSessionLogoff extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_login_session_logoff';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alsl_hash', 'alsl_admin_id', 'alsl_device_id', 'alsl_date_expire'], 'required'],
            [['alsl_admin_id'], 'integer'],
            [['alsl_date_expire'], 'safe'],
            [['alsl_hash', 'alsl_device_id'], 'string', 'max' => 32],
            [['alsl_hash'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'alsl_hash' => 'Alsl Hash',
            'alsl_admin_id' => 'Alsl Admin ID',
            'alsl_device_id' => 'Alsl Device ID',
            'alsl_date_expire' => 'Alsl Date Expire',
        ];
    }
}