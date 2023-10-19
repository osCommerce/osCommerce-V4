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

use backend\models\EP\DataSources;
use common\classes\platform_config;
use common\classes\platform;
use common\classes\order_total;
use common\classes\shipping;
use common\classes\payment;
use common\helpers\Acl;
use common\helpers\Output;
use common\helpers\Status;
use common\helpers\Order as OrderHelper;
use common\helpers\Translation;
use common\models\Orders;
use common\helpers\Html;
use common\models\EcommerceTracking;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Yii;

class OrdersGaController extends Sceleton {

    public $acl = ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS_GA'];

    /**
     * Index action is the default action in a controller.
     */
    public function __construct($id, $module = '') {
        parent::__construct($id, $module);
    }

    public function actionIndex() {

        \common\helpers\Translation::init('admin/orders');
        
        $this->selectedMenu = array('customers', 'orders');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders-ga/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['orders-ga/import']) . '" class="btn btn-import">' . TEXT_IMPORT . '</a>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['orders-ga/update-stat']) . '" class="btn btn-import">' . TEXT_UPDATE . '</a>';

        $this->view->headingTitle = HEADING_TITLE;
        $this->view->ordersTable = [];
        $this->view->ordersTable[] = array(
            'title' => '<input type="checkbox" class="uniform batch batch-send">',
            'not_important' => 2
        );
        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_DETAILS,
            'not_important' => 0
        );
        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_ORDER_TOTAL,
            'not_important' => 0
        );
        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_DATE_PURCHASED,
            'not_important' => 0
        );
        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_STATUS,
            'not_important' => 1
        );



        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_GA_DATE_SENT,
            'not_important' => 0
        );

        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_GA_SENT_VIA,
            'not_important' => 0
        );

        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_GA_DATE_VERIFIED,
            'not_important' => 0
        );

        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_ORDER_TOTAL_GA,
            'not_important' => 0
        );
