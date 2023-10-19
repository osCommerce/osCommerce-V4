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

namespace common\helpers;

use Yii;

class Acl
{
  public const ACL_CACHE_LIFETIME = 5;
  const DEVICE_HASH_SOURCES = [
                                'HTTP_USER_AGENT'/*, 'HTTP_ACCEPT'*/, 'HTTP_ACCEPT_LANGUAGE'
                              ];
  //const COOKIE_SALT = '__tladhs'; //doesn't work now cross domain cookies impossible

    protected static function getAdminAclData($login_id)
    {
        static $lastData = [];
        if ( !isset($lastData[(int)$login_id]) ){
            $lastData[(int)$login_id] = false;
            $checkAdmin = tep_db_query("select access_levels_id, admin_persmissions from " . TABLE_ADMIN . " where admin_id = '" . (int)$login_id . "'");
            if ( tep_db_num_rows($checkAdmin)>0 ) {
                $admin = tep_db_fetch_array($checkAdmin);
                $admin['admin_persmissions'] = explode(",", $admin['admin_persmissions'] ?? '');
                $lastData[(int)$login_id] = $admin;
            }
        }
        return $lastData[(int)$login_id];
    }

    protected static function getAccessLevelData($access_levels_id)
    {
        static $lastData = [];
        if ( !isset($lastData[(int)$access_levels_id]) ) {
            $lastData[(int)$access_levels_id] = false;

            $checkAccess = tep_db_query("SELECT access_levels_persmissions FROM " . TABLE_ACCESS_LEVELS . " WHERE access_levels_id = '" . (int)$access_levels_id . "'");
            $access = tep_db_fetch_array($checkAccess);
            $lastData[(int)$access_levels_id] = explode(",", $access['access_levels_persmissions']);
        }
        return $lastData[(int)$access_levels_id];
    }

    public static function rule($rules, $rootId = 0, $selectedIds = '', $admin_id = false)
    {
        global $login_id;
        
        if (empty($admin_id)) {
          $admin_id = $login_id;
        }

        if (is_string($rules)) {
            $rules = [
                $rules,
            ];
        }

        if (is_string($selectedIds)) {
            if (empty($selectedIds)) {
                $admin = static::getAdminAclData($admin_id);
                if (!is_array($admin)) {
                    return false;
                }
                $adminPersmissions = $admin['admin_persmissions'];

                $selectedIds = static::getAccessLevelData((int)$admin['access_levels_id']);

                if (count($adminPersmissions) > 0) {
                    foreach ($adminPersmissions as $permissionIs) {
                        if (empty($permissionIs)) {
                            continue;
                        }
                        if ($permissionIs > 0) {
                            if (!in_array($permissionIs, $selectedIds)) {
                                $selectedIds[] = $permissionIs;//add to list
                            }
                        } elseif ($permissionIs < 0) {
                            if (false !== ($key = array_search(abs($permissionIs), $selectedIds)) ) {
                                unset($selectedIds[$key]);//remove from list
                            }
                        }
                    }
                }
            } else {
                $selectedIds = explode(",", $selectedIds);
            }
        }

        $currentKey = array_shift($rules);

        static $acl_list = false;
        if ( !is_array($acl_list) ) {
            $acl_list = [];
            $preload_r = tep_db_query("select access_control_list_id, access_control_list_key, parent_id from " . TABLE_ACCESS_CONTROL_LIST . "");
            if ( tep_db_num_rows($preload_r)>0 ) {
                while ( $preload = tep_db_fetch_array($preload_r) ){
                    $key = (int)$preload['parent_id'].'@'.(string)$preload['access_control_list_key'];
                    $acl_list[$key] = $preload['access_control_list_id'];
                }
            }
        }

        $key = (int)$rootId.'@'.(string)$currentKey;
        if ( !isset($acl_list[$key]) ) {
            $checkQuery = tep_db_query("select access_control_list_id from " . TABLE_ACCESS_CONTROL_LIST . " where access_control_list_key = '" . tep_db_input($currentKey) . "' and parent_id = '" . (int)$rootId . "'");
            if (tep_db_num_rows($checkQuery) > 0) {
                $check = tep_db_fetch_array($checkQuery);
                $acl_list[$key] = $check['access_control_list_id'];
            }
        }
        if ( isset($acl_list[$key]) ) {
            $currentId = $acl_list[$key];
        } else {
            if (defined('DISABLE_NEW_ACL') && DISABLE_NEW_ACL == 1) {
                return false;
            }
            if (empty($currentKey)) {
                return true;
            }
            $sql_data_array = [
                'parent_id' => (int)$rootId,
                'access_control_list_key' => $currentKey,
            ];
            tep_db_perform(TABLE_ACCESS_CONTROL_LIST, $sql_data_array);
            $currentId = tep_db_insert_id();
        }

        if (count($rules) > 0) {
            $response = self::rule($rules, $currentId, $selectedIds);
        } else {
            $response = true;
        }

        if (!in_array($currentId, $selectedIds)) {
            $response = false;
        }

        return $response;
    }

