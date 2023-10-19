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

class Zones {

    public static function geoZoneAddressMatch($geoZoneId, $orderAddress)
    {
        return static::geoZoneMatch($geoZoneId, $orderAddress['country']['id'], $orderAddress['zone_id'], $orderAddress['postcode']);
    }

    protected static function splitPostcodeForCompare($postcode, $countryId)
    {
//From https://github.com/commerceguys/addressing/blob/master/src/AddressFormat/AddressFormatRepository.php
        $postcode = str_replace(' ', '', $postcode);
        $postal_code_pattern = 'GIR ?0AA|(?:(?:AB|AL|B|BA|BB|BD|BF|BH|BL|BN|BR|BS|BT|BX|CA|CB|CF|CH|CM|CO|CR|CT|CV|CW|DA|DD|DE|DG|DH|DL|DN|DT|DY|E|EC|EH|EN|EX|FK|FY|G|GL|GY|GU|HA|HD|HG|HP|HR|HS|HU|HX|IG|IM|IP|IV|JE|KA|KT|KW|KY|L|LA|LD|LE|LL|LN|LS|LU|M|ME|MK|ML|N|NE|NG|NN|NP|NR|NW|OL|OX|PA|PE|PH|PL|PO|PR|RG|RH|RM|S|SA|SE|SG|SK|SL|SM|SN|SO|SP|SR|SS|ST|SW|SY|TA|TD|TF|TN|TQ|TR|TS|TW|UB|W|WA|WC|WD|WF|WN|WR|WS|WV|YO|ZE)(?:\d[\dA-Z]? ?\d[ABD-HJLN-UW-Z]{2}))|BFPO ?\d{1,4}';
        preg_match('/^'.$postal_code_pattern.'/i', $postcode, $matches);
        if (!isset($matches[0]) || $matches[0] !== $postcode) {
            return preg_split('/-/',$postcode,2);
        }else{
            // valid uk postcode
            if (preg_match('/^([A-Za-z][A-Ha-hJ-Yj-y]?[0-9][A-Za-z0-9]?)(\d\D{2})$/',$postcode,$prefPostf)){
                return [$prefPostf[1],$prefPostf[2]];
            }
        }
        return [$postcode,''];
    }

    public static function geoZoneMatch($geoZoneId, $countryId, $zoneId, $postcode)
    {
        $postcode = strtoupper(str_replace(' ','',$postcode));
        $get_zones_r = tep_db_query(
            "SELECT association_id, zone_country_id, zone_id, postcode_start, postcode_end ".
            "FROM ".TABLE_ZONES_TO_GEO_ZONES." ".
            "WHERE geo_zone_id='".(int)$geoZoneId."' ".
            " AND zone_country_id='".(int)$countryId."' ".
            ""
        );
        if ( tep_db_num_rows($get_zones_r)>0 ) {
            $match = false;
            while( $_zone = tep_db_fetch_array($get_zones_r) ){
                $postcode_start = strtoupper(str_replace(' ','',$_zone['postcode_start']));
                $postcode_end = strtoupper(str_replace(' ','',$_zone['postcode_end']));
                if ( !empty($postcode_start) ) {
                    if (empty($postcode_end)) $postcode_end = $postcode_start;
                    list($start_prefix, $start_postfix) = static::splitPostcodeForCompare($postcode_start, $_zone['zone_country_id']);
                    list($end_prefix, $end_postfix) = static::splitPostcodeForCompare($postcode_end, $_zone['zone_country_id']);
                    list($main_prefix, $main_postfix) = static::splitPostcodeForCompare($postcode, $countryId);
                    if ( preg_match('/^([A-Za-z][A-Ha-hJ-Yj-y]?)([0-9]{1,2}[A-Za-z]?)$/',$main_prefix, $main_uk_compare)  ){
                        preg_match('/^([A-Za-z][A-Ha-hJ-Yj-y]?)([0-9]{1,2}[A-Za-z]?)$/',$start_prefix, $start_uk_compare);
                        preg_match('/^([A-Za-z][A-Ha-hJ-Yj-y]?)([0-9]{1,2}[A-Za-z]?)$/',$end_prefix, $end_uk_compare);
                        if ( empty($start_uk_compare) && empty($end_uk_compare) ) {
                            // {{ bad pattern in configuration - not UK postcode
                            if (substr($main_prefix, 0, strlen($start_prefix)) >= $start_prefix && substr($main_prefix, 0, strlen($end_prefix)) <= $end_prefix) {
                                $match = $_zone['association_id'];
                            } else {
                                continue;
                            }
                            // }}
                        }
                        if ($main_uk_compare[1]>=$start_uk_compare[1] && $main_uk_compare[1]<=$end_uk_compare[1]
                            && $main_uk_compare[2]>=$start_uk_compare[2] && $main_uk_compare[2]<=$end_uk_compare[2]) {
                            $match = $_zone['association_id'];
                        }else{
                            continue;
                        }
                    }else {
                        if (substr($main_prefix, 0, strlen($start_prefix)) >= $start_prefix && substr($main_prefix, 0, strlen($end_prefix)) <= $end_prefix) {
                            $match = $_zone['association_id'];
                        } else {
                            // post code configured and not match
                            continue;
                        }
                    }
                }

                if ( empty($_zone['zone_id']) ) {
                    $match = $_zone['association_id'];
                }elseif ( $_zone['zone_id']==$zoneId ){
                    $match = $_zone['association_id'];
                }
            }
            return $match;
        }
        return false;
    }

