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

namespace common\classes\modules;

use common\classes\platform;

class ModuleExtensions extends Module {

    public $isExtension = true;
    public $userConfirmedDropDatatables = false;// check this field in remove function if extention is able to drop own datatables into isAbleToDropDatatables()
    public $assign_to_access_levels = 1;
    public $userConfirmedDeleteAcl = false; 
    
    public function __construct() {
        $ref = new \ReflectionClass(get_called_class());
        $this->code = $ref->getShortName();
        $this->namespace = $ref->getNamespaceName();
        $this->title = \yii\helpers\Inflector::camel2words($this->code);
        static::initTranslationArray('init_constructor');
    }

    public static function allowed()
    {
        if (self::enabled()) {
            static::initTranslationArray('init_always');
            return true;
        }
        return false;
    }

    public static function checkSetup($methodName = null)
    {
        $reflect = new \ReflectionClass(static::class);
        $className =  $reflect->getNamespaceName() . '\\Setup';
        if (!class_exists($className)) {
            return null;
        }
        if (empty($methodName) || (is_string($methodName) && method_exists($className, $methodName))) {
            return $className;
        }
        if (is_array($methodName)) {
            foreach($methodName as $name) {
                if (!method_exists($className, $name)) {
                    return null;
                }
            }
            return $className;
        }
    }

    public static function getDescription() {
        if ($setup = static::checkSetup('getDescription')) {
            return $setup::getDescription();
        }
        return parent::getDescription();
    }

    public static function getVersionHistory()
    {
        if ($setup = static::checkSetup('getVersionHistory')) {
            return $setup::getVersionHistory();
        }
        return parent::getVersionHistory();
    }

    public static function getVersion()
    {
        if ($setup = static::checkSetup('getVersion')) {
            return $setup::getVersion();
        }
        return parent::getVersion();
    }

    /**
     * @return null|false|object (stdClass)
     * object - since v4.09 + AppShop application created after v4.09
     */
    public static function getDistribObj()
    {
        $fn = static::getExtDir() . '/distribution.json';
        if (file_exists($fn) && ($text = file_get_contents($fn))) {
            return json_decode($text);
        }
    }

    /**
     * @return void|null|string - string is application revision
     */
    public static function getRevision()
    {
        $distribObj = self::getDistribObj();
        if (is_object($distribObj)) {
            return $distribObj->revision ?? null;
        }
    }

    protected static function getTranslationArray()
    {
        if ($setup = static::checkSetup('getTranslationArray')) {
            return $setup::getTranslationArray();
        }
        return [];
    }

    public static function getTranslationValueOwn($key, $entity = null, $default = '##key##')
    {
        static $arr = null;
        if (is_null($arr)) {
            $arr = self::getTranslationArray();
            if (!is_array($arr)) $arr = [];
        }
        if (!empty($entity) && isset($arr[$entity][$key])) {
            return $arr[$entity][$key];
        } else {
            foreach ($arr as $translations) {
                if (isset($translations[$key])) {
                    return $translations[$key];
                }
            }
        }
        if ($default == '##key##') return $key;
        return $default;
    }

    public static function getTranslationValue($key, $entity = '')
    {
        $res = \common\helpers\Translation::getValue($key, $entity, null);
        if (!$res) {
            $res = self::getTranslationValueOwn($key, $entity);
        }
        return $res;
    }

    public static function getTranslatedArray(array $translationKeys)
    {
        $res = [];
        foreach ($translationKeys as $val) {
            $res[$val] = static::getTranslationValue($val);
        }
        return $res;
    }


    public static function getAdminMenu() {
        if ($setup = static::checkSetup('getAdminMenu')) {
            return $setup::getAdminMenu();
        }
        return [];
    }

    public static function getRequiredModules()
    {
        if ($setup = static::checkSetup('getRequiredModules')) {
            return $setup::getRequiredModules();
        }
    }

