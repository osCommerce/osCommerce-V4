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

use \common\classes\modules\ModuleExtensions;
use \common\helpers\Acl;

class Extensions
{

    private static $cacheAllowed = [];
    private static $cacheEnabled = []; // cache for Acl::checkXXX instead of const 'ext_EXTENSION_STATUS'

    /**
     * Returns extension class if it exists and is allowed
     * @param string $code extension classname
     * @return bool|mixed|string
     */
    public static function isAllowed(string $code)
    {
        return self::allowed($code);
    }

    /**
     * return extension class if allowed and $func return true
     * @param string $code
     * @param string $func
     * @param array $args
     * @return bool|mixed|string
     */
    public static function isAllowedAnd(string $code, string $func, array $args = null)
    {
        if (($ext = self::isAllowed($code)) &&
            (
                (method_exists($ext, $func) && call_user_func([$ext, $func], $args)) ||
                (method_exists($ext, 'cfg') && class_exists($cfgClass=$ext::cfg()) && method_exists($cfgClass, $func) && call_user_func([$cfgClass, $func], $args))
            ))
        {
            return $ext;
        }
        return false;
    }

    /**
     * Calls $func if extension $code is allowed
     * @param string $code - extension classname
     * @param string $func - static extension function name
     * @param array $args - args for function $func
     * @return false|mixed - false if extension is not allowed or return value of $func
     */
    public static function callIfAllowed(string $code, string $func, array $args = [])
    {
        if (($ext = self::isAllowed($code)) && method_exists($ext, $func)) {
            return call_user_func([$ext, $func], $args);
        }
        return false;
    }

    private static function allowed(string $code, $func = 'allowed')
    {
        if ($func == 'allowed') {
            if (!isset(self::$cacheAllowed[$code])) {
                self::$cacheAllowed[$code] = Acl::checkExtensionAllowed($code);
            }
            return self::$cacheAllowed[$code];
        }
        return Acl::checkExtensionAllowed($code, $func);
    }

    /*
     * @return null|common\extensions\UserGroups\UserGroups
     */
    public static function isCustomerGroupsAllowed()
    {
        return self::isAllowed('UserGroups');
    }

    /*
     * @return null|common\extensions\CronScheduler\CronScheduler
     */
    public static function isCronScheduler($funcName = null)
    {
        $ext = self::isAllowed('CronScheduler');
        if ($ext && (empty($funcName) || method_exists($ext, $funcName))) {
            return $ext;
        }
    }

    /*
     * @return null|common\extensions\Inventory\Inventory
     */
    public static function isInventoryAllowed()
    {
        return self::isAllowed('Inventory');
    }

    private static function getState($code)
    {
        if (!isset(self::$cacheEnabled[$code])) {
            $row = \common\models\PlatformsConfiguration::findOne(['configuration_key' => $code . '_EXTENSION_STATUS', 'platform_id' => 0]);
            if (empty($row) || !class_exists("\\common\\extensions\\$code\\$code")) {
                self::$cacheEnabled[$code] = 'uninstalled';
            } elseif ($row->configuration_value == 'True') {
                self::$cacheEnabled[$code] = 'enabled';
            } else {
                self::$cacheEnabled[$code] = 'disabled';
            }
        }
        return self::$cacheEnabled[$code];
    }

    public static function isEnabled($code)
    {
        return self::getState($code) == 'enabled';
    }

    public static function isInstalled($code)
    {
        return in_array(self::getState($code), ['enabled', 'disabled']);
    }

    public static function isUninstalled($code)
    {
        return self::getState($code) == 'uninstalled';
    }

    public static function isDisabled($code)
    {
        return self::getState($code) == 'disabled';
    }

    public static function clearCache($code = null)
    {
        if (empty($code)) {
            self::$cacheEnabled = [];
            self::$cacheAllowed = [];
        } else {
            unset(self::$cacheEnabled[$code]);
            unset(self::$cacheAllowed[$code]);
        }
    }

