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

namespace common\components;

use Yii;
use common\classes\opc;
use common\components\Socials;
use common\models;
use frontend\forms\registration\CustomerRegistration;
use common\helpers\Date as DateHelper;

class Customer  extends \common\models\Customers implements \yii\web\IdentityInterface {

    const LOGIN_STANDALONE = 1;
    const LOGIN_RECOVERY = 2;
    const LOGIN_SOCIALS = 3;
    const LOGIN_WITHOUT_CHECK = 4;

    private $loginType;
    private $isMulti = 0;
    private $data = [];
    private $temporary = [];
    protected $_customersInfo = null;
    //protected $authKey;
    //protected $auth_key; there must be the field in DB => model\Customers
    public $rememberMe = false;

    public function __construct($type = 0) {
        $this->setLoginType($type);
        $this->storage = Yii::$app->get('storage');
    }

    public function setLoginType($type){
        $this->loginType = $type;
    }

    /**
     *
     * @param string $checkParam
     * @return boolean|0
     */
    public function validateCustomer($checkParam){
        $success = true;
        $checkGdpr = true;
        switch ($this->loginType) {
            case static::LOGIN_STANDALONE :
                    $success = \common\helpers\Password::validate_password($checkParam, $this->customers_password, 'frontend');
                break;
            case static::LOGIN_RECOVERY :
                if (!$this->checkValidToken($this->customers_id, $checkParam))
                    $success = false;
                break;
            case static::LOGIN_SOCIALS :
                if ($checkParam != Socials::HASHCODE)
                    $success = false;
                break;
            case static::LOGIN_WITHOUT_CHECK :
                if ($this->customers_id != $checkParam) {
                    $success = false;
                }
                $checkGdpr = false;
                break;
            default :
                $success = false;
                break;
        }

        if ($success && $checkGdpr){
            $this->checkGdpr();
        }

        return $success;
    }

    public function getId()
    {
        return $this->customers_id;
    }

    public function loginCustomerById($cId) {
        if ($this->loginType != static::LOGIN_WITHOUT_CHECK) {
            return false;
        }

        $success = true;
        $this->isMulti = 0;

        $_user = $this->findIdentity($cId);
        if (!$_user) {
            return false;
        }

        return $success;
    }

    public function loginCustomer($email_address, $checkParam) {

        if ( !$this->loginType || !$email_address ) {
            return false;
        }

        $success = true;

        if (!Yii::$app->user->isGuest)
            return $success;

        $this->isMulti = 0;
        /** @var self $_user */
        $_user = $this->findIdentityByEmail($email_address);

        if (!$_user) {
            $_user = $this->findByMultiEmail($email_address);//findByEmail
            $this->isMulti = 1;
        }

        if (!$_user) {
            $this->isMulti = 0;
            \common\models\Fraud::registerAddress();
            return false;
        }

        $_user->loginType = $this->loginType;
        $_user->isMulti = $this->isMulti;

        $success = $_user->validateCustomer($checkParam);
        foreach (\common\helpers\Hooks::getList('customers/login-customer/after-validate') as $filename) {
            include($filename);
        }
        if ($success) {

            if ($this->rememberMe) {
                $duration = \Yii::$app->user->autoLoginDuration;
                if (defined('RememberMe_EXTENSION_DURATION') && intval(RememberMe_EXTENSION_DURATION)>0) {
                    $duration = intval(RememberMe_EXTENSION_DURATION);
                }
                \Yii::$app->user->login($_user, $duration);
            }

            $_user->_afterAuth();
            \common\models\Fraud::cleanAddress();
        } elseif ($success === false) {
            \common\models\Fraud::registerAddress();
        }
        foreach (\common\helpers\Hooks::getList('customers/login-customer') as $filename) {
            include($filename);
        }
        return $success;
    }

