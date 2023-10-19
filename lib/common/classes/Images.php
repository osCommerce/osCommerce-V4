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

namespace common\classes;
use frontend\design\Info;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\Html;

/**
 * Product Images
 *
 * @property array $data
 */
class Images {

    public const IMAGETYPES_CACHE_LIFETIME = 15;
    const watermarkPrefix = [
        'top_left_',
        'top_',
        'top_right_',
        'left_',
        '',
        'right_',
        'bottom_left_',
        'bottom_',
        'bottom_right_',
      ];

    public static function getFSCatalogImagesPath() {
        if (defined('DIR_FS_CATALOG_IMAGES')) {
            return DIR_FS_CATALOG_IMAGES;
        }
        return DIR_FS_CATALOG . DIR_WS_IMAGES;
    }

    public static function getWSCatalogImagesPath($use_cdn=false) {
        if (defined('DIR_WS_CATALOG_IMAGES')) {
            return DIR_WS_CATALOG_IMAGES;
        }
        if ($use_cdn) {
          $platform_config = \Yii::$app->get('platform')->config();
          $cdn_server = $platform_config->getImagesCdnUrl();
          if ( !empty($cdn_server) ) {
            return $cdn_server/*.DIR_WS_IMAGES*/;
          }
        }
        return /*DIR_WS_HTTP_CATALOG .*/ DIR_WS_IMAGES;
    }

    public function __construct() {
        $path = self::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR;
        $this->createFolder($path);
    }