/*
        $this->view->ordersTable[] = array(
            'title' => '<input type="checkbox" class="uniform batch batch-correct">',
            'not_important' => 2
        );
*/

        $GET = Yii::$app->request->get();

        \Yii::$app->controller->view->sortColumns = '1,3,4,5,6,7,8';
        if (!empty($GET['order']) && is_array($GET['order'])) {
        } else {
          \Yii::$app->controller->view->sortNow = '3,1,5';
          \Yii::$app->controller->view->sortNowDir = "desc,desc,desc";
        }

        $AdminFilters = \common\models\AdminFilters::findOne(['filter_type' => 'orders-ga']);
        if ($AdminFilters instanceof \common\models\AdminFilters) {
            $GET += \Opis\Closure\unserialize($AdminFilters->filter_data);
        }
        
        $this->view->filters = new \stdClass();


        $search = '';
        if (isset($GET['search'])) {
            $search = $GET['search'];
        }
        $this->view->filters->search = $search;

        if (isset($GET['date']) && $GET['date'] == 'exact') {
            $this->view->filters->presel = false;
            $this->view->filters->exact = true;
        } else {
            $this->view->filters->presel = true;
            $this->view->filters->exact = false;
        }

        $interval = [
            [
                'name' => TEXT_ALL,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_TODAY,
                'value' => '1',
                'selected' => '',
            ],
            [
                'name' => TEXT_WEEK,
                'value' => 'week',
                'selected' => '',
            ],
            [
                'name' => TEXT_THIS_MONTH,
                'value' => 'month',
                'selected' => '',
            ],
            [
                'name' => TEXT_THIS_YEAR,
                'value' => 'year',
                'selected' => '',
            ],
            [
                'name' => TEXT_LAST_THREE_DAYS,
                'value' => '3',
                'selected' => '',
            ],
            [
                'name' => TEXT_LAST_SEVEN_DAYS,
                'value' => '7',
                'selected' => '',
            ],
            [
                'name' => TEXT_LAST_FOURTEEN_DAYS,
                'value' => '14',
                'selected' => '',
            ],
            [
                'name' => TEXT_LAST_THIRTY_DAYS,
                'value' => '30',
                'selected' => '',
            ],
        ];
        foreach ($interval as $key => $value) {
            if (isset($GET['interval']) && $value['value'] == $GET['interval']) {
                $interval[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->interval = $interval;

        $this->view->filters->status = \common\helpers\Order::getStatusList();
        $this->view->filters->status_selected = $GET['status'] ?? [];

        if (!empty($GET['fp_from'])) { //summ
          $this->view->filters->fp_from = htmlspecialchars($GET['fp_from']);
          $this->view->filters->fpFrom = true; //flag
        }
        if (!empty($GET['fp_to'])) { //summ
          $this->view->filters->fp_to = htmlspecialchars($GET['fp_to']);
          $this->view->filters->fpTo = true; //flag
        }

        $from = '';
        if (isset($GET['from'])) {
            $from = $GET['from'];
        }
        $this->view->filters->from = $from;

        $to = '';
        if (isset($GET['to'])) {
            $to = $GET['to'];
        }
        $this->view->filters->to = $to;

        $this->view->filters->row = (int) $GET['row'];
        $fs = 'closed';
        if (isset($GET['fs'])) {
            $fs = $GET['fs'];
        }
        $this->view->filters->fs = $fs;

        $this->view->filters->platform = array();
        if (isset($GET['platform']) && is_array($GET['platform'])) {
            foreach ($GET['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $this->view->filters->platform[] = (int) $_platform_id;
        }

        $departments = false;
        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $this->view->filters->departments = [];
            if ( isset($GET['departments']) && is_array($GET['departments']) ){
                foreach( $GET['departments'] as $_department_id ) if ( (int)$_department_id>0 ) $this->view->filters->departments[] = (int)$_department_id;
            }
            $departments = \common\classes\department::getList(false);
        }

        $ordersStatuses = \common\helpers\Order::getStatusList(false, true, 0);
        $ordersStatusesOptions = [];
        foreach(\common\helpers\Order::getStatuses(true, 0) as $orders_status){
            if (is_array($orders_status->statuses)){
                foreach($orders_status->statuses as $status){
                    if ($status->order_evaluation_state_id > 0) {
                        $ordersStatusesOptions[$status->orders_status_id]['evaluation_state_id'] = $status->order_evaluation_state_id;
                    }
                }
            }
        }

        return $this->render('index', [
                    'isMultiPlatform' => \common\classes\platform::isMulti(),
                    'platforms' => \common\classes\platform::getList(true, true),
                    'departments' => $departments,
                    'ordersStatuses' => $ordersStatuses,
                    'ordersStatusesOptions' => $ordersStatusesOptions,
        ]);
    }

    public function actionOrderlist() {
        global $login_id;
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/orders');
        \common\helpers\Translation::init('admin/orders-ga');

        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $departments = [];
            $departmentsList = \common\classes\department::getList(false);
            foreach ($departmentsList as $department) {
                $departments[$department['departments_id']] = $department['departments_store_name'];
            }
        }

        $draw = Yii::$app->request->get('draw');
        $start = Yii::$app->request->get('start');
        $length = Yii::$app->request->get('length');

        if ($length == -1)
            $length = 10000;

        $_session = Yii::$app->session;

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $quickFilter = ' (1';
            $quickFilter .= " or o.orders_id LIKE '%" . tep_db_input($keywords) . "%' ";
            $quickFilter .= " or o.customers_telephone LIKE '%" . tep_db_input($keywords) . "%' ";
            $quickFilter .= " or o.delivery_telephone LIKE '%" . tep_db_input($keywords) . "%' ";
            $quickFilter .= " or o.billing_telephone LIKE '%" . tep_db_input($keywords) . "%' ";
            $quickFilter .= ") ";
        } else {
            $quickFilter = "";
        }

        $gets = Yii::$app->request->get();
        $formFilter = Yii::$app->request->get('filter');
        $output = [];
        parse_str($formFilter, $output);

        $orders_query_raw = (new \yii\db\Query())->from(['o' => TABLE_ORDERS])
            ->select("o.orders_id, s.orders_status_name, s.orders_status_groups_id, o.date_purchased, o.payment_method,o.department_id, o.admin_id, o.platform_id, o.api_client_order_id ")
            ->innerJoin(TABLE_ORDERS_STATUS . " s", "o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "'")
            //->andWhere("" . $quickFilter  . "  ")
            ->leftJoin('ecommerce_tracking et', 'et.orders_id=o.orders_id')
            ->addSelect('services, message_type, via, date_added, extra_info, id, verified, verified_amount')
            ;
        ///and s.orders_status_groups_id IN('" . implode("','", array_keys($statusGroupData)) . "')

        if (!empty($gets['order']) && is_array($gets['order'])) {
          foreach ($gets['order'] as $sort) {
            $dir = 'asc';
            if (!empty($sort['dir']) && $sort['dir'] == 'desc') {
              $dir = 'desc';
            }
            switch ($sort['column']) {
              case 1:
                $orders_query_raw->addOrderBy(" o.orders_id " . $dir);
                //$orders_query_raw->addOrderBy(new \yii\db\Expression(" ifnull(sku, '') " . $dir));
                break;
              /*case 2:
                $orders_query_raw->addOrderBy(" total " . $dir);
                break;*/
              case 3:
                $orders_query_raw->addOrderBy(" date_purchased " . $dir);
                break;
              case 4:
                $orders_query_raw->addOrderBy(" orders_status_name " . $dir);
                break;
              case 5:
                $orders_query_raw->addOrderBy(" date_added " . $dir);
                break;
              case 6:
                $orders_query_raw->addOrderBy(" via " . $dir);
                break;
              case 7:
                $orders_query_raw->addOrderBy(" verified " . $dir);
                break;
              case 8:
                $orders_query_raw->addOrderBy(" verified_amount " . $dir);
                break;
              default:
                $orders_query_raw->addOrderBy(" o.date_purchased desc, o.orders_id desc ");
                break;
            }
          }
        } else {
            $orders_query_raw->addOrderBy(" o.date_purchased desc, o.orders_id desc, et.date_added desc ");
        }

        $statusGroupData = \yii\helpers\ArrayHelper::index(\common\models\OrdersStatusGroups::find()
            ->select(['orders_status_groups_name', 'orders_status_groups_color', 'orders_status_groups_id'])
            ->where(['language_id' => (int) $languages_id, 'orders_status_type_id'=>\common\helpers\Order::getStatusTypeId()])
            ->asArray()
            ->all(),'orders_status_groups_id');

        
        
        $filter = '';

        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $filter_by_departments = [];
            if ( isset($output['departments']) && is_array($output['departments']) ) {
                foreach( $output['departments'] as $_department_id ) if ( (int)$_department_id>0 ) $filter_by_departments[] = (int)$_department_id;
            }

            if ( count($filter_by_departments)>0 ) {
                $orders_query_raw->andWhere(['in', 'o.department_id', $filter_by_departments]);
            }
        }

        $filter_by_platform = array();
        if (isset($output['platform']) && is_array($output['platform'])) {
            foreach ($output['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $filter_by_platform[] = (int) $_platform_id;
        } elseif (false === \common\helpers\Acl::rule(['SUPERUSER'])) {
            $platforms = \common\models\AdminPlatforms::find()->where(['admin_id' => $login_id])->asArray()->all();
            foreach ($platforms as $platform) {
                $filter_by_platform[] = $platform['platform_id'];
            }
            $filter_by_platform[] = 0;
        }

        if (count($filter_by_platform) > 0) {
            $orders_query_raw->andWhere(['in', 'o.platform_id', $filter_by_platform]);
        }

        /**
         * @var $ext \common\extensions\Handlers\Handlers
         */
        if ($ext = \common\helpers\Extensions::isAllowed('Handlers')) {
            global $access_levels_id;
            $orders_query_raw->andWhere(['in', 'hp.handlers_id', $ext::getHandlersQuery((int) $access_levels_id)]);
        }
        

        if (tep_not_null($output['search'])) {
            $search = tep_db_prepare_input($output['search']);
            $orders_query_raw->andFilterWhere(['or',
                ['o.orders_id' => $search],
                ['like','o.customers_name',$search],
                ['like','o.customers_email_address',$search],
                ["like", "o.customers_telephone", $search],
                ["like", "o.delivery_telephone", $search],
                ["like", "o.billing_telephone", $search],
                ]);
        }
        if (is_array($output['status'])) {
            $orders_query_raw->andWhere(['in', 's.orders_status_id', $output['status']]);
        }

        if (tep_not_null($output['date'])) {
            switch ($output['date']) {
                case 'exact':
                    if (tep_not_null($output['from'])) {
                        $from = tep_db_prepare_input($output['from']);
                        $orders_query_raw->andWhere("to_days(o.date_purchased) >= to_days('" . \common\helpers\Date::prepareInputDate($from) . "')");
                    }
                    if (tep_not_null($output['to'])) {
                        $to = tep_db_prepare_input($output['to']);
                        $orders_query_raw->andWhere("to_days(o.date_purchased) <= to_days('" . \common\helpers\Date::prepareInputDate($to) . "')");
                    }
                    break;
                case 'presel':
                    if (tep_not_null($output['interval'])) {
                        switch ($output['interval']) {
                            case 'week':
                                $orders_query_raw->andWhere("o.date_purchased >= '" . date('Y-m-d', strtotime('monday this week')) . "'");
                                break;
                            case 'month':
                                $orders_query_raw->andWhere("o.date_purchased >= '" . date('Y-m-d', strtotime('first day of this month')) . "'");
                                break;
                            case 'year':
                                $orders_query_raw->andWhere("o.date_purchased >= '" . date("Y") . "-01-01" . "'");
                                break;
                            case '1':
                                $orders_query_raw->andWhere("o.date_purchased >= '" . date('Y-m-d') . "'");
                                break;
                            case '3':
                            case '7':
                            case '14':
                            case '30':
                                $orders_query_raw->andWhere("o.date_purchased >= date_sub(now(), interval " . (int) $output['interval'] . " day)");
                                break;
                        }
                    }
                    break;
            }
        }

        if (is_array($output['payments'])) {
            $orders_query_raw->andWhere(['in', 'o.payment_method', $output['payments']]);
        }

        if (is_array($output['shipping'])) {
            $orders_query_raw->andWhere(['in', 'o.shipping_method', $output['shipping']]);
        }

        if ((tep_not_null($output['fp_from']) || tep_not_null($output['fp_to'])) && tep_not_null($output['fp_class'])) {
          if (strpos($output['fp_from'], ',') !== false) {
            if (strpos($output['fp_from'], '.') !== false) {
              $output['fp_from'] = str_replace(',', '', $output['fp_from']);
            } else {
              $output['fp_from'] = str_replace(',', '.', $output['fp_from']);
            }
          }
          $fp_from = preg_replace('/[^0-9\.]/', '', $output['fp_from']);
          if (strpos($output['fp_to'], ',') !== false) {
            if (strpos($output['fp_to'], '.') !== false) {
              $output['fp_to'] = str_replace(',', '', $output['fp_to']);
            } else {
              $output['fp_to'] = str_replace(',', '.', $output['fp_to']);
            }
          }
          $fp_to = preg_replace('/[^0-9\.]/', '', $output['fp_to']);//" . tep_db_input($output['fp_class']). "
            $orders_query_raw->innerJoin(TABLE_ORDERS_TOTAL . " otfp", "o.orders_id=otfp.orders_id and otfp.class='ot_total'"
                . (tep_not_null($output['fp_from'])?" and round(otfp.value, 2)>='" . tep_db_input(round($fp_from,2)) . "'":'')
                . (tep_not_null($output['fp_to'])?" and round(otfp.value,2)<='" . tep_db_input(round($fp_to,2)) . "'":'')
                . "");
        }

        if (!empty($output['not_sent'])) {
          $orders_query_raw->andWhere(['is', 'et.orders_id', null]);
        }
//echo $orders_query_raw->createCommand()->getRawSql();

        $orders_query_numrows = $orders_query_raw->count();

        $orders_query_raw->limit($length)->offset($start);

//echo $orders_query_raw->createCommand()->getRawSql()."\n\n";

        $ordersAll = $orders_query_raw->all();
        $ids = ArrayHelper::map($ordersAll, 'orders_id', 'orders_id');
        $tmp = \common\models\OrdersTotal::find()->andWhere(['orders_id' => $ids])->asArray()->all();
        $totals = [];
        if (is_array($tmp)) {
          foreach ($tmp as $d) {
            $totals[$d['orders_id']][] = $d;
          }
        }

        // {{ append orders status group table
        foreach ($ordersAll as $__idx=>$_row){
            if (isset($statusGroupData[$_row['orders_status_groups_id']])){
              $ordersAll[$__idx] = array_merge($ordersAll[$__idx], $statusGroupData[$_row['orders_status_groups_id']]);
            }
            $ordersAll[$__idx]['ordersTotals'] = $totals[$_row['orders_id']]??[];
        }
        // }} append orders status group table

        $responseList = array();
        $stack = [];
        if ($ordersAll){
            $selected_platform_id = \common\classes\platform::firstId();
            Yii::$app->get('platform')->config($selected_platform_id)->constant_up();
            foreach($ordersAll as $orders){

                $orderTotals = '';

                if (is_array($orders['ordersTotals']) && count($orders['ordersTotals'])){
                    foreach($orders['ordersTotals'] as $totals){
                      $_class = $totals['class'];
                        if (file_exists(\Yii::getAlias('@common') . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'orderTotal' . DIRECTORY_SEPARATOR . $totals['class'] . '.php')) {
                            include_once(\Yii::getAlias('@common') . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'orderTotal' . DIRECTORY_SEPARATOR . $totals['class'] . '.php');
                            $totals['class'] = '\common\modules\orderTotal\\'.$totals['class'];
                        } else
                        if (file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $totals['class'] . '.php')) {
                            include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'order_total/' . $totals['class'] . '.php');
                        }
                        if (class_exists($totals['class'])) {
                            if (!array_key_exists($totals['class'], $stack)) {
                                $stack[$totals['class']] = new $totals['class'];
                            }
                            $object = $stack[$totals['class']];
                            if (!is_object($object)) {
                                $object = new $totals['class'];
                            }

                            if (method_exists($object, 'visibility')) {
                                if (true == $object->visibility(platform::defaultId(), 'TEXT_ADMINORDER')) {
                                    if (method_exists($object, 'visibility')) {
                                        $result = $object->displayText(platform::defaultId(), 'TEXT_ADMINORDER', $totals);
                                        $orderTotals .= '<div class="' . $result['class'] . ($result['show_line'] ? ' totals-line' : '') . '"><span>' . $result['title'] . '</span><span>' . $result['text'] . '</span></div>';
                                        if ($_class=='ot_total') {
                                          $orders['order_total'] = $totals['text'];
                                          $orders['order_total_val'] = $totals['value'];
                                        }
                                    } else {
                                        $orderTotals .= '<div><span>' . $totals['title'] . '</span><span>' . $totals['text'] . '</span></div>';
                                        if ($_class=='ot_total') {
                                          $orders['order_total'] = $totals['text'];
                                          $orders['order_total_val'] = $totals['value'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
                    $departmentInfo = TEXT_FROM . ' ' . $departments[$orders['department_id']];
                    if ( $orders['api_client_order_id'] ) {
                        $departmentInfo .= ' (#'.$orders['api_client_order_id'].')';
                    }
                } else {
                    $departmentInfo = ($orders['admin_id'] > 0 ? '&nbsp;by admin' : (\common\classes\platform::isMulti() >= 0 ? (SHOW_PRODUCTS_ON_ORDER_LIST === 'False' ? '<br>' : ' ') . TEXT_FROM . ' ' . \common\classes\platform::name($orders['platform_id']) : ''));
                }

                $purchasedDate = \common\helpers\Date::datetime_short($orders['date_purchased']);
                $dp = strtotime($orders['date_purchased']);
                $dga = strtotime($orders['date_added']);
                $to_early_to_resend = strtotime('2 hours ago')<$dga;
                $can_resend = !$to_early_to_resend && empty($orders['via']);

                $todayDate = \common\helpers\Date::date_short(date('Y-m-d'));
                $purchasedDate = str_replace($todayDate, TEXT_TODAY, $purchasedDate);
                $cusColumn = '';

                $orderRow = [];

                $orderRow[] = (empty($orders['via'])?'<input type="checkbox" class="uniform">':'') . '<input class="cell_identify" type="hidden" value="' . $orders['orders_id'] . '">';

                $orderRow[] = '<div class="ord-desc-tab click_double" data-click---double="' . \Yii::$app->urlManager->createUrl(['orders-ga/process-order', 'orders_id' => $orders['orders_id']]) . '"><a href="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '" target="_blank" class="order-inf"><span class="ord-id">' . TEXT_ORDER_NUM . $orders['orders_id'] . '</span> ' . $departmentInfo . (tep_not_null($orders['payment_method']) ? (SHOW_PRODUCTS_ON_ORDER_LIST === 'False' ? '<br>' : ' ') . TEXT_VIA . ' ' . strip_tags($orders['payment_method']) : '') . (tep_not_null($orders['shipping_method']) ? ' ' . TEXT_DELIVERED_BY . ' ' . strip_tags($orders['shipping_method']) : '') . '</a>' .  '</div>';
                $orderRow[] = '<div class="ord-total click_double" data-click---double="' . \Yii::$app->urlManager->createUrl(['orders-ga/process-order', 'orders_id' => $orders['orders_id']]) . '">' . $orders['order_total'] . '<div class="ord-total-info"><div class="ord-box-img"></div>' . $orderTotals . '</div></div>';

                $orderRow[] = '<div class="ord-date-purch click_double" data-click---double="' . \Yii::$app->urlManager->createUrl(['orders-ga/process-order', 'orders_id' => $orders['orders_id']]) . '">' . $purchasedDate . "</div>";
                $orderRow[] = '<div class="ord-status click_double" data-click---double="' . \Yii::$app->urlManager->createUrl(['orders-ga/process-order', 'orders_id' => $orders['orders_id']]) . '"><span><i style="background: ' . $orders['orders_status_groups_color'] . ';"></i>' . $orders['orders_status_groups_name'] . '</span><div>' . $orders['orders_status_name'] . '</div></div>';
                $orderRow[] = '<div class=""><span class="date-sent">' . \common\helpers\Date::datetime_short($orders['date_added']) . '</span></div>';
                $orderRow[] = '<div class=""><span class="via-sent">' . ($orders['via']) . '</span></div>';
                $orderRow[] = '<div class=""><span class="verified-sent">' . \common\helpers\Date::datetime_short($orders['verified']) . '</span></div>';
                $orderRow[] = '<div class=""><span class="verified-revenue">' . $orders['verified_amount']
                    . (!$to_early_to_resend && $orders['order_total_val']*1.5<$orders['verified_amount']?'<a href="javascript:void(0);" onclick="return reversGA(' . (int)$orders['orders_id'] . ');">' . TEXT_REVERSE.'</a>':'')
                    . '</span></div>';
                //$orderRow[] = ($can_resend?'<input type="checkbox" class="uniform batch batch-correct" value="' . $orders['orders_id'] . '">':'');

                $responseList[] = $orderRow;
            }
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_query_numrows,
            'recordsFiltered' => $orders_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR);
        //die();
    }

    public static function cropStr($str, $length)
    {
        if (mb_strlen($str) > $length) {
            $str = strip_tags($str);

            if (mb_strlen($str) > $length) {
                $str = mb_substr($str, 0, $length) . '...';
            }
        }

        return $str;
    }

    public function actionOrderactions() {

        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $orders_query = tep_db_query("select o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.platform_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.language_id, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.orders_id = '" . (int) $orders_id . "'");
        $orders = tep_db_fetch_array($orders_query);

        if (!is_array($orders)) {
            die("Please select order.");
        }

        $oInfo = new \objectInfo($orders);
        return $this->render('actions', ['oInfo' => $oInfo]);
    }

    
    
    public function actionUpdateStat() {
      \common\helpers\EcommerceTrackingHelper::updateStat();
      tep_redirect(Yii::$app->urlManager->createUrl('orders-ga/index'));

    }

    public function actionImport() {
      $q = Orders::find()->select('date_purchased')->orderBy('date_purchased')->limit(1)->asArray()->one();
      if ($q) {
        $dateStart = strtotime($q['date_purchased']);
      }
      $yearAgo = strtotime('1 year ago');
      if ($dateStart<$yearAgo) {
        $dateStart = $yearAgo;
      }
      $gess = new \common\components\google\GoogleEcommerceSS();
      foreach (\common\classes\platform::getList(false) as $platform) {
        $gess->setPlatformId($platform['id']);
        $orders = $gess->getTransactionsReport([date('Y-m-d', $dateStart)]);
        if (is_array($orders)) {
          $oids = Orders::find()->select('orders_id')->where(['orders_id' => array_map('intval', array_keys($orders))])->asArray()->column();
          foreach ($orders as $orders_id => $order) {
            if (in_array($orders_id, $oids )) {
              \common\helpers\EcommerceTrackingHelper::saveET($orders_id, $order);
            }
          }
        }
      }
      tep_redirect(Yii::$app->urlManager->createUrl('orders-ga/index'));

    }


    public function actionReverse() {
      \common\helpers\Translation::init('admin/orders-ga');
      $oid = \Yii::$app->request->post('orders_id', 0);
      if ($oid>0) {
        //checks
        $message = '';
        \common\helpers\EcommerceTrackingHelper::updateStat($oid);
        $order = new \common\classes\Order($oid);
        // no recent request to GA
        $et = EcommerceTracking::find()->andWhere(['orders_id' => $oid])->orderBy('id desc')->asArray()->one();
        if ($et) {
          if (strtotime($et['date_added'])>strtotime('1 hour ago')) { //&& (empty($et['verified']) || strtotime($et['verified'])>strtotime('1 hour ago')
            $message = TEXT_TOO_OFTEN;
          }elseif ($et['verified_amount']>0 && $et['verified_amount']<$order->info['total']){ //revenue shoudnot became negative
            $message = TEXT_INCORRECT_AMOUNT;
          }
        } else {
          $message = TEXT_INCORRECT_ORDER_ID;
        }

        if (empty($message)) {
          $ess = new \common\components\google\GoogleEcommerceSS($order);
          $provider = (new \common\components\GoogleTools())->getModulesProvider();
          $installed_modules = $provider->getInstalledModules($order->info['platform_id']);
          if (isset($installed_modules['ecommerce'])) {
            $res = $installed_modules['ecommerce']->forceServerSide($order, '', true);
            if (!$res) {
              $message = TEXT_SENDING_ERROR;
            }
          } else {
            $message = TEXT_NO_ECOMMERCE;
          }
        }
      } else {
        $message = TEXT_INCORRECT_ORDER_ID;
      }
      if (empty($message)) {
        $ret = ['error'=>0, 'message' => TEXT_OK];
      } else {
        $ret = ['error'=>1, 'message' => $message];
      }
      
      echo json_encode($ret);
    }
    
    public function actionSendEcommerce() {
      if (tep_not_null($_POST['orders'])) {
        // request orders reports by platforms
        // send order details if it's not in report only. (2do - send update if total is changed)
        $p = \common\models\Orders::find()
            ->select(['platform_id', 
              'min_date' => new \yii\db\Expression('min(date_purchased)'),
              'orders' => new \yii\db\Expression('group_concat(orders_id)'),
              ])
            ->groupBy('platform_id')
            ->where(['orders_id' => array_map('intval', explode(',', $_POST['orders'])) ])
            ->asArray()->all();
        if (is_array($p)) {
          $provider = (new \common\components\GoogleTools())->getModulesProvider();
          foreach ($p as $pd) {
            $gess = new \common\components\google\GoogleEcommerceSS();
            $gess->setPlatformId($pd['platform_id']);
            $dateStart = date('Y-m-d', strtotime($pd['min_date']));
            $orders = $gess->getTransactionsReport([$dateStart]);
            if ($orders !== false) {
              $oids = [];
              if (is_array($orders)) {
                $oids = array_map('intval', array_keys($orders));
              }
              $toProcess = array_map('intval', explode(',', $pd['orders']));
              $installed_modules = $provider->getInstalledModules($pd['platform_id']);
              if (isset($installed_modules['ecommerce'])) {
                foreach ($toProcess as $oid) {
                  if (!in_array($oid, $oids)) {
                    $order = new \common\classes\Order($oid);
                    $installed_modules['ecommerce']->forceServerSide($order);
                  } else {
                    // save GA Data
                    \common\helpers\EcommerceTrackingHelper::saveET($oid, $orders[$oid]);
                  }
                }
              }
            }
            
          }

        }
      }
      tep_redirect(Yii::$app->urlManager->createUrl('orders-ga/index'));
    }



}
