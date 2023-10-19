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
use yii\helpers\ArrayHelper;
use backend\components\Information;
use \common\helpers\Translation;
//Yii::import('application.components.Information', true);
/**
 * default controller to handle user requests.
 */
class Information_managerController extends Sceleton  {
	  
    public $acl = ['BOX_HEADING_DESIGN_CONTROLS', 'BOX_INFORMATION_MANAGER'];
    
    public function __construct($id, $module=null){
      Translation::init('admin/information_manager');
      parent::__construct($id, $module);
    }    
    
    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('design_controls', 'information_manager');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('information_manager/index'), 'title' => MANAGER_INFORMATION);
      $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl(['information_manager/create']).'" class="btn btn-primary"><i class="icon-file-o"></i>'.TEXT_ADD_NEW_PAGE.'</a>';
      
      $this->view->headingTitle = MANAGER_INFORMATION;
      $this->view->infoTable = array(     
            array(
                'title' => '<input type="checkbox" class="uniform">',
                'not_important' => 2
            ),
            array(
                'title' => TITLE_INFORMATION,
                'not_important' => 0
            ),
      );
      if ( \common\classes\platform::isMulti() ) {
        $this->view->infoTable[] = array(
          'title' => TABLE_HEAD_PLATFORM_NAME,
          'not_important' => 0.
        );
        $this->view->infoTable[] = array(
          'title' => TABLE_HEAD_PLATFORM_PAGE_ASSIGN,
          'not_important' => 0.
        );
      }else{
        $this->view->infoTable[] = array(
          'title' => TABLE_HEADING_STATUS,
          'not_important' => 0,
        );
      }

	  return $this->render('index', array(
      'platforms' => \common\classes\platform::getList(false),
      'first_platform_id' => \common\classes\platform::firstId(),
      'isMultiPlatforms' => \common\classes\platform::isMulti(),
    ));
    }
  
  public function actionList(){
    $languages_id = \Yii::$app->settings->get('languages_id');
     $draw = Yii::$app->request->get('draw', 1);
     $start = Yii::$app->request->get('start', 0);
     $length = Yii::$app->request->get('length', 10);
     if( $length == -1 ) $length = 10000;
     
       $search = '';
        if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = "(page_title like '%" . $keywords . "%' or info_title like '%" . $keywords . "%' or description like '%" . $keywords . "%')";
        } else {
            $search_condition = "1";
        }
        $list_query_raw = 
                "select * ".
                "from " . TABLE_INFORMATION . " ".
                "where languages_id='".$languages_id."' and platform_id=".\common\classes\platform::firstId()." and affiliate_id = 0 ".
                " and {$search_condition} ".
                (Information::showHidePage() ? '' : " and hide=0 ").
                "order by hide, v_order, info_title ";
        $current_page_number = ($start / $length) + 1;
        $listing_split = new \splitPageResults(
          $current_page_number, $length, $list_query_raw, $listing_query_numrows, 'information_id'
        );

        $db_query = tep_db_query($list_query_raw);
     
        $responseList = array();
        if (tep_db_num_rows($db_query)>0){
          $visible_flags = array();
          if ( \common\classes\platform::isMulti() ) {
              foreach ( \common\classes\platform::getList(false) as $_platform ) {
                  $get_visible_flags_r = tep_db_query(
                      "select i.information_id, ".$_platform['id']." as platform_id, IFNULL(i2.visible,i.visible) as visible " .
                      "from " . TABLE_INFORMATION . " i " .
                      " left join ".TABLE_INFORMATION. " i2 ON i2.information_id=i.information_id and i2.platform_id=".$_platform['id']." and i2.languages_id=i.languages_id and i2.affiliate_id=i.affiliate_id ".
                      "where i.languages_id='" . $languages_id . "' and i.affiliate_id = 0 and i.platform_id='".\common\classes\platform::defaultId()."' " .
                      ""
                  );
                  while ($_visible = tep_db_fetch_array($get_visible_flags_r)) {
                      if ( !$_visible['visible'] ) continue;
                      $visible_flags[$_visible['information_id'] . '^' . $_visible['platform_id']] = 1;
                  }
              }
          }else{
            $visible_flags = array();
          }
          while( $val = tep_db_fetch_array($db_query) ) {
            $row = array(
              '<input type="checkbox" class="uniform">' . '<input class="cell_identify" type="hidden" value="' . $val['information_id'] . '">',
              '<div class="tp_title click_double' . ($val['hide'] ? ' hide-page' : '') . '" data-click-double="' . \Yii::$app->urlManager->createUrl(['information_manager/edit', 'info_id' => $val['information_id']]) . '">'.($val['page_title']?$val['page_title']:$val['info_title']).'</div>',
            );
            if ( \common\classes\platform::isMulti() ) {
              $platforms = '';
              $public_checkbox = '';
              foreach ( \common\classes\platform::getList(false) as $platform_variant ) {
                $platforms .= '<div id="page-' . $val['information_id'] . '-' . $platform_variant['id'] . '"' . (isset($visible_flags[$val['information_id']."^".$platform_variant['id']]) ? '' : ' class="platform-disable"') . '>'.$platform_variant['text'].'</div>';

                $public_checkbox .= '<div>'.
                  (isset($visible_flags[$val['information_id']."^".$platform_variant['id']])?
                    '<input type="checkbox" value="' . $val['information_id'] . '" name="page_status['.$platform_variant['id'].']" class="check_on_off" checked="checked" data-id="page-' . $val['information_id'] . '-' . $platform_variant['id'] . '">' :
                    '<input type="checkbox" value="' . $val['information_id'] . '" name="page_status['.$platform_variant['id'].']" class="check_on_off" data-id="page-' . $val['information_id'] . '-' . $platform_variant['id'] . '">'
                  ).'</div>';
              }
              $row[] = '<div class="platforms-cell">'.$platforms.'</div>';
              $row[] = '<div class="platforms-cell-checkbox">'.$public_checkbox.'</div>';
            }else{
              $row[] = ($val['visible'] == 1 ? '<input type="checkbox" value="' . $val['information_id'] . '" name="page_status" class="check_on_off" checked="checked">' : '<input type="checkbox" value="' . $val['information_id'] . '" name="page_status" class="check_on_off">');
            }
            $responseList[] = $row;
          }
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => intval($listing_query_numrows),
            'recordsFiltered' => intval($listing_query_numrows),
            'data' => $responseList
        );
        echo json_encode($response);
  }
    

  public function actionCreate()
  {
    global $language;

    $back = 'information_manager';
    if (isset($_GET['back'])) {
      $back = $_GET['back'];
    }
    $this->view->backOption = $back;

    $this->selectedMenu = array('design_controls', 'information_manager');

    $pages_data = array();
    $languages = \common\helpers\Language::get_languages();
    $edit_page_title = '';
    $platforms = \common\classes\platform::getList(false);
    foreach( $platforms as $platform ) {
      $page_data = array();
      foreach ($languages as $i => $_language) {
        $_lang_id = $_language['id'];
        $page_data[$i] = array();
        $page_data[$i]['code'] = $_language['code'];

        $languages[$i]['logo'] = $_language['image'];

        // $data is never assigned here since rev.1 in 2016 (seems it's copy/paste from actionEdit)
        $page_data[$i]['c_page_title'] = tep_draw_input_field('page_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['page_title']) ? $data['page_title'] : '', 'class="form-control"');
        $page_data[$i]['c_info_title'] = tep_draw_input_field('info_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['info_title']) ? $data['info_title'] : '', 'class="form-control"');

        $page_data[$i]['c_description'] = tep_draw_textarea_field('description[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['description']) ? $data['description'] : '', 'class="form-control ckeditor" id="desc'.$_lang_id.$platform['id'].'"');
        $page_data[$i]['c_description_short'] = tep_draw_textarea_field('description_short[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['description_short']) ? $data['description_short'] : '', 'class="form-control ckeditor" id="desc_short'.$_lang_id.$platform['id'].'"');

          $page_data[$i]['editor_id'] = 'desc' . $_lang_id . $platform['id'];

        $page_data[$i]['c_seo_page_name'] = tep_draw_input_field('seo_page_name[' . $_lang_id . ']['.$platform['id'].']', isset($data['seo_page_name']) ? $data['seo_page_name'] : '', 'class="form-control"');
        $page_data[$i]['c_old_seo_page_name'] = tep_draw_input_field('old_seo_page_name[' . $_lang_id . ']['.$platform['id'].']', isset($data['old_seo_page_name']) ? $data['old_seo_page_name'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_title'] = tep_draw_input_field('meta_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['meta_title']) ? $data['meta_title'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_key'] = tep_draw_textarea_field('meta_key[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['meta_key']) ? $data['meta_key'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_description'] = tep_draw_textarea_field('meta_description[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['meta_description']) ? $data['meta_description'] : '', 'class="form-control"');
        $page_data[$i]['c_h1_tag'] = tep_draw_input_field('information_h1_tag[' . $_lang_id . ']['.$platform['id'].']', isset($data['information_h1_tag']) ? $data['information_h1_tag'] : '', 'class="form-control"');
        $page_data[$i]['c_h2_tag'] = $data['information_h2_tag'] ?? null; //tep_draw_input_field('information_h2_tag[' . $_lang_id . ']['.$platform['id'].']', isset($data['information_h2_tag']) ? $data['information_h2_tag'] : '', 'class="form-control"');
        $page_data[$i]['c_h3_tag'] = $data['information_h3_tag'] ?? null; //tep_draw_input_field('information_h3_tag[' . $_lang_id . ']['.$platform['id'].']', isset($data['information_h3_tag']) ? $data['information_h3_tag'] : '', 'class="form-control"');
      }
      $pages_data[$platform['id']]['lang'] = $page_data;
      $pages_data[$platform['id']]['visible'] = 0;
      $pages_data[$platform['id']]['platform_id'] = $platform['id'];
    }
      $additionalFields = [];
      $additionalFields['date_added'] = date("Y-m-d");
      $additionalFields['date_added1'] = date("d M Y");
      $additionalFields['type'] = 0;
      $additionalFields['image'] = '';
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('information_manager/edit'), 'title' => TEXT_ADD_NEW_PAGE);

    return $this->render('edit', array(
        'showHidePage' => Information::showHidePage(),
        'hidePage' => 0,
        'templates' => Information::template(),
      'information_id' => 0,
      'languages' => $languages,
      'platforms' => \common\classes\platform::getList(false),
      'first_platform_id' => \common\classes\platform::firstId(),
      'isMultiPlatforms' => \common\classes\platform::isMulti(),
      'additionalFields' => $additionalFields,
      'dirImages' => Yii::getAlias('@webCatalogImages/'.Information::imagesLocation()),
      'pages_data' => $pages_data,
      'link_href_back' => \Yii::$app->urlManager->createUrl(['information_manager/index', 'back' => 'information_manager']),
    ));
  }

  public function actionEdit()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');

    //$this->layout = false;

    $info_id = Yii::$app->request->get('info_id', 0);

    //$this->selectedMenu = array('seo_cms', 'cms', 'information_manager');
    $this->selectedMenu = array('design_controls', 'information_manager');

      $this->topButtons[] = '<span class="btn btn-confirm btn-save-page">'. IMAGE_SAVE .'</span>';

    $pages_data = array();
    $languages = \common\helpers\Language::get_languages();
    $edit_page_title = '';
    $platforms = \common\classes\platform::getList(false);
      $date = date("Y-m-d H:i:s");
      $type = 0;
      $image = '';
      $mapsId = 0;
      $additionalFields = [];
    foreach( $platforms as $platform ) {
      $page_data = array();
      $data_visible = false;
      $no_logged = false;
      foreach ($languages as $i => $_language) {
        $_lang_id = $_language['id'];
        $languages[$i]['logo'] = $_language['image'];

        $data = Information::read_data($info_id, $_lang_id, $platform['id']);
        if ($_lang_id == $languages_id) {
          //$edit_page_title = $data['page_title'];
        }

        $page_data[$i] = $data;
        if (!is_array($page_data[$i])) $page_data[$i] = array();
        $page_data[$i]['code'] = $_language['code'];

        $page_data[$i]['c_page_title'] = tep_draw_input_field('page_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['page_title']) ? $data['page_title'] : '', 'class="form-control"');
        $page_data[$i]['c_info_title'] = tep_draw_input_field('info_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['info_title']) ? $data['info_title'] : '', 'class="form-control"');

        $page_data[$i]['c_description'] = \common\helpers\Html::textarea('description[' . $_lang_id . ']['.$platform['id'].']', $data['description'] ?? '',
                ['wrap' => 'soft', 'cols' => '70', 'rows' => '5', 'class' => 'form-control ckeditor',  'id' => 'desc'.$_lang_id.$platform['id']] );
        $page_data[$i]['c_description_short'] = \common\helpers\Html::textarea('description_short[' . $_lang_id . ']['.$platform['id'].']', $data['description_short'] ?? '',
                ['wrap' => 'soft', 'cols' => '70', 'rows' => '5', 'class' => 'form-control ckeditor',  'id' => 'desc_short'.$_lang_id.$platform['id']] );

        $page_data[$i]['editor_id'] = 'desc' . $_lang_id . $platform['id'];

        $page_data[$i]['c_seo_page_name'] = tep_draw_input_field('seo_page_name[' . $_lang_id . ']['.$platform['id'].']', isset($data['seo_page_name']) ? $data['seo_page_name'] : '', 'class="form-control"');
        $page_data[$i]['noindex_option'] = tep_draw_checkbox_field('noindex_option[' . $_lang_id . ']['.$platform['id'].']', '1', ArrayHelper::getValue($data, 'noindex_option') == 1, '', 'class="check_on_off"');
        $page_data[$i]['nofollow_option'] = tep_draw_checkbox_field('nofollow_option[' . $_lang_id . ']['.$platform['id'].']', '1', ArrayHelper::getValue($data, 'nofollow_option') == 1, '', 'class="check_on_off"');
        $page_data[$i]['rel_canonical'] = tep_draw_input_field('rel_canonical[' . $_lang_id . ']['.$platform['id'].']', $data['rel_canonical'] ?? null, 'class="form-control form-control-small"');
        $page_data[$i]['c_old_seo_page_name'] = tep_draw_input_field('old_seo_page_name[' . $_lang_id . ']['.$platform['id'].']', isset($data['old_seo_page_name']) ? $data['old_seo_page_name'] : '', 'class="form-control seo-input-field"');
        $page_data[$i]['c_old_seo_page_name_clear'] = (isset($data['old_seo_page_name']) ? $data['old_seo_page_name'] : '');
        $page_data[$i]['c_meta_title'] = tep_draw_input_field('meta_title[' . $_lang_id . ']['.$platform['id'].']', isset($data['meta_title']) ? $data['meta_title'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_key'] = tep_draw_textarea_field('meta_key[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['meta_key']) ? $data['meta_key'] : '', 'class="form-control"');
        $page_data[$i]['c_meta_description'] = tep_draw_textarea_field('meta_description[' . $_lang_id . ']['.$platform['id'].']', 'soft', '70', '5', isset($data['meta_description']) ? $data['meta_description'] : '', 'class="form-control"');
        $page_data[$i]['c_h1_tag'] = tep_draw_input_field('information_h1_tag[' . $_lang_id . ']['.$platform['id'].']', isset($data['information_h1_tag']) ? $data['information_h1_tag'] : '', 'class="form-control"');
        $page_data[$i]['c_h2_tag'] = $data['information_h2_tag'] ?? null; //tep_draw_input_field('information_h2_tag[' . $_lang_id . ']['.$platform['id'].']', isset($data['information_h2_tag']) ? $data['information_h2_tag'] : '', 'class="form-control"');
        $page_data[$i]['c_h3_tag'] = $data['information_h3_tag'] ?? null; //tep_draw_input_field('information_h3_tag[' . $_lang_id . ']['.$platform['id'].']', isset($data['information_h3_tag']) ? $data['information_h3_tag'] : '', 'class="form-control"');

        if ( !is_array($data) ){
            $data_visible = $pages_data[\common\classes\platform::defaultId()]['visible'] ?? null;
        }else
        if ($data['visible'] ?? null) {
          $data_visible = true;
        }
        if ($data['no_logged'] ?? null) {
          $no_logged = true;
        }
          if ($data['type'] ?? null) {
              $additionalFields['type'] = true;
          }
          if ($data['date_added'] ?? null) {
              $date =$data['date_added'];
              $additionalFields['date_added'] = date("Y-m-d",strtotime($date));
              $additionalFields['date_added1'] = date("d M Y",strtotime($date));
          }
          if ($data['image'] ?? null) {
              $additionalFields['image'] = $data['image'];
          }
          if ($data['maps_id'] ?? null) {
              $mapsId = $data['maps_id'];
          }
          if ($data['hide_on_xml'] ?? null) {
              $additionalFields['hide_on_xml'] = $data['hide_on_xml'];
          }
      }
      $pages_data[$platform['id']]['lang'] = $page_data;
      $pages_data[$platform['id']]['visible'] = $data_visible?'1':'0';;
      $pages_data[$platform['id']]['platform_id'] = $platform['id'];
      $pages_data[$platform['id']]['no_logged'] = $no_logged?'1':'0';
    }


    $information_id = Yii::$app->request->get('info_id', 0);

    //$button=array("Update");
    //$title=($information_id ?  "" . EDIT_ID_INFORMATION . " $information_id" : "" . ADD_QUEUE_INFORMATION);
//    echo tep_draw_form('edit_info',FILENAME_INFORMATION_MANAGER.'/update', '', 'post', 'id="edit_info"');
//    echo tep_draw_hidden_field('information_id', "$information_id");
    //$this->view->tabList = Information::form(($information_id ? 'edit': 'Added'), $information_id, $title);

    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('information_manager/edit'), 'title' => TEXT_EDITING . ' '.$edit_page_title.' page');

    $platforms = \common\classes\platform::getList(false);
    $some_need_login = false;
    foreach ($platforms as $item){
      if ($item['need_login']){
        $some_need_login = true;
      }
    }

      $hidePage = tep_db_fetch_array(tep_db_query("select hide from " . TABLE_INFORMATION . " where information_id = '" . (int)$info_id . "'"));

      /**
       * @var $imageMaps \common\extensions\ImageMaps\models\ImageMaps
       */
      if ($imageMaps = \common\helpers\Extensions::getModel('ImageMaps', 'ImageMaps')) {
        if ($mapsId && !empty($imageMaps)) {
            $map = $imageMaps::findOne($mapsId);
            $mapImage = $map->image;
            $mapTitle = $map->getTitle($languages_id);
        }
      }

      foreach (\common\classes\platform::getList(false) as $frontend) {
          if ($pages_data[$frontend['id']]['visible']) {
              $seo_url = tep_db_fetch_array(tep_db_query("select seo_page_name from " . TABLE_INFORMATION . " where information_id = '" . $information_id . "' and languages_id = '" . (int)\common\helpers\Language::get_default_language_id() . "' and platform_id = '" . (int)$frontend['id'] . "'"));
              if ($seo_url['seo_page_name'] ?? null) {
                  $preview_link[] = [
                      'link' => 'http://' . $frontend['platform_url'] . '/' . $seo_url['seo_page_name'],
                      'name' => $frontend['text']
                  ];
              } else {
                  $preview_link[] = [
                      'link' => 'http://' . $frontend['platform_url'] . '/info?info_id=' . $information_id,
                      'name' => $frontend['text']
                  ];
              }
          }
      }

    return $this->render('edit', array(
      'preview_link' => $preview_link ?? null,
      'showHidePage' => Information::showHidePage(),
      'hidePage' => $hidePage['hide'] ?? null,
      'templates' => Information::template($information_id),
      'some_need_login' => $some_need_login,
      'information_id' => $information_id,
      'languages' => $languages,
      'platforms' => $platforms,
        'additionalFields' => $additionalFields,
      'first_platform_id' => \common\classes\platform::firstId(),
      'isMultiPlatforms' => \common\classes\platform::isMulti(),
      'pages_data' => $pages_data,
        'dirImages' => Yii::getAlias('@webCatalogImages/'.Information::imagesLocation()),
      'link_href_back' => \Yii::$app->urlManager->createUrl(['information_manager/', 'info_id' => $information_id, 'back' => 'information_manager']),
        'mapsId' => $mapsId,
        'mapsTitle' => $mapTitle ?? null,
        'mapsImage' => $mapImage ?? null,
    ));
  }

  public function actionPageSave(){
    $messageStack = \Yii::$container->get('message_stack');

    $this->view->errorMessageType = 'success';
    $this->view->errorMessage = '';
    $this->layout = false;

    //$popup = (int) Yii::$app->request->post('popup');
    $info_id = (int)Yii::$app->request->post('information_id');

    $_POST = tep_db_prepare_input($_POST);
    $languages = \common\helpers\Language::get_languages();
    $platforms = \common\classes\platform::getList(false);

    $_old_data = array();
    $_new_data = array();
    $show_edit = false;
    if ($info_id > 0) {
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $_old_data[$languages[$i]['id']] = array();
        $_new_data[$languages[$i]['id']] = array();
        foreach( $platforms as $platform ) {
          $_old_data[$languages[$i]['id']][$platform['id']] = tep_db_fetch_array(tep_db_query(
              "select * from " . TABLE_INFORMATION . " where information_id= '" . $info_id . "' and languages_id = '" . $languages[$i]['id'] . "' and platform_id='".(int)$platform['id']."' and affiliate_id = '0'"
          ));
          Information::update_information($_POST, $languages[$i]['id'], $platform['id']);
          Information::update_no_logged(\Yii::$app->request->post('no_logged'), $languages[$i]['id'], $platform['id'], \Yii::$app->request->post('information_id'));
          $_new_data[$languages[$i]['id']][$platform['id']] = tep_db_fetch_array(tep_db_query(
              "select * from " . TABLE_INFORMATION . " where information_id= '" . $info_id . "' and languages_id = '" . $languages[$i]['id'] . "' and platform_id='".(int)$platform['id']."' and affiliate_id = '0'"
          ));
        }
      }
      Information::updateAdditionalField($info_id);
      Yii::$app->request->setQueryParams(['info_id'=>$info_id]);
      //$this->view->errorMessage = UPDATE_ID_INFORMATION.$info_id;
      $this->view->errorMessage = MESSAGE_INFORMATION_UPDATED;
    } else {
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        foreach( $platforms as $platform ) {
          $_POST['information_id'] = Information::update_information($_POST, $languages[$i]['id'], $platform['id']);
          $info_id = Information::update_no_logged(\Yii::$app->request->post('no_logged'), $languages[$i]['id'], $platform['id'], $_POST['information_id']);
        }
      }
      Information::updateAdditionalField((int)\Yii::$app->request->post('information_id'));
      $this->view->errorMessage = defined('TEXT_INFO_SAVED')?TEXT_INFO_SAVED:'Page added';
      $show_edit = true;
      Yii::$app->request->setQueryParams(['info_id'=>$_POST['information_id']]);
    }

      if (Information::showHidePage()) {
          Information::updateHideStatus($info_id, Yii::$app->request->post('hide_page'));
      }
      Information::templateSave($info_id, Yii::$app->request->post('page_template'), Yii::$app->request->post('page_style'));

    \common\helpers\PageStatus::saveScheduledStatuses('information', $info_id, Yii::$app->request->post('page_status'));
    
    if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
        $ext::saveInfoLinks($info_id, $_POST);
        foreach ($_old_data as $_lang_id=>$_sub_data) {
            foreach ($_sub_data as $_platform_id=>$__old_data) {
                $ext::trackInfoLinks($info_id, $_lang_id, $_platform_id, $_new_data[$_lang_id][$_platform_id], $__old_data);
            }
        }
    }
      \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),'information');
      if($_POST['information_id'] > 0){
          $response['status'] = 'ok';
      }
      if ($messageStack->size() > 0) {
          $this->view->errorMessage = $messageStack->output(true);
          $this->view->errorMessageType = $messageStack->messageType;
      }
      $response['error'] = $this->render('error');

      if ( $show_edit ) {
          $response['url'] = Yii::$app->urlManager->createUrl(['information_manager/edit', 'info_id'=>$info_id]);
      }

      return json_encode($response);
  }

  public function actionPageactions(){
    $languages_id = \Yii::$app->settings->get('languages_id');

    $this->layout = false;
//
    $information_id = Yii::$app->request->post('info_id');

    $info_data = tep_db_fetch_array(tep_db_query(
      "SELECT information_id, info_title, /*page,*/ date_added, last_modified ".
      "FROM ".TABLE_INFORMATION." ".
      "WHERE information_id='".(int)$information_id."' ".
      " AND languages_id='".(int)$languages_id."' ".
      " AND platform_id='".\common\classes\platform::firstId()."' ".
      " AND affiliate_id=0"
    ));
    if ( !is_array($info_data) ) exit();
    if ( $info_data['information_id']>0 ) {
      $info_data['link_href_edit'] = \Yii::$app->urlManager->createUrl(['information_manager/edit', 'info_id' => $info_data['information_id'], 'back' => 'information_manager']);
      $info_data['link_href_delete'] = \Yii::$app->urlManager->createUrl(['information_manager/delete_confirm', 'info_id' => $info_data['information_id'], 'back' => 'information_manager']);
    }

    $iInfo = new \objectInfo($info_data);

//    $heading = array();
//    $contents = array();

      if ($information_id) {
          return $this->render('pageactions.tpl', ['iInfo' => $iInfo]);
      } else {
        return '';
      }

}

  public function actionConfirmDelete()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');

    $this->layout = false;
//
    $information_id = Yii::$app->request->post('info_id');

    $info_data = tep_db_fetch_array(tep_db_query(
      "SELECT information_id, info_title, /*page,*/ date_added, last_modified ".
      "FROM ".TABLE_INFORMATION." ".
      "WHERE information_id='".(int)$information_id."' ".
      " AND languages_id='".(int)$languages_id."' ".
      " AND platform_id='".\common\classes\platform::firstId()."' ".
      " AND affiliate_id=0"
    ));
    if ( !is_array($info_data) ) $info_data = array();
 
    $iInfo = new \objectInfo($info_data);

    return $this->render('pageactions_delete_confirm.tpl', ['iInfo' => $iInfo]);
  }
  
  public function actionUpdate(){
    
    $this->layout = false;

    $_POST = tep_db_prepare_input($_POST);
    $languages = \common\helpers\Language::get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      Information::update_information($_POST, $languages[$i]['id']);
    }
      \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),'information');
    $this->redirect('list');
  }

  public function actionStatusChangeSelected(){
    global $language;
    $this->layout = false;

    $selected_ids = Yii::$app->request->post('selected_ids',array());
    $status = Yii::$app->request->post('status');
    foreach( $selected_ids as $info_id ) {
      Information::update_visible_status($info_id, in_array((string)$status,array('1','true')));
    }
      \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),'information');
    //$this->redirect('list');
  }
  public function actionSwitchStatus()
  {
    global $language;

    $this->layout = false;

    $switch_type = Yii::$app->request->post('type','page_status');
    $info_id = Yii::$app->request->post('id',0);
    $status = Yii::$app->request->post('status');
    if ( $switch_type=='page_status' ) {
      Information::update_visible_status($info_id, in_array((string)$status,array('1','true')));
    }elseif ( preg_match('/^page_status\[(\d+)\]$/',$switch_type, $get_platform) ) {
      Information::update_visible_status($info_id, in_array((string)$status,array('1','true')), $get_platform[1]);
    }
      \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),'information');
    $this->redirect('list');
  }

  public function actionAdd(){
    global $language;  
    
    $this->layout = false;
    
    $insert_id = '';
    $_POST = tep_db_prepare_input($_POST);
    $languages = \common\helpers\Language::get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      Information::add_information($_POST, $languages[$i]['id']);
    }
    $this->redirect('list');    
  }
  
  public function actionDelete(){
    
    $this->layout = false;
    
    $information_id = Yii::$app->request->getBodyParam('info_id', 0);
    
    if ($information_id>0) {
      Information::delete_information($information_id);
      if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
          $ext::deleteInfoLinks($information_id);
      }
        \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),'information');
    }
    $this->actionList();
    //$this->redirect('list');
  }
  public function actionDeleteSelected(){
    $this->layout = false;

    $selected_ids = Yii::$app->request->getBodyParam('selected_ids', array());

    foreach ($selected_ids as $selected_id) {
      Information::delete_information($selected_id);
    }
      \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),'information');
  }

    public function actionPageLinks()
    {
        $this->layout = false;
        $pageLinks = \common\classes\TlUrl::pageLinks();

        if (Yii::$app->request->get('json', false)) {
            return json_encode($pageLinks);
        }

        return $this->render('pagelinks.tpl', [
            'items' => $pageLinks['items'],
            'suggest' => $pageLinks['suggest']
        ]);
    }


