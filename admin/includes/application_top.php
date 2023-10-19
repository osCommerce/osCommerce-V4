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

// Start the clock for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());

// Set the level of error reporting
  error_reporting(E_ALL & ~E_NOTICE);

// MySQL error
  $mysql_errors = array(); 

/*
// Check if register_globals is enabled.
// Since this is a temporary measure this message is hardcoded. The requirement will be removed before 2.2 is finalized.
  if (function_exists('ini_get')) {
    ini_get('register_globals') or exit('FATAL ERROR: register_globals is disabled in php.ini, please enable it!');
  }
*/

// set the type of request (secure or not)
  $request_type = (getenv('HTTPS') == 'on') ? 'SSL' : 'NONSSL';

// set php_self in the local scope
  
  if (isset($_SERVER['REQUEST_URI'])) $PHP_SELF = $_SERVER['REQUEST_URI'];
  if (!isset($PHP_SELF)) $PHP_SELF = preg_replace ("/.php/i", "", $_SERVER['PHP_SELF']);
  $pos = strpos($PHP_SELF, '?');
  if ($pos !== false) {
      $PHP_SELF = substr($PHP_SELF, 0, $pos);
  }
  
// Set the local configuration parameters - mainly for developers
  if (file_exists('includes/local/configure.php')) include('includes/local/configure.php');

// Include application configuration parameters
  require('includes/configure.php');

// include whitelabel config if exists
  if (file_exists('../includes/configure.WL.php')) include('../includes/configure.WL.php');  

// Define the project version
if (file_exists('../includes/version.php')) include('../includes/version.php'); 
if (defined('WL_ENABLED') && WL_ENABLED === true) {
    define('PROJECT_VERSION', PROJECT_VERSION_NAME . ' ' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '.' . PROJECT_VERSION_PATCH . ' ' . WL_PRODUCT_NAME);
} else {
    define('PROJECT_VERSION', PROJECT_VERSION_NAME . ' ' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '.' . PROJECT_VERSION_PATCH);
}

// Used in the "Backup Manager" to compress backups
  define('LOCAL_EXE_GZIP', '/bin/gzip');
  define('LOCAL_EXE_GUNZIP', '/bin/gunzip');
  define('LOCAL_EXE_ZIP', '/usr/bin/zip');
  define('LOCAL_EXE_UNZIP', '/usr/bin/unzip');

// include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');

// Define how do we update currency exchange rates
// Possible values are 'oanda' 'xe' or ''
  define('CURRENCY_SERVER_PRIMARY', 'oanda');
  define('CURRENCY_SERVER_BACKUP', 'xe');

// include the database functions
  require(DIR_WS_FUNCTIONS . 'database.php');

// make a connection to the database... now
  tep_db_connect() or die('Unable to connect to database server!');

// set application wide parameters
if (!file_exists('../lib/common/extensions/VatOnOrder/VatOnOrder.php')) {
    //define('ACCOUNT_COMPANY', 'disabled');
    define('ACCOUNT_COMPANY_VAT', 'disabled');
    define('ACCOUNT_CUSTOMS_NUMBER', 'disabled');
}

if (defined('PLATFORM_ID') && PLATFORM_ID > 0) {
    $platformDefault = tep_db_fetch_array(tep_db_query("select * from platforms where platform_id=" . PLATFORM_ID . "LIMIT 1 "));
} else {
    $platformDefault = tep_db_fetch_array(tep_db_query("select * from platforms " ."where is_default=1 " ."LIMIT 1 "));
}
define('STORE_NAME', $platformDefault['platform_name']);
define('STORE_OWNER', $platformDefault['platform_owner']);
define('EMAIL_FROM', $platformDefault['platform_email_from']);
define('STORE_OWNER_EMAIL_ADDRESS', $platformDefault['platform_email_address']);
define('SEND_EXTRA_ORDER_EMAILS_TO', $platformDefault['platform_email_extra']);

