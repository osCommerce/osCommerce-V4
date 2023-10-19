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
use common\components\CategoriesCache;
use common\helpers\Product;
use common\classes\platform;
use common\helpers\Affiliate;

class Categories {

    use SqlTrait;
    public static function get_categories_name($who_am_i, $language_id=0) {
        global $languages_id;

        $_language_id = (int)$language_id>0?$language_id:$languages_id;

        static $cached = [];
        $cache_key = (int)$who_am_i.'@'.(int)$_language_id;

        if ( !isset($cached[$cache_key]) ) {
            $the_categories_name = tep_db_fetch_array(tep_db_query("select if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd.categories_id = cd1.categories_id and cd1.affiliate_id = '" . Affiliate::id() . "' and cd1.language_id = '" . (int)$_language_id . "' and cd1.categories_id = '" . (int)$who_am_i . "' where cd.categories_id = '" . (int)$who_am_i . "' and cd.language_id = '" . (int)$_language_id . "' and cd.affiliate_id = '0'"));
            $cached[$cache_key] = $the_categories_name['categories_name']??null;
        }

        return $cached[$cache_key];
    }

    public static function getSeoPageName($categoryId, $languageId)
    {
        return CategoriesCache::getInstance()->getSeoName($categoryId, $languageId);
        /*
        static $cached = [];
        $cache_key = (int)$categoryId.'@'.(int)$languageId;
        if ( count($cached)>100 ) $cached = [];

        if ( !isset($cached[$cache_key]) ) {
            $category = tep_db_fetch_array(tep_db_query(
                "select if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name) as categories_seo_page_name " .
                "from " . TABLE_CATEGORIES . " c " .
                "  left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languageId . "' " .
                "where c.categories_id = '" . (int)$categoryId . "' " .
                "order by length(if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name)) desc " .
                "limit 1"
            ));
            $cached[$cache_key] = is_array($category)?$category['categories_seo_page_name']:false;
        }

        return $cached[$cache_key];
        */
    }

    public static function get_categories($categories_array = '', $parent_id = '0', $indent = '') {
        global $languages_id;

        if (!is_array($categories_array))
            $categories_array = array();
        $categories_query = tep_db_query("select c.categories_id, if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on c.categories_id = cd1.categories_id and cd1.affiliate_id = '" . Affiliate::id() . "' and cd1.language_id = '" . (int) $languages_id . "' where c.parent_id = '" . (int) $parent_id . "' and c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int) $languages_id . "' AND c.categories_status = 1 order by c.sort_order, cd.categories_name");
        while ($categories = tep_db_fetch_array($categories_query)) {
            $categories_array[] = array('id' => $categories['categories_id'],
                'text' => $indent . $categories['categories_name']);

            if ($categories['categories_id'] != $parent_id) {
                $categories_array = self::get_categories($categories_array, $categories['categories_id'], $indent . '&nbsp;&nbsp;');
            }
        }
        return $categories_array;
    }

    public static function get_path($current_category_id = '') {
        global $cPath_array;

        if (tep_not_null($current_category_id)) {
            if (empty($cPath_array)) {
                $cPath_new = $current_category_id;
            } else {
                $cp_size = count($cPath_array);
                $cPath_new = '';
                $last_cid = (int) $cPath_array[($cp_size - 1)];
                static $_cache=[];
                if (!isset($_cache[$last_cid])) {
                  $last_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . $last_cid . "'");
                  $last_category = $_cache[$last_cid] = tep_db_fetch_array($last_category_query);
                } else {
                  $last_category = $_cache[$last_cid];
                }

                $current_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $current_category_id . "'");
                $current_category = tep_db_fetch_array($current_category_query);

                if ($last_category['parent_id'] == $current_category['parent_id']) {
                    for ($i = 0; $i < ($cp_size - 1); $i++) {
                        $cPath_new .= '_' . $cPath_array[$i];
                    }
                } else {
                    for ($i = 0; $i < $cp_size; $i++) {
                        $cPath_new .= '_' . $cPath_array[$i];
                    }
                }
                $cPath_new .= '_' . $current_category_id;

                if (substr($cPath_new, 0, 1) == '_') {
                    $cPath_new = substr($cPath_new, 1);
                }
            }
        } else {
            $cPath_new = implode('_', $cPath_array);
        }

