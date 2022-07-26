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

  if ( (bool)ini_get('register_globals') == false ) {
    @ini_set('session.bug_compat_42', 1);
    @ini_set('session.bug_compat_warn', 0);
  }
//  @ini_set('session.cookie_httponly', 1);
//  @ini_set('session.use_only_cookies', 1);
//  @ini_set('session.cookie_secure', 1);  
  
  if (STORE_SESSIONS == 'mysql') {
    if (!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime')) {
      $SESS_LIFE = 1440;
    }

    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {
        
      $sane_session_id = true;
      if (preg_match('/^[a-zA-Z0-9]+$/', $key) == false) {
        $sane_session_id = false;
      }
      
      if ($sane_session_id == false) {
          return false;
      }
      
      $value_query = tep_db_query("select value from " . TABLE_SESSIONS . " where sesskey = '" . tep_db_input($key) . "' and expiry > '" . time() . "'");
      $value = tep_db_fetch_array($value_query);

      if (isset($value['value'])) {
        return $value['value'];
      }

      return '';
    }

    function _sess_write($key, $val) {
      global $SESS_LIFE;

      $sane_session_id = true;
      if (preg_match('/^[a-zA-Z0-9]+$/', $key) == false) {
        $sane_session_id = false;
      }
      
      if ($sane_session_id == false) {
          return false;
      }
      
      $expiry = time() + $SESS_LIFE;
      $value = $val;

      $check_query = tep_db_query("select count(*) as total from " . TABLE_SESSIONS . " where sesskey = '" . tep_db_input($key) . "'");
      $check = tep_db_fetch_array($check_query);

      if ($check['total'] > 0) {
        return tep_db_query("update " . TABLE_SESSIONS . " set expiry = '" . tep_db_input($expiry) . "', value = '" . tep_db_input($value) . "' where sesskey = '" . tep_db_input($key) . "'");
      } else {
        return tep_db_query("insert ignore into " . TABLE_SESSIONS . " values ('" . tep_db_input($key) . "', '" . tep_db_input($expiry) . "', '" . tep_db_input($value) . "')");
      }
    }

    function _sess_destroy($key) {
      return tep_db_query("delete from " . TABLE_SESSIONS . " where sesskey = '" . tep_db_input($key) . "'");
    }

    function _sess_gc($maxlifetime) {
      tep_db_query("delete from " . TABLE_SESSIONS . " where expiry < '" . time() . "'");

      return true;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
  }

  function tep_session_start($force = false) {
    $sane_session_id = true;

    if (isset($_GET[tep_session_name()])) {
      if (preg_match('/^[a-zA-Z0-9]+$/', $_GET[tep_session_name()]) == false) {
        unset($_GET[tep_session_name()]);

        $sane_session_id = false;
      }
    } elseif (isset($_POST[tep_session_name()])) {
      if (preg_match('/^[a-zA-Z0-9]+$/', $_POST[tep_session_name()]) == false) {
        unset($_POST[tep_session_name()]);

        $sane_session_id = false;
      }
    } elseif (isset($_COOKIE[tep_session_name()])) {
      if (preg_match('/^[a-zA-Z0-9]+$/', $_COOKIE[tep_session_name()]) == false || $force) {
        $session_data = session_get_cookie_params();

        setcookie(tep_session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);

        $sane_session_id = false;
      }
    }

    if ($sane_session_id == false) {
        if (defined('ENABLE_SSL') && ENABLE_SSL == true) {
            $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
        } else {
            $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
        }
        tep_redirect($link);
    }

    register_shutdown_function('session_write_close');
    
    return session_start();
  }

  function tep_session_register($variable) {
    global $session_started;

    if ($session_started == true) {
        $_SESSION[$variable] =& $GLOBALS[$variable];
    }

    return false;
  }

  function tep_session_is_registered($variable) {
      return isset($_SESSION) && array_key_exists($variable, $_SESSION);
  }

  function tep_session_unregister($variable) {
      unset($_SESSION[$variable]);
  }

  function tep_session_id($sessid = '') {
    if (!empty($sessid)) {
      return session_id($sessid);
    } else {
      return session_id();
    }
  }

  function tep_session_name($name = '') {
    if (!empty($name)) {
      return session_name($name);
    } else {
      return session_name();
    }
  }

  function tep_session_close() {
      return @session_write_close();
  }

  function tep_session_destroy() {
    return @session_destroy();
  }

  function tep_session_save_path($path = '') {
    if (!empty($path)) {
      return session_save_path($path);
    } else {
      return session_save_path();
    }
  }

  function tep_session_recreate() {
      $session_backup = $_SESSION;

      unset($_COOKIE[tep_session_name()]);

      tep_session_destroy();

      if (STORE_SESSIONS == 'mysql') {
        session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
      }

      tep_session_start();

      $_SESSION = $session_backup;
      unset($session_backup);
  }

