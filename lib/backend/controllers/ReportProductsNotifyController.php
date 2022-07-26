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

use yii;

class ReportProductsNotifyController extends Sceleton
{

    public $acl = ['BOX_HEADING_REPORTS', 'BOX_REPORTS_PRODUCTS_NOTIFY'];

    public function actionIndex() {
        global $languages_id, $language;

        $this->selectedMenu = array('reports', 'report-products-notify');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('report-products-notify/index'), 'title' => BOX_REPORTS_PRODUCTS_NOTIFY);
        $this->view->headingTitle = BOX_REPORTS_PRODUCTS_NOTIFY;


        $this->view->filters = new \stdClass();

        $selectProductVariants = [
            ['id' => '', 'text' => TEXT_ALL_PRODUCTS]
        ];
        $products_query = tep_db_query(
            "select ifnull(i.prid, p.products_id) as products_id, pd.products_name, count(pn.products_notify_email) ".
            "from " . TABLE_PRODUCTS_NOTIFY . " pn ".
            "  left join " . TABLE_INVENTORY . " i on pn.products_notify_products_id = i.products_id ".
            "  inner join " . TABLE_PRODUCTS . " p on pn.products_notify_products_id = p.products_id ".
            "  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and pd.platform_id = '".\common\classes\platform::defaultId()."' ".
            "where 1 ".
            "group by products_id ".
            "order by products_name"
        );
        while ($products = tep_db_fetch_array($products_query)) {
            $selectProductVariants[] = array('id' => $products['products_id'], 'text' => $products['products_name']);
        }

        $by = [
            '' => [
                [
                    'label' => rtrim(HEADING_TITLE_SEARCH,': '),
                    'name' => 'search',
                    'selected' => '',
                    'type' => 'text'
                ],
                [
                    'label' => TEXT_CUSTOMER_STATUS,
                    'name' => 'notified',
                    'selected' => '2',
                    'type' => 'dropdown',
                    'value' => [
                        ['id'=>'', 'text'=>TEXT_ALL],
                        ['id'=>'1', 'text'=>TEXT_NOTIFIED],
                        ['id'=>'2', 'text'=>TEXT_NOT_NOTIFIED],
                    ]
                ],
                [
                    'label' => HEADING_TITLE_PRODUCT,
                    'name' => 'prid',
                    'selected' => '',
                    'value' => $selectProductVariants,
                    'type' => 'dropdown'
                ],
            ],
        ];

        foreach ($by as $label => $items) {
            foreach($items as $key => $item){
                if (isset($_GET[$item['name']])) {
                    if ($by[$label][$key]['type'] == 'text'){
                        $by[$label][$key]['value'] = $_GET[$item['name']];
                    } else if($by[$label][$key]['type'] == 'dropdown'){
                        $by[$label][$key]['selected'] = $_GET[$item['name']];
                    }

                }
            }

        }

        $this->view->filters->by = $by;

        $this->view->filters->row = (int)$_GET['row'];

        $this->view->filters->platform = array();
        if (isset($_GET['platform']) && is_array($_GET['platform'])) {
            foreach ($_GET['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $this->view->filters->platform[] = (int) $_platform_id;
        }

        $this->view->reportTable = $this->getTable();
        return $this->render('index', [
                'isMultiPlatform' => \common\classes\platform::isMulti(),
                'platforms' => \common\classes\platform::getList(true, true),
        ]);
    }

    public function getTable(){
        return array(
            array(
                'title' => TABLE_HEADING_PRODUCTS_MODEL,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_PRODUCTS_NAME,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_PRODUCTS_ATTRIBUTES,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_CUSTOMERS_EMAIL,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_CUSTOMERS_NAME,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_IS_CUSTOMER,
                'not_important' => 0,
            ),
        );
    }

    public function build($full = false){
        global $languages_id, $language, $login_id;

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_category_id = Yii::$app->request->get('id', 0);

        if ($length == -1)
            $length = 10000;

        $output = [];
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);
        if (is_null($formFilter) && count($_GET)){
            foreach($_GET as $key => $value){
                $output[$key] = $value;
            }
        }

        $filter = '';
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
            $filter .= " AND pn.platform_id in ('" . implode("','", $filter_by_platform) . "') ";
        }
        if (isset($output['search']) && tep_not_null($output['search'])) {
            $keywords = tep_db_input(tep_db_prepare_input($output['search']));
            $filter .= " and (ifnull(i.products_name, pd.products_name) like '%" . $keywords . "%' or ifnull(i.products_model, p.products_model) like '%" . $keywords . "%' or products_notify_email like '%" . $keywords . "%' or products_notify_name like '%" . $keywords . "%') ";
        }
        if ($output['prid'] > 0) {
            $filter .= " and ifnull(i.prid, p.products_id) = '" . (int)$output['prid'] . "' ";
        }
        if ($output['notified'] > 0) {
            switch ($output['notified']){
                case 1: $filter .= " and pn.products_notify_sent is not null ";
                    break;
                case 2: $filter .= " and pn.products_notify_sent is null ";
                    break;
            }
        }