        return 'cPath=' . $cPath_new;
    }

    public static function count_products_in_category($category_id, $include_inactive = false, $platformId = 0) {
        //TODO: move to CategoriesCache
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $currency_id = \Yii::$app->settings->get('currency_id');
        static $cache = [];
        $cache_key = (int)$category_id.'^'.(int)$include_inactive.'^'.(int)$customer_groups_id.'^'.(int)$currency_id.'^'.(int)$platformId;

        if ( isset($cache[$cache_key]) ) return $cache[$cache_key];

        if (!$include_inactive) {
            $add_sql = Product::getState(true) . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ";
        }

        $categories_join = '';
        $products_join = '';
        if (platform::activeId() || $platformId) {
            $categories_join .= self::sqlCategoriesToPlatform($platformId);
            $products_join .= self::sqlProductsToPlatform($platformId);
        }

        if ($customer_groups_id == 0) {
            $products = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_PRODUCTS . " p {$products_join} " . " where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c1.categories_id = '" . (int) $category_id . "' " . " and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right and c.categories_status = 1) " . $add_sql));
        } else {
            $products = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_PRICES . " pgp on p.products_id = pgp.products_id and pgp.groups_id = '" . (int) $customer_groups_id . "' and pgp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $currency_id : '0') . "' " . " where p.products_id = p2c.products_id and if(pgp.products_group_price is null, 1, pgp.products_group_price != -1 ) and p2c.categories_id = c.categories_id and c1.categories_id = '" . (int) $category_id . "' " . " and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right and c.categories_status = 1) " . $add_sql));
        }
        $cache[$cache_key] = $products['total'];

        return $products['total'];
    }

    public static function notEmpty($category_id, $include_inactive = false) {
        return CategoriesCache::getInstance()->notEmpty($category_id, $include_inactive);
        /*
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $currency_id = \Yii::$app->settings->get('currency_id');
        static $cache = [];
        $cache_key = (int)$category_id.'^'.(int)$include_inactive.'^'.(int)$customer_groups_id.'^'.(int)$currency_id;

        if ( isset($cache[$cache_key]) ) return $cache[$cache_key];

        $q = new \common\components\ProductsQuery([
          'filters'=> ['categories' => [$category_id]],
          'anyExists' => 1,
          'currentCategory' => false,
          'orderBy' => ['fake' => 1],
          'limit' => 1,
          'active' => !$include_inactive,
        ]);

        $cnt = $q->buildQuery()->getQuery()->cache(600)->one();
        //echo "<PRE STYle='position:absolute; width:40%; top:0; left:60%; z-index:100'>" . __FILE__ .':' . __LINE__ . " #### \$cnt ". print_r($q,1) .print_r($q->getQuery()->createCommand()->rawSql, 1) ."</PRE>";
        $cache[$cache_key] = ($cnt?1:0);

        return $cache[$cache_key];
        */
    }

    public static function has_category_subcategories($category_id) {
        static $cache = [];
        $cache_key = (int)$category_id.'^'.intval(platform::activeId());

        if ( isset($cache[$cache_key]) ) return $cache[$cache_key];

        $categories_join = '';
        if (platform::activeId()) {
            $categories_join .=
                    " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on c.categories_id = plc.categories_id  and plc.platform_id = '" . platform::currentId() . "' ";
        }

        $child_category = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CATEGORIES . " c {$categories_join} where c.parent_id = '" . (int) $category_id . "' and c.categories_status = 1"));

        $cache[$cache_key] = ($child_category['count'] > 0);
        return ($child_category['count'] > 0);
    }

    public static function get_subcategories(&$subcategories_array, $parent_id = 0, $include_deactivated = true) {
        $subcategories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int) $parent_id . "'" . (!$include_deactivated ? " and categories_status = 1" : ''));
        while ($subcategories = tep_db_fetch_array($subcategories_query)) {
            $subcategories_array[$subcategories['categories_id']] = $subcategories['categories_id'];
            if ($subcategories['categories_id'] != $parent_id) {
                self::get_subcategories($subcategories_array, $subcategories['categories_id'], $include_deactivated);
            }
        }
    }

    protected static function get_parent_category_id($category_id, $active=true){
        static $cache = false;
        if ( !is_array($cache) ){
            $cache = [];
            $top_r = tep_db_query(
                "SELECT categories_id, categories_status ".
                "FROM ".TABLE_CATEGORIES." ".
                "WHERE parent_id=0"
            );
            while($top = tep_db_fetch_array($top_r)){
                $cache[(int)$top['categories_id']] = ['categories_status'=>(int)$top['categories_status'],'parent_id'=>0];
            }
        }
        if ( !isset($cache[(int)$category_id]) ){
            $parent_categories_query = tep_db_query("select parent_id, categories_status from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $category_id . "'");
            if ( tep_db_num_rows($parent_categories_query)>0 ) {
                $parent_category = tep_db_fetch_array($parent_categories_query);
                $cache[(int)$category_id] = ['categories_status'=>(int)$parent_category['categories_status'],'parent_id'=>(int)$parent_category['parent_id']];
            }
        }
        if ( isset($cache[(int)$category_id]) ){
            if ( $active ) {
                if ( $cache[(int)$category_id]['categories_status'] ) return $cache[(int)$category_id];
                return false;
            }
            return $cache[(int)$category_id];
        }
        return false;
    }

    public static function get_parent_categories(&$categories, $categories_id, $only_active=true) {
        if ($parent_categories = static::get_parent_category_id($categories_id, $only_active)) {
            if ($parent_categories['parent_id'] == 0)
                return true;
            $categories[sizeof($categories)] = $parent_categories['parent_id'];
            if ($parent_categories['parent_id'] != $categories_id) {
                self::get_parent_categories($categories, $parent_categories['parent_id'],$only_active);
            }
        }
    }

    public static function parse_category_path($cPath) {
        $string_to_int = function($string) {
            return (int)$string;
        };
        $cPath_array = array_map($string_to_int, explode('_', $cPath));
        $tmp_array = array();
        $n = sizeof($cPath_array);
        for ($i = 0; $i < $n; $i++) {
            if (!in_array($cPath_array[$i], $tmp_array)) {
                $tmp_array[] = $cPath_array[$i];
            }
        }
        return $tmp_array;
    }

    public static function get_category_filters($categories_id) {
        $filters_array = array();
        if ($categories_id > 0) {
            $filters_query = tep_db_query("select c.parent_id, f.filters_type, f.options_id, f.properties_id, f.status from " . TABLE_CATEGORIES . " c left join " . TABLE_FILTERS . " f on c.categories_id = f.categories_id and f.filters_of = 'category' where c.categories_id = '" . (int) $categories_id . "' order by f.sort_order");
            while ($filters = tep_db_fetch_array($filters_query)) {
                $parent_id = $filters['parent_id'];
                if ($filters['status']) {
                    if (\Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login') && $filters['filters_type'] == 'price') {
                        //skip
                    } else {
                        $filters_array[] = $filters;
                    }
                }
            }
            if (count($filters_array) == 0) {
                if ($parent_id > 0) {
                    return self::get_category_filters($parent_id);
                } else {
                    $filters_query = tep_db_query("select f.filters_type, f.options_id, f.properties_id, f.sort_order from " . TABLE_FILTERS . " f where f.categories_id = '0' and f.filters_of = 'category' and f.status = '1' group by f.filters_type, f.options_id, f.properties_id order by f.sort_order");
                    while ($filters = tep_db_fetch_array($filters_query)) {
                        if (\Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login') && $filters['filters_type'] == 'price') {
                            //skip
                        } else {
                            $filters_array[] = $filters;
                        }
                    }
                }
            }
        } else {
            $filters_query = tep_db_query("select f.filters_type, f.options_id, f.properties_id, min(f.sort_order) as sort_order from " . TABLE_FILTERS . " f where (f.categories_id = '0' or f.categories_id in (select c.categories_id from " . TABLE_CATEGORIES . " c where c.parent_id = '0')) and f.filters_of = 'category' and f.status = '1' group by f.filters_type, f.options_id, f.properties_id order by min(f.sort_order)");
            while ($filters = tep_db_fetch_array($filters_query)) {
                if (\Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login') && $filters['filters_type'] == 'price') {
                    //skip
                } else {
                    $filters_array[] = $filters;
                }
            }
        }
        return $filters_array;
    }

    public static function remove_category($category_id, $reset_cache = true) {
        $category_image_query = tep_db_query("select categories_image from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $category_id . "'");
        $category_image = tep_db_fetch_array($category_image_query);

        self::remove_category_image($category_image['categories_image']);
        self::remove_category_image_folder($category_id);

        \common\components\CategoriesCache::getCPC()::invalidateCategories((int) $category_id);
        tep_db_query("delete from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $category_id . "'");
        tep_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int) $category_id . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int) $category_id . "'");
        tep_db_query("delete from " . TABLE_PLATFORMS_CATEGORIES . " where categories_id = '" . (int) $category_id . "'");

        if ( (int)$category_id>0 ) {
            tep_db_query("delete from " . TABLE_FILTERS . " WHERE categories_id='" . (int)$category_id . "'");
        }

        foreach (\common\helpers\Hooks::getList('categories/after-delete') as $filename) {
            include($filename);
        }

        if ($reset_cache && USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
    }

    public static function trunk_categories() {

        tep_db_query("TRUNCATE " . TABLE_CATEGORIES);
        tep_db_query("TRUNCATE " . TABLE_CATEGORIES_DESCRIPTION);
        tep_db_query("TRUNCATE " . TABLE_PRODUCTS_TO_CATEGORIES);
        tep_db_query("TRUNCATE " . TABLE_PLATFORMS_CATEGORIES);
        tep_db_query("DELETE FROM " . TABLE_FILTERS." WHERE categories_id>0");

        foreach (\common\helpers\Hooks::getList('categories/after-trunk') as $filename) {
            include($filename);
        }

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
    }

    public static function remove_category_image_folder($category_id)
    {
        $category = DIR_FS_CATALOG_IMAGES . 'categories' . DIRECTORY_SEPARATOR . $category_id;

        \yii\helpers\FileHelper::removeDirectory($category);
    }

    public static function remove_category_image($filename) {
        $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where categories_image = '" . tep_db_input($filename) . "'");
        $duplicate_image = tep_db_fetch_array($duplicate_image_query);

        if ($duplicate_image['total'] < 2) {
            if (file_exists(DIR_FS_CATALOG_IMAGES . $filename)) {
                @unlink(DIR_FS_CATALOG_IMAGES . $filename);
            }
        }
    }

    public static function set_categories_status($category_id, $status, $force_products_status = null) {
        if ( !is_bool($force_products_status) ){
            $force_products_status = false;
            if ( !defined('FORCE_DISABLE_ENABLE_CATEGORY_PRODUCTS') || FORCE_DISABLE_ENABLE_CATEGORY_PRODUCTS=='Yes' ){
                $force_products_status = true;
            }
        }
        $chk_status = tep_db_fetch_array(tep_db_query("select categories_status from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $category_id . "'"));
        if (!isset($chk_status['categories_status']) || (int) $chk_status['categories_status'] == $status)
            return;

        if ($status == '1') {
            tep_db_query("update " . TABLE_CATEGORIES . " set previous_status = NULL, categories_status = '1', last_modified = now() where categories_id = '" . $category_id . "'");
            $query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = " . $category_id);
            while ($data = tep_db_fetch_array($query)) {
                if ($force_products_status) {
                    tep_db_query("update " . TABLE_PRODUCTS . " set products_status = IFNULL(previous_status, '1'), previous_status = NULL where products_id = " . $data['products_id']);
                }else{
                    tep_db_query("update " . TABLE_PRODUCTS . " set products_status = IFNULL(previous_status, '1'), previous_status = NULL where products_id = " . $data['products_id']." AND previous_status IS NOT NULL");
                }
            }
            $tree = self::get_category_tree($category_id);
            for ($i = 1; $i < sizeof($tree); $i++) {
                tep_db_query("update " . TABLE_CATEGORIES . " set  categories_status = IFNULL(previous_status, '1'), previous_status = NULL, last_modified = now() where categories_id = '" . $tree[$i]['id'] . "'");
                $query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = " . $tree[$i]['id']);
                while ($data = tep_db_fetch_array($query)) {
                    if ($force_products_status){
                        tep_db_query("update " . TABLE_PRODUCTS . " set  products_status = IFNULL(previous_status, '1'), previous_status = NULL where products_id = " . $data['products_id']);
                    }else{
                        tep_db_query("update " . TABLE_PRODUCTS . " set  products_status = IFNULL(previous_status, '1'), previous_status = NULL where products_id = " . $data['products_id']." AND previous_status IS NOT NULL");
                    }
                }
            }
        } elseif ($status == '0') {
            tep_db_query("update " . TABLE_CATEGORIES . " set previous_status = NULL, categories_status = '0', last_modified = now() where categories_id = '" . $category_id . "'");
            if ( $force_products_status ) {
                $query = tep_db_query("select products_id, 0 AS linked_to_active_categories from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = " . $category_id);
            }else {
                $query = tep_db_query(
                    "select p2c.products_id, count(p2c_linked_active.products_id) AS linked_to_active_categories " .
                    "from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c " .
                    " left join (".
                    "   select p2c_linked.products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c_linked ".
                    "    inner join " . TABLE_CATEGORIES . " c on c.categories_id=p2c_linked.categories_id and c.categories_status=1 ".
                    "    inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " limit_products on p2c_linked.products_id=limit_products.products_id and limit_products.categories_id='" . (int)$category_id . "' ".
                    "   where p2c_linked.categories_id!='" . (int)$category_id . "'".
                    ") p2c_linked_active on p2c_linked_active.products_id=p2c.products_id ".
                    "where p2c.categories_id='" . (int)$category_id . "' ".
                    "group by p2c.products_id"
                );
            }
            while ($data = tep_db_fetch_array($query)) {
                if ( $data['linked_to_active_categories']>0 ) continue;
                tep_db_query("update " . TABLE_PRODUCTS . " set previous_status = products_status, products_status = '0' where products_id = " . $data['products_id']);
            }
            $tree = self::get_category_tree($category_id);
            for ($i = 1; $i < sizeof($tree); $i++) {
                tep_db_query("update " . TABLE_CATEGORIES . " set previous_status = categories_status, categories_status = '0', last_modified = now() where categories_id = '" . $tree[$i]['id'] . "'");

                if ( $force_products_status ) {
                    $query = tep_db_query("select products_id, 0 AS linked_to_active_categories from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = " . $tree[$i]['id']);
                }else {
                    $query = tep_db_query(
                        "select p2c.products_id, count(p2c_linked_active.products_id) AS linked_to_active_categories " .
                        "from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c " .
                        " left join (".
                        "   select p2c_linked.products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c_linked ".
                        "    inner join " . TABLE_CATEGORIES . " c on c.categories_id=p2c_linked.categories_id and c.categories_status=1 ".
                        "    inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " limit_products on p2c_linked.products_id=limit_products.products_id and limit_products.categories_id='" . (int)$tree[$i]['id'] . "' ".
                        "   where p2c_linked.categories_id!='" . (int)$tree[$i]['id'] . "'".
                        ") p2c_linked_active on p2c_linked_active.products_id=p2c.products_id ".
                        "where p2c.categories_id='" . (int)$tree[$i]['id'] . "' ".
                        "group by p2c.products_id"
                    );
                }

                while ($data = tep_db_fetch_array($query)) {
                    if ( $data['linked_to_active_categories']>0 ) continue;
                    tep_db_query("update " . TABLE_PRODUCTS . " set previous_status = products_status, products_status = '0' where products_id = " . $data['products_id']);
                }
            }
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus', 'allowed')) {
            $ext::categoryAutoSwitchOff($category_id);
        }
        if (array_search($category_id, $tree) === false) {
            $tree[] = (int) $category_id;
        }
        \common\components\CategoriesCache::getCPC()::invalidateCategories($tree);
    }
/**
 *
 * @global type $languages_id
 * @param int $parent_id
 * @param string $spacing
 * @param int $exclude (exclude, incl children)
 * @param type $category_tree_array
 * @param bool $include_itself
 * @param type $with_full_path
 * @param type $platform_id
 * @param type $active
 * @param type $add_products
 * @param type $lang_id
 * @return array
 */
    public static function get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $with_full_path = false, $platform_id = 0, $active = false, $add_products = false, $lang_id = 0) {
        global $languages_id;

        $lang_id = $lang_id ? $lang_id : $languages_id;

        if (!is_array($category_tree_array))
            $category_tree_array = array();
        if ((sizeof($category_tree_array) < 1) && ($exclude != '0'))
            $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP, 'desc'=>'cat');

        if ($include_itself) {
            $category_query = tep_db_query("select cd.categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd where cd.language_id = '" . (int) $lang_id . "' and cd.categories_id = '" . (int) $parent_id . "' and affiliate_id = 0");
            $category = tep_db_fetch_array($category_query);
            if (is_array($category)) {
                $category_tree_array[] = array('id' => $parent_id, 'text' => $category['categories_name'], 'desc'=>'cat', 'parent_id'=>$parent_id);
            }
        }

        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id, c.categories_status from " . TABLE_CATEGORIES . " c, " .
                                         TABLE_CATEGORIES_DESCRIPTION . " cd " .
                                         ($platform_id? " left join " . TABLE_PLATFORMS_CATEGORIES . " pc on pc.categories_id=cd.categories_id and pc.platform_id='" . $platform_id . "' " : "") .
                                         " where c.categories_id = cd.categories_id and cd.language_id = '" . (int) $lang_id . "'"  .
                                         ($active? " and c.categories_status = 1 " : "") .
                                         " and c.parent_id = '" . (int) $parent_id . "' and affiliate_id = 0 order by c.sort_order, cd.categories_name");

        $children_spacing = $spacing;

        while ($categories = tep_db_fetch_array($categories_query)) {
            if (intval($exclude)==0 || $exclude != $categories['categories_id']) {
                $products = [];
                if ($add_products){
                    $products = self::products_in_category($categories['categories_id'], false, $platform_id, $children_spacing);
                }
                $category_tree_array[] = array('id' => $categories['categories_id'], 'text' => $spacing . $categories['categories_name'], 'desc'=>'cat', 'parent_id'=>$parent_id, 'products' => $products, 'status' => $categories['categories_status']);
              $children_spacing = $spacing . '&nbsp;&nbsp;&nbsp;';
              if ($with_full_path) {
                  $children_spacing = $spacing . $categories['categories_name'] . '&nbsp;&nbsp;&gt;&nbsp;&nbsp;';
              }
              $category_tree_array = self::get_category_tree($categories['categories_id'], $children_spacing, $exclude, $category_tree_array, false, $with_full_path, $platform_id, $active, $add_products);
            }
        }

        return $category_tree_array;
    }

    public static function products_in_category($categories_id, $include_deactivated = false, $platform_id = 0, $spacing = '') {
        global $languages_id;
        $products_array = [];

        $products_query = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p " .
                                       ($platform_id? " inner join " . TABLE_PLATFORMS_PRODUCTS . " pp on pp.products_id=p.products_id and pp.platform_id='" . $platform_id . "' " : "") .
                                       " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id ."' and pd.platform_id = '".($platform_id?$platform_id:intval(\common\classes\platform::defaultId()))."' " .
                                       ", " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id " .
                                       (!$include_deactivated? "and p.products_status = '1' " . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "":"" ) ." and p2c.categories_id = '" . (int) $categories_id . "' order by p.sort_order, pd.products_name");


        if (tep_db_num_rows($products_query)){
            while($products = tep_db_fetch_array($products_query)){
               $products_array[] =  array('id' => $products['products_id'], 'text' => $spacing . $products['products_name'], 'desc'=>'prod', 'parent_id'=>$categories_id);
            }
        }
        return $products_array;
    }

    public static function ids_products_in_category($categories_id, $include_deactivated = false, $platform_id = 0, $with_subcategories = false) {
        global $languages_id;
        $products_array = [];

        if ($with_subcategories){
            $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
            $currency_id = \Yii::$app->settings->get('currency_id');
            static $cache = [];
            $cache_key = (int)$categories_id.'^'.(int)$include_deactivated.'^'.(int)$customer_groups_id.'^'.(int)$currency_id;

            if ( isset($cache[$cache_key]) ) return $cache[$cache_key];

            if (!$include_deactivated) {
                $add_sql = Product::getState(true) . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ";
            }

            $categories_join = '';
            $products_join = '';
            if (platform::activeId()) {
                $categories_join .= self::sqlCategoriesToPlatform();
                $products_join .= self::sqlProductsToPlatform();
            }

            if ($customer_groups_id == 0) {
                $products_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_PRODUCTS . " p {$products_join} " . " where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c1.categories_id = '" . (int) $categories_id . "' " . " and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right and c.categories_status = 1) " . $add_sql);
            } else {
                $products_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$categories_join}, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_PRICES . " pgp on p.products_id = pgp.products_id and pgp.groups_id = '" . (int) $customer_groups_id . "' and pgp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $currency_id : '0') . "' " . " where p.products_id = p2c.products_id and if(pgp.products_group_price is null, 1, pgp.products_group_price != -1 ) and p2c.categories_id = c.categories_id and c1.categories_id = '" . (int) $categories_id . "' " . " and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right and c.categories_status = 1) " . $add_sql);
            }
            if (tep_db_num_rows($products_query)){
                while($product = tep_db_fetch_array($products_query)){
                    $cache[$cache_key][] = $product['products_id'];
                }
            }

            return $cache[$cache_key];
        } else {
            $products_array = self::products_in_category($categories_id, $include_deactivated, $platform_id);
            $products_array = \yii\helpers\ArrayHelper::getColumn($products_array, 'id');
            return $products_array;
        }
    }

    public static function get_full_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false, $platform_id = 0, $active = false, $level = 0) {
      global $languages_id;

      if (!is_array($category_tree_array)) $category_tree_array = array();

      if ($include_itself && $parent_id != 0) {
        $category_query = tep_db_query("select cd.categories_name, c.categories_status from " . TABLE_CATEGORIES_DESCRIPTION . " cd left join " . TABLE_CATEGORIES . " c on c.categories_id = cd.categories_id ".
                                      ($platform_id? " inner join " . TABLE_PLATFORMS_CATEGORIES . " pc on pc.categories_id=cd.categories_id and pc.platform_id='" . $platform_id . "' " : "") .
                                      " where cd.language_id = '" . (int)$languages_id . "' and cd.affiliate_id = 0 and cd.categories_id = '" . (int)$parent_id . "'" .
                                      ($active? " and c.categories_status = 1" : ""));
        $category = tep_db_fetch_array($category_query);/*print_r($category);*/
        $category_tree_array[] = array('id' => $parent_id, 'text' => $category['categories_name'], 'category' => '1', 'level' => $level, 'status' => $category['categories_status']);
      }

      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id, c.categories_status from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd " .
                                      ($platform_id? " inner join " . TABLE_PLATFORMS_CATEGORIES . " pc on pc.categories_id=cd.categories_id and pc.platform_id='" . $platform_id . "' " : "") .
                                       " where c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int)$languages_id . "' and c.parent_id = '" . (int)$parent_id . "' " .
                                       ($active? " and c.categories_status = 1" : "") .
                                       " order by c.sort_order, cd.categories_name");
      while ($categories = tep_db_fetch_array($categories_query)) {
        if ($exclude != $categories['categories_id']) $category_tree_array[] = array('id' => $categories['categories_id'], 'text' => $spacing . $categories['categories_name'], 'category' => '1', 'level' => $level, 'status' => $categories['categories_status']);
        $category_tree_array = self::get_full_category_tree($categories['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array, false, $platform_id, $active, $level+1);

        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_status from " . TABLE_PRODUCTS . " p " .
                                        ($platform_id? " inner join " . TABLE_PLATFORMS_PRODUCTS . " pp on pp.products_id=p.products_id and pp.platform_id='" . $platform_id . "' " : "") .
                                        ", " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and p.products_id = p2c.products_id and p2c.categories_id = '" .(int)$categories['categories_id'] . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.language_id = '" . (int)$languages_id . "' " .
                                        ($active? " and p.products_status = 1 " . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "" : "") .
                                        " order by p.sort_order, pd.products_name");
        while ($products = tep_db_fetch_array($products_query)){
          $category_tree_array[] = array('id' => $products['products_id'], 'text' => $spacing . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $products['products_name'], 'parent_id' => $categories['categories_id'], 'category' => '0', 'status' => $products['products_status']);
        }

      }

      if ($parent_id == 0){
        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_status from " . TABLE_PRODUCTS . " p "  .
                                      ($platform_id? " inner join " . TABLE_PLATFORMS_PRODUCTS . " pp on pp.products_id=p.products_id and pp.platform_id='" . $platform_id . "' " : "") .
                                       ", " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and p.products_id = p2c.products_id and p2c.categories_id = '" .(int)$parent_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.language_id = '" . (int)$languages_id . "' ".
                                       ($active? " and p.products_status = 1 " . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "" : "") .
                                       " order by p.sort_order, pd.products_name");
        while ($products = tep_db_fetch_array($products_query)){
          $category_tree_array[] = array('id' => $products['products_id'], 'text' => $spacing . '&nbsp;&nbsp;&nbsp;' . $products['products_name'], 'parent_id' => $parent_id, 'category' => '0', 'status' => $products['products_status']);
        }
      }

      return $category_tree_array;
    }

    public static function products_in_category_count($categories_id, $include_deactivated = false) {
        $products_count = 0;

        if ($include_deactivated) {
            $products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p2c.categories_id = '" . (int) $categories_id . "'");
        } else {
            $products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = p2c.products_id and p.products_status = '1' " . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p2c.categories_id = '" . (int) $categories_id . "'");
        }

        $products = tep_db_fetch_array($products_query);

        $products_count += $products['total'];

        $childs_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int) $categories_id . "'");
        if (tep_db_num_rows($childs_query)) {
            while ($childs = tep_db_fetch_array($childs_query)) {
                $products_count += self::products_in_category_count($childs['categories_id'], $include_deactivated);
            }
        }

        return $products_count;
    }

    public static function childs_in_category_count($categories_id) {
        $categories_count = 0;

        $categories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int) $categories_id . "'");
        while ($categories = tep_db_fetch_array($categories_query)) {
            $categories_count++;
            $categories_count += self::childs_in_category_count($categories['categories_id']);
        }

        return $categories_count;
    }

    public static function output_generated_category_path($id, $from = 'category', $format = '%2$s', $line_separator = '<br>') {
        $TEXT_TOP = defined('TEXT_TOP')?TEXT_TOP:'Top';
        $calculated_category_path_string = '';
        $calculated_category_path = self::generate_category_path($id, $from);
        for ($i = 0, $n = sizeof($calculated_category_path); $i < $n; $i++) {
            for ($j = 0, $k = sizeof($calculated_category_path[$i]); $j < $k; $j++) {
                $variant = $calculated_category_path[$i][$j];
                if ($from == 'category' && $variant['id'] == 0 && count($calculated_category_path[$i]) == 1) {
                    $variant['text'] = $TEXT_TOP;
                }
                $calculated_category_path_string .= (empty($format) ? $variant['text'] : sprintf($format, $variant['id'], $variant['text'])) . '&nbsp;&gt;&nbsp;';
            }
            $calculated_category_path_string = substr($calculated_category_path_string, 0, -16) . $line_separator;
        }
        $calculated_category_path_string = substr($calculated_category_path_string, 0, -(strlen($line_separator)));

        if (strlen($calculated_category_path_string) < 1)
            $calculated_category_path_string = (empty($format) ? $TEXT_TOP : sprintf($format, '0', $TEXT_TOP));

        return $calculated_category_path_string;
    }

    public static function get_category_path($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false) {
        global $languages_id;

        if (!is_array($category_tree_array))
            $category_tree_array = array();
        if ((sizeof($category_tree_array) < 1) && ($exclude != '0'))
            $category_tree_array[] = array('id' => '0', 'text' => TEXT_TOP);

        if ($include_itself) {
            $category_query = tep_db_query("select cd.categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " cd where cd.language_id = '" . (int) $languages_id . "' and cd.categories_id = '" . (int) $parent_id . "' and affiliate_id = 0");
            $category = tep_db_fetch_array($category_query);
            $category_tree_array[] = array('id' => $parent_id, 'text' => $category['categories_name']);
        }

        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id, cd.categories_seo_page_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "' and c.parent_id = '" . (int) $parent_id . "' and affiliate_id = 0 order by c.sort_order, cd.categories_name");
        while ($categories = tep_db_fetch_array($categories_query)) {
            if ($exclude != $categories['categories_id'])
                $category_tree_array[] = array('id' => $categories['categories_seo_page_name'], 'text' => $spacing . $categories['categories_name']);
            $category_tree_array = self::get_category_tree($categories['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array);
        }

        return $category_tree_array;
    }
/**
 * recursive: updates tree (recalculates left, right, level)
 * @global int $counter
 * @global int $level
 * @param int $parent_id
 */
    public static function categories_tree($parent_id = 0) {
        global $counter, $level/*, $languages_id*/;
        $languages_id = \common\classes\language::defaultId();
        $categories_query = tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where parent_id='" . (int) $parent_id . "' and c.categories_id=cd.categories_id and cd.language_id='" . (int) $languages_id . "' and cd.affiliate_id='0' order by sort_order, categories_name");
        while ($categories = tep_db_fetch_array($categories_query)) {
            $counter++;
            // update level and left part for node
            tep_db_query("update " . TABLE_CATEGORIES . " set categories_level='" . $level . "', categories_left='" . $counter . "' where categories_id='" . $categories['categories_id'] . "'");
            // check for siblings
            $sibling_query = tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where parent_id='" . (int) $categories['categories_id'] . "' and c.categories_id=cd.categories_id and cd.language_id='" . (int) $languages_id . "' and cd.affiliate_id='0' order by sort_order, categories_name");
            if (tep_db_num_rows($sibling_query) > 0) { // has siblings
                $level++;
                self::categories_tree($categories['categories_id']);
                $level--;
            }
            $counter++;
            // update right part of node
            tep_db_query("update " . TABLE_CATEGORIES . " set categories_right='" . $counter . "' where categories_id='" . $categories['categories_id'] . "'");
        }
    }
/**
 * calls categories_tree
 * @global int $counter
 * @global int $level
 */
    public static function update_categories() {
        global $counter, $level;
        $counter = 1;
        $level = 1;
        self::categories_tree();
    }

    public static function generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
        global $languages_id;

        if (!is_array($categories_array))
            $categories_array = array();

        if ($from == 'product') {
            $categories_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $id . "'");
            while ($categories = tep_db_fetch_array($categories_query)) {
                if (!(isset($categories_array[$index]) && is_array($categories_array[$index])))
                    $categories_array[$index] = array();
                if ($categories['categories_id'] == '0') {
                    array_unshift($categories_array[$index], array('id' => '0', 'text' => TEXT_TOP));
                } else {
                    $category_query = tep_db_query("select cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $categories['categories_id'] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "'");
                    $category = tep_db_fetch_array($category_query);
                    array_unshift($categories_array[$index], array('id' => $categories['categories_id']??null, 'text' => $category['categories_name']??null));
                    if ((tep_not_null($category['parent_id']??null)) && ($category['parent_id'] != '0'))
                        $categories_array = self::generate_category_path($category['parent_id'], 'category', $categories_array, $index);
                }
                $index++;
            }
        } elseif ($from == 'category') {
            if (!isset($categories_array[$index])) {
                $categories_array[$index] = [];
            }
            if (!is_array($categories_array[$index])) {
                $categories_array[$index] = [];
            }
            $category_query = tep_db_query("select cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "'");
            $category = tep_db_fetch_array($category_query);
            if (!is_array($category)) {
                $category = [
                    'categories_name' => '',
                    'parent_id' => 0,
                ];
            }
            array_unshift($categories_array[$index], array('id' => $id, 'text' => $category['categories_name']));
            if ((tep_not_null($category['parent_id'])) && ($category['parent_id'] != '0'))
                $categories_array = self::generate_category_path($category['parent_id'], 'category', $categories_array, $index);
        }

        return $categories_array;
    }
/**
 * return all category's parents (for cPath)
 * @global \common\helpers\type $languages_id
 * @param int $id
 * @return array [['id' => N, 'text' =>'', 'status' => 1/0],]
 */
    public static function getCategoryParents($id) {
        global $languages_id;
        $categories_array = [];

        $category_query = tep_db_query("select distinct  c.categories_id as id, c.categories_status, cd.categories_name  as text from  " . TABLE_CATEGORIES . " c1 join " . TABLE_CATEGORIES . "  c on c.categories_left<=c1.categories_left and c.categories_right>=c1.categories_right join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id=cd.categories_id and  cd.language_id='" . (int) $languages_id . "' where c1.categories_id = '" . (int)$id . "' order by c.categories_left");
        while ($category = tep_db_fetch_array($category_query)) {
         $categories_array[] = array('id' => $category['id'], 'text' => $category['text'], 'status' => $category['categories_status']);
        }
        tep_db_free_result($category_query);

        return $categories_array;
    }

    public static function getCategoryParentsIds($id) {
        $categories_array = [];

        $category_query = tep_db_query("select distinct  c.categories_id as id from  " . TABLE_CATEGORIES . " c1 join " . TABLE_CATEGORIES . "  c on c.categories_left<=c1.categories_left and c.categories_right>=c1.categories_right where c1.categories_id = '" . (int)$id . "' order by c.categories_left");
        while ($category = tep_db_fetch_array($category_query)) {
         $categories_array[] = $category['id'];
        }
        tep_db_free_result($category_query);

        return $categories_array;
    }

    public static function get_assigned_catalog($platform_id,$validate=false,$active = false) {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $assigned = array();
        if ( $validate ) {
          $get_assigned_r = tep_db_query(
            "SELECT pp.products_id AS id, p2c.categories_id as cid " .
            "FROM " . TABLE_PLATFORMS_PRODUCTS . " pp, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd " .
            "WHERE pp.platform_id = '" . intval($platform_id) . "' and pp.products_id=p2c.products_id ".
            " AND p.products_id=pp.products_id ".
            " AND c.categories_id=p2c.categories_id ".
            ($active? " AND p.products_status=1 " . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ":"") .
            " AND cd.categories_id=c.categories_id AND cd.language_id='".$languages_id."' AND cd.affiliate_id=0 ".
            " AND pd.products_id=p.products_id AND pd.language_id='".$languages_id."' AND pd.platform_id='".\common\classes\platform::defaultId()."' "
          );
        }else {
          $get_assigned_r = tep_db_query(
            "SELECT pp.products_id AS id, p2c.categories_id as cid " .
            "FROM " . TABLE_PLATFORMS_PRODUCTS . " pp, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c " .
            "WHERE pp.platform_id = '" . intval($platform_id) . "' and pp.products_id=p2c.products_id "
          );
        }
        if ( tep_db_num_rows($get_assigned_r)>0 ) {
          while( $_assigned = tep_db_fetch_array($get_assigned_r) ) {
            $_key = 'p'.(int)$_assigned['id']."_".$_assigned['cid'];
            $assigned[$_key] = $_key;
          }
        }
        if ( $validate ) {
          $get_assigned_r = tep_db_query(
            "SELECT DISTINCT pc.categories_id AS id " .
            "FROM " . TABLE_PLATFORMS_CATEGORIES . " pc, ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd " .
            "WHERE pc.platform_id = '" . intval($platform_id) . "' ".
            " AND c.categories_id=pc.categories_id ".
            " AND cd.categories_id=c.categories_id AND cd.language_id='".$languages_id."' AND cd.affiliate_id=0 "
          );
        }else {
          $get_assigned_r = tep_db_query(
            "SELECT categories_id AS id " .
            "FROM " . TABLE_PLATFORMS_CATEGORIES . " " .
            "WHERE platform_id = '" . intval($platform_id) . "' "
          );
        }
        if ( tep_db_num_rows($get_assigned_r)>0 ) {
          while( $_assigned = tep_db_fetch_array($get_assigned_r) ) {
            $assigned['c'.(int)$_assigned['id']] = 'c'.(int)$_assigned['id'];
          }
        }
        return $assigned;
    }

    public static function get_department_assigned_catalog($department_id,$validate=false,$active = false) {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $assigned = array();
        if ( $validate ) {
            $get_assigned_r = tep_db_query(
                "SELECT pp.products_id AS id, p2c.categories_id as cid " .
                "FROM " . TABLE_DEPARTMENTS_PRODUCTS . " pp, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd, ".TABLE_PRODUCTS." p, ".TABLE_PRODUCTS_DESCRIPTION." pd " .
                "WHERE pp.departments_id = '" . intval($department_id) . "' and pp.products_id=p2c.products_id ".
                " AND p.products_id=pp.products_id ".
                " AND c.categories_id=p2c.categories_id ".
                ($active? " AND p.products_status=1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ":"") .
                " AND cd.categories_id=c.categories_id AND cd.language_id='".$languages_id."' AND cd.affiliate_id=0 ".
                " AND pd.products_id=p.products_id AND pd.language_id='".$languages_id."' AND pd.platform_id='".\common\classes\platform::defaultId()."' "
            );
        }else {
            $get_assigned_r = tep_db_query(
                "SELECT pp.products_id AS id, p2c.categories_id as cid " .
                "FROM " . TABLE_DEPARTMENTS_PRODUCTS . " pp, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c " .
                "WHERE pp.departments_id = '" . intval($department_id) . "' and pp.products_id=p2c.products_id "
            );
        }
        if ( tep_db_num_rows($get_assigned_r)>0 ) {
            while( $_assigned = tep_db_fetch_array($get_assigned_r) ) {
                $_key = 'p'.(int)$_assigned['id']."_".$_assigned['cid'];
                $assigned[$_key] = $_key;
            }
        }
        if ( $validate ) {
            $get_assigned_r = tep_db_query(
                "SELECT DISTINCT pc.categories_id AS id " .
                "FROM " . TABLE_DEPARTMENTS_CATEGORIES . " pc, ".TABLE_CATEGORIES." c, ".TABLE_CATEGORIES_DESCRIPTION." cd " .
                "WHERE pc.departments_id = '" . intval($department_id) . "' ".
                " AND c.categories_id=pc.categories_id ".
                " AND cd.categories_id=c.categories_id AND cd.language_id='".$languages_id."' AND cd.affiliate_id=0 "
            );
        }else {
            $get_assigned_r = tep_db_query(
                "SELECT categories_id AS id " .
                "FROM " . TABLE_DEPARTMENTS_CATEGORIES . " " .
                "WHERE departments_id = '" . intval($department_id) . "' "
            );
        }
        if ( tep_db_num_rows($get_assigned_r)>0 ) {
            while( $_assigned = tep_db_fetch_array($get_assigned_r) ) {
                $assigned['c'.(int)$_assigned['id']] = 'c'.(int)$_assigned['id'];
            }
        }
        return $assigned;
    }

    public static function load_tree_slice($platform_id, $category_id, $activeProducts = false, $search = '', $inner = false, $innerCategory = false, $activeCategories = false){
          $tree_init_data = array();

          $category_selected_state = true;
          if ( $category_id>0 ) {
            $_check = tep_db_fetch_array(tep_db_query(
              "SELECT COUNT(*) AS c FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE platform_id='" . $platform_id . "' AND categories_id='" . (int)$category_id . "' "
            ));
            $category_selected_state = $_check['c']>0;
          }

          $languages_id = \Yii::$app->settings->get('languages_id');

          $get_categories_r = tep_db_query(
            "SELECT CONCAT('c',c.categories_id) as `key`, cd.categories_name as title, ".
            " IF(pc.categories_id IS NULL, 0, 1) AS selected, c.categories_image as image ".
            "FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
            ($innerCategory? "inner": "left") . " join ".TABLE_PLATFORMS_CATEGORIES." pc on pc.categories_id=c.categories_id and pc.platform_id='".$platform_id."' ".
            "WHERE cd.categories_id=c.categories_id and cd.language_id='" . $languages_id . "' AND cd.affiliate_id=0 and c.parent_id='" . (int)$category_id . "' ".
            ($activeCategories? " and c.categories_status = 1 " :"").
            "order by c.sort_order, cd.categories_name"
          );
          while ($_categories = tep_db_fetch_array($get_categories_r)) {
              $_categories['parent'] = (int)$category_id;
              $_categories['folder'] = true;
              $_categories['lazy'] = true;
              $_categories['selected'] = $category_selected_state && !!$_categories['selected'];
              $_categories['image'] = is_file(DIR_FS_CATALOG_IMAGES . $_categories['image']) ? $_categories['image'] : '';
              $tree_init_data[] = $_categories;
          }
          $addSelect = '';
          if (\common\helpers\Settings::isBackendSearchAggregateProductType()) {
              $addSelect = ', p.is_bundle, p.products_pctemplates_id, p.without_inventory, EXISTS (SELECT 1 FROM products_attributes pa WHERE pa.products_id = p.products_id) as attr_exists ';
          }
          $get_products_r = tep_db_query(
            "SELECT concat('p',p.products_id,'_',p2c.categories_id) AS `key`, ".ProductNameDecorator::instance()->listingQueryExpression('pd','pd1')." as title, p.products_id, ".
            " IF(pp.products_id IS NULL, 0, 1) AS selected, p.products_model as model ".
            $addSelect .
            "from ".TABLE_PRODUCTS_DESCRIPTION." pd left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id=pd.products_id and pd1.platform_id = '".($platform_id?$platform_id:intval(\common\classes\platform::defaultId()))."' and pd1.language_id = '".$languages_id."', ".TABLE_PRODUCTS_TO_CATEGORIES." p2c, ".TABLE_PRODUCTS." p ".
            ($inner ? "inner " : "left ") . " join ".TABLE_PLATFORMS_PRODUCTS." pp on pp.products_id=p.products_id and pp.platform_id='".$platform_id."' ".
            "WHERE  pd.products_id=p.products_id and pd.language_id='".$languages_id."' and pd.platform_id='".\common\classes\platform::defaultId()."' and p2c.products_id=p.products_id and p2c.categories_id='".(int)$category_id."' ".
            ($activeProducts? " AND p.products_status=1 " . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ":"") .
            (tep_not_null($search)?" and pd.products_name like '%{$search}%' " :"").
            "order by p.sort_order, title"
          );


          if ( tep_db_num_rows($get_products_r)>0 ) {
              while ($_product = tep_db_fetch_array($get_products_r)) {
                //$_product['parent'] = (int)$category_id;
                  $_product['selected'] = $category_selected_state && !!$_product['selected'];
                  $_product=self::setProductData($_product);
                  $tree_init_data[] = $_product;
              }
          }

          return $tree_init_data;
    }

    public static function setProductData(array $_product=[]) : Array 
    {
        static $currencies=null;
        static $viewSettings=null;
        if (!$currencies)
           $currencies = new \common\classes\Currencies();
        if (!$viewSettings) {
            if (defined('BACKEND_SEARCH_SHOW_DATA'))
              $viewSettings=array_fill_keys(array_map('trim',explode(",",BACKEND_SEARCH_SHOW_DATA)),true);
            else $viewSettings=[];
        }

        $thumbnail='';
        if (($viewSettings['Price']??null) || (!defined('BACKEND_SEARCH_AGREGATE_PRODUCT_DATA') || BACKEND_SEARCH_AGREGATE_PRODUCT_DATA == 'Standard') ) {
           $price = \common\helpers\Product::get_products_price($_product['products_id']);
           $_product['price_ex'] = $currencies->display_price($price, 0, 1, false);
        }

        if (($viewSettings['Image']??null) || (!defined('BACKEND_SEARCH_AGREGATE_PRODUCT_DATA') || BACKEND_SEARCH_AGREGATE_PRODUCT_DATA == 'Standard')
            ) {
            $_product['image'] = \common\classes\Images::getImage($_product['products_id'], 'Small');
            $thumbnail = \common\classes\Images::getImage($_product['products_id']);
            if ($thumbnail) {
                $thumbnail = '<span style="display: none;" class="product-thumbnail">' . $thumbnail . '</span> ';
            } else {
                $thumbnail = '<span style="display: none;" class="product-thumbnail-ico fancytree-icon icon-cubes"></span> ';
            }
        }

        if( ($viewSettings['Stock']??null) || !defined('BACKEND_SEARCH_AGREGATE_PRODUCT_DATA') || BACKEND_SEARCH_AGREGATE_PRODUCT_DATA=='Standard') {
            $_product['stock'] = \common\helpers\Product::get_products_stock($_product['products_id']);
            $_product['stock_virtual'] = \common\helpers\Product::getVirtualItemQuantity($_product['products_id'], $_product['stock']);
        }

        if (\common\helpers\Settings::isBackendSearchAggregateProductType()) {
            $_product['type'] = \common\helpers\Product::getProductTypes($_product);
        }

        $_product['name'] = $_product['title'];
        $_product['title'] = '<span class="' . (isset($_product['stock']) ? ($_product['stock'] == 0 ? ' empty-stock' : '') : "") . '">' . $thumbnail . $_product['title'] . '</span>';
        return $_product;
    }

    public static function load_department_tree_slice($department_id, $category_id, $active = false, $search = '', $inner = false)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $tree_init_data = array();

        $category_selected_state = true;
        if ( $category_id>0 ) {
            $_check = tep_db_fetch_array(tep_db_query(
                "SELECT COUNT(*) AS c FROM " . TABLE_DEPARTMENTS_CATEGORIES . " WHERE departments_id='" . $department_id . "' AND categories_id='" . (int)$category_id . "' "
            ));
            $category_selected_state = $_check['c']>0;
        }

        $get_categories_r = tep_db_query(
            "SELECT CONCAT('c',c.categories_id) as `key`, cd.categories_name as title, ".
            " IF(pc.categories_id IS NULL, 0, 1) AS selected ".
            "FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
            " left join ".TABLE_DEPARTMENTS_CATEGORIES." pc on pc.categories_id=c.categories_id and pc.departments_id='".$department_id."' ".
            "WHERE cd.categories_id=c.categories_id and cd.language_id='" . $languages_id . "' AND cd.affiliate_id=0 and c.parent_id='" . (int)$category_id . "' ".
            "order by c.sort_order, cd.categories_name"
        );
        while ($_categories = tep_db_fetch_array($get_categories_r)) {
            //$_categories['parent'] = (int)$category_id;
            $_categories['folder'] = true;
            $_categories['lazy'] = true;
            $_categories['selected'] = $category_selected_state && !!$_categories['selected'];
            $tree_init_data[] = $_categories;
        }
        $get_products_r = tep_db_query(
            "SELECT concat('p',p.products_id,'_',p2c.categories_id) AS `key`, pd.products_name as title, ".
            " IF(pp.products_id IS NULL, 0, 1) AS selected ".
            "from ".TABLE_PRODUCTS_DESCRIPTION." pd, ".TABLE_PRODUCTS_TO_CATEGORIES." p2c, ".TABLE_PRODUCTS." p ".
            ($inner ? "inner " : "left ") . " join ".TABLE_DEPARTMENTS_PRODUCTS." pp on pp.products_id=p.products_id and pp.departments_id='".$department_id."' ".
            "WHERE pd.products_id=p.products_id and pd.language_id='".$languages_id."' and pd.platform_id='".\common\classes\platform::defaultId()."' and p2c.products_id=p.products_id and p2c.categories_id='".(int)$category_id."' ".
            ($active? " AND p.products_status=1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ":"") .
            (tep_not_null($search)?" and pd.products_name like '%{$search}%' " :"").
            "order by p.sort_order, pd.products_name"
        );
        if ( tep_db_num_rows($get_products_r)>0 ) {
            while ($_product = tep_db_fetch_array($get_products_r)) {
                //$_product['parent'] = (int)$category_id;
                $_product['selected'] = $category_selected_state && !!$_product['selected'];
                $tree_init_data[] = $_product;
            }
        }

        return $tree_init_data;
    }

    public static function get_category_image ($cat_id){
        return tep_db_fetch_array(tep_db_query("select categories_image, categories_image_2 from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$cat_id . "'"));
    }

    public static function breadcrumbs ($category_id){
        $breadcrumb_array = $parent_categories = array();
        \common\helpers\Categories::get_parent_categories($parent_categories, $category_id);
        foreach (array_reverse($parent_categories) as $cat_id) {
            $breadcrumb_array[] = \common\helpers\Categories::get_categories_name($cat_id);
        }
        return implode(' / ', $breadcrumb_array);
    }

/**
 * Url of parent category if category exists or false
 * @param integer $products_id
 * @return string|false
 */
    public static function get302redirect($categoriesId) {
      $new_url = false;
      $tmp = \common\models\Categories::findOne(['categories_id' => $categoriesId]);
      if ($tmp) {
        $check = $tmp->getVisibleParents()->asArray()->all();
        if (!empty($check)) {
          $new_url = \Yii::$app->urlManager->createUrl(['catalog', 'cPath' => implode('_', \yii\helpers\ArrayHelper::map($check, 'categories_id', 'categories_id'))]);
        } else {
          $new_url = \Yii::$app->urlManager->createAbsoluteUrl(['index']);
        }
      }
      return $new_url;
    }

/**
 * check whether the category is visible and redirects to to parent category if it is not visible
 * @param integer $categoriesId
 */
    public static function redirectIfInactive($categoriesId) {
      $new_url = false;
      if (! \common\models\Categories::isVisible($categoriesId) ) {
        $new_url = self::get302redirect($categoriesId);
      }
      if ($new_url && !empty($new_url)) {
          header('HTTP/1.1 302 Found');
          header("Location: " . $new_url);
          exit();
      }

    }

/**
 * search categories with parent indexed by level
 * @param string $searchTerm
 * @param int|array|false $platform_id default false  - no restriction, 0<= current platform
 * @param bool $active only def true
 * @param bool $byLevel - def true | by categories_id
 * @return array {['categories_level']}['categories_id'] = [c.categories_level, c.categories_id as id, c.parent_id, c.categories_left,  c.categories_status, cd.categories_name  as text]
 */
    public static function searchCategories($searchTerm, $platform_id = false, $active = true, $byLevel = true) {
        $languages_id = \Yii::$app->settings->get('languages_id');

        if ($platform_id !== false) {
          if (is_array($platform_id)) {
            $platform_id = array_map('intval', $platform_id);
          } elseif ($platform_id <=0) {
            $platform_id = intval(platform::currentId());
          } else {
            $platform_id = intval($platform_id);
          }
        }

        $cQ = \common\models\Categories::find()->alias('c1')
            ->innerJoin(['cd1' => TABLE_CATEGORIES_DESCRIPTION], "c1.categories_id=cd1.categories_id and cd1.language_id='" . (int) $languages_id . "'")
            ->innerJoin(['c' => TABLE_CATEGORIES], "c.categories_left<=c1.categories_left and c.categories_right>=c1.categories_right")
            ->innerJoin(['cd' => TABLE_CATEGORIES_DESCRIPTION], "c.categories_id=cd.categories_id and cd.language_id='" . (int) $languages_id . "'")
            ->select('c.categories_level, c.categories_id as id, c.parent_id, c.categories_left,  c.categories_status, cd.categories_name  as text ')
            ->andWhere(['like', 'cd1.categories_name', $searchTerm])
            ->orderBy('c.categories_left, c.sort_order, cd.categories_name')
            ->distinct()->asArray()
            ;
        if ($platform_id) {
          $cQ->andWhere([
           'in', 'c.categories_id', (new \yii\db\Query())->select('categories_id')->distinct()->from(TABLE_PLATFORMS_CATEGORIES)->where(['platform_id' => $platform_id])
          ]);
        }
        if ($active) {
          $cQ->andWhere('c1.categories_status=1');
          $cQ->andWhere('c.categories_status=1');
        }

        $categories_by_level = [];
        if ($byLevel) {
          foreach ($cQ->all() as $categories) {
            $categories['child'] = [];
            $categories_by_level[$categories['categories_level']][$categories['id']] = $categories;
          }
        } else {
          $categories_by_level = $cQ->indexBy('id')->all();
        }

        return $categories_by_level;
    }

    public static function getCategoryTree($parent_id = '0', $platform_id = false, $departments = false) {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $filter_by_platform = array();
        if (is_array($platform_id)) {
            $filter_by_platform = array_map('intval', $platform_id);
        }

        $platform_filter_categories = '';
        if (count($filter_by_platform) > 0) {
            $platform_filter_categories .= ' and c.categories_id IN (SELECT categories_id FROM ' . TABLE_PLATFORMS_CATEGORIES . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
        }

        $filter_by_departments = array();
        if (is_array($departments)) {
            $filter_by_departments = array_map('intval', $departments);
        }
        if (count($filter_by_departments) > 0) {
            $platform_filter_categories .= ' and c.categories_id IN (SELECT categories_id FROM ' . TABLE_DEPARTMENTS_CATEGORIES . ' WHERE departments_id IN(\'' . implode("','", $filter_by_departments) . '\'))  ';
        }

        $categories_query = tep_db_query("select c.categories_level, c.categories_id as id, cd.categories_name as text, c.parent_id, c.categories_status from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "' and c1.parent_id = '" . (int) $parent_id . "' and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right) and affiliate_id = 0 {$platform_filter_categories} order by c.categories_left, c.sort_order, cd.categories_name");

        $categories_by_level = [];
        while ($categories = tep_db_fetch_array($categories_query)) {
          $categories['child'] = array();
          $categories_by_level[$categories['categories_level']][$categories['id']] = $categories;
        }

        $categoriesTree = self::buildTree($categories_by_level);
        return $categoriesTree;
    }

    /**
     * transform plain array to tree
     * @param array $categories_by_level
     * @return array
     */
    public static function buildTree(array &$categories_by_level) {
      $categoriesTree = [];
      if (count($categories_by_level)) {
        $levels = array_keys($categories_by_level);
        $topLevel = min($levels);
        for ($level = max($levels); $level >= $topLevel; $level--) {
          foreach ($categories_by_level[$level] as $id => $cat_info) {
            if ($level == $topLevel) {
              $categoriesTree[] = $cat_info;
            } else {
              $to_parent_id = $cat_info['parent_id'];
              $categories_by_level[$level - 1][$to_parent_id]['child'][] = $cat_info;
            }
          }
        }
      }
      return $categoriesTree;
    }

    /**
     *
     * @param string $searchTerm
     * @param int|array|false $platform_id default false  - no restriction, 0<= current platform
     * @param bool $active only
     * @return array [category_id] => [category_name, cPath, [parents => [[category_name, cPath],[category_name, cPath]] ]]
     */
    public static function searchCategoryTreePlain($searchTerm, $platform_id = false, $active = true) {
      $allCats = self::searchCategories($searchTerm, $platform_id, $active, false);
      $ret = [];
      foreach ($allCats as $cat) {
        if (stripos($cat['text'], $searchTerm) !== false) {
          $ret[$cat['id']] = $cat;
          $ret[$cat['id']]['cPath'] = $cat['id'];
          if ($cat['parent_id']>0) {
            $parentId = $cat['parent_id'];
            $ret[$cat['id']]['parents'] = [];
            while ($parentId) {
              $ret[$cat['id']]['parents'][] = $allCats[$parentId];
              if (is_array($allCats[$parentId]) && $allCats[$parentId]['parent_id']>0){
                $parentId = $allCats[$parentId]['parent_id'];
              } else {
                $parentId = false;
              }
            }
            $ret[$cat['id']]['parents'] = array_reverse($ret[$cat['id']]['parents']);
            if ($ret[$cat['id']]['parents'][0]['categories_level'] != 1) { //inactive parent
              unset($ret[$cat['id']]);
            } else {
              $ret[$cat['id']]['cPath'] = implode('_', \yii\helpers\ArrayHelper::getColumn($ret[$cat['id']]['parents'], 'id')) . '_' . $ret[$cat['id']]['cPath'];
            }
          }
        }
      }

      return $ret;
    }

    public static function getAdminDetailsList($categoriesId, $platform_id = false) {

      $languages_id = \Yii::$app->settings->get('languages_id');
      if (!is_array($categoriesId)) {
        if (is_numeric($categoriesId)) {
          $categoriesId = [$categoriesId];
        } else {
          $categoriesId = array_map('intval',preg_split('/,/',$categoriesId,-1,PREG_SPLIT_NO_EMPTY));
        }
      }


      if ($platform_id !== false) {
        if (is_array($platform_id)) {
          $platform_id = array_map('intval', $platform_id);
        } elseif ($platform_id <=0) {
          $platform_id = intval(platform::currentId());
        } else {
          $platform_id = intval($platform_id);
        }
      }
/*
      $cQ = \common\models\Categories::find()->alias('c1')
          ->innerJoin(['cd1' => TABLE_CATEGORIES_DESCRIPTION], "c1.categories_id=cd1.categories_id and cd1.language_id='" . (int) $languages_id . "'")
//          ->innerJoin(['c' => TABLE_CATEGORIES], "c.categories_left<=c1.categories_left and c.categories_right>=c1.categories_right")
//          ->innerJoin(['cd' => TABLE_CATEGORIES_DESCRIPTION], "c.categories_id=cd.categories_id and cd.language_id='" . (int) $languages_id . "'")
//          ->select('c.categories_level, c.categories_id as id, c.parent_id, c.categories_left,  c.categories_status, cd.categories_name  as text ')
          ->select('c1.categories_level, c1.categories_id, c1.parent_id, c1.categories_left,  c1.categories_status, cd1.categories_name ')
          ->andWhere([
            'c1.categories_id' => array_map('intval', $categoriesId)
            ])
          //->orderBy('c.categories_left, c.sort_order, cd.categories_name')
          ->orderBy('c1.categories_left, c1.sort_order, cd1.categories_name')
          ->distinct()
          ;*/
      $cQ = \common\models\Categories::find()->alias('c1')->joinWith('description')
          ->select('c1.categories_level, c1.categories_id, c1.parent_id, c1.categories_left, c1.categories_status, categories_name ')
          ->andWhere([
            'c1.categories_id' => array_map('intval', $categoriesId)
            ])
          ->orderBy('c1.categories_left, c1.sort_order, categories_name')
          ->distinct()
          ;
      if ($platform_id) {
        $cQ->andWhere([
         //'in', 'c.categories_id', (new \yii\db\Query())->select('categories_id')->distinct()->from(TABLE_PLATFORMS_CATEGORIES)->where(['platform_id' => $platform_id])
         'in', 'c1.categories_id', (new \yii\db\Query())->select('categories_id')->distinct()->from(TABLE_PLATFORMS_CATEGORIES)->where(['platform_id' => $platform_id])
        ]);
      }

/*
              $cArray = \common\helpers\Categories::searchCategoryTreePlain($keywords, 0);
              foreach($cArray  as $info_array) {
                  $cResponse[] = array(
                    'type' => TEXT_CATEGORIES,
                    'type_class' => 'categories',
                    'link' => tep_href_link('catalog', 'cPath=' . $info_array['cPath']),
                    'title' => preg_replace($re, $replace, strip_tags($info_array['text'])),
                    'extra' =>  (!empty($info_array['parents']) && is_array($info_array['parents'])?
                            '<span class="brackets">(</span>' . implode('<span class="comma-sep">, </span>', \yii\helpers\ArrayHelper::getColumn($info_array['parents'], 'text')) .
                            '<span class="brackets">)</span>':''),
                  );
              }
*/
      $categories_by_level = [];
      $categories_by_level = $cQ->all();

      $ret = '';
      foreach ($categories_by_level as $cat) {
        $ret .= '<div class="row col-md-12 prod-row ' . (!$cat->categories_status?'dis_module':'') . '">';
        $ret .= '<span class="col-md-6 cat-name">' . $cat->description->categories_name;
        if ($cat->parent_id>0) {
          $parents = \yii\helpers\ArrayHelper::getColumn( self::getCategoryParents($cat->categories_id), 'text');
          array_pop($parents);
          //$ret .= '<span class="col-md-6 cat-name"> (' . implode(', ', $parents) . ')</span>';
          $ret .= ' (' . implode(', ', $parents) . ')';
        }
        $ret .= '</span>';
        $ret .= '</div>';
      }
      //$ret = '<div class="row col-md-12 container">' . $ret . '</div>';

      return $ret;

    }

    public static function hasGrouppedProducts($category_id, $include_inactive = false) {
        $q = \common\models\Categories::findOne($category_id)->getDescendants(null, true)
            ->joinWith(['productIds p2c'])
            ->leftJoin(['p' => TABLE_PRODUCTS], 'p.products_id=p2c.products_id')
            ->andWhere('p.products_groups_id>0')
            ->select('p.products_id')
            ->orderBy([])
            ->limit(1)
        ;
        if (!$include_inactive) {
            $q->andWhere(['p.products_status' => 1]);
        }
        return !empty($q->one());

    }

    public static function createCategoriesCache($categoryIds = null)
    {
        return true;
        if (!is_array($categoryIds)) {
            $categories = \common\models\Categories::find()->select('categories_id')->asArray()->all();
        } else {
            $categories = [];
            foreach (array_unique($categoryIds) as $id) {
                if ( empty($id) ) continue;
                $categories[] = ['categories_id' => (int)$id];
            }
        }
        if ( count($categories)==0 ){
            return true;
        }
        $platforms = \common\classes\platform::getList(false, false);
        $groups = [0];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
            $groups = array_merge($groups, \common\helpers\Group::get_customer_groups_list());
            if (count($groups) > 20) {
                return true;
            }
        }

        $errors = false;

        foreach ($categories as $category) {
            $categoryDataArray = [];
            foreach ($platforms as $platform) {
                $keepGroupId = \Yii::$app->storage->get('customer_groups_id');
                foreach ($groups as $groupId => $group) {
                    \Yii::$app->storage->set('customer_groups_id', $groupId);
                    $products = self::count_products_in_category($category['categories_id'], false, $platform['id']);
                    $categoryDataArray[] = [
                        'categories_id' => $category['categories_id'],
                        'platform_id' => $platform['id'],
                        'groups_id' => $groupId,
                        'products' => $products,
                    ];
                }
                \Yii::$app->storage->set('customer_groups_id', $keepGroupId);
            }

            \common\models\CategoriesCache::deleteAll(['categories_id' => $category['categories_id']]);
            foreach ($categoryDataArray as $categoryData) {
                try {
                    $categoriesCache = new \common\models\CategoriesCache($categoryData);
                    $categoriesCache->save();
                } catch (\Exception $ex) {
                    \Yii::error('Insert categories cache error: ' . $ex->getMessage() . "\n" . $ex->getTraceAsString());
                }
                if (is_array($categoriesCache->errors) && count($categoriesCache->errors) > 0) {
                    $errors = true;
                }
            }
        }

        return !$errors;
    }
}
