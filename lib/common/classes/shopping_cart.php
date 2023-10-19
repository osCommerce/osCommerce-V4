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

namespace common\classes;
use Yii;
use yii\helpers\ArrayHelper;
use common\helpers\Tax;
use common\models\CustomersBasket;
use common\models\CustomersBasketAttributes;
use common\helpers\Inventory as InventoryHelper;
use common\helpers\Session;

#[\AllowDynamicProperties]
class shopping_cart {

    /**
     * Override main config, used for feeds
     *  null - use global config
     *  false - don't use temporary stock
     * @var null|bool
     */
    public $config_temporary_stock_enable = null;

    public $table_prefix = '';

    public $max_width;
    public $max_length;
    public $max_height;

    public $contents = [];
    public $total;
    public $total_ex_tax;
    public $weight;
    public $volume;
    public $cartID;
    public $content_type;
    public $giveaway; // separated storage for GA products
    public $platform_id; //current customer selected platform
    /// full shit with shared session between platforms
    public $language_id; //current customer selected language
    public $currency; //current customer selected currency
    public $basketID; //current customer basket id, used for recovery cart
    public $admin_id = 0; //used at backend
    public $order_id = null;
    public $reference_id;
    public $customer_id = null;
    public $address = [];
    public $overwrite = []; // to overwrte by admin wish
    public $totals = []; // to overwrte by admin wish
    public $hidden = []; // to overwrte by admin wish
    public $readonly = ['ot_tax', 'ot_total', 'ot_subtotal', 'ot_subtax', 'ot_due', 'ot_paid'];
    private $adjusted = false; // order adjusted by 0.01
    protected $products_array = [];
    private $paid = [];
    private $order_status;
    private $collections;
    public $ignoreHooks;

    function __construct($id = '') {
        global $languages_id;
        $this->reset();
        $this->platform_id = \common\classes\platform::currentId();
        $this->language_id = $languages_id;
        $this->currency = \Yii::$app->settings->get('currency');
        $this->setBasketID(0);
        if (tep_not_null($id)) {
            $this->order_id = $id;
            $this->restore_order();
        }
        $this->reference_id = null;
    }

    public function __wakeup() {
    }

    public function setReference($reference_id){
        $this->reference_id = (int)$reference_id;
    }

    public function emptyReference(){
        $this->reference_id = null;
    }

    public function getReference(){
        return $this->reference_id;
    }

    public function notEmpty(){
        return count($this->contents) > 0;
    }

    public function isEmptyProducts() {
        return count($this->get_products()) == 0;
    }

    function restore_order() {
        $contents = array();
        $this->overwrite = array();
        $this->giveaway = array();
        $this->totals = array();
        $this->hidden = array();
        $res = tep_db_fetch_array(tep_db_query("select platform_id from " . $this->table_prefix . TABLE_ORDERS . " where orders_id = '" . $this->order_id . "'"));
        if ($res){
            $this->platform_id = $res['platform_id'];
        }
        $res = tep_db_query("select * from " . $this->table_prefix . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $this->order_id . "' order by sort_order, orders_products_id");
        while ($d = tep_db_fetch_array($res)) {
            if (strlen($d['template_uprid']) > strlen($d['uprid'])) $d['uprid'] = $d['template_uprid'];
            if ($d['is_giveaway']) {
                $this->giveaway = array();
                $this->giveaway[$d['uprid']] = array('qty' => $d['products_quantity'], 'reserved_qty' => $d['products_quantity'], 'promo_id' => $d['promo_id']);
            } else {
                $contents[$d['uprid']] = array('qty' => $d['products_quantity'], 'parent' => $d['parent_product'], 'reserved_qty' => $d['products_quantity']);
                if ($d['gift_wrapped']) {
                    $contents[$d['uprid']]['gift_wrap'] = 1;
                }
                $this->setOverwrite($d['uprid'], 'name', $d['products_name']);
                $this->setOverwrite($d['uprid'], 'model', $d['products_model']);
                $this->setOverwrite($d['uprid'], 'price', $d['products_price']);
                $this->setOverwrite($d['uprid'], 'final_price', $d['final_price']);
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                    $_units = $ext::queryOrderAdmin($this->order_id, $d, $d['orders_products_id']);
                    if (is_array($_units) && count($_units)){
                        foreach ($_units as $k => $v) {
                            $this->setOverwrite($d['uprid'], $k, $v);
                        }
                    }
                }
            }
            $ares = tep_db_query("select * from " . $this->table_prefix . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . $this->order_id . "' and orders_products_id = '" . $d['orders_products_id'] . "'");
            if (tep_db_num_rows($ares)) {
                if ($d['is_giveaway']) {
                    $this->giveaway[$d['uprid']]['attributes'] = [];
                    while ($a = tep_db_fetch_array($ares)) {
                        $this->giveaway[$d['uprid']]['attributes'][$a['products_options_id']] = $a['products_options_values_id'];
                    }
                } else {
                    $contents[$d['uprid']]['attributes'] = [];
                    while ($a = tep_db_fetch_array($ares)) {
                        if ($a['products_options'] == GIFT_WRAP_OPTION)
                            continue;
                        if (!tep_not_null($a['products_options_values']) && !$a['products_options_id'] && !$a['products_options_values_id'] && strlen($a['price_prefix'] == 0)) {
                            //
                        } else {
                            $contents[$d['uprid']]['attributes'][$a['products_options_id']] = $a['products_options_values_id'];
                        }
                    }
                }
            }
            if (tep_not_null($d['sets_array'])){
                $bundle = unserialize($d['sets_array']);
                $bundle = current($bundle);
                if (is_array($bundle)){
                    if (isset($bundle['attributes']) && is_array($bundle['attributes'])){
                        foreach($bundle['attributes'] as $option => $value){
                             $contents[$d['uprid']]['attributes'][$option . '-'. $bundle['id']] = $value;
                        }
                    }
                }
            }
            if (tep_not_null($d['overwritten'])) {
                $_temp = unserialize($d['overwritten']);
                if (is_array($_temp)) {
                    foreach ($_temp as $_k => $_v) {
                        $this->setOverwrite($d['uprid'], $_k, $_v);
                    }
                }
            }
        }

        $res = tep_db_fetch_array(tep_db_query("select orders_status from " . $this->table_prefix . TABLE_ORDERS . " where orders_id = '" . $this->order_id . "'"));
        $this->setOrderStatus($res['orders_status']);

