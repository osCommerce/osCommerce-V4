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
      
      $value_query = tep_db_query("select value from " . TABLE_SESSIONS_ADMIN . " where sesskey = '" . tep_db_input($key) . "' and expiry > '" . time() . "'");
      $value = tep_db_fetch_array($value_query);

      if (isset($value['value'])) {
        return $value['value'];
      }

      return '';
    }

    function tep_db_input_session($string, $link = 'db_link') {
      global $$link;

      if (function_exists('mysqli_real_escape_string')) {
        return mysqli_real_escape_string($$link,$string??'');
      } elseif (function_exists('mysqli_escape_string')) {
        return mysqli_escape_string($$link,$string);
      }

      return addslashes($string);
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

      $check_query = tep_db_query("select count(*) as total from " . TABLE_SESSIONS_ADMIN . " where sesskey = '" . tep_db_input($key) . "'");
      $check = tep_db_fetch_array($check_query);

      if ($check['total'] > 0) {
        return tep_db_query("update " . TABLE_SESSIONS_ADMIN . " set expiry = '" . tep_db_input($expiry) . "', value = '" . tep_db_input_session($value) . "' where sesskey = '" . tep_db_input($key) . "'");
      } else {
        return tep_db_query("insert ignore into " . TABLE_SESSIONS_ADMIN . " values ('" . tep_db_input($key) . "', '" . tep_db_input($expiry) . "', '" . tep_db_input_session($value) . "')");
      }
    }

    function _sess_destroy($key) {
      return tep_db_query("delete from " . TABLE_SESSIONS_ADMIN . " where sesskey = '" . tep_db_input($key) . "'");
    }

    function _sess_gc($maxlifetime) {
      tep_db_query("delete from " . TABLE_SESSIONS_ADMIN . " where expiry < '" . time() . "'");

      return true;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
  }

  function tep_session_start() {
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
      if (preg_match('/^[a-zA-Z0-9]+$/', $_COOKIE[tep_session_name()]) == false) {
        $session_data = session_get_cookie_params();

        setcookie(tep_session_name(), '', time()-42000, $session_data['path'], $session_data['domain']);

        $sane_session_id = false;
      }
    }

    if ($sane_session_id == false) {
      tep_redirect(tep_href_link('index', '', 'NONSSL', false));
    }

    register_shutdown_function('session_write_close');

    return session_start();
  }

  function tep_session_register($variable, $value = '') {
    if (!empty($value)) {
      $_SESSION[$variable] =  $value;
    } else {
      $_SESSION[$variable] =& $GLOBALS[$variable];
    }
    return false;
  }

  function tep_session_var($variable) {
      return $_SESSION[$variable];
  }
  
  function tep_session_is_registered($variable) {
      return isset($_SESSION) && array_key_exists($variable, $_SESSION);
  }
  
  function tep_session_unregister($variable) {
      unset($_SESSION[$variable]);
  }

  function tep_session_id($sessid = '') {
    if ($sessid != '') {
      return session_id($sessid);
    } else {
      return session_id();
    }
  }

  function tep_session_name($name = '') {
    if ($name != '') {
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
    if ($path != '') {
      return session_save_path($path);
    } else {
      return session_save_path();
    }
  }
