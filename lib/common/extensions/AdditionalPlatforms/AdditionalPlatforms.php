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

namespace common\extensions\AdditionalPlatforms;

use Yii;

// Additional Platforms
class AdditionalPlatforms extends \common\classes\modules\ModuleExtensions {

    public static function getDescription() {
        return 'This extension allows to use more than one sales channel.';
    }
    
    public static function allowed() {
        return self::enabled();
    }
    
    public static function configure() {
        if (!self::allowed()) {
            return '';
        }
        global $request_type;
        $REQUEST_PLATFORM_URL = rtrim($_SERVER['HTTP_HOST'].'/'.trim(dirname($_SERVER['SCRIPT_NAME']),'/'),'/');
        $platform = false;
        if (isset($_GET['platform_id']) && $_GET['platform_id'] > 0) {
            $platform = tep_db_fetch_array(tep_db_query(
                            "select *, 0 AS _platform_cdn_server, IF(LENGTH(platform_url_secure)>0,platform_url_secure,platform_url) AS _platform_url_secure from platforms " .
                            "where platform_id='" . (int) $_GET['platform_id'] . "' " .
                            "LIMIT 1 "
            ));
        }

        if (!is_array($platform)) {
            $_search_platform_url = 'p.platform_url';
            if ($request_type == 'SSL') {
                $_search_platform_url = 'IF(LENGTH(p.platform_url_secure)>0,p.platform_url_secure,p.platform_url)';
            }
            $platform = tep_db_fetch_array(tep_db_query(
                "select p.*, " .
                " IF(pu.url IS NULL,0,1) AS _platform_cdn_server, ".
                " IF(LENGTH(p.platform_url_secure)>0,p.platform_url_secure,p.platform_url) AS _platform_url_secure ".
                "from (select pp.* from platforms pp where pp.status=1 or pp.is_default=1 ) p " .
                " left join platforms_url pu ON pu.platform_id=p.platform_id AND pu.url!={$_search_platform_url} AND pu.url = '" . tep_db_input($REQUEST_PLATFORM_URL.'/') . "' AND pu.status=1 ".
                "where p.is_default=1 " .
                " OR ({$_search_platform_url} = '" . tep_db_input($REQUEST_PLATFORM_URL) . "' OR {$_search_platform_url} = 'www." . tep_db_input($REQUEST_PLATFORM_URL) . "') " .
                "ORDER BY " .
                " IF({$_search_platform_url} = '" . tep_db_input($REQUEST_PLATFORM_URL) . "',0,1), " .
                " IF({$_search_platform_url} = 'www." . tep_db_input($REQUEST_PLATFORM_URL) . "',0,1) " .
                "LIMIT 1 "
            ));
        }

        return $platform;
    }
    
    public static function getSattelite($code) {
        if (!self::allowed()) {
            return '';
        }
        return tep_db_fetch_array(tep_db_query(
                            "select sattelite_platform_id, platform_id from platforms ".
                            " where platform_code='" . tep_db_input($code) . "' and status = 1 " .
                            "LIMIT 1 "
            ));
    }

    public static function setSattelite($code) {
        if (!self::allowed()) {
            return '';
        }
        if ($sattelite = self::getSattelite($code)) {
            if (!tep_session_is_registered('platform_sattelite')){
                tep_session_register ('platform_sattelite');
            }
            if (!tep_session_is_registered('platform_code')) {
                tep_session_register('platform_code'); 
            }
            global $platform_sattelite, $platform_code;
            $platform_sattelite = $_SESSION['platform_sattelite'] = $sattelite['platform_id'];
            $platform_code = $_SESSION['platform_code'] = $code;
            $cookies = Yii::$app->response->cookies;
            $lifetime = 3600;
            if (defined('AFFILIATE_COOKIE_LIFETIME')) {
                $lifetime = (int)AFFILIATE_COOKIE_LIFETIME;
            }
            $cookies->add(new \yii\web\Cookie([
                'name' => 'platform_sattelite',
                'value' => (int)$platform_sattelite,
                'expire' => time()+$lifetime,
            ]));
            $cookies->add(new \yii\web\Cookie([
                'name' => 'platform_code',
                'value' => (string)$platform_code,
                'expire' => time()+$lifetime,
            ]));
        }
    }
    