        $this->contents = $contents;
        $this->get_content_type();
        $this->restoreTotals();
    }

    public $cc_array = [];

    public function clearCcItems() {
        $this->cc_array = [];
    }

    public function addCcItem($coupon_code) {
        if (!empty($coupon_code)) {
            if ( !is_array($this->cc_array) ) $this->cc_array = [];
            $coupon_result = \common\models\Coupons::getCouponByCode($coupon_code, true);
            if ($coupon_result->coupon_type != 'G') {
                if ( $coupon_result->single_per_order ){
                    $this->cc_array = [];
                }else{
                    if ( count($this->cc_array)>0 ){
                        $any_single_used = \common\models\Coupons::find()
                            ->where(['IN', 'coupon_id', array_keys($this->cc_array)])
                            ->andWhere(['single_per_order'=>1])->count();
                        if ( $any_single_used ){
                            $this->cc_array = [];
                        }
                    }
                }
                $this->cc_array[$coupon_result->coupon_id] = $coupon_result->coupon_code;
            }
        }
    }

    public function removeCcItem($code) {
        if (is_array($this->cc_array)) {
            foreach ($this->cc_array as $key => $value) {
                if ($code == $value) {
                    unset($this->cc_array[$key]);
                }
            }
        }
    }
    public function restoreTotals(){
        if ($this->order_id > 0){
            $this->clearCcItems();
            $totals_query = tep_db_query("select * from " . $this->table_prefix . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $this->order_id . "' and is_removed = 0 order by sort_order");
            while ($totals = tep_db_fetch_array($totals_query)) {
                if ($totals['class'] =='ot_coupon') {
                    $ex = [];
                    preg_match("/\((.*)\):$/", $totals['title'], $ex);
                    if (is_array($ex) && isset($ex[1])) {
                        $coupon_code = $ex[1];
                        if (!empty($coupon_code)) {
                            $coupon_result = \common\models\Coupons::getCouponByCode($coupon_code, true);
                            if ($coupon_result->coupon_type != 'G') {
                                $this->cc_array[$coupon_result->coupon_id] = $coupon_result->coupon_code;
                            }
                        }
                    }
                }

                $this->totals[$totals['class']] = array('title' => $totals['title'],
                    'value' => [
                        'in' => $totals['value_inc_tax'],
                        'ex' => $totals['value_exc_vat'],
                    ],
                    //$totals['value'],
                    'class' => $totals['class'],
                    'text' => $totals['text'],
                    'text_exc_tax' => $totals['text_exc_tax'],
                    'text_inc_tax' => $totals['text_inc_tax'],
    // {{
                    'tax_class_id' => $totals['tax_class_id'],
                    'value_exc_vat' => $totals['value_exc_vat'],
                    'value_inc_tax' => $totals['value_inc_tax'],
    // }}
                );
                if ($totals['class'] == 'ot_tax' && $this->totals[$totals['class']]['value_exc_vat'] != $this->totals[$totals['class']]['value_inc_tax']) {
                    $this->totals[$totals['class']]['prefix'] = ($this->totals[$totals['class']]['value_inc_tax'] > $this->totals[$totals['class']]['value_exc_vat'] ? "+" : "-");
                    $this->totals[$totals['class']]['value'] = [
                        'in' => abs($this->totals[$totals['class']]['value_inc_tax'] - $this->totals[$totals['class']]['value_exc_vat']),
                        'ex' => abs($this->totals[$totals['class']]['value_inc_tax'] - $this->totals[$totals['class']]['value_exc_vat'])
                    ];
                }
            }
            $removed_query = tep_db_query("select class from " . $this->table_prefix . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int) $this->order_id . "' and is_removed = 1 order by sort_order");
            while ($removed = tep_db_fetch_array($removed_query)) {
                $this->addHiddenModule($removed['class']);
            }
            $adj = tep_db_fetch_array(tep_db_query("select adjusted from " . $this->table_prefix . TABLE_ORDERS . " where orders_id = '" . (int) $this->order_id . "'"));
            $this->setAdjusted($adj['adjusted']);
        }
    }

    public function setTotalKey($code, $value) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        //$readonly = ['ot_tax', 'ot_total', 'ot_subtotal'];
        if (!in_array($code, $this->readonly))
            $this->totals[$code]['value'] = $value;
    }

    public function setTotalTax($code, $value, $prefix) { //only for tax
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        $this->totals[$code]['value'] = $value;
        $this->totals[$code]['prefix'] = $prefix;
    }

    public function setTotalPaid($value, $prefix = '+', $comment = '') { //only for paid
        $currencies = \Yii::$container->get('currencies');
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        if (!is_array($this->totals['ot_paid'] ?? null)) $this->totals['ot_paid'] = [];
        if (!is_array($this->totals['ot_paid']['value'] ?? null)) $this->totals['ot_paid']['value'] = [];
        if (is_array($this->totals['ot_paid']['value'] ?? null)){
            $this->totals['ot_total']['value_inc_tax'] = $this->totals['ot_total']['value_inc_tax'] ?? null;
            $this->totals['ot_total']['value_exc_vat'] = $this->totals['ot_total']['value_exc_vat'] ?? null;
            $this->totals['ot_paid']['value']['in'] = $this->totals['ot_paid']['value']['in'] ?? null;
            $this->totals['ot_paid']['value']['ex'] = $this->totals['ot_paid']['value']['ex'] ?? null;
            $diff = $this->totals['ot_total']['value_inc_tax'] / ($this->totals['ot_total']['value_exc_vat'] != 0 ? $this->totals['ot_total']['value_exc_vat'] : 1);
            if ($prefix == '-'){
                $this->totals['ot_paid']['value']['in'] -= $value;
                $this->totals['ot_paid']['value']['ex'] -= $value / ($diff > 0 ? $diff : 1);
            } else {
                $this->totals['ot_paid']['value']['in'] += $value;
                $this->totals['ot_paid']['value']['ex'] += $value / ($diff > 0 ? $diff : 1);
            }
        }

        if (!isset($this->totals['ot_paid']['info'])) $this->totals['ot_paid']['info'] = [];
        $this->totals['ot_paid']['info'][] = [
            'comment' => $comment,
            ];
    }

    public function getPaidInfo(){
        if (!\frontend\design\Info::isTotallyAdmin())
            return false;
        if (isset($this->totals['ot_paid']['info'])) {
            $info = ['info' => $this->totals['ot_paid']['info'], 'status' => $this->getStatusAfterPaid()];
            return $info;
        }

        return false;
    }

    public function getStatusAfterPaid(){
        if (!\frontend\design\Info::isTotallyAdmin())
            return false;
        if (!is_null($this->order_status)) return $this->order_status;
        return false;
    }

    public function setOrderStatus($status){
        if (!\frontend\design\Info::isTotallyAdmin())
            return false;
        $this->order_status = $status;
    }

    public function getTotalTaxPrefix() {
        return $this->totals['ot_tax']['prefix'];
    }

    public function getTotalKey($code) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return false;
        if (isset($this->totals[$code]))
            return $this->totals[$code]['value'] ?? 0;
        return false;
    }

    public function getTotalKeyEx($code) {
        return $this->totals[$code]??null;
    }

    public function getTotalTitle($code) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        if (isset($this->totals[$code]))
            return $this->totals[$code]['title'];
        return false;
    }

    public function setTotalTitle($code, $value) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        //$readonly = ['ot_tax', 'ot_total', 'ot_subtotal'];
        if (!in_array($code, $this->readonly))
            $this->totals[$code]['title'] = $value;
    }

    public function getAllTotals() {
        if (!\frontend\design\Info::isTotallyAdmin())
            return [];
        return $this->totals;
    }

    public function clearTotalKey($code) {
        if (isset($this->totals[$code]))
            unset($this->totals[$code]);
    }

    public function clearTotals($only_values = false) {
        if (!$only_values){
            if (is_array($this->totals) && count($this->totals)){
                foreach($this->totals as $k=>$v){
                    if (!in_array($k, ['ot_coupon', 'ot_gv'])){
                        unset($this->totals[$k]);
                    }
                }
            }
            //$this->totals = array();
        } else {
            if (is_array($this->totals)){
                foreach($this->totals as $k => $v){
                    $this->totals[$k]['value'] = ['in'=>$this->totals[$k]['value_inc_tax'], 'ex'=> $this->totals[$k]['value_exc_vat']];
                }
            }
        }

    }

    public function addHiddenModule($code) {
        if (!\frontend\design\Info::isTotallyAdmin())
            return;
        if (!in_array($code, $this->readonly))
            $this->hidden[] = $code;
    }

    public function existHiddenModule($code) {
        return in_array($code, $this->hidden);
    }

    public function clearHiddenModule($code) {
        if (in_array($code, $this->hidden)) {
            $r = array_flip($this->hidden);
            unset($this->hidden[$r[$code]]);
        }
    }

    public function clearHiddenModules() {
        $this->hidden = [];
    }

    public function getHiddenModules() {
        return $this->hidden;
    }

    public function before_restore(){
        if (!defined('TEMPORARY_STOCK_ENABLE') || TEMPORARY_STOCK_ENABLE != 'true') return;
        if ( is_bool($this->config_temporary_stock_enable) && $this->config_temporary_stock_enable==false ) return;
        if (!is_array($this->contents)) return;

        foreach ($this->contents as $key => $value) {
            \common\helpers\Warehouses::remove_customers_temporary_stock_quantity($key);
        }
    }

    function restore_contents($uid = false) {
        global $languages_id;

        if (Yii::$app->user->isGuest)
            return false;
        $customer_id = Yii::$app->user->getId();

        if (!$this->platform_id)
            $this->platform_id = \common\classes\platform::currentId();
        if (!$this->language_id)
            $this->language_id = $languages_id;
        if (!$this->currency)
            $this->currency = \Yii::$app->settings->get('currency');

        $def_id = false;
        if( $multiCart = \common\helpers\Acl::checkExtension( 'MultiCart', 'allowed' ) ) {
            if ($multiCart::allowed()){
                $multiCart::saveBasketsInfo($customer_id);
                $def_id = $multiCart::getCurrentCartKey();
            }
        }

        if(!$def_id) {
            if (($def_id = CustomersBasket::getBasketId($customer_id)) !== false){
                $this->basketID = $def_id;
            } else {
                $this->basketID = $this->generate_cart_id();
            }
        }

        // insert current cart contents in database
        if (is_array($this->contents)) {
            $this->saveContents();
        }

        // recreate GA products
        if (sizeof($this->giveaway) > 0) {
            CustomersBasket::clearGiveaway($customer_id);
            $this->saveGiveAways();
        }

        // {{ WA - keep applied coupons after login
        $applied_coupons = $this->cc_array;
        // }} WA - keep applied coupons after login

        $this->reset(false);

        // {{ WA - keep applied coupons after login
        $this->cc_array = $applied_coupons;
        // }} WA - keep applied coupons after login

        $this->restoreContentProducts($uid);

// {{ Virtual Gift Card
        if ($customer_id > 0 && Session::getSession()->has('gift_handler')) {
            tep_db_query("update " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " set customers_id = '" . (int) $customer_id . "', session_id = '' where (length(virtual_gift_card_code) = 0 || !activated) and customers_id = '0' and session_id = '" . Session::getSession()->get('gift_handler') . "'");
            Session::getSession()->remove('gift_handler');
        }
// }}
        //$this->update_basket_info();

        $this->update_customer_info(false);

        $this->cleanup();

        $this->check_giveaway(); // GAW depends on customers group

/** @var \common\extensions\MultiCart\MultiCart $MultiCart */
        if( $multiCart = \common\helpers\Acl::checkExtension( 'MultiCart', 'allowed' ) ) {
            if ($multiCart::allowed()){
                $multiCart::restoreCarts($this, $customer_id);
            }
        }

        Yii::$app->get('PropsHelper')::afterCartRestore($this);

    }

    function saveContents(){

        if (is_array($this->contents) && !Yii::$app->user->isGuest){
            $customer_id = Yii::$app->user->getId();
            if( $multiCart = \common\helpers\Acl::checkExtension( 'MultiCart', 'allowed' ) ) {
                if ($multiCart::allowed()){
                    $multiCart::saveBasketInfo($this, $customer_id);
                }
            }
            foreach ($this->contents as $products_id => $val) {
                CustomersBasket::saveProduct([ $products_id => $val ], $customer_id, $this);
            }
        }
    }

    function saveGiveAways(){

        $customer_id = Yii::$app->user->getId();
        // insert new GA into DB
        if (is_array($this->giveaway) && $customer_id){
            foreach ($this->giveaway as $products_id => $giveaway) {
                CustomersBasket::saveProduct([$products_id => $giveaway], $customer_id, $this);
            }
        }
    }

    function restoreContentProducts($uid = false){

        // reset per-session cart contents, but not the database contents
        $customer_id = Yii::$app->get('storage')->get('customer_id');

        if ($uid !== false) {
            $productBasket_query = CustomersBasket::find()->where(['basket_id' => $uid]);
            $products_basket = $productBasket_query->with('productAttributes')
                    ->asArray()
                    ->all();
        } else {
            $products_basket = CustomersBasket::getProducts($customer_id, $this->basketID);
        }

        if ($products_basket){
            foreach (\common\helpers\Hooks::getList('shopping-cart/restore-content-products/products-basket') as $filename) {
                include($filename);
            }
            foreach($products_basket as $products){
                if (!\common\helpers\Product::check_product($products['products_id'], true, false, true) && !tep_not_null($products['parent_product'])) {
                    $this->remove($products['products_id']);
                    continue;
                }
                if ($products['is_pack'] == 1) {
                    $this->contents[$products['products_id']] = array('qty' => $products['customers_basket_quantity'], 'parent' => $products['parent_product'], 'unit' => $products['unit'], 'pack_unit' => $products['pack_unit'], 'packaging' => $products['packaging'], 'is_pack' => 1);
                } else {
                    $this->contents[$products['products_id']] = array('qty' => $products['customers_basket_quantity'], 'parent' => $products['parent_product']);
                }
                $this->contents[$products['products_id']]['relation_type'] = $products['relation_type'];
                $this->contents[$products['products_id']]['platform_id'] = $products['platform_id'];

                // attributes
                if ($products['productAttributes']){
                    foreach($products['productAttributes'] as $attributes){
                        if ($attributes['products_options_id'] == 'gift_wrap') {
                            $this->contents[$products['products_id']]['gift_wrap'] = $attributes['products_options_value_id'];
                        } else {
                            $this->contents[$products['products_id']]['attributes'][$attributes['products_options_id']] = $attributes['products_options_value_id'];
                        }
                    }
                }
                if (!empty($products['props'])) {
                    $this->contents[$products['products_id']]['props'] = $products['props'];
                }
            }
        }

        // recreate session GA
        $ga_query = tep_db_query("select customers_basket_id, products_id, customers_basket_quantity, gaw_id from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $customer_id . "' and is_giveaway = 1");
        while ($d = tep_db_fetch_array($ga_query)) {
            if ($d['gaw_id']==0) { //backward compatibility
              if($gaw_id = \common\helpers\Gifts::allowedGAW($d['products_id'], $d['customers_basket_quantity'])){
                tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set gaw_id='" . $gaw_id . "' where customers_basket_id='" . $d['customers_basket_id'] . "'");
                $d['gaw_id'] = $gaw_id;
              } else {
              //can't find matching active GAW - delete
                tep_db_query("delete from  " . TABLE_CUSTOMERS_BASKET . " where customers_basket_id='" . $d['customers_basket_id'] . "'");
                continue;
              }
            }
            $this->giveaway[$d['products_id']] = array('qty' => $d['customers_basket_quantity'], 'gaw_id' => $d['gaw_id']);
            if (strpos($d['products_id'], '{') !== false && preg_match_all('/\{(\d+)\}(\d+)/', $d['products_id'], $uprid_parts)) {
                $this->giveaway[$d['products_id']]['attributes'] = array();
                foreach ($uprid_parts[1] as $idx => $opt_id) {
                    $this->giveaway[$d['products_id']]['attributes'][$opt_id] = $uprid_parts[2][$idx];
                }
            }
        }
        $this->cartID = $this->generate_cart_id();
    }

/**
 *
 * @param bool $reset_database
 */
    function reset($reset_database = false) {

        $contents = $this->contents;
        $this->contents = array();
        $this->overwrite = array();
        $this->giveaway = array();
        $this->hidden = array();
        $this->total = 0;
        $this->total_ex_tax = 0;
        $this->weight = 0;
        $this->max_width = 0;
        $this->max_length = 0;
        $this->max_height = 0;
        $this->content_type = false;
        //unset($this->products_array);
        $this->products_array = [];

        $this->clearCcItems();

        if (!Yii::$app->user->isGuest && ($reset_database == true)) {
            CustomersBasket::clearBasket(Yii::$app->user->getId());
            //$this->basketID = $this->generate_cart_id();
        }

        unset($this->cartID);
        if (tep_session_is_registered('cartID'))
            tep_session_unregister('cartID');

        if (!$this->ignoreHooks && (get_class($this) == 'common\classes\shopping_cart')) {
            foreach (\common\helpers\Hooks::getList('shopping-cart/reset') as $filename) {
                include($filename);
            }
        }
    }

    public function resetDb($basket_id = null){
        if (!Yii::$app->user->isGuest) {
            CustomersBasket::clearBasket(Yii::$app->user->getId(), 0, $basket_id);
            //$this->basketID = $this->generate_cart_id();
        }
    }

    function __sleep() {
        $object_properties = get_object_vars($this);
        unset($object_properties['products_array']);
        $prop_names = array_keys($object_properties);

        // php8 compatibility - fix error: serialize(): "prop_name" returned as member variable from __sleep() but does not exist
        // where "prop_name" - private property of shopping_cart
        // happens if serialize children:
        // \\extensions\\Samples\\SampleCart
        // \\extensions\\Quotations\\QuoteCart
        // remove all unaccessable props for child classes
        if (get_class() != get_called_class()) {
            $reflection = new \ReflectionObject($this);
            foreach($prop_names as $key => $prop_name) {
                if (!$reflection->hasProperty($prop_name)) {
                    unset($prop_names[$key]);
                }
            }
        }
        return $prop_names;
    }

    function add_cart($products_id, $qty = '1', $attributes = '', $notify = true, $gaw_id = 0, $gift_wrap = null, $props = '') {
        global $new_products_id_in_cart;
        //unset($this->products_array);
        $this->products_array = [];
        $this->total_virtual = 0;
        $this->total = 0;
        $this->total_ex_tax = 0;
        $this->weight = 0;
        $this->max_width = 0;
        $this->max_length = 0;
        $this->max_height = 0;
        //$allow_auto_GAW = true;
        $customer_id = Yii::$app->user->getId();

        $original_products_id = $products_id;
        $products_id = \common\helpers\Inventory::get_prid($products_id);
// {{
        if ((int)$gaw_id > 0) { // product added as GA, check it
            $products_id = InventoryHelper::get_uprid($products_id, $attributes);
            $products_id = InventoryHelper::normalize_id($products_id);
            if (\common\helpers\Product::is_giveaway($products_id)) { // only GA can be added as GA
                //if (!isset($this->contents[$products_id . '(GA)'])) { //bug - always true (not set)
                //if (!$this->in_giveaway($products_id, $qty)) {// fix which cause logical problem - can't replace with GAW with dfferent option
                    if (sizeof($this->giveaway) > 0) { // only one GA allowed
                        $this->giveaway = array();
                    }
                    if ($gaw_id == \common\helpers\Gifts::allowedGAW($products_id, $qty)) {
                      $gaw_data = array('qty' => $qty, 'gaw_id' => $gaw_id);
                    } else {
                      $gaw_data = \common\helpers\Gifts::get_max_quantity($products_id);
                    }
                    $this->giveaway[$products_id] = array('qty' => $gaw_data['qty'], 'gaw_id' => $gaw_data['gaw_id']);
                    if (is_array($attributes) && count($attributes)) {
                        $this->giveaway[$products_id]['attributes'] = $attributes;
                    }
                    if ($customer_id > 0) { // update database
                        CustomersBasket::clearGiveaway($customer_id);
                        CustomersBasket::saveProduct([$products_id => $this->giveaway[$products_id]], $customer_id, $this);
                    }
            }
        } else { // ordinary product
// }}
            $packQty = $qty;
            if (is_array($qty)) {
                $qty = $packQty['qty'];
            }
            $qty = intval($qty);

// {{ Products Bundle Sets
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                $bundle_products = $ext::getBundleProducts($products_id, \common\classes\platform::currentId());
                if (count($bundle_products) > 0) {
                    return $this->add_bundle($products_id, $bundle_products, $qty, $attributes, $notify);
                }
            }
// }}
            if ($ext = \common\helpers\Extensions::isAllowed('LinkedProducts')) {
                $bundle_products = $ext::getChildrenProducts($products_id);
                if (count($bundle_products) > 0) {
                    return $this->add_bundle($products_id, $bundle_products, $qty, $attributes, $notify, 'linked');
                }
            }

            $normalized = false;
            if ($ext = \common\helpers\Extensions::isAllowed('SupplierPurchase')) {
                $products_id = $ext::make_uprid($ext::getSupplierFromUprid($original_products_id) ? $original_products_id : $products_id, $attributes);
                $products_id = $ext::normalize_id($products_id);
                $normalized = true;
            }
            if (!$normalized){
                $products_id = InventoryHelper::get_uprid($products_id, $attributes);
                $products_id = InventoryHelper::normalize_id($products_id);
            }

            // update cart
            if (empty($props) && isset($this->contents[$original_products_id]['props'])) {
                $props = $this->contents[$original_products_id]['props'];
            }
            // update cart
            if ( !empty($props) ) {
                $products_id = Yii::$app->get('PropsHelper')::cartUprid($products_id, $props);
            }

            if ($qty == 0) {
                return $this->remove($products_id);
            }
            if ($notify == true) {
                $new_products_id_in_cart = $products_id;
                tep_session_register('new_products_id_in_cart');
            }

            \common\components\google\widgets\GoogleTagmanger::setEvent('addToCart');

            if ($this->in_cart($products_id) && ((int)$gaw_id == 0)) {
                if (is_null($gift_wrap))
                    $gift_wrap = $this->is_gift_wrapped($products_id);
                $this->update_quantity($products_id, $packQty, $attributes, $gift_wrap);
                if (\frontend\design\Info::isTotallyAdmin()) {
                    $this->contents[$products_id]['props'] = $props;
                }
                //$allow_auto_GAW = false;
            } else {
                $qty = \common\helpers\Product::filter_product_order_quantity($products_id, $qty);

                if (is_null($gift_wrap))
                    $gift_wrap = false;
//      $this->contents[] = array($products_id);
                if (is_array($packQty)) {
                    $this->contents[$products_id] = array('qty' => $qty, 'parent' => '', 'unit' => $packQty['unit'], 'pack_unit' => $packQty['pack_unit'], 'packaging' => $packQty['packaging'], 'is_pack' => 1);
                } else {
                    $this->contents[$products_id] = array('qty' => $qty, 'parent' => '');
                }
                if ($gift_wrap && \common\helpers\Gifts::allow_gift_wrap($products_id)) {
                    $this->contents[$products_id]['gift_wrap'] = 1;
                }
                if (is_array($attributes)) {
                    $this->contents[$products_id]['attributes'] = $attributes;
                }
                if (!empty($props)) {
                    $props = Yii::$app->get('PropsHelper')::onCartAdd($props);
                    $this->contents[$products_id]['props'] = $props;
                }

                $this->contents[$products_id]['platform_id'] = \common\classes\platform::currentId();

                // insert into db
                if (!Yii::$app->user->isGuest) {
                    CustomersBasket::saveProduct([ $products_id => $this->contents[$products_id] ], $customer_id, $this);
                }

            }
// {{
        } // end if ($gaw_id == 1) else
// }}

        $this->cleanup();

        if (!empty($props)) {
            Yii::$app->get('PropsHelper')::cartChanged($this);
        }

        // assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
        $this->cartID = $this->generate_cart_id();

        //$this->update_basket_info();

        if (((int)$gaw_id == 0) /*&& $allow_auto_GAW*/) {
            $this->auto_giveaway();
        }

        $this->update_customer_info();

        $this->check_giveaway();

        foreach (\common\helpers\Hooks::getList('shopping-cart/add-cart') as $filename) {
            include($filename);
        }

        if ((int)$gaw_id > 0) {
            return $this->in_giveaway($products_id) ? $products_id : false;
        } else {
            return $this->in_cart($products_id) ? $products_id : false;
        }
    }

    public function updateProps($products_id, $props)
    {
        if ( !$this->in_cart($products_id) ) return false;
        $this->contents[$products_id]['props'] = $props;
        if (!Yii::$app->user->isGuest) {
            CustomersBasket::saveProduct([ $products_id => $this->contents[$products_id] ], Yii::$app->user->getId(), $this);
        }

        Yii::$app->get('PropsHelper')::cartChanged($this);

        return true;
    }


    public function cart_allow_giftwrap() {
        if (is_array($this->contents)) {
            foreach ($this->contents as $key => $product) {
                if (\common\helpers\Gifts::allow_gift_wrap($key)) {
                    return true;
                    break;
                }
            }
        }
        return false;
    }

    function update_basket_info() {
        global $languages_id;

        /*if (\frontend\design\Info::isTotallyAdmin())
            return;*/
        if ($this->language_id != $languages_id) {
            $this->language_id = $languages_id;
        }
        $currency = \Yii::$app->settings->get('currency');
        if (!$this->currency != $currency) {
            $this->currency = $currency;
        }

        $customer_id = Yii::$app->user->getId();

        if ($this->basketID && $customer_id) {
            tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set basket_id = '" . (int) $this->basketID . "', language_id = '" . (int) $this->language_id . "', currency = '" . tep_db_input($this->currency) . "' where customers_id = '" . (int) $customer_id . "'");

            // do not update platform_id = '" . (int) $this->platform_id . "',  - save original (from which it was added)
        }
    }

