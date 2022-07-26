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

namespace backend\controllers;

use Yii;

/**
 * default controller to handle user requests.
 */
class AdminMenuController extends Sceleton {
 
    public $acl = ['BOX_HEADING_ADMINISTRATOR', 'BOX_ADMINISTRATOR_MENU'];

    private function buildTree($parent_id, $queryResponse) {
        $tree = [];
        foreach ($queryResponse as $response) {
            if ($response['parent_id'] == $parent_id) {
                if ($response['box_type'] == 1) {
                    $response['child'] = $this->buildTree($response['box_id'], $queryResponse);
                }
                if (defined($response['title'])) {
                    eval('$currentName =  ' . $response['title'] . ';');
                } else {
                    $currentName = $response['title'];
                }
                $response['title'] = $currentName;
                $tree[] = $response;
            }
        }
        return $tree;
    }
    
    public function actionIndex() {
        
        //$this->selectedMenu = array('administrator', 'admin-menu');
        $this->view->headingTitle = BOX_ADMINISTRATOR_MENU;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('admin-menu/'), 'title' => BOX_ADMINISTRATOR_MENU);

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="return save();">' . IMAGE_SAVE . '</span>';
        $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('admin-menu/reset').'" onclick="return confirm(\'' . TEXT_RESET_TO_DEFAULT . '\')" class="btn btn-primary"><i class="icon-refresh"></i>' . TEXT_RESET . '</a>';
        $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('admin-menu/export-menu').'" class="btn btn-primary backup"><i class="icon-file-text"></i>' . TEXT_EXPORT . '</a>';
        $this->topButtons[] = '<a href="javascript:void(0)" class="btn-import btn btn-primary backup"><i class="icon-file-text"></i>' . TEXT_IMPORT . '</a>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['admin-menu/add']) . '" class="btn btn-primary create_item_popup">' . IMAGE_ADD . '</a>';
        
        $current_menu = [];
        
        $queryResponse = \common\models\AdminBoxes::find()
                ->orderBy(['sort_order' => SORT_ASC])
                ->asArray()
                ->all(); 
        
        $currentMenu = $this->buildTree(0, $queryResponse);
        
