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

/*refactoring: $GLOBALS => $this->include_modules */
use yii\helpers\ArrayHelper;

class shipping extends modules\ModuleCollection {

    public $modules;
    protected $include_modules = [];
    private $manager;

// class constructor
    function __construct($module, \common\services\OrderManager $manager) {
        global $PHP_SELF;

        if (defined('MODULE_SHIPPING_INSTALLED') && tep_not_null(MODULE_SHIPPING_INSTALLED)) {
            $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

            $include_modules = array();

            $this->manager = $manager;

            if (( tep_not_null($module) ) && ( in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, ( strrpos($PHP_SELF, '.') + 1)), $this->modules) )) {
                $include_modules[] = array(
                    'class' => substr($module['id'], 0, strpos($module['id'], '_')),
                    'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, ( strrpos($PHP_SELF, '.') + 1))
                );
            } else {
// Show either normal shipping modules or free shipping module when Free Shipping Module is On
                // Free Shipping Only
                if (false && defined('MODULE_SHIPPING_FREESHIPPER_STATUS') && ( MODULE_SHIPPING_FREESHIPPER_STATUS == '1' || MODULE_SHIPPING_FREESHIPPER_STATUS == 'True' ) and $manager->getCart()->show_weight() == 0) {
                    $include_modules[] = array('class' => 'freeshipper', 'file' => 'freeshipper.php');
                } else {
                    // All Other Shipping Modules
                    if (is_array($this->modules)) {
                        foreach ($this->modules as $value) {
                            //$value = basename(str_replace('\\', '/', $value));
                            $class = substr($value, 0, strrpos($value, '.'));
                            // Don't show Free Shipping Module
                            if (true || $class != 'freeshipper') {
                                $include_modules[] = array('class' => $class, 'file' => $value);
                            }
                        }
                    }
                }
            }

            \common\helpers\Translation::init('shipping');
            //$this->include_modules = $include_modules;
            $builder = new \common\classes\modules\ModuleBuilder($manager);