$get_store_country_config_r = tep_db_query("SELECT entry_country_id, entry_zone_id FROM platforms_address_book WHERE platform_id='".$platformDefault['platform_id']."' AND is_default=1 LIMIT 1");
if ( tep_db_num_rows($get_store_country_config_r)>0 ) {
  $_store_country_config = tep_db_fetch_array($get_store_country_config_r);
  define('STORE_COUNTRY', $_store_country_config['entry_country_id']);
  define('STORE_ZONE', $_store_country_config['entry_zone_id']);
}

  $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = tep_db_fetch_array($configuration_query)) {
    defined($configuration['cfgKey']) or define($configuration['cfgKey'], $configuration['cfgValue']);
  }
  $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_PLATFORMS_CONFIGURATION . ' where platform_id = "0" and configuration_key like "%\_EXTENSION\_%"');
  while ($configuration = tep_db_fetch_array($configuration_query)) {
    defined($configuration['cfgKey']) or define($configuration['cfgKey'], $configuration['cfgValue']);
  }
if (defined('PURCHASE_OFF_STOCK')) {
    if (PURCHASE_OFF_STOCK == 'true'){
        define('STOCK_CHECK', 'false');
        define('STOCK_ALLOW_CHECKOUT', 'true');
    } else {
        define('STOCK_CHECK', 'true');
        define('STOCK_ALLOW_CHECKOUT', 'false');
    }
}
require_once('../lib/common/helpers/Dbg.php');
\common\helpers\Dbg::defineConsts();

  // {{ time zones
  if ( !class_exists('\common\helpers\Date') ) {
    include_once('../lib/common/helpers/Date.php');
  }
  if ( class_exists('\common\helpers\Date') ) {
    \common\helpers\Date::setServerTimeZone( \common\helpers\Date::getDefaultServerTimeZone() );
  }
  // }} time zones

// define our general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');
  require(DIR_WS_FUNCTIONS . 'extra_control.php');

// initialize the logger class
  require(DIR_WS_CLASSES . 'logger.php');

  // set the cookie domain
  $cookie_domain = (($request_type == 'NONSSL') ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN);
  $cookie_path = (($request_type == 'NONSSL') ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH);
  
// define how the session functions will be used
  require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
  tep_session_name('tlAdminID');
  tep_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
   if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, DIR_WS_CATALOG);
  } elseif (function_exists('ini_set')) {
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', DIR_WS_CATALOG);
  }

// split-page-results
  require(DIR_WS_CLASSES . 'split_page_results.php');

// entry/item info classes
  require(DIR_WS_CLASSES . 'object_info.php');

// email classes
  require(DIR_WS_CLASSES . 'mime.php');
  require(DIR_WS_CLASSES . 'email.php');

// calculate category path
  if (isset($_GET['cPath'])) {
    $cPath = $_GET['cPath'];
  } else {
    $cPath = '';
  }

  $current_category_id = 0;

// the following cache blocks are used in the Tools->Cache section
// ('language' in the filename is automatically replaced by available languages)
  $cache_blocks = array(array('title' => defined('TEXT_CACHE_CATEGORIES')?TEXT_CACHE_CATEGORIES:'TEXT_CACHE_CATEGORIES', 'code' => 'categories', 'file' => 'categories_box-language.cache', 'multiple' => true),
                        array('title' => defined('TEXT_CACHE_MANUFACTURERS')?TEXT_CACHE_MANUFACTURERS:'TEXT_CACHE_MANUFACTURERS', 'code' => 'manufacturers', 'file' => 'manufacturers_box-language.cache', 'multiple' => true),
                        array('title' => defined('TEXT_CACHE_ALSO_PURCHASED')?TEXT_CACHE_ALSO_PURCHASED:'TEXT_CACHE_ALSO_PURCHASED', 'code' => 'also_purchased', 'file' => 'also_purchased-language.cache', 'multiple' => true)
                       );
	$tax_rates_array = array();
/*
// mysql error
  if(!tep_session_is_registered('mysql_error_dump')) {
    $mysql_error_dump = array();
    tep_session_register('mysql_error_dump');
    if(count($mysql_errors) > 0) {
      $mysql_error_dump = $mysql_errors;
    }
  } else {
    if(count($mysql_errors) > 0) {
      if(count($mysql_error_dump == 0)) {
        $mysql_error_dump = $mysql_errors;
      }
      else {
        $mysql_error_dump = array_merge($mysql_error_dump, $mysql_errors);
      }
    }
  }
*/
//check if xml dump/restore feature enabled


define('DIR_WS_TEMPLATE_IMAGES', 'images/');

