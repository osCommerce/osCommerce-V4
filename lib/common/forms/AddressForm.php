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
namespace common\forms;

use Yii;
use yii\base\Model;
use common\helpers\Address;
use common\helpers\Country;

class AddressForm extends Model {    
    
    CONST CUSTOM_ADDRESS = 1;
    CONST SHIPPING_ADDRESS = 2;
    CONST BILLING_ADDRESS = 3;
    
    private $_addressesTypes = [];    
    
    public $addressType  = null;
    
    private $formName = null;
    private $reflector;
    
    protected $_prefix;
    
    public function __construct($config = array()){
        $this->reflector = new \ReflectionClass($this);
        
        $this->loadAddressTypes();
        
        if (!in_array($config['scenario'], $this->_addressesTypes)){
            throw new \Exception('Undefined address type');
        }
        $this->addressType = $config['scenario'];
        
        $this->setFormName();
        
        $this->definePrefix();
        
        parent::__construct($config);
    }
    
    protected function setFormName(){
        if ($this->addressType){
            $const = array_flip($this->reflector->getConstants());
            $this->formName = \yii\helpers\Inflector::id2camel(strtolower($const[$this->addressType]));
        }
    }
    
    public function formName(){        
        return $this->formName;
    }
    
    public function beforeValidate() {
        foreach ($this->attributes as $attribute_name => $attribute_value) {
            if (is_string($attribute_value)) {
                $this->$attribute_name = \yii\helpers\HtmlPurifier::process($attribute_value);
            }
        }
        return parent::beforeValidate();
    }

    private function loadAddressTypes(){
        $this->_addressesTypes = $this->_getDefinedScenarios();
    }
    
    public function rules() {
        return [
            [['address_book_id', 'company', 'company_vat', 'customs_number', 'gender', 'firstname', 'lastname', 'telephone', 'email_address', 'postcode', 'street_address', 'suburb', 'city', 'state', 'country', 'zone_id', 'drop_ship', 'type'], 'stronglyRequired', 'skipOnEmpty' => false],
            [['country', 'zone_id'], 'defaultGeoValues', 'skipOnEmpty' => false],
            ['as_preferred', 'default', 'value' => 0]
            //['postcode', 'inExtensions', 'skipOnEmpty' => false, 'on' => [static::SHIPPING_ADDRESS]],
        ];
    }
    
    private function getEntryLabel($label){
        $label = strtoupper($label);
        return ($this->addressType == static::SHIPPING_ADDRESS && defined('SHIP_'.$label) ? constant('SHIP_'.$label) : constant('ENTRY_'.$label) );
    }
    
    private $isLightCheck = false;
    public function setLightCheck(bool $value){
        $this->isLightCheck = $value;
    }
    
    public function isLightCheck(){
        return $this->isLightCheck;
    }

