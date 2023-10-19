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

use common\classes\platform;
use common\models\repositories\OrderRepository;
use frontend\design\Info;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Session;
use common\components\Customer;
use common\components\Socials;

/**
 * Site controller
 * @property \common\services\OrderManager $manager
 */
class CheckoutController extends \frontend\classes\AbstractCheckoutController {

    private $orderRepository;

    public function actionIndex() {

        global $breadcrumb;
        global $session_started, $cart;

        if (GROUPS_DISABLE_CHECKOUT) {
            tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

        $messageStack = \Yii::$container->get('message_stack');
        $currencies = \Yii::$container->get('currencies');
// redirect the customer to a friendly cookie-must-be-enabled page if cookies are disabled (or the session has not started)
        if ($session_started == false) {
            tep_redirect(tep_href_link(FILENAME_COOKIE_USAGE));
        }
        if ($cart->count_contents() < 1 || $cart->hasBlockedProducts()) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

        if (defined('MAX_ORDER_PRICE') && MAX_ORDER_PRICE > 0) {
            if ($cart->show_total() > (int)MAX_ORDER_PRICE) {
                $messageStack->add_session(ERROR_AMOUNT_TOO_LARGE, 'shopping_cart', 'error');
                tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
            }
        }

        $breadcrumb->add(NAVBAR_TITLE_CHECKOUT);

        $this->manager->remove("credit_covers");

        $cart->order_id = 0;

        $needLogged = \Yii::$app->get('platform')->getConfig(platform::currentId())->checkNeedLogged();

        if (Yii::$app->request->get('guest') && !$needLogged){
            $this->manager->set('guest', true);
            $this->manager->remove('account');
        }

        if (Yii::$app->request->get('account')){
            $this->manager->set('account', true);
            $this->manager->remove('guest');
        }

        if (!Yii::$app->user->isGuest) {
            $this->manager->remove('estimate_ship');
            $this->manager->remove('estimate_bill');
        }

        //$create_temp_account = ($this->manager->has('guest_email_address') && !empty($this->manager->get('guest_email_address')));

        /*if (!$create_temp_account && Yii::$app->user->isGuest) {
            tep_redirect(tep_href_link('checkout/login', '', 'SSL'));
        }*/

        $this->manager->loadCart($cart);

        if (!Yii::$app->user->isGuest) {
            if (!$this->manager->isCustomerAssigned()) {
                $this->manager->assignCustomer(Yii::$app->user->getId());
            }
        }

        $error = false;
        $order = $this->manager->createOrderInstance('\common\classes\Order');

        foreach (\common\helpers\Hooks::getList('checkout/index', '') as $filename) {
            include($filename);
        }
        foreach (\common\helpers\Hooks::getList('frontend/checkout/index', '') as $filename) {
            include($filename);
        }

        if (Yii::$app->request->isPost) {

            if (tep_not_null($_POST['comments'])) {
                $this->manager->set('comments', tep_db_prepare_input($_POST['comments']));
            }
            if(!$this->manager->validateShipping(\Yii::$app->request->post())) {
                $error = true;
            }

            if (!$this->manager->validateContactForm(Yii::$app->request->post())) {
                $error = true;
            }

            if (!$this->manager->validateCaptcha(\Yii::$app->request->post())) {
                $error = true;
            }

            $shipAsBill = Yii::$app->request->post('ship_as_bill', false) && true;
            $shipAsBill = $shipAsBill || (Yii::$app->request->post('bill_as_ship', false) && true);

            if (!$this->manager->validateAddressForms(Yii::$app->request->post(), '', $shipAsBill)) {
                $error = true;
            }

            if (!$error) {
                if (Yii::$app->user->isGuest) {
                    $this->manager->registerCustomerAccount($this->manager->has('guest')? 1:0);
                }
            }

            if ($this->manager->isShippingNeeded() && !is_array($this->manager->getShipping()) && count($this->manager->getShippingCollection()->getEnabledModules()) > 0) {
                $messageStack->add(TEXT_CHOOSE_SHIPPING_METHOD, 'one_page_checkout');
                $error = true;
                $this->manager->remove('shipping');
            }

            if (!$error) {
                $this->manager->set('cartID', $cart->cartID);
                foreach ($_POST as $key => $value) {
                    if (is_scalar($value)){
                        $this->manager->set('one_page_checkout_' . $key, $value);
                    }
                }

                /** @var \common\classes\payment $_p_modules*/
                $_p_modules = $this->manager->getPaymentCollection($this->manager->getPayment());
                if (Yii::$app->request->isAjax && $this->manager->getPayment() && ($_p_modules->popUpMode() || $_p_modules->directPayment())) {
                    if ($_p_modules->directPayment()) { //do confirmation validation also (it redirects in case of error)
                        //$tmp = Yii::$app->runAction(FILENAME_CHECKOUT_CONFIRMATION, ['ajax_check' => 1]);
                        $tmp = $this->actionConfirmation(1);
                        if (!empty($tmp['check']) && $tmp['check'] == 'ok'){
                            $data = [
                                'payment_error' => '',
                                'formCheck' => 'OK',
                                '_csrf' => Yii::$app->request->getCsrfToken(),
                            ];
                            return $this->asJson($data);
                        }
                    } else {
                        $data = [
                            'payment_error' => '',
                            'formCheck' => 'OK',
                            '_csrf' => Yii::$app->request->getCsrfToken(),
                        ];
                        return $this->asJson($data);
                    }

                } else {
                  tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
                }
            }
        }

        if ($this->manager->getCreditPayment()) {
            $this->manager->remove('cot_gv');
        }
        $this->manager->totalCollectPosts($_POST);

        $payment_error = '';
        if (isset($_GET['payment_error'])) {
            $currentPayment = $this->manager->getPaymentCollection()->get($_GET['payment_error']);
            if (is_object($currentPayment) && method_exists($currentPayment, 'get_error')) {
                $payment_error = $currentPayment->get_error();
                $this->manager->setPayment($_GET['payment_error']);
            }
        }

        $this->manager->getShippingQuotesByChoice();

        $order->prepareOrderInfo();
        $order->prepareOrderInfoTotals();

        if ($creditPayment = $this->manager->getCreditPayment()) {
            $creditPayment->processIfEnabled();
        }

        $this->manager->totalProcess();

        $this->manager->totalPreConfirmationCheck();

        \common\components\google\widgets\GoogleTagmanger::setEvent('checkout');

        if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
            //$messageStack->add(tep_db_prepare_input($_GET['error_message']), 'one_page_checkout');
        }

        $message = '';
        if ($messageStack->size('one_page_checkout') > 0) {
            $message = $messageStack->output('one_page_checkout');
        }

        $render_data = [
            'manager' => $this->manager, //new
            'params' => ['manager' => $this->manager],
            'worker' => Yii::$app->getUrlManager()->createUrl('checkout/worker'),
            'message' => $message,
            'payment_javascript_validation' => (!defined('ONE_PAGE_POST_PAYMENT') ? $this->manager->getPaymentJSValidation() : ''),
            'payment_error' => $payment_error,
        ];
        $page_name = Yii::$app->request->get('page_name');

        $noShipping = Yii::$app->request->get('no_shipping', 0);
        if (!$this->manager->isShippingNeeded() || (Info::isAdmin() && $noShipping)) {
            $this->manager->setTemplate('checkout_no_shipping');
            $render_data['noShipping'] = true;
        } else {
            $this->manager->setTemplate('checkout');
            $render_data['noShipping'] = false;
        }

        Info::addBlockToPageName($this->manager->getTemplate());

        if (
            (
            Info::themeSetting('checkout_view') == 1
            || Yii::$app->request->isAjax )
            &&
                $error == true && Yii::$app->request->isPost
        ) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $data = [
                'payment_error' => $payment_error,
                'message' => $message,
                'error_box' => $this->manager->errorForm,
            ];
            return Yii::$app->response->data = $data;
        } elseif (Info::themeSetting('checkout_view') == 1 && $page_name != 'index' || $page_name == 'index_2') {
            $tpl = 'index_2.tpl';
        } else {
            $tpl = 'index.tpl';
        }

