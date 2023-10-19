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

namespace common\components;

use Yii;

class ProductItem extends \ArrayObject
{

    private $product_prices = [];

    public function checkAttachedDetails($block){
        return $this->offsetExists($block);
    }
    
    public function attachDetails($array){
        if (!is_array($array)) return $this;

        //$type = key($array);
        //$data = current($array);
        
        //if ($this->checkAttachedDetails($type)) return $this;
        
        if (!is_array($array)) return $this;
        
        $product = array_merge($this->getArrayCopy(), $array);
        $this->exchangeArray($product);
        
        return $this;
    }
    
    public function removeDetails($key){
        
        $current = $this->getArrayCopy();
        if (isset($current[$key])){
            unset($current[$key]);
        }        
        
        $this->exchangeArray($current);
        
        return $this;
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($index)
    {
        if ( $index!='products_id' && !$this->offsetExists($index) && $this->getId() ) {
            /**
             * @var $productsSchema \yii\db\TableSchema
             */
            $productsSchema = \common\models\Products::getTableSchema();
            if ( $productsSchema->getColumn($index) ){
                $loadKeys = [$index];
                $offenUsedKeys = [
                    //'products_id_stock','products_id_price',
                    'disable_discount', 'products_status', 'stock_indication_id', 'stock_delivery_terms_id', 'is_virtual', 'request_quote', 'ask_sample',
                    'allow_backorder', 'request_quote_out_stock', 'cart_button',
                    'products_price', 'products_price_full', 'pack_unit', 'products_price_pack_unit', 'packaging', 'products_price_packaging',
                ];
                if ( in_array($index,$offenUsedKeys) ){
                    $loadKeys = $offenUsedKeys;
                }else{
                    //echo '<pre>'; var_dump($index); echo '</pre>';
                }

                $missing_data = \common\models\Products::find()
                    ->where(['products_id'=>$this->getId()])
                    ->select($loadKeys)
                    ->asArray()
                    ->one();

                foreach ($missing_data as $loadedKey=>$loadedValue){
                    $this->offsetSet($loadedKey, $loadedValue);
                }
            }
        }

        if ( in_array($index,['products_id_stock','products_id_price']) && !$this->offsetExists($index) ) {
            if ( defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' ) {
                $missing_data = \common\models\Products::find()
                    ->where(['products_id' => $this->getId()])
                    ->select(['products_id_stock', 'products_id_price'])
                    ->asArray()
                    ->one();
                foreach ($missing_data as $key => $val) {
                    $this->offsetSet($key, $val);
                }
            }else{
                $index = 'products_id';
            }
        }
        return parent::offsetExists($index) ? parent::offsetGet($index) : null;
    }

    public function getId()
    {
        return $this->offsetGet('products_id');
    }

    public function getPriceProductId()
    {
        return $this->offsetGet(\common\helpers\Product::priceProductIdColumn());
    }

    public function getProductsPrices($params)
    {
        $key = md5(\json_encode($params));
        if ( !array_key_exists($key,$this->product_prices) ){
            $this->product_prices[$key] = \common\models\ProductsPrices::find()
                ->select('products_group_price as products_price, products_group_price_pack_unit as products_price_pack_unit, products_group_price_packaging as products_price_packaging')
                ->where('products_id=:products_id and groups_id = :groups_id and currencies_id =:currencies_id', $params)
                ->asArray()->one();
        }
        return $this->product_prices[$key];
    }

    public function getProductWeight($uprid)
    {
        $product_weight = $this->offsetGet('products_weight');
        $without_inventory = $this->offsetGet('without_inventory');

        $inventory_attributes = [];
        $virtual_attributes = [];

        $inventoryUprid = \common\helpers\Inventory::normalizeInventoryId($uprid, $inventory_attributes, $virtual_attributes);
        if (\common\helpers\Extensions::isAllowed('Inventory') && !$without_inventory) {
            $attributes = $virtual_attributes;
            $inventory_weight = \common\models\Inventory::find()
                ->where(['products_id'=>strval($inventoryUprid), 'prid'=>(int)$inventoryUprid])
                ->select('inventory_weight')
                ->scalar();
            if ( is_numeric($inventory_weight) ) $product_weight += $inventory_weight;
        }else{
            //$attributes = $inventory_attributes+$virtual_attributes;
            $attributes = [];
            \common\helpers\Inventory::normalize_id($uprid, $attributes);
        }

        if ( count($attributes)>0 ) {
            $attributes_weight_fixed = 0;
            $attributes_weight_percents = [];
            $attribute_price_query = tep_db_query(
                "select products_attributes_weight, products_attributes_weight_prefix " .
                "from " . TABLE_PRODUCTS_ATTRIBUTES . " " .
                "where products_id = '" . (int)$uprid . "' ".
                "  and options_id IN ('" . implode("','",array_keys($attributes)). "') ".
                "  and options_values_id IN('" . implode("','",array_values($attributes)) . "')"
            );
            while($attribute_price = tep_db_fetch_array($attribute_price_query)) {
                if (tep_not_null($attribute_price['products_attributes_weight'])) {
                    if ( $attribute_price['products_attributes_weight_prefix']=='-%' ) {
                        $attributes_weight_percents[] = 1-$attribute_price['products_attributes_weight']/100;
                    }elseif($attribute_price['products_attributes_weight_prefix'] == '+%'){
                        $attributes_weight_percents[] = 1+$attribute_price['products_attributes_weight']/100;
                    }else{
                        $attributes_weight_fixed += (($attribute_price['products_attributes_weight_prefix']=='-')?-1:1)*$attribute_price['products_attributes_weight'];
                    }
                }
            }
            $product_weight += $attributes_weight_fixed;
            foreach( $attributes_weight_percents as $attributes_weight_percent ) {
                $product_weight *= $attributes_weight_percent;
            }
        }

        return $product_weight;
    }
}