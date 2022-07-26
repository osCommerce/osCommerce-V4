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

class Video extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $languageId = (int)Yii::$app->settings->get('languages_id');
        $productId = (int)Yii::$app->request->get('products_id');

        $productsVideos = \common\models\ProductsVideos::find()->where(['products_id' => $productId])->asArray()->all();

        $video = [];
        foreach ($productsVideos as $item) {
            if ($this->settings[0]['by_language'] && $item['language_id'] != $languageId ) {
                continue;
            }
            if ($item['type'] == 1) {
                $item['src'] = \common\classes\Images::getWSCatalogImagesPath() . 'products'
                    . DIRECTORY_SEPARATOR . $productId
                    . DIRECTORY_SEPARATOR . 'videos'
                    . DIRECTORY_SEPARATOR . $item['video'];
                $video[] = $item;
            } else {
                $item['code'] = '';
                if (strrpos($item['video'], 'youtu.be')) {
                    preg_match_all("/\/([^\/^?]+)/", $item['video'], $arr);
                    $item['code'] = $arr[1][1];
                } elseif (strrpos($item['video'], 'youtube.com')) {
                    preg_match_all("/\/([^\/^?^\"]+)[\"\?]/", $item['video'], $arr);
                    $item['code'] = $arr[1][0];
                } elseif (preg_match("/^[a-zA-z0-9]+$/", $item['video'])) {
                    $item['code'] = $item['video'];
                }
                if ($item['code']) {
                    $video[] = $item;
                }
            }
        }

        if (count($video) == 0) {
            return '';
        }

        return IncludeTpl::widget(['file' => 'boxes/product/video.tpl', 'params' => [
            'video' => $video,
            'settings' => $this->settings
        ]]);
    }
}