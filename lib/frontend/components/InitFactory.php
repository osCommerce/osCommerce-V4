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

namespace app\components;

use Yii;

class InitFactory {
  
  public static function init(){
    global $session_started, $request_type, $ssl_session_id, $http_user_agent, $mysql_errors, $mysql_error_dump, $device, $SID, $user_agent;
    //global $customer_groups_id;
    global $lng, $language, $languages_id, $breadcrumb, $navigation;
    global $platform_code;

    /*init session from appication_top*/

    if ( ($session_started == true) && function_exists('ini_get') && (ini_get('register_globals') == false || ini_get('register_globals') == "Off") )
    {
     if (is_array($_SESSION)){
        $skip = ['language', 'shipping', 'payment'];
        extract($_SESSION, EXTR_OVERWRITE+EXTR_REFS);
        foreach($_SESSION as $_key => $_item){
          global $$_key;
          $$_key = $GLOBALS[$_key] = &$_SESSION[$_key];
        }
      }
     
    }

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')){
        $ext::setExclusionRules();
    }
    
     if (!tep_session_is_registered('platform_code')) {
         tep_session_register('platform_code'); 
         $platform_code = '';
     }
     if (isset($_GET['code']) && $_GET['code'] == 'WBUKGOOGLE') {//test on google
         $platform_code = $_GET['code'];
     } else if (isset($_GET['code']) && !empty($_GET['code'])){
         if ($ext = \common\helpers\Acl::checkExtensionAllowed('AdditionalPlatforms', 'allowed')){
             $ext::setSattelite($_GET['code']);
         }
     } else {
         if ($ext = \common\helpers\Acl::checkExtensionAllowed('AdditionalPlatforms', 'allowed')){
             $ext::checkSattelite();
         }
     }
     
    // set SID once, even if empty
    $SID = (defined('SID') ? SID : '');

     if ( strpos(Yii::$app->id,'frontend')!==false ){
         $_params = \common\helpers\System::get_cookie_params();
         unset($_params['path']); //????
         Yii::$app->request->csrfCookie = array_merge(Yii::$app->request->csrfCookie, $_params);
     }

  // verify the ssl_session_id if the feature is enabled
    if ( ($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started == true) ) {
      $ssl_session_id = getenv('SSL_SESSION_ID');
      if (!tep_session_is_registered('SSL_SESSION_ID')) {
        $SESSION_SSL_ID = $ssl_session_id;
        tep_session_register('SESSION_SSL_ID');
      }

      if ($SESSION_SSL_ID != $ssl_session_id) {
        tep_session_destroy();
        tep_redirect(tep_href_link(FILENAME_SSL_CHECK));
      }
    }

    if (!Yii::$app->user->isGuest && Yii::$app->user->getId() > 0) {
      if (!\common\helpers\Customer::is_customer_exist(Yii::$app->user->getId())) {
        Yii::$app->user->getIdentity()->logoffCustomer();        
        tep_session_unregister('comments');
        tep_session_unregister('customer_groups_id');
        tep_session_unregister('cart_address_id');
        tep_session_unregister('gv_id');
        tep_session_unregister('cc_id');
        tep_redirect(tep_href_link(FILENAME_LOGIN));
      } else {
          if (Yii::$app->storage->has('customer_groups_id')) {// CHECK IF CUSTOMER GROUP HAS CHANGED
            $groups_id = \common\helpers\Customer::get_customers_group(Yii::$app->user->getId());
            if ($groups_id != Yii::$app->storage->get('customer_groups_id')) {
                Yii::$app->storage->set('customer_groups_id', $groups_id);
            }
          }
      }
    } else {
        // if not logged customer - default group can be different for different platforms
        Yii::$app->storage->set('customer_groups_id', DEFAULT_USER_GROUP);
    }

  // verify the browser user agent if the feature is enabled
    if (SESSION_CHECK_USER_AGENT == 'True') {
      $http_user_agent = getenv('HTTP_USER_AGENT');
      if (!tep_session_is_registered('SESSION_USER_AGENT')) {
        $SESSION_USER_AGENT = $http_user_agent;
        tep_session_register('SESSION_USER_AGENT');
      }

      if ($SESSION_USER_AGENT != $http_user_agent) {
        tep_session_destroy();
        tep_redirect(tep_href_link(FILENAME_LOGIN));
      }
    }

  // verify the IP address if the feature is enabled
    if (SESSION_CHECK_IP_ADDRESS == 'True') {
      $ip_address = \common\helpers\System::get_ip_address();
      if (!tep_session_is_registered('SESSION_IP_ADDRESS')) {
        $SESSION_IP_ADDRESS = $ip_address;
        tep_session_register('SESSION_IP_ADDRESS');
      }

      if ($SESSION_IP_ADDRESS != $ip_address) {
        tep_session_destroy();
        tep_redirect(tep_href_link(FILENAME_LOGIN));
      }
    }

