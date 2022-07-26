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

use common\models\PlatformsConfiguration;

class Configuration {

    public static function get_configuration_key_value($lookup) {
        $configuration_query_raw = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key='" . $lookup . "'");
        $configuration_query = tep_db_fetch_array($configuration_query_raw);
        $lookup_value = $configuration_query['configuration_value'];
        return $lookup_value;
    }

    public static function get_platform_configuration_key_value($platformId, $lookup) {
        $configuration_query_raw = tep_db_query("select configuration_value from " . TABLE_PLATFORMS_CONFIGURATION . " where configuration_key='" . $lookup . "' and platform_id = '" . (int)$platformId . "'");
        $configuration_query = tep_db_fetch_array($configuration_query_raw);
        $lookup_value = $configuration_query['configuration_value'];
        return $lookup_value;
    }

    public static function copyPlatformModuleSetting($target_platform_id, $source_platform_id=0)
    {
        if ( empty($source_platform_id) ) $source_platform_id = \common\classes\platform::defaultId();
        if ( $target_platform_id==0 || (int)$source_platform_id==(int)$target_platform_id ) return;

        $source_config_array = \common\models\PlatformsConfiguration::find()
            ->where(['platform_id'=> (int) $source_platform_id])
            /*->andWhere([
                'OR',
                ['IN', 'configuration_key', ['DD_MODULE_ORDER_TOTAL_SORT', 'DD_MODULE_PAYMENT_SORT', 'DD_MODULE_SHIPPING_SORT']],
                ['LIKE', 'configuration_key', 'MODULE\_ORDER\_TOTAL\_%', false],
                ['LIKE', 'configuration_key', 'MODULE\_SHIPPING\_%', false],
                ['LIKE', 'configuration_key', 'MODULE\_PAYMENT\_%', false],
            ])*/
            ->asArray()->all();
        foreach ($source_config_array as $source_config){
            $targetConfigModel = \common\models\PlatformsConfiguration::find()
                ->where([
                    'platform_id' => $target_platform_id,
                    'configuration_key' => $source_config['configuration_key']]
                )->one();
            if ( !$targetConfigModel ){
                $targetConfigModel = new PlatformsConfiguration(array_merge($source_config,['configuration_id'=>null, 'platform_id' => $target_platform_id]));
                $targetConfigModel->save();
            }
        }
        \common\models\VisibilityArea::deleteAll(['platform_id'=>(int)$target_platform_id]);
        foreach(\common\models\VisibilityArea::find()
                    ->where(['platform_id' => (int)$source_platform_id])
                    ->asArray()->all() as $visibilityData){
            $visibilityData['platform_id'] = (int)$target_platform_id;
            $copiedModel = new \common\models\VisibilityArea($visibilityData);
            $copiedModel->save();
        }
    }
}
