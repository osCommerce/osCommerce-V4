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

namespace frontend\controllers;

use Yii;
use common\classes\Images;
use common\helpers\Affiliate;

class XmlsitemapController extends Sceleton
{

    protected $urlParams = [];
    protected $registered_map = array();
    protected $config = array(
        'GSM_OTHER_CHANGE_FREQ' => 'daily',
        'GSM_PROD_CHANGE_FREQ' => 'weekly',
        'GSM_CAT_CHANGE_FREQ' => 'weekly',
        'GSM_BRAND_CHANGE_FREQ' => 'weekly',
        'GSM_NEWS_CHANGE_FREQ' => 'weekly',
        'GSM_BLOG_CHANGE_FREQ' => 'weekly',
        'GSM_INFOPAGE_CHANGE_FREQ' => 'monthly',
        'GSM_DELIVERY_LOCATION_CHANGE_FREQ' => 'monthly',

        'GSM_OTHER_MIN_RATIO' => '0.8',
        'GSM_PROD_MIN_RATIO' => '0.7',
        'GSM_CAT_MIN_RATIO' => '0.8',
        'GSM_BRAND_MIN_RATIO' => '0.8',
        'GSM_NEWS_MIN_RATIO' => '0.8',
        'GSM_INFOPAGE_MIN_RATIO' => '0.6',
        'GSM_BLOG_MIN_RATIO' => '0.7',
        'GSM_DELIVERY_LOCATION_MIN_RATIO' => '0.5',

        'GSM_PROD_PAGE_COUNT' => '1000',
        'GSM_DELIVERY_LOCATION_PAGE_COUNT' => '5000',
    );


