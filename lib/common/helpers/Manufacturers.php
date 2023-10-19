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

use common\helpers\Product;
use yii\db\ActiveQuery;
use Yii;
use common\classes\Images;
use backend\design\Uploads;

class Manufacturers {
    static $cache = [];

    public static function get_manufacturers($manufacturers_array = '') {
        if (!is_array($manufacturers_array))
            $manufacturers_array = array();

        $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'], 'text' => $manufacturers['manufacturers_name']);
        }

        return $manufacturers_array;
    }
    
    public static function getManufacturersList() {
        $mArray = [];

        foreach (\common\models\Manufacturers::find()->select(['manufacturers_id', 'manufacturers_name'])->orderBy('manufacturers_name')->all() as $manufacturer){
            $mArray[$manufacturer->manufacturers_id] = $manufacturer->manufacturers_name;
        }
        return $mArray;
    }

    public static function get_manufacturer_info($field, $manufacturers_id) {
        if ( $manufacturers_id==0 ) return null;
        $manufacturers_query = tep_db_fetch_array(tep_db_query("select {$field} from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int) $manufacturers_id . "'"));
        return $manufacturers_query[$field] ?? null;
    }
    
    public static function get_manufacturer_meta_descr($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_meta_description from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_meta_description'] ?? null;
    }
    public static function getManufacturerDescription($manufacturer_id, $language_id) {
      if (!isset(self::$cache[$manufacturer_id][$language_id])) {
        self::$cache[$manufacturer_id][$language_id] = [];//in case of nincorrect $manufacturer_id / $language_id
        $manufacturer_query = tep_db_query("select * from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' /*and languages_id = '" . (int) $language_id . "'*/");
        while ($manufacturer = tep_db_fetch_array($manufacturer_query)) {
            self::$cache[$manufacturer_id][$manufacturer['languages_id']] = $manufacturer;
        }
      }
      return isset(self::$cache[$manufacturer_id][$language_id]['manufacturers_description'])?self::$cache[$manufacturer_id][$language_id]['manufacturers_description']:'';
    }

    public static function get_manufacturer_meta_key($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_meta_key from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_meta_key'] ?? null;
    }

    public static function get_manufacturer_meta_title($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_meta_title from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_meta_title'] ?? null;
    }

    public static function get_manufacturers_h1_tag($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_h1_tag from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_h1_tag'] ?? null;
    }

    public static function get_manufacturers_h2_tag($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_h2_tag from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_h2_tag'] ?? null;
    }

    public static function get_manufacturers_h3_tag($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_h3_tag from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_h3_tag'] ?? null;
    }

    public static function get_manufacturer_seo_name($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_seo_name from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_seo_name'] ?? null;
    }

    public static function get_manufacturer_url($manufacturer_id, $language_id) {
        $manufacturer_query = tep_db_query("select manufacturers_url from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturer_id . "' and languages_id = '" . (int) $language_id . "'");
        $manufacturer = tep_db_fetch_array($manufacturer_query);

        return $manufacturer['manufacturers_url'] ?? null;
    }
    
    public static function products_ids_manufacturer($manufacturer_id, $include_deactivated = false, $platform_id = 0) {
        global $languages_id;
        $products_array = [];

        $products_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p " . 
                                       ($platform_id? " inner join " . TABLE_PLATFORMS_PRODUCTS . " pp on pp.products_id=p.products_id and pp.platform_id='" . $platform_id . "' " : "") .                                       
                                       " where 1 " . (!$include_deactivated? Product::getState(true). Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "":"" ) ." and p.manufacturers_id = '" . (int) $manufacturer_id . "'");        
        
        
        if (tep_db_num_rows($products_query)){
            while($products = tep_db_fetch_array($products_query)){
               $products_array[] = $products['products_id'];
            }            
        }
        return $products_array;
    }
    /**
     *
     * @param type $manufacturer_id
     * @param type $include_deactivated
     * @param type $platform_id
     * @return type
     */
    public static function hasAnyProduct($manufacturer_id, $include_deactivated = false, $platform_id = 0) {
        $products_query = tep_db_query("select p.products_id from " . TABLE_PRODUCTS . " p " .
                                       ($platform_id? " inner join " . TABLE_PLATFORMS_PRODUCTS . " pp on pp.products_id=p.products_id and pp.platform_id='" . $platform_id . "' " : "") .
                                       " where 1 " . (!$include_deactivated? Product::getState(true). Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "":"" ) ." and p.manufacturers_id = '" . (int) $manufacturer_id . "' limit 1");

        return tep_db_num_rows($products_query);
    }

    public static function get_manufacturer_filters($manufacturers_id) {
        $filters_array = array();
        if ($manufacturers_id > 0) {
            $filters_query = tep_db_query("select f.filters_type, f.options_id, f.properties_id, f.status from " . TABLE_MANUFACTURERS . " m left join " . TABLE_FILTERS . " f on m.manufacturers_id = f.manufacturers_id and f.filters_of = 'brand' where m.manufacturers_id = '" . (int) $manufacturers_id . "' order by f.sort_order");
            while ($filters = tep_db_fetch_array($filters_query)) {
                if ($filters['status']) {
                    $filters_array[] = $filters;
                }
            }
            if (count($filters_array) == 0) {
                return self::get_manufacturer_filters(0);
            }
        } else {
            $filters_query = tep_db_query("select f.filters_type, f.options_id, f.properties_id, f.sort_order from " . TABLE_FILTERS . " f where f.manufacturers_id = '0' and f.filters_of = 'brand' and f.status = '1' group by f.filters_type, f.options_id, f.properties_id order by f.sort_order");
            while ($filters = tep_db_fetch_array($filters_query)) {
                $filters_array[] = $filters;
            }
        }
        return $filters_array;
    }

    public static function findManufacturerByName($name) {
      $ret = 0;
      $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_name like '" . tep_db_input($name) . "'" );
      if ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
        $ret = $manufacturers['manufacturers_id'];
      }

      return $ret;
    }

    /**
     * @param int $languagesId
     * @param int $manufacturersId
     *
     * @return array|null
     */
    public static function getCategoriesDescriptionList(int $languagesId, int $manufacturersId)
    {
        $manufacture = \common\models\Manufacturers::find()
            ->alias('m')
            ->select([
                'm.manufacturers_id AS id',
                'm.manufacturers_name AS categories_name',
                'm.manufacturers_image AS categories_image',
            ])
            ->joinWith([
                'manufacturersInfo mi' => function (ActiveQuery $q) use ($languagesId) {
                    $q->onCondition(['mi.languages_id' => $languagesId]);
                    return $q;
                }
            ], false)
            ->where(['m.manufacturers_id' => $manufacturersId])
            ->asArray()
            ->one();
        return $manufacture;
    }

    public static function saveImage(int $manufacturersId, $suffix, $imageType)
    {
        $imageLoaded = Yii::$app->request->post('image_loaded' . $suffix);
        $deleteImage = Yii::$app->request->post('delete_image' . $suffix);
        $manufacturersImage = Yii::$app->request->post('manufacturers_image' . $suffix);

        $manufacture = \common\models\Manufacturers::findOne($manufacturersId);
        if (!$manufacture) return false;

        $manufactureImage = 'manufacturers_image' . $suffix;

        $saveImage = \common\helpers\Image::prepareSavingImage(
            $manufacture->$manufactureImage,
            $manufacturersImage,
            $imageLoaded,
            'brands' . DIRECTORY_SEPARATOR . $manufacturersId,
            $deleteImage
        );
        if ($deleteImage && $manufacture->$manufactureImage) {
            Images::removeResizeImages($manufacture->$manufactureImage);
        }
        Images::createResizeImages($saveImage, 'Brand ' . $imageType);

        $manufacture->$manufactureImage = $saveImage;
        $manufacture->save();
    }

}
