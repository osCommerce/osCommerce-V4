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

use Yii;

class PageComponents {

    public static function addComponents($text)
    {
        if ( !empty($text) && strpos($text,'##COMPONENT%')!==false ) {
            $text = preg_replace_callback("/\#\#COMPONENT\%([^\#^\%]+)[\%]{0,1}([^\#]{0,})##/", self::class . "::addComponent", $text);
        }

        return $text;
    }

    private static function addComponent($matches)
    {
        $findPage = \common\models\ThemesSettings::findOne([
            'theme_name' => THEME_NAME,
            'setting_group' => 'added_page',
            'setting_value' => $matches[1],
        ]);

        if (!$findPage) {
            return '';
        }

        $params = [];
        if ($matches[2]) {
            $arr = explode('=', $matches[2]);
            $params = [
                $arr[0] => $arr[1]
            ];
        }

        return \frontend\design\Block::widget([
            'name' => \common\classes\design::pageName($matches[1]),
            'params' => [
                'params' => $params
            ],
        ]);
    }

    public static function componentTemplates()
    {
        $platform_id = Yii::$app->request->get('platform_id');

        $platform = \common\models\PlatformsToThemes::findOne($platform_id);
        $theme = \common\models\Themes::findOne($platform['theme_id']);

        $templatesQuery = \common\models\ThemesSettings::find()
            ->where([
                'theme_name' => $theme['theme_name'],
                'setting_group' => 'added_page',
            ])
            ->asArray()
            ->all();

        $templates = [];

        foreach ($templatesQuery as $template) {
            if ($template['setting_name'] == 'components') {
                $templates[$template['theme_name']][$template['setting_name']][\common\classes\design::pageName($template['setting_value'])] = $template['setting_value'];
            }
        }
        foreach ($templatesQuery as $template) {
            if ($template['setting_name'] != 'components') {
                $templates[$template['theme_name']][$template['setting_name']][\common\classes\design::pageName($template['setting_value'])] = $template['setting_value'];
            }
        }

        return $templates;
    }

}