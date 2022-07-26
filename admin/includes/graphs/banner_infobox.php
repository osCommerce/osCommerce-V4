<?php
/*
  $Id: banner_infobox.php,v 1.1.1.1 2005/12/03 21:36:03 max Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  include(DIR_WS_CLASSES . 'phplot.php');

  $stats = array();
  $banner_stats_query = tep_db_query("select dayofmonth(banners_history_date) as name, banners_shown as value, banners_clicked as dvalue from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . $banner_id . "' and to_days(now()) - to_days(banners_history_date) < " . $days . " order by banners_history_date");
  while ($banner_stats = tep_db_fetch_array($banner_stats_query)) {
    $stats[] = array($banner_stats['name'], $banner_stats['value'], $banner_stats['dvalue']);
  }

  if (sizeof($stats) < 1) $stats = array(array(date('j'), 0, 0));

  $graph = new PHPlot(320, 320, 'images/graphs/banner_infobox-' . $banner_id . '.' . $banner_extension);

  //$graph->SetFileFormat($banner_extension);
  $graph->SetIsInline(1);
  $graph->SetPrintImage(0);

  $graph->draw_vert_ticks = 0;
  $graph->SetSkipBottomTick(1);
  $graph->SetDrawXDataLabels(0);
  $graph->SetDrawYGrid(0);
  $graph->SetPlotType('bars');
  //$graph->SetDrawDataLabels(1);
  $graph->SetLabelScalePosition(1);
  $graph->SetMarginsPixels(40,40,40,40);

  $graph->SetTitle(TEXT_BANNERS_LAST_3_DAYS);

  $graph->SetDataValues($stats);
  $graph->SetDataColors(array('blue','red'),array('blue', 'red'));

  $graph->DrawGraph();

  $graph->PrintImage();
?>
