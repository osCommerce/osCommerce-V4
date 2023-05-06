<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;

/**
 * Password forgotten controller to handle user requests.
 */
class Password_forgottenController extends Controller {

    /**
     * Disable layout for the controller view
     */
    public $layout = false;
    public $errorMessage = '';
    public $enableCsrfValidation = false;

    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        $_GET['login'] = '';
        if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
            $loginModel = new \backend\forms\Login(['captha_enabled' => true]);
            if ($loginModel->load(Yii::$app->request->post()) && $loginModel->validate()) {
                if ($loginModel->hasErrors()) {
                    $errorMessage = '';
                    foreach($loginModel->getErrors() as $error) {
                        if (is_array($error)) {
                            $errorMessage .= implode(", ", $error);
                        } else if (is_string($error)) {
                            $errorMessage .= $error;
                        }
                    }
                    $_GET['login'] = 'captcha';
                }
            } else {
                if ($loginModel->hasErrors()) {
                    $errorMessage = '';
                    foreach($loginModel->getErrors() as $error) {
                        if (is_array($error)) {
                            $errorMessage .= implode(", ", $error);
                        } else if (is_string($error)) {
                            $errorMessage .= $error;
                        }
                    }
                }
                $_GET['login'] = 'captcha';
            }
            if ($_GET['login'] == '') {
                if (\common\models\AdminPasswordForgotLog::isBlocked() == true) {
                    $_GET['login'] = 'ban';
                }
            }
            if ($_GET['login'] == '') {
                \common\models\AdminPasswordForgotLog::register();

                $email_address = Yii::$app->request->post('email_address', '');
                $log_times = \Yii::$app->request->post('log_times') + 1;
                if ($log_times >= 4) {
                    tep_session_register('password_forgotten');
                }
                // Check if email exists
                $check_admin_query = tep_db_query("select admin_id as check_id, admin_firstname as check_firstname, admin_lastname as check_lastname, admin_phone_number as check_phone_number, admin_email_address as check_email_address, admin_email_token as check_email_token, admin_username from " . TABLE_ADMIN . " where admin_email_address = '" . tep_db_input($email_address) . "'");
                if (!tep_db_num_rows($check_admin_query)) {
                    $_GET['login'] = 'fail';
                } else {
                    $check_admin = tep_db_fetch_array($check_admin_query);

                    $passwordResetFileds = ['firstname'];
                    if (defined('RESET_PASSWORD_FIELDS')) {
                        $passwordResetFileds = explode(", ", RESET_PASSWORD_FIELDS);
                    }

                    $loginFail = false;
                    foreach ($passwordResetFileds as $passwordResetFiled) {
                        switch ($passwordResetFiled) {
                            case 'firstname':
                                $firstname = Yii::$app->request->post('firstname', '');
                                if ($check_admin['check_firstname'] != $firstname) {
                                    $loginFail = true;
                                }
                                break;
                            case 'lastname':
                                $lastname = Yii::$app->request->post('lastname', '');
                                if ($check_admin['check_lastname'] != $lastname) {
                                    $loginFail = true;
                                }
                                break;
                            case 'phone':
                                $phone = Yii::$app->request->post('phone', '');
                                if ($check_admin['check_phone_number'] != $phone) {
                                    $loginFail = true;
                                }
                                break;
                            case 'username':
                                $username = Yii::$app->request->post('username', '');
                                if ($check_admin['admin_username'] != $username) {
                                    $loginFail = true;
                                }
                                break;
                            default:
                                break;
                        }
                    }

                    if (!\common\helpers\Password::validate_password($check_admin['check_email_address'], $check_admin['check_email_token'], 'backend')) {
                        $loginFail = true;
                    }
                    if ($loginFail) {
                        $_GET['login'] = 'fail';
                    } else {
                        $_GET['login'] = 'success';
                        //{{
                        //\common\models\AdminPasswordForgotLog::clear();

                        $currentPlatformId = \Yii::$app->get('platform')->config()->getId();
                        $platform_config = \Yii::$app->get('platform')->config($currentPlatformId);

                        $STORE_NAME = $platform_config->const_value('STORE_NAME');
                        $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                        $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

                        $email_params = array();

                        if (defined('ADMIN_PASSWORD_FORGOTTEN_MODE') && ADMIN_PASSWORD_FORGOTTEN_MODE == 'invite') {
                            $adminInfo = \common\models\Admin::findOne($check_admin['check_id']);
                            $email_params['NEW_PASSWORD_SENTENCE'] = '';
                            if ($adminInfo) {
                                $token = $adminInfo->updateToken();
                                \common\helpers\Translation::init('account/password-forgotten');
                                $email_params['NEW_PASSWORD'] = \yii\helpers\Html::a(TEXT_PASSWORD_INVITATION_LINK, tep_href_link('password-forgotten-new-password/', 'token='.$token, 'SSL'));
                                unset($token);
                            } else {
                                $email_params['NEW_PASSWORD'] = '';
                            }
                        } else {
                            $makePassword = \common\helpers\Password::randomize();
                            $email_params['NEW_PASSWORD'] = $makePassword;
                            tep_db_query("update " . TABLE_ADMIN . " set admin_password = '" . tep_db_input(\common\helpers\Password::encrypt_password($makePassword, 'backend')) . "', reset_ip='" . tep_db_input(\common\helpers\System::get_ip_address()) . "', reset_date = now(), password_last_update = now() where admin_id = '" . $check_admin['check_id'] . "'");
                        }

                        $email_params['STORE_NAME'] = $STORE_NAME;
                        $email_params['CUSTOMER_FIRSTNAME'] = $check_admin['check_firstname'];
                        $email_params['HTTP_HOST'] = \common\helpers\Output::get_clickable_link(tep_href_link(FILENAME_LOGIN));
                        $email_params['CUSTOMER_EMAIL'] = $check_admin['check_email_address'];
                        $email_params['STORE_OWNER_EMAIL_ADDRESS'] = $STORE_OWNER_EMAIL_ADDRESS;
                        list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Admin Password Forgotten', $email_params);
                        //}}
                        \common\helpers\Mail::send($check_admin['check_firstname'] . ' ' . ($check_admin['admin_lastname']??null), $check_admin['check_email_address'], $email_subject, $email_text, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS, $email_params);
                    }
                }
            }
            echo $_GET['login'];
        }
    }
}