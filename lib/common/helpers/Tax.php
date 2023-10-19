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

class Tax {
    const CACHE_TIME = 600;
    
    public static function getDefaultTaxClassIdForProducts()
    {
        $mostUsedTaxClassId = \common\models\Products::find()
            ->select('products_tax_class_id')
            ->groupBy('products_tax_class_id')
            ->orderBy(new \yii\db\Expression('COUNT(*) DESC'))
            ->limit(1)
            ->scalar();
        return (int)$mostUsedTaxClassId;
    }

    public static function add_tax($price, $tax) {
/*      // Rounding to currency decimal places moved to $currencies->calculate_price()
        $currency = \Yii::$app->settings->get('currency');
        $currencies = \Yii::$container->get('currencies');
        if (is_null($currency)) {
            $currency = DEFAULT_CURRENCY;
        }
*/
        if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
            return round(self::roundPriceExc($price), 6);
        } elseif ((DISPLAY_PRICE_WITH_TAX == 'true') && ($tax > 0)) {
            return round(self::roundPriceExc($price) + self::roundTax(self::calculate_tax($price, $tax)), 6); //$currencies->currencies[$currency]['decimal_places'];
        } else {
            return round(self::roundPriceExc($price), 6); //$currencies->currencies[$currency]['decimal_places'];
        }
    }

    public static function get_untaxed_value($price, $tax) {
        if ($tax == 100) return $price / 2;
        return $price * 100 / ($tax + 100);
    }

    public static function add_tax_always($price, $tax) {
/*
        $currency = \Yii::$app->settings->get('currency');
        $currencies = \Yii::$container->get('currencies');
        if (is_null($currency)) {
            $currency = DEFAULT_CURRENCY;
        }
*/
        return round(self::roundPriceExc($price) + self::roundTax(self::calculate_tax($price, $tax)), 6); //$currencies->currencies[$currency]['decimal_places'];
    }
    
    public static function reduce_tax_always($price, $tax) {
        return round(self::get_untaxed_value($price, $tax), 6);
    }

    public static function calculate_tax($price, $tax) {
        //$currency = \Yii::$app->settings->get('currency');
        //$currencies = \Yii::$container->get('currencies');
        return round((float)$price * $tax / 100, 6); //$currencies->currencies[$currency]['decimal_places']
    }

    public static function get_tax_description($class_id, $country_id, $zone_id) {
        static $_cached = [];
        $key = (int)$class_id.'@'.(int)$country_id.'@'.(int)$zone_id;
        if ( !isset($_cached[$key]) ) {
            $tax_query = tep_db_query("SELECT tax_description FROM " . TABLE_TAX_RATES . " tr LEFT JOIN " . TABLE_ZONES_TO_TAX_ZONES . " za ON (tr.tax_zone_id = za.geo_zone_id) LEFT JOIN " . TABLE_TAX_ZONES . " tz ON (tz.geo_zone_id = tr.tax_zone_id) WHERE (za.zone_country_id IS NULL OR za.zone_country_id = '0' OR za.zone_country_id = '" . (int)$country_id . "') AND (za.zone_id IS NULL OR za.zone_id = '0' OR za.zone_id = '" . (int)$zone_id . "') AND tr.tax_class_id = '" . (int)$class_id . "' ORDER BY tr.tax_priority");
            if (tep_db_num_rows($tax_query)) {
                $tax_description = '';
                while ($tax = tep_db_fetch_array($tax_query)) {
                    $tax_description .= $tax['tax_description'] . ' + ';
                }
                $tax_description = substr($tax_description, 0, -3);

                $_cached[$key] = $tax_description;
            } else {
                $_cached[$key] = TEXT_UNKNOWN_TAX_RATE;
            }
        }
        return $_cached[$key];
    }

    public static function display_tax_value($value, $padding = TAX_DECIMAL_PLACES) {
        if (strpos($value, '.')) {
            $loop = true;
            while ($loop) {
                if (substr($value, -1) == '0') {
                    $value = substr($value, 0, -1);
                } else {
                    $loop = false;
                    if (substr($value, -1) == '.') {
                        $value = substr($value, 0, -1);
                    }
                }
            }
        }

        if ($padding > 0) {
            if ($decimal_pos = strpos($value, '.')) {
                $decimals = strlen(substr($value, ($decimal_pos + 1)));
                for ($i = $decimals; $i < $padding; $i++) {
                    $value .= '0';
                }
            } else {
                $value .= '.';
                for ($i = 0; $i < $padding; $i++) {
                    $value .= '0';
                }
            }
        }

        return $value;
    }

