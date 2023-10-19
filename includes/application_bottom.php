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

// close session (store variables)

if(isset($mysql_error_dump) && is_array($mysql_error_dump) && count($mysql_error_dump)>0) {
    if ( defined('YII_DEBUG') && YII_DEBUG ) {
        ?>
        <div id="id_mysql_error">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="heading">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="heading">You have an error in your SQL syntax</td>
                                <td class="close"><a
                                            onclick="javascript: document.getElementById('id_mysql_error').style.display = 'none';"
                                            href="javascript: void(0);">[Hide]</a></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="error">
                        <?php
                        for ($i = 0; $i < count($mysql_error_dump); $i++) {
                            if ($i != 0) echo '<hr>';
                            echo $mysql_error_dump[$i];
                        }
                        tep_session_unregister('mysql_error_dump');
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }else{
        tep_session_unregister('mysql_error_dump');
    }
}
  if (session_status() === PHP_SESSION_ACTIVE) {tep_session_close();}

  if (STORE_PAGE_PARSE_TIME == 'true') {
    $time_start = explode(' ', PAGE_PARSE_START_TIME);
    $time_end = explode(' ', microtime());
    $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
    $f = null;
    if (defined('STORE_PAGE_PARSE_TIME_LOG') && (is_file(STORE_PAGE_PARSE_TIME_LOG) || ($f = @fopen(STORE_PAGE_PARSE_TIME_LOG, 'w+')))) {
      if ($f) @fclose($f);

      if (STORE_PAGE_PARSE_IP == '*') {
          error_log('Trans total: ' . $trans_count . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . getenv('REQUEST_URI') . ' (' . $parse_time . 's)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
      } elseif (ip2long(STORE_PAGE_PARSE_IP) !== false && STORE_PAGE_PARSE_IP == \common\helpers\System::get_ip_address()) {
          error_log('Trans total: ' . $trans_count . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
          error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . getenv('REQUEST_URI') . ' (' . $parse_time . 's)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
      } else {
          error_log(STORE_PAGE_PARSE_IP . TEXT_INVALID_IP . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
      }
    }
    

    if (DISPLAY_PAGE_PARSE_TIME == 'true') {
      echo '<span class="smallText">Parse Time: ' . $parse_time . 's</span>';
    }
  }
