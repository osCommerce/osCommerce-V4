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

namespace common\helpers;

use backend\models\ProductNameDecorator;
use Yii;
use common\classes\platform;
use common\extensions\UserGroupsRestrictions\UserGroupsRestrictions;
use common\helpers\Inventory as InventoryHelper;

defined('ALLOW_ANY_QUERY_CACHE') or define('ALLOW_ANY_QUERY_CACHE', 'True');

class Product {
    const PRODUCT_RECORD_CACHE = 1;

    use SqlTrait;

    public static function getTemporaryStockTableName() {
        return \common\helpers\Warehouses::getTemporaryStockTableName();
    }

    public static function isSubProductWithPrice()
    {
        return false;
    }

    public static function priceProductIdColumn()
    {
        if ( defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' ) {
            return 'products_id_price';
        }
        return 'products_id';
    }
    public static function stockProductIdColumn()
    {
        if ( defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' ) {
            return 'products_id_stock';
        }
        return 'products_id';
    }

    public static function subProductMainAttributesShare()
    {
        static $table_columns = false;
        if ( !is_array($table_columns) ) {
            $table_columns = Yii::$app->getDb()->getTableSchema('products')->getColumnNames();
            $table_columns = array_flip($table_columns);

            $exceptColumns = [
                'products_id',
                'products_model',
                'products_date_added',
                'products_last_modified',
                'products_status',
                'products_status_bundle',
                'manual_control_status',
                'products_ordered',
                'products_seo_page_name',
                'products_old_seo_page_name',
                'sort_order',
                'previous_status',
                'last_xml_import',
                'last_xml_export',
                'products_ean',
                'products_asin',
                'products_isbn',
                'products_upc',
                'products_popularity',
                'popularity_simple',
                'popularity_bestseller',
                'created_by_platform_id',
                'is_listing_product',
                'parent_products_id',
                'products_id_stock',
                'products_id_price',
                'maps_id',
            ];
            foreach ($exceptColumns as $exceptColumn){
                unset($table_columns[$exceptColumn]);
            }
            $table_columns = array_values(array_flip($table_columns));
        }

        return $table_columns;
    }

    public static function isListing($productId)
    {
        return !!\common\models\Products::find()
            ->where(['products_id'=>$productId])
            ->select(['is_listing_product'])
            ->scalar();
    }

    public static function childDetach($childProductId)
    {
        if ($product = \common\models\Products::findOne($childProductId)){
            $product->parent_products_id = 0;
            if ($product->save(false)){
                return true;
            }
        }
        return false;
    }

    public static function childAttach($childProductId, $parentProductId)
    {
        if ($product = \common\models\Products::findOne($childProductId)){
            $product->parent_products_id = $parentProductId;
            if ($product->save(false)){
                \common\helpers\SubProduct::copyAttributesFromParent($childProductId);
                return true;
            }
        }
        return false;
    }

    /**
     * @return \common\components\ProductItem
     */
    public static function itemInstance($params)
    {
        /**
         * @var $productContainer \common\components\ProductsContainer
         */
        if ( !is_array($params) ) $params = ['products_id'=>$params];

        $productContainer = \Yii::$container->get('products');
        $productContainer->loadProducts($params);
        return $productContainer->getProduct($params["products_id"]);
    }

    public static function getState($and = false){
      /* @var $ext \common\extensions\ShowInactive\ShowInactive */
        if ($ext = \common\helpers\Extensions::isAllowed('ShowInactive')) {
            return $ext::getState($and);
        } else {
            return ($and ? " and ": " ") . " p.products_status = 1 ";
        }
    }

    public static function priceProductId($unifiedProductId){
        if (preg_match('/^(\d+)\{/',$unifiedProductId, $match)){
            $unifiedProductId = \common\helpers\Product::normalizePricePrid((int)$match[1]).substr($unifiedProductId, strlen($match[1]));
            $unifiedProductId = \common\helpers\Inventory::normalize_id($unifiedProductId);
        }else{
            $unifiedProductId = \common\helpers\Product::normalizePricePrid((int)$unifiedProductId);
        }
        return $unifiedProductId;
    }

    public static function isSubProduct($productsId)
    {
        $parentage = \common\models\Products::find()
            ->where(['products_id' => (int)$productsId])
            ->select('parent_products_id')
            ->asArray()
            ->one();
        return $parentage['parent_products_id']>0;
    }

    public static function normalizePricePrid($productsId)
    {
        static $lastNormalized = [];
        if ( count($lastNormalized)>50 ) $lastNormalized = [];
        if ( !isset($lastNormalized[$productsId]) ) {
            ///2do check in the storage first (* from products)
            $lastNormalized[$productsId] = (int)$productsId;
            if ( self::priceProductIdColumn()!=='products_id' ) {
                $parentage = static::getProductColumns((int)$productsId, [self::priceProductIdColumn()]);
                if (is_array($parentage) && $parentage[self::priceProductIdColumn()] > 0) {
                    $lastNormalized[$productsId] = (int)$parentage[self::priceProductIdColumn()];
                    $lastNormalized[(int)$parentage[self::priceProductIdColumn()]] = (int)$parentage[self::priceProductIdColumn()];
                }
            }
        }

        return $lastNormalized[$productsId];
    }

    public static function normalizePrid($productsId)
    {
        static $lastNormalized = [];
        if ( count($lastNormalized)>50 ) $lastNormalized = [];
        if ( !isset($lastNormalized[$productsId]) ) {
          ///2do check in the storage first (* from products)
            $lastNormalized[$productsId] = (int)$productsId;
            if ( self::stockProductIdColumn()=='products_id' ){
                $lastNormalized[(int)$productsId] = (int)$productsId;
            }else {
                $parentage = static::getProductColumns((int)$productsId, [self::stockProductIdColumn()]);
                if (is_array($parentage) && isset($parentage[self::stockProductIdColumn()]) && $parentage[self::stockProductIdColumn()] > 0) {
                    $lastNormalized[$productsId] = (int)$parentage[self::stockProductIdColumn()];
                    $lastNormalized[(int)$parentage[self::stockProductIdColumn()]] = (int)$parentage[self::stockProductIdColumn()];
                }
            }
        }

        return $lastNormalized[$productsId];
    }

    /**
     * check product availability by current platform, status, and "product_restrictions" (generally stock indication)
     * @param int $products_id
     * @param bool $check_status
     * @param bool $view true : to display product
     * @param bool $cart - allow share cart between platform
     * @return int
     */
    public static function check_product($products_id, $check_status = 1, $view = false, $cart = false) {
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        $products_join = '';
        if (platform::activeId() && $check_status) {
          if (!$cart || !defined('SHOPPING_CART_SHARE') || SHOPPING_CART_SHARE != 'True') {
            $products_join .= self::sqlProductsToPlatform();
          }
        }

        if ($view){
            $state = self::getState(true);
        } else {
            $state = " and p.products_status = 1 ";
        }

        if ($customer_groups_id == 0) {
            $products_check_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p {$products_join} " . "  where p.products_id = '" . (int) $products_id . "' " . ($check_status ?  $state . self::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "" : ""));
        } else {
            $products_check_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p {$products_join} " . " left join " . TABLE_PRODUCTS_PRICES . " pgp on p.products_id = pgp.products_id and pgp.groups_id = '" . (int) $customer_groups_id . "'  where if(pgp.products_group_price is null, 1, pgp.products_group_price != -1 ) and p.products_id = '" . (int) $products_id . "'  " . ($check_status ? $state . self::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "" : ""));
        }
        return tep_db_num_rows($products_check_query);
    }

    public static function get_product_order_quantity($product_id, $data = null) {
        static $fetched = array();
        if (!isset($fetched[(int) $product_id]) && is_array($data) && array_key_exists('order_quantity_minimal', $data) && array_key_exists('order_quantity_max', $data) && array_key_exists('order_quantity_step', $data)) {
            $fetched[(int) $product_id] = array(
                'order_quantity_minimal' => $data['order_quantity_minimal'],
                'order_quantity_max' => $data['order_quantity_max'],
                'order_quantity_step' => $data['order_quantity_step'],
            );
        }
        if (!isset($fetched[(int) $product_id])) {
            $get_data_r = tep_db_query("SELECT order_quantity_minimal, order_quantity_max, order_quantity_step, pack_unit, packaging FROM " . TABLE_PRODUCTS . " WHERE products_id='" . (int) $product_id . "'");
            if (tep_db_num_rows($get_data_r) > 0) {
                $fetched[(int)$product_id] = tep_db_fetch_array($get_data_r);
//                if ( $fetched[(int)$product_id]['pack_unit']>0 || $fetched[(int)$product_id]['packaging']>0 ) {
//                   $fetched[(int)$product_id]['order_quantity_step'] = 1;
//                }
            } else {
                $fetched[(int)$product_id] = array('order_quantity_minimal' => 1,'order_quantity_max' => -1, 'order_quantity_step' => 1,);
            }
        }
        $fetched[(int) $product_id]['order_quantity_minimal'] = max(1, $fetched[(int) $product_id]['order_quantity_minimal']);
        $fetched[(int) $product_id]['order_quantity_max'] = $fetched[(int) $product_id]['order_quantity_max'];
        $fetched[(int) $product_id]['order_quantity_step'] = max(1, $fetched[(int) $product_id]['order_quantity_step']);
        //$fetched[(int) $product_id]['order_quantity_minimal'] = max($fetched[(int) $product_id]['order_quantity_minimal'], $fetched[(int) $product_id]['order_quantity_step']);
        $fetched[(int) $product_id]['products_id'] = (int) $product_id;
        return $fetched[(int) $product_id];
    }

    public static function filter_product_order_quantity($product_id, $quantity, $quantity_is_top_bound = false) {
        $order_qty_data = self::get_product_order_quantity($product_id);
        if ( $order_qty_data['order_quantity_minimal']>$order_qty_data['order_quantity_step'] ) {
          $result_quantity = max($order_qty_data['order_quantity_minimal'], $quantity,1);
          $base_qty = $order_qty_data['order_quantity_minimal'];
        }else{
          $result_quantity = max($order_qty_data['order_quantity_minimal'],$quantity, 1);
          $base_qty = 0;
        }
        if ( $result_quantity>$order_qty_data['order_quantity_minimal'] && (($result_quantity-$base_qty)%$order_qty_data['order_quantity_step'])!=0 ) {
          $result_quantity = $base_qty+((intval(($result_quantity-$base_qty) / $order_qty_data['order_quantity_step'])+1)*$order_qty_data['order_quantity_step']);
        }
        if ( $quantity_is_top_bound && $result_quantity>$quantity ) {
          $result_quantity = max($order_qty_data['order_quantity_minimal'],$result_quantity-$order_qty_data['order_quantity_step']);
        }
        return $result_quantity;
    }

    public static function get_product_path($products_id) {

        static $last_call_result = [];
        if ( !empty($last_call_result['products_id']) && (int)$last_call_result['products_id']==(int)$products_id ) {
            return $last_call_result['cPath'];
        }

        $cPath = '';

        if (!self::check_product($products_id, 1, true)) {
            return '';
        }

        $categories_join = '';
        if (platform::activeId()) {
            $categories_join .= self::sqlCategoriesToPlatform();
        }

        $linked_categories = Yii::$app->getDb()
            ->createCommand(
                "select p2c.categories_id ".
                "from " . TABLE_PRODUCTS . " p, " .
                TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c ".
                "where p.products_id = '" . (int) $products_id . "' " .
                self::getState(true) . self::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) .
                " and p.products_id = p2c.products_id and c.categories_id=p2c.categories_id and c.categories_status=1"
            )
            ->queryAll();

        $category = false;
        if (count($linked_categories) >= 1) {
            $category = $linked_categories[0];
            if (count($linked_categories) > 1 && strpos(Yii::$app->id,'frontend')!==false) {
                if (Yii::$app->has('request') && Yii::$app->request instanceof \yii\web\Request) {
                    $ref_path = \parse_url(trim(Yii::$app->request->getReferrer()), PHP_URL_PATH);
                    foreach ($linked_categories as $check_category) {
                        $_link = Yii::$app->getUrlManager()->createAbsoluteUrl(['catalog/index', 'cPath' => $check_category['categories_id']]);
                        if (\parse_url($_link, PHP_URL_PATH) == $ref_path) {
                            $category = $check_category;
                            break;
                        }
                    }
                }
            }
        }

        if ($category) {

            $categories = array();
            \common\helpers\Categories::get_parent_categories($categories, $category['categories_id']);

            $categories = array_reverse($categories);

            $cPath = implode('_', $categories);

            if (tep_not_null($cPath))
                $cPath .= '_';
            $cPath .= $category['categories_id'];
        }

        $last_call_result = array(
            'products_id' => (int)$products_id,
            'cPath' => $cPath,
        );

