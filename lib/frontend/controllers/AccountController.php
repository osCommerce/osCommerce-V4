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

namespace frontend\controllers;

use common\models\AddressBook;
use common\models\Admin;
use common\models\Cities;
use common\models\Customers;
use common\models\CustomersCreditHistory;
use common\models\CustomersInfo;
use common\models\DesignBoxes;
use common\models\Orders;
use common\models\OrdersProducts;
use common\models\OrdersStatus;
use common\models\Products;
use common\models\RegularOffers;
use common\models\Reviews;
use common\models\TrackingNumbersToOrdersProducts;
use common\models\Zones;
use common\models\ThemesSettings;
use frontend\design\Info;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\Session;
use common\helpers\Password;
use common\classes\opc;
use frontend\design\SplitPageResults;
use common\classes\Images;
use common\components\Customer;
use common\components\Socials;
use common\helpers\Date as DateHelper;
use frontend\forms\registration\CustomerRegistration;
use common\forms\AddressForm;
use function GuzzleHttp\Psr7\str;

/**
 * Site controller
 */
class AccountController extends Sceleton
{

    private $use_social = false;
    private $forever = false;
    private $couponsRepository;
    private $customerRepository;

    public function __construct($id, $module)
    {
        parent::__construct($id, $module);

        $platform_config = new \common\classes\platform_config(\common\classes\platform::currentId());

        $this->use_social = $platform_config->checkNeedSocials();
        if ($this->use_social){
            \common\components\Socials::loadComponents(PLATFORM_ID);
        }
        \common\helpers\Translation::init('checkout');
    }

    public function actionIndex()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        global $breadcrumb;
        global $cart;

