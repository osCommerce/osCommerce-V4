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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Promotions extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('Promotions')) {
            return '';
        }
        $params = Yii::$app->request->get();

        if (!$params['products_id']) {
            return '';
        }

        $promotions = \common\models\promotions\Promotions::onlyPromotion('multidiscount', \common\classes\platform::currentId())->all();

        $hasPromo = false;
        $productPromos = [];
        if ($promotions){
            $service = new \common\models\promotions\PromotionService();
            foreach($promotions as $pIdx=> &$promo){
                if (is_array($promo->sets) && count($promo->sets) && \common\models\promotions\PromotionService::isPersonalizedPromoToCustomer($promo->promo_id)){
                    if ($promo->promo_date_start) {
                        $promo->promo_date_start = date(DATE_FORMAT_DATEPICKER_PHP, strtotime($promo->promo_date_start));
                    }
                    if ($promo->promo_date_expired) {
                        $promo->promo_date_expired = date(DATE_FORMAT_DATEPICKER_PHP, strtotime($promo->promo_date_expired));
                    }
                    $class = $service($promo->promo_class);
                    $class->loadSettings([
                        'platform_id' => (int)PLATFORM_ID,
                        'promo_id' => $promo->promo_id,
                    ]);

                    $productDiscount = [];
                    $hasProduct = false;
                    $mainHash = 0;
                    $hash = [];
                    foreach ($class->settings['assigned_items'] as $product) {
                        if ($product['product']->id == $params['products_id']) {
                            $hasPromo = true;
                            $hasProduct = true;
                            $mainHash = $class->settings['sets_conditions']['product'][$product['product']->id][0]->promotions_sets_conditions_hash;
                        }
                        $hash[$product['product']->id] = $class->settings['sets_conditions']['product'][$product['product']->id][0]
                            ->promotions_sets_conditions_hash;
                        $productDiscount[$product['product']->id] = $class->settings['sets_conditions']['product'][$product['product']->id][0]
                            ->promotions_sets_conditions_discount;
                    }

                    foreach ($hash as $id => $h) {
                        if ($h != $mainHash || $h == 0) {
                            unset($productDiscount[$id]);
                        }
                    }

                    if (count($productDiscount) < 2) {
                        continue;
                    }

                    if ($hasProduct) {

                        $promoProducts = self::getProducts($productDiscount);

                        $productPromos[] = [
                            'label' => $promo->promo_label,
                            'image' => \common\classes\Images::getWSCatalogImagesPath() . 'promo_icons/' . $promo->promo_icon,
                            'date_start' => $promo->promo_date_start,
                            'date_expired' => $promo->promo_date_expired,
                            'promo_id' => $promo->promo_id,
                            'settings' => $class->settings,
                            'products' => $promoProducts['products'],
                            'full_price' => $promoProducts['full_price'],
                            'full_price_discount' => $promoProducts['full_price_discount'],
                            'stock_indicator' => $promoProducts['stock_indicator'],
                            'save' => $promoProducts['save'],
                            'save_percents' => $promoProducts['save_percents'],
                        ];
                    }

                } else {
                    unset($promotions[$pIdx]);
                }
            }

        }

        if (!$hasPromo) {
            return '';
        }

        return IncludeTpl::widget(['file' => 'boxes/product/promotions.tpl', 'params' => [
            'promotions' => $productPromos,
            'params'=> $this->params,
            'id'=> $this->id,
            'products_id'=> $params['products_id'],
            'slide'=> $params['slide'] ? $params['slide'] : 0
        ]]);
    }

    public static function getProducts($productDiscount)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $currencies = \Yii::$container->get('currencies');
        $params = Yii::$app->request->get();

        $collection_full_price = 0;
        $collection_full_price_discount = 0;
        $collection_products = [];

        $ids = array_keys($productDiscount);