        $render_data['page_name'] = $page_name ? $page_name :$this->manager->getTemplate();

        $render_data = array_merge($render_data, [
            'params' => $render_data,
        ]);

        \common\components\google\widgets\GoogleTagmanger::setEvent('orderStep2');
        foreach (\common\helpers\Hooks::getList('frontend/checkout/index/before-render', '') as $filename) {
            include($filename);
        }

        return $this->render($tpl, $render_data);
    }

    public function actionWorker($subaction) {
        global $cart;

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->user->isGuest) {
            if (!$this->manager->isCustomerAssigned()) {
                $this->manager->assignCustomer(Yii::$app->user->getId());
            }
        }

        $this->manager->loadCart($cart);
        $this->manager->createOrderInstance('\common\classes\Order');

        if ($this->manager->isShippingNeeded()) {
            $this->manager->setTemplate('checkout');
        } else {
            $this->manager->setTemplate('checkout_no_shipping');
        }

        return parent::actionWorker($subaction);
    }

    public function actionLogin() {
        global $cart;

        \common\helpers\Translation::init('js');

        $this->manager->remove('guest_email_address');

        if ($cart->count_contents() == 0) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

        if (!Yii::$app->user->isGuest) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }

        global $breadcrumb;
        $breadcrumb->add(NAVBAR_TITLE_CHECKOUT);
        $breadcrumb->add(NAVBAR_TITLE);

        $params = [
            'action' => tep_href_link('checkout/login', '', 'SSL'),
            'show_socials' => $this->use_social,
        ];

        $authContainer = new \frontend\forms\registration\AuthContainer();
        $params['enterModels'] = $authContainer->getForms('checkout');
        $params['showAddress'] = $authContainer->isShowAddress();

        if (Yii::$app->request->isPost) {
            $scenario = Yii::$app->request->post('scenario');

            $authContainer->loadScenario($scenario);
            if ($authContainer->hasErrors()) {
                $messageStack = \Yii::$container->get('message_stack');
                foreach ($authContainer->getErrors($scenario) as $error) {
                    if (Yii::$app->request->isAjax) {
                        $messageStack->add_session((is_array($error) ? implode("<br>", $error) : $error), $scenario);
                    } else {
                        $messageStack->add((is_array($error) ? implode("<br>", $error) : $error), $scenario);
                    }
                }
                $messages = '';
                if ($messageStack->size($scenario) > 0) {
                    $messages = $messageStack->output($scenario);
                }
                $params['messages_' . $scenario] = $messages;
                $params['active'] = $scenario;
            } else {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
            }
        }


        $page_name = Yii::$app->request->get('page_name');
        if (Info::themeSetting('checkout_view') || $page_name == 'login_2') {
            $tpl = 'login_2.tpl';
        } else {
            $check = tep_db_fetch_array(tep_db_query("select id from " . TABLE_DESIGN_BOXES . " where block_name = 'login_checkout' and theme_name = '" . THEME_NAME . "'"));
            if ($check['id'] || Info::isAdmin()) {
                $tpl = 'login-widgets.tpl';
            } else {
                $tpl = 'login.tpl';
            }
        }

        \common\components\google\widgets\GoogleTagmanger::setEvent('orderStep1');
        foreach (\common\helpers\Hooks::getList('frontend/checkout/login/before-render', '') as $filename) {
            include($filename);
        }

        return $this->render($tpl, ['params' => $params, 'settings' => ['tabsManually' => true]]);
    }

    public function actionPayment() {

        return $this->render('payment.tpl', ['products' => '']);
    }

    public function actionPaymentAddress() {

        return $this->render('payment-address.tpl', ['products' => '']);
    }

    public function actionShipping() {

        return $this->render('shipping.tpl', ['products' => '']);
    }

    public function actionShippingAddress() {

        return $this->render('shipping-address.tpl', ['products' => '']);
    }

    public function actionConfirmation($ajax_check = false) {
        global $navigation, $cart;
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        if (Yii::$app->user->isGuest) {
            $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }


        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            $ext::checkDisableCheckout($customer_groups_id); // shit - incorrect extension
        }
        if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_disable_checkout')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')); //same as b2b, not part of extension
        }

        $this->manager->loadCart($cart);

        if ($cart->order_id) {
            $orderModel = \common\models\Orders::find()->where(['orders_id' => (int) $cart->order_id])->one();
            if ($orderModel) {
                tep_redirect(tep_href_link('checkout/restart', 'order_id=' . $cart->order_id, 'SSL'));
            }
        }
// if there is nothing in the customers cart, redirect them to the shopping cart page
        if ($cart->count_contents() < 1 || $cart->hasBlockedProducts()) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

// avoid hack attempts during the checkout procedure by checking the internal cartID
        if ($cart->cartID !== $this->manager->get('cartID')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'cartChanged', 'SSL'));
        }

        if ($this->manager->get('shipping_choice') && !$this->manager->has('sendto')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'shipping', 'SSL'));
        }

        if (!$this->manager->has('billto')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'billing', 'SSL'));
        }

// if no shipping method has been selected, redirect the customer to the shipping method selection page

        if ($this->manager->isShippingNeeded() && $this->manager->get('shipping_choice') && !$this->manager->has('shipping')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'error_message=' . urlencode(ERROR_NO_SHIPPING_METHOD), 'SSL'));
        }

        if (defined('GERMAN_SITE') && GERMAN_SITE == 'True') {
            if (!$this->manager->has('conditions')) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_CONDITIONS_NOT_ACCEPTED), 'SSL', true, false));
            }
        }

        foreach ($this->manager->getAll() as $key => $value) {
            if (is_scalar($value) && strpos($key, 'one_page_checkout_') === 0){
                $_POST[str_replace('one_page_checkout_', '', $key)] = $value;
            }
        }

        if (tep_not_null($_POST['comments'] ?? null)) {
            $this->manager->set('comments', tep_db_prepare_input($_POST['comments']));
        }

        if (tep_not_null($_POST['pointto'] ?? null)) {
            $this->manager->set('pointto', tep_db_prepare_input($_POST['pointto']));
        }

        //global PO
        if (!empty($_POST['purchase_order'] ?? '')) {
          $_SESSION['purchase_order'] = tep_db_prepare_input($_POST['purchase_order']);
        } else {
          $_SESSION['purchase_order'] = '';
        }

        $this->manager->setSelectedPaymentModule($this->manager->getPayment());

        $this->manager->getShippingCollection($this->manager->getShipping());

        $order = $this->manager->createOrderInstance('\common\classes\Order');
        $this->manager->checkoutOrderWithAddresses();

        if ($this->manager->isShippingNeeded() && !$this->manager->checkShippingIsValid()) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'error_message=' . urlencode(ERROR_NO_SHIPPING_METHOD), 'SSL'));
        }

