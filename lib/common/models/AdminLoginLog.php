<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_login_log".
 *
 * @property int $all_id
 * @property int $all_event
 * @property string $all_device_id
 * @property string $all_ip
 * @property string $all_agent
 * @property int $all_user_id
 * @property string $all_user
 * @property string $all_date
 */
class AdminLoginLog extends \yii\db\ActiveRecord
{
    public static $eventList = array(
        1 => 'error_user',
        2 => 'error_password',
        3 => 'error_autorize',
        4 => 'error_email_token',
        10 => 'login',
        20 => 'logout',
        21 => 'logout_multi'
    );

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_login_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['all_event', 'all_device_id'], 'required'],
            [['all_event', 'all_user_id'], 'integer'],
            [['all_agent'], 'string'],
            [['all_date'], 'safe'],
            [['all_device_id'], 'string', 'max' => 32],
            [['all_ip'], 'string', 'max' => 45],
            [['all_user'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'all_id' => 'All ID',
            'all_event' => 'All Event',
            'all_device_id' => 'All Device ID',
            'all_ip' => 'All Ip',
            'all_agent' => 'All Agent',
            'all_user_id' => 'All User ID',
            'all_user' => 'All User',
            'all_date' => 'All Date',
        ];
    }

    public static function getAdminEmail($adminId = 0)
    {
        $return = '';
        $adminId = \common\models\Admin::findOne((int)$adminId);
        if ($adminId instanceof \common\models\Admin) {
            $return = $adminId->admin_email_address;
        }
        unset($adminId);
        return $return;
    }
}