    /*if (SEARCH_ENGINE_STATS == 'True') {
      \common\helpers\System::referer_stat();
    }*/   

  // mysql error
    if(!tep_session_is_registered('mysql_error_dump'))
    {
        $mysql_error_dump = array();
        tep_session_register('mysql_error_dump');
        if(is_array($mysql_errors) && count($mysql_errors) > 0) {
            $mysql_error_dump = $mysql_errors;
        }
    }
    elseif( is_array($mysql_errors) && count($mysql_errors) > 0) {
        if (count($mysql_error_dump) == 0) {
            $mysql_error_dump = $mysql_errors;
        } else {
            $mysql_error_dump = array_merge($mysql_error_dump, $mysql_errors);
        }
    }

    $currencies = Yii::$container->get('currencies');
    $languages_id = (int)\Yii::$app->settings->get('languages_id');
    
    $lng = new \common\classes\language();

    if (!tep_session_is_registered('languages_id') || $languages_id == 0 || (isset($_GET['language']) ) ) {
        if (!tep_session_is_registered('languages_id')) {
          tep_session_register('languages_id');
          tep_session_register('language');
        }
        if (!tep_session_is_registered('locale')){
          tep_session_register('locale');
        }

        if (isset($_GET['language']) && tep_not_null($_GET['language'])) {
          $lng->set_language($_GET['language']);
        } else {
          $lng->get_browser_language();
        }

        $language = $_SESSION['language'] = $lng->language['directory'];
        $languages_id = $lng->language['id'];
        \Yii::$app->settings->set('locale', $lng->language['locale']);
        \Yii::$app->settings->set('languages_id', $languages_id);//preparing to switch  
    } else {
        $language = $_SESSION['language'];
    }

    if ( Yii::$app->id!='app-frontend' ) {
        $lng->set_locale();
        $lng->load_vars();
    }

    $breadcrumb = new \common\classes\breadcrumb;

    $currency = \Yii::$app->settings->get('currency');
    $currency_id = \Yii::$app->settings->get('currency_id');
    
    if ($currency === false || !in_array($currency, $currencies->platform_currencies))
    {
      $currency = null;
      $currency_id = 0;
    }