    private function _afterAuth(){
        global $cart, $quote, $sample;

        if ($this->customers_id){

            if ( is_object($cart) ) $cart->before_restore();


            if (is_object($cart)) {
                $cart->before_restore();
            }

            if (SESSION_RECREATE == 'True') {
                tep_session_recreate();
            }
            Yii::$app->storage->setPointer(Yii::$app->storage->getPointer());

            Yii::$app->user->login($this);

            $this->setCustomersData();
            
            if ($this->customers_id) {
                $addressBook = $this->getDefaultAddress()->one();
                if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
                    $shippingAddressBook = $this->getDefaultShippingAddress()->one();
                } else {
                    $shippingAddressBook = $addressBook;
                }
                if ( \common\helpers\Acl::checkExtensionAllowed('DealersMultiCustomers', 'allowed')) {
                    $multi_customer_id = $this->data['multi_customer_id'] ?? 0;
                    if ($multi_customer_id > 0) {
                        $user = \common\extensions\DealersMultiCustomers\models\Users::find()->where(['user_id' => $multi_customer_id])->one();
                        if ($user && $user->customers_shipto > 0) {
                            $shippingAddressBook = models\AddressBook::findOne($user->customers_shipto);
                        }
                        unset($user);
                    }
                }
                Yii::$app->get('storage')->set('billto', $addressBook->address_book_id ?? null);
                Yii::$app->get('storage')->set('sendto', $shippingAddressBook->address_book_id ?? null);
            }else{
                Yii::$app->get('storage')->remove('billto');
                Yii::$app->get('storage')->remove('sendto');
            }
            //Yii::$app->get('storage')->remove('payment');
            Yii::$app->get('storage')->remove('comments');
            Yii::$app->get('storage')->remove('credit_covers');
            Yii::$app->get('storage')->remove('order_delivery_date');

            $this->convertToSession();

            $this->updateAccess();

            // restore cart contents
            if (is_object($cart)) {
                $cart->restore_contents();
            }

            foreach (\common\helpers\Hooks::getList('customers/after-auth') as $filename) {
                include($filename);
            }

        }
    }

    public function logoffCustomer(){
        Yii::$app->user->logout(false);
        $this->clearAllParams();
        foreach (\common\helpers\Hooks::getList('customers/logoff') as $filename) {
            include($filename);
        }
    }

    public function updateAccess(){
        if ($this->customers_id){
            $info = models\CustomersInfo::findOne(['customers_info_id' => $this->customers_id]);
            if ($info){
                $info->customers_info_date_of_last_logon = date("Y-m-d");
                $info->customers_info_number_of_logons++;
                $info->update();
            }
        }
    }

    public function checkGdpr(){
        $gdpr = new Gdpr($this);
        return $gdpr->processGdprChecking();
    }

    public function getAddressBooks($toArray = false, $woDropShip = false, $type = '') {
        $ab = parent::getAddressBooks();
        if ($woDropShip) {
            $ab->andWhere(['drop_ship' => 0]);
        }
        $allowMulti = false;
        if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
            if (!empty($type)) {
                switch ($type) {
                    case 'custom':
                        $ab->andWhere(['entry_type' => \common\forms\AddressForm::CUSTOM_ADDRESS]);// 1
                        break;
                    case 'shipping':
                        $ab->andWhere(['entry_type' => \common\forms\AddressForm::SHIPPING_ADDRESS]);// 2
                        $allowMulti = true;
                        break;
                    case 'billing':
                        $ab->andWhere(['entry_type' => \common\forms\AddressForm::BILLING_ADDRESS]);//3
                        break;
                    default:
                        break;
                }
            }
        }
        if ($allowMulti &&  \common\helpers\Acl::checkExtensionAllowed('DealersMultiCustomers', 'allowed')) {
            $multi_customer_id = \Yii::$app->get('storage')->get('multi_customer_id');
            if ($multi_customer_id > 0) {
                $shipto = [];
                $addressQuery = \common\extensions\DealersMultiCustomers\models\UsersToAddressBook::find()->where(['user_id' => $multi_customer_id]);
                foreach ($addressQuery->each() as $addressRow) {
                    $shipto[] = $addressRow->address_book_id;
                }
                unset($addressQuery);
                $ab->andWhere(['IN', 'address_book_id', $shipto]);
            }
        }
        if ($toArray) {
            $ab->asArray();
        }
        return $ab->all();
    }

    public function getAddressBook($abId, $toArray = false){
        if ($this->customers_id){
            if ($toArray){
                return parent::getAddressBook($abId)->asArray()->one();
            } else {
                return parent::getAddressBook($abId)->one();
            }
        }
        return null;
    }

    private function checkValidToken($cid, $token) {
        $query_token = tep_db_fetch_array(tep_db_query("select token from " . TABLE_CUSTOMERS_INFO . " where customers_info_id = '" . (int) $cid . "'"));
        if ($query_token) {
            return $query_token['token'] == $token;
        }
        return false;
    }

    protected $storage;

    private function setCustomersData(){
        if ($this->customers_id){
            $this->data['customer_id'] = $this->customers_id;
            $this->data['customer_default_address_id'] = $this->customers_default_address_id;
            $this->data['customer_first_name'] = $this->customers_firstname;
            $this->data['customer_last_name'] = $this->customers_lastname;
            $this->data['customer_email_address'] = $this->customers_email_address;
            $this->data['is_multi'] = $this->isMulti;

            $this->data['multi_customer_id'] = $this->multi_customer_id;

            $addressBook = $this->getDefaultAddress()->one();
            $this->data['customer_country_id'] = $addressBook->entry_country_id ?? null;
            $this->data['customer_zone_id'] = $addressBook->entry_zone_id ?? null;
            if (($addressBook->entry_company_vat_status ?? null) > 1) {
              $this->data['customers_company_vat'] = $addressBook->entry_company_vat;
              $this->data['customers_company_vat_status'] = $addressBook->entry_company_vat_status;
              $this->data['customers_company_vat_date'] = $addressBook->entry_company_vat_date;
            }
            if (($addressBook->entry_customs_number_status ?? null)> 0) {
              $this->data['customers_customs_number'] = $addressBook->entry_customs_number;
              $this->data['customers_customs_number_status'] = $addressBook->entry_customs_number_status;
              $this->data['customers_customs_number_date'] = $addressBook->entry_customs_number_date;
            }

            if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
                $this->data['customer_groups_id'] = $this->groups_id;
            } else {
                $this->data['customer_groups_id'] = 0;
            }
        }
    }

    public function loadCustomer($customer_id) {

        if (!$this->customers_id){
            $_user = $this->findIdentity($customer_id);
            if ($_user){
                $this->setAttributes($_user->getAttributes(), false);
            }
        }

        $this->setCustomersData();

        $this->data['currency_id'] = \Yii::$app->settings->get('currency_id');
        $this->data['currency'] =  \Yii::$app->settings->get('currency');

        return $this;
    }

    public function get($name){
        $this->data[$name] = $this->data[$name] ?? $this->storage->get($name);
        return $this->data[$name];
    }

    public function getAll(){
        return $this->data;
    }

    public function set($name, $value, $enableSession = false) {
        $this->data[$name] = $value;
        if ($enableSession){
            $this->storage->set($name, $value);
        }
    }

    public function clearParam($name) {
        unset($this->data[$name]);
        unset($this->temporary[$name]);
    }

    public function clearAllParams() {
        $this->data = [];
        $this->temporary = [];
        $this->storage->removeAll();
    }

    public function convertToSession() {
        if (is_array($this->data) && count($this->data)) {
            foreach ($this->data as $key => $value) {
                $this->set($key, $value, true);
            }
        }
    }
    /*depricated*/
    public function convertBackSession() {
        /*
        if (is_array($this->temporary) && count($this->temporary)) {
            $this->data = [];
            $setUpCurrency = true;
            foreach ($this->temporary as $key => $value) {
                //global $$key;
                if (tep_not_null($value)) {
                    if($key === 'currency' && !empty($value)){
                        $setUpCurrency = false;
                    }
                    $this->data[$key] = $value;
                    unset($GLOBALS[$key]);
                    $_SESSION[$key] = $value;
                    $GLOBALS[$key] = &$_SESSION[$key];
                }
            }
            if($setUpCurrency){
                $GLOBALS['currency'] = DEFAULT_CURRENCY;
            }
        }*/
    }

    public function createCustomerQo($qoModel){

        $firstname = $qoModel->firstname;
        $telephone = $qoModel->telephone;
        $email_address = $qoModel->email_address;

        $login = true;
        /*if($customer = static::findByEmail($email_address)){
            $customer->addPhone($telephone);
        } elseif (!empty($telephone) && $customer = static::findByPhone($telephone)){
            if($email_address){
                $customer->addEmail($email_address);
            }
        } else {/**/
            $login = \common\helpers\Customer::check_need_login($qoModel->group);
            $customer = new self();
            $customer->setAttributes([
                'opc_temp_account' =>1,
                'customers_firstname' => strval($firstname),
                'customers_email_address' => strval($email_address),
                'customers_telephone' => strval($telephone),
                'groups_id' => $qoModel->group,
                'customers_status' => ($login ? 1 : 0),
                'customers_password' => \common\helpers\Password::encrypt_password(\common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH), 'frontend'),
                'platform_id' => \common\classes\platform::currentId(),
                ], false);
            if($customer->save(false)){
                if (!empty($telephone)){
                    (new models\CustomersPhones(['customers_phone' => $telephone]))->link('customer', $customer);
                }
                if (!empty($email_address)){
                    (new models\CustomersEmails(['customers_email' => $email_address]))->link('customer', $customer);
                }
                $customer->addCustomersInfo();
                $address = $customer->getAddressFromModel($qoModel);
                $customer->addDefaultAddress($address);
                
                if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
                    $address['entry_type'] = \common\forms\AddressForm::SHIPPING_ADDRESS;
                    $_address = $customer->addAddress($address);
                    if ($_address) {
                        $customer->customers_shipping_address_id = $_address->address_book_id;
                        $customer->save();
                    }
                }
            }
        /*}/**/

        if ($login){
            $customer->_afterAuth();
        }

        return $customer;
    }

    public function increaseCreditAmount(\common\models\Coupons $gv){

        if ($this->customers_id){

            $currencies = Yii::$container->get('currencies');

            $addAmount = ($gv->coupon_amount * $currencies->get_market_price_rate($gv->coupon_currency, DEFAULT_CURRENCY));

            $this->credit_amount += (float)$addAmount;
            $this->save(false);
            $comment = 'Redeem ' . $gv->coupon_code;

            $this->saveCreditHistory($this->customers_id, $addAmount, '+', $gv->coupon_currency, $currencies->currencies[$gv->coupon_currency]['value'], $comment);

            return $this->credit_amount;
        }
        return false;
    }

    /*
    * type = 0 if credit amount, type = 1 if bonus amount
    */
    public function saveCreditHistory($customers_id, $amount, $prefix = '+', $currency = '', $currency_value = '', $comment = '', $type = 0, $customer_notified = 0){
        if (!$customers_id) $customers_id = $this->customers_id;
        models\CustomersCreditHistory::saveCreditHistory((int)$customers_id, $amount, $prefix, $currency, $currency_value, $comment, $type, $customer_notified);
        return $this;
    }

    /*return customers model*/
    public function getUserByToken($token){
        if ($token){
            $cInfo = models\CustomersInfo::find()->where(['token' => $token])->one();
            if ($cInfo){
                return self::findOne($cInfo->customers_info_id);
            }
        }
        return false;
    }

    public function updateUserToken($customers_id = null){
        $customers_id = ($customers_id ? $customers_id : $this->customers_id);
        if ($customers_id){
            $cInfo = $this->getCustomersInfo($customers_id);
        }
        if ($cInfo){
            $cInfo->updateToken();
        }
        return false;
    }

    public function getCustomersInfo($customers_id = null){
        $_cid = $this->customers_id ?? $customers_id;
        if ($_cid){
            if (is_null($this->_customersInfo)){
                $this->_customersInfo = models\CustomersInfo::findOne(['customers_info_id' => $_cid]);
            }
        }
        return $this->_customersInfo;
    }

    public function fillCustomerFields($model){
        if (!empty($model->email_address)) {
            $this->customers_email_address = $model->email_address;
        }
        if (!empty($model->gender)) {
            $this->customers_gender = $model->gender;
        }
        if (!empty($model->firstname)) {
            $this->customers_firstname = $model->firstname;
        }
        if (!empty($model->lastname)) {
             $this->customers_lastname = $model->lastname;
        }
        if (!empty($model->dob)) {
            $this->customers_dob = DateHelper::date_raw($model->dob);
        } else {
            $this->customers_dob = '0000-00-00';
        }
        if (!empty($model->gdpr)) {
            $this->dob_flag = $model->gdpr;
        }
        if (!empty($model->telephone)) {
            $this->customers_telephone = $model->telephone;
        }
        if (!empty($model->landline)) {
            $this->customers_landline = $model->landline;
        }
        $this->customers_company = '';
        //$this->customers_company_vat = '';
    }


    public function registerCustomer(CustomerRegistration $model = null, $withLogin = true, \common\forms\AddressForm $addressModel = null) {
        if ($model){
            $login = \common\helpers\Customer::check_need_login($model->group);

            $this->setAttributes([
                //'customers_email_address' => $model->email_address,
                'customers_newsletter' => $model->newsletter,
                'groups_id' => $model->group,
                'customers_status' => ($login ? 1 : 0),
                'customers_password' => \common\helpers\Password::encrypt_password($model->password, 'frontend'),
                'opc_temp_account' => (!is_null($model->opc_temp_account) ? $model->opc_temp_account : 0),
            ], false);

            $this->platform_id = (!empty($model->platform_id) ? $model->platform_id : \common\classes\platform::currentId());

            try {
                $languageId = (int)\Yii::$app->settings->get('languages_id');
            } catch (\Exception $e) {
                $languageId = (int)\common\classes\language::defaultId();
            }
            $this->language_id = $model->language_id ?? $languageId;

            if (!is_null($addressModel)){
                $this->fillCustomerFields($addressModel);
            }
            $this->fillCustomerFields($model);

            if ($ext = \common\helpers\Acl::checkExtensionAllowed('CustomerCode', 'allowed')) {
                $ext::addErpFields($this, $model);
            }

            $this->insert(false);
            $this->addCustomersInfo();

            /** @var \common\extensions\Subscribers\Subscribers $subscr  */
            if ($subscr = \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed')) {
                if (defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true') {
                    $subscr::onSaveCustomer($this);
                    $this->saveRegularOffers($model);
                }
            }

            if (!is_null($addressModel)){
                $address = $this->getAddressFromModel($addressModel);
            } else {
                $address = $this->getAddressFromModel($model);
            }
            $this->addDefaultAddress($address);
            
            if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
                $address['entry_type'] = \common\forms\AddressForm::SHIPPING_ADDRESS;
                $_address = $this->addAddress($address);
                if ($_address) {
                    $this->customers_shipping_address_id = $_address->address_book_id;
                    $this->save();
                }
            }

            if ($ext = \common\helpers\Acl::checkExtensionAllowed('PlatformRestrictLogin', 'enabled')) {
                $loginStatus = $ext::customerRegister($this);
                if ( is_bool($loginStatus) && $loginStatus===false ){
                    $withLogin = false;
                }
            }
            if ($this->customers_status && $withLogin){
                $this->_afterAuth();
            }

            foreach (\common\helpers\Hooks::getList('customers/register') as $filename) {
                include($filename);
            }

            if (property_exists(Yii::$app->controller, 'promoActionsObs') && is_object(Yii::$app->controller->promoActionsObs)){
                Yii::$app->controller->promoActionsObs->triggerAction('create_account');
                if ($model->newsletter && is_object(Yii::$app->controller->promoActionsObs)) {
                    Yii::$app->controller->promoActionsObs->triggerAction('signing_newsletter');
                }
            }
            if ($withLogin){
                $this->sendCongratulation($login);
            }

            return $this;
        }
        return null;
    }

    /**/
    public function registerGuestCustomer(CustomerRegistration $customerModel, \common\forms\AddressForm $addressModel){
        $this->opc_temp_account = 1;
        foreach($customerModel->getAttributesByScenario() as $name => $value){
            if ($this->hasAttribute('customers_'.$name)){
                $this->{'customers_'.$name} = $value;
            }
            if ($this->hasAttribute($name)){
                $this->{$name} = $value;
            }
        }
        $this->groups_id = $customerModel->group;
        /*if (defined('ONE_PAGE_CREATE_ACCOUNT')){
            if (ONE_PAGE_CREATE_ACCOUNT == 'onebuy' && $customerModel->terms){ //terms used as create account prompt
                $this->opc_temp_account = 0;
            }
        }*/

        if (empty($customerModel->password)){
            $customerModel->password = \common\helpers\Password::create_random_value(ENTRY_PASSWORD_MIN_LENGTH);
        }
        $this->customers_password = \common\helpers\Password::encrypt_password($customerModel->password, 'frontend');
        $this->platform_id = \common\classes\platform::currentId();

        $this->fillCustomerFields($addressModel);
        $this->save(false);
        $this->addCustomersInfo();

        $book = $this->getAddressFromModel($addressModel);
        if ($book){
            $newBook = $this->addDefaultAddress($book);
            
            if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
                $book['entry_type'] = \common\forms\AddressForm::SHIPPING_ADDRESS;
                $_address = $this->addAddress($book);
                if ($_address) {
                    $this->customers_shipping_address_id = $_address->address_book_id;
                    $this->save();
                }
            }
        }

        $this->_afterAuth();
    }

    public function removeDuplicateGuestsAccounts(){
        if (!empty($this->customers_email_address) && $this->customers_id){

            $guests = models\Customers::find()->where(['customers_email_address' => $this->customers_email_address, 'opc_temp_account' => 1])
                    ->andWhere(['<>', 'customers_id', $this->customers_id])->all();
            if ($guests){
                foreach($guests as $guest){
                    if (opc::is_temp_customer($guest->customers_id)) {
                        opc::remove_temp_customer($guest->customers_id, $this->customers_id);
                    }
                }
            }
        }
    }

    public function getAddressFromArray(array $array_data){
        //to do
    }

    public function updateCustomer($array_data){
//        if ($this->customers_id){
            if (is_array($array_data)){
                foreach ($array_data as $keyField => $value){
                    if ($this->hasAttribute($keyField)){
                        $this->$keyField = $value;
                    } else if ($this->hasAttribute('customers_'.$keyField)){
                        $this->{'customers_'.$keyField} = $value;
                    }
                    if ($keyField == 'group'){
                        $this->groups_id = (int)$value;
                    }
                }
                $this->save(false);
            }
//        }
    }

    public function saveRegularOffers(CustomerRegistration $model){
        if ($model->newsletter && $model->regular_offers){
            $rOffer = new \common\models\RegularOffers();
            $rOffer->setAttributes([
                'customers_id' => $this->customers_id,
                'period' => $model->regular_offers,
                'date_end' => date('Y-m-d', strtotime('+'.$model->regular_offers.' months')),
            ], false);
            $rOffer->save(false);
        }
    }

    public function getAddressFromModel($model){
        $addressDetails = [];
        if ($model){
            $addressDetails = [
                'customers_id' => $this->customers_id,
                'entry_country_id' => $model->country,
            ];

            if (!empty($model->gender)){
                $addressDetails['entry_gender'] = $model->gender;
            }

            if (!empty($model->firstname)){
                $addressDetails['entry_firstname'] = $model->firstname;
            }

            if (!empty($model->lastname)){
                $addressDetails['entry_lastname'] = $model->lastname;
            }

            if (!empty($model->lastname)){
                $addressDetails['entry_lastname'] = $model->lastname;
            }

            if (!empty($model->postcode)){
                $addressDetails['entry_postcode'] = $model->postcode;
            }

            if (!empty($model->street_address)){
                $addressDetails['entry_street_address'] = $model->street_address;
            }

            if (isset($model->suburb)){
                $addressDetails['entry_suburb'] = $model->suburb;
            }

            if (!empty($model->city)){
                $addressDetails['entry_city'] = $model->city;
            }

            if (isset($model->company)){
                $addressDetails['entry_company'] = $model->company;
            }

            if (isset($model->company_vat)){
                $addressDetails['entry_company_vat'] = $model->company_vat;
            }

            if (isset($model->customs_number)){
                $addressDetails['entry_customs_number'] = $model->customs_number;
            }

            if ($model->zone_id){
                $addressDetails['entry_zone_id'] = $model->zone_id;
                $addressDetails['entry_state'] = '';
            } else {
                $addressDetails['entry_zone_id'] = 0;
                $addressDetails['entry_state'] = $model->state ?? '';
            }
            if (isset($model->telephone)){
                $addressDetails['entry_telephone'] = $model->telephone;
            }
            if (isset($model->email_address)){
                $addressDetails['entry_email_address'] = $model->email_address;
            }
            if (isset($model->drop_ship)){
                $addressDetails['drop_ship'] = ($model->drop_ship && 1);
            }
            if (!empty($model->type)){
                $addressDetails['entry_type'] = $model->type;
            }
        }
        return $addressDetails;
    }

    public function addDefaultAddress($address){
        if ($address){
            if (!$this->customers_default_address_id){
                $_address = $this->addAddress($address);
                if ($_address){
                    $this->customers_default_address_id = $_address->address_book_id;
                    $this->save();
                }
            } else {
                $_address = $this->getDefaultAddress();
                if ( !empty($_address) && is_object($_address) && $_address instanceof \yii\db\ActiveQuery){
                    $_address = $_address->one();
                }
                if ($_address){
                    $this->updateAddress($_address->address_book_id, $address);
                }
            }
        }
    }

    public function addAddress($attributes){
        $aBook = \common\models\AddressBook::create($attributes);
        if (!$aBook->customers_id) {
            $aBook->customers_id = $this->customers_id;
        }
        if ($aBook){
            $aBook->save(false);
        }
        /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
        if ($VatOnOrder = \common\helpers\Acl::checkExtensionAllowed('VatOnOrder', 'allowed')) {
          try {
            $attributes['address_book_id'] = $aBook->address_book_id;
            $check = $VatOnOrder::check_vat_status($attributes); // updates entry_company_vat_status
            if ($check>1) {
              $aBook->entry_company_vat_status = $check;
            }
          } catch (\Exception $ex) {
            \Yii::error($ex->getMessage());
          }
        }

        return $aBook;
    }

    public function updateAddress($abId, $attributes){
        $aBook = \common\models\AddressBook::findOne(['address_book_id' => $abId]);
        if ($aBook){
            $attributes['entry_company_vat_status'] = 0;
            $aBook->edit($attributes);
            $aBook->save(false);
            /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
            if ($VatOnOrder = \common\helpers\Acl::checkExtensionAllowed('VatOnOrder', 'allowed')) {
              try {
                $attributes['address_book_id'] = $aBook->address_book_id;
                $check = $VatOnOrder::check_vat_status($attributes); // updates entry_company_vat_status
                if ($check>1) {
                  $aBook->entry_company_vat_status = $check;
                }
              } catch (\Exception $ex) {
                \Yii::error($ex->getMessage());
              }
            }
        } else {
            return $this->addAddress($attributes);
        }

        return $aBook;
    }

    public function removeAddress($abId){
        if ($this->customers_id){
            $aBook = \common\models\AddressBook::findOne(['address_book_id' => $abId, 'customers_id' =>$this->customers_id]);
            if ($aBook){
                $aBook->delete();
            }
        }
        return false;
    }

    public function addCustomersInfo() {
        $cInfo = null;
        if ($this->customers_id){
            $cInfo = models\CustomersInfo::findOne(['customers_info_id' => $this->customers_id]);
            if (!$cInfo){
                $cInfo = new models\CustomersInfo();
                $cInfo->setAttributes([
                    'customers_info_id' => $this->customers_id,
                    'customers_info_number_of_logons' => 0
                ], false);
            }
            $cInfo->save(false);
        }
        return $cInfo;
    }

    public function sendCongratulation($login = false){
        if ($this->customers_id){
            $name = $this->customers_firstname . ' ' . $this->customers_lastname;

            if ($this->customers_gender == 'm') {
                $user_greeting = sprintf(EMAIL_GREET_MR,  $this->customers_lastname);
            } elseif ($this->customers_gender == 'f' || $this->customers_gender == 's') {
                $user_greeting = sprintf(EMAIL_GREET_MS,  $this->customers_lastname);
            } else {
                $user_greeting = sprintf(EMAIL_GREET_NONE, $this->customers_firstname);
            }

            $email_params = array();
            $email_params['STORE_NAME'] = STORE_NAME;
            $email_params['USER_GREETING'] = trim($user_greeting);
            $email_params['CUSTOMER_FIRSTNAME'] = $this->customers_firstname;
            $email_params['CUSTOMER_LASTNAME'] = $this->customers_lastname;
            $email_params['CUSTOMER_EMAIL'] = $this->customers_email_address;
            $email_params['STORE_OWNER_EMAIL_ADDRESS'] = STORE_OWNER_EMAIL_ADDRESS;
            list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('New Customer Confirmation', $email_params);

            \common\helpers\Mail::send($name, $this->customers_email_address, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            if (!$login) {

                $email_params['ADMIN_CUSTOMER_URL'] = Yii::$app->urlManager->createAbsoluteUrl(['admin/customers/customeredit' , 'customers_id' => $this->customers_id]);

                list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('New Customer Query', $email_params);

                \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }
        }
    }

}