    public static function buildTree($selectedIds = '', $rootId = 0)
    {
        $response = [];
        if (is_string($selectedIds)) {
            $selectedIds = explode(",", $selectedIds);
        }
        if (!is_array($selectedIds)) {
            $selectedIds = [];
        }

        $accessQuery = tep_db_query("select * from " . TABLE_ACCESS_CONTROL_LIST . " where parent_id = '" . $rootId . "' order by sort_order");
        while ($access = tep_db_fetch_array( $accessQuery )) {
            $currentId = $access['access_control_list_id'];
            if (defined($access['access_control_list_key'])) {
                eval('$currentName =  ' . $access['access_control_list_key'] . ';');
            } else {
                $currentName = $access['access_control_list_key'];
            }

            /*if (in_array($currentId, $selectedIds)) {
                $child = self::buildTree($selectedIds, $currentId);
            } else {
                $child = [];
            }*/
            $child = self::buildTree($selectedIds, $currentId);
            $response[] = [
                'id' => $currentId,
                'text' => $currentName,
                'selected' => in_array($currentId, $selectedIds),
                'child' => $child,
            ];
        }


        return $response;
    }

    public static function buildTreePDO($selectedIds = '', $rootId = 0)
    {
        $response = [];
        if (is_string($selectedIds)) {
            $selectedIds = explode(",", $selectedIds);
        }
        if (!is_array($selectedIds)) {
            $selectedIds = [];
        }

        $accessQuery = \common\models\PDOConnector::query("select * from " . TABLE_ACCESS_CONTROL_LIST . " where parent_id = '" . $rootId . "' order by sort_order");
        while ($access = \common\models\PDOConnector::fetch( $accessQuery )) {
            $currentId = $access['access_control_list_id'];
            if (defined($access['access_control_list_key'])) {
                eval('$currentName =  ' . $access['access_control_list_key'] . ';');
            } else {
                $currentName = $access['access_control_list_key'];
            }
            /*if (in_array($currentId, $selectedIds)) {
                $child = self::buildTreePDO($selectedIds, $currentId);
            } else {
                $child = [];
            }*/
            $child = self::buildTreePDO($selectedIds, $currentId);
            $response[] = [
                'id' => $currentId,
                'text' => $currentName,
                'selected' => in_array($currentId, $selectedIds),
                'child' => $child,
            ];
        }


        return $response;
    }

