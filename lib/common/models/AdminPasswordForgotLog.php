<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_password_forgot_log".
 *
 * @property string $apflDeviceId
 * @property string $apflDateCreate
 */
class AdminPasswordForgotLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_password_forgot_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['apflDeviceId', 'apflDateCreate'], 'required'],
            [['apflDeviceId', 'apflDateCreate'], 'safe'],
            [['apflDeviceId'], 'string', 'max' => 64],
            [['apflDeviceId', 'apflDateCreate'], 'unique', 'targetAttribute' => ['apflDeviceId', 'apflDateCreate']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'apflDeviceId' => 'Apfl Device ID',
            'apflDateCreate' => 'Apfl Date Create',
        ];
    }

    public static function register()
    {
        $apflRecord = new self();
        $apflRecord->loadDefaultValues(false);
        $apflRecord->setAttributes([
            'apflDeviceId' => self::getDeviceId(),
            'apflDateCreate' => date('Y-m-d H:i:s'),
        ], false);
        return $apflRecord->save(false);
    }

    public static function isBlocked()
    {
        $checkAttempt = 3;
        $checkPeriod = '1 hour';
        return (
            (int)(self::find()
                ->where(['apflDeviceId' => self::getDeviceId()])
                ->andWhere(['>=', 'apflDateCreate', date('Y-m-d H:i:s', strtotime('-' . $checkPeriod))])
                ->count()
            ) >= $checkAttempt
        );
    }

    public static function clear()
    {
        self::deleteAll(['apflDeviceId' => self::getDeviceId()]);
        return true;
    }

    private static function getDeviceId()
    {
        return \common\helpers\System::get_ip_address();
    }
}