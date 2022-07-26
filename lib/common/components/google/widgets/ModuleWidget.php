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

namespace common\components\google\widgets;

use Yii;

class ModuleWidget extends \yii\base\Widget
{
    public $module;    
    
    public function init(){
        parent::init();
    }
    
    public function run(){
        $elements = $this->module->config[$this->module->code];
        $controllers = [];
        $only_selected = (isset($elements['pages_only']) && $elements['pages_only'] ? true : false);
        $controllers = $only_selected ? [] : ['all' => 'All'];
        $_excluded = ['Callback', 'EmailTemplate', 'GetWidget', 'ListDemo', 'Sitemap', 'Xmlsitemap', 'ApiPlatform', 'Api', 'AbstractCheckout', 'Cron', 'Image'];
        $iterator = new \DirectoryIterator(\Yii::$aliases['@frontend'] . '/controllers/');
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $controller = substr($file->getBasename(), 0, strpos($file->getBasename(), 'Controller'));
                if (!in_array($controller, $_excluded) && tep_not_null($controller)) {
                    if ($only_selected && !in_array(strtolower($controller), $elements['pages']))
                        continue;
                    $controllers[strtolower($controller)] = $controller;
                }
            }
        }
        return $this->render('module-config', [
            'elements' => $elements,
            'example' => $elements['example'] ? $this->module->renderExample() : '',
            'controllers' => $controllers,
        ]);
    }
}