    public static function buildOverrideTree($selectedIds = '', $adminPersmissions = '', $rootId = 0)
    {
        $response = [];

        if (is_string($selectedIds)) {
            $selectedIds = explode(",", $selectedIds);
        }
        if (is_string($adminPersmissions)) {
            $adminPersmissions = explode(",", $adminPersmissions);
        }

        $accessQuery = tep_db_query("select * from " . TABLE_ACCESS_CONTROL_LIST . " where parent_id = '" . $rootId . "' order by sort_order");
        while ($access = tep_db_fetch_array( $accessQuery )) {
            $currentId = $access['access_control_list_id'];
            if (defined($access['access_control_list_key'])) {
                eval('$currentName =  ' . $access['access_control_list_key'] . ';');
            } else {
                $currentName = $access['access_control_list_key'];
            }

            $showChilds = true;
            $selected = 0;
            if (in_array($currentId, $selectedIds)) {
                if (in_array(($currentId*-1), $adminPersmissions)) {
                    $currentName = '<font color="red">' . $currentName . '</font>';//red - removed
                    $showChilds = false;
                    $selected = 0;
                } else {
                    //normal mode
                    $showChilds = true;
                    $selected = 1;
                }
            } elseif (in_array($currentId, $adminPersmissions)) {
                $currentName = '<font color="green">' . $currentName . '</font>';//green - added
                $showChilds = true;
                $selected = 1;
            } else {
                //normal mode
                $showChilds = false;
                $selected = 0;
            }

            /*if ($showChilds) {
                $child = self::buildOverrideTree($selectedIds, $adminPersmissions, $currentId);
            } else {
                $child = [];
            }*/
            $child = self::buildOverrideTree($selectedIds, $adminPersmissions, $currentId);
            $response[] = [
                'id' => $currentId,
                'text' => $currentName,
                'selected' => $selected,
                'child' => $child,
            ];
        }

        return $response;
    }

    public static function buildOverrideTreePDO($selectedIds = '', $adminPersmissions = '', $rootId = 0)
    {
        $response = [];

        if (is_string($selectedIds)) {
            $selectedIds = explode(",", $selectedIds);
        }
        if (is_string($adminPersmissions)) {
            $adminPersmissions = explode(",", $adminPersmissions);
        }

        $accessQuery = \common\models\PDOConnector::query("select * from " . TABLE_ACCESS_CONTROL_LIST . " where parent_id = '" . $rootId . "' order by sort_order");
        while ($access = \common\models\PDOConnector::fetch( $accessQuery )) {
            $currentId = $access['access_control_list_id'];
            if (defined($access['access_control_list_key'])) {
                eval('$currentName =  ' . $access['access_control_list_key'] . ';');
            } else {
                $currentName = $access['access_control_list_key'];
            }

            $showChilds = true;
            $selected = 0;
            if (in_array($currentId, $selectedIds)) {
                if (in_array(($currentId*-1), $adminPersmissions)) {
                    $currentName = '<font color="red">' . $currentName . '</font>';//red - removed
                    $showChilds = false;
                    $selected = 0;
                } else {
                    //normal mode
                    $showChilds = true;
                    $selected = 1;
                }
            } elseif (in_array($currentId, $adminPersmissions)) {
                $currentName = '<font color="green">' . $currentName . '</font>';//green - added
                $showChilds = true;
                $selected = 1;
            } else {
                //normal mode
                $showChilds = false;
                $selected = 0;
            }

            /*if ($showChilds) {
                $child = self::buildOverrideTree($selectedIds, $adminPersmissions, $currentId);
            } else {
                $child = [];
            }*/
            $child = self::buildOverrideTreePDO($selectedIds, $adminPersmissions, $currentId);
            $response[] = [
                'id' => $currentId,
                'text' => $currentName,
                'selected' => $selected,
                'child' => $child,
            ];
        }

        return $response;
    }

    public static function checkAccess($rules) {
        if (false == \common\helpers\Acl::rule($rules)) {
            die('Access denied.');
        }
    }

    const extPath = '\\common\\extensions\\';

