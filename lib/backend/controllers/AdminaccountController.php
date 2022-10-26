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
use \common\helpers\Translation;
use common\models\Currencies;
use common\models\Customers;
use common\models\Platforms;
use common\models\repositories\AdminRepository;
use yii\helpers\ArrayHelper;
use backend\models\Admin;

class AdminaccountController extends Sceleton
{
    private $adminRepository;

    public function __construct($id, $module, AdminRepository $adminRepository, array $config = []){
      Translation::init('admin/admin_account');
      $this->adminRepository = $adminRepository;
      parent::__construct($id, $module,$config);
    }

    public function actionIndex() {
        $this->selectedMenu = array('administrator', 'adminaccount');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminaccount/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $expiredFlag = false;
        if (defined('ADMIN_PASSWORD_EXPIRE') && ADMIN_PASSWORD_EXPIRE != 'Never') {

            $admin_id = tep_session_var('login_id');
            $myAccount = $this->getAdminObj($admin_id);
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
                $dateTimestamp1 = strtotime($myAccount['password_last_update']);
                if ($dateTimestamp1 === false) {
                    $expiredFlag = true;
                } else if ($dateTimestamp1 < $dateTimestamp2) {
                    $expiredFlag = true;
                }

            }
        }
        $admin = new Admin();
        $adminInfo = $admin->getAdditionalInfo();
        return $this->render('index', [
          'expiredFlag' => $expiredFlag,
          'adminInfo' => $adminInfo,
          'hidden_admin_language' => ((isset($adminInfo['hidden_admin_language']) && is_array($adminInfo['hidden_admin_language'])) ?$adminInfo['hidden_admin_language']: []),
          'global_hidden_admin_language' => \common\helpers\Language::getAdminHiddenLanguages()
            ]);
    }

    function actionHideLanguage() {
        $hide = \Yii::$app->request->post('hide', false);
        $id  = (int)\Yii::$app->request->post('id', 0);
        $ret = ['error' => 1];
        if ($hide !== false && \common\helpers\Language::get_default_language_id()!=$id) {
            $admin = new Admin();
            $adminInfo = $admin->getAdditionalInfo();
            $hidden_admin_language = ((isset($adminInfo['hidden_admin_language']) && is_array($adminInfo['hidden_admin_language'])) ?$adminInfo['hidden_admin_language']: []);

            if ($hide) {
                $hidden_admin_language[] = $id;
                $hidden_admin_language = array_unique($hidden_admin_language);
            } else {
                $hidden_admin_language = array_diff($hidden_admin_language, [$id]);
            }
            $adminInfo['hidden_admin_language'] = $hidden_admin_language;
            $admin->saveAdditionalInfo($adminInfo);
            $hidden_admin_language = \common\helpers\Language::getAdminHiddenLanguages();

            $ret = ['ok' => 1, 'hidden_admin_language' => array_values($hidden_admin_language)];
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $ret;

    }

    function actionAdminaccountactions() {
        $this->layout = FALSE;

        $admin_id = (int) \Yii::$app->request->post('admin_id');

        if ($admin_id == 0) {
            $admin_id = tep_session_var('login_id');
        }

        $myAccount = $this->getAdminObj($admin_id);

        $customers_query = tep_db_query("select customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where customers_id=" . $myAccount['customers_id']);
        $customers = tep_db_fetch_array($customers_query);
        if ($customers) {
            $myAccount['admin_customer'] = $customers['customers_firstname'] . ' ' . $customers['customers_lastname'] . ' <' . $customers['customers_email_address'] . '>';
        }

        if (!is_array($myAccount))
            die("Wrong admin id: $admin_id");
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image_svg'];
        }
        return $this->renderAjax('accountactions', ['myAccount' => $myAccount, 'avatar' => @GetImageSize(DIR_FS_CATALOG_IMAGES . $myAccount['avatar']), 'image' => tep_image(DIR_WS_CATALOG_IMAGES . $myAccount['avatar'], $myAccount['admin_firstname'] . " " . $myAccount['admin_lastname'])]);
    }

    function getAdminObj($admin_id) {
        return $this->adminRepository->getAdminData($admin_id);
    }

    function actionSaveaccount() {
        $this->layout = FALSE;
        $error        = FALSE;
        $message      = '';
        $messageType  = 'success';
        $html = "";

        $hiddenPassword = TEXT_INFO_PASSWORD_HIDDEN;
        $login_id  = (int) tep_session_var( 'login_id' );
        $myAccount = $this->getAdminObj( $login_id );
        if( is_array( $myAccount ) ) $mInfo = new \objectInfo( $myAccount );

        $admin_id = (int) \Yii::$app->request->post( 'admin_id' );

        if( $login_id !== $admin_id ) {
            $error   = TRUE;
            $message = TEXT_MESS_WRONG_DATA;
        }

            $popupname        = \Yii::$app->request->post( 'popupname' );
            $admin_email_address = '';
            $stored_email = array();
            if($popupname == 'name'){
                $admin_firstname        = \Yii::$app->request->post( 'admin_firstname' );
                $admin_lastname         = \Yii::$app->request->post( 'admin_lastname' );
            }elseif($popupname == 'email'){
                $admin_email_address    = \Yii::$app->request->post( 'admin_email_address' );
                $stored_email[]      = 'NONE';

                $check_email_query = tep_db_query( "select admin_email_address from " . TABLE_ADMIN . " where admin_id <> " . $admin_id . "" );
                while( $check_email = tep_db_fetch_array( $check_email_query ) ) {
                                $stored_email[] = $check_email['admin_email_address'];
                }
                if( in_array( $admin_email_address, $stored_email )) {
                    $error   = TRUE;
                    $message = TEXT_MESS_EMAIL_EXISTS;
                }
            }elseif($popupname == 'password'){
                $password_confirmation = \Yii::$app->request->post('password_confirmation');
                $admin_password = \Yii::$app->request->post('admin_password');
                $admin_password_confirm = \Yii::$app->request->post('admin_password_confirm');
                $check_pass_query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'");
                $check_pass = tep_db_fetch_array($check_pass_query);
                if (!\common\helpers\Password::validate_password($password_confirmation, $check_pass['admin_password'], 'backend')) {
                    $message = TEXT_MESS_PASSWORD_WRONG;
                    $error = TRUE;
                } else if ($admin_password != $admin_password_confirm) {
                    $message = TEXT_MESS_PASSWORD_WRONG;
                    $error = TRUE;
                } else if ($admin_password == $password_confirmation) {
                    $message = TEXT_MESS_PASSWORD_LIKE_CURRENT;
                    $error = TRUE;
                } else {
                    if (defined('ADMIN_PASSWORD_BAN_EASY') && ADMIN_PASSWORD_BAN_EASY == 'True') {
                        $dontAcceptList = [
                            $check_pass['admin_username'],
                            $check_pass['admin_firstname'],
                            $check_pass['admin_lastname'],
                            $check_pass['admin_phone_number'],
                        ];
                        foreach ($dontAcceptList as $dontAcceptItem) {
                            if (!empty($dontAcceptItem)) {
                                if (preg_match('/^' . preg_quote($dontAcceptItem) . '/i', $admin_password)) {
                                    $message = TEXT_MESS_PASSWORD_START_AT . ' ' . $dontAcceptItem;
                                    $error = TRUE;
                                }
                                if (preg_match('/' . preg_quote($dontAcceptItem) . '$/i', $admin_password)) {
                                    $message = TEXT_MESS_PASSWORD_END_AT . ' ' . $dontAcceptItem;
                                    $error = TRUE;
                                }
                            }
                        }
                        if($error === FALSE ) {
                            $easyPassCheck = \common\models\EasyPasswords::find()
                                    ->where(['password' => $admin_password])
                                    ->one();
                            if ($easyPassCheck instanceof \common\models\EasyPasswords) {
                                $message = TEXT_MESS_PASSWORD_EASY;
                                $error = TRUE;
                            }
                            unset($easyPassCheck);
                        }
                    }
                    if (defined('ADMIN_PASSWORD_USE_SAME') && ADMIN_PASSWORD_USE_SAME == 'True') {
                        if (\common\models\AdminOldPasswords::isOld($admin_id, tep_db_prepare_input($admin_password)) == true) {
                            $message = TEXT_MESS_PASSWORD_OLD;
                            $error = TRUE;
                        }
                    }
                }
            }elseif($popupname == 'group'){
                $admin_groups_id = \Yii::$app->request->post( 'admin_groups_id' );
            }elseif($popupname == 'avatar'){
                $file_name = Uploads::move($_POST['avatar']);
                $avatar_img = $file_name ? $file_name : '';
            }elseif($popupname == 'admin_username'){
                $admin_username = \Yii::$app->request->post( 'admin_username' );
            } elseif ($popupname == 'customers_id') {
                $customers_id = \Yii::$app->request->post( 'customers_id' );
            } elseif ($popupname == 'pin') {
                $pin = \Yii::$app->request->post( 'pin' );
            }elseif ($popupname == 'pos_platform_id') {
                $pos_platform_id= \Yii::$app->request->post('pos_platform_id',0);
            }elseif ($popupname == 'pos_currency_id') {
                $pos_currency_id= \Yii::$app->request->post( 'pos_currency_id',0);
            }

            if($error === FALSE ) {
                if ($popupname == 'name') {
                $sql_data_array['admin_firstname'] = tep_db_prepare_input($admin_firstname);
                $sql_data_array['admin_lastname'] = tep_db_prepare_input($admin_lastname);
            } elseif ($popupname == 'email') {
                $sql_data_array['admin_email_address'] = tep_db_prepare_input($admin_email_address);
            } elseif ($popupname == 'password') {
                if (defined('ADMIN_PASSWORD_USE_SAME') && ADMIN_PASSWORD_USE_SAME == 'True') {
                    \common\models\AdminOldPasswords::addOld($admin_id, tep_db_prepare_input($admin_password));
                }
                $sql_data_array['admin_password'] = \common\helpers\Password::encrypt_password(tep_db_prepare_input($admin_password), 'backend');
                $sql_data_array['password_last_update'] = 'now()';
            } elseif ($popupname == 'group') {
                $sql_data_array['admin_groups_id'] = tep_db_prepare_input($admin_groups_id);
            } elseif ($popupname == 'avatar') {
                $sql_data_array['avatar'] = tep_db_prepare_input($avatar_img);
            } elseif ($popupname == 'admin_username') {
                $sql_data_array['admin_username'] = tep_db_prepare_input($admin_username);
            } elseif ($popupname == 'customers_id') {
                $sql_data_array['customers_id'] = tep_db_prepare_input($customers_id);
            } elseif ($popupname == 'pin') {
                $sql_data_array['pin'] = tep_db_prepare_input($pin);
            }elseif ($popupname == 'pos_platform_id') {
                $sql_data_array['pos_platform_id'] = tep_db_prepare_input($pos_platform_id);
            }elseif ($popupname == 'pos_currency_id') {
                $sql_data_array['pos_currency_id'] = tep_db_prepare_input($pos_currency_id);
            }

            $sql_data_array['admin_modified'] = 'now()';


            tep_db_perform( TABLE_ADMIN, $sql_data_array, 'update', 'admin_id = \'' . $admin_id . '\'' );

            $data_query = tep_db_query( "select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'" );
            $data = tep_db_fetch_array( $data_query );

            $currentPlatformId = \Yii::$app->get('platform')->config()->getId();
            $platform_config = \Yii::$app->get('platform')->config($currentPlatformId);

            $STORE_NAME = $platform_config->const_value('STORE_NAME');
            $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
            $STORE_OWNER = $platform_config->const_value('STORE_OWNER');
            if ($popupname == 'password'){
              $email_params = [];
              $email_params['STORE_NAME'] = $STORE_NAME;
              $email_params['NEW_PASSWORD'] = $admin_password;
              $email_params['CUSTOMER_FIRSTNAME'] = $data['admin_firstname'];
              $email_params['HTTP_HOST'] = \common\helpers\Output::get_clickable_link(HTTP_SERVER . DIR_WS_ADMIN);
              $email_params['CUSTOMER_EMAIL'] = $data['admin_email_address'];
              $email_params['STORE_OWNER_EMAIL_ADDRESS'] = $STORE_OWNER_EMAIL_ADDRESS;
              list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Admin Password Forgotten', $email_params);
              \common\helpers\Mail::send($data['admin_firstname'] . ' ' . $data['admin_lastname'], $data['admin_email_address'], $email_subject, $email_text, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS, $email_params);
            } else {


                $email_params = array();
                $email_params['STORE_URL'] = \common\helpers\Output::get_clickable_link(HTTP_SERVER . DIR_WS_ADMIN);
                $email_params['CUSTOMER_FIRSTNAME'] = $data['admin_firstname'];
                $email_params['CUSTOMER_LASTNAME'] = $data['admin_lastname'];
                $email_params['CUSTOMER_EMAIL'] = $data['admin_email_address'];
                $email_params['STORE_OWNER'] = STORE_OWNER;
                $email_params['NEW_PASSWORD'] = $hiddenPassword;

                list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Admin update', $email_params);

                \common\helpers\Mail::send(
                    $data['admin_firstname'] . ' ' . $data['admin_lastname'],
                    $data['admin_email_address'],
                    $email_subject,//ADMIN_EMAIL_SUBJECT,
                    $email_text,//sprintf(ADMIN_EMAIL_TEXT, $sql_data_array['admin_firstname'], \common\helpers\Output::get_clickable_link($adminUrl), $sql_data_array['admin_email_address'], $makePassword, STORE_OWNER),
                    STORE_OWNER,
                    STORE_OWNER_EMAIL_ADDRESS, [], '', '', ['add_br' => 'no']);

            }
            $message = TEXT_MESS_DATA_CHANGE_SUCCESS;
        }
        if( $error === TRUE ) {
            $messageType = 'warning';
        }
        if( $message != '' ) {
            ?>
            <div class="alert alert-<?= $messageType ?> fade in">
                <i data-dismiss="alert" class="icon-remove close"></i>
                <?= $message ?>
            </div>
            <?php //echo $html ?>
        <?php
        }
    }

    function actionNameform() {
        $this->layout = false;
        $this->view->usePopupMode = true;

        $login_id = (int) tep_session_var('login_id');

        $myAccount = $this->getAdminObj($login_id);

        $html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"') . tep_draw_hidden_field('admin_id', $myAccount['admin_id']) . tep_draw_hidden_field('popupname', 'name');
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="dataTableContent">' . TEXT_INFO_FIRSTNAME . '</td>
                    <td class="dataTableContent">' . tep_draw_input_field('admin_firstname', $myAccount['admin_firstname'], 'class="form-control"') . '</td>
                </tr>
                <tr>
                    <td class="dataTableContent">' . TEXT_INFO_LASTNAME . '</td><td class="dataTableContent">' . tep_draw_input_field('admin_lastname', $myAccount['admin_lastname'], 'class="form-control"') . '</td>
                </tr>
            </table>
            <div class="btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
                <div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
            </div></form></div>';

        return $html;
    }

    function actionEmailform() {
        $this->layout = false;
        $this->view->usePopupMode = true;

        $login_id = (int) tep_session_var('login_id');

        $myAccount = $this->getAdminObj($login_id);

        $html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"') . tep_draw_hidden_field('admin_id', $myAccount['admin_id']) . tep_draw_hidden_field('popupname', 'email');
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="dataTableContent">' . TEXT_INFO_EMAIL . '</td>
                    <td class="dataTableContent">' . tep_draw_input_field('admin_email_address', $myAccount['admin_email_address'], 'class="form-control"') . '</td>
                </tr>
            </table>
            <div class="btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
                <div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
            </div></form></div>';

        return $html;
    }

    function actionPasswordform() {
        \common\helpers\Translation::init('main');
        $this->layout = false;
        $this->view->usePopupMode = true;
        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);
        $password_confirmation = \Yii::$app->request->post('password_confirmation');
        return $this->renderPartial('password-form.tpl',[
            'myAccount' => $myAccount,
            'password_confirmation' => $password_confirmation,
            'action' => \common\helpers\Output::get_all_get_params(array('action')) . 'action=update',
        ]);
    }

    function actionChangeavatar() {
        $this->layout = false;
        $this->view->usePopupMode = true;
        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);
        $html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"') . tep_draw_hidden_field('admin_id', $myAccount['admin_id']) . tep_draw_hidden_field('popupname', 'avatar');
        $html .= '<div class="avatar_img"><div class="upload" data-name="avatar"></div></div>
            <div class="btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
                <div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
            </div></form></div>';
        $html .='<script type="text/javascript">$(document).ready(function(){$(".upload").uploads1();})</script>';
        return $html;
    }

    function actionGetpassword() {
        $this->layout = false;
        $this->view->usePopupMode = true;
        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);
        $html = '<div id="accountpopup">' . tep_draw_form('check_pass_form', 'adminaccount', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="check_pass_form" onSubmit="return checkPassword();"') . tep_draw_hidden_field('admin_id', $myAccount['admin_id']);
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
							<tr>
									<td class="dataTableContent">' . TEXT_INFO_PASSWORD_CURRENT . '</td>
									<td class="dataTableContent">' . \common\helpers\Html::passwordInput('password_confirmation', '', ['class' => 'form-control']) . '</td>
							</tr>
					</table>
					<div class="btn-bar">
							<div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
							<div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
					</div></form><script>$(function(){$(\'input[type="password"]\').showPassword()})</script></div>';
        return $html;
    }

    function actionCheckpassword() {
        $this->layout = FALSE;
        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);
        $password_confirmation = \Yii::$app->request->post('password_confirmation');
        $check_pass_query = tep_db_query("select admin_password as confirm_password from " . TABLE_ADMIN . " where admin_id = '" . $myAccount['admin_id'] . "'");
        $check_pass = tep_db_fetch_array($check_pass_query);

        if (!\common\helpers\Password::validate_password($password_confirmation, $check_pass['confirm_password'], 'backend')) {
            ?>
                    <div class="alert alert-warning fade in">
                        <i data-dismiss="alert" class="icon-remove close"></i>
            <?php echo TEXT_MESS_PASSWORD_WRONG; ?>
                    </div>
            <?php
        } else {
            return $this->actionPasswordform();
        }
    }

    function actionDeleteimage() {
        $this->layout = FALSE;
        $this->view->usePopupMode = true;

        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);

        $sql_data_array['avatar'] = '';
        tep_db_perform(TABLE_ADMIN, $sql_data_array, 'update', 'admin_id = \'' . $myAccount['admin_id'] . '\'');
            ?><div class="popup-box-wrap delete_popup"><div class="around-pop-up"></div><div class="popup-box"><div class="popup-heading cat-head"><?php echo TEXT_EDITING_ACCOUNT; ?></div><div class="pop-up-content">
        								<div class="alert alert-success fade in">
        										<i data-dismiss="alert" class="icon-remove close"></i>
        <?php echo TEXT_MESSTYPE_SUCCESS; ?>
        								</div>
        				</div></div></div>
        <?php
    }

    function actionUsernameform() {
        $this->layout = false;
        $this->view->usePopupMode = true;

        $login_id = (int) tep_session_var('login_id');

        $myAccount = $this->getAdminObj($login_id);

        $html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"') . tep_draw_hidden_field('admin_id', $myAccount['admin_id']) . tep_draw_hidden_field('popupname', 'admin_username');
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td class="dataTableContent">' . TEXT_INFO_USERNAME . '</td>
						<td class="dataTableContent">' . tep_draw_input_field('admin_username', $myAccount['admin_username'], 'class="form-control"') . '</td>
					</tr>
				</table>
				<div class="btn-bar">
					<div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
					<div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
				</div></form></div>';

        return $html;
    }

    function actionCustomerform() {
        $this->layout = false;
        $this->view->usePopupMode = true;
        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);
        $customers = [];
        $customers[] = array('id' => '', 'text' => TEXT_SELECT_CUSTOMER);
        $mail_query = tep_db_query("select customers_id, customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where 1 order by customers_lastname");
        while ($customers_values = tep_db_fetch_array($mail_query)) {
            $customers[] = array(
                'id' => $customers_values['customers_id'],
                'text' => $customers_values['customers_lastname'] . ', ' . $customers_values['customers_firstname'] . ' (' . $customers_values['customers_email_address'] . ')',
            );
        }
        $html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"') . tep_draw_hidden_field('admin_id', $myAccount['admin_id']) . tep_draw_hidden_field('popupname', 'customers_id');
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td class="dataTableContent">' . ENTRY_CUSTOMER . '</td>
						<td class="dataTableContent">' . tep_draw_pull_down_menu('customers_id', $customers, $myAccount['customers_id'], 'class="form-control"') . '</td>
					</tr>
				</table>
				<div class="btn-bar">
					<div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
					<div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
				</div></form></div>';

        return $html;
    }
    function actionSelectCustomerForm() {
        $this->layout = false;
        $this->view->usePopupMode = true;
        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);

        return $this->renderPartial('customer-form.tpl',[
            'myAccount' => $myAccount,
            'action' => \common\helpers\Output::get_all_get_params(array('action')) . 'action=update',
        ]);
    }

    function actionSelectPosPlatformForm() {
        $this->layout = false;
        $this->view->usePopupMode = true;
        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);

        $aPlatforms = \common\classes\platform::getList(true, true);

        return $this->renderPartial('pos-platform-form.tpl',[
            'myAccount' => $myAccount,
            'platformDropDown' => (['0' => TEXT_ALL]+ArrayHelper::map($aPlatforms,'id','text')),
            'posPlatformId' => isset($myAccount['posPlatform']['platform_id'])?$myAccount['posPlatform']['platform_id']:0,
            'action' => \common\helpers\Output::get_all_get_params(array('action')) . 'action=update',
        ]);
    }

    function actionSelectPosCurrencyForm() {
        $this->layout = false;
        $this->view->usePopupMode = true;
        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);

        $aCurrencies = Currencies::find()
            ->where(['status'=> 1])
            ->orderBy(['sort_order' => SORT_ASC])
            ->all();

        return $this->renderPartial('pos-currency-form.tpl',[
            'myAccount' => $myAccount,
            'currencyDropDown' => (['0' => TEXT_DEFAULT]+ArrayHelper::map($aCurrencies,'currencies_id','code')),
            'posCurrencyId' => isset($myAccount['posPlatform']['platform_id'])?$myAccount['posPlatform']['platform_id']:0,
            'action' => \common\helpers\Output::get_all_get_params(array('action')) . 'action=update',
        ]);
    }

    public function actionGetCustomers($term = '',$limit=25) {
        $aCustomers = Customers::find()
            ->select(['customers_id', 'customers_email_address','customers_firstname','customers_lastname'])
            ->FilterWhere(
                [
                    'or',
                    ['like','customers_email_address',$term],
                    ['like','customers_firstname',$term],
                    ['like','customers_lastname',$term],
                    ['like','pin',$term],
                    ['like','customers_telephone',$term],
                ]
            )
            ->orderBy('customers_lastname')
            ->limit($limit)
            ->asArray()
            ->all();

        $result = [];

        if($aCustomers != null){
            foreach ($aCustomers as $customer){
                $customerText = $customer['customers_lastname'] . ', ' . $customer['customers_firstname'] . ' (' . $customer['customers_email_address'] . ')';
                $result[] = ['id'=>$customer['customers_id'],'label'=>$customerText,'value'=>$customerText];
            }
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $result;
    }
    function actionGetpin() {
        $this->layout = false;
        $this->view->usePopupMode = true;
        $login_id = (int) tep_session_var('login_id');
        $myAccount = $this->getAdminObj($login_id);
        $html = '<div id="accountpopup">' . tep_draw_form('save_account_form', 'adminaccount', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="save_account_form" onSubmit="return saveAccount();"') . tep_draw_hidden_field('admin_id', $myAccount['admin_id']) . tep_draw_hidden_field('popupname', 'pin');
        $html .= '<table cellspacing="0" cellpadding="0" width="100%">
					<tr>
						<td class="dataTableContent">' . TEXT_PIN . '</td>
						<td class="dataTableContent">' . tep_draw_input_field('pin', $myAccount['pin'], 'class="form-control"') . '</td>
					</tr>
				</table>
				<div class="btn-bar">
					<div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">' . IMAGE_CANCEL . '</a></div>
					<div class="btn-right"><button class="btn btn-primary">' . IMAGE_UPDATE . '</button></div>
				</div></form></div>';

        return $html;
    }

    public function actionGeneratePassword()
    {
        if (\Yii::$app->request->isAjax) {
            $frontend = (int)\Yii::$app->request->get('frontend', 0);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \common\helpers\Password::randomize($frontend);
        }
    }

    public function actionFirstSetup()
    {
        global $login_id;

        $admin = \common\models\Admin::findOne($login_id);
        if (!($admin instanceof \common\models\Admin)) {
            return $this->redirect(['index/']);
        }
        if ($admin->disposable != 1) {
            return $this->redirect(['index/']);
        }
        if (\Yii::$app->request->isPost) {
            $admin->admin_firstname = \Yii::$app->request->post('admin_firstname');
            $admin->admin_lastname = \Yii::$app->request->post('admin_lastname');
            $admin->admin_email_address = \Yii::$app->request->post('admin_email_address');
            $admin->admin_phone_number = \Yii::$app->request->post('admin_phone_number');

            $admin_password = \common\helpers\Password::randomize(false);
            //$admin_password = \Yii::$app->request->post('admin_password');
            $admin->admin_password = \common\helpers\Password::encrypt_password(tep_db_prepare_input($admin_password), 'backend');

            $admin->disposable = 0;
            $admin->save(false);

            $email_params = array();
            $email_params['STORE_URL'] = \common\helpers\Output::get_clickable_link(HTTP_SERVER . DIR_WS_ADMIN);
            $email_params['CUSTOMER_FIRSTNAME'] = $admin->admin_firstname;
            $email_params['CUSTOMER_LASTNAME'] = $admin->admin_lastname;
            $email_params['CUSTOMER_EMAIL'] = $admin->admin_email_address;
            $email_params['STORE_OWNER'] = STORE_OWNER;
            $email_params['NEW_PASSWORD'] = $admin_password;

            list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Admin update', $email_params);

            \common\helpers\Mail::send(
                $admin->admin_firstname . ' ' . $admin->admin_lastname,
                $admin->admin_email_address,
                $email_subject,
                $email_text,
                STORE_OWNER,
                STORE_OWNER_EMAIL_ADDRESS, [], '', '', ['add_br' => 'no']
            );

            return $this->redirect(['logout/']);
        }
        $this->layout = false;
        return $this->render('first-setup');
    }

}
