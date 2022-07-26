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

class SalesGraph extends Widget {

    public function run() {
        $currencies = \Yii::$container->get('currencies');
        $params = ['currcode_left' => $currencies->currencies[DEFAULT_CURRENCY]['symbol_left'], 'currcode_right' => $currencies->currencies[DEFAULT_CURRENCY]['symbol_right']];
        $exclude_order_statuses_array = \common\helpers\Order::extractStatuses(DASHBOARD_EXCLUDE_ORDER_STATUSES);

        $orders_data_array = array('blue' => array(), 'green' => array(), 'red' => array(), 'blue2' => array(), 'green2' => array(), 'red2' => array());
        $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') + 1, 1, date('Y') - 1));
        $orders_query = tep_db_query(
            "select year(o.date_purchased) as date_year, month(o.date_purchased) as date_month, ".
            "  count(*) as total_orders, avg(ost.value*o.currency_value) as avg_order_amount, ".
            "  sum(ot.value*o.currency_value) as total_amount ".
            "from " . TABLE_ORDERS . " o ".
            " inner join " . TABLE_ORDERS . " oin ON oin.orders_id=o.orders_id AND oin.date_purchased >= '" . tep_db_input($date_from) . "' ".
            " left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id and ot.class = 'ot_total') ".
            " left join " . TABLE_ORDERS_TOTAL . " ost on (o.orders_id = ost.orders_id and ost.class = 'ot_subtotal') ".
            "where o.orders_status not in ('" . implode("','", $exclude_order_statuses_array) . "') ".
            "group by year(o.date_purchased), month(o.date_purchased) ".
            "order by year(o.date_purchased), month(o.date_purchased)"
        );
        $orders_counts = tep_db_num_rows($orders_query);
        $orders_count = 0;
        $monthes = [];
        $startM = date('m');
        //$startM = 12;
        $startY = date('Y');
        //$startY = 2019;
        for ($i=0;$i<12;$i++) {
          $monthes[] = ($startM + $i)%12+1;
          if ($i < 11) {
                $orders_data_array['blue'][] = '[' . mktime(14, 0, 0, $startM + $i - 11, 1, $startY) . '000,' . 0 . ']';
                $orders_data_array['green'][] = '[' . mktime(14, 0, 0, $startM + $i - 11, 1, $startY) . '000,' . 0 . ']';
                $orders_data_array['red'][] = '[' . mktime(14, 0, 0, $startM + $i - 11, 1, $startY) . '000,' . 0 . ']';
          }
        }
        $monthes = array_flip($monthes);

        while ($orders = tep_db_fetch_array($orders_query)) {
          $orders_count++;

/* seems so:
 * blue - orders q-ty
 * green - avg
 * red - total
 * xx2 - estimate for current month
 */
// {{
            if ($orders_count == $orders_counts) { // Last month
                if ((int) $orders['date_month'] != (int) $startM) { // No orders in current month yet
                    $orders_data_array['blue2'] = $orders_data_array['green2'] = $orders_data_array['red2'] = array();
                    //$orders_data_array['blue2'][$monthes[$orders['date_month']]+1] =
                    $orders_data_array['blue2'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $startM, 1, $startY) . '000,' . $orders['total_orders'] . ']';
                    $orders_data_array['blue'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_orders'] . ']';
                    //$orders_data_array['green2'][$monthes[$orders['date_month']]+1] =
                    $orders_data_array['green2'][$monthes[$orders['date_month']]] =  '[' . mktime(14, 0, 0, $startM, 1, $startY) . '000,' . $orders['avg_order_amount'] . ']';
                    $orders_data_array['green'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['avg_order_amount'] . ']';
                    //$orders_data_array['red2'][$monthes[$orders['date_month']]+1] =
                    $orders_data_array['red2'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $startM, 1, $startY) . '000,' . $orders['total_amount'] . ']';
                    $orders_data_array['red'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_amount'] . ']';
                    break;
                } else { // Estimate to full month interval
                    $firstMonthsDay = mktime(0, 0, 0, $startM, 1, $startY);
                    $percent =  (mktime(0, 0, 0, $startM+1, 1, $startY)- $firstMonthsDay) / (time() - $firstMonthsDay);
                    $orders['total_orders'] = round($orders['total_orders'] * $percent);
                    $orders['total_amount'] *= (float)$percent;
                }
            }
// }}

            if ($orders_counts == 1 ) {
                $orders_data_array['blue2'][$monthes[$orders['date_month']]-1] = '[' . mktime(14, 0, 0, $orders['date_month']-1, 1, $orders['date_year']) . '000,' . 0 . ']';
                $orders_data_array['green2'][$monthes[$orders['date_month']]-1] = '[' . mktime(14, 0, 0, $orders['date_month']-1, 1, $orders['date_year']) . '000,' . 0 . ']';
                $orders_data_array['red2'][$monthes[$orders['date_month']]-1] = '[' . mktime(14, 0, 0, $orders['date_month']-1, 1, $orders['date_year']) . '000,' . 0 . ']';
            }
            if ($orders_count == $orders_counts - 1 || $orders_count == $orders_counts) {
                $orders_data_array['blue2'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_orders'] . ']';
                $orders_data_array['green2'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['avg_order_amount'] . ']';
                $orders_data_array['red2'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_amount'] . ']';
            }
            if ($orders_count != $orders_counts) {
                $orders_data_array['blue'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_orders'] . ']';
                $orders_data_array['green'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['avg_order_amount'] . ']';
                $orders_data_array['red'][$monthes[$orders['date_month']]] = '[' . mktime(14, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total_amount'] . ']';
            }
        }
        \Yii::$app->view->registerJs(
                "var data_blue  = [ " . implode(" , ", $orders_data_array['blue']) . " ];" .
                "var data_blue2  = [ " . implode(" , ", $orders_data_array['blue2']) . " ];" .
                "var data_green = [ " . implode(" , ", $orders_data_array['green']) . " ];" .
                "var data_green2 = [ " . implode(" , ", $orders_data_array['green2']) . " ];" .
                "var data_red   = [ " . implode(" , ", $orders_data_array['red']) . " ];" .
                "var data_red2   = [ " . implode(" , ", $orders_data_array['red2']) . " ];", \yii\web\View::POS_BEGIN
        );


        return $this->render('SalesGraph.tpl', $params);
    }

}