        \common\helpers\Translation::init('account/history');
        $messageStack = \Yii::$container->get('message_stack');
        $currencies = Yii::$container->get('currencies');

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));

        $this->checkIsGuest();

        if ($messageStack->size('account') > 0) {
            $account_links['message'] = '<div class="main">' . $messageStack->output('account') . '</div>';
        }


        $customer = Yii::$app->user->getIdentity();
        $customer_id = $customer->customers_id;
        $customer_default_address_id = $customer->get('customer_default_address_id');

        $topAcc = array();
        $topAcc['credit_amount'] = $currencies->format($customer->credit_amount);
        $topAcc['count_credit_amount'] = $customer->credit_amount;

        /**
         * @var $GroupAdministrator \common\extensions\GroupAdministrator\GroupAdministrator
         */
        if ($GroupAdministrator = \common\helpers\Extensions::isAllowed('GroupAdministrator')) {
            $cIds = $GroupAdministrator::getCustomerIdsByAdministrator((int) $customer_id);
        } else {
            $cIds = [$customer_id];
        }

        $cOrders = \common\models\Orders::find()->alias('o')->joinWith(['ordersTotals ot' => function (\yii\db\ActiveQuery $query){
            $query->andWhere(['ot.class' => 'ot_total']);
        }])->where(['IN', 'customers_id', $cIds]);

        if (USE_MARKET_PRICES == 'True'){
            $cOrders->andWhere(['o.currency' => Yii::$app->request->get('currency') ?? DEFAULT_CURRENCY ]);
        }
        $topAcc['total_sum'] = $currencies->format($cOrders->sum('ot.value_inc_tax'));
        $topAcc['total_orders'] = $cOrders->count();
        $lastPurchased = $cOrders->max('date_purchased');
        $topAcc['last_purchased'] = DateHelper::date_long($lastPurchased);
        $topAcc['last_purchased_days'] = DateHelper::getDateRange(date('Y-m-d'), $lastPurchased);
        $topAcc['customer_points'] = $customer->customers_bonus_points;
        $topAcc['has_customer_points_history'] = ($extBonusActions = \common\helpers\Extensions::isAllowed('BonusActions')) && $extBonusActions::hasBonusHistory($customer_id);
        $orders = $cOrders->orderBy('o.orders_id desc')->all();

        $regular_offers_value = 0;

        $regular_offers = \common\models\RegularOffers::find()->select(['customers_id', 'period'])->andWhere(['customers_id' => (int) $customer_id])->one();

        if (isset($regular_offers->period)) {
            $regular_offers_value = $regular_offers->period;
        }

        if (Info::themeSetting('customer_account') == 'new') {

            $order_id = (int)Yii::$app->request->get('order_id');
            $orders_id = (int)Yii::$app->request->get('orders_id');
            /* in some widgets are used order_id and some orders_id */
            if (!$order_id && $orders_id) {
                $order_id = $orders_id;
            }
            if ($orders_id && $orders_id != $order_id) {
                tep_redirect(tep_href_link('account'));
            }
            if ($order_id && !Info::isAdmin()) {
                if (!in_array($order_id, \yii\helpers\ArrayHelper::getColumn($orders, 'orders_id'))) {
                    die('oops');
                    tep_redirect(tep_href_link('account'));
                }
            }

            \common\helpers\Translation::init('account/address-book');
            \common\helpers\Translation::init('account/address-book-process');
            \common\helpers\Translation::init('account/create');
            \common\helpers\Translation::init('account/create-success');
            \common\helpers\Translation::init('account/download');
            \common\helpers\Translation::init('account/edit');
            \common\helpers\Translation::init('account/history');
            \common\helpers\Translation::init('account/history-info');
            \common\helpers\Translation::init('account/newsletters');
            \common\helpers\Translation::init('account/products-reviews');
            \common\helpers\Translation::init('account/password');
            \common\helpers\Translation::init('account/quotation-history');
            \common\helpers\Translation::init('account/quotation-history-info');
            \common\helpers\Translation::init('account/update');
            \common\helpers\Translation::init('account/wishlist');

            $page_name = tep_db_prepare_input(Yii::$app->request->get('page_name'));
            if (!$page_name) {
                $page_name = 'account';
            }
            $page_name = \common\classes\design::pageName($page_name);
            Info::addBlockToPageName($page_name);

            return $this->render('main.tpl', [
                'page_name' => $page_name,
                'params' => [
                    'mainData' => $topAcc,
                    'customer' => $customer,
                    'regular_offers' => $regular_offers_value
                ]
            ]);
        }

        $account_links['account_history_array'] = '';
        if ($orders) {
            $account_links['account_history_array'] .= '<h2>' . OVERVIEW_TITLE . '&nbsp;&nbsp;<a href="' . tep_href_link('account/history', '', 'SSL') . '">' . OVERVIEW_SHOW_ALL_ORDERS . '</a></h2>';
            $account_links['account_history_array'] .= '';
            $account_orders = array();
            $account_links['account_history_array'] .= '<div class="contentBoxContents"><strong class="box-title">' . OVERVIEW_PREVIOUS_ORDERS . '</strong><table class="orders-table">';

            foreach($orders as $limit => $order){
                $cOrder = $order->getAttributes();
                if ($limit > 2) break;
                if (tep_not_null($order->delivery_name)) {
                    $order_name = $order->delivery_name;
                    $cOrder['country'] = $order->delivery_country;
                } else {
                    $order_name = $order->billing_name;
                    $cOrder['country'] = $order->billing_country;
                }
                $cOrder['order_total'] = (isset($order->ordersTotals[0])? $order->ordersTotals[0]->text : '');
                $cOrder['date'] = DateHelper::date_long($order->date_purchased, "%e %b %G");
                $_status = $order->getOrdersStatus()->one();
                $cOrder['orders_status_name'] = $_status ? $_status->orders_status_name : '';
                $cOrder['name'] = \common\helpers\Output::output_string_protected($order_name);
                $cOrder['products'] = $order->getOrdersProducts()->count();
                $cOrder['view'] = tep_href_link('account/history-info', 'order_id=' . $order->orders_id, 'SSL');
                $cOrder['reorder_link'] = tep_href_link('checkout/reorder', 'order_id=' . $order->orders_id, 'SSL');
                $cOrder['reorder_confirm'] = ($cart->count_contents() > 0 ? REORDER_CART_MERGE_WARN : '');

                $pay_link = false;
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
                    $pay_link = $ext::payLink($order->orders_id);
                }
                $cOrder['pay_link'] = $pay_link;
                $account_orders[] = $cOrder;
            }
            $account_links['account_history_array'] .= '</table></div>';
        }

        $account_reviews = array();
      $account_reviews_more_link = false;

      $customer_reviewActive = \common\models\Reviews::find()
          ->alias('r')
          ->innerJoinWith(['product p'])
          ->where(['customers_id' => (int) $customer_id])
          ->orderBy(['reviews_id' => SORT_DESC])
          ->limit(4)
          ->all();

        foreach ($customer_reviewActive as $customerReview) {
            if (count($account_reviews) == 3) {
                $account_reviews_more_link = tep_href_link('account/products-reviews', '', 'SSL');
                continue;
            }
            $customer_review['products_link'] = '';
            if (\common\helpers\Product::check_product($customerReview->products_id)) {
                $customer_review['products_link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $customerReview->products_id, '');
            }
            $customer_review['products_name'] = \common\helpers\Product::get_products_name($customerReview->products_id);
            $customer_review['reviews_rating'] = $customerReview->reviews_rating;
            $customer_review['date_added_str'] = DateHelper::date_short($customerReview->date_added);
            if ($customer_review['status']) {
                $customer_review['status_name'] = TEXT_REVIEW_STATUS_APPROVED;
            } else {
                $customer_review['status_name'] = TEXT_REVIEW_STATUS_NOT_APPROVED;
            }
            $customer_review['view'] = tep_href_link('reviews/info', 'reviews_id=' . $customerReview->reviews_id . '&back=' . FILENAME_ACCOUNT);
            $account_reviews[] = $customer_review;
        }
      /*wishlist*/

//      for ($i=0, $n=sizeof($products_wishlist); $i<$n; $i++) {
//        $products_wishlist[$i]['image'] = Images::getImageUrl($products_wishlist[$i]['id'], 'Small');
//        $products_wishlist[$i]['link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products_wishlist[$i]['id']);
//        $products_wishlist[$i]['final_price_formatted'] = $currencies->display_price($products_wishlist[$i]['final_price'], \common\helpers\Tax::get_tax_rate($products_wishlist[$i]['tax_class_id']));
//        $products_wishlist[$i]['remove_link'] = tep_href_link(FILENAME_WISHLIST,'products_id=' . $products_wishlist[$i]['id'].'&action=remove_wishlist','SSL');
//        $products_wishlist[$i]['move_in_cart'] = tep_href_link(FILENAME_WISHLIST,'products_id=' . $products_wishlist[$i]['id'].'&action=wishlist_move_to_cart','SSL');
//      }
      /*wishlist*/
      /*subscription*/
        $subscriptions = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Subscriptions', 'allowed')) {
            $subscriptions = $ext::getSubscriptions(3);
        }
      /*subscription*/

      /*quotations*/
        $quotations = [];
        if($ext = \common\helpers\Acl::checkExtensionAllowed('Quotations', 'allowed')){
            $quotations = $ext::getQuotationList($customer_id, $languages_id);
        }
      /*quotations*/

        /*samples*/
        $samples = [];
        if($ext = \common\helpers\Acl::checkExtensionAllowed('Samples', 'allowed')){
            $samples = $ext::getSamplesList($customer_id, $languages_id);
        }
      /*samples*/

        /*bonus points*/
        $showBonusPart = \common\helpers\Acl::checkExtensionAllowed('BonusActions');
        /*bonus points*/

        $priamry_address = \common\helpers\Address::address_label($customer_id, $customer_default_address_id, true, ' ', '<br>');
        $account_links['acount_edit'] = tep_href_link('account/edit', '', 'SSL');
        $account_links['address_book_edit'] = tep_href_link('account/address-book-process', 'edit=' . $customer_default_address_id, 'SSL');
        $account_links['address_book'] = tep_href_link('account/address-book', '', 'SSL');
        $account_links['account_password'] = tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL');
        $account_links['wishlist'] = ''; // remove? was: tep_href_link(FILENAME_WISHLIST, '','SSL');
        $account_links['account_logoff'] = tep_href_link(FILENAME_LOGOFF, '');
        $account_links['account_history'] = tep_href_link('account/history', '', 'SSL');
        $account_links['account_newsletters'] = tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL');
        $account_links['account_notifications'] = tep_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL');


        return $this->render('index.tpl', [
            'description' => '',
            'account_links' => $account_links,
            'subscriptions' => $subscriptions,
            'quotations' => $quotations,
            'samples' => $samples,
            'account_orders' => $account_orders,
            'topAcc' => $topAcc,
            'customers' => $customer,
            'priamry_address' => $priamry_address,
            'account_reviews' => $account_reviews,
            'account_reviews_more_link' => $account_reviews_more_link,
            'products_wishlist' => [],
            'showBonusPart' => $showBonusPart,
            'regular_offers' => $regular_offers_value,
        ]);
    }

  /* ???? */
    public function actionSuccess()
    {
        $this->accountRedirect('Created success');

        return $this->render('success.tpl', ['description' => '']);
    }


    public function actionLogin()
    {
        global $cart, $navigation;

        if (!Yii::$app->user->isGuest){
            tep_redirect(tep_href_link('account/index', '', 'SSL'));
        }

        $messageStack = \Yii::$container->get('message_stack');
        \common\helpers\Translation::init('js');
        \common\helpers\Translation::init('checkout/login');

        global $breadcrumb;

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE,tep_href_link('account/login','','SSL'));

        if (Yii::$app->request->isPost){
            $form = Yii::$app->request->post('scenario');
            if ( $form == 'registration' ) {
                return $this->actionCreate();
            }
        }

        $params = [
                    'action' => tep_href_link('account/login', 'action=process', 'SSL'),
                    'account_create_action' => tep_href_link('account/login', 'action=process', 'SSL'),
                    'customers_newsletter' => true,//gdpr
                    'messages_account_create' => '',
                    'show_socials' => $this->use_social,
                    ];
        $authContainer = new \frontend\forms\registration\AuthContainer();
        $params['enterModels'] = $authContainer->getForms('account/create');
        $params['showAddress'] = $authContainer->isShowAddress();

        if (Yii::$app->request->isPost){

            $scenario = Yii::$app->request->post('scenario');

            $authContainer->loadScenario($scenario);

            if (!$authContainer->hasErrors()){

                if ($ext = \common\helpers\Extensions::isAllowed('UserTwoStepAuth')) {
                    $ext::checkLogin();
                }

                $customer_id = Yii::$app->user->getId();
                if ($customer_id > 0) {
                    $AddressBooks = \common\models\AddressBook::find()
                            ->where(['customers_id' => $customer_id])
                            ->andWhere('drop_ship > 0')
                            ->all();
                    if (is_array($AddressBooks)) {
                        foreach ($AddressBooks as $AB) {
                            tep_db_query('UPDATE orders SET drop_ship=1, delivery_address_book_id=0 WHERE delivery_address_book_id='.$AB->address_book_id);
                            $AB->delete();
                        }
                    }
                    unset($AddressBooks);
                }
                unset($customer_id);

                foreach (\common\helpers\Hooks::getList('frontend/account/login-success') as $filename) {
                    include($filename);
                }

                if (sizeof($navigation->snapshot) > 0 && !Yii::$app->request->post('reviews')) {
                    if (is_array($navigation->snapshot['get'])){
                        $origin_href = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
                    } else {
                        $origin_href = tep_href_link($navigation->snapshot['page'], $navigation->snapshot['get'], $navigation->snapshot['mode']);
                    }
                    $navigation->clear_snapshot();
                    return $this->redirect($origin_href);
                } else {
                    if (Yii::$app->request->isAjax){
                        if (strpos($_SERVER['HTTP_REFERER'], 'logoff') !== false){
                            return 'gt';
                        } else {
                            return 'ok';
                        }
                    } else {
                        return $this->redirect(tep_href_link(FILENAME_ACCOUNT));
                    }
                }
            } else {
                foreach ($authContainer->getErrors($scenario) as $error){
                    if (Yii::$app->request->isAjax) {
                        $messageStack->add_session((is_array($error)? implode("<br>", $error): $error), $scenario);
                    } else {
                        $messageStack->add((is_array($error)? implode("<br>", $error): $error), $scenario);
                    }
                }
                if (Yii::$app->request->isAjax) {
                    $messageStack->add_session('<a href="' . tep_href_link('account/password-forgotten', '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN_S . '</a>', 'login');
                } else{
                    $messageStack->add('<a href="' . tep_href_link('account/password-forgotten', '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN_S . '</a>', 'login');
                }
                $messages = '';
                if ($messageStack->size($scenario)>0){
                    $messages = $messageStack->output($scenario);
                }
                $params['messages_'.$scenario] = $messages;

                if (Yii::$app->request->isAjax){
                    return \frontend\design\boxes\login\Returning::widget(['params' => [
                        'enterModels' => $authContainer->getForms('account/login-box'),
                        'action' => tep_href_link('account/login', 'action=process', 'SSL'),
                        'messages_login' => $messages,
                    ]]);
                }
            }
        }
        if ($messageStack->size('login')>0){
            $params['messages_login'] = $messageStack->output('login');
        }

        $loginView = Info::themeSetting('login_view');
        if (!$loginView) {
            $loginView = 'login';
        }

        $check = \common\models\DesignBoxes::find()
            ->select(['id'])
            ->andWhere(['block_name' => 'login_account', 'theme_name' => THEME_NAME])
            ->one();

        if ($check['id']??null || Info::isAdmin()) {
            return $this->render('login-widgets.tpl', [
                'params' => $params,
            ]);
        }

        return $this->render($loginView . '.tpl', ['params' => $params, 'settings' => ['tabsManually' => true]]);

    }

    public function actionCreate() {
        global $cart, $navigation;

        if (!Yii::$app->user->isGuest){
            tep_redirect(tep_href_link('account/index', '', 'SSL'));
        }

        \common\helpers\Translation::init('account/create');
        \common\helpers\Translation::init('account/login');
        \common\helpers\Translation::init('js');

        $params = [
            'action' => tep_href_link('account/login', 'action=process', 'SSL'),
            'create_tab_active' => true,
            'show_socials' => $this->use_social,
        ];

        if ($wExt = \common\helpers\Acl::checkExtensionAllowed('WeddingRegistry', 'allowed')){
            $wExt::registerPartner($params);
        }

        $authContainer = new \frontend\forms\registration\AuthContainer();
        $params['enterModels'] = $authContainer->getForms('account/create');
        $params['showAddress'] = $authContainer->isShowAddress();

        if (Yii::$app->request->isPost){
            $scenario = Yii::$app->request->post('scenario');

            $authContainer->loadScenario($scenario);

            if (!$authContainer->hasErrors()){
                if ($customer_id = Yii::$app->user->getId() ){

                    foreach (\common\helpers\Hooks::getList('frontend/account/create-success') as $filename) {
                        include($filename);
                    }

                    if ($wExt = \common\helpers\Acl::checkExtensionAllowed('WeddingRegistry', 'allowed')){
                        //if register via wedding registry partner invite
                        $wExt::processWeddingRegistryInviting();

                    }
                    // seems not used
                    // $fields = Yii::$app->request->post('field');
                    // if (\common\helpers\Acl::checkExtensionAllowed('TradeForm')) {
                    //     \common\extensions\TradeForm\helpers\TradeForm::saveAdditionalFields($fields, $customer_id);
                    // }
                }

                if (sizeof($navigation->snapshot) > 0) {
                    $origin_href = tep_href_link($navigation->snapshot['page'], \common\helpers\Output::array_to_string($navigation->snapshot['get'], array(tep_session_name())), $navigation->snapshot['mode']);
                    $navigation->clear_snapshot();
                    tep_redirect($origin_href);
                } else {
                    /** @var \common\extensions\TradeForm\TradeForm $ext*/
                    if (($ext = \common\helpers\Acl::checkExtensionAllowed('TradeForm')) && method_exists($ext, 'optIsMandatoryTradeForm') && $ext::optIsMandatoryTradeForm()) {
                        $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['trade-form/', 'create' => 1]));
                    } else {
                        tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
                    }
                }
            } else {
                $messageStack = \Yii::$container->get('message_stack');
                foreach ($authContainer->getErrors($scenario) as $error){
                    if (Yii::$app->request->isAjax) {
                        $messageStack->add_session((is_array($error)? implode("<br>", $error): $error), $scenario);
                    } else {
                        $messageStack->add((is_array($error)? implode("<br>", $error): $error), $scenario);
                    }
                }
                $messages = '';
                if ($messageStack->size($scenario) > 0){
                    $messages = $messageStack->output($scenario);
                }
                $params['messages_'.$scenario] = $messages;
            }
        }

        $check = \common\models\DesignBoxes::find()
            ->select(['id'])
            ->andWhere(['block_name' => 'login_account', 'theme_name' => THEME_NAME])
            ->one();

        if ($check['id'] || Info::isAdmin()) {
            return $this->render('login-widgets.tpl', [
                'params' => $params,
            ]);
        }

        $loginView = Info::themeSetting('login_view');
        if (!$loginView) {
            $loginView = 'login';
        }

        return $this->render($loginView . '.tpl', ['params' => $params, 'settings' => ['tabsManually' => true]]);
    }

    public function actionCreateSuccess(){
        $this->accountRedirect('Created success');
      global $cart;

      global $breadcrumb;
      $breadcrumb->add(TEXT_MY_ACCOUNT);
      $breadcrumb->add(NAVBAR_TITLE_2);

      //$after_create_go = tep_href_link(FILENAME_ACCOUNT, '', 'SSL');
      $after_create_go = tep_href_link(FILENAME_DEFAULT, '', 'SSL');
      if ($cart->count_contents() >= 1) {
        $after_create_go = tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL');
      }

      return $this->render('success.tpl', [
        'title' => HEADING_TITLE,
        'description' => sprintf(TEXT_ACCOUNT_CREATED, tep_href_link(FILENAME_CONTACT_US), tep_href_link(FILENAME_CONTACT_US)),
        'next_page' => $after_create_go,
      ]);
    }

    public function actionLogoff()
    {
        $this->accountRedirect('Logoff');
      global $breadcrumb, $cart;

      if (!Yii::$app->user->isGuest){
        \Yii::$app->settings->clear();

        $customer_id = Yii::$app->user->getId();
        if ($customer_id > 0) {
            $AddressBooks = \common\models\AddressBook::find()
                    ->where(['customers_id' => $customer_id])
                    ->andWhere('drop_ship > 0')
                    ->all();
            if (is_array($AddressBooks)) {
                foreach ($AddressBooks as $AB) {
                    tep_db_query('UPDATE orders SET drop_ship=1, delivery_address_book_id=0 WHERE delivery_address_book_id='.$AB->address_book_id);
                    $AB->delete();
                }
            }
            unset($AddressBooks);
        }
        unset($customer_id);

        Yii::$app->user->getIdentity()->logoffCustomer();

        //$customer_groups_id = DEFAULT_USER_GROUP;
        $cart->reset();

        if ($this->forever){
            return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['account/logoff', 'forever' => 1]));
        } else {
            return $this->redirect('logoff');
        }
      }

        $title = HEADING_TITLE;
        $breadcrumb->add(NAVBAR_TITLE);
        $forever = (int)Yii::$app->request->get('forever');
        if ($forever) {
            $this->forever = $forever;
            $title = ACCOUNT_DELETE_TITLE;
        }

        $widgets = false;

        $check = \common\models\DesignBoxes::find()
            ->select(['id'])
            ->andWhere(['block_name' => 'logoff', 'theme_name' => THEME_NAME])
            ->one();

        if ($check['id'] || Info::isAdmin()) {
            $widgets = true;
        }
        return $this->render('logoff.tpl', [
            'link_continue_href' => tep_href_link(FILENAME_DEFAULT,'','NONSSL'),
            'forever' => $this->forever,
            'title' => $title,
            'widgets' => $widgets
        ]);
    }

    public function actionLoginMe()
    {
        global $navigation;
        if ( is_object($navigation) && method_exists($navigation,'remove_current_page') ) {
            $navigation->remove_current_page();
        }
        /*if (!\frontend\design\Info::isAdmin()){
            return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['account/login','error'=>'access']));
        }*/
        \Yii::$app->urlManager->setOverrideSettings(['seo_url_parts_currency'=>false, 'seo_url_parts_language'=>false]);

        $aup = base64_decode(str_replace(' ','+',Yii::$app->request->get('aup','')));
        $aup = \common\helpers\Password::decryptAuthUserParam($aup);
        if ( $aup ) {
            $cId = $aup['customers_id'];
            $customerEmail = $aup['customers_email'];
            $auth_type = $aup['auth_type'];
            $auth_key = $aup['auth_key'];

            Yii::$app->settings->clear();
            if (!Yii::$app->user->isGuest){
                Yii::$app->user->logout();
            }
            if ( Yii::$app->getSession()->getIsActive() ) {
                Yii::$app->getSession()->destroy();
            }
            Yii::$app->getSession()->regenerateID();

            Yii::$app->getSession()->open();

            $platformConfig = \Yii::$app->get('platform')->config();
            $currency = $platformConfig->getDefaultCurrency();
            $lng = new \common\classes\language();
            $lng->set_language($platformConfig->getDefaultLanguage());
            global $languages_id;
            $languages_id = $lng->language['id'];
            \Yii::$app->settings->set('currency_id', \common\helpers\Currencies::getCurrencyId($currency));
            \Yii::$app->settings->set('currency', $currency);
            \Yii::$app->settings->set('locale', $lng->language['locale']);
            \Yii::$app->settings->set('languages_id', $languages_id);

            global $cart, $multi_cart, $quote, $sample;
            unset($multi_cart);
            unset($quote);
            unset($sample);
            $cart = new \common\classes\shopping_cart();
            Yii::$app->getSession()->set('cart',$cart);

            $cInfo = Customers::find()
                    ->where(['customers_id' => $cId])
                    ->andWhere(['auth_key' => $auth_key])
                    ->one();
            if (!($cInfo instanceof Customers)) {
                return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['account/login']));
            }

            $model = new \common\components\Customer(\common\components\Customer::LOGIN_WITHOUT_CHECK);

            if ($cId > 0) {
                $AddressBooks = \common\models\AddressBook::find()
                        ->where(['customers_id' => (int)$cId])
                        ->andWhere('drop_ship > 0')
                        ->all();
                if (is_array($AddressBooks)) {
                    foreach ($AddressBooks as $AB) {
                        tep_db_query('UPDATE orders SET drop_ship=1, delivery_address_book_id=0 WHERE delivery_address_book_id='.$AB->address_book_id);
                        $AB->delete();
                    }
                }
                unset($AddressBooks);
            }

            $passLogin = false;
            if ( $auth_type=='payment' && !empty($customerEmail) && $model->loginCustomer($customerEmail, $cId) ) {
                $passLogin = true;
            } elseif ( $auth_type=='payment' && $model->loginCustomerById($cId) ) {
                $passLogin = true;
            }
            if ($passLogin) {
                if (\Yii::$app->request->get('payer', false)) {
                    \Yii::$app->settings->set('from_admin', true);
                    return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['payer/order-pay', 'order_id' => \Yii::$app->request->get('order_id', '')]));
                } else {
                    return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['account/index']));
                }
            } elseif ( $auth_type=='login' && $model->loginCustomer($customerEmail, $cId) ) {
                return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['account/index']));
            }
        }
        return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['account/login']));
    }


    public function actionGeneratePassword()
    {
        if (Yii::$app->request->isAjax) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \common\helpers\Password::randomize(true);
        }

    }

    public function actionPassword()
    {
        $this->accountRedirect('My password');
      global $navigation;
      global $breadcrumb;

      $this->checkIsGuest();

      $customer_id = Yii::$app->user->getId();

      $messageStack = \Yii::$container->get('message_stack');
      $error = false;
      if ( Yii::$app->request->isPost /*isset($_POST['action']) && ($_POST['action'] == 'process')*/) {
        $password_current = tep_db_prepare_input($_POST['password_current']);
        $password_new = tep_db_prepare_input($_POST['password_new']);
        $password_confirmation = tep_db_prepare_input($_POST['password_confirmation']);

        if (strlen($password_current) < ENTRY_PASSWORD_MIN_LENGTH) {
          $error = true;

          $messageStack->add(ENTRY_PASSWORD_CURRENT_ERROR, 'account_password');
        } elseif (strlen($password_new) < ENTRY_PASSWORD_MIN_LENGTH) {
          $error = true;

          $messageStack->add(ENTRY_PASSWORD_NEW_ERROR, 'account_password');
        } elseif ($password_new != $password_confirmation) {
          $error = true;

          $messageStack->add(ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING, 'account_password');
        }

        if ($error == false) {
            $check_customer = Customers::find()
              ->select(['customers_password'])
              ->where(['customers_id' => (int)$customer_id])
              ->limit(1)
              ->asArray()
              ->one();
          if (\common\helpers\Password::validate_password($password_current, $check_customer['customers_password'], 'frontend')) {
              // Update Customer
              $customer = Customers::findOne($customer_id);
              $customer->editCustomersPassword(Password::encrypt_password($password_new, 'frontend'));
              Yii::$app->getSession()->set(Yii::$app->user->authKeyParam, $customer->auth_key);
              // Update CUSTOMERS_INFO
              $customerInfo = CustomersInfo::findOne($customer_id);
              $customerInfo->editCustomersInfoDateAccountLastModified();

              if (!Yii::$app->request->isAjax) {
                  $messageStack->add_session(SUCCESS_PASSWORD_UPDATED, 'account_password', 'success');
                  tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
              } else {
                  $messageStack->add(SUCCESS_PASSWORD_UPDATED, 'account_password', 'success');
              }
          } else {
            $error = true;

            $messageStack->add(ERROR_CURRENT_PASSWORD_NOT_MATCHING, 'account_password');
          }
        }
          if (Yii::$app->request->isAjax) {
              return json_encode($messageStack->asArray('account_password'));
          }
      }


      $message_account_password = '';
      if ($messageStack->size('account_password')>0) {
        $message_account_password = $messageStack->output('account_password');
      }

      $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
      $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL'));

      return $this->render('password.tpl',[
        'account_password_action' => ['account/password', 'action'=>'process'],
        'link_back_href' => tep_href_link(FILENAME_ACCOUNT, '', 'SSL'),
        'message_account_password' => $message_account_password,
      ]);

    }

    public function actionPasswordForgotten()
    {
        $this->accountRedirect('Password forgotten');
        global $breadcrumb;
        $messageStack = \Yii::$container->get('message_stack');
        $email_address = '';

        $loginModel = new \backend\forms\Login(['captha_enabled' => true]);

        if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
            $cError = false;
            $errorMessage = '';
            if ($loginModel->captha_enabled) {
                if (\Yii::$app->request->isPost && \Yii::$app->request->post('action', '') !== 'authorize') {
                    if ($loginModel->load(Yii::$app->request->post()) && $loginModel->validate()){
                        if ($loginModel->hasErrors()) {
                            $errorMessage = '';
                            foreach($loginModel->getErrors() as $error){
                                if (is_array($error)) {
                                    $errorMessage .= implode(", ", $error);
                                } else if (is_string($error)) {
                                    $errorMessage .= $error;
                                }
                            }
                            $cError = true;
                        }
                    } else {
                        if ($loginModel->hasErrors()) {
                            $errorMessage = '';
                            foreach($loginModel->getErrors() as $error){
                                if (is_array($error)) {
                                    $errorMessage .= implode(", ", $error);
                                } else if (is_string($error)) {
                                    $errorMessage .= $error;
                                }
                            }


                        }
                        $cError = true;
                    }
                }
            }

            $email_address = (string)tep_db_prepare_input($_POST['email_address']);
            if ($cError) {
                $check_customer = false;
            } else if ( empty($email_address) ) {
                $check_customer = false;
            } else {
                $check_customer = Customers::find()
                  ->select(['customers_firstname', 'customers_lastname', 'customers_password', 'customers_id', 'opc_temp_account'])
                  ->where(['customers_email_address' => tep_db_input($email_address)])
                  ->orderBy(['opc_temp_account' => SORT_ASC])
                  ->asArray()
                  ->limit(1)
                  ->one();
            }
            if (is_array($check_customer)) {
              if ( opc::is_temp_customer($check_customer['customers_id']) ){
                $messageStack->add(TEXT_NO_EMAIL_ADDRESS_FOUND, 'password_forgotten');
              }else{
                // {{
                $email_params = array();
                $email_params['STORE_NAME'] = STORE_NAME;
                $email_params['IP_ADDRESS'] = \common\helpers\System::get_ip_address();
                if (defined('PASSWORD_FORGOTTEN_MODE') && PASSWORD_FORGOTTEN_MODE == 'invite'){
                    $cInfo = \common\models\CustomersInfo::findOne($check_customer['customers_id']);
                    if ($cInfo){
                        $cInfo->updateToken();
                        $email_params['NEW_PASSWORD'] = \yii\helpers\Html::a(TEXT_PASSWORD_INVITATION_LINK, tep_href_link('account/new-password', 'token='.$cInfo->getToken(), 'SSL'));
                    } else {
                        $email_params['NEW_PASSWORD'] = '';
                    }
                } else {
                    $new_password = \common\helpers\Password::randomize(true);
                    $crypted_password = \common\helpers\Password::encrypt_password($new_password, 'frontend');
                    $customer = Customers::findOne((int)$check_customer['customers_id']);
                    $customer->editCustomersPassword(tep_db_input($crypted_password));
                    $email_params['NEW_PASSWORD'] = $new_password;
                }
                $email_params['CUSTOMER_FIRSTNAME'] = $check_customer['customers_firstname'];
                $e = explode("://", HTTP_SERVER);
                $email_params['HTTP_HOST'] = '<a href="' . HTTP_SERVER . DIR_WS_HTTP_CATALOG . '">' . $e[1] . '</a>';
                $email_params['CUSTOMER_EMAIL'] = $email_address;
                list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Password Forgotten', $email_params);
                // }}
                \common\helpers\Mail::send($check_customer['customers_firstname'] . ' ' . $check_customer['customers_lastname'], $email_address, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_params);

                if (defined('PASSWORD_FORGOTTEN_MODE') && PASSWORD_FORGOTTEN_MODE == 'invite'){
                    $messageStack->add_session(SUCCESS_PASSWORD_INVITATION_SENT, 'login', 'success');
                } else {
                    $messageStack->add_session(SUCCESS_PASSWORD_SENT, 'login', 'success');
                }

                foreach (\common\helpers\Hooks::getList('frontend/account/password-forgotten') as $filename) {
                    include($filename);
                }

                if (!Yii::$app->request->isAjax) {
                    tep_redirect(tep_href_link('account/login', '', 'SSL'));
                }
              }
            } else if ($cError) {
                $messageStack->add($errorMessage, 'password_forgotten');
            } else {
              $messageStack->add(TEXT_NO_EMAIL_ADDRESS_FOUND, 'password_forgotten');
            }
        }

      $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link('account/login', '', 'SSL'));
      $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link('account/password-forgotten', '', 'SSL'));

        $widgets = false;
        $check = DesignBoxes::find()
            ->select(['id'])
            ->where(['block_name' => 'password_forgotten'])
            ->andWhere(['theme_name' => THEME_NAME])
            ->limit(1)
            ->asArray()
            ->one();
        if ($check['id'] || Info::isAdmin()) {
            $widgets = true;
        }

        if (Yii::$app->request->isAjax && $messageStack->size('password_forgotten') > 0) {
            return json_encode($messageStack->asArray('password_forgotten'));
        } elseif (Yii::$app->request->isAjax) {
            return json_encode('ok');
        } else {
            $messages_password_forgotten = '';
            if ( $messageStack->size('password_forgotten')>0 ) {
              $messages_password_forgotten = $messageStack->output('password_forgotten');
            }
            return $this->render('password_forgotten.tpl',[
                'messages_password_forgotten' => $messages_password_forgotten,
                'email_address' => $email_address,
                'link_back_href' => tep_href_link(FILENAME_ACCOUNT, '', 'SSL'),
                'widgets' => $widgets,
                'loginModel' => $loginModel,
            ]);
        }
    }

  public function actionNewPassword()
  {
      //$this->accountRedirect('My password');
     $messageStack = \Yii::$container->get('message_stack');
     if (!Yii::$app->user->isGuest) {
      tep_redirect(tep_href_link('account', '', 'SSL'));
     }
     \common\helpers\Translation::init('account/password');
     $customer = new Customer();
     $message_account_password = '';
     if ( $messageStack->size('password_forgotten')>0 ) {
        $message_account_password = $messageStack->output('password_forgotten');
     }
     if(\Yii::$app->request->isPost){
        $password_new = \Yii::$app->request->post('password_new', null);
        $password_cnf = \Yii::$app->request->post('password_confirmation', null);
        $token = \Yii::$app->request->get('token', null);

        if (!$token){
            $messageStack->add_session('Invalid Token', 'password_forgotten');
            tep_redirect(tep_href_link('account/new-password', '', 'SSL'));
        }

        if (!$password_new || !$password_cnf || strcmp($password_new, $password_cnf) !== 0) {
            $messageStack->add_session('Invalid Password Compare', 'password_forgotten');
            tep_redirect(tep_href_link('account/new-password', 'token='.$token, 'SSL'));
        } else {
            $customer = $customer->getUserByToken($token);
            if ($customer){
                $customer->customers_password = \common\helpers\Password::encrypt_password($password_new, 'frontend');
                $customer->update(false);
                $customer->updateUserToken($customer->customers_id);
                \common\helpers\Session::deleteCustomerSessions($customer->customers_id);
                $messageStack->add_session('Password has been changed, Please Login', 'login', 'success');
                tep_redirect(tep_href_link('account/login', '', 'SSL'));
            }
        }
     } else {
        $token = \Yii::$app->request->get('token', null);
        if ($token){
            $customer = $customer->getUserByToken($token);
            if (!$customer){
                $token = null;
                $messageStack->add('Invalid Token', 'password_forgotten');
                $message_account_password = $messageStack->output('password_forgotten');
            }
        } else {
            $messageStack->add('Token required', 'password_forgotten');
            $message_account_password = $messageStack->output('password_forgotten');
        }
     }
     return $this->render('new-password', [
           'account_password_action' => ['account/new-password', 'action'=>'process', 'token' => $token],
           'token' => $token,
           'message_account_password' => $message_account_password,
        ]);
  }

  public function actionNewsletters()
  {
    global $navigation, $breadcrumb;

    $this->checkIsGuest();

    $messageStack = \Yii::$container->get('message_stack');
    $customer_id = Yii::$app->user->getId();
    $newsletter = Customers::find()
        ->select(['customers_id', 'customers_newsletter'])
        ->where(['customers_id' => (int)$customer_id])
        ->asArray()
        ->limit(1)
        ->one();

    if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
      if (isset($_POST['newsletter_general']) && is_numeric($_POST['newsletter_general'])) {
        $newsletter_general = tep_db_prepare_input($_POST['newsletter_general']);
      } else {
        $newsletter_general = '0';
      }

      if ($newsletter_general != $newsletter['customers_newsletter']) {
        $newsletter_general = (($newsletter['customers_newsletter'] == '1') ? '0' : '1');

        if ($newsletter_general == '1' && is_object($this->promoActionsObs)) { $this->promoActionsObs->triggerAction('signing_newsletter'); }
        $customer = Customers::findOne($customer_id);
        $customer->editCustomersNewsletter((int)$newsletter_general);
      }

      $messageStack->add_session(SUCCESS_NEWSLETTER_UPDATED, 'account', 'success');

      tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }

    $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL'));

    return $this->render('newsletters.tpl',[
      'account_newsletter_action' => tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, 'action=process', 'SSL'),
      'newsletter_general' => $newsletter['customers_newsletter']!=0,
      'link_back_href' => tep_href_link(FILENAME_ACCOUNT, '', 'SSL'),
    ]);
  }

  public function actionEdit()
  {
      $this->accountRedirect('Account edit');
        global $navigation, $breadcrumb;

        \common\helpers\Translation::init('js');

        $this->checkIsGuest();

        $messageStack = \Yii::$container->get('message_stack');

        $customer = Yii::$app->user->getIdentity();

        $editModel = new CustomerRegistration(['scenario' => CustomerRegistration::SCENARIO_EDIT, 'shortName' => CustomerRegistration::SCENARIO_EDIT]);
        $editModel->preloadCustomersData($customer);

        if (\Yii::$app->request->isPost){
            if ($editModel->load(\Yii::$app->request->post()) && $editModel->validate()){

                $editModel->processCustomerAuth($customer);

                $hasErrors = false;
                foreach (\common\helpers\Hooks::getList('frontend/account/edit') as $filename) {
                    include($filename);
                }

                /** @var \common\extensions\CustomersMultiEmails\CustomersMultiEmails $CustomersMultiEmails */
                if ($CustomersMultiEmails = \common\helpers\Acl::checkExtensionAllowed('CustomersMultiEmails', 'allowed')) {
                  $ret = $CustomersMultiEmails::saveCustomer((int) $customer->customers_id, $hasErrors);
                  if (!$ret && !$hasErrors) {
                    $hasErrors = true;
                  }
                }

                if (!$hasErrors && is_object($this->promoActionsObs)) {
                    $this->promoActionsObs->triggerAction('complete_profile');
                }

                if (!$hasErrors) {
                    if (Yii::$app->request->isAjax) {
                        $messageStack->add(SUCCESS_ACCOUNT_UPDATED, 'account_edit', 'success');
                    } else {
                        $messageStack->add_session(SUCCESS_ACCOUNT_UPDATED, 'account_edit', 'success');
                        tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
                    }
                }
            }
        }

        if ($editModel->hasErrors()){
            foreach($editModel->getErrors() as $error){
                $messageStack->add(is_array($error)? implode("<br>", $error): $error, 'account_edit');
            }
        }

        $message = '';
        if (Yii::$app->request->isAjax) {
            return json_encode($messageStack->asArray('account_edit'));
        } else {
            $message = '<div class="main">' . $messageStack->output('account_edit') . '</div>';
        }

        $back_link = tep_href_link(FILENAME_ACCOUNT, '', 'SSL');

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));

        return $this->render('accountedit.tpl', [
                    'editModel' => $editModel,
                    'back_link' => $back_link,
                    'message'   => $message,
        ]);
    }

    public function actionHistoryInfo() {
        $this->accountRedirect('Order History Info');
        global $cart;
        global $languages_id, $navigation, $breadcrumb;

        $this->checkIsGuest();

        $customer_id = Yii::$app->user->getId();

        if (!isset($_GET['order_id']) || (isset($_GET['order_id']) && !is_numeric($_GET['order_id']))) {
            tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
        }
        $historyOrderId = (int)$_GET['order_id'];

        $currencies = Yii::$container->get('currencies');

        $customer_info = Orders::find()
            ->select(['customers_id', 'tracking_number'])
            ->where(['orders_id' => $historyOrderId])
            ->asArray()
            ->one();

        if ($customer_info['customers_id'] != $customer_id) {
            tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
        }

        $trackings = [];
        $trackingsArr = [];
        $customers_id = $customer_info['customers_id'];
        if ($customer_info && tep_not_null($customer_info['tracking_number'])) {
            $trackings = explode(";", $customer_info['tracking_number']);
            foreach ($trackings as $i => $track) {
                $tracking_data = \common\helpers\Order::parse_tracking_number($track);
                $trackingsArr[] = [
                    'url' => $tracking_data['url'],
                    'number' => $tracking_data['number'],
                    'qr_code_url' => Yii::$app->urlManager->createUrl([
                        'account/order-qrcode',
                        'oID' => $historyOrderId,
                        'cID' => $customers_id,
                        'tracking' => '1',
                        'tracking_number' => $track
                    ]),
                ];
            }
        }

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
        $breadcrumb->add(sprintf(NAVBAR_TITLE_3, $_GET['order_id']), tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $historyOrderId, 'SSL'));

        $order = new \common\classes\Order($historyOrderId);
