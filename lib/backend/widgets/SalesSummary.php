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

namespace backend\widgets;

use Yii;
use yii\base\Widget;

class SalesSummary extends Widget {
    
    public $stats = array();
    
    public function run() {
        
        $currencies = Yii::$container->get('currencies');
        $exclude_order_statuses_array = \common\helpers\Order::extractStatuses(DASHBOARD_EXCLUDE_ORDER_STATUSES);

        $q = \common\models\Products::find()->select('is_listing_product, is_bundle, products_status')
            ->addSelect(['isChild' => (new \yii\db\Expression('parent_products_id>0')), 'total' => (new \yii\db\Expression('count(*)') )])
            ->groupBy('is_listing_product, is_bundle, products_status, isChild')
        ;
        $d = $q->asArray()->all();
        $ap = array_filter($d, function ($e) { return $e['products_status'];});
        $ip = array_filter($d, function ($e) { return !$e['products_status'];});
        $pData = [];
        foreach (['bundle' => 'is_bundle', 'listing' => 'is_listing_product', 'master' => '!is_listing_product', 'child' => 'isChild'] as $key => $value) {
          if (substr($value, 0, 1) == '!') {
            $v = 0;
            $value = substr($value, 1);
          } else {
            $v = 1;
          }
          $pData[$key]['active'] = array_sum(\yii\helpers\ArrayHelper::getColumn(array_filter($ap, function ($e) use($v, $value) { return $e[$value]==$v;}), 'total'));
          $pData[$key]['inactive'] = array_sum(\yii\helpers\ArrayHelper::getColumn(array_filter($ip, function ($e) use($v, $value) { return $e[$value]==$v;}), 'total'));
        }
        $this->stats['pData'] = $pData;
        
        $manufacturers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_MANUFACTURERS . " where 1"));
        $this->stats['manufacturers'] = number_format($manufacturers['count']);
        $reviews_confirmed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_REVIEWS . " where status = '1'"));
        $this->stats['reviews_confirmed'] = number_format($reviews_confirmed['count']);
        $reviews_to_confirm = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_REVIEWS . " where status = '0'"));
        $this->stats['reviews_to_confirm'] = number_format($reviews_to_confirm['count']);

        // Today stats
        $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
        $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')));
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1' and ci.customers_info_date_account_created >= '" . tep_db_input($date_from) . "' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'"));
        $this->stats['today']['customers'] = number_format($customers['count']);
        $order_stats_query =
            "SELECT ".
            "  COUNT(o.orders_id) AS orders, " .
            "  SUM(IF(o.orders_status='" . (int) DEFAULT_ORDERS_STATUS_ID . "',1,0)) AS orders_new, ".
            "  SUM(ott.value) as total_sum, AVG(ots.value) as total_avg ".
            "FROM " . TABLE_ORDERS . " o ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ott ON (o.orders_id = ott.orders_id) AND ott.class = 'ot_total' ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ots ON (o.orders_id = ots.orders_id) and ots.class = 'ot_subtotal' ".
            "WHERE o.date_purchased >= '" . tep_db_input($date_from) . "' AND o.date_purchased <= '" . tep_db_input($date_to) . "' ".
            "  AND o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "') ";
        $range_stat = tep_db_fetch_array(tep_db_query($order_stats_query));
        $this->stats['today']['orders'] = number_format($range_stat['orders']);
        $this->stats['today']['orders_new'] = number_format($range_stat['orders_new']);
        $this->stats['today']['orders_avg_amount'] = $currencies->format($range_stat['total_avg']);
        $this->stats['today']['orders_amount'] = $currencies->format($range_stat['total_sum']);
        /*
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['today']['orders'] = number_format($orders['count']);
        $orders_new = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "' and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')")); // <<<< Processing for now
        $this->stats['today']['orders_new'] = number_format($orders_new['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_subtotal' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['today']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_total' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['today']['orders_amount'] = $currencies->format($orders_amount['total_sum']);
        */

        // This week stats
        $date_from = date('Y-m-d H:i:s', strtotime('monday this week'));
        $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')));
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1' and ci.customers_info_date_account_created >= '" . tep_db_input($date_from) . "' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'"));
        $this->stats['week']['customers'] = number_format($customers['count']);
        $order_stats_query =
            "SELECT ".
            "  COUNT(o.orders_id) AS orders, " .
            "  SUM(IF(o.orders_status='" . (int) DEFAULT_ORDERS_STATUS_ID . "',1,0)) AS orders_new, ".
            "  SUM(ott.value) as total_sum, AVG(ots.value) as total_avg ".
            "FROM " . TABLE_ORDERS . " o ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ott ON (o.orders_id = ott.orders_id) AND ott.class = 'ot_total' ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ots ON (o.orders_id = ots.orders_id) and ots.class = 'ot_subtotal' ".
            "WHERE o.date_purchased >= '" . tep_db_input($date_from) . "' AND o.date_purchased <= '" . tep_db_input($date_to) . "' ".
            "  AND o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "') ";
        $range_stat = tep_db_fetch_array(tep_db_query($order_stats_query));
        $this->stats['week']['orders'] = number_format($range_stat['orders']);
        $this->stats['week']['orders_not_processed'] = number_format($range_stat['orders_new']);
        $this->stats['week']['orders_avg_amount'] = $currencies->format($range_stat['total_avg']);
        $this->stats['week']['orders_amount'] = $currencies->format($range_stat['total_sum']);
        /*
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['week']['orders'] = number_format($orders['count']);
        $orders_not_processed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "' and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')")); // <<<< Processing for now
        $this->stats['week']['orders_not_processed'] = number_format($orders_not_processed['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_subtotal' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['week']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_total' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['week']['orders_amount'] = $currencies->format($orders_amount['total_sum']);
        */

        // This month stats
        $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')));
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1' and ci.customers_info_date_account_created >= '" . tep_db_input($date_from) . "' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'"));
        $this->stats['month']['customers'] = number_format($customers['count']);
        $order_stats_query =
            "SELECT ".
            "  COUNT(o.orders_id) AS orders, " .
            "  SUM(IF(o.orders_status='" . (int) DEFAULT_ORDERS_STATUS_ID . "',1,0)) AS orders_new, ".
            "  SUM(ott.value) as total_sum, AVG(ots.value) as total_avg ".
            "FROM " . TABLE_ORDERS . " o ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ott ON (o.orders_id = ott.orders_id) AND ott.class = 'ot_total' ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ots ON (o.orders_id = ots.orders_id) and ots.class = 'ot_subtotal' ".
            "WHERE o.date_purchased >= '" . tep_db_input($date_from) . "' AND o.date_purchased <= '" . tep_db_input($date_to) . "' ".
            "  AND o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "') ";
        $range_stat = tep_db_fetch_array(tep_db_query($order_stats_query));
        $this->stats['month']['orders'] = number_format($range_stat['orders']);
        $this->stats['month']['orders_not_processed'] = number_format($range_stat['orders_new']);
        $this->stats['month']['orders_avg_amount'] = $currencies->format($range_stat['total_avg']);
        $this->stats['month']['orders_amount'] = $currencies->format($range_stat['total_sum']);
        /*
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['month']['orders'] = number_format($orders['count']);
        $orders_not_processed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "' and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')")); // <<<< Processing for now
        $this->stats['month']['orders_not_processed'] = number_format($orders_not_processed['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_subtotal' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['month']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_total' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['month']['orders_amount'] = $currencies->format($orders_amount['total_sum']);
        */

        // This year stats
        $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y')));
        $date_to = date('Y-m-d H:i:s', mktime(23, 59, 59, date('m'), date('d'), date('Y')));
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1' and ci.customers_info_date_account_created >= '" . tep_db_input($date_from) . "' and ci.customers_info_date_account_created <= '" . tep_db_input($date_to) . "'"));
        $this->stats['year']['customers'] = number_format($customers['count']);
        $order_stats_query =
            "SELECT ".
            "  COUNT(o.orders_id) AS orders, " .
            "  SUM(IF(o.orders_status='" . (int) DEFAULT_ORDERS_STATUS_ID . "',1,0)) AS orders_new, ".
            "  SUM(ott.value) as total_sum, AVG(ots.value) as total_avg ".
            "FROM " . TABLE_ORDERS . " o ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ott ON (o.orders_id = ott.orders_id) AND ott.class = 'ot_total' ".
            "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ots ON (o.orders_id = ots.orders_id) and ots.class = 'ot_subtotal' ".
            "WHERE o.date_purchased >= '" . tep_db_input($date_from) . "' AND o.date_purchased <= '" . tep_db_input($date_to) . "' ".
            "  AND o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "') ";
        $range_stat = tep_db_fetch_array(tep_db_query($order_stats_query));
        $this->stats['year']['orders'] = number_format($range_stat['orders']);
        $this->stats['year']['orders_not_processed'] = number_format($range_stat['orders_new']);
        $this->stats['year']['orders_avg_amount'] = $currencies->format($range_stat['total_avg']);
        $this->stats['year']['orders_amount'] = $currencies->format($range_stat['total_sum']);
        /*
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['year']['orders'] = number_format($orders['count']);
        $orders_not_processed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where date_purchased >= '" . tep_db_input($date_from) . "' and date_purchased <= '" . tep_db_input($date_to) . "' and orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "' and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')")); // <<<< Processing for now
        $this->stats['year']['orders_not_processed'] = number_format($orders_not_processed['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_subtotal' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['year']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where o.date_purchased >= '" . tep_db_input($date_from) . "' and o.date_purchased <= '" . tep_db_input($date_to) . "' and ot.class = 'ot_total' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['year']['orders_amount'] = $currencies->format($orders_amount['total_sum']);
        */

        // All period stats
        $customers = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_INFO . " ci on c.customers_id = ci.customers_info_id where customers_status = '1'"));
        $this->stats['all']['customers'] = number_format($customers['count']);
        $lazyLoadOrderAll = false;
        $checkOrdersCount = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM ".TABLE_ORDERS));
        if ( $checkOrdersCount['c']>100000 ) {
            $lazyLoadOrderAll = true;
            $this->stats['all']['orders'] = '?';
            $this->stats['all']['orders_not_processed'] = '?';
            $this->stats['all']['orders_avg_amount'] = '?';
            $this->stats['all']['orders_amount'] = '?';
        }else {
            $order_stats_query =
                "SELECT " .
                "  COUNT(o.orders_id) AS orders, " .
                "  SUM(IF(o.orders_status=1,1,0)) AS orders_new, " .
                "  SUM(ott.value) as total_sum, AVG(ots.value) as total_avg " .
                "FROM " . TABLE_ORDERS . " o " .
                "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ott ON (o.orders_id = ott.orders_id) AND ott.class = 'ot_total' " .
                "  LEFT JOIN " . TABLE_ORDERS_TOTAL . " ots ON (o.orders_id = ots.orders_id) and ots.class = 'ot_subtotal' " .
                "WHERE 1=1 " .
                "  AND o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "') ";
            $range_stat = tep_db_fetch_array(tep_db_query($order_stats_query));
            $this->stats['all']['orders'] = number_format($range_stat['orders']);
            $this->stats['all']['orders_not_processed'] = number_format($range_stat['orders_new']);
            $this->stats['all']['orders_avg_amount'] = $currencies->format($range_stat['total_avg']);
            $this->stats['all']['orders_amount'] = $currencies->format($range_stat['total_sum']);
        }
        /*
        $orders = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where 1 and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['all']['orders'] = number_format($orders['count']);
        $orders_not_processed = tep_db_fetch_array(tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where orders_status = '" . (int) DEFAULT_ORDERS_STATUS_ID . "' and orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')")); // <<<< Processing for now
        $this->stats['all']['orders_not_processed'] = number_format($orders_not_processed['count']);
        $orders_avg_amount = tep_db_fetch_array(tep_db_query("select avg(ot.value) as total_avg from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ot.class = 'ot_subtotal' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['all']['orders_avg_amount'] = $currencies->format($orders_avg_amount['total_avg']);
        $orders_amount = tep_db_fetch_array(tep_db_query("select sum(ot.value) as total_sum from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ot.class = 'ot_total' and o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "')"));
        $this->stats['all']['orders_amount'] = $currencies->format($orders_amount['total_sum']);
        */
        
        $currency = \Yii::$app->settings->get('currency');
        switch ($currency) {
            case 'USD':
                $prefixClass = 'global-currency-usd';
                break;
            case 'GBP':
                $prefixClass = 'global-currency-gbp';
                break;
            case 'EUR':
                $prefixClass = 'global-currency-eur';
                break;
            default:
                $prefixClass = '';
                break;
        }
                
        return $this->render('SalesSummary.tpl', [
            'stats' => $this->stats,
            'prefix' => $prefixClass,
            'lazyLoadOrderAll' => $lazyLoadOrderAll,
        ]);
    }

}