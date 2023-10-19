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
    'Header' => 'site/products',
    'dependsOn' => ['site/languages', 'site/groups', 'site/currencies'],
    'Data' => [
        'common\\models\\Products' => [
            //'where' => ['products_id'=>340],
            'xmlCollection' => 'Products>Product',
            'properties' => [
                'products_image' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_lrg' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_sm_1' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_xl_1' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_sm_2' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_xl_2' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_sm_3' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_xl_3' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_sm_4' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_xl_4' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_sm_5' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_xl_5' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_sm_6' => ['class'=>'IOAttachment', 'location'=>'@images'],
                'products_image_xl_6' => ['class'=>'IOAttachment', 'location'=>'@images'],
            ],
            'hideProperties' => [
                'vendor_id',
                'products_image_med',
                'last_xml_import',
                'last_xml_export',
            ],
            'withRelated' => [
                'productsDescriptions' => [
                    'xmlCollection' => 'Descriptions>Description',
                    'properties' => [
                        'language_id' => ['class' => 'IOLanguageMap'],
                        'products_name_soundex' => false,
                        'products_description_soundex' => false,
                    ],
                ],
/*
                'platform' => [
                    'xmlCollection' => 'AssignedPlatforms>AssignedPlatform',
                ],
*/
                'categoriesList' => [
                    'xmlCollection' => 'AssignedCategories>AssignedCategory',
                ],
                'productsPrices' => [
                    'xmlCollection' => 'Prices>Price',
                    'properties' => [
                        'groups_id' => ['class'=>'IOMap', 'table'=>'groups','attribute'=>'groups_id'],
                        'currencies_id' => ['class'=>'IOCurrencyMap'],
                    ],
                ],
                'productsAttributes' => [
                    'xmlCollection' => 'Attributes>Attribute',
                ],
                'inventories' => [
                    'xmlCollection' => 'Inventories>Inventory',
                ],
/*
                'productImagesList' => [
                    'xmlCollection' => 'ImagesList>Image',
                    'properties' => [
                        //'products_images_id' => false,
                    ],
                    'withRelated' => [
                        'description' => [
                            'xmlCollection' => 'ImageDescriptions>ImageDescription',
                            'properties' => [
                                'imageUri' => [
                                    'class'=>'IOGalleryAttachment',
                                    'location'=>'@images',
                                    'options'=>['importVia'=>'File', 'noValue'=>true,'mergeLimitOptions'=>['url', 'checksum_sha1', 'file_info',]],
                                    'record' => true,
                                ],
                            ],
                        ],
                    ],
                ],
                'giftWrap' => [
                    'xmlCollection' => 'GiftWrap>GiftWrapPrice',
                    'properties' => [
                        'gw_id' => false,
                        'groups_id' => ['class'=>'IOMap', 'table'=>'groups','attribute'=>'groups_id'],
                        'currencies_id' => ['class'=>'IOCurrencyMap'],
                    ],
                ],
*/
                'productsFeatured' => [
                    'xmlCollection' => 'Featured>FeaturedInfo',
                    'where' => ['AND',['affiliate_id'=>0],['status'=>'1']],
                    'properties' => [
                        'featured_id' => false,
                        'featured_last_modified' => false,
                        'date_status_change' => false,
                        'status' => false,
                        'affiliate_id' => false,
                    ],
                ],
/*
                'productsVideos' => [
                    'xmlCollection' => 'ListVideos>VideoInfo',
                    'properties' => [
                        'video_id' => false,
                        'language_id' => ['class' => 'IOLanguageMap'],
                    ],
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
                if (isset($data->data['products_id']) && is_object($data->data['products_id'])) {
                    $products_id = $data->data['products_id']->toImportModel();

                    $groups = array();
                    if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'getGroupsArray')) {
                        $groups = $ext::getGroupsArray();
                    }

                    $platform_id = \common\classes\platform::defaultId();
                    \common\models\ProductsDescription::updateAll(['platform_id' => $platform_id], 'platform_id = 0');

                    static $assign_to_all_platform = false;
                    if (!is_array($assign_to_all_platform)) {
                        $assign_to_all_platform = array_map(function($platform) {
                            return [
                                'platform_id' => $platform['id'],
                            ];
                        }, \common\classes\platform::getList());
                    }

                    $images_path = DIR_FS_CATALOG . DIR_WS_IMAGES;

                    $additional_images = '';
                    for ($i = 1; $i < 7; $i++) {
                        $additional_images .= ", products_image_sm_{$i}, products_image_xl_{$i}";
                    }
                    $check_product_query = tep_db_query("SELECT products_id, products_model, products_image, products_image_med, products_image_lrg {$additional_images}, products_weight, products_seo_page_name FROM " . TABLE_PRODUCTS . " WHERE products_id = '" . (int)$products_id . "'");
                    if ($product = tep_db_fetch_array($check_product_query)) {
                        set_time_limit(0);

// {{
                        $productImport = [];
                        $localProduct = \common\api\models\AR\Products::find()->where(['products_id' => $products_id])->one();
                        if ($localProduct) {
                            $productImport['without_inventory'] = !\common\helpers\Extensions::isAllowed('Inventory');
                            $productImport['weight_cm'] = $product['products_weight'];
                            $productImport['weight_in'] = $product['products_weight'] * 2.20462;
                            $productImport['assigned_platforms'] = $assign_to_all_platform;
                        }

                        $product_name = \common\helpers\Product::get_products_name($product['products_id']);
                        $product['products_name'] = $product_name;
                        $product['products_seo_page_name'] = \common\helpers\Seo::makeProductSlug($product, $products_id);
                        if ($localProduct) {
                            $productImport['descriptions'] = [
                                '*' => [
                                    'products_seo_page_name' => (string) $product['products_seo_page_name'],
                                ]
                            ];
                        }

                        $get_data_r = tep_db_query(
                            "SELECT p.products_id, p.products_price, count(pa.options_values_price) " .
                            "FROM " . TABLE_PRODUCTS . " p " .
                            " inner join " . TABLE_PRODUCTS_ATTRIBUTES . " pa on pa.products_id = p.products_id " .
                            "where p.products_id = '" . (int)$products_id . "' group by p.products_id, p.products_price " .
                            " having count(pa.options_values_price) > 0"
                        );
                        if (tep_db_num_rows($get_data_r) == 0 || !\common\helpers\Extensions::isAllowed('Inventory')) {
                            if ($localProduct) {
                                \common\models\SuppliersProducts::deleteAll(['products_id' => $products_id]);
                                $productImport['suppliers_product'] = [
                                    [
                                        'suppliers_model' => $product['products_model'],
                                        'suppliers_id' => \common\helpers\Suppliers::getDefaultSupplierId(),
                                    ]
                                ];
                            }
                        }
                        while ($data = tep_db_fetch_array($get_data_r)) {
                            $data['products_price'] = \common\helpers\Product::get_products_price_for_edit($data['products_id'], 0, 0, $data['products_price']);

                            $total_comb = 1;
                            $count_values = array();
                            $products_options_array = array();
                            $products_options_query = tep_db_query("select distinct patrib.options_id as products_options_id from " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id = '" . (int) $data['products_id'] . "'");
                            while ($products_options = tep_db_fetch_array($products_options_query)) {
                                $options_id = $products_options['products_options_id'];
                                $products_options_array[$options_id] = array();
                                $count_values[$options_id] = 0;
                                $products_options_values_query = tep_db_query("select pa.products_attributes_id, pa.options_values_id as products_options_values_id, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int) $data['products_id'] . "' and pa.options_id = '" . (int) $options_id . "' order by pa.products_options_sort_order");
                                while ($products_options_values = tep_db_fetch_array($products_options_values_query)) {
                                    $products_options_values['options_values_price'] = \common\helpers\Attributes::get_attributes_price($products_options_values['products_attributes_id'], 0, 0, $products_options_values['options_values_price']);
                                    $products_options_array[$options_id][] = array('id' => $products_options_values['products_options_values_id']);
                                    $products_options_array[$options_id][sizeof($products_options_array[$options_id]) - 1]['prefix'] = $products_options_values['price_prefix'];
                                    $products_options_array[$options_id][sizeof($products_options_array[$options_id]) - 1]['price'][0] = $products_options_values['options_values_price'];
                                    tep_db_query("insert ignore into " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " set products_attributes_id = '" . (int)$products_options_values['products_attributes_id'] . "', groups_id = '0', currencies_id = '0', attributes_group_price = '" . tep_db_input($products_options_values['options_values_price']) . "'");
                                    foreach ($groups as $group) {
                                        $options_values_price = \common\helpers\Attributes::get_attributes_price($products_options_values['products_attributes_id'], 0, $group['groups_id'], $products_options_values['options_values_price']);
                                        tep_db_query("insert ignore into " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " set products_attributes_id = '" . (int)$products_options_values['products_attributes_id'] . "', groups_id = '" . (int)$group['groups_id'] . "', currencies_id = '0', attributes_group_price = '" . tep_db_input($options_values_price) . "'");
                                        if ($options_values_price == -2)
                                            $options_values_price = $products_options_values['options_values_price'];
                                        $products_options_array[$options_id][sizeof($products_options_array[$options_id]) - 1]['price'][$group['groups_id']] = $options_values_price;
                                    }
                                    $count_values[$options_id] ++;
                                }
                                $total_comb *= $count_values[$options_id];
                            }

                            if ($total_comb < 5000) {
                                for ($i = 0; $i < $total_comb; $i++) {
                                    $num = $i;
                                    $total_attributes_price = array();
                                    $attributes_array = array();
                                    $prefix = '+';
                                    foreach ($products_options_array as $id => $array) {
                                        $k = $num % $count_values[$id];
                                        $attributes_array[$id] = $array[$k]['id'];
                                        if ($total_attributes_price[0] != -1) {
                                            $total_attributes_price[0] += ($array[$k]['prefix'] != '-' ? $array[$k]['price'][0] : -$array[$k]['price'][0]);
                                        }
                                        if ($total_attributes_price[0] == 0 && $array[$k]['prefix'] == '-') {
                                            $prefix = '-';
                                        }
                                        foreach ($groups as $group) {
                                            if ($total_attributes_price[$group['groups_id']] != -1) {
                                                $total_attributes_price[$group['groups_id']] += ($array[$k]['prefix'] != '-' ? $array[$k]['price'][$group['groups_id']] : -$array[$k]['price'][$group['groups_id']]);
                                            }
                                        }
                                        $num = (int) ($num / $count_values[$id]);
                                    }
                                    $uprid = \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($data['products_id'], $attributes_array));

                                    $inventory = tep_db_fetch_array(tep_db_query("select inventory_id from " . TABLE_INVENTORY . " where prid = '" . (int) $data['products_id'] . "' and products_id = '" . tep_db_input($uprid) . "'"));
                                    if (!($inventory['inventory_id'] > 0)) {
                                        tep_db_query("insert into " . TABLE_INVENTORY . " set prid = '" . (int) $data['products_id'] . "', products_id = '" . tep_db_input($uprid) . "'");
                                        $inventory = tep_db_fetch_array(tep_db_query("select inventory_id from " . TABLE_INVENTORY . " where prid = '" . (int) $data['products_id'] . "' and products_id = '" . tep_db_input($uprid) . "'"));
                                    }

                                    if ($data['products_price'] == -1 || $total_attributes_price[0] == -1) {
                                        tep_db_query("update " . TABLE_INVENTORY . " set inventory_price = '-1', price_prefix = '+', inventory_full_price = '-1' where prid = '" . (int) $data['products_id'] . "' and products_id = '" . tep_db_input($uprid) . "'");
                                    } else {
                                        tep_db_query("update " . TABLE_INVENTORY . " set inventory_price = '" . (float) abs($total_attributes_price[0]) . "', price_prefix = '" . ($total_attributes_price[0] < 0 ? '-' : $prefix) . "', inventory_full_price = '" . (float) ($data['products_price'] + $total_attributes_price[0]) . "' where prid = '" . (int) $data['products_id'] . "' and products_id = '" . tep_db_input($uprid) . "'");
                                    }
                                    foreach ($groups as $group) {
                                        $products_price = \common\helpers\Product::get_products_price_for_edit($data['products_id'], 0, $group['groups_id'], $data['products_price']);
                                        if ($products_price == -2)
                                            $products_price = $data['products_price'];
                                        tep_db_query("delete from " . TABLE_INVENTORY_PRICES . " where prid = '" . (int) $data['products_id'] . "' and products_id = '" . tep_db_input($uprid) . "' and groups_id = '" . (int) $group['groups_id'] . "' and currencies_id = '0' and inventory_id = '" . (int) $inventory['inventory_id'] . "'");
                                        if ($products_price == -1 || $total_attributes_price[$group['groups_id']] == -1) {
                                            tep_db_query("insert into " . TABLE_INVENTORY_PRICES . " set inventory_id = '" . (int) $inventory['inventory_id'] . "', inventory_group_price  = '-1', price_prefix = '+', inventory_full_price = '-1', prid = '" . (int) $data['products_id'] . "', products_id = '" . tep_db_input($uprid) . "', groups_id = '" . (int) $group['groups_id'] . "', currencies_id = '0'");
                                        } else {
                                            tep_db_query("insert into " . TABLE_INVENTORY_PRICES . " set inventory_id = '" . (int) $inventory['inventory_id'] . "', inventory_group_price  = '" . (float) abs($total_attributes_price[$group['groups_id']]) . "', price_prefix = '" . ($total_attributes_price[$group['groups_id']] < 0 ? '-' : '+') . "', inventory_full_price = '" . (float) ($products_price + $total_attributes_price[$group['groups_id']]) . "', prid = '" . (int) $data['products_id'] . "', products_id = '" . tep_db_input($uprid) . "', groups_id = '" . (int) $group['groups_id'] . "', currencies_id = '0'");
                                        }
                                    }
                                }
                            }
                        }

                        tep_db_query("UPDATE " . TABLE_INVENTORY . " SET warehouse_stock_quantity = products_quantity where prid = '" . (int)$products_id . "' and products_quantity > 0");
                        tep_db_query("INSERT IGNORE INTO " . TABLE_WAREHOUSES_PRODUCTS . " (warehouse_id, products_id, prid, products_model, products_quantity, allocated_stock_quantity, temporary_stock_quantity, warehouse_stock_quantity, ordered_stock_quantity, suppliers_id) SELECT '" . (int)\common\helpers\Warehouses::get_default_warehouse() . "', products_id, prid, products_model, products_quantity, allocated_stock_quantity, temporary_stock_quantity, warehouse_stock_quantity, ordered_stock_quantity, '" . (int)\common\helpers\Suppliers::getDefaultSupplierId() . "' FROM " . TABLE_INVENTORY . " where prid = '" . (int)$products_id . "'");
                        tep_db_query("UPDATE " . TABLE_PRODUCTS . " SET warehouse_stock_quantity = products_quantity where products_id = '" . (int)$products_id . "' and products_quantity > 0");
                        tep_db_query("INSERT IGNORE INTO " . TABLE_WAREHOUSES_PRODUCTS . " (warehouse_id, products_id, prid, products_model, products_quantity, allocated_stock_quantity, temporary_stock_quantity, warehouse_stock_quantity, ordered_stock_quantity, suppliers_id) SELECT '" . (int)\common\helpers\Warehouses::get_default_warehouse() . "', products_id, products_id as prid, products_model, products_quantity, allocated_stock_quantity, temporary_stock_quantity, warehouse_stock_quantity, ordered_stock_quantity, '" . (int)\common\helpers\Suppliers::getDefaultSupplierId() . "' FROM " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
// }}

                        $image_location = $images_path . 'products' . DIRECTORY_SEPARATOR . $product['products_id'] . DIRECTORY_SEPARATOR;

                        if (!empty($product['products_image_lrg']) && is_file($images_path . $product['products_image_lrg'])) {
                            $orig_file = $product['products_image_lrg'];
                        } elseif (!empty($product['products_image_med']) && is_file($images_path . $product['products_image_med'])) {
                            $orig_file = $product['products_image_med'];
                        } else {
                            $orig_file = $product['products_image'];
                        }

                        $check = tep_db_fetch_array(tep_db_query("select pi.products_images_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid where pi.products_id = '" . (int) $product['products_id'] . "' and pi.products_images_id = pid.products_images_id and pid.language_id = '0' and pid.orig_file_name like '%" . tep_db_input($orig_file) . "'"));

                        $tmp_name = $images_path . $orig_file;

                        if (!empty($orig_file) && !is_file($tmp_name)) {
                            file_put_contents($images_path . 'products/missing_img.txt', 'pid:' . $product['products_id'] . ":{$orig_file}\n", FILE_APPEND);
                        }

                        if (!empty($orig_file) && is_file($tmp_name) && !($check['products_images_id'] > 0)) {
                            if ($localProduct) {
                                $productImport['images'][] = [
                                    'default_image' => 1,
                                    'image_status' => 1,
                                    'sort_order' => 0,
                                    'image_description' => [
                                        '00' => [
                                            'image_source_url' => $tmp_name,
                                            'orig_file_name' => pathinfo($tmp_name, PATHINFO_BASENAME),
                                            'image_title' => (string) $product_name,
                                        ]
                                    ]
                                ];
                            }
                        }

                        for ($i = 1; $i < 7; $i++) {
                            if (!empty($product['products_image_xl_' . $i]) && is_file($images_path . $product['products_image_xl_' . $i])) {
                                $orig_file = $product['products_image_xl_' . $i];
                            } else {
                                $orig_file = $product['products_image_sm_' . $i];
                            }

                            if (!empty($orig_file) && !is_file($tmp_name)) {
                                file_put_contents($images_path . 'products/missing_img.txt', 'pid:' . $product['products_id'] . ":{$orig_file}\n", FILE_APPEND);
                            }

                            $check = tep_db_fetch_array(tep_db_query("select pi.products_images_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid where pi.products_id = '" . (int) $product['products_id'] . "' and pi.products_images_id = pid.products_images_id and pid.language_id = '0' and pid.orig_file_name like '%" . tep_db_input($orig_file) . "'"));

                            $tmp_name = $images_path . $orig_file;

                            if (!empty($orig_file) && is_file($tmp_name) && !($check['products_images_id'] > 0)) {
                                if ($localProduct) {
                                    $productImport['images'][] = [
                                        'default_image' => 0,
                                        'image_status' => 1,
                                        'sort_order' => $i,
                                        'image_description' => [
                                            '00' => [
                                                'image_source_url' => $tmp_name,
                                                'orig_file_name' => pathinfo($tmp_name, PATHINFO_BASENAME),
                                                'image_title' => (string) $product_name,
                                            ]
                                        ]
                                    ];
                                }
                            }
                        }
                        if ($localProduct) {
                            $localProduct->importArray($productImport);
                            $localProduct->save(false);
                        }
                    }
                }
            },
        ],
    ],
    'covered_tables' => [
        'products',
        'products_description',
        'platforms_products',
        'products_to_categories',
        'products_images',
        'products_images_description',
        'products_prices',
        'products_attributes',
        'products_attributes_prices',
        'inventory',
        'inventory_prices',
        'suppliers_products',
        'warehouses_products',
        //'gift_wrap_products',
        'featured',
        //'products_videos',
    ],
];