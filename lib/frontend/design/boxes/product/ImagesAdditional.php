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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use common\classes\Images as cImages;
use frontend\design\Info;

class ImagesAdditional extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        if (!is_array($this->params) ) $this->params = array();
        parent::init();
    }

    public function run()
    {
        if ( isset($this->params['uprid']) && $this->params['uprid']>0 ) {
            $show_uprid = $this->params['uprid'];
        }else {
            $show_uprid = Yii::$app->request->get('products_id',0);
        }

        if (!$show_uprid) {
            return '';
        }

        $images = \common\classes\Images::getImageList($show_uprid);
        if ( count($images)==0 ) {
            $show_uprid = \common\helpers\Inventory::get_prid($show_uprid);
            $images = \common\classes\Images::getImageList($show_uprid);
        }
        
        $languageId = (int)Yii::$app->settings->get('languages_id');
        $productId = (int)Yii::$app->request->get('products_id');
        
        $productsVideos = \common\models\ProductsVideos::find()->where(['products_id' => $productId])->asArray()->all();

        $video = [];
        foreach ($productsVideos as $item) {
            $video[$item['video_id']] = $item;
        }
        
        $imagesArr = [];
        $defaultImage = '';
        $count = 0;
        foreach( $images as $imgId => $__image ) {
            if (isset($video[$__image['link_video_id']])) {
                $item = $video[$__image['link_video_id']];
                
                if ($this->settings[0]['by_language'] && $item['language_id'] != $languageId ) {
                    continue;
                }
                if ($item['type'] == 1) {
                    $item['src'] = \common\classes\Images::getWSCatalogImagesPath() . 'products'
                        . DIRECTORY_SEPARATOR . $productId
                        . DIRECTORY_SEPARATOR . 'videos'
                        . DIRECTORY_SEPARATOR . $item['video'];
                    $item['video_type'] = '1';
                } else {
                    $item['code'] = '';
                    if (strrpos($item['video'], 'youtu.be')) {
                        preg_match_all("/\/([^\/^?]+)/", $item['video'], $arr);
                        $item['code'] = $arr[1][1];
                    } elseif (strrpos($item['video'], 'youtube.com') && strrpos($item['video'], '/watch?')) {
                        preg_match_all("/v=([a-zA-Z0-9\-\_]+)/", $item['video'], $arr);
                        $item['code'] = $arr[1][0];
                    }  elseif (strrpos($item['video'], 'youtube.com')) {
                        preg_match_all("/\/([^\/^?^\"]+)[\"\?]/", $item['video'], $arr);
                        $item['code'] = $arr[1][0];
                    } elseif (preg_match("/^[a-zA-z0-9]+$/", $item['video'])) {
                        $item['code'] = $item['video'];
                    }
                    $item['video_type'] = '0';
                    if (!$item['code']) {
                        continue;
                    }
                    $item['video_preview'] = "https://img.youtube.com/vi/".$item['code']."/0.jpg";
                    if (isset($__image['image']['Small']['url'])) {
                        $item['video_preview'] = $__image['image']['Small']['url'];
                    }
                    
                }
                $item['type'] = 'video';
                $imagesArr['video-' . $item['video_id']] = $item;

                if (!$defaultImage) {
                    $defaultImage = 'video-' . $item['video_id'];
                }

                unset($video[$__image['link_video_id']]);
            } else {
                $_srcsetSizes = cImages::getImageSrcsetSizes($show_uprid, 'Small', -1, $imgId);
                $images[$imgId]['srcset'] = $_srcsetSizes['srcset'];
                $images[$imgId]['sizes'] = $_srcsetSizes['sizes'];
                $images[$imgId]['type'] = 'image';
                $imagesArr['image-' . $imgId] = $images[$imgId];
                $count++;
                if ($count == 1) {
                    $defaultImage = 'image-' . $imgId;
                }
                if ($__image['defaut']) {
                    $defaultImage = 'image-' . $imgId;
                    Info::addJsData(['products' => [
                        $show_uprid => ['defaultImage' => 'image-' . $imgId]
                    ]]);
                }
            }
        }


        Info::addJsData(['widgets' => [
            $this->id => [
                'alignPosition' => $this->settings[0]['align_position'],
            ]]
        ]);

        foreach ($video as $item) {
            if ($this->settings[0]['by_language'] && $item['language_id'] != $languageId ) {
                continue;
            }
            if ($item['type'] == 1) {
                $item['src'] = \common\classes\Images::getWSCatalogImagesPath() . 'products'
                    . DIRECTORY_SEPARATOR . $productId
                    . DIRECTORY_SEPARATOR . 'videos'
                    . DIRECTORY_SEPARATOR . $item['video'];
                $item['video_type'] = '1';
            } else {
                $item['code'] = '';
                if (strrpos($item['video'], 'youtu.be')) {
                    preg_match_all("/\/([^\/^?]+)/", $item['video'], $arr);
                    $item['code'] = $arr[1][1];
                } elseif (strrpos($item['video'], 'youtube.com') && strrpos($item['video'], '/watch?')) {
                    preg_match_all("/v=([a-zA-Z0-9\-\_]+)/", $item['video'], $arr);
                    $item['code'] = $arr[1][0];
                }  elseif (strrpos($item['video'], 'youtube.com')) {
                    preg_match_all("/\/([^\/^?^\"]+)[\"\?]/", $item['video'], $arr);
                    $item['code'] = $arr[1][0];
                } elseif (preg_match("/^[a-zA-z0-9]+$/", $item['video'])) {
                    $item['code'] = $item['video'];
                }
                $item['video_type'] = '0';
                if (!$item['code']) {
                    continue;
                }
                $item['video_preview'] = "https://img.youtube.com/vi/".$item['code']."/0.jpg";
            }
            $item['type'] = 'video';
            $imagesArr['video-' . $item['video_id']] = $item;

            if (!$defaultImage) {
                $defaultImage = 'video-' . $item['video_id'];
            }
        }

        
        Info::addJsData(['products' => [
            $productId => [
                'images' => $imagesArr,
                'defaultImage' => $defaultImage
            ]
        ]]);

        if ($this->params['no_tpl']) {
            return json_encode($imagesArr);
        }

        return IncludeTpl::widget(['file' => 'boxes/product/images-additional.tpl', 'params' => [
            'images' => $imagesArr,
            'images_count' => count($imagesArr),
            'settings' => $this->settings
        ]]);
    }
}