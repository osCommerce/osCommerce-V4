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

namespace frontend\forms\registration;

use Yii;
use yii\base\Model;
use common\classes\ReCaptcha;
use common\components\Customer;
use common\helpers\Date as DateHelper;

class CustomerRegistration extends Model {

    const SCENARIO_LOGIN = 'login';
    const SCENARIO_LOGIN_TOP = 'login_top';
    const SCENARIO_GUEST = 'guest';
    const SCENARIO_REGISTER = 'registration';
    const SCENARIO_FAST_ORDER = 'fast_order';
    const SCENARIO_ENQUIRE = 'enquire';
    const SCENARIO_CHECKOUT = 'checkout';
    const SCENARIO_EDIT = 'edit';
    const SCENARIO_CREATE = 'create';

    public $email_address;
    public $password;
    public $confirmation;
    public $company;
    public $language_id;
    public $company_vat;
    public $gender;
    public $firstname;
    public $lastname;
    public $group;
    public $telephone;
    public $landline;
    public $dobTmp;
    public $dob;
    public $gdpr;
    public $newsletter;
    public $regular_offers;
    public $postcode;
    public $street_address;
    public $suburb;
    public $city;
    public $state;
    public $country;
    public $zone_id;
    public $terms;
    public $customs_number;
    /* for create account */
    public $status;
    public $erp_customer_id;
    public $erp_customer_code;
    public $platform_id;
    public $admin_id;
    public $opc_temp_account;
    public $pin;
    public $drop_ship;
    public $can_use_drop_ship;

    /* for enquire */
    public $name;
    public $phone;
    public $content;
    private $shortName;
    private $showAddress = false;
    public $useExtending = false; //admin section    
    
    public $captha_enabled = false;
    
    public $captcha = null;
    
    public $captcha_response;    
    public $captcha_widget;

    public $remember = null;

    public function __construct($config = array()) {
        if (isset($config['shortName'])) {
            $this->shortName = $config['shortName'];
            unset($config['shortName']);
        }
        if (isset($config['captha_enabled']) && $config['captha_enabled'] == true) {
            $this->captha_enabled = 'captha';
            unset($config['captha_enabled']);
        } elseif (\common\models\Fraud::verifyAddress()) {
            $this->captha_enabled = 'captha';
        }
        if ($this->captha_enabled == 'captha') {
            if (defined('PREFERRED_USE_RECAPTCHA') && PREFERRED_USE_RECAPTCHA == 'True') {
                $captcha = new ReCaptcha();            
                if ($captcha->isEnabled()) {
                    $this->captha_enabled = 'recaptha';
                    $this->captcha_widget = \frontend\design\boxes\ReCaptchaWidget::widget();
                    $this->captcha = $captcha;
                }else{
                    $this->captha_enabled = false;
                }
            } else {
                $this->captcha_widget = \yii\captcha\Captcha::widget([
                        'model' => $this,
                        'attribute' => 'captcha'
                ]);
            }
        }
        parent::__construct($config);
        $this->initParms();
    }

    public function formName() {
        return $this->shortName;
    }
    
    public function beforeValidate() {
        foreach ($this->attributes as $attribute_name => $attribute_value) {
            if (is_string($attribute_value) && !in_array($attribute_name, ['password', 'confirmation','captcha_widget'])) {
                $this->$attribute_name = \yii\helpers\HtmlPurifier::process($attribute_value);
            }
        }
        if ($this->captha_enabled == 'recaptha'){
            $this->captcha_response = Yii::$app->request->post('g-recaptcha-response', null);
        }
        return parent::beforeValidate();
    }

    public static function hasScenario($scenario) {
        if (!is_string($scenario)) return false;
        $reflection = new \ReflectionClass(self::className());
        $_const = $reflection->getConstants();
        if (is_array($_const)) {
            $_const = array_flip($_const);
            return isset($_const[$scenario]);
        }
        return null;
    }