    public static function getAdminHooks()
    {
        if ($setup = static::checkSetup('getAdminHooks')) {
            return $setup::getAdminHooks();
        }
    }

    public static function getWidgets($type = 'general') {
        if (!self::allowed()) {
            return '';
        }
        if ($setup = static::checkSetup('getWidgets')) {
            $res = $setup::getWidgets($type);
            if (empty($res)) {
                return '';
            } else {
                static::initTranslation('init_widget');
                foreach($res as &$wd){
                    if (isset($wd['type']) && empty($wd['type'])) {
                        $wd['type'] = 'general';
                    }
                }
                return $res;
            }
        }
    }

    public static function getPages() {
        if (!self::allowed()) {
            return '';
        }
        if ($setup = static::checkSetup('getPages')) {
            $res = $setup::getPages();
            return $res;
        }
    }


    public static function showSettings($settings) {
        
    }

    public static function getEpDataSources()
    {
        if ($setup = static::checkSetup('getEpDataSources')) {
            return $setup::getEpDataSources();
        }
    }

    public static function getEpProviders()
    {
        if ($setup = static::checkSetup('getEpProviders')) {
            return $setup::getEpProviders();
        }
    }

    public function enable_module($platform_id, $flag)
    {
        $res = parent::enable_module($platform_id, $flag);
        if ($res !== false) {
            \yii\caching\TagDependency::invalidate(\Yii::$app->cache, 'extension_changed');
        }
        return $res;
    }
    /* Not needed for overriding if Setup::install is implemented */
    public function install($platform_id) {
        try {
            $migrate = new \common\classes\Migration();
            $migrate->compact = true;
            self::installTranslationArray($platform_id, $migrate);
            $this->appendAcl($platform_id, $migrate);
            \common\helpers\MenuHelper::createAdminMenuItems( static::getAdminMenu() );
            if ($setup = static::checkSetup('install')) {
                $installed = self::getInstalled();
                if (!empty($installed->version_db ?? null)) {
                    \Yii::warning("Extension already exists in DB. Ver=$installed->version VerDB=$installed->version_db");
                }
                $setup::install($platform_id, $migrate);
            }
            \common\helpers\Hooks::registerHooks($this->getAdminHooks(), $this->code);
            \backend\design\Style::validateCache();
            self::installCronJobs();
            $res = parent::install($platform_id);
            \yii\caching\TagDependency::invalidate(\Yii::$app->cache, 'extension_changed');
            return $res;
        } catch (\Exception $ex) {
            \Yii::warning($ex->getMessage() . ' ' . $ex->getTraceAsString(), "Extensions/$this->code");
            throw $ex;
        }
    }

    /* Not needed for overriding if Setup::remove is implemented */
    public function remove($platform_id) {
        try {
            $migrate = new \common\classes\Migration();
            $migrate->compact = true;
            if ($setup = static::checkSetup('remove')) {
                $setup::remove($platform_id, $migrate, $this->userConfirmedDropDatatables);
            }
            if ($this->userConfirmedDropDatatables && ($setup = static::checkSetup('getDropDatabasesArray'))) {
                $migrate->dropTables($setup::getDropDatabasesArray());
                self::removeCronJobs();
                \common\helpers\Modules::changeModule($this->code, 'remove_drop');
            }
            if ($this->userConfirmedDeleteAcl && ($setup = static::checkSetup('getAclArray'))) {
                self::dropAcl($platform_id, $migrate);
            }
            \common\helpers\MenuHelper::removeAdminMenuItems(static::getAdminMenu());
            static::removeTranslationArray($platform_id, $migrate, $this->userConfirmedDeleteAcl); // after remove menu

            \common\helpers\Hooks::unregisterHooks($this->code);
            $res = parent::remove($platform_id);
            \common\helpers\Modules::changeModule($this->code, 'remove');
            \yii\caching\TagDependency::invalidate(\Yii::$app->cache, 'extension_changed');
            return $res;
        } catch (\Exception $ex) {
            \Yii::warning($ex->getMessage() . ' ' . $ex->getTraceAsString(), "Extensions/$this->code");
            throw $ex;
        }
    }

