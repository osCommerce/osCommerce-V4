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
use frontend\design\Info;
use Yii;

class ManifestController extends Sceleton {

    public function actionIndex() { 
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $response_array = array(
            "name" => \common\classes\platform::name(PLATFORM_ID),
            "short_name" => \common\classes\platform::name(PLATFORM_ID),
            "start_url" => "/",
            "background_color" => Info::themeSetting('theme_color'),
            "theme_color" => Info::themeSetting('theme_color'),
            "orientation" => "any",
            "display" => "standalone",
            "icons" => array(
                array(
                    "src" => Info::themeFile("/icons/android-icon-36x36.png"),
                    "sizes" => "36x36",
                    "type" => "image/png"
                ),
                array(
                    "src" => Info::themeFile("/icons/android-icon-48x48.png"),
                    "sizes" => "48x48",
                    "type" => "image/png"
                ),
                array(
                    "src" => Info::themeFile("/icons/android-icon-72x72.png"),
                    "sizes" => "72x72",
                    "type" => "image\/png"
                ),
                array(
                    "src" => Info::themeFile("/icons/android-icon-96x96.png"),
                    "sizes" => "96x96",
                    "type" => "image/png"
                ),
                array(
                    "src" => Info::themeFile("/icons/android-icon-144x144.png"),
                    "sizes" => "144x144",
                    "type" => "image/png"
                ),
                array(
                    "src" => Info::themeFile("/icons/android-icon-192x192.png"),
                    "sizes" => "192x192",
                    "type" => "image/png"
                ),
                array(
                    "src" => Info::themeFile("/icons/android-icon-512x512.png"),
                    "sizes" => "512x512",
                    "type" => "image/png"
                )
            )
        );
        return $response_array;
    }
    
}