// {{
        $get_trackings = Orders::find()
            ->alias('o')
            ->select(['trn.tracking_numbers_id', 'trn.tracking_carriers_id', 'trn.tracking_number' , 'o.orders_id', 'trn.orders_id'])
            ->joinWith(['trackingNumbers trn'])
            ->where(['o.orders_id' => $historyOrderId])
            ->asArray()
            ->all();

        if (is_array($get_trackings)) {
            $trackingsArr = [];
            foreach ($get_trackings as $tracking) {
                $productsArr = [];
                $tracking_products = TrackingNumbersToOrdersProducts::find()
                    ->select(['orders_products_id', 'products_quantity'])
                    ->where(['tracking_numbers_id' => $tracking['tracking_numbers_id']])
                    ->andWhere(['orders_id' => $historyOrderId])
                    ->asArray()
                    ->all();
                foreach ($tracking_products as $products) {
                    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                        if ($order->products[$i]['orders_products_id'] == $products['orders_products_id']) {
                            $productsArr[] = $order->products[$i];
                            $productsArr[count($productsArr)-1]['qty'] = $products['products_quantity'];
                        }
                    }
                }
                $tracking_data = \common\helpers\Order::parse_tracking_number($tracking['tracking_number']);
                $trackingsArr[] = [
                    'url' => $tracking_data['url'],
                    'number' => $tracking_data['number'],
                    'qr_code_url' => Yii::$app->urlManager->createUrl([
                        'account/order-qrcode',
                        'oID' => $historyOrderId,
                        'cID' => $customers_id,
                        'tracking' => '1',
                        'tracking_number' => $tracking['tracking_number']
                    ]),
                    'products' => $productsArr,
                ];
            }
        }
