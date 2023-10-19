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

use common\helpers\Seo;
use Yii;
use yii\helpers\ArrayHelper;

class PropertiesController extends Sceleton {
    
    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_PROPERTIES'];
    
  private $properties_types_array = array();

  function __construct($id, $module=null) {
    global $language;
    \common\helpers\Translation::init('admin/properties');
    \common\helpers\Translation::init('admin/main');

    $this->properties_types_array[''] = TEXT_PLEASE_CHOOSE;
    $this->properties_types_array['text'] = TEXT_TEXT;
    $this->properties_types_array['number'] = TEXT_NUMBER;
    $this->properties_types_array['interval'] = TEXT_NUMBER_INTERVAL;
    $this->properties_types_array['flag'] = TEXT_PR_FLAG;
    $this->properties_types_array['file'] = TEXT_PR_FILE;

    parent::__construct($id, $module);
  }

  protected function getPossiblePropertiesFlags()
  {
      return array(
          'display_product' => array(
              'label' => defined('TEXT_PRODUCT_INFO')?TEXT_PRODUCT_INFO:'',
              'show_on_listing' => true,
          ),
          'display_listing' => array(
              'label' => defined('TEXT_LISTING')?TEXT_LISTING:'',
              'show_on_listing' => true,
          ),
          'display_filter' => array(
              'label' => defined('TEXT_FILTER')?TEXT_FILTER:'',
              'show_on_listing' => true,
          ),
          'display_search' => array(
              'label' => defined('TEXT_SEARCH')?TEXT_SEARCH:'',
          ),
          'display_compare' => array(
              'label' => defined('TEXT_COMPARE')?TEXT_COMPARE:'',
          ),
          'products_groups' => array(
              'label' => defined('TEXT_PRODUCTS_GROUPS')?TEXT_PRODUCTS_GROUPS:'',
              'show_on_listing' => true,
          ),
      );
  }

  public function actionIndex() {
    $this->navigation[]       = array( 'link' => Yii::$app->urlManager->createUrl('properties/index'), 'title' => TEXT_PROPERTIES_TITLE );
    $this->view->headingTitle = TEXT_PROPERTIES_TITLE;

    $this->selectedMenu = array( 'catalog', 'properties' );

    $pID = Yii::$app->request->get('pID', 0);
    $parID = Yii::$app->request->get('parID', 0);

    $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['properties/edit', 'parID' => $parID]) . '" class="btn btn-primary"><i class="icon-file-text"></i>' . ucwords(TEXT_CREATE_NEW_PROPERTY) . '</a>';
    $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl(['properties/category', 'parID' => $parID]).'" class="btn btn-primary addprbtn"><i class="icon-folder-close-alt"></i>' . ucwords(TEXT_CREATE_NEW_CATEGORY) . '</a>';

    $this->view->PropertyTable = array(
      array(
        'title' => TABLE_HEADING_CATEGORIES_PROPERTIES,
        'not_important' => 0,
      ),
    );
    foreach ($this->getPossiblePropertiesFlags() as $propConfig){
        if ( !isset($propConfig['show_on_listing']) || !$propConfig['show_on_listing'] ) continue;
        $this->view->PropertyTable[] =  array(
            'title' => $propConfig['label'],
            'not_important' => 0,
        );
    }
    $this->view->PropertyTable[] =  array(
        'title' => TABLE_HEADING_PROPERTIES_TYPE,
        'not_important' => 0,
    );

    $messages = [];
    if (isset($_SESSION['messages'])) {
        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
    }
    if (!is_array($messages)) $messages = [];