        return $cPath;
    }

    public static function getProductWeight($uprid, $qty=1) {
      $products_weight = $qty * self::get_products_weight($uprid);
          if (\common\helpers\Extensions::isAllowed('Inventory') && !InventoryHelper::disabledOnProduct($uprid)){
              $simpleUprid = InventoryHelper::normalize_id($uprid);
              if (($inventory_weight = InventoryHelper::get_inventory_weight_by_uprid($simpleUprid)) > 0) {
                  $products_weight += $qty * $inventory_weight;
              }
          } else {
              /*if (isset($this->contents[$products_id]['attributes'])) {
                  reset($this->contents[$products_id]['attributes']);
                  if (is_array($this->contents[$products_id]['attributes'])) {
                      foreach ($this->contents[$products_id]['attributes'] as $option => $value) {
                          $option_arr = explode('-', $option);
                          $attribute_price_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix, products_attributes_weight, products_attributes_weight_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) ($option_arr[1] > 0 ? $option_arr[1] : $prid) . "' and options_id = '" . (int) $option_arr[0] . "' and options_values_id = '" . (int) $value . "'");
                          $attribute_price = tep_db_fetch_array($attribute_price_query);
                          if (tep_not_null($attribute_price['products_attributes_weight'])) {
                              if ($attribute_price['products_attributes_weight_prefix'] == '+' || $attribute_price['products_attributes_weight_prefix'] == '') {
                                  $products_weight += $qty * $attribute_price['products_attributes_weight'];
                              } else {
                                  $products_weight -= $qty * $attribute_price['products_attributes_weight'];
                              }
                          }
                      }
                  }
              }*/
          }
          return $products_weight;
    }

    public static function get_products_weight($products_id) {
      $ret = 0;
      $product = tep_db_fetch_array(tep_db_query("select is_bundle, products_id, products_weight, products_file from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'"));
      if (empty($product['products_file'])) {
        // same as in shopping_cart
        if ($product['is_bundle']) {
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
              $ret = $ext::getWeight($product);
            }
        } else {
          $ret = $product['products_weight'];
        }
      }
      return $ret;
    }

    public static function get_manufacturers_name($product_id) {
        $manufacturers_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS . " p where p.manufacturers_id = m.manufacturers_id and p.products_id='".(int)$product_id."'");
        $manufacturers = tep_db_fetch_array($manufacturers_query);
        return $manufacturers['manufacturers_name'];
    }

    public static function get_products_volume(int $products_id, bool $weight = false) {
        $product = tep_db_fetch_array(tep_db_query("select is_bundle, products_id, length_cm, width_cm, height_cm, bundle_volume_calc, volume_weight_cm from " . TABLE_PRODUCTS . " where products_id = '" . $products_id . "'"));
        if ($product['is_bundle']) {
          if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
            return $ext::getVolume($product, $weight);
          }
        }
        $volume = $product['length_cm'] * $product['width_cm'] * $product['height_cm'];
        if ($weight) {
            $product['volume_weight_cm'] = (float)$product['volume_weight_cm'];
            return $product['volume_weight_cm'] > 0.00 ? $product['volume_weight_cm'] : $volume / VOLUME_WEIGHT_COEFFICIENT;
        }
        return $volume;
    }

    public static function convert_kgs_to_lbs($weight) {
      return round($weight * 2.20462, 2);
    }

    public static function convert_lbs_to_kgs($weight) {
      return round($weight / 2.20462, 3);
    }

    public static function convert_inch_to_cm($size) {
      return round($size * 2.54, 1);
    }

    public static function convert_cm_to_inch($size) {
      return round($size / 2.54, 2);
    }

    public static function getProductColumns($products_id, $fields)
    {
        $column_values = [];
        static $container;
        if ( !is_object($container) && Yii::$container->has('products') ) {
            $container = Yii::$container->get('products');
        }
        if (is_object($container) && $container->has((int)$products_id)){
            $productItem = $container->getProduct((int)$products_id);
            foreach ($fields as $idx=>$field) {
                if (array_key_exists($field, (array)$productItem)) {
                    $column_values[$field] = $productItem[$field];
                    unset($fields[$idx]);
                }
            }
        }
        if ( count($fields)>0 ){
            $missingValues = Yii::$app->getDb()->createCommand(
                "select `".implode('`, `', $fields)."` ".
                "from " . TABLE_PRODUCTS . " ".
                "where products_id = '" . (int) $products_id . "'"
            )->queryOne();
            $column_values = array_merge($column_values, (array)$missingValues);
        }

        return $column_values;
    }

    public static function get_products_info($products_id, $field) {
        $product = static::getProductColumns($products_id, [$field]);
        return $product[$field] ?? null;
    }

    public static function get_backend_products_name($product_id, $language = '', $platform_id = '', $search_terms = array()) {
        $languages_id = \Yii::$app->settings->get('languages_id');
        if (empty($language))
            $language = $languages_id;
        $_def = \common\classes\platform::defaultId();
        $platform_id = (int)($platform_id ? $platform_id : $_def);
        $product_query = tep_db_query("select ".ProductNameDecorator::instance()->listingQueryExpression('pd','pd1')." as products_name from " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd.products_id = pd1.products_id and pd1.platform_id = '".intval($platform_id)."' and pd1.language_id = '" . (int) $language . "' where pd.products_id = '" . (int) $product_id . "' and pd.language_id = '" . (int) \common\helpers\Language::get_default_language_id() . "' and pd.platform_id = '" . $_def . "'");
        $product = tep_db_fetch_array($product_query);
        if (!isset($product['products_name'])) {
            return '';
        }
        if (sizeof($search_terms) == 0) {
            return $product['products_name'];
        } else {
            if (defined('MSEARCH_HIGHLIGHT_ENABLE') && MSEARCH_HIGHLIGHT_ENABLE == 'true') {
                return \common\helpers\Output::highlight_text($product['products_name'], $search_terms);
            } else {
                return $product['products_name'];
            }
        }
    }

    public static function get_products_name($product_id, $language = '', $platform_id = '', $search_terms = array()) {
        $languages_id = \Yii::$app->settings->get('languages_id');
        if (empty($language))
            $language = $languages_id;
        $_def = \common\classes\platform::defaultId();
        $platform_id = (int)($platform_id ? $platform_id : $_def);
        $product_query = tep_db_query("select if(length(pd1.products_name) > 0, pd1.products_name, pd.products_name) as products_name from " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd.products_id = pd1.products_id and pd1.platform_id = '".intval($platform_id)."' and pd1.language_id = '" . (int) $language . "' where pd.products_id = '" . (int) $product_id . "' and pd.language_id = '" . (int) \common\helpers\Language::get_default_language_id() . "' and pd.platform_id = '" . $_def . "'");
        $product = tep_db_fetch_array($product_query);
        if (empty($product['products_name']) && stripos(\Yii::$app->id, 'backend')!==false){
            $product = Yii::$app->getDb()->createCommand(
                "SELECT products_name FROM ".TABLE_PRODUCTS_DESCRIPTION." ".
                "WHERE products_id='".(int)$product_id."' AND products_name!='' ".
                "LIMIT 1"
            )->queryOne();
        }
        if (!isset($product['products_name'])) {
            return '';
        }
        if (sizeof($search_terms) == 0) {
            return $product['products_name'];
        } else {
            if (defined('MSEARCH_HIGHLIGHT_ENABLE') && MSEARCH_HIGHLIGHT_ENABLE == 'true') {
                return \common\helpers\Output::highlight_text($product['products_name'], $search_terms);
            } else {
                return $product['products_name'];
            }
        }
    }

    public static function get_products_description($product_id, $language = '', $platform_id = '') {
        $languages_id = \Yii::$app->settings->get('languages_id');
        if (empty($language)) {
            $language = $languages_id;
        }
        $_def = \common\classes\platform::defaultId();
        $platform_id = (int) ($platform_id ? $platform_id : $_def);
        $product_query = tep_db_query("select if(length(pd1.products_description) > 0, pd1.products_description, pd.products_description) as products_description from " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd.products_id = pd1.products_id and pd1.platform_id = '" . intval($platform_id) . "' and pd1.language_id = '" . (int) $language . "' where pd.products_id = '" . (int) $product_id . "' and pd.language_id = '" . (int) \common\helpers\Language::get_default_language_id() . "' and pd.platform_id = '" . $_def . "'");
        $product = tep_db_fetch_array($product_query);
        if (!isset($product['products_description'])) {
            return '';
        }
        return $product['products_description'];
    }

    public static function getSeoName($products_id, $language_id, $platform_id = null)
    {
        if ( empty($language_id) ) $language_id = (int)$GLOBALS['languages_id'];
        if ( empty($platform_id))  $platform_id = \common\classes\platform::defaultId ();
        $_key = (int)$products_id.'^'.(int)$language_id.'^'.$platform_id;
        static $_lookup_product = array();
        if ( isset($_lookup_product[$_key]) ) {
            $product = $_lookup_product[$_key];
        }else {
            /*$product = tep_db_fetch_array(tep_db_query(
                "select if(length(pd.products_seo_page_name) > 0, pd.products_seo_page_name, p.products_seo_page_name) as products_seo_page_name ".
                "from " . TABLE_PRODUCTS . " p ".
                "  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$language_id . "' ".
                "where p.products_id = '" . (int)$products_id . "' ".
                "order by length(if(length(pd.products_seo_page_name) > 0, pd.products_seo_page_name, p.products_seo_page_name)) desc ".
                "limit 1"
            ));*/
            $product = false;
            $product_r = tep_db_query(
                "select pd.products_seo_page_name as products_seo_page_name ".
                "from " . TABLE_PRODUCTS_DESCRIPTION . " pd ".
                "where pd.products_id = '" . (int)$products_id . "' and pd.language_id = '" . (int)$language_id . "' AND pd.platform_id ='" .(int)$platform_id. "' "
            );
            if ( tep_db_num_rows($product_r)>0 ) {
                $product = tep_db_fetch_array($product_r);
            }
            if ( !is_array($product) || empty($product['products_seo_page_name']) ) {
                $product_r = tep_db_query(
                    "select p.products_seo_page_name as products_seo_page_name ".
                    "from " . TABLE_PRODUCTS . " p ".
                    "where p.products_id = '" . (int)$products_id . "' "
                );
                if ( tep_db_num_rows($product_r)>0 ) {
                    $product = tep_db_fetch_array($product_r);
                }
            }

            if ( count($_lookup_product)>50 ) $_lookup_product = array();
            $_lookup_product[$_key] = $product;
        }
        return $product['products_seo_page_name'] ?? null;
    }

    public static function get_products_stock($products_id) {
        $products_id = \common\helpers\Inventory::normalizeInventoryId($products_id);
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'isAllowed')) {
            if (!$ext::isStockAvailable($products_id)) {
                return 0;
            }
        }
        if (defined('TEMPORARY_STOCK_ENABLE') && TEMPORARY_STOCK_ENABLE == 'true') {
            $customers_temporary_stock_quantity = self::get_customers_temporary_stock_quantity($products_id);
        } else {
            $customers_temporary_stock_quantity = 0;
        }
        if (\common\helpers\Extensions::isAllowed('Inventory') && strpos($products_id,'{')!==false && !\common\helpers\Inventory::disabledOnProduct($products_id)) {
            $stock_query = tep_db_query("select products_quantity, suppliers_stock_quantity, stock_control from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($products_id) . "'");
            if (tep_db_num_rows($stock_query)) {
                $stock_values = tep_db_fetch_array($stock_query);
                $stock_values['products_quantity'] = self::getAvailable($products_id, 0);
                /** @var \common\extensions\StockControl\StockControl $extScl */
                if ($extScl = \common\helpers\Extensions::isAllowed('StockControl')) {
                    $extScl::updateGetProductStockInventory($products_id, $stock_values);
                }
                /** @var \common\extensions\ReportFreezeStock\ReportFreezeStock $ext */
                if (($ext = \common\helpers\Extensions::isAllowed('ReportFreezeStock')) && $ext::isFreezed()) {
                    $freezeModel = \common\helpers\Extensions::getModel('ReportFreezeStock', 'FreezeInventory');
                    if (empty($freezeModel)) $freezeInventory = null;
                    $freezeInventory = $freezeModel::find()->where(['products_id' => $products_id])->asArray()->one();
                    if (is_array($freezeInventory)) {
                        $stock_values = array_merge($stock_values, $freezeInventory);
                    }
                }
            } else {
                $products_id = \common\helpers\Inventory::get_prid($products_id);
                $stock_query = tep_db_query("select products_quantity, suppliers_stock_quantity, stock_control from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                $stock_values = tep_db_fetch_array($stock_query);
                $stock_values['products_quantity'] = self::getAvailable((int)$products_id, 0);
                if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
                    $extScl::updateGetProductStockProduct($products_id, $stock_values);
                }
                if (($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezed()) {
                    $freezeProducts = \common\extensions\ReportFreezeStock\models\FreezeProducts::find()->where(['products_id' => (int)$products_id])->asArray()->one();
                    if (is_array($freezeProducts)) {
                        $stock_values = array_merge($stock_values, $freezeProducts);
                    }
                }
            }
        } else {
            $products_id = \common\helpers\Inventory::get_prid($products_id);
            $stock_values = static::getProductColumns((int) $products_id, ['products_quantity', 'suppliers_stock_quantity', 'stock_control']);
            $stock_values['products_quantity'] = self::getAvailable((int)$products_id, 0);
            /** @var \common\extensions\StockControl\StockControl $extScl */
            if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
                $extScl::updateGetProductStockProduct($products_id, $stock_values);
            }
            if (($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezed()) {
                $freezeProducts = \common\extensions\ReportFreezeStock\models\FreezeProducts::find()->where(['products_id' => (int)$products_id])->asArray()->one();
                if (is_array($freezeProducts)) {
                    $stock_values = array_merge($stock_values, $freezeProducts);
                }
            }
        }
        $stock = ($stock_values['products_quantity']??0) + ($stock_values['suppliers_stock_quantity']??0) + $customers_temporary_stock_quantity - self::get_customers_limit_stock_quantity($products_id);
        if ($stock < 0) {
            $stock = 0;
        }
        return $stock;
    }

    public static function get_customers_limit_stock_quantity($products_id) {
        $products_id = \common\helpers\Inventory::get_prid($products_id);
        $product_values = static::getProductColumns((int) $products_id, ['stock_limit', 'manufacturers_id']);
        if (($product_values['stock_limit']??null) > -1) {
            return $product_values['stock_limit'];
        }
        $stockLevelLimit = 0;
        //check brand
        if (($product_values['manufacturers_id']??null) > 0) {
            $manufacturer_query = tep_db_query("select stock_limit from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int) $product_values['manufacturers_id'] . "'");
            $manufacturer_values = tep_db_fetch_array($manufacturer_query);
            if ($manufacturer_values['stock_limit'] > -1) {
                $stockLevelLimit = $manufacturer_values['stock_limit'];
            }
        }
        $cat_r = tep_db_query(
               "SELECT c.categories_id, c.stock_limit
                FROM categories c
                INNER JOIN products_to_categories p2c ON (c.categories_id=p2c.categories_id)
                INNER JOIN platforms_categories pc ON (pc.categories_id=p2c.categories_id and pc.platform_id='" . \common\classes\platform::currentId() . "') ".
                "WHERE p2c.products_id='".$products_id."' ");
        if(tep_db_num_rows($cat_r)>0){
            while($cat_r_array = tep_db_fetch_array($cat_r)) {
                if ($cat_r_array['stock_limit'] > $stockLevelLimit) {
                    $stockLevelLimit = $cat_r_array['stock_limit'];
                }
            }
        }
        if ($stockLevelLimit == 0 && defined('ADDITIONAL_STOCK_LIMIT')) {
            $stockLevelLimit = (int)ADDITIONAL_STOCK_LIMIT;
        }
        return $stockLevelLimit;
    }

    public static function check_stock($products_id, $products_quantity) {
        if (defined('TEMPORARY_STOCK_ENABLE') && TEMPORARY_STOCK_ENABLE == 'true') {
            $products_quantity -= self::get_customers_temporary_stock_quantity($products_id);
        }
        $stock_left = self::get_products_stock($products_id) - $products_quantity;
        $out_of_stock = '';

        if ($stock_left < 0) {
            $out_of_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
        }

        return $out_of_stock;
    }

    public static function get_allocated_stock_quantity($products_id) {
        return 0;
        $orders_status_array = array(); // not Completed and not Cancelled orders
        $orders_status_query = tep_db_query("select distinct orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_groups_id not in (4,5)");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
          $orders_status_array[] = $orders_status['orders_status_id'];
        }
        if (strpos(\common\helpers\Inventory::normalize_id_excl_virtual($products_id), '{') !== false) {
            $allocated_stock_data = tep_db_fetch_array(tep_db_query("select sum(op.products_quantity) as allocated_stock_quantity from " . TABLE_INVENTORY . " i left join " . TABLE_ORDERS_PRODUCTS . " op on op.uprid = i.products_id and op.products_id = i.prid left join " . TABLE_ORDERS . " o on o.orders_id = op.orders_id where i.products_id = '" . tep_db_input(\common\helpers\Inventory::normalize_id_excl_virtual($products_id)) . "' and o.stock_updated = '1' and o.orders_status in ('" . implode("','", $orders_status_array) . "') group by i.products_id"));
            tep_db_query("update " . TABLE_INVENTORY . " set allocated_stock_quantity = '" . (int)$allocated_stock_data['allocated_stock_quantity'] . "', warehouse_stock_quantity =  products_quantity + '" . (int)$allocated_stock_data['allocated_stock_quantity'] . "' + temporary_stock_quantity where products_id = '" . tep_db_input(\common\helpers\Inventory::normalize_id_excl_virtual($products_id)) . "'");
        } else {
            $allocated_stock_data = tep_db_fetch_array(tep_db_query("select sum(op.products_quantity) as allocated_stock_quantity from " . TABLE_PRODUCTS . " p left join " . TABLE_ORDERS_PRODUCTS . " op on op.products_id = p.products_id left join " . TABLE_ORDERS . " o on o.orders_id = op.orders_id where p.products_id = '" . (int)$products_id . "' and o.stock_updated = '1' and o.orders_status in ('" . implode("','", $orders_status_array) . "') group by p.products_id"));
            tep_db_query("update " . TABLE_PRODUCTS . " set allocated_stock_quantity = '" . (int)$allocated_stock_data['allocated_stock_quantity'] . "', warehouse_stock_quantity =  products_quantity + '" . (int)$allocated_stock_data['allocated_stock_quantity'] . "' + temporary_stock_quantity where products_id = '" . (int)$products_id . "'");
        }
        return (int)$allocated_stock_data['allocated_stock_quantity'];
    }

    public static function get_temporary_stock_quantity($products_id) {
        return 0;
        if (strpos(\common\helpers\Inventory::normalize_id_excl_virtual($products_id), '{') !== false) {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(temporary_stock_quantity) as temporary_stock_quantity from " . self::getTemporaryStockTableName() . " where if(length(normalize_id) > 0, normalize_id, products_id) = '" . tep_db_input(\common\helpers\Inventory::normalize_id_excl_virtual($products_id)) . "' group by if(length(normalize_id) > 0, normalize_id, products_id)"));
            tep_db_query("update " . TABLE_INVENTORY . " set temporary_stock_quantity = '" . (int)$temporary_stock_data['temporary_stock_quantity'] . "', warehouse_stock_quantity =  products_quantity + allocated_stock_quantity + '" . (int)$temporary_stock_data['temporary_stock_quantity'] . "' where products_id = '" . tep_db_input(\common\helpers\Inventory::normalize_id_excl_virtual($products_id)) . "'");
        } else {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(temporary_stock_quantity) as temporary_stock_quantity from " . self::getTemporaryStockTableName() . " where prid = '" . (int)$products_id . "' group by prid"));
            tep_db_query("update " . TABLE_PRODUCTS . " set temporary_stock_quantity = '" . (int)$temporary_stock_data['temporary_stock_quantity'] . "', warehouse_stock_quantity =  products_quantity + allocated_stock_quantity + '" . (int)$temporary_stock_data['temporary_stock_quantity'] . "' where products_id = '" . (int)$products_id . "'");
        }
        return $temporary_stock_data['temporary_stock_quantity'];
    }

    public static function cleanup_temporary_stock_quantity() {
        if (defined('TEMPORARY_STOCK_ENABLE') && defined('TEMPORARY_STOCK_PERIOD') && TEMPORARY_STOCK_ENABLE == 'true' && TEMPORARY_STOCK_PERIOD > 0) {
            $temporary_stock_query = tep_db_query("select * from " . self::getTemporaryStockTableName() . " where temporary_stock_datetime < (now() - interval " . (int)TEMPORARY_STOCK_PERIOD . " minute)");
            while ($temporary_stock_data = tep_db_fetch_array($temporary_stock_query)) {
                tep_db_query("delete from " . self::getTemporaryStockTableName() . " where temporary_stock_id = '" . (int)$temporary_stock_data['temporary_stock_id'] . "'");
                self::log_stock_history_before_update($temporary_stock_data['normalize_id'], $temporary_stock_data['temporary_stock_quantity'], '+', ['warehouse_id' => $temporary_stock_data['warehouse_id'], 'suppliers_id' => $temporary_stock_data['suppliers_id'], 'comments' => TEXT_TEMPORARY_STOCK_UPDATE, 'is_temporary' => 1]);
                self::update_stock($temporary_stock_data['normalize_id'], $temporary_stock_data['temporary_stock_quantity'], 0, $temporary_stock_data['warehouse_id'], $temporary_stock_data['suppliers_id']);
                \common\helpers\Warehouses::get_temporary_stock_quantity($temporary_stock_data['normalize_id'], $temporary_stock_data['warehouse_id'], $temporary_stock_data['suppliers_id']);
                self::get_temporary_stock_quantity($temporary_stock_data['normalize_id']);
                self::doCache($temporary_stock_data['normalize_id']);
                self::writeHistory($temporary_stock_data['normalize_id'], $temporary_stock_data['warehouse_id'], $temporary_stock_data['suppliers_id'], 0, -$temporary_stock_data['temporary_stock_quantity'], [
                    'comments' => TEXT_TEMPORARY_STOCK_UPDATE,
                    'is_temporary' => 1
                ]);
            }
        }
    }

    public static function get_customers_temporary_stock_quantity_data($products_id, $warehouse_id, $suppliers_id, $original_products_id = '') {
        //$the_session_id = tep_session_id();
        if (\Yii::$app->id=='app-console') {
          $the_session_id = \Yii::$app->storage->get('guid');
        } else {
          $the_session_id = tep_session_id();
        }
        $original_products_id = trim($original_products_id);
        if (!\Yii::$app->user->isGuest) {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select * from " . self::getTemporaryStockTableName() . " where (customers_id = '" . (int)\Yii::$app->user->getId() . "' or (customers_id = '0' and session_id = '" . tep_db_input($the_session_id) . "')) and products_id = '" . tep_db_input($products_id) . "'" . ($original_products_id != '' ? (" and child_id = '" . tep_db_input($original_products_id) . "'") : '') . " and warehouse_id = '" . (int)$warehouse_id . "' and suppliers_id = '" . (int)$suppliers_id . "'"));
        } else {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select * from " . self::getTemporaryStockTableName() . " where session_id = '" . tep_db_input($the_session_id) . "' and products_id = '" . tep_db_input($products_id) . "'" . ($original_products_id != '' ? (" and child_id = '" . tep_db_input($original_products_id) . "'") : '') . " and warehouse_id = '" . (int)$warehouse_id . "' and suppliers_id = '" . (int)$suppliers_id . "'"));
        }
        return $temporary_stock_data;
    }

    // $warehouse_id = 0 - all warehouses, $suppliers_id = 0 - all suppliers
    public static function get_customers_temporary_stock_quantity($products_id, $warehouse_id = 0, $suppliers_id = 0, $original_products_id = '') {
        //$the_session_id = tep_session_id();
        if (\Yii::$app->id=='app-console') {
          $the_session_id = \Yii::$app->storage->get('guid');
        } else {
          $the_session_id = tep_session_id();
        }
        $original_products_id = trim($original_products_id);
        if (!\Yii::$app->user->isGuest) {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(temporary_stock_quantity) as temporary_stock_quantity from " . self::getTemporaryStockTableName() . " where (customers_id = '" . (int)\Yii::$app->user->getId() . "' or (customers_id = '0' and session_id = '" . tep_db_input($the_session_id) . "'))" . ($original_products_id != '' ? (" and child_id = '" . tep_db_input($original_products_id) . "'") : '') . " and if(length(normalize_id) > 0, normalize_id, products_id) = '" . tep_db_input(\common\helpers\Inventory::normalize_id_excl_virtual($products_id)) . "'" . ($warehouse_id > 0 ? " and warehouse_id = '" . (int) $warehouse_id . "'" : '') . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "'" : '')));
        } else {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(temporary_stock_quantity) as temporary_stock_quantity from " . self::getTemporaryStockTableName() . " where session_id = '" . tep_db_input($the_session_id) . "'" . ($original_products_id != '' ? (" and child_id = '" . tep_db_input($original_products_id) . "'") : '') . " and if(length(normalize_id) > 0, normalize_id, products_id) = '" . tep_db_input(\common\helpers\Inventory::normalize_id_excl_virtual($products_id)) . "'" . ($warehouse_id > 0 ? " and warehouse_id = '" . (int) $warehouse_id . "'" : '') . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "'" : '')));
        }
        return $temporary_stock_data['temporary_stock_quantity'];
    }

/**
 *
 * @param int $products_id - required
 * @param int $qty - required
 * @param int $warehouse_id = 0
 * @param int $suppliers_id = 0
 * @param int $not_available = false
 * @param int $original_products_id = ''
 * @param string $keepUntil = 'now()' (parsed with strtotime) actually date+TEMPORARY_STOCK_PERIOD minutes
 */
    public static function update_customers_temporary_stock_quantity($products_id, $qty, $warehouse_id = 0, $suppliers_id = 0, $not_available = false, $original_products_id = '', $keepUntil = 'now()') {
        if (defined('STOCK_LIMITED') && defined('TEMPORARY_STOCK_ENABLE') && STOCK_LIMITED == 'true' && TEMPORARY_STOCK_ENABLE == 'true') {
          if (\Yii::$app->id=='app-console') {
            $guid = \Yii::$app->storage->get('guid');
          } else {
            $guid = tep_session_id();
          }

          if ($keepUntil != 'now()') {
            $tst = strtotime($keepUntil);
            if ($tst) {
              $keepUntil = date('Y-m-d H:i:s', $tst);
            } else {
              $keepUntil = 'now()';
            }
          }

            if ($warehouse_id == 0) {
                $warehouse_id = \common\helpers\Warehouses::get_default_warehouse();
            }
            if ($suppliers_id == 0) {
                $suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
            }
            $original_products_id = trim($original_products_id);
            $normalize_id = \common\helpers\Inventory::normalize_id_excl_virtual($products_id);
            $temporary_stock_data = self::get_customers_temporary_stock_quantity_data($products_id, $warehouse_id, $suppliers_id, $original_products_id);
            $sql_data_array = [
                'warehouse_id' => (int)$warehouse_id,
                'suppliers_id' => (int)$suppliers_id,
                'session_id' => $guid,
                'customers_id' => (int)\Yii::$app->user->getId(),
                'prid' => (int)\common\helpers\Inventory::get_prid($products_id),
                'products_id' => $products_id,
                'normalize_id' => $normalize_id,
                'temporary_stock_quantity' => $qty,
                'temporary_stock_datetime' => $keepUntil,
                'child_id' => $original_products_id
            ];
            if (preg_match('/^.+\{sub\}(\d+)(\|.*)?$/si', $original_products_id, $match)) {
                $sql_data_array['parent_id'] = (int)$match[1];
            }
            unset($match);

            $sql_data_array['specials_id'] = Specials::getSpecialId($sql_data_array, $qty);

            if (isset($temporary_stock_data['temporary_stock_id']) && $temporary_stock_data['temporary_stock_id'] > 0) {
                $temporary_stock_quantity = $qty - $temporary_stock_data['temporary_stock_quantity'];
                if ($qty > 0) {
                    tep_db_perform(self::getTemporaryStockTableName(), $sql_data_array, 'update', "temporary_stock_id = '" . (int)$temporary_stock_data['temporary_stock_id'] . "'");
                    if ($not_available && abs($temporary_stock_quantity) > 0) {
                        if ($temporary_stock_quantity > 0) {
                            tep_db_query("update " . self::getTemporaryStockTableName() . " set not_available_quantity = not_available_quantity + " . (int) abs($temporary_stock_quantity) . " where temporary_stock_id = '" . (int)$temporary_stock_data['temporary_stock_id'] . "'");
                        } else {
                            tep_db_query("update " . self::getTemporaryStockTableName() . " set not_available_quantity = not_available_quantity - " . (int) abs($temporary_stock_quantity) . " where temporary_stock_id = '" . (int)$temporary_stock_data['temporary_stock_id'] . "'");
                        }
                    }
                } else {
                    tep_db_query("delete from " . self::getTemporaryStockTableName() . " where temporary_stock_id = '" . (int)$temporary_stock_data['temporary_stock_id'] . "'");
                }
            } elseif ($qty > 0) {
                $temporary_stock_quantity = $qty;
                if ($not_available) {
                    $sql_data_array['not_available_quantity'] = $qty;
                }
                tep_db_perform(self::getTemporaryStockTableName(), $sql_data_array);
            }
            if (abs($temporary_stock_quantity) > 0) {
                if ($temporary_stock_quantity > 0) {
                    self::log_stock_history_before_update($normalize_id, $temporary_stock_quantity, '-', ['warehouse_id' => $warehouse_id, 'suppliers_id' => $suppliers_id, 'comments' => TEXT_TEMPORARY_STOCK_UPDATE, 'is_temporary' => 1]);
                    self::update_stock($normalize_id, 0, $temporary_stock_quantity, $warehouse_id, $suppliers_id);
                } else {
                    self::log_stock_history_before_update($normalize_id, abs($temporary_stock_quantity), '+', ['warehouse_id' => $warehouse_id, 'suppliers_id' => $suppliers_id, 'comments' => TEXT_TEMPORARY_STOCK_UPDATE, 'is_temporary' => 1]);
                    self::update_stock($normalize_id, abs($temporary_stock_quantity), 0, $warehouse_id, $suppliers_id);
                }
                self::doCache($products_id);
                self::writeHistory($products_id, $warehouse_id, $suppliers_id, 0, $temporary_stock_quantity, [
                    'comments' => TEXT_TEMPORARY_STOCK_UPDATE,
                    'is_temporary' => 1
                ]);
            }
            \common\helpers\Warehouses::get_temporary_stock_quantity($normalize_id, $warehouse_id, $suppliers_id);
            self::get_temporary_stock_quantity($normalize_id);
        }
    }

    public static function remove_customers_temporary_stock_quantity($products_id, $warehouse_id = 0, $suppliers_id = 0) {
        self::update_customers_temporary_stock_quantity($products_id, 0, $warehouse_id, $suppliers_id);
    }

    public static function log_stock_history_before_update($uprid, $qty, $qty_prefix, $params = []) {
        return 0;
        if (strpos(\common\helpers\Inventory::normalize_id_excl_virtual($uprid), '{') !== false) {
            $check = tep_db_fetch_array(tep_db_query("select products_id, prid, products_model, products_quantity from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input(\common\helpers\Inventory::normalize_id_excl_virtual($uprid)) . "'"));
            $check_warehouse = tep_db_fetch_array(tep_db_query("select sum(warehouse_stock_quantity) as warehouse_stock_quantity from " . TABLE_WAREHOUSES_PRODUCTS . " where warehouse_id = '" . (int) $params['warehouse_id'] . "' and products_id = '" . tep_db_input(\common\helpers\Inventory::normalize_id_excl_virtual($uprid)) . "' and prid = '" . (int)\common\helpers\Inventory::get_prid($uprid) . "'"));
        } else {
            $check = tep_db_fetch_array(tep_db_query("select products_id, products_id as prid, products_model, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$uprid . "'"));
            $check_warehouse = tep_db_fetch_array(tep_db_query("select sum(warehouse_stock_quantity) as warehouse_stock_quantity from " . TABLE_WAREHOUSES_PRODUCTS . " where warehouse_id = '" . (int) $params['warehouse_id'] . "' and products_id = '" . (int)$uprid . "' and prid = '" . (int)$uprid . "'"));
        }
        if ($check['prid'] > 0) {
            $sql_data_array = [
                'products_id' => $check['products_id'],
                'prid' => $check['prid'],
                'products_model' => $check['products_model'],
                'products_quantity_before' => $check['products_quantity'],
                'warehouse_quantity_before' => $check_warehouse['warehouse_stock_quantity'],
                'products_quantity_update_prefix' => $qty_prefix,
                'products_quantity_update' => $qty,
                'comments' => $params['comments'],
                'orders_id' => $params['orders_id'],
                'warehouse_id' => $params['warehouse_id'],
                'suppliers_id' => $params['suppliers_id'],
                'admin_id' => $params['admin_id'],
                'is_temporary' => $params['is_temporary'],
                'date_added' => 'now()',
            ];
            tep_db_perform(TABLE_STOCK_HISTORY, $sql_data_array);
        }
    }

    public static function update_stock($uprid, $qty, $old_qty = 0, $warehouse_id = 0, $suppliers_id = 0, $platform_id = 0) {
        return 0;
        $prid = \common\helpers\Inventory::get_prid($uprid);
        if (!tep_not_null($prid))
            return false;

        if ($warehouse_id == 0) {
            $warehouse_id = \common\helpers\Warehouses::get_default_warehouse();
        }
        if ($suppliers_id == 0) {
            $suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
        }
        if ($platform_id == 0) {
            $platform_id = \common\classes\platform::currentId();
        }
        if (defined('STOCK_LIMITED') && STOCK_LIMITED == 'true') {
            if ($qty > $old_qty) {
                $q = "+" . (int) ($qty - $old_qty) . "";
            } else {
                $q = "-" . (int) ($old_qty - $qty) . "";
            }
            if (defined('DOWNLOAD_ENABLED') && DOWNLOAD_ENABLED == 'true') {
                preg_match_all("/\{\d+\}/", $uprid, $arr);
                $options_id = $arr[0][1];
                preg_match_all("/\}[^\{]+/", $uprid, $arr);
                $values_id = $arr[0][1];

                if (is_array($options_id)) {
                    $stock_query_raw = "SELECT count(*) as total FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad WHERE pa.products_attributes_id=pad.products_attributes_id and pa.products_id = '" . (int) $prid . "' and pad.products_attributes_filename<>'' ";
                    $stock_query_raw .= " and ( 0 ";
                    for ($k = 0; $k < count($options_id); $k++) {
                        $stock_query_raw .= " OR (pa.options_id = '" . (int) $options_id[$k] . "' AND pa.options_values_id = '" . (int) $values_id[$k] . "')  ";
                    }
                    $stock_query_raw .= ") ";
                    $d = tep_db_fetch_array(tep_db_query($stock_query_raw));
                    if ($d['total'] > 0) {
                        return true;
                    }
                }
                $stock_query_raw = "SELECT count(*) as total FROM " . TABLE_PRODUCTS . " WHERE products_id = '" . (int) $prid . "' and products_file <> '' ";
                $d = tep_db_fetch_array(tep_db_query($stock_query_raw));
                if ($d['total'] > 0) {
                    return true;
                }
            }
/*
            if (\common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                $vids = array();
                $attributes_query = tep_db_query("select options_id, options_values_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $prid . "'");
                while ($attributes = tep_db_fetch_array($attributes_query)) {
                    if (preg_match('/\{' . $attributes['options_id'] . '\}' . $attributes['options_values_id'] . '(\{|$)/', $uprid)) {
                        $vids[$attributes['options_id']] = $attributes['options_values_id'];
                    }
                }
                ksort($vids);
                $uprid = \common\helpers\Inventory::get_uprid($prid, $vids);
            }
*/
            /** @var \common\extensions\Inventory\Inventory $ext */
            if ($ext = \common\helpers\Extensions::isAllowed('Inventory')) {
                $ext::updateStock($prid, $uprid, $q, $warehouse_id, $suppliers_id, $platform_id);
            } else {
                tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity  " . $q . " where products_id = '" . (int)$prid . "'");
                tep_db_query("update " . TABLE_WAREHOUSES_PRODUCTS . " set products_quantity = products_quantity  " . $q . " where warehouse_id = '" . (int) $warehouse_id . "' and suppliers_id = '" . (int) $suppliers_id . "' and products_id = '" . (int) $prid . "' and prid = '" . (int) $prid . "'");
                $data_q = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where  products_id = '" . (int)$prid . "'");
                $data = tep_db_fetch_array($data_q);
                if ($data['products_quantity'] < 1 && (STOCK_ALLOW_CHECKOUT == 'false')) {
                    tep_db_query("update " . TABLE_PRODUCTS . " set products_status = 0 where products_id = '" . (int)$prid . "'");
                }
            }
        }
    }

    public static function remove_product_image($filename) {
        $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image = '" . tep_db_input($filename) . "' or products_image_med = '" . tep_db_input($filename) . "' or products_image_lrg = '" . tep_db_input($filename) . "' or products_image_xl_1 = '" . tep_db_input($filename) . "' or products_image_sm_1 = '" . tep_db_input($filename) . "' or products_image_xl_2 = '" . tep_db_input($filename) . "' or products_image_sm_2 = '" . tep_db_input($filename) . "' or products_image_xl_3 = '" . tep_db_input($filename) . "' or products_image_sm_3 = '" . tep_db_input($filename) . "' or products_image_xl_4 = '" . tep_db_input($filename) . "' or products_image_sm_4 = '" . tep_db_input($filename) . "' or products_image_xl_5 = '" . tep_db_input($filename) . "' or products_image_sm_5 = '" . tep_db_input($filename) . "' or products_image_xl_6 = '" . tep_db_input($filename) . "' or products_image_sm_6 = '" . tep_db_input($filename) . "'");
        $duplicate_image = tep_db_fetch_array($duplicate_image_query);
        if ($duplicate_image['total'] < 2) {
            if (file_exists(DIR_FS_CATALOG_IMAGES . $filename)) {
                @unlink(DIR_FS_CATALOG_IMAGES . $filename);
            }
        }
    }

    public static function remove_product($product_id) {
        $changePids = [$product_id];
        // {{ remove sub products
        if ( (int)$product_id>0 ) {
            foreach (\common\models\Products::find()
                         ->select('products_id')
                         ->where(['parent_products_id' => (int)$product_id])
                         ->asArray()
                         ->all() as $sub_product) {
                $changePids[] = (int) $sub_product['products_id'];
                static::remove_product($sub_product['products_id']);
            }
        }
        // }} remove sub products
        \common\components\CategoriesCache::getCPC()::invalidateProducts($changePids);
        $productModel = \common\models\Products::findOne($product_id);

        \common\classes\Images::removeProductImages($product_id);

        /**
         * Moved to hook
         */
        // {{ put redirect to category
//        $get_category_r = tep_db_query(
//            "SELECT c.categories_id ".
//            "FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ".
//              "LEFT JOIN ".TABLE_CATEGORIES." c ON c.categories_id=p2c.categories_id ".
//            "WHERE p2c.products_id='".(int)$product_id."' ".
//            "ORDER BY IFNULL(c.categories_left,4000000), c.categories_status DESC ".
//            "LIMIT 1"
//        );
//        if ( tep_db_num_rows($get_category_r)>0 ) {
//            $_category = tep_db_fetch_array($get_category_r);
//
//            tep_db_query(
//                "INSERT INTO seo_redirect (old_url, new_url, platform_id) ".
//                "SELECT DISTINCT pd.products_seo_page_name, cd.categories_seo_page_name, pd.platform_id ".
//                "FROM ".TABLE_PRODUCTS_DESCRIPTION." pd ".
//                " INNER JOIN ".TABLE_CATEGORIES_DESCRIPTION." cd ON cd.categories_id='".$_category['categories_id']."' AND cd.categories_seo_page_name!='' AND cd.language_id=pd.language_id ".
//                "WHERE pd.products_seo_page_name!='' AND pd.products_id='".(int)$product_id."' "
//            );
//        }
        // }}

        //if (USE_MARKET_PRICES == 'True') {
        tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PLATFORMS_PRODUCTS . " where products_id = '" . (int) $product_id . "'");
        $query = tep_db_query("select specials_id from " . TABLE_SPECIALS . " where products_id = '" . (int) $product_id . "'");
        while ($data = tep_db_fetch_array($query)) {
            tep_db_query("delete from " . TABLE_SPECIALS_PRICES . " where specials_id = " . $data['specials_id']);
        }
        $query = tep_db_query("select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $product_id . "'");
        while ($data = tep_db_fetch_array($query)) {
            tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . (int) $data['products_attributes_id'] . "'");
        }
        //}
        if (defined('PRODUCTS_PROPERTIES') && PRODUCTS_PROPERTIES == 'True') {
            tep_db_query("delete from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id = '" . (int) $product_id . "'");
        }

        tep_db_query("delete from " . TABLE_SPECIALS . " where products_id = '" . (int) $product_id . "'");
        if ( $productModel ) {
            $productModel->delete();
        }
        tep_db_query("delete from " . TABLE_PRODUCTS . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_COMMENTS . " where products_id = '" . $product_id . "'");

        /** @var \common\extensions\Inventory\Inventory $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('Inventory')) {
            $ext::deleteProduct((int) $product_id);
        }

        tep_db_query("delete from " . TABLE_SUPPLIERS_PRODUCTS . " where products_id = '" . (int) $product_id . "'");
        tep_db_query("delete from " . TABLE_WAREHOUSES_PRODUCTS . " where prid = '" . (int) $product_id . "'");

        $product_reviews_query = tep_db_query("select reviews_id from " . TABLE_REVIEWS . " where products_id = '" . (int) $product_id . "'");
        while ($product_reviews = tep_db_fetch_array($product_reviews_query)) {
            tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int) $product_reviews['reviews_id'] . "'");
        }
        tep_db_query("delete from " . TABLE_REVIEWS . " where products_id = '" . (int) $product_id . "'");

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductTemplates', 'allowed')) {
            $ext::productDelete($product_id);
        }

        $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('products_linked_parent');
        if ( $schemaCheck ) {
            tep_db_query("DELETE FROM products_linked_parent WHERE product_id='".$product_id."'");
        }
        $schemaCheck = Yii::$app->get('db')->schema->getTableSchema('products_linked_children');
        if ( $schemaCheck ) {
            tep_db_query("DELETE FROM products_linked_children WHERE parent_product_id='".$product_id."'");
            tep_db_query("DELETE FROM products_linked_children WHERE linked_product_id='".$product_id."'");
        }

        foreach (\common\helpers\Hooks::getList('product/after-delete') as $filename) {
            include($filename);
        }

        if (defined('USE_CACHE') && USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
    }

    public static function trunk_products(){
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_TO_CATEGORIES);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_DESCRIPTION);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_ATTRIBUTES);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_ATTRIBUTES_PRICES);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_PRICES);
        tep_db_query("TRUNCATE " . TABLE_PROPERTIES_TO_PRODUCTS);

        tep_db_query("TRUNCATE " . TABLE_INVENTORY);
        tep_db_query("TRUNCATE " . TABLE_INVENTORY_PRICES);

        tep_db_query("TRUNCATE " . TABLE_SUPPLIERS_PRODUCTS);
        tep_db_query("TRUNCATE " . TABLE_PLATFORMS_PRODUCTS);

        tep_db_query("TRUNCATE " . TABLE_REVIEWS);
        tep_db_query("TRUNCATE " . TABLE_REVIEWS_DESCRIPTION);

        tep_db_query("TRUNCATE " . TABLE_SPECIALS);
        tep_db_query("TRUNCATE " . TABLE_SPECIALS_PRICES);

        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_IMAGES);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_IMAGES_ATTRIBUTES);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_IMAGES_DESCRIPTION);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_IMAGES_INVENTORY);

        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_OPTIONS);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_OPTIONS_VALUES);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS);

        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_COMMENTS);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_VIDEOS);
        tep_db_query("TRUNCATE " . TABLE_FEATURED);
        tep_db_query("TRUNCATE " . TABLE_GIVE_AWAY_PRODUCTS);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_NOTIFY);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_NOTIFICATIONS);

        tep_db_query("TRUNCATE " . TABLE_STOCK_HISTORY);
        tep_db_query("TRUNCATE " . \common\models\WarehousesProducts::tableName());

        tep_db_query("TRUNCATE " . TABLE_CUSTOMERS_BASKET);
        tep_db_query("TRUNCATE " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES);

        if (defined('USE_CACHE') && USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }

        $var_tables = [
            // bundle
            TABLE_SETS_PRODUCTS,
            // GiftWrap
            TABLE_GIFT_WRAP_PRODUCTS, TABLE_VIRTUAL_GIFT_CARD_PRICES, TABLE_VIRTUAL_GIFT_CARD_BASKET, \common\models\VirtualGiftCardInfo::tableName(),
            // LinkedProducts
            'products_linked_parent', 'products_linked_children',

        ];
        foreach($var_tables as $table) {
          if ( \Yii::$app->db->schema->getTableSchema($table) ) {
             tep_db_query("TRUNCATE TABLE $table");
          }
        }

        foreach (\common\helpers\Hooks::getList('product/after-trunk') as $filename) {
            include($filename);
        }

    }

    public static function duplicate($products_id, $categories_id, $copy_attributes, $copyCategories = false)
    {
        $copy_attributes = is_bool($copy_attributes)?$copy_attributes:( !empty($copy_attributes) && $copy_attributes=='yes' );
        $originProduct = \common\models\Products::findOne($products_id);
        if ( !$originProduct ) return false;

        $__data = $originProduct->getAttributes();
        unset($__data['products_id']);
        $__data['products_date_added'] = date('Y-m-d H:i:s');
        $productModel = new \common\models\Products($__data);
        $productModel->loadDefaultValues();
        $productModel->products_status = 0;
        $productModel->products_old_seo_page_name = '';
        $productModel->products_seo_page_name = '';
        $productModel->products_quantity = 0;
        $productModel->allocated_stock_quantity = 0;
        $productModel->temporary_stock_quantity = 0;
        $productModel->warehouse_stock_quantity = 0;
        $productModel->suppliers_stock_quantity = 0;
        $productModel->ordered_stock_quantity = 0;
        $productModel->products_ordered = 0;
        $productModel->is_bundle = 0;
        $productModel->products_popularity = 0;
        $productModel->popularity_simple = 0;
        $productModel->popularity_bestseller = 0;
        $productModel->sub_product_children_count = 0;
        $productModel->parent_products_id = 0;
        $productModel->products_file = '';

        if (!$productModel->save(false)){
            return false;
        }
        $productModel->refresh();
        $dup_products_id = intval($productModel->products_id);

        if ($copyCategories) {
            tep_db_query("insert ignore into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) select * from (select '" . (int) $dup_products_id . "', categories_id from ". TABLE_PRODUCTS_TO_CATEGORIES . " where products_id='" . (int)$products_id . "') a");
        }

        tep_db_query("insert ignore into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $dup_products_id . "', '" . (int) $categories_id . "')");

        $copyModels = [
            '\common\models\ProductsDescription' => 'products_id',
            '\common\models\PlatformsProducts' => 'products_id',
            '\common\models\ProductsPrices' => 'products_id',
        ];
        if (\common\helpers\Acl::checkExtensionAllowed('ProductBundles')) {
            $copyModels['\common\models\SetsProducts'] = 'sets_id';
        }
        if (\common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions')) {
            $copyModels['\common\extensions\UserGroupsRestrictions\models\GroupsProducts'] = 'products_id';
        }
        foreach ($copyModels as $copyModelClass=>$copyProductColumn){
            if ( !class_exists($copyModelClass) ) {
                continue;
            }

            call_user_func_array([$copyModelClass,'deleteAll'], [[$copyProductColumn=>$dup_products_id]]);
            $sourceCollection = call_user_func_array([$copyModelClass,'findAll'], [[$copyProductColumn=>$originProduct->products_id]]);
            foreach ($sourceCollection as $originModel)
            {
                $__data = $originModel->getAttributes();
                $__data[$copyProductColumn] = $dup_products_id;
                $copyModel = Yii::createObject($copyModelClass);
                if ( $copyModel instanceof \yii\db\ActiveRecord )
                {
                    if ( $copyModel instanceof \common\models\ProductsDescription ) {
                        $__data['products_seo_page_name'] = '';
                    }
                    $copyModel->setAttributes($__data, false);
                    $copyModel->loadDefaultValues(true);
                    $copyModel->save(false);
                }
            }
        }

        // [[ Properties
        if (defined('PRODUCTS_PROPERTIES') && PRODUCTS_PROPERTIES == 'True') {
            tep_db_query("insert into " . TABLE_PROPERTIES_TO_PRODUCTS . " (products_id, properties_id, values_id, values_flag, extra_value) select * from (select " . (int)$dup_products_id . ", properties_id, values_id, values_flag, extra_value from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id = '" . tep_db_input($products_id) . "') a");
            /* outdated table structure .... Unknown column 'language_id' in field list
             $properties_query = tep_db_query("select * from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id = '" . tep_db_input($products_id) . "'");
            while ($properties = tep_db_fetch_array($properties_query)) {
                tep_db_query("insert into " . TABLE_PROPERTIES_TO_PRODUCTS . " (products_id, properties_id, language_id, set_value, additional_info) values ('" . $dup_products_id . "', '" . $properties['properties_id'] . "', '" . $properties['language_id'] . "', '" . $properties['set_value'] . "', '" . $properties['additional_info'] . "')");
            }
             */
        }
        // ]]

        // [[ SUPPLEMENT_STATUS
        if ((defined('SUPPLEMENT_STATUS') && SUPPLEMENT_STATUS == 'True') && \common\helpers\Acl::checkExtensionAllowed('UpSell'))
        {
            $query = tep_db_query("select * from " . TABLE_PRODUCTS_UPSELL . " where products_id = '" . (int) $products_id . "'");
            while ($data = tep_db_fetch_array($query)) {
                tep_db_query("insert into " . TABLE_PRODUCTS_UPSELL . " (products_id, upsell_id, sort_order) values ('" . $dup_products_id . "', '" . $data['upsell_id'] . "', '" . $data['sort_order'] . "')");
            }

            $query = tep_db_query("select * from " . TABLE_PRODUCTS_XSELL . " where products_id = '" . (int) $products_id . "'");
            while ($data = tep_db_fetch_array($query)) {
                tep_db_query("insert into " . TABLE_PRODUCTS_XSELL . " (products_id, xsell_id, sort_order) values ('" . $dup_products_id . "', '" . $data['xsell_id'] . "', '" . $data['sort_order'] . "')");
            }
        }
        // ]]
        // BOF: WebMakers.com Added: Attributes Copy on non-linked
        $products_id_from = tep_db_input($products_id);
        $products_id_to = $dup_products_id;
        //$products_id = $dup_products_id;


        if ($copy_attributes) {
            /*$copy_attributes_delete_first = '1';
            $copy_attributes_duplicates_skipped = '1';
            $copy_attributes_duplicates_overwrite = '0';
            ob_start();
            \common\helpers\Attributes::copy_products_attributes($products_id_from, $products_id_to);
            ob_get_clean();*/
            try {
                \common\helpers\Attributes::copyProductsAttributes($products_id_from, $products_id_to, true);
            } catch (\Exception $e) {
                \Yii::warning(" #### " . $e->getCode() . ' ' . print_r($e->getMessage(), true), 'TLDEBUG');
            }
        }
/// images (after attributes and inventory
        \common\helpers\Image::copyProductImages($products_id, $dup_products_id);
        return $dup_products_id;
    }

/**
 * common\models\Product\Price()->getProductSpecialPrice
 * @param int $product_id
 * @param int $qty
 * @return float|false
 */
    public static function get_products_special_price($product_id, $qty = 1) {
        return \common\models\Product\Price::getInstance($product_id)->getProductSpecialPrice([
            'qty' => $qty,
        ]);
    }

    public static function save_specials_prices($specials_id, $group_id, $currencies_id = 0,$specials_groups_prices = 0){
        $sql_data_array = [];
        $sql_data_array['specials_new_products_price'] = (float)$specials_groups_prices;
        $check = tep_db_fetch_array(tep_db_query("select count(*) as specials_price_exists from " . TABLE_SPECIALS_PRICES . " where specials_id = '" . (int) $specials_id . "' and groups_id = '" . (int) $group_id . "' and currencies_id = '" . (int)$currencies_id . "'"));
        if ($check['specials_price_exists']) {
            tep_db_perform(TABLE_SPECIALS_PRICES, $sql_data_array, 'update', "specials_id = '" . (int) $specials_id . "' and groups_id = '" . (int) $group_id . "' and currencies_id = '". (int)$currencies_id."'");
        } else {
            $sql_data_array['specials_id'] = $specials_id;
            $sql_data_array['groups_id'] = $group_id;
            $sql_data_array['currencies_id'] = $currencies_id;
            tep_db_perform(TABLE_SPECIALS_PRICES, $sql_data_array);
        }
    }

    public static function get_products_price_for_edit($product_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (!\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $product_query = tep_db_query("select products_price from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
        } else {
            $product_query = tep_db_query("select products_group_price as products_price from " . TABLE_PRODUCTS_PRICES . " where  products_id = '" . (int)$product_id . "' and  groups_id = '" . (int)$group_id . "' and  currencies_id = '" . (int)$currency_id . "'");
        }
        $product = tep_db_fetch_array($product_query);

        if (empty($product['products_price']) && $default != '') {
            $product['products_price'] = $default;
        }
        return $product['products_price'];
    }
/**
 * product price in selected currency for specified group (already q-ty discount)
 *
 * @param int $product_id
 * @param int $currency_id
 * @param int $group_id
 * @param float $default price
 * @return float product price in selected currency for specified group
 */
    public static function get_products_price($products_id, $qty = 1, $price = 0, $curr_id = 0, $group_id = 0) {
        return \common\models\Product\Price::getInstance($products_id)->getProductPrice([
            'qty' => $qty,
            'curr_id' => $curr_id,
            'group_id' => $group_id,
        ]);
    }

    /* function tep_get_products_discount_price($product_id, $currency_id = 0, $group_id = 0, $default = ''){ */
    public static function get_products_discount_price($products_id, $qty, $products_price, $curr_id = 0, $group_id = 0) {
        return \common\models\Product\Price::getInstance($products_id)->getProductsDiscountPrice([
            'products_price' => $products_price, //???
            'qty' => $qty,
            'curr_id' => $curr_id,
            'group_id' => $group_id,
        ]);
    }

    public static function get_products_discount_table($products_id, $curr_id = 0, $group_id = 0) {
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        if ($curr_id > 0) {
            $_currency_id = $curr_id;
        } else {
            $_currency_id = \Yii::$app->settings->get('currency_id');
        }
        if ($group_id > 0) {
            $_customer_groups_id = $group_id;
        } else {
            $_customer_groups_id = $customer_groups_id;
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            if ($ext::checkShowPrice($_customer_groups_id)) {
                return false;
            }
        }

        $apply_discount = false;
        if ((defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $query = tep_db_query("select pp.products_group_discount_price as products_price_discount, pp.products_group_price from " . TABLE_PRODUCTS_PRICES . " pp where pp.products_id = '" . (int)$products_id . "' and pp.groups_id = '" . (int)$_customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'? $_currency_id :'0'). "'");
            $num_rows = tep_db_num_rows($query);
            $data = tep_db_fetch_array($query);
            if (!$num_rows || ($data['products_price_discount'] == '' && $data['products_group_price'] == -2) || $data['products_price_discount'] == -2 || (USE_MARKET_PRICES != 'True' && $_customer_groups_id == 0)) {
                if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
                    $data = tep_db_fetch_array(tep_db_query("select pp.products_group_discount_price as products_price_discount from " . TABLE_PRODUCTS_PRICES . " pp where pp.products_id = '" . (int)$products_id . "' and pp.groups_id = '0' and pp.currencies_id = '" . (int)$_currency_id . "'"));
                } else {
                    $data = tep_db_fetch_array(tep_db_query("select products_price_discount from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'"));
                }
                $apply_discount = true;
            }
        } else {
            $data  = tep_db_fetch_array(tep_db_query("select products_price_discount from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'"));
        }
        if ($data['products_price_discount'] == '' || $data['products_price_discount'] == -1) {
            return false;
        }
        $ar = preg_split("/[:;]/", preg_replace('/;\s*$/', '', $data['products_price_discount'])); // remove final separator

        if (!is_array($ar) || count($ar)<2 || count($ar)%2==1) { // incorrect table format - skip
          return false;
        }

        if ($apply_discount) {
            $discount = \common\helpers\Customer::check_customer_groups($_customer_groups_id, 'groups_discount');
            for ($i=0, $n=sizeof($ar); $i<$n; $i=$i+2) {
                $ar[$i+1] = $ar[$i+1] * (1 - ($discount/100));
            }
        }

        foreach (\common\helpers\Hooks::getList('product/get-products-discount-table') as $filename) {
            include($filename);
        }

        return $ar;
    }

    public static function is_giveaway($products_id) {
        $query = tep_db_query("select * from " . TABLE_GIVE_AWAY_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
        if (tep_db_num_rows($query) > 0) {
            return true;
        }
        return false;
    }

    public static function draw_products_pull_down($name, $parameters = '', $exclude = '') {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $_params = \Yii::$app->request->getBodyParams();

        if (!isset($_params->currencies)) {
            $currencies = Yii::$container->get('currencies');
        } else {
            $currencies = $_params->currencies;
        }

        if ($exclude == '') {
            $exclude = array();
        }

        $select_string = '<select name="' . $name . '"';

        if ($parameters) {
            $select_string .= ' ' . $parameters;
        }

        $select_string .= '>';

        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.language_id = '" . (int) $languages_id . "' order by products_name");
        while ($products = tep_db_fetch_array($products_query)) {
            if (!in_array($products['products_id'], $exclude)) {
                $select_string .= '<option ' . (($_POST[$name] == $products['products_id']) ? ' selected ' : '') . ' value="' . $products['products_id'] . '">' . $products['products_name'] . ' (' . $currencies->format(\common\helpers\Product::get_products_price($products['products_id'], 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id']), true, DEFAULT_CURRENCY) . ')</option>';
            }
        }

        $select_string .= '</select>';

        return $select_string;
    }

/**
 * generally for admin only - get special price value for group/currency
 * @param int $specials_id
 * @param int $currency_id
 * @param int $group_id
 * @param float $default
 * @return float
 */
    public static function get_specials_price($specials_id, $currency_id = 0, $group_id = 0, $default = '') {
        if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES != 'True') {
            $currency_id = 0;
        }
        if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $group_id = 0;
        }
        if ($currency_id == 0 && $group_id == 0) {
            $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS . " where specials_id = '" . (int)$specials_id . "'");
        } else {
            $specials_query = tep_db_query("select specials_new_products_price from " . TABLE_SPECIALS_PRICES . " where  specials_id = '" . (int)$specials_id . "' and  groups_id = '" . (int)$group_id . "' and  currencies_id = '" . (int)$currency_id . "'");
        }
        $specials_data = tep_db_fetch_array($specials_query);
        if ($specials_data['specials_new_products_price'] == '' && $default != '') {
            $specials_data['specials_new_products_price'] = $default;
        }
        return $specials_data['specials_new_products_price'];
    }
    public static function get_sql_product_restrictions($table_prefixes = array('p', 'pd', 's', 'sp', 'pp'), $listingCheck=true) {
      // " . \common\helpers\Product::get_sql_product_restrictions(array('p'=>'')) . "
      $def = array('p', 'pd', 's', 'sp', 'pp');
      if (!is_array($table_prefixes)) {
        $table_prefixes['p'] = (trim($table_prefixes)!=''?rtrim($table_prefixes, '.') . '.':'');
      } else {
        foreach($table_prefixes as $k => $v) {
          if (is_integer($k)) {
            $k = $def[$k];
          }
          $table_prefixes[$k] = (trim($v) != '' ? rtrim($v, '.') . '.':'');
        }
      }
      foreach($def as $k) {
        if (!isset($table_prefixes[$k])) {
          $table_prefixes[$k] = $k . '.';
        }
      }

      $where_str = '';
      static $_cache=[];
      if (!isset($_cache['hidden_stock_indication'])) {
        $_cache['hidden_stock_indication'] = \common\classes\StockIndication::getHiddenIds();
      }
      if (count($_cache['hidden_stock_indication'])>0 && !\frontend\design\Info::isTotallyAdmin()) {
        $where_str .= " and " .$table_prefixes['p'] . "stock_indication_id not in ('" . implode("','", $_cache['hidden_stock_indication']) . "')";
      }
      if (!$listingCheck && defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' && !\frontend\design\Info::isTotallyAdmin()) {
        $where_str .= " and " . $table_prefixes['p'] . "is_listing_product=1 ";
      }
      if (\common\helpers\Extensions::isAllowed('Inventory')) {
        if ($groupsInventory = \common\helpers\Acl::checkExtensionTableExist('UserGroupsRestrictions', 'GroupsInventory', 'isAllowed')) {
          $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
          $where_str .= " and ((not exists (select * from " . TABLE_INVENTORY . " where " . $table_prefixes['p'] . "products_id = prid)) or ((exists (select * from " . TABLE_INVENTORY . " where " . $table_prefixes['p'] . "products_id = prid)) and (exists (select * from " . $groupsInventory::tableName() . " where (" . $table_prefixes['p'] . "products_id = prid) and (groups_id = '" . (int)$customer_groups_id . "')))))";
        }
      }
      return $where_str;
    }

    public static function getProductImages($products_id) {
      $image_path = DIR_WS_CATALOG_IMAGES . 'products' . '/' . $products_id . '/';
      $images = [];
      $images_query = tep_db_query("select id.*, i.* from " . TABLE_PRODUCTS_IMAGES . " as i left join " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " as id on (i.products_images_id=id.products_images_id and id.language_id=0) where i.products_id = '" . (int) $products_id . "' order by i.sort_order");
      while ($images_data = tep_db_fetch_array($images_query)) {
          $images[] = [
              'products_images_id' => $images_data['products_images_id'],
              'image_name' => (empty($images_data['hash_file_name']) ? '' : $image_path . $images_data['products_images_id'] . '/' . $images_data['hash_file_name']),
          ];
      }
      return $images;
    }

    public static function parseQtyDiscountArray($discount) {
      $qty_discounts = [];
      if ($discount != '') {
        foreach (explode(';', $discount) as $qty_discount) {
          $ar = explode(':', $qty_discount);
          if ($ar[0] > 0 && $ar[1] > 0) {
            $qty_discounts[$ar[0]] = $ar[1];
          }
        }
      }
      ksort($qty_discounts);
      return $qty_discounts;
    }

    public static function products_groups_name($products_groups_id, $language_id = '') {
        $languages_id = \Yii::$app->settings->get('languages_id');
        if (!$language_id) {
            $language_id = $languages_id;
        }

        $products_groups_query = tep_db_query("select products_groups_name from " . TABLE_PRODUCTS_GROUPS . " where products_groups_id = '" . (int)$products_groups_id . "' and language_id = '" . (int)$language_id . "'");
        $products_groups = tep_db_fetch_array($products_groups_query);

        return $products_groups['products_groups_name'] ?? null;
    }

    public static function set_status($productId, $status)
    {
        tep_db_query(
            "update " . TABLE_PRODUCTS . " ".
            "set products_status = '" . ($status? 1 : 0) . "', ".
            " previous_status=NULL, ".
            " products_last_modified = now() ".
            "where products_id = '" . (int)$productId. "'"
        );
        if ( (int)$productId ) {
            if ( $status ) {
                tep_db_query(
                    "update " . TABLE_PRODUCTS . " " .
                    "set products_status = IFNULL(sub_product_prev_status,1), " .
                    " sub_product_prev_status = NULL, " .
                    " products_last_modified = NOW() " .
                    "where parent_products_id = '" . (int)$productId . "'"
                );
            }else {
                tep_db_query(
                    "update " . TABLE_PRODUCTS . " " .
                    "set sub_product_prev_status = products_status, " .
                    " products_status=0, " .
                    " products_last_modified = NOW() " .
                    "where parent_products_id = '" . (int)$productId . "'"
                );
            }
        }

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus', 'allowed')) {
            $ext::productAutoSwitchOff($productId);
        }
        \common\components\CategoriesCache::getCPC()::invalidateProducts($productId);
    }

    public static function fillGlobalSort($platform_id = 0, $products_id = 0) {
      if ($products_id==0) {
        //clean up: delete if product was removed
        $sql = " delete gs from " . TABLE_PRODUCTS_GLOBAL_SORT . " gs where not exists (select * from " . TABLE_PRODUCTS . " p where p.products_id=gs.products_id)";
        if ($platform_id>0) {
          $sql .= " and platform_id='" . (int)$platform_id . "'";
        }
        tep_db_query($sql);
      }
// new products to top
// 2do new products by name
      if ($platform_id == 0) {
        $plSql = "";
      } else {
        $plSql = " and plp.platform_id='" . (int) $platform_id . "'";
      }
      if ($products_id == 0) {
        $pSql = "";
      } else {
        $pSql = " and plp.products_id='" . (int) $products_id . "'";
      }
      $sql = " insert ignore into " . TABLE_PRODUCTS_GLOBAL_SORT . " (products_id, platform_id, sort_order) select * from "
          . "(select plp.products_id, plp.platform_id,  @n:=@n+1 from " . TABLE_PLATFORMS_PRODUCTS . " plp  left join " . TABLE_PRODUCTS_GLOBAL_SORT . " gs1 on plp.products_id=gs1.products_id  and plp.platform_id =gs1.platform_id, (SELECT @n:=ifnull(max(sort_order),0) from " . TABLE_PRODUCTS_GLOBAL_SORT . ") r  where gs1.products_id is null {$plSql} {$pSql} order by " . ("plp.products_id") . ") s";
      $ret = tep_db_query($sql);


      return $ret;
    }

    public static function globalSortSerialIndex($platform_id, $start = 0, $range = [], $pids=[], $exclude=false) {
      //update products_global_sort p, (SELECT @n:=@n+1 as cnt, products_id from products_global_sort, (select @n:=0) c where platform_id=1 ORDER BY `sort_order`, `products_id` ) i set sort_order=cnt where platform_id=1 and p.products_id=i.products_id
      if (is_array($range)) {
        if (!empty($range)) {
          $range = array_map('intval', $range);
        }
      } else {
        $range = [];
      }

      if (is_array($pids)) {
        if (!empty($pids)) {
          $pids = array_map('intval', $pids);
        }
      } else {
        $pids = [intval($pids)];
      }

      $sql = " update " . TABLE_PRODUCTS_GLOBAL_SORT . " p, "
          . "(select products_id, sort_order, @n:=@n+1 as cnt from " . TABLE_PRODUCTS_GLOBAL_SORT . ", (SELECT @n:=" . (int)$start . ") c "
          . " where platform_id='" . (int)$platform_id . "' "
          . (!empty($range)?" and sort_order>=" . (int)$range[0] . " and sort_order<=" . (int)$range[1] :'')
          . (!empty($pids)?" and products_id " . ($exclude?"not ":'') . "in ('" . implode("','", $pids) . "')":'')
          . " order by sort_order, products_id) i "
          . "  set p.sort_order=cnt where platform_id='" . (int)$platform_id . "' and p.products_id=i.products_id ";
      $ret = tep_db_query($sql);
      //echo $sql . " <BR>\n";
      //if ($exclude)      die;
      //return $sql . "<BR>";
      return $ret;
    }

    public static function globalSortReindexGroupped($platform_id) {
      $ret = self::globalSortSerialIndex($platform_id);

      if ($ret ) {
        $first = true;
        $q = (new \yii\db\Query())
            ->select('p.products_groups_id')
            ->addSelect([
              'min' => new \yii\db\Expression('min(gso.sort_order)'),
              'max' => new \yii\db\Expression('max(gso.sort_order)'),
              'cnt' => new \yii\db\Expression('count(gso.products_id)'),
              'ids' => new \yii\db\Expression('group_concat(gso.products_id)'),
            ])
            ->from(['gso' => TABLE_PRODUCTS_GLOBAL_SORT, 'p' => TABLE_PRODUCTS])
            ->andWhere('p.products_id=gso.products_id and p.products_groups_id>0')
            ->andWhere(['gso.platform_id' => (int)$platform_id])
            ->groupBy('p.products_groups_id')
            ->having('min(gso.sort_order)+count(gso.products_id)-1!=max(gso.sort_order)')
            ->orderBy('max(gso.sort_order) desc')
            ;
//echo $q ->createCommand()->rawSql . "<br>\n";
        foreach ($q->all() as $gdata) {
          $gdata['ids'] = explode(",", $gdata['ids']);
          if (!$first) { // all indexes could change
            $toSort = (new \yii\db\Query())
            ->addSelect([
              'min' => new \yii\db\Expression('min(gso.sort_order)'),
              'max' => new \yii\db\Expression('max(gso.sort_order)'),
              'cnt' => new \yii\db\Expression('count(gso.products_id)'),
            ])
            ->from(['gso' => TABLE_PRODUCTS_GLOBAL_SORT])
            ->andWhere([
              'gso.platform_id' => (int)$platform_id,
              'gso.products_id' => $gdata['ids']
                ])
            ->one()
            ;
            $toSort['ids'] = $gdata['ids'];
          } else {
            $first = false;
            $toSort = $gdata;
          }

          $ret = self::globalSortReindexGroup($toSort, $platform_id);
          if (!$ret) {
            break;
          }
        }
      }
      return $ret;
    }

/**
 * sort_order MUST be serial
 * ToDo fill in $groupData if required data is missed
 * @param array $groupData
 * @param int $platform_id
 * @return bool|string true|error message
 */
    public static function globalSortReindexGroup($groupData, $platform_id) {
      $ret = self::globalSortSerialIndex($platform_id, $groupData['max']-$groupData['cnt'], [$groupData['min'], $groupData['max']], $groupData['ids']);
      if ($ret) {
        $ret = self::globalSortSerialIndex($platform_id, $groupData['min']-1, [$groupData['min'], $groupData['max']], $groupData['ids'], true);
      }
      return $ret;
    }

    public static function copyGlobalSort($fromPlatformId, $toPlatformId) {
      $ret = false;
      if ($fromPlatformId>0 && $toPlatformId>0) {
        $sql = " delete from " . TABLE_PRODUCTS_GLOBAL_SORT . " where platform_id='" . (int)$toPlatformId . "'";
        tep_db_query($sql);

        $sql = " insert ignore into " . TABLE_PRODUCTS_GLOBAL_SORT . " (products_id, platform_id, sort_order) select products_id, " . (int)$toPlatformId . ", @n:=@n+1 from "
          . "(select plp.products_id, gs1.sort_order from " . TABLE_PLATFORMS_PRODUCTS . " plp  left join " . TABLE_PRODUCTS_GLOBAL_SORT . " gs1 on plp.products_id=gs1.products_id  and gs1.platform_id='" . (int)$fromPlatformId . "' where plp.platform_id='" . (int)$toPlatformId . "' order by gs1.sort_order is null desc, gs1.sort_order, plp.products_id ) s, (SELECT @n:=0) r  ";
        $ret = tep_db_query($sql);
      }
      return $ret;
    }

    /**
     *
     * @param int $categories_id
     * @param int $start
     * @param array $range
     * @param array $pids
     * @param bool $exclude
     * @return type
     */
    public static function inCategorySortSerialIndex($categories_id, $start = 0, $range = [], $pids=[], $exclude=false) {
      if (is_array($range)) {
        if (!empty($range)) {
          $range = array_map('intval', $range);
        }
      } else {
        $range = [];
      }

      if (is_array($pids)) {
        if (!empty($pids)) {
          $pids = array_map('intval', $pids);
        }
      } else {
        $pids = [intval($pids)];
      }

      $sql = " update " . TABLE_PRODUCTS_TO_CATEGORIES . " p, "
          . "(select products_id, sort_order, @n:=@n+1 as cnt from " . TABLE_PRODUCTS_TO_CATEGORIES . ", (SELECT @n:=" . (int)$start . ") c "
          . " where categories_id='" . (int)$categories_id . "' "
          . (!empty($range)?" and sort_order>=" . (int)$range[0] . " and sort_order<=" . (int)$range[1] :'')
          . (!empty($pids)?" and products_id " . ($exclude?"not ":'') . "in ('" . implode("','", $pids) . "')":'')
          . " order by sort_order, products_id desc) i "
          . "  set p.sort_order=cnt where categories_id='" . (int)$categories_id . "' and p.products_id=i.products_id ";
      $ret = tep_db_query($sql);
      //echo $sql . " <BR>\n";
      //if ($exclude)      die;
      //return $sql . "<BR>";
      return $ret;
    }

    public static function inCategorySortReindexGroupped($categories_id) {
      $ret = self::inCategorySortSerialIndex($categories_id);

      if ($ret ) {
        $first = true;
        $q = (new \yii\db\Query())
            ->select('p.products_groups_id')
            ->addSelect([
              'min' => new \yii\db\Expression('min(p2c.sort_order)'),
              'max' => new \yii\db\Expression('max(p2c.sort_order)'),
              'cnt' => new \yii\db\Expression('count(p2c.products_id)'),
              'ids' => new \yii\db\Expression('group_concat(p2c.products_id)'),
            ])
            ->from(['p2c' => TABLE_PRODUCTS_TO_CATEGORIES, 'p' => TABLE_PRODUCTS])
            ->andWhere('p.products_id=p2c.products_id and p.products_groups_id>0')
            ->andWhere(['p2c.categories_id' => (int)$categories_id])
            ->groupBy('p.products_groups_id')
            ->having('min(p2c.sort_order)+count(p2c.products_id)-1!=max(p2c.sort_order)')
            ->orderBy('max(p2c.sort_order) desc')
            ;
        foreach ($q->all() as $gdata) {
          $gdata['ids'] = explode(",", $gdata['ids']);
          if (!$first) { // all indexes could change
            $toSort = (new \yii\db\Query())
            ->addSelect([
              'min' => new \yii\db\Expression('min(p2c.sort_order)'),
              'max' => new \yii\db\Expression('max(p2c.sort_order)'),
              'cnt' => new \yii\db\Expression('count(p2c.products_id)'),
            ])
            ->from(['p2c' => TABLE_PRODUCTS_TO_CATEGORIES])
            ->andWhere([
              'p2c.categories_id' => (int)$categories_id,
              'p2c.products_id' => $gdata['ids']
                ])
            ->one()
            ;
            $toSort['ids'] = $gdata['ids'];
          } else {
            $first = false;
            $toSort = $gdata;
          }
          if ($toSort['min']+$toSort['cnt']-1 != $toSort['max']) {

            $ret = self::inCategorySortReindexGroup($toSort, $categories_id);
            if (!$ret) {
              break;
            }
          }
        }
      }
      return $ret;
    }

/**
 * sort_order MUST be serial
 * ToDo fill in $groupData if required data is missed
 * @param array $groupData
 * @param int $categories_id
 * @return bool|string true|error message
 */
    public static function inCategorySortReindexGroup($groupData, $categories_id) {
      $ret = self::inCategorySortSerialIndex($categories_id, $groupData['min']-1, [$groupData['min'], $groupData['max']], $groupData['ids']);
      if ($ret) {
        $ret = self::inCategorySortSerialIndex($categories_id, $groupData['min']+$groupData['cnt']-1, [$groupData['min'], $groupData['max']], $groupData['ids'], true);
      }
      return $ret;
    }

    /**
     * Write Stock History by Warehouse and Supplier (and Location)
     * @param string $uProductId Product Id or Product Inventory Id
     * @param integer $warehouseId Warehouse Id. Mandatory
     * @param integer $supplierId Supplier Id. Mandatory
     * @param integer $locationId Location Id
     * @param integer $productQuantity product quantity delta
     * @param array $parameterArray array of History values
     * @return boolean true on success, false on error
     */
    public static function writeHistory($uProductId = 0, $warehouseId = 0, $supplierId = 0, $locationId = 0, $productQuantity = 0, $parameterArray = [])
    {
        $return = false;
        $productQuantity = (int)$productQuantity;
        $warehouseId = (int)$warehouseId;
        $supplierId = (int)$supplierId;
        $locationId = (int)$locationId;
        if ($productQuantity != 0 AND $warehouseId > 0 AND $supplierId > 0) {
            $parameterArray = (is_array($parameterArray) ? $parameterArray : []);
            $layers_id = (int)(isset($parameterArray['layers_id']) ? $parameterArray['layers_id'] : 0);
            $batch_id = (int)(isset($parameterArray['batch_id']) ? $parameterArray['batch_id'] : 0);
            $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
            $productRecord = self::getRecord($uProductId);
            if (($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezeProductRecord($productRecord)) {
                $productRecord = self::getRecord($uProductId, false, true);
                $return = true;
                $inventoryRecord = \common\helpers\Inventory::getRecord($uProductId);
                if (!($inventoryRecord instanceof \common\models\Inventory)) {
                    $uProductId = $productRecord->products_id;
                    $inventoryRecord = $productRecord;
                }


                //$productRecord->warehouse_stock_quantity; //22
                //$productRecord->products_quantity; // 18

                //TODO
                /*$warehouseStockQuantity = 0;
                $warehouseProductQuantity = 0;
                if (count(self::getChildArray($productRecord)) > 0) {
                    $warehouseStockQuantity += (int)$productRecord->warehouse_stock_quantity;
                    $warehouseProductQuantity += (int)$productRecord->products_quantity;
                } else {
                    foreach (\common\helpers\Warehouses::getProductArray($uProductId) as $warehouseProductRecord) {
                        if ((int)$warehouseProductRecord['warehouse_id'] == $warehouseId) {
                            $warehouseStockQuantity += (int)$warehouseProductRecord['warehouse_stock_quantity'];
                            $warehouseProductQuantity += (int)$warehouseProductRecord['products_quantity'];
                        }
                    }
                }*/
                $isTemporary = (int)trim(isset($parameterArray['is_temporary']) ? $parameterArray['is_temporary'] : 0);
                if ($isTemporary > 0) {
                    $productQuantity *= (-1);
                }
                $productQuantityPrefix = ($productQuantity > 0 ? '+' : '-');
                unset($warehouseProductRecord);


                $productRecord->warehouse_stock_quantity  = $warehouseStockQuantity = $productRecord->warehouse_stock_quantity + $productQuantity;
                $productRecord->products_quantity = $warehouseProductQuantity = $productRecord->products_quantity + $productQuantity;
                $productRecord->save();



                //log to freeze_stock_history
                $stockHistoryRecord = new \common\models\StockHistory();
                $stockHistoryRecord->setAttributes($parameterArray,false);
                try {
                    $stockHistoryRecord->warehouse_id = $warehouseId;
                    $stockHistoryRecord->suppliers_id = $supplierId;
                    $stockHistoryRecord->location_id = $locationId;
                    $stockHistoryRecord->layers_id = $layers_id;
                    $stockHistoryRecord->batch_id = $batch_id;
                    $stockHistoryRecord->products_id = $uProductId;
                    $stockHistoryRecord->prid = (int)$uProductId;
                    $stockHistoryRecord->products_model = (($inventoryRecord->products_model != '') ? $inventoryRecord->products_model : $productRecord->products_model);
                    $stockHistoryRecord->products_quantity_before = ($warehouseProductQuantity - $productQuantity);
                    $stockHistoryRecord->warehouse_quantity_before = ($isTemporary == 0 ? ($warehouseStockQuantity - $productQuantity) : $warehouseStockQuantity);
                    $stockHistoryRecord->products_quantity_update_prefix = $productQuantityPrefix;
                    $stockHistoryRecord->products_quantity_update = abs($productQuantity);
                    $stockHistoryRecord->comments = trim(isset($parameterArray['comments']) ? $parameterArray['comments'] : '');
                    $stockHistoryRecord->admin_id = (int)trim(isset($parameterArray['admin_id']) ? $parameterArray['admin_id'] : 0);
                    $stockHistoryRecord->is_temporary = $isTemporary;
                    $stockHistoryRecord->orders_id = (int)trim(isset($parameterArray['orders_id']) ? $parameterArray['orders_id'] : 0);
                    $stockHistoryRecord->date_added = (isset($parameterArray['date_added']) ? $parameterArray['date_added'] : date('Y-m-d H:i:s'));
                    $stockHistoryRecord->save();
                } catch (\Exception $exc) {
                    $return = false;
                }

                unset($warehouseProductQuantity);
                unset($warehouseStockQuantity);
                unset($productQuantityPrefix);
                unset($stockHistoryRecord);
                unset($inventoryRecord);
                unset($isTemporary);

            } elseif ($productRecord instanceof \common\models\Products) {
                $return = true;
                $inventoryRecord = \common\helpers\Inventory::getRecord($uProductId);
                if (!($inventoryRecord instanceof \common\models\Inventory)) {
                    $uProductId = $productRecord->products_id;
                    $inventoryRecord = $productRecord;
                }
                $warehouseStockQuantity = 0;
                $warehouseProductQuantity = 0;
                if (count(self::getChildArray($productRecord)) > 0) {
                    $warehouseStockQuantity += (int)$productRecord->warehouse_stock_quantity;
                    $warehouseProductQuantity += (int)$productRecord->products_quantity;
                } else {
                    foreach (\common\helpers\Warehouses::getProductArray($uProductId) as $warehouseProductRecord) {
                        if ((int)$warehouseProductRecord['warehouse_id'] == $warehouseId) {
                            $warehouseStockQuantity += (int)$warehouseProductRecord['warehouse_stock_quantity'];
                            $warehouseProductQuantity += (int)$warehouseProductRecord['products_quantity'];
                        }
                    }
                }
                $isTemporary = (int)trim(isset($parameterArray['is_temporary']) ? $parameterArray['is_temporary'] : 0);
                if ($isTemporary > 0) {
                    $productQuantity *= (-1);
                }
                $productQuantityPrefix = ($productQuantity > 0 ? '+' : '-');
                unset($warehouseProductRecord);
                $stockHistoryRecord = new \common\models\StockHistory();
                $stockHistoryRecord->setAttributes($parameterArray,false);
                try {
                    $stockHistoryRecord->warehouse_id = $warehouseId;
                    $stockHistoryRecord->suppliers_id = $supplierId;
                    $stockHistoryRecord->location_id = $locationId;
                    $stockHistoryRecord->layers_id = $layers_id;
                    $stockHistoryRecord->batch_id = $batch_id;
                    $stockHistoryRecord->products_id = $uProductId;
                    $stockHistoryRecord->prid = (int)$uProductId;
                    $stockHistoryRecord->products_model = (($inventoryRecord->products_model != '') ? $inventoryRecord->products_model : $productRecord->products_model);
                    $stockHistoryRecord->products_quantity_before = ($warehouseProductQuantity - $productQuantity);
                    $stockHistoryRecord->warehouse_quantity_before = ($isTemporary == 0 ? ($warehouseStockQuantity - $productQuantity) : $warehouseStockQuantity);
                    $stockHistoryRecord->products_quantity_update_prefix = $productQuantityPrefix;
                    $stockHistoryRecord->products_quantity_update = abs($productQuantity);
                    $stockHistoryRecord->comments = trim(isset($parameterArray['comments']) ? $parameterArray['comments'] : '');
                    $stockHistoryRecord->admin_id = (int)trim(isset($parameterArray['admin_id']) ? $parameterArray['admin_id'] : 0);
                    $stockHistoryRecord->is_temporary = $isTemporary;
                    $stockHistoryRecord->orders_id = (int)trim(isset($parameterArray['orders_id']) ? $parameterArray['orders_id'] : 0);
                    $stockHistoryRecord->date_added = (isset($parameterArray['date_added']) ? $parameterArray['date_added'] : date('Y-m-d H:i:s'));
                    $stockHistoryRecord->save();
                } catch (\Exception $exc) {
                    $return = false;
                }
                unset($warehouseProductQuantity);
                unset($warehouseStockQuantity);
                unset($productQuantityPrefix);
                unset($stockHistoryRecord);
                unset($inventoryRecord);
                unset($isTemporary);
            }
            unset($productRecord);
        }
        unset($productQuantity);
        unset($parameterArray);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($uProductId);
        return $return;
    }

    /**
     * Automatically allocating stock for Product.
     * Rules: see \common\helpers\OrderProduct->doAllocateAutomatic
     * @param mixed $productRecord Product Id or Product Inventory Id or instance of Products model
     * @param boolean $doCache defines should Product stock quantities be calculated and cached. Cleaning up invalid DB records
     * @return boolean true on success, false on error
     */
    public static function doAllocateAutomatic($productRecord = 0, $doCache = false)
    {
        $return = false;
        $productRecord = self::getRecord($productRecord);
        if ($productRecord instanceof \common\models\Products) {
            $return = true;
            $orderProductIdArray = \common\models\OrdersProducts::find()
                ->select('orders_products_id')
                ->where(['products_id' => $productRecord->products_id])
                ->andWhere(['IN', 'orders_products_status', [
                    \common\helpers\OrderProduct::OPS_QUOTED,
                    \common\helpers\OrderProduct::OPS_STOCK_DEFICIT,
                    \common\helpers\OrderProduct::OPS_STOCK_ORDERED,
                    \common\helpers\OrderProduct::OPS_RECEIVED
                    ]
                ])
                ->column();
            foreach ($orderProductIdArray as $orderProductId) {
                $return = (\common\helpers\OrderProduct::doAllocateAutomatic($orderProductId, false) AND $return);
            }
            unset($orderProductIdArray);
            unset($orderProductId);
            if ((int)$doCache > 0) {
                $return = self::doCache($productRecord);
            }
        }
        unset($productRecord);
        unset($doCache);
        return $return;
    }

    /**
     * Get product's stock deficit quantity
     * (Stock deficit = Real quantity - Received [Real quantity -> 0])
     * @param mixed $uProductId Product Id or Product Inventory Id
     * @return int product's stock deficit quantity
     */
    public static function getStockDeficit($uProductId = 0)
    {
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        return (int)\common\models\OrdersProducts::find()
            ->where(['uprid' => $uProductId])
            ->andWhere(['>', '(products_quantity - (qty_cnld + qty_rcvd))', 0])
            ->andWhere(['NOT IN', 'orders_products_status', [
                \common\helpers\OrderProduct::OPS_QUOTED,
            ]])
            ->sum('products_quantity - (qty_cnld + qty_rcvd)');
    }

    /**
     * Get Product quantity
     * @param string $uProductId Product Id or Product Inventory Id
     * @return integer Product quantity
     */
    public static function getQuantity($uProductId = 0)
    {
        $return = 0;
        foreach (\common\helpers\Warehouses::getProductArray($uProductId) as $warehouseProductRecord) {
            $return += $warehouseProductRecord['warehouse_stock_quantity'];
        }
        unset($warehouseProductRecord);
        return $return;
    }

    /**
     * Get Product quantity on supplier(s)
     * @param string $uProductId Product Id or Product Inventory Id
     * @pararm integer $supplierId specific Supplier Id or default for all suppliers
     * @return integer Product quantity on supplier(s)
     */
    public static function getQuantitySupplier($uProductId = 0, $supplierId = -1)
    {
        $return = 0;
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $supplierId = (int)$supplierId;
        $supplierQuery = \common\models\SuppliersProducts::find()
            ->where([
                'status' => 1,
                'products_id' => (int)$uProductId
            ]);
        if (\common\helpers\Inventory::isInventory($uProductId) == true) {
            $supplierQuery->andWhere(['uprid' => $uProductId]);
        }
        if ($supplierId > -1) {
            $supplierQuery->andWhere(['suppliers_id' => $supplierId]);
        }
        foreach ($supplierQuery->asArray(true)->all() as $supplierProductRecord) {
            $return += $supplierProductRecord['suppliers_quantity'];
        }
        unset($supplierProductRecord);
        unset($supplierQuery);
        return $return;
    }

    /**
     * limited use cases only!!!
     * - there isn't any condition on $productRecord->stock_control if platform is specified.
     * - skipped stock_limit on product and manufacturer.
     * Get available Product quantity
     * @param string $uProductId Product Id or Product Inventory Id
     * @param mixed $platformId Platform Id. If false - calculate for all platforms; if equals 0 - front-end mode, depending on Platform Stock Control; if greater than 0 - calculate for specific platform
     * @param mixed $warehouseId Warehouse id. Calculate for specific Warehouse if passed
     * @param mixed $supplierId Supplier id. Calculate for specific Supplier if passed
     * @param mixed $locationId Location id. Calculate for specific Location if passed
     * @param mixed $layersId Layers id. Calculate for specific Layer if passed
     * @param mixed $batchId Batch id. Calculate for specific Batch if passed
     * @return integer available Product quantity
     */
    public static function getAvailable($uProductId = 0, $platformId = false, $warehouseId = false, $supplierId = false, $locationId = false, $layersId = false, $batchId = false)
    {
        $productChildArray = self::getChildArray($uProductId);
        if (count($productChildArray) > 0) {
            $childStockArray = [];
            foreach ($productChildArray as $prid => $item) {
                $childStockArray[] = self::getAvailable($prid, $platformId, $warehouseId, $supplierId, $locationId, $layersId, $batchId);
            }
            return min($childStockArray);
        }

        $return = 0;
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $platformArray = [];
        $isStockControl = false;
        if ($platformId === false) {
            foreach (\common\models\Platforms::find()->where(['status' => 1])->asArray(true)->all() as $platformRecord) {
                $platformArray[] = (int)$platformRecord['platform_id'];
            }
            unset($platformRecord);
        } else {
            $platformId = (int)$platformId;
            if ($platformId <= 0) {
                if (defined('PLATFORM_ID') AND (int)PLATFORM_ID > 0) {
                    $platformId = PLATFORM_ID;
                } elseif (\common\classes\platform::defaultId() > 0) {
                    $platformId = \common\classes\platform::defaultId();
                } else {
                    $platformId = \common\classes\platform::currentId();
                }
                $platformId = (int)$platformId;
                if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
                    $isStockControl = $extScl::updateGetAvailable($uProductId, $platformId);
                }
            }
            $platformArray[] = (int)$platformId;
        }
        $warehouseProductSkipArray = [];
        $productAllocatedSkipArray = [];
        $productAllocatedTemporarySkipArray = [];
        $productAllocatedArray = self::getAllocatedArray($uProductId);
        $productAllocatedTemporaryArray = self::getAllocatedTemporaryArray($uProductId);
        foreach ($platformArray as $platformId) {
            $warehouseIdArray = self::getWarehouseIdPriorityArray($uProductId, 1, $platformId);
            $supplierIdArray = self::getSupplierIdPriorityArray($uProductId);
            $locationIdArray = self::getLocationIdPriorityArray($uProductId);
            $layersIdArray = self::getLayersIdPriorityArray($uProductId);
            $batchIdArray = self::getBatchIdPriorityArray($uProductId);
            foreach (\common\helpers\Warehouses::getProductArray($uProductId, $platformId) as $warehouseProductRecord) {
                if (isset($warehouseProductSkipArray[$warehouseProductRecord['warehouse_id']][$warehouseProductRecord['suppliers_id']][$warehouseProductRecord['location_id']][$warehouseProductRecord['layers_id']][$warehouseProductRecord['batch_id']])) {
                    continue;
                }
                if ($warehouseId !== false AND $warehouseId != $warehouseProductRecord['warehouse_id']) {
                    continue;
                }
                if ($supplierId !== false AND $supplierId != $warehouseProductRecord['suppliers_id']) {
                    continue;
                }
                if ($locationId !== false AND $locationId != $warehouseProductRecord['location_id']) {
                    continue;
                }
                if ($layersId !== false AND $layersId != $warehouseProductRecord['layers_id']) {
                    continue;
                }
                if ($batchId !== false AND $batchId != $warehouseProductRecord['batch_id']) {
                    continue;
                }
                if (!in_array($warehouseProductRecord['warehouse_id'], $warehouseIdArray)) {
                    continue;
                }
                if (!in_array($warehouseProductRecord['suppliers_id'], $supplierIdArray)) {
                    continue;
                }
                if (!in_array($warehouseProductRecord['location_id'], $locationIdArray)) {
                    continue;
                }
                if (!in_array($warehouseProductRecord['layers_id'], $layersIdArray)) {
                    continue;
                }
                if (!in_array($warehouseProductRecord['batch_id'], $batchIdArray)) {
                    continue;
                }
                $warehouseProductSkipArray[$warehouseProductRecord['warehouse_id']][$warehouseProductRecord['suppliers_id']][$warehouseProductRecord['location_id']][$warehouseProductRecord['layers_id']][$warehouseProductRecord['batch_id']] = $warehouseProductRecord;
                $return += $warehouseProductRecord['warehouse_stock_quantity'];
            }
            unset($warehouseProductRecord);
            foreach ($productAllocatedArray as $productAllocatedRecord) {
                if (isset($productAllocatedSkipArray[$productAllocatedRecord['warehouse_id']][$productAllocatedRecord['suppliers_id']][$productAllocatedRecord['location_id']][$productAllocatedRecord['layers_id']][$productAllocatedRecord['batch_id']][$productAllocatedRecord['orders_products_id']])) {
                    continue;
                }
                if ($isStockControl!==false && !in_array((int)$productAllocatedRecord['platform_id'], $platformArray) /*$platformId != $productAllocatedRecord['platform_id']*/) {
                    continue;
                }
                if ($warehouseId !== false AND $warehouseId != $productAllocatedRecord['warehouse_id']) {
                    continue;
                }
                if ($supplierId !== false AND $supplierId != $productAllocatedRecord['suppliers_id']) {
                    continue;
                }
                if ($locationId !== false AND $locationId != $productAllocatedRecord['location_id']) {
                    continue;
                }
                if ($layersId !== false AND $layersId != $productAllocatedRecord['layers_id']) {
                    continue;
                }
                if ($batchId !== false AND $batchId != $productAllocatedRecord['batch_id']) {
                    continue;
                }
                if (!in_array($productAllocatedRecord['warehouse_id'], $warehouseIdArray)) {
                    continue;
                }
                if (!in_array($productAllocatedRecord['suppliers_id'], $supplierIdArray)) {
                    continue;
                }
                if (!in_array($productAllocatedRecord['location_id'], $locationIdArray)) {
                    continue;
                }
                if (!in_array($productAllocatedRecord['layers_id'], $layersIdArray)) {
                    continue;
                }
                if (!in_array($productAllocatedRecord['batch_id'], $batchIdArray)) {
                    continue;
                }
                $productAllocatedSkipArray[$productAllocatedRecord['warehouse_id']][$productAllocatedRecord['suppliers_id']][$productAllocatedRecord['location_id']][$productAllocatedRecord['layers_id']][$productAllocatedRecord['batch_id']][$productAllocatedRecord['orders_products_id']] = $productAllocatedRecord;
                $return += ($productAllocatedRecord['allocate_dispatched'] - $productAllocatedRecord['allocate_received']);
            }
            unset($productAllocatedRecord);
            foreach ($productAllocatedTemporaryArray as $productAllocatedTemporaryRecord) {
                if (isset($productAllocatedTemporarySkipArray[$productAllocatedTemporaryRecord['warehouse_id']][$productAllocatedTemporaryRecord['suppliers_id']][$productAllocatedTemporaryRecord['location_id']][$productAllocatedTemporaryRecord['layers_id']][$productAllocatedTemporaryRecord['batch_id']][$productAllocatedTemporaryRecord['temporary_stock_id']])) {
                    continue;
                }
                if ($warehouseId !== false AND $warehouseId != $productAllocatedTemporaryRecord['warehouse_id']) {
                    continue;
                }
                if ($supplierId !== false AND $supplierId != $productAllocatedTemporaryRecord['suppliers_id']) {
                    continue;
                }
                if ($locationId !== false AND $locationId != $productAllocatedTemporaryRecord['location_id']) {
                    continue;
                }
                if ($layersId !== false AND $layersId != $productAllocatedTemporaryRecord['layers_id']) {
                    continue;
                }
                if ($batchId !== false AND $batchId != $productAllocatedTemporaryRecord['batch_id']) {
                    continue;
                }
                if (!in_array($productAllocatedTemporaryRecord['warehouse_id'], $warehouseIdArray)) {
                    continue;
                }
                if (!in_array($productAllocatedTemporaryRecord['suppliers_id'], $supplierIdArray)) {
                    continue;
                }
                if (!in_array($productAllocatedTemporaryRecord['location_id'], $locationIdArray)) {
                    continue;
                }
                if (!in_array($productAllocatedTemporaryRecord['layers_id'], $layersIdArray)) {
                    continue;
                }
                if (!in_array($productAllocatedTemporaryRecord['batch_id'], $batchIdArray)) {
                    continue;
                }
                $productAllocatedTemporarySkipArray[$productAllocatedTemporaryRecord['warehouse_id']][$productAllocatedTemporaryRecord['suppliers_id']][$productAllocatedTemporaryRecord['location_id']][$productAllocatedTemporaryRecord['layers_id']][$productAllocatedTemporaryRecord['batch_id']][$productAllocatedTemporaryRecord['temporary_stock_id']] = $productAllocatedTemporaryRecord;
                $return -= $productAllocatedTemporaryRecord['temporary_stock_quantity'];
            }
            unset($productAllocatedTemporaryRecord);
            unset($warehouseIdArray);
            unset($supplierIdArray);
            unset($locationIdArray);
            unset($layersIdArray);
            unset($batchIdArray);
        }
        unset($productAllocatedTemporarySkipArray);
        unset($productAllocatedTemporaryArray);
        unset($warehouseProductSkipArray);
        unset($productAllocatedSkipArray);
        unset($productAllocatedArray);
        unset($platformArray);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($layersId);
        unset($batchId);
        unset($platformId);
        unset($uProductId);
        if ($isStockControl !== false) {
            $return = ($isStockControl < $return ? $isStockControl : $return);
        }
        unset($isStockControl);
        return $return;
    }

    /**
     * Validate and updating Product Allocation records.
     * Updating Dispatched based on Delivered and Received based on Disptached.
     * Deleting orphan allocation records or where Received equals 0.
     * Rule: Received >= Dispatched >= Delivered
     * @param string $uProductId Product Id or Product Inventory Id
     * @return boolean false on error, true - if validation is passed
     */
    public static function isValidAllocated($uProductId = 0)
    {
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $orderProductSkipList = array();
        foreach (self::getAllocatedArray($uProductId, false) as $productAllocated) {
            if (!isset($orderProductSkipList[$productAllocated->orders_products_id])) {
                $orderProductSkipList[$productAllocated->orders_products_id] = $productAllocated->orders_products_id;
                $orderProductRecord = \common\helpers\OrderProduct::getRecord($productAllocated->orders_products_id);
                if ($orderProductRecord instanceof \common\models\OrdersProducts) {
                    if (\common\helpers\OrderProduct::isValidAllocated($orderProductRecord) != true) {
                        unset($orderProductRecord);
                        return false;
                    }
                } else {
                    $productAllocated->delete();
                }
                unset($orderProductRecord);
            }
        }
        unset($orderProductSkipList);
        unset($productAllocated);
        unset($uProductId);
        return true;
    }

    /**
     * Get Product Allocation array
     * @param string $uProductId Product Id or Product Inventory Id
     * @param boolean $asArray switching return type between array of arrays or array of instances of OrdersProductsAllocate
     * @param boolean $returnAwaiting false - return all product allocations, true - return not fully processed allocations
     * @return array array of mixed depending on $asArray parameter
     */
    public static function getAllocatedArray($uProductId = 0, $asArray = true, $returnAwaiting = true)
    {
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $return = \common\models\OrdersProductsAllocate::find()
            ->andWhere(['products_id' => $uProductId])
            ->andWhere(['prid' => (int)$uProductId])
            ->asArray($asArray);
        if ($returnAwaiting > 0) {
            $return->andWhere('allocate_dispatched < allocate_received');
        }
        $return = $return->all();
        unset($returnAwaiting);
        unset($uProductId);
        unset($asArray);
        return (is_array($return) ? $return : []);
    }

    /**
     * Get Product Allocation quantity
     * @param string $uProductId Product Id or Product Inventory Id
     * @return integer Product Allocation quantity
     */
    public static function getAllocated($uProductId = 0)
    {
        $return = 0;
        foreach (self::getAllocatedArray($uProductId) as $productAllocated) {
            $return += ($productAllocated['allocate_received'] - $productAllocated['allocate_dispatched']);
        }
        unset($productAllocated);
        return $return;
    }

    /**
     * Get Product Temporary Allocation array
     * Behaviour: depending on TEMPORARY_STOCK_ENABLE configuration value
     * @param string $uProductId Product Id or Product Inventory Id
     * @param boolean $asArray switching return type between array of arrays or array of instances
     * @param boolean $isBackend switching return type between Front-end OrdersProductsTemporaryStock if false, and Back-end OrdersProductsAllocate if true
     * @return array array of mixed depending on $asArray and $isBackend parameters
     */
    public static function getAllocatedTemporaryArray($uProductId = 0, $asArray = true, $isBackend = false)
    {
        $return = [];
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        if ((int)$isBackend > 0) {
            $return = \common\models\OrdersProductsAllocate::find()
                ->where(['products_id' => $uProductId])
                ->andWhere(['prid' => (int)$uProductId])
                ->andWhere(['is_temporary' => 1])
                ->andWhere(['allocate_dispatched' => 0])
                ->asArray($asArray)
                ->all();
        } else {
            if (defined('TEMPORARY_STOCK_ENABLE') AND TEMPORARY_STOCK_ENABLE == 'true') {
                if (($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezed()) {
                    $return = $ext::getAllocatedTemporaryArray($uProductId, $asArray);
                } else {
                    $return = \common\models\OrdersProductsTemporaryStock::find()
                        ->where(['normalize_id' => $uProductId])
                        ->andWhere(['prid' => (int)$uProductId])
                        ->asArray($asArray)
                        ->all();
                }
            }
        }
        unset($uProductId);
        unset($asArray);
        return (is_array($return) ? $return : []);
    }

    /**
     * Get Product Temporary Allocation quantity
     * @param string $uProductId Product Id or Product Inventory Id
     * @param boolean $isBackend switching return value calculation between Front-end OrdersProductsTemporaryStock if false, and Back-end OrdersProductsAllocate if true
     * @return integer Front-end or Back-end Product Temporary Allocation quantity depending on $isBackend parameter
     */
    public static function getAllocatedTemporary($uProductId = 0, $isBackend = false)
    {
        $return = 0;
        if ((int)$isBackend > 0) {
            foreach (self::getAllocatedTemporaryArray($uProductId, true, true) as $productAllocatedTemporary) {
                $return += ((int)$productAllocatedTemporary['allocate_received'] - (int)$productAllocatedTemporary['allocate_dispatched']);
            }
            unset($productAllocatedTemporary);
        } else {
            foreach (self::getAllocatedTemporaryArray($uProductId, true, false) as $productAllocatedTemporary) {
                $return += (int)$productAllocatedTemporary['temporary_stock_quantity'];
            }
            unset($productAllocatedTemporary);
        }
        unset($uProductId);
        return $return;
    }

    /**
     * Get product's stock ordered quantity
     * (Dependent on pending Purchase Orders Products)
     * @param string $uProductId Product Id or Product Inventory Id
     * @return int product's stock ordered quantity
     */
    public static function getStockOrdered($uProductId = 0, $isStrict = false)
    {
        $return = 0;
        if (\common\helpers\Acl::checkExtensionAllowed('PurchaseOrders')) {
            $return = \common\extensions\PurchaseOrders\helpers\PurchaseOrder::getStockOrdered($uProductId, $isStrict);
        }
        return $return;
    }

    /**
     * Get Warehouse stock allocation priority for Product.
     * Rule: if $platformId === false - get priority for all platforms available for product, else - get for current platform
     * Behaviour: dependent on WarehousePriority extension
     * @param string $uProductId Product Id or Product Inventory Id
     * @param integer $productQuantity Product quantity
     * @param integer $platformId Platform Id
     * @return array array of Warehouse Id
     */
    public static function getWarehouseIdPriorityArray($uProductId = 0, $productQuantity = 1, $platformId = 0)
    {
        $return = [];
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $productQuantity = ((int)$productQuantity > 0 ? (int)$productQuantity : 1);
        if ($platformId === false) {
            foreach (\common\models\PlatformsProducts::find()->alias('pp')
                ->leftJoin(\common\models\Platforms::tableName() . ' AS p', 'pp.platform_id = p.platform_id')
                ->where(['p.status' => '1'])
                ->andWhere(['pp.products_id' => (int)$uProductId])
                ->orderBy(['p.is_virtual' => SORT_ASC, 'p.is_default' => SORT_DESC])
                ->asArray(true)->all() as $platformsProductsRecord
            ) {
                foreach (self::getWarehouseIdPriorityArray($uProductId, $productQuantity, (int)$platformsProductsRecord['platform_id']) as $warehouseId) {
                    $return[$warehouseId] = $warehouseId;
                }
                unset($warehouseId);
            }
            unset($platformsProductsRecord);
            return $return;
        }
        $platformId = (int)$platformId;
        if ($platformId <= 0) {
            if (defined('PLATFORM_ID') AND (int)PLATFORM_ID > 0) {
                $platformId = PLATFORM_ID;
            } elseif (\common\classes\platform::defaultId() > 0) {
                $platformId = \common\classes\platform::defaultId();
            } else {
                $platformId = \common\classes\platform::currentId();
            }
            $platformId = (int)$platformId;
        }
        $isStockControl = false;
        if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
            $warehouseArray = $extScl::updateGetAvailable($uProductId, $platformId);
            if (is_array($warehouseArray)) {
                $return = $warehouseArray;
                $isStockControl = true;
            }
            unset($warehouseArray);
        }
        if ($isStockControl == false) {
            $warehouseQuery = \common\models\Warehouses::find()->alias('w')
                ->leftJoin(\common\models\WarehousesPlatforms::tableName() . ' AS wtp', [
                    'and', 'w.warehouse_id = wtp.warehouse_id', ['wtp.platform_id' => $platformId]
                ])
                ->where(['ifnull(wtp.status, w.status)' => 1])
                ->orderBy(['ifnull(wtp.sort_order, w.sort_order)' => SORT_ASC, 'w.warehouse_name' => SORT_ASC])
                ->cache((defined('ALLOW_ANY_QUERY_CACHE') && ALLOW_ANY_QUERY_CACHE=='True')?self::PRODUCT_RECORD_CACHE : -1)
                ->asArray(true);
            foreach ($warehouseQuery->all() as $warehouseRecord) {
                $return[] = (int)$warehouseRecord['warehouse_id'];
            }
            unset($warehouseRecord);
            unset($warehouseQuery);

            /**
             * @var $extension \common\extensions\WarehousePriority\WarehousePriority
             */
            if ($extension = \common\helpers\Extensions::isAllowed('WarehousePriority')) {
                $rulesArray = [
                    'products_id' => $uProductId,
                    'products_quantity' => $productQuantity,
                ];
                $warehouseIdPriorityArray = $extension::getInstance()->getPreferredWarehouseId($rulesArray, $platformId);
                unset($rulesArray);
                if (count($warehouseIdPriorityArray) > 0) {
                    $return = $warehouseIdPriorityArray;
                }
                unset($warehouseIdPriorityArray);
            }
            unset($extension);
        }
        unset($productQuantity);
        unset($isStockControl);
        unset($platformId);
        unset($uProductId);
        return $return;
    }

    /**
     * Get Supplier stock allocation priority for Product.
     * Behaviour: dependent on SupplierPriority extension
     * @param string $uProductId Product Id or Product Inventory Id
     * @return array array of Supplier Id
     */
    public static function getSupplierIdPriorityArray($uProductId = 0)
    {
        $return = [];
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $productRecord = self::getRecord($uProductId);
        if ($productRecord instanceof \common\models\Products) {
            $supplierQuery = \common\models\Suppliers::find()->alias('s')
                ->leftJoin(\common\models\SuppliersProducts::tableName() . ' AS sp', 's.suppliers_id = sp.suppliers_id')
                ->where(['sp.products_id' => (int)$uProductId])
                ->andWhere(['sp.uprid' => $uProductId])
                ->andWhere(['s.status' => 1])
                ->andWhere(['sp.status' => 1])
                ->cache((defined('ALLOW_ANY_QUERY_CACHE') && ALLOW_ANY_QUERY_CACHE=='True')?self::PRODUCT_RECORD_CACHE : -1)
                ->orderBy(['s.sort_order' => SORT_ASC, 's.suppliers_name' => SORT_ASC]);
            foreach ($supplierQuery->all() as $supplierRecord) {
                $return[] = (int)$supplierRecord['suppliers_id'];
            }
            unset($supplierRecord);
            unset($supplierQuery);
            if ($extension = \common\helpers\Acl::checkExtensionAllowed('SupplierPriority', 'getInstance')) {
                $rulesArray = \common\helpers\PriceFormula::calculateSupplierProducts($uProductId);
                $supplierIdPriorityArray = $extension::getInstance()->getPreferredSupplierId($rulesArray);
                unset($rulesArray);
                if (count($supplierIdPriorityArray) > 0) {
                    $return = $supplierIdPriorityArray;
                }
                unset($supplierIdPriorityArray);
            }
            unset($extension);
            if (count($return) == 0) {
                $return[] = (int)\common\helpers\Suppliers::getDefaultSupplierId();
            }
        }
        unset($productRecord);
        unset($uProductId);
        return $return;
    }

    /**
     * Get Location stock allocation priority for Product.
     * @param string $uProductId Product Id or Product Inventory Id
     * @return array array of Location Id
     */
    public static function getLocationIdPriorityArray($uProductId = 0)
    {
        $return = [];
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $productRecord = self::getRecord($uProductId);
        if ($productRecord instanceof \common\models\Products) {
            $locationQuery = \common\models\WarehousesProducts::find()
                ->where(['products_id' => $uProductId])
                ->andWhere(['prid' => $productRecord->products_id])
                ->orderBy(['SUM(warehouse_stock_quantity)' => SORT_DESC])
                ->groupBy(['location_id'])
                ->asArray(true);
            foreach ($locationQuery->all() as $warehouseProductRecord) {
                $return[] = (int)$warehouseProductRecord['location_id'];
            }
            unset($warehouseProductRecord);
            unset($locationQuery);
        }
        unset($productRecord);
        unset($uProductId);
        return $return;
    }

    /**
     * Get Layers stock allocation priority for Product.
     * @param string $uProductId Product Id or Product Inventory Id
     * @return array array of Layers Id
     */
    public static function getLayersIdPriorityArray($uProductId = 0)
    {
        $return = [];
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $productRecord = self::getRecord($uProductId);
        if ($productRecord instanceof \common\models\Products) {
            $layersQuery = \common\models\WarehousesProducts::find()->alias('wp')
                ->leftJoin(['wpl' => \common\models\WarehousesProductsLayers::tableName()], 'wp.layers_id = wpl.layers_id')
                ->where(['wp.products_id' => $uProductId])
                ->andWhere(['wp.prid' => $productRecord->products_id])
                ->orderBy(new \yii\db\Expression('wpl.expiry_date is null asc, wpl.expiry_date asc'))
                ->groupBy(['wp.layers_id'])
                ->asArray(true);
            foreach ($layersQuery->all() as $warehouseProductRecord) {
                $return[] = (int)$warehouseProductRecord['layers_id'];
            }
            unset($warehouseProductRecord);
            unset($layersQuery);
        }
        unset($productRecord);
        unset($uProductId);
        return $return;
    }

    /**
     * Get Batch stock allocation priority for Product.
     * @param string $uProductId Product Id or Product Inventory Id
     * @return array array of Batch Id
     */
    public static function getBatchIdPriorityArray($uProductId = 0)
    {
        $return = [];
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $productRecord = self::getRecord($uProductId);
        if ($productRecord instanceof \common\models\Products) {
            $batchQuery = \common\models\WarehousesProducts::find()->alias('wp')
                ->leftJoin(['wpb' => \common\models\WarehousesProductsBatches::tableName()], 'wp.batch_id = wpb.batch_id')
                ->where(['wp.products_id' => $uProductId])
                ->andWhere(['wp.prid' => $productRecord->products_id])
                ->orderBy(new \yii\db\Expression('wpb.batch_name is null asc, wpb.batch_name asc'))
                ->groupBy(['wp.batch_id'])
                ->asArray(true);
            foreach ($batchQuery->all() as $warehouseProductRecord) {
                $return[] = (int)$warehouseProductRecord['batch_id'];
            }
            unset($warehouseProductRecord);
            unset($batchQuery);
        }
        unset($productRecord);
        unset($uProductId);
        return $return;
    }

    /**
     * Get array of Product's Children Set
     * @param mixed $productRecord Product Id or Product Inventory Id or instance of Products model
     * @param boolean $asArray switching return type between array of arrays or array of instances of SetsProducts
     * @param integer $recursionBreak current recursion nested level, max = 10
     * @return array array of mixed depending on $asArray parameter
     */
    public static function getChildArray($productRecord = 0, $asArray = true, $recursionBreak = 0)
    {
        $return = [];
        $productRecord = self::getRecord($productRecord);
        if ($productRecord instanceof \common\models\Products) {
            if ((int)$productRecord->is_bundle > 0) {
                foreach ((\common\models\SetsProducts::find()->alias('sp')
                    ->leftJoin(\common\models\Products::tableName() . ' AS p', 'sp.product_id = p.products_id')
                    ->where(['sp.sets_id' => (int)$productRecord->products_id])
                    ->andWhere(['p.products_status_bundle' => 1])
                    ->asArray($asArray)->all()) as $productSetRecord
                ) {
                    $uProductId = trim(is_array($productSetRecord) ? $productSetRecord['product_id'] : $productSetRecord->product_id);
                    if (($recursionBreak < 10) AND (count(self::getChildArray($uProductId, true, ($recursionBreak + 1))) == 0)) {
                        $return[$uProductId] = $productSetRecord;
                    }
                    unset($uProductId);
                }
                unset($productSetRecord);
            }
        }
        unset($productRecord);
        unset($asArray);
        return $return;
    }

    /**
     * Calculate and cache Product stock quantities. Cleaning up invalid DB records
     * @param mixed $productRecord Product Id or Product Inventory Id or instance of Products model
     * @return boolean true on success, false on error
     */
    public static function doCache($productRecord = 0)
    {
        $return = false;
        $productRecord = self::getRecord($productRecord, false);
        if (($extFreeze = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $extFreeze::isFreezeProductRecord($productRecord)) {
            $productId = trim($productRecord->products_id);

            //warehouse_stock_quantity(tmp) = warehouse_stock_quantity(real) - allocated_stock_quantity(real)
            $warehouse_stock_quantity_real = 0;
            foreach (\common\models\WarehousesProducts::find()->where(['prid' => $productId])->all() as $warehouseProductRecord) {
                $warehouse_stock_quantity_real = $warehouseProductRecord->warehouse_stock_quantity;
            }
            unset($warehouseProductRecord);

            $allocated_stock_quantity_real = 0;
            foreach (\common\models\OrdersProductsAllocate::find()->where(['prid' => $productId])->asArray(true)->all() as $productAllocatedRecord) {
                $allocated_stock_quantity_real += ($productAllocatedRecord['allocate_received'] - $productAllocatedRecord['allocate_dispatched']);
            }
            unset($productAllocatedRecord);

            $realProductRecord = self::getRecord($productId, false, true);
            if ($realProductRecord instanceof \common\models\Products) {
                $realProductRecord->warehouse_stock_quantity = $warehouse_stock_quantity_real;
                $realProductRecord->allocated_stock_quantity = $allocated_stock_quantity_real;
                $realProductRecord->products_quantity = $realProductRecord->warehouse_stock_quantity - ($realProductRecord->allocated_stock_quantity + $realProductRecord->temporary_stock_quantity);
                $realProductRecord->save();
                //---
                $inventoryArray = [];
                $inventoryId = 0;

                $warehouseProductArray = [];
                foreach (\common\models\WarehousesProducts::find()->where(['prid' => $productId])->all() as $warehouseProductRecord) {
                    $key = ((int)$warehouseProductRecord->warehouse_id . '_' . (int)$warehouseProductRecord->suppliers_id . '_' . (int)$warehouseProductRecord->location_id . '_' . (int)$warehouseProductRecord->layers_id . '_' . (int)$warehouseProductRecord->batch_id);
                    $warehouseProductRecord->products_quantity = $warehouseProductRecord->warehouse_stock_quantity;
                    $warehouseProductRecord->allocated_stock_quantity = 0;
                    $warehouseProductRecord->temporary_stock_quantity = 0;
                    $warehouseProductArray[$key][$warehouseProductRecord->products_id] = $warehouseProductRecord;
                    unset($key);
                }
                unset($warehouseProductRecord);

                $productAllocatedTemporaryArray = [];
                if (defined('TEMPORARY_STOCK_ENABLE') AND TEMPORARY_STOCK_ENABLE == 'true') {
                    foreach (\common\models\OrdersProductsTemporaryStock::find()->where(['prid' => $productId])
                        ->asArray(true)->all() as $productAllocatedTemporaryRecord
                    ) {
                        $key = ((int)$productAllocatedTemporaryRecord['warehouse_id'] . '_' . (int)$productAllocatedTemporaryRecord['suppliers_id'] . '_' . (int)$productAllocatedTemporaryRecord['location_id'] . '_' . (int)$productAllocatedTemporaryRecord['layers_id'] . '_' . (int)$productAllocatedTemporaryRecord['batch_id']);
                        $productAllocatedTemporaryArray[$key][] = $productAllocatedTemporaryRecord;
                        unset($key);
                    }
                    unset($productAllocatedTemporaryRecord);
                }

                $productAllocatedArray = [];
                foreach (\common\models\OrdersProductsAllocate::find()->where(['prid' => $productId])
                    ->andWhere('allocate_dispatched < allocate_received')
                    ->asArray(true)->all() as $productAllocatedRecord
                ) {
                    $key = ((int)$productAllocatedRecord['warehouse_id'] . '_' . (int)$productAllocatedRecord['suppliers_id'] . '_' . (int)$productAllocatedRecord['location_id'] . '_' . (int)$productAllocatedRecord['layers_id'] . '_' . (int)$productAllocatedRecord['batch_id']);
                    $productAllocatedArray[$key][] = $productAllocatedRecord;
                    unset($key);
                }
                unset($productAllocatedRecord);

                foreach ($productAllocatedArray as $key => $orderProductAllocatedArray) {
                    foreach ($orderProductAllocatedArray as $productAllocatedRecord) {
                        if (isset($warehouseProductArray[$key][$productAllocatedRecord['products_id']])) {
                            $warehouseProductRecord = $warehouseProductArray[$key][$productAllocatedRecord['products_id']];
                        } else {
                            $warehouseProductRecord = new \common\models\WarehousesProducts();
                            $warehouseProductRecord->prid = $productAllocatedRecord['prid'];
                            $warehouseProductRecord->products_id = $productAllocatedRecord['products_id'];
                            $warehouseProductRecord->warehouse_id = $productAllocatedRecord['warehouse_id'];
                            $warehouseProductRecord->suppliers_id = $productAllocatedRecord['suppliers_id'];
                            $warehouseProductRecord->location_id = $productAllocatedRecord['location_id'];
                            $warehouseProductRecord->layers_id = $productAllocatedRecord['layers_id'];
                            $warehouseProductRecord->batch_id = $productAllocatedRecord['batch_id'];
                        }
                        $warehouseProductRecord->allocated_stock_quantity += ($productAllocatedRecord['allocate_received'] - $productAllocatedRecord['allocate_dispatched']);
                        $warehouseProductRecord->products_quantity = ($warehouseProductRecord->warehouse_stock_quantity - $warehouseProductRecord->allocated_stock_quantity);
                        $warehouseProductArray[$key][$productAllocatedRecord['products_id']] = $warehouseProductRecord;
                    }
                    unset($productAllocatedRecord);
                }
                unset($orderProductAllocatedArray);
                unset($productAllocatedArray);
                unset($key);

                foreach ($productAllocatedTemporaryArray as $key => $orderProductAllocatedTemporaryArray) {
                    foreach ($orderProductAllocatedTemporaryArray as $productAllocatedTemporaryRecord) {
                        if (isset($warehouseProductArray[$key][$productAllocatedTemporaryRecord['normalize_id']])) {
                            $warehouseProductRecord = $warehouseProductArray[$key][$productAllocatedTemporaryRecord['normalize_id']];
                        } else {
                            $warehouseProductRecord = new \common\models\WarehousesProducts();
                            $warehouseProductRecord->prid = $productAllocatedTemporaryRecord['prid'];
                            $warehouseProductRecord->products_id = $productAllocatedTemporaryRecord['normalize_id'];
                            $warehouseProductRecord->warehouse_id = $productAllocatedTemporaryRecord['warehouse_id'];
                            $warehouseProductRecord->suppliers_id = $productAllocatedTemporaryRecord['suppliers_id'];
                            $warehouseProductRecord->location_id = $productAllocatedTemporaryRecord['location_id'];
                            $warehouseProductRecord->layers_id = $productAllocatedTemporaryRecord['layers_id'];
                            $warehouseProductRecord->batch_id = $productAllocatedTemporaryRecord['batch_id'];
                        }
                        $warehouseProductRecord->temporary_stock_quantity += $productAllocatedTemporaryRecord['temporary_stock_quantity'];
                        $warehouseProductRecord->products_quantity = ($warehouseProductRecord->warehouse_stock_quantity - ($warehouseProductRecord->allocated_stock_quantity + $warehouseProductRecord->temporary_stock_quantity));
                        $warehouseProductArray[$key][$productAllocatedTemporaryRecord['normalize_id']] = $warehouseProductRecord;
                        unset($warehouseProductRecord);
                    }
                    unset($productAllocatedTemporaryRecord);
                }
                unset($orderProductAllocatedTemporaryArray);
                unset($productAllocatedTemporaryArray);
                unset($key);

                foreach ($warehouseProductArray as $warehouseProductRecordArray) {
                    if ($inventoryId > 0) {
                        $warehouseProductCollectionRecord = new \common\models\WarehousesProducts();
                    }
                    foreach ($warehouseProductRecordArray as $warehouseProductRecord) {
                        try {
                            $warehouseProductRecord->save();
                            $warehouseIdArray = self::getWarehouseIdPriorityArray($warehouseProductRecord->products_id, 1, false);
                            $supplierIdArray = self::getSupplierIdPriorityArray($warehouseProductRecord->products_id);
                            if (!in_array((int)$warehouseProductRecord->warehouse_id, $warehouseIdArray)
                                OR !in_array((int)$warehouseProductRecord->suppliers_id, $supplierIdArray)
                            ) {
                                $warehouseProductRecord->warehouse_stock_quantity = 0;
                                $warehouseProductRecord->products_quantity = ($warehouseProductRecord->warehouse_stock_quantity - ($warehouseProductRecord->allocated_stock_quantity + $warehouseProductRecord->temporary_stock_quantity));
                            }
                            unset($warehouseIdArray);
                            unset($supplierIdArray);
                            if (isset($warehouseProductCollectionRecord)) {
                                $warehouseProductCollectionRecord->prid = $warehouseProductRecord->prid;
                                $warehouseProductCollectionRecord->products_id = $warehouseProductRecord->prid;
                                $warehouseProductCollectionRecord->warehouse_id = $warehouseProductRecord->warehouse_id;
                                $warehouseProductCollectionRecord->suppliers_id = $warehouseProductRecord->suppliers_id;
                                $warehouseProductCollectionRecord->location_id = $warehouseProductRecord->location_id;
                                $warehouseProductCollectionRecord->layers_id = $warehouseProductRecord->layers_id;
                                $warehouseProductCollectionRecord->batch_id = $warehouseProductRecord->batch_id;
                                $warehouseProductCollectionRecord->warehouse_stock_quantity += $warehouseProductRecord->warehouse_stock_quantity;
                                $warehouseProductCollectionRecord->allocated_stock_quantity += $warehouseProductRecord->allocated_stock_quantity;
                                $warehouseProductCollectionRecord->temporary_stock_quantity += $warehouseProductRecord->temporary_stock_quantity;
                                $warehouseProductCollectionRecord->products_quantity += $warehouseProductRecord->products_quantity;
                            }
                            if ($inventoryId > 0 AND isset($inventoryArray[$warehouseProductRecord->products_id])) {
                                $inventoryArray[$warehouseProductRecord->products_id]->warehouse_stock_quantity += $warehouseProductRecord->warehouse_stock_quantity;
                                $inventoryArray[$warehouseProductRecord->products_id]->allocated_stock_quantity += $warehouseProductRecord->allocated_stock_quantity;
                                $inventoryArray[$warehouseProductRecord->products_id]->temporary_stock_quantity += $warehouseProductRecord->temporary_stock_quantity;
                                $inventoryArray[$warehouseProductRecord->products_id]->products_quantity += $warehouseProductRecord->products_quantity;
                            }
                            //$productRecord->warehouse_stock_quantity += $warehouseProductRecord->warehouse_stock_quantity;
                            //$productRecord->allocated_stock_quantity += $warehouseProductRecord->allocated_stock_quantity;
                            //$productRecord->temporary_stock_quantity += $warehouseProductRecord->temporary_stock_quantity;
                            //$productRecord->products_quantity += $warehouseProductRecord->products_quantity;
                        } catch (\Exception $exc) {
                            $return = false;
                        }
                    }
                    if (isset($warehouseProductCollectionRecord)) {
                        try {
                            $warehouseProductCollectionRecord->save();
                        } catch (\Exception $exc) {
                            $return = false;
                        }
                    }
                    unset($warehouseProductCollectionRecord);
                    unset($warehouseProductRecord);
                }
                unset($warehouseProductRecordArray);
                unset($warehouseProductArray);
                //---
            }

            $productRecord->warehouse_stock_quantity = $warehouse_stock_quantity_real - $allocated_stock_quantity_real;

            $allocated_stock_quantity = $extFreeze::allocatedStockQuantity($productId);
            $productRecord->allocated_stock_quantity = $allocated_stock_quantity;

            $temporary_stock_quantity = $extFreeze::temporaryStockQuantity($productId);
            $productRecord->temporary_stock_quantity = $temporary_stock_quantity;

            $productRecord->products_quantity = $productRecord->warehouse_stock_quantity - ($productRecord->allocated_stock_quantity + $productRecord->temporary_stock_quantity);

            /*$return = true;
            try {
                $productRecord->save();
            } catch (\Exception $exc) {
                $return = false;
            }*/
            //$productRecord = self::getRecord($productId, false, true);
        }
        if ($productRecord instanceof \common\models\Products) {
            if (self::doCacheParent($productRecord) == true) {
                return true;
            }
            $productId = trim($productRecord->products_id);
            $productRecord->warehouse_stock_quantity = 0;
            $productRecord->allocated_stock_quantity = 0;
            $productRecord->temporary_stock_quantity = 0;
            $productRecord->products_quantity = 0;
            $inventoryArray = [];
            $inventoryId = 0;
            if (\common\helpers\Extensions::isAllowed('Inventory')) {
                foreach (\common\models\Inventory::find()->where(['prid' => $productRecord->products_id])->all() as $inventoryRecord) {
                    if (\common\helpers\Inventory::isInventory($inventoryRecord->products_id) != true) {
                        continue;
                    }
                    $inventoryRecord->warehouse_stock_quantity = 0;
                    $inventoryRecord->allocated_stock_quantity = 0;
                    $inventoryRecord->temporary_stock_quantity = 0;
                    $inventoryRecord->products_quantity = 0;
                    $inventoryArray[$inventoryRecord->products_id] = $inventoryRecord;
                }
                unset($inventoryRecord);
            }
            if (count($inventoryArray) == 0) {
                $inventoryArray[$productId] = $productId;
            } else {
                $inventoryId = $productId;
                \common\models\WarehousesProducts::deleteAll(['AND', ['prid' => $productId], ['NOT IN', 'products_id', array_map('strval', array_keys($inventoryArray))]]);
            }

            if ((int)$productRecord->manual_stock_unlimited > 0) {
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('SupplierPurchase', 'allowed')) {
                    \common\models\WarehousesProducts::deleteAll(['prid' => $productId, 'suppliers_id' => \common\helpers\Suppliers::getDefaultSupplierId()]);
                } else {
                    \common\models\WarehousesProducts::deleteAll(['prid' => $productId]);
                }
                foreach ($inventoryArray as $iId => $iRecord) {
                    $warehouseId = self::getWarehouseIdPriorityArray($iId, 1, false);
                    reset($warehouseId);
                    $warehouseId = (int)current($warehouseId);
                    $supplierId = self::getSupplierIdPriorityArray($iId);
                    reset($supplierId);
                    $supplierId = (int)current($supplierId);
                    $locationId = 0;
                    $layersId = 0;
                    $batchId = 0;
                    $warehouseProductRecord = new \common\models\WarehousesProducts();
                    $warehouseProductRecord->warehouse_id = $warehouseId;
                    $warehouseProductRecord->suppliers_id = $supplierId;
                    $warehouseProductRecord->location_id = $locationId;
                    $warehouseProductRecord->layers_id = $layersId;
                    $warehouseProductRecord->batch_id = $batchId;
                    $warehouseProductRecord->products_id = $iId;
                    $warehouseProductRecord->prid = (int)$iId;
                    $warehouseProductRecord->products_model = $productRecord->products_model;
                    if (($iRecord instanceof \common\models\Inventory) AND $iRecord->products_model != '') {
                        $warehouseProductRecord->products_model = $iRecord->products_model;
                    }
                    $warehouseProductRecord->warehouse_stock_quantity = 9999;
                    try {
                        $warehouseProductRecord->save();
                    } catch (\Exception $exc) {}
                    unset($warehouseProductRecord);
                    unset($warehouseId);
                    unset($supplierId);
                    unset($locationId);
                }
                unset($iRecord);
                unset($iId);
            }

            $warehouseProductArray = [];
            foreach (\common\models\WarehousesProducts::find()->where(['prid' => $productId])
                ->andWhere(['IN', 'products_id', array_map('strval', array_keys($inventoryArray))])->all() as $warehouseProductRecord
            ) {
                $key = ((int)$warehouseProductRecord->warehouse_id . '_' . (int)$warehouseProductRecord->suppliers_id . '_' . (int)$warehouseProductRecord->location_id . '_' . (int)$warehouseProductRecord->layers_id . '_' . (int)$warehouseProductRecord->batch_id);
                $warehouseProductRecord->products_quantity = $warehouseProductRecord->warehouse_stock_quantity;
                $warehouseProductRecord->allocated_stock_quantity = 0;
                $warehouseProductRecord->temporary_stock_quantity = 0;
                $warehouseProductArray[$key][$warehouseProductRecord->products_id] = $warehouseProductRecord;
                unset($key);
            }
            unset($warehouseProductRecord);

            $productAllocatedArray = [];
            foreach (\common\models\OrdersProductsAllocate::find()->where(['prid' => $productId])
                ->andWhere('allocate_dispatched < allocate_received')
                ->andWhere(['IN', 'products_id', array_map('strval', array_keys($inventoryArray))])->asArray(true)->all() as $productAllocatedRecord
            ) {
                $key = ((int)$productAllocatedRecord['warehouse_id'] . '_' . (int)$productAllocatedRecord['suppliers_id'] . '_' . (int)$productAllocatedRecord['location_id'] . '_' . (int)$productAllocatedRecord['layers_id'] . '_' . (int)$productAllocatedRecord['batch_id']);
                $productAllocatedArray[$key][] = $productAllocatedRecord;
                unset($key);
            }
            unset($productAllocatedRecord);

            $productAllocatedTemporaryArray = [];
            if (defined('TEMPORARY_STOCK_ENABLE') AND TEMPORARY_STOCK_ENABLE == 'true') {
                foreach (\common\models\OrdersProductsTemporaryStock::find()->where(['prid' => $productId])
                    ->andWhere(['IN', 'normalize_id', array_map('strval', array_keys($inventoryArray))])->asArray(true)->all() as $productAllocatedTemporaryRecord
                ) {
                    $key = ((int)$productAllocatedTemporaryRecord['warehouse_id'] . '_' . (int)$productAllocatedTemporaryRecord['suppliers_id'] . '_' . (int)$productAllocatedTemporaryRecord['location_id'] . '_' . (int)$productAllocatedTemporaryRecord['layers_id'] . '_' . (int)$productAllocatedTemporaryRecord['batch_id']);
                    $productAllocatedTemporaryArray[$key][] = $productAllocatedTemporaryRecord;
                    unset($key);
                }
                unset($productAllocatedTemporaryRecord);
            }
            unset($productId);

            foreach ($productAllocatedArray as $key => $orderProductAllocatedArray) {
                foreach ($orderProductAllocatedArray as $productAllocatedRecord) {
                    if (isset($warehouseProductArray[$key][$productAllocatedRecord['products_id']])) {
                        $warehouseProductRecord = $warehouseProductArray[$key][$productAllocatedRecord['products_id']];
                    } else {
                        $warehouseProductRecord = new \common\models\WarehousesProducts();
                        $warehouseProductRecord->prid = $productAllocatedRecord['prid'];
                        $warehouseProductRecord->products_id = $productAllocatedRecord['products_id'];
                        $warehouseProductRecord->warehouse_id = $productAllocatedRecord['warehouse_id'];
                        $warehouseProductRecord->suppliers_id = $productAllocatedRecord['suppliers_id'];
                        $warehouseProductRecord->location_id = $productAllocatedRecord['location_id'];
                        $warehouseProductRecord->layers_id = $productAllocatedRecord['layers_id'];
                        $warehouseProductRecord->batch_id = $productAllocatedRecord['batch_id'];
                    }
                    $warehouseProductRecord->allocated_stock_quantity += ($productAllocatedRecord['allocate_received'] - $productAllocatedRecord['allocate_dispatched']);
                    $warehouseProductRecord->products_quantity = ($warehouseProductRecord->warehouse_stock_quantity - $warehouseProductRecord->allocated_stock_quantity);
                    $warehouseProductArray[$key][$productAllocatedRecord['products_id']] = $warehouseProductRecord;
                }
                unset($productAllocatedRecord);
            }
            unset($orderProductAllocatedArray);
            unset($productAllocatedArray);
            unset($key);

            foreach ($productAllocatedTemporaryArray as $key => $orderProductAllocatedTemporaryArray) {
                foreach ($orderProductAllocatedTemporaryArray as $productAllocatedTemporaryRecord) {
                    if (isset($warehouseProductArray[$key][$productAllocatedTemporaryRecord['normalize_id']])) {
                        $warehouseProductRecord = $warehouseProductArray[$key][$productAllocatedTemporaryRecord['normalize_id']];
                    } else {
                        $warehouseProductRecord = new \common\models\WarehousesProducts();
                        $warehouseProductRecord->prid = $productAllocatedTemporaryRecord['prid'];
                        $warehouseProductRecord->products_id = $productAllocatedTemporaryRecord['normalize_id'];
                        $warehouseProductRecord->warehouse_id = $productAllocatedTemporaryRecord['warehouse_id'];
                        $warehouseProductRecord->suppliers_id = $productAllocatedTemporaryRecord['suppliers_id'];
                        $warehouseProductRecord->location_id = $productAllocatedTemporaryRecord['location_id'];
                        $warehouseProductRecord->layers_id = $productAllocatedTemporaryRecord['layers_id'];
                        $warehouseProductRecord->batch_id = $productAllocatedTemporaryRecord['batch_id'];
                    }
                    $warehouseProductRecord->temporary_stock_quantity += $productAllocatedTemporaryRecord['temporary_stock_quantity'];
                    $warehouseProductRecord->products_quantity = ($warehouseProductRecord->warehouse_stock_quantity - ($warehouseProductRecord->allocated_stock_quantity + $warehouseProductRecord->temporary_stock_quantity));
                    $warehouseProductArray[$key][$productAllocatedTemporaryRecord['normalize_id']] = $warehouseProductRecord;
                    unset($warehouseProductRecord);
                }
                unset($productAllocatedTemporaryRecord);
            }
            unset($orderProductAllocatedTemporaryArray);
            unset($productAllocatedTemporaryArray);
            unset($key);

            $return = true;
            foreach ($warehouseProductArray as $warehouseProductRecordArray) {
                if ($inventoryId > 0) {
                    $warehouseProductCollectionRecord = new \common\models\WarehousesProducts();
                }
                foreach ($warehouseProductRecordArray as $warehouseProductRecord) {
                    try {
                        $warehouseProductRecord->save();
                        $warehouseIdArray = self::getWarehouseIdPriorityArray($warehouseProductRecord->products_id, 1, false);
                        $supplierIdArray = self::getSupplierIdPriorityArray($warehouseProductRecord->products_id);
                        if (!in_array((int)$warehouseProductRecord->warehouse_id, $warehouseIdArray)
                            OR !in_array((int)$warehouseProductRecord->suppliers_id, $supplierIdArray)
                        ) {
                            $warehouseProductRecord->warehouse_stock_quantity = 0;
                            $warehouseProductRecord->products_quantity = ($warehouseProductRecord->warehouse_stock_quantity - ($warehouseProductRecord->allocated_stock_quantity + $warehouseProductRecord->temporary_stock_quantity));
                        }
                        unset($warehouseIdArray);
                        unset($supplierIdArray);
                        if (isset($warehouseProductCollectionRecord)) {
                            $warehouseProductCollectionRecord->prid = $warehouseProductRecord->prid;
                            $warehouseProductCollectionRecord->products_id = $warehouseProductRecord->prid;
                            $warehouseProductCollectionRecord->warehouse_id = $warehouseProductRecord->warehouse_id;
                            $warehouseProductCollectionRecord->suppliers_id = $warehouseProductRecord->suppliers_id;
                            $warehouseProductCollectionRecord->location_id = $warehouseProductRecord->location_id;
                            $warehouseProductCollectionRecord->layers_id = $warehouseProductRecord->layers_id;
                            $warehouseProductCollectionRecord->batch_id = $warehouseProductRecord->batch_id;
                            $warehouseProductCollectionRecord->warehouse_stock_quantity += $warehouseProductRecord->warehouse_stock_quantity;
                            $warehouseProductCollectionRecord->allocated_stock_quantity += $warehouseProductRecord->allocated_stock_quantity;
                            $warehouseProductCollectionRecord->temporary_stock_quantity += $warehouseProductRecord->temporary_stock_quantity;
                            $warehouseProductCollectionRecord->products_quantity += $warehouseProductRecord->products_quantity;
                        }
                        if ($inventoryId > 0 AND isset($inventoryArray[$warehouseProductRecord->products_id])) {
                            $inventoryArray[$warehouseProductRecord->products_id]->warehouse_stock_quantity += $warehouseProductRecord->warehouse_stock_quantity;
                            $inventoryArray[$warehouseProductRecord->products_id]->allocated_stock_quantity += $warehouseProductRecord->allocated_stock_quantity;
                            $inventoryArray[$warehouseProductRecord->products_id]->temporary_stock_quantity += $warehouseProductRecord->temporary_stock_quantity;
                            $inventoryArray[$warehouseProductRecord->products_id]->products_quantity += $warehouseProductRecord->products_quantity;
                        }
                        $productRecord->warehouse_stock_quantity += $warehouseProductRecord->warehouse_stock_quantity;
                        $productRecord->allocated_stock_quantity += $warehouseProductRecord->allocated_stock_quantity;
                        $productRecord->temporary_stock_quantity += $warehouseProductRecord->temporary_stock_quantity;
                        $productRecord->products_quantity += $warehouseProductRecord->products_quantity;
                    } catch (\Exception $exc) {
                        $return = false;
                    }
                }
                if (isset($warehouseProductCollectionRecord)) {
                    try {
                        $warehouseProductCollectionRecord->save();
                    } catch (\Exception $exc) {
                        $return = false;
                    }
                }
                unset($warehouseProductCollectionRecord);
                unset($warehouseProductRecord);
            }
            unset($warehouseProductRecordArray);
            unset($warehouseProductArray);
            if ($inventoryId > 0) {
                foreach ($inventoryArray as $inventoryRecord) {
                    try {
                        $inventoryRecord->suppliers_stock_quantity = self::getQuantitySupplier($inventoryRecord->products_id);
                        $inventoryRecord->save();
                    } catch (\Exception $exc) {
                        $return = false;
                    }
                }
                unset($inventoryRecord);
            }
            unset($inventoryArray);
            unset($inventoryId);
            try {
                $productRecord->suppliers_stock_quantity = self::getQuantitySupplier($productRecord->products_id);

                // {{ switch off EOL
                if ( $productRecord->stock_indication_id && $productRecord->products_quantity<=0 && in_array((int)$productRecord->stock_indication_id, \common\classes\StockIndication::productDisableByStockIds()) ){
                    if ( ($productRecord->products_quantity+$productRecord->temporary_stock_quantity)<=0 ) {
                        $productRecord->products_status = 0;
                    }
                }
                // }} switch off EOL
                // {{ reset to default
                if ( $productRecord->stock_indication_id && $productRecord->products_quantity<=0 && in_array((int)$productRecord->stock_indication_id, \common\classes\StockIndication::productResetToDefaultStockIds()) ){
                    if ( ($productRecord->products_quantity+$productRecord->temporary_stock_quantity)<=0 ) {
                        $productRecord->stock_indication_id = 0;
                        $productRecord->stock_delivery_terms_id = 0;
                    }
                }

                // }} reset to default

                $productRecord->save(false);
            } catch (\Exception $exc) {
                $return = false;
            }
            \common\components\CategoriesCache::getCPC()::invalidateProducts((int)$productRecord->products_id);
        }
        unset($productRecord);
        return $return;
    }

    private static function doCacheParent(\common\models\Products $productRecord)
    {
        $return = false;
        $productChildArray = self::getChildArray($productRecord);
        if (count($productChildArray) > 0) {
            \common\models\WarehousesProducts::deleteAll(['prid' => (int)$productRecord->products_id]);
            $bpWarehouseStock = -1;
            $bpAllocatedStock = -1;
            $bpTemporaryStock = -1;
            $bpAvailableStock = -1;
            foreach ($productChildArray as $productChildId => $productChildData) {
                $productChildQuantity = (int)$productChildData['num_product'];
                $bcWarehouseStock = 0;
                $bcAllocatedStock = 0;
                $bcTemporaryStock = 0;
                if (count(self::getChildArray($productChildId)) > 0) {
                    $pcRecord = self::getRecord($productChildId);
                    $bcWarehouseStock += (int)$pcRecord->warehouse_stock_quantity;
                    $bcAllocatedStock += (int)$pcRecord->allocated_stock_quantity;
                    $bcTemporaryStock += (int)$pcRecord->temporary_stock_quantity;
                    unset($pcRecord);
                } else {
                    foreach (\common\helpers\Warehouses::getProductArray($productChildId) as $wpRecord) {
                        $bcWarehouseStock += (int)$wpRecord['warehouse_stock_quantity'];
                        $bcAllocatedStock += (int)$wpRecord['allocated_stock_quantity'];
                        $bcTemporaryStock += (int)$wpRecord['temporary_stock_quantity'];
                    }
                    unset($wpRecord);
                }
                $bcWarehouseStock -= ($bcAllocatedStock + $bcTemporaryStock);
                $bcAllocatedStock = 0;
                $bcTemporaryStock = 0;
                foreach ((\common\models\OrdersProducts::find()
                    ->where(['products_id' => $productChildId])
                    //->andWhere('(`template_uprid` REGEXP "{sub}' . (int)$productRecord->products_id . '$")')
                    //->andWhere('(`template_uprid` = CONCAT(uprid, "{sub}", ' . (int)$productRecord->products_id . '))')
                    ->andWhere('`template_uprid` LIKE "' . $productChildId . '%{sub}' . (int)$productRecord->products_id . '"')
                    ->andWhere(['IN', 'orders_products_status', [
                        \common\helpers\OrderProduct::OPS_QUOTED,
                        \common\helpers\OrderProduct::OPS_STOCK_DEFICIT,
                        \common\helpers\OrderProduct::OPS_STOCK_ORDERED,
                        \common\helpers\OrderProduct::OPS_RECEIVED
                    ]])
                    ->asArray(true)->all()) as $opRecord
                ) {
                    $bcAllocatedStock += ((int)$opRecord['qty_rcvd'] - (int)$opRecord['qty_dspd']);
                    $bcWarehouseStock += ((int)$opRecord['qty_rcvd'] - (int)$opRecord['qty_dspd']);
                }
                unset($opRecord);
                foreach ((\common\models\OrdersProductsTemporaryStock::find()
                    ->where(['prid' => $productChildId])
                    ->andWhere(['parent_id' => (int)$productRecord->products_id])
                    ->asArray(true)->all()) as $optsRecord
                ) {
                    $bcTemporaryStock += (int)$optsRecord['temporary_stock_quantity'];
                    $bcWarehouseStock += (int)$optsRecord['temporary_stock_quantity'];
                }
                unset($optsRecord);
                $bcWarehouseStock = (int)floor($bcWarehouseStock / $productChildQuantity);
                $bcAllocatedStock = (int)ceil($bcAllocatedStock / $productChildQuantity);
                $bcTemporaryStock = (int)ceil($bcTemporaryStock / $productChildQuantity);
                unset($productChildQuantity);
                $bcAvailableStock = ($bcWarehouseStock - ($bcAllocatedStock + $bcTemporaryStock));
                if (($bpAvailableStock < 0) OR ($bpAvailableStock > $bcAvailableStock)) {
                    $bpWarehouseStock = $bcWarehouseStock;
                    $bpAllocatedStock = $bcAllocatedStock;
                    $bpTemporaryStock = $bcTemporaryStock;
                    $bpAvailableStock = $bcAvailableStock;
                }
                unset($bcWarehouseStock);
                unset($bcAllocatedStock);
                unset($bcTemporaryStock);
                unset($bcAvailableStock);
            }
            unset($bpAvailableStock);
            unset($productChildData);
            unset($productChildId);
            $bpWarehouseStock = ($bpWarehouseStock < 0 ? 0 : $bpWarehouseStock);
            $bpAllocatedStock = ($bpAllocatedStock < 0 ? 0 : $bpAllocatedStock);
            $bpTemporaryStock = ($bpTemporaryStock < 0 ? 0 : $bpTemporaryStock);
            $productRecord->warehouse_stock_quantity = $bpWarehouseStock;
            $productRecord->allocated_stock_quantity = $bpAllocatedStock;
            $productRecord->temporary_stock_quantity = $bpTemporaryStock;
            $productRecord->products_quantity = ($bpWarehouseStock - ($bpAllocatedStock + $bpTemporaryStock));
            try {
                $productRecord->save();
                $return = true;
            } catch (\Exception $exc) {}
            unset($bpWarehouseStock);
            unset($bpAllocatedStock);
            unset($bpTemporaryStock);
        }
        unset($productChildArray);
        unset($productRecord);
        return $return;
    }

    /**
     * Get Product record
     * @param mixed $uProductId Product Id or Product Inventory Id or instance of Products model
     * @param boolean $doCache defines should Product stock quantities be calculated and cached. Cleaning up invalid DB records
     * @return mixed instance of Products model or null
     */
    public static function getRecord($uProductId = 0, $doCache = false, $ignoreFreeze = false)
    {
        if (($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezed() && !$ignoreFreeze) {
            if (!$ext::isFreezeProductRecord($uProductId)) {
                if ($uProductId instanceof \common\models\Products) {
                    $uProductId = $uProductId->products_id;
                }
                $uProductId = \common\extensions\ReportFreezeStock\models\FreezeProducts::find()->andWhere(['products_id' => (int)$uProductId])
                    ->cache((!$doCache && defined('ALLOW_ANY_QUERY_CACHE') && ALLOW_ANY_QUERY_CACHE=='True')?self::PRODUCT_RECORD_CACHE : -1)
                    ->one();
            }
            if ((int)$doCache > 0 AND $ext::isFreezeProductRecord($uProductId)) {
                if (self::doCache($uProductId) != true) {
                    $uProductId = null;
                }
            }
        } else {
            if (!($uProductId instanceof \common\models\Products)) {
                $uProductId = \common\models\Products::find()->andWhere(['products_id' => (int)$uProductId])
                    ->cache((!$doCache && defined('ALLOW_ANY_QUERY_CACHE') && ALLOW_ANY_QUERY_CACHE=='True')?self::PRODUCT_RECORD_CACHE : -1)
                    ->one();
            }
            if ((int)$doCache > 0 AND ($uProductId instanceof \common\models\Products)) {
                if (self::doCache($uProductId) != true) {
                    $uProductId = null;
                }
            }
        }
        unset($doCache);
        return $uProductId;
    }

    public static function getVirtualItemQuantityValue($uProductId = 0)
    {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderQuantityStep', 'allowed')) {
            return $ext::getVirtualItemQuantityValue($uProductId);
        }
        return 1;
    }

    public static function getVirtualItemQuantity($uProductId = 0, $realQuantity = 0)
    {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderQuantityStep', 'allowed')) {
            $realQuantity = $ext::getVirtualItemQuantity($uProductId, $realQuantity);
        }
        return $realQuantity;
    }

    public static function getVirtualItemStep($uProductId = 0, $checkArray = false)
    {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('OrderQuantityStep', 'allowed')) {
            return $ext::getVirtualItemStep($uProductId, $checkArray);
        }
        return array(1);
    }

    /**
     * check if product has any asset
     * @param int $products_id
     * @return boolean
     */
    public static function hasAssets($products_id){
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')){
            return $ext::hasAssets($products_id);
        }
        return false;
    }
    /**
     *
     * @param string $uprid
     * @param array $params
     * @return product asset or null
     */
    public static function getAssets($uprid, array $params = []){
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')){
            return $ext::getAssets($uprid, $params);
        }
        return null;
    }

    public static function getAsset($asset_id){
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')){
            return $ext::getAsset($asset_id);
        }
        return null;
    }

    public static function getSettings($products_id){
        $settings = \common\models\ProductsSettings::find()->where(['products_id' => intval($products_id)])->one();
        if (!$settings) {
            $settings = new \common\models\ProductsSettings;
            $settings->loadDefaultValues();
        }
        return $settings;
    }
