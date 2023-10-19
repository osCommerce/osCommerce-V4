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

namespace backend\design;

use common\classes\Images;
use common\models\ProductsImagesDescription;

class Uploads
{

  public static function move($file_name, $folder = DIR_WS_IMAGES, $show_path = true)
  {
      $uploaded = false;
      if (in_array(substr($file_name, 0, 7), ['images/', 'themes/'])) {
          $path = DIR_FS_CATALOG;
      } else {
          $path = \Yii::getAlias('@webroot');
          $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
          $uploaded = true;
      }

    $upload_file = $path . $file_name;

    if (is_file($upload_file)) {
      $folders_arr = explode('/', $folder);
      $path2 = \Yii::getAlias('@webroot');
      $path2 .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
      $path3 = trim(str_replace('\\', '/', $folder), '/') . '/';
      foreach ($folders_arr as $item) {
          if (!$item) continue;
        $path2 .= $item . DIRECTORY_SEPARATOR;
        if (!file_exists($path2)) {
          mkdir($path2, 0777, true);
          @chmod($path2,0777);
        }
      }

      $splitFileName = explode('/', $file_name);
      if ($splitFileName[0] == 'products') {
          $img = ProductsImagesDescription::find()->where([
              'hash_file_name' => end($splitFileName),
          ])->asArray()->one();
          $copy_file = (isset($img['orig_file_name']) ? $img['orig_file_name'] : end($splitFileName));
      } else {
          $copy_file = basename($file_name);
      }

      $i = 1;
      $dot_pos = strrpos($copy_file, '.');
      $end = substr($copy_file, $dot_pos);
      $temp_name = $copy_file;
      while (is_file($path2 . $temp_name)) {
        $temp_name = substr($copy_file, 0, $dot_pos) . '-' . $i . $end;
        $temp_name = str_replace(' ', '_', $temp_name);
        $i++;
      }

      @copy($upload_file, $path2 . $temp_name);
      @chmod($path2 . $temp_name,0666);
      if ($uploaded) {
          @unlink($upload_file);
      }

      \common\classes\Images::createWebp($path3 . $temp_name, true);

      return ($show_path ? $path3 : '') . $temp_name;
    } else {
      return false;
    }
  }

  public static $archiveImages = [];

    public static function addArchiveImages($name, $value){

        $image = $value;
        $image_ = $value;
        if ($name == 'background_image' || $name == 'logo' ){
            $path_arr = explode(DIRECTORY_SEPARATOR, $value);
            $image = end($path_arr);
            $image_ = '$$' . $image;
            if (count($path_arr) == 1) {
                $old = 'images/' . $value;
            } else {
                $old = $value;
            }

            $change = false;

            foreach (self::$archiveImages as $item){
                if ($old == $item['old']){
                    return '$$' . $item['new'];
                }
                if ($image == $item['new']){
                    $change = true;
                }
            }

            $i = 1;
            $dot_pos = strrpos($image, '.');
            $end = substr($image, $dot_pos);
            $temp_name = $image;
            while ($change){
                $has_name = false;
                foreach (self::$archiveImages as $item){
                    if ($temp_name == $item['new']){
                        $has_name = true;
                        break;
                    }
                }
                if (!$has_name){
                    $change = false;
                    $image = $temp_name;
                    $image_ = '$$' . $temp_name;
                }
                $temp_name = substr($image, 0, $dot_pos) . '-' . $i . $end;
                $i++;
            }
            self::$archiveImages[] = ['old' => $old, 'new' => $image];
        }

        return $image_;
    }

}
