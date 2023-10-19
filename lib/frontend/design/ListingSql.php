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
use common\classes\platform;
use common\extensions\UserGroupsRestrictions\UserGroupsRestrictions;
use yii\helpers\ArrayHelper;
use common\helpers\Affiliate;

class ListingSql
{
  use \common\helpers\SqlTrait;

  public static function query($settings = array())
  {
    global $customer_id;
    $languages_id = \Yii::$app->settings->get('languages_id');

    $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
      'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
      'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
      'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
      'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
      'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
      'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
      'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW,
      'PRODUCT_LIST_SHORT_DESRIPTION' => PRODUCT_LIST_SHORT_DESRIPTION,
      'PRODUCT_LIST_POPULARITY' => PRODUCT_LIST_POPULARITY,
        );

    asort($define_list);

    $column_list = array();
    foreach ($define_list as $key => $value) {
      if ($value > 0) $column_list[] = $key;
    }

    $select_column_list = 'p.order_quantity_minimal, p.order_quantity_step, p.stock_indication_id, pd.products_h2_tag, ';

    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      switch ($column_list[$i]) {
        case 'PRODUCT_LIST_MODEL':
          $select_column_list .= 'p.products_model, ';
          break;
        case 'PRODUCT_LIST_NAME':
          $select_column_list .= 'if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, ';
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $select_column_list .= 'm.manufacturers_name, ';
          break;
        case 'PRODUCT_LIST_SHORT_DESRIPTION':
          $select_column_list .= 'if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, if(length(pd1.products_description), pd1.products_description, pd.products_description) as products_description, ';
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $select_column_list .= 'p.products_quantity, ';
          break;
        case 'PRODUCT_LIST_IMAGE':
          $select_column_list .= 'p.products_image, ';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $select_column_list .= 'p.products_weight, ';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $select_column_list .= 'p.products_popularity, ';
          break;
      }
    }

    if (!isset($settings['no_filters'])) {
      $filters_sql_array = ListingSql::get_filters_sql_array();
    }
    
    $filename = $settings['filename'];

	$listing_sql_array = ListingSql::get_listing_sql_array($filename);

	$customer_join = '';
	$groupBy = '';
      $groupJoin = '';
      /*
      if (UserGroupsRestrictions::isAllowed()) {
          $groupJoin = ' groups_products as gp, ';
          $listing_sql_array['where'] .= " and gp.products_id = p.products_id and gp.groups_id = '{$customer_groups_id}' ";
      }
      /**/
    if(isset($settings['only_samples']) && $settings['only_samples']){
        $listing_sql_array['where'] .= ' and p.ask_sample=1 ';
    }

    $p2c_listing_join = '';
    if ( platform::activeId() ) {
      $p2c_listing_join = self::sqlCategoriesToPlatform();
    }
    $listing_sql = "
      select
        " . $select_column_list . "
        p.products_id,
        p.is_virtual, p.is_bundle,
        p.manufacturers_id,
        p.products_price,
        p.products_tax_class_id,
        IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price,
        IF(s.status, s.specials_new_products_price, p.products_price) as final_price
      from
        " . $listing_sql_array['from'] . "
        " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c {$p2c_listing_join},
        " . TABLE_PRODUCTS_DESCRIPTION . " pd, {$groupJoin} 
        " . TABLE_PRODUCTS . " p {$customer_join}
          left join
            " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id
              and pd1.language_id = '" . (int)$languages_id . "'
              and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "'
          left join " . TABLE_PRODUCTS_GLOBAL_SORT . " pgso on p.products_id = pgso.products_id
              and pgso.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "'
          left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id
          left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id
          " . $listing_sql_array['left_join'] . $filters_sql_array['left_join'] . "
      where
        p2c.products_id = p.products_id
        and pd.products_id = p.products_id
        and pd.platform_id = '".intval(\common\classes\platform::defaultId())."'
        and pd.language_id = '" . (int)$languages_id . "'
        " . $listing_sql_array['where'] . $filters_sql_array['where'] ."
      group by p.products_id {$groupBy}";

    if ($_GET['sort']) {
      $sr = $_GET['sort'];
    } elseif ($_GET['sort'] === 0) {
      $sr = false;
    } elseif ($settings['sort']) {
      $sr = $settings['sort'];
    } else {
      $sr = Info::sortingId();
    }
    
    if ($sr) {
      $sort_col = substr($sr, 0 , 1);
      $sort_order = substr($sr, 1);
      $listing_sql .= ' order by ';
          
      switch ($sort_col) {
        case 'm':
          $listing_sql .= " p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'b':
          $listing_sql .= " m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'q':
          $listing_sql .= " p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'i':
          $listing_sql .= " products_name";
          break;
        case 'w':
          $listing_sql .= " p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'p':
          $listing_sql .= " final_price " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'd':
          $listing_sql .= " p.products_date_added " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'y':
          $listing_sql .= " (p.products_popularity + p.popularity_simple) " . ($sort_order == 'd' ? 'desc' : '') . ", products_name";
          break;
        case 'n':
        default:
          $listing_sql .= " pgso.sort_order desc, p.products_date_added " . ($sort_order == 'd' ? 'desc' : '');
          break;
      }
    } else {
      if (tep_not_null($filters_sql_array['relevance_order'])) {
        $listing_sql .= ' order by (' . $filters_sql_array['relevance_order'] . ') desc, products_name';
      } else {
        $listing_sql .= " order by " . (!empty($listing_sql_array['order'])? $listing_sql_array['order'] .", " : "") . " pgso.sort_order desc, p2c.sort_order, products_name";
      }
    }

    //echo $listing_sql;
    return $listing_sql;
  }

  public static function get_listing_sql_array($filename = '') {
    global $current_category_id;
    $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
    $currency_id = \Yii::$app->settings->get('currency_id');
    
    $listing_from = '';
    $listing_where = '';
    $listing_left_join = '';
    $order = '';

    if ( platform::activeId() ) {
      $listing_left_join .= self::sqlProductsToPlatform();
      //$listing_where .= " and plp.platform_id is not null ";
      //$listing_left_join .= " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on p2c.categories_id = plc.categories_id  and plc.platform_id = '" . platform::currentId() . "' ";
      //$listing_where .= " and plc.platform_id is not null ";
    }

    if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
      $listing_left_join .= " left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)$currency_id : '0') . "' ";
      $listing_where .= " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) ";
    }

    // show the active products
    $listing_where .= \common\helpers\Product::getState(true);

    $listing_where .= \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " ";

    $listing_left_join .= " left join " . TABLE_PRODUCTS_STOCK_INDICATION . " psi on p.stock_indication_id = psi.stock_indication_id ";
    if (!SHOW_OUT_OF_STOCK) {
      $listing_where .= " and if(psi.stock_indication_id is null, p.products_quantity > 0, psi.allow_out_of_stock_checkout = '1' and psi.allow_out_of_stock_add_to_cart = '1') ";
    }

    switch ($filename) {
    case 'catalog/sales': // show specials
      if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
        $listing_left_join .= " left join " . TABLE_SPECIALS_PRICES . " sp on s.specials_id = sp.specials_id and sp.groups_id = '" . (int)$customer_groups_id . "' and sp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)$currency_id : '0'). "' ";
        $listing_where .= " and if(sp.specials_new_products_price is NULL, 1, sp.specials_new_products_price != -1 ) and s.status = 1 ";
      } else {
        $listing_where .= " and s.status = 1 ";
      }
      break;
    case 'catalog/featured-products': // show featured products
      $listing_from .= " " . TABLE_FEATURED . " f, ";
      $listing_where .= " and p.products_id = f.products_id and f.status = '1' ";
      if (Affiliate::id() > 0) {
        $listing_where .= " and (f.affiliate_id = '" . Affiliate::id() . "' or f.affiliate_id = 0) ";
      } else {
        $listing_where .= " and f.affiliate_id = 0 ";
      }
      break;
    case 'catalog/products-new':// show all products sorted by date
      break;
    case 'catalog/free-samples':
    case 'catalog/all-products':
        $order = "p2c.sort_order";
        break;
    case 'catalog/advanced-search': // show all products filtered
      break;
    default: case 'catalog': case 'catalog/index':
      if ($current_category_id > 0) {
        if (\frontend\design\Info::themeSetting('show_products_from_subcategories')) {
          $categories_array = array($current_category_id);
          \common\helpers\Categories::get_subcategories($categories_array, $current_category_id);
          if (count($categories_array) == 1 && $categories_array[0] == $current_category_id) {
            $order = "p2c.sort_order";
          }
        } else {
          $categories_array = array($current_category_id);
        }
      }
      if (isset($_GET['manufacturers_id'])) {
        // show the products of a specified manufacturer(s)
        $listing_where .= " and m.manufacturers_id in ('" . implode("','", array_map('intval', explode('_', $_GET['manufacturers_id']))) . "') " . (is_array($categories_array) && count($categories_array) > 0 ? " and p2c.categories_id in ('" . implode("','", array_map('intval', $categories_array)) . "') " : '');
      } else {
        // show the products of a specified category(ies)
        if (is_array($categories_array ?? null)) {
            $listing_where .= (count($categories_array) > 0 ? " and p2c.categories_id in ('" . implode("','", array_map('intval', $categories_array)) . "') " : '');
        }
      }
      break;
    }

    return array('from' => $listing_from, 'left_join' => $listing_left_join, 'where' => $listing_where, 'order' => $order);
  }

  public static function get_filters_sql_array($exclude = '') {
    global $languages_id;
    $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
    $currencies = \Yii::$container->get('currencies');
    $currency_id = \Yii::$app->settings->get('currency_id');
    
    $filters_left_join = '';
    $filters_where = '';
    $relevance_order = '';

    // Search keywords
    if (isset($_GET['keywords'])) {
        
        $searchBuilder = new \common\components\SearchBuilder('simple');

        $keywords = \yii\helpers\Html::encode(\Yii::$app->request->get('keywords', ''));

        $searchBuilder->setSearchInDesc(SEARCH_IN_DESCRIPTION == 'True');
        $searchBuilder->searchInProperty = true; 
        $searchBuilder->searchInAttributes = true; 
        $searchBuilder->prepareRequest($keywords);

        $filters_where = $searchBuilder->getProductsArray();
        $relevance_keywords = $searchBuilder->relevanceWords;
      
        if (isset($relevance_keywords) && (sizeof($relevance_keywords) > 0)) {
            $relevance_order .= " (match(pd.products_name) against ('" . implode(' ', $relevance_keywords) . "') * 1.2) + (match(p.products_model) against ('" . implode(' ', $relevance_keywords) . "') * 1.0) ";

            /**
             * @var $ext \common\extensions\GoogleAnalyticsTools\GoogleAnalyticsTools
             */
            if ( $ext = \common\helpers\Extensions::isAllowed('GoogleAnalyticsTools') ){
                $filters_left_join .= $ext::joinListingSql();
                $relevance_order .= $ext::relevanceListingSql($relevance_keywords);
            }
            if (isset($_GET['search_in_description']) && ($_GET['search_in_description'] == '1')) {
              $relevance_order .= " + (match(pd.products_description) against ('" . implode(' ', $relevance_keywords) . "') * 0.8) ";
            }
        }

        if (PRODUCTS_PROPERTIES == 'True' && true) {
            $filters_left_join .= " left join " . TABLE_PROPERTIES_TO_PRODUCTS . " pr2pk on pr2pk.products_id = p.products_id left join " . TABLE_PROPERTIES . " prk on prk.properties_id = pr2pk.properties_id and prk.display_search = '1' left join " . TABLE_PROPERTIES_VALUES . " pvk on prk.properties_id = pvk.properties_id and pr2pk.values_id = pvk.values_id";
        }

        if ($searchBuilder->searchInAttributes) {
            $filters_left_join .= " left join " . TABLE_PRODUCTS_ATTRIBUTES . " pak on pak.products_id = p.products_id left join " . TABLE_PRODUCTS_OPTIONS . " pok on pok.products_options_id = pak.options_id and pok.display_search = '1' left join " . TABLE_PRODUCTS_OPTIONS_VALUES . " povk on povk.products_options_values_id = pak.options_values_id";
        }
    }

    // Price interval
    if ($exclude !== 'p') {
      $pfrom = (float)preg_replace("/[^\d\.]/", '', \Yii::$app->request->get('pfrom'));
      $pto = (float)preg_replace("/[^\d\.]/", '', \Yii::$app->request->get('pto'));
      if ($pfrom > 0 || $pto > 0) {
        $ids = array();
        $ids[] = 0;

        $products_join = '';
        if ( platform::activeId() ) {
          $products_join .= self::sqlProductsToPlatformCategories();
        }

          /**
           * @var $ext \common\extensions\ProductPriceIndex\ProductPriceIndex
           * @var $extModel \common\extensions\ProductPriceIndex\models\ProductPriceIndex
           */
        if(($ext = \common\helpers\Extensions::isAllowed('ProductPriceIndex')) && !empty($extModel = $ext::getModel('ProductPriceIndex'))){
          $_tax_rates = [];
          $r = tep_db_query("select tax_class_id from " . TABLE_TAX_CLASS);
          while ($d = tep_db_fetch_array($r)) {
            $tmp = \common\helpers\Tax::get_tax_rate($d['tax_class_id']);
            if ($tmp  > 0) {
              $_tax_rates[$d['tax_class_id']] = $tmp;
            }
          }
          $mwhere = "";
          $where_price = " and (";
          if (count($_tax_rates)>0 && (DISPLAY_PRICE_WITH_TAX == 'true')) {
            $mwhere = "*(CASE p.products_tax_class_id ";
            foreach ($_tax_rates as $tax_class_id => $tax_percent) {
              $mult = 1.00 + $tax_percent/100;
              $mwhere .= " WHEN '" . $tax_class_id . "' THEN " . $mult . " ";
            }
            $mwhere .= " ELSE 1 END)";
          }

          if ($pto > 0) {
            $where_price .= " if(products_special_price_min>0 and products_special_price_min<products_price_min, products_special_price_min, products_price_min)" . $mwhere . " <='" . (float)$pto . "'";
          }
          if ($pfrom > 0) {
            $where_price .= (($pto > 0)?" and ":"") . " if(products_special_price_max>0 and products_special_price_max>products_price_max,products_special_price_max, products_price_max)" . $mwhere . " >='" . (float)$pfrom. "'";
          }

          $where_price .= ") ";

          $ext::checkUpdateStatus();
//echo htmlspecialchars( "select distinct p.products_id from " . TABLE_PRODUCTS . " p join " . $extModel::tableName() . " ppi on ppi.products_id=p.products_id and groups_id='" . (int)$customer_groups_id . "' and currencies_id='" . (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES=='True'?(int)$currency_id:0) . "' {$products_join} where " . \common\helpers\Product::getState() . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . $where_price) . " <br>";
          $products_query = tep_db_query("select distinct p.products_id from " . TABLE_PRODUCTS . " p join " . $extModel::tableName() . " ppi on ppi.products_id=p.products_id and groups_id='" . (int)$customer_groups_id . "' and currencies_id='" . (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES=='True' ? (int)$currency_id : 0) . "' {$products_join} where " . \common\helpers\Product::getState() . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . $where_price . " ");
          while ($data = tep_db_fetch_array($products_query)) {
            $ids[] = $data['products_id'];
          }

        } else {
          if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
            $query = tep_db_query("select p.products_id, p.products_tax_class_id, p.products_price from " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? $currency_id : '0') . "' where " . \common\helpers\Product::getState() . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) group by p.products_id");
          } else {
            $query = tep_db_query("select p.products_id, p.products_tax_class_id, p.products_price from " . TABLE_PRODUCTS . " p {$products_join} where " . \common\helpers\Product::getState() . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " group by p.products_id");
          }
          $container = \Yii::$container->get('products');
          while ($data = tep_db_fetch_array($query)) {
            $container->loadProducts($data);
            $special_price = \common\helpers\Product::get_products_special_price($data['products_id']);
            $price = \common\helpers\Product::get_products_price($data['products_id'], 1, $data['products_price']);
            if ($special_price) {
              $price = $special_price;
            }
            $price = $currencies->display_price_clear($price, \common\helpers\Tax::get_tax_rate($data['products_tax_class_id']));
            if ($pfrom > 0 && $pto > 0) {
              if ($price >= $pfrom && $price <= $pto) {
                $ids[] = $data['products_id'];
              }
            } elseif ($pfrom > 0) {
              if ($price >= $pfrom) {
                $ids[] = $data['products_id'];
              }
            } elseif ($pto > 0) {
              if ($price <= $pto) {
                $ids[] = $data['products_id'];
              }
            }
          }
        }
        $filters_where .= " and p.products_id in ('" . implode("','", array_map('intval', $ids)) . "') ";
      }
    }

    // Categories selected
    if ($exclude !== 'cat') {
      $cat = Yii::$app->request->get('cat');
      if (is_array($cat)) {
        $filters_left_join .= " left join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c2 on p.products_id = p2c2.products_id ";
        $filters_where .= " and p2c2.categories_id in ('" . implode("','", array_map('intval', $cat)) . "') ";
      } elseif ($cat > 0) {
        $filters_left_join .= " left join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c2 on p.products_id = p2c2.products_id ";
        $filters_where .= " and p2c2.categories_id = '" . (int)$cat . "' ";
      }
    }

    // Brands selected
    if ($exclude !== 'brand') {
        $brand = Yii::$app->request->get('brand');
        if (is_array($brand)) {
        $filters_where .= " and p.manufacturers_id in ('" . implode("','", array_map('intval', $brand)) . "') ";
      } elseif ($brand > 0) {
        $filters_where .= " and p.manufacturers_id = '" . (int)$brand . "' ";
      }
    }

    if (is_array($_GET))
    foreach ($_GET as $key => $values) {
      // Properties interval
      if (preg_match("/^pr(\d+)from$/", $key, $arr)) {
        $prop_id = (int)$arr[1];
        if ($prop_id > 0 && $exclude !== 'pr' . $prop_id && isset($_GET['pr' . $prop_id . 'to'])) {
          $from = (float)$values;
          $to = (float)$_GET['pr' . $prop_id . 'to'];
          $filters_left_join .= " left join " . TABLE_PROPERTIES_TO_PRODUCTS . " p2pi" . $prop_id . " on p.products_id = p2pi" . $prop_id . ".products_id and p2pi" . $prop_id . ".properties_id = '" . (int)$prop_id . "' left join " . TABLE_PROPERTIES_VALUES . " pvi" . $prop_id . " on p2pi" . $prop_id . ".properties_id = pvi" . $prop_id . ".properties_id and p2pi" . $prop_id . ".values_id = pvi" . $prop_id . ".values_id and pvi" . $prop_id . ".language_id = '" . (int)$languages_id . "' ";
          if ($from > 0 && $to > 0) {
            $filters_where .= " and pvi" . $prop_id . ".values_number >= " . (float)$from . " and pvi" . $prop_id . ".values_number <= " . (float)$to . " ";
          } elseif ($from > 0) {
            $filters_where .= " and pvi" . $prop_id . ".values_number >= " . (float)$from . " ";
          } elseif ($to > 0) {
            $filters_where .= " and pvi" . $prop_id . ".values_number <= " . (float)$to . " ";
          }
        }
      }
     
      if (preg_match("/^vpr(\d+)from(\d+)$/", $key, $arr)) {
          $prop_id = (int)$arr[1];
          $from = (float)$values;
          if ($from > 0) {
            $filters_where .= " and p2pi" . $prop_id . ".extra_value >= " . $from . " ";
          }
      }
      
      if (preg_match("/^vpr(\d+)to(\d+)$/", $key, $arr)) {
          $prop_id = (int)$arr[1];
          $to = (float)$values;
          if ($to > 0) {
            $filters_where .= " and p2pi" . $prop_id . ".extra_value <= " . $to . " ";
          }
      }

      // Properties selected
      if (preg_match("/^pr(\d+)$/", $key, $arr)) {
        $prop_id = (int)$arr[1];
        if ($prop_id > 0 && $exclude !== 'pr' . $prop_id) {
          if (is_array($values)) {
              $values = implode(',', $values);
          }
          if (tep_not_null($values)) {
            $values = explode(',', $values);
            $filters_left_join .= " left join " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p" . $prop_id . " on p.products_id = p2p" . $prop_id . ".products_id ";
            $filters_where .= " and p2p" . $prop_id . ".properties_id = '" . (int)$prop_id . "' ";
            if (is_array($values)) {
              if ($values[0] > 0) {
                $filters_where .= " and p2p" . $prop_id . ".values_id in ('" . implode("','", array_map('intval', $values)) . "') ";
              } elseif ($values[0] == 'Y' || $values[0] == 'N') { // properties_type == flag
                $filters_where .= " and p2p" . $prop_id . ".values_id = '0' and p2p" . $prop_id . ".values_flag in ('" . implode("','", array_map(function($v) {return (int)($v == 'Y');}, $values)) . "') ";
              }
            } else {
              if ($values > 0) {
                $filters_where .= " and p2p" . $prop_id . ".values_id = '" . (int)$values . "' ";
              } elseif ($values == 'Y' || $values == 'N') { // properties_type == flag
                $filters_where .= " and p2p" . $prop_id . ".values_id = '0' and p2p" . $prop_id . ".values_flag = '" . (int)($values == 'Y') . "' ";
              }
            }
          }
        }
      }

      // Attributes selected
      if (preg_match("/^at(\d+)$/", $key, $arr)) {
        $attr_id = (int)$arr[1];
        if ($attr_id > 0 && $exclude !== 'at' . $attr_id) {
          $filters_left_join .= " left join " . TABLE_PRODUCTS_ATTRIBUTES . " pa" . $attr_id . " on p.products_id = pa" . $attr_id . ".products_id ";
          $filters_where .= " and pa" . $attr_id . ".options_id = '" . (int)$attr_id . "' ";
          if (is_array($values)) {
            $filters_where .= " and pa" . $attr_id . ".options_values_id in ('" . implode("','", array_map('intval', $values)) . "') ";
          } else {
            $filters_where .= " and pa" . $attr_id . ".options_values_id = '" . (int)$values . "' ";
          }
        }
      }
    }

    return array('left_join' => $filters_left_join, 'where' => $filters_where, 'relevance_order' => $relevance_order);
  }
}
