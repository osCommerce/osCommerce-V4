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

use common\helpers\Coupon;
use common\helpers\Order as OrderHelper;
use common\models\Orders;
use common\helpers\Output;
use backend\models\AdminCarts;

use Yii;

/**
 * default controller to handle user requests.
 */
class FiltersController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_FILTERS'];

    public function actionIndex() {

        $messages = [];
        if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
            if (!is_array($messages)) $messages = [];
        }
        
        $this->selectedMenu = array('settings', 'filters');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('filters/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $filters = [
            'orders',
            'customers',
            //'products',
        ];
        $filter_selected = Yii::$app->request->get('type','orders');
        
        $GET = [];
        $this->view->filters = new \stdClass();
        
        if ($filter_selected == 'orders') {// orders start
        
        \common\helpers\Translation::init('admin/orders');
        
        $AdminFilters = \common\models\AdminFilters::findOne(['filter_type' => 'orders']);
        if ($AdminFilters instanceof \common\models\AdminFilters) {
            $GET = \Opis\Closure\unserialize($AdminFilters->filter_data);
        }
        
        $markers = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $markers = $ext::getMarkersList(true);
        }
        $this->view->markers = $markers;
        $this->view->filters->marker = (isset($GET['marker']) ? (int)$GET['marker'] : 0);

        $flags = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')){
            $flags = $ext::getFlagsList(true);
        }
        $this->view->flags = $flags;
        $this->view->filters->flag = (isset($GET['flag']) ? (int)$GET['flag'] : 0);

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
        }
        if (!empty($GET['fp_to'])) { //summ
          $this->view->filters->fp_to = htmlspecialchars($GET['fp_to']);
          $this->view->filters->fpTo = true; //flag
        }
        $this->view->filters->fpClass = OrderHelper::getUsedTotalClassList($GET['fp_class']??'');

        $oModelQuery = Orders::find();

        $this->view->filters->payments = $payments = \yii\helpers\ArrayHelper::map($oModelQuery->select(['payment_method'])->groupBy('payment_method')->orderBy('payment_method')->asArray()->all(), 'payment_method', 'payment_method');
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

        $this->view->filters->row = (isset($GET['row']) ? (int)$GET['row'] : 0);
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
        
        }// orders end
        
        if ($filter_selected == 'customers') {// customers start
            
            \common\helpers\Translation::init('admin/customers');
        
            $AdminFilters = \common\models\AdminFilters::findOne(['filter_type' => 'customers']);
            if ($AdminFilters instanceof \common\models\AdminFilters) {
                $GET = \Opis\Closure\unserialize($AdminFilters->filter_data);
            }

            $by = [
                [
                    'name' => TEXT_ANY,
                    'value' => '',
                    'selected' => '',
                ],
                [
                    'name' => ENTRY_FIRST_NAME,
                    'value' => 'firstname',
                    'selected' => '',
                ],
                [
                    'name' => ENTRY_LAST_NAME,
                    'value' => 'lastname',
                    'selected' => '',
                ],
                [
                    'name' => TEXT_EMAIL,
                    'value' => 'email',
                    'selected' => '',
                ],
                [
                    'name' => ENTRY_COMPANY,
                    'value' => 'companyname',
                    'selected' => '',
                ],
                [
                    'name' => ENTRY_TELEPHONE_NUMBER,
                    'value' => 'phone',
                    'selected' => '',
                ],
                [
                    'name' => TEXT_ZIP_CODE,
                    'value' => 'postcode',
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

            $this->view->filters->showGroup = \common\helpers\Extensions::isCustomerGroupsAllowed();
            $group = '';
            if (isset($GET['group'])) {
                $group = $GET['group'];
            }
            $this->view->filters->group = $group;

            $country = '';
            if (isset($GET['country'])) {
                $country = $GET['country'];
            }
            $this->view->filters->country = $country;

            $state = '';
            if (ACCOUNT_STATE == 'required' || ACCOUNT_STATE == 'visible') {
                $this->view->showState = true;
            } else {
                $this->view->showState = false;
            }
            if (isset($GET['state'])) {
                $state = $GET['state'];
            }
            $this->view->filters->state = $state;

            $city = '';
            if (isset($GET['city'])) {
                $city = $GET['city'];
            }
            $this->view->filters->city = $city;

            $company = '';
            if (isset($GET['company'])) {
                $company = $GET['company'];
            }
            $this->view->filters->company = $company;

            $guest = [
                [
                    'name' => TEXT_ALL_CUSTOMERS,
                    'value' => '',
                    'selected' => '',
                ],
                [
                    'name' => TEXT_BTN_YES,
                    'value' => 'y',
                    'selected' => '',
                ],
                [
                    'name' => TEXT_BTN_NO,
                    'value' => 'n',
                    'selected' => '',
                ],
            ];
            foreach ($guest as $key => $value) {
                if (isset($GET['guest']) && $value['value'] == $GET['guest']) {
                    $guest[$key]['selected'] = 'selected';
                }
            }
            $this->view->filters->guest = $guest;

/** @var \common\extensions\Subscribers\Subscribers $subscr  */
            if ($subscr = \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed')) {
                $newsletter = [
                    [
                        'name' => TEXT_ANY,
                        'value' => '',
                        'selected' => '',
                    ],
                    [
                        'name' => TEXT_SUBSCRIBED,
                        'value' => 's',
                        'selected' => '',
                    ],
                    [
                        'name' => TEXT_NOT_SUBSCRIBED,
                        'value' => 'ns',
                        'selected' => '',
                    ],
                ];
                foreach ($newsletter as $key => $value) {
                    if (isset($GET['newsletter']) && $value['value'] == $GET['newsletter']) {
                        $newsletter[$key]['selected'] = 'selected';
                    }
                }
                $this->view->filters->newsletter = $newsletter;
            }

            $status = [
                [
                    'name' => TEXT_ALL,
                    'value' => '',
                    'selected' => '',
                ],
                [
                    'name' => TEXT_ACTIVE,
                    'value' => 'y',
                    'selected' => '',
                ],
                [
                    'name' => TEXT_NOT_ACTIVE,
                    'value' => 'n',
                    'selected' => '',
                ],
            ];
            foreach ($status as $key => $value) {
                if (isset($GET['status']) && $value['value'] == $GET['status']) {
                    $status[$key]['selected'] = 'selected';
                }
            }
            $this->view->filters->status = $status;

            $title = [
                [
                    'name' => TEXT_ALL,
                    'value' => '',
                    'selected' => '',
                ],
                [
                    'name' => T_MR,
                    'value' => 'm',
                    'selected' => '',
                ],
                [
                    'name' => T_MRS,
                    'value' => 'f',
                    'selected' => '',
                ],
                [
                    'name' => T_MISS,
                    'value' => 's',
                    'selected' => '',
                ],
            ];
            foreach ($title as $key => $value) {
                if (isset($GET['title']) && $value['value'] == $GET['title']) {
                    $title[$key]['selected'] = 'selected';
                }
            }
            $this->view->filters->title = $title;

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

            $this->view->filters->platform = array();
            if (isset($GET['platform']) && is_array($GET['platform'])) {
                foreach ($GET['platform'] as $_platform_id)
                    if ((int) $_platform_id > 0)
                        $this->view->filters->platform[] = (int) $_platform_id;
            }
        
        }// customers end
        
        $departments = false;
        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $this->view->filters->departments = [];
            if (isset($GET['departments']) && is_array($GET['departments'])) {
                foreach ($GET['departments'] as $_department_id)
                    if ((int) $_department_id > 0)
                        $this->view->filters->departments[] = (int) $_department_id;
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
            'messages' => $messages,
            'filters' => $filters,
            'filter_selected' => $filter_selected,
            'isMultiPlatform' => \common\classes\platform::isMulti(),
            'platforms' => \common\classes\platform::getList(true, true),
            'departments' => $departments,
            'ordersStatuses' => $ordersStatuses,
            'ordersStatusesOptions' => $ordersStatusesOptions,
        ]);
    }
    
    public function actionReset() {
        $type = Yii::$app->request->post('type');
        switch ($type) {
            case 'orders':
                $data = Yii::$app->request->post();
                unset($data['type']);
                $AdminFilters = \common\models\AdminFilters::findOne(['filter_type' => 'orders']);
                if ($AdminFilters instanceof \common\models\AdminFilters) {
                    $AdminFilters->delete();
                }
                $response = ['message' => 'Orders filter dropped'];
                break;
            case 'customers':
                $data = Yii::$app->request->post();
                unset($data['type']);
                $AdminFilters = \common\models\AdminFilters::findOne(['filter_type' => 'customers']);
                if ($AdminFilters instanceof \common\models\AdminFilters) {
                    $AdminFilters->delete();
                }
                $response = ['message' => 'Customers filter dropped'];
                break;
            default:
                $response = ['message' => 'Unknown type'];
                break;
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }
        
    public function actionSave() {
        $type = Yii::$app->request->post('type');
        switch ($type) {
            case 'orders':
                $data = Yii::$app->request->post();
                unset($data['type']);
                $AdminFilters = \common\models\AdminFilters::findOne(['filter_type' => 'orders']);
                if (!($AdminFilters instanceof \common\models\AdminFilters)) {
                    $AdminFilters = new \common\models\AdminFilters();
                    $AdminFilters->loadDefaultValues();
                    $AdminFilters->filter_type = 'orders';
                }
                $AdminFilters->filter_data = \Opis\Closure\serialize($data);
                $AdminFilters->save(false);
                $response = ['message' => 'Orders filter saved'];
                break;
            case 'customers':
                $data = Yii::$app->request->post();
                unset($data['type']);
                $AdminFilters = \common\models\AdminFilters::findOne(['filter_type' => 'customers']);
                if (!($AdminFilters instanceof \common\models\AdminFilters)) {
                    $AdminFilters = new \common\models\AdminFilters();
                    $AdminFilters->loadDefaultValues();
                    $AdminFilters->filter_type = 'customers';
                }
                $AdminFilters->filter_data = \Opis\Closure\serialize($data);
                $AdminFilters->save(false);
                $response = ['message' => 'Customers filter saved'];
                break;
            default:
                $response = ['message' => 'Unknown type'];
                break;
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

}
