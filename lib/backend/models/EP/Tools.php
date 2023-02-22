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

namespace backend\models\EP;

use common\helpers\Seo;
use common\models\Zones;
use yii\helpers\ArrayHelper;

class Tools
{
    public $languages_id;
    private $categories_id_to_name = array();
    private $categories_path_to_id = array();
    private $new_category_added = false;

    private $lookupOptionValuesId = [];

    protected $warehouse_location_list = [];

    function __construct() {
        $this->languages_id = \common\classes\language::defaultId();
        $languages_id = (int)\Yii::$app->settings->get('languages_id');
        if ($languages_id > 0) {
            $this->languages_id = $languages_id;
        }
    }

    public static function getInstance()
    {
        static $obj = false;
        if ( $obj===false ){
            $obj = new self();
        }
        return $obj;
    }
    
    function tep_get_full_listing_products_for_categories($parent_id = '0', $products_array = '')
    {
      $languages_id = $this->languages_id;
      if (!is_array($products_array)) $products_array = array();
      $products_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$parent_id . "' and pd.language_id = '" . (int)$languages_id . "' order by p.sort_order, p.products_id, pd.products_name");
      while ($products = tep_db_fetch_array($products_query)) {
        $products_array[$products['products_id']] = $products['products_id'];
      }

      $categories_query = tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and c.parent_id = '" . (int)$parent_id . "' order by c.sort_order, cd.categories_name");
      while ($categories = tep_db_fetch_array($categories_query)) {
        $products_array = $this->tep_get_full_listing_products_for_categories($categories['categories_id'], $products_array);
      }
      return $products_array;
    }

    function tep_get_categories_full_path( $categories_id )
    {
      $languages_id = $this->languages_id;

      if ( !isset($this->categories_id_to_name[(int)$categories_id]) ) {
        $this->categories_id_to_name[(int)$categories_id] = tep_db_fetch_array( tep_db_query(
          "SELECT c.categories_id, cd.categories_name, c.parent_id ".
          "FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
          "WHERE c.categories_id = cd.categories_id and c.categories_id = '" . $categories_id . "' AND cd.affiliate_id = 0 AND cd.language_id = " . (int)$languages_id
        ) );
      }
      $sql2 = $this->categories_id_to_name[(int)$categories_id];

      if( ($sql2['parent_id'] ?? null) > 0 ) {
        return $this->tep_get_categories_full_path( (int) $sql2['parent_id'] ) . ';' . $sql2['categories_name'];
      } else {
        return $sql2['categories_name'] ?? null;
      }
    }

    private function lookupCategory($lookup_parent_id, $category_name )
    {
      $languages_id = $this->languages_id;

      $category_ids = array();

      $lookup_names = array();
      $lookup_names[] = $category_name;
      $lookup_names[] = $this->_cleanCatName($category_name,1);
      $lookup_names[] = $this->_cleanCatName($category_name,2);
      $lookup_names[] = $this->_cleanCatName($category_name);

      $db_lookup_r = tep_db_query(
        "SELECT c.categories_id ".
        "FROM ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd ".
        "WHERE cd.categories_id = c.categories_id AND cd.language_id = '".$languages_id."' AND cd.affiliate_id=0 ".
        " AND c.parent_id='".(int)$lookup_parent_id."' ".
        " AND cd.categories_name IN ('".implode("','",array_map('tep_db_input',array_unique($lookup_names)))."') ".
        ""
      );
      if ( tep_db_num_rows($db_lookup_r)>0 ) {
        while( $_lookup = tep_db_fetch_array($db_lookup_r) ) {
          $category_ids[] = (int)$_lookup['categories_id'];
        }
      }
      return $category_ids;
    }

    function tep_get_products_groups_by_name( $name, $products_groups_id=0, $languages_id = 0, $messages=false )
    {

      if (! isset($this->products_groups_name_to_id[$name]) ) {
        if ($languages_id==0) {
          $languages_id = $this->languages_id;
        }
        $name = $this->_cleanCatName($name, 2);
        if ( strlen($name)==0 ) return 0;
        
        $r = tep_db_query("select * from " . TABLE_PRODUCTS_GROUPS . " where products_groups_name = '" . tep_db_input($name) . "' "
                        . " order by if(language_id=" . (int)$languages_id . ", 0, 1) limit 1 ");

        if ( tep_db_num_rows($r)>0 ) {
          $d = tep_db_fetch_array($r);
          $products_groups_id = $d['products_groups_id'];

        } elseif ($products_groups_id>0) {
          // update name on current language
            tep_db_perform(TABLE_PRODUCTS_GROUPS,array(
              'products_groups_name' => $name
            ), 'update', " language_id='" . (int)$languages_id . "' and products_groups_id=' " . (int)$products_groups_id . "'");

        } else {
          // insert
            tep_db_perform(TABLE_PRODUCTS_GROUPS,array(
              'date_added' => 'now()',
              'language_id' => (int)$languages_id,
              'products_groups_name' => $name
            ));
            $products_groups_id = tep_db_insert_id();
            tep_db_query(
              "INSERT ignore INTO ".TABLE_PRODUCTS_GROUPS." ".
              "(products_groups_id, language_id, products_groups_name) ".
              " SELECT '{$products_groups_id}', languages_id, '" . tep_db_input($name)."' FROM ".TABLE_LANGUAGES." where languages_id<>'" . (int)$languages_id . "' "
            );
        }

        $this->products_groups_name_to_id[$name] = $products_groups_id;

      }

      return $this->products_groups_name_to_id[$name];

    }

    function tep_get_categories_by_name( $categories_path, $messages=false, $allowCreateCategory=true )
    {
      $languages_id = $this->languages_id;
      if ( isset($this->categories_path_to_id[$categories_path]) ) {
        return $this->categories_path_to_id[$categories_path];
      }
      $categories_array = explode(';',$categories_path);

      $category_id = false;
      // skip empty
      if ( empty($categories_path) ) {
        return $category_id;
      }
      $lookup_parent_id = 0;
      $_track_path_original = '';
      $_track_path_trimmed = '';
      foreach( $categories_array as $idx=>$category_name ){
        $category_name_trimmed = $this->_cleanCatName($category_name);
        $_track_path_original .= (empty($_track_path_original)?'':';').$category_name;
        $_track_path_trimmed .= (empty($_track_path_trimmed)?'':';').$category_name_trimmed;

        $found_categories = $this->lookupCategory((int)$lookup_parent_id,$category_name);
        $count_found_categories = count($found_categories);

        if ( $count_found_categories==1 ) {
          $category_id = current($found_categories);
        }elseif($count_found_categories==0){
          if ( !$allowCreateCategory ){
              return false;
          }
          $category_seo_name = Seo::makeSlug($category_name);

          // create
          tep_db_perform(TABLE_CATEGORIES,array(
            'parent_id' => $lookup_parent_id,
            'date_added' => 'now()',
            'categories_status' => 0,
            'categories_seo_page_name' => ''
          ));
          $category_id = tep_db_insert_id();

          $check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_seo_page_name='".tep_db_input($category_seo_name)."'"));
          if ( $check['c']>0 ) {
            $category_seo_name .= '-'.$category_id;
          }

          $category_name_create = $this->_cleanCatName($category_name,2);

          tep_db_query(
            "INSERT INTO ".TABLE_CATEGORIES_DESCRIPTION." ".
            "(categories_id, language_id, categories_name, categories_seo_page_name) ".
            " SELECT '{$category_id}', languages_id, '".tep_db_input($category_name_create)."', '".tep_db_input($category_seo_name)."' FROM ".TABLE_LANGUAGES." "
          );
          $this->new_category_added = true;

          /** @var \common\extensions\UserGroupsRestrictions\UserGroupsRestrictions $ext */
          if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
              if ( $groupService = $ext::getGroupsService() ){
                  $groupService->addCategoryToAllGroups($category_id);
              }
          }

          // if there is only 1 platform then autoassign new category to it
          $platforms = \common\classes\platform::getList(false);
          if (count($platforms)==1) {
            tep_db_query(
              "INSERT INTO ".TABLE_PLATFORMS_CATEGORIES." ".
              "(categories_id, platform_id) ".
              " values ('" . (int)$category_id . "','" . (int)$platforms[0]['id'] . "') "
            );
          }

          if ( is_object($messages) && is_a($messages,'backend\models\EP\Messages') ) {
            /**
             * @var $messages Messages
             */
            $messages->info('New category "'.$category_name_create.'" has been added');
          }

        }else{
          // 1 child deep lookup
          if ( isset($categories_array[$idx+1]) ) {
            $current_level_possible_cat_ids = array();
            foreach ($found_categories as $alter_parent) {
              $found_next_level = $this->lookupCategory($alter_parent,$categories_array[$idx+1]);
              if ( count($found_next_level)==1 ) {
                $current_level_possible_cat_ids[] = $alter_parent;
              }
            }
            if ( count($current_level_possible_cat_ids)==1 ) {
              $category_id = current($current_level_possible_cat_ids);
            }else{
              if (is_object($messages) && is_a($messages, 'backend\models\EP\Messages')) {
                /**
                 * @var $messages Messages
                 */
                $messages->info('Found multiple "' . $_track_path_original . '"');
              }
            }
          }else {
            if (is_object($messages) && is_a($messages, 'backend\models\EP\Messages')) {
              /**
               * @var $messages Messages
               */
              $messages->info('Found multiple "' . $_track_path_original . '"');
            }
          }
        }
        $lookup_parent_id = $category_id;

        $this->categories_path_to_id[$_track_path_original] = $category_id;
        if ( $_track_path_original!=$_track_path_trimmed ) {
          $this->categories_path_to_id[$_track_path_trimmed] = $category_id;
        }
      }

      return $category_id;
    }

    private function _cleanCatName($name, $clean_level=10){
      $name = trim($name);
      if ( $clean_level>1 && strpos($name,'  ')!==false ) $name = preg_replace('/\s{2,}/',' ',$name);
      static $mb_str = null;
      if ( $mb_str===null ) $mb_str = function_exists('mb_strtolower');
      if ( $clean_level>2 ) {
        if ( $mb_str ) {
          $name = mb_strtolower($name);
        }else{
          $name = strtolower($name);
        }
      }
      return $name;
    }

    public function done($what){
      // products_import, categories_import, attributes_import, properties_import, properties_settings_import
      if ( $what=='products_import' || $what=='categories_import' || $what=='products_to_categories_import' ){
        if ( true || $this->new_category_added) {
          \common\helpers\Categories::update_categories();
        }
        echo '<script type="text/javascript">if (typeof window.parent.refreshFilterContent==\'function\') window.parent.refreshFilterContent();</script>';
      }elseif( $what=='properties_import' || $what=='properties_settings_import' ){
        echo '<script type="text/javascript">if (typeof window.parent.refreshFilterContent==\'function\') window.parent.refreshFilterContent();</script>';
      }
    }

    public function get_brand_by_name($brand_name){
      if ( empty($brand_name) ) return 'null';
      static $processed = array();
      if ( !isset($processed[$brand_name]) ) {
        $processed[$brand_name] = 'null';
        $get_brand_r = tep_db_query(
          "SELECT manufacturers_id ".
          "FROM ".TABLE_MANUFACTURERS." WHERE manufacturers_name IN('".tep_db_input($brand_name)."', '".tep_db_input(trim($brand_name))."') ".
          "ORDER BY IF(manufacturers_name='".tep_db_input($brand_name)."',0,1) ".
          "LIMIT 1"
        );
        if ( tep_db_num_rows($get_brand_r)>0 ) {
          $get_brand = tep_db_fetch_array($get_brand_r);
          $processed[$brand_name] = $get_brand['manufacturers_id'];
        }else{
          tep_db_perform(TABLE_MANUFACTURERS, array(
            'manufacturers_name' => trim($brand_name),
            'date_added' => 'now()',
          ));
          $processed[$brand_name] = tep_db_insert_id();
          tep_db_query("INSERT INTO ".TABLE_MANUFACTURERS_INFO." (manufacturers_id, languages_id) SELECT '{$processed[$brand_name]}', languages_id FROM ".TABLE_LANGUAGES." ");
        }
      }
      return $processed[$brand_name];
    }

    public function getXSellTypeId( $xsellType )
    {
        static $cached = [];
        if  (isset($cached[$xsellType])){
            return $cached[$xsellType];
        }
        $cached[$xsellType] = 0;

        $get_type_id_r = tep_db_query(
            "SELECT xsell_type_id ".
            "FROM ".TABLE_PRODUCTS_XSELL_TYPE." ".
            "WHERE xsell_type_name='".tep_db_input($xsellType)."' ".
            "LIMIT 1"
        );
        if ( tep_db_num_rows($get_type_id_r)>0 ) {
            $_get_type_id = tep_db_fetch_array($get_type_id_r);
            $cached[$xsellType] = $_get_type_id['xsell_type_id'];
        }else{
            $get_max_id = tep_db_fetch_array(tep_db_query("SELECT MAX(xsell_type_id) AS max_id FROM ".TABLE_PRODUCTS_XSELL_TYPE));
            $xsell_type_id = intval($get_max_id['max_id'])+1;
            tep_db_query(
                "INSERT INTO ".TABLE_PRODUCTS_XSELL_TYPE." (xsell_type_id, language_id, xsell_type_name) ".
                "SELECT {$xsell_type_id}, languages_id, '".tep_db_input($xsellType)."' FROM ".TABLE_LANGUAGES
            );
            $cached[$xsellType] = $xsell_type_id;
        }

        return $cached[$xsellType];
    }

    public function getXSellTypeName( $xsellTypeId )
    {
        static $cached = [];
        if  (isset($cached[$xsellTypeId])){
            return $cached[$xsellTypeId];
        }
        $cached[$xsellTypeId] = '';

        $get_type_name_r = tep_db_query(
            "SELECT xsell_type_name ".
            "FROM ".TABLE_PRODUCTS_XSELL_TYPE." ".
            "WHERE xsell_type_id='".tep_db_input($xsellTypeId)."' AND language_id='".$this->languages_id."' ".
            "LIMIT 1"
        );
        if ( tep_db_num_rows($get_type_name_r)>0 ) {
            $_get_type_name = tep_db_fetch_array($get_type_name_r);
            $cached[$xsellTypeId] = $_get_type_name['xsell_type_name'];
        }

        return $cached[$xsellTypeId];
    }

    public function get_option_by_name($option_names){
      if ( empty($option_names) || (is_array($option_names) && trim(implode('',$option_names))=='') ) return 0;
      if ( !is_array($option_names) ) $option_names = array( intval($this->languages_id) => $option_names);
      $option_id = false;
      foreach($option_names as $_lang=> $_lookup_name ) {
        if ( $option_id ) {
          //update
          tep_db_perform(TABLE_PRODUCTS_OPTIONS, array(
            'products_options_name' => trim($_lookup_name),
          ),'update', "products_options_id='{$option_id}' AND language_id='".(int)$_lang."'");
          continue;
        }
        $get_id_r = tep_db_query(
          "SELECT products_options_id ".
          "FROM ".TABLE_PRODUCTS_OPTIONS." ".
          "WHERE language_id='".(int)$_lang."' AND (products_options_name='".tep_db_input($_lookup_name)."' OR products_options_name='".tep_db_input(trim($_lookup_name))."' )"
        );
        if ( tep_db_num_rows($get_id_r)>0 ) {
          $get_id = tep_db_fetch_array($get_id_r);
          $option_id = $get_id['products_options_id'];
        }else{
          $_new_option_id = tep_db_fetch_array(tep_db_query(
            "SELECT MAX(products_options_id) AS current_max FROM ".TABLE_PRODUCTS_OPTIONS." "
          ));
          $option_id = (is_array($_new_option_id)?($_new_option_id['current_max']+1):1);

          $_new_order = tep_db_fetch_array(tep_db_query(
            "SELECT MAX(products_options_sort_order) AS current_max FROM ".TABLE_PRODUCTS_OPTIONS." "
          ));
          $sort_order = (is_array($_new_order)?($_new_order['current_max']+1):1);

          tep_db_query(
            "INSERT INTO ".TABLE_PRODUCTS_OPTIONS." (products_options_id, language_id, products_options_name, products_options_sort_order) ".
            "SELECT '{$option_id}', languages_id, '".tep_db_input(trim($_lookup_name))."', '{$sort_order}' FROM ".TABLE_LANGUAGES
          );
        }
      }
      return $option_id;
    }

    public function lookupOptionValueId($optionId, $optionName)
    {
        $key = intval($optionId).'^^'.strval($optionName);

        if (!isset($this->lookupOptionValuesId[$key])) {
            $this->lookupOptionValuesId[$key] = false;
            $get_id_r = tep_db_query(
                "SELECT pov.products_options_values_id ".
                "FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." pov, ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." pov2po ".
                "WHERE pov2po.products_options_id='".(int)$optionId."' AND pov2po.products_options_values_id=pov.products_options_values_id ".
                " AND (pov.products_options_values_name='".tep_db_input($optionName)."' OR pov.products_options_values_name='".tep_db_input(trim($optionName))."' ) ".
                "LIMIT 1"
            );
            if( tep_db_num_rows($get_id_r)>0 ) {
                $get_id = tep_db_fetch_array($get_id_r);
                $this->lookupOptionValuesId[$key] = (int)$get_id['products_options_values_id'];
            }
        }
        return $this->lookupOptionValuesId[$key];
    }

    public function get_option_value_by_name($option_id, $value_names){
        if ( empty($value_names) || (is_array($value_names) && trim(implode('',$value_names))=='') ) return 0;
        if ( !is_array($value_names) ) $value_names = array( intval($this->languages_id) => $value_names);
        $option_value_id = false;

        foreach($value_names as $_lang=> $_lookup_name ) {
            if ( $option_value_id ) {
              //update
              tep_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES, array(
                'products_options_values_name' => trim($_lookup_name),
              ),'update', "products_options_values_id='{$option_value_id}' AND language_id='".(int)$_lang."'");
              continue;
            }
            $get_id_r = tep_db_query(
              "SELECT pov.products_options_values_id ".
              "FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." pov, ".TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS." pov2po ".
              "WHERE pov2po.products_options_id='".$option_id."' AND pov2po.products_options_values_id=pov.products_options_values_id ".
              " AND pov.language_id='".(int)$_lang."' AND (pov.products_options_values_name='".tep_db_input($_lookup_name)."' OR pov.products_options_values_name='".tep_db_input(trim($_lookup_name))."' )"
            );
            if ( tep_db_num_rows($get_id_r)>0 ) {
                $get_id = tep_db_fetch_array($get_id_r);
                $option_value_id = $get_id['products_options_values_id'];
            }else{
                $_new_option_id = tep_db_fetch_array(tep_db_query(
                  "SELECT MAX(products_options_values_id) AS current_max FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." "
                ));
                $option_value_id = (is_array($_new_option_id)?($_new_option_id['current_max']+1):1);

                $_new_order = tep_db_fetch_array(tep_db_query(
                  "SELECT MAX(products_options_values_sort_order) AS current_max FROM ".TABLE_PRODUCTS_OPTIONS_VALUES." "
                ));
                $sort_order = (is_array($_new_order)?($_new_order['current_max']+1):1);

                tep_db_query(
                  "INSERT INTO ".TABLE_PRODUCTS_OPTIONS_VALUES." (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order) ".
                  "SELECT '{$option_value_id}', languages_id, '".tep_db_input(trim($_lookup_name))."', '{$sort_order}' FROM ".TABLE_LANGUAGES
                );
                tep_db_perform(TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS,array(
                  'products_options_id' => $option_id,
                  'products_options_values_id' => $option_value_id,
                ));
                $this->lookupOptionValuesId = [];
            }
        }

        return $option_value_id;
    }

    public function get_option_value_name($option_value_id, $language_id)
    {
        $key = (int)$option_value_id.'^'.(int)$language_id;
        static $_cached = array();
        if ( !isset($_cached[$key]) ) {
            $get_name_r = tep_db_query(
              "SELECT products_options_values_name AS name " .
              "FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " " .
              "WHERE products_options_values_id='" . (int)$option_value_id . "' AND language_id='" . (int)$language_id . "'"
            );
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $_name = tep_db_fetch_array($get_name_r);
                $_cached[$key] = $_name['name'];
            }
        }
        return $_cached[$key];
    }

    public function is_option_virtual($option_id)
    {
        return \common\helpers\Attributes::is_virtual_option($option_id);
    }

    public function get_option_name($option_id, $language_id)
    {
        $key = (int)$option_id.'^'.(int)$language_id;
        static $_cached = array();
        if ( !isset($_cached[$key]) ) {
            $get_name_r = tep_db_query(
                "SELECT products_options_name AS name " .
                "FROM " . TABLE_PRODUCTS_OPTIONS . " " .
                "WHERE products_options_id='" . (int)$option_id . "' AND language_id='" . (int)$language_id . "'"
            );
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $_name = tep_db_fetch_array($get_name_r);
                $_cached[$key] = $_name['name'];
            }
        }
        return $_cached[$key];
    }

    public function getStockIndication($id)
    {
        static $cached = [];
        if ( !isset($cached[(int)$id]) ) {
            $cached[(int)$id] = '';
            $get_text_r = tep_db_query(
                "SELECT sit.stock_indication_text " .
                "FROM " . TABLE_PRODUCTS_STOCK_INDICATION . " si " .
                " INNER JOIN " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " sit ON sit.stock_indication_id=si.stock_indication_id AND sit.language_id='" . $this->languages_id . "' " .
                "WHERE si.stock_indication_id='" . (int)$id . "'"
            );
            if ( tep_db_num_rows($get_text_r)>0 ){
                $get_text = tep_db_fetch_array($get_text_r);
                $cached[(int)$id] = $get_text['stock_indication_text'];
            }
        }
        return $cached[(int)$id];
    }

    public function lookupStockIndicationId($text)
    {
        static $cached = [];
        if (!isset($cached[$text])) {
            $cached[$text] = 0;
            $lookup_id_r = tep_db_query(
                "SELECT DISTINCT si.stock_indication_id " .
                "FROM " . TABLE_PRODUCTS_STOCK_INDICATION . " si " .
                " INNER JOIN " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " sit ON sit.stock_indication_id=si.stock_indication_id " .
                "WHERE (sit.stock_indication_text='" . tep_db_input($text) . "' OR sit.stock_indication_text='" . tep_db_input(trim($text)) . "') ".
                "LIMIT 1"
            );
            if ( tep_db_num_rows($lookup_id_r)>0 ){
                $get_id = tep_db_fetch_array($lookup_id_r);
                $cached[$text] = $get_id['stock_indication_id'];
            }

        }
        return $cached[$text];
    }

    public function getStockDeliveryTerms($id)
    {
        static $cached = [];
        if ( !isset($cached[(int)$id]) ) {
            $cached[(int)$id] = '';
            $get_text_r = tep_db_query(
                "SELECT sit.stock_delivery_terms_text " .
                "FROM " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " si " .
                " INNER JOIN " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " sit ON sit.stock_delivery_terms_id=si.stock_delivery_terms_id AND sit.language_id='" . $this->languages_id . "' " .
                "WHERE si.stock_delivery_terms_id='" . (int)$id . "'"
            );
            if ( tep_db_num_rows($get_text_r)>0 ){
                $get_text = tep_db_fetch_array($get_text_r);
                $cached[(int)$id] = $get_text['stock_delivery_terms_text'];
            }
        }
        return $cached[(int)$id];
    }

    public function lookupStockDeliveryTermId($text)
    {
        static $cached = [];
        if (!isset($cached[$text])) {
            $cached[$text] = 0;
            $lookup_id_r = tep_db_query(
                "SELECT DISTINCT si.stock_delivery_terms_id " .
                "FROM " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " si " .
                " INNER JOIN " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " sit ON sit.stock_delivery_terms_id=si.stock_delivery_terms_id " .
                "WHERE (sit.stock_delivery_terms_text='" . tep_db_input($text) . "' OR sit.stock_delivery_terms_text='" . tep_db_input(trim($text)) . "') ".
                "LIMIT 1"
            );
            if ( tep_db_num_rows($lookup_id_r)>0 ){
                $get_id = tep_db_fetch_array($lookup_id_r);
                $cached[$text] = $get_id['stock_delivery_terms_id'];
            }

        }
        return $cached[$text];
    }

    public function getWarehouseLocations($warehouse_id)
    {
        if ( isset($this->warehouse_location_list[$warehouse_id]) ){
            return $this->warehouse_location_list[$warehouse_id];
        }

        $blocks = [];
        $locations = [];
        $parentMap = [];

        $locationList = [];
        foreach(\common\models\Locations::find()
            ->where(['warehouse_id' => $warehouse_id])
            ->orderBy(['parrent_id'=>SORT_ASC,'location_name'=>SORT_ASC])
            ->all() as $location){
            $locations[$location->location_id] = $location;
            if ( !isset($parentMap[$location->parrent_id]) ) $parentMap[$location->parrent_id] = [];
            $parentMap[$location->parrent_id][] = $location->location_id;
            if ( !isset($blocks[$location->block_id]) ) {
                $blocks[$location->block_id] = $location->locationBlock->block_name;
            }

            $locationList[$location->location_id] = [
                'location_id' => $location->location_id,
                'sort_weight' => 0,
                'parent_id' => $location->parrent_id,
                'block_name' => $blocks[$location->block_id],
                'location_name' => $location->location_name,
                'complete_name' => [],//[$blocks[$location->block_id].' '.$location->location_name]
            ];
        }

        $maxLevel = 0;
        foreach( $locationList as $_locId=>$locationVariant ){
            $level = 0;
            $parent_id = $locationVariant['parent_id'];
            $weight = [];
            $weight[] = array_search($_locId,$parentMap[$parent_id])+1;
            $completeName = [];
            $completeName[] = $locationVariant['block_name'].' '.$locationVariant['location_name'];
            while ($parent_id!=0){
                if ( !isset($locationList[$parent_id]) ) {
                    $level = false;
                    break;
                }
                $completeName[] = $locationList[$parent_id]['block_name'].' '.$locationList[$parent_id]['location_name'];
                $parent_id = $locationList[$parent_id]['parent_id'];
                $weight[] = array_search($_locId,$parentMap[$parent_id])+1;
                $level++;
            }
            if ( $level===false ) {
                unset($locationList[$_locId]);
            }else{
                $maxLevel = max($maxLevel,$level);
                $locationList[$_locId]['level'] = $level;
                $locationList[$_locId]['sort_weight'] = $weight;
                $locationList[$_locId]['complete_name'] = implode('; ',array_reverse($completeName));
            }
        }
        $sortWeight = [];
        foreach ($locationList as $locId=>$locData){
            $weight = $locData['sort_weight'];
            $sum = 0;
            $levelMult = 100;
            for( $i=0; $i<=$maxLevel;$i++){
                $levelIdx = $maxLevel-$i;
                $levelAdd = (isset($weight[$levelIdx])?$weight[$levelIdx]:1);
                $levelMult = $levelMult*($levelIdx+1);
                $sum += $levelMult*$levelAdd;
            }
            $sortWeight[$locId] = $sum;
            $locationList[$locId]['sort_weight'] = $sum;
        }
        array_multisort($sortWeight, SORT_NUMERIC, $locationList);

        $this->warehouse_location_list[$warehouse_id] = $locationList;
        return /*array_values*/($locationList);
    }

    public function getCustomerGroupName($id)
    {
        static $lookupedGroups = [];
        if ( !isset($lookupedGroups[(int)$id]) ) {
            $lookupedGroups[(int)$id] = \common\helpers\Group::get_user_group_name((int)$id);
        }
        return $lookupedGroups[(int)$id];
    }

    public function getCustomerGroupId($name)
    {
        static $lookuped = [];
        if ( !isset($lookuped[$name]) ) {
            $lookuped[$name] = 0;
            $get_id_r = tep_db_query("SELECT groups_id FROM ".TABLE_GROUPS." WHERE groups_name='".tep_db_input($name)."'");
            if ( tep_db_num_rows($get_id_r)>0 ) {
                $get_id = tep_db_fetch_array($get_id_r);
                $lookuped[$name] = $get_id['groups_id'];
            }
        }
        return $lookuped[$name];
    }

    public function getManufacturerName($id, $refresh=false)
    {
        static $lookuped;
        if ( !is_array($lookuped) || $refresh ){
            $lookuped = ArrayHelper::map(
                \common\models\Manufacturers::find()->select(['manufacturers_id', 'manufacturers_name'])->asArray()->all(),
                'manufacturers_id', 'manufacturers_name'
            );
        }
        return isset($lookuped[(int)$id])?$lookuped[(int)$id]:null;
    }

    public function getManufacturerId($name, $refresh=false)
    {
        static $lookuped = [];
        if ( !isset($lookuped[$name]) || $refresh ) {
            $lookuped[$name] = \common\models\Manufacturers::find()
                ->where(['manufacturers_name'=>$name])
                ->select(['manufacturers_id'])
                ->scalar();
        }
        return $lookuped[$name];
    }
    public function getPlatformName($id)
    {
        static $lookuped = [];
        if ( !isset($lookuped[(int)$id]) ) {
            $lookuped[(int)$id] = '';
            $get_name_r = tep_db_query("SELECT platform_name FROM ".TABLE_PLATFORMS." WHERE platform_id='".(int)$id."'");
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $get_name = tep_db_fetch_array($get_name_r);
                $lookuped[(int)$id] = $get_name['platform_name'];
            }
        }
        return $lookuped[(int)$id];
    }

    public function getPlatformId($name)
    {
        static $lookuped = [];
        if ( !isset($lookuped[$name]) ) {
            $lookuped[$name] = 0;
            $get_id_r = tep_db_query("SELECT platform_id FROM ".TABLE_PLATFORMS." WHERE platform_name='".tep_db_input($name)."'");
            if ( tep_db_num_rows($get_id_r)>0 ) {
                $get_id = tep_db_fetch_array($get_id_r);
                $lookuped[$name] = $get_id['platform_id'];
            }
        }
        return $lookuped[$name];
    }

    public function getDepartmentsName($id)
    {
        if ( !defined('TABLE_DEPARTMENTS') ) return '';
        static $lookuped = [];
        if ( !isset($lookuped[(int)$id]) ) {
            $lookuped[(int)$id] = '';
            $get_name_r = tep_db_query("SELECT departments_store_name FROM ".TABLE_DEPARTMENTS." WHERE departments_id='".(int)$id."'");
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $get_name = tep_db_fetch_array($get_name_r);
                $lookuped[(int)$id] = $get_name['departments_store_name'];
            }
        }
        return $lookuped[(int)$id];
    }

    public function getDepartmentsId($name)
    {
        if ( !defined('TABLE_DEPARTMENTS') ) return 0;
        static $lookuped = [];
        if ( !isset($lookuped[$name]) ) {
            $lookuped[$name] = 0;
            $get_id_r = tep_db_query("SELECT departments_id FROM ".TABLE_DEPARTMENTS." WHERE departments_store_name='".tep_db_input($name)."'");
            if ( tep_db_num_rows($get_id_r)>0 ) {
                $get_id = tep_db_fetch_array($get_id_r);
                $lookuped[$name] = $get_id['departments_id'];
            }
        }
        return $lookuped[$name];
    }


    public function get_document_types_name($id, $language_id)
    {
        static $lookuped = [];
        if ( !isset($lookuped[(int)$id]) ) {
            $lookuped[(int)$id] = '';
            $get_name_r = tep_db_query(
                "SELECT document_types_name FROM ".TABLE_DOCUMENT_TYPES." ".
                "WHERE document_types_id='".(int)$id."' AND language_id='".$language_id."'"
            );
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $get_name = tep_db_fetch_array($get_name_r);
                $lookuped[(int)$id] = $get_name['document_types_name'];
            }
        }
        return $lookuped[(int)$id];
    }

    public function get_document_types_by_name($name)
    {
        static $cached = [];
        if (!isset($cached[$name])) {
            $cached[$name] = 0;
            $lookup_id_r = tep_db_query(
                "SELECT DISTINCT dt.document_types_id " .
                "FROM " . TABLE_DOCUMENT_TYPES . " dt " .
                "WHERE dt.document_types_name='" . tep_db_input($name) . "' ".
                "LIMIT 1"
            );
            if ( tep_db_num_rows($lookup_id_r)>0 ){
                $get_id = tep_db_fetch_array($lookup_id_r);
                $cached[$name] = $get_id['document_types_id'];
            }else{
                $get_max_value = tep_db_fetch_array(tep_db_query(
                    "SELECT MAX(document_types_id) AS max_id FROM ".TABLE_DOCUMENT_TYPES
                ));
                $doc_id = intval($get_max_value['max_id'])+1;
                tep_db_query(
                    "INSERT INTO ".TABLE_DOCUMENT_TYPES." (document_types_id, language_id, document_types_name) ".
                    "SELECT '{$doc_id}', languages_id, '".tep_db_input(trim($name))."' FROM ".TABLE_LANGUAGES
                );
                $cached[$name] = $doc_id;
            }
        }
        return $cached[$name];
    }

    public function getCountryId($country)
    {
        $country = trim($country);
        $key = $country;
        static $_cached = false;
        if ( $_cached==false ) {
            $_cached[''] = 0;
            $countries_query = tep_db_query("select countries_id, countries_iso_code_2, countries_name from " . TABLE_COUNTRIES . " where language_id = '" . (int)\common\classes\language::defaultId() . "'");
            while ($countries = tep_db_fetch_array($countries_query)) {
                $_cached[$countries['countries_iso_code_2']] = $countries['countries_id'];
                if ( $countries['countries_iso_code_2']=='GB' ) $_cached['UK'] = $countries['countries_id'];
                $_cached[$countries['countries_name']] = $countries['countries_id'];
            }
        }
        if ( !isset($_cached[$key]) ) {
            $get_name_r = tep_db_query(
                "SELECT countries_id " .
                "FROM " . TABLE_COUNTRIES . " " .
                "WHERE countries_name='" . tep_db_input($country) . "' ".
                (strlen($country)==2?" OR countries_iso_code_2='".tep_db_input($country)."' ":'').
                (strlen($country)==3?" OR countries_iso_code_3='".tep_db_input($country)."' ":'').
                "LIMIT 1"
            );
            if ( tep_db_num_rows($get_name_r)>0 ) {
                $_name = tep_db_fetch_array($get_name_r);
                $_cached[$key] = $_name['countries_id'];
            }else{
                $_cached[$key] = 0;
            }
        }
        return isset($_cached[$key])?$_cached[$key]:0;
    }

    public function getCountryInfo($countryId)
    {
        $key = (int)$countryId;
        static $_cached = [];
        if ( !isset($_cached[$countryId]) ) {
            $_cached[$countryId] = \common\helpers\Country::get_countries((int)$countryId,true);
            if ( is_array($_cached[$countryId]) ) {
                $_cached[$countryId]['address_format_id'] = \common\helpers\Address::get_address_format_id($countryId);
            }
        }
        return isset($_cached[$key])?$_cached[$key]:0;
    }

    public function getCountryZoneInfo($zoneId)
    {
        $key = (int)$zoneId;
        static $_cached = [];
        if ( !isset($_cached[$key]) ) {
            $_cached[$key] = Zones::find()->where(['zone_id'=>$zoneId])->asArray()->one();
        }
        return isset($_cached[$key])?$_cached[$key]:false;
    }

    public function getCountryZoneId($zone, $countryId=null)
    {
        $key = (string)$zone.'^'.(!is_numeric($countryId)?'*':intval($countryId));
        static $_cached = [];
        if ( !isset($_cached[$key]) ) {
            $zone = Zones::find()->where([
                'OR',['LIKE','zone_code', $zone],['LIKE','zone_name', $zone]
            ])->andFilterWhere(['zone_country_id'=>$countryId])->select('zone_id')->asArray()->one();
            $_cached[$key] = is_array($zone)?$zone['zone_id']:0;
        }
        return isset($_cached[$key])?$_cached[$key]:false;
    }

    public function addressBookFind($customerId, $orderAddressBook)
    {
        // {{ fix address book linked to not existent customer
        $check_customer_exist = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS c ".
            "FROM ".TABLE_CUSTOMERS." ".
            "WHERE customers_id='".(int)$customerId."' "
        ));
        if ( is_array($check_customer_exist) && $check_customer_exist['c']==0 ) return 0;
        // }} fix address book linked to not existent customer

        $columnMap = [
            'company' => 'entry_company',
            'firstname' => 'entry_firstname',
            'lastname' => 'entry_lastname',
            'street_address' => 'entry_street_address',
            'suburb' => 'entry_suburb',
            'postcode' => 'entry_postcode',
            'city' => 'entry_city',
        ];
        $columnCheck = '';
        foreach ( $orderAddressBook as $key=>$val ) {
            if ( isset($columnMap[$key]) ) {
                $columnCheck .= "AND IFNULL({$columnMap[$key]},'')='".tep_db_input(strval($val))."' ";
            }
        }
        $columnCheck .= "AND entry_country_id='".tep_db_input(strval($orderAddressBook['country']['id']))."' ";
        if ( $orderAddressBook['zone_id'] ) {
            $columnCheck .= "AND IFNULL(entry_zone_id,0)='".tep_db_input(strval($orderAddressBook['zone_id']))."' ";
        }else{
            $columnCheck .= "AND IFNULL(entry_state,'')='".tep_db_input(strval($orderAddressBook['state']))."' ";
        }

        $searchAddressBook_r = tep_db_query(
            "SELECT address_book_id ".
            "FROM ".TABLE_ADDRESS_BOOK." ".
            "WHERE customers_id='".(int)$customerId."' ".
            " {$columnCheck} ".
            "LIMIT 1"
        );
        if ( tep_db_num_rows($searchAddressBook_r)>0 ) {
            $AddressBook = tep_db_fetch_array($searchAddressBook_r);
            return $AddressBook['address_book_id'];
        }

        tep_db_perform(TABLE_ADDRESS_BOOK,[
            'customers_id' => $customerId,
            'entry_company' => strval($orderAddressBook['company']),
            'entry_firstname' => strval($orderAddressBook['firstname']),
            'entry_lastname' => strval($orderAddressBook['lastname']),
            'entry_street_address' => strval($orderAddressBook['street_address']),
            'entry_suburb' => strval($orderAddressBook['suburb']),
            'entry_postcode' => strval($orderAddressBook['postcode']),
            'entry_city' => strval($orderAddressBook['city']),
            'entry_state' => strval($orderAddressBook['state']),
            'entry_country_id' => strval($orderAddressBook['country']['id']),
            'entry_zone_id' => strval($orderAddressBook['zone_id']),
        ]);

        return tep_db_insert_id();
    }

    public function supplierData($supplierId, $reset=false)
    {
        static $cached = [];
        if (!isset($cached[(int)$supplierId]) || $reset ){
            $cached[(int)$supplierId] = tep_db_fetch_array(tep_db_query("SELECT * FROM suppliers WHERE suppliers_id='".(int)$supplierId."' "));
        }
        return $cached[(int)$supplierId];
    }

    public function getSupplierName($SupplierId) {
        static $cache = [];
        if ( !isset($cache[$SupplierId]) ) {
            $cache[$SupplierId] = \common\helpers\Suppliers::getSupplierName($SupplierId);
        }
        return $cache[$SupplierId];
    }

    public function getSupplierIdByName($SupplierName)
    {
        static $cache = [];
        if ( !array_key_exists($SupplierName, $cache) ) {
            $cache[$SupplierName] = \common\helpers\Suppliers::getSupplierIdByName($SupplierName);
            if ( !$cache[$SupplierName] && (string)$SupplierName!=trim($SupplierName) ){
                $cache[$SupplierName] = \common\helpers\Suppliers::getSupplierIdByName(trim($SupplierName));
            }
        }
        return $cache[$SupplierName];
    }

    public function getWarehouseName($warehouseId)
    {
        static $cache = [];
        if ( !isset($cache[$warehouseId]) ) {
            $cache[$warehouseId] = \common\helpers\Warehouses::get_warehouse_name($warehouseId);
        }
        return $cache[$warehouseId];
    }

    public function getWarehouseIdByName($warehouseName)
    {
        static $cache = [];
        if ( !array_key_exists($warehouseName, $cache) ) {
            $cache[$warehouseName] = \common\helpers\Warehouses::get_warehouse_id($warehouseName);
        }
        return $cache[$warehouseName];
    }

    public function getWarehouseLocationName($warehouseId, $warehouseLocationId)
    {
        static $cache = [];
        if ( !isset($cache[$warehouseLocationId]) ) {
            $cache[$warehouseLocationId] = '';
            $locations = $this->getWarehouseLocations($warehouseId);
            foreach ($locations as $location) {
                if ($warehouseLocationId!=$location['location_id']) continue;
                $cache[$warehouseLocationId] = $location['complete_name'];
                break;
            }
        }
        return $cache[$warehouseLocationId];
    }

    public function getWarehouseLocationByName($warehouseId, $warehouseLocationName)
    {
        static $cache = [];
        $key = intval($warehouseId).'@'.strval($warehouseLocationName);
        if ( array_key_exists($key, $cache) ) {
            $cache[$key] = null;
            $locations = $this->getWarehouseLocations($warehouseId);
            foreach ($locations as $location) {
                if ( $location['complete_name']==$warehouseLocationName || trim(strtolower($location['complete_name']))==trim(strtolower($warehouseLocationName)) ){
                    $cache[$key] = (int)$location['location_id'];
                    break;
                }
            }
        }
        return $cache[$key];
    }

    public function lookupOrderStatus($statusName, $createMissing=false)
    {

        $getSameName_r = tep_db_query(
            "SELECT orders_status_id ".
            "FROM ".TABLE_ORDERS_STATUS." ".
            "WHERE orders_status_name='".tep_db_input($statusName)."' ".
            "ORDER BY orders_status_groups_id ".
            "LIMIT 1 "
        );
        if ( tep_db_num_rows($getSameName_r)>0 ) {
            $statusIdArray = tep_db_fetch_array($getSameName_r);
            return $statusIdArray['orders_status_id'];
        }

        if ( $createMissing ) {
            $get_new_order_status_id_arr = tep_db_fetch_array(tep_db_query(
                "SELECT MAX(orders_status_id) AS current_max_id FROM ".TABLE_ORDERS_STATUS
            ));
            $new_order_status_id = intval($get_new_order_status_id_arr['current_max_id'])+1;
            tep_db_query(
                "INSERT INTO ".TABLE_ORDERS_STATUS." ".
                "(orders_status_id, language_id, orders_status_groups_id, orders_status_name ) ".
                "SELECT {$new_order_status_id}, languages_id, 1, '".tep_db_input($statusName)."' FROM ".TABLE_LANGUAGES
            );

            return $new_order_status_id;
        }
        return 0;
    }

    public function getPcTemplateName($id)
    {
        static $lookuped = [];
        if ( !isset($lookuped[$id]) ) {
            $lookuped[$id] = '';
            $get_data_r = tep_db_query("SELECT pctemplates_name FROM " . TABLE_PCTEMPLATES . " WHERE pctemplates_id='" . (int)$id . "' ");
            if ( tep_db_num_rows($get_data_r)>0 ) {
                $_data = tep_db_fetch_array($get_data_r);
                $lookuped[$id] = $_data['pctemplates_name'];
            }
        }
        return $lookuped[$id];
    }
    public function lookupPcTemplateId($name)
    {
        static $lookuped = [];
        if ( !isset($lookuped[$name]) ) {
            $lookuped[$name] = 0;
            $get_data_r = tep_db_query("SELECT pctemplates_id FROM " . TABLE_PCTEMPLATES . " WHERE pctemplates_name='" . tep_db_input($name) . "' LIMIT 1");
            if ( tep_db_num_rows($get_data_r)>0 ) {
                $_data = tep_db_fetch_array($get_data_r);
                $lookuped[$name] = $_data['pctemplates_id'];
            }
        }
        return $lookuped[$name];
    }

    public function lookupProductId($value, $field='products_model') {
        static $lookuped = [];
        $key = $field . '^' . $value;
        if ( !isset($lookuped[$key]) ) {
            $lookuped[$key] = 0;
            $get_data_r = tep_db_query("SELECT products_id FROM " . TABLE_PRODUCTS . " WHERE " . tep_db_input($field). "='" . tep_db_input($value) . "'");
            if ( tep_db_num_rows($get_data_r)==1 ) {
                $_data = tep_db_fetch_array($get_data_r);
                $lookuped[$key] = $_data['products_id'];
            }
        }
        return $lookuped[$key];
    }
    
    public function lookupCustomerId($value, $field='customers_email_address') {
        static $lookuped = [];
        $key = $field . '^' . $value;
        if ( !isset($lookuped[$key]) ) {
            $lookuped[$key] = 0;
            $get_data_r = tep_db_query("SELECT customers_id FROM " . TABLE_CUSTOMERS . " WHERE " . tep_db_input($field). "='" . tep_db_input($value) . "'");
            if ( tep_db_num_rows($get_data_r)==1 ) {
                $_data = tep_db_fetch_array($get_data_r);
                $lookuped[$key] = $_data['customers_id'];
            }
        }
        return $lookuped[$key];
    }

    public function lookupProductData($products_id, $field='products_model') {
        static $lookuped = [];
        $key = $field . '^' . $products_id;
        $lookuped[$key] = '';
        if ( !isset($lookuped[$key]) ) {
          $_data = \common\models\Products::findOne($products_id);
          if ( isset($_data[$field])) {
            $lookuped[$key] = $_data[$field];
          }
        }
        return $lookuped[$key];
    }

    public function lookupOrderStatusId($statusName, $typeId=1)
    {
        $status_id = false;
        $orders_status_query = tep_db_query(
            "select s.orders_status_id ".
            "from " . TABLE_ORDERS_STATUS . " s ".
            " INNER JOIN ".TABLE_ORDERS_STATUS_GROUPS." sg ON sg.orders_status_groups_id=s.orders_status_groups_id AND sg.orders_status_type_id='".(int)$typeId."' ".
            "where s.orders_status_name='".tep_db_input($statusName)."' ".
            "limit 1 "
        );
        if ( tep_db_num_rows($orders_status_query)>0 ) {
            $_orders_status = tep_db_fetch_array($orders_status_query);
            $status_id = (int)$_orders_status['orders_status_id'];
        }
        return $status_id;
    }

    public function orderStatusIdExists($statusId, $typeId=1)
    {
        $status_id_exists = false;
        $orders_status_query = tep_db_query(
            "select s.orders_status_id ".
            "from " . TABLE_ORDERS_STATUS . " s ".
            " INNER JOIN ".TABLE_ORDERS_STATUS_GROUPS." sg ON sg.orders_status_groups_id=s.orders_status_groups_id AND sg.orders_status_type_id='".(int)$typeId."' ".
            "where s.orders_status_id='".intval($statusId)."' ".
            "limit 1 "
        );
        if ( tep_db_num_rows($orders_status_query)>0 ) {
            $_orders_status = tep_db_fetch_array($orders_status_query);
            $status_id_exists = true;
        }
        return $status_id_exists;
    }

    public function lookupPropertyId($name, $parentId=0, $languageId=null)
    {
        return \common\models\Properties::find()
            ->alias('p')
            ->where(['p.parent_id'=>$parentId])
            ->join('inner join', \common\models\PropertiesDescription::tableName().' pd', 'pd.properties_id=p.properties_id')
            ->andFilterWhere(['pd.language_id'=>$languageId])
            ->andWhere(['pd.properties_name'=>$name])
            ->distinct()
            ->select(['id'=>'p.properties_id', 'type'=>'p.properties_type'])
            ->asArray()
            ->all();
    }

    public function parseDate( $date )
    {
        $resultDate = date('Y-m-d H:i:s',strtotime($date));
        if ( preg_match('/^(?<day>\d{2})\/(?<month>\d{2})\/(?<year>\d{4})\D?((\d{2}:\d{2})(:\d{2})?)?/', $date, $match) ){
            $resultDate = date('Y-m-d H:i:s',strtotime($match['year'].'-'.$match['month'].'-'.$match['day'].' '.$match[4]));
        }
        return $resultDate;
    }

}