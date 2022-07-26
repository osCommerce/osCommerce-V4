<?php

namespace suppliersarea\widgets;

use Yii;
use yii\helpers\Html;

class QuantityEditor extends \yii\base\Widget {

    
    public $product;
    
    public function init() {
        parent::init();        
    }

    public function run() {    
        
        $uprid = $this->product->uprid;
        
        return $this->render('quantity-editor',[
            'value' => $this->product->suppliers_quantity,
            'b_uprid' => base64_encode($uprid),
            'uprid' => $uprid,            
            'baseUrl' => \suppliersarea\SupplierModule::getInstance()->baseUrl,
        ]);
        
    }
}
