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
use common\classes\modules\ModuleLabel;
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
use common\models\Orders;
use common\models\ShippingNpOrderParams;
use backend\models\forms\NovaPoshtaForm;
use common\services\CustomersService;
use yii\helpers\Html;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Yii;

use backend\models\EP\Messages;

/**
 * default controller to handle user requests.
 */
class OrdersController extends Sceleton {

    public $acl = ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS'];

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
        parent::__construct($id, $module);
    }

    public function actionIndex() {
        global $login_id, $navigation;

        if (is_object($navigation) && method_exists($navigation, 'set_snapshot')){
            $navigation->set_snapshot();
        }

        $this->selectedMenu = array('customers', 'orders');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders/index'), 'title' => HEADING_TITLE);
        if (\common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_NEW'])) {
            $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['editor/order-edit', 'back' => 'orders']) . '" class="btn btn-primary"><i class="icon-file-text"></i>' . TEXT_CREATE_NEW_OREDER . '</a>';
        }

        $this->view->headingTitle = HEADING_TITLE;
        $this->view->ordersTable = [];
        $this->view->ordersTable[] = array(
            'title' => '<input type="checkbox" class="uniform form-check-input">',
            'not_important' => 2
        );

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
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
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $markers = $ext::getMarkersList(true);
        }
        $this->view->markers = $markers;
        $this->view->filters->marker = (int)Yii::$app->request->get('marker', 0);

        $flags = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
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
            [
                'name' => TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME,
                'value' => 'batchName',
                'selected' => '',
            ],
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
            [
                'name' => TEXT_TRACKING_NUMBER,
                'value' => 'tracking_number',
                'selected' => '',
            ],
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

        foreach(\common\helpers\Admin::getAdminsWithWalkinOrders() as $admin){
            $this->view->filters->admin[$admin->admin_id] = $admin->admin_firstname .' '. $admin->admin_lastname;
        }

        $this->view->filters->status = \common\helpers\Order::getStatusList();
        $this->view->filters->status_selected = $GET['status'] ?? [];

        $this->view->filters->fcoupon = 'byId';
        $this->view->filters->fc_id = $GET['fc_id'] ?? [];
        if (!empty($GET['fc_code'])) {
          $this->view->filters->fc_code = htmlspecialchars($GET['fc_code']);
          $this->view->filters->fcoupon = 'like';
          $this->view->filters->fc_id = [];
        }
        $this->view->filters->fCoupons = \yii\helpers\ArrayHelper::map(Coupon::getOrderedList(), 'coupon_id', 'coupon_code');

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

        $oModelQuery->select(['payment_class', 'payment_method'])->where(['not', ['payment_class' => '']])->andWhere(['not', ['payment_method' => '']]);
        $payments = \yii\helpers\ArrayHelper::map($oModelQuery->groupBy('payment_class')->orderBy('payment_class, payment_method')->asArray()->all(), 'payment_class', 'payment_method');
        $payments = array_map('html_entity_decode', array_map('strip_tags', $payments));
        foreach ($payments as $class => $method) {
            if (tep_not_null($method)) {
                $this->view->filters->payments[$class] = trim($method) . ' (' . $class . ')';
            }
        }
        if (!empty($this->view->filters->payments)) {
            asort($this->view->filters->payments);
        }
        $this->view->filters->payments_selected = $GET['payments'] ?? [];

        $oModelQuery->select(['shipping_class', 'shipping_method'])->where(['not', ['shipping_class' => '']])->andWhere(['not', ['shipping_method' => '']]);
        $shippings = \yii\helpers\ArrayHelper::map($oModelQuery->groupBy('shipping_class')->orderBy('shipping_class, shipping_method')->asArray()->all(), 'shipping_class', 'shipping_method');
        $shippings = array_map('html_entity_decode', array_map('strip_tags', $shippings));
        $this->view->filters->shipping = [];
        foreach ($shippings as $class => $method) {
            list($class, ) = explode('_', $class);
            if (tep_not_null($method)) {
                $this->view->filters->shipping[$class] = trim($method) . ' (' . $class . ')';
            }
        }
        asort($this->view->filters->shipping);
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
        $admin->loadCustomersBaskets();
        $ids = $admin->getVirtualCartIDs();
        $this->view->filters->admin_choice = [];
        if ($ids) {
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
        $addedPages = ArrayHelper::map($addedPages, 'id', 'setting_value', 'setting_name');

        $tableHeading = '';
        $adminTable = \common\models\Admin::findOne(['admin_id' => (int)$login_id]);
        if ($adminTable) {
            $adminTemplates = \common\models\AdminTemplates::findOne([
                'access_levels_id' => $adminTable->access_levels_id,
                'page' => 'backendOrdersList'
            ]);
            if ($adminTemplates) {;
                defined('THEME_NAME') or define('THEME_NAME', \common\classes\design::pageName(BACKEND_THEME_NAME));
                $params = [];
                $params['backendOrdersList\BatchCheckbox'] = '<div class="checkbox-column"><input type="checkbox" class="uniform form-check-input"></div>';
                if ($adminTemplates->template) {
                    $tableHeading = \frontend\design\boxes\TableRow::headingRow($params, $adminTemplates->template);
                }
            }
        }

        return $this->render('index', [
                    'isMultiPlatform' => \common\classes\platform::isMulti(),
                    'platforms' => \common\classes\platform::getList(true, true),
                    'departments' => $departments,
                    'ordersStatuses' => $ordersStatuses,
                    'ordersStatusesOptions' => $ordersStatusesOptions,
                    'addedPages' => $addedPages,
                    'tableHeading' => $tableHeading,
        ]);
    }

    public function actionOrderHistory() {
        $this->layout = false;

        $orders_id = Yii::$app->request->get('orders_id');

        \common\helpers\Translation::init('admin/orders');

        $params = [];

        $history = [];

        $orders_history_query = tep_db_query("select * from " . TABLE_ORDERS_HISTORY . " o left join " . TABLE_ADMIN . " a on a.admin_id = o.admin_id where orders_id='" . (int) $orders_id . "' order by orders_history_id desc");
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
            $params['errors'] = \common\models\CustomersErrors::find()->linkingTo(\common\models\Orders::class)
                    ->where(['orders_id' => $orders_id])->orderBy('error_date desc')->all();

            //contacts
            $scart = tep_db_query("select * from " . TABLE_SCART . " s inner join " . TABLE_ORDERS . " o on o.orders_id = '" . (int) $orders_id . "' where o.basket_id = s.basket_id and s.customers_id = '" . (int) $cid . "'");
            if (tep_db_num_rows($scart)) {
                $_scart = tep_db_fetch_array($scart);
                $_scart['recovered'] = $_scart['recovered'] ? TEXT_BTN_YES : TEXT_BTN_NO;
                $_scart['contacted'] = $_scart['contacted'] ? TEXT_BTN_YES : TEXT_BTN_NO;
                $_scart['workedout'] = $_scart['workedout'] ? TEXT_BTN_YES : TEXT_BTN_NO;
                $params['scart'] = $_scart;
                //gv && cc
                $coupons = tep_db_query("select cet.coupon_id, cet.sent_firstname, cet.sent_lastname, cet.date_sent, c.coupon_code, c.coupon_amount, c.coupon_currency, c.coupon_type, c.coupon_active from " . TABLE_COUPON_EMAIL_TRACK . " cet left join " . TABLE_COUPONS . " c on c.coupon_id = cet.coupon_id inner join " . TABLE_ORDERS . " o on o.orders_id = '" . (int) $orders_id . "' where o.basket_id = cet.basket_id and cet.customer_id_sent = '" . (int) $cid . "'");
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
            $searchFields = ['o.order_number', 'o.customers_telephone', 'o.delivery_telephone', 'o.billing_telephone', 'o.customers_lastname', 'o.customers_firstname', 'o.customers_email_address', 'o.orders_id', 'op.products_model', 'op.products_name'];

            if ( is_numeric($keywords) ) {
                $searchFields[] = 'o.api_client_order_id';
            }

            /** @var \common\extensions\InvoiceNumberFormat\InvoiceNumberFormat $infExt */
            if ($infExt = \common\helpers\Acl::checkExtensionAllowed('InvoiceNumberFormat', 'allowed')){
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
        $orders_query_raw = \common\models\Orders::find()
            ->select("o.orders_id, s.orders_status_name, s.orders_status_groups_id ")
            //->addSelect("c.customers_gender")
            //->addSelect("ad.admin_firstname, ad.admin_lastname")
            //->addSelect('ot.text_inc_tax as order_total')
            ->from([TABLE_ORDERS_STATUS . " s", TABLE_ORDERS . " o"])
            //->leftJoin(TABLE_ORDERS_TOTAL . " ot", "(o.orders_id = ot.orders_id and ot.class = 'ot_total')")
            //->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "(op.orders_id = o.orders_id)")
            //->leftJoin(TABLE_ADMIN. " ad", "(ad.admin_id = o.admin_id)")
        ;
        if ((isset($_GET['in_stock']) && $_GET['in_stock'] != '')){
            $_orders_products_joined = true;
            $orders_query_raw->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "(op.orders_id = o.orders_id)");
            $orders_query_raw->addSelect("BIT_AND(" . (\common\helpers\Extensions::isAllowed('Inventory') ? "if(i.products_quantity is not null,if((i.products_quantity>=op.products_quantity),1,0),if((p.products_quantity>=op.products_quantity),1,0))" : "if((p.products_quantity>=op.products_quantity),1,0)") . ") as in_stock");
            $orders_query_raw->leftJoin(TABLE_PRODUCTS . " p", "(p.products_id = op.products_id)");
            if (\common\helpers\Extensions::isAllowed('Inventory')){
                $orders_query_raw->leftJoin(TABLE_INVENTORY . " i", "(i.prid = op.products_id and i.products_id = op.uprid)");
            }
        }
        if (\common\helpers\Extensions::isAllowed('Handlers')) {
            if ( !$_orders_products_joined ) {
                $_orders_products_joined = true;
                $orders_query_raw->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "(op.orders_id = o.orders_id)");
            }
            $orders_query_raw->leftJoin("handlers_products hp", "hp.products_id = op.products_id");
        }

        $_orders_products_allocate_joined = false;
        if (!(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_WAREHOUSES']))) {
            $orders_query_raw->leftJoin("orders_products_allocate opa", "opa.orders_id = o.orders_id");
            $_orders_products_allocate_joined = true;
        }
        if (!(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_SUPPLIERS']))) {
            if ( !$_orders_products_allocate_joined ) {
                $orders_query_raw->leftJoin("orders_products_allocate opa", "opa.orders_id = o.orders_id");
                $_orders_products_allocate_joined = true;
            }
        }

        //$orders_query_raw->leftJoin(TABLE_CUSTOMERS . " c", "(o.customers_id = c.customers_id)");
        $orders_query_raw->where("o.orders_status = s.orders_status_id " . $search_condition . " and s.language_id = '" . (int)$languages_id . "' and s.orders_status_groups_id IN('" . implode("','", array_keys($statusGroupData)) . "') ");
        if ( strpos($search_condition,' op.')!==false && !$_orders_products_joined ){
            $orders_query_raw->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "(op.orders_id = o.orders_id)");
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
            $orders_query_raw->andWhere([
                'OR',
                ['in', 'hp.handlers_id', $ext::getHandlersQuery((int) $access_levels_id)],
                ['hp.handlers_id' => NULL]
            ]);
        }

        if (!(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_WAREHOUSES']))) {
            $warehousesArray = [];
            foreach (\common\models\AdminWarehouses::find()->where(['admin_id' => $login_id])->asArray()->all() as $warehouse) {
                $warehousesArray[] = $warehouse['warehouse_id'];
            }
            unset($warehouse);
            $orders_query_raw->andWhere(['in', 'opa.warehouse_id', $warehousesArray]);
            unset($warehousesArray);
        }
        if (!(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_SUPPLIERS']))) {
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
                    $orders_query_raw->andWhere([ 'or', ["o.orders_id" => (int) $search], ['o.order_number' => $search]]);
                    break;
                case 'model': default:
                    if ( !$_orders_products_joined ) {
                       $_orders_products_joined = true;
                       $orders_query_raw->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "(op.orders_id = o.orders_id)");
                    }
                    $orders_query_raw->andWhere([$operator, "op.products_model", $search]);
                    break;
                case 'name':
                    if ( !$_orders_products_joined ) {
                        $_orders_products_joined = true;
                        $orders_query_raw->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "(op.orders_id = o.orders_id)");
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
                       $orders_query_raw->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "(op.orders_id = o.orders_id)");
                    }
                    $orders_query_raw->leftJoin(TABLE_ORDERS_STATUS_HISTORY." osh", "o.orders_id = osh.orders_id");
                    $orders_query_raw->andFilterWhere([
                        'or',
                        ['o.orders_id' => $search],
                        [
                        ($operator=='LIKE'?'OR':'AND'),
                        [$operator, 'o.order_number',$search],
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
            $orders_query_raw->andWhere(['in', 'o.payment_class', $output['payments']]);
        }

        if (isset($output['shipping']) && !empty($output['shipping'])) {
            $orders_query_raw->andWhere(['in', 'SUBSTRING_INDEX(o.shipping_class, "_", 1)', $output['shipping']]);
        }

        if (isset($output['fc_id']) && is_array($output['fc_id']) && count($output['fc_id'])) {
            $orders_query_raw->innerJoin(TABLE_COUPON_REDEEM_TRACK . " crt", "o.orders_id=crt.order_id and crt.coupon_id in (" .implode(",", $output['fc_id']). ") ");
        }

        if (isset($output['fc_code']) && !empty($output['fc_code'])) {
            $orders_query_raw->innerJoin(TABLE_ORDERS_TOTAL . " otfc", "o.orders_id=otfc.orders_id and otfc.class='ot_coupon' and otfc.title like '%" . tep_db_input($output['fc_code']) . "%'");
        }

        if (isset($output['flag']) && $output['flag'] > 0) {
            $orders_query_raw->innerJoin('orders_markers' . " omf", "o.orders_id=omf.orders_id and omf.flags='" . (int)$output['flag'] . "'");
        }

        if (isset($output['marker']) && $output['marker'] > 0) {
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
            $orders_query_raw->innerJoin(TABLE_ORDERS_TOTAL . " otfp", "o.orders_id=otfp.orders_id and otfp.class='" . tep_db_input($output['fp_class']). "'"
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
               $orders_query_raw->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "(op.orders_id = o.orders_id)");
            }
            $orders_query_raw->andWhere('((op.products_quantity - op.qty_cnld) > op.qty_rcvd)');
        }

        $orders_query_raw->groupBy("o.orders_id");
        if ((isset($_GET['in_stock']) && $_GET['in_stock'] != '')){
            $orders_query_raw->having("in_stock " . ($_GET['in_stock'] > 0 ? " > 0" : " < 1"));
        }

        $orders_query_raw->orderBy($orderBy);

        if ($ext = \common\helpers\Acl::checkExtension('Neighbour', 'allowed')){
            if ($ext::allowed()){
                $ext_query = $ext::getQuery($orders_query_raw);
            }
        }

        foreach (\common\helpers\Hooks::getList('orders/orderlist') as $filename) {
            include($filename);
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

            $completePageData = \common\models\Orders::find()
                ->select('o.*')
                ->addSelect("c.customers_gender")
                ->addSelect("ad.admin_firstname, ad.admin_lastname")
                ->addSelect('ot.text_inc_tax as order_total')
                ->from(TABLE_ORDERS . " o")
                ->leftJoin(TABLE_ORDERS_TOTAL . " ot", "(o.orders_id = ot.orders_id and ot.class = 'ot_total')")
                ->leftJoin(TABLE_ADMIN. " ad", "(ad.admin_id = o.admin_id)")
                ->leftJoin(TABLE_CUSTOMERS . " c", "(o.customers_id = c.customers_id)")
                ->where(['IN', 'o.orders_id', $_pageOrderIds])
                ->asArray()->all();

            foreach($completePageData as $__order_data){
                $__idx = $_pageOrderIdToIdx[$__order_data['orders_id']];
                $ordersAll[$__idx] = array_merge($__order_data, $ordersAll[$__idx]);
            }

            if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')) {
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
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $markers = $ext::getMarkers();
        }

        $flags = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $flags = $ext::getFlags();
        }

        $pageName = false;
        $adminTable = \common\models\Admin::findOne(['admin_id' => (int)$login_id]);
        if ($adminTable) {
            $adminTemplates = \common\models\AdminTemplates::findOne([
                'access_levels_id' => $adminTable->access_levels_id,
                'page' => 'backendOrdersList'
            ]);
            if ($adminTemplates) {
                $pageName = $adminTemplates->template;
            }
        };
        defined('THEME_NAME') or define('THEME_NAME', \common\classes\design::pageName(BACKEND_THEME_NAME));

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

                $tableOrderRow = [];
                $purchasedDate = \common\helpers\Date::datetime_short($orders['date_purchased']);
                $todayDate = \common\helpers\Date::date_short(date('Y-m-d'));
                $purchasedDate = str_replace($todayDate, TEXT_TODAY, $purchasedDate);
                $cusColumn = '';
                if ($orders['customers_id']){
                    $cusColumn = '<div class="ord-name ord-gender ord-gender-' . $orders['customers_gender'] . ' click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '">'.
                    (\common\models\Customers::findOne($orders['customers_id'])?
                    '<a href="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $orders['customers_id']]) . '" title="' . strip_tags($orders['customers_name']) . '">' . Html::encode(self::cropStr($orders['customers_name'], 22)) . '</a>':Html::encode(self::cropStr($orders['customers_name'], 22))) .
                    '</div><a href="mailto:' . $orders['customers_email_address'] . '" class="ord-name-email" title="' . strip_tags($customers_email_address) . '">' . self::cropStr($customers_email_address, 22) . '</a><div class="ord-location" style="margin-top: 5px;">' . Html::encode($orders['customers_postcode']) . '<div class="ord-total-info ord-location-info"><div class="ord-box-img"></div><b>' . Html::encode($orders['customers_name']) . '</b>' . Html::encode($orders['customers_street_address']) . '<br>' . Html::encode($orders['customers_city'] . ', ' . $orders['customers_state']) . '&nbsp;' . Html::encode($orders['customers_postcode']) . '<br>' . $orders['customers_country'] . '</div></div>';

                    $tableOrderRow['backendOrdersList\CustomerGender'] = $orders['customers_gender'];
                    $tableOrderRow['backendOrdersList\CustomerName'] = (\common\models\Customers::findOne($orders['customers_id'])?
                        '<a href="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $orders['customers_id']]) . '" title="' . strip_tags($orders['customers_name']) . '">' . Html::encode(self::cropStr($orders['customers_name'], 22)) . '</a>':Html::encode(self::cropStr($orders['customers_name'], 22)));

                    $tableOrderRow['backendOrdersList\CustomerEmail'] = '<a href="mailto:' . $orders['customers_email_address'] . '" class="ord-name-email" title="' . strip_tags($customers_email_address) . '">' . self::cropStr($customers_email_address, 22) . '</a>';
                    $tableOrderRow['backendOrdersList\OrderLocation'] = '<div class="ord-total-info ord-location-info"><div class="ord-box-img"></div><b>' . Html::encode($orders['customers_name']) . '</b>' . Html::encode($orders['customers_street_address']) . '<br>' . Html::encode($orders['customers_city'] . ', ' . $orders['customers_state']) . '&nbsp;' . Html::encode($orders['customers_postcode']) . '<br>' . $orders['customers_country'] . '</div></div>';

                } elseif ($orders['admin_id']) {
                    $customer_delivery_name = '('.$orders['delivery_name'].')';
                    $customer_delivery_info = '<div class="ord-location" style="margin-top: 5px;">' . $orders['delivery_postcode'] . '<div class="ord-total-info ord-location-info"><div class="ord-box-img"></div><b>' . Html::encode($orders['delivery_name']) . '</b>' . Html::encode($orders['delivery_street_address']) . '<br>' . Html::encode($orders['delivery_city'] . ', ' . $orders['delivery_state']) . '&nbsp;' . Html::encode($orders['delivery_postcode']) . '<br>' . $orders['delivery_country'] . '</div></div>';
                    $cusColumn = '<div class="ord-name click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '">' . (defined('TEXT_WALKIN_ORDER')? TEXT_WALKIN_ORDER: '') . $orders['admin_firstname'] . ' ' . $orders['admin_lastname']. ' '.$customer_delivery_name.'</div>'.$customer_delivery_info;

                    $tableOrderRow['backendOrdersList\CustomerName'] = $customer_delivery_name;
                    $tableOrderRow['backendOrdersList\OrderLocation'] = $customer_delivery_info;
                    $tableOrderRow['backendOrdersList\WalkinOrder'] = (defined('TEXT_WALKIN_ORDER')? TEXT_WALKIN_ORDER: '') . $orders['admin_firstname'] . ' ' . $orders['admin_lastname'];
                }

                $orderRow = [];
                if ( $orders['hold_on_date'] ) {
                    $orderRow['DT_RowClass'] = ArrayHelper::getValue($orderRow, 'DT_RowClass') . ' holdOnOrder';
                    $purchasedDate .= '<div class="holdOrderInfo">'.sprintf(LIST_ORDER_HOLD_ON, \common\helpers\Date::date_short($orders['hold_on_date'])).'</div>';
                    $tableOrderRow['DT_RowClass'] = $orderRow['DT_RowClass'];
                }

                if ( isset($orders['isFraud']) && $orders['isFraud'] ) {
                    $orderRow['DT_RowClass'] = ArrayHelper::getValue($orderRow, 'DT_RowClass') . ' fraudOrder';
                    $tableOrderRow['DT_RowClass'] = $orderRow['DT_RowClass'];
                }

                $batchCheckbox = '<input type="checkbox" class="uniform form-check-input">' . '<input class="cell_identify" type="hidden" value="' . $orders['orders_id'] . '">';
                $orderRow[] = $batchCheckbox;
                $tableOrderRow['backendOrdersList\BatchCheckbox'] = $batchCheckbox;

                $coloredRow = '';
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')) {
                    $orderMarkers = $orders['orderMarkers'];
                    if (isset($orderMarkers['markers']) && isset($markers[$orderMarkers['markers']])){
                        $coloredRow = $markers[$orderMarkers['markers']];
                    }
                    $paint = '<div class="fa-paint-brush" onclick="sendOrderMarker(' . (int)$orders['orders_id'] . ', ' . (int)($orderMarkers['markers'] ?? 0) . ')"></div>';
                    if (isset($orderMarkers['flags']) && isset($flags[$orderMarkers['flags']])){
                        $orderMarkers = '<div class="fa-flag" style="color: ' . $flags[$orderMarkers['flags']] . ';" onclick="sendOrderFlag(' . (int)$orders['orders_id'] . ', ' . (int)$orderMarkers['flags'] . ')"></div>' . $paint;
                    } else {
                        $orderMarkers = '<div class="fa-flag-o" onclick="sendOrderFlag(' . (int)$orders['orders_id'] . ')"></div>' . $paint;
                    }
                    $orderRow[] = $orderMarkers;
                    $tableOrderRow['backendOrdersList\OrderMarkersCell'] = '<input type="checkbox" class="uniform form-check-input">' . '<input class="cell_identify" type="hidden" value="' . $orders['orders_id'] . '">';
                }

                $customerColumn = $cusColumn . '<input class="row_colored" type="hidden" value="' . $coloredRow . '">';
                $orderRow[] = $customerColumn;
                $tableOrderRow['backendOrdersList\CustomerColumnCell'] = $customerColumn;

                $orderTotals = '<div class="ord-total click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '">' . $orders['order_total'] . '<div class="ord-total-info"><div class="ord-box-img"></div>' . $orderTotals . '</div></div>';
                $orderRow[] = $orderTotals;
                $tableOrderRow['backendOrdersList\OrderTotalsCell'] = $orderTotals;

                $orderDescription = '<div class="ord-desc-tab click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '"><a href="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '" class="order-inf"><span class="ord-id">' . TEXT_ORDER_NUM . (!empty($orders['order_number'])?$orders['order_number']:$orders['orders_id']) . '</span> ' . (!empty($orders['invoice_number'])?' <span class="inv-id"><span class="title">' . TEXT_INVOICE . '</span>' . $orders['invoice_number'] . '</span> ':'')   . $departmentInfo . (tep_not_null($orders['payment_method']) ? (SHOW_PRODUCTS_ON_ORDER_LIST === 'False' ? '<br>' : ' ') . TEXT_VIA . ' ' . strip_tags($orders['payment_method']) : '') . (tep_not_null($orders['shipping_method']) ? ' ' . TEXT_DELIVERED_BY . ' ' . strip_tags($orders['shipping_method']) : '') . '</a>' . (SHOW_PRODUCTS_ON_ORDER_LIST !== 'False' ? $p_list : '') . '</div>';
                $orderRow[] = $orderDescription;
                $tableOrderRow['backendOrdersList\OrderDescriptionCell'] = $orderDescription;

                $orderPurchase = '<div class="ord-date-purch click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '">' . $purchasedDate . $deliveryInfo;
                $orderRow[] = $orderPurchase;
                $tableOrderRow['backendOrdersList\OrderPurchaseCell'] = $orderPurchase;
                $tableOrderRow['backendOrdersList\OrderPurchase'] = $purchasedDate;

                $orderStatus = '<div class="ord-status click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders['orders_id']]) . '"><span><i style="background: ' . $orders['orders_status_groups_color'] . ';"></i>' . $orders['orders_status_groups_name'] . ',</span> <div>' . $orders['orders_status_name'] . '</div></div>';
                $orderRow[] = $orderStatus;
                $tableOrderRow['backendOrdersList\OrderStatusCell'] = $orderStatus;

                if ($ext = \common\helpers\Acl::checkExtension('Neighbour', 'allowed')){
                    if ($ext::allowed()){
                        $neighbour = ($orders['to_neighbour']?'<div class=" ord-date-purch-delivery ord-date-purch-delivery-check">':'');
                        $orderRow[] = $neighbour;
                        $tableOrderRow['backendOrdersList\NeighbourCell'] = $neighbour;
                    }
                }

                $tableOrderRow['backendOrdersList\OrderId'] = $orders['orders_id'];
                $tableOrderRow['backendOrdersList\OrderProducts'] = $p_list;
                $tableOrderRow['backendOrdersList\Platform'] = \common\classes\platform::name($orders['platform_id']);
                $tableOrderRow['backendOrdersList\PaymentMethod'] = $orders['payment_method'] ?? '';
                $tableOrderRow['backendOrdersList\ShippingMethod'] = $orders['shipping_method'] ?? '';

                if ($pageName) {
                    $responseList[] = \frontend\design\boxes\TableRow::row($tableOrderRow, $pageName);
                } else {
                    $responseList[] = $orderRow;
                }
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

        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');
/*
        $orders_query = tep_db_query("select o.customers_id, o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.platform_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.language_id, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.orders_id = '" . (int) $orders_id . "' and s.orders_status_id=o.orders_status and s.language_id='" . (int)$languages_id . "'");

        $orders = tep_db_fetch_array($orders_query);
*/
        $orders = Orders::find()->alias('o')/*->select('o.customers_id, o.orders_status, o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.platform_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.language_id, o.currency_value')*/
            ->andWhere(['orders_id' => (int) $orders_id ])->asArray()->one();
        if (empty($orders)) {
            die("Please select order.");
        }

        $_pl = ArrayHelper::map(platform::getList(false), 'id', 'id');
        if (!in_array($orders['platform_id'], $_pl)) {
          $orders['platform_id'] = platform::defaultId();
        }

        $addedPages = \common\models\ThemesSettings::find()->alias('ts')
            ->innerJoin(TABLE_THEMES . ' t', 't.theme_name=ts.theme_name')
            ->innerJoin(TABLE_PLATFORMS_TO_THEMES . ' p2t', 't.id=p2t.theme_id')
            ->select(['setting_name','setting_value', 'ts.id'])
            ->where([
                'p2t.platform_id' => $orders['platform_id'],
                'setting_group' => 'added_page',
                'setting_name' => ['packingslip', 'invoice'],
            ])
            ->orderBy('setting_name')
            ->asArray()
            ->all();
        $addedPages = ArrayHelper::map($addedPages, 'id', 'setting_value', 'setting_name');
        $addedPages['invoice'] = $addedPages['invoice'] ?? [];
        $addedPages['packingslip'] = $addedPages['packingslip'] ?? [];

        $canAnonimize = $tmp = false;
        if (defined('GDPR_CUSTOMER_DELETE_OPEN_ORDER_STATUSES') && !empty(trim(GDPR_CUSTOMER_DELETE_OPEN_ORDER_STATUSES))) {
          $tmp = array_map('intval', explode(',', GDPR_CUSTOMER_DELETE_OPEN_ORDER_STATUSES));
        }
        if ($orders['customers_id'] != \common\helpers\Customer::findCreateAnonymousCustomer()
            &&  (!is_array($tmp ) || !in_array($orders['orders_status'], $tmp)) ) {
          $canAnonimize = true;
        }

        $oInfo = new \objectInfo($orders);
        return $this->render('actions', ['oInfo' => $oInfo, 'addedPages' => $addedPages, 'canAnonimize' => $canAnonimize]);
    }

    public function actionOrderReassign() {
        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $orders_query = tep_db_query("select o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.orders_id = '" . (int) $orders_id . "'");
        $orders = tep_db_fetch_array($orders_query);

        if (!is_array($orders)) {
            die("Wrong order data.");
        }

        $oInfo = new \objectInfo($orders);
        return $this->render('reassign', ['oInfo' => $oInfo]);

    }

    public function actionAnonimizeOrder() {
      $orders_id = Yii::$app->request->get('orders_id');
      $this->layout = false;
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      \common\helpers\Order::anonimizeOrder($orders_id);
      return ['status' => 'ok'];
    }

    public function actionConfirmedOrderReassign() {
        $customers_id = Yii::$app->request->post('customers_id');
        $orders_id = Yii::$app->request->post('orders_id');

        $customers_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . (int) $customers_id . "'");
        $customers = tep_db_fetch_array($customers_query);
        if (is_array($customers) && $orders_id > 0) {
            tep_db_query("update " . TABLE_ORDERS . " set customers_id = '" . (int) $customers_id . "', customers_name = '" . tep_db_input($customers['customers_firstname'] . ' ' . $customers['customers_lastname']) . "', customers_firstname = '" . tep_db_input($customers['customers_firstname']) . "', customers_lastname = '" . tep_db_input($customers['customers_lastname']) . "', customers_email_address = '" . tep_db_input($customers['customers_email_address']) . "' where orders_id = '" . (int) $orders_id . "';");
        }
    }

    public function actionProcessOrder()
    {
        global $login_id;
        defined('THEME_NAME') or define('THEME_NAME', \common\classes\design::pageName(BACKEND_THEME_NAME));

        \common\helpers\Translation::init('admin/orders');

        $this->selectedMenu = array('customers', 'orders');

        if (Yii::$app->request->isPost) {
            $oID = Yii::$app->request->post('orders_id');
        } else {
            $oID = Yii::$app->request->get('orders_id');
        }

        $manager = \common\services\OrderManager::loadManager(new \common\classes\shopping_cart);
        $manager->cleanupTemporaryGuests();
        $manager->clearOrderInstance();
        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $oID);
        $oQuery = $order->getARModel()->where(['or',
          ['orders_id' => $oID],
          ['order_number' => $oID],
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

            return $this->redirect(\Yii::$app->urlManager->createUrl(['orders/', 'by' => 'oID', 'search' => $oID]));
        } else {
            $oModel = $oQuery->one();
            if ($oID != $oModel->orders_id) {
              $oID = $oModel->orders_id;
              $order = $manager->getOrderInstanceWithId('\common\classes\Order', $oID);
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
            return $this->redirect(\Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => (int)$oID]));
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

        $pagin_model = \common\models\Orders::find()->select('o.orders_id')->from(TABLE_ORDERS . " o USE INDEX (PRIMARY) ");
        if ($_session->has('search_condition')) {
            $pagin_model->andWhere($_session->get('search_condition'));
        }
        $_orders_products_allocate_joined = false;
        if( $filter){
            $pagin_model->leftJoin(TABLE_ORDERS_PRODUCTS . " op", "o.orders_id = op.orders_id")
                    ->leftJoin(TABLE_ORDERS_STATUS . " s", "o.orders_status=s.orders_status_id")
                    ->leftJoin(TABLE_ORDERS_STATUS_GROUPS . " sg", "s.orders_status_groups_id = sg.orders_status_groups_id");
            if (    !(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_WAREHOUSES']))
                    ||
                    !(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_SUPPLIERS']))
                ) {
                $pagin_model->leftJoin("orders_products_allocate opa", "opa.orders_id = o.orders_id");
                $_orders_products_allocate_joined = true;
            }
            $pagin_model->leftJoin(TABLE_ORDERS_STATUS_HISTORY." osh", "o.orders_id = osh.orders_id");
        }
        if ($filter && \common\helpers\Extensions::isAllowed('Handlers')) {
            $pagin_model->leftJoin("handlers_products hp", "hp.products_id = op.products_id");
        }
        if ($filter && strpos($pagin_model->andWhere($filter)->createCommand()->getRawSql(),'wpb')!==false){
            if ( !$_orders_products_allocate_joined ) {
                $pagin_model->leftJoin("orders_products_allocate opa", "opa.orders_id = o.orders_id");
                $_orders_products_allocate_joined = true;
            }
            $pagin_model->leftJoin("warehouses_products_batches wpb", "wpb.batch_id = opa.batch_id");
        }

        foreach (\common\helpers\Hooks::getList('orders/process-order/before-next-prev-query') as $filename) {
            include($filename);
        }

        $order_next = $pagin_model->where("o.orders_id > '" . (int) $order->order_id . "'")
                ->andWhere($filter)->orderBy("orders_id ASC")->limit(1)->asArray()->one();
        $order_prev = $pagin_model->where("o.orders_id < '" . (int) $order->order_id . "'")
                ->andWhere($filter)->orderBy("orders_id DESC")->limit(1)->asArray()->one();
        
        $this->view->order_next = ( isset($order_next['orders_id']) ? $order_next['orders_id'] : 0);
        $this->view->order_prev = ( isset($order_prev['orders_id']) ? $order_prev['orders_id'] : 0);

        $order_language = \common\classes\language::get_code($order->info['language_id']);
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders/process-order?orders_id=' . $order->order_id), 'title' => TEXT_PROCESS_ORDER . ' #' . (!empty($order->info['order_number'])?'<span class="order-number">' . $order->info['order_number'] . '</span> ':'')  . $order->order_id . ' <div class="head-or-time">' . TEXT_DATE_AND_TIME . '' . $order->info['date_purchased'] . '</div><div class="order-platform">' . TABLE_HEADING_PLATFORM . ':' . \common\classes\platform::name($order->info['platform_id']) . '</div>');

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
        // remove ticket button. define('ENABLE_ORDER_TICKET', 1) to enable
        if (is_array($addedPages['invoice']) && !defined('ENABLE_ORDER_TICKET') && ($key = array_search('ticket', $addedPages['invoice'])) !== false) {
            unset($addedPages['invoice'][$key]);
        }
        $addedPages['packingslip'] = $addedPages['packingslip'] ?? [];

        $fraudView = false;
        if ( \common\helpers\Acl::checkExtensionAllowed('FraudAddress','allowed') ) {
            $fraudView = \common\extensions\FraudAddress\FraudAddress::fraudView($order);
        }

        global $navigation;
        if (sizeof($navigation->snapshot) > 0) {
            $addedPages['backUrl'] = Yii::$app->urlManager->createUrl(array_merge([$navigation->snapshot['page']], $navigation->snapshot['get']));
        } else {
            $addedPages['backUrl'] = Yii::$app->urlManager->createUrl(['orders']);
        }

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
        ]);
    }

    public function actionOrdersubmit() {

        global $login_id;
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/orders');

        $admin_id = $login_id;

        $this->layout = false;

/** @var \common\classes\Currencies $currencies*/
        $currencies = Yii::$container->get('currencies');

        $orders_statuses = array();
        $orders_status_array = array();
        $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "'");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
            $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                'text' => $orders_status['orders_status_name']);
            $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
        }

        $oID = Yii::$app->request->post('orders_id');

        $manager = \common\services\OrderManager::loadManager(new \common\classes\shopping_cart);
        $manager->setModulesVisibility(['admin']);
        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $oID);
        /**
         * @var \common\classes\Order $order
         */

        if (!$order->getDetails()){
            die("Wrong order data.");
        }

        $check_status = $order->getDetails();
        $order_updated = false;

        /**
        * @var $platform_config platform_config
        */
        $platform_config = Yii::$app->get('platform')->config($check_status['platform_id']);
        Yii::$app->get('platform')->config($check_status['platform_id'])->constant_up();

        $status = tep_db_prepare_input($_POST['status']);
        $comments = tep_db_prepare_input($_POST['comments']);
        $update_paid_amount_flag = (int)Yii::$app->request->post('use_update_amount', 0);
        $update_paid_amount = (float) Yii::$app->request->post('update_paid_amount', 0);
        $t_status = (int) Yii::$app->request->post('t_status', \common\helpers\OrderPayment::OPYS_PENDING);
        $t_number =  Yii::$app->request->post('transaction_id', '');
        if ($update_paid_amount_flag && is_numeric($update_paid_amount) && $update_paid_amount) {
            $order->info['comments'] = '';
            $totals = \yii\helpers\ArrayHelper::map($order->totals, 'code', 'value_inc_tax');
            if (!empty($totals['ot_due']) && !empty($totals['ot_paid'])) {

                $value = (float) $update_paid_amount * $currencies->get_market_price_rate($order->info['currency'], DEFAULT_CURRENCY);
                $paid_prefix = \Yii::$app->request->post('paid_prefix', '+');
                $value = ($paid_prefix == '-' ? -$value: $value);

                ////
                if (!empty($t_number)) {
                    $payment = $manager->getPaymentCollection($order->info['payment_class'])->get($order->info['payment_class'], true);
                    $tm = $manager->getTransactionManager(($payment?$payment:null));
                    //offline methods are added (always as payment->code != $order->info['payment_class'] (offline != offline_NN)
                    $res = $tm->updatePaymentTransaction($t_number,
                      [
                        'fulljson' => '',
                        'status_code' => $t_status,
                        'status' =>  defined('TEXT_STATUS_OPYS_SUCCESSFUL')?TEXT_STATUS_OPYS_SUCCESSFUL:'',
                        'amount' => (float) $value,
                        //'comments'  => $value . ' ' . $order->getOrderNumber(),
                        'date'  => date('Y-m-d H:i:s' /*, strtotime($res->update_time)*/),
                        'payment_class' => $order->info['payment_class'],
                        'payment_method' => $order->info['payment_method'],
                        'parent_transaction_id' => 0,
                        'orders_id' => 0
                      ]);
                }
                ////

                $manager->loadCart(new \common\classes\shopping_cart);
                $cart = $manager->getCart();

                $comment = $comments . " " . TEXT_PAID_AMOUNT . " " . $paid_prefix . $currencies->format($update_paid_amount, true, $order->info['currency'], 1);
                $value += $totals['ot_paid'];
                $cart->setTotalPaid($value, '+', $comment);
                $manager->getTotalCollection()->process(['ot_paid', 'ot_due']);
                $order->isPaidUpdated = true;


                if ($order->maintainSplittering()){
                    $manager->getOrderSplitter()->makeSplinters($order->order_id);
                }
                $order->save_details();
                $order_updated = true;
            }
        }

        $order_stock_updated_flag = false;
        $update_order_stock = Yii::$app->request->post('update_order_stock', 0);
        if ($update_order_stock && !\common\helpers\Order::is_stock_updated((int) $oID)) {
            $_get_order_products_r = tep_db_query(
                    "select IF(LENGTH(uprid)>0,uprid,products_id) AS uprid, products_quantity " .
                    "from " . TABLE_ORDERS_PRODUCTS . " " .
                    "where orders_id='" . (int) $oID . "'"
            );
            while ($ordered_uprid = tep_db_fetch_array($_get_order_products_r)) {
                \common\helpers\Product::update_stock($ordered_uprid['uprid'], 0, $ordered_uprid['products_quantity']);
            }
            tep_db_query("UPDATE " . TABLE_ORDERS . " SET stock_updated=1 WHERE orders_id='" . (int) $oID . "'");

            $order_stock_updated_flag = true;
        }

