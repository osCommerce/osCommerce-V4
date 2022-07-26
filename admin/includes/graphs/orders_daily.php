<?php
/*
  $Id: orders_daily.php,v 1.1.1.1 2005/12/03 21:36:03 max Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  include(DIR_WS_CLASSES . 'phplot.php');
  global $tmp;
  $year = (($_GET['year']) ? $_GET['year'] : date('Y'));
  $month = (($_GET['month']) ? $_GET['month'] : date('n'));

  $stats = array();
  if (($year==date('Y')) && ($month==date('n'))){
    $days = (date('d'));
  } else {
    $days = (date('t', mktime(0,0,0,$month,1)) );//+1
  }
  for ($i=1; $i<=$days; $i++) {
    $stats[] = array_merge(($show_total_order?array($i, 0):array($i)), $tmp);
  }

  //$sql = "select dayofmonth(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where month(o.date_purchased) = '" . $month . "' and year(o.date_purchased) = '" . $year . "' " . $sel_status_sql . " group by dayofmonth(o.date_purchased), ot.class order by report_day, ot.sort_order ";
  $sql = "select dayofmonth(o.date_purchased) as report_day, count(*) as report_total, sum(ot.value) as report_total_sum, ot.class from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where ".(USE_MARKET_PRICES == 'True' ? "o.currency = '" . tep_db_input($_GET['currency'] ? $_GET['currency'] : DEFAULT_CURRENCY) . "' and " : '')."  month(o.date_purchased) = '" . $month . "' and year(o.date_purchased) = '" . $year . "' " . $sel_status_sql . " group by dayofmonth(o.date_purchased), ot.class order by report_day, ot.sort_order ";
  $orders_query = tep_db_query($sql);
  $prev_day = -1;
  $report_data = array_merge(($show_total_order?array(0, 0):array(0)), $tmp);
  $max_total = 0;
  while ($order_stats = tep_db_fetch_array($orders_query)) {
    if (($prev_day != $order_stats['report_day']) && ($prev_day!=-1)){
      $stats[($prev_day-1)] = $report_data;
      $report_data = array_merge(($show_total_order?array(0, 0):array(0)), $tmp);
      $max_total = 0;
    }
    $report_data[0] = $order_stats['report_day']; // day of month
    if ($show_total_order &&($max_total<$order_stats['report_total'])) { //total orders per day
      $report_data[1] = $order_stats['report_total'];
      $max_total=$order_stats['report_total'];
    } 
    if ((count($sel_total)>0) && (in_array($order_stats['class'], $sel_total))){
      $report_data[$headers_array[$order_stats['class']]] = $order_stats['report_total_sum'];
    } 
    $prev_day = $order_stats['report_day'];
  }
  if ($prev_day!=-1){
    if ($i==0) {
      for($k=0; $k<count($report_data); $k++){
        if($k==0){
          $stats[($prev_day-2)][0] = $report_data[0] - 1;
        } else {
          $stats[($prev_day-2)][$k] = 0;
        }
      }
      $stats[($prev_day-1)] = $report_data;
    } else {
      $stats[($prev_day-1)] = $report_data;
    }
  }
  
  unset($stats[-1]);
  $graph = new PHPlot(600, 350); //, 'images/graphs/order_daily.' . $order_extension

  //$graph->SetFileFormat($order_extension);

  $graph->SetIsInline(1);
  $graph->SetPrintImage(0);

  $graph->SetSkipBottomTick(1);
  $graph->SetDrawYGrid(1);
  $graph->SetPrecisionY(0);
  $graph->SetPlotType($_GET['graph']);

  $graph->SetPlotBorderType('left');

  $graph->SetTitle(sprintf(TEXT_SALES_DAILY_STATISTICS, strftime('%B', mktime(0,0,0,$month,1)), $year));

  $graph->SetBackgroundColor('#eeeeee');

  $graph->SetVertTickPosition('plotleft');

  $graph->SetDataValues($stats);

  $graph->SetDataColors($colors_graph, $colors_graph);


/*  
  $arr = array(TABLE_HEADING_COUNT);
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
  
?>
