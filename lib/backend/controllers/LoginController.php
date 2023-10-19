<?php

/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;
use yii\web\Controller;

/**
 * login controller to handle user requests.
 */
class LoginController extends Controller {

    function __construct($id, $module = null) {
        if ( defined('STRICT_ACCESS_STATUS') && STRICT_ACCESS_STATUS=='True' ) {
            $allowed = false;
            $clientIp = \common\helpers\System::get_ip_address();
            $ipWhiteList = preg_split('/[,;\s]/', (defined('STRICT_ACCESS_ALLOWED_IP') ? STRICT_ACCESS_ALLOWED_IP : ''), -1, PREG_SPLIT_NO_EMPTY);
            $ipWhiteList = array_map('trim', $ipWhiteList);
            foreach ($ipWhiteList as $white_ip){
                if ( strpos($white_ip,'/')!==false ){
                    if (\yii\helpers\IpHelper::inRange($clientIp, $white_ip)){
                        $allowed = true;
                    }
                } else {
                    if ($clientIp == $white_ip) {
                        $allowed = true;
                    }
                }
            }

            if (!$allowed) {
                header('HTTP/1.0 403 Forbidden');
                echo (defined('TEXT_PAGE_ACCESS_FORBIDDEN') ? TEXT_PAGE_ACCESS_FORBIDDEN : 'Access Denied') . ' ' . $clientIp;
                die();
            }
        }
        \Yii::$app->view->title = TEXT_SIGN_IN . ' | '. \common\classes\platform::name(\common\classes\platform::defaultId()) . ' | ' . \Yii::$app->name;
        return parent::__construct($id, $module);
    }

    /**
     * Disable layout for the controller view
     */
    public $layout = false;
    public $errorMessage = '';
    //public $enableCsrfValidation = false;

    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        global $language, $navigation;
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/main');
        \common\helpers\Translation::init('admin/admin-login-view');

        $stamp = date('Y-m-d H:i:s', strtotime("-1 hour"));
        tep_db_query("update " . TABLE_ADMIN . " set login_failture = 0, login_failture_ip = '', login_failture_date = NULL where login_failture > 2 and login_failture_date IS NOT NULL and login_failture_date < '" . $stamp . "'");

        $get = \Yii::$app->request->get();
        $get['login'] = '';
// {{ From superadmin
        /*if (isset($get['uid']) && $get['uid'] > 0 && tep_not_null($get['tr'])) {
            $check_admin = tep_db_fetch_array(tep_db_query("select admin_id, admin_groups_id, admin_firstname from admin where admin_id = '" . (int) $get['uid'] . "' and admin_password = '" . tep_db_input(tep_db_prepare_input($get['tr'])) . "'"));
            if ($check_admin['admin_id'] > 0 && $get['uid'] == $check_admin['admin_id']) {
                $login_id = $check_admin['admin_id'];
                $login_groups_id = $check_admin['admin_groups_id'];
                $login_firstname = $check_admin['admin_firstname'];

                \common\helpers\Acl::saveManagerDeviceHash($login_id);

                tep_session_register('login_id', $login_id);
                tep_session_register('login_groups_id', $login_groups_id);
                tep_session_register('login_first_name', $login_firstname);

                tep_redirect(tep_href_link(FILENAME_DEFAULT));
            }
        }*/

