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

namespace backend\components;

use Yii;
use \common\components\SessionFlow;
use \frontend\design\Info;

class AdminFactory {

    public static function init() {
        global $session_started, $request_type, $ssl_session_id, $SID;
        global $lng, $language, $languages_id, $breadcrumb, $navigation;
        global $PHP_SELF;

        /* init session from appication_top */

        if (($session_started == true) && function_exists('ini_get') && (ini_get('register_globals') == false || ini_get('register_globals') == "Off")) {
            if (is_array($_SESSION)) {
                extract($_SESSION, EXTR_OVERWRITE + EXTR_REFS);
                foreach ($_SESSION as $_key => $_item) {
                    $GLOBALS[$_key] = &$_SESSION[$_key];
                }
            }
        }

        // set SID once, even if empty
        $SID = (defined('SID') ? SID : '');

        // verify the ssl_session_id if the feature is enabled
        if (($request_type == 'SSL') && (SESSION_CHECK_SSL_SESSION_ID == 'True') && (ENABLE_SSL == true) && ($session_started == true)) {
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
        
        $lng = new \common\classes\language();

        if (!tep_session_is_registered('language') || isset($_GET['language'])) {
            if (!tep_session_is_registered('language')) {
                tep_session_register('language');
                tep_session_register('languages_id');
            }

            if (isset($_GET['language']) && tep_not_null($_GET['language'])) {
                $lng->set_language($_GET['language'], true);
            } else {
                $lng->get_browser_language();
            }

            $language = $lng->language['code'];
            $languages_id = $lng->language['id'];
            \Yii::$app->settings->set('locale', $lng->language['locale']);
            \Yii::$app->settings->set('languages_id', $languages_id);//preparing to switch  
        } else {
            $lng->set_language($language);
            \Yii::$app->settings->set('locale', $lng->language['locale']);
            \Yii::$app->settings->set('languages_id', $lng->language['id']);//preparing to switch  
        }
        $lng->set_locale();
        $lng->load_vars();

        $breadcrumb = new \common\classes\breadcrumb;

        // navigation history
        if (!tep_session_is_registered('navigation')) {
            tep_session_register('navigation');
            $navigation = new \common\classes\navigation();
        }
        if (is_object($navigation) && method_exists($navigation, 'add_current_page')) {
          $route = false;
          $tmp = Yii::$app->urlManager->parseRequest(Yii::$app->request);
          if (is_array($tmp) && !empty($tmp[0])){
            $route = $tmp[0];
          }
            if (!Yii::$app->request->isAjax 
                && $route && !(in_array($route, ['index/load-languages-js']))
                ) {
                $navigation->add_current_page();
            }
        }
        \common\helpers\Translation::init('admin/main');
        
        $messageStack = \Yii::$container->get('message_stack');

        if (defined('XML_DUMP_ENABLE') && XML_DUMP_ENABLE == "True") {
            $can_backup_xml = true;
            if (is_dir(DIR_FS_CATALOG_XML)) {
                if (!is_writeable(DIR_FS_CATALOG_XML)) {
                    $messageStack->add(ERROR_CATALOG_XML_DIRECTORY_NOT_WRITEABLE);
                    $can_backup_xml = false;
                }
            } else {
                $messageStack->add(ERROR_CATALOG_XML_DIRECTORY_DOES_NOT_EXIST);
                $can_backup_xml = false;
            }
            $backup_to_xml = ($can_backup_xml) ? TEXT_XML_BACKUP_POSSIBLE : TEXT_XML_BACKUP_IMPOSSIBLE;
        }

        // check if a default currency is set
        if (!defined('DEFAULT_CURRENCY')) {
            $messageStack->add(ERROR_NO_DEFAULT_CURRENCY_DEFINED);
        }

        // check if a default language is set
        if (!defined('DEFAULT_LANGUAGE')) {
            $messageStack->add(ERROR_NO_DEFAULT_LANGUAGE_DEFINED);
        }

        if (function_exists('ini_get') && ((bool) ini_get('file_uploads') == false)) {
            $messageStack->add(WARNING_FILE_UPLOADS_DISABLED, 'header', 'warning');
        }

        if (basename($PHP_SELF) != FILENAME_LOGIN && basename($PHP_SELF) != FILENAME_PASSWORD_FORGOTTEN && basename($PHP_SELF) != FILENAME_LOGOFF && basename($PHP_SELF) != 'password-forgotten-new-password' && basename($PHP_SELF) != 'captcha') {
            tep_admin_check_login();
        }

        return;
    }

}
