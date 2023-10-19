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

use yii\helpers\FileHelper;
use Yii;

class System {

    public static function get_ip_address() {
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if ( strpos($_SERVER['HTTP_X_FORWARDED_FOR'],',')!==false ) {
                    $ips = array_map('trim', preg_split('/,/',$_SERVER['HTTP_X_FORWARDED_FOR'],-1,PREG_SPLIT_NO_EMPTY));
                    $ip = reset($ips);
                }
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } else {
                $ip = getenv('REMOTE_ADDR');
            }
        }
        $ip = preg_replace('/:(\d+)$/i', '', $ip);
        return $ip;
    }

    public static function get_cookie_params()
    {
        $params = [];
        $platform_config = Yii::$app->get('platform')->config();
        /**
         * @var $platform_config \common\classes\platform_config
         */
        $base_url = $platform_config->getCatalogBaseUrl( Yii::$app->request->getIsSecureConnection() );
        $base_parsed = parse_url($base_url);
        $params['path'] = $base_parsed['path'] ?? null;

        $platform_data = $platform_config->getPlatformData();
        if ($platform_data['ssl_enabled']==2){
            $params['secure'] = true;
        }
        return $params;
    }
    public static function js_cookie_setting($var_name)
    {
        return "{$var_name} = (function(c){c.toString=function(){ return (this.path?'; path='+this.path:'')+(this.secure?'; secure':''); }; return c;})(".json_encode(static::get_cookie_params()).");";
    }

    public static function setcookie($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = null) {
        $common_params = static::get_cookie_params();
        if ( is_null($domain) ) $domain = '';
        if ( is_null($path) ) $path = $common_params['path']?$common_params['path']:'/';
        if ( is_null($secure) ) $secure = $common_params['secure']?$common_params['secure']:false;
        setcookie($name, $value, $expire, $path, (tep_not_null($domain) ? $domain : ''), $secure);
    }

    public static function getcookie($name) {
        if (isset($_COOKIE[$name])) {
          return $_COOKIE[$name];
        } else {
          return '';
        }
    }

    public static function ga_detection($manager) {
        if (!\frontend\design\Info::isTotallyAdmin()){
            $collector = Yii::createObject('\frontend\components\GaCollector');
            $collector->collectData($manager);
        }
    }

    public static function get_ga_detection($order_id){
        $collector = Yii::createObject('\frontend\components\GaCollector');
        $data = $collector->getCollectedData($order_id);
        return $collector->describeCollection($data);
    }

    public static function get_ga_basket_detection($customer_id, $basket_id){
        $collector = Yii::createObject('\frontend\components\GaCollector');
        $data = $collector->getCollectedData(0, $customer_id, $basket_id);
        return $collector->describeCollection($data);
    }

    public static function referer_stat() {
        global $HTTP_REFERER, $search_engines_id, $search_words_id;
        $ref_data = parse_url($HTTP_REFERER);
        $localhost = parse_url(HTTP_SERVER);
        // overture
        if ((strpos($ref_data['host'], $localhost['host']) === false) && strlen($_GET['source']) > 0) {
            $ref_data['host'] = 'overture.com';
            $ref_data['query'] = 'source=' . $_GET['source'];
        }
        // kelkoo
        if ((strpos($ref_data['host'], $localhost['host']) === false) && ($_GET['kelkoo'] == 1)) {
            $ref_data['host'] = 'kelkoo.co.uk';
            $ref_data['query'] = 'keywords=' . $_GET['keywords'];
        }
        // referrer=thomweb
        if ((strpos($ref_data['host'], $localhost['host']) === false) && ($_GET['referrer'] == 'thomweb')) {
            $ref_data['host'] = 'thomweb';
            $ref_data['query'] = 'keywords=' . $_GET['keywords'];
        }
        // dealtime
        if ((strpos($ref_data['host'], $localhost['host']) === false) && ($_GET['dealtime'] == 1)) {
            $ref_data['host'] = 'dealtime.co.uk';
            $ref_data['query'] = 'keywords=' . $_GET['keywords'];
        }

        if (((strpos($ref_data['host'], $localhost['host']) !== false)) || ($search_engines_id > 0) || (strlen($ref_data['host']) == 0)) {
            return true;
        } else {
            $ref_data['host'] = preg_replace("/^w{2,3}\d?\.(.*)/i", "\\1", $ref_data['host']);
            $str_host = strtoLower(trim($ref_data['host']));

            $res = tep_db_query("select search_engines_id, wordkey, name from " . TABLE_SEARCH_ENGINES . " where url like '%" . tep_db_input($str_host) . "' order by show_flag desc ");
            if ($data = tep_db_fetch_array($res)) {
                $search_engines_id = $data['search_engines_id'];
                tep_session_register("search_engines_id");
                $arr = explode("&", $ref_data['query']);
                if (strlen($data['wordkey']) == 0) {
                    return;
                }
                for ($i = 0; $i < count($arr); $i++) {
                    if (strpos($arr[$i], $data['wordkey']) !== false) {
                        $val = explode('=', $arr[$i]);
                        $search_word = trim(str_replace($data['name'], '', urldecode($val[1])));
                    }
                }

                $res = tep_db_query("select search_words_id from " . TABLE_SEARCH_WORDS . " where search_engines_id=" . tep_db_input($search_engines_id) . " and word like '" . tep_db_input($search_word) . "'");
                if ($data = tep_db_fetch_array($res)) {
                    $search_words_id = $data['search_words_id'];
                    tep_session_register("search_words_id");
                    tep_db_query("update " . TABLE_SEARCH_WORDS . " set click_count=click_count+1 where search_words_id='" . tep_db_input($search_words_id) . "'");
                } else {
                    tep_db_query("insert into " . TABLE_SEARCH_WORDS . " set word='" . tep_db_input($search_word) . "', click_count=1, search_engines_id=" . (int) $search_engines_id);
                    $search_words_id = tep_db_insert_id();
                    tep_session_register("search_words_id");
                }
            } else {
                tep_db_query("insert into " . TABLE_SEARCH_ENGINES . " set url='" . tep_db_input($str_host) . "',  wordkey='" . tep_db_input(urldecode($ref_data['query'])) . "'");
                $search_engines_id = tep_db_insert_id();
                tep_session_register("search_engines_id");
            }
        }
    }

    public static function get_seo_path() {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
            $seo_path = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $seo_path = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $seo_path = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $seo_path .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        if (($pos = strpos($seo_path, '?')) !== false) {
            $seo_path = substr($seo_path, 0, $pos);
        }
        $seo_path = urldecode($seo_path);
        if (strpos($seo_path, HTTP_SERVER) === 0) {
            $seo_path = substr($seo_path, strlen(HTTP_SERVER));
        } elseif (strpos($seo_path, DIR_WS_HTTP_CATALOG) === 0) {
            $seo_path = substr($seo_path, strlen(DIR_WS_HTTP_CATALOG));
        }
        return $seo_path;
    }

    public static function get_system_information() {
        $db_query = tep_db_query("select now() as datetime");
        $db = tep_db_fetch_array($db_query);

        list($system, $host, $kernel) = array_pad( preg_split('/[\s,]+/', \common\helpers\Php::exec('uname -a'), 5), 3, 'unknown');

        return array('date' => \common\helpers\Date::datetime_short(date('Y-m-d H:i:s')),
            'system' => $system,
            'kernel' => $kernel,
            'host' => $host,
            'ip' => gethostbyname($host),
            'uptime' => @\common\helpers\Php::exec('uptime'),
            'http_server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'php' => PHP_VERSION,
            'zend' => (function_exists('zend_version') ? zend_version() : ''),
            'db_server' => DB_SERVER,
            'db_ip' => gethostbyname(DB_SERVER),
            'db_version' => 'MySQL ' . (function_exists('mysqli_get_server_info') ? tep_db_get_server_info() : ''),
            'db_date' => \common\helpers\Date::datetime_short($db['datetime']));
    }

    public static function getSysInfo() {
        return [
            'php' => PHP_VERSION,
            'db_server' => DB_SERVER,
            'db_version' => 'MySQL ' . (function_exists('mysqli_get_server_info') ? tep_db_get_server_info() : ''),
            'osCommerce version' => defined('PROJECT_VERSION')? PROJECT_VERSION : 'unknown',
            'osCommerce revision' => defined('MIGRATIONS_DB_REVISION')? MIGRATIONS_DB_REVISION : 'unknown',
            'PLATFORM_ID' => defined('PLATFORM_ID')? PLATFORM_ID : '',
            'DEFAULT_LANGUAGE' => defined('DEFAULT_LANGUAGE')? DEFAULT_LANGUAGE : '',
            'DEFAULT_CURRENCY' => defined('DEFAULT_CURRENCY')? DEFAULT_CURRENCY : '',
        ];
    }

    public static function get_timezones() {
        return array(
            array('id' => '-12', 'text' => '(GMT - 12:00) Eniwetok, Kwajalein'),
            array('id' => '-11', 'text' => '(GMT - 11:00) Midway Island, Samoa'),
            array('id' => '-10', 'text' => '(GMT - 10:00) Hawaii'),
            array('id' => '-09', 'text' => '(GMT - 9:00) Alaska'),
            array('id' => '-08', 'text' => '(GMT - 8:00) Pacific Time, Tijuana'),
            array('id' => '-07', 'text' => '(GMT - 7:00) Mountain Time, Arizona'),
            array('id' => '-06', 'text' => '(GMT - 6:00) Central Time, Mexico City'),
            array('id' => '-05', 'text' => '(GMT - 5:00) Eastern Time, Lima, Indiana'),
            array('id' => '-04', 'text' => '(GMT - 4:00) Atlantic Time, Caracas'),
            array('id' => '-03.5', 'text' => '(GMT - 3:30) Newfoundland'),
            array('id' => '-03', 'text' => '(GMT - 3:00) Greenland, Buenos Aires'),
            array('id' => '-02', 'text' => '(GMT - 2:00) Mid-Atlantic'),
            array('id' => '-01', 'text' => '(GMT - 1:00) Cape Verde Islands, Azores'),
            array('id' => '-00', 'text' => '(GMT + 0:00) Casablanca, London'),
            array('id' => '+01', 'text' => '(GMT + 1:00) Berlin, Rome, Paris '),
            array('id' => '+02', 'text' => '(GMT + 2:00) Cairo, Athens, Instanbul'),
            array('id' => '+03', 'text' => '(GMT + 3:00) Moscow, St. Petersburg'),
            array('id' => '+03.5', 'text' => '(GMT + 3:30) Tehran'),
            array('id' => '+04', 'text' => '(GMT + 4:00) Abu Dhabi, Muscat'),
            array('id' => '+04.5', 'text' => '(GMT + 4:30) Kabul'),
            array('id' => '+05', 'text' => '(GMT + 5:00) Islamabad, Karachi'),
            array('id' => '+05.5', 'text' => '(GMT + 5:30) Calcutta, New Delhi'),
            array('id' => '+05.75', 'text' => '(GMT + 5:45) Kathmandu'),
            array('id' => '+06', 'text' => '(GMT + 6:00) Sri Lanka'),
            array('id' => '+07', 'text' => '(GMT + 7:00) Bangkok, Hanoi, Jakarta'),
            array('id' => '+08', 'text' => '(GMT + 8:00) Beijing, Singapore, Taipei'),
            array('id' => '+09', 'text' => '(GMT + 9:00) Seoul, Osaka, Tokyo'),
            array('id' => '+09.5', 'text' => '(GMT + 9:30) Darwin, Adelaide'),
            array('id' => '+10', 'text' => '(GMT + 10:00) Melbourne, Sydney, Guam'),
            array('id' => '+11', 'text' => '(GMT + 11:00) Magadan, Solomon Islands'),
            array('id' => '+12', 'text' => '(GMT + 12:00) Fiji Islands'),
            array('id' => '+13', 'text' => '(GMT + 13:00) Nuku\'alofa, Tonga'));
    }

    public static function getTimezones(){
        return \yii\helpers\ArrayHelper::map(self::get_timezones(), 'id', 'text');
    }

    public static function browser_detect($component) {
        return stristr($_SERVER["HTTP_USER_AGENT"], $component);
    }

    public static function getHttpUserInfoArray()
    {
        return array($_SERVER['HTTP_USER_AGENT']);
    }

    public static function reset_cache_block($cache_block) {
        global $cache_blocks;

        for ($i = 0, $n = sizeof($cache_blocks); $i < $n; $i++) {
            if ($cache_blocks[$i]['code'] == $cache_block) {
                if ($cache_blocks[$i]['multiple']) {
                    if ($dir = @opendir(DIR_FS_CACHE)) {
                        while ($cache_file = readdir($dir)) {
                            $cached_file = $cache_blocks[$i]['file'];
                            $languages = \common\helpers\Language::get_languages();
                            for ($j = 0, $k = sizeof($languages); $j < $k; $j++) {
                                $cached_file_unlink = preg_replace('/-language/', '-' . $languages[$j]['directory'], $cached_file);
                                if (preg_match('/^' . preg_quote($cached_file_unlink, "/") . '/', $cache_file)) {
                                    @unlink(DIR_FS_CACHE . $cache_file);
                                }
                            }
                        }
                        closedir($dir);
                    }
                } else {
                    $cached_file = $cache_blocks[$i]['file'];
                    $languages = \common\helpers\Language::get_languages();
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                        $cached_file = preg_replace('/-language/', '-' . $languages[$i]['directory'], $cached_file);
                        @unlink(DIR_FS_CACHE . $cached_file);
                    }
                }
                break;
            }
        }
    }

    public static function phpMaxUploadSize()
    {
        $phpMaxSizes = [ini_get('post_max_size'), ini_get('upload_max_filesize')];
        $phpMaxByteSizes = array_map(function($v){
            $l = substr($v, -1);
            $ret = substr($v, 0, -1);
            switch(strtoupper($l)){
                case 'P': $ret *= 1024;
                case 'T': $ret *= 1024;
                case 'G': $ret *= 1024;
                case 'M': $ret *= 1024;
                case 'K': $ret *= 1024;
                    break;
            }
            return $ret;
        },$phpMaxSizes);
        return min($phpMaxByteSizes);
    }

    public static function symlink($existingName, $symlinkName, $isRelative=true)
    {
        if ( $isRelative ) {
            $commonRoot = '';
            $existingDir = dirname(\yii\helpers\FileHelper::normalizePath($existingName));
            $symlinkDir = dirname(\yii\helpers\FileHelper::normalizePath($symlinkName));
            $baseNameExisting = substr($existingName,strlen($existingDir)+1);
            $baseNameSymlink = substr($symlinkName,strlen($symlinkDir)+1);
            try{
                FileHelper::createDirectory($symlinkDir,0777);
            }catch (\Exception $ex){
                return false;
            }
            if ( $existingDir==$symlinkDir ) {
                $commonRoot = $existingDir;
                $existingDir = '';
                $symlinkDir = '';
            }else{
                $commonRoot = $symlinkDir;

                $stillSame = true;
                $symlinkDirParts = explode(DIRECTORY_SEPARATOR, $symlinkDir);
                $existingDirParts = explode(DIRECTORY_SEPARATOR, $existingDir);

                $existingDir = '';
                $symlinkDir = '';
                foreach ($symlinkDirParts as $_idx=>$symlinkDirPart) {
                    if ( $stillSame ) {
                        $existingDirPart = array_shift($existingDirParts);
                        if ( is_null($existingDirPart) ) {
                            array_unshift($existingDirParts,'..');
                            break;
                        }else
                            if ( $existingDirPart!=$symlinkDirPart ){
                                array_unshift($existingDirParts, $existingDirPart);
                                array_unshift($existingDirParts,'..');
                                $stillSame = false;
                            }
                    }else{
                        array_unshift($existingDirParts,'..');
                    }
                }
                $existingDir = implode(DIRECTORY_SEPARATOR,$existingDirParts).(count($existingDirParts)>0?DIRECTORY_SEPARATOR:'');
            }
            $existingName = (substr($existingDir,0,3)=='..'.DIRECTORY_SEPARATOR?'':'.'.DIRECTORY_SEPARATOR).$existingDir.$baseNameExisting;
            $symlinkName = '.'.DIRECTORY_SEPARATOR.$symlinkDir.$baseNameSymlink;

            $returnDir = getcwd();
            chdir($commonRoot);
            @symlink($existingName, $symlinkName);
            chdir($returnDir);
        }else{
            symlink($existingName, $symlinkName);
        }
    }

    public static function isYiiLoaded()
    {
        return class_exists('\Yii') && isset(\Yii::$app) && is_object(\Yii::$app);
    }

    public static function isBackend()
    {
        return \Yii::$app->id == 'app-backend';
    }

    public static function isFrontend()
    {
        return \Yii::$app->id == 'app-frontend';
    }

    public static function isConsole()
    {
        return \Yii::$app->id == 'app-console';
    }

    public static function isProduction()
    {
        return YII_ENV == 'prod';
    }

    public static function isDevelopment()
    {
        return !self::isProduction();
    }
}
