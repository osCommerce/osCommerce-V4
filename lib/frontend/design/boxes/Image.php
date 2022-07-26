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

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Image extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();

        if (isset($this->settings[0]['lazy_load']) && $this->settings[0]['lazy_load']) {
            \frontend\design\Info::addJsData(['widgets' => [
                $this->id => ['lazyLoad' => $this->settings[0]['lazy_load']]
            ]]);
        }
    }

    public function run()
    {
        $languages_id = (int)Yii::$app->settings->get('languages_id');

        if (isset($this->params['language_id']) && $this->params['language_id'] > 0 && isset($this->settings[0]['pdf']) && $this->settings[0]['pdf']) {
            $languages_id = (int)$this->params['language_id'];
        }

        $settings = (isset($this->settings[$languages_id]) ? $this->settings[$languages_id] : []);

        $image = \frontend\design\Info::themeImage($settings['logo'],
            [$this->settings[\common\classes\language::defaultId()]['logo'], $this->settings[0]['params']]);

        if (isset($this->settings[0]['pdf']) && $this->settings[0]['pdf']){

            if (function_exists('tep_catalog_href_link')){
                $img = tep_catalog_href_link($image);
            } else {
                $img = HTTP_SERVER . DIR_WS_HTTP_CATALOG . $image;
            }

            $img = '<img src="' . $img . '">';

            return $img;

        } else {

            $image = \common\classes\Images::getWebp($image, '');
            $imageUrl =  Yii::$app->request->baseUrl . '/' . $image;
            if (isset($this->params['absoluteUrl']) && $this->params['absoluteUrl']) {
                $imageUrl = Yii::$app->urlManager->createAbsoluteUrl($image);
            }

            $attributes = [];
            if (isset($settings['alt'])) {
                $attributes['alt'] = $settings['alt'];
            }
            if (isset($settings['title'])) {
                $attributes['title'] = $settings['title'];
            }
            if (isset($this->settings[0]['lazy_load']) && $this->settings[0]['lazy_load']) {
                $attributes['data-src'] = $imageUrl;
                $imageUrl = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
            }

            $attributesLink = [];
            if (isset($settings['target_blank']) && $settings['target_blank']) {
                $attributesLink['target'] = '_blank';
            }
            if (isset($settings['no_follow']) && $settings['no_follow']) {
                $attributesLink['rel'] = 'nofollow';
            }

            $html =  \yii\helpers\Html::img($imageUrl, $attributes);

            if (isset($settings['img_link']) && $settings['img_link']) {
                $html = \yii\helpers\Html::a($html, $settings['img_link'], $attributesLink);
            }
            return $html;

        }

    }
}