// }}
        $order_info = array();
        $order_title = $historyOrderId;
        $order_date = DateHelper::date_long($order->info['date_purchased']);
        $order_info_status = $order->info['orders_status_name'];
        $order_info_total = $order->info['total'];
        $order_delivery_address = '';
        $order_shipping_method = '';
        if ($order->delivery != false) {
            $order_delivery_address = \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>');
            if (tep_not_null($order->info['shipping_method'])) {
                $order_shipping_method = $order->info['shipping_method'];
            }
        }
        $order_info['tax_groups'] = '';
        $tax_groups = sizeof($order->info['tax_groups']);
        $order_product = array();
        //echo '<pre>';print_r($order->products);
        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            $order_img = Products::find()
                ->select(['products_image'])
                ->where(['products_id' => $order->products[$i]['id']])
                ->asArray()
                ->limit(1)
                ->one();
            $order_info['products_image'] = Images::getImageUrl($order->products[$i]['id'], 'Small');
            $order_info['order_product_qty'] = $order->products[$i]['qty'];
            $order_info['order_product_name'] = $order->products[$i]['name'];
            $order_info['product_info_link'] = '';
            $order_info['id'] = $order->products[$i]['id'];
            if (\common\helpers\Product::check_product($order->products[$i]['id'])) {
                $order_info['product_info_link'] = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . \common\helpers\Inventory::get_prid($order->products[$i]['id']));
            }
            $order_info_attr = array();
            if ((isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0)) {
                //$order_info_attr['size'] = sizeof($order->products[$i]['attributes']);
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                    $order_info_attr[$j]['order_pr_option'] = str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($order->products[$i]['attributes'][$j]['option']));
                    $order_info_attr[$j]['order_pr_value'] = ($order->products[$i]['attributes'][$j]['value'] ? htmlspecialchars($order->products[$i]['attributes'][$j]['value']) : '');
                }
            }
            $order_info['attr_array'] = $order_info_attr;
            if (sizeof($order->info['tax_groups']) > 1) {
                $order_info['order_products_tax'] = \common\helpers\Tax::display_tax_value($order->products[$i]['tax']) . '%';
            }
            $order_info['final_price'] = $currencies->format($currencies->calculate_price_in_order($order->info, $order->products[$i]['final_price'], (DISPLAY_PRICE_WITH_TAX == 'true' ? $order->products[$i]['tax'] : 0), $order->products[$i]['qty']), true, $order->info['currency'], $order->info['currency_value']);
            $order_product[] = $order_info;
        }
        $order_billing = \common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>');
        $payment_method = $order->info['payment_method'];
        $order_info_array = array();
        $order_info_ar = array();
        $pay_link = false;
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
            $pay_link = $ext::payLink($historyOrderId);
        }
        $reorder_link = tep_href_link('checkout/reorder', 'order_id=' . (int)$historyOrderId, 'SSL');
        if ($pay_link) {
            $reorder_link = false;
        }

        $cancel_and_restart = false;
        //1 is new orders, 5 - cancelled orders - imho:it is not good idea to use ids
        $orderModel = \common\models\Orders::find()->where(['orders_id' => $historyOrderId])->joinWith(['ordersStatusGroup sg'])
                ->where(['in', 'sg.orders_status_groups_id', [1,5]])->andWhere(['orders_id' => $historyOrderId])->one();

        if ($orderModel){
            $cancel_and_restart = tep_href_link('checkout/restart', 'order_id=' . (int)$historyOrderId, 'SSL');
        }

        for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {

            if (file_exists( DIR_WS_MODULES . 'order_total/' . $order->totals[$i]['class'] . '.php')) {
                include_once( DIR_WS_MODULES . 'order_total/' . $order->totals[$i]['class'] . '.php');
            }

            if (class_exists($order->totals[$i]['class'])) {
                $orderClass = $order->totals[$i]['class'];
                $object = new $orderClass;
                if (method_exists($object, 'visibility')) {
                    if (true == $object->visibility(\common\classes\platform::defaultId(), 'TEXT_ACCOUNT')) {
                        if (method_exists($object, 'visibility')) {
                            $order_info_ar[] = $object->displayText(\common\classes\platform::defaultId(), 'TEXT_ACCOUNT', $order->totals[$i]);
                        } else {
                            $order_info_ar[] = 0;
                        }
                    }
                }
            }
        }

        /*
        $statuses = OrdersStatus::find()
            ->alias('os')
            ->select(['os.orders_status_name' , 'osh.date_added', 'osh.comments', 'osh.orders_id', 'os.orders_status_id', 'osh.orders_status_id', 'osh.date_added'])
            ->joinWith(['ordersStatusHistory osh'])
            ->where(['osh.orders_id' => $historyOrderId])
            ->andWhere(['os.language_id' => $languages_id])
            ->orderBy('osh.date_added')
            ->asArray()
            ->all();

        $order_statusses = [];
        foreach ($statuses as $i => $status) {
            $statuses[$i]['date'] = DateHelper::date_short($status['date_added']);
            $statuses[$i]['status_name'] = $status['orders_status_name'];
            $statuses[$i]['comments_new'] = (empty($status['comments']) ? '&nbsp;' : nl2br(\common\helpers\Output::output_string_protected($status['comments'])));
            $order_statusses[] = $statuses;
        }
        */
        $order_statusses = [];
        foreach ($order->getStatusHistory() as $statusHistoryLine) {
            $statusHistoryLine['date'] = DateHelper::date_short($statusHistoryLine['date_added']);
            $statusHistoryLine['status_name'] = $statusHistoryLine['orders_status_name'];
            $statusHistoryLine['comments_new'] = (empty($statusHistoryLine['comments']) ? '&nbsp;' : nl2br(\common\helpers\Output::output_string_protected($statusHistoryLine['comments'])));
            $order_statusses[] = $statusHistoryLine;
        }

        $print_order_link = tep_href_link(FILENAME_ORDERS_PRINTABLE, \common\helpers\Output::get_all_get_params(array('orders_id')) . 'orders_id=' . $historyOrderId);
        $back_link = tep_href_link('account/history', \common\helpers\Output::get_all_get_params(array('order_id')), 'SSL');
        return $this->render('historyinfo.tpl', [
                    'description' => '',
                    'order' => $order,
                    'order_delivery_address' => $order_delivery_address,
                    'order_shipping_method' => $order_shipping_method,
                    'tax_groups' => $tax_groups,
                    'order_product' => $order_product,
                    'order_info_ar' => $order_info_ar,
                    'order_statusses' => $order_statusses,
                    'print_order_link' => $print_order_link,
                    'back_link' => $back_link,
                    'order_title' => $order_title,
                    'order_date' => $order_date,
                    'order_info_total' => $order_info_total,
                    'order_info_status' => $order_info_status,
                    'order_billing' => $order_billing,
                    'payment_method' => $payment_method,
                    'reorder_link' => $reorder_link,
                    'reorder_confirm' => ($cart->count_contents() > 0 ? REORDER_CART_MERGE_WARN : ''),
                    'pay_link' => $pay_link,
                    'cancel_and_restart' => $cancel_and_restart,
                    'downloads' => \frontend\design\boxes\success\Download::widget(['params' =>['orders_id' => $historyOrderId]]),
            'trackings' => $trackingsArr,
            'order_id'  => $historyOrderId,
            'customers_id' => $customers_id,
        ]);
    }

    public function actionHistory() {
        $this->accountRedirect('Order History');
        global $cart, $languages_id, $language, $navigation, $breadcrumb;

        $this->checkIsGuest();

        $customer_id = Yii::$app->user->getId();
        $orders_total = \common\helpers\Customer::count_customer_orders();
        $history_query_raw = "select o.orders_id, o.date_purchased, o.delivery_name, o.billing_name, ot.text as order_total, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int) $customer_id . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int) $languages_id . "' order by orders_id DESC";
        $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_ORDER_HISTORY);
        $history_query = tep_db_query($history_split->sql_query);
        $history_links = $history_split->display_links(MAX_DISPLAY_PAGE_LINKS, \common\helpers\Output::get_all_get_params(array('page', 'info', 'x', 'y')), 'account/history');
        $history_array = array();
        while ($history = tep_db_fetch_array($history_query)) {
            $products = OrdersProducts::find()
                ->select(['COUNT(*) AS count'])
                ->where(['orders_id' => (int) $history['orders_id']])
                ->asArray()
                ->limit(1)
                ->one();
            if (tep_not_null($history['delivery_name'])) {
                $history['type'] = TEXT_ORDER_SHIPPED_TO;
                $history['name'] = $history['delivery_name'];
            } else {
                $history['type'] = TEXT_ORDER_BILLED_TO;
                $history['name'] = $history['billing_name'];
            }
            $history['count'] = $products['count'];
            $history['date'] = DateHelper::date_long($history['date_purchased']);
            $history['link'] = tep_href_link('account/history-info', (isset($_GET['page']) ? 'page=' . (int) $_GET['page'] . '&' : '') . 'order_id=' . $history['orders_id'], 'SSL');
            $history['reorder_link'] = tep_href_link('checkout/reorder', 'order_id=' . (int) $history['orders_id'], 'SSL');
            $history['reorder_confirm'] = ($cart->count_contents() > 0 ? REORDER_CART_MERGE_WARN : '');

            $pay_link = false;
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
                $pay_link = $ext::payLink($history['orders_id']);
            }
            $history['pay_link'] = $pay_link;

            $history_array[] = $history;
        }

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));

        return $this->render('history.tpl', ['description' => '', 'orders_total' => $orders_total, 'history_array' => $history_array, 'number_of_rows' => $history_split->number_of_rows, 'links' => $history_links, 'history_count' => $history_split->display_count(LISTING_PAGINATION), 'account_back' => '<a class="btn" href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . IMAGE_BUTTON_BACK . '</a>']);
    }

    public function actionAddressBook() {
        $this->accountRedirect('Address Book');
        global $languages_id, $language, $navigation, $breadcrumb;

        $this->checkIsGuest();

        $messageStack = \Yii::$container->get('message_stack');
        if ($messageStack->size('addressbook') > 0) {
            $message = $messageStack->output('addressbook');
        }
        $customer = Yii::$app->user->getIdentity();

        $address_array = array();
        $aBooks = $customer->getAddressBooks(true);
        $aBooks = \common\helpers\Address::skipEntryKey($aBooks);
        foreach($aBooks as $addresses){
            $format_id = \common\helpers\Address::get_address_format_id($addresses['country_id']);
            $addresses['text'] = $addresses['city'] . ' ' . $addresses['postcode'] . ' ' . \common\helpers\Country::get_country_name($addresses['country_id']);
            $addresses['format'] = \common\helpers\Address::address_format($format_id, $addresses, true, '', ' ');
            $addresses['link_edit'] = tep_href_link('account/address-book-process', 'edit=' . $addresses['address_book_id'], 'SSL');
            $addresses['link_delete'] = tep_href_link('account/address-book-process', 'delete=' . $addresses['address_book_id'], 'SSL');
            $addresses['default_address'] = $customer->customers_default_address_id;
            $addresses['customers'] = \common\helpers\Output::output_string_protected($addresses['firstname'] . ' ' . $addresses['lastname']);
            $address_array[] = $addresses;
        }
        if (count($aBooks) < MAX_ADDRESS_BOOK_ENTRIES) {
            $addr_process = tep_href_link('account/address-book-process', '', 'SSL');
        }
        $link_back = tep_href_link('account', '', 'SSL');
        $max_val = sprintf(TEXT_MAXIMUM_ENTRIES, MAX_ADDRESS_BOOK_ENTRIES);

        $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        $breadcrumb->add(TEXT_ADDRESS_BOOK, tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL'));

        return $this->render('address_book.tpl', ['message' => $message, 'address_array' => $address_array, 'addr_process' => $addr_process, 'link_back' => $link_back, 'max_val' => $max_val, 'customer_id' => $customer->getId()]);
    }

    public function actionAddressList()
    {
        $box_id = Yii::$app->request->get('box_id');
        return \frontend\design\boxes\account\AddressesList::widget(['id' => $box_id]);
    }

    public function actionAddressBookEdit()
    {
        return \frontend\design\boxes\account\EditAddress::widget([
            'id' => rand()
        ]);
    }

    public function actionAddressBookProcess(){
        $this->accountRedirect('Address Book Process');
        global $languages_id, $language, $breadcrumb, $navigation;

        $this->checkIsGuest();
        /** @var Customer $customer */
        $customer = Yii::$app->user->getIdentity();
        $action = Yii::$app->request->post('action', '');
        $delete = (int)Yii::$app->request->get('delete', 0);

        $messageStack = \Yii::$container->get('message_stack');
        if ($action == 'deleteconfirm' && $delete > 0 ) {
            if ($aBook = $customer->getAddressBook($delete)) {
                if ($aBook->address_book_id == $customer->customers_default_address_id) {
                    if (Yii::$app->request->isAjax) {
                        $messageStack->add(WARNING_PRIMARY_ADDRESS_DELETION, 'addressbook', 'warning');
                        return json_encode($messageStack->asArray('addressbook'));
                    } else {
                        $messageStack->add_session(WARNING_PRIMARY_ADDRESS_DELETION, 'addressbook', 'warning');
                        tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
                    }
                } else {
                    $aBook->delete();
                }
            }

            if (Yii::$app->request->isAjax) {
                $messageStack->add(SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'addressbook', 'success');
                return json_encode($messageStack->asArray('addressbook'));
            } else {
                $messageStack->add_session(SUCCESS_ADDRESS_BOOK_ENTRY_DELETED, 'addressbook', 'success');
                tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
            }
        }

        $message = '';

        $type = Yii::$app->request->post('type', '');
        switch ($type) {
            case 'billing':
                $scenario = AddressForm::BILLING_ADDRESS;
                break;
            case 'shipping':
                $scenario = AddressForm::SHIPPING_ADDRESS;
                break;
            default:
                $scenario = AddressForm::CUSTOM_ADDRESS;
                $type = '';
                break;
        }

        $bookModel = new AddressForm(['scenario' => $scenario]);

        if (isset($_POST['action']) && (($_POST['action'] == 'process') || ($_POST['action'] == 'update'))) {

            if (!isset($_GET['delete']) && !isset($_GET['edit'])) {
                if (\common\helpers\Customer::count_customer_address_book_entries() >= MAX_ADDRESS_BOOK_ENTRIES) {
                    if (Yii::$app->request->isAjax) {
                        $messageStack->add(ERROR_ADDRESS_BOOK_FULL, 'addressbook');
                        return json_encode($messageStack->asArray('addressbook'));
                    } else {
                        $messageStack->add_session(ERROR_ADDRESS_BOOK_FULL, 'addressbook');
                        tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
                    }
                }
            }

            if ($bookModel->load(\Yii::$app->request->post()) && $bookModel->validate() ){
                $book = $customer->getAddressFromModel($bookModel);
                if ($bookModel->address_book_id){
                    $dbBook = $customer->updateAddress($bookModel->address_book_id, $book);
                } else {
                    $dbBook = $customer->addAddress($book);
                }

                if ($bookModel->as_preferred && $dbBook){
                    if ($type == 'shipping') {
                        if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
                            $customer->customers_shipping_address_id = $dbBook->address_book_id;
                            $customer->save(false);
                        }
                    } else {
                        //update customer if set new default address
                        $customer->customers_firstname = $bookModel->firstname;
                        $customer->customers_lastname = $bookModel->lastname;
                        $customer->customers_default_address_id = $dbBook->address_book_id;
                        if ($bookModel->gender) {
                            $customer->customers_gender = $bookModel->gender;
                        }
                        $customer->save(false);
                    }
                }
                if ($dbBook && $customer->customers_default_address_id == $dbBook->address_book_id){

                  //update entity if default address was edited
                    $customer->set('customer_first_name', $bookModel->firstname, true);
                    $customer->set('customer_country_id', $bookModel->country, true);
                    $customer->set('customer_zone_id', ($bookModel->zone_id > 0 ? (int) $bookModel->zone_id : 0), true);
                    $customer->set('customer_default_address_id', $dbBook->address_book_id, true);
                }

                if (Yii::$app->request->isAjax) {
                    $messageStack->add(SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'addressbook', 'success');
                    return json_encode($messageStack->asArray('addressbook'));
                } else {
                    $messageStack->add_session(SUCCESS_ADDRESS_BOOK_ENTRY_UPDATED, 'addressbook', 'success');
                    tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
                }
            }

            if ($bookModel->hasErrors()){
                foreach($bookModel->getErrors() as $error){
                    $messageStack->add( (is_array($error)? implode("<br>", $error):$error), 'addressbook' );
                }
            }


            if ($messageStack->size('addressbook') > 0) {
                $message = $messageStack->output('addressbook');
            }
            if (Yii::$app->request->isAjax) {
                return json_encode($messageStack->asArray('addressbook'));
            }
        }

        return $this->render('address_book_process.tpl', ['message' => $message]);
    }

    public function actionInvoice() {

        global $languages_id, $navigation;

        $this->checkIsGuest();

        $currencies = Yii::$container->get('currencies');

        $orders_id = (int)Yii::$app->request->get('orders_id');

        $manager = \common\services\OrderManager::loadManager();
        $splitter = $manager->getOrderSplitter();
        $order = $manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);

        if ($order->customer['customer_id'] != Yii::$app->user->getId()) {
            tep_redirect(tep_href_link('account', '', 'SSL'));
        }

        $invoices = $splitter->getInstancesFromSplinters($orders_id, $splitter::STATUS_PAYED);
        if ($invoices){
            $pages = [];
            foreach($invoices as $invoice){
                $pages[] = ['name' => 'invoice',
                            'params' => [
                                'orders_id' => $orders_id,
                                'platform_id' => $invoice->info['platform_id'] ? $invoice->info['platform_id'] : 1,
                                'language_id' => $languages_id,
                                'order' => $invoice,
                                'currencies' => $currencies,
                                'oID' => $orders_id
                            ]
                        ];
            }
        } else {
            $pages = [
                ['name' => 'invoice', 'params' => [
                'orders_id' => $orders_id,
                'platform_id' => $order->info['platform_id'],
                'language_id' => $languages_id,
                'order' => $order,
                'currencies' => $currencies,
                'oID' => $orders_id
            ]]];
        }

        \backend\design\PDFBlock::widget([
            'pages' => $pages,
            'params' => [
                'theme_name' => THEME_NAME,
                'document_name' => str_replace(' ', '_', TEXT_INVOICE) . $order->getOrderId() . '.pdf',
                'title' => TEXT_INVOICE . ' ' . $order->getOrderId(),
                'subject' => TEXT_INVOICE . ' ' . $order->getOrderId(),
            ]
        ]);
        die;
    }

    public function actionGv_send(){
      //TODO
      /*/ {{
      $email_params = array();
      $email_params['STORE_NAME'] = STORE_NAME;
      $email_params['MESSAGE_TEXT'] = tep_db_prepare_input($_POST['message']);
      $email_params['GV_AMOUNT'] = $currencies->format($_POST['amount']);
      $email_params['CUSTOMERS_NAME'] = tep_db_prepare_input($_POST['send_name']);
      $email_params['FRIEND_NAME'] = tep_db_prepare_input($_POST['to_name']);
      $email_params['GV_CODE'] = $id1;
      $email_params['GV_REDEEM_URL'] = tep_href_link('gv_redeem', 'gv_no=' . $id1, 'NONSSL', false);

      list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('GV Send to Friend', $email_params);
      // }} */
    }

    public function actionOrderBarcode()
    {
        $oID = intval(Yii::$app->request->get('oID'));
        $cID = intval(Yii::$app->request->get('cID', Yii::$app->user->getId()));
        $type = Yii::$app->request->get('type', 'Orders');
        if (in_array($type, ['QuoteOrders' , 'SampleOrders'])) {
            \Yii::warning('Quotations and Samples are migrating to AppStore. Not ready yet');
        }
        if (in_array($type, ['Orders'/*, 'QuoteOrders' , 'SampleOrders'*/]) && class_exists('\\common\\models\\' . $type)) {
          $type = '\\common\\models\\' . $type;
          $check = $type::find()
              ->select('customers_id')
              ->where(['orders_id' => (int)$oID])
              ->limit(1)
              ->asArray()
              ->one();
          if ($check['customers_id'] == (int)$cID) {
// walk in orders could don't have customer_id - can't print invoice //$cID > 0 &&
              tep_draw_barcode('', str_pad($oID, 8, '0', STR_PAD_LEFT));
          }
        }
    }

    public function actionOrderQrcode()
    {

        $oID = intval(Yii::$app->request->get('oID'));
        $cID = intval(Yii::$app->request->get('cID', (int)Yii::$app->user->getId()));
        $cID = (int)$cID;
        $check = Orders::find()
            ->select('customers_id')
            ->where(['orders_id' => (int)$oID])
            ->limit(1)
            ->asArray()
            ->one();
        if ($cID > 0 && $check['customers_id'] == $cID) {
            $order = new \common\classes\Order($oID);
            if (Yii::$app->request->get('tracking')) {
                if (isset($_GET['tracking_number'])) {
                    $tracking_data = \common\helpers\Order::parse_tracking_number($_GET['tracking_number']);
                    \common\classes\qrcode\QRcode::png($tracking_data['url']);
                } elseif (is_array($order->info['tracking_number']) && count($order->info['tracking_number'])) {
                    $tracking_data = \common\helpers\Order::parse_tracking_number($order->info['tracking_number'][0]);
                    \common\classes\qrcode\QRcode::png($tracking_data['url']);
                }
            } else {
                $address = \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 0, '', "\n");
                !empty($address) && \common\classes\qrcode\QRcode::png($address);
            }
        }
    }

    public function actionQuotationQrcode()
    {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Quotations')) {
            return $ext::actionQuotationQrcode();
        }
    }

    public function actionSwitchPrimary()
    {
        global $navigation;
        $this->checkIsGuest();

        $id = intval(Yii::$app->request->post('is_default'));

        /*if (!$id) {
            $id = intval(Yii::$app->request->get('is_default'));
        }*/
        $customer = Yii::$app->user->getIdentity();

        $aBook = $customer->getAddressBook((int)$id);

        $updateCustomer = true;
        if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
            if ($aBook->entry_type == AddressForm::SHIPPING_ADDRESS) {
                $customer->customers_shipping_address_id = $aBook->address_book_id;
                $customer->save(false);
                $updateCustomer = false;
            }
        }

        if ( $updateCustomer && $aBook ) {
            $customer->set('customer_default_address_id', (int)$id, true);
            $customer->set('customer_country_id', (int)$aBook->entry_country_id, true);
            $customer->set('customer_zone_id', (int)$aBook->entry_zone_id, true);
            $customer->customers_default_address_id = (int)$id;
            $customer->save();
        }

        if ( Yii::$app->request->isAjax ) {
            $this->layout = false;
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'status' => 'ok',
                'default_address_id' => (int)$customer->customers_default_address_id,
            ];
            return;
        }

        tep_redirect(tep_href_link('account/address-book', '', 'SSL'));
        //return $this->render('address_book.tpl', ['message' => $message]);
    }

    public function actionSwitchNewsletter()
    {
      global $navigation;
      $this->checkIsGuest();

      $customer_id = Yii::$app->user->getId();
      $customer = RegularOffers::findOne($customer_id);
      if ($customer) {
          $customer->delete();
      }
      $newsletter_general = tep_db_prepare_input(Yii::$app->request->post('newsletter_general'));
      $regular_offers = (int) Yii::$app->request->post('regular_offers');
      if ($newsletter_general == 'true' && $regular_offers > 0) {
        $sql_data_array = array(
            'customers_id' => $customer_id,
            'period' => $regular_offers,
            'date_end' => date('Y-m-d', strtotime('+'.$regular_offers.' months')),
        );
        tep_db_perform('regular_offers', $sql_data_array);
      }
      //$id = Yii::$app->request->post('id');
      $customersFind = Customers::findOne((int)$customer_id);
      if ($newsletter_general == 'true') {
          $customersFind->editCustomersNewsletter(1);
      } else {
          $customersFind->editCustomersNewsletter(0);
      }
      tep_redirect(tep_href_link('account', '', 'SSL'));
      //return $this->render('index.tpl', ['message' => $message]);
    }

    public function actionProductsReviews()
    {
        $this->accountRedirect('Review');
      global $language, $breadcrumb, $navigation;

      $this->checkIsGuest();

      $customer_id = Yii::$app->user->getId();
      $historyReviews = Reviews::find()
          ->alias('r')
          ->innerJoinWith('product p')
          ->where(['r.customers_id' => (int)$customer_id])
          ->orderBy(['r.reviews_id' => SORT_DESC])
          ->asArray()
          ->all();
      $history_query_raw =
        "select r.* ".
        "from " . TABLE_REVIEWS . " r " .
        " inner join ".TABLE_PRODUCTS." p on p.products_id=r.products_id ".
        "where r.customers_id = '" . (int)$customer_id . "' ".
        "order by r.reviews_id DESC";
      $history_split = new splitPageResults($history_query_raw, MAX_DISPLAY_NEW_REVIEWS);
      $customer_reviews = [];
      foreach ($historyReviews as $customer_review) {
          $customer_review['products_link'] = '';
          if ( \common\helpers\Product::check_product($customer_review['products_id']) ) {
              $customer_review['products_link'] = tep_href_link(FILENAME_PRODUCT_INFO,'products_id='.$customer_review['products_id'],'');
          }
          $customer_review['products_name'] = \common\helpers\Product::get_products_name($customer_review['products_id']);
          $customer_review['reviews_rating'];
          $customer_review['date_added_str'] = DateHelper::date_short($customer_review['date_added']);
          if ($customer_review['status']){
              $customer_review['status_name'] = TEXT_REVIEW_STATUS_APPROVED;
          }else{
              $customer_review['status_name'] = TEXT_REVIEW_STATUS_NOT_APPROVED;
          }
          $customer_review['view'] = tep_href_link('reviews/info','reviews_id='.$customer_review['reviews_id'].'&back=account-products-reviews'.(isset($_GET['page']) && (int)$_GET['page']>1?'-'.(int)$_GET['page']:''));
          //$back = array('account/products-reviews', isset($_GET['page'])?'page='.$_GET['page']:'','SSL');
          $customer_reviews[] = $customer_review;
      }

      $breadcrumb->add(TEXT_MY_ACCOUNT, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
      $breadcrumb->add(NAVBAR_TITLE, tep_href_link('account/products-reviews', '', 'SSL'));

      $params = array(
        'listing_split' => $history_split,
        'this_filename' => 'account/products-reviews',
        'listing_display_count_format' => TEXT_DISPLAY_NUMBER_OF_REVIEWS,
      );

      return $this->render('products-reviews.tpl', ['reviews'=> $customer_reviews, 'params' => ['params'=>$params],'account_back_link'=>tep_href_link(FILENAME_ACCOUNT,'','SSL')]);
		}

    public function actionCreditAmount()
    {
        global $navigation;
        $this->checkIsGuest();

        $customer_id = Yii::$app->user->getId();

        $this->layout = false;
        \common\helpers\Translation::init('account/history');

        $currencies = Yii::$container->get('currencies');
        $type = (Yii::$app->request->get('type', 'credit') == 'credit' ? 0 : 1 );

        $history = [];
        $customer_history_queryActive = CustomersCreditHistory::find()
            ->where(['customers_id' => (int)$customer_id])
            ->andWhere(['credit_type' => $type])
            ->orderBy('customers_credit_history_id')
            ->asArray()
            ->all();
        foreach ($customer_history_queryActive as $customer_history) {
            $admin = '';
            if ($customer_history['admin_id'] > 0) {
                $check_admin = Admin::find()
                    ->where(['admin_id' => (int)$customer_history['admin_id']])
                    ->asArray()
                    ->one();
                if (is_array($check_admin)) {
                    $admin =  $check_admin['admin_firstname'] . ' ' . $check_admin['admin_lastname'];
                }
            }
            $history[] = [
                'date' => ($type?DateHelper::datepicker_date($customer_history['date_added']):DateHelper::datetime_short($customer_history['date_added'])),
                'credit' => $customer_history['credit_prefix'] . ($customer_history['credit_type'] ? $customer_history['credit_amount'] : $currencies->format($customer_history['credit_amount'], true, $customer_history['currency'], $customer_history['currency_value'])),
                'notified' => $customer_history['customer_notified'],
                'comments' => $customer_history['comments'],
                'admin' => $admin,
            ];
        }

        if ($type){
            if(\common\helpers\Acl::checkExtensionAllowed('BonusActions')){
                $_history = promotions\PromotionsBonusHistory::find()->where('customer_id = :id', [':id' => (int)$customer_id])->asArray()->all();
                if ($_history){
                    $titles = [];
                    foreach($_history as $h){
                        if (!isset($titles[$h['bonus_points_id']])){
                            $titles[$h['bonus_points_id']] = \common\extensions\BonusActions\models\PromotionsBonusPoints::find()->where('bonus_points_id = ' . (int)$h['bonus_points_id'])->with('description')->one();
                        }
                        $history[] = [
                            'date' => DateHelper::datepicker_date($h['action_date']),
                            'credit' => '+' . $h['bonus_points_award'],
                            'notified' => 1,
                            'comments' => $titles[$h['bonus_points_id']]->description->points_title,
                            'admin' => '',
                        ];
                    }
                    \yii\helpers\ArrayHelper::multisort($history, 'date');
                }
            }
        }
        return $this->render('credit-history.tpl', ['history' => $history, 'type' => $type]);
    }

    public function actionAddressState() {
      $term = tep_db_prepare_input(Yii::$app->request->get('term'));
      $country = tep_db_prepare_input(Yii::$app->request->get('country'));

      $zones = [];
      $zones_queryActive = Zones::find()
          ->where(['zone_country_id' => $country])
          ->andfilterWhere(['like', 'zone_name', $term])
          ->orderBy('zone_name')
          ->asArray()
          ->all();

      foreach ($zones_queryActive as $response) {
          $zones[] = $response['zone_name'];
      }
      echo json_encode($zones);
    }

    public function actionAddressCity() {
      $term = tep_db_prepare_input(Yii::$app->request->get('term'));
      $state = tep_db_prepare_input(Yii::$app->request->get('state',''));
      $country = tep_db_prepare_input(Yii::$app->request->get('country'));

      $cities = [];
      $cities_queryActive = Cities::find()
          ->alias('c')
          ->where(['c.city_country_id' => $country])
          ->join('left join', \common\models\Zones::tableName().' z', 'z.zone_id=c.city_zone_id')
          ->andFilterWhere(['like', 'c.city_name', $term])
          ->orderBy('c.city_name')
          ->select(['c.city_name','z.zone_name']);
      if ( $state ){
          $zones_queryActive = clone $cities_queryActive;
          $zones_queryActive->andFilterWhere(['z.zone_name'=>$state]);
          if ( $zones_queryActive->count()>0 ){
              $cities_queryActive = $zones_queryActive;
          }
      }

      foreach ($cities_queryActive->asArray()->all() as $response) {
          $cities[] = [
              'id' => $response['city_name'],
              'value' => $response['city_name'],
              'state' => (string)$response['zone_name'],
          ];
      }
      echo json_encode($cities);
    }

    public function actionAddressPostcode() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $country = tep_db_prepare_input(Yii::$app->request->get('country'));

        $addresses = [];

        $searchAddress = \common\models\PostalCodes::find()
            ->alias('p')
            ->where(['like', 'postcode', $term.'%', false])
            ->andFilterWhere(['country_id' => $country])
            ->join('left join', \common\models\Cities::tableName().' c', 'c.city_id=p.city_id')
            ->join('left join', \common\models\Zones::tableName().' z', 'z.zone_id=p.zone_id')
            ->orderBy(['p.postcode'=>SORT_ASC])
            ->select(['p.postcode', 'p.suburb', 'c.city_name', 'z.zone_name']);

        foreach ($searchAddress->asArray()->all() as $addr){
            $addresses[] = [
                'id' => $addr['postcode'],
                'value' => $addr['postcode'],
                'suburb' => (string)$addr['suburb'],
                'city' => (string)$addr['city_name'],
                'state' => (string)$addr['zone_name'],
            ];
        }

        echo json_encode($addresses);
    }

    public function actionDownloadCustomerFile()
    {
        $customerId = 0;
        if (!Yii::$app->user->isGuest) {
            $customerId = Yii::$app->user->getId();
        }

        if ($customerId <= 0 && !Yii::$app->request->get('document')) {
            die;
        }
        $file = Yii::$app->request->get('file');

        if (Yii::$app->request->get('document')) {
            $path = DIR_FS_DOWNLOAD . 'documents' . DIRECTORY_SEPARATOR;
        } else {
            $path = DIR_FS_DOWNLOAD . 'customers' . DIRECTORY_SEPARATOR . $customerId . DIRECTORY_SEPARATOR;
        }

        if ((!$customerId && !Yii::$app->request->get('document')) || !$file) {
            die;
        }

        $messageStack = \Yii::$container->get('message_stack');

        // Die if file is not there
        if (!file_exists($path . $file)) {
            $messageStack->add_session('TEXT_DOWNLOAD_FILE_NOT_FOUND', 'download');
            tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

        header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        $mimeType = mime_content_type($path . $file);
        if (in_array($mimeType, ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/tiff', 'image/webp', 'application/pdf'])) {
            header("Content-Type: " . $mimeType);
            header("Content-disposition: inline; filename=" . $file);
        } else {
            header("Content-Type: Application/octet-stream");
            header("Content-disposition: attachment; filename=" . $file);
        }

        if (DOWNLOAD_BY_REDIRECT == 'true') {
            \common\helpers\Download::unlink_temp_dir(DIR_FS_DOWNLOAD_PUBLIC);
            $tempdir = \common\helpers\Download::random_name();
            umask(0000);
            mkdir(DIR_FS_DOWNLOAD_PUBLIC . $tempdir, 0777);
            symlink($path . $file, DIR_FS_DOWNLOAD_PUBLIC . $tempdir . '/' . $file);
            tep_redirect(DIR_WS_DOWNLOAD_PUBLIC . $tempdir . '/' . $file);
        } else {
            readfile($path . $file);
        }
    }

    public function actionQuotationHistory() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Quotations', 'allowed')) {
            return $ext::actionQuotationHistory();
        }
    }

    public function actionQuotationHistoryInfo() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Quotations', 'allowed')) {
            return $ext::actionQuotationHistoryInfo();
        }
    }

    public function actionQuotationCancel($quotation_id) {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Quotations', 'allowed')) {
            return $ext::actionQuotationCancel($quotation_id);
        }
    }

    public function actionQuotationConfirm($quotation_id) {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Quotations', 'allowed')) {
            return $ext::actionQuotationConfirm($quotation_id);
        }
    }

    public function actionSamplesHistory() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Samples', 'allowed')) {
            return $ext::actionSamplesHistory();
        }
    }

    public function actionSamplesHistoryInfo() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Samples', 'allowed')) {
            return $ext::actionSamplesHistoryInfo();
        }
    }

    public function actionSubscriptionHistory() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Subscriptions', 'allowed')) {
            return $ext::actionSubscriptionHistory();
        }
    }

    public function actionSubscriptionHistoryInfo() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Subscriptions', 'allowed')) {
            return $ext::actionSubscriptionHistoryInfo();
        }
    }

    public function actionSubscriptionInvoice($subscription_id) {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Subscriptions', 'allowed')) {
            return $ext::actionSubscriptionInvoice($subscription_id);
        }
    }

    public function actionSubscriptionCancel($subscription_id) {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Subscriptions', 'allowed')) {
            return $ext::actionSubscriptionCancel($subscription_id);
        }
    }

    public function actions()
    {
        $actions = parent::actions();
        if ( !is_array($actions) ) $actions = [];
        $actions['auth'] = [
            'class' => 'yii\authclient\AuthAction',
            'successCallback' => [$this, 'onAuthSuccess'],
        ];
        return $actions;
    }

    public function actionAuth(){

    }

    public function onAuthSuccess($client)
    {
        \common\helpers\Translation::init('account/login');
        \common\helpers\Translation::init('checkout/login');
        (new Socials($client))->handle();
    }

    public function actionDownload() {

        if (Yii::$app->user->isGuest) {
            die;
        }
        $customer_id = Yii::$app->user->getId();

        $orders_id = (int) Yii::$app->request->get('order');
        $download_id = (int) Yii::$app->request->get('id');

        if ($orders_id <= 0 || $download_id <= 0) {
            die;
        }
        $messageStack = \Yii::$container->get('message_stack');
        $downloads_query = tep_db_query("select date_format(IFNULL(o.last_modified,o.date_purchased), '%Y-%m-%d') as date_purchased_day, opd.download_maxdays, opd.download_count, opd.download_count_1, opd.download_maxdays, opd.orders_products_filename from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " opd where o.customers_id = '" . $customer_id . "' and o.orders_id = '" . $orders_id . "' and o.orders_id = op.orders_id and op.orders_products_id = opd.orders_products_id and opd.orders_products_download_id = '" . $download_id . "' and opd.orders_products_filename != ''");
        if (!tep_db_num_rows($downloads_query)) {
            $messageStack->add_session(TEXT_DOWNLOAD_PRODUCT_NOT_FOUND, 'download');
            tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

        $downloads = tep_db_fetch_array($downloads_query);

        // MySQL INTERVAL
        list($dt_year, $dt_month, $dt_day) = explode('-', $downloads['date_purchased_day']);
        $download_timestamp = mktime(23, 59, 59, $dt_month, $dt_day + $downloads['download_maxdays'], $dt_year);

        // Die if time expired (maxdays = 0 means no time limit)
        if (($downloads['download_maxdays'] != 0) && ($download_timestamp <= time())) {
            $messageStack->add_session(sprintf(TEXT_DOWNLOAD_PRODUCT_EXPIRED, \common\helpers\Date::datetime_short($download_timestamp)), 'download');
            tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders_id, 'SSL'));
        }
        // Die if remaining count is <=0
        if ($downloads['download_count'] <= 0) {
            $messageStack->add_session(sprintf(TEXT_DOWNLOAD_PRODUCT_EXPIRED_DOWNLOADS, $downloads['download_count_1']), 'download');
            tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders_id, 'SSL'));
        }

        // Die if file is not there
        if (!file_exists(DIR_FS_DOWNLOAD . $downloads['orders_products_filename'])) {
            $messageStack->add_session(TEXT_DOWNLOAD_FILE_NOT_FOUND, 'download');
            tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

        // Now decrement counter
        tep_db_query("update " . TABLE_ORDERS_PRODUCTS_DOWNLOAD . " set download_count = download_count-1, download_count_1 = download_count_1+1 where orders_products_download_id = '" . $download_id . "'");

        // Now send the file with header() magic
        header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-Type: Application/octet-stream");
        header("Content-disposition: attachment; filename=" . $downloads['orders_products_filename']);

        if (DOWNLOAD_BY_REDIRECT == 'true') {
            // This will work only on Unix/Linux hosts
            \common\helpers\Download::unlink_temp_dir(DIR_FS_DOWNLOAD_PUBLIC);
            $tempdir = \common\helpers\Download::random_name();
            umask(0000);
            mkdir(DIR_FS_DOWNLOAD_PUBLIC . $tempdir, 0777);
            symlink(DIR_FS_DOWNLOAD . $downloads['orders_products_filename'], DIR_FS_DOWNLOAD_PUBLIC . $tempdir . '/' . $downloads['orders_products_filename']);
            tep_redirect(DIR_WS_DOWNLOAD_PUBLIC . $tempdir . '/' . $downloads['orders_products_filename']);
        } else {
            // This will work on all systems, but will need considerable resources
            // We could also loop with fread($fp, 4096) to save memory
            readfile(DIR_FS_DOWNLOAD . $downloads['orders_products_filename']);
        }
    }

    public function actionApplyCertificate(){

        $form = new \frontend\forms\account\ApplyCerificate;

        if (Yii::$app->request->isPost){
            \common\helpers\Translation::init('ordertotal');

            $result = array('error' => true, 'message' => ERROR_NO_INVALID_REDEEM_GV);

            if ($form->load(Yii::$app->request->post()) && $form->validate()){
                $result = $form->checkGvCertificate();
            }

            echo json_encode($result);
            exit();
        }

        return $this->renderAjax('apply-certificate', [
            'model' => $form,
        ]);
    }

    public function actionDelete() {
        $this->checkIsGuest();
        \common\helpers\Customer::deleteCustomer(Yii::$app->user->getId());
        $this->forever = true;
        return $this->actionLogoff();
    }

    public function actionDownloadMyOrders() {

        $this->checkIsGuest();
        global $languages_id;

        $currencies = Yii::$container->get('currencies');

        $orders_queryActive = Orders::find()
            ->select(['orders_id', 'platform_id', 'orders_status', 'language_id'])
            ->where(['customers_id' => (int) Yii::$app->user->getId()])
            ->orderBy(['orders_id' => SORT_DESC])
            ->asArray()
            ->all();

        if ($orders_queryActive){
            $pages = [];
            foreach ($orders_queryActive as $orders) {
                //$order = new \common\classes\Order($orders['orders_id']);
                $manager = \common\services\OrderManager::loadManager();
                $order = $manager->getOrderInstanceWithId('\common\classes\Order', $orders['orders_id']);
                $pages[] = ['name' => 'invoice',
                    'params' => [
                        'orders_id' => $orders['orders_id'],
                        'platform_id' => $orders['platform_id'] ? $orders['platform_id'] : 1,
                        'language_id' => $languages_id,
                        'order' => $order,
                        'currencies' => $currencies,
                        'oID' => $orders['orders_id']
                    ]
                ];
            }
            \backend\design\PDFBlock::widget([
                'pages' => $pages,
                'params' => [
                    'theme_name' => THEME_NAME,
                    'document_name' => str_replace(' ', '_', TEXT_INVOICE) . '.pdf',
                ]
            ]);
            die;
        }
        tep_redirect(tep_href_link('account', '', 'SSL'));
    }

    public function actionUpdate() {
        \common\helpers\Translation::init('js');
        $token = Yii::$app->request->get('token');
        $customers_dob = '';
        $messages = '';
        $error = false;

        $gdpr = new \common\components\Gdpr();

        if ($gdpr->validToken($token)){
            if ($gdpr->isBanned()){
                $messages = ENTRY_DATE_OF_BIRTH_RESTRICTION;
                $error = true;
            } else {
                if ( Yii::$app->request->isPost) {
                    $dob = tep_db_prepare_input(Yii::$app->request->post('dob'));
                    $dob = DateHelper::date_raw($dob);

                    if ($gdpr->setDobDate($dob)){
                        $gdpr->validateGdpr();
                    }

                    $messages = $gdpr->getMessage();
                    if (($error = $gdpr->getError() ) === false && !$gdpr->hasMistake()) {
                        $gModel = $gdpr->getTokenEntity();
                        tep_db_query("DELETE FROM regular_offers WHERE customers_id=" . (int)$gModel->customers_id);
                        $newsletter = (int)Yii::$app->request->post('newsletter');
                        if ($newsletter > 0) {
                            $regular_offers = (int) Yii::$app->request->post('regular_offers');
                            if ($regular_offers > 0) {
                                $sql_data_array = array(
                                    'customers_id' => $gModel->customers_id,
                                    'period' => $regular_offers,
                                    'date_end' => date('Y-m-d', strtotime('+'.$regular_offers.' months')),
                                );
                                tep_db_perform('regular_offers', $sql_data_array);
                            }
                        }

                        $messages = TEXT_RDPR_CONFIRM_SUCC;
                        $customer = new Customer(Customer::LOGIN_WITHOUT_CHECK);
                        if ($customer->loginCustomer($gModel->email, $gModel->customers_id)) {
                            $customer = Yii::$app->user->getIdentity();
                            $customer->customers_dob = DateHelper::date_raw($dob);
                            $customer->customers_newsletter = (int)$newsletter;
                            $customer->update(false);
                            tep_redirect(tep_href_link('account', '', 'SSL'));
                        }
                    }
                }
            }
            return $this->render('update-rdpr.tpl', [
                'token' => $token,
                'customers_dob' => $customers_dob,
                'customers_dobTmp' => $customers_dob,
                'messages' => $messages,
                'error' => $error,
                'mistake' => $gdpr->hasMistake()
            ]);

        } else {
            tep_redirect(tep_href_link('account', '', 'SSL'));
        }
    }

    public function actionRecreate() {
        global $cart;

        \common\helpers\Translation::init('js');
        \common\helpers\Translation::init('account/login');
        \common\helpers\Translation::init('account/create');
        $messageStack = \Yii::$container->get('message_stack');
        $token = Yii::$app->request->get('token');

        $guest_check_query = tep_db_query("select * from guest_check where token = '" . tep_db_input($token) . "'");
        if (tep_db_num_rows($guest_check_query) == 0) {
            tep_redirect(tep_href_link('account', '', 'SSL'));
        }
        $guest_check = tep_db_fetch_array($guest_check_query);

        $customer = (new Customer())->loadCustomer((int)$guest_check['customers_id']);

        if (!$customer->customers_id){
            tep_redirect(tep_href_link('account', '', 'SSL'));
        }
        global $navigation;

        $params = [
            'action' => tep_href_link('account/recreate', 'token='.$token, 'SSL'),
            'create_tab_active' => true,
            'show_socials' => $this->use_social,
        ];

        if ($wExt = \common\helpers\Acl::checkExtensionAllowed('WeddingRegistry', 'allowed')){
            $wExt::registerPartner($params);
        }

        $authContainer = new \frontend\forms\registration\AuthContainer();
        $params['enterModels'] = $authContainer->getForms('account/create');
        $params['showAddress'] = $authContainer->isShowAddress();

        if (is_object($params['enterModels']['registration'])){
            $params['enterModels']['registration']->preloadCustomersData($customer);
        }

        if (Yii::$app->request->isPost){
            $scenario = Yii::$app->request->post('scenario');

            $rCustomer = $authContainer->loadScenario($scenario);

            if (!$authContainer->hasErrors()){
                $guests = \common\models\Customers::find()->where(['customers_email_address' => $guest_check['email'], 'opc_temp_account' => 1])->all();
                if ($guests && $rCustomer){
                    foreach($guests as $guest){
                        opc::remove_temp_customer($guest->customers_id, $rCustomer->customers_id);
                        \common\models\GdprCheck::deleteAll(['customers_id' => $guest->customers_id]);
                        \common\models\GuestCheck::deleteAll(['customers_id' => $guest->customers_id]);
                    }
                }
                tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
            } else {
                $messageStack = \Yii::$container->get('message_stack');
                foreach ($authContainer->getErrors($scenario) as $error){
                    if (Yii::$app->request->isAjax) {
                        $messageStack->add_session((is_array($error)? implode("<br>", $error): $error), $scenario);
                    } else {
                        $messageStack->add((is_array($error)? implode("<br>", $error): $error), $scenario);
                    }
                }
                $messages = '';
                if ($messageStack->size($scenario) > 0){
                    $messages = $messageStack->output($scenario);
                }
                $params['messages_'.$scenario] = $messages;
            }
        }
        return $this->render('recreate-rdpr.tpl', ['params' => $params, 'settings' => ['tabsManually' => true]]);
    }

    public function actionSubscriptionRenewal() {
        \common\helpers\Translation::init('js');
        $token = Yii::$app->request->get('token');
        $messages = '';

        $regular_offers_check_query = tep_db_query("select * from regular_offers where token = '" . tep_db_input($token) . "'");
        if (tep_db_num_rows($regular_offers_check_query) == 0) {
            tep_redirect(tep_href_link('account', '', 'SSL'));
        }
        if ( Yii::$app->request->isPost) {
            $regular_offers_check = tep_db_fetch_array($regular_offers_check_query);

            $newsletter = (int) Yii::$app->request->post('newsletter');

            tep_db_query("DELETE FROM regular_offers WHERE customers_id=" . (int)$regular_offers_check['customers_id']);
            if ($newsletter > 0) {
                $regular_offers = (int) Yii::$app->request->post('regular_offers');
                if ($regular_offers > 0) {
                    $sql_data_array = array(
                        'customers_id' => (int)$regular_offers_check['customers_id'],
                        'period' => $regular_offers,
                        'date_end' => date('Y-m-d', strtotime('+'.$regular_offers.' months')),
                    );
                    tep_db_perform('regular_offers', $sql_data_array);
                }

            }
            tep_db_query("update " . TABLE_CUSTOMERS . " set customers_newsletter = '" . ($newsletter > 0 ? 1 : 0) . "' where customers_id = '" . (int)$regular_offers_check['customers_id'] . "'");

            $messages = TEXT_REGULAR_OFFERS_CONFIRM_SUCC;

        }

        return $this->render('subscription-renewal.tpl', [
            'token' => $token,
            'messages' => $messages,
        ]);
    }

    public function checkIsGuest($snapshotPage = null){
        global $navigation;
        if (Yii::$app->user->isGuest) {
            if (is_object($navigation) && method_exists($navigation, 'set_snapshot')){
                if (!empty($snapshotPage)){
                    $navigation->set_snapshot(array('mode' => 'SSL', 'page' => $snapshotPage));
                } else {
                    $navigation->set_snapshot();
                }
            }
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
    }

    public function accountRedirect($pageName)
    {
        if (Yii::$app->request->get('action') || Yii::$app->request->post()) {
            return '';
        }

        $isPage = ThemesSettings::find()->where([
            'theme_name' => THEME_NAME,
            'setting_group' => 'added_page',
            'setting_name' => 'account',
            'setting_value' => $pageName,
        ])->count();

        if ($isPage) {
           $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(array_merge([
                'account',
                'page_name' => \common\classes\design::pageName($pageName)
            ], Yii::$app->request->get())));
        }
    }

    public function actionPaymentTokenRename()
    {
      \common\helpers\Translation::init('account');
      $messages = [];
      $messages[] = [
        'type' => 'danger',
        'text' => PAYMENT_TOKEN_WASNT_UPDATED
      ];
      $ret = [];
      $tmp = Yii::$app->request->post('token_name', []);
      $is_default = Yii::$app->request->post('is_default', []);
      if (is_array($tmp)) {
        $tId = key($tmp);
        $name = trim(substr(strip_tags($tmp[$tId]), 0, 50));
        $isDefault = !empty($is_default[$tId]);
      }
      //if (Yii::$app->user->isGuest || empty($name) || !$tId) {      } else {
      if (!Yii::$app->user->isGuest && $tId) {
        try {
          $m = \common\models\PaymentTokens::findOne([
            'payment_tokens_id' => (int)$tId,
            'customers_id' => Yii::$app->user->getId(),
          ]);
          if ($m) {
            $m->card_name = $name;
            $m->is_default = $isDefault;
            $m->save();
            $ret = [
                  'id' => $tId,
                  'name' => $name,
                  'is_default' => $m->is_default,
                  'payment_class' => $m->payment_class,
                  ];
            $messages = [];
            $messages[] = [
              'type' => 'success',
              'text' => PAYMENT_TOKEN_UPDATED
              ];

          }
        } catch (\Exception $ex) {
          Yii::warning($ex->getMessage() . $ex->getTraceAsString());
        }
      }
      $ret['messages'] = $messages;

      return json_encode($ret);

    }

    public function actionPaymentTokenDelete()
    {
      \common\helpers\Translation::init('account');
      $messages = [];
      $messages[] = [
        'type' => 'danger',
        'text' => PAYMENT_TOKEN_WASNT_DELETED
      ];
      $ret = [];
      $tId = Yii::$app->request->get('id', false);
      $token = Yii::$app->request->get('token', '');
      $payment = Yii::$app->request->get('class', '');
      if (Yii::$app->user->isGuest || !$token || empty($payment)) {
        return '';
      }
      try {

        $manager = \common\services\OrderManager::loadManager();
        $paymentModules = $manager->getPaymentCollection();
        if ($paymentModules->isPaymentEnabled($payment)){
          $pm = $paymentModules->get($payment);
          $deleted = $pm->deleteToken(Yii::$app->user->getId(), $token);
          if (!$deleted) {
            throw new \Exception('incorrect token/customer Ids ' . (int)$tId . ' ' . Yii::$app->user->getId());
          }
        } else {

          $m = \common\models\PaymentTokens::findOne([
            'payment_tokens_id' => (int)$tId,
            'customers_id' => Yii::$app->user->getId(),
          ]);
          if ($m) {
            $m->delete();
          } else {
            throw new \Exception('incorrect token/customer Ids ' . (int)$tId . ' ' . Yii::$app->user->getId());
          }

        }

        $messages = [];
        $messages[] = [
          'type' => 'success',
          'text' => PAYMENT_TOKEN_DELETED
          ];


      } catch (\Exception $ex) {
        Yii::warning($ex->getMessage() . $ex->getTraceAsString());
      }
      $ret['messages'] = $messages;

      return json_encode($ret);
    }

  	public function actionRma() {

        global $languages_id, $navigation;

        $this->checkIsGuest();

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Rma', 'allowed')) {
            $ext::adminActionRequestPrint();
        }
    }