    public static function isAbleToDeleteAcl() {

        return boolval( static::checkSetup(['getAclArray']) );
    }
    
    public static function isAbleToDropDatatables() {

        return boolval( static::checkSetup(['getDropDatabasesArray']) );
    }

    /* Not needed for overriding if Setup::getAclArray is implemented */
    public static function acl() {
        if ($setup = static::checkSetup('getAclArray')) {
            $acl = $setup::getAclArray();
            $acl_default = $acl['default'] ?? ($acl[0] ?? []);
            $action = \Yii::$app->request->get('action', '');
            if (!empty($action)) {
                return $acl[$action] ?? $acl_default;
            }
            return $acl_default;
        }
        return [];
    }

    public function describe_status_key() {
        return new ModuleStatus($this->code . '_EXTENSION_STATUS', 'True', 'False');
    }

    public function describe_sort_key() {
        
    }

    public function configure_keys() {
        $keys0 = [
            $this->code . '_EXTENSION_STATUS' => [
                'title' => $this->title . ' status',
                'value' => 'False',
                'set_function' => 'tep_cfg_select_option(array(\'True\', \'False\'), ',
            ]
        ];
        $keys = $this->getConfigureKeysArea(true, 'restrictions', 'platform');
        if (is_array($keys)) {
            $keys0 = array_merge($keys0, $keys);
        }
        return $keys0;
    }

    public function configure_keys_platforms(){
        return $keys = $this->getConfigureKeysArea(false, 'platforms');
    }

    public function add_platform_key($platform_id, $key, $data)
    {
        $this->add_config_key($platform_id, $key, $data );
    }

    public static function getSetupConfigureKeys()
    {
        if ($setup = static::checkSetup('getConfigureKeys')) {
            $keys = $setup::getConfigureKeys(self::getModuleCode());
            if (is_array($keys)) {
                array_walk($keys, function(&$value, $key) {
                    foreach(['title', 'description'] as $keyItem) {
                        if (preg_match('/##([\w_\-]*)##/', $value[$keyItem], $match)) {
                            $value[$keyItem] = static::getTranslationValue($match[1]);
                        }
                    }
                });
                return $keys;
            }
        }
    }

    public function getConfigureKeysArea(bool $includeEmptyArea = true, $includeArea = null, $excludeArea = null)
    {
        $keys = self::getSetupConfigureKeys();
        if (is_array($keys) && !empty($keys)) {
            $includeArea = is_array($includeArea) ? $includeArea : explode(',', $includeArea);
            $excludeArea = is_array($excludeArea) ? $excludeArea : explode(',', $excludeArea);
            return array_filter($keys, function($value) use ($includeEmptyArea, $includeArea, $excludeArea ) {
                if (empty($value['area'])) {
                    return $includeEmptyArea;
                } else {
                    return in_array($value['area'], $includeArea) && !in_array($value['area'], $excludeArea);
                }
            });
        }
    }

    public static function enabled() {
        $class = (new \ReflectionClass(get_called_class()))->getShortName();
        if (class_exists('\common\helpers\Extensions')) {
            return \common\helpers\Extensions::isEnabled($class);
        } else {
            // issue: system update && uninstall && resetMenu
            // but can't remove because of include platforms into application_top
            return defined($class . '_EXTENSION_STATUS') && constant($class . '_EXTENSION_STATUS') == 'True';
        }
    }

    public static function getMetaTagKeys($meta_tags) {
        if (($setup = static::checkSetup('getMetaTagKeys')) && self::allowed()) {
            return $setup::getMetaTagKeys($meta_tags);
        } else {
            return $meta_tags;
        }
    }

