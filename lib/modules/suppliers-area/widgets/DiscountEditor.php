<?php

namespace suppliersarea\widgets;

use Yii;
use yii\helpers\Html;

class DiscountEditor extends \yii\base\Widget {

    
    public $product;
    
    public function init() {
        parent::init();        
    }

    public function run() {    
        
        $uprid = $this->product->uprid;
        
        return $this->render('discount-editor',[
            'value' => floatval($this->product->supplier_discount),
            'b_uprid' => base64_encode($uprid),
            'uprid' => $uprid,            
            'baseUrl' => \suppliersarea\SupplierModule::getInstance()->baseUrl,
        ]);
        
    }
}