function actionSeoproductsname($products_id){
    $languages_id = \Yii::$app->settings->get('languages_id');

    $product_seo_name = \common\helpers\Product::getSeoName($products_id, $languages_id);
  
    if($product_seo_name){
        $url_product = $product_seo_name;
    }else{
        $url_product = '/catalog/product?products_id='.$products_id;
    }
    return $url_product;
}

    public function actionAllList()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $info = \common\models\Information::find()
            ->select(['id' => 'information_id', 'text' => 'info_title'])->distinct()
            ->where([
                'languages_id' => $languages_id
            ])
            ->asArray()
            ->all();

        $infoArr = [];
        foreach ($info as $item) {
            if (trim($item['text'])) {
                $infoArr[] = $item;
            }
        }

        return json_encode($infoArr);
    }

    public function actionComponentKeys()
    {
        $this->layout = false;
        $templates = \common\classes\PageComponents::componentTemplates();
        $isHtml = \Yii::$app->request->get('html', 0);

        return $this->render('component-templates.tpl', [
            'templates' => $templates,
            'isHtml' => $isHtml,
            'languages_id' => \Yii::$app->request->get('languages_id', 0),
            'platform_id' => \Yii::$app->request->get('platform_id', 0),
        ]);
    }

    public function actionChangePageStatus()
    {
        $type = \Yii::$app->request->post('type');
        $pageId = \Yii::$app->request->post('page_id');
        $status = \Yii::$app->request->post('status');

        $pageStatus = \common\models\PageStatus::findOne([
            'type' => $type,
            'page_id' => $pageId
        ]);
        $pageStatus->status = $status;
        $pageStatus->save();
        if (!$pageStatus->errors) {
            return 'ok';
        }
    }
}
