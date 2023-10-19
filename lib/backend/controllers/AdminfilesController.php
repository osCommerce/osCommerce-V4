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

class AdminfilesController extends Sceleton {

    public $acl = ['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_BOXES'];

    public function actionIndex() {
        $this->selectedMenu = array('administrator', 'adminfiles');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminfiles/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('adminfiles/edit').'" class="btn btn-primary" onclick="return editItem(0)">'.IMAGE_INSERT.'</a>';
        $this->view->accessTable = [
            [
                'title' => TABLE_HEADING_NAME,
                'not_important' => 0
            ],
        ];

        $this->view->filters = new \stdClass();
        $this->view->filters->row = (int)Yii::$app->request->get('row', 0);

        return $this->render('index');
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $responseList = [];
        if ($length == -1)
            $length = 10000;
        $recordsTotal = 0;

        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where access_levels_name like '%" . $keywords . "%' ";
        } else {
            $search_condition = " where 1 ";
        }

        /*if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
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
        }*/

        $orderBy = "sort_order, access_levels_name";

        $current_page_number = ( $start / $length ) + 1;
        $accessQueryRaw = "select * from " . TABLE_ACCESS_LEVELS . " $search_condition order by $orderBy";
        $_split = new \splitPageResults($current_page_number, $length, $accessQueryRaw, $recordsTotal, 'access_levels_id');
        $accessQuery = tep_db_query($accessQueryRaw);
        while ($access = tep_db_fetch_array($accessQuery)) {
            $responseList[] = array(
                    '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder">' . $access['access_levels_name']  .
                        '<input class="cell_identify" type="hidden" value="' . $access['access_levels_id'] . '">'.
                        '<input class="cell_type" type="hidden" value="top" >'.
                    '</div></div>',
                    //$access['access_levels_name'] . '<input class="cell_identify" type="hidden" value="' . $access['access_levels_id'] . '">',
                );
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $responseList
        ];
        echo json_encode($response);
    }

    private function getAccessLevelByIdArray($accessLevelId = 0)
    {
        $return = array();
        try {
            $return = \common\models\AccessLevels::find()->where(['access_levels_id' => (int)$accessLevelId])->asArray(true)->one();
            try {
                if (is_array($return) AND isset($return['access_levels_persmissions'])) {
                    if ($return['access_levels_persmissions'] == '') {
                        $return['access_levels_persmissions'] = array();
                    } else {
                        $return['access_levels_persmissions'] = explode(',', $return['access_levels_persmissions']);
                        $return['access_levels_persmissions'] = array_combine($return['access_levels_persmissions'], $return['access_levels_persmissions']);
                    }
                }
            } catch (\Exception $exc) {}
            $return['admin_templates'] = (\common\models\AdminTemplates::find()->where(['access_levels_id' => (int)$accessLevelId])
                ->indexBy('admin_template_id')->asArray(true)->all()
            );
        } catch (\Exception $exc) {
            \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'ErrorAdminfilesGetAccessLevelByIdArray');
        }
        return $return;
    }

    public function actionSortOrder() {
        $moved_id = (int) $_POST['sort_top'];
        $ref_array = (isset($_POST['top']) && is_array($_POST['top'])) ? array_map('intval', $_POST['top']) : array();
        if ($moved_id && in_array($moved_id, $ref_array)) {
            // {{ normalize
            $order_counter = 0;
            $order_list_r = tep_db_query(
                    "SELECT access_levels_id, sort_order " .
                    "FROM " . TABLE_ACCESS_LEVELS . " " .
                    "WHERE 1 " .
                    "ORDER BY sort_order, access_levels_name"
            );
            while ($order_list = tep_db_fetch_array($order_list_r)) {
                $order_counter++;
                tep_db_query("UPDATE " . TABLE_ACCESS_LEVELS . " SET sort_order='{$order_counter}' WHERE access_levels_id='{$order_list['access_levels_id']}' ");
            }
            // }} normalize
            $get_current_order_r = tep_db_query(
                    "SELECT access_levels_id, sort_order " .
                    "FROM " . TABLE_ACCESS_LEVELS . " " .
                    "WHERE access_levels_id IN('" . implode("','", $ref_array) . "') " .
                    "ORDER BY sort_order"
            );
            $ref_ids = array();
            $ref_so = array();
            while ($_current_order = tep_db_fetch_array($get_current_order_r)) {
                $ref_ids[] = (int) $_current_order['access_levels_id'];
                $ref_so[] = (int) $_current_order['sort_order'];
            }

            foreach ($ref_array as $_idx => $id) {
                tep_db_query("UPDATE " . TABLE_ACCESS_LEVELS . " SET sort_order='{$ref_so[$_idx]}' WHERE access_levels_id='{$id}' ");
            }
        }
    }

    public function actionPreview() {
        $this->layout = false;
        $item_id = (int) Yii::$app->request->post('item_id');

        $accessQuery = tep_db_query("select * from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . $item_id . "'");
        $access = tep_db_fetch_array( $accessQuery );
        if (is_array($access)) {
            echo '<div class="or_box_head">' . $access['access_levels_name'] . '</div>';
            echo '<div class="btn-toolbar btn-toolbar-order">';
            echo '<a class="btn btn-edit btn-no-margin" href="' . Yii::$app->urlManager->createUrl(['adminfiles/edit', 'item_id' => $item_id]) . '">' . IMAGE_EDIT . '</a>';
            echo '<button class="btn btn-delete" onclick="accessDelete(\'' . $item_id . '\')">' . IMAGE_DELETE . '</button>';

            echo '<button class="btn btn-copy btn-no-margin" onclick="confirmAclCopy(' . $item_id . ')">' . IMAGE_COPY_TO . '</button>';
            echo '<button class="btn btn-copy" onclick="confirmAclDublicate(' . $item_id . ')">' . IMAGE_DUBLICATE . '</button>';

            /**
             * @var $ext \common\extensions\Messages\Messages
             */
            if ($ext = \common\helpers\Extensions::isAllowed('Messages', 'allowed')) {
                $ext::adminActionPreEdit($access);
            }

            /**
             * @var $handlers \common\extensions\Handlers\Handlers
             */
            if ($handlers = \common\helpers\Extensions::isAllowed('Handlers')) {
                $handlers::adminActionPreEdit($item_id);
            }
            echo '</div>';
        }
    }

    public function actionConfirmAclCopy() {
        \common\helpers\Translation::init('admin/adminfiles');
        $this->layout = false;
        $item_id = (int) Yii::$app->request->post('item_id');
        $acl = \common\models\AccessLevels::find()->where(['access_levels_id' => $item_id])->one();
        if ($acl) {
            $aclList = [];
            foreach ( \common\models\AccessLevels::find()->where(['NOT IN', 'access_levels_id', $item_id])->all() as $record) {
                $aclList[$record->access_levels_id] = $record->access_levels_name;
            }
            $params = [
                'obj' => $acl,
                'aclList' => $aclList,
            ];
            return $this->render('confirmaclcopy.tpl', $params);
        }
    }

    public function actionAclCopy() {
        $item_id = (int) Yii::$app->request->post('item_id');
        $move_to_acl_id = (int) Yii::$app->request->post('move_to_acl_id');
        $acl = \common\models\AccessLevels::find()->where(['access_levels_id' => $item_id])->one();
        $target = \common\models\AccessLevels::find()->where(['access_levels_id' => $move_to_acl_id])->one();
        if ($acl && $target) {

            if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
                $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
                ($logUniversal
                    ->setType($logUniversal::ULT_ACCESS_LEVEL_UPDATE)
                    ->setRelation($move_to_acl_id)
                    ->setBeforeArray($this->getAccessLevelByIdArray($move_to_acl_id))
                );
            }

            $target->access_levels_persmissions = $acl->access_levels_persmissions;
            $target->save(false);

            if (isset($logUniversal)) {
                ($logUniversal
                    ->setAfterArray($this->getAccessLevelByIdArray($move_to_acl_id))
                    ->doSave(true)
                );
                unset($logUniversal);
            }
        }
    }

    public function actionConfirmAclDublicate() {
        \common\helpers\Translation::init('admin/adminfiles');
        $this->layout = false;
        $item_id = (int) Yii::$app->request->post('item_id');
        $acl = \common\models\AccessLevels::find()->where(['access_levels_id' => $item_id])->one();
        if ($acl) {
            $params = [
                'obj' => $acl,
            ];
            return $this->render('confirmacldublicate.tpl', $params);
        }
    }

    public function actionAclDublicate() {
        $item_id = (int) Yii::$app->request->post('item_id');
        $new_title = (string) tep_db_prepare_input(Yii::$app->request->post('new_title', ''));
        $acl = \common\models\AccessLevels::find()->where(['access_levels_id' => $item_id])->asArray()->one();
        if ($acl) {

            if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
                $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
                ($logUniversal
                    ->setType($logUniversal::ULT_ACCESS_LEVEL_CREATE)
                    ->setBeforeArray($this->getAccessLevelByIdArray(0))
                );
            }

            unset($acl['access_levels_id']);
            $acl['access_levels_name'] = $new_title;
            $dublicate = new \common\models\AccessLevels();
            $dublicate->loadDefaultValues();
            $dublicate->setAttributes($acl);
            $dublicate->save(false);

            if (isset($logUniversal)) {
                ($logUniversal
                    ->setRelation($dublicate->access_levels_id)
                    ->setAfterArray($this->getAccessLevelByIdArray($dublicate->access_levels_id))
                    ->doSave(true)
                );
                unset($logUniversal);
            }
        }
    }

    public function actionEdit() {
        \common\helpers\Translation::init('admin/adminfiles');
        \common\helpers\Translation::init('admin/categories');

        if (Yii::$app->request->isPost) {
            $item_id = (int) Yii::$app->request->post('item_id');
            $this->layout = false;
        } else {
            $item_id = (int) Yii::$app->request->get('item_id');
        }

        $this->selectedMenu = array('administrator', 'adminfiles');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('adminfiles/index'), 'title' => HEADING_TITLE);

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#save_item_form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        if ($item_id > 0) {
            $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl(['adminfiles/export-acl', 'item_id' => $item_id]).'" class="btn btn-primary backup"><i class="icon-file-text"></i>' . TEXT_EXPORT . '</a>';
            $this->topButtons[] = '<a href="javascript:void(0)" class="btn-import btn btn-primary backup"><i class="icon-file-text"></i>' . TEXT_IMPORT . '</a>';
        }

        $this->view->headingTitle = HEADING_TITLE;

        if ($item_id > 0) {
            $actionName = IMAGE_EDIT;
        } else {
            $actionName = IMAGE_INSERT;
        }

        $accessQuery = tep_db_query("select * from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . $item_id . "'");
        $access = tep_db_fetch_array( $accessQuery );
        $accessInfo = new \objectInfo( $access );


        $aclTree = \common\helpers\Acl::buildTree($accessInfo->access_levels_persmissions ?? null);

        return $this->render('edit', [
            'actionName' => $actionName,
            'accessInfo' => $accessInfo,
            'aclTree' => $aclTree,
            'item_id' => $item_id,
            'templatesList' => \common\helpers\AdminTemplates::templatesList($item_id),
        ]);
    }

    public function actionSubmit() {
        \common\helpers\Translation::init('admin/adminfiles');

        $item_id = (int) Yii::$app->request->post('item_id');

        if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
            $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
            $logUniversal->setBeforeArray($this->getAccessLevelByIdArray($item_id));
        }

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
            if (isset($logUniversal)) {
                $logUniversal->setType($logUniversal::ULT_ACCESS_LEVEL_UPDATE);
            }
            tep_db_perform(TABLE_ACCESS_LEVELS, $sql_data_array, 'update', "access_levels_id = '" . (int) $item_id . "'");
        } else {
            if (isset($logUniversal)) {
                $logUniversal->setType($logUniversal::ULT_ACCESS_LEVEL_CREATE);
            }
            tep_db_perform(TABLE_ACCESS_LEVELS, $sql_data_array);
            $item_id = tep_db_insert_id();
        }

        \common\helpers\AdminTemplates::save(Yii::$app->request->post('pages'), $item_id);

        if (isset($logUniversal)) {
            ($logUniversal
                ->setRelation((int)$item_id)
                ->setAfterArray($this->getAccessLevelByIdArray($item_id))
                ->doSave(true)
            );
            unset($logUniversal);
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
        echo '<script> window.location.replace("'. Yii::$app->urlManager->createUrl(['adminfiles/edit', 'item_id' => $item_id]) . '");</script>';
        //return $this->actionEdit();
    }

    public function actionDelete() {
        $item_id = (int) Yii::$app->request->post('item_id');

        if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
            $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
            ($logUniversal
                ->setType($logUniversal::ULT_ACCESS_LEVEL_DELETE)
                ->setRelation($item_id)
                ->setBeforeArray($this->getAccessLevelByIdArray($item_id))
            );
        }

        tep_db_query("delete from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . $item_id . "'");

        if (isset($logUniversal)) {
            ($logUniversal
                ->setAfterArray($this->getAccessLevelByIdArray($item_id))
                ->doSave(true)
            );
            unset($logUniversal);
        }
    }

    public function actionRecalcAcl() {
        $this->layout = false;
        $persmissions = Yii::$app->request->post('persmissions');

        $aclTree = \common\helpers\Acl::buildTree($persmissions);

        return $this->render('recalc-acl', [
            'aclTree' => $aclTree,
        ]);
    }

    public function actionExportAcl() {
        $access_levels_id = Yii::$app->request->get('item_id');
        $this->layout = false;

        $xml = new \yii\web\XmlResponseFormatter;
        $xml->rootTag = 'Acl';
        Yii::$app->response->format = 'custom_xml';
        Yii::$app->response->formatters['custom_xml'] = $xml;


        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'text/xml; charset=utf-8');
        $headers->add('Content-Disposition', 'attachment; filename="admin-acl.xml"');
        $headers->add('Pragma', 'no-cache');

        $acl = \common\models\AccessLevels::find()->where(['access_levels_id' => $access_levels_id])->one();
        if (is_string($acl->access_levels_persmissions)) {
            $selectedIds = explode(",", $acl->access_levels_persmissions);
        }
        if (!is_array($selectedIds)) {
            $selectedIds = [];
        }
        unset($acl);

        $acl = \common\models\AccessControlList::find()
                ->select(['access_control_list_key'])
                ->where(['IN', 'access_control_list_id', $selectedIds])
                ->orderBy('sort_order')
                ->asArray()
                ->all();

        $response = [];
        foreach ($acl as $item) {
            $response[] = $item['access_control_list_key'];
        }
        return $response;
    }

    public function actionImportAcl() {
        if (isset($_FILES['file']['tmp_name'])) {
            $xmlfile = file_get_contents($_FILES['file']['tmp_name']);
            $ob = simplexml_load_string($xmlfile);
            if (isset($ob->item)) {

                $access_levels_id = (int) Yii::$app->request->get('item_id');
                $selectedIds = [];
                foreach ($ob->item as $key) {
                     $acl = \common\models\AccessControlList::find()->where(['access_control_list_key' => (string)$key])->one();
                     if (is_object($acl)) {
                         $selectedIds[] = $acl->access_control_list_id;
                     }
                }
                if (count($selectedIds) > 0) {
                    $access_levels_persmissions = implode(",", $selectedIds);
                } else {
                    $access_levels_persmissions = '';
                }
                $al = \common\models\AccessLevels::find()->where(['access_levels_id' => $access_levels_id])->one();
                if (is_object($al)) {

                    if (\common\helpers\Acl::checkExtensionAllowed('ReportUniversalLog')) {
                        $logUniversal = \common\extensions\ReportUniversalLog\classes\LogUniversal::getInstance();
                        ($logUniversal
                            ->setType($logUniversal::ULT_ACCESS_LEVEL_UPDATE)
                            ->setRelation($access_levels_id)
                            ->setBeforeArray($this->getAccessLevelByIdArray($access_levels_id))
                        );
                    }

                    $al->access_levels_persmissions = $access_levels_persmissions;
                    $al->save();

                    if (isset($logUniversal)) {
                        ($logUniversal
                            ->setAfterArray($this->getAccessLevelByIdArray($access_levels_id))
                            ->doSave(true)
                        );
                        unset($logUniversal);
                    }
                }
            }
            unlink($_FILES['file']['tmp_name']);
        }
    }

}
