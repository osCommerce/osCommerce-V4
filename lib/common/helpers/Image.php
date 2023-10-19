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
use common\models\BannersGroupsImages;
use common\models\BannersGroupsSizes;
use common\models\BannersLanguages;
use common\models\ImageTypes;
use yii\helpers\ArrayHelper;
use common\models\ProductsImages;
use common\classes\Images;
use yii\helpers\FileHelper;

class Image {

    public static function copyProductImages($fromProductId, $toProductId) {
      $imgs = ProductsImages::find()//->with(['description', 'imagesAttributes', 'externalUrl', 'inventory'])
          ->andWhere(['products_id' => $fromProductId])->asArray()->all();
      if (is_array($imgs)) {
        $copyModels = [
            '\common\models\ProductsImagesAttributes' => 'products_images_id',
            '\common\models\ProductsImagesDescription' => 'products_images_id',
            '\common\models\ProductsImagesExternalUrl' => 'products_images_id',
        ];

        $basePath = \common\classes\Images::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR;
        //$productsId.'/' .$imageId
        foreach ($imgs as $img) {
          $tmp = $img;
          foreach (['description', 'attributes', 'externalUrl', 'inventory', 'products_images_id'] as $key) {
            unset($tmp[$key]);
          }
          $tmp['products_id'] = $toProductId;
          try {
            $copyModel = new ProductsImages();
            $copyModel->setAttributes($tmp, false);
            $copyModel->loadDefaultValues(true);
            $copyModel->save(false);
            $newImageId = $copyModel->products_images_id;
            \yii\helpers\BaseFileHelper::copyDirectory($basePath . $fromProductId . DIRECTORY_SEPARATOR . $img['products_images_id'],
                $basePath . $toProductId . DIRECTORY_SEPARATOR . $newImageId);

            foreach ($copyModels as $copyModelClass=>$copyProductColumn) {
              if ( !class_exists($copyModelClass) ) {
                  continue;
              }

              call_user_func_array([$copyModelClass,'deleteAll'], [[$copyProductColumn => $newImageId]]);
              $sourceCollection = call_user_func_array([$copyModelClass,'findAll'], [[$copyProductColumn => $img['products_images_id']]]);
              foreach ($sourceCollection as $originModel) {
                $__data = $originModel->getAttributes();
                $__data[$copyProductColumn] = $newImageId;
                $copyModel = \Yii::createObject($copyModelClass);
                if ( $copyModel instanceof \yii\db\ActiveRecord ) {
                  $copyModel->setAttributes($__data, false);
                  $copyModel->loadDefaultValues(true);
                  $copyModel->save(false);
                }
              }
            }

          } catch (\Exception $ex) {
            \Yii::error($ex->getMessage());
          }
        }
      }
    }

    public static function getNewSize($pic, $reqW, $reqH) {
        $size = @GetImageSize($pic);
        if (!is_array($size)) {
            $size = [0,0];
        }
        if ($size[0] == 0 || $size[1] == 0) {
            $newsize[0] = $reqW;
            $newsize[1] = $reqH;
            return $newsize;
        }

        $scale = @min(intval($reqW) / intval($size[0]), intval($reqH) / intval($size[1]));
        $newsize[0] = $size[0] * $scale;
        $newsize[1] = $size[1] * $scale;
        return $newsize;
    }

    public static function info_image($image, $alt, $width = '', $height = '') {
        if (tep_not_null($image) && (file_exists(DIR_FS_CATALOG_IMAGES . $image))) {
            if ($width != '' && $height != '') {
                $size = @GetImageSize(DIR_FS_CATALOG_IMAGES . $image);

                if (!($size[0] <= $width && $size[1] <= $height)) {
                    $newsize = self::getNewSize(DIR_FS_CATALOG_IMAGES . $image, $width, $height);

                    $width = $newsize[0];
                    $height = $newsize[1];
                } else {
                    $width = $size[0];
                    $height = $size[1];
                }
            }
            $image = tep_image(DIR_WS_CATALOG_IMAGES . $image, $alt, $width, $height);
        } else {
            $image = TEXT_IMAGE_NONEXISTENT;
        }
        return $image;
    }

