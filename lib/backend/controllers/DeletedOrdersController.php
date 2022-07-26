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

class DeletedOrdersController extends Sceleton {

    public $acl = ['BOX_HEADING_REPORTS', 'BOX_DELETED_ORDERS'];

    public function __construct($id, $module = null) {
        \common\helpers\Translation::init('admin/deleted-orders');
        parent::__construct($id, $module);
    }

    public function actionIndex() {

        $this->selectedMenu = array('reports', 'deleted-orders');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('deleted-orders/index'), 'title' => BOX_DELETED_ORDERS);
        $this->view->headingTitle = BOX_DELETED_ORDERS;

        $this->view->LogTable = [
            [
                'title' => TABLE_HEADING_DATE_ADDED,
                'not_important' => 0
            ],
            [
                'title' => TEXT_ORDER_ID,
                'not_important' => 0
            ],
            [
                'title' => 'Admin',
                'not_important' => 0
            ],
            [
                'title' => TABLE_HEADING_COMMENTS,
                'not_important' => 0
            ],
        ];
        
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
            $search_condition = " where comments like '%" . $keywords . "%' ";
        } else {
            $search_condition = " where 1 ";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "date_added " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "orders_id " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 2:
                    $orderBy = "admin_id " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 3:
                    $orderBy = "comments " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "date_added";
                    break;
            }
        } else {
            $orderBy = "date_added";
        }

        $current_page_number = ( $start / $length ) + 1;
        $accessQueryRaw = "select * from " . \common\models\OrdersDeleteHistory::tableName() . " $search_condition order by $orderBy";
        $_split = new \splitPageResults($current_page_number, $length, $accessQueryRaw, $recordsTotal, 'orders_history_id');
        $accessQuery = tep_db_query($accessQueryRaw);
        while ($access = tep_db_fetch_array($accessQuery)) {
            $responseList[] = array(
                    \common\helpers\Date::datetime_short($access['date_added']) . '<input class="cell_identify" type="hidden" value="' . $access['orders_history_id'] . '">',
                    $access['orders_id'],
                    $access['admin_id'],
                    $access['comments'],
                );
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $responseList
        ];
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

}
