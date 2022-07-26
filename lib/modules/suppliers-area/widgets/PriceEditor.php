<?php

namespace suppliersarea\widgets;

use Yii;
use yii\helpers\Html;

class PriceEditor extends \yii\base\Widget {

    
    public $product;
    public $currencies;
    
    private $cMap;
    private $cMapTitle;
    
    public function init() {
        parent::init();
        
        $this->cMap = \yii\helpers\ArrayHelper::map($this->currencies->currencies, 'id', 'code');//        
        $this->cMapTitle = \yii\helpers\ArrayHelper::index($this->product->supplier->getAllowedCurrencies()->all(), 'currencies_id');
    }

    public function run() {
    
        $value = $this->currencies->format($this->product->suppliers_price, false, $this->cMap[$this->product->currencies_id]);
        $uprid = $this->product->uprid;
        
        return $this->render('price-editor',[
            'value' => $value,
            'b_uprid' => base64_encode($uprid),
            'uprid' => $uprid,
            'price' => $this->product->suppliers_price,
            'cLlist' => array_intersect_key($this->cMap, $this->cMapTitle),//$this->cMapTitle,
            'sCurrency' => $this->product->currencies_id,
            'baseUrl' => \suppliersarea\SupplierModule::getInstance()->baseUrl,
        ]);
        
    }
}
