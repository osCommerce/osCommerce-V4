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
use yii\helpers\FileHelper;
use yii\web\Controller;
use common\classes\Images;

/**
 * Image controller
 */
class ImageController extends Sceleton {

    public function actionIndex() {

    }
    
    public function actionPath() {
        $src = tep_db_prepare_input(Yii::$app->request->get('src'));
        \common\classes\Images::waterMark($src);
    }

    public function actionCached()
    {
      $this->layout = false;
      $requested_image = /*tep_db_prepare_input*/(Yii::$app->getRequest()->get('image',''));
      if ( empty($requested_image) ) {
          $requested_image = Yii::$app->request->getPathInfo();
      }
      $requested_image = str_replace('+',' ',$requested_image);

      if ( !empty($requested_image) && is_file(\common\classes\Images::getFSCatalogImagesPath().$requested_image) ) {
          $output_file = \common\classes\Images::getFSCatalogImagesPath().$requested_image;
          \common\classes\Images::sendImageToBrowser($output_file);
          die;
      }

      $slugStore = \common\helpers\Seo::makeSlug(Yii::$app->get('platform')->config()->const_value('STORE_NAME'));
      $isWatermarkedImage = strpos($requested_image,$slugStore.'/')===0;
      $work_platform_id = Yii::$app->get('platform')->config()->getId();

      if ( !$isWatermarkedImage && defined('SEO_IMAGE_URL_PARTS_NAME') && SEO_IMAGE_URL_PARTS_NAME=='False' ) {
          header("HTTP/1.0 404 Not Found");
          die;
      }else
      if ( preg_match('#(?P<seoRef>.*?)/(products\/\d+/)?(?P<nameInsideProductDir>(?P<imageId>[\d]+)/(?<imageSize>\d+x\d+)/(?P<lang_code>[a-zA-Z]{2}/)?(?P<image_name>.+))$#',$requested_image, $imagePattern) ){
          if ( preg_match('#products/\d+#',$imagePattern['seoRef']) || count(explode('/',$imagePattern['seoRef']))>10 ){
              header("HTTP/1.0 400 Bad Request");
              die;
          }
          $output_file = '';
          if ( (int)$imagePattern['imageId']>0 ) {

              $check_image_ref_r = tep_db_query(
                  "SELECT pi.products_id, pi.products_images_id, pid.hash_file_name, pid.file_name ".
                  "FROM ".TABLE_PRODUCTS_IMAGES." pi ".
                  " INNER JOIN ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." pid ON pi.products_images_id=pid.products_images_id AND pid.language_id=0 ".
                  "WHERE pi.products_images_id='".(int)$imagePattern['imageId']."' "
              );
              if ( tep_db_num_rows($check_image_ref_r)>0 ) {
                  $_image_ref = tep_db_fetch_array($check_image_ref_r);
                  if ( $isWatermarkedImage ) {
                      $watermark_images = \common\classes\Images::getWatermarkImage($work_platform_id, (int)$imagePattern['imageSize']);
                      if ( !is_array($watermark_images) ) {
                          \common\helpers\System::symlink(
                              \common\classes\Images::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR . (int)$_image_ref['products_id'],
                              \common\classes\Images::getFSCatalogImagesPath() . $imagePattern['seoRef']
                          );
                          tep_db_perform(TABLE_IMAGE_COPY_REFERENCE,[
                              'platform_id' => $work_platform_id,
                              'products_id' => (int)$_image_ref['products_id'],
                              'products_image_id' => (int)$_image_ref['products_images_id'],
                              'filename' => $imagePattern['seoRef'],
                              'date_added' => 'now()',
                          ]);
                      }else{
                          $originalImageName =
                              \common\classes\Images::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR . (int)$_image_ref['products_id'].
                              DIRECTORY_SEPARATOR.$imagePattern['nameInsideProductDir'];
                          if ( !is_file($originalImageName) ){
                              header("HTTP/1.0 404 Not Found");
                              die;
                          }
                          $image_content = Images::applyWatermark($originalImageName, $watermark_images, 'string');

                          if (is_array($watermark_images)) {
                              $watermark_mtime = 0;
                              foreach ($watermark_images as $watermark_image_path) {
                                  $watermark_mtime = max($watermark_mtime, filemtime($watermark_image_path));
                              }
                          } else {
                              $watermark_mtime = 0;
                          }
                          $watermarkedImageMtimeMustBe = max(filemtime($originalImageName), $watermark_mtime);

                          $watermarkedImageName = \common\classes\Images::getFSCatalogImagesPath() . $requested_image;

                          try{
                              FileHelper::createDirectory(dirname($watermarkedImageName),0777);
                          }catch (\Exception $ex){}

                          $tmp_file = tempnam(dirname($watermarkedImageName), basename($watermarkedImageName));
                          if ($tmp_file_h = fopen($tmp_file,'wb')) {
                              fwrite($tmp_file_h, $image_content);
                              fclose($tmp_file_h);

                              @chmod($tmp_file,0666);
                              touch($tmp_file, $watermarkedImageMtimeMustBe);
                              rename($tmp_file, $watermarkedImageName);

                              tep_db_perform(TABLE_IMAGE_COPY_REFERENCE,[
                                  'platform_id' => $work_platform_id,
                                  'products_id' => (int)$_image_ref['products_id'],
                                  'products_image_id' => (int)$_image_ref['products_images_id'],
                                  'filename' => $requested_image,
                                  'date_added' => 'now()',
                              ]);

                              \common\classes\Images::sendImageToBrowser($watermarkedImageName);
                              die;
                          }else{
                              $_image_info = array();
                              $size = getimagesizefromstring($image_content, $_image_info);
                              header('Content-type: ' . $size['mime']);
                              echo $image_content;
                              die;
                          }
                      }

                  }else {
                      \common\helpers\System::symlink(
                          \common\classes\Images::getFSCatalogImagesPath() . 'products' . DIRECTORY_SEPARATOR . (int)$_image_ref['products_id'],
                          \common\classes\Images::getFSCatalogImagesPath() . $imagePattern['seoRef']
                      );
                      tep_db_perform(TABLE_IMAGE_COPY_REFERENCE,[
                          'platform_id' => $work_platform_id,
                          'products_id' => (int)$_image_ref['products_id'],
                          'products_image_id' => (int)$_image_ref['products_images_id'],
                          'filename' => $imagePattern['seoRef'],
                          'date_added' => 'now()',
                      ]);
                  }
                  $output_file = \common\classes\Images::getFSCatalogImagesPath() . $requested_image;
              }
          }

          if ( !empty($output_file) && is_file($output_file) ) {
              \common\classes\Images::sendImageToBrowser($output_file);
          }
          die;

      }elseif ( preg_match('#(?=(?<responsiveAddon>@((?P<size1>\d+[w|h])(?P<size2>\d+[w|h])?)\..{3,4}))#',$requested_image, $responsive) ) {
          $originFilename = substr($requested_image, 0, -strlen($responsive['responsiveAddon']));
          if ( is_file(\common\classes\Images::getFSCatalogImagesPath().$originFilename) ) {
              $imageInfo = getimagesize(\common\classes\Images::getFSCatalogImagesPath().$originFilename);

              $imageWidth = 0;
              $imageHeight = 0;

              if ( !empty($responsive['size2']) ){
                  if (substr($responsive['size2'],-1)=='w'){
                      $imageWidth = (int)$responsive['size2'];
                  }else{
                      $imageHeight = (int)$responsive['size2'];
                  }
              }
              if ( !empty($responsive['size1']) ){
                  if (substr($responsive['size1'],-1)=='w'){
                      $imageWidth = (int)$responsive['size1'];
                  }else{
                      $imageHeight = (int)$responsive['size1'];
                  }
              }
              if ( empty($imageHeight) ) $imageHeight = null;
              if ( empty($imageWidth) ) $imageWidth = null;

              if ( is_null($imageHeight) ){
                  $dim = \common\classes\Images::calculateImageSize($imageInfo[0],$imageInfo[1], $imageWidth,null, 'inside');
              }else{
                  $dim = \common\classes\Images::calculateImageSize($imageInfo[0],$imageInfo[1], $imageWidth,$imageHeight, 'outside');
              }
              $imageWidth = $dim['width'];
              $imageHeight = $dim['height'];

              $targetName = \common\classes\Images::getFSCatalogImagesPath().$requested_image;
              \common\classes\Images::tep_image_resize(
                  \common\classes\Images::getFSCatalogImagesPath().$originFilename,
                  $targetName,
                  $imageWidth, $imageHeight
              );
              if ( is_file($targetName) ) {
                  tep_db_perform(TABLE_IMAGE_COPY_REFERENCE,[
                      'platform_id' => $work_platform_id,
                      'products_id' => -1,
                      'products_image_id' => 0,
                      'filename' => $requested_image,
                      'date_added' => 'now()',
                  ]);
                  if ( !empty($targetName) && is_file($targetName) ) {
                      \common\classes\Images::sendImageToBrowser($targetName, $imageInfo['mime']);
                  }
                  die;
              }
          }
      }

      header("HTTP/1.0 404 Not Found");
      die;
    }
    
}
