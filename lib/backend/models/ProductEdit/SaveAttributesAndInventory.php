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

namespace backend\models\ProductEdit;

use yii;
use common\models\Products;

class SaveAttributesAndInventory
{

    protected $product;

    public function __construct(Products $product)
    {
        $this->product = $product;
    }

    public function save()
    {
        $currencies = Yii::$container->get('currencies');
        $languages_id = \Yii::$app->settings->get('languages_id');

        $products_id = $this->product->products_id;

        $without_inventory = !!$this->product->without_inventory;

        $old_products_id = (int) Yii::$app->request->post('products_id');
        $currencies_ids = $groups = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $groups = $ext::getGroupsArray();
            if (!isset($groups['0'])) {
                $groups['0'] = ['groups_id' => 0];
            }
        }
        $_def_curr_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];

        if (USE_MARKET_PRICES == 'True') {
            foreach ($currencies->currencies as $key => $value)  {
                $currencies_ids[$currencies->currencies[$key]['id']] = $currencies->currencies[$key]['id'];
            }
        } else {
            $currencies_ids[$_def_curr_id] = '0'; /// here is the post and db currencies_id are different.
        }

        //no such field $products_price = (float) Yii::$app->request->post('products_price');
        $products_price_full = Yii::$app->request->post('products_price_full');
        ///already tep_db_query("update " . TABLE_PRODUCTS . " set products_price_full = '" . (int) $products_price_full . "' where products_id = '" . (int) $products_id . "'");
