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

/**
 * default controller to handle user requests.
 */
class Orders_status_groupsController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_LOCALIZATION_ORDERS_STATUS', 'BOX_ORDERS_STATUS_GROUPS'];

    public function actionIndex() {

        $this->selectedMenu = array('settings', 'status', 'orders_status_groups');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders_status_groups/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="btn btn-primary" onclick="return statusGroupEdit(0)">'.TEXT_INFO_HEADING_NEW_ORDERS_STATUS_GROUP.'</a>';

        $this->view->StatusGroupTable = array(
            array(
                'title' => TABLE_HEADING_ORDERS_STATUS_GROUP,
                'not_important' => 0,
            ),
        );

        $this->view->filterStatusTypes = \yii\helpers\Html::dropDownList('ostID', (int)Yii::$app->request->get('ostID', 0), \common\helpers\Status::getStatusTypeList(true), ['class'=>'form-control', 'onchange' => 'return applyFilter();']);

        $messages = [];
        if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
            if (!is_array($messages)) $messages = [];
        }
        return $this->render('index', array('messages' => $messages));
    }

    public function actionList() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and (osg.orders_status_groups_name like '%" . $keywords . "%')";
        }

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $filter);
        if ($filter['ostID'] > 0) {
            $search .= " and osg.orders_status_type_id = '" . (int) $filter['ostID'] . "'";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "osg.orders_status_groups_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "osg.orders_status_groups_id";
                    break;
            }
        } else {
            $orderBy = "osg.orders_status_groups_id";
        }

        $orders_status_groups_query_raw = "select osg.orders_status_groups_id, osg.orders_status_groups_name, osg.orders_status_groups_color, ost.orders_status_type_name from " . TABLE_ORDERS_STATUS_GROUPS . " as osg left join " . TABLE_ORDERS_STATUS_TYPE . " as ost on osg.orders_status_type_id=ost.orders_status_type_id where osg.language_id = '" . (int)$languages_id . "' and ost.language_id = '" . (int)$languages_id . "' " . $search . " order by osg.orders_status_type_id, " . $orderBy;
        $orders_status_groups_split = new \splitPageResults($current_page_number, $length, $orders_status_groups_query_raw, $orders_status_groups_query_numrows);
        $orders_status_groups_query = tep_db_query($orders_status_groups_query_raw);

        while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {

            $responseList[] = array(
                '<span class="or-st-color">' . $orders_status_groups['orders_status_type_name'] . '/</span>' . $orders_status_groups['orders_status_groups_name'] . tep_draw_hidden_field('id', $orders_status_groups['orders_status_groups_id'], 'class="cell_identify"'),
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_status_groups_query_numrows,
            'recordsFiltered' => $orders_status_groups_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionStatusactions() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/orders_status_groups');

        $orders_status_groups_id = Yii::$app->request->post('orders_status_groups_id', 0);
        $this->layout = false;
        if ($orders_status_groups_id) {
            $ostatus_groups = tep_db_fetch_array(tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "' and language_id = " . (int)$languages_id ));
            $oInfo = new \objectInfo($ostatus_groups, false);
            $heading = array();
            $contents = array();

            if (is_object($oInfo)) {
                echo '<div class="or_box_head">' . $oInfo->orders_status_groups_name . '</div>';

                $orders_status_inputs_string = '';
                $languages = \common\helpers\Language::get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $orders_status_inputs_string .= '<div class="col_desc">' . $languages[$i]['image'] . '&nbsp;' . \common\helpers\Order::orders_status_groups_name($oInfo->orders_status_groups_id, $languages[$i]['id']) . '</div>';
                }
                echo $orders_status_inputs_string;

                echo '<div class="btn-toolbar btn-toolbar-order">';
                echo '<button class="btn btn-edit btn-no-margin" onclick="statusGroupEdit(' . $orders_status_groups_id . ')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="statusGroupDelete(' . $orders_status_groups_id . ')">' . IMAGE_DELETE . '</button>';
                echo '</div>';
            }

        }
    }

    public function actionEdit() {
        \common\helpers\Translation::init('admin/orders_status_groups');
        \common\helpers\Translation::init('admin/orders_status');

        $orders_status_groups_id = Yii::$app->request->get('orders_status_groups_id', 0);
        $ostatus_groups = tep_db_fetch_array(tep_db_query("select * from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "'"));
        $oInfo = new \objectInfo($ostatus_groups, false);
        $oInfo->orders_status_groups_id = $oInfo->orders_status_groups_id ?? null;
        $oInfo->orders_status_type_id = $oInfo->orders_status_type_id ?? null;

        echo tep_draw_form('status_group', FILENAME_ORDERS_STATUS_GROUPS . '/save', 'orders_status_groups_id=' . $oInfo->orders_status_groups_id);
        if ($orders_status_groups_id) {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_ORDERS_STATUS_GROUP . '</div>';
        } else {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_ORDERS_STATUS_GROUP . '</div>';
        }

        $orders_status_inputs_string = '';
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $orders_status_inputs_string .= '<div class="langInput">' . $languages[$i]['image'] . tep_draw_input_field('orders_status_groups_name[' . $languages[$i]['id'] . ']', \common\helpers\Order::orders_status_groups_name($oInfo->orders_status_groups_id, $languages[$i]['id'])) . '</div>';
        }
        echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
        echo '<div class="main_title">' . TEXT_ORDERS_STATUS_TYPE . ':</div>';
        echo '<div class="main_value">' . \yii\helpers\Html::dropDownList('orders_status_type_id', $oInfo->orders_status_type_id, \common\helpers\Status::getStatusTypeList()) . '</div>';

        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_ORDERS_STATUS_GROUPS_NAME . '</div><div class="main_value">' . $orders_status_inputs_string . '</div></div>';

        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_ORDERS_STATUS_GROUPS_COLOR . '</div><div class="main_value">' . tep_draw_input_field('orders_status_groups_color', $oInfo->orders_status_groups_color ?? null) . '</div></div>';
        $osOesList = [0 => ''];
        foreach (\common\helpers\Order::getEvaluationStateArray() as $oesId => $oesArray) {
            $osOesList[$oesId] = (defined('TEXT_EVALUATION_STATE_LONG_' . $oesArray['key']) ? constant('TEXT_EVALUATION_STATE_LONG_' . $oesArray['key']) : $oesArray['long']);
        }
        unset($oesArray);
        unset($oesId);
        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_ORDERS_STATUS_GROUPS_ORDER_EVALUATION_STATE . '</div><div class="main_value">' . \yii\helpers\Html::dropDownList('order_group_evaluation_state_id', $oInfo->order_group_evaluation_state_id ?? null, $osOesList) . '</div></div>';
        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_ORDERS_STATUS_GROUPS_STORE_TEMPORARY . '</div><div class="main_value">' . \yii\helpers\Html::checkbox('orders_status_groups_store_temporary', $oInfo->orders_status_groups_store_temporary ?? null) . '</div></div>';
        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_ORDERS_STATUS_GROUPS_SEND_GA . '</div><div class="main_value">' . \yii\helpers\Html::checkbox('orders_status_groups_send_ga', $oInfo->orders_status_groups_send_ga ?? null) . '</div></div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="statusGroupSave(' . ($oInfo->orders_status_groups_id ? $oInfo->orders_status_groups_id : 0) . ')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
        echo '</div>';
        echo '</form>';
        echo '<script type="text/javascript">
            $(function(){
                $(\'input[type="checkbox"][name^="orders_status_groups_"]\').bootstrapSwitch({
                    onText: "' . SW_ON . '",
                    offText: "' . SW_OFF . '",
                    handleWidth: "20px",
                    labelWidth: "24px"
                });
            });
        </script>';
    }

    public function actionSave()
    {
        \common\helpers\Translation::init('admin/orders_status_groups');
        $orders_status_type_id = (int)Yii::$app->request->post('orders_status_type_id');
        $orders_status_groups_id = intval(Yii::$app->request->get('orders_status_groups_id', 0));
        $order_group_evaluation_state_id = (int)Yii::$app->request->post('order_group_evaluation_state_id', false);
        $orders_status_groups_name_array = tep_db_prepare_input(Yii::$app->request->post('orders_status_groups_name', array()));
        $orders_status_groups_name_array = (is_array($orders_status_groups_name_array) ? $orders_status_groups_name_array : array());
        $orders_status_groups_name_default = '';
        foreach ($orders_status_groups_name_array as $key => &$value) {
            $value = trim($value);
            if ($value == '') {
                unset($orders_status_groups_name_array[$key]);
            }
            if ($orders_status_groups_name_default == '') {
                $orders_status_groups_name_default = $value;
            }
            unset($value);
        }
        if (count($orders_status_groups_name_array) == 0) {
            echo json_encode([
                'message' => ('Status group name can\'t be empty!'),
                'messageType' => 'alert-error'
            ]);
            return false;
        }

        if ($orders_status_groups_id == 0) {
            $next_id_query = tep_db_query("select max(orders_status_groups_id) as orders_status_groups_id from " . TABLE_ORDERS_STATUS_GROUPS . " where 1");
            $next_id = tep_db_fetch_array($next_id_query);
            $insert_id = $next_id['orders_status_groups_id'] + 1;
        }
        $esArray = [];
        if ($orders_status_type_id == \common\helpers\Order::getStatusTypeId()) {
            $esArray = \common\helpers\Order::getEvaluationStateArray();
        } elseif (\common\helpers\Acl::checkExtensionAllowed('PurchaseOrders') && $orders_status_type_id == \common\extensions\PurchaseOrders\helpers\PurchaseOrder::getStatusTypeId()) {
            $esArray = \common\extensions\PurchaseOrders\helpers\PurchaseOrder::getEvaluationStateArray();
        }
        if (!isset($esArray[$order_group_evaluation_state_id])) {
            $order_group_evaluation_state_id = 0;
        }
        unset($esArray);
        $languages = \common\helpers\Language::get_languages(true);
        $language_installed_array = [];
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $language_id = $languages[$i]['id'];
            $language_installed_array[] = $language_id;

            if (!isset($orders_status_groups_name_array[$language_id])) {
                $orders_status_groups_record = \common\models\OrdersStatusGroups::findOne(['orders_status_groups_id' => $orders_status_groups_id, 'language_id' => $language_id]);
                if (($orders_status_groups_record instanceof \common\models\OrdersStatusGroups) AND (trim($orders_status_groups_record->orders_status_groups_name) != '')) {
                    $orders_status_groups_name_array[$language_id] = trim($orders_status_groups_record->orders_status_groups_name);
                }
                unset($orders_status_groups_record);
            }

            $sql_data_array = [
                'orders_status_groups_name' => (isset($orders_status_groups_name_array[$language_id]) ? $orders_status_groups_name_array[$language_id] : $orders_status_groups_name_default),
                'orders_status_groups_color' => tep_db_prepare_input(Yii::$app->request->post('orders_status_groups_color', '')),
                'orders_status_type_id' => $orders_status_type_id,
                'order_group_evaluation_state_id' => $order_group_evaluation_state_id,
                'orders_status_groups_store_temporary' => (int)Yii::$app->request->post('orders_status_groups_store_temporary'),
                'orders_status_groups_send_ga' => (int)Yii::$app->request->post('orders_status_groups_send_ga'),
            ];

            if ($orders_status_groups_id == 0) {
                $insert_sql_data = array('orders_status_groups_id' => $insert_id,
                                         'language_id' => $language_id);
                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                tep_db_perform(TABLE_ORDERS_STATUS_GROUPS, $sql_data_array);
                $action = 'added';
            } else {
                $check = tep_db_fetch_array(tep_db_query("select count(orders_status_groups_id) as orders_status_groups_exists from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "' and language_id = '" . (int)$language_id . "'"));
                if (!$check['orders_status_groups_exists']) {
                    $insert_sql_data = array('orders_status_groups_id' => $orders_status_groups_id,
                                             'language_id' => $language_id);
                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                    tep_db_perform(TABLE_ORDERS_STATUS_GROUPS, $sql_data_array);
                } else {
                    tep_db_perform(TABLE_ORDERS_STATUS_GROUPS, $sql_data_array, 'update', "orders_status_groups_id = '" . (int) $orders_status_groups_id . "' and language_id = '" . (int)$language_id . "'");
                }
                $action = 'updated';
            }

            if ($orders_status_groups_id == 0) {
                $orders_status_groups_id = tep_db_insert_id();
            }
        }
        if (count($language_installed_array) > 0) {
            \common\models\OrdersStatusGroups::deleteAll(['not in', 'language_id', $language_installed_array]);
        }

        echo json_encode(array('message' => 'Status Group ' . $action, 'messageType' => 'alert-success'));
    }

    public function actionDelete() {
        global $language;
        \common\helpers\Translation::init('admin/orders_status_groups');

        $orders_status_groups_id = Yii::$app->request->post('orders_status_groups_id', 0);

        if ($orders_status_groups_id) {

            $remove_status_group = true;
            $error = array();
            $status_group_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS_STATUS . " where orders_status_groups_id = '" . (int) $orders_status_groups_id . "'");
            $status_group = tep_db_fetch_array($status_group_query);
            if ($status_group['count'] > 0) {
                $remove_status_group = false;
                $error = array('message' => ERROR_STATUS_GROUPS_USED_IN_ORDERS_STATUS, 'messageType' => 'alert-danger');
            }
            if (!$remove_status_group) {
                ?>
                <div class="alert fade in <?= $error['messageType'] ?>">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"><?= $error['message'] ?></span>
                </div>
                <?php
            } else {
                tep_db_query("delete from " . TABLE_ORDERS_STATUS_GROUPS . " where orders_status_groups_id = '" . tep_db_input($orders_status_groups_id) . "'");
                echo 'reset';
            }
        }
    }

}
