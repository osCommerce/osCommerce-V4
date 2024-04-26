<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\helpers;

class PlatformConfig {

    public static function getValue($key, $platformId = -1) {
        if (defined($key)) {
            //?add for better priority && $platformId < 1
            return constant($key);
        } else {
            if ($platformId < 1) {
                if ((int) \common\classes\platform::activeId() > 0) {
                    $platformId = (int) \common\classes\platform::activeId();
                } else {
                    $platformId = (int) \common\classes\platform::defaultId();
                }
            }
            $__platform = \Yii::$app->get('platform');
            $platformConfig = $__platform->config($platformId);
            return $platformConfig->const_value($key);
        }
    }

/**
 * return country_id from config or constant
 * @param int $platformId
 * @return int 
 */
    public static function getStoreCountry($platformId = -1) {
        $address = self::getDefaultAddress($platformId);
        $country_id = $address['country_id']??0;
        if (empty($country_id ) && defined('STORE_COUNTRY') && STORE_COUNTRY > 0) {
            $country_id = STORE_COUNTRY;
        }
        return $country_id;
    }
    
    public static function getDefaultAddress($platformId = -1) {
        if ($platformId < 1) {
            if ((int) \common\classes\platform::activeId() > 0) {
                $platformId = (int) \common\classes\platform::activeId();
            } else {
                $platformId = (int) \common\classes\platform::defaultId();
            }
        }
        $__platform = \Yii::$app->get('platform');
        $platformConfig = $__platform->config($platformId);
        return $platformConfig->getPlatformAddress();
    }

    public static function getFieldValue($field, $platformId = -1) {
        if ($platformId < 1) {
            if ((int) \common\classes\platform::activeId() > 0) {
                $platformId = (int) \common\classes\platform::activeId();
            } else {
                $platformId = (int) \common\classes\platform::defaultId();
            }
        }
        $__platform = \Yii::$app->get('platform');
        $platformConfig = $__platform->config($platformId);
        return $platformConfig->getPlatformDataField($field);
    }

    static $_cache = [];

    public static function getVal($key, $default = null, $platform_id = 0)
    {
        if (array_key_exists($platform_id . $key, self::$_cache)) {
            return self::$_cache[$platform_id . $key] ?? $default;
        } else {
            $configuration_value = \common\models\PlatformsConfiguration::find()->where(['platform_id' => $platform_id, 'configuration_key' => $key])->select('configuration_value')->scalar();
            self::$_cache[$platform_id . $key] = $configuration_value ?? null;
            return $configuration_value ?? $default;
        }
    }

    public static function setVal($key, $value, $platform_id = 0)
    {
        unset(self::$_cache[$platform_id . $key]);
        $row = \common\models\PlatformsConfiguration::findOne(['platform_id' => $platform_id, 'configuration_key' => $key]);
        if (empty($row)) {
            $row = new \common\models\PlatformsConfiguration();
            $row->platform_id = $platform_id;
            $row->configuration_key = $key;
        }
        $row->configuration_value = $value;
        $row->save(false);
    }

}
