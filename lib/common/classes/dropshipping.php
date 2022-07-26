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

class dropshipping {

    var $modules;
    private $include_modules = [];

// class constructor
    function __construct($module = '') {
        global $language, $PHP_SELF, $cart;

        if (defined('MODULE_DROPSHIPPING_INSTALLED') && tep_not_null(MODULE_DROPSHIPPING_INSTALLED)) {
            $this->modules = explode(';', MODULE_DROPSHIPPING_INSTALLED);

            $include_modules = array();

            if ((tep_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.') + 1)), $this->modules))) {
                $include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.') + 1)));
            } else {
                if (is_array($this->modules))
                    foreach ($this->modules as $value) {
                        $class = substr($value, 0, strrpos($value, '.'));
                        $include_modules[] = array('class' => $class, 'file' => $value);
                    }
            }

            \common\helpers\Translation::init('dropshipping');
            //$this->include_modules = $include_modules;
            for ($i = 0, $n = sizeof($include_modules); $i < $n; $i++) {

                if (\frontend\design\Info::isTotallyAdmin()) {
                    include_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'dropshipping/' . $include_modules[$i]['file']);
                } else {
                    include_once(DIR_WS_MODULES . 'dropshipping/' . $include_modules[$i]['file']);
                }

                $this->include_modules[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
            }
        }
    }

    public function getIncludedModules() {
        return $this->include_modules;
    }
    
    public function getIncludedModule($module) {
        if (isset($this->include_modules[$module])){
            return $this->include_modules[$module];
        }
        return false;
    }
    
    public function process($module, $params = []){
        if (($object = $this->getIncludedModule($module)) !== false){
            if (is_object($object) && method_exists($object, 'process') && $object->enabled){
                $response = $object->process($params);
                if ($response){
                    \Yii::$app->session->setFlash('success', DROPSHIPPING_PROCESS_SUCCESSED);
                } else {
                    \Yii::$app->session->setFlash('danger', DROPSHIPPING_PROCESS_DECLINED);
                }
            }
        }
        return;
    }

    public function renderOrderProcessButton (){
        $html = [];
        foreach($this->getIncludedModules() as $object){
            if (is_object($object) && $object->enabled) {
                if (method_exists($object, 'renderOrderProcessButton')){
                    $html[] = $object->renderOrderProcessButton();
                } else {
                    $params = \Yii::$app->request->getQueryParams();
                    $params['action'] = 'd-execute';
                    $params['dropshipping'] = $object->code;
                    $html[] = \yii\helpers\Html::a($object->title, tep_href_link('orders/process-order', http_build_query($params)), ['class' => 'btn btn-primary btn-right btn-' . $object->code]);
                }
            }
        }
        return implode("", $html);
    }    

}

?>