/// Don't use directly!!!
/// use ONLY add_cart instead
/// there could be problem with auto_giveaways.
    function update_quantity($products_id, $quantity = '', $attributes = '', $gift_wrap = false) {
        $customer_id = Yii::$app->user->getId();

        if (empty($quantity))
            return true; // nothing needs to be updated if theres no quantity, so we return true..
        $packQty = $quantity;
        if (is_array($quantity)) {
            $quantity = $packQty['qty'];
        }
        $quantity = \common\helpers\Product::filter_product_order_quantity($products_id, $quantity);
        $quantity = intval($quantity);
        $props = isset($this->contents[$products_id]['props'])?$this->contents[$products_id]['props']:false;
        $reserved_qty = (isset($this->contents[$products_id]['reserved_qty'])? $this->contents[$products_id]['reserved_qty']:0);
        $parent = (isset($this->contents[$products_id]['parent']) ? $this->contents[$products_id]['parent'] : '');
        $relationType = (isset($this->contents[$products_id]['relation_type']) ? $this->contents[$products_id]['relation_type'] : '');
        $platform_id = (isset($this->contents[$products_id]['platform_id']) ? $this->contents[$products_id]['platform_id'] : \common\classes\platform::currentId() );
        if (is_array($packQty)) {
            $this->contents[$products_id] = array('qty' => $quantity, 'parent' => $parent, 'reserved_qty' => $reserved_qty, 'relation_type' => $relationType, 'unit' => $packQty['unit'], 'pack_unit' => $packQty['pack_unit'], 'packaging' => $packQty['packaging'], 'is_pack' => 1);
        } else {
            $this->contents[$products_id] = array('qty' => $quantity, 'parent' => $parent, 'reserved_qty' => $reserved_qty, 'relation_type' => $relationType);
        }
        if ( $props!==false ) {
            $this->contents[$products_id]['props'] = $props;
        }
        if ($gift_wrap && \common\helpers\Gifts::allow_gift_wrap($products_id)) {
            $this->contents[$products_id]['gift_wrap'] = 1;
        }

        if (is_array($attributes)) {
           $this->contents[$products_id]['attributes'] = $attributes;
        }

        $this->contents[$products_id]['platform_id'] = $platform_id;

        // update database
        if (!Yii::$app->user->isGuest) {
            CustomersBasket::saveProduct([ $products_id => $this->contents[$products_id] ], $customer_id, $this);
        }

        // will be done in add_cart, second call cause error message. $this->auto_giveaway();
        // will be done in add_cart $this->check_giveaway();
    }

    function cleanup() {


        //unset($this->products_array);
        $this->products_array = [];
        $this->total_virtual = 0;
        $this->total = 0;
        $this->total_ex_tax = 0;
        $this->weight = 0;
        $this->max_width = 0;
        $this->max_length = 0;
        $this->max_height = 0;

        foreach (\common\helpers\Hooks::getList('shopping-cart/cleanup/before') as $filename) {
            include($filename);
        }

        if (is_array($this->contents)) {
            $user = Yii::$app->user->getIdentity();
            if ($user){
                $customer_groups_id = $user->get('customer_groups_id');
            }

            if (!$this->ignoreHooks) {
                foreach (\common\helpers\Hooks::getList('shopping-cart/cleanup') as $filename) {
                    include($filename);
                }
            }

            foreach (array_reverse($this->contents, true) as $key => $value) {
    // {{
                $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
                //For Amount Product
                if((int)$key === 0){
                    continue;
                }

                $products2c_join = '';
                if (!defined('SHOPPING_CART_SHARE') || SHOPPING_CART_SHARE != 'True') {
                  if ($this->platform_id && \common\classes\platform::isMulti() ) {
                    $products2c_join .= " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . $this->platform_id . "' " .
                            " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id " .
                            " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . $this->platform_id . "' ";
                  }
                }

                if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                    $product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p {$products2c_join} " . "  left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int) $customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)\Yii::$app->settings->get('currency_id') : 0) . "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp'), false) . " " . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1) and p.products_id = '" . (int) $key . "'");
                } else {
                    $product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p {$products2c_join} " . " where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp'), false) . " " . " and p.products_id = '" . (int) $key . "'");
                }
                $product_check = tep_db_fetch_array($product_check_query);
                if ($product_check['total'] < 1 && !tep_not_null($this->contents[$key]['parent'])) {
                    $this->contents[$key]['qty'] = 0;
                }
                if (tep_not_null($this->contents[$key]['parent']) && !isset($this->contents[$this->contents[$key]['parent']])) {
                    $this->contents[$key]['qty'] = 0; // No parent - clear
                }
                if (\common\helpers\Extensions::isAllowed('Inventory')) {
                    if (!\common\helpers\Inventory::isValidVariant($key)) {
                        $this->contents[$key]['qty'] = 0; // bad uprid - clear
                    }
                }
    // }}
                if (defined('MAX_PRODUCT_PRICE') && MAX_PRODUCT_PRICE > 0) {
                    $currencies = \Yii::$container->get('currencies');
                    $newItemPrice = \common\helpers\Product::get_products_price($key, $value['qty']);
                    $products_tax_class_id = \common\helpers\Product::get_products_info($key, 'products_tax_class_id');
                    $newFullPrice = $currencies->calculate_price($newItemPrice, \common\helpers\Tax::get_tax_rate($products_tax_class_id), $value['qty']);
                    if ($newFullPrice > (int)MAX_PRODUCT_PRICE) {
                        $this->contents[$key]['qty'] = 0;
                    }
                }
                if (defined('TEMPORARY_STOCK_ENABLE') && TEMPORARY_STOCK_ENABLE == 'true') {
                    if ( is_null($this->config_temporary_stock_enable) || $this->config_temporary_stock_enable===true ) {
                        \common\helpers\Warehouses::update_customers_temporary_stock_quantity($key, $this->contents[$key]['qty']);
                    }
                }
                if ($this->contents[$key]['qty'] < 1) {
                    unset($this->contents[$key]);
                    // remove from database
                    if (!Yii::$app->user->isGuest) {
                        CustomersBasket::deleteProduct(Yii::$app->user->getId(), $key);
                    }
                }
            }
        }

        foreach (\common\helpers\Hooks::getList('shopping-cart/cleanup/after') as $filename) {
            include($filename);
        }
    }

    function count_contents() {  // get total number of items in cart
        $total_items = 0;
        if (is_array($this->contents)) {
            foreach ($this->contents as $products_id => $value) {
                /* PC configurator addon begin */
                if (tep_not_null(ArrayHelper::getValue($this->contents, [$products_id,'parent']))) continue;
                /* PC configurator addon end */
                $total_items += $this->get_quantity($products_id);
            }
        }

        // GA
        if (is_array($this->giveaway)) {
            foreach ($this->giveaway as $giveaway) {
                $total_items += $giveaway['qty'];
            }
        }

// {{ Virtual Gift Card
        global $languages_id;
        if ( !Yii::$app->user->isGuest || Session::getSession()->get('gift_handler') ) {
            $virtual_gift_card_query = tep_db_query(
                "select vgcb.virtual_gift_card_basket_id, vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name, c.code as currency_code " .
                "from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_CURRENCIES . " c, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p " .
                "  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int)$languages_id . "' and pd1.platform_id = '" . intval(Yii::$app->get('platform')->config()->getPlatformToDescription()) . "' " .
                "where (length(vgcb.virtual_gift_card_code) = 0 || !vgcb.activated) " .
                "  and p.products_id = vgcb.products_id and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' " .
                "  and pd.products_id = p.products_id " .
                "  and pd.language_id = '" . (int)$languages_id . "' and vgcb.currencies_id = c.currencies_id and c.status = 1 " .
                "  and " . (!Yii::$app->user->isGuest ? " vgcb.customers_id = '" . (int)Yii::$app->user->getId() . "'" : " vgcb.customers_id = '0' and vgcb.session_id = '" . Session::getSession()->get('gift_handler') . "'")
            );
            while ($virtual_gift_card = tep_db_fetch_array($virtual_gift_card_query)) {
                $total_items += 1;
            }
        }
// }}
        foreach (\common\helpers\Hooks::getList('shopping-cart/count_contents/after') as $filename) {
            include($filename);
        }
        return $total_items;
    }

    function get_quantity($products_id, $ga = 0) {
        if ($ga == 1) {
            $products_id = InventoryHelper::normalize_id($products_id);
            if (isset($this->giveaway[$products_id])) {
                return $this->giveaway[$products_id]['qty'];
            } elseif (isset($this->giveaway[InventoryHelper::get_prid($products_id)])) {
                return $this->giveaway[InventoryHelper::get_prid($products_id)]['qty'];
            } else {
                foreach ($this->giveaway as $_ga_uprid => $ga_info) {
                    if (InventoryHelper::get_prid($products_id) == InventoryHelper::get_prid($_ga_uprid)) {//InventoryHelper::get_uprid
                        return $ga_info['qty'];
                    }
                }
                return 0;
            }
        } else {
            if (isset($this->contents[$products_id])) {
                return $this->contents[$products_id]['qty'];
            } else {
                return 0;
            }
        }
    }

    function get_reserved_quantity($products_id, $ga = 0) {
        if ($ga == 1) {
            $products_id = InventoryHelper::normalize_id($products_id);
            if (isset($this->giveaway[$products_id])) {
                return $this->giveaway[$products_id]['reserved_qty'];
            } elseif (isset($this->giveaway[InventoryHelper::get_prid($products_id)])) {
                return $this->giveaway[InventoryHelper::get_prid($products_id)]['reserved_qty'];
            } else {
                foreach ($this->giveaway as $_ga_uprid => $ga_info) {
                    if (InventoryHelper::get_prid($products_id) == InventoryHelper::get_prid($_ga_uprid)) {//InventoryHelper::get_uprid
                        return $ga_info['reserved_qty'];
                    }
                }
                return 0;
            }
        } else {
            if (isset($this->contents[$products_id])) {
                return $this->contents[$products_id]['reserved_qty'] ?? 0;
            } else {
                return 0;
            }
        }
    }

    function in_cart($keys) {
        if (is_array($keys)){
            foreach($keys as &$key){
                if (isset($this->contents[$key])) unset ($key);
            }
            return $keys;
        } else if (isset($this->contents[$keys])) {
            if (isset($this->contents[$keys]['qty'])) {
                return $this->contents[$keys]['qty'];
            }
            return true;
        } else {
            return false;
        }
    }

    function in_giveaway($products_id, $qty = 0, $gaw_id = false) {
      if (!is_array($this->giveaway)) return false;
      if ($gaw_id === false) {
        // no restriction on attributes/variations, so check by prid, not uprid.
        $ga_pids = array();
        foreach($this->giveaway as $uprid => $data) {
          $ga_pids[InventoryHelper::get_prid($uprid)] = $data['qty'];
        }
        if (isset($ga_pids[InventoryHelper::get_prid($products_id)]) ) {
          if ($qty==0 || $ga_pids[InventoryHelper::get_prid($products_id)]==$qty) {
            return true;
          } else {
            return false;
          }
        } else {
            return false;
        }
      } else {
        if (is_array($this->giveaway)) {
          foreach ($this->giveaway as $gaw) {
            if ($gaw['gaw_id'] == (int)$gaw_id) {
              return true;
            }
          }
        }
        return false;
      }
    }

    function remove($products_id) {
        global $last_removed;

        /* PC configurator addon begin */
        if (is_array($this->contents)) {
            $removeChildren = [];
            foreach ($this->contents as $key => $val) {
                if ($this->contents[$key]['parent'] == $products_id) {
                    $this->contents[$key]['qty'] = 0;
                    if ($this->existOwerwritten($key))
                        $this->clearOverwriten($key);
                    $removeChildren[] = $key;
                }
            }
        }
        if (empty($this->order_id)) { // don't clean up saved orders because disabled products disapear
            $this->cleanup();
        } else {
            foreach ($removeChildren as $key) {
                unset($this->contents[$key]);
            }
        }
        unset($removeChildren);
        /* PC configurator addon end */

        if (!tep_session_is_registered('last_removed')){
            tep_session_register('last_removed');
        }
        $_tmp = [
            'products_id' => $products_id,
            'data' => ArrayHelper::getValue($this->contents, $products_id),
        ];
        $last_removed = base64_encode(serialize($_tmp));
        \common\components\google\widgets\GoogleTagmanger::setEvent('removeFromCart');

        unset($this->contents[$products_id]);
        if ($this->existOwerwritten($products_id))
            $this->clearOverwriten($products_id);

        // remove from database
        $customer_id = Yii::$app->user->getId();
        CustomersBasket::deleteProduct($customer_id, $products_id);

// {{ Virtual Gift Card
        if (preg_match("/(\d+)\{0\}(\d+)/", $products_id, $arr)) {
            tep_db_query("delete from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " where (length(virtual_gift_card_code) = 0 || !activated) and virtual_gift_card_basket_id = '" . (int) $arr[2] . "' and products_id = '" . (int) $arr[1] . "' and " . ($customer_id > 0 ? " customers_id = '" . (int) $customer_id . "'" : " session_id = '" . Session::getSession()->get('gift_handler') . "'"));
        }
// }}
        // assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
        $this->cartID = $this->generate_cart_id();

        //$this->update_basket_info();

        $this->check_giveaway();

        if (defined('TEMPORARY_STOCK_ENABLE') && TEMPORARY_STOCK_ENABLE == 'true') {
            if ( is_null($this->config_temporary_stock_enable) || $this->config_temporary_stock_enable===true ) {
                //\common\helpers\Product::remove_customers_temporary_stock_quantity($products_id);
                \common\helpers\Warehouses::remove_customers_temporary_stock_quantity($products_id);
            }
        }
        Yii::$app->get('PropsHelper')::cartChanged($this);

        foreach (\common\helpers\Hooks::getList('shopping-cart/remove') as $filename) {
            include($filename);
        }
    }

    function remove_giveaway($products_id) {
        $products_id = InventoryHelper::normalize_id($products_id);
        $_remove_uprid = '';
        if (isset($this->giveaway[$products_id])) {
            unset($this->giveaway[$products_id]);
            $_remove_uprid = $products_id;
        } elseif (isset($this->giveaway[InventoryHelper::get_prid($products_id)])) {
            unset($this->giveaway[InventoryHelper::get_prid($products_id)]);
            $_remove_uprid = InventoryHelper::get_prid($products_id);
        } else {
            foreach (array_keys($this->giveaway) as $_ga_uprid) {
                if (InventoryHelper::get_prid($_ga_uprid) == InventoryHelper::get_prid($products_id)) {
                    unset($this->giveaway[$_ga_uprid]);
                    $_remove_uprid = $_ga_uprid;
                    break;
                }
            }
        }

        if (!empty($_remove_uprid) && !Yii::$app->user->isGuest) {
            CustomersBasket::deleteProduct(Yii::$app->user->getId(), $_remove_uprid, 1);
        }
        // assign a temporary unique ID to the order contents to prevent hack attempts during the checkout procedure
        $this->cartID = $this->generate_cart_id();

        //$this->update_basket_info();
    }

    function remove_all() {
        $this->reset();
    }

    function get_product_id_list() {
        $product_id_list = '';
        if (is_array($this->contents)) {
            foreach ($this->contents as $products_id => $val) {
                $product_id_list .= ', ' . $products_id;
            }
        }

        return substr($product_id_list, 2);
    }

    function calculate() {
        global $languages_id;

        $currencies = \Yii::$container->get('currencies');
        $currency = \Yii::$app->settings->get('currency');
        /*
          if ($this->total != 0){
          return;
          }
         */
        $this->weight_virtual = 0;
        $this->total_virtual = 0;
        $this->total = 0;
        $this->total_ex_tax = 0;
        $this->weight = 0;
        $this->max_width = 0;
        $this->max_length = 0;
        $this->max_height = 0;
        $this->volume = 0;
        $products_price = $products_tax = 0;
        if (!is_array($this->contents))
            return 0;

        $customer_id = Yii::$app->user->getId();
        // Collections addon
        $this->collections = [];
        if ($collections = \common\helpers\Acl::checkExtensionAllowedClass('ProductsCollections', 'helpers\Collections')) {
            $this->collections = $collections::get_collections_from_shopping_cart($this->contents);
        }

        foreach ($this->contents as $products_id => $val) {
            $qty = $this->contents[$products_id]['qty'];

            // products price
            $product_query = tep_db_query("select products_id, products_price, products_tax_class_id, products_price_full, products_weight, products_file, length_cm, width_cm, height_cm, bundle_volume_calc, is_bundle from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
            if ($product = tep_db_fetch_array($product_query)) {
                // ICW ORDER TOTAL CREDIT CLASS Start Amendment
                $no_count = 1;
                $gv_query = tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                $gv_result = tep_db_fetch_array($gv_query);
                if (preg_match('/^GIFT/', $gv_result['products_model'])) {
                    $no_count = 0;
                }
                // ICW ORDER TOTAL  CREDIT CLASS End Amendment
                $prid = $product['products_id'];
                $products_tax = Tax::get_tax_rate($product['products_tax_class_id']);

                $configurator_koeff = 1;
                $linked_product = (!empty($this->contents[$products_id]['relation_type']) && $this->contents[$products_id]['relation_type']=='linked');
                if ($this->contents[$products_id]['parent'] != '') {
                    if ($linked_product) continue;
                    $check_parent = \common\models\Products::find()->select('products_pctemplates_id')->where('products_id=:products_id', [':products_id' => \common\helpers\Inventory::get_prid($this->contents[$products_id]['parent'])])->asArray()->one();
                    if ($check_parent['products_pctemplates_id']) {
                        // if parent is configurator
                        $priceInstance = \common\models\Product\ConfiguratorPrice::getInstance($products_id);
                        $_qty = $this->getQty($products_id, false, true);
                        $products_price = $priceInstance->getConfiguratorPrice(['qty' => $_qty ]);
                        $special_price = $priceInstance->getConfiguratorSpecialPrice(['qty' => $_qty ]);
                        if ($special_price !== false) {
                            $products_price = $special_price;
                        }
                    } else {
                        $_qty = $this->getQty($products_id, false, true);
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                            $bundle_products = $ext::getBundleProducts(\common\helpers\Inventory::get_prid($this->contents[$products_id]['parent']), \common\classes\platform::currentId());
                            if (count($bundle_products) > 0 && $bundle_products[\common\helpers\Inventory::get_prid($products_id)] > 0) {
                                // if parent is bundle
                                $priceInstance = \common\models\Product\BundlePrice::getInstance($products_id);
                                $products_price = $priceInstance->getBundlePrice(['qty' => $_qty, 'parent' => $this->contents[$products_id]['parent']]);
                                $special_price = $priceInstance->getBundleSpecialPrice(['qty' => $_qty, 'parent' => $this->contents[$products_id]['parent']]);
                                if ( $special_price !== false) {
                                  $_spd = $priceInstance->getSpecialPriceDetails(['qty' => $_qty, 'parent' => $this->contents[$products_id]['parent']]);
                                  if (\common\helpers\Specials::qtyInRange($_spd, $qty)!==false) { //could be null
                                    $specials_id = $_spd['specials_id']??0;
                                  } else {
                                    $special_price = false;
                                  }
                                }
                                if ( $special_price !== false) {
                                    $products_price = $special_price;
                                }
                            }
                        }
                        if ($products_price === false){
                            $priceInstance = \common\models\Product\Price::getInstance($products_id);
                            $products_price = $priceInstance->getInventoryPrice(['qty' => $_qty]);
                            $special_price = $priceInstance->getInventorySpecialPrice(['qty' => $_qty]);
                            if ( $special_price !== false) {
                              $_spd = $priceInstance->getSpecialPriceDetails(['qty' => $_qty]);
                              if (\common\helpers\Specials::qtyInRange($_spd, $qty)!==false) { //could be null
                                $specials_id = $_spd['specials_id']??0;
                              } else {
                                $special_price = false;
                              }
                            }
                            if ( $special_price !== false) {
                                $products_price = $special_price;
                            }
                        }
                    }
                } else {
                    $priceInstance = \common\models\Product\Price::getInstance($products_id)->updateUprid($products_id);
                    $_qty = $this->getQty($products_id, false, true);
                    $products_price = $priceInstance->getInventoryPrice(['qty' => $_qty]);
                    $special_price = $priceInstance->getInventorySpecialPrice(['qty' => $_qty]);
                    if ( $special_price !== false) {
                      $_spd = $priceInstance->getSpecialPriceDetails(['qty' => $_qty]);
                      if (\common\helpers\Specials::qtyInRange($_spd, $qty)!==false) { //could be null
                        $specials_id = $_spd['specials_id']??0;
                      } else {
                        $special_price = false;
                      }
                    }
                    if ($special_price !== false) {
                        $products_price = $special_price;
                    }
                }

///2do fix  attribute weight exists!!!!
                if ($product['products_file'] == '') {
// {{ Products Bundle Sets
          /** @var \common\extensions\ProductBundles\ProductBundles $ext*/
                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                        $products_weight = $ext::getWeight($product);
                        $products_volume = $ext::getVolume($product);
                    } else {
                        $products_weight = $product['products_weight'];
                        $products_volume = $product['length_cm'] * $product['width_cm'] * $product['height_cm'];
                    }
// }}
                } else {
                    $products_weight = 0;
                    $products_volume = 0;
                }

                if (\common\helpers\Extensions::isAllowed('Inventory') && !InventoryHelper::disabledOnProduct($products_id)){
                    $simpleUprid = InventoryHelper::normalize_id($products_id);
                    if (($inventory_weight = InventoryHelper::get_inventory_weight_by_uprid($simpleUprid)) > 0) {
                        $products_weight += $inventory_weight;
                    }
                } else {
                    if (isset($this->contents[$products_id]['attributes'])) {
                        reset($this->contents[$products_id]['attributes']);
                        if (is_array($this->contents[$products_id]['attributes'])) {
                            foreach ($this->contents[$products_id]['attributes'] as $option => $value) {
                                $option_arr = explode('-', $option);
                                $attribute_price_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix, products_attributes_weight, products_attributes_weight_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) (($option_arr[1]??0) > 0 ? $option_arr[1] : $prid) . "' and options_id = '" . (int) $option_arr[0] . "' and options_values_id = '" . (int) $value . "'");
                                $attribute_price = tep_db_fetch_array($attribute_price_query);
                                if (tep_not_null($attribute_price['products_attributes_weight'])) {
                                    if ($attribute_price['products_attributes_weight_prefix'] == '+' || $attribute_price['products_attributes_weight_prefix'] == '') {
                                        $products_weight += $qty * $attribute_price['products_attributes_weight'];
                                    } else {
                                        $products_weight -= $qty * $attribute_price['products_attributes_weight'];
                                    }
                                }
                            }
                        }
                    }
                }

                if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                    if ($pack_units_data = $ext::getProductsCartFrontend($products_id, $this->contents)) {
                        $products_price = $pack_units_data['price'];
                    }
                }

                $this->total_virtual += $currencies->calculate_price($products_price, $products_tax, $qty * $no_count); // ICW CREDIT CLASS;
                $this->weight_virtual += ($qty * $products_weight) * $no_count; // ICW CREDIT CLASS;
                $this->weight += ($qty * $products_weight);
                $this->volume += ($qty * $products_volume);

                $this->max_width = max($product['width_cm'], $this->max_width);
                $this->max_length = max($product['length_cm'], $this->max_width);
                $this->max_height = max($product['height_cm'], $this->max_width);
            }


            $attributes_price = 0;

