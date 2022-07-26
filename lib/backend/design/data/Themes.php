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

namespace backend\design\data;

use Yii;
use common\models\Themes as ThemesModel;
use backend\design\Theme;
use backend\design\Style;

class Themes
{
    public static function getList()
    {
        $themes = ThemesModel::find()->orderBy('sort_order')->asArray()->all();

        return $themes;
    }

    public static function sortOrder($params)
    {
        foreach ($params as $key => $themeId) {
            $theme = ThemesModel::findOne(['id' => $themeId]);
            $theme->sort_order = $key;
            $theme->save();
        }

        return json_encode(['ok']);
    }

    public static function addTheme($params)
    {
        \common\helpers\Translation::init('admin/design');

        if (!$params['title']) {
            return json_encode(['code' => 406, 'text' => THEME_TITLE_REQUIRED]);
        }

        if (!$params['theme_name']) {
            $params['theme_name'] = \common\classes\design::pageName($params['title']);
        }
        if (!preg_match("/^[a-z0-9_\-]+$/", $params['theme_name'])) {
            return json_encode(['code' => 1, 'text' => 'Enter only lowercase letters and numbers for theme name']);
        }

        $theme = ThemesModel::findOne(['theme_name' => $params['theme_name']]);
        if ($theme){
            return json_encode(['code' => 406, 'text' => 'Theme with this name already exist']);
        }

        $parentTheme = '';
        if (
            $params['parent_theme'] &&
            $params['theme_source'] == 'theme' &&
            $params['parent_theme_files'] == 'link'
        ) {
            $parentTheme = $params['parent_theme'];
        }

        $theme = new ThemesModel();
        $theme->theme_name = $params['theme_name'];
        $theme->title = $params['title'];
        $theme->install = 1;
        $theme->is_default = 0;
        $theme->sort_order = ThemesModel::find()->max('sort_order') + 1;
        $theme->parent_theme = $parentTheme;
        $theme->save();

        if ($params['parent_theme'] && $params['theme_source'] == 'theme'){

            //Theme::copyTheme($params['theme_name'], $params['parent_theme'], $params['parent_theme_files']);
            //Theme::copyTheme($params['theme_name'] . '-mobile', $params['parent_theme'] . '-mobile', $params['parent_theme_files']);

        }

        if ($params['theme_source'] == 'url' || $params['theme_source'] == 'computer') {

            if ($params['theme_source'] == 'url') {
                $themeFile = $params['theme_source_url'];
            } else {
                $themeFile = \Yii::getAlias('@webroot');
                $themeFile .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $params['theme_source_computer'];
            }
            if ( !Theme::import($params['theme_name'], $themeFile) ) {
                return json_encode(['code' => 406, 'text' => 'Wrong theme file']);
            }

        }

        //Style::createCache($params['theme_name']);
        //Style::createCache($params['theme_name'] . '-mobile');

        return json_encode(['code' => 200, 'themes' => ThemesModel::find()->orderBy('sort_order')->asArray()->all()]);
    }
}
