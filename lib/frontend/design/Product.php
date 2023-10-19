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

class Product
{

    public static function pageName($products_id, $cPath_array = [])
    {
        $template_query = tep_db_fetch_array(tep_db_query("
            select template_name from " . TABLE_PRODUCT_TO_TEMPLATE . " where
                products_id = '" . (int)$products_id . "' and
                platform_id = '" . \common\classes\platform::currentId() . "' and
                theme_name in( '" . tep_db_input(THEME_NAME) . "', '" . str_replace('-mobile', '', tep_db_input(THEME_NAME)) . "')"));

        $categories_template_name = '';
        if (is_array($cPath_array)) {
          foreach ($cPath_array as $categories_id) {
            $template_cat_query = tep_db_fetch_array(tep_db_query("
                select template_name from " . TABLE_CATEGORIES_PRODUCT_TO_TEMPLATE . " where
                    categories_id = '" . (int)$categories_id . "' and
                    platform_id = '" . \common\classes\platform::currentId() . "' and
                    theme_name in( '" . tep_db_input(THEME_NAME) . "', '" . str_replace('-mobile', '', tep_db_input(THEME_NAME)) . "')"));
            if (isset($template_cat_query['template_name']) && !empty($template_cat_query['template_name'])) {
                $categories_template_name = $template_cat_query['template_name'];
            }
          }
        }

        if (isset($template_query['template_name']) && !empty($template_query['template_name'])) {
            $page_name = $template_query['template_name'];
        } elseif ($categories_template_name) {
            $page_name = $categories_template_name;
        } else {
            $page_name = self::templateByRules($products_id);
        }

        return design::pageName($page_name);
    }

    public static function templateByRules($products_id)
    {
        $page_name = '';
        $products_id = intval($products_id);

        $get = Yii::$app->request->get();

        $query = tep_db_query("
select aps.setting_value as rule, ap.setting_value as page_title
from " . TABLE_THEMES_SETTINGS . " ap left join " . TABLE_THEMES_SETTINGS . " aps on ap.setting_value = aps.setting_name
where
    ap.theme_name = '" . tep_db_input(THEME_NAME) . "' and
    aps.theme_name = '" . tep_db_input(THEME_NAME) . "' and
    ap.setting_group = 'added_page' and
    aps.setting_group = 'added_page_settings' and
    ap.setting_name = 'product'");

        $pages = array();
        while ($page = tep_db_fetch_array($query)) {
            $pages[$page['page_title']][] = $page['rule'];
        }
        $selected = array();
        foreach ($pages as $page => $rules) {
            $selected[$page] = 1;
            foreach ($rules as $rule) {
                if ($selected[$page] !== false) {
                    switch ($rule) {

                        case 'has_attributes':
                            if (\common\helpers\Attributes::has_product_attributes((int)$products_id)) {
                                $selected[$page] ++;
                            } else {
                                $selected[$page] = false;
                            }
                            break;

                        case 'is_bundle':
                            $bundle = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_SETS_PRODUCTS . " where sets_id = '" . (int)$products_id . "'"));
                            if ($bundle['total'] > 0) {
                                $selected[$page] ++;
                            } else {
                                $selected[$page] = false;
                            }
                            break;

                        case 'popup_product':
                            if (Yii::$app->request->isAjax) {
                                $selected[$page] ++;
                            } else {
                                $selected[$page] = false;
                            }
                            break;
                    }
                }
            }
        }
        arsort($selected);
        reset($selected);
        if (current($selected) !== false) {
            $page_name = key($selected);
        }

        if (isset($get['page_name']) && !empty($get['page_name'])) {
            $page_name = $get['page_name'];
        } elseif (!$page_name || Info::isAdmin()) {
            $page_name = 'product';
        }

        return $page_name;
    }

}
