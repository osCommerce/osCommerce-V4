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

return [
    'Header' => 'site/categories',
    'Data' => [
        'common\\models\\Categories' => [
            'xmlCollection' => 'Categories>Category',
            'orderBy' => ['categories_left' => 'asc'],
            'properties' => [
                'parent_id' => ['class'=>'IOMap', 'table'=>'categories', 'attribute' => 'categories_id'],
                'categories_level' => false,
                'categories_left' => false,
                'categories_right' => false,
                'categories_image' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'categories_image_2' => ['class'=>'IOAttachment', 'location'=>'@images'],
            ],
            'withRelated' => [
                'descriptions' => [
                    'where' => ['affiliate_id'=>0],
                    'xmlCollection' => 'Descriptions>Description',
                    'properties' => [
                        'language_id' => ['class' => 'IOLanguageMap'],
                    ],
                    'hideProperties' => [
                        'affiliate_id',
                    ],
                ],
/*
                'platform' => [
                    'xmlCollection' => 'AssignedPlatforms>AssignedPlatform',
                ],
                'seoRedirectsNamed' => [
                    'xmlCollection' => 'SeoRedirects>SeoRedirect',
                    'properties' => [
                        'seo_redirects_named_id' => false,
                        'language_id' => ['class' => 'IOLanguageMap'],
                        'redirects_type' => false,
                    ],
                ],
*/
            ],
            'afterImport' => function($model, $data) {
                if (isset($data->data['categories_id']) && is_object($data->data['categories_id'])) {
                    $categories_id = $data->data['categories_id']->toImportModel();
// {{
                    static $assign_to_all_platform = false;
                    if (!is_array($assign_to_all_platform)) {
                        $assign_to_all_platform = array_map(function($platform) {
                            return [
                                'platform_id' => $platform['id'],
                            ];
                        }, \common\classes\platform::getList());
                    }

                    $categoryImport = [];
                    $localCategory = \common\api\models\AR\Categories::find()->where(['categories_id' => $categories_id])->one();
                    if ($localCategory) {
                        $categoryImport['assigned_platforms'] = $assign_to_all_platform;
                    }

                    $get_data_r = tep_db_query(
                        "SELECT c.categories_id, c.categories_seo_page_name, cd.categories_name " .
                        "FROM categories c " .
                        " inner join categories_description cd on cd.categories_id = c.categories_id and cd.language_id = '" . (int)\common\helpers\Language::get_default_language_id() . "' " .
                        "where c.categories_id = '" . (int)$categories_id . "'"
                    );
                    if ($data = tep_db_fetch_array($get_data_r)) {
                        if (strlen($data['categories_seo_page_name']) > 0) {
                            $categories_seo_page_name = $data['categories_seo_page_name'];
                        } else {
                            $categories_seo_page_name = \common\helpers\Seo::makeSlug($data['categories_name']);
                        }
                        $check_unique_seo_name = tep_db_fetch_array(tep_db_query(
                            "SELECT COUNT(*) AS check_double " .
                            "FROM " . TABLE_CATEGORIES_DESCRIPTION . " " .
                            "WHERE categories_id != '" . intval($data['categories_id']) . "' " .
                            " AND categories_seo_page_name = '" . tep_db_input($categories_seo_page_name) . "'"
                        ));
                        if ($check_unique_seo_name['check_double'] > 0) {
                            $categories_seo_page_name .= '-' . intval($data['categories_id']);
                        }
                        if ($localCategory) {
                            $categoryImport['descriptions'] = [
                                '*' => [
                                    'categories_seo_page_name' => (string) $categories_seo_page_name,
                                ]
                            ];
                        }
                    }
                    if ($localCategory) {
                        $localCategory->importArray($categoryImport);
                        $localCategory->save(false);
                    }
                    tep_db_query("UPDATE menus SET last_modified = (SELECT MIN(date_added) - INTERVAL 1 DAY FROM categories)");
// }}
                    \common\helpers\Categories::update_categories();
                }
            },
        ],
    ],
    'covered_tables' => ['categories', 'categories_description', 'platforms_categories'],
];