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

use backend\services\ConfigurationService;
use Yii;

/**
 * default controller to handle user requests.
 */
class Orders_statusController extends Sceleton  {

    public $acl = ['TEXT_SETTINGS', 'BOX_LOCALIZATION_ORDERS_STATUS', 'BOX_ORDERS_STATUS'];
    /** @var ConfigurationService */
    private $configurationService;

    public function __construct(
        $id,
        $module,
        ConfigurationService $configurationService,
        array $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->configurationService = $configurationService;
    }

    public function actionIndex() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $type_id = (int) Yii::$app->request->get('type_id', 1);
        $row = (int) Yii::$app->request->get('row');
        $osgID = (int) Yii::$app->request->get('osgID');

        $this->selectedMenu = array('settings', 'status', 'orders_status');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders_status/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="' . \Yii::$app->urlManager->createUrl(['orders_status/edit', 'type_id' => $type_id]) . '" class="btn btn-primary">' . TEXT_INFO_HEADING_NEW_ORDERS_STATUS . '</a>';

        $this->view->StatusTable = array(
            array(
                'title' => TABLE_HEADING_ORDERS_STATUS,
                'not_important' => 0,
            ),
        );

        // \common\helpers\Status::getStatusGroupsList(true)
        $ordersStatusGroups = [];
        $ordersStatusGroups[''] = TEXT_ALL_ORDERS_STATUS_GROUPS;
        $orders_status_groups_query = tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int) $languages_id . "' and orders_status_type_id = '" . $type_id . "'");
        while ($orders_status_groups = tep_db_fetch_array($orders_status_groups_query)) {
            $ordersStatusGroups[$orders_status_groups['orders_status_groups_id']] = $orders_status_groups['orders_status_groups_name'];
        }

        $this->view->filterStatusGroups = \yii\helpers\Html::dropDownList('osgID', (int) $osgID, $ordersStatusGroups, ['class' => 'form-control', 'onchange' => 'return applyFilter();']);

        $messages = [];
        if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
            if (!is_array($messages)) $messages = [];
        }
        return $this->render('index', [
                    'messages' => $messages,
                    'types' => \common\helpers\Status::getStatusTypeList(false),
                    'type_id' => $type_id,
                    'row' => $row,
        ]);
    }

    public function actionList()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and (os.orders_status_name like '%" . $keywords . "%')";
        }

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $filter);

        if ($filter['type_id'] > 0) {
            $search .= " and osg.orders_status_type_id = '" . (int)$filter['type_id'] . "'";
        }

        if ($filter['osgID'] > 0) {
            $search .= " and os.orders_status_groups_id = '" . (int)$filter['osgID'] . "'";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "os.orders_status_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "os.orders_status_name";
                    break;
            }
        } else {
            $orderBy = "os.orders_status_name";
        }

        //$orders_status_query_raw = "select os.orders_status_id, os.orders_status_name, osg.orders_status_groups_name, ost.orders_status_type_name from " . TABLE_ORDERS_STATUS . " as os left join " . TABLE_ORDERS_STATUS_GROUPS . " as osg on os.orders_status_groups_id=osg.orders_status_groups_id left join " . TABLE_ORDERS_STATUS_TYPE . " as ost on osg.orders_status_type_id=ost.orders_status_type_id where os.language_id = '" . (int)$languages_id . "' and osg.language_id = '" . (int)$languages_id . "' and ost.language_id = '" . (int)$languages_id . "' " . $search . " order by orders_status_type_name, orders_status_groups_name, " . $orderBy;
        $orders_status_query_raw = "select os.orders_status_id, os.orders_status_name, osg.orders_status_groups_name, os.hidden from " . TABLE_ORDERS_STATUS . " as os left join " . TABLE_ORDERS_STATUS_GROUPS . " as osg on os.orders_status_groups_id=osg.orders_status_groups_id where os.language_id = '" . (int)$languages_id . "' and osg.language_id = '" . (int)$languages_id . "' " . $search . " order by os.hidden, osg.orders_status_groups_id, orders_status_groups_name, " . $orderBy;
        $orders_status_split = new \splitPageResults($current_page_number, $length, $orders_status_query_raw, $orders_status_query_numrows);
        $orders_status_query = tep_db_query($orders_status_query_raw);

        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
            $defaultPaymentOSText = '';
            if ($this->configurationService->isDefaultOrderStatusIdForOnlinePayment((int) $orders_status['orders_status_id'])) {
                $defaultPaymentOSText = sprintf(' <b>(%s)</b> ', DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS);
            }
            if ($this->configurationService->isDefaultOrderStatusIdForOnlinePaymentSuccess((int)$orders_status['orders_status_id'])) {
                $defaultPaymentOSText .= sprintf(' <b>(%s)</b> ', TEXT_DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS);
            }
            $responseList[] = array(
                '<div class="wrap ' . (!empty($orders_status['hidden'])?' dis_module':'') . '"><span class="or-st-color">' /*. $orders_status['orders_status_type_name'] . '/'*/ . $orders_status['orders_status_groups_name'] . '</span>/' . (DEFAULT_ORDERS_STATUS_ID == $orders_status['orders_status_id']? '<b>' . $orders_status['orders_status_name'] . ' (' . TEXT_DEFAULT . ')</b>': $orders_status['orders_status_name']) . $defaultPaymentOSText . tep_draw_hidden_field('id', $orders_status['orders_status_id'], 'class="cell_identify"') . '</div>',
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_status_query_numrows,
            'recordsFiltered' => $orders_status_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);

    }

    public function actionStatusactions() {
      $languages_id = \Yii::$app->settings->get('languages_id');

      \common\helpers\Translation::init('admin/orders_status');

        $orders_status_id = Yii::$app->request->post('orders_status_id', 0);
        $this->layout = false;
        if ($orders_status_id) {
            $ostatus = tep_db_fetch_array(tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' and orders_status_id='" . (int)$orders_status_id . "'"));
            $oInfo = new \objectInfo($ostatus, false);

            if (is_object($oInfo)) {
                echo '<div class="or_box_head">' . $oInfo->orders_status_name . '</div>';
                  $status_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where orders_status = '" . (int)$orders_status_id . "'");
                $status = tep_db_fetch_array($status_query);


                $orders_status_inputs_string = '';
                $languages = \common\helpers\Language::get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $orders_status_inputs_string .= '<div class="col_desc">' . $languages[$i]['image'] . '&nbsp;' . \common\helpers\Order::get_order_status_name($oInfo->orders_status_id, $languages[$i]['id']) . '</div>';
                }

                $gets = array_filter(\Yii::$app->request->getQueryParams());
                $gets['orders_status_id'] = $orders_status_id;

                echo $orders_status_inputs_string;
                echo '<div class="btn-toolbar btn-toolbar-order">';
                echo '<a class="btn btn-edit btn-no-margin" href="' . \Yii::$app->urlManager->createUrl(['orders_status/edit'] + $gets). '">' . IMAGE_EDIT . '</a><button class="btn btn-delete" onclick="statusDelete('.$orders_status_id.')">' . IMAGE_DELETE . '</button>';
                echo '</div>';
            }

        }

    }

    public function actionEdit() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/orders_status');
        \common\helpers\Translation::init('admin/email/templates');

        $this->topButtons[] = '<span class="btn btn-confirm">' . IMAGE_SAVE . '</span>';

        $orders_status_template = [''=>''] + \common\helpers\Mail::emailTemplatesList();

        $orders_status_id = Yii::$app->request->get('orders_status_id', 0);
        $ostatus = tep_db_fetch_array(tep_db_query("select * from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' and orders_status_id='" . (int)$orders_status_id . "'"));
        $oInfo = new \objectInfo($ostatus, false);
        $oInfo->orders_status_id = $oInfo->orders_status_id ?? null;
        $oInfo->orders_status_groups_id = $oInfo->orders_status_groups_id ?? null;
        $oInfo->order_evaluation_state_id = $oInfo->order_evaluation_state_id ?? null;

        $orders_status_inputs_string = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $orders_status_inputs_string[$languages[$i]['id']] = \common\helpers\Html::input(
                'text',
                'orders_status_name[' . $languages[$i]['id'] . ']',
                \common\helpers\Order::get_order_status_name($oInfo->orders_status_id, $languages[$i]['id']),
                ['class' => 'form-control']
            );
        }


        if ($orders_status_id) {
            $title = TEXT_INFO_HEADING_EDIT_ORDERS_STATUS;
        } else {
            $title =  TEXT_INFO_HEADING_NEW_ORDERS_STATUS;
        }

        $this->selectedMenu = array('settings', 'status', 'orders_status');
        $this->view->headingTitle = $title;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders_status/index'), 'title' => $title);
        //$this->topButtons[] = '<a href="#" class="create_item" onclick="return statusEdit(0)">'.TEXT_INFO_HEADING_NEW_ORDERS_STATUS.'</a>';

        $platforms = \common\classes\platform::getList(false);
        $designTemplates = [];
        $emailDesignTemplate = [];
        foreach ($platforms as $platform) {
            $theme_id = \common\models\PlatformsToThemes::findOne($platform['id'])->theme_id;
            $theme_name = \common\models\Themes::findOne(['id' => $theme_id])->theme_name;
            $templates = \common\models\ThemesSettings::find()
                ->select(['setting_value'])
                ->where([
                    'theme_name' => $theme_name,
                    'setting_group' => 'added_page',
                    'setting_name' => 'email',
                ])
                ->asArray()
                ->all();

            $designTemplates[$platform['id']][] = TEXT_DEFAULT;
            foreach ($templates as $template) {
                $designTemplates[$platform['id']][\common\classes\design::pageName($template['setting_value'])] = $template['setting_value'];
            }

            $emailDesignTemplate[$platform['id']] = \common\models\OrdersStatusToDesignTemplate::findOne([
                    'orders_status_id' => $orders_status_id,
                    'platform_id' => $platform['id'],
            ])->email_design_template ?? null;
        }
        $osOesList = false;
        $oInfo->orders_status_send_ga = 0;
        $osgRecord = \common\models\OrdersStatusGroups::findOne(['orders_status_groups_id' => $oInfo->orders_status_groups_id]);
        if ($orders_status_id == 0 OR $osgRecord instanceof \common\models\OrdersStatusGroups) {
            if ($orders_status_id > 0 AND $osgRecord->orders_status_type_id != \common\helpers\Order::getStatusTypeId()) {
                unset($oInfo->orders_status_allocate_allow);
            } elseif ($orders_status_id == 0) {
                $oInfo->orders_status_allocate_allow = 0;
            }
            if ($orders_status_id == 0 OR $osgRecord->orders_status_type_id == \common\helpers\Order::getStatusTypeId()) {
                $osOesList = [0 => ''];
                foreach (\common\helpers\Order::getEvaluationStateArray() as $oesId => $oesArray) {
                    $osOesList[$oesId] = (defined('TEXT_EVALUATION_STATE_LONG_' . $oesArray['key']) ? constant('TEXT_EVALUATION_STATE_LONG_' . $oesArray['key']) : $oesArray['long']);
                }
                unset($oesArray);
                unset($oesId);
            }
            if (\common\helpers\Acl::checkExtensionAllowed('PurchaseOrders') &&
               ($orders_status_id == 0 OR $osgRecord->orders_status_type_id == \common\extensions\PurchaseOrders\helpers\PurchaseOrder::getStatusTypeId())) {
                $osOesListPointer = &$osOesList;
                if (is_array($osOesList)) {
                    $stList = \common\helpers\Status::getStatusTypeList();
                    unset($osOesList[0]);
                    $osOesList = [0 => '', $stList[\common\helpers\Order::getStatusTypeId()] => $osOesList, $stList[\common\extensions\PurchaseOrders\helpers\PurchaseOrder::getStatusTypeId()] => []];
                    $osOesListPointer = &$osOesList[$stList[\common\extensions\PurchaseOrders\helpers\PurchaseOrder::getStatusTypeId()]];
                    unset($stList);
                } else {
                    $osOesListPointer = [0 => ''];
                }
                foreach (\common\extensions\PurchaseOrders\helpers\PurchaseOrder::getEvaluationStateArray() as $poesId => $poesArray) {
                    $osOesListPointer[$poesId] = (defined('TEXT_EVALUATION_STATE_LONG_' . $poesArray['key']) ? constant('TEXT_EVALUATION_STATE_LONG_' . $poesArray['key']) : $poesArray['long']);
                }
                unset($osOesListPointer);
                unset($poesArray);
                unset($poesId);
            }
            $oInfo->orders_status_send_ga = (int)($osgRecord->orders_status_groups_send_ga ?? null);
        }
        unset($osgRecord);

        $comment_templates['selected'] = $oInfo->comment_template_id ?? null;
        $comment_templates['items'] = [''=>''];
        $comment_templates['options'] = [];
        foreach(\common\helpers\CommentTemplate::getActiveVariants($comment_templates['selected']) as $variant){
            $comment_templates['items'][$variant['id']] = $variant['text'];
            //$comment_templates['options']['items'] = $variant['visibility'];
        }
        //TODO: need show/hide item list according visibility (rel orders_status_groups_id select)

        $orders_status_template_sms = [['id' => '', 'text' => '']];
        foreach (\common\models\SmsTemplates::find()->groupBy(['sms_templates_key'])->orderBy(['sms_templates_key' => SORT_ASC])->asArray(true)->all() as $smsTemplatesRecord) {
            $orders_status_template_sms[] = ['id' => $smsTemplatesRecord['sms_templates_key'], 'text' => $smsTemplatesRecord['sms_templates_key']];
        }
        $gets = array_filter(\Yii::$app->request->getQueryParams());
        $gets['orders_status_id'] = $oInfo->orders_status_id;
        $typeId = (int)\Yii::$app->request->get('type_id', 0);


        return $this->render('edit', [
            'oInfo' => $oInfo,
            'actionUrl' => Yii::$app->urlManager->createUrl(['orders_status/save'] + $gets),
            'cancelUrl' => Yii::$app->urlManager->createUrl(['orders_status/index'] + $gets),
            'orders_status_id' => $orders_status_id,
            'orders_status_template' => $orders_status_template,
            'orders_status_template_sms' => $orders_status_template_sms,
            'comment_templates' => $comment_templates,
            'oInfo_orders_status_id' => $oInfo->orders_status_id ? $oInfo->orders_status_id : 0,
            'orders_status_inputs_string' => $orders_status_inputs_string,
            'languages' => $languages,
            'platforms' => $platforms,
            'designTemplates' => $designTemplates,
            'emailDesignTemplate' => $emailDesignTemplate,
            'typeId' => $typeId,
            'osOesList' => $osOesList
        ]);
    }

    public function actionSave()
    {
        \common\helpers\Translation::init('admin/orders_status');
        $orders_status_id = intval(Yii::$app->request->get('orders_status_id', 0));
        $orders_status_groups_id = intval(Yii::$app->request->post('orders_status_groups_id', 0));
        $order_evaluation_state_id = Yii::$app->request->post('order_evaluation_state_id', false);
        $order_evaluation_state_default = (int)Yii::$app->request->post('order_evaluation_state_default');
        $hidden = (int)Yii::$app->request->post('hidden', 0);

        if ($orders_status_id == 0) {
            $next_id_query = tep_db_query("select max(orders_status_id) as orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_id <> '99999'");//paypal
            $next_id = tep_db_fetch_array($next_id_query);
            $insert_id = $next_id['orders_status_id'] + 1;
        }

        if ($order_evaluation_state_id !== false) {
            $osgRecord = \common\models\OrdersStatusGroups::findOne(['orders_status_groups_id' => $orders_status_groups_id]);
            if ($osgRecord instanceof \common\models\OrdersStatusGroups) {
                $esArray = [];
                if ($osgRecord->orders_status_type_id == \common\helpers\Order::getStatusTypeId()) {
                    $esArray = \common\helpers\Order::getEvaluationStateArray();
                } elseif (\common\helpers\Acl::checkExtensionAllowed('PurchaseOrders') && $osgRecord->orders_status_type_id == \common\extensions\PurchaseOrders\helpers\PurchaseOrder::getStatusTypeId()) {
                    $esArray = \common\extensions\PurchaseOrders\helpers\PurchaseOrder::getEvaluationStateArray();
                }
                if (isset($esArray[$order_evaluation_state_id])) {
                    //\common\models\OrdersStatus::updateAll(['order_evaluation_state_id' => 0], ['order_evaluation_state_id' => $order_evaluation_state_id]);
                    if ($order_evaluation_state_default > 0) {
                        \common\models\OrdersStatus::updateAll(['order_evaluation_state_default' => 0], ['order_evaluation_state_id' => $order_evaluation_state_id]);
                    }
                } else {
                    $order_evaluation_state_id = 0;
                    $order_evaluation_state_default = 0;
                }
                unset($esArray);
            } else {
                $order_evaluation_state_id = 0;
                $order_evaluation_state_default = 0;
            }
            unset($osgRecord);
        }

        $languages = \common\helpers\Language::get_languages(true);
        $orders_status_name_array = $_POST['orders_status_name'];
        $orders_status_name_array = (is_array($orders_status_name_array) ? $orders_status_name_array : []);
        $orders_status_name_default = '';
        foreach ($orders_status_name_array as $key => &$value) {
            $value = trim($value);
            if ($value == '') {
                unset($orders_status_name_array[$key]);
            }
            if ($orders_status_name_default == '') {
                $orders_status_name_default = $value;
            }
            unset($value);
        }
        if (count($orders_status_name_array) == 0) {
            echo json_encode([
                'message' => ('Status name can\'t be empty!'),
                'messageType' => 'alert-error'
            ]);
            return false;
        }
        $language_installed_array = [];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];
            $language_installed_array[] = $language_id;

            $oOrdersStatus = \common\models\OrdersStatus::findOne([
                'orders_status_id' => $orders_status_id,
                'language_id' => (int)$language_id
            ]);

            $action = 'updated';
            $added = false;
            if (!($oOrdersStatus instanceof \common\models\OrdersStatus)) {
                $added = $insert_id;
                $action = 'added';
                $oOrdersStatus = new \common\models\OrdersStatus();
                $oOrdersStatus->language_id = $language_id;
                $oOrdersStatus->orders_status_id = (($orders_status_id == 0) ? $insert_id : $orders_status_id);
            }
            $oOrdersStatus->orders_status_template = tep_db_prepare_input(Yii::$app->request->post('orders_status_template'));
            $oOrdersStatus->comment_template_id = intval(tep_db_prepare_input(Yii::$app->request->post('comment_template_id')));
            $oOrdersStatus->orders_status_template_confirm = tep_db_prepare_input(Yii::$app->request->post('orders_status_template_confirm'));
            $oOrdersStatus->orders_status_template_sms = tep_db_prepare_input(Yii::$app->request->post('orders_status_template_sms'));
            $oOrdersStatus->automated = (int)Yii::$app->request->post('automated');
            $oOrdersStatus->orders_status_groups_id = $orders_status_groups_id;
            if (!isset($orders_status_name_array[$language_id]) AND (trim($oOrdersStatus->orders_status_name) != '')) {
                $orders_status_name_array[$language_id] = trim($oOrdersStatus->orders_status_name);
            }
            $oOrdersStatus->orders_status_name = tep_db_prepare_input(isset($orders_status_name_array[$language_id]) ? $orders_status_name_array[$language_id] : $orders_status_name_default);
            if ($order_evaluation_state_id !== false) {
                $oOrdersStatus->order_evaluation_state_id = $order_evaluation_state_id;
                $oOrdersStatus->order_evaluation_state_default = $order_evaluation_state_default;
            }
            $oOrdersStatus->orders_status_allocate_allow = (int)Yii::$app->request->post('orders_status_allocate_allow');
            $oOrdersStatus->orders_status_release_deferred = (int)Yii::$app->request->post('orders_status_release_deferred');
            $oOrdersStatus->orders_status_send_ga = -1;//(int)Yii::$app->request->post('orders_status_send_ga');
            $oOrdersStatus->hidden = $hidden;
            try {
                $oOrdersStatus->save(false);
            } catch (\Exception $e) {
                \Yii::warning($e->getMessage() . ' '. $e->getTraceAsString());
            }
        }
        if (count($language_installed_array) > 0) {
            \common\models\OrdersStatus::deleteAll(['not in', 'language_id', $language_installed_array]);
        }

        if ($orders_status_id == 0) {
            $orders_status_id = $insert_id;
        }

        if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
            tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($orders_status_id) . "' where configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
        }
        $defaultOnlinePaymentStatusChange = (int)\Yii::$app->request->post('defaultOnlinePaymentStatus', 0);
        if ($defaultOnlinePaymentStatusChange === 1) {
            $this->configurationService->setDefaultOrderStatusIdForOnlinePayment($orders_status_id);
        }
        $defaultOnlinePaymentSuccessStatusChange = (int)\Yii::$app->request->post('defaultOnlinePaymentSuccessStatus', 0);
        if ($defaultOnlinePaymentSuccessStatusChange > 0) {
            $this->configurationService->setDefaultOrderStatusIdForOnlinePaymentSuccess($orders_status_id);
        }
        $designTemplates = Yii::$app->request->post('designTemplates');
        $platforms = \common\classes\platform::getList(false);
        foreach ($platforms as $platform) {
            $designTemplates[$platform['id']];
            $template = \common\models\OrdersStatusToDesignTemplate::findOne([
                'orders_status_id' => $orders_status_id,
                'platform_id' => $platform['id']
            ]);
            if ($designTemplates[$platform['id']]) {
                if (!$template) {
                    $template = new \common\models\OrdersStatusToDesignTemplate();
                }
                $template->attributes = [
                    'orders_status_id' => $orders_status_id,
                    'platform_id' => $platform['id'],
                    'email_design_template' => $designTemplates[$platform['id']]
                ];
                $template->save();
            } elseif ($template) {
                $template->delete();
            }
        }

        if ($ext = \common\helpers\Extensions::isAllowed('OrderStatusRules')) {
            $ext::orderStatusSave($orders_status_id);
        }

        echo json_encode([
            'message' => ('Status ' . $action),
            'messageType' => 'alert-success',
            'added' => $added,
        ]);
    }

    public function actionOrderStatusRules()
    {
        if ($ext = \common\helpers\Extensions::isAllowed('OrderStatusRules')) {
            return $ext::orderStatusAction($this);
        }
        return '';
    }

    public function actionDelete() {
      global $language;
      \common\helpers\Translation::init('admin/orders_status');

        $orders_status_id =  (int) Yii::$app->request->post('orders_status_id', 0);

        if($orders_status_id) {
            $remove_status = true;
            $status = tep_db_fetch_array(tep_db_query(
                "SELECT COUNT(*) AS `count` FROM ".TABLE_ORDERS." WHERE orders_status='".$orders_status_id."' "
            ));
            $error = array();
            if ($orders_status_id == DEFAULT_ORDERS_STATUS_ID) {
                $remove_status = false;
                $error = array('message' => ERROR_REMOVE_DEFAULT_ORDER_STATUS, 'messageType' => 'alert-danger');
            } elseif ($this->configurationService->isDefaultOrderStatusIdForOnlinePayment($orders_status_id)) {
                $remove_status = false;
                $error = array('message' => ERROR_REMOVE_DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS, 'messageType' => 'alert-danger');
            } elseif ($this->configurationService->isDefaultOrderStatusIdForOnlinePaymentSuccess($orders_status_id)) {
                $remove_status = false;
                $error = array('message' => TEXT_ERROR_REMOVE_DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS, 'messageType' => 'alert-danger');
            } elseif ($status['count'] > 0) {
                $remove_status = false;
                $error = array('message' => ERROR_STATUS_USED_IN_ORDERS, 'messageType' => 'alert-danger');
            } else {
                $history_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_status_id = '" . (int)$orders_status_id . "'");
                $history = tep_db_fetch_array($history_query);
                if ($history['count'] > 0) {
                    $remove_status = false;
                    $error = array('message' => ERROR_STATUS_USED_IN_HISTORY, 'messageType' => 'alert-danger');
                }
            }
            if (!$remove_status) {
                ?>
              <div class="alert fade in <?=$error['messageType']?>">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"><?=$error['message']?></span>
              </div>
                <?php

            } else {
                $orders_status_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
                $orders_status = tep_db_fetch_array($orders_status_query);

                if ($orders_status['configuration_value'] == $orders_status_id) {
                  tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_ORDERS_STATUS_ID'");
                }

                if ($this->configurationService->isDefaultOrderStatusIdForOnlinePayment($orders_status_id)) {
                    $this->configurationService->setDefaultOrderStatusIdForOnlinePayment((int) $orders_status['configuration_value']);
                }

                tep_db_query("delete from " . TABLE_ORDERS_STATUS . " where orders_status_id = '" . tep_db_input($orders_status_id) . "'");
                echo 'reset';
            }

        }

    }
}
