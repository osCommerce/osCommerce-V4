<?php

namespace backend\controllers;

use common\helpers\Api;
use Yii;
use Yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * default controller to handle user requests.
 */
class DepartmentsController extends Sceleton {

    public $acl = ['BOX_HEADING_DEPARTMENTS', 'BOX_HEADING_DEPARTMENTS'];
    
    public function actionIndex() {
        $this->selectedMenu = array('departments', 'departments');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('departments/index'), 'title' => BOX_HEADING_DEPARTMENTS);
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('departments/new') . '" class="create_item"><i class="icon-file-text"></i>' . TEXT_CREATE_NEW_DEPARTMENT . '</a>';
        $this->view->headingTitle = BOX_HEADING_DEPARTMENTS;

        $this->view->departmentsTable = array(
            array(
                'title' => DEPARTMENT_DATE_ADDED,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_STORE_NAME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_HTTP_SERVER,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_FULLNAME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_EMAIL,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 0
            ),
            array(
                'title' => DEPARTMENT_KEEP_ALIVE,
                'not_important' => 0
            ),
        );

        $messageStack = \Yii::$container->get('message_stack');
        if (isset($messageStack->size) && $messageStack->size > 0) {
            $this->view->errorMessage = $messageStack->output(true);
            $this->view->errorMessageType = $messageStack->messageType;
        }