/**
 * Url of parent category if product exists or false
 * @param integer $products_id
 * @return string|false
 */
    public static function get302redirect($products_id) {
      // parent category if product exists
      $new_url = false;
      $product = \common\models\Products::findOne($products_id);
      if ($product->products_id) {
        $leaf = false;
        $check = $product->getCategories()->active()->limit(1)->one();
        if (!$check) {
          $check = $product->getCategories()->limit(1)->one();
        } elseif ($check->categories_status) { // useless if? active() above
          $leaf = $check->categories_id;
        }
        if ($check) {
          $check = $check->getVisibleParents()->asArray()->all();
        }
        //$check = $check ->category[0]->getVisibleParents()->asArray()->one();

        if (!empty($check)) {
          $path = \yii\helpers\ArrayHelper::map($check, 'categories_id', 'categories_id');
          if ($leaf) {
            $path[$leaf] = $leaf;
          }
          $new_url = Yii::$app->urlManager->createUrl(['catalog', 'cPath' => implode('_', $path)]);
        } elseif ($leaf) {
          $new_url = Yii::$app->urlManager->createUrl(['catalog', 'cPath' => $leaf]);
        } else {
          $new_url = Yii::$app->urlManager->createUrl('index');
        }
      }
      return $new_url;
    }

/**
 * check whether the product is visible and redirects to to parent category if product is not visible
 * @param integer $productsId
 */
    public static function redirectIfInactive($productsId) {
      $new_url = false;
      $check_status = 1;
      if (\frontend\design\Info::isAdmin()){
        $check_status = 0;
      }
      if (!self::check_product($productsId, $check_status, true)) {
        $new_url = self::get302redirect($productsId);
      }
      if ($new_url && !empty($new_url)) {
          header('HTTP/1.1 302 Found');
          header("Location: " . $new_url);
          exit();
      }

    }

