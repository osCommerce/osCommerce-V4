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

/**
 * Images 
 */
class ImagesSource {

    protected $imageSize;
    protected $imageSource;
    
    public function __construct($image) {
        try{
            if (!function_exists("gd_info")) {
                throw new \Exception('GD Functions disabled ');
            }
            
            if (is_file($image)){
                $this->imageSize = @GetImageSize($image);
                $this->detectImageSource($image);
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }
    
    public function __destruct() {
        if ($this->imageSource){
            imagedestroy($this->imageSource);
        }
    }

    public function detectImageSource($image){
        if ($this->imageSize){
            switch ($this->imageSize[2]) {
                case 1 : // GIF
                    $this->imageSource = @ImageCreateFromGif($image);
                    break;
                case 3 : // PNG
                    $this->imageSource = @ImageCreateFromPng($image);
                    if ($this->imageSource) {
                        if (function_exists('imageAntiAlias')) {
                            @imageAntiAlias($this->imageSource, true);
                        }
                        @imageAlphaBlending($this->imageSource, true);
                        @imageSaveAlpha($this->imageSource, true);
                    }
                    break;
                case 2 : // JPEG
                    $this->imageSource = @ImageCreateFromJPEG($image);
                    break;
                default :
                    $this->imageSource = false;
            }
        } else {
            $this->imageSource = false;
        }
    }
    
    /*return @source*/
    public function getImageSource(){
        return $this->imageSource;
    }
    
    /*return @size&type*/
    public function getImageSize(){
        return $this->imageSize;
    }
    
    public function cropImage($imageSource, array $cropRectangle){
        return imagecrop($imageSource, $cropRectangle);
    }
    
    public function saveImageSource($imageSource, $destination = null, $quality = null){
        if ($this->imageSize && is_resource($imageSource)){
            switch ($this->imageSize[2]) {
                case 1 : // GIF
                    @imagegif($imageSource, $destination);
                    break;
                case 3 : // PNG
                    $quality = is_null($quality)? 9 : $quality;
                    @imagePNG($imageSource, $destination, $quality);
                    break;
                case 2 : // JPEG
                    $quality = is_null($quality)? 100 : $quality;
                    @imageJPEG($imageSource, $destination, $quality);
                    break;
                default :
                    break;
            }
            if (!is_null($destination)){
                return $destination;
            }
        }
    }
    
}