// BOF: WebMakers.com Added: Downloads Controller
// always update date and time on order_status
// original        if ( ($check_status['orders_status'] != $status) || tep_not_null($comments)) {
        $order_comment = Yii::$app->request->post('order_comment');

        if (!empty($order_comment)){
            $visible = Yii::$app->request->post('visible_to', '');
            $visibility = [];
            if ($visible){
                $vis = explode("_", $visible);
                if ($vis){
                    $visibility = [mb_substr($vis[0], 0, 1) => $vis[1]];
                }
            }
            $oComment = \common\models\OrdersComments::create($order->order_id, $login_id, $order_comment, 0, $visibility);
            $order_updated = true;
        }

        $invoice_comment = Yii::$app->request->post('invoice_comment');

        $iComment = \common\models\OrdersComments::findInvoiceComment($order->order_id);
        if (!empty($invoice_comment) || $iComment){
            if ($iComment){
                $iComment->setAttribute('comments', $invoice_comment);
            } else {
                $iComment = \common\models\OrdersComments::create($order->order_id, $login_id, $invoice_comment, 1);
            }
            $order_updated = true;
        }
        $messages = [];
        $smscomments = trim(isset($_POST['smscomments']) ? $_POST['smscomments'] : '');
        if (($check_status['orders_status'] != $status) || $comments != '' || $smscomments != '' || ($status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE)) {
            /*tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . tep_db_input($status) . "', last_modified = now() where orders_id = '" . (int) $oID . "'");
            $check_status_query2 = tep_db_query("select customers_name, customers_email_address, orders_status, date_purchased from " . TABLE_ORDERS . " where orders_id = '" . (int) $oID . "'");
            $check_status2 = tep_db_fetch_array($check_status_query2);
            if ($check_status2['orders_status'] == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) {*/
            if ($status == DOWNLOADS_ORDERS_STATUS_UPDATED_VALUE) {
                tep_db_query("update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_maxdays = '" . tep_db_input(\common\helpers\Configuration::get_configuration_key_value('DOWNLOAD_MAX_DAYS')) . "', download_count = '" . tep_db_input(\common\helpers\Configuration::get_configuration_key_value('DOWNLOAD_MAX_COUNT')) . "' where orders_id = '" . (int) $oID . "'");
            }

// EOF: WebMakers.com Added: Downloads Controller

            $email_headers = '';

            $customer_notified = '0';
            if (isset($_POST['notify']) && ($_POST['notify'] == '1')) {
                $notify_comments = '';
                if (isset($_POST['notify_comments']) && ($_POST['notify_comments'] == '1') && $comments) {
                  $EMAIL_TEXT_COMMENTS_UPDATE = Translation::getTranslationValue('EMAIL_TEXT_COMMENTS_UPDATE', 'admin/main', $order->info['language_id']);
                  $notify_comments = trim(sprintf($EMAIL_TEXT_COMMENTS_UPDATE, $comments)) . "\n\n";
                  //  $notify_comments = trim(sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments)) . "\n\n";
                }

                $order->info['order_status'] = $status;
                $customer_notified = $order->send_status_notify($notify_comments, []);

                if ($smsService = \common\helpers\Acl::checkExtensionAllowed('SmsService', 'allowed')) {
                    $orderStatusRecord = \common\models\OrdersStatus::find()
                        ->where([
                            'orders_status_id' => $status,
                            'language_id' => $languages_id
                        ])->asArray(true)->one();
                    if (is_array($orderStatusRecord) AND isset($orderStatusRecord['orders_status_template_sms'])) {
                        $parameterArray = array();
                        $smsMessage = \common\helpers\Mail::get_sms_template_parsed($orderStatusRecord['orders_status_template_sms'], $parameterArray);
                        $customerPhone = '';
                        $countryRecord = \common\models\Countries::findOne($order->customer['country_id']);
                        if ($countryRecord instanceof \common\models\Countries) {
                            $customerPhone = $countryRecord->checkPhone($order->customer['telephone']);
                        }
                        unset($countryRecord);
                        if (($customerPhone != '') AND ($smsMessage != '')) {
                            $parameterArray = array(
                                'phone' => $customerPhone,
                                'message' => $smsMessage,
                                'sender' => null
                            );
                            $isSent = true;//false;
                            $platformConfigurationRecord = \common\models\PlatformsConfiguration::findOne(['configuration_key' => 'PLATFORM_SMS_SERVICE', 'platform_id' => $check_status['platform_id']]);
                            if ($platformConfigurationRecord instanceof \common\models\PlatformsConfiguration) {
                                if (trim($platformConfigurationRecord->configuration_value) != '') {
                                    if ($smsService::sendSms($platformConfigurationRecord->configuration_value, $parameterArray) != false) {
                                        $smscomments = $smsMessage;
                                        $customer_notified = '1';
                                        $isSent = true;
                                    }
                                }
                            }
                            if ($isSent != true) {
                                if (defined('ADMIN_TWO_STEP_AUTH_SERVICE_SMS') AND (ADMIN_TWO_STEP_AUTH_SERVICE_SMS != '')) {
                                    if ($smsService::sendSms(ADMIN_TWO_STEP_AUTH_SERVICE_SMS, $parameterArray) != false) {
                                        $smscomments = $smsMessage;
                                        $customer_notified = '1';
                                        $isSent = true;
                                    }
                                }
                            }
                            if (($isSent != true) AND ($check_status['platform_id'] != \common\classes\platform::defaultId())) {
                                $platformConfigurationRecord = \common\models\PlatformsConfiguration::findOne(['configuration_key' => 'PLATFORM_SMS_SERVICE', 'platform_id' => \common\classes\platform::defaultId()]);
                                if ($platformConfigurationRecord instanceof \common\models\PlatformsConfiguration) {
                                    if (trim($platformConfigurationRecord->configuration_value) != '') {
                                        if ($smsService::sendSms($platformConfigurationRecord->configuration_value, $parameterArray) != false) {
                                            $smscomments = $smsMessage;
                                            $customer_notified = '1';
                                            $isSent = true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /*if (!$order_updated){
                tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, array(
                    'orders_id' => (int) $oID,
                    'orders_status_id' => (int) $status,
                    'date_added' => 'now()',
                    'customer_notified' => $customer_notified,
                    'comments' => $comments,
                    'admin_id' => $admin_id,
                ));
            }*/

            $isAlternativeBehaviour = false;
            $orderStatusRecord = \common\models\OrdersStatus::findOne(['orders_status_id' => (int)$status]);
            if ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_PENDING) {
                $isAlternativeBehaviour = (int)Yii::$app->request->post('evaluation_state_reset_cancel', 0);
            } elseif ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_PROCESSING) {
            } elseif ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_CANCELLED) {
                $isAlternativeBehaviour = (int)Yii::$app->request->post('evaluation_state_restock', 0);
            } elseif ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_DISPATCHED) {
                $isAlternativeBehaviour = (int)Yii::$app->request->post('evaluation_state_force', 0);
            } elseif ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_DELIVERED) {
                $isAlternativeBehaviour = (int)Yii::$app->request->post('evaluation_state_force', 0);
            }
            unset($orderStatusRecord);
            \common\helpers\Order::setStatus($oID, $status, [
                'comments' => $comments,
                'smscomments' => $smscomments,
                'customer_notified' => $customer_notified
            ], false, $isAlternativeBehaviour);

            if ($TrustpilotClass = Acl::checkExtensionAllowed('Trustpilot', 'allowed')) {
                $TrustpilotClass::onOrderUpdateEmail((int)$oID, '');
            }

            if (Acl::checkExtensionAllowed('SMS','showOnOrderPage') && ($sms = Acl::checkExtensionAllowed('SMS', 'allowed')) ){
                $commentid = tep_db_insert_id();
                $response = $sms::sendSMS($oID, $commentid);
                if (is_array($response) && count($response)){
                    $messages[] = ['message' => $response['message'], 'messageType' => $response['messageType']];
                }
            }

            if (method_exists('\common\helpers\Coupon', 'credit_order_check_state')){
                \common\helpers\Coupon::credit_order_check_state((int) $oID);
            }

            $order_updated = true;
        }

        if ($order_updated == true || $order_stock_updated_flag) {
            $messageType = 'success';
            if ($order_stock_updated_flag) {
                $message = '<p>' . TEXT_MESSAGE_ORDER_STOCK_UPDATED . '</p>';
            }
            if ($order_updated) {
                $message = '<p>' . SUCCESS_ORDER_UPDATED . '</p>';
            }
            $messages[] = ['messageType' => 'success', 'message' => $message];
        } else {
            $message = '<p>'.WARNING_ORDER_NOT_UPDATED.'</p>';
            $messages[] = ['messageType' => 'warning', 'message' => $message];
        }

        foreach (\common\helpers\Hooks::getList('orders/process-order') as $filename) {
            include($filename);
        }

        $messageStack = \Yii::$container->get('message_stack');
        if (is_array($messages) && count($messages)){
            foreach($messages as $message){
                $messageStack->add($message['message'], 'header', $message['messageType']);
            }
        }

        return $this->actionProcessOrder();
    }

    public function actionResetAdmin() {
        $basket_id = Yii::$app->request->post('basket_id');
        $customer_id = Yii::$app->request->post('customer_id');
        $orders_id = Yii::$app->request->post('orders_id', 0);
        $admin = new AdminCarts();
        if ($basket_id && $customer_id) {
            $admin->relocateCart($basket_id, $customer_id);
        }
        if ($orders_id) {
            $reload = Url::to(['order-edit', 'orders_id' => $orders_id]);
        } else {
            $reload = Url::to(['order-edit']);
        }
        echo json_encode(['reload' => $reload]);
        exit();
    }

    public function actionResetCart() {
        $id = Yii::$app->request->get('id');
        $admin = new AdminCarts();
        $admin->setLastVirtualID($id);
        return $this->redirect('order-edit');
    }

    public function actionDeletecart(){
        $id = Yii::$app->request->post('deleteCart');
        $admin = new AdminCarts();
        $_cb = explode("-", $id);
        if ($admin->deleteCartByBC($_cb[0], $_cb[1])){
            $ids = $admin->getVirtualCartIDs();
            if ($ids){
                $_last = $admin->getLastVirtualID();
                if (!in_array($_last, $ids)){ // last was deleted
                    echo json_encode(['goto' => Url::to(['orders/order-edit', 'currentCart'  =>$ids[0]]) ]);
                    exit();
                }
            } else {
                echo json_encode(['goto' => Url::to(['orders/']) ]);
                exit();
            }
        }
        echo json_encode(['reload' => true]);
        exit();
    }

    public function actionOrderdelete() {

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $admin = new AdminCarts;
        $admin->deleteCartByOrder($orders_id);

        \common\helpers\Order::remove_order($orders_id, Yii::$app->request->post('restock'), 'Manually deleted');
    }

    public function actionConfirmorderdelete() {

        \common\helpers\Translation::init('admin/orders');

        $this->layout = false;

        $orders_id = Yii::$app->request->post('orders_id');

        $orders_query = tep_db_query("select o.settlement_date, o.approval_code, o.last_xml_export, o.transaction_id, o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.orders_id = '" . (int) $orders_id . "'");
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
        $order_stock_updated = \common\helpers\Order::is_stock_updated($oInfo->orders_id);
        echo '<div class="col_desc_check">' .
        tep_draw_checkbox_field('restock', 'on', $order_stock_updated, '', ($order_stock_updated ? '' : 'disabled="disabled" readonly="readonly"')) . '<span>' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY . '</span>' .
        '</div>';
        ?>
        <div class="btn-toolbar btn-toolbar-order">
            <?php
            echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return cancelStatement()">';
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

    private function tep_get_category_children(&$children, $platform_id, $categories_id, $search = '') {
        if (!is_array($children))
            $children = array();
        $l = \common\helpers\Categories::load_tree_slice($platform_id, $categories_id, true, $search, true);
        foreach ($l as $item) {
            $key = $item['key'];
            $children[] = $key;
            if ($item['folder']) {
                $this->tep_get_category_children($children, $platform_id, intval(mb_substr($item['key'], 1)));
            }
        }
    }

    public function actionCountries() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));

        $delivery_countries = \common\helpers\Order::getOrdersQuery(['delivery_country' => $term])
                ->groupBy('delivery_country')->orderBy('delivery_country')->all();

        echo json_encode(\yii\helpers\ArrayHelper::getColumn($delivery_countries, 'delivery_country'));
    }

    public function actionState() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $country = tep_db_prepare_input(Yii::$app->request->get('country'));

        $delivery_states = \common\helpers\Order::getOrdersQuery(['delivery_state' => $term, 'delivery_country' => $country])
                ->groupBy('delivery_state')->orderBy('delivery_state')->all();

        echo json_encode(\yii\helpers\ArrayHelper::getColumn($delivery_states, 'delivery_state'));
        exit();

    }

    public function actionOrdersdelete() {

        $this->layout = false;

        $selected_ids = Yii::$app->request->post('selected_ids');

        foreach ($selected_ids as $orders_id) {
            \common\helpers\Order::remove_order((int) $orders_id, (int) $_POST['restock'], 'Batch deleting');
        }
    }

    public function actionOrdersbatch() {

        \common\helpers\Translation::init('main');
        \common\helpers\Translation::init('admin/orders');

        $currencies = Yii::$container->get('currencies');

        $use_pdf = true;

        $pages = array();

        $filename = 'document';

        if (!\Yii::$app->request->post('orders') && \Yii::$app->request->get('orders_id')) {
            $_POST['orders'] = $_GET['orders_id'];
        }

        $defaultLanguageId = \common\classes\language::defaultId();

        if ($_GET['action'] == 'selected' && tep_not_null($_POST['orders'])) {
            $orders_query = tep_db_query("select orders_id, platform_id, orders_status, language_id from " . TABLE_ORDERS . " where orders_id in(" . $_POST['orders'] . ")");
        } else if (isset($_GET['oID']) && !empty($_GET['oID'])) {
            $orders_query = tep_db_query("select orders_id, platform_id, orders_status, language_id from " . TABLE_ORDERS . " where orders_id ='" . (int) $_GET['oID'] . "'");
        } else {
            $orders_query = tep_db_query("select orders_id, platform_id, orders_status, language_id from " . TABLE_ORDERS . " where orders_status = 1");
        }

        $isInvoice = (isset($_GET['pdf']) && $_GET['pdf'] == 'invoice' ? true : false);
        $manager = \common\services\OrderManager::loadManager();
        $manager->setModulesVisibility(['shop_order']);
        $splitter = $manager->getOrderSplitter();
        $_qty = tep_db_num_rows($orders_query);
        if ($_qty){
          $pn = ($isInvoice ?'invoice': 'packingslip');
          $pn = \Yii::$app->request->get('page_name', $pn);
            while ($orders = tep_db_fetch_array($orders_query)) {
                $invoices = $splitter->getInstancesFromSplinters($orders['orders_id'], $splitter::STATUS_PAYED);
                if ($isInvoice && $invoices){
                    if ($_qty==1 && $isInvoice ) {
                      //$orderId = $invoice->getOrderId();
                      $orderId = $orders['orders_id'];
                    }
                    foreach($invoices as $invoice){
                        $lan_id = $orders['language_id'] ? $orders['language_id'] : $defaultLanguageId;
                        $pages[] = ['name' => $pn,
                                    'params' => [
                                        'orders_id' => $orders['orders_id'],
                                        'platform_id' => $invoice->info['platform_id'] ? $invoice->info['platform_id'] : 1,
                                        'language_id' => $lan_id,
                                        'order' => $invoice,
                                        'currencies' => $currencies,
                                        'theme_name' => \backend\design\Theme::getThemeName($invoice->info['platform_id'] ? $invoice->info['platform_id'] : 1),
                                        'oID' => $orders['orders_id']
                                    ]
                                ];
                    }
                } else {
                    $order = $manager->getOrderInstanceWithId('\common\classes\Order', $orders['orders_id']);
                    $order->addLegend(($isInvoice ?'Invoice': 'Packingslip') . ' printed', $_SESSION['login_id']);
                    if ($_qty==1) {
                      $orderId = $order->getOrderId();
                    }
                    $lan_id = $orders['language_id'] ? $orders['language_id'] : $defaultLanguageId;
                    $pages[] = ['name' => $pn,
                                'params' => [
                                    'orders_id' => $orders['orders_id'],
                                    'platform_id' => $orders['platform_id'] ? $orders['platform_id'] : 1,
                                    'language_id' => $lan_id,
                                    'order' => $order,
                                    'currencies' => $currencies,
                                    'theme_name' => \backend\design\Theme::getThemeName($orders['platform_id'] ? $orders['platform_id'] : 1),
                                    'oID' => $orders['orders_id']
                                ]
                            ];
                }

                //$filename = ($isInvoice ? str_replace(' ', '_', TEXT_INVOICE) : str_replace(' ', '_', TEXT_PACKINGSLIP));
                $platform_id = $orders['platform_id'];
            }
        }

        $filename = ($isInvoice ? str_replace(' ', '_', TEXT_INVOICE) : str_replace(' ', '_', TEXT_PACKINGSLIP));
        if ($_qty==1) {
          $filename .= $orderId;
          $title = ($isInvoice?TEXT_INVOICE:TEXT_PACKINGSLIP) . ' ' . $orderId;
          $subject = ($isInvoice?TEXT_INVOICE:TEXT_PACKINGSLIP). ' ' . $orderId;
        } else {
          $title = $subject = $filename;

        }

        if ($_qty > 1) {
            // print product list
            if (defined('PACKING_SLIPS_SUMMARY') && PACKING_SLIPS_SUMMARY == 'True') {
                $products = [];
                foreach ($pages as $page) {
                    $order = $page['params']['order'];
                    foreach ($order->getOrderedProducts('packing_slip') as $product) {
                        if (isset($products[$product['id']])) {
                            $products[$product['id']]['qty'] += $product['qty'];
                        } else {
                            $products[$product['id']] = $product;
                        }
                    }
                }
                if (count($products) > 0) {
                    $pages[] = [
                        'name' => 'products-list',
                        'params' => [
                            'products' => $products,
                        ],
                    ];
                }
            }
        }

        \backend\design\PDFBlock::widget([
            'pages' => $pages,
            'params' => [
                'theme_name' => \backend\design\Theme::getThemeName($platform_id),
                'document_name' => $filename . '.pdf',
                'title' => $title,
                'subject' => $subject,
            ]
        ]);
        die;
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

    function actionGettracking() {
        \common\helpers\Translation::init('admin/orders');
        $this->layout = false;
        $this->view->usePopupMode = true;
        if (Yii::$app->request->isPost) {
            $oID = Yii::$app->request->post('orders_id');
        } else {
            $oID = Yii::$app->request->get('orders_id');
        }
        $view = Yii::$app->request->get('view', 0);

        $get_tracking = tep_db_query("select customers_id, tracking_number from " . TABLE_ORDERS . " where orders_id = " . (int) $oID);
        if (tep_db_num_rows($get_tracking) > 0) {
            $result_tracking = tep_db_fetch_array($get_tracking);
            $trackings = [];
            if ($result_tracking && tep_not_null($result_tracking['tracking_number'])) {
                $trackings = explode(";", $result_tracking['tracking_number']);
            }
            return $this->renderAjax('tracking'. ($view?'_view':'') ,[
                    'trackings' => $trackings,
                    'order_id'  => (int)$oID,
                    'customers_id' => $result_tracking['customers_id'],
                ]);
        } else {
            return false;
        }
    }

    function actionParseTracking()
    {
        $this->layout = false;
        $oID = \Yii::$app->request->post('order_id',0);
        $tracking_number = \Yii::$app->request->post('tracking_number','');

        $order = new \common\classes\Order($oID);
        $platform_config = Yii::$app->get('platform')->config($order->info['platform_id']);
        $parsed_tracking = \common\helpers\Order::parse_tracking_number($tracking_number);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'tracking' => $parsed_tracking,
            'qr_image_src' => tep_catalog_href_link('account/order-qrcode', 'oID=' . (int)$oID . '&cID=' . (int)$order->customer['customer_id'] . '&tracking=1&tracking_number=' . urlencode($parsed_tracking['number']), 'SSL'),
        ];
    }

    /**
     * @deprecated new action with table tracking support actionTrackingSave
     *
     */
    function actionSavetracking() {
        global $admin_id;
        \common\helpers\Translation::init('admin/orders');
        $messageType = '';
        $message = '';
        $tracks = [];
        if (Yii::$app->request->isPost) {
            $oID = intval(Yii::$app->request->post('orders_id'));
            $tracking_number = Yii::$app->request->post('tracking_number',[]);
            if(is_array($tracking_number)){
                for($i=0;$i<count($tracking_number);$i++){
                    if (!empty($tracking_number[$i]) && !in_array($tracking_number[$i], $tracks)){
                        $tracks[] = tep_db_prepare_input($tracking_number[$i]);
                    }
                }
            }
        } else {//??
            $oID = intval(Yii::$app->request->get('orders_id'));
            $tracking_number = tep_db_prepare_input(Yii::$app->request->get('tracking_number'));
        }


        $order = new \common\classes\Order($oID);
        $platform_config = Yii::$app->get('platform')->config($order->info['platform_id']);

        if (count($tracks)>0) {
// {{
            if (array_diff($tracks, $order->info['tracking_number']) || count($tracks) != count($order->info['tracking_number'])) {
            //if ($order->info['tracking_number'] != $tracking_number) {
                $notify_comments = $notify_comments_mail = '';
                $new_tracking_codes = [];
                $_check_old = array_map('strtolower',$order->info['tracking_number']);
                foreach ($tracks as $_check_track ) {
                    $_old_index = array_search(strtolower($_check_track), $_check_old);
                    if ( $_old_index===false ){
                        $new_tracking_codes[] = $_check_track;
                    }else{
                        unset($_check_old[$_old_index]);
                    }
                }
                if ( count($new_tracking_codes)>0 ) {
                    $email_params_tracking = array(
                        'TRACKING_NUMBER' => '',
                        'TRACKING_NUMBER_URL' => '',
                    );
                    foreach ($new_tracking_codes as $track) {
                        $tracking_data = \common\helpers\Order::parse_tracking_number($track);
                        $notify_comments .= TEXT_TRACKING_NUMBER . ': ' . $tracking_data['number'] . "\n";
                        $email_params_tracking['TRACKING_NUMBER'] .= (empty($email_params_tracking['TRACKING_NUMBER'])?'':', ').$tracking_data['number'];
                        $email_params_tracking['TRACKING_NUMBER_URL'] .= (empty($email_params_tracking['TRACKING_NUMBER_URL'])?'':', ').'<a href="' . $tracking_data['url'] . '" target="_blank"><img border="0" alt="' . $tracking_data['number'] . '" src="' . tep_catalog_href_link('account/order-qrcode', 'oID=' . (int)$oID . '&cID=' . (int)$order->customer['customer_id'] . '&tracking=1&tracking_number=' . urlencode($track), 'SSL') . '"></a>';
                    }
                    $notify_comments = rtrim($notify_comments);

                    $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                    $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

                    $email_params = \common\helpers\Mail::emailParamsFromOrder($order);
                    $email_params['TRACKING_NUMBER'] = $email_params_tracking['TRACKING_NUMBER'];
                    $email_params['TRACKING_NUMBER_URL'] = $email_params_tracking['TRACKING_NUMBER_URL'];

                    [$email_subject, $email_text] = \common\helpers\Mail::get_parsed_email_template('Add Tracking Number', $email_params, $order->info['language_id'], $order->info['platform_id']);
                    \common\helpers\Mail::send(
                            $order->customer['name'], $order->customer['email_address'],
                            $email_subject, $email_text,
                            $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS
                    );

                    tep_db_perform(TABLE_ORDERS_STATUS_HISTORY,array(
                        'orders_id' => $order->order_id,
                        'orders_status_id' => $order->info['order_status'],
                        'date_added' => 'now()',
                        'customer_notified' => 1,
                        'comments' => $notify_comments,
                        'admin_id' => $admin_id,
                    ));
                }

                tep_db_perform(TABLE_ORDERS, array('tracking_number' => implode(";", $tracks), 'last_modified'=>'now()'), 'update', "orders_id = '" . (int) $oID . "'");
            }
// }}
            $messageType = 'success';
            $message = TEXT_TRACKING_MESSAGE_SUCCESS;
        } else {
            if ( !empty($order->info['tracking_number']) ) {
                tep_db_perform(TABLE_ORDERS, array('tracking_number' => '', 'last_modified'=>'now()'), 'update', "orders_id = '" . (int)$oID . "'");
            }

            $messageType = 'warning';
            $message = TEXT_TRACKING_MESSAGE_WARNING;
        }

        echo json_encode([
           'message'  => '<div class="alert alert-' . $messageType . ' fade in"><i data-dismiss="alert" class="icon-remove close"></i>' . $message . '</div>',
        ]);
        exit();
    }

    /**
     * for order view
     * @return bool|string
     */
    function actionTrackingList() {
        \common\helpers\Translation::init('admin/orders');
        $this->layout = false;
        $this->view->usePopupMode = true;
        if (Yii::$app->request->isPost) {
            $orders_id = Yii::$app->request->post('orders_id');
        } else {
            $orders_id = Yii::$app->request->get('orders_id');
        }

        $get_order = tep_db_fetch_array(tep_db_query("select o.customers_id, sum(op.products_quantity) as products_quantity from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_PRODUCTS . " op on o.orders_id = op.orders_id where o.orders_id = '" . (int) $orders_id . "'"));
        if ($get_order['customers_id'] > 0) {
            $order = new \common\classes\Order($orders_id);

            //$trackings = array_map(function($item){ return $item->getAttributes(); },$order->info['tracking_number']);
            $trackings = $order->info['tracking_number'];

            $selected_products_quantity = 0;
            foreach (\common\helpers\Order::getAllocatedArray($orders_id, true) as $opaRecord) {
                $selected_products_quantity += ($opaRecord['allocate_received'] - $opaRecord['allocate_dispatched']);
            }
            unset($opaRecord);

            $get_tracking = tep_db_query("select trn.tracking_numbers_id, trn.tracking_carriers_id, trn.tracking_number, sum(trn2op.products_quantity) as products_quantity from " . TABLE_TRACKING_NUMBERS . " trn left join " . TABLE_TRACKING_NUMBERS_TO_ORDERS_PRODUCTS . " trn2op on trn2op.tracking_numbers_id = trn.tracking_numbers_id and trn2op.orders_id = trn.orders_id where trn.orders_id = '" . (int) $orders_id . "' group by trn.tracking_numbers_id");

            $products_per_tracking = [];
            while ($result_tracking = tep_db_fetch_array($get_tracking)) {
                //$trackings[$result_tracking['tracking_numbers_id']] = $result_tracking;
                //$selected_products_quantity += $result_tracking['products_quantity'];
                $productsArr = [];
                $tracking_products_query = tep_db_query("select tracking_numbers_id, orders_products_id, products_quantity from " . TABLE_TRACKING_NUMBERS_TO_ORDERS_PRODUCTS . " where tracking_numbers_id = '" . (int) $result_tracking['tracking_numbers_id'] . "' and orders_id = '" . (int) $orders_id . "'");
                while ($tracking_products = tep_db_fetch_array($tracking_products_query)) {
                    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                        if ($order->products[$i]['orders_products_id'] == $tracking_products['orders_products_id']) {
                            $productsArr[] = $order->products[$i];
                            $productsArr[count($productsArr)-1]['qty'] = $tracking_products['products_quantity'];
                            foreach ($trackings as &$trackingRecord) {
                                if ($trackingRecord->tracking_numbers_id == $tracking_products['tracking_numbers_id']) {
                                    $trackingRecord->products_quantity += $tracking_products['products_quantity'];
                                    break;
                                }
                            }
                            unset($trackingRecord);
                        }
                    }
                }
                $products_per_tracking[$result_tracking['tracking_numbers_id']] = $productsArr;
                //$trackings[$result_tracking['tracking_numbers_id']]['products'] = $productsArr;
            }

            return $this->renderAjax('tracking-list', [
                        'trackings' => $trackings,
                        'products_per_tracking' => $products_per_tracking,
                        'orders_id' => (int) $orders_id,
                        'customers_id' => $get_order['customers_id'],
                        'products_left' => $selected_products_quantity,
            ]);
        } else {
            return false;
        }
    }

    function actionTrackingEdit() {
        $this->layout = false;
        $this->view->usePopupMode = true;
        $orders_id = Yii::$app->request->get('orders_id');
        $tracking_numbers_id = Yii::$app->request->get('tracking_numbers_id');

        $get_tracking = tep_db_fetch_array(tep_db_query(
            "select o.customers_id, trn.tracking_numbers_id, trn.tracking_carriers_id, trn.tracking_number ".
            "from " . TABLE_ORDERS . " o ".
            "  left join " . TABLE_TRACKING_NUMBERS . " trn on o.orders_id = trn.orders_id and trn.tracking_numbers_id = '" . (int) $tracking_numbers_id . "' ".
            "where o.orders_id = '" . (int) $orders_id . "'"
        ));

        $order = new \common\classes\Order($orders_id);

        $orders_products = [];
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            $product = $order->products[$i];
            $product['qty_max'] = ((int)$product['qty_rcvd'] - (int)$product['qty_dspd']);
            $product['qty_min'] = min(1, $product['qty_max']);
            $product['qty'] = $product['qty_max'];
            $orders_products[$order->products[$i]['orders_products_id']] = $product;
            unset($product);
        }

        if ($tracking_numbers_id > 0) {
            $selected_products_query = tep_db_query("select orders_products_id, products_quantity from " . TABLE_TRACKING_NUMBERS_TO_ORDERS_PRODUCTS . " where tracking_numbers_id = '" . (int) $tracking_numbers_id . "' and orders_id = '" . (int) $orders_id . "'");
            if (tep_db_num_rows($selected_products_query) > 0) {
                while ($selected_products = tep_db_fetch_array($selected_products_query)) {
                    $orders_products[$selected_products['orders_products_id']]['selected'] = true;
                    $orders_products[$selected_products['orders_products_id']]['qty'] = $selected_products['products_quantity'];
                    $orders_products[$selected_products['orders_products_id']]['qty_min'] = $selected_products['products_quantity'];
                    $orders_products[$selected_products['orders_products_id']]['qty_max'] += $selected_products['products_quantity'];
                }
            }

            //draft - 2do extra payment when TN is added to another transaction
            $paymentTrackingQ = \common\models\TrackingNumbersExport::find()
                ->andWhere(['tracking_numbers_id' => $tracking_numbers_id])
                ->joinWith('payments p', false, 'INNER JOIN')
                ->select([
                    'date_added', 'status', 'message',
                    'id' => 'p.orders_payment_id',
                    'paid_on' => 'orders_payment_transaction_date',
                    'payment_class' => 'orders_payment_module',
                    'payment' => 'orders_payment_module_name',
                    'transaction' => 'orders_payment_transaction_id',
                ])
                ->andWhere('status>0')
                ->orderBy('status desc, orders_payment_module')
                ;
            $transactions = $paymentTrackingQ->asArray()->all();
        }

        if ($get_tracking['customers_id'] > 0) {

            if (empty($transactions)) {
                $paymentQ = \common\models\OrdersPayment::find()
                    ->select([
                      'id' => 'orders_payment_id',
                      'paid_on' => 'orders_payment_transaction_date',
                      'payment_class' => 'orders_payment_module',
                      'payment' => 'orders_payment_module_name',
                      'transaction' => 'orders_payment_transaction_id',
                    ])
                    ->andWhere(['orders_payment_order_id' => $orders_id])
                    ;
                $transactions = $paymentQ->asArray()->all();
                $skip = $keep = [];
                foreach ($transactions as $i => $transaction) {
                    if (in_array($transaction['payment_class'], $keep)) {
                        continue;
                    }
                    if (!in_array($transaction['payment_class'], $skip)) {
                        $manager = \common\services\OrderManager::loadManager();
                        /** @var common\classes\Order $order */
                        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);
                        try { //payment could be switched off
                            $builder = new \common\classes\modules\ModuleBuilder($manager);
                            $class = $builder(['class' => "\\common\\modules\\orderPayment\\{$transaction['payment_class']}"]);
                            if (method_exists($class, 'add_tracking')) {
                                $keep[] = $transaction['payment_class'];
                                continue;
                            }
                        } catch (\Exception $e) {
                        }
                    }
                    unset($transactions[$i]);
                }
            }
            if (!empty($transactions)) {
                \common\helpers\Translation::init('payment');
                $sync['transactions'] = $transactions;
                $sync['added'] = !empty($transactions[0]['status']);
            }
            


            return $this->renderAjax('tracking-edit', [
                        'orders_id' => (int) $orders_id,
                        'customers_id' => $get_tracking['customers_id'],
                        'tracking_number' => $get_tracking['tracking_number'],
                        'tracking_numbers_id' => $get_tracking['tracking_numbers_id'],
                        'orders_products' => $orders_products,
                        'platform_id' => $order->info['platform_id'],
                        'sync' => $sync??null,
            ]);
        } else {
            return false;
        }
    }

    function actionTrackingSave() {
        \common\helpers\Translation::init('admin/orders');
        \common\helpers\Translation::init('payment');
        $orders_id = Yii::$app->request->post('orders_id');
        $tracking_numbers_id = Yii::$app->request->post('tracking_numbers_id');
        $tracking_number = Yii::$app->request->post('tracking_number');
        $selected_products = Yii::$app->request->post('selected_products', []);
        $selected_products_qty = Yii::$app->request->post('selected_products_qty', []);
        $selected_products_qty_max = Yii::$app->request->post('selected_products_qty_max', []);

        foreach (\common\models\TrackingNumbersToOrdersProducts::find()
            ->where(['tracking_numbers_id' => $tracking_numbers_id])
            ->andWhere(['orders_id' => $orders_id])
            ->all() as $trackingProductRecord
        ) {
            if (!in_array($trackingProductRecord->orders_products_id, $selected_products)) {
                $selected_products[] = $trackingProductRecord->orders_products_id;
            }
        }
        unset($trackingProductRecord);
        $tracking_order_products = [];
        foreach ($selected_products as $orders_products_id){
            $selected_qty = min($selected_products_qty[$orders_products_id], $selected_products_qty_max[$orders_products_id]);
            if ( $selected_qty>0 ) {
                $tracking_order_products[$orders_products_id] = $selected_qty;
            }
        }

        if (tep_not_null($tracking_number)) {
            $order = new \common\classes\Order($orders_id);

            $_update_tracking = false;
            if ( !empty($tracking_numbers_id) ) {
                foreach ($order->info['tracking_number'] as $_idx=>$trackingNumber) {
                    /**
                     * @var $trackingNumber \common\classes\OrderTrackingNumber
                     */
                    if ($trackingNumber->tracking_numbers_id == $tracking_numbers_id) {
                        $_update_tracking = true;
                        if ( empty($tracking_number) ) {
                            unset($order->info['tracking_number'][$_idx]);
                        }else {
                            $trackingNumber->tracking_number = $tracking_number;
                        }
                        $trackingNumber->setOrderProducts($tracking_order_products);
                    }
                }
            }
            if ( !$_update_tracking && !empty($tracking_number) ) {
                $addTracking = \common\classes\OrderTrackingNumber::instanceFromString($tracking_number, $order->order_id);
                $addTracking->setOrderProducts($tracking_order_products);
                $order->info['tracking_number'][] = $addTracking;
            }
            $order->saveTrackingNumbers();
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('Ebay', 'allowed')) {
                $ext::setUpdateOrder($orders_id);
            }
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('Amazon', 'allowed')) {
                $ext::setUpdateOrder($orders_id);
            }

            $transactions = Yii::$app->request->post('sync_to_payment', []);

            if (!empty($transactions)) {
                $paymentQ = \common\models\OrdersPayment::find()
                    ->select([
                      'id' => 'orders_payment_id',
                      'paid_on' => 'orders_payment_transaction_date',
                      'payment_class' => 'orders_payment_module',
                      'payment' => 'orders_payment_module_name',
                      'transaction' => 'orders_payment_transaction_id',
                    ])
                    ->andWhere(['orders_payment_id' => array_values($transactions)])
                    ;
                $transactions = $paymentQ->asArray()->all();

                $keep = [];
                foreach ($transactions as $i => $transaction) {
                    if (!isset($keep[$transaction['payment_class']])) {
                        $manager = \common\services\OrderManager::loadManager();
                        try { //payment could be switched off
                            $builder = new \common\classes\modules\ModuleBuilder($manager);
                            $class = $builder(['class' => "\\common\\modules\\orderPayment\\{$transaction['payment_class']}"]);
                            if (method_exists($class, 'add_tracking')) {
                                $keep[$transaction['payment_class']] = $class;
                            }
                        } catch (\Exception $e) {
                        }
                    }
                    if (isset($keep[$transaction['payment_class']])) {
                        if (empty($tracking_numbers_id) && !empty($addTracking)) {
                            if (empty($addTracking->tracking_numbers_id)) {
                                $addTracking->refresh();
                            }
                            $tracking_numbers_id = $addTracking->tracking_numbers_id;
                        }
                        if (!empty($tracking_numbers_id)) {
                            $keep[$transaction['payment_class']]->add_tracking([
                              "transaction_id" => $transaction['transaction'],
                              "tracking_number" => $tracking_number,
                              "orders_payment_id" => $transaction['id'],
                              "tracking_numbers_id" => $tracking_numbers_id,
                              "orders_id" => $orders_id
                            ]);
                        }
                    }
                }
            }

        }
    }

    function actionTrackingDelete() {
        $orders_id = Yii::$app->request->post('orders_id');
        $tracking_numbers_id = Yii::$app->request->post('tracking_numbers_id');

        //payment tracking before deleting TN
        if ($tracking_numbers_id) {
            $ptns = \common\models\TrackingNumbersExport::findAll(['tracking_numbers_id' => $tracking_numbers_id]);
            foreach ($ptns as $ptn) {
                if (!empty($ptn) && !empty($ptn->classname)) {
                    $manager = \common\services\OrderManager::loadManager();

                    try { //payment could be switched off
                        $builder = new \common\classes\modules\ModuleBuilder($manager);
                        $class = $builder(['class' => "\\common\\modules\\orderPayment\\{$ptn->classname}"]);
                        if (method_exists($class, 'delete_tracking')) {
                            $class->delete_tracking($ptn->getAttributes());
                        }
                        $ptn->delete();
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        $order = new \common\classes\Order($orders_id);
        $order->removeTrackingNumber($tracking_numbers_id);
    }

    public function actionOrdersexport() {
        if (tep_not_null($_POST['orders'])) {

            $filename = 'orders_' . strftime('%Y%b%d_%H%M') . '.csv';
            $writer = new \backend\models\EP\Formatter\CSV('write', array(), $filename);
            $writer->write_array(["Order ID", "Ship Method", "Shipping Company", "Shipping Street 1", "Shipping Street 2", "Shipping Suburb", "Shipping State", "Shipping Zip", "Shipping Country", "Shipping Name"]);

            foreach(\common\models\Orders::find()->where(['orders_id' => array_map('intval', explode(',', $_POST['orders'])) ])->all() as $order){
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

    public function actionGvChangeState() {
        \common\helpers\Translation::init('admin/orders');

        $opID = intval(Yii::$app->request->get('opID', 0));
        $_order_id = tep_db_fetch_array(tep_db_query("SELECT orders_id, gv_state FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_products_id='" . (int) $opID . "'"));
        if (Yii::$app->request->isPost) {
            \common\helpers\Coupon::credit_order_manual_update_state($opID, Yii::$app->request->post('new_gv_state', $_order_id['gv_state']));
            echo 'ok';
        }
        ?>
        <?php echo tep_draw_form('update_gv', 'orders/gv-change-state', \common\helpers\Output::get_all_get_params(), 'post', 'id="frmGvChangeState"'); ?>
        <div class="pop-up-content">
            <div class="popup-content">
                <div><label><?php echo tep_draw_radio_field('new_gv_state', 'pending', $_order_id['gv_state'] == 'pending', '', (in_array($_order_id['gv_state'], array('released')) ? 'disabled="disabled" readonly="readonly"' : '')); ?>
                        <?php echo TEXT_GV_STATE_SWITCH_TO_PENDING ?></label></div>
                <div><label><?php echo tep_draw_radio_field('new_gv_state', 'released', $_order_id['gv_state'] == 'released', '', (in_array($_order_id['gv_state'], array('released')) ? 'disabled="disabled" readonly="readonly"' : '')); ?>
                        <?php echo TEXT_GV_STATE_SWITCH_TO_RELEASED ?></label></div>
                <div><label><?php echo tep_draw_radio_field('new_gv_state', 'canceled', $_order_id['gv_state'] == 'canceled', '', (in_array($_order_id['gv_state'], array('released')) ? 'disabled="disabled" readonly="readonly"' : '')); ?>
                        <?php echo TEXT_GV_STATE_SWITCH_TO_CANCELED ?></label></div>
            </div>
        </div>
        <div class="noti-btn">
            <div><span class="btn btn-cancel"><?php echo IMAGE_CANCEL; ?></span></div>
            <div><span class="btn btn-primary" id="btnGvChangeState"><?php echo IMAGE_UPDATE; ?></span></div>
        </div>
        </form>
        <script type="text/javascript">
            $('#btnGvChangeState').on('click', function () {
                $.ajax({
                    type: "POST",
                    url: $('#frmGvChangeState').attr('action'),
                    data: $('#frmGvChangeState').serializeArray(),
                    success: function (data) {
                        window.location.href = window.location.href;
                        $('#frmGvChangeState .btn-cancel').trigger('click');
                    }
                });
            });
        </script>
        <?php
    }

    public function actionProductsStatusHistory() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/orders');
        $opID = Yii::$app->request->get('opID');

        $orders_products_statuses = [
            ['id' => 0, 'text' => ''],
            ['id' => \common\helpers\OrderProduct::OPS_QUOTED, 'text' => TEXT_STATUS_LONG_OPS_QUOTED],
            ['id' => \common\helpers\OrderProduct::OPS_RECEIVED, 'text' => TEXT_STATUS_LONG_OPS_RECEIVED],
            ['id' => \common\helpers\OrderProduct::OPS_DISPATCHED, 'text' => TEXT_STATUS_LONG_OPS_DISPATCHED],
            ['id' => \common\helpers\OrderProduct::OPS_DELIVERED, 'text' => TEXT_STATUS_LONG_OPS_DELIVERED],
            ['id' => \common\helpers\OrderProduct::OPS_CANCELLED, 'text' => TEXT_STATUS_LONG_OPS_CANCELLED]
        ];

        $orders_products_statuses_manual = [[
            'id' => 0,
            'text' => ''
        ]];
        $orders_products_status_array = [];

        $orderProductArray = [];
        $orderProductRecord = \common\helpers\OrderProduct::getRecord($opID);
        if (\common\helpers\OrderProduct::isValidAllocated($orderProductRecord) == true) {
            if (count(\common\helpers\OrderProduct::getChildArray($orderProductRecord)) == 0) {
                $warehouseNameList = [];
                foreach (\common\models\Warehouses::find()->asArray(true)->all() as $warehouseRecord) {
                    $warehouseNameList[$warehouseRecord['warehouse_id']] = $warehouseRecord['warehouse_name'];
                }
                unset($warehouseRecord);
                $supplierNameList = [];
                foreach (\common\models\Suppliers::find()->asArray(true)->all() as $supplierRecord) {
                    $supplierNameList[$supplierRecord['suppliers_id']] = $supplierRecord['suppliers_name'];
                }
                unset($supplierRecord);
                $locationBlockList = [];
                foreach (\common\models\LocationBlocks::find()->asArray(true)->all() as $locationBlockRecord) {
                    $locationBlockList[$locationBlockRecord['block_id']] = $locationBlockRecord['block_name'];
                }
                unset($locationBlockRecord);

                $quotedArray = [];
                $receivedArray = [];
                $cancelledArray = [];
                $deliveredArray = [];
                $dispatchedArray = [];
                $quantityReceived = 0;
                $quantityDispatched = 0;
                $quantityReal = (int)\common\helpers\OrderProduct::getQuantityReal($orderProductRecord);
                $quantityRealParent = $quantityReal;
                $oppRecord = \common\helpers\OrderProduct::getParent($orderProductRecord, false);
                if ($oppRecord instanceof \common\models\OrdersProducts) {
                    $opcQuantityMultiplier = 1;
                    if ((int)$oppRecord->products_quantity > 0) {
                        $opcQuantityMultiplier = (int)ceil((int)$orderProductRecord->products_quantity / (int)$oppRecord->products_quantity);
                    }
                    $opcQuantityReal = (\common\helpers\OrderProduct::getQuantityReal($oppRecord) * $opcQuantityMultiplier);
                    unset($opcQuantityMultiplier);
                    if ($quantityRealParent > $opcQuantityReal) {
                        $quantityRealParent = $opcQuantityReal;
                    }
                    unset($opcQuantityReal);
                }
                unset($oppRecord);
                foreach (\common\helpers\OrderProduct::getAllocatedArray($orderProductRecord, true) as $opaRecord) {
                    $warehouseName = (isset($warehouseNameList[$opaRecord['warehouse_id']]) ? $warehouseNameList[$opaRecord['warehouse_id']] : 'N/A');
                    $supplierName = (isset($supplierNameList[$opaRecord['suppliers_id']]) ? $supplierNameList[$opaRecord['suppliers_id']] : 'N/A');
                    $locationName = trim(\common\helpers\Warehouses::getLocationPath($opaRecord['location_id'], $opaRecord['warehouse_id'], $locationBlockList));
                    $locationName = (($locationName != '') ? $locationName : 'N/A');
                    $layersName = \common\helpers\Date::date_short(\common\helpers\Warehouses::getExpiryDateByLayersID($opaRecord['layers_id']));
                    $layersName = ($layersName != '' ? \common\helpers\Translation::getTranslationValue('TEXT_EXPIRY_DATE', 'admin/categories') . ' ' . $layersName : 'N/A');
                    $batchName = \common\helpers\Warehouses::getBatchNameByBatchID($opaRecord['batch_id']);
                    $batchName = ($batchName != '' ? TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME . ' ' . $batchName : 'N/A');
                    // QUOTED
                    $min = 0;
                    $max = (int)$opaRecord['allocate_received'];
                    if ($min != $max) {
                        $quotedArray[$opaRecord['warehouse_id']][$opaRecord['suppliers_id']][$opaRecord['location_id']][$opaRecord['layers_id']][$opaRecord['batch_id']] = [
                            'value' => 0,
                            'min' => $min,
                            'max' => $max,
                            'warning' => [
                                '>' => [
                                    'value' => ($max - (int)$opaRecord['allocate_dispatched']),
                                    'message' => TEXT_ORDER_PRODUCT_RESTOCK_WARNING_MESSAGE,
                                    'calculate' => ('value - ' . ($max - (int)$opaRecord['allocate_dispatched'])),
                                    'calculateAfter' => 'x&nbsp;'
                                ]
                            ],
                            'warehouseName' => $warehouseName,
                            'supplierName' => $supplierName,
                            'locationName' => $locationName,
                            'layersName' => $layersName,
                            'batchName' => $batchName,
                        ];
                    }
                    unset($max);
                    unset($min);
                    // EOF QUOTED
                    // RECEIVED
                    $min = 0;
                    $max = (int)$opaRecord['allocate_received'];
                    $quantityReceived += $max;
                    if ($min != $max) {
                        $receivedArray[$opaRecord['warehouse_id']][$opaRecord['suppliers_id']][$opaRecord['location_id']][$opaRecord['layers_id']][$opaRecord['batch_id']] = [
                            'value' => $max,
                            'min' => $min,
                            'max' => $max,
                            'awaiting' => $quantityRealParent,
                            'warning' => [
                                '<' => [
                                    'value' => (int)$opaRecord['allocate_dispatched'],
                                    'message' => TEXT_ORDER_PRODUCT_RESTOCK_WARNING_MESSAGE,
                                    'calculate' => ('Math.abs(value - ' . (int)$opaRecord['allocate_dispatched'] . ')'),
                                    'calculateAfter' => 'x&nbsp;'
                                ]
                            ],
                            'warehouseName' => $warehouseName,
                            'supplierName' => $supplierName,
                            'locationName' => $locationName,
                            'layersName' => $layersName,
                            'batchName' => $batchName,
                        ];
                    }
                    unset($max);
                    unset($min);
                    // EOF RECEIVED
                    // DISPATCHED
                    $min = 0;
                    $max = ((int)$opaRecord['allocate_received'] - (int)$opaRecord['allocate_dispatched']);
                    if ($min != $max) {
                        $dispatchedArray[$opaRecord['warehouse_id']][$opaRecord['suppliers_id']][$opaRecord['location_id']][$opaRecord['layers_id']][$opaRecord['batch_id']] = [
                            'value' => 0,
                            'min' => $min,
                            'max' => $max,
                            'warehouseName' => $warehouseName,
                            'supplierName' => $supplierName,
                            'locationName' => $locationName,
                            'layersName' => $layersName,
                            'batchName' => $batchName,
                        ];
                    }
                    unset($max);
                    unset($min);
                    // EOF DISPATCHED
                    // DELIVERED
                    $min = 0;
                    $max = ((int)$opaRecord['allocate_dispatched'] - (int)$opaRecord['allocate_delivered']);
                    if ($min != $max) {
                        $deliveredArray[$opaRecord['warehouse_id']][$opaRecord['suppliers_id']][$opaRecord['location_id']][$opaRecord['layers_id']][$opaRecord['batch_id']] = [
                            'value' => 0,
                            'min' => $min,
                            'max' => $max,
                            'warehouseName' => $warehouseName,
                            'supplierName' => $supplierName,
                            'locationName' => $locationName,
                            'layersName' => $layersName,
                            'batchName' => $batchName,
                        ];
                    }
                    unset($max);
                    unset($min);
                    // EOF DELIVERED
                    $quantityDispatched += (int)$opaRecord['allocate_dispatched'];
                    unset($warehouseName);
                    unset($supplierName);
                    unset($locationName);
                    unset($layersName);
                    unset($batchName);
                }
                unset($opaRecord);

                // CANCELLED
                $min = 0;
                $max = ($quantityReal - $quantityReceived);
                if ($min != $max OR $quantityReal == 0) {
                    $cancelledArray[0][0][0][0][0] = [
                        'value' => $orderProductRecord->qty_cnld,
                        'min' => $min,
                        'max' => ($max + (int)$orderProductRecord->qty_cnld)
                    ];
                } else {
                    $cancelledArray[0][0][0][0][0] = [
                        'html' => \yii\helpers\Html::checkbox('evaluation_state_restock', false, ['label' => TEXT_EVALUATION_STATE_RESTOCK])
                    ];
                }
                unset($max);
                unset($min);
                // EOF CANCELLED

                unset($quantityDispatched);
                unset($quantityReceived);

                $uProductId = \common\helpers\Inventory::getInventoryId($orderProductRecord->uprid);
                $paArray = [];
                foreach (\common\helpers\Product::getAllocatedArray($uProductId) as $paRecord) {
                    $paArray[$paRecord['warehouse_id']][$paRecord['suppliers_id']][$paRecord['location_id']][$paRecord['layers_id']][$paRecord['batch_id']][] = $paRecord;
                }
                unset($paRecord);
                $patArray = [];
                foreach (\common\helpers\Product::getAllocatedTemporaryArray($uProductId) as $patRecord) {
                    $patArray[$patRecord['warehouse_id']][$patRecord['suppliers_id']][$patRecord['location_id']][$patRecord['layers_id']][$patRecord['batch_id']][] = $patRecord;
                }
                unset($patRecord);
                foreach (\common\helpers\Warehouses::getProductArray($uProductId) as $wpRecord) {
                    $warehouseId = $wpRecord['warehouse_id'];
                    $supplierId = $wpRecord['suppliers_id'];
                    $locationId = $wpRecord['location_id'];
                    $layersId = $wpRecord['layers_id'];
                    $batchId = $wpRecord['batch_id'];
                    $available = (int)$wpRecord['warehouse_stock_quantity'];
                    if (isset($paArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId])) {
                        foreach ($paArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId] as $paRecord) {
                            $available -= (int)$paRecord['allocate_received'];
                        }
                        unset($paArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId]);
                        unset($paRecord);
                    }
                    if (isset($patArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId])) {
                        foreach ($patArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId] as $patRecord) {
                            $available -= (int)$patRecord['temporary_stock_quantity'];
                        }
                        unset($patArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId]);
                        unset($patRecord);
                    }
                    if (isset($receivedArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId])) {
                        $receivedRecord = &$receivedArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId];
                        $available += $receivedRecord['max'];
                        if ($receivedRecord['max'] < $available) {
                            $receivedRecord['max'] = $available;
                        }
                        unset($receivedRecord);
                    } elseif ($available > 0) {
                        $warehouseName = (isset($warehouseNameList[$warehouseId]) ? $warehouseNameList[$warehouseId] : 'N/A');
                        $supplierName = (isset($supplierNameList[$supplierId]) ? $supplierNameList[$supplierId] : 'N/A');
                        $locationName = trim(\common\helpers\Warehouses::getLocationPath($locationId, $warehouseId, $locationBlockList));
                        $locationName = (($locationName != '') ? $locationName : 'N/A');
                        $layersName = \common\helpers\Date::date_short(\common\helpers\Warehouses::getExpiryDateByLayersID($layersId)); 
                        $layersName = ($layersName != '' ? \common\helpers\Translation::getTranslationValue('TEXT_EXPIRY_DATE', 'admin/categories') . ' ' . $layersName : 'N/A');
                        $batchName = \common\helpers\Warehouses::getBatchNameByBatchID($batchId);
                        $batchName = ($batchName != '' ? TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME . ' ' . $batchName : 'N/A');
                        $receivedArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId] = [
                            'value' => 0,
                            'min' => 0,
                            'max' => $available,
                            'awaiting' => $quantityRealParent,
                            'warehouseName' => $warehouseName,
                            'supplierName' => $supplierName,
                            'locationName' => $locationName,
                            'layersName' => $layersName,
                            'batchName' => $batchName,
                        ];
                        unset($warehouseName);
                        unset($supplierName);
                        unset($locationName);
                        unset($layersName);
                        unset($batchName);
                    }
                    unset($warehouseId);
                    unset($supplierId);
                    unset($locationId);
                    unset($layersId);
                    unset($batchId);
                    unset($available);
                }
                unset($quantityRealParent);
                unset($locationBlockList);
                unset($warehouseNameList);
                unset($supplierNameList);
                unset($quantityReal);
                unset($uProductId);
                unset($patArray);
                unset($wpRecord);
                unset($paArray);

                if (count($quotedArray) == 0) {
                    $quotedArray[0][0][0][0][0] = [
                        'html' => \yii\helpers\Html::checkbox('evaluation_state_reset_cancel', false, ['label' => TEXT_EVALUATION_STATE_RESET_CANCEL])
                    ];
                }
                if (count($dispatchedArray) == 0) {
                    $dispatchedArray[0][0][0][0][0] = [
                        'html' => \yii\helpers\Html::checkbox('evaluation_state_force', false, ['label' => TEXT_EVALUATION_STATE_FORCE])
                    ];
                }
                if (count($deliveredArray) == 0) {
                    $deliveredArray[0][0][0][0][0] = [
                        'html' => \yii\helpers\Html::checkbox('evaluation_state_force', false, ['label' => TEXT_EVALUATION_STATE_FORCE])
                    ];
                }

                $orderProductArray = [
                    \common\helpers\OrderProduct::OPS_QUOTED => $quotedArray,
                    \common\helpers\OrderProduct::OPS_RECEIVED => $receivedArray,
                    \common\helpers\OrderProduct::OPS_DISPATCHED => $dispatchedArray,
                    \common\helpers\OrderProduct::OPS_DELIVERED => $deliveredArray,
                    \common\helpers\OrderProduct::OPS_CANCELLED => $cancelledArray,
                ];
                unset($dispatchedArray);
                unset($deliveredArray);
                unset($cancelledArray);
                unset($receivedArray);
                unset($quotedArray);
            } else {
                $orders_products_statuses = [];
            }
        }

        foreach (\common\models\OrdersProductsStatus::find()->where(['language_id' => (int)$languages_id])->all() as $opsRecord) {
            if (is_object($orderProductRecord) AND $orderProductRecord->orders_products_status == $opsRecord->orders_products_status_id) {
                foreach ($opsRecord->getMatrixArray() as $opsmmRecord) {
                    $orders_products_statuses_manual[] = [
                        'id' => $opsmmRecord->orders_products_status_manual_id,
                        'text' => $opsmmRecord->orders_products_status_manual_name_long
                    ];
                }
                unset($opsmmRecord);
            }
            $orders_products_status_array[$opsRecord->orders_products_status_id] = $opsRecord->orders_products_status_name_long;
        }
        unset($opsRecord);
        $orders_products_status_manual_array = [];
        foreach (\common\models\OrdersProductsStatusManual::find()->asArray(true)->where(['language_id' => (int)$languages_id])->all() as $opsmRecord) {
            $orders_products_status_manual_array[$opsmRecord['orders_products_status_manual_id']] = $opsmRecord['orders_products_status_manual_name_long'];
        }
        unset($opsmRecord);

        foreach (\common\models\OrdersProductsStatusHistory::find()->asArray(true)->where(['orders_products_id' => (int)$opID])->orderBy(['orders_products_history_id' => SORT_DESC])->all() as $opshRecord) {
            $adminName = '';
            if ($opshRecord['admin_id'] > 0) {
                $adminRecord = \common\models\Admin::findOne(['admin_id' => (int)$opshRecord['admin_id']]);
                if (is_object($adminRecord)) {
                    $adminName = trim($adminRecord->admin_firstname . ' ' . $adminRecord->admin_lastname);
                }
                unset($adminRecord);
            }
            $history[] = [
                'id' => $opshRecord['orders_products_history_id'],
                'date' => \common\helpers\Date::datetime_short($opshRecord['date_added']),
                'status' => $orders_products_status_array[$opshRecord['orders_products_status_id']],
                'status_manual' => $orders_products_status_manual_array[$opshRecord['orders_products_status_manual_id']],
                'comments' => $opshRecord['comments'],
                'admin' => $adminName
            ];
            unset($adminName);
        }
        unset($orders_products_status_manual_array);
        unset($orders_products_status_array);
        unset($opshRecord);
        return $this->renderAjax('products-status-history', [
            'history' => $history ?? null,
            'product' => $orderProductRecord->toArray(),
            'statuses_array' => $orders_products_statuses,
            'statuses_manual_array' => $orders_products_statuses_manual,
            'orderProductArray' => $orderProductArray
        ]);
    }

    public function actionProductsStatusUpdate() {
        global $login_id;
        $languages_id = \Yii::$app->settings->get('languages_id');

        $oStatus = 0;
        $opStatus = (int)Yii::$app->request->post('status', 0);
        $orderProductId = (int)Yii::$app->request->post('opID', 0);
        $commentary = trim(Yii::$app->request->post('comments', ''));
        $opStatusManual = (int)Yii::$app->request->post('status_manual', 0);
        $opRecord = \common\helpers\OrderProduct::getRecord($orderProductId);
        if ($opRecord instanceof \common\models\OrdersProducts) {
            $opStatusValue = (int)$opRecord->orders_products_status;
            $opStatusManualValue = (int)$opRecord->orders_products_status_manual;
            if (count(\common\helpers\OrderProduct::getChildArray($opRecord)) > 0) {
                \common\helpers\OrderProduct::evaluate($opRecord);
            } elseif ($opStatus > 0) {
                $opUpdateArray = Yii::$app->request->post('update_order_product_' . $opStatus, false);
                if ($opStatus == \common\helpers\OrderProduct::OPS_QUOTED) {
                    if (is_array($opUpdateArray)) {
                        foreach ($opUpdateArray as $warehouseId => $supplierArray) {
                            foreach ($supplierArray as $supplierId => $locationArray) {
                                foreach ($locationArray as $locationId => $layersArray) {
                                    foreach ($layersArray as $layersId => $batchArray) {
                                        foreach ($batchArray as $batchId => $quantityUpdate) {
                                            if ($quantityUpdate > 0) {
                                                \common\helpers\OrderProduct::doQuoteSpecific($opRecord, $quantityUpdate, $warehouseId, $supplierId, $locationId, $layersId, $batchId);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        \common\helpers\OrderProduct::doQuote($opRecord, (int)Yii::$app->request->post('evaluation_state_reset_cancel', 0));
                    }
                } elseif ($opStatus == \common\helpers\OrderProduct::OPS_RECEIVED) {
                    if (is_array($opUpdateArray)) {
                        foreach (\common\helpers\OrderProduct::getAllocatedArray($opRecord, true) as $opaRecord) {
                            if (isset($opUpdateArray[$opaRecord['warehouse_id']][$opaRecord['suppliers_id']][$opaRecord['location_id']][$opaRecord['layers_id']][$opaRecord['batch_id']])
                                AND (int)$opUpdateArray[$opaRecord['warehouse_id']][$opaRecord['suppliers_id']][$opaRecord['location_id']][$opaRecord['layers_id']][$opaRecord['batch_id']] < (int)$opaRecord['allocate_received']
                            ) {
                                \common\helpers\OrderProduct::doAllocateSpecific($opRecord, $opUpdateArray[$opaRecord['warehouse_id']][$opaRecord['suppliers_id']][$opaRecord['location_id']][$opaRecord['layers_id']][$opaRecord['batch_id']], $opaRecord['warehouse_id'], $opaRecord['suppliers_id'], $opaRecord['location_id'], $opaRecord['layers_id'], $opaRecord['batch_id']);
                                unset($opUpdateArray[$opaRecord['warehouse_id']][$opaRecord['suppliers_id']][$opaRecord['location_id']][$opaRecord['layers_id']][$opaRecord['batch_id']]);
                            }
                        }
                        unset($opaRecord);
                        foreach ($opUpdateArray as $warehouseId => $supplierArray) {
                            foreach ($supplierArray as $supplierId => $locationArray) {
                                foreach ($locationArray as $locationId => $layersArray) {
                                    foreach ($layersArray as $layersId => $batchArray) {
                                        foreach ($batchArray as $batchId => $quantityUpdate) {
                                            \common\helpers\OrderProduct::doAllocateSpecific($opRecord, $quantityUpdate, $warehouseId, $supplierId, $locationId, $layersId, $batchId);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($opStatus == \common\helpers\OrderProduct::OPS_DISPATCHED) {
                    if (is_array($opUpdateArray)) {
                        foreach ($opUpdateArray as $warehouseId => $supplierArray) {
                            foreach ($supplierArray as $supplierId => $locationArray) {
                                foreach ($locationArray as $locationId => $layersArray) {
                                    foreach ($layersArray as $layersId => $batchArray) {
                                        foreach ($batchArray as $batchId => $quantityUpdate) {
                                            \common\helpers\OrderProduct::doDispatchSpecific($opRecord, $quantityUpdate, $warehouseId, $supplierId, $locationId, $layersId, $batchId);
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        \common\helpers\OrderProduct::doDispatch($opRecord, (int)Yii::$app->request->post('evaluation_state_force', 0));
                    }
                } elseif ($opStatus == \common\helpers\OrderProduct::OPS_DELIVERED) {
                    if (is_array($opUpdateArray)) {
                        foreach ($opUpdateArray as $warehouseId => $supplierArray) {
                            foreach ($supplierArray as $supplierId => $locationArray) {
                                foreach ($locationArray as $locationId => $layersArray) {
                                    foreach ($layersArray as $layersId => $batchArray) {
                                        foreach ($batchArray as $batchId => $quantityUpdate) {
                                            \common\helpers\OrderProduct::doDeliverSpecific($opRecord, $quantityUpdate, $warehouseId, $supplierId, $locationId, $layersId, $batchId);
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        \common\helpers\OrderProduct::doDeliver($opRecord, (int)Yii::$app->request->post('evaluation_state_force', 0));
                    }
                } elseif ($opStatus == \common\helpers\OrderProduct::OPS_CANCELLED) {
                    if (is_array($opUpdateArray)) {
                        foreach ($opUpdateArray as $warehouseId => $supplierArray) {
                            foreach ($supplierArray as $supplierId => $locationArray) {
                                foreach ($locationArray as $locationId => $layersArray) {
                                    foreach ($layersArray as $layersId => $batchArray) {
                                        foreach ($batchArray as $batchId => $quantityUpdate) {
                                            $quantityReceived = \common\helpers\OrderProduct::getReceived($opRecord, true);
                                            if ($quantityUpdate < 0) {
                                                $quantityUpdate = 0;
                                            }
                                            if ($quantityUpdate > ($opRecord->products_quantity - $quantityReceived)) {
                                                $quantityUpdate = ($opRecord->products_quantity - $quantityReceived);
                                            }
                                            $opRecord->qty_cnld = $quantityUpdate;
                                            try {
                                                $opRecord->save();
                                            } catch (\Exception $exc) {}
                                            \common\helpers\OrderProduct::evaluate($opRecord);
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        \common\helpers\OrderProduct::doCancel($opRecord, (int)Yii::$app->request->post('evaluation_state_restock', 0));
                    }
                }
                if ($opStatusValue != (int)$opRecord->orders_products_status) {
                    $opStatusManual = false;
                }
            }
            $oStatus = \common\helpers\Order::evaluate($opRecord->orders_id);
            if (($commentary != '') OR ($opStatusManual != $opStatusManualValue) OR ($opStatusValue != (int)$opRecord->orders_products_status)) {
                if ($opStatusManual !== false) {
                    $opRecord->orders_products_status_manual = $opStatusManual;
                    try {
                        $opRecord->save();
                    } catch (\Exception $exc) {
                        $opRecord->orders_products_status_manual = $opStatusManualValue;
                    }
                }
                $opshRecord = new \common\models\OrdersProductsStatusHistory();
                $opshRecord->orders_id = (int)$opRecord->orders_id;
                $opshRecord->orders_products_id = (int)$opRecord->orders_products_id;
                $opshRecord->orders_products_status_id = (int)$opRecord->orders_products_status;
                $opshRecord->orders_products_status_manual_id = (int)$opRecord->orders_products_status_manual;
                $opshRecord->comments = $commentary;
                $opshRecord->admin_id = (int)$login_id;
                $opshRecord->date_added = date('Y-m-d H:i:s');
                try {
                    $opshRecord->save();
                } catch (\Exception $exc) {}
                unset($opshRecord);
            }
            unset($opStatusManualValue);
            unset($opStatusManual);
            unset($opStatusValue);
        }

        if (Yii::$app->request->isAjax) {
            $qty_dfct = 0;
            $qty_cnld = 0;
            $qty_rcvd = 0;
            $qty_dspd = 0;
            $qty_dlvd = 0;
            $opsStatus = '';
            $opsmStatus = '';
            $opsColour = '#000000';
            $opsmColour = '#000000';
            if ($opRecord instanceof \common\models\OrdersProducts) {
                $qty_dfct = \common\helpers\OrderProduct::getStockDeficit($opRecord);
                $qty_cnld = $opRecord->qty_cnld;
                $qty_rcvd = $opRecord->qty_rcvd;
                $qty_dspd = $opRecord->qty_dspd;
                $qty_dlvd = $opRecord->qty_dlvd;
                $opsRecord = \common\models\OrdersProductsStatus::findOne([
                    'orders_products_status_id' => $opRecord->orders_products_status,
                    'language_id' => (int)$languages_id
                ]);
                if ($opsRecord instanceof \common\models\OrdersProductsStatus) {
                    $opsStatus = $opsRecord->orders_products_status_name;
                    $opsColour = $opsRecord->getColour();
                }
                unset($opsRecord);
                $opsmRecord = \common\models\OrdersProductsStatusManual::findOne([
                    'orders_products_status_manual_id' => $opRecord->orders_products_status_manual,
                    'language_id' => (int)$languages_id
                ]);
                if ($opsmRecord instanceof \common\models\OrdersProductsStatusManual) {
                    $opsmStatus = $opsmRecord->orders_products_status_manual_name;
                    $opsmColour = $opsmRecord->getColour();
                }
                unset($opsmRecord);
            }
            $opArray = [(int)$opRecord->orders_products_id => [
                'qty_dfct' => $qty_dfct,
                'qty_cnld' => $qty_cnld,
                'qty_rcvd' => $qty_rcvd,
                'qty_dspd' => $qty_dspd,
                'qty_dlvd' => $qty_dlvd,
                'ops' => [
                    'status' => $opsStatus,
                    'colour' => $opsColour
                ],
                'opsm' => [
                    'status' => $opsmStatus,
                    'colour' => $opsmColour
                ],
                'prid' => (int)$opRecord->products_id
            ]];
            $oppRecord = \common\helpers\OrderProduct::getParent($opRecord, false);
            if ($oppRecord instanceof \common\models\OrdersProducts) {
                $oppsStatus = '';
                $oppsmStatus = '';
                $oppsColour = '#000000';
                $oppsmColour = '#000000';
                $oppsRecord = \common\models\OrdersProductsStatus::findOne([
                    'orders_products_status_id' => $oppRecord->orders_products_status,
                    'language_id' => (int)$languages_id
                ]);
                if ($oppsRecord instanceof \common\models\OrdersProductsStatus) {
                    $oppsStatus = $oppsRecord->orders_products_status_name;
                    $oppsColour = $oppsRecord->getColour();
                }
                unset($oppsRecord);
                $oppsmRecord = \common\models\OrdersProductsStatusManual::findOne([
                    'orders_products_status_manual_id' => $oppRecord->orders_products_status_manual,
                    'language_id' => (int)$languages_id
                ]);
                if ($oppsmRecord instanceof \common\models\OrdersProductsStatusManual) {
                    $oppsmStatus = $oppsmRecord->orders_products_status_manual_name;
                    $oppsmColour = $oppsmRecord->getColour();
                }
                unset($oppsmRecord);
                $opArray[(int)$oppRecord->orders_products_id] = [
                    'qty_dfct' => \common\helpers\OrderProduct::getStockDeficit($oppRecord),
                    'qty_cnld' => $oppRecord->qty_cnld,
                    'qty_rcvd' => $oppRecord->qty_rcvd,
                    'qty_dspd' => $oppRecord->qty_dspd,
                    'qty_dlvd' => $oppRecord->qty_dlvd,
                    'ops' => [
                        'status' => $oppsStatus,
                        'colour' => $oppsColour
                    ],
                    'opsm' => [
                        'status' => $oppsmStatus,
                        'colour' => $oppsmColour
                    ],
                    'prid' => (int)$oppRecord->products_id
                ];
            }
            unset($oppRecord);
            foreach ($opArray as &$opInformation) {
                $opInformation['qty_dfct'] = \common\helpers\Product::getVirtualItemQuantity($opInformation['prid'], $opInformation['qty_dfct']);
                $opInformation['qty_cnld'] = \common\helpers\Product::getVirtualItemQuantity($opInformation['prid'], $opInformation['qty_cnld']);
                $opInformation['qty_rcvd'] = \common\helpers\Product::getVirtualItemQuantity($opInformation['prid'], $opInformation['qty_rcvd']);
                $opInformation['qty_dspd'] = \common\helpers\Product::getVirtualItemQuantity($opInformation['prid'], $opInformation['qty_dspd']);
                $opInformation['qty_dlvd'] = \common\helpers\Product::getVirtualItemQuantity($opInformation['prid'], $opInformation['qty_dlvd']);
            }
            unset($opInformation);
            echo json_encode([
                'status' => 'ok',
                'op' => $opArray,
                'os' => [
                    'status' => $oStatus
                ]
            ]);
        } else {
            $url = Url::to(['orders/process-order', 'orders_id' => $data['orders_id']]);
            return $this->redirect($url);
        }
    }

    public function actionSendRequest() {
        $orders_id = Yii::$app->request->get('orders_id');

        \common\helpers\Translation::init('admin/recover_cart_sales');
        \common\helpers\Translation::init('admin/manufacturers');

        $manager = \common\services\OrderManager::loadManager();
        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);

        $platform_config = Yii::$app->get('platform')->config($order->info['platform_id']);
        $platform_config->constant_up();

        $message = ['type' => 'danger', 'text' => WARN_UNKNOWN_ERROR];

        $customer_id = $order->customer['customer_id'];
        if ($customer_id){

            $manager->assignCustomer($customer_id);
            $currencies = Yii::$container->get('currencies');

            $totals = \yii\helpers\ArrayHelper::map($order->totals, 'class', 'value_inc_tax');

            $ot_paid_value = $totals['ot_paid'];
            $ot_total_value = $totals['ot_total'];

            $paid = $manager->getTotalCollection()->get('ot_paid');

            $update_and_pay_amount = round($ot_total_value, 2) - round($ot_paid_value, 2);

            $customer = $manager->getCustomersIdentity();
            $customer->updateUserToken();

            $token = $customer->getCustomersInfo()->getToken();

            if ($order->customer['email_address']){
                $STORE_NAME = $platform_config->const_value('STORE_NAME');
                $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

                $email_params = [];
                $email_params['STORE_NAME'] = $STORE_NAME;
                $email_params['CUSTOMER_NAME'] = $order->customer['firstname'] . ' ' . $order->customer['lastname'];
                $email_params['ORDER_NUMBER'] = method_exists($order, 'getOrderNumber')?$order->getOrderNumber():$order->order_id;
                $email_params['REQUEST_MESSAGE'] = $currencies->format(abs($update_and_pay_amount));
                $email_params['REQUEST_URL'] = tep_catalog_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'action=payment_request&order_id=' . $order->order_id . '&email_address=' . $order->customer['email_address'] . '&token=' . $token, 'SSL', false);

                $email_params['CUSTOMER_FIRSTNAME'] = $order->customer['firstname'];
                $email_params['ORDER_DATE_LONG'] = strftime(DATE_FORMAT_LONG);
                $email_params['ORDER_DATE_LONG'] = strftime(DATE_FORMAT_LONG);
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('DelayedDespatch', 'allowed')) {
                    $email_params['ORDER_DATE_LONG'] .= $ext::mailInfo($order->info['delivery_date']);
                }
                $email_params['PRODUCTS_ORDERED'] = $order->getProductsHtmlForEmail();

                $email_params['ORDER_TOTALS'] = '';
                $order_total_output = $manager->getTotalOutput(true, 'TEXT_EMAIL');

                $email_params['ORDER_TOTALS'] = \frontend\design\boxes\email\OrderTotals::widget(['params' => ['order_total_output' => $order_total_output , 'platform_id' => $order->info['platform_id']]]);
                $email_params['BILLING_ADDRESS'] = \common\helpers\Address::address_format($order->billing['format_id'],$order->billing,0, '', "<br>");
                $email_params['DELIVERY_ADDRESS'] = '';
                if($order->content_type != 'virtual'){
                    $email_params['DELIVERY_ADDRESS'] = \common\helpers\Address::address_format($order->delivery['format_id'],$order->delivery,0, '', "<br>");
                    [$class, $method] = explode('_', $order->info['shipping_class']);
                    $shipping = $manager->getShippingCollection()->get($class);
                    if (is_object($shipping)) {
                        $collect = $shipping->toCollect($method);
                        if ($collect && method_exists($shipping, 'getAdditionalOrderParams')) {
                            $email_params['DELIVERY_ADDRESS'] = $shipping->getAdditionalOrderParams([], $order->order_id, $order->table_prefix);
                        }
                    }
                }
                $email_params['PAYMENT_METHOD'] = $order->info['payment_method'];
                $email_params['SHIPPING_METHOD'] = $order->info['shipping_method'];

                [$email_subject, $email_text] = \common\helpers\Mail::get_parsed_email_template('Request for payment', $email_params, -1, $order->info['platform_id']);
                \common\helpers\Mail::send(
                        $order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], $email_subject, $email_text, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS,
                    [],
                    '',
                    '',
                    ['add_br' => 'no', 'platform_id' => $order->info['platform_id']]
                );
                //add message to history
                $message = ['type' => 'success', 'text' => NOTICE_EMAILS_SENT];
                $order->addLegend('Sent payment Request', $_SESSION['login_id']);
                Yii::$app->get('storage')->removeAll();
            }

        } else {
            $message['text'] = ERROR_NO_CUSTOMER_SELECTED;
        }

        echo json_encode($message);
        exit();
    }

/**
 *
 * @return string
 */

    public function actionExchangeStateSwitch()
    {
        $this->layout = false;
        $orderId = Yii::$app->request->post('order_id',0);
        $directoryId = Yii::$app->request->post('directory_id',0);
        /**
         *  -1 - disable // add record to tracking table with -1 (incorrect) external id
         *   0 - export again  // clean up tracking table
         *   1 - exported
         *   2 - export error
         */
        $new_state = Yii::$app->request->post('new_state');
        if ( is_numeric($new_state) && in_array((int)$new_state, [0,-1, 2]) ) {
            if ( (int)$new_state==0 ) {
              tep_db_query("DELETE FROM ep_holbi_soap_link_orders WHERE local_orders_id='" . (int)$orderId . "' and ep_directory_id='" . (int)$directoryId . "'");
              tep_db_query("DELETE FROM ep_order_issues WHERE orders_id='" . (int)$orderId . "' and ep_directory_id='" . (int)$directoryId . "'");
            } elseif ( (int)$new_state==-1 ) {
              tep_db_query("DELETE FROM ep_holbi_soap_link_orders WHERE local_orders_id='" . (int)$orderId . "' and ep_directory_id='" . (int)$directoryId . "'");
              $d = [
                'local_orders_id' => (int)$orderId,
                'remote_orders_id' => -1,
                'track_remote_order' => 0,
                'ep_directory_id' => (int)$directoryId,
                ];
              tep_db_perform('ep_holbi_soap_link_orders', $d);
            }
            return 'ok';
        }
        return 'fail';
    }

    public function actionExchangeExportNow()
    {
        $this->layout = false;
        \common\helpers\Translation::init('admin/easypopulate');
        $result = ['status' => 'fail'];
        $orderIds = Yii::$app->request->post('order_id');
        $directoryId = Yii::$app->request->post('directory_id');
        //sleep(1);

        $orderIds = !is_array($orderIds)?[$orderIds]:$orderIds;
        $orderIds = array_unique(array_map('intval',$orderIds));

        if ( count($orderIds)==0 ) {
            $result['messages'][] = 'Orders not selected';
        } else {

          ob_start();
          $epDirectory = \backend\models\EP\Directory::loadById($directoryId);
          $providerName = $epDirectory->directory_config[0]['file_format'];
          $jobId = $epDirectory->touchImportJob($providerName . '_ExportOrders_'.date('YmdHis'),'configured', $providerName . '\\ExportOrders');
          $exportOrderJob = \backend\models\EP\Job::loadById($jobId);

          if ( $exportOrderJob ) {
            if ( !is_array($exportOrderJob->job_configure) ) $exportOrderJob->job_configure = [];
            $exportOrderJob->job_configure['oneTimeJob'] = true;
            $exportOrderJob->job_configure['forceProcessOrders'] = $orderIds;
            $exportOrderJob->saveConfigureState();
            $exportOrderJob->setJobStartTime(time());
            $messages = new Messages([
                'job_id' => $jobId,
                'output' => 'db',
            ]);
            ob_start();
            try {
                $messages->info('Run export manually');
                $exportOrderJob->run($messages);

                $result['status'] = 'ok';
                $result['messages'] = $messages->getMessages();
            }catch (\Exception $ex){
                $result['messages'][] = $ex->getMessage();
            }
            ob_end_flush();
            $exportOrderJob->jobFinished();
          }
          ob_get_clean();

          Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
          Yii::$app->response->data = $result;

          if ( count($orderIds) == 1 ) {
             Yii::$app->response->data = array_merge(Yii::$app->response->data, ['exchange_info_block' => self::renderExchangeInfo($directoryId, $orderIds[0], true)]);
          }
        }
    }

    public static function renderExchangeInfo($directoryId, $orderId, $skipHeader = false)
    {
        $issues = '';
        $remoteId = $status = 0;
        $directoryId = intval($directoryId);
        $orderId = intval($orderId);
        $get_issues_r = tep_db_query(
            "SELECT epo.*, oi.status, oi.date_added, oi.issue_text " .
            "FROM ep_holbi_soap_link_orders epo left join ep_order_issues oi on oi.orders_id='" . (int)$orderId . "' and oi.ep_directory_id='" . (int)$directoryId . "' " .
            "WHERE epo.local_orders_id='" . (int)$orderId . "' and epo.ep_directory_id='" . (int)$directoryId . "' and cfg_export_as = 'order'" .
            "ORDER BY oi.date_added DESC ".
            "LIMIT 4"
        );

        while ($get_issue = tep_db_fetch_array($get_issues_r)) {
          if ($issues=='') {
            $remoteId = $get_issue['remote_orders_id'];
            if (!empty($get_issue['remote_order_number'])) {
              $remoteId = $get_issue['remote_order_number'];
            } elseif (!empty($get_issue['remote_guid']) && $remoteId==$orderId) {
              $remoteId = $get_issue['remote_guid'];
            }

            $status = $get_issue['status'];
            $exportDate = \common\helpers\Date::datetime_short($get_issue['date_added']);
          }

          if (!empty($get_issue['issue_text'])) {
            if ($issues=='') {
              $issues .= "<span>Export issues:</span>";
              $issues .= '<ol style="padding: 0 0 0 16px" class="js-exchange_try_again' . $directoryId . '">';
            }
            $issues .= "<li style=\"padding: 0; \"> " . $get_issue['issue_text'] . ' (' . \common\helpers\Date::datetime_short($get_issue['date_added']) . ")</li>";
          }
        }
        if ($issues!='') {
          $issues .= '</ol>';
        }

        if ( $remoteId >= 0 ) {
          $epDirectory = \backend\models\EP\Directory::loadById($directoryId);

          if (!$skipHeader) {
            $info = '<div class="cr-ord-cust cr-ord-cust-datasource" id="jsBlkExchangeInfo' . $directoryId . '">';
          } else {
            $info = '';
          }
          $info .= '<span>' . $epDirectory->directory . '</span>';
          if ( $remoteId > 0 ) {
            $info .= '<div>';
            $info .= TEXT_EXTERNAL_ORDERS_ID . ' ' . $remoteId . '<br />';
            $info .= TEXT_DATE_ADDED . ' ' . $exportDate;
            $info .= '</div>';
          }

          if ($status != 1){
            //$info .= '<div class="cr-ord-cust cr-ord-cust-client-order-id" id="jsBlkSapInfo">';
            //$info .= '<span>'.TEXT_SAP_HEADING.'</span>';
            //echo '<p style="display:block;" class="">' . TEXT_SAP_EXPORT_MODE.' '.($order->info['sap_export_mode']=='auto'?TEXT_SAP_EXPORT_AUTO:TEXT_SAP_EXPORT_MANUAL).'</p>';
            $info .= '<p><button type="button" class="btn btn-1" id="js-exchange-export' . $directoryId . '" >'.TEXT_EXPORT.'</button></p>';
            if ( $status==2 ) {
              $info .= '<p style="display:block;" class="js-exchange_try_again' . $directoryId . '">' . TEXT_ERROR_INTRO . ' <button type="button" id="exchange_try_again' . $directoryId . '" class="btn btn-2">' . IMAGE_RESET . '</button><br><small style="opacity:0.8;">' . TEXT_RESET_ERROR_NOTE . '</small></p>';
            }
            $info .= "<div style=\"padding: 0;margin: 0; font-weight: inherit; font-size: inherit; line-height: inherit;\" id=\"jsBlkSapIssues" . $directoryId . "\">{$issues}</div>";

            $info .= '<p '.($status==2?' style="display:none;" ':'').' class="js-exchange_on_off' . $directoryId . '"><input type="checkbox" '.($status==-1?' checked="checked" ':'').' value="1" id="exchange_export_switch' . $directoryId . '"> '.TEXT_DISABLE_EXPORT.'</p>';
            if (!$skipHeader) {
              $info .= "</div>";
            }

            ob_start();
            ?>
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#js-exchange-export<?php echo $directoryId ?>').on('click',function () {
                        bootbox.dialog({
                            message: '<div id="exchangeExportResult">Export in progress...</div>',
                            title: "<?php echo $epDirectory->directory ?>",
                            buttons: {
                                done:{
                                    label: "<?php echo TEXT_BTN_OK; ?>",
                                    className: "btn-cancel"
                                }
                            }
                        })
                        .on('shown.bs.modal', function(){
                           $.post(
                              "<?php echo Yii::$app->urlManager->createUrl('orders/exchange-export-now');?>",
                              [ {name:'order_id', value:'<?php echo $orderId; ?>'}, {name:'directory_id', value:'<?php echo $directoryId; ?>'}, {name:'page',value:'order-detail'} ],
                              function(data, status){
                                  if ( data.exchange_info_block && data.exchange_info_block != 'null') {
                                      $('#jsBlkExchangeInfo<?php echo $directoryId ?>').html(data.exchange_info_block);
                                  }
                                  /*if ( data.exchange_export_issues ) {
                                      $('#jsBlkExchangeIssues<?php echo $directoryId ?>').html(data.exchange_export_issues);
                                  }*/

                                  $('#exchangeExportResult').html('Complete');
                                  if ( data.messages && data.messages.length ) {
                                      $('#exchangeExportResult').html('');
                                      for(var i=0; i<data.messages.length;i++){
                                          $('#exchangeExportResult').append('<div>'+data.messages[i]+'</div>');
                                      }
                                  }
                              },
                              'json'
                          );

                        });
                    });
                    if (typeof setNewState !== 'function') {
                      setNewState = function(newState, onComplete, directoryId){
                          $.post(
                              "<?php echo Yii::$app->urlManager->createUrl('orders/exchange-state-switch');?>",
                              [ {name:'order_id', value:'<?php echo $orderId; ?>'}, {name:'directory_id', value:directoryId}, {name:'new_state', value:newState} ],
                              function(data, status){
                                  if ( data=='ok' )
                                      $('.js-exchange_try_again' + directoryId).remove();
                                  if ( typeof onComplete === 'function' ) onComplete(data);
                              }
                          );
                      };
                    }

                    $('#exchange_export_switch<?php echo $directoryId?>').bootstrapSwitch({
                        onText: "<?php echo defined('SW_ON')?SW_ON:'';?>",
                        offText: "<?php echo defined('SW_OFF')?SW_OFF:'';?>",
                        onSwitchChange: function () {
                            if($(this).is(':checked')){
                                setNewState(-1, '', '<?php echo $directoryId?>');
                            }else{
                                setNewState(0, '', '<?php echo $directoryId?>');
                            }
                            bootbox.alert('<?php echo str_replace(["'","\n"],["\'",'\n'],TEXT_EXCHANGE_SWITCH_UPDATED); ?>');
                        }
                    });
                    $('#exchange_try_again<?php echo $directoryId?>').on('click', function(){
                        setNewState(0, function(data){
                            if ( data=='ok' ) {
                                $('.js-exchange_on_off<?php echo $directoryId?>').show();
                                bootbox.alert('<?php echo str_replace(["'","\n"],["\'",'\n'],TEXT_EXCHANGE_RESET_ERROR_OK); ?>');
                            }
                        }, '<?php echo $directoryId?>');
                    });
                });
            </script>
            <?php
            $info .=  ob_get_clean();
          }

        }
        return $info;
    }

    public function actionPrintLabel()
    {
        $this->view->errors = [];

        \common\helpers\Translation::init('admin/orders');
        \common\helpers\Translation::init('shipping');

        $orders_label_id = \Yii::$app->request->get('orders_label_id', 0);
        $orders_id = \Yii::$app->request->get('orders_id', 0);
        if ($orders_label_id > 0) {
            $oLabel = \common\models\OrdersLabel::findOne(['orders_label_id' => $orders_label_id, 'orders_id' => $orders_id]);
            if ($oLabel) {
                [$label_module, $label_method] = array_pad( explode('_', $oLabel->label_class, 2), 2, null);
                if ($label_module && $label_method) {
                    $class = "common\\modules\\label\\" . $label_module;
                    if (class_exists($class) && is_subclass_of($class, ModuleLabel::class)) {
                        $label = new $class;
                        if ($label->withoutSettings($oLabel)) {
                            $orders_label_id = 0;
                            $oLabel->delete();
                        }
                    }
                }
            }
        }
        $action = \Yii::$app->request->get('action', '');
        $new_module_method = \Yii::$app->request->get('method', '');
        $all_methods = \Yii::$app->request->get('all_methods', 1);

        [$new_module, $new_method] = array_pad(explode('_', $new_module_method, 2), 2, null);

        $delivery_date = \Yii::$app->request->get('delivery_date', '');
        if ($action == 'set_delivery') {
            $delivery_date = \common\helpers\Date::checkInputDate($delivery_date, false);
            $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $delivery_date);
            tep_db_query("update " . TABLE_ORDERS . " set delivery_date ='" . $date->format('Y-m-d') . "' where orders_id = '" . (int) $orders_id . "'");
            $manager = \common\services\OrderManager::loadManager();
            $order = $manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);
            $order->addLegend('Invoice printed', $_SESSION['login_id']);
            unset($order);
            unset($manager);
        }
        if ($orders_label_id > 0 && !empty($new_module) && !empty($new_method)) {
            $oLabel = \common\models\OrdersLabel::findOne(['orders_label_id' => $orders_label_id, 'orders_id' => $orders_id]);
            $oLabel->label_class = $new_module_method;
            $oLabel->save();
        }
        if ($action == 'save_label_products') {
            $selected_products = Yii::$app->request->get('selected_products', []);
            $selected_products_qty = Yii::$app->request->get('selected_products_qty', []);
            $selected_products_qty_max = Yii::$app->request->get('selected_products_qty_max', []);
            if ($orders_label_id > 0) {
                //$oLabel = \common\models\OrdersLabel::findOne(['orders_label_id' => $orders_label_id, 'orders_id' => $orders_id]);
            } else {
                $oLabel = new \common\models\OrdersLabel();
                $oLabel->orders_id = $orders_id;
                $oLabel->insert();
                $orders_label_id = $oLabel->orders_label_id;
            }
            Yii::$app->db->createCommand()->delete(TABLE_ORDERS_LABEL_TO_ORDERS_PRODUCTS, ['orders_label_id' => $orders_label_id, 'orders_id' => $orders_id])->execute();
            foreach ($selected_products as $orders_products_id) {
                $selected_qty = min($selected_products_qty[$orders_products_id], $selected_products_qty_max[$orders_products_id]);
                if ($selected_qty > 0) {
                    Yii::$app->db->createCommand()->insert(TABLE_ORDERS_LABEL_TO_ORDERS_PRODUCTS, ['orders_label_id' => $orders_label_id, 'orders_id' => $orders_id, 'orders_products_id' => $orders_products_id, 'products_quantity' => $selected_qty])->execute();
                }
            }
        }

        $manager = \common\services\OrderManager::loadManager();
        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);
        Yii::$app->get('platform')->config($order->info['platform_id'])->constant_up();
        $manager->set('platform_id', $order->info['platform_id']);

        [$module, $method] = explode('_', $order->info['shipping_class']);

        $shipping_modules = $manager->getShippingCollection();
        $shipping = $shipping_modules->get($module);

        $this->view->methods = [];

        if ($order->canBeDelivered() /*|| (is_object($shipping) && $shipping->hasLabelModule())*/) {
            if (method_exists($shipping, 'checkDeliveryDate')) {
                if ($shipping->needDeliveryDate() && false === $shipping->checkDeliveryDate($order->info['delivery_date'])) {
                    return $this->renderPartial('print-label-date.tpl', [
                        'orders_id' => $orders_id,
                        'all_methods' => $all_methods,
                    ]);
                }
            }

            if ($orders_label_id > 0) {
                $oLabel = \common\models\OrdersLabel::findOne(['orders_label_id' => $orders_label_id, 'orders_id' => $orders_id]);
                [$label_module, $label_method] = array_pad( explode('_', $oLabel->label_class??'', 2), 2, '');
                if (!empty($label_module) && !empty($label_method)) {
                    $class = "common\\modules\\label\\" . $label_module;
                    if (class_exists($class) && is_subclass_of($class, "common\\classes\\modules\\ModuleLabel")) {
                        $label = new $class;

                        if ($action == 'delete' && !$label->shipment_exists($orders_id, $orders_label_id)) {
                            $oLabel->delete();
                            echo '<script type="text/javascript"> setTimeout(function(){ $.get("' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders_id]) . '", function(data) { $("#order_management_data").html(data.content); },"json"); },100); </script>';
                            $this->view->errors = array('The label has been canceled.');
                            $this->layout = false;
                            return $this->render('print-label.tpl', ['orders_id' => $orders_id]);
                            exit;
                        }

                        if ($action == 'cancel' && $label->shipment_exists($orders_id, $orders_label_id)) {
                            $result = $label->cancel_shipment($orders_id, $orders_label_id);
                            if (tep_not_null($result['success'])) {
                                echo '<script type="text/javascript"> setTimeout(function(){ $.get("' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders_id]) . '", function(data) { $("#order_management_data").html(data.content); },"json"); },100); </script>';
                                $this->view->errors = array($result['success']);
                            } elseif (is_array($result['errors']) && count($result['errors']) > 0) {
                                $this->view->errors = $result['errors'];
                            }
                            $this->layout = false;
                            return $this->render('print-label.tpl', ['orders_id' => $orders_id]);
                            exit;
                        }

                        $methods = $label->get_methods($order->delivery['country']['iso_code_2'], $label_method, $order->info['shipping_weight'], method_exists($shipping, 'calc_order_num_of_sheets') ? $shipping->calc_order_num_of_sheets($orders_id) : '');
                        if (isset($methods[$label_module . '_' . $label_method])) {
                            if ($action == 'update' && $label->shipment_exists($orders_id, $orders_label_id)) {
                                $result = $label->update_shipment($orders_id, $orders_label_id);
                            } else {
                                $result = $label->create_shipment($orders_id, $orders_label_id, $label_method);
                                $order->addLegend('Print Label created', $_SESSION['login_id']);
                            }
                            if ($ext = \common\helpers\Acl::checkExtensionAllowed('Ebay', 'allowed')) {
                                $ext::setUpdateOrder($orders_id);
                            }
                            if ($ext = \common\helpers\Acl::checkExtensionAllowed('Amazon', 'allowed')) {
                                $ext::setUpdateOrder($orders_id);
                            }
                            if (tep_not_null($result['tracking_number']) && Yii::$app->request->isAjax) {
                                echo '<script type="text/javascript"> setTimeout(function(){ $.get("' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders_id]) . '", function(data) { $("#order_management_data").html(data.content); },"json"); },100); </script>';
                            }
                            if (tep_not_null($result['parcel_label'])) {
                                if (Yii::$app->request->isAjax) {
                                    if ($result['parcel_label_format'] == 'vnd.zebra-zpl') {
                                        echo $this->renderPartial('label/label-zpl.tpl',[
                                            'orders_id' => $orders_id,
                                            'orders_label_id' => $orders_label_id,
                                            'parcel_label' => $result['parcel_label'],
                                        ]);
                                        exit;
                                    }
                                    echo $this->renderPartial('label/label-info.tpl',[
                                        'text' => TEXT_PLEASE_WAIT,
                                    ]);
                                    echo '<script type="text/javascript">
                                                var pop = $(".pop-up-close:last");
                                                pop.on("click", function() {
                                                  $.get("' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders_id]) . '", function(data) { $("#order_management_data").html(data.content); },"json");
                                                } );
                                                window.location.href = "' . \Yii::$app->urlManager->createUrl(['orders/print-label', 'orders_id' => $orders_id, 'orders_label_id' => $orders_label_id]) . '";
                                                pop.trigger("click");
                                          </script>';
                                    // echo '<script type="text/javascript"> setTimeout(function(){ $.get("' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders_id]) . '", function(data) { $("#order_management_data").html(data.content); },"json"); },100); </script>';
                                } elseif ($result['parcel_label_format'] == 'html') {
                                    header('Content-type: text/html');
                                    header('Content-disposition: attachment; filename=parcel_label_' . $orders_id . '.html');
                                    echo $result['parcel_label'];
                                } elseif ($result['parcel_label_format'] == 'vnd.eltron-epl') {
                                    header('Content-type: text/vnd.eltron-epl');
                                    header('Content-disposition: attachment; filename=parcel_label_' . $orders_id . '.epl');
                                    echo $result['parcel_label'];
                                } elseif ($result['parcel_label_format'] == 'vnd.zebra-zpl') {
                                    header('Content-type: text/vnd.zebra-zpl');
                                    header('Content-disposition: attachment; filename=parcel_label_' . $orders_id . '.zpl');
                                    echo $result['parcel_label'];
                                } else {
                                    header('Content-type: application/pdf');
                                    header('Content-disposition: attachment; filename=parcel_label_' . $orders_id . '.pdf');
                                    echo $result['parcel_label'];
                                }
                                exit;
                            } else {
                                /*if (is_array($result['errors']) && count($result['errors']) > 0) {
                                    $this->view->errors = $result['errors'];
                                }/**/
                                echo $this->renderPartial('label/label-info.tpl',[
                                    'text' => is_array($result['errors']) ? implode('<br>', $result['errors']) : $result['errors'],
                                ]);
                                echo '<script type="text/javascript">
                                                var pop = $(".pop-up-close:last");
                                                pop.on("click", function() {
                                                  $.get("' . \Yii::$app->urlManager->createUrl(['orders/process-order', 'orders_id' => $orders_id]) . '", function(data) { $("#order_management_data").html(data.content); },"json");
                                                } );
                                          </script>';
                                die();
                            }
                        }
                    }
                }
            } else {
                $orders_products = [];
                $__order_products = $order->products;
                if (ProductNameDecorator::instance()->useInternalNameForOrder()){
                    $__order_products = ProductNameDecorator::instance()->getUpdatedOrderProducts($__order_products, $order->info['language_id'], $order->info['platform_id']);
                }
                $__order_products = \common\helpers\Product::removeOrderSubProducts($__order_products);
                for ($i = 0, $n = sizeof($__order_products); $i < $n; $i++) {
                    $orders_products[$__order_products[$i]['orders_products_id']] = $__order_products[$i];
                    $orders_products[$__order_products[$i]['orders_products_id']]['selected'] = true;
                }
                $already_selected_products_query = (new \yii\db\Query())->select('orders_products_id, products_quantity')->from(TABLE_ORDERS_LABEL_TO_ORDERS_PRODUCTS)->where(['orders_id' => $orders_id])->all();
                foreach ($already_selected_products_query as $already_selected_products) {
                    if ($orders_products[$already_selected_products['orders_products_id']]['qty'] > $already_selected_products['products_quantity']) {
                        $orders_products[$already_selected_products['orders_products_id']]['qty'] -= $already_selected_products['products_quantity'];
                    } else {
                        unset($orders_products[$already_selected_products['orders_products_id']]);
                    }
                }
                $autoSendForm = false;
                if (is_array($orders_products) && count($orders_products) === 1) {
                    $product = array_values($orders_products)[0];
                    if (array_key_exists('qty',$product) && (int)$product['qty'] === 1) {
                        $autoSendForm = true;
                    }
                }
                return $this->renderPartial('print-label-products.tpl', [
                    'orders_id' => $orders_id,
                    'orders_label_id' => $orders_label_id,
                    'orders_products' => $orders_products,
                    'autoSendForm' => $autoSendForm
                ]);
            }

            $shippingLabels = [];
            if ( is_object($shipping) ) {
                $shippingLabels = $shipping->getPreferredLabels();
            }
            if ($all_methods || empty($shippingLabels)) {
                $labels = \common\helpers\Modules::getLabelsList($order->info['platform_id']);
                if ( count($shippingLabels)>0 ) {
                    $labels = array_unique(array_merge($shippingLabels,$labels));
                }
            }else{
                $labels = $shippingLabels;
            }

            $auto_selected_label = '';
            /** @var \common\extensions\ShippingCarrierPick\ShippingCarrierPick $ext */
            if ($ext = \common\helpers\Extensions::isAllowed('ShippingCarrierPick')) {
                $auto_selected_label = $ext::suggestOnOrder($order);
                if (!empty($auto_selected_label)) {
                    if (strpos($auto_selected_label, '_') !== false) {
                        [$auto_selected_label_class, $auto_selected_label_method] = explode('_', $auto_selected_label, 2);
                        if (!in_array($auto_selected_label_class, $labels)) $labels[] = $auto_selected_label_class;
                    } else {
                        $labels[] = $auto_selected_label;
                    }
                }
            }

            $_selectedAccordion = false;
            foreach ($labels as $class) {
                $namespaceModuleClass = "common\\modules\\label\\" . $class;
                if (class_exists($namespaceModuleClass) && is_subclass_of($namespaceModuleClass, "common\\classes\\modules\\ModuleLabel")) {
                    $label = new $namespaceModuleClass;

                    $methods = $label->get_methods(
                        $order->delivery['country']['iso_code_2'],
                        $new_method,
                        $order->info['shipping_weight'],
                        method_exists($shipping, 'calc_order_num_of_sheets') ? $shipping->calc_order_num_of_sheets($orders_id) : '',
                        $orders_label_id > 0 ? $orders_label_id : $oLabel->orders_label_id
                    );

                    $this->view->methods[] = [
                        'title' => $label->title,
                        'accordion' => strpos($auto_selected_label, $class.'_')===0,
                        'selected' => $auto_selected_label,
                        'methods' => $methods
                    ];
                    $_selectedAccordion = $_selectedAccordion || strpos($auto_selected_label, $class.'_')===0;
                }
            }
            if ( !$_selectedAccordion && count($this->view->methods)>0) $this->view->methods[0]['accordion'] = true;
        } else {
            $this->view->errors = array('The shipping module (' . $module . ') was not found.');
        }

        $this->layout = false;

        return $this->render('print-label.tpl', [
            'orders_id' => $orders_id,
            'orders_label_id' => $orders_label_id,
            'all_methods' => $all_methods,
            'hypashipTracking' => $result ?? null
        ]);
    }
    public function actionMakeOrderLabel()
    {
        Translation::init('admin/orders');

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        /** @var \common\extensions\ShippingCarrierPick\ShippingCarrierPick $ext */
        $ext = \common\helpers\Extensions::isAllowed('ShippingCarrierPick');
        if ( !$ext ) {
            Yii::$app->response->data = [
                'status' => 'error',
                'message' => 'Batch shipping label not allowed',
            ];
            return;
        }
        $orders_id = (int)Yii::$app->request->post('order_id',0);
        Yii::$app->response->data = $ext::makeOrderLabel($orders_id);
    }


    public function actionHoldOn()
    {
        \common\helpers\Translation::init('admin/orders');
        $this->layout = false;
        $orders_id = Yii::$app->request->get('orders_id',0);

        $orderModel = \common\models\Orders::findOne($orders_id);

        if ( Yii::$app->request->isPost ) {
            $hold_on_date = Yii::$app->request->post('hold_on_date','');
            if ( empty($hold_on_date) )
                $hold_on_date = null;

            $orderModel->setAttribute('hold_on_date',$hold_on_date);
            $updateHistory = false;
            if ( $orderModel->isAttributeChanged('hold_on_date') ) {
                $updateHistory = true;
            }
            $orderModel->save();
            $orderModel->refresh();
            if ( $updateHistory ) {
                $order = new \common\classes\Order($orders_id);
                if ( empty($hold_on_date) ) {
                    $order->addAdminComment('Hold on date cleared.', (int)$_SESSION['login_id']);
                }else{
                    $order->addAdminComment('Hold on date changed: ' . $hold_on_date, (int)$_SESSION['login_id']);
                }
            }
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'status' => 'ok',
                'hold_on_date' => $orderModel->hold_on_date,
            ];
            return;
        }

        return $this->render('hold-on.tpl',[
            'updateUrl' => \Yii::$app->urlManager->createUrl(['orders/hold-on', 'orders_id' => $orders_id]),
            'currentHoldOnDate' => $orderModel->hold_on_date,
        ]);
    }

/**
 * @deprecated
 * @return type
 */
    public function actionTransactions(){
        \common\helpers\Translation::init('admin/orders');
        $order_id  = Yii::$app->request->get('orders_id');
        if ($order_id){
          /** @var \common\services\OrderManager $manager */
            $manager = \common\services\OrderManager::loadManager();
            $order = $manager->getOrderInstanceWithId('\common\classes\Order', $order_id);
            $manager->setModulesVisibility(['admin']);

            Yii::$app->get('platform')->config($order->info['platform_id'])->constant_up();

            $manager->setRenderPath('\\backend\\design\\orders\\');

            $data = ['type' => 'full'];
            $format = 'html';
            $response = [];
            if (Yii::$app->request->isPost){
                $format = 'json';
                $_action = Yii::$app->request->post('action');
                if ($_action == 'get_children'){
                    $data = ['type' => 'children', 'statuses' => ['parent' => Yii::$app->request->post('parent')]];
                } else if ($_action == 'check_server_refunds'){
                    $data = ['type' => 'children'];
                    $orders_transactions = Yii::$app->request->post('orders_transactions');
                    if (!is_array($orders_transactions)){
                        $orders_transactions = [$orders_transactions];
                    }
                    $data['statuses'] = $manager->getTransactionManager()->getTransactionsStatus($orders_transactions);
                    echo json_encode($data);
                    exit();
                } else if ($_action == 'get_fields'){
                    $payment = $manager->getPaymentCollection()->get(Yii::$app->request->post('payment_class'), true);
                    if ($payment){
                        $tManager = $manager->getTransactionManager($payment);
                        return $manager->render('payments\PaymentFields', [ 'manager' => $manager , 'rules' => $tManager->getFields()], 'json');
                    }
                    exit();
                } else if ($_action == 'search_transactions'){
                    $class = Yii::$app->request->post('payment_class');
                    $payment = $manager->getPaymentCollection()->get($class, true);
                    if ($payment){
                        $tManager = $manager->getTransactionManager($payment);
                        if ($tManager->prepareQuery(Yii::$app->request->post())){
                            $transactions = $tManager->executeQuery();
                            $response['transactions'] = $manager->render('FoundTransactionsList', ['manager' => $manager, 'transactions' => $transactions, 'payment' => $class ]);
                        } else {
                            $response['errors'] = $tManager->getErrors();
                        }
                    }
                    echo json_encode($response);
                    exit();
                } elseif ($_action == 'assign_transaction'){
                    $transaction_id = Yii::$app->request->post('transaction_id');
                    if ($transaction_id){
                        $class = Yii::$app->request->post('payment_class');
                        $payment = $manager->getPaymentCollection()->get($class, true);
                        if ($payment){
                          /** @var \common\services\PaymentTransactionManager $tManager */
                            $tManager = $manager->getTransactionManager($payment);
                            $transaction = $tManager->getTransaction($transaction_id);
                            if (!$transaction){
                                if ($tManager->addTransaction($transaction_id, 'undefined', 0, null, 'Manually assigned transaction')){
                                    $payment->getTransactionDetails($transaction_id, $tManager);
                                    $transaction = $tManager->getTransaction($transaction_id);
                                    $tManager->linkLocalTransaction($transaction_id);
                                    $response['message'] = [TEXT_MESSEAGE_SUCCESS_ADDED];
                                    $response['done'] = $transaction->orders_transactions_id;
                                } else {
                                    $response['errors'] = ['Transaction already assigned to another order'];
                                }
                            } else {
                                $response['errors'] = ['Transaction already assigned'];
                            }
                        }
                    }
                    echo json_encode($response);
                    exit();
                } else if ($_action == 'unlink_transaction'){
                    $transaction_orders_id = Yii::$app->request->post('transaction_orders_id');
                    $tManager = $manager->getTransactionManager();
                    $tManager->unlinkTransactionById($transaction_orders_id);
                    $response = [];
                    echo json_encode($response);
                    exit();
                } else if (in_array($_action, ['make_void', 'make_refund'])){ //return per transaction
                    $transaction_orders_id = Yii::$app->request->post('transaction_orders_id');
                    $tManager = $manager->getTransactionManager();
                    $tr = $tManager->getTransactionById($transaction_orders_id);
                    if ($tr){
                        $payment = $manager->getPaymentCollection()->get($tr->payment_class, true);
                        if ($payment){
                            $tManager->usePayment($payment);
                            if ($_action == 'make_void'){
                                $payment_response = $tManager->paymentVoid($tr->transaction_id);
                            } else {
                                $amount = Yii::$app->request->post('amount', 0);
                                if (number_format($tr->transaction_amount, 2) == number_format($amount, 2)) $amount = 0;
                                $payment_response = $tManager->paymentRefund($tr->transaction_id, $amount);
                            }
                            if ($payment_response){

                                // ORDER CANCELLATION IF NEEDED
                                $order = $manager->getOrderInstance();
                                $totals = \yii\helpers\ArrayHelper::map($order->totals, 'code', 'value_inc_tax');
                                $paid = (float)($totals['ot_paid'] ?? 0);
                                $refund = (float)($totals['ot_refund'] ?? 0);
                                $orderStatus = false;
                                if ($refund >= ($paid - 0.01)) {
                                  if (method_exists($payment, 'refundOrderStatus')) {
                                    $orderStatus = $payment->refundOrderStatus();
                                  } else {
                                    $orderStatus = \common\models\OrdersStatus::getDefaultByOrderEvaluationState(\common\helpers\Order::OES_CANCELLED);
                                  }
                                } else {
                                  if (method_exists($payment, 'partialRefundOrderStatus')) {
                                    $orderStatus = $payment->partialRefundOrderStatus();
                                  } else {
                                    $orderStatus = \common\models\OrdersStatus::getDefaultByOrderEvaluationState(\common\helpers\Order::OES_PARTIAL_CANCELLED);
                                  }
                                }
                                if (is_object($orderStatus)) {
                                    \common\helpers\Order::setStatus($order->order_id, $orderStatus->orders_status_id, [], false, true);
                                }
                                unset($totals);
                                unset($refund);
                                unset($paid);
                                // EOF ORDER CANCELLATION IF NEEDED

                                $response['statuses'] = $tManager->getTransactionsStatus([$transaction_orders_id]);
                            } else {
                                //need error status
                            }
                        }
                    }
                    echo json_encode($response);
                    exit();
                } elseif ($_action == 'return_by_credit'){ //return by full credit, may have several transactions
                    $transaction_data = Yii::$app->request->post('transaction_data', []);
                    $fullReturningAmount = $transaction_data['amount'];
                    $log = [];$hide=[];
                    $docId = 0;
                    if ($fullReturningAmount){
                        if (is_array($transaction_data['to_return'])){
                            $tManager = $manager->getTransactionManager();
//                            $tManager->stopPropagination();
                            $completed = false;
                            $fullyCompleted = true;
                            $returnedAmount = 0;
                            $children = [];
                            $log[] = TEXT_LOG_REFUND_START;
                            foreach($transaction_data['to_return'] as &$transaction){
                                if ($returnedAmount >= $fullReturningAmount) {
                                    $log[] = TEXT_LOG_REFUND_AMOUNT_LIMIT;
                                    break;
                                }
                                $tr = $tManager->getTransactionById($transaction['transaction_orders_id']);
                                if ($tr){
                                    $transaction['returning_amount'] = round($transaction['returning_amount'], 2);
                                    $payment = $manager->getPaymentCollection()->get($tr->payment_class, true);
                                    $tManager->usePayment($payment);
                                    if ($tManager->canPaymentVoid($tr->transaction_id)){
                                        if ($tManager->paymentVoid($tr->transaction_id)){
                                            $log[] = sprintf(TEXT_LOG_REFUND_SUCCESSFUL, $tr->transaction_id);
                                            $child = $tr->getLastChildtransaction();
                                            if ($child){
                                                $children[] = $child;
                                                $returnedAmount += $transaction['returning_amount'];
                                                $transaction['success'] = true;
                                            }
                                        } else {
                                            $log[] = sprintf(TEXT_LOG_REFUND_ERROR, $tr->transaction_id);
                                        }
                                    } elseif ($tManager->paymentRefund($tr->transaction_id, $transaction['returning_amount'])) {
                                        $log[] = sprintf(TEXT_LOG_REFUND_SUCCESSFUL, $tr->transaction_id);
                                        $child = $tr->getLastChildtransaction();
                                        if ($child){
                                            $children[] = $child;
                                            $returnedAmount += $transaction['returning_amount'];
                                            $transaction['success'] = true;
                                        }
                                    } else {
                                        $log[] = sprintf(TEXT_LOG_REFUND_ERROR, $tr->transaction_id);
                                    }
                                }
                                $fullyCompleted = $fullyCompleted && $transaction['success'];
                                $completed = $completed || $transaction['success'];
                                if ($transaction['success']){
                                    $hide[] = $tr->orders_transactions_id;//$tr->transaction_id;
                                }
                            }
                            $parentInvoiceId = null;
                            $docId = $tManager->finalizeRefunding($parentInvoiceId, $children, $returnedAmount);
                            if ($fullyCompleted){
                                $log[] = TEXT_LOG_REFUND_COMPLETE;
                            } else if ($completed){
                                $log[] = TEXT_LOG_REFUND_IMCOMPLETE;
                            } else {
                                $log[] = TEXT_LOG_REFUND_PROCESS_ERROR;
                            }
                        }
                    }
                    $currencies = Yii::$container->get('currencies');
                    $response = [
                        'log' => $log,
                        'returned_amount' => $currencies->format($returnedAmount, true, $order->info['currency'], $order->info['currency_value']),
                        'cn_id' => $docId,
                        'hide' => $hide,
                        ];
                    echo json_encode($response);
                    exit();
                }
            }

            return $manager->render('Transactions', ['manager' => $manager, 'orders_id' => $order_id, 'data' => $data], $format);
        }
        exit();
    }

    /**
     * transactional payments actions
     * @return type
     */
    public function actionPTransactions() {
      $updateStatusAndNotify = true;

      \common\helpers\Translation::init('admin/orders');
      $order_id  = Yii::$app->request->get('orders_id');
      $ret = ['status' => 'fail', 'message' => TEXT_MESSAGE_ERROR];
      /** @var \common\services\OrderManager $manager */
      $manager = \common\services\OrderManager::loadManager();
      $manager->setRenderPath('\\backend\\design\\orders\\');
      /** @var common\classes\Order $order */
      $order = $manager->getOrderInstanceWithId('\common\classes\Order', $order_id);

      if ($order_id && Yii::$app->request->isPost && is_object($order)) {
        ///$manager->setModulesVisibility(['admin']);
        \Yii::$app->get('platform')->config($order->info['platform_id'])->constant_up();

        $_action = Yii::$app->request->post('action');

        switch ($_action) {
          case 'make_void':
          case 'make_refund':
          case 'make_capture':
          case 'make_reauthorize':
            $method = str_replace('make_', '', $_action);
            $opId = Yii::$app->request->post('op_id', 0);
            $amount = (float)Yii::$app->request->post('amount', 0);

            $data = \common\helpers\OrderPayment::getRecord($opId);
            if (!$data ||
                (in_array($_action, ['make_refund']) && !in_array($data->orders_payment_status, [\common\helpers\OrderPayment::OPYS_SUCCESSFUL]) )
                ||
                (in_array($_action, ['make_void', 'make_reauthorize']) && !in_array($data->orders_payment_status, [\common\helpers\OrderPayment::OPYS_PENDING, \common\helpers\OrderPayment::OPYS_PROCESSING]) )
                ||
                (in_array($_action, ['make_capture']) && !in_array($data->orders_payment_status, [\common\helpers\OrderPayment::OPYS_PENDING, \common\helpers\OrderPayment::OPYS_PROCESSING, \common\helpers\OrderPayment::OPYS_SUCCESSFUL]) )
                ) {
              $ret['message'] = TEXT_MESSAGE_ERROR_INCORRECT_TRANSACTION;

            } elseif($amount<=0.01 && !in_array($method, ['void'])) {
              $ret['message'] = TEXT_MESSAGE_ERROR_INCORRECT_AMOUNT;

            } else {

              $class = $data->orders_payment_module;
              $builder = new \common\classes\modules\ModuleBuilder($manager);
              $class = $builder(['class' => "\\common\\modules\\orderPayment\\{$class}"]);
              $tmp = 'can' . ucfirst($method);

              if (is_object($class) && method_exists($class, $tmp) && $class->$tmp($data->orders_payment_transaction_id) ) {
                $tmp = $class->$method($data->orders_payment_transaction_id, $amount);
                if ($tmp === true) {
                  $ret = ['status' => 'OK'];
                  //all other - not related to transaction
/*in payment module (now?)
                  $updated = $order->updatePaidTotals();

                  if ($updated) { //update order status and notify customer if required
                    $status = '';
                    if (isset($updated['paid']) ) {
                      //if ($updated['details']['status']>0) {// has due
                      if (abs(
                          round($updated['details']['total'], 2)-
                          round($updated['details']['debit'], 2)
                          ) < 0.01) {
                        $status = $class->paidOrderStatus();
                      } else {
                        $status = $class->partlyPaidOrderStatus();
                      }
                    } elseif (isset($updated['refund']) && $updated['details']['credit']>0) {
                      if (abs(
                          round($updated['details']['total'], 2)-
                          round($updated['details']['credit'], 2)
                          ) < 0.01) {
                        $status = $class->refundOrderStatus();
                      } else {
                        $status = $class->partialRefundOrderStatus();
                      }
                    }

                    if ($updateStatusAndNotify && !empty($status) && $status != $order->info['order_status']) {
                      $order->update_status_and_notify($status);
                    }

                  }*/
                } elseif (is_string($tmp)) {
                  $ret['message'] = $tmp;
                }
              }
            }
          break;
          case 'make_delete':
            $method = str_replace('make_', '', $_action);
            $opId = Yii::$app->request->post('op_id', 0);

            $data = \common\helpers\OrderPayment::getRecord($opId);

            if (!$data ) {
              $ret['message'] = TEXT_MESSAGE_ERROR_INCORRECT_TRANSACTION;

            } elseif($data['orders_payment_admin_create']==0 ||
                !in_array($data['orders_payment_status'], [
                    \common\helpers\OrderPayment::OPYS_PENDING,
                    \common\helpers\OrderPayment::OPYS_DISCOUNTED,
                ]) || \common\helpers\OrderPayment::hasChildren($data['orders_payment_id'])


                ) {
              $ret['message'] = TEXT_MESSAGE_ERROR_TRANSACTION_CANT_DELETE;

            } else {
              $data->delete();
              $ret = ['status' => 'OK'];
            }
          break;
          case 'get_fields':
              $payment = $manager->getPaymentCollection()->get(Yii::$app->request->post('payment_class'), true);
              if ($payment){
                $tManager = $manager->getTransactionManager($payment);
                return $manager->render('payments\PaymentFields', [ 'manager' => $manager , 'rules' => $tManager->getFields()], 'json');
              }
          break;
          case 'search_transactions':
              $class = Yii::$app->request->post('payment_class');
              $payment = $manager->getPaymentCollection()->get($class, true);
              if ($payment){
                $tManager = $manager->getTransactionManager($payment);
                if ($tManager->prepareQuery(Yii::$app->request->post())){
                  $transactions = $tManager->executeQuery();

                  $url = Yii::$app->urlManager->createUrl(['orders/p-transactions', 'orders_id' => $order_id, 'platform_id' => $order->info['platform_id']]);

                  $ret['transactions'] = $this->renderAjax('payment-found-list', [
                    'url' => $url,
                    'transactions' => $transactions,
                    'payment' => $payment->code]);

                      //$manager->render('FoundTransactionsList', ['manager' => $manager, 'transactions' => $transactions, 'payment' => $class ]);
                } else {
                  $ret['errors'] = $tManager->getErrors();
                }
              }
          break;
          case 'assign_transaction': //vl2do
              $transaction_id = Yii::$app->request->post('transaction_id', false);
              $type = Yii::$app->request->post('type', '');

              if ($transaction_id){
                $class = Yii::$app->request->post('payment_class');
                $payment = $manager->getPaymentCollection()->get($class, true);
                //$builder = new \common\classes\modules\ModuleBuilder($manager);
                //$payment = $builder(['class' => "\\common\\modules\\orderPayment\\{$class}"]);

                if ($payment){
                  // check if already added
                  $op = \common\helpers\OrderPayment::searchRecord($class, $transaction_id);
                  if ($op && !empty($op->orders_payment_id)) {
                    $ret['errors'] = [TEXT_ERROR_PAYMENT_EXISTS];
                  } elseif ($op) {
                    /** @var \common\services\PaymentTransactionManager $tManager */
                    $tManager = $manager->getTransactionManager($payment);
                    try {
                      $op->setAttributes([
                          'orders_payment_order_id' => $order_id,
                          'orders_payment_module' => $class,
                          'orders_payment_module_name' => $payment->title,
                          'orders_payment_status' => \common\helpers\OrderPayment::OPYS_PENDING,
                          'orders_payment_amount' => 0,
                          'orders_payment_currency' => $order->info['currency'],
                          'orders_payment_transaction_id' => $transaction_id,
                          'payment_type' => $type,
                          'orders_payment_transaction_commentary' => 'Assigned transaction id',
                          'orders_payment_transaction_date' => date(\common\helpers\Date::DATABASE_DATETIME_FORMAT)
                          ]);
                      $op->save(false);
                    } catch (\Exception $e) {
                      $ret['errors'] = [$e->getMessage()];
                    }

                    if ($op && !empty($op->orders_payment_id)) {
                      $res  = false;
                      if (is_object($payment)  ) {
                        $res = \common\helpers\OrderPayment::updateTransactionDetails(
                            $op,
                            $payment,
                            $manager,
                            false
                            );
                      }

                      if ($res !== true) {
                        if (!empty($res)) {
                          $ret['message'] = [$res];
                        }
                      } else {
                        $ret['message'] = [TEXT_MESSEAGE_SUCCESS_ADDED];
                        $ret['done'] = $op->orders_payment_id;
                      }



                    }
                  }
                }
              }
          break;
        }

      }

      $this->layout = false;
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return $ret;
    }

    public function actionCreditNotes(){
        $cnId = Yii::$app->request->get('cnId');
        $orders_id = Yii::$app->request->get('orders_id');
        if ($orders_id){
            $manager = \common\services\OrderManager::loadManager();
            /** @var \common\services\SplitterManager  $splitter */
            $splitter = $manager->getOrderSplitter();
            $CreditNotes = $splitter->getInstancesFromSplinters($orders_id, $splitter::STATUS_RETURNED, $cnId);
            if ($CreditNotes){
                $languages_id = Yii::$app->settings->get('languages_id');

                $currencies = \Yii::$container->get('currencies');
                $creditNote = array_pop($CreditNotes);

                $platform_id = $creditNote->info['platform_id'] ?? \common\classes\platform::defaultId();
                $__platform = Yii::$app->get('platform');
                $platform_config = $__platform->config($platform_id);
                if ($platform_config->isVirtual()) {
                    $detected = false;
                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('AdditionalPlatforms', 'allowed')) {
                        $_plid = $ext::getVirtualSattelitId($platform_id);
                        if($_plid){
                            $platform_id = $_plid;
                            $detected = true;
                        }
                    }
                    if (!$detected){
                        $platform_id = \common\classes\platform::defaultId();
                    }
                }

                $pages = [['name' => 'credit_note', 'params' => [
                    'orders_id' => $creditNote->order_id,
                    'platform_id' => $platform_id,
                    'language_id' => $languages_id,
                    'order' => $creditNote,
                    'currencies' => $currencies,
                    'oID' => $creditNote->order_id,
                ]]];

                $theme_id = \common\models\PlatformsToThemes::findOne($platform_id)->theme_id;
                $theme_name = \common\models\Themes::findOne($theme_id)->theme_name;

                define('THEME_NAME', $theme_name);
                return  \backend\design\PDFBlock::widget([
                    'pages' => $pages,
                    'params' => [
                        'theme_name' => $theme_name,
                        'document_name' => str_replace(' ', '_', TEXT_CREDITNOTE) . '.pdf',
                    ]
                ]);
            }


        }
        die;
    }

    public function actionSortProducts(){
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'status' => 'ok',
        ];

        $order_id = Yii::$app->request->get('order_id',0);
        $sortkey = Yii::$app->request->post('sortkey',[]);
        if ( is_array($sortkey) && count($sortkey)>0 ) {
            $sort_order = 0;
            foreach($sortkey as $op_id) {
                tep_db_query("UPDATE ".TABLE_ORDERS_PRODUCTS." SET sort_order='".($sort_order++)."' WHERE orders_id='".(int)$order_id."' AND orders_products_id='".(int)$op_id."'");
            }
        }else{
            Yii::$app->response->data = [
                'status' => 'error',
            ];
        }
    }

    public function actionMerge() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('MergeOrders', 'allowed')) {
            return $ext::actionMerge();
        }
        return $this->redirect(Yii::$app->urlManager->createUrl(['orders/']));
    }

    public function actionProductAllocateTemporaryInformation()
    {
        $orderStatusExpiredDurationHours = (int)\common\helpers\Configuration::get_configuration_key_value('ORDER_STATUS_TEMPORARY_ALLOCATION_EXPIRED_DURATION');
        if ($orderStatusExpiredDurationHours < 1) {
            $orderStatusExpiredDurationHours = 1;
        }
        echo '<div class="popup-heading">';
        foreach (\common\helpers\OrderProduct::getAllocatedArray(Yii::$app->request->get('opID', 0), true) as $opaRecord) {
            $qtyRcvd = ((int)$opaRecord['allocate_received'] - (int)$opaRecord['allocate_dispatched']);
            if ((int)$opaRecord['is_temporary'] > 0 AND $qtyRcvd > 0) {
                $timeExpire = (strtotime($opaRecord['datetime']) + $orderStatusExpiredDurationHours * 60 * 60);
                $timeDelta = ($timeExpire - time());
                $isExpired = ($timeDelta < 0);
                $timeDelta = ($timeDelta / 60);
                $timeHour = floor($timeDelta / 60);
                $timeMinute = floor($timeDelta - ($timeHour * 60));
                $qtyRcvd = \common\helpers\Product::getVirtualItemQuantity($opaRecord['prid'], $qtyRcvd);
                if ($isExpired == true) {
                    echo sprintf(MESSAGE_ORDER_STATUS_TEMPORARY_ALLOCATION_EXPIRED,
                        $qtyRcvd, $orderStatusExpiredDurationHours
                    ) . '<br />';
                } else {
                    echo sprintf(MESSAGE_ORDER_STATUS_TEMPORARY_ALLOCATION_EXPIRE_IN,
                        $qtyRcvd, $timeHour, $timeMinute, $orderStatusExpiredDurationHours
                    ) . '<br />';
                }
            }
        }
        echo '</div>';
    }

    /**
     * Set status selected orders in orders list
     */
    public function actionSetStatus(){
        $this->layout = false;
        $languages_id = \Yii::$app->settings->get('languages_id');

        $selected_ids = Yii::$app->request->post('selected_ids',[]);
        if(!is_array($selected_ids) &&(int)$selected_ids > 0){
            $selected_ids = [];
            $selected_ids[]= (int)Yii::$app->request->post('selected_ids',0);
        }

        $status = (int)Yii::$app->request->post('status');

        $force = Yii::$app->request->post('force','false') === 'true' ? 1 : 0;
        $restock = Yii::$app->request->post('restock','false') === 'true' ? 1 : 0;
        $cancel = Yii::$app->request->post('cancel','false') === 'true' ? 1 : 0;

        $comments = Yii::$app->request->post('comments','');
        $customer_notified = Yii::$app->request->post('notify','false') === 'true' ? 1 : 0;
        $paid = (int)Yii::$app->request->post('paid','');

        $isAlternativeBehaviour = false;
        $orderStatusRecord = \common\models\OrdersStatus::findOne(['orders_status_id' => $status]);
        if ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_PENDING) {
            $isAlternativeBehaviour = $cancel;
        } elseif ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_PROCESSING) {
        } elseif ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_CANCELLED) {
            $isAlternativeBehaviour = $restock;
        } elseif ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_DISPATCHED) {
            $isAlternativeBehaviour = $force;
        } elseif ($orderStatusRecord->order_evaluation_state_id == \common\helpers\Order::OES_DELIVERED) {
            $isAlternativeBehaviour = $force;
        }
        unset($orderStatusRecord);

        $orders_statuses = array();
        $orders_status_array = array();
        $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "'");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
            $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                'text' => $orders_status['orders_status_name']);
            $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
        }

        $manager = \common\services\OrderManager::loadManager();
        $manager->setModulesVisibility(['shop_order']);

        foreach ($selected_ids as $oID) {
            $customer_notified_status = 0;

            if ($customer_notified) {

                //$order = new \common\classes\Order($oID);
                $order = $manager->getOrderInstanceWithId('\common\classes\Order', $oID);
                /**
                 * @var \common\classes\Order $order
                 */

                $notify_comments = '';
                $EMAIL_TEXT_COMMENTS_UPDATE = Translation::getTranslationValue('EMAIL_TEXT_COMMENTS_UPDATE', 'admin/main', $order->info['language_id']);
                $notify_comments = trim(sprintf($EMAIL_TEXT_COMMENTS_UPDATE, $comments)) . "\n\n";

                $order->info['order_status'] = $status;
                $customer_notified_status = $order->send_status_notify($notify_comments, []);
            }

            \common\helpers\Order::setStatus($oID, $status, [
                'comments' => $comments,
                //'smscomments' => $smscomments,
                'customer_notified' => $customer_notified_status
            ], false, $isAlternativeBehaviour);
        }

        //return \common\helpers\Order::change_order_status($selected_ids,$status,$comments,$snotify);
    }

    public function actionPaymentList()
    {
      Translation::init('admin/orders');
        $oID = Yii::$app->request->get('oID');
        $listOnly = Yii::$app->request->get('list_only', 0);
        $currencies = new \common\classes\Currencies();
        $opyStatusList = \common\helpers\OrderPayment::getStatusList();

        $adminArray = $modules = $paymentArray = [];
        $manager = new \common\services\OrderManager(Yii::$app->get('storage'));
        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $oID);
        Yii::$app->get('platform')->config($order->info['platform_id'])->constant_up();

        if (!empty($order->orders_id)) {
          $orders_id = $order->orders_id;
        } else {
          $orders_id = $oID;
        }
        $_activePlatformId = $order->info['platform_id'];
        $builder = new \common\classes\modules\ModuleBuilder($manager);

        foreach (\common\helpers\OrderPayment::getArrayByOrderId($oID) as $paymentRecord) {
            if (!isset($adminArray[$paymentRecord['orders_payment_admin_create']])) {
                $adminArray[$paymentRecord['orders_payment_admin_create']] = new \backend\models\Admin($paymentRecord['orders_payment_admin_create']);
            }
            if (!isset($adminArray[$paymentRecord['orders_payment_admin_update']])) {
                $adminArray[$paymentRecord['orders_payment_admin_update']] = new \backend\models\Admin($paymentRecord['orders_payment_admin_update']);
            }
            if ($paymentRecord['orders_payment_admin_create']>0) {
              $manaual = true;
              $paymentRecord['orders_payment_admin_create'] = ($adminArray[$paymentRecord['orders_payment_admin_create']]->getInfo('admin_firstname')
                  . ' ' . $adminArray[$paymentRecord['orders_payment_admin_create']]->getInfo('admin_lastname')
              );
            } else {
              $manaual = false;
            }
            if ($paymentRecord['orders_payment_admin_update']>0) {
              $paymentRecord['orders_payment_admin_update'] = ($adminArray[$paymentRecord['orders_payment_admin_update']]->getInfo('admin_firstname')
                  . ' ' . $adminArray[$paymentRecord['orders_payment_admin_update']]->getInfo('admin_lastname')
              );
            }

            $colour = 'black';
            /// 2do something (with _type field) Auth->reauth->Capture(payment)->refund
            //if ($paymentRecord['orders_payment_id_parent'] == 0) {
            if ($paymentRecord['orders_payment_status'] == \common\helpers\OrderPayment::OPYS_SUCCESSFUL) {
                $colour = 'green';
            } elseif ($paymentRecord['orders_payment_status'] == \common\helpers\OrderPayment::OPYS_REFUNDED) {
                $colour = 'blue';
            } elseif ($paymentRecord['orders_payment_status'] == \common\helpers\OrderPayment::OPYS_DISCOUNTED) {
                $colour = 'purple';
            }
            /*} else {
                if ($paymentRecord['orders_payment_status'] == \common\helpers\OrderPayment::OPYS_REFUNDED) {
                    $colour = 'blue';
                    if ($paymentRecord['orders_payment_is_credit'] == 0) {
                        $colour = 'green';
                    }
                } elseif ($paymentRecord['orders_payment_status'] == \common\helpers\OrderPayment::OPYS_DISCOUNTED) {
                    $colour = 'purple';
                    if ($paymentRecord['orders_payment_is_credit'] == 0) {
                        $colour = 'green';
                    }
                }
            }*/
            $paymentRecord['orders_payment_amount_colour'] = $colour;
            $paymentRecord['orders_payment_is_refund'] = 0;
            if (/*($paymentRecord['orders_payment_id_parent'] <= 0) AND ///!!! capture has parent auth transaction */
                in_array($paymentRecord['orders_payment_status'], [
                \common\helpers\OrderPayment::OPYS_SUCCESSFUL,
                \common\helpers\OrderPayment::OPYS_DISCOUNTED,
            ])) {
                $paymentRecord['orders_payment_is_refund'] = 1;
            }

            $class = $paymentRecord['orders_payment_module'];
            if (empty($modules[$class])) {
              try {
                $modules[$class] = $builder(['class' => "\\common\\modules\\orderPayment\\{$class}"]);
              } catch (\Exception $e) { } // offline payment -not important: no extra links buttons
            }

            if (is_object($modules[$class]) && $modules[$class] instanceof \common\classes\modules\TransactionalInterface ) {
              $paymentRecord['transactional'] = true;
              foreach ([
                'can_refund' => 'canRefund',
                'can_void' => 'canVoid',
                'can_capture' => 'canCapture',
                'can_reauthorize' => 'canReauthorize',
              ] as $key => $method) {
                if (($method!='canRefund' || $paymentRecord['orders_payment_is_refund'] != 0) && !empty($method) && method_exists($modules[$class], $method)) {
                   $paymentRecord[$key] = $modules[$class]->$method($paymentRecord['orders_payment_transaction_id']);
                }
              }
            }
            /// manual, pending,  and no children - can delete
            if ($manaual &&
                in_array($paymentRecord['orders_payment_status'], [
                    \common\helpers\OrderPayment::OPYS_PENDING,
                    \common\helpers\OrderPayment::OPYS_DISCOUNTED,
                ]) && !\common\helpers\OrderPayment::hasChildren($paymentRecord['orders_payment_id'])
            ){
              $paymentRecord['can_delete'] = 1;
            } else {
                $paymentRecord['can_delete'] = 0;
            }

            $paymentRecord['orders_payment_status'] = $opyStatusList[$paymentRecord['orders_payment_status']];
            $paymentRecord['payment_amount'] =  $currencies->format_clear($paymentRecord['orders_payment_amount'], false, $paymentRecord['orders_payment_currency']);
            $paymentRecord['orders_payment_amount'] = $currencies->format($paymentRecord['orders_payment_amount'], false, $paymentRecord['orders_payment_currency']);
            $paymentRecord['orders_payment_date_create'] = \common\helpers\Date::datetime_short($paymentRecord['orders_payment_date_create']);
            $paymentRecord['orders_payment_date_update'] = \common\helpers\Date::datetime_short($paymentRecord['orders_payment_date_update']);
            $paymentRecord['orders_payment_transaction_date'] = \common\helpers\Date::datetime_short($paymentRecord['orders_payment_transaction_date']);
            $paymentRecord['orders_payment_transaction_commentary'] = nl2br($paymentRecord['orders_payment_transaction_commentary']);

            $paymentArray[] = $paymentRecord;
        }
        $onBehalfUrl = false;
        if ( extension_loaded('openssl') ) {
          $actions[] = [
              'value' => 'on_behalf',
              'name' => TEXT_PAY_ON_BEHALF,
          ];
          $cInfo = \common\models\Customers::find()->where(['customers_id' => $order->customer['id']])->one();
          $aup = \common\helpers\Password::encryptAuthUserParam($order->customer['id'], $order->customer['email_address'], 'payment', ($cInfo->auth_key ?? ''));
          \Yii::$app->get('platform')->config($_activePlatformId);

          $due = array_filter($order->totals, function ($el) { return ($el['class']=='ot_due' && round($el['value'],2)>0.01 );} );
          if (count($due)>0) {
            $onBehalfUrl = tep_catalog_href_link('account/login-me', 'order_id=' . (int)$orders_id . '&payer=1&aup='.$aup);
          }

        }

        $url = Yii::$app->urlManager->createUrl(['orders/p-transactions', 'orders_id' => $orders_id, 'platform_id' => ($_activePlatformId??0)]);

        return $this->renderAjax('payment-list', [
            'oID' => $oID,
            'url' => $url,
            'listOnly' => $listOnly,
            'onBehalfUrl' => $onBehalfUrl,
            'platform_id' => ($_activePlatformId??0),
            'paymentArray' => $paymentArray
        ]);
    }

