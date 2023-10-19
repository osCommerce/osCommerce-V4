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

namespace backend\controllers;

use Yii;

/**
 * default controller to handle user requests.
 */
class InstallController extends Sceleton {

    public $acl = ['BOX_HEADING_INSTALL'];
    
    private $deployLog = [];
    
    private $doMigrations;
    private $doSystem;
    private $doSmarty;
    private $doTheme;
    private $doHooks;
    private $doMenu;
    
    private $show_ignore_field = false;
    private $dst_file_ignore = [];
    
    function __construct($id,$module=null) {
        \common\helpers\Translation::init('admin/install');

        parent::__construct($id,$module);
    }
    
    private function checkSystemRequires()
    {
        if (!(PHP_VERSION_ID >= 70400)) {
            echo 'Further system upgrade requires a PHP version ">= 7.4.0". You are running ' . PHP_VERSION . '.';
            die();
        }
        ini_set('memory_limit', '512M'); // for large updates
    }

    private static function isKnownRequireModule($filename)
    {
        return $filename === 'php_version_74';
    }

    private function basename($param, $suffix=null,$charset = 'utf-8')
    {
        if ( $suffix ) {
            $tmpstr = ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
            if ( (mb_strpos($param, $suffix, null, $charset)+mb_strlen($suffix, $charset) )  ==  mb_strlen($param, $charset) ) {
                return str_ireplace( $suffix, '', $tmpstr);
            } else {
                return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
            }
        } else {
            return ltrim(mb_substr($param, mb_strrpos($param, DIRECTORY_SEPARATOR, 0, $charset), null, $charset), DIRECTORY_SEPARATOR);
        }
    }
    
    
    private function delTree($dir) 
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : @unlink("$dir/$file");
            }
        }
        return @rmdir($dir);
    }
    
    private function buildXMLTree($parent_id, $queryResponse) 
    {
        $tree = [];
        foreach ($queryResponse as $response) {
            if ($response['parent_id'] == $parent_id) {
                if ($response['box_type'] == 1) {
                    $response['child'] = $this->buildXMLTree($response['box_id'], $queryResponse);
                }
                unset($response['box_id']);
                unset($response['parent_id']);
                $tree[] = $response;
            }
        }
        return $tree;
    }
    
    private function resetReCacheFlags() 
    {
        $this->doMigrations = false;
        $this->doSystem = false;
        $this->doSmarty = false;
        $this->doTheme = false;
        $this->doHooks = false;
        $this->doMenu = false;
    }
    
    private function runSystemReCache($echo = false) 
    {
        set_time_limit(0);
        @ignore_user_abort(true);
        
        $runtimePath = Yii::getAlias('@runtime');
        $all_runtime_directories = [];
        $all_runtime_directories[] = $runtimePath;
        $runtime_dir_name = str_replace(
            Yii::getAlias('@backend'),
            '',
            Yii::getAlias('@runtime')
        );
        $other_apps_aliases = [
            '@frontend',
            '@console',
            //'@pos',
            //'@superadmin',
            //'@rest',
        ];
        foreach ( $other_apps_aliases as $_apps_alias ) {
            $_app_runtime_dir = Yii::getAlias($_apps_alias . $runtime_dir_name, false);
            if ( !$_app_runtime_dir || !is_dir($_app_runtime_dir) ) continue;

            $all_runtime_directories[] = $_app_runtime_dir;
        }
        
        if ($this->doMigrations) {
            if ($echo) echo TEXT_APPLY_MIGRATIONS . "<br>\n";
            $oldApp = \Yii::$app;
            new \yii\console\Application([
                'id' => 'Command runner',
                'basePath' => '@site_root',
                'components' => [
                    'db' => $oldApp->db,
                    'cache' => [
                        'class' => 'yii\caching\FileCache',
                        'cachePath' => '@frontend/runtime/cache'
                    ],
                    'log' => [
                        'targets' => [
                            [
                                'class' => 'yii\log\FileTarget',
                                'levels' => ['error', 'warning'],
                            ],
                        ],
                    ],
                    'errorHandler' => [
                        'class' => '\common\classes\TlErrorHandlerConsole',
                    ],
                ],
            ]);
            \Yii::$app->runAction('migrate/up', ['migrationPath' => '@console/migrations/', 'interactive' => false, 'compact' => true]);
            \Yii::$app = $oldApp;
        }
        
        
        if ($this->doSystem) {
            if ($echo) echo TEXT_CLEAN_CACHE . "<br>\n";
            Yii::$app->getCache()->flush();
            if (function_exists('opcache_reset')) {
                opcache_reset();
                if ($echo) echo TEXT_CACHE_FLUSHED . "<br>\n";
            }
        }
        
        if ($this->doSmarty) {
            if ($echo) echo TEXT_CLEAN_SMARTY . "<br>\n";
            foreach ($all_runtime_directories as $runtime_directory){
                $smartyPath = $runtime_directory . DIRECTORY_SEPARATOR . 'Smarty' . DIRECTORY_SEPARATOR . 'compile' . DIRECTORY_SEPARATOR . '*.*';
                array_map('unlink', glob($smartyPath));
            }
            $themesPath = DIR_FS_CATALOG . 'themes' . DIRECTORY_SEPARATOR;
            $dir = scandir($themesPath);
            foreach ($dir as $theme) {
                if (file_exists($themesPath . $theme . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR)) {
                    \yii\helpers\FileHelper::removeDirectory($themesPath . $theme . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);
                }
            }
        }
        
        if ($this->doMenu) {
            \common\helpers\MenuHelper::resetAdminMenu();
        } 
        
        if ($this->doHooks) {
            \common\helpers\Hooks::resetHooks();
        }
        
        if ($this->doTheme) {
            \backend\design\Style::flushCacheAll();
        }
        
        $this->resetReCacheFlags();
    }
    
    private function getFileWithDependencies($get_by, $filter) 
    {
        $status = false;
        $filename = '';
        if ($request = curl_init()) {
            $storageUrl = \Yii::$app->params['appStorage.url'];
            $storageKey = $this->getStorageKey();
            $secKeyGlobal = md5(\Yii::$app->db->dsn . (defined('INSTALLED_MICROTIME') ? INSTALLED_MICROTIME : ''));
            curl_setopt($request, CURLOPT_URL, $storageUrl . 'app-api-server/product');

            // for testing
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
            if (defined('CURLOPT_SSL_VERIFYSTATUS')) { // Added in cURL 7.41.0
                curl_setopt($request, CURLOPT_SSL_VERIFYSTATUS, false);
            }

            curl_setopt($request, CURLOPT_TIMEOUT_MS, 30000);
            curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $storageKey . ':' . $secKeyGlobal
            ));

            $postFieldArray = [
                'get_by' => $get_by,
                'filter' => $filter,
            ];
            $postFieldArray = json_encode($postFieldArray);

            curl_setopt($request, CURLOPT_POSTFIELDS, $postFieldArray);
            //$result = curl_exec($request);
            $result = json_decode(curl_exec($request), true);
            $response = curl_getinfo($request);
            curl_close($request);

            if ($response['http_code'] == 200 && isset($result['content'])) {
                $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
                $filename = $result['filename'] ?? '';
                if (!file_exists($path . $filename)) {
                    $content = base64_decode($result['content']);
                    $size = $result['size'] ?? 0;
                    if (strlen($content) == $size) {
                        file_put_contents($path . $filename, $content);
                        $status = true;
                    }
                } else {
                    $status = true;
                }
                $zip = new \ZipArchive();
                if ($zip->open($path . $filename) === true) {
                    $json = $zip->getFromName('distribution.json');
                    $json = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',$json);
                    $zip->close();
                    if (!empty($json)) {
                        $distribution = json_decode($json);
                        if (isset($distribution->require->modules) && is_array($distribution->require->modules)) {
                            foreach ($distribution->require->modules as $subfile) {
                                $record = \common\models\Installer::find()->where(['filename' => $subfile])->one();
                                if ( !($record instanceof \common\models\Installer) ) {
                                    $status = $status && $this->getFileWithDependencies('file', $subfile);
                                }
                            }
                        }
                    }
                } else {
                    $status = false;
                }
            }
        }
        if ($status) {
            return $filename;
        }
        return $status;
    }
    
    private function installFileWithDependencies($filename, $settings = [], $echo = false) 
    {
        $this->deployLog[] = TEXT_CHECKING . ' ' . $filename;
                
        $selected_platform_id = $settings['platform_id'] ?? 0;
        $locale = $settings['locale'] ?? 0;
        
        $platformNames = [];
        $toAssign = [];
        if ($selected_platform_id > 0) {
            $toAssign[] = $selected_platform_id;
            $pRow = \common\models\Platforms::find()->select(['platform_name'])
                    ->where(['is_virtual' => 0, 'is_marketplace' => 0, 'platform_id' => $selected_platform_id])
                    ->asArray()
                    ->one();
            $platformNames[$selected_platform_id] = $pRow['platform_name'] ?? '';
        }
        if ($selected_platform_id < 0) {
            foreach (\common\models\Platforms::find()->select(['platform_id', 'platform_name'])->where(['is_virtual' => 0, 'is_marketplace' => 0])->asArray()->all() as $pRow) {
                $toAssign[] = $pRow['platform_id'];
                $platformNames[$pRow['platform_id']] = $pRow['platform_name'];
            }
        }
        
        $force = $settings['force'] ?? 0;
        $selected_acl = $settings['acl'] ?? 0;
        $status = false;
        $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR;
        $zip = new \ZipArchive();
        if ($zip->open($path . 'uploads' . DIRECTORY_SEPARATOR . $filename) === true) {
            $json = $zip->getFromName('distribution.json');
            $json = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',$json);
            $zip->close();
            if (!empty($json)) {
                $distribution = json_decode($json);
                $status = true;
                if (isset($distribution->require->version)) {
                    $conf = \common\models\Configuration::find()->where(['configuration_key' => 'MIGRATIONS_DB_REVISION'])->one();
                    if ($conf instanceof \common\models\Configuration) {
                        $version = $conf->configuration_value;
                    } else {
                        $version = '';
                    }
                    $versionApplicable = $distribution->require->version_applicable ?? 'equal';
                    switch ($versionApplicable) {
                        case 'equal':
                            if ($version != $distribution->require->version) {
                                $status = false;
                                $this->deployLog[] = 'Version required ' . $distribution->require->version;
                            }                                      
                            break;
                        case 'greater-equal':
                            if (intval($version) < intval($distribution->require->version)) {
                                $status = false;
                                $this->deployLog[] = 'Version must be greater or equal to ' . $distribution->require->version;
                            }
                            break;
                        case 'less-equal':
                            if (intval($version) > intval($distribution->require->version)) {
                                $status = false;
                                $this->deployLog[] = 'Version must be less or equal to ' . $distribution->require->version;
                            }
                            break;
                        default:
                            break;
                    }
                    
                }
                if (isset($distribution->require->modules) && is_array($distribution->require->modules)) {
                    foreach ($distribution->require->modules as $subfile) {
                        if (self::isKnownRequireModule($subfile)) continue;
                        $record = \common\models\Installer::find()->where(['filename' => $subfile])->one();
                        if ( !($record instanceof \common\models\Installer) ) {
                            $status = $status && $this->installFileWithDependencies($subfile, $settings, $echo);
                        }
                    }
                }
                if (isset($distribution->require->classes) && is_array($distribution->require->classes)) {
                    foreach ($distribution->require->classes as $classversion) {
                        $recordQuery = \common\models\Installer::find()->where(['archive_class' => $classversion->name]);
                        $cv = '';
                        if (isset($classversion->min)) {
                            list($major, $minor, $patch) = array_pad( explode('.', (string)$classversion->min), 3, 0);
                            $archive_version = intval($major) + intval($minor)/100 + intval($patch)/10000;
                            $recordQuery->andWhere(['>=', 'archive_version', $archive_version]);
                            $cv .= ', v.' . $classversion->min . ' or greater';
                        }
                        if (isset($classversion->max)) {
                            list($major, $minor, $patch) = array_pad( explode('.', (string)$classversion->max), 3, 0);
                            $archive_version = intval($major) + intval($minor)/100 + intval($patch)/10000;
                            $recordQuery->andWhere(['<=', 'archive_version', $archive_version]);
                            $cv .= ', v.' . $classversion->max . ' or less';
                        }
                        $record = $recordQuery->one();
                        if (!($record instanceof \common\models\Installer)) {
                            $this->deployLog[] = 'Class '.$classversion->name.$cv.' must be installed';
                            $status = false;
                        }
                        unset($record);
                    }
                }
                if ($status) {
                    $moduleDir = '';
                    $setParam = '';
                    switch ($distribution->type) {
                        case 'extension':// Extension
                            if (isset($distribution->class)) {
                                $pathP = $path . 'lib' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'extensions';
                                $check = \common\models\Installer::find()
                                        ->select(['max(archive_version) as version'])
                                        ->where(['archive_type' => (string)$distribution->type])
                                        ->andWhere(['archive_class' => (string)$distribution->class])
                                        ->asArray()
                                        ->one();
                                if (isset($check['version'])) {
                                    $major = floor($check['version']);
                                    $minor = floor(($check['version'] - $major)*100);
                                    $patch = ($check['version'] - $major - $minor/100)*10000;
                                    $jsonFile = 'v-' . $major . '-' . $minor . '-' . $patch . '.json';
                                    $zip->open($path . 'uploads' . DIRECTORY_SEPARATOR . $filename);
                                    $jsonString = $zip->getFromName($jsonFile);
                                    $zip->close();
                                    if ($jsonString !== false) {
                                        $checklist = json_decode($jsonString);
                                        $pathC = $pathP . DIRECTORY_SEPARATOR . $distribution->class . DIRECTORY_SEPARATOR;
                                        foreach ($checklist as $checkfile => $checksum) {
                                            $dst = str_replace('|', DIRECTORY_SEPARATOR, $checkfile);
                                            if (empty($checksum) && !is_dir($pathC . $dst)) {
                                                $status = false;
                                                $this->deployLog[] = "Directory $dst not found.";
                                            }
                                            if (!empty($checksum) && is_file($pathC . $dst)) {
                                                $crc = crc32(file_get_contents($pathC . $dst));
                                                if ($crc != $checksum) {
                                                    $status = false;
                                                    $this->deployLog[] = "File $dst modified.";
                                                }
                                            } elseif (!empty($checksum)) {
                                                $status = false;
                                                $this->deployLog[] = "File $dst not found.";
                                            }
                                        }
                                    }
                                }
                                if ($status) {
                                    $status = $this->checkFileDst($distribution->src, $filename, $pathP, $echo, $force);
                                }
                                if ($status) {
                                    $this->runFileDst($distribution->src, $filename, $pathP, $echo);

                                    $class = '\\common\\extensions\\' . (string)$distribution->class . '\\' . (string)$distribution->class;
                                    $this->doInstallClass($class, 0, $selected_acl);
                                    $this->doHooks = true;
                                    $this->doMenu = false;
                                    $this->doInstallRecord($filename, (string)$distribution->type, (string)$distribution->class, (string)$distribution->version, $distribution->src);
                                    $this->doSystem = true;
                                }
                            }
                            break;
                        case 'design':// Theme
                            $theme = new \common\models\Themes();
                            $theme->loadDefaultValues();
                            $theme_name = \common\classes\design::pageName($distribution->name);
                            $theme->theme_name = $theme_name;
                            $theme->title = $distribution->name;
                            $theme->install = 1;
                            $theme->is_default = 0;
                            $theme->sort_order = 0;
                            $theme->parent_theme = '';
                            if ($theme->save()) {
                                \backend\design\Theme::import($theme_name, $path . 'uploads' . DIRECTORY_SEPARATOR . $filename);
                                $oldData = [
                                    'id' => $theme->id
                                ];
                                if ($theme->id > 0) {
                                    foreach ($toAssign as $toId) {
                                        $oldData['platforms_to_themes'][$toId] = \common\models\PlatformsToThemes::find()->where(['platform_id' => $toId])->asArray()->all();
                                        \common\models\PlatformsToThemes::deleteAll(['platform_id' => $toId]);
                                        $p2t = new \common\models\PlatformsToThemes();
                                        $p2t->loadDefaultValues();
                                        $p2t->platform_id = $toId;
                                        $p2t->theme_id = $theme->id;
                                        $p2t->is_default = 1;
                                        $p2t->save(false);
                                    }
                                }
                                
                                $this->doSystem = true;
                                $this->doSmarty = true;
                                //$this->doTheme = true;
                                $this->doInstallRecord($filename, (string)$distribution->type, (string)($distribution->class ?? ''), (string)$distribution->version, $oldData);
                                $status = true;
                            } else {
                                $status = false;
                                
                            }
                            break;
                        case 'translate':// Translations
                            $languages = \common\helpers\Language::get_languages(true);
                            $override = $addnew = true;
                            $zip->open($path . 'uploads' . DIRECTORY_SEPARATOR . $filename);
                            $localejson = $zip->getFromName('locale.json');
                            $localejson = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',$localejson);
                            $oldData = [];
                            if (!empty($json)) {
                                $localejson = json_decode($localejson, JSON_OBJECT_AS_ARRAY);
                                $lang = \common\models\Languages::find()->where(['code' => (string)$localejson['code']])->one();
                                if ($lang instanceof \common\models\Languages) {
                                    if ($locale == 1) {
                                        // update language settings
                                        $update_language_id = $lang->languages_id;
                                        if ($update_language_id > 0 && isset($localejson['formats']) && is_array($localejson['formats'])) {
                                            foreach ($localejson['formats'] as $configuration_key => $configuration_value) {
                                                $lFormats = \common\models\LanguagesFormats::find()
                                                        ->where(['configuration_key' => $configuration_key])
                                                        ->andWhere(['language_id' => $update_language_id])
                                                        ->one();
                                                if ($lFormats instanceof \common\models\LanguagesFormats) {
                                                    $lFormats->configuration_value = $configuration_value;
                                                    $lFormats->save(false);
                                                } else {
                                                    $lFormats = new \common\models\LanguagesFormats();
                                                    $lFormats->loadDefaultValues();
                                                    $lFormats->configuration_key = $configuration_key;
                                                    $lFormats->configuration_value = $configuration_value;
                                                    $lFormats->language_id = $update_language_id;
                                                    $lFormats->save(false);
                                                }
                                            }
                                        }
                                    }
                                    $insert_id = $lang->languages_id;
                                } else {
                                    // install new language and settings
                                    $max = tep_db_fetch_array(tep_db_query("select max(sort_order)+1 as sort_order from languages where 1"));
                                    $sql_array = array(
                                        'name' => $localejson['name'],
                                        'code' => strtolower((string)$localejson['code']),
                                        'image_svg' => $localejson['icon'],
                                        'locale' => (string)$localejson['locale'],
                                        'sort_order' => $max['sort_order'],
                                        'languages_status' => 0,
                                    );
                                    $lang = new \common\models\Languages();
                                    $lang->loadDefaultValues();
                                    $lang->setAttributes($sql_array, false);
                                    if ($lang->save(false)) {
                                        $insert_id = $lang->languages_id;
                                        if ($insert_id > 0 && isset($localejson['formats']) && is_array($localejson['formats'])) {
                                            foreach ($localejson['formats'] as $configuration_key => $configuration_value) {
                                                $lFormats = new \common\models\LanguagesFormats();
                                                $lFormats->loadDefaultValues();
                                                $lFormats->configuration_key = $configuration_key;
                                                $lFormats->configuration_value = $configuration_value;
                                                $lFormats->language_id = $insert_id;
                                                $lFormats->save(false);
                                            }
                                            $oldData[] = [
                                                'action' => 'deletelanguage',
                                                'language_id' => $insert_id
                                            ];
                                        }
                                    }
                                    $languages = \common\helpers\Language::get_languages(true);
                                }
                                // update or create from default language
                                \common\helpers\Language::copyLanguage((int) \common\helpers\Language::get_default_language_id(), (int) $insert_id);
                            }
                            foreach ((array)$distribution->files as $file) {
                                $CsvString = $zip->getFromName($file);
                                
                                $bom = substr($CsvString, 0, 2);
                                if ($bom === chr(0xff).chr(0xfe) || $bom === chr(0xfe).chr(0xff)) {
                                    $encoding = 'UTF-16';
                                } else {
                                    $encoding = mb_detect_encoding($CsvString, 'auto', true);
                                }
                                if ($encoding) {
                                    $CsvString = iconv($encoding, "UTF-8", $CsvString);
                                } else {
                                    $CsvString = iconv('CP850', "UTF-8", $CsvString);
                                }
                                $Data = str_getcsv($CsvString, "\n");
                                $uploadedKeys = false;
                                foreach($Data as &$data) {
                                    $data = str_getcsv($data, "\t");
                                    if ($uploadedKeys === false) {
                                        $uploadedKeys = array_flip($data);
                                        continue;
                                    }
                                    if (isset($data[$uploadedKeys['HASH']]) && !empty($data[$uploadedKeys['HASH']])) {
                                        foreach ($languages as $_lang) {
                                            if (isset($uploadedKeys[$_lang['code']])) {

                                                $check_hash_query = tep_db_query("SELECT * FROM " . TABLE_TRANSLATION . " WHERE language_id='" . (int)$_lang['id'] . "' and hash = '" . tep_db_input($data[$uploadedKeys['HASH']]) . "'");
                                                if (tep_db_num_rows($check_hash_query) > 0) {
                                                    if ($override) {
                                                        $check_hash = tep_db_fetch_array($check_hash_query);
                                                        $oldData[] = [
                                                            'action' => 'update',
                                                            'translation_value' => $check_hash['translation_value'],
                                                            'translated' => $check_hash['translated'],
                                                            'language_id' => $check_hash['language_id'],
                                                            'hash' => $check_hash['hash'],
                                                        ];
                                                        tep_db_query("update " . TABLE_TRANSLATION . " set translation_value = '" . tep_db_input($data[$uploadedKeys[$_lang['code']]]) . "', translated = '" . tep_db_input($data[$uploadedKeys[$_lang['code'] . '_TSL']]) . "' where language_id = '" . (int)$_lang['id'] . "' and hash = '" . tep_db_input($data[$uploadedKeys['HASH']]) . "'");
                                                    }
                                                } elseif (isset($data[$uploadedKeys['Entity']]) && isset($data[$uploadedKeys['Key']]) ) {
                                                    $check_hash_query = tep_db_query("SELECT * FROM " . TABLE_TRANSLATION . " WHERE language_id='" . (int)$_lang['id'] . "' and translation_key = '" . tep_db_input($data[$uploadedKeys['Key']]) . "' and translation_entity = '" . tep_db_input($data[$uploadedKeys['Entity']]) . "'");
                                                    if (tep_db_num_rows($check_hash_query) > 0) {
                                                        if ($override) {
                                                            $check_hash = tep_db_fetch_array($check_hash_query);
                                                            $oldData[] = [
                                                                'action' => 'update',
                                                                'translation_value' => $check_hash['translation_value'],
                                                                'translated' => $check_hash['translated'],
                                                                'language_id' => $check_hash['language_id'],
                                                                'hash' => $check_hash['hash'],
                                                            ];
                                                            tep_db_query("update " . TABLE_TRANSLATION . " set translation_value = '" . tep_db_input($data[$uploadedKeys[$_lang['code']]]) . "', translated = '" . tep_db_input($data[$uploadedKeys[$_lang['code'] . '_TSL']]) . "' where language_id = '" . (int)$_lang['id'] . "' and hash = '" . tep_db_input($data[$uploadedKeys['HASH']]) . "'");
                                                        }
                                                    } elseif ($addnew && !empty($data[$uploadedKeys['Key']]) && !empty($data[$uploadedKeys['Entity']])) {
                                                        $hash = md5($data[$uploadedKeys['Key']] . '-' . $data[$uploadedKeys['Entity']]);
                                                        $sql_data_array = [
                                                            'language_id' => (int)$_lang['id'],
                                                            'translation_key' => $data[$uploadedKeys['Key']],
                                                            'translation_entity' => $data[$uploadedKeys['Entity']],
                                                            'translation_value' => $data[$uploadedKeys[$_lang['code']]],
                                                            'hash' => $hash,
                                                            'translated' => $data[$uploadedKeys[$_lang['code'] . '_TSL']],
                                                        ];
                                                        $oldData[] = [
                                                            'action' => 'delete',
                                                            'language_id' => (int)$_lang['id'],
                                                            'hash' => $hash,
                                                        ];
                                                        tep_db_perform(TABLE_TRANSLATION, $sql_data_array);
                                                    }
                                                }
                                            } else {
                                                //check and add empty value if not exist
                                            }
                                        }
                                    }
                                }
                            }
                            $this->doInstallRecord($filename, (string)$distribution->type, (string)($distribution->class ?? ''), (string)$distribution->version, $oldData);
                            $this->doSystem = true;
                            $this->doTheme = true;
                            $zip->close();
                            break;
                        case 'payment':// Payment
                            $moduleDir = 'orderPayment';
                            $setParam = 'payment';
                        case 'shipping':// Shipping
                            if (empty($moduleDir)) { $moduleDir = 'orderShipping';$setParam = 'shipping'; }
                        case 'totals':// Order structure
                            if (empty($moduleDir)) { $moduleDir = 'orderTotal';$setParam = 'ordertotal'; }
                        case 'analytic':// Google analytic
                            if (empty($moduleDir)) { $moduleDir = 'analytic'; }
                        case 'label':// Shipping label
                            if (empty($moduleDir)) { $moduleDir = 'label';$setParam = 'label'; }
                            $pathP = $path . 'lib' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleDir;
                            $status = $this->checkFileDst($distribution->src, $filename, $pathP, $echo, $force);
                            if ($status) {
                                $this->runFileDst($distribution->src, $filename, $pathP, $echo);
                                if (isset($distribution->class)) {
                                    $class = '\\common\\modules\\' . $moduleDir . '\\' . (string)$distribution->class;
                                    foreach ($toAssign as $toId) {
                                        $this->doInstallClass($class, $toId);
                                        $this->doRecalcModuleSort('add', $class, $distribution->type, $toId);
                                        if (!empty($setParam)) {
                                            $this->deployLog[] =  'This ' . $distribution->type . ' installed automatically.' . 'Dont forget <a target="_blank" href="' . Yii::$app->urlManager->createUrl(['modules/edit', 'set' => $setParam, 'module' => (string)$distribution->class, 'platform_id' => $toId]) . '">check settings for platform '.$platformNames[$toId].'</a>.';
                                        }
                                    }
                                }
                                $this->doInstallRecord($filename, (string)$distribution->type, (string)($distribution->class ?? ''), (string)$distribution->version, $distribution->src);
                                $this->doSystem = true;
                            }
                            break;
                        case 'samples':// Sample data
                            $status = true;
                            \common\helpers\Translation::init('admin/easypopulate');
                            \common\helpers\Translation::init('admin/main');
                            try {
                                
                                copy($path . 'uploads' . DIRECTORY_SEPARATOR . $filename, $path . 'ep_files' . DIRECTORY_SEPARATOR . 'manual_import' . DIRECTORY_SEPARATOR . $filename);
                              
                                ob_start();
                                $messages = new \backend\models\EP\Messages([
                                    'output' => 'null',
                                ]);

                                $importJob = new \backend\models\EP\JobZipFile([
                                    'directory_id' => 2, //manual import
                                    'file_name' => $filename,
                                    'direction' => 'import',
                                    'job_provider' => 'auto',
                                ]);
                                $importJob->tryAutoConfigure();
                                $importJob->run($messages);
                                ob_flush();
                                
                                if ($zip->open($path . 'ep_files' . DIRECTORY_SEPARATOR . 'manual_import' . DIRECTORY_SEPARATOR . $filename) === true) {
                                        
                                    $catalog_categories = [];
                                    $zip->extractTo($path . 'ep_files' . DIRECTORY_SEPARATOR . 'manual_import' . DIRECTORY_SEPARATOR, 'catalog_categories.csv');
                                    $reader = new \backend\models\EP\Reader\CSV([
                                        'filename' => $path . 'ep_files' . DIRECTORY_SEPARATOR . 'manual_import' . DIRECTORY_SEPARATOR . 'catalog_categories.csv',
                                    ]);
                                    while ($Columns = $reader->read()) {
                                        $catalog_categories[] = $Columns['Categories SEO page name (URL) en'];
                                    }
                                    @unlink($path . 'ep_files' . DIRECTORY_SEPARATOR . 'manual_import' . DIRECTORY_SEPARATOR . 'catalog_categories.csv');
                                    unset($reader);

                                    $catalog_products = [];
                                    $zip->extractTo($path . 'ep_files' . DIRECTORY_SEPARATOR . 'manual_import' . DIRECTORY_SEPARATOR, 'catalog_products.csv');
                                    $reader = new \backend\models\EP\Reader\CSV([
                                        'filename' => $path . 'ep_files' . DIRECTORY_SEPARATOR . 'manual_import' . DIRECTORY_SEPARATOR . 'catalog_products.csv',
                                    ]);
                                    while ($Columns = $reader->read()) {
                                        $catalog_products[] = $Columns['Products Model'];
                                    }
                                    @unlink($path . 'ep_files' . DIRECTORY_SEPARATOR . 'manual_import' . DIRECTORY_SEPARATOR . 'catalog_products.csv');
                                    unset($reader);

                                    $zip->close();
                                        
                                    foreach ($toAssign as $toId) {
                                            
                                        foreach ($catalog_categories as $catalog_cat) {
                                            $cat = \common\models\CategoriesDescription::find()->where(['categories_seo_page_name' => $catalog_cat])->one();
                                            if ($cat instanceof \common\models\CategoriesDescription) {
                                                tep_db_query("INSERT IGNORE INTO platforms_categories (platform_id, categories_id) VALUES ($toId, ".$cat->categories_id.");");
                                            }
                                        }
                                        
                                        foreach (\common\models\Products::find()->where(['IN', 'products_model', $catalog_products])->all() as $product) {
                                            tep_db_query("INSERT IGNORE INTO platforms_products (platform_id, products_id) VALUES ($toId, ".$product->products_id.");");
                                            \common\helpers\Product::doCache($product->products_id);
                                        }
                                        
                                    }
                                    
                                }
                                $oldData = [
                                    'catalog_categories' => $catalog_categories,
                                    'catalog_products' => $catalog_products
                                ];
                                $this->doInstallRecord($filename, (string)$distribution->type, (string)($distribution->class ?? ''), (string)$distribution->version, $oldData);
                                @unlink($path . 'ep_files' . DIRECTORY_SEPARATOR . 'manual_import' . DIRECTORY_SEPARATOR . $filename);
                                

                            } catch (\Exception $ex) {
                                //echo "err:".$ex->getMessage()."\n".$ex->getTraceAsString()."\n";die();
                                $status = false;
                                $this->sendEcho("<font color='red'>Exception: " . $ex->getMessage() . ".</font><br>\n" );
                            }
                            break;
                        case 'system':
                        case 'update':// System update
                            $status = $this->checkFileDst($distribution->src, $filename, $path, ($force ? false : $echo), $force);
                            if ($status) {
                                $this->runFileDst($distribution->src, $filename, $path, $echo);
                                $this->doInstallRecord($filename, (string)$distribution->type, (string)($distribution->class ?? ''), (string)$distribution->version, $distribution->src);
                                $this->doMigrations = true;
                                $this->doSystem = true;
                                $this->doSmarty = true;
                                //$this->doTheme = true;
                                $this->doHooks = true;
                                $this->doMenu = true;
                                if ($distribution->type == 'update') {
                                    \common\models\Configuration::updateAll(['configuration_value' => (string) $distribution->version], ['configuration_key'=> 'MIGRATIONS_DB_REVISION']);
                                }
                            }
                            break;
                        case 'configuration':
                            $oldData = [];
                            $zip->open($path . 'uploads' . DIRECTORY_SEPARATOR . $filename);
                            foreach ((array)$distribution->files as $file) {
                                $CsvString = $zip->getFromName($file);
                                
                                $bom = substr($CsvString, 0, 2);
                                if ($bom === chr(0xff).chr(0xfe) || $bom === chr(0xfe).chr(0xff)) {
                                    $encoding = 'UTF-16';
                                } else {
                                    $encoding = mb_detect_encoding($CsvString, 'auto', true);
                                }
                                if ($encoding) {
                                    $CsvString = iconv($encoding, "UTF-8", $CsvString);
                                } else {
                                    $CsvString = iconv('CP850', "UTF-8", $CsvString);
                                }
                                $Data = str_getcsv($CsvString, "\n");
                                $uploadedKeys = false;
                                foreach($Data as &$data) {
                                    $data = str_getcsv($data, "\t");
                                    if ($uploadedKeys === false) {
                                        $uploadedKeys = array_flip($data);
                                        continue;
                                    }
                                    // Key Group Operation Value
                                    if (
                                            isset($data[$uploadedKeys['Key']]) && !empty($data[$uploadedKeys['Key']]) &&
                                            isset($data[$uploadedKeys['Operation']]) && !empty($data[$uploadedKeys['Operation']])
                                        ) {
                                        switch($data[$uploadedKeys['Operation']]) {
                                                    case 'add':
                                                        $conf = \common\models\Configuration::find()->where(['configuration_key' => (string) $data[$uploadedKeys['Key']]])->one();
                                                        if ($conf instanceof \common\models\Configuration) {
                                                            $oldData[] = [
                                                                'action' => 'update',
                                                                'configuration_key' => $conf->configuration_key,
                                                                'configuration_value' => $conf->configuration_value,
                                                                'configuration_group_id' => $conf->configuration_group_id,
                                                            ];
                                                        } else {
                                                            $oldData[] = [
                                                                'action' => 'delete',
                                                                'configuration_key' => (string) $data[$uploadedKeys['Key']],
                                                            ];
                                                            $conf = new \common\models\Configuration();
                                                            $conf->loadDefaultValues();
                                                            $conf->configuration_key = (string) $data[$uploadedKeys['Key']];
                                                        }
                                                        $conf->configuration_value = (string) $data[$uploadedKeys['Value']];
                                                        $conf->configuration_group_id = (string) $data[$uploadedKeys['Group']];
                                                        $conf->save(false);
                                                        break;
                                                    case 'delete':
                                                        $conf = \common\models\Configuration::find()->where(['configuration_key' => (string) $data[$uploadedKeys['Key']]])->one();
                                                        if ($conf instanceof \common\models\Configuration) {
                                                            $oldData[] = [
                                                                'action' => 'add',
                                                                'configuration_title' => $conf->configuration_title,
                                                                'configuration_key' => $conf->configuration_key,
                                                                'configuration_value' => $conf->configuration_value,
                                                                'configuration_description' => $conf->configuration_description,
                                                                'configuration_group_id' => $conf->configuration_group_id,
                                                                'sort_order' => $conf->sort_order,
                                                                'last_modified' => $conf->last_modified,
                                                                'date_added' => $conf->date_added,
                                                                'use_function' => $conf->use_function,
                                                                'set_function' => $conf->set_function,
                                                            ];
                                                            $conf->delete();
                                                        }
                                                        break;
                                                    case 'modify':
                                                        $conf = \common\models\Configuration::find()->where(['configuration_key' => (string) $data[$uploadedKeys['Key']]])->one();
                                                        if ($conf instanceof \common\models\Configuration) {
                                                            $oldData[] = [
                                                                'action' => 'update',
                                                                'configuration_key' => $conf->configuration_key,
                                                                'configuration_value' => $conf->configuration_value,
                                                                'configuration_group_id' => $conf->configuration_group_id,
                                                            ];
                                                            $conf->configuration_value = (string) $data[$uploadedKeys['Value']];
                                                            $conf->configuration_group_id = (string) $data[$uploadedKeys['Group']];
                                                            $conf->save(false);
                                                        }
                                                        break;
                                                }
                                    }
                                }
                            }
                            $this->doInstallRecord($filename, (string)$distribution->type, (string)($distribution->class ?? ''), (string)$distribution->version, $oldData);
                            $this->doSystem = true;
                            $zip->close();
                            break;
                        default:
                            $status = false;
                            break;
                    }
                }
            }
            
        }
        if ($status) {
            $this->deployLog[] = $filename . ' ' . TEXT_PACK_INSTALLED . '.';
        } else {
            $this->deployLog[] = $filename . ' ' . TEXT_PACK_ABORTED . '.';
        }
        return $status;
    }
    
    private function checkFileDst($rules, $zipFile, $pathP, $echo = false, $force = 0)
    {
        $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR;
        $checked = true;
        $forceBackup = false;
        if ($force == 1) {
            $zipForce = new \ZipArchive();
            if ($zipForce->open($path . 'uploads' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . 'force.'.$zipFile, \ZipArchive::CREATE) === true) {
                $forceBackup = true;
            }
        }
        foreach ((array) $rules as $src) {
            switch ($src->action) {
                case 'add':
                case 'modify':
                case 'copy':
                case 'delete':
                    break;
                default:
                    if ($echo) echo "<font color='red'>".TEXT_ACTION_ERROR.".</font><br>\n";
                    $this->deployLog[] = "<font color='red'>".TEXT_ACTION_ERROR.".</font><br>\n";
                    $checked = false;
                    break;
            }
            switch ($src->type) {
                case 'dir':
                case 'file':
                    break;
                default:
                    if ($echo) echo "<font color='red'>".TEXT_TYPE_ERROR.".</font><br>\n";
                    $this->deployLog[] = "<font color='red'>".TEXT_TYPE_ERROR.".</font><br>\n";
                    $checked = false;
                    break;
            }
            if (isset($src->crc32)) {
                $dst = str_replace('|', DIRECTORY_SEPARATOR, $src->path);
                if (!is_file($pathP . DIRECTORY_SEPARATOR . $dst)) {
                    if ($echo) echo "<font color='red'>File $dst not found.</font><br>\n";
                    $this->deployLog[] = "<font color='red'>File $dst not found.</font><br>\n";
                    $checked = false;
                 } else {
                    $oldItemCrc = crc32(file_get_contents($pathP . DIRECTORY_SEPARATOR . $dst));
                    if ($src->crc32 != $oldItemCrc) {
                        if ($echo) echo "<font color='red'>" . TEXT_FILE . " " . $dst . " " . TEXT_CHECKSUM_ERROR . ".</font>".($this->show_ignore_field ? '<label><input type="checkbox" name="dst_file_ignore[]" class="dst_file_ignore" value="'.$dst.'">Ignore</label>' : '')."<br>\n";
                        $this->deployLog[] = "<font color='red'>" . TEXT_FILE . " " . $dst . " " . TEXT_CHECKSUM_ERROR . ".</font><br>\n";
                        $checked = false;
                        if ($forceBackup) {
                            $zipForce->addFile($pathP . DIRECTORY_SEPARATOR . $dst, $dst);
                        }
                    } else {
                        if ($echo) echo "<font color='green'>" . TEXT_FILE . " " . $dst . " " . TEXT_CHECKSUM_PASSED . ".</font><br>\n";
                        $this->deployLog[] = "<font color='green'>" . TEXT_FILE . " " . $dst . " " . TEXT_CHECKSUM_PASSED . ".</font><br>\n";
                    }
                }
            }
        }
        if ($force == 1) {
            if ($forceBackup) {
                $zipForce->close();
            }
            unset($zipForce);
            $checked = true;
        }
        if ($checked) {
            $zip = new \ZipArchive();
            if ($zip->open($path . 'uploads' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . $zipFile, \ZipArchive::CREATE) === true) {// $zipFile must contain path to new backup file
                foreach ($rules as $src) {
                    $dst = str_replace('|', DIRECTORY_SEPARATOR, $src->path);
                    switch ($src->action) {
                        case 'copy':
                            if ($src->type == 'dir' && is_dir($pathP . DIRECTORY_SEPARATOR . $dst)) {
                                $zip->addEmptyDir($dst);
                                $scanner = new \common\classes\DirScanner($pathP . DIRECTORY_SEPARATOR . $dst);
                                $result = $scanner->run();
                                foreach ($result as $pathSub => $crc) {
                                    $dstSub = str_replace('|', DIRECTORY_SEPARATOR, $pathSub);
                                    if (is_dir($pathP . DIRECTORY_SEPARATOR . $dstSub)) {
                                        $zip->addEmptyDir($dstSub);
                                    } else if (is_file($pathP . DIRECTORY_SEPARATOR . $dst . DIRECTORY_SEPARATOR . $dstSub)) {
                                        $zip->addFile($pathP . DIRECTORY_SEPARATOR . $dst . DIRECTORY_SEPARATOR . $dstSub, $dst . DIRECTORY_SEPARATOR . $dstSub);
                                    }
                                }
                                unset($scanner);
                            }
                            break;
                        case 'modify':
                        case 'delete':
                            if ($src->type == 'file' && is_file($pathP . DIRECTORY_SEPARATOR . $dst)) {
                                $zip->addFile($pathP . DIRECTORY_SEPARATOR . $dst, $dst);
                            }
                            break;
                        default:
                            break;
                    }
                    
                    
                }
                $zip->close();
            }
            unset($zip);
        }
        return $checked;
    }

    private function runFileDst($rules, $zipFile, $pathP, $echo = false) 
    {
//        $pathP = \yii\helpers\BaseFileHelper::normalizePath($pathP, '/');
        $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR;
        $zip = new \ZipArchive();
        if ($zip->open($path . 'uploads' . DIRECTORY_SEPARATOR . $zipFile) === true) {
            foreach ($rules as $src) {
                $dst = str_replace('|', '/', $src->path); // don't use DIRECTORY_SEPARATOR here
                if (in_array($dst, $this->dst_file_ignore)) {
                    if ($echo) echo "<font color='red'>$dst ignored.</font><br>\n";
                    continue;
                }
                switch ($src->action) {
                    case 'add':
                        if ($src->type == 'dir') {
                            if (!is_dir($pathP . DIRECTORY_SEPARATOR . $dst)) {
                                @mkdir($pathP . DIRECTORY_SEPARATOR . $dst);
                                if ($echo) echo "<font color='blue'>" . TEXT_DIRECTORY . " $dst " . TEXT_ADDED . ".</font><br>\n";
                            }
                        }
                        if ($src->type == 'file') {
                            if (!$zip->extractTo($pathP, $dst)) {
                                $errorMsg = sprintf('Error extracting %s: %s', $dst, $zip->getStatusString() );
                                \Yii::warning($errorMsg);
                                if ($echo) echo "<font color='red'>$errorMsg</font><br>\n";
                            } else {
                                if ($echo) echo "<font color='blue'>" . TEXT_FILE . " $dst " . TEXT_ADDED . ".</font><br>\n";
                            }
                        }
                        break;
                    case 'modify':
                        if ($src->type == 'file') {
                            $fileName = $pathP . DIRECTORY_SEPARATOR . $dst;
                            @rename($fileName, $fileName . '_old'); // to avoid 'Failed to open stream: Permission denied' under Windows
                            @unlink($fileName . '_old');
                            if (!$zip->extractTo($pathP, $dst)) {
                                $errorMsg = sprintf('Error extracting %s: %s', $dst, $zip->getStatusString() );
                                \Yii::warning($errorMsg);
                                if ($echo) echo "<font color='red'>$errorMsg</font><br>\n";
                            } else {
                                if ($echo) echo "<font color='blue'>" . TEXT_FILE . " $dst " . TEXT_MODIFIED . ".</font><br>\n";
                            }
                        }
                        break;
                    case 'copy':
                        if ($src->type == 'dir') {
                            for($i = 0; $i < $zip->numFiles; $i++) {
                                $entry = $zip->getNameIndex($i);
                                if (strpos($entry, $dst) === 0) {
                                    if (!$zip->extractTo($pathP, $entry)) {
                                        $errorMsg = sprintf('Error extracting %s: %s', $entry, $zip->getStatusString() );
                                        \Yii::warning($errorMsg);
                                        if ($echo) echo "<font color='red'>$errorMsg</font><br>\n";
                                    }
                                }
                            }
                            if ($echo) echo "<font color='blue'>" . TEXT_DIRECTORY . " $dst " . TEXT_COPIED . ".</font><br>\n";
                        }
                        break;
                    case 'delete':
                        $fn = $pathP . DIRECTORY_SEPARATOR . $dst;
                        if ($src->type == 'dir' && is_dir($fn)) {
                            if (!@rmdir($fn)) {
                                $errorMsg = "Can't remove dir $fn: " . error_get_last()['message']??'unknown';
                                \Yii::warning($errorMsg . "Dir contains: " . implode("\n", glob($fn . '/*')) . "\n" . implode("\n", glob($fn . '/.*')));
                                if ($echo) echo "<font color='red'>$errorMsg</font><br>\n";
                            } else {
                                if ($echo) echo "<font color='blue'>" . TEXT_DIRECTORY . " $dst " . TEXT_DELETED . ".</font><br>\n";
                            }
                        }
                        if ($src->type == 'file' && is_file($fn)) {
                            if (!@unlink($fn)) {
                                $errorMsg = "Can't remove file $fn: " . error_get_last()['message']??'unknown';
                                \Yii::warning($errorMsg);
                                if ($echo) echo "<font color='red'>$errorMsg</font><br>\n";
                            } else {
                                if ($echo) echo "<font color='blue'>" . TEXT_FILE . " $dst " . TEXT_DELETED . ".</font><br>\n";
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
            if (!@$zip->close()) {
                $errorMsg = sprintf('Error closing zip: %s', $zip->getStatusString() );
                \Yii::warning($errorMsg);
            }
        }
        unset($zip);
    }
    
    private function revertFileDst($rules, $zipFile, $pathP, $echo = false) 
    {
        $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR;
        $zip = new \ZipArchive();
        $canUseZipForRevert = ($zip->open($path . 'uploads' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . $zipFile) === true);// $zipFile must contain deleted files
        $rules = array_reverse($rules);
        foreach ($rules as $src) {
            $dst = str_replace('|', DIRECTORY_SEPARATOR, $src->path);
            switch ($src->action) {
                case 'add':
                    if ($src->type == 'dir') {//delete
                        if (is_dir($pathP . DIRECTORY_SEPARATOR . $dst)) {
                            @rmdir($pathP . DIRECTORY_SEPARATOR . $dst . DIRECTORY_SEPARATOR);
                            if ($echo) echo TEXT_DIRECTORY . " $dst deleted.<br>\n";
                        }
                    }
                    if ($src->type == 'file') {//delete
                        @unlink($pathP . DIRECTORY_SEPARATOR . $dst);
                        if ($echo) echo TEXT_FILE . " $dst deleted.<br>\n";
                    }
                    break;
                case 'modify':
                    if ($src->type == 'file') {//restore from backup
                        @unlink($pathP . DIRECTORY_SEPARATOR . $dst);
                        if ($canUseZipForRevert) {
                            $zip->extractTo($pathP, $dst);
                            if ($echo) echo TEXT_FILE . " $dst restored.<br>\n";
                        }
                    }
                    break;
                case 'copy':
                    if ($src->type == 'dir') {//delete
                        $this->delTree($pathP . DIRECTORY_SEPARATOR . $dst . DIRECTORY_SEPARATOR);
                        if ($echo) echo TEXT_DIRECTORY . " $dst deleted.<br>\n";
                        if ($canUseZipForRevert) {
                            for($i = 0; $i < $zip->numFiles; $i++) {
                                $entry = $zip->getNameIndex($i);
                                if (strpos($entry, $dst) === 0) {
                                    $zip->extractTo($pathP, $entry);
                                }
                            }
                            if ($echo) echo TEXT_DIRECTORY . " $dst copied.<br>\n";
                        }
                    }
                    break;
                case 'delete':
                    if ($src->type == 'dir' && !is_dir($pathP . DIRECTORY_SEPARATOR . $dst)) {//add
                        @mkdir($pathP . DIRECTORY_SEPARATOR . $dst);
                        if ($echo) echo TEXT_DIRECTORY . " $dst added.<br>\n";
                    }
                    if ($src->type == 'file' && !is_file($pathP . DIRECTORY_SEPARATOR . $dst)) {//restore from backup
                        if ($canUseZipForRevert) {
                            $zip->extractTo($pathP, $dst);
                            if ($echo) echo TEXT_FILE . " $dst added.<br>\n";
                        }
                    }
                    break;
                default:
                    break;
            }
            
        }
        if ($canUseZipForRevert) {
            $zip->close();
            @unlink($path . 'uploads' . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . $zipFile);
        }
        unset($zip);
    }
    
    private function doInstallClass($class, $selected_platform_id = 0, $acl = 0) 
    {
        if (class_exists($class)) {
            $module = new $class;
            $exportSettings = [];
            if (method_exists($module, 'remove')) {
                if (method_exists($module, 'keys')) {
                    $keys = $module->keys();
                    $rows = \common\models\PlatformsConfiguration::find()
                        ->select(['configuration_value', 'configuration_key'])
                        ->where(['platform_id' => $selected_platform_id])
                        ->andWhere(['IN', 'configuration_key', $keys])
                        ->all();
                    foreach ($rows  as $row) {
                        $exportSettings['keys'][$row['configuration_key']] = $row['configuration_value'];
                    }
                    if (method_exists($module, 'get_extra_params')) {
                        $extra_params = $module->get_extra_params($selected_platform_id);
                        if (count($extra_params) > 0) {
                            $exportSettings['extra_params'] = $extra_params;
                        }
                    }
                }
                // $module->remove($selected_platform_id);
            }
            if (method_exists($module, 'install')) {
                if ($acl > 0) {
                     if (isset($module->isExtension)) {
                        switch ($acl) {
                            case 'all':
                                $access_levels = [];
                                foreach (\common\models\AccessLevels::find()->select(['access_levels_id'])->asArray()->all() as $al) {
                                    $access_levels[] = $al['access_levels_id'];
                                }
                                $module->assign_to_access_levels = $access_levels;
                                break;
                            case 'my':
                                global $access_levels_id;
                                $module->assign_to_access_levels = $access_levels_id;
                                break;
                            default:
                                $module->assign_to_access_levels = 0;
                                break;
                        }
                    }
                }
                $module->install($selected_platform_id);
                if (method_exists($module, 'save_config') && is_array($exportSettings['keys']??null)) {
                    $module->save_config($selected_platform_id, $exportSettings['keys']);
                    if (method_exists($module, 'set_extra_params') && isset($exportSettings['extra_params'])) {
                        $module->set_extra_params($selected_platform_id, $exportSettings['extra_params']);
                    }
                }
                if (method_exists($module, 'enable_module')) {
                    $module->enable_module($selected_platform_id, true);
                }
            }
            unset($exportSettings);
        }
    }
    
    private function doUninstallClass($class, $selected_platform_id = 0, $prevVer = null)
    {
        if (class_exists($class)) {
            $module = new $class;
            if (is_null($prevVer)) {
                if (method_exists($module, 'remove')) {
                    $module->remove($selected_platform_id);
                }
            } else {
                if (method_exists($module, 'downgrade')) {
                    $module->downgrade($prevVer);
                }
            }
        }
    }
    
    private function doRecalcModuleSort($action, $module, $type, $selected_platform_id)
    {
        switch ($type) {
            case 'payment':
                $module_key = 'MODULE_PAYMENT_INSTALLED';
                break;
            case 'shipping':
                $module_key = 'MODULE_SHIPPING_INSTALLED';
                break;
            case 'totals':
                $module_key = 'MODULE_ORDER_TOTAL_INSTALLED';
                break;
            case 'label':
                $module_key = 'MODULE_LABEL_INSTALLED';
                break;
            default:
                $module = '';
                break;
        }
        if (!empty($module)) {
            $module = \common\helpers\Output::mb_basename($module);
            $conf = \common\models\PlatformsConfiguration::findOne(['configuration_key' => tep_db_input($module_key), 'platform_id' => intval($selected_platform_id)]);
            if (!empty($conf)) {
                $sorted = explode(';', $conf->configuration_value);
                if ($action == 'add') {
                    if (!in_array($module . '.php', $sorted)) {
                        $sorted[] = $module . '.php';
                    }
                }
                if ($action == 'delete') {
                    if ($key = array_search($module. '.php', $sorted) !== false) {
                        unset($sorted[$key]);
                    }
                }
                $newSort = implode(';', $sorted);
                if ($newSort != $conf->configuration_value) {
                    $conf->configuration_value = $newSort;
                    $conf->save(false);
                }
             }
        }
    }
    
    private function doInstallRecord($filename, $type, $class, $version, $data)
    {
        $record = new \common\models\Installer();
        $record->data = serialize($data);
        $record->filename = $filename;
        $record->date_added = date('Y-m-d H:i:s');
        $record->archive_type = $type;
        $record->archive_class = $class;
        list($major, $minor, $patch) = array_pad( explode('.', $version), 3, '0');
        $record->archive_version = intval($major) + intval($minor)/100 + intval($patch)/10000;
        return $record->save(false);
    }
    
    private function getStorageKey()
    {
        global $login_id;
        $admin = \common\models\Admin::findOne($login_id);
        //$storageKey = \Yii::$app->params['appStorage.key'];
        return $admin->storage_key ?? '';
    }

    public function actionIndex() 
    {
        
        \common\helpers\Translation::init('admin/easypopulate');
        
        defined('TEXT_CLEANUP_INTRO') or define('TEXT_CLEANUP_INTRO', 'Are you sure you want to cleanup? All backups and unused archives will be deleted. Also deletion will make it impossible to revert to the previous version.');

        $this->selectedMenu = array('settings', 'logging');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('install/'), 'title' => BOX_HEADING_INSTALL);
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['install/add-storage-key']) . '" class="create_item create_item_popup">' . TEXT_STORE_KEY . '</a>';
        $this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('install/reset-storage-key').'" onclick="return confirm(\'' . TEXT_RESET_STORAGE_KEY . '\')" class="create_item"><i class="icon-refresh"></i>' . TEXT_RESET . '</a>';
        
        $this->view->headingTitle = BOX_HEADING_INSTALL;

        $messages = [];
        
        if ( Yii::$app->request->isPost ) {
            if (isset($_FILES['data_file']['name'])) {
                $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
                $uploadfile = $path . $this->basename($_FILES['data_file']['name']);
                
                $ext = substr(basename($uploadfile), strrpos(basename($uploadfile), '.') + 1);
                if ($ext != 'zip') {
                    $messages[] = 'Wrong file format';
                } elseif ( !is_writeable(dirname($uploadfile)) ) {
                    $messages[] = 'Directory "'.$path.'" not writeable';
                } elseif(!is_uploaded_file($_FILES['data_file']['tmp_name']) || filesize($_FILES['data_file']['tmp_name'])==0){
                    $messages[] = 'File upload error';
                } elseif (move_uploaded_file($_FILES['data_file']['tmp_name'], $uploadfile)) {
                    $messages[] = 'File successfully uploaded';
                } else {
                    $messages[] = 'Cant upload file';
                }
                
            }
        }
        
        $this->view->filters = new \stdClass();
        
        $this->view->filters->search = Yii::$app->request->get('search', '');
        
        $this->view->filters->type = Yii::$app->request->get('type', '');;
        
        $selectedRootDirectoryId = Yii::$app->request->get('set', 'selection');
        $directories = [];
        
        $directories[] = [
            'id' => 'selection',
            'text' => TEXT_SELECTION,
            'link' => Yii::$app->urlManager->createUrl(['install/','set'=> 'selection']),
        ];
        $directories[] = [
            'id' => 'library',
            'text' => TEXT_MY_LIB,
            'link' => Yii::$app->urlManager->createUrl(['install/','set'=> 'library']),
        ];
        $directories[] = [
            'id' => 'modules',
            'text' => TEXT_INSTALLED,
            'link' => Yii::$app->urlManager->createUrl(['install/','set'=> 'modules']),
        ];
        /*$directories[] = [
            'id' => 'settings',
            'text' => TEXT_MY_SETTINGS,
            'link' => Yii::$app->urlManager->createUrl(['install/','set'=> 'settings']),
        ];*/
        $directories[] = [
            'id' => 'updates',
            'text' => TEXT_SYSTEM_UPDATE,
            'link' => Yii::$app->urlManager->createUrl(['install/','set'=> 'updates']),
        ];
        
        $storageUrl = \Yii::$app->params['appStorage.url'];
        $storageKey = $this->getStorageKey();
        $secKeyGlobal = md5(\Yii::$app->db->dsn . (defined('INSTALLED_MICROTIME') ? INSTALLED_MICROTIME : ''));
        if (!isset(\Yii::$app->params['secKey.global']) OR (\Yii::$app->params['secKey.global'] != $secKeyGlobal)) {
            $message = (defined('MESSAGE_KEY_DOMAIN_WANING')
                ? constant('MESSAGE_KEY_DOMAIN_WANING')
                : 'Warning: Security keys were generated for a different domain! Update required. Please change \'security store key\' to the actual value: %s.'
            );
            if (strpos($message, '%s') === false) {
                $message .= ' (%s)';
            }
            $message = sprintf($message, $secKeyGlobal);
            $messages[] = $message;
        } elseif (empty($storageKey)) {
            
            $showEmptyKeyIntro = true;
            if ($request = curl_init()) {
                curl_setopt($request, CURLOPT_URL, $storageUrl . 'app-api-server');
            
                // for testing
                curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
                if (defined('CURLOPT_SSL_VERIFYSTATUS')) { // Added in cURL 7.41.0
                    curl_setopt($request, CURLOPT_SSL_VERIFYSTATUS, false);
                }

                curl_setopt($request, CURLOPT_TIMEOUT_MS, 30000);
                curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($request, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $storageKey . ':' . $secKeyGlobal
                ));

                $return = curl_exec($request);
                $response = curl_getinfo($request);
                curl_close($request);
                
                if ($response['http_code'] == 406 ) {
                    $result = json_decode($return, true);
                    if (isset($result['code']) && $result['code'] == 428) {
                        $ownerName = $result['message'];
                        $message = (defined('MESSAGE_KEY_DOMAIN_INFO2')
                            ? constant('MESSAGE_KEY_DOMAIN_INFO2')
                            : 'This shop is already registered to %3$s and is not shared key to all administrators. You need to connect it using your own credentials.<br>
                                If your already registered with us and %3$s, approved your storage key, please insert \'storage\' key value. If you do not remember your \'storage\' key - please login at <a target="_blank" href="%1$s">application shop</a> with your credentials and copy it from there.<br>
                                If you not registered with us, please visit <a target="_blank" href="%1$s">application shop</a>, register your account and put there your \'security store key\'.<br>
                                You \'secutiry store key\' for this shop is [%2$s].<br>
                                After registration, wait for confirmation by %3$s. If this approve take a lot, you may e-mail him directly. After confirmation insert the received \'storage\' key (<a href="javascript:void(0);" onclick="$(\'.create_item_popup\').click();">use button on this page</a>) value.'
                        );
                        $messages[] = sprintf($message, $storageUrl . 'account?return', $secKeyGlobal, $ownerName);
                        $showEmptyKeyIntro = false;
                    }
                }
            }
            
            if ($showEmptyKeyIntro) {
                $message = (defined('MESSAGE_KEY_DOMAIN_INFO')
                    ? constant('MESSAGE_KEY_DOMAIN_INFO')
                    : 'It is looks like your store is not connected to our <a target="_blank" href="%1$s">application shop</a>.<br>If your already registered with us, please insert \'storage\' key value. If you do not remember your \'storage\' key - please login at <a target="_blank" href="%1$s">application shop</a> with your credentials and copy it from there.<br>If you not registered with us, please visit <a target="_blank" href="%1$s">application shop</a>, register your account and put there your \'security store key\'.<br>You \'secutiry store key\' for this shop is [%2$s].<br>After registration insert the received \'storage\' key (<a href="javascript:void(0);" onclick="$(\'.create_item_popup\').click();">use button on this page</a>) value.'
                );
                $messages[] = sprintf($message, $storageUrl . 'account?return', $secKeyGlobal);
            }
        } else {
            if ($request = curl_init()) {
                curl_setopt($request, CURLOPT_URL, $storageUrl . 'app-api-server');
            
                // for testing
                curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
                if (defined('CURLOPT_SSL_VERIFYSTATUS')) { // Added in cURL 7.41.0
                    curl_setopt($request, CURLOPT_SSL_VERIFYSTATUS, false);
                }

                curl_setopt($request, CURLOPT_TIMEOUT_MS, 30000);
                curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($request, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $storageKey . ':' . $secKeyGlobal
                ));

                curl_exec($request);
                $response = curl_getinfo($request);
                curl_close($request);
                
                if ($response['http_code'] != 200 ) {
                    $message = (defined('MESSAGE_KEY_DOMAIN_ERROR')
                        ? constant('MESSAGE_KEY_DOMAIN_ERROR')
                        : 'Error: Your \'storage\' key is wrong. Please login at <a target="_blank" href="%1$s">application shop</a> with your credentials and copy it from there. You \'secutiry store key\' for this shop is [%2$s].'
                    );
                    $messages[] = sprintf($message, $storageUrl . 'account?return', $secKeyGlobal);
                }
            }
        }
        
        $success = '';
        $types = [];
        if (($selectedRootDirectoryId == 'library' || $selectedRootDirectoryId == 'selection') && count($messages) == 0) {
            
            $message = (defined('MESSAGE_KEY_DOMAIN_OK')
                ? constant('MESSAGE_KEY_DOMAIN_OK')
                : 'Your store successfully connected to our <a target="_blank" href="%1$s">application shop</a>. You \'secutiry store key\' for this shop is [%2$s].'
            );
            $success = sprintf($message, $storageUrl, $secKeyGlobal);
        }

        if (!empty($storageUrl)) {
            $context = null;
            if (\common\helpers\System::isDevelopment()) {
                $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]]);
            }
            $response = @file_get_contents($storageUrl . 'app-api-types.json', false, $context);
            $result = json_decode($response, true);
        }
        if (isset($result['types'])) {
            $types = $result['types'];
        }
            
        $platforms = [0 => TEXT_NONE, -1 => TEXT_ALL_PLATFORMS] + \yii\helpers\ArrayHelper::map(
            \common\models\Platforms::find()->select(['platform_id', 'platform_name'])->where(['is_virtual' => 0, 'is_marketplace' => 0])->asArray()->all(),
            'platform_id', 'platform_name'
        );
        
        return $this->render('index', [
            'messages' => $messages,
            'success' => $success,
            'directories' => $directories,
            'selectedRootDirectoryId' => $selectedRootDirectoryId,
            'job_list_url' => Yii::$app->urlManager->createUrl(['install/files-list']),
            'store_list_url' => Yii::$app->urlManager->createUrl(['install/store-list']),
            'types' => $types,
            'platforms' => $platforms,
        ]);
    }
    
    public function actionAddStorageKey()
    {
        $this->layout = false;
        return $this->render('add-storage-key.tpl', [
            'storageKey' => $this->getStorageKey(),
        ]);
    }
    
    
    public function actionResetStorageKey(){
        global $login_id;
        $admin = \common\models\Admin::findOne($login_id);
        if ($admin instanceof \common\models\Admin) {
            $admin->storage_key = '';
            $admin->save(false);
        }
        return $this->redirect(Yii::$app->urlManager->createUrl('install/'));
    }
    
    public function actionSubmitStorageKey()
    {
        $storekey = Yii::$app->request->post('storekey', '');
        $button = Yii::$app->request->post('button', '');
        if ($button == 'all') {
            \common\models\Admin::updateAll(['storage_key' => $storekey]);
        } else {
            global $login_id;
            $admin = \common\models\Admin::findOne($login_id);
            if ($admin instanceof \common\models\Admin) {
                $admin->storage_key = $storekey;
                $admin->save(false);
            }
        }
        return $this->redirect(Yii::$app->urlManager->createUrl('install/'));
    }
    
    public function actionStoreList()
    {
        $start = (int)Yii::$app->request->post('start',0);
        $length = (int)Yii::$app->request->post('length', 9);
        
        $type = \Yii::$app->request->post('type', '');
        $search = \Yii::$app->request->post('search', '');
        $sort = \Yii::$app->request->post('sort_by', '');
        
        $this->layout = false;
        $recordsTotal = 0;
        $recordsFiltered = 0;
        
        $items = [];
        
        global $login_id;
        $secKeyGlobal = md5(\Yii::$app->db->dsn . (defined('INSTALLED_MICROTIME') ? INSTALLED_MICROTIME : ''));
        $storageUrl = \Yii::$app->params['appStorage.url'];
        $storageKey = $this->getStorageKey();
        if (!isset(\Yii::$app->params['secKey.global']) OR (\Yii::$app->params['secKey.global'] != $secKeyGlobal)) {
            // wrong security store key
        } elseif (empty($storageKey) || empty($storageUrl)) {
            // wrong storage key or url
        } else {
            \common\models\InstallListCache::deleteAll('date_added <= :date_added', [':date_added' => date("Y-m-d H:i:s", strtotime('- 1 hour'))]);
            $result = false;
            $cache = \common\models\InstallListCache::find()
                    ->where(['admin_id' => $login_id])
                    ->andWhere(['offset' => $start])
                    ->andWhere(['limit' => $length])
                    ->andWhere(['type' => $type])
                    ->andWhere(['search' => $search])
                    ->andWhere(['sort' => $sort])
                    ->one();
            if ($cache instanceof \common\models\InstallListCache) {
                $result = json_decode(stripslashes($cache->return), true);
            } else if ($request = curl_init()) {
                curl_setopt($request, CURLOPT_URL, $storageUrl . 'app-api-server/products');

                // for testing
                curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
                if (defined('CURLOPT_SSL_VERIFYSTATUS')) { // Added in cURL 7.41.0
                    curl_setopt($request, CURLOPT_SSL_VERIFYSTATUS, false);
                }

                curl_setopt($request, CURLOPT_TIMEOUT_MS, 30000);
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($request, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $storageKey . ':' . $secKeyGlobal
                ));

                $postField = [
                    'offset' => $start,
                    'limit' => $length,
                    'type' => $type,
                    'search' => $search,
                    'sort' => $sort,
                ];
                $postFieldArray = json_encode($postField);

                curl_setopt($request, CURLOPT_POSTFIELDS, $postFieldArray);
                $return = curl_exec($request);
                $response = curl_getinfo($request);
                curl_close($request);

                if ($response['http_code'] == 200) {
                    if ($sort != 'installed') {
                        $cache = new \common\models\InstallListCache();
                        $cache->loadDefaultValues();
                        $cache->setAttributes($postField, false);
                        $cache->admin_id = $login_id;
                        $cache->return = tep_db_input($return);
                        $cache->date_added = date('Y-m-d H:i:s');
                        $cache->save(false);
                    }
                    $result = json_decode($return, true);
                }
            }
            
            if (isset($result['products'])) {
                $recordsTotal = $result['total'];
                $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
                foreach ($result['products'] as $product) {
                    $deployed = 0;// Install or Discover
                    if (!empty($product['filename']) && file_exists($path . $product['filename'])) {
                        $deployed = 1;// Downloaded (Not installed)
                    }
                    $archive_version = (float)$product['archive_version'];
                    $archive_type = (string)$product['archive_type'];
                    $archive_class = (string)$product['archive_class'];
                    $check = \common\models\Installer::find()
                            ->select(['max(archive_version) as version'])
                            ->where(['archive_type' => $archive_type])
                            ->andWhere(['archive_class' => $archive_class])
                            ->asArray()
                            ->one();
                    
                    if (isset($check['version']) && $check['version'] == $archive_version) {
                        $deployed = 2;// Installed
                    }
                    if (isset($check['version']) && $check['version'] < $archive_version) {
                        $deployed = 3;// Update
                    }
                    
                    $recordsFiltered++;
                    $product['deployed'] = $deployed;
                    $items[] = $product;
                }
            }
            
            
        }
        
        $pages = [];
        if ($recordsTotal > $recordsFiltered) {
            for ($p=0;$p<ceil($recordsTotal/$length);$p++) {
                $pages[] = $p;
            }
        }
        
        return $this->render('store-list', [
            'items' => $items,
            'module_list_url' => Yii::$app->urlManager->createUrl(['install/', 'set' => 'modules']),
            'pages' => $pages,
            'start' => $start,
            'length' => $length,
        ]);
    }
    
    public function actionUploadFileInfo() 
    {
        $this->layout = false;
        $id = (int) Yii::$app->request->get('id', 0);
        if ($id > 0) {

            if ($request = curl_init()) {
                $storageUrl = \Yii::$app->params['appStorage.url'];
                $storageKey = $this->getStorageKey();
                $secKeyGlobal = md5(\Yii::$app->db->dsn . (defined('INSTALLED_MICROTIME') ? INSTALLED_MICROTIME : ''));
                curl_setopt($request, CURLOPT_URL, $storageUrl . 'app-api-server/product-info');

                // for testing
                curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
                if (defined('CURLOPT_SSL_VERIFYSTATUS')) { // Added in cURL 7.41.0
                    curl_setopt($request, CURLOPT_SSL_VERIFYSTATUS, false);
                }

                curl_setopt($request, CURLOPT_TIMEOUT_MS, 30000);
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($request, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $storageKey . ':' . $secKeyGlobal
                ));

                $postFieldArray = [
                    'id' => $id,
                ];
                $postFieldArray = json_encode($postFieldArray);

                curl_setopt($request, CURLOPT_POSTFIELDS, $postFieldArray);
                //$result = curl_exec($request);
                $result = json_decode(curl_exec($request), true);
                $response = curl_getinfo($request);
                curl_close($request);

                if ($response['http_code'] == 200) {

                    $packagesSelectedList = $result['packagesSelectedList'];
                    $readyForInstall = $result['readyForInstall'];
                    $platformSelection = $result['platformSelection'];
                    $aclSelection = $result['aclSelection'];
                    $packagesDependedList = $result['packagesDependedList'];

                    \common\helpers\Translation::init('admin/modules');
                    
                    $platforms = [0 => TEXT_NONE, -1 => TEXT_ALL_PLATFORMS] + \yii\helpers\ArrayHelper::map(
                                    \common\models\Platforms::find()->select(['platform_id', 'platform_name'])->where(['is_virtual' => 0, 'is_marketplace' => 0])->asArray()->all(),
                                    'platform_id', 'platform_name'
                    );
                    
                    return $this->render('upload-file-info', [
                        'id' => $id,
                        'packagesSelectedList' => $packagesSelectedList,
                        'readyForInstall' => $readyForInstall,
                        'platformSelection' => $platformSelection,
                        'aclSelection' => $aclSelection,
                        'packagesDependedList' => $packagesDependedList,
                        'platforms' => $platforms,
                    ]);
                }
            }
        }
        echo 'Failed to download application.';
    }

    public function actionUploadFile() 
    {
        $this->layout = false;
        $status = 'fail';
        $id = (int)Yii::$app->request->post('id',0);
        $this->deployLog = [];
        if ($id > 0) {
            $this->resetReCacheFlags();
            if ($file = $this->getFileWithDependencies('id', $id)) {
                $platform_id = (int)Yii::$app->request->post('platform',0);
                $acl = (string)Yii::$app->request->post('acl', '');
                $readyForInstall = (int)Yii::$app->request->post('readyForInstall', 0);
                if ($readyForInstall) {
                    if ($this->installFileWithDependencies($file, ['platform_id' => $platform_id, 'acl' => $acl])) {
                        $status = 'success';
                    }
                    $depended = (array)Yii::$app->request->post('depended', []);
                    if (is_array($depended)) {
                        foreach ($depended as $depid) {
                            if ($subfile = $this->getFileWithDependencies('id', $depid)) {
                                $this->installFileWithDependencies($subfile, ['platform_id' => $platform_id, 'acl' => $acl]);
                            }
                        }
                    }
                
                }
                $this->runSystemReCache();
            }
        }
        $uploadInfo = implode("<br>", $this->deployLog);
        if ($status == 'success') {
            $packagesSynergyList = [];
            if ($request = curl_init()) {
                $storageUrl = \Yii::$app->params['appStorage.url'];
                $storageKey = $this->getStorageKey();
                $secKeyGlobal = md5(\Yii::$app->db->dsn . (defined('INSTALLED_MICROTIME') ? INSTALLED_MICROTIME : ''));
                curl_setopt($request, CURLOPT_URL, $storageUrl . 'app-api-server/product-synergy');

                // for testing
                curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
                if (defined('CURLOPT_SSL_VERIFYSTATUS')) { // Added in cURL 7.41.0
                    curl_setopt($request, CURLOPT_SSL_VERIFYSTATUS, false);
                }

                curl_setopt($request, CURLOPT_TIMEOUT_MS, 30000);
                curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($request, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer ' . $storageKey . ':' . $secKeyGlobal
                ));

                $postFieldArray = [
                    'id' => $id,
                ];
                $postFieldArray = json_encode($postFieldArray);

                curl_setopt($request, CURLOPT_POSTFIELDS, $postFieldArray);
                //$result = curl_exec($request);
                $result = json_decode(curl_exec($request), true);
                $response = curl_getinfo($request);
                curl_close($request);

                if ($response['http_code'] == 200) {

                    $packagesSynergyList = $result['packagesSynergyList'];
                    

                    
                }
            }
            return $this->render('upload-file-success', [
                    'message' => APP_INSTALL_OK,
                    'uploadInfo' => $uploadInfo,
                    'packagesSynergyList' => $packagesSynergyList,                        
                ]);
            
        } else {
            echo $uploadInfo . '<br>';
            echo APP_INSTALL_FAIL;
        }
        
        /*Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'status' => $status,
        ];*/
    }
        
    public function actionFilesList()
    {
        $this->layout = false;
        $formatter = new \yii\i18n\Formatter();
        
        $version = (defined('MIGRATIONS_DB_REVISION') ? MIGRATIONS_DB_REVISION : '');
        $recordsTotal = 0;
        $recordsFiltered = 0;
        $start = (int)Yii::$app->request->get('start',0);
        $length = (int)Yii::$app->request->get('length',25);
        
        $search_word = '';
        $search_array = Yii::$app->request->get('search');
        if (is_array($search_array) && isset($search_array['value']) && !empty($search_array['value']) ) {
            $search_word = tep_db_prepare_input($search_array['value']);
        }
        $files = [];
        
        $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        if ($dir = @dir($path)) {
            while ($file = $dir->read()) {
                if (!empty($search_word)) {
                    if (false === stripos($file, $search_word)) {
                        continue;
                    }
                }
                
                $ext = substr($file, strrpos($file, '.') + 1);
                if ($ext == 'zip') {
                    
                    $deployed = false;
                    $canDeploy = true;
                    $type = 'unknown';
                    $dclass = '';
                    $dtype = '';
                    $appName = '';
                    $req = '';
                    $choosePlatform = 0;
                    
                    $canRevert = false;
                    $canDelete = true;
                    
                    $zip = new \ZipArchive();
                    if ($zip->open($path . $file) === true) {
                        $json = $zip->getFromName('distribution.json');
                        $json = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',$json);
                        if (!empty($json)) {
                            $distribution = json_decode($json);
                            $dtype = (string)($distribution->type ?? '');
                            $dclass = (string)($distribution->class ?? '');
                            $appName = (string)($distribution->name ?? '');
                            $type = '<div class="ord-location">'
                                    . $distribution->type
                                    . '<div class="ord-total-info ord-location-info"><div class="ord-box-img"></div><b>' 
                                    . \yii\helpers\Html::encode($distribution->name) 
                                    . '</b>' 
                                    . \yii\helpers\Html::encode($distribution->description) 
                                    . '<br>Vesion: '
                                    . \yii\helpers\Html::encode($distribution->version) 
                                    . '<br>'
                                    . '</div></div>';
                            if (isset($distribution->require->version)) {
                                $versionApplicable = $distribution->require->version_applicable ?? 'equal';
                                switch ($versionApplicable) {
                                    case 'equal':
                                        if ($version == $distribution->require->version) {
                                            $req .= '<p style="color:green">Version: ' . \yii\helpers\Html::encode($distribution->require->version) . '</p>';
                                        } else {
                                            $req .= '<p style="color:red">Version: ' . \yii\helpers\Html::encode($distribution->require->version) . '</p>';
                                            $canDeploy = false;
                                        }                                        
                                        break;
                                    case 'greater-equal':
                                        if (intval($version) >= intval($distribution->require->version)) {
                                            $req .= '<p style="color:green">Version: ' . \yii\helpers\Html::encode($distribution->require->version) . ' or greater</p>';
                                        } else {
                                            $req .= '<p style="color:red">Version: ' . \yii\helpers\Html::encode($distribution->require->version) . ' or greater</p>';
                                            $canDeploy = false;
                                        }
                                        break;
                                    case 'less-equal':
                                        if (intval($version) <= intval($distribution->require->version)) {
                                            $req .= '<p style="color:green">Version: ' . \yii\helpers\Html::encode($distribution->require->version) . ' or less</p>';
                                        } else {
                                            $req .= '<p style="color:red">Version: ' . \yii\helpers\Html::encode($distribution->require->version) . ' or less</p>';
                                            $canDeploy = false;
                                        }
                                        break;
                                    default:
                                        break;
                                }
                                
                            }
                            if (isset($distribution->require->modules) && is_array($distribution->require->modules)) {
                                foreach ($distribution->require->modules as $subfile) {
                                    $record = \common\models\Installer::find()->where(['filename' => $subfile])->one();
                                    if ($record instanceof \common\models\Installer || self::isKnownRequireModule($subfile)) {
                                        $req .= '<p style="color:green">'.$subfile.'</p>';
                                    } elseif (is_file($path . $subfile)) {
                                        $req .= '<p style="color:yellow">'.$subfile.'</p>';
                                    } else {
                                        $req .= '<p style="color:red">'.$subfile.'</p>';
                                        $canDeploy = false;
                                    }
                                    unset($record);
                                }
                            }
                            if (isset($distribution->require->classes) && is_array($distribution->require->classes)) {
                                foreach ($distribution->require->classes as $classversion) {
                                    $recordQuery = \common\models\Installer::find()->where(['archive_class' => $classversion->name]);
                                    $cv = '';
                                    if (isset($classversion->min)) {
                                        list($major, $minor, $patch) = array_pad( explode('.', (string)$classversion->min), 3, 0);
                                        $archive_version = intval($major) + intval($minor)/100 + intval($patch)/10000;
                                        $recordQuery->andWhere(['>=', 'archive_version', $archive_version]);
                                        $cv .= ', v.' . $classversion->min . ' or greater';
                                    }
                                    if (isset($classversion->max)) {
                                        list($major, $minor, $patch) = array_pad( explode('.', (string)$classversion->max), 3, 0);
                                        $archive_version = intval($major) + intval($minor)/100 + intval($patch)/10000;
                                        $recordQuery->andWhere(['<=', 'archive_version', $archive_version]);
                                        $cv .= ', v.' . $classversion->max . ' or less';
                                    }
                                    $record = $recordQuery->one();
                                    if ($record instanceof \common\models\Installer) {
                                        $req .= '<p style="color:green">'.$classversion->name.$cv.'</p>';
                                    } else {
                                        $req .= '<p style="color:red">'.$classversion->name.$cv.'</p>';
                                        $canDeploy = false;
                                    }
                                    unset($record);
                                }
                            }
                            if (isset($distribution->require->platform) && (string)$distribution->require->platform == 'True') {
                                $choosePlatform = 1;
                            }
                        }
                        
                        
                        if ($dtype == 'translate') {
                            $choosePlatform = 0;
                            $json = $zip->getFromName('locale.json');
                            $json = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',$json);
                            if (!empty($json)) {
                                $locale = json_decode($json);
                                $lang = \common\models\Languages::find()->andWhere(['code' => (string)$locale->code])->one();
                                if ($lang instanceof \common\models\Languages) {
                                    $choosePlatform = 3;
                                } else {
                                    $choosePlatform = 2;
                                }
                            }
                        }
                        
                        
                        //$zip->extractTo($path);
                        $zip->close();
                    }
                    
                    $record = \common\models\Installer::find()->where(['filename' => $file])->one();
                    if ($record instanceof \common\models\Installer) {
                        $deployed = true;
                    }
                    
                    $fileNameCell = '<div style="white-space: nowrap"><a href="' . Yii::$app->urlManager->createUrl(['install/download-file', 'name' => $file]) . '" target="_blank"><i class="' . 'icon-upload' . '"></i></a> ' . $file . '</div>';
                    
                    
                    
                    switch ($dtype) {
                        case 'extension':
                        case 'design':
                        case 'translate':
                        case 'analytic':
                        case 'payment':
                        case 'shipping':
                        case 'totals':
                        case 'label':
                        case 'samples':
                        case 'configuration':
                        case 'system':
//                            $canDeploy = true;
                            if ($deployed) {
                                $canRevert = true;
                                $canDelete = false;
                            }
                            break;
                        case 'update':
//                            $canDeploy = true;
                            if ($deployed) {
                                if ((string)$distribution->version == MIGRATIONS_DB_REVISION) {
                                    $canRevert = true;
                                    $canDelete = false;
                                }
                            }
                            break;
                        default:
                            $canDeploy = false;
                            break;
                    }
                    
                    if ($canDeploy) {
                        list($major, $minor, $patch) = array_pad( explode('.', (string)$distribution->version), 3, 0);
                        $archive_version = intval($major) + intval($minor)/100 + intval($patch)/10000;
                        $check = \common\models\Installer::find()
                                ->select(['max(archive_version) as version'])
                                ->where(['archive_type' => $dtype])
                                ->andWhere(['archive_class' => $dclass])
                                ->asArray()
                                ->one();
                        
                        
                        if (isset($check['version']) && $check['version'] > $archive_version) {
                            $canDeploy = false;
                        }
                        if (isset($check['version']) && $check['version'] != $archive_version) {
                            $canRevert = false;
                        }
                    }
                    
                    $file_row = array(
                        \common\helpers\Date::datetime_short(date('Y-m-d H:i:s', filemtime($path . $file))),
                        $fileNameCell,
                        //$formatter->asShortSize(filesize($path . $file), 3),
                        $appName,
                        $type,
                        $req,
                        ($deployed ? '<span style="color:green;">deployed</span>' : '<span style="white-space: nowrap;color:red;">not deployed</span>'),
                        '<div class="job-actions">' .
                        //remove archive
                        ($canDelete ? '<a class="job-button" href="javascript:void(0);" onclick="return file_remove(\'' . $file . '\');"><i class="icon-trash iconTrash"></i></a>' : '') .
                        // deploy/revert
                        (!$deployed && $canDeploy ? '<a class="job-button" href="javascript:void(0);" onclick="return file_deploy(\'' . $file . '\', \'' . $choosePlatform . '\');"><i class="icon-plus-sign iconPlusSign"></i></a>' : '') .
                        ($canRevert ? '<a class="job-button" href="javascript:void(0);" onclick="return file_revert(\'' . $file . '\');"><i class="icon-remove-sign iconRemoveSign"></i></a>' : '') .
                        '</div>'
                    );
                    
                    $files[filemtime($path . $file).'_'.$recordsTotal] = $file_row;
                    
                    $recordsTotal++;
                    $recordsFiltered++;
                }
            }
        }
        krsort($files);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'data' => array_values($files),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        ];
    }
    
    
    public function actionDeployFile() 
    {
        $this->deployLog = [];
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $status = 'error';
        $filename = Yii::$app->request->post('name','');
        $platform_id = (int)Yii::$app->request->post('platform',0);
        $locale = (int)Yii::$app->request->post('locale',0);
        $this->resetReCacheFlags();
        if ($this->installFileWithDependencies($filename, ['platform_id' => $platform_id, 'locale' => $locale])) {
            $status = 'ok';
            $this->runSystemReCache();
        }
        $message = implode("<br>", $this->deployLog);
        Yii::$app->response->data = ['status' => $status, 'text' => $message];
    }
    
    public function actionRevertFile() 
    {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR;
        $filename = Yii::$app->request->post('name','');
        
        ob_start();
        
        $zip = new \ZipArchive();
        if ($zip->open($path . 'uploads' . DIRECTORY_SEPARATOR . $filename) === true) {
            $json = $zip->getFromName('distribution.json');
            $json = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',$json);
            if (!empty($json)) {
                $distribution = json_decode($json);
                $this->resetReCacheFlags();
                $status = 'fail';
                switch ($distribution->type) {
                    case 'extension':// Extension
                        $record = \common\models\Installer::find()->where(['filename' => $filename])->one();
                        if ($record instanceof \common\models\Installer) {
                            $oldData = unserialize($record->data);
                            $pathP = $path . 'lib' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'extensions';
                            if (isset($distribution->class)) {
                                $class = (string)$distribution->class;
                                $twoRecs = \common\models\Installer::find()->where(['archive_class' => $record->archive_class])->orderBy(['archive_version' => SORT_DESC])->limit(2)->all();
                                $prevVer = (count($twoRecs) == 2)? $twoRecs[1]->archive_version : null;
                                if (!class_exists($class)) {
                                    if ($ext = \common\helpers\Acl::checkExtension($class, 'always')) {
                                        $class = $ext;
                                    }
                                }
                                $this->doUninstallClass($class, 0, $prevVer);
                            }
                            $this->revertFileDst($oldData, $filename, $pathP, true);
                            
                            $record->delete();
                            $this->doSystem = true;
                            $this->doHooks = true;
                            $this->doMenu = true;
                            $status = 'success';
                        }
                        break;
                    case 'design':// Design
                        $record = \common\models\Installer::find()->where(['filename' => $filename])->one();
                        if ($record instanceof \common\models\Installer) {
                            $theme_name = \common\classes\design::pageName($distribution->name);
                            \backend\design\Theme::themeRemove ($theme_name, true);
                            $oldData = unserialize($record->data);
                            if (isset($oldData['id'])) {
                                \common\models\PlatformsToThemes::deleteAll(['platform_id' => (int) $oldData['id']]);
                            }
                            if (isset($oldData['platforms_to_themes'])) {
                                foreach ($oldData['platforms_to_themes'] as $platforms_to_themes) {
                                    $p2t = \common\models\PlatformsToThemes::find()
                                            ->where(['platform_id' => $platforms_to_themes['platform_id'], 'theme_id' => $platforms_to_themes['theme_id']])
                                            ->one();
                                    if ($p2t instanceof \common\models\PlatformsToThemes) {
                                        $p2t->is_default = $platforms_to_themes['is_default'] ?? 0;
                                    } else {
                                        $p2t = new \common\models\PlatformsToThemes();
                                        $p2t->loadDefaultValues();
                                        $p2t->setAttributes($platforms_to_themes, false);
                                    }
                                    $p2t->save(false);
                                }
                            }
                            $record->delete();
                            $status = 'success';
                        }
                        break;
                    case 'translate':// Translations
                        $record = \common\models\Installer::find()->where(['filename' => $filename])->one();
                        if ($record instanceof \common\models\Installer) {
                            $oldData = unserialize($record->data);
                            if (is_array($oldData)) {
                                foreach ($oldData as $old) {
                                    switch ($old['action']) {
                                        case 'deletelanguage':
                                            if (isset($old['language_id'])) {
                                                \common\helpers\Language::dropLanguage($old['language_id']);
                                            }
                                            break;
                                        case 'update':
                                            \common\models\Translation::updateAll(
                                                    ['translation_value' => $old['translation_value'], 'translated' => $old['translated']],
                                                    ['hash' => $old['hash'], 'language_id' => $old['language_id']]
                                                );
                                            break;
                                        case 'delete':
                                            \common\models\Translation::deleteAll(['hash' => $old['hash'], 'language_id' => $old['language_id']]);
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                            $record->delete();
                            $this->doSystem = true;
                            $status = 'success';
                        }
                        break;
                    case 'payment':// Payment
                        $moduleDir = 'orderPayment';
                    case 'shipping':// Shipping
                        if (empty($moduleDir)) {
                            $moduleDir = 'orderShipping';
                        }
                    case 'analytic':// Payment
                        if (empty($moduleDir)) {
                            $moduleDir = 'analytic';
                        }
                    case 'totals':// Order structure
                        if (empty($moduleDir)) {
                            $moduleDir = 'orderTotal';
                        }
                    case 'label':// Shipping label
                        if (empty($moduleDir)) {
                            $moduleDir = 'label';
                        }
                        $record = \common\models\Installer::find()->where(['filename' => $filename])->one();
                        if ($record instanceof \common\models\Installer) {
                            $oldData = unserialize($record->data);
                            $pathP = $path . 'lib' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduleDir;
                            if (isset($distribution->class)) {
                                $class = (string) $distribution->class;
                                foreach (\common\models\Platforms::find()->select(['platform_id'])->asArray()->all() as $_platform) {
                                    $this->doUninstallClass($class, $_platform['platform_id']);
                                    $this->doRecalcModuleSort('delete', $class, $distribution->type, $_platform['platform_id']);
                                }
                            }
                            $this->revertFileDst($oldData, $filename, $pathP, true);
                            $record->delete();
                            $this->doSystem = true;
                            $status = 'success';
                        }
                        break;
                    case 'samples':// Sample data
                        $record = \common\models\Installer::find()->where(['filename' => $filename])->one();
                        if ($record instanceof \common\models\Installer) {
                            $oldData = unserialize($record->data);
                            foreach ($oldData as $action => $old) {
                                switch ($action) {
                                    case 'catalog_categories':
                                        $sdn = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed');
                                        foreach (\common\models\CategoriesDescription::find()->select('categories_id')->where(['IN','categories_seo_page_name', $old])->groupBy('categories_id')->asArray()->all() as $category) {
                                            \common\helpers\Categories::remove_category($category['categories_id'], false);
                                            if ($sdn) {
                                                $sdn::deleteCategoryLinks($category['categories_id']);
                                            }
                                        }
                                         break;
                                    case 'catalog_products':
                                        $sdn = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed');
                                        foreach (\common\models\Products::find()->select('products_id')->where(['IN', 'products_model', $old])->asArray()->all() as $product) {
                                            \common\helpers\Product::remove_product($product['products_id']);
                                            if ($sdn){
                                               $sdn::deleteProductLinks($product['products_id']);
                                            }
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            }
                            $record->delete();
                            if (USE_CACHE == 'true') {
                                \common\helpers\System::reset_cache_block('categories');
                                \common\helpers\System::reset_cache_block('also_purchased');
                            }
                            $status = 'success';
                        }
                        break;
                    case 'system':
                    case 'update':// System update
                        $record = \common\models\Installer::find()->where(['filename' => $filename])->one();
                        if ($record instanceof \common\models\Installer) {
                            $oldData = unserialize($record->data);
                            $this->revertFileDst($oldData, $filename, $path, true);
                            $record->delete();
                            $this->doMigrations = true;
                            $this->doSystem = true;
                            $this->doSmarty = true;
                            //$this->doTheme = true;
                            $this->doHooks = true;
                            $this->doMenu = true;
                            $status = 'success';
                            if ($distribution->type == 'update') {
                                \common\models\Configuration::updateAll(['configuration_value' => (string) $distribution->require->version], ['configuration_key'=> 'MIGRATIONS_DB_REVISION']);
                            }
                        }
                        break;
                    case 'configuration':
                        $record = \common\models\Installer::find()->where(['filename' => $filename])->one();
                        if ($record instanceof \common\models\Installer) {
                            $oldData = unserialize($record->data);
                            if (is_array($oldData)) {
                                foreach ($oldData as $old) {
                                    switch ($old['action']) {
                                        case 'add':
                                            $conf = \common\models\Configuration::find()->where(['configuration_key' => $old['configuration_key']])->one();
                                            if (!($conf instanceof \common\models\Configuration)) {
                                                $conf = new \common\models\Configuration();
                                                $conf->loadDefaultValues();
                                                $conf->configuration_title = $old['configuration_title'];
                                                $conf->configuration_key = $old['configuration_key'];
                                                $conf->configuration_value = $old['configuration_value'];
                                                $conf->configuration_description = $old['configuration_description'];
                                                $conf->configuration_group_id = $old['configuration_group_id'];
                                                $conf->sort_order = $old['sort_order'];
                                                $conf->last_modified = $old['last_modified'];
                                                $conf->date_added = $old['date_added'];
                                                $conf->use_function = $old['use_function'];
                                                $conf->set_function = $old['set_function'];
                                                $conf->save(false);
                                            }
                                            break;
                                        case 'update':
                                            $conf = \common\models\Configuration::find()->where(['configuration_key' => $old['configuration_key']])->one();
                                            if ($conf instanceof \common\models\Configuration) {
                                                $conf->configuration_value = $old['configuration_value'];
                                                $conf->configuration_group_id = $old['configuration_group_id'];
                                                $conf->save(false);
                                            }
                                            break;
                                        case 'delete':
                                            $conf = \common\models\Configuration::find()->where(['configuration_key' => $old['configuration_key']])->one();
                                            if ($conf instanceof \common\models\Configuration) {
                                                $conf->delete();
                                            }
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                            $record->delete();
                            $this->doSystem = true;
                            $status = 'success';
                        }
                        break;
                    default:
                        $status = 'fail';
                        break;
                }
                $this->runSystemReCache();
            }
            $zip->close();
        }
        $output = ob_get_clean();
        if ($status == 'success') {
            Yii::$app->response->data = ['status'=>'ok', 'text' => $output . "<br>File $filename reverted."];
        } else {
            Yii::$app->response->data = ['status'=>'error', 'text' => $output . "<br>Can't revert file $filename."];
        }
    }
    
    public function actionRemoveFile() 
    {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        $filename = Yii::$app->request->post('name','');
        $filename = \common\helpers\Output::mb_basename($filename);
        
        if ( is_file($path . $filename) ){
            @unlink($path . $filename);
            Yii::$app->response->data = ['status'=>'ok', 'text' => "File $filename removed."];
        } else {
            Yii::$app->response->data = ['status'=>'error', 'text' => "Can't remove file $filename."];
        }
    }
    
    public function actionDownloadFile() 
    {
        $this->layout = false;
        
        $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        $filename = Yii::$app->request->get('name','');
        $filename = \common\helpers\Output::mb_basename($filename);
        
        $mime_type = \yii\helpers\FileHelper::getMimeTypeByExtension($path . $filename);

        header('Content-Type: ' . $mime_type);
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
        header('Pragma: no-cache');

        readfile($path . $filename);
        die;
    }
    
    /**
     * Settings
     */

    public function actionUpload() 
    {
        if (isset($_FILES['file']['tmp_name'])) {
            
            $xmlfile = file_get_contents($_FILES['file']['tmp_name']);
            $ob= simplexml_load_string($xmlfile);
            if (isset($ob->Menu)) {
                $obPrepared = \common\helpers\MenuHelper::prepareAdminTree($ob->Menu, []);
                tep_db_query("TRUNCATE TABLE admin_boxes;");
                \common\helpers\MenuHelper::importAdminTree($obPrepared);
            }
            if (isset($ob->Groups->item)) {
                foreach ($ob->Groups->item as $item) {
                    $al = \common\models\AccessLevels::find()->select(['access_levels_id'])->where(['access_levels_name' => (string)$item->Name])->one();
                    if (!is_object($al)) {
                        $al = new \common\models\AccessLevels();
                        $al->access_levels_name = (string)$item->Name;
                    }
                    if (is_object($al)) {
                        $selectedIds = [];
                        foreach ($item->Acl->item as $key) {
                            $acl = \common\models\AccessControlList::find()->where(['access_control_list_key' => (string) $key])->one();
                            if (is_object($acl)) {
                                $selectedIds[] = $acl->access_control_list_id;
                            }
                        }
                        if (count($selectedIds) > 0) {
                            $access_levels_persmissions = implode(",", $selectedIds);
                        } else {
                            $access_levels_persmissions = '';
                        }
                        $al->access_levels_persmissions = $access_levels_persmissions;
                        $al->save();
                    }
                }
            }
            if (isset($ob->Members->item)) {
                foreach ($ob->Members->item as $item) {
                    $admin = false;
                    if (isset($item->id)) {
                        $admin = \common\models\Admin::find()->where(['admin_id' => (int)$item->id])->one();
                    }
                    if (!is_object($admin)) {
                        $admin = new \common\models\Admin();
                    }
                    $admin->admin_username = (string)$item->username;
                    $admin->admin_firstname = (string)$item->firstname;
                    $admin->admin_lastname = (string)$item->lastname;
                    $admin->admin_email_address = (string)$item->email;
                    $admin->admin_phone_number = (string)$item->phone;
                    $admin->languages = (string)$item->languages;
                    $admin->access_levels_id = (int)$item->group;
                    $persmissions = [];
                    if (isset($item->persmissions->include)) {
                        foreach ($item->persmissions->include as $key) {
                            $aclItem = \common\models\AccessControlList::find()->select(['access_control_list_id'])->where(['access_control_list_key' => (string)$key])->asArray()->one();
                            if (isset($aclItem['access_control_list_id'])) {
                                $persmissions[] = $aclItem['access_control_list_id'];
                            }
                        }
                    }
                    if (isset($item->persmissions->exclude)) {
                        foreach ($item->persmissions->exclude as $key) {
                            $aclItem = \common\models\AccessControlList::find()->select(['access_control_list_id'])->where(['access_control_list_key' => (string)$key])->asArray()->one();
                            if (isset($aclItem['access_control_list_id'])) {
                                $persmissions[] = ($aclItem['access_control_list_id'] * -1);
                            }
                        }
                    }
                    $admin_persmissions = '';
                    if (count($persmissions) > 0) {
                        $admin_persmissions = implode(",", $persmissions);
                    }
                    $admin->admin_persmissions = $admin_persmissions;
                    $admin->save();
                }
            }
            unlink($_FILES['file']['tmp_name']);
        }
    }
    
    public function actionDownload() 
    {
        $this->layout = false;
        $response = [];
        
        $xml = new \yii\web\XmlResponseFormatter;
        $xml->rootTag = 'Install';
        Yii::$app->response->format = 'custom_xml';
        Yii::$app->response->formatters['custom_xml'] = $xml;
        
        $headers = Yii::$app->response->headers;
        $headers->add('Content-Type', 'text/xml; charset=utf-8');
        $headers->add('Content-Disposition', 'attachment; filename="install.xml"');
        $headers->add('Pragma', 'no-cache');
        
        $menu = (int) Yii::$app->request->post('menu');
        $groups = (int) Yii::$app->request->post('groups');
        $members = (int) Yii::$app->request->post('members');
        
        if ($menu == 1) {
            $queryResponse = \common\models\AdminBoxes::find()
                ->orderBy(['sort_order' => SORT_ASC])
                ->asArray()
                ->all(); 
        
            $response['Menu'] = $this->buildXMLTree(0, $queryResponse, []);
        }
        
        if ($groups == 1) {
            $Groups = [];
            foreach (\common\models\AccessLevels::find()->all() as $acl) {
                $selectedIds = [];
                if (is_string($acl->access_levels_persmissions)) {
                    $selectedIds = explode(",", $acl->access_levels_persmissions);
                }
                if (!is_array($selectedIds)) {
                    $selectedIds = [];
                }
                $aclList = \common\models\AccessControlList::find()
                        ->select(['access_control_list_key'])
                        ->where(['IN', 'access_control_list_id', $selectedIds])
                        ->orderBy('sort_order')
                        ->asArray()
                        ->all();

                $aclRules = [];
                foreach ($aclList as $item) {
                    $aclRules[] = $item['access_control_list_key'];
                }
                
                $Groups[] = [
                    'Name' => $acl->access_levels_name,
                    'Acl' => $aclRules,
                ];
            }
            $response['Groups'] = $Groups;
        }
        
        if ($members == 1) {
             $membersList = \common\models\Admin::find()
                        ->asArray()
                        ->all();
            $Members = [];
            foreach ($membersList as $item) {
                $persmissions = [
                    'include' => [],
                    'exclude' => [],
                ];
                $adminPersmissions = explode(",", $item['admin_persmissions']);
                foreach ($adminPersmissions as $ap) {
                    if ($ap > 0) {
                        $aclItem = \common\models\AccessControlList::find()->select(['access_control_list_key'])->where(['access_control_list_id' => $ap])->asArray()->one();
                        if (isset($aclItem['access_control_list_key'])) {
                            $persmissions['include'][] = $aclItem['access_control_list_key'];
                        }
                    } elseif ($ap < 0) {
                        $aclItem = \common\models\AccessControlList::find()->select(['access_control_list_key'])->where(['access_control_list_id' => ($ap * -1)])->asArray()->one();
                        if (isset($aclItem['access_control_list_key'])) {
                            $persmissions['exclude'][] = $aclItem['access_control_list_key'];
                        }
                    }
                }
                
                $Members[] = [
                    'id' => $item['admin_id'],
                    'username' => $item['admin_username'],
                    'firstname' => $item['admin_firstname'],
                    'lastname' => $item['admin_lastname'],
                    'email' => $item['admin_email_address'],
                    'phone' => $item['admin_phone_number'],
                    'languages' => $item['languages'],
                    'group' => $item['access_levels_id'],
                    'persmissions' => $persmissions,
                ];
            }
            $response['Members'] = $Members;
        }
        
        return $response;
    }
    
    
    public function actionUpdates() 
    {
        \common\helpers\Translation::init('admin/install');
        $this->layout = false;
        
        $this->checkSystemRequires();
        
        $updates = [];
        $version = (defined('MIGRATIONS_DB_REVISION') ? MIGRATIONS_DB_REVISION : '');
        
        $secKeyGlobal = md5(\Yii::$app->db->dsn . (defined('INSTALLED_MICROTIME') ? INSTALLED_MICROTIME : ''));
        $storageUrl = \Yii::$app->params['appStorage.url'];
        $storageKey = $this->getStorageKey();
        if (!isset(\Yii::$app->params['secKey.global']) OR (\Yii::$app->params['secKey.global'] != $secKeyGlobal)) {
            // wrong security store key
        } elseif (empty($storageKey) || empty($storageUrl)) {
            // wrong storage key or url
        } elseif ($request = curl_init()) {
            curl_setopt($request, CURLOPT_URL, $storageUrl . 'app-api-server/system-updates');
            
            // for testing
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
            if (defined('CURLOPT_SSL_VERIFYSTATUS')) { // Added in cURL 7.41.0
                curl_setopt($request, CURLOPT_SSL_VERIFYSTATUS, false);
            }

            curl_setopt($request, CURLOPT_TIMEOUT_MS, 30000);
            curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($request, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $storageKey . ':' . $secKeyGlobal
            ));
            
            $postFieldArray = [
                'version' => $version,
            ];
            $postFieldArray = json_encode($postFieldArray);
            
            curl_setopt($request, CURLOPT_POSTFIELDS, $postFieldArray);
            $result = json_decode(curl_exec($request), true);
            $response = curl_getinfo($request);
            curl_close($request);
            if ($response['http_code'] == 200 && isset($result['updates'])) {
                foreach ($result['updates'] as $item) {
                    $updates[] = $item;
                }
            }
        }
        
        $updatesCount = \common\models\Installer::find()
                ->where(['archive_type' => 'update'])
                ->count();
        
        $installed = (defined('INSTALLED_DATE') ? INSTALLED_DATE : '');
        $updated = (defined('UPDATED_DATE') ? UPDATED_DATE : '');
        
        return $this->render('update-list', [
            'installed' => $installed,
            'version' => PROJECT_VERSION_MAJOR. '.' . PROJECT_VERSION_MINOR . '.' . $version . (!empty($updated) ? ' updated at ' . $updated : ''),
            'updates' => $updates,
            'updatesCount' => $updatesCount,
        ]);
    }
    
    public function actionUpdateLog()
    {
        \common\helpers\Translation::init('admin/install');
        $this->layout = false;
        $responseLog = [];
        foreach( \common\models\Installer::find()
                ->select(['filename', 'date_added', 'archive_version', 'data'])
                ->where(['archive_type' => 'update'])
                ->orderBy('archive_version ASC')
                ->asArray()
                ->all() as $update) {
            $responseLog[] = $update['date_added'] . " <font color='green'>" . TEXT_UPDATE_APPLIED . " " . $update['filename'] . "</font><br>\n";
            $data = unserialize($update['data']);
            if (is_array($data)) {
                foreach ($data as $item) {
                    $responseLog[] = $update['date_added'] . ' ' . $item->action . ' ' . $item->type . ' ' . str_replace('|', DIRECTORY_SEPARATOR, $item->path);
                }
            }
            
        }
        
        
        return $this->render('update-log', [
            'responseLog' => $responseLog,
        ]);
    }
    
    private function sendEcho($string) {
        echo $string;
        ob_flush();
        flush();
    }
    
    public function actionSaveIgnoreList()
    {
        \common\models\InstallIgnoreList::deleteAll();
        $dst_file_ignore = Yii::$app->request->post('dst_file_ignore');
        if (is_array($dst_file_ignore)) {
            foreach($dst_file_ignore as $index => $value) {
                if (!empty($value)) {
                    $file = new \common\models\InstallIgnoreList();
                    $file->id = $index;
                    $file->path = $value;
                    $file->save(false);
                }
            }
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['status' => 'ok'];
    }
    
    public function actionUpdateNow() 
    {
        $this->checkSystemRequires();
        
        @set_time_limit(0);
        @ignore_user_abort(true);
        
        $force = (int) Yii::$app->request->get('force');
        
        if ($force) {
            $this->dst_file_ignore = [];
            foreach (\common\models\InstallIgnoreList::find()->asArray()->all() as $file) {
                $this->dst_file_ignore[] = $file['path'];
            }
        } else {
            $this->show_ignore_field = true;
        }
        try {
            \common\models\InstallIgnoreList::deleteAll();
        } catch (\Exception $exc) {
            $this->sendEcho("<font color='red'>Exception: " . $exc->getMessage() . ".</font><br>\n" );
        }

        
        $this->layout = false;
        
        header('Content-Type: text/html');
        header('Content-Transfer-Encoding: utf-8');
        header('Pragma: no-cache');
        
        $conf = \common\models\Configuration::find()->where(['configuration_key' => 'MIGRATIONS_DB_REVISION'])->one();
        if ($conf instanceof \common\models\Configuration) {
            $version = $conf->configuration_value;
        } else {
            $version = '';
        }
        
        $secKeyGlobal = md5(\Yii::$app->db->dsn . (defined('INSTALLED_MICROTIME') ? INSTALLED_MICROTIME : ''));
        $storageUrl = \Yii::$app->params['appStorage.url'];
        $storageKey = $this->getStorageKey();
        if (!isset(\Yii::$app->params['secKey.global']) OR (\Yii::$app->params['secKey.global'] != $secKeyGlobal)) {
            // wrong security store key
        } elseif (empty($storageKey) || empty($storageUrl)) {
            // wrong storage key or url
        } else {
            $needReCache = false;
            while (!empty($version)) {
                if ($request = curl_init()) {
                    $this->sendEcho(TEXT_CHECK_UPDATES . " $version<br>\n");
                    
                    curl_setopt($request, CURLOPT_URL, $storageUrl . 'app-api-server/get-update');

                    // for testing
                    curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);
                    if (defined('CURLOPT_SSL_VERIFYSTATUS')) { // Added in cURL 7.41.0
                        curl_setopt($request, CURLOPT_SSL_VERIFYSTATUS, false);
                    }

                    curl_setopt($request, CURLOPT_TIMEOUT_MS, 30000);
                    curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($request, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Accept: application/json',
                        'Authorization: Bearer ' . $storageKey . ':' . $secKeyGlobal
                    ));

                    $postFieldArray = [
                        'version' => $version,
                    ];
                    $postFieldArray = json_encode($postFieldArray);

                    curl_setopt($request, CURLOPT_POSTFIELDS, $postFieldArray);
                    $result = json_decode(curl_exec($request), true);
                    $response = curl_getinfo($request);
                    curl_close($request);

                    //$version = '';
                    if ($response['http_code'] == 200 && isset($result['content'])) {
                        $path = Yii::getAlias('@site_root');
                        $filename = $result['filename'] ?? '';
                        if (!file_exists($path . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename)) {
                            $content = base64_decode($result['content']);
                            $size = $result['size'] ?? 0;
                            if (strlen($content) == $size) {
                                file_put_contents($path . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename, $content);
                                $this->sendEcho(TEXT_FOUND_UPDATE . ". " . TEXT_FILE . " $filename " . TEXT_DOWNLOADED . ".<br>\n");
                            }
                            unset($content);
                        } else {
                            $this->sendEcho(TEXT_FOUND_UPDATE . ". " . TEXT_FILE . " $filename " . TEXT_ALREADY_DOWNLOADED . ".<br>\n");
                        }
                        unset($result);
                        try {
                            $status = $this->installFileWithDependencies($filename, ['force' => $force], true);
                        } catch (\Exception $exc) {
                            $status = false;
                            $this->sendEcho("<font color='red'>Exception: " . $exc->getMessage() . ".</font><br>\n" );
                        }
                        $force = 0;
                        ob_flush();
                        flush();
                        if ($status) {
                            $this->sendEcho("<font color='green'>" . $filename . " " . TEXT_PACK_INSTALLED.".</font><br>\n");
                            $zip = new \ZipArchive();
                            if ($zip->open($path . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename) === true) {
                                $json = $zip->getFromName('distribution.json');
                                $json = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',$json);
                                $distribution = json_decode($json);
                                $version = (string) $distribution->version;
                                $zip->close();
                            } else {
                                $version = '';
                            }
                            $updatedDate = \common\models\Configuration::find()->where(['configuration_key'=> 'UPDATED_DATE'])->one();
                            if ($updatedDate instanceof \common\models\Configuration) {
                                $updatedDate->last_modified = date('Y-m-d H:i:s');
                            } else {
                                $updatedDate = new \common\models\Configuration();
                                $updatedDate->loadDefaultValues();
                                $updatedDate->configuration_title = 'Date of last update';
                                $updatedDate->configuration_key = 'UPDATED_DATE';
                                $updatedDate->date_added = date('Y-m-d H:i:s');
                            }
                            $updatedDate->configuration_value = date('Y-m-d H:i:s');
                            $updatedDate->save(false);
                            $needReCache = true;
                            try {
                                $this->runSystemReCache(true);
                            } catch (\Exception $exc) {
                                $this->sendEcho("<font color='red'>Exception: " . $exc->getMessage() . ".</font><br>\n" );
                            }
                            ob_flush();
                            flush();
                            //@unlink($path . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename);
                        } else {
                            $this->sendEcho("<font color='red'>". $filename . " " . TEXT_PACK_ABORTED.".</font><br>\n");
                            $version = '';
                            echo TEXT_USE . ' <a style="font-size: 30px;" class="btn" href="javascript:void(0)" onclick="return parent.runQuery(1);">' . TEXT_FORCE_UPDATE . '</a>. ' . TEXT_FORCE_UPDATE_INTRO . '.<br>';
                        }
                        
                    } else {
                        if ($response['http_code'] != 400 ) {
                            $this->sendEcho("<font color='red'>Status response: " . $response['http_code'] . ".</font><br>\n");
                        }
                        $this->sendEcho(TEXT_NO_UPDATES . "<br>\n");
                        $version = '';
                    }
                }
            }
        
            if ($needReCache) {
                ob_flush();
                flush();
                $this->sendEcho(TEXT_UPDATE_FINISH . ".<br>\n");
            }
        }
        echo '<br><a class="btn" href="javascript:void(0)" onclick="return parent.checkActualStatus();">' . IMAGE_BACK . '</a>';
    }
    
    public function actionCleanupLocalStorage()
    {
        $path = Yii::getAlias('@site_root') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        $backup_path = $path . 'backups' . DIRECTORY_SEPARATOR;
        if ($dir = @dir($path)) {
            while ($file = $dir->read()) {
                $ext = substr($file, strrpos($file, '.') + 1);
                if ($ext != 'zip') {
                    continue;
                }
                $deployed = false;
                $record = \common\models\Installer::find()->where(['filename' => $file])->one();
                if ($record instanceof \common\models\Installer) {
                    $deployed = true;
                    $zip = new \ZipArchive();
                    if ($zip->open($path . $file) === true) {
                        $json = $zip->getFromName('distribution.json');
                        $json = preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','',$json);
                        if (!empty($json)) {
                            $distribution = json_decode($json);
                            list($major, $minor, $patch) = explode('.', (string)$distribution->version);
                            $archive_version = intval($major) + intval($minor)/100 + intval($patch)/10000;
                            $check = \common\models\Installer::find()
                                    ->select(['max(archive_version) as version'])
                                    ->where(['archive_type' => (string)$distribution->type])
                                    ->andWhere(['archive_class' => (string)$distribution->class])
                                    ->asArray()
                                    ->one();
                            if (isset($check['version']) && $check['version'] > $archive_version) {
                                $deployed = false;
                                $record->delete();
                                //delete backup for latest version
                                $checkLatest = \common\models\Installer::find()
                                    ->select(['filename'])
                                    ->where(['archive_type' => (string)$distribution->type])
                                    ->andWhere(['archive_class' => (string)$distribution->class])
                                    ->andWhere(['archive_version' => $check['version']])
                                    ->asArray()
                                    ->one();//filename
                                if (isset($checkLatest['filename'])) {
                                    if (is_file($backup_path . $checkLatest['filename'])) {
                                        @unlink($backup_path . $checkLatest['filename']);
                                    }
                                }
                            }
                        }
                        $zip->close();
                    }
                    unset($zip);
                }
                if ($deployed === false) {
                    @unlink($path . $file);
                    if (is_file($backup_path . $file)) {
                        @unlink($backup_path . $file);
                    }
                }
            }
        }
        return $this->redirect(Yii::$app->urlManager->createUrl(['install/', 'set' => 'modules']));
    }
    
}