    public static function getCategoriesAdditionalImages($categoriesId)
    {
        $imageTypesId = ImageTypes::findOne(['image_types_name' => 'Category gallery add'])->image_types_id ?? 0;
        $where['categories_id'] = $categoriesId;
        $where['image_types_id'] = $imageTypesId;

        $categoriesImages = \common\models\CategoriesImages::find()
            ->where($where)
            ->asArray()
            ->orderBy('sort_order')
            ->all();

        $cImages = [];
        foreach ($categoriesImages as $categoriesImage) {
            $catalog = defined("DIR_WS_CATALOG_IMAGES") ? DIR_WS_CATALOG_IMAGES : DIR_WS_IMAGES;
            $categoriesImage['image_url'] = $catalog . $categoriesImage['image'];
            $cImages[$categoriesImage['platform_id']][] = $categoriesImage;
        }

        return $cImages;
    }

    public static function saveCategoriesAdditionalImages($images, $categoriesId)
    {
        $imageTypesId = ImageTypes::findOne(['image_types_name' => 'Category gallery add'])->image_types_id ?? 0;
        foreach (array_merge([['id' => 0]], \common\classes\platform::getList(false)) as $platform) {

            $categoriesImages = \common\models\CategoriesImages::find()->where([
                'categories_id' => $categoriesId,
                'platform_id' => $platform['id'],
                'image_types_id' => $imageTypesId,
            ])->all();
            foreach ($categoriesImages as $cImage) {
                if (
                    !is_array($images['image_id'][$platform['id']]) ||
                    !in_array($cImage->categories_images_id, $images['image_id'][$platform['id']])
                ) {
                    $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . $cImage->image;
                    if (file_exists($image_location)) @unlink($image_location);

                    Images::removeResizeImages($cImage->image);
                    Images::removeWebp($cImage->image);

                    $cImage->delete();
                }
            }

            if (!isset($images['image_id'][$platform['id']]) || !is_array($images['image_id'][$platform['id']])) {
                continue;
            }

            foreach ($images['image_id'][$platform['id']] as $key => $image_id) {
                if ($image_id) {
                    $categoriesImages = \common\models\CategoriesImages::findOne($image_id);
                } else {

                    $categoriesImages = new \common\models\CategoriesImages();

                    $imgPath = DIR_WS_IMAGES . 'categories' . DIRECTORY_SEPARATOR . $categoriesId . DIRECTORY_SEPARATOR;
                    $val = \backend\design\Uploads::move($images['image'][$platform['id']][$key], $imgPath, true);
                    $val = str_replace(DIR_WS_IMAGES, '', $val);
                    $categoriesImages->image = $val;
                    Images::createWebp($val, true);
                    Images::createResizeImages($val, 'Category gallery add', true);

                    $categoriesImages->categories_id = $categoriesId;
                    $categoriesImages->platform_id = $platform['id'];
                    $categoriesImages->image_types_id = $imageTypesId;
                }
                $categoriesImages->sort_order = $key;
                $categoriesImages->save(false);
            }
        }
    }

    public static function info_image_if_exists($image, $alt, $width = '', $height = '') {
        return str_replace(TEXT_IMAGE_NONEXISTENT, '', self::info_image($image, $alt, $width, $height));
    }