// {{ Collections addon
            if (is_array($this->collections) && $this->contents[$products_id]['parent'] == '') {
                foreach ($this->collections as $collection) {
                    foreach ($collection['products'] as $prid) {
                        if ($prid == InventoryHelper::get_prid($products_id)) {
                            $products_price *= (100 - $collection['discount']) / 100;
                            $attributes_price *= (100 - $collection['discount']) / 100;
                            break 2;
                        }
                    }
                }
            }
// }}

            $this->total += $currencies->calculate_price(($products_price + $attributes_price), $products_tax, $qty);
            $this->total_ex_tax += $currencies->calculate_price(($products_price + $attributes_price), 0, $qty);
        }

// {{ Virtual Gift Card
        if ($customer_id > 0 || Session::getSession()->get('gift_handler')){
            $virtual_gift_card_query = tep_db_query("select vgcb.virtual_gift_card_basket_id, vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name, c.code as currency_code from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_CURRENCIES . " c, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.platform_id = '" . intval(Yii::$app->get('platform')->config()->getPlatformToDescription()) . "' where (length(vgcb.virtual_gift_card_code) = 0 || !vgcb.activated) and p.products_id = vgcb.products_id and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and vgcb.currencies_id = c.currencies_id and c.status = 1 and " . ($customer_id > 0 ? " vgcb.customers_id = '" . (int) $customer_id . "'" : " vgcb.customers_id = '0' and vgcb.session_id = '" . Session::getSession()->get('gift_handler') . "'"));
            while ($virtual_gift_card = tep_db_fetch_array($virtual_gift_card_query)) {
                $virtual_gift_card['products_price'] *= $currencies->get_market_price_rate($virtual_gift_card['currency_code'], $currency);
                $this->total_virtual += $currencies->calculate_price($virtual_gift_card['products_price'], Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']));
                $this->total += $currencies->calculate_price($virtual_gift_card['products_price'], Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']));
                $this->total_ex_tax += $currencies->calculate_price($virtual_gift_card['products_price'], 0);
            }
        }
// }}
    }

    function have_gift_wrap_products() {
        if (!is_array($this->contents))
            return false;
        foreach (array_unique(array_map('intval', array_keys($this->contents))) as $_check_pid) {
            if (\common\helpers\Gifts::allow_gift_wrap($_check_pid)) {
                return true;
            }
        }
        return false;
    }

    function get_gift_wrap_amount() {
        if (!is_array($this->contents))
            return 0;
        $gift_wrap_amount = 0;
        foreach ($this->contents as $products_id => $value) {
            if (!$this->is_gift_wrapped($products_id))
                continue;
            $gift_wrap_price = \common\helpers\Gifts::get_gift_wrap_price($products_id);
            if ($gift_wrap_price !== false) {
                if (defined('MODULE_ORDER_TOTAL_GIFT_WRAP_EACH_PRODUCT') && MODULE_ORDER_TOTAL_GIFT_WRAP_EACH_PRODUCT == 'True') {
                    $gift_wrap_price *= $value['qty'];
                }
                $gift_wrap_amount += $gift_wrap_price;
            }
        }
        return $gift_wrap_amount;
    }

    function is_gift_wrapped($products_id) {
        return (isset($this->contents[$products_id]['gift_wrap']) && $this->contents[$products_id]['gift_wrap']);
    }

    function get_contents(){
        return (array)$this->contents;
    }

    function get_products($selected_products_id = null) {

        $currencies = \Yii::$container->get('currencies');
        $currency = \Yii::$app->settings->get('currency');
        $languages_id = \Yii::$app->settings->get('languages_id');

        $virtual_gift_cards = [];
        if (!Yii::$app->user->isGuest || Session::getSession()->get('gift_handler')) {
            $virtual_gift_card_query = tep_db_query("SELECT vgcb.virtual_gift_card_basket_id, vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) AS products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_discount_price, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name, c.code AS currency_code FROM " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_CURRENCIES . " c, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd1 ON pd1.products_id = p.products_id AND pd1.language_id = '" . (int)$languages_id . "' AND pd1.platform_id = '" . intval(Yii::$app->get('platform')->config()->getPlatformToDescription()) . "' WHERE (length(vgcb.virtual_gift_card_code) = 0 || !vgcb.activated) AND p.products_id = vgcb.products_id AND pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' AND pd.products_id = p.products_id AND pd.language_id = '" . (int)$languages_id . "' AND vgcb.currencies_id = c.currencies_id AND c.status = 1 AND " . (!Yii::$app->user->isGuest ? " vgcb.customers_id = '" . (int)Yii::$app->user->getId() . "'" : " vgcb.customers_id = '0' and vgcb.session_id = '" . Session::getSession()->get('gift_handler') . "'"));
            if (tep_db_num_rows($virtual_gift_card_query) > 0) {
                while ($_virtual_gift_card = tep_db_fetch_array($virtual_gift_card_query)) {
                    $virtual_gift_cards[] = $_virtual_gift_card;
                }
            }
        }


        if (!is_array($this->contents) && !is_array($this->giveaway))
            return false;

        if (!is_array($this->products_array))
            $this->products_array = [];

        static $last_products_array_cache_key = '';
        $current_products_array_cache_key = sha1(json_encode(array_merge($this->contents, $this->giveaway, $virtual_gift_cards)));
        if ( empty($selected_products_id) && !empty($this->products_array) ) {
            if ($last_products_array_cache_key == $current_products_array_cache_key && !\frontend\design\Info::isTotallyAdmin()) {
                if (!(Yii::$app->params['reset_static_product_prices_cache'] ?? false)) return array_values($this->products_array);
            }
        }

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        // Collections addon
        $this->collections = [];
        if ($collections = \common\helpers\Acl::checkExtensionAllowedClass('ProductsCollections', 'helpers\Collections')) {
            $this->collections = $collections::get_collections_from_shopping_cart($this->contents);
        }

        $products_array = array();
        $container = \Yii::$container->get('products');
        foreach ($this->contents as $products_id => $val) {
            if (!is_null($selected_products_id) && $selected_products_id != $products_id) continue;

            if($this->isAmount($products_id)){
                $amountData = $this->productAmountData($products_id);
                if($amountData !== false){
                    $products_array[$products_id] = $amountData;
                }
                continue;
            }

            $products_query = tep_db_query("select p.products_id, p.is_virtual, p.stock_indication_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, IF(p.is_listing_product AND p.products_status, 1, 0) AS _status, p.products_model, p.products_image, p.products_price, p.products_price_full, p.products_weight, p.products_tax_class_id, p.products_file, p.subscription, p.subscription_code, p.bonus_points_price, p.bonus_points_cost, p.length_cm, p.width_cm, p.height_cm, p.bundle_volume_calc, p.is_bundle from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int) $languages_id . "' and pd1.platform_id = '" . intval(Yii::$app->get('platform')->config()->getPlatformToDescription()) . "' " . " where p.products_id = '" . (int) $products_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.products_id = p.products_id " . " and pd.language_id = '" . (int) $languages_id . "'");
            if ($products = tep_db_fetch_array($products_query)) {
                $cProduct = $container->getProduct($products_id);
                if (!$cProduct) {
                    $data = $products;
                    $data['products_id'] = $products_id;
                    $data['listing_type'] = 'cart';
                    $cProduct = $container->loadProducts($data)->getProduct($products_id);
                }
                $prid = $products['products_id'];
                $uprid = InventoryHelper::normalize_id($products_id);
                $products['products_weight'] = $cProduct->getProductWeight($uprid);
                $stock_products_id = InventoryHelper::normalizeInventoryId($products_id);
                $configurator_koeff = 1;
                $qty = $this->getQty($products_id, false, true);
                $swProduct = false;$products_price=false;$standard_price=false;
                $linked_product = (!empty($this->contents[$products_id]['relation_type']) && $this->contents[$products_id]['relation_type']=='linked');
                if (ArrayHelper::getValue($this->contents, [$products_id, 'parent']) != '') {
                    $check_parent = \common\models\Products::find()->select('products_pctemplates_id')->where('products_id=:products_id', [':products_id' => \common\helpers\Inventory::get_prid($this->contents[$products_id]['parent'])])->asArray()->one();
                    if ($check_parent['products_pctemplates_id']) {
                        // if parent is configurator
                        $priceInstance = \common\models\Product\ConfiguratorPrice::getInstance($products_id);
                        $products_price = $priceInstance->getConfiguratorPrice(['qty' => $qty]);
                        $special_price = $priceInstance->getConfiguratorSpecialPrice(['qty' => $qty]); //not yet implemented (false)
                        if ($special_price !== false) {
                            $_spd = $priceInstance->getSpecialPriceDetails(['qty' => $qty]);
                            $specials_id = $_spd['specials_id']??0;
                            $standard_price = $products_price;
                            $products_price = $special_price;
                        } else {
                            if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && $products['products_price'] > $products_price) {
                                $standard_price = $products['products_price'];
                            }
                        }
                    }elseif ( $linked_product ){
                        $products_price = 0;
                    } else {
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                            $bundle_products = $ext::getBundleProducts(\common\helpers\Inventory::get_prid($this->contents[$products_id]['parent']), \common\classes\platform::currentId());
                            if (count($bundle_products) > 0 && $bundle_products[\common\helpers\Inventory::get_prid($products_id)] > 0) {
                                // if parent is bundle
                                $priceInstance = \common\models\Product\BundlePrice::getInstance($products_id);
                                $products_price = $priceInstance->getBundlePrice(['qty' => $qty, 'parent' => $this->contents[$products_id]['parent']]);
                                $special_price = $priceInstance->getBundleSpecialPrice(['qty' => $qty, 'parent' => $this->contents[$products_id]['parent']]);
                                if ( $special_price !== false) {
                                  $_spd = $priceInstance->getSpecialPriceDetails(['qty' => $qty]);
                                  if (\common\helpers\Specials::qtyInRange($_spd, $qty)!==false) { //could be null
                                    $specials_id = $_spd['specials_id']??0;
                                  } else {
                                    $special_price = false;
                                  }
                                }
                                if ( $special_price !== false) {
                                    $standard_price = $products_price;
                                    $products_price = $special_price;
                                } else {
                                    if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && $products['products_price'] > $products_price) {
                                        $standard_price = $products['products_price'];
                                    }
                                }
                            }
                        }
                        if ($products_price === false){
                            $priceInstance = \common\models\Product\Price::getInstance($products_id);
                            $products_price = $priceInstance->getInventoryPrice(['qty' => $qty]);
                            $special_price = $priceInstance->getInventorySpecialPrice(['qty' => $qty]);
                            if ( $special_price !== false) {
                              $_spd = $priceInstance->getSpecialPriceDetails(['qty' => $qty]);
                              if (\common\helpers\Specials::qtyInRange($_spd, $qty)!==false) { //could be null
                                $specials_id = $_spd['specials_id']??0;
                              } else {
                                $special_price = false;
                              }
                            }
                            if ( $special_price !== false) {
                                $standard_price = $products_price;
                                $products_price = $special_price;
                            } else {
                                if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && $products['products_price'] > $products_price) {
                                    $standard_price = $products['products_price'];
                                }
                            }
                        }
                    }
                } else {
                  /** @var \common\extensions\SupplierPurchase\SupplierPurchase $ext */
                    if ($ext = \common\helpers\Extensions::isAllowed('SupplierPurchase')){
                        $swProduct = $ext::getWSProduct($products_id, \common\classes\platform::currentId());
                        if ($swProduct){
                            $products_price = $swProduct->getProductPrice();
                            $special_price = false;
                            $products['products_name'] = $swProduct->getProductName();
                        }
                    }
                    if ($products_price === false){
                        $priceInstance = \common\models\Product\Price::getInstance($products_id);
                        $products_price = $priceInstance->getInventoryPrice(['qty' => $qty]);
                        $special_price = $priceInstance->getInventorySpecialPrice(['qty' => $qty]);

                        $_spd = false;
                        if ( $special_price !== false) {
                          $_spd = $priceInstance->getSpecialPriceDetails(['qty' => $qty]);

                          if (\common\helpers\Specials::qtyInRange($_spd, $qty)!==false) { //could be null
                            $specials_id = $_spd['specials_id']??0;
                          } else {
                            $special_price = false;
                          }
                        }
                        if ( $special_price !== false) {
                            $standard_price = $products_price;
                            $products_price = $special_price;
                        } else {
                            if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && $products['products_price'] > $products_price) {
                                $standard_price = $products['products_price'];
                            }
                        }
                    }
                }

                $gift_wrap_allowed = false;
                $gift_wrap_price = \common\helpers\Gifts::get_gift_wrap_price($products_id);
                $gift_wrapped = false;
                if ($gift_wrap_price !== false) {
                    $gift_wrap_allowed = true;
                    $gift_wrapped = $this->is_gift_wrapped($products_id);
                    if (defined('MODULE_ORDER_TOTAL_GIFT_WRAP_EACH_PRODUCT') && MODULE_ORDER_TOTAL_GIFT_WRAP_EACH_PRODUCT == 'True') {
                        $gift_wrap_price *= $val['qty'];
                    }
                } else {
                    $gift_wrap_price = 0;
                }

                /** @var \common\extensions\Inventory\Inventory $ext */
                if ($ext = \common\helpers\Extensions::isAllowed('Inventory')) {
                    $products = array_replace($products, $ext::getInventorySettings((int)$stock_products_id, $stock_products_id));
                }