/**
 *
 * @global array $tax_rates_array
 * @param int $class_id
 * @param int $country_id
 * @param int $zone_id
 * @param array $extraAddress - 2do: USA Tax rate could depend on postcode (county)
 * @param bool $check_group
 * @params int $customer_groups_id - for extension
 * @return int
 */
    public static function get_tax_rate($class_id, $country_id = -1, $zone_id = -1, $extraAddress='', $check_group = true, $customer_groups_id = null) {
        if (is_null($customer_groups_id)) {
            $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        }

        $tax_rate = false;
        foreach (\common\helpers\Hooks::getList('tax/get-tax-rate') as $filename) {
            $tax_rate = include($filename);
            if ($tax_rate !== false) return $tax_rate;
        }
        global $tax_rates_array;

        $_pos = 1;

        if ($check_group && $ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            if ($ext::checkTaxRate($customer_groups_id)) {
                if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                    $_pos = -1;
                } else {
                    return 0;
                }
            }
        }

        /** @var \common\extensions\VatOnOrder\VatOnOrder $VatOnOrder */
        if ($VatOnOrder = \common\helpers\Acl::checkExtensionAllowed('VatOnOrder', 'allowed')) {
            if ($VatOnOrder::check_tax_rate($country_id != -1? true : false)) {
                if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                    $_pos = -1;
                } else {
                    return 0;
                }
            }
        }

        if (($country_id == -1) && ($zone_id == -1)) {
            if (\Yii::$app->user->isGuest){
                $country_id = \Yii::$app->storage->has('customer_country_id')? \Yii::$app->storage->get('customer_country_id') : PlatformConfig::getValue('STORE_COUNTRY');
                $zone_id = \Yii::$app->storage->has('customer_zone_id')? \Yii::$app->storage->get('customer_zone_id') : PlatformConfig::getValue('STORE_ZONE');
                //$country_id = STORE_COUNTRY;
                //$zone_id = STORE_ZONE;
            } else {
                $country_id = \Yii::$app->user->getIdentity()->get('customer_country_id') ?? PlatformConfig::getValue('STORE_COUNTRY');
                $zone_id = \Yii::$app->user->getIdentity()->get('customer_zone_id') ?? PlatformConfig::getValue('STORE_ZONE');
            }
        }

        if (\Yii::$app->storage->has('skipTaxRates')) {
            if (defined('PRICE_WITH_BACK_TAX') && PRICE_WITH_BACK_TAX == 'True') {
                $skip =  0;
                $_pos = -1;
            } else {
                $skip =  \Yii::$app->storage->get('skipTaxRates');
            }
            $q = \common\models\TaxRates::find()->alias('tr')
                ->leftJoin(['za' => \common\models\ZonesToTaxZones::tableName()], 'tr.tax_zone_id = za.geo_zone_id')
                ->leftJoin(['tz' => \common\models\TaxZones::tableName()], 'tz.geo_zone_id = tr.tax_zone_id')
                ->andWhere(['tr.tax_class_id' => (int) $class_id ])
                ->andWhere(['not in', 'tr.tax_rates_id', $skip])
                ->andWhere([
                  'or',
                  0,
                  //['is', 'za.zone_country_id', null], field is not null def 0
                  ['za.zone_country_id' => [0, (int) $country_id] ]
                ])
                ->andWhere([
                  'or',
                  ['is', 'za.zone_id', null],
                  ['za.zone_id' => [0, (int) $zone_id] ]
                ])
                ->addGroupBy('tr.tax_priority')
                ->select(['tax_rate' => (new \yii\db\Expression('sum(tax_rate)'))])
                ;
//echo "<BR>\n " . $q->createCommand()->rawSql; 
            $taxes = $q->all();
            if (!empty($taxes) && is_array($taxes)) {
                $tax_multiplier = 1.0;
                foreach ($taxes as $tax) {
                    $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
                }
                return $_pos*($tax_multiplier - 1.0) * 100;
            } else {
                return 0;
            }
        }

        if (isset($tax_rates_array[$class_id][$country_id][$zone_id])) {
            return $tax_rates_array[$class_id][$country_id][$zone_id];
        }

        $tax_query = tep_db_query("select sum(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_TAX_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) left join " . TABLE_TAX_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) where (/*za.zone_country_id is null or */ za.zone_country_id = '0' or za.zone_country_id = '" . (int) $country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int) $zone_id . "') and tr.tax_class_id = '" . (int) $class_id . "' group by tr.tax_priority");

        if (tep_db_num_rows($tax_query)) {
            $tax_multiplier = 1.0;
            while ($tax = tep_db_fetch_array($tax_query)) {
                $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
            }

            $ret = $_pos*($tax_multiplier - 1.0) * 100;
            $tax_rates_array[$class_id][$country_id] = array($zone_id => $ret);//echo '<pre>';print_r($tax_rates_array);die;
            return $ret;
        } else {
            $tax_rates_array[$class_id][$country_id] = array($zone_id => 0);
            return 0;
        }
    }

    public static function getTaxZones($country_id, $zone_id) {
        $key = (int)$country_id.'^'.(int)$zone_id;
        static $fetched = [];
        if ( isset($fetched[$key]) ) return $fetched[$key];
        
        $geo_zones_array = [];
        $geo_zones_query = tep_db_query("select geo_zone_id from " . TABLE_ZONES_TO_TAX_ZONES . " za where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int) $zone_id . "')");
        while ($geo_zones = tep_db_fetch_array($geo_zones_query)) {
            $geo_zones_array[] = $geo_zones['geo_zone_id'];
        }

        $fetched[$key] = $geo_zones_array;
        return $geo_zones_array;
    }

    /**
     * country is taxable either if it's in taxable geo zone or it's country of platform or in the same tax zone.
     * @param int $tax_country_id
     * @param int $platform_id
     * @return int 0 - not taxable, 1 - taxable (same country, same tax zone), 2 - probably taxable (in taxable geo zone)
     */
    public static function isTaxableCountry($tax_country_id, $platform_id = null, $tax_class_id=null) {

        $ret = 0;

        $paltform_config = \Yii::$app->get('platform')->getConfig($platform_id);
        $platform_address = $paltform_config->getPlatformAddress();
        $ret = ((int) $platform_address['country_id'] == $tax_country_id)?1:0;

        if ($ret == 0 && self::countriesInSameTaxZone((int) $platform_address['country_id'], $tax_country_id, $tax_class_id)) {
            $ret = 1;
        } else {
            $c = Country::getPlatformCountries($platform_id);
            if (is_array($c) && !empty($c)) {
                $cIds = \yii\helpers\ArrayHelper::getColumn($c, 'id');
                $ret = in_array($tax_country_id, $cIds)?2:0;
            }
        }
        return $ret;
    }

    public static function countriesInSameTaxZone($cid1, $cid2, $tax_class_id=null) {
        $sQueryTaxClassCond = '1';
        if (!empty((int)$tax_class_id)) {
            $sQueryTaxClassCond = ['tr.tax_class_id' => (int)$tax_class_id];
        }
        return \common\models\ZonesToTaxZones::find()->alias('z2tz')
            ->andWhere(['zone_country_id' => [0, (int)$cid1, (int)$cid2]])
            ->addSelect('geo_zone_id')
            ->addSelect(['zone_country_id' => new \yii\db\Expression('min(zone_country_id)')])
            ->andWhere(['exists', \common\models\TaxRates::find()->alias('tr')->andWhere('tr.tax_zone_id=z2tz.geo_zone_id')->andWhere($sQueryTaxClassCond)])
            ->groupBy('geo_zone_id')
            ->having('zone_country_id=0 or count(distinct zone_country_id)=2')
            ->exists();
            ;
    }

    public static function getTaxValues($platform_id, $tax_class_id, $tax_country_id, $tax_zone_id) {
        $tax_values = false;
        foreach (\common\helpers\Hooks::getList('tax/get-tax-values') as $filename) {
            $tax_values = include($filename);
            if (is_array($tax_values) && isset($tax_values['tax'])) return $tax_values;
        }
        $tax = 0;
        $tax_description = '';

        $paltform_config = \Yii::$app->get('platform')->getConfig($platform_id);
        $platform_address = $paltform_config->getPlatformAddress();
        if ($platform_address) {
            //check taxable simple style - platform and tax address are in the same tax zone
            $check_tax_zones = \common\helpers\Tax::getTaxZones($tax_country_id, $tax_zone_id);
            $check_platform_zone = tep_db_fetch_array(tep_db_query("select za.geo_zone_id from " . TABLE_TAX_RATES . " tr, " . TABLE_ZONES_TO_TAX_ZONES . " za where tr.tax_zone_id = za.geo_zone_id and (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $platform_address['country_id'] . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int) $platform_address['zone_id'] . "') and tr.tax_class_id = '" . (int) $tax_class_id . "'"));

            if (isset($check_platform_zone['geo_zone_id']) && is_array($check_tax_zones) && in_array($check_platform_zone['geo_zone_id'], $check_tax_zones)) {
                if (defined('TAX_BY_PLATFORM_ADDRESS') && TAX_BY_PLATFORM_ADDRESS == 'True') { // probably not needed any more but can be defined in configure.php for compatibility
                    // be aware that it may be a reason of different taxes for product and totals for multiple rates
                    $tax = \common\helpers\Tax::get_tax_rate($tax_class_id, $platform_address['country_id'], $platform_address['zone_id']);
                    $tax_description = \common\helpers\Tax::get_tax_description($tax_class_id, $platform_address['country_id'], $platform_address['zone_id']);
                } else {
                    $tax = \common\helpers\Tax::get_tax_rate($tax_class_id, $tax_country_id, $tax_zone_id);
                    $tax_description = \common\helpers\Tax::get_tax_description($tax_class_id, $tax_country_id, $tax_zone_id);
                }
            }
            //generally export (tax free), but .... EU pays 3rd party taxes :(
            elseif (defined('ALLOW_SEVERAL_TAX_COUNTRIES') && ALLOW_SEVERAL_TAX_COUNTRIES=='True' && 
                    (int)$tax_country_id>0 && self::isTaxableCountry($tax_country_id, $platform_id)) { // tax for other countries (conditional)
                $tax = \common\helpers\Tax::get_tax_rate($tax_class_id, $tax_country_id, $tax_zone_id);
                $tax_description = \common\helpers\Tax::get_tax_description($tax_class_id, $tax_country_id, $tax_zone_id);
            }
            
        } else {
            $tax = \common\helpers\Tax::get_tax_rate($tax_class_id, $tax_country_id, $tax_zone_id);
            $tax_description = \common\helpers\Tax::get_tax_description($tax_class_id, $tax_country_id, $tax_zone_id);
        }

        return [
            'tax_class_id' => $tax_class_id,
            'tax' => $tax,
            'tax_description' => $tax_description
        ];
    }

    /*public static function et_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
        global $customer_zone_id, $customer_country_id;

        if (($country_id == -1) && ($zone_id == -1)) {
            if (!tep_session_is_registered('customer_id')) {
                $country_id = STORE_COUNTRY;
                $zone_id = STORE_ZONE;
            } else {
                $country_id = $customer_country_id;
                $zone_id = $customer_zone_id;
            }
        }

        $tax_query = tep_db_query("select SUM(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_TAX_ZONES . " za ON tr.tax_zone_id = za.geo_zone_id left join " . TABLE_TAX_ZONES . " tz ON tz.geo_zone_id = tr.tax_zone_id WHERE (za.zone_country_id IS NULL OR za.zone_country_id = '0' OR za.zone_country_id = '" . (int) $country_id . "') AND (za.zone_id IS NULL OR za.zone_id = '0' OR za.zone_id = '" . (int) $zone_id . "') AND tr.tax_class_id = '" . (int) $class_id . "' GROUP BY tr.tax_priority");
        if (tep_db_num_rows($tax_query)) {
            $tax_multiplier = 0;
            while ($tax = tep_db_fetch_array($tax_query)) {
                $tax_multiplier += $tax['tax_rate'];
            }
            return $tax_multiplier;
        } else {
            return 0;
        }
    }*/

/**
 * @deprecated should be removed at all - sum all rates disregards tax zone Temporary - could be added tax priority to rates which are assigned to default country zone.
 * @param int $class_id
 * @return int
 */
    
    public static function get_tax_rate_value($class_id) {
        $tax_query = tep_db_query("select SUM(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " where tax_class_id = '" . (int) $class_id . "' group by tax_priority order by tax_priority desc");
        if (tep_db_num_rows($tax_query)) {
            $tax_multiplier = 0;
            while ($tax = tep_db_fetch_array($tax_query)) {
                $tax_multiplier += $tax['tax_rate'];
                if (defined('ONLY_TOP_PRIORITY_TAX') && ONLY_TOP_PRIORITY_TAX=='True') {
                  break;
                }
            }
            return $tax_multiplier;
        } else {
            return 0;
        }
    }

    public static function get_tax_rate_value_edit_order($class_id, $tax_zone_id) {
        $tax_query = tep_db_query("select SUM(tax_rate) as tax_rate from " . TABLE_TAX_RATES . " where tax_class_id = '" . (int) $class_id . "' and tax_zone_id = '" . $tax_zone_id . "' group by tax_priority");
        if (tep_db_num_rows($tax_query)) {
            $tax_multiplier = 0;
            while ($tax = tep_db_fetch_array($tax_query)) {
                $tax_multiplier += $tax['tax_rate'];
            }
            return $tax_multiplier;
        } else {
            return 0;
        }
    }

/**
 * Tax descriptions should be unique ....
 * @param string  $tax_desc
 * @return 0-100
 */
    public static function get_tax_rate_from_desc($tax_desc) {
        $tax_query = tep_db_query("select tax_rate from " . TABLE_TAX_RATES . " where tax_description = '" . tep_db_input($tax_desc) . "'");
        $tax = tep_db_fetch_array($tax_query);
        return $tax['tax_rate'];
    }

/**
 * Tax descriptions should be unique ....
 * @param string  $tax_desc
 * @return array|null
 */
    public static function get_rate_info_from_desc($tax_desc) {
        $tax_query = tep_db_query("select * from " . TABLE_TAX_RATES . " where tax_description = '" . tep_db_input($tax_desc) . "'");
        $tax = tep_db_fetch_array($tax_query);
        return $tax;
    }

    public static function get_tax_class_title($tax_class_id) {
        if ($tax_class_id == '0') {
            return TEXT_NONE;
        } else {
            $classes_query = tep_db_query("select tax_class_title from " . TABLE_TAX_CLASS . " where tax_class_id = '" . (int) $tax_class_id . "'");
            $classes = tep_db_fetch_array($classes_query);

            return $classes['tax_class_title'] ?? null;
        }
    }

    public static function get_zone_id($class_id, $country_id, $zone_id){
        $tax = tep_db_fetch_array(tep_db_query("select geo_zone_id from " . TABLE_TAX_RATES . " tr left join " . TABLE_ZONES_TO_TAX_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int) $country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int) $zone_id . "') and tr.tax_class_id = '" . (int) $class_id . "' group by tr.tax_priority"));
        if ($tax) {
            return $tax['geo_zone_id'];
        } else {
            return false;
        }
    }

    public static function getTaxClassesVariants($withNone = false)
    {
        $taxClasses = [];
        if ($withNone) {
            $taxClasses[] = [
                'id' => 0,
                'text' => '',
            ];
        }
        $classes_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
        if ( tep_db_num_rows($classes_query)>0 ) {
            while ($classes = tep_db_fetch_array($classes_query)) {
                $taxClasses[] = [
                    'id' => $classes['tax_class_id'],
                    'text' => $classes['tax_class_title'],
                ];
            }
        }
        return $taxClasses;
    }

    public static function tax_classes_pull_down($parameters, $selected = '') {
        $select_string = '<select ' . $parameters . '>';
        foreach( self::getTaxClassesVariants() as $variant){
            $select_string .= '<option value="' . $variant['id'] . '"';
            if ($selected == $variant['id'])
                $select_string .= ' SELECTED';
            $select_string .= '>' . $variant['text'] . '</option>';
        }
        $select_string .= '</select>';

        return $select_string;
    }

    public static function isTaxRatesInBackend()
    {
        return !defined('BACKEND_TAX_RATES_OR_CLASSES') || BACKEND_TAX_RATES_OR_CLASSES == 'RATES';
    }

    public static function normalizeTaxSelected($taxSelected)
    {
        if (\common\helpers\System::isBackend() && !self::isTaxRatesInBackend()) {
            $taxArray = array_pad(explode('_', $taxSelected), 2, 0);
            $taxArray[1] = 0;
            return implode('_', $taxArray);
        }
        return $taxSelected;
    }

    public static function get_complex_classes_list(){
      if (self::isTaxRatesInBackend()) {
        $tax_class_array = [];
        $tax_class_query = tep_db_query("select tr.tax_class_id, tr.tax_zone_id, sum(tr.tax_rate) as rate, tr.tax_description, tc.tax_class_title from " . TABLE_TAX_RATES . " tr inner join " . TABLE_TAX_CLASS . " tc on tc.tax_class_id = tr.tax_class_id where 1 group by tax_class_id, tax_zone_id order by tax_description");
        while ($tax_class = tep_db_fetch_array($tax_class_query)) {
            $query = tep_db_query("select * from " . TABLE_TAX_RATES . " tr left join " . TABLE_TAX_CLASS . " tc on tc.tax_class_id = tr.tax_class_id where tr.tax_class_id = '" . $tax_class['tax_class_id'] . "' and tr.tax_zone_id = '" . $tax_class['tax_zone_id'] . "'");
            if (tep_db_num_rows($query) > 1){
                $str = '';
                while ($data = tep_db_fetch_array($query)){
                  if ($str == ''){
                        $str .= $data['tax_class_title'];
                  }else{
                        $str .= " + " . $data['tax_class_title'];
                  }
                }
                $tax_class['tax_class_title'] = $str;
            }
            $tax_class_array[$tax_class['tax_class_id'] . '_' . $tax_class['tax_zone_id']] = //[
                $tax_class['tax_class_title'];
                        //'text' => $tax_class['tax_class_title'],
                        //'rate' => $tax_class['rate']
                //];

        }
        return $tax_class_array;
      } else {
          $classes = \common\models\TaxClass::find()->select('tax_class_title, tax_class_id')->asArray();
          $res = [];
          foreach($classes->each() as $rec) {
              $res[$rec['tax_class_id'] . '_0'] = $rec['tax_class_title'];
          }
          return $res;
      }
    }

    public static function getOrderTaxRates($classId = null, $country_id = -1, $zone_id = -1, $extraAddress = '', $check_group = true, $group_id = null){
      if (self::isTaxRatesInBackend()) {
        $where = $classId ? "tr.tax_class_id = $classId" : 1;
        $rates_query = tep_db_query("select tr.tax_class_id, tr.tax_zone_id, tr.tax_rate from " . TABLE_TAX_RATES . " tr inner join " . TABLE_TAX_CLASS . " tc on tc.tax_class_id = tr.tax_class_id where " . $where . " group by tr.tax_class_id, tr.tax_zone_id");
        $rates = [];
        if (tep_db_num_rows($rates_query)) {
            while ($row = tep_db_fetch_array($rates_query)) {
                $rates[$row['tax_class_id'] . '_' . $row['tax_zone_id']] = $row['tax_rate'];
            }
        }
        return $rates;
      } else {
          $classes = \common\models\TaxClass::find()->select('tax_class_id')->filterWhere(['tax_class_id' => $classId])->asArray();
          $res = [];
          foreach($classes->each() as $rec) {
              $res[$rec['tax_class_id'] . '_0'] = self::get_tax_rate($rec['tax_class_id'], $country_id, $zone_id, $extraAddress, $check_group, $group_id);
          }
          return $res;
      }
    }

    public static function roundPriceExc($price) {
        $ret = $price;
        if (defined('PRODUCTS_PRICE_EXC_ROUND') && PRODUCTS_PRICE_EXC_ROUND == 'True') {
            try {
                $currency = \Yii::$app->settings->get('currency');
                $currencies = \Yii::$container->get('currencies');
                if ($currency && !empty($currencies->currencies[$currency])) {
                    $ret = round($price, $currencies->currencies[$currency]['decimal_places']);
                }
            } catch (\Exception $e) {
                \Yii::warning($e->getMessage() . ' ' . $e->getTraceAsString());
            }
        }
        return (float)$ret;
    }

    public static function roundTax($price) {
        $ret = $price;
        if (defined('TAX_ROUND_PER_ROW') && TAX_ROUND_PER_ROW == 'True') {
            try {
                $currency = \Yii::$app->settings->get('currency');
                $currencies = \Yii::$container->get('currencies');
                if ($currency && !empty($currencies->currencies[$currency])) {
                    $ret = round($price, $currencies->currencies[$currency]['decimal_places']);
                }
            } catch (\Exception $e) {
                \Yii::warning($e->getMessage() . ' ' . $e->getTraceAsString());
            }
        }
        return $ret;
    }

    public static function displayTaxable() {

        if (\Yii::$app->storage->has('taxable')) {
            $ret = \Yii::$app->storage->get('taxable');
        } else {
            $def_country = PlatformConfig::getValue('STORE_COUNTRY');
            if (\Yii::$app->user->isGuest){
                $country_id = \Yii::$app->storage->has('customer_country_id')? \Yii::$app->storage->get('customer_country_id') : $def_country;
            } else {
                $country_id = \Yii::$app->user->getIdentity()->get('customer_country_id') ?? $def_country;
            }
            $tCountry = self::isTaxableCountry($country_id);
            $ret = ($tCountry != 0 && PlatformConfig::getValue('DISPLAY_PRICE_WITH_TAX')=='true');
        }
        return $ret;
    }

    public static function getTaxAddressOption() {
        if (!defined('TAX_ADDRESS_OPTION') ) {
            $option = 2;//any
        } else {
            $option = (int)TAX_ADDRESS_OPTION;
        }
        if ($option<0 || $option>2) {
            $option = 2;
        }
        return $option;
    }
    
    public static function getTaxClassesWithRates($countryId = -1, $zoneId = -1, $extraAddress='', $checkGroup = true, $withNone = true)
    {
        $ret = [];
        if ($withNone) {
            $ret[0] = [
                'id' => 0,
                'text' => defined('TEXT_NONE')?TEXT_NONE:'',
                'rate' => 0.00,
            ];
        }
        $taxClasses = \common\models\TaxClass::find()->select([
                                            'id' => 'tax_class_id',
                                            'text' => 'tax_class_title',
                                          ])
            ->orderBy('tax_class_title')
            ->asArray()
            ->cache(self::CACHE_TIME)
            ->all();
        foreach ($taxClasses as $taxClass) {
            $ret[$taxClass['id']] = $taxClass;
            $ret[$taxClass['id']]['rate'] = self::get_tax_rate($taxClass['id'], $countryId, $zoneId, $extraAddress, $checkGroup);
        }
        return $ret;
    }

    public static function getRateDetails($tax_rate, $byField = 'tax_description') {
        $q = \common\models\TaxRates::find();
        if ($byField == 'id') {
            $q->andWhere(['tax_rates_id' => $tax_rate]);
        } else {
            $q->andWhere(['tax_description' => $tax_rate]);
        }
        $q->cache(5);
        $data = $q->asArray()->one();
        return $data;
    }
    
    public static function getCompanyVATId($tax_rate, $byField = 'tax_description', $platform_id = 0) {
        $data = self::getRateDetails($tax_rate, $byField);
        if (!empty($data['company_number'])) {
            $ret = $data['company_number'];
        } else {
            //from platform details
            if ($platform_id == 0) {
                $platform_id = intval(\common\classes\platform::currentId());
            }

            $defaultAddress = \Yii::$app->get('platform')->getConfig($platform_id)->getPlatformAddress();
            $ret = $defaultAddress['entry_company_vat']??'';
        }
        return $ret;
    }

    public static function getCompanyName($tax_rate, $byField = 'tax_description', $platform_id = 0) {
        $data = self::getRateDetails($tax_rate, $byField);
        if (!empty($data['company_name'])) {
            $ret = $data['company_name'];
        } else {
            //from platform details
            if ($platform_id == 0) {
                $platform_id = intval(\common\classes\platform::currentId());
            }

            $defaultAddress = \Yii::$app->get('platform')->getConfig($platform_id)->getPlatformAddress();

            $ret = $defaultAddress['company']??'';
        }
        return $ret;
    }

    public static function getCompanyAddress($tax_rate, $byField = 'tax_description', $platform_id = 0) {
        $data = self::getRateDetails($tax_rate, $byField);
        if (!empty($data['company_address'])) {
            $ret = $data['company_address'];
        } else {
            //from platform details
            if ($platform_id == 0) {
                $platform_id = intval(\common\classes\platform::currentId());
            }

            $data = \Yii::$app->get('platform')->getConfig($platform_id)->getPlatformAddress();
            $ret = \common\helpers\Address::address_format(\common\helpers\Address::get_address_format_id($data['country_id']), $data, 1, '', "\n");
            
        }
        return $ret;
    }

    /**
     * products_tax_class_id may be overwritten by group (UserGroupsTax extension)
     * @param int|mixed|\common\models\Products $product
     * @param int|mixed $products_tax_class_id cached products_tax_class_id
     * @param $group_id
     * @return int|mixed|null
     * @throws \Exception
     */
    public static function getProductTaxClass($product, $products_tax_class_id = null, $group_id = null)
    {
        if (is_null($products_tax_class_id)) {
            $product = \common\models\Products::findByVarCheck($product);
            $products_tax_class_id = $product->$products_tax_class_id ?? 0;
        }
        if (is_null($group_id)) {
            $group_id = (int) \Yii::$app->storage->get('customer_groups_id');
        }
        foreach (\common\helpers\Hooks::getList('tax/get-product-tax-class-id') as $filename) {
            $overwritten_class_id = include($filename);
            if ($overwritten_class_id !== false) return $overwritten_class_id;
        }
        return (int) $products_tax_class_id;
    }

    public static function getProductTaxRate($product, $products_tax_class_id = null, $group_id = null)
    {
        $taxRate = 0;
        if (is_null($group_id)) {
            $group_id = (int) \Yii::$app->storage->get('customer_groups_id');
        }

        if (\common\helpers\Group::isTaxApplicable($group_id)) {
            $products_tax_class_id = self::getProductTaxClass($product, $products_tax_class_id, $group_id);
            $taxRate = self::get_tax_rate($products_tax_class_id, -1, -1, '', true, $group_id);
        }
        return (float)$taxRate;
    }

}
