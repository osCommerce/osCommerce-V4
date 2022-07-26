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

function tep_redirect($url) {
    if ((strstr($url, "\n") != false) || (strstr($url, "\r") != false)) {
        tep_redirect(tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));
    }
    if ((ENABLE_SSL == true) && (getenv('HTTPS') == 'on')) { // We are loading an SSL page
        if (substr($url, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) { // NONSSL url
            $url = HTTPS_SERVER . substr($url, strlen(HTTP_SERVER)); // Change it to SSL
        }
    }
    $url = str_replace('&amp;', '&', $url);
    header('Location: ' . $url);
    if (session_status() === PHP_SESSION_ACTIVE) {tep_session_close();}
    exit();
}

function tep_not_null($value) {
    if (is_array($value)) {
        if (sizeof($value) > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
            return true;
        } else {
            return false;
        }
    }
}

function tep_round($number, $precision) {
// {{
  if (abs($number) < (1 / pow(10, $precision + 1))) $number = 0;
// }}
  if (strpos($number, '.') && (strlen(substr($number, strpos($number, '.') + 1)) > $precision)) {
    $number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);

    if (substr($number, -1) >= 5) {
      if ($precision > 1) {
        $number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision-1) . '1');
      } elseif ($precision == 1) {
        $number = substr($number, 0, -1) + 0.1;
      } else {
        $number = substr($number, 0, -1) + 1;
      }
    } else {
      $number = substr($number, 0, -1);
    }
  }

  return $number;
}