    return $this->render('index', array('messages' => $messages, 'pID' => $pID, 'parID' => $parID));
  }

  public function actionList() {
    $languages_id = \Yii::$app->settings->get('languages_id');
    $draw = Yii::$app->request->get('draw', 1);
    $start = Yii::$app->request->get('start', 0);
    $length = Yii::$app->request->get('length', 10);
    $formFilter = Yii::$app->request->get('filter', array());
    parse_str($formFilter, $filter);

    $search = '';
    if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
      $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
      $search .= " and (properties_name like '%" . $keywords . "%' or properties_name_alt like '%" . $keywords . "%')";
    }

    $listingFlags = [];
    $selectColumns = '';
    foreach ($this->getPossiblePropertiesFlags() as $propField => $propConfig){
        if ( !isset($propConfig['show_on_listing']) || !$propConfig['show_on_listing'] ) continue;
        $listingFlags[$propField] = '';
        $selectColumns .= "p.{$propField}, ";
    }

    $current_page_number = ($start / $length) + 1;
    $responseList = array();

    if ($filter['parID'] > 0) {
      $parent_query = tep_db_query("select parent_id from " . TABLE_PROPERTIES . " where properties_id = '" . (int)$filter['parID'] . "'");
      if ($parent = tep_db_fetch_array($parent_query)) {
          $row = array(
              '<span class="parent_cats"><i class="icon-circle"></i><i class="icon-circle"></i><i class="icon-circle"></i></span><input class="cell_identify" type="hidden" value="' . $parent['parent_id'] . '"><input class="cell_type" type="hidden" value="parent">',
          );
          foreach( $listingFlags as $listingFlag ) $row[] = '';
          $row[] = '';
          $responseList[] = $row;
      }
    }

    $properties_query_raw = "select p.properties_id, p.properties_type, {$selectColumns} if(length(pd.properties_name_alt) > 0, pd.properties_name_alt, pd.properties_name) as properties_name, pd.properties_image, p.date_added, p.last_modified from " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_DESCRIPTION . " pd where p.properties_id = pd.properties_id and pd.language_id = '" . (int)$languages_id . "' and parent_id = '" . (int)$filter['parID'] . "' " . $search . " order by (p.properties_type = 'category') desc, p.sort_order, properties_name";
    $properties_split = new \splitPageResults($current_page_number, $length, $properties_query_raw, $properties_query_numrows);
    $properties_query = tep_db_query($properties_query_raw);
    while ($properties = tep_db_fetch_array($properties_query)) {
      if ($properties['properties_type'] == 'category') {
          $row = array(
              '<div class="handle_cat_list state-disabled"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name"><b>' . $properties['properties_name'] . '</b><input class="cell_identify" type="hidden" value="' . $properties['properties_id'] . '"><input class="cell_type" type="hidden" value="category"></div></div>',
          );
          foreach( $listingFlags as $listingFlag ) $row[] = '';
          $row[] = '';
      } else {
        $image = \common\helpers\Image::info_image($properties['properties_image'], $properties['properties_name'], 50, 50);
        $row = array(
          '<div class="handle_cat_list state-disabled"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="prod_name">' . (tep_not_null($image) && $image != TEXT_IMAGE_NONEXISTENT ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>')  . '<table class="wrapper"><tr><td><span class="prodNameC">' . $properties['properties_name'] . '</span></td></tr></table>' . '<input class="cell_identify" type="hidden" value="' . $properties['properties_id'] . '"><input class="cell_type" type="hidden" value="property"></div></div>',
        );
        foreach( array_keys($listingFlags) as $listingFlag ) {
            $row[] = '<input type="checkbox" class="js-listing_switcher" data-id="'.$properties['properties_id'].'" name="'.$listingFlag.'" '.($properties[$listingFlag]?' checked':'').'>';
        }
        $row[] = $this->properties_types_array[$properties['properties_type']];
      }
      $responseList[] = $row;
    }

    $response = array(
        'draw' => $draw,
        'recordsTotal' => $properties_query_numrows,
        'recordsFiltered' => $properties_query_numrows,
        'data' => $responseList
    );
    echo json_encode($response);
  }

  public function actionStatusactions() {
    $languages_id = \Yii::$app->settings->get('languages_id');

    $parent_id = Yii::$app->request->post('parent_id', 0);
    $properties_id = Yii::$app->request->post('properties_id', 0);
    $this->layout = false;

    if ($properties_id > 0) {
      $properties = tep_db_fetch_array(tep_db_query("select p.properties_id, p.properties_type, if(length(pd.properties_name_alt) > 0, pd.properties_name_alt, pd.properties_name) as properties_name, p.date_added, p.last_modified from " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_DESCRIPTION . " pd where p.properties_id = pd.properties_id and pd.language_id = '" . (int)$languages_id . "' and p.parent_id = '" . (int)$parent_id . "' and p.properties_id = '" . (int)$properties_id . "'"));
      $pInfo = new \objectInfo($properties, false);

      if ($pInfo->properties_id > 0) {
          if ($pInfo->properties_type=='category'){
              $children_properties = \common\helpers\Properties::get_properties_tree($pInfo->properties_id,'',[['id'=>$pInfo->properties_id]],false);
              $children_property_ids = \yii\helpers\ArrayHelper::getColumn($children_properties,'id');
              $stat = tep_db_fetch_array(tep_db_query(
                  "SELECT COUNT(DISTINCT products_id) AS assigned_to_products " .
                  "FROM " . TABLE_PROPERTIES_TO_PRODUCTS . " " .
                  "WHERE properties_id IN ('" . implode("','",$children_property_ids) . "') "
              ));
          }else {
              $stat = tep_db_fetch_array(tep_db_query(
                  "SELECT COUNT(DISTINCT products_id) AS assigned_to_products " .
                  "FROM " . TABLE_PROPERTIES_TO_PRODUCTS . " " .
                  "WHERE properties_id='" . intval($pInfo->properties_id) . "' "
              ));
          }
          echo '<div class="or_box_head">' . $pInfo->properties_name . '</div>';
          echo '<div class="col_desc">Used in '.$stat['assigned_to_products'].' products</div>';
          echo '<div class="btn-toolbar btn-toolbar-order">';
          echo '<a href="' . Yii::$app->urlManager->createUrl(['properties/' . ($pInfo->properties_type == 'category' ? 'category' : 'edit'), 'pID' => $properties_id]) . '"><button class="btn btn-edit btn-no-margin">' . IMAGE_EDIT . '</button></a>';
          if ($pInfo->properties_type != 'category') {
            echo '<button onclick="confirmMoveProperty(\'' . $pInfo->properties_id . '\')" class="btn">' . IMAGE_MOVE . '</button>';
          }
          echo '<button class="btn btn-delete btn-no-margin" onclick="propertyDeleteConfirm(' . $properties_id . ')">' . IMAGE_DELETE . '</button>';
          echo '</div>';
      }
    }
  }

    public function actionMoveConfirm() {
        $parent_id = \Yii::$app->request->post('parID', 0);
        $properties_id = \Yii::$app->request->post('properties_id', 0);
        $this->layout = false;
        if ($properties_id>0) {
            tep_db_query("update " . TABLE_PROPERTIES . " set parent_id = '" . (int)$parent_id . "'  where properties_id = '" . (int)$properties_id . "' and properties_type != 'category'");
        }
        return '';

    }
  
    public function actionMove() {
        $languages_id = \Yii::$app->settings->get('languages_id');
$properties_id = \Yii::$app->request->post('properties_id', 0);
        $this->layout = false;

        if ($properties_id > 0) {
            $properties = tep_db_fetch_array(tep_db_query("select p.properties_id, p.properties_type, if(length(pd.properties_name_alt) > 0, pd.properties_name_alt, pd.properties_name) as properties_name, p.date_added, p.last_modified from " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_DESCRIPTION . " pd where p.properties_id = pd.properties_id and pd.language_id = '" . (int) $languages_id . "' and p.properties_id = '" . (int) $properties_id . "'"));
            $pInfo = new \objectInfo($properties, false);

            if ($pInfo->properties_id > 0) {
                if ($pInfo->properties_type == 'category') {
                    return false;
                } else {
                    return $this->render('move.tpl', ['pInfo' => $pInfo]);
                }
            }
        }
    }

  function actionEdit()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');

    $this->navigation[]       = array( 'link' => Yii::$app->urlManager->createUrl('properties/edit'), 'title' => TEXT_PROPERTIES_TITLE );
    $this->view->headingTitle = TEXT_PROPERTIES_TITLE;

      $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#property_edit\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

    $this->selectedMenu = array( 'catalog', 'properties' );

    $this->view->usePopupMode = false;
    if (Yii::$app->request->isAjax) {
      $this->layout = false;
      $this->view->usePopupMode = true;
    }

    $properties_id = Yii::$app->request->get('pID', 0);
    $properties = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PROPERTIES . " where properties_id = '" . (int)$properties_id . "'"));
    if (!$properties) {
      $properties['parent_id'] = Yii::$app->request->get('parID', 0);
    }
    $pInfo = new \objectInfo($properties, false);

    $this->view->properties_types = $this->properties_types_array;

    $this->view->multi_choices[''] = TEXT_PLEASE_CHOOSE;
    $this->view->multi_choices['0'] = TEXT_SINGLE;
    $this->view->multi_choices['1'] = TEXT_MULTIPLE;

    $this->view->multi_lines[''] = TEXT_PLEASE_CHOOSE;
    $this->view->multi_lines['0'] = TEXT_SINGLE_LINE;
    $this->view->multi_lines['1'] = TEXT_MULTILINE;

    $this->view->decimals[''] = TEXT_PLEASE_CHOOSE;
    $this->view->decimals['0'] = '1234';
    $this->view->decimals['1'] = '1234.5';
    $this->view->decimals['2'] = '1234.56';
    $this->view->decimals['3'] = '1234.567';
    $this->view->decimals['4'] = '1234.5678';
    $this->view->decimals['5'] = '1234.56789';
    $this->view->decimals['9'] = '1234.56789xxx';

    $p = \common\models\Properties::find()->alias('p')->joinWith(['backendname'])
        ->select('properties_name, properties_name_alt, p.properties_id')
        ->andWhere('display_filter=1 and properties_type="text"')
        ->orderBy('properties_name')
        ->asArray()->indexBy('properties_id')->column()
        ;
    $p = [''=>TEXT_PLEASE_CHOOSE] + $p;

    $this->view->filter_by_property = $p;

    $default_language_id = $languages_id;
    $languages = \common\helpers\Language::get_languages();
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $languages[$i]['logo'] = $languages[$i]['image'];
      if ($languages[$i]['code'] == DEFAULT_LANGUAGE) {
        $default_language_id = $languages[$i]['id'];
      }
    }

    if (strlen(\common\helpers\Properties::get_properties_description($properties_id, $default_language_id)) > 0) {
      $this->view->additional_info = 1;
    }

    $this->view->properties_values = array();
    $this->view->properties_values_sorted_ids = array();
    $property_type_value = ArrayHelper::getValue($properties, 'properties_type');
    $properties_values_query = tep_db_query("select values_id, properties_id, language_id, values_text, values_number, values_number_upto, values_alt, values_seo_page_name as values_seo, values_color, values_image, values_prefix, values_postfix, maps_id, sort_order from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int)$properties_id . "' order by sort_order, " . ($property_type_value == 'number' || $property_type_value == 'interval' ? 'values_number' : 'values_text'));
    while($properties_values = tep_db_fetch_array($properties_values_query)) {
      if ($property_type_value == 'number' || $property_type_value == 'interval') {
        $properties_values['values'] = (float)number_format($properties_values['values_number'], $properties['decimals'],'.','');
        $properties_values['values_number_upto'] = (float)number_format($properties_values['values_number_upto'], $properties['decimals'],'.','');
      } else {
        $properties_values['values'] = $properties_values['values_text'];
      }

      /**
       * @var $imageMaps \common\extensions\ImageMaps\models\ImageMaps
       */
      if ($imageMaps = \common\helpers\Extensions::getModel('ImageMaps', 'ImageMaps')) {
        if ($properties_values['maps_id'] && !empty($imageMaps)) {
          if ($map = $imageMaps::findOne($properties_values['maps_id'])) {
            $properties_values['mapsId'] = $properties_values['maps_id'];
            $properties_values['mapsImage'] = $map->image;
            $properties_values['mapsTitle'] = $map->getTitle($properties_values['language_id']);
          }
        }
      }
      
      $this->view->properties_values[$properties_values['language_id']][$properties_values['values_id']] = $properties_values;

      if ($properties_values['language_id'] == $default_language_id) {
        $this->view->properties_values_sorted_ids[$properties_values['values_id']] = $properties_values['values_id'];
      }
    }

    return $this->render('edit.tpl', ['languages' => $languages, 'default_language' => DEFAULT_LANGUAGE, 'pInfo' => $pInfo]);
  }

  function actionCategory()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');

    $this->navigation[]       = array( 'link' => Yii::$app->urlManager->createUrl('properties/category'), 'title' => TEXT_PROPERTIES_TITLE );
    $this->view->headingTitle = TEXT_PROPERTIES_TITLE;

      $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#property_edit\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

    $this->selectedMenu = array( 'catalog', 'properties' );

    $this->view->usePopupMode = false;
    if (Yii::$app->request->isAjax) {
      $this->layout = false;
      $this->view->usePopupMode = true;
    }

    $properties_id = Yii::$app->request->get('pID', 0);
    $properties = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PROPERTIES . " where properties_id = '" . (int)$properties_id . "'"));
    if (!$properties) {
      $properties['parent_id'] = Yii::$app->request->get('parID', 0);
    }
    $pInfo = new \objectInfo($properties, false);

    $default_language_id = $languages_id;
    $languages = \common\helpers\Language::get_languages();
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      $languages[$i]['logo'] = $languages[$i]['image'];
      if ($languages[$i]['code'] == DEFAULT_LANGUAGE) {
        $default_language_id = $languages[$i]['id'];
      }
    }

    if (strlen(\common\helpers\Properties::get_properties_description($properties_id, $default_language_id)) > 0) {
      $this->view->additional_info = 1;
    }

    return $this->render('category.tpl', ['languages' => $languages, 'default_language' => DEFAULT_LANGUAGE, 'pInfo' => $pInfo]);
  }

  function actionSave()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');

    $parent_id = Yii::$app->request->post('parent_id', 0);
    $properties_id = Yii::$app->request->post('properties_id', 0);
    $properties_type = Yii::$app->request->post('properties_type', 'text');
    $multi_choice = Yii::$app->request->post('multi_choice', 0);
    $multi_line = Yii::$app->request->post('multi_line', 0);
    $filter_by_property = Yii::$app->request->post('filter_by_property', 0);
    $filter_steps = Yii::$app->request->post('filter_steps', 0);
    $decimals = Yii::$app->request->post('decimals', 0);
    $display_product = Yii::$app->request->post('display_product', 0);
    $display_listing = Yii::$app->request->post('display_listing', 0);
    $display_filter = Yii::$app->request->post('display_filter', 0);
    $display_search = Yii::$app->request->post('display_search', 0);
    $display_compare = Yii::$app->request->post('display_compare', 0);
    $display_as_image = Yii::$app->request->post('display_as_image', 0);
    $display_filter_as = Yii::$app->request->post('display_filter_as', '');
    $products_groups = Yii::$app->request->post('products_groups', 0);
    $extra_values = (int) \Yii::$app->request->post('extra_values', 0);
    $range_select = (int) \Yii::$app->request->post('range_select', 0);
    $same_all_languages = Yii::$app->request->post('same_all_languages', 0);
    $additional_info = tep_db_prepare_input(Yii::$app->request->post('additional_info', 0));
    $properties_name = tep_db_prepare_input(Yii::$app->request->post('properties_name', array()));
    $properties_name_alt = tep_db_prepare_input(Yii::$app->request->post('properties_name_alt', array()));
    $properties_description = tep_db_prepare_input(Yii::$app->request->post('properties_description', array()));
    $properties_seo_page_name = tep_db_prepare_input(Yii::$app->request->post('properties_seo_page_name', array()));
    $properties_image = Yii::$app->request->post('properties_image', array());
    $properties_image_loaded = Yii::$app->request->post('properties_image_loaded', array());
    $properties_image_delete = Yii::$app->request->post('properties_image_delete', array());
    $properties_units_title = tep_db_prepare_input(Yii::$app->request->post('properties_units_title', array()));
    $properties_color = tep_db_prepare_input(Yii::$app->request->post('properties_color', array()));

    $values = tep_db_prepare_input(Yii::$app->request->post('values', array()));
    $values_upto = tep_db_prepare_input(Yii::$app->request->post('values_upto', array()));
    $values_alt = tep_db_prepare_input(Yii::$app->request->post('values_alt', array()));
    $values_seo = tep_db_prepare_input(Yii::$app->request->post('values_seo', array()));
    $upload_docs = tep_db_prepare_input(Yii::$app->request->post('upload_docs', array()));
    $values_color = tep_db_prepare_input(Yii::$app->request->post('values_color', array()));
    $maps_id = tep_db_prepare_input(Yii::$app->request->post('maps_id', array()));
    $values_image = tep_db_prepare_input(Yii::$app->request->post('values_image', array()));
    $values_image_loaded = tep_db_prepare_input(Yii::$app->request->post('values_image_loaded', array()));
    $values_image_delete = tep_db_prepare_input(Yii::$app->request->post('values_image_delete', array()));
    $values_prefix = tep_db_prepare_input(Yii::$app->request->post('values_prefix', array()));
    $values_postfix = tep_db_prepare_input(Yii::$app->request->post('values_postfix', array()));
    
    $tmp_sort_order = tep_db_prepare_input(Yii::$app->request->post('sort_order', array()));
    $sort_order = array();
    if (is_array($tmp_sort_order)) {
      foreach ($tmp_sort_order as $lang => $value) {
        if (count($value)>1) {
          asort($value);
          $i = 1;
          foreach ($value as $key => $value) {
            $sort_order[$key][$lang] = $i++;
          }          
        }
      }
    }

    $sql_data_array = array('properties_type' => $properties_type,
                            'multi_choice' => $multi_choice,
                            'multi_line' => $multi_line,
                            'filter_by_property' => $filter_by_property,
                            'filter_steps' => $filter_steps,
                            'decimals' => $decimals,
                            'display_product' => $display_product,
                            'display_listing' => $display_listing,
                            'display_filter' => $display_filter,
                            'display_search' => $display_search,
                            'display_compare' => $display_compare,
                            'display_as_image' => $display_as_image,
                            'display_filter_as' => $display_filter_as,
                            'extra_values' => $extra_values,
                            'range_select' => $range_select,
                            'products_groups' => $products_groups,
    );

    if ($properties_id == 0) {
      $insert_sql_data = array('parent_id' => $parent_id, 'date_added' => 'now()');
      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
      tep_db_perform(TABLE_PROPERTIES, $sql_data_array);
      $properties_id = tep_db_insert_id();
    } else {
      $update_sql_data = array('last_modified' => 'now()');
      $sql_data_array = array_merge($sql_data_array, $update_sql_data);
      tep_db_perform(TABLE_PROPERTIES, $sql_data_array, 'update', "properties_id = '" . (int)$properties_id . "'");
    }

    $default_language_id = $languages_id;
    $languages = \common\helpers\Language::get_languages();
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      if ($languages[$i]['code'] == DEFAULT_LANGUAGE) {
        $default_language_id = $languages[$i]['id'];
      }
    }
    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
      if (trim($properties_name[$languages[$i]['id']] ?? null) == '' || $same_all_languages) $properties_name[$languages[$i]['id']] = $properties_name[$default_language_id] ?? null;
      if (trim($properties_name_alt[$languages[$i]['id']] ?? null) == '' || $same_all_languages) $properties_name_alt[$languages[$i]['id']] = $properties_name_alt[$default_language_id] ?? null;
      if (!$additional_info) $properties_description[$languages[$i]['id']] = '';
      elseif (trim($properties_description[$languages[$i]['id']] ?? null) == '' || $same_all_languages) $properties_description[$languages[$i]['id']] = $properties_description[$default_language_id];

      if (trim($properties_seo_page_name[$languages[$i]['id']] ?? null) == '') {
        $properties_seo_page_name[$languages[$i]['id']] = Seo::makeSlug($properties_name[$languages[$i]['id']]);
      }
      if (trim($properties_seo_page_name[$languages[$i]['id']] ?? null) == '') {
        $properties_seo_page_name[$languages[$i]['id']] = $properties_id;
      }
      if (trim($properties_seo_page_name[$languages[$i]['id']] ?? null) == '' || $same_all_languages) $properties_seo_page_name[$languages[$i]['id']] = $properties_seo_page_name[$default_language_id];

      if (trim($properties_units_title[$languages[$i]['id']] ?? null) == '' || $same_all_languages) $properties_units_title[$languages[$i]['id']] = $properties_units_title[$default_language_id] ?? null;
      if (tep_not_null($properties_units_title[$languages[$i]['id']])) {
        $check = tep_db_fetch_array(tep_db_query("select properties_units_id from " . TABLE_PROPERTIES_UNITS . " where properties_units_title = '" . tep_db_input($properties_units_title[$languages[$i]['id']]) . "'"));
        if (($check['properties_units_id']??null) > 0) {
          $properties_units_id = $check['properties_units_id'];
        } else {
          tep_db_perform(TABLE_PROPERTIES_UNITS, array('properties_units_title' => $properties_units_title[$languages[$i]['id']]));
          $properties_units_id = tep_db_insert_id();
        }
      }
      if (trim($properties_color[$languages[$i]['id']] ?? null) == '' || $same_all_languages) $properties_color[$languages[$i]['id']] = $properties_color[$default_language_id] ?? null;

      $sql_data_array = array('properties_name'        => $properties_name[$languages[$i]['id']],
                              'properties_name_alt'    => $properties_name_alt[$languages[$i]['id']],
                              'properties_description' => $properties_description[$languages[$i]['id']],
                              'properties_seo_page_name' => $properties_seo_page_name[$languages[$i]['id']],
                              'properties_units_id' => intval($properties_units_id ?? null),
                              'properties_color' => $properties_color[$languages[$i]['id']],
      );
      $check = tep_db_fetch_array(tep_db_query("select count(*) as properties_description_exists from " . TABLE_PROPERTIES_DESCRIPTION . " where properties_id = '" . (int)$properties_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'"));
      if ($check['properties_description_exists']) {
        tep_db_perform(TABLE_PROPERTIES_DESCRIPTION, $sql_data_array, 'update', "properties_id = '" . (int)$properties_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'" );
      } else {
        $insert_sql_data = array('properties_id' => $properties_id,
                                 'language_id' => $languages[$i]['id']);
        $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
        tep_db_perform(TABLE_PROPERTIES_DESCRIPTION, $sql_data_array);
      }

      if ((trim($properties_image_loaded[$languages[$i]['id']] ?? null) == '' || $same_all_languages) && trim($properties_image_loaded[$default_language_id] ?? null) != '') {
        $properties_image_loaded[$languages[$i]['id']] = $properties_image_loaded[$default_language_id];
      }
      if ((trim($properties_image_delete[$languages[$i]['id']] ?? null) == '' || $same_all_languages) && trim($properties_image_delete[$default_language_id] ?? null) != '') {
        $properties_image_delete[$languages[$i]['id']] = $properties_image_delete[$default_language_id];
      }
      if ((trim($properties_image[$languages[$i]['id']] ?? null) == '' || $same_all_languages) && trim($properties_image[$default_language_id] ?? null) != '') {
        $properties_image[$languages[$i]['id']] = $properties_image[$default_language_id];
      }

        $propertiesDescription = \common\models\PropertiesDescription::findOne([
            'properties_id' => $properties_id,
            'language_id' => $languages[$i]['id']
        ]);
        $propertiesDescription->properties_image = \common\helpers\Image::prepareSavingImage(
            $propertiesDescription->properties_image ?? '',
            $properties_image[$languages[$i]['id']],
            $properties_image_loaded[$languages[$i]['id']],
            'properties' . DIRECTORY_SEPARATOR . $properties_id,
            $properties_image_delete[$languages[$i]['id']]
        );
        $propertiesDescription->save(false);

    }

    $all_values_id = array();
    if (in_array($properties_type, array('text', 'number', 'interval', 'file'))) {
      foreach ($values as $val_id => $val) {
        if (trim($values[$val_id][$default_language_id]) == '' && trim($upload_docs[$val_id][$default_language_id]) == '') {
          continue; // Skip empty lines
        }
        if (strstr($val_id, 'new')) {
          $max_value = tep_db_fetch_array(tep_db_query("select max(values_id) + 1 as next_id from " . TABLE_PROPERTIES_VALUES));
          $values_id = $max_value['next_id'];
          if ( !($values_id > 0) ) $values_id = 1;
        } elseif ($val_id > 0) {
          $values_id = $val_id;
        } else {
          continue;
        }
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
// {{
          if ($properties_type == 'file') {
            if ((trim($upload_docs[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) && trim($upload_docs[$val_id][$default_language_id]) != '') {
              $upload_docs[$val_id][$languages[$i]['id']] = $upload_docs[$val_id][$default_language_id];
            }
            if ($upload_docs[$val_id][$languages[$i]['id']] != '') {
              $path = \Yii::getAlias('@webroot');
              $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
              $tmp_name = $path . $upload_docs[$val_id][$languages[$i]['id']];
              $new_name = DIR_FS_CATALOG_IMAGES . 'prop-' . $properties_id . '-' . $upload_docs[$val_id][$languages[$i]['id']];
              @copy($tmp_name, $new_name);
              @unlink($tmp_name);
              $values[$val_id][$languages[$i]['id']] = 'prop-' . $properties_id . '-' . $upload_docs[$val_id][$languages[$i]['id']];
            }
          }
// }}
          if (trim($values[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) $values[$val_id][$languages[$i]['id']] = $values[$val_id][$default_language_id];
          if ($properties_type == 'interval') {
            if (trim($values_upto[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) $values_upto[$val_id][$languages[$i]['id']] = $values_upto[$val_id][$default_language_id];
          } else {
            $values_upto[$val_id][$languages[$i]['id']] = '';
          }
          if (trim($values_alt[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) $values_alt[$val_id][$languages[$i]['id']] = $values_alt[$val_id][$default_language_id];
          
          if (trim($values_prefix[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) $values_prefix[$val_id][$languages[$i]['id']] = $values_prefix[$val_id][$default_language_id];
          if (trim($values_postfix[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) $values_postfix[$val_id][$languages[$i]['id']] = $values_postfix[$val_id][$default_language_id];

          if (trim($values_seo[$val_id][$languages[$i]['id']]) == '') {
            $values_seo[$val_id][$languages[$i]['id']] = Seo::makeSlug(tep_not_null($values_alt[$val_id][$languages[$i]['id']]) ? $values_alt[$val_id][$languages[$i]['id']] : $values[$val_id][$languages[$i]['id']]);
          }
          if (trim($values_seo[$val_id][$languages[$i]['id']]) == '') {
            $values_seo[$val_id][$languages[$i]['id']] = $values_id;
          }
          if (trim($values_seo[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) $values_seo[$val_id][$languages[$i]['id']] = $values_seo[$val_id][$default_language_id];

          if (trim($values_color[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) $values_color[$val_id][$languages[$i]['id']] = $values_color[$val_id][$default_language_id];
          if (trim($maps_id[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) $maps_id[$val_id][$languages[$i]['id']] = $maps_id[$val_id][$default_language_id];
          if (trim($sort_order[$val_id][$languages[$i]['id']]) == '' || $same_all_languages) $sort_order[$val_id][$languages[$i]['id']] = $sort_order[$val_id][$default_language_id];
      
          $sql_data_array = array('values_text'          => $values[$val_id][$languages[$i]['id']],
                                  'values_number'        => round((float)$values[$val_id][$languages[$i]['id']], (int)$decimals),
                                  'values_number_upto'   => round((float)$values_upto[$val_id][$languages[$i]['id']], (int)$decimals),
                                  'values_alt'           => $values_alt[$val_id][$languages[$i]['id']],
                                  'values_seo_page_name' => $values_seo[$val_id][$languages[$i]['id']],
                                  'values_color' => $values_color[$val_id][$languages[$i]['id']],
                                  'sort_order' => $sort_order[$val_id][$languages[$i]['id']],
                                  'maps_id' => $maps_id[$val_id][$languages[$i]['id']],
                                  'values_prefix'           => $values_prefix[$val_id][$languages[$i]['id']],
                                  'values_postfix'           => $values_postfix[$val_id][$languages[$i]['id']],
          );

            $propertiesValues = \common\models\PropertiesValues::findOne([
                'values_id' => $values_id,
                'properties_id' => $properties_id,
                'language_id' => $languages[$i]['id'],
            ]);
            $sql_data_array['values_image'] = \common\helpers\Image::prepareSavingImage(
                $propertiesValues->values_image ?? '',
                $values_image[$val_id][$languages[$i]['id']] ?? '',
                $values_image_loaded[$val_id][$languages[$i]['id']] ?? '',
                'properties' . DIRECTORY_SEPARATOR . $properties_id,
                $values_image_delete[$val_id][$languages[$i]['id']] ?? ''
            );
          
          $check = tep_db_fetch_array(tep_db_query("select count(*) as properties_values_exists from " . TABLE_PROPERTIES_VALUES . " where values_id = '" . (int)$values_id . "' and properties_id = '" . (int)$properties_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'"));
          if ($check['properties_values_exists']) {
            tep_db_perform(TABLE_PROPERTIES_VALUES, $sql_data_array, 'update', "values_id = '" . (int)$values_id . "' and properties_id = '" . (int)$properties_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'" );
          } else {
            $insert_sql_data = array('values_id' => $values_id,
                                     'properties_id' => $properties_id,
                                     'language_id' => $languages[$i]['id']);
            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
            tep_db_perform(TABLE_PROPERTIES_VALUES, $sql_data_array);
          }
        }
        if ($same_all_languages) {
            $check_image = tep_db_fetch_array(tep_db_query("SELECT values_image FROM ".TABLE_PROPERTIES_VALUES." WHERE values_id = '" . (int)$values_id . "' and properties_id = '" . (int)$properties_id . "' and language_id = '" . (int)$default_language_id . "' limit 1"));
            tep_db_query("UPDATE ".TABLE_PROPERTIES_VALUES." SET values_image='".$check_image['values_image']."' WHERE values_id = '" . (int)$values_id . "' and properties_id = '" . (int)$properties_id . "' and language_id != '" . (int)$default_language_id . "' ");
        }
        $all_values_id[] = $values_id;
      }
    }
    $properties_values_query = tep_db_query("select values_id from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int)$properties_id . "' and values_id not in ('" . implode("','", $all_values_id) . "')");
    while ($properties_values = tep_db_fetch_array($properties_values_query)) {
      tep_db_query("delete from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int)$properties_id . "' and values_id = '" . (int)$properties_values['values_id'] . "'");
      tep_db_query("delete from " . TABLE_PROPERTIES_TO_PRODUCTS . " where properties_id = '" . (int)$properties_id . "' and values_id = '" . (int)$properties_values['values_id'] . "'");
    }
    \common\helpers\Properties::check_filter_table($properties_id);
    \common\helpers\ProductsGroupSortCache::update();

    if (Yii::$app->request->isAjax) {
      $this->layout = false;
      $this->view->properties_tree = \common\helpers\Properties::get_properties_tree('0', '&nbsp;&nbsp;&nbsp;&nbsp;', '', false);
      return $this->render('properties_box.tpl', ['properties_id' => $properties_id]);
    } else {
      if ($properties_type == 'category') {
        return $this->redirect(Yii::$app->urlManager->createUrl(['properties/category', 'pID' => $properties_id]));
      } else {
        return $this->redirect(Yii::$app->urlManager->createUrl(['properties/edit', 'pID' => $properties_id]));
      }
    }
  }

  public function actionSortOrder()
  {
    $categories_sorted = Yii::$app->request->post('category', array());
    foreach ($categories_sorted as $sort_order => $properties_id) {
      tep_db_query("update " . TABLE_PROPERTIES . " set sort_order = '" . (int)$sort_order . "' where properties_id = '" . (int)$properties_id . "'");
    }
    $properties_sorted = Yii::$app->request->post('property', array());
    foreach ($properties_sorted as $sort_order => $properties_id) {
      tep_db_query("update " . TABLE_PROPERTIES . " set sort_order = '" . (int)($sort_order + count($categories_sorted)) . "' where properties_id = '" . (int)$properties_id . "'");
    }
    \common\helpers\ProductsGroupSortCache::update();
  }

  public function actionConfirmdelete() {
    $languages_id = \Yii::$app->settings->get('languages_id');

    $this->layout = false;

    $properties_id = Yii::$app->request->post('properties_id');

    if ($properties_id > 0) {
      $properties = tep_db_fetch_array(tep_db_query("select p.properties_id, if(length(pd.properties_name_alt) > 0, pd.properties_name_alt, pd.properties_name) as properties_name, p.date_added, p.last_modified from " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_DESCRIPTION . " pd where p.properties_id = pd.properties_id and pd.language_id = '" . (int)$languages_id . "' and p.properties_id = '" . (int)$properties_id . "'"));
      $pInfo = new \objectInfo($properties, false);

      echo tep_draw_form('properties', FILENAME_PROPERTIES, \common\helpers\Output::get_all_get_params(array('pID', 'action')) . 'dID=' . $pInfo->properties_id . '&action=deleteconfirm', 'post', 'id="item_delete" onSubmit="return propertyDelete();"');

      echo '<div class="or_box_head">' . $pInfo->properties_name . '</div>';
      echo TEXT_DELETE_INTRO . '<br>';
      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo '<button type="submit" class="btn btn-primary btn-no-margin">' . IMAGE_CONFIRM . '</button>';
      echo '<button class="btn btn-cancel" onClick="return resetStatement(' . (int)$properties_id . ')">' . IMAGE_CANCEL . '</button>';      

      echo tep_draw_hidden_field('properties_id', $properties_id);
      echo '</div></form>';
    }
  }

  public function actionDelete() {
    global $language;

    $properties_id = Yii::$app->request->post('properties_id', 0);
    if ($properties_id > 0) {
      \common\helpers\Properties::remove_property($properties_id);
      echo 'reset';
    }
  }

  /**
  * Autocomplette
  */
  public function actionUnits() {
    $term = tep_db_prepare_input(Yii::$app->request->get('term'));

    $search = "1";
    if (!empty($term)) {
      $search = "properties_units_title like '%" . tep_db_input($term) . "%'";
    }

    $response = [];
    $units_query = tep_db_query("select properties_units_title from " . TABLE_PROPERTIES_UNITS . " where " . $search . " group by properties_units_title order by properties_units_title");
    while ($units = tep_db_fetch_array($units_query)) {
      $response[] = $units['properties_units_title'];
    }
    echo json_encode($response);
  }

    public function actionUpdatePropertyFlag()
    {
        $this->layout = false;
        $propertyId = Yii::$app->request->post('id',0);
        $propertyFlagName = Yii::$app->request->post('name',0);
        $allowed_flag_names = array('display_product', 'display_listing', 'display_filter', 'display_search', 'display_compare','products_groups');
        $flag = !!Yii::$app->request->post('flag',0)?1:0;
        if ( $propertyId && in_array($propertyFlagName,$allowed_flag_names) ) {
            tep_db_query(
                "UPDATE ".TABLE_PROPERTIES." ".
                "SET {$propertyFlagName}='{$flag}', last_modified=NOW() ".
                "WHERE properties_id='".intval($propertyId)."' "
            );
            \common\helpers\Properties::check_filter_table($propertyId);
        }
        echo 'ok';
    }

}