    private function checkSpamHack($val, $capitals=false){
        return (//$capitals || 
                strpos($val, '<')!==false ||
                strpos($val, 'https://')!==false ||
                strpos($val, 'http://')!==false ||
                strip_tags($val) != $val
            );
    }
    public function stronglyRequired($attribute, $params){
        if (!$this->isLightCheck && $this->has($attribute, false)){
            switch ($attribute){
                case 'gender':
                    if (!in_array($this->$attribute, array_keys($this->getGendersList()))){
                        $this->addError($attribute, ENTRY_GENDER_ERROR);
                    }
                    break;
                case 'firstname':
                    if ($this->checkSpamHack($this->$attribute, true) || strlen($this->$attribute) < ENTRY_FIRST_NAME_MIN_LENGTH) {
                        $this->addError($attribute, sprintf($this->getEntryLabel('FIRST_NAME_ERROR'), ENTRY_FIRST_NAME_MIN_LENGTH));
                    }
                    break;
                case 'lastname':
                    if ($this->checkSpamHack($this->$attribute, true) || strlen($this->$attribute) < ENTRY_LAST_NAME_MIN_LENGTH) {
                        $this->addError($attribute, sprintf($this->getEntryLabel('LAST_NAME_ERROR'), ENTRY_LAST_NAME_MIN_LENGTH));
                    }
                    break;
                case 'company':
                    if ($this->checkSpamHack($this->$attribute) || empty($this->$attribute)){
                        $this->addError($attribute, ENTRY_COMPANY_ERROR);
                    }
                    break;
                case 'company_vat':
                    if ($this->checkSpamHack($this->$attribute) || (empty($this->$attribute) || !\common\helpers\Validations::checkVAT($this->$attribute))){
                        $this->addError($attribute, ENTRY_VAT_ID_ERROR);
                    }
                    break;
                case 'customs_number':
                    $cfg = $this->up('CUSTOMS_NUMBER');
                    if ($cfg && empty($this->$attribute) && (
                        ( in_array($cfg, ['required', 'required_register']) ) ||
                        ( in_array($cfg, ['required_company']) && !empty($this->company) )
                        )) {
                        $this->addError($attribute, TEXT_CUSTOMS_NUMBER_ERROR);
                    }
                    break;
                case 'postcode':
                    if ($this->checkSpamHack($this->$attribute) || strlen($this->$attribute) < ENTRY_POSTCODE_MIN_LENGTH){
                        $this->addError($attribute, sprintf($this->getEntryLabel('POST_CODE_ERROR'), ENTRY_POSTCODE_MIN_LENGTH));
                    }
                    break;
                case 'street_address':
                    if ($this->checkSpamHack($this->$attribute) || strlen($this->$attribute) < ENTRY_STREET_ADDRESS_MIN_LENGTH){
                        $this->addError($attribute, sprintf($this->getEntryLabel('STREET_ADDRESS_ERROR'), ENTRY_STREET_ADDRESS_MIN_LENGTH));
                    }
                    break;
                case 'suburb':
                    if ($this->checkSpamHack($this->$attribute) || empty($this->$attribute)){
                        $this->addError($attribute, $this->getEntryLabel('SUBURB_ERROR'));
                    }
                    break;
                case 'city':
                    if ($this->checkSpamHack($this->$attribute) || strlen($this->$attribute) < ENTRY_CITY_MIN_LENGTH){
                        $this->addError($attribute, sprintf($this->getEntryLabel('CITY_ERROR'), ENTRY_STREET_ADDRESS_MIN_LENGTH));
                    }
                    break;
                case 'country':
                    if (!is_numeric($this->$attribute)){
                        $this->addError($attribute, ENTRY_COUNTRY_ERROR);
                    }
                    break;
            }
        }
        if ($attribute == 'state'){
            $this->zone_id = 0;
            $qZones = \common\models\Zones::find()->where(['zone_country_id' => $this->country]);
            if ($qZones->count() > 0){
                $qZones = \common\models\Zones::find()->where(['zone_country_id' => $this->country, 'zone_name' => $this->$attribute])->all();
                if (count($qZones) == 1){
                    $this->zone_id = $qZones[0]->zone_id;
                } else{
                    if ($this->has($attribute, $this->isLightCheck)){
                        $this->addError($attribute, ENTRY_STATE_ERROR_SELECT);
                    }
                }
            } else {
                if (strlen($this->$attribute) < ENTRY_STATE_MIN_LENGTH && $this->has($attribute, $this->isLightCheck)) {
                    $this->addError($attribute, sprintf(ENTRY_STATE_ERROR, ENTRY_STATE_MIN_LENGTH));
                }
            }
        }
    }
    
    public function defaultGeoValues(){
        if (is_null($this->country)){
            $this->country = (int) STORE_COUNTRY;
        }
        if (is_null($this->zone_id)){
            $this->zone_id = (int) STORE_ZONE;
        }
    }
    
    private function _getDefinedScenarios(){
        return [static::CUSTOM_ADDRESS, static::SHIPPING_ADDRESS, static::BILLING_ADDRESS];
    }

    public function scenarios() {
        $_sc = [];
        foreach($this->_getDefinedScenarios() as $scena){
            $_sc[$scena] = $this->collectFields();
        }
        return $_sc;
    }
    
    public $address_book_id;
    public $company;
    public $company_vat;
    public $company_vat_date;
    public $company_vat_status;
    public $customs_number;
    public $customs_number_date;
    public $customs_number_status;
    public $gender;
    public $firstname;
    public $lastname;    
    public $telephone;
    public $email_address;
    public $postcode;
    public $street_address;
    public $suburb;
    public $city;
    public $state;
    public $country;
    public $zone_id;
    public $drop_ship;
    public $type;
    
    public $as_preferred;
    
    public function getActiveAttributes(){
        return $this->getAttributes(null, ['addressType', 'as_preferred']);
    }
    
