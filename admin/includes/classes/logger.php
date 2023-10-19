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

  class logger {
    var $timer_start, $timer_stop, $timer_total;

// class constructor
    function __construct() {
      $this->timer_start();
    }

    function timer_start() {
      if (defined("PAGE_PARSE_START_TIME")) {
        $this->timer_start = PAGE_PARSE_START_TIME;
      } else {
        $this->timer_start = microtime();
      }
    }

    function timer_stop($display = 'false') {
      $this->timer_stop = microtime();

      $time_start = explode(' ', $this->timer_start);
      $time_end = explode(' ', $this->timer_stop);

      $this->timer_total = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);

      $this->write(getenv('REQUEST_URI'), $this->timer_total . 's');

      if ($display == 'true') {
        return $this->timer_display();
      }
    }

    function timer_display() {
      return '<span class="smallText">Parse Time: ' . $this->timer_total . 's</span>';
    }

    function write($message, $type) {
      $f=null;
      if (!defined('STORE_PAGE_PARSE_TIME_LOG') || !(is_file(STORE_PAGE_PARSE_TIME_LOG) || ($f = @fopen(STORE_PAGE_PARSE_TIME_LOG, 'w+')))) { return; }
      if ($f) @fclose($f);

      if(STORE_PAGE_PARSE_IP == '*') {
        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $type . '] ' . $message . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
      }
      elseif(ip2long(STORE_PAGE_PARSE_IP) !== false && STORE_PAGE_PARSE_IP == \common\helpers\System::get_ip_address()) {
        error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' [' . $type . '] ' . $message . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
      }
      else {
        error_log(STORE_PAGE_PARSE_IP . TEXT_INVALID_IP . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);
      }
    }
  }