/*
        $listing_sql_array = \frontend\design\ListingSql::get_listing_sql_array('catalog/all-products');
        $productsQuery = tep_db_query("
            select distinct p.products_id, p.order_quantity_minimal, p.order_quantity_step, p.stock_indication_id, p.is_virtual, p.products_weight, p.products_image, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, p.products_tax_class_id, p.products_price, p.products_quantity, p.products_model 
            from " . $listing_sql_array['from'] . " " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int)$languages_id ."' and pd1.platform_id = '".(int)Yii::$app->get('platform')->config()->getPlatformToDescription()."' 
            where p.products_id in(" . $ids . ") and pd.language_id = '" . (int)$languages_id . "' and pd.products_id = p.products_id and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' ");

         $products = \frontend\design\Info::getProducts($productsQuery);

 */

        $q = new \common\components\ProductsQuery([
          'customAndWhere' =>  ['p.products_id' => $ids],
          'currentCategory' => false,
          'orderBy' =>  ['p.products_id' => SORT_ASC],
        ]);
        
        $all_filled_array = array();
        $stock_indicators_ids = array();
        $stock_indicators_array = array();
        $stock_indicator_public = array();
        $collection_base_price = $collection_full_price =0;

        $products = \frontend\design\Info::getListProductsDetails($q->buildQuery()->allIds());

        $collection_products = [];
        if ($collections = \common\helpers\Acl::checkExtensionAllowedClass('ProductsCollections', 'helpers\Collections')) {
            $collection_products = $collections::obtainCollectionProducts($params, $products, $all_filled_array, $stock_indicators_ids, $stock_indicators_array, $collection_base_price, $collection_full_price, $stock_indicator_public);
        }
        
        $collection_base_price = $collection_full_price =0;
        $products = Yii::$container->get('products');
        
        foreach($collection_products as &$collection_sets){
            
            $products_id = $collection_sets['products_id'];
            $special_price  = $collection_sets['special_price'];
            $product_price  = $collection_sets['product_price'];
            $product = $products->getProduct($products_id);
            if ($productDiscount[$products_id]) {
                if ($special_price && false) {
                    $price_old = $special_price;
                    $price_special = $special_price - $special_price * ($productDiscount[$products_id] / 100);
                } else {
                    $price_old = $product_price;
                    $price_special = $product_price - $product_price * ($productDiscount[$products_id] / 100);
                }

                $save = $price_old - $price_special;
                $save_percents = 0;
                if ($price_old) {
                    $save_percents = round((($price_old - $price_special) / $price_old) * 100);
                }

                $collection_sets['save'] = $currencies->display_price($save, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));;
                $collection_sets['save_percents'] = $save_percents;

                $collection_sets['price'] = '';
                $collection_sets['price_old'] = $currencies->display_price($price_old, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));
                $collection_sets['price_special'] = $currencies->display_price($price_special, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));

                $collection_full_price += $currencies->display_price_clear($price_old, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']), $collection_sets['collections_qty']);
                $collection_full_price_discount += $currencies->display_price_clear($price_special, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']), $collection_sets['collections_qty']);

            } else {
                if ($special_price !== false) {
                    $price = $special_price;
                } else {
                    $price = $product_price;
                }

                $collection_sets['price'] = $currencies->display_price($price, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));
                $collection_sets['price_old'] = '';
                $collection_sets['price_special'] = '';
                $collection_full_price += $currencies->display_price_clear($price, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']), $collection_sets['collections_qty']);
            }
        }
        
        if (count($stock_indicators_ids) > 0) {
            $stock_indicators_sorted = \common\classes\StockIndication::sortStockIndicators($stock_indicators_ids);
            $collection_stock_indicator = $stock_indicators_array[$stock_indicators_sorted[count($stock_indicators_sorted)-1]];
        } else {
            $collection_stock_indicator = $stock_indicator_public;
        }
        
        $save_percents = 0;
        if ($collection_full_price_discount) {
            $save_percents = round((($collection_full_price - $collection_full_price_discount) / $collection_full_price) * 100);
        }
        
        $return_data = [
            'products' => $collection_products,
            'stock_indicator' => $collection_stock_indicator,
            'full_price' => $currencies->format($collection_full_price, false),
            'full_price_discount' => $currencies->format($collection_full_price_discount, false),
            'save' => $currencies->format($collection_full_price - $collection_full_price_discount, false),
            'save_percents' => $save_percents,
        ];
        return $return_data;
        
        /*
        $stock_indicators_ids = [];
        foreach ($products as $collection_sets) {
            $products_id = $collection_sets['products_id'];
            if (is_array($params['collections_attr'][$products_id])) {
                $attributes = $params['collections_attr'][$products_id];
            } else {
                $attributes = array();
            }

            $product = $collection_sets;

            $product_price = \common\helpers\Product::get_products_price($product['products_id'], $params['collections_qty'][$products_id], $product['products_price']);
            $special_price = \common\helpers\Product::get_products_special_price($product['products_id'], $params['collections_qty'][$products_id]);
            $product_price_old = $product_price;

            $comb_arr = $attributes;
            foreach ($comb_arr as $opt_id => $val_id) {
                if ( !($val_id > 0) ) {
                    $comb_arr[$opt_id] = '0000000';
                }
            }
            reset($comb_arr);
            $mask = str_replace('0000000', '%', \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($products_id, $comb_arr)));
            $check_inventory = tep_db_fetch_array(tep_db_query("select inventory_id, min(if(price_prefix = '-', -inventory_price, inventory_price)) as inventory_price, min(inventory_full_price) as inventory_full_price from " . TABLE_INVENTORY . " i where products_id like '" . tep_db_input($mask) . "' and non_existent = '0' " . \common\helpers\Inventory::get_sql_inventory_restrictions(array('i', 'ip'))  . " limit 1"));
            if ($check_inventory['inventory_id']) {
                $check_inventory['inventory_price'] = \common\helpers\Inventory::get_inventory_price_by_uprid($mask, $params['collections_qty'][$products_id], $check_inventory['inventory_price']);
                $check_inventory['inventory_full_price'] = \common\helpers\Inventory::get_inventory_full_price_by_uprid($mask, $params['collections_qty'][$products_id], $check_inventory['inventory_full_price']);
                if ($product['products_price_full'] && $check_inventory['inventory_full_price'] != -1) {
                    $product_price = $check_inventory['inventory_full_price'];
                    if ($special_price !== false) {
                        // if special - add difference
                        $special_price += $product_price - $product_price_old;
                    }
                } elseif ($check_inventory['inventory_price'] != -1) {
                    $product_price += $check_inventory['inventory_price'];
                    if ($special_price !== false) {
                        $special_price += $check_inventory['inventory_price'];
                    }
                }
            }




            $actual_product_price = $product_price;

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
            if (isset($attributes) && is_array($attributes))
                foreach($attributes as $value) {
                    $all_filled = $all_filled && (bool)$value;
                }

            $attributes_array = array();
            tep_db_data_seek($products_options_name_query, 0);
            while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
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
                    $mask = str_replace('0000000', '%', \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($products_id, $comb_arr)));
                    $check_inventory = tep_db_fetch_array(tep_db_query(
                        "select inventory_id, max(products_quantity) as products_quantity, stock_indication_id, stock_delivery_terms_id, ".
                        " min(if(price_prefix = '-', -inventory_price, inventory_price)) as inventory_price, min(inventory_full_price) as inventory_full_price ".
                        "from " . TABLE_INVENTORY . " i ".
                        "where products_id like '" . tep_db_input($mask) . "' and non_existent = '0' " . \common\helpers\Inventory::get_sql_inventory_restrictions(array('i', 'ip'))  . " ".
                        "order by products_quantity desc ".
                        "limit 1"
                    ));
                    if (!$check_inventory['inventory_id']) continue;
                    $check_inventory['inventory_price'] = \common\helpers\Inventory::get_inventory_price_by_uprid($mask, $params['collections_qty'][$products_id], $check_inventory['inventory_price']);
                    $check_inventory['inventory_full_price'] = \common\helpers\Inventory::get_inventory_full_price_by_uprid($mask, $params['collections_qty'][$products_id], $check_inventory['inventory_full_price']);
                    if ($product['products_price_full'] && $check_inventory['inventory_full_price'] == -1) {
                        continue; // Disabled for specific group
                    } elseif ($check_inventory['inventory_price'] == -1) {
                        continue; // Disabled for specific group
                    }

                    $products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name'], 'price_diff' => 0);
                    if ($product['products_price_full']) {
                        $price_diff = $check_inventory['inventory_full_price'] - $actual_product_price;
                    } else {
                        $price_diff = $product_price_old + $check_inventory['inventory_price'] - $actual_product_price;
                    }
                    if ($price_diff != '0') {
                        $products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . ($price_diff < 0 ? '-' : '+') . $currencies->display_price(abs($price_diff), \common\helpers\Tax::get_tax_rate($products_options_name['products_tax_class_id'])) .') ';
                    }
                    $products_options_array[sizeof($products_options_array)-1]['price_diff'] = $price_diff;

                    $stock_indicator = \common\classes\StockIndication::product_info(array(
                        'products_id' => $check_inventory['products_id'],
                        'products_quantity' => ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : '0'),
                        'stock_indication_id' => (isset($check_inventory['stock_indication_id'])?$check_inventory['stock_indication_id']:null),
                        'stock_delivery_terms_id' => (isset($check_inventory['stock_delivery_terms_id'])?$check_inventory['stock_delivery_terms_id']:null),
                    ));

                    if ( !($check_inventory['products_quantity'] > 0) ) {
                        $products_options_array[sizeof($products_options_array)-1]['params'] = ' class="outstock" data-max-qty="' . (int)$stock_indicator['max_qty'] . '"';
                        $products_options_array[sizeof($products_options_array)-1]['text'] .= ' - ' . strip_tags($stock_indicator['stock_indicator_text_short']);
                    } else {
                        $products_options_array[sizeof($products_options_array)-1]['params'] = ' class="outstock" data-max-qty="'.(int)$stock_indicator['max_qty'].'"';
                    }
                }

                if ($attributes[$products_options_name['products_options_id']]) {
                    $selected_attribute = $attributes[$products_options_name['products_options_id']];
                } else {
                    $selected_attribute = false;
                }

                $attributes_array[] = array(
                    'title' => htmlspecialchars($products_options_name['products_options_name']),
                    'name' => 'collections_attr[' . $products_id . '][' . $products_options_name['products_options_id'] . ']',
                    'options' => $products_options_array,
                    'selected' => $selected_attribute,
                );

            }




            $product_query = tep_db_query("select products_id, products_price, products_tax_class_id, stock_indication_id, stock_delivery_terms_id, products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
            $_backup_products_quantity = 0;
            $_backup_stock_indication_id = $_backup_stock_delivery_terms_id = 0;
            if ($product = tep_db_fetch_array($product_query)) {
                $_backup_products_quantity = $product['products_quantity'];
                $_backup_stock_indication_id = $product['stock_indication_id'];
                $_backup_stock_delivery_terms_id = $product['stock_delivery_terms_id'];
            }
            $current_uprid = \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($products_id, $attributes));
            $collection_sets['current_uprid'] = $current_uprid;

            $check_inventory = tep_db_fetch_array(tep_db_query(
                "select inventory_id, products_quantity, stock_indication_id, stock_delivery_terms_id ".
                "from " . TABLE_INVENTORY . " ".
                "where products_id like '" . tep_db_input($current_uprid) . "' ".
                "limit 1"
            ));

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
            if ($stock_indicator_public['request_for_quote']) {
                $special_price = false;
            }
            $collection_sets['stock_indicator'] = $stock_indicator_public;

            if ($stock_indicator['id'] > 0) {
                $stock_indicators_ids[] = $stock_indicator['id'];
                $stock_indicators_array[$stock_indicator['id']] = $stock_indicator_public;
            }

            $collection_sets['all_filled'] = $all_filled;
            $collection_sets['product_qty'] = ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : ($product['products_quantity']?$product['products_quantity']:'0'));

            $all_filled_array[] = $collection_sets['all_filled'];

            $collection_sets['collections_qty'] = ($params['collections_qty'][$products_id] > 0 ? $params['collections_qty'][$products_id] : 1);

            if ($special_price !== false) {
                $collection_sets['price_old'] = $currencies->display_price($product_price, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));
                $collection_sets['price_special'] = $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));
            } else {
                $collection_sets['price'] = $currencies->display_price($product_price, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));
            }


            if ($productDiscount[$products_id]) {
                if ($special_price) {
                    $price_old = $special_price;
                    $price_special = $special_price - $special_price * ($productDiscount[$products_id] / 100);
                } else {
                    $price_old = $product_price;
                    $price_special = $product_price - $product_price * ($productDiscount[$products_id] / 100);
                }

                $save = $price_old - $price_special;
                $save_percents = 0;
                if ($price_old) {
                    $save_percents = round((($price_old - $price_special) / $price_old) * 100);
                }

                $collection_sets['save'] = $currencies->display_price($save, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));;
                $collection_sets['save_percents'] = $save_percents;

                $collection_sets['price'] = '';
                $collection_sets['price_old'] = $currencies->display_price($price_old, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));
                $collection_sets['price_special'] = $currencies->display_price($price_special, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));

                $collection_full_price += $currencies->display_price_clear($price_old, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']), $collection_sets['collections_qty']);
                $collection_full_price_discount += $currencies->display_price_clear($price_special, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']), $collection_sets['collections_qty']);

            } else {
                if ($special_price !== false) {
                    $price = $special_price;
                } else {
                    $price = $product_price;
                }

                $collection_sets['price'] = $currencies->display_price($price, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']));
                $collection_sets['price_old'] = '';
                $collection_sets['price_special'] = '';
                $collection_full_price += $currencies->display_price_clear($price, \common\helpers\Tax::get_tax_rate($collection_sets['products_tax_class_id']), $collection_sets['collections_qty']);
            }

            $collection_sets['attributes_array'] = $attributes_array;


            $collection_products[] = $collection_sets;
        }

        if (count($stock_indicators_ids) > 0) {
            $stock_indicators_sorted = \common\classes\StockIndication::sortStockIndicators($stock_indicators_ids);
            $collection_stock_indicator = $stock_indicators_array[$stock_indicators_sorted[count($stock_indicators_sorted)-1]];
        } else {
            $collection_stock_indicator = $stock_indicator_public;
        }

        $save_percents = 0;
        if ($collection_full_price_discount) {
            $save_percents = round((($collection_full_price - $collection_full_price_discount) / $collection_full_price) * 100);
        }

        $return_data = [
            'products' => $collection_products,
            'stock_indicator' => $collection_stock_indicator,
            'full_price' => $currencies->format($collection_full_price, false),
            'full_price_discount' => $currencies->format($collection_full_price_discount, false),
            'save' => $currencies->format($collection_full_price - $collection_full_price_discount, false),
            'save_percents' => $save_percents,
        ];
        return $return_data;*/
    }
}