    // LANGUAGE_CURRENCY is not defined yet
    $langCurrency = $lng->get_language_formats($languages_id)['LANGUAGE_CURRENCY'] ?? (empty($currency)? 'GBP' : $currency);
    if (is_null($currency) || isset($_GET['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && ($langCurrency != $currency) ) ) {
      $_maybe_currency = null;
      if (isset($_GET['currency'])){
        $_maybe_currency = $_GET['currency'];
      } elseif (USE_DEFAULT_LANGUAGE_CURRENCY == 'true'){
        $_maybe_currency = $langCurrency;
      }
      if (!in_array($_maybe_currency, $currencies->platform_currencies) || is_null($_maybe_currency)){
        $currency = $currencies->dp_currency;
      } else {
        $currency = $_maybe_currency;
      }
      $currency_id = 0;
    }
  
    if (!$currency_id || isset($_GET['currency']) || ( (USE_DEFAULT_LANGUAGE_CURRENCY == 'true') && ($langCurrency != $currency) )) {
      $currency_id = $currencies->currencies[$currency]['id'];
    }
    \Yii::$app->settings->set('currency_id', $currency_id);
    \Yii::$app->settings->set('currency', $currency);

    
    // navigation history
    if (!tep_session_is_registered('navigation'))
    {
      tep_session_register('navigation');    
      $navigation = new \common\classes\navigation();
    }
    if (is_object($navigation) && method_exists($navigation, 'add_current_page')){
      $navigation->add_current_page();
    }

  if (!isset($PHP_SELF)) $PHP_SELF = @$_SERVER['PHP_SELF'];


  /***
  * actions moved to \frontend\models\Cartfactory
  ***/

  self::tep_update_whos_online();
  \common\helpers\Specials::tep_expire_specials();
  \common\helpers\Featured::tep_expire_featured();

  /// 
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    if (stripos($user_agent, 'iphone') !== false ||
        stripos($user_agent, 'ipod') !== false ||
        stripos($user_agent, 'blackberry') !== false ||
        (stripos($user_agent, 'BB10') !== false && stripos($user_agent, 'Mobile') !== false) ||
        stripos($user_agent, 'NOKIA') !== false ||
        (stripos($user_agent, 'android') !== false && stripos($user_agent, 'mobile') !== false)) {
      if ((isset($_GET['r']) && $_GET['r'] == 'fullsite' ) || (isset($_SESSION['device']) && $_SESSION['device'] == 'pc')) {
        $device = 'pc';
      } else {
        $device = 'mobile';
      }
    } elseif (stripos($user_agent, 'ipad') !== false ||
              stripos($user_agent, 'android') !== false) {
      $device = 'tablet';
    } else {
      $device = 'pc';
    }
    if (!tep_session_is_registered('device')) tep_session_register('device');

    if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
        $ext::checkCustomerGroups();
    }
    defined('GROUPS_IS_SHOW_PRICE') or define('GROUPS_IS_SHOW_PRICE', true);
    defined('GROUPS_DISABLE_CART') or define('GROUPS_DISABLE_CART', false);
    defined('GROUPS_DISABLE_CHECKOUT') or define('GROUPS_DISABLE_CHECKOUT', false);


      if (\frontend\design\Info::hasBlog()){
          global $Blog;
          $Blog = new \common\classes\Blog;
      }

    foreach (\common\helpers\Hooks::getList('init-factory/init') as $filename) {
        include($filename);
    }

    // After hook to allow extensions overwrite these constants
    if (defined('PURCHASE_OFF_STOCK')) {
        if (PURCHASE_OFF_STOCK == 'true') {
            defined('STOCK_CHECK') or define('STOCK_CHECK', 'false');
            defined('STOCK_ALLOW_CHECKOUT') or define('STOCK_ALLOW_CHECKOUT', 'true');
        } else {
            defined('STOCK_CHECK') or define('STOCK_CHECK', 'true');
            defined('STOCK_ALLOW_CHECKOUT') or define('STOCK_ALLOW_CHECKOUT', 'false');
        }
    }

// {{ Show out of stock switcher
    if (!tep_session_is_registered('SHOW_OUT_OF_STOCK')) {
      tep_session_register('SHOW_OUT_OF_STOCK');
      $SHOW_OUT_OF_STOCK = $_SESSION['SHOW_OUT_OF_STOCK'] = 1;
    } else {
      $SHOW_OUT_OF_STOCK = $_SESSION['SHOW_OUT_OF_STOCK'];
    }
    if (isset($_POST['show_out_of_stock_update']) && $_POST['show_out_of_stock_update'] == 1) {
      $SHOW_OUT_OF_STOCK = $_SESSION['SHOW_OUT_OF_STOCK'] = (int)($_POST['show_out_of_stock'] ?? 0);
      Yii::$app->response->redirect(Yii::$app->request->getReferrer());
    }
    define('SHOW_OUT_OF_STOCK', $SHOW_OUT_OF_STOCK);
// }}
    
    return;
  }
  
  public static function tep_update_whos_online() {
    

    if (!Yii::$app->user->isGuest) {
      $wo_customer_id = Yii::$app->user->getId();
      
      $customer = Yii::$app->user->getIdentity();

      $wo_full_name = $customer['customers_firstname'] . ' ' . $customer['customers_lastname'];
    } else {
      $wo_customer_id = '';
      $wo_full_name = 'Guest';
    }

    $wo_session_id = tep_session_id();
    $wo_ip_address = getenv('REMOTE_ADDR');
    $wo_last_page_url = getenv('REQUEST_URI');

    $current_time = time();
    $xx_mins_ago = ($current_time - 900);

    // remove entries that have expired
    tep_db_query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

    $stored_customer_query = tep_db_query("select count(*) as count from " . TABLE_WHOS_ONLINE . " where session_id = '" . tep_db_input($wo_session_id) . "'");
    $stored_customer = tep_db_fetch_array($stored_customer_query);

    $wo_last_page_url = substr($wo_last_page_url,0,255);

    if ($stored_customer['count'] > 0) {
      tep_db_query("update " . TABLE_WHOS_ONLINE . " set customer_id = '" . (int)$wo_customer_id . "', full_name = '" . tep_db_input($wo_full_name) . "', ip_address = '" . tep_db_input($wo_ip_address) . "', time_last_click = '" . tep_db_input($current_time) . "', last_page_url = '" . tep_db_input($wo_last_page_url) . "', platform_id = '" . (int)PLATFORM_ID . "' where session_id = '" . tep_db_input($wo_session_id) . "'");
    } else {
      tep_db_query("insert into " . TABLE_WHOS_ONLINE . " (customer_id, full_name, session_id, ip_address, time_entry, time_last_click, last_page_url, platform_id) values ('" . (int)$wo_customer_id . "', '" . tep_db_input($wo_full_name) . "', '" . tep_db_input($wo_session_id) . "', '" . tep_db_input($wo_ip_address) . "', '" . tep_db_input($current_time) . "', '" . tep_db_input($current_time) . "', '" . tep_db_input($wo_last_page_url) . "', '" . (int)PLATFORM_ID . "')");
    }
  }
 
}