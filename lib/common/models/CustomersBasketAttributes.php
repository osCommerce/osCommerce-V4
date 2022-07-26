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

use yii\db\ActiveRecord;

class CustomersBasketAttributes extends ActiveRecord {

    public static function tableName() {
        return 'customers_basket_attributes';
    }

    public static function saveProductAttributes($product, $customer_id, $cart){
        if ($product && $customer_id ){
            $products_id = key($product);
            $val = current($product);
            if (isset($val['attributes'])){
                reset($val['attributes']);
                foreach ($val['attributes'] as $option => $value) {
                    
                    $productAttribute = self::find()->where('customers_id = :cid and products_id =:prid and products_options_id = :oid and products_options_value_id = :vid and basket_id =:bid',[
                        ':cid' => (int) $customer_id,
                        ':bid' => (int) $cart->basketID,
                        ':prid' => $products_id,
                        ':oid' => $option,
                        ':vid' => (int)$value
                    ])->one();
                    if (!$productAttribute){
                        $productAttribute = new self();
                    }                    
                    
                    $productAttribute->setAttributes([
                        'customers_id' => (int) $customer_id,
                        'products_id' => $products_id,
                        'basket_id' => (int)$cart->basketID,
                        'products_options_id' => $option,
                        'products_options_value_id' => (int)$value
                    ], false);
                    if (!$productAttribute->hasErrors()){
                        $productAttribute->save(false);
                    }
                }
                return true;
            }
        }
        return false;                    
    }
    
    public static function hasGiftWrapAttribute($products_id, $customer_id, $basket_id){
        return self::find()->where('customers_id = :cid and products_id =:prid and products_options_id = "gift_wrap" and basket_id=:bid', 
                [':cid' => (int)$customer_id, ':prid' => tep_db_input($products_id), ':bid' =>$basket_id])->count();
    }

}