    /**
     * Prepare image for saving: move, remove, resize image
     *
     * @param string $oldValue - value from db
     * @param string $newValue - value from form
     * @param string $upload - upload value from form, image with path from root or admin/uploads folder which has to be moved to the $path
     * @param string $path - path to image destination, starts from image dir if $themes==false and from root if $themes==true
     * @param boolean $remove - has to remove $oldImage
     * @param boolean $themes - is it theme image
     * @param array $resize - [width, height, fit] - settings for resize image
     * @return string - filename with path for saving in db
     */
    public static function prepareSavingImage($oldValue, $newValue, $upload = '', $path = '', $remove = false, $themes = false, $resize = [])
    {
        $image = $oldValue;

        if (!$newValue && !$upload && !$remove && !($resize['parentImage'] ?? false)) {
            return '';
        }

        if (($resize['parentImage'] ?? false) && ($resize['parentOldImage'] ?? false) &&
            $resize['parentImage'] != $resize['parentOldImage'] && !$upload
        ) {
            $parentOldImage = pathinfo($resize['parentOldImage'], PATHINFO_FILENAME);
            $oldImage = pathinfo($oldValue, PATHINFO_FILENAME);
            if (strpos($oldImage, $parentOldImage) === 0 &&
                pathinfo($oldValue, PATHINFO_EXTENSION ) == pathinfo($resize['parentOldImage'], PATHINFO_EXTENSION)
            ) {
                $remove = true;
                $upload = DIR_WS_IMAGES . $resize['parentImage'];
            }
        } elseif (($resize['parentImage'] ?? false) && !$upload && !$newValue) {
            $upload = DIR_WS_IMAGES . $resize['parentImage'];
        }

        if ($themes) {
            $fsPath = DIR_FS_CATALOG;
        } else {
            $fsPath = Images::getFSCatalogImagesPath();
            $path = DIR_WS_IMAGES . $path;
        }

        if ($remove && $oldValue) {
            if (is_file($fsPath . $oldValue)) {
                unlink($fsPath . $oldValue);
            }
            $pos = strripos($oldValue, '.');
            $name = substr($oldValue, 0, $pos+1) . 'webp';
            if (is_file($fsPath . $name)) {
                unlink($fsPath . $name);
            }
        }

        if (!$newValue && !$upload) {
            return '';
        }

        if ($oldValue == $newValue && !$upload && !$remove) {
            return $oldValue;
        }

        if ($themes && dirname($oldValue) == dirname($newValue) && !$upload) {
            return $newValue;
        }

        if (in_array(substr($upload, 0, 7), ['images/', 'themes/'])) {
            $fileFrom = DIR_FS_CATALOG . $upload;
        } else {
            $fileFrom = DIR_FS_CATALOG . 'uploads/' . $upload;
        }
        $type = false;
        if (is_file($fileFrom)) {
            $type = explode('/', mime_content_type($fileFrom));
        }

        if (($resize['width'] ?? false) && $upload && $type && $type[0] == 'image' && substr($upload, -3) != 'svg') {

            $imgExplode = explode('/', str_replace('\\', '/', $upload));
            $imgName = end($imgExplode);
            $pos = strrpos($imgName, '.');
            $name = substr($imgName, 0, $pos);
            $ext = substr($imgName, $pos);

            $newImg = $path . '/' . $name . '[' . $resize['width'] . ']' . $ext;

            if (!is_file(DIR_FS_CATALOG . $newImg) && $ext !== 'svg') {
                $size = @GetImageSize($fileFrom);
                if (isset($size[0]) && $size[0]) {

                    if ($resize['width'] && $resize['height'] && ($resize['fit'] == 'cover' || !$resize['fit'])) {
                        $scale = @max($resize['width'] / $size[0], $resize['height'] / $size[1]);

                        $width = $size[0] * $scale;
                        $height = $size[1] * $scale;

                    } elseif (!$resize['width'] && $resize['height']) {
                        $width = ($size[0] * $resize['height']) / $size[1];
                        $height = $resize['height'];
                    }else {
                        $width = $resize['width'];
                        $height = ($size[1] * $resize['width']) / $size[0];
                    }
                    Images::tep_image_resize($fileFrom, DIR_FS_CATALOG . $newImg, $width, $height, false);
                    Images::createWebp($newImg, true);
                    $image = str_replace(DIR_WS_IMAGES, '', str_replace('\\', '/', $newImg));
                }
            } else if (is_file(DIR_FS_CATALOG . $newImg)) {
                $image = str_replace(DIR_WS_IMAGES, '', str_replace('\\', '/', $newImg));
            }

        } elseif ($upload) {
            $img = str_replace('uploads' . DIRECTORY_SEPARATOR, '', $upload);
            $val = \backend\design\Uploads::move($img, $path);
            $image = str_replace(DIR_WS_IMAGES, '', str_replace('\\', '/', $val));
        }
        return $image;
    }
}