            foreach ($include_modules as $include_module) {
                $class = $include_module['class'];
                $module = "\\common\\modules\\orderShipping\\{$class}";
                if (!class_exists($module)) {
                    continue;
                }
                $this->include_modules[$class] = $builder(['class' => $module]);
                if (!is_null($manager)) {
                    $this->include_modules[$class]->setPlatform($manager->get('platform_id') ?? \Yii::$app->get('platform')->config()->getId() );
                }
            }
        }
        if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER') && defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true'){
            $this->setFreeShippingOver(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER);
        }
    }

    public function getIncludedModules() {
        return $this->include_modules;
    }

    public function getEnabledModules() {
        static $enabled = null;
        if (is_null($enabled)){
            /** @var \common\extensions\CustomerModules\CustomerModules $CustomerModules */
            //$CustomerModules = \common\helpers\Acl::checkExtensionAllowed('CustomerModules', 'allowed');
            $enabled = [];
            foreach ($this->include_modules as $class => $module){
                /*$forceCustomer = false;
                if ($CustomerModules && !\Yii::$app->user->isGuest) {
                  if ($CustomerModules::checkForceAllowed(\common\classes\platform::currentId(), \Yii::$app->user->getId(), $class)) {
                    $forceCustomer = true;
                  }
                }
*/
                if ($module->enabled /*|| $forceCustomer*/){
                    $enabled[$class] = $module;
                }
            }
        }
        return $enabled;
    }

    public function get($class){
        return $this->getEnabledModules()[$class] ?? false;
    }

    public function has($class){
        return isset($this->include_modules[$class]) ? $this->include_modules[$class] : false;
    }

    protected $surcharge = null;
    public $shipping_weight;
    public $shipping_quoted = '';
    public $shipping_num_boxes = 1;
    public function claculateShippingElements(){

        $this->shipping_weight = $this->manager->get('total_weight');

        if (SHIPPING_BOX_WEIGHT >= $this->shipping_weight * SHIPPING_BOX_PADDING / 100) {
            $this->shipping_weight += SHIPPING_BOX_WEIGHT;
        } else {
            $this->shipping_weight += ( $this->shipping_weight * SHIPPING_BOX_PADDING / 100 );
        }

        if ($this->shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
            $this->shipping_num_boxes = ceil($this->shipping_weight / max(SHIPPING_MAX_WEIGHT,1) );
            $this->shipping_weight /= $this->shipping_num_boxes;
        }
    }

    public function getSurcharge(){
        static $perWarehouseAdditionalCharge;
        if ( !is_array($perWarehouseAdditionalCharge) ) {
            $perWarehouseAdditionalCharge = ArrayHelper::map(
                \common\models\Warehouses::find()
                    ->where(['>', 'shipping_additional_charge', 0])
                    ->select(['warehouse_id', 'shipping_additional_charge'])
                    ->asArray()
                    ->all(),
                'warehouse_id', 'shipping_additional_charge'
            );
        }

        if (is_null($this->surcharge)){
            $this->surcharge = 0;
            $additionalWarehouseCharge = 0;
            $products = $this->manager->getCart()->get_products();
            $productsIds = array_map('intval', ArrayHelper::getColumn($products, 'id'));
            $currencies = \Yii::$container->get('currencies');
            $groups_id = 0;
            if (!\Yii::$app->user->isGuest) {
                $groups_id = \Yii::$app->user->getIdentity()->groups_id;
            } elseif ( \Yii::$app->user->isGuest && defined('DEFAULT_USER_GROUP') ){
                $groups_id = (int)DEFAULT_USER_GROUP;
            }
            if ((defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') ||
                \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                $q = \common\models\ProductsPrices::find()->select('shipping_surcharge_price, products_id')
                    ->where([
                      'products_id' => $productsIds,
                      'groups_id' => $groups_id,
                      'currencies_id' => ((defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') ? (int)\Yii::$app->settings->get('currency_id') : 0)
                        ]);
                if (defined('SHIPPING_SURCHARGE_ONE_TIME_CART') && SHIPPING_SURCHARGE_ONE_TIME_CART == 'True') {
                    $q->orderBy('shipping_surcharge_price desc')->limit(1);
                }

            } else {
                $q = \common\models\Products::find()->select('shipping_surcharge_price, products_id')
                    ->where(['products_id' => $productsIds]);
                if (defined('SHIPPING_SURCHARGE_ONE_TIME_CART') && SHIPPING_SURCHARGE_ONE_TIME_CART == 'True') {
                    $q->orderBy('shipping_surcharge_price desc')->limit(1);
                }
            }
            $mProduct = $q->asArray()->indexBy('products_id')->column();

            foreach ($products as $product) {
                if (!empty($mProduct[(int)$product['id']])) {
                    if (defined('SHIPPING_SURCHARGE_ONE_TIME') && SHIPPING_SURCHARGE_ONE_TIME == 'True') {
                        $this->surcharge += $mProduct[(int)$product['id']];
                        unset($mProduct[(int)$product['id']]);
                    } elseif (defined('SHIPPING_SURCHARGE_ONE_TIME_VARIATION') && SHIPPING_SURCHARGE_ONE_TIME_VARIATION == 'True') {
                        $this->surcharge += $mProduct[(int)$product['id']];
                    } else {
                        $this->surcharge += $mProduct[(int)$product['id']] * $product['quantity'];
                    }
                }
                if ( count($perWarehouseAdditionalCharge)>0 ) {
                    foreach ($perWarehouseAdditionalCharge as $checkWarehouseId => $additionalCharge) {
                        if (\common\helpers\Warehouses::get_products_quantity($product['id'],$checkWarehouseId)>0) {
                            $additionalWarehouseCharge = max($additionalWarehouseCharge, $additionalCharge);
                        }
                    }
                }
            }
            $this->surcharge += $additionalWarehouseCharge;
        }
        return $this->surcharge;
    }

    public $pickupQuotes = [];
    public $deliveryQuotes = [];
    public $deliveryMethodsCount = 0;
    public $pickupMethodsCount = 0;
    public $allMethodsCount = 0;

    private $freeShippingOver;

    public function setFreeShippingOver($amount){
        $this->freeShippingOver = (float)$amount;
    }

    public function getFreeShippingOver(){
        return $this->freeShippingOver;
    }

    function quote($method = '', $module = '', $visibility = ['shop_order', 'shop_quote', 'shop_sample', 'admin', 'pos'], $groups_id = 0) {
        $visibility = \common\helpers\Extensions::getVisibilityVariants($visibility);
      if ($groups_id==0 && !\Yii::$app->user->isGuest) {
        $groups_id = \Yii::$app->user->getIdentity()->groups_id;
      }elseif( empty($groups_id) && \Yii::$app->user->isGuest && defined('DEFAULT_USER_GROUP') ){
        $groups_id = (int)DEFAULT_USER_GROUP;
      }
        $quotes_array = array();
        $this->surcharge = null;
        $this->deliveryQuotes = [];
        $this->pickupQuotes = [];

        if ( \common\helpers\Acl::checkExtensionAllowed('FraudAddress','allowed') && is_object($this->manager) ) {
            $fraudChecker = \common\extensions\FraudAddress\FraudAddress::checkoutChecker($this->manager);
            if ($fraudChecker && $fraudChecker->isSuspect()) {
                \Yii::info('Detected fraud checkout '.var_export($fraudChecker->suspectDetails(), true),'events');
                if (!\common\extensions\FraudAddress\FraudAddress::allowFraudCheckout()) {
                    $this->deliveryQuotes = \common\extensions\FraudAddress\FraudAddress::fraudShippingQuotes();
                    return $this->deliveryQuotes;
                }
            }
        }

        if ($method == 'free' && $module == 'free'){
            $currencies = \Yii::$container->get('currencies');
            return [
                        [
                            'id' => 'free',
                            'module' => FREE_SHIPPING_TITLE,
                            'methods' => [
                                [
                                    'id' => 'free',
                                    'selected' => true,
                                    'title' => sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format($this->getFreeShippingOver())),
                                    'code' => 'free_free',
                                    'cost_f' => '&nbsp;',
                                    'cost' => 0,
                                ],
                            ],
                        ]
                    ];
        }

        if (is_array($this->modules)) {

          /** @var \common\extensions\CustomerModules\CustomerModules $CustomerModules */
          $CustomerModules = \common\helpers\Acl::checkExtensionAllowed('CustomerModules', 'allowed');

            $this->claculateShippingElements();

            $include_quotes = array();

            foreach ($this->include_modules as $_module){
              if ( $_module->getGroupVisibily(\common\classes\platform::currentId(), $groups_id) || (!empty($CustomerModules) && $CustomerModules::checkAllowed(\common\classes\platform::currentId(), \Yii::$app->user->getId(), $_module->code, 'shipping'))) {
                $forceCustomer = false;
                if ($CustomerModules && !\Yii::$app->user->isGuest) {
                  if ($CustomerModules::checkForceAllowed(\common\classes\platform::currentId(), \Yii::$app->user->getId(), $_module->code, 'shipping')) {
                    $forceCustomer = true;
                  }
                }
                if (tep_not_null($module)) {
                    if (( $module == $_module->code ) && ( $_module->enabled || $forceCustomer) && $_module->getVisibily(\common\classes\platform::currentId(), $visibility) ) {
                        $include_quotes[] = $_module;
                    }
                } elseif (($_module->enabled || $forceCustomer) && $_module->getVisibily(\common\classes\platform::currentId(), $visibility)) {
                    $include_quotes[] = $_module;
                }
              }
            }

            if ($include_quotes){
                foreach($include_quotes as $_module){
                    $_module->setWeight($this->shipping_weight);
                    $_module->setNumBoxes($this->shipping_num_boxes);
                    $quotes = $_module->quote($method, '', $visibility);
                    if (property_exists($_module, 'tax_class') && $_module->useDelivery()) {
                        if ($_module->tax_class > 0) {
                            $response = $_module->getTaxValues($_module->tax_class);
                            if (is_array($quotes) AND is_array($response)) {
                                $quotes['tax'] = $response['tax'];
                            }
                        }
                    }
                    if (is_array($quotes)) {

                        $module_ignored = false;
                        foreach (\common\helpers\Hooks::getList('shipping/check-ignored') as $filename) {
                            $module_ignored = include($filename);
                            if ($module_ignored === true) break;
                        }
                        if ($module_ignored === true) continue;


                        /**/
                      if ($CustomerModules && !\Yii::$app->user->isGuest) {
                        if ( is_array($quotes['methods'])) {
                          foreach ($quotes['methods'] as $key => $value) {
                            if (!$CustomerModules::checkAvailable(\common\classes\platform::currentId(), \Yii::$app->user->getId(), $quotes['id'], 'shipping', $value['id'])) {
                              unset($quotes['methods'][$key]);
                            }
                          }
                          if ( count($quotes['methods']) == 0) {
                            continue;
                          }
                        } else {
                          if (!$CustomerModules::checkAvailable(\common\classes\platform::currentId(), \Yii::$app->user->getId(), $quotes['id'], 'shipping')) {
                            continue;
                          }
                        }
                      }
/**/
                        if ($this->getSurcharge() > 0 && $quotes['id'] != 'freeshipper' && is_array($quotes['methods'])) {
                            foreach ($quotes['methods'] as $key => $value) {
                                if ($value['cost']>0 || !defined('SHIPPING_SURCHARGE_FREE_METHODS') || SHIPPING_SURCHARGE_FREE_METHODS=='True') {
                                    $quotes['methods'][$key]['cost'] = $value['cost'] + $this->getSurcharge();
                                }
                            }
                        }
                        if (method_exists($_module, 'widget')) {
                            $quotes['widget'] = $_module->widget();
                        }
                        foreach (\common\helpers\Hooks::getList('shipping/after-quote') as $filename) {
                            include($filename);
                        }
                        if ($_module->useDelivery()){
                            $this->deliveryQuotes[] = $quotes;
                            $this->deliveryMethodsCount += (is_array($quotes['methods']??null)? count($quotes['methods']): 0);
                        } else {
                            $this->pickupQuotes[] = $quotes;
                            $this->pickupMethodsCount += (is_array($quotes['methods']??null)? count($quotes['methods']): 0);
                        }
                        $this->allMethodsCount += (is_array($quotes['methods']??null)? count($quotes['methods']): 0);
                    }
                }

                $this->deliveryQuotes = $this->limitModulesResult($this->deliveryQuotes);
            }

        }

        return array_merge($this->deliveryQuotes, $this->pickupQuotes);
    }

    public function getDeliveryQuotes(){
        return $this->deliveryQuotes;
    }

    public function getPickupQuotes(){
        return $this->pickupQuotes;
    }

    protected function limitModulesResult($moduleQuotes)
    {
        $resultQuotes = [];
        if ( !defined('MODULE_ORDER_TOTAL_SHIPPING_RESULT_COUNT') || !is_numeric(MODULE_ORDER_TOTAL_SHIPPING_RESULT_COUNT) ) {
            return $moduleQuotes;
        }

        $shippingCost = [];
        $shippingOrder = [];
        $shippingRef = [];
        foreach ($moduleQuotes as $moduleIdx=>$moduleInfo){
            if ( isset($moduleInfo['methods']) && is_array($moduleInfo['methods']) && count($moduleInfo['methods'])>0 ) {
                foreach( $moduleInfo['methods'] as $methodIdx => $moduleMethod ) {
                    if ( !empty($moduleInfo['error']) || !empty($moduleMethod['error']) || !isset($moduleMethod['cost']) ) {
                        $shippingCost[] = 1000000;
                        $shippingOrder[] = count($shippingOrder);
                        $shippingRef[] = [$moduleIdx, $methodIdx];
                    }else{
                        $cost = \common\helpers\Tax::add_tax_always($moduleMethod['cost'], $moduleInfo['tax']);
                        $shippingCost[] = floatval($cost);
                        $shippingOrder[] = count($shippingOrder);
                        $shippingRef[] = [$moduleIdx, $methodIdx];
                    }
                }
            }else{
                $shippingCost[] = 1000000;
                $shippingOrder[] = count($shippingOrder);
                $shippingRef[] = [ $moduleIdx, -1 ];
            }
        }

        if ( !defined('MODULE_ORDER_TOTAL_SHIPPING_LIMIT_RESULT_SORT') || MODULE_ORDER_TOTAL_SHIPPING_LIMIT_RESULT_SORT=='Cheapest first' ) {
            array_multisort($shippingCost, SORT_NUMERIC, $shippingOrder, SORT_NUMERIC, $shippingRef);
        }else{
            //array_multisort($shippingOrder, SORT_NUMERIC, $shippingRef);
        }

        $addedMethods = 0;
        foreach ( $shippingRef as $ref){
            $moduleIdx = $ref[0];
            $methodIdx = $ref[1];
            if ( !isset($resultQuotes[$moduleIdx]) ) {
                $resultQuotes[$moduleIdx] = $moduleQuotes[$moduleIdx];
                $resultQuotes[$moduleIdx]['methods'] = [];
            }
            if ( $methodIdx==-1 ) {
                unset($resultQuotes[$moduleIdx]['methods']);
            }else{
                $resultQuotes[$moduleIdx]['methods'][] = $moduleQuotes[$moduleIdx]['methods'][$methodIdx];
            }
            if (!$resultQuotes[$moduleIdx]['hide_row'] && !$moduleQuotes[$moduleIdx]['methods'][$methodIdx]['hide_row']) $addedMethods++;
            if ( $addedMethods>=(int)MODULE_ORDER_TOTAL_SHIPPING_RESULT_COUNT ) break;
        }

        return array_values($resultQuotes);
    }

    function getFirstQuoteModule($class, $visibility = ['shop_order', 'admin', 'pos']) {
        if (is_object($this->include_modules[$class]) && $this->include_modules[$class]->enabled) {
            $quotes = $this->include_modules[$class]->quote('', '', $visibility);
            if (is_array($quotes['methods'])) {
                return [[
                'id' => $quotes['id'] . '_' . $quotes['methods'][0]['id'],
                'title' => $quotes['module'] . ' (' . $quotes['methods'][0]['title'] . ')',
                'cost' => $quotes['methods'][0]['cost']
                ]];
            }
        }
        return false;
    }

    public $cheapest = null;
    CONST CHEAPEST_DELIVERY = 1;
    CONST CHEAPEST_PICKUP = 2;

    public function useDeliveryCheapest(){
        $this->cheapest = self::CHEAPEST_DELIVERY;
    }

    public function usePickupCheapest(){
        $this->cheapest = self::CHEAPEST_PICKUP;
    }

    /*var $type: pickup, delivery or empty(all)*/
    function cheapest($type = '') {
        //global $select_shipping;
        $cheapest = false;
        if (is_array($this->modules)) {
            $rates = [];
            $_quotes = null;

            if (!is_null($this->cheapest) && empty($type)){
                if ($this->cheapest == self::CHEAPEST_DELIVERY && $this->deliveryQuotes){
                    $_quotes = $this->deliveryQuotes;
                } else if ($this->cheapest == self::CHEAPEST_PICKUP && $this->pickupQuotes){
                    $_quotes = $this->pickupQuotes;
                }
            }
            if (is_null($_quotes)){
                $_quotes = ($type == 'pickup' ? $this->pickupQuotes : ($type == 'delivery' ? $this->deliveryQuotes : array_merge($this->deliveryQuotes, $this->pickupQuotes)));
            }

            foreach($_quotes as $quotes){
                if (isset($quotes['hide_row']) && $quotes['hide_row']) continue;
                    if (!isset($quotes['error'])) {
                        if (is_array($quotes['methods'])) {
                        for ($i = 0, $n = sizeof($quotes['methods']); $i < $n; $i ++) {
                            if (isset($quotes['methods'][$i]['cost']) && is_numeric($quotes['methods'][$i]['cost'])) {
                                $rates[] = array(
                                    'module' => $quotes['id'],
                                    'id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
                                    'title' => $quotes['module'] . ' (' . $quotes['methods'][$i]['title'] . ')',
                                    'cost' => $quotes['methods'][$i]['cost'],
                                    'no_cost' => (isset($quotes['methods'][$i]['no_cost']) ? $quotes['methods'][$i]['no_cost'] : false),
                                    'cost_inc_tax' => ((defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True')?$quotes['methods'][$i]['cost']:
                                        \common\helpers\Tax::add_tax_always($quotes['methods'][$i]['cost'], (isset($quotes['tax']) ? $quotes['tax'] : 0))),
                                    'cost_exc_tax' => ((defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True')?
                                  \common\helpers\Tax::reduce_tax_always($quotes['methods'][$i]['cost'], (isset($quotes['tax']) ? $quotes['tax'] : 0)):
                                  $quotes['methods'][$i]['cost'])
                                );
                            }
                        }
                    }
                }
            }

            for ($i = 0, $n = sizeof($rates); $i < $n; $i ++) {
              if ($i==0 || !defined('DEFAULT_SHIPPING_BY_SORT_ORDER') || DEFAULT_SHIPPING_BY_SORT_ORDER != 'True') {
                if (is_array($cheapest)) {
                    if ($rates[$i]['cost'] < $cheapest['cost']) {
                        $cheapest = $rates[$i];
                    }
                } else {
                    $cheapest = $rates[$i];
                }
              }
            }
        }
        return $cheapest;
    }

    public static function module($module, $front = false) {
        $file = $front ? DIR_WS_MODULES . 'shipping/' . $module . '.php' : DIR_FS_DOCUMENT_ROOT . '/includes/modules/shipping/' . $module . '.php';

        if (!is_null($module) && file_exists($file)) {
            include_once( $file);
            if (class_exists($module)) {
                return new $module;
            }
        }
        return null;
    }

}
