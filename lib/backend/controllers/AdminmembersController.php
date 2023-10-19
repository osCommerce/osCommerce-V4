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

class AdminmembersController extends Sceleton {

    public $acl = ['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_MEMBERS'];

    public function actionIndex() {
        $this->selectedMenu = array('administrator', 'adminmembers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminmembers/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="' . \Yii::$app->urlManager->createUrl(['adminmembers/adminedit']) . '" class="btn btn-primary">' . IMAGE_INSERT . '</a>';
        $this->view->adminTable = array(
            array(
                'title' => TABLE_HEADING_NAME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_EMAIL,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_GROUPS,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_LOGNUM,
                'not_important' => 1
            ),
        );

        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int)\Yii::$app->request->get('row', 0);

        $access_array = [];
        $access_array[0] = TEXT_ALL;
        $access_query = tep_db_query("select * from " . TABLE_ACCESS_LEVELS . " order by access_levels_id ");
        while ($access = tep_db_fetch_array($access_query)) {
            $access_array[$access['access_levels_id']] = $access['access_levels_name'];
        }

        $this->view->filterStatusTypes = \yii\helpers\Html::dropDownList('aclID', (int)\Yii::$app->request->get('aclID', 0), $access_array, ['class'=>'form-control']);

        return $this->render('index');
    }

    public function actionMemberlist() {

        \common\helpers\Translation::init('admin/adminmembers');

        $draw = \Yii::$app->request->get('draw');
        $start = \Yii::$app->request->get('start');
        $length = \Yii::$app->request->get('length');

        $search = '';
        if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_keywords = explode(" ", $keywords);
            if (is_array($search_keywords) && count($search_keywords) > 1) {
                $search_condition = " where 1";
                foreach ($search_keywords as $key => $keyword) {
                    $search_condition .= " and (";
                    $search_condition .= " a.admin_firstname like '%" . tep_db_input($keyword) . "%' ";
                    $search_condition .= " or a.admin_lastname like '%" . tep_db_input($keyword) . "%' ";
                    $search_condition .= " or a.admin_email_address like '%" . tep_db_input($keyword) . "%' ";
                    $search_condition .= ") ";
                }
            } else {
                $search_condition = " where (a.admin_firstname like '%" . $keywords . "%' or a.admin_lastname like '%" . $keywords . "%' or a.admin_email_address like '%" . $keywords . "%')";
            }
        } else {
            $search_condition = " where 1 ";
        }