//ICW ADDED FOR CREDIT CLASS SYSTEM
        $this->manager->totalCollectPosts();
//ICW ADDED FOR CREDIT CLASS SYSTEM
        $this->manager->totalProcess();

        if ($ccExt = \common\helpers\Acl::checkExtensionAllowed('CustomerCredit', 'allowed')) {
            $ccExt::onCheckout($this->manager);
        }

        $this->manager->totalPreConfirmationCheck();

// ICW CREDIT CLASS Amended Line

        $paymentCollection = $this->manager->getPaymentCollection();
        $withoutPayment = count($paymentCollection->getEnabledModules()) == 0;

        if (!$withoutPayment) {
            if (!$paymentCollection->isPaymentSelected() && !$this->manager->get('credit_covers')) {
                $this->manager->remove('payment');
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
            }

            if (!defined('ONE_PAGE_POST_PAYMENT')) {
                $this->manager->paymentPreConfirmationCheck();
            }
        }

// Stock Check
        if (!$order->stockAllowCheckout()) {
            // Out of Stock
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

        $form_action_url = $this->manager->getPaymentUrl() ?? tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');

        $payment_confirmation = $this->manager->getPaymentConfirmation();

        $payment_process_button_hidden = $this->manager->getPaymentButton();

        global $breadcrumb;
        $breadcrumb->add(NAVBAR_TITLE_CHECKOUT);
        $breadcrumb->add(NAVBAR_TITLE);

        if (Yii::$app->request->isAjax && (Yii::$app->request->get('check', false) === 'only' || !empty($ajax_check)) ) {
            if (!empty($ajax_check)) {
                return ['check' => 'ok'];
            } else {
                return $this->asJson(['check' => 'ok']);
            }
        }

        \common\components\google\widgets\GoogleTagmanger::setEvent('checkout');

        $page_name = Yii::$app->request->get('page_name');

        if (Info::themeSetting('checkout_view') == 1 && $page_name != 'confirmation' || $page_name == 'confirmation_2') {
            $tpl = 'confirmation_2.tpl';
            $render_data['page_name'] = 'confirmation_2';
            $block_name = 'checkout_confirmation';
        } else {
            $tpl = 'confirmation.tpl';
            $block_name = 'confirmation';
        }

        $deliveryAddress = '';
        if ($this->manager->isDeliveryUsed()) {
            $deliveryAddress = \common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>');
        }
        $deliveryAddress = (!empty($deliveryAddress) ? $deliveryAddress : TEXT_WITHOUT_SHIPPING_ADDRESS);
        $billingAddress = \common\helpers\Address::address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>');
        $billingAddress = (!empty($billingAddress) ? $billingAddress : 'Without billing addreess');

        if ($form_action_url != tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL') ) {
          $skipCsrf = true;
        } else {
          $skipCsrf = false;
        }
        $forceConfirmationPage = false;
        if (
            ($this->manager->has('force_confirmation_page') && $this->manager->get('force_confirmation_page'))
            || ($this->manager->has('ppartner_total_check') && $this->manager->get('ppartner_total_check'))
            ) {
            $forceConfirmationPage = true;
        }

        if (!$forceConfirmationPage && defined('SKIP_CHECKOUT') && SKIP_CHECKOUT == 'True') {
            $possible = true;
            $checkout_post = [];
            if (!empty($payment_process_button_hidden)) {
              $tmp = $this->manager->getPaymentButtonPost();
              if (is_array($tmp)) {
                $checkout_post = $tmp;
              } else {
                $possible = false;
              }
            }
            if ($form_action_url != tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL') ) {

              if ($this->manager->getPayment() && $this->manager->getPaymentCollection()->confirmationCurlAllowed() && !empty($checkout_post)) {
                $ch = curl_init($form_action_url);

                // set URL and other appropriate options
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $checkout_post);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: 0'));

                // grab URL and pass it to the browser
                $response = curl_exec($ch);

                // close cURL resource, and free up system resources
                curl_close($ch);
                return;
              }
              elseif ($this->manager->getPayment() && $this->manager->getPaymentCollection()->confirmationAutosubmit()) {
              //small HTML with autosubmit by JS ("click here if page is not redirected properly")
                return '<html><body style="text-align:center"><form action="' . $form_action_url . '" method="post">' . $payment_process_button_hidden .
                    '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+P+/HgAFhAJ/wlseKgAAAABJRU5ErkJggg==">' .
                    '<script>document.forms[0].submit();</script>'.
                    '<a href="javascript:document.forms[0].submit();">Click here if form is not redirected automatically.</a>' .
                    '</form></body></html>';
              }
              $possible = false;
              $skipCsrf = true; // posted to another server and csrf is not a part of signature, but could be added by beginForm method.

            }

            if ($possible) {
              \Yii::$app->settings->set('checkout_post', $checkout_post);
              tep_redirect(tep_href_link('checkout/process', 'skip=1', 'SSL'));
            }
        } else {
            \common\components\google\widgets\GoogleTagmanger::setEvent('orderStep3');
        }

        $render_data = [
            'shipping_address_link' => tep_href_link('checkout/index#shipping_address'),
            'billing_address_link' => tep_href_link('checkout/index#billing_address'),
            'shipping_method_link' => tep_href_link('checkout/index#shipping_method'),
            'payment_method_link' => tep_href_link('checkout/index#payment_method'),
            'cart_link' => tep_href_link('shopping-cart'),
            'address_label_delivery' => $deliveryAddress,
            'address_label_billing' => $billingAddress,
            'order' => $order,
            'is_shipable_order' => $this->manager->isShippingNeeded(),
            'form_action_url' => $form_action_url,
            'payment_process_button_hidden' => $payment_process_button_hidden,
            'payment_confirmation' => $payment_confirmation,
            'manager' => $this->manager,
            'skipCsrf' => $skipCsrf,
        ];

        $noShipping = Yii::$app->request->get('no_shipping', 0);
        if (!$render_data['is_shipable_order'] || (Info::isAdmin() && $noShipping)) {
            $render_data['noShipping'] = true;
            Info::addBlockToPageName('no_shipping');
        } else {
            $render_data['noShipping'] = false;
        }

        //check if page filed in designer
        $designCheckout = \common\models\DesignBoxes::find()->where([
                    'theme_name' => THEME_NAME,
                    'block_name' => $block_name . ($render_data['noShipping'] ? '_no_shipping' : ''),
                ])->count();

        $render_data = array_merge($render_data, [
            'params' => $render_data,
            'widgets' => $designCheckout > 0 || Info::isAdmin() ? true : false
        ]);

        foreach (\common\helpers\Hooks::getList('frontend/checkout/confirmation/before-render', '') as $filename) {
            include($filename);
        }

        return $this->render($tpl, $render_data);
    }

    public function actionSuccess() {
        global $breadcrumb, $platform_code, $cart;

        if (!$this->manager->isCustomerAssigned() && !\frontend\design\Info::isAdmin()) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }
        $this->layout = 'main.tpl';

        $customer_id = Yii::$app->user->getId();

        if (tep_session_is_registered('platform_code')) {
            $platform_code = '';
        }

        $breadcrumb->add(NAVBAR_TITLE_CHECKOUT);
        $breadcrumb->add(NAVBAR_TITLE);

        $order_info = false;
        $cart->order_id = 0;

        $order_id = intval(Yii::$app->request->getQueryParam('order_id', 0));

        if ($order_id) {
            $order_info = tep_db_fetch_array(tep_db_query(
                            "SELECT orders_id, orders_status " .
                            "FROM " . TABLE_ORDERS . " " .
                            "WHERE orders_id='" . (int) $order_id . "' AND customers_id = '" . (int) $customer_id . "'"
            ));
        }
        if (!is_array($order_info)) {
            $orders_query = tep_db_query(
                    "select orders_id, orders_status " .
                    "from " . TABLE_ORDERS . " " .
                    "where customers_id = '" . (int) $customer_id . "' " .
                    "order by /*date_purchased*/ orders_id desc limit 1"
            );
            if (tep_db_num_rows($orders_query)) {
                $order_info = tep_db_fetch_array($orders_query);
            }
        }
        $order_info_data = array(
            'order_id' => 0,
            'print_order_href' => (Info::isAdmin() ? '1111' : ''),
            'order' => false,
        );
        if (is_array($order_info)) {
            $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $order_info['orders_id']);

            \common\components\google\widgets\GoogleTagmanger::setEvent('checkout');

            $order->info['order_id'] = $order_info['orders_id'];
            $order_info_data = array(
                'order_id' => $order_info['orders_id'],
                'print_order_href' => tep_href_link('account/invoice', \common\helpers\Output::get_all_get_params(array('order_id')) . 'orders_id=' . $order_info['orders_id'], 'SSL'),
                'order' => $order,
                'manager' => $this->manager,
            );
        }

        \common\components\google\widgets\GoogleTagmanger::setEvent('orderSuccess');

        foreach (\common\helpers\Hooks::getList('checkout/success', '') as $filename) {
            include($filename);
        }
        foreach (\common\helpers\Hooks::getList('frontend/checkout/success', '') as $filename) {
            include($filename);
        }

        if (defined('AUTO_LOGOFF_GUEST_ON_SUCCESS') && AUTO_LOGOFF_GUEST_ON_SUCCESS=='True' && !\Yii::$app->user->isGuest) {
            $customer = \Yii::$app->user->getIdentity();
            if ($customer->opc_temp_account == 1) {
                \Yii::$app->settings->clear(['languages_id']);
                \Yii::$app->user->getIdentity()->logoffCustomer();

                $cart->reset();

                unset($order_info_data['print_order_href']);
            }
        }

        return $this->render('success.tpl', array_merge([
                    'products' => '',
                    'continue_href' => tep_href_link(FILENAME_DEFAULT, '', 'NONSSL'),
                    'params' => $order_info_data,
                    'order' => $order_info_data['order'],
               ], $order_info_data));
    }

    public function actionFail()
    {
        $order_id = (int)\Yii::$app->request->getQueryParam('order_id', 0);
        $lastStatus = $this->orderRepository->getLastHistoryStatus($order_id);
        return $this->render('fail.tpl', [
                    'continue_href' => tep_href_link(FILENAME_DEFAULT, '', 'NONSSL'),
                    'message' => $lastStatus ? $lastStatus->comments : "Somthing wrong"
                ]);
    }

    public function actionProcess() {
        $skip = (int)\Yii::$app->settings->get('skip');
        if (defined('SKIP_CHECKOUT') && SKIP_CHECKOUT == 'True' && $skip == 1) {
            $checkout_post = \Yii::$app->settings->get('checkout_post');
            if (is_array($checkout_post)) {
                foreach ($checkout_post as $key => $value) {
                    $_POST[$key] = $value;
                }
            }
        }
        global $navigation, $cart;
        // if the customer is not logged on, redirect them to the login page
        if (Yii::$app->user->isGuest) {
            $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }

        $this->manager->loadCart($cart);

        if ($this->manager->getShippingChoice() && !$this->manager->get('sendto')) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }

        $payment_modules = $this->manager->getPaymentCollection();
        $withoutPayment = count($payment_modules->getEnabledModules()) ? false : true;

        if (!$withoutPayment){
            if ((tep_not_null(MODULE_PAYMENT_INSTALLED)) && (!$this->manager->has('payment'))) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }

            if (!$this->manager->has('billto')) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }
        }

