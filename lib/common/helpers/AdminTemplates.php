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

namespace common\helpers;

class AdminTemplates
{
    public static $pages = [
        'backendOrder' => TABLE_HEADING_ORDER,
        'backendOrdersList' => ORDERS_LIST,
    ];

    public static function templatesList($accessLevelsId)
    {
        $templatesList = [];
        $adminTemplates = [];
        $adminTemplatesData = \common\models\AdminTemplates::find()->where(['access_levels_id' => $accessLevelsId])->asArray()->all();
        foreach ($adminTemplatesData as $template) {
            $adminTemplates[$template['page']] = $template['template'];
        }

        foreach (self::$pages as $page => $title) {
            $themesSettings = \common\models\ThemesSettings::find()->where([
                'theme_name' => \common\classes\design::pageName(BACKEND_THEME_NAME),
                'setting_group' => 'added_page',
                'setting_name' => $page
            ])->asArray()->all();

            $templates = ['' => TEXT_DEFAULT];
            if (is_array($themesSettings)) foreach ($themesSettings as $setting) {
                $templates[\common\classes\design::pageName($setting['setting_value'])] = $setting['setting_value'];
            }
            $templatesList[] = [
                'name' => $page,
                'title' => $title,
                'selectedTemplate' => $adminTemplates[$page] ?? null,
                'templates' => $templates
            ];
        }

        return $templatesList;
    }

    public static function save($pages, $accessLevelsId)
    {
        if (is_array($pages))
        foreach ($pages as $page => $template) {
            $pageTemplate = \common\models\AdminTemplates::findOne(['page' => $page, 'access_levels_id' => $accessLevelsId]);
            if (!$pageTemplate) {
                $pageTemplate = new \common\models\AdminTemplates();
                $pageTemplate->access_levels_id = $accessLevelsId;
                $pageTemplate->page = $page;
            }
            $pageTemplate->template = $template;
            $pageTemplate->save();
        }
    }


}
