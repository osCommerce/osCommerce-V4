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
use backend\components\Information;
use \common\helpers\Translation;

/**
 * default controller to handle user requests.
 */
class Meta_tagsController extends Sceleton {
    
    public $acl = ['BOX_HEADING_SEO', 'BOX_META_TAGS'];
    
    public function __construct($id, $module=null){
      Translation::init('admin/meta-tags');
      parent::__construct($id, $module);
    }
    
	/**
	 * Index action is the default action in a controller.
	 */
    public function actionIndex()
    {
    $languages_id = \Yii::$app->settings->get('languages_id');

    \common\helpers\Translation::init('admin/email/templates');

    $this->selectedMenu = array('seo_cms', 'meta_tags');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('meta_tags/'), 'title' => HEADING_TITLE);
    $this->view->headingTitle = HEADING_TITLE;
    $tabList = array();

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="updateMetaTags()">' . IMAGE_UPDATE . '</span>';

//    $data = array();
    $get_ex_values_q = tep_db_query("select * from ".TABLE_META_TAGS." where affiliate_id=0 and platform_id>0");
    if (tep_db_num_rows($get_ex_values_q)>0) {
      while($get_ex_values = tep_db_fetch_array($get_ex_values_q)) {
//        $key1 = $get_ex_values['meta_tags_key'];
//        $key2 = $get_ex_values['platform_id'].'_'.$get_ex_values['language_id'].'_'.$get_ex_values['affiliate_id'];
//        if ( !isset($data[$key1]) ) $data[$key1] = array();
//        $data[$key1][$key2] = $get_ex_values['meta_tags_value'];
        $constName = $get_ex_values['meta_tags_key'].'_'.$get_ex_values['language_id'].'_'.$get_ex_values['platform_id'];
        defined($constName) or define($constName, $get_ex_values['meta_tags_value']);
      }
    }

    $languages = \common\helpers\Language::get_languages();
    $platforms = \common\classes\platform::getList(false);
    foreach( $platforms as $_idx=>$_aff ) {
      $platforms[$_idx]['name'] = $_aff['text'];
    }
    //$affiliates = $platforms;

