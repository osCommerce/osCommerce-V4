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
use common\helpers\Tax;
use common\helpers\Inventory as InventoryHelper;

class Bundles {
    use SqlTrait;
    /**
     *
     * @param array $params
     * @param array $attributes_details
     * @return array|''
     */
    public static function getDetails($params, $attributes_details = array(), $skip_attributes = false) {
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        if ( !$params['products_id'] ) return '';
        /** @var \common\classes\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');
        $bundle_products = array();
        $bundle_products_attributes = array();
        $languages_id = \Yii::$app->settings->get('languages_id');

        $main_attributes = array();
        $params['qty'] = $params['qty'] ?? null;
        $attributes = (isset($params['id']) ? $params['id'] : null);
        if (is_array($attributes)) {
          //$mainUprid = InventoryHelper::normalize_id(InventoryHelper::get_uprid($params['products_id'], $attributes));
          foreach ($attributes as $key => $value) {
            if (strpos($key, '-') !== false) {
                list($bundle_item_attr_id, $bundle_item_id) = explode('-', $key);
                $bundle_products_attributes[$bundle_item_id][$bundle_item_attr_id] = $value;
            } else {
                $main_attributes[$key] = $value;
            }
          }
          $mainUprid = InventoryHelper::normalize_id(InventoryHelper::get_uprid($params['products_id'], $main_attributes));
        } else {
          $mainUprid = $params['products_id'];
        }

        $products_join = '';
        if ( \common\classes\platform::activeId() ) {
          $products_join .= self::sqlProductsToPlatform();
        }
        
        $sqlWhere = '';
        $sqlWhere .= " AND p.products_status = 1 ";

        $bundle_sets_query = tep_db_query(
          "select p.products_id, p.products_model, p.products_image, p.products_tax_class_id, sp.num_product, ".
          "parent_p.products_tax_class_id AS parent_products_tax_class_id, ".
          "IF(p.is_listing_product AND p.products_status, 1, 0) AS _status, ".
          "if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name ".
          "from " . TABLE_PRODUCTS . " p {$products_join} " .
          "  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int)$languages_id ."' and pd1.platform_id = '" . intval(\Yii::$app->get('platform')->config()->getPlatformToDescription()) . "' ".
          " left join " . TABLE_PRODUCTS_PRICES . " pgp on p.products_id = pgp.products_id and pgp.groups_id = '" . (int)$customer_groups_id . "' and pgp.currencies_id = '" . (int)(USE_MARKET_PRICES == 'True' ? (int)\Yii::$app->settings->get('currency_id') : 0) . "', "  . TABLE_PRODUCTS_DESCRIPTION . " pd, ".
          "" . TABLE_SETS_PRODUCTS . " sp ".
          " inner join ".TABLE_PRODUCTS." parent_p on parent_p.products_id=sp.sets_id ".
          "where sp.product_id = p.products_id and sp.product_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' ".
          " and sp.sets_id = '" . (int)$params['products_id'] . "' and p.products_status_bundle = '1' " /* . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) */ . " " . ((isset($_SESSION['affiliate_ref']) && $_SESSION['affiliate_ref'] > 0) ? " and p2a.affiliate_id is not null ":'') . " and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and if(pgp.products_group_price is null, 1, pgp.products_group_price != -1 ) ".
              $sqlWhere . " " .
          "group by p.products_id ".
          "order by sp.sort_order"
        );
        if (tep_db_num_rows($bundle_sets_query) > 0)
        {
          $all_filled_array = array();
          $product_qty_array = array();
          $quantity_max_array = array();
          $stock_indicators_ids = array();
          $stock_indicators_array = array();
          $all_stock_indicators = array();
          $full_bundle_price = $actual_bundle_price = $actual_bundle_price_unit = 0;
          $full_bundle_price_ex = $actual_bundle_price_ex = $actual_bundle_price_unit_ex = 0;


          $bundle_tax_class_id = 0;
          $bundle_products_id = $params['products_id'];
          while ($bundle_sets = tep_db_fetch_array($bundle_sets_query))
          {
            $products_id = $bundle_sets['products_id'];
            if (is_array($bundle_products_attributes[$products_id] ?? null)) {
              $attributes = $bundle_products_attributes[$products_id];
            } else {
              $attributes = array();
            }
            $bundle_tax_class_id = $bundle_sets['parent_products_tax_class_id'];

            $uprid = InventoryHelper::normalize_id(InventoryHelper::get_uprid($products_id, $attributes));
            unset($params['products_id']);
            $priceInstance = \common\models\Product\Price::getInstance($uprid);
            $products_price = $priceInstance->getInventoryPrice(['qty' => $params['qty']/* * $bundle_sets['num_product']*/]);

            $special_price = $priceInstance->getInventorySpecialPrice(['qty' => $params['qty']]);
            $actual_product_price = $products_price;


            $products_options_name_query = tep_db_query("select distinct p.products_id, p.products_tax_class_id, popt.products_options_id, popt.products_options_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where p.products_id = '" . (int)$products_id . "' and patrib.products_id = p.products_id and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' order by popt.products_options_sort_order, popt.products_options_name");
            while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
                if (!isset($attributes[$products_options_name['products_options_id']])) {
                    $check = tep_db_fetch_array(tep_db_query("select max(pov.products_options_values_id) as values_id, count(pov.products_options_values_id) as values_count from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$products_id . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "'"));
                    if ($check['values_count'] == 1) { // if only one option value - it should be selected
                        $attributes[$products_options_name['products_options_id']] = $check['values_id'];
                    } else {
                        $attributes[$products_options_name['products_options_id']] = 0;
                    }
                }
            }

            $all_filled = true;
/*
            if (isset($attributes) && is_array($attributes))
            foreach($attributes as $value) {
               $all_filled = $all_filled && (bool)$value;
            }
*/

            $attributes_array = array();
// {{
            if (!$skip_attributes) {
// }}
            tep_db_data_seek($products_options_name_query, 0);
            while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
                $selected_attribute = false;
                $products_options_array = array();
                $products_options_query = tep_db_query(
                  "select pa.products_attributes_id, pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix ".
                  "from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov ".
                  "where pa.products_id = '" . (int)$products_id . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "' ".
                  "order by pa.products_options_sort_order, pov.products_options_values_sort_order, pov.products_options_values_name");
                while ($products_options = tep_db_fetch_array($products_options_query)) {
                    $comb_arr = $attributes;
                    $comb_arr[$products_options_name['products_options_id']] = $products_options['products_options_values_id'];
                    foreach ($comb_arr as $opt_id => $val_id) {
                        if ( !($val_id > 0) ) {
                            $comb_arr[$opt_id] = '0000000';
                        }
                    }
                    reset($comb_arr);
                    $mask = str_replace('0000000', '%', InventoryHelper::normalizeInventoryId(InventoryHelper::get_uprid($products_id, $comb_arr), $vids, $virtual_vids));
                    $check_inventory = tep_db_fetch_array(tep_db_query(
                      "select inventory_id, max(products_quantity) as products_quantity, min(non_existent) as non_existent, products_id, stock_indication_id, stock_delivery_terms_id, stock_control, products_id ".
                     // " min(if(price_prefix = '-', -inventory_price, inventory_price)) as inventory_price, min(inventory_full_price) as inventory_full_price ".
                      "from " . TABLE_INVENTORY . " i ".
                      "where products_id like '" . tep_db_input($mask) . "' " . InventoryHelper::get_sql_inventory_restrictions(array('i', 'ip'))  . " ".
                      "order by products_quantity desc ".
                      "limit 1"
                    ));
                    if (!$check_inventory['inventory_id']) continue;
                    if ($check_inventory['non_existent']) continue;
                    $priceInstance = \common\models\Product\Price::getInstance($mask);
                    $products_price = $priceInstance->getInventoryPrice($params);
                    $check_inventory['products_quantity'] = \common\helpers\Product::getAvailable($check_inventory['products_id'], 0);

                    //$check_inventory['inventory_price'] = InventoryHelper::get_inventory_price_by_uprid($mask, $params['qty'] * $bundle_sets['num_product'], $check_inventory['inventory_price']);
                    //$check_inventory['inventory_full_price'] = InventoryHelper::get_inventory_full_price_by_uprid($mask, $params['qty'] * $bundle_sets['num_product'], $check_inventory['inventory_full_price']);
                    if ($priceInstance->calculate_full_price && $products_price == -1) {
                        continue; // Disabled for specific group
                    } elseif ($products_price == -1) {
                        continue; // Disabled for specific group
                    }

                    /*$special_price = $priceInstance->getInventorySpecialPrice($params);
                    if ($special_price !== false){
                        $products_price = $special_price;
                    }*/
                    /*if ($product['products_price_full'] && $check_inventory['inventory_full_price'] == -1) {
                        continue; // Disabled for specific group
                    } elseif ($check_inventory['inventory_price'] == -1) {
                        continue; // Disabled for specific group
                    }*/

                    if ($virtual_vids) {
                        $virtual_attribute_price = \common\helpers\Attributes::get_virtual_attribute_price($products_id, $virtual_vids, $params['qty'], $products_price);
                        if ($virtual_attribute_price === false) {
                            continue; // Disabled for specific group
                        } else {
                            $products_price += $virtual_attribute_price;
                        }
                    }

                    $products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name'], 'price_diff' => 0);
                    $price_diff = $products_price - $actual_product_price;
                    /*if ($product['products_price_full']) {
                        $price_diff = $check_inventory['inventory_full_price'] - $actual_product_price;
                    } else {
                        $price_diff = $product_price_old + $check_inventory['inventory_price'] - $actual_product_price;
                    }*/
                    if ($price_diff != '0') {
                        $products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . ($price_diff < 0 ? '-' : '+') . $currencies->display_price(abs($price_diff), Tax::get_tax_rate($products_options_name['products_tax_class_id']), 1, false) .') ';
                    }
                    $products_options_array[sizeof($products_options_array)-1]['price_diff'] = $price_diff;

                    $stock_indicator = \common\classes\StockIndication::product_info(array(
                      'products_id' => $check_inventory['products_id'],
                      'products_quantity' => ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : '0'),
                      'stock_indication_id' => (isset($check_inventory['stock_indication_id'])?$check_inventory['stock_indication_id']:null),
                      'stock_delivery_terms_id' => (isset($check_inventory['stock_delivery_terms_id'])?$check_inventory['stock_delivery_terms_id']:null),
                    ));

                    if ( !($check_inventory['products_quantity'] > 0) && strstr($mask, '{') ) {
                      $products_options_array[sizeof($products_options_array)-1]['params'] = ' class="outstock" data-max-qty="' . (int)$stock_indicator['max_qty'] . '"';
                      $products_options_array[sizeof($products_options_array)-1]['text'] .= ' - ' . strip_tags($stock_indicator['stock_indicator_text_short']);
                    } else {
                      $products_options_array[sizeof($products_options_array)-1]['params'] = ' class="_outstock_" data-max-qty="'.(int)$stock_indicator['max_qty'].'"';
                    }

                    if ($attributes[$products_options_name['products_options_id']] > 0) {
                        $selected_attribute = $attributes[$products_options_name['products_options_id']];
//                    } elseif (isset($cart->contents[$bundle_products_id]['attributes'][$products_options_name['products_options_id'] . '-' . $products_id])) {
//                        $selected_attribute = $cart->contents[$bundle_products_id]['attributes'][$products_options_name['products_options_id'] . '-' . $products_id];
                    } elseif (($bundle_product_pos = strpos($bundle_products_id, '|' . $products_id . '{')) !== false) {
                        if (strpos(substr($bundle_products_id, $bundle_product_pos, strpos($bundle_products_id, '{sub}', $bundle_product_pos) - $bundle_product_pos),  '{' . $products_options_name['products_options_id'] . '}' . $products_options['products_options_values_id']) !== false) {
                            $selected_attribute = $products_options['products_options_values_id'];
                        }
                    } else {
                        $selected_attribute = false;
                    }
                }

                if (count($products_options_array) > 0) {
                    $all_filled = $all_filled && (bool) $attributes[$products_options_name['products_options_id']];
                    $attributes_array[] = array(
                        'title' => htmlspecialchars($products_options_name['products_options_name']),
                        'name' => 'id[' . $products_options_name['products_options_id'] . '-' . $products_id . ']',
                        'id' => $products_options_name['products_options_id'] . '-' . $products_id,
                        'options' => $products_options_array,
                        'selected' => $selected_attribute,
                    );
                }

            }
// {{
            } // end if (!$skip_attributes)
// }}

            $product_query = tep_db_query("select products_id, products_price, products_tax_class_id, stock_indication_id, stock_delivery_terms_id, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
            $_backup_products_quantity = 0;
            $_backup_stock_indication_id = $_backup_stock_delivery_terms_id = 0;
            if ($product = tep_db_fetch_array($product_query)) {
                $_backup_products_quantity = $product['products_quantity'];
                $_backup_stock_indication_id = $product['stock_indication_id'];
                $_backup_stock_delivery_terms_id = $product['stock_delivery_terms_id'];
            }
            $current_uprid = InventoryHelper::normalize_id(InventoryHelper::get_uprid($products_id, $attributes));
            $bundle_sets['current_uprid'] = $current_uprid;

            $check_inventory = tep_db_fetch_array(tep_db_query(
              "select inventory_id, products_quantity, stock_indication_id, stock_delivery_terms_id ".
              "from " . TABLE_INVENTORY . " ".
              "where products_id like '" . tep_db_input(InventoryHelper::normalizeInventoryId($current_uprid)) . "' ".
              "limit 1"
            ));
            \common\helpers\Php8::nullArrProps($check_inventory, ['inventory_id', 'products_quantity', 'stock_indication_id', 'stock_delivery_terms_id']);
            $stock_indicator = \common\classes\StockIndication::product_info(array(
              'products_id' => $current_uprid,
              'products_quantity' => ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : $_backup_products_quantity),
              'stock_indication_id' => (isset($check_inventory['stock_indication_id'])?$check_inventory['stock_indication_id']:$_backup_stock_indication_id),
              'stock_delivery_terms_id' => (isset($check_inventory['stock_delivery_terms_id'])?$check_inventory['stock_delivery_terms_id']:$_backup_stock_delivery_terms_id),
            ));
            $stock_indicator_public = $stock_indicator['flags'];
            $stock_indicator_public['quantity_max'] = \common\helpers\Product::filter_product_order_quantity($current_uprid, $stock_indicator['max_qty'], true);
            $stock_indicator_public['stock_code'] = $stock_indicator['stock_code'];
            $stock_indicator_public['text_stock_code'] = $stock_indicator['text_stock_code'];
            $stock_indicator_public['stock_indicator_text'] = $stock_indicator['stock_indicator_text'];
            $stock_indicator_public['products_date_available'] = (!empty($stock_indicator['products_date_available'])? sprintf(TEXT_EXPECTED_ON , '', \common\helpers\Date::date_short($stock_indicator['products_date_available'])):'');
            if ($stock_indicator_public['request_for_quote']) {
              $special_price = false;
            }
            $bundle_sets['stock_indicator'] = $stock_indicator_public;

            $stock_indicator['id'] = $stock_indicator['id'] ?? null;
            if ($stock_indicator['id'] > 0 && !$stock_indicator['is_hidden']) {
              $stock_indicators_ids[] = $stock_indicator['id'];
              $stock_indicators_array[$stock_indicator['id']][] = $stock_indicator_public;
            }
//vl2do - id is 0 - details are diff
            $all_stock_indicators[] = $stock_indicator_public;

            $bundle_sets['all_filled'] = $all_filled;
            $bundle_sets['product_qty'] = ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : ($product['products_quantity']?$product['products_quantity']:'0'));

