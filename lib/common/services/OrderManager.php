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

namespace common\services;

use common\classes\MessageStack;
use Yii;
use yii\base\Model;
use yii\web\Session;
use common\services\storages\StorageInterface;
use common\forms\AddressForm;
use common\forms\ShippingChoice;
use frontend\forms\registration\CustomerRegistration;

#[\AllowDynamicProperties]
class OrderManager {

    protected $storage;
    public $combineShippings = false; //used to perform ceparetly pickup and delivery methods or not
    public $skipShipping = false;
    private $softShippingValidation = false;

    public function __construct(StorageInterface $storage) {
        $this->storage = $storage;
        $this->combineShippings = self::getCombineShippingsDefault();
        self::$instance = $this;
    }

    public function set($name, $value) {
        $this->storage->set($name, $value);
    }

    public function get($name) {
        return $this->storage->get($name);
    }

    public function has($name) {
        return $this->storage->has($name);
    }

    public function remove($name) {
        return $this->storage->remove($name);
    }

    public function getAll() {
        return $this->storage->getAll();
    }

    public function getPayment() {
        return $this->get('payment');
    }

    public function getShipping() {
        return $this->get('shipping');
    }

    public function setPayment($payment_data) {
        return $this->set('payment', $payment_data);
    }

    public function setShipping(array $shipping_data) {
        return $this->set('shipping', $shipping_data);
    }

    public function assignCustomer($customer_id) {
        if (\common\models\Customers::findOne($customer_id)){
            $this->set('customer_id', $customer_id);
        }
    }

    public function isCustomerAssigned() {
        return $this->get('customer_id') ?? false;
    }

    public function getCustomerAssigned() {
        return (int) $this->get('customer_id');
    }

    public $contentType;

    public function setContentType($type) {
        $this->contentType = $type;
    }

    public function isShippingNeeded() {
        $needed = ($this->contentType != 'virtual') && ($this->contentType != 'virtual_weight');
        /** @var \common\extensions\Quotations\Quotations $ext */
        if ($this->getInstanceType() == 'quote' && ( ($ext = \common\helpers\Extensions::isAllowed('Quotations')) && !$ext::optionIsSkipShipping() )) {
          $needed = false;
        } else {
          // in some cases we don't have order instance here,
          // then getInstanceType() returns false and
          // skipShipping should be set according QUOTE_SKIP_SHIPPING in
          // <quote>CheckoutController etc.
          $needed = $needed && !$this->skipShipping;
        }

        if (!$needed) $this->remove('shipping');
        return $needed;
    }

    private $shipping;

    public function getShippingCollection($only_shipping = '') {
        if (!is_object($this->shipping)) {
            $this->shipping = new \common\classes\shipping($only_shipping, $this);
        }
        return $this->shipping;
    }

    public $quotes = [];

    public function getAllShippingQuotes($requote=false) {
        if (!$this->quotes || $requote) {
            $this->_pickupQuotes = null;
            $this->_dispatchQuotes = null;
            $this->shippingChoice = null;
            $this->quotes = $this->getShippingCollection()->quote('', '', $this->getModulesVisibility());
        }
        return $this->quotes;
    }

    protected $modulesVisiblility = ['shop_order'];

    public function setModulesVisibility($visibility = ['shop_order', 'shop_quote', 'shop_sample', 'admin', 'pos']) {
        $this->modulesVisiblility = \common\helpers\Extensions::getVisibilityVariants($visibility);
    }

    public function getModulesVisibility() {
        return $this->modulesVisiblility;
    }

    protected $_pickupQuotes = null;

    public function getPickupShippingQuotes() {
        if (is_null($this->_pickupQuotes)) {
            $this->getAllShippingQuotes();
            $this->_pickupQuotes = $this->getShippingCollection()->getPickupQuotes();
        }

        return $this->_pickupQuotes;
    }

    protected $_dispatchQuotes = null;

    public function getDispatchShippingQuotes() {
        if (is_null($this->_dispatchQuotes)) {
            $this->getAllShippingQuotes();
            $this->_dispatchQuotes = $this->getShippingCollection()->getDeliveryQuotes();
        }

        return $this->_dispatchQuotes;
    }

    //public $useDevorcedShippings = true; //true - pickup and delivery are cepareated
    public $shippingChoice = null;

    public function getPickupOrDeliveryChoice() {

        if (/* $this->useDevorcedShippings && */$this->isShippingNeeded() && ($this->getDispatchShippingQuotes() || $this->getPickupShippingQuotes()) && !$this->combineShippings) {
            $this->shippingChoice = new ShippingChoice($this);
            if ($this->has('shipping_choice')) {
                $this->shippingChoice->setChoice($this->get('shipping_choice'));
            }
        }
        return $this->shippingChoice;
    }

    public function setCustomerShippingChoice($choice) {
        $this->set('shipping_choice', $choice);
        $this->set('select_shipping', false);
        $this->set('shipping', false);
        if (!$choice) { //pickup
            //$this->remove('sendto');
            $this->setDefaultTaxCountry();
            $this->setDefaultTaxZone();
        }
        $this->getShippingQuotesByChoice();
    }

    public function setDefaultTaxCountry() {
        if ($this->isCustomerAssigned()) {
            $this->getCustomersIdentity()->set('customer_country_id', STORE_COUNTRY, true);
        }
    }

    public function getTaxAddress() {
        if ($this->isCustomerAssigned()) {
            ///STOP2do
            echo "#### <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($this->getCustomersIdentity()->getAll(), true) . "</PRE>";
            die;

            $ret = $this->getCustomersIdentity()->get('customer_country_id');
        } else {
            $option = \common\helpers\Tax::getTaxAddressOption();
            if ($option>0) { //shipping or any
                $ret = \common\helpers\Country::getDefaultShippingCountryId($this->getPlatformId());
            } else {//billing
                $ret = \common\helpers\Country::getDefaultBillingCountryId($this->getPlatformId());
            }
        }
        return $ret;
    }

    public function getTaxCountry() {
        if ($this->isCustomerAssigned()) {
            $ret = $this->getCustomersIdentity()->get('customer_country_id');
        } else {
            $option = \common\helpers\Tax::getTaxAddressOption();
            if ($option>0) { //shipping or any
                $ret = \common\helpers\Country::getDefaultShippingCountryId($this->getPlatformId());
            } else {//billing
                $ret = \common\helpers\Country::getDefaultBillingCountryId($this->getPlatformId());
            }
        }
        return $ret;
    }

    public function setDefaultTaxZone() {
        if ($this->isCustomerAssigned()) {
            $this->getCustomersIdentity()->set('customer_zone_id', STORE_ZONE, true);
        }
    }

    public function getTaxZone() {
        if ($this->isCustomerAssigned()) {
            return $this->getCustomersIdentity()->get('customer_zone_id');
        }
        return \common\helpers\PlatformConfig::getValue('STORE_ZONE', $this->getPlatformId());
    }

    /* 0-pickup, 1-delivery or all quotes, return wrapped quotes */

    public function getShippingQuotesByChoice($renew = false) {
        static $_quotes = null;
        if ( $renew ) $_quotes = null;
        if (is_null($_quotes) && $this->isShippingNeeded()) {
            if ($this->checkFreeShipping()) {
                $_quotes = $this->getShippingCollection()->quote('free', 'free', $this->getModulesVisibility());
            } else {
                if ($this->combineShippings) {
                    $_quotes = $this->getAllShippingQuotes();
                } else {
                    if ($this->getShippingChoice()) {
                        $_quotes = $this->getDispatchShippingQuotes();
                    } else {
                        $_quotes = $this->getPickupShippingQuotes();
                        if (!$_quotes) {
                            $_quotes = $this->getDispatchShippingQuotes();
                            $this->setCustomerShippingChoice(1);
                        }
                    }
                }
            }

            $this->checkExistedShippinInQuotes($_quotes);
            $_quotes = $this->wrapQuotes($_quotes);
        }

        return $_quotes;
    }

    /* 0-pickup, 1-delivery */

    public function getShippingChoice() {
        return ($this->shippingChoice ? $this->shippingChoice->getChoice() : ($this->has('shipping_choice')? $this->get('shipping_choice'):1));
    }

    protected function _detectCountry() {
        if (is_object($this->_order)) {
            return $this->_order->delivery['country_id'];
        } else {
            $_address = $this->getDeliveryAddress();
            return $_address['country']['id'];
        }
    }

    private $chargeOrder = true;

    public function setChargeOrder(bool $value) {
        $this->chargeOrder = $value;
        if (!$this->chargeOrder) {
            $this->setSelectedShipping('free_free');
        }
    }

    public function isChargedOrder() {
        return $this->chargeOrder;
    }

    public function checkFreeShipping() {

        if (!$this->chargeOrder)
            return true;

        if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true')) {
            $pass = false;

            switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
                case 'national':
                    if ($this->_detectCountry() == STORE_COUNTRY) {
                        $pass = true;
                    }
                    break;
                case 'international':
                    if ($this->_detectCountry() != STORE_COUNTRY) {
                        $pass = true;
                    }
                    break;
                case 'both':
                    $pass = true;
                    break;
            }

