<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;

/**
 * Password forgotten controller to handle user requests.
 */
class PasswordForgottenNewPasswordController extends Controller {

    /**
     * Disable layout for the controller view
     */
    public $layout = false;

    public function actionIndex() {
        if (\Yii::$app->request->isAjax) {
            if (\Yii::$app->request->get('action', null) == 'gp') {
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \common\helpers\Password::randomize(false);
            }
            die();
        }

        $token = \Yii::$app->request->get('token', null);
        $adminInfo = null;
        \common\models\Admin::updateAll(
            ['token' => '', 'token_date' => '0000-00-00 00:00:00'],
            ['<=', 'token_date', date('Y-m-d H:i:s', strtotime(
                    '-' . (int)trim(defined('FORGOTTEN_PASSWORD_TOKEN_EXPIRE_MIN') ? constant('FORGOTTEN_PASSWORD_TOKEN_EXPIRE_MIN') : 5) . ' minutes'
                ))
            ]
        );
        foreach (\common\models\Admin::find()
            ->where(['!=', 'token', ''])
            //->andWhere(['<', 'login_failture', 4])
            ->asArray(false)->each(10) as $aRecord
        ) {
            if (\common\helpers\Password::validate_password($token, $aRecord->getToken(), 'backend')
                AND \common\helpers\Password::validate_password($aRecord->admin_email_address, $aRecord->admin_email_token, 'backend')
            ) {
                $adminInfo = $aRecord;
                break;
            }
        }
        unset($aRecord);
        if (!is_object($adminInfo)) {
            tep_admin_check_login();
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }

        \common\helpers\Translation::init('account/password');
        \common\helpers\Translation::init('admin/admin_account');
        \common\helpers\Translation::init('main');

        $message_account_password = '';
        if (empty($token)) {
            $message_account_password = TEXT_INVALID_TOKEN;
        } else if (\Yii::$app->request->isPost) {
            $save = true;
            $postToken = \Yii::$app->request->post('token', null);
            if ($token != $postToken) {
                $message_account_password = TEXT_INVALID_TOKEN;
                $save = false;
            }
            if (!is_object($adminInfo)) {
                $message_account_password = TEXT_INVALID_TOKEN;
                $save = false;
            }
            if ($save) {
                $admin_password = \Yii::$app->request->post('password_new', null);
                $admin_password_confirm = \Yii::$app->request->post('password_confirmation', null);
                if ($admin_password != $admin_password_confirm) {
                    $message_account_password = TEXT_MESS_PASSWORD_WRONG;
                    $save = false;
                }
            }
            if ($save) {
                if (defined('ADMIN_PASSWORD_BAN_EASY') && ADMIN_PASSWORD_BAN_EASY == 'True') {
                    $dontAcceptList = [
                        $adminInfo->admin_username,
                        $adminInfo->admin_firstname,
                        $adminInfo->admin_lastname,
                        $adminInfo->admin_phone_number,
                    ];
                    foreach ($dontAcceptList as $dontAcceptItem) {
                        if (!empty($dontAcceptItem)) {
                            preg_match('/^'.preg_quote($dontAcceptItem).'/i', $admin_password, $matches);
                            if (count($matches) > 0) {
                                $message_account_password = TEXT_MESS_PASSWORD_START_AT . ' ' . $dontAcceptItem;
                                $save = false;
                            }
                            preg_match('/'.preg_quote($dontAcceptItem).'$/i', $admin_password, $matches);
                            if (count($matches) > 0) {
                                $message_account_password = TEXT_MESS_PASSWORD_END_AT . ' ' . $dontAcceptItem;
                                $save = false;
                            }
                        }
                    }
                }
            }
            if ($save) {
                if (defined('ADMIN_PASSWORD_BAN_EASY') && ADMIN_PASSWORD_BAN_EASY == 'True') {
                    $easyPassCheck = \common\models\EasyPasswords::find()
                            ->where(['password' => $admin_password])
                            ->one();
                    if ($easyPassCheck instanceof \common\models\EasyPasswords) {
                        $message_account_password = TEXT_MESS_PASSWORD_EASY;
                        $save = false;
                    }
                    unset($easyPassCheck);
                }
            }
            if ($save) {
                if (defined('ADMIN_PASSWORD_USE_SAME') && ADMIN_PASSWORD_USE_SAME == 'True') {
                    if (\common\models\AdminOldPasswords::isOld($adminInfo->admin_id, tep_db_prepare_input($admin_password)) == true) {
                        $message_account_password = TEXT_MESS_PASSWORD_OLD;
                        $message_account_password .= ', Please <a href="' . Yii::$app->urlManager->createUrl(['password-forgotten-new-password/', 'token' => $token]) . '">Try again</a>';
                        $save = false;
                    }
                }
            }
            if ($save) {
                if (defined('ADMIN_PASSWORD_USE_SAME') && ADMIN_PASSWORD_USE_SAME == 'True') {
                    \common\models\AdminOldPasswords::addOld($adminInfo->admin_id, tep_db_prepare_input($admin_password));
                }
                $adminInfo->admin_password = \common\helpers\Password::encrypt_password(tep_db_prepare_input($admin_password), 'backend');
                $adminInfo->password_last_update = date('Y-m-d H:i:s');
                $adminInfo->clearToken();
                $message_account_password = TEXT_PASSWORD_CHANGED . ', Please <a href="'.Yii::$app->urlManager->createUrl('login/').'">Login</a>';
            }
        }

        return $this->render('index', [
           'account_password_action' => ['password-forgotten-new-password/', 'token' => $token],
           'token' => $token,
           'message_account_password' => $message_account_password,
        ]);
    }


}
