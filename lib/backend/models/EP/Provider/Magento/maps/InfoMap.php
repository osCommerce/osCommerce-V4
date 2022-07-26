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


class InfoMap {
    
    private static $dependence = [ 
    ];
    
    public static function getMap(){
        return [
                'customers_info_date_account_created' => 'created_at',
                'customers_info_date_account_last_modified' => 'updated_at',
               ];
    }
    
    public static function apllyMapping($data, $params = []){
        $response = [];
        $map = self::getMap();
        foreach($map as $tl_key => $mg_key){
            if (is_scalar($map[$tl_key]) && is_scalar($data[$mg_key])){
                $response[$tl_key] = date("Y-m-d H:i:s", strtotime($data[$mg_key]));
            }
        }
        return ['*' => $response];
    }
    
}