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

    public static function isAllowed(string $code)
    {

        if (!isset(self::$cacheAllowed[$code])) {
            self::$cacheAllowed[$code] = Acl::checkExtensionAllowed($code);
        }
        return self::$cacheAllowed[$code];
    }

    public static function isCustomerGroupsAllowed()
    {
        return self::isAllowed('UserGroups');
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

}