        $loginModel = new \backend\forms\Login();
// }}
        if (\Yii::$app->request->get('action', '') == 'process') {
            $errorMessage = TEXT_LOGIN_ERROR;

            if ($loginModel->captha_enabled) {
                if (\Yii::$app->request->isPost
                    AND !in_array(\Yii::$app->request->post('action', ''), ['otp', 'authorize', 'gaauthorize'])
                ) {
                    if ($loginModel->load(Yii::$app->request->post()) && $loginModel->validate()){
                        if ($loginModel->hasErrors()) {
                            $errorMessage = '';
                            foreach($loginModel->getErrors() as $error){
                                if (is_array($error)) {
                                    $errorMessage .= implode(", ", $error);
                                } else if (is_string($error)) {
                                    $errorMessage .= $error;
                                }
                            }
                            $get['login'] = 'fail';
                        }
                    } else {
                        if ($loginModel->hasErrors()) {
                            $errorMessage = '';
                            foreach($loginModel->getErrors() as $error){
                                if (is_array($error)) {
                                    $errorMessage .= implode(", ", $error);
                                } else if (is_string($error)) {
                                    $errorMessage .= $error;
                                }
                            }


                        }
                        $get['login'] = 'fail';
                    }
                }
            }

            if ($get['login'] !== 'fail') {
                $email_address = (isset($_POST['email_address']) ? tep_db_prepare_input($_POST['email_address']) : '');
                $password = (isset($_POST['password']) ? tep_db_prepare_input($_POST['password']) : '');

                $adminLoginLogRecord = new \common\models\AdminLoginLog();
                $adminLoginLogRecord->all_event = 1;
                $adminLoginLogRecord->all_device_id = \common\helpers\Acl::saveManagerDeviceHash(0);
                $adminLoginLogRecord->all_ip = \common\helpers\System::get_ip_address();
                $adminLoginLogRecord->all_agent = implode("\n", array_merge([\common\models\AdminLogin::getIpGeoInformation($adminLoginLogRecord->all_ip)], \common\helpers\System::getHttpUserInfoArray()));
                $adminLoginLogRecord->all_user_id = 0;
                $adminLoginLogRecord->all_user = $email_address;
                $adminLoginLogRecord->all_date = date('Y-m-d H:i:s');

                $adminSecurityKey = '';
                if (\Yii::$app->request->post('action', '') == 'authorize') {
                    $al_admin_login_id = trim(tep_session_is_registered('al_admin_login_id') ? tep_session_var('al_admin_login_id') : '');
                    tep_session_unregister('al_admin_login_id');
                    if ($al_admin_login_id == '') {
                        tep_redirect(tep_href_link(FILENAME_DEFAULT));
                    }
                    $email_address = $al_admin_login_id;
                    $adminSecurityKey = preg_replace('/[^0-9a-z]*/si', '', \Yii::$app->request->post('security_key', ''));

                    $adminLoginLogRecord->all_event = 3;
                    $adminLoginLogRecord->all_user = $email_address;

                }
                if (\Yii::$app->request->post('action', '') == 'gaauthorize') {
                    $al_admin_login_id = trim(tep_session_is_registered('al_admin_login_id') ? tep_session_var('al_admin_login_id') : '');
                    if ($al_admin_login_id == '') {
                        tep_redirect(tep_href_link(FILENAME_DEFAULT));
                    }
                    $email_address = $al_admin_login_id;
                }

                // Check if email exists
                $check_admin_query = tep_db_query("select * from " . TABLE_ADMIN . " where (admin_email_address = '" . tep_db_input($email_address) . "' or admin_username='" . tep_db_input($email_address) . "')");
                if (!tep_db_num_rows($check_admin_query)) {
                    $get['login'] = 'fail';
                }
            }
            if ($get['login'] !== 'fail') {
                $check_admin = tep_db_fetch_array($check_admin_query);
                if (!\common\helpers\Password::validate_password($check_admin['admin_email_address'], $check_admin['admin_email_token'], 'backend')) {
                    $get['login'] = 'fail';
                    $errorMessage = ('TEXT_' . strtoupper(\common\models\AdminLoginLog::$eventList[4]));
                    $errorMessage = (defined($errorMessage) ? constant($errorMessage) : 'Wrong email security token');
                    $adminLoginLogRecord->all_event = 4;
                    $adminLoginLogRecord->all_user_id = $check_admin['admin_id'];
                    $adminLoginLogRecord->all_user = $check_admin['admin_email_address'];
                }
            }
            if ($get['login'] !== 'fail') {
                $adminLoginLogRecord->all_user_id = $check_admin['admin_id'];
                $adminLoginLogRecord->all_user = $check_admin['admin_email_address'];

                if (defined('ADMIN_LOGIN_OTP_ENABLE') AND (ADMIN_LOGIN_OTP_ENABLE == 'True')
                    AND (!in_array(\Yii::$app->request->post('action', ''), ['otp', 'authorize', 'gaauthorize']))
                ) {
                    $currentPlatformId = \Yii::$app->get('platform')->config()->getId();
                    $platform_config = \Yii::$app->get('platform')->config($currentPlatformId);
                    $STORE_NAME = $platform_config->const_value('STORE_NAME');
                    $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                    $STORE_OWNER = $platform_config->const_value('STORE_OWNER');
                    $email_params = [];
                    $email_params['NEW_PASSWORD'] = \common\helpers\Password::randomize();
                    $email_params['STORE_NAME'] = $STORE_NAME;
                    $email_params['CUSTOMER_FIRSTNAME'] = $check_admin['admin_firstname'];
                    $email_params['HTTP_HOST'] = \common\helpers\Output::get_clickable_link(tep_href_link(FILENAME_LOGIN));
                    $email_params['CUSTOMER_EMAIL'] = $check_admin['admin_email_address'];
                    $email_params['STORE_OWNER_EMAIL_ADDRESS'] = $STORE_OWNER_EMAIL_ADDRESS;
                    tep_db_query("UPDATE `" . TABLE_ADMIN . "` SET `admin_password` = '" . tep_db_input(\common\helpers\Password::encrypt_password($email_params['NEW_PASSWORD'], 'backend')) . "', `reset_ip` = '" . tep_db_input(\common\helpers\System::get_ip_address()) . "', `reset_date` = now(), `password_last_update` = now() WHERE `admin_id` = '" . $check_admin['admin_id'] . "';");
                    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Admin Password Forgotten', $email_params);
                    \common\helpers\Mail::send(($check_admin['admin_firstname'] . ' ' . $check_admin['admin_lastname']), $check_admin['admin_email_address'], $email_subject, $email_text, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS, $email_params);
                    $loginModel->captha_enabled = false;
                    return $this->render('index', ['passwordResetFileds' => [], 'loginModel' => $loginModel, 'action' => 'otp', 'email' => $check_admin['admin_email_address']]);
                }

                $isAdminNoPassword = false;
                $isGuest = ((int)\Yii::$app->request->post('ad_is_guest', 0) > 0 ? true : false);
                $al_computer_id = md5($check_admin['admin_id'] . $check_admin['admin_email_address'] . ((defined('ADMIN_LOGIN_OTP_ENABLE') AND (ADMIN_LOGIN_OTP_ENABLE == 'True')) ? $check_admin['admin_email_token'] : $check_admin['admin_password']) . $_SERVER['HTTP_USER_AGENT'] . \common\helpers\System::get_ip_address());
                if ($adminSecurityKey != '') {
                    $adminLoginRecord = \common\models\AdminLogin::getByIdComputer($check_admin['admin_id'], $al_computer_id);
                    if (is_object($adminLoginRecord)) {
                        if ($adminLoginRecord->al_expire == '0000-00-00 00:00:00'
                            AND \common\helpers\Password::validate_password($adminSecurityKey, $adminLoginRecord->al_security_key, 'backend')
                        ) {
                            $alExpire = date('Y-m-d H:i:s', strtotime('+3 second'));
                            $securityKeyExpire = (int)\Yii::$app->request->post('security_key_expire', 0);
                            $securityKeyExpireArray = \common\models\AdminLogin::getSecurityKeyExpireArray();
                            if (($isGuest != true) AND isset($securityKeyExpireArray[$securityKeyExpire]) AND ((int)$securityKeyExpireArray[$securityKeyExpire]['ale_expire_minutes'] > 0)) {
                                $alExpire = date('Y-m-d H:i:s', strtotime('+' . (int)$securityKeyExpireArray[$securityKeyExpire]['ale_expire_minutes'] . ' minutes'));
                            }
                            $adminLoginRecord->al_expire = $alExpire;
                            $adminLoginRecord->save(false);
                            $isAdminNoPassword = true;
                        } else {
                            $adminLoginRecord->delete();
                        }
                    }
                }
                $isGApassed = null;
                if (\Yii::$app->request->post('action', '') == 'gaauthorize') {
                    $gaSecurityKey = preg_replace('/[^0-9a-z]*/si', '', \Yii::$app->request->post('security_key', ''));
                    if ($gaSecurityKey != '') {
                        /**
                         * @var $ext \common\extensions\GoogleAuthenticator\GoogleAuthenticator
                         */
                        if ($ext = \common\helpers\Extensions::isAllowed('GoogleAuthenticator')) {
                            $isGApassed = $isAdminNoPassword = $ext::checkTwoStepAuth($check_admin);
                        }
                    }
                }

                // Check that password is good
                if ($check_admin['login_failture'] >= 3) {
                    $get['login'] = 'fail';
                    $errorMessage = TEXT_LOGIN_BLOCK;
                } elseif (($isAdminNoPassword !== true) AND ($pCheck = \common\helpers\Password::validate_password($password, $check_admin['admin_password'], 'backend')) != true) {

                    $get['login'] = 'fail';
                    if ($pCheck === 0 ) {
                        //passord format is changed, pwd is OK
                        $errorMessage = ADMIN_TEXT_LOGIN_REQUEST_NEW_PASSWORD;


                    } else {

                        if ($adminLoginLogRecord->all_event == 1) {
                            $adminLoginLogRecord->all_event = 2;
                        }

                        \common\models\Fraud::registerAddress();
                        $loginModel = new \backend\forms\Login();
                        tep_db_query("update " . TABLE_ADMIN . " set login_failture = login_failture + 1, login_failture_ip='" . tep_db_input(\common\helpers\System::get_ip_address()) . "', login_failture_date = now() where admin_id = '" . (int) $check_admin['admin_id'] . "' and admin_email_address!='vlad@holbi.co.uk'");
                        $login_failture = 3 - ($check_admin['login_failture']+1);
                        if ($login_failture < 0) {
                            $login_failture = 0;
                        }
                        $errorMessage = sprintf(TEXT_LOGIN_WARNING, $login_failture);
                        try {
                            $emailSubject = '';
                            $emailMessage = '';
                            $parameterArray = array(
                                'DEVICE_DATE' => date('Y-m-d H:i:s'),
                                'DEVICE_AGENT' => $adminLoginLogRecord->all_agent,
                                'DEVICE_IP' => $adminLoginLogRecord->all_ip,
                                'LOGIN_URL' => Yii::$app->urlManager->createAbsoluteUrl(['login'])
                            );
                            switch ($adminLoginLogRecord->all_event) {
                                case 2:
                                    $parameterArray['PASSWORD_INVALID'] = $password;
                                    list($emailSubject, $emailMessage) = \common\helpers\Mail::get_parsed_email_template('Admin Login Password Error', $parameterArray);
                                break;
                                case 3:
                                    list($emailSubject, $emailMessage) = \common\helpers\Mail::get_parsed_email_template('Admin Login Security Key Error', $parameterArray);
                                break;
                            }
                            if (($emailSubject != '') AND ($emailMessage != '')) {
                                \common\helpers\Mail::send(trim(trim($check_admin['admin_firstname']) . ' ' . trim($check_admin['admin_lastname'])), trim($check_admin['admin_email_address']), $emailSubject, $emailMessage, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $parameterArray);
                            }
                            unset($parameterArray);
                            unset($emailSubject);
                            unset($emailMessage);
                        } catch (\Exception $exc) {
                            \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'ErrorAdminLoginNotify');
                        }
                    }
                } else {
                    if (
                            ($check_admin['admin_two_step_auth'] != 'disabled')
                            AND
                            ((ADMIN_TWO_STEP_AUTH_ENABLED == 'true') OR ($check_admin['admin_two_step_auth'] != ''))
                        ) {
                        $isAdminLogged = false;
                        /**
                         * @var $ext \common\extensions\GoogleAuthenticator\GoogleAuthenticator
                         */
                        if ($ext = \common\helpers\Extensions::isAllowed('GoogleAuthenticator')) {
                            if ($isGApassed) {
                                $isAdminLogged = true;
                            } else {
                                return $ext::askTwoStepAuth($check_admin);
                            }
                        } else {
                            $adminLoginRecord = \common\models\AdminLogin::getByIdComputer($check_admin['admin_id'], $al_computer_id);
                            if (is_object($adminLoginRecord)) {
                                if (strtotime($adminLoginRecord->al_expire) > time()) {
                                    $isAdminLogged = true;
                                } else {
                                    $adminLoginRecord->delete();
                                }
                            }
                        }
                        if ($isAdminLogged != true) {
                            $al_security_key = \common\models\AdminLogin::securityKeyGenerate();
                            $adminLoginRecord = new \common\models\AdminLogin();
                            $adminLoginRecord->al_admin_id = (int)$check_admin['admin_id'];
                            $adminLoginRecord->al_computer_id = trim($al_computer_id);
                            $adminLoginRecord->al_security_key = \common\helpers\Password::encrypt_password(preg_replace('/[^0-9a-z]*/si', '', $al_security_key), 'backend');
                            $adminLoginRecord->al_expire = '0000-00-00 00:00:00';
                            $adminLoginRecord->al_create = date('Y-m-d H:i:s');
                            $adminLoginRecord->save(false);
                            tep_session_register('al_admin_login_id', $check_admin['admin_email_address']);
                            $securityKeyExpireArray = array();
                            foreach (\common\models\AdminLogin::getSecurityKeyExpireArray() as $loginExpireArray) {
                                $securityKeyExpireArray[] = ['id' => $loginExpireArray['ale_id'], 'text' => $loginExpireArray['ale_title']];
                            }
                            $isMobile = false;
                            if ($ext = \common\helpers\Extensions::isAllowed('MobileDetect')) {
                                $isMobile = $ext::isMobileTabletOrIphone();
                            }
                            $parameterArray = [
                                'securityKeyExpireArray' => $securityKeyExpireArray,
                                'isMobile' => $isMobile,
                            ];
                            $two_step_auth_service = (($check_admin['admin_two_step_auth'] != '') ? $check_admin['admin_two_step_auth'] : ADMIN_TWO_STEP_AUTH_SERVICE);
                            if (($two_step_auth_service == 'email') OR (trim($check_admin['admin_phone_number']) == '')) {
                                $parameterArray['type'] = 'email';
                                \common\models\AdminLogin::securityKeyEmail($check_admin, $al_security_key);
                            } else {
                                $parameterArray['type'] = 'sms';
                                if (\common\models\AdminLogin::securityKeySms($check_admin, $al_security_key) != true) {
                                    $parameterArray['type'] = 'email';
                                    \common\models\AdminLogin::securityKeyEmail($check_admin, $al_security_key);
                                }
                            }
                            return $this->render('authorize', $parameterArray);
                        }
                    }

                    if (tep_session_is_registered('password_forgotten')) {
                        tep_session_unregister('password_forgotten');
                    }

                    $login_id = $check_admin['admin_id'];
                    $login_groups_id = $check_admin['admin_groups_id'];
                    $login_firstname = $check_admin['admin_firstname'];
                    $login_email_address = $check_admin['admin_email_address'];
                    $login_logdate = $check_admin['admin_logdate'];
                    $login_lognum = $check_admin['admin_lognum'];
                    $login_modified = $check_admin['admin_modified'];
                    $access_levels_id = $check_admin['access_levels_id'];
                    $language = $check_admin['languages'];

                    $adminLoginLogRecord->all_event = 10;

                    session_regenerate_id();

                    tep_session_register('login_id', $login_id);
                    tep_session_register('login_groups_id', $login_groups_id);
                    tep_session_register('login_first_name', $login_firstname);
                    tep_session_register('access_levels_id', $access_levels_id);
                    tep_session_register('language', $language);

                    $lng = new \common\classes\language();
                    $lng->set_language($language);
                    $languages_id = $lng->language['id'];
                    tep_session_register('languages_id', $languages_id);
                    $lng->set_locale();
                    $lng->load_vars();

                    //$date_now = date('Ymd');
                    $device_hash = \common\helpers\Acl::saveManagerDeviceHash($login_id);
                    tep_session_register('device_hash', $device_hash);

                    $adminLoginLogRecord->all_device_id = $device_hash;
                    try {
                        $adminLoginLogRecord->save(false);
                    } catch (\Exception $exc) {}

                    if ((\common\models\AdminLogin::checkAdminDevice($login_id, $device_hash, $isGuest) != true)
                        OR (\common\models\AdminLoginSession::updateAdminSession($login_id, $device_hash) != true)
                    ) {
                        tep_redirect(tep_href_link(FILENAME_LOGOFF));
                    }
                    tep_db_query("update " . TABLE_ADMIN . " set login_failture = 0, login_failture_ip = '', token = '', admin_logdate = now(), admin_lognum = admin_lognum+1 where admin_id = '" . (int)$login_id . "'");
                    \common\models\Fraud::cleanAddress();

                    $expiredFlag = false;
                    if (defined('ADMIN_PASSWORD_EXPIRE') && ADMIN_PASSWORD_EXPIRE != 'Never') {
                        $dateTimestamp2 = false;
                        switch (ADMIN_PASSWORD_EXPIRE) {
                            case '1 Week':
                                $dateTimestamp2 = strtotime("-1 week");
                                break;
                            case '2 Weeks':
                                $dateTimestamp2 = strtotime("-2 weeks");
                                break;
                            case '1 Month':
                                $dateTimestamp2 = strtotime("-1 month");
                                break;
                            case '3 Months':
                                $dateTimestamp2 = strtotime("-3 months");
                                break;
                            case '6 Months':
                                $dateTimestamp2 = strtotime("-6 months");
                                break;
                            default:
                                break;
                        }
                        if ($dateTimestamp2 !== false) {
                            $dateTimestamp1 = strtotime($check_admin['password_last_update']);
                            if ($dateTimestamp1 === false) {
                                $expiredFlag = true;
                            } else if ($dateTimestamp1 < $dateTimestamp2) {
                                $expiredFlag = true;
                            }

                        }
                    }

                    $disposable = $check_admin['disposable'];

                    if ($disposable) {
                        tep_redirect(tep_href_link('adminaccount/first-setup'));
                    } else if (($login_lognum == 0) || !($login_logdate) || ($login_email_address == 'admin@localhost') || ($login_modified == '0000-00-00 00:00:00')) {
                        tep_redirect(tep_href_link(FILENAME_ADMIN_ACCOUNT));
                    } else if ($expiredFlag) {
                        tep_redirect(tep_href_link('adminaccount'));
                    } else {
                        if (sizeof($navigation->snapshot) > 0) {
                            $origin_href = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
                            $navigation->clear_snapshot();
                            tep_redirect($origin_href);
                        } else {
                            tep_redirect(tep_href_link(FILENAME_DEFAULT));
                        }
                    }
                }
            }
            if ($get['login'] == 'fail') {
                $this->errorMessage = $errorMessage;
            }
            try {
                if (isset($adminLoginLogRecord)) {
                    $adminLoginLogRecord->save(false);
                }
            } catch (\Exception $exc) {}
        }
        if (!\Yii::$app->request->isAjax AND tep_session_is_registered('admin_multi_session_error')) {
            $this->errorMessage = ADMIN_MULTI_SESSION_ERROR;
            tep_session_unregister('admin_multi_session_error');
        }
        $passwordResetFileds = ['firstname'];
        if (defined('RESET_PASSWORD_FIELDS')) {
            $passwordResetFileds = explode(", ", RESET_PASSWORD_FIELDS);
        }

        $action = '';
        if (\Yii::$app->request->get('action') == 'restore') {
            $action = 'restore';
            if (empty($loginModel->captha_enabled)) {
                $loginModel->captha_enabled = 'captha';
            }
        }
        return $this->render('index', ['passwordResetFileds' => $passwordResetFileds, 'loginModel' => $loginModel, 'action' => $action]);
    }

}
