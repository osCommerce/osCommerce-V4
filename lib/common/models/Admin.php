<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin".
 *
 * @property int $admin_id
 * @property int $admin_groups_id
 * @property string $admin_firstname
 * @property string $admin_lastname
 * @property string $admin_email_address
 * @property string $admin_phone_number
 * @property string $admin_password
 * @property string $admin_two_step_auth
 * @property string $admin_created
 * @property string $admin_modified
 * @property string $admin_logdate
 * @property int $admin_lognum
 * @property string $individual_id
 * @property string $avatar
 * @property int $login_failture
 * @property string $login_failture_ip
 * @property string $login_failture_date
 * @property int $access_levels_id
 * @property string $admin_persmissions
 * @property string $additional_info
 * @property string $reset_ip
 * @property string $reset_date
 * @property string $languages
 * @property string $admin_username
 * @property string $pin
 * @property int $customers_id
 * @property string $device_hash
 * @property string $chat_email_address
 * @property string $chat_password
 * @property int $pos_platform_id
 * @property int $pos_currency_id
 * @property int $frontend_translation
 * @property string $token
 */
class Admin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_groups_id', 'admin_lognum', 'login_failture', 'access_levels_id', 'customers_id', 'pos_platform_id', 'pos_currency_id', 'frontend_translation'], 'integer'],
            [['admin_firstname', 'admin_email_address', 'admin_password', 'individual_id', 'avatar', 'login_failture', 'access_levels_id', 'additional_info', 'admin_username', 'device_hash'], 'required'],
            [['admin_created', 'admin_modified', 'admin_logdate', 'login_failture_date', 'reset_date'], 'safe'],
            [['admin_persmissions', 'additional_info'], 'string'],
            [['admin_firstname', 'admin_lastname', 'admin_phone_number', 'individual_id', 'login_failture_ip', 'reset_ip', 'device_hash'], 'string', 'max' => 32],
            [['admin_email_address'/*, 'chat_email_address'*/], 'string', 'max' => 96],
            //[['chat_password'], 'string', 'max' => 40],
            [['admin_two_step_auth'], 'string', 'max' => 16],
            [['admin_password', 'avatar'], 'string', 'max' => 255],
            [['languages'], 'string', 'max' => 2],
            [['admin_username', 'token'], 'string', 'max' => 64],
            [['pin'], 'string', 'max' => 8],
            [['admin_email_address'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'admin_id' => 'Admin ID',
            'admin_groups_id' => 'Admin Groups ID',
            'admin_firstname' => 'Admin Firstname',
            'admin_lastname' => 'Admin Lastname',
            'admin_email_address' => 'Admin Email Address',
            'admin_phone_number' => 'Admin Phone Number',
            'admin_password' => 'Admin Password',
            'admin_two_step_auth' => 'Admin Two Step Auth',
            'admin_created' => 'Admin Created',
            'admin_modified' => 'Admin Modified',
            'admin_logdate' => 'Admin Logdate',
            'admin_lognum' => 'Admin Lognum',
            'individual_id' => 'Individual ID',
            'avatar' => 'Avatar',
            'login_failture' => 'Login Failture',
            'login_failture_ip' => 'Login Failture Ip',
            'login_failture_date' => 'Login Failture Date',
            'access_levels_id' => 'Access Levels ID',
            'admin_persmissions' => 'Admin Persmissions',
            'additional_info' => 'Additional Info',
            'reset_ip' => 'Reset Ip',
            'reset_date' => 'Reset Date',
            'languages' => 'Languages',
            'admin_username' => 'Admin Username',
            'pin' => 'Pin',
            'customers_id' => 'Customers ID',
            'device_hash' => 'Device Hash',
            'chat_email_address' => 'Chat Email Address',
            'chat_password' => 'Chat Password',
            'pos_platform_id' => 'Pos Platform ID',
            'pos_currency_id' => 'Pos Currency ID',
            'frontend_translation' => 'Frontend Translation',
        ];
    }

    public function updateToken() {
        $token = ('AT-' . strtoupper(md5(microtime(true))));
        $this->token = \common\helpers\Password::encrypt_password($token, 'backend');
        $this->token_date = date('Y-m-d H:i:s');
        $this->update(false);
        return $token;
    }

    public function clearToken() {
        $this->token = '';
        $this->update(false);
    }

    public function getToken() {
        return $this->token;
    }

    public function getAccesslevel()
    {
        return $this->hasOne(AccessLevels::className(), ['access_levels_id' => 'access_levels_id']);
    }
    public function getPosCurrency()
    {
        return $this->hasOne(Currencies::className(), ['currencies_id' => 'pos_currency_id']);
    }
    public function getPosPlatform()
    {
        return $this->hasOne(Platforms::className(), ['platform_id' => 'pos_platform_id']);
    }
    public static function isWalkinPosOrder($customerId,$admin)
    {
        return static::find()->where(['AND',['customers_id' => $customerId],['admin_id'=> $admin]]) ->exists();
    }

    public function getOrders(){
        return $this->hasMany(Orders::className(), ['admin_id' => 'admin_id']);
    }

    public function hasWalkinOrder()
    {
        return $this->getOrders()->exists();
    }
}