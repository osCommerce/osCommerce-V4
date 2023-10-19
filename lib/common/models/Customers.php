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

namespace common\models;

use common\models\queries\CustomersQuery;
use Yii;
use yii\db\ActiveRecord;
use common\models\Orders;
use yii\db\ColumnSchema;
use yii\db\Query;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "customers".
 *
 * @property int $customers_id
 * @property string $customers_gender
 * @property string $customers_firstname
 * @property string $customers_lastname
 * @property string $customers_dob
 * @property string $customers_email_address
 * @property int $platform_id
 * @property int $customers_default_address_id
 * @property string $customers_telephone
 * @property string $customers_landline
 * @property string $customers_fax
 * @property string $customers_password
 * @property string $customers_newsletter
 * @property string $customers_selected_template
 * @property int $admin_id
 * @property string $customers_alt_email_address
 * @property string $customers_alt_telephone
 * @property string $customers_cell
 * @property int $customers_owc_member
 * @property int $customers_type_id
 * @property int $customers_bonus_points
 * @property string $customers_credit_avail
 * @property int $affiliate_id
 * @property int $groups_id
 * @property int $customers_status
 * @property string $last_xml_import
 * @property string $last_xml_export
 * @property int $opc_temp_account
 * @property string $customers_company
 * @property string $customers_company_vat
 * @property float $credit_amount
 * @property int $sap_servers_id
 * @property int $customers_currency_id
 * @property string $customers_cardcode
 * @property int $currency_switcher
 * @property int $erp_customer_id
 * @property string $erp_customer_code
 * @property int $trustpilot_disabled
 * @property bool $dob_flag [tinyint(1)]
 * @property int $departments_id [int(11)]
 * @property string $pin [varchar(8)]
 * @property int $_api_time_modified [timestamp]
 * @property string $payerreference [varchar(255)]
 * @property int $language_id
 * @property string $auth_key [varchar(32)]
 */