    $tabs_data = array(
        'CATEGORY_INDEX_TAGS' => array(
            'tab_title' => CATEGORY_INDEX_TAGS,
            'id' => 'index_tags',
            'active' => true,
            'input_key' => array(
                'HEAD_TITLE_TAG_DEFAULT', 'HEAD_KEY_TAG_DEFAULT', 'HEAD_DESC_TAG_DEFAULT', 'HEAD_H1_TAG_DEFAULT', 'HEAD_H2_TAG_DEFAULT', 'HEAD_H3_TAG_DEFAULT',
            ),
        ),
        'CATEGORY_CATEGORY_TAGS' => array(
            'tab_title' => CATEGORY_CATEGORY_TAGS,
            'id' => 'category_tags',
            'popupOption' => 'category',
            'class' => 'add-keys',
            'input_key' => array(
                'HEAD_TITLE_TAG_CATEGORY', 'HEAD_KEY_TAG_CATEGORY', 'HEAD_DESC_TAG_CATEGORY', 'DEFAULT_BREADCRUMB_CATALOG_INDEX', 'HEAD_H1_TAG_CATEGORY', 'HEAD_H2_TAG_CATEGORY', 'HEAD_H3_TAG_CATEGORY', 'IMAGE_ALT_TAG_MASK_CATEGORY', 'IMAGE_TITLE_TAG_MASK_CATEGORY',
            ),
        ),
        'CATEGORY_BRAND_TAGS' => array(
            'tab_title' => CATEGORY_BRAND_TAGS,
            'id' => 'brand_tags',
            'popupOption' => 'brand',
            'class' => 'add-keys',
            'input_key' => array(
                'HEAD_TITLE_TAG_BRAND', 'HEAD_KEY_TAG_BRAND', 'HEAD_DESC_TAG_BRAND', 'HEAD_H1_TAG_BRAND', 'HEAD_H2_TAG_BRAND', 'HEAD_H3_TAG_BRAND',
            ),
        ),
        'CATEGORY_PRODUCT_INFO_TAGS' => array(
            'tab_title' => CATEGORY_PRODUCT_INFO_TAGS,
            'id' => 'product_info_tags',
            'class' => 'add-keys',
            'input_key' => array(
                'HEAD_TITLE_TAG_PRODUCT_INFO', 'HEAD_KEY_TAG_PRODUCT_INFO', 'HEAD_DESC_TAG_PRODUCT_INFO', 'DEFAULT_BREADCRUMB_CATALOG_PRODUCT', 'HEAD_H1_TAG_PRODUCT_INFO', 'HEAD_H2_TAG_PRODUCT_INFO', 'HEAD_H3_TAG_PRODUCT_INFO', 'IMAGE_ALT_TAG_MASK_PRODUCT_INFO', 'IMAGE_TITLE_TAG_MASK_PRODUCT_INFO',
            ),
        ),
        'CATEGORY_PRODUCTS_ALL_TAGS' => array(
            'tab_title' => CATEGORY_PRODUCTS_ALL_TAGS,
            'id' => 'products_all_tags',
            'input_key' => array(
                'HEAD_TITLE_TAG_PRODUCTS_ALL', 'HEAD_KEY_TAG_PRODUCTS_ALL', 'HEAD_DESC_TAG_PRODUCTS_ALL', 'HEAD_H1_TAG_PRODUCTS_ALL', 'HEAD_H2_TAG_PRODUCTS_ALL', 'HEAD_H3_TAG_PRODUCTS_ALL',
            ),
        ),

      'CATEGORY_WHATS_NEW_TAGS' => array(
        'tab_title' => CATEGORY_WHATS_NEW_TAGS,
        'id' => 'whats_new_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_WHATS_NEW', 'HEAD_KEY_TAG_WHATS_NEW', 'HEAD_DESC_TAG_WHATS_NEW', 'HEAD_H1_TAG_WHATS_NEW', 'HEAD_H2_TAG_WHATS_NEW', 'HEAD_H3_TAG_WHATS_NEW',
        ),
      ),
      'CATEGORY_SPECIALS_TAGS' => array(
        'tab_title' => CATEGORY_SPECIALS_TAGS,
        'id' => 'specials_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_SPECIALS', 'HEAD_KEY_TAG_SPECIALS', 'HEAD_DESC_TAG_SPECIALS', 'HEAD_H1_TAG_SPECIALS', 'HEAD_H2_TAG_SPECIALS', 'HEAD_H3_TAG_SPECIALS',
        ),
      ),
      'CATEGORY_FEATURED_TAGS' => array(
        'tab_title' => CATEGORY_FEATURED_TAGS,
        'id' => 'featured_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_FEATURED', 'HEAD_KEY_TAG_FEATURED', 'HEAD_DESC_TAG_FEATURED', 'HEAD_H1_TAG_FEATURED', 'HEAD_H2_TAG_FEATURED', 'HEAD_H3_TAG_FEATURED',
        ),
      ),
      'CATEGORY_PRODUCT_REVIEWS_TAGS' => array(
        'tab_title' => CATEGORY_PRODUCT_REVIEWS_TAGS,
        'id' => 'product_reviews_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_PRODUCT_REVIEWS_INFO', 'HEAD_KEY_TAG_PRODUCT_REVIEWS_INFO', 'HEAD_DESC_TAG_PRODUCT_REVIEWS_INFO', 'HEAD_H1_TAG_PRODUCT_REVIEWS_INFO', 'HEAD_H2_TAG_PRODUCT_REVIEWS_INFO', 'HEAD_H3_TAG_PRODUCT_REVIEWS_INFO',
        ),
      ),
      'CATEGORY_GIFT_CARD_TAGS' => array(
        'tab_title' => CATEGORY_GIFT_CARD_TAGS,
        'id' => 'gift_card_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_GIFT_CARD', 'HEAD_KEY_TAG_GIFT_CARD', 'HEAD_DESC_TAG_GIFT_CARD', 'HEAD_H1_TAG_GIFT_CARD', 'HEAD_H2_TAG_GIFT_CARD', 'HEAD_H3_TAG_GIFT_CARD',
        ),
      ),
      'CATEGORY_FREE_SAMPLES_TAGS' => array(
        'tab_title' => CATEGORY_FREE_SAMPLES_TAGS,
        'id' => 'free_samples_tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_FREE_SAMPLES', 'HEAD_KEY_TAG_FREE_SAMPLES', 'HEAD_DESC_TAG_FREE_SAMPLES', 'HEAD_H1_TAG_FREE_SAMPLES', 'HEAD_H2_TAG_FREE_SAMPLES', 'HEAD_H3_TAG_FREE_SAMPLES',
        ),
      ),
      'CATEGORY_DEFAULT_TAGS' => array(
        'tab_title' => CATEGORY_DEFAULT_TAGS,
        'id' => 'default_tags',
        'class' => 'deprecated-tags',
        'input_key' => array(
          'HEAD_TITLE_TAG_ALL', 'HEAD_KEY_TAG_ALL', 'HEAD_DESC_TAG_ALL', 'HEAD_H1_TAG_ALL', 'HEAD_H2_TAG_ALL', 'HEAD_H3_TAG_ALL',
        ),
      ),
    );

    $tabs_data = \common\helpers\Acl::applyExtensionMetaTags($tabs_data);
    $tabs_data = array_values($tabs_data);
    $popup_keys = [];
    foreach ( $tabs_data as $_tabs_data ) {
        if (isset($_tabs_data['popup_keys']) && is_array($_tabs_data['popup_keys'])){
            $popup_keys = array_merge($popup_keys, $_tabs_data['popup_keys']);
        }
    }

    foreach( $tabs_data as $idx=>$tab_data ) {
      $tabs_data[$idx]['input_controls'] = array();
      foreach($tab_data['input_key'] as $meta_const_key){
        if (strpos($meta_const_key, 'HEAD_KEY_TAG') !== false) continue;
        foreach($platforms as $platform) {
          foreach ($languages as $__language) {
            $control_value = defined($meta_const_key.'_' . $__language['id'] . '_' . $platform['id']) ? constant($meta_const_key.'_' . $__language['id'] . '_' . $platform['id']) : '';
            if (strpos($meta_const_key, 'HEAD_DESC_TAG') !== false) {
              $control = tep_draw_textarea_field($meta_const_key . '[' . $__language['id'] . '][' . $platform['id'] . ']', 'soft', '70', '3', $control_value, 'class="form-control head-desc-tag"');
            } elseif (strpos($meta_const_key, 'HEAD_TITLE_TAG_') !== false) {
                $control = tep_draw_input_field($meta_const_key . '[' . $__language['id'] . '][' . $platform['id'] . ']', $control_value, 'class="form-control head-title-tag"');
            } else {
              $control = tep_draw_input_field($meta_const_key . '[' . $__language['id'] . '][' . $platform['id'] . ']', $control_value, 'class="form-control"');
            }
            if (strpos($meta_const_key, 'HEAD_H1_TAG') !== false) {
              $label = TEXT_H1_TAG;
            } elseif (strpos($meta_const_key, 'HEAD_H2_TAG') !== false) {
              $label = TEXT_H2_TAG;
              $control_inputs = '';
              foreach (explode("\n", $control_value) as $value) {
                $control_inputs .= '<span class="row">' . tep_draw_input_field($meta_const_key . '[' . $__language['id'] . '][' . $platform['id'] . '][]', $value, 'class="form-control"') . '<span class="del-pt del-tag"></span></span>';
              }
              $control = '<span id="' . $meta_const_key . '-' . $__language['id'] . '-' . $platform['id'] . '">' . $control_inputs . '</span><span onclick="addInput(\'' . $meta_const_key . '-' . $__language['id'] . '-' . $platform['id'] . '\', \'' . htmlspecialchars('<span class="row">' . tep_draw_input_field($meta_const_key . '[' . $__language['id'] . '][' . $platform['id'] . '][]', '', 'class="form-control"') . '<span class="del-pt del-tag"></span></span>') . '\')" class="btn btn-add-more">' . TEXT_AND_MORE  . '</span>';
            } elseif (strpos($meta_const_key, 'HEAD_H3_TAG') !== false) {
              $label = TEXT_H3_TAG;
              $control_inputs = '';
              foreach (explode("\n", $control_value) as $value) {
                $control_inputs .= '<span class="row">' . tep_draw_input_field($meta_const_key . '[' . $__language['id'] . '][' . $platform['id'] . '][]', $value, 'class="form-control"') . '<span class="del-pt del-tag"></span></span>';
              }
              $control = '<span id="' . $meta_const_key . '-' . $__language['id'] . '-' . $platform['id'] . '">' . $control_inputs . '</span><span onclick="addInput(\'' . $meta_const_key . '-' . $__language['id'] . '-' . $platform['id'] . '\', \'' . htmlspecialchars('<span class="row">' . tep_draw_input_field($meta_const_key . '[' . $__language['id'] . '][' . $platform['id'] . '][]', '', 'class="form-control"') . '<span class="del-pt del-tag"></span></span>') . '\')" class="btn btn-add-more">' . TEXT_AND_MORE  . '</span>';
            } else {
              $label = (tep_not_null($c_key = \common\helpers\Translation::getTranslationValue($meta_const_key, 'metatags', $languages_id))? $c_key:(defined($meta_const_key) ? constant($meta_const_key) : ''));
              if ($label == '' && strpos($meta_const_key, 'HEAD_TITLE_TAG') !== false) {
                $label = (defined('HEAD_TITLE_TAG_ALL') ? HEAD_TITLE_TAG_ALL : '');
              }
              if ($label == '' && strpos($meta_const_key, 'HEAD_DESC_TAG') !== false) {
                $label = (defined('HEAD_DESC_TAG_ALL') ? HEAD_DESC_TAG_ALL : '');
              }
              if (strpos($meta_const_key, 'IMAGE_ALT_TAG_MASK') !== false) {
                $label = TEXT_IMAGE_ALT_TAG_MASK;
              }
              if (strpos($meta_const_key, 'IMAGE_TITLE_TAG_MASK') !== false) {
                $label = TEXT_IMAGE_TITLE_TAG_MASK;
              }
              if ( strpos($meta_const_key,'DEFAULT_BREADCRUMB_')===0 ) {
                  if ( defined('LABEL_'.$meta_const_key) ) {
                      $label = constant('LABEL_'.$meta_const_key);
                  }elseif (defined('LABEL_DEFAULT_BREADCRUMB')){
                      $label = LABEL_DEFAULT_BREADCRUMB;
                  }
              }
            }
            if ( !isset($tabs_data[$idx]['input_controls'][$__language['id'].'_'.$platform['id']]) ) $tabs_data[$idx]['input_controls'][$__language['id'].'_'.$platform['id']] = array();
            $tabs_data[$idx]['input_controls'][$__language['id'].'_'.$platform['id']][] = array(
              'label' => $label,
              'control' => $control,
            );
          }
        }
      }
    }

    return $this->render('index', [
      'tabs_data' => $tabs_data,
      'platforms' => $platforms,
      'first_platform_id' => \common\classes\platform::firstId(),
      'isMultiPlatform' => \common\classes\platform::isMulti(),
      'languages' => array_map(function($lang){
        $lang['logo'] = $lang['image'];
        return $lang;
      }, $languages),
      'popup_keys' => $popup_keys,
      'update_form_action' => Yii::$app->urlManager->createUrl('meta_tags/update'),
    ]);
  }

  
  public function actionUpdate()
  {
        $this->layout = false;
        $result = false;
        $in_data = tep_db_prepare_input($_POST);
        foreach($in_data as $inskey=>$values) {
         if (is_array($values) && sizeof($values)>0){
           foreach($values as $lang_id=>$insvalue) {
              if (is_array($insvalue) && sizeof($insvalue) > 0){
                foreach ($insvalue as $platform_id => $value){
                  $this->update_value($inskey, $value, $lang_id, $platform_id);
                  $result = true;
                }
              }
           }
         }
        }
        if ($result){
          echo 'ok';
        }
        
  }
  
   private function update_value($inkey, $invalue, $inlang, $platform_id) {
    $inkey = $inkey;
    if (is_array($invalue)) {
      $invalue = implode("\n", array_map('strip_tags', $invalue));
    } else {
      $invalue = strip_tags($invalue);
    }
    $inlang = intval($inlang);
    if ($inkey != 'x' && $inkey != 'y' && $inlang>0) {
      $ch_ex = tep_db_query("select meta_tags_key from ".TABLE_META_TAGS." where meta_tags_key='".tep_db_input($inkey)."' and language_id='".$inlang."' and platform_id='".(int)$platform_id."' and affiliate_id = '0'");
      if (tep_db_num_rows($ch_ex)>0) {
        // update
        tep_db_query("update ".TABLE_META_TAGS." set meta_tags_value='".tep_db_input($invalue)."' where meta_tags_key='".tep_db_input($inkey)."' and language_id='".$inlang."' and platform_id='".(int)$platform_id."' and affiliate_id = '0'");
      } else {
        // insert
        tep_db_query("insert into ".TABLE_META_TAGS." set meta_tags_value='".tep_db_input($invalue)."', meta_tags_key='".tep_db_input($inkey)."', language_id='".$inlang."', platform_id='".(int)$platform_id."', affiliate_id = '0'");
      }
    }
  }

}