/**
 *
 * @param string $string to cleanup
 * @param int $checkLength <1 | 1 | >1  do not apply | by conf | by DB full-text settings
 * @return type
 */
    public static function cleanupSearch($string, $checkLength = 2) {
      $string = str_replace('<', ' <', $string);
      $string = preg_replace('/\s+/', ' ', strip_tags($string));
      if ($checkLength ) {
        $minLen = 0;
        if (defined('MSEARCH_WORD_LENGTH') && (int)MSEARCH_WORD_LENGTH>1) {
          $minLen = (int)MSEARCH_WORD_LENGTH;
        }
        if ($checkLength == 2 && defined('MSEARCH_ENABLE') && MSEARCH_ENABLE == 'fulltext') {
          /* @var $ext \common\extensions\PlainProductsDescription\PlainProductsDescription */
          if ($ext = \common\helpers\Acl::checkExtensionAllowed('PlainProductsDescription', 'allowed')) {
            $minLen = max($minLen, $ext::getMinTokenLength());
          }
        }
        if ($minLen > 1) {
          $words = explode(' ', $string);
          //$words = array_map(function ($word) { return preg_replace(['/^\W+/', '/\W+$/'], '', $word); },  $words);
          $words = array_map(function ($word) { return trim($word, '., -_!?:\'"'); },  $words);
          $words = array_filter($words, function ($__word) use($minLen) {
                              return strlen($__word) >= $minLen;
                          });
          $string = implode(' ' , $words);
        }
      }
      return $string;
    }