/*              // bundle products are included to shopping cart, so their weight is calculated separately
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                    $products['products_weight'] =  $ext::getAdditionalWeight($products, $this->platform_id);
                }
*/
                if ($swProduct){
                    $stock_info = $swProduct->getStock();
                } else {
                    /** @var \common\extensions\ProductBundles\ProductBundles $ext */
                    if ($products['is_bundle'] && ($ext = \common\helpers\Extensions::isAllowed('ProductBundles')) ) {
                        $stock_info = \common\classes\StockIndication::product_info($ext::getStockIndicationData(array(
                            'products_id' => $products_id,
                            'stock_indication_id' => $products['stock_indication_id'],
                            'products_quantity' => \common\helpers\Product::get_products_stock($products_id),
                            'cart_qty' => $this->contents[$products_id]['qty'],
                            'cart_class' => true,
                        )));
                        $stock_info['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($stock_products_id, $stock_info['max_qty'], true);
                    }else
                  /** @var \common\extensions\Inventory\Inventory $ext */
                    if ( ($ext = \common\helpers\Extensions::isAllowed('Inventory')) && !InventoryHelper::disabledOnProduct($stock_products_id) ) {
                        $stock_info = $ext::getInventoryStock($stock_products_id, $products['stock_indication_id'], $this->contents[$products_id]['qty']);
                    } else {
                        $stock_info = \common\classes\StockIndication::product_info(array(
                                    'products_id' => $stock_products_id,
                                    'stock_indication_id' => $products['stock_indication_id'],
                                    'cart_qty' => $this->contents[$products_id]['qty'],
                                    'cart_class' => true,
                                    'products_quantity' => \common\helpers\Product::get_products_stock($stock_products_id),
                        ));
                        $stock_info['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($stock_products_id, $stock_info['max_qty'], true);
                    }

                    if (false && \common\helpers\Acl::checkExtensionAllowed('ProductBundles') && $products['is_bundle'] && empty($val['attributes'])) {
                      /// bundles kostyl' for now
                      $tmp = (isset($val['attributes']) && is_array($val['attributes']))?$val['attributes']:[];
                      $attributes_details = \common\helpers\Attributes::getDetails($products_id, $tmp, []);
                      $details = \common\helpers\Bundles::getDetails($products, $attributes_details);

                      $stock_info = $details['stock_indicator'];
                    }

                }
                $attributes_price = 0;

                /*if (isset($this->contents[$products_id]['attributes'])) {
                        if (is_array($this->contents[$products_id]['attributes'])) {
                            foreach ($this->contents[$products_id]['attributes'] as $option => $value) {
                                $option_arr = explode('-', $option);
                                $attribute_price_query = tep_db_query("select products_attributes_id, products_attributes_weight, products_attributes_weight_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) ($option_arr[1] > 0 ? $option_arr[1] : (int)$prid) . "' and options_id = '" . (int) $option_arr[0] . "' and options_values_id = '" . (int) $value . "'");
                                $attribute_price = tep_db_fetch_array($attribute_price_query);

                                if (tep_not_null($attribute_price['products_attributes_weight'])) {
                                    if ($attribute_price['products_attributes_weight_prefix'] == '+' || $attribute_price['products_attributes_weight_prefix'] == '') {
                                        $this->weight += $qty * $attribute_price['products_attributes_weight'];
                                    } else {
                                        $this->weight -= $qty * $attribute_price['products_attributes_weight'];
                                    }
                                }
                            }
                        }
                    }*/

                $products_price_old = $products_price;
                //$attributes_price_old = $attributes_price;

// {{ Collections addon
                if (is_array($this->collections) && ArrayHelper::getValue($this->contents, [$products_id, 'parent']) == '') {
                    foreach ($this->collections as $collection) {
                        foreach ($collection['products'] as $prid) {
                            if ($prid == InventoryHelper::get_prid($products_id)) {
                                $products['collection'] = $collection;
                                $products_price *= (100 - $collection['discount']) / 100;
                                $attributes_price *= (100 - $collection['discount']) / 100;
                                break 2;
                            }
                        }
                    }
                }
// }}
                $explain_info = false;
                $props = false;
                if ( isset($this->contents[$products_id]['props']) && !empty($this->contents[$products_id]['props']) ) {
                    $props = Yii::$app->get('PropsHelper')::XmlToParams($this->contents[$products_id]['props']);
                    //props code here
                    $explain_info = Yii::$app->get('PropsHelper')::explainParams($props, Tax::get_tax_rate($products['products_tax_class_id']) );
                }

                $products_array[$products_id] = array(
                    'id' => $products_id,
                    'platform_id' => isset($this->contents[$products_id]['platform_id'])?$this->contents[$products_id]['platform_id']:\common\classes\platform::currentId(),
                    'name' => $products['products_name'],
                    'model' => $products['products_model'],
                    'image' => $products['products_image'],
                    '_status' => $products['_status'],
                    'gift_wrap_allowed' => $gift_wrap_allowed,
                    'gift_wrap_price' => $gift_wrap_price,
                    'gift_wrapped' => $gift_wrapped,
                    'price' => $products_price,
                    'products_file' => $products['products_file'],
                    'ga' => 0,
                    'is_virtual' => (int) $products['is_virtual'],
                    'props' => isset($this->contents[$products_id]['props'])?$this->contents[$products_id]['props']:'',
                    'propsData' => $props,
                    'explain_info' => $explain_info,
                    'quantity' => $this->contents[$products_id]['qty'],
                    'reserved_qty' => (isset($this->contents[$products_id]['reserved_qty']) ? $this->contents[$products_id]['reserved_qty'] : 0),
                    'stock_info' => $stock_info,
                    'stock_products_id' => $stock_products_id,
                    'weight' => ($products['products_weight'] /* + $this->attributes_weight($products_id) */),
                    'final_price' => ($products_price + $attributes_price /* $this->attributes_price($products_id, $this->contents[$products_id]['qty']) */),
/* PC configurator addon begin */
                    'parent' => ArrayHelper::getValue($this->contents, [$products_id, 'parent']),
                    'sub_products' => $this->get_subproducts($products_id),
                    'relation_type' => isset($this->contents[$products_id]['relation_type'])?$this->contents[$products_id]['relation_type']:'',
/* PC configurator addon end */
                    'tax_class_id' => $products['products_tax_class_id'],
                    'attributes' => (isset($this->contents[$products_id]['attributes']) ? $this->contents[$products_id]['attributes'] : []),
                    'overwritten' => $this->getOwerwritten($products_id),
                    'subscription' => $products['subscription'],
                    'subscription_code' => $products['subscription_code'],
                    'special_price' => $special_price,
                    'specials_id' => $specials_id ?? 0,
                    'standard_price' => ($standard_price !== false && $standard_price > $products_price? ($standard_price + $attributes_price) : false),
// {{ Collections addon
                    'collection' => $products['collection'] ?? '',
                    'price_old' => ($products_price_old /*+ $attributes_price_old*/),
// }}
                    'tax_rate' => Tax::get_tax_rate($products['products_tax_class_id']),
                );
                $products_array[$products_id]['length_cm'] = $products['length_cm'];
                $products_array[$products_id]['width_cm'] = $products['width_cm'];
                $products_array[$products_id]['height_cm'] = $products['height_cm'];
                $products_array[$products_id]['bundle_volume_calc'] = $products['bundle_volume_calc'];

                /** @var \common\extensions\ProductBundles\ProductBundles $ext*/
                if ($products['is_bundle'] && $ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                  $products_array[$products_id]['volume'] = $ext::getVolume($products);
                } else {
                  $products_array[$products_id]['volume'] = $products['length_cm'] * $products['width_cm'] * $products['height_cm'];
                }

// {{ Bonus Points
                /** @var \common\extensions\BonusActions\BonusActions $extBonusAction */
                if ($extBonusAction = \common\helpers\Extensions::isAllowedAnd('BonusActions', 'isProductPointsEnabled')) {
                    $bonuses = $extBonusAction::getProductBonuses($products_id, $customer_groups_id, null, $products_array[$products_id]['quantity'], $products_array[$products_id]['parent']);
                    if (is_array($bonuses)) {
                        foreach($bonuses as $keyBonus=>$valueBonus) {
                            $products_array[$products_id][$keyBonus] = $valueBonus;
                        }
                    }
                    unset($bonuses);
                }
// }}

                if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                    $replace = $ext::getProductsCartFrontend($products_id, $this->contents);
                    if ($replace){
                        $products_array[$products_id] = array_replace($products_array[$products_id], $replace);
                    }
                }//echo '<pre>';print_r($products_array);die;

                if ($this->existOwerwritten($products_id)) {
                    $this->overWrite($products_id, $products_array[$products_id]);
                    $owd = $this->getOwerwritten($products_id);
                    if (is_array($owd) && count($owd)) {
                        if (isset($owd['final_price_formula']) OR isset($owd['final_price'])) {
                            $products_array[$products_id]['final_price'] *= \common\helpers\Product::getVirtualItemQuantityValue($products_id);
                        }
                    }
                    unset($owd);
                }
                $cProduct->attachDetails($products_array[$products_id]);
            }
        }

        if ($products_array){
            /* some Promo may change prices dependently priority */
            if (\common\helpers\Acl::checkExtensionAllowed('Promotions')) {
                foreach($products_array as $products_id => $_product){
                    $promoPrice = \common\extensions\Promotions\models\Product\PromotionPrice::getInstance($_product['id']);
                    $promoPrice->setCalculateAfter(true);
                    $price = $promoPrice->getPromotionPrice();
                    if ($price !== false){
                        if (!$products_array[$products_id]['standard_price']){
                            $products_array[$products_id]['standard_price'] = $products_array[$products_id]['final_price'];
                        }
                        $products_array[$products_id]['final_price'] = $price;
                        if ($this->existOwerwritten($products_id)) {
                            $this->overWrite($products_id, $products_array[$products_id]);
                        }
                        $cProduct = $container->getProduct($products_id);
                        if($cProduct){
                            $cProduct->attachDetails($products_array[$products_id]);
                        }
                    }
                    $restructQty = $promoPrice->getRestrictQuantity();
                    if ($restructQty !== false){
                        $products_array[$products_id]['stock_info']['quantity_max'] = $restructQty;
                    }
                }
            }
            /* container is used to sync product prices */
            foreach($products_array as $products_id => $_product){
                $product = $container->getProduct($products_id);
                if ($product){
                    if (isset($product['final_price'])){
                        $products_array[$products_id]['final_price'] = $product['final_price'];
                    }
                    if (isset($product['standard_price'])){
                        $products_array[$products_id]['standard_price'] = $product['standard_price'];
                    }
                    if (isset($product['special_price'])){
                        $products_array[$products_id]['special_price'] = $product['special_price'];
                    }
                }
            }
        }


        if ($products_array){
            foreach($products_array as &$_product) {
                $_product['quantity_virtual'] = \common\helpers\Product::getVirtualItemQuantity($_product['id'], $_product['quantity']);
                $_product['quantity_virtual_item'] = \common\helpers\Product::getVirtualItemQuantityValue($_product['id']);
                if ($_product['quantity_virtual_item'] > 1) {
                    $_product['weight'] = ($_product['weight'] / $_product['quantity_virtual_item']);
                    $_product['price'] = ($_product['price'] / $_product['quantity_virtual_item']);
                    $_product['final_price'] = ($_product['final_price'] / $_product['quantity_virtual_item']);
                    if ($_product['price_old']) {
                        $_product['price_old'] = ($_product['price_old'] / $_product['quantity_virtual_item']);
                    }
                    if ($_product['special_price']) {
                        $_product['special_price'] = ($_product['special_price'] / $_product['quantity_virtual_item']);
                    }
                    if ($_product['standard_price']) {
                        $_product['standard_price'] = ($_product['standard_price'] / $_product['quantity_virtual_item']);
                    }
                    if ($_product['bonus_points_price']) {
                        $_product['bonus_points_price'] = ($_product['bonus_points_price'] / $_product['quantity_virtual_item']);
                    }
                    if ($_product['bonus_points_cost']) {
                        $_product['bonus_points_cost'] = ($_product['bonus_points_cost'] / $_product['quantity_virtual_item']);
                    }
                }
            }
            unset($_product);

            $_total = 0;
            foreach($products_array as $_product){
                $_total += $currencies->calculate_price($_product['final_price'], $_product['tax_rate'], $_product['quantity']);
            }
            $d = \common\helpers\Customer::get_additional_superdiscount(Yii::$app->user->getId(), $_total);
            if ($d > 0){
                foreach($products_array as $products_id => $_product){
                    $dSettings = new \common\components\DisabledSettings($products_id);
                    if ($dSettings->applyGroupDiscount()){
                        $products_array[$products_id]['final_price'] = $_product['final_price'] - ($_product['final_price'] * $d /100);
                        if ($this->existOwerwritten($products_id)) {
                            $this->overWrite($products_id, $products_array[$products_id]);
                        }
                        $cProduct = $container->getProduct($products_id);
                        if($cProduct){
                            $cProduct->attachDetails($products_array[$products_id]);
                        }
                    }
                }
            }
        }

        if (sizeof($this->giveaway) > 0) { // if we also have GA add them too
            foreach ($this->giveaway as $products_id => $product) {
                $products = tep_db_fetch_array(tep_db_query("select p.products_id, p.is_virtual, p.stock_indication_id, pd.products_name, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_tax_class_id, p.length_cm, p.width_cm, p.height_cm, p.bundle_volume_calc, p.is_bundle from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int) $products_id . "' and pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."'"));
                if ($ext = \common\helpers\Extensions::isAllowed('Inventory')) {
                    $products = array_replace($products, $ext::getInventorySettings($products_id, InventoryHelper::normalizeInventoryId($products_id)));
                }
                // {{ stock info
                /* $stock_info = \common\classes\StockIndication::product_info(array(
                  'products_id' => InventoryHelper::normalize_id($products_id),
                  'stock_indication_id' => $products['stock_indication_id'],
                  'cart_qty' => $this->contents[$products_id]['qty'],
                  'cart_class' => true,
                  'products_quantity' => \common\helpers\Product::get_products_stock(InventoryHelper::normalize_id($products_id)),
                  )); */
                // }} stock info
                $products_array[$products_id . '(GA)'] = array('id' => $products_id,
                    'name' => $products['products_name'],
                    'model' => $products['products_model'],
                    'image' => $products['products_image'],
                    //'stock_info' => $stock_info,
                    'gift_wrap_allowed' => false,
                    'gift_wrap_price' => 0,
                    'gift_wrapped' => false,
                    'price' => 0,
                    'ga' => 1,
                    'gaw_id' => $product['gaw_id'],
                    'is_virtual' => (int) $products['is_virtual'],
                    'quantity' => $product['qty'],
                    'weight' => $products['products_weight'],
                    'final_price' => 0,
                    'standard_price' => false,
                    'tax_class_id' => $products['products_tax_class_id'],
                    'promo_id' => $product['promo_id'],
                    'attributes' => (isset($product['attributes']) ? $product['attributes'] : ''));

                $products_array[$products_id . '(GA)']['length_cm'] = $products['length_cm'];
                $products_array[$products_id . '(GA)']['width_cm'] = $products['width_cm'];
                $products_array[$products_id . '(GA)']['height_cm'] = $products['height_cm'];
                $products_array[$products_id . '(GA)']['bundle_volume_calc'] = $products['bundle_volume_calc'];
                /** @var \common\extensions\ProductBundles\ProductBundles $ext*/
                if ($products['is_bundle'] && $ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                  $products_array[$products_id . '(GA)']['volume'] = $ext::getVolume($products);
                } else {
                  $products_array[$products_id . '(GA)']['volume'] = $products['length_cm'] * $products['width_cm'] * $products['height_cm'];
                }

            }
        }

        $this->update_final_price($products_array);

// {{ Virtual Gift Card
        foreach($virtual_gift_cards as $virtual_gift_card) {
            $virtual_gift_card['products_price'] *= $currencies->get_market_price_rate($virtual_gift_card['currency_code'], $currency);
            $virtual_gift_card['products_discount_price'] *= $currencies->get_market_price_rate($virtual_gift_card['currency_code'], $currency);
            $_price = $virtual_gift_card['products_price'];
            if ($virtual_gift_card['products_discount_price'] > 0){
                $_price = $virtual_gift_card['products_discount_price'];
            }
            $products_id = $virtual_gift_card['products_id'] . '{0}' . $virtual_gift_card['virtual_gift_card_basket_id'];
            $display_gift_card_price = $currencies->display_gift_card_price($_price, Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']), $virtual_gift_card['currency_code']);
            $display_gift_card_real_price = $currencies->display_gift_card_price($virtual_gift_card['products_price'], Tax::get_tax_rate($virtual_gift_card['products_tax_class_id']), $virtual_gift_card['currency_code']);
            $products_array[$products_id] = array('id' => $products_id,
                'use_shipwire' => 0,
                'name' => $display_gift_card_real_price . ' - ' . $virtual_gift_card['products_name'],
                'model' => $virtual_gift_card['products_model'],
                'image' => $virtual_gift_card['products_image'],
                'price' => $_price,
                'virtual_gift_card' => 1,
                'quantity' => 1,
                'is_virtual' => 1, //???
                'weight' => $virtual_gift_card['products_weight'],
                'final_price' => $_price,
                'standard_price' => false,
                'display_price' => $display_gift_card_price,
                'currency_code' => $virtual_gift_card['currency_code'],
                'tax_class_id' => $virtual_gift_card['products_tax_class_id'],
                'attributes' => array(0 => $virtual_gift_card['virtual_gift_card_basket_id']));
        }
// }}

        foreach (\common\helpers\Hooks::getList('shopping-cart/get-products') as $filename) {
            include($filename);
        }

        // Sort sub-products to be listed after parent product
        $sorted_products_array = array();
        foreach ($products_array as $prid => $prod) {
            if ($prod['parent'] == '') {
                $sorted_products_array[$prid] = $products_array[$prid];
                if (is_array($prod['sub_products']) && count($prod['sub_products']) > 0) {
                    foreach ($prod['sub_products'] as $subprid) {
                        if (!isset($products_array[$subprid])) continue;
                        $sorted_products_array[$subprid] = $products_array[$subprid];
                    }
                }
            }
        }
        $sorted_products_array = array_values($sorted_products_array);
        if (!empty($selected_products_id)) return $sorted_products_array;

        $this->products_array = $sorted_products_array;

        $this->compactLinkedProducts();

        $last_products_array_cache_key = $current_products_array_cache_key;

        return $this->products_array;
    }

    public function hasBlockedProducts(){
        $container = \Yii::$container->get('products');
        $has = false;
        foreach ($this->get_products() as $product){
            $cProduct = $container->getProduct($product['id']);
            if($cProduct){
                $has = (bool)($cProduct['block_in_cart'] ?? false) || $has;
                //?? VL if ($has) {break;}
            }
        }
        return $has;
    }

    protected function compactLinkedProducts()
    {
        if ($ext = \common\helpers\Extensions::isAllowed('LinkedProducts')) {
            $this->products_array = $ext::hookCompactLinkedProducts($this->products_array)??$this->products_array;
        }
    }


    public function get_products_calculated() {
        $this->products_array = [];
        return $this->get_products();
    }

    function update_final_price($products_array) {
        if (!Yii::$app->user->isGuest){
            if (is_array($products_array)) {
                foreach ($products_array as $products_id => $cprod) {
                    tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set final_price = '" . (float) $cprod['final_price'] . "' where customers_id = '" . (int) Yii::$app->user->getId() . "' and basket_id = '" . $this->basketID . "' and products_id = '" . tep_db_input($products_id) . "'");
                }
            }
        }

        return;
    }

    function show_total() {
        $currencies = \Yii::$container->get('currencies');
        if (!$this->products_array && $this->count_contents()){
            $this->calculate();
        } else {
            $this->total = 0;
            foreach($this->products_array as $_product){
                $this->total += $currencies->calculate_price($_product['final_price'], $_product['tax_rate'], $_product['quantity']);
            }
        }
        return $this->total;
    }

    function show_total_ex_tax() {
        $currencies = \Yii::$container->get('currencies');
        if (!$this->products_array && $this->count_contents()) {
            $this->calculate();
        } else {
            $this->total_ex_tax = 0;
            foreach($this->products_array as $_product) {
                $this->total_ex_tax += $currencies->calculate_price($_product['final_price'], 0, $_product['quantity']);
            }
        }
        return $this->total_ex_tax;
    }

    function show_weight() {
        if (!$this->products_array && $this->count_contents()){
            $this->calculate();
        } else {
            $this->weight = 0;
            foreach($this->products_array as $_product){
                $this->weight += $_product['weight'] * $_product['quantity'];
            }
        }
        return $this->weight;
    }

    function show_volume() {
        if (!$this->products_array && $this->count_contents()){
          ///do not fetch products again???
            $this->calculate();
        } else {
            $this->volume = 0;
            foreach($this->products_array as $product){
              if (empty($product['parent']) && !empty($product['volume']) ) {
                $this->volume += $product['volume'] * $product['quantity'];
              }
            }
        }

        return $this->volume/1000000;
    }

    function show_volume_weight() {
        $volume_weight = 0;
        if (defined('VOLUME_WEIGHT_COEFFICIENT') && VOLUME_WEIGHT_COEFFICIENT > 0) {
            foreach ($this->get_products() as $product) {
                $volume_weight += max($product['weight'], $product['volume'] / VOLUME_WEIGHT_COEFFICIENT) * $product['quantity'];
            }
        }
        return $volume_weight;
    }

    function showDimensions() {
        if (!$this->products_array && $this->count_contents()){
            $this->calculate();
        } else {
            $this->max_width = 0;
            $this->max_length = 0;
            $this->max_height = 0;
            foreach($this->products_array as $_product){
                $this->max_width = max($this->max_width, $_product['width_cm']??0);
                $this->max_length = max($this->max_length, $_product['length_cm']??0);
                $this->max_height = max($this->max_height, $_product['height_cm']??0);
            }
        }
        return [
            'max_width' => $this->max_width,
            'max_length' => $this->max_length,
            'max_height' => $this->max_height,
        ];
    }

    // CREDIT CLASS Start Amendment
    function show_total_virtual() {
        $this->calculate();

        return $this->total_virtual;
    }

    function show_weight_virtual() {
        $this->calculate();

        return $this->weight_virtual;
    }

    // CREDIT CLASS End Amendment

    function generate_cart_id($length = 5) {
        $random = \common\helpers\Password::create_random_value($length, 'digits');
        if( $multiCart = \common\helpers\Acl::checkExtension( 'MultiCart', 'allowed' )) {
            if ($multiCart::allowed()){ // check for existing
                $random = $multiCart::checkBasketID($random, Yii::$app->user->getId(), $length);
            }
        }
        return $random;
    }

    function get_content_type() {
        $this->content_type = false;
        $count_virtual = 0;
        $count_physical = 0;
        foreach ($this->get_products() as $product) {
            if ($product['is_virtual'] != 0) {
                $count_virtual++;
            } else {
                $count_physical++;
            }
        }
        if ($count_physical > 0 && $count_virtual == 0) {
            $this->content_type = 'physical';
        } elseif ($count_physical > 0 && $count_virtual > 0) {
            $this->content_type = 'mixed';
        } elseif ($count_physical == 0 && $count_virtual > 0) {
            $this->content_type = 'virtual';
        } else {
            $this->content_type = 'physical';
        }
        return $this->content_type;
    }

    function __get_content_type() {
        $this->content_type = false;

        if ((DOWNLOAD_ENABLED == 'true') && ($this->count_contents() > 0)) {
            if (is_array($this->contents)) {
                foreach ($this->contents as $products_id => $val) {
                    if (isset($this->contents[$products_id]['attributes']) && is_array($this->contents[$products_id]['attributes'])) {

                        $virtual_check_product_query = tep_db_query("select products_weight, products_file from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                        $virtual_check_product = tep_db_fetch_array($virtual_check_product_query);

                        foreach ($this->contents[$products_id]['attributes'] as $option => $value) {
    // {{ Products Bundle Sets
                            $option_arr = explode('-', $option);
    // }}
                            $virtual_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_ATTRIBUTES . "  where products_id = '" . (int) ($option_arr[1] > 0 ? $option_arr[1] : $products_id) . "' and options_values_id = '" . (int) $value . "' and products_attributes_filename <> ''");
                            $virtual_check = tep_db_fetch_array($virtual_check_query);

                            if ($virtual_check['total'] > 0 || $virtual_check_product['products_file'] != '') {
                                switch ($this->content_type) {
                                    case 'physical':
                                        $this->content_type = 'mixed';

                                        return $this->content_type;
                                        break;
                                    default:
                                        $this->content_type = 'virtual';
                                        break;
                                }
                            } else {
                                switch ($this->content_type) {
                                    case 'virtual':
                                        $this->content_type = 'mixed';

                                        return $this->content_type;
                                        break;
                                    default:
                                        $this->content_type = 'physical';
                                        break;
                                }
                            }
                        }
                        // ICW ADDED CREDIT CLASS - Begin
                    } elseif ($this->show_weight() == 0) {
                        if (is_array($this->contents)) {
                            foreach ($this->contents as $products_id => $val) {
                                $virtual_check_query = tep_db_query("select products_weight, products_file from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                                $virtual_check = tep_db_fetch_array($virtual_check_query);
                                if ($virtual_check['products_file'] != '') {
                                    $virtual_check['products_weight'] = 0;
                                }
                                if ($virtual_check['products_weight'] == 0) {
                                    switch ($this->content_type) {
                                        case 'physical':
                                            $this->content_type = 'mixed';

                                            return $this->content_type;
                                            break;
                                        default:
                                            $this->content_type = 'virtual';
                                            break;
                                    }
                                } else {
                                    switch ($this->content_type) {
                                        case 'virtual':
                                            $this->content_type = 'mixed';

                                            return $this->content_type;
                                            break;
                                        default:
                                            $this->content_type = 'physical';
                                            break;
                                    }
                                }
                            }
                        }
                        // ICW ADDED CREDIT CLASS - End
                    } else {
                        switch ($this->content_type) {
                            case 'virtual':
                                $this->content_type = 'mixed';
                                return $this->content_type;
                                break;
                            default:
                                $this->content_type = 'physical';
                                break;
                        }
                    }
                }
            }
        } elseif ($this->count_contents() > 0) { // GIFT addon by Senia 2008-04-21
            $this->show_weight();
            if (!is_array($this->products_array) || sizeof($this->products_array) == 0) {
                $this->get_products();
            }
            if (is_array($this->products_array) && sizeof($this->products_array) > 0) {
                $total_virtual = 0;
                foreach ($this->products_array as $pdata) {
                    if (preg_match('/^GIFT/', $pdata['model']) || $pdata['virtual_gift_card']) {
                        $total_virtual++;
                    }
                }
            }

            if ($total_virtual > 0 && $total_virtual == sizeof($this->products_array)) {
                $this->content_type = 'virtual';
            } elseif ($total_virtual > 0) {
                $this->content_type = 'mixed';
            } else {
                $this->content_type = 'physical';
            }
        } else {
            $this->content_type = 'physical';
        }

        return $this->content_type;
    }

    function unserialize($broken) {
        if (is_array($broken)) foreach ($broken as $key => $value) {
            if (gettype($this->$key) != "user function")
                $this->$key = $value;
        }
    }

    // ------------------------ ICW CREDIT CLASS Gift Voucher Addittion-------------------------------Start
    // amend count_contents to show nil contents for shipping
    // as we don't want to quote for 'virtual' item
    // GLOBAL CONSTANTS if NO_COUNT_ZERO_WEIGHT is true then we don't count any product with a weight
    // which is less than or equal to MINIMUM_WEIGHT
    // otherwise we just don't count gift certificates

    function count_contents_virtual() {  // get total number of items in cart disregard gift vouchers
        $total_items = 0;
        if (is_array($this->contents)) {
            foreach ($this->contents as $products_id => $val) {
                $no_count = false;
                $gv_query = tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                $gv_result = tep_db_fetch_array($gv_query);
                if (preg_match('/^GIFT/', $gv_result['products_model'])) {
                    $no_count = true;
                }
                if (NO_COUNT_ZERO_WEIGHT == 1) {
                    $gv_query = tep_db_query("select products_weight from " . TABLE_PRODUCTS . " where products_id = '" . InventoryHelper::get_prid($products_id) . "'");
                    $gv_result = tep_db_fetch_array($gv_query);
                    if ($gv_result['products_weight'] <= MINIMUM_WEIGHT) {
                        $no_count = true;
                    }
                }
                if (!$no_count)
                    $total_items += $this->get_quantity($products_id);
            }
        }
        return $total_items;
    }

    // ------------------------ ICW CREDIT CLASS Gift Voucher Addittion-------------------------------End

    function check_giveaway() {
      if (is_array($this->giveaway)) {
        foreach ($this->giveaway as $products_id => $giveaway) {
          if(!\common\helpers\Gifts::allowedGAW($products_id, $giveaway['qty']) && !$giveaway['promo_id']){
            unset($this->giveaway[$products_id]);
            if (!Yii::$app->user->isGuest) {
                CustomersBasket::deleteProduct(Yii::$app->user->getId(), $products_id, 1);
            }
          }
        }
      }
    }

    function is_valid_product_data($product_id, $attributes) {
        if (!is_array($attributes))
            $attributes = array();

        $valid_data = true;

        if (\common\helpers\Attributes::has_product_attributes($product_id)) {
            // include bundle
// {{ Products Bundle Sets
            $bundle_products = array(InventoryHelper::get_prid($product_id));
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                $bundle_products = $ext::getBundles($product_id, \common\classes\platform::currentId());
            }
// }}
            $valid_options = array();
            $attributes_query = tep_db_query(
                    "select products_id, options_id, options_values_id " .
                    "from " . TABLE_PRODUCTS_ATTRIBUTES . " " .
                    "where 1 " .
                    "AND " . (count($bundle_products) > 1 ? " products_id in ('" . implode("','", $bundle_products) . "')" : " products_id = '" . (int) $product_id . "'") . " "
            );
            while ($attr = tep_db_fetch_array($attributes_query)) {
                $look_opt_id = $attr['options_id'];
                if ((int) $attr['products_id'] != (int) $product_id) {
                    $look_opt_id = $attr['options_id'] . '-' . $attr['products_id'];
                }
                if (!isset($valid_options[$look_opt_id]))
                    $valid_options[$look_opt_id] = false;

                if (isset($attributes[$look_opt_id]) && $attributes[$look_opt_id] == $attr['options_values_id']) {
                    $valid_options[$look_opt_id] = true;
                }
            }

            $have_other_options = $attributes;
            foreach ($valid_options as $opt_id => $_present_valid) {
                unset($have_other_options[$opt_id]);
                if (!$_present_valid) {
                    $valid_data = false;
                }
            }

            if (count($have_other_options) > 0) {
                $valid_data = false;
            }
        } elseif (is_array($attributes) && count($attributes) > 0) {
            $valid_data = false;
        }

        return $valid_data;
    }

    function show_discount() {
        global $order, $ot_coupon;
        if (!is_object($ot_coupon)) {
            if (!is_object($order)) {
                $order = new \common\classes\Order();
            }
            require_once(DIR_WS_MODULES . 'order_total/ot_coupon.php');
            $ot_coupon = new ot_coupon();
            $ot_coupon->process();
        }
        return $ot_coupon->deduction;
    }

    function auto_giveaway() {
        if (USE_AUTO_GIVEAWAY != 'true') {
            return false;
        }


        $response = \common\helpers\Gifts::getGiveAwaysSQL(false, true, false); // all products, only active , no default sort order
        ///as only 1 gaw in cart the sort order is important.
        $giveaway_query = $response['giveaway_query']->addOrderBy('shopping_cart_price, buy_qty desc')->asArray()->all();
        if (is_array($giveaway_query)) {

            foreach ($giveaway_query as $d) {
              $best_gaw = \common\helpers\Gifts::get_max_quantity($d['products_id']);
              $this->add_cart($d['products_id'],  $best_gaw ['qty'], '', $best_gaw ['gaw_id'], 1);
              return;
            }
        }
    }

    public function update_customer_info($only_time = true) {
        if (!Yii::$app->user->isGuest) {
            $customersInfo = Yii::$app->user->getIdentity()->getCustomersInfo();
            if ($customersInfo){
                $customersInfo->updateTimeLong();
                if (!$only_time) {
                    $customersInfo->updateToken();
                }
                return $customersInfo->getToken();
            }
        }
    }

    public function setPlatform($platform_id) {
        $this->platform_id = (int) $platform_id;
        return $this;
    }

    public function setCustomer($customer_id) {
        $this->customer_id = (int) $customer_id;
        return $this;
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
        return $this;
    }

    public function setLanguage($language_id) {
        $this->language_id = (int) $language_id;
        return $this;
    }

    public function setAdmin($admin_id) {
        $this->admin_id = (int) $admin_id;
        return $this;
    }

    public function setBasketID($basket_id) {
        if (!$basket_id) {
            $this->basketID = $this->generate_cart_id();
        } else {
            $this->basketID = (int) $basket_id;
        }
        return $this;
    }

    public function setOverwrite($uprid, $key, $value) {

        if (!\frontend\design\Info::isTotallyAdmin())
            return;

        if (!isset($this->overwrite[$uprid])) {
            $this->overwrite[$uprid] = [];
        }
        $this->overwrite[$uprid][$key] = $value;

        return $this;
    }

    public function existOwerwritten($uprid) {
        if (isset($this->overwrite[$uprid]))
            return true;
        return false;
    }

    public function getOwerwritten($uprid) {
        if (isset($this->overwrite[$uprid]))
            return $this->overwrite[$uprid];
        return false;
    }

    public function getOwerwrittenKey($uprid, $key) {
        if (isset($this->overwrite[$uprid]) && isset($this->overwrite[$uprid][$key]))
            return $this->overwrite[$uprid][$key];
        return false;
    }

    public function clearOverwritenKey($uprid, $key) {
        if (isset($this->overwrite[$uprid]) && isset($this->overwrite[$uprid][$key]))
            unset($this->overwrite[$uprid][$key]);
    }

    public function clearOverwriten($uprid) {
        unset($this->overwrite[$uprid]);
    }

    public function overWrite($uprid, &$product) {
        $details = $this->getOwerwritten($uprid);
        if (is_array($details) && count($details)) {
            foreach ($details as $key => $value) {
                if (is_callable($value)){
                    $product[$details[$key.'_data'][0]] = call_user_func_array($value, $details[$key.'_data']);
                } elseif (is_scalar($value) && !($key == 'final_price' && isset($details['final_price_formula']))) { // don't overwrite final_price if formula exists
                    $product[$key] = $value;
                }
            }
        }
    }

    public function setAdjusted($value = 0){
        $this->adjusted = (bool)$value;
    }

    public function isAdjusted(){
        return $this->adjusted;
    }

    public function getQty($prid, $allVariations = true, $incGAW = false, $priceOnlyGAW = true) {
        if (!is_array($this->contents)) {
          return false;
        }
        $inCartQty = 0;
        if ($allVariations) {
          $prid = InventoryHelper::get_prid($prid);
        }

        $cartProducts = $this->contents;
        foreach ($cartProducts as $id => $cartProductsValues) {
          if ($allVariations) {
            if ($prid == InventoryHelper::get_prid($id)) {
              $inCartQty += $cartProductsValues['qty'];
            }
          } else {
            if ($prid == $id) {
              $inCartQty += $cartProductsValues['qty'];
            }
          }
        }

        if ($incGAW) {
          $cartProducts = $this->giveaway;
          if (is_array($cartProducts)) {
            foreach ($cartProducts as $id => $cartProductsValues) {
              if ($allVariations) {
                if ($prid == InventoryHelper::get_prid($id)) {
                  if (!$priceOnlyGAW || \common\helpers\Gifts::in_qty_discount($cartProductsValues['gaw_id']) ) {
                    $inCartQty += $cartProductsValues['qty'];
                  }
                }
              } else {
                if ($prid == $id) {
                  if (!$priceOnlyGAW || \common\helpers\Gifts::in_qty_discount($cartProductsValues['gaw_id']) ) {
                    $inCartQty += $cartProductsValues['qty'];
                  }
                }
              }
            }
          }
        }
        return $inCartQty;
    }

    /* PC configurator addon begin */
    function add_cart_cfg($products_id, $qty = '1', $attributes = '', $notify = false, $parent_id = '', $type = 'update', $relationType='') {
        $customer_id = Yii::$app->user->getId();
        if ($this->in_cart($products_id) && $this->contents[$products_id]['parent'] == $parent_id && $type == 'update') {
            $parent_old_quantity = $this->get_quantity($products_id);
            $this->update_quantity($products_id, $qty, $attributes);

            if ($parent_id == '' && is_array($this->contents)) {
                foreach ($this->contents as $key => $val) {
                    if ($this->contents[$key]['parent'] == $products_id) {
                        $this->contents[$key]['qty'] = $qty * ($this->contents[$key]['qty'] / $parent_old_quantity);
                        if ($customer_id) {
                            tep_db_query("update " . TABLE_CUSTOMERS_BASKET . " set customers_basket_quantity = '" . (int) $this->contents[$key]['qty'] . "' where customers_id = '" . (int) $customer_id . "' and products_id = '" . tep_db_input($key) . "'");
                        }
                    }
                }
            }
        } else {
            if (isset($this->contents[$products_id]) && ($this->contents[$products_id]['parent'] != $parent_id || $type == 'add')) {
                $idx = 0;
                $new_id = $products_id . '|' . $idx;
                while (isset($this->contents[$new_id]) && ($this->contents[$new_id]['parent'] != $parent_id || $type == 'add')) {
                    $idx++;
                    $new_id = $products_id . '|' . $idx;
                }
                $products_id = $new_id;
            }
            $this->contents[$products_id] = array('qty' => $qty, 'parent' => $parent_id, 'relation_type'=>$relationType);
            $this->contents[$products_id]['platform_id'] = \common\classes\platform::currentId();
            if (is_array($attributes)) {
                $this->contents[$products_id]['attributes'] = $attributes;
            }
            // insert into database
            if (!Yii::$app->user->isGuest) {
                CustomersBasket::saveProduct([$products_id => $this->contents[$products_id]], $customer_id, $this);
            }
        }
        \common\components\google\widgets\GoogleTagmanger::setEvent('addToCart');
        $this->cartID = $this->generate_cart_id();
        foreach (\common\helpers\Hooks::getList('shopping-cart/add-cart-cfg') as $filename) {
            include($filename);
        }
        return $products_id;
    }

    function add_configuration(array $post_data, $notify = true, $type = 'update') {
        global $new_products_id_in_cart;

        $bundle_products = array();
        $bundle_products_attributes = array();

        $post_data['id'] = $post_data['id'] ?? null;
        $sub = InventoryHelper::normalize_id(InventoryHelper::get_uprid($post_data['products_id'], $post_data['id']));
        $products_uprid = $sub . '{tpl}';
        reset($post_data['elements']);
        foreach ($post_data['elements'] as $key => $value) {
            if ($value == 'on' || $value == '0')
                continue;
// {{
            if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'getBundleProducts')) {
                $bundle_products[$key . '-' . $value] = $ext::getBundleProducts($value, \common\classes\platform::currentId());
                if (is_array($bundle_products[$key . '-' . $value]) && count($bundle_products[$key . '-' . $value]) > 0) {
                    $bundle_products_attributes[$key . '-' . $value] = array();
                    if (is_array($post_data['elements_attr'][$key][$value]??null)) {
                        $attributes = $post_data['elements_attr'][$key][$value];
                    } else {
                        $attributes = $post_data['elements_attr'][$value]??null;
                    }
                    if (is_array($attributes)) {
                        foreach ($attributes as $pk => $pv) {
                            if (strpos($pk, '-') !== false) {
                                $temp = explode('-', $pk);
                                $bundle_products_attributes[$key . '-' . $value][$temp[1]][$temp[0]] = $pv; //products options in bundle
                            } else {
                                $bundle_products_attributes[$key . '-' . $value][$value][$pk] = $pv; //main product option
                            }
                        }
                    }
                    foreach ($bundle_products[$key . '-' . $value] as $prid => $bundle_qty) {
                        $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($prid, $bundle_products_attributes[$key . '-' . $value][$prid]??null)) . '{sub}' . $sub;
                        $products_uprid .= ($key + \common\extensions\ProductConfigurator\helpers\Configurator::BUNDLE_ELEMENTS_SHIFT) . '|' . $sub_product_id . '|';
                    }
                    continue;
                }
            }
// }}
            $post_data['elements_attr'][$key][$value] = $post_data['elements_attr'][$key][$value] ?? null;
            $post_data['elements_attr'][$value] = $post_data['elements_attr'][$value] ?? null;
            if (is_array($post_data['elements_attr'][$key][$value])) {
                $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($value, $post_data['elements_attr'][$key][$value])) . '{sub}' . $sub;
            } else {
                $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($value, $post_data['elements_attr'][$value])) . '{sub}' . $sub;
            }
            $products_uprid .= $key . '|' . $sub_product_id . '|';
        }

        $qty = (is_numeric($post_data['qty']) ? (int) $post_data['qty'] : 1);
        if (isset($post_data['previous_id']) && isset($this->contents[$post_data['previous_id']])) {
            $qty = $this->contents[$post_data['previous_id']]['qty'];
            if (is_array($this->contents)) {
                foreach ($this->contents as $key => $val) {
                    if ($this->contents[$key]['parent'] == $post_data['previous_id']) {
                        $this->contents[$key]['qty'] = 0;
                    }
                }
            }
            $this->contents[$post_data['previous_id']]['qty'] = 0;
            $this->cleanup();
        } elseif (isset($this->contents[$products_uprid])) {
            if ($post_data['pconfig_dont_inc_qty'] ?? false) {
                $qty = (is_numeric($post_data['qty']) ? (int)$post_data['qty'] : 1);
            } else { // have no idea why he increments here
                $qty = $this->contents[$products_uprid]['qty'] + (is_numeric($post_data['qty']) ? (int)$post_data['qty'] : 1);
            }
            $type = 'update';
        }
        if ($notify == true) {
            $new_products_id_in_cart = $products_uprid;
            tep_session_register('new_products_id_in_cart');
        }
        $__ids = [];
        $__ids[] = $this->add_cart_cfg($products_uprid, $qty, $post_data['id'], true);
        reset($post_data['elements']);
        foreach ($post_data['elements'] as $key => $value) {
            if ($value == 'on' || $value == '0')
                continue;
            $post_data['elements_qty'][$key] = $post_data['elements_qty'][$key] ?? null;
            $post_data['elements_attr'][$value]['id'] = $post_data['elements_attr'][$value]['id'] ?? null;
            $elements_qty = ($post_data['elements_qty'][$key] > 0 ? $post_data['elements_qty'][$key] : 1);
// {{
            \common\helpers\Php8::nullArrProps($bundle_products, [$key . '-' . $value]);
            if (is_array($bundle_products[$key . '-' . $value]) && count($bundle_products[$key . '-' . $value]) > 0) {
                foreach ($bundle_products[$key . '-' . $value] as $prid => $bundle_qty) {
                    $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($prid, $bundle_products_attributes[$key . '-' . $value][$prid]??null)) . '{sub}' . $sub;
                    $__ids[] = $this->add_cart_cfg($sub_product_id, $qty * $elements_qty * $bundle_qty, $bundle_products_attributes[$key . '-' . $value][$prid]??null, false, $products_uprid, $type);
                }
                continue;
            }