    /**
     *
     * @param type $class
     * @param type $method = null - check to be sure to use extension classes
     * @param type $own_method
     * @return boolean
     */
    public static function checkExtension($class, $method = null, $own_method = false) {
        $path = $class;
        if (strpos($class, '\\')){
            $parts = explode('\\', $class);
            $class = $parts[sizeof($parts)-1];
            $path = implode('\\', $parts);
        }
        if (!class_exists(self::extPath . $path . '\\' . $class)) {
            return false;
        }
        if (empty($method)) {
            return true;
        }
        if (!method_exists(self::extPath . $path . '\\' . $class, $method)) {
            return false;
        }

        if ($own_method){
            $ref = new \ReflectionClass(self::extPath . $path . '\\' . $class);
            if ($ref->hasMethod($method)){
                $_method = $ref->getMethod($method);
                if ($_method->class == 'yii\base\Widget') return false;
            }
            if (!$ref->hasMethod($method)) return false;
        }

        return self::extPath . $path . '\\' . $class;
    }

    public static function checkExtensionInstalled($class, $method = null, $own_method = false){
        $ext = self::checkExtension($class, $method, $own_method);
        if (!$ext) return false;

        if (strpos($class, '\\')){
            $parts = explode('\\', $class);
            $class = $parts[sizeof($parts)-1];
        }
        return \common\helpers\Extensions::isEnabled($class) ? $ext : false;
    }

    public static function checkExtensionAllowed($class, $method = 'allowed', $own_method = false){
        if ($extension = self::checkExtension($class, $method, $own_method)){
            if (call_user_func([$extension, $method])){
                return $extension;
            }
        }
        return false;
    }

    /**
     * @param string $class className of extension
     * @param string $relativeModelName 'models\Collections' or just 'Collections'
     * @param string|null $allowedFunc
     * @return \yii\db\ActiveRecord|null
     */
    public static function checkExtensionTableExist($class, $relativeModelName, $allowedFunc = 'allowed')
    {
        return \common\helpers\Extensions::getModel($class, $relativeModelName, $allowedFunc);
    }

    /**
     * @param string $relativeClassName 'helpers\Collections'
     */
    public static function checkExtensionAllowedClass($class, $relativeClassName)
    {
        if ($ext = self::checkExtensionAllowed($class)) {
            $className = $ext . "\\$relativeClassName";
            if (class_exists($className)) {
                return $className;
            }
        }
    }

    public static function get($class) {
        return self::extPath . $class . '\\' . $class;
    }

    public static function getExtensionPages(){
        $pages = [];
        $extensioins = new \DirectoryIterator(Yii::$aliases['@common'] . '/extensions/');
        foreach($extensioins as $ext){
            $class = $ext->getFilename();
            if (($_w = self::checkExtension($class, 'getPages')) && $_w::allowed()){
                $_pages = $_w::getPages();
                if (is_array($_pages) && count($_pages)){
                    foreach($_pages as $wd){
                        $pages[] = $wd;
                    }
                }
                if (!is_array($pages)) $pages = [];
            }
        }
        return $pages;
    }

    public static function getExtensionActions($controllerId){
        $actions = [];
        $extensioins = new \DirectoryIterator(Yii::$aliases['@common'] . '/extensions/');
        foreach($extensioins as $ext){
            $class = $ext->getFilename();
            if ($_w = self::checkExtension($class, 'getControllerActions')){
                $_actions = $_w::getControllerActions($controllerId);
                if (is_array($_actions) && count($_actions)>0){
                    $actions = array_merge($actions, $_actions);
                }
                if (!is_array($actions)) $actions = [];
            }
        }
        return $actions;
    }

    public static function getExtensionPageTypes(){
        $pages = [];
        $extensioins = new \DirectoryIterator(Yii::$aliases['@common'] . '/extensions/');
        foreach($extensioins as $ext){
            $class = $ext->getFilename();
            if ($_w = self::checkExtension($class, 'getPageTypes')){
                $_pages = $_w::getPageTypes();
                if (is_array($_pages) && count($_pages)){
                    foreach($_pages as $wd){
                        $pages[] = $wd;
                    }
                }
                if (!is_array($pages)) $pages = [];
            }
        }
        return $pages;
    }

