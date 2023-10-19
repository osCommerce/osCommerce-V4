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

//use backend\models\EP\DataSources;
use backend\components\LocationSearchTrait;
use common\classes\platform_config;
use common\classes\platform;
use common\components\Customer;
use common\helpers\Acl;
use common\helpers\Output;
use backend\models\AdminCarts;
use common\helpers\Status;
use common\helpers\Coupon;
use common\helpers\Order as OrderHelper;
use common\models\AddressBook;
use common\models\Orders;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Yii;
use backend\design\editor\Formatter;
use backend\models\EP\Messages;

/**
 * default controller to handle user requests.
 */
class EditorController extends Sceleton {

    public $acl = ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS'];

    /**
     * Index action is the default action in a controller.
     */
    /** @prop \common\services\OrderManager $manager */
    public $manager;
    /** @prop \common\classes\Currencies $currencies */
    public $currencies;
    /** @prop \backend\models\AdminCarts $admin */
    public $admin;
    protected $storage;

    use LocationSearchTrait;

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
        \common\helpers\Translation::init('admin/orders/create');
        \common\helpers\Translation::init('admin/orders/order-edit');

        $this->storage = Yii::$app->get('storage');
        $this->manager = new \common\services\OrderManager($this->storage);
        $this->manager->setModulesVisibility(['admin']);
        $this->manager->combineShippings = true;
        $this->currencies = Yii::$container->get('currencies');

        $this->admin = new AdminCarts();

        parent::__construct($id, $module);

        $this->pageSettings();