// avoid hack attempts during the checkout procedure by checking the internal cartID
        if (isset($cart->cartID) && $this->manager->has('cartID')) {
            if ($cart->cartID != (string) $this->manager->get('cartID')) {
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
            }
        }

        foreach (\common\helpers\Hooks::getList('frontend/checkout/process/cart-loaded') as $filename) {
            include($filename);
        }
// load selected payment module

        $payment = $this->manager->getPayment();

        if ($this->manager->get('credit_covers') && !in_array($payment, ['covered_by_coupon']) /* && $payment != 'paypal_express' */) {
            $payment = ''; //ICW added for CREDIT CLASS
            $this->manager->remove('payment');
        }

        if ($payment){
            $payment_modules = $this->manager->setSelectedPaymentModule($payment);
        }

        $this->manager->getShippingCollection($this->manager->getShipping());
/** @var \common\classes\Order $order */
        $order = $this->manager->createOrderInstance('\common\classes\Order');
        $this->manager->checkoutOrderWithAddresses();

        if ($this->manager->isShippingNeeded() && !$this->manager->checkShippingIsValid()) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, 'error_message=' . urlencode(ERROR_NO_SHIPPING_METHOD), 'SSL'));
        }

        if (!$withoutPayment) {
            if (defined('ONE_PAGE_POST_PAYMENT') && preg_match("/" . preg_quote(FILENAME_CHECKOUT_CONFIRMATION, "/") . "/", $_SERVER['HTTP_REFERER'])) {
                $this->manager->paymentPreConfirmationCheck();
            }
        }
// load the selected shipping module

        if ($this->manager->get('credit_covers')) {
            if (defined('MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID_COVERS')) {
                $order->info['order_status'] = MODULE_ORDER_TOTAL_GV_ORDER_STATUS_ID_COVERS;
            } else if (defined('MODULE_ORDER_TOTAL_BONUS_POINTS_ORDER_STATUS_ID_COVERS')) {
                $order->info['order_status'] = MODULE_ORDER_TOTAL_BONUS_POINTS_ORDER_STATUS_ID_COVERS;
            }
        }

        foreach (\common\helpers\Hooks::getList('frontend/checkout/process/before-process') as $filename) {
            include($filename);
        }

        $this->manager->totalProcess();

        if ($ccExt = \common\helpers\Acl::checkExtensionAllowed('CustomerCredit', 'allowed')) {
            $ccExt::onCheckout($this->manager);
        }

