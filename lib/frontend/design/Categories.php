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

namespace frontend\design;

use Yii;
use common\classes\design;

class Categories
{

    public static function pageName($categories_id, $countSubCategories)
    {
        $get = Yii::$app->request->get();

        $categories_template_name = '';
        $template_cat_query = tep_db_fetch_array(tep_db_query("
                select template_name from " . TABLE_CATEGORIES_TO_TEMPLATE . " where
                    categories_id = '" . (int)$categories_id . "' and
                    platform_id = '" . \common\classes\platform::currentId() . "' and
                    theme_name in( '" . tep_db_input(THEME_NAME) . "', '" . str_replace('-mobile', '', tep_db_input(THEME_NAME)) . "')"));
        if (isset($template_cat_query['template_name']) && !empty($template_cat_query['template_name'])) {
            $categories_template_name = $template_cat_query['template_name'];
        }

        if (isset($get['manufacturers_id']) && $get['manufacturers_id'] > 0) {

            $page = \common\models\ThemesSettings::find()->where([
                'setting_group' => 'added_page_settings',
                'setting_value' => 'brands',
            ])->asArray()->one();

            if (isset($page['setting_name']) && !empty($page['setting_name'])) {
                return design::pageName($page['setting_name']);
            }
        }

        if ($countSubCategories > 0) {
            if (isset($get['page_name']) && !empty($get['page_name'])) {
                $page_name = $get['page_name'];
            } elseif ($categories_template_name) {
                $page_name = $categories_template_name;
            } else {
                $page_name = self::listingTemplate('categories', $categories_id);
            }
        } elseif ($categories_template_name) {
            $page_name = $categories_template_name;
        } else {
            $page_name = self::listingTemplate('products', $categories_id);

        }

        return design::pageName($page_name);
    }

    public static function listingTemplate($pageTypeName, $categories_id)
    {
        $get = Yii::$app->request->get();

        $query = tep_db_query("
select aps.setting_value as rule, ap.setting_value as page_title
from " . TABLE_THEMES_SETTINGS . " ap left join " . TABLE_THEMES_SETTINGS . " aps on ap.setting_value = aps.setting_name
where 
    ap.theme_name = '" . tep_db_input(THEME_NAME) . "' and 
    aps.theme_name = '" . tep_db_input(THEME_NAME) . "' and 
    ap.setting_group = 'added_page' and 
    aps.setting_group = 'added_page_settings' and 
    ap.setting_name = '" . $pageTypeName . "'");

        $arr = array();
        while ($page = tep_db_fetch_array($query)){
            $p_name = design::pageName($page['page_title']);

            if (isset($page['rule']) && $page['rule'] == 'no_filters'){

                if (\common\helpers\Acl::checkExtensionAllowed('ProductPropertiesFilters'))
                {
                    if (isset($get['manufacturers_id']) && $get['manufacturers_id'] > 0) {
                        $filters = \common\helpers\Manufacturers::get_manufacturer_filters($get['manufacturers_id']);
                    } else {
                        $filters = \common\helpers\Categories::get_category_filters($categories_id);
                    }
                }else{
                    $filters = [];
                }
                if (count($filters) > 0) {
                    $arr[$p_name] = false;
                } else {
                    $arr[$p_name] = true;
                }
            }

        }
        $page_name = null;
        foreach ($arr as $pn => $set){
            if ($set) $page_name = $pn;
        }

        if (isset($get['page_name']) && !empty($get['page_name'])){
            $page_name = $get['page_name'];
        } elseif (!$page_name) {
            $page_name = $pageTypeName;
        }

        return $page_name;
    }

}
