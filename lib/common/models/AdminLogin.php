<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "admin_login".
 *
 * @property int $al_admin_id
 * @property string $al_computer_id
 * @property string $al_security_key
 * @property string $al_expire
 * @property string $al_create
 */
class AdminLogin extends \yii\db\ActiveRecord
{
    const SECURITY_MASK_DEFAULT = 'NN-NN-NN-NN';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_login';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['al_admin_id', 'al_computer_id', 'al_security_key'], 'required'],
            [['al_admin_id'], 'integer'],
            [['al_expire', 'al_create'], 'safe'],
            [['al_computer_id'], 'string', 'max' => 32],
            [['al_security_key'], 'string', 'max' => 255],
            [['al_admin_id', 'al_computer_id'], 'unique', 'targetAttribute' => ['al_admin_id', 'al_computer_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'al_admin_id' => 'Al Admin ID',
            'al_computer_id' => 'Al Computer ID',
            'al_security_key' => 'Al Security Key',
            'al_expire' => 'Al Expire',
            'al_create' => 'Al Create',
        ];
    }

    public static function getByIdComputer($alAdminId = 0, $alComputerId = '')
    {
        $return = self::findOne(['al_admin_id' => (int)$alAdminId, 'al_computer_id' => trim($alComputerId)]);
        return (is_object($return) ? $return : false);
    }

    public static function securityKeyGenerate($mask = '')
    {
        if ($mask == '') {
            $mask = trim(defined('ADMIN_TWO_STEP_AUTH_MASK') ? constant('ADMIN_TWO_STEP_AUTH_MASK') : '');
        }
        if ($mask == '') {
            $mask = self::SECURITY_MASK_DEFAULT;
        }
        $randomSymbolCount = 0;
        $letterArray = range('A', 'Z');
        $symbolArray = array('N', 'a', 'A');
        mt_srand();
        $mask = str_split($mask, 1);
        foreach ($mask as &$symbol) {
            $symbol = (($symbol == '?') ? $symbolArray[mt_rand(0, (count($symbolArray) - 1))] : $symbol);
            switch ($symbol) {
                case 'n':
                case 'N':
                    $randomSymbolCount ++;
                    $symbol = mt_rand(0, 9);
                break;
                case 'a':
                    $randomSymbolCount ++;
                    $symbol = strtolower($letterArray[mt_rand(0, (count($letterArray) - 1))]);
                break;
                case 'A':
                    $randomSymbolCount ++;
                    $symbol = strtoupper($letterArray[mt_rand(0, (count($letterArray) - 1))]);
                break;
            }
        }
        unset($letterArray);
        unset($symbolArray);
        unset($symbol);
        if ($randomSymbolCount < 2) {
            return self::securityKeyGenerate(self::SECURITY_MASK_DEFAULT);
        }
        unset($randomSymbolCount);
        return implode('', $mask);
    }

