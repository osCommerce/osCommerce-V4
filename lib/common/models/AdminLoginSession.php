<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_login_session".
 *
 * @property int $als_admin_id
 * @property string $als_device_id
 * @property string $als_date_activity
 * @property string $als_date_login
 */
class AdminLoginSession extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_login_session';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['als_admin_id', 'als_device_id'], 'required'],
            [['als_admin_id'], 'integer'],
            [['als_date_activity', 'als_date_login'], 'safe'],
            [['als_device_id'], 'string', 'max' => 32],
            [['als_admin_id', 'als_device_id'], 'unique', 'targetAttribute' => ['als_admin_id', 'als_device_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'als_admin_id' => 'Als Admin ID',
            'als_device_id' => 'Als Device ID',
            'als_date_activity' => 'Als Date Activity',
            'als_date_login' => 'Als Date Login',
        ];
    }

    public static function updateAdminSession($adminId = 0, $deviceId = '')
    {
        $return = false;
        $adminId = (int)$adminId;
        $deviceId = trim($deviceId);
        try {
            $adminLoginSessionRecord = \common\models\AdminLoginSession::findOne([
                'als_admin_id' => $adminId, 'als_device_id' => $deviceId
            ]);
            if (!($adminLoginSessionRecord instanceof \common\models\AdminLoginSession)) {
                $adminLoginSessionRecord = new \common\models\AdminLoginSession();
                $adminLoginSessionRecord->als_admin_id = $adminId;
                $adminLoginSessionRecord->als_device_id = $deviceId;
            }
            $adminLoginSessionRecord->als_date_login = date('Y-m-d H:i:s');
            $adminLoginSessionRecord->als_date_activity = date('Y-m-d H:i:s');
            $adminLoginSessionRecord->save();
            $return = true;
        } catch (\Exception $exc) {}
        unset($adminLoginSessionRecord);
        unset($deviceId);
        unset($adminId);
        return $return;
    }

    public static function checkAdminSession($adminId = 0, $deviceId = '')
    {
        $return = true;
        $adminId = (int)$adminId;
        $deviceId = trim($deviceId);
        $adminLoginSessionRecord = \common\models\AdminLoginSession::findOne([
            'als_admin_id' => $adminId, 'als_device_id' => $deviceId
        ]);
        if (!($adminLoginSessionRecord instanceof \common\models\AdminLoginSession)) {
            $return = false;
        } else {
            $adminDeviceRecord = \common\models\AdminDevice::findOne(['ad_device_id' => $deviceId, 'ad_admin_id' => $adminId]);
            if ($adminDeviceRecord instanceof \common\models\AdminDevice) {
                if ((int)$adminDeviceRecord->ad_is_blocked > 0) {
                    $adminLoginSessionRecord->delete();
                    $return = false;
                }
            }
            unset($adminDeviceRecord);
            if ($return != false) {
                if ($adminLoginSessionRecord->als_date_activity != date('Y-m-d H:i:s')) {
                    try {
                        $adminLoginSessionRecord->als_date_activity = date('Y-m-d H:i:s');
                        $adminLoginSessionRecord->save();
                    } catch (\Exception $exc) {}
                }
            }
        }
        unset($adminLoginSessionRecord);
        unset($deviceId);
        unset($adminId);
        return $return;
    }
}