/**
 * update transaction status from payment gateway if possible.
 */
    public function actionPaymentUpdateStatus() {
      $ret = ['status' => 'fail', 'message' => TEXT_MESSAGE_ERROR];
      $opyID = Yii::$app->request->post('opyID');
      $paymentRecord = \common\helpers\OrderPayment::getRecord($opyID);

      if ($paymentRecord instanceof \common\models\OrdersPayment && $paymentRecord->orders_payment_order_id>0) {
        $orderManager = new \common\services\OrderManager(Yii::$app->get('storage'));
        /** @var \common\classes\Order $order */
        $order = $orderManager->getOrderInstanceWithId('\common\classes\Order', $paymentRecord->orders_payment_order_id);
        $platform_id = $order->info['platform_id'];
        $config = new \common\classes\platform_config($platform_id);
        $config->constant_up();

        $builder = new \common\classes\modules\ModuleBuilder($orderManager);
        $class = $builder(['class' => "\\common\\modules\\orderPayment\\{$paymentRecord->orders_payment_module}"]);

        $res  = false;

        if (is_object($class)  ) {
          $res = \common\helpers\OrderPayment::updateTransactionDetails(
              $paymentRecord,
              $class,
              $orderManager,
              false
              );
        }

        if ($res !== true) {
          if (!empty($res)) {
            $ret['message'] = $res;
          }
        } else {
          $ret = ['status' => 'OK'];
        }

      }

      $this->layout = false;
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      return $ret;
    }


    public function actionPaymentEdit()
    {
        $opyID = Yii::$app->request->get('opyID');
        $orderPaymentStatusArray = [];
        $paymentRecord = \common\helpers\OrderPayment::getRecord($opyID);
        if ($paymentRecord instanceof \common\models\OrdersPayment) {
            $paymentChildCount = count(\common\helpers\OrderPayment::getArrayChildByParentId($paymentRecord->orders_payment_id));
            foreach (\common\helpers\OrderPayment::getStatusList($paymentRecord['orders_payment_status']) as $statusId => $statusName) {
                if ($paymentChildCount > 0 AND $paymentRecord['orders_payment_status'] != $statusId) {
                    continue;
                }
                $orderPaymentStatusArray[] = ['id' => $statusId, 'text' => $statusName];
            }
            return $this->renderAjax('payment-edit', [
                'orderPaymentStatusArray' => $orderPaymentStatusArray,
                'paymentRecord' => $paymentRecord
            ]);
        } else {
            foreach (\common\helpers\OrderPayment::getStatusList() as $statusId => $statusName) {
                if (in_array($statusId, [
                    \common\helpers\OrderPayment::OPYS_REFUSED,
                    \common\helpers\OrderPayment::OPYS_REFUNDED,
                    \common\helpers\OrderPayment::OPYS_CANCELLED,
                    \common\helpers\OrderPayment::OPYS_DISCOUNTED
                ])) {
                    continue;
                }
                $orderPaymentStatusArray[] = ['id' => $statusId, 'text' => $statusName];
            }
            $oID = Yii::$app->request->get('oID');
            $orderRecord = \common\helpers\Order::getRecord($oID);
            $cartInstance = new \common\classes\shopping_cart((int)$orderRecord->orders_id);
            $managerInstance = \common\services\OrderManager::loadManager($cartInstance);
            Yii::$app->get('platform')->config((int)$orderRecord->platform_id)->constant_up();
            $managerInstance->set('platform_id', (int)$orderRecord->platform_id);
            $payments = new \common\classes\payment('', $managerInstance);
            $paymentArray = $pSearchList = [];
            $paymentArray[] = [
                'id' => '',
                'text' => ''
            ];
            foreach ($payments->getEnabledModules() as $paymentClass) {
                $paymentArray[] = [
                    'id' => $paymentClass->code,
                    'text' => $paymentClass->title
                ];
                if ($paymentClass instanceof \common\classes\modules\TransactionSearchInterface) {
                  $pSearchList[$paymentClass->code] = $paymentClass->title;
                }
            }
            $currencies = new \common\classes\Currencies((int)$orderRecord->platform_id);
            $currencyArray = [];
            $currencyArray[] = [
                'id' => '',
                'text' => ''
            ];
            foreach ($currencies->currencies as $currencyData) {
                $currencyArray[] = [
                    'id' => $currencyData['code'],
                    'text' => $currencyData['title']
                ];
            }
            $url = Yii::$app->urlManager->createUrl(['orders/p-transactions', 'orders_id' => $oID, 'platform_id' => ((int)$orderRecord->platform_id??0)]);
            $mode = Yii::$app->request->get('search', 0);
            return $this->renderAjax('payment-edit-add', [
                'oID' => $oID,
                'paymentArray' => $paymentArray,
                'currencyArray' => $currencyArray,
                'currencyDefaultCode' => $currencies->dp_currency,
                'orderPaymentStatusArray' => $orderPaymentStatusArray,
                'url' => $url,
                'list' => $pSearchList,
                'mode' => $mode,
                'search' => Yii::$app->request->get('search', false),
            ]);
        }
    }

    public function actionPaymentRefund()
    {
        $opyID = Yii::$app->request->get('opyID');
        $orderPaymentStatusArray = []; //?? refund statuses?
        foreach (\common\helpers\OrderPayment::getStatusList() as $statusId => $statusName) {
            if (in_array($statusId, [
                \common\helpers\OrderPayment::OPYS_PENDING,
                \common\helpers\OrderPayment::OPYS_PROCESSING,
                \common\helpers\OrderPayment::OPYS_SUCCESSFUL,
                \common\helpers\OrderPayment::OPYS_REFUSED,
                \common\helpers\OrderPayment::OPYS_CANCELLED
            ])) {
                continue;
            }
            $orderPaymentStatusArray[] = ['id' => $statusId, 'text' => $statusName];
        }
        $paymentRecord = \common\helpers\OrderPayment::getRecord($opyID);
        if ($paymentRecord instanceof \common\models\OrdersPayment) {
            if (($paymentRecord->orders_payment_id_parent > 0) OR !in_array($paymentRecord->orders_payment_status, [
                \common\helpers\OrderPayment::OPYS_SUCCESSFUL,
                \common\helpers\OrderPayment::OPYS_DISCOUNTED,
            ])) {
                return 'Selected order payment record can\'t be refunded!';
            }
            $orderRecord = \common\helpers\Order::getRecord($paymentRecord->orders_payment_order_id);
            $cartInstance = new \common\classes\shopping_cart((int)$orderRecord->orders_id);
            $managerInstance = \common\services\OrderManager::loadManager($cartInstance);
            Yii::$app->get('platform')->config((int)$orderRecord->platform_id)->constant_up();
            $managerInstance->set('platform_id', (int)$orderRecord->platform_id);
            $payments = new \common\classes\payment('', $managerInstance);
            $paymentArray = [];
            $paymentArray[] = [
                'id' => '',
                'text' => ''
            ];
            foreach ($payments->getEnabledModules() as $paymentClass) {
                $paymentArray[] = [
                    'id' => $paymentClass->code,
                    'text' => $paymentClass->title
                ];
            }
            return $this->renderAjax('payment-refund', [
                'orderPaymentStatusArray' => $orderPaymentStatusArray,
                'paymentRecord' => $paymentRecord,
                'paymentArray' => $paymentArray
            ]);
        } else {
            return 'Order payment record not found!';
        }
    }

    public function actionPaymentSave()
    {
        global $login_id;

        $return = [
            'status' => 'error',
            'message' => '',
            'information' => '',
            'reload' => 0
        ];
        $orderPaymentRecord = false;
        $orderPaymentIsCredit = false;
        $orderPaymentCurrencyRate = null;
        $oID = (int)Yii::$app->request->post('oID');
        $oPyID = (int)Yii::$app->request->post('orders_payment_id');
        $oPyIDParent = (int)Yii::$app->request->post('orders_payment_id_parent');
        $orderPaymentStatus = (int)Yii::$app->request->post('orders_payment_status');
        $orderPaymentModule = trim(Yii::$app->request->post('orders_payment_module'));
        $orderPaymentCurrency = trim(Yii::$app->request->post('orders_payment_currency'));
        $orderPaymentAmount = (float)trim(Yii::$app->request->post('orders_payment_amount'));
        $orderPaymentTransactionId = trim(Yii::$app->request->post('orders_payment_transaction_id'));
        $orderPaymentTransactionDate = \common\helpers\Date::unformatCalendarDate(trim(Yii::$app->request->post('orders_payment_transaction_date')));
        $orderPaymentTransactionDate = (empty($orderPaymentTransactionDate) ? '0000-00-00 00:00:00' : $orderPaymentTransactionDate);
        $orderPaymentTransactionCommentary = trim(strip_tags(Yii::$app->request->post('orders_payment_transaction_commentary')));
        if (!in_array($orderPaymentStatus, array_keys(\common\helpers\OrderPayment::getStatusList()))) {
            $return['message'] = 'Selected status is invalid!';
        } elseif (in_array($orderPaymentStatus, [
                \common\helpers\OrderPayment::OPYS_PROCESSING,
                \common\helpers\OrderPayment::OPYS_SUCCESSFUL,
                \common\helpers\OrderPayment::OPYS_REFUNDED
            ]) AND ($orderPaymentTransactionId == ''
                OR $orderPaymentTransactionDate == '0000-00-00 00:00:00'
            )
        ) {
            $return['message'] = 'Transaction information is invalid!';
        } elseif ($orderPaymentAmount <= 0) {
            $return['message'] = 'Amount is invalid!';
        } else {
            if ($oPyID > 0) {
                $orderPaymentRecord = \common\helpers\OrderPayment::getRecord($oPyID);
                if ($orderPaymentRecord instanceof \common\models\OrdersPayment) {
                    $oID = (int)$orderPaymentRecord->orders_payment_order_id;
                    $oPyIDParent = (int)$orderPaymentRecord->orders_payment_id_parent;
                    $orderPaymentRecord->orders_payment_amount = (float)$orderPaymentRecord->orders_payment_amount;
                    $orderPaymentAmountAvailable = \common\helpers\OrderPayment::getAmountAvailable($orderPaymentRecord);
                    if ($orderPaymentRecord->orders_payment_id_parent == 0) {
                        if ($orderPaymentAmount < ($orderPaymentRecord->orders_payment_amount - $orderPaymentAmountAvailable)) {
                            $orderPaymentAmount = ($orderPaymentRecord->orders_payment_amount - $orderPaymentAmountAvailable);
                        }
                    } else {
                        if ($orderPaymentAmount > ($orderPaymentRecord->orders_payment_amount + $orderPaymentAmountAvailable)) {
                            $orderPaymentAmount = ($orderPaymentRecord->orders_payment_amount + $orderPaymentAmountAvailable);
                        }
                    }
                    if (count(\common\helpers\OrderPayment::getArrayChildByParentId($orderPaymentRecord->orders_payment_id)) > 0) {
                        $orderPaymentStatus = $orderPaymentRecord->orders_payment_status;
                    }
                } else {
                    $return['message'] = 'Payment record not found!';
                }
            } elseif ($oPyIDParent > 0) {
                $orderPaymentParentRecord = \common\helpers\OrderPayment::getRecord($oPyIDParent);
                if ($orderPaymentParentRecord instanceof \common\models\OrdersPayment) {
                    $oID = (int)$orderPaymentParentRecord->orders_payment_order_id;
                    $orderPaymentCurrency = $orderPaymentParentRecord->orders_payment_currency;
                    $orderPaymentCurrencyRate = (float)$orderPaymentParentRecord->orders_payment_currency_rate;
                    $orderPaymentIsCredit = ((int)$orderPaymentParentRecord->orders_payment_is_credit > 0 ? 0 : 1);
                    $orderPaymentAmountAvailable = \common\helpers\OrderPayment::getAmountAvailable($orderPaymentParentRecord);
                    if ($orderPaymentAmount > $orderPaymentAmountAvailable) {
                        $orderPaymentAmount = $orderPaymentAmountAvailable;
                    }
                    if ($orderPaymentAmount <= 0) {
                        $return['message'] = 'Amount already refunded!';
                    }
                } else {
                    $return['message'] = 'Parent payment record not found!';
                }
            }
        }
        if ($return['message'] == '') {
            $orderRecord = \common\helpers\Order::getRecord($oID);
            if ($orderRecord instanceof \common\models\Orders) {
                $cartInstance = new \common\classes\shopping_cart((int)$orderRecord->orders_id);
                if (is_object($cartInstance)) {
                    $managerInstance = \common\services\OrderManager::loadManager($cartInstance);
                    if (is_object($managerInstance)) {
                        $orderInstance = $managerInstance->getOrderInstanceWithId('\common\classes\Order', (int)$orderRecord->orders_id);
                        if (is_object($orderInstance)) {
                            Yii::$app->get('platform')->config((int)$orderRecord->platform_id)->constant_up();
                            $managerInstance->set('platform_id', (int)$orderRecord->platform_id);
                        } else {
                            $return['message'] = 'Invalid Order instance!';
                        }
                    } else {
                        $return['message'] = 'Invalid Manager instance!';
                    }
                } else {
                    $return['message'] = 'Invalid Cart instance!';
                }
            } else {
                $return['message'] = 'Order record not found!';
            }
        }
        if ($return['message'] == '') {
            if (!($orderPaymentRecord instanceof \common\models\OrdersPayment)) {
                $currencies = new \common\classes\Currencies((int)$orderRecord->platform_id);
                $currencyArray = [];
                foreach ($currencies->currencies as $currencyData) {
                    $currencyArray[$currencyData['code']] = $currencyData['value'];
                }
                $orderPaymentCurrencyRate = (float)(($orderPaymentCurrencyRate > 0)
                    ? $orderPaymentCurrencyRate
                    : (isset($currencyArray[$orderPaymentCurrency])
                        ? $currencyArray[$orderPaymentCurrency]
                        : 0
                    )
                );
                if ($orderPaymentCurrencyRate <= 0) {
                    $return['message'] = 'Payment currency is invalid!';
                } else {
                    $payments = new \common\classes\payment('', $managerInstance);
                    $paymentArray = [];
                    foreach ($payments->getEnabledModules() as $paymentClass) {
                        $paymentArray[$paymentClass->code] = $paymentClass->title;
                    }
                    if (!isset($paymentArray[$orderPaymentModule])) {
                        $return['message'] = 'Payment method is invalid!';
                    } else {
                        $orderPaymentRecord = new \common\models\OrdersPayment();
                        $orderPaymentRecord->orders_payment_id_parent = $oPyIDParent;
                        $orderPaymentRecord->orders_payment_order_id = $orderRecord->orders_id;
                        $orderPaymentRecord->orders_payment_currency = $orderPaymentCurrency;
                        $orderPaymentRecord->orders_payment_currency_rate = $orderPaymentCurrencyRate;
                        $orderPaymentRecord->orders_payment_module = $orderPaymentModule;
                        $orderPaymentRecord->orders_payment_module_name = $paymentArray[$orderPaymentModule];
                        $orderPaymentRecord->orders_payment_snapshot = json_encode(\common\helpers\OrderPayment::getOrderPaymentSnapshot($orderInstance));
                        $orderPaymentRecord->orders_payment_transaction_status = '';
                        $orderPaymentRecord->orders_payment_admin_create = $login_id;
                        if ($orderPaymentIsCredit !== false) {
                            $orderPaymentRecord->orders_payment_is_credit = $orderPaymentIsCredit;
                        } elseif (in_array($orderPaymentStatus, [
                            \common\helpers\OrderPayment::OPYS_REFUNDED,
                            \common\helpers\OrderPayment::OPYS_DISCOUNTED
                        ])) {
                            $orderPaymentRecord->orders_payment_is_credit = 1;
                        }
                    }
                }
            } else {
                if ($orderPaymentStatus != \common\helpers\OrderPayment::OPYS_PENDING
                    AND $orderPaymentAmount != $orderPaymentRecord->orders_payment_amount
                ) {
                    $orderPaymentAmount = $orderPaymentRecord->orders_payment_amount;
                    $return['message'] = 'Amount not changed due to payment status!';
                }
            }
            if (($orderPaymentRecord->orders_payment_amount??null) != $orderPaymentAmount
                OR (int)$orderPaymentRecord->orders_payment_status != $orderPaymentStatus
            ) {
                $return['reload'] = 1;
            }
            $orderPaymentRecord->orders_payment_status = $orderPaymentStatus;
            $orderPaymentRecord->orders_payment_amount = $orderPaymentAmount;
            $orderPaymentRecord->orders_payment_transaction_id = $orderPaymentTransactionId;
            $orderPaymentRecord->orders_payment_transaction_date = $orderPaymentTransactionDate;
            $orderPaymentRecord->orders_payment_transaction_commentary = $orderPaymentTransactionCommentary;
            $orderPaymentRecord->orders_payment_admin_update = $login_id;
            $orderPaymentRecord->orders_payment_date_update = date('Y-m-d H:i:s');
            try {
                if ($orderPaymentRecord->save()) {
                    try {
                      /*
                        $totalCollection = $managerInstance->getTotalCollection();
                        $totalCollection = $totalCollection->process(['ot_paid', 'ot_due', 'ot_refund']);
                        if (is_array($totalCollection) AND count($totalCollection) > 0) {
                            $orderInstance->totals = array_replace($orderInstance->totals, $totalCollection); //??? STUPID!!! SORT ORDER CHANGE ISSUE
                            $orderInstance->save_totals();
                        }
                       */
                        $updated = $orderInstance->updatePaidTotals();
                        //?? auto switch status??
                        $return['status'] = 'ok';
                    } catch (\Exception $exc) {
                        $return['message'] = 'Error while updating Order totals!';
                    }
                } else {
                    $message = '';
                    foreach ($orderPaymentRecord->getErrors() as $errorArray) {
                        $message .= implode("\n", $errorArray) . "\n";
                    }
                    unset($errorArray);
                    $return['message'] = trim($message);
                    unset($message);
                }
            } catch (\Exception $exc) {
                $return['message'] = 'Error while updating Payment record!' . $exc->getMessage();
                \Yii::error(" #### " . print_r($exc->getMessage(), 1), 'TLDEBUG');
            }
        }
        if ($return['message'] != '') {
            $return['reload'] = 0;
        }
        echo json_encode($return);
        exit();
    }
}
