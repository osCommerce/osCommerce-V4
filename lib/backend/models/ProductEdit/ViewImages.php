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

class ViewImages
{

    /**
     * @var \objectInfo
     */
    protected $productInfoRef;

    public function __construct($productInfo)
    {
        $this->productInfoRef = $productInfo;
        //$this->wrap($this->productInfoRef);
    }

    public function populateView($view)
    {
        $pInfo = $this->productInfoRef;
        $languages = \common\helpers\Language::get_languages();

        //////////// images
        //{Yii::getAlias('@web')}/images/
        $image_path = DIR_WS_CATALOG_IMAGES . 'products' . '/' . $pInfo->products_id . '/';
        //$image_path = Yii::getAlias('@web');
        $image_path_upload = \Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;


        $images = [];

        // {{
        $_ext_images_array = [];
        foreach (Images::getImageTypes() as $image_type) {
            $_ext_images_array[$image_type['image_types_id']] = [
                'image_types_id' => $image_type['image_types_id'],
                'image_types_name' => $image_type['image_types_name'],
                'image_size' => $image_type['image_types_x'] . 'x' . $image_type['image_types_y'],
                'image_url' => '',
            ];
        }
        // }}
        $image_clone_mode = false;
        $image_product_id = $pInfo->products_id;
        if (empty($pInfo->products_id) && $pInfo->parent_products_id) {
            $image_clone_mode = true;
            $image_product_id = $pInfo->parent_products_id;
        }

        $images_query = tep_db_query("SELECT id.*, i.* FROM " . TABLE_PRODUCTS_IMAGES . " AS i LEFT JOIN " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " AS id ON (i.products_images_id=id.products_images_id AND id.language_id=0) WHERE i.products_id = '" . (int)$image_product_id . "' ORDER BY i.sort_order");
        while ($images_data = tep_db_fetch_array($images_query)) {
            // {{
            $images_data['use_external_images'] = !!$images_data['use_external_images'];
            $images_data['external_images'] = $_ext_images_array;
            // }}

            $uploaded_ws_path = '';
            $preload_image = '';
            $description = [];
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $images_description_query = tep_db_query("SELECT * FROM " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " WHERE language_id = '" . (int)$languages[$i]['id'] . "' AND products_images_id = '" . (int)$images_data['products_images_id'] . "'");
                $images_description = tep_db_fetch_array($images_description_query);
                $imageSize = null;
                $imageSrc = DIR_FS_CATALOG . DIR_WS_IMAGES . 'products' . '/' . $images_data['products_id'] . '/' . ($images_description['products_images_id'] ?? null) . '/' . ($images_description['hash_file_name'] ?? null);
                if (file_exists($imageSrc)) {
                    try {
                        $imageSize = getimagesize($imageSrc);
                    } catch (\Exception $ex) {
                    }
                    if ($image_clone_mode) {
                        if (@copy($imageSrc, $image_path_upload.$images_description['orig_file_name'])){
                            $uploaded_ws_path = \Yii::getAlias('@web/uploads/').$images_description['orig_file_name'];
                            $preload_image = $images_description['orig_file_name'];
                        };
                    }
                }
                if ($image_clone_mode) {
                    $images_description['hash_file_name'] = '';
                }
                $description[$languages[$i]['id']] = [
                    'key' => ($i + 1),
                    'id' => $languages[$i]['id'],
                    'code' => $languages[$i]['code'],
                    'name' => $languages[$i]['name'],
                    'logo' => (isset($languages[$i]['logo'])?$languages[$i]['logo']:''),
                    'image_title' => $images_description['image_title'] ?? null,
                    'image_alt' => $images_description['image_alt'] ?? null,
                    'orig_file_name' => $images_description['orig_file_name'] ?? null,
                    'use_origin_image_name' => $images_description['use_origin_image_name'] ?? null,
                    'hash_file_name' => $images_description['hash_file_name'] ?? null,
                    'file_name' => $images_description['file_name'] ?? null,
                    'alt_file_name' => $images_description['alt_file_name'] ?? null,
                    'no_watermark' => $images_description['no_watermark'] ?? null,
                    'external_image_original' => $images_description['external_image_original'] ?? null,
                    'image_name' => (empty($images_description['hash_file_name']) ? $uploaded_ws_path : $image_path . $images_description['products_images_id'] . '/' . $images_description['hash_file_name']),
                    'preload' => $preload_image,
                    'imageSize' => $imageSize,
                    'use_external_images' => !!($images_description['use_external_images'] ?? null),
                    'external_images' => $_ext_images_array,
                    'link_video_id' => $images_description['link_video_id'] ?? null,
                ];
            }

            $inventory = [];
            if (is_array($view->selectedInventory ?? null)) {
                foreach ($view->selectedInventory as $key => $value) {
                    $check_data = tep_db_query("SELECT products_images_id FROM " . TABLE_PRODUCTS_IMAGES_INVENTORY . " WHERE products_images_id='" . $images_data['products_images_id'] . "' AND  inventory_id = '" . $value['id'] . "'");
                    if (tep_db_num_rows($check_data)) {
                        $inventory[$key] = 1;
                    } else {
                        $inventory[$key] = 0;
                    }
                }
            }

            // {{
            $_image_url = (empty($images_data['hash_file_name']) ? '' : $image_path . $images_data['products_images_id'] . '/' . $images_data['hash_file_name']);
            $get_image_url_data_r = tep_db_query(
                "SELECT * FROM " . TABLE_PRODUCTS_IMAGES_EXTERNAL_URL . " " .
                "WHERE products_images_id='" . (int)$images_data['products_images_id'] . "'"
            );
            if (tep_db_num_rows($get_image_url_data_r) > 0) {
                while ($_image_url_data = tep_db_fetch_array($get_image_url_data_r)) {
                    if ($_image_url_data['language_id'] == 0) {
                        if (!isset($images_data['external_images'][$_image_url_data['image_types_id']])) continue;
                        $images_data['external_images'][$_image_url_data['image_types_id']]['image_url'] = $_image_url_data['image_url'];
                        if ($images_data['use_external_images']) {
                            $_image_url = $_image_url_data['image_url'];
                        }
                    } else {
                        if (!isset($description[$_image_url_data['language_id']][$_image_url_data['image_types_id']])) continue;
                        $description[$_image_url_data['language_id']][$_image_url_data['image_types_id']]['image_url'] = $_image_url_data['image_url'];
                    }
                }
            }
            // }}

            $imageSize = null;

            $preload_image = '';
            $uploaded_ws_path = '';
            $imageSrc = DIR_FS_CATALOG . DIR_WS_IMAGES . 'products' . '/' . $images_data['products_id'] . '/' . $images_data['products_images_id'] . '/' . $images_data['hash_file_name'];
            if (file_exists($imageSrc)) {
                try {
                    $imageSize = getimagesize($imageSrc);
                } catch (\Exception $ex) {
                }
                if ($image_clone_mode) {
                    if (@copy($imageSrc, $image_path_upload.$images_data['orig_file_name'])){
                        $uploaded_ws_path = \Yii::getAlias('@web/uploads/').$images_data['orig_file_name'];
                        $_image_url = $uploaded_ws_path;
                        $preload_image = $images_data['orig_file_name'];
                    };
                }
            }
            if ($image_clone_mode) {
                $images_data['hash_file_name'] = '';
            }
            $images[] = [
                'products_images_id' => $image_clone_mode?0:$images_data['products_images_id'],
                'default_image' => $images_data['default_image'],
                'image_status' => $images_data['image_status'],
                'image_name' => (empty($images_data['hash_file_name']) ? $uploaded_ws_path : $image_path . $images_data['products_images_id'] . '/' . $images_data['hash_file_name']),
                'image_url' => $_image_url,
                'preload' => $preload_image,
                // for language_id = 0
                'image_title' => $images_data['image_title'],
                'image_alt' => $images_data['image_alt'],
                'orig_file_name' => $images_data['orig_file_name'],
                'use_origin_image_name' => $images_data['use_origin_image_name'],
                'hash_file_name' => $images_data['hash_file_name'],
                'file_name' => $images_data['file_name'],
                'alt_file_name' => $images_data['alt_file_name'],
                'no_watermark' => $images_data['no_watermark'],
                'external_image_original' => $images_data['external_image_original'],
                'use_external_images' => !!$images_data['use_external_images'],
                'external_images' => $images_data['external_images'],
                'link_video_id' => $images_data['link_video_id'],
                //
                'description' => array_values($description),
                'inventory' => $inventory,
                'imageSize' => $imageSize,
            ];
            if ($ext = \common\helpers\Extensions::isAllowed('ProductImagesByPlatform')) {
                $ext::imageView($images, $images_data);
            }
        }
        $view->images = $images;
        $view->imagesQty = count($images);
    }

}