            $all_filled_array[] = $bundle_sets['all_filled'];
            $product_qty_array[] = floor($bundle_sets['num_product'] > 0 ? $bundle_sets['product_qty'] / $bundle_sets['num_product'] : $bundle_sets['product_qty']);
            $quantity_max_array[] = floor($bundle_sets['num_product'] > 0 ? $stock_indicator_public['quantity_max'] / $bundle_sets['num_product'] : $stock_indicator_public['quantity_max']);

            if ($special_price !== false) {
              $bundle_sets['price_old'] = $currencies->display_price($actual_product_price, Tax::get_tax_rate($bundle_sets['products_tax_class_id']), $bundle_sets['num_product']);
              $bundle_sets['price_special'] = $currencies->display_price($special_price, Tax::get_tax_rate($bundle_sets['products_tax_class_id']), $bundle_sets['num_product']);
              $full_bundle_price += $currencies->display_price_clear($special_price, Tax::get_tax_rate($bundle_sets['products_tax_class_id']), $bundle_sets['num_product']);
              if (/*Tax::get_tax_rate($bundle_sets['products_tax_class_id'])>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                $full_bundle_price_ex += $currencies->display_price_clear($special_price, 0, $bundle_sets['num_product']);
              }
            } else {
              $bundle_sets['price'] = $currencies->display_price($actual_product_price, Tax::get_tax_rate($bundle_sets['products_tax_class_id']), $bundle_sets['num_product']);
              $full_bundle_price += $currencies->display_price_clear($actual_product_price, Tax::get_tax_rate($bundle_sets['products_tax_class_id']), $bundle_sets['num_product']);
              if (/*Tax::get_tax_rate($bundle_sets['products_tax_class_id'])>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                $full_bundle_price_ex += $currencies->display_price_clear($actual_product_price, 0, $bundle_sets['num_product']);
              }
            }

            $priceInstance = \common\models\Product\BundlePrice::getInstance($uprid);
            $bundle_price = $priceInstance->getBundlePrice(['qty' => $params['qty'] * $bundle_sets['num_product'], 'parent' => $mainUprid]);
            $bundle_special_price = $priceInstance->getBundleSpecialPrice(['qty' => $params['qty'] * $bundle_sets['num_product'], 'parent' => $mainUprid]);
            if ($bundle_special_price !== false) {
                $actual_bundle_price += $currencies->display_price_clear($bundle_special_price, Tax::get_tax_rate($bundle_sets['products_tax_class_id']), $bundle_sets['num_product']);
                $actual_bundle_price_unit += $currencies->display_price_clear($bundle_special_price, 0, $bundle_sets['num_product']);
                if (/*Tax::get_tax_rate($bundle_sets['products_tax_class_id'])>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                  $actual_bundle_price_ex += $currencies->display_price_clear($bundle_special_price, 0, $bundle_sets['num_product']);
                  $actual_bundle_price_unit_ex += $currencies->display_price_clear($bundle_special_price, 0, $bundle_sets['num_product']);
                }
            } else {
                $actual_bundle_price += $currencies->display_price_clear($bundle_price, Tax::get_tax_rate($bundle_sets['products_tax_class_id']), $bundle_sets['num_product']);
                $actual_bundle_price_unit += $currencies->display_price_clear($bundle_price, 0, $bundle_sets['num_product']);
                if (/*Tax::get_tax_rate($bundle_sets['products_tax_class_id'])>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                  $actual_bundle_price_ex += $currencies->display_price_clear($bundle_price, 0, $bundle_sets['num_product']);
                  $actual_bundle_price_unit_ex += $currencies->display_price_clear($bundle_price, 0, $bundle_sets['num_product']);
                }
            }

            $bundle_sets['attributes_array'] = $attributes_array;

            $bundle_sets['image'] = \common\classes\Images::getImageUrl($bundle_sets['products_id'], 'Small');
            if ($bundle_sets['_status']) {
                $bundle_sets['product_link'] = tep_href_link('catalog/product', 'products_id=' . $bundle_sets['products_id']);
            }else{
                $bundle_sets['product_link'] = tep_href_link('catalog/product', 'products_id=' . (int)$bundle_products_id);
            }

            $bundle_products[] = $bundle_sets;
          }

          $priceInstance = \common\models\Product\Price::getInstance($mainUprid);
          $products_price = $priceInstance->getInventoryPrice(['qty' => $params['qty']]);
          $special_price = $priceInstance->getInventorySpecialPrice(['qty' => $params['qty']]);

          $check_parent = \common\helpers\Product::getProductColumns((int) \common\helpers\Inventory::get_prid($mainUprid), ['use_sets_discount']);
          if ($check_parent['use_sets_discount']) {
            $full_bundle_price += $currencies->display_price_clear($products_price, Tax::get_tax_rate($bundle_tax_class_id));
            if (/*Tax::get_tax_rate($bundle_tax_class_id)>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
              $full_bundle_price_ex += $currencies->display_price_clear($products_price, 0);
            }
          }
          if ($special_price !== false) {
            $actual_bundle_price += $currencies->display_price_clear($special_price, Tax::get_tax_rate($bundle_tax_class_id));
            $actual_bundle_price_unit += $currencies->display_price_clear($special_price, 0);
            if (/*Tax::get_tax_rate($bundle_tax_class_id)>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
              $actual_bundle_price_unit_ex += $currencies->display_price_clear($special_price, 0);
              $actual_bundle_price_ex += $currencies->display_price_clear($special_price, 0);
            }
          } else {
            $actual_bundle_price += $currencies->display_price_clear($products_price, Tax::get_tax_rate($bundle_tax_class_id));
            $actual_bundle_price_unit += $currencies->display_price_clear($products_price, 0);
            if (/*Tax::get_tax_rate($bundle_tax_class_id)>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
              $actual_bundle_price_ex += $currencies->display_price_clear($products_price, 0);
              $actual_bundle_price_unit_ex += $currencies->display_price_clear($products_price, 0);
            }
          }
          array_unique($all_stock_indicators, SORT_REGULAR);

          if (count($stock_indicators_ids) > 0) {
            $stock_indicators_sorted = \common\classes\StockIndication::sortStockIndicators($stock_indicators_ids);
            $tmp = $stock_indicators_array[$stock_indicators_sorted[count($stock_indicators_sorted)-1]];
            if (count($tmp)==1) {
              $bundle_stock_indicator = $tmp[0];
            } else {
              $stock_indicators_sorted = \common\classes\StockIndication::sortStockFlags($tmp);
              $bundle_stock_indicator = $stock_indicators_sorted[count($tmp)-1];
            }
          } elseif (count($all_stock_indicators) > 0) {
            $stock_indicators_sorted = \common\classes\StockIndication::sortStockFlags($all_stock_indicators);
            $bundle_stock_indicator = $stock_indicators_sorted[count($all_stock_indicators)-1];
          } elseif ($stock_indicator['is_hidden']) {
            $bundle_stock_indicator = $attributes_details['stock_indicator'];
          } else {
            $bundle_stock_indicator = $stock_indicator_public;
          }

          $bundle_stock_indicator['quantity_max'] = min($quantity_max_array);

          $return_data = [
              'product_valid' => (min($all_filled_array) && (isset($attributes_details['product_valid']) ? $attributes_details['product_valid'] : true) ? '1' : '0'),
              'product_qty' => min($product_qty_array),
              'bundle_products' => $bundle_products,
              'stock_indicator' => $bundle_stock_indicator,
              'product_price' => $currencies->display_price($products_price, Tax::get_tax_rate($bundle_tax_class_id), 1),
              'special_price' => $currencies->display_price($special_price, Tax::get_tax_rate($bundle_tax_class_id), 1),
              'full_bundle_price' => $currencies->format($full_bundle_price, false),
              'actual_bundle_price' => $currencies->format($actual_bundle_price, false),
              'full_bundle_price_clear' => $full_bundle_price,
              'actual_bundle_price_clear' => $actual_bundle_price,
              'actual_bundle_price_unit' => $actual_bundle_price_unit,
          ];

          if (/*Tax::get_tax_rate($bundle_tax_class_id)>0 && */defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
            $return_data += [
              'product_price_ex' => $currencies->display_price($products_price, 0, 1, false, false),
              'special_price_ex' => $currencies->display_price($special_price, 0, 1, false, false),
              'full_bundle_price_ex' => $currencies->format($full_bundle_price_ex, false),
              'actual_bundle_price_ex' => $currencies->format($actual_bundle_price_ex, false),
              'full_bundle_price_clear_ex' => $full_bundle_price_ex,
              'actual_bundle_price_clear_ex' => $actual_bundle_price_ex,
              'actual_bundle_price_unit_ex' => $actual_bundle_price_unit_ex,
              ];
          }

          // Hide "Notify me when in stock" button for Bundles
          $return_data['stock_indicator']['notify_instock'] = false;

          return $return_data;
        }
    }

    public static function parseUprid($uprid)
    {
        if ( strpos($uprid,'{tpl}')===false ) return false;

        list($prid,) = explode('{tpl}', $uprid);

        $bundleParts = [];
        $bundleParts[] = $prid;
        if ( preg_match_all('/(\d+)\|(.*?){sub}'.(int)$prid.'/',$uprid, $matches) ){
            foreach ($matches[2] as $_idx=>$partUprid){
                $bundleParts[] = $partUprid;
            }
        }
        return $bundleParts;
    }

}
