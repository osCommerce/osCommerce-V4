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
use \common\models\PDOConnector;

class DepartmentsAdminfilesController extends Sceleton {

    public $acl = ['BOX_HEADING_DEPARTMENTS', 'BOX_DEPARTMENTS_BOXES'];
    
    public function actionIndex() {

        \common\helpers\Translation::init('admin/adminfiles');
        
        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int)Yii::$app->request->get('row', 0);

        $selected_department_id = (int)Yii::$app->request->get('department_id');
        
        $departments = [];
        $departments_query = tep_db_query("SELECT * FROM " . TABLE_DEPARTMENTS . " WHERE departments_status > 0");
        while ($department = tep_db_fetch_array($departments_query)) {
            if ($selected_department_id == 0) {
                $selected_department_id = $department['departments_id'];
            }
            $departments[] = [
                'id' => $department['departments_id'],
                'text' => $department['departments_store_name'],
                'link' => Yii::$app->urlManager->createUrl(['departments-adminfiles/', 'department_id'=> $department['departments_id'] ]),
            ];
        }

        $this->selectedMenu = array('departments', 'departments-adminfiles');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('departments-adminfiles/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl(['departments-adminfiles/edit', 'department_id'=> $selected_department_id ]).'" class="create_item">'.IMAGE_INSERT.'</a>';
        $this->view->accessTable = [
            [
                'title' => TABLE_HEADING_NAME,
                'not_important' => 0
            ],
        ];

        return $this->render('index', [
            'departments' => $departments,
            'selected_department_id' => $selected_department_id,
        ]);
    }

    public function actionList() {
        
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        if ($length == -1) {
            $length = 10000;
        }

        $responseList = [];
        $recordsTotal = $recordsFiltered = 0;
        
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);
        
