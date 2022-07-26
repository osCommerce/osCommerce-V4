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

function tep_image_resize($image, $t_location, $thumbnail_width, $thumbnail_height)
{
  $image = str_replace("/./", "/", str_replace("//", "/", $image));
  $t_location = str_replace("/./", "/", str_replace("//", "/", $t_location));
  $size = @GetImageSize($image);
  if (($thumbnail_width >= $size[0]) && ($thumbnail_height >= $size[1])) {
    if ($image != $t_location)
      @copy($image, $t_location);
    return true;
  }
  if (IMAGE_RESIZE != 'GD' && IMAGE_RESIZE != 'ImageMagick') {
    return false;
  }
  if (IMAGE_RESIZE == 'ImageMagick') {
    if (is_executable(CONVERT_UTILITY)) {
      @exec(CONVERT_UTILITY . ' -thumbnail ' . $thumbnail_width . 'x' . $thumbnail_height . ' ' . $image . ' ' . $t_location);
      return true;
    }
    return false;
  }
  elseif (IMAGE_RESIZE == 'GD') {
    if (function_exists("gd_info")) {
      $scale = @min($thumbnail_width / $size[0], $thumbnail_height / $size[1]);
      $x = $size[0] * $scale;
      $y = $size[1] * $scale;

      switch ($size[2]) {
        case 1 : // GIF
          $im = @ImageCreateFromGif($image);
          break;
        case 3 : // PNG
          $im = @ImageCreateFromPng($image);
          if ($im) {
            if (function_exists('imageAntiAlias'))
            {
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
        $imPic = @ImageCreateTrueColor($x, $y);
      if ($imPic == 0)
        $imPic = @ImageCreate($x, $y);
      if ($imPic != 0) {
        @ImageInterlace($imPic, 1);
        if (function_exists('imageAntiAlias'))
        {
          @imageAntiAlias($imPic, true);
        }
        @imagealphablending($imPic, false);
        @imagesavealpha($imPic, true);
        $transparent = @imagecolorallocatealpha($imPic, 255, 255, 255, 0);
        for ($i = 0; $i < $x; $i++) {
          for ($j = 0; $j < $y; $j++) {
            @imageSetPixel($imPic, $i, $j, $transparent);
          }
        }
        if (function_exists('ImageCopyResampled')) {
          $resized = @ImageCopyResampled($imPic, $im, 0, 0, 0, 0, $x, $y, $size[0], $size[1]);
        }
        if (!$resized) {
          @ImageCopyResized($imPic, $im, 0, 0, 0, 0, $x, $y, $size[0], $size[1]);
        }
      } else {
        return false;
      }

      if ($size[2] == 3) {
        @imagePNG($imPic, $t_location);
      } else {
        @imageJPEG($imPic, $t_location, 100);
      }
      if (is_file($t_location))
        return true;
    }
  }
  return false;
}
