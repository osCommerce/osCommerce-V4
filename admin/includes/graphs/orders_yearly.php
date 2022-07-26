<?php
/*
  $Id: orders_yearly.php,v 1.1.1.1 2005/12/03 21:36:03 max Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  include(DIR_WS_CLASSES . 'phplot.php');
  $stats = array();


//echo "select year(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where 1 " . $sel_status_sql . " group by year(o.date_purchased), ot.class order by report_day, ot.sort_order ";
  //$orders_query = tep_db_query("select year(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where 1 " . $sel_status_sql . " group by year(o.date_purchased), ot.class order by report_day, ot.sort_order ");
  $orders_query = tep_db_query("select year(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ".(USE_MARKET_PRICES == 'True' ? "o.currency = '" . tep_db_input($_GET['currency'] ? $_GET['currency'] : DEFAULT_CURRENCY) . "'" : '1')." ". $sel_status_sql . " group by year(o.date_purchased), ot.class order by report_day, ot.sort_order ");
  
  $prev_day = -1;
  $report_data = array_merge(($show_total_order?array(date("Y"), 0):array(date("Y"))), $tmp);
  $i = 0;
  $max_total = 0;
  while ($order_stats = tep_db_fetch_array($orders_query)) {
    if (($prev_day != $order_stats['report_day']) && ($prev_day!=-1)){
//next year, save prev year data into $stats
      $stats[] = $report_data;
//      $report_data = $stats[$i];
      $report_data = array_merge(($show_total_order?array(date("Y"), 0):array(date("Y"))), $tmp);
      $i++;
      $max_total = 0;
    }
    $report_data[0] = $order_stats['report_day']; // 

    if ($show_total_order &&($max_total<$order_stats['report_total'])) {
      $report_data[1] = $order_stats['report_total'];
      $max_total=$order_stats['report_total'];
    } 
    if ((count($sel_total)>0) && in_array($order_stats['class'], $sel_total)){
      $report_data[$headers_array[$order_stats['class']]] = $order_stats['report_total_sum'];
    }
    $prev_day = $order_stats['report_day'];
  }
  if ($prev_day!=-1){
    if ($i==0) {
      $stats[] = $report_data;
      for($k=0; $k<count($report_data); $k++){
        if($k==0){
          $stats[0][0] = $report_data[0] - 1;
        } else {
          $stats[0][$k] = 0;
        }
      }
    }
    $stats[] = $report_data;
  }

  if(count($stats)>0)
  {
//  $graph = new PHPlot(600, 350, 'images/graphs/order_yearly.' . $order_extension);
  $graph = new PHPlot(600, 350);

  //$graph->SetFileFormat($order_extension);
  $graph->SetIsInline(1);

  $graph->SetPrintImage(0);

  $graph->SetSkipBottomTick(1);
  $graph->SetDrawYGrid(1);
  $graph->SetPrecisionY(0);
  $graph->SetPlotType($_GET['graph']);

  $graph->SetPlotBorderType('left');

  $graph->SetTitle(TEXT_SALES_YEARLY_STATISTICS);

  $graph->SetBackgroundColor('#eeeeee');

  $graph->SetVertTickPosition('plotleft');
  $graph->SetDataValues($stats);

  $graph->SetDataColors($colors_graph, $colors_graph);
  
  /*$arr = array(TABLE_HEADING_COUNT);
  foreach ($headers_array as $k => $v){
    $arr[] = str_replace('_', ' ', strtoupper(substr($k, 3)));
  }

  $graph->SetLegend($arr);
  $graph->SetLegendPixels(1,1,0);
  $graph->DrawLegend(0, 0, 0);
*/

  $graph->DrawGraph();

  ob_start();
  $graph->PrintImage();
  $img = ob_get_contents();
  ob_end_clean();
  if ($_GET['show_stat']==1)
    echo $img;
  }
?>
