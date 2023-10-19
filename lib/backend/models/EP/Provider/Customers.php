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

namespace backend\models\EP\Provider;


use backend\models\EP\ArrayTransform;
use backend\models\EP\Exception;
use backend\models\EP\Messages;
use backend\models\EP\Tools;
use common\api\models\AR\Customer;
use common\api\models\AR\Customer\Address;

class Customers extends ProviderAbstract implements ImportInterface, ExportInterface
{

    protected $data = array();
    protected $entry_counter = 0;

    protected $export_query;

    function init()
    {
        parent::init();
        $this->initFields();

    }

    protected function initFields()
    {
        $this->fields = array();
        $this->fields = array();
        $this->fields[] = array( 'name' => 'customer.customers_email_address', 'value' => 'Customers Email Address', 'is_key_part'=>true, 'is_key'=>true,);
        $dummy = new Customer();
        $dummy->setModelFlag('credit_amount_delta', true);
        $dummy->setModelFlag('customers_bonus_points_delta', true);
        $attr = $dummy->getPossibleKeys();
        $columnCover = [
            'customers_firstname' => ['value'=>'Customers Firstname', 'is_key_part'=>true,],
            'customers_lastname' => ['value'=>'Customers Lastname', 'is_key_part'=>true,],
            'customers_email_address' => false,
            'customers_id' => false,
            'dob_flag' => ['value'=>'GDPR Dob Flag'],
            'departments_id' => false,
            'customers_default_address_id' => false,
            'admin_id' => false,
            'credit_amount' => ['value'=>'Credit Amount (read only)'],
            'customers_bonus_points' => ['value'=>'Customers Bonus Points (read only)'],
            'customers_company' => ['value'=>'Account Company'],
            'customers_company_vat' => false,
            'sap_servers_id' => false,
            '_api_time_modified' => false,
            'opc_temp_account' => ['value' => 'Is Guest?'],
            'customers_dob' => ['get'=>'getDate', 'set'=>'setDate'],
            'platform_id' => ['value' => 'Platform', 'get'=>'getPlatformName', 'set'=>'setPlatformName'],
            'groups_id' => ['value' => 'Group', 'get'=>'getGroupName', 'set'=>'setGroupName'],
            'customers_currency_id' => ['value' => 'Currency', 'get'=>'getCurrencyCode', 'set'=>'setCurrencyCode'],
        ];

        foreach ( $attr as $key ) {
            $columnDescribe = array( 'name' => 'customer.'.$key, 'value' => ucwords(preg_replace('/[ \._]/',' ',$key)), );
            if ( isset($columnCover[$key]) ) {
                if ($columnCover[$key]==false) continue;
                if (is_array($columnCover[$key])) {
                    $columnDescribe = array_merge($columnDescribe, $columnCover[$key]);
                }
            }
            $this->fields[] = $columnDescribe;
        }

        $dummy = new Address();
        $attr = $dummy->getPossibleKeys();
        $columnCover = [
            'address_book_id' => false,
            '_api_time_modified' => false,
            'is_default' => false,
            'entry_company' => ['value' => 'Company Name'],
            'entry_company_vat' => ['value' => 'Company Vat'],
            'entry_country_id' => ['value' => 'Address Country','get'=>'getCountryISO', 'set'=>'setCountry'],
            'entry_zone_id' => false,//['value' => 'Address State','get'=>'getCountryState', 'set'=>'setCountryState'],

            'entry_gender' => false,
            'entry_telephone' => ['value' => 'Phone'],
            'entry_firstname' => ['value' => 'Address Firstname'],
            'entry_lastname' => ['value' => 'Address Lastname'],
            'entry_street_address' => ['value' => 'Street address'],
            'entry_suburb' => ['value' => 'Suburb'],
            'entry_postcode' => ['value' => 'Postcode'],
            'entry_city' => ['value' => 'City'],
            'entry_state' => ['value' => 'State'],

        ];

        $pattern = [];
        foreach ( $attr as $key ) {
            $columnDescribe = array( 'name' => 'address.'.$key, 'value' => ucwords(preg_replace('/[ \._]/',' ',str_replace('entry_','address_',$key))), );
            if ( isset($columnCover[$key]) ) {
                if ($columnCover[$key]==false) continue;
                if (is_array($columnCover[$key])) {
                    $columnDescribe = array_merge($columnDescribe, $columnCover[$key]);
                }
            }
            //$this->fields[] = $columnDescribe;
            $pattern[] = $columnDescribe;
        }

        for ($i=0;$i<=Customer::maxAddresses();$i++) { // Up to 5 addressbook etries, + 1 new, + default is first
          foreach ($pattern as $v) {
            $v['name'] = str_replace('address.', 'address.' . $i . '.', $v['name']);
            if ($i==0) {
              $v['value'] = 'Default ' . $v['value'];
            } else {
              $v['value'] .= ' ' . $i;
            }
            $this->fields[] = $v;
          }
        }
//        $dummy = new Customer\Info();
//        $ss = $dummy->getPossibleKeys();
        $this->fields[] = [
            'name'=>'info.customers_info_date_account_created',
            'value' => 'Date Created',
            'set' => 'setDatetime',
        ];
    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);