    public static function checkAttribute($products_images_id = 0, $products_options_id = 0, $products_options_values_id = 0) {
        $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_ATTRIBUTES . " where products_images_id = '" . (int)$products_images_id  . "' and products_options_id = '" . (int)$products_options_id  . "' and products_options_values_id = '" . (int)$products_options_values_id  . "'");
        if (tep_db_num_rows($images_query) >0) {
            return true;
        }
        return false;
    }

    public static function getQuery($productsId, $limit = '') {
        static $_dummy_fetch = null;
        if (is_null($_dummy_fetch)) {
            $_dummy_fetch = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where products_id = '-1'");
        }
        $images_query = $_dummy_fetch;
        /** @var \common\extensions\InventoryImages\InventoryImages $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('InventoryImages')) {
            $images_query = $ext::getQuery($productsId, $limit);
        }
        /** @var \common\extensions\AttributesImages\AttributesImages $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('AttributesImages')) {
            $images_query = $ext::getQuery($images_query, $productsId, $limit);
        }
        /** @var \common\extensions\ProductImagesByPlatform\ProductImagesByPlatform $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('ProductImagesByPlatform')) {
            $images_query = $ext::getQuery($images_query, $productsId, $limit);
        }
        if (tep_db_num_rows($images_query) == 0) {
            $images_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES . " where image_status = 1 and products_id = '" . (int) \common\helpers\Inventory::get_prid($productsId) . "' order by default_image desc, sort_order " . $limit);
        }
        return $images_query;
    }

    public static function getImageExists($productsId = 0, $typeName = 'Thumbnail', $languageId = 0, $imageId = 0) {
        $imagePath = self::getImage($productsId, $typeName, $languageId, $imageId);
        if (empty($imagePath)) {
            return false;
        }
    }

    public static function getImageTypes($type_name=false, $all_types = false)
    {
        static $types = false;
        if ( !is_array($types) ) {
            $types = [];
            $image_types_query = tep_db_query("select * from " . TABLE_IMAGE_TYPES . ($all_types ? " where 1" : " where parent_id = '0'"));
            while ($image_types = tep_db_fetch_array($image_types_query)) {
                $image_types['folder_name'] = $image_types['image_types_x'] . 'x' . $image_types['image_types_y'];
                $types[] = $image_types;
            }
        }
        if ( $type_name!==false ) {
            foreach( $types as $type ) {
                if ( strtolower($type['image_types_name'])==strtolower($type_name) ) {
                    return $type;
                }
            }
            return false;
        }
        return $types;
    }

    private static function imageDescriptionFetch($productId, $imageId, $languageId)
    {
        static $cache = array();

        if ( !isset($cache[(int)$productId]) ) {
            $cache = array((int)$productId=>array());
            $fetch_data_r = tep_db_query(
                "SELECT pid.* ".
                "FROM ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." pid ".
                "INNER JOIN ".TABLE_PRODUCTS_IMAGES." pi ON pid.products_images_id=pi.products_images_id ".
                "WHERE pi.products_id='".(int)$productId."'"
            );
            if ( tep_db_num_rows($fetch_data_r)>0 ) {
                while( $data = tep_db_fetch_array($fetch_data_r) ){
                    $cache[(int)$productId][(int)$data['products_images_id'].'@'.(int)$data['language_id']] = $data;
                }
            }
        }

        $_key = (int)$imageId.'@'.(int)$languageId;
        return isset($cache[(int)$productId][$_key])?$cache[(int)$productId][$_key]:false;
    }

    public static function getImageList($productsId = 0, $languageId = -1, $getPath = false, $inWebp = true)
    {
        if ( $languageId<0 ) {
          $languageId = (int)\Yii::$app->settings->get('languages_id');
        }

        $images = [];

        $images_query = self::getQuery($productsId);
        while ($images_data = tep_db_fetch_array($images_query)) {

            $item = [];

            foreach( self::getImageTypes() as $image_types ) {

                $image = self::getImageUrl($productsId, $image_types['image_types_name'], $languageId, $images_data['products_images_id'], $getPath, $inWebp);
                if (!empty($image)) {

                    $item[$image_types['image_types_name']] = [
                        'url' => $image,
                        'type' => $image_types['image_types_name'],
                        'x' => $image_types['image_types_x'],
                        'y' => $image_types['image_types_y'],
                    ];
                }
            }

            $images_tags = self::getImageTags($productsId, $images_data['products_images_id'], $languageId);
            if (count($item) > 0) {
                $images[$images_data['products_images_id']] = [
                    'image' => $item,
                    'alt' => $images_tags['alt_tag'] ,
                    'title' => $images_tags['title_tag'],
                    'default' => $images_data['default_image'],
                    'sort_order' => 0,
                    'link_video_id' => $images_tags['link_video_id'],
                ];
            }
        }
        return $images;
    }

    public static function getImageTags($productsId, $imageId = 0, $languageId = -1) {
        if ($languageId < 0) {
          $languageId = (int)\Yii::$app->settings->get('languages_id');
        }
        $products = \Yii::$container->get('products');

        $result_tags = array();
        if ($imageId > 0) {
            $product_image = tep_db_fetch_array(tep_db_query("select pi.products_images_id, if(length(pid1.image_alt) > 0, pid1.image_alt, pid.image_alt) as image_alt, if(length(pid1.image_title) > 0, pid1.image_title, pid.image_title) as image_title, pid.link_video_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid left join " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid1 on pid.products_images_id = pid1.products_images_id and pid1.language_id = '" . (int) $languageId . "' where pi.products_id = '" . (int) $productsId . "' and pi.products_images_id = '" . (int) $imageId . "' and pi.products_images_id = pid.products_images_id and pid.language_id = '0'"));
        } else {
            $product_image = tep_db_fetch_array(tep_db_query("select pi.products_images_id, if(length(pid1.image_alt) > 0, pid1.image_alt, pid.image_alt) as image_alt, if(length(pid1.image_title) > 0, pid1.image_title, pid.image_title) as image_title, pid.link_video_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid left join " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid1 on pid.products_images_id = pid1.products_images_id and pid1.language_id = '" . (int) $languageId . "' where pi.products_id = '" . (int) $productsId . "' and pi.default_image = '1' and pi.products_images_id = pid.products_images_id and pid.language_id = '0'"));
        }
        $result_tags['alt_tag'] = (isset($product_image['image_alt']) ? $product_image['image_alt'] : '');
        $result_tags['title_tag'] = (isset($product_image['image_title']) ? $product_image['image_title'] : '');
        $result_tags['link_video_id'] = (isset($product_image['link_video_id']) ? $product_image['link_video_id'] : '');

        static $_product_cached = array();
        $product_cache_key = (int)$productsId.'@'.(int)$languageId;
        if ( !isset($_product_cached[$product_cache_key]) ) {
            if ( count($_product_cached)>20 ) $_product_cached = array();
            $_product_cached[$product_cache_key] = $products->getProduct($productsId);
            if (!$_product_cached[$product_cache_key]){
                $_product_cached[$product_cache_key] = tep_db_fetch_array(tep_db_query("select p.products_id, p.products_isbn, p.products_ean, p.products_asin, p.products_upc, p.manufacturers_id, if(length(pd1.products_name) > 0, pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_image_alt_tag_mask) > 0, pd1.products_image_alt_tag_mask, pd.products_image_alt_tag_mask) as products_image_alt_tag_mask, if(length(pd1.products_image_title_tag_mask) > 0, pd1.products_image_title_tag_mask, pd.products_image_title_tag_mask) as products_image_title_tag_mask from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd.products_id = pd1.products_id and pd1.platform_id = '".intval(\Yii::$app->get('platform')->config()->getPlatformToDescription())."' and pd1.language_id = '" . (int)$languageId . "' where p.products_id = '" . (int)$productsId . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)\common\helpers\Language::get_default_language_id() . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."'"));
            }
        }
        $product = $_product_cached[$product_cache_key];

        if (empty($result_tags['alt_tag']) && isset($product['products_image_alt_tag_mask'])) {
            $result_tags['alt_tag'] = $product['products_image_alt_tag_mask'];
        }
        if (empty($result_tags['title_tag']) && isset( $product['products_image_title_tag_mask'])) {
            $result_tags['title_tag'] = $product['products_image_title_tag_mask'];
        }

        static $categories_info = array();
        if (empty($result_tags['alt_tag']) || empty($result_tags['title_tag'])) {
            $categories_array = array_reverse(explode('_', \common\helpers\Product::get_product_path($productsId)));
            foreach ($categories_array as $categoriesId) {
                $key = (int)$categoriesId.'@'.(int)$languageId;
                if ( !isset($categories_info[$key]) ) {
                    if ( count($categories_info)>20 ) $categories_info = array();
                    $categories_info[$key] = tep_db_fetch_array(tep_db_query("select if(length(cd1.categories_name) > 0, cd1.categories_name, cd.categories_name) as categories_name, if(length(cd1.categories_image_alt_tag_mask) > 0, cd1.categories_image_alt_tag_mask, cd.categories_image_alt_tag_mask) as categories_image_alt_tag_mask, if(length(cd1.categories_image_title_tag_mask) > 0, cd1.categories_image_title_tag_mask, cd.categories_image_title_tag_mask) as categories_image_title_tag_mask from " . TABLE_CATEGORIES_DESCRIPTION . " cd left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd.categories_id = cd1.categories_id and cd1.affiliate_id = '0' and cd1.language_id = '" . (int) $languageId . "' where cd.categories_id = '" . (int) $categoriesId . "' and cd.language_id = '" . (int) \common\helpers\Language::get_default_language_id() . "' and cd.affiliate_id = '0'"));
                }
                $category = $categories_info[$key];

                if (empty($result_tags['alt_tag'])) {
                    $result_tags['alt_tag'] = $category['categories_image_alt_tag_mask'] ?? null;
                }
                if (empty($result_tags['title_tag'])) {
                    $result_tags['title_tag'] = $category['categories_image_title_tag_mask'] ?? null;
                }
                if (!empty($result_tags['alt_tag']) && !empty($result_tags['title_tag'])) {
                    break;
                }
            }
        }

        if (empty($result_tags['alt_tag'])) {
            $result_tags['alt_tag'] = (defined('IMAGE_ALT_TAG_MASK_PRODUCT_INFO') && tep_not_null(IMAGE_ALT_TAG_MASK_PRODUCT_INFO) ? IMAGE_ALT_TAG_MASK_PRODUCT_INFO : $product['products_name']);
        }
        if (empty($result_tags['title_tag'])) {
            $result_tags['title_tag'] = (defined('IMAGE_TITLE_TAG_MASK_PRODUCT_INFO') && tep_not_null(IMAGE_TITLE_TAG_MASK_PRODUCT_INFO) ? IMAGE_TITLE_TAG_MASK_PRODUCT_INFO : $product['products_name']);
        }

        if (strstr($result_tags['alt_tag'], '##CATEGORY_NAME##') || strstr($result_tags['title_tag'], '##CATEGORY_NAME##')) {
            $categories_array = array_reverse(explode('_', \common\helpers\Product::get_product_path($productsId)));
            $category_name = \common\helpers\Categories::get_categories_name($categories_array[0], $languageId);
            if (strstr($result_tags['alt_tag'], '##CATEGORY_NAME##')) {
                $result_tags['alt_tag'] = str_replace('##CATEGORY_NAME##', $category_name, $result_tags['alt_tag']);
            }
            if (strstr($result_tags['title_tag'], '##CATEGORY_NAME##')) {
                $result_tags['title_tag'] = str_replace('##CATEGORY_NAME##', $category_name, $result_tags['title_tag']);
            }
        }
        if (strstr($result_tags['alt_tag'], '##BRAND_NAME##') || strstr($result_tags['title_tag'], '##BRAND_NAME##')) {
            $brand_name = \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $product['manufacturers_id']);
            if (strstr($result_tags['alt_tag'], '##BRAND_NAME##')) {
                $result_tags['alt_tag'] = str_replace('##BRAND_NAME##', $brand_name, $result_tags['alt_tag']);
            }
            if (strstr($result_tags['title_tag'], '##BRAND_NAME##')) {
                $result_tags['title_tag'] = str_replace('##BRAND_NAME##', $brand_name, $result_tags['title_tag']);
            }
        }
        if (strstr($result_tags['alt_tag'], '##PRODUCT_NAME##')) {
            $result_tags['alt_tag'] = str_replace('##PRODUCT_NAME##', $product['products_name'], $result_tags['alt_tag']);
        }
        if (strstr($result_tags['title_tag'], '##PRODUCT_NAME##')) {
            $result_tags['title_tag'] = str_replace('##PRODUCT_NAME##', $product['products_name'], $result_tags['title_tag']);
        }
        if (strstr($result_tags['alt_tag'], '##EAN##')) {
            $result_tags['alt_tag'] = str_replace('##EAN##', $product['products_ean'], $result_tags['alt_tag']);
        }
        if (strstr($result_tags['title_tag'], '##EAN##')) {
            $result_tags['title_tag'] = str_replace('##EAN##', $product['products_ean'], $result_tags['title_tag']);
        }
        if (strstr($result_tags['alt_tag'], '##ASIN##')) {
            $result_tags['alt_tag'] = str_replace('##ASIN##', $product['products_asin'], $result_tags['alt_tag']);
        }
        if (strstr($result_tags['title_tag'], '##ASIN##')) {
            $result_tags['title_tag'] = str_replace('##ASIN##', $product['products_asin'], $result_tags['title_tag']);
        }
        if (strstr($result_tags['alt_tag'], '##ISBN##')) {
            $result_tags['alt_tag'] = str_replace('##ISBN##', $product['products_isbn'], $result_tags['alt_tag']);
        }
        if (strstr($result_tags['title_tag'], '##ISBN##')) {
            $result_tags['title_tag'] = str_replace('##ISBN##', $product['products_isbn'], $result_tags['title_tag']);
        }
        if (strstr($result_tags['alt_tag'], '##UPC##')) {
            $result_tags['alt_tag'] = str_replace('##UPC##', $product['products_upc'], $result_tags['alt_tag']);
        }
        if (strstr($result_tags['title_tag'], '##UPC##')) {
            $result_tags['title_tag'] = str_replace('##UPC##', $product['products_upc'], $result_tags['title_tag']);
        }
        $result_tags['alt_tag'] = strip_tags(str_replace(array("\n","\r","\r\n","\n\r"), ' ', $result_tags['alt_tag']));
        $result_tags['alt_tag'] = preg_replace('/##[A-Z_]+##/', '', $result_tags['alt_tag']);
        $result_tags['alt_tag'] = preg_replace('/\s{2,}/', ' ', $result_tags['alt_tag']);
        $result_tags['alt_tag'] = trim($result_tags['alt_tag']);
        $result_tags['title_tag'] = strip_tags(str_replace(array("\n","\r","\r\n","\n\r"), ' ', $result_tags['title_tag']));
        $result_tags['title_tag'] = preg_replace('/##[A-Z_]+##/', '', $result_tags['title_tag']);
        $result_tags['title_tag'] = preg_replace('/\s{2,}/', ' ', $result_tags['title_tag']);
        $result_tags['title_tag'] = trim($result_tags['title_tag']);

        return $result_tags;
    }

    /**
     *
     * @param int $productsId
     * @param string, array $typeName
     * @param int $languageId - if language id set to -1 then find by priority main->1->2->...
     * @param integer $imageId - if image id not set then use default image
     * @param integer $getPath - if need return url and server path
     * @return array or string
     */
    public static function getImageUrl($productsId = 0, $typeName = 'Thumbnail', $languageId = -1, $imageId = 0, $getPath = false, $inWebp = true)
    {
        if (is_array($typeName)) {
            $image_types = $typeName;
            $naImage = false;
        } else {
            $image_types = self::getImageTypes($typeName);
            if (Info::isTotallyAdmin()) {
                $naImage = 'images/na.png';
            } else {
                $naImage = Info::themeSetting('na_product', 'hide');
            }
        }
        if (!$image_types) {
            return $naImage;
        }

        if ( $languageId < 0 ) {
            $languageId = (int)\Yii::$app->settings->get('languages_id');
        }

        if ($imageId == 0) {
            $imageId = self::getImageId($productsId);
        }
        if (!$imageId) {
            return $naImage;
        }

        $productsId = \common\helpers\Inventory::get_prid($productsId);

        $images_description = static::imageDescription((int)$productsId, (int)$imageId, (int)$languageId);

        if ($images_description === false) {
            return $naImage;
        }

        if ( $images_description['use_external_images'] && is_string($typeName)) {
            $get_external_image_r = tep_db_query(
                "SELECT image_url FROM " . TABLE_PRODUCTS_IMAGES_EXTERNAL_URL . " ".
                "WHERE products_images_id = '" . (int)$images_description['products_images_id'] . "' ".
                " AND language_id='" . (int)$images_description['language_id'] . "' ".
                " AND image_types_id='" . (int)$image_types['image_types_id'] . "' "
            );
            if ( tep_db_num_rows($get_external_image_r)>0 ) {
                $_external_image = tep_db_fetch_array($get_external_image_r);
                if ( $_external_image['image_url'] ) {
                    return $_external_image['image_url'];
                }
            }
        }

        $language = '';
        if ( $images_description['language_id'] ) {
            $language = \common\classes\language::get_code($images_description['language_id']);
        }

        $path = self::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR;
        $product_image_location = $path . $productsId . DIRECTORY_SEPARATOR . $imageId . DIRECTORY_SEPARATOR;
        $image_location = $product_image_location . $image_types['folder_name'] . DIRECTORY_SEPARATOR;
        if (!empty($language)) {
            $image_location .= $language . DIRECTORY_SEPARATOR;
        }

        $imageName = $images_description['file_name'];
        if (false && !empty($images_description['alt_file_name'])) {
            $imageName = $images_description['alt_file_name'];
        }

        if (!file_exists($image_location . $imageName) && file_exists($image_location . $images_description['hash_file_name'])) {
            $imageName = $images_description['hash_file_name'];
        }
        if (file_exists($image_location . $imageName)) {
            $target_image_info = getimagesize($image_location . $imageName);
            $platformConfig = \Yii::$app->get('platform')->config();

            $publicFilenamePrefix = '';
            $productRef = 'products/'.(int)$productsId.'/';
            if ( !defined('SEO_IMAGE_URL_PARTS_NAME') && SEO_IMAGE_URL_PARTS_NAME=='True' ) {
                $productRef = \common\helpers\Product::getSeoName($productsId, $images_description['language_id']) . '/';
                if ($productRef == '/') {
                    $productRef = $productsId . '/';
                }
            }
            $publicFilenamePrefix .= $productRef;
            $publicFileName = $publicFilenamePrefix . $imageId . '/' . $image_types['folder_name'] . '/' . (!empty($language) ? $language . '/' : '') . rawurlencode($imageName);
            if ($images_description['no_watermark'] == 0 && self::useWaterMark($target_image_info[0], $target_image_info[1]) ) {
                $watermark_image = self::getWatermarkImage($platformConfig->getId(), $target_image_info[0]);
                if (is_array($watermark_image)) {
                    $watermark_mtime = 0;
                    foreach ($watermark_image as $watermark_image_path) {
                        $watermark_mtime = max($watermark_mtime, filemtime($watermark_image_path));
                    }
                } else {
                    $watermark_mtime = 0;
                }

                $publicFileName = \common\helpers\Seo::makeSlug($platformConfig->const_value('STORE_NAME')).'/'.$publicFileName;
                if ( is_file( self::getFSCatalogImagesPath().$publicFileName) ) {
                    $watermarkedImageMtimeMustBe = max(filemtime($image_location . $imageName),$watermark_mtime);
                    if ( filemtime(self::getFSCatalogImagesPath().$publicFileName)!=$watermarkedImageMtimeMustBe ){
                        @unlink(self::getFSCatalogImagesPath().$publicFileName);
                    }
                }
            }

            if ($inWebp) {
                $publicFileName = self::getWebp($publicFileName);
            }

            $imageUrl = \common\helpers\Media::getAlias('@webCatalogImages/'.$publicFileName);
            if ($getPath) {
                return [$imageUrl, $image_location . $imageName];
            } else {
                return $imageUrl;
            }
        }

        return $naImage;
    }

    public static function imageDescription($productsId, $imageId, $languageId)
    {
        $images_description = static::imageDescriptionFetch((int)$productsId, (int)$imageId, (int)$languageId);

        $images_description_default = static::imageDescriptionFetch((int)$productsId, (int)$imageId, 0);
        if (
            is_array($images_description_default)
            && (!empty($images_description_default['file_name']) or $images_description_default['use_external_images']!=0)
        ) {
            if ($images_description==false) {
                $images_description = $images_description_default;
            }else{
                foreach ( $images_description_default as $_key=>$default_value ) {
                    if ( $_key=='language_id' && (empty($images_description['file_name']) && empty($images_description['alt_file_name'])) ) {
                        $images_description[$_key] = $default_value;
                    }else
                        if ( empty($images_description[$_key]) && !empty($default_value) ) {
                            $images_description[$_key] = $default_value;
                        }
                }
            }
        }
        return $images_description;
    }

    public static function getImageId($productsId = 0)
    {
        static $_images=[];
        $uprid = \common\helpers\Inventory::normalize_id($productsId);

        if (isset($_images[$uprid])) 
           return (isset($_images[$uprid]['products_images_id']) ? $_images[$uprid]['products_images_id'] : 0);

        $images_query = self::getQuery($uprid, ' LIMIT 1');
        $images = tep_db_fetch_array($images_query);
        $_images[$uprid]=$images;
        return (isset($images['products_images_id']) ? $images['products_images_id'] : 0);
    }

    public static function getImage($productsId = 0, $typeName = 'Thumbnail', $languageId = -1, $imageId = 0, $attributes = [], $lazyLoad = false) {

        $url = self::getImageUrl($productsId, $typeName, $languageId, $imageId);

        if (!$url) {
            return false;
        }

        if ($imageId == 0) {
            $imageId = self::getImageId($productsId);
        }
        if ($imageId) {
            $images_tags = self::getImageTags($productsId, $imageId, $languageId);
        } else {
            $images_tags = [];
        }

        $srcsetSizes = self::getImageSrcsetSizes($productsId, $typeName, $languageId, $imageId);

        if ($lazyLoad) {
            if (is_array($attributes['class'])) {
                $attributes['class'][] = 'lazy';
            } else {
                $attributes['class'] .= ' lazy';
            }
            if ($srcsetSizes['srcset']) {
                $attributes['data-srcset'] = $srcsetSizes['srcset'];
            }
            if ($srcsetSizes['sizes']) {
                $attributes['data-sizes'] = $srcsetSizes['sizes'];
            }
            $attributes['data-src'] = $url;
            return \yii\helpers\Html::tag('img', '', $attributes);
        }
        return  \yii\helpers\Html::img($url, array_merge([
            'alt' => $images_tags['alt_tag'] ?? '',
            'title' => $images_tags['title_tag'] ?? '',
            'srcset' => $srcsetSizes['srcset'] ?? '',
            'sizes' => $srcsetSizes['sizes'] ?? '',
        ], $attributes));
    }

    public static function getImageSrcsetSizes($productsId = 0, $typeName = 'Thumbnail', $languageId = -1, $imageId = 0)
    {
        static $_resolutions=[];

        if (isset($_resolutions[$typeName])) 
            $resolutions=$_resolutions[$typeName];
        else {
            $resolutions = \common\models\ImageTypes::find()->where(['image_types_name' => $typeName])->asArray()
                ->cache(self::IMAGETYPES_CACHE_LIFETIME)
                ->all();
            $_resolutions[$typeName]=$resolutions;
        }

        if (!$resolutions || count($resolutions) < 2) {
            return ['srcset' => '', 'sizes' => '', 'sources' => []];
        }

        $srcset = '';
        $sizes = '';
        $sources = [];
        foreach ($resolutions as $resolution) {
            $resolution['folder_name'] = $resolution['image_types_x'] . 'x' . $resolution['image_types_y'];
            list($imageUrl, $imagePath) = self::getImageUrl($productsId, $resolution, $languageId, $imageId, true);

            if (!$imageUrl) continue;

            $size = @GetImageSize($imagePath);

            if (!$size[0]) continue;

            if ($srcset) $srcset .= ', ';

            $srcset .= $imageUrl . ' ' . $size[0] . 'w';

            if (!$resolution['width_from'] && !$resolution['width_to']) continue;

            if ($sizes) $sizes .= ', ';
            $sizes .= '(';
            $media = '';
            if ($resolution['width_from']) {
                $sizes .= 'min-width: ' . $resolution['width_from'] . 'px';
                $media .= '(min-width: ' . $resolution['width_from'] . 'px)';
            }
            if ($resolution['width_from'] && $resolution['width_to']) {
                $sizes .= ' and ';
                $media .= ' and ';
            }
            if ($resolution['width_to']) {
                $sizes .= 'max-width: ' . $resolution['width_to'] . 'px';
                $media .= '(max-width: ' . $resolution['width_to'] . 'px)';
            }
            $sizes .= ') ' . $size[0] . 'px';

            $sources[] = [
                'srcset' => $imageUrl,
                'media' => $media
            ];
        }

        return [
            'srcset' => $srcset,
            'sizes' => $sizes,
            'sources' => $sources,
        ];
    }

    /**
     * @depricated
     * @param $params
     * @return string
     */
    private static function allocateCacheKey($params)
    {
      $platform_id = (int)$params['platform_id'];
      if (is_array($params['watermark_image'])) {
          $watermark_image = serialize($params['watermark_image']);
      } else {
          $watermark_image = '';
      }
      $key_data = array(
        'platform_id' => $platform_id,
        'image_size' => isset($params['image_size'])?$params['image_size']:'',
        'watermark_image' => $watermark_image,
        'watermark_mtime' => isset($params['watermark_mtime'])?$params['watermark_mtime']:'',
      );
      $params = array_diff_key($params, $key_data);
      ksort($params);

      $key_data['extra_params'] = count($params)>0?base64_encode(serialize($params)):'';

      $internal_key = md5(implode('/',$key_data));

      static $lookup = array();
      if ( !isset($lookup[$internal_key]) ) {
        $get_external_key_r = tep_db_query(
          "SELECT external_key ".
          "FROM ".TABLE_IMAGE_CACHE_KEYS." ".
          "WHERE internal_key='{$internal_key}' AND is_valid=1 AND platform_id='{$platform_id}'"
        );
        if ( tep_db_num_rows($get_external_key_r)>0 ) {
          $_external_key = tep_db_fetch_array($get_external_key_r);
          $lookup[$internal_key] = $_external_key['external_key'];
        }else{
          do {
            $external_key = strtoupper(uniqid());
            $check_key = tep_db_fetch_array(tep_db_query(
              "SELECT COUNT(*) AS c FROM " . TABLE_IMAGE_CACHE_KEYS . " WHERE external_key='" . $external_key . "' "
            ));
          }while($check_key['c']==1);

          $key_data['external_key'] = $external_key;
          $key_data['internal_key'] = $internal_key;
          tep_db_perform(TABLE_IMAGE_CACHE_KEYS, $key_data);
          $lookup[$internal_key] = $external_key;
        }
      }
      $external_key = $lookup[$internal_key];

      return $external_key.'/';
    }

    /**
     * @depricated
     * @param $watermark_name
     * @param int $platform_id
     */
    public static function cacheKeyInvalidateByWatermark($watermark_name, $platform_id=0)
    {
      if ( !empty($watermark_name) ) {
        tep_db_query(
          "UPDATE " . TABLE_IMAGE_CACHE_KEYS . " ".
          "SET is_valid=0 ".
          "WHERE watermark_image='" . tep_db_input($watermark_name) . "' ".
          ($platform_id>0?"AND platform_id='".(int)$platform_id."' ":'')
        );
      }
    }

    /**
     * @depricated
     * @param $platform_id
     */
    public static function cacheKeyInvalidateByPlatformId($platform_id)
    {
      if ( !empty($watermark_name) ) {
        tep_db_query("UPDATE " . TABLE_IMAGE_CACHE_KEYS . " SET is_valid=0 WHERE platform_id='" . (int)$platform_id . "'");
      }
    }

    /**
     * @depricated
     * @param bool $deep_check
     */
    public static function cacheFlush($deep_check=false)
    {
        if ( $deep_check ) {
            tep_db_query("UPDATE " . TABLE_IMAGE_CACHE_KEYS . " SET is_valid=0");
        }
      /*if ( $deep_check ) {
        $get_valid_keys_r = tep_db_query(
          "SELECT * ".
          "FROM ".TABLE_IMAGE_CACHE_KEYS." ".
          "WHERE is_valid=1"
        );
        if ( tep_db_num_rows($get_valid_keys_r)>0 ) {
          while ($cache_data = tep_db_fetch_array($get_valid_keys_r)) {

          }
        }
      }*/
      $get_invalid_keys_r = tep_db_query("SELECT external_key FROM ".TABLE_IMAGE_CACHE_KEYS." WHERE is_valid=0");
      if ( tep_db_num_rows($get_invalid_keys_r)>0 ) {
        while ($invalid_key = tep_db_fetch_array($get_invalid_keys_r)) {
          $flush_dir = self::getFSCatalogImagesPath() . 'cached/'.$invalid_key['external_key'];
          \yii\helpers\FileHelper::removeDirectory($flush_dir);
          if (!is_dir($flush_dir)) {
            tep_db_query("DELETE FROM ".TABLE_IMAGE_CACHE_KEYS." WHERE external_key='".tep_db_input($invalid_key['external_key'])."'");
          }
        }
      }
    }


  public static function getTypeFromFile($file_name)
    {
      $extension = '';
      if (is_file($file_name) && $image_info = @getimagesize($file_name)){
        switch( $image_info[2] ) {
          case IMAGETYPE_GIF: $extension = 'gif'; break;
          case IMAGETYPE_JPEG: $extension = 'jpg'; break;
          case IMAGETYPE_PNG: $extension = 'png'; break;
          case IMAGETYPE_BMP: $extension = 'bmp'; break;
        }
      }
      return $extension;
    }

    // (file_exists(DIR_FS_CATALOG_IMAGES . $products['products_image']) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($products['products_image'], $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>')

    public function createImages($productsId, $imageId, $hashName, $imageName, $language = '') {
        $path = self::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR;
        $product_image_location = $path . $productsId . DIRECTORY_SEPARATOR;
        $this->createFolder($product_image_location);
        $product_image_location .= $imageId . DIRECTORY_SEPARATOR;
        $this->createFolder($product_image_location);

        $image_types_query = tep_db_query("select * from " . TABLE_IMAGE_TYPES . " order by image_types_id");
        while ($image_types = tep_db_fetch_array($image_types_query)) {
            $image_location = $product_image_location . $image_types['image_types_x'] . 'x' . $image_types['image_types_y'] . DIRECTORY_SEPARATOR;
            $this->createFolder($image_location);
            if (!empty($language)) {
                $image_location .= $language . DIRECTORY_SEPARATOR;
                $this->createFolder($image_location);
            }
            $this->createImage($product_image_location . $hashName, $image_location . $imageName, $image_types['image_types_x'], $image_types['image_types_y'], PRODUCT_IMAGE_FIELD_COLOR);
        }
    }

    /**
     * Create image
     * @param string $path
     * @param integer $width
     * @param integer $height
     * @param fields_color
     */
    public function createImage($source_image, $destination_image, $width, $height, $fields_color = false) {
        return self::tep_image_resize($source_image, $destination_image, $width, $height, $fields_color);
    }

    /**
     * Create folder
     * @param string $path
     */
    public function createFolder($path) {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
            @chmod($path,0777);
        }
    }

    public static function tep_image_resize($image, $t_location, $thumbnail_width, $thumbnail_height, $fields_color = false) {
        if (!$thumbnail_width || !$thumbnail_height) {
            return false;
        }
        $image = str_replace("/./", "/", str_replace("//", "/", $image));
        $t_location = str_replace("/./", "/", str_replace("//", "/", $t_location));
        $size = @GetImageSize($image);
        if (($thumbnail_width >= $size[0]) && ($thumbnail_height >= $size[1])) {
            if ($image != $t_location) {
                @copy($image, $t_location);
                @chmod($t_location,0666);
            }
            return true;
        }
        if (IMAGE_RESIZE != 'GD' && IMAGE_RESIZE != 'ImageMagick') {
            return false;
        }
        if (IMAGE_RESIZE == 'ImageMagick') {
            if (is_executable(CONVERT_UTILITY)) {
                @\common\helpers\Php::exec(CONVERT_UTILITY . ' -thumbnail ' . $thumbnail_width . 'x' . $thumbnail_height . ' ' . $image . ' ' . $t_location);
                @chmod($t_location,0666);
                return true;
            }
            return false;
        } elseif (IMAGE_RESIZE == 'GD') {
            if (function_exists("gd_info")) {

                $scale = @min($thumbnail_width / $size[0], $thumbnail_height / $size[1]);
                $x = $size[0] * $scale;
                $y = $size[1] * $scale;

                if ($fields_color) {
                    $newWidth = $thumbnail_width;
                    $newHeight = $thumbnail_height;
                    if ($thumbnail_width - $x < $thumbnail_height - $y){
                        $newTop = ($thumbnail_height - $y) / 2;
                        $newLeft = 0;
                    } else {
                        $newTop = 0;
                        $newLeft = ($thumbnail_width - $x) / 2;
                    }
                    $fields_color = str_replace(' ', '', $fields_color);
                    if (preg_match("/^rgb\(([0-9]{1,3})\,([0-9]{1,3})\,([0-9]{1,3})\)$/i", $fields_color, $matches)) {
                        $bgColorR = $matches[1];
                        $bgColorG = $matches[2];
                        $bgColorB = $matches[3];
                    } elseif (preg_match("/^rgba\(([0-9]{1,3})\,([0-9]{1,3})\,([0-9]{1,3}),([0-9\.]{1,5})\)$/i", $fields_color, $matches)) {
                        $bgColorR = $matches[1];
                        $bgColorG = $matches[2];
                        $bgColorB = $matches[3];
                    } elseif (preg_match("/^\#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i", $fields_color, $matches)) {
                        $bgColorR = hexdec($matches[1]);
                        $bgColorG = hexdec($matches[2]);
                        $bgColorB = hexdec($matches[3]);
                    } elseif (preg_match("/^\#([0-9a-f])([0-9a-f])([0-9a-f])$/i", $fields_color, $matches)) {
                        $bgColorR = hexdec($matches[1] . $matches[1]);
                        $bgColorG = hexdec($matches[2] . $matches[2]);
                        $bgColorB = hexdec($matches[3] . $matches[3]);
                    } else {
                        $bgColorR = 255;
                        $bgColorG = 255;
                        $bgColorB = 255;
                    }
                } else {
                    $newWidth = $x;
                    $newHeight = $y;
                    $newTop = 0;
                    $newLeft = 0;
                    $bgColorR = 255;
                    $bgColorG = 255;
                    $bgColorB = 255;
                }
                $bgColorA = 0;

                switch ($size[2]) {
                    case 18:
                        $im = @imagecreatefromwebp($image);
                        break;
                    case 1 : // GIF
                        $im = @ImageCreateFromGif($image);
                        break;
                    case 3 : // PNG
                        $im = @ImageCreateFromPng($image);
                        if ($im) {
                            if (function_exists('imageAntiAlias')) {
                                @imageAntiAlias($im, true);
                            }
                            @imageAlphaBlending($im, true);
                            @imageSaveAlpha($im, true);
                        }
                        break;
                    case 2 : // JPEG
                        $im = @ImageCreateFromJPEG($image);
                        break;
                    case 8 : // webp
                        $im = @imagecreatefromwebp($image);
                        break;
                    default :
                        return false;
                }

                if (!$im) {
                    return false;
                }

                if(function_exists("exif_read_data") && $size[2] == 2){
                    $exif = @exif_read_data($image);
                    if(!empty($exif['Orientation'])) {
                        switch($exif['Orientation']) {
                            case 8:
                                $im = imagerotate($im,90,0);
                                break;
                            case 3:
                                $im = imagerotate($im,180,0);
                                break;
                            case 6:
                                $im = imagerotate($im,-90,0);
                                break;
                        }
                    }
                }

                $imPic = 0;
                if (function_exists('ImageCreateTrueColor'))
                    $imPic = @ImageCreateTrueColor($newWidth, $newHeight);
                if ($imPic == 0)
                    $imPic = @ImageCreate($newWidth, $newHeight);
                if ($imPic != 0) {
                    @ImageInterlace($imPic, 1);
                    if (function_exists('imageAntiAlias')) {
                        @imageAntiAlias($imPic, true);
                    }
                    @imagealphablending($imPic, false);
                    @imagesavealpha($imPic, true);
                    $transparent = @imagecolorallocatealpha($imPic, $bgColorR, $bgColorG, $bgColorB, $bgColorA);
                    for ($i = 0; $i < $newWidth; $i++) {
                        for ($j = 0; $j < $newHeight; $j++) {
                            @imageSetPixel($imPic, $i, $j, $transparent);
                        }
                    }
                    if (function_exists('ImageCopyResampled')) {
                        $resized = @ImageCopyResampled($imPic, $im, $newLeft, $newTop, 0, 0, $x, $y, $size[0], $size[1]);
                    }
                    if (!$resized) {
                        @ImageCopyResized($imPic, $im, $newLeft, $newTop, 0, 0, $x, $y, $size[0], $size[1]);
                    }
                } else {
                    return false;
                }

                if ($size[2] == 3) {
                    @imagePNG($imPic, $t_location, 9);
                } else {
                    @imageJPEG($imPic, $t_location, 85);
                }
                if (is_file($t_location)) {
                    chmod($t_location,0666);
                    return true;
                }
            }
        }
        return false;
    }

    public static function getPlatformWatermarks($platform_id=false){
      if (DEMO_STORE == 'true') {
        return [
          'watermark300' => 'demo300.png',
          'watermark170' => 'demo170.png',
          'watermark30' => 'demo30.png',
          ];
      }
      static $cached = array();
      $platform_id = ((int)$platform_id>0)?(int)$platform_id:(int)PLATFORM_ID;
      if ( !isset($cached[$platform_id]) ) {
        $cached[$platform_id] = false;
        $check_watermark_query = tep_db_query("SELECT * FROM " . TABLE_PLATFORMS_WATERMARK . " WHERE status=1 AND platform_id='{$platform_id}'");
        if ( tep_db_num_rows($check_watermark_query)>0 ) {
          $cached[$platform_id] = tep_db_fetch_array($check_watermark_query);
        }
      }
      return $cached[$platform_id];
    }

    public static function getWatermarkImage($platform_id, $baseWidth=0){
      $watermarkData = self::getPlatformWatermarks(PLATFORM_ID);
      if ( $watermarkData===false ) return false;

      if ($baseWidth > 299) {
        $watermarkName = 'watermark300';
      } elseif ($baseWidth > 169) {
        $watermarkName = 'watermark170';
      } else {
        $watermarkName = 'watermark30';
      }

      $watermarkFilenames = [];
      foreach (self::watermarkPrefix as $prefix) {
          if (isset($watermarkData[$prefix . $watermarkName]) && !empty($watermarkData[$prefix . $watermarkName])) {
                $watermark_filename = self::getFSCatalogImagesPath() . 'stamp' . DIRECTORY_SEPARATOR . $watermarkData[$prefix . $watermarkName];
                if (is_file($watermark_filename)) {
                  $watermarkFilenames[$prefix] =  $watermark_filename;
                }
          }
      }
      if (count($watermarkFilenames) > 0)  {
          return $watermarkFilenames;
      }
      return false;
    }

    public static function useWaterMark($base_width=0, $base_height=0) {
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
// {{
        if (method_exists(\Yii::$app->request, 'get') AND (\Yii::$app->request->get('nowatermark') == 1)) {
            return false;
        }
// }}
        if (!defined('PLATFORM_ID')) {
            return false;
        }

        if (DEMO_STORE == 'true') {
            return true;
        }

        $watermark_image = self::getWatermarkImage(PLATFORM_ID, $base_width);
        if ($watermark_image === false) {
          return false;
        }

        if ($customer_groups_id > 0) {
            static $group_wm_status = array();
            if ( !isset($group_wm_status[(int)$customer_groups_id]) ) {
              $group_wm_status[(int)$customer_groups_id] = true;
              $groups_check = tep_db_fetch_array(tep_db_query(
                "select count(*) as wm_status from " . TABLE_GROUPS . " where groups_id = '" . (int)$customer_groups_id . "' AND disable_watermark=1"
              ));
              if ( $groups_check['wm_status']>0 ) {
                $group_wm_status[(int)$customer_groups_id] = false;
              }
            }
            if ( !$group_wm_status[(int)$customer_groups_id] ) {
                return false;
            }
        }
        /*if (CONFIG_IMAGE_WATERMARK != 'true') {
            return false;
        }*/

        return true;
    }

    public static function applyWatermark($source_image, $watermark_image, $output_file=null)
    {
      $size = @GetImageSize($source_image);

      $output_as = 'png';

      switch ($size[2]) {
        case 1 : // GIF
          $im = @ImageCreateFromGif($source_image);
          break;
        case 3 : // PNG
          $im = @ImageCreateFromPng($source_image);
          if ($im) {
            if (function_exists('imageAntiAlias')) {
              @imageAntiAlias($im, true);
            }
            @imageAlphaBlending($im, true);
            @imageSaveAlpha($im, true);
          }
          break;
        case 2 : // JPEG
          $im = @ImageCreateFromJPEG($source_image);
          $output_as = 'jpg';
          break;
        default :
          return false;
      }

      if (is_array($watermark_image)) {
          foreach ($watermark_image as $watermarkPosition => $watermarkImage) {
                $stamp = @imagecreatefrompng($watermarkImage);
                if ($stamp) {
                    switch ($watermarkPosition) {
                        case 'top_left_':
                            imagecopy($im, $stamp, 0, 0, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'top_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)) / 2, 0, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'top_right_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)), 0, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'left_':
                            imagecopy($im, $stamp, 0, (imagesy($im) - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case '':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)) / 2, (imagesy($im) - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'right_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)), (imagesy($im) - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'bottom_left_':
                            imagecopy($im, $stamp, 0, (imagesy($im) - imagesy($stamp)), 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'bottom_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)) / 2, (imagesy($im) - imagesy($stamp)), 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        case 'bottom_right_':
                            imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)), (imagesy($im) - imagesy($stamp)), 0, 0, imagesx($stamp), imagesy($stamp));
                            break;
                        default:
                            break;
                    }
                }
          }
      } elseif ( !empty($watermark_image) ) {
        $stamp = @imagecreatefrompng($watermark_image);
        if ($stamp) {
          imagecopy($im, $stamp, (imagesx($im) - imagesx($stamp)) / 2, (imagesy($im) - imagesy($stamp)) / 2, 0, 0, imagesx($stamp), imagesy($stamp));
          //imagecopymerge($im, $stamp, (imagesx($im) - imagesx($stamp))/2, (imagesy($im) - imagesy($stamp))/2, 0, 0, imagesx($stamp), imagesy($stamp), 10);
        }
      }

      if ( is_null($output_file) ) {
        header('Content-type: ' . $size['mime']);//image/png
      }
      if ( $output_file==='string' ) {
        ob_start();
        if ($output_as == 'jpg') {
          imagejpeg($im, null, 85);
        } else {
          imagepng($im, null, 9);
        }
        imagedestroy($im);
        return ob_get_clean();
      }else {
        if ($output_as == 'jpg') {
          imagejpeg($im, $output_file, 85);
        } else {
          imagepng($im, $output_file, 9);
        }
        imagedestroy($im);
      }
    }

    public static function waterMark($image = '') {
        $image = str_replace("/./", "/", str_replace("//", "/", $image));
        $image = DIR_FS_CATALOG . $image;
        if (!file_exists($image)) {
            return false;
        }

        $size = @GetImageSize($image);

        $watermark_image = false;
        if (self::useWaterMark($size[0]) ) {
          $watermark_image = self::getWatermarkImage(PLATFORM_ID, $size[0]);
        }
        self::applyWatermark($image, $watermark_image,'direct');

        die();
    }

    public static function encodeImageName($image_file_name)
    {
        $encodeChars = [
            '%' => '%25',
            '/' => '%2F',
            '\\' => '%5C',
            '&' => '%26',
            '+' => '%2B',
            '#' => '%23',
            ':' => '%3A',
            '>' => '%3E',
            '<' => '%3C',
            '"' => '%22',
        ];
        $image_file_name = str_replace(
            array_keys($encodeChars),
            array_values($encodeChars),
            $image_file_name
        );
        return $image_file_name;
    }
    /**
     * Create right names and resized copies from original file
     *
     * @param $productsId
     * @param $imageId
     */
    public static function normalizeImageFiles($productsId, $imageId)
    {
        if ( empty($productsId) || empty($imageId) ) return;

        $count = 0;
        $imagesDirectory = self::getFSCatalogImagesPath().'products'.DIRECTORY_SEPARATOR.(int)$productsId.DIRECTORY_SEPARATOR.(int)$imageId.DIRECTORY_SEPARATOR;
        $registeredDirectoryImages = [];

        $get_images_r = tep_db_query(
            "SELECT pi_d.products_images_id, pi_d.language_id, ".
            "  pi_d.file_name, pi_d.use_origin_image_name, ".
            "  pi_d.hash_file_name, pi_d.alt_file_name, pi_d.orig_file_name, ".
            "  p.products_model, pd.products_seo_page_name ".
            "FROM ".TABLE_PRODUCTS." p ".
            "  INNER JOIN ".TABLE_PRODUCTS_IMAGES." pi ON pi.products_id=p.products_id ".
            "  INNER JOIN ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." pi_d ON pi.products_images_id=pi_d.products_images_id ".
            "  LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON p.products_id=pd.products_id AND pd.language_id=IF(pi_d.language_id=0,".\common\classes\language::get_id(DEFAULT_LANGUAGE).",pi_d.language_id) and pd.platform_id='".intval(\common\classes\platform::defaultId())."'".
            "WHERE p.products_id='".(int)$productsId."' AND pi.products_images_id='".(int)$imageId."' ".
            "  AND (pi_d.hash_file_name!='' OR pi_d.alt_file_name!='') ".
            "ORDER BY pi_d.language_id "
        );
        if ( tep_db_num_rows($get_images_r)>0 ) {
            $main_image = false; // 0 language_id
            while($image_data = tep_db_fetch_array($get_images_r)){
                if ( !empty($image_data['hash_file_name']) && is_file($imagesDirectory.$image_data['hash_file_name']) )
                {
                    $registeredDirectoryImages[] = $image_data['hash_file_name'];
                    foreach ( self::getImageTypes(false, true) as $imageType ) {
                        $imageFilenameResized = $imagesDirectory.$imageType['folder_name'].DIRECTORY_SEPARATOR.$image_data['hash_file_name'];
                        $registeredDirectoryImages[] = $imageType['folder_name'].DIRECTORY_SEPARATOR.$image_data['hash_file_name'];
                        if( !is_file($imageFilenameResized) ) {
                            try{
                                FileHelper::createDirectory(dirname($imageFilenameResized),0777);
                            }catch (\Exception $ex){}
                            self::tep_image_resize(
                                $imagesDirectory.$image_data['hash_file_name'],
                                $imageFilenameResized,
                                $imageType['image_types_x'],
                                $imageType['image_types_y'],
                                PRODUCT_IMAGE_FIELD_COLOR
                            );
                        }
                    }
                }

                if ( $image_data['language_id']==0 && $main_image===false ) {
                    $main_image = $image_data;
                }

                $source_file = !empty($image_data['hash_file_name'])?$image_data['hash_file_name']:$main_image['hash_file_name'];

                if ( empty($source_file) || !is_file($imagesDirectory.$source_file) ) continue;

                $imageExtension = self::getTypeFromFile($imagesDirectory.$source_file);
                /* filename?
                if alternative file name existent - use it:
                else if SKU Existent - use it:
                else if SEO name Existent - use it
                else use Origin image name
                */
                $image_file_name = $image_data['orig_file_name'];
                if ( !$image_data['use_origin_image_name'] ) {
                    if (!empty($image_data['alt_file_name'])) {
                        $image_file_name = $image_data['alt_file_name'];
                    } elseif (!empty($image_data['products_model'])) {
                        $image_file_name = $image_data['products_model'] . (empty($imageExtension) ? '' : '.' . $imageExtension);
                    } elseif (!empty($image_data['products_seo_page_name'])) {
                        $image_file_name = $image_data['products_seo_page_name'] . (empty($imageExtension) ? '' : '.' . $imageExtension);
                    }
                }

                $image_file_name = self::encodeImageName($image_file_name);

                if ( $image_data['file_name']!=$image_file_name ) {
                    tep_db_query(
                        "UPDATE ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." ".
                        "SET file_name='".tep_db_input($image_file_name)."' ".
                        "WHERE products_images_id='".(int)$image_data['products_images_id']."' AND language_id='".$image_data['language_id']."' "
                    );
                }else{
                    $image_file_name = $image_data['file_name'];
                }
                if ($image_data['language_id']) {
                    $image_file_name = \common\classes\language::get_code($image_data['language_id']).DIRECTORY_SEPARATOR.$image_file_name;
                }

                if ( !empty($source_file) ) {
                    foreach (self::getImageTypes(false, true) as $imageType) {
                        $publicFilename = $imagesDirectory . $imageType['folder_name'] . DIRECTORY_SEPARATOR . $image_file_name;
                        $registeredDirectoryImages[] = $imageType['folder_name'] . DIRECTORY_SEPARATOR . $image_file_name;

                        $pos = strripos($image_file_name, '.');
                        $fileName = substr($image_file_name, 0, $pos);
                        $registeredDirectoryImages[] = $imageType['folder_name'] . DIRECTORY_SEPARATOR . $fileName . '.webp';

                        if ( is_link($publicFilename) ){
                            $existingLinkPointTo = readlink($publicFilename);
                            if ( basename($existingLinkPointTo)!=$source_file ) {
                                unlink($publicFilename);
                                \common\helpers\System::symlink($imagesDirectory.$imageType['folder_name'] . DIRECTORY_SEPARATOR.$source_file, $publicFilename);
                            }
                        }elseif ( is_file($publicFilename) ){

                        }else{
                            \common\helpers\System::symlink($imagesDirectory.$imageType['folder_name'] . DIRECTORY_SEPARATOR.$source_file, $publicFilename);
                        }
                    }
                }

                $img = false;
                foreach ( self::getImageTypes(false, true) as $imageType ) {
                    $imName = 'products' . DIRECTORY_SEPARATOR
                        .(int)$productsId . DIRECTORY_SEPARATOR
                        .(int)$imageId . DIRECTORY_SEPARATOR
                        .$imageType['folder_name'] . DIRECTORY_SEPARATOR
                        .$image_file_name;

                    $webp = self::createWebp($imName);
                    if ($webp) {
                        $img = true;
                    }
                }
                if ($img) {
                    $count++;
                }
            }
        }
        self::cleanProductImageDirectory($productsId, $imageId, $registeredDirectoryImages);
        return $count;
    }

    /**
     *
     * @param $productsId
     * @param $imageId
     * @param bool $registeredDirectoryImages
     * @return array
     */
    public static function cleanProductImageDirectory($productsId, $imageId, $registeredDirectoryImages=false)
    {
        $removedFiles = [];
        $imagesDirectory = self::getFSCatalogImagesPath().'products'.DIRECTORY_SEPARATOR.(int)$productsId.DIRECTORY_SEPARATOR.(int)$imageId.DIRECTORY_SEPARATOR;
        if ( !is_dir($imagesDirectory) ) return $removedFiles;
        if ( !is_array($registeredDirectoryImages) ) {
            $registeredDirectoryImages = [];

            $get_images_r = tep_db_query(
                "SELECT pi_d.products_images_id, pi_d.language_id, ".
                "  pi_d.file_name, pi_d.use_origin_image_name, ".
                "  pi_d.hash_file_name, pi_d.alt_file_name, pi_d.orig_file_name, ".
                "  p.products_model, pd.products_seo_page_name ".
                "FROM ".TABLE_PRODUCTS." p ".
                "  INNER JOIN ".TABLE_PRODUCTS_IMAGES." pi ON pi.products_id=p.products_id ".
                "  INNER JOIN ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." pi_d ON pi.products_images_id=pi_d.products_images_id ".
                "  LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON p.products_id=pd.products_id AND pd.language_id=IF(pi_d.language_id=0,".\common\classes\language::get_id(DEFAULT_LANGUAGE).",pi_d.language_id) and platform_id='".intval(\common\classes\platform::defaultId())."' ".
                "WHERE p.products_id='".(int)$productsId."' AND pi.products_images_id='".(int)$imageId."' ".
                "  AND (pi_d.hash_file_name!='' OR pi_d.alt_file_name!='') ".
                "ORDER BY pi_d.language_id "
            );
            if ( tep_db_num_rows($get_images_r)>0 ) {
                while($image_data = tep_db_fetch_array($get_images_r)) {
                    $registeredDirectoryImages[] = $image_data['hash_file_name'];
                    foreach (self::getImageTypes(false, true) as $imageType) {
                        $image_file_name = $image_data['file_name'];
                        if ($image_data['language_id']) {
                            $image_file_name = \common\classes\language::get_code($image_data['language_id']).DIRECTORY_SEPARATOR.$image_file_name;
                        }
                        $registeredDirectoryImages[] = $imageType['folder_name'] . DIRECTORY_SEPARATOR . $image_file_name;
                        $registeredDirectoryImages[] = $imageType['folder_name'] . DIRECTORY_SEPARATOR . $image_data['hash_file_name'];

                        $pos = strripos($image_file_name, '.');
                        $fileName = substr($image_file_name, 0, $pos);
                        $registeredDirectoryImages[] = $imageType['folder_name'] . DIRECTORY_SEPARATOR . $fileName . '.webp';
                    }
                }
            }
        }
        $registeredDirectoryImages = array_flip($registeredDirectoryImages);
        foreach( FileHelper::findFiles($imagesDirectory) as $fileInDir)
        {
            $checkFile = ltrim(substr($fileInDir,strlen($imagesDirectory)),'/');
            if ( !isset($registeredDirectoryImages[$checkFile]) )
            {
                $removedFiles[] = $fileInDir;
                @unlink($fileInDir);
            }
        }
        return $removedFiles;
    }

    public static function removeProductImages($productId)
    {
        $productImagesDir = self::getFSCatalogImagesPath().'products/'.(int)$productId;
        if ( !empty($productId) && is_dir($productImagesDir) )
        {
            try {
                FileHelper::removeDirectory($productImagesDir);
            }catch (\Exception $ex){}
        }

        $schemaCheck = \Yii::$app->getDb()->schema->getTableSchema(TABLE_PRODUCTS_IMAGES_EXTERNAL_URL);
        if ( $schemaCheck ) {
            tep_db_query(
                "DELETE image_depend FROM ".TABLE_PRODUCTS_IMAGES_EXTERNAL_URL." image_depend ".
                "  INNER JOIN ".TABLE_PRODUCTS_IMAGES." image_main ON image_main.products_images_id=image_depend.products_images_id ".
                "WHERE image_main.products_id='".(int)$productId."'"
            );
        }

        tep_db_query(
            "DELETE image_depend FROM ".TABLE_PRODUCTS_IMAGES_ATTRIBUTES." image_depend ".
            "  INNER JOIN ".TABLE_PRODUCTS_IMAGES." image_main ON image_main.products_images_id=image_depend.products_images_id ".
            "WHERE image_main.products_id='".(int)$productId."'"
        );

        tep_db_query(
            "DELETE image_depend FROM ".TABLE_PRODUCTS_IMAGES_INVENTORY." image_depend ".
            "  INNER JOIN ".TABLE_PRODUCTS_IMAGES." image_main ON image_main.products_images_id=image_depend.products_images_id ".
            "WHERE image_main.products_id='".(int)$productId."'"
        );

        tep_db_query(
            "DELETE image_depend FROM ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." image_depend ".
            "  INNER JOIN ".TABLE_PRODUCTS_IMAGES." image_main ON image_main.products_images_id=image_depend.products_images_id ".
            "WHERE image_main.products_id='".(int)$productId."'"
        );

        tep_db_query(
            "DELETE FROM ".TABLE_PRODUCTS_IMAGES." ".
            "WHERE products_id='".(int)$productId."'"
        );
        self::cleanProductImageReference($productId);
    }

    public static function removeProductImage($productId, $imageId)
    {
        $productImagesDir = self::getFSCatalogImagesPath().'products/'.(int)$productId.'/'.(int)$imageId;
        try {
            FileHelper::removeDirectory($productImagesDir);
        }catch (\Exception $ex){}

        $check_remove_default = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS c ".
            "FROM ".TABLE_PRODUCTS_IMAGES." ".
            "WHERE products_images_id = '" . (int) $imageId . "' AND default_image=1 "
        ));
        if ( $check_remove_default['c'] ) {
            tep_db_query(
                "UPDATE ".TABLE_PRODUCTS_IMAGES." ".
                "SET default_image=1 ".
                "WHERE products_id='".(int)$productId."' AND products_images_id != '" . (int) $imageId . "' ".
                "ORDER BY sort_order, products_images_id ".
                "LIMIT 1"
            );
        }

        tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_EXTERNAL_URL . " where products_images_id = '" . (int) $imageId . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where products_images_id = '" . (int) $imageId . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_images_id = '" . (int) $imageId . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_ATTRIBUTES . " where products_images_id = '" . (int) $imageId . "'");
        tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_INVENTORY . " where products_images_id = '" . (int) $imageId . "'");
        self::cleanProductImageReference($productId, $imageId);
    }

    public static function removeMissingAttributesLink()
    {
        \Yii::$app->getDb()->createCommand(
            "DELETE pia FROM ".TABLE_PRODUCTS_IMAGES_ATTRIBUTES." pia ".
            "  LEFT JOIN ".TABLE_PRODUCTS_IMAGES." pi ON pi.products_images_id=pia.products_images_id ".
            "  LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES." pa on pa.products_id=pi.products_id AND pa.options_id=pia.products_options_id AND pa.options_values_id = pia.products_options_values_id ".
            "WHERE pa.products_attributes_id IS NULL"
        )->execute();
    }

    public static function collectGarbage()
    {
        $echoMessages = \Yii::$app instanceof \yii\console\Application;

        $productImagesDir = self::getFSCatalogImagesPath().'products';
        $handle = opendir($productImagesDir);
        if ( $handle ) {
            while (($productDirectory = readdir($handle)) !== false) {
                if (!is_numeric($productDirectory) || intval($productDirectory)!=$productDirectory ) continue;

                $path = $productImagesDir . DIRECTORY_SEPARATOR . $productDirectory;

                $removeDirectories = [];
                $get_product_images_ids_r = tep_db_query(
                    "SELECT p.products_id, pi.products_images_id ".
                    "FROM ".TABLE_PRODUCTS." p ".
                    "  LEFT JOIN " .TABLE_PRODUCTS_IMAGES." pi ON p.products_id=pi.products_id ".
                    "WHERE p.products_id='".(int)$productDirectory."'"
                );
                if ( tep_db_num_rows($get_product_images_ids_r)>0 ) {
                    $currentDirectories = [];
                    $subDirHandle = opendir($path);
                    if ( !$subDirHandle ) continue;
                    while (($productImageDirectory = readdir($subDirHandle)) !== false) {
                        if (!is_numeric($productImageDirectory) || intval($productImageDirectory)!=$productImageDirectory) continue;
                        $currentDirectories[(int)$productImageDirectory] = $path . DIRECTORY_SEPARATOR . $productImageDirectory;
                    }
                    closedir($subDirHandle);

                    while ( $_product_images_id = tep_db_fetch_array($get_product_images_ids_r) ) {
                        if ( isset($currentDirectories[$_product_images_id['products_images_id']]) ) {
                            unset($currentDirectories[$_product_images_id['products_images_id']]);
                        }
                    }
                    $removeDirectories = array_merge($removeDirectories, array_values($currentDirectories));
                }else{
                    // product not found - remove all
                    $removeDirectories[] = $path;
                }

                if ( count($removeDirectories)>0 ) {
                    foreach ($removeDirectories as $removeDirectoryPath) {
                        try {
                            FileHelper::removeDirectory($removeDirectoryPath);
                        }catch (\Exception $ex){}
                        if ( $echoMessages ) {
                            echo " [".(is_file($removeDirectoryPath)?Console::ansiFormat('FAIL',[Console::FG_RED]):Console::ansiFormat('OK',[Console::FG_GREEN]))."] {$removeDirectoryPath}\n";
                        }
                    }
                }

            }
            closedir($handle);
        }

        $pageSize = 1000;
        $page = 0;
        do {
            $page++;
            $get_images_page_r = tep_db_query(
                "SELECT products_id, products_images_id " .
                "FROM " . TABLE_PRODUCTS_IMAGES . " " .
                "ORDER BY products_id, products_images_id " .
                "LIMIT " . $pageSize*-($page-1) . ",{$pageSize}"
            );
            if (tep_db_num_rows($get_images_page_r) > 0) {
                while ($image = tep_db_fetch_array($get_images_page_r)) {
                    $removedList = self::cleanProductImageDirectory($image['products_id'], $image['products_images_id']);
                }
            } else {
                break;
            }
        }while(true);

    }

    protected static function removeReferenceFilename($filename)
    {
        if ( empty($filename) ) return;

        $removeFilename = \common\classes\Images::getFSCatalogImagesPath().$filename;
        if ( is_file($removeFilename) || is_link($removeFilename) ) {
            unlink($removeFilename);
            clearstatcache();
        }

        $checkEmptyDirectory = dirname($filename);
        if ($checkEmptyDirectory!='.' && $checkEmptyDirectory!='products') while($checkEmptyDirectory){
            $removeDirectory = \common\classes\Images::getFSCatalogImagesPath().$checkEmptyDirectory;
            if ( is_dir($removeDirectory) ) {
                $filesInDir = FileHelper::findFiles($removeDirectory);
                if (count($filesInDir) > 0) {
                    break;
                } else {
                    FileHelper::removeDirectory($removeDirectory);
                }
            }
            $checkEmptyDirectory = dirname($checkEmptyDirectory);
            if ( $checkEmptyDirectory=='.' || $checkEmptyDirectory=='products' ) break;
        };
    }

    public static function cleanImageReference()
    {
        $isConsole = \Yii::$app instanceof \yii\console\Application;

        $images_count = tep_db_fetch_array(tep_db_query(
            "SELECT COUNT(*) AS total FROM ".TABLE_IMAGE_COPY_REFERENCE
        ));
        if ( $images_count['total']==0 ) return;

        if ($isConsole) Console::startProgress(0,$images_count['total']);
        $processedCount = 0;
        $pageSize = 5;
        $page = 0;
        do {
            $page++;
            tep_db_query(
                "UPDATE " . TABLE_IMAGE_COPY_REFERENCE . " " .
                "SET clean_flag=1 ".
                "ORDER BY date_added " .
                "LIMIT {$pageSize}"
            );
            $get_images_page_r = tep_db_query(
                "SELECT * " .
                "FROM " . TABLE_IMAGE_COPY_REFERENCE . " " .
                "WHERE clean_flag=1 "
            );
            if (tep_db_num_rows($get_images_page_r) > 0) {
                while ($image = tep_db_fetch_array($get_images_page_r)) {
                    self::removeReferenceFilename($image['filename']);
                    if ($isConsole) Console::updateProgress(++$processedCount,$images_count['total']);
                }
                tep_db_query(
                    "DELETE FROM ".TABLE_IMAGE_COPY_REFERENCE." ".
                    "WHERE clean_flag=1 "
                );
            } else {
                break;
            }
        }while(true);
        tep_db_query(
            "OPTIMIZE TABLE ".TABLE_IMAGE_COPY_REFERENCE." "
        );
        if ($isConsole) {
            Console::endProgress(true);
            echo "Done.\n";
        }
    }

    public static function cleanProductImageReference($productId=0, $productsImageId=0)
    {
        $where = '';
        if ( $productId ) {
            $where .= "AND products_id='".(int)$productId."' ";
        }
        if ( $productsImageId ) {
            $where .= "AND products_image_id='".(int)$productsImageId."' ";
        }
        if ( $where ) {
            tep_db_query(
                "UPDATE " . TABLE_IMAGE_COPY_REFERENCE . " " .
                " SET clean_flag=1 ".
                "WHERE 1 {$where}"
            );
            $get_reference_r = tep_db_query(
                "SELECT DISTINCT filename ".
                "FROM " . TABLE_IMAGE_COPY_REFERENCE . " " .
                "WHERE clean_flag=1 {$where}"
            );
            if ( tep_db_num_rows($get_reference_r)>0 ) {
                while($reference = tep_db_fetch_array($get_reference_r)){
                    self::removeReferenceFilename($reference['filename']);
                }
            }

            tep_db_query(
                "DELETE FROM " . TABLE_IMAGE_COPY_REFERENCE . " " .
                "WHERE clean_flag=1 {$where}"
            );
        }

    }

    public static function sendImageToBrowser($imageFileName, $mimeType='')
    {
        if ( empty($mimeType) ) {
            $mimeType = 'image/png';
            $imageInfo = @GetImageSize($imageFileName);
            if ( $imageInfo && $imageInfo['mime'] ){
                $mimeType = $imageInfo['mime'];
            }
        }
        header('Content-type: '.$mimeType);
        readfile($imageFileName);

    }

    public static function calculateImageSize($imgWidth, $imgHeight, $boxWidth, $boxHeight, $fit='inside')
    {
        if ( (is_null($boxWidth) && is_null($boxHeight)) || (int)$imgWidth==0 )
        {
            return ['width'=>(int)$imgWidth, 'height'=>(int)$imgHeight];
        }

        if (!empty($boxWidth))
        {
            $rx = $imgWidth / $boxWidth;
        }
        else
            $rx = null;

        if (!empty($boxHeight))
        {
            $ry = $imgHeight / $boxHeight;
        }
        else
            $ry = null;

        $width = $boxWidth;
        $height = $boxHeight;
        if ($rx === null && $ry !== null)
        {
            $rx = $ry;
            $width = round($imgWidth / $rx);
        }

        if ($ry === null && $rx !== null)
        {
            $ry = $rx;
            $height = round($imgHeight / $ry);
        }

        if ($width === 0 || $height === 0)
            return array('width' => 0, 'height' => 0);

        $dim = array();
        if ($fit == 'fill')
        {
            $dim['width'] = $width;
            $dim['height'] = $height;
        }
        else
        {
            $ratio = ($rx > $ry) ? $rx : $ry;
            if ($fit == 'outside')
                $ratio = ($rx < $ry) ? $rx : $ry;

            $dim['width'] = round($imgWidth / $ratio);
            $dim['height'] = round($imgHeight / $ratio);
        }
        return $dim;
    }

    public static function createWebp($sourceImage, $rewrite = false, $catalog = false)
    {
        if (!function_exists('imagewebp')) return false;

        if ($catalog === false) {
            $catalog = DIR_WS_IMAGES;
        }
        $path = DIR_FS_CATALOG;

        if (!is_file($path . $sourceImage)) return false;

        $pos = strripos($sourceImage, '.');
        $ext = strtolower(substr($sourceImage, $pos+1));
        $name = substr($sourceImage, 0, $pos);

        $webpName = $name . '.webp';

        if (!$rewrite && is_file($path . $webpName)) return false;

        if ($ext == 'jpg' || $ext == 'jpeg') {
            $image = @imagecreatefromjpeg($path . $sourceImage);
        } elseif ($ext == 'png') {
            $image = @imagecreatefrompng($path . $sourceImage);
        } elseif ($ext == 'gif') {
            $image = @imagecreatefromgif($path . $sourceImage);
        } else {
            return false;
        }
        if (!$image) {
            return false;
        }
        imagepalettetotruecolor($image);
        $result = imagewebp( $image, $path . $webpName);
        imagedestroy($image);

        return $result;
    }

    public static function removeWebp($sourceImage)
    {
        $path = self::getFSCatalogImagesPath();

        $pos = strripos($sourceImage, '.');
        $fileName = substr($sourceImage, 0, $pos);

        $webpName = $fileName . '.webp';

        if (is_file($path . $webpName)) {
            @unlink($path . $webpName);
        }
    }

    public static function getWebp($sourceImage, $catalog = false)
    {
        if (stripos($_SERVER['HTTP_USER_AGENT'], 'Chrome') === false && stripos($_SERVER['HTTP_USER_AGENT'], 'Firefox') === false) {
            return $sourceImage;
        }

        if ($catalog === false) {
            $catalog = DIR_WS_IMAGES;
        }
        $path = \Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $catalog;
        if (defined("DIR_WS_HTTP_ADMIN_CATALOG")) {
            $path = str_replace(DIR_WS_HTTP_ADMIN_CATALOG, '', $path);
        }

        $pos = strripos($sourceImage, '.');
        $fileName = substr($sourceImage, 0, $pos);

        $webpName = $fileName . '.webp';

        if (is_file($path . rawurldecode($webpName))) {
            return $webpName;
        }
        return $sourceImage;
    }

    /**
     * create images in sizes from image_types table
     * @param string   $sourceImage  image name with path from image folder
     * @param string   $imageType    image type from image_types_name field of image_types table if empty create all sizes
     * @param boolean  $rewrite      rewrite image if exist
     * @return boolean
     */
    public static function createResizeImages($sourceImage, $imageType = '', $rewrite = false)
    {
        $path = self::getFSCatalogImagesPath();

        if (!is_file($path . $sourceImage)) {
            return false;
        }

        $fileNameFull = strtolower($sourceImage);

        $pos = strripos($fileNameFull, '.');
        $ext = strtolower(substr($fileNameFull, $pos+1));
        $fileName = substr($fileNameFull, 0, $pos);
        if (!in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])) {
            return false;
        }

        $conditions = [];
        if ($imageType) {
            $conditions['image_types_name'] = $imageType;
        }
        $imageTypes = \common\models\ImageTypes::find()->where($conditions)->asArray()->all();

        foreach ($imageTypes as $_imageType) {

            $width = $_imageType['image_types_x'];

            $newImg = $fileName . '-' . $width . '.' . $ext;

            if ($rewrite || !is_file($path . $newImg)) {
                $size = @GetImageSize($path . $sourceImage);
                $height = ($size[1] * $width) / $size[0];

                self::tep_image_resize($path . $sourceImage, $path . $newImg, $width, $height);
            }
            self::createWebp(DIR_WS_IMAGES . $newImg);
        }

        return true;

    }

    /**
     * remove images in sizes from image_types table
     * @param string   $sourceImage  image name with path from image folder
     * @return boolean
     */
    public static function removeResizeImages($sourceImage)
    {
        $path = self::getFSCatalogImagesPath();

        $fileNameFull = strtolower($sourceImage);

        $pos = strripos($fileNameFull, '.');
        $ext = strtolower(substr($fileNameFull, $pos+1));
        $fileName = substr($fileNameFull, 0, $pos);

        $imageTypes = \common\models\ImageTypes::find()->asArray()->all();

        foreach ($imageTypes as $imageType) {
            $width = $imageType['image_types_x'];
            $removeImage = $path . $fileName . '-' . $width . '.' . $ext;
            if (is_file($removeImage)) {
                @unlink($removeImage);
                self::removeWebp($fileName . '-' . $width . '.' . $ext);
            }
        }

        return true;
    }

    /**
     * @param string   $sourceImage  image name with path from image folder
     * @param string   $imageType   image type from image_types_name field of image_types table if empty create all sizes
     * @param array    $attributes  additional attributes for teg
     * @param string   $naImage
     * @param boolean  $lazy_load
     * @param array  $responsiveImages list of images by image_types_id
     * @return string img html teg with srcset and sizes attributes
     */
    public static function getImageSet($sourceImage, $imageType = '', $attributes = [], $naImage = '', $lazy_load = false, $responsiveImages = [])
    {
        $urlPath = \common\helpers\Media::getAlias('@webCatalogImages/');

        $path = \Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . DIR_WS_IMAGES;
        if (defined("DIR_WS_HTTP_ADMIN_CATALOG")) {
            $path = str_replace(DIR_WS_HTTP_ADMIN_CATALOG, '', $path);
        }

        if (!$naImage && $naImage !== false) $naImage = Info::themeFile('/img/na.png');

        if (!is_file($path . $sourceImage)) {
            if ($naImage === false) {
                return false;
            }

            return Html::tag('picture', Html::img($naImage, $attributes));
        }

        $pos = strripos($sourceImage, DIRECTORY_SEPARATOR);
        $fileNameFull = strtolower(substr($sourceImage, $pos+1));
        $filePath = substr($sourceImage, 0, $pos);

        $pos = strripos($fileNameFull, '.');
        $ext = strtolower(substr($fileNameFull, $pos+1));
        $fileName = substr($fileNameFull, 0, $pos);

        $conditions = [];
        if ($imageType) {
            $conditions['image_types_name'] = $imageType;
        }
        $imageTypes = \common\models\ImageTypes::find()->where($conditions)->asArray()->all();

        $sources = '';
        foreach ($imageTypes as $imageType) {
            $width = $imageType['image_types_x'];
            if ($responsiveImages[$imageType['image_types_id']]['image'] ?? false) {
                $imagePath = $path . $responsiveImages[$imageType['image_types_id']]['image'];
                $imageUrl = $urlPath . $responsiveImages[$imageType['image_types_id']]['image'];
            } else {
                $imagePath = $path . $filePath . DIRECTORY_SEPARATOR . $fileName . '-' . $width . '.' . $ext;
                $imageUrl = $urlPath . self::getWebp($filePath . DIRECTORY_SEPARATOR . rawurlencode($fileName) . '-' . $width . '.' . $ext);
            }

            $media = '';
            if ($imageType['width_from']) {
                $media .= '(min-width: ' . $imageType['width_from'] . 'px)';
            }
            if ($imageType['width_from'] && $imageType['width_to']) {
                $media .= ' and ';
            }
            if ($imageType['width_to']) {
                $media .= '(max-width: ' . $imageType['width_to'] . 'px)';
            }

            if ( ($attributes['id'] ?? false)) {
                $css = '';
                if ($media) {
                    $css .= '@media ' . $media . '{';
                }
                if ($imageType['image_types_y'] && $imageType['image_types_x']) {
                    $heightPer = round($imageType['image_types_y'] * 100 / $imageType['image_types_x'], 4);
                    $css .= 'picture#' . $attributes['id'] . ' {padding-top: ' . $heightPer . '%;position: relative}';
                    if ($responsiveImages[$imageType['image_types_id']]['fit'] ?? false) {
                        $fit = $responsiveImages[$imageType['image_types_id']]['fit'];
                    } else {
                        $fit = 'cover';
                    }
                    $css .= 'picture#' . $attributes['id'] . ' img {object-fit: ' . $fit . ';';
                    if ($responsiveImages[$imageType['image_types_id']]['position'] ?? false) {
                        $position = $responsiveImages[$imageType['image_types_id']]['position'];
                        $css .= 'object-position: ' . $position . '%;';
                    }
                    $css .= 'position:absolute;left:0;top:0;width:100%;height:100%}';
                } else {
                    $css .= 'picture#' . $attributes['id'] . ' img {position: static;}';
                }
                if ($media) {
                    $css .= '}';
                }
                Info::setScriptCss($css);
            }

            if (!is_file($imagePath)) continue;
            $size = @GetImageSize($imagePath);
            if (!$size[0]) continue;
            if (!$imageType['width_from'] && !$imageType['width_to']) continue;

            $sourcesAttr = [
                'srcset' => $imageUrl,
                'media' => $media,
            ];
            if ($lazy_load) {
                $sourcesAttr['data-srcset'] = $imageUrl;
                $sourcesAttr['srcset'] = $naImage;
            }
            $sources .= Html::tag('source', '', $sourcesAttr);
        }

        $src = $urlPath . self::getWebp($sourceImage);
        if ($lazy_load) {
            $attributes['data-src'] = $src;
            $src = $naImage;
        }


        $id = 0;
        if ($attributes['id']) {
            $id = $attributes['id'];
            unset($attributes['id']);
        }
        $img = Html::img($src, $attributes);
        $html = Html::tag('picture', $sources . $img, ($id ? ['id' => $id] : []));
        return  $html;
    }


    /**
     * @param  string    $type  which type of image need to create,
     *                          can be '', 'products', 'categories', 'banners',
     *                          if '' create all types
     * @param  integer   $iteration  if iteration == -1 create all images in one frame
     * @param  integer   $frameSize  count images of categories or products in one frame
     * @return string json [
     *                        'iteration', // iteration for next request
     *                        'products_count', // all product images in db
     *                        'product', // processed products
     *                        'product_images', // created product images
     *                        'product_images_all', // processed product images
     *                        'categories_count', // all category images in db
     *                        'categories', // processed categories
     *                        'category_images', // created category images
     *                        'category_images_all', // processed category images
     *                        'banners', //  processed banners
     *                        'banner_images', // created banner images
     *                        'banner_images_all', // processed banner images
     *                     ]
     */
    public static function createAllWebpImages($type = '', $iteration = 0, $frameSize = 10)
    {
        $result = [];

        if (!$type || $type == 'products') {
            $products_id = 0;

            $count = \common\models\ProductsImages::find()->count();
            $result['products_count'] = $count;

            if ($iteration == -1) {
                $productsImages = \common\models\ProductsImages::find()
                    ->select(['products_id', 'products_images_id'])
                    ->orderBy('products_id', 'products_images_id')
                    ->asArray()
                    ->all();
            } else {
                if ($count > $frameSize * ($iteration + 1)) {
                    $result['iteration'] = $iteration + 1;
                }

                $productsImages = \common\models\ProductsImages::find()
                    ->select(['products_id', 'products_images_id'])
                    ->orderBy('products_id', 'products_images_id')
                    ->limit($frameSize)
                    ->offset($frameSize * $iteration)
                    ->asArray()
                    ->all();
            }

            foreach ($productsImages as $image) {
                if ($image['products_id'] != $products_id) {
                    $products_id = $image['products_id'];
                    $result['product']++;
                }
                $count = \common\classes\Images::normalizeImageFiles($image['products_id'], $image['products_images_id']);
                $result['product_images'] += $count;
                $result['product_images_all']++;
            }
        }

        if (!$type || $type == 'categories') {
            $result['category_images'] = 0;

            $categoriesCount = \common\models\Categories::find()->count();
            $result['categories_count'] = $categoriesCount;

            if ($iteration == -1) {
                $categories = \common\models\Categories::find()->asArray()->all();
            } else {

                $categories = \common\models\Categories::find()
                    ->limit($frameSize)
                    ->offset($frameSize * $iteration)
                    ->asArray()
                    ->all();
            }

            foreach ($categories as $category) {
                $result['category_images_all']++;
                $result['categories']++;
                $sql_data_array = [];
                foreach (['gallery' => '', 'hero' => '_2', 'homepage' => '_3'] as $imageType => $mod) {

                    $sql_data_array['categories_image' . $mod] = self::moveImage(
                        $category['categories_image' . $mod],
                        'categories' . DIRECTORY_SEPARATOR . $category['categories_id'] . DIRECTORY_SEPARATOR . $imageType
                    );
                    if (!$sql_data_array['categories_image' . $mod]) {
                        continue;
                    }
                    $webp = Images::createWebp($sql_data_array['categories_image' . $mod]);
                    Images::createResizeImages($sql_data_array['categories_image' . $mod], 'Category ' . $imageType);
                    if ($webp) {
                        $result['category_images']++;
                    }
                }
                $categoryMod = \common\models\Categories::findOne($category['categories_id']);
                $categoryMod->attributes = $sql_data_array;
                $categoryMod->save();
            }


            $categoriesPSCount = \common\models\CategoriesPlatformSettings::find()->count();
            $result['categories_count'] = $result['categories_count'] + $categoriesPSCount;

            if ($iteration == -1) {
                $categories = \common\models\CategoriesPlatformSettings::find()->asArray()->all();
            } else {
                if ($categoriesCount > $frameSize * $iteration) {
                    $categories = [];

                    $result['iteration'] = $iteration + 1;
                } else {

                    $iteration2 = $iteration - ceil($categoriesCount / $frameSize);

                    if ($categoriesPSCount > $frameSize * ($iteration2 + 1)) {
                        $result['iteration'] = $iteration + 1;
                    }

                    $categories = \common\models\CategoriesPlatformSettings::find()
                        ->limit($frameSize)
                        ->offset($frameSize * $iteration2)
                        ->asArray()
                        ->all();
                }
            }

            foreach ($categories as $category) {
                $result['category_images_all']++;
                $sql_data_array = [];
                foreach (['gallery' => '', 'hero' => '_2', 'homepage' => '_3'] as $imageType => $mod) {

                    $sql_data_array['categories_image' . $mod] = self::moveImage(
                        $category['categories_image' . $mod],
                        'categories' . DIRECTORY_SEPARATOR . $category['categories_id'] . DIRECTORY_SEPARATOR . $imageType
                    );
                    if (!$sql_data_array['categories_image' . $mod]) {
                        continue;
                    }
                    $webp = Images::createWebp($sql_data_array['categories_image' . $mod]);
                    Images::createResizeImages($sql_data_array['categories_image' . $mod], 'Category ' . $imageType);
                    if ($webp) {
                        $result['category_images']++;
                    }

                }
                $categoryMod = \common\models\CategoriesPlatformSettings::findOne([
                    'categories_id' => $category['categories_id'],
                    'platform_id' => $category['platform_id']
                ]);
                $categoryMod->attributes = $sql_data_array;
                $categoryMod->save();
            }

        }

        if (!$type || $type == 'banners') {

            $mainImages = \common\models\BannersLanguages::find()
                ->orderBy('banners_id')
                ->all();

            $languages = \common\helpers\Language::get_languages();

            $result['banner_images'] = 0;
            $result['banner_images_all'] = 0;
            $result['banners'] = 0;
            $bannersId = 0;
            foreach ($mainImages as $mainImage) {

                $imgPath = 'banners' . DIRECTORY_SEPARATOR . $mainImage->banners_id;
                if ($mainImage->banners_image) {
                    $mainImage->banners_image = self::moveImage($mainImage->banners_image, $imgPath);
                    $mainImage->save();
                    $webp = self::createWebp($mainImage->banners_image);
                    if ($webp) {
                        $result['banner_images']++;
                    }
                    $result['banner_images_all']++;
                }

                if ($bannersId == $mainImage->banners_id) {
                    continue;
                }
                $bannersId = $mainImage->banners_id;
                $result['banners']++;

                $banner = \common\models\Banners::find()
                    ->where(['banners_id' => $bannersId])
                    ->asArray()
                    ->one();

                $groupSizes = \common\models\BannersGroups::find()
                    ->where(['banners_group' => $banner['banners_group']])
                    ->asArray()
                    ->all();

                foreach ($languages as $language) {

                    $bannersLanguages = \common\models\BannersLanguages::find()
                        ->where(['banners_id' => $bannersId, 'language_id' => $language['id']])
                        ->asArray()
                        ->one();
                    $mainImage = $bannersLanguages['banners_image'];

                    foreach ($groupSizes as $groupSize) {

                        $imgPath = 'banners' . DIRECTORY_SEPARATOR . $bannersId;

                        $img = \common\models\BannersGroupsImages::findOne([
                            'banners_id' => $bannersId,
                            'language_id' => $language['id'],
                            'image_width' => $groupSize['image_width'],
                        ]);
                        if (!$img || !$img->image || !is_file(DIR_FS_CATALOG_IMAGES . $img->image)) {
                            if (!$mainImage || !is_file(DIR_FS_CATALOG_IMAGES . $mainImage)) {
                                continue;
                            }

                            $imgExplode = explode('/', $mainImage);
                            $imgName = end($imgExplode);
                            $pos = strrpos($imgName, '.');
                            $name = substr($imgName, 0, $pos);
                            $ext = substr($imgName, $pos);

                            $newImg = $imgPath . DIRECTORY_SEPARATOR . $name . '[' . $groupSize['image_width'] . ']' . $ext;

                            if (!is_file(DIR_FS_CATALOG_IMAGES .$newImg)) {
                                $size = @GetImageSize(DIR_FS_CATALOG_IMAGES . $mainImage);
                                $height = ($size[1] * $groupSize['image_width']) / $size[0];
                                self::tep_image_resize(DIR_FS_CATALOG_IMAGES . $mainImage, DIR_FS_CATALOG_IMAGES . $newImg, $groupSize['image_width'], $height);
                            }

                        } elseif ($img && $img->image) {
                            $newImg = $img->image;
                        }
                        if (!$img) {
                            $img = new \common\models\BannersGroupsImages();
                        }
                        $img->attributes = [
                            'banners_id' => (int)$bannersId,
                            'language_id' => (int)$language['id'],
                            'image_width' => (int)$groupSize['image_width'],
                            'image' => $newImg,
                        ];

                        $img->save();

                        $webp = self::createWebp($newImg);
                        if ($webp) {
                            //$result['banner_images']++;
                        }

                    }
                }
            }
        }

        foreach (\common\helpers\Acl::getExtensionCreateImagesSettings() as $createImagesSettings) {
            if (!$type || $type == $createImagesSettings['type']) {
                if ($_w = \common\helpers\Acl::checkExtension($createImagesSettings['extension'], 'createImages')){
                    $_response = $_w::createImages($iteration, $frameSize);
                    $result = array_merge($result, $_response);
                }
            }
        }

        return json_encode($result);
    }


    /**
     * if image is't in $destination folder copy it to this folder and returns whole filename
     * @param string $sourceImage
     * @param string $destination
     * @return string
     */
    public static function moveImage($sourceImage, $destination, $defaultImagePath = true)
    {
        if (stripos($sourceImage, $destination) !== false) {
            return $sourceImage;
        }

        $path = \Yii::getAlias('@webroot') . '/' . DIR_WS_IMAGES; // don't use DIRECTORY_SEPARATOR here
        if (defined("DIR_WS_HTTP_ADMIN_CATALOG")) {
            $path = str_replace('/'.trim(DIR_WS_HTTP_ADMIN_CATALOG,'/').'/', '/', $path);
        }
        $pathDestination = $path;
        if (!$defaultImagePath) {
            $path = '';
        }

        if (!is_file($path . $sourceImage)) {
            return '';
        }

        \yii\helpers\FileHelper::createDirectory($pathDestination . $destination, 0777);

        if (str_contains($sourceImage, '/')) {
            $pos = strripos($sourceImage, '/');
            $fileName = strtolower(substr($sourceImage, $pos+1));
        } elseif (str_contains($sourceImage, '\\')) {
            $pos = strripos($sourceImage, '\\');
            $fileName = strtolower(substr($sourceImage, $pos+1));
        } else {
            $fileName = $sourceImage;
        }

        @copy($path . $sourceImage, $pathDestination . $destination . DIRECTORY_SEPARATOR . $fileName);

        return $destination . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param array $settings[
     *      'src', //image source - required
     *      'top', // crop - required
     *      'left', // crop - required
     *      'width', // crop - required
     *      'height', // crop - required
     *      'destination', // image destination - required
     *      'imgWidth', // required image width - not required
     *      'imgHeight', // required image height - not required
     *      'color', // side space color - not required
     * ]
     * @return mixed false if error, image destination (string)
     */
    public static function cropImage($settings) {
        if (IMAGE_RESIZE != 'GD' || !function_exists("gd_info")) {
            return false;
        }

        $image = $settings['src'];
        $size = @GetImageSize($image);
        $originWidth = $size[0];
        $originHeight = $size[1];
        $newWidth = $settings['imgWidth'];
        $newHeight = $settings['imgHeight'];
        if (!$newWidth && !$newHeight) {
            $newWidth = $settings['width'];
            $newHeight = $settings['height'];
        } elseif ($newWidth && !$newHeight) {
            $newHeight = $newWidth * $settings['height'] / $settings['width'];
        } elseif (!$newWidth && $newHeight) {
            $newWidth = $newHeight * $settings['width'] / $settings['height'];
        }

        switch ($size[2]) {
            case 18:
                $im = @imagecreatefromwebp($image);
                break;
            case 1 : // GIF
                $im = @ImageCreateFromGif($image);
                break;
            case 3 : // PNG
                $im = @ImageCreateFromPng($image);
                if ($im) {
                    if (function_exists('imageAntiAlias')) {
                        @imageAntiAlias($im, true);
                    }
                    @imageAlphaBlending($im, true);
                    @imageSaveAlpha($im, true);
                }
                break;
            case 2 : // JPEG
                $im = @ImageCreateFromJPEG($image);
                break;
            default :
                return false;
        }
        if (!$im) {
            return false;
        }

        $imPic = 0;
        if (function_exists('ImageCreateTrueColor'))
            $imPic = @ImageCreateTrueColor($newWidth, $newHeight);
        if ($imPic == 0)
            $imPic = @ImageCreate($newWidth, $newHeight);
        if ($imPic == 0) {
            return false;
        }

        if ($settings['color']) {
            $settings['color'] = str_replace(' ', '', $settings['color']);
            if (preg_match("/^rgb\(([0-9]{1,3})\,([0-9]{1,3})\,([0-9]{1,3})\)$/i", $settings['color'], $matches)) {
                $bgColorR = $matches[1];
                $bgColorG = $matches[2];
                $bgColorB = $matches[3];
            } elseif (preg_match("/^rgba\(([0-9]{1,3})\,([0-9]{1,3})\,([0-9]{1,3}),([0-9\.]{1,5})\)$/i", $settings['color'], $matches)) {
                $bgColorR = $matches[1];
                $bgColorG = $matches[2];
                $bgColorB = $matches[3];
            } elseif (preg_match("/^\#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i", $settings['color'], $matches)) {
                $bgColorR = hexdec($matches[1]);
                $bgColorG = hexdec($matches[2]);
                $bgColorB = hexdec($matches[3]);
            } elseif (preg_match("/^\#([0-9a-f])([0-9a-f])([0-9a-f])$/i", $settings['color'], $matches)) {
                $bgColorR = hexdec($matches[1] . $matches[1]);
                $bgColorG = hexdec($matches[2] . $matches[2]);
                $bgColorB = hexdec($matches[3] . $matches[3]);
            } else {
                $bgColorR = 255;
                $bgColorG = 255;
                $bgColorB = 255;
            }
        } else {
            $bgColorR = 255;
            $bgColorG = 255;
            $bgColorB = 255;
        }
        $bgColorA = 0;
        @ImageInterlace($imPic, 1);
        if (function_exists('imageAntiAlias')) {
            @imageAntiAlias($imPic, true);
        }
        @imagealphablending($imPic, false);
        @imagesavealpha($imPic, true);
        $transparent = @imagecolorallocatealpha($imPic, $bgColorR, $bgColorG, $bgColorB, $bgColorA);
        for ($i = 0; $i < $newWidth; $i++) {
            for ($j = 0; $j < $newHeight; $j++) {
                @imageSetPixel($imPic, $i, $j, $transparent);
            }
        }

        $scale = $newWidth / $settings['width'];
        $left = $settings['left'];
        $top = $settings['top'];
        $width = $settings['width'];
        $height = $settings['height'];
        $imgLeft = 0;
        $imgTop = 0;
        $imgWidth = $newWidth;
        $imgHeight = $newHeight;
        if ($settings['top'] < 0) {
            $top = 0;
            $height = $settings['height'] + $settings['top'];
            $imgTop = -$settings['top'] * $scale;
            $imgHeight = $height * $scale;
        }
        if ($settings['left'] < 0) {
            $left = 0;
            $width = $settings['width'] + $settings['left'];
            $imgLeft = -$settings['left'] * $scale;
            $imgWidth = $width * $scale;
        }
        if ($height + $top > $originHeight) {
            $height = $height - ($height + $top - $originHeight);
            $imgHeight = $height * $scale;
        }
        if ($width + $left > $originWidth) {
            $width = $width - ($width + $left - $originWidth);
            $imgWidth = $width * $scale;
        }

        if (function_exists('ImageCopyResampled')) {
            $resized = @ImageCopyResampled($imPic, $im,
                $imgLeft, $imgTop, $left, $top,
                $imgWidth, $imgHeight, $width, $height);
        }
        if (!$resized) {
            @ImageCopyResized($imPic, $im,
                $imgLeft, $imgTop, $left, $top,
                $imgWidth, $imgHeight, $width, $height);
        }

        if ($size[2] == 3) {
            @imagePNG($imPic, $settings['destination'], 9);
        } elseif ($size[2] == 18) {
            @imagewebp($imPic, $settings['destination']);
        } else {
            @imageJPEG($imPic, $settings['destination'], 85);
        }
        if (is_file($settings['destination'])) {
            chmod($settings['destination'],0666);
            return $settings['destination'];
        }
        return false;
    }
}
