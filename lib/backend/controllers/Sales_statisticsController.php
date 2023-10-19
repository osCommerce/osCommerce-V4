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
use backend\components\Graphs;
use backend\models\Report;

class Sales_statisticsController extends Sceleton {

    public $acl = ['BOX_HEADING_REPORTS', 'BOX_REPORTS_SALES'];

    public function __construct($id, $module = null) {
        \common\helpers\Translation::init('shipping');
        \common\helpers\Translation::init('ordertotal');
        \common\helpers\Translation::init('admin/sales_statistics');
        parent::__construct($id, $module);
    }

    public function actionIndex() {
        $this->selectedMenu = array('reports', 'sales_statistics');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('sales_statistics/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;

        \common\helpers\Translation::init('admin/orders');

        $this->view->filter = new \stdClass();

        $report = new Report($_GET);

        $this->view->filter->precision = $report->precisionList();
        $this->view->filter->precision_selected = $report->getPrecision();

        $this->view->filter->statuses = $report->getStatuses();
        $this->view->filter->payment_methods = $report->getPayments();
        $this->view->filter->shipping_methods = $report->getShippings();
        $this->view->filter->platforms = $report->getPlatforms();
        $this->view->filter->customer_groups = $report->getCustomerGroups('', true);

        $this->view->filter->walkin = Yii::$app->request->get('walkin') ?? false;
        $this->view->filter->admin = [];
        foreach(\common\helpers\Admin::getAdminsWithWalkinOrders() as $admin){
            $this->view->filter->admin[$admin->admin_id] = $admin->admin_firstname .' '. $admin->admin_lastname;
        }

        $this->view->filter->charts = $report->getChartsGroups();


        $model = $report->getReportModel();

        $m_titles = $model->getOtModules();

        $data = $model->loadPurchases();

        $columns = [];
        if (is_array($data) && count($data)) {
            $_columns = array_keys($data[0]);
            foreach ($_columns as $v) {
                $columns[] = ['class' => $v];
            }
            $m_titles = \yii\helpers\ArrayHelper::map($m_titles, 'class', 'title');
            foreach ($columns as $k => $c) {
                if (isset($m_titles[$c['class']])) {
                    $columns[$k]['title'] = $m_titles[$c['class']];
                } else {
                    $columns[$k]['title'] = $model->convertColumnTitle($columns[$k]['class']);
                }
            }
        }
        $ph = $report->getSelectedPlatforms();
        if (empty($ph)){
            $ph = [];
            foreach (\common\classes\platform::getList(true, true) as $p) {
                $ph[] = $p['id'];
            }
        }
        $params = [
            'options' => $model->getRangeList(),
            'data' => $data,
            'columns' => $columns,
            'range' => $this->renderAjax('range', ['range' => $model->getRange()]),
            'holidays' => \common\helpers\Date::getHolidays($ph, 'm/d/Y H:i:s', $model->getDataYear($data)),
            'rows' => $model->getRowsCount(),
            'table_title' => $model->getTableTitle(),
            'filters' => $report->getFilters(),
            'selected_filter' => \yii\helpers\Url::to(['sales_statistics/index']) . '?' . $_SERVER["QUERY_STRING"],
            'selected_statuses' => $report->getSelectedStatuses(),
            'selected_payments' => $report->getSelectedPayments(),
            'selected_shippings' => $report->getSelectedShippings(),
            'selected_platforms' => $report->getSelectedPlatforms(),
            'selected_customer_groups' => $report->getSelectedCustomerGroups(),
            'undisabled' => $report->getUndisabledCharts(),
            'class_range' => array_keys($model->getClassRange()),
            'geo_details' => $this->getGeoDetails($report),
            'with_products' => $report->getWithProducts(),
            'walkin' => $report->getWalkInAdmins(),
        ];
        //echo '<pre>';print_r($params);die;

        if (Yii::$app->request->isAjax) {
            echo json_encode($params);
            exit();
        } else {
            return $this->render('index', $params);
        }
    }

    public function actionLoadRange() {
        $type = Yii::$app->request->get('type');
        $range = '';
        $undisabled = [];
        if ($type) {
            $report = new Report(['type' => $type]);
            $range = $report->getReportModel()->getRangeList();
            //$undisabled = $report->getUndisabledCharts();
        }
        echo json_encode(['range' => $range, 'undisabled' => $undisabled]);
    }

    public function actionLoadOptions() {
        $type = Yii::$app->request->get('type');
        $range = Yii::$app->request->get('range');
        $options = '';
        $undisabled = [];
        if ($type) {
            $report = new Report(Yii::$app->request->get());
            $options = $report->getReportModel()->getOptions($range);
            if ($range == 'custom')
            $undisabled = $report->getUndisabledCharts();
        }
        echo json_encode(['options' => $options, 'undisabled' => $undisabled]);
    }

    public function getGeoDetails($report, $isAjax = false){

         return $this->renderAjax('geo_details', [
                'selected_geo_type' => $report->getSelectedGeoType(),
                'geo_type' => $report->getGeoType(),
                'selected_zones' => $report->getSelectedZones(),
                'zones' => $report->getGeoZones(),
                'ajax' => $isAjax,
                'country' => $report->getSelectedCountry(),
                'state' => $report->getSelectedState(),
                'sps' => $report->getSelectedSPS(),
            ]);
    }

    public function actionGetGeo(){
        $geoType = Yii::$app->request->get('geo_type', 0);
        $sAction = Yii::$app->request->get('action', '');
        switch ($sAction){
            case "country" :
                $term = Yii::$app->request->get('term', '');
                $delivery_countries = \common\helpers\Order::getOrdersQuery(['delivery_country' => $term])
                                            ->groupBy('delivery_country')->orderBy('delivery_country')->all();
                $response = \yii\helpers\ArrayHelper::getColumn($delivery_countries, 'delivery_country');
                break;
            case "state" :
                $term = Yii::$app->request->get('term', '');
                $country = Yii::$app->request->get('country');
                $delivery_states = \common\helpers\Order::getOrdersQuery(['delivery_state' => $term, 'delivery_country' => $country])
                        ->groupBy('delivery_state')->orderBy('delivery_state')->all();
                $response = \yii\helpers\ArrayHelper::getColumn($delivery_states, 'delivery_state');
                break;
            default:
                $report = new Report($_GET);
                $response = [
                    'selectors' => $this->getGeoDetails($report, Yii::$app->request->isAjax),
                ];
                break;
        }

        echo json_encode($response);
        exit();
    }

    public function actionSaveFilter() {
        $params = Yii::$app->request->getBodyParams();
        $message = '';

        //$params['options'] = urldecode($params['options']);

        if (is_array($params)) {
            if (isset($params['filter_name']) && !empty($params['filter_name']) && isset($params['options']) && !empty($params['options'])) {
                tep_db_query("insert into " . TABLE_SALES_FILTERS . " set sales_filter_name = '" . tep_db_input($params['filter_name']) . "', sales_filter_vals = '" . tep_db_input($params['options']) . "'");
                $message = TEXT_MESSEAGE_SUCCESS;
            } else {
                $message = TEXT_MESSAGE_ERROR;
            }
        } else {
            $message = TEXT_MESSAGE_ERROR;
        }
        echo json_encode(['message' => $message]);
        exit();
    }

    public function actionDeleteFilter() {
        $params = Yii::$app->request->getBodyParams();
        $message = '';

        if (is_array($params)) {
            $params['filter_vals'] = str_replace(\yii\helpers\Url::to(['sales_statistics/index']) . '?', '', $params['filter_vals']);
            if (isset($params['filter_vals']) && !empty($params['filter_vals'])) {
                tep_db_query("delete from " . TABLE_SALES_FILTERS . " where sales_filter_vals = '" . tep_db_input($params['filter_vals']) . "'");
                $message = TEXT_MESSEAGE_SUCCESS;
            } else {
                $message = TEXT_MESSAGE_ERROR;
            }
        } else {
            $message = TEXT_MESSAGE_ERROR;
        }
        echo json_encode(['message' => $message]);
        exit();
    }

    public function actionMapShow() {
        $origPlace = array(0, 0, 2);
        $country_info = tep_db_fetch_array(tep_db_query("select ab.entry_country_id from " . TABLE_PLATFORMS_ADDRESS_BOOK . " ab inner join " . TABLE_PLATFORMS . " p on p.is_default = 1 and p.platform_id = ab.platform_id where ab.is_default = 1"));
        $_country = (int) STORE_COUNTRY;
        if ($country_info) {
            $_country = $country_info['entry_country_id'];
        }
        if (defined('STORE_COUNTRY') && (int) STORE_COUNTRY > 0) {
            $origPlace = tep_db_fetch_array(tep_db_query("select lat, lng, zoom from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $_country . "'"));
        }
        return $this->renderAjax('map', ['mapskey' => \common\components\GoogleTools::instance()->getMapProvider()->getMapsKey(), 'origPlace' => $origPlace]);
    }

    public function actionMap() {
        $report = new Report($_GET);
        $model = $report->getReportModel();
        $data = $model->loadPurchases(true);

        echo json_encode(['data' => $data]);
        exit();
    }

    public function actionExport() {
        $report = new Report($_GET);
        $model = $report->getReportModel();
        $data = $model->loadPurchases(false);
        $start = Yii::$app->request->get('start', null);
        $end = Yii::$app->request->get('end', null);

        $ex_type = Yii::$app->request->get('ex_type');
        $ex_data = explode("|", Yii::$app->request->get('ex_data'));
        $report->export($data, ['modules' => $ex_data, 'type' => $ex_type, 'start' => $start, 'end' => $end]);
        exit();
    }

}
