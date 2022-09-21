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

use yii\db\ActiveQuery;

class Currencies {

    public static function batchRateUpdate( ActiveQuery $currenciesQuery )
    {
        \common\helpers\Translation::init('admin/currencies');

        $baseCurrency = static::systemCurrencyCode();

        $messages = [];
        foreach ( $currenciesQuery->all() as $currency ) {
            $server_used = 'oanda';
            if ( $currency->code == $baseCurrency ) {
                $rate = 1;
            }else {
                $rate = static::quote_oanda_currency($currency->code, $baseCurrency);
            }
            /*$server_used = 'xe';
            $rate = CurrenciesHelper::quote_xe_currency($currency->code);
            if (empty($rate)) {
                $messages[] = array('message' => sprintf(WARNING_PRIMARY_SERVER_FAILED, $server_used, $currency->title, $currency->code), 'messageType' => 'alert-warning');
                $rate = CurrenciesHelper::quote_google_currency($currency->code);
                $server_used = 'google';
            }*/
            if (tep_not_null($rate)) {
                $currency->value = $rate;
                $currency->last_updated = new \yii\db\Expression('NOW()');
                $currency->save(false);
                $currency->refresh();
                $messages[] = array('message' => sprintf(TEXT_INFO_CURRENCY_UPDATED, $currency->title, $baseCurrency.' -> '.$currency->code.' = '.$currency->value, $server_used), 'messageType' => 'alert-success');
            } else {
                $messages[] = array('message' => sprintf(ERROR_CURRENCY_INVALID, $currency->title, $currency->code, $server_used), 'messageType' => 'alert-danger');
            }
        }
        return $messages;
    }

