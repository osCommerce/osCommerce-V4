<?php

namespace suppliersarea\models;

use Yii;

class SuppliersProductsSearch extends yii\base\Model {

    public $products_model;
    public $suppliers_model;
    public $suppliers_quantity;
    public $status;
    public $products_name;

    public function rules() {
        return [
            [['products_model', 'suppliers_model', 'suppliers_quantity', 'status', 'products_name'], 'safe']
        ];
    }
    
    public function getQuantityRange(){
        return ['<1', '>1', '>10', '>100'];
    }
    
    public function setSort(\yii\db\ActiveQuery $productsQuery){
        $_sort = Yii::$app->request->get('sort');
        $_dir = "asc";
        if (substr($_sort, 0, 1) == '-'){
            $_dir = 'desc';
            $_sort = substr($_sort, 1);
        }        
        switch ($_sort){
            case 'suppliers_model':
                $supplier_id = \suppliersarea\SupplierModule::getInstance()->user->getId();
                $productsQuery->joinWith(['suppliersProducts' => function($query) use ($_dir, $supplier_id) { 
                    $query->where(['suppliers_id' => $supplier_id]);
                    $query->orderBy("suppliers_model {$_dir}"); }
                    ]);
                break;
            case 'products_name':
                $productsQuery->orderBy("products_description.products_name {$_dir}");
                break;
        }
        
    }

    public function search($productsQuery) {
        
        if ($productsQuery) {
            
            if (!empty($this->products_model)) {
                
                $productsQuery->orWhere(['or',
                    ['like', 'products.products_model', $this->products_model],
                    ['like', 'products.products_ean', $this->products_model],
                    ['like', 'products.products_asin', $this->products_model],
                    ['like', 'products.products_upc', $this->products_model],
                    ['like', 'inventory.products_model', $this->products_model],
                    ['like', 'inventory.products_ean', $this->products_model],
                    ['like', 'inventory.products_asin', $this->products_model],
                    ['like', 'inventory.products_upc', $this->products_model]
                ])->joinWith([
                    'inventories'
                ]);
            }
            if (!empty($this->suppliers_model)) {
                $supplier_id = \suppliersarea\SupplierModule::getInstance()->user->getId();
                $productsQuery->joinWith([
                    'suppliersProducts' => function($query) use ($supplier_id) {
                        $query->where(['suppliers_id' => $supplier_id]);
                        $query->andWhere(['or',
                            ['like', 'suppliers_model', $this->suppliers_model],
                            ['like', 'suppliers_ean', $this->suppliers_model],
                            ['like', 'suppliers_asin', $this->suppliers_model],
                            ['like', 'suppliers_upc', $this->suppliers_model]
                        ]);
                    }
                ]);
                //echo '<pre>';print_r($productsQuery);die;
            }
            
            if (!empty($this->products_name)) {
                $productsQuery->joinWith([
                    'description' => function($query){
                        $query->where(['like', 'products_description.products_name', $this->products_name]);
                    }
                ]);
            }
            
            if (!is_null($this->suppliers_quantity)){
                $range = $this->getQuantityRange();
                if (isset($range[$this->suppliers_quantity])){
                    
                }
            }
        }
    }

}
