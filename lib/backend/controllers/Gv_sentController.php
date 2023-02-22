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

use backend\services\CouponsService;
use common\models\Coupons;
use Yii;

/**
 * GV sent controller to handle user requests.
 */
class Gv_sentController extends Sceleton {

    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_HEADING_GV_ADMIN', 'BOX_GV_ADMIN_SENT'];

    /** @var CouponsService */
    private $couponsService;

    public function __construct($id, $module, CouponsService $couponsService, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->couponsService = $couponsService;
    }

    public function beforeAction($action)
    {
        if (false === \common\helpers\Acl::checkExtensionAllowed('CouponsAndVauchers', 'allowed')) {
            $this->redirect(array('/'));
            return false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex() {
        global $language;
        
        $this->selectedMenu = array('marketing', 'gv_admin', 'gv_sent');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('gv_sent/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->view->catalogTable = array(
            array(
                'title' => TABLE_HEADING_SENDERS_NAME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_VOUCHER_VALUE,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_VOUCHER_CODE,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_DATE_SENT,
                'not_important' => 0
            ),
        );
        return $this->render('index', ['cid' => (int)Yii::$app->request->get('cid', 0)]);
    }

    public function actionList() {
        $currencies = Yii::$container->get('currencies');
        
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cid = Yii::$app->request->get('cid', 0);

        if( $length == -1 ) $length = 10000;
        $query_numrows = 0;
        $responseList = array();

        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where c.coupon_id = et.coupon_id and (et.sent_firstname like '%" . $keywords . "%' or et.sent_lastname like '%" . $keywords . "%' or et.emailed_to like '%" . $keywords . "%') ";
        } else {
            $search_condition = " where c.coupon_id = et.coupon_id ";
        }
        if ($cid){
          $search_condition .= " and et.coupon_id = '" . (int)$cid . "'";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "et.sent_firstname " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "c.coupon_amount " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 2:
                    $orderBy = "c.coupon_code " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 3:
                    $orderBy = "et.date_sent " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "et.date_sent desc";
                    break;
            }
        } else {
            $orderBy = "et.date_sent desc";
        }

        $gv_query_raw = "select et.unique_id, c.coupon_amount, c.coupon_currency, c.coupon_code, c.coupon_id, c.coupon_type, c.free_shipping, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, c.coupon_id from " . TABLE_COUPONS . " c, " . TABLE_COUPON_EMAIL_TRACK . " et $search_condition ORDER by $orderBy ";
        
        $current_page_number = ( $start / $length ) + 1;
        $_split = new \splitPageResults($current_page_number, $length, $gv_query_raw, $query_numrows, 'unique_id');
        $gv_query = tep_db_query($gv_query_raw);
        while ($gv_list = tep_db_fetch_array($gv_query)) {

            $coupon_amount = '';
            if ($gv_list['coupon_type'] == 'P') {
                $coupon_amount =  number_format($gv_list['coupon_amount'], 2) . '%';
            } elseif($gv_list['coupon_amount']>0) {
                $coupon_amount =  $currencies->format($gv_list['coupon_amount'], false, $gv_list['coupon_currency']);
            }
            if ($gv_list['free_shipping']){
                if ( !empty($coupon_amount) ){
                    $coupon_amount .= ' + '.TEXT_FREE_SHIPPING;
                }else{
                    $coupon_amount = TEXT_FREE_SHIPPING;
                }
            }

            $responseList[] = array(
                $gv_list['sent_firstname'] . ' ' . $gv_list['sent_lastname'] .
                '<input class="cell_identify" type="hidden" value="' . $gv_list['unique_id'] . '">',
                $coupon_amount,
                $gv_list['coupon_code'],
                \common\helpers\Date::date_short($gv_list['date_sent']),
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
        \common\helpers\Translation::init('admin/gv_sent');
        
        $currencies = Yii::$container->get('currencies');

        $this->layout = false;

        $item_id = (int) Yii::$app->request->post('item_id');

        $gv_query = tep_db_query("select et.unique_id, c.coupon_amount, c.coupon_currency, c.coupon_code, c.coupon_id, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, c.coupon_id, c.coupon_active from " . TABLE_COUPONS . " c, " . TABLE_COUPON_EMAIL_TRACK . " et where c.coupon_id = et.coupon_id and et.unique_id = '" . (int) $item_id . "'");
        $gv_list = tep_db_fetch_array($gv_query);
        $gInfo = new \objectInfo($gv_list);
        \common\helpers\Php8::nullObjProps($gInfo, ['unique_id', 'coupon_amount', 'coupon_currency', 'coupon_code', 'coupon_id', 'sent_firstname', 'sent_lastname', 'customer_id_sent', 'emailed_to', 'date_sent', 'coupon_id', 'coupon_active']);
        
        echo '<div class="or_box_head">[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount, false, $gInfo->coupon_currency) . '</div>';
  $redeem_query = tep_db_query("select * from " . TABLE_COUPON_REDEEM_TRACK . " where coupon_id = '" . $gInfo->coupon_id . "'");
  $redeemed = 'No';
  if (tep_db_num_rows($redeem_query) > 0) $redeemed = 'Yes';	
	echo '<div class="row_or_wrapp">';
	echo '<div class="row_or"><div>' . TEXT_INFO_SENDERS_ID . '</div><div>' . $gInfo->customer_id_sent . '</div></div>';
	echo '<div class="row_or"><div>' . TEXT_INFO_AMOUNT_SENT . '</div><div>' . $currencies->format($gInfo->coupon_amount, false, $gInfo->coupon_currency) . '</div></div>';
	echo '<div class="row_or"><div>' . TEXT_INFO_DATE_SENT . '</div><div>' . \common\helpers\Date::date_short($gInfo->date_sent) . '</div></div>';
	echo '<div class="row_or"><div>' . TEXT_INFO_VOUCHER_CODE . '</div><div>' . $gInfo->coupon_code . '</div></div>';	
	
  if ($redeemed=='Yes') {
    $redeem = tep_db_fetch_array($redeem_query);
		echo '<div class="row_or"><div>' . TEXT_INFO_DATE_REDEEMED . '</div><div>' . \common\helpers\Date::date_short($redeem['redeem_date']) . '</div></div>';
		echo '<div class="row_or"><div>' . TEXT_INFO_IP_ADDRESS . '</div><div>' . $redeem['redeem_ip'] . '</div></div>';
		echo '<div class="row_or"><div>' . TEXT_INFO_CUSTOMERS_ID . '</div><div>' . $redeem['customer_id'] . '</div></div>';
  } else {
		echo '<div class="row_or">' . TEXT_INFO_NOT_REDEEMED . '</div>';
        echo '<div class="row_full" id="wrapToggleStatus">' .
            $this->renderPartial('status-button.tpl', [
                'gInfo' => $gInfo,
            ])
            . '</div>';


  }
	
		echo '</div>';
		echo '<div class="row_full">' . TEXT_INFO_EMAIL_ADDRESS . ' ' . $gInfo->emailed_to . '</div>';
		echo '<div class="row_full"><div id="pre-wait"></div></div>';
    }

    public function actionToggleStatus()
    {
        $id = (int)Yii::$app->request->post('id',0);
        $status = (string)Yii::$app->request->post('status','');
        if ($id < 1) {
            throw new \InvalidArgumentException('Wrong coupon');
        }
        $coupon = $this->couponsService->getById($id);
        switch ($status) {
            case Coupons::STATUS_DISABLE:
                $this->couponsService->setActive($coupon);
                break;
            case Coupons::STATUS_ACTIVE:
                $this->couponsService->setDisable($coupon);
                break;
            default:
                throw new \InvalidArgumentException('Wrong coupon status');
        }
        return $this->asJson([
            'success' => true,
            'html' => $this->renderAjax('status-button.tpl', [
                'gInfo' => $coupon,
            ])
        ]);
    }
}