        return $this->render('index', [
            'currentMenu' => $currentMenu,
        ]);
    }
    
    private function updateTree($data, $parent = 0) {
        $sortOrder = 0;
        foreach ($data as $item) {
            $object = \common\models\AdminBoxes::findOne($item->id);
            if (is_object($object)) {
                $object->parent_id = $parent;
                $object->sort_order = $sortOrder;
                $object->save();
                //--- update acl
                $acl = \common\models\AccessControlList::findOne(['access_control_list_key' => $object->title]);
                if (is_object($acl)) {
                    if ($parent == 0) {
                        $acl->parent_id = $parent;
                    } else {
                        $parentObject = \common\models\AdminBoxes::findOne($parent);
                        if (is_object($parentObject)) {
                            $parentAcl = \common\models\AccessControlList::findOne(['access_control_list_key' => $parentObject->title]);
                            if (is_object($parentAcl)) {
                                $acl->parent_id = $parentAcl->access_control_list_id;
                            }
                        }
                    }
                    $acl->sort_order = $sortOrder;
                    $acl->save();
                }
                //--- update acl
                if (isset($item->children) && is_array($item->children) && count($item->children) > 0) {
                    $this->updateTree($item->children, $item->id);
                }
            }
            $sortOrder++;
        }
    }

    public function actionSave() {
        $post_data = json_decode(\Yii::$app->request->post('post_data'));
        $this->updateTree($post_data);
        echo json_encode(['status' => 'ok']);
    }
    public function actionEdit(){
      $languages_id = \Yii::$app->settings->get('languages_id');
      $id = \Yii::$app->request->get('id');

      $this->layout = false;
      $this->view->usePopupMode = true;

      $languages = \common\helpers\Language::get_languages();
      $lang = array();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $languages[$i]['logo'] = $languages[$i]['image'];
        $lang[] = $languages[$i];
      }

      $queryResponse = \common\models\AdminBoxes::findOne($id);
      $queryResponse2 = \common\models\AdminBoxes::find()
        ->where(['box_id'=>$id])
        ->asArray()
        ->all();
      $queryResponse1 = \common\models\Translation::find()
        ->where(['translation_key'=>$queryResponse->title, 'translation_entity' => 'admin/main'])
        ->asArray()
        ->all();

      return $this->render('edit.tpl', [
        'languages' => $lang,
        'id' => $id,
        'languages_id' => $languages_id,
        'icons' => $queryResponse2,
        'currentMenu' => $queryResponse1
      ]);
    }
    public function actionSavePopup(){
      $languages_id = \Yii::$app->settings->get('languages_id');
      $post_data = json_decode(\Yii::$app->request->post('post_data'),true);
      $languages = \common\helpers\Language::get_languages();
      $post_data = \backend\design\Style::paramsFromOneInput(\Yii::$app->request->post('post_data'));

      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {

        if (isset($post_data['menu'][$languages[$i]['id']])) {
            foreach ($post_data['menu'][$languages[$i]['id']] as $key => $value) {
                \common\helpers\Translation::setTranslationValue($key, 'admin/main', $languages[$i]['id'], $value);
                if ($languages[$i]['id'] == $languages_id) {
                    $langPost = $value;
                }
            }
        }
      }
      if($post_data['icon']){
        $queryResponse = \common\models\AdminBoxes::findOne($post_data['id']);
        if(is_object($queryResponse)){
          $queryResponse->filename = $post_data['icon'];
          $queryResponse->save();
        }
      }
      echo json_encode(['status' => 'ok', 'val' => $langPost ?? null, 'message' => TEXT_MESSEAGE_SUCCESS]);
    }
    
    public function actionAdd() {
        $this->layout = false;
        return $this->render('add.tpl');
    }

    public function actionAddSubmit(){
        $object = new \common\models\AdminBoxes();
        $object->parent_id = 0;
        $object->sort_order = 0;
        $object->box_type = (int)\Yii::$app->request->post('box_type');
        $object->acl_check = '';
        $object->config_check = '';
        $object->path = \Yii::$app->request->post('path');
        $object->title = \Yii::$app->request->post('title');
        $object->filename = '';
        $object->save();
        return $this->redirect(Yii::$app->urlManager->createUrl('admin-menu/'));
    }
    
    public function actionDelete() {
        $this->layout = false;
        $id = (int)\Yii::$app->request->get('id');
        return $this->render('delete.tpl', ['id' => $id]);
    }
    
    private function deleteXMLTree($id) {
        $object = \common\models\AdminBoxes::findOne($id);
        if (is_object($object)) {
            $childs = \common\models\AdminBoxes::findAll(['parent_id' => $id]);
            foreach($childs as $child) {
                $this->deleteXMLTree($child->box_id);
            }
            $object->delete();
        }
    }
            
    public function actionDeleteSubmit() {
        $id = (int)\Yii::$app->request->post('id');
        $this->deleteXMLTree($id);
        return $this->redirect(Yii::$app->urlManager->createUrl('admin-menu/'));
    }
    
    public function actionImportMenu(){
        if (isset($_FILES['file']['tmp_name'])) {
            
            $xmlfile = file_get_contents($_FILES['file']['tmp_name']);
            $ob= simplexml_load_string($xmlfile);
            if (isset($ob)) {
                tep_db_query("TRUNCATE TABLE admin_boxes;");
                \common\helpers\MenuHelper::importAdminTree($ob);
            }
            unlink($_FILES['file']['tmp_name']);
        }
    }
    
    private function buildXMLTree($parent_id, $queryResponse) {
        $tree = [];
        foreach ($queryResponse as $response) {
            if ($response['parent_id'] == $parent_id) {
                if ($response['box_type'] == 1) {
                    $response['child'] = $this->buildXMLTree($response['box_id'], $queryResponse);
                }
                unset($response['box_id']);
                unset($response['parent_id']);
                $tree[] = $response;
            }
        }
        return $tree;
    }
    
    public function actionExportMenu(){
        $this->layout = false;
        
        $xml = new \yii\web\XmlResponseFormatter;
        $xml->rootTag = 'Menu';
        Yii::$app->response->format = 'custom_xml';
        Yii::$app->response->formatters['custom_xml'] = $xml;
        
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'text/xml; charset=utf-8');
        $headers->add('Content-Disposition', 'attachment; filename="admin-menu.xml"');
        $headers->add('Pragma', 'no-cache');
        
        $queryResponse = \common\models\AdminBoxes::find()
                ->orderBy(['sort_order' => SORT_ASC])
                ->asArray()
                ->all(); 
        
        $currentMenu = $this->buildXMLTree(0, $queryResponse, []);
        
        return $currentMenu;
    }
    
    public function actionReset(){
        \common\helpers\MenuHelper::resetAdminMenu();
        return $this->redirect(Yii::$app->urlManager->createUrl('admin-menu/'));
    }
}
