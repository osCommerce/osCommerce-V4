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

class Output {

    public static function parse_input_field_data($data, $parse) {
        return strtr(trim($data), $parse);
    }

    public static function output_string($string, $translate = false, $protected = false) {
        if (!is_scalar($string)) return $string;
        //$string = \yii\helpers\Html::encode($string);
        //$string = \yii\helpers\HtmlPurifier::process($string);
        //$string = self::xss_clean(stripslashes(strip_tags($string)));
        if ($protected == true) {
            return htmlspecialchars($string);
        } else {
            if ($translate == false) {
                return self::parse_input_field_data($string, array('"' => '&quot;'));
            } else {
                return self::parse_input_field_data($string, $translate);
            }
        }
    }

    public static function output_string_protected($string) {
        return self::output_string($string, false, true);
    }
    
    public static function break_string($string, $len, $break_char = '-') {
        $l = 0;
        $output = '';
        for ($i = 0, $n = strlen($string); $i < $n; $i++) {
            $char = substr($string, $i, 1);
            if ($char != ' ') {
                $l++;
            } else {
                $l = 0;
            }
            if ($l > $len) {
                $l = 1;
                $output .= $break_char;
            }
            $output .= $char;
        }

        return $output;
    }

    public static function xss_clean($data)
    {
    // Fix &entity\n;
    $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
    $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

    // Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

    // Remove javascript: and vbscript: protocols
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

    // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

    do
    {
        // Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    }
    while ($old_data !== $data);

    return $data;
    }

    public static function get_all_get_params($exclude_array = '', $as_fields = false) {
        if (!is_array($exclude_array))
            $exclude_array = array();

        $get_url = '';
        if (is_array($_GET) && (sizeof($_GET) > 0)) {
            foreach ($_GET as $key => $value) {
                if ( strpos($key,'<')!==false || strpos($key,'>')!==false ) continue;
                if (((!is_array($value) && strlen($value) > 0) || (is_array($value) && sizeof($value) > 0)) && ($key != session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y')) {
                    if (is_array($value)) {
                        for ($i = 0, $n = sizeof($value); $i < $n; $i++) {
                          if (preg_match('/javascript:/', $value[$i])) {// XSS prevention
                            $value[$i] = '';
                          }
                          $value[$i] = \yii\helpers\Html::encode($value[$i]);
                          if (strlen($value[$i]) > 0) {
                            if ($as_fields) {
                                $get_url .= tep_draw_hidden_field($key . '[]', tep_db_input(self::xss_clean(stripslashes(strip_tags($value[$i])))) );
                            } else {
                                $get_url .= $key . rawurlencode('[]') . '=' . rawurlencode(self::xss_clean(stripslashes(strip_tags($value[$i])))) . '&';
                            }
                          }
                        }
                    } else {
                        $value = \yii\helpers\Html::encode($value);
                        if (preg_match('/javascript:/', $value)) {// XSS prevention
                            $value = '';
                        }
                        if ($as_fields) {
                            $get_url .= tep_draw_hidden_field($key, tep_db_input(self::xss_clean(stripslashes(strip_tags($value)))));
                        } else {
                            $get_url .= $key . '=' . rawurlencode(self::xss_clean(stripslashes(strip_tags($value)))) . '&';
                        }
                    }
                }
            }
        }
        return $get_url;
    }

    public static function parse_search_string($search_str, &$objects, $msearch_enable = MSEARCH_ENABLE) {
        $search_str = trim(strtolower($search_str));
        $search_str = preg_replace(array('/(\S)(\()/', '/(\))(\S)/', '/\(\s+/', '/\s+\)/'), array('$1 $2', '$1 $2', '(', ')'), $search_str);
        $pieces = preg_split('/[\s]+/', $search_str,-1,PREG_SPLIT_NO_EMPTY);

        $objects = array();
        $tmpstring = '';
        $flag = '';
        for ($k = 0; $k < count($pieces); $k++) {
            while (substr($pieces[$k], 0, 1) == '(') {
                $objects[] = '(';
                if (strlen($pieces[$k]) > 1) {
                    $pieces[$k] = substr($pieces[$k], 1);
                } else {
                    $pieces[$k] = '';
                }
            }
            $post_objects = array();
            while (substr($pieces[$k], -1) == ')') {
                $post_objects[] = ')';
                if (strlen($pieces[$k]) > 1) {
                    $pieces[$k] = substr($pieces[$k], 0, -1);
                } else {
                    $pieces[$k] = '';
                }
            }
            if ((substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"')) {
                $objects[] = trim($pieces[$k]);
                for ($j = 0; $j < count($post_objects); $j++) {
                    $objects[] = $post_objects[$j];
                }
            } else {
                $tmpstring = trim(str_replace('"', ' ', $pieces[$k]));
                if (substr($pieces[$k], -1) == '"') {
                    $flag = 'off';
                    $objects[] = trim($pieces[$k]);
                    for ($j = 0; $j < count($post_objects); $j++) {
                        $objects[] = $post_objects[$j];
                    }
                    unset($tmpstring);
                    continue;
                }
                $flag = 'on';
                $k++;
                while (($flag == 'on') && ($k < count($pieces))) {
                    while (substr($pieces[$k], -1) == ')') {
                        $post_objects[] = ')';
                        if (strlen($pieces[$k]) > 1) {
                            $pieces[$k] = substr($pieces[$k], 0, -1);
                        } else {
                            $pieces[$k] = '';
                        }
                    }
                    if (substr($pieces[$k], -1) != '"') {
                        $tmpstring .= ' ' . $pieces[$k];
                        $k++;
                        continue;
                    } else {
                        $tmpstring .= ' ' . trim(str_replace('"', ' ', $pieces[$k]));
                        $objects[] = trim($tmpstring);
                        for ($j = 0; $j < count($post_objects); $j++) {
                            $objects[] = $post_objects[$j];
                        }
                        unset($tmpstring);
                        $flag = 'off';
                    }
                }
            }
        }
        if ($msearch_enable == 'true') {
            $pares = array();
            $_tmpMinLenght=((defined('BACKEND_MSEARCH_WORD_LENGTH') && \frontend\design\Info::isTotallyAdmin()) ? (int)BACKEND_MSEARCH_WORD_LENGTH : (int)MSEARCH_WORD_LENGTH);
            for ($i = 0; $i < sizeof($objects); $i++) {
                $objects[$i] = str_replace(array(",", ";", ".", "&", "!", ":", "\""), array("", "", "", "", "", "", ""), $objects[$i]);
                if (($objects[$i] == 'and') || ($objects[$i] == 'or') || ($objects[$i] == '(') || ($objects[$i] == ')')) {
                    $pares[] = $objects[$i];
                } else {
                    $pieces = preg_split('/[\s]+/', $objects[$i]);
                    foreach ($pieces as $piece) {
                        if (strlen($piece) >= $_tmpMinLenght) {
                            $ks_hash = tep_db_fetch_array(tep_db_query("select soundex('" . addslashes($piece) . "') as sx"));
                            $pares[] = $ks_hash["sx"];
                        } else {
                            $pares[] = '';
                        }
                    }
                }
            }
            $objects = $pares;
        }
        $temp = array();
        for ($i = 0; $i < (count($objects) - 1); $i++) {
            $temp[] = $objects[$i];
            if (($objects[$i] != 'and') &&
                    ($objects[$i] != 'or') &&
                    ($objects[$i] != '(') &&
                    ($objects[$i + 1] != 'and') &&
                    ($objects[$i + 1] != 'or') &&
                    ($objects[$i + 1] != ')')) {
                $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
            }
        }
        $temp[] = $objects[$i];
        $objects = $temp;
        $keyword_count = 0;
        $operator_count = 0;
        $balance = 0;
        for ($i = 0; $i < count($objects); $i++) {
            if ($objects[$i] == '(')
                $balance --;
            if ($objects[$i] == ')')
                $balance ++;
            if (($objects[$i] == 'and') || ($objects[$i] == 'or')) {
                $operator_count ++;
            } elseif (($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')')) {
                $keyword_count ++;
            }
        }
        if (($operator_count < $keyword_count) && ($balance == 0)) {
            return true;
        } else {
            return false;
        }
    }

    public static function array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {
        if (!is_array($exclude))
            $exclude = array();

        $get_string = '';
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $key => $value) {
                if ((!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y')) {
                    if (is_array($value)) {
                        $value = self::array_to_string($value, $exclude, $equals, $separator);
                    }
                    $get_string .= $key . $equals . $value . $separator;
                }
            }
            $remove_chars = strlen($separator);
            $get_string = substr($get_string, 0, -$remove_chars);
        }

        return $get_string;
    }

/**
 *
 * @param string $text
 * @param array $search_terms
 * @return string
 */
    public static function highlight_text($text, $search_terms) {
      if (is_array($search_terms)) {
        $re = [];
        foreach ($search_terms as $v) {
          if (is_scalar($v)) {
            $re[] = preg_quote($v, '/');
          }
        }
        if (!empty($re)) {
          $text = preg_replace('/(' . join('|', $re) . ')/iu',
              '<span ' . (defined('MSEARCH_HIGHLIGHT_BGCOLOR')?'style="background:' . MSEARCH_HIGHLIGHT_BGCOLOR . '"':'') . ' class="typed">\1</span>',
              $text);
        }
      }
      return $text;
    }

    public static function unhtmlentities($string) {
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        return strtr($string, $trans_tbl);
    }

    public static function get_clickable_link($tep_href_link, $text = '') {
        if (EMAIL_USE_HTML == 'true') {
            if (!$text) {
                if (defined("TEXT_VIEW")) {
                    $text = TEXT_VIEW;
                } elseif (defined("IMAGE_VIEW")) {
                    $text = IMAGE_VIEW;
                } else {
                    $text = $tep_href_link;
                }
            }
            if (\common\helpers\Validations::validate_email($tep_href_link)) {
                return '<a href="mailto:' . $tep_href_link . '">' . $text . '</a>';
            }
            return '<a href="' . $tep_href_link . '">' . $text . '</a>';
        }
        return $tep_href_link;
    }

    public static function recursive_array_intersect_key(array $array1, array $array2) {
      $array1 = array_intersect_key($array1, $array2);
      foreach ($array1 as $key => &$value) {
          if (is_array($value) && is_array($array2[$key])) {
              $value = recursive_array_intersect_key($value, $array2[$key]);
          }
      }
      return $array1;
    }
    
    public static function truncate($string, $length, $trail="..."){
        $out = '';
        if (mb_strlen($string) > $length){
            $words = explode(" ", $string);
            if ($words){
                foreach ($words as $word){
                    if (mb_strlen($out . $word) <= $length){
                        $out .= $word . " ";
                    } else {
                        $out = trim($out) . $trail;
                        break;
                    }
                }
            }
            
        } else {
            $out = $string;
        }
        return $out;
    }

    public static function percent($number,$sign='%')
    {
        return rtrim(rtrim(number_format($number,2,'.',''),'0'),'.').$sign;
    }

    public static function strip_tags($text, $allowable_tags = null)
    {
        $text = preg_replace('/>(\w)/', '> $1', $text);
        if ( stripos($text,'<style')!==false ) {
            $text = preg_replace('/<style[^>]*>.*?<\/style>/ims', '', $text);
        }
        if ( stripos($text,'<script')!==false ) {
            $text = preg_replace('/<script[^>]*>.*?<\/script>/ims', '', $text);
        }
        $text = \strip_tags($text, $allowable_tags);
        $text = trim($text);
        return $text;
    }
    
    public static function mb_basename($path) {
        if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        } else if (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        }
        return '';
    }

}
