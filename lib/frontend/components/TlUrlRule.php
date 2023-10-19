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

namespace app\components;

use common\classes\language;
use common\components\InformationPage;
use frontend\services\CatalogPagesService;
use yii\web\UrlRule;
use Yii;
use yii\helpers\ArrayHelper;

class TlUrlRule extends UrlRule
{
    public function init()
    {
        if ($this->name === null) {
            $this->name = __CLASS__;
        }
    }

    public function createUrl($manager, $route, $params)
    {
        $currencies = \Yii::$container->get('currencies');
        $settings = Yii::$app->getUrlManager()->getSettings();
        if ( $settings['search_engine_friendly_urls'] && $settings['search_engine_unhide'] ) {
            $_languages_id = \Yii::$app->settings->get('languages_id');
            if (isset($params['language'])){
                global $lng;
                if (isset($lng->catalog_languages[$params['language']])){
                    $_languages_id = $lng->catalog_languages[$params['language']]['id'];
                }
            }

            if (isset($settings['seo_url_parts_language']) && $settings['seo_url_parts_language'] == true) {
                if (isset($params['language']) && $params['language'] == \common\classes\language::get_code(\common\classes\language::defaultId())) {
                    unset($params['language']);
                }
            }
            if (isset($settings['seo_url_parts_currency']) && $settings['seo_url_parts_currency'] == true) {
                if (isset($params['currency']) && $params['currency'] == $currencies->dp_currency) {
                    unset($params['currency']);
                }
            }
            if ($route === 'catalog/product' && tep_not_null($params['products_id'] ?? null) && !strstr($params['products_id'], '{') && !strstr($params['products_id'], '}')) {
                $products_seo_page_name = \common\helpers\Product::getSeoName((int)$params['products_id'], (int)$_languages_id, (isset($params['platform_id'])?$params['platform_id']:\common\classes\platform::currentId()));
                if (tep_not_null($products_seo_page_name)) {
                    $params_array = array();
                    foreach ($params as $key => $value) {
                      if (in_array($key, ['products_id', 'platform_id'])) continue;
                      if (is_array($value)) {
                        for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                          $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                        }
                      } else {
                        $params_array[] = $key . '=' . urlencode($value);
                      }
                    }
                    return $products_seo_page_name . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                }
            }
// {{
            $properties_array = $brands_array = $categories_array = array();
            if ( ($route === 'catalog' || $route === 'catalog/index' || $route === 'catalog/all-products' ||
                  $route === 'catalog/products-new' || $route === 'catalog/sales') && is_array($params) ) {
                foreach ($params as $key => $value) {
                    if (defined('SEO_URL_SALES_ONLY_FILTER_KEY') && SEO_URL_SALES_ONLY_FILTER_KEY != '' && $key == 'salesOnly') {
                        // Sales Only Filter
                        if (boolval($value)) {
                            $properties_array[] = SEO_URL_SALES_ONLY_FILTER_KEY;
                            unset($params[$key]);
                        }
                    }
                    if ($key == 'cat') {
                        foreach (explode(',', (is_array($value) ? implode(',', $value) : $value)) as $val_id) {
                            if ($val_id > 0) {
                                $category = tep_db_fetch_array(tep_db_query("select c.categories_id, cd.categories_seo_page_name from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on (c.categories_id = cd.categories_id and cd.language_id = '" . (int)$_languages_id . "')  where c.categories_id = '" . (int)$val_id . "' order by length(cd.categories_seo_page_name) desc limit 1"));
                                if (tep_not_null($category['categories_seo_page_name'])) {
                                    $categories_array[] = $category['categories_seo_page_name'];
                                } elseif ($category['categories_id'] == $val_id) {
                                    $categories_array[] = $category['categories_id'];
                                }
                            }
                        }
                        sort($categories_array);
                        $properties_array[] = '(' . implode(',', $categories_array) . ')';
                        unset($params[$key]);
                    }
                    if ($key == 'brand') {
                        foreach (explode(',', (is_array($value) ? implode(',', $value) : $value)) as $val_id) {
                            if ($val_id > 0) {
                                $manufacturer = tep_db_fetch_array(tep_db_query("select m.manufacturers_id, mi.manufacturers_seo_name from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$_languages_id . "')  where m.manufacturers_id = '" . (int)$val_id . "' order by length(mi.manufacturers_seo_name) desc limit 1"));
                                if (tep_not_null($manufacturer['manufacturers_seo_name'])) {
                                    $brands_array[] = $manufacturer['manufacturers_seo_name'];
                                } elseif ($manufacturer['manufacturers_id'] == $val_id) {
                                    $brands_array[] = $manufacturer['manufacturers_id'];
                                }
                            }
                        }
                        sort($brands_array);
                        $properties_array[] = '(' . implode(',', $brands_array) . ')';
                        unset($params[$key]);
                    }
                    if (preg_match("/^pr(\d+)$/", $key, $arr)) {
                        $property = tep_db_fetch_array(tep_db_query("select pd.properties_seo_page_name from " . TABLE_PROPERTIES . " p left join " . TABLE_PROPERTIES_DESCRIPTION . " pd on (p.properties_id = pd.properties_id and pd.language_id = '" . (int)$_languages_id . "')  where p.properties_id = '" . (int)$arr[1] . "' order by length(pd.properties_seo_page_name) desc limit 1"));
                        if (tep_not_null($property['properties_seo_page_name'])) {
                            $values_array = array();
                            foreach (explode(',', (is_array($value) ? implode(',', $value) : $value)) as $val_id) {
                                if ($val_id > 0) {
                                    $value = tep_db_fetch_array(tep_db_query("select values_seo_page_name from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int)$arr[1] . "' and values_id = '" . (int)$val_id . "' and language_id = '" . (int)$_languages_id . "' order by length(values_seo_page_name) desc limit 1"));
                                    if (tep_not_null($value['values_seo_page_name'])) {
                                        if (!in_array($value['values_seo_page_name'], $values_array)) $values_array[] = $value['values_seo_page_name'];
                                    } else {
                                        if (!in_array($val_id, $values_array)) $values_array[] = $val_id;
                                    }
                                } elseif (in_array($val_id, ['Y','N'])) {
                                    if (!in_array($val_id, $values_array)) $values_array[] = $val_id;
                                }
                            }
                            if (count($values_array) > 0) {
                                sort($values_array);
                                $properties_array[] = $property['properties_seo_page_name'] . '(' . implode(',', $values_array) . ')';
                            }
                            unset($params[$key]);
                        }
                    }
                }
            }
// }}
            if ($route === 'catalog' || $route === 'catalog/index') {
                $params_cPath = ArrayHelper::getValue($params,'cPath');
                if (tep_not_null($params_cPath)) {
                    $cPath_array = \common\helpers\Categories::parse_category_path($params_cPath);
// {{
                    if ($settings['seo_url_full_categories_path']) {
                        $category['categories_seo_page_name'] = '';
                        $categories_array = array($cPath_array[(sizeof($cPath_array)-1)]);
                        \common\helpers\Categories::get_parent_categories($categories_array, $categories_array[0]);
                        $categories_array = array_reverse($categories_array);
                        foreach ($categories_array as $cat_id) {
                            $cat['categories_seo_page_name'] = \common\helpers\Categories::getSeoPageName($cat_id, $_languages_id);
                            $category['categories_seo_page_name'] .= $cat['categories_seo_page_name'] . '/';
                        }
                        $category['categories_seo_page_name'] = trim($category['categories_seo_page_name'], '/');
                    } else
// }}
                    $category['categories_seo_page_name'] = \common\helpers\Categories::getSeoPageName($cPath_array[(sizeof($cPath_array)-1)], $_languages_id);
                    if (tep_not_null($category['categories_seo_page_name'])) {
                        $params_array = array();
                        foreach ($params as $key => $value) {
                          if ($key == 'cPath') continue;
                          if (is_array($value)) {
                            for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                              $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                            }
                          } else {
                            $params_array[] = $key . '=' . urlencode($value);
                          }
                        }
                        return $category['categories_seo_page_name'] . (count($properties_array) > 0 ? '/' . implode('/', $properties_array) : '') . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                    }
                } elseif (ArrayHelper::getValue($params, 'manufacturers_id') > 0) {
                    $manufacturer = tep_db_fetch_array(tep_db_query("select mi.manufacturers_seo_name from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$_languages_id . "')  where m.manufacturers_id = '" . (int)$params['manufacturers_id'] . "' order by length(mi.manufacturers_seo_name) desc limit 1"));
                    if (tep_not_null($manufacturer['manufacturers_seo_name'])) {
                        $params_array = array();
                        foreach ($params as $key => $value) {
                          if ($key == 'manufacturers_id') continue;
                          if (is_array($value)) {
                            for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                              $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                            }
                          } else {
                            $params_array[] = $key . '=' . urlencode($value);
                          }
                        }
                        return $manufacturer['manufacturers_seo_name'] . (count($properties_array) > 0 ? '/' . implode('/', $properties_array) : '') . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                    }
                }
            }
            if ( ($route === 'info' || $route === 'info/index') && ArrayHelper::getValue($params, 'info_id') > 0) {
                $information = \common\components\InformationPage::getFrontendData((int)$params['info_id'], (int)$_languages_id);
                if ( is_array($information) && !empty($information['seo_page_name']) ){
                    $params_array = array();
                    foreach ($params as $key => $value) {
                      if ($key == 'info_id' || $key == 'information_id') continue;
                      if (is_array($value)) {
                        for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                          $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                        }
                      } else {
                        $params_array[] = $key . '=' . urlencode($value);
                      }
                    }
                    return $information['seo_page_name'] . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                }else{
                    unset($params['information_id']);
                }
            }
            if (($route === 'catalog/all-products' || $route === 'catalog/products-new' || $route === 'catalog/sales') && is_array($params)) {
                if (count($properties_array) > 0) {
                    $params_array = array();
                    foreach ($params as $key => $value) {
                      if (is_array($value)) {
                        for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                          $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]);
                        }
                      } else {
                        $params_array[] = $key . '=' . urlencode($value);
                      }
                    }
                    if ($route === 'catalog/products-new') {
                        return 'catalog/products-new/' . implode('/', $properties_array) . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                    } elseif ($route === 'catalog/sales') {
                        return 'catalog/sales/' . implode('/', $properties_array) . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                    } else {
                        return implode('/', $properties_array) . (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');
                    }
                }
            }

            if ($ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryLocation', 'allowed')) {
                $url = $ext::createUrl($route, $params, $_languages_id);
                if ( $url ) return $url;
            }

            if ($route == 'info/custom') {
                $params_array = array();
                if (is_array($params)) {
                    foreach ($params as $key => $value) {
                      if ( $key=='action' ) continue;
                      if (is_array($value)) {
                        for ($i=0,$n=sizeof($value); $i<$n; $i++) {
                          $params_array[] = $key . urlencode('[]') . '='. urlencode($value[$i]??'');
                        }
                      } else {
                        $params_array[] = $key . '=' . urlencode($value);
                      }
                    }
                }
                $platform_config = \Yii::$app->get('platform')->getConfig(PLATFORM_ID);
                return preg_replace("/".preg_quote($platform_config->getCatalogBaseUrl(true), "/")."/", "", Yii::$app->request->getPathInfo()). (count($params_array) > 0 ? '?' . implode('&', $params_array) : '');;
            }

            if ($ext = \common\helpers\Acl::checkExtensionAllowed('Blog', 'allowed')) {
                $url = $ext::createUrl($route, $params);
                if ( $url ) return $url;
            }elseif (\frontend\design\Info::hasBlog() && ($route == 'blog/index' || $route=='blog')) {
                if ( is_array($params) && isset($params['url_path']) ) {
                    $blogUri = 'blog/'.$params['url_path'];
                    unset($params['url_path']);
                    if ( count($params)>0 ) {
                        $blogUri .= (count($params) > 0 ? '?' .  http_build_query($params) : '');
                    }
                    return $blogUri;
                }else{
                    $blogUri = 'blog/';
                    if ( is_array($params) && count($params)>0 ) {
                        $blogUri .= (count($params) > 0 ? '?' .  http_build_query($params) : '');
                    }
                    return $blogUri;
                }
                //return Yii::$app->request->getPathInfo();
            }

            /* CATALOG-PAGES START /**/
            try {
                /** @var CatalogPagesService $catalogPagesService */
                $catalogPagesService = \Yii::createObject(CatalogPagesService::class);
                if ($catalogPagesService->isRoute($route)) {
                    return $catalogPagesService->createUrl($route, $params, $_languages_id, (int)\common\classes\platform::currentId());
                }
            } catch (\Exception $e) {
                \Yii::error('Catalog Pages error parse url.' . $e->getMessage());
            }
            /* CATALOG-PAGES END /**/

            if ($route == 'index/index' ) {
                $route = '/';
            }

            return parent::createUrl($manager, $route, $params);
        } else {
            return parent::createUrl($manager, $route, $params);
        }
        return false;
    }

