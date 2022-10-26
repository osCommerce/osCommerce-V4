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
use yii\helpers\ArrayHelper;
use common\models\ProductsImages;
use common\classes\Images;

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

    public static function getCategorisAdditionlImages($categoriesId)
    {
        $where['categories_id'] = $categoriesId;

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

    public static function saveCategorisAdditionlImages($images, $categoriesId)
    {
        foreach (array_merge([['id' => 0]], \common\classes\platform::getList(false)) as $platform) {

            $categoriesImages = \common\models\CategoriesImages::find()->where([
                'categories_id' => $categoriesId,
                'platform_id' => $platform['id'],
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
                    Images::createResizeImages($val, 'Category galery small', true);

                    $categoriesImages->categories_id = $categoriesId;
                    $categoriesImages->platform_id = $platform['id'];
                }
                $categoriesImages->sort_order = $key;
                $categoriesImages->save(false);
            }
        }
    }

    public static function info_image_if_exists($image, $alt, $width = '', $height = '') {
        return str_replace(TEXT_IMAGE_NONEXISTENT, '', self::info_image($image, $alt, $width, $height));
    }

}
