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

namespace backend\controllers;

class Uploads
{

  public static function move($file_name)
  {
    $path = \Yii::getAlias('@webroot');
    $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
		
    $upload_file = $path . $file_name;
    $path2 = \Yii::getAlias('@webroot');
    $path2 .= DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
    $copy_file = $file_name;
    $i = 1;
    $dot_pos = strrpos($copy_file, '.');
    $end = substr($copy_file, $dot_pos);
    $temp_name = $copy_file;
    while (is_file($path2 . $temp_name)){
      $temp_name = substr($copy_file, 0, $dot_pos) . '-' . $i . $end;		
			$temp_name = str_replace(' ', '_', $temp_name);
      $i++;
    }

    @copy($upload_file, $path2 . $temp_name);
    @unlink($upload_file);

    return $temp_name;
  }

}