        $main_source = $this->main_source;

        $filter_sql = '';
        if (is_array($filter)) {

        }

        $main_sql =
            "SELECT customers_id " .
            "FROM " . TABLE_CUSTOMERS . " " .
            "WHERE 1 {$filter_sql} ".
            " AND customers_email_address != 'removed' ".
            "ORDER BY customers_id";

        $this->export_query = tep_db_query($main_sql);
    }

    public function exportRow()
    {
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;

        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;

        static $exportGroupColumns = false;
        if ( !is_array($exportGroupColumns) ) {
            $exportKeys = array_keys($export_columns);
            $exportGroupColumns = ArrayTransform::convertFlatToMultiDimensional(array_combine($exportKeys,$exportKeys));
        }
        $customerModel = Customer::findOne($this->data['customers_id']);
        $customerData = $customerModel->exportArray([]);

        if ( !empty($exportGroupColumns['customer']) ) {
            foreach ($customerData as $__key=>$__val) {
                if ( is_array($__val) ) continue;
                $this->data['customer.'.$__key] = $__val;
            }
        }


        if ( is_array($customerData['addresses']) ) {
            foreach ($customerData['addresses'] as $idx => $address){
                //if ( $customerData['customers_default_address_id']!=$address['address_book_id'] ) continue;
                foreach ($address as $__key=>$__val) {
                    $this->data['address.' . $idx . '.' .$__key] = $__val;
                }
            }
        }

        if ( is_array($customerData['info']) ) {
            foreach ($customerData['info'] as $address){
                foreach ($address as $__key=>$__val) {
                    $this->data['info.'.$__key] = $__val;
                }
            }
        }

        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['customers_id']));
            }
            $this->data[$db_key] = isset($this->data[$db_key])?$this->data[$db_key]:'';
        }
        return $this->data;
    }

    public function importRow($data, Messages $message)
    {
        $this->buildSources( array_keys($data) );

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;

        $this->file_primary_columns;

        $extra_cols = array_intersect_key($data, $this->file_primary_columns);

        $this->data = $data;
        $is_updated = false;
        if (!array_key_exists($file_primary_column, $data) && count($extra_cols)!=count($this->file_primary_columns) ) {
            throw new Exception('Primary key not found in file');
        }

        $lookup_email = $this->data[$file_primary_column];
        if ( empty($lookup_email) ) {
            $customerModels = Customer::find()
                ->where([
                    'customers_firstname' => $extra_cols['customer.customers_firstname'],
                    'customers_lastname' => $extra_cols['customer.customers_lastname'],
                ])
                ->all();
        }else{
            $customerModels = Customer::find()
                ->where(['customers_email_address'=>$lookup_email])
                ->all();
        }
        if ( count($customerModels)>1 ){
            $message->info('Duplicate email address. Skipped');
            return false;
        }

        if ( count($customerModels)==1 ) {
            $customerModel = reset($customerModels);
        }else{
            $customerModel = new Customer();
            $customerModel->loadDefaultValues();
        }
        $this->data['customers_id'] = $customerModel->customers_id;
        // {{
        $customerModel->setModelFlag('credit_amount_delta', true);
        $customerModel->setModelFlag('customers_bonus_points_delta', true);
        // }}

        $update_data_array = array();
        foreach ($main_source['columns'] as $file_column => $db_column) {
            if (!array_key_exists($file_column, $data)) continue;

            if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['customers_id'], $message));
            }
            if ( array_key_exists($file_column, $this->data) ) {
                $update_data_array[$db_column] = $this->data[$file_column];
            }
        }

        $importData = ArrayTransform::convertFlatToMultiDimensional($this->data);
        $customerImportData = $importData['customer'];
        /*
        if ( $customerModel->customers_default_address_id ) {
            $importData['address']['address_book_id'] = $customerModel->customers_default_address_id;
        }
         */
        if ( isset($importData['address']) && is_array($importData['address']) && count($importData['address'])>0 ) {
            foreach ($importData['address'] as $idx=>$address) {
                // need remove all empty addresses
                $all_empty = true;
                foreach ($address as $a_val) {
                    if ( is_numeric($a_val) ) {
                        if ( !empty($a_val) ) {
                            $all_empty = false;
                            break;
                        }
                    }elseif ( !empty($a_val) ) {
                        $all_empty = false;
                        break;
                    }
                }
                if ($all_empty) {
                    unset($importData['address'][$idx]);
                }
            }
            $importData['address'] = array_values($importData['address']);
            $importData['address'][0]['is_default'] = 1;
            $customerImportData['addresses'] = $importData['address'];
            $customerModel->indexedCollectionAppendMode('addresses',true);
        }

        if ( isset($importData['info']) && is_array($importData['info']) ) {
            $customerImportData['info'] = [$importData['info']];
        }

        $customerModel->importArray($customerImportData);
        if ($customerModel->save(false) ) {
            $this->entry_counter++;
        }

        return true;
    }

    public function postProcess(Messages $message)
    {
        $message->info('Processed '.$this->entry_counter.' customers');
        $message->info('Done');
    }

    protected function getPlatformName()
    {
        return Tools::getInstance()->getPlatformName($this->data['customer.platform_id']);
    }

    protected function setPlatformName($field_data, $customer_id, Messages $message)
    {
        $file_value = $this->data[$field_data['name']];

        $db_value = Tools::getInstance()->getPlatformId($file_value);
        if ( empty($db_value) ) {
            $message->info('Platform "'.$file_value.'" not found');
            $db_value = \common\classes\platform::defaultId();
        }

        $this->data[$field_data['name']] = (int)$db_value;
    }

    protected function getGroupName()
    {
        return ($this->data['customer.groups_id']==0?'':Tools::getInstance()->getCustomerGroupName($this->data['customer.groups_id']));
    }

    protected function setGroupName($field_data, $customer_id, Messages $message)
    {
        $file_value = $this->data[$field_data['name']];
        if ( $file_value==='' ) {
            $db_value = 0;
        }else{
            $db_value = Tools::getInstance()->getCustomerGroupId($file_value);
            if (empty($db_value)) {
                $message->info('Customer group "'.$file_value.'" not found');
            }
        }
        $this->data[$field_data['name']] = (int)$db_value;

    }

    protected function getCurrencyCode()
    {
        return \common\helpers\Currencies::getCurrencyCode($this->data['customer.customers_currency_id']);
    }

    protected function setCurrencyCode($field_data, $customer_id, Messages $message)
    {
        $file_value = $this->data[$field_data['name']];
        $db_value = \common\helpers\Currencies::getCurrencyId($file_value);
        if ( $db_value===false ) {
            $message->info('Currency code "'.$file_value.'" not valid');
            $db_value = \common\helpers\Currencies::systemCurrencyCode();
        }
        $this->data[$field_data['name']] = (int)$db_value;
    }

    protected function getCountryISO($field_data)
    {
        $file_value = $this->data[$field_data['name']] ?? null;
        if (empty($file_value)) {
          return '';
        }
        $countryInfo = Tools::getInstance()->getCountryInfo($file_value);
        return is_array($countryInfo)?$countryInfo['countries_iso_code_2']:'';
    }

    protected function setCountry($field_data, $customer_id, Messages $message)
    {
        $file_value = $this->data[$field_data['name']];
        $db_value = Tools::getInstance()->getCountryId($file_value);
        if ( empty($db_value) ) {
            $message->info('Country "'.$file_value.'" not found');
        }
        $this->data[$field_data['name']] = (int)$db_value;
    }

    protected function setDatetime($field_data, $customer_id, Messages $message)
    {
        $file_value = $this->data[$field_data['name']];
        $db_value = Tools::getInstance()->parseDate($file_value);
        if ( empty($db_value) || $db_value<1971 ) {
            $db_value = date('Y-m-d H:i:s');
        }
        $this->data[$field_data['name']] = $db_value;
        return $this->data[$field_data['name']];
    }

    protected function setDate($field_data, $customer_id, Messages $message)
    {
        $file_value = $this->data[$field_data['name']];
        $db_value = Tools::getInstance()->parseDate($file_value);
        $this->data[$field_data['name']] = $db_value;
        return $this->data[$field_data['name']];
    }

    protected function getDate($field_data, $customer_id)
    {
        $this->data[$field_data['name']] = substr($this->data[$field_data['name']],0,10);
        return $this->data[$field_data['name']];
    }

}