    public function parseRequest($manager, $request)
    {
        foreach (\common\helpers\Hooks::getList('url-rule/parse-request/before') as $filename) {
            $result = include($filename);
            if ($result && is_array($result)) return $result;
        }
        $languages_id = \Yii::$app->settings->get('languages_id');
        if (!tep_session_is_registered('cart') && session_status() === PHP_SESSION_ACTIVE) {//first run
            $ipAddress = \common\helpers\System::get_ip_address();
            $countryCode = '';
            if ( !function_exists( 'geoip_country_code_by_name_v6' ) ) { 
                require_once(DIR_FS_CATALOG . DIR_WS_MODULES . 'geoip/geoip.inc');
            } 
            if (function_exists('geoip_db_avail') && geoip_db_avail(GEOIP_COUNTRY_EDITION)) {
                $countryCode = geoip_country_code_by_name($ipAddress);
            } else {
                $gi = geoip_open(DIR_FS_CATALOG . DIR_WS_MODULES . 'geoip/GeoIP.dat', GEOIP_STANDARD);
                $countryCode = geoip_country_code_by_addr($gi, $ipAddress);
                geoip_close($gi);
            }
            $countryCode = mb_strtolower($countryCode);
            global $lng, $request_type;
            if (!empty($countryCode) && $ipAddress != $_SERVER['SERVER_ADDR']) {
                $country = tep_db_fetch_array(tep_db_query("select countries_id from countries where countries_iso_code_2 = '" . tep_db_input($countryCode) . "'"));
                if (isset($country['countries_id'])) {
                    $locations = tep_db_fetch_array(tep_db_query("select * from platforms_locations where platform_id = '" . (int)PLATFORM_ID . "' and country = '" . (int)$country['countries_id'] . "' limit 1"));
                    if (is_array($locations)) {
                        $currency = \Yii::$app->settings->get('currency');
                        if ($currency != $locations['currency'] || $lng->get_code() != $locations['language']) {
                            $params = $request->getQueryParams();
                            $params['language'] = $locations['language'];
                            $params['currency'] = $locations['currency'];
                            $url = $manager->createAbsoluteUrl(array_merge([$request->getPathInfo()], $params), ($request_type == 'SSL'? 'https' : 'http'));
                            header('HTTP/1.1 302 Moved Temporarily');
                            header('Location: ' . $url);
                            exit();
                        }
                    }
                }
            }
            language::redirectToLocale((int)$languages_id, $countryCode, $lng, $request, $request_type);
        }
        
        global $cPath_array, $current_category_id, $cPath;
        
        if (isset($_GET['cPath'])) {
          $cPath = $_GET['cPath'];
        } elseif (isset($_GET['products_id']) && !isset($_GET['manufacturers_id'])) {
          $cPath = \common\helpers\Product::get_product_path($_GET['products_id']);
        } else {
          $cPath = '';
        }

        if (tep_not_null($cPath)) {
          $cPath_array = \common\helpers\Categories::parse_category_path($cPath);
          $cPath = implode('_', $cPath_array);
          $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
          $query = tep_db_query("select * from " . TABLE_CATEGORIES . " where categories_status = 1 and categories_id = " . (int)$current_category_id);
          if (tep_db_num_rows($query) == 0) {
            $current_category_id = 0;
          }
        } else {
          $current_category_id = 0;
        }    
  
        $seo_path = trim($request->getPathInfo());
        if ( $seo_path=='403' || strpos($seo_path, '.')===0 ){
            return ['index/error-forbidden', []];
        }
        // init cannonical uri
        MetaCannonical::instance($seo_path);

        $settings = Yii::$app->getUrlManager()->getSettings();
        if ( $settings['search_engine_friendly_urls'] && $settings['search_engine_unhide'] ) {
            
            //$seo_path = str_replace(DIR_WS_HTTP_CATALOG, "", $request->getUrl());
            if (!tep_not_null($seo_path)) {
                return false;
            }
            if($seo_path === 'sitemap.xml'){
                return ['xmlsitemap/index',[]];
            }
            if($seo_path === 'manifest.json'){
                return ['manifest/index',[]];
            }

            $onlyListingProducts = '';
            if (defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' && !\frontend\design\Info::isTotallyAdmin()) {
                $onlyListingProducts = " and p.is_listing_product=1 ";
            }
            $product = tep_db_fetch_array(tep_db_query(
              "select pd.products_id from " . TABLE_PRODUCTS_DESCRIPTION . " pd inner join " . TABLE_PRODUCTS . " p on p.products_status=1 {$onlyListingProducts} and p.products_id = pd.products_id  where pd.products_seo_page_name  = '" . tep_db_input($seo_path) . "' and pd.platform_id='".intval(\common\classes\platform::currentId())."' and pd.language_id = '" . (int)$languages_id . "' ORDER BY products_status DESC  limit 1 "
            ));
            if ( !is_array($product) ) {
              $product = tep_db_fetch_array(tep_db_query(
                "select p.products_id from " . TABLE_PRODUCTS . " p where p.products_seo_page_name = '" . tep_db_input($seo_path) . "' {$onlyListingProducts} limit 1"
              ));
            }
            if (isset($product['products_id']) && $product['products_id'] > 0) {
              \common\helpers\Product::redirectIfInactive($product['products_id']);
                global $products_id;
                $products_id = $_GET['products_id'] = $product['products_id'];
                $cPath_array = explode("_", \common\helpers\Product::get_product_path($product['products_id']));
                return ['catalog/product', ['products_id' => $product['products_id']]];
            }
// {{
            $seo_path_array = explode('/', $seo_path);
            $properties_array = $brands_array = $categories_array = array();
            foreach (explode('/', $seo_path) as $seo_path_key => $seo_path_part) {
                if (defined('SEO_URL_SALES_ONLY_FILTER_KEY') && SEO_URL_SALES_ONLY_FILTER_KEY != '' && $seo_path_part == SEO_URL_SALES_ONLY_FILTER_KEY) {
                    // Sales Only Filter
                    $param_key = 'salesOnly';
                    $_GET[$param_key] = $properties_array[$param_key] = '1';
                    unset($seo_path_array[$seo_path_key]);
                    continue;
                }
                if (count($categories_array) == 0 && substr($seo_path_part, 0, 1) == '(' && substr($seo_path_part, -1) == ')') {
                    $categories_seo_array = explode(',', substr($seo_path_part, 1, -1));
                    foreach ($categories_seo_array as $categories_seo_page_name) {
                        $category = tep_db_fetch_array(tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on (c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "')  where cd.categories_seo_page_name = '" . tep_db_input($categories_seo_page_name) . "' limit 1"));
                        if (ArrayHelper::getValue($category, 'categories_id') > 0) {
                            $categories_array[] = $category['categories_id'];
                        } elseif (is_numeric($categories_seo_page_name)) {
                            $category = tep_db_fetch_array(tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_seo_page_name . "' limit 1"));
                            if (ArrayHelper::getValue($category, 'categories_id') == $categories_seo_page_name) {
                                $categories_array[] = $categories_seo_page_name;
                            }
                        }
                    }
                    if (count($categories_seo_array) == count($categories_array)) {
                        $param_key = 'cat';
                        $_GET[$param_key] = $properties_array[$param_key] = $categories_array;
                        unset($seo_path_array[$seo_path_key]);
                    } else {
                        $categories_array = array();
                    }
                }
                if (count($brands_array) == 0 && substr($seo_path_part, 0, 1) == '(' && substr($seo_path_part, -1) == ')') {
                    $brands_seo_array = explode(',', substr($seo_path_part, 1, -1));
                    foreach ($brands_seo_array as $brands_seo_name) {
                        $manufacturer = tep_db_fetch_array(tep_db_query("select m.manufacturers_id from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "')  where mi.manufacturers_seo_name = '" . tep_db_input($brands_seo_name) . "' limit 1"));
                        if (ArrayHelper::getValue($manufacturer, 'manufacturers_id') > 0) {
                            $brands_array[] = $manufacturer['manufacturers_id'];
                        } elseif (is_numeric($brands_seo_name)) {
                            $manufacturer = tep_db_fetch_array(tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$brands_seo_name . "' limit 1"));
                            if (ArrayHelper::getValue($manufacturer, 'manufacturers_id') == $brands_seo_name) {
                                $brands_array[] = $brands_seo_name;
                            }
                        }
                    }
                    if (count($brands_seo_array) == count($brands_array)) {
                        $param_key = 'brand';
                        $_GET[$param_key] = $properties_array[$param_key] = $brands_array;
                        unset($seo_path_array[$seo_path_key]);
                    } else {
                        $brands_array = array();
                    }
                }
                if (preg_match('/([^\(\)]+?)\(([^\[\]]*?)\)/', $seo_path_part, $arr)) {
                    $property = tep_db_fetch_array(tep_db_query("select p.properties_id from " . TABLE_PROPERTIES . " p left join " . TABLE_PROPERTIES_DESCRIPTION . " pd on (p.properties_id = pd.properties_id and pd.language_id = '" . (int)$languages_id . "')  where pd.properties_seo_page_name = '" . tep_db_input($arr[1]) . "' order by p.properties_id limit 1"));
                    if (isset($property['properties_id']) && $property['properties_id'] > 0) {
                        $values_array = array();
                        foreach (explode(',', $arr[2]) as $val) {
                            if (trim($val) == '') continue;
                            $value_query = tep_db_query("select values_id from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int)$property['properties_id'] . "' and (values_seo_page_name = '" . tep_db_input($val) . "' or values_id = '" . (int)$val . "') and language_id = '" . (int)$languages_id . "' order by values_id");
                            if (tep_db_num_rows($value_query) == 0 && in_array($val, ['Y','N'])) {
                                $values_array[] = $val;
                            }
                            while ($value = tep_db_fetch_array($value_query)) {
                                if ($value['values_id'] > 0) {
                                    $values_array[] = $value['values_id'];
                                }
                            }
                        }
                        $param_key = 'pr' . $property['properties_id'];
                        $_GET[$param_key] = $properties_array[$param_key] = $values_array;
                        unset($seo_path_array[$seo_path_key]);
                    }
                }
            }
            $seo_path = implode('/', $seo_path_array);
            if (trim($seo_path) == '' && count($properties_array) > 0) {
                return ['catalog/all-products', $properties_array];
            }
            if (trim($seo_path) == 'catalog/products-new' && count($properties_array) > 0) {
                return ['catalog/products-new', $properties_array];
            }
            if ($settings['seo_url_full_categories_path']) {
                $cPath_array = array();
                $seo_path_array = explode('/', $seo_path);
                foreach ($seo_path_array as $seo_path_part) {
                    $category = tep_db_fetch_array(tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' where if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name) = '" . tep_db_input($seo_path_part) . "' ORDER BY c.categories_status DESC limit 1"));
                    if ($category['categories_id'] > 0) {
                        $cPath_array[] = $category['categories_id'];
                    }
                }
                if (count($cPath_array) > 0 && count($cPath_array) == count($seo_path_array)) {
                  \common\helpers\Categories::redirectIfInactive($cPath_array[(sizeof($cPath_array)-1)]);
                    global $current_category_id, $cPath;
                    $cPath = implode('_', $cPath_array);
                    $_GET['cPath'] = $cPath;
                    $current_category_id = $cPath_array[(sizeof($cPath_array)-1)];
                    return ['catalog/index', array_merge($properties_array, ['cPath' => $cPath])];
                }
            }
// }}
            $category = tep_db_fetch_array(tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' where if(length(cd.categories_seo_page_name) > 0, cd.categories_seo_page_name, c.categories_seo_page_name) = '" . tep_db_input($seo_path) . "' ORDER BY c.categories_status DESC limit 1"));
            if (isset($category['categories_id']) && $category['categories_id'] > 0) {
              \common\helpers\Categories::redirectIfInactive($category['categories_id']);
                global $current_category_id, $cPath;
                $categories = array();
                \common\helpers\Categories::get_parent_categories($categories, $category['categories_id']);
                $categories = array_reverse($categories);
                $cPath = implode('_', $categories);
                if (tep_not_null($cPath)) $cPath .= '_';
                $cPath .= $category['categories_id'];
                $_GET['cPath'] = $cPath;
                $current_category_id = $category['categories_id'];
                return ['catalog/index', array_merge($properties_array, ['cPath' => $cPath])];
            }
            $manufacturer = tep_db_fetch_array(tep_db_query("select m.manufacturers_id from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on (m.manufacturers_id = mi.manufacturers_id and mi.languages_id = '" . (int)$languages_id . "')  where mi.manufacturers_seo_name = '" . tep_db_input($seo_path) . "' limit 1"));
            if (isset($manufacturer['manufacturers_id']) && $manufacturer['manufacturers_id'] > 0) {
                global $manufacturers_id;
                $manufacturers_id = $_GET['manufacturers_id'] = $manufacturer['manufacturers_id'];
                return ['catalog/index', array_merge($properties_array, ['manufacturers_id' => $manufacturer['manufacturers_id']])];
            }
            $information = InformationPage::findPageIdBySeo($seo_path);
            if (isset($information['information_id']) && $information['information_id'] > 0) {
                // {{ wrong language seo page name
                if ( $information['languages_id'] != $languages_id ) {
                    throw new \yii\web\UrlNormalizerRedirectException(Yii::$app->getUrlManager()->createUrl(['info/index','info_id' => $information['information_id']]), 301);
                }
                // }} wrong language seo page name

                global $information_id;
                $information_id = $_GET['info_id'] = $information['information_id'];
                return ['info/index', ['info_id' => $information['information_id']]];
            }
            /* CATALOG-PAGES START /**/
            try {
                /** @var CatalogPagesService $catalogPagesService */
                $catalogPagesService = \Yii::createObject(CatalogPagesService::class);
                $catalogPage = $catalogPagesService->parseRequest($request, (int)$languages_id, (int)\common\classes\platform::currentId());
                if ($catalogPage !== false && is_array($catalogPage)) {
                    return $catalogPage;
                }
            } catch (\Exception $e) {
                \Yii::error('Catalog Pages error parse url.' . $e->getMessage());
            }
            /* CATALOG-PAGES END /**/
//{{ old seo urls
            $href = null;
/*          // Moved to SeoRedirectsNamed
            $product = tep_db_fetch_array(tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' where p.products_old_seo_page_name = '" . tep_db_input($seo_path) . "' limit 1"));
            if ($product['products_id'] > 0) {
              $href = tep_href_link('catalog/product', 'products_id='.$product['products_id']);
            }
            $category = tep_db_fetch_array(tep_db_query("select c.categories_id from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' where c.categories_old_seo_page_name = '" . tep_db_input($seo_path) . "' limit 1"));
            if ($category['categories_id'] > 0) {
              $cPath_new = \common\helpers\Categories::get_path($category['categories_id']);
              $href = tep_href_link('catalog', $cPath_new);
            }
            $manufacturer = tep_db_fetch_array(tep_db_query("select m.manufacturers_id from " . TABLE_MANUFACTURERS . " m where m.manufacturers_old_seo_page_name = '" . tep_db_input($seo_path) . "' limit 1"));
            if ($manufacturer['manufacturers_id'] > 0) {
              $href = tep_href_link('catalog', 'manufacturers_id=' . $manufacturer['manufacturers_id']);
            }
            $information = tep_db_fetch_array(tep_db_query(
              "select information_id from " . TABLE_INFORMATION . " ".
              "where old_seo_page_name = '" . tep_db_input($seo_path) . "' ".
              " AND platform_id='".\common\classes\platform::currentId()."' ".
              "limit 1"
            ));
            if ($information['information_id'] > 0) {
              $href = tep_href_link('info', 'info_id=' . $information['information_id']);
            }
*/
            /** @var \common\extensions\Blog\Blog $ext */
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('Blog', 'allowed')) {
                $url = $ext::parseRequest($seo_path);
                if ( $url && is_array($url) ) {
                    return $url;
                }
            }elseif (\frontend\design\Info::hasBlog() && (strpos($seo_path, 'blog/') === 0 || $seo_path == 'blog')){
                $url = parse_url(Yii::$app->request->url);
                return ['blog/index', ['url_path' => substr($seo_path, 5) . (empty($url['query'])?'':'?' . $url['query'])]];
            }
          
          $custom = tep_db_fetch_array(tep_db_query("select ts.setting_value from " . TABLE_MENU_ITEMS . " mi inner join " . TABLE_THEMES_SETTINGS . " ts on ts.id=mi.theme_page_id and ts.setting_group = 'added_page' and setting_name='custom' where link = '" . tep_db_input($seo_path) . "' and mi.theme_page_id > 0 and mi.platform_id='" . \common\classes\platform::currentId() . "'"));
          if ($custom){
            return ['info/custom', ['action' => $custom['setting_value'] ]];
          }

          foreach (\common\helpers\Hooks::getList('url-rule/parse-request') as $filename) {
            $result = include($filename);
            if ($result && is_array($result)) return $result;
          }

          /* @var $ext \common\extensions\SeoRedirects\SeoRedirects*/
          if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirects', 'allowed')) {
            $ext::checkRedirect($seo_path);
          }
          /* @var $ext \common\extensions\SeoRedirectsNamed\SeoRedirectsNamed*/
          if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')) {
            $ext::checkRedirect($seo_path);
          }

          if ( $request->isGet && preg_match('#^catalog(/|/index)?$#', $seo_path) && empty($_GET) && empty($href) ) {
              $href = Yii::$app->getUrlManager()->createUrl(['index/index']);
              if ( empty($href) ) $href = '/';
          }

          if (!is_null($href)){
              header("HTTP/1.1 301 Moved Permanently"); 
              header("Location: " . $href); 
              exit();
          }
//}}
        }

        if (isset($_GET['products_id'])) {
            $relUrl = self::createUrl($manager, 'catalog/product', ['products_id' => (int)$_GET['products_id']]);
            if (!empty($relUrl)) {
                \app\components\MetaCannonical::instance()->setCannonical($relUrl);
            }
        } elseif (isset($_GET['manufacturers_id'])) {
            \app\components\MetaCannonical::instance()->setCannonical(['catalog/index', 'manufacturers_id' => (int)$_GET['manufacturers_id']]);
        } elseif (isset($_GET['info_id'])) {
            \app\components\MetaCannonical::instance()->setCannonical(['info/index', 'info_id' => (int)$_GET['info_id']]);
        } elseif (isset($_GET['cPath'])) {
            \app\components\MetaCannonical::instance()->setCannonical(['catalog/index', 'cPath' => $_GET['cPath']]);
        }


        return false;
    }
}