    public static function getIpGeoInformation($ip = '')
    {
        $geoInformation = '';
        try {
            $detailClass = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
            $geoInformation = ' (' . implode(', ', [$detailClass->city ?? null, $detailClass->region ?? null, $detailClass->country ?? null]) . ')';
        } catch (\Exception $exc) {
            \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'ErrorAdminLoginGetIpGeoInformation');
        }
        unset($detailClass);
        return $geoInformation;
    }

    public static function securityKeyEmail($adminRecord = array(), $alSecurityKey = '')
    {
        $ip = \common\helpers\System::get_ip_address();
        $alSecurityKey = trim($alSecurityKey);
        if (defined('ADMIN_TWO_STEP_AUTH_MASK_SIMPLE_EMAIL') AND (strtolower(constant('ADMIN_TWO_STEP_AUTH_MASK_SIMPLE_EMAIL'))) == 'true') {
            $alSecurityKey = trim(preg_replace('/[^a-z0-9]/i', '', $alSecurityKey));
        }
        $parameterArray = array(
            'SECURITY_KEY' => $alSecurityKey,
            'DEVICE_AGENT' => $_SERVER['HTTP_USER_AGENT'],
            'DEVICE_IP' => ($ip . self::getIpGeoInformation($ip)),
            'LOGIN_URL' => Yii::$app->urlManager->createAbsoluteUrl(['login'])
        );
        $adminEmail = trim($adminRecord['admin_email_address']);
        $adminName = trim(trim($adminRecord['admin_firstname']) . ' ' . trim($adminRecord['admin_lastname']));
        list($emailSubject, $emailMessage) = \common\helpers\Mail::get_parsed_email_template('Admin Login Security Key', $parameterArray);
        \common\helpers\Mail::send($adminName, $adminEmail, $emailSubject, $emailMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $parameterArray);
        return true;
    }

    public static function securityKeySms($adminRecord = array(), $alSecurityKey = '')
    {
        if (defined('ADMIN_TWO_STEP_AUTH_SERVICE_SMS') AND (ADMIN_TWO_STEP_AUTH_SERVICE_SMS != '')) {
            if ($smsService = \common\helpers\Acl::checkExtensionAllowed('SmsService', 'allowed')) {
                $parameterArray = array(
                    'SECURITY_KEY' => $alSecurityKey
                );
                $smsMessage = \common\helpers\Mail::get_sms_template_parsed('Admin Login Security Key', $parameterArray);
                $parameterArray = array(
                    'phone' => $adminRecord['admin_phone_number'],
                    'message' => $smsMessage,
                    'sender' => null
                );
                return $smsService::sendSms(ADMIN_TWO_STEP_AUTH_SERVICE_SMS, $parameterArray);
            }
        }
        return false;
    }

    public static function getSecurityKeyExpireArray($languageId = 0)
    {
        $languageId = (int)((int)$languageId <= 0 ? \Yii::$app->settings->get('languages_id') : $languageId);
        $languageId = ($languageId <= 0 ? 1 : $languageId);
        $return = array();
        foreach (\common\models\AdminLoginExpire::find()->where(['ale_language_id' => $languageId])->orderBy(['ale_order' => SORT_ASC])->asArray(true)->all()
            as $loginExpireArray
        ) {
            $return[$loginExpireArray['ale_id']] = $loginExpireArray;
        }
        return $return;
    }

    public static function checkAdminDevice($adminId = 0, $deviceId = '', $isGuest = false)
    {
        $adminId = (int)$adminId;
        $deviceId = trim($deviceId);
        $isGuest = ((int)$isGuest > 0 ? true : false);
        $adminDeviceRecord = \common\models\AdminDevice::findOne(['ad_device_id' => $deviceId, 'ad_admin_id' => $adminId]);
        if (!($adminDeviceRecord instanceof \common\models\AdminDevice)) {
            $adminDeviceRecord = new \common\models\AdminDevice();
            $adminDeviceRecord->ad_device_id = $deviceId;
            $adminDeviceRecord->ad_admin_id = $adminId;
            $adminDeviceRecord->ad_date_add = date('Y-m-d H:i:s');
            $adminRecord = \common\models\Admin::findOne($adminId);
            if (!($adminRecord instanceof \common\models\Admin) OR (int)$adminRecord->login_failture > 2) {
                return false;
            }
            $adminEmail = trim($adminRecord->admin_email_address);
            $adminName = trim(trim($adminRecord->admin_firstname) . ' ' . trim($adminRecord->admin_lastname));


            $ip = \common\helpers\System::get_ip_address();
            $alslHash = '';
            try {
                $alslRecord = new \common\models\AdminLoginSessionLogoff();
                $dateExpire = date('Y-m-d H:i:s', strtotime('+ 15 minutes'));
                $alslRecord->alsl_hash = md5($adminId . $deviceId . $dateExpire);
                $alslRecord->alsl_admin_id = $adminId;
                $alslRecord->alsl_device_id = $deviceId;
                $alslRecord->alsl_date_expire = $dateExpire;
                unset($dateExpire);
                if ($alslRecord->save(false)) {
                    $alslHash = $alslRecord->alsl_hash;
                }
                unset($alslRecord);
            } catch (\Exception $exc) {
                \Yii::warning($exc->getMessage() . ' ' . $exc->getTraceAsString(), 'Error.AdminLogin.CheckAdminDevice.AdminLoginSessionLogoff.Create');
            }
            unset($alslRecord);

            $parameterArray = array(
                'AD_DEVICE_DATE' => date('Y-m-d H:i:s'),
                'AD_DEVICE_ID' => $_SERVER['HTTP_USER_AGENT'] . ' (' . $deviceId . ')',
                'AD_DEVICE_IP' => $ip . self::getIpGeoInformation($ip),
                'AD_DEVICE_LOGOFF_URL' => Yii::$app->urlManager->createAbsoluteUrl(['login'])
            );

            if ($alslHash != '') {
                $parameterArray['AD_DEVICE_LOGOFF_URL'] = Yii::$app->urlManager->createAbsoluteUrl(['logout', 'hash' => $alslHash]);
            }
            unset($alslHash);

            list($emailSubject, $emailMessage) = \common\helpers\Mail::get_parsed_email_template('Admin New Device Login', $parameterArray);
            \common\helpers\Mail::send($adminName, $adminEmail, $emailSubject, $emailMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $parameterArray);
        } elseif ((int)$adminDeviceRecord->ad_is_blocked > 0) {
            return false;
        }
        $adminDeviceRecord->ad_date_login = date('Y-m-d H:i:s');
        $adminDeviceRecord->ad_login_count += 1;
        if (($isGuest != true) OR ($adminDeviceRecord->isNewRecord != true)) {
            $adminDeviceRecord->save(false);
        }
        return true;
    }
}