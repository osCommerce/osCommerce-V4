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
                'categories_image' => ['class'=>'IOAttachment', 'location'=>'@images/categories'],
                'categories_image_2' => ['class'=>'IOAttachment', 'location'=>'@images/categories'],
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
            'beforeDelete' => function($model, $id) {
                \common\helpers\Categories::remove_category($id);
                return 'deleted';
            },
            'beforeImportSave' => function($model, $data){
                if ( $model instanceof \common\models\Categories && !$data->skipped) {
                    if (empty($model->categories_seo_page_name)) {
                        $cat_name = $cat_name_other_lang = '';
                        if (is_array($data->data['descriptions']->data ?? null)) {
                            foreach($data->data['descriptions']->data as $IODescription) {
                                if ($IODescription->data['language_id']->language == 'en') {
                                    $cat_name = $IODescription->data['categories_name'] ?? '';
                                } else {
                                    if (!empty($IODescription->data['products_name'])) {
                                        $cat_name_other_lang = $IODescription->data['categories_name'];
                                    }
                                }
                            }
                            if (empty($cat_name)) {
                                $cat_name = $cat_name_other_lang;
                            }
                        }
                        $model->categories_seo_page_name = \common\helpers\Seo::makeSlug($cat_name); 
                    }
                    $cat_id = $model->isNewRecord ? null : $model->categories_id;
                    if (!empty(\common\models\Categories::find()->where(['categories_seo_page_name' => $model->categories_seo_page_name])->andFilterWhere(['not', ['categories_id' => $cat_id]])->one())) {
                        $model->categories_seo_page_name .= '-' . intval($model->categories_id);
                    }
                }
            },
            'afterImport' => function($model, $data) {
                if (!empty($model) && !empty($model->categories_id)) {
                    $categories_id = $model->categories_id;

                    try {
                        if (!empty($model->categories_seo_page_name)) {
                            $categoryImport = [];
                            $localCategory = \common\api\models\AR\Categories::find()->where(['categories_id' => $categories_id])->one();
                            if ($localCategory) {
                                $categoryImport['descriptions'] = [
                                    '*' => [
                                        'categories_seo_page_name' => (string) $model->categories_seo_page_name,
                                    ]
                                ];
                            }
                            if ($localCategory) {
                                $localCategory->importArray($categoryImport);
                                $localCategory->save(false);
                            }
                        }
                    } catch (\Exception $e) {
                        \OscLink\Logger::get()->log_record($model, 'Error while set categories_seo_page_name: ',$e->getMessage()."\n".$e->getTraceAsString());
                    }
                }
            },
        ],
    ],
    'covered_tables' => ['categories', 'categories_description', 'platforms_categories'],
];