///newsletterLists moved to subscribers/account extension

    public function actionGiftCardPdf()
    {
        $gift_card_id = Yii::$app->request->get('gift_card_id', 0);
        $customer = Yii::$app->user->getIdentity();
        $customer_id = $customer->customers_id;

        if (!$gift_card_id || !$customer_id) {
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }
        $giftCard = \common\models\VirtualGiftCardInfo::find()->where([
            'virtual_gift_card_info_id' => $gift_card_id,
            'customers_id' => $customer->customers_id
        ])->asArray()->one();

        if (!$giftCard) {
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }

        $pages = [['name' => $giftCard['gift_card_design'] . '_pdf', 'params' => [
        ]]];

        return \backend\design\PDFBlock::widget([
            'pages' => $pages,
            'params' => [
                'theme_name' => THEME_NAME,
                'document_name' => 'gift_card.pdf',
            ]
        ]);
    }

    public function actionSendValidationRequest()
    {
        if (Yii::$app->request->isAjax) {
            $email = trim(filter_var(htmlentities(Yii::$app->request->get('email')), FILTER_SANITIZE_STRING));
            if (!empty($email)) {
                $cevEmail = md5($email);
                $cevCode = \common\helpers\Password::create_random_value(8, 'digits');
                $emailValidation = \common\models\CustomersEmailValidation::find()->where(['cev_email' => $cevEmail])->one();
                if ( !($emailValidation instanceof \common\models\CustomersEmailValidation) ) {
                    $emailValidation = new \common\models\CustomersEmailValidation();
                    $emailValidation->loadDefaultValues();
                    $emailValidation->cev_email = $cevEmail;
                }
                $emailValidation->cev_code = md5($cevCode);
                if ($emailValidation->save(false)){
                    $email_subject = TEXT_VERIFICATION_CODE;
                    $email_text = $cevCode;
                    \common\helpers\Mail::send('', $email, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
                }
            }
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return ['status' => 'ok'];
        }
        tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }

}
