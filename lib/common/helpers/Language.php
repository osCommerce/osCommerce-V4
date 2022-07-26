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

use backend\models\Admin;

class Language
{
    public static function get_language_code($id, $in_array=true) {
        static $_cached = [];
        if (!isset($_cached[$id])) {
          $_cached[$id] = tep_db_fetch_array(tep_db_query("select LOWER(code) as code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$id . "'"));
        }
        if ($in_array) {
          $ret = $_cached[$id];
        } else {
          $ret = isset($_cached[$id]['code'])?$_cached[$id]['code']:null;
        }
        return $ret;
    }
    
    public static function get_language_id($code) {
        return \Yii::$app->getCache()->getOrSet('lang_'.$code,function() use ($code){
            return tep_db_fetch_array(tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . tep_db_input($code) . "'"));
        }, 600);
        //return tep_db_fetch_array(tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . tep_db_input($code) . "'"));
    }
    
    public static function get_default_language_id() {
        static $_cached = false;
        if ($_cached === false) {
            $get_id_arr = static::get_language_id(DEFAULT_LANGUAGE);
            $_cached = is_array($get_id_arr) ? $get_id_arr['languages_id'] : \Yii::$app->settings->get('languages_id');
        }
        return $_cached;
    }
    
    public static function systemLanguageCode()
    {
        static $defaultSystemLanguage = false;
        if ( $defaultSystemLanguage===false ) {
            $_data = tep_db_fetch_array(tep_db_query(
                "SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key='DEFAULT_LANGUAGE'"
            ));
            $defaultSystemLanguage = $_data['configuration_value'];
        }
        return $defaultSystemLanguage;
    }

    public static function systemLanguageId()
    {
        static $defaultSystemLanguageId = false;
        if ( $defaultSystemLanguageId===false ){
            $defaultSystemLanguageId = static::get_language_id(static::systemLanguageCode());
        }
        return $defaultSystemLanguageId['languages_id'];
    }
    
/**
 *
 * @param bool $all include inactive
 * @return array
 */
    public static function get_languages($all = false) {
        $_def_id = self::get_default_language_id();
        if ($all) {
            $languages_query = tep_db_query("select languages_id, name, code, image_svg as image, image_svg, locale, shown_language, searchable_language, directory from " . TABLE_LANGUAGES . " where 1 order by IF(code='" . tep_db_input(strtolower(DEFAULT_LANGUAGE)) . "',0,1), sort_order");
        } else {
            $languages_query = tep_db_query("select languages_id, name, code, image_svg as image, image_svg, locale, shown_language, searchable_language, directory from " . TABLE_LANGUAGES . " where languages_status = '1' order by IF(code='" . tep_db_input(strtolower(DEFAULT_LANGUAGE)) . "',0,1), sort_order");
        }
        $languages_array = array();
        $_new = array();
        while ($languages = tep_db_fetch_array($languages_query)) {
            $_tmp = array('id' => $languages['languages_id'],
                'name' => $languages['name'],
                'code' => strtolower($languages['code']),
                'image' => tep_image(DIR_WS_CATALOG . DIR_WS_ICONS . $languages['image'], $languages['name'], '24', '16', 'class="language-icon"'),
                'image_svg' => tep_image(DIR_WS_CATALOG . DIR_WS_ICONS . $languages['image_svg'], $languages['name']),
                'locale' => $languages['locale'],
                'shown_language' => $languages['shown_language'],
                'searchable_language' => $languages['searchable_language'],
                'directory' => $languages['directory']);
            if ($languages['languages_id'] == $_def_id) {
                $_new[] = $_tmp;
            } else {
                $languages_array[] = $_tmp;
            }
        }
        $languages_array = array_merge($_new, $languages_array);

        return $languages_array;
    }

    /**
     * get arrray of language IDs which are hidden in backend for (current) admin
     * @param int $admin
     * @return array
     */
    public static function getAdminHiddenLanguages($admin = false) {

        $ret = \common\models\Languages::find()->select('languages_id')->andWhere(['hide_in_admin'=>1, 'languages_status'=>1])->asArray()->cache(1)->column();
        $admin = new Admin($admin);
        $adminInfo = $admin->getAdditionalInfo();
        if (!empty($adminInfo['hidden_admin_language']) && is_array($adminInfo['hidden_admin_language'])) {
            $ret = array_merge($ret, $adminInfo['hidden_admin_language']);
        }
        $ret = array_map('intval', array_unique(array_diff($ret, [self::get_default_language_id()])));

        return $ret;
    }

    public static function pull_languages() {
        $languages = self::get_languages();
        $lang = array();
        foreach ($languages as $item) {
            $lang[] = array('id' => $item['code'], 'text' => $item['directory']);
        }
        return $lang;
    }

    public static function getPossibleLanguage($char) {
      $ret = false;
      $char = strtolower($char);
      if (defined('DEFAULT_LANGUAGE')) {
        $tmp = self::alphabets([strtolower(constant('DEFAULT_LANGUAGE'))]);
        if (in_array($char, $tmp)) {
          $ret = strtolower(constant('DEFAULT_LANGUAGE'));
        }
      }
      if (!$ret) {
        $ls = \common\models\Languages::find()->select('code')
            ->andWhere([
                  'and',
                  ['languages_status' => 1],
                  ['<>', 'languages_id', self::get_default_language_id()],
                ])
            ->orderBy('sort_order')->asArray()->all();

        foreach ($ls as $l) {
          $tmp = self::alphabets([strtolower($l['code'])]);
          if (in_array($char, $tmp)) {
            $ret = strtolower($l['code']);
            break;
          }
        }
      }
      return $ret;

    }

    public static function alphabets($langs = []) {
      $ret = [];
      foreach ([
        'en' => 'abcdefghijklmnopqrstuvwxyz',
        'ru' => 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя',
        'uk' => 'абвгґдеєжзиіїйклмнопрстуфхцчшщьюя',
        ] as $l => $a) {
        if (empty($l) || empty($a)) {
          continue;
        }
        if (empty($langs) || in_array($l, $langs)) {
          $ret = array_merge($ret, preg_split('//u', $a, null, PREG_SPLIT_NO_EMPTY));
        }
      }
      return $ret;
    }
}
