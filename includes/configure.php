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

// define our database connection
defined('DB_SERVER') or define('DB_SERVER', 'localhost');
defined('DB_SERVER_USERNAME') or define('DB_SERVER_USERNAME', 'root');
defined('DB_SERVER_PASSWORD') or define('DB_SERVER_PASSWORD', '12345678');
defined('DB_DATABASE') or define('DB_DATABASE', 'trueloadeduk');
defined('USE_PCONNECT') or define('USE_PCONNECT', 'false');
defined('STORE_SESSIONS') or define('STORE_SESSIONS', 'mysql');

// include the database functions
  global $dir_ws_includes;
  require_once(($dir_ws_includes ? $dir_ws_includes : 'includes/') . 'functions/database.php');

// make a connection to the database... now
  tep_db_connect() or die('Unable to connect to database server!');

  global $request_type;

  $platform = false;
  if(!defined('AdditionalPlatforms_EXTENSION_STATUS')){
      $arr = tep_db_fetch_array(tep_db_query(
                  "select configuration_value from platforms_configuration where configuration_key = 'AdditionalPlatforms_EXTENSION_STATUS'"
                  ));
      define('AdditionalPlatforms_EXTENSION_STATUS', is_array($arr) ? $arr['configuration_value'] ?? null : null );
  }

  if (file_exists('lib/common/extensions/AdditionalPlatforms/AdditionalPlatforms.php') && AdditionalPlatforms_EXTENSION_STATUS == 'True') {
    if ( !class_exists('\common\extensions\AdditionalPlatforms\AdditionalPlatforms') ) {
      include_once('lib/common/classes/modules/Module.php');
      include_once('lib/common/classes/modules/ModuleExtensions.php');
      include_once('lib/common/extensions/AdditionalPlatforms/AdditionalPlatforms.php');
      $platform = \common\extensions\AdditionalPlatforms\AdditionalPlatforms::configure();
    }
  } else {
      $REQUEST_PLATFORM_URL = rtrim($_SERVER['HTTP_HOST'].'/'.trim(dirname($_SERVER['SCRIPT_NAME']),'/'),'/');
      $_search_platform_url = 'p.platform_url';
      if ($request_type == 'SSL') {
          $_search_platform_url = 'IF(LENGTH(p.platform_url_secure)>0,p.platform_url_secure,p.platform_url)';
      }
      $platform = tep_db_fetch_array(tep_db_query(
          "select p.*, ".
          " IF(pu.url IS NULL,0,1) AS _platform_cdn_server, ".
          " IF(LENGTH(p.platform_url_secure)>0,p.platform_url_secure,p.platform_url) AS _platform_url_secure ".
          "from platforms p ".
          " left join platforms_url pu ON pu.platform_id=p.platform_id AND pu.url!={$_search_platform_url} AND pu.url = '" . tep_db_input($REQUEST_PLATFORM_URL.'/') . "' AND pu.status=1 ".
          "where p.platform_id='1' ".
          "LIMIT 1"
      ));
  }

    // CONSOLE APPLICATION DEFAULT PLATFORM LOAD
    // TO ENABLE - PASS '-dP' KEY
    if (!isset($platform['platform_id'])
        AND isset($_SERVER['argv']) AND is_array($_SERVER['argv'])
        AND isset($_SERVER['argv'][0]) AND (strtolower($_SERVER['argv'][0]) == 'yii.php')
        AND (in_array('-dP', $_SERVER['argv']))
    ) {
        $platform = tep_db_fetch_array(tep_db_query(
            "select p.*, ".
            " IF(pu.url IS NULL,0,1) AS _platform_cdn_server, ".
            " IF(LENGTH(p.platform_url_secure)>0,p.platform_url_secure,p.platform_url) AS _platform_url_secure ".
            "from platforms p ".
            " left join platforms_url pu ON pu.platform_id=p.platform_id AND pu.url!=IF(LENGTH(p.platform_url_secure)>0,p.platform_url_secure,p.platform_url) AND pu.status=1 ".
            "where p.is_default = 1 ".
            "LIMIT 1"
        ));
    }
    // EOF CONSOLE APPLICATION DEFAULT PLATFORM LOAD

  if (isset($platform['platform_id']) && $platform['platform_id'] > 0) {
      if ($platform['is_marketplace'] == 1) {
          $default_platform = tep_db_fetch_array(tep_db_query(
            "select *, IF(LENGTH(platform_url_secure)>0,platform_url_secure,platform_url) AS _platform_url_secure from platforms " .
            "where platform_id=" . $platform['default_platform_id'] . " " .
            "LIMIT 1 "
          ));
          $platform['platform_url'] = $default_platform['platform_url'];
          $platform['_platform_url_secure'] = $default_platform['_platform_url_secure'];
          $platform['ssl_enabled'] = $default_platform['ssl_enabled'];
          $platform['need_login'] = $default_platform['need_login'];
          $theme_array = tep_db_fetch_array(tep_db_query("select t.theme_name from platforms_to_themes AS p2t INNER JOIN themes as t ON (p2t.theme_id=t.id) where p2t.is_default = 1 and p2t.platform_id = " . (int)$default_platform['platform_id']));
          if ($theme_array['theme_name']){
                $_GET['theme_name'] = $theme_array['theme_name'];
            } else {
                $_GET['theme_name'] = 'theme-1';
            }
      }
      if ($platform['is_virtual'] == 1) {
          $default_platform = tep_db_fetch_array(tep_db_query(
            "select *, IF(LENGTH(platform_url_secure)>0,platform_url_secure,platform_url) AS _platform_url_secure from platforms " .
            "where is_default=1 " .
            "LIMIT 1 "
          ));
          $platform['platform_url'] = $default_platform['platform_url'];
          $platform['_platform_url_secure'] = $default_platform['_platform_url_secure'];
          $platform['ssl_enabled'] = $default_platform['ssl_enabled'];
          $platform['need_login'] = $default_platform['need_login'];
          $theme_array = tep_db_fetch_array(tep_db_query("select t.theme_name from platforms_to_themes AS p2t INNER JOIN themes as t ON (p2t.theme_id=t.id) where p2t.is_default = 1 and p2t.platform_id = " . (int)$default_platform['platform_id']));
          if ($theme_array['theme_name']){
                $_GET['theme_name'] = $theme_array['theme_name'];
            } else {
                $_GET['theme_name'] = 'theme-1';
            }
      }

    if ($platform['ssl_enabled'] == 2) {
        if ($request_type == 'NONSSL') {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("HTTP/1.1 301 Moved Permanently");
            header('Location: ' . $redirect);
            exit();
        }
        $secureProtocol = 'https';
        @ini_set('session.cookie_secure', 1);
    } else {
        $secureProtocol = 'http';
    }
    if ( isset($_GET['theme_name']) ) {
      $REQUEST_PLATFORM_URL = rtrim($_SERVER['HTTP_HOST'].'/'.trim(dirname($_SERVER['SCRIPT_NAME']),DIRECTORY_SEPARATOR),'/');
      $parsed = parse_url('http://'.rtrim($REQUEST_PLATFORM_URL, '/').'/');
      $parsed_ssl = $parsed;
      $parsed['host'] = $parsed['host'] ?? null;
      $parsed['port'] = $parsed['port'] ?? null;
      defined('HTTP_SERVER') or define('HTTP_SERVER', '//' . $parsed['host'] . ($parsed['port']!=''?":".$parsed['port']:""));
      defined('HTTPS_SERVER') or define('HTTPS_SERVER', '//' . $parsed['host'] . ($parsed['port']!=''?":".$parsed['port']:""));
    }else{
      $parsed = parse_url('http://'.rtrim($platform['platform_url'], "\\/\n\r\t\v\0").'/');
      $parsed_ssl = parse_url('http://'.rtrim($platform['_platform_url_secure']).'/');
      $port = isset($parsed['port']) && $parsed['port']!='' ? ":".$parsed['port'] : "";
      defined('HTTP_SERVER') or define('HTTP_SERVER', $secureProtocol . '://' . $parsed['host'] . $port);
      defined('HTTPS_SERVER') or define('HTTPS_SERVER', 'https://' . $parsed_ssl['host'] . $port);
      // {{ www redirect
      if ( isset($_SERVER['HTTP_HOST']) && stripos($_SERVER['HTTP_HOST'],'www.')!==0 && stripos(($request_type==='SSL'?$parsed_ssl['host']:$parsed['host']),'www.')===0 ){
          if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD'])=='GET') {
              $redirect = ($request_type === 'SSL' ? HTTPS_SERVER : HTTP_SERVER) . preg_replace('#/index\.php#', '/', $_SERVER['REQUEST_URI']);
              header("HTTP/1.1 301 Moved Permanently");
              header('Location: ' . $redirect);
              exit();
          }
      }
      // }} www redirect
    }
    defined('HTTP_COOKIE_DOMAIN') or define('HTTP_COOKIE_DOMAIN', $parsed['host']);
    defined('HTTPS_COOKIE_DOMAIN') or define('HTTPS_COOKIE_DOMAIN', $parsed_ssl['host']);

    defined('HTTP_COOKIE_PATH') or define('HTTP_COOKIE_PATH', $parsed['path']);
    defined('HTTPS_COOKIE_PATH') or define('HTTPS_COOKIE_PATH', $parsed_ssl['path']);
    defined('DIR_WS_HTTP_CATALOG') or define('DIR_WS_HTTP_CATALOG', $parsed['path']);
    defined('DIR_WS_HTTPS_CATALOG') or define('DIR_WS_HTTPS_CATALOG', $parsed_ssl['path']);

    defined('ENABLE_SSL') or define('ENABLE_SSL', !!$platform['ssl_enabled']);

    defined('PLATFORM_ID') or define('PLATFORM_ID', $platform['platform_id']);

    if (isset($platform['default_currency']) && !empty($platform['default_currency'])) {
        define('DEFAULT_CURRENCY', $platform['default_currency']);
    }
    if (isset($platform['default_language']) && !empty($platform['default_language'])) {
        define('DEFAULT_LANGUAGE', $platform['default_language']);
    }

    define('PLATFORM_NEED_LOGIN', $platform['need_login']);

    define('STORE_NAME', $platform['platform_name']);
    define('STORE_OWNER', $platform['platform_owner']);

    if ($platform['is_default_contact'] == 1 && $platform['default_platform_id'] > 0) {
        $platformDefault = tep_db_fetch_array(tep_db_query(
            "select * from platforms " .
            "where platform_id=" . (int)$platform['default_platform_id'] . " " .
            "LIMIT 1 "
          ));
        define('EMAIL_FROM', $platformDefault['platform_email_from']);
        define('STORE_OWNER_EMAIL_ADDRESS', $platformDefault['platform_email_address']);
        define('SEND_EXTRA_ORDER_EMAILS_TO', $platformDefault['platform_email_extra']);
    } else if ($platform['is_default_contact'] == 1) {
        $platformDefault = tep_db_fetch_array(tep_db_query(
            "select * from platforms " .
            "where is_default=1 " .
            "LIMIT 1 "
        ));
        define('EMAIL_FROM', $platformDefault['platform_email_from']);
        define('STORE_OWNER_EMAIL_ADDRESS', $platformDefault['platform_email_address']);
        define('SEND_EXTRA_ORDER_EMAILS_TO', $platformDefault['platform_email_extra']);
    } else {
        define('EMAIL_FROM', $platform['platform_email_from']);
        define('STORE_OWNER_EMAIL_ADDRESS', $platform['platform_email_address']);
        define('SEND_EXTRA_ORDER_EMAILS_TO', $platform['platform_email_extra']);
    }

    if ($platform['is_default_address'] == 1 && $platform['default_platform_id'] > 0) {
        $get_store_country_config_r = tep_db_query("SELECT pab.entry_country_id, pab.entry_zone_id FROM platforms_address_book AS pab INNER JOIN platforms AS p ON pab.platform_id=p.platform_id WHERE p.platform_id='".$platform['default_platform_id']."' LIMIT 1");
    } else if ($platform['is_default_address'] == 1) {
        $get_store_country_config_r = tep_db_query("SELECT pab.entry_country_id, pab.entry_zone_id FROM platforms_address_book AS pab INNER JOIN platforms AS p ON pab.platform_id=p.platform_id WHERE p.is_default=1 and pab.is_default=1 LIMIT 1");
    } else {
        $get_store_country_config_r = tep_db_query("SELECT entry_country_id, entry_zone_id FROM platforms_address_book WHERE platform_id='".$platform['platform_id']."' AND is_default=1 LIMIT 1");
    }

    if ( tep_db_num_rows($get_store_country_config_r)>0 ) {
      $_store_country_config = tep_db_fetch_array($get_store_country_config_r);
      define('STORE_COUNTRY', $_store_country_config['entry_country_id']);
      define('STORE_ZONE', $_store_country_config['entry_zone_id']);
    }

    define('IS_IMAGE_CDN_SERVER', !!$platform['_platform_cdn_server']);
  } else {
    // fallback - db connect error
    defined('HTTP_SERVER') or define('HTTP_SERVER', 'http://tl.local');
    defined('HTTPS_SERVER') or define('HTTPS_SERVER', 'https://tl.local');
    defined('HTTP_COOKIE_DOMAIN') or define('HTTP_COOKIE_DOMAIN', '/');
    defined('HTTPS_COOKIE_DOMAIN') or define('HTTPS_COOKIE_DOMAIN', '/');

    defined('HTTP_COOKIE_PATH') or define('HTTP_COOKIE_PATH', '');
    defined('HTTPS_COOKIE_PATH') or define('HTTPS_COOKIE_PATH', '/');
    defined('DIR_WS_HTTP_CATALOG') or define('DIR_WS_HTTP_CATALOG', '/');
    defined('DIR_WS_HTTPS_CATALOG') or define('DIR_WS_HTTPS_CATALOG', '/');

    defined('ENABLE_SSL') or define('ENABLE_SSL', false);

    defined('PLATFORM_ID') or define('PLATFORM_ID', 0);

    defined('PLATFORM_NEED_LOGIN') or define('PLATFORM_NEED_LOGIN', 0);

    defined('IS_IMAGE_CDN_SERVER') or define('IS_IMAGE_CDN_SERVER', false);
  }

  defined('AFFILIATE_ID') or define('AFFILIATE_ID', 0);

  define('DIR_WS_HTTP_ADMIN_CATALOG', 'admin/');
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
  define('DIR_WS_INCLUDES', 'includes/');
  define('DIR_WS_BOXES', DIR_WS_INCLUDES . 'boxes/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
  define('DIR_WS_AFFILIATES', 'affiliates/');

//Added for BTS1.0
  define('DIR_WS_TEMPLATES', 'templates/');
  define('DIR_WS_CONTENT', DIR_WS_TEMPLATES . 'content/');
//End BTS1.0
  define('DIR_WS_DOWNLOAD_PUBLIC', '/pub/');
  define('DIR_FS_CATALOG', dirname(dirname(__FILE__)) . '/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
  define('DIR_FS_AFFILIATES', DIR_FS_CATALOG . 'affiliates/');
  define('DIR_WS_TEMPLATE_IMAGES', 'images/');
