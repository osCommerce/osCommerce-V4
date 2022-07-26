<?php

namespace suppliersarea\widgets;

use Yii;
use yii\helpers\Html;
use yii\widgets\Pjax;

class ProductsList extends \yii\base\Widget {

    public $provider;
    public $columns;
    public $productSearch;
    public $service;
    public $cMap;
    private $_currencies;
    private $_baseUrl;
    private $tools;

    public function init() {
        parent::init();
        $this->_baseUrl = (\suppliersarea\SupplierModule::getInstance())->baseUrl;
        $this->_currencies = $this->service->get('currencies');
        $this->cMap = \yii\helpers\ArrayHelper::map($this->_currencies->currencies, 'id', 'code');        
        $this->tools = new \backend\models\EP\Tools;
    }

    public function run() {
        //ob_start();
        
        Pjax::begin(['enablePushState' => true]);
        
        echo Html::beginForm('suppliers-area/products/index', 'post', ['id' => 'form-process']);
                
        echo \yii\grid\GridView::widget([
                'dataProvider' => $this->provider,
                'filterModel' => $this->productSearch,
                'columns' => $this->columns,
                'afterRow' => function($model, $key, $index, $object) {
                    if (is_array($model->inventories) && count($model->inventories)) {
                        $rows = '';
                        $data = $this->drawInventoryRow($model->inventories);
                        foreach ($data as $row) {
                            $rows .= $row;
                        }
                        return $rows;
                    }
                }
        ]);
        
        echo Html::endForm();
        
        Pjax::end();       
    }

    public function drawInventoryRow($Inventories) {        
        
        foreach ($Inventories as $inventory) {
            
            $pos = ['', $this->getVariation($inventory), $this->getModelData($inventory)];
            if ($inventory->suppliersProducts[0]) {
                $pos[] = $this->getModelData($inventory->suppliersProducts[0]);
                
                $pos[] = PriceEditor::widget([
                    'product' => $inventory->suppliersProducts[0],                    
                    'currencies' => $this->_currencies,                    
                ]);                        
                
                $pos[] = DiscountEditor::widget([
                            'product' => $inventory->suppliersProducts[0],
                        ]); 
                $pos[] = QuantityEditor::widget([
                            'product' => $inventory->suppliersProducts[0],
                        ]); 
                        //$inventory->suppliersProducts[0]->suppliers_quantity;
                $pos[] = Html::checkbox('status[]', $inventory->suppliersProducts[0]->status, ['value' => 1,
                            'class' => 'check_on_off',
                            'data-sid' => $inventory->suppliersProducts[0]->suppliers_id,
                            'data-uprid' => $inventory->products_id]);
                $pos[] = ActionButton::widget(['template' => '{update}', 'url' => Yii::$app->urlManager->createUrl([$this->_baseUrl. '/products/update', 'uprid' => $inventory->products_id ])]);
            } else {
                $pos = array_merge($pos, array_fill(0, 5, '&nbsp;'));
                $pos[] = ActionButton::widget(['template' => '{propose}', 'url' => Yii::$app->urlManager->createUrl([$this->_baseUrl. '/products/propose', 'uprid' => $inventory->products_id ])]);
            }

            array_walk($pos, function(&$item) {
                $item = Html::tag("td", $item);
            });

            yield "<tr>" . implode("", $pos) . "</tr>";
        }
    }

    public function getModelData($object) {
        $iterator = $object->getIterator();
        foreach ($iterator as $key => $value) {
            if (preg_match("/model|ean|asin|upc/", $key)) {
                return $value;
            }
        }
        return "(not set)";
    }
    
    public function getVariation($inventory){
        $languages_id = \Yii::$app->settings->get('languages_id');
        $str = '';
        if ($inventory && $inventory->products_id){
            $vids = [];
            
            \common\helpers\Inventory::normalize_id($inventory->products_id, $vids);
            if (is_array($vids) && count($vids)){
                foreach($vids as $oid => $vid){                    
                    $str .= $this->tools->get_option_name($oid, $languages_id) .": ". $this->tools->get_option_value_name($vid, $languages_id).", ";
                }
            }
            return (strlen($str)? substr($str, 0, -2): $str);
        }
    }

}
