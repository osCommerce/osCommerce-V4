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
use backend\models\ProductNameDecorator;
use common\classes\platform_config;
use common\classes\platform;
use common\classes\shopping_cart;
use common\classes\order_total;
use common\classes\shipping;
use common\classes\payment;
use common\components\Customer;
use common\extensions\NovaPoshta\NovaPoshta;
use common\helpers\Acl;
use common\helpers\Output;
use backend\models\AdminCarts;
use common\helpers\Status;
use common\helpers\Coupon;
use common\helpers\Order as OrderHelper;
use common\helpers\Translation;
use common\models\TmpOrders as Orders;
use common\models\ShippingNpOrderParams;
use backend\models\forms\NovaPoshtaForm;
use common\services\CustomersService;
use common\helpers\Html;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Yii;

use backend\models\EP\Messages;

/**
 * default controller to handle user requests.
 */
class TmpOrdersController extends Sceleton {

    public $acl = ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_TMP_ORDERS'];
    private $tmpOrderController = true;

    /**
     * Index action is the default action in a controller.
     */
    public function __construct($id, $module = '') {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            $ext::checkCustomerGroups();
        }
        defined('GROUPS_IS_SHOW_PRICE') or define('GROUPS_IS_SHOW_PRICE', true);
        defined('GROUPS_DISABLE_CHECKOUT') or define('GROUPS_DISABLE_CHECKOUT', false);
        defined('GROUPS_DISABLE_CART') or define('GROUPS_DISABLE_CART', false);
        defined('SHOW_OUT_OF_STOCK') or define('SHOW_OUT_OF_STOCK', 1);
        \common\helpers\Translation::init('ordertotal');
        \common\helpers\Translation::init('admin/orders');
        parent::__construct($id, $module);
    }