        $this->view->inactive = (int)Yii::$app->request->get('inactive');
        $dID = Yii::$app->request->get('dID', 0);
        return $this->render('index', array('dID' => $dID));
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and ( departments_store_name like '%" . tep_db_input($keywords) . "%' "
                    . " or departments_http_server like '%" . tep_db_input($keywords) . "%' "
                    . " or departments_firstname like '%" . tep_db_input($keywords) . "%' "
                    . " or departments_lastname like '%" . tep_db_input($keywords) . "%' "
                    . " or departments_email_address like '%" . tep_db_input($keywords) . "%' )";
        }
        if ( isset($output['inactive']) && $output['inactive'] == 1 ){

        } else {
            $search .= " and departments_status=1 and locked=0 ";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "departments_created " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 1:
                    $orderBy = "departments_store_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 2:
                    $orderBy = "departments_http_server " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 3:
                    $orderBy = "departments_firstname " . tep_db_prepare_input($_GET['order'][0]['dir']) . ", departments_lastname " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 4:
                    $orderBy = "departments_email_address " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 5:
                    $orderBy = "departments_status " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "departments_http_server";
                    break;
            }
        } else {
            $orderBy = "locked asc, departments_status desc, departments_sort_order, departments_id";
        }

        $departments_query_raw = "select * from " . TABLE_DEPARTMENTS . " where 1 " . $search . " order by " . $orderBy;
        $departments_split = new \splitPageResults($current_page_number, $length, $departments_query_raw, $departments_query_numrows);
        $departments_query = tep_db_query($departments_query_raw);

        while ($departments = tep_db_fetch_array($departments_query)) {
            if ((int) $departments['departments_status'] > 0) {
                $status = '<input type="checkbox" name="check_status" class="check_on_off" value="' . $departments['departments_id'] . '" checked /></div>';
            } else {
                $status = '<input type="checkbox" name="check_status" class="check_on_off" value="' . $departments['departments_id'] . '" /></div>';
            }

            if ((int) $departments['keep_alive'] > 0) {
                $keep = '<input type="checkbox" name="keep_alive" class="keep_on_off" value="' . $departments['departments_id'] . '" checked /></div>';
            } else {
                $keep = '<input type="checkbox" name="keep_alive" class="keep_on_off" value="' . $departments['departments_id'] . '" /></div>';
            }
            //$begin = '<div class="handle_dep_list"><div class="module_title">';
            //$end = '</div></div>';
            if ($departments['locked'] == 1) {
                //$begin = '<div class="handle_dep_list"><div class="module_title dis_module">';
                $status = '&nbsp;';
            }
            /*$responseList[] = array(
                $begin . $departments['departments_store_name'] . tep_draw_hidden_field('id', $departments['departments_id'], 'class="cell_identify"') . $end,
                $begin . $departments['departments_http_server'] . $end,
                $begin . $departments['departments_firstname'] . ' ' . $departments['departments_lastname'] . $end,
                $begin . $departments['departments_email_address'] . $end,
                $begin . $status . $end,
            );*/
            $responseList[] = array(
                //'DT_RowClass' => ($departments['departments_status']>0 && $departments['locked'] == 0 ?'':' hided-page'),
                '<div class="'.($departments['departments_status']>0 && $departments['locked'] == 0 ?'':' hide-page').'"><div class="cat_name cat_name_attr cat_no_folder'.($departments['departments_status']>0 && $departments['locked'] == 0 ?'':' hide-page').'">'.'<input type="checkbox" class="uniform"> ' . $departments['departments_created'] . tep_draw_hidden_field('id', $departments['departments_id'], 'class="cell_identify"').tep_draw_hidden_field('type', 'item', 'class="cell_type"').'</div></div>',
                '<div class="'.($departments['departments_status']>0 && $departments['locked'] == 0 ?'':' hide-page').'"><div class="cat_name cat_name_attr cat_no_folder'.($departments['departments_status']>0 && $departments['locked'] == 0 ?'':' hide-page').'">'.$departments['departments_store_name'] . '</div></div>',
                '<div class="'.($departments['departments_status']>0 && $departments['locked'] == 0 ?'':' hide-page').'">'.$departments['departments_http_server'].'</div>',
                '<div class="'.($departments['departments_status']>0 && $departments['locked'] == 0 ?'':' hide-page').'">'.$departments['departments_firstname'] . ' ' . $departments['departments_lastname'].'</div>',
                '<div class="'.($departments['departments_status']>0 && $departments['locked'] == 0 ?'':' hide-page').'">'.$departments['departments_email_address'].'</div>',
                $status,
                $keep,
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $departments_query_numrows,
            'recordsFiltered' => $departments_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionView() {
        global $login_id;
        \common\helpers\Translation::init('admin/departments');

        $departments_id = Yii::$app->request->post('dID', 0);
        $this->layout = false;

        $departments = tep_db_fetch_array(tep_db_query("select * from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $departments_id . "'"));
        $dInfo = new \objectInfo($departments, false);

        if ($dInfo->departments_id > 0) {
            $multiplatform = '';
            if ( count(\common\classes\department::getCatalogAssignList())>1 ) {
                $multiplatform .= '<a href="' . Yii::$app->urlManager->createUrl(['departments/edit-catalog', 'id' => $departments_id]) . '" class="btn btn-edit btn-process-order js-open-tree-popup">'.BUTTON_ASSIGN_CATEGORIES_PRODUCTS.'</a>';
            }

            echo '<div class="or_box_head">' . $dInfo->departments_store_name . '</div>';
            echo '<div class="btn-toolbar btn-toolbar-order">';
            if ($departments['locked'] == 0) {
                echo '<a href="' . Yii::$app->urlManager->createUrl(['departments/edit', 'dID' => $departments_id]) . '"><button class="btn btn-edit btn-primary btn-process-order ">' . IMAGE_EDIT . '</button></a>';
                echo $multiplatform;
            }
            echo '<button class="btn btn-delete btn-no-margin btn-process-order" onclick="deleteItemConfirm(' . $departments_id . ')">' . IMAGE_DELETE . '</button>';
            
            if (\common\helpers\Acl::checkExtensionAllowed('SoapServer', 'allowed')) {
                if ( \common\helpers\Acl::rule(['BOX_HEADING_FRONENDS', 'BOX_SOAP_SERVER_SETTINGS']) ) {
                    echo  '<a href="' . \yii\helpers\Url::toRoute(['departments/soap-server-configure', 'id' => $dInfo->departments_id]) . '"><button class="btn btn-edit btn-primary btn-process-order ">Configure department SOAP server</button></a>';
                }
            }
            
            if (\common\helpers\Acl::checkExtensionAllowed('RestServer', 'allowed')) {
                if ( \common\helpers\Acl::rule(['BOX_HEADING_FRONENDS', 'BOX_REST_SERVER_SETTINGS']) ) {
                    echo  '<a href="' . \yii\helpers\Url::toRoute(['departments/rest-server-configure', 'id' => $dInfo->departments_id]) . '"><button class="btn btn-edit btn-primary btn-process-order ">Configure department rest server</button></a>';
                }
            }
            
            if ($dInfo->departments_status) {
                $check_admin = tep_db_fetch_array(tep_db_query("SELECT admin_email_address FROM ".TABLE_ADMIN." WHERE admin_id='".(int)$login_id."'"));
                tep_db_close();

                try {
                    if (tep_db_connect($dInfo->departments_db_server_host, $dInfo->departments_db_server_username, $dInfo->departments_db_server_password, $dInfo->departments_db_database)) {
                        $admin = tep_db_fetch_array(tep_db_query("select admin_id, admin_password from " . TABLE_ADMIN . " where admin_email_address = '" . $check_admin['admin_email_address'] . "' order by admin_id limit 1"));
                        if ($admin) {
                            echo '<div align="center"><a href="http://' . $dInfo->departments_http_server . $dInfo->departments_http_catalog . DIR_WS_HTTP_ADMIN_CATALOG . FILENAME_LOGIN . '?uid=' . urlencode($admin['admin_id']) . '&tr=' . urlencode($admin['admin_password']) . '" onclick="return confirm(\'' . sprintf(TEXT_YOU_ARE_GOING_TO_LOGIN_AS_DEPARTMENT, $dInfo->departments_store_name) . '\')"><input type="button" value="' . IMAGE_LOGIN . '" class="btn btn-primary"></a></div>';
                        }
                        tep_db_close();
                    }
                } catch (ErrorException $e) {
                    echo '<div align="center">' . TEXT_UNABLE_CONNECT_DEPARTMENTS_DB . '</div>';
                }

                tep_db_connect() or die('Unable to connect to database server!');
            }

            echo '<br>' . TEXT_DATE_DEPARTMENTS_CREATED . ' ' . \common\helpers\Date::date_short($dInfo->departments_created);
            if ($dInfo->departments_modified) {
                echo '<br>' . TEXT_DATE_DEPARTMENTS_LAST_MODIFIED . ' ' . \common\helpers\Date::date_short($dInfo->departments_modified);
            }

            if (\common\helpers\Acl::checkExtensionAllowed('NetSuite') ) {
                echo \common\extensions\NetSuite\helpers\NetSuiteHelper::departmentLink($dInfo);
            }

            echo '</div>';
        }
    }

    public function actionEdit() {
        \common\helpers\Translation::init('admin/departments');

        $departments_id = Yii::$app->request->get('dID', 0);


        $departments_query = tep_db_query("select * from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $departments_id . "'");
        $departments = tep_db_fetch_array($departments_query);
        
        $HEADING_TITLE = defined('HEADING_TITLE_EDIT_DEPARTMENT')?sprintf(HEADING_TITLE_EDIT_DEPARTMENT,$departments['departments_store_name']):HEADING_TITLE;
        
        $departments['project_codes'] = array_map('trim', preg_split('/[;,]/',$departments['project_code'],-1,PREG_SPLIT_NO_EMPTY));
        $dInfo = new \objectInfo($departments);
        /*
          $dInfo->templates_array = array();
          $template_query = tep_db_query("select template_id, template_name from " . TABLE_TEMPLATE . " order by template_name");
          while ($template = tep_db_fetch_array($template_query)) {
          $dInfo->templates_array[$template['template_name']] = $template['template_name'];
          }
         */
        $dInfo->packages_array = array('' => TEXT_NONE);
        if ( defined('TABLE_PACKAGES') && tep_db_table_exists('TABLE_PACKAGES') ) {
            $packages_query = tep_db_query("select packages_id, packages_title from " . TABLE_PACKAGES . " order by sort_order, packages_title");
            while ($packages = tep_db_fetch_array($packages_query)) {
                $dInfo->packages_array[$packages['packages_id']] = $packages['packages_title'];
            }
        }

        $dInfo->api = [
            'client_statuses' => [],
            'local_statuses' => [0=>''],
            'local_statuses_wo_create' => [0=>''],
            'client_create_status_map' => [],
            'order_status_mapped' => [],
        ];


        $shareDepartmentsList = ArrayHelper::map(\common\classes\department::getList(false),'id','text');
        $shareDepartmentsListSelected = [];
        $_share_map = json_decode(Api::getDepartmentServerKeyValue((int)$departments_id, 'catalog/createShare'),true);
        if ( !is_array($_share_map) ) {
            $_share_map = [
                'all' => true,
                'selected' => []
            ];
            if ( (int)$departments_id ) $_share_map['selected'][] = (int)$departments_id;
        }

        $dInfo->api['createShareAll'] = $_share_map['all']?'all':'selected';
        $dInfo->api['shareDepartmentsList'] = [
            'items' => $shareDepartmentsList,
            'selected' => $shareDepartmentsListSelected,
            'options' => [
                'class' => 'assign-multi-checkbox',
                'multiple' => 'multiple',
                'data-role' => 'multiselect',
                'options' => [],
            ]
        ];
        if ( (int)$departments_id ) {
            $dInfo->api['shareDepartmentsList']['selected'][] = (int)$departments_id;
            $dInfo->api['shareDepartmentsList']['options']['options'][(int)$departments_id]['disabled'] = 'disabled';
        }

        $currentGroup = '';
        foreach( \common\helpers\Order::getStatusesGrouped(true) as $orderStatusVariant ) {
            if ( strpos($orderStatusVariant['id'],'group_')===0 ) {
                $currentGroup = $orderStatusVariant['text'];
                $dInfo->api['local_statuses'][$currentGroup]['new_in_'.$orderStatusVariant['id']] = TEXT_API_SYNCHRONIZATION_ORDER_STATUS_CREATE_CLIENT_STATUS;
                continue;
            }
            $dInfo->api['local_statuses'][$currentGroup][str_replace('status_','',$orderStatusVariant['id'])]
                = str_replace('&nbsp;','',$orderStatusVariant['text']);
            $dInfo->api['local_statuses_wo_create'][$currentGroup][str_replace('status_','',$orderStatusVariant['id'])]
                = str_replace('&nbsp;','',$orderStatusVariant['text']);
        }

        if ( (int)$departments_id ) {
            $dInfo->api['department-service-url'] = tep_catalog_href_link('api-department/service','','SSL');

            $_formula = json_decode($dInfo->api_outgoing_price_formula,true);
            if ( is_array($_formula) && !empty($_formula['text']) ) {
                $dInfo->api_outgoing_price_formula_text = $_formula['text'];
            }

            $client_status_map = json_decode(Api::getDepartmentServerKeyValue((int)$departments_id, 'clientOrder/StatusesMapping'),true);
            $dInfo->api['client_create_status_map'] = json_decode(Api::getDepartmentServerKeyValue((int)$departments_id, 'clientOrder/StatusesNeedCreate'),true);
            $_keyValue = Api::getDepartmentServerKeyValue($departments_id, 'clientOrder/Statuses');
            if ( !empty($_keyValue) ) {
                $extract_value = json_decode(base64_decode($_keyValue),true);
                if ( is_array($extract_value) ) {
                    $last_group_id = -1;
                    foreach ($extract_value as $status) {
                        if ( $status['group_id']!=$last_group_id ) {
                            $last_group_id = $status['group_id'];
                            $group_name = current($status['group_names']);
                            if ( isset($status['group_names'][\common\classes\language::get_code()]) ) {
                                $group_name = $status['group_names'][\common\classes\language::get_code()];
                            }
                            $dInfo->api['client_statuses'][] = [
                                'id' => 'group_'.$status['group_id'],
                                '_id' => $status['group_id'],
                                'group_id' => $status['group_id'],
                                'text' => $group_name,
                            ];
                        }
                        $status_name = current($status['names']);
                        if ( isset($status['names'][\common\classes\language::get_code()]) ) {
                            $status_name = $status['names'][\common\classes\language::get_code()];
                        }
                        $dInfo->api['client_statuses'][] = [
                            'id' => 'status_'.$status['id'],
                            '_id' => $status['id'],
                            'group_id' => $status['group_id'],
                            'text' => $status_name,
                        ];
                        if ( is_array($client_status_map) ) {
                            $dInfo->api['order_status_mapped'][$status['id']] = isset($client_status_map[$status['id']]) ? $client_status_map[$status['id']] : 0;
                        }else {
                            $dInfo->api['order_status_mapped'][$status['id']] = isset($status['external_status_id']) ? $status['external_status_id'] : 0;
                        }
                    }
                }
            }
            $dInfo->apiProductFlags = Api::productFlags();
            $dInfo->api_products_update_custom_flags = false;
            $dInfo->api_products_update_custom = [];
            $dInfo->api_products_update_custom = json_decode(Api::getDepartmentServerKeyValue((int)$departments_id, 'product/UpdateDataFlag'),true);
            foreach ($dInfo->apiProductFlags as $checkFlag) {
                if ( isset($checkFlag['server']) && isset($dInfo->api_products_update_custom[$checkFlag['server']]) && $dInfo->api_products_update_custom[$checkFlag['server']] ) {
                    $dInfo->api_products_update_custom_flags = true;
                    break;
                }
                if ( isset($checkFlag['server_own']) && isset($dInfo->api_products_update_custom[$checkFlag['server_own']]) && $dInfo->api_products_update_custom[$checkFlag['server_own']] ) {
                    $dInfo->api_products_update_custom_flags = true;
                    break;
                }
            }
        }

        $messageStack = \Yii::$container->get('message_stack');
        if ($messageStack->size > 0) {
            $this->view->errorMessage = $messageStack->output(true);
            $this->view->errorMessageType = $messageStack->messageType;
        }

        $this->selectedMenu = array('departments', 'departments');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('departments/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '';
        $this->view->headingTitle = $HEADING_TITLE;
        $this->view->showSAP = class_exists('\common\classes\SapClient');
        
        return $this->render('edit', array('dInfo' => $dInfo));
    }

    public function actionUpdate() {
        $messageStack = \Yii::$container->get('message_stack');
        \common\helpers\Translation::init('admin/departments');

        $departments_id = tep_db_prepare_input(Yii::$app->request->post('dID'));
        if (!($departments_id > 0)) {
            if (Yii::$app->request->isAjax) {
                echo 'Wrong department ID!';
                exit;
            } else {
                $messageStack->add_session('Wrong department ID!', 'error');
                return $this->redirect(Yii::$app->urlManager->createUrl('departments/index'));
            }
        }
        $departments_store_name = tep_db_prepare_input(Yii::$app->request->post('departments_store_name'));
        $departments_http_server = tep_db_prepare_input(Yii::$app->request->post('departments_http_server'));
        $departments_https_server = tep_db_prepare_input(Yii::$app->request->post('departments_https_server'));
        $departments_enable_ssl = (int)Yii::$app->request->post('departments_enable_ssl');
        $departments_http_catalog = tep_db_prepare_input(Yii::$app->request->post('departments_http_catalog'));
        $departments_https_catalog = tep_db_prepare_input(Yii::$app->request->post('departments_https_catalog'));
//    $departments_design_template_name = tep_db_prepare_input(Yii::$app->request->post('departments_design_template_name'));
//    $departments_template_monster_template_id = tep_db_prepare_input(Yii::$app->request->post('departments_template_monster_template_id'));
//    $departments_custom_design_requirements = tep_db_prepare_input(Yii::$app->request->post('departments_custom_design_requirements'));

        $departments_monthly_price = tep_db_prepare_input(Yii::$app->request->post('departments_monthly_price'));
        $departments_setup_price = tep_db_prepare_input(Yii::$app->request->post('departments_setup_price'));
        $departments_commission_percentage = tep_db_prepare_input(Yii::$app->request->post('departments_commission_percentage'));

        $departments_already_registered_domain = tep_db_prepare_input(Yii::$app->request->post('departments_already_registered_domain'));
        $departments_registered_uk_vat = tep_db_prepare_input(Yii::$app->request->post('departments_registered_uk_vat'));
        $departments_custom_design_artwork = tep_db_prepare_input(Yii::$app->request->post('departments_custom_design_artwork'));

        $departments_firstname = tep_db_prepare_input(Yii::$app->request->post('departments_firstname'));
        $departments_lastname = tep_db_prepare_input(Yii::$app->request->post('departments_lastname'));
        $departments_email_address = tep_db_prepare_input(Yii::$app->request->post('departments_email_address'));
        $departments_telephone = tep_db_prepare_input(Yii::$app->request->post('departments_telephone'));
        $departments_street_address = tep_db_prepare_input(Yii::$app->request->post('departments_street_address'));
        $departments_suburb = tep_db_prepare_input(Yii::$app->request->post('departments_suburb'));
        $departments_postcode = tep_db_prepare_input(Yii::$app->request->post('departments_postcode'));
        $departments_city = tep_db_prepare_input(Yii::$app->request->post('departments_city'));
        $departments_state = tep_db_prepare_input(Yii::$app->request->post('departments_state'));
        $departments_zone_id = tep_db_prepare_input(Yii::$app->request->post('departments_zone_id'));
        $departments_country_id = tep_db_prepare_input(Yii::$app->request->post('departments_country_id'));

        $format_id = \common\helpers\Address::get_address_format_id($departments_country_id);
        $address = array();
        $address['firstname'] = $departments_firstname;
        $address['lastname'] = $departments_lastname;
        $address['street_address'] = $departments_street_address;
        $address['suburb'] = $departments_suburb;
        $address['city'] = $departments_city;
        $address['postcode'] = $departments_postcode;
        $address['state'] = $departments_state;
        $address['zone_id'] = $departments_zone_id;
        $address['country_id'] = $departments_country_id;
        $departments_address = \common\helpers\Address::address_format($format_id, $address, 0, '', "\n");

        $departments_ftp_type = tep_db_prepare_input(Yii::$app->request->post('departments_ftp_type'));
        $departments_ftp_host = tep_db_prepare_input(Yii::$app->request->post('departments_ftp_host'));
        $departments_ftp_port = tep_db_prepare_input(Yii::$app->request->post('departments_ftp_port'));
        $departments_ftp_path = tep_db_prepare_input(Yii::$app->request->post('departments_ftp_path'));
        $departments_ftp_username = tep_db_prepare_input(Yii::$app->request->post('departments_ftp_username'));
        $departments_ftp_password = tep_db_prepare_input(Yii::$app->request->post('departments_ftp_password'));
        $departments_ftp_pasv = tep_db_prepare_input(Yii::$app->request->post('departments_ftp_pasv'));

        $departments_db_server_host = tep_db_prepare_input(Yii::$app->request->post('departments_db_server_host'));
        $departments_db_server_username = tep_db_prepare_input(Yii::$app->request->post('departments_db_server_username'));
        $departments_db_server_password = tep_db_prepare_input(Yii::$app->request->post('departments_db_server_password'));
        $departments_db_database = tep_db_prepare_input(Yii::$app->request->post('departments_db_database'));
        $departments_db_use_pconnect = tep_db_prepare_input(Yii::$app->request->post('departments_db_use_pconnect'));
        $departments_db_store_sessions = tep_db_prepare_input(Yii::$app->request->post('departments_db_store_sessions'));

        $api_key = tep_db_prepare_input(Yii::$app->request->post('api_key',''));
        $api_categories_allow_create = Yii::$app->request->post('api_categories_allow_create','0');
        $api_categories_allow_update = Yii::$app->request->post('api_categories_allow_update','0');
        $api_products_allow_create = Yii::$app->request->post('api_products_allow_create','0');
        $api_products_allow_update = Yii::$app->request->post('api_products_allow_update','0');
        $api_products_allow_remove_owned = Yii::$app->request->post('api_products_allow_remove_owned','0');
        $api_outgoing_price_formula = Yii::$app->request->post('api_outgoing_price_formula','');
        $api_outgoing_price_discount = Yii::$app->request->post('api_outgoing_price_discount','0');
        $api_outgoing_price_surcharge = Yii::$app->request->post('api_outgoing_price_surcharge','0');
        $api_outgoing_price_margin = Yii::$app->request->post('api_outgoing_price_margin','0');

        $departments_status = tep_db_prepare_input(Yii::$app->request->post('departments_status'));
        $packages_id = tep_db_prepare_input(Yii::$app->request->post('packages_id'));

        $project_code = '';
        $project_codes = tep_db_prepare_input(Yii::$app->request->post('project_code',[]));
        if ( !is_array($project_codes) ) $project_codes = [];
        $project_codes = array_flip($project_codes);
        foreach( array_keys($project_codes) as $_project_code ) {
            if ( !empty($_project_code) ) $project_code .= $_project_code.';';
        }
        $project_code = rtrim($project_code,';');

        $sql_data_array = array('departments_store_name' => $departments_store_name,
            'project_code' => $project_code,
            'departments_http_server' => $departments_http_server,
            'departments_https_server' => $departments_https_server,
            'departments_enable_ssl' => $departments_enable_ssl,
            'departments_http_catalog' => $departments_http_catalog,
            'departments_https_catalog' => $departments_https_catalog,
//                            'departments_design_template_name' => $departments_design_template_name,
//                            'departments_template_monster_template_id' => $departments_template_monster_template_id,
//                            'departments_custom_design_requirements' => $departments_custom_design_requirements,
//            'departments_monthly_price' => $departments_monthly_price,
//            'departments_setup_price' => $departments_setup_price,
//            'departments_commission_percentage' => $departments_commission_percentage,
            'departments_already_registered_domain' => $departments_already_registered_domain,
            'departments_registered_uk_vat' => $departments_registered_uk_vat,
            'departments_custom_design_artwork' => $departments_custom_design_artwork,
            'departments_firstname' => $departments_firstname,
            'departments_lastname' => $departments_lastname,
            'departments_email_address' => $departments_email_address,
            'departments_telephone' => $departments_telephone,
            'departments_street_address' => $departments_street_address,
            'departments_suburb' => $departments_suburb,
            'departments_postcode' => $departments_postcode,
            'departments_city' => $departments_city,
            'departments_state' => $departments_state,
            'departments_zone_id' => $departments_zone_id,
            'departments_country_id' => $departments_country_id,
            'departments_address' => $departments_address,
            'departments_ftp_type' => $departments_ftp_type,
            'departments_ftp_host' => $departments_ftp_host,
            'departments_ftp_port' => $departments_ftp_port,
            'departments_ftp_path' => $departments_ftp_path,
            'departments_ftp_username' => $departments_ftp_username,
            'departments_ftp_password' => $departments_ftp_password,
            'departments_ftp_pasv' => $departments_ftp_pasv,
            'departments_db_server_host' => $departments_db_server_host,
            'departments_db_server_username' => $departments_db_server_username,
            'departments_db_server_password' => $departments_db_server_password,
            'departments_db_database' => $departments_db_database,
            'departments_db_use_pconnect' => $departments_db_use_pconnect,
            'departments_db_store_sessions' => $departments_db_store_sessions,
            'api_key' => $api_key,
            'api_categories_allow_create' => $api_categories_allow_create,
            'api_categories_allow_update' => $api_categories_allow_update,
            'api_products_allow_create' => $api_products_allow_create,
            'api_products_allow_update' => $api_products_allow_update,
            //'api_products_allow_remove_owned' => $api_products_allow_remove_owned,
            'api_outgoing_price_formula' => $api_outgoing_price_formula,
            'api_outgoing_price_discount' => $api_outgoing_price_discount,
            'api_outgoing_price_surcharge' => $api_outgoing_price_surcharge,
            'api_outgoing_price_margin' => $api_outgoing_price_margin,
            'packages_id' => $packages_id,
            'departments_status' => $departments_status);

        $sql_data_array = array_merge($sql_data_array, array('departments_modified' => 'now()'));
        tep_db_perform(TABLE_DEPARTMENTS, $sql_data_array, 'update', "departments_id = '" . (int) $departments_id . "'");


        // {{ API
        $api = Yii::$app->request->post('api',[]);
        if ( (int)$departments_id && isset($api['client_status_map']) && is_array($api['client_status_map']) ) {
            $client_status_map = tep_db_prepare_input($api['client_status_map']);
            Api::setDepartmentServerKeyValue((int)$departments_id, 'clientOrder/StatusesMapping', json_encode($client_status_map) );
        }
        if ( (int)$departments_id && isset($api['create_on_client']) && is_array($api['create_on_client']) ) {
            $client_create_status_map = tep_db_prepare_input($api['create_on_client']);
            Api::setDepartmentServerKeyValue((int)$departments_id, 'clientOrder/StatusesNeedCreate', json_encode($client_create_status_map) );
        }
        if ( (int)$departments_id ) {
            $api_products_update_custom = [];
            if (Yii::$app->request->post('api_products_update_custom_flags', false)) {
                $api_products_update_custom = Yii::$app->request->post('api_products_update_custom', []);
                if (!is_array($api_products_update_custom)) $api_products_update_custom = [];
                foreach (Api::productFlags() as $flagInfo) {
                    if (!empty($flagInfo['server'])) {
                        $_key = $flagInfo['server'];
                        $api_products_update_custom[$_key] = isset($api_products_update_custom[$_key]);
                    }
                    if (!empty($flagInfo['server_own'])) {
                        $_key = $flagInfo['server_own'];
                        $api_products_update_custom[$_key] = isset($api_products_update_custom[$_key]);
                    }
                }
            }
            Api::setDepartmentServerKeyValue((int)$departments_id, 'product/UpdateDataFlag', json_encode($api_products_update_custom));

            $api_create_share = [
                'all' => $api['createShareAll'] == 'all',
                'selected' => (isset($api['createShare']) && is_array($api['createShare'])) ? array_map('intval', $api['createShare']) : [],
            ];
            if (!in_array((int)$departments_id, $api_create_share['selected'])) {
                $api_create_share['selected'][] = (int)$departments_id;
            }
            Api::setDepartmentServerKeyValue((int)$departments_id, 'catalog/createShare', json_encode($api_create_share));
        }
        // }} API

        if ( defined('TABLE_DEPARTMENTS_TO_FEATURES') && tep_db_table_exists(TABLE_DEPARTMENTS_TO_FEATURES) ) {
            tep_db_query("delete from " . TABLE_DEPARTMENTS_TO_FEATURES . " where departments_id = '" . (int)$departments_id . "'");
            foreach (Yii::$app->request->post('features', array()) as $features_id => $enabled) {
                if ($enabled == '1') {
                    tep_db_query("insert into " . TABLE_DEPARTMENTS_TO_FEATURES . " set departments_id = '" . (int)$departments_id . "', features_id = '" . (int)$features_id . "'");
                }
            }
        }

        $sql_query_dump_array = array();
        $features_dump_array = tep_db_get_table_dump(TABLE_FEATURES, true, true);
        $sql_query_dump_array = array_merge($sql_query_dump_array, $features_dump_array);
        $features_dump_array = tep_db_get_table_dump(TABLE_FEATURES_TYPES, true, true);
        $sql_query_dump_array = array_merge($sql_query_dump_array, $features_dump_array);
        $features_dump_array = tep_db_get_table_dump(TABLE_DEPARTMENTS_TO_FEATURES, true, false);
        $sql_query_dump_array = array_merge($sql_query_dump_array, $features_dump_array);

        if ( count($sql_query_dump_array)>0 || Yii::$app->request->post('features', false)!==false ) {
            tep_db_close();
            try {
                if (!tep_db_connect($departments_db_server_host, $departments_db_server_username, $departments_db_server_password, $departments_db_database)) {
                    tep_db_connect();
                    if (Yii::$app->request->isAjax) {
                        echo TEXT_UNABLE_CONNECT_DEPARTMENTS_DB;
                        exit;
                    } else {
                        $messageStack->add_session(TEXT_UNABLE_CONNECT_DEPARTMENTS_DB, 'error');
                        return $this->redirect(Yii::$app->urlManager->createUrl(['departments/edit', 'dID' => $departments_id]));
                    }
                } else {
                    foreach ($sql_query_dump_array as $sql_query) {
                        tep_db_query($sql_query);
                    }
                    tep_db_query("delete from " . TABLE_DEPARTMENTS_TO_FEATURES . " where departments_id = '" . (int)$departments_id . "'");
                    foreach (Yii::$app->request->post('features', array()) as $features_id => $enabled) {
                        if ($enabled == '1') {
                            tep_db_query("insert into " . TABLE_DEPARTMENTS_TO_FEATURES . " set departments_id = '" . (int)$departments_id . "', features_id = '" . (int)$features_id . "'");
                        }
                    }
                    tep_db_close();
                }
            } catch (ErrorException $e) {
                tep_db_connect();
                if (Yii::$app->request->isAjax) {
                    return TEXT_UNABLE_CONNECT_DEPARTMENTS_DB;
                    exit;
                } else {
                    $messageStack->add_session(TEXT_UNABLE_CONNECT_DEPARTMENTS_DB, 'error');
                    return $this->redirect(Yii::$app->urlManager->createUrl(['departments/edit', 'dID' => $departments_id]));
                }
            }
            tep_db_connect() or die('Unable to connect to database server!');
        }

        $messageStack->add_session('The department has been successfully updated!', 'success');
        if (Yii::$app->request->isAjax) {
            echo 'OK';
            exit;
        } else {
            return $this->redirect(Yii::$app->urlManager->createUrl('departments/index'));
        }
    }

    function actionNew() {
        \common\helpers\Translation::init('admin/departments');

        $this->selectedMenu = array('departments', 'departments');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('departments/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '';
        $this->view->headingTitle = HEADING_TITLE;

        $dInfo = new \objectInfo(array(
            'project_codes' => [],
        ));
        /*
          $dInfo->templates_array = array();
          $template_query = tep_db_query("select template_id, template_name from " . TABLE_TEMPLATE . " order by template_name");
          while ($template = tep_db_fetch_array($template_query)) {
          $dInfo->templates_array[$template['template_name']] = $template['template_name'];
          }
         */
        $messageStack = \Yii::$container->get('message_stack');
        if ($messageStack->size() > 0) {
            $this->view->errorMessage = $messageStack->output(true);
            $this->view->errorMessageType = $messageStack->messageType;
        }

        return $this->render('new', array('dInfo' => $dInfo));
    }

    public function actionCreate() {
        $next_id_query = tep_db_query("select max(departments_id) as departments_id from " . TABLE_DEPARTMENTS . " where 1");
        $next_id = tep_db_fetch_array($next_id_query);
        $insert_id = $next_id['departments_id'] + 1;
        
        $user = CPANEL_USER_PREFIX;
        $user .= str_pad($insert_id, (8 - strlen($user)), '0', STR_PAD_LEFT);
        /*$user = tep_db_prepare_input(Yii::$app->request->post('username'));
        if (strlen($user) > 8) {
            $user = substr($user, 0, 8);
        }*/
        $password = \common\helpers\Password::create_random_value(12);
        
        $departments_store_name = tep_db_prepare_input(Yii::$app->request->post('departments_store_name'));
        $departments_http_server_short = tep_db_prepare_input(Yii::$app->request->post('departments_http_server'));
        $departments_http_server = $departments_http_server_short . '.' . CPANEL_PARENT_DOMAIN;
        
        $domain_alias = (array)Yii::$app->request->post('domain_alias', []);
        
        if (defined('CPANEL_TYPE') && CPANEL_TYPE == 'Virtualmin') {
            $departments_db_database = $user;
            $departments_db_server_username = $user;
        } else {
            $departments_db_database = $user . '_db';
            $departments_db_server_username = $user . '_user';
        }
        $departments_db_server_password = \common\helpers\Password::create_random_value(12);
        
        $departments_http_catalog = '/';
        $departments_https_server = $departments_http_server;
        $departments_https_catalog = $departments_http_catalog;
        $departments_enable_ssl = 0;

        $departments_db_server_host = DB_SERVER;
        $departments_db_use_pconnect = 'false';
        $departments_db_store_sessions = 'mysql';
        
        $departments_ftp_type = 'ftp';
        $departments_ftp_host = $departments_http_server;
        $departments_ftp_port = '';
        $departments_ftp_path = '/';
        $departments_ftp_username = $user;
        $departments_ftp_password = $password;
        $departments_ftp_pasv = true;
/* 
        $departments_monthly_price = tep_db_prepare_input(Yii::$app->request->post('departments_monthly_price'));
        $departments_setup_price = tep_db_prepare_input(Yii::$app->request->post('departments_setup_price'));
        $departments_commission_percentage = tep_db_prepare_input(Yii::$app->request->post('departments_commission_percentage'));

        $departments_already_registered_domain = (int)Yii::$app->request->post('departments_already_registered_domain');
        $departments_registered_uk_vat = (int)Yii::$app->request->post('departments_registered_uk_vat');
        $departments_custom_design_artwork = (int)Yii::$app->request->post('departments_custom_design_artwork');
*/
        $platform = tep_db_fetch_array(tep_db_query("
            select 
                p.platform_id, 
                p.platform_owner, 
                p.platform_name, 
                p.platform_telephone, 
                p.platform_landline, 
                p.platform_email_address,
                a.entry_street_address,
                a.entry_suburb,
                a.entry_city,
                a.entry_postcode,
                a.entry_state,
                a.entry_zone_id 
            from " . TABLE_PLATFORMS . " p, " . TABLE_PLATFORMS_ADDRESS_BOOK . " a 
            where p.is_default = 1 and a.platform_id = p.platform_id and a.is_default
            "));
        
        $department_install = 1;// myself
        $platform_owner = $platform['platform_owner'];
        $platform_name = $platform['platform_name'];
        $platform_landline = $platform['platform_landline'];
        $platform_companyname = '';
        $platform_companyvat = '';
        $platform_companyno = '';
        $admin_username = 'Superadmin';
        $user_password = \common\helpers\Password::create_random_value(12);

        $departments_firstname = tep_db_prepare_input(Yii::$app->request->post('departments_firstname'));
        $departments_lastname = tep_db_prepare_input(Yii::$app->request->post('departments_lastname'));
        $departments_email_address = tep_db_prepare_input(Yii::$app->request->post('departments_email_address'));
        if (empty($departments_email_address)) {
            $departments_email_address = $platform['platform_email_address'];
        }
        $departments_telephone = tep_db_prepare_input(Yii::$app->request->post('departments_telephone'));
        if (empty($departments_telephone)) {
            $departments_telephone = $platform['platform_telephone'];
        }
        $departments_street_address = tep_db_prepare_input(Yii::$app->request->post('departments_street_address'));
        if (empty($departments_street_address)) {
            $departments_street_address = $platform['entry_street_address'];
        }
        $departments_suburb = tep_db_prepare_input(Yii::$app->request->post('departments_suburb'));
        if (empty($departments_suburb)) {
            $departments_suburb = $platform['entry_suburb'];
        }
        $departments_postcode = tep_db_prepare_input(Yii::$app->request->post('departments_postcode'));
        if (empty($departments_postcode)) {
            $departments_postcode = $platform['entry_postcode'];
        }
        $departments_city = tep_db_prepare_input(Yii::$app->request->post('departments_city'));
        if (empty($departments_city)) {
            $departments_city = $platform['entry_city'];
        }
        $departments_state = tep_db_prepare_input(Yii::$app->request->post('departments_state'));
        $departments_zone_id = (int)Yii::$app->request->post('departments_zone_id');
        if (empty($departments_state)) {
            $departments_state = $platform['entry_state'];
            $departments_zone_id = $platform['entry_zone_id'];
        }
        $departments_country_id = (int)Yii::$app->request->post('departments_country_id');

        $api_key = tep_db_prepare_input(Yii::$app->request->post('api_key',''));

        $format_id = \common\helpers\Address::get_address_format_id($departments_country_id);
        $address = array();
        $address['firstname'] = $departments_firstname;
        $address['lastname'] = $departments_lastname;
        $address['street_address'] = $departments_street_address;
        $address['suburb'] = $departments_suburb;
        $address['city'] = $departments_city;
        $address['postcode'] = $departments_postcode;
        $address['state'] = $departments_state;
        $address['zone_id'] = $departments_zone_id;
        $address['country_id'] = $departments_country_id;
        $departments_address = \common\helpers\Address::address_format($format_id, $address, 0, '', "\n");
        $departments_status = (int)Yii::$app->request->post('departments_status');

        $sql_data_array = array('departments_store_name' => $departments_store_name,
            'departments_http_server' => $departments_http_server,
            'departments_https_server' => $departments_https_server,
            'departments_enable_ssl' => $departments_enable_ssl,
            'departments_http_catalog' => $departments_http_catalog,
            'departments_https_catalog' => $departments_https_catalog,
            'departments_already_registered_domain' => $department_install,
            'departments_firstname' => $departments_firstname,
            'departments_lastname' => $departments_lastname,
            'departments_email_address' => $departments_email_address,
            'departments_telephone' => $departments_telephone,
            'departments_street_address' => $departments_street_address,
            'departments_suburb' => $departments_suburb,
            'departments_postcode' => $departments_postcode,
            'departments_city' => $departments_city,
            'departments_state' => $departments_state,
            'departments_zone_id' => $departments_zone_id,
            'departments_country_id' => $departments_country_id,
            'departments_address' => $departments_address,
            'departments_ftp_type' => $departments_ftp_type,
            'departments_ftp_host' => $departments_ftp_host,
            'departments_ftp_port' => $departments_ftp_port,
            'departments_ftp_path' => $departments_ftp_path,
            'departments_ftp_username' => $departments_ftp_username,
            'departments_ftp_password' => $departments_ftp_password,
            'departments_ftp_pasv' => $departments_ftp_pasv,
            'departments_db_server_host' => $departments_db_server_host,
            'departments_db_server_username' => $departments_db_server_username,
            'departments_db_server_password' => $departments_db_server_password,
            'departments_db_database' => $departments_db_database,
            'departments_db_use_pconnect' => $departments_db_use_pconnect,
            'departments_db_store_sessions' => $departments_db_store_sessions,
            'api_key' => $api_key,
            'departments_status' => $departments_status,
            'departments_created' => 'now()',
            'alias' => implode(";", $domain_alias),
        );
        tep_db_perform(TABLE_DEPARTMENTS, $sql_data_array);
        $departments_id = tep_db_insert_id();

        if (true) {
            tep_db_perform(TABLE_DEPARTMENTS_CATEGORIES,[
                'departments_id' => $departments_id,
                'categories_id' => 0,
            ]);
        } else {
            $platforms_categories_query = tep_db_query("select * from " . TABLE_PLATFORMS_CATEGORIES . " where platform_id = '" . (int)$platform['platform_id'] . "'");
            while ($platforms_categories = tep_db_fetch_array($platforms_categories_query)) {
                tep_db_perform(TABLE_DEPARTMENTS_CATEGORIES,[
                    'departments_id' => $departments_id,
                    'categories_id' => $platforms_categories['categories_id'],
                ]);
            }

            $platforms_products_query = tep_db_query("select * from " . TABLE_PLATFORMS_PRODUCTS . " where platform_id = '" . (int)$platform['platform_id'] . "'");
            while ($platforms_products = tep_db_fetch_array($platforms_products_query)) {
                tep_db_perform(TABLE_DEPARTMENTS_PRODUCTS,[
                    'departments_id' => $departments_id,
                    'products_id' => $platforms_products['products_id'],
                ]);
            }
        }

        $conf = "";
        $conf .= "action=create\n";
        $conf .= "domain=". $departments_http_server . "\n";
        $conf .= "alias=". implode(";", $domain_alias) . "\n";
        $conf .= "username=". $user . "\n";
        $conf .= "password=". $password . "\n";
        $conf .= "db_name=". $departments_db_database . "\n";
        $conf .= "db_user=". $departments_db_server_username . "\n";
        $conf .= "db_password=". $departments_db_server_password . "\n";
        if (CPANEL_SOURCE == 'svn') {
            $conf .= "svn=" . CPANEL_SVN_LINK . "\n";
        } else {
            $conf .= "file=" . CPANEL_ARCHIVE_FILE . "\n";
        }
        $conf .= "source=" . $departments_http_server_short . "_create.xml" . "\n";
        $conf .= "destination=setup.xml" . "\n";
        if (CPANEL_SOURCE == 'svn') {
            $conf .= "execute=setup.php" . "\n";
        } else {
            $conf .= "execute=setup".$department_install.".php" . "\n";
        }
        $conf .= "departments_api_key=" . $api_key . "\n";
        $conf .= "callback_url=".tep_catalog_href_link('', '', 'SSL') . "\n";
        fwrite(fopen(CPANEL_PARENT_PATH . $departments_http_server_short . '_create.settings', 'w'), $conf);
        
        $data = [
                        'username' => $user,
                        'password' => $password,
                        'db_name' => $departments_db_database,
                        'db_user' => $departments_db_server_username,
                        'db_password' => $departments_db_server_password,
                        'departments_http_server' => $departments_http_server,
                        'departments_enable_ssl' => ($departments_enable_ssl == 1 ? 2 : 0),
                        'platform_owner' => $platform_owner,
                        'platform_name' => $platform_name,
                        'departments_telephone' => $departments_telephone,
                        'departments_store_name' => $departments_store_name,
                        'departments_firstname' => $departments_firstname,
                        'departments_lastname' => $departments_lastname,
                        'departments_email_address' => $departments_email_address,
                        'departments_address' => $departments_address,
                        'departments_street_address' => $departments_street_address,
                        'departments_suburb' => $departments_suburb,
                        'platform_landline' => $platform_landline,
                        'platform_companyname' => $platform_companyname,
                        'platform_companyvat' => $platform_companyvat,
                        'platform_companyno' => $platform_companyno,
                        'departments_postcode' => $departments_postcode,
                        'departments_city' => $departments_city,
                        'departments_state' => $departments_state,
                        'departments_zone_id' => $departments_zone_id,
                        'departments_country_id' => $departments_country_id,
                        'departments_api_key' => $api_key,
                        'callback_url' => tep_catalog_href_link('', '', 'SSL'),
                        'admin_username' => $admin_username,
                        'admin_password' => $user_password,
                        'alias' => $domain_alias,
                    ];
        $xml_data = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><response/>');
        $this->array_to_xml($data,$xml_data);
        $xml_data->saveXML(CPANEL_PARENT_PATH . $departments_http_server_short . '_create.xml');
        
        $messageStack = \Yii::$container->get('message_stack');
        $messageStack->add_session('A request for registration of the new department was sent!', 'success');
        if (Yii::$app->request->isAjax) {
            echo $departments_id;
            exit;
        } else {
            return $this->redirect(Yii::$app->urlManager->createUrl(['departments/new', 'dID' => $departments_id]));
        }
    }
    
    private function array_to_xml( $data, &$xml_data ) {
        foreach( $data as $key => $value ) {
            if( is_numeric($key) ){
                $key = 'item'.$key; //dealing with <0/>..<n/> issues
            }
            if( is_array($value) ) {
                $subnode = $xml_data->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml_data->addChild("$key",htmlspecialchars("$value"));
            }
         }
    }

    public function actionTest() {
        $data = ['username' => 'Dave', 'password' => 'qwerty'];
        
        /*
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
        return $data;
        */
        
        $xml_data = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><response/>');
        $this->array_to_xml($data,$xml_data);
        //$result = $xml_data->asXML('/file/path/name.xml');
        $a = $xml_data->asXML();
        
        echo "<pre>";
        print_r($a);
        echo "</pre>";
        die();
        
    }

    public function actionSwitchStatus() {
        $departments_id = Yii::$app->request->post('dID');
        $status = Yii::$app->request->post('status');
        
        $departments = tep_db_fetch_array(tep_db_query("select departments_id, departments_store_name, departments_http_server, departments_https_server, departments_enable_ssl, departments_http_catalog, departments_https_catalog, departments_design_template_name, departments_firstname, departments_lastname, departments_email_address, departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database, departments_ftp_username, departments_status, departments_created, departments_modified from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $departments_id . "'"));
        $dInfo = new \objectInfo($departments, false);
        
        if ($status == 'true') {
            $conf = "";
            $conf .= "action=unsuspend\n";
            $conf .= "domain=" . $dInfo->departments_http_server . "\n";
            $conf .= "username=". $dInfo->departments_ftp_username . "\n";
            fwrite(fopen(CPANEL_PARENT_PATH . $dInfo->departments_ftp_username . '_unsuspend.settings', 'w'), $conf);
            @unlink(CPANEL_PARENT_PATH . $dInfo->departments_ftp_username . '_suspend.settings');
        } else {
            $conf = "";
            $conf .= "action=suspend\n";
            $conf .= "domain=" . $dInfo->departments_http_server . "\n";
            $conf .= "username=". $dInfo->departments_ftp_username . "\n";
            fwrite(fopen(CPANEL_PARENT_PATH . $dInfo->departments_ftp_username . '_suspend.settings', 'w'), $conf);
            @unlink(CPANEL_PARENT_PATH . $dInfo->departments_ftp_username . '_unsuspend.settings');
        }
        tep_db_query("update " . TABLE_DEPARTMENTS . " set departments_status = '" . ($status == 'true' ? 1 : 0) . "', departments_modified = now() where departments_id = '" . (int) $departments_id . "'");
    }
    
    public function actionKeepAlive() {
        $departments_id = Yii::$app->request->post('dID');
        $status = Yii::$app->request->post('status');
        tep_db_query("update " . TABLE_DEPARTMENTS . " set keep_alive = '" . ($status == 'true' ? 1 : 0) . "', departments_modified = now() where departments_id = '" . (int) $departments_id . "'");
    }

    public function actionConfirmitemdelete() {
        \common\helpers\Translation::init('admin/departments');

        $this->layout = false;

        $departments_id = Yii::$app->request->post('dID');

        if ($departments_id > 0) {
            $departments = tep_db_fetch_array(tep_db_query("select departments_id, departments_store_name, departments_http_server, departments_https_server, departments_enable_ssl, departments_http_catalog, departments_https_catalog, departments_design_template_name, departments_firstname, departments_lastname, departments_email_address, departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database, departments_status, departments_created, departments_modified from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $departments_id . "'"));
            $dInfo = new \objectInfo($departments, false);

            echo tep_draw_form('departments', 'departments', \common\helpers\Output::get_all_get_params(array('dID', 'action')) . 'dID=' . $cInfo->departments_id . '&action=deleteconfirm', 'post', 'id="item_delete" onSubmit="return deleteItem();"');

            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_DEPARTMENT . '</div>';
            echo TEXT_DELETE_INTRO . '<br><br><b>' . $dInfo->departments_store_name . '</b>';
            echo '<div class="btn-toolbar btn-toolbar-order">';
            echo '<button type="submit" class="btn btn-primary btn-no-margin">' . IMAGE_CONFIRM . '</button>';
            echo '<button class="btn btn-cancel" onClick="return resetStatement(' . (int) $departments_id . ')">' . IMAGE_CANCEL . '</button>';

            echo tep_draw_hidden_field('dID', $departments_id);
            echo '</div></form>';
        }
    }

    public function actionItemdelete() {
        $this->layout = false;

        $departments_id = (int) Yii::$app->request->post('dID');

        $departments = tep_db_fetch_array(tep_db_query("select departments_id, departments_store_name, departments_http_server, departments_https_server, departments_enable_ssl, departments_http_catalog, departments_https_catalog, departments_design_template_name, departments_firstname, departments_lastname, departments_email_address, departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database, departments_ftp_username, departments_status, departments_created, departments_modified from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $departments_id . "'"));
        $dInfo = new \objectInfo($departments, false);

        $conf = "";
        $conf .= "action=delete\n";
        $conf .= "username=". $dInfo->departments_ftp_username . "\n";
        fwrite(fopen(CPANEL_PARENT_PATH . $dInfo->departments_ftp_username . '_delete.settings', 'w'), $conf);
        
        tep_db_query("delete from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $departments_id . "'");
        //tep_db_query("delete from " . TABLE_DEPARTMENTS_TO_FEATURES . " where departments_id = '" . (int) $departments_id . "'");
        tep_db_query("delete from " . TABLE_DEPARTMENTS_CATEGORIES . " where departments_id = '" . (int) $departments_id . "'");
        tep_db_query("delete from departments_categories_price_formula where departments_id = '" . (int) $departments_id . "'");
        tep_db_query("delete from " . TABLE_DEPARTMENTS_EXTERNAL_PLATFORMS . " where departments_id = '" . (int) $departments_id . "'");
        tep_db_query("delete from " . TABLE_DEPARTMENTS_PRODUCTS . " where departments_id = '" . (int) $departments_id . "'");

        $messageStack = \Yii::$container->get('message_stack');
        $messageStack->add_session('The department has been successfully deleted!', 'success');
        if (Yii::$app->request->isAjax) {
            echo 'OK';
            exit;
        } else {
            return $this->redirect(Yii::$app->urlManager->createUrl('departments/index'));
        }
    }

    public function actionSortOrder()
    {
        $this->layout = false;

        $moved_id = (int)$_POST['sort_item'];
        $ref_array = (isset($_POST['item']) && is_array($_POST['item']))?array_map('intval',$_POST['item']):array();
        if ( $moved_id && in_array($moved_id, $ref_array) ) {
            // {{ normalize
            $order_counter = 0;
            $order_list_r = tep_db_query(
                "SELECT departments_id, departments_sort_order ".
                "FROM ". TABLE_DEPARTMENTS ." ".
                "WHERE 1 ".
                "ORDER BY departments_sort_order, departments_store_name"
            );
            while( $order_list = tep_db_fetch_array($order_list_r) ){
                $order_counter++;
                tep_db_query("UPDATE ".TABLE_DEPARTMENTS." SET departments_sort_order='{$order_counter}' WHERE departments_id='{$order_list['departments_id']}' ");
            }
            // }} normalize
            $get_current_order_r = tep_db_query(
                "SELECT departments_id, departments_sort_order ".
                "FROM ".TABLE_DEPARTMENTS." ".
                "WHERE departments_id IN('".implode("','",$ref_array)."') ".
                "ORDER BY departments_sort_order"
            );
            $ref_ids = array();
            $ref_so = array();
            while($_current_order = tep_db_fetch_array($get_current_order_r)){
                $ref_ids[] = (int)$_current_order['departments_id'];
                $ref_so[] = (int)$_current_order['departments_sort_order'];
            }

            foreach( $ref_array as $_idx=>$id ) {
                tep_db_query("UPDATE ".TABLE_DEPARTMENTS." SET departments_sort_order='{$ref_so[$_idx]}' WHERE departments_id='{$id}' ");
            }

        }
        return 'ok';
    }

    public function actionEditCatalog()
    {
        \common\helpers\Translation::init('admin/departments');

        $department_id   = (int) Yii::$app->request->get('id');

        $this->layout = false;

        $assigned = $this->get_assigned_catalog($department_id, true);

        $tree_init_data = $this->load_tree_slice($department_id,0);
        foreach ($tree_init_data as $_idx=>$_data) {
            if ( isset($assigned[$_data['key']]) ){
                $tree_init_data[$_idx]['selected'] = true;
            }
        }

        $selected_data = json_encode($assigned);

        return $this->render('edit-catalog.tpl', [
            'selected_data' => $selected_data,
            'tree_data' => $tree_init_data,
            'tree_server_url' => Yii::$app->urlManager->createUrl(['departments/load-tree', 'department_id' => $department_id]),
            'tree_server_save_url' => Yii::$app->urlManager->createUrl(['departments/update-catalog-selection', 'department_id' => $department_id])
        ]);
    }

    private function get_assigned_catalog($department_id, $validate=false)
    {
        return \common\helpers\Categories::get_department_assigned_catalog($department_id, $validate);
    }

    private function load_tree_slice($department_id, $category_id)
    {
        return \common\helpers\Categories::load_department_tree_slice($department_id, $category_id);
    }

    private function tep_get_category_children(&$children, $department_id, $categories_id) {
        if ( !is_array($children) ) $children = array();
        foreach($this->load_tree_slice($department_id, $categories_id) as $item) {
            $key = $item['key'];
            $children[] = $key;
            if ($item['folder']) {
                $this->tep_get_category_children($children, $department_id, intval(substr($item['key'],1)));
            }
        }
    }

    public function actionLoadTree()
    {
        \common\helpers\Translation::init('admin/platforms');
        $this->layout = false;

        $department_id = Yii::$app->request->get('department_id');
        $do = Yii::$app->request->post('do','');

        $response_data = array();

        if ( $do == 'missing_lazy' ) {
            $category_id = Yii::$app->request->post('id');
            $selected = Yii::$app->request->post('selected');
            $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
            $selected_data = json_decode($req_selected_data,true);
            if ( !is_array($selected_data) ) {
                $selected_data = json_decode($selected_data,true);
            }

            if (substr($category_id, 0, 1) == 'c') $category_id = intval(substr($category_id, 1));

            $response_data['tree_data'] = $this->load_tree_slice($department_id, $category_id);
            foreach( $response_data['tree_data'] as $_idx=>$_data ) {
                $response_data['tree_data'][$_idx]['selected'] = isset($selected_data[$_data['key']]);
            }
            $response_data = $response_data['tree_data'];
        }

        if ( $do == 'update_selected' ) {
            $id = Yii::$app->request->post('id');
            $selected = Yii::$app->request->post('selected');
            $select_children = Yii::$app->request->post('select_children');
            $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
            $selected_data = json_decode($req_selected_data,true);
            if ( !is_array($selected_data) ) {
                $selected_data = json_decode($selected_data,true);
            }

            if ( substr($id,0,1)=='p' ) {
                list($ppid, $cat_id) = explode('_',$id,2);
                if ( $selected ) {
                    // check parent categories
                    $parent_ids = array((int)$cat_id);
                    \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0], false);
                    foreach( $parent_ids as $parent_id ) {
                        if ( !isset($selected_data['c'.(int)$parent_id]) ) {
                            $response_data['update_selection']['c'.(int)$parent_id] = true;
                            $selected_data['c'.(int)$parent_id] = 'c'.(int)$parent_id;
                        }
                    }
                    if ( !isset($selected_data[$id]) ) {
                        $response_data['update_selection'][$id] = true;
                        $selected_data[$id] = $id;
                    }
                }else{
                    if ( isset($selected_data[$id]) ) {
                        $response_data['update_selection'][$id] = false;
                        unset($selected_data[$id]);
                    }
                }
            }elseif ( substr($id,0,1)=='c' ) {
                $cat_id = (int)substr($id,1);
                if ( $selected ) {
                    $parent_ids = array((int)$cat_id);
                    \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0], false);
                    foreach( $parent_ids as $parent_id ) {
                        if ( !isset($selected_data['c'.(int)$parent_id]) ) {
                            $response_data['update_selection']['c'.(int)$parent_id] = true;
                            $selected_data['c'.(int)$parent_id] = 'c'.(int)$parent_id;
                        }
                    }
                    if ( $select_children ) {
                        $children = array();
                        $this->tep_get_category_children($children,$department_id,$cat_id);
                        foreach($children as $child_key){
                            if ( !isset($selected_data[$child_key]) ) {
                                $response_data['update_selection'][$child_key] = true;
                                $selected_data[$child_key] = $child_key;
                            }
                        }
                    }
                    if ( !isset($selected_data[$id]) ) {
                        $response_data['update_selection'][$id] = true;
                        $selected_data[$id] = $id;
                    }
                }else{
                    $children = array();
                    $this->tep_get_category_children($children,$department_id,$cat_id);
                    foreach($children as $child_key){
                        if ( isset($selected_data[$child_key]) ) {
                            $response_data['update_selection'][$child_key] = false;
                            unset($selected_data[$child_key]);
                        }
                    }
                    if ( isset($selected_data[$id]) ) {
                        $response_data['update_selection'][$id] = false;
                        unset($selected_data[$id]);
                    }
                }
            }

            $response_data['selected_data'] = $selected_data;
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $response_data;

    }

    function actionUpdateCatalogSelection()
    {
        \common\helpers\Translation::init('admin/departments');
        $this->layout = false;

        $department_id = Yii::$app->request->get('department_id');
        $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
        $selected_data = json_decode($req_selected_data,true);
        if ( !is_array($selected_data) ) {
            $selected_data = json_decode($selected_data,true);
        }
        if ( !isset($selected_data['c0']) ) $selected_data['c0'] = 'c0';

        $assigned = $this->get_assigned_catalog($department_id);
        $assigned_products = array();
        foreach ( $assigned as $assigned_key ) {
            if ( substr($assigned_key,0,1)=='p' ) {
                $pid = intval(substr($assigned_key,1));
                $assigned_products[$pid] = $pid;
                unset($assigned[$assigned_key]);
            }
        }
        if (is_array($selected_data)) {
            $selected_products = array();
            foreach( $selected_data as $selection ) {
                if ( substr($selection,0,1)=='p' ) {
                    $pid = intval(substr($selection,1));
                    $selected_products[$pid] = $pid;
                    continue;
                }
                if (isset($assigned[$selection])){
                    unset($assigned[$selection]);
                }else{
                    if ( substr($selection,0,1)=='c' ) {
                        $cat_id = (int)substr($selection, 1);
                        tep_db_perform(TABLE_DEPARTMENTS_CATEGORIES,array(
                            'departments_id' => $department_id,
                            'categories_id' => $cat_id,
                        ));
                        unset($assigned[$selection]);
                    }
                }
            }
            foreach( $selected_products as $pid ) {
                if (isset($assigned_products[$pid])) {
                    unset($assigned_products[$pid]);
                }else{
                    tep_db_perform(TABLE_DEPARTMENTS_PRODUCTS,array(
                        'departments_id' => $department_id,
                        'products_id' => $pid,
                    ));
                }
            }
        }

        foreach ($assigned as $clean_key) {
            if ( substr($clean_key,0,1)=='c' ) {
                $cat_id = (int)substr($clean_key, 1);
                if ( $cat_id==0 ) continue;
                tep_db_query(
                    "DELETE FROM ".TABLE_DEPARTMENTS_CATEGORIES." ".
                    "WHERE categories_id!=0 AND departments_id ='".$department_id."' AND categories_id = '".$cat_id."' "
                );
                unset($assigned[$clean_key]);
            }
        }
        if ( count($assigned_products)>1000 ) {
            foreach( $assigned_products as $assigned_product_id ) {
                tep_db_query(
                    "DELETE FROM ".TABLE_DEPARTMENTS_PRODUCTS." ".
                    "WHERE departments_id ='".$department_id."' AND products_id = '".$assigned_product_id."' "
                );
            }
        }elseif( count($assigned_products)>0 ){
            tep_db_query(
                "DELETE FROM ".TABLE_DEPARTMENTS_PRODUCTS." ".
                "WHERE departments_id ='".$department_id."' AND products_id IN ('".implode("','",$assigned_products)."') "
            );
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = array(
            'status' => 'ok'
        );

    }

    public function actionCreateLocallyApiClientStatus()
    {
        $this->layout = false;

        $departments_id = intval(Yii::$app->request->get('dID', 0));
        $clientStatusId = intval(Yii::$app->request->post('clientStatusId', 0));
        $localStatusGroupId = intval(Yii::$app->request->post('localStatusGroupId', 0));

        $result = array(
            'status' => 'ok'
        );
        if ( $departments_id && $clientStatusId && $localStatusGroupId ) {
            $_keyValue = Api::getDepartmentServerKeyValue($departments_id, 'clientOrder/Statuses');
            if (!empty($_keyValue)) {
                $extract_value = json_decode(base64_decode($_keyValue), true);
                if (is_array($extract_value)) {
                    foreach ($extract_value as $clientStatus) {
                        if ($clientStatus['id'] != $clientStatusId) continue;

                        $defaultName = current($clientStatus['names']);
                        if ( isset($clientStatus['names'][DEFAULT_LANGUAGE]) ) {
                            $defaultName = $clientStatus['names'][DEFAULT_LANGUAGE];
                            $check_exists_r = tep_db_query(
                                "SELECT orders_status_id ".
                                "FROM ".TABLE_ORDERS_STATUS." ".
                                "WHERE orders_status_name='".tep_db_input($defaultName)."' ".
                                " AND language_id='".\common\classes\language::get_id(DEFAULT_LANGUAGE)."' ".
                                " AND orders_status_groups_id='{$localStatusGroupId}' ".
                                "LIMIT 1"
                            );
                            if ( tep_db_num_rows($check_exists_r)>0 ) {
                                $check_exists = tep_db_fetch_array($check_exists_r);
                                $result['status_id'] = $check_exists['orders_status_id'];
                                break;
                            }
                        }else{
                            $check_exists_r = tep_db_query(
                                "SELECT orders_status_id ".
                                "FROM ".TABLE_ORDERS_STATUS." ".
                                "WHERE orders_status_name='".tep_db_input($defaultName)."' ".
                                " AND orders_status_groups_id='{$localStatusGroupId}' ".
                                "LIMIT 1"
                            );
                            if ( tep_db_num_rows($check_exists_r)>0 ) {
                                $check_exists = tep_db_fetch_array($check_exists_r);
                                $result['status_id'] = $check_exists['orders_status_id'];
                                break;
                            }
                        }
                        $get_current_status_id = tep_db_fetch_array(tep_db_query(
                            "SELECT MAX(orders_status_id) AS current_max_id FROM ".TABLE_ORDERS_STATUS." "
                        ));
                        $new_status_id = intval($get_current_status_id['current_max_id'])+1;
                        tep_db_query(
                            "INSERT INTO ".TABLE_ORDERS_STATUS." (orders_status_id, orders_status_groups_id, language_id, orders_status_name) ".
                            " SELECT {$new_status_id}, {$localStatusGroupId}, languages_id, '".tep_db_input($defaultName)."' FROM ".TABLE_LANGUAGES." "
                        );
                        $defLangId = \common\classes\language::get_id(DEFAULT_LANGUAGE);
                        foreach ( $clientStatus['names'] as $langCode=>$langName ) {
                            $langId = \common\classes\language::get_id($langCode);
                            if ( $defLangId==$langId ) continue;
                            tep_db_query(
                                "UPDATE ".TABLE_ORDERS_STATUS." ".
                                "SET orders_status_name='".tep_db_input($defaultName)."' ".
                                "WHERE orders_status_id='{$new_status_id}' AND language_id='".$langId."'"
                            );
                        }
                        $result['status_id'] = $new_status_id;
                    }
                }
            }
            if ( isset($result['status_id']) && $result['status_id'] ) {
                $local_statuses = [];
                $currentGroup = '';
                foreach( \common\helpers\Order::getStatusesGrouped(true) as $orderStatusVariant ) {
                    if ( strpos($orderStatusVariant['id'],'group_')===0 ) {
                        $currentGroup = $orderStatusVariant['text'];
                        continue;
                    }
                    $local_statuses[$currentGroup][str_replace('status_','',$orderStatusVariant['id'])]
                        = str_replace('&nbsp;','',$orderStatusVariant['text']);
                }

                $result['dropDownList'] =  Html::dropDownList('newLocalStatuses','', $local_statuses,['class'=>'form-control js-status-map']);
            }
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $result;
    }
    
    public function actionDepartmentsdelete() 
    {
        $this->layout = false;
        $selected_ids = Yii::$app->request->post('selected_ids');
        foreach ($selected_ids as $departments_id) {
            $departments = tep_db_fetch_array(tep_db_query("select departments_id, departments_store_name, departments_http_server, departments_https_server, departments_enable_ssl, departments_http_catalog, departments_https_catalog, departments_design_template_name, departments_firstname, departments_lastname, departments_email_address, departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database, departments_ftp_username, departments_status, departments_created, departments_modified from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $departments_id . "'"));
            $dInfo = new \objectInfo($departments, false);

            $conf = "";
            $conf .= "action=delete\n";
            $conf .= "username=". $dInfo->departments_ftp_username . "\n";
            fwrite(fopen(CPANEL_PARENT_PATH . $dInfo->departments_ftp_username . '_delete.settings', 'w'), $conf);

            tep_db_query("delete from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $departments_id . "'");
            //tep_db_query("delete from " . TABLE_DEPARTMENTS_TO_FEATURES . " where departments_id = '" . (int) $departments_id . "'");
            tep_db_query("delete from " . TABLE_DEPARTMENTS_CATEGORIES . " where departments_id = '" . (int) $departments_id . "'");
            tep_db_query("delete from departments_categories_price_formula where departments_id = '" . (int) $departments_id . "'");
            tep_db_query("delete from " . TABLE_DEPARTMENTS_EXTERNAL_PLATFORMS . " where departments_id = '" . (int) $departments_id . "'");
            tep_db_query("delete from " . TABLE_DEPARTMENTS_PRODUCTS . " where departments_id = '" . (int) $departments_id . "'");

        }
    }
}