///products_option_values_sort_order[33]
//products_attributes_id[34][175]
        $all_inventory_ids_array = $all_inventory_uprids_array = $options = $attributes_array = [];
        $products_attributes_id = Yii::$app->request->post('products_attributes_id');
        $products_option_values_sort_order = Yii::$app->request->post('products_option_values_sort_order');
        $products_attributes_weight_prefix = Yii::$app->request->post('products_attributes_weight_prefix');
        $products_attributes_weight = Yii::$app->request->post('products_attributes_weight');
        $default_option_values = Yii::$app->request->post('default_option_value', []);

        $attr_file = Yii::$app->request->post('attr_file');
        $products_attributes_maxdays = Yii::$app->request->post('products_attributes_maxdays');
        $products_attributes_maxcount = Yii::$app->request->post('products_attributes_maxcount');
        $attr_previous_file = Yii::$app->request->post('attr_previous_file');
        $delete_attr_file = Yii::$app->request->post('delete_attr_file');
        $attr_virtual = Yii::$app->request->post('attr_file_switch');

        if (is_array($products_attributes_id)) {
            $_attr_order_array = [];
            foreach ($products_attributes_id as $option_id => $values) {
                $is_virtual_option = \common\helpers\Attributes::is_virtual_option($option_id);
                $_attr_order_array[$option_id] = array_flip(explode(',', strval($products_option_values_sort_order[$option_id]))); //O_O copied out old code hz
                foreach ($values as $value_id => $non) {
                    $attributes_array[$option_id . '-' . $value_id] = [$option_id, $value_id];
                    if (!$is_virtual_option) {
                        if (isset($options[$option_id])) { // separate array for inventory stuff
                            $options[$option_id][] = $value_id;
                        } else {
                            $options[$option_id] = [];
                            $options[$option_id][] = $value_id;
                        }
                    }
                }
            }

///vl2do          save attributes and inventory
            // add file to attribute (fill in).

            /* ??discontinued?? (in inventory a different functionality)
                                          " product_attributes_one_time = '" . tep_db_input($_POST['product_attributes_one_time'][$option_id][$value_id]) .
                                      "', products_attributes_units = '" . tep_db_input($_POST['products_attributes_units'][$option_id][$value_id]) .
                                      "', products_attributes_units_price = '" . tep_db_input($_POST['products_attributes_units_price'][$option_id][$value_id]) .
                                      "', products_attributes_discount_price = '" . tep_db_input($_POST['products_attributes_discount_price'][$option_id][$value_id][0]) .
            */

            /// save attributes, probably "calculate" (generally guess) attribute price if inventory is enabled
            ///inventorypriceprefix_0-33-170[19][0] , products_group_price0-33-170[19][0] products_attributes_weight_prefix[33][170] products_attributes_weight[33][170]

            foreach ($attributes_array as $val) {
                $option_id = $val[0];
                $value_id = $val[1];
                $__attr_order_array = $_attr_order_array[$option_id];
                $sql_data_array = [
                    'price_prefix' => self::getFromPostArrays(['post' => 'inventorypriceprefix_' . $old_products_id . '-'. $option_id . '-' . $value_id, 'dbdef'=>'+'], (int)$_def_curr_id),
                    'options_values_price' => self::getFromPostArrays(['post' => 'products_group_price_' . $old_products_id . '-'. $option_id . '-' . $value_id, 'dbdef'=>'0'], (int)$_def_curr_id), //(USE_MARKET_PRICES == 'True'?xxxx:0)
                    'products_attributes_weight_prefix' => tep_db_prepare_input(isset($products_attributes_weight_prefix[$option_id][$value_id])?$products_attributes_weight_prefix[$option_id][$value_id]:'+'),
                    'default_option_value' => isset($default_option_values[$option_id][$value_id])?1:0,
                ];
                if (isset($__attr_order_array[$value_id])) { // for new def value in DB, for old - only if sort order changed.
                    $sql_data_array['products_options_sort_order'] = (int) $__attr_order_array[$value_id];
                }

                if ($ext = \common\helpers\Extensions::isAllowed('TypicalOperatingTemp')) {
                    $_ext_data = $ext::saveProductAttribute($option_id, $value_id);
                    if ( is_array($_ext_data) ) $sql_data_array = array_merge($sql_data_array, $_ext_data);
                }

                /*
                          $attr_file = Yii::$app->request->post('attr_file');
                          $products_attributes_maxdays = Yii::$app->request->post('products_attributes_maxdays');
                          $products_attributes_maxcount = Yii::$app->request->post('products_attributes_maxcount');
                          $attr_previous_file = Yii::$app->request->post('attr_previous_file');
                           = Yii::$app->request->post('delete_attr_file');
                  */
                if (isset($attr_virtual[$option_id][$value_id]) &&  $attr_virtual[$option_id][$value_id] == 1) {
                    //virtual
                    //delete old file
                    if (isset($delete_attr_file[$option_id][$value_id]) &&  $delete_attr_file[$option_id][$value_id] == 'yes'  &&
                        isset($attr_previous_file[$option_id][$value_id]) &&  tep_not_null($attr_previous_file[$option_id][$value_id]) ) {
                        @unlink(DIR_FS_DOWNLOAD . $attr_previous_file[$option_id][$value_id]);
                        $sql_data_array = array_merge($sql_data_array, [
                            'products_attributes_filename' => '',
                            'products_attributes_maxdays' => 0,
                            'products_attributes_maxcount' => 0]);
                    }
                    //save new
                    if (isset($attr_file[$option_id][$value_id]) &&  tep_not_null($attr_file[$option_id][$value_id])  && $attr_file[$option_id][$value_id]!= 'none') {
                        $tmp_name = \Yii::getAlias('@webroot');
                        $tmp_name .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
                        $tmp_name .= $attr_file[$option_id][$value_id];
                        $new_name = DIR_FS_DOWNLOAD . $attr_file[$option_id][$value_id];
                        copy($tmp_name, $new_name);
                        @unlink($tmp_name);
                        $sql_data_array = array_merge($sql_data_array, [
                            'products_attributes_weight' => 0,
                            'products_attributes_filename' => tep_db_prepare_input($attr_file[$option_id][$value_id]),
                            'products_attributes_maxdays' => (isset($products_attributes_maxdays[$option_id][$value_id]) && (int)$products_attributes_maxdays[$option_id][$value_id]>0 ? (int)$products_attributes_maxdays[$option_id][$value_id]:0),
                            'products_attributes_maxcount' => (isset($products_attributes_maxcount[$option_id][$value_id]) && (int)$products_attributes_maxcount[$option_id][$value_id]>0? (int)$products_attributes_maxcount[$option_id][$value_id]:0)
                        ]);
                    } else {
                        $sql_data_array = array_merge($sql_data_array, [
                            'products_attributes_maxdays' => (isset($products_attributes_maxdays[$option_id][$value_id]) && (int)$products_attributes_maxdays[$option_id][$value_id]>0 ? (int)$products_attributes_maxdays[$option_id][$value_id]:0),
                            'products_attributes_maxcount' => (isset($products_attributes_maxcount[$option_id][$value_id]) && (int)$products_attributes_maxcount[$option_id][$value_id]>0? (int)$products_attributes_maxcount[$option_id][$value_id]:0)
                        ]);
                    }

                } else {
                    $sql_data_array = array_merge($sql_data_array, [
                        'products_attributes_weight' => tep_db_prepare_input(isset($products_attributes_weight[$option_id][$value_id])?$products_attributes_weight[$option_id][$value_id]:0),
                        'products_attributes_filename' => '',
                        'products_attributes_maxdays' => 0,
                        'products_attributes_maxcount' => 0]);
                }

                //if (is_array($sql_data_array['options_values_price'])) { $sql_data_array['options_values_price'] = $sql_data_array['options_values_price'][0]; } // Kostyl'

                $check = tep_db_query("select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $products_id . "' and options_id = '" . (int) $option_id . "' and options_values_id = '" . (int) $value_id . "'");
                if (tep_db_num_rows($check)) {
                    $Qdata = tep_db_fetch_array($check);
                    $products_attributes_id = $Qdata['products_attributes_id'];
                    tep_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $sql_data_array, 'update', "products_attributes_id = '" . (int) $products_attributes_id . "'");
                } else {
                    $sql_data_array['products_id'] = (int) $products_id;
                    $sql_data_array['options_id'] = (int) $option_id;
                    $sql_data_array['options_values_id'] = (int) $value_id;
                    tep_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $sql_data_array);
                    $products_attributes_id = tep_db_insert_id();
                }

                ///group prices
                if ($without_inventory || \common\helpers\Extensions::isAllowed('Inventory') || \common\helpers\Attributes::is_virtual_option($option_id)) {
                    tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . (int) $products_attributes_id . "'");
                    ///['db' => 'products_group_price_packaging', 'dbdef' => -2, 'post' => 'products_group_price_packaging', 'f' => ['self', 'defGroupPrice']],
                    if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                        foreach ($currencies_ids as $post_currencies_id => $currencies_id) {
                            foreach ($groups as $groups_id => $non) {
                                $sql_data_array = [
                                    'attributes_group_price' => tep_db_prepare_input(self::getFromPostArrays(
                                        ['db' => 'attributes_group_price', 'dbdef' => ($groups_id==0?0:-2), 'post' => 'products_group_price_' . $old_products_id . '-'. $option_id . '-' . $value_id, 'f' => ['self', 'defGroupPrice']]
                                        , $post_currencies_id, $groups_id
                                    )),
                                    'groups_id' => tep_db_prepare_input($groups_id),
                                    'products_attributes_id' => tep_db_prepare_input($products_attributes_id),
                                    'currencies_id' => (USE_MARKET_PRICES == 'True'?tep_db_prepare_input($post_currencies_id):0),
                                    //'attributes_group_discount_price' =>
                                ];
                                tep_db_perform(TABLE_PRODUCTS_ATTRIBUTES_PRICES, $sql_data_array);
                            }
                        }
                    }
                }
            }

            /** @var \common\extensions\Inventory\Inventory $inventoryExt */
            if (($inventoryExt = \common\helpers\Extensions::isAllowed('Inventory')) && !$without_inventory) {
                /// no direct attributes info
                // inventorypriceprefix_0{33}171{34}174[19][0], products_group_price_0{33}171{34}174[19][0] qty_discount_status0{33}171{34}174[19][0] discount_price_0{33}171{34}174[15][0][10]
                ksort($options);
                reset($options);
                $i = 0;
                $idx = 0;
                foreach ($options as $key => $value) {
                    if ($i == 0) {
                        $idx = $key;
                        $i = 1;
                    }
                    asort($options[$key]);
                }
                $inventory_options = \common\helpers\Inventory::get_inventory_uprid($options, $idx);

                foreach ($inventory_options as $tmp) {
                    $post_uprid = $old_products_id . $tmp;
                    $db_uprid = $products_id . $tmp;
                    if (Yii::$app->request->post('inventoryexistent_' . $post_uprid) === null && Yii::$app->request->post('inventorystock_delivery_terms_' . $post_uprid) === null) {
                        // If inventory data is not set - skip it
                        $check_inventory = \common\models\Inventory::findOne(['products_id' => $db_uprid]);
                        if (is_object($check_inventory)) {
                            $all_inventory_ids_array[] = $check_inventory->inventory_id;
                            $all_inventory_uprids_array[] = $db_uprid;
                            continue;
                        }
                        if (!method_exists($inventoryExt, 'optionGenerateInventoryPartly') || $inventoryExt::optionGenerateInventoryPartly()) {
                            continue;
                        }
                    }
                    $arr = [];
                    preg_match_all('/\}(\d+)/', $post_uprid, $arr);
                    $oids = array_map('intval', $arr[1]);
                    $tmp = Yii::$app->request->post('products_name');
                    $label = $tmp[$languages_id] ?? null;
                    if (count($oids) > 0) {
                        $query = tep_db_query("SELECT products_options_values_name AS name FROM " . TABLE_PRODUCTS_OPTIONS_VALUES . " WHERE products_options_values_id  IN ('" . implode("','", $oids) . "') AND language_id  = '" . (int)$languages_id . "' ORDER BY products_options_values_sort_order, products_options_values_id");
                        while ($options_values_name_data = tep_db_fetch_array($query)) {
                            $label .= ' ' . $options_values_name_data['name'];
                        }
                    }
                    /// 2check inventorypriceprefix_ always is set even if $products_price_full=1  !!!????
                    $non_existent = Yii::$app->request->post('inventoryexistent_' . $post_uprid, 0);
                    $sql_data_array = [
                        'products_name' => tep_db_prepare_input($label),
                        'non_existent' => tep_db_prepare_input($non_existent),
                    ];

                    if (!is_null(\Yii::$app->request->post('inventorystock_indication_' . $post_uprid))) {
                        $sql_data_array['stock_indication_id'] = intval($_POST['inventorystock_indication_' . $post_uprid]);
                    }
                    if (!is_null(\Yii::$app->request->post('inventorystock_delivery_terms_' . $post_uprid))) {
                        $sql_data_array['stock_delivery_terms_id'] = intval($_POST['inventorystock_delivery_terms_' . $post_uprid]);
                    }

                    $price = self::getFromPostArrays(['post' => 'products_group_price_' . $post_uprid, 'dbdef' => '0'], (int)$_def_curr_id);
                    if ($products_price_full) {
                        $sql_data_array['inventory_full_price'] = tep_db_prepare_input($price);
                        $sql_data_array['price_prefix'] = '+';
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('AttributesQuantity', 'allowed')) {
                            //2do nice to have check parent q-ty discount threshholds
                            $sql_data_array['inventory_discount_full_price'] = self::getFromPostArrays(['db' => 'inventory_discount_full_price', 'dbdef' => '', 'post' => 'discount_price' . $post_uprid, 'flag' => 'qty_discount_status' . $post_uprid, 'f' => ['self', 'formatDiscountString']], $_def_curr_id);
                        }
                    } else {
                        $sql_data_array['price_prefix'] = tep_db_prepare_input(self::getFromPostArrays(['post' => 'inventorypriceprefix_' . $post_uprid, 'dbdef' => '+'], (int)$_def_curr_id, 0));
                        $sql_data_array['inventory_price'] = tep_db_prepare_input($price);

                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('AttributesQuantity', 'allowed')) {
                            //2do nice to have check parent q-ty discount threshholds
                            $sql_data_array['inventory_discount_price'] = self::getFromPostArrays(['db' => 'inventory_discount_price', 'dbdef' => '', 'post' => 'discount_price' . $post_uprid, 'flag' => 'qty_discount_status' . $post_uprid, 'f' => ['self', 'formatDiscountString']], $_def_curr_id);
                        }
                    }


                    $sql_data_array['supplier_price_manual'] = self::getFromPostArrays(['dbdef' => 'null', 'post' => 'supplier_auto_price_' . $post_uprid], (int)$_def_curr_id, 0);
                    // posted auto, make manual
                    $sql_data_array['supplier_price_manual'] = $sql_data_array['supplier_price_manual'] == '1' ? 0 : 1;
                    // reset matched with current config
                    if (($sql_data_array['supplier_price_manual'] == 1 && SUPPLIER_UPDATE_PRICE_MODE == 'Manual') || ($sql_data_array['supplier_price_manual'] == 0 && SUPPLIER_UPDATE_PRICE_MODE == 'Auto')) {
                        $sql_data_array['supplier_price_manual'] = 'null';
                    }

                    $sql_data_array['inventory_tax_class_id'] = 'null';
                    $inventory_tax_class_id = Yii::$app->request->post('inventory_tax_class_id_' . $post_uprid, null);
                    if (!is_null($inventory_tax_class_id)) $sql_data_array['inventory_tax_class_id'] = (int)$inventory_tax_class_id;

                    $check_data = tep_db_fetch_array(tep_db_query("SELECT inventory_id FROM " . TABLE_INVENTORY . " WHERE products_id = '" . tep_db_input($db_uprid) . "'"));
                    if ($check_data) {
                        $inventory_id = $check_data['inventory_id'];
                        tep_db_perform(TABLE_INVENTORY, $sql_data_array, 'update', "inventory_id = '" . (int)$inventory_id . "'");
                    } else {
                        $sql_data_array['products_id'] = tep_db_prepare_input($db_uprid);
                        $sql_data_array['prid'] = tep_db_prepare_input($products_id);
                        tep_db_perform(TABLE_INVENTORY, $sql_data_array);
                        $inventory_id = tep_db_insert_id();
                    }

                    ///group prices
                    tep_db_query("DELETE FROM " . TABLE_INVENTORY_PRICES . " WHERE inventory_id = '" . (int)$inventory_id . "'");

                    ///['db' => 'products_group_price_packaging', 'dbdef' => -2, 'post' => 'products_group_price_packaging', 'f' => ['self', 'defGroupPrice']],
                    if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                        foreach ($currencies_ids as $post_currencies_id => $currencies_id) {
//in tpl currency_id should be always correct, so w/o market price: $post_currencies_id (15) => $currencies_id (0)
                            foreach ($groups as $groups_id => $non) {
                                $sql_data_array = [
                                    'inventory_id' => tep_db_prepare_input($inventory_id),
                                    'products_id' => tep_db_prepare_input($db_uprid),
                                    'prid' => tep_db_prepare_input($products_id),
                                    'groups_id' => tep_db_prepare_input($groups_id),
                                    'currencies_id' => tep_db_prepare_input($currencies_id),
                                ];
                                $price = self::getFromPostArrays(['post' => 'products_group_price_' . $post_uprid, 'dbdef' => ((int)$groups_id == 0 ? 0 : '-2'), 'f' => ['self', 'defGroupPrice']], (int)$post_currencies_id, $groups_id);
                                if ($products_price_full) {
                                    //inventory_full_price, inventory_discount_full_price,
                                    $sql_data_array['inventory_full_price'] = tep_db_prepare_input($price);
                                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('AttributesQuantity', 'allowed')) {
                                        //2do nice to have check parent q-ty discount thresholds
                                        $sql_data_array['inventory_discount_full_price'] = self::getFromPostArrays(['dbdef' => '', 'post' => 'discount_price' . $post_uprid, 'flag' => 'qty_discount_status' . $post_uprid, 'f' => ['self', 'formatDiscountString']], $post_currencies_id, $groups_id);
                                    }
                                } else {
                                    //inventory_group_price, inventory_group_discount_price,
                                    $sql_data_array['inventory_group_price'] = tep_db_prepare_input($price);
                                    $sql_data_array['price_prefix'] = tep_db_prepare_input(self::getFromPostArrays(['post' => 'inventorypriceprefix_' . $post_uprid, 'dbdef' => '+'], (int)$post_currencies_id, $groups_id));
                                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('AttributesQuantity', 'allowed')) {
                                        //2do nice to have check parent q-ty discount threshholds
                                        $sql_data_array['inventory_group_discount_price'] = self::getFromPostArrays(['dbdef' => '', 'post' => 'discount_price' . $post_uprid, 'flag' => 'qty_discount_status' . $post_uprid, 'f' => ['self', 'formatDiscountString']], $post_currencies_id, $groups_id);
                                    }
                                }
                                $sql_data_array['supplier_price_manual'] = self::getFromPostArrays(['db' => 'supplier_price_manual', 'dbdef' => 'null', 'post' => 'supplier_auto_price_' . $post_uprid], (int)$post_currencies_id, $groups_id);
                                if ($groups_id == 0) {
                                    // posted auto, make manual
                                    $sql_data_array['supplier_price_manual'] = $sql_data_array['supplier_price_manual'] == '1' ? 0 : 1;
                                    // reset matched with current config
                                    if (($sql_data_array['supplier_price_manual'] == 1 && SUPPLIER_UPDATE_PRICE_MODE == 'Manual') || ($sql_data_array['supplier_price_manual'] == 0 && SUPPLIER_UPDATE_PRICE_MODE == 'Auto')) {
                                        unset($sql_data_array['supplier_price_manual']);
                                    }
                                } else {
                                    unset($sql_data_array['supplier_price_manual']);
                                }

                                tep_db_perform(TABLE_INVENTORY_PRICES, $sql_data_array);
                            }
                        }
                    }

                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
                        $ext::saveInventory($db_uprid, $post_uprid);
                    }

                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('AttributesDetails', 'allowed')) {
                        $ext::saveProduct($inventory_id, $post_uprid);
                    }

                    // Update stock quantity
                    /*$inventory_quantity_update = intval(Yii::$app->request->post('inventoryqtyupdate_' . $post_uprid));
                    $inventory_quantity_update_prefix = tep_db_prepare_input(Yii::$app->request->post('inventoryqtyupdateprefix_' . $post_uprid) == '-' ? '-' : '+');
                    if ($inventory_quantity_update > 0) {
                      $warehouse_id = intval(Yii::$app->request->post('warehouse_id_' . $post_uprid));
                      $w_suppliers_id = (int)Yii::$app->request->post('w_suppliers_id_' . $post_uprid, 0);
                      $stock_comments = tep_db_prepare_input(Yii::$app->request->post('stock_comments_' . $post_uprid));
                      global $login_id;
                      //\common\helpers\Product::log_stock_history_before_update($db_uprid, $inventory_quantity_update, $inventory_quantity_update_prefix, ['warehouse_id' => $warehouse_id, 'comments' => TEXT_MANUALL_STOCK_UPDATE . (trim($stock_comments) != '' ? ': ' . $stock_comments : ''), 'admin_id' => $login_id]);
                      if ($warehouse_id > 0) {
                        $parameters = [
                            'admin_id' => $login_id,
                            'comments' => (TEXT_MANUALL_STOCK_UPDATE . (trim($stock_comments) != '' ? ': ' . $stock_comments : ''))
                        ];
                        \common\helpers\Warehouses::update_products_quantity($db_uprid, $warehouse_id, $inventory_quantity_update, $inventory_quantity_update_prefix, $w_suppliers_id, 0, $parameters);
                        \common\helpers\Warehouses::get_allocated_stock_quantity($db_uprid);
                        \common\helpers\Warehouses::get_temporary_stock_quantity($db_uprid);
                      } else {
                        tep_db_query("update " . TABLE_INVENTORY . " set products_quantity = products_quantity " . $inventory_quantity_update_prefix . $inventory_quantity_update . " where products_id = '" . tep_db_input($db_uprid) . "'");
                      }
                    }*/

                    $all_inventory_ids_array[] = $inventory_id;
                    $all_inventory_uprids_array[] = $db_uprid;

                    if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
                        $extScl::saveAttributesAndInventorySave($db_uprid);
                    }
                }
                \common\models\Inventory::deleteAll(['AND', ['prid' => (int)$products_id], ['NOT IN', 'inventory_id', $all_inventory_ids_array]]);
                \common\models\InventoryPrices::deleteAll(['AND', ['prid' => (int)$products_id], ['NOT IN', 'inventory_id', $all_inventory_ids_array]]);
                $all_inventory_uprids_array[] = trim((int)$products_id);
                \common\models\WarehousesProducts::deleteAll(['AND', ['prid' => (int)$products_id], ['NOT IN', 'products_id', $all_inventory_uprids_array]]);

            }elseif (\common\helpers\Extensions::isAllowed('Inventory') && $without_inventory) {
                \common\models\Inventory::deleteAll( ['prid' => (int) $products_id] );
                \common\models\InventoryPrices::deleteAll( ['prid' => (int) $products_id] );
                \common\models\WarehousesProducts::deleteAll( ['AND', ['prid' => (int) $products_id], ['!=', 'products_id', trim((int) $products_id)]] );
            }


            if (\common\helpers\Extensions::isAllowed('Inventory')) {
                $inventory_quantity = tep_db_fetch_array(tep_db_query(
                    "SELECT SUM(products_quantity) AS left_quantity " .
                    "FROM " . TABLE_INVENTORY . " " .
                    "WHERE prid = '" . (int) $products_id . "' AND IFNULL(non_existent,0)=0 " .
                    " AND products_quantity>0"
                ));
                tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int) $inventory_quantity['left_quantity'] . "' where products_id = '" . (int) $products_id . "'");
                \common\helpers\Warehouses::update_sum_of_inventory_quantity($products_id);
            }
        } else {
            /// no attributes / all attributes has been deleted

            if (\common\helpers\Extensions::isAllowed('Inventory')) {
                \common\models\Inventory::deleteAll( ['prid' => (int) $products_id] );
                \common\models\InventoryPrices::deleteAll( ['prid' => (int) $products_id] );
                \common\models\WarehousesProducts::deleteAll( ['AND', ['prid' => (int) $products_id], ['!=', 'products_id', trim((int) $products_id)]] );
            }
        }

        ///clean-up product attributes and prices
        $Qcheck = tep_db_query("select products_attributes_id, products_attributes_filename from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $products_id . "' and concat(options_id, '-', options_values_id) not in ('" . implode("', '", array_keys($attributes_array) ) . "')");
        if (tep_db_num_rows($Qcheck)) {
            while ($data = tep_db_fetch_array($Qcheck)) {
                tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . (int) $data['products_attributes_id'] . "'");
                if (DOWNLOAD_ENABLED == true || $data['products_attributes_filename'] != '') {
                    @unlink(DIR_FS_DOWNLOAD . $data['products_attributes_filename']);
                }
                tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id = '" . (int) $data['products_attributes_id'] . "'");
            }
        }

        $switch_off_stock_ids = \common\classes\StockIndication::productDisableByStockIds();
        if (!empty($switch_off_stock_ids)) {
            tep_db_query(
                "update " . TABLE_PRODUCTS . " " .
                "set products_status = 0 " .
                "where products_id = '" . (int) $products_id . "' " .
                " AND products_quantity<=0 " .
                " AND stock_indication_id IN ('" . implode("','", $switch_off_stock_ids) . "')"
            );
        }

        $settings = \common\helpers\Product::getSettings($products_id);
        if ($settings){
            $saq = Yii::$app->request->post('show_attributes_quantity', 0);
            if ((int)Yii::$app->request->post('is_bundle') || !\common\helpers\Attributes::has_product_attributes($products_id)) $saq = 0;
            $settings->saveSettings($products_id, ['show_attributes_quantity' => $saq]);
        }
        return $all_inventory_uprids_array;
    }

    /**
     * check & returns data from marketing tabs if any
     * @field ['db' => 'products_price_discount_pack_unit', db field name --- not required at all :(
     * 'postreindex' => 'discount_qty_pack_unit', POST - change array keys to
     * 'post' => 'discount_price_pack_unit', POST key. Required!
     * 'flag' => 'qty_discount_status_pack_unit', POST switcher flag (1 - on!!! someone use yes o_O )
     * 'f' => ['self', 'formatDiscountString']] - validator - callback
     */
    private static function getFromPostArrays($field, $curr_id, $group_id=0) {
        return \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays($field, $curr_id, $group_id);
    }
}