    public function collectConfigurableFields($includeVisible = true){
        if ($includeVisible) {
            $fields = ['telephone', 'email_address', 'drop_ship'];
            if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
                $fields[] = 'type';
            }
        } else {
            $fields = [];
        }
        $publicFields = $this->reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
        if (is_array($publicFields)){
            foreach($publicFields as $_field){
                if ($this->has($_field->name, $includeVisible)){
                    $fields[] = $_field->name;
                }
            }
        }
        return $fields;
    }

    public function collectFields(){
        $fields = ['address_book_id', 'as_preferred', 'telephone', 'email_address', 'drop_ship'];
        if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
            $fields[] = 'type';
        }
        
        $fields = array_merge($fields, $this->collectConfigurableFields(true));
        
        return $fields;
    }
    
    public function definePrefix(){
        
        switch($this->addressType){
            case static::SHIPPING_ADDRESS :
                $this->_prefix = 'SHIPPING_';
                break;
            case static::BILLING_ADDRESS :
                $this->_prefix = 'BILLING_';
                break;
            default :
                $this->_prefix = 'ACCOUNT_';
                break;
        }
    }
    
    public function getPrefix(){
        return $this->_prefix;
    }
    
    public function up($postfix){
        return defined($this->_prefix . strtoupper($postfix)) ? constant($this->_prefix . strtoupper($postfix)) : false;
    }
    
    public function get($postfix){
        return $this->_prefix . $postfix;
    }
    
    public function has($postfix, $includeVisible = true){
        if ($includeVisible){
            if ($_c = $this->up($postfix)){
                return in_array($_c, ['required', 'required_register', 'visible', 'visible_register', 'required_company']);
            }
        } else {
            if ($_c = $this->up($postfix)){
                return in_array($_c, ['required', 'required_register', 'required_company']);
            }
        }   
        return false;
    }
    
    public function getGendersList(){
        return \common\helpers\Address::getGendersList();
    }
    
    public function getAllowedCountries(){
        $_countries = Country::get_countries('', false, '', strtolower(substr($this->_prefix, 0, 4)));
        $_countries = \yii\helpers\ArrayHelper::map($_countries, 'countries_id', 'text');
        return $_countries;
    }
    
    public function getAllowedCountriesISO($iso = 'iso_code_2'){
        $_countries = Country::get_countries('', false, '', strtolower(substr($this->_prefix, 0, 4)));
        $_countries = \yii\helpers\ArrayHelper::map($_countries, 'countries_id', 'countries_'.$iso);
        return $_countries;
    }    
    
    public function preload($data = []){
        if (is_array($data)|| is_object($data)){
            foreach($data as $name => $value){
                if (is_array($data[$name])){
                    $this->preload($value);
                } else {
                    try{
                        if ($this->hasProperty($name)){
                            $this->{$name} = $value;
                        } else if (strlen(substr($name, 6)) >0 &&  $this->hasProperty(substr($name, 6))){
                            $this->{substr($name, 6)} = $value;
                        }
                        if($name == 'country_id' || substr($name, 6) == 'country_id'){
                            $this->country = $value;
                        }
                    } catch (\Exception $ex) {
                        //var_dump($ex->getMessage(), $name, $value);
                      \Yii::error($ex->getMessage() . ' $name ' . $name . ' $value ' .  $value);
                    }
                }    
            }
            $this->obtainState();
        }
        $this->preloadDefault();
    }
    
    public function preloadDefault(){
        if (empty($this->gender)) $this->gender = 'm';
        if (!is_numeric($this->country)) $this->country = (int)STORE_COUNTRY;
    }
    
    public function obtainState(){
        if ($this->zone_id){
            $qZones = \common\models\Zones::find()->where(['zone_country_id' => $this->country]);
            if ($qZones->count() > 0){
                $qZones = \common\models\Zones::find()->where(['zone_country_id' => $this->country, 'zone_id' => $this->zone_id])->one();
                if ($qZones){
                    $this->state = $qZones->zone_name;
                }
            }
        }
    }
    
    public function notEmpty($withCountry = false){
        return !empty($this->company) || !empty($this->firstname) || !empty($this->lastname) || !empty($this->postcode) || !empty($this->street_address) || !empty($this->city) || !empty($this->state) || ($withCountry && !empty($this->country));
    }
    
    public function customerAddressIsReady(){
        $ready = true;
        foreach( $this->collectConfigurableFields(false) as $field){
            if (empty($this->$field)) $ready = false;
        }
        return $ready;
    }
}