    function __construct($id,$module=null)
    {
        parent::__construct($id,$module);

        $this->registered_map = array(
            'index' => array(
                'index_feed' => true,
                'feedname' => 'index',
                'processedBy' => 'do_create_index',
            ),
            'other' => array(
                'feedname' => 'other',
                'processedBy' => 'do_create_other',
            ),
            'pages' => array(
                'feedname' => 'pages',
                'processedBy' => 'do_create_pages',
            ),
            'categories' => array(
                'feedname' => 'categories',
                'processedBy' => 'do_create_categories',
            ),
            'products' => array(
                'feedname' => 'products',
                'processedBy' => 'do_create_products',
            ),
            'manufacturers' => array(
                'feedname' => 'manufacturers',
                'processedBy' => 'do_create_manufacturers',
            ),
//      'news' => array(
//        'feedname' => 'news',
//        'processedBy' => 'do_create_news',
//      ),
        /*
            'images' => array(
                'feedname' => 'images',
                'processedBy' => 'do_create_images',
            ),
        */
        );
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryLocation', 'allowed')) {
            $this->registered_map['delivery-location'] = array(
                'feedname' => 'delivery-location',
                'processedBy' => 'do_create_delivery_location',
            );
        }
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('Blog', 'allowed')) {
            $this->registered_map['blog'] = array(
                'feedname' => 'blog',
                'processedBy' => 'do_create_blog',
            );
        }
        $this->layout = false;

        $this->urlParams = [];
        $urlSettings = Yii::$app->urlManager->getSettings();
        if ($urlSettings['search_engine_friendly_urls'] && $urlSettings['search_engine_unhide']){
            if ( $urlSettings['seo_url_parts_language'] ) {
                $objLanguage = new \common\classes\language();
                if ( is_array($objLanguage->paltform_languages) && count($objLanguage->paltform_languages)>0 ) {
                    $paltform_languages = array_unique(array_merge([ $objLanguage->dp_language ],$objLanguage->paltform_languages));

                    $_urlParams = $this->urlParams;
                    $this->urlParams = [];
                    foreach ($paltform_languages as $feedLanguageCode ) {
                        $copyParams = [];
                        if ( count($_urlParams)==0 ) $_urlParams = [[]];
                        foreach ( $_urlParams as $existingParams ) {
                            $existingParams['language'] = $feedLanguageCode;
                            $copyParams[] = $existingParams;
                        }
                        $this->urlParams = array_merge($this->urlParams, $copyParams);
                    }
                }
            }
            if (false && $urlSettings['seo_url_parts_currency']) {
                $currencies = \Yii::$container->get('currencies');
                $_urlParams = $this->urlParams;
                $this->urlParams = [];
                $platform_currencies = array_unique(array_merge([ $currencies->dp_currency ],$currencies->platform_currencies));
                foreach($platform_currencies as $currencyCode){
                    $copyParams = [];
                    if ( count($_urlParams)==0 ) $_urlParams = [[]];
                    foreach ( $_urlParams as $existingParams ) {
                        $existingParams['currency'] = $currencyCode;
                        $copyParams[] = $existingParams;
                    }
                    $this->urlParams = array_merge($this->urlParams, $copyParams);
                }
            }
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_XML;
        //@header("Content-Type: text/xml");
  }

  public function actionIndex()
  {
      @header("Content-Type: text/xml");

      echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
      echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
      foreach ( $this->registered_map as $registered_feed ) {
          if (isset($registered_feed['index_feed']) && $registered_feed['index_feed']) continue;
          if ( in_array($registered_feed['feedname'], ['products', 'images', 'delivery-location', 'categories', 'manufacturers']) ) {
              $feed_count = 1;
              if ( method_exists($this,$registered_feed['processedBy']) ) {
                  $feed_count = call_user_func(array($this,$registered_feed['processedBy']), 'count');
              }
              for ($i = 1; $i <= $feed_count; $i++) {
                  echo "\t" . '<sitemap>' . "\n";
                  echo "\t\t" . '<loc>' . tep_href_link('xmlsitemap/'. $registered_feed['feedname'] . ($feed_count>1?''.$i:''), '', 'NONSSL', false) . '</loc>' . "\n";
                  echo "\t\t" . '<lastmod>' . date("Y-m-d") . '</lastmod>' . "\n";
                  echo "\t" . '</sitemap>' . "\n";
              }
          } else {
              echo "\t" . '<sitemap>' . "\n";
              echo "\t\t" . '<loc>' . tep_href_link('xmlsitemap/' . $registered_feed['feedname'], '', 'NONSSL', false) . '</loc>' . "\n";
              echo "\t\t" . '<lastmod>' . date("Y-m-d") . '</lastmod>' . "\n";
              echo "\t" . '</sitemap>' . "\n";
          }
      }
      echo '</sitemapindex>';
  }

  public function actionCategories()
  {
    $this->do_create_categories();
  }

  public function actionProducts()
  {
    $page = intval(\Yii::$app->request->get('page',1));
    $this->do_create_products($page);
  }

  public function actionManufacturers()
  {
    $this->do_create_manufacturers();
  }

  public function actionOther()
  {
     $this->do_create_other();
  }

  public function actionPages()
  {
    $this->do_create_pages();
  }

  public function actionBlog()
  {
    $this->do_create_blog();
  }

  public function actionDeliveryLocation()
  {
      $page = intval(\Yii::$app->request->get('page',1));
      $this->do_create_delivery_location($page);
  }

  public function actionImages()
  {
    $page = intval(\Yii::$app->request->get('page',1));
    $this->do_create_images($page);
  }

  protected function do_create_blog()
  {
      $locations = [];
      if ($ext = \common\helpers\Acl::checkExtensionAllowed('Blog', 'allowed')) {
          $locations = $ext::getXmlSitemap([
              'changefreq' => $this->config['GSM_BLOG_CHANGE_FREQ'],
              'min_priority' => $this->config['GSM_BLOG_MIN_RATIO'],
              'loopParams' => $this->urlParams,
          ])->getLocations();
      }
      self::_gsm_urlset_start();

      foreach ($locations as $location) {
          $location['loc'] = htmlspecialchars($location['loc']);
          self::_gsm_urlset($location);
      }
      self::_gsm_urlset_stop();
  }

    protected function do_create_delivery_location( $pageNumber=0 ){
        $locations = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryLocation', 'allowed')) {
            $per_page = 100000;
            if ( (int)$this->config['GSM_DELIVERY_LOCATION_PAGE_COUNT']>0 ) $per_page = (int)$this->config['GSM_DELIVERY_LOCATION_PAGE_COUNT'];

            if ( $pageNumber!=='count' ) {
                $locations = $ext::getXmlSitemap([
                    'changefreq' => $this->config['GSM_DELIVERY_LOCATION_CHANGE_FREQ'],
                    'min_priority' => $this->config['GSM_DELIVERY_LOCATION_MIN_RATIO'],
                    'per_page' => $per_page,
                    'loopParams' => $this->urlParams,
                ])->getLocations($pageNumber);
            }else{
                return $ext::getXmlSitemap([
                    'changefreq' => $this->config['GSM_DELIVERY_LOCATION_CHANGE_FREQ'],
                    'min_priority' => $this->config['GSM_DELIVERY_LOCATION_MIN_RATIO'],
                    'per_page' => $per_page,
                    'loopParams' => $this->urlParams,
                ])->getPageCount();
            }
        }

        self::_gsm_urlset_start();

        foreach ($locations as $location) {
            $location['loc'] = htmlspecialchars($location['loc']);
            self::_gsm_urlset($location);
        }
        self::_gsm_urlset_stop();
    }


    protected function make_brand_image_set($imageFile, $manufacturers_id)
    {
        if ( empty($imageFile) || !is_file(Yii::getAlias('@webroot') . '/images/' . $imageFile) ) return false;

        $image_set = [
            'image:loc' => self::__xml_site_map_encode_image_path(\common\helpers\Media::getAlias('@webCatalogImages/'.$imageFile),'self'),
            'image:caption' => htmlspecialchars(\common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name',$manufacturers_id)),
            'image:title' => '',
        ];

        return $image_set;
    }

    protected function make_category_image_set($imageFile, $category_id, $language_id)
    {
        if ( empty($imageFile) || !is_file(Yii::getAlias('@webroot') . '/images/' . $imageFile) ) return false;

        $image_set = [
            'image:loc' => self::__xml_site_map_encode_image_path(\common\helpers\Media::getAlias('@webCatalogImages/'.$imageFile),'self'),
            'image:caption' => htmlspecialchars(\common\helpers\Categories::get_categories_name($category_id, $language_id)),
            'image:title' => '',
        ];

        return $image_set;
    }

    protected function do_create_categories( $pageNumber=0 ){
        
        \Yii::$app->get('mediaManager')->allowUrlTypeAlias('images',false);
        Yii::setAlias('@webCatalogImages', \Yii::$app->get('platform')->config()->getCatalogBaseUrl().DIR_WS_IMAGES );

        if ( $pageNumber!=='count' ) {
            self::_gsm_urlset_start('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');
        }

        $max_viewed = self::_get_max_ratio_param_in_categories('viewed');
        $onePieceRatio = ceil($max_viewed/10);

        $platform_enabled_languages = [];
        global $lng;
        foreach ($lng->paltform_languages as $allowed_lang_code) {
            if (!isset($lng->catalog_languages[$allowed_lang_code])) continue;
            $platform_enabled_languages[$allowed_lang_code] = $lng->catalog_languages[$allowed_lang_code]['id'];
        }

        $categories_join = '';
        if ( \common\classes\platform::activeId() ) {
            $categories_join .=
                " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
        }

        $categories_r = tep_db_query(
            "SELECT SQL_CALC_FOUND_ROWS c.categories_id, ".
            " GROUP_CONCAT(cdnoi.language_id SEPARATOR ',') AS no_follow_allowed_languages, ".
            " c.categories_image, c.categories_image_2, ".
            " c.last_modified, c.date_added ".
            "FROM ".TABLE_CATEGORIES." c ".
            " {$categories_join} ".
            " LEFT JOIN ".TABLE_CATEGORIES_DESCRIPTION . " cdnoi ON cdnoi.categories_id=c.categories_id AND cdnoi.affiliate_id=0 AND cdnoi.language_id IN ('".implode("','",$platform_enabled_languages)."') AND cdnoi.noindex_option!=1 ".
            "WHERE c.categories_status = 1 ".
            " AND cdnoi.categories_id IS NOT NULL ".
            "GROUP BY c.categories_id ".
            "ORDER BY c.categories_left, c.categories_id "
        );

        $total_rows_array = tep_db_fetch_array(tep_db_query("SELECT FOUND_ROWS() AS total_rows"));
        $on_page = 100000;
        if ( $pageNumber=='count' ) {
            tep_db_free_result($categories_r);
            $pages_count = ceil($total_rows_array['total_rows']/$on_page);
            return $pages_count;
        }

        while ( $categories = tep_db_fetch_array($categories_r) ) {
            $strCategoryPath = self::_get_checked_cpath( $categories['categories_id'] );
            if ( empty($strCategoryPath) ) continue;

            $locations = [];
            $images_set_per_location = [];

            if ( count($this->urlParams)==0 ) {
                $location = tep_href_link('catalog/index', $strCategoryPath, 'NONSSL', false);
                $images_set_per_location[$location] = [];
                if (!empty($categories['categories_image'])) {
                    $_image = $this->make_category_image_set($categories['categories_image'], $categories['categories_id'], 0);
                    if ( !empty($_image) ) {
                        $images_set_per_location[$location][$_image['image:loc']] = $_image;
                    }
                }
                if (!empty($categories['categories_image_2'])) {
                    $_image = $this->make_category_image_set($categories['categories_image_2'], $categories['categories_id'], 0);
                    if ( !empty($_image) ) {
                        $images_set_per_location[$location][$_image['image:loc']] = $_image;
                    }
                }
                $locations[$location] = $location;
            }else{
                foreach ($this->urlParams as $urlParam) {
                    if ( !empty($urlParam['language']) && strpos(",{$categories['no_follow_allowed_languages']},", ",{$platform_enabled_languages[$urlParam['language']]}," )===false ) continue;
                    $location = tep_href_link('catalog/index', $strCategoryPath.'&'.http_build_query($urlParam), 'NONSSL', false);

                    $images_set_per_location[$location] = [];
                    if (!empty($categories['categories_image']) && is_file(Yii::getAlias('@webroot') . '/images/' . $categories['categories_image'])) {
                        $_image = $this->make_category_image_set($categories['categories_image'], $categories['categories_id'], $platform_enabled_languages[$urlParam['language']]);
                        if ( !empty($_image) ) {
                            $images_set_per_location[$location][$_image['image:loc']] = $_image;
                        }
                    }
                    if (!empty($categories['categories_image_2']) && is_file(Yii::getAlias('@webroot') . '/images/' . $categories['categories_image_2'])) {
                        $_image = $this->make_category_image_set($categories['categories_image_2'], $categories['categories_id'], $platform_enabled_languages[$urlParam['language']]);
                        if ( !empty($_image) ) {
                            $images_set_per_location[$location][$_image['image:loc']] = $_image;
                        }
                    }
                    $locations[$location] = $location;
                }
            }

            if ( !empty($categories['last_modified']) && substr($categories['last_modified'], 0, 10)!='0000-00-00' ) {
                $lastmod = $categories['last_modified'];
            }else{
                $lastmod = $categories['date_added'];
            }

            // get ratio
            $childs_categories = self::_get_checked_cchilds( $categories['categories_id'] );
            $max_viewed_in = self::_get_max_ratio_param_in_categories( 'viewed' , $childs_categories );
            if ( (int)$max_viewed_in>0 ) {
                $ratio = max(0.1, ceil( $max_viewed_in/$onePieceRatio) / 10);
            }else{
                $ratio = 0.1;
            }
            //$ratio = max($ratio, floatval(GSM_CAT_MIN_RATIO));
            $ratio = floatval($this->config['GSM_CAT_MIN_RATIO']);

            foreach( $locations as $location ) {
                self::_gsm_image_urlset(array(
                    'loc' => htmlspecialchars($location),
                    'lastmod' => date("Y-m-d", strtotime($lastmod)),
                    'changefreq' => $this->config['GSM_CAT_CHANGE_FREQ'],
                    'priority' => number_format($ratio, 1, '.', ''),
                    'images_set' => isset($images_set_per_location[$location])?$images_set_per_location[$location]:[],
                ));
            }
        }
        self::_gsm_urlset_stop();
  }

  protected function do_create_products( $pageNumber=0 ){
      $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
      $languages_id = \Yii::$app->settings->get('languages_id');

      \Yii::$app->get('mediaManager')->allowUrlTypeAlias('images',false);
      Yii::setAlias('@webCatalogImages', \Yii::$app->get('platform')->config()->getCatalogBaseUrl().DIR_WS_IMAGES );

      $ratio_column = 'p.products_ordered'; // ordered

      $max_ratio = self::_get_max_ratio_param_in_categories('ordered');
      $onePieceRatio = ceil($max_ratio/10);

      //$childs_categories = self::_get_checked_cchilds( 0 );

      $platform_enabled_languages = [];
      global $lng;
      foreach ($lng->paltform_languages as $allowed_lang_code) {
          if (!isset($lng->catalog_languages[$allowed_lang_code])) continue;
          $platform_enabled_languages[$allowed_lang_code] = $lng->catalog_languages[$allowed_lang_code]['id'];
      }

      $products_join = '';
      $categories_join = '';
      $categories_join .= " left join ".TABLE_CATEGORIES." c ON p2c.categories_id=c.categories_id ";
      if ( \common\classes\platform::activeId() ) {
          $products_join .=
              " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ";
          $categories_join .=
              " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
      }

      $shop_products_sql = (
          "SELECT SQL_CALC_FOUND_ROWS p.products_id, p.products_last_modified, p.products_date_added, p2c.categories_id, ".
          " GROUP_CONCAT(pdnoi.language_id SEPARATOR ',') AS no_follow_allowed_languages, ".
          " ".$ratio_column." as line_ratio ".
          "FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c {$categories_join}, " . TABLE_PRODUCTS_DESCRIPTION . " pd, ".TABLE_PRODUCTS." p {$products_join} ".
          " LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION . " pdnoi ON pdnoi.products_id=p.products_id AND pdnoi.platform_id='".(int)Yii::$app->get('platform')->config()->getPlatformToDescription()."' AND pdnoi.language_id IN ('".implode("','",$platform_enabled_languages)."') AND pdnoi.noindex_option!=1 ".
          (
          (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed())?
              " LEFT JOIN " . TABLE_PRODUCTS_PRICES . " pp ".
              "ON p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' ".
              "and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)\Yii::$app->settings->get('currency_id') : 0). "' ":
              ""
          ).
          "WHERE p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' ".
          "AND p2c.products_id=p.products_id AND (p2c.categories_id=0 OR (c.categories_id IS NOT NULL AND c.categories_status=1) ) ".
          "AND pdnoi.products_id IS NOT NULL ".
          "AND p.products_status!=0  " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ".
          (Affiliate::id()>0?" and p2a.affiliate_id is not null ":'').
          (
          (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed())?
              "AND if(pp.products_group_price is null, 1, pp.products_group_price != -1) ":
              ""
          ).
          " GROUP BY p.products_id "
      );

      $on_page = 100000;
      if ( (int)$this->config['GSM_PROD_PAGE_COUNT']>0 ) $on_page = (int)$this->config['GSM_PROD_PAGE_COUNT'];

      $fetchPageNumber = max( 1, (int)$pageNumber );
      $shop_products_sql_page = $shop_products_sql . "  LIMIT ".($on_page*($fetchPageNumber-1)).", {$on_page}";

      $shop_products_r = tep_db_query( $shop_products_sql_page );

      $total_rows_array = tep_db_fetch_array(tep_db_query("SELECT FOUND_ROWS() AS total_rows"));
      if ( $pageNumber=='count' ) {
          tep_db_free_result($shop_products_r);
          $pages_count = ceil($total_rows_array['total_rows']/$on_page);
          return $pages_count;
      }

      $flush_count = 100;
      if ( tep_db_num_rows($shop_products_r)>0 ) {
          self::_gsm_urlset_start('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');

          while( $shop_products = tep_db_fetch_array($shop_products_r) ) {

              /*
              $strCategoryPath = self::_get_checked_cpath($shop_products['categories_id']);
              if (empty($strCategoryPath)) {
                  $strCategoryPath = '';
              } else {
                  $strCategoryPath .= '&';
              }
              */

              $images_set_per_location = array();

              $locations = [];
              if ( count($this->urlParams)==0 ) {
                  $location = tep_href_link(FILENAME_PRODUCT_INFO, /*$strCategoryPath .*/ 'products_id=' . $shop_products['products_id'], 'NONSSL', false);
                  $locations[$location] = $location;

                  $images_set = [];
                  foreach ( Images::getImageList($shop_products['products_id']) as $_image){
                      if ( !isset($_image['image']) || !isset($_image['image']['Large']) ) continue;
                      $uri = $_image['image']['Large']['url'];
                      if ( strpos($uri,DIR_WS_THEME_IMAGES)!==false ) continue;
                      $images_set[$uri] = array(
                          'image:loc' => self::__xml_site_map_encode_image_path($uri),
                          //'image:caption' => '',
                      );
                      if ( !empty($_image['alt']) ) {
                          $images_set[$uri]['image:caption'] = htmlspecialchars($_image['alt']);
                      }
                      if ( !empty($_image['title']) ) {
                          $images_set[$uri]['image:title'] = htmlspecialchars($_image['title']);
                      }
                  }
                  $images_set_per_location[$location] = $images_set;
              }else{
                  foreach ($this->urlParams as $urlParam) {
                      if ( !empty($urlParam['language']) && strpos(",{$shop_products['no_follow_allowed_languages']},", ",{$platform_enabled_languages[$urlParam['language']]}," )===false ) continue;
                      $location = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $shop_products['products_id'].'&'.http_build_query($urlParam), 'NONSSL', false);
                      $locations[$location] = $location;

                      if ( !empty($urlParam['language']) && !isset($images_set_per_location[$location]) ) {
                          $_param_language_id = $platform_enabled_languages[$urlParam['language']];

                          $images_set = [];

                          foreach ( Images::getImageList($shop_products['products_id'], $_param_language_id) as $_image){
                              if ( !isset($_image['image']) || !isset($_image['image']['Large']) ) continue;
                              $uri = $_image['image']['Large']['url'];
                              if ( strpos($uri,DIR_WS_THEME_IMAGES)!==false ) continue;
                              $images_set[$uri] = array(
                                  'image:loc' => self::__xml_site_map_encode_image_path($uri),
                                  //'image:caption' => '',
                              );
                              if ( !empty($_image['alt']) ) {
                                  $images_set[$uri]['image:caption'] = htmlspecialchars($_image['alt']);
                              }
                              if ( !empty($_image['title']) ) {
                                  $images_set[$uri]['image:title'] = htmlspecialchars($_image['title']);
                              }
                          }

                          if ( count($images_set)>0 ) {
                              $images_set_per_location[$location] = $images_set;
                          }
                      }
                  }
              }

              if (!empty($shop_products['products_last_modified']) && (int)$shop_products['products_last_modified'] > 0 /*substr($shop_products['products_last_modified'], 0, 10)!='0000-00-00'*/) {
                  $lastmod = $shop_products['products_last_modified'];
              } else {
                  $lastmod = $shop_products['products_date_added'];
              }

              if ((int)$shop_products['line_ratio'] > 0) {
                  $ratio = max(0.1, ceil($shop_products['line_ratio'] / $onePieceRatio) / 10);
              } else {
                  $ratio = 0.1;
              }
              //$ratio = max($ratio, floatval(GSM_PROD_MIN_RATIO));
              $ratio = floatval($this->config['GSM_PROD_MIN_RATIO']);

              foreach($locations as $location) {
                  self::_gsm_image_urlset(array(
                      'loc' => htmlspecialchars($location),
                      'lastmod' => date("Y-m-d", strtotime($lastmod)),
                      'changefreq' => $this->config['GSM_PROD_CHANGE_FREQ'],
                      'priority' => number_format($ratio, 1, '.', ''),
                      'images_set' => isset($images_set_per_location[$location])?$images_set_per_location[$location]:[],
                  ));
              }

              $flush_count--;
              if ($flush_count == 0) {
                  $flush_count = 10;
                  ob_flush();
                  flush();
              }
          }
          self::_gsm_urlset_stop();
      }else{
          self::_gsm_urlset_start();
          self::_gsm_urlset_stop();
      }

      return -1;
  }

  protected function do_create_manufacturers( $pageNumber=0 ){
    $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

    \Yii::$app->get('mediaManager')->allowUrlTypeAlias('images',false);
    Yii::setAlias('@webCatalogImages', \Yii::$app->get('platform')->config()->getCatalogBaseUrl().DIR_WS_IMAGES );
    if ( $pageNumber!=='count' ) {
        self::_gsm_urlset_start('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');
    }

    $products_join = '';
    if ( \common\classes\platform::activeId() ) {
      $products_join .=
        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
        " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }

    $get_used_manufacturers_r = tep_db_query(
      "SELECT SQL_CALC_FOUND_ROWS DISTINCT m.* ".
      "FROM ".TABLE_MANUFACTURERS." m, ".TABLE_PRODUCTS." p {$products_join} ".
      (
      (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed())?
        " LEFT JOIN " . TABLE_PRODUCTS_PRICES . " pp ".
        "ON p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' ".
        "and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)\Yii::$app->settings->get('currency_id') : 0). "' ":
        ""
      ).
      "WHERE m.manufacturers_id=p.manufacturers_id AND p.products_status!=0  " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "".
      (
      (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed())?
        "AND if(pp.products_group_price is null, 1, pp.products_group_price != -1) ":
        ""
      ).
      (Affiliate::id()>0?" and p2a.affiliate_id is not null ":'')
    );

    $total_rows_array = tep_db_fetch_array(tep_db_query("SELECT FOUND_ROWS() AS total_rows"));
    $on_page = 100000;
    if ( $pageNumber=='count' ) {
        tep_db_free_result($get_used_manufacturers_r);
        $pages_count = ceil($total_rows_array['total_rows']/$on_page);
        return $pages_count;
    }

    while( $used_manufacturers = tep_db_fetch_array($get_used_manufacturers_r) ) {
        $locations = [];
        $images_set_per_location = [];
        if ( count($this->urlParams)==0 ) {
            $location = tep_href_link('catalog/index', 'manufacturers_id='.$used_manufacturers['manufacturers_id'], 'NONSSL',false);
            $locations[$location] = $location;
            $_image = $this->make_brand_image_set($used_manufacturers['manufacturers_image'], $used_manufacturers['manufacturers_id']);
            if ( !empty($_image) ) {
                $images_set_per_location[$location][$_image['image:loc']] = $_image;
            }
        }else{
            foreach ($this->urlParams as $urlParam) {
                $location = tep_href_link('catalog/index', 'manufacturers_id='.$used_manufacturers['manufacturers_id'].'&'.http_build_query($urlParam), 'NONSSL',false);
                $_image = $this->make_brand_image_set($used_manufacturers['manufacturers_image'], $used_manufacturers['manufacturers_id']);
                if ( !empty($_image) ) {
                    $images_set_per_location[$location][$_image['image:loc']] = $_image;
                }
                $locations[$location] = $location;
            }
        }

        if ( !empty($used_manufacturers['last_modified']) && substr($used_manufacturers['last_modified'], 0, 10)!='0000-00-00' ) {
            $lastmod = $used_manufacturers['last_modified'];
        }else{
            $lastmod = $used_manufacturers['date_added'];
        }
        if ( $lastmod<2000 ) { $lastmod = date('Y-m-d'); }

        foreach ($locations as $location) {
            self::_gsm_image_urlset(array(
                'loc' => htmlspecialchars($location),
                'lastmod' => date("Y-m-d", strtotime($lastmod)),
                'changefreq' => $this->config['GSM_BRAND_CHANGE_FREQ'],
                'priority' => number_format($this->config['GSM_BRAND_MIN_RATIO'], 1, '.', ''),
                'images_set' => isset($images_set_per_location[$location])?$images_set_per_location[$location]:[],
            ));
        }
    }

    self::_gsm_urlset_stop();

  }

  protected function do_create_other($pageNumber=0)
  {
      self::_gsm_urlset_start();

      $location_ratio = 1;
      if ( count($this->urlParams)==0 ) {
          $location = Yii::$app->urlManager->createAbsoluteUrl('/', 'http');
          self::_gsm_urlset(array(
              'loc' => htmlspecialchars($location),
              'lastmod' => date("Y-m-d"),
              'changefreq' => $this->config['GSM_OTHER_CHANGE_FREQ'],
              'priority' => number_format(max($this->config['GSM_OTHER_MIN_RATIO'],$location_ratio), 1, '.', ''),
          ));
      }else{
          foreach ($this->urlParams as $urlParam){
              $urlParam[0] = '/';
              $location = Yii::$app->urlManager->createAbsoluteUrl($urlParam, 'http');
              self::_gsm_urlset(array(
                  'loc' => htmlspecialchars($location),
                  'lastmod' => date("Y-m-d"),
                  'changefreq' => $this->config['GSM_OTHER_CHANGE_FREQ'],
                  'priority' => number_format(max($this->config['GSM_OTHER_MIN_RATIO'],$location_ratio), 1, '.', ''),
              ));

          }
      }

      self::_gsm_urlset_stop();
  }

  protected function do_create_pages( $pageNumber=0 ){
      $languages_id = \Yii::$app->settings->get('languages_id');

      $platform_enabled_languages = [];
      global $lng;
      foreach ($lng->paltform_languages as $allowed_lang_code) {
          if (!isset($lng->catalog_languages[$allowed_lang_code])) continue;
          $platform_enabled_languages[$allowed_lang_code] = $lng->catalog_languages[$allowed_lang_code]['id'];
      }

      self::_gsm_urlset_start();

      $sql_query = tep_db_query(
          "SELECT i.information_id, i.languages_id, ".
          " GROUP_CONCAT(pdnoi.languages_id SEPARATOR ',') AS no_follow_allowed_languages, ".
          " if(length(i1.info_title), i1.info_title, i.info_title) as info_title, ".
          " i.page, i.page_type, i.scope ".
          "FROM " . TABLE_INFORMATION ." i ".
          "  LEFT JOIN " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id and i1.languages_id = '". $languages_id . "' and i1.affiliate_id = '" . Affiliate::id() . "' ".(\common\classes\platform::activeId()?" AND i1.platform_id='".\common\classes\platform::currentId()."' ":'')." ".
          "  LEFT JOIN ".TABLE_INFORMATION . " pdnoi ON pdnoi.information_id=i.information_id AND pdnoi.affiliate_id=0 AND pdnoi.platform_id=i.platform_id AND pdnoi.languages_id IN ('".implode("','",$platform_enabled_languages)."') AND pdnoi.noindex_option!=1 ".
          "WHERE IFNULL(i1.visible,i.visible)='1' and i.hide_on_xml='0' and i.languages_id ='".$languages_id."' and i.affiliate_id = 0 ".(\common\classes\platform::activeId()?" AND i.platform_id='".\common\classes\platform::currentId()."' ":'')." ".
          "  AND pdnoi.information_id IS NOT NULL ".
          "GROUP BY i.information_id ".
          "ORDER BY i.v_order"
      );

      while ($row = tep_db_fetch_array($sql_query)){
          $locations = [];
          if ( count($this->urlParams)==0 ) {
              $locations[] = tep_href_link(FILENAME_INFORMATION, 'info_id=' . $row['information_id'], 'NONSSL', false);
          }else{
              foreach ($this->urlParams as $urlParam) {
                  if ( !empty($urlParam['language']) && strpos(",{$row['no_follow_allowed_languages']},", ",{$platform_enabled_languages[$urlParam['language']]}," )===false ) continue;
                  $location = tep_href_link(FILENAME_INFORMATION, 'info_id=' . $row['information_id'].'&'.http_build_query($urlParam), 'NONSSL', false);
                  $locations[$location] = $location;
              }
          }

          foreach ($locations as $location) {
              self::_gsm_urlset(array(
                  'loc' => htmlspecialchars($location),
                  'lastmod' => date("Y-m-d"),
                  'changefreq' => $this->config['GSM_INFOPAGE_CHANGE_FREQ'],
                  'priority' => number_format($this->config['GSM_INFOPAGE_MIN_RATIO'], 1, '.', ''),
              ));
          }
      }

      self::_gsm_urlset_stop();
  }

  protected function do_create_images( $pageNumber=0 ){
    $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
    $languages_id = \Yii::$app->settings->get('languages_id');

    \Yii::$app->get('mediaManager')->allowUrlTypeAlias('images',false);
    Yii::setAlias('@webCatalogImages', \Yii::$app->get('platform')->config()->getCatalogBaseUrl().DIR_WS_IMAGES );

    if ( $pageNumber!=='count' ) {
      self::_gsm_urlset_start('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"');
      $pageNumber = max(1,intval($pageNumber));
    }

    $childs_categories = self::_get_checked_cchilds( 0 );

    $products_join = '';
    $categories_join = '';
    if ( \common\classes\platform::activeId() ) {
      $products_join .=
        " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ";
      $categories_join .=
        " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
    }

    $shop_products_sql = (
      "SELECT p.products_id, p.products_last_modified, p.products_date_added, p2c.categories_id, ".
      "p.products_image ".
      "FROM ".TABLE_PRODUCTS_TO_CATEGORIES." p2c {$categories_join}, " . TABLE_PRODUCTS_DESCRIPTION . " pd, ".TABLE_PRODUCTS." p {$products_join} ".
      (
      (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed())?
        " LEFT JOIN " . TABLE_PRODUCTS_PRICES . " pp ".
        "ON p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' ".
        "and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)\Yii::$app->settings->get('currency_id') : 0). "' ":
        ""
      ).
      "WHERE p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and pd.platform_id = '".\common\classes\platform::currentId()."' ".
      "AND p2c.products_id=p.products_id AND p2c.categories_id IN ('".implode("','", $childs_categories)."') ".
      "AND p.products_status!=0  " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "".
      (Affiliate::id()>0?" and p2a.affiliate_id is not null ":'').
      (
      (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed())?
        "AND if(pp.products_group_price is null, 1, pp.products_group_price != -1) ":
        ""
      )
    );

    $on_page = 100000;
    if ( (int)$this->config['GSM_PROD_PAGE_COUNT']>0 ) $on_page = (int)$this->config['GSM_PROD_PAGE_COUNT'];

    $used_ids = array();

    if ( $pageNumber==='count' ) {
      $shop_products_sql = preg_replace('/^SELECT(.*?)FROM/', 'SELECT COUNT(DISTINCT p.products_id) AS total FROM', $shop_products_sql);
      $shop_products_r = tep_db_query( $shop_products_sql );
      $shop_products_count = tep_db_fetch_array($shop_products_r);
      $pages_count = ceil($shop_products_count['total']/$on_page);
      return $pages_count;
    }

    $processed_products = 0;
    $output_range_low = $on_page*($pageNumber-1)+1;
    $output_range_high = $output_range_low+$on_page;

    $pass_page = 0;
    $page_fetch = min(1000,$on_page);
    do{
      $process_pages = true;
      $pass_page++;
      $shop_products_sql_page = $shop_products_sql . " GROUP BY p.products_id LIMIT ".($page_fetch*($pass_page-1)).", {$page_fetch}";

      $shop_products_r = tep_db_query( $shop_products_sql_page );

      if ( tep_db_num_rows($shop_products_r)==0 ) {
        $process_pages = false;
      }
      $flush_count = 100;
      while( $shop_products = tep_db_fetch_array($shop_products_r) ) {
        if (isset($used_ids[$shop_products['products_id']])) continue;

        $processed_products++;
        // {{
        if ($processed_products < $output_range_low) {
          // low pages
          $used_ids[$shop_products['products_id']] = $shop_products['products_id'];
          continue;
        } elseif ($processed_products >= $output_range_high) {
          // over top range. free and exit
          tep_db_free_result($shop_products_r);
          $process_pages = false;
          break;
        }
        $strCategoryPath = self::_get_checked_cpath($shop_products['categories_id']);
        if (empty($strCategoryPath)) {
          $strCategoryPath = '';
        } else {
          $strCategoryPath .= '&';
        }

        $images_set = array();

        foreach ( Images::getImageList($shop_products['products_id']) as $_image){
          if ( !isset($_image['image']) || !isset($_image['image']['Large']) ) continue;
          $uri = $_image['image']['Large']['url'];
          if ( strpos($uri,DIR_WS_THEME_IMAGES)!==false ) continue;
          $images_set[$uri] = array(
            'image:loc' => $uri,
            //'image:caption' => '',
          );
          if ( !empty($_image['title']) ) {
            $images_set[$uri]['image:caption'] = htmlspecialchars($_image['title']);
          }
        }

        if ( count($images_set)>0 ) {
          foreach( $images_set as $idx=>$images_set_patch ) {
            $images_set[$idx]['image:loc'] = self::__xml_site_map_encode_image_path($images_set_patch['image:loc']);
          }
          self::_gsm_image_urlset(array(
            'loc' => htmlspecialchars(tep_href_link(FILENAME_PRODUCT_INFO, /*$strCategoryPath . */'products_id=' . $shop_products['products_id'], 'NONSSL', false)),
            //'lastmod' => date("Y-m-d", strtotime($lastmod)),
            //'changefreq' => GSM_PROD_CHANGE_FREQ,
            'images_set' => $images_set
            //'priority' => number_format($ratio, 1, '.', ''),
          ));
        }
        $used_ids[$shop_products['products_id']] = $shop_products['products_id'];
        $flush_count--;
        if ($flush_count == 0) {
          $flush_count = 500;
          ob_flush();
          flush();
        }
      }
    }while($process_pages);

    self::_gsm_urlset_stop();

    return -1;
  }


  static protected function _gsm_urlset_start($urlset_attributes = ''){
    header("Content-type: text/xml; charset=utf-8");
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'.(empty($urlset_attributes)?'':' '.$urlset_attributes).'>' . "\n";
  }

  static protected function _gsm_urlset( $url ){
    echo utf8_encode(
      "\t<url>\n".
      "\t\t<loc>{$url['loc']}</loc>\n".
      "\t\t<lastmod>{$url['lastmod']}</lastmod>\n".
      "\t\t<changefreq>{$url['changefreq']}</changefreq>\n".
      "\t\t<priority>{$url['priority']}</priority>\n".
      "\t</url>\n"
    );
  }

  static function _gsm_image_urlset( $url ){
    $media = '';
    if (isset($url['images_set']) && is_array($url['images_set']) ) {
      foreach( $url['images_set'] as $image ) {
        $media .=
          "\t\t<image:image>\n".
          /* The URL of the image.*/
          "\t\t\t<image:loc>{$image['image:loc']}</image:loc>\n".
          ((isset($image['image:caption']) && !empty($image['image:caption']))?
            /*The caption of the image.*/
            "\t\t\t<image:caption>{$image['image:caption']}</image:caption>\n":''
          ).
          ((isset($image['image:geo_location']) && !empty($image['image:geo_location']))?
            /*The geographic location of the image. For example, "Limerick, Ireland".*/
            "\t\t\t<image:geo_location>{$image['image:geo_location']}</image:geo_location>\n":''
          ).
          ((isset($image['image:title']) && !empty($image['image:title']))?
            /*The title of the image.*/
            "\t\t\t<image:title>{$image['image:title']}</image:title>\n":''
          ).
          ((isset($image['image:license']) && !empty($image['image:license']))?
            /*A URL to the license of the image.*/
            "\t\t\t<image:license>{$image['image:license']}</image:license>\n":''
          ).
          "\t\t</image:image>\n";
      }
    }
    echo /*utf8_encode*/(
      "\t<url>\n".
      "\t\t<loc>{$url['loc']}</loc>\n".
      ((isset($url['lastmod']) && !empty($url['lastmod']))?
        "\t\t<lastmod>{$url['lastmod']}</lastmod>\n":''
      ).
      ((isset($url['changefreq']) && !empty($url['changefreq']))?
        "\t\t<changefreq>{$url['changefreq']}</changefreq>\n":''
      ).
      ((isset($url['priority']) && !empty($url['priority']))?
        "\t\t<priority>{$url['priority']}</priority>\n":''
      ).
      $media.
      "\t</url>\n"
    );
  }

  static protected function _gsm_urlset_stop(){
    echo '</urlset>';
  }

  static protected function __xml_site_map_encode_image_path($image_path, $type='none'){
      if ( $type!=='self'  ) {
          return $image_path;
      }
      $parts = explode('/', $image_path);
      $encodedParts = array_map('rawurlencode', $parts);
      $encodedParts[0] = $parts[0];
      $image_path = implode('/', $encodedParts);
      return $image_path;
  }

  static protected function _get_max_ratio_param_in_categories( $ratio_param='viewed', $categories_ids=false ) {
    $languages_id = \Yii::$app->settings->get('languages_id');

    if ( Affiliate::id()>0 ) {
      $ratio_column = 'IFNULL(pd1.products_viewed, pd.products_viewed)'; // viewed
    }else{
      $ratio_column = 'pd.products_viewed'; // viewed
    }
    if ( $ratio_param=='ordered' ) {
      $ratio_column = 'p.products_ordered'; // ordered
    }

    $max_viewed_r = tep_db_query(
        "SELECT MAX( ".$ratio_column." ) as max_ratio ".
        "FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd, ".TABLE_PRODUCTS." p ".
        ( is_array($categories_ids)?", ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ":"" ).
        "WHERE p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and pd.platform_id = '".intval(Yii::$app->get('platform')->config()->getPlatformToDescription())."' ".
        ( is_array($categories_ids)?" AND p2c.products_id=p.products_id AND p2c.categories_id IN ('".implode("','", $categories_ids)."') ":"" )
    );
    $max_viewed_arr = tep_db_fetch_array($max_viewed_r);
    return (int)$max_viewed_arr['max_ratio'];
  }

  protected static function _get_checked_cchilds($toCategoryId){
    $categoriesSet = array();
    $categoriesMap = self::_get_categories_map_tree();
    if ( isset( $categoriesMap[$toCategoryId]) && $categoriesMap[$toCategoryId]['status']!=0 ) {
      $categoriesSet[] = $toCategoryId;
      foreach( $categoriesMap[$toCategoryId]['childs'] as $child_id ) {
        $categoriesSet = array_merge( $categoriesSet, self::_get_checked_cchilds($child_id) );
      }
    }
    return $categoriesSet;
  }

  protected static function _get_checked_cpath($toCategoryId){
    $categoriesMap = self::_get_categories_map_tree();
    $categoriesPath = array();
    do{
      if ( $toCategoryId!=0 && isset( $categoriesMap[$toCategoryId] ) && $categoriesMap[$toCategoryId]['status']!=0 ) {
        $categoriesPath[] = $toCategoryId;
        $toCategoryId = $categoriesMap[$toCategoryId]['parent'];
        if ( $toCategoryId==0 ) break;
      }else{
        $categoriesPath = array();
        break;
      }
    }while(true);

    if ( count($categoriesPath)>0 ) {
      return 'cPath='.implode('_', array_reverse($categoriesPath));
    }
    return false;
  }

  static protected function _get_categories_map_tree(){
    $languages_id = \Yii::$app->settings->get('languages_id');
    static $categoriesMap = false;
    if ( $categoriesMap===false ) {
      $categoriesMap = array();
      $_entry_skel = array(
        'childs' => array(),
        'status' => 0,
        'parent' => 0,
      );

      $categories_tree_sql =
        "select c.categories_id, c.categories_seo_page_name, c.categories_status, ".
        "if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) as categories_name, c.parent_id ".
        "from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd ".
        "left join " . TABLE_CATEGORIES_DESCRIPTION .  " cd1 on cd1.categories_id = cd.categories_id and cd1.affiliate_id = '" . Affiliate::id() . "' and cd1.language_id = '" . (int)$languages_id . "' ".
        "where c.categories_id = cd.categories_id and cd.language_id='" . (int)$languages_id ."' and cd.affiliate_id=0 ";

      $get_all_r = tep_db_query($categories_tree_sql);
      while( $get_all = tep_db_fetch_array($get_all_r) ) {
        if ( !isset($categoriesMap[ $get_all['categories_id'] ]) ) {
          $categoriesMap[ $get_all['categories_id'] ] = $_entry_skel;
        }
        if ( !isset($categoriesMap[ $get_all['parent_id'] ]) ) {
          $categoriesMap[ $get_all['parent_id'] ] = $_entry_skel;
        }
        $categoriesMap[ $get_all['categories_id'] ]['status'] = (int)$get_all['categories_status'];
        $categoriesMap[ $get_all['categories_id'] ]['parent'] = (int)$get_all['parent_id'];
        $categoriesMap[ $get_all['parent_id'] ]['childs'][] = (int)$get_all['categories_id'];
      }
      if ( isset($categoriesMap[0]) ) $categoriesMap[0]['status'] = 1;
    }
    return $categoriesMap;
  }

}