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
 * GV queue controller to handle user requests.
 */
class Gv_queueController extends Sceleton {

    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_GV_ADMIN_QUEUE'];
    
    public function beforeAction($action)
    {
        if (false === \common\helpers\Acl::checkExtensionAllowed('CouponsAndVauchers', 'allowed')) {
            $this->redirect(array('/'));
            return false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex() {
        $this->selectedMenu = array('marketing', 'gv_admin', 'gv_queue');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('gv_queue/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->view->catalogTable = array(
            array(
                'title' => TABLE_HEADING_CUSTOMERS,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_ORDERS_ID,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_VOUCHER_VALUE,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_DATE_PURCHASED,
                'not_important' => 0
            ),
        );
        return $this->render('index');
    }

    public function actionList() {
        $currencies = Yii::$container->get('currencies');
        
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        if( $length == -1 ) $length = 10000;
        $query_numrows = 0;
        $responseList = array();

        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where (gv.customer_id = c.customers_id and gv.release_flag = 'N') and (c.customers_firstname like '%" . $keywords . "%' or c.customers_lastname like '%" . $keywords . "%' or c.customers_email_address like '%" . $keywords . "%') ";
        } else {
            $search_condition = " where (gv.customer_id = c.customers_id and gv.release_flag = 'N') ";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "c.customers_firstname " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "gv.order_id " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 2:
                    $orderBy = "gv.amount " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "c.customers_firstname desc";
                    break;
            }
        } else {
            $orderBy = "c.customers_firstname desc";
        }

        $gv_query_raw = "select c.customers_firstname, c.customers_lastname, gv.unique_id, gv.date_created, gv.amount, gv.currency, gv.order_id from " . TABLE_CUSTOMERS . " c, " . TABLE_COUPON_GV_QUEUE . " gv $search_condition order by $orderBy ";
        
        $current_page_number = ( $start / $length ) + 1;
        $_split = new \splitPageResults($current_page_number, $length, $gv_query_raw, $query_numrows, 'unique_id');
        $gv_query = tep_db_query($gv_query_raw);
        while ($gv_list = tep_db_fetch_array($gv_query)) {

              
            $responseList[] = array(
                $gv_list['customers_firstname'] . ' ' . $gv_list['customers_lastname'] .
                '<input class="cell_identify" type="hidden" value="' . $gv_list['unique_id'] . '">',
                $gv_list['order_id'],
                $currencies->format($gv_list['amount'], false, $gv_list['currency']),
                \common\helpers\Date::datetime_short($gv_list['date_created']),
            );
        }
        
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $query_numrows,
            'recordsFiltered' => $query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionItempreedit() {
        \common\helpers\Translation::init('admin/gv_queue');
        
        $currencies = Yii::$container->get('currencies');

        $this->layout = false;

        $item_id = (int) Yii::$app->request->post('item_id');

        $gv_query = tep_db_query("select c.customers_firstname, c.customers_lastname, gv.unique_id, gv.date_created, gv.amount, gv.currency, gv.order_id from " . TABLE_CUSTOMERS . " c, " . TABLE_COUPON_GV_QUEUE . " gv where gv.unique_id = '" . (int) $item_id . "'");
        $gv_list = tep_db_fetch_array($gv_query);
        if (!is_array($gv_list)) {
            die();
        }
        $gInfo = new \objectInfo($gv_list);
        
        echo '<div class="or_box_head">[' . $gInfo->unique_id . '] ' . \common\helpers\Date::datetime_short($gInfo->date_created) . ' ' . $currencies->format($gInfo->amount, false, $gInfo->currency) . '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order"><a class="btn btn-primary btn-process-order" href="' . tep_href_link('gv_queue','action=release&gid=' . $gInfo->unique_id,'NONSSL'). '">' . IMAGE_RELEASE . '</a></div>';
    }

}
