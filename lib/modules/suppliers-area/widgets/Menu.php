<?php

namespace suppliersarea\widgets;

use Yii;

class Menu extends \yii\base\Widget{
    
    public function init() {
        parent::init();
    }
    
    public function run() {        
        
        if (!Yii::$app->user->isGuest){
            $_baseUrl = \suppliersarea\SupplierModule::getInstance()->baseUrl;            
            $items = [
                ['label' => 'Home', 'url' => Yii::$app->urlManager->createUrl([$_baseUrl .'/index/index'])],
                ['label' => 'Products', 'url' => Yii::$app->urlManager->createUrl([$_baseUrl .'/products/index']) ],
                ['label' => 'Settings', 'url' => Yii::$app->urlManager->createUrl([$_baseUrl .'/index/settings']) ],
                ['label' => 'Logoff', 'url' => Yii::$app->urlManager->createUrl([$_baseUrl . '/index/logoff']) ],
            ];
        } else {
            $items = [
                ['label' => html_entity_decode('&nbsp;')]
            ];
        }
        
        return $this->render('menu', [
            'items' => $items,
        ]);
        
    }
    
}