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

// start the timer for the page parse time log
  define('PAGE_PARSE_START_TIME', microtime());
  ini_set('session.use_only_cookies', '0');
// set the level of error reporting
  if(defined('E_DEPRECATED'))
  {
    //error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    error_reporting(E_ALL & ~E_NOTICE);
  }
  else
  {
    error_reporting(E_ALL & ~E_NOTICE);
  }

// MySQL error
  $mysql_errors = array(); 

//============================ error log =============================
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars) {
   // timestamp for the error entry
   $dt = date("Y-m-d H:i:s (T)");
   $errortype = array (
               E_ERROR              => 'Error',
               E_WARNING            => 'Warning',
               E_PARSE              => 'Parsing Error',
               E_NOTICE             => 'Notice',
               E_CORE_ERROR         => 'Core Error',
               E_CORE_WARNING       => 'Core Warning',
               E_COMPILE_ERROR      => 'Compile Error',
               E_COMPILE_WARNING    => 'Compile Warning',
               E_USER_ERROR         => 'User Error',
               E_USER_WARNING       => 'User Warning',
               E_USER_NOTICE        => 'User Notice',
               E_STRICT             => 'Runtime Notice',
               E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
               );
   // set of errors for which a var trace will be saved
   $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);
   
   $err = "<errorentry>\n";
   $err .= "\t<datetime>" . $dt . "</datetime>\n";
   $err .= "\t<errornum>" . $errno . "</errornum>\n";
   $err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
   $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
   $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
   $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

   if (in_array($errno, $user_errors)) {
     $err .= "\t<vartrace>" . var_export($vars,true) . "</vartrace>\n";
   }
   $err .= "</errorentry>\n\n<br>";
   echo $err; 
}
//$old_error_handler = set_error_handler("userErrorHandler");
//\=========================== error log =============================
/*
// check if register_globals is enabled.
// since this is a temporary measure this message is hardcoded. The requirement will be removed before 2.2 is finalized.
  if (function_exists('ini_get')) {
    ini_get('register_globals') or exit('FATAL ERROR: register_globals is disabled in php.ini, please enable it!');
  }
*/

// set the type of request (secure or not)
  $request_type = (
      isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
      || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0
    )?'SSL':'NONSSL';

// set php_self in the local scope
  if (isset($_SERVER['SCRIPT_NAME'])) $PHP_SELF = $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
  if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

// Set the local configuration parameters - mainly for developers
  if (file_exists('includes/local/configure.php')) include_once('includes/local/configure.php');

// include server parameters
  require('includes/configure.php');

// include whitelabel config if exists
  if (file_exists('includes/configure.WL.php')) include('includes/configure.WL.php');  

  if (strlen(DB_SERVER) < 1) {
    if (is_dir('install')) {
      header('Location: install/index.php');
    }
  }

  if ( !class_exists('\common\classes\platform') ) {
    include_once('lib/common/classes/platform.php');
  }

// define the project version
if (file_exists('includes/version.php')) include('includes/version.php'); 
if (defined('WL_ENABLED') && WL_ENABLED === true) {
    define('PROJECT_VERSION', PROJECT_VERSION_NAME . ' ' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '.' . PROJECT_VERSION_PATCH . ' ' . WL_PRODUCT_NAME);
} else {
    define('PROJECT_VERSION', PROJECT_VERSION_NAME . ' ' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . '.' . PROJECT_VERSION_PATCH);
}

// set php_self in the local scope
  if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
  
  if ($request_type == 'NONSSL') {
    define('DIR_WS_CATALOG', DIR_WS_HTTP_CATALOG);
  } else {
    define('DIR_WS_CATALOG', DIR_WS_HTTPS_CATALOG);
  }

// include the list of project filenames
  require(DIR_WS_INCLUDES . 'filenames.php');

// include the list of project database tables
  require(DIR_WS_INCLUDES . 'database_tables.php');

