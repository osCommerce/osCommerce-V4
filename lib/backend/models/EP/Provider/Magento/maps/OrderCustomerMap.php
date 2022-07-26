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

use backend\models\EP\Provider\Magento\maps\CustomerMap;

class OrderCustomerMap {
    
    private static $dependence = [ 
        'customer' => '\backend\models\EP\Provider\Magento\maps\CustomerMap',
    ];
        
    public static function getMap(){
        $map = [];
        
        if (is_array(self::$dependence)){
            foreach(self::$dependence as $key => $_dep){
                if (method_exists($_dep, 'getMap')){
                    $map[$key] = $_dep::getMap(CustomerMap::SCENARIO_ORDER);
                }
            }
        }
        return $map;
    }
        
    protected function customer_gender($data){
        return ($data == '1'? 'm':'f');
    }
    
    public static function apllyMapping($data){
        $response = [];
        $map = self::getMap();
        $is_default = false;
        $data['addresses'] = [];
        if (isset($data['shipping_address']) && is_array($data['shipping_address'])){
            $data['shipping_address']['is_shiping_address'] = 1;
            $data['shipping_address']['is_default_shipping'] = 1;
            $is_default = true;
            $data['addresses'][] = $data['shipping_address'];
            if (empty($data['customer_firstname']) && !empty($data['shipping_address']['firstname'])){
                $data['customer_firstname'] = $data['shipping_address']['firstname'];
            }
            if (empty($data['customer_lastname'])) {
                $ex= explode(" ", $data['customer_firstname']);
                if (count($ex)>1){
                    $data['customer_lastname'] = $ex[count($ex)-1];
                }                
            }
        }
        if (isset($data['billing_address']) && is_array($data['billing_address'])){
            $data['billing_address']['is_billing_address'] = 1;
            if (!$is_default){
                $data['billing_address']['is_default_shipping'] = 1;
            }
            $data['addresses'][] = $data['billing_address'];
        }        
        
       
        foreach($map as $tl_key => $mg_key){
            if (isset(self::$dependence[$tl_key])){
                $class = self::$dependence[$tl_key];
                if (method_exists($class, 'apllyMapping')){
                    $response[$tl_key] = $class::apllyMapping($data, ['scenario' => CustomerMap::SCENARIO_ORDER]);
                }                
            } else if(method_exists(self, $mg_key)){
                $response[$tl_key] = self::$mg_key($data[$mg_key]);
            }elseif (is_scalar($map[$tl_key]) && is_scalar($data[$mg_key])){
                $response[$tl_key] = $data[$mg_key];
            }
        }
        
        return $response['customer'];
    }
    
}