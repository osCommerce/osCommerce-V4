<?php

namespace suppliersarea\controllers;

use Yii;
use suppliersarea\SupplierModule;

class Sceleton extends \yii\web\Controller
{
    public $module;
    public $view = null;
    public $service;

    public function __construct($id, $module, \common\services\SupplierService $service, $config = []){

        \common\helpers\Translation::init('main');
        \common\helpers\Translation::init('admin/main');
        \common\helpers\Translation::init('admin/suppliers');
        \common\helpers\Translation::init('admin/platforms');
        \common\helpers\Translation::init('admin/categories');
        
        $this->service = $service;        
        
        $this->module = SupplierModule::getInstance();
        
        $this->service->loadSupplier($this->module->user->getId());
        
        $this->view = new \stdClass();
        
        $theme = \frontend\design\Info::getThemeName(PLATFORM_ID);
        $themes_path = \frontend\design\Info::getThemesPath(array($theme));
        $themes_path[] = 'basic';
        $GLOBALS['themeMap'] = $themes_path;

        $pathMapView = array();
        foreach ($themes_path as $item){
          $pathMapView[] = '@app/themes/' . $item;
        }
        Yii::$app->view->theme = new \yii\base\Theme([
          'pathMap' => [
            '@app/views' => $pathMapView
          ],
          'baseUrl' => '@web/themes/' . $theme,
        ]);

        Yii::setAlias('@webThemes', '@web/themes');
        Yii::setAlias('@theme', '@app/themes/' . $theme);
        Yii::setAlias('@webTheme', '@web/themes/' . $theme);
        Yii::setAlias('@themeImages', '@web/themes/' . $theme . '/img/');
        define('DIR_WS_THEME', Yii::getAlias('@webTheme'));
        define('THEME_NAME', $theme);
        define('DIR_WS_THEME_IMAGES', Yii::getAlias('@themeImages'));
        define('SHOW_OUT_OF_STOCK', false);
        
        $this->view->no_header_footer = false;
        
        $this->view->supplier_name = !$this->module->user->isGuest? $this->service->get('supplier')->suppliers_name:'';
        
        return parent::__construct($id, $module);
    }    
    
}