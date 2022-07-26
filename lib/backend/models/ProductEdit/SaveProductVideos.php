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

use yii;
use common\models\Products;
use backend\design\Uploads;

class SaveProductVideos
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
        $languages = \common\helpers\Language::get_languages();
        $productsId = $this->product->products_id;
        $path = $this->uploadsDirectory;

        $imagesDirectory = DIR_WS_IMAGES . 'products'
            . DIRECTORY_SEPARATOR . $productsId
            . DIRECTORY_SEPARATOR . 'videos'
            . DIRECTORY_SEPARATOR;

        $video = Yii::$app->request->post('video');
        $videoType = Yii::$app->request->post('video_type');
        $videoId = Yii::$app->request->post('video_id');
        //tep_db_query("delete from " . TABLE_PRODUCTS_VIDEOS . " where products_id  = '" . (int) $productsId . "'");
        $productsVideos = \common\models\ProductsVideos::find()->where(['products_id' => $productsId])->all();

        foreach ($productsVideos as $productsVideo) {
            if (empty($videoId[$productsVideo->language_id]) || !is_array($videoId) || !in_array($productsVideo->video_id, $videoId[$productsVideo->language_id])) {
                $productsVideo->delete();
                $imagesPath = \common\classes\Images::getFSCatalogImagesPath() . 'products'
                    . DIRECTORY_SEPARATOR . $productsId
                    . DIRECTORY_SEPARATOR . 'videos'
                    . DIRECTORY_SEPARATOR;
                if ( $productsVideo->video && is_file($imagesPath . $productsVideo->video) ) @unlink($imagesPath . $productsVideo->video);
            }
        }

        if (!is_array($video)) {
            return;
        }

        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];

            if (!isset($video[$language_id]) || !is_array($video[$language_id])) {
                continue;
            }
            foreach ($video[$language_id] as $key => $item) {

                if (!$item) {
                    continue;
                }
                if ($videoId[$language_id][$key]) {
                    continue;
                }

                if ($videoType[$language_id][$key] == '1') {
                    $item = Uploads::move($item, $imagesDirectory, false);
                }

                if ($videoId[$language_id][$key]) {
                    $productsVideos = \common\models\ProductsVideos::findOne(['video_id' => $videoId[$language_id][$key]]);
                } else {
                    $productsVideos = new \common\models\ProductsVideos();
                }

                $productsVideos->products_id = $productsId;
                $productsVideos->video = $item;
                $productsVideos->language_id = $language_id;
                $productsVideos->type = $videoType[$language_id][$key];
                $productsVideos->save();

            }
        }
    }
}