// load the before_process function from the payment modules

        if (!$withoutPayment) {
            $payment_modules->before_process();
            $order->update_piad_information();
        }

        $order->save_order();

        $order->save_details();

        $order->save_products();

        // process
        foreach (\common\helpers\Hooks::getList('checkout/process', '') as $filename) { // deprecated
            include($filename);
        }
        foreach (\common\helpers\Hooks::getList('frontend/checkout/process/after-save', '') as $filename) {
            include($filename);
        }

        if (!$withoutPayment) {
            $this->manager->getTotalCollection()->apply_credit(); //ICW ADDED FOR CREDIT CLASS SYSTEM
        }

        foreach ($order->products as $i => $product) {
            $uuid = $payment_modules->before_subscription($i);
            if ($uuid != false) {
                $info = $payment_modules->get_subscription_info($uuid);
                $subscription_id = $order->save_subscription(0, $order->order_id, $i, $uuid, $info);
            }
        }
        $cart->order_id = $order->order_id;

        if (!$withoutPayment) {
            $payment_modules->after_process();
        }

        $payment_modules->trackCredits();

        // remove drop_ship address
        $oinfo = tep_db_fetch_array(tep_db_query('SELECT customers_id FROM orders WHERE orders_id='.$order->order_id));
        if (isset($oinfo['customers_id']) && $oinfo['customers_id'] > 0) {
            $AddressBooks = \common\models\AddressBook::find()
                    ->where(['customers_id' => $oinfo['customers_id']])
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

        $this->manager->clearAfterProcess();

        foreach (\common\helpers\Hooks::getList('checkout/after-process', '') as $filename) { //deprecated
            include($filename);
        }
        foreach (\common\helpers\Hooks::getList('frontend/checkout/process/end', '') as $filename) {
            include($filename);
        }

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, 'order_id=' . $order->order_id, 'SSL'));
    }

    public function actionReorder() {
        global $navigation, $cart;

        $messageStack = \Yii::$container->get('message_stack');
        $currencies = \Yii::$container->get('currencies');

        if (Yii::$app->user->isGuest) {
            $navigation->set_snapshot(array('mode' => 'SSL', 'page' => 'checkout/reorder', 'get' => 'order_id=' . (int) (isset($_GET['order_id']) ? $_GET['order_id'] : 0)));
            tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
        }

        $oID = (int) $_GET['order_id'];
        $customer_id = Yii::$app->user->getId();

        $get_order_info_r = tep_db_query(
                "SELECT orders_id, shipping_class, payment_class " .
                "FROM " . TABLE_ORDERS . " " .
                "WHERE orders_id='" . (int) $oID . "' AND customers_id='" . (int) $customer_id . "' "
        );

        if (tep_db_num_rows($get_order_info_r) == 0) {
            tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
        }

        $_order_info = tep_db_fetch_array($get_order_info_r);

        $get_products_r = tep_db_query(
                "SELECT * " .
                "FROM " . TABLE_ORDERS_PRODUCTS . " " .
                "WHERE orders_id='{$_order_info['orders_id']}' " .
                "ORDER BY is_giveaway, orders_products_id"
        );
        while ($get_product = tep_db_fetch_array($get_products_r)) {
            if (!$get_product['is_giveaway'] && !\common\helpers\Product::check_product((int) $get_product['uprid'])) {
                $messageStack->add(sprintf(ERROR_REORDER_PRODUCT_DISABLED_S, $get_product['products_name']), 'shopping_cart', 'warning');
                continue;
            }
            if ($get_product['is_giveaway'] && !\common\helpers\Product::is_giveaway((int) $get_product['uprid'])) {
                $messageStack->add(sprintf(ERROR_REORDER_PRODUCT_DISABLED_S, $get_product['products_name']), 'shopping_cart', 'warning');
                continue;
            }

            $attr = '';
            if (strpos($get_product['uprid'], '{') !== false && preg_match_all('/{(\d+)}(\d+)/', $get_product['uprid'], $attr_parts)) {
                $attr = array();
                foreach ($attr_parts[1] as $_idx => $opt) {
                    $attr[$opt] = $attr_parts[2][$_idx];
                }
            }
            if (!$cart->is_valid_product_data((int) $get_product['uprid'], $attr)) {
                $messageStack->add(sprintf(ERROR_REORDER_PRODUCT_VARIATION_MISSING_S, $get_product['products_name']), 'shopping_cart', 'warning');
                continue;
            }
            if ($get_product['is_giveaway']) {
                $cart->add_cart((int) $get_product['uprid'], /* $cart->get_quantity(\common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid((int)$get_product['uprid'], $attr)),1)+ */ $get_product['products_quantity'], $attr, true, 1);
            } else {
                $cart->add_cart((int) $get_product['uprid'], $cart->get_quantity(\common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid((int) $get_product['uprid'], $attr))) + $get_product['products_quantity'], $attr, false, 0, !!$get_product['gift_wrapped']);
            }
        }

        $cart->setReference($oID);

        $this->manager->set('cartID', $cart->cartID);

        $order_sendto = \common\helpers\Address::find_order_ab($_order_info['orders_id'], 'delivery');
        $order_billto = \common\helpers\Address::find_order_ab($_order_info['orders_id'], 'billing');

        if (is_numeric($order_billto) || (is_array($order_billto) && $order_billto['country_id'])) {
            $this->manager->set('billto', $order_billto);
        } else {
            $this->manager->set('billto', Yii::$app->user->getIdentity()->customers_default_address_id);
            unset($order_billto);
        }
        if (is_numeric($order_sendto) || (is_array($order_sendto) && $order_sendto['country_id']) ) {
            $this->manager->set('sendto', $order_sendto);
        } else {
            $this->manager->set('sendto', Yii::$app->user->getIdentity()->customers_default_address_id);
            unset($order_sendto);
        }

        if ($messageStack->size('shopping_cart') > 0) {
            $messageStack->convert_to_session('shopping_cart');
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL'));
        } elseif (is_array($order_sendto) || is_array($order_billto)) {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }

        $this->manager->loadCart($cart);

        $payment = $_order_info['payment_class'];
        $this->manager->set('payment', $payment);

        $order = $this->manager->createOrderInstance('\common\classes\Order');

        $this->manager->setSelectedShipping($_order_info['shipping_class']);
        if ($shipping = $this->manager->getShipping()) {
            $this->manager->reverseChoiceByShipping(['module' => $shipping['module']]);
        } else {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
        }
        if ( defined('SKIP_CHECKOUT') && SKIP_CHECKOUT == 'True') {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'));
        } else {
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
        }
    }

    public function __construct($id, $module, OrderRepository $orderRepository, $config = []) {
        \common\helpers\Translation::init('checkout');
        \common\helpers\Translation::init('checkout/login');

        $this->orderRepository = $orderRepository;

        $this->manager = new \common\services\OrderManager(Yii::$app->get('storage'));

        parent::__construct($id, $module, $config);

        parent::checkoutInit();

        $customers_id = (int)Yii::$app->user->getId();

        /**
         * @var $GroupAdministrator \common\extensions\GroupAdministrator\GroupAdministrator
         */
        if ($GroupAdministrator = \common\helpers\Extensions::isAllowed('GroupAdministrator')) {
            $gIds = $GroupAdministrator::isGroupAdministratorFor($customers_id);
        } else {
            $gIds = false;
        }
        if (is_array($gIds)) {
            $this->manager->setModulesVisibility(['shop_order', 'moderator']);
        } else {
            $this->manager->setModulesVisibility(['shop_order']);
        }
    }

    public function actionAmazonlogin() {
        ///kostyli for now (oAuth - not all required detail).

        $debug = false;
        $amazon_payment = $this->manager->getPaymentCollection('amazon_payment')->getSelectedPayment();
        if (!$amazon_payment) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }
        \common\helpers\Translation::init('checkout/login');

        $login_results = array();
        $errors = array();
        $logged = false;

        if (isset($_COOKIE['amazon_Login_state_cache'])) {
            $login_state = json_decode($_COOKIE['amazon_Login_state_cache'], true);
            $login_results['login_state'] = $login_state;

            if (!$logged && isset($login_state['client_id']) && isset($_SESSION['amazon_eu_login']['login_state']['client_id']) && isset($_SESSION['amazon_eu_login']['token_info']['aud']) && isset($_SESSION['amazon_eu_login']['token_info']['user_id']) && isset($_SESSION['amazon_eu_login']['user_profile']['user_id']) && $_SESSION['amazon_eu_login']['login_state']['client_id'] == $_SESSION['amazon_eu_login']['token_info']['aud'] && $_SESSION['amazon_eu_login']['token_info']['aud'] == $login_state['client_id'] && $_SESSION['amazon_eu_login']['token_info']['user_id'] == $_SESSION['amazon_eu_login']['user_profile']['user_id']) {

                $logged = true;
                $login_results = $_SESSION['amazon_eu_login'];
            } else {
                if (isset($_SESSION['amazon_eu_login'])) {
                    unset($_SESSION['amazon_eu_login']);
                }
            }

            if (!$logged && isset($login_state['access_token']) && $login_state['access_token']) {
                $link = $amazon_payment::getTokenUrl() . '?access_token=' . urlencode($login_state['access_token']);

                if ($debug) {
                    Yii::info($link);
                }

                $c = curl_init($link);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                $r = curl_exec($c);
                curl_close($c);

                if ($debug) {
                    Yii::info(print_r($r));
                }

                $d = json_decode($r, true);
                $login_results['token_info'] = $d;

                if (isset($d['aud']) && $d['aud'] == $amazon_payment::getClientId()) {

                    $c = curl_init($amazon_payment::getProfileUrl());
                    curl_setopt($c, CURLOPT_HTTPHEADER, array('Authorization: bearer ' . $login_state['access_token']));
                    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

                    $r = curl_exec($c);
                    curl_close($c);

                    if ($debug) {
                        Yii::info(print_r($r));
                    }

                    $d = json_decode($r, true);

                    if (isset($d['user_id']) && $d['user_id']) {
                        $login_results['user_profile'] = $d;
                    } else {
                        // error reporting
                        $errors[] = 'No profile information';
                    }
                } else {
                    // error reporting
                    $errors[] = 'No or wrong ClientID';
                }
            }
        }

        if (!$errors && isset($login_results['user_profile']) && $login_results['user_profile']) {
            $_SESSION['amazon_eu_login'] = $login_results;

            //save/login customer
            global $cart;
            $messageStack = \Yii::$container->get('message_stack');
            $email = tep_db_prepare_input($login_results['user_profile']['email']);
            $amazon_id = tep_db_prepare_input($login_results['user_profile']['user_id']);
            $name = tep_db_prepare_input($login_results['user_profile']['name']);
            $parts = explode(' ', $name, 2);
            $firstname = tep_db_prepare_input(trim(isset($parts[0]) ? $parts[0] : ''));
            $lastname = tep_db_prepare_input(trim(isset($parts[1]) ? $parts[1] : ''));

            $customer = new Customer(Customer::LOGIN_SOCIALS);
            if (!$customer->loginCustomer($email, Socials::HASHCODE)) {
                if (tep_not_null($email)) {

                    $model = new \frontend\forms\registration\CustomerRegistration();

                    if (ENABLE_CUSTOMER_GROUP_CHOOSE == 'True') {
                        $model->group = 0; //ToDo, ask customer for group
                    } else {
                        if (!defined("DEFAULT_USER_LOGIN_GROUP")) {
                            $model->group = 0;
                        } else {
                            $model->group = DEFAULT_USER_LOGIN_GROUP;
                        }
                    }
                    $model->password = \common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
                    $model->newsletter = 0;
                    $model->email_address = $email;

                    if (isset($firstname) && !empty($firstname)) {
                        $model->firstname = $firstname;
                    }

                    if (isset($lastname) && !empty($lastname)) {
                        $model->lastname = $lastname;
                    }

                    $model->country = (int) STORE_COUNTRY;
                    $model->zone_id = (int) STORE_ZONE;

                    $customer->registerCustomer($model);
                } else {
                    $messageStack->add_session(TEXT_INVALID_EMAIL, 'login', 'error');
                    return Yii::$app->controller->redirect(['account/login']);
                }
            } else {
                //2do link usual and amazon accounts.
            }

            $this->manager->assignCustomer($customer->customers_id);

            $this->manager->set('cartID', $cart->cartID);

            if (isset($_GET['error_message']) && tep_not_null($_GET['error_message'])) {
                $messageStack->add(tep_db_prepare_input($_GET['error_message']), 'one_page_checkout');
            }

            $message = '';
            if ($messageStack->size('one_page_checkout') > 0) {
                $message = $messageStack->output('one_page_checkout');
            }

            //show address and payment widgets.
            return $this->render('amazon', [
                        'clientId' => $amazon_payment::getClientId(),
                        'merchantId' => $amazon_payment::getMerchantId(),
                        'widgetUrl' => $amazon_payment::getWidgetUrl(),
                        'updateShippingUrl' => Yii::$app->urlManager->createAbsoluteUrl(['checkout/amazonaddress']),
            ]);
        } else {
            if (isset($_SESSION['amazon_eu_login'])) {
                unset($_SESSION['amazon_eu_login']);
            }

            if (isset($_COOKIE['amazon_Login_state_cache'])) {
                unset($_COOKIE['amazon_Login_state_cache']);
            }
        }
        //if any error - go to shopping cart again
        //return Yii::$app->controller->redirect([FILENAME_SHOPPING_CART]);
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
    }

    public function actionAmazonaddress() {
        //TL session could expire befoore amazon's
        ///VL2check 2do (somethiong not OK

        if (Yii::$app->user->isGuest) {
            tep_redirect(Yii::$app->urlManager->createUrl(['checkout/amazonlogin']));
        } else {
            $this->manager->assignCustomer(Yii::$app->user->getId());
        }

        $amazon_payment = $this->manager->getPaymentCollection('amazon_payment')->getSelectedPayment();
        if (!$amazon_payment) {
            tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        }

        $customer = $this->manager->getCustomersIdentity();

        $ref = Yii::$app->request->post('amazon_order_reference');
        $_SESSION['amazon_eu_login']['orderRef'] = $ref;
        $oData = $amazon_payment->getOrderReferenceDetails($ref);
        $destination = isset($oData['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination']) ? $oData ['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination']['PhysicalDestination'] : array();
        $adr = [];
        if (isset($destination['CountryCode'])) {
            if (isset($destination['CountryCode'])) {
                $tmp = \common\helpers\Country::get_country_info_by_iso($destination['CountryCode']);
                if (is_array($tmp) && isset($tmp['id'])) {
                    $adr['country'] = $tmp['id'];
                } else {
                    $adr['country'] = '';
                }
            } else {
                $adr['country'] = '';
            }
            $adr['city'] = (isset($destination['City']) ? $destination['City'] : '');
            $adr['postcode'] = (isset($destination['PostalCode']) ? $destination['PostalCode'] : '');
            $adr['state'] = (isset($destination['StateOrRegion']) ? $destination['StateOrRegion'] : '');
        }

        $address = tep_db_query("select address_book_id from " . TABLE_ADDRESS_BOOK .
                " where customers_id='" . $customer->customers_id . "' and TRIM(entry_street_address)='' "
                . "and entry_postcode in ('', '" . tep_db_input($adr['postcode']) . "') and entry_city in ('', '" . tep_db_input($adr['city']) . "')");

        if ($d = tep_db_fetch_array($address)) {
            $this->manager->set('sendto', $d['address_book_id']);
            $this->manager->set('billto', $d['address_book_id']);
        } else {
            $country = isset($adr['country']) && (int) $adr['country'] ? (int) $adr['country'] : (int) STORE_COUNTRY;

            $state = tep_db_prepare_input($adr['state']);
            if (is_int($adr['state'])) {
                $zone_id = tep_db_prepare_input($adr['state']);
                $state = '';
            } else {
                $zone_id = 0;
                $qZones = \common\models\Zones::find()->where(['zone_country_id' => $country]);
                if ($qZones->count() > 0) {
                    $qZones = \common\models\Zones::find()->where(['zone_country_id' => $country])
                                    ->andWhere(['or',
                                        ['zone_code' => $state],
                                        ['zone_name' => $state]
                                    ])->all();
                    if (count($qZones)) {
                        $zone_id = $qZones[0]->zone_id;
                        $state = $qZones[0]->zone_name;
                    }
                }
            }

            $address = [
                'entry_postcode' => $adr['postcode'],
                'entry_city' => $adr['city'],
                'entry_country_id' => $country,
                'entry_state' => $state,
                'entry_zone_id' => $zone_id,
            ];

            $book = $customer->addAddress($address);
            $this->manager->set('sendto', $book->address_book_id);
            $this->manager->set('billto', $book->address_book_id);
        }

        $this->manager->remove('cot_gv');

        $this->manager->remove('cc_id');
        $this->manager->remove('cc_code');

        if (!$this->manager->has('pointto')) {
            $this->manager->set('pointto', 0);
        }

        $cart = $_SESSION['cart'];

        $this->manager->loadCart($cart);

        $this->manager->createOrderInstance('\common\classes\Order');

        $this->manager->getShippingQuotesByChoice();
        $this->manager->checkoutOrderWithAddresses();

        $this->manager->totalCollectPosts($_POST);

        $this->manager->totalPreConfirmationCheck();

        $order_total_output = $this->manager->getTotalOutput(true, 'TEXT_CHECKOUT');

        $response = array(
            'replace' => array(
                'shipping_method' => \frontend\design\boxes\checkout\Shipping::widget(['params' => $this->manager]),
                'order_totals' => \frontend\design\boxes\checkout\Totals::widget(['params' => $this->manager]),
            ),
        );

        echo json_encode($response);

        Yii::warning($this->manager->getShipping(), 'amazon_address');
        die;

        if (true) {
            //global $currencies;
            //global $total_weight, $total_count, $order, $shipping, $select_shipping, $pointto;
            //global $cc_id, $cot_gv;
            // populate posted data into order
            /*
              global $opc_billto, $opc_sendto;

              $_country_info = \common\helpers\Country::get_countries($country, true);
              $opc_billto = array(
              'gender' => isset($gender) ? $gender : null,
              'firstname' => isset($firstname) ? $firstname : '',
              'lastname' => isset($lastname) ? $lastname : '',
              'street_address' => isset($street_address) ? $street_address : '',
              'suburb' => isset($suburb) ? $suburb : '',
              'city' => isset($city) ? $city : '',
              'postcode' => isset($postcode) ? $postcode : '',
              'state' => isset($state) ? $state : '',
              'zone_id' => isset($zone_id) ? $zone_id : 0,
              'country' => array(
              'id' => $country,
              'title' => $_country_info['countries_name'],
              'iso_code_2' => $_country_info['countries_iso_code_2'],
              'iso_code_3' => $_country_info['countries_iso_code_3'],
              ),
              'country_id' => $country,
              'format_id' => \common\helpers\Address::get_address_format_id($country),
              );


              $_country_info = \common\helpers\Country::get_countries($ship_country, true);
              $opc_sendto = array(
              'gender' => isset($shipping_gender) ? $shipping_gender : null,
              'firstname' => isset($ship_firstname) ? $ship_firstname : '',
              'lastname' => isset($ship_lastname) ? $ship_lastname : '',
              'street_address' => isset($ship_street_address) ? $ship_street_address : '',
              'suburb' => isset($ship_suburb) ? $ship_suburb : '',
              'city' => isset($ship_city) ? $ship_city : '',
              'postcode' => isset($ship_postcode) ? $ship_postcode : '',
              'state' => isset($ship_state) ? $ship_state : '',
              'zone_id' => isset($ship_zone_id) ? $ship_zone_id : 0,
              'country' => array(
              'id' => $ship_country,
              'title' => $_country_info['countries_name'],
              'iso_code_2' => $_country_info['countries_iso_code_2'],
              'iso_code_3' => $_country_info['countries_iso_code_3'],
              ),
              'country_id' => $ship_country,
              'format_id' => \common\helpers\Address::get_address_format_id($ship_country),
              );
              $order = new \common\classes\OpcOrder();

              $company_vat_status = 0;
              $customer_company_vat_status = '&nbsp;';

              if ($ext = \common\helpers\Acl::checkExtensionAllowed('VatOnOrder', 'allowed')) {
              list($company_vat_status, $customer_company_vat_status) = $ext::update_vat_status($order);
              $order->customer['company_vat_status'] = $company_vat_status;
              } */



            /**
             * @var $cart \shoppingCart
             */
            // weight and count needed for shipping !
            //$total_weight = $cart->show_weight();
            //$total_count = $cart->count_contents();
            /*
              $order_total_modules = new \common\classes\order_total();

              $free_shipping = false;
              $quotes = array();
              if (($order->content_type != 'virtual') && ($order->content_type != 'virtual_weight')) {

              $shipping_modules = new \common\classes\shipping();

              if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
              $pass = false;

              switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
              case 'national':
              if ($order->delivery['country_id'] == STORE_COUNTRY) {
              $pass = true;
              }
              break;
              case 'international':
              if ($order->delivery['country_id'] != STORE_COUNTRY) {
              $pass = true;
              }
              break;
              case 'both':
              $pass = true;
              break;
              }

              $free_shipping = false;
              if (($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
              $free_shipping = true;
              }
              } else {
              $free_shipping = false;
              }
              // get all available shipping quotes
              $quotes = $shipping_modules->quote('','',['shop_order']);
              }

              $quotes_radio_buttons = 0;
              if ($free_shipping) {
              $quotes = array(
              array(
              'id' => 'free',
              'module' => FREE_SHIPPING_TITLE,
              'methods' => array(
              array(
              'id' => 'free',
              'selected' => true,
              'title' => sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)),
              'code' => 'free_free',
              'cost_f' => '&nbsp;',
              'cost' => 0,
              ),
              ),
              ),
              );
              } else {
              $useChapest = false;
              if (!isset($_POST['shipping'])) {
              $useChapest = true;
              }
              $i_chapest = 0;
              $j_chapest = 0;
              $cost_chapest = 999999;
              for ($i = 0, $n = sizeof($quotes); $i < $n; $i++) {
              if (!isset($quotes[$i]['error'])) {
              for ($j = 0, $n2 = sizeof($quotes[$i]['methods']); $j < $n2; $j++) {
              if ($useChapest && $quotes[$i]['methods'][$j]['cost'] < $cost_chapest) {
              $cost_chapest = $quotes[$i]['methods'][$j]['cost'];
              $i_chapest = $i;
              $j_chapest = $j;
              }
              $quotes[$i]['methods'][$j]['cost_f'] = $currencies->format(\common\helpers\Tax::add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)));
              $quotes[$i]['methods'][$j]['code'] = $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'];
              $quotes[$i]['methods'][$j]['selected'] = (isset($_POST['shipping']) && $_POST['shipping'] == $quotes[$i]['methods'][$j]['code']);
              $quotes[$i]['selected'] = (isset($_POST['shipping']) && $_POST['shipping'] == $quotes[$i]['methods'][$j]['code']);
              $quotes_radio_buttons++;
              }
              }
              }
              if ($useChapest) {
              $quotes[$i_chapest]['methods'][$j_chapest]['selected'] = true;
              }
              }
              $keep_shipping = $shipping;
              foreach ($quotes as $quote_info) {
              if (!is_array($quote_info['methods']))
              continue;
              foreach ($quote_info['methods'] as $quote_method) {
              if ($quote_method['selected']) {
              $shipping = array(
              'id' => $quote_method['code'],
              'title' => $quote_info['module'] . (empty($quote_method['title']) ? '' : ' (' . $quote_method['title'] . ')'),
              'cost' => $quote_method['cost'],
              'cost_inc_tax' => \common\helpers\Tax::add_tax_always($quote_method['cost'], (isset($quote_info['tax']) ? $quote_info['tax'] : 0))
              );
              $order->change_shipping($shipping);
              $select_shipping = $quote_method['code'];
              tep_session_register('select_shipping');
              }
              }
              } */

            //\shipping error
            /*
              $payment_modules = new payment(); // $payment_modules - for selected country (was update_status)

              $payment_modules->update_status(); //???
              $selection = $payment_modules->selection(false,false,['shop_order']);
              $jspayments = array();
              if (is_array($selection))
              foreach ($selection as $p_sel) {
              if (isset($p_sel['methods']) && is_array($p_sel['methods'])) {
              foreach ($p_sel['methods'] as $p_sel_method) {
              $jspayments[] = $p_sel_method['id'];
              }
              }
              $jspayments[] = $p_sel['id'];
              }
              if (count($jspayments) == 0)
              $jspayments[] = 'none';
             */
//ICW ADDED FOR CREDIT CLASS SYSTEM
            //global $opc_coupon_pool;
            //$opc_coupon_pool = array();
            //$this->manager->totalCollectPosts($_POST);
            /* $order_total_modules = new \common\classes\order_total(array(
              'ONE_PAGE_CHECKOUT' => 'True',
              'ONE_PAGE_SHOW_TOTALS' => 'true',
              ));

              $order_total_modules->collect_posts();
             */
            //$this->manager->totalPreConfirmationCheck();
            //$order_total_modules->pre_confirmation_check();

            /*
              $order_total_output = $order_total_modules->process();

              $result = [];
              foreach ($order_total_output as $total) {
              if (class_exists($total['code'])) {
              if (method_exists($GLOBALS[$total['code']], 'visibility')) {
              if (true == $GLOBALS[$total['code']]->visibility(PLATFORM_ID, 'TEXT_CHECKOUT')) {
              if (method_exists($GLOBALS[$total['code']], 'visibility')) {
              $result[] = $GLOBALS[$total['code']]->displayText(PLATFORM_ID, 'TEXT_CHECKOUT', $total);
              } else {
              $result[] = $total;
              }
              }
              }
              }
              }
             */

            //$opc_coupon_pool['message'];
            //$opc_coupon_pool['error'];
            //$shipping = $keep_shipping;
            ///update customer's addressbook
            /*
              $sql_array = array(
              'entry_country_id' =>  $opc_sendto['country_id'],
              'entry_city' =>  $opc_sendto['city'],
              'entry_state' =>  $opc_sendto['state'],
              'entry_postcode' =>  $opc_sendto['postcode'],
              'entry_zone_id' =>  $opc_sendto['zone_id'],
              'entry_state' =>  $opc_sendto['state'],
              );

              if ((int)$sendto > 0) {
              tep_db_perform(TABLE_ADDRESS_BOOK, $sql_array, 'update', " customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
              } else {
              $sql_array['customers_id'] = $customer_id;
              tep_db_perform(TABLE_ADDRESS_BOOK, $sql_array);
              $sendto  = tep_db_insert_id();
              } */
        }
    }

    public function actionRestart($order_id) {
        global $navigation, $cart;
        if (!$order_id)
            $this->goHome();

        if (Yii::$app->user->isGuest) {
            $navigation->set_snapshot();
            return $this->redirect(tep_href_link('account/login', '', 'SSL'));
        }

        $orderModel = \common\models\Orders::find()->select(['stock_updated', 'customers_id'])
                        ->where(['orders_id' => (int) $order_id])->one();

        if (!$orderModel || $orderModel->customers_id != Yii::$app->user->getId()) {
            $this->goHome();
        }

        if ($orderModel->stock_updated && defined('STOCK_LIMITED') && STOCK_LIMITED == 'true') {
            \common\helpers\Order::restock($order_id);
        }

        $cart = new \common\classes\shopping_cart($order_id);
        if ($multiCart = \common\helpers\Extensions::isAllowed('MultiCart')) {
            $key = $multiCart::getCurrentCartKey();
            if ($key){
                $cart->setBasketID($key);
            }
        }

        if ($cart->count_contents() > 0) {
            return $this->redirect(tep_href_link('checkout/index', '', 'SSL'));
        } else {
            return $this->redirect(tep_href_link('shopping-cart/index', '', 'SSL'));
        }
    }

}