/**
 * mmm gets ... random first categories id of product
 * @staticvar array $_cache
 * @param int $productId
 * @return int
 */
    public static function getCategories ($productId) {
      static $_cache = [];
      $productId = intval($productId);
      $ret = [];
      if ($productId>0) {
        if (!isset($_cache[$productId])) {
          $model = \common\models\Products::find()->andWhere(['products_id' => $productId])->with('listingCategories')->asArray()->one();

          if ($model) {
            $ret = $_cache[$productId] = $model['listingCategories'][0];
          }
        } else {
          $ret = $_cache[$productId];
        }
      }
      return $ret;
    }

/**
 * gets list of properties and values of the product
 * @staticvar array $_cache
 * @param int $productId
 * @return array
 */
    public static function getPropertiesShort ($productId) {
      static $_cache = [];
      $productId = intval($productId);
      $ret = [];
      if ($productId>0) {
        if (!isset($_cache[$productId])) {
          $model = \common\models\Products::find()->andWhere(['products_id' => $productId])->with('properties')->asArray()->one();
          if ($model) {
            $ret = $_cache[$productId] = $model['properties'];
          }
        } else {
          $ret = $_cache[$productId];
        }
      }
      return $ret;
    }

    public static function removeOrderSubProducts(array $products)
    {
        return array_filter($products, static function (array $item) {
            if (empty($item['parent_product'])) {
                return true;
            }
            return false;
        });
    }

    public static function reduceOrderProducts(array $products)
    {
        $parentProducts = [];
        $products = array_filter($products, static function (array $item) use (&$parentProducts) {
            if (empty($item['parent_product'])) {
                $id = $item['template_uprid'] ?: $item['id'];
                $parentProducts[$id] = $item;
                return false;
            }
            return true;
        });
        array_walk($products, static function($item) use (&$parentProducts) {
            if (!empty($item['parent_product']) && isset($parentProducts[$item['parent_product']])) {
                $id = $item['template_uprid'] ?: $item['id'];
                $parentProducts[$item['parent_product']]['subProducts'][$id] = $item;
            }
        });
        return $parentProducts;
    }


    public static function getCategoriesIdListWithParents($productId)
    {
      $ret = [];
      if ( (int)$productId>0 ) {
        $ret = \common\models\Products2Categories::find()->alias('p2c')
            ->andWhere(['products_id' => $productId])
            ->innerJoin(TABLE_CATEGORIES . ' c1', 'c1.categories_id=p2c.categories_id')
            ->innerJoin(TABLE_CATEGORIES . ' c2', 'c1.categories_left >= c2.categories_left and c1.categories_right <= c2.categories_right')
            ->select('c2.categories_id')
            ->asArray()->distinct()->column();
        if (!$ret ) {
          $ret = [];
        }
      }

      return $ret;
    }

