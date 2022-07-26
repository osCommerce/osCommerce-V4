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

class Validations {

    public static function validate_email($email) {
        $valid_address = true;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $valid_address = false;
        if ($valid_address && ENTRY_EMAIL_ADDRESS_CHECK == 'true') {
            $ex = explode('@', $email);
            $domain = $ex[1];
            if (!checkdnsrr($domain, "MX") && !checkdnsrr($domain, "A")) {
                $valid_address = false;
            }
        }
        return $valid_address;
    }

    public static function sanitizeVatId($vat_number) {
      return preg_replace('/[^0-9A-Z]+/', '', strtoupper($vat_number));
    }

    public static function verif_tva($vat_number) {
      $vat_number = self::sanitizeVatId($vat_number);
        //return 'no_verif'; //disable
        static $page_check_results = [];
        if ( empty($vat_number) ) return 'no_verif';

        if (isset($page_check_results[$vat_number])) {
            return $page_check_results[$vat_number];
        }
        $page_check_results[$vat_number] = 'no_verif';

        $countryCode = substr($vat_number, 0, 2);
        $vatNumber = substr($vat_number, 2);

        try {
            /* for test only - if no SSL CA file/ DNS available
             $context = stream_context_create([
                'ssl' => [
                    // set some SSL/TLS specific options
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ]);*/
            $client = new \SoapClient("https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl", [
                'connection_timeout' => 3,
                //'stream_context' => $context,
            ]);
            $params = array('countryCode' => $countryCode, 'vatNumber' => $vatNumber);
            $result = $client->checkVat($params);
            if ($result && $result->valid) {
                $page_check_results[$vat_number] = 'true';
            }
        } catch (\Exception $e) {
            $page_check_results[$vat_number] = 'no_verif_na';
          \Yii::warning(" #### " .print_r($e->getMessage(), true), 'TLDEBUG');
        }

        return $page_check_results[$vat_number];
    }


/**
 * slow if country is not specified :( - huge reg_exp
 * @param type $vatNumber
 * @param type $countries_id
 * @return boolean
 */
    public static function extendedCheckVAT($vatNumber, $countries_id = 0) {
      $vatNumber = self::sanitizeVatId($vatNumber);
        $languages_id = \Yii::$app->settings->get('languages_id');
        if ($countries_id > 0) {
            $countries = tep_db_query("select * from " . TABLE_COUNTRIES . " where countries_id = '" . (int) $countries_id . "' and language_id = '" . (int) $languages_id . "' and vat_code_chars <> '0' and vat_code_chars <> '' and vat_code_prefix !=''");
            $countries_values = tep_db_fetch_array($countries);
            if (is_array($countries_values)) {
                $prefix = $countries_values['vat_code_prefix'];
                switch ($countries_values['vat_code_type']) {
                    case 1://alphanumeric
                        $chars = "[A-Z0-9]";
                        break;
                    case 2://alphabetical
                        $chars = "[A-Z]";
                        break;
                    default://numeric
                        $chars = "[0-9]";
                        break;
                }
                if (strpos($countries_values['vat_code_chars'], '|')) {
                  $v = explode('|', $countries_values['vat_code_chars']);
                  $ccount = '(';
                  if (!empty($v) && is_array($v)) {
                    foreach ($v as $count) {
                      if (!empty(trim($count, ',| '))) {
                        $ccount .= '(' . $chars . '{' . $count . '})|';
                      }
                    }
                    $ccount = trim($ccount, '|');
                  }
                  $ccount .= ')';
                } else {
                  $ccount = $chars . "{" . $countries_values['vat_code_chars'] . "}";
                }
                $regular = "/^" . '(' . $prefix . ')' . $ccount . "$/";
                
                if (preg_match($regular, $vatNumber)) {
                    return true;
                } 
                ///missing country prefix - return it, so validation should not be rejected.
                if (!empty($prefix)) {
                  $regular = "/^" . $ccount . "$/";
                  if (preg_match($regular, $vatNumber)) {
                      return $prefix;
                  }
                }
            }
            return false;
        }
        $regular = "/^(";
        $first = true;
        $countries = tep_db_query("select distinct vat_code_prefix, vat_code_chars, vat_code_type from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "' and vat_code_chars <>'' and vat_code_chars <>'0' and vat_code_prefix !=''");
        while ($countries_values = tep_db_fetch_array($countries)) {
            $prefix = $countries_values['vat_code_prefix'];
            switch ($countries_values['vat_code_type']) {
                case 1://alphanumeric
                    $chars = "[A-Z0-9]";
                    break;
                case 2://alphabetical
                    $chars = "[A-Z]";
                    break;
                default://numeric
                    $chars = "[0-9]";
                    break;
            }
            if (strpos($countries_values['vat_code_chars'], '|')) {
              $v = explode('|', $countries_values['vat_code_chars']);
              $ccount = '(';
              if (!empty($v) && is_array($v)) {
                foreach ($v as $count) {
                  if (!empty(trim($count, ',| '))) {
                    $ccount .= '(' . $chars . '{' . $count . '})|';
                  }
                }
                $ccount = trim($ccount, '|');
              }
              $ccount .= ')';
            } else {
              $ccount = $chars . "{" . $countries_values['vat_code_chars'] . "}";
            }
            $regular .= ($first ? "" : "|") . "((" . $prefix  . ')'. $ccount . ")";
            $first = false;
        }
        $regular .= ")$/";
        if (preg_match($regular, $vatNumber)) {
            return true;
        }
        return false;
    }

/**
 * 
 * @param string $number
 * @return boolean
 */
    public static function checkVAT($number) {
        if (strpos($number, 'DE') === false) {
            return self::checkVAT_local($number);
        } else {
            $http = new \common\classes\httpClient();
            if (!$http->Connect("wddx.bff-online.de", 80)) { //We can’t connect to the server at wddx.bff-online.de.
                return self::checkVAT_local($number);
            }
            $http->addHeader('Host', 'wddx.bff-online.de');
            $http->addHeader('User-Agent', 'osCommerce');
            $http->addHeader('Connection', 'Close');

            $status = $http->Get('/ustid.php?eigene_id=' . 'DE22222222' . '&abfrage_id=' . $number);
            if ($status != 200) {
                return self::checkVAT_local($number);
            } else {
                $str = $http->getBody();
            }
            $http->Disconnect();
            $search = "<var name='fehler_code'><string>";
            $pos = strpos($str, $search);
            $code = 0;
            if ($pos !== false) {
                $code = substr($str, $pos + strlen($search), 3);
            }

            if ($code == '200') {
                return true;
            } else {
                if ($code == '777' || $code == '205' || $code == '208' || $code == '666' || $code == '999') {
                    return self::checkVAT_local($number);
                } else {
                    return false;
                }
            }
        }
    }

    public static function checkVAT_local($number) {
        if (!preg_match("/^(((BE|DE|PT)[0-9]{9})|((DK|FI|LU|MT)[0-9]{8})|(IT[0-9]{11})|(GB[0-9]{9})|(GB[0-9]{12})|(ATU[0-9]{8})|(SE[0-9]{10}01)|(ES[A-Z0-9]{1}[0-9]{7}[A-Z0-9]{1})|(NL[0-9]{9}B[0-9]{2})|(IE[0-9]{1}[A-Z0-9]{1}[0-9]{5}[A-Z]{1})|(EL[0-9]{8,9})|(FR[A-Z0-9]{2}[0-9]{9}))/", $number)) {
            return false;
        } else {
            return true;
        }
    }

}