        $formFilter = \Yii::$app->request->get('filter');
        parse_str($formFilter, $filter);
        if ($filter['aclID'] > 0) {
            $search .= " and a.access_levels_id = '" . (int) $filter['aclID'] . "'";
        }
        if (isset($filter['status']) && $filter['status'] == 1) {
            $search .= " and a.login_failture < '3'";
        }
        if (isset($filter['status']) && $filter['status'] == 2) {
            $search .= " and a.login_failture > '2'";
        }
        $search_condition .= $search;

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "a.admin_firstname " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "a.admin_email_address " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 2:
                    $orderBy = "al.access_levels_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 3:
                    $orderBy = "a.admin_lognum " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "a.admin_lastname, a.admin_firstname";
                    break;
            }
        } else {
            $orderBy = "a.admin_firstname, a.admin_lastname";
        }

        $db_admin_query_raw = "select a.*, al.access_levels_name
                            from " . TABLE_ADMIN . " a
                            left join " . TABLE_ACCESS_LEVELS . " al ON a.access_levels_id = al.access_levels_id
                            $search_condition
                            order by $orderBy";
        $current_page_number = ($start / $length) + 1;

        $db_admin_split = new \splitPageResults($current_page_number, $length, $db_admin_query_raw, $db_admin_query_numrows, 'a.admin_id');

        $db_admin_query = tep_db_query($db_admin_query_raw);

        $recordsTotal = $recordsFiltered = 0;
        $responseList = array();
        while ($admin = tep_db_fetch_array($db_admin_query)) {
            $disabledAdmin = '';
            if ($admin['login_failture'] > 2) {
                $disabledAdmin = 'dis_module';
            }
            $responseList[] = array(
                '<div class="' . $disabledAdmin . '">' . $admin['admin_firstname'] . " " . $admin['admin_lastname'] . '<input class="cell_identify" type="hidden" value="' . $admin['admin_id'] . '">' . '</div>',
                '<div class="' . $disabledAdmin . '">' . $admin['admin_email_address'] . '</div>',
                '<div class="' . $disabledAdmin . '">' . $admin['access_levels_name'] . (empty($admin['admin_persmissions']) ? '' : ' (' . TEXT_MANUALLY_UPDATED . ')') . '</div>',
                '<div class="' . $disabledAdmin . '">' . $admin['admin_lognum'] . '</div>',
            );
        }

        $_response = array(
            'draw' => $draw,
            'recordsTotal' => $db_admin_query_numrows,
            'recordsFiltered' => $db_admin_query_numrows,
            'data' => $responseList
        );
        echo json_encode($_response, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    function actionAdminmembersactions() {
        \common\helpers\Translation::init('admin/adminmembers');

        $this->layout = false;

        $admin_id = \Yii::$app->request->post('admin_id');

        $query = tep_db_query("
          select distinct(a.admin_id), a.*, al.access_levels_name
          from " . TABLE_ADMIN . " a
          left join " . TABLE_ACCESS_LEVELS . " al ON a.access_levels_id = al.access_levels_id
          where a.admin_id = '" . (int) $admin_id . "'");

        $admins = tep_db_fetch_array($query);

        if (!is_array($admins)) {
            die("Wrong data.");
        }

        $mInfo = new \objectInfo($admins);

        echo '<div class="row_or_wrapp">';
        echo '<div class="row_or"><div>' . TEXT_INFO_FULLNAME . '</div><div>' . $mInfo->admin_firstname . ' ' . $mInfo->admin_lastname . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_EMAIL . '</div><div>' . $mInfo->admin_email_address . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_PHONE . '</div><div>' . $mInfo->admin_phone_number . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_GROUP . '</div><div>' . $mInfo->access_levels_name . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_CREATED . '</div><div>' . \common\helpers\Date::date_short($mInfo->admin_created) . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($mInfo->admin_modified) . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_LOGDATE . '</div><div>' . \common\helpers\Date::date_short($mInfo->admin_logdate) . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_LOGNUM . '</div><div>' . $mInfo->admin_lognum . '</div></div>';
        echo '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<a class="btn btn-edit btn-no-margin" href="' . \Yii::$app->urlManager->createUrl(['adminmembers/adminedit', 'admin_id' => $mInfo->admin_id]) . '">' . IMAGE_EDIT . '</a>' . '<button onclick="confirmDeleteAdmin(' . $mInfo->admin_id . ')" class="btn btn-delete">' . IMAGE_DELETE . '</button>';
        if (\common\helpers\Acl::rule(['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_BOXES'])) {
            echo '<a class="btn btn-primary btn-process-order" href="' . \Yii::$app->urlManager->createUrl(['adminmembers/override-permissions', 'admin_id' => $mInfo->admin_id]) . '">' . TEXT_OVERRIDE_PERMISSIONS . '</a>';
        }
        if (\common\helpers\Acl::rule(['SUPERUSER'])) {
            echo '<a class="btn btn-process-order" href="' . \Yii::$app->urlManager->createUrl(['adminmembers/assign-platforms', 'admin_id' => $mInfo->admin_id]) . '">' . TEXT_ASSIGN_PLATFORMS . '</a>';
        }
        if (\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_WAREHOUSES'])) {
            echo '<button class="btn btn-process-order" onclick="assignWarehouses(' . $mInfo->admin_id . ')">' . TEXT_ASSIGN_WAREHOUSES . '</button>';
        }
        if (\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_SUPPLIERS'])) {
            echo '<button class="btn btn-process-order" onclick="assignSuppliers(' . $mInfo->admin_id . ')">' . TEXT_ASSIGN_SUPPLIERS . '</button>';
        }
        if ($mInfo->login_failture > 2) {
            if (\common\helpers\Acl::rule(['MANAGE_MEMBERS', 'TEXT_ENABLE_USER'])) {
                echo '<button class="btn btn-primary btn-process-order" onclick="enableUser(' . $mInfo->admin_id . ')">' . TEXT_ENABLE_USER . '</button>';
            }
            if (!empty($mInfo->login_failture_date)) {
                echo '<div class="row_or"><div>DATE:</div><div>' . \common\helpers\Date::date_short($mInfo->login_failture_date) . '</div></div>';
            }
            if (!empty($mInfo->login_failture_ip)) {
                echo '<div class="row_or"><div>IP:</div><div>' . $mInfo->login_failture_ip . '</div></div>';
            }
        } else {
            if (\common\helpers\Acl::rule(['MANAGE_MEMBERS', 'TEXT_DISABLE_USER'])) {
                echo '<button class="btn btn-primary btn-process-order" onclick="disableUser(' . $mInfo->admin_id . ')">' . TEXT_DISABLE_USER . '</button>';
            }
        }
        /**
         * @var $ext \common\extensions\GoogleAuthenticator\GoogleAuthenticator
         */
        if ($ext = \common\helpers\Extensions::isAllowed('GoogleAuthenticator')) {
            echo $ext::manageButtons($mInfo);
        }

        echo '<a class="btn btn-primary btn-process-order" href="' . \Yii::$app->urlManager->createUrl(['adminmembers/admin-login-view', 'admin_id' => $mInfo->admin_id]) . '">' . TEXT_ADMIN_LOGIN_VIEW . '</a>';
        echo '<a class="btn btn-danger btn-process-order active" href="' . \Yii::$app->urlManager->createUrl(['adminmembers/admin-login-view', 'type' => 'invalid', 'admin_id' => $mInfo->admin_id]) . '">' . (defined('TEXT_ADMIN_LOGIN_VIEW_INVALID')? TEXT_ADMIN_LOGIN_VIEW_INVALID : ''). '</a>';
        echo '<a class="btn btn-primary btn-process-order" href="' . \Yii::$app->urlManager->createUrl(['adminmembers/admin-device-view', 'admin_id' => $mInfo->admin_id]) . '">' . TEXT_ADMIN_DEVICE_VIEW . '</a>';
        echo '<a class="btn btn-primary btn-process-order" href="' . \Yii::$app->urlManager->createUrl(['adminmembers/admin-session-view', 'admin_id' => $mInfo->admin_id]) . '">' . TEXT_ADMIN_SESSION_VIEW . '</a>';
        echo '<a class="btn btn-primary btn-process-order" href="' . \Yii::$app->urlManager->createUrl(['adminmembers/admin-login-session-view', 'admin_id' => $mInfo->admin_id]) . '">' . TEXT_ADMIN_LOGIN_SESSION_VIEW . '</a>';
        echo '</div>';

       
        \common\helpers\Translation::init('admin/customers');
        $titleDataPattern = sprintf(ENTRY_PASSWORD_ERROR, ADMIN_PASSWORD_MIN_LENGTH);
        $passDataPattern = '.{' . ADMIN_PASSWORD_MIN_LENGTH . '}';
        if (defined('ADMIN_PASSWORD_STRONG')) {
            if (ADMIN_PASSWORD_STRONG == 'ULNS') {
                $titleDataPattern = sprintf(ENTRY_PASSWORD_ULNS_ERROR, ADMIN_PASSWORD_MIN_LENGTH);
                $passDataPattern = addslashes('(?=.*\d)(?=.*\W+)(?=.*[a-z])(?=.*[A-Z]).{' . ADMIN_PASSWORD_MIN_LENGTH . '}');
            } elseif (ADMIN_PASSWORD_STRONG == 'ULN') {
                $titleDataPattern = sprintf(ENTRY_PASSWORD_ULN_ERROR, ADMIN_PASSWORD_MIN_LENGTH);
                $passDataPattern = addslashes('(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{' . ADMIN_PASSWORD_MIN_LENGTH . '}');
            }
        }
        \common\helpers\Translation::init('main');
        echo '<div class="btn-toolbar btn-toolbar-order btn-toolbar-pass"><span class="btn btn-pass-cus">'.T_UPDATE_PASS.'</span>
                            <script>
                            $(document).ready(function() {
                            $("a.popup").popUp();
                            $(".btn-pass-cus").on("click", function(){
                                alertMessage("<div class=\"popup-heading popup-heading-pass\">' . TEXT_UPDATE_PASSWORD_FOR. ' '.$mInfo->admin_firstname.'&nbsp;'.$mInfo->admin_lastname.'</div><div class=\"popup-content\"><form name=\"passw_form\" id=\"passw_form\" action=\"' . tep_href_link('adminmembers', \common\helpers\Output::get_all_get_params(array('admin_id', 'action')) . 'admin_id=' . $mInfo->admin_id . '&action=password') . '\" method=\"post\"><table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tr><td class=\"dataTableContent\"><a href=\"#\" class=\"generate_password\">'.TEXT_GENERATE_PASSWORD.'</a></td></tr><tr><td class=\"dataTableContent\">'.T_NEW_PASS.':</td><td class=\"dataTableContent\"><input type=\"password\" data-required=\"'.$titleDataPattern.'\" data-pattern=\"'.$passDataPattern.'\" name=\"change_pass\" class=\"form-control\"></td></tr></table><div class=\"btn-bar\" style=\"padding-bottom: 0;\"><div class=\"btn-left\"><span class=\"btn btn-cancel\">' . IMAGE_CANCEL . '</span></div><div class=\"btn-right\"><input type=\"submit\" value=\"' . IMAGE_UPDATE. '\" class=\"btn btn-primary\"></div></div><input type=\"hidden\" name=\"admin_id\" value=\"'.$mInfo->admin_id.'\"></form></div>");
                                passFormAfretShow();
                            });
                            });
                            </script>
                            </div>';
        
    }

    function actionAssignPlatforms() {
        \common\helpers\Translation::init('admin/adminmembers');

        $admin_id = (int)\Yii::$app->request->get('admin_id');
        if ($admin_id==0) {
          return $this->redirect(\Yii::$app->urlManager->createUrl(['adminmembers']));
        }

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#save_item_form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        $assigned_platforms = \yii\helpers\ArrayHelper::map(
            \common\models\AdminPlatforms::find()->select(['platform_id'])->where(['admin_id' => $admin_id])->asArray()->all(),
            'platform_id','platform_id'
        );
        return $this->render('assign-platforms', [
            'admin_id' => $admin_id,
            'assigned_platforms' => $assigned_platforms,
            'platforms' => \common\classes\platform::getList(true,true),
        ]);

    }

    function actionAssignWarehouses() {
        \common\helpers\Translation::init('admin/adminmembers');
        $this->layout = false;
        $admin_id = (int)\Yii::$app->request->post('admin_id');

        $assigned_warehouses = \yii\helpers\ArrayHelper::map(
            \common\models\AdminWarehouses::find()->select(['warehouse_id'])->where(['admin_id' => $admin_id])->asArray()->all(),
            'warehouse_id','warehouse_id'
        );

        echo tep_draw_form('admin', 'adminmembers', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="admin_edit" onSubmit="return check_form();"');
        echo '<div class="or_box_head">' . BOX_CATALOG_WAREHOUSES . '</div>';

        foreach (\common\helpers\Warehouses::get_warehouses(true) as $info){
            echo '<div class="row_fields">';
            echo tep_draw_checkbox_field('warehouse_id[]', $info['id'], isset($assigned_warehouses[$info['id']])) . '<span>' . $info['text'] . '</span>';
            echo '</div>';
        }

        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<input type="submit" class="btn btn-no-margin" value="' . IMAGE_UPDATE . '" >';
        echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
        echo '</div>';

        echo tep_draw_hidden_field('admin_id', $admin_id);
        echo tep_draw_hidden_field('action', 'warehouses');
        echo '</form>';
    }

    function actionAssignSuppliers() {
        \common\helpers\Translation::init('admin/adminmembers');
        $this->layout = false;
        $admin_id = (int)\Yii::$app->request->post('admin_id');

        $assigned_suppliers = \yii\helpers\ArrayHelper::map(
            \common\models\AdminSuppliers::find()->select(['suppliers_id'])->where(['admin_id' => $admin_id])->asArray()->all(),
            'suppliers_id','suppliers_id'
        );

        echo tep_draw_form('admin', 'adminmembers', \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="admin_edit" onSubmit="return check_form();"');
        echo '<div class="or_box_head">' . BOX_CATALOG_SUPPIERS . '</div>';

        foreach (\common\helpers\Suppliers::getSuppliers(true) as $info){
            echo '<div class="row_fields">';
            echo tep_draw_checkbox_field('suppliers_id[]', $info['suppliers_id'], isset($assigned_suppliers[$info['suppliers_id']])) . '<span>' . $info['suppliers_name'] . '</span>';
            echo '</div>';
        }

        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<input type="submit" class="btn btn-no-margin" value="' . IMAGE_UPDATE . '" >';
        echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
        echo '</div>';

        echo tep_draw_hidden_field('admin_id', $admin_id);
        echo tep_draw_hidden_field('action', 'suppliers');
        echo '</form>';
    }

    function actionAdminedit() {
        \common\helpers\Translation::init('admin/adminmembers');

        //$this->layout = false;
        $error = $entry_firstname_error = $entry_lastname_error = $entry_admin_email_address_error = false;
        $entry_admin_groups_name_error = false;

        $admin_id = (int)\Yii::$app->request->get('admin_id', 0);

        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = $admin_id; ");
        if ($admin = tep_db_fetch_array($query))
            $mInfo = new \objectInfo($admin);

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#admin_edit\').trigger(\'submit\')">' . IMAGE_UPDATE . '</span>';

        $access_array = [];
        $access_query = tep_db_query("select * from " . TABLE_ACCESS_LEVELS . " order by sort_order, access_levels_name ");
        while ($access = tep_db_fetch_array($access_query)) {
            $access_array[] = [
                'id' => $access['access_levels_id'],
                'text' => $access['access_levels_name'],
            ];
        }
        /*$access_array[] = array(
            array('id' => 0, 'text' => 'none')
        );*/
        $adminTwoStepAuthArray = array(
            array('id' => '', 'text' => TEXT_TWO_STEP_AUTH_DEFAULT),
            array('id' => 'email', 'text' => TEXT_TWO_STEP_AUTH_EMAIL),
            array('id' => 'sms', 'text' => TEXT_TWO_STEP_AUTH_SMS),
            array('id' => 'disabled', 'text' => TEXT_DISABLED),
        );

        \common\helpers\Translation::init('admin/texts');

        return $this->render('adminedit', [
            'mInfo' => $mInfo ?? null,
            'access_array' => $access_array,
            'admin_id' => $admin_id,
            'adminTwoStepAuthArray' => $adminTwoStepAuthArray
        ]);
    }

    function actionConfirmadmindelete() {
        \common\helpers\Translation::init('admin/adminmembers');
        \common\helpers\Translation::init('admin/faqdesk');

        $this->layout = false;

        $admin_id = \Yii::$app->request->post('admin_id');

        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = $admin_id; ");

        if ($admin = tep_db_fetch_array($query))
            $mInfo = new \objectInfo($admin);
        else
            die("Wrong admin data.");

        echo tep_draw_form('admin', FILENAME_ADMIN_ACCOUNT, \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="admin_edit" onSubmit="return deleteAdmin();"');
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_ITEM . '</div>';
        echo '<div class="col_desc">' . TEXT_DELETE_ITEM_INTRO . ' ' . $mInfo->admin_firstname . ' ' . $mInfo->admin_lastname . '</div>';
        ?>
        <div class="btn-toolbar btn-toolbar-order">
            <button class="btn btn-delete btn-no-margin"><?php echo IMAGE_DELETE; ?></button><?php echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
        echo tep_draw_hidden_field('admin_id', $mInfo->admin_id);
        ?>
        </div>
        </form>
            <?php
        }

        function actionAdmindelete() {
            $this->layout = false;

            $admin_id = \Yii::$app->request->post('admin_id');
            if ((int)$admin_id == (int)\Yii::$app->session->get('login_id')) {
                die('Operation not permitted!');
            }

            if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
                $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
                ($logUniversal
                    ->setRelation((int)$admin_id)
                    ->setType($logUniversal::ULT_ADMIN_DELETE)
                    ->setBeforeArray(\common\models\Admin::find()
                        ->where(['admin_id' => $admin_id])
                        ->asArray(true)->one()
                    )
                );
            }

            tep_db_query("delete from " . TABLE_ADMIN . " where admin_id = '" . (int) $admin_id . "'");

            if (isset($logUniversal)) {
                ($logUniversal
                    ->setAfterArray(\common\models\Admin::find()
                        ->where(['admin_id' => $admin_id])
                        ->asArray(true)->one()
                    )
                    ->doSave(true)
                );
                unset($logUniversal);
            }
            try {
                \common\models\AdminLoginSession::deleteAll(['als_admin_id' => (int)$admin_id]);
                \common\models\AdminLogin::deleteAll(['al_admin_id' => (int)$admin_id]);
            } catch (\Exception $exc) {
                \Yii::warning($exc->getMessage() . ' ' . $exc->getTraceAsString(), 'ErrorDeleteAdminLogin');
            }
        }

        function actionAdminsubmit() {
            \common\helpers\Translation::init('admin/adminmembers');

            //$this->layout = FALSE;
            $error = FALSE;
            $message = '';

            $messageType = 'success';

            $admin_id = \Yii::$app->request->post('admin_id');

            if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
                $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
                $logUniversal->setRelation((int)$admin_id);
            }
            $action = \Yii::$app->request->post('action');
            if ($action == 'permissions') {
                if (isset($logUniversal)) {
                    ($logUniversal
                        ->setType($logUniversal::ULT_ADMIN_PLATFORM)
                        ->setBeforeArray(\common\models\AdminPlatforms::find()
                            ->select('platform_id')
                            ->where(['admin_id' => $admin_id])
                            ->indexBy('platform_id')->asArray(true)->column()
                        )
                    );
                }
                $platform = \Yii::$app->request->post('platform_id');
                \common\models\AdminPlatforms::deleteAll(['admin_id' => $admin_id]);
                if (is_array($platform)) {
                    foreach ($platform as $value) {
                        $object = new \common\models\AdminPlatforms();
                        $object->platform_id =(int)$value;
                        $object->admin_id =(int)$admin_id;
                        $object->save();
                    }
                }
                if (isset($logUniversal)) {
                    ($logUniversal
                        ->setAfterArray(\common\models\AdminPlatforms::find()
                            ->select('platform_id')
                            ->where(['admin_id' => $admin_id])
                            ->indexBy('platform_id')->asArray(true)->column()
                        )
                        ->doSave(true)
                    );
                    unset($logUniversal);
                }
                return 'ok';//$this->actionAdminmembersactions();
            } elseif ($action == 'warehouses') {
                if (isset($logUniversal)) {
                    ($logUniversal
                        ->setType($logUniversal::ULT_ADMIN_WAREHOUSE)
                        ->setBeforeArray(\common\models\AdminWarehouses::find()
                            ->select('warehouse_id')
                            ->where(['admin_id' => $admin_id])
                            ->indexBy('warehouse_id')->asArray(true)->column()
                        )
                    );
                }
                $warehouses = \Yii::$app->request->post('warehouse_id');
                \common\models\AdminWarehouses::deleteAll(['admin_id' => $admin_id]);
                if (is_array($warehouses)) {
                    foreach ($warehouses as $value) {
                        $object = new \common\models\AdminWarehouses();
                        $object->warehouse_id =(int)$value;
                        $object->admin_id =(int)$admin_id;
                        $object->save();
                    }
                }
                if (isset($logUniversal)) {
                    ($logUniversal
                        ->setAfterArray(\common\models\AdminWarehouses::find()
                            ->select('warehouse_id')
                            ->where(['admin_id' => $admin_id])
                            ->indexBy('warehouse_id')->asArray(true)->column()
                        )
                        ->doSave(true)
                    );
                    unset($logUniversal);
                }
                return $this->actionAdminmembersactions();
            } elseif ($action == 'suppliers') {
                if (isset($logUniversal)) {
                    ($logUniversal
                        ->setType($logUniversal::ULT_ADMIN_SUPPLIER)
                        ->setBeforeArray(\common\models\AdminSuppliers::find()
                            ->select('suppliers_id')
                            ->where(['admin_id' => $admin_id])
                            ->indexBy('suppliers_id')->asArray(true)->column()
                        )
                    );
                }
                $suppliers = \Yii::$app->request->post('suppliers_id');
                \common\models\AdminSuppliers::deleteAll(['admin_id' => $admin_id]);
                if (is_array($suppliers)) {
                    foreach ($suppliers as $value) {
                        $object = new \common\models\AdminSuppliers();
                        $object->suppliers_id =(int)$value;
                        $object->admin_id =(int)$admin_id;
                        $object->save();
                    }
                }
                if (isset($logUniversal)) {
                    ($logUniversal
                        ->setAfterArray(\common\models\AdminSuppliers::find()
                            ->select('suppliers_id')
                            ->where(['admin_id' => $admin_id])
                            ->indexBy('suppliers_id')->asArray(true)->column()
                        )
                        ->doSave(true)
                    );
                    unset($logUniversal);
                }
                return $this->actionAdminmembersactions();
            }

            if (isset($logUniversal)) {
                $logUniversal->setBeforeArray(\common\models\Admin::find()
                    ->where(['admin_id' => $admin_id])
                    ->asArray(true)->one()
                );
            }

            $admin_firstname = tep_db_prepare_input($_POST['admin_firstname']);
            $admin_lastname = tep_db_prepare_input($_POST['admin_lastname'] ?? null);
            $admin_email_address = tep_db_prepare_input($_POST['admin_email_address']);
            $admin_phone_number = tep_db_prepare_input($_POST['admin_phone_number']);
            $admin_two_step_auth = tep_db_prepare_input($_POST['admin_two_step_auth']);
            $admin_group_level = tep_db_prepare_input($_POST['access_levels_name']);
            $frontend_translation = tep_db_prepare_input($_POST['frontend_translation'] ?? null);

            $sql_data_array = array(
                'admin_id' => $admin_id,
                'admin_firstname' => $admin_firstname,
                'admin_lastname' => $admin_lastname,
                'admin_email_address' => $admin_email_address,
                'admin_phone_number' => $admin_phone_number,
                'access_levels_id' => $admin_group_level,
                'admin_two_step_auth' => $admin_two_step_auth,
                'frontend_translation' => $frontend_translation ? 1 : 0,
            );

            if (strlen($admin_firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                $error = TRUE;
                $message .= TEXT_INFO_FIRSTNAME . ' ' . sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH) . '<br/>';
            }
            if (trim($admin_email_address) == '') {
                $error = TRUE;
                $message .= ENTRY_EMAIL_ADDRESS_CHECK_ERROR . '<br/>';
            } else{

              $checkDup = \common\models\Admin::find()
                  ->andWhere(['like', 'admin_email_address', $admin_email_address, false])
                  ->andWhere(['<>', 'admin_id', $admin_id])
                  ->exists();

              if ($checkDup) {
                  $error = true;
                  $message = ENTRY_EMAIL_ADDRESS_ERROR_EXISTS;
              }
            }

            foreach (\common\helpers\Hooks::getList('adminmembers/before-save', '') as $filename) {
                include($filename);
            }

            if ($error === false) {
                $sql_data_array['admin_email_token'] = \common\helpers\Password::encrypt_password($sql_data_array['admin_email_address'], 'backend');
                if ((int) $admin_id > 0) {
                    if (isset($logUniversal)) {
                        $logUniversal->setType($logUniversal::ULT_ADMIN_UPDATE);
                    }
                    tep_db_perform(TABLE_ADMIN, $sql_data_array, 'update', "admin_id = '" . (int) $admin_id . "'");
                    tep_db_query("update " . TABLE_ADMIN . " set admin_modified = now() where admin_id = '" . (int) $admin_id . "'");

                    $message = SUCCESS_ADMIN_UPDATED;
                } else {
                    if (isset($logUniversal)) {
                        $logUniversal->setType($logUniversal::ULT_ADMIN_CREATE);
                    }
                    $makePassword = \common\helpers\Password::randomize();
                    $sql_data_array['admin_password'] = \common\helpers\Password::encrypt_password($makePassword, 'backend');
                    $sql_data_array['password_last_update'] = 'now()';

                    tep_db_perform(TABLE_ADMIN, $sql_data_array);
                    $admin_id = tep_db_insert_id();
                    $_GET['mID'] = $admin_id; // FIXME: Why do we need this?
                    tep_db_query("update " . TABLE_ADMIN . " set admin_created = now(), admin_modified = now() where admin_id = '" . (int) $admin_id . "'");

                    $message = SUCCESS_ADMIN_CREATED;

                    $currentPlatformId = \Yii::$app->get('platform')->config()->getId();
                    $platform_config = \Yii::$app->get('platform')->config($currentPlatformId);

                    $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                    $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

                    $email_params = array();
                    $email_params['STORE_URL'] = \common\helpers\Output::get_clickable_link(tep_catalog_href_link('admin'));
                    $email_params['CUSTOMER_FIRSTNAME'] = $sql_data_array['admin_firstname'];
                    $email_params['CUSTOMER_LASTNAME'] = $sql_data_array['admin_lastname'];
                    $email_params['CUSTOMER_EMAIL'] = $sql_data_array['admin_email_address'];
                    $email_params['STORE_OWNER'] = STORE_OWNER;
                    $email_params['NEW_PASSWORD'] = $makePassword;

                    list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Admin create', $email_params);

                    \common\helpers\Mail::send(
                        $sql_data_array['admin_firstname'] . ' ' . $sql_data_array['admin_lastname'],
                        $sql_data_array['admin_email_address'],
                        $email_subject,//ADMIN_EMAIL_SUBJECT,
                        $email_text,//sprintf(ADMIN_EMAIL_TEXT, $sql_data_array['admin_firstname'], \common\helpers\Output::get_clickable_link($adminUrl), $sql_data_array['admin_email_address'], $makePassword, STORE_OWNER),
                        STORE_OWNER,
                        STORE_OWNER_EMAIL_ADDRESS, [], '', '', ['add_br' => 'no']);
                }

                if ($ext = \common\helpers\Acl::checkExtensionAllowed('Communication')) {
                    $ext::adminActionAdminEditSave((int)$admin_id, \Yii::$app->request->post('communication_group_to_admin'));
                }

                if (false === \common\helpers\Acl::rule(['SUPERUSER'], 0, '', $admin_id)) {
                  // offer to assign admin to platforms
                  $check = \common\models\AdminPlatforms::find()->andWhere(['admin_id' => $admin_id])->exists();
                  if (!$check && count(\common\classes\platform::getList(true,true))>0) {
                    $messageType = 'warning';
                    $message .=  ' ' . sprintf(TEXT_ADMIN_ASSIGN_PLATFORMS, \Yii::$app->urlManager->createUrl(['adminmembers/assign-platforms', 'admin_id' => $admin_id]) );
                  }
                }
                if (isset($logUniversal)) {
                    ($logUniversal
                        ->setRelation((int)$admin_id)
                        ->setAfterArray(\common\models\Admin::find()
                            ->where(['admin_id' => $admin_id])
                            ->asArray(true)->one()
                        )
                        ->doSave(true)
                    );
                    unset($logUniversal);
                }
            }

            if ($error === true) {
                $messageType = 'warning';
                if ($message == '')
                    $message = WARN_UNKNOWN_ERROR;
            }
            /** /
            ?>
        <div class="alert alert-<?= $messageType ?> fade in">
            <i data-dismiss="alert" class="icon-remove close"></i>
        <?= $message ?>
        </div>

        <?php

             /**/
            $messageStack = \Yii::$container->get('message_stack');
            if ($error === true) {
              $messageStack->add($message, 'header', $messageType);
              return $this->actionAdminedit(); exit;
            } else {
              $messageStack->add_session($message, 'header', $messageType);

              echo '<script> window.location.replace("' . \Yii::$app->urlManager->createUrl(['adminmembers/adminedit', 'admin_id' => $admin_id]) . '");</script>';
            }
    }

    public function actionOverridePermissions() {
        \common\helpers\Translation::init('admin/adminmembers');

        $this->selectedMenu = array('administrator', 'adminmembers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminmembers/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#save_item_form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        $admin_id = (int) \Yii::$app->request->get('admin_id');

        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'");
        $admin = tep_db_fetch_array($query);

        if (!is_array($admin)) {
            die("Wrong data.");
        }

        $adminPersmissions = explode(",", $admin['admin_persmissions']);

        $checkAccess = tep_db_query("select access_levels_persmissions from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . (int) $admin['access_levels_id'] . "'");
        $access = tep_db_fetch_array($checkAccess);
        $selectedIds = explode(",", $access['access_levels_persmissions']);

        $aclTree = \common\helpers\Acl::buildOverrideTree($selectedIds, $adminPersmissions);

        return $this->render('override-permissions', [
                    'aclTree' => $aclTree,
                    'admin_id' => $admin_id,
        ]);
    }

    public function actionRecalcAcl() {
        $this->layout = false;

        $admin_id = (int) \Yii::$app->request->post('admin_id');
        $persmissions = \Yii::$app->request->post('persmissions');

        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'");
        $admin = tep_db_fetch_array($query);

        if (!is_array($admin)) {
            die("Wrong data.");
        }

        $checkAccess = tep_db_query("select access_levels_persmissions from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . (int) $admin['access_levels_id'] . "'");
        $access = tep_db_fetch_array($checkAccess);
        $selectedIds = explode(",", $access['access_levels_persmissions']);

        $adminPersmissions = [];
        foreach ($persmissions as $persmission) {
            if (!in_array($persmission, $selectedIds)) {
                $adminPersmissions[] = $persmission; //green - added
            }
        }
        foreach ($selectedIds as $selected) {
            if (!in_array($selected, $persmissions)) {
                $adminPersmissions[] = ($selected * -1); //red - removed
            }
        }

        $aclTree = \common\helpers\Acl::buildOverrideTree($selectedIds, $adminPersmissions);

        return $this->render('recalc-acl', [
                    'aclTree' => $aclTree,
        ]);
    }

    public function actionSubmitPermissions() {

        $admin_id = (int) \Yii::$app->request->post('admin_id');
        $query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . $admin_id . "'");
        $admin = tep_db_fetch_array($query);

        if (!is_array($admin)) {
            die("Wrong data.");
        }

        if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
            $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
            ($logUniversal
                ->setRelation((int)$admin_id)
                ->setType($logUniversal::ULT_ADMIN_PERMISSION)
                ->setBeforeArray($admin)
            );
        }

        $checkAccess = tep_db_query("select access_levels_persmissions from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . (int) $admin['access_levels_id'] . "'");
        $access = tep_db_fetch_array($checkAccess);
        $selectedIds = explode(",", $access['access_levels_persmissions']);

        $persmissions = \Yii::$app->request->post('persmissions');
        if (!is_array($persmissions)) {
            $persmissions = [];
        }

        $adminPersmissions = [];
        foreach ($persmissions as $persmission) {
            if (!in_array($persmission, $selectedIds)) {
                $adminPersmissions[] = $persmission; //green - added
            }
        }
        foreach ($selectedIds as $selected) {
            if (!in_array($selected, $persmissions)) {
                $adminPersmissions[] = ($selected * -1); //red - removed
            }
        }

        $admin_persmissions = implode(",", $adminPersmissions);

        $sql_data_array = [
            'admin_persmissions' => $admin_persmissions,
        ];
        tep_db_perform(TABLE_ADMIN, $sql_data_array, 'update', "admin_id = '" . $admin_id . "'");

        if (isset($logUniversal)) {
            ($logUniversal
                ->setAfterArray(\common\models\Admin::find()
                    ->where(['admin_id' => $admin_id])
                    ->asArray(true)->one()
                )
                ->doSave(true)
            );
            unset($logUniversal);
        }

        echo '<script> window.location.replace("' . \Yii::$app->urlManager->createUrl(['adminmembers/override-permissions', 'admin_id' => $admin_id]) . '");</script>';
    }

    function actionEnableAdmin() {
        $this->layout = false;

        $admin_id = \Yii::$app->request->post('admin_id');

        if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
            $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
            ($logUniversal
                ->setRelation((int)$admin_id)
                ->setType($logUniversal::ULT_ADMIN_STATUS)
                ->setBeforeArray(\common\models\Admin::find()
                    ->where(['admin_id' => $admin_id])
                    ->asArray(true)->one()
                )
            );
        }

        tep_db_query("update " . TABLE_ADMIN . " set login_failture = 0 where admin_id = '" . (int) $admin_id . "'");

        if (isset($logUniversal)) {
            ($logUniversal
                ->setAfterArray(\common\models\Admin::find()
                    ->where(['admin_id' => $admin_id])
                    ->asArray(true)->one()
                )
                ->doSave(true)
            );
            unset($logUniversal);
        }
    }

    function actionDisableAdmin() {
        $this->layout = false;

        $admin_id = \Yii::$app->request->post('admin_id');
        if ((int)$admin_id == (int)\Yii::$app->session->get('login_id')) {
            die('Operation not permitted!');
        }

        if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
            $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
            ($logUniversal
                ->setRelation((int)$admin_id)
                ->setType($logUniversal::ULT_ADMIN_STATUS)
                ->setBeforeArray(\common\models\Admin::find()
                    ->where(['admin_id' => $admin_id])
                    ->asArray(true)->one()
                )
            );
        }

        tep_db_query("update " . TABLE_ADMIN . " set login_failture = 3 where admin_id = '" . (int) $admin_id . "'");

        if (isset($logUniversal)) {
            ($logUniversal
                ->setAfterArray(\common\models\Admin::find()
                    ->where(['admin_id' => $admin_id])
                    ->asArray(true)->one()
                )
                ->doSave(true)
            );
            unset($logUniversal);
        }

        try {
            \common\models\AdminLoginSession::deleteAll(['als_admin_id' => (int)$admin_id]);
            \common\models\AdminLogin::deleteAll(['al_admin_id' => (int)$admin_id]);
        } catch (\Exception $exc) {
            \Yii::warning($exc->getMessage() . ' ' . $exc->getTraceAsString(), 'ErrorDisableAdminLogin');
        }
    }

    function actionResetAdminGa() {
        $this->layout = false;
        /**
         * @var $ext \common\extensions\GoogleAuthenticator\GoogleAuthenticator
         */
        if ($ext = \common\helpers\Extensions::isAllowed('GoogleAuthenticator')) {
            $admin_id = \Yii::$app->request->post('admin_id');
            echo $ext::resetGaSecret($admin_id);
        }
    }

    function actionGeneratepassword() {
        $this->layout = false;

        \common\helpers\Translation::init('account/password');
        \common\helpers\Translation::init('admin/admin_account');
        \common\helpers\Translation::init('main');

        $admin_id = \Yii::$app->request->post('admin_id');
        $admin_password = \Yii::$app->request->post('change_pass');

        $message_account_password = '';
        $save = true;

        if (empty($admin_password)) {
            $message_account_password = TEXT_MESS_PASSWORD_WRONG;
            $save = false;
        }

        if ($save) {
            $adminInfo = \common\models\Admin::find()->where(['admin_id' => $admin_id])->one();
            if (!is_object($adminInfo)) {
                $message_account_password = TEXT_INVALID_TOKEN;
                $save = false;
            }
        }

        if ($save) {
            if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
                $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
                ($logUniversal
                    ->setRelation((int)$admin_id)
                    ->setType($logUniversal::ULT_ADMIN_PASSWORD)
                    ->setBeforeArray($adminInfo->toArray())
                );
            }
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
                    $save = false;
                }
            }
        }

        if ($save) {
            if (defined('ADMIN_PASSWORD_USE_SAME') && ADMIN_PASSWORD_USE_SAME == 'True') {
                \common\models\AdminOldPasswords::addOld($adminInfo->admin_id, tep_db_prepare_input($admin_password));
            }
            $adminInfo->admin_email_token = \common\helpers\Password::encrypt_password($adminInfo->admin_email_address, 'backend');
            $adminInfo->admin_password = \common\helpers\Password::encrypt_password(tep_db_prepare_input($admin_password), 'backend');
            $adminInfo->password_last_update = date('Y-m-d H:i:s');
            $adminInfo->clearToken();
            $message_account_password = TEXT_PASSWORD_CHANGED;

            if (isset($logUniversal)) {
                ($logUniversal
                    ->setAfterArray(\common\models\Admin::find()
                        ->where(['admin_id' => $admin_id])
                        ->asArray(true)->one()
                    )
                    ->doSave(true)
                );
                unset($logUniversal);
            }

            $currentPlatformId = \Yii::$app->get('platform')->config()->getId();
            $platform_config = \Yii::$app->get('platform')->config($currentPlatformId);

            $STORE_NAME = $platform_config->const_value('STORE_NAME');
            $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
            $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

            $email_params = array();
            $email_params['STORE_NAME'] = $STORE_NAME;
            $email_params['NEW_PASSWORD'] = $admin_password;
            $email_params['CUSTOMER_FIRSTNAME'] = $adminInfo->admin_firstname;
            $email_params['HTTP_HOST'] = \common\helpers\Output::get_clickable_link(HTTP_SERVER . DIR_WS_ADMIN);
            $email_params['CUSTOMER_EMAIL'] = $adminInfo->admin_email_address;
            $email_params['STORE_OWNER_EMAIL_ADDRESS'] = $STORE_OWNER_EMAIL_ADDRESS;
            list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Admin Password Forgotten', $email_params);
            \common\helpers\Mail::send($adminInfo->admin_firstname . ' ' . $adminInfo->admin_lastname, $adminInfo->admin_email_address, $email_subject, $email_text, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS, $email_params);

            echo json_encode(array('message' => $message_account_password, 'messageType' => 'alert-success'));
        } else {
            echo json_encode(array('message' => $message_account_password, 'messageType' => 'alert-danger'));
        }
    }

    function actionAdminLoginView()
    {
        \common\helpers\Translation::init('admin/admin-login-view');

        $this->selectedMenu = array('administrator', 'adminmembers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminmembers/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $type = trim(\Yii::$app->request->get('type', ''));
        $admin_id = (int)\Yii::$app->request->get('admin_id');
        $adminRecord = \common\models\Admin::findOne($admin_id);
        if ($adminRecord instanceof \common\models\Admin) {
            $adminRecord = $adminRecord->toArray();
        } elseif ($admin_id < 0) {
            $adminRecord = ['admin_id' => -1];
        }
        $adminRecord['type'] = $type;

        $this->view->LogTable = array(
            array(
                'title' => TABLE_HEADING_EVENT,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_USER,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_DEVICE,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_IP,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_AGENT,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_DATE,
                'not_important' => 0
            )
        );

        return $this->render('admin-login-view', ['adminRecord' => $adminRecord]);
    }

    function actionAdminLoginViewList()
    {
        \common\helpers\Translation::init('admin/admin-login-view');

        $id = \Yii::$app->request->get('id', 0);
        $type = trim(\Yii::$app->request->get('type', ''));
        $email = \common\models\AdminLoginLog::getAdminEmail($id);
        $draw = \Yii::$app->request->get('draw', 1);
        $start = \Yii::$app->request->get('start', 0);
        $length = \Yii::$app->request->get('length', 10);
        $logQuery = \common\models\AdminLoginLog::find();
        if ($id >= 0) {
            $logQuery->where(['or', ['all_user_id' => $id], ['all_user' => $email]]);
        }
        if ($type == 'invalid') {
            // for more info see \common\models\AdminLoginLog::$eventList
            $logQuery->andWhere(['IN', 'all_event', [1, 2, 3, 4]]);
        }
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $logQuery->andWhere(['or',
                ['like', 'all_user', tep_db_input(tep_db_prepare_input($_GET['search']['value']))],
                ['all_event' => tep_db_input(tep_db_prepare_input($_GET['search']['value']))]
            ]);
        }
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 5:
                    $logQuery->orderBy('all_date ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])) . ', all_id ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                default:
                    $logQuery->orderBy('all_date DESC, all_id DESC');
                break;
            }
        } else {
            $logQuery->orderBy('all_date DESC');
        }
        $numrows = $logQuery->count();
        if ($length > 0) {
            $logQuery->limit($length)->offset($start);
        }
        $logQuery = $logQuery->asArray(true)->all();
        $responseList = [];
        $eventList = \common\models\AdminLoginLog::$eventList;
        foreach ($eventList as &$event) {
            $event_tr = ('TEXT_' . strtoupper($event));
            $event = (defined($event_tr) ? constant($event_tr) : $event);
            unset($event);
        }
        foreach ($logQuery as $logRecord) {
            $responseList[] = array(
                ((isset($eventList[$logRecord['all_event']]) ? $eventList[$logRecord['all_event']] : 'Unknown')
                    . tep_draw_hidden_field('id', $logRecord['all_id'], 'class="cell_identify"')
                ),
                $logRecord['all_user'],
                trim($logRecord['all_device_id']),
                trim($logRecord['all_ip']),
                trim($logRecord['all_agent']),
                $logRecord['all_date']
            );
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $numrows,
            'recordsFiltered' => $numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    function actionAdminDeviceView()
    {
        \common\helpers\Translation::init('admin/admin-device-view');

        $this->selectedMenu = array('administrator', 'adminmembers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminmembers/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $admin_id = (int)\Yii::$app->request->get('admin_id');
        $adminRecord = \common\models\Admin::findOne($admin_id);
        if ($adminRecord instanceof \common\models\Admin) {
            $adminRecord = $adminRecord->toArray();
            $this->view->DeviceTable = array(
                array(
                    'title' => TABLE_HEADING_DEVICE,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_LOGIN_DATE,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_LOGIN_COUNT,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_DATE_ADD,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_BLOCKED,
                    'not_important' => 0
                ),
                array(
                    'title' => '',
                    'not_important' => 0
                )
            );
        } else {
            $adminRecord = ['admin_id' => 0];
            $this->view->DeviceTable = array(
                array(
                    'title' => TABLE_HEADING_MEMBER,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_DEVICE,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_LOGIN_DATE,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_LOGIN_COUNT,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_DATE_ADD,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_BLOCKED,
                    'not_important' => 0
                ),
                array(
                    'title' => '',
                    'not_important' => 0
                )
            );
        }

        return $this->render('admin-device-view', ['adminRecord' => $adminRecord]);
    }

    function actionAdminDeviceViewList()
    {
        \common\helpers\Translation::init('admin/admin-device-view');

        $id = \Yii::$app->request->get('id', 0);
        $draw = \Yii::$app->request->get('draw', 1);
        $start = \Yii::$app->request->get('start', 0);
        $length = \Yii::$app->request->get('length', 10);
        $deviceQuery = \common\models\AdminDevice::find()->alias('ad');
        if ($id > 0) {
            $deviceQuery->where(['ad.ad_admin_id' => $id]);
        } else {
            $deviceQuery->leftJoin(\common\models\Admin::tableName() . ' a', 'a.admin_id = ad.ad_admin_id')
                ->select(['ad.*', 'TRIM(CONCAT(TRIM(a.admin_firstname), " ", TRIM(a.admin_lastname))) AS admin_name']);
        }
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            if ($id > 0) {
                $deviceQuery->andWhere(['like', 'ad.ad_device_id', tep_db_input(tep_db_prepare_input($_GET['search']['value']))]);
            } else {
                $deviceQuery->andWhere(['OR',
                    ['like', 'ad.ad_device_id', tep_db_input(tep_db_prepare_input($_GET['search']['value']))],
                    ['like', 'TRIM(CONCAT(TRIM(a.admin_firstname), " ", TRIM(a.admin_lastname)))', tep_db_input(tep_db_prepare_input($_GET['search']['value']))]
                ]);
            }
        }
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 1:
                    $deviceQuery->orderBy('ad.ad_date_login ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                case 2:
                    $deviceQuery->orderBy('ad.ad_login_count ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                case 3:
                    $deviceQuery->orderBy('ad.ad_date_add ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                case 4:
                    $deviceQuery->orderBy('ad.ad_is_blocked ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                default:
                    $deviceQuery->orderBy('ad.ad_date_login DESC');
                break;
            }
        } else {
            $deviceQuery->orderBy('ad.ad_date_login DESC');
        }
        $numrows = $deviceQuery->count();
        if ($length > 0) {
            $deviceQuery->limit($length)->offset($start);
        }
        $deviceQuery = $deviceQuery->asArray(true)->all();
        $responseList = [];
        foreach ($deviceQuery as $deviceRecord) {
            if ($id > 0) {
                $responseList[] = array(
                    $deviceRecord['ad_device_id'],
                    $deviceRecord['ad_date_login'],
                    $deviceRecord['ad_login_count'],
                    $deviceRecord['ad_date_add'],
                    ($deviceRecord['ad_is_blocked'] == 0 ? TEXT_NO : TEXT_YES),
                    '<a class="btn btn-primary" is_blocked="' . (int)$deviceRecord['ad_is_blocked'] . '" onclick="return doAdminDeviceBlockToggle(\'' . $deviceRecord['ad_device_id'] . '\', this);">' . ($deviceRecord['ad_is_blocked'] == 0 ? TEXT_BUTTON_BLOCK : TEXT_BUTTON_UNBLOCK) . '</a>'
                );
            } else {
                $responseList[] = array(
                    $deviceRecord['admin_name'],
                    $deviceRecord['ad_device_id'],
                    $deviceRecord['ad_date_login'],
                    $deviceRecord['ad_login_count'],
                    $deviceRecord['ad_date_add'],
                    ($deviceRecord['ad_is_blocked'] == 0 ? TEXT_NO : TEXT_YES),
                    '<a class="btn btn-primary" is_blocked="' . (int)$deviceRecord['ad_is_blocked'] . '" onclick="return doAdminDeviceBlockToggle(\'' . $deviceRecord['ad_device_id'] . '\', this, \'' . (int)$deviceRecord['ad_admin_id'] . '\');">' . ($deviceRecord['ad_is_blocked'] == 0 ? TEXT_BUTTON_BLOCK : TEXT_BUTTON_UNBLOCK) . '</a>'
                );
            }
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $numrows,
            'recordsFiltered' => $numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    function actionAdminDeviceBlockToggle()
    {
        $this->layout = false;
        \common\helpers\Translation::init('admin/admin-device-view');

        $id = (int)\Yii::$app->request->post('id', 0);
        $device = trim(\Yii::$app->request->post('device', ''));
        $return = ['status' => 'error'];
        $deviceRecord = \common\models\AdminDevice::findOne(['ad_device_id' => $device, 'ad_admin_id' => $id]);
        if ($deviceRecord instanceof \common\models\AdminDevice) {
            $deviceRecord->ad_is_blocked = ((int)$deviceRecord->ad_is_blocked > 0 ? 0 : 1);
            try {
                $deviceRecord->save();
                if ($deviceRecord->ad_is_blocked > 0) {
                    \common\models\AdminLoginSession::deleteAll(['als_admin_id' => (int)$id, 'als_device_id' => trim($device)]);
                }
                $return = [
                    'status' => 'ok',
                    'button' => ($deviceRecord->ad_is_blocked == 0 ? TEXT_BUTTON_BLOCK : TEXT_BUTTON_UNBLOCK),
                    'blocked' => ($deviceRecord->ad_is_blocked == 0 ? TEXT_NO : TEXT_YES),
                    'is_blocked' => $deviceRecord->ad_is_blocked
                ];
            } catch (\Exception $exc) {}
        }
        echo json_encode($return);
    }

    function actionAdminSessionView()
    {
        \common\helpers\Translation::init('admin/admin-session-view');

        $this->selectedMenu = array('administrator', 'adminmembers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminmembers/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $admin_id = (int)\Yii::$app->request->get('admin_id');
        $adminRecord = \common\models\Admin::findOne($admin_id);
        if (!($adminRecord instanceof \common\models\Admin)) {
            die("Wrong data.");
        }
        $adminRecord = $adminRecord->toArray();

        $this->view->SessionTable = array(
            array(
                'title' => TABLE_HEADING_COMPUTER,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_DATE_EXPIRE,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_DATE_CREATE,
                'not_important' => 0
            ),
            array(
                'title' => '',
                'not_important' => 0
            )
        );

        return $this->render('admin-session-view', ['adminRecord' => $adminRecord]);
    }

    function actionAdminSessionViewList()
    {
        \common\helpers\Translation::init('admin/admin-session-view');

        $id = \Yii::$app->request->get('id', 0);
        $draw = \Yii::$app->request->get('draw', 1);
        $start = \Yii::$app->request->get('start', 0);
        $length = \Yii::$app->request->get('length', 10);
        $sessionQuery = \common\models\AdminLogin::find()
            ->where(['al_admin_id' => $id]);
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $sessionQuery->andWhere(['like', 'al_computer_id', tep_db_input(tep_db_prepare_input($_GET['search']['value']))]);
        }
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 1:
                    $sessionQuery->orderBy('al_expire ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                case 2:
                    $sessionQuery->orderBy('al_create ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                default:
                    $sessionQuery->orderBy('al_expire DESC');
                break;
            }
        } else {
            $sessionQuery->orderBy('al_expire DESC');
        }
        $numrows = $sessionQuery->count();
        if ($length > 0) {
            $sessionQuery->limit($length)->offset($start);
        }
        $sessionQuery = $sessionQuery->asArray(true)->all();
        $responseList = [];
        foreach ($sessionQuery as $sessionRecord) {
            $responseList[] = array(
                $sessionRecord['al_computer_id'],
                $sessionRecord['al_expire'],
                $sessionRecord['al_create'],
                '<a class="btn btn-primary" onclick="return doAdminSessionDelete(\'' . $sessionRecord['al_computer_id'] . '\', this);">' . TEXT_BUTTON_DELETE . '</a>'
            );
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $numrows,
            'recordsFiltered' => $numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    function actionAdminSessionDelete()
    {
        $this->layout = false;
        \common\helpers\Translation::init('admin/admin-session-view');

        $id = (int)\Yii::$app->request->post('id', 0);
        $computer = trim(\Yii::$app->request->post('computer', ''));
        $return = ['status' => 'error'];
        $sessionRecord = \common\models\AdminLogin::findOne(['al_computer_id' => $computer, 'al_admin_id' => $id]);
        if ($sessionRecord instanceof \common\models\AdminLogin) {
            try {
                $sessionRecord->delete();
                $return = ['status' => 'ok'];
            } catch (\Exception $exc) {}
        }
        echo json_encode($return);
    }

    function actionAdminLoginSessionView()
    {
        \common\helpers\Translation::init('admin/admin-login-session-view');

        $this->selectedMenu = array('administrator', 'adminmembers');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminmembers/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $admin_id = (int)\Yii::$app->request->get('admin_id');
        $adminRecord = \common\models\Admin::findOne($admin_id);
        if ($adminRecord instanceof \common\models\Admin) {
            $adminRecord = $adminRecord->toArray();
            $this->view->LoginSessionTable = array(
                array(
                    'title' => TABLE_HEADING_DEVICE,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_DATE_LOGIN,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_DATE_ACTIVITY,
                    'not_important' => 0
                ),
                array(
                    'title' => '',
                    'not_important' => 0
                )
            );
        } else {
            $adminRecord = ['admin_id' => 0];
            $this->view->LoginSessionTable = array(
                array(
                    'title' => TABLE_HEADING_MEMBER,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_DEVICE,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_DATE_LOGIN,
                    'not_important' => 0
                ),
                array(
                    'title' => TABLE_HEADING_DATE_ACTIVITY,
                    'not_important' => 0
                ),
                array(
                    'title' => '',
                    'not_important' => 0
                )
            );
        }

        return $this->render('admin-login-session-view', ['adminRecord' => $adminRecord]);
    }

    function actionAdminLoginSessionViewList()
    {
        \common\helpers\Translation::init('admin/admin-login-session-view');

        $id = \Yii::$app->request->get('id', 0);
        $draw = \Yii::$app->request->get('draw', 1);
        $start = \Yii::$app->request->get('start', 0);
        $length = \Yii::$app->request->get('length', 10);
        $loginSessionQuery = \common\models\AdminLoginSession::find()->alias('als');
        if ($id > 0) {
            $loginSessionQuery->where(['als.als_admin_id' => $id]);
        } else {
            $loginSessionQuery->leftJoin(\common\models\Admin::tableName() . ' a', 'a.admin_id = als.als_admin_id')
                ->select(['als.*', 'TRIM(CONCAT(TRIM(a.admin_firstname), " ", TRIM(a.admin_lastname))) AS admin_name']);
        }
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            if ($id > 0) {
                $loginSessionQuery->andWhere(['like', 'als.als_device_id', tep_db_input(tep_db_prepare_input($_GET['search']['value']))]);
            } else {
                $loginSessionQuery->andWhere(['OR',
                    ['like', 'als.als_device_id', tep_db_input(tep_db_prepare_input($_GET['search']['value']))],
                    ['like', 'TRIM(CONCAT(TRIM(a.admin_firstname), " ", TRIM(a.admin_lastname)))', tep_db_input(tep_db_prepare_input($_GET['search']['value']))]
                ]);
            }
        }
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 1:
                    $loginSessionQuery->orderBy('als.als_date_login ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                case 2:
                    $loginSessionQuery->orderBy('als.als_date_activity ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                default:
                    $loginSessionQuery->orderBy('als.als_date_activity DESC');
                break;
            }
        } else {
            $loginSessionQuery->orderBy('als.als_date_activity DESC');
        }
        $numrows = $loginSessionQuery->count();
        if ($length > 0) {
            $loginSessionQuery->limit($length)->offset($start);
        }
        $loginSessionQuery = $loginSessionQuery->asArray(true)->all();
        $responseList = [];
        foreach ($loginSessionQuery as $loginSessionRecord) {
            if ($id > 0) {
                $responseList[] = array(
                    $loginSessionRecord['als_device_id'],
                    $loginSessionRecord['als_date_login'],
                    $loginSessionRecord['als_date_activity'],
                    '<a class="btn btn-primary" onclick="return doAdminLoginSessionDelete(\'' . $loginSessionRecord['als_device_id'] . '\', this);">' . TEXT_BUTTON_DELETE . '</a>'
                );
            } else {
                $responseList[] = array(
                    $loginSessionRecord['admin_name'],
                    $loginSessionRecord['als_device_id'],
                    $loginSessionRecord['als_date_login'],
                    $loginSessionRecord['als_date_activity'],
                    '<a class="btn btn-primary" onclick="return doAdminLoginSessionDelete(\'' . $loginSessionRecord['als_device_id'] . '\', this, \'' . (int)$loginSessionRecord['als_admin_id'] . '\');">' . TEXT_BUTTON_DELETE . '</a>'
                );
            }
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $numrows,
            'recordsFiltered' => $numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    function actionAdminLoginSessionDelete()
    {
        $this->layout = false;
        \common\helpers\Translation::init('admin/admin-login-session-view');

        $id = (int)\Yii::$app->request->post('id', 0);
        $device = trim(\Yii::$app->request->post('device', ''));
        $return = ['status' => 'error'];
        $loginSessionRecord = \common\models\AdminLoginSession::findOne(['als_device_id' => $device, 'als_admin_id' => $id]);
        if ($loginSessionRecord instanceof \common\models\AdminLoginSession) {
            try {
                $loginSessionRecord->delete();
                $return = ['status' => 'ok'];
            } catch (\Exception $exc) {}
        }
        echo json_encode($return);
    }
}