/**
 *
 * @param array|int $productsIds
 * @param array $details
 * @param int|false $limit
 * @return string
 */
    public static function getAdminDetailsList($productsIds, $details = ['name', 'price', 'status', 'model'], $limit = false) {
      if (!is_array($productsIds)) {

        if (is_numeric($productsIds)) {
          $productsIds = [$productsIds];
        } else {
          $productsIds = array_map('intval',preg_split('/,/',$productsIds,-1,PREG_SPLIT_NO_EMPTY));
        }

      }
      $pQ = \common\models\Products::find()->alias('p')
          ->joinWith('backendDescription')
          ->addSelect('p.products_id, p.products_model, p.products_price, p.products_status')
          ->andWhere(['p.products_id' => array_map('intval', $productsIds)])
          ;

      if (false && \backend\models\ProductNameDecorator::instance()->useInternalNameForListing()) {
        //$pQ->addSelect(['products_name' => new \yii\db\Expression("IF(LENGTH(products_internal_name), products_internal_name, products_name)")]);
        $orderBy = new \yii\db\Expression("IF(LENGTH(products_internal_name), products_internal_name, products_name)");
      } else {
        $pQ->addSelect('products_name');
        $orderBy = 'products_name';
      }

      $pQ ->orderBy($orderBy);
      if ($limit && (int)$limit>0) {
        $pQ ->limit((int)$limit);
      }
      $data = $pQ->asArray()->all();
      $ret = '';
      if (!empty($data)) {
        /** @var \common\classes\Currencies $currencies */
        $currencies = Yii::$container->get('currencies');
        foreach ($data as $d) {
          $ret .= '<div class="row col-md-12 prod-row ' . (!$d['products_status']?'dis_module':'') . '">';
          if (in_array('name', $details)) {
            $ret .= '<span class="col-md-8 prod-name">' . $d['products_name'] . '</span>';
          }
          if (in_array('model', $details)) {
            $ret .= '<span class="col-md-2 prod-model">' . $d['products_model'] . '</span>';
          }
          if (in_array('price', $details)) {
            $ret .= '<span class="col-md-2 prod-price">' . $currencies->format($d['products_price']) . '</span>';
          }
          $ret .= '</div>';
        }
        //$ret = '<div class="row col-md-12 container">' . $ret . '</div>';
      }

      return $ret;

    }