        $department_id = 0;
        if ( isset($output['department_id']) ){
            $department_id = (int)$output['department_id'];
        }
        $departments = tep_db_fetch_array(tep_db_query("select departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $department_id . "'"));
        if (is_array($departments)) {

            PDOConnector::init(['host' => $departments['departments_db_server_host'], 'user' => $departments['departments_db_server_username'], 'password' => $departments['departments_db_server_password'], 'dbname' => $departments['departments_db_database']]);
            
            if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
                $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
                $search_condition = " where access_levels_name like '%" . $keywords . "%' ";
            } else {
                $search_condition = " where 1 ";
            }

            if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
                switch ($_GET['order'][0]['column']) {
                    case 0:
                        $orderBy = "access_levels_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                        break;
                    default:
                        $orderBy = "access_levels_name";
                        break;
                }
            } else {
                $orderBy = "access_levels_name";
            }

            $accessQueryRaw = " from " . TABLE_ACCESS_LEVELS . " $search_condition order by $orderBy";
            //---
            $currentPageNumber = ( $start / $length ) + 1;
            $offset = ($length * ($currentPageNumber - 1));
            $sqlLimit = " limit " . max($offset, 0) . ", " . $length;
            
            PDOConnector::query("select count(*) as total" . $accessQueryRaw);
            $recordsCount = PDOConnector::fetch();
            $recordsTotal = $recordsCount['total'];
            PDOConnector::query("select *" . $accessQueryRaw . $sqlLimit);
            //---
            //$current_page_number = ( $start / $length ) + 1;
            //$_split = new \splitPageResults($current_page_number, $length, $accessQueryRaw, $recordsTotal, 'access_levels_id');
            //$accessQuery = tep_db_query($accessQueryRaw);
            while ($access = PDOConnector::fetch()) {
            //while ($access = tep_db_fetch_array($accessQuery)) {
                $responseList[] = array(
                        $access['access_levels_name'] . '<input class="cell_identify" type="hidden" value="' . $access['access_levels_id'] . '">',
                    );
            }

        }
        
        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $responseList
        ];
        echo json_encode($response);
    }

    public function actionPreview() {
        $this->layout = false;
        
        $item_id = (int) Yii::$app->request->post('item_id');
        $department_id = (int)Yii::$app->request->post('department_id');
        
        $departments = tep_db_fetch_array(tep_db_query("select departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $department_id . "'"));
        if (!is_array($departments)) {
            die("Wrong data.");
        }
        
        PDOConnector::init(['host' => $departments['departments_db_server_host'], 'user' => $departments['departments_db_server_username'], 'password' => $departments['departments_db_server_password'], 'dbname' => $departments['departments_db_database']]);
        
        PDOConnector::query("select * from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . $item_id . "'");
        $access = PDOConnector::fetch();
        if (is_array($access)) {
            echo '<div class="or_box_head">' . $access['access_levels_name'] . '</div>';
            echo '<div class="btn-toolbar btn-toolbar-order">';
            echo '<a class="btn btn-edit btn-no-margin" href="' . Yii::$app->urlManager->createUrl(['departments-adminfiles/edit', 'item_id' => $item_id, 'department_id' => $department_id]) . '">' . IMAGE_EDIT . '</a>';
            echo '<button class="btn btn-delete" onclick="accessDelete(\'' . $item_id . '\')">' . IMAGE_DELETE . '</button>';
            echo '</div>';
        }
    }
    
    public function actionEdit() {
        \common\helpers\Translation::init('admin/adminfiles');
        \common\helpers\Translation::init('admin/categories');
        
        $this->selectedMenu = array('departments', 'departments-adminfiles');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('departments-adminfiles/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        
        if (Yii::$app->request->isPost) {            
            $item_id = (int) Yii::$app->request->post('item_id');
            $this->layout = false;
        } else {
            $item_id = (int) Yii::$app->request->get('item_id');
        }
        $department_id = (int)Yii::$app->request->get('department_id');
        
        $departments = tep_db_fetch_array(tep_db_query("select departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $department_id . "'"));
        if (!is_array($departments)) {
            die("Wrong data.");
        }
        
        PDOConnector::init(['host' => $departments['departments_db_server_host'], 'user' => $departments['departments_db_server_username'], 'password' => $departments['departments_db_server_password'], 'dbname' => $departments['departments_db_database']]);
        
        if ($item_id > 0) {
            $actionName = IMAGE_EDIT;
        } else {
            $actionName = IMAGE_INSERT;
        }
        
        $accessQuery = PDOConnector::query("select * from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . $item_id . "'");
        $access = PDOConnector::fetch( $accessQuery );
        $accessInfo = new \objectInfo( is_array($access)?$access:['access_levels_persmissions'=>''] );

        
        $aclTree = \common\helpers\Acl::buildTreePDO($accessInfo->access_levels_persmissions);
        
        return $this->render('edit', [
            'actionName' => $actionName,
            'accessInfo' => $accessInfo,
            'aclTree' => $aclTree,
            'item_id' => $item_id,
            'department_id' => $department_id,
        ]);
    }
    
    public function actionSubmit() {
        \common\helpers\Translation::init('admin/adminfiles');
        
        $item_id = (int) Yii::$app->request->post('item_id');
        $department_id = (int)Yii::$app->request->post('department_id');
        
        $departments = tep_db_fetch_array(tep_db_query("select departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $department_id . "'"));
        if (!is_array($departments)) {
            die("Wrong data.");
        }
        
        PDOConnector::init(['host' => $departments['departments_db_server_host'], 'user' => $departments['departments_db_server_username'], 'password' => $departments['departments_db_server_password'], 'dbname' => $departments['departments_db_database']]);
        
        $access_levels_name = Yii::$app->request->post('access_levels_name');
        
        $persmissions = Yii::$app->request->post('persmissions');
        if (!is_array($persmissions)) {
            $persmissions = [];
        }
        $access_levels_persmissions = implode(",", $persmissions);
        
        $sql_data_array = [
            'access_levels_name' => $access_levels_name,
            'access_levels_persmissions' => $access_levels_persmissions,
        ];
        
        if( $item_id > 0 ) {
            PDOConnector::perform(TABLE_ACCESS_LEVELS, $sql_data_array, 'update', "access_levels_id = '" . (int) $item_id . "'");
        } else {
            PDOConnector::perform(TABLE_ACCESS_LEVELS, $sql_data_array);
            $item_id = PDOConnector::lastInsertId();
        }
                
        $messageType = 'success';
        $message = TEXT_MESSEAGE_SUCCESS;
?>
        <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>   
                    </div>  
                    <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
                </div> 
                <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
                
            </script>
            </div>
<?php
        echo '<script> window.location.replace("'. Yii::$app->urlManager->createUrl(['departments-adminfiles/edit', 'item_id' => $item_id, 'department_id' => $department_id]) . '");</script>';
        //return $this->actionEdit();
    }
    
    public function actionDelete() {
        $item_id = (int) Yii::$app->request->post('item_id');
        $department_id = (int)Yii::$app->request->post('department_id');

        $departments = tep_db_fetch_array(tep_db_query("select departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $department_id . "'"));
        if (!is_array($departments)) {
            die("Wrong data.");
        }

        PDOConnector::init(['host' => $departments['departments_db_server_host'], 'user' => $departments['departments_db_server_username'], 'password' => $departments['departments_db_server_password'], 'dbname' => $departments['departments_db_database']]);
        PDOConnector::query("delete from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . $item_id . "'");
    }
    
    public function actionRecalcAcl() {
        $this->layout = false;
        
        $persmissions = Yii::$app->request->post('persmissions');
        $department_id = (int)Yii::$app->request->post('department_id');

        $departments = tep_db_fetch_array(tep_db_query("select departments_db_server_host, departments_db_server_username, departments_db_server_password, departments_db_database from " . TABLE_DEPARTMENTS . " where departments_id = '" . (int) $department_id . "'"));
        if (!is_array($departments)) {
            die("Wrong data.");
        }

        PDOConnector::init(['host' => $departments['departments_db_server_host'], 'user' => $departments['departments_db_server_username'], 'password' => $departments['departments_db_server_password'], 'dbname' => $departments['departments_db_database']]);
        
        $aclTree = \common\helpers\Acl::buildTreePDO($persmissions);
        
        return $this->render('recalc-acl', [
            'aclTree' => $aclTree,
        ]);
    }

}
