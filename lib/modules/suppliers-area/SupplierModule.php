<?php

namespace suppliersarea;
use Yii;

class SupplierModule extends \yii\base\Module
{
    
    public $controllerNamespace = 'suppliersarea\controllers';
    public $defaultRoute = 'index/index';
    public $user;
    
    public $baseUrl = '/suppliers-area';
        
    public function init()
    {        
        
        Yii::configure($this->module->user, $this->components['user']['config']);
    
        if (!($this->module->user->getIdentity() instanceof components\SupplierIdentity)) {            
            $sid = Yii::$app->session->get($this->module->user->idParam);
            if ($sid){
                $identity = components\SupplierIdentity::findIdentity($sid);
                $this->module->user->setIdentity($identity);
            } else {
                $this->module->user->setIdentity(null);
            }
            
            $this->module->user->init();
            $this->user = $this->module->user;
        }        

        $this->layout = 'main.tpl';        
        
        parent::init();       
    }
        
}