    public static function getExtensionWidgets($type){
        $widgets = [];
        $extensioins = new \DirectoryIterator(Yii::$aliases['@common'] . '/extensions/');
        foreach($extensioins as $ext){
            $class = $ext->getFilename();
            if (($_w = self::checkExtension($class, 'getWidgets')) && $_w::allowed()){
                $_widgets = $_w::getWidgets($type);
                if (is_array($_widgets) && count($_widgets)){
                    foreach($_widgets as $wd){
                        if (empty($wd['type'])) {
                            $wd['type'] = 'general';
                        }
                        $widgets[] = $wd;
                    }
                }
                if (!is_array($widgets)) $widgets = [];
            }
        }
        return $widgets;
    }

    public static function getExtensionEpDataSources()
    {
        $widgets = [];
        $extensioins = new \DirectoryIterator(Yii::$aliases['@common'] . '/extensions/');
        foreach($extensioins as $ext){
            $class = $ext->getFilename();
            if (!self::checkExtensionAllowed($class)) continue;
            if ($_w = self::checkExtension($class, 'getEpDataSources')){
                $_widgets = $_w::getEpDataSources();
                if (is_array($_widgets)) {
                    $widgets = array_merge($widgets, $_widgets);
                }
            }
        }
        return $widgets;
    }

    public static function getExtensionEpProviders()
    {
        $widgets = [];
        $extensioins = new \DirectoryIterator(Yii::$aliases['@common'] . '/extensions/');
        foreach($extensioins as $ext){
            $class = $ext->getFilename();
            if (!self::checkExtensionAllowed($class, 'enabled')) continue;
            if ($_w = self::checkExtension($class, 'getEpProviders')){
                $_widgets = $_w::getEpProviders();
                if (is_array($_widgets)) {
                    $widgets = $widgets + $_widgets;
                }
            }
        }
        return $widgets;
    }

    public static function applyExtensionMetaTags($meta_tags){
        $extensions = new \DirectoryIterator(Yii::$aliases['@common'] . '/extensions/');
        foreach($extensions as $ext){
            $class = $ext->getFilename();
            if ($_w = self::checkExtension($class, 'getMetaTagKeys')){
                $_applied_tags = $_w::getMetaTagKeys($meta_tags);
                if (is_array($_applied_tags) && count($_applied_tags)>0){
                    $meta_tags = $_applied_tags;
                }
            }
        }
        return $meta_tags;
    }

    public static function getExtensionCreateImagesSettings(){
        $settings = [];
        $extensioins = new \DirectoryIterator(Yii::$aliases['@common'] . '/extensions/');
        foreach($extensioins as $ext){
            $class = $ext->getFilename();
            if ($_w = self::checkExtension($class, 'getCreateImagesSettings')){
                $_settings = $_w::getCreateImagesSettings();
                if (is_array($_settings) && count($_settings)){
                    foreach($_settings as $wd){
                        $settings[] = $wd;
                    }
                }
                if (!is_array($settings)) $settings = [];
            }
        }
        return $settings;
    }

    private static function _getDeviceHashString() {
      $str = '';
      foreach(self::DEVICE_HASH_SOURCES as $source) {
        if (isset($_SERVER[$source])) {
          $str .= $_SERVER[$source];
        }
      }
      $str .= \common\helpers\System::get_ip_address();
      return $str;
    }

