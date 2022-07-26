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
    global $logger;
    if ((strstr($url, "\n") != false) || (strstr($url, "\r") != false)) {
        tep_redirect(tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));
    }
    header('Location: ' . $url);
    if (STORE_PAGE_PARSE_TIME == 'true') {
        if (!is_object($logger))
            $logger = new logger;
        $logger->timer_stop();
    }
    exit;
}

function tep_not_null($value) {
    if (is_array($value)) {
        if (sizeof($value) > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        if ((is_string($value) || is_int($value) || is_float($value) || is_bool($value) ) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
            return true;
        } else {
            return false;
        }
    }
}

function tep_admin_check_login() {
    global $navigation, $login_id, $device_hash;
    if (!tep_session_is_registered('login_id')) {
        if ( class_exists('\Yii') && \Yii::$app->request->isAjax ) {
            header('HTTP/1.1 401 Unauthorized');
            die;
        }
        if (is_object($navigation) && method_exists($navigation, 'set_snapshot')){
            $navigation->set_snapshot();
        }
        tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
    } else {
        if (ADMIN_MULTI_SESSION_ENABLED != 'true') {
            if ((int)\common\models\Admin::find()->where(['admin_id' => $login_id, 'device_hash' => trim($device_hash)])->count() <= 0) {
                if (!tep_session_is_registered('admin_multi_session_error')) {
                    $adminLoginLogRecord = new \common\models\AdminLoginLog();
                    $adminLoginLogRecord->all_event = 21;
                    $adminLoginLogRecord->all_device_id = $device_hash;
                    $adminLoginLogRecord->all_ip = '';
                    $adminLoginLogRecord->all_agent = '';
                    $adminLoginLogRecord->all_user_id = $login_id;
                    $adminLoginLogRecord->all_user = \common\models\AdminLoginLog::getAdminEmail($login_id);
                    $adminLoginLogRecord->all_date = date('Y-m-d H:i:s');
                    try {
                        $adminLoginLogRecord->save();
                    } catch (\Exception $exc) {}
                }
                tep_session_register('admin_multi_session_error', true);
                tep_redirect(tep_href_link(FILENAME_LOGOFF));
            }
        }
        if (\common\models\AdminLoginSession::checkAdminSession($login_id, $device_hash) != true) {
            tep_redirect(tep_href_link(FILENAME_LOGOFF));
        }
    }
}

function tep_call_function($function, $parameter, $object = '') {
    if ($object == '') {
        return call_user_func($function, $parameter);
    } else {
        return call_user_func(array($object, $function), $parameter);
    }
}

function convert($input){
    return \common\helpers\Seo::transliterate($input);
}

// Next two functions are used only in the admin for disallowed shipping options.
// The (short) constants like US_12, CAN_14 are stored in the database
// to stay below 255 characters. The defines themselves are found in the upsxml
// language file prefixed with UPSXML_ to avoid collisions with other shipping modules.
// They can be moved to admin/includes/function/general.php if you like but don't forget
// to remove them from this file in future updates or you will get an error in the admin
// about re-declaring functions
  function get_multioption_upsxml($values) {
    if (tep_not_null($values)) {
      $values_array = explode(',', $values);
      foreach ($values_array as $key => $_method) {
        if ($_method == '--none--') {
          $method = $_method;
        } else {
          $method = constant('UPSXML_' . trim($_method));
        }
        $readable_values_array[] = $method;
      }
      $readable_values = implode(', ', $readable_values_array);
      return $readable_values;
    } else {
      return '';
    }
  }

