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

  $counter_query = tep_db_query("select startdate, counter from " . TABLE_COUNTER);

  if (!tep_db_num_rows($counter_query)) {
    $date_now = date('Ymd');
    tep_db_query("insert into " . TABLE_COUNTER . " (startdate, counter) values ('" . $date_now . "', '1')");
    $counter_startdate = $date_now;
    $counter_now = 1;
  } else {
    $counter = tep_db_fetch_array($counter_query);
    $counter_startdate = $counter['startdate'];
    $counter_now = ($counter['counter'] + 1);
    tep_db_query("update " . TABLE_COUNTER . " set counter = '" . $counter_now . "'");
  }

  $counter_startdate_formatted = strftime(DATE_FORMAT_LONG, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));

