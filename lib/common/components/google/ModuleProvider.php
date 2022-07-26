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

namespace common\components\google;

use Yii;
use yii\helpers\Inflector;
use common\models\repositories\GoogleSettingsRepository;

class ModuleProvider extends Providers {

    private static $modules = ['analytics', 'adwords', 'ecommerce', 'verification', 'tagmanger', 'reviews', 'klaviyo_analytics', 'klaviyo_ecommerce', 'facebook_pixel', 'twitter_pixel', 'tiktok_pixel', 'bing', 'gtag', 'hotjar'];
    private $gsRepository;

    public function __construct(GoogleSettingsRepository $gsRepository) {
        $this->gsRepository = $gsRepository;
    }

    public function getInstalledModules($platform_id, $console = false) {
        static $front_active_cache = [];
        if (!$console && \frontend\design\Info::isTotallyAdmin()) {
            $modules = $this->gsRepository->getSettings(self::$modules, $platform_id);
        } else {
            if (isset($front_active_cache[(int) $platform_id])) {
                return $front_active_cache[(int) $platform_id];
            }
            $modules = $this->gsRepository->getSettings(self::$modules, $platform_id, true);
        }
        $mods = [];
        if (is_array($modules)) {
            foreach ($modules as $mod) {
                $module = $this->_describeSetting($mod, true);
                if (!is_object($module)) continue;
                $mods[$mod['module']] = $module;
            }
        }

        $front_active_cache[(int) $platform_id] = $mods;

        return $mods;
    }

    private function getModuleObject($module) {
        $class = "common\\modules\\analytic\\{$module}";
        if (class_exists($class)) {
            $object = new $class;
            $object->setProvider($this);
            $object->getParams();
            return $object;
        }
        return false;
    }

    public function getUninstalledModules($platform_id) { // return all modules
        $mods = [];
        $installed = $this->getInstalledModules($platform_id);
        foreach (self::$modules as $_mod) {
            if (!isset($installed[$_mod])) {
                if ($module = $this->getModuleObject($_mod)) {
                    $params = $module->getParams();
                    $mods = array_merge($mods, $params);
                }
            }
        }
        return $mods;
    }

    public function getInstalledById($id, $overload = true) {
        $setting = $this->gsRepository->findById($id);
        if ($setting) {
            return $this->_describeSetting($setting, $overload);
        }
        return false;
    }

    public function getActiveByCode($code, $platform_id) {
        $setting = $this->gsRepository->getSetting($code, $platform_id, 1);
        if ($setting) {
            return $this->_describeSetting($setting, true);
        }
        return false;
    }

    private function _describeSetting($setting, $overload = true) {
        if ($module = $this->getModuleObject($setting->module)) {
            if (tep_not_null($setting->info)) {
                $module->overloadConfig($setting->info);
            }
            $module->params = (array) $setting->getAttributes();
            return $module;
        }
        return false;
    }

    public function overloadConfig($config) {
        $this->config = unserialize($config);
        return $this;
    }

    public function save($module) {
        $setting = $this->gsRepository->findById($module->params['google_settings_id']);
        if ($setting) {
            $this->gsRepository->updateSetting($setting, [$this->gsRepository->getConfigHolder() => serialize($module->config)]);
        }
    }

    public function perform($module, $action, $platform_id, $status = 0) {
        if ($object = $this->getModuleObject($module)) {
            if (method_exists($this, $action)) {
                $this->$action($object, $platform_id, $status);
            }
        }
    }

    public function remove(modules\AbstractGoogle $module, $platform_id, $status) {
        $installed = $this->gsRepository->getSetting($module->code, $platform_id);
        return $installed ? $this->gsRepository->delete($installed->google_settings_id) : false;
    }

    public function install(modules\AbstractGoogle $module, $platform_id, $status) {
        $installed = $this->gsRepository->getSetting($module->code, $platform_id);
        if (!$installed) {
            return $this->gsRepository->createSetting($module->code, (string) $module->config[$module->code]['name'], serialize($module->config), $platform_id, $status);
        }
        return false;
    }

    public function status(modules\AbstractGoogle $module, $platform_id, $status) {
        $setting = $this->gsRepository->getSetting($module->code, $platform_id);
        if ($setting) {
            return $this->gsRepository->updateSetting($setting, ['status' => (int) $status]);
        }
        return false;
    }

    public static function notify() {
        \common\helpers\Translation::init('checkout/success');
        if (defined('TEXT_NEED_SETUP_ANALYTICS') && defined('IMAGE_BUTTON_NOTIFICATIONS')) {
            \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, IMAGE_BUTTON_NOTIFICATIONS, TEXT_NEED_SETUP_ANALYTICS, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }
    }

    public function getApiResult($url, $method, $params = array()) {
        //??what for
        $data = http_build_query($params);
        $fp = @file_get_contents($url . '?' . $data, false);
        return $fp;
    }

}