class Customers extends ActiveRecord 
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLE = 0;
    
    public $multi_customer_id = 0;
    public $cart_uid = 0;
    
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'customers';
    }

    public static function findByVar($customerOrModelOrId)
    {
        if ($customerOrModelOrId instanceof self) {
            return $customerOrModelOrId;
        } elseif($customerOrModelOrId instanceof \common\components\Customer)
            return self::findIdentity($customerOrModelOrId->customers_id);
        elseif (is_numeric($customerOrModelOrId)) {
            return self::findIdentity($customerOrModelOrId);
        }
    }

    public static function findByVarCheck($customerOrModelOrId)
    {
        $res = self::findByVar($customerOrModelOrId);
        \common\helpers\Assert::instanceOf($res, self::class);
        \common\helpers\Assert::assert($res->customers_id > 0, 'Not valid customer id: ' . $res->customers_id);
        return $res;
    }

    public static function findIdentity($id){
        return static::findOne(['customers_id' => $id]);
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
       
 
    public function getAuthKey()
    {
        if (!empty(\Yii::$app->params['enableAutoLogin']) && isset($this->auth_key) && empty($this->auth_key)) {
            $this->auth_key = \Yii::$app->security->generateRandomString();
            try {
                $this->save(false);
            } catch (\Exception $ex) {
                \Yii::warning(" #### " . print_r($ex->getMessage(), true), 'TLDEBUG');
            }
        }
        return $this->auth_key;
    }
 
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    public function findIdentityByEmail($email)
    {
        return static::find()
                ->where(['or', ['customers_email_address' => $email], ['erp_customer_code' => $email] ])
                ->andWhere(['customers_status' => 1, 'opc_temp_account' => 0])
                ->limit(1)->one();
    }

// personal_catalolog moved to extension. relation is used nowhere in osc and extensions but maybe somethere in old projects?
//    public function getProducts(){
//        return $this->hasMany(\common\models\Products::className(), ['products_id' => 'products_id'])
//                    ->viaTable('personal_catalog', ['customers_id' => 'customers_id']);
//    }

    public function getAddressBooks(){
        return $this->hasMany(AddressBook::className(), ['customers_id' => 'customers_id'])->joinWith('country');
    }
    
    public function hasAddressBooks(){
        return count($this->getAddressBooks());
    }

    public function init() {
        if($this->isNewRecord){
            $this->platform_id = \common\classes\platform::currentId();
        }
        parent::init();
    }

    public function getCustomersEmails(){
        return $this->hasMany(CustomersEmails::className(), ['customers_id' => 'customers_id']);
    }

    public function getCustomersPhones(){
        return $this->hasMany(CustomersPhones::className(), ['customers_id' => 'customers_id']);
    }

    /*
    public function getAddressBook(){
        return $this->hasMany(AddressBook::className(), ['customers_id' => 'customers_id']);
    }*/
    public function getAddressBook($id){
        return $this->hasOne(AddressBook::className(), ['customers_id' => 'customers_id'])
                ->onCondition(['address_book_id' => $id])->joinWith('country');
    }

/**
 * @deprecated all subscribed customers are in subscribers table now use it (subscribersToLists) instead
 * @return type
 */
    public function getSubscribersToLists() {
        /** @var \common\extensions\Subscribers\Subscribers $subscr  */
        if ($subscr = \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed')) {
            return $this->hasMany(\common\extensions\Subscribers\models\CustomersToLists::class, ['customers_id' => 'customers_id']);
        } else {
            return $this->andWhere(0);
        }
    }

    public function getSubscribersLists() {
        /** @var \common\extensions\Subscribers\Subscribers $subscr  */
        if ($subscr = \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed')) {
            $languages_id = (int) \Yii::$app->settings->get('languages_id');
            return $this->hasMany(\common\extensions\Subscribers\models\SubscribersLists::class, ['subscribers_lists_id' => 'subscribers_lists_id'])
                ->via('subscribersToLists')
                ->andOnCondition(['language_id' => $languages_id]);
        } else {
            return $this->andWhere(0);
        }
    }

    public function getCustomersInfo(){
        return $this->hasOne(CustomersInfo::className(), ['customers_info_id' => 'customers_id']);
    }

    /**
     * @param $email
     *
     * @return Customers
     */
    public static function findByEmail($email) {

        $customerModel = static::find()
            ->where([ 'customers_email_address' => $email ])
            ->limit(1)
            ->one();
        if ( !$customerModel ) {
            $customerModel = static::find()
                ->joinWith('customersEmails')
                ->where([ CustomersEmails::tableName() . '.customers_email' => $email ])
                ->limit(1)
                ->one();
        }
        return $customerModel;
        /*
        return static::find()
            ->joinWith('customersEmails')
            ->where([ CustomersEmails::tableName() . '.customers_email' => $email ])
            ->orWhere([static::tableName() . '.customers_email_address' => $email ])
            ->limit(1)
            ->one();
        */
    }
    
    /**
     * @param $email
     *
     * @return Customers
     */
    public static function findByMultiEmail($email) {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('DealersMultiCustomers', 'allowed')) {
            return $ext::findByMultiEmail($email);
        }
      if ($CustomersMultiEmails = \common\helpers\Acl::checkExtensionAllowed('CustomersMultiEmails', 'allowed')) {
        //2do same agent (email) of several customers
        $multi = \common\extensions\CustomersMultiEmails\models\CustomersMultiEmails::find()
                ->where(['customers_email' => $email])
                ->limit(1)->one();
        if (is_object($multi)) {
            $customer = static::find()
                ->where(['customers_id' => $multi->customers_id])
                ->andWhere(['customers_status' => 1, 'opc_temp_account' => 0])
                ->limit(1)->one();
            if (is_object($customer)) {
                $customer->customers_email_address = $multi->customers_email;
                $customer->customers_password = $multi->customers_password;
                $customer->customers_firstname = $multi->customers_firstname;
                $customer->customers_lastname = $multi->customers_lastname;
                
                $customer->multi_customer_id = $multi->id;
                $customer->cart_uid = $multi->cart_uid;

                return $customer;
            }
        }
      }
      return NULL;
    }

    /**
     * @param $email
     *
     * @return Customers
     */
    public static function findByPhone($email) {
        return static::find()
            ->joinWith('customersPhones')
            ->where([ CustomersPhones::tableName() . '.customers_phone' => $email ])
            ->orWhere([static::tableName() . '.customers_telephone' => $email ])
            ->limit(1)
            ->one();
    }

    public function addPhone($phone){
        if(!($customer = static::findByPhone($phone))){
            if(!trim($this->customers_telephone)){
                $this->customers_telephone = $phone;
                $this->save(false);
                (new CustomersPhones(['customers_phone' => $phone]))->link('customer', $this);
            }
        }
        return $this;
    }

    public function addEmail($email){
        if(!($customer = static::findByPhone($email))){
            if(!trim($this->customers_email_address)) {
                $this->customers_email_address = $email;
                $this->save(false);
                (new CustomersEmails([ 'customers_email' => $email ]))->link( 'customer', $this );
            }
        }
        return $this;
    }
    
    public function getOrders()
    {
        return $this->hasMany(Orders::className(), ['customers_id' => 'customers_id']);
    }
    
    public function getOrdersTotals(){
        return $this->hasMany(OrdersTotal::className(), ['orders_id' => 'orders_id'])->viaTable(Orders::tableName(), ['customers_id' => 'customers_id']);
    }

    /**
    * @param $withTax boolean, $from, $to - datetime db format or null
    * @return ordered total amount
    **/
    public function fetchOrderTotalAmount($withTax = false, $from = null, $to = null){
        $amount = 0;
        $query = $this->getOrdersTotals()->onCondition('class="ot_total"')->innerJoinWith([
            'order' => function (\yii\db\ActiveQuery $query) use ($from, $to){
                if (!is_null($from)){
                    $query->andOnCondition(['>=','date_purchased', $from]);
                }
                if (!is_null($to)){
                    $query->andOnCondition(['<=','date_purchased', $to]);
                }
                if (defined('ORDER_COMPLETE_STATUSES')){
                    $completedStatuses = array_map("intval", explode(",", ORDER_COMPLETE_STATUSES));
                    if ($completedStatuses) $query->andOnCondition(['orders_status' => $completedStatuses]);
                }
            }
        ]);
        
        $list = $query->asArray()->all();
        if ($list){
            if ($withTax){
                $amount = array_sum(ArrayHelper::getColumn($list, 'value_inc_tax'));
            } else {
                $amount = array_sum(ArrayHelper::getColumn($list, 'value_exc_vat'));
            }
        }
        return $amount;
    }

    public function getDefaultAddress(){
    	return $this->hasOne(AddressBook::className(), ['address_book_id' => 'customers_default_address_id'])
                ->joinWith('country');
    }
    
    public function getDefaultShippingAddress() {
        if (\common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')) {
            return $this->hasOne(AddressBook::className(), ['address_book_id' => 'customers_shipping_address_id'])
                ->joinWith('country');
        }
        return $this->getDefaultAddress();
    }

    public function getInfo(){
    	return $this->hasOne(CustomersInfo::class, ['customers_info_id' => 'customers_id']);
    }

    public function getGroup(){
    	return $this->hasOne(Groups::class, ['groups_id' => 'groups_id']);
    }

    public function editCustomersPassword($customersPassword): void
    {
        $this->customers_password = $customersPassword;
        $this->save();
    }

    public function editCustomersNewsletter($customersNewsletter): void
    {
        /** @var \common\extensions\Subscribers\Subscribers $subscr  */
        if ($subscr = \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed')) {
            $this->customers_newsletter = $customersNewsletter;
            $this->save();
            $subscr::onSaveCustomer($this);
        }
    }

    public function editCustomerDetails(array $customerDetails): void
    {
        $this->customers_email_address = $customerDetails['customers_email_address'];
        $this->customers_gender = $customerDetails['customers_gender'];
        $this->customers_firstname = $customerDetails['customers_firstname'];
        $this->customers_lastname = $customerDetails['customers_lastname'];
        $this->customers_dob = date('Y-m-d H:i:s');
        $this->customers_telephone = $customerDetails['customers_telephone'];
        $this->customers_landline = $customerDetails['customers_landline'];
        $this->customers_company = $customerDetails['customers_company'];
        //$this->customers_company_vat = $customerDetails['customers_company_vat'];
        $this->save();
    }

    /**
     * {@inheritdoc}
     * @return CustomersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CustomersQuery(get_called_class());
    }
    
    public function beforeDelete() {
        AddressBook::deleteAll(['customers_id' => $this->customers_id]);
        CustomersBasket::deleteAll(['customers_id' => $this->customers_id]);
        CustomersBasketAttributes::deleteAll(['customers_id' => $this->customers_id]);
        CustomersCreditHistory::deleteAll(['customers_id' => $this->customers_id]);
        CustomersEmails::deleteAll(['customers_id' => $this->customers_id]);
        CustomersInfo::deleteAll(['customers_info_id' => $this->customers_id]);
        CustomersPhones::deleteAll(['customers_id' => $this->customers_id]);
        return parent::beforeDelete();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ( $insert ) {
            
            $this->auth_key = \Yii::$app->security->generateRandomString();

            foreach ($this->getTableSchema()->columns as $column) {
                /**
                 * @var $column ColumnSchema
                 */
                if (!$column->allowNull && ($this->getAttribute($column->name) === null || $column->dbTypecast($this->getAttribute($column->name))===null) ) {
                    $defValue = $column->defaultValue;
                    if ( $column->dbTypecast($defValue)===null ) {
                        $defTypeValue = [
                            'boolean' => 0,
                            'float' => 0.0,
                            'decimal' => 0.0,
                        ];
                        if ( stripos($column->type,'int')!==false ) {
                            $defValue = 0;
                        }else{
                            $defValue = isset($defTypeValue[$column->type])?$defTypeValue[$column->type]:'';
                        }
                    }
                    $this->setAttribute($column->name, $defValue);
                }
            }
        } else {
            /// reset remember me auth_key if customer's password is changed.
            $dirty = $this->getDirtyAttributes(['customers_password']);
            if (!empty($dirty)) {
                $this->auth_key = \Yii::$app->security->generateRandomString();
            }
        }

        return true;
    }

    public function isGuest(): bool
    {
        return $this->opc_temp_account === 1;
    }
}