    /**
     * @param $code - extension classname
     * @return null|string null - success, string - error message
     */
    public static function installSafe($code)
    {
        try {
            self::install($code);
        } catch (\Exception $e) {
            \Yii::error( sprintf("%s: %s\n%s", __FUNCTION__, $e->getMessage(), $e->getTraceAsString() ));
            return $e->getMessage();
        }
    }

    public static function install($code)
    {
        $ext = \common\helpers\Acl::checkExtension($code, 'allowed');
        if (!$ext) throw new \Exception("Extension $code not found");

        if (self::isAllowed($code)) throw new \Exception("Extension $code already installed");

        $obj = new $ext;
        $obj->install(0);

        $obj->enable_module(0, true);
        self::clearCache($code);
    }

    /**
     * @param $code - extension classname
     * @param bool $forceIfUninstalled set true to call remove method even if extension is already uninstalled
     * @param array|null $options uninstall options ['userConfirmedDropDatatables', 'userConfirmedDeleteAcl']
     * @return null|string null - success, string - error message
     */
    public static function uninstall($code, $forceIfUninstalled = false, $options = null)
    {
        $ext = self::isAllowed($code);
        if (!$ext) {
            if ($forceIfUninstalled) {
                $ext = \common\helpers\Acl::checkExtension($code, 'enabled');
                if (!$ext) {
                    throw new \Exception("Extenstion $code is not exist on the disk");
                }
            } else {
                throw new \Exception("Extenstion $code is not installed");
            }
        }
        $obj = new $ext;
        if (is_array($options)) {
            foreach ($options as $option) {
                if (!property_exists($obj, $options)) {
                    throw new \Exception("Property $option is not exists in extenstion $code");
                }
                $obj->$options = true;
            }
        }
        $obj->remove(0);

        $obj->enable_module(0, false);
        self::clearCache($code);
    }

    /**
     * @param $code - extension classname
     * @param bool $forceIfUninstalled set true to call remove method even if extension is already uninstalled
     * @param array|null $options uninstall options
     * @return null|string null - success, string - error message
     */
    public static function uninstallSafe($code, $forceIfUninstalled = false, $options = null)
    {
        try {
            self::uninstall($code, $forceIfUninstalled, $options);
        } catch (\Exception $e) {
            \Yii::error( sprintf("%s: %s\n%s", __FUNCTION__, $e->getMessage(), $e->getTraceAsString() ));
            return $e->getMessage();
        }
    }


    public static function getBaseDirRelative($code)
    {
        return 'lib/common/extensions/' . $code;
    }

    /**
     * Get image file name for extension $code
     * @param $code - extension class
     * @param $imageFN - base image file name like 'image1.png'
     * @param $defImageFN - path to default image
     * @return null|string
     */
    public static function getImageRelative($code, $imageFN, $defImageFN = null)
    {
        $baseDir = self::getBaseDirRelative($code) . '/';
        if (file_exists(\Yii::getAlias('@site_root/' . ($res = $baseDir . 'images/' . $imageFN)))) {
            return $res;
        } elseif (file_exists(\Yii::getAlias('@site_root/' . ($res = $baseDir . $imageFN)))) {
            return $res;
        } else {
            return $defImageFN;
        }
    }


    /**
     * @param string $class className of extension
     * @param string $relativeModelName 'models\Collections' or just 'Collections'
     * @param string|null $allowedFunc
     * @return \yii\db\ActiveRecord|null
     */
    public static function getModel($class, $relativeModelName, $allowedFunc = 'allowed')
    {
        /** @var \common\classes\modules\ModuleExtensions $ext */
        if ($ext = self::allowed($class, 'enabled')) {
            if (method_exists($ext, 'getModel') && ($model = $ext::getModel($relativeModelName)) && class_exists($model)) {
                return $model;
            }
            if (!empty($allowedFunc) && !(method_exists($ext, $allowedFunc) && call_user_func([$ext, $allowedFunc]))) return null;
            $reflection_class = new \ReflectionClass($ext);
            $namespace = $reflection_class->getNamespaceName();
            $modelClass = $namespace . "\\$relativeModelName";
            if (!class_exists($modelClass) || ($class == $relativeModelName)) {
                $modelClass = $namespace . "\\models\\$relativeModelName";
            }
            if (class_exists($modelClass) && \Yii::$app->db->schema->getTableSchema($modelClass::tablename()) !==null) {
                return $modelClass;
            }
        }
    }