    /**
     * Attach actions for the controller.
     *
     * ```php
     * return [
     *     'action1' => '\common\extensions\Extension\Extension\actions\backend\Action1',
     *     'action2' => [
     *         'class' => '\common\extensions\Extension\Extension\actions\frontend\Action2',
     *         'property1' => 'value1',
     *         'property2' => 'value2',
     *     ],
     * ];
     * ```
     *
     * @param $controllerId
     * @return array
     */
    public static function getControllerActions($controllerId)
    {
        // used enabled to prevent init translations
        if (self::enabled() && ($setup = static::checkSetup('getControllerActions'))) {
            return $setup::getControllerActions($controllerId);
        }
        return [];
    }


    public static function getCronJobs(bool $checkAllowed = true)
    {
        if ((!$checkAllowed || self::allowed()) && ($setup = static::checkSetup('getCronJobs'))) {
            return $setup::getCronJobs();
        }
        return [];
    }

    private static function installCronJobs()
    {
        if ($cronSheduler = \common\helpers\Extensions::isAllowed('CronScheduler')) {
            $jobs = \common\helpers\Cron::getExtensionJobs(self::getModuleCode(), false);
            if (is_array($jobs)) {
                foreach ($jobs as $job) {
                    if ($job['active'] ?? false) {
                        $cronSheduler::addJob($job);
                    }
                }
            }
        }
    }

    private static function removeCronJobs()
    {
        if ($cronSheduller = \common\helpers\Extensions::isCronScheduler('removeJobsExtension')) {
            $cronSheduller::removeJobsExtension(static::getModuleCode());
        }
    }


        private function appendAcl($platform_id, \common\classes\Migration $migrate) {
        if ($setup = static::checkSetup('getAclArray')) {
            $aclArray = $setup::getAclArray();
            if (is_array($aclArray) && count($aclArray) > 0) {
                foreach($aclArray as $key => $acl) {
                    $migrate->appendAcl($acl, $this->assign_to_access_levels);
                }
            }
        }
    }
    
    private static function dropAcl($platform_id, $migrate) {
        if ($setup = static::checkSetup('getAclArray')) {
            $aclArray = $setup::getAclArray();
            if (is_array($aclArray) && count($aclArray) > 0) {
                // place parent keys in the end to delete their later
                uasort($aclArray, function($a, $b) { return count($b) <=> count($a); });

                foreach($aclArray as $key => $acl) {
                    $migrate->dropAcl($acl);
                }
                if (isset($aclArray['default'])) {
                    $migrate->dropAcl($aclArray['default']);
                }
            }
        }
    }

    /**
     * get Acl chain for entity/action
     * @param string $action - name of action
     * @param string $entity - 'extension' or 'ControllerClassName'
     * @param bool $matchSimilar = false - checks only full match $entity/$action
     *                           = true - if $entity/$action not found, checks keys: $entity/$action, $entity, 'default'
     * @return null|array
     */
    public static function getAcl(string $action = '', string $entity = 'extension', bool $matchSimilar = true)
    {
        if ($setup = static::checkSetup('getAclArray')) {
            $aclArray = $setup::getAclArray();

            $actionSuffix = empty($action)? '' : "/$action";

            foreach(["$entity$actionSuffix", $entity, $action, 'default'] as $key) {
                if (!empty($key) && isset($aclArray[$key])) {
                    return $aclArray[$key];
                } elseif (!$matchSimilar) {
                    return null;
                }
            }
        }
    }

    protected static function installTranslationArray($platform_id, $migrate)
    {
        $translationArray = static::getTranslationArray();
        if (is_array($translationArray) && count($translationArray) > 0) {
            foreach($translationArray as $entity => $keysArray) {
                if (static::isProcessTranslationPair($entity, $keysArray, 'install')) {
                    $withoutMagicKeys = array_filter($keysArray, function($key) { return is_string($key) && substr($key, 0, 2) != '__'; }, ARRAY_FILTER_USE_KEY);
                    $migrate->addTranslation($entity, $withoutMagicKeys);
                }
            }
        }
    }

