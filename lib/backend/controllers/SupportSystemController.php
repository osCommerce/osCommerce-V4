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
use common\helpers\Seo;

class SupportSystemController extends Sceleton {

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_SUPPORT_SYSTEM'];  
    
    private $supportSystem = null;
    
    public function __construct($id, $module = null) {
        \common\helpers\Translation::init('admin/support-system');
        parent::__construct($id, $module);
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('SupportSystem', 'allowed')){
            $this->supportSystem = $ext::getInstance();
        } else {
            return $this->redirect('index');
        }
    }

    public function actionIndex() {
        
        $this->selectedMenu = array('catalog', 'support-system');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('support-system/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('support-system/edit').'" class="create_item addprbtn"><i class="icon-tag"></i>'.TEXT_CREATE_NEW_TOPIC.'</a>';
        
        $this->view->ptoductsTable = array(
            array(
                'title' => TEXT_PRODUCTS_NAME,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 0,
            ),            
        );
        if (isset($_GET['row'])){
            $this->view->row = $_GET['row'];
        }
        
        if (isset($_GET['list'])){
            $this->view->list = $_GET['list'];
        }
        
        if (isset($_GET['pID'])){
            $this->view->pID = $_GET['pID'];
        }
        
        return $this->render('index');        
    }
    
    public function actionList(){
        
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $pID = Yii::$app->request->get('id', 0);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search = $keywords;
        }
        
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $current_page_number = ($start / $length) + 1;
        $responseList = array();
        
        if (!$pID && (isset($output['pID']) && $output['list'] == 'topics') ){
            $pID = $output['pID'];
        }

        if ($pID){
            $orderBy = " sort_order, td.info_title";
        } else {
            $orderBy = " pd.products_name";
        }
        
        if ($pID){
            $responseList[] = [
                '<span class="parent_cats"><i class="icon-circle"></i><i class="icon-circle"></i><i class="icon-circle"></i></span><input class="cell_identify" type="hidden" value="0">'
                . '<input class="cell_type" type="hidden" value="parent">',
                    '',
            ];
            $topics = $this->supportSystem->getProductTopics($pID, $languages_id, $orderBy, $search, false);
            $total = count($topics);
            foreach($topics as $topic){
                $responseList[] = array(
                    '<div class="handle_cat_list prod_handle">' .
                      '<span class="handle"><i class="icon-hand-paper-o"></i></span>'
                    . '<div class="cat_name">'.
                    $topic->description->info_title.tep_draw_hidden_field('id', $topic->topic_id, 'class="cell_identify"').
                    '</div></div>',
                    \yii\helpers\Html::checkbox('status', $topic->status, ['class' => 'check_on_off'])
                );
            }
        } else {
            $products = $this->supportSystem->getProductsWithTopics('', $search, [], false, false);
            $total = count($products);
            foreach($products as $product){
                $image = \common\classes\Images::getImage($product->products_id);
                $responseList[] = array(
                    '<div class="cat_name prod_name"><div class="p-holder">' .(!empty($image) ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') .
                    $product->description->products_name.tep_draw_hidden_field('id', $product->products_id, 'class="cell_identify"'). 
                    '<input class="cell_type" type="hidden" value="product"></div></div>',
                    \yii\helpers\Html::checkbox('status', array_sum(\yii\helpers\ArrayHelper::getColumn($product->topics, 'status')), ['class' => 'check_on_off'])
                );
            }
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $responseList,
            'list' => $pID > 0 ? 'topics' : 'products',
            'pID' =>$pID
        );
        echo json_encode($response);
    }
    
    public function actionView(){        
        $products_id = Yii::$app->request->get('pID');
        $topic_id = Yii::$app->request->get('tID');
        
        $product = null;
        
        if ($products_id){
            $product = $this->supportSystem->getProductById($products_id);
        } elseif ($topic_id) {
            $topic = $this->supportSystem->getTopic($topic_id);
        }
        
        return $this->renderAjax ('view', [
            'product' => $product,
            'topic' => $topic,
        ]);
    }

    private function get_assigned_catalog($platform_id,$validate=false){
        return \common\helpers\Categories::get_assigned_catalog($platform_id,$validate);
    }

    private function load_tree_slice($platform_id, $category_id){
        return \common\helpers\Categories::load_tree_slice($platform_id, $category_id);
    }
    
    public function actionEditCatalog()
    {
        $platform_id   = \common\classes\platform::defaultId();

        $this->layout = false;
        
        $products_id = Yii::$app->request->get('products_id');

        $assigned = [$products_id];//$this->get_assigned_catalog($platform_id, true);

        $tree_init_data = $this->load_tree_slice($platform_id,0);
        
        foreach ($tree_init_data as $_idx=>$_data) {
            if ($products_id && preg_match("/^p{$products_id}_*/", $_data['key'] ) ){
                $tree_init_data[$_idx]['selected'] = true;
                $assigned[$_data['key']] = $_data['key'];
            } else {
                $tree_init_data[$_idx]['selected'] = false;
            }
        }

        $selected_data = json_encode($assigned);

        return $this->render('edit-catalog.tpl', [
          'selected_data' => $selected_data,          
          'tree_data' => $tree_init_data,
          'tree_server_url' => Yii::$app->urlManager->createUrl(['support-system/load-tree', 'platform_id' => $platform_id]),
          //'tree_server_save_url' => Yii::$app->urlManager->createUrl(['platforms/update-catalog-selection', 'platform_id' => $platform_id])
        ]);
    }
    
    public function actionEdit(){
        \common\helpers\Translation::init('admin/categories/productedit');
        $products_id = Yii::$app->request->get('pID');
        $topic_id = Yii::$app->request->get('tID');
        
        $platforms = \common\models\Platforms::getPlatformsByType("non-virtual")->all();
        $def_platformId = \common\classes\platform::defaultId();
        $languages = \common\helpers\Language::get_languages();
        $this->view->def_platform_id = $def_platformId;
        $this->view->platforms = $platforms;        
        
        $assigned = $this->get_assigned_catalog($def_platformId, true);

        $tree_init_data = $this->load_tree_slice($def_platformId,0);
        
        if ($topic_id){
            //edit mode
            $topic = $this->supportSystem->getTopic($topic_id, true);
            if ($topic->descriptions){
                $topic->comDesc = [];
                foreach($topic->descriptions as $_desc){
                    $topic->comDesc[$_desc->platform_id . '_' . $_desc->language_id] = $_desc;
                }
            }            
            $title = TEXT_EDIT_TOPIC;
        } else {
            $topic = $this->supportSystem->createTopic($products_id);
            $title = TEXT_CREATE_NEW_TOPIC;
        }
        
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('support-system/index'), 'title' => $title);

        return $this->render('edit', [
                    'products_id' => $products_id,
                    'languages' => $languages,
                    'topic' => $topic,
        ]);
    }

    public function actionSubmit() {

        \common\helpers\Translation::init('admin/virtual-gift-card');
        
        $products_id = (int)Yii::$app->request->post('products_id');
        $topic_id = Yii::$app->request->post('topic_id');
        $post = Yii::$app->request->post('topic');        
        $isNew = false;
        if ($products_id){
            if ($topic_id){
                $topic = $this->supportSystem->getTopic($topic_id);
            } else {
                $topic = $this->supportSystem->createTopic($products_id);
                $topic->status = 1;
            }
            $isNew = $topic->isNewRecord;
            if ($topic->save()){
                $platforms = \common\models\Platforms::getPlatformsByType("non-virtual")->all();
                $languages = \common\helpers\Language::get_languages();
                foreach($platforms as $platform){
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                        $description = $this->supportSystem->getTopicDescription((int)$languages[$i]['id'], $platform->platform_id);
                        if ($description){
                            $description->setAttributes($post[$platform->platform_id][$languages[$i]['id']]);
                            if (empty($description->info_seo_name)){
                                $description->info_seo_name = Seo::makeSlug($description->info_title);
                            }
                            $description->save();
                        }                        
                    }
                }
            }
        }
        if ($isNew){
            $message = TEXT_MESSEAGE_SUCCESS_ADDED;
            return json_encode(['message' => $message, 'redirect' => Yii::$app->urlManager->createUrl(['support-system/edit', 'tID' => $topic->topic_id])]);
        } else {
            $message = TEXT_MESSEAGE_SUCCESS;
            return json_encode(['message' => $message, 'reload' => true]);
        }
    }
    
    public function actionLoadTree()
    {
        \common\helpers\Translation::init('admin/platforms');
        $this->layout = false;

        $platform_id = \common\classes\platform::defaultId();
        $do = Yii::$app->request->post('do','');

        $response_data = array();

        if ( $do == 'missing_lazy' ) {
          $category_id = Yii::$app->request->post('id');
          $selected = Yii::$app->request->post('selected');
          $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
          $selected_data = json_decode($req_selected_data,true);          
          $products_id = 0;          
          if ( !is_array($selected_data) ) {
            $selected_data = json_decode($selected_data, true);
          }
          if (is_array($selected_data)){
              $products_id = (int)$selected_data[0];
          }
          if (substr($category_id, 0, 1) == 'c') $category_id = intval(substr($category_id, 1));
// 
          $response_data['tree_data'] = $this->load_tree_slice($platform_id,$category_id);
          
          foreach( $response_data['tree_data'] as $_idx=>$_data ) {              
            $response_data['tree_data'][$_idx]['selected'] = preg_match("/^p{$products_id}_*/", $_data['key'] );
          }
          $response_data = $response_data['tree_data'];
        }

        if ( $do == 'update_selected' ) {
          $id = Yii::$app->request->post('id');
          $selected = Yii::$app->request->post('selected');
          $select_children = Yii::$app->request->post('select_children');
          $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
          $selected_data = json_decode($req_selected_data,true);
          if ( !is_array($selected_data) ) {
            $selected_data = json_decode($selected_data,true);
          }

          if ( substr($id,0,1)=='p' ) {
            list($ppid, $cat_id) = explode('_',$id,2);
            if ( $selected ) {
              // check parent categories
              $parent_ids = array((int)$cat_id);
              \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0], false);
              foreach( $parent_ids as $parent_id ) {
                if ( !isset($selected_data['c'.(int)$parent_id]) ) {
                  $response_data['update_selection']['c'.(int)$parent_id] = true;
                  $selected_data['c'.(int)$parent_id] = 'c'.(int)$parent_id;
                }
              }
              if ( !isset($selected_data[$id]) ) {
                $response_data['update_selection'][$id] = true;
                $selected_data[$id] = $id;
              }
            }else{
              if ( isset($selected_data[$id]) ) {
                $response_data['update_selection'][$id] = false;
                unset($selected_data[$id]);
              }
            }
          }elseif ( substr($id,0,1)=='c' ) {
            $cat_id = (int)substr($id,1);
            if ( $selected ) {
              $parent_ids = array((int)$cat_id);
              \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0], false);
              foreach( $parent_ids as $parent_id ) {
                if ( !isset($selected_data['c'.(int)$parent_id]) ) {
                  $response_data['update_selection']['c'.(int)$parent_id] = true;
                  $selected_data['c'.(int)$parent_id] = 'c'.(int)$parent_id;
                }
              }
              if ( $select_children ) {
                $children = array();
                $this->tep_get_category_children($children,$platform_id,$cat_id);
                foreach($children as $child_key){
                  if ( !isset($selected_data[$child_key]) ) {
                    $response_data['update_selection'][$child_key] = true;
                    $selected_data[$child_key] = $child_key;
                  }
                }
              }
              if ( !isset($selected_data[$id]) ) {
                $response_data['update_selection'][$id] = true;
                $selected_data[$id] = $id;
              }
            }else{
              $children = array();
              $this->tep_get_category_children($children,$platform_id,$cat_id);
              foreach($children as $child_key){
                if ( isset($selected_data[$child_key]) ) {
                  $response_data['update_selection'][$child_key] = false;
                  unset($selected_data[$child_key]);
                }
              }
              if ( isset($selected_data[$id]) ) {
                $response_data['update_selection'][$id] = false;
                unset($selected_data[$id]);
              }
            }
          }

          $response_data['selected_data'] = $selected_data;
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $response_data;

    }
    
    public function actionSwitchStatus(){
        $products_id = Yii::$app->request->post('pID');
        $status = Yii::$app->request->post('status');
        if ($products_id){
            $topic_id = Yii::$app->request->post('target');
            $this->supportSystem->setStatus($products_id, $topic_id, $status == 'true'? 1:0);
        } else {
            $products_id = Yii::$app->request->post('target');
            $this->supportSystem->setStatus($products_id, null, $status == 'true'? 1:0);
        }
    }
    
    public function actionSort(){
        $products_id = Yii::$app->request->post('pID');
        $sort_order = Yii::$app->request->post('sort_order', []);
        if ($products_id && $sort_order){
            $this->supportSystem->setSort($products_id, $sort_order);
        }
    }
    
    public function actionDelete(){
        $products_id = Yii::$app->request->post('pID');
        $topic_id = Yii::$app->request->post('tID');
        if ($topic_id){
            $this->supportSystem->deteleTopic($topic_id);
        } else {
            if ($products_id){
                $this->supportSystem->deteleProductsTopics($products_id);
            }
        }
        echo json_encode(['message' => TEXT_REMOVED, 'messageType' => 'success']);
        exit();
    }

}