/**
 * from product price widget - to check
 * 2do fill in all details to 'clear' array
 * @param array $product product details from storage
 * @param int $qty def 1
 * @param int|false $customer_groups_id from storage if false
 * @return array
 */
    public static function getPiceDetails($product, $qty=1, $customer_groups_id = false) {
      if (!$customer_groups_id) {
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
      }
      $ret = $clear = [];
      $special_ex = $old_ex = $current_ex = '';
      /** @var \common\classes\Currencies $currencies */
      $currencies = \Yii::$container->get('currencies');
      $special_clear = $special_ex_clear = $special_one = $old_one = $special_ex_one = $old_ex_one = $current_ex_one = 0;
      $special_promo_str = $special_promo_value = $special_promo_ex_value = $special_promo_ex_str = $special_promo_one_value = $special_promo_one_str = $special_promo_ex_one_value = $special_promo_ex_one_str = 0;
      if ($product['is_bundle']) {
            $details = \common\helpers\Bundles::getDetails(['products_id' => $product['products_id']]);
            if ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear']) {
                $special = $details['actual_bundle_price'];
                if (!empty($details['actual_bundle_price_ex'])) {
                  $special_ex = $details['actual_bundle_price_ex'];
                }
                $old = $details['full_bundle_price'];
                if (!empty($details['full_bundle_price_ex'])) {
                  $old_ex = $details['full_bundle_price_ex'];
                }
                $current = '';
            } else {
                $special_value = 0;
                $special = '';
                $old = '';
                $current = $details['actual_bundle_price'];
                if (!empty($details['actual_bundle_price_ex'])) {
                  $current_ex = $details['actual_bundle_price_ex'];
                }
            }

            $special_clear = ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear']?$details['actual_bundle_price_clear']:false);
            $old_clear = ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear']?$details['full_bundle_price_clear']:false);
            $special_ex_clear = ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear']?$details['actual_bundle_price_clear_ex']:false);
            $old_ex_clear = ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear']?$details['full_bundle_price_clear_ex']:false);

            $clear = [
              'special' => $special_clear,
              'old' => $old_clear,
              'current' => $details['actual_bundle_price_clear'],
              'special_ex' => $special_ex_clear,
              'old_ex' => $old_ex_clear,
              'current_ex' => $details['actual_bundle_price_clear_ex'],
              'discount' => ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear']?
                $details['full_bundle_price_clear'] - $details['actual_bundle_price_clear']:false),
              'percent' => ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear'] && $details['full_bundle_price_clear']?
                round(($details['full_bundle_price_clear'] - $details['actual_bundle_price_clear'])/$details['full_bundle_price_clear']*100) . '%':false),
              'special_total_qty' => $product['special_total_qty']??0,
              'special_max_per_order' => $product['special_max_per_order']??0,
            ];
            $jsonPrice = $details['actual_bundle_price_clear'];
        } else {
            $priceInstance = \common\models\Product\Price::getInstance($product['products_id']);

            $product['products_price'] = $priceInstance->getInventoryPrice(['qty' => $qty]);
            $product['special_price'] = $priceInstance->getInventorySpecialPrice(['qty' => $qty]);
            // for 1 and q-ty could be different prices. so 1 first
            if (isset($product['special_price']) && $product['special_price'] !== false) {
                $special_one_clear = $currencies->display_price_clear($product['special_price'], $product['tax_rate'], 1);
                $special_one = $currencies->format($special_one_clear, false, '', '', true, true);

                $old_one_clear = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], 1);
                $old_one = $currencies->format($old_one_clear, false);
                if (/*$product['tax_rate']>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') { //&& (!\Yii::$app->storage->has('taxable') || (\Yii::$app->storage->has('taxable') && \Yii::$app->storage->get('taxable')))  - switcher from box and account ...
                  $special_ex_one_clear = $currencies->display_price_clear($product['special_price'], 0, 1);
                  $special_ex_one = $currencies->format($special_ex_one_clear, false);

                  $old_ex_one_clear = $currencies->display_price_clear($product['products_price'], 0, 1);
                  $old_ex_one = $currencies->format($old_ex_one_clear, false);
                }
            } else {
                $current_one = $currencies->display_price($product['products_price'], $product['tax_rate'], 1, true, true);
                if (/*$product['tax_rate']>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                  $current_ex_one = $currencies->display_price($product['products_price'], 0, 1, false, false);
                }

                if (\common\helpers\Extensions::isCustomerGroupsAllowed() && \common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && !isset($product['products_price_main'])) {
                  $_p = \common\models\Products::find()->select(['products_price_main' => 'products_price', 'products_id'])
                        ->where('products_id=:products_id', [':products_id' => (int) $product['products_id']])->asArray()->one();
                  if (!empty($_p)) {
                    \Yii::$container->get('products')->loadProducts($_p);
                    $product['products_price_main'] = $_p['products_price_main'];
                  }
                }

                if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && $product['products_price_main'] > $product['products_price']) {
                    $special_one = $current_one;
                    $old_one_clear = $currencies->display_price_clear($product['products_price_main'], $product['tax_rate'], 1);
                    $old_one = $currencies->format($old_one_clear, false);
                    $special_one_clear = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], 1);

                    if (/*$product['tax_rate']>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                        $old_ex_one_clear = $currencies->display_price_clear($product['products_price_main'], 0, 1, false, false);
                        $old_ex_one = $currencies->format($old_ex_one_clear, false);
                        $special_ex_one_clear = $currencies->display_price_clear($product['products_price'], 0, 1, false, false);
                    }
                    $current = '';
                }
            }
/*
            if ($qty != 1) {
              $product['products_price'] = $priceInstance->getInventoryPrice(['qty' => $qty]);
              $product['special_price'] = $priceInstance->getInventorySpecialPrice(['qty' => $qty]);
            }
*/

            if (isset($product['special_price']) && $product['special_price'] !== false) {
                $special_value = $product['special_price'];
                $special_clear = $currencies->display_price_clear($product['special_price'], $product['tax_rate'], $qty);
                $special = $currencies->format($special_clear, false, '', '', true, true);

                $old_clear = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], $qty);
                $old = $currencies->format($old_clear, false);

                if (/*$product['tax_rate']>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') { //&& (!\Yii::$app->storage->has('taxable') || (\Yii::$app->storage->has('taxable') && \Yii::$app->storage->get('taxable')))  - switcher from box and account ...
                  $special_ex_clear = $currencies->display_price_clear($product['special_price'], 0, $qty);
                  $special_ex = $currencies->format($special_ex_clear, false);

                  $old_ex_clear = $currencies->display_price_clear($product['products_price'], 0, $qty);
                  $old_ex = $currencies->format($old_ex_clear, false);

                }
                $current = $current_ex = '';
                $jsonPrice = $currencies->display_price_clear($product['special_price'], $product['tax_rate'], 1);
            } else {
                $special_value = 0;
                $special = '';
                $old = '';
                $current = $currencies->display_price($product['products_price'], $product['tax_rate'], $qty, true, true);
                $jsonPrice = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], 1);
                if (/*$product['tax_rate']>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                  $current_ex = $currencies->display_price($product['products_price'], 0, $qty, false, false);
                }

                if (\common\helpers\Extensions::isCustomerGroupsAllowed() && \common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && !isset($product['products_price_main'])) {
                  $_p = \common\models\Products::find()->select(['products_price_main' => 'products_price', 'products_id'])
                        ->where('products_id=:products_id', [':products_id' => (int) $product['products_id']])->asArray()->one();
                  if (!empty($_p)) {
                    \Yii::$container->get('products')->loadProducts($_p);
                    $product['products_price_main'] = $_p['products_price_main'];
                  }
                }

                if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && $product['products_price_main'] > $product['products_price']) {
                    $special_value = $product['products_price'];
                    $special = $current;
                    $old_clear = $currencies->display_price_clear($product['products_price_main'], $product['tax_rate'], $qty);
                    $old = $currencies->format($old_clear, false);

                    $special_clear = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], $qty);
                    $special_one_clear = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], 1);

                    if (/*$product['tax_rate']>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                        $special_ex = $current_ex;
                        $old_ex_clear = $currencies->display_price($product['products_price_main'], 0, $qty);
                        $old_ex = $currencies->format($old_ex_clear, false);
                        $current_ex = '';
                        $special_ex_clear = $currencies->display_price_clear($product['products_price'], 0, $qty);
                    }
                    $current = '';
                }
            }
            $clear = [
              'special' => ($special_clear ? $special_clear : false),
              //'old' => ((isset($product['special_price']) && $product['special_price'] !== false)?$old_clear:false),
              'old' => (!empty($old_clear)?$old_clear:false),
              'current' => $currencies->display_price_clear(
                  (isset($product['special_price']) && $product['special_price'] !== false)?$product['special_price']:$product['products_price'],
                  $product['tax_rate']),
              'special_ex' => ($special_ex_clear?$special_ex_clear:false),
              'old_ex' => ((isset($product['special_price']) && $product['special_price'] !== false)?$product['products_price']:false),
              'current_ex' => $special_ex_clear?$special_ex_clear:$product['products_price'],
              'special_total_qty' => $product['special_total_qty']??0,
              'special_max_per_order' => $product['special_max_per_order']??0,
            ];
            $clear['discount'] = ((isset($product['special_price']) && $product['special_price'] !== false)? $clear['old'] - $clear['special']:false);
            if (abs($clear['discount'])<0.01) {
              $clear['discount'] = false;
            } else {
              $clear['percent'] = ((isset($product['special_price']) && $product['special_price'] !== false && $clear['old']>0)?
                round(($clear['old'] - $clear['special'])/$clear['old']*100):false);
              if (abs($clear['percent'])<1) {
                $clear['percent'] = false;
              } else {
                $clear['percent'] .= '%';
              }
            }
        }

        if (!empty($product['special_promote_type']) && !empty($clear['discount'] && isset($product['special_expiration_date']) && $product['special_expiration_date'] != '') ) {
          if ($product['special_promote_type']==1) { //percent
            if ($old_clear>0) {
              $special_promo_value = round(($old_clear - $special_clear)/$old_clear*100);
            } else {
             $special_promo_value = 100;
            }
            $special_promo_str = $special_promo_value  . '%';

            if ($old_ex_clear>0) {
              $special_promo_ex_value = round(($old_ex_clear - $special_ex_clear)/$old_ex_clear*100);
            } else {
             $special_promo_ex_value = 100;
            }
            $special_promo_ex_str = $special_promo_ex_value  . '%';

            if (isset($old_one_clear)) {
              if ($old_one_clear>0) {
                $special_promo_one_value = round(($old_one_clear - $special_one_clear)/$old_one_clear*100);
              } else {
               $special_promo_one_value = 100;
              }
              $special_promo_one_str = $special_promo_one_value  . '%';
            }

            if (isset($old_ex_one_clear)) {
              if ($old_ex_one_clear>0) {
                $special_promo_ex_one_value = round(($old_ex_one_clear - $special_ex_one_clear)/$old_ex_one_clear*100);
              } else {
               $special_promo_ex_one_value = 100;
              }
              $special_promo_ex_one_str = $special_promo_ex_one_value  . '%';
            }


          } elseif ($product['special_promote_type']==2) { //fixed
            $special_promo_value = $currencies->format_clear($old_clear - $special_clear, false);
            $special_promo_str = $currencies->format($old_clear - $special_clear, false);

            $special_promo_ex_value = $currencies->format_clear($old_ex_clear - $special_ex_clear, false);
            $special_promo_ex_str = $currencies->format($old_ex_clear - $special_ex_clear, false);

            if (isset($special_one_clear)) {
              $special_promo_one_value = $currencies->format_clear($old_one_clear - $special_one_clear, false);
              $special_promo_one_str = $currencies->format($old_one_clear - $special_one_clear, false);
            }
            if (isset($special_ex_one_clear)) {
              $special_promo_ex_one_value = $currencies->format_clear($old_ex_one_clear - $special_ex_one_clear, false);
              $special_promo_ex_one_str = $currencies->format($old_ex_one_clear - $special_ex_one_clear, false);
            }

          }
        }

        $taxable = (DISPLAY_PRICE_WITH_TAX == 'true') && ($product['tax_rate']>0 );
        /*if (\Yii::$app->storage->has('taxable')){
          $taxable = $taxable && \Yii::$app->storage->get('taxable');
        }*/
        $taxable = $taxable && \common\helpers\Tax::displayTaxable();
        $ret = [
          'formatted' => [
              'special' => $special ?? null,
              'old' => $old ?? null,
              'current' => $current ?? null,
              'special_ex' => $special_ex ?? null,
              'old_ex' => $old_ex ?? null,
              'current_ex' => $current_ex ?? null,

              'special_one' => $special_one ?? null,
              'old_one' => $old_one ?? null,
              'current_one' => $current_one ?? null,
              'special_ex_one' => $special_ex_one ?? null,
              'old_ex_one' => $old_ex_one ?? null,
              'current_ex_one' => $current_ex_one ?? null,
              'tax_rate' => $taxable ?? null,

              'special_promo_str' => $special_promo_str ?? null,
              'special_promo_value' => $special_promo_value ?? null,
              'special_promo_ex_value' => $special_promo_ex_value ?? null,
              'special_promo_ex_str' => $special_promo_ex_str ?? null,
              'special_promo_one_value' => $special_promo_one_value ?? null,
              'special_promo_one_str' => $special_promo_one_str ?? null,
              'special_promo_ex_one_value' => $special_promo_ex_one_value ?? null,
              'special_promo_ex_one_str' => $special_promo_ex_one_str ?? null,
              'special_promote_type' => (isset($product['special_promote_type']) ? $product['special_promote_type'] : ''),
              'special_total_qty' => (isset($product['special_total_qty']) ? $product['special_total_qty'] : 0) ?? 0,
              'special_max_per_order' => (isset($product['special_max_per_order']) ? $product['special_max_per_order'] : 0) ?? 0,
          ],
          'clear' => $clear ?? null,
          'jsonPrice' => $jsonPrice ?? null,
          'special_value' => $special_value ?? null
        ];

        return $ret;

    }

    /**
     * could be added to cart...... inc. pre-order etc
     * @param string $uProductId
     * @param int $platformId
     * @param int $warehouseId
     * @param int $supplierId
     * @return bool
     */
    public static function isAvailableForSale($uProductId, $platformId = false, $warehouseId = false, $supplierId = false) {
        $ProductId = \common\helpers\Inventory::get_prid($uProductId);
        $cart_button = isset(\common\models\Products::findOne($ProductId)->cart_button) ? \common\models\Products::findOne($ProductId)->cart_button : 1;
        if ($cart_button) {
            //$product_qty = self::get_products_stock($uProductId);
            $product_qty = self::getAvailable($uProductId, ($platformId > 0 ? $platformId : false), ($warehouseId > 0 ? $warehouseId : false), ($supplierId > 0 ? $supplierId : false));
            $stock_info = \common\classes\StockIndication::product_info(array(
                    'products_id' => $uProductId,
                    'products_quantity' => $product_qty,
            ));
            return $stock_info['flags']['add_to_cart'];
        }
    }

    /**
     * could be bought and delivered
     * @param string $uProductId
     * @param int $platformId
     * @param int $warehouseId
     * @param int $supplierId
     * @return bool
     */
    public static function isAvailableForSaleNow($uProductId, $platformId = false, $warehouseId = false, $supplierId = false) {
        $ProductId = \common\helpers\Inventory::get_prid($uProductId);
        $cart_button = isset(\common\models\Products::findOne($ProductId)->cart_button) ? \common\models\Products::findOne($ProductId)->cart_button : 1;
        if ($cart_button) {
            $product_qty = self::get_products_stock($uProductId);
            //$product_qty = self::getAvailable($uProductId, ($platformId > 0 ? $platformId : false), ($warehouseId > 0 ? $warehouseId : false), ($supplierId > 0 ? $supplierId : false));
            $stock_info = \common\classes\StockIndication::product_info(array(
                    'products_id' => $uProductId,
                    'products_quantity' => $product_qty,
            ));
            return ($stock_info['flags']['add_to_cart'] && ($product_qty>0) && empty($stock_info['flags']['notify_instock']));
        }
    }

    /**
     * Return product unit label array if no values are passed or string if search mode is activated.
     * If $isSearch is passed - check is label key $productUnitLabelKey is existing. Return is depending on $productUnitLabelReturnValue.
     * If $productUnitLabelReturnValue = true - return label translated value, if = false - return label key.
     * If key doesn't exists - return empty string.
     * @param boolean $isSearch - is search mode is activated
     * @param string $productUnitLabelKey
     * @param boolean $productUnitLabelReturnValue
     * @return mixed product unit label array or product unit label value or key
     */
    public static function getUnitLabelList($isSearch = false, $productUnitLabelKey = '', $productUnitLabelReturnValue = true)
    {
        $return = [];
        $isSearch = (((int)$isSearch > 0) ? true : false);
        $productUnitLabelKey = trim($productUnitLabelKey);
        $productUnitLabelReturnValue = (((int)$productUnitLabelReturnValue > 0) ? true : false);
        \common\helpers\Translation::init('product_unit_label');
        foreach (\common\models\Translation::find()
            ->where(['translation_entity' => 'product_unit_label'])
            ->groupBy(['translation_key'])
            ->asArray(true)->all() as $labelRecord
        ) {
            $return[$labelRecord['translation_key']] = (defined($labelRecord['translation_key'])
                ? constant($labelRecord['translation_key']) : $labelRecord['translation_key']
            );
        }
        if ($isSearch == true) {
            return ((($productUnitLabelKey != '') AND isset($return[$productUnitLabelKey]))
                ? (($productUnitLabelReturnValue == true) ? $return[$productUnitLabelKey] : $productUnitLabelKey)
                : ''
            );
        }
        asort($return, SORT_STRING);
        return $return;
    }

    public static function getProductIdByModel(string $model)
    {
        $productId = null;
        if (\common\helpers\Extensions::isInventoryAllowed()) {
            $productId = \common\models\Inventory::findOne(['products_model' => $model])->products_id ?? null;
        }
        if (is_null($productId)) {
            $productId = \common\models\Products::findOne(['products_model' => $model])->products_id ?? null;
        }
        return $productId;
    }

    public static function getProductTypes($productArray)
    {
        $res = [];
        if (($productArray['is_bundle']??null) && \common\helpers\Extensions::isAllowed('ProductBundles')) {
            $res = ['bundle'];
        } elseif (($productArray['products_pctemplates_id']??null) && \common\helpers\Extensions::isAllowed('ProductConfigurator')) {
            $res = ['configurator'];
        } elseif ($productArray['attr_exists']??null) {
            $res = ['attributes'];
            if (!($productArray['without_inventory']??null) && \common\helpers\Extensions::isAllowed('Inventory')) {
                $res[] = 'inventory';
            }
        }
        return $res;
    }
}
