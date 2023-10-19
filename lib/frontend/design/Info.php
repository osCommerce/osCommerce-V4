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

namespace frontend\design;

use common\classes\design;
use common\models\Zones;
use Yii;
use common\classes\Images;
use backend\design\Style;
use yii\helpers\VarDumper;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use frontend\design\Block;

class Info
{

    public static $pdfProductsEnd = false;
    public static $jsGlobalData = [];
    public static $includeJsFiles = [];
    public static $includeExtensionJsFiles = [];
    public static $includeReducer = [];
    public static $themeMap = [];
    public static $scriptCss = '';

  public static function isAdmin()
  {
    if ( Yii::$app->id=='app-console' || Yii::$app->storage->get('is_admin')) {
        return true;
    }
    if (\common\helpers\System::isBackend()) {
        return true;
    }
    $params = Yii::$app->request->get();

    $referer = Yii::$app->request->headers['referer'] ?? ''; // php 8.1: strpos(): Passing null to parameter #1 ($haystack) of type string is deprecated
    if ( ( strpos($referer, '/admin/design') && \common\helpers\Acl::checkRuleByDeviceHash(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_THEMES']) ) ||
        ((
         ( strpos($referer, '/admin/categories/productedit') && \common\helpers\Acl::checkRuleByDeviceHash(['BOX_HEADING_CATALOG', 'BOX_CATALOG_CATEGORIES_PRODUCTS']) ) ||
         ( strpos($referer, '/admin/platforms' ) && \common\helpers\Acl::checkRuleByDeviceHash(['BOX_HEADING_FRONENDS']) ) ||
         ( strpos($referer, '/admin/google_analytics' ) && \common\helpers\Acl::checkRuleByDeviceHash(['BOX_HEADING_SEO', 'BOX_HEADING_GOOGLE_ANALYTICS']) ) ||
         ( strpos($referer, '/admin/customers' ) && \common\helpers\Acl::checkRuleByDeviceHash(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_CUSTOMERS']) ) ||
         ( strpos($referer, '/admin/editor' ) && \common\helpers\Acl::checkRuleByDeviceHash(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_CUSTOMERS']) ) ||
         ( strpos($referer, '/admin/orders' ) && \common\helpers\Acl::checkRuleByDeviceHash(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS']) )
         //( \common\helpers\Acl::checkRuleByDeviceHash(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS']) )// for payment frames
        ) && ((Yii::$app->controller->id ?? null) . '/' . (Yii::$app->controller->action->id ?? null) != 'index/index')) // don't confuse admin with logged in view
          /*|| $params['is_admin'] can't check access level this way*/
       ) {
      return true;
    } else {
      return false;
    }

  }
  public static function isTotallyAdmin()
  {
    if ( preg_match('/^app-(backend|pos)$/',Yii::$app->id) ){
      return true;
    } else {
      return false;
    }
  }
  
  public static function isAdminOrders()
  {
    if (strpos(Yii::$app->request->headers['referer'], '/admin/orders' )){
      return true;
    } else {
      return false;
    }
  }

  public static function dataClass($class)
  {

    if (Info::isAdmin()){
      return ' data-class="' . str_replace('"', '\'', $class) . '"';
    } else {
      return '';
    }

  }

/**
 * fills in products list container with product details according design
 * @param array $products_ids (in correct display order)
 * @param array $settings - theme/block settings
 * @return array products array (all data according settings)
 */
  public static function getListProductsDetails($products_ids, $settings = array()) {

    $withToSettings = [
      //VL2do 'show_price' => 'price',
      'show_image' => 'defaultImage.imageListDescription',
      'show_attributes' => 'listingAttributes',
      'show_categories' => 'listingCategories',
      //VL2do 'show_properties' => 'listingProperties',
      //VL2do 'show_inventories' => 'listingInventories',
      //VL2do comments
      //VL2do stock
    ];

    global $cart;
    $ret = $addSettings = [];

    $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
    if (is_array($products_ids) && !empty($products_ids)) {

      $currencies = \Yii::$container->get('currencies');
      $container = Yii::$container->get('products');
      $repository = new \common\models\repositories\ProductsRepository();
      $addSettings = array_replace($addSettings, $settings);

      $listingType = (isset($settings['listing_type'])?$settings['listing_type']:'main');
      $addSettings['listing_type'] = $listingType;

      $with = [];
      $with[] = 'listingDescription'; // name is required (always)

      foreach ($withToSettings as $k => $v) {
        if (isset($settings[0][$k]) && !$settings[0][$k]) {
          $with[] = $v;
        }
      }
      if (!isset($settings['options_prefix'])) {
        $settings['options_prefix'] = 'list';
      }
      $addSettings['settingsAdditional'] = $settings['settingsAdditional'] ?? [];
      $products = $repository->getListDetails($products_ids, $with, $addSettings);

      if (is_array($products) && count($products)>0) {
        
        $container->loadList($products); // main products details to container to avoid extra queries

          $listingName = (isset($settings[0]['listing_type']) ? $settings[0]['listing_type'] : '');
          if ((isset($settings[0]['listing_type_rows']) && $settings[0]['listing_type_rows']) || (isset($settings[0]['listing_type_b2b']) && $settings[0]['listing_type_b2b'])) {
              $listingName = self::listType($settings[0]);
          }

          if (isset($settings['itemElements']) && is_array($settings['itemElements']) && !empty($settings['itemElements'])){
              $settings['itemElements'] = array_merge($settings['itemElements'], \frontend\design\boxes\ProductListing::getItemElements($listingName));
          } else {
              $settings['itemElements'] = \frontend\design\boxes\ProductListing::getItemElements($listingName);
          }

        foreach ($products as $products_arr) {
          $products_arr['id'] = $products_arr['products_id'];
          if (!empty($products_arr['products_tax_class_id']) && !isset($products_arr['tax_rate'])) {
              $products_arr['tax_rate'] = \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']);
          }
          
          if (!(isset($settings[0]['show_price']) && $settings[0]['show_price']) && (Info::themeSetting('old_listing') || (isset($settings['itemElements']['price']) ? $settings['itemElements']['price'] : false))) {
            $pInstance = \common\models\Product\Price::getInstance($products_arr['products_id']);
            $product_price = $pInstance->getProductPrice(['qty'=>1]);
            $special_price = $pInstance->getProductSpecialPrice(['qty'=>1]);

            //$product_price = \common\helpers\Product::get_products_price($products_arr['products_id'], 1, $products_arr['products_price']);
            //$special_price = \common\helpers\Product::get_products_special_price($products_arr['products_id']);
            if ( strpos($products_arr['products_id'],'{')!==false ){
              $special_price = $pInstance->getInventorySpecialPrice(['qty'=>1]);
              $product_price = $pInstance->getInventoryPrice(['qty'=>1]);
            }

            if ($special_price!==false && is_numeric($special_price)) {
              $products_arr = array_merge($pInstance->getSpecialPriceDetails(['qty'=>1]), $products_arr);
              $products_arr['price_old'] = $currencies->display_price($product_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
              $products_arr['price_special'] = $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
              if (abs($special_price) < 0.01 && defined('PRODUCT_PRICE_FREE') && PRODUCT_PRICE_FREE == 'true') {
                $products_arr['price_special'] = TEXT_FREE;
              }
              // clear true final price
              $products_arr['calculated_price'] = $currencies->calculate_price($special_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
              $products_arr['calculated_price_exc'] = $currencies->calculate_price($special_price, 0);
              if (isset($products_arr['special_promote_type']) ) {
                  $old_clear = $currencies->calculate_price($product_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
                  $old_ex_clear = $currencies->calculate_price($product_price, 0);

                  if ($products_arr['special_promote_type']==1) { //percent
                      if ($old_clear>0) {
                        $special_promo_value = round(($old_clear - $products_arr['calculated_price'])/$old_clear*100);
                      } else {
                       $special_promo_value = 100;
                      }
                      $special_promo_str = $special_promo_value  . '%';

                      if ($old_ex_clear>0) {
                        $special_promo_ex_value = round(($old_ex_clear - $products_arr['calculated_price_exc'])/$old_ex_clear*100);
                      } else {
                       $special_promo_ex_value = 100;
                      }
                      $special_promo_ex_str = $special_promo_ex_value  . '%';


                    } elseif ($products_arr['special_promote_type']==2) { //fixed
                      $special_promo_value = $currencies->format_clear($old_clear - $products_arr['calculated_price'], false);
                      $special_promo_str = $currencies->format($old_clear - $products_arr['calculated_price'], false);

                      $special_promo_ex_value = $currencies->format_clear($old_ex_clear - $products_arr['calculated_price_exc'], false);
                      $special_promo_ex_str = $currencies->format($old_ex_clear - $products_arr['calculated_price_exc'], false);

                    }
                    $products_arr += [
                                    'special_promo_str' => $special_promo_str ?? null,
                                    'special_promo_value' => $special_promo_value ?? null,
                                    'special_promo_ex_value' => $special_promo_ex_value ?? null,
                                    'special_promo_ex_str' => $special_promo_ex_str ?? null
                        ];

              }



            } else {
              $products_arr['price'] = $currencies->display_price($product_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
              // clear true final price
              $products_arr['calculated_price'] = $currencies->calculate_price($product_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
              $products_arr['calculated_price_exc'] = $currencies->calculate_price($product_price, 0);
              
                if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && $products_arr['products_price'] > $product_price) {
                    $products_arr['price_old'] = $currencies->display_price($products_arr['products_price'], \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
                    $products_arr['price_special'] = $currencies->display_price($product_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));;
                    unset($products_arr['price']);
                }
              
            }
          }

            if (!(isset($settings[0]['show_price']) && $settings[0]['show_price']) && (Info::themeSetting('old_listing') || (isset($settings['itemElements']['priceFrom']) ? $settings['itemElements']['priceFrom'] : false))) {
                $dt = \common\helpers\Product::get_products_discount_table($products_arr['products_id']);
                $minPrice = 0;
                if (is_array($dt)) {
                    for ($i=1, $n=sizeof($dt); $i<=$n; $i=$i+2) {
                        if ($dt[$i] && $dt[$i] < $minPrice || !$minPrice) {
                            $minPrice = $dt[$i];
                        }
                    }
                    if ($minPrice) {
                        $products_arr['price_from'] = $currencies->display_price($minPrice, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
                    }
                }
            }


          if (!(isset($settings[0]['show_image']) && $settings[0]['show_image']) && (Info::themeSetting('old_listing') || ($settings['itemElements']['image'] ?? null))) {
            $products_arr['image'] = Images::getImageUrl($products_arr['products_id'], 'Small');
            $image_tags_arr = Images::getImageTags($products_arr['products_id']);
            $products_arr['image_alt'] = $image_tags_arr['alt_tag'];
            $products_arr['image_title'] = $image_tags_arr['title_tag'];

            $srcsetSizes = Images::getImageSrcsetSizes($products_arr['products_id'], 'Small');
            $products_arr['srcset'] = $srcsetSizes['srcset'];
            $products_arr['sizes'] = $srcsetSizes['sizes'];
            $products_arr['sources'] = $srcsetSizes['sources'];
          }
          if (\frontend\design\Info::themeSetting('group_product_by_product_group')) {
              $products_arr['product_group_params'] = [];
              if ($products_arr['products_groups_id'] && !isset($settings['skip_grouping'])) {
                  $products_arr['product_group_params'] = \frontend\design\boxes\product\Groups::prepareParams(
                      $products_arr['products_id'],
                      0,
                      (isset($settings[0]['hide_out_of_stock_groups']) && $settings[0]['hide_out_of_stock_groups']),
                      (isset($settings[0]['limit_group_products']) ? $settings[0]['limit_group_products'] : []),
                      (isset($settings[0]['hide_single_property']) && $settings[0]['hide_single_property'])
                  );
                  if (!empty($products_arr['product_group_params'])) {
                      $group_name = tep_db_fetch_array(tep_db_query("SELECT products_groups_name FROM " . TABLE_PRODUCTS_GROUPS . " WHERE products_groups_id='" . $products_arr['products_groups_id'] . "' AND language_id='" . \Yii::$app->settings->get('languages_id') . "'"));
                      if (is_array($group_name)) {
                          $products_arr['products_groups_name'] = $group_name['products_groups_name'];
                          if (isset($settings[0]['custom_names']) && !empty($settings[0]['custom_names'][$group_name['products_groups_name']])) {
                              $products_arr['products_groups_name'] = $settings[0]['custom_names'][$group_name['products_groups_name']];
                          }
                      }
                  }
              }
          }

          $products_arr['product_attributes'] = [];
          if (!(isset($settings[0]['show_attributes']) && $settings[0]['show_attributes']) && $products_arr['product_has_attributes'] && (Info::themeSetting('old_listing') || ArrayHelper::getValue($settings, ['itemElements','attributes']))){

              $productSettings = \common\models\ProductsSettings::find()
                  ->where(['products_id' => $products_arr['products_id']])->asArray()->one();
              $products_arr['show_attributes_quantity'] = $productSettings['show_attributes_quantity'] ?? null;

              $attributes = [];
              $products_arr['product_attributes_details'] = \common\helpers\Attributes::getDetails($products_arr['products_id'], $attributes);
              $products_arr['product_attributes'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/listing-product/attributes.tpl', 'params' => ['attributes' => $products_arr['product_attributes_details']['attributes_array'], 'isAjax' => false, 'products_id' => $products_arr['products_id'], 'options_prefix' => $settings['options_prefix']]]);
          }
          if (is_object($cart)) {
            $products_arr['product_in_cart'] = $cart->in_cart($products_arr['products_id']);//Info::checkProductInCart($products_arr['products_id']);
          }

          if (!(isset($settings[0]['show_description']) && $settings[0]['show_description']) && (Info::themeSetting('old_listing') || (isset($settings['itemElements']['description']) && $settings['itemElements']['description']) )) {
            if ($products_arr['products_description']) {
                $products_arr['products_description'] = \common\classes\TlUrl::replaceUrl($products_arr['products_description']);
                $products_arr['products_description_st'] = self::highlightKeywords(strip_tags($products_arr['products_description']));
            }
            //if ($products_arr['products_description_short'])$products_arr['products_description_short'] = strip_tags($products_arr['products_description_short']);

            $products_arr['products_description_short'] = self::highlightKeywords(strip_tags($products_arr['products_description_short']));
          }

          if (isset($settings['itemElements']['brand']) ) {
              $manufacturers = \common\models\Manufacturers::find()
                  ->where(['manufacturers_id' => $products_arr['manufacturers_id']])
                  ->asArray()->one();
              $products_arr['brand'] = $manufacturers['manufacturers_name'];
              $products_arr['brand_url'] = Yii::$app->urlManager->createUrl(['catalog', 'manufacturers_id' => $manufacturers['manufacturers_id']]);
              $products_arr['brand_image'] = \common\classes\Images::getImageSet(
                  $manufacturers['manufacturers_image'],
                  'Brand on product listing',
                  [],
                  Info::themeSetting('na_category', 'hide')
              );
          }

            $products_arr['products_name'] = self::highlightKeywords(strip_tags($products_arr['products_name'] ?? null));

            $settings[0]['product_names_teg'] = (isset($settings[0]['product_names_teg']) ? $settings[0]['product_names_teg'] : '');
            $keywords = Yii::$app->request->get('keywords');
            $keywords = \yii\helpers\Html::encode($keywords);
            if (in_array($settings[0]['product_names_teg'], ['h2', 'h3', 'h4']) && !$keywords){
                if ($products_arr['products_' . $settings[0]['product_names_teg'] . '_tag']) {
                    $heading_arr = explode("\n", $products_arr['products_' . $settings[0]['product_names_teg'] . '_tag']);
                    $products_arr['products_name_teg'] = '<' . $settings[0]['product_names_teg'] . '>'
                        . $heading_arr[0] . '</' . $settings[0]['product_names_teg'] . '>';
                } else {
                    $products_arr['products_name_teg'] = '<' . $settings[0]['product_names_teg'] . '>'
                        . $products_arr['products_name'] . '</' . $settings[0]['product_names_teg'] . '>';
                }
            } elseif ($settings[0]['product_names_teg'] == 'name_h2'){
                $products_arr['products_name_teg'] = '<h2>' . $products_arr['products_name'] . '</h2>';
            } elseif ($settings[0]['product_names_teg'] == 'name_h3'){
                $products_arr['products_name_teg'] = '<h3>' . $products_arr['products_name'] . '</h3>';
            } elseif ($settings[0]['product_names_teg'] == 'name_h4'){
                $products_arr['products_name_teg'] = '<h4>' . $products_arr['products_name'] . '</h4>';
            } else {
                $products_arr['products_name_teg'] = $products_arr['products_name'];
            }

            if (!(isset($settings[0]['show_model']) && $settings[0]['show_model']) && (Info::themeSetting('old_listing') || (isset($settings['itemElements']['model']) && $settings['itemElements']['model']) )) {
                $products_arr['products_model'] = self::highlightKeywords(strip_tags($products_arr['products_model']));
            }

          if (!($settings[0]['show_stock'] ?? false) || !($settings[0]['show_price'] ?? false) || !($settings[0]['show_qty_input'] ?? false)) {

            if ($products_arr['products_pctemplates_id'] > 0 || $products_arr['is_bundle']) {
              $tmp = [];
              $attributes_details = \common\helpers\Attributes::getDetails($products_arr['products_id'], $tmp, []);
              if ($products_arr['products_pctemplates_id'] > 0) {
                    $details = null;
                    if (\common\helpers\Acl::checkExtensionAllowed('ProductConfigurator')) {
                        $details = \common\extensions\ProductConfigurator\helpers\Configurator::getDetails($products_arr, $attributes_details);
                    }
              } else {
                $details = \common\helpers\Bundles::getDetails($products_arr, $attributes_details);
              }

              $products_arr['product_qty'] = $details['product_qty']??0;
              $products_arr['order_quantity_data'] = $details;
              $products_arr['stock_indicator'] = $details['stock_indicator']??null;
              $products_arr['stock_indicator']['max_qty'] = ($details['stock_indicator']['quantity_max']??null);
              foreach (['add_to_cart', 'ask_sample', 'can_add_to_cart', 'request_for_quote', 'display_price_options'] as $flag) {
                $products_arr['stock_indicator']['flags'][$flag] = $details['stock_indicator'][$flag] ?? null;
              }

              if ($products_arr['products_pctemplates_id'] > 0) {
                unset($products_arr['price_old']);
                unset($products_arr['price_special']);
                $products_arr['price'] = $details['configurator_price']??null;
              } else {
                if ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear']) {
                  $products_arr['price_old'] = $details['full_bundle_price'];
                  $products_arr['price_special'] = $details['actual_bundle_price'];
                } else {
                  $products_arr['price'] = $details['actual_bundle_price'];
                }
                $products_arr['calculated_price'] = $details['actual_bundle_price_clear'];
              }
              $products_arr['product_has_attributes'] = true;

            } else {
              $products_quantity = \common\helpers\Product::get_products_stock($products_arr['products_id']);
              $products_arr['product_qty'] = $products_quantity;
              $products_arr['stock_indicator'] = \common\classes\StockIndication::product_info(array(
                  'products_id' => (int)$products_arr['products_id'],
                  'is_virtual' => $products_arr['is_virtual'],
                  'products_quantity' => $products_quantity,
                  'stock_indication_id' => (isset($products_arr['stock_indication_id']) ? $products_arr['stock_indication_id'] : null),
              ));
            }

            $products_arr['order_quantity_data'] = \common\helpers\Product::get_product_order_quantity($products_arr['products_id'], $products_arr);

            if (isset($products_arr['calculated_price']) && abs($products_arr['calculated_price']) < 0.01 && defined('PRODUCT_PRICE_FREE') && PRODUCT_PRICE_FREE == 'true') {
                $products_arr['price'] = TEXT_FREE;
            }

            /**
             * $stock_indicator_public['display_price_options']
             * 0 - display
             * 1 - hide
             * 2 - hide if zero
             */
            /** @var \common\extensions\Quotations\Quotations $ext */
            if (($products_arr['stock_indicator']['flags']['request_for_quote'] && ( ($ext = \common\helpers\Extensions::isAllowed('Quotations')) && !$ext::optionIsPriceShow() ) /*&& $products_arr['stock_indicator']['flags']['display_price_options'] != 0*/) ||
                ($products_arr['stock_indicator']['flags']['display_price_options'] == 1) ||
                (isset($products_arr['calculated_price']) && abs($products_arr['calculated_price'])<0.01 && $products_arr['stock_indicator']['flags']['display_price_options'] == 2)
                ) {
              $products_arr['price'] = '';
              $products_arr['price_special'] = '';
              $products_arr['price_old'] = '';
              $container->attachDetails($products_arr['products_id'], ['promo_icon' => '']);
            }
            $products_arr['stock_indicator']['quantity_max'] = \common\helpers\Product::filter_product_order_quantity((int)$products_arr['products_id'], $products_arr['stock_indicator']['max_qty'], true);
          }

          $products_arr['please_login'] = (Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login') ? sprintf(TEXT_PLEASE_LOGIN, tep_href_link(FILENAME_LOGIN,'','SSL')) : false);

          $products_arr['properties'] = [];
          if (!(isset($settings[0]['show_properties']) && $settings[0]['show_properties']) && (Info::themeSetting('old_listing') || (isset($settings['itemElements']['properties']) && $settings['itemElements']['properties']))) {
            $products_arr['properties'] = self::getProductProperties($products_arr['products_id']);
          }

          if (isset($settings[0]['show_paypal_button']) && $settings[0]['show_paypal_button']) {
            $arr = \common\services\OrderManager::loadManager($cart)->getPaymentCollection()->showPaynowButton(1);
            if (count($arr)){
                $products_arr['show_paypal_button'] = implode("\n", $arr);
            }
          }

            if ($ex=\common\helpers\Acl::checkExtensionAllowed('PersonalCatalog', 'allowed')){
                if ( Yii::$app->getUser()->isGuest ){
                    $products_arr['in_wish_list'] = false;
                }else {
                    $products_arr['in_wish_list'] = $ex::getService()->isInPersonalCatalog(Yii::$app->getUser()->getId(), $products_arr['products_id']);
                }
            }

          if ($extBonusActions = \common\helpers\Extensions::isAllowedAnd('BonusActions', 'isProductPointsEnabled')) {
            $bonuses = $extBonusActions::getProductBonuses($products_arr['products_id'], $customer_groups_id);
            if (is_array($bonuses)) {
                $products_arr = array_merge($products_arr, $bonuses);
            }
          }

          $ret[] = $products_arr;
        }

        $products_details_array = &$ret;
        foreach (\common\helpers\Hooks::getList('catalog/products-list-info') as $filename) {
            include($filename);
        }
      }
      $container->loadList($ret);
      

    }

    return $ret;

  }


/**
 * @deprecated use getListProductsDetails + \common\components\ProductsQuery instead
 * fill in products container with product details 
 * @global type $cart
 * @global int $customer_groups_id
 * @param mysql_query_result $products_query
 * @param array $settings
 * @return array products array (all data according settings)
 */
  public static function getProducts($products_query, $settings = array())
  {
    global $cart;

    $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
    $currencies = \Yii::$container->get('currencies');
    $container = Yii::$container->get('products');
    /**
     * @var $container \common\components\ProductsContainer
     **/
    $products = array();

    $sort_order = 0;

    \common\helpers\Php8::nullArrProps($settings[0], ['show_price', 'show_image', 'show_attributes', 'show_description', 'show_model', 'show_stock', 'show_qty_input', 'show_properties', 'show_paypal_button']);
    while ($products_arr = tep_db_fetch_array($products_query)) {
      $products_arr['listing_type'] = (isset($settings['listing_type'])?$settings['listing_type']:'main');
      $container->loadProducts($products_arr);

      $products_arr = $container->getProduct($products_arr['products_id']);
      $products_arr['id'] = $products_arr['products_id'];
      $price_products_id = $products_arr[\common\helpers\Product::priceProductIdColumn()];
      if (!$settings[0]['show_price']) {
        $special_price = \common\helpers\Product::get_products_special_price($price_products_id);
        if ($special_price) {
          $products_arr['price_old'] = $currencies->display_price(\common\helpers\Product::get_products_price($price_products_id, 1), \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
          $products_arr['price_special'] = $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
          // clear true final price
          $products_arr['calculated_price'] = $special_price;
        } else {
          $products_arr['price'] = $currencies->display_price(\common\helpers\Product::get_products_price($price_products_id, 1), \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
          // clear true final price
          $products_arr['calculated_price'] = $currencies->calculate_price(\common\helpers\Product::get_products_price($price_products_id, 1), \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id']));
        }
      }
      //if (!$settings[0]['show_bonus_points']) {
//        if ($extBonusActions = \common\helpers\Extensions::isAllowedAnd('BonusActions', 'isProductPointsEnabled')) {
//            $bonuses = $extBonusActions::getProductBonuses($products_arr['products_id'], $customer_groups_id);
//            if (is_array($bonuses)) {
//                $products_arr->attachDetails($bonuses);
//            }
//        }
      //}

      $products_arr['link'] = tep_href_link('catalog/product', 'products_id=' . $products_arr['products_id']);
      $products_arr['link_buy'] = tep_href_link('catalog/product', 'action=buy_now&products_id=' . $products_arr['products_id']);
      $products_arr['action'] = Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $products_arr['products_id']]);
      $products_arr['action_buy'] = Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $products_arr['products_id'], 'action' => 'add_product']);
      if (!$settings[0]['show_image']) {
        $products_arr['image'] = Images::getImageUrl($products_arr['products_id'], 'Small');
        $image_tags_arr = Images::getImageTags($products_arr['products_id']);
        $products_arr['image_alt'] = $image_tags_arr['alt_tag'];
        $products_arr['image_title'] = $image_tags_arr['title_tag'];
      }
      $products_arr['product_has_attributes'] = \common\helpers\Attributes::has_product_attributes($price_products_id);
      $products_arr['product_attributes'] = [];
      if ($settings[0]['show_attributes'] && $products_arr['product_has_attributes']){
          $attributes = [];
          $products_arr['product_attributes_details'] = \common\helpers\Attributes::getDetails($price_products_id, $attributes);
          $products_arr['product_attributes'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/listing-product/attributes.tpl', 'params' => ['attributes' => $products_arr['product_attributes_details']['attributes_array'], 'isAjax' => false, 'products_id' => $products_arr['products_id']]]);
      }
      $products_arr['product_in_cart'] = false;
      if ( is_object($cart) ) {
          $products_arr['product_in_cart'] = $cart->in_cart($products_arr['products_id']);//Info::checkProductInCart($products_arr['products_id']);
      }

      if (!$settings[0]['show_description']) {
        if ($products_arr['products_description']) {
            $products_arr['products_description'] = \common\classes\TlUrl::replaceUrl($products_arr['products_description']);
            $products_arr['products_description'] = self::highlightKeywords(strip_tags($products_arr['products_description']));
        }
        //if ($products_arr['products_description_short'])$products_arr['products_description_short'] = strip_tags($products_arr['products_description_short']);

        $products_arr['products_description_short'] = self::highlightKeywords(strip_tags($products_arr['products_description_short']));
      }

        $products_arr['products_name'] = self::highlightKeywords(strip_tags($products_arr['products_name']));
        if (!$settings[0]['show_model']) {
            $products_arr['products_model'] = self::highlightKeywords(strip_tags($products_arr['products_model']));
        }

      if (!$settings[0]['show_stock'] || !$settings[0]['show_price'] || !$settings[0]['show_qty_input']) {
        $stock_products_id = $products_arr[\common\helpers\Product::stockProductIdColumn()];
        $products_quantity = \common\helpers\Product::get_products_stock($stock_products_id);
        $products_arr['product_qty'] = $products_quantity;
        $products_arr['order_quantity_data'] = \common\helpers\Product::get_product_order_quantity($stock_products_id, $products_arr);
        $products_arr['stock_indicator'] = \common\classes\StockIndication::product_info(array(
            'products_id' => (int)$stock_products_id,
            'is_virtual' => $products_arr['is_virtual'],
            'products_quantity' => $products_quantity,
            'stock_indication_id' => (isset($products_arr['stock_indication_id']) ? $products_arr['stock_indication_id'] : null),
        ));
        /**
         * $stock_indicator_public['display_price_options']
         * 0 - display
         * 1 - hide
         * 2 - hide if zero
         */

        if (($products_arr['stock_indicator']['flags']['request_for_quote'] /*&& $products_arr['stock_indicator']['flags']['display_price_options'] != 0*/) ||
            ($products_arr['stock_indicator']['flags']['display_price_options'] == 1) ||
            (abs($products_arr['calculated_price'])<0.01 && $products_arr['stock_indicator']['flags']['display_price_options'] == 2)
            ) {
          $products_arr['price'] = '';
          $products_arr['price_special'] = '';
          $products_arr['price_old'] = '';
          $container->attachDetails($products_arr['products_id'], ['promo_icon' => '']);
        }
        $products_arr['stock_indicator']['quantity_max'] = \common\helpers\Product::filter_product_order_quantity((int)$products_arr['products_id'], $products_arr['stock_indicator']['max_qty'], true);
      }

      $products_arr['properties'] = [];
      if (!$settings[0]['show_properties']) {
        $products_arr['properties'] = self::getProductProperties($products_arr['products_id']);
      }
      
      if ($settings[0]['show_paypal_button']) {
        $arr = \common\services\OrderManager::loadManager($cart)->getPaymentCollection()->showPaynowButton(1);
        if (count($arr)){
            $products_arr['show_paypal_button'] = implode("\n", $arr);
        }
      }

      $products_arr['sort_order'] = $sort_order++;

      $products[] = $products_arr;

    }
    return $products;
  }

  /**
   * @deprecated (keywords could be parsed in different ways)
   * highlight keywords from get params 
   * @param string $text
   * @return string 
   */
  public static function highlightKeywords($text)
  {
        if (MSEARCH_HIGHLIGHT_ENABLE != 'true') {
            return $text;
        }
        $keywords = \Yii::$app->request->get('keywords', '');
        $keywords = \yii\helpers\Html::encode($keywords);
        if (!$keywords || !is_scalar($keywords)) {
            return $text;
        }

        $search_keywords = [];
        if (!\common\helpers\Output::parse_search_string($keywords, $search_keywords, false)) {
            $search_keywords = [$keywords];
        }
        
        $text = \common\helpers\Output::highlight_text($text, $search_keywords);

        return $text;
  }


  public static function getProductProperties($products_id){

    $properties_array = array();
    $values_array = array();
    $properties_tree_array = [];
    $properties_query = tep_db_query("select p.properties_id, if(p2p.values_id > 0, p2p.values_id, p2p.values_flag) as values_id from " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p, " . TABLE_PROPERTIES . " p where p2p.properties_id = p.properties_id and p.display_listing = '1' and p2p.products_id = '" . (int)$products_id . "'");
    while ($properties = tep_db_fetch_array($properties_query)) {
      if (!in_array($properties['properties_id'], $properties_array)) {
        $properties_array[] = $properties['properties_id'];
      }
      $values_array[$properties['properties_id']][] = $properties['values_id'];
    }
    if (count($properties_array) > 0) {
      $properties_tree_array = \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_array);
    }
    return $properties_tree_array;
  }


  public static function getProductsRating($products_id, $field = 'rating'){

    $rating_query = tep_db_query("select count(*) as count, AVG(reviews_rating) as average from " . TABLE_REVIEWS . " where products_id = '" . (int)$products_id . "' and status");
    $rating = tep_db_fetch_array($rating_query);
    
    if ($field == 'count') {
      return $rating['count'];
    } else {
      return round($rating['average']??0);
    }
  }


    public static function getCss()
    {
        $theme_name = THEME_NAME;

        $pageStyle = self::getPageStyle();
        if ($pageStyle) {
            self::addBlockToPageName(str_replace('.s-', '', $pageStyle));
        }

        $development_mode = \frontend\design\Info::themeSetting('development_mode', 'hide');
        if ($development_mode) {

            $devPath = DIR_FS_CATALOG . 'themes/' . $theme_name . '/css/';
            if (!is_file($devPath . 'develop.css')) {
                $style = Style::getCss($theme_name);
                \yii\helpers\FileHelper::createDirectory($devPath);
                file_put_contents($devPath . 'develop.css', $style);
            }

            $versionOfBaseCss = \frontend\design\Info::themeSetting('include_css');
            switch ($versionOfBaseCss) {
                case 1:
                    $css = file_get_contents(Info::themeFile('/css/base.css', 'fs'));
                    break;
                case 2:
                    $css = file_get_contents(Info::themeFile('/css/base_1.css', 'fs'));
                    break;
                case 3:
                    $css = file_get_contents(Info::themeFile('/css/base_3.css', 'fs'));
                    break;
                default:
                    $css = "";
            }

            $code = '
                <script type="text/javascript">
                    tl("' . Info::themeFile('/js/jquery.hotkeys.js') . '", function(){            
                      $(document).bind("keydown", "alt+s", function(){
                        $.get("' . Yii::$app->urlManager->createUrl('get-widget/css-save') . '")
                      });
                    })
                </script>
                <style type="text/css">' . self::fonts() . $css . '</style>
                ';

            $page = '.p-' . Yii::$app->controller->id . '-' . Yii::$app->controller->action->id;
            $widgets = self::getWidgetsNames();
            $areaArr[] = '';
            $areaArr[] = tep_db_input($page);
            foreach ($widgets as $widget) {
                $areaArr[] = tep_db_input($widget);
            }
            foreach ($widgets as $widget) {
                $areaArr[] = tep_db_input($page . ' ' . $widget);
            }
            $area = "'" . implode("','", $areaArr) . "'";

            $query = tep_db_query("select accessibility from " . TABLE_THEMES_STYLES_CACHE . " where theme_name = '" . tep_db_input($theme_name) . "' and accessibility in(" . $area . ")");

            while ($item = tep_db_fetch_array($query)) {
                if (!$item['accessibility']) {
                    $item['accessibility'] = 'main';
                }
                $code .= '
                <link rel="stylesheet" href="' . self::themeFile('/development/style' . $item['accessibility'] . '.css') . '"/>';
            }

            $code .= '
                <style type="text/css">' . \frontend\design\Block::getStyles()
                . \frontend\design\boxes\ProductListing::getStyles() . '</style>';

            return $code;
        }


        $cssFile = Yii::$app->controller->id . '_' . Yii::$app->controller->action->id . Block::$blockNamesStr . self::$extendedPageName . '.css';
        $filePath = DIR_FS_CATALOG . 'themes/' . $theme_name . '/cache/css/';

        if (is_file($filePath . $cssFile) && !self::isAdmin() && @$_SERVER['HTTP_CACHE_CONTROL'] != 'no-cache') {
            return '<style type="text/css">' . file_get_contents($filePath . $cssFile) . self::$scriptCss . '</style>';
        }

        $page = '.p-' . Yii::$app->controller->id . '-' . Yii::$app->controller->action->id;

        // add fonts from theme settings
        $css = self::fonts();

        $css .= file_get_contents(Info::themeFile('/css/base.css', 'fs'));

        // add css for widgets ".p-{page}", ".p-{widget}", ".b-{class}"
        $widgets = self::getWidgetsNames();

        $query = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where theme_name = '" . tep_db_input($theme_name) . "' and setting_group = 'css' and setting_name = 'css'"));
        if (isset($query['setting_value']) && !empty($query['setting_value'])) {
            $css .= $query['setting_value'];
        }

        $areaArr[] = '';
        $areaArr[] = tep_db_input($page);
        foreach ($widgets as $widget) {
            $areaArr[] = tep_db_input($widget);
        }
        foreach ($widgets as $widget) {
            $areaArr[] = tep_db_input($page . ' ' . $widget);
        }

        $areaArr[] = $pageStyle;
        $area = "'" . implode("','", $areaArr) . "'";

        $query = tep_db_query("select css, accessibility from " . TABLE_THEMES_STYLES_CACHE . " where theme_name = '" . tep_db_input($theme_name) . "' and accessibility in(" . $area . ")");

        while ($item = tep_db_fetch_array($query)) {
            $css .= $item['css'];
            if ($item['css']) {
                unset($areaArr[array_search($item['accessibility'], $areaArr)]);
            }
        }
        if (is_array($areaArr) && count($areaArr) > 0) {
            foreach (Info::$themeMap as $theme) {
                if ($theme == THEME_NAME || $theme == 'basic') {
                    continue;
                }
                $area = "'" . implode("','", $areaArr) . "'";
                $query = tep_db_query("select css, accessibility from " . TABLE_THEMES_STYLES_CACHE . " where theme_name = '" . tep_db_input($theme) . "' and accessibility in(" . $area . ")");
                while ($item = tep_db_fetch_array($query)) {
                    $css .= $item['css'];
                    if ($item['css']) {
                        unset($areaArr[array_search($item['accessibility'], $areaArr)]);
                    }
                }
            }
        }

        //add widgets for blocks "#box-{id}"
        $css .= \frontend\design\Block::getStyles();
        $css .= \frontend\design\boxes\ProductListing::getStyles();


        $css = self::minifyCss($css);

        \yii\helpers\FileHelper::createDirectory($filePath);
        file_put_contents($filePath . $cssFile, $css);

        return '<style type="text/css">' . $css . self::minifyCss(self::$scriptCss) . '</style>';
    }


    public static function minifyCss($css)
    {
        $css = preg_replace('/[\s]+/', ' ', $css);
        $css = str_replace(' }', '}', $css);
        $css = str_replace('} ', '}', $css);
        $css = str_replace(' {', '{', $css);
        $css = str_replace('{ ', '{', $css);
        $css = str_replace(': ', ':', $css);
        $css = str_replace('; ', ';', $css);
        $css = str_replace(';}', '}', $css);
        $css = preg_replace('/[^{^}]+{}/', '', $css);
        $css = preg_replace('/([^0-9^.])0px/', '${1}0', $css);

        return $css;
    }

    public static function getCssArray($theme_name, $page)
    {
        $widgets = self::getWidgetsNames();
        $css = array();

        $areaArr[] = tep_db_input($page);
        foreach ($widgets as $widget) {
            $areaArr[] = tep_db_input($widget);
        }
        foreach ($widgets as $widget) {
            $areaArr[] = tep_db_input($page . ' ' . $widget);
        }
        $area = "'" . implode("','", $areaArr) . "'";

        $query = tep_db_query("select css, accessibility from " . TABLE_THEMES_STYLES_CACHE . " where theme_name = '" . tep_db_input($theme_name) . "' and accessibility in(" . $area . ")");

        while ($item = tep_db_fetch_array($query)) {
            $css[$item['accessibility']] = $item['css'];
        }
        $css['blocks'] = \frontend\design\Block::getStyles() . \frontend\design\boxes\ProductListing::getStyles();

        return $css;
    }

  public static function getStyle($theme_name, $tmp = false, $visibility = '')
  {

    $styles_query = tep_db_query("select * from " . TABLE_THEMES_STYLES . " where theme_name = '" . $theme_name . "' and visibility='" . $visibility . "'");

    $styles_array = array();
    $styles_groups = array();
    while ($item = tep_db_fetch_array($styles_query)){

      $styles_groups[$item['selector']][0][$item['attribute']] = $item['value'];

    }

    foreach ($styles_groups as $selector => $style){
      $text = \frontend\design\Block::styles($style);
      $styles_array[$selector] = $text;
    }

    $vArr = Style::vArr($visibility);

    $css = '';
    if (in_array(1, $vArr) ||in_array(2, $vArr) ||in_array(3, $vArr) ||in_array(4, $vArr)) {
      $add = '';
      if (in_array(2, $vArr)){
          $add .= '.active';
      }
      if (in_array(3, $vArr)){
          $add .= ':before';
      }
      if (in_array(4, $vArr)){
          $add .= ':after';
      }
      if (in_array(1, $vArr)){
          $add .= ':hover';
      }
      foreach ($styles_array as $key => $value){
        $key_arr = explode(',', $key);
        $selector_arr = array();
        foreach ($key_arr as $item){
          $selector_arr[] = trim($item) . $add;
        }
        $selector = implode(', ', $selector_arr);
        if ($value) {
          $css .= $selector . '{' . $value . '} ';
        }
      }
    } else {
      foreach ($styles_array as $key => $value){
        if ($value) {
          $css .= $key . '{' . $value . '} ';
        }
      }
    }
    
    if ($visibility == ''){
      $css .= self::getStyle($theme_name, $tmp, 1);
      $css .= self::getStyle($theme_name, $tmp, 2);
      $css .= self::getStyle($theme_name, $tmp, 3);
      $css .= self::getStyle($theme_name, $tmp, 4);

      $media_query_arr = tep_db_query("select * from " . TABLE_THEMES_SETTINGS . " where setting_name = 'media_query'");
      while ($item = tep_db_fetch_array($media_query_arr)){
        $arr = explode('w', $item['setting_value']);
        $css .= '@media';
        if ($arr[0]){
          $css .= ' (min-width:' . $arr[0] . 'px)';
        }
        if ($arr[0] && $arr[1]){
          $css .= ' and ';
        }
        if ($arr[1]){
          $css .= ' (max-width:' . $arr[1] . 'px)';
        }
        $css .= '{';
        //$css .= $block_styles[$item['id']];
        $css .= self::getStyle($theme_name, $tmp, $item['id']);
        $css .= '} ';
      }
    }

    return $css;

  }


    public static function widgetsArr($name, $include_blocks = false, $theme_name = '')
    {
        static $_cache = [];

        $controller = Yii::$app->controller->id;
        $action = Yii::$app->controller->action->id;

        if (!$theme_name && defined("THEME_NAME")) {
            $theme_name = THEME_NAME;
        }
        if (!$theme_name) {
            return [];
        }

        $cache_key = (string)$name.'^'.(int)$include_blocks.'^'.(string)$controller.'^'.(string)$action.'^'.(string)$theme_name;

        if ( !isset($_cache[$cache_key]) ) {
            $widgets = array();

            $query = tep_db_query("select id, widget_name from " . TABLE_DESIGN_BOXES . " where theme_name = '" . tep_db_input($theme_name) . "' and block_name = '" . tep_db_input($name) . "'");

            while ($item = tep_db_fetch_array($query)) {

                $settings = array();
                $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "' and visibility = ''");
                while ($set = tep_db_fetch_array($settings_query)) {
                    $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
                }

//                $controller = Yii::$app->controller->id;
//                $action = Yii::$app->controller->action->id;

                $cookies = Yii::$app->request->cookies;

                if (
                    !(
                        !($settings[0]['visibility_first_view'] ?? null) && Yii::$app->user->isGuest && !$cookies->has('was_visit') ||
                        !($settings[0]['visibility_more_view'] ?? null) && Yii::$app->user->isGuest && $cookies->has('was_visit') ||
                        !($settings[0]['visibility_logged'] ?? null) && !Yii::$app->user->isGuest ||
                        !($settings[0]['visibility_not_logged'] ?? null) && Yii::$app->user->isGuest
                    ) ||

                    $controller == 'index' && $action == 'index' && (isset($settings[0]['visibility_home']) && $settings[0]['visibility_home']) ||
                    $controller == 'catalog' && $action == 'product' && (isset($settings[0]['visibility_product']) && $settings[0]['visibility_product']) ||
                    $controller == 'catalog' && $action == 'index' && (isset($settings[0]['visibility_catalog']) && $settings[0]['visibility_catalog']) ||
                    $controller == 'info' && $action == 'index' && (isset($settings[0]['visibility_info']) && $settings[0]['visibility_info']) ||
                    $controller == 'cart' && $action == 'index' && (isset($settings[0]['visibility_cart']) && $settings[0]['visibility_cart']) ||
                    $controller == 'checkout' && $action != 'success' && (isset($settings[0]['visibility_checkout']) && $settings[0]['visibility_checkout']) ||
                    $controller == 'checkout' && $action == 'success' && (isset($settings[0]['visibility_success']) && $settings[0]['visibility_success']) ||
                    $controller == 'account' && $action != 'login' && (isset($settings[0]['visibility_account']) && $settings[0]['visibility_account']) ||
                    $controller == 'account' && $action == 'login' && (isset($settings[0]['visibility_login']) && $settings[0]['visibility_login'])
                  ){
                } elseif(
                    !($controller == 'index' && $action == 'index' ||
                      $controller == 'index' && $action == 'design' ||
                      $controller == 'catalog' && $action == 'product' ||
                      $controller == 'catalog' && $action == 'index' ||
                      $controller == 'info' && $action == 'index' ||
                      $controller == 'cart' && $action == 'index' ||
                      $controller == 'checkout' && $action != 'success' ||
                      $controller == 'checkout' && $action == 'success' ||
                      $controller == 'account' && $action != 'login' ||
                      $controller == 'account' && $action == 'login') &&
                    (isset($settings[0]['visibility_other']) && $settings[0]['visibility_other'])
                  ) {
                } else {
                    if ($item['widget_name'] != 'BlockBox' && $item['widget_name'] != 'Tabs' || $include_blocks) {
                      $widgets['id-' . $item['id']] = $item['widget_name'];
                    }
                    $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'], false, $theme_name));
                    $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-1', false, $theme_name));
                    $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-2', false, $theme_name));
                    $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-3', false, $theme_name));
                    $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-4', false, $theme_name));
                    $widgets = array_merge($widgets, Info::widgetsArr('block-' . $item['id'] . '-5', false, $theme_name));
                }
            }
            $_cache[$cache_key] = $widgets;
        }

        return $_cache[$cache_key];
    }


  public static function pageBlock() {
    global $current_category_id;

    $controller = Yii::$app->controller->id;
    $action = Yii::$app->controller->action->id;

    $block_name = '';
    if ($controller == 'index' && $action == 'index'){
      $block_name = 'main';
    } elseif ($controller == 'info' && $action == 'index'){
      $block_name = 'info';
    } elseif ($controller == 'shopping-cart' && $action == 'index'){
      $block_name = 'cart';
    } elseif ($controller == 'checkout' && $action == 'success'){
      $block_name = 'success';
    } elseif ($controller == 'catalog' && ($action == 'product' || substr($action,0,8) == 'product-')){ //configurator attributes and other ajax
      $block_name = 'product';
      if ( isset(Yii::$app->controller->view) && is_object(Yii::$app->controller->view) && !empty(Yii::$app->controller->view->page_name) ) {
        $block_name = Yii::$app->controller->view->page_name;
      }
    } elseif ($controller == 'pdf' && ($action == 'index')){
      $block_name = 'pdf';
    } elseif ($controller == 'pdf' && ($action == 'cover')){
      $block_name = 'pdf_cover';
    } elseif ($controller == 'catalog' && $action == 'index'){
        $manufacturers_id =  (int) \Yii::$app->storage->get('manufacturers_id');
      /*
      $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "' and categories_status = 1");
      $category_parent = tep_db_fetch_array($category_parent_query);
      $parent = ($manufacturers_id ? '0' : $category_parent['total'] );
      */
      if ( $manufacturers_id >0 ) {
          $parent = 0;
      }else{
          $parent = \common\helpers\Categories::has_category_subcategories((int)$current_category_id)?1:0;
      }

      if ($parent > 0){
        $block_name = 'categories';
      } else {
        $block_name = 'products';
      }
    } elseif ($controller == 'catalog' && (
//        $action == 'advanced-search-result' ||
        $action == 'advanced-search' ||
        $action == 'featured-products' ||
        $action == 'products-new' ||
        $action == 'all-products' ||
        $action == 'personal-catalog' ||
        $action == 'sales'
      )){
        $block_name = 'products';
    } elseif ($controller == 'email-template' && $action == 'index'){
        $block_name = 'email';
    } elseif ($controller == 'catalog' && $action == 'gift'){
        $block_name = 'gift_card';
    } elseif ($controller == 'orders' && $action == 'packingslip'){
        $block_name = 'packingslip';
    } elseif ($controller == 'orders' && $action == 'invoice'){
        $block_name = 'invoice';
    } elseif ($controller == 'orders'){
        $block_name = 'orders';
    } elseif ($controller == 'account' && $action == 'trade-form-pdf'){
        $block_name = 'trade_form_pdf';
    }
    
    return $block_name;
  }
  
  public static function widgets()
  {
    $widgets = array();

    $widgets = array_merge($widgets, Info::widgetsArr('header'));
    $widgets = array_merge($widgets, Info::widgetsArr('footer'));

    $block_name = Info::pageBlock();

    if ($block_name){
      $widgets = array_merge($widgets, Info::widgetsArr($block_name));
    }

    return $widgets;

  }

  public static function widgetSettingsById($boxId, $setting_name = false) {
      $query = tep_db_query(
          "select setting_name, setting_value, language_id ".
          "from " . (Info::isAdmin() ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " ".
          "where	box_id = '".tep_db_input($boxId)."'" . ($setting_name ? " and setting_name = '" . $setting_name . "'" : '') . " and visibility = ''"
      );

      $settings = array();
      while ($item = tep_db_fetch_array($query)){
          $settings[$item['language_id']][$item['setting_name']] = $item['setting_value'];
      }
      if ($setting_name) {
          $settings = $settings[0][$setting_name];
      }
      return $settings;
  }
/**
 *
 * @param string $widget_name
 * @param string  $setting_name
 * @param string $block_name
 * @param type $include_blocks
 * @param string $theme_name
 * @return type
 */
  public static function widgetSettings($widget_name = false, $setting_name = false, $block_name = false, $include_blocks = false, $theme_name = '') {

    $settings = array();
    
    if (!$block_name){
      $block_name = Info::pageBlock();
    }

    $widgets = Info::widgetsArr($block_name, $include_blocks, $theme_name);
    if ($widget_name){
        if (!array_search($widget_name, $widgets)) {
            return false;
        }

      $query = tep_db_query("select setting_name, setting_value, language_id from " . (Info::isAdmin() ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " where	box_id = '" . str_replace('id-', '', array_search($widget_name, $widgets)) . "'" . ($setting_name ? " and setting_name = '" . $setting_name . "'" : '') . " and visibility = ''");

      $settings = array();
      while ($item = tep_db_fetch_array($query)){
        $settings[$item['language_id']][$item['setting_name']] = $item['setting_value'];
      }
      if ($setting_name) {
        $settings = (isset($settings[0][$setting_name]) ? $settings[0][$setting_name] : '');
      }
      
    } else {
      foreach ($widgets as $key => $box){

        $query = tep_db_query("select setting_name, setting_value, language_id from " . (Info::isAdmin() ? TABLE_DESIGN_BOXES_SETTINGS_TMP : TABLE_DESIGN_BOXES_SETTINGS) . " where 	box_id = '" . str_replace('id-', '', $key) . "'" . ($setting_name ? " and setting_name = '" . $setting_name . "'" : '') . " and visibility = ''");

        while ($item = tep_db_fetch_array($query)){
          $settings[$box][] = array($item[$item['language_id']]['setting_name'] => $item['setting_value']);
        }
        if ($setting_name) {
          $settings[$box] = $settings[$setting_name];
        }
      }
    }

    
    

    return $settings;
  }


  public  static function platformData($platform_id = 0){
      if(!$platform_id){
          if (defined('PLATFORM_ID') && PLATFORM_ID) {
              $platform_id = PLATFORM_ID;
          } else {
              $platform_id = \common\classes\platform::defaultId();
          }
      }
      $platform_config = Yii::$app->get('platform')->getConfig($platform_id);
      if ($platform_config->isMarketPlace()) {
          $platform_id = \common\classes\platform::defaultId();
      }
      
       $times = array();
      if ($platform_id){
        \common\helpers\Translation::forceConst(['TEXT_DAY_MO', 'TEXT_DAY_TU', 'TEXT_DAY_WE', 'TEXT_DAY_TH', 'TEXT_DAY_FR', 'TEXT_DAY_SA', 'TEXT_DAY_SU'], 'main');
        foreach(Yii::$app->get('platform')->getConfig($platform_id)->getOpenHours() as $item){
            $allDays = ['', TEXT_DAY_MO, TEXT_DAY_TU, TEXT_DAY_WE, TEXT_DAY_TH, TEXT_DAY_FR, TEXT_DAY_SA, TEXT_DAY_SU];
            $days = explode(',', $item['days']);
            $item['days_arr'] = [];
            foreach ($days as $dayItem) {
                if ($dayItem) $item['days_arr'][] = $allDays[(int)$dayItem];
            }
          $day = str_replace('0,', '', $item['days']);
          $day = str_replace('1', TEXT_DAY_MO, $day);
          $day = str_replace('2', TEXT_DAY_TU, $day);
          $day = str_replace('3', TEXT_DAY_WE, $day);
          $day = str_replace('4', TEXT_DAY_TH, $day);
          $day = str_replace('5', TEXT_DAY_FR, $day);
          $day = str_replace('6', TEXT_DAY_SA, $day);
          $day = str_replace('7', TEXT_DAY_SU, $day);
          $day = str_replace(',', ', ', $day);
          $item['days_short'] = $day;
          if (str_replace('0,', '', $item['days']) == '1,2,3,4,5') $item['days_short'] = TEXT_DAY_MO . ' - ' . TEXT_DAY_FR;
          $times['open'][] = $item;
        }
      }
      
      if ($ext = \common\helpers\Acl::checkExtensionAllowed('AdditionalPlatforms', 'allowed')){
          if ($ext::checkSattelite()){
              $platform_id = $ext::getSatteliteId();
              $platform_config = Yii::$app->get('platform')->getConfig($platform_id);
          }
      }
    /*$query1 = tep_db_fetch_array(tep_db_query("
      select 
        platform_id as id,
        platform_owner as owner,
        platform_name as title,
        platform_url as url,
        platform_email_address as email_address,
        platform_email_from as email_from,
        platform_email_extra as email_extra,
        platform_telephone as telephone,
        platform_landline as landline
      from " . TABLE_PLATFORMS . "
      where platform_id = '" . $platform_id . "'"));*/
    $query1 = $platform_config->getPlatformData();
    $query2 = $platform_config->getPlatformAddress();
    /*tep_db_fetch_array(tep_db_query("
      select
        	entry_company as company,
        	entry_company_vat as company_vat,
        	entry_postcode as postcode,
        	entry_street_address as street_address,
        	entry_suburb as suburb,
        	entry_city as city,
        	entry_state as state,
        	entry_country_id as country_id,
        	entry_zone_id as zone_id,
          entry_company_reg_number as reg_number
      from " . TABLE_PLATFORMS_ADDRESS_BOOK . "
      where platform_id = '" . $platform_id . "' and is_default = 1"));*/

    if ($query1 && $query2){
       foreach($query1 as $name => $value){
           $newName = preg_replace("/platform_/","", $name);
           $query1[$newName] = $value;
       }
      $query1['is_virtual'] = $platform_config->isVirtual();
      $query2['country'] = \common\helpers\Country::get_country_name($query2['country_id']);
      $query2['country_info'] = \common\helpers\Country::get_country_info_by_id($query2['country_id']);

      if (!$query2['state'] && (int)$query2['zone_id']) {
          $zone = Zones::findOne(['zone_id' => $query2['zone_id']]);
          if ($zone) {
              $query2['state'] = $zone->zone_name;
          }
      }

      $data = array_merge($query1, $query2, $times);

      return $data;
    } else {
      return array();
    }

  }

  public  static function themeFile($file_path, $visibility = 'ws', $version = true)
  {
      if (!$visibility) {
          $visibility = 'ws';
      }
    if (defined('DIR_WS_THEME')) {
        $url = DIR_WS_THEME . $file_path;
        if ($visibility == 'fs'){
            $url = Yii::getAlias('@site_root').substr($url, strlen(Yii::getAlias('@web')));
        }
    } else {
        if ($visibility == 'ws') {
            $url = DIR_WS_CATALOG . 'themes/basic' . $file_path;
        } elseif ($visibility == 'fs'){
            $url = DIR_FS_CATALOG . 'themes/basic' . $file_path;
        }
    }
    if (is_array(self::$themeMap) && count(self::$themeMap) > 0) {
        for ($i = count(self::$themeMap) - 1; $i >= 0; $i--){

          if (file_exists(DIR_FS_CATALOG . 'themes/' . self::$themeMap[$i] . $file_path)) {
            if ($visibility == 'ws') {
              //$url = DIR_WS_CATALOG . 'themes/' . self::$themeMap[$i] . $file_path;
              $url = \common\helpers\Media::getAlias('@webThemes/'.self::$themeMap[$i].$file_path);
            } elseif ($visibility == 'fs'){
              $url = DIR_FS_CATALOG . 'themes/' . self::$themeMap[$i] . $file_path;
            }
          }
        }
    }

    if ($version && $visibility == 'ws') {
        $url .= '?' . self::themeSetting('theme_version', 'hide');
    }

    return $url;
  }


  public static function blockWidthMultiplier($id){
    $query = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id='" . $id . "'"));

    $query['block_name'] = $query['block_name'] ?? '';
    if (substr($query['block_name'], 0, 5) != 'block'){
      return false;
    }

    $id_arr = explode('-', substr($query['block_name'], 6));
    if ($id_arr[1]){
      $col = $id_arr[1];
    } else {
      $col = 1;
    }
    $parent_id = $id_arr[0];
    $query = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id='" . $parent_id . "' and setting_name='block_type' and visibility = ''"));
    $type = $query['setting_value'];

    $multiplier = 1;
    if ($type == 2 || $type == 8 && $col == 2){
      $multiplier = 0.5;
    } elseif ($type == 9 && $col == 1 || $type == 10 && $col == 2 || $type == 13 && ($col == 1 || $col == 3) || $type ==15){
      $multiplier = 0.2;
    } elseif ($type == 6 && $col == 1 || $type == 7 && $col == 2 || $type == 8 && ($col == 1 || $col == 3) || $type == 14){
      $multiplier = 0.25;
    } elseif ($type == 3 || $type == 4 && $col == 2 || $type == 5 && $col == 1){
      $multiplier = 0.3333;
    } elseif ($type == 11 && $col == 1 || $type == 12 && $col == 2){
      $multiplier = 0.4;
    } elseif ($type == 11 && $col == 2 || $type == 12 && $col == 1 || $type == 13 && $col == 2){
      $multiplier = 0.6;
    } elseif ($type == 4 && $col == 1 || $type == 5 && $col == 2){
      $multiplier = 0.6666;
    } elseif ($type == 6 && $col == 2 || $type == 7 && $col == 1){
      $multiplier = 0.75;
    } elseif ($type == 9 && $col == 2 || $type == 10 && $col == 1){
      $multiplier = 0.8;
    }

    $query = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id='" . $parent_id . "' and setting_name='padding_left' and visibility = ''"));
    $padding = $query['setting_value'];
    $query = tep_db_fetch_array(tep_db_query("select setting_value from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id='" . $parent_id . "' and setting_name='padding_right' and visibility = ''"));
    $padding = $padding + $query['setting_value'];

    $arr = Info::blockWidthMultiplier($parent_id);
    if (!$arr){
      $arr = array();
    }
    $arr[] = array('multiplier' => $multiplier, 'padding' => $padding);

    return $arr;
  }
  
  public static function blockWidth($id, $p_width = 680){
    $p_width_arr = (array)Info::blockWidthMultiplier($id);
    foreach ($p_width_arr as $item1) {
      if (!($item1['multiplier'] ?? null)) $item1['multiplier'] = 1;
      $p_width = ($p_width - ($item1['padding'] ?? null)) * $item1['multiplier'];
    }
    return floor($p_width);
  }
  
  public static function themeSetting($setting_name, $setting_group = 'main', $theme_name = ''){
      if (Yii::$app->id == 'app-console'){
          $params = [];
      }else {
          $params = Yii::$app->request->get();
      }
    if (!$theme_name && isset($params['theme_name'])) {
        $theme_name = filter_var($params['theme_name'], FILTER_SANITIZE_STRING);
    } elseif (defined("THEME_NAME")) {
        $theme_name = THEME_NAME;
    } elseif (!$theme_name) {
        return false;
    }

    static $cache = [];
    $cache_key = strval($setting_name).'^'.strval($setting_group).'^'.strval($theme_name);
    if ( isset($cache[$cache_key]) ) return $cache[$cache_key];

    $db_query = tep_db_query("select setting_value from " . TABLE_THEMES_SETTINGS . " where setting_group = '" . tep_db_input($setting_group) . "' and setting_name = '" . tep_db_input($setting_name) ."' and theme_name = '" . tep_db_input($theme_name) . "'");
    if (tep_db_num_rows($db_query) > 1 || $setting_group == 'extend'){

      $arr = array();
      while ($item = tep_db_fetch_array($db_query)){
        $arr[] = $item['setting_value'];
      }
      $cache[$cache_key] = $arr;

    } else {
      $query = tep_db_fetch_array($db_query);
      $cache[$cache_key] = isset($query['setting_value']) ? $query['setting_value'] : false;
    }

    return $cache[$cache_key];
  }

  public static function listType($settings){
    
    $list_type = 'productListing';
    \common\helpers\Php8::nullArrProps($settings, ['listing_type', 'listing_type_rows', 'listing_type_b2b']);

    if ((isset($settings['list_type']) && $settings['list_type']) && !$settings['listing_type_rows'] && !$settings['listing_type_b2b']){
      $list_type = $settings['list_type'];
    } else {
      $gl = $_SESSION['gl'];
      if ($gl == 'grid' && $settings['listing_type'] && $settings['listing_type'] != 'no'){
        $list_type = $settings['listing_type'];
      } elseif (($gl == 'list' || $settings['listing_type'] == 'no') && $settings['listing_type_rows'] && $settings['listing_type_rows'] != 'no') {
        $list_type = $settings['listing_type_rows'];
      } elseif ($gl == 'b2b' && $settings['listing_type_b2b']) {
        $list_type = $settings['listing_type_b2b'];
      } elseif ($settings['listing_type']) {
        $list_type = $settings['listing_type'];
      }

    }
    
    return $list_type;
  }


  public static function checkProductInCart($uprid){

    global $cart;
	
    if (self::isTotallyAdmin()) return ;
    
    $products = $cart->get_contents();

    $uprid = \common\helpers\Inventory::normalize_id($uprid);
    
    //return false;
    if (defined('CHECK_BUNDLE_PARTS_IN_CART') && CHECK_BUNDLE_PARTS_IN_CART=='False') {
    // nice to have q-ty instead of bool return $cart->get_quantity($uprid);
      $inCart = array_keys($products);
    } else {
      $inCart = array_map(['\common\helpers\Inventory', 'normalize_id'], array_keys($products));
    }
    return in_array($uprid, $inCart);
    
  }
  
  public static function platformLanguages(){
      return \Yii::$app->get('platform')->config()->getAllowedLanguages();
  }
  
  public static function platformDefLanguage(){
      return \Yii::$app->get('platform')->config()->getDefaultLanguage();
  }
  
  public static function platformCurrencies(){
      return \Yii::$app->get('platform')->config()->getAllowedCurrencies();
  }
  
  public static function platformDefCurrency(){
      return \Yii::$app->get('platform')->config()->getDefaultCurrency();
  }

  public static function get_gl(){
    return $_SESSION['gl'];
  }

  public static function fonts(){
    $fonts = self::themeSetting('font_added', 'extend');

    $css = '';
    if (is_array($fonts)) {
      foreach ($fonts as $font) {
        $css .= $font . "\n";
      }
    }

    return $css;
  }

  public static function sortingId(){

    $get = Yii::$app->request->get();
    $sorting_id = '';
    if (isset($get['sort'])) {
      $sorting_id = $get['sort'];
    } else {
      if (true) {
        global $current_category_id;
        $sorting_id = \common\helpers\Sorting::getDefaultSortOrder($current_category_id);
      } else {
      ///was first in the list in design theme...
      $settings = self::widgetSettings('catalog\Sorting');
      if (!isset($settings[0]['sort_pos_0'])) {
          $settings = self::widgetSettings('ListingFunctionality');
      }

      $arr = array();
      for ($i=0; $i < 18; $i++){
        if (!$settings[0]['sort_hide_' . $i]){
          $arr[$settings[0]['sort_pos_' . $i]] = 'sort_pos_' . $i;
        }
      }
      ksort($arr);
      $key = array_shift($arr);
      switch ($key){
        case 'sort_pos_0': $sorting_id = 0; break;
        case 'sort_pos_1': $sorting_id = 'ma'; break;
        case 'sort_pos_2': $sorting_id = 'md'; break;
        case 'sort_pos_3': $sorting_id = 'na'; break;
        case 'sort_pos_4': $sorting_id = 'nd'; break;
        case 'sort_pos_5': $sorting_id = 'ba'; break;
        case 'sort_pos_6': $sorting_id = 'bd'; break;
        case 'sort_pos_7': $sorting_id = 'pa'; break;
        case 'sort_pos_8': $sorting_id = 'pd'; break;
        case 'sort_pos_9': $sorting_id = 'qa'; break;
        case 'sort_pos_10': $sorting_id = 'qd'; break;
        case 'sort_pos_11': $sorting_id = 'wa'; break;
        case 'sort_pos_12': $sorting_id = 'wd'; break;
        case 'sort_pos_13': $sorting_id = 'da'; break;
        case 'sort_pos_14': $sorting_id = 'dd'; break;
        case 'sort_pos_15': $sorting_id = 'ya'; break;
        case 'sort_pos_16': $sorting_id = 'yd'; break;
          default: $sorting_id = 0; break;
      }
      }
    }


    return $sorting_id;
  }

  public static function themeImage($img, $alternative_images = false, $na = true, $theme_name = ''){

      if (defined('THEME_NAME') && !$theme_name){
          $theme_name = THEME_NAME;
      }
    if (\common\helpers\System::isFrontend()){
      $app = Yii::getAlias('@webroot') . '/';
    } else {
      $app = Yii::getAlias('@webroot') . '/../';
    }
    
    if (is_file($app . $img)) {
      return $img;
    } 
    
    if (is_file($app . 'images/' . $img)) {
      return 'images/' . $img;
    }

    if ($alternative_images && is_array($alternative_images)){
      foreach ($alternative_images as $image){
        if (is_file($app . $image)) {
          return $image;
        }
        if (is_file($app . 'images/' . $image)) {
          return 'images/' . $image;
        }
      }
    }
    
    if ($theme_name && $na){
      if (is_file($app . 'themes/' . $theme_name . '/img/na.png')) {
        return 'themes/' . $theme_name . '/img/na.png';
      }
    }
    if (is_file($app . 'images/na.png') && $na) {
      return 'images/na.png';
    }

    return false;
  }

    public static function getWidgetsNames()
    {
        global $allWidgetsOnPage, $allBoxesOnPage, $allCustomClasses;
        $arr = array();

        if (is_array($allWidgetsOnPage)) {
            foreach ($allWidgetsOnPage as $name) {
                $arr[] = self::nameToClass($name);
            }
        }

        if (is_array($allBoxesOnPage)) {
            foreach ($allBoxesOnPage as $name) {
                $arr[] = '.b-' . $name;
            }
        }

        if (is_array($allCustomClasses)) {
            foreach ($allCustomClasses as $name) {
                $arr[] = '.' . $name;
            }
        }

        return $arr;
    }

    public static function getBoxesNames()
    {
        global $allBoxesOnPage;

        if (!self::themeSetting('development_mode', 'hide')) {
            return '';
        }

        $names = '';
        if (is_array($allBoxesOnPage)) {
            foreach ($allBoxesOnPage as $name) {
                $names .= ' b-' . $name;
            }
        }

        return $names;
    }

    public static function nameToClass($name)
    {
        $class = preg_replace('/([A-Z])/', "-\$1", $name);
        $class = str_replace('\\', '-', $class);
        $class = '.w-' . $class;
        $class = str_replace('--', '-', $class);
        $class = strtolower($class);

        return $class;
    }

    public static function addBlockToWidgetsList($name)
    {
        global $allWidgetsOnPage;

        $allWidgetsOnPage[$name] = $name;
    }

    public static $extendedPageName = '';
    public static $extendedPageNameArr = [];
    public static function addBlockToPageName($name)
    {
        if (!in_array($name, self::$extendedPageNameArr)) {
            self::$extendedPageNameArr[] = $name;
            self::$extendedPageName .= '_' . $name;
        }
    }

    public static function addBoxToCss($name)
    {
        global $allBoxesOnPage;

        $allBoxesOnPage[$name] = $name;
    }

    public static function addCustomClassToCss($name)
    {
        global $allCustomClasses;

        $allCustomClasses[$name] = $name;
    }


    public static function chooseTemplate ($pageType, $defaultPage = '', $rule = '')
    {
        $get = Yii::$app->request->get();

        $query = tep_db_query("
            select aps.setting_value as rule, ap.setting_value as page_title
            from " . TABLE_THEMES_SETTINGS . " ap left join " . TABLE_THEMES_SETTINGS . " aps on ap.setting_value = aps.setting_name
            where
                ap.theme_name = '" . THEME_NAME . "' and
                aps.theme_name = '" . THEME_NAME . "' and
                ap.setting_group = 'added_page' and
                aps.setting_group = 'added_page_settings' and
                ap.setting_name = '" . $pageType . "'");

        $cookies = Yii::$app->request->cookies;

        while ($page = tep_db_fetch_array($query)) {
            if ($rule && $rule != $page['rule']) {
                continue;
            }
            switch ($page['rule']) {
                case 'first_visit':
                    if (Yii::$app->user->isGuest && !$cookies->has('was_visit')) {
                        return $page['page_title'];
                    }
                    break;
                case 'more_visits':
                    if (Yii::$app->user->isGuest && $cookies->has('was_visit')) {
                        return $page['page_title'];
                    }
                    break;
                case 'logged_customer':
                    if (!Yii::$app->user->isGuest) {
                        return $page['page_title'];
                    }
                    break;
                case 'not_logged':
                    if (Yii::$app->user->isGuest) {
                        return $page['page_title'];
                    }
                    break;
            }
        }

        return $defaultPage;
    }

    public static function getThemeName($platformId)
    {

        $device = Yii::$app->request->get('device');

        $theme_array = tep_db_fetch_array(tep_db_query("select t.theme_name from " . TABLE_PLATFORMS_TO_THEMES . " AS p2t INNER JOIN " . TABLE_THEMES . " as t ON (p2t.theme_id=t.id) where p2t.is_default = '1' and p2t.platform_id = " . (int)$platformId));

        $theme = 'theme-1';

        if ($theme_array['theme_name'] ?? null){
            $theme = $theme_array['theme_name'];
        }

        if (self::themeSetting('use_mobile_theme', 'main', $theme)) {

            $cookies = Yii::$app->response->cookies;
            if ($device && ($device == 'mobile' || $device == 'desktop')) {
                $cookies->add(new \yii\web\Cookie([
                    'name' => 'device',
                    'value' => $device,
                    'expire' => time() + 3600*24*365
                ]));
            }

            $detect = new \Mobile_Detect;

            $cookiesDevice = $cookies->get('device');
            if (
                ($detect->isMobile() && !$detect->isTablet() && $cookiesDevice != 'desktop')
                || $cookiesDevice == 'mobile'
            ) {
                $theme .= '-mobile';
            }
        }

        return $theme;
    }

    public static function getThemesPath($themes_path)
    {
        $theme = $themes_path[count($themes_path)-1];
        $parentTheme = false;
        if (substr($theme, -7) == '-mobile') {
            $themeData = \common\models\Themes::findOne(['theme_name' => substr($theme, 0, -7)]);
            if ($themeData->parent_theme) {
                $parentTheme = $themeData->parent_theme . '-mobile';
            }
        } else {
            $themeData = \common\models\Themes::findOne(['theme_name' => $theme]);
            $parentTheme = $themeData->parent_theme ?? null;
        }
        if ($parentTheme){
            $themes_path[] = $parentTheme;
            $themes_path = static::getThemesPath($themes_path);
        }
        return $themes_path;
    }

    /**
     * replace translation keys. If You use it in widgets add widget name and setting name to
     *              backend\design\Theme::$widgetTranslationKeysWidgetsNames and
     *              backend\design\Theme::$widgetTranslationKeysSettingsNames
     * @param string $text
     * @return string
     */
    public static function translateKeys($text){

        $text = preg_replace_callback("/\#\#([0-9A-Z_]+)\%([0-9A-Z_]+)\#\#/", self::class . "::translate", $text);
        $text = preg_replace_callback("/\#\#([0-9A-Z_]+)\#\#/", self::class . "::translate", $text);

        return $text;
    }

    public static function translate($matches)
    {
        if (isset($matches[1]) && defined($matches[1])) {
            if (isset($matches[2]) && defined($matches[2])) {
                return sprintf(constant($matches[1]), constant($matches[2]));
            } elseif (isset($matches[2])) {
                return sprintf(constant($matches[1]), $matches[2]);
            } else {
                return constant($matches[1]);
            }
        } else {
            return $matches[0];
        }
    }

    public static function hasBlog() {
        return false && is_dir(DIR_FS_CATALOG . '_blog');
    }

    public static function createJs($ajax = false){

        $theme_name = THEME_NAME;

        $page_name = '';
        if (Yii::$app->controller->view->page_name) {
            $page_name = '_' . design::pageName(Yii::$app->controller->view->page_name);
        }

        $filePath = DIR_FS_CATALOG . 'themes/' . $theme_name . '/cache/js/';
        $jsFile = Yii::$app->controller->id . '_' . Yii::$app->controller->action->id . $page_name . ($ajax ? '-ajax' : '') . '.js';

        if (is_file($filePath . $jsFile) && !self::isAdmin() && @$_SERVER['HTTP_CACHE_CONTROL'] != 'no-cache' && !self::themeSetting('dev_mode')) {
            return '';
        }

        $js = '';
        if (!$ajax) {
            $js .= IncludeTpl::widget(['file' => 'js/libraries/redux.min.js', 'params' => []]);

            $js .= "\n\n" . 'var reducers={};' . "\n\n";
        }

        foreach (self::$includeJsFiles as $file) {
            $fileCode = IncludeTpl::widget(['file' => 'js/' . $file . '.js', 'params' => []]);
            if ($fileCode) {
                $js .= "\n" . '/* Start file "' . $file . '" */' . "\n";
                $js .= $fileCode;
                $js .= "\n" . '/* End file "' . $file . '" */' . "\n\n";
            }
        }

        foreach (self::$includeExtensionJsFiles as $file) {
            if (!is_file(DIR_FS_CATALOG . 'lib/common/extensions/' . $file . '.js')) {
                continue;
            }
            $path = explode('/', $file);
            $ext = \common\helpers\Acl::checkExtension($path[0], 'allowed');
            if (!$ext || !$ext::allowed()) {
                continue;
            }
            $fileCode = file_get_contents(DIR_FS_CATALOG . 'lib/common/extensions/' . $file . '.js');
            if ($fileCode) {
                $js .= "\n" . '/* Start file extension "' . $file . '" */' . "\n";
                $js .= $fileCode;
                $js .= "\n" . '/* End file extension "' . $file . '" */' . "\n\n";
            }
        }

        foreach (Block::$widgetsList as $widget) {
            if (isset(self::$includeJsFiles['boxes/' . $widget]) && self::$includeJsFiles['boxes/' . $widget]) continue;
            if (isset(self::$includeJsFiles['boxes/' . str_replace('\\', '/', $widget)]) && self::$includeJsFiles['boxes/' . str_replace('\\', '/', $widget)]) continue;
            $widgetCode = IncludeTpl::widget(['file' => 'js/boxes/' . str_replace('\\', DIRECTORY_SEPARATOR, $widget) . '.js', 'params' => []]);
            if ($widgetCode) {
                $js .= "\n\n" . '/* Start widget "' . $widget . '" */' . "\n\n";
                $js .= $widgetCode;
                $js .= "\n\n" . '/* End widget "' . $widget . '" */' . "\n\n";
            }
        }

        if (!$ajax) {
            $js .= IncludeTpl::widget(['file' => 'js/main.js', 'params' => []]);

            $js .= "\n\n" . 'tl(tlSize.init);tl_action(tl_js);' . "\n\n";
        }

        if (!self::themeSetting('dev_mode')) {
            $minifier = new \MatthiasMullie\Minify\JS();
            $minifier->add($js);
            $js = $minifier->minify();
        }

        \yii\helpers\FileHelper::createDirectory($filePath);
        file_put_contents($filePath . $jsFile, $js);
    }

    public static function addLayoutData(){
        $layoutSizes = [];
        foreach (\frontend\design\Info::themeSetting('media_query', 'extend') as $i) {
            $layoutSizes[$i]= explode('w', $i);
        }
        self::addJsData(['layoutSizes' => $layoutSizes]);
    }

    public static function addJsData($arr = []){
        self::$jsGlobalData = self::merge(self::$jsGlobalData, $arr);
    }

    public static function merge($a, $b)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            foreach (array_shift($args) as $k => $v) {
                if ($v instanceof UnsetArrayValue) {
                    unset($res[$k]);
                } elseif ($v instanceof ReplaceArrayValue) {
                    $res[$k] = $v->value;
                } elseif (false && is_int($k) && $k < 1000) {
                    if (array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = static::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

    public static function includeJsFile($fileName){
        self::$includeJsFiles[$fileName] = $fileName;
    }

    public static function includeExtensionJsFile($fileName){
        $path = explode('/', $fileName);
        if (\common\helpers\Acl::checkExtensionAllowed($path[0], 'allowed')) {
            self::$includeExtensionJsFiles[$fileName] = $fileName;
        }
    }

    public static function jsFilePath($suffix = ''){
        $page_name = '';
        if (Yii::$app->controller->view->page_name) {
            $page_name = '_' . design::pageName(Yii::$app->controller->view->page_name);
        }
        $filePath = self::themeFile('/cache/js/' . Yii::$app->controller->id . '_' . Yii::$app->controller->action->id . $page_name . ($suffix ? '-' : '') . $suffix . '.js');

        if (self::themeSetting('dev_mode')) {
            $filePath .= date("U");
        }

        return $filePath;
    }

    public static function getPageStyle(){
        $platformId = \common\classes\platform::defaultId();

        $pageId = (int)Yii::$app->request->get('info_id', 0);
        if (!$pageId) {
            return '';
        }
        $type = 'info';
        $pageStyles = \common\models\PageStyles::find()->where([
            'type' => $type,
            'page_id' => $pageId,
            'platform_id' => $platformId,
        ])->asArray()->one();
        if (isset($pageStyles['style'])) {
            return $pageStyles['style'];
        }
        return '';
    }

    public static function hideBox($id = 0, $parents = 0){
        if (!$id || !$parents || $parents == 1 || self::isAdmin()) {
            return '';
        }

        return IncludeTpl::widget(['file' => 'boxes/hide-box.tpl','params' => [
            'parents' => $parents,
            'id' => $id
        ]]);
    }

    public static function setScriptCss($css){
        self::$scriptCss .= $css;
    }

    public static function widgetToContent($content)
    {

        $arr = explode('<widget ', $content);
        $html = '';

        foreach ($arr as $item){
            if(stripos($item, '</widget>') === false) {
                $html .= $item;
                continue;
            }

            $split = explode('</widget>', $item);

            preg_match_all("/ ([a-zA-Z0-9_\-]+)=\"([^\"]+)\"/", ' ' . $split[0], $matches, PREG_SET_ORDER);

            $name = '';
            $settings = [];
            foreach ($matches as $match) {
                if ($match[1] == 'name') {
                    $name = $match[2];
                } elseif ($match[1] == 'settings') {
                    $settings[0] = (array)json_decode( str_replace('\'', '"', $matches[2]));
                } else {
                    $settings[0][$match[1]] = $match[2];
                }
            }

            Block::addToWidgetsList($name);

            $id = rand(10000000, 11000000);

            $widget_name = 'frontend\design\boxes\\' . $name;
            $html .= '<div class="' . Block::nameToClass($name) . '" id="box-' . $id . '">' . $widget_name::widget(['settings' => $settings, 'id' => $id]) . '</div>';

            $html .= $split[1];

        }

        return $html;
    }
}

