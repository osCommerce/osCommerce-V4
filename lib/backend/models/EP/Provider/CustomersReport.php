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

use backend\models\EP;

class CustomersReport extends ProviderAbstract implements ExportInterface
{

    protected $data = array();
    protected $EPtools;
    protected $entry_counter = 0;

    protected $export_query;

    function init()
    {
        parent::init();
        $this->initFields();
        $this->EPtools = new EP\Tools();

    }

    public function initFields()
    {
        $this->fields = array();



        $this->fields[] = array( 'name' => 'platform_id', 'value' => 'Frontend Name', 'get' => 'getPlatformName' );
        $this->fields[] = array( 'name' => 'opc_temp_account', 'value' => 'Is Guest?', 'get' => 'getIsGuest' );
        $this->fields[] = array( 'name' => 'customers_email_address', 'value' => 'Email' );
        $this->fields[] = array( 'name' => 'customers_firstname', 'value' => 'First Name' );
        $this->fields[] = array( 'name' => 'customers_lastname', 'value' => 'Last Name' );
        $this->fields[] = array( 'name' => 'customers_company', 'value' => 'Company' );
        $this->fields[] = array( 'name' => 'customers_telephone', 'value' => 'Telephone' );
        $this->fields[] = array( 'name' => 'customers_newsletter', 'value' => 'Newsletter status', 'get' => 'getNewsletterStatus' );
        $this->fields[] = array( 'name' => 'groups_id','value'=>'Group Name', 'get'=>'getGroupName');
        $this->fields[] = array( 'name' => 'customers_status', 'value'=> 'Status');
        $this->fields[] = array( 'name' => 'entry_country_id', 'value'=> 'Country', 'get'=>'getCountryName');
        $this->fields[] = array( 'name' => 'customers_info_date_account_created', 'value'=> 'Account Create Date', 'get'=>'getDate');
    }

    public function getPlatformName($field_data)
    {
        $key = $field_data['name'];
        if ( $this->data[$key] ) {
            $this->data[$key] = $this->EPtools->getPlatformName($this->data[$key]);
        }
        return $this->data[$key];
    }

    public function getIsGuest($field_data)
    {
        $key = $field_data['name'];
        $this->data[$key] = $this->data[$key]?'Y':'';
        return $this->data[$key];
    }

    public function getNewsletterStatus($field_data)
    {
        $key = $field_data['name'];
        $this->data[$key] = $this->data[$key]?'Subscribed':'';
        return $this->data[$key];
    }

    public function getGroupName($field_data)
    {
        static $groups = array();
        $key = $field_data['name'];
        $groupId = $this->data[$key];
        $this->data[$key] = '';
        if ( $groupId ) {
            if ( !isset($groups[$groupId]) ) {
                $groups[$groupId] = \common\helpers\Group::get_user_group_name($groupId);
            }
        }
        if ( isset($groups[$groupId]) ) {
            $this->data[$key] = $groups[$groupId];
        }
        return $this->data[$key];
    }

    public function getCountryName($field_data)
    {
        $key = $field_data['name'];
        $countryId = $this->data[$key];
        $this->data[$key] = '';
        if ( $countryId ) {
            $countryInfo = $this->EPtools->getCountryInfo($countryId);
            if ( is_array($countryInfo) && isset($countryInfo['countries_name']) ) {
                $this->data[$key] = $countryInfo['countries_name'];
            }
        }
        return $this->data[$key];
    }

    public function getDate($field_data)
    {
        $key = $field_data['name'];
        $dateValue = $this->data[$key];
        $this->data[$key] = '';
        if ( $dateValue>0 && substr($dateValue,0,10)!='0000-00-00' ) {
            $this->data[$key] = substr($dateValue,0,10);
        }
        return $this->data[$key];
    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);

        $main_source = $this->main_source;

        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['platform_id']) && $filter['platform_id']>0 ) {
                $filter_sql .= "AND c.platform_id = '".(int)$filter['platform_id']."' ";
            }
        }

        $main_sql =
            "SELECT c.*, ab.entry_country_id, ci.customers_info_date_account_created " .
            "FROM " . TABLE_CUSTOMERS . " c " .
            " LEFT JOIN ".TABLE_ADDRESS_BOOK." ab ON ab.address_book_id=c.customers_default_address_id ".
            " LEFT JOIN ".TABLE_CUSTOMERS_INFO." ci ON ci.customers_info_id=c.customers_id ".
            "WHERE 1 {$filter_sql} ".
            "ORDER BY c.customers_lastname";

        $this->export_query = tep_db_query($main_sql);
    }

    public function exportRow()
    {
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;

        $data_sources = $this->data_sources;

        $export_columns = $this->export_columns;
        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['categories_id']));
            }
            $this->data[$db_key] = isset($this->data[$db_key])?$this->data[$db_key]:'';
        }

        return $this->data;
    }


}