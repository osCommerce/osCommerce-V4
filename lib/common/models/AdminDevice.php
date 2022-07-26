<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_device".
 *
 * @property string $ad_device_id
 * @property int $ad_admin_id
 * @property string $ad_date_login
 * @property int $ad_login_count
 * @property string $ad_date_add
 * @property string $ad_is_blocked
 */
class AdminDevice extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_device';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ad_device_id', 'ad_admin_id', 'ad_date_login', 'ad_login_count'], 'required'],
            [['ad_admin_id', 'ad_login_count', 'ad_is_blocked'], 'integer'],
            [['ad_date_login', 'ad_date_add'], 'safe'],
            [['ad_device_id'], 'string', 'max' => 32],
            [['ad_device_id', 'ad_admin_id'], 'unique', 'targetAttribute' => ['ad_device_id', 'ad_admin_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ad_device_id' => 'Ad Device ID',
            'ad_admin_id' => 'Ad Admin ID',
            'ad_date_login' => 'Ad Date Login',
            'ad_login_count' => 'Ad Login Count',
            'ad_date_add' => 'Ad Date Add',
            'ad_is_blocked' => 'Ad Is Blocked',
        ];
    }
}
