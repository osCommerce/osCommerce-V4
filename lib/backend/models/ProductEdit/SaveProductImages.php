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

namespace backend\models\ProductEdit;

use common\classes\Images;
use yii;
use yii\helpers\ArrayHelper;
use common\models\Products;
use common\api\models\AR\Products as ARProduct;

class SaveProductImages
{

    protected $product;
    protected $uploadsDirectory = '';

    public function __construct(Products $product, $pathToUploads)
    {
        $this->product = $product;
        $this->uploadsDirectory = $pathToUploads;
    }

    public function save()
    {
        $products_id = $this->product->products_id;
        $path = $this->uploadsDirectory;

        $Images = new \common\classes\Images();

        $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $products_id . DIRECTORY_SEPARATOR;
        if (!file_exists($image_location)) {
            mkdir($image_location, 0777, true);
            @chmod($image_location, 0777);
        }

        $default_image = (int) Yii::$app->request->post('default_image'); //pointer

        $image_status = Yii::$app->request->post('image_status');
        $products_images_id = Yii::$app->request->post('products_images_id');
        $products_images_deleted = Yii::$app->request->post('products_images_deleted');

        $orig_file_name = Yii::$app->request->post('orig_file_name'); //new uploaded images
        $use_origin_image_name = Yii::$app->request->post('use_origin_image_name',[]);
        $image_title = Yii::$app->request->post('image_title');
        $image_alt = Yii::$app->request->post('image_alt');
        $alt_file_name_flag = Yii::$app->request->post('alt_file_name_flag');
        $alt_file_name = Yii::$app->request->post('alt_file_name');
        $no_watermark = Yii::$app->request->post('no_watermark');
        $use_external_image_array = Yii::$app->request->post('use_external_images', []);
        $external_image_array = Yii::$app->request->post('external_image', []);
        $external_image_original = Yii::$app->request->post('external_image_original','');
        $link_video_flag_array = Yii::$app->request->post('link_video_flag', []);
        $link_video_id_array = Yii::$app->request->post('link_video_id', []);
        //hash_file_name

        $cleaningUploads = [];

        $update_image_data = [];
        $_sort_order2pointer = [];
        $images_sort = [];
        if (is_array($products_images_id)) {
            $images_sort_order = Yii::$app->request->post('images_sort_order');
            if (!empty($images_sort_order)) {
                parse_str($images_sort_order, $images_sort);
                if (isset($images_sort['image-box']) && is_array($images_sort['image-box'])) {
                    $images_sort = array_flip($images_sort['image-box']);
                }
            }

            foreach ($products_images_id as $pointer => $imageId) {
                tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_ATTRIBUTES . " where products_images_id = '" . (int) $imageId . "'");
                tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_INVENTORY . " where products_images_id = '" . (int) $imageId . "'");
                if ((int) $products_images_deleted[$pointer] == 1) {
                    continue;
                }

                $image_data = [
                    'products_images_id' => $imageId,
                    'products_id' => $products_id,
                    'default_image' => ($pointer == $default_image)?1:0,
                    'image_status' => (int)($image_status[$pointer]??0),
                    'sort_order' => isset($images_sort[$pointer])?$images_sort[$pointer]:(int)$pointer,
                    'image_description' => [],
                ];
                $_sort_order2pointer[$image_data['sort_order']] = (int)$pointer;

                foreach ($orig_file_name[$pointer] as $language_id => $orig_file) {
                    if ( !empty($orig_file) ) {
                        $cleaningUploads[] = $path . $orig_file;
                    }
                    $image_description = [
                        'language_id' => (int)$language_id,
                        'image_title' => $image_title[$pointer][$language_id],
                        'image_alt' => $image_alt[$pointer][$language_id],
                        'image_source_url' => (empty($orig_file)?'':$path . $orig_file),
                        'alt_file_name' =>((int)ArrayHelper::getValue($alt_file_name_flag, [$pointer, $language_id]) == 0)?'':$alt_file_name[$pointer][$language_id],
                        'no_watermark' => (int)ArrayHelper::getValue($no_watermark, [$pointer, $language_id]),
                        'use_external_images' => (isset($use_external_image_array[$pointer][$language_id]) ? 1 : 0) ,
                        'external_image_original' => $external_image_original[$pointer][$language_id],
                        'use_origin_image_name' => (int)ArrayHelper::getValue($use_origin_image_name, [$pointer, $language_id]),
                        'link_video_id' => (isset($link_video_flag_array[$pointer][$language_id]) && $link_video_flag_array[$pointer][$language_id] == 1 ? (isset($link_video_id_array[$pointer][$language_id]) ? $link_video_id_array[$pointer][$language_id] : 0) : 0) ,
                    ];
                    $image_data['image_description'][$language_id==0?'00':\common\classes\language::get_code($language_id)] = $image_description;
                    if ( isset($external_image_array[$pointer][$language_id]) && is_array($external_image_array[$pointer][$language_id]) ) {
                        foreach ( Images::getImageTypes(false, true) as $image_type ) {
                            $external_image_url = $external_image_array[$pointer][$language_id][$image_type['image_types_id']] ?? '';
                            tep_db_query(
                                "INSERT INTO ".TABLE_PRODUCTS_IMAGES_EXTERNAL_URL."(products_images_id, language_id, image_types_id, image_url) ".
                                "VALUES('".(int)$imageId."', '".(int)$language_id."', '".(int)$image_type['image_types_id']."', '".tep_db_input($external_image_url)."') ".
                                "ON DUPLICATE KEY UPDATE image_url='".tep_db_input($external_image_url)."'"
                            );
                            ;
                        }
                    }
                }
                if ($ext = \common\helpers\Extensions::isAllowed('ProductImagesByPlatform')) {
                    $ext::imageSave($image_data, $pointer);
                }
                $update_image_data[] = $image_data;
            }
        }
        
        $objProduct = ARProduct::findOne(['products_id'=>$products_id]);
        $objProduct->importArray([
            'images' => $update_image_data,
        ]);
        $objProduct->save();
        $objProduct->refresh();
        $imageBack = $objProduct->exportArray([
            'images'=>[
                '*'=>[
                    'products_images_id',
                    'sort_order',
                ],
            ],
        ]);
        unset($objProduct);

        foreach( $imageBack['images'] as $_map ) {
            $imageId = $_map['products_images_id'];
            $pointer = $_sort_order2pointer[$_map['sort_order']];
            $products_images_id[$pointer] = $imageId;
            /** @var \common\extensions\AttributesImages\AttributesImages $ext */
            if ($ext = \common\helpers\Extensions::isAllowed('AttributesImages')) {
                $ext::productSave($imageId, $pointer);
            }

            /** @var \common\extensions\InventoryImages\InventoryImages $ext */
            if ($ext = \common\helpers\Extensions::isAllowed('InventoryImages')) {
                $ext::productSave($imageId, $pointer);
            }
        }
        unset($update_image_data);
        unset($_sort_order2pointer);
        unset($imageBack);

        $check_image_query = tep_db_query("SELECT products_images_id FROM " . TABLE_PRODUCTS_IMAGES . " WHERE default_image = '1' and products_id = '" . (int) $products_id . "'");
        if (tep_db_num_rows($check_image_query) == 0) {
            $check_image_query = tep_db_query("SELECT products_images_id FROM " . TABLE_PRODUCTS_IMAGES . " WHERE products_id = '" . (int) $products_id . "'"); //add sort order
            if (tep_db_num_rows($check_image_query) > 0) {
                $check_image = tep_db_fetch_array($check_image_query);
                tep_db_query("update " . TABLE_PRODUCTS_IMAGES . " set default_image = '1' where products_images_id = '" . (int) $check_image['products_images_id'] . "'");
            }
        }

        // {{ cleaning uploaded images
        if ( count($cleaningUploads)>0 ) {
            $cleaningUploads = array_unique($cleaningUploads);
            foreach ($cleaningUploads as $cleaningUpload) {
                if ( is_file($cleaningUpload) ) @unlink($cleaningUpload);
            }
        }
        // }} cleaning uploaded images
        $mapsId = (int)Yii::$app->request->post('maps_id', 0);
        $this->product->setAttributes(['maps_id'=>$mapsId], false);

        /** @var \common\extensions\ProductDesigner\ProductDesigner $pdExt */
        if ($pdExt = \common\helpers\Extensions::isAllowed('ProductDesigner')){
            $pdExt::productSave($products_id);
        }
    }
}