            $free_shipping = false;
            if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER')) {
                if (($pass == true) && ($this->_cart->show_total() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) {
                    $free_shipping = true;
                }
            }
        } else {
            $free_shipping = false;
        }

        foreach (\common\helpers\Hooks::getList('order-manager/check-free-shipping') as $filename) {
            include($filename);
        }

        return $free_shipping;
    }

    public function checkExistedShippinInQuotes($quotes) {
        $select_shipping = $this->getSelectedShipping();
        $exist = false;
        if (is_array($quotes) && $select_shipping) {
            for ($i = 0, $n = sizeof($quotes); $i < $n; $i++) {
                if (!isset($quotes[$i]['error'])) {
                    for ($j = 0, $n2 = sizeof($quotes[$i]['methods']??[]); $j < $n2; $j++) {
                        if (($select_shipping == $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'])) {
                            $exist = true;
                            $shipping = $this->_getShipingAsArray($quotes[$i], $quotes[$i]['id'], $quotes[$i]['methods'][$j]['id'], $j, false);
                            $this->setShipping($shipping);
                        }
                    }
                }
            }
        }
        if (!$exist) {
            $this->resetShipping();
        }
    }

    public function wrapQuotes($quotes) {
        if (is_array($quotes)) {
            $currencies = Yii::$container->get('currencies');
            $select_shipping = $this->getSelectedShipping();
            for ($i = 0, $n = count($quotes); $i < $n; $i++) {
                if (!isset($quotes[$i]['error'])) {
                    for ($j = 0, $n2 = count($quotes[$i]['methods']??[]); $j < $n2; $j++) {
                        $quotes[$i]['methods'][$j]['cost_f'] = $quotes[$i]['methods'][$j]['cost_f'] ?? $currencies->format(\common\helpers\Tax::add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0)));
                        $quotes[$i]['methods'][$j]['no_cost'] = $quotes[$i]['methods'][$j]['no_cost'] ?? null;
                        $quotes[$i]['methods'][$j]['code'] = $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'];
                        $quotes[$i]['methods'][$j]['selected'] = $select_shipping === $quotes[$i]['methods'][$j]['code'];
                        $quotes[$i]['selected'] = $select_shipping === $quotes[$i]['methods'][$j]['code'];
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ModulesZeroPrice', 'allowed')) {
                            if ( method_exists($ext, 'shippingQuoteMethod') ) {
                                $quotes[$i]['methods'][$j] = $ext::shippingQuoteMethod($this->getPlatformId(), $quotes[$i]['methods'][$j]);
                            }
                        }
                    }
                }
            }
        }
        return $quotes;
    }

    public function resetShipping() {
        if (array_intersect(['admin', 'pos'], $this->getModulesVisibility()) && defined('SHIPPING_UNSELECTED') && SHIPPING_UNSELECTED == 'unselected')
            return;
        if ($this->checkFreeShipping()) {
            $cheapest = $this->getShippingCollection()->quote('free', 'free', $this->getModulesVisibility());
            $cheapest = $this->_getShipingAsArray($cheapest[0], 'free', 'free', 0);
        } else {
            $cheapest = $this->getShippingCollection()->cheapest($this->combineShippings ? '' : ($this->getShippingChoice() ? 'delivery' : 'pickup'));
        }
        if (is_array($cheapest)) {
            $this->setShipping($cheapest);
        } else {
            $this->remove('shipping');
        }
    }

    public function updateShippingCost() {
        // recalc shipping on changing product count and weight
        $needUpdate = $this->updateSummaryFields();
        if ($this->getCart()->getTotalKey('ot_shipping') === false || $needUpdate) {
            $this->getCart()->clearTotalKey('ot_shipping');
            //$this->setSelectedShipping($this->getSelectedShipping());
            $this->getShippingQuotesByChoice(true);
            $this->checkoutOrder();
        }
    }

    public function getSelectedShipping() {
        $_selected = false;
        $_shipping = $this->getShipping();
        $_selected = is_array($_shipping) ? $_shipping['id'] : false;
        if (!$_selected) {
            $_selected = $this->get('select_shipping') ?? false;
        }
        return $_selected;
    }

    /* used to detect selecting delivery method by customer */

    public function isDeliveryUsed() {
        if ($this->isShippingNeeded()) {
            $_shipping = $this->getShipping();
            if (is_array($_shipping)) {
                $class = $_shipping['module'];
                if ($class == 'free') return true;
                $_shipping = $this->getShippingCollection()->get($class);
                return ($_shipping ? $_shipping->useDelivery() : false);
            }
        }
        return false;
    }

    /**
     * @param null|object|array $data
     * @param string $key
     * @return bool
     */
    public function validateShipping($data = null, string $key = 'one_page_checkout'): bool
    {
        if ($this->checkFreeShipping()){
            return true;
        }
        $shipping = $this->getShipping();
        if (empty($shipping['id'])) {
            return true;
        }
        [$class, $method] = explode('_', $shipping['id']);
        $shipping = $this->getShippingCollection()->get($class);
        $response = $shipping->validate($method, $data);
        if (is_bool($response)) {
            return $response;
        }
        if (is_array($response)) {
            try {
                $messageStack = \Yii::$container->get('message_stack');
                foreach ($response as $error) {
                    $messageStack->add((is_array($error) ? implode('<br>', $error) : $error), $key);
                }
            } catch (\Exception $ex) {}
        }
        return false;
    }

    private function _getShipingAsArray($quote, $module, $method, $method_index, $free_shipping = false){
        $cost_inc = $cost_exc = $cost = (float) $quote['methods'][$method_index]['cost'];
        if (!empty($quote['tax'])) {
            if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                $cost_exc = \common\helpers\Tax::reduce_tax_always($cost, $quote['tax']);
            } else {
                $cost_inc = \common\helpers\Tax::add_tax_always($cost, $quote['tax']);
            }
        }
        return [
            'module' => $module,
            'id' => $module . '_' . $quote['methods'][$method_index]['id'],
            'title' => (($free_shipping == true) ? $quote['methods'][$method_index]['title'] : $quote['module'] . (empty(trim($quote['methods'][$method_index]['title']))?'':' (' . $quote['methods'][$method_index]['title'] . ')')),
            'cost' => $cost,
            'no_cost' => $quote['methods'][$method_index]['no_cost'] ?? null,
            'cost_inc_tax' => $cost_inc,
            'cost_exc_tax' => $cost_exc,
            'cost_f' => @$quote['methods'][$method_index]['cost_f'],
        ];
    }

    /**
     * if shipping is required and $shippping has correct format then set or reset "shipping" in the manager itself
     * @param string $shipping value shippingCode_shippingMethod
     */
    public function setSelectedShipping($shipping) {
        if ($this->isShippingNeeded() && (strpos($shipping, '_') !== false)) {
            list($module, $method) = explode('_', $shipping);
            $free_shipping = $this->checkFreeShipping();
            if ($free_shipping && $shipping == 'free_free') {
                $quote = $this->getShippingCollection()->quote('free', 'free', $this->getModulesVisibility());
            } elseif (!$free_shipping && $shipping == 'free_free') {
                $quote[0]['error'] = true;
            } else {
                $quote = $this->getShippingCollection()->quote($method, $module, $this->getModulesVisibility());
            }
            if (!isset($quote[0]['error'])) {
                if (isset($quote[0]['methods'][$method]['title']) && isset($quote[0]['methods'][$method]['cost'])) {
                    $shipping = $this->_getShipingAsArray($quote[0], $module, $method, $method, $free_shipping);
                } else if ((isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost']))) {
                    $shipping = $this->_getShipingAsArray($quote[0], $module, $method, 0, $free_shipping);
                }
                if (is_array($shipping)) {
                    $this->setShipping($shipping);
                } else {
                    $this->remove('shipping');
                }
            } else {
                $this->remove('shipping');
            }
        }
    }

    public function reverseChoiceByShipping(array $shipping) {
        if ($this->combineShippings||true) {
            $_delivery = $this->getShippingCollection()->getDeliveryQuotes();
            $set = false;
            if ($_delivery) {
                foreach ($_delivery as $_quote) {
                    if ($_quote['id'] == $shipping['module']) {
                        $this->set('shipping_choice', 1);
                        $set = true;
                        break;
                    }
                }
            }
            if (!$set) {
                $_pickup = $this->getShippingCollection()->getPickupQuotes();
                if ($_pickup) {
                    foreach ($_pickup as $_quote) {
                        if ($_quote['id'] == $shipping['module']) {
                            $this->set('shipping_choice', 0);
                            $set = true;
                            break;
                        }
                    }
                }
            }
        }
    }

    private $payment_modules;

    public function getPaymentCollection($only_payment = '') {
        if (!is_object($this->payment_modules)) {
            $this->payment_modules = new \common\classes\payment($only_payment, $this);
        }
        return $this->payment_modules;
    }

    public function setSelectedPaymentModule($only_payment) {
        $this->payment_modules = null;
        return $this->getPaymentCollection($only_payment, $this);
    }

    /**
     * @param string $customerDetails - 'exist'/'optional'/'absent'/'auto'. Currently - exist for compatibility
     */
    public function getPaymentSelection($opc = false, $onlyOnline = false, $customerDetails = 'exist') {
        if ($customerDetails == 'auto') {
            $customerDetails = $this->isCustomerAssigned() ? 'exist' : 'absent';
        }
        $selections = $this->getPaymentCollection()->selection($opc, $onlyOnline, $this->getModulesVisibility(), $this->get('customer_groups_id'), $customerDetails);
        return $this->wrapSelections($selections);
    }

    public function getCreditPayment() {
        $payments = $this->getPaymentCollection();
        foreach ($payments->include_modules as $payment) {
            if ($payment->manageCredit ?? false) {
                return $payment;
            }
        }
    }

    public function wrapSelections($selections) {
        if (is_array($selections)) {
            $select_payment = $this->getSelectedPayment($selections);
            for ($i = 0, $n = sizeof($selections); $i < $n; $i++) {
                if ($select_payment == $selections[$i]['id']) {
                    $selections[$i]['checked'] = true;
                }
                if (isset($selections[$i]['methods']) && is_array($selections[$i]['methods'])) {
                    $subselected = false;
                    for ($j = 0, $m = sizeof($selections[$i]['methods']); $j < $m; $j++) {
                        if ($select_payment == $selections[$i]['methods'][$j]['id']) {
                            $selections[$i]['methods'][$j]['checked'] = true;
                            $subselected = true;
                        }
                    }
                    if ($subselected === false && isset($selections[$i]['methods'][0]) && isset($selections[$i]['checked'])) {
                        $selections[$i]['methods'][0]['checked'] = $selections[$i]['checked'];
                    }
                }
            }
        }
        return $selections;
    }

    public function getSelectedPayment($selections = []) {
        $_selected = $this->getPayment() ?? false;
        if (array_intersect(['admin'], $this->getModulesVisibility()) && defined('PAYMENT_UNSELECTED') && PAYMENT_UNSELECTED == 'unselected') {
            return $_selected;
        }
        if ($selections) {
            $exist = false;
            for ($i = 0, $n = sizeof($selections); $i < $n; $i++) {
                if (!$_selected) {
                    if (!($selections[$i]['hide_row'] ?? false)) {
                        $_selected = $selections[$i]['id'];
                        $exist = true;
                        break;
                    }
                } else { //payment can be depended from shipping
                    if ($_selected == $selections[$i]['id']) {
                        $exist = true;
                    }
                    if (isset($selections[$i]['methods']) && is_array($selections[$i]['methods'])) {
                        for ($j = 0, $m = sizeof($selections[$i]['methods']); $j < $m; $j++) {
                            if ($_selected == $selections[$i]['methods'][$j]['id']) {
                                $exist = true;
                            }
                        }
                    }
                }
            }
            if (!$exist) {
                $_selected = $selections[0]['id'];
            }
            if ($_selected) {
                $this->setPayment($_selected);
            }
        }

        return $_selected;
    }

    public function setSelectedPayment($payment) {
        if ($payment) {
            $this->setPayment($payment);
        }
    }

    public function paymentPreConfirmationCheck() {
        $this->getPaymentCollection()->pre_confirmation_check();
    }

    public function getPaymentUrl() {
        return $this->getPaymentCollection()->getPaymentUrl();
    }

    public function getPaymentConfirmation() {
        $confirmation = $this->getPaymentCollection()->confirmation();
        if (!is_array($confirmation))
            $confirmation = [];
        return $confirmation;
    }

    public function getPaymentButton() {
        return $this->getPaymentCollection()->process_button();
    }

    public function getPaymentButtonPost() {
      try {
        $tst = $this->getPaymentCollection()->processButton();
        if (is_array($tst)) {
          return $tst;
        }
      } catch (\Exception $e) {
        \Yii::warning($e->getMessage() . ' '. $e->getTraceAsString());
      }
      return false;
    }

    public function getPaymentJSValidation() {
        return $this->getPaymentCollection()->javascript_validation();
    }

    private static $instance = null;

    public static function loadManager($cart = null) {
        if (is_null(self::$instance)) {
            self::$instance = new self(Yii::$app->get('storage'));
            if (is_object($cart)) {
                self::$instance->loadCart($cart);
            }
        }
        return self::$instance;
    }

    /** @var \common\classes\shopping_cart $_cart */
    protected $_cart;

    public function loadCart(\common\classes\shopping_cart $cart) {
        $this->_cart = $cart;
        $this->contentType = $cart->get_content_type();
        $this->updateSummaryFields();
    }

    public function getCart() {
        return $this->_cart;
    }

    public function hasCart() {
        return is_object($this->_cart);
    }

    public function is($class) {
        return $this->_cart instanceof $class;
    }

    public function getSendto() {
        $sendto = $this->get('sendto') ?? false;
        if (!$sendto && $this->getShippingChoice()) {
            if ($customer = $this->getCustomersIdentity()) {
                $address = $customer->getDefaultShippingAddress()->one();
                if ($address) {
                    $sendto = $address->address_book_id;
                    $this->set('sendto', $sendto);
                }
            }
        }
        return $sendto;
    }

    public function getBillto() {
        $billto = $this->get('billto') ?? false;
        if (!$billto) {
            if ($customer = $this->getCustomersIdentity()) {
                $address = $customer->getDefaultAddress()->one();
                if ($address) {
                    $billto = $address->address_book_id;
                    $this->set('billto', $billto);
                }
            }
        }
        return $billto;
    }

    public function isBillAsShip() {
        if ($this->isCustomerAssigned()) {
            return $this->getBillto() == $this->getSendto();
        } else {
            return $this->getBillto() == $this->getSendto() ||
                (
                empty($this->getBillto()
                && 0 && 
                 (!\common\helpers\Country::checkPlatformCountry(null, null, 'bill') ||
                  !\common\helpers\Country::checkPlatformCountry(null, null, 'ship') )
                 
                
                ));
        }
        //return false;
    }

    private $_customer = null;

    public function getCustomersIdentity() {
        if ($this->isCustomerAssigned()) {
            if (Yii::$app->user->getId() == $this->getCustomerAssigned()) { //current frontend user
                $this->_customer = Yii::$app->user->getIdentity();
            } else {
                if (is_object($this->_customer) && $this->getCustomerAssigned() == $this->_customer->customers_id) {
                    return $this->_customer;
                }
                $this->_customer = \common\components\Customer::findOne(['customers_id' => $this->getCustomerAssigned()]);
                // Guest checkout on few platforms at the same time is in logic conflict with Customer::removeDuplicateGuestsAccounts()
                if (is_null($this->_customer)) {
                    $this->set('customer_id', 0); // Maybe we should use $this->clearStorage();
                    $this->_customer = new \common\components\Customer();
                    if ($this->has('guest_email_address')) {
                        $this->_customer->customers_email_address = $this->get('guest_email_address');
                    }
                    if ($this->has('guest_telephone')) {
                        $this->_customer->customers_telephone = $this->get('guest_telephone');
                    }
                }
            }
        } else {
            if (is_null($this->_customer)) {
                if ($this->has('customer')){ //do not use on frontend, only for stored before deleted customer account
                    if ($this->get('customer') instanceof \common\components\Customer){
                        $this->_customer = $this->get('customer');
                        $this->_customer->set('fromOrder', true);
                    }
                }
                if (is_null($this->_customer)){
                    $this->_customer = new \common\components\Customer();
                    if ($this->has('guest_email_address')) {
                        $this->_customer->customers_email_address = $this->get('guest_email_address');
                    }
                    if ($this->has('guest_telephone')) {
                        $this->_customer->customers_telephone = $this->get('guest_telephone');
                    }
                }
            }
        }
        return $this->_customer;
    }

    public function getCustomersAddresses($toArray = false, $stripEntry = false, $type = '') {
        $addresses = [];
        if ($customer = $this->getCustomersIdentity()) {
            $addresses = $customer->getAddressBooks($toArray, false, $type);
            if ($addresses && $stripEntry && $toArray) {
                $addresses = \common\helpers\Address::skipEntryKey($addresses);
            }
        }
        return $addresses;
    }

    public function getCustomersAddress($abId, $toArray = false, $stripEntry = false) {
        $address = null;
        if ($customer = $this->getCustomersIdentity()) {
            $address = $customer->getAddressBook($abId, $toArray);
            if ($address && $stripEntry && $toArray) {
                $address = \common\helpers\Address::skipEntryKey([$address]);
                $address = $address[0];
            }
        }
        return $address;
    }

    protected function loadDefaultAddressValues($postfix) {
        if ($this->has('estimate' . $postfix)) {
            $estimate = $this->get('estimate' . $postfix);
            $_country_info = \common\helpers\Country::get_countries($estimate['country_id'], true, '', substr($postfix, 1));
            $address = [
                'street_address' => $estimate['street_address']??'',
                'suburb' => $estimate['suburb']??'',
                'city' => $estimate['city']??'',
                'postcode' => $estimate['postcode']??'',
                'zone_id' => (isset($estimate['zone']) && !empty($estimate['zone']) ? (is_int($estimate['zone']) ? $estimate['zone'] : \common\helpers\Zones::get_zone_id($estimate['country_id'], $estimate['zone'])) : 0),
                'country_id' => $estimate['country_id'],
            ];
            foreach (['company_vat', 'company_vat_date', 'company_vat_status', 'customs_number', 'customs_number_date', 'customs_number_status', 'city'] as $k) {
              if (!empty($estimate[$k])) {
                $address[$k] = $estimate[$k];
              }
            }
        } else {
            $_country_info = \common\helpers\Country::get_countries($this->getTaxCountry(), true);
            $address = [
                'street_address' => '',
                'suburb' => '',
                'city' => '',
                'postcode' => '',
                'zone_id' => $this->getTaxZone(),
                'country_id' => $this->getTaxCountry(),
            ];
        }

        $_country_info['title'] = $_country_info['text'];
        $_country_info['iso_code_2'] = $_country_info['countries_iso_code_2'];
        $_country_info['iso_code_3'] = $_country_info['countries_iso_code_3'];
        $address['country'] = $_country_info;
        return $address;
    }

    public static function getRecalculateShippingFields()
    {
        $trigger_fields = preg_split('/,\s?/',TRIGGER_RECALCULATE_FIELDS, -1, PREG_SPLIT_NO_EMPTY);
        if ( count($trigger_fields)==0 ) {
            $trigger_fields[] = 'country';
        }
        return $trigger_fields;
    }

    private $deliveryAddressChanged = false;

    public function resetDeliveryAddress(){
        $this->deliveryAddressChanged = true;
        foreach (\common\helpers\Hooks::getList('order-manager/reset-delivery-address') as $filename) {
            include($filename);
        }
    }

    private $billingAddressChanged = false;

    public function resetBillingAddress(){
        $this->billingAddressChanged = true;
    }
    /* delivery address for all modules (payment, shippng, ot_) */

    public function getDeliveryAddress() {
        static $address = null;
/** /
        $ee = new \Exception();
        echo "getDeliveryAddress \$address#### <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($address, true) . "</PRE>";
        echo "#### <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($ee->getTraceAsString(), true) . "</PRE>";
/**/
        if (is_null($address) || $this->deliveryAddressChanged) {
            $address = [];
            if ($this->has('estimate_ship') || !$this->isCustomerAssigned()) {
                $address = $this->loadDefaultAddressValues('_ship');
            } else {
                $sendto = $this->getSendto();
                if ($sendto) {
                    if (is_scalar($sendto)) {
                        $address = $this->getCustomersAddress($sendto, true, true);
                        if ($address) {
                            if (is_array($address['country'])) {
                                $address['country'] = \common\helpers\Address::addCountriesKey($address['country']);
                            }
                        }
                    } else if (is_array($sendto)) {
                        $address = $sendto;
                    }
                }
            }
            /* else {
              $address = $this->loadDefaultAddressValues('_ship');
              } */
        }//echo '<pre>';print_r($address);

        return $address;
    }

    /**
     * billing address for all modules (payment, shipping, ot_)
     * @staticvar array|null $address
     * @return array
     */
    public function getBillingAddress() {
        static $address = null;
        if (is_null($address) || $this->billingAddressChanged) {
            $address = [];
            if ($this->has('estimate_bill') || !$this->isCustomerAssigned()) {
                $address = $this->loadDefaultAddressValues('_bill');
            } else {
                $billto = $this->getBillto();
                if ($billto) {
                    if (is_scalar($billto)) {
                        $address = $this->getCustomersAddress($billto, true, true);
                    } else if (is_array($billto)) {
                        $address = $billto;
                    }
                    if (is_array($address['country']??null)) {
                        $address['country'] = \common\helpers\Address::addCountriesKey($address['country']);
                    }
                }
            }
            /* else {
              $address = $this->loadDefaultAddressValues('_bill');
              } */
        }
        return $address;
    }

    protected $shippingForm;

    public function buildShippingAddressForm() {
        $this->shippingForm = new AddressForm(['scenario' => AddressForm::SHIPPING_ADDRESS]);
    }

    public function getShippingForm($ab_id = null, $preload = true) {
        if (!is_object($this->shippingForm)) {
            $this->buildShippingAddressForm();
        }

        if ($preload && !$this->shippingForm->hasErrors()) {
            if ($this->isCustomerAssigned()) {
                if ($customer = $this->getCustomersIdentity()) {
                    $_sendto = $this->getSendto();
                    if (is_array($_sendto)) {
                        $address = $_sendto;
                    } else {
                        $address = $customer->getAddressBook($ab_id ?? $_sendto);
                    }
                    if ($address) {
                        $this->shippingForm->preload($address);
                    } else {
                        $this->shippingForm->preloadDefault();
                    }
                }
            } else {
                $_sendto = $this->getSendto();
                if (is_array($_sendto)) {
                    $this->shippingForm->preload($_sendto);
                } else {
                    $this->shippingForm->preloadDefault();
                }
            }
        }
        $this->shippingForm->setLightCheck($this->isSoftShippingValidation());
        return $this->shippingForm;
    }

    public function changeCustomerAddressSelection($type, $address) {
        if ($this->isCustomerAssigned() && $address) {
          if ($type == 'shipping') {
            $this->set('sendto', $address);
            $this->changeCustomerTaxAddress(1);
          } else {
            $this->set('billto', $address);
            $this->changeCustomerTaxAddress(0);
          }
        } else {
            if (is_array($address)) {
                if ($type == 'shipping') {
                    $this->set('sendto', $address);
                } else {
                    $this->set('billto', $address);
                    $this->set('customer_country_id', $address['country_id']);
                    $this->set('customer_zone_id', $address['zone_id']);
                }
            }
        }
    }
    public function changeCustomerTaxAddress($which = 2) {
      if ($this->isCustomerAssigned()) {
        $option = \common\helpers\Tax::getTaxAddressOption();
        if ($option != 2 && $option != $which) {
            return; // apprpriate address wasn't changed
        } else {
            $option = $which;
        }

        switch ($option) {
          case 0:
            $ab = $this->getCustomersIdentity()->getAddressBook($this->get('billto'));
            if ($ab && $ab->entry_country_id ) {
                $this->getCustomersIdentity()->set('customer_country_id', $ab->entry_country_id, true);
                $this->getCustomersIdentity()->set('customer_zone_id', $ab->entry_zone_id, true);
                /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
                if ($VatOnOrder = \common\helpers\Acl::checkExtension('VatOnOrder', 'resetCustomerData')) {
                    $VatOnOrder::resetCustomerData($ab);
                }
            }
            break;
          case 1:
            $ab = $this->getCustomersIdentity()->getAddressBook($this->get('sendto'));
            if ($ab && $ab->entry_country_id) {
                $this->getCustomersIdentity()->set('customer_country_id', $ab->entry_country_id, true);
                $this->getCustomersIdentity()->set('customer_zone_id', $ab->entry_zone_id, true);
                /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
                if ($VatOnOrder = \common\helpers\Acl::checkExtension('VatOnOrder', 'resetCustomerData')) {
                    $VatOnOrder::resetCustomerData($ab);
                }
            }
            break;
          case 2:
            $country_id = /*\Yii::$app->storage->has('customer_country_id')? \Yii::$app->storage->get('customer_country_id') : */ \common\helpers\PlatformConfig::getValue('STORE_COUNTRY');
            $zone_id = /*\Yii::$app->storage->has('customer_zone_id')? \Yii::$app->storage->get('customer_zone_id') :*/ \common\helpers\PlatformConfig::getValue('STORE_ZONE');

            $ab = $this->getCustomersIdentity()->getAddressBook($this->get('sendto'));

            if ($ab && $ab->entry_country_id && $ab->entry_country_id==$country_id && $ab->entry_zone_id==$zone_id) {
              $this->getCustomersIdentity()->set('customer_country_id', $ab->entry_country_id, true);
              $this->getCustomersIdentity()->set('customer_zone_id', $ab->entry_zone_id, true);
                /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
                if ($VatOnOrder = \common\helpers\Acl::checkExtension('VatOnOrder', 'resetCustomerData')) {
                    $VatOnOrder::resetCustomerData($ab);
                }

            } else {
              $bab = $this->getCustomersIdentity()->getAddressBook($this->get('billto'));

              if ($bab && $bab->entry_country_id && $bab->entry_country_id==$country_id && $bab->entry_zone_id==$zone_id) {
                $this->getCustomersIdentity()->set('customer_country_id', $bab->entry_country_id, true);
                $this->getCustomersIdentity()->set('customer_zone_id', $bab->entry_zone_id, true);
                /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
                if ($VatOnOrder = \common\helpers\Acl::checkExtension('VatOnOrder', 'resetCustomerData')) {
                    $VatOnOrder::resetCustomerData($bab);
                }
              } else {
              //match w/o zone
                if ($ab && $ab->entry_country_id && $ab->entry_country_id==$country_id) {
                  $this->getCustomersIdentity()->set('customer_country_id', $ab->entry_country_id, true);
                  $this->getCustomersIdentity()->set('customer_zone_id', $ab->entry_zone_id, true);
                    /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
                    if ($VatOnOrder = \common\helpers\Acl::checkExtension('VatOnOrder', 'resetCustomerData')) {
                        $VatOnOrder::resetCustomerData($ab);
                    }
                } elseif ($bab && $bab->entry_country_id && $bab->entry_country_id==$country_id) {
                  $this->getCustomersIdentity()->set('customer_country_id', $bab->entry_country_id, true);
                  $this->getCustomersIdentity()->set('customer_zone_id', $bab->entry_zone_id, true);
                    /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
                    if ($VatOnOrder = \common\helpers\Acl::checkExtension('VatOnOrder', 'resetCustomerData')) {
                        $VatOnOrder::resetCustomerData($bab);
                    }
                }
              }
            }
            break;

        }
      }

    }

/** @prop common\forms\AddressForm $billingForm */
    protected $billingForm;

    public function buildBillingAddressForm() {
        $this->billingForm = new AddressForm(['scenario' => AddressForm::BILLING_ADDRESS]);
    }

    public function getBillingForm($ab_id = null, $preload = true) {
        if (!is_object($this->billingForm)) {
            $this->buildBillingAddressForm();
        }

        if ($preload && !$this->billingForm->hasErrors()) {
            if ($this->isCustomerAssigned()) {
                if ($customer = $this->getCustomersIdentity()) {
                    $_billto = $this->getBillto();
                    if (is_array($_billto)) {
                        $address = $_billto;
                    } else {
                        $address = $customer->getAddressBook($ab_id ?? $_billto);
                    }
                    if ($address) {
                        $this->billingForm->preload($address);
                    } else {
                        $this->billingForm->preloadDefault();
                    }
                }
            } else {
                $_billto = $this->getBillto();
                if (is_array($_billto)) {
                    $this->billingForm->preload($_billto);
                } else {
                    $this->billingForm->preloadDefault();
                }
            }
        }
        return $this->billingForm;
    }

    private function _overrideAddressSelection($addressForm, $addressBook) {
        if ($addressBook) {
            if ($addressForm->scenario == AddressForm::SHIPPING_ADDRESS) {
                $this->set('sendto', $addressBook->address_book_id);
            } else if ($addressForm->scenario == AddressForm::BILLING_ADDRESS) {
                $this->set('billto', $addressBook->address_book_id);
            }
        }
    }

    private function _setPrefferedAddress($addressForm, $addressBook) {
        if ($addressForm->as_preferred && $addressBook) {
            $prevSendto = (int) $this->get('sendto');
            $prevBillto = (int) $this->get('billto');
            $this->_overrideAddressSelection($addressForm, $addressBook);
            if ($prevSendto && $prevBillto && $prevBillto == $prevSendto && (int) $this->get('sendto') != (int) $this->get('billto') && $addressForm->scenario == AddressForm::SHIPPING_ADDRESS) {
                $this->set('billto', $this->get('sendto'));
            }
        }
    }

    public function skipStrongAddressCheck(AddressForm $addressForm){
        $addressForm->setLightCheck(true);
    }

    public function useStrongAddressCheck(AddressForm $addressForm){
        $addressForm->setLightCheck(false);
    }

    public $errorForm = [];

/**
 *
 * @param array $post
 * @param string $type default '' [shipping|billing|else => both] which address to validate
 * @param boolean $shipAsBill default true
 * @param boolean $skipValidation default false
 * @return boolean
 */
    public function validateAddressForms($post, $type = '', $shipAsBill = true, $skipValidation = false) {
        //if ($this->isCustomerAssigned()) {
        $_forms = [];
        if ($type == 'shipping') {
            $_forms[] = $this->getShippingForm(0);
        } else if ($type == 'billing') {
            $_forms[] = $this->getBillingForm(0);
        } else {
            $_forms[] = $this->getBillingForm(0);
            $_forms[] = $this->getShippingForm(0);
        }
        if (is_array($_forms)) {
            $messageStack = \Yii::$container->get('message_stack');
            foreach ($_forms as $key => $addressForm) {
                if (isset($post[$addressForm->formName()])) {
                    $addressForm->load($post);
                    if ($skipValidation && !$addressForm->notEmpty()) {
                        unset($_forms[$key]);
                    }
                } else {
                    unset($_forms[$key]);
                }
            }

            $has_errors = false;
            $updated = false;
            foreach ($_forms as $addressForm) {
                if (/* $addressForm->notEmpty() && */$addressForm->validate() && $this->validateExtensions($addressForm)) {
                    if ($this->isCustomerAssigned()) {
                      /** @var \common\components\Customer $customer*/
                        $customer = $this->getCustomersIdentity();
                        $book = $customer->getAddressFromModel($addressForm);

                        if ($this->checkDifferenceFormAddresses(false)) {
                            if ($addressForm->scenario == AddressForm::BILLING_ADDRESS) {
                                if ($this->checkDifferenceFormAddressesId(false)) {
                                  $dbBook = $addressForm->address_book_id ? $customer->updateAddress($addressForm->address_book_id, $book) : $customer->addAddress($book);
                                } else {
                                    if ($shipAsBill) {
                                        $dbBook = $customer->updateAddress($addressForm->address_book_id, $book);
                                    } else {
                                        $dbBook = $customer->addAddress($book);
                                    }
                                }
                            } else {
                                if ($this->checkDifferenceFormAddressesId(false)) {
                                    $dbBook = $customer->updateAddress($addressForm->address_book_id, $book);
                                } else {
                                    if ($shipAsBill) {
                                        $dbBook = $customer->updateAddress($addressForm->address_book_id, $book);
                                    } else {
                                        $dbBook = $customer->addAddress($book);
                                    }
                                }
                            }
                        } else {//same
                            if (!$updated) {
                                $dbBook = $addressForm->address_book_id ? $customer->updateAddress($addressForm->address_book_id, $book) : $customer->addAddress($book);
                                $updated = true;
                            }
                        }

                        if ($dbBook) {
                            if ($addressForm->as_preferred) {
                                $this->_setPrefferedAddress($addressForm, $dbBook);
                            } else {
                                $this->_overrideAddressSelection($addressForm, $dbBook);
                            }
                            //$this->changeCustomerAddressSelection(($addressForm->scenario == AddressForm::BILLING_ADDRESS ? 'billing': 'shipping'), $dbBook->address_book_id);
                        }
                    } else {
                        $customer = new \common\components\Customer();
                        $book = $customer->getAddressFromModel($addressForm);
                        $book['state'] = $addressForm->state;
                        $book = array_pop(\common\helpers\Address::skipEntryKey([$book]));
                        $country_info = \common\helpers\Country::get_countries($book['country_id'], true);
                        $book['country_iso_code_2'] = $country_info['countries_iso_code_2'];
                        if ($addressForm->scenario == AddressForm::SHIPPING_ADDRESS) {
                            $this->set('sendto', $book);
                            if ($shipAsBill) {
                              $this->set('billto', $book);
                            }
                        } else {
                            $this->set('billto', $book);
                            if ($shipAsBill) {
                              $this->set('sendto', $book);
                            }
                        }
                    }
                }
                if ($addressForm->hasErrors()) {
                    $has_errors = true;
                    $this->errorForm[] = $addressForm->addressType;
                    foreach ($addressForm->getErrors() as $error) {
                        $messageStack->add((is_array($error) ? implode("<br>", $error) : $error), 'one_page_checkout');
                    }
                }
            }
            if ($has_errors) {
                return false;
            }
        }
        //}
        return true;
    }

    /* $address in db format fields */

    public function checkSameAddress($customer, array $address) {
        if ($customer) {
            foreach ($customer->getAddressBooks() as $book) {
                $same = true;
                foreach ($address as $field => $value) {
                    if (property_exists($book, $field) && $book->$field != $value) {
                        $same = false;
                    }
                }
                if ($same) {
                    return $book->address_book_id;
                }
            }
        }
        return false;
    }

    public function validateExtensions($form) {
        $valid = true;

        /**
         * @var $ext \common\extensions\UkrPost\UkrPost
         */
        if ($ext = \common\helpers\Extensions::isAllowed('UkrPost')) {
            $module = $this->getShipping();
            if ($module && $module['module'] == 'ukrpost' && $form->scenario == AddressForm::SHIPPING_ADDRESS) {
                $valid = $ext::validate($form);
            }
        }
        return $valid;
    }

    public function getPlatformId() {
        return ($this->has('platform_id') ? $this->get('platform_id') : \common\classes\platform::currentId());
    }

    public function registerCustomerAccount($opc_temp_account = 0, $contactPreload=false) {
        if (!$this->isCustomerAssigned()) {
            $customer = $this->getCustomersIdentity();
            $shippingAddress = $this->getShippingForm();
            $billingAddress = $this->getBillingForm();
            $contactForm = $this->getCustomerContactForm($contactPreload);
            if (!$shippingAddress->hasErrors() && !$billingAddress->hasErrors() && !$contactForm->hasErrors()) {

                if ($opc_temp_account) {
                    if (property_exists($contactForm, 'opc_temp_account')) {
                        $contactForm->opc_temp_account = !$contactForm->opc_temp_account;
                    }
                    $customer->registerGuestCustomer($contactForm, $billingAddress->notEmpty()? $billingAddress : $shippingAddress);
                    if ( $contactPreload && $customer->customers_id ){
                        $this->assignCustomer($customer->customers_id);
                    }
                } else {
                    $contactForm->opc_temp_account = 0;
                    $customer->registerCustomer($contactForm, true, $billingAddress->notEmpty()? $billingAddress : $shippingAddress);
                }

                $defBook = $customer->getDefaultAddress()->one();
                if ($defBook) {
                    $this->_overrideAddressSelection($billingAddress, $defBook);
                }
                if ($this->checkDifferenceFormAddresses(false)) {
                    if ($shippingAddress->notEmpty()) {
                        $book = $customer->getAddressFromModel($shippingAddress);
                        if ($defBook) {
                            $updated = false;
                            foreach ($customer->getAddressBooks() as $_book) {
                                if ($updated && $_book->address_book_id != $defBook->address_book_id) {
                                    $_book->delete();
                                }
                                if ($_book->address_book_id != $defBook->address_book_id) {
                                    $newBook = $customer->updateAddress($_book->address_book_id, $book);
                                    $updated = true;
                                }
                            }
                            if (!$updated) {
                                $newBook = $customer->addAddress($book);
                            }
                        } else {
                            $newBook = $customer->addDefaultAddress($book);
                        }
                        $this->_overrideAddressSelection($shippingAddress, $newBook);
                    }
                } else {
                    $this->set('sendto', $defBook->address_book_id);
                }

                /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
                if ($VatOnOrder = \common\helpers\Acl::checkExtension('VatOnOrder', 'resetCustomerData')) {
                    $option = \common\helpers\Tax::getTaxAddressOption();
                    $tmp = ($option>0 && $shippingAddress->notEmpty())?$shippingAddress : $billingAddress;
                    $ab = $tmp->attributes;
                    $VatOnOrder::resetCustomerData($ab);
                }

                $this->remove('guest_email_address');
            }
        }
    }

    /**
     * compare shipping and billing addresses (both addresses are loaded and have any different field)
     * @param boolean $preload
     * @return boolean
     */
    private function checkDifferenceFormAddresses($preload = true) {
        $shippingAddress = $this->getShippingForm(null, $preload);
        $billingAddress = $this->getBillingForm(null, $preload);
        $different = false;
        if (!$shippingAddress->notEmpty() || !$billingAddress->notEmpty()) {
            return false;
        }
        if (is_object($shippingAddress) && is_object($billingAddress)) {
            foreach ($shippingAddress->getActiveAttributes() as $name => $value) {
                if ($billingAddress->{$name} != $value) {
                    $different = true;
                }
            }
        }
        return $different;
    }

/**
 * compare shipping and billing addresses by id (both addresses are loaded and have different id)
 * @param boolean $preload
 * @return boolean
 */
    private function checkDifferenceFormAddressesId($preload = true) {
        $shippingAddress = $this->getShippingForm(null, $preload);
        $billingAddress = $this->getBillingForm(null, $preload);
        $different = false;
        if (!$shippingAddress->notEmpty() || !$billingAddress->notEmpty()) {
            return false;
        }
        if (is_object($shippingAddress) && is_object($billingAddress)) {
            if ($shippingAddress->address_book_id && $billingAddress->address_book_id && $shippingAddress->address_book_id != $billingAddress->address_book_id)
                return true;
        }
        return $different;
    }

    public function validateContactForm($post, $admin_edit=false) {
        $form = $this->getCustomerContactForm(false);
        $messageStack = \Yii::$container->get('message_stack');
        if ($form->load($post) && ($admin_edit || $form->validate())) {
            $customer = $this->getCustomersIdentity();
            if ($this->isCustomerAssigned()) {
                $customer->removeDuplicateGuestsAccounts();
                $checkFraud = false;
                if ($customer->customers_email_address != $form->email_address) {
                    $checkFraud = \common\models\Customers::find()->where(['and',
                                ['customers_email_address' => $form->email_address],
                                ['<>', 'customers_id', $customer->customers_id]])
                            ->count();
                }

                if (!$checkFraud) {
                    $multiEmail = $this->get('customer_email_address');
                    foreach ($form->getAttributesByScenario() as $name => $value) {
                        if ($name == 'email_address' && $customer->customers_email_address != $multiEmail) {
                            continue;
                        }
                        if (in_array($name, ['password', 'opc_temp_account']))
                            continue;
                        if ($customer->hasAttribute('customers_' . $name)) {
                            $customer->{'customers_' . $name} = $value;
                        }
                        if ($customer->hasAttribute($name)) {
                            $customer->{$name} = $value;
                        }
                    }
                    $customer->save(false);
                } else {
                    $form->addError('email_address', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
                }
            } else { //guest
                $checkFraud = false;
                if ( $admin_edit ) {
                    $customer = $this->getCustomersIdentity();
                    foreach ($form->getAttributesByScenario() as $name => $value) {
                        if ($customer->hasAttribute('customers_' . $name)) {
                            $customer->{'customers_' . $name} = $value;
                        }
                        if ($customer->hasAttribute($name)) {
                            $customer->{$name} = $value;
                        }
                    }
                }
                /*if ($customer->customers_email_address != $form->email_address) {
                    $checkFraud = \common\models\Customers::find()->where(
                                    ['customers_email_address' => $form->email_address, 'opc_temp_account' => 0])
                            ->count();
                }*/
                if ($checkFraud) {
                    $form->addError('email_address', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
                }
            }
        }
        if ($form->hasErrors()) {
            foreach ($form->getErrors() as $error) {
                $messageStack->add((is_array($error) ? implode("<br>", $error) : $error), 'one_page_checkout');
            }
            return false;
        }
        return true;
    }

    private $totals;

    public function getTotalCollection($reconfig = false) {
        if (!is_object($this->totals)) {
            $this->totals = new \common\classes\order_total($reconfig, $this);
        }
        return $this->totals;
    }

    public function totalCollectPosts($post_data = []) {
        $this->triggerEvents(__FUNCTION__);
        return $this->getTotalCollection()->collect_posts('', $post_data);
    }

    public function totalPreConfirmationCheck() {
        $this->triggerEvents(__FUNCTION__);
        $this->getTotalCollection()->pre_confirmation_check($this->orderInstance);
    }

    public function totalProcess() {
        $this->triggerEvents(__FUNCTION__);
//echo "manager #### <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($this->getAll(), true) . "</PRE>";
//echo "before #### <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($this->getOrderInstance(), true) . "</PRE>";

        foreach (\common\helpers\Hooks::getList('order-manager/total-process') as $filename) {
            include($filename);
        }

        $ret =  $this->getTotalCollection()->process();
//echo "after #### <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($this->getOrderInstance(), true) . "</PRE>";

        if (defined('ALLOW_SEVERAL_TAX_COUNTRIES') && ALLOW_SEVERAL_TAX_COUNTRIES=='True' && is_array($ret)) {

            $order = $this->getOrderInstance();
            $total = $order->info['total_inc_tax'];
            $currency = \Yii::$app->settings->get('currency');
            if (\frontend\design\Info::isTotallyAdmin() && empty($currency) && defined('DEFAULT_CURRENCY')) {
                $currency = DEFAULT_CURRENCY;
            }
            $currencies = \Yii::$container->get('currencies');
            $rate = $currencies->rate($currency);
            if ($rate!=1 && $rate>0) {
                $total *= $rate;
            }

            $skipTaxRates = $orderHash = [];
//echo "\$total $total #### <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($ret, true) ."</PRE>"; die;

            foreach ($ret as $ot) {
                if (!empty($ot['code']) && !in_array($ot['code'], ['ot_tax'])) {
                    $orderHash[]  = [$ot['code'] => $ot['value_exc_vat']];
                }
                if (!empty($ot['code']) && $ot['code']=='ot_tax') { // could be several
                    $ri = \common\helpers\Tax::get_rate_info_from_desc(trim($ot['title'], ':'));
//echo "#### <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($ri, true) ."</PRE>";

                    if (is_array($ri)) {
                        if (isset($ri['max_total']) && is_numeric($ri['max_total']) && $total>=$ri['max_total']) {
                            $skipTaxRates[] = $ri['tax_rates_id'];
                        }
                        if (isset($ri['min_total']) && is_numeric($ri['min_total']) && $total<=$ri['min_total']) {
                            $skipTaxRates[] = $ri['tax_rates_id'];
                        }
                    }
                }

            }

            if (!empty($skipTaxRates)) {
                $skipTaxRates = array_unique($skipTaxRates);
                \Yii::$app->storage->set('skipTaxRates', $skipTaxRates);
                $this->totals = null;
                $this->checkoutOrderWithAddresses();
                $ret =  $this->getTotalCollection()->process();
                \Yii::$app->storage->remove('skipTaxRates');
            }
        }

        return $ret;
    }

    /* use $context as null when wrap is false */

    public function getTotalOutput($wrap = true, $context = null, $all = false) {
        $order = $this->getOrderInstance();
        if ($order->order_id && count($order->totals)) { //probably used for restored order from db
            $order_total_output = $order->totals;
            if ($order->isPaidUpdated) {
                $repocessed_total_output = $this->totalProcess();
                if ($repocessed_total_output) {
                    $order_total_output = array_replace($order_total_output, $repocessed_total_output);
                }
            }
        } else {
            $order_total_output = $this->totalProcess();
        }
        return ($wrap ? $this->wrapTotals($order_total_output, $context, $all) : $order_total_output);
    }

    public function wrapTotals(array $order_total_output, $context, $all = false) {
        $result = [];
        foreach ($order_total_output as $total) {
            $module = $this->getTotalCollection()->get($total['code'], $all);
            if (isset($module) && method_exists($module, 'visibility')) {
                if ($module->visibility($this->getPlatformId(), $context)) {
                    if (method_exists($module, 'displayText')) {
                        $result[] = $module->displayText($this->getPlatformId(), $context, $total);
                    } else {
                        $result[] = $total;
                    }
                }
            }
        }
        return $result;
    }

    public function getCreditModules() {
        $currencies = \Yii::$container->get('currencies');
        $credit_classes = $this->getTotalCollection()->getCreditClasses();
        $credit_modules = array(
            'applied_coupon_code' => ($this->has('cc_id') && $this->get('cc_id') > 0 ? ($this->has('cc_code') && !empty($this->get('cc_code'))?$this->get('cc_code'):\common\helpers\Coupon::get_coupon_name($this->get('cc_id'))) : ''),
            'credit_amount_formatted' => $currencies->format(0),
            'credit_amount' => 0,
            'cot_gv_active' => $this->has('cot_gv'),
            'custom_gv_amount' => ($this->has('cot_gv') && is_numeric($this->get('cot_gv')) ? round($this->get('cot_gv') * $currencies->get_market_price_rate(DEFAULT_CURRENCY, \Yii::$app->settings->get('currency')), 2) : ''),
        );
        if (is_array($credit_classes)) {
            foreach ($credit_classes as $code => $module) {
                $credit_modules[$code] = true;
            }
        }

        if ($credit_modules['ot_gv']) {
            if ($this->isCustomerAssigned()) {
                $customer = $this->getCustomersIdentity();
                $credit_modules['credit_amount'] = $currencies->format_clear($customer->credit_amount);
                if (!$credit_modules['credit_amount'])
                    $credit_modules['credit_amount'] = 0;
                $credit_modules['credit_amount_formatted'] = $currencies->format($customer->credit_amount);
            }
        }
        return $credit_modules;
    }

    private function triggerEvents($method) {
        if ($this->has('events')) {
            $events = $this->get('events');
            if (is_array($events)) {
                foreach ($events as $event) {
                    $response = null;
                    if (isset($event['before']) && $method == $event['before']) {
                        $ot_module = $this->getTotalCollection()->get($event['module']);
                        if ($ot_module && method_exists($ot_module, $event['method'])) {
                            $ot_module->config(array(
                                'ONE_PAGE_CHECKOUT' => 'True',
                                'ONE_PAGE_SHOW_TOTALS' => 'true',
                                'COUPON_SUCCESS_APPLY' => 'true',
                                'GV_SOLO_APPLY' => 'true'
                            ));
                            $response = $ot_module->{$event['method']}($event['data']);
                            if ($response) {
                                if (is_array($response)) {
                                    \Yii::$container->get('message_stack')->add($response['message'], (isset($event['message_class']) ? $event['message_class'] : 'one_page_checkout'), ($response['error'] ? 'error' : 'success'));
                                }
                            }
                        }
                    }
                    $this->removeEvent($event);
                }
            }
        }
    }

    private function removeEvent($event) {
        $events = $this->get('events');
        if (is_array($events)) {
            foreach ($events as $key => $_event) {
                if ($event == $_event) {
                    unset($events[$key]);
                }
            }
            $this->set('events', $events);
        }
        //echo'<pre>';print_r($this->get('events'));
    }

    private $contactForm;
    public $createAccount = false;

    public function buildContactForm() {
        if ($this->createAccount) {
            $this->contactForm = new CustomerRegistration(['scenario' => CustomerRegistration::SCENARIO_CREATE, 'shortName' => CustomerRegistration::SCENARIO_CREATE]);
        } else {
            $this->contactForm = new CustomerRegistration(['scenario' => CustomerRegistration::SCENARIO_CHECKOUT, 'shortName' => CustomerRegistration::SCENARIO_CHECKOUT]);
            if (in_array('admin', $this->getModulesVisibility())) {
                $this->contactForm->useExtending = true;
            }
        }
    }

    public function getCustomerContactForm($preload = true) {
        if (!is_object($this->contactForm)) {
            $this->buildContactForm();
        }
        if ($preload) {
            $customer = $this->getCustomersIdentity();
            if (!$customer->isNewRecord || $customer->get('fromOrder') || $this->has('guest_email_address')) {
                $this->contactForm->preloadCustomersData($customer);
                //$this->contactForm->email_address = $this->get('customer_email_address');
                //$this->contactForm->firstname = $this->get('customer_first_name');
                //$this->contactForm->lastname = $this->get('customer_last_name');
            }
        }
        return $this->contactForm;
    }

    /** @var \common\classes\extended\OrderAbstract $orderInstance */
    private $orderInstance;

    //create empty instance of order
    public function createOrderInstance($class) {
        $class = new \ReflectionClass($class);
        $instance = $class->newInstanceWithoutConstructor();
        $this->orderInstance = $instance;
        $this->orderInstance->manager = $this;
        return $this->orderInstance;
    }

    /* be carefull, it may return as order from cart as well from db */

    public function getOrderInstance() {
        if (!is_object($this->orderInstance))
            throw new \Exception('Order instance is not defined');
        return $this->orderInstance;
    }

    /*
     * Use only in particaular case
     * Substitution for current orderInstance
     * @params OrderAbstract $newInstance
     */
    public function replaceOrderInstance(\common\classes\extended\OrderAbstract $newInstance){
        //if ($this->isInstance()){
            $this->orderInstance = $newInstance;
            $this->orderInstance->manager = $this;
        //}
    }

    public function isInstance() {
        return is_object($this->orderInstance);
    }

    public function getInstanceType(){
        if ($this->isInstance()){
            switch ($this->orderInstance->table_prefix){
                case "quote_":
                    return 'quote';
                case "sample_":
                    return 'sample';
                case "purchase_":
                    return 'purchase';
                default:
                    return 'order';
            }
        }
        return false;
    }

    public function clearOrderInstance() {
        $this->orderInstance = null;
    }

    public function getOrderInstanceWithId($class, $id) {
        if (!$this->orderInstance || !$this->orderInstance->order_id || $this->orderInstance->order_id != $id) {
            $this->createOrderInstance($class)->__construct($id);
        }
        return $this->orderInstance;
    }

    public function defineOrderTaxAddress() {
        $bAddress = $this->getBillingAddress();
        $sAddress = $this->getDeliveryAddress();
        $scheck = \common\helpers\Tax::getTaxZones($sAddress['country_id'] ?? null, $sAddress['zone_id'] ?? null);
        $bcheck = \common\helpers\Tax::getTaxZones($bAddress['country_id'] ?? null, $bAddress['zone_id'] ?? null);
        $address = null;
        if (($bcheck && $scheck) || $bcheck) {
            $address = $bAddress;
        } else if ($scheck) {
            $address = $sAddress;
        } else {
            $address = $bAddress;
        }
        if ($address && $this->isInstance()) {
            $this->orderInstance->tax_address = [
                'entry_country_id' => (isset($address['entry_country_id']) ? $address['entry_country_id'] : 0),
                'entry_zone_id' => (isset($address['entry_zone_id']) ? $address['entry_zone_id'] : 0),
                'postcode' => (isset($address['postcode']) ? $address['postcode'] : ''),
                'company_vat' => (isset($address['company_vat']) ? $address['company_vat'] : ''),
                'company_vat_date' => (isset($address['company_vat_date']) ? $address['company_vat_date'] : ''),
                'company_vat_status' => (isset($address['company_vat_status']) ? $address['company_vat_status'] : ''),
                'customs_number' => (isset($address['customs_number']) ? $address['customs_number'] : ''),
                'customs_number_date' => (isset($address['customs_number_date']) ? $address['customs_number_date'] : ''),
                'customs_number_status' => (isset($address['customs_number_status']) ? $address['customs_number_status'] : '')
            ];
        }
    }

    public function prepareOrderInfo() {
        $this->getOrderInstance()->prepareOrderInfo();
    }

    public function prepareOrderProducts() {
        $this->getOrderInstance()->prepareProducts();
    }

    public function prepareOrderTotals() {
        $this->getOrderInstance()->prepareOrderInfoTotals();
    }

    public function prepareOrderAddresses() {
        $this->getOrderInstance()->prepareOrderAddresses();
    }

    public function checkoutOrder() {
        $this->prepareOrderInfo();
        $this->prepareOrderProducts();
        $this->prepareOrderTotals();
    }

    public function checkoutOrderWithAddresses() {
        /* calls
          $this->prepareOrderInfo();
          $this->prepareOrderAddresses();
          $this->prepareOrderProducts();
          $this->prepareOrderTotals();
         */
  //      echo "#### <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($this->getOrderInstance(), true) . "</PRE>";

        $this->getOrderInstance()->cart();
//        echo "#### <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($this->getOrderInstance(), true) . "</PRE>";
//        die;
        }

    private $_renderPath = "\\frontend\\design\\boxes\\checkout\\";

    public function setRenderPath($path) {
        $this->_renderPath = $path;
    }

    public function render($widget, $params = [], $format = 'html', $path = null) {
        if (is_array($this->modulesVisiblility)) {
            $widget = ($path ? $path : $this->_renderPath ) . $widget;
            $_params = [];
            foreach ($params as $key => $param) {
                if (property_exists($widget, $key)) {
                    $_params[$key] = $param;
                }
            }
            $params = $_params;
            if (!array_intersect(['admin', 'pos'], $this->modulesVisiblility)) {
                //frontend
                //$_path = "\\frontend\\design\\boxes\\checkout\\";
                //$widget = ($path ? $path : $this->_renderPath ) . $widget;
                if (class_exists($widget)) {
                    if ($format == 'html') {
                        return $widget::widget($params);
                    } else if ($format == 'json') {
                        $object = new $widget;
                        Yii::configure($object, $params);
                        return $object->run();
                    }
                }
            } else { // if no difference admin <=> forntend, move to one block
                //$widget = ($path ? $path : $this->_renderPath ) . $widget;
                if (class_exists($widget)) {
                    if ($format == 'html') {
                        return $widget::widget($params);
                    } else if ($format == 'json') {
                        $object = new $widget;
                        Yii::configure($object, $params);
                        return $object->run();
                    }
                }
            }
        }
    }

    public function clearAfterProcess() {
        if ($this->hasCart()) {
            $this->_cart->reset(true);
            $this->_cart->order_id = 0;
            if (($multiCart = \common\helpers\Extensions::isAllowed('MultiCart')) && empty($this->_cart->table_prefix)){
                $multiCart::reassignCart($this->_cart->basketID);
            } else {
                $this->_cart->setBasketId(0);
            }
        }
        $this->remove('sendto');
        $this->remove('billto');
        $this->remove('shipping');
        $this->remove('sendto');
        $this->remove('payment');
        $this->remove('comments');
        $this->remove('purchase_order');
        $this->remove('shippingparam');
        $this->remove('pointto');
        $this->remove('credit_covers');
        $this->remove('estimate_ship');
        $this->remove('estimate_bill');
        $this->remove('shipping_choice');
        $this->remove('cartID');
        $this->remove('sampleID');
        $this->remove('quoteID');
        $this->remove('guest');
        $this->remove('account');
        $this->remove('order_delivery_date');
        $this->remove('pay_order_id');
        $this->getTotalCollection()->clear_posts();
    }

    public function clearStorage() {
        $this->storage->removeAll();
    }

    public function prepareEstimateData() {

        static $estimate_data = null;

        if (is_null($estimate_data)) {

            $this->triggerEvents(__FUNCTION__);

            $currencies = \Yii::$container->get('currencies');

            $addresses = [];
            $addresses_selected_value = 0;
            if ($this->isCustomerAssigned()) {
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
                    $addresses = $this->getCustomersAddresses(true, true, 'shipping');
                    $addresses_selected_value = $this->getSendto();
                    if (!$addresses_selected_value) {
                        $addresses_selected_value = $this->getCustomersIdentity()->customers_shipping_address_id;
                        if ($addresses_selected_value) {
                            $this->set('sendto', $addresses_selected_value);
                        }
                    }
                    
                    $addressesBilling = $this->getCustomersAddresses(true, true, 'billing');
                    $billing_selected_value = $this->getBillto();
                    
                    if (!$billing_selected_value) {
                        $billing_selected_value = $this->getCustomersIdentity()->customers_default_address_id;
                        if ($billing_selected_value)
                            $this->set('billto', $billing_selected_value);
                    }
                    
                } else {
                    $addresses = $this->getCustomersAddresses(true, true);
                    $addresses_selected_value = $this->getSendto();
                    if (!$addresses_selected_value) {
                        $addresses_selected_value = $this->getCustomersIdentity()->customers_default_address_id;
                        if ($addresses_selected_value)
                            $this->set('sendto', $addresses_selected_value);
                    }
                }
            }
            if (defined('PREFERRED_CHEAPEST_TYPE')){
                if (PREFERRED_CHEAPEST_TYPE == 'delivery'){
                    $this->getShippingCollection()->useDeliveryCheapest();
                } else {
                    $this->getShippingCollection()->usePickupCheapest();
                }
            }

            $this->getShippingQuotesByChoice();

            /*if ($_predefined = $this->getSelectedShipping()) {
                $this->setSelectedShipping($_predefined);
            }*/


            $selected_shipping = $this->getShipping();
            if ($selected_shipping) {
                $this->reverseChoiceByShipping($selected_shipping);
            }

            $this->checkoutOrderWithAddresses();

            $estimate = [];
            if ($this->has('estimate_ship')) {
                $estimate = $this->get('estimate_ship');
            }

            $estimate_data = array(
                'is_logged_customer' => $this->isCustomerAssigned(),
                'estimate' => $estimate,
                'countries' => \common\helpers\Country::get_countries('', false, '', 'ship'),
                'addresses' => $addresses,
                'addresses_selected_value' => $addresses_selected_value,
                'cart_weight' => rtrim(rtrim(number_format($this->_cart->show_weight(), 2, '.', ''), '0'), '.'),
                'manager' => $this,
            );
        }

        return $estimate_data;
    }

    /* use for after proccessing coupons, vouchers
     * event = [
     *  'before' => start before manager method
     *  'module' => get total module
     *  'method' => run total module method
     *  'data' => data fo method
     * ]
     */

    public function addEvent() {
        $events = $this->has('events') ? $this->get('events') : [];
        $args = func_get_args();
        if (is_array($args[0])) {
            array_push($events, $args[0]);
            $this->set('events', $events);
        }
    }

    public function getCouponName() {
        if ($this->has('cc_id')) {
          if ($this->has('cc_code') && !empty($this->get('cc_code') )) {
            return $this->get('cc_code');
          }
            return \common\helpers\Coupon::get_coupon_name($this->get('cc_id'));
        }
        return '';
    }

    private $_template;

    public function setTemplate($template) {
        $this->_template = $template;
    }

    public function getTemplate() {
        return $this->_template;
    }

    /* used in admin section */

    public function predefineOrderDetails() {
        if (is_object($this->_cart) && $this->_cart->order_id) {
            if ($this->has('order_instance')) {
                try {
                    $instance = $this->get('order_instance');
                    $_order = new $instance($this->_cart->order_id);

                    if (is_object($_order)) {
                        if (!$this->isCustomerAssigned() || $this->getCustomerAssigned() != $_order->customer['customer_id']) {
                            $this->predefineCustomerDetails($_order->customer['customer_id']);
                        }

                        if (!$this->isCustomerAssigned()){ //restored customer from order may be absent
                            $customer = $this->getCustomersIdentity();
                            foreach($_order->customer as $field => $value){
                                if (is_scalar($value)){
                                    if ($customer->hasAttribute('customers_'.$field)){
                                        $customer->setAttribute('customers_'.$field, $value);
                                    } else {
                                        $customer->set($field, $value);
                                    }
                                } else if (is_array($value)){
                                    $customer->set($field, $value);
                                }
                                //!!!potential danger, stored customer has customers_id, do not recreate account
                            }
                            if ($_order->customer['customer_id']){
                                $this->set('customer', $customer);
                            }
                        }

                        $this->set('platform_id', $_order->info['platform_id']);
                        $this->set('languages_id', $_order->info['language_id']);
                        $this->set('currency', $_order->info['currency']);
                        $this->_cart->setPlatform($_order->info['platform_id']);
                        $this->_cart->setCurrency($_order->info['currency']);
                        $this->_cart->setLanguage($_order->info['language_id']);

                        if ($this->isCustomerAssigned()) {
                            $customer = $this->getCustomersIdentity();
                            $this->_cart->setCustomer($customer->customers_id);
                            if ($_order->delivery['address_book_id']) {
                                $aBook = $customer->getAddressBook($_order->delivery['address_book_id'], true);
                                if ($aBook) {
                                    $_delivery = [];
                                    foreach($_order->delivery as $key => $v){
                                        $_delivery["entry_" . $key] = $v;
                                    }
                                    if (\common\helpers\Address::cmpAddresses($aBook, $_delivery)){
                                        $this->changeCustomerAddressSelection('shipping', $aBook['address_book_id']);
                                    } else {
                                        $this->set('sendto', $_order->delivery);
                                    }
                                } else {
                                    if ($_order->delivery) {
                                        $this->set('sendto', $_order->delivery);
                                    }
                                }
                            }
                            if ($_order->billing['address_book_id']) {
                                $aBook = $customer->getAddressBook($_order->billing['address_book_id'], true);
                                if ($aBook) {
                                    $_billing = [];
                                    foreach($_order->billing as $key => $v){
                                        $_billing["entry_" . $key] = $v;
                                    }
                                    if (\common\helpers\Address::cmpAddresses($aBook, $_billing)){
                                        $this->changeCustomerAddressSelection('billing', $aBook->address_book_id);
                                    } else {
                                        $this->set('billto', $_order->billing);
                                    }
                                } else {
                                    if ($_order->billing) {
                                        $this->set('billto', $_order->billing);
                                    }
                                }
                            }
                        } else {
                            if ($_order->delivery) {
                                $this->set('sendto', $_order->delivery);
                                $this->set('estimate_ship', ['country_id' => $_order->delivery['country_id'], 'postcode' => $_order->delivery['postcode'], 'zone' => $_order->delivery['state'], 'company_vat' => $_order->delivery['company_vat'], 'company_vat_date' => $_order->delivery['company_vat_date'] ?? null, 'company_vat_status' => $_order->delivery['company_vat_status'], 'customs_number' => $_order->delivery['customs_number'], 'customs_number_date' => $_order->delivery['customs_number_date'] ?? null, 'customs_number_status' => $_order->delivery['customs_number_status']]); // need here as bill as ship just copies out manager's data
                            }
                            if ($_order->billing) {
                                $this->set('billto', $_order->billing);
                                $this->set('estimate_bill', ['country_id' => $_order->billing['country_id'], 'postcode' => $_order->billing['postcode'], 'zone' => $_order->billing['state'], 'company_vat' => $_order->billing['company_vat'], 'company_vat_date' => $_order->billing['company_vat_date'] ?? null, 'company_vat_status' => $_order->billing['company_vat_status'], 'customs_number' => $_order->billing['customs_number'], 'customs_number_date' => $_order->billing['customs_number_date'] ?? null, 'customs_number_status' => $_order->billing['customs_number_status']]
                                    );
                            }
                        }
                        $this->resetDeliveryAddress();
                        $this->resetBillingAddress();
                        if ($_order->info['shipping_class']) {
                            $this->setSelectedShipping($_order->info['shipping_class']);
                            if (!$this->has('shipping')) {
                                $this->_cart->clearTotalKey('ot_shipping');
                            }
                        }

                        if ($_order->info['payment_class']) {
                            $this->setPayment($_order->info['payment_class']);
                        }

                    }
                } catch (\Exception $ex) {
                    \Yii::warning('Order Instance is not defined:'.$ex->getMessage()."\n".$ex->getTraceAsString());
                    throw new \Exception('Order Instance is not defined');
                }
            }
        }
    }

    public function predefineCustomerDetails($customers_id, $withAddress = false) {
        $this->assignCustomer($customers_id); //reassign customer
        $customer = $this->getCustomersIdentity();
        if ($customer) {
            if ($this->isCustomerAssigned()){
                $customer->loadCustomer($customer->customers_id);
                $customer->convertToSession();
                if ($withAddress) {
                    $defBook = $customer->getDefaultAddress()->one();
                    if ($defBook) {
                        $this->set('sendto', $defBook->address_book_id);
                        $this->set('billto', $defBook->address_book_id);
                    } else {
                        $this->remove('sendto');
                        $this->remove('billto');
                    }
                }
            }
        }
    }

    private $instanceParent = null;
    private function createInstanceParent($class){
        $class = new \ReflectionClass($class);
        $instance = $class->newInstanceWithoutConstructor();
        $this->instanceParent = $instance;
        $this->instanceParent->manager = $this;
        return $this->instanceParent;
    }

    public function getParentToInstance($class){

        if (is_null($this->instanceParent)){
            if (!is_object($this->orderInstance))
            throw new \Exception('Order instance is not defined');

            $this->createInstanceParent($class);

            $this->instanceParent->info = $this->orderInstance->info;
            $this->instanceParent->totals = $this->orderInstance->totals;
            $this->instanceParent->products = $this->orderInstance->products;
            $this->instanceParent->customer = $this->orderInstance->customer;
            $this->instanceParent->delivery = $this->orderInstance->delivery;
            $this->instanceParent->billing = $this->orderInstance->billing;
            $this->instanceParent->content_type = $this->orderInstance->content_type;
            $this->instanceParent->tax_address = $this->orderInstance->tax_address;
        }

        return $this->instanceParent;
    }

    public function getParentToInstanceWithId($class, $id){
        if (!$this->instanceParent || !$this->instanceParent->order_id || $this->instanceParent->order_id != $id) {
            $this->createInstanceParent($class)->__construct($id);
        }
        return $this->instanceParent;
    }

    public function checkShippingIsValid() {
        $shipping = $this->getShipping();
        if ($shipping['id']=='free_free' && $this->checkFreeShipping()) {
          return true;
        }
        foreach ($this->getAllShippingQuotes() as $quote) {
            if (($quote['error'] ?? false)) continue;
            if ($quote['id'] == $shipping['module'] && is_array($quote['methods'])) {
                foreach ($quote['methods'] as $method) {
                    if ($shipping['id'] == $quote['id'] . '_' . $method['id']) {
                        if (round($shipping['cost'], 1) == round($method['cost'], 1) || round($shipping['cost'], 2) == round($method['cost'], 2)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    private $_tm;
    /*
    * SubManager to wotk with payment transactions
    * @params object $payment to work with api
    * return TransactionManager instance
    */
    public function getTransactionManager(\common\classes\modules\ModulePayment $payment = null){
        if (is_null($payment)){
            $payment = $this->getPaymentCollection()->getSelectedPayment();
        }

        if (!$this->_tm){
            $this->_tm = new PaymentTransactionManager($this, $payment);
        }
        return $this->_tm;
    }

    private $_splitter;

    /**
    * SubManager to make splinter from current order
    * return SplitterManager instance
    */
    public function getOrderSplitter(){
        if (!$this->_splitter){
            $this->_splitter = new SplitterManager($this);
        }
        return $this->_splitter;
    }

    /**
     * @param bool $softShippingValidation
     * @return OrderManager
     */
    public function setSoftShippingValidation(bool $softShippingValidation): self
    {
        $this->softShippingValidation = $softShippingValidation;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSoftShippingValidation(): bool
    {
        return $this->softShippingValidation;
    }

    public function cleanupTemporaryGuests()
    {
        $CustomersTemporaryGuestArray = \common\models\CustomersTemporaryGuest::find()->where('expiration < "' . time() . '"')->all();
        foreach ($CustomersTemporaryGuestArray as $customerInfo) {
            \common\models\Orders::updateAll(['customers_id' => 0], ['customers_id' => $customerInfo->customers_id]);
            \common\models\AdminShoppingCarts::deleteAll(['customers_id' => $customerInfo->customers_id]);
            \common\helpers\Customer::deleteCustomer($customerInfo->customers_id, false);
            $customerInfo->delete();
        }
    }

    public function updateSummaryFields(): bool
    {
        $cart = $this->getCart();
        if (is_object($cart)) {
            $prevCount = $this->get('total_count');
            $prevWeight = $this->get('total_weight');
            $newCount = $cart->count_contents();
            $newWeight = $cart->show_weight();
            $changed = $prevCount !== $newCount || $prevWeight !== $newWeight;
            if ($changed) {
                $this->set('total_weight', $newWeight);
                $this->set('total_count', $newCount);
                return true;
            }
        }
        return false;
    }

    public function isPaymentAllowedEx($includeRefund = false, &$reason = null)
    {
//        if (!$this->isCustomerAssigned()) {
//            $reason = 'Customer is not assigned';
//            return false;
//        }
        $cart = $this->getCart();
        if (!is_object($cart) || $cart->isEmptyProducts()) {
            $reason = 'Cart is empty';
            return false;
        }

        $amountDueCost = 1;
        $totals = $this->getTotalOutput(false);
        if (is_array($totals)) {
            $amountDueCost = \common\helpers\Php::arrayGetSubArrayBySubValue($totals, 'code', 'ot_due');
            if (empty($amountDueCost)) { // ot_due maybe disabled
                $amountDueCost = \common\helpers\Php::arrayGetSubArrayBySubValue($totals, 'code', 'ot_total')['value_inc_tax'] ?? 0;
            } else {
                $amountDueCost = \common\helpers\Php::arrayGetSubArrayBySubValue($totals, 'code', 'ot_due')['value_inc_tax'] ?? 0;
            }
        }
        $res = $amountDueCost > 0 || ($includeRefund && $amountDueCost < 0);
        if (!$res) {
            $reason = 'Amount due = ' . $amountDueCost;
        }
        return $res;
    }

    public function isPaymentSelected(&$reason = null)
    {
        $res = (bool) $this->getPayment();
        if (!$res) {
            $reason = 'Payment is not selected';
        }
        return $res;
    }

    public function isPaymentAllowed($includeRefund = false)
    {
        return $this->isPaymentAllowedEx($includeRefund);
    }

    public function isPaymentAllowedTpl($includeRefund = false)
    {
        $reason = '';
        $res['allowed'] = $this->isPaymentAllowedEx($includeRefund, $reason);
        $res['reason'] = $reason;
        return $res;
    }

    public function isShippingAllowed()
    {
        $cart = $this->getCart();
        return is_object($cart) && !$cart->isEmptyProducts();
    }

    public static function getCombineShippingsDefault()
    {
        if (class_exists('\common\helpers\Extensions') && method_exists(\common\helpers\Extensions::class, 'isAllowed') && ($ext = \common\helpers\Extensions::isAllowed('CollectionPoints')) && method_exists($ext, 'isSeparateShipping')) {
            return !$ext::isSeparateShipping();
        } else {
            return !defined('SHIPPING_SEPARATELY') || (defined('SHIPPING_SEPARATELY') && SHIPPING_SEPARATELY == 'false');
        }
    }

    public function getOrderTaxRates($classId = null)
    {
        return \common\helpers\Tax::getOrderTaxRates($classId, $this->getTaxCountry(), $this->getTaxZone(), '', true, $this->getCustomersIdentity()->groups_id ?? 0);
    }


    private function getCaptchaType()
    {
        if (isset($this->captcha_type)) return $this->captcha_type;

        $this->captcha_type = $this->captcha_widget = null;
        if (!defined('CAPTCHA_ON_CREATE_ACCOUNT') || CAPTCHA_ON_CREATE_ACCOUNT != 'True') return null;

        if (defined('PREFERRED_USE_RECAPTCHA') && PREFERRED_USE_RECAPTCHA == 'True') {
            $captcha = new \common\classes\ReCaptcha();
            if ($captcha->isEnabled()) {
                $this->captcha_widget = \frontend\design\boxes\ReCaptchaWidget::widget();
                $this->captcha_type = 'recaptcha';
                $this->captcha = $captcha;
            }
        }
        if (empty($this->captcha_widget)) {
            $this->captcha_type = 'captcha';
            $this->captcha_widget = \yii\captcha\Captcha::widget([
                'model' => $this->getCustomerContactForm(),
                'attribute' => 'captcha',
//                'captchaAction' => 'site/captcha_order',
            ]);
        }
        return $this->captcha_type;
    }


    /**
     * The second capcha widget on checkout form
     * @return string|void|null
     * @throws \Throwable
     */
    public function createCaptchaWidget()
    {
        $this->getCaptchaType();
        return $this->captcha_widget;
    }

    public function validateCaptcha($params)
    {
        if (!\Yii::$app->user->isGuest) return true;
        if (!($params['checkout']['opc_temp_account']??false)) return true;
        $errorMsg = null;
        $res = true;
        switch ($this->getCaptchaType()) {
            case 'captcha':
                $value = $params['checkout']['captcha'] ?? null;
                $res = (new \yii\captcha\CaptchaValidator())->validate($value, $errorMsg);
                break;
            case 'recaptcha':
                $res = $this->captcha->checkVerification($params['g-recaptcha-response'] ?? null);
                break;
        }
        if (!$res) {
            $messageStack = \Yii::$container->get('message_stack');
            $messageStack->add($errorMsg ?? UNSUCCESSFULL_ROBOT_VERIFICATION, 'one_page_checkout');
        }
        return $res;
    }

}
