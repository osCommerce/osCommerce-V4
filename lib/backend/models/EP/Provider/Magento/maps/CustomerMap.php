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

namespace backend\models\EP\Provider\Magento\maps;


class CustomerMap {
    
    CONST SCENARIO_CUSTOMER = 1;
    CONST SCENARIO_ORDER = 2;
    private static $dependence = [ 
        'addresses' => '\backend\models\EP\Provider\Magento\maps\AddressMap',
        'info' => '\backend\models\EP\Provider\Magento\maps\InfoMap',
    ];
    
        
    public static function getMap($scenario = 1){
        if ($scenario == self::SCENARIO_CUSTOMER){
            $map = [
                'customers_email_address' => 'email',
                'customers_firstname' => 'firstname',
                'customers_lastname' => 'lastname',
                'customers_dob' => 'dob',
                //'customers_company_vat' => 'taxvat',
                'gender' => 'gender',
                'groups_id' => 'group_id',
                'opc_temp_account' => 'customer_is_guest'
            ];
        } else if ($scenario == self::SCENARIO_ORDER){
             $map = [
                'customers_email_address' => 'customer_email',
                'customers_firstname' => 'customer_firstname',
                'customers_lastname' => 'customer_lastname',
                'customers_dob' => 'customer_dob',
                'customers_company_vat' => 'customer_taxvat',
                'gender' => 'customer_gender',
                'groups_id' => 'customer_group_id',
                'opc_temp_account' => 'customer_is_guest'
            ];
        } 
        
        if (is_array(self::$dependence)){
            foreach(self::$dependence as $key => $_dep){
                if (method_exists($_dep, 'getMap')){
                    $map[$key] = $_dep::getMap();
                }
            }
        }
        return $map;
    }
    
    protected function gender($data){
        return ($data == '1'? 'm':'f');
    }
    
    public static function apllyMapping($data, $params = []){
        $response = [];
        if (isset($params['scenario'])){
            $map = self::getMap($params['scenario']);
        } else {
            $map = self::getMap();
        }
        
        foreach($map as $tl_key => $mg_key){
            if (isset(self::$dependence[$tl_key])){
                $class = self::$dependence[$tl_key];
                if (method_exists($class, 'apllyMapping')){
                    $response[$tl_key] = $class::apllyMapping($data, $params);
                }                
            } else if(method_exists('CustomerMap', $mg_key)){
                $response[$tl_key] = self::$mg_key($data[$mg_key]);
            }elseif (is_scalar($map[$tl_key]) && is_scalar($data[$mg_key])){
                $response[$tl_key] = $data[$mg_key];
            }
        }        
        return $response;
    }
    
}