    public static function saveManagerDeviceHash($loginId) {
      $str = '';

//2DO problem with intranet devices as X_FORWARDED_FOR etc often is not set
      /* and cookies couldn't be set / checked for another domain.
      if (function_exists('random_int')) {
        $salt = random_int(1000000, 9999999);
      } else {
        $salt = rand(1000000, 9999999);
      }

      $q = tep_db_query( "select p.* from platforms p where p.status=1 and p.is_virtual=0" );
      while ($platform = tep_db_fetch_array($q)) {
        $parsed = parse_url('http://' . $platform['platform_url'] . '/');
        \common\helpers\System::setcookie(self::COOKIE_SALT, $salt, 0, '/', $parsed['host']);
      }
      $tmp = \common\helpers\System::getcookie(self::COOKIE_SALT);
      if ($tmp == $salt) {
        $str .= $salt;
      }*/


      $str .= self::_getDeviceHashString();
      $str = md5($str);
      tep_db_query("update " . TABLE_ADMIN . " set device_hash = '" . tep_db_input($str) . "' where admin_id = '" . (int) $loginId . "'");
      return $str;
    }

    public static function checkRuleByDeviceHash($rule) {
      $ret = false;
      $str = '';
      //$str = \common\helpers\System::getcookie(self::COOKIE_SALT);
      $str .= self::_getDeviceHashString();
// not defined on frontend " . TABLE_ADMIN . "
      $checkAdmin = tep_db_query("select a.admin_persmissions, al.access_levels_persmissions from admin a join " . TABLE_ACCESS_LEVELS . " al on a.access_levels_id=al.access_levels_id where device_hash = '" . tep_db_input(md5($str))  . "' order by admin_logdate DESC");
      if (tep_db_num_rows($checkAdmin) > 0) {
        // admin's permissions override AL permissions (>0 allow <0 forbid)
        $admin = tep_db_fetch_array($checkAdmin);
        $ALIds = explode(",", $admin['access_levels_persmissions']);
        $adminPersmissions = (!empty($admin['admin_persmissions'])) ? explode(",", $admin['admin_persmissions']) : [];
        $a = $r = [];
        foreach ($adminPersmissions as $v) {
            if (!empty($v)) {
                if ($v > 0) {
                  $a[] = $v;
                } else {
                  $r[] = abs($v);
                }
            }
        }
        $ruleIds = array_diff(array_merge($ALIds, $a), $r);
        $ret = self::rule($rule, 0, $ruleIds);
      }
      return $ret;

    }

    /*
    * run extension widget
    */
    public static function runExtensionWidget($widgeName, $widgetArray){
        if (($ext_widget = self::checkExtension($widgeName, 'run', true)) && (!method_exists($ext_widget, 'allowed') || $ext_widget::allowed())){
            $widgetArray = array_merge($widgetArray, ['name' => $widgeName]);
            return $ext_widget::widget($widgetArray);
        } else {
            return false;
        }
    }

    public static function isFrontendTranslation()
    {
        static $status;

        if ( !is_bool($status) && \Yii::$app instanceof \yii\console\Application ){
            $status = false;
        }

        if (isset($status)) return $status;

        if (defined("DIR_WS_ADMIN")) {
            $status = false;
            return false;
        }

        $admin = \common\models\Admin::find()
            ->where(['device_hash' => md5(self::_getDeviceHashString()), 'frontend_translation' => '1'])
            ->cache(self::ACL_CACHE_LIFETIME)
            ->asArray()->one();
        if (!$admin) {
            $status = false;
            return false;
        }

        $cookies = Yii::$app->request->cookies;
        if ($admin && $cookies->has('frontend_translation')) {
            return true;
        }

        if (!strpos(Yii::$app->request->headers['referer'], '/admin/texts')){
            return false;
        }

        if (\common\helpers\Acl::checkRuleByDeviceHash(['BOX_HEADING_DESIGN_CONTROLS', 'BOX_TRANSLATION_TEXTS'])) {
            $status = true;
        } else {
            $status = false;
        }

        if ($status) {
            $accessLevels = \common\models\AccessLevels::find()
                ->where(['access_levels_id' => $admin['access_levels_id']])
                ->asArray()->one();

            $cookies = Yii::$app->response->cookies;
            $cookies->add(new \yii\web\Cookie([
                'name' => 'frontend_translation',
                'value' => $accessLevels['access_levels_persmissions'],
                'expire' => time() + 300,
            ]));
        }

        return $status;
    }

}