    /**
     * @param $platform_id int 0
     * @param $migrate \common\classes\Migration
     * @param bool $fullDel
     * @return void
     */
    protected static function removeTranslationArray($platform_id, $migrate, bool $fullDel = false)
    {
        $translationArray = static::getTranslationArray();
        if (is_array($translationArray) && count($translationArray) > 0) {
            foreach($translationArray as $entity => $keysArray) {
                if (static::isProcessTranslationPair($entity, $keysArray, 'remove_entity')) {
                    $migrate->removeTranslation($entity);
                } elseif (static::isProcessTranslationPair($entity, $keysArray, 'remove_keys')) {
                    $keys = array_keys($keysArray);
                    if (!empty($keys)) { // don't remove entity for empty array
                        $migrate->removeTranslation($entity, $keys);
                    }
                } elseif ($fullDel && static::isProcessTranslationPair($entity, $keysArray, 'remove_keys_if_acl_removing')) {
                    $keys = array_keys($keysArray);
                    if (!empty($keys)) { // don't remove entity for empty array
                        if ($entity = 'admin/main') {
                            $usedKeys = \common\models\AdminBoxes::find()->where(['title' => $keys])->select('title')->column();
                            if (!empty($usedKeys)) {
                                $keys = array_diff($keys, $usedKeys);
                            }
                        }
                        $migrate->removeTranslation($entity, $keys);
                    }
                }
            }
        }
    }

    //it's protected for overriding, not for using
    //use ::initTranslation() for custom initializing
    protected static function initTranslationArray($from = 'init_directcall')
    {
        if (!\common\helpers\System::isYiiLoaded()) return;
        if (is_bool($from)) {
            $from = 'init_directcall';
        }
        $transl = static::getTranslationArray();
        if (is_array($transl)) {
            // make sure 'main' gets initialized first
            foreach($transl as $entity => $keysArray) {
                if (in_array($entity, ['main', 'admin/main'])) {
                    if (static::isProcessTranslationPair($entity, $keysArray, $from)) {
                        \common\helpers\Translation::init($entity);
                    }
                }
            }

            foreach($transl as $entity => $keysArray) {
                if (static::isProcessTranslationPair($entity, $keysArray, $from)) {
                    \common\helpers\Translation::init($entity);
                }
            }
        }
    }

    public static function initTranslation($from = 'init_directcall')
    {
        static::initTranslationArray($from);
    }

    private const TRANSLATION_KEYS_DEF_OPERATIONS = [
        'main'         => ['install', 'remove_keys_if_acl_removing'], // 'main' and 'admin/main' keys
        'extension'    => ['install', 'remove_entity', 'init_always', 'init_controller', 'init_beforeaction', 'init_widget', 'init_directcall'], // extension keys
        'external'     => ['install', 'init_always', 'init_controller', 'init_beforeaction', 'init_widget', 'init_directcall'],
        'other'        => ['install', 'remove_keys', 'init_always', 'init_controller', 'init_beforeaction', 'init_widget', 'init_directcall'],
    ];
    private static function isProcessTranslationPair($key, $value, $operation)
    {
        $config = $value['__config__'] ?? self::getDefault($key, $value);
        return in_array($operation, $config);
    }