/**
 *
 * @global int $login_id
 * @global type $navigation
 * @return string
 */
    public function actionIndex() {

        global $login_id, $navigation;

        if (is_object($navigation) && method_exists($navigation, 'set_snapshot')){
            $navigation->set_snapshot();
        }

        $this->selectedMenu = array('BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_TMP_ORDERS');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('tmporders/index'), 'title' => HEADING_TMP_ORDERS);
        $this->view->headingTitle = HEADING_TMP_ORDERS;
        $this->view->ordersTable = [];
        $this->view->ordersTable[] = array(
            'title' => '<input type="checkbox" class="uniform">',
            'not_important' => 2
        );

        if (!$this->tmpOrderController && $ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $this->view->ordersTable[] =  array(
              'title' => TABLE_HEADING_FLAG,
              'not_important' => 0
              );
        }

        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_CUSTOMERS,
        );
        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_ORDER_TOTAL,
            'not_important' => 0
        );
        $this->view->ordersTable[] = array(
            'title' => TABLE_HEADING_DETAILS,
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

        if (\common\helpers\Acl::checkExtensionAllowed('Neighbour')){
            $this->view->ordersTable[] =  array(
                'title' => defined('EXT_NEIGHBOUR_TABLE_HEADING') ? EXT_NEIGHBOUR_TABLE_HEADING : TABLE_HEADING_NEIGHBOUR,
                'not_important' => 0
            );
        }

        $GET = Yii::$app->request->get();
        $AdminFilters = \common\models\AdminFilters::findOne(['filter_type' => 'orders']);
        if ($AdminFilters instanceof \common\models\AdminFilters) {
            $GET += \Opis\Closure\unserialize($AdminFilters->filter_data);
        }

        $this->view->filters = new \stdClass();

        $markers = [];
        if (!$this->tmpOrderController && $ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $markers = $ext::getMarkersList(true);
        }
        $this->view->markers = $markers;
        $this->view->filters->marker = (int)Yii::$app->request->get('marker', 0);

        $flags = [];
        if (!$this->tmpOrderController && $ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $flags = $ext::getFlagsList(true);
        }
        $this->view->flags = $flags;
        $this->view->filters->flag = (int)Yii::$app->request->get('flag', 0);

        $this->view->filters->mode = Yii::$app->request->get('mode','');

        $by = [
            [
                'name' => TEXT_ANY,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_ORDER_ID,
                'value' => 'oID',
                'selected' => '',
            ],
            [
                'name' => TEXT_CUSTOMER_ID,
                'value' => 'cID',
                'selected' => '',
            ],
            [
                'name' => TEXT_MODEL,
                'value' => 'model',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_NAME,
                'value' => 'name',
                'selected' => '',
            ],
            /* [
              'name' => 'Brand',
              'value' => 'brand',
              'selected' => '',
              ], */
            /*[
                'name' => TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME,
                'value' => 'batchName',
                'selected' => '',
            ],*/
            [
                'name' => TEXT_CLIENT_NAME,
                'value' => 'fullname',
                'selected' => '',
            ],
            [
                'name' => TEXT_CLIENT_EMAIL,
                'value' => 'email',
                'selected' => '',
            ],
            [
                'name' => TEXT_CLIENT_PHONE,
                'value' => 'phone',
                'selected' => '',
            ],
            /*[
                'name' => TEXT_TRACKING_NUMBER,
                'value' => 'tracking_number',
                'selected' => '',
            ],*/
        ];
        foreach ($by as $key => $value) {
            if (isset($GET['by']) && $value['value'] == $GET['by']) {
                $by[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->by = $by;

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

        $this->view->filters->walkin = Yii::$app->request->get('walkin') ?? false;
        $this->view->filters->admin = [];
/*
        foreach(\common\helpers\Admin::getAdminsWithWalkinOrders() as $admin){
            $this->view->filters->admin[$admin->admin_id] = $admin->admin_firstname .' '. $admin->admin_lastname;
        }
*/
        $this->view->filters->status = \common\helpers\Order::getStatusList();
        $this->view->filters->status_selected = $GET['status'] ?? [];

        $this->view->filters->fcoupon = 'byId';
        $this->view->filters->fc_id = $GET['fc_id'] ?? [];
  /*      if (!empty($GET['fc_code'])) {
          $this->view->filters->fc_code = htmlspecialchars($GET['fc_code']);
          $this->view->filters->fcoupon = 'like';
          $this->view->filters->fc_id = [];
        }
        $this->view->filters->fCoupons = \yii\helpers\ArrayHelper::map(Coupon::getOrderedList(), 'coupon_id', 'coupon_code');
*/
        if (!empty($GET['fp_from'])) { //summ
          $this->view->filters->fp_from = htmlspecialchars($GET['fp_from']);
          $this->view->filters->fpFrom = true; //flag
        } else {
            $this->view->filters->fpFrom = false;
            $this->view->filters->fp_from = null;
        }
        if (!empty($GET['fp_to'])) { //summ
            $this->view->filters->fp_to = htmlspecialchars($GET['fp_to']);
            $this->view->filters->fpTo = true; //flag
        } else {
            $this->view->filters->fp_to = null;
            $this->view->filters->fpTo = false;
        }
        $this->view->filters->fpClass = OrderHelper::getUsedTotalClassList($GET['fp_class']??'');

        $oModelQuery = Orders::find();

        $this->view->filters->payments = $payments = $oModelQuery->select(['payment_method'])->distinct()->orderBy('payment_method')->asArray()->indexBy('payment_method')->column();
        $this->view->filters->shipping = array_map('html_entity_decode', $this->view->filters->payments);
        $this->view->filters->payments_selected = $GET['payments'] ?? [];

        $this->view->filters->shipping = $payments = \yii\helpers\ArrayHelper::map($oModelQuery->select(['shipping_method'])->groupBy('shipping_method')->orderBy('shipping_method')->asArray()->all(), 'shipping_method', 'shipping_method');
        $this->view->filters->shipping = array_map('html_entity_decode', $this->view->filters->shipping);
        $this->view->filters->shipping_selected = $GET['shipping'] ?? [];

        $delivery_country = '';
        if (isset($GET['delivery_country'])) {
            $delivery_country = $GET['delivery_country'];
        }
        $this->view->filters->delivery_country = $delivery_country;

        $delivery_state = '';
        if (in_array(ACCOUNT_STATE, ['required', 'required_register', 'visible', 'visible_register'])) {
            $this->view->showState = true;
        } else {
            $this->view->showState = false;
        }
        if (isset($GET['delivery_state'])) {
            $delivery_state = $GET['delivery_state'];
        }
        $this->view->filters->delivery_state = $delivery_state;

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

        $this->view->filters->row = (int)Yii::$app->request->get('row', 0);
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

        $this->view->filters->deficit_only = (int)Yii::$app->request->get('deficit_only', 0);

        $admin = new AdminCarts;
        /*$admin->loadCustomersBaskets();
        $ids = $admin->getVirtualCartIDs();*/
        $this->view->filters->admin_choice = [];
        if (!$this->tmpOrderController && $ids) {
            foreach ($ids as $_ids) {
                $this->view->filters->admin_choice[] = $this->renderAjax('mini', [
                    'ids' => $_ids,
                    'customer' => \common\helpers\Customer::getCustomerData($_ids)]
                );
            }
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

        $plFilters = \Yii::$app->request->get('platform', []);
        $_pl = ArrayHelper::map(platform::getList(false), 'id', 'id');
        if (!empty($plFilters)) {
          $plFilters = array_intersect($plFilters, $_pl);
        }
        if (is_array($plFilters) && count($plFilters)==1) {
          $theme_platform_id = $plFilters[0];
        } else {
          $theme_platform_id = platform::defaultId();
        }
/*
// batch print extra documents
        $addedPages = \common\models\ThemesSettings::find()->alias('ts')
            ->innerJoin(TABLE_THEMES . ' t', 't.theme_name=ts.theme_name')
            ->innerJoin(TABLE_PLATFORMS_TO_THEMES . ' p2t', 't.id=p2t.theme_id')
            ->select(['setting_name','setting_value', 'ts.id'])
            ->where([
                'p2t.platform_id' => $theme_platform_id,
                'setting_group' => 'added_page',
                'setting_name' => ['packingslip', 'invoice'],
            ])
            ->orderBy('setting_name')
            ->asArray()
            ->all();
        $addedPages = ArrayHelper::map($addedPages, 'id', 'setting_value', 'setting_name');*/
        $addedPages = [];

        return $this->render('index', [
                    'isMultiPlatform' => \common\classes\platform::isMulti(),
                    'platforms' => \common\classes\platform::getList(true, true),
                    'departments' => $departments,
                    'ordersStatuses' => $ordersStatuses,
                    'ordersStatusesOptions' => $ordersStatusesOptions,
                    'addedPages' => $addedPages,
                    'tmpOrderController' => true,
        ]);
    }

    public function actionOrderHistory() {
        $this->layout = false;

        $orders_id = Yii::$app->request->get('orders_id');

        $params = [];

        $history = [];

        $orders_history_query = tep_db_query("select * from tmp_orders_history o left join " . TABLE_ADMIN . " a on a.admin_id = o.admin_id where orders_id='" . (int) $orders_id . "' order by orders_history_id desc");
        while ($orders_history = tep_db_fetch_array($orders_history_query)) {
            $history[] = [
                'date' => \common\helpers\Date::datetime_short($orders_history['date_added']),
                'comments' => $orders_history['comments'], //Edited by Name of admin
                'admin' => ($orders_history['admin_id'] ? $orders_history['admin_firstname'] . ' ' . $orders_history['admin_lastname'] : '')
            ];

        }
        $params['history'] = $history;
        $cid = Yii::$app->request->get('cid', 0);

        $params['show_recovery_details'] = false;
        if (($ext = \common\helpers\Acl::checkExtensionAllowed('RecoverShoppingCart')) && defined('RCS_SHOW_AT_ORDERS') && RCS_SHOW_AT_ORDERS == 'true' && $orders_id && $cid) {
            $params['show_recovery_details'] = true;

            $ext::initTranslation('order-history');

            $params['ua'] = \common\helpers\System::get_ga_detection($orders_id);
            $params['ua_tracking'] = \common\models\EcommerceTracking::findAll(['orders_id'=>$orders_id]);

            //errors
            $params['errors'] = \common\models\CustomersErrors::find()->linkingTo(\common\models\TmpOrders::class)
                    ->where(['orders_id' => $orders_id])->orderBy('error_date desc')->all();

            //contacts
            $scart = tep_db_query("select * from " . TABLE_SCART . " s inner join tmp_orders o on o.orders_id = '" . (int) $orders_id . "' where o.basket_id = s.basket_id and s.customers_id = '" . (int) $cid . "'");
            if (tep_db_num_rows($scart)) {
                $_scart = tep_db_fetch_array($scart);
                $_scart['recovered'] = $_scart['recovered'] ? TEXT_BTN_YES : TEXT_BTN_NO;
                $_scart['contacted'] = $_scart['contacted'] ? TEXT_BTN_YES : TEXT_BTN_NO;
                $_scart['workedout'] = $_scart['workedout'] ? TEXT_BTN_YES : TEXT_BTN_NO;
                $params['scart'] = $_scart;
                //gv && cc
                $coupons = tep_db_query("select cet.coupon_id, cet.sent_firstname, cet.sent_lastname, cet.date_sent, c.coupon_code, c.coupon_amount, c.coupon_currency, c.coupon_type, c.coupon_active from " . TABLE_COUPON_EMAIL_TRACK . " cet left join " . TABLE_COUPONS . " c on c.coupon_id = cet.coupon_id inner join tmp_orders o on o.orders_id = '" . (int) $orders_id . "' where o.basket_id = cet.basket_id and cet.customer_id_sent = '" . (int) $cid . "'");
                if (tep_db_num_rows($coupons)) {
                    $_cops = [];
                    $currencies = Yii::$container->get('currencies');
                    while ($cop = tep_db_fetch_array($coupons)) {
                        $_cops[$cop['coupon_id']] = $cop;
                        $_cops[$cop['coupon_id']]['coupon_amount'] = $cop['coupon_code'] . ' (' . ($cop['coupon_type'] == 'F' || $cop['coupon_type'] == 'G' ? $currencies->format($cop['coupon_amount'], false, $cop['coupon_currency']) : ($cop['coupon_type'] == 'P' ? round($cop['coupon_amount'], 2) . '%' : '')) . ') ' . ($cop['coupon_type'] == 'G' && $cop['coupon_active'] == 'N' ? TEXT_USED : '') . ' - ' . $cop['sent_firstname'] . ' ' . $cop['sent_lastname'];
                        $_cops[$cop['coupon_id']]['coupon_type'] = ($cop['coupon_type'] == 'G' ? GIFT_CERTIFICATE : DISCOUNT_COUPON);
                    }
                    $params['coupons'] = $_cops;
                }
                tep_db_free_result($coupons);
            }
        }
        return $this->renderAjax('recovery', $params);

        //return $this->render('order-history.tpl');
    }

/**
 *
 * @global int $login_id
 * @global int  $access_levels_id
 */
    public function actionOrderlist() {
        global $login_id;
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/orders');

        $manager = \common\services\OrderManager::loadManager(new \common\classes\shopping_cart);
        $manager->cleanupTemporaryGuests();

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
            $searchFields = ['o.customers_telephone', 'o.delivery_telephone', 'o.billing_telephone', 'o.customers_lastname', 'o.customers_firstname', 'o.customers_email_address', 'o.orders_id', 'op.products_model', 'op.products_name'];

            if ( is_numeric($keywords) ) {
                $searchFields[] = 'o.api_client_order_id';
            }

            /** @var \common\extensions\InvoiceNumberFormat\InvoiceNumberFormat $infExt */
            if (!$this->tmpOrderController && $infExt = \common\helpers\Acl::checkExtensionAllowed('InvoiceNumberFormat', 'allowed')){
                if ($infExt::hasInvoiceNumber()){
                    $searchFields[] = 'o.invoice_number';
                }
            }

            $operator = 'LIKE';
            $operator1 = 'or';
            if (substr($keywords, 0, 1) == '!') {
                $operator = 'NOT LIKE';
                $operator1 = 'and';
                $keywords = substr($keywords, 1);
            }

            if (!empty($searchFields) &&  is_array($searchFields)) {
                $search_condition = " and ( " . implode(" $operator '%" . $keywords . "%' $operator1 ", $searchFields) . " $operator '%" . $keywords . "%' )";
            }

        } else {
            $search_condition = "";
        }
        $_session->set('search_condition', $search_condition);

        $formFilter = Yii::$app->request->get('filter');
        $output = [];
        parse_str($formFilter, $output);

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir'] && $_GET['draw'] != 1) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "o.customers_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 1:
                    $orderBy = "ot.text " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 2:
                    $orderBy = "o.date_purchased " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 3:
                    $orderBy = "s.orders_status_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "o.date_purchased desc, o.orders_id desc";
                    break;
            }
        } else {
            $orderBy = "o.date_purchased desc, o.orders_id desc";
            if ( isset($output['mode']) && $output['mode']=='need_process' ) {
                $orderBy = "IFNULL(`o`.`hold_on_date`, `o`.`date_purchased`) desc";
            }
        }

        $CutOffTime = new \common\classes\CutOffTime();

        $statusGroupData = \yii\helpers\ArrayHelper::index(\common\models\OrdersStatusGroups::find()
            ->select(['orders_status_groups_name', 'orders_status_groups_color', 'orders_status_groups_id'])
            ->where(['language_id' => (int) $languages_id, 'orders_status_type_id'=>\common\helpers\Order::getStatusTypeId()])
            ->asArray()
            ->all(),'orders_status_groups_id');

        $_orders_products_joined = false;
        $orders_query_raw = 
            //(new \yii\db\Query())
            \common\models\TmpOrders::find()
            ->select("o.orders_id, s.orders_status_name, s.orders_status_groups_id ")
            //->addSelect("c.customers_gender")
            //->addSelect("ad.admin_firstname, ad.admin_lastname")
            //->addSelect('ot.text_inc_tax as order_total')
            ->from([TABLE_ORDERS_STATUS . " s", "tmp_orders o"])
            //->leftJoin(TABLE_ORDERS_TOTAL . " ot", "(o.orders_id = ot.orders_id and ot.class = 'ot_total')")
            //->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "(op.orders_id = o.orders_id)")
            //->leftJoin(TABLE_ADMIN. " ad", "(ad.admin_id = o.admin_id)")
        ;
        if ((isset($_GET['in_stock']) && $_GET['in_stock'] != '')){
            $_orders_products_joined = true;
            $orders_query_raw->leftJoin("tmp_orders_products op", "(op.orders_id = o.orders_id)");
            $orders_query_raw->addSelect("BIT_AND(" . (\common\helpers\Extensions::isAllowed('Inventory') ? "if(i.products_quantity is not null,if((i.products_quantity>=op.products_quantity),1,0),if((p.products_quantity>=op.products_quantity),1,0))" : "if((p.products_quantity>=op.products_quantity),1,0)") . ") as in_stock");
            $orders_query_raw->leftJoin(TABLE_PRODUCTS . " p", "(p.products_id = op.products_id)");
            if (\common\helpers\Extensions::isAllowed('Inventory')){
                $orders_query_raw->leftJoin(TABLE_INVENTORY . " i", "(i.prid = op.products_id and i.products_id = op.uprid)");
            }
        }
        if (\common\helpers\Acl::checkExtensionAllowed('Handlers', 'allowed')) {
            if ( !$_orders_products_joined ) {
                $_orders_products_joined = true;
                $orders_query_raw->leftJoin("tmp_orders_products op", "(op.orders_id = o.orders_id)");
            }
            $orders_query_raw->leftJoin("handlers_products hp", "hp.products_id = op.products_id");
        }

        $_orders_products_allocate_joined = false;
        if (!$this->tmpOrderController && !(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_TMP_ORDERS', 'RULE_ALLOW_WAREHOUSES']))) {
            $orders_query_raw->leftJoin("orders_products_allocate opa", "opa.orders_id = o.orders_id");
            $_orders_products_allocate_joined = true;
        }
        if (!$this->tmpOrderController && !(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_TMP_ORDERS', 'RULE_ALLOW_SUPPLIERS']))) {
            if ( !$_orders_products_allocate_joined ) {
                $orders_query_raw->leftJoin("orders_products_allocate opa", "opa.orders_id = o.orders_id");
                $_orders_products_allocate_joined = true;
            }
        }

        //$orders_query_raw->leftJoin(TABLE_CUSTOMERS . " c", "(o.customers_id = c.customers_id)");
        $orders_query_raw->where("o.orders_status = s.orders_status_id " . $search_condition . " and s.language_id = '" . (int)$languages_id . "' and s.orders_status_groups_id IN('" . implode("','", array_keys($statusGroupData)) . "') ");
        if ( strpos($search_condition,' op.')!==false && !$_orders_products_joined ){
            $orders_query_raw->leftJoin("tmp_orders_products op", "(op.orders_id = o.orders_id)");
            $_orders_products_joined = true;
        }

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
            $handlers_array = [];
            $handlers_array = $ext::getHandlersQuery((int)$access_levels_id);

            $orders_query_raw->andWhere(['in', 'hp.handlers_id', $handlers_array]);
        }

        if (!$this->tmpOrderController && !(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_TMP_ORDERS', 'RULE_ALLOW_WAREHOUSES']))) {
            $warehousesArray = [];
            foreach (\common\models\AdminWarehouses::find()->where(['admin_id' => $login_id])->asArray()->all() as $warehouse) {
                $warehousesArray[] = $warehouse['warehouse_id'];
            }
            unset($warehouse);
            $orders_query_raw->andWhere(['in', 'opa.warehouse_id', $warehousesArray]);
            unset($warehousesArray);
        }
        if (!$this->tmpOrderController && !(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_TMP_ORDERS', 'RULE_ALLOW_SUPPLIERS']))) {
            $suppliersArray = [];
            foreach (\common\models\AdminSuppliers::find()->where(['admin_id' => $login_id])->asArray()->all() as $supplier) {
                $suppliersArray[] = $supplier['suppliers_id'];
            }
            unset($supplier);
            $orders_query_raw->andWhere(['in', 'opa.suppliers_id', $suppliersArray]);
            unset($suppliersArray);
        }

        if (tep_not_null($output['search'])) {
            $search = tep_db_prepare_input($output['search']);
            $operator = 'LIKE';
            if (substr($search, 0, 1) == '!') {
                $operator = 'NOT LIKE';
                $search = substr($search, 1);
            }

            switch ($output['by']) {
                case 'cID':
                    $orders_query_raw->andWhere("o.customers_id = '" . (int) $search . "'");
                    break;
                case 'oID':
                    $orders_query_raw->andWhere(["o.orders_id" => (int) $search]);
                    //$orders_query_raw->andWhere([ 'or', ["o.orders_id" => (int) $search], ['o.order_number' => $search]]);
                    break;
                case 'model': default:
                    if ( !$_orders_products_joined ) {
                       $_orders_products_joined = true;
                       $orders_query_raw->leftJoin("tmp_orders_products op", "(op.orders_id = o.orders_id)");
                    }
                    $orders_query_raw->andWhere([$operator, "op.products_model", $search]);
                    break;
                case 'name':
                    if ( !$_orders_products_joined ) {
                        $_orders_products_joined = true;
                        $orders_query_raw->leftJoin("tmp_orders_products op", "(op.orders_id = o.orders_id)");
                    }
                    $orders_query_raw->andWhere([$operator, "op.products_name", $search]);
                    break;
                case 'brand':
                    break;
                case 'batchName':
                    if ( !$_orders_products_allocate_joined ) {
                        $orders_query_raw->leftJoin("orders_products_allocate opa", "opa.orders_id = o.orders_id");
                        $_orders_products_allocate_joined = true;
                    }
                    $orders_query_raw->leftJoin("warehouses_products_batches wpb", "wpb.batch_id = opa.batch_id");
                    $orders_query_raw->andWhere([$operator, "wpb.batch_name", $search]);
                    break;
                case 'fullname':
                    $orders_query_raw->andWhere([$operator, "o.customers_name", $search]);
                    break;
                case 'email':
                    $orders_query_raw->andWhere([$operator, "o.customers_email_address", $search]);
                    break;
                case 'phone':
                    $orders_query_raw->andWhere([
                      ($operator=='LIKE'?'OR':'AND'),
                        [$operator, "o.customers_telephone", $search],
                        [$operator, "o.delivery_telephone", $search],
                        [$operator, "o.billing_telephone", $search],
                    ]);
                    break;
                case 'tracking_number':
                    $orders_query_raw->andWhere([$operator, "o.tracking_number", $search]);
                    break;
                case '':
                case 'any':
                    if ( !$_orders_products_joined ) {
                       $_orders_products_joined = true;
                       $orders_query_raw->leftJoin("tmp_orders_products op", "(op.orders_id = o.orders_id)");
                    }
                    $orders_query_raw->leftJoin("tmp_orders_status_history osh", "o.orders_id = osh.orders_id");
                    $orders_query_raw->andFilterWhere([
                        'or',
                        ['o.orders_id' => $search],
                        [
                        ($operator=='LIKE'?'OR':'AND'),
                        //[$operator, 'o.order_number',$search],
                        [$operator, 'op.products_model',$search],
                        [$operator, 'op.products_name',$search],
                        [$operator, 'o.customers_name',$search],
                        [$operator, 'o.customers_email_address',$search],
                        [$operator, "osh.comments", $search],
                        [$operator, 'o.tracking_number',$search],
                        [$operator, "o.customers_telephone", $search],
                        [$operator, "o.delivery_telephone", $search],
                        [$operator, "o.billing_telephone", $search],
                          ]
                        ]);
                    break;
            }
        }
        if (isset($output['delivery_country']) && !empty($output['delivery_country'])) {
            $orders_query_raw->andWhere("o.delivery_country='" . tep_db_input($output['delivery_country']) . "'");
        }
        if (isset($output['delivery_state']) && !empty($output['delivery_state'])) {
            $orders_query_raw->andWhere("o.delivery_state='" . tep_db_input($output['delivery_state']) . "'");
        }
        if (isset($output['status']) && is_array($output['status'])) {
            $orders_query_raw->andWhere(['in', 's.orders_status_id', $output['status']]);
        }
        if ( isset($output['mode']) && $output['mode']=='need_process' ) {
            if ( defined('ORDERS_NOT_PROCESSED_ORDER_STATUSES') ) {
                $_filter_need_statuses = implode("','",array_map('intval',explode(',',ORDERS_NOT_PROCESSED_ORDER_STATUSES)));
                if ( strlen($_filter_need_statuses)>0 ) {
                    $orders_query_raw->andWhere("o.orders_status IN ('".$_filter_need_statuses."')");
                }
            }else {
                $orders_query_raw->andWhere("s.orders_status_groups_id IN (1,2)");
            }
        }

        if (isset($output['date'])) {
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

        if (isset($output['payments']) && !empty($output['payments'])) {
            $orders_query_raw->andWhere(['in', 'o.payment_method', $output['payments']]);
        }

        if (isset($output['shipping']) && !empty($output['shipping'])) {
            $orders_query_raw->andWhere(['in', 'o.shipping_method', $output['shipping']]);
        }

        if (isset($output['fc_id']) && is_array($output['fc_id']) && count($output['fc_id'])) {
            $orders_query_raw->innerJoin(TABLE_COUPON_REDEEM_TRACK . " crt", "o.orders_id=crt.order_id and crt.coupon_id in (" .implode(",", $output['fc_id']). ") ");
        }

        if (isset($output['fc_code']) && !empty($output['fc_code'])) {
            $orders_query_raw->innerJoin("tmp_orders_total otfc", "o.orders_id=otfc.orders_id and otfc.class='ot_coupon' and otfc.title like '%" . tep_db_input($output['fc_code']) . "%'");
        }

        if (isset($output['flag']) && $output['flag'] > 0) {
            $orders_query_raw->innerJoin('orders_markers' . " omf", "o.orders_id=omf.orders_id and omf.flags='" . (int)$output['flag'] . "'");
        }

        if (!$this->tmpOrderController && isset($output['marker']) && $output['marker'] > 0) {
            $orders_query_raw->innerJoin('orders_markers' . " omm", "o.orders_id=omm.orders_id and omm.markers='" . (int)$output['marker'] . "'");
        }

        if (( (isset($output['fp_from']) && !empty($output['fp_from'])) || (isset($output['fp_to']) && !empty($output['fp_to'])) ) && (isset($output['fp_class']) && !empty($output['fp_class'])) ) {
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
          $fp_to = preg_replace('/[^0-9\.]/', '', $output['fp_to']);
            $orders_query_raw->innerJoin("tmp_orders_total otfp", "o.orders_id=otfp.orders_id and otfp.class='" . tep_db_input($output['fp_class']). "'"
                . (tep_not_null($output['fp_from'])?" and round(otfp.value, 2)>='" . tep_db_input(round($fp_from,2)) . "'":'')
                . (tep_not_null($output['fp_to'])?" and round(otfp.value,2)<='" . tep_db_input(round($fp_to,2)) . "'":'')
                . "");
        }
        if (isset($output['walkin']) && is_array($output['walkin'])) {
            $orders_query_raw->andWhere(["in", "o.admin_id", $output['walkin']]);
        }

        if (isset($output['deficit_only']) AND ((int)$output['deficit_only'] > 0)) {
            if ( !$_orders_products_joined ) {
               $_orders_products_joined = true;
               $orders_query_raw->leftJoin("tmp_orders_products op", "(op.orders_id = o.orders_id)");
            }
            $orders_query_raw->andWhere('((op.products_quantity - op.qty_cnld) > op.qty_rcvd)');
        }

        $orders_query_raw->groupBy("o.orders_id");
        if ((isset($_GET['in_stock']) && $_GET['in_stock'] != '')){
            $orders_query_raw->having("in_stock " . ($_GET['in_stock'] > 0 ? " > 0" : " < 1"));
        }

        $orders_query_raw->orderBy($orderBy);
/** @var \common\extensions\Neighbour\Neighbour $ext */
        if (!$this->tmpOrderController && $ext = \common\helpers\Acl::checkExtension('Neighbour', 'allowed')){
            if ($ext::allowed()){
                $ext_query = $ext::getQuery($orders_query_raw);
            }
        }
//echo $orders_query_raw->createCommand()->getRawSql();
        $_session->set('filter', $orders_query_raw->where);

        $orders_query_numrows = $orders_query_raw->count();

        $orders_query_raw->limit($length)->offset($start)->with('ordersTotals');
        if (SHOW_PRODUCTS_ON_ORDER_LIST !== 'False'){
            $orders_query_raw->with('ordersProducts');
        }
//echo $orders_query_raw->createCommand()->getRawSql()."\n\n";
        $ordersAll = $orders_query_raw->asArray()->all();
        // {{ append orders status group table
        foreach ($ordersAll as $__idx=>$_row){
            if (isset($statusGroupData[$_row['orders_status_groups_id']])){ $ordersAll[$__idx] = array_merge($ordersAll[$__idx], $statusGroupData[$_row['orders_status_groups_id']]); }
        }
        // }} append orders status group table
        // {{ append page data
        if ( count($ordersAll)>0 ) {
            $_pageOrderIds = array_map(function($row){ return $row['orders_id']; },$ordersAll);
            $_pageOrderIdToIdx = array_flip($_pageOrderIds);

            $completePageData = \common\models\TmpOrders::find()
                ->select('o.*')
                ->addSelect("c.customers_gender")
                ->addSelect("ad.admin_firstname, ad.admin_lastname")
                ->addSelect('ot.text_inc_tax as order_total')
                ->from("tmp_orders o")
                ->leftJoin("tmp_orders_total ot", "(o.orders_id = ot.orders_id and ot.class = 'ot_total')")
                ->leftJoin(TABLE_ADMIN. " ad", "(ad.admin_id = o.admin_id)")
                ->leftJoin(TABLE_CUSTOMERS . " c", "(o.customers_id = c.customers_id)")
                ->where(['IN', 'o.orders_id', $_pageOrderIds])
                ->asArray()->all();

            foreach($completePageData as $__order_data){
                $__idx = $_pageOrderIdToIdx[$__order_data['orders_id']];
                $ordersAll[$__idx] = array_merge($__order_data, $ordersAll[$__idx]);
            }

            if (!$this->tmpOrderController && $ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')) {
                $orderMarkers = $ext::getOrderMarkersBatch($_pageOrderIds);
                foreach($orderMarkers as $_testOrderId=>$_orderMarker) {
                    $__idx = $_pageOrderIdToIdx[$_testOrderId];
                    $ordersAll[$__idx]['orderMarkers'] = $_orderMarker;
                }
            }
        }
        // }} append page data
        if ( \common\helpers\Acl::checkExtensionAllowed('FraudAddress','allowed') ) {
            $ordersAll = \common\extensions\FraudAddress\FraudAddress::ordersListing($ordersAll);
        }

        $markers = [];
        if (!$this->tmpOrderController && $ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $markers = $ext::getMarkers();
        }

        $flags = [];
        if (!$this->tmpOrderController && $ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $flags = $ext::getFlags();
        }

        $responseList = array();
        $stack = [];
        if ($ordersAll){
            $selected_platform_id = \common\classes\platform::firstId();
            Yii::$app->get('platform')->config($selected_platform_id)->constant_up();
            foreach($ordersAll as $orders){
                $p_list = '';
                $p_list2 = '';
                $counter = 0;
                $max_view = MAX_PRODUCTS_IN_ORDERS;
                if (SHOW_PRODUCTS_ON_ORDER_LIST !== 'False'){
                    $_product_block_cache_key = 'orders_list_products_'.(int)$orders['orders_id'].'_'.strtotime($orders['last_modified']);
                    if ( !$p_list = Yii::$app->getCache()->get($_product_block_cache_key) ) {
                        if (is_array($orders['ordersProducts']) && count($orders['ordersProducts']) > 0) {
                            foreach ($orders['ordersProducts'] as $__idx => $ordersProduct) {
                                $orders['ordersProducts'][$__idx]['name'] = $ordersProduct['products_name'];
                                $orders['ordersProducts'][$__idx]['id'] = $ordersProduct['uprid'];
                            };
                            if (ProductNameDecorator::instance()->useInternalNameForOrder()) {
                                $orders['ordersProducts'] = ProductNameDecorator::instance()->getUpdatedOrderProducts($orders['ordersProducts'], $orders['language_id'], $orders['platform_id']);
                            }

                        foreach($orders['ordersProducts'] as $products){
                            $products['name'] = htmlentities($products['name']);
                            $counter++;
                            $p_list_tmp = '<div class="ord-desc-row"><div>' . $products['products_quantity'] . ' x ' . (mb_strlen($products['name']) > 48 ? mb_substr($products['name'], 0, 48) . '...' : $products['name']) . '</div><div class="order_pr_model">' . 'SKU: ' . (mb_strlen($products['products_model']) > 8 ? mb_substr($products['products_model'], 0, 8) . '...' : $products['products_model']) . ($products['products_model'] ? '<span>' . $products['products_model'] . '</span>' : '') . '</div></div>';
                            if ($counter <= $max_view) {
                                $p_list .= $p_list_tmp;
                            }
                            if ($counter == $max_view + 1) {
                                $p_list2 = $p_list_tmp;
                            }
                        }
                    }
                    if ($counter == $max_view + 1) {
                        $p_list .= $p_list2;
                    }
                    if ($counter > $max_view + 1) {
                        $p_list .= '<div class="ord-desc-row ord-desc-row-more"><div>...</div></div>';
                        $p_list .= '<div class="ord-desc-row ord-desc-row-more"><div>' . $max_view . ' ' . TEXT_OF_TOTAL . ' ' . $counter . '</div></div>';}
                        Yii::$app->getCache()->set($_product_block_cache_key, $p_list, 600);
                    }
                }

                $deliveryInfo = '';
                $timestamp = strtotime($orders['date_purchased']);
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('DelayedDespatch', 'allowed')){
                    $deliveryInfo = $ext::showDeliveryDate($orders['delivery_date']);
                }
                if (date('Y-m-d', $timestamp) == date('Y-m-d') && mb_strlen($deliveryInfo) == 0) {
                    $deliveryInfo .= '</div><div class="ord-date-purch-delivery' . ($CutOffTime->isTodayDelivery($orders['date_purchased'], $orders['platform_id']) ? ' ord-date-purch-delivery-check' : '') . '">' . TEXT_TODAY_DELIVERY . ':</div><div class="ord-date-purch-delivery' . ($CutOffTime->isNextDayDelivery($orders['date_purchased'], $orders['platform_id']) ? ' ord-date-purch-delivery-check' : '') . '">' . TEXT_NEXT_DELIVERY . ':</div>';
                }

                //------
                $customers_email_address = $orders['customers_email_address'];
                $w = preg_quote(trim($search));
                if (!empty($w)) {
                    $regexp = "/($w)(?![^<]+>)/i";
                    $replacement = '<b style="color:#ff0000">\\1</b>';
                    $orders['customers_name'] = preg_replace($regexp, $replacement, $orders['customers_name']);
                    $p_list = preg_replace($regexp, $replacement, $p_list);
                    $customers_email_address = preg_replace($regexp, $replacement, $orders['customers_email_address']);
                }
                //------
                $orderTotals = '';

                if (is_array($orders['ordersTotals']) && count($orders['ordersTotals'])){
                    foreach($orders['ordersTotals'] as $totals){
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
                                        $orderTotals .= '<div class="' . $result['class'] . (ArrayHelper::getValue($result, 'show_line') ? ' totals-line' : '') . '"><span>' . $result['title'] . '</span><span>' . $result['text'] . '</span></div>';
                                    } else {
                                        $orderTotals .= '<div><span>' . $totals['title'] . '</span><span>' . $totals['text'] . '</span></div>';
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
                $todayDate = \common\helpers\Date::date_short(date('Y-m-d'));
                $purchasedDate = str_replace($todayDate, TEXT_TODAY, $purchasedDate);
                $cusColumn = '';
                if ($orders['customers_id']){
                    $cusColumn = '<div class="ord-name ord-gender ord-gender-' . $orders['customers_gender'] . ' click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $orders['orders_id']]) . '">'.
                    (\common\models\Customers::findOne($orders['customers_id'])?
                    '<a href="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $orders['customers_id']]) . '" title="' . strip_tags($orders['customers_name']) . '">' . Html::encode(self::cropStr($orders['customers_name'], 22)) . '</a>':Html::encode(self::cropStr($orders['customers_name'], 22))) .
                    '</div><a href="mailto:' . $orders['customers_email_address'] . '" class="ord-name-email" title="' . strip_tags($customers_email_address) . '">' . self::cropStr($customers_email_address, 22) . '</a><div class="ord-location" style="margin-top: 5px;">' . Html::encode($orders['customers_postcode']) . '<div class="ord-total-info ord-location-info"><div class="ord-box-img"></div><b>' . Html::encode($orders['customers_name']) . '</b>' . Html::encode($orders['customers_street_address']) . '<br>' . Html::encode($orders['customers_city'] . ', ' . $orders['customers_state']) . '&nbsp;' . Html::encode($orders['customers_postcode']) . '<br>' . $orders['customers_country'] . '</div></div>';
                } elseif ($orders['admin_id']) {
                    $customer_delivery_name = '('.$orders['delivery_name'].')';
                    $customer_delivery_info = '<div class="ord-location" style="margin-top: 5px;">' . $orders['delivery_postcode'] . '<div class="ord-total-info ord-location-info"><div class="ord-box-img"></div><b>' . Html::encode($orders['delivery_name']) . '</b>' . Html::encode($orders['delivery_street_address']) . '<br>' . Html::encode($orders['delivery_city'] . ', ' . $orders['delivery_state']) . '&nbsp;' . Html::encode($orders['delivery_postcode']) . '<br>' . $orders['delivery_country'] . '</div></div>';
                    $cusColumn = '<div class="ord-name click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $orders['orders_id']]) . '">' . (defined('TEXT_WALKIN_ORDER')? TEXT_WALKIN_ORDER: '') . $orders['admin_firstname'] . ' ' . $orders['admin_lastname']. ' '.$customer_delivery_name.'</div>'.$customer_delivery_info;
                }

                $orderRow = [];
                if ( $orders['hold_on_date'] ) {
                    $orderRow['DT_RowClass'] = ArrayHelper::getValue($orderRow, 'DT_RowClass') . ' holdOnOrder';
                    $purchasedDate .= '<div class="holdOrderInfo">'.sprintf(LIST_ORDER_HOLD_ON, \common\helpers\Date::date_short($orders['hold_on_date'])).'</div>';
                }

                if ( isset($orders['isFraud']) && $orders['isFraud'] ) {
                    $orderRow['DT_RowClass'] = ArrayHelper::getValue($orderRow, 'DT_RowClass') . ' fraudOrder';
                }

                $orderRow[] = '<input type="checkbox" class="uniform">' . '<input class="cell_identify" type="hidden" value="' . $orders['orders_id'] . '">';

                $coloredRow = '';
                if (!$this->tmpOrderController && $ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')) {
                    $orderMarkers = $orders['orderMarkers'];
                    if (isset($orderMarkers['markers']) && isset($markers[$orderMarkers['markers']])){
                        $coloredRow = $markers[$orderMarkers['markers']];
                    }
                    $paint = '<div class="fa-paint-brush" onclick="sendOrderMarker(' . (int)$orders['orders_id'] . ', ' . (int)($orderMarkers['markers'] ?? 0) . ')"></div>';
                    if (isset($orderMarkers['flags']) && isset($flags[$orderMarkers['flags']])){
                        $orderRow[] = '<div class="fa-flag" style="color: ' . $flags[$orderMarkers['flags']] . ';" onclick="sendOrderFlag(' . (int)$orders['orders_id'] . ', ' . (int)$orderMarkers['flags'] . ')"></div>' . $paint;
                    } else {
                        $orderRow[] = '<div class="fa-flag-o" onclick="sendOrderFlag(' . (int)$orders['orders_id'] . ')"></div>' . $paint;
                    }
                }

                $orderRow[] = $cusColumn . '<input class="row_colored" type="hidden" value="' . $coloredRow . '">';
                $orderRow[] = '<div class="ord-total click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $orders['orders_id']]) . '">' . $orders['order_total'] . '<div class="ord-total-info"><div class="ord-box-img"></div>' . $orderTotals . '</div></div>';
                $orderRow[] = '<div class="ord-desc-tab click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $orders['orders_id']]) . '"><a href="' . \Yii::$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $orders['orders_id']]) . '" class="order-inf"><span class="ord-id">' . TEXT_ORDER_NUM . (!empty($orders['order_number'])?$orders['order_number']:$orders['orders_id']) . '</span> ' . (!empty($orders['invoice_number'])?' <span class="inv-id"><span class="title">' . TEXT_INVOICE . '</span>' . $orders['invoice_number'] . '</span> ':'')   . $departmentInfo . (tep_not_null($orders['payment_method']) ? (SHOW_PRODUCTS_ON_ORDER_LIST === 'False' ? '<br>' : ' ') . TEXT_VIA . ' ' . strip_tags($orders['payment_method']) : '') . (tep_not_null($orders['shipping_method']) ? ' ' . TEXT_DELIVERED_BY . ' ' . strip_tags($orders['shipping_method']) : '') . '</a>' . (SHOW_PRODUCTS_ON_ORDER_LIST !== 'False' ? $p_list : '') . '</div>';
                $orderRow[] = '<div class="ord-date-purch click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $orders['orders_id']]) . '">' . $purchasedDate . $deliveryInfo;
                $orderRow[] = '<div class="ord-status click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => $orders['orders_id']]) . '"><span><i style="background: ' . $orders['orders_status_groups_color'] . ';"></i>' . $orders['orders_status_groups_name'] . '</span><div>' . $orders['orders_status_name'] . '</div></div>';

                if (!$this->tmpOrderController && $ext = \common\helpers\Acl::checkExtension('Neighbour', 'allowed')){
                    if ($ext::allowed()){
                        $orderRow[] = ($orders['to_neighbour']?'<div class=" ord-date-purch-delivery ord-date-purch-delivery-check">':'');
                    }
                }

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

    private static function cropStr($str, $length)
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

        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');
        $orders = Orders::find()->alias('o')/*->select('o.customers_id, o.orders_status, o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.platform_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.language_id, o.currency_value')*/
            ->andWhere(['orders_id' => (int) $orders_id ])->asArray()->one();
        if (empty($orders)) {
            die("Please select order.");
        }

        $_pl = ArrayHelper::map(platform::getList(false), 'id', 'id');
        if (!in_array($orders['platform_id'], $_pl)) {
          $orders['platform_id'] = platform::defaultId();
        }

        $oInfo = new \objectInfo($orders);
        return $this->render('actions', ['oInfo' => $oInfo]);
    }

    public function actionOrderReassign() {
        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $orders_query = tep_db_query("select o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, tmp_orders o left join tmp_orders_total ot on (o.orders_id = ot.orders_id) where o.orders_id = '" . (int) $orders_id . "'");
        $orders = tep_db_fetch_array($orders_query);

        if (!is_array($orders)) {
            die("Wrong order data.");
        }

        $oInfo = new \objectInfo($orders);
        return $this->render('reassign', ['oInfo' => $oInfo]);

    }

    public function actionConfirmedOrderReassign() {
        $customers_id = Yii::$app->request->post('customers_id');
        $orders_id = Yii::$app->request->post('orders_id');

        $customers_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customers_id . "'");
        $customers = tep_db_fetch_array($customers_query);
        if (is_array($customers) && $orders_id > 0) {
            tep_db_query("update tmp_orders set customers_id = '" . (int) $customers_id . "', customers_name = '" . tep_db_input($customers['customers_firstname'] . ' ' . $customers['customers_lastname']) . "', customers_firstname = '" . tep_db_input($customers['customers_firstname']) . "', customers_lastname = '" . tep_db_input($customers['customers_lastname']) . "', customers_email_address = '" . tep_db_input($customers['customers_email_address']) . "' where orders_id = '" . (int) $orders_id . "';");
        }
    }

    public function actionOrderdelete() {

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $admin = new AdminCarts;
        //2do $admin->deleteCartByOrder($orders_id);

        \common\helpers\Order::remove_tmp_order($orders_id);
    }

    public function actionConfirmorderdelete() {

        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $orders_query = tep_db_query("select o.child_id, o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, tmp_orders o left join tmp_orders_total ot on (o.orders_id = ot.orders_id) where o.orders_id = '" . (int) $orders_id . "'");
        $orders = tep_db_fetch_array($orders_query);

        if (!is_array($orders)) {
            die("Wrong order data.");
        }

        $oInfo = new \objectInfo($orders);

        echo tep_draw_form('orders', FILENAME_ORDERS, \common\helpers\Output::get_all_get_params(array('action')) . 'action=deleteconfirm', 'post', 'id="orders_edit" onSubmit="return deleteOrder();"');
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_ORDER . '</div>';
        echo '<div class="col_desc">' . TEXT_INFO_DELETE_INTRO . '</div>';
        echo '<div class="row_or_wrapp">';
        echo '<div class="row_or"><div>' . TEXT_INFO_DELETE_DATA . ':</div><div>' . $oInfo->customers_name . '</div></div>';
        echo '<div class="row_or"><div>' . TEXT_INFO_DELETE_DATA_OID . ':</div><div>' . $oInfo->orders_id . '</div></div>';

        echo '</div>';
        ?>
        <div class="btn-toolbar btn-toolbar-order">
            <?php
            if (empty($oInfo->child_id)) {
                echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return cancelStatement()">';
            } else {
                echo TEXT_CANT_DELETE_HAS_CHILD . '<a href="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $oInfo->child_id]) . '">' . $oInfo->child_id .'</a><br>';
                echo '<input type="button" class="btn btn-no-margin btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return cancelStatement()">';
            }
            echo tep_draw_hidden_field('orders_id', $oInfo->orders_id);
            ?>
        </div>
        </form>
        <?php
    }

    public function LoadPlatformDetails($entry, $platform = 0) {
        $entry->platforms = platform::getList(false);
        if (!$platform) {
            $platform = platform::defaultId();
        }
        $entry->default_platform = $platform;
        $platform_config = new platform_config($entry->default_platform);

        //currency
        $platform_currencies = $platform_config->getAllowedCurrencies();
        if ($platform_currencies) {
            $_tmp = [];
            foreach ($platform_currencies as $pc) {
                $_tmp[] = ['id' => $pc, 'text' => $pc];
            }
            $entry->platform_currencies = $_tmp;
        } else {
            $entry->platform_currencies = [['id' => DEFAULT_CURRENCY, 'text' => DEFAULT_CURRENCY]];
        }
        if ($this->view->convert && isset($_GET['basket_id'])) {
            $params = tep_db_fetch_array(tep_db_query("select currency, language_id from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $entry->customer_id . "' and basket_id = '" . (int) $_GET['basket_id'] . "'"));
        }
        if ($this->view->convert && isset($params['currency']) && tep_not_null($params['currency'])) {
            $entry->defualt_platform_currency = $params['currency'];
        } elseif ($c = $platform_config->getDefaultCurrency()) {
            $entry->defualt_platform_currency = $c;
        } else {
            $entry->defualt_platform_currency = DEFAULT_CURRENCY;
        }

        //language
        global $lng;
        $platform_languages = $platform_config->getAllowedLanguages();
        if ($platform_languages) {
            $_tmp = [];
            foreach ($platform_languages as $pl) {
                $_tmp[] = ['id' => $lng->catalog_languages[$pl]['id'], 'text' => $lng->catalog_languages[$pl]['name']];
            }
            $entry->platform_languages = $_tmp;
        } else {
            $entry->platform_languages = [['id' => $lng->catalog_languages[DEFAULT_LANGUAGE]['id'], 'text' => $lng->catalog_languages[DEFAULT_LANGUAGE]['name']]];
        }

        if ($this->view->convert && isset($params['language_id']) && $params['language_id'] > 0) {
            $entry->defualt_platform_language = $params['language_id'];
        } elseif ($c = $platform_config->getDefaultLanguage()) {
            $entry->defualt_platform_language = $lng->catalog_languages[$c]['id'];
        } else {
            $entry->defualt_platform_language = $lng->catalog_languages[DEFAULT_LANGUAGE]['id'];
        }
    }

    public function actionGetPlatformDetails() {
        $paltform_id = Yii::$app->request->get('platform_id', 0);
        if ($paltform_id) {
            $entry = new \stdClass();
            $this->loadPlatformDetails($entry, $paltform_id);
            return $this->renderAjax('currency_language', ['entry' => $entry]);
        }
        return '';
    }


    public function actionGetStates() {
        $response = '';
        if (Yii::$app->request->isPost) {
            $country_id = Yii::$app->request->post('country_id', 0);
            $prefix = Yii::$app->request->post('prefix', 0);
            $value = Yii::$app->request->post('value', '');
            $def_country_id = Yii::$app->request->post('def_country_id', 0);
            if ($country_id) {
                $zones = \common\helpers\Zones::get_country_zones($country_id);
                if (is_array($zones) && count($zones)) {
                    if (!is_numeric($value)) {
                        $value = \common\helpers\Zones::get_zone_id($country_id, $value);
                    }
                    $response = tep_draw_pull_down_menu($prefix . 'entry_state', $zones, $value, 'class="form-control"');
                } else {
                    $def_zones = \common\helpers\Zones::get_country_zones($def_country_id);
                    if (is_array($def_zones) && count($def_zones)) {
                        $hepler = \yii\helpers\ArrayHelper::map($def_zones, 'id', 'text');
                        $value = $hepler[$value];
                    }
                    $response = tep_draw_input_field($prefix . 'entry_state', $value, 'class="form-control"');
                }
            }
        }
        echo $response;
        exit();
    }

/**
 * filter - countries suggest
 */
    public function actionCountries() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $delivery_countries = \common\models\TmpOrders::find()
                ->select('delivery_country')
                ->andWhere(['like', 'delivery_country', $term])
            ->orderBy('delivery_country')
            //->indexBy('delivery_country')
            ->distinct()
            ->column();
        return $this->asJson($delivery_countries);
    }

/**
 * filter - states suggest
 */
    public function actionState() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $country = tep_db_prepare_input(Yii::$app->request->get('country'));

        $delivery_states = \common\models\TmpOrders::find()
                ->select('delivery_state')
                ->andWhere(['like', 'delivery_country', $country])
                ->andWhere(['like', 'delivery_state', $term])
            ->orderBy('delivery_state')
            //->indexBy('delivery_country')
            ->distinct()
            ->column();

        return $this->asJson($delivery_states);

    }

    public function actionOrdersdelete() {

        $this->layout = false;

        $selected_ids = Yii::$app->request->post('selected_ids');

        foreach ($selected_ids as $orders_id) {
            \common\helpers\Order::remove_tmp_order((int) $orders_id);
        }
    }

    public function actionCustomer() {

        $search = Yii::$app->request->get('term');
        $customers = [];
        if (!empty($search)){
            $cRep = new \common\models\repositories\CustomersRepository();
            foreach ($cRep->search($search, [0,1], [0,1])->all() as $customer) {
                $customers[] = ['id' => $customer->customers_id,
                  'value' => Html::encode($customer->customers_firstname . ' ' . $customer->customers_lastname . ' (' . $customer->customers_email_address . ')')
                  . (empty($customer->customers_status)?' ' . TEXT_INACTIVE:'')
                  . (!empty($customer->opc_temp_account)?' ' . TEXT_GUEST:'')
                  ];
            }
        }
        echo json_encode($customers);
    }


    
    public function actionOrdersexport() {
        if (tep_not_null($_POST['orders'])) {

            $filename = 'tmp_orders_' . strftime('%Y%b%d_%H%M') . '.csv';
            $writer = new \backend\models\EP\Formatter\CSV('write', array(), $filename);
            $writer->write_array(["Order ID", "Ship Method", "Shipping Company", "Shipping Street 1", "Shipping Street 2", "Shipping Suburb", "Shipping State", "Shipping Zip", "Shipping Country", "Shipping Name"]);

            foreach(\common\models\TmpOrders::find()->where(['orders_id' => array_map('intval', explode(',', $_POST['orders'])) ])->all() as $order){
                $writer->write_array([
                            $order->orders_id,
                            $order->shipping_method,
                            $order->delivery_company,
                            $order->delivery_street_address,
                            $order->delivery_suburb,
                            $order->delivery_city,
                            $order->delivery_state,
                            $order->delivery_postcode,
                            $order->delivery_country,
                            $order->delivery_name,
                        ]);
            }
        }
        exit;
    }


    public function actionConvert() {
        $ret = ['error' => 1, 'msg' => TEXT_UNEXPECTED_ERROR];
        $tmpOid = (int)Yii::$app->request->post('orders_id');
        $currentDate = (int)Yii::$app->request->post('current_date', 0);

        $manager = \common\services\OrderManager::loadManager(new \common\classes\shopping_cart);
        $manager->cleanupTemporaryGuests();
        $manager->clearOrderInstance();
        
        $oQuery = Orders::find()->where([
          'orders_id' => $tmpOid
          ]);
        if (false === \common\helpers\Acl::rule(['SUPERUSER'])) {
            global $login_id;
            $platforms = \common\models\AdminPlatforms::find()
                ->select('platform_id')
                ->where(['admin_id' => $login_id])->asArray()->column();
            $platforms[] = 0;
            $oQuery->andWhere(['platform_id' => $platforms]);
        }

        if (!$oQuery->exists()){
            $messageStack = \Yii::$container->get('message_stack');
            $messageStack->add_session(TEXT_ADMIN_ORDER_NOT_FOUND_ASSIGN_PLATFORMS, 'header', 'warning');

            return $this->redirect(\Yii::$app->urlManager->createUrl(['tmp-orders/', 'by' => 'oID', 'search' => $tmpOid]));
        } else {
            $oModel = $oQuery->one();
            if ($oModel->platform_id){
                $selected_platform_id = $oModel->platform_id;
            } else {
                $selected_platform_id = \common\classes\platform::firstId();
            }
        }

        $manager->set('platform_id', $selected_platform_id);

        Yii::$app->get('platform')->config($selected_platform_id)->constant_up();
        Yii::$app->get('platform')->config(\common\classes\platform::defaultId())->constant_up();
        /* @var \common\classes\TmpOrder $tmporder */
        $tmporder = $manager->getParentToInstanceWithId('\common\classes\TmpOrder', $tmpOid);
        if ($tmporder) {
            if ($currentDate) {
                $tmporder->info['date_purchased'] = date(\common\helpers\Date::DATABASE_DATETIME_FORMAT);
            }

            $orderId = $tmporder->createOrder();
            if (!$orderId) {
                $ret = ['error' => 1, 'msg' => TEXT_ERROR_TMP_ORDER_PROCESSED];
                \Yii::warning("tmporder is incorrect or processed $tmpOid", 'TLDEBUG');
            } else {
                $ret = ['error' => 0, 'url' => \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orderId])];
            }
        } else {
            $ret = ['error' => 1, 'msg' => TEXT_ERROR_INCORRECT_TMP_ORDER];
            \Yii::warning("tmporder is incorrect $tmpOid", 'TLDEBUG');
        }
        return $this->asJson($ret);
    }
    
    public function actionProcessOrder() {
        global $login_id;
        define('THEME_NAME', \common\classes\design::pageName(BACKEND_THEME_NAME));

        $this->selectedMenu = array('BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_TMP_ORDERS');

        if (Yii::$app->request->isPost) {
            $oID = Yii::$app->request->post('orders_id');
        } else {
            $oID = Yii::$app->request->get('orders_id');
        }

        $manager = \common\services\OrderManager::loadManager(new \common\classes\shopping_cart);
        $manager->cleanupTemporaryGuests();
        $manager->clearOrderInstance();
        $order = $manager->getOrderInstanceWithId('\common\classes\TmpOrder', $oID);
        $oQuery = $order->getARModel()->where([
          'orders_id' => $oID
          ]);
        if (false === \common\helpers\Acl::rule(['SUPERUSER'])) {
            global $login_id;
            $platforms = \common\models\AdminPlatforms::find()
                ->select('platform_id')
                ->where(['admin_id' => $login_id])->asArray()->column();
            $platforms[] = 0;
            $oQuery->andWhere(['platform_id' => $platforms]);
        }

        if (!$oQuery->exists()){
            $messageStack = \Yii::$container->get('message_stack');
            $messageStack->add_session(TEXT_ADMIN_ORDER_NOT_FOUND_ASSIGN_PLATFORMS, 'header', 'warning');

            return $this->redirect(\Yii::$app->urlManager->createUrl(['tmp-orders/', 'by' => 'oID', 'search' => $oID]));
        } else {
            $oModel = $oQuery->one();
            if ($oID != $oModel->orders_id) {
              $oID = $oModel->orders_id;
              $order = $manager->getOrderInstanceWithId('\common\classes\TmpOrder', $oID);
            }
            if ($oModel->platform_id){
                $selected_platform_id = $oModel->platform_id;
            } else {
                $selected_platform_id = \common\classes\platform::firstId();
            }
        }

        $manager->set('platform_id', $selected_platform_id);
        $manager->setModulesVisibility(['admin']);
        $manager->setRenderPath('\\backend\\design\\orders\\');

        Yii::$app->get('platform')->config($selected_platform_id)->constant_up();
        Yii::$app->get('platform')->config(\common\classes\platform::defaultId())->constant_up();

        $action = Yii::$app->request->get('action', '');
        $dropshippingcode = Yii::$app->request->get('dropshipping', '');
        if ($action == 'd-execute' && !empty($dropshippingcode)){
            $dropshipping = new \common\classes\dropshipping();
            $dropshipping->process($dropshippingcode, Yii::$app->request->getQueryParams());
            return $this->redirect(\Yii::$app->urlManager->createUrl(['tmp-orders/process-order', 'orders_id' => (int)$oID]));
        }

        $messageStack = \Yii::$container->get('message_stack');
        $messageStack->initFlash();

        $queryParams = Yii::$app->request->getQueryParams();
        unset($queryParams['action']);

        $access_levels_id = \common\models\Admin::findOne(['admin_id' => (int)$login_id])->access_levels_id;
        $pageName = \common\models\AdminTemplates::findOne([
            'access_levels_id' => $access_levels_id,
            'page' => 'backendOrder'
        ])->template;


        if (Yii::$app->request->isAjax){
            echo json_encode([
                'content' => $this->renderAjax('process-order', [
                    'queryParams' => $queryParams,
                    'manager' => $manager,
                    'order' => $order,
                    'pageName' => $pageName
                ]),
                'message' => $messageStack->asArray('header'),
            ]);
            exit();
        }

        $_session = Yii::$app->session;
        $filter = '';
        if ($_session->has('filter')) {
            $filter = $_session->get('filter');
        }

        $pagin_model = \common\models\Orders::find()->select('o.orders_id')->from("tmp_orders o ");
        if ($_session->has('search_condition')) {
            $pagin_model->andWhere($_session->get('search_condition'));
        }
        $_orders_products_allocate_joined = false;
        if( $filter){
            $pagin_model->leftJoin("tmp_orders_products op", "o.orders_id = op.orders_id")
                    ->leftJoin(TABLE_ORDERS_STATUS . " s", "o.orders_status=s.orders_status_id")
                    ->leftJoin(TABLE_ORDERS_STATUS_GROUPS . " sg", "s.orders_status_groups_id = sg.orders_status_groups_id");
            $pagin_model->leftJoin("tmp_orders_status_history osh", "o.orders_id = osh.orders_id");
        }
        if ($filter && \common\helpers\Acl::checkExtensionAllowed('Handlers', 'allowed')) {
            $pagin_model->leftJoin("handlers_products hp", "hp.products_id = op.products_id");
        }

        $order_next = $pagin_model->where("o.orders_id > '" . (int) $order->order_id . "'")
                ->andWhere($filter)->orderBy("orders_id ASC")->limit(1)->asArray()->one();
        $order_prev = $pagin_model->where("o.orders_id < '" . (int) $order->order_id . "'")
                ->andWhere($filter)->orderBy("orders_id DESC")->limit(1)->asArray()->one();

        $this->view->order_next = ( isset($order_next['orders_id']) ? $order_next['orders_id'] : 0);
        $this->view->order_prev = ( isset($order_prev['orders_id']) ? $order_prev['orders_id'] : 0);

        $order_language = \common\classes\language::get_code($order->info['language_id']);
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('tmp-orders/process-order?orders_id=' . $order->order_id), 'title' => TEXT_PROCESS_TMP_ORDER . '' . (!empty($order->info['order_number'])?'<span class="order-number">' . $order->info['order_number'] . '</span> ':'')  . $order->order_id . ' <div class="head-or-time">' . TEXT_DATE_AND_TIME . '' . $order->info['date_purchased'] . '</div><div class="order-platform">' . TABLE_HEADING_PLATFORM . ':' . \common\classes\platform::name($order->info['platform_id']) . '</div>');

        $_pl = ArrayHelper::map(platform::getList(false), 'id', 'id');
        if (!in_array($order->info['platform_id'], $_pl)) {
          $theme_platform_id = platform::defaultId();
        } else {
          $theme_platform_id = $order->info['platform_id'];
        }

        $addedPages = \common\models\ThemesSettings::find()->alias('ts')
            ->innerJoin(TABLE_THEMES . ' t', 't.theme_name=ts.theme_name')
            ->innerJoin(TABLE_PLATFORMS_TO_THEMES . ' p2t', 't.id=p2t.theme_id')
            ->select(['setting_name','setting_value', 'ts.id'])
            ->where([
                'p2t.platform_id' => $theme_platform_id,
                'setting_group' => 'added_page',
                'setting_name' => ['packingslip', 'invoice'],
            ])
            ->orderBy('setting_name')
            ->asArray()
            ->all();
        $addedPages = ArrayHelper::map($addedPages, 'id', 'setting_value', 'setting_name');
        $addedPages['invoice'] = $addedPages['invoice'] ?? [];
        $addedPages['invoice'] = []; // remove Ticket button
        $addedPages['packingslip'] = $addedPages['packingslip'] ?? [];

        $fraudView = false;
        if ( \common\helpers\Acl::checkExtensionAllowed('FraudAddress','allowed') ) {
            $fraudView = \common\extensions\FraudAddress\FraudAddress::fraudView($order);
        }

        global $navigation;
        if (sizeof($navigation->snapshot) > 0) {
            $addedPages['backUrl'] = Yii::$app->urlManager->createUrl(array_merge([$navigation->snapshot['page']], $navigation->snapshot['get']));
        } else {
            $addedPages['backUrl'] = Yii::$app->urlManager->createUrl(['tmp-orders']);
        }

        $details = $order->getDetails();

        return $this->render('update',[
            'queryParams' => $queryParams,
            'messsages' => $messageStack->messages,
            'manager' => $manager,
            'order' => $order,
            'customer_id' => (int) $order->customer["customer_id"],
            'qr_img_url' => HTTP_CATALOG_SERVER . DIR_WS_CATALOG . "account/order-qrcode?oID=" . (int) $order->order_id . "&cID=" . (int) $order->customer["customer_id"] . "&tracking=1",
            //'order_platform_id' => $order->info['platform_id'], //using undefined
            //'order_language' => $order_language, //using undefined
            'ref_id' => $order->getReferenceId(),
            'fraudView' => $fraudView,
            'dropshipping' => $dropshipping ?? null,
            'addedPages' => $addedPages,
            'pageName' => $pageName,
            'child_id' => $details['child_id'],
        ]);
    }


    
}
