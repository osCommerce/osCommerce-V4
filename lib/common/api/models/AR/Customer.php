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

namespace common\api\models\AR;


use common\api\models\AR\Customer\Address;
use common\api\models\AR\Customer\Info;

class Customer extends EPMap
{

    protected $hideFields = [
        'customers_password',
        'last_xml_import',
        'last_xml_export',
        'affiliate_id',
        'customers_alt_email_address',
        'customers_alt_telephone',
        'customers_cell',
        'customers_owc_member',
        'customers_type_id',
        'customers_selected_template',
        'customers_fax',
        'dnu_customers_company_vat',
    ];

    protected $childCollections = [
        'addresses' => false,
        'info' => false,
    ];

    protected $indexedCollections = [
        'addresses' => 'common\api\models\AR\Customer\Address',
        'info' => 'common\api\models\AR\Customer\Info',
    ];

    public static function tableName()
    {
        return TABLE_CUSTOMERS;
    }

    public static function primaryKey()
    {
        return ['customers_id'];
    }

    public static function maxAddresses() {
      $def = 5;
      if (defined('MAX_ADDRESS_BOOK_ENTRIES') && intval(MAX_ADDRESS_BOOK_ENTRIES)>0) {
        $def = intval(MAX_ADDRESS_BOOK_ENTRIES);
      }
      return min(20, $def);
    }

    
    public function rules() {
        return array_merge(
            parent::rules(),
            [
                ['customers_dob', 'default', 'value' => '0000-00-00 00:00:00'],
                //['customers_company_vat', 'default', 'value' => '']
            ]
        );
    }

    public function initCollectionByLookupKey_Addresses($lookupKeys)
    {
        if ( !is_array($this->childCollections['addresses']) ) {
            $this->childCollections['addresses'] = [];
            if ($this->customers_id) {
                $this->childCollections['addresses'] =
                    Address::find()
                        ->addSelect(['*','IF(address_book_id='.intval($this->customers_default_address_id).',1,NULL) As is_default'])
                        ->where(['customers_id' => $this->customers_id])
                        ->orderBy([
                          new \yii\db\Expression('address_book_id='.intval($this->customers_default_address_id) . ' DESC') ,
                          'address_book_id' => SORT_ASC,
                        ])
//                        ->limit(self::maxAddresses()+1)
                        ->all();
            }
        }
        return $this->childCollections['addresses'];
    }


    public function initCollectionByLookupKey_Info($lookupKeys)
    {
        if ( !is_array($this->childCollections['info'])) {
            $this->childCollections['info'] = [];
            if ($this->customers_id) {
                $info = Info::findOne(['customers_info_id' => $this->customers_id]);
                if ($info) {
                    $this->childCollections['info'][] = $info;
                }
            }
        }
        return $this->childCollections['info'];
    }

    public function exportArray(array $fields = [])
    {
        $export = parent::exportArray($fields);

        if ( array_key_exists('customers_currency_id', $export) || in_array('customers_currency',$fields) ) {
            $export['customers_currency'] = \common\helpers\Currencies::getCurrencyCode($this->customers_currency_id);
        }

        return $export;
    }

    public function importArray($data)
    {
        if ( array_key_exists('customers_currency', $data) ) {
            $data['customers_currency_id'] = \common\helpers\Currencies::getCurrencyId($data['customers_currency']);
        }

        $importResult = parent::importArray($data);
        return $importResult;
    }


    public function beforeSave($insert)
    {
        if ( $insert && (!is_array($this->childCollections['info']) || count($this->childCollections['info'])==0) ) {
            $this->childCollections['info'] = [];
            $this->childCollections['info'][] = new Info();
        }
        /*
        if ( !$insert ) {
            $creditChanged = $this->getDirtyAttributes(['credit_amount']);
            if ( count($creditChanged)>0 ) {
                $old_credit_amount = $this->getOldAttribute('credit_amount');
                if ( number_format($old_credit_amount,4,'.','')!=number_format($old_credit_amount,4,'.','') ) {
                    tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY,[
| customers_id                | int(11)       | NO     |       |    <null> |                |
| credit_prefix               | varchar(1)    | NO     |       |    <null> |                |
| credit_amount               | decimal(11,2) | NO     |       |    <null> |                |
| currency                    | char(3)       | NO     |       |    <null> |                |
| currency_value              | decimal(14,6) | NO     |       |    <null> |                |
| customer_notified           | tinyint(1)    | NO     |       |    <null> |                |
| comments                    | mediumtext    | NO     |       |    <null> |                |
| date_added                  | datetime      | NO     |       |    <null> |                |
| admin_id
                    ]);
                }
            }
        }
        */
        return parent::beforeSave($insert);
    }


}