    public static function checkSattelite() {
        if (!self::allowed()) {
            return '';
        }
        //get cookie start
        global $platform_sattelite, $platform_code;
        if (! (Yii::$app instanceof \yii\console\Application) ) {
          $cookies = Yii::$app->request->cookies;
          $platform_sattelite = $_SESSION['platform_sattelite'] = (int)$cookies->getValue('platform_sattelite', '');
          $platform_code = $_SESSION['platform_code'] = (string)$cookies->getValue('platform_code', '');
        }
        return isset($_SESSION['platform_sattelite']) && (int)$_SESSION['platform_sattelite'];
    }
    
    public static function getSatteliteId() {
        if (!self::allowed()) {
            return '';
        }
        if (($_SESSION['platform_sattelite']) && (int)$_SESSION['platform_sattelite']){
            return (int)$_SESSION['platform_sattelite'];
        }
        return false;
    }
    
    public static function getVirtualSattelitId($virtual_platform_id) {
        if (!self::allowed()) {
            return '';
        }
        $sattelite = tep_db_fetch_array(tep_db_query("select sattelite_platform_id from " . TABLE_PLATFORMS . " where platform_id = '" .(int)$virtual_platform_id. "' and is_virtual = 1"));
        if ($sattelite){
            return $sattelite['sattelite_platform_id'];
        }
        return false;
    }

    public static function index() {
        if (!self::allowed()) {
            return '';
        }
        Yii::$app->controller->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('platforms/edit').'" class="create_item addprbtn"><i class="icon-tag"></i>'.TEXT_CREATE_NEW_PLATFORM.'</a>';
    }
    
    public static function edit() {
        if (!self::allowed()) {
            return '';
        }
        if (Yii::$app->request->isPost) {
            $item_id = (int) Yii::$app->request->post('id');
        } else {
            $item_id = (int) Yii::$app->request->get('id');
        }
        return $item_id;
    }
    
    public static function save($item_id, $planguages, $pcurrencies, $sql_data_array, &$message) {
        if (!self::allowed()) {
            return '';
        }
        if ($item_id > 0) {
            // Update
            $message = "Platform updated";
            $sql_data_array['last_modified'] = 'now()';
            tep_db_perform(TABLE_PLATFORMS, $sql_data_array, 'update', "platform_id = '" . (int)$item_id . "'");
            $platform_updated = true;
        } else {
            // Insert
            $message = "Platform inserted";
            $sql_data_array['date_added'] = 'now()';
            if (count($planguages) == 0) {
                $sql_data_array['defined_languages'] = DEFAULT_LANGUAGE;
            }
            if (empty($sql_data_array['default_language'])
                && preg_match('/^[^\s,]+/', $sql_data_array['defined_languages'], $def)) {
                $sql_data_array['default_language'] = $def[0];
            }

            if (count($pcurrencies) == 0) {
                $sql_data_array['defined_currencies'] = DEFAULT_CURRENCY;
            }
            if (empty($sql_data_array['default_currency'])
                && preg_match('/^[^\s,]+/', $sql_data_array['defined_currencies'], $def)) {
                $sql_data_array['default_currency'] = $def[0];
            }

            tep_db_perform(TABLE_PLATFORMS, $sql_data_array);
            $item_id = tep_db_insert_id();
        }
        return $item_id;
    }

    public static function switchStatus() {
        if (!self::allowed()) {
            return '';
        }
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        tep_db_query("UPDATE " . TABLE_PLATFORMS . " SET status='" . ($status == 'true' ? 1 : 0) . "' WHERE platform_id='".(int)$id."'");        
        $defPlatformId = \common\classes\platform::defaultId();
        if ($id != $defPlatformId){
            if (defined('PLATFORM_OWN_PRICE') && PLATFORM_OWN_PRICE == 'true'){
                \common\models\PlatformsSettings::updateAll(['use_owner_prices' => $defPlatformId], ['use_owner_prices' => $id]);
            }
            \common\models\PlatformsSettings::updateAll(['use_owner_descriptions' => $defPlatformId], ['use_owner_descriptions' => $id]);        
        }
    }

}