    public function rules() {
        try {
            $languageId = (int)\Yii::$app->settings->get('languages_id');
        } catch (\Exception $e) {
            $languageId = (int)\common\classes\language::defaultId();
        }

        $_rules = [
            [['email_address', 'password', 'confirmation','content', 'phone', 'name', 'dob', 'gender', 'firstname', 'lastname', 'telephone', 'landline'], 'string'],
            [[ 'gdrp', 'newsletter', 'terms', 'regular_offers', 'language_id'], 'integer'],
            ['language_id', 'default', 'value' => $languageId],
            [['email_address', 'password'], 'required', 'on' => [static::SCENARIO_LOGIN, static::SCENARIO_LOGIN_TOP/*, static::SCENARIO_REGISTER*/]],
            ['email_address', 'email', 'message' => ENTRY_EMAIL_ADDRESS_CHECK_ERROR, 'on' => [static::SCENARIO_REGISTER, static::SCENARIO_GUEST, static::SCENARIO_ENQUIRE, static::SCENARIO_EDIT, static::SCENARIO_CREATE], 'skipOnEmpty' => false],
            [['email_address', 'content', 'phone', 'name'], 'required', 'on' => [static::SCENARIO_ENQUIRE], 'skipOnEmpty' => false],
            ['email_address', 'emailUnique', 'on' => [static::SCENARIO_REGISTER, static::SCENARIO_GUEST, static::SCENARIO_CHECKOUT, static::SCENARIO_EDIT, static::SCENARIO_CREATE]],
            ['password', 'compare', 'compareAttribute' => 'confirmation', 'on' => [static::SCENARIO_REGISTER], 'message' => ENTRY_PASSWORD_ERROR_NOT_MATCHING],
            [['email_address', 'gender', 'firstname', 'lastname', 'dob', 'telephone', 'landline', 'company', 'company_vat', 'postcode', 'street_address', 'suburb', 'city', 'country', 'zone_id', 'state', 'password'],
                'requiredOnRegister', 'on' => [static::SCENARIO_REGISTER], 'skipOnEmpty' => false],
            [['country', 'zone_id'], 'defaultGeoValues', 'on' => [static::SCENARIO_REGISTER, static::SCENARIO_FAST_ORDER, static::SCENARIO_CREATE], 'skipOnEmpty' => false],
            ['gdrp', 'required', 'on' => [static::SCENARIO_REGISTER, static::SCENARIO_GUEST]],
            ['newsletter', 'default', 'value' => 0, 'on' => [static::SCENARIO_REGISTER, static::SCENARIO_CREATE]],
            ['regular_offers', 'default', 'value' => 0, 'on' => [static::SCENARIO_REGISTER, static::SCENARIO_CREATE]],
            ['group', 'defaultGroup', 'on' => [static::SCENARIO_REGISTER, static::SCENARIO_FAST_ORDER, static::SCENARIO_CHECKOUT, static::SCENARIO_CREATE], 'skipOnEmpty' => false],//, static::SCENARIO_EDIT
            [['dob'], 'requiredOnRegister', 'on' => [static::SCENARIO_GUEST,static::SCENARIO_CHECKOUT], 'skipOnEmpty' => false],
            [['email_address', 'telephone', 'firstname', 'content'], 'requiredOnRegister', 'on' => [static::SCENARIO_FAST_ORDER], 'skipOnEmpty' => false],
            [['email_address', 'telephone', 'landline'/* , 'company', 'company_vat' */, 'firstname', 'lastname', 'gender'], 'requiredOnRegister', 'on' => [static::SCENARIO_CHECKOUT, static::SCENARIO_CREATE], 'skipOnEmpty' => false],
            [['password', 'terms'], 'requiredOnCheckoutAccount', 'on' => [static::SCENARIO_CHECKOUT], 'skipOnEmpty' => false],
            //[['dob', 'gdpr'], 'requiredOnCheckoutAccount', 'on' => [static::SCENARIO_CHECKOUT], 'skipOnEmpty' => false],
            [['gender', 'firstname', 'lastname', 'telephone', 'landline'], 'requiredOnRegister', 'on' => [static::SCENARIO_EDIT], 'skipOnEmpty' => false],
            ['dob', 'requiredOnEdit', 'on' => [static::SCENARIO_EDIT, static::SCENARIO_CREATE], 'skipOnEmpty' => false],
            ['erp_customer_id', 'default', 'value' => '0', 'on' => [static::SCENARIO_CREATE, static::SCENARIO_EDIT]],
            ['erp_customer_code', 'default', 'value' => null, 'on' => [static::SCENARIO_CREATE, static::SCENARIO_EDIT]],
            ['status', 'default', 'value' => '1', 'on' => [static::SCENARIO_CREATE, static::SCENARIO_EDIT]],
            ['platform_id', 'required', 'on' => [static::SCENARIO_CREATE, static::SCENARIO_EDIT], 'skipOnEmpty' => false],
            ['opc_temp_account', 'default', 'value' => '0', 'on' => [static::SCENARIO_CREATE, static::SCENARIO_EDIT], 'skipOnEmpty' => false],
            ['pin', 'default', 'value' => '', 'on' => [static::SCENARIO_EDIT]],
            ['can_use_drop_ship', 'default', 'value' => '0', 'on' => [static::SCENARIO_EDIT]],
            ['admin_id', 'default', 'value' => '0', 'on' => [static::SCENARIO_EDIT]],
        ];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            if ($ext::checkNeedLogin()) {
                $_rules[] = ['terms', 'requiredTrems', 'on' => [static::SCENARIO_LOGIN, static::SCENARIO_LOGIN_TOP], 'skipOnEmpty' => false];
            }
        }

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('PlatformRestrictLogin', 'enabled')) {
            $extRules = $ext::customerRegistrationRules();
            if ( $extRules ) {
                foreach ($extRules as $extRule) $_rules[] = $extRule+['model'=>$this];
            }
        }

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('CustomerCode', 'allowed')) {
            $_rules[] = [['erp_customer_id', 'erp_customer_code'], 'requiredOnCreate', 'on' => [static::SCENARIO_CREATE, static::SCENARIO_EDIT], 'skipOnEmpty' => false];
        }

        if ($this->captha_enabled == 'captha') {
            $_rules[] = ['captcha', 'required'];
            $_rules[] = ['captcha', 'captcha'];
        }
        if ($this->captha_enabled == 'recaptha') {
            $_rules[] = ['captcha_response', 'validateCaptcha', 'skipOnEmpty' => false];
        }
        
        return $_rules;
    }
    
    public function validateCaptcha($attribute, $params) {
        if ($this->captha_enabled == 'recaptha'){
            if (!$this->captcha->checkVerification($this->captcha_response)){
                $this->addError($attribute, 'Wrong captcha verification');
            }
        }
    }
    
    public function requiredTrems($attribute, $params) {
        if (!$this->$attribute) {
            $this->addError($attribute, 'Please Read terms & conditions');
        }
    }

    public function requiredOnCheckoutAccount($attribute, $params) {
	if (!$this->useExtending) {
                if ($this->opc_temp_account) {
                    if ($attribute == 'password') {
                        if (empty($this->password) || $this->password != $this->confirmation) {
                            $this->addError($attribute, ENTRY_PASSWORD_ERROR_NOT_MATCHING);
                        }
                    }
                }
        }
        /*if (defined('ONE_PAGE_CREATE_ACCOUNT')) {
            if (ONE_PAGE_CREATE_ACCOUNT == 'onebuy' && !$this->useExtending) {
                if ($this->terms) {
                    if ($attribute == 'password') {
                        if (empty($this->password) || $this->password != $this->confirmation) {
                            $this->addError($attribute, ENTRY_PASSWORD_ERROR_NOT_MATCHING);
                        }
                    }
                }
            }
        }*/
    }

    public function requiredOnCreate($attribute, $params) {

        if($ext = \common\helpers\Acl::checkExtensionAllowed('CustomerCode')) {
            /* @var \common\extensions\CustomerCode */
            switch ($attribute) {
                case 'erp_customer_id':
                    if (in_array(\common\helpers\PlatformConfig::getVal('EXT_CUSTOMER_CODE_ERP_ID'), $this->getRequired()) && empty($this->$attribute)) {
                        $this->addError($attribute, 'Invalid ERP Id');
                    }
                    break;
                case 'erp_customer_code':
                    if (in_array(\common\helpers\PlatformConfig::getVal('EXT_CUSTOMER_CODE_ERP_CODE'), $this->getRequired()) && empty($this->$attribute)) {
                        $this->addError($attribute, 'Invalid ERP Code');
                    }
                    break;
            }
        }
    }

    public function requiredOnEdit() {
        if (empty($this->dob)) {
            if (in_array(ACCOUNT_DOB, $this->getRequired())) {
                $this->addError('dob', ENTRY_DATE_OF_BIRTH_ERROR);
            } else {
                $this->dob = '0000-00-00';
            }
        } else {
            $dob = DateHelper::date_raw($this->dob);
            if (!checkdate(date('m', strtotime($dob)), date('d', strtotime($dob)), date('Y', strtotime($dob)))) {
                if (in_array(ACCOUNT_DOB, $this->getRequired())) {
                    $this->addError('dob', ENTRY_DATE_OF_BIRTH_ERROR);
                } else {
                    $this->dob = '0000-00-00';
                }
            }
        }
    }

    public function checkPin($attribute, $customers_id) {
        if (in_array(ACCOUNT_PIN, ['required', 'required_register', 'visible', 'visible_register'])) {
            if (!empty($this->pin)) {
                $oCustomer = \common\models\Customers::find()->where(['AND', ['pin' => $this->pin], ['!=', 'customers_id', $customers_id]])->limit(1)->one();
                if (is_object($oCustomer)) {
                    $this->addError('pin', TEXT_PIN . ' ' . TEXT_MUST_UNIQUE);
                }
                unset($oCustomer);
            }
        }
    }

    public function attributeLabels() {
        $labels = [];
        if ($this->scenario == static::SCENARIO_CHECKOUT) {
            $labels = ['terms' => TEXT_CREATE_ACCOUNT_DEFENETLY];
        }
        $labels['status'] = '';
        return array_merge(parent::attributeLabels(), $labels
        );
    }

    public function emailUnique($attribute, $params) {
	//static::SCENARIO_CREATE - create customer on edit order
	//static::SCENARIO_CHECKOUT - customer on checkout & edit order
	//static::SCENARIO_REGISTER - customer register by himself
        if ($this->scenario == static::SCENARIO_REGISTER && (defined('FLAG_EMAIL_VERIFICATION') && FLAG_EMAIL_VERIFICATION == 'True')) {
            $emailValidation = \common\models\CustomersEmailValidation::find()->where(['cev_email' => md5($this->$attribute)])->one();
            if ($emailValidation instanceof \common\models\CustomersEmailValidation) {
                $cevCode = trim(filter_var(htmlentities(Yii::$app->request->post('email_verification_code')), FILTER_SANITIZE_STRING));
                if ( !empty($cevCode) && $emailValidation->cev_code != md5($cevCode) ) {
                    $this->addError($attribute, ENTRY_VERIFICATION_CODE_ERROR);
                }
            } else {
                $this->addError($attribute, ENTRY_VERIFICATION_CODE_ERROR);
            }
        }
        if (in_array($this->scenario, [static::SCENARIO_REGISTER, static::SCENARIO_CREATE, static::SCENARIO_CHECKOUT])) {
            
            if ($this->scenario == static::SCENARIO_CHECKOUT){
                if ($this->opc_temp_account){
                    $exist = \common\models\Customers::find()->where(['customers_email_address' => $this->$attribute, 'opc_temp_account' => 0]);
                    if (Yii::$app->storage->has('customer_id') ){
                        $exist->andWhere(['!=', 'customers_id', Yii::$app->storage->get('customer_id')]);
                    }
                    if ($exist->one()) {
                        $this->addError($attribute, ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
                    }
                }
            } else {
                $exist = \common\models\Customers::find()->where(['customers_email_address' => $this->$attribute, 'opc_temp_account' => 0]);
                if ($exist->one()) {
                    $this->addError($attribute, ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
                }
            }
        }

        if ($this->scenario == static::SCENARIO_EDIT) {
            $_customer = null;
            $_customerQ = \common\models\Customers::find()
                    ->where(['customers_email_address' => $this->$attribute, 'opc_temp_account' => 0]);
            if (\Yii::$app->user->getId()) {
                $_customerQ->andWhere(['!=', 'customers_id', \Yii::$app->user->getId()]);
                $_customer = $_customerQ->one();
            } else if (isset($params['customers_id'])) {//
                $_customerQ->andWhere(['!=', 'customers_id', $params['customers_id']]);
                $_customer = $_customerQ->one();
            }

            if ($_customer) {
                $this->addError($attribute, ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
            }
        }

        /* do not process with existed email
         * if ($this->scenario == static::SCENARIO_GUEST){
          if (\common\models\Customers::findOne(['customers_email_address' => $this->$attribute])){
          $this->addError($attribute, ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
          }
          } */
        /* not needed indeed  {{ */
        /*if (strlen($this->$attribute) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
            $this->addError($attribute, ENTRY_EMAIL_ADDRESS_ERROR);
        }*/
        /* }} */
        if (!$this->hasErrors($attribute)) {
            \common\models\YoungCustomers::deleteAll(['<=', 'expiration_date', date('Y-m-d')]);
            if (\common\models\YoungCustomers::findOne(['email' => md5($this->$attribute)]) && !$this->useExtending) {
                $this->addError($attribute, ENTRY_DATE_OF_BIRTH_RESTRICTION);
            }
        }
    }

    protected $_required = null;

    public function getRequired() {
        if (is_null($this->_required)) {
            $this->_required = ['required_register'];
            if (in_array($this->scenario, [static::SCENARIO_CHECKOUT, static::SCENARIO_EDIT, static::SCENARIO_CREATE])) {
                $this->_required[] = 'required';
                $this->_required[] = 'required_company';
            }
        }
        return $this->_required;
    }

    public function requiredOnRegister($attribute, $params) {
        if (in_array($attribute, ['email_address'])) {
            $cfg = false;
        } else {
            $cfg = (defined('ACCOUNT_' . strtoupper($attribute)) ? constant('ACCOUNT_' . strtoupper($attribute)) : false);
        }
        switch ($attribute) {
            case 'email_address':
                $cfg = (defined('ACCOUNT_EMAIL') ? constant('ACCOUNT_EMAIL') : false);
                $valid = \common\helpers\Validations::validate_email($this->$attribute);
                if (in_array(ACCOUNT_EMAIL, $this->getRequired()) && !$valid) {
                    $this->addError($attribute, ENTRY_EMAIL_ADDRESS_ERROR);
                }else if (!empty($this->$attribute) && !$valid){
		    $this->addError($attribute, ENTRY_EMAIL_ADDRESS_ERROR);
		}
                break;
            case 'gender':
                if (in_array(ACCOUNT_GENDER, $this->getRequired()) && !in_array($this->$attribute, array_keys($this->getGenderList()))) {
                    $this->addError($attribute, ENTRY_GENDER_ERROR);
                }
                break;
            case 'firstname':
                if (strpos($this->$attribute, '<')!==false || strpos($this->$attribute, 'https://')!==false || strpos($this->$attribute, 'http://')!==false 
                    || (strip_tags($this->$attribute) != $this->$attribute) || (in_array(ACCOUNT_FIRSTNAME, $this->getRequired()) && strlen($this->$attribute) < ENTRY_FIRST_NAME_MIN_LENGTH)) {
                //if (in_array(ACCOUNT_FIRSTNAME, $this->getRequired()) && strlen($this->$attribute) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                    $this->addError($attribute, sprintf(ENTRY_FIRST_NAME_ERROR, ENTRY_FIRST_NAME_MIN_LENGTH));
                }
                break;
            case 'lastname':
                if (strpos($this->$attribute, '<')!==false || strpos($this->$attribute, 'https://')!==false || strpos($this->$attribute, 'http://')!==false 
                    || (strip_tags($this->$attribute) != $this->$attribute) || (in_array(ACCOUNT_LASTNAME, $this->getRequired()) && strlen($this->$attribute) < ENTRY_LAST_NAME_MIN_LENGTH)) {
                //if (in_array(ACCOUNT_LASTNAME, $this->getRequired()) && strlen($this->$attribute) < ENTRY_LAST_NAME_MIN_LENGTH) {
                    $this->addError($attribute, sprintf(ENTRY_LAST_NAME_ERROR, ENTRY_LAST_NAME_MIN_LENGTH));
                }
                break;
            case 'dob':
                if ($this->scenario == self::SCENARIO_CHECKOUT && $this->useExtending) return;
                if (!$this->gdpr) {
                    if (empty($this->$attribute)) {
                        if (in_array(ACCOUNT_DOB, $this->getRequired())) {
                            $this->addError($attribute, ENTRY_DATE_OF_BIRTH_ERROR);
                        } else {
                            $this->dob = '0000-00-00';
                        }
                    } else {
                        $gdpr = new \common\components\Gdpr();
                        $dob = DateHelper::date_raw($this->dob);
                        $gdpr->setDobDate($dob);
                        $gdpr->setEmail($this->email_address);
                        $gdpr->validateGdpr();
                        if ($gdpr->hasMistake() || $gdpr->getError()) {
                            $this->addError($attribute, $gdpr->getMessage());
                        }
                    }
                }
                break;
            case 'telephone':
                if (in_array(ACCOUNT_TELEPHONE, $this->getRequired()) && strlen($this->$attribute) < ENTRY_TELEPHONE_MIN_LENGTH) {
                    $this->addError($attribute, sprintf(ENTRY_TELEPHONE_NUMBER_ERROR, ENTRY_TELEPHONE_MIN_LENGTH));
                }
                break;
            case 'landline':
                if (in_array(ACCOUNT_LANDLINE, $this->getRequired()) && strlen($this->$attribute) < ENTRY_LANDLINE_MIN_LENGTH) {
                    $this->addError($attribute, sprintf(ENTRY_LANDLINE_NUMBER_ERROR, ENTRY_LANDLINE_MIN_LENGTH));
                }
                break;
            case 'company':
                if (in_array(ACCOUNT_COMPANY, $this->getRequired()) && empty($this->$attribute)) {
                    $this->addError($attribute, ENTRY_COMPANY_ERROR);
                }
                break;
            case 'company_vat':
                if (in_array(ACCOUNT_COMPANY_VAT, $this->getRequired()) && (empty($this->$attribute) || !\common\helpers\Validations::checkVAT($this->$attribute))) {
                    $this->addError($attribute, ENTRY_VAT_ID_ERROR);
                }
                break;
            case 'customs_number':
                if ($cfg && in_array(ACCOUNT_CUSTOMS_NUMBER, $this->getRequired()) && empty($this->$attribute) && (
                        ( in_array($cfg, ['required', 'required_register']) ) ||
                        ( in_array($cfg, ['required_company']) && !empty($this->company) )
                        )
                    ) {
                    $this->addError($attribute, TEXT_CUSTOMS_NUMBER_ERROR);
                }
                break;
            case 'postcode':
                if (ACCOUNT_POSTCODE == 'required_register' && strlen($this->$attribute) < ENTRY_POSTCODE_MIN_LENGTH) {
                    $this->addError($attribute, sprintf(ENTRY_POST_CODE_ERROR, ENTRY_POSTCODE_MIN_LENGTH));
                }
                break;
            case 'street_address':
                if (ACCOUNT_STREET_ADDRESS == 'required_register' && strlen($this->$attribute) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
                    $this->addError($attribute, sprintf(ENTRY_STREET_ADDRESS_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
                }
                break;
            case 'suburb':
                if (ACCOUNT_SUBURB == 'required_register' && empty($this->$attribute)) {
                    $this->addError($attribute, ENTRY_SUBURB_ERROR);
                }
                break;
            case 'city':
                if (ACCOUNT_CITY == 'required_register' && strlen($this->$attribute) < ENTRY_CITY_MIN_LENGTH) {
                    $this->addError($attribute, sprintf(ENTRY_CITY_ERROR, ENTRY_STREET_ADDRESS_MIN_LENGTH));
                }
                break;
            case 'country':
                if (!is_numeric($this->$attribute)) {
                    if (ACCOUNT_COUNTRY == 'required_register') {
                        $this->addError($attribute, ENTRY_COUNTRY_ERROR);
                    } else {
                        $this->country = (int) STORE_COUNTRY;
                        $this->zone_id = (int) STORE_ZONE;
                    }
                }
                break;
            case 'state':
                $this->zone_id = 0;
                $qZones = \common\models\Zones::find()->where(['zone_country_id' => $this->country]);
                if ($qZones->count() > 0) {
                    $qZones = \common\models\Zones::find()->where(['zone_country_id' => $this->country, 'zone_name' => $this->$attribute])->all();
                    if (count($qZones) == 1) {
                        $this->zone_id = $qZones[0]->zone_id;
                    } elseif (ACCOUNT_STATE == 'required_register') {
                        $this->addError($attribute, ENTRY_STATE_ERROR_SELECT);
                    }
                } else {
                    if (ACCOUNT_STATE == 'required_register' && strlen($this->$attribute) < ENTRY_STATE_MIN_LENGTH) {
                        $this->addError($attribute, sprintf(ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH));
                    }
                }
                break;
            case 'password':
                if (strlen($this->$attribute) < ENTRY_PASSWORD_MIN_LENGTH) {
                    $this->addError($attribute, sprintf(ENTRY_PASSWORD_ERROR, ENTRY_PASSWORD_MIN_LENGTH));
                }
                break;
        }
    }

    public function afterValidate() {
        if ($this->hasErrors()) {
            $this->dobTmp = DateHelper::datepicker_date($this->dob);
            if ($this->scenario != static::SCENARIO_CHECKOUT) {
                $this->terms = 0; //used as create account
            }
        }
        return parent::afterValidate();
    }

    public function defaultGeoValues() {
        if (is_null($this->country)) {
            $this->country = (int) STORE_COUNTRY;
        }
        if (is_null($this->zone_id)) {
            $this->zone_id = (int) STORE_ZONE;
        }
    }

    public function defaultGroup($attribute, $params) {
        if (empty($this->group)) {
            if (ENABLE_CUSTOMER_GROUP_CHOOSE == 'False') {
                if (!defined("DEFAULT_USER_LOGIN_GROUP")) {
                    $this->group = 0;
                } else {
                    $this->group = (int) DEFAULT_USER_LOGIN_GROUP;
                }
            }
        }

        if (is_null($this->group)) {
            $this->addError($attribute, 'Group is not defined');
        }
    }

    public function initParms() {
        if (in_array($this->scenario, [static::SCENARIO_REGISTER])) {
            if (in_array('required_register', [ACCOUNT_POSTCODE, ACCOUNT_STREET_ADDRESS, ACCOUNT_SUBURB, ACCOUNT_CITY, ACCOUNT_STATE, ACCOUNT_COUNTRY])) {
                $this->showAddress = true;
            } elseif (in_array('visible_register', [ACCOUNT_POSTCODE, ACCOUNT_STREET_ADDRESS, ACCOUNT_SUBURB, ACCOUNT_CITY, ACCOUNT_STATE, ACCOUNT_COUNTRY])) {
                $this->showAddress = true;
            } else {
                $this->showAddress = false;
            }
        }
    }

    public function scenarios() {
        return [
            static::SCENARIO_LOGIN => $this->collectFields(static::SCENARIO_LOGIN),
            static::SCENARIO_LOGIN_TOP => $this->collectFields(static::SCENARIO_LOGIN),
            static::SCENARIO_GUEST => $this->collectFields(static::SCENARIO_GUEST),
            static::SCENARIO_REGISTER => $this->collectFields(static::SCENARIO_REGISTER),
            static::SCENARIO_FAST_ORDER => $this->collectFields(static::SCENARIO_FAST_ORDER),
            static::SCENARIO_ENQUIRE => $this->collectFields(static::SCENARIO_ENQUIRE),
            static::SCENARIO_CHECKOUT => $this->collectFields(static::SCENARIO_CHECKOUT),
            static::SCENARIO_EDIT => $this->collectFields(static::SCENARIO_EDIT),
            static::SCENARIO_CREATE => $this->collectFields(static::SCENARIO_CREATE),
        ];
    }

    public function getAttributesByScenario() {
        $attributes = $this->getAttributes();
        $list = [];
        foreach ($attributes as $attribute_name => $attribute_value) {
            if (in_array($attribute_name, $this->collectFields($this->scenario))) {
                $list[$attribute_name] = $attribute_value;
            }
        }
        return $list;
    }
    
    public function cleanupSafeFields() {
        $this->email_address = '';
        $this->password = '';
        $this->confirmation = '';
        $this->captcha = '';
    }

    public function collectFields($type) {
        $fields = [];
        switch ($type) {
            case static::SCENARIO_LOGIN :
            case static::SCENARIO_LOGIN_TOP :
                $fields[] = 'password';
                $fields[] = 'remember';
                $fields[] = 'email_address';
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
                    if ($ext::checkNeedLogin()) {
                        $fields[] = 'terms';
                    }
                }
                if ($this->captha_enabled == 'captha') {
                    $fields[] = 'captcha';
                }
                if ($this->captha_enabled == 'recaptha') {
                    $fields[] = 'captcha_response';
                }
                break;
            case static::SCENARIO_REGISTER :
                $fields[] = 'password';
                $fields[] = 'confirmation';
                if ($this->captha_enabled == 'captha') {
                    $fields[] = 'captcha';
                }
                if ($this->captha_enabled == 'recaptha') {
                    $fields[] = 'captcha_response';
                }
                $fields[] = 'group';
                if (in_array(ACCOUNT_EMAIL, ['required_register', 'visible_register'])) {
                    $fields[] = 'email_address';
                }
                if (in_array(ACCOUNT_COMPANY, ['required_register', 'visible_register'])) {
                    $fields[] = 'company';
                }
                if (in_array(ACCOUNT_COMPANY_VAT, ['required_register', 'visible_register'])) {
                    $fields[] = 'company_vat';
                }
                if (defined('ACCOUNT_CUSTOMS_NUMBER') && in_array(ACCOUNT_CUSTOMS_NUMBER, ['required_register', 'visible_register']) ||
                    (in_array('company', $fields) && ACCOUNT_CUSTOMS_NUMBER == 'required_company') ) {
                    $fields[] = 'customs_number';
                }
                if (in_array(ACCOUNT_GENDER, ['required_register', 'visible_register'])) {
                    $fields[] = 'gender';
                }
                if (in_array(ACCOUNT_FIRSTNAME, ['required_register', 'visible_register'])) {
                    $fields[] = 'firstname';
                }
                if (in_array(ACCOUNT_LASTNAME, ['required_register', 'visible_register'])) {
                    $fields[] = 'lastname';
                }
                if (in_array(ACCOUNT_TELEPHONE, ['required_register', 'visible_register'])) {
                    $fields[] = 'telephone';
                }
                if (in_array(ACCOUNT_LANDLINE, ['required_register', 'visible_register'])) {
                    $fields[] = 'landline';
                }
                if (in_array(ACCOUNT_DOB, ['required_register', 'visible_register'])) {
                    $fields[] = 'dobTmp';
                    $fields[] = 'dob';
                    $fields[] = 'gdpr';
                }
                if (\common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true') {
                    $fields[] = 'newsletter';
                    $fields[] = 'regular_offers';
                }
                if ($this->showAddress) {
                    if (in_array(ACCOUNT_POSTCODE, ['required_register', 'visible_register'])) {
                        $fields[] = 'postcode';
                    }
                    if (in_array(ACCOUNT_STREET_ADDRESS, ['required_register', 'visible_register'])) {
                        $fields[] = 'street_address';
                    }
                    if (in_array(ACCOUNT_SUBURB, ['required_register', 'visible_register'])) {
                        $fields[] = 'suburb';
                    }
                    if (in_array(ACCOUNT_CITY, ['required_register', 'visible_register'])) {
                        $fields[] = 'city';
                    }
                    if (in_array(ACCOUNT_STATE, ['required_register', 'visible_register'])) {
                        $fields[] = 'state';
                    }
                }
                $fields[] = 'country';
                $fields[] = 'zone_id';
                break;
            case static::SCENARIO_ENQUIRE :
                $fields[] = 'phone';
                $fields[] = 'content';
                $fields[] = 'name';
                $fields[] = 'email_address';
                break;
            case static::SCENARIO_GUEST :
                $fields[] = 'terms';
                $fields[] = 'email_address';
                if (in_array(ACCOUNT_DOB, ['required_register', 'visible_register'])) {
                    $fields[] = 'dobTmp';
                    $fields[] = 'dob';
                    $fields[] = 'gdpr';
                }
                break;
            case static::SCENARIO_FAST_ORDER :
                $fields[] = 'country';
                $fields[] = 'zone_id';
                $fields[] = 'group';
                $fields[] = 'content';
                $fields[] = 'email_address';
                if (in_array(ACCOUNT_FIRSTNAME, ['required_register', 'visible_register'])) {
                    $fields[] = 'firstname';
                }
                if (in_array(ACCOUNT_TELEPHONE, ['required_register', 'visible_register'])) {
                    $fields[] = 'telephone';
                }
                break;
            case static::SCENARIO_CHECKOUT:
                $fields[] = 'group';
                if (in_array(ACCOUNT_EMAIL, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'email_address';
                }
                if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'telephone';
                }
                if (in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'landline';
                }
                if ($this->useExtending) {
                    if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                        $fields[] = 'gender';
                    }
                    if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                        $fields[] = 'firstname';
                    }
                    if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                        $fields[] = 'lastname';
                    }
                }
                if(!Yii::$app->storage->has('customer_id')){
                    if (in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])) {
                        $fields[] = 'dob';
                        $fields[] = 'dobTmp';
                        $fields[] = 'gdpr';
                    }
                }
                $fields[] = 'opc_temp_account';
                if (/*defined('ONE_PAGE_CREATE_ACCOUNT') &&*/ !$this->useExtending) {
                    //if (ONE_PAGE_CREATE_ACCOUNT == 'onebuy') {
                    $fields[] = 'password';
                    $fields[] = 'confirmation';
                    //}
                }
                if (\common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true') {
                    $fields[] = 'newsletter';
                    $fields[] = 'regular_offers';
                }
                break;
            case static::SCENARIO_CREATE:
                $fields[] = 'group';
                if (in_array(ACCOUNT_EMAIL, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'email_address';
                }
                if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'telephone';
                }
                if (in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'landline';
                }
                if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'gender';
                }
                if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'firstname';
                }
                if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'lastname';
                }
                if (in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'dob';
                    $fields[] = 'dobTmp';
                }
                $fields[] = 'status';

                if($ext = \common\helpers\Acl::checkExtensionAllowed('CustomerCode')) {
                    /* @var \common\extensions\CustomerCode */
                    if ($ext::isEnabledErpId()) {
                        if ( \common\helpers\Acl::checkExtension($ext, 'readonlyInput') ) {
                            if (!$ext::readonlyInput('erp_customer_id')) {
                                $fields[] = 'erp_customer_id';
                            }
                        } else {
                            $fields[] = 'erp_customer_id';
                        }
                    }
                    if ($ext::isEnabledErpCode()) {
                        if ( \common\helpers\Acl::checkExtension($ext, 'readonlyInput') ) {
                            if (!$ext::readonlyInput('erp_customer_code')) {
                                $fields[] = 'erp_customer_code';
                            }
                        } else {
                            $fields[] = 'erp_customer_code';
                        }
                    }
                }

                $fields[] = 'dob';
                $fields[] = 'dobTmp';
                $fields[] = 'platform_id';
                $fields[] = 'language_id';
                $fields[] = 'admin_id';
                $fields[] = 'newsletter';
                $fields[] = 'opc_temp_account';
                $fields[] = 'country';
                $fields[] = 'zone_id';

                break;
            case static::SCENARIO_EDIT :
                if (in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'gender';
                }
                if (in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'firstname';
                }
                if (in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'lastname';
                }
                if (in_array(ACCOUNT_EMAIL, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'email_address';
                }
                if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'telephone';
                }
                if (in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'landline';
                }
                if (in_array(ACCOUNT_DOB, ['required', 'required_register', 'visible', 'visible_register'])) {
                    $fields[] = 'dobTmp';
                    $fields[] = 'dob';
                }
                if ($this->useExtending) {
                    $fields[] = 'status';
                    $fields[] = 'group';
                    /* @var \common\extensions\CustomerCode\CustomerCode $ext*/
                    if ($ext = \common\helpers\Extensions::isAllowed('CustomerCode')) {
                        if ($ext::isEnabledErpId()) {
                            $fields[] = 'erp_customer_id';
                        }
                        if ($ext::isEnabledErpCode()) {
                            $fields[] = 'erp_customer_code';
                        }
                    }

                    $fields[] = 'platform_id';
                    $fields[] = 'language_id';
                    $fields[] = 'admin_id';
                    $fields[] = 'opc_temp_account';
                    $fields[] = 'pin';
                    $fields[] = 'can_use_drop_ship';
                }
                break;
        }
        $fields[] = 'terms';
        return $fields;
    }

    public function getRegularOfferList() {
        return [
            '12' => '12 months',
            '24' => '24 months',
            '36' => '36 months',
            '60' => '60 months',
            '0' => 'indefinitely',
        ];
    }

    public function getGenderList() {
        return \common\helpers\Address::getGendersList();
    }

    public function getDefaultCountryId() {
        return $this->country ? $this->country : STORE_COUNTRY;
    }

    public function isShowAddress() {
        return $this->showAddress;
    }

    public function processCustomerAuth() {
        //get success result
        switch ($this->scenario) {
            case static::SCENARIO_LOGIN :
            case static::SCENARIO_LOGIN_TOP :
                if (\common\models\Fraud::blockAddress()) {
                    $this->addError('email_address', TEXT_LOGIN_BLOCKED);
                    return false;
                }
                $customer = new Customer(Customer::LOGIN_STANDALONE);
                $customer->rememberMe = !empty($this->remember);
                if (($pwdCheck = $customer->loginCustomer($this->email_address, $this->password))) {
                    return true;
                } else {
                    if ($pwdCheck === false) {
                        $this->addError('email_address', TEXT_LOGIN_ERROR);
                    } else {
                        $this->addError('email_address', TEXT_LOGIN_REQUEST_NEW_PASSWORD);
                    }
                    return false;
                }
                break;
            case static::SCENARIO_REGISTER :
                $customer = new Customer();
                return $customer->registerCustomer($this);
                break;
            case static::SCENARIO_ENQUIRE :
                $name = STORE_OWNER;
                $email_address = STORE_OWNER_EMAIL_ADDRESS;
                $email_params = array();
                $email_params['USER_NAME'] = $this->name;
                $email_params['COMPANY_NAME'] = $this->company;
                $email_params['USER_EMAIL'] = $this->email_address;
                $email_params['USER_PHONE'] = $this->phone;
                $email_params['ENQUIRY'] = $this->content;
                list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Enquiries', $email_params);
                \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, $email_subject, $email_text, $name, $email_address);
                return true;
                break;
            case static::SCENARIO_GUEST:
                $customer = new Customer();
                $customer->set('guest_email_address', $this->email_address, true); //over storage interface
                //(new \yii\web\Session())->set('guest_email_address', $this->email_address);
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
                break;
            case static::SCENARIO_FAST_ORDER:
                $customer = (new Customer())->createCustomerQo($this);
                if ($this->content) {
                    $_POST['comments'] = $this->content;
                }
                return $customer;
                break;
            case static::SCENARIO_CHECKOUT:

                break;
            case static::SCENARIO_EDIT :
                $vars = func_get_args();
                if (is_object($vars[0]) && $vars[0] instanceof Customer) {
                    $customer = $vars[0];
                    $customer->updateCustomer($this->getAttributesByScenario());
                    \common\models\CustomersInfo::findOne(['customers_info_id' => $customer->customers_id])->update();
                    $book = $customer->getDefaultAddress()->one();
                    if ($book) {
                        $book->entry_firstname = $this->firstname;
                        $book->entry_lastname = $this->lastname;
                        $book->entry_gender = $this->gender;
                        $book->update();
                    }
                }
                break;
        }
        return false;
    }

    public function preloadCustomersData($customer = null) {
        if ($customer instanceof Customer) {
            if ($this->scenario == static::SCENARIO_REGISTER) {
                $this->gender = $customer->customers_gender;
                $this->firstname = $customer->customers_firstname;
                $this->lastname = $customer->customers_lastname;
                $this->dob = $customer->customers_dob;
                $this->email_address = $customer->customers_email_address;
                if ($this->showAddress) {
                    $address = $customer->getDefaultAddress()->one();
                    $this->country = $address->entry_country_id;
                    $this->zone_id = $address->entry_zone_id;
                    if ($this->zone_id) {
                        $qZones = \common\models\Zones::find()->where(['zone_country_id' => $this->country]);
                        if ($qZones->count() > 0) {
                            $qZones = \common\models\Zones::find()->where(['zone_country_id' => $this->country, 'zone_id' => $this->zone_id])->one();
                            if ($qZones) {
                                $this->state = $qZones->zone_name;
                            }
                        }
                    } else {
                        $this->state = $address->entry_state;
                    }
                }
            } else if ($this->scenario == static::SCENARIO_CHECKOUT) {
                $this->email_address = $customer->customers_email_address;
                $this->telephone = $customer->customers_telephone;
                $this->landline = $customer->customers_landline;
                $this->company = $customer->customers_company;
//                $this->company_vat = $customer->customers_company_vat;
                if ($this->useExtending) {
                    $this->gender = $customer->customers_gender;
                    $this->firstname = $customer->customers_firstname;
                    $this->lastname = $customer->customers_lastname;
                }
            } elseif ($this->scenario == static::SCENARIO_EDIT) {
                $this->gender = $customer->customers_gender;
                if (empty($this->gender))
                    $this->gender = 'm';
                $this->email_address = $customer->customers_email_address;
                $this->telephone = $customer->customers_telephone;
                $this->landline = $customer->customers_landline;
                $this->firstname = $customer->customers_firstname;
                $this->lastname = $customer->customers_lastname;
                $this->dob = $customer->customers_dob;
                $this->dobTmp = \common\helpers\Date::date_short($customer->customers_dob);
                if ($this->useExtending) {
                    $this->opc_temp_account = $customer->opc_temp_account;
                    $this->pin = $customer->pin;
                    $this->erp_customer_id = $customer->erp_customer_id;
                    $this->erp_customer_code = $customer->erp_customer_code;
                    $this->status = $customer->customers_status;
                    $this->group = $customer->groups_id;
                    $this->platform_id = $customer->platform_id;
                    $this->language_id = $customer->language_id;
                    $this->admin_id = $customer->admin_id;
                    $this->can_use_drop_ship = $customer->can_use_drop_ship;
                }
            }
        } else {
            if ($this->scenario == static::SCENARIO_CREATE) {
                if (empty($this->gender)) {
                    $this->gender = 'm';
                }
                try {
                    $languageId = (int)\Yii::$app->settings->get('languages_id');
                } catch (\Exception $e) {
                    $languageId = (int)\common\classes\language::defaultId();
                }
                $this->platform_id = \common\classes\platform::defaultId();
                $this->language_id = $languageId;
                if ((int) Yii::$app->session->get('login_id')) {
                  $this->admin_id = (int) Yii::$app->session->get('login_id');
                }
                $this->status = true;
            }
        }
    }

}
