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

class Country {

    public static function new_get_countries($default = '', $showDisabled = true) {
        Global $languages_id;
        $countries_array = array();
        if ($default) {
            $countries_array[''] = $default;
        }
        $filter = '';
        if ($showDisabled == false) {
            $filter = ' and status=1';
        }
        $countries_query = tep_db_query("select countries_id, countries_name from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "'" . $filter . " order by countries_name");
        while ($countries = tep_db_fetch_array($countries_query)) {
            $countries_array[$countries['countries_id']] = $countries['countries_name'];
        }

        return $countries_array;
    }

    public static function get_countries($countries_id = '', $with_iso_codes = false, $default = '', $type = '') {
        global $languages_id;
        $countries_array = array();
        $first = 0;
        if (!empty($default)) {
            $countries_array[] = array(
                'id' => '',
                'text' => $default
            );
            $first = 1;
        }
        if (tep_not_null($countries_id)) {
            if ($with_iso_codes == true) {
//                $countries = tep_db_query("select countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, lat, lng, zoom from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $countries_id . "' and language_id = '" . (int) $languages_id . "' and status=1 order by sort_order, countries_name");
//                $countries_values = tep_db_fetch_array($countries);
                $countries_values = \common\models\Countries::find()
                    ->where(['countries_id'=>(int)$countries_id, 'language_id' => (int)$languages_id, 'status'=>1])
                    ->select(['countries_id', 'countries_name', 'countries_iso_code_2', 'countries_iso_code_3', 'address_format_id', 'dialling_prefix', 'lat', 'lng', 'zoom'])
                    ->cache(1200)
                    ->asArray()->one();

                $countries_array = array(
                    'id' => $countries_values['countries_id'] ?? null,
                    'text' => $countries_values['countries_name'] ?? null,
                    'countries_name' => $countries_values['countries_name'] ?? null,
                    'countries_iso_code_2' => $countries_values['countries_iso_code_2'] ?? null,
                    'countries_iso_code_3' => $countries_values['countries_iso_code_3'] ?? null,
                    'address_format_id' => $countries_values['address_format_id'] ?? null,
                    'dialling_prefix' => $countries_values['dialling_prefix'] ?? null,
                    'latitude' => $countries_values['lat'] ?? null,
                    'longitude' => $countries_values['lng'] ?? null,
                    'zoom' => $countries_values['zoom'] ?? null,
                );
            } else {
                $countries = tep_db_query("select countries_name from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $countries_id . "' and language_id = '" . (int) $languages_id . "' and status=1");
                $countries_values = tep_db_fetch_array($countries);
                $countries_array = array('countries_name' => $countries_values['countries_name']);
            }
        } else {
            $data = \frontend\design\Info::platformData();
            
            $c = self::getPlatformCountries($data['platform_id']??null, $type);
            
            if (empty($default)) {
                $countries_array = $c;
            } else {
                $countries_array = array_merge($countries_array, $c);
            }

            /*
            if ($data['country_id']) {
                $countries_array[$first] = array();
            }
            switch ($type) {
                case 'ship':
                    $geo_zones_query = tep_db_query("select z2gz.zone_country_id from " . TABLE_ZONES_TO_GEO_ZONES . " as z2gz left join " . TABLE_GEO_ZONES . " as gz ON (z2gz.geo_zone_id=gz.geo_zone_id) where gz.shipping_status = '1' group by zone_country_id");
                    $geo_zones_ids = [];
                    while ($geo_zones = tep_db_fetch_array($geo_zones_query)) {
                        $geo_zones_ids[] = $geo_zones['zone_country_id'];
                    }
                    if (in_array(0, $geo_zones_ids)) {
                        $countries = tep_db_query("select countries_id, countries_name, countries_iso_code_2 from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "' and status=1 order by sort_order, countries_name");
                    } elseif (count($geo_zones_ids) > 0) {
                        $countries = tep_db_query("select countries_id, countries_name, countries_iso_code_2 from " . TABLE_COUNTRIES . " where  language_id = '" . (int) $languages_id . "' and countries_id IN (" . implode(", ", $geo_zones_ids) . ") and status=1 order by sort_order, countries_name");
                    } else {
                        $countries = tep_db_query("select countries_id, countries_name, countries_iso_code_2 from " . TABLE_COUNTRIES . " where  language_id = '" . (int) $languages_id . "' and countries_id = '" . (int) $data['country_id'] . "' and status=1 order by sort_order, countries_name");
                    }
                    break;
                case 'bill':
                    $geo_zones_query = tep_db_query("select z2gz.zone_country_id from " . TABLE_ZONES_TO_GEO_ZONES . " as z2gz left join " . TABLE_GEO_ZONES . " as gz ON (z2gz.geo_zone_id=gz.geo_zone_id) where gz.billing_status = '1' group by zone_country_id");
                    $geo_zones_ids = [];
                    while ($geo_zones = tep_db_fetch_array($geo_zones_query)) {
                        $geo_zones_ids[] = $geo_zones['zone_country_id'];
                    }
                    if (in_array(0, $geo_zones_ids)) {
                        $countries = tep_db_query("select countries_id, countries_name, countries_iso_code_2 from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "' and status=1 order by sort_order, countries_name");
                    } elseif (count($geo_zones_ids) > 0) {
                        $countries = tep_db_query("select countries_id, countries_name, countries_iso_code_2 from " . TABLE_COUNTRIES . " where  language_id = '" . (int) $languages_id . "' and countries_id IN (" . implode(", ", $geo_zones_ids) . ") and status=1 order by sort_order, countries_name");
                    } else {
                        $countries = tep_db_query("select countries_id, countries_name, countries_iso_code_2 from " . TABLE_COUNTRIES . " where  language_id = '" . (int) $languages_id . "' and countries_id = '" . (int) $data['country_id'] . "' and status=1 order by sort_order, countries_name");
                    }
                    break;
                default :
                    $countries = tep_db_query("select countries_id, countries_name, countries_iso_code_2 from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "' and status=1 order by sort_order, countries_name");
                    break;
            }
            while ($countries_values = tep_db_fetch_array($countries)) {
                $country = array(
                    'id' => $countries_values['countries_id'],
                    'text' => $countries_values['countries_name'],
                    'countries_id' => $countries_values['countries_id'],
                    'countries_name' => $countries_values['countries_name'],
                    'countries_iso_code_2' => $countries_values['countries_iso_code_2'],
                );
                if ($data['country_id'] === $countries_values['countries_id']) {
                    $countries_array[$first] = $country;
                } else {
                    $countries_array[] = $country;
                }
            }
             */
        }

        return $countries_array;
    }

    public static function get_country_id($country_name) {

        $country_id_query = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_name = '" . tep_db_input($country_name) . "'");

        if (!tep_db_num_rows($country_id_query)) {
            return 0;
        } else {
            $country_id_row = tep_db_fetch_array($country_id_query);
            return $country_id_row['countries_id'];
        }
    }

    public static function get_country_name($country_id, $lan_id = 0) {
        global $languages_id;
        if ($lan_id == 0) {
            $lan_id = $languages_id;
        }
        static $_cached_results = [];
        $key = (int)$country_id."@" . (int)$lan_id;
        if ( !isset($_cached_results[$key]) ) {
            $country_query = tep_db_query("SELECT countries_name FROM " . TABLE_COUNTRIES . " WHERE countries_id = '" . (int)$country_id . "' AND language_id = '" . (int)$lan_id . "'");
            if (!tep_db_num_rows($country_query)) {
                $_cached_results[$key] = $country_id;
            } else {
                $country = tep_db_fetch_array($country_query);
                $_cached_results[$key] = $country['countries_name'];
            }
        }
        return $_cached_results[$key];
    }

    public static function get_country_info_by_id($country_id) {
        $country_array = self::get_countries($country_id, true);
        return $country_array;
    }

    public static function get_country_info_by_name($country_name, $language_id = '') {
        global $languages_id;
        if ($language_id == '' || $language_id == 0) {
            $language_id = $languages_id;
        }

        static $_cached_results = [];

        $key = strval($country_name)."@" . (int) $language_id;
        if ( !isset($_cached_results[$key]) ) {

            $res = tep_db_query("SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_name = '" . tep_db_input($country_name) . "' ORDER BY (IF(language_id = '" . (int)$language_id . "', 0,1)) LIMIT 1");
            $ret = array();

            if ($d = tep_db_fetch_array($res)) {
                $ret = array('id' => $d['countries_id'],
                    'title' => $d['countries_name'],
                    'iso_code_2' => $d['countries_iso_code_2'],
                    'iso_code_3' => $d['countries_iso_code_3'],
                    'address_format_id' => $d['address_format_id'],
                    'dialling_prefix' => $d['dialling_prefix'],
                    'zoom' => $d['zoom'],
                    'lng' => $d['lng'],
                    'lat' => $d['lat']
                );
            } else {
                $res = tep_db_query("SELECT * FROM " . TABLE_COUNTRIES . " WHERE soundex(countries_name) = soundex('" . tep_db_input($country_name) . "') OR countries_iso_code_2 LIKE '" . preg_replace("/\W/", "", tep_db_input($country_name)) . "' OR countries_iso_code_3 LIKE '" . preg_replace("/\W/", "", tep_db_input($country_name)) . "'");
                if ($d = tep_db_fetch_array($res)) {
                    $ret = array('id' => $d['countries_id'],
                        'title' => $d['countries_name'],
                        'iso_code_2' => $d['countries_iso_code_2'],
                        'iso_code_3' => $d['countries_iso_code_3'],
                        'address_format_id' => $d['address_format_id'],
                        'dialling_prefix' => $d['dialling_prefix'],
                        'zoom' => $d['zoom'],
                        'lng' => $d['lng'],
                        'lat' => $d['lat']
                    );
                } else {
                    $ret = $country_name;
                }
            }
            $_cached_results[$key] = $ret;
        }

        return $_cached_results[$key];
    }

    public static function get_country_info_by_iso($iso_code, $which='iso-2', $language_id = '') {
      global $languages_id;
      if ($language_id == '' || $language_id == 0) {
          $language_id = $languages_id;
      }
      if (!in_array($which, array('iso2', 'iso-2', 'iso3', 'iso-3'))) {
        $which = 'iso-2';
      }
      if ($which == 'iso-2' || $which == 'iso2') {
        $field = 'countries_iso_code_2';
      } else {
        $field = 'countries_iso_code_3';
      }
      
      $res = tep_db_query("select * from " . TABLE_COUNTRIES . " where $field = '" . tep_db_input($iso_code) . "' and language_id = '" . (int) $language_id . "'");
      $ret = array();

      if ($d = tep_db_fetch_array($res)) {
          $ret = array('id' => $d['countries_id'],
              'title' => $d['countries_name'],
              'iso_code_2' => $d['countries_iso_code_2'],
              'iso_code_3' => $d['countries_iso_code_3'],
              'address_format_id' => $d['address_format_id'],
              'dialling_prefix' => $d['dialling_prefix'],
              'zoom' => $d['zoom'],
              'lng' => $d['lng'],
              'lat' => $d['lat']
          );
      } else {
        $ret = $iso_code;
      }
      return $ret;
    }

    public static function checkPlatformCountry($country_id=null, $platform_id = null, $type = '') {
        if (is_null($country_id)) {
            $country_id = STORE_COUNTRY; //2do platform
        }
        $c = \yii\helpers\ArrayHelper::getColumn(self::getPlatformCountries($platform_id, $type), 'id');
        return in_array($country_id, $c);
    }

    public static function getDefaultShippingCountryId($platform_id = null) {
        return self::getDefaultPlatformCountryId($platform_id, 'ship');
    }

    public static function getDefaultBillingCountryId($platform_id = null) {
        return self::getDefaultPlatformCountryId($platform_id, 'bill');
    }

    public static function getDefaultTaxCountryId($platform_id = null) {
        return self::getDefaultPlatformCountryId($platform_id, 'tax');
    }

    public static function getDefaultPlatformCountryId($platform_id = null, $type = 'ship') {
        if (empty($platform_id)) {
            if (defined('PLATFORM_ID') && PLATFORM_ID) {
                $platform_id = PLATFORM_ID;
            } else {
                $platform_id = \common\classes\platform::defaultId();
            }
        }

        /**@var \common\classes\platform_config $platform_config */
        $platform_config = \Yii::$app->get('platform')->getConfig($platform_id);
        $ret = $platform_config->const_value('STORE_COUNTRY', 0);

        if (!in_array($type, ['ship', 'bill', 'tax'])) {
            $type = 'ship';
        }
        $tmp = self::getPlatformCountries($platform_id, $type);
        if (is_array($tmp)) {
            $cl = \yii\helpers\ArrayHelper::getColumn($tmp, 'id');

            if (is_array($cl) && !in_array($ret, $cl)) {
                $ret = $cl[0];
            }
        }

        return $ret;

    }

/**
 * select all countries assigned to platform either directly OR via appropriate geozone
 * @global int $languages_id
 * @param int $platform_id
 * @param string  $type any of 'ship', 'bill', 'tax' - else limitation is ignored
 * @param int $lang_id
 * @return array [['id', 'text', 'countries_id', 'countries_name', 'countries_iso_code_2', 'countries_iso_code_3'] ... ]
 */
    public static function getPlatformCountries($platform_id = null, $type = '', $lang_id=0) {
        global $languages_id;

        if (empty($lang_id)) {
            $lang_id = $languages_id;
        }

        if (empty($platform_id)) {
            if (defined('PLATFORM_ID') && PLATFORM_ID) {
                $platform_id = PLATFORM_ID;
            } else {
                $platform_id = \common\classes\platform::defaultId();
            }
        }
        $platform_config = \Yii::$app->get('platform')->getConfig($platform_id);
        $d = $platform_config->getPlatformAddress();

        $def_country_id = $d['country_id'];

        $q = \common\models\Countries::find()->alias('c')
            ->select([
                'id' => 'countries_id',
                'text' => 'countries_name',
                'countries_id',
                'countries_name',
                'countries_iso_code_2',
                'countries_iso_code_3'
            ])
            ->andWhere([
              'language_id' => (int)$lang_id,
              'status' => 1
                ]);

        if (!empty($def_country_id)) { // default country first
            $q->orderBy(new \yii\db\Expression('countries_id<>'. (int)$def_country_id));
        }
        $q->addOrderBy('sort_order, countries_name');

        $limitCountries = [];
        $check1 = false;
        if (in_array($type, ['ship', 'bill', 'tax'])) {
        ////zone limitation: 1) platform 2) type:ship/billing
            $zq = \common\models\GeoZones::find()->alias('gz')
                    ->joinWith('zones z2gz', false, 'inner join')
                    ->select('z2gz.zone_country_id');
            switch ($type) {
                case 'ship':
                    $zq->andWhere('gz.shipping_status = 1');
                    break;
                case 'bill':
                    $zq->andWhere('gz.billing_status = 1');
                    break;
                case 'tax':
                    $zq->andWhere('gz.taxable = 1');
                    break;
            }

            $check1 = \common\models\PlatformsGeoZones::find()->andWhere(['platform_id' => (int)$platform_id])->cache(1200)->exists();
            if (!empty($check1)) {
                $zq->joinWith('platformZones pz2gz', false, 'inner join')
                    ->andWhere(['pz2gz.platform_id' => (int)$platform_id]);
            }

            $limitCountries = $zq->cache(1200)->column();

        }

        //platform countries limitation
        $check2 = \common\models\PlatformsCountries::find()->andWhere(['platform_id' => (int)$platform_id])->cache(1200)->exists();
        if ($check2) {
            if ($check1 && !empty($limitCountries) && !in_array(0, $limitCountries)) {
                $q->andWhere([
                  'or', 
                  ['c.countries_id' => $limitCountries],
                  ['c.countries_id' => \common\models\PlatformsCountries::find()->select('countries_id')->andWhere(['platform_id' => (int)$platform_id])]
                ]);
            } else {
                $q->andWhere([
                  'c.countries_id' => \common\models\PlatformsCountries::find()->select('countries_id')->andWhere(['platform_id' => (int)$platform_id])
                ]);
            }
        } else {
            if (!empty($limitCountries) && !in_array(0, $limitCountries)) {
                $q->andWhere([
                  'c.countries_id' => $limitCountries
                ]);
            }
        }

        $q->cache(1200)->asArray();
        $countries_array = $q->all();

        return $countries_array;
    }


    public static function getShippingCountriesToPlatforms($lang_id=0) {
        global $languages_id;

        if (empty($lang_id)) {
            $lang_id = $languages_id;
        }
        if (defined('PLATFORM_ID') && PLATFORM_ID) {
            $platform_id = PLATFORM_ID;
        } else {
            $platform_id = \common\classes\platform::defaultId();
        }

        /*
         * platforms -< platforms_geo_zones -< geo_zones(geo_zones_id, shipping_status) >- zones_to_geo_zones(zone_country_id) >- countries
         *             platform_countries
         */
        
        //platform with all countries to ship
        $pq = \common\models\Platforms::find()->alias('pl')
            ->select('pl.platform_id')
            ->joinWith('geoZones pgz', false)->andOnCondition('pgz.shipping_status=1')
            ->joinWith('shipCountries pszc', false)
            ->joinWith('platformsCountries pc', false)
            ->andWhere([
                'and',
                    'pc.platform_id is null',
                    ['or',
                    'pgz.geo_zone_id is null',
                    'pszc.zone_country_id=0 '
                    ]
                ])
            ->andWhere(' pl.status=1 and pl.is_virtual=0 and pl.is_marketplace=0 ')
            ->orderBy(new \yii\db\Expression('pl.platform_id<>'. (int)$platform_id))
            ->distinct();

        $allCountriesPlatforms = $pq
            ->cache(1200)
            ->asArray()
            ->column()
            ;

        $platforms = \common\classes\platform::getList(false);

        if (count($platforms)==count($allCountriesPlatforms)) {
            $q = \common\models\Countries::find()->alias('c');

        } else {
            //some platformse with restricted shipping countries


            $q = \common\models\Countries::find()->alias('c')
                ->joinWith('shipZonePlatforms szp', false)
                ->joinWith('platforms pc', false)
                ->addSelect(
                  'pc.platform_id'
                )
                ->addSelect([
                  'platforms' => new \yii\db\Expression('group_concat(distinct szp.platform_id)')
                ])
                ->groupBy([
                    'id' => 'c.countries_id',
                    'text' => 'c.countries_name',
                    'countries_id',
                    'countries_name',
                    'countries_iso_code_2',
                    'countries_iso_code_3',
                    'pc.platform_id'
                ])
                //->addOrderBy(new \yii\db\Expression('pc.platform_id='. (int)$platform_id . ' desc'))
                //->addOrderBy(new \yii\db\Expression('group_concat(distinct szp.platform_id)='. (int)$platform_id . ' desc'))
                ;
        }
        $q->addSelect([
                'id' => 'c.countries_id',
                'text' => 'c.countries_name',
                'c.countries_id',
                'countries_name',
                'countries_iso_code_2',
                'countries_iso_code_3'
            ])
            ->andWhere([
              'c.language_id' => (int)$lang_id,
              'c.status' => 1
                ])
            ->addOrderBy('c.countries_name')
            ;

//echo __FILE__ .':' . __LINE__ . ' ' . $q->createCommand()->rawSql .  "<br>\n";
            $q->asArray()
                ->indexBy('id')
                ->cache(1200)
                ;
        $ret = $q->all();

        //fill in countries with $allCountriesPlatforms
        $add = count($allCountriesPlatforms);
        foreach ($ret as $k => $v) {

            if (!$add && empty($v['platform_id']) && empty($v['platforms'])) {
                unset($ret[$k]);
                continue;
            }

            if(!empty($v['platforms'])) {
                $ret[$k]['platforms'] = explode(",", $v['platforms']);
            } else {
                $ret[$k]['platforms'] = [];
            }

            $ret[$k]['platforms'] = array_values(array_unique(array_merge($ret[$k]['platforms'], $allCountriesPlatforms)));
            if (empty($v['platform_id']) && !empty($ret[$k]['platforms'])) {
                $ret[$k]['platform_id'] = $ret[$k]['platforms'][0];
            }
            
        }
        return $ret;
    }


}
