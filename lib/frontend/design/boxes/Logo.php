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
use frontend\design\Info;
use yii\helpers\ArrayHelper;

class Logo extends Widget
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
        $languages_id = (int)\Yii::$app->settings->get('languages_id');

        $image = false;
        $theme_name = '';
        if (defined("THEME_NAME")) {
            $theme_name = THEME_NAME;
        } elseif (!defined("THEME_NAME") && isset ($this->params['theme_name'])) {
            $theme_name = $this->params['theme_name'];
        }

        if (isset($this->params['language_id']) && $this->params['language_id']>0 && isset($this->settings[0]['pdf']) && $this->settings[0]['pdf']) {
            $lang_id = $this->params['language_id'];
        }else {
            $lang_id = $languages_id;
        }

        $this->settings[0]['logo_from'] = $this->settings[0]['logo_from']??null;
        if ($this->settings[0]['logo_from'] == 'platform') {
            $platform = \common\models\Platforms::find()
                ->select('logo')
                ->where(['platform_id' => \common\classes\platform::currentId()])
                ->asArray()
                ->one();
            if ($platform['logo'] && is_file(DIR_FS_CATALOG . DIR_WS_IMAGES . $platform['logo'])) {
                $image = DIR_WS_IMAGES . $platform['logo'];
            }
        }

        if ($this->settings[0]['logo_from'] == 'theme') {
            $image = \frontend\design\Info::themeSetting('logo', 'hide', $theme_name);
        }

        if (!$image) {
            $image = Info::themeImage(
                $this->settings[$lang_id]['logo'],
                [$this->settings[\common\classes\language::defaultId()]['logo'], $this->settings[0]['params']],
                true,
                $theme_name
            );
        }

        if ((!$image || isset($image) && is_string($image) && strpos($image, '/na.png')) && defined("THEME_NAME")) {
            $logo = Info::widgetSettings('Logo', false, 'header');
            if ($logo[$lang_id]['logo'] ?? null) {
                $image = Info::themeImage($logo[$lang_id]['logo']);
            }
        }

        if (isset($this->settings[0]['pdf']) && $this->settings[0]['pdf']){

            if ( is_file(DIR_FS_CATALOG . $image) ) {
                return '<img src="@' . base64_encode(file_get_contents(DIR_FS_CATALOG . $image)) . '">';
            } elseif (is_file(DIR_FS_CATALOG . $this->settings[$languages_id]['logo'])) {
                return '<img src="@' . base64_encode(file_get_contents(DIR_FS_CATALOG . $this->settings[$languages_id]['logo'])) . '">';
            }

            return '';

        } else {

            $url = '';
            if (Yii::$app->controller->id != 'index' || Yii::$app->controller->action->id != 'index') {
                $url = tep_href_link('/');
            }

            if (Yii::$app->id == 'app-console' || (empty(Yii::$app->request->baseUrl) && defined('DIR_WS_HTTPS_CATALOG')) ) {
                $imageUrl =  DIR_WS_HTTPS_CATALOG . $image;
            }else{
                $imageUrl =  Yii::$app->request->baseUrl . '/' . $image;
            }
            $width = 0;
            $height = 0;
            if (isset($this->params['absoluteUrl']) && $this->params['absoluteUrl']) {
                if (Yii::$app->id == 'app-console'){
                    $ssl = true;
                } else {
                    $ssl = Yii::$app->request->getIsSecureConnection();
                }
                $imageUrl = \Yii::$app->get('platform')->config()->getCatalogBaseUrl($ssl).$image;
                $width = ArrayHelper::getValue($this->settings, [0,'width'], 0);
                $height = ArrayHelper::getValue($this->settings, [0,'height'], 0);
            }

            if (Yii::$app->id == 'app-console'){
                return '<a href="' . $url . '"><img src="' . $image . '" style="border: none;"></a>';
            }

            return IncludeTpl::widget([
                'file' => 'boxes/logo.tpl',
                'params' => [
                    'url' => $url,
                    'image' => $imageUrl,
                    'width' => $width,
                    'height' => $height
                ],
            ]);
        }
    }
}