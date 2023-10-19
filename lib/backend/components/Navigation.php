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

namespace backend\components;

use yii\base\Widget;

class Navigation extends Widget {

    public $box_files_list = array();
    public $selectedMenu = array();
    public $noHtml = false;

    public function buildTree($parent_id, $queryResponse, $rule = []) {
        $tree = [];
        if ($parent_id == 0) {
            if (\common\helpers\Acl::rule(['TEXT_DASHBOARD'])) {
                $tree[] = [
                    'box_type' => 0,
                    'path' => 'index',
                    'title' => TEXT_DASHBOARD,
                    'filename' => 'dashboard',
                    'acl' => '',
                ];
            }
            if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
                $tree[] = [
                    'box_type' => 1,
                    'path' => 'departments',
                    'title' => BOX_HEADING_DEPARTMENTS,
                    'acl' => 'BOX_HEADING_DEPARTMENTS',
                    'filename' => 'cubes',
                    'child' => [
                        [
                            'box_type' => 0,
                            'path' => 'departments',
                            'title' => BOX_HEADING_DEPARTMENTS,
                            'acl' => 'BOX_HEADING_DEPARTMENTS',
                            'filename' => ''
                        ],
                        [
                            'box_type' => 0,
                            'path' => 'departments-adminmembers',
                            'title' => BOX_DEPARTMENTS_MEMBERS,
                            'acl' => 'BOX_DEPARTMENTS_MEMBERS',
                            'filename' => ''
                        ],
                        [
                            'box_type' => 0,
                            'path' => 'departments-adminfiles',
                            'title' => BOX_DEPARTMENTS_BOXES,
                            'acl' => 'BOX_DEPARTMENTS_BOXES',
                            'filename' => ''
                        ],
                    ],
                ];
            }
        }
        foreach ($queryResponse as $response) {
            if ($response['parent_id'] == $parent_id) {
                $ruleTmp = $rule;
                $ruleTmp[] = $response['title'];
                if (\common\helpers\Acl::rule($ruleTmp)) {// enabled
                    
                    if ($response['box_type'] == 1) {
                        $response['child'] = $this->buildTree($response['box_id'], $queryResponse, $ruleTmp);
                        if ($response['title'] == 'BOX_HEADING_CONFIGURATION' && defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
                            $response['child'][] = [
                                'box_type' => 0,
                                'path' => 'configuration/index?groupid=BOX_CONFIGURATION_PANEL',
                                'title' => 'Control panel',
                                'filename' => ''
                            ];
                        }
                    }
                    if (defined($response['title'])) {
                        eval('$currentName =  ' . $response['title'] . ';');
                    } else {
                        $currentName = $response['title'];
                    }
                    $response['acl'] = $response['title'];
                    $response['title'] = $currentName;
                    
                    $response['dis_module'] = false;
                    $response['disabled'] = false;
                    if (!empty($response['acl_check'])) {
                        list($moduleName, $actionName) = explode(',', $response['acl_check']);
                        if (false === \common\helpers\Acl::checkExtensionAllowed($moduleName, $actionName)) {
                            $response['disabled'] = true;
                            $response['dis_module'] = true;
                        } else if (false === \common\helpers\Acl::checkExtensionAllowed($moduleName, $actionName)) {
                            $response['dis_module'] = true;
                        }
                    }
                    
                    if (!empty($response['config_check'])) {
                        list($configKey, $configValue) = explode(',', $response['config_check']);
                        if (!defined($configKey) || constant($configKey) != $configValue) {
                            $response['dis_module'] = true;
                        }
                    }
                    // hide if no child
                    if ($response['box_type'] == 1) {
                        $response['dis_module'] = true;
                        if (is_array($response['child'] ?? null)) {
                            foreach( $response['child'] as $child) {
                                if ($child['dis_module'] === false) {
                                    $response['dis_module'] = false;
                                    break;
                                }
                            }
                        }
                    }
                    if ($response['dis_module'] === false) {// hide disabled menu items
                        $tree[] = $response;
                    }
                    
                }
                
            }
        }
        return $tree;
    }
    
    private function parse_menu() {
        $totalRecords = \common\models\AdminBoxes::find()->count();
        if ($totalRecords > 0) {
            return false;
        }
        
        $path = \Yii::getAlias('@webroot');
        $filename = $path . DIRECTORY_SEPARATOR . 'includes' .DIRECTORY_SEPARATOR . 'default_menu.xml';
        
        $xmlfile = file_get_contents($filename);
        $ob= simplexml_load_string($xmlfile);
        if (isset($ob)) {
            $exItems = \common\helpers\MenuHelper::getExtensionsTreeItems();
            $obPrepared = \common\helpers\MenuHelper::prepareAdminTree($ob, $exItems);
            tep_db_query("TRUNCATE TABLE admin_boxes;");
            \common\helpers\MenuHelper::importAdminTree($obPrepared);
        }
        return true;
    }

    public function run() {

        $this->parse_menu();

        if (isset(\Yii::$app->controller->acl)) {
            $this->selectedMenu = \Yii::$app->controller->acl;
        } else {
            $this->selectedMenu = array("index");
        }
        
        $queryResponse = \common\models\AdminBoxes::find()
                ->orderBy(['sort_order' => SORT_ASC])
                ->asArray()
                ->all(); 
        
        $currentMenu = $this->buildTree(0, $queryResponse, []);

        if ($this->noHtml){
            return json_encode([
                'menu' => $currentMenu,
                'selectedMenu' => $this->selectedMenu
            ]);
        }
        
        $autoHideMenu = false;
        if (count($currentMenu) < 2) {
            $autoHideMenu = true;
        }
        
        return $this->render('Navigation', [
            'context' => $this,
            'currentMenu' => $currentMenu,
            'autoHideMenu' => $autoHideMenu,
        ]);
    }

}