    /**
     * Check extensions for hide or show in "Modules Restrictions Visibility on pages"
     * @param $visibilityConstant string
     * @return bool
     */
    public static function isVisibility($visibilityConstant)
    {
        $const = [
            'Quotations' => [
                'TEXT_EMAIL_QUOTE',
                'TEXT_QUOTE_CART',
                'TEXT_QUOTE_CHECKOUT'
            ],
            'Samples' => [
                'TEXT_EMAIL_SAMPLE',
            ],
        ];

        foreach ($const as $extension => $constants) {
            if (in_array($visibilityConstant, $constants)) {
                return self::isAllowed($extension);
            }
        }
        return true;
    }

    /**
     * Check extensions and POS available for "Modules Restrictions Available for"
     * @param $variant string
     * @return bool
     */
    public static function isVisibilityVariant($variant)
    {
        $extVariants = [
            'shop_quote' => 'Quotations',
            'shop_sample' => 'Samples',
            'moderator' => 'GroupAdministrator'
        ];
        if ($variant == 'pos') return self::isPosExist();
        if (!empty($extVariants[$variant])) {
            return self::isAllowed($extVariants[$variant]);
        }
        return true;
    }

    /**
     * Get correct visibility variants for "Modules Restrictions Available for"
     * @param $variants array | string
     * @return array
     * @uses isVisibilityVariant() for check available extensions and POS
     */
    public static function getVisibilityVariants($variants)
    {
        $result = [];
        if (is_array($variants)) {
            foreach ($variants as $variant) {
                if (self::isVisibilityVariant($variant)) {
                    $result[] = $variant;
                }
            }
        } else {
            if (self::isVisibilityVariant($variants)) {
                $result[] = $variants;
            }
        }
        return $result;
    }

    public static function isPosExist()
    {
        return file_exists(\Yii::getAlias('@pos'));
    }

    public static function checkSetup($extClass, $setupFuncName = null)
    {
        if (($ext = self::isAllowed($extClass)) && method_exists($ext, 'checkSetup')) {
            return $ext::checkSetup($setupFuncName);
        }
        return false;
    }

    public static function getOverwrittenCfgKeys()
    {
        static $keysAll = null;
        if (is_null($keysAll)) {
            $keysAll = \Yii::$app->getCache()->getOrSet('overwritten-config-keys', function () {
                $res = [];
                $extensions = new \DirectoryIterator(\Yii::$aliases['@common'] . '/extensions/');
                foreach($extensions as $extFile){
                    $class = $extFile->getFilename();
                    if (method_exists(self::class, 'checkSetup') && ($setup = self::checkSetup($class, 'getOverwrittenCfgKeys'))){
                        $keys = $setup::getOverwrittenCfgKeys();
                        if (is_array($keys) && count($keys)>0){
                            foreach ($keys as &$arr) {
                                $arr['extension'] = $class;
                            }
                            $res = array_merge($res, $keys);
                        }
                    }
                }
                return $res;
            },0, new \yii\caching\TagDependency(['tags'=>['extension_changed']]));
        }
        return $keysAll;
    }

    public static function getOverwrittenCfgKey($configKey)
    {
        $res = self::getOverwrittenCfgKeys()[$configKey] ?? null;
        if (is_array($res) && !isset($res['value'])) {
            $class = $res['extension'];
            $defaultValue = '<a href="%s">%s</a>';
            \common\helpers\Translation::init('configuration');
            $defaultCaption = defined('TEXT_EXTENSION_OVERWRITE_CONFIG_KEY')? TEXT_EXTENSION_OVERWRITE_CONFIG_KEY : 'The extension <strong>%s</strong> enhances this option</a>';
            $url = \Yii::$app->urlManager->createUrl(['modules/edit', 'set' => 'extensions', 'module' => $class]);
            $caption = isset($arr['caption']) ? $arr['caption'] : sprintf($defaultCaption, $class);
            $res['value'] = sprintf($defaultValue, $url, $caption);
        }
        return $res;
    }

}