if (!file_exists('lib/common/extensions/VatOnOrder/VatOnOrder.php')) {
    //define('ACCOUNT_COMPANY', 'disabled');
    define('ACCOUNT_COMPANY_VAT_ID', 'disabled');
    define('ACCOUNT_CUSTOMS_NUMBER', 'disabled');
}

  if (PLATFORM_ID > 0) {
        $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_PLATFORMS_CONFIGURATION . ' where platform_id = ' . PLATFORM_ID);
        while ($configuration = tep_db_fetch_array($configuration_query)) {
            if (!defined($configuration['cfgKey'])) {
                define($configuration['cfgKey'], $configuration['cfgValue']);
            }
        }
        tep_db_free_result($configuration_query);

        $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_PLATFORMS_CONFIGURATION . ' where platform_id = "0" and configuration_key like "%\_EXTENSION\_%"');
        while ($configuration = tep_db_fetch_array($configuration_query)) {
            if (!defined($configuration['cfgKey'])) {
                define($configuration['cfgKey'], $configuration['cfgValue']);
            }
        }
        tep_db_free_result($configuration_query);
  }
  
  $configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
  while ($configuration = tep_db_fetch_array($configuration_query)) {
      if (!defined($configuration['cfgKey'])) {
        defined($configuration['cfgKey']) or define($configuration['cfgKey'], $configuration['cfgValue']);
      }
    /*
    if ($configuration['cfgKey'] == 'STORE_NAME') {
      $store_name = $configuration['cfgValue'];
    }else if ($configuration['cfgKey'] == 'STORE_OWNER') {
      $store_owner = $configuration['cfgValue'];
    } else if ($configuration['cfgKey'] == 'STORE_OWNER_EMAIL_ADDRESS') {
      $store_owner_email_address = $configuration['cfgValue'];
    } else if ($configuration['cfgKey'] == 'EMAIL_FROM') {
      $email_from = $configuration['cfgValue'];
    } else if ($configuration['cfgKey'] == 'STORE_OWNER_EMAIL_ADDRESS') {
      $store_owner_email_address = $configuration['cfgValue'];
    } else {
      define($configuration['cfgKey'], $configuration['cfgValue']);
    }
    */
  }
  tep_db_free_result($configuration_query);
  require_once('lib/common/helpers/Dbg.php');
  \common\helpers\Dbg::defineConsts();

  if (!defined("DEFAULT_USER_GROUP")) {
    define("DEFAULT_USER_GROUP", 0);
  }
/* Moved to InitFactory
if (defined('PURCHASE_OFF_STOCK')) {
    if (PURCHASE_OFF_STOCK == 'true'){
        define('STOCK_CHECK', 'false');
        define('STOCK_ALLOW_CHECKOUT', 'true');
    } else {
        define('STOCK_CHECK', 'true');
        define('STOCK_ALLOW_CHECKOUT', 'false');
    }
}
*/
  // {{ time zones
  if ( !class_exists('\common\helpers\Date') ) {
    include_once('lib/common/helpers/Date.php');
  }
  if ( class_exists('\common\helpers\Date') ) {
    \common\helpers\Date::setServerTimeZone( \common\helpers\Date::getDefaultServerTimeZone() );
  }
  // }} time zones

  $tax_rates_array = array();

// define general functions used application-wide
  require(DIR_WS_FUNCTIONS . 'general.php');
  require(DIR_WS_FUNCTIONS . 'html_output.php');


// set the cookie domain
  $cookie_domain = (($request_type == 'NONSSL') ? HTTP_COOKIE_DOMAIN : HTTPS_COOKIE_DOMAIN);
  $cookie_path = (($request_type == 'NONSSL') ? HTTP_COOKIE_PATH : HTTPS_COOKIE_PATH);

// include cache functions if enabled
  //if (USE_CACHE == 'true') include(DIR_WS_FUNCTIONS . 'cache.php');

// define how the session functions will be used
  require(DIR_WS_FUNCTIONS . 'sessions.php');

// set the session name and save path
  if ( defined('SESSION_NAME_POSTFIX') && SESSION_NAME_POSTFIX=='Sale Channel Id' ){
      tep_session_name('tlSID'.intval(PLATFORM_ID));
  }else {
      tep_session_name('tlSID');
  }
  tep_session_save_path(SESSION_WRITE_DIRECTORY);

// set the session cookie parameters
  if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, $cookie_path, $cookie_domain);
  } elseif (function_exists('ini_set')) {
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', $cookie_path);
    ini_set('session.cookie_domain', $cookie_domain);
  }

  //{{ for old version
    if (basename($PHP_SELF) != 'index.php' and is_file($_SERVER["DOCUMENT_ROOT"].$PHP_SELF)){
      $dir = dirname(__DIR__);
      try{
        require($dir . '/lib/vendor/autoload.php');
        require($dir . '/lib/vendor/yiisoft/yii2/Yii.php');
        require($dir . '/lib/common/config/bootstrap.php');

        $config = yii\helpers\ArrayHelper::merge(
            require($dir . '/lib/common/config/main.php'),
            require($dir . '/lib/common/config/main-local.php'),
            require($dir . '/lib/frontend/config/main.php'),
            require($dir . '/lib/frontend/config/main-local.php')
        );

        $application = new yii\web\Application($config);      
        
      } catch(Exception $e){
        echo $e->getMessage();
      }
    }
  //}}
  /*common\models\sessionFlow*/

// set which precautions should be checked
  define('WARN_INSTALL_EXISTENCE', 'true');
  define('WARN_CONFIG_WRITEABLE', 'true');
  define('WARN_SESSION_DIRECTORY_NOT_WRITEABLE', 'true');
  define('WARN_SESSION_AUTO_START', 'true');
  define('WARN_DOWNLOAD_DIRECTORY_NOT_READABLE', 'true');

  


