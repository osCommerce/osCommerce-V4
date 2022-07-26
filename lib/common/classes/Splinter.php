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

namespace common\classes;
use common\services\SplitterManager;

class Splinter extends Order
{
    public $modified;
    
    public function __construct($order_id = null) {
        $this->modified = false;
    }
    
    public $splitter;
    public function setSplitter(\common\services\SplitterManager $splitterManager){
        $this->splitter = $splitterManager;
    }
    
    public function getSplitter(){
        return $this->splitter;
    }

    public function create_product($order_id, $uprid, $qty, $price_ex, $price_in, $data = []){
        if (is_array($data) && count($data)){
            $product = $data;
        } else {
            $orders_product = $this->getProductsARModel()->select(['*', 'if(length(uprid),uprid, products_id) as products_id'])
                ->where(['orders_id' => $order_id, 'template_uprid' => $uprid])->asArray()->orderBy('sort_order, orders_products_id')->one();
            if (!$orders_product){
                $name = \common\helpers\Inventory::get_inventory_name_by_uprid(\common\helpers\Inventory::normalize_id($uprid));
                if (empty($name)){
                    $name = \common\helpers\Product::get_products_name(intval($uprid));
                }
                $orders_product = [
                    'products_name'  => $name,
                    'products_model' => \common\helpers\Product::get_products_info($uprid, 'products_model'),
                    'sort_order' => 0
                ];
            }
            $product = ['qty' => ($qty? $qty: 1),
                'id' => \common\helpers\Inventory::normalize_id($uprid),
                'name' => $orders_product['products_name'],
                'model' => $orders_product['products_model'],
                'tax' => ($price_in / ($price_ex?$price_ex:1) - 1) * 100,
                'final_price' => $price_ex,
                'template_uprid' => $uprid,
                'sort_order' => $orders_product['sort_order'],
            ];
        }
        
        $this->products[] = $product;
    }
    
    public function query($order_id)
    {

    }
    
    public static function getARModel($new = false){
        //return \common\models\Orders::find();
    }

    public function getStatusHistoryARModel(){
        //return \common\models\OrdersStatusHistory::find();
    }
    
    public function getHistoryARModel(){
        //return \common\models\OrdersHistory::find();
    }

    public function removeOrder($restock = false){
        return false;
    }
    
    public function getParent(){
        return false;
    }
    
    public function hasTransactions(){
        return false;
    }
    
    public $status;
    
    public function isInvoice(){
        return $this->status == SplitterManager::STATUS_PAYED && $this->order_id;
    }
    
    public function isCreditNote(){
        return $this->status == SplitterManager::STATUS_RETURNED && $this->order_id;
    }
    
    
    protected $splinters = [];
    /*@params $splinters - rows for creating splinter instance */
    public function setSplinters(array $splinters){
        $this->splinters = $splinters;
    }
    
    public function getSplinters(){
        return $this->splinters;
    }
    
    public $mixed = [];
    public function createMixedType($owner, $amount, $data){
        $this->mixed[] = [
            'qty' => 1,
            'value_exc_vat' => $amount,
            'value_inc_tax' => $amount,
            'owner' => $owner,
            'data' => $data
        ];
    }
    
    public function getReturningCreditNoteAmount(){
        $amount = 0;
        if ($this->isCreditNote()){
            
        }
        return $amount;
    }
}
