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

    public $credit_amount_delta;
    public $customers_bonus_points_delta;

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
        'customers_credit_avail',
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

    public function customFields()
    {
        $fields = parent::customFields();
        $fields[] = 'credit_amount_delta';
        $fields[] = 'customers_bonus_points_delta';
        return $fields;
    }

    public function getPossibleKeys()
    {
        $keys  = parent::getPossibleKeys();
        $keys = array_values($keys);
        $credit_idx = array_search('credit_amount', $keys);
        $credit_delta_idx = array_search('credit_amount_delta', $keys);
        if ( $credit_idx!==false && $credit_delta_idx!==false ) {
            unset($keys[$credit_delta_idx]);
            array_splice($keys, $credit_idx+1, 0, ['credit_amount_delta']);
        }
        $bonus_points_idx = array_search('customers_bonus_points', $keys);
        $bonus_points_delta_idx = array_search('customers_bonus_points_delta', $keys);
        if ( $bonus_points_idx!==false && $bonus_points_delta_idx!==false ) {
            unset($keys[$bonus_points_delta_idx]);
            array_splice($keys, $bonus_points_idx+1, 0, ['customers_bonus_points_delta']);
        }

        return $keys;
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

        if (!defined('ALLOW_CUSTOMER_CREDIT_AMOUNT') || ALLOW_CUSTOMER_CREDIT_AMOUNT == 'false'){
            unset($export['credit_amount']);
        }else{
            if ( (count($fields)==0 || array_key_exists('credit_amount_delta', $fields))) {
                $export['credit_amount_delta'] = '';
            }
        }
        if (!\common\helpers\Acl::checkExtensionAllowed('BonusActions')){
            unset($export['customers_bonus_points']);
        }else{
            if ( (count($fields)==0 || array_key_exists('customers_bonus_points_delta', $fields))) {
                $export['customers_bonus_points_delta'] = '';
            }
        }
        return $export;
    }

    public function importArray($data)
    {
        if ( array_key_exists('customers_currency', $data) ) {
            $data['customers_currency_id'] = \common\helpers\Currencies::getCurrencyId($data['customers_currency']);
        }
        if (!defined('ALLOW_CUSTOMER_CREDIT_AMOUNT') || ALLOW_CUSTOMER_CREDIT_AMOUNT == 'false'){
            unset($data['credit_amount']);
            unset($data['credit_amount_delta']);
        }else{
            if ( !empty($this->modelFlags['credit_amount_delta']) ) {
                unset($data['credit_amount']);
                if ( array_key_exists('credit_amount_delta', $data) && !empty($data['credit_amount_delta'])) {
                    $data['credit_amount'] = (float)$this->credit_amount + (float)$data['credit_amount_delta'];
                }
            }
        }
        if (!\common\helpers\Acl::checkExtensionAllowed('BonusActions')){
            unset($data['customers_bonus_points']);
            unset($data['customers_bonus_points_delta']);
        }else{
            if ( !empty($this->modelFlags['customers_bonus_points_delta']) ) {
                unset($data['customers_bonus_points']);
                if (array_key_exists('customers_bonus_points_delta', $data) && !empty($data['customers_bonus_points_delta'])) {
                    $data['customers_bonus_points'] = (float)$this->customers_bonus_points + (float)$data['customers_bonus_points_delta'];
                }
            }
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

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ( array_key_exists('credit_amount', $changedAttributes) || array_key_exists('customers_bonus_points', $changedAttributes) ) {
            static $customer;
            if (!is_object($customer)) {
                $customer = new \common\components\Customer();
            }
            if (array_key_exists('credit_amount', $changedAttributes)) {
                $diff = $this->getOldAttribute('credit_amount') - $changedAttributes['credit_amount'];
                if ((float)$diff != 0) {
                    $customer->saveCreditHistory($this->customers_id, abs($diff), $diff > 0 ? '+' : '-', DEFAULT_CURRENCY, 1, 'Import update', 0, 0);
                }
            }
            if (array_key_exists('customers_bonus_points', $changedAttributes)) {
                $diff = $this->getOldAttribute('customers_bonus_points') - $changedAttributes['customers_bonus_points'];
                if ((float)$diff != 0) {
                    $customer->saveCreditHistory($this->customers_id, abs($diff), $diff > 0 ? '+' : '-', '', 1, 'Import update', 1, 0);
                }
            }
        }
    }


}