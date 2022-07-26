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

namespace common\api\models\AR\Warehouses;


use backend\models\EP\Tools;
use common\api\models\AR\EPMap;

class Address extends EPMap
{

    public $is_default;
    public $save_lookup = false;

    protected $hideFields = [
        'warehouse_id',
        //'address_book_id',
    ];

    protected $parentObject;

    public static function tableName()
    {
        return TABLE_WAREHOUSES_ADDRESS_BOOK;
    }

    public static function primaryKey()
    {
        return ['warehouses_address_book_id'];
    }

    /*public function customFields()
    {
        return ['is_default'];
    }*/
    
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
        $this->warehouse_id = $parentObject->warehouse_id;
        $this->parentObject = $parentObject;
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( !is_null($importedObject->warehouses_address_book_id) && !is_null($this->warehouses_address_book_id) && $importedObject->warehouses_address_book_id==$this->warehouses_address_book_id ){
            $this->pendingRemoval = false;
            return true;
        }
        $compareFields = [
            'is_default',
            'entry_company',
            'entry_company_vat',
            'entry_company_reg_number',
            'entry_street_address',
            'entry_suburb',
            'entry_postcode',
            'entry_city',
            'entry_state',
            'entry_country_id',
            'entry_zone_id',
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
        return $match;
    }


    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);
        if ( array_key_exists('entry_country_id', $data) ) {
            $tools = new Tools();
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
            $tools = new Tools();
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
    
}