    private static function getDefault($key, $value)
    {
        if (isset($value['__config_as__'])) {
            if (is_string($value['__config_as__']) && isset(self::TRANSLATION_KEYS_DEF_OPERATIONS[$value['__config_as__']])) {
                return self::TRANSLATION_KEYS_DEF_OPERATIONS[$value['__config_as__']];
            } else {
                \Yii::warning('Wrong __config_as__ value: '. var_export($value['__config_as__'], true));
            }
        }
        if (in_array($key, ['main', 'admin/main'])) {
            return self::TRANSLATION_KEYS_DEF_OPERATIONS['main'];
        } elseif (\common\helpers\Php8::str_start_with($key, 'extensions/')) {
            return self::TRANSLATION_KEYS_DEF_OPERATIONS['extension'];
        } elseif (isset($value['__external__']) && !\common\helpers\Extensions::isUninstalled($value['__external__']['extension'])) {
            return self::TRANSLATION_KEYS_DEF_OPERATIONS['external'];
        } else {
            return self::TRANSLATION_KEYS_DEF_OPERATIONS['other'];
        }
    }

    /**
     * @param $migrate \common\classes\Migration
     */
    public static function reinstallTranslation($migrate)
    {
        static::removeTranslationArray(null, $migrate, true);
        static::installTranslationArray(null, $migrate);
    }


    /*
     * Developer can use it to update translation constants by URL:
     * https://localhost/admin/extensions?module=YourExt&action=actionRefreshTranslation
     */
    public static function actionRefreshTranslation()
    {
        if (\common\helpers\System::isDevelopment()) {
            $migrate = new \common\classes\Migration();
            $migrate->compact = true;
            static::reinstallTranslation($migrate);
            echo "translation reinstalled<br>";
        }
    }

    public static function getExtDir()
    {
        return self::getBaseDir();
    }

    public static function getBaseDir()
    {
        return \Yii::getAlias('@site_root/' . self::getBaseDirRelative());
    }

    public static function getBaseDirRelative()
    {
        return \common\helpers\Extensions::getBaseDirRelative(self::getModuleCode());
    }

    public static function getImageRelative($imageFN, $defImageRelativeFN = null)
    {
        return \common\helpers\Extensions::getImageRelative(self::getModuleCode(), $imageFN, $defImageRelativeFN);
    }

    private static function getViewFile($view)
    {
        return '@common/extensions/' . self::getModuleCode() . '/views/' . $view;
    }



    public static function render($view, $params = [])
    {
        $html = RenderExtensions::widget(['template' => self::getViewFile($view), 'params' => $params]);
        return \Yii::$app->controller->renderContent($html);
    }

    public static function renderAjax($view, $params = [])
    {
        return RenderExtensions::widget(['template' => self::getViewFile($view), 'params' => $params]);
    }

    public static function renderContent($view, $params = [])
    {
        return self::render($view, $params);
    }

    /*
     * If extension store its model in non standard folder.
     * For example UsersGroups extensions and its models into common/models folder
     * @return null|string - yii\db\ActiveRecord class
     */
    public static function getModel($modelName) {}

    /**
     * Get Dbg helper if consts DBG_ExtClassName === true
     * @return \common\helpers\Dbg
     */
    public static function dbg()
    {
        return \common\helpers\Dbg::ifDefined(self::getModuleCode());
    }

    /**
     * Get config value that
     * @param string $name
     * @return void|mixed void - in production mode if if config name is not found in Setup::getConfigureKeys
     * @throws Exception in development mode if config name is not found in Setup::getConfigureKeys
     */
    public static function getCfgValue(string $name, $platformId = null)
    {
        try {
            $cfgKeys = self::getSetupConfigureKeys();
            \common\helpers\Assert::keyExists($cfgKeys, $name, "The config key $name is not found in Setup::getConfigureKeys: %s");

            $platformId = ($cfgKeys[$name]['area']??null) == 'platforms' ? ($platformId ?? platform::currentId()) : 0;
            $defValue = $cfgKeys[$name]['value']??'';

            return \common\helpers\PlatformConfig::getVal($name, $defValue, $platformId);

        } catch (\Throwable $e) {
            \common\helpers\Php::handleErrorProd($e);
        }
    }

    public static function getCfgValueLower(string $name, $platformId = null)
    {
        return strtolower(self::getCfgValue($name, $platformId));
    }

    public static function beforeAction($action)
    {
        return true;
    }

}
