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

namespace backend\design;

use Yii;
use backend\controllers\DesignController;

class CategoryTemplate
{

    public static function categoryedit($categories_id)
    {
        $platforms = tep_db_query("
            SELECT p.platform_id, t.theme_name, t.title
            FROM " . TABLE_PLATFORMS_CATEGORIES . " p
                left join " . TABLE_PLATFORMS_TO_THEMES . " p2t on p.platform_id = p2t.platform_id
                left join " . TABLE_THEMES . " t on t.id = p2t.theme_id
            WHERE p2t.is_default = 1 and p.categories_id = '" . $categories_id . "'
            ");
        $themes = array();
        $showBlock = false;
        while ($platform = tep_db_fetch_array($platforms)) {
            $templates = tep_db_query("
                select setting_value, setting_name
                from " . TABLE_THEMES_SETTINGS . "
                where
                    theme_name = '" . $platform['theme_name'] . "' and
                    setting_group = 'added_page' and
                    (setting_name = 'product' or setting_name = 'categories' or setting_name = 'products')
            ");
            if (tep_db_num_rows($templates) > 0) {
                while ($item = tep_db_fetch_array($templates)) {
                    if ($item['setting_name'] == 'product') {
                        $platform['themes'][] = $item['setting_value'];
                    } elseif ($item['setting_name'] == 'categories' || $item['setting_name'] == 'products') {
                        $platform['themes_categories'][] = $item['setting_value'];
                    }
                }
                $showBlock = true;
            }
            $themes[$platform['platform_id']] = $platform;
        }

        $list = \common\classes\platform::getList(false);
        foreach ($list as $key => $item) {

            if ($themes[$item['id']] ?? null) {
                $list[$key]['active'] = 1;
                $list[$key]['theme_name'] = $themes[$item['id']]['theme_name'];
                $list[$key]['theme_title'] = $themes[$item['id']]['title'];
                $list[$key]['template'] = '';
                $list[$key]['template_product'] = '';

                $themes[$item['id']]['themes'] = $themes[$item['id']]['themes'] ?? null;
                $themes[$item['id']]['themes_categories'] = $themes[$item['id']]['themes_categories'] ?? null;
                if ($themes[$item['id']]['themes'] || $themes[$item['id']]['themes_categories']) {
                    $list[$key]['templates'] = $themes[$item['id']]['themes'];
                    $list[$key]['templates_categories'] = $themes[$item['id']]['themes_categories'];

                    $setTemplate = tep_db_fetch_array(tep_db_query("
                        select template_name
                        from " . TABLE_CATEGORIES_TO_TEMPLATE . "
                        where
                            categories_id = '" . $categories_id . "' and
                            platform_id = '" . $item['id'] . "' and
                            theme_name = '" . $themes[$item['id']]['theme_name'] . "'
                    "));
                    if (isset($setTemplate['template_name']) && !empty($setTemplate['template_name'])) {
                        $list[$key]['template'] = $setTemplate['template_name'];
                    }

                    $setTemplateProduct = tep_db_fetch_array(tep_db_query("
                        select template_name
                        from " . TABLE_CATEGORIES_PRODUCT_TO_TEMPLATE . "
                        where
                            categories_id = '" . $categories_id . "' and
                            platform_id = '" . $item['id'] . "' and
                            theme_name = '" . $themes[$item['id']]['theme_name'] . "'
                    "));
                    if (isset($setTemplateProduct['template_name']) && !empty($setTemplateProduct['template_name'])) {
                        $list[$key]['template_product'] = $setTemplateProduct['template_name'];
                    }
                }
            } else {
                $list[$key]['active'] = 0;
            }
        }

        $template['list'] = $list;
        $template['show_block'] = $showBlock;

        return $template;
    }

    public static function categorySubmit($categories_id)
    {
        $product_template = Yii::$app->request->post('product_template');
        $category_template = Yii::$app->request->post('category_template');
        $list = self::categoryedit($categories_id);

        foreach ($list['list'] as $item) {
            if ($categories_id && $item['id'] && ($item['theme_name']??null)) {
                tep_db_query("
                delete from " . TABLE_CATEGORIES_PRODUCT_TO_TEMPLATE . "
                where
                    categories_id = '" . $categories_id . "' and
                    platform_id = '" . $item['id'] . "' and
                    theme_name = '" . $item['theme_name'] . "'");

                if ($product_template[$item['id']]) {
                    $data = array(
                        'categories_id' => $categories_id,
                        'platform_id' => $item['id'],
                        'theme_name' => $item['theme_name'],
                        'template_name' => $product_template[$item['id']]
                    );
                    tep_db_perform(TABLE_CATEGORIES_PRODUCT_TO_TEMPLATE, $data);
                }

                tep_db_query("
                delete from " . TABLE_CATEGORIES_TO_TEMPLATE . "
                where
                    categories_id = '" . $categories_id . "' and
                    platform_id = '" . $item['id'] . "' and
                    theme_name = '" . $item['theme_name'] . "'");

                if ($category_template[$item['id']]) {
                    $data = array(
                        'categories_id' => $categories_id,
                        'platform_id' => $item['id'],
                        'theme_name' => $item['theme_name'],
                        'template_name' => $category_template[$item['id']]
                    );
                    tep_db_perform(TABLE_CATEGORIES_TO_TEMPLATE, $data);
                }
            }
        }
    }

    public static function categorydelete($categories_id)
    {
        tep_db_query("
            delete from " . TABLE_CATEGORIES_PRODUCT_TO_TEMPLATE . "
            where categories_id = '" . $categories_id . "'");
        tep_db_query("
            delete from " . TABLE_CATEGORIES_TO_TEMPLATE . "
            where categories_id = '" . $categories_id . "'");
    }

}