        $orderBy = '';
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = " ifnull(i.products_model, p.products_model) " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])).", products_id, pn.products_notify_email";
                    break;
                case 1:
                    $orderBy = "products_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])).", products_id, pn.products_notify_email";
                    break;
                case 3:
                    $orderBy = "pn.products_notify_email " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])).", products_id";
                    break;
                case 4:
                    $orderBy = "pn.products_notify_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])).", products_id, pn.products_notify_email";
                    break;
                case 5:
                    $orderBy = "if(c.customers_email_address is null, 0, 1) " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])).", products_id, pn.products_notify_email ";
                    break;
                default:
                    $orderBy = "products_name, products_id, pn.products_notify_email";
                    break;
            }
        }
        if (empty($orderBy)){
            $orderBy = "products_name, products_id, pn.products_notify_email";
        }

        $iQuery =
            "select ifnull(i.products_id, p.products_id) as products_id, ".
            " /* ifnull(i.products_name, pd.products_name) as products_name, */ pd.products_name, ".
            " ifnull(i.products_model, p.products_model) as products_model, ".
            " pn.products_notify_email, pn.products_notify_name, ".
            " if(c.customers_email_address is null, 0, 1) as is_customer ".
            "from " . TABLE_PRODUCTS_NOTIFY . " pn ".
            " left join " . TABLE_CUSTOMERS . " c on pn.products_notify_email = c.customers_email_address and c.opc_temp_account=0 ".
            " left join " . TABLE_INVENTORY . " i on pn.products_notify_products_id = i.products_id ".
            " inner join " . TABLE_PRODUCTS . " p on pn.products_notify_products_id = p.products_id ".
            " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and pd.platform_id = '".\common\classes\platform::defaultId()."' ".
            "where 1 " . $filter . " ".
            "order by {$orderBy}";

        $current_page_number = ($start / $length) + 1;
        $itemQuery_numrows = 0;
        if (!$full){
            $split = new \splitPageResults($current_page_number, $length, $iQuery, $itemQuery_numrows);
        }
//echo $iQuery;
        $report_query = tep_db_query($iQuery);
        $responseList = array();

        while ($row = tep_db_fetch_array($report_query)) {
            $responseList[] = array(
                $row['products_model'],
                $row['products_name'],
                \common\helpers\Attributes::getUpridAttrNames($row['products_id']),
                $row['products_notify_email'],
                $row['products_notify_name'],
                ($row['is_customer'] ? TEXT_YES : TEXT_NO),
            );
        }
        if ($report_query) {
            tep_db_free_result($report_query);
            unset($report_query);
        }
        return [
            'responseList' => $responseList,
            'count' => $itemQuery_numrows,
        ];
    }

    public function actionList() {

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_page_number = ($start / $length) + 1;

        $data = $this->build();

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $data['count'],
            'recordsFiltered' => $data['count'],
            'data' => $data['responseList'],
        );
        echo json_encode($response);
    }

    public function actionExport() {
        \common\helpers\Translation::init('admin/report-products-notify');
        $data = $this->build(true);
        $head = $this->getTable();

        $writer = new \backend\models\EP\Formatter\CSV('write', array("column_separator"=>','), 'products-notify-' . strftime('%Y%m%d-%H%I') . '.csv');
        $a = [];
        foreach($head as $m){
            $a[] = $m['title'];
        }
        $writer->write_array($a);

        foreach($data['responseList'] as $row){
            $newArray = array_map(function($v){
                $vv = trim(strip_tags($v));
                $vv = str_replace(['&nbsp;&raquo;&nbsp;', '&nbsp;', ], [' / ', '', ], $vv);
                return $vv;
            }, $row);
            $writer->write_array($newArray);
        }
        exit();
    }

}