        $this->manager->setRenderPath('\\backend\\design\\editor\\');
    }

    protected function checkOrderOwner($cart) {
        if (!$this->admin->checkCartOwnerClear($cart)){
            header('HTTP/1.0 406 Not Acceptable');
            die();
        }
    }

    protected function addLog($comment){
        global $login_id;
        $log = $this->storage->has('log') ? $this->storage->get('log') : [];
        $log = is_array($log) ? $log : [] ;
        $log[] = [
            'comment' => $comment,
            'admin_id' => $login_id,
        ];
        $this->storage->set('log', $log);
    }

    protected function saveLog(){
        $order = $this->manager->getOrderInstance();
        if ($order && $order->order_id){
            $log = $this->storage->has('log') ? $this->storage->get('log') : [];
            foreach($log as $row){
                $order->addLegend($row['comment'], $row['admin_id']);
            }
            $this->storage->remove('log');
        }
    }

    protected function getPIName($uprid){
        if (\common\helpers\Inventory::isInventory($uprid)){
            $name = \common\helpers\Inventory::get_inventory_name_by_uprid($uprid);
        } else {
            $name = \common\helpers\Product::get_products_name($uprid);
        }
        return $name;
    }

    public function pageSettings() {
        $this->topButtons[] = '';
        $this->view->headingTitle = HEADING_TITLE;
        if (isset($_GET['new'])) {
            $this->view->newOrder = true;
        } else {
            $this->view->newOrder = false;
        }
        $this->view->backOptionTrue = \Yii::$app->request->get('back');
        if (isset($_GET['back'])) {
            $this->view->backOption = $_GET['back'];
        } else {
            $this->view->backOption = 'orders';
        }
        $this->selectedMenu = array('customers', 'orders');
    }

    public function obtainCustomerCart($cartInsatnceType, \yii\db\ActiveRecord $order = null, $currentCart = '', $customers_id = null) {
        if (tep_not_null($currentCart)) {
            $cart = $this->admin->getCartById($currentCart);
            if (!$cart) {
                if ($details = \common\helpers\Cart::decodeId($currentCart)) {
                    $customers_id = ($details['customers_id'] != 0)? $details['customers_id'] : $customers_id;
                    $cart = $this->admin->createCart($cartInsatnceType, $order, $details['basket_id'], $customers_id);
                }
            } else {
                $this->admin->setCurrentCartID($currentCart, (($order->orders_id ?? null) ? false : true));
            }
        } else {
            $cart = $this->admin->createCart($cartInsatnceType, $order, 0, $customers_id);
        }
        return $cart;
    }

    //!!use only after current cart id is defined
    public function loadCheckoutDetails($order_id = null) {
        //we have to know witch source have to be used as main
        //let use "checkout" as point to start
        //if checkout is not defined try to upload data from admin carts or order instance
        //remove "checkout" during cancelling data
        if (!$this->manager->has('checkout')) {
            $loadded = false;
            if ($this->admin->getCurrentCartID()) {
                if ($this->admin->hasCheckoutDetails()) {
                  foreach ($this->admin->getCheckoutDetails() as $name => $value) {
                        if ($name == 'customer_id') {
                            $cidExist = true;
                            $cart = $this->manager->get('cart');
                            if ($cart) {
                                $cart->setCustomer($value);
                            }
                        }
                        $this->manager->set($name, $value);
                    }
                    if ($this->manager->has('customer')){
                        $_customer = $this->manager->get('customer');
                        if ($_customer instanceof \common\components\Customer && $_customer->get('fromOrder')){
                            $this->manager->remove('customer_id');
                            $cart = $this->manager->get('cart');
                            if ($cart) {
                                $cart->setCustomer(0);
                            }
                        }
                    }
                    $loadded = true;
                }
            }

            if (!$loadded) {
                if (!is_null($order_id)) {
                    $this->manager->predefineOrderDetails();
                }
            }
            $this->manager->set('checkout', 1);
        }
    }

    public function loadPlatformConsts() {
        //ask platform/language/currency if not defined!!!!
        if (!$this->manager->has('platform_id')) {
            $this->manager->set('platform_id', \common\classes\platform::defaultId());
        }
        $__platform = Yii::$app->get('platform');
        $platform_id = $this->manager->getPlatformId();
        $platform_config = $__platform->config($platform_id);
        if ($platform_config->isVirtual() || $platform_config->isMarketPlace()) {
            $_detected = false;
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('AdditionalPlatforms', 'allowed')) {
                if ($_plid = $ext::getVirtualSattelitId($platform_id)) {
                    $platform_config = $__platform->config($_plid);
                    $_detected = true;
                }
            }
            if (!$_detected)
                $platform_config = $__platform->config(\common\classes\platform::defaultId());
        }
        $platform_config->constant_up();
        defined('PLATFORM_ID') or define('PLATFORM_ID', $platform_id);

        $theme_id = \common\models\PlatformsToThemes::findOne(['platform_id' => $platform_id])->theme_id;
        $theme_name = \common\models\Themes::findOne(['id' => $theme_id])->theme_name;

        defined('THEME_NAME') or define('THEME_NAME', $theme_name);
    }

    public function actionCartWorker() {//ajax action
        $currencies = \Yii::$container->get('currencies');

        $data = Yii::$app->request->post();

        $this->storage->setPointer($data['currentCart']); //!!importnat to get current data
        /** @var \common\classes\shopping_cart $cart */
        $cart = $this->manager->get('cart'); //working_cart
        $this->checkOrderOwner($cart);
        $response = [];
        if ($cart) {
            $this->loadPlatformConsts();
            $this->manager->loadCart($cart);
            $this->manager->createOrderInstance($this->manager->get('order_instance'));
            $this->manager->defineOrderTaxAddress();
            $cart->clearTotalKey('ot_tax');
            $cart->clearTotalKey('ot_gift_wrap');
            if (is_array($data['uprid'] ?? null)) {
                $uprid = $data['uprid'];
                foreach ($uprid as &$item) {
                    $item = urldecode($item);
                }
            } else {
                $uprid = urldecode($data['uprid'] ?? null);
            }
            if (isset($data['action'])) {
                switch ($data['action']) {
                    case 'change_qty':
                        $attributes = [];
                        $_uprid = \common\helpers\Inventory::normalize_id($uprid, $attributes); //uprid will be modified for bundles
                        if (is_array($data['qty_'] ?? null)) {
                            $cart->clearOverwritenKey($uprid, 'final_price_formula');
                            $cart->clearOverwritenKey($uprid, 'final_price_formula_data');
                            $cart->clearOverwritenKey($uprid, 'final_price');
                            $packQty = [
                                'unit' => (int) $data['qty_'][0],
                                'pack_unit' => (int) $data['qty_'][1],
                                'packaging' => (int) $data['qty_'][2],
                            ];
                            if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                                $packQty['qty'] = $ext::recalcQauntity(\common\helpers\Inventory::get_prid($_uprid), $packQty);
                            }
                        } else {
                            $packQty = $data['qty'];
                        }
                        if (strpos($uprid, '{tpl}') !== false) {
                            $cart->add_cart_cfg($uprid, $data['qty'], $attributes);
                        } else {
                            $cart->add_cart(\common\helpers\Inventory::get_prid($_uprid), $packQty, $attributes, false, 0, ($data['gift_wrap'] ?? null) == 'true');
                        }
                        $this->addLog($this->getPIName($_uprid) . ' changed qty to ' . (is_scalar($packQty)? $packQty : $packQty['qty']));
                        break;
                    case 'remove_product':
                        if (!is_array($uprid)) $uprid = [$uprid];
                        foreach ($uprid as $pid) {
                            $cart->remove($pid);
                            $this->addLog($this->getPIName($pid) . ' removed ');
                        }
                        break;
                    case 'remove_giveaway':
                        $cart->remove_giveaway($uprid);
                        $this->addLog($this->getPIName($uprid) . ' removed as giveaway');
                        break;
                    case 'change_tax':
                        if (!is_null($uprid)) {
                            $insulator = new \backend\services\ProductInsulatorService($uprid, $this->manager);
                            $insulator->setData($data);
                            $insulator->setProductTax($uprid);
                            $products = $cart->get_products($uprid);
                            $product = array_shift($products);
                            $this->addLog($this->getPIName($uprid) . ' tax changed to '. $cart->getOwerwrittenKey($uprid, 'tax_rate'));
                        }
                        break;
                    case 'extra_charge':
                    case 'change_price':
                        if (!is_null($uprid)) {
                            $insulator = new \backend\services\ProductInsulatorService($uprid, $this->manager);
                            $insulator->setData($data);
                            if ($data['action'] == 'change_price'){
                                $insulator->manualPriceChanged = true;
                            }
                            $insulator->setExtraCharge();
                            $products = $cart->get_products($uprid);
                            $product = array_shift($products);
                            $this->addLog($product['name'] . ' manual price changed to '. $product['final_price']);
                        }
                        break;
                    case 'reset_cart':
                        $this->manager->remove('cart'); //working_cart
                        return json_encode(['ok' => true]);
                        break;
                    case 'save_cart':
                        if ($this->admin->saveCustomerBasket($cart)) {
                            echo json_encode(['message' => 'Cart Saved', 'type' => 'success']);
                        } else {
                            echo json_encode(['message' => 'Cart Not Saved', 'type' => 'wanring']);
                        }
                        exit();
                        break;
                    case 'delete_cart':
                        if (isset($data['deleteCart'])){
                            $index = $data['currentCart'];
                            $this->admin->removeCart($data['deleteCart']);
                            if ($data['deleteCart'] == $index){ //not current cart
                                $goto = $this->getRedirect($cart, true);
                                $this->manager->clearStorage();
                                echo json_encode(['goto' => Yii::$app->urlManager->createUrl([$goto, 'orders_id' => Yii::$app->request->get('orders_id')])]);
                            } else {
                                $this->storage->setPointer($data['deleteCart']);
                                $this->manager->clearStorage();
                                echo json_encode(['reload' => 1]);
                            }
                        }
                        exit();
                        break;
                    case 'save_settings':
                        $this->manager->set('platform_id', Yii::$app->request->post('platform_id'));
                        $this->manager->set('currency', Yii::$app->request->post('currency'));
                        $this->manager->set('languages_id', Yii::$app->request->post('language_id'));
                        $this->manager->remove('shipping');
                        $cart->clearTotals();
                        $this->manager->set('cart', $cart);
                        echo json_encode(['reload' => true]);
                        exit();
                        break;
                }
            }
            $this->manager->set('cart', $cart); //working_cart

            $this->manager->checkoutOrderWithAddresses();
            $this->manager->updateShippingCost();

            $response['products_listing'] = $this->manager->render('ProductsListing', ['manager' => $this->manager]);

            $response['order_totals'] = $this->manager->render('OrderTotals', ['manager' => $this->manager]);
            $response['order_shipping'] = $this->manager->render('Shipping', ['manager' => $this->manager]);
            $response['order_payment'] = $this->manager->render('Payment', ['manager' => $this->manager]);
            $response['order_state'] = $this->getOrderStateArray();
        }

        echo json_encode($response);
        exit();
    }

    /* processing with products in basket */

    public function actionShowBasket() {
        $_get = Yii::$app->request->get();

        $this->storage->setPointer($_get['currentCart']); //!!importnat to set pointer before using stored data

        $this->loadPlatformConsts();

        $cart = $this->manager->get('cart'); //working_cart
        $this->checkOrderOwner($cart);
        $this->manager->loadCart($cart);
        $this->manager->createOrderInstance($this->manager->get('order_instance'));
        $this->manager->defineOrderTaxAddress();
        if (Yii::$app->request->isPost) {
            switch (Yii::$app->request->post('action')) {
                case 'load_categories':
                    if (\Yii::$app->request->post('products_id')) {
                        $response = [];
                        $productsId=\Yii::$app->request->post('products_id');
                        $product_categories = \common\helpers\Categories::generate_category_path($productsId, 'product');
                        $product_categories_string = '';
                        for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                           $category_path = '';
                           for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                              $category_path .= '<span class="category_path__location">' . $product_categories[$i][$j]['text'] . '</span>&nbsp;&gt;&nbsp;';
                           }
                           $category_path = substr($category_path, 0, -16);
                           $product_categories_string .= '<li class="category_path">' . $category_path . '</li>';
                        }
                        $product_categories_string = ($product_categories_string !='' ? '<ul class="category_path_list">' . $product_categories_string . '</ul>' :"");
                        $response['categories']=$product_categories_string;
                        echo json_encode($response);
                    }
                    exit();
                break;
                case 'load_product':
                    if (Yii::$app->request->post('products_id')) {
                        $response = [];
                        $_id = (int) Yii::$app->request->post('products_id');
                        $response['products_id'] = $_id;
                        $insulator = new \backend\services\ProductInsulatorService(Yii::$app->request->post('products_id'), $this->manager);
                        $productDetails = $insulator->getProductMainDetails();
                        $product = $insulator->getProduct();
                        $response['isComplex'] = \common\helpers\Attributes::has_product_attributes($_id) || $product->is_bundle || $product->products_pctemplates_id;
                        $response['product'] = $productDetails;
                        $attr = $insulator->getProductDetails();
                        if ($attr['attributes_box']['data']['attributes_array'] ?? false) {
                            $response['attributes_array'] = $attr['attributes_box']['data']['attributes_array'];
                        }
                        $response['content'] = $this->manager->render('Product', ['product' => $productDetails, 'manager' => $this->manager]);
                        echo json_encode($response);
                    }
                    exit();
                    break;
                case 'get_details_ex':
                    $post = Yii::$app->request->post('product_info');
                    $response = 'wrong product id';
                    if (is_array($post)) {
                        $response = [];
                        foreach ($post as $key => $data) {
                            if (isset($data['products_id'])) {
                                $pid = $data['products_id'];
                                if (is_array($pid)) { // sometime sh
                                    $pid = reset($pid);
                                    $data['products_id'] = $pid;
                                }
                                $insulator = new \backend\services\ProductInsulatorService($pid, $this->manager);
                                $insulator->setData($data);
                                $result = $insulator->getProductDetails();
                                $overwritten = $insulator->getOverwritten();
                                $result['product_info']['overwritten'] = $overwritten;
                                $currentPrice = round((float)$result['product_info']['product_unit_price'], 2);
                                $originPrice = $overwritten['origin_price'] ?? ($mainDetails['price'] ?? $currentPrice);

                                $result['product_info']['origin_price'] = $originPrice;
                                $result['product_info']['final_price'] = $overwritten['final_price_formula_data'][1]['vars']['init_value'] ?? $originPrice; // i.e. final_init_value_price
                                $result['product_info']['current_product_price'] = $currentPrice;
                                $response[] = $result;
                            }
                        }
                    }
                    echo json_encode(array_pop($response));
                    exit();
                    break;
                case 'get_details':
                    $post = Yii::$app->request->post('product_info');
                    if (is_array($post)) {
                        $response = [];
                        foreach ($post as $key => $data) {
                            if (isset($data['products_id'])) {
                                $insulator = new \backend\services\ProductInsulatorService($data['products_id'], $this->manager);
                                $insulator->setData($data);
                                if (Yii::$app->request->post('edit')) {
                                    $insulator->edit = true;
                                }
                                $productDetail = $insulator->getProductDetails();
                                if (\common\helpers\Product::getVirtualItemQuantityValue($data['products_id']) > 1) {
                                    $productDetail['product_info']['virtual_item_qty'] = \common\helpers\Product::getVirtualItemQuantityValue($data['products_id']);
                                    $productDetail['product_info']['virtual_item_step'] = \common\helpers\Product::getVirtualItemStep($data['products_id']);
                                }
                                $response[] = $productDetail;
                            }
                        }
                    }
                    echo json_encode(array_pop($response));

                    exit();
                    break;
                case 'add_products':
                    $post = Yii::$app->request->post();
                    //if add gift wrap, clear gift wrap totals
                    $added = true;
                    $errProducts = '';
                    if (is_array($post['product_info'])) {
                        foreach ($post['product_info'] as $product_data) {
                            $insulator = new \backend\services\ProductInsulatorService($product_data['products_id'], $this->manager);
                            $insulator->edit = Yii::$app->request->get('action') == 'edit_product';
                            $product_data['pconfig_dont_inc_qty'] = true;
                            $insulator->setData($product_data);
                            $insulator->setExtraCharge();
                            $justAdded = $insulator->addProduct($post['replace_same_product'] ?? true);
                            $added = $justAdded && $added;
                            if (!$justAdded) {
                                $productName = !empty($product_data['name']) ? $product_data['name'] : ('Product with empty name and id=' . $product_data['products_id']);
                                $errProducts .= $productName . '<br>';
                            }
                        }
                    }

                    $this->manager->set('cart', $this->manager->getCart()); //working_cart
                    $this->admin->saveCustomerBasket($this->manager->getCart());
                    if ($added) {
                        echo json_encode(['status' => 'ok']);
                    } else {
                        echo json_encode(['status' => 'bad', 'message' => 'Not All products were been added:<br>' . $errProducts]);
                    }
                    exit();
                    break;
                case 'add_giveaway':
                    $post = Yii::$app->request->post();
                    $added = false;
                    if (isset($post['giveaways'])) {
                        foreach ($post['giveaways'] as $gaw_id => $giveaways) {
                            $insulator = new \backend\services\ProductInsulatorService($giveaways['products_id'], $this->manager);
                            if (isset($post['giveaway_switch'][$gaw_id])) {
                                $giveaways['giveaway_switch'] = $gaw_id;
                            }
                            $insulator->setData($giveaways);
                            $added = $added || $insulator->addGiveAway($gaw_id);
                        }
                    }

                    $this->manager->set('cart', $this->manager->getCart()); //working_cart
                    $this->admin->saveCustomerBasket($this->manager->getCart());
                    if ($added) {
                        echo json_encode(['status' => 'ok']);
                    } else {
                        echo json_encode(['status' => 'bad', 'message' => 'Not All products were been added']);
                    }
                    break;
            }
        } else {
            if (Yii::$app->request->get('action') == 'show_giveaways') {
                return $this->manager->render('GiveAway', ['manager' => $this->manager]);
            } else if (Yii::$app->request->get('action') == 'edit_product') {
                $uprid = Yii::$app->request->get('uprid');
                return $this->manager->render('EditProduct', ['manager' => $this->manager, 'uprid' => $uprid]);
            } else {
                $this->manager->checkoutOrderWithAddresses();
                $this->manager->totalPreConfirmationCheck();
                $this->manager->updateShippingCost();
                return $this->manager->render('ProductsBox', ['cart' => $cart, 'manager' => $this->manager, 'post' => \Yii::$app->request->get()]);
            }
        }
    }

    public function getRedirect($cart, $toProcess = false){
        $goto = '';
        if ($cart->table_prefix == 'sample_') {
            $goto = 'samples/';
            if ($toProcess && $cart->order_id) {
                $goto .= 'process-samples';
            }
        } else if ($cart->table_prefix == 'quote_') {
            $goto = 'quotation/';
            if ($toProcess && $cart->order_id) {
                $goto .= 'process-quotation';
            }
        } else {
            $goto = 'orders/';
            if ($toProcess && $cart->order_id) {
                $goto .= 'process-order';
            }
        }
        return $goto;
    }

    /* is used for customer/shipping/paymens/order totals preocessing */

    public function actionCheckout() {
        $_get = Yii::$app->request->get();
        //!!importnat to set pointer before using stored data
        $this->storage->setPointer($_get['currentCart']);
        $this->admin->setCurrentCartID($_get['currentCart']);

        /** @var \common\classes\shopping_cart $cart */
        $cart = $this->manager->get('cart'); //working_cart
        $this->checkOrderOwner($cart);

        if ( !$this->manager->has('admin_edit_order') ){
            $this->manager->set('admin_edit_order', 1);
        }

        $data = Yii::$app->request->post();
        $data['type'] = $data['type'] ?? null;
        $response = [];
        if ($cart) {
            $currencies = Yii::$container->get('currencies');
            $this->loadPlatformConsts();
            $this->manager->loadCart($cart);
            $this->manager->createOrderInstance($this->manager->get('order_instance'));
            //$this->manager->defineOrderTaxAddress();
            //VL 20191220 uncomment??
            if (Yii::$app->request->isPost) {
                if (isset($data['action'])) {
                    switch ($data['action']) {
                        case 'recalculate_totals':
                            if (isset($data['update_totals'])) {
                                foreach ($data['update_totals'] as $module => $values) {
                                    if (is_array($values)){
                                        foreach($values as &$value){
                                            $value = floatval($value);
                                            $value *= $currencies->get_market_price_rate($cart->currency, DEFAULT_CURRENCY);
                                        }
                                    }
                                    $cart->setTotalKey($module, $values);
                                }
                            }
                            break;
                        case 'update_amount':
                            if (isset($data['paid_amount'])) {
                                $value = (float) $data['paid_amount'] * $currencies->get_market_price_rate($cart->currency, DEFAULT_CURRENCY);
                                $_value = ($data['paid_prefix'] == '-' ? -$value: $value);
                                $comment = $currencies->format($_value) . ' ' . $data['comment'] . ' (' . \common\helpers\Date::formatDateTime(new \yii\db\Expression('now')) . '), ' . $this->admin->getInfo('admin_firstname') . ' ' . $this->admin->getInfo('admin_lastname');
                                $cart->setTotalPaid($value, $data['paid_prefix'], $comment);
                                $this->addLog('Paid amount changed to '. $value);
                            }
                            break;
                        case 'reset_totals':
                            $cart->clearTotals(false);
                            $cart->clearHiddenModules();
                            $cart->restoreTotals();
                            $cart->clearTotalKey('ot_shipping');
                            $this->manager->updateShippingCost();
                            break;
                        case 'reset_checkout':
                            $this->manager->remove('estimate_ship');
                            $this->manager->remove('estimate_bill');
                            $this->manager->remove('checkout');
                            return json_encode(['ok' => true]);
                            exit();
                            break;
                        case 'save_checkout':
                        case 'save_order':
                            //validate forms before
                            if ($this->manager->isShippingNeeded()) {
                                //need to do something))
                            }
                            $shipAsBill = $data['ship_as_bill'] ?? null;
                            $valid = $this->manager->validateShipping(\Yii::$app->request->post());

                            $__guest_order_edit = false;
                            if ($this->manager->isCustomerAssigned() || $this->manager->getCustomersIdentity()->get('fromOrder')){
                                if ( isset($data['checkout']['email_address']) && !isset($data['checkout']['firstname']) ) {
                                    if ( is_array($data['Shipping_address']) ) {
                                        $data['checkout']['firstname'] = $data['Shipping_address']['firstname'];
                                        $data['checkout']['lastname'] = $data['Shipping_address']['lastname'];
                                    }elseif( is_array($data['Billing_address']) ){
                                        $data['checkout']['firstname'] = $data['Billing_address']['firstname'];
                                        $data['checkout']['lastname'] = $data['Billing_address']['lastname'];
                                    }
                                }
                            }else{
                                if ( isset($data['checkout']['email_address']) && !isset($data['checkout']['firstname']) ){
                                    if ( is_array($data['Shipping_address']) ) {
                                        $data['checkout']['firstname'] = $data['Shipping_address']['firstname'];
                                        $data['checkout']['lastname'] = $data['Shipping_address']['lastname'];
                                    }elseif( is_array($data['Billing_address']) ){
                                        $data['checkout']['firstname'] = $data['Billing_address']['firstname'];
                                        $data['checkout']['lastname'] = $data['Billing_address']['lastname'];
                                    }
                                    $__guest_order_edit = true;
                                }
                            }
                            $valid = $this->manager->validateContactForm($data, true) && $valid;
                            $valid = $this->manager->validateAddressForms($data, '', $shipAsBill, true) && $valid;
                            if ($ext = \common\helpers\Acl::checkExtensionAllowed('DelayedDespatch', 'allowed')) {
                                $valid = !$ext::prepareDeliveryDate(true, $this->manager) && $valid;
                            }
                            if ($valid) {
                                $customer = $this->manager->getCustomersIdentity();
                                if ($__guest_order_edit || $customer->get('fromOrder')){
                                    $customer->fillCustomerFields($this->manager->getCustomerContactForm(false));
                                    $this->manager->set('customer', $customer);
                                }
                                if ($data['action'] == 'save_order') {

                                    if (\common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory') && Yii::$app->request->get('orders_id')) {
                                        $logger = new \common\extensions\ReportChangesHistory\classes\Logger();
                                        $beforeObject = new \common\api\Classes\Order();
                                        $beforeObject->load(Yii::$app->request->get('orders_id'));
                                        $logger->setBeforeObject($beforeObject);
                                        unset($beforeObject);
                                    }

                                    if (isset($_POST['comments'])){
                                        $this->manager->set('comments', tep_db_prepare_input($_POST['comments']));
                                    }
                                    if (isset($_POST['status'])){
                                        $this->manager->getCart()->setOrderStatus((int)$_POST['status']);
                                    }

                                    $this->manager->getOrderInstance()->withDelivery = true;
                                    $this->manager->setSelectedPaymentModule($this->manager->getPayment());
                                    $this->manager->checkoutOrderWithAddresses();
                                    /** @var \common\classes\extended\OrderAbstract $order */
                                    $order = $this->manager->getOrderInstance();
                                    if ($__guest_order_edit || $customer->get('fromOrder')){
                                        $order->customer = [];
                                        foreach ($customer->getAttributes() as $field => $value){
                                            $order->customer[preg_replace("/customers_/", "", $field)] = $value;
                                        }
                                        $order->customer = array_merge($order->customer, $customer->getAll());
                                    }

                                    $output = $this->manager->getTotalOutput(false);

                                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
                                        $ext::checkStatus($this->manager);
                                    }

                                    // {{ WA: prevent set order status as paid when select payment
                                    if (!isset($_POST['status'])){
                                        $order->info['order_status'] = (int) DEFAULT_ORDERS_STATUS_ID;
                                        if ( false && Yii::$app->request->get('orders_id') ){
                                            $_order_status = $order->getArModel()->where(['orders_id' => Yii::$app->request->get('orders_id')])->select('orders_status')->scalar();
                                            if ( $_order_status ){
                                                $order->info['order_status'] = $_order_status;
                                            }
                                        }
                                    }
                                    // }} WA: prevent set order status as paid when select payment

                                    if (empty($order->info['order_status']) || !$order->info['order_status']) {
                                        $order->info['order_status'] = (int) DEFAULT_ORDERS_STATUS_ID;
                                    }

                                    if (\Yii::$app->request->post('purchase_order', false) !== false){
                                      $order->info['purchase_order'] = tep_db_prepare_input(\Yii::$app->request->post('purchase_order'));
                                    }

                                    if ($order->maintainSplittering()){
                                        $this->manager->getOrderSplitter()->makeSplinters(Yii::$app->request->get('orders_id'));
                                    }

                                    $oldModel = null;
                                    if (Yii::$app->request->get('orders_id')) {
                                        $oldModel = $order->getArModel()->where(['orders_id' => Yii::$app->request->get('orders_id')])->one();
                                        $order->save_order(Yii::$app->request->get('orders_id'));
                                    } else {
                                        $order->save_order();
                                    }

                                    if ($order->maintainSplittering()){
                                        $this->manager->getOrderSplitter()->updateSplinterOrderId($order->order_id);
                                    }

                                    $cart->order_id = $order->order_id;

                                    /*if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
                                        $ext::checkRefund($this->manager, $data['difference']);
                                    }*/

                                    $_notify = false;// email on each save order request :( It's better to send it separately 
                                    $order->save_details($_notify);

                                    $order->save_products($_notify);// email on each save order request :( It's better to send it separately $data['type'] != 'send_request');
                                    /** @var \common\extensions\UpdateAndPay\UpdateAndPay $ext */
                                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
                                        $ext::saveOrder($this->manager, $data['type'], $data['difference'] ?? null);
                                    }
                                    if($oldModel && $this->manager->isCustomerAssigned()){
                                        \common\helpers\Customer::updateBasketId($this->manager->getCustomerAssigned(), $oldModel->basket_id ?? null, $cart->basket_id ?? null);
                                    }

                                    $this->saveLog();

                                    if (\common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory') && isset($logger) && Yii::$app->request->get('orders_id')) {
                                        $afterObject = new \common\api\Classes\Order();
                                        $afterObject->load(Yii::$app->request->get('orders_id'));
                                        $logger->setAfterObject($afterObject);
                                        unset($afterObject);
                                        $logger->run();
                                    }

                                    $response['message'] = 'Order saved';
                                    $response['prompt'] = true;
                                    $response['order_id'] = $order->order_id;
                                }
                                if ($this->admin->saveCheckoutDetails($cart, $this->storage)) {
                                    $response['message'] = 'All changes saved';
                                    $response['type'] = 'success';
                                    $newCartId = $this->admin->getCurrentCartID();
                                    if ($_get['currentCart'] != $newCartId) {
                                        $params = $this->storage->getAll();
                                        $this->storage->removeAll();
                                        $this->storage->setPointer($newCartId);
                                        foreach ($params as $key => $value) {
                                            $this->manager->set($key, $value);
                                        }
                                    }
                                    $this->manager->set('cart', $cart);
                                    $params = Yii::$app->request->getReferrer();
                                    $params = parse_url($params);
                                    $qParams = Yii::$app->request->getQueryParams();
                                    $qParams['currentCart'] = $newCartId;
                                    if ($cart->order_id) {
                                        $qParams['orders_id'] = $cart->order_id;
                                    }
                                    $response['urlCheckout'] = Yii::$app->urlManager->createAbsoluteUrl(array_merge(['editor/checkout'], $qParams)) . '#tab_contact';
                                    $response['redirect'] = Yii::$app->urlManager->createUrl(array_merge(['editor/' . basename($params['path'])], $qParams)) . '#tab_contact';
                                    /*} else {
                                        $response['reload'] = true;
                                    }*/
                                    $this->layout = false;
                                    if ( isset($data['get_fresh_aup']) && $data['get_fresh_aup'] ){
                                        $cInfo = \common\models\Customers::find()->where(['customers_id' => $order->customer['id']])->one();
                                        $aup = \common\helpers\Password::encryptAuthUserParam($order->customer['id'], $order->customer['email_address'], 'payment', ($cInfo->auth_key ?? ''));
                                        $response['frontend_aup_raw'] = $order->customer['id']."\t".$order->customer['email_address'];
                                        $response['frontend_aup'] = $aup;
                                    }
                                } else {
                                    $response = ['message' => 'Changes Not Saved', 'type' => 'wanring'];
                                }



                            } else {
                                $messageStack = \Yii::$container->get('message_stack');
                                if ($messageStack->size('one_page_checkout') > 0) {
                                    $message = $messageStack->output('one_page_checkout');
                                }
                                $response = ['message' => $message, 'type' => 'wanring'];
                            }
                            echo json_encode($response);
                            exit();
                            break;
                        case 'remove_cart':
                            $index = $this->admin->getCurrentCartID();
                            $goto = $this->getRedirect($cart, true);
                            $order_id = $cart->order_id;

                            $this->admin->removeCart($index);
                            $this->manager->clearStorage();

                            echo json_encode(['redirect' => Yii::$app->urlManager->createUrl([$goto, 'orders_id' => $order_id])]);
                            exit();
                            break;
                        case 'delete_order':
                            $orders_id = Yii::$app->request->get('orders_id');
                            $goto = $this->getRedirect($cart);
                            if ($orders_id){
                                $order = $this->manager->getOrderInstance();
                                $order->order_id = $orders_id;
                                $order->removeOrder($data['restock'] == 'true'? true : false);
                                $index = $this->admin->getCurrentCartID();
                                $this->admin->removeCart($index);
                                $this->manager->clearStorage();
                            }
                            echo json_encode(['redirect' => Yii::$app->urlManager->createUrl([$goto])]);
                            exit();
                            break;
                        case 'search_customer':
                            $cRep = new \common\models\repositories\CustomersRepository();
                            $customers = [];
                            foreach ($cRep->search($data['search'])->limit(200)->all() as $customer) {
                                $customers[] = ['id' => $customer->customers_id, 'text' => \common\helpers\Output::output_string_protected($customer->customers_firstname . ' ' . $customer->customers_lastname . ' (' . $customer->customers_email_address . ')')];
                            }
                            if (empty($customers)){
                                $customers[] = ['text' => TEXT_NOTHING_FOUND];
                            }
                            echo json_encode($customers);
                            exit();
                            break;
                        case 'reassign_customer':
                            $customrs_id = $data['customers_id'];
                            $cRep = new \common\models\repositories\CustomersRepository();
                            $customer = $cRep->getById($customrs_id);
                            if ($customer) {
                                $old = null;
                                if ($cart->customer_id){
                                    $old = $cRep->getById($cart->customer_id);
                                }
                                $this->manager->remove('estimate_ship');
                                $this->manager->remove('estimate_bill');
                                $this->manager->remove('cot_gv');
                                $cart->restoreTotals();
                                $cart->clearTotalKey('ot_coupon');
                                $cart->clearTotalKey('ot_gv');
                                if (!$this->manager->isCustomerAssigned() || ($this->manager->isCustomerAssigned() && $customer->customers_id != $this->manager->getCustomerAssigned())) {
                                    $this->manager->predefineCustomerDetails($customer->customers_id, true);
                                    $cart->setCustomer($customer->customers_id);
                                    if ($this->manager->getCustomersIdentity()->get('fromOrder')){
                                        $this->manager->remove('customer');
                                    }
                                    $this->addLog('New customer assigned '. $customer->customers_firstname. ' '.$customer->customers_lastname . ' (id:'.$customer->customers_id.') from ' . ($old?$old->customers_firstname. ' '.$old->customers_lastname . ' (id:'.$old->customers_id.')' :''));
                                }
                                $this->manager->set('cart', $cart);
                                $this->admin->saveCheckoutDetails($cart, $this->storage);
                            }
                            $params = Yii::$app->request->getReferrer();
                            $params = parse_url($params);
                            $qParams = Yii::$app->request->getQueryParams();
                            $qParams['currentCart'] = $this->admin->getCurrentCartID();
                            $url = Yii::$app->urlManager->createAbsoluteUrl(array_merge(['editor/' . basename($params['path'])], $qParams)) . '#tab_contact';
                            return json_encode(['ok' => true, 'url' => $url]);
                            exit();
                            break;
                        case 'change_address_list':
                            $type = $data['type'];
                            $value = $data['value'];
                            if ($value) {
                                $this->manager->changeCustomerAddressSelection($type, $value);
                                /* if($type == 'shipping'){
                                  $this->manager->set('shipping', false);
                                  } */
                            }
                            $this->manager->getShippingQuotesByChoice();
                            $response['shipping_address'] = $this->manager->render('ShippingAddress', ['manager' => $this->manager]);
                            $response['billing_address'] = $this->manager->render('BillingAddress', ['manager' => $this->manager]);
                            break;
                        case 'save_address':

                            $post = Yii::$app->request->post();
                            $customer = $this->manager->getCustomersIdentity();
                            $customer->get('fromOrder');
                            if ($post['type'] == 'shipping') {
                                $form = $this->manager->getShippingForm();
                            } else {
                                $form = $this->manager->getBillingForm();
                            }
                            $form->load(Yii::$app->request->post());
                            $this->manager->set('customer_id', $cart->customer_id);
                            $attributes = $customer->getAddressFromModel($form);
                            $attributes['customers_id'] = $cart->customer_id;
                            $aBook = $customer->updateAddress($form->address_book_id, $attributes);

                            $this->manager->changeCustomerAddressSelection($post['type'], $aBook->address_book_id);
                            $response['shipping_address'] = $this->manager->render('ShippingAddress', ['manager' => $this->manager]);
                            $response['billing_address'] = $this->manager->render('BillingAddress', ['manager' => $this->manager]);

                            break;
                        case 'set_bill_as_ship':
                            $_sendto = $this->manager->get('sendto');
                            if ($_sendto) {
                                $this->manager->changeCustomerAddressSelection('billing', $_sendto);
                            }
                            $response['billing_address'] = $this->manager->render('BillingAddress', ['manager' => $this->manager]);
                            $response['payments'] = $this->manager->render('Payment', ['manager' => $this->manager], 'json');
                            break;
                        case 'check_vat':
                            $modelName = $data['checked_model'];
                            if ($modelName == 'Shipping_address') {
                                $address = $this->manager->getShippingForm();
                            } else {
                                $address = $this->manager->getBillingForm();
                            }
                            $address->preload($data[$modelName]);
                            $company_vat_status = 0;
                            $customer_company_vat_status = '';
                            if ($ext = \common\helpers\Acl::checkExtensionAllowed('VatOnOrder', 'allowed')) {
                                list($company_vat_status, $customer_company_vat_status) = $ext::update_vat_status($address);
                            }
                            $response['vat_status'] = $customer_company_vat_status;
                            $response['field'] = \yii\helpers\Html::getInputId($address, 'company_vat');
                            break;
                        case 'recalculation':
                            $response = [];
                            $sAddress = $this->manager->getShippingForm(null, false);
                            $sAddress->load($data);
                            $preSelectedShipping = false;
                            if ($sAddress->notEmpty(true)) {
                                $preSelectedShipping = $this->manager->getSelectedShipping();
                                $this->manager->remove('shipping');
                                $this->manager->set('estimate_ship', ['country_id' => $sAddress->country, 'postcode' => $sAddress->postcode, 'zone' => $sAddress->state, 'company_vat' => $sAddress->company_vat, 'company_vat_date' => $sAddress->company_vat_date, 'company_vat_status' => $sAddress->company_vat_status]);
                                $this->manager->resetDeliveryAddress();
                            }

                            $bAddress = $this->manager->getBillingForm(null, false);
                            $bAddress->load($data);
                            if ($bAddress->notEmpty(true)) {
                                $this->manager->set('estimate_bill', ['country_id' => $bAddress->country, 'postcode' => $bAddress->postcode, 'zone' => $bAddress->state, 'company_vat' => $bAddress->company_vat, 'company_vat_date' => $bAddress->company_vat_date, 'company_vat_status' => $bAddress->company_vat_status]);
                                $this->manager->resetBillingAddress();
                            }

                            if (isset($data['shipping']) && !empty($data['shipping'])) {
                                $this->manager->setSelectedShipping($data['shipping']);
                                $response['__ship1'] = $this->manager->getSelectedShipping();
                                $cart->clearTotalKey('ot_shipping');
                                $this->manager->getShippingQuotesByChoice(true);
                            }else{
                                $collection = $this->manager->getShippingCollection(is_array($preSelectedShipping)?$preSelectedShipping:'');
                                //if ($cheapest = $collection->getFirstQuoteModule('no_shipping')) {
                                //}else{
                                    $cheapest = $collection->cheapest();
                                //}
                                if ( !$this->manager->getShipping() && $cheapest ){
                                    $this->manager->setSelectedShipping($cheapest['id']);
                                    $cart->clearTotalKey('ot_shipping');
                                    $this->manager->getShippingQuotesByChoice(true);
                                }
                            }

                            $_shipping = $this->manager->getShipping();
                            if ($_shipping) {
                                $module = $this->manager->getShippingCollection()->get($_shipping['module']);
                                if (is_object($module) && method_exists($module, 'setAdditionalParams')) {
                                    $module->setAdditionalParams($data);
                                } else {
                                    $this->manager->remove('shippingparam');
                                }
                            }

                            $this->manager->getShippingQuotesByChoice(true);

                            $this->manager->checkoutOrderWithAddresses();
                            if ($sAddress->notEmpty(true)) {
                                $response['shipping'] = $this->manager->render('Shipping', ['manager' => $this->manager]);
                            }
                            $response['payments'] = $this->manager->render('Payment', ['manager' => $this->manager]);
                            $response['order_totals'] = $this->manager->render('OrderTotals', ['manager' => $this->manager]);
                            if ($this->manager->isCustomerAssigned()){
                                $this->manager->remove('estimate_ship');
                                $this->manager->remove('estimate_bill');
                            }
                            echo json_encode($response);
                            exit();
                            break;
                        case 'shipping_changed':
                            $_prev_shipping = $this->manager->getShipping();
                            $shipping = $data['shipping'];
                            if ($shipping) {
                                if ( preg_match('/^collect/', $shipping ) ) {
                                    if ( empty($this->manager->getBillto()) ) {
                                        $this->manager->set('billto', $this->manager->getSendto());
                                    }
                                    $this->manager->set('sendto', false);
                                }elseif(is_array($_prev_shipping) && $_prev_shipping['module']=='collect'){
                                    if ( empty($this->manager->getSendto()) && !empty($this->manager->getBillto()) ) {
                                        $this->manager->set('sendto', $this->manager->getBillto());
                                    }
                                }
                                $this->manager->setSelectedShipping($shipping);
                                $cart->clearTotalKey('ot_shipping');
                            }
                            $response['_isBillAsShip'] = $this->manager->isBillAsShip();
                            $this->manager->checkoutOrder();
                            $_shipping = $this->manager->getShipping();
                            $response['_shipping'] = $_shipping;
                            if ($_shipping) {
                                $module = $this->manager->getShippingCollection()->get($_shipping['module']);
                                if (is_object($module) && method_exists($module, 'setAdditionalParams')) {
                                    $module->setAdditionalParams($data);
                                } else {
                                    $this->manager->remove('shippingparam');
                                }
                                $this->addLog('Shipping changed to ' . $_shipping['module']);
                                $response['_set_to'] = $shipping;

                                $switch_to_collect = (is_array($_prev_shipping) && $_prev_shipping['module']=='collect') && !preg_match('/^collect/',$shipping);
                                $switch_from_collect = (is_array($_prev_shipping) && $_prev_shipping['module']!='collect') && preg_match('/^collect/',$shipping);

                                if ( $switch_to_collect || $switch_from_collect ){
                                    $response['shipping_address'] = $this->manager->render('ShippingAddress', ['manager' => $this->manager]);
                                    $response['billing_address'] = $this->manager->render('BillingAddress', ['manager' => $this->manager]);
                                }
                            }
                            $response['payments'] = $this->manager->render('Payment', ['manager' => $this->manager]);
                            break;
                        case 'payment_changed':
                            $payment = $data['payment'];
                            if ($payment) {
                                $this->manager->setSelectedPayment($payment);
                                $this->addLog('Payment changed to ' . $payment);
                            }
                            //$this->manager->checkoutOrder();
                            //$response['order_totals'] = $this->manager->render('Totals', ['manager' => $this->manager]);
                            break;
                        case 'remove_coupon':
                            $cart->removeCcItem($data['coupon']);
                            break;
                        case 'remove_module':
                            $cart->addHiddenModule($data['module']);
                            $cart->clearTotalKey($data['module']);
                            break;
                        case 'credit_class':
                            $this->manager->remove('cot_gv');
                            $this->manager->remove('cc_id');
                            $this->manager->remove('cc_code');

                            if ($data['coupon_apply'] != 'y' || isset($data['cot_gv_amount'])) break;
                            if (!$this->manager->isCustomerAssigned()) break;

                            $creditAmount = \common\helpers\Customer::getCreditAmount($cart->customer_id);
                            if ($creditAmount <= 0) break;

                            $this->manager->checkoutOrder();
                            $output =  $this->manager->getTotalOutput(false);
                            $otDue = \common\helpers\Php::arrayGetSubArrayBySubValue($output, 'code', 'ot_due')['value'] ?? 0;
                            if ($otDue <= 0) break;

                            $nextCredit = min($otDue, $creditAmount);
                            $data['cot_gv_amount'] = $nextCredit;
                            $this->manager->getTotalCollection()->clearTotalCache();
                            break;
                        case 'detect_code':
                            if (!empty($data['coupons'])) {
                                $cart->addCcItem($data['coupons']);
                                //$data['gv_redeem_code'] = $data['coupons'];
                            }
                            break;
                        case 'check_refund':
                            $order = $this->manager->getOrderInstance();
                            $orders_id = Yii::$app->request->get('orders_id');
                            if ($orders_id){
                                $order->order_id = $orders_id;
                                if ($order->hasTransactions()){
                                    $tm = $this->manager->getTransactionManager();
                                    if ($tm->isReady()){
                                        if ($tm->getTransactionsCount() > 1){
                                            return json_encode(['message' => 'There are some transactions. To refund do it manualy..', 'value' => 'to_credit']);
                                        } else {
                                            $transaction = $tm->getTransactions()[0];
                                            $payment = $this->manager->getPaymentCollection()->get($transaction->payment_class);
                                            if ($payment){
                                                $tm->usePayment($payment);
                                                if ($tm->canPaymentRefund($transaction->transaction_id)){
                                                    return json_encode(['value' => 'refund', 'text' => TEXT_MAKE_REFUND]);
                                                } else if ($tm->canPaymentVoid($transaction->transaction_id)){
                                                    return json_encode(['value' => 'void', 'text' => TEXT_VOID_PAYMENT]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            return json_encode([]);
                            break;
                    }
                }
            } else {
                $data = Yii::$app->request->get();
                switch ($data['action'] ?? null) {
                    case 'get_address_list':
                        $type = $data['type'];
                        return $this->manager->render('AddressesList', ['manager' => $this->manager, 'mode' => 'select', 'type' => $type], 'html');
                        break;
                    case 'edit_address':
                        $type = $data['type'];
                        return $this->manager->render('AddressesList', ['manager' => $this->manager, 'mode' => 'edit', 'type' => $type, 'ab_id' => $data['ad_id'] ?? ''], 'html');
                        break;
                    case 'show_statuses':
                        return $this->manager->render('OrderStatusesList', ['manager' => $this->manager], 'html');
                        break;
                    case 'show_delete':
                        return $this->manager->render('DeleteOrderConfirm', ['manager' => $this->manager], 'html');
                        break;
                }
            }

            $this->manager->set('cart', $cart); //working_cart

            $this->manager->checkoutOrderWithAddresses();

            if ($data) {
                $response['credit_modules'] = $this->manager->totalCollectPosts($data);
                $response['payments'] = $this->manager->render('Payment', ['manager' => $this->manager]);
            }

            $this->manager->totalPreConfirmationCheck();
            $this->manager->updateShippingCost();

            $response['order_totals'] = $this->manager->render('OrderTotals', ['manager' => $this->manager]);
            $response['order_state'] = $this->getOrderStateArray();
        }

        echo json_encode($response);
        exit();
    }

    public function actionSaveAddress()
    {
        $post = Yii::$app->request->post();

        $customer = $this->manager->getCustomersIdentity();
        $this->manager->createOrderInstance($this->manager->get('order_instance'));
        $customer->get('fromOrder');
        if ($post['type'] == 'shipping') {
            $form = $this->manager->getShippingForm();
        } else {
            $form = $this->manager->getBillingForm();
        }
        $form->load(Yii::$app->request->post());
        $ab = AddressBook::findOne(['address_book_id' => $form->address_book_id]);
        $this->manager->set('customer_id', $ab->customers_id);
        $attributes = $customer->getAddressFromModel($form);
        $attributes['customers_id'] = $ab->customers_id;
        $aBook = $customer->updateAddress($form->address_book_id, $attributes);
        if ($post['type'] == 'shipping') {
            $this->manager->set('sendto', $aBook->address_book_id);
        } else {
            $this->manager->set('billto', $aBook->address_book_id);
        }

        $this->manager->changeCustomerAddressSelection($post['type'], $aBook->address_book_id);
        $response['shipping_address'] = $this->manager->render('ShippingAddress', ['manager' => $this->manager]);
        $response['billing_address'] = $this->manager->render('BillingAddress', ['manager' => $this->manager]);

        return json_encode($response);
    }

    public function actionCreateAccount() {
        \common\helpers\Translation::init('admin/customers');
        $this->manager->createAccount = true;
        $contactForm = $this->manager->getCustomerContactForm();
        $contactForm->preloadCustomersData();
        $shippingForm = null;
        if ($this->manager->isShippingNeeded()) {
            $shippingForm = $this->manager->getShippingForm();
        }
        $billingForm = $this->manager->getBillingForm();

        $messageStack = \Yii::$container->get('message_stack');

        if (Yii::$app->request->isPost) {
            $billingValid = $shippingValid = true;
            $contactValid = $contactForm->load(Yii::$app->request->post()) && $contactForm->validate();
            $shipAsBill = Yii::$app->request->post('ship_as_bill');
            if ($this->manager->isShippingNeeded()) {
                $shippingValid = $shippingForm->load(Yii::$app->request->post()) && $shippingForm->validate();
                if (!$shipAsBill) {
                    $billingValid = $billingForm->load(Yii::$app->request->post()) && $billingForm->validate();
                }
            } else {
                $billingValid = $billingForm->load(Yii::$app->request->post()) && $billingForm->validate();
            }
            $error = false;
            if ($billingValid && $shippingValid && $contactValid) {
                $_get = Yii::$app->request->get();
                $this->storage->setPointer($_get['currentCart']);
                $this->admin->setCurrentCartID($_get['currentCart']);
                $cart = $this->manager->get('cart');
                $this->manager->remove('estimate_ship');
                $this->manager->remove('estimate_bill');
                $customer = new \common\components\Customer();
                $customer->registerCustomer($contactForm, false);
                $this->manager->predefineCustomerDetails($customer->customers_id);
                if ($cart) {
                    $cart->setCustomer($customer->customers_id);
                    $this->manager->set('cart', $cart);
                }
                //switch pointer && cart id
                if ($this->manager->isShippingNeeded()) {
                    if ($shipAsBill) {//add only
                        $attributes = $customer->getAddressFromModel($shippingForm);
                        $aBook = $customer->updateAddress($customer->customers_default_address_id, $attributes);
                        if ($aBook) {
                            $this->manager->set('sendto', $aBook->address_book_id);
                            $this->manager->set('billto', $aBook->address_book_id);
                        }
                    } else {
                        $different = false;
                        if ($shippingForm->notEmpty() && $billingForm->notEmpty()) {
                            foreach ($shippingForm->getActiveAttributes() as $name => $value) {
                                if ($billingForm->{$name} != $value) {
                                    $different = true;
                                }
                            }
                            if ($different) {
                                $attributes = $customer->getAddressFromModel($billingForm);
                                $aBook = $customer->updateAddress($customer->customers_default_address_id, $attributes);
                                if ($aBook) {
                                    $this->manager->set('billto', $aBook->address_book_id);
                                }
                                $attributes = $customer->getAddressFromModel($shippingForm);
                                $aBook = $customer->addAddress($attributes);
                                if ($aBook) {
                                    $this->manager->set('sendto', $aBook->address_book_id);
                                }
                            }
                        }
                    }
                } else {//ne address from billing form
                    $attributes = $customer->getAddressFromModel($billingForm);
                    $aBook = $customer->updateAddress($customer->customers_default_address_id, $attributes);
                    if ($aBook) {
                        $this->manager->set('billto', $aBook->address_book_id);
                    }
                }
                echo json_encode(['success' => true, 'messages' => SUCCESS_CUSTOMERUPDATED]);
                exit();
            } else {
                $error = true;
                foreach ($contactForm->getErrors() as $error) {
                    $messageStack->add((is_array($error) ? implode("<br>", $error) : $error), 'one_page_checkout');
                }
                if ($this->manager->isShippingNeeded()) {
                    foreach ($shippingForm->getErrors() as $error) {
                        $messageStack->add((is_array($error) ? implode("<br>", $error) : $error), 'one_page_checkout');
                    }
                }
                if (!$shipAsBill) {
                    foreach ($billingForm->getErrors() as $error) {
                        $messageStack->add((is_array($error) ? implode("<br>", $error) : $error), 'one_page_checkout');
                    }
                }
                if ($messageStack->size('one_page_checkout') > 0) {
                    $messages = $messageStack->output('one_page_checkout');
                }
                echo json_encode(['error' => true, 'messages' => $messages]);
                exit();
            }
        }

        return $this->manager->render('Account', ['manager' => $this->manager,
                    'contactForm' => $contactForm,
                    'shippingForm' => $shippingForm,
                    'billingForm' => $billingForm
        ]);
    }

    public function actionLoadTree() {
        \common\helpers\Translation::init('admin/platforms');
        $this->layout = false;

        $_get = Yii::$app->request->get();

        $this->storage->setPointer($_get['currentCart']);

        $post = Yii::$app->request->post();
        $post['platform_id'] = $this->manager->getPlatformId();

        return $this->manager->render('ProductsBox', ['manager' => $this->manager, 'post' => $post], 'json');
    }

    protected function tep_get_category_children(&$children, $platform_id, $categories_id) {
        if (!is_array($children))
            $children = array();
        foreach ($this->load_tree_slice($platform_id, $categories_id) as $item) {
            $key = $item['key'];
            $children[] = $key;
            if ($item['folder']) {
                $this->tep_get_category_children($children, $platform_id, intval(substr($item['key'], 1)));
            }
        }
    }

    public function actionSettings() {
        $this->layout = false;

        $showFull = true;
        $currentCurrent = Yii::$app->request->get('currentCurrent', null);
        $currency = $language_id = null;
        if ($currentCurrent) {
            $this->storage->setPointer($currentCurrent); //!!importnat to set pointer before using stored data
            $platform_id = $this->storage->get('platform_id');
            $currency = $this->storage->get('currency');
            $language_id = $this->storage->get('languages_id');
        } else {
            $platform_id = Yii::$app->request->post('platform_id') ?? 0;
            $showFull = $platform_id ? false : true;
        }

        $entry = new \stdClass();
        $this->loadPlatformDetails($entry, $platform_id, $currency, $language_id);
        
        return $this->renderAjax('settings', [
                    'entry' => $entry,
                    'cl' => !$showFull,
                    'currentCurrent' => $currentCurrent,
                    'back' => \Yii::$app->request->get('back'),

        ]);
    }

    public function process($cart) { //for all order instances
        $currentCart = $this->admin->getCurrentCartID();

        if ($cart) {

            if ($this->manager->has('platform_id')) {
                $cart->setPlatform($this->manager->get('platform_id'));
            }
            if ($this->manager->has('currency')) {
                $cart->setCurrency($this->manager->get('currency'));
            }
            if ($this->manager->has('languages_id')) {
                $cart->setLanguage($this->manager->get('languages_id'));
            }

            $this->loadPlatformConsts();

            $this->manager->showAdminOwnerNotification = false;
            if (!$this->admin->checkCartOwnerClear($cart)){
                $this->manager->showAdminOwnerNotification = true;
            }

            $this->manager->loadCart($cart);
            $this->manager->createOrderInstance($this->manager->get('order_instance'));
            $this->loadCheckoutDetails($cart->order_id);

            if ($cart->customer_id && (!$this->manager->isCustomerAssigned() || $cart->customer_id != $this->manager->getCustomerAssigned() )) {
                $this->manager->predefineCustomerDetails($cart->customer_id);
            }
            $this->manager->set('cart', $cart);

            if (!($this->manager->has('platform_id') && $this->manager->has('currency') && $this->manager->has('languages_id') )) {
                $this->manager->showSettings = true && (Yii::$app->request->get('currentCart') ?? false);
                if ($this->manager->showSettings){
                    $this->manager->showSettings = $this->minifyPlatforms();
                }
            }

            if (!$this->manager->has('shipping')) {
                $this->manager->getShippingQuotesByChoice();
                $collection = $this->manager->getShippingCollection($this->manager->getShipping());
                // {{ set shipping on open
                //if ($cheapest = $collection->getFirstQuoteModule('no_shipping')) {
                //}else{
                    $cheapest = $collection->cheapest();
                //}
                if ( is_array($cheapest) && !$this->manager->getShipping() ){
                    $this->manager->setShipping($cheapest);
                    $this->manager->getShippingQuotesByChoice(true);
                }
                // }} set shipping on open
            } else {
                $this->manager->updateShippingCost();
            }

            $this->manager->checkoutOrderWithAddresses();

            return $this->render('edit', [
                        'currentCart' => $currentCart,
                        'manager' => $this->manager,
                        'admin' => $this->admin,
                        'order_id' => $this->manager->getOrderInstance()->order_id,
                        'paramOrderId' => Yii::$app->request->get('orders_id'),
                        'order_state' => $this->getOrderStateArray(),
            ]);
        } else {
            throw new \Exception('cart not created');
        }
    }

    public function minifyPlatforms(){
        if (\common\classes\platform::isMulti(false, true)){
            return true;
        } else {
            $entry = new \stdClass();
            $this->loadPlatformDetails($entry, \common\classes\platform::defaultId());
            if ($entry->default_platform){
                $this->manager->set('platform_id', $entry->default_platform);
            }
            if ($entry->defualt_platform_currency){
                $this->manager->set('currency', $entry->defualt_platform_currency);
            }
            if ($entry->defualt_platform_language){
                $this->manager->set('languages_id', $entry->defualt_platform_language);
            }
            if ($this->manager->has('platform_id') && $this->manager->has('currency') && $this->manager->has('languages_id'))
                return false;
        }
        return true;
    }

    public function actionOrderEdit()
    {
        $this->admin->loadCustomersBaskets('cart');
        \common\helpers\Translation::init('admin/customers');

        $order = null;
        $oID = Yii::$app->request->get('orders_id');
        if (tep_not_null($oID) && $oID) {
            $order = \common\models\Orders::findOne(['orders_id' => $oID]);
            if (!$order) {
                return $this->redirect(['orders/']);
            }
            $this->manager->set('platform_id', $order->platform_id);
            $this->manager->set('currency', $order->currency);
            $this->manager->set('languages_id', $order->language_id);
        }
        
        $cart = $this->obtainCustomerCart('\common\classes\shopping_cart', $order, Yii::$app->request->get('currentCart', ''));
        
        $currentCart = $this->admin->getCurrentCartID();
        $this->storage->setPointer($currentCart);
        if ($this->admin->newCartCreated) {
            $this->storage->removeAll();
            if ($order) {
                $this->manager->set('platform_id', $order->platform_id);
                $this->manager->set('currency', $order->currency);
                $this->manager->set('languages_id', $order->language_id);
            }
        }

        if (!Yii::$app->request->get('currentCart') && $currentCart){
            $qParams = Yii::$app->request->getQueryParams();
            $qParams['currentCart'] = $currentCart;
            return $this->redirect(array_merge(['order-edit'], $qParams));
        }

        $titleID = empty($oID)? TEXT_CREATE_NEW_OREDER : TEXT_ORDER_ID . ' #' . $oID;
        $titleDT = empty($order->date_purchased) ? '' : ' <div class="head-or-time">' . TEXT_DATE_AND_TIME . ' ' . $order->date_purchased . '</div>';
        $titlePlatform = $this->manager->has('platform_id') ? ' <div class="order-platform">' .TABLE_HEADING_PLATFORM . ':' . \common\classes\platform::name($this->manager->get('platform_id')) . '</div>' : '';
        $this->navigation[] = array('title' => $titleID . $titleDT . $titlePlatform);
        //set currency before
        if ($this->manager->has('cart')) {
            $_cart = $this->manager->get('cart');
            if ($_cart->basketID != $cart->basketID) { //different stored form working cart
                $this->manager->set('cart', $cart);
            } else {
                $cart = $_cart;
            }
        }

        $this->manager->set('order_instance', '\common\classes\Order');

        return $this->process($cart);
    }

    public function actionQuoteEdit() {

        if (!\common\helpers\Acl::checkExtensionAllowed('Quotations')) return '';

        $this->admin->loadCustomersBaskets('quote');

        $oID = Yii::$app->request->get('orders_id');
        if (tep_not_null($oID) && $oID) {
            $order = \common\extensions\Quotations\models\QuoteOrders::findOne(['orders_id' => $oID]);
            if (!$order) {
                return $this->redirect(['quotation/']);
            }
        }

        $cart = $this->obtainCustomerCart('\common\extensions\Quotations\QuoteCart', $order, Yii::$app->request->get('currentCart', ''));

        $currentCart = $this->admin->getCurrentCartID();
        $this->storage->setPointer($currentCart);
        if ($this->admin->newCartCreated) {
            $this->storage->removeAll();
        }

        $this->navigation[] = array('title' => (tep_not_null($oID) ? TEXT_QUOTATION : TEXT_CREATE_NEW_QUOTATION) . (tep_not_null($oID) ? ' #' . $oID . ' <div class="head-or-time">' . TEXT_DATE_AND_TIME . ' ' . $order->date_purchased . '</div>' : '') . ($this->manager->has('platform_id')? ' <div class="order-platform">' . TABLE_HEADING_PLATFORM . ':' . \common\classes\platform::name($this->manager->get('platform_id')) . '</div>':''));
        //set currency before
        if ($this->manager->has('cart')) {
            $_cart = $this->manager->get('cart');
            if ($_cart->basketID != $cart->basketID) {
                $this->manager->set('cart', $cart);
            } else {
                $cart = $_cart;
            }
        }

        $this->manager->set('order_instance', '\common\extensions\Quotations\Quotation');

        return $this->process($cart);
    }

    public function actionDeletecart() {
        $id = Yii::$app->request->post('deleteCart');
        $admin = new AdminCarts();
        $_cb = explode("-", $id);
        if ($admin->deleteCartByBC($_cb[0], $_cb[1])) {
            $ids = $admin->getVirtualCartIDs();
            if ($ids) {
                $_last = $admin->getLastVirtualID();
                if (!in_array($_last, $ids)) { // last was deleted
                    echo json_encode(['goto' => Url::to(['orders/order-edit', 'currentCart' => $ids[0]])]);
                    exit();
                }
            } else {
                echo json_encode(['goto' => Url::to(['orders/'])]);
                exit();
            }
        }
        echo json_encode(['reload' => true]);
        exit();
    }

    public function loadPlatformDetails($entry, $platform = 0, $currency = null, $language_id = null) {
        $entry->platforms = \yii\helpers\ArrayHelper::map(platform::getList(false, true), 'id', 'text');
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
                $_tmp[$pc] = $pc;
            }
            $entry->platform_currencies = $_tmp;
        } else {
            $entry->platform_currencies[DEFAULT_CURRENCY] = DEFAULT_CURRENCY;
        }
        if ($c = $platform_config->getDefaultCurrency()) {
            $entry->defualt_platform_currency = $c;
        } else {
            $entry->defualt_platform_currency = DEFAULT_CURRENCY;
        }

        if (!is_null($currency)) {
            $entry->defualt_platform_currency = $currency;
        }

        //language
        global $lng;
        $platform_languages = $platform_config->getAllowedLanguages();
        if ($platform_languages) {
            $_tmp = [];
            foreach ($platform_languages as $pl) {
                $_tmp[$lng->catalog_languages[$pl]['id']] = $lng->catalog_languages[$pl]['name'];
            }
            $entry->platform_languages = $_tmp;
        } else {
            $entry->platform_languages[$lng->catalog_languages[DEFAULT_LANGUAGE]['id']] = $lng->catalog_languages[DEFAULT_LANGUAGE]['name'];
        }

        if ($c = $platform_config->getDefaultLanguage()) {
            $entry->defualt_platform_language = $lng->catalog_languages[$c]['id'];
        } else {
            $entry->defualt_platform_language = $lng->catalog_languages[DEFAULT_LANGUAGE]['id'];
        }
        if (!is_null($language_id)) {
            $entry->defualt_platform_language = $language_id;
        }
    }

    public function actionUpdatepay() {
        $currencies = Yii::$container->get('currencies');
        //$session = new \yii\web\Session;

        \common\helpers\Translation::init('admin/main');
        \common\helpers\Translation::init('admin/orders/order-edit');

        $this->view->headingTitle = HEADING_TITLE;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders/index'), 'title' => HEADING_TITLE);
        $this->layout = false;
        $data = Yii::$app->request->post();
        $this->storage->setPointer($data['currentCart']); //!!importnat to get current data
        $this->admin->setCurrentCartID($data['currentCart']);
        $cart = $this->manager->get('cart'); //working_cart
        $this->checkOrderOwner($cart);
        $this->loadPlatformConsts();
        if ($cart) {
            $this->manager->loadCart($cart);
            $this->manager->createOrderInstance($this->manager->get('order_instance'));
            $this->manager->checkoutOrderWithAddresses();
            /*if ($cart->order_id){
                $this->manager->getOrderInstanceWithId($this->manager->get('order_instance'), $cart->order_id);
            }*/
            $ot_total = $data['ot_total'] ?? 0;
            $ot_paid = $data['ot_paid'] ?? 0;

            $new_ot_total = $ot_total;
            $ot_paid = (float) $ot_paid;

            $old_ot_total = $ot_paid;

            /** @var \common\extensions\UpdateAndPay\UpdateAndPay $ext */
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UpdateAndPay', 'allowed')) {
                return $ext::getActions($old_ot_total, $new_ot_total, $this->manager);
            }

            $difference_ot_total = $old_ot_total - $new_ot_total;
            $difference = ($difference_ot_total >= 0 ? true : false);

            $adminPaymentLink = false;
            if ( extension_loaded('openssl') ) {
              $adminPaymentLink = true;
            }

            $currency_value = $currencies->currencies[$cart->currency]['value'];
            return $this->render('updatepay', [
                        'new_ot_total' => Formatter::price($new_ot_total, 0, 1, $cart->currency, $currency_value),
                        'old_ot_total' => Formatter::price($old_ot_total, 0, 1, $cart->currency, $currency_value),
                        'difference_ot_total' => $currencies->format($difference_ot_total, true, $cart->currency, $currency_value),
                        'pay_difference' => $difference_ot_total,
                        'difference' => $difference,
                        'adminPaymentLink' => $adminPaymentLink,
                        'difference_desc' => $difference ? CREDIT_AMOUNT : TEXT_AMOUNT_DUE,
                        'manager' => $this->manager,
            ]);
        }
    }

    public function actionCreateOrder(){
        $customers_id = Yii::$app->request->get('customers_id');
        $basket_id = Yii::$app->request->get('basket_id', false);
        if ($customers_id){

            $customer = \common\components\Customer::findOne($customers_id);

            if ($customer){
                $cart = $this->obtainCustomerCart('\common\classes\shopping_cart', null, '', $customer->customers_id);

                $currentCart = $this->admin->getCurrentCartID();
                $this->storage->setPointer($currentCart);
                if ($this->admin->newCartCreated) {
                    $this->storage->removeAll();
                }

                if (!$this->manager->isCustomerAssigned()) {
                    $this->manager->predefineCustomerDetails($customer->customers_id, true);
                    $cart->setCustomer($customer->customers_id);
                }

                if (Yii::$app->request->get('convert')){
                    if ($ext = Acl::checkExtensionAllowed('RecoverShoppingCart', 'allowed')) {
                        $ext::convertCart($cart, true, $basket_id, $customers_id);
                    }
                }

                $this->manager->set('cart', $cart);

                return $this->redirect(['editor/order-edit', 'currentCart' => $currentCart]);
            }
        }

        return $this->redirect([$_GET['back'].'/index', 'customers_id' => $customers_id]);
    }

    public function actionOwner(){
        $currentCurrent = Yii::$app->request->get('currentCurrent', null);
        $response = ['reload' => true];
        if ($currentCurrent) {
            $this->storage->setPointer($currentCurrent);
            $cart = $this->manager->get('cart');
            if ($cart){
                $name = $this->admin->getAdminByCart($cart);
                if (Yii::$app->request->isPost){
                    if (Yii::$app->request->post('action') == 'confirm'){
                        if ($this->admin->reassignCart($cart)){
                            $order = $this->manager->getOrderInstanceWithId($this->manager->get('order_instance'), $cart->order_id);
                            $order->addLegend("Order sucessfully reassigned from {$name}", Yii::$app->session->get('login_id'));
                        }
                    }
                    if (Yii::$app->request->post('action') == 'discard'){
                        $order_id = $cart->order_id;
                        $this->admin->deleteCartByOrder($order_id);
                        $order = $this->manager->getOrderInstanceWithId($this->manager->get('order_instance'), $order_id);
                        $order->addLegend("Order sucessfully unassigned from {$name}", Yii::$app->session->get('login_id'));
                    }
                } else {
                    $changesList = [];
                    //----------------------------------------------------------
                    $admin = tep_db_fetch_array(tep_db_query("select * from " . TABLE_ADMIN_SHOPPING_CARTS . " where customers_id ='" . (int) $cart->customer_id . "' and order_id = '" . (int) $cart->order_id . "' and cart_type='".$this->admin->getCartType($cart)."'"));

                    $obj1 = $cart;
                    $obj2 = unserialize(base64_decode($admin['customer_basket']));

                    if (is_array($obj1->contents)) {
                        foreach ($obj1->contents as $key => $value) {
                            $productname = 'Unknown';
                            if (isset($obj1->overwrite[$key]['name'])) {
                                $productname = $obj1->overwrite[$key]['name'];
                            } else {
                                $productname = \common\helpers\Product::get_products_name($key);
                            }
                            $qty = $value['qty'];

                            if (isset($obj2->contents[$key]['qty'])) {
                                $qty2 = $obj2->contents[$key]['qty'];
                                if ($qty > $qty2) {
                                    $changesList[] = "<font color=\"red\">" . $productname . ": " . $qty . " > " . $qty2 . "</font>";//red
                                } elseif ($qty2 > $qty) {
                                    $changesList[] = "<font color=\"green\">" . $productname . ": " . $qty . " > " . $qty2 . "</font>";//green
                                } else {
                                    $changesList[] = $productname . ": " . $qty2;
                                }

                            } else {
                                $changesList[] = "<font color=\"red\">" . $productname . ": DELETED</font>";//red
                            }
                        }
                    }

                    if (is_array($obj2->contents)) {
                        foreach ($obj2->contents as $key => $value) {
                            if (!isset($obj1->contents[$key]['qty'])) {
                                $productname = 'Unknown';
                                if (isset($obj2->overwrite[$key]['name'])) {
                                    $productname = $obj2->overwrite[$key]['name'];
                                } else {
                                    $productname = \common\helpers\Product::get_products_name($key);
                                }
                                $changesList[] = "<font color=\"green\">" . $productname . ": ADDED</font>";//red
                            }


                        }
                    }

                    $obj1Total = $obj1->show_total();
                    $obj2Total = $obj2->show_total();
                    if ($obj1Total > $obj2Total) {
                        $changesList[] = "<font color=\"red\">Total: " . $obj1Total . " > " . $obj2Total . "</font>";
                    } elseif ($obj2Total > $obj1Total) {
                        $changesList[] = "<font color=\"green\">Total: " . $obj1Total . " > " . $obj2Total . "</font>";
                    } else {
                        $changesList[] = "Total: " . $obj2Total;
                    }
                    //----------------------------------------------------------
                    //draw
                    $goto = $this->getRedirect($cart, true);
                    $order_id = $cart->order_id;
                    return $this->manager->render('Owner', [
                            'changesList' => $changesList,
                            'currentCurrent' => $currentCurrent,
                            'name' => $name,
                            'manager' => $this->manager,
                            'cancel' => \Yii::$app->urlManager->createAbsoluteUrl([$goto, 'orders_id' => $order_id]),
                            'redirect' => \Yii::$app->urlManager->createAbsoluteUrl(['editor/order-edit', 'orders_id' => $order_id]),
                    ]);
                }
            }
        }
        return json_encode($response);
        exit();
    }

    public function actionOrderEditProducts() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        if( $length == -1 ) $length = 10000;

        $recordsTotal = 0;
        $recordsFiltered = 0;
        $responseList = [];

        $currencies = \Yii::$container->get('currencies');

        $data = Yii::$app->request->get('1');
        if (isset($data['currentCart'])) {
            $this->storage->setPointer($data['currentCart']); //!!importnat to get current data
            $cart = $this->manager->get('cart'); //working_cart
            if ($cart) {

                $tax_class_array = \common\helpers\Tax::get_complex_classes_list();

                $this->loadPlatformConsts();
                $this->manager->loadCart($cart);
                $this->manager->createOrderInstance($this->manager->get('order_instance'));
                $this->manager->defineOrderTaxAddress();

                $order = $this->manager->getOrderInstance($this->manager->get('order_instance'));
                $tax_address = $order->tax_address;
                $customer_groups_id = $this->manager->get('customer_groups_id');

                $savedProducts = [];
                if ($data['orders_id'] ?? false) {
                    $savedOrder = new \common\classes\Order($data['orders_id']);
                    if (isset($savedOrder->products) && is_array($savedOrder->products)) {
                        foreach ($savedOrder->products as $product) {
                            $savedProducts[] = $product['id'];
                        }
                    }
                }

                $products = $cart->get_products();
                if (is_array($products)){
                     foreach($products as $index => $product){
                        $recordsTotal++;
                        if ($index >= $start && $index < ($start+$length)) {
                             if (is_array($product['attributes'])){
                                $attrText = \common\classes\PropsWorkerAttrText::getAttrText($product['props']);
                                $_attributes = [];
                                foreach ($product['attributes'] as $option => $value) {
                                    $attributes_query = tep_db_query("select pa.products_attributes_id, popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int) $product['id'] . "' and pa.options_id = '" . (int) $option . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int) $value . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int) $this->manager->get('languages_id') . "' and poval.language_id = '" . (int) $this->manager->get('languages_id') . "'");
                                    $attributes = tep_db_fetch_array($attributes_query);

                                    if (isset($attributes['products_options_name']))
                                    $_attributes[] = array(
                                        'option' => $attributes['products_options_name'],
                                        'value' => $attributes['products_options_values_name'],
                                        'option_id' => $option,
                                        'value_id' => $value,
                                    );
                                }
                                $product['attributes'] = $_attributes;
                            }

                            $responseItem = [];
                            $qtyColumn = '';
                            if ($product['parent'] == '') {
                                if (!$product['ga']) {
                                    $qtyColumn .= $this->manager->render('Qty', ['product' => $product,  'manager' => $this->manager, 'isPack' => ($product['is_pack'] ?? null)]);
                                } else {
                                    $qtyColumn .= '<div class="box_al_center">' . $product['quantity'] . '</div>';
                                }
                            } else {
                                $qtyColumn .= $product['quantity'];
                            }
                            $qtyColumn .= tep_draw_hidden_field('uprid', $product['id']);

                            $nameColumn = '<table class="table no-border"><tr><td width="25%" class="order-product-image">';
                            $nameColumn .= '<div>'.\common\classes\Images::getImage($product['id'],'Small') . '</div>';
                            $nameColumn .= '</td><td style="text-align:left;vertical-align:middle;">';
                            if (false) {//$isEditInGrid
                                $nameColumn .= \yii\helpers\Html::input('text', "name", $product['name'],['class' => 'form-control name']);
                            } else {
                                $nameColumn .= '<label class="product-name">' . $product['name'] . '</label>';
                            }
                            if (!$product['ga'] && ($pu = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed'))) {
                                $nameColumn .= $pu::queryOrderProcessAdmin($products, $index);
                            }
                            if (is_array($product['attributes']) && count($product['attributes']) > 0) {
							//
                                foreach ($product['attributes'] as $option => $value) {
                                    $nameColumn .= '<div class="prop-tab-det-inp"><small>&ndash; ';
                                    $nameColumn .= $value['option'] . ' : ';
                                    $nameColumn .= $attrText[$value['option_id']] ?? $value['value'];
                                    $nameColumn .= '</small></div>';
                                }
                            }
							$nameColumn .= '<div><strong>'.TABLE_HEADING_PRODUCTS_MODEL.': </strong>' . $product['model'] . '</div>';
							if ($cart->cart_allow_giftwrap()) {
                                $gift_wrap = '';
                                if ($product['parent'] == '' && $product['gift_wrap_allowed']) {
                                    $gift_wrap = '<div class="gift-wrap"><strong>'.TEXT_GIFT_WRAP.': </strong><label>+' . $currencies->display_price($product['gift_wrap_price'], $product['tax']) . ' ' . \yii\helpers\Html::checkbox('gift_wrap[' . $product['id'] . ']', $product['gift_wrapped'], ['class' => 'check_on_off gift_wrap', 'onchange'=> "order.updateProductInRow(this, 'change_qty')"]) . '</label></div>';
                                }
                                $nameColumn .= $gift_wrap;
                            }
                            $nameColumn .= '</td></tr></table>';

                            $responseItem[] = $nameColumn;

                            $taxColumn = '';
                            if (!$product['ga'] && $product['final_price']) {
                                if (ArrayHelper::getValue($product, ['overwritten','tax_selected']) != '') {
                                    $zone_id = $product['overwritten']['tax_selected'];
                                } else {
                                    if (isset($product['products_tax_class_id'])) {
                                        $class_id = $product['products_tax_class_id'];
                                    } else {
                                        $class_id = $product['tax_class_id'];
                                    }
                                    $zone = \common\helpers\Tax::get_zone_id($class_id, $tax_address['entry_country_id'], $tax_address['entry_zone_id']);
                                    $zone_id = $class_id . "_" . $zone;
                                 }
                                if (\common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_EDIT_PRODUCT'])) {
                                    //$taxColumn = $this->manager->render('Tax', ['manager' => $this->manager, 'product' => $product, 'tax_address' => $tax_address, 'tax_class_array' => $tax_class_array, 'onchange' => "order.updateProductInRow(this, 'change_tax')" ]);
                                    $taxColumn = (isset($tax_class_array[$zone_id]) ? '<center>' . $tax_class_array[$zone_id] . '</center>': '');
                                } else {
                                    $taxColumn = (isset($tax_class_array[$zone_id]) ? '<center>' . $tax_class_array[$zone_id] . '</center>' : '');
                                }
                            }

                            // UnitPrice
                            $priceColumn = '';
                            if (\common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_EDIT_PRODUCT'])) {
                                if (!$product['ga'] && $product['parent'] == '') {

                                    /*$priceColumn = '<div class="dropdown" data-id="' . $product['id'] . '"><div data-bs-toggle="dropdown" data-bs-auto-close="outside" class="price-edit" data-bs-offset="-150,5">';*/
                                    $priceColumn .= $this->manager->render('ExtraCharge', ['product' => $product, 'manager' => $this->manager, 'edit' => false]);
                                    $priceColumn .= $this->manager->render('Price', ['field' => 'result_price', 'price' => $product['final_price'], 'tax' => 0, 'qty' => \common\helpers\Product::getVirtualItemQuantityValue($product['id']), 'currency' => $cart->currency, 'isEditInGrid' => false, 'classname' => 'result-price' ]);

                                    /*$priceColumn .= '</div><div class="dropdown-menu"><div class="price-editing">';

                                    $priceColumn .= $this->manager->render('ExtraCharge', ['product' => $product, 'manager' => $this->manager]);

                                    $priceColumn .= '</div></div></div>';*/
                                }
                            }
                            $responseItem[] = $priceColumn;

                            // TotalPrice exc vat
                            $responseItem[] = '<div class="qtyWrap">' . $qtyColumn . '</div>';
							$exc_vat_table = $this->manager->render('Price', ['field' => 'final_price_total_exc_tax', 'price' => $product['final_price'], 'tax' => 0, 'qty' => $product['quantity'], 'currency' => $cart->currency ]);
							$tax_table = $taxColumn;
							$responseItem[] = $exc_vat_table . ($tax_table ? ('<div><center><strong>' .TABLE_HEADING_TAX.'</strong></center></div>'.$tax_table) : '');
//vat on order
                            $_rate = $product['tax_rate'];
                            if ($_rate>0 && $VatOnOrder = \common\helpers\Acl::checkExtensionAllowed('VatOnOrder', 'allowed')) {
                                if ($VatOnOrder::check_vat_status($tax_address)) {
                                    $_rate = 0;
                                }
                            }
                            /** @var \common\extensions\BusinessToBusiness\BusinessToBusiness $ext */
                            if ($_rate>0 && $ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
                                if ($ext::checkTaxRate($customer_groups_id)) {
                                    $_rate = 0;
                                }
                            }

                            // TotalPrice inc vat
                            $responseItem[] = $this->manager->render('Price', ['field' => 'final_price_total_inc_tax', 'price' => $product['final_price'], 'tax' => $_rate, 'qty' => $product['quantity'], 'currency' => $cart->currency ]);

                            $queryParams = array_merge(['editor/show-basket'], $data);
                            $actionsColumn = '';
                            if ($product['parent'] == '') {
                                if ($product['ga']) {
                                    $actionsColumn .= '<div class="order-product-edit">';
                                    $actionsColumn .= \yii\helpers\Html::a('<i class="icon-pencil"></i>', Yii::$app->urlManager->createUrl(array_merge($queryParams, ['action' => 'show_giveaways', 'edit' => true])), ['class'=> "popup", 'data-class'=>"add-product"] );
                                    $actionsColumn .= '</div>';
                                    $actionsColumn .= '<div class="del-pt" onclick="deleteOrderGiveaway(this);">';
                                } else {
                                    $actionsColumn .= '<div class="order-product-edit">';
                                    $actionsColumn .= \yii\helpers\Html::a('<i class="icon-pencil"></i>', Yii::$app->urlManager->createUrl(array_merge($queryParams, ['uprid' => $product['id'], 'action' => 'edit_product'])), ['class'=> "popup", 'data-class'=>"edit-product"] );
                                    $actionsColumn .= '</div>';
                                    $actionsColumn .= '<div class="del-pt" onclick="deleteOrderProduct(this);">';
                                }
                            }
                            $responseItem[] = $actionsColumn;

                            $rowClass = '';
                            if (!in_array($product['id'], $savedProducts)) {
                                $rowClass = ' new-product';
                            }
                            if ($product['parent'] ?? '') {
                                $rowClass = ' child-product';
                            }
                            $responseItem['DT_RowClass'] = ' dataTableRow product_info' . $rowClass;
                            $responseList[] = $responseItem;
                        }
                        $recordsFiltered++;
                    }
                }
            }

        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $responseList
        );
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    private function getOrderStateArray()
    {
        $reason = '';
        return [
            'UpdateAndPay_available' => $this->manager->isPaymentAllowedEx(true, $reason) && $this->manager->isPaymentSelected($reason),
            'UpdateAndPay_available_reason' => $reason,
        ];
    }

}