// }}
            if (is_array($post_data['elements_attr'][$key][$value])) {
                $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($value, $post_data['elements_attr'][$key][$value]??null)) . '{sub}' . $sub;
                $__ids[] = $this->add_cart_cfg($sub_product_id, $qty * $elements_qty, $post_data['elements_attr'][$key][$value]??null, false, $products_uprid, $type);
            } else {
                $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($value, $post_data['elements_attr'][$value]['id']??null)) . '{sub}' . $sub;
                $__ids[] = $this->add_cart_cfg($sub_product_id, $qty * $elements_qty, $post_data['elements_attr'][$value]??null, false, $products_uprid, $type);
            }
        }
        $this->cleanup();
        Yii::$app->get('PropsHelper')::cartChanged($this);
        return $this->in_cart($__ids);
    }

    function configurator_price($parent_id, $products = '', $withTax = true, $_default_field = 'final_price') {
        $currencies = \Yii::$container->get('currencies');
        if ($this->contents[$parent_id]['qty'] == 0) return;
        $configurator_price = 0;
        if (!is_array($products)) {
            $products = $this->get_products();
        }
        for ($i=0, $n=sizeof($products); $i<$n; $i++) {
            if (ArrayHelper::getValue($products, [$i,'parent']) == $parent_id) {
                if (isset( $products[$i]['relation_type']) && $products[$i]['relation_type']=='linked' ) continue;
                $configurator_price += $currencies->calculate_price($products[$i][$_default_field], ($withTax ? \common\helpers\Tax::get_tax_rate($products[$i]['tax_class_id']): 0), $this->contents[$products[$i]['id']]['qty']);
            } elseif ($products[$i]['id'] == $parent_id) {
                $configurator_price += $currencies->calculate_price($products[$i][$_default_field], ($withTax ? \common\helpers\Tax::get_tax_rate($products[$i]['tax_class_id']): 0), $this->contents[$products[$i]['id']]['qty']);
            }
        }
        return ($configurator_price / $this->contents[$parent_id]['qty']);
    }

    function get_subproducts($parent_id) {
        $ar = [];
        if (is_array($this->contents)) foreach ($this->contents as $products_id => $value) {
            if (ArrayHelper::getValue($this->contents,[$products_id, 'parent']) == $parent_id) {
                $ar[] = $products_id;
            }
        }

        if (sizeof($ar)) {
            return $ar;
        } else {
            return false;
        }
    }
    /* PC configurator addon end */

    function add_bundle($products_id, $bundle_products = array(), $qty = '1', $attributes = '', $notify = true, $relationType='') {
        $bundle_products_attributes = array();
        if (is_array($attributes)) {
            foreach ($attributes as $pk => $pv) {
                if (strpos($pk, '-') !== false) {
                    $temp = explode('-', $pk);
                    $bundle_products_attributes[$temp[1]][$temp[0]] = $pv; //products options in bundle
                } else {
                    $bundle_products_attributes[$products_id][$pk] = $pv; //main product option
                }
            }
        }

        $sub = InventoryHelper::get_uprid($products_id, $bundle_products_attributes[$products_id] ?? null);
        $sub = InventoryHelper::normalize_id($sub);
        $products_uprid = $sub . '{tpl}';
        $key = 0;
        foreach ($bundle_products as $prid => $bundle_qty) {
            $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($prid, $bundle_products_attributes[$prid] ?? null)) . '{sub}' . $sub;
            $products_uprid .= ++$key . '|' . $sub_product_id . '|';
        }

        if (isset($this->contents[$products_uprid])) {
            $qty = $this->contents[$products_uprid]['qty'] + (is_numeric($qty) ? (int) $qty : 1);
            $type = 'update';
        } else {
            $type = 'add';
        }
        if ($notify == true) {
            $new_products_id_in_cart = $products_uprid;
            tep_session_register('new_products_id_in_cart');
        }
        $__ids = [];
        $__ids[] = $this->add_cart_cfg($products_uprid, $qty, $bundle_products_attributes[$products_id] ?? null, $notify, false, 'update', $relationType);

        foreach ($bundle_products as $prid => $bundle_qty) {
            $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($prid, $bundle_products_attributes[$prid] ?? null)) . '{sub}' . $sub;
            if (count(\common\helpers\Product::getChildArray($sub_product_id)) == 0) {
                $__ids[] = $this->add_cart_cfg($sub_product_id, $qty * $bundle_qty, $bundle_products_attributes[$prid] ?? null, false, $products_uprid, $type, $relationType);
            }
        }
        $this->cleanup();
        Yii::$app->get('PropsHelper')::cartChanged($this);
        return $this->in_cart($__ids);
    }

    function add_custom_bundle($notify = true, $type = 'update') {
        global $new_products_id_in_cart;

        $sub = InventoryHelper::normalize_id(InventoryHelper::get_uprid(\Yii::$app->request->post('products_id'), \Yii::$app->request->post('id')));
        $products_uprid = $sub . '{tpl}';
        reset($_POST['custom_bundles']);
        foreach ($_POST['custom_bundles'] as $key => $value) {
            if ($value == 'on' || $value == '0')
                continue;
            if (is_array($_POST['custom_bundles_attr'][$key][$value] ?? null)) {
                $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($value, $_POST['custom_bundles_attr'][$key][$value])) . '{sub}' . $sub;
            } else {
                $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($value, $_POST['custom_bundles_attr'][$value] ?? null)) . '{sub}' . $sub;
            }
            $products_uprid .= $key . '|' . $sub_product_id . '|';
        }

        $qty = (is_numeric($_POST['qty']) ? (int) $_POST['qty'] : 1);
        if (isset($this->contents[$products_uprid])) {
            $qty = $this->contents[$products_uprid]['qty'] + (is_numeric($_POST['qty']) ? (int) $_POST['qty'] : 1);
            $type = 'update';
        }
        if ($notify == true) {
            $new_products_id_in_cart = $products_uprid;
            tep_session_register('new_products_id_in_cart');
        }
        $__ids = [];
        $__ids[] = $this->add_cart_cfg($products_uprid, $qty, \Yii::$app->request->post('id'), true);
        reset($_POST['custom_bundles']);
        foreach ($_POST['custom_bundles'] as $key => $value) {
            if ($value == 'on' || $value == '0')
                continue;
            $custom_bundles_qty = (($_POST['custom_bundles_qty'][$value] ?? null) > 0 ? $_POST['custom_bundles_qty'][$value] : 1);
            if (is_array($_POST['custom_bundles_attr'][$key][$value] ?? null)) {
                $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($value, $_POST['custom_bundles_attr'][$key][$value])) . '{sub}' . $sub;
                $__ids[] = $this->add_cart_cfg($sub_product_id, $qty * $custom_bundles_qty, $_POST['custom_bundles_attr'][$key][$value], false, $products_uprid, $type);
            } else {
                $sub_product_id = InventoryHelper::normalize_id(InventoryHelper::get_uprid($value, $_POST['custom_bundles_attr'][$value] ?? null)) . '{sub}' . $sub;
                $__ids[] = $this->add_cart_cfg($sub_product_id, $qty * $custom_bundles_qty, $_POST['custom_bundles_attr'][$value] ?? null, false, $products_uprid, $type);
            }
        }
        $this->cleanup();
        Yii::$app->get('PropsHelper')::cartChanged($this);
        return $this->in_cart($__ids);
    }

    function isAmount($uprid){
        if( (int)$uprid===0 && (strpos($uprid,'_')!== false)  ){
            return true;
        }
        return false;
    }

    function add_cart_amount($price,$qty = 1,$name = '',$tax = null,$postfix = true)
    {
        global $order;

        if(empty($name)){
            $name = TEXT_CUSTOM_AMOUNT;
        }
        $qty = (int)$qty;
        if($qty < 1){
            $qty = 1;
        }
        $i = 0;
        do{
            $products_id = '0_'.$i;
            $i++;
        }while(array_key_exists($products_id, $this->contents));

        $this->contents[$products_id] = ['qty' => $qty, 'parent' => ''];

        if (!Yii::$app->user->isGuest) {
            $aBasket = CustomersBasket::create(Yii::$app->user->getId(),$products_id,$qty);
            $aBasket->save();
        }

        $this->setOverwrite($products_id, 'final_price', $price);
        $namePost = '';

        if($postfix && $i > 1 && $name === TEXT_CUSTOM_AMOUNT){
            $namePost = " (" . ($i-1) . ")";
        }
//backward comp - admin area???
        if (!is_null($tax) && strpos($tax, '_')!==false) {
            $ex = explode("_", $tax);
            if (count($ex) == 2) {
                $description = '';
                if(isset($order->tax_address['entry_country_id'])){
                    $description = \common\helpers\Tax::get_tax_description($ex[0], $order->tax_address['entry_country_id'], $ex[1]);
                }
                $tax_value = \common\helpers\Tax::get_tax_rate_value_edit_order($ex[0], $ex[1]);
                $this->setOverwrite($products_id, 'tax_selected', $tax);
                $this->setOverwrite($products_id, 'tax', $tax_value);
                $this->setOverwrite($products_id, 'tax_class_id', $ex[0]);
                $this->setOverwrite($products_id, 'tax_description',$description);
            } else {
                $this->setOverwrite($products_id, 'tax_selected', 0);
                $this->setOverwrite($products_id, 'tax', 0);
                $this->setOverwrite($products_id, 'tax_class_id', 0);
                $this->setOverwrite($products_id, 'tax_description', '');
            }
        } elseif (!is_null($tax)) {
            $uprid = $products_id;
            $tax_value = \common\helpers\Tax::get_tax_rate($tax);
            $this->setOverwrite($uprid, 'tax_selected', $tax);
            $this->setOverwrite($uprid, 'tax', $tax_value);
            $this->setOverwrite($uprid, 'tax_class_id', $tax);
            $this->setOverwrite($uprid, 'tax_description', \common\helpers\Tax::get_tax_description($tax, $order->tax_address['entry_country_id'], $order->tax_address['entry_zone_id']));
        } else {
            $this->setOverwrite($uprid, 'tax_selected', 0);
            $this->setOverwrite($uprid, 'tax', 0);
            $this->setOverwrite($uprid, 'tax_class_id', 0);
            $this->setOverwrite($uprid, 'tax_description', '');
        }


        $this->setOverwrite($products_id, 'name', $name.$namePost);

        $this->cleanup();
        $this->update_basket_info();
        $this->update_customer_info();
        return $products_id;
    }

    function edit_cart_amount($products_id,$price = false,$qty = false,$name = false,$tax = false)
    {
        global $order;

        if(!isset($this->contents[$products_id])){
            throw new \DomainException('Product missing in cart');
        }
        if(is_numeric($price) && (float)$price >= 0.00 ){
            $this->setOverwrite($products_id, 'final_price', $price);
        }
        if($qty !== false && (int)$qty > 0){
            $this->contents[$products_id]['qty'] = (int)$qty;
        }
        if($name !== false && mb_strlen($name)>0){
            $this->setOverwrite($products_id, 'name', $name);
        }

        if($tax !== false){
            $ex = explode("_", $tax);
            if (count($ex) == 2) {
                $description = '';
                if(isset($order->tax_address['entry_country_id'])){
                    $description = \common\helpers\Tax::get_tax_description($ex[0], $order->tax_address['entry_country_id'], $ex[1]);
                }
                $tax_value = \common\helpers\Tax::get_tax_rate_value_edit_order($ex[0], $ex[1]);
                $this->setOverwrite($products_id, 'tax_selected', $tax);
                $this->setOverwrite($products_id, 'tax', $tax_value);
                $this->setOverwrite($products_id, 'tax_class_id', $ex[0]);
                $this->setOverwrite($products_id, 'tax_description',$description);
            } else {
                $this->setOverwrite($products_id, 'tax_selected', 0);
                $this->setOverwrite($products_id, 'tax', 0);
                $this->setOverwrite($products_id, 'tax_class_id', 0);
                $this->setOverwrite($products_id, 'tax_description', '');
            }
        }

        if (!Yii::$app->user->isGuest) {
            $aBasket = CustomersBasket::create(Yii::$app->user->getId(),$products_id,$qty);
            $aBasket->save();
        }

        $this->cleanup();
        $this->update_basket_info();
        $this->update_customer_info();

    }
    public function productAmountData($products_id,$add = false)
    {
        $currencies = \Yii::$container->get('currencies');

        if((!isset($this->overwrite[$products_id]) || !isset($this->overwrite[$products_id]['final_price'])) && !$add){
            return false;
        }

        /** @var \common\extensions\Inventory\Inventory $ext */
        if ( ($ext = \common\helpers\Extensions::isAllowed('Inventory')) && !InventoryHelper::disabledOnProduct($products_id)) {
            $stock_info = $ext::getInventoryStock($products_id, 0, 1);
        } else {
            $stock_info = \common\classes\StockIndication::product_info(array(
                'products_id' => $products_id,
                'stock_indication_id' => 0,
                'max_qty'=>$this->contents[$products_id]['qty']+300,
                'cart_qty' => $this->contents[$products_id]['qty'],
                'cart_class' => true,
                'products_quantity' => $this->contents[$products_id]['qty']+300,
            ));
            $stock_info['quantity_max'] = $this->contents[$products_id]['qty']+300;
        }
        $productName = isset($this->overwrite[$products_id]['name'])?$this->overwrite[$products_id]['name']:'';
        $productModel = isset($this->overwrite[$products_id]['model'])?$this->overwrite[$products_id]['model']:'';
        $productQty = isset($this->contents[$products_id]['qty'])?$this->contents[$products_id]['qty']:1;
        $productFinalPrice = isset($this->overwrite[$products_id]['final_price'])?$this->overwrite[$products_id]['final_price']:0;
        $productTax = isset($this->overwrite[$products_id]['tax'])?$this->overwrite[$products_id]['tax']:0;
        $productsTaxClassId = isset($this->overwrite[$products_id]['tax_class_id'])?$this->overwrite[$products_id]['tax_class_id']:0;
        $productsTaxSelected = isset($this->overwrite[$products_id]['tax_selected'])?$this->overwrite[$products_id]['tax_selected']:0;
        $productsTaxDescription = isset($this->overwrite[$products_id]['tax_description'])?$this->overwrite[$products_id]['tax_description']:'';
        $data = [
            'id' => $products_id,
            'products_id' => $products_id,
            'product_id' => $products_id,
            'uprid' => $products_id,
            'product_name' => $productName,
            'name' => $productName,
            'model' => $productModel,
            'image' => '',
            'free_delivery' => 0,
            'not_for_same_day' => 0,
            'gift_wrap_allowed' => 0,
            'gift_wrap_price' => 0,
            'gift_wrapped' => 0,
            'price' => $productFinalPrice,
            'products_file' => '',
            'ga' => 0,
            'is_virtual' => 0,
            'qty' => $productQty,
            'quantity' => $productQty,
            'reserved_qty' => null,
            'stock_info' => $stock_info,
            'weight' => 0.00,
            'product_qty' => $productQty+300,
            'final_price' => $productFinalPrice,
            'parent' => '',
            'sub_products' => false,
            'tax' => $productTax,
            'tax_class_id' => $productsTaxClassId,
            'tax_description' => $productsTaxDescription,
            'tax_selected' => $productsTaxSelected,
            'attributes' => '',
            'overwritten' => $this->getOwerwritten($products_id),
            'subscription' => '0',
            'bonus_points_price' => null,
            'bonus_points_cost' => null,
            'product_valid' => '1',
            'final_price_tax' => $currencies->display_price($productFinalPrice, \common\helpers\Tax::get_tax_rate($productsTaxClassId), 1),
            'product_price' => $currencies->display_price($productFinalPrice, \common\helpers\Tax::get_tax_rate($productsTaxClassId), 1),
            'product_unit_price' => $productFinalPrice,
            'special_price' => '',
            'special_unit_price' => '',
            'product_in_cart' => true,
            'current_uprid' => $products_id,
            'attributes_array' => [],
            'bundles_block' => '',
            'bundles' => [],
            'order_quantity' => [
                'order_quantity_minimal' => 1,
                'order_quantity_max' => -1,
                'order_quantity_step' => 1,
                'pack_unit' => 0,
                'packaging' => 0,
            ],
            'is_editing' => false,
            'product_attributes' => '<div id="product-attributes"></div>',
            'subscription_code' => '',
            'product' =>[
                'product_id' => $products_id,
                'id' => $products_id,
                'products_id'=> $products_id,
                'uprid' => $products_id,
                'qty' => $productQty,
                'quantity' => $productQty,
                'name' => $productName,
                'stock_info' => $stock_info,
                'final_price' => $productFinalPrice,
                'sub_products' => false,
                'tax_selected' => $productsTaxSelected,
                'product_price' => $currencies->display_price($productFinalPrice, \common\helpers\Tax::get_tax_rate($productsTaxClassId), 1),
                'final_price_tax' => $currencies->display_price($productFinalPrice, \common\helpers\Tax::get_tax_rate($productsTaxClassId), 1),
                'tax' => $productTax,
                'tax_description' => $productsTaxDescription,
                'tax_class_id' => $productsTaxClassId,
                'price' => $productFinalPrice,
            ]
        ];
        /* Now not necessary, maybe in future.....
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
            $data= array_replace($data, $ext::getProductsCartFrontend($products_id, $this->contents));
        }
        /**/

        if ($this->existOwerwritten($products_id)) {
            $this->overWrite($products_id, $data);
        }
        return $data;
    }
}
