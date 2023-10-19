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

namespace common\api\models\AR\Customer;


use backend\models\EP\Tools;
use common\api\models\AR\Customer;
use common\api\models\AR\EPMap;

class Address extends EPMap
{

    public $is_default;
    public $save_lookup = false;
    public $is_shiping_address = false;
    public $is_billing_address = false;

    protected $hideFields = [
        'customers_id',
        'entry_company_vat_date',
        //'address_book_id',
    ];

    protected $parentObject;

    public static function tableName()
    {
        return TABLE_ADDRESS_BOOK;
    }

    public static function primaryKey()
    {
        return ['address_book_id'];
    }

    public function customFields()
    {
        return ['is_default'];
    }
    
    public function rules() {
        return array_merge(
            parent::rules(),
            [
                [['entry_firstname', 'entry_lastname', 'entry_street_address', 'entry_postcode', 'entry_city'], 'default', 'value' => ''],
            ]
        );
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->customers_id = $parentObject->customers_id;
        $this->parentObject = $parentObject;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( !is_null($importedObject->address_book_id) && !is_null($this->address_book_id) && $importedObject->address_book_id==$this->address_book_id ){
            $this->pendingRemoval = false;
            return true;
        }
        $compareFields = [
            'entry_company',
            'entry_company_vat',
            'entry_customs_number',
//VL???            'entry_gender',
            'entry_firstname',
            'entry_lastname',
            'entry_street_address',
            'entry_suburb',
            'entry_postcode',
            'entry_city',
            'entry_state',
            'entry_country_id',
            'entry_zone_id',
            'entry_telephone',
        ];
        $match = true;
        foreach ($compareFields as $compareField){
            if ( !$this->hasAttribute($compareField) ) continue;
            if ( in_array($compareField,['entry_country_id','entry_zone_id'] ) ) {
                // integer fields
                if ( intval($importedObject->$compareField)!==intval($this->$compareField) ){
                    $match = false;
                    break;
                }
            }else
            if ( strval($importedObject->$compareField)!==strval($this->$compareField) ){
                $match = false;
                break;
            }
        }
        if ( $match ) {
            $this->pendingRemoval = false;
        }
        else {
//          echo "#### not match by \$compareField $compareField <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($importedObject, 1) . ' this:' . print_r($this, 1) ."</PRE>";
        }
        return $match;
    }

    public function afterFind()
    {
        parent::afterFind();
        if ( !is_null($this->is_default) ) {
            $this->is_default = !!$this->is_default;
        }
    }


    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);
        if ( array_key_exists('entry_country_id', $data) ) {
            $tools = Tools::getInstance();
            $countryInfo = $tools->getCountryInfo($data['entry_country_id']);
            $data['entry_country_iso2'] = $countryInfo['countries_iso_code_2'];
        }
        if ( array_key_exists('entry_state', $data) && is_numeric($this->entry_zone_id)) {
            $data['entry_state'] = \common\helpers\Zones::get_zone_name($data['entry_country_id'],$this->entry_zone_id,$this->entry_state);
        }
        return $data;
    }

    public function importArray($data)
    {
        if ( isset($data['entry_country_iso2']) ) {
            $tools = Tools::getInstance();
            $data['entry_country_id'] = $tools->getCountryId($data['entry_country_iso2']);
        }
        if ( isset($data['entry_state']) ) {
            $data['entry_zone_id'] = \common\helpers\Zones::get_zone_id($data['entry_country_id'],$data['entry_state']);
            if ( $data['entry_zone_id'] ) {
                $data['entry_state'] = '';
            }
        }

        $importResult = parent::importArray($data);
        if ( array_key_exists('is_default', $data) ) {
            $this->is_default = !!$data['is_default'];
        }
        
        if (array_key_exists('save_lookup', $data)){
            $this->save_lookup = $data['save_lookup'];
        }
        return $importResult;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ( $this->is_default && $this->parentObject->customers_default_address_id !== $this->address_book_id ) {
            if ( !empty($this->entry_firstname) && !empty($this->entry_lastname) ) {
                $this->getDb()->createCommand()
                    ->update(
                        Customer::tableName(),
                        [
                            'customers_default_address_id' => $this->address_book_id,
                            'customers_gender' => $this->entry_gender,
                            'customers_firstname' => $this->entry_firstname,
                            'customers_lastname' => $this->entry_lastname,
                        ],
                        [
                            'customers_id' => (int)$this->customers_id,
                        ]
                    )->execute();
            }
            if(is_object($this->parentObject)){
                $this->parentObject->refresh();
            }                
            /*
            $this->parentObject->customers_default_address_id = $this->address_book_id;
            $this->parentObject->customers_gender = $this->entry_gender;
            $this->parentObject->customers_firstname = $this->entry_firstname;
            $this->parentObject->customers_lastname = $this->entry_lastname;
            $this->parentObject->save();*/
        }
    }


}