    public static function quote_google_currency($to, $from = DEFAULT_CURRENCY) {
        $url = "https://www.google.com/finance/converter?a=1&from=$from&to=$to";
        $request = curl_init();
        $timeOut = 0;
        curl_setopt ($request, CURLOPT_URL, $url);
        curl_setopt ($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($request, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        curl_setopt ($request, CURLOPT_CONNECTTIMEOUT, $timeOut);
        $response = curl_exec($request);
        curl_close($request);

        if ( $response && preg_match('#\<span class=bld\>(.+?)\<\/span\>#s',$response, $finalData) ) {
            return preg_replace('/[^\d\.]/','',$finalData[0]);
        }
        return false;
    }
    
    public static function quote_oanda_currency($code, $base = DEFAULT_CURRENCY) {
        $context = stream_context_create(array(
            'http'=>array(
                'method'=>"GET",
                'header'=>
                    "User-Agent: Mozilla/5.0 (0) Gecko/20100101 Firefox/51.0\r\n".
                    "Accept-language: en\r\n" .
                    "Accept: text/javascript, text/html, application/xml, text/xml, */*\r\n".
                    "X-Requested-With: XMLHttpRequest\r\n"
            )
        ));
        $page = @file_get_contents(
            'https://www.oanda.com/currency/converter/update?'.
            'base_currency_0='.$code.'&quote_currency='.$base.
            '&end_date='.date('Y-m-d').'&view=details&id=2&action=C&',
            false, $context
        );
        if ( $page ) {
            $page_data = json_decode($page,true);
            if ( isset($page_data['data']) && isset($page_data['data']['bid_ask_data']) && isset($page_data['data']['bid_ask_data']['bid']) ) {
                return $page_data['data']['bid_ask_data']['bid'];
            }
        }
        return false;
    }

    public static function quote_xe_currency($to, $from = DEFAULT_CURRENCY) {
        $page = file('http://www.xe.com/currencyconverter/convert/?Amount=1&From=' . $from . '&To=' . $to);

        $match = array();

        preg_match('/[0-9.]+\s*' . $from . '\s*=\s*([0-9.]+)\s*' . $to . '/', implode('', $page), $match);

        if (sizeof($match) > 0) {
            return $match[1];
        } else {
            return false;
        }
    }

    public static function currency_exists($code) {
        $code = tep_db_prepare_input($code);
        $currency_code = tep_db_query("select currencies_id, code from " . TABLE_CURRENCIES . " where code = '" . tep_db_input($code) . "' and status = 1");
        if ($d = tep_db_fetch_array($currency_code)) {
            return $d['code'];
        } else {
            return false;
        }
    }

    public static function getDefaultCurrencyId()
    {
        $currencies = \Yii::$container->get('currencies');
        return isset($currencies->currencies[DEFAULT_CURRENCY])? $currencies->currencies[DEFAULT_CURRENCY]['id'] : self::getCurrencyId(DEFAULT_CURRENCY);
    }

    public static function getCurrencyId($code) {
        static $_cache = [];
        if ( !isset($_cache[$code]) ) {
            $_cache[$code] = false;
            $currency_code = tep_db_query("select currencies_id from " . TABLE_CURRENCIES . " where code = '" . tep_db_input($code) . "'");
            if ($d = tep_db_fetch_array($currency_code)) {
                $_cache[$code] = $d['currencies_id'];
            }
        }
        return $_cache[$code];
    }

    public static function getCurrencyCode($currencies_id) {
        static $_cache = [];
        if ( !isset($_cache[(int)$currencies_id]) ) {
            $currency_code = tep_db_query("select code from " . TABLE_CURRENCIES . " where currencies_id = '" . tep_db_input($currencies_id) . "'");
            if ($d = tep_db_fetch_array($currency_code)) {
                $_cache[(int)$currencies_id] = $d['code'];
            } else {
                $_cache[(int)$currencies_id] = false;
            }
        }
        return $_cache[(int)$currencies_id];
    }

    public static function systemCurrencyCode()
    {
        static $defaultSystemCurrency = false;
        if ( $defaultSystemCurrency===false ) {
            $_data = tep_db_fetch_array(tep_db_query(
                "SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key='DEFAULT_CURRENCY'"
            ));
            $defaultSystemCurrency = $_data['configuration_value'];
        }
        return $defaultSystemCurrency;
    }

    public static function get_currencies($only_code = 0, $default = '') {
        $currencies_array = array();
        if ($default) {
            $currencies_array[] = array('id' => '', 'text' => $default);
        }
        $currencies_query = tep_db_query("select currencies_id, code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from " . TABLE_CURRENCIES . " where status = 1 order by sort_order, title");
        while ($currency = tep_db_fetch_array($currencies_query)) {
            $currencies_array[] = array(
                'id' => ($only_code < 0 ? $currency['id'] : $currency['code']),
                'text' => ($only_code > 0 ? $currency['code'] : $currency['title'] . ' [' . $currency['code'] . ']'),
                'code' => $currency['code'],
                'currencies_id' => $currency['currencies_id'],
            );
        }
        return $currencies_array;
    }
    
    public static function correctPlatformLanguages(){
        $currencies_query = tep_db_query("select code from " . TABLE_CURRENCIES ." where status = 0");
        if (tep_db_num_rows($currencies_query)){
            $disabled = [];
            while($row = tep_db_fetch_array($currencies_query)) {
                $disabled[] = $row['code'];
            }
            $platforms = tep_db_query("select platform_id, defined_currencies, default_currency from " . TABLE_PLATFORMS);
            if (tep_db_num_rows($platforms)){
                while ($row = tep_db_fetch_array($platforms)){
                    $defined = $row['defined_currencies'];
                    if (!empty($defined)){
                        $curs = explode(",", $defined);
                        $pl_defined = array_diff($curs, $disabled);
                        tep_db_query("update " . TABLE_PLATFORMS . " set defined_currencies = '" . implode(",", $pl_defined) . "' where platform_id = '" . (int)$row['platform_id'] . "'");
                    }
                    $default = $row['default_currency'];
                    if (!empty($default) && in_array($default, $disabled)) {
                        tep_db_query("update " . TABLE_PLATFORMS . " set default_currency = '" . DEFAULT_CURRENCY . "' where platform_id = '" . (int)$row['platform_id'] . "'");
                    }
                }
            }
        }
    }
    
    public static function correctSupplierCurrency($currency){
        $suppliers = \common\helpers\Suppliers::getSuppliers();
        if ($suppliers){
            $currencies = \Yii::$container->get('currencies');
            foreach($suppliers as $supplier){
                $save = false;
                if (!$supplier->currencies_id) {
                    $supplier->currencies_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];
                    $save = true;
                }
                if ($currency->currencies_id == $supplier->currencies_id){
                    $supplier->currencies_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];
                    $save = true;
                }
                if ($save)
                    $supplier->save(false);
            }
        }
    }

    public static function getCurrencyIdByName($name) {
        static $_cache = [];
        if ( !isset($_cache[$name]) ) {
            $_cache[$name] = false;
            $currency_code = tep_db_query("select currencies_id from " . TABLE_CURRENCIES . " where title like '" . tep_db_input($name) . "'");
            if ($d = tep_db_fetch_array($currency_code)) {
                $_cache[$name] = $d['currencies_id'];
            }
        }
        return $_cache[$name];
    }

}
