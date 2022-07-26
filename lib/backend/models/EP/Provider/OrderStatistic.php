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

namespace backend\models\EP\Provider;

use backend\models\EP;

class OrderStatistic extends ProviderAbstract implements ExportInterface
{
    protected $fields = array();
    protected $export_query;

    function init()
    {
        parent::init();
        $this->initFields();
    }
    
    protected function initFields()
    {
        $this->fields[] = array( 'name' => 'stat_customers_count', 'value' => 'Customers Count', );
        $this->fields[] = array( 'name' => 'stat_orders_count', 'value' => 'Order Count', );
        $this->fields[] = array( 'name' => 'completed_orders_total', 'value' => 'Orders Amount', );
    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);
        
        $main_source = $this->main_source;

        $filter_sql = '';

        if ( is_array($filter) ) {
            $order_filter = (isset($filter['order']) && is_array($filter['order']))?$filter['order']:[];

            if (isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='exact')
            {
                if (!empty($order_filter['date_from'])) {
                    $filter_sql .= " AND o.date_purchased >= '" . tep_db_input(substr($order_filter['date_from'], 0, 10)) . " 00:00:00' ";
                }
                if (!empty($order_filter['date_to'])) {
                    $filter_sql .= " AND o.date_purchased <= '" . tep_db_input(substr($order_filter['date_to'], 0, 10)) . " 23:59:59' ";
                }
            }
            elseif(isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='year/month')
            {
                $year = $order_filter['year'];
                $filter_sql .= " AND YEAR(o.date_purchased)='" . tep_db_input($year) . "' ";
                $month = $order_filter['month'];
                if ( !empty($month) ) {
                    $filter_sql .= " AND DATE_FORMAT(o.date_purchased,'%Y%m')='".tep_db_input($year.sprintf('%02s',(int)$month))."' ";
                }
            }
            elseif(isset($order_filter['date_type_range']) && $order_filter['date_type_range']=='presel')
            {
                switch ($order_filter['interval']) {
                    case 'week':
                        $filter_sql .= " AND o.date_purchased >= '" . date('Y-m-d', strtotime('monday this week')) . "' ";
                        break;
                    case 'month':
                        $filter_sql .= " AND o.date_purchased >= '" . date('Y-m-d', strtotime('first day of this month')) . "' ";
                        break;
                    case 'year':
                        $filter_sql .= " AND o.date_purchased >= '" . date("Y") . "-01-01" . "' ";
                        break;
                    case '1':
                        $filter_sql .= " AND o.date_purchased >= '" . date('Y-m-d') . "' ";
                        break;
                    case '3':
                    case '7':
                    case '14':
                    case '30':
                        $filter_sql .= " AND o.date_purchased >= '".date('Y-m-d',strtotime('-'.$filters['interval'].' days'))."' ";
                        break;
                }
            }
        }
        
        
        $filter_total_completed_sql = '';
        $statuses = \common\helpers\Order::extractStatuses('group_4');
        if ( count($statuses)>0 ) {
            $filter_total_completed_sql .= "AND o.orders_status IN('".implode("','", array_map('intval', $statuses))."') ";    
        }
        
        $this->export_arrays = [];

        $main_sql =
            "SELECT COUNT(DISTINCT o.customers_email_address) AS stat_customers_count, ".
            "  COUNT(o.orders_id) AS stat_orders_count ".
            "FROM ".TABLE_ORDERS." o ".
            "WHERE 1 {$filter_sql} ";
        $this->export_arrays[0] = tep_db_fetch_array(tep_db_query( $main_sql ));

        $total_completed_sql = 
            "SELECT SUM(ROUND(ot_total_group_4.value,2)) AS completed_orders_total ".
            "FROM ".TABLE_ORDERS." o ".
            "  INNER JOIN ".TABLE_ORDERS_TOTAL." ot_total_group_4 ON ot_total_group_4.orders_id=o.orders_id AND ot_total_group_4.class='ot_total' ".
            "WHERE 1 {$filter_sql} {$filter_total_completed_sql} ";
        $total_completed_sql_r = tep_db_query($total_completed_sql);
        if ( tep_db_num_rows($total_completed_sql_r)>0 ) {
            $total_completed = tep_db_fetch_array($total_completed_sql_r);
            $this->export_arrays[0] = array_merge($this->export_arrays[0], $total_completed);
        }

        reset($this->export_arrays);
    }

    public function exportRow()
    {
        $this->data = current($this->export_arrays);
        next($this->export_arrays);
        return $this->data;
    }

    
}