    public static function get_zone_class_title($zone_class_id) {
        if ($zone_class_id == '0') {
            return TEXT_NONE;
        } else {
            $classes_query = tep_db_query("select geo_zone_name from " . TABLE_TAX_ZONES . " where geo_zone_id = '" . (int) $zone_class_id . "'");
            $classes = tep_db_fetch_array($classes_query);
            return $classes['geo_zone_name'] ?? null;
        }
    }
    
    public static function get_geo_zone_class_title($zone_class_id) {
        if ($zone_class_id == '0') {
            return TEXT_NONE;
        } else {
            $classes_query = tep_db_query("select geo_zone_name from " . TABLE_GEO_ZONES . " where geo_zone_id = '" . (int) $zone_class_id . "'");
            $classes = tep_db_fetch_array($classes_query);
            return $classes['geo_zone_name'];
        }
    }

    public static function get_zone_code($country_id, $zone_id, $default_zone) {
        $zone_query = tep_db_query("select zone_code from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country_id . "' and zone_id = '" . (int) $zone_id . "'");
        if (tep_db_num_rows($zone_query)) {
            $zone = tep_db_fetch_array($zone_query);
            return $zone['zone_code'];
        } else {
            return $default_zone;
        }
    }

    public static function get_zone_name($country_id, $zone_id, $default_zone) {
        $zone_query = tep_db_query("select zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country_id . "' and zone_id = '" . (int) $zone_id . "'");
        if (tep_db_num_rows($zone_query)) {
            $zone = tep_db_fetch_array($zone_query);
            return $zone['zone_name'];
        } else {
            return $default_zone;
        }
    }

    public static function get_country_zones($country_id) {
        $zones_array = array();
        $zones_query = tep_db_query("select zone_id, zone_name from " . TABLE_ZONES . " where zone_country_id = '" . (int) $country_id . "' order by zone_name");
        while ($zones = tep_db_fetch_array($zones_query)) {
            $zones_array[] = array('id' => $zones['zone_id'],
                'text' => $zones['zone_name']);
        }

        return $zones_array;
    }

    public static function prepare_country_zones_pull_down($country_id = '') {
        // preset the width of the drop-down for Netscape
        $pre = '';
        if ((!\common\helpers\System::browser_detect('MSIE')) && (\common\helpers\System::browser_detect('Mozilla/4'))) {
            for ($i = 0; $i < 45; $i++)
                $pre .= '&nbsp;';
        }

        $zones = self::get_country_zones($country_id);

        if (sizeof($zones) > 0) {
            $zones_select = array(array('id' => '', 'text' => PLEASE_SELECT));
            $zones = array_merge($zones_select, $zones);
        } else {
            $zones = array(array('id' => '', 'text' => TYPE_BELOW));
            // create dummy options for Netscape to preset the height of the drop-down
            if ((!\common\helpers\System::browser_detect('MSIE')) && (\common\helpers\System::browser_detect('Mozilla/4'))) {
                for ($i = 0; $i < 9; $i++) {
                    $zones[] = array('id' => '', 'text' => $pre);
                }
            }
        }

        return $zones;
    }

    public static function get_zone_id($country_id, $zone_name) {
        static $cached = [];
        $key = (int)$country_id.'@'.strval($zone_name);
        if ( !isset($cached[$key]) ) {
            $zone_id_query = tep_db_query("SELECT * FROM " . TABLE_ZONES . " WHERE zone_country_id = '" . (int)$country_id . "' AND zone_name = '" . tep_db_input($zone_name) . "'");
            if (!tep_db_num_rows($zone_id_query)) {
                $zone_id_query = tep_db_query("SELECT * FROM " . TABLE_ZONES . " WHERE zone_country_id = '" . (int)$country_id . "' AND zone_code = '" . tep_db_input($zone_name) . "'");
            }

            if (!tep_db_num_rows($zone_id_query)) {
                $cached[$key] = 0;
            } else {
                $zone_id_row = tep_db_fetch_array($zone_id_query);
                $cached[$key] = $zone_id_row['zone_id'];
            }
        }
        return $cached[$key];
    }

    public static function lookupZone($country_id, $zone) {
        return \common\models\Zones::find()
            ->andwhere(['zone_country_id' => (int)$country_id ])
            ->andwhere([
                'or',
                ['like', 'zone_code', $zone, false],
                ['like', 'zone_name', $zone, false]
              ])
            ->asArray()
            ->cache(60)
            ->one();
    }

    public static function geo_zones_pull_down($parameters, $selected = '') {
        $select_string = '<select ' . $parameters . '>';
        $zones_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_TAX_ZONES . " order by geo_zone_name");
        while ($zones = tep_db_fetch_array($zones_query)) {
            $select_string .= '<option value="' . $zones['geo_zone_id'] . '"';
            if ($selected == $zones['geo_zone_id'])
                $select_string .= ' SELECTED';
            $select_string .= '>' . $zones['geo_zone_name'] . '</option>';
        }
        $select_string .= '</select>';

        return $select_string;
    }

    public static function stick_shipping_rates($rates_array, $innerTable=false, $size = false, $extra=[]) {
        $check_secret = preg_split('/[:;]/', $rates_array[0]??null);
        if (sizeof($check_secret) > 2) {
            return str_replace(',', '.', $rates_array[0]);
        }

        $output = '';
        if (is_array($rates_array)) {
            $keyIdx = array_keys($rates_array);
            for ($ki = 0; $ki < count($keyIdx); $ki+=2) {
                $i = $keyIdx[$ki];
                if ((float) $rates_array[$i] > 0) {
                    $output .= (float) str_replace(',', '.', $rates_array[$i]) . ':' . (float) str_replace(',', '.', $rates_array[$i + 1]);
                    if (substr($rates_array[$i + 1], -1) == '%') {
                        $output .= '%'; // allow % from cart total
                    }
                    if ( is_array($extra) ){
                        $extra_op = [];
                        if ( isset($extra['each'][$i]) && is_numeric($extra['each'][$i]) && $extra['each'][$i]!=0.0 ){
                            $extra_op['each'] = strval((float)str_replace(',', '.', $extra['each'][$i]));
                            if (isset($extra['each_from'][$i]) && is_numeric($extra['each_from'][$i])){
                                $extra_op['each_from'] = strval((float)str_replace(',', '.', $extra['each_from'][$i]));
                            }
                        }
                        if ( count($extra_op)>0 ) $output.= str_replace(':','@', \json_encode($extra_op));
                    }

                    if ( is_array($innerTable) && isset($innerTable[$i]) && is_array($innerTable[$i]) ) {
                        $innerTableString = '';
                        $startPrice = array_map(function($item){
                            return $item['from'];
                        },$innerTable[$i]);
                        array_multisort($startPrice,SORT_NUMERIC,$innerTable[$i]);
                        foreach ($innerTable[$i] as $innerValue) {
                            if ($size) {
                                if (
                                        ( strlen($innerValue['from_w'])>0 || strlen($innerValue['to_w'])>0 )
                                        || ( strlen($innerValue['from_l'])>0 || strlen($innerValue['to_l'])>0 )
                                        || ( strlen($innerValue['from_h'])>0 || strlen($innerValue['to_h'])>0 )
                                        || ( strlen($innerValue['from_v'])>0 || strlen($innerValue['to_v'])>0 )
                                    )
                                {
                                    if ( strlen($innerValue['from_w'])>0 ) {
                                        $innerValue['from_w'] = floatval(str_replace(',','.',$innerValue['from_w']));
                                    }
                                    if ( strlen($innerValue['to_w'])>0 ) {
                                        $innerValue['to_w'] = floatval(str_replace(',','.',$innerValue['to_w']));
                                    }
                                    if ( strlen($innerValue['from_l'])>0 ) {
                                        $innerValue['from_l'] = floatval(str_replace(',','.',$innerValue['from_l']));
                                    }
                                    if ( strlen($innerValue['to_l'])>0 ) {
                                        $innerValue['to_l'] = floatval(str_replace(',','.',$innerValue['to_l']));
                                    }
                                    if ( strlen($innerValue['from_h'])>0 ) {
                                        $innerValue['from_h'] = floatval(str_replace(',','.',$innerValue['from_h']));
                                    }
                                    if ( strlen($innerValue['to_h'])>0 ) {
                                        $innerValue['to_h'] = floatval(str_replace(',','.',$innerValue['to_h']));
                                    }
                                    if ( strlen($innerValue['from_v'])>0 ) {
                                        $innerValue['from_v'] = floatval(str_replace(',','.',$innerValue['from_v']));
                                    }
                                    if ( strlen($innerValue['to_v'])>0 ) {
                                        $innerValue['to_v'] = floatval(str_replace(',','.',$innerValue['to_v']));
                                    }
                                    if ( !empty($innerTableString) ) $innerTableString .= '|';
                                    $innerTableString .=
                                        $innerValue['from_w'].'@'.$innerValue['to_w'].'@'.
                                        $innerValue['from_l'].'@'.$innerValue['to_l'].'@'.
                                        $innerValue['from_h'].'@'.$innerValue['to_h'].'@'.
                                        $innerValue['from_v'].'@'.$innerValue['to_v'].'@'.
                                        floatval(str_replace(',','.',$innerValue['value']));
                                }
                            } else if ( strlen($innerValue['from'])>0 || strlen($innerValue['to'])>0 ) {
                                if ( strlen($innerValue['from'])>0 ) {
                                    $innerValue['from'] = floatval(str_replace(',','.',$innerValue['from']));
                                }
                                if ( strlen($innerValue['to'])>0 ) {
                                    $innerValue['to'] = floatval(str_replace(',','.',$innerValue['to']));
                                }
                                if ( !empty($innerTableString) ) $innerTableString .= '|';
                                $innerTableString .=
                                    $innerValue['from'].'@'.$innerValue['to'].'@'.floatval(str_replace(',','.',$innerValue['value']));
                            }
                        }
                        if ( !empty($innerTableString) ) {
                            $output .= '('.$innerTableString.')';
                        }
                    }
                    $output .= ';';
                }
            }
        }
        return $output;
    }

    /**
     * Used in shippings if there is zone restriction
     * @param $geoZoneId
     * @param $countryId
     * @param $stateId
     * @return bool true is $countryId and $stateId belong $geoZoneId
     */
    public static function isGeoZone($geoZoneId, $countryId, $stateId = 0 /* All zones*/): bool
    {
        if (empty($countryId)) return false;

        $res = \common\models\ZonesToGeoZones::find()
            ->where(['geo_zone_id' => $geoZoneId])
            ->andWhere('COALESCE(zone_country_id,0) = 0 OR (zone_country_id = :country_id AND (COALESCE(zone_id,0) = 0 OR zone_id=:state_id))', [
                ':country_id' => $countryId,
                ':state_id' => $stateId
            ]);
        $res = $res->one();
        return !empty($res);
    }

}
