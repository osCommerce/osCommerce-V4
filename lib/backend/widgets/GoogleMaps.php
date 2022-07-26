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

namespace backend\widgets;

use Yii;
use yii\base\Widget;

class GoogleMaps extends Widget {

    public function run() {
        $currencies = \Yii::$container->get('currencies');

        $params = ['currcode_left' => $currencies->currencies[DEFAULT_CURRENCY]['symbol_left'], 'currcode_right' => $currencies->currencies[DEFAULT_CURRENCY]['symbol_right']];
        if (defined('SHOW_GOOGLE_MAPS')) {
            $config = tep_db_fetch_array(tep_db_query("select configuration_title, configuration_id from " . TABLE_CONFIGURATION . " where configuration_key='SHOW_GOOGLE_MAPS'"));
            $params['enabled_map'] = $config;
            if (isset($params['enabled_map']['configuration_title'])) {
                $_t = \common\helpers\Translation::getTranslationValue('SHOW_GOOGLE_MAPS_TITLE', 'configuration');
                $params['enabled_map']['configuration_title'] = ($_t ? $_t : $params['enabled_map']['configuration_title'] );
            }
            if (SHOW_GOOGLE_MAPS == 'true') {
                $params['mapskey'] = (new \common\components\GoogleTools)->getMapProvider()->getMapsKey();

                $origPlace = array(0, 0, 2);
                $country_info = tep_db_fetch_array(tep_db_query("select ab.entry_country_id from " . TABLE_PLATFORMS_ADDRESS_BOOK . " ab inner join " . TABLE_PLATFORMS . " p on p.is_default = 1 and p.platform_id = ab.platform_id where ab.is_default = 1"));
                $_country = (int) STORE_COUNTRY;
                if ($country_info) {
                    $_country = $country_info['entry_country_id'];
                }
                if (defined('STORE_COUNTRY') && (int) STORE_COUNTRY > 0) {
                    $origPlace = tep_db_fetch_array(tep_db_query("select lat, lng, zoom from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $_country . "'"));
                }
                $params['origPlace'] = $origPlace;
            }
        }
        return $this->render('GoogleMaps.tpl', $params);
    }

}
