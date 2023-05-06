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
 * @param bool $include_admin_hidden  include admin_hidden
 * @return array
 */
    public static function get_languages($all = false, $include_admin_hidden = false) {
        $_def_id = self::get_default_language_id();
        /*
        if ($all) {
            $languages_query = tep_db_query("select languages_id, name, code, image_svg as image, image_svg, locale, shown_language, searchable_language, directory from " . TABLE_LANGUAGES . " where 1 order by IF(code='" . tep_db_input(strtolower(DEFAULT_LANGUAGE)) . "',0,1), sort_order");
        } else {
            $languages_query = tep_db_query("select languages_id, name, code, image_svg as image, image_svg, locale, shown_language, searchable_language, directory from " . TABLE_LANGUAGES . " where languages_status = '1' order by IF(code='" . tep_db_input(strtolower(DEFAULT_LANGUAGE)) . "',0,1), sort_order");
        }*/
        $q  = \common\models\Languages::find()
            ->select('languages_id, name, code, image_svg as image, image_svg, locale, shown_language, searchable_language, directory')
            ->orderBy(new \yii\db\Expression("IF(code='" . tep_db_input(strtolower(DEFAULT_LANGUAGE)) . "',0,1)"))
            ->addOrderBy('sort_order');
        if (!$all) {
            $q->andWhere(['languages_status' => 1]);
        }
        $skip_admin_hidden = (!$include_admin_hidden && \frontend\design\Info::isTotallyAdmin());
        if ($skip_admin_hidden && defined('FORCE_HIDE_ADMIN_LANGUAGE') && FORCE_HIDE_ADMIN_LANGUAGE == 'True') {
            $q->andWhere(['hide_in_admin' => 0]);
            $admin = new Admin();
            $adminInfo = $admin->getAdditionalInfo();
            if ((is_array($adminInfo)) && isset($adminInfo['hidden_admin_language']) && is_array($adminInfo['hidden_admin_language'])) {
                $q->andWhere(['not in', 'languages_id', $adminInfo['hidden_admin_language']]);
            }
        }

        $languages_array = array();
        $_new = array();
        foreach ($q->asArray()->all() as $languages) {
        //while ($languages = tep_db_fetch_array($languages_query)) {
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

    private static function copyTable(string $table, int $fromLangId, int $toLangId, $options = null)
    {
        $cols = \Yii::$app->db->schema->getTableSchema($table)->getColumnNames();

        // excludedColumns
        if (is_array($options) && isset($options['excludeColumns'])) {
            $excludedColumns = $options['excludeColumns'];
            if (is_string($excludedColumns)) {
                $excludedColumns = explode(',', $excludedColumns);
            }
            $cols = array_filter($cols, function ($value) use ($excludedColumns) {
                return !in_array($value, $excludedColumns);
            });
        }

        $toColumns = implode(',', $cols);

        $langColumnName = null;
        foreach(['language_id', 'languages_id'] as $supposedColName) {
            $i = array_search($supposedColName, $cols);
            if (false !== $i) {
                \common\helpers\Assert::assert(is_null($langColumnName), "There are two language columns in table $table: $langColumnName and $supposedColName");
                $langColumnName = $supposedColName;
                $cols[$i] = $toLangId;
            }
        }
        \common\helpers\Assert::isNotNull($langColumnName, "Language column not found in table $table: " . var_export($cols, true));
        $fromColumns = implode(',', $cols);

        if ($options['deleteBefore'] ?? false) {
            $deletedRows = \Yii::$app->db->createCommand("DELETE FROM $table WHERE $langColumnName = :toLangId", [':toLangId' => $toLangId])->execute();
            \Yii::info("Copying language $fromLangId to $toLangId. Table $table - deleted $deletedRows rows<br>");
        }

        $affected_rows = \Yii::$app->db->createCommand("INSERT IGNORE INTO $table($toColumns) SELECT $fromColumns FROM $table WHERE $langColumnName = :fromLangId", [':fromLangId' => $fromLangId])->execute();
        \Yii::info( "Copying language $fromLangId to $toLangId. Table $table - $affected_rows rows<br>");
    }

    /**
     * Duplicates language except TABLE_LANGUAGES and TABLE_LANGUAGES_FORMATS
     * @param int $fromLangId
     * @param int $toLangId
     */
    public static function copyLanguage(int $fromLangId, int $toLangId)
    {
        $tables = [
            TABLE_CATEGORIES_DESCRIPTION, TABLE_PRODUCTS_DESCRIPTION, TABLE_PRODUCTS_IMAGES_DESCRIPTION,
            TABLE_PRODUCTS_OPTIONS, TABLE_PRODUCTS_OPTIONS_VALUES,
            TABLE_COUPONS_DESCRIPTION,
            TABLE_PROPERTIES_CATEGORIES_DESCRIPTION, TABLE_PROPERTIES_DESCRIPTION, TABLE_PROPERTIES_VALUES,
            TABLE_INFORMATION,
            TABLE_MANUFACTURERS_INFO,
            TABLE_ORDERS_STATUS, TABLE_ORDERS_STATUS_GROUPS,
            TABLE_PRODUCTS_STOCK_INDICATION_TEXT, TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT,
            TABLE_PRODUCTS_XSELL_TYPE,
            TABLE_COUNTRIES,
            TABLE_BANNERS_LANGUAGES => ['excludeColumns' => 'blang_id'],
            TABLE_DESIGN_BOXES_SETTINGS => ['excludeColumns' => 'id'], TABLE_DESIGN_BOXES_SETTINGS_TMP => ['excludeColumns' => 'id'],
            TABLE_EMAIL_TEMPLATES_TEXTS,
            TABLE_MENU_TITLES => ['excludeColumns' => 'id'],
            TABLE_TRANSLATION => ['excludeColumns' => 'translated,checked'],
        ];
        foreach ($tables as $key=>$value) {
            if (is_int($key)) {
                $table = $value;
                $options = null;
            } else {
                $table = $key;
                $options = $value;
            }
            self::copyTable($table, $fromLangId, $toLangId, $options);
        }
    }
/*
    public static function copyLanguage_old(int $fromLangId, int $toLangId)
    {
        // create additional categories_description records
        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, cd.affiliate_id, cd.categories_seo_page_name from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id where cd.language_id = '" . (int)$fromLangId . "'");
        while ($categories = tep_db_fetch_array($categories_query)) {
            tep_db_query("insert into " . TABLE_CATEGORIES_DESCRIPTION . " (categories_id, language_id, categories_name, affiliate_id, categories_seo_page_name) values ('" . (int)$categories['categories_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($categories['categories_name']) . "', '" . (int)$categories['affiliate_id'] . "', '" . tep_db_input($categories['categories_seo_page_name']) . "')");
        }
        tep_db_free_result($categories_query);

        // create additional products_description records
        $products_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, pd.products_url, pd.platform_id, pd.products_seo_page_name from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where pd.language_id = '" . (int)$fromLangId . "'");
        while ($products = tep_db_fetch_array($products_query)) {
            tep_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url, platform_id, products_seo_page_name) values ('" . (int)$products['products_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($products['products_name']) . "', '" . tep_db_input($products['products_description']) . "', '" . tep_db_input($products['products_url']) . "', '" . (int)$products['platform_id'] . "', '" . tep_db_input($products['products_seo_page_name']) . "')");
        }
        tep_db_free_result($products_query);

        // create additional products_options records
        $products_options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$fromLangId . "'");
        while ($products_options = tep_db_fetch_array($products_options_query)) {
            tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, language_id, products_options_name) values ('" . (int)$products_options['products_options_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($products_options['products_options_name']) . "')");
        }
        tep_db_free_result($products_options_query);

        //coupons
        $coupons_query = tep_db_query("select coupon_id, coupon_name, coupon_description from " . TABLE_COUPONS_DESCRIPTION . " where language_id = '" . (int)$fromLangId . "'");
        while ($coupons = tep_db_fetch_array($coupons_query)) {
            tep_db_query("insert into " . TABLE_COUPONS_DESCRIPTION . " (coupon_id, language_id, coupon_name, coupon_description) values ('" . (int)$coupons['coupon_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($coupons['coupon_name']) . "', '" . tep_db_input($coupons['coupon_description']) . "')");
        }
        tep_db_free_result($coupons_query);

        // create additional products_options_values records
        $products_options_values_query = tep_db_query("select products_options_values_id, products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int)$fromLangId . "'");
        while ($products_options_values = tep_db_fetch_array($products_options_values_query)) {
            tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . (int)$products_options_values['products_options_values_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($products_options_values['products_options_values_name']) . "')");
        }
        tep_db_free_result($products_options_values_query);

        //property categories
        $properties_query = tep_db_query("select categories_id, categories_name, categories_description from " . TABLE_PROPERTIES_CATEGORIES_DESCRIPTION . " where language_id = '" . (int)$fromLangId . "'");
        while ($row = tep_db_fetch_array($properties_query)) {
            tep_db_query("insert into " . TABLE_PROPERTIES_CATEGORIES_DESCRIPTION . " (	categories_id, language_id, categories_name, categories_description ) values ('" . (int)$row['categories_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($row['categories_name']) . "', '" . tep_db_input($row['categories_description']) . "')");
        }
        tep_db_free_result($properties_query);

        //properties
        $properties_query = tep_db_query("select properties_id, properties_name, properties_description, properties_image, properties_units_id from " . TABLE_PROPERTIES_DESCRIPTION . " where language_id = '" . (int)$fromLangId . "'");
        while ($row = tep_db_fetch_array($properties_query)) {
            tep_db_query("insert into " . TABLE_PROPERTIES_DESCRIPTION . " (	properties_id, language_id, properties_name, properties_description, properties_image, properties_units_id ) values ('" . (int)$row['properties_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($row['properties_name']) . "', '" . tep_db_input($row['properties_description']) . "', '" . tep_db_input($row['properties_image']) . "', '" . (int)$row['properties_units_id'] . "')");
        }
        tep_db_free_result($properties_query);

        //properties
        $properties_query = tep_db_query("select values_id, properties_id, values_text, values_number, values_number_upto, values_alt from " . TABLE_PROPERTIES_VALUES . " where language_id = '" . (int)$fromLangId . "'");
        while ($row = tep_db_fetch_array($properties_query)) {
            tep_db_query("insert into " . TABLE_PROPERTIES_VALUES . " (	values_id, properties_id, language_id, values_text, values_number, values_number_upto, values_alt ) values ('" . (int)$row['values_id'] . "', '" . (int)$row['properties_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($row['values_text']) . "', '" . $row['values_number'] . "', '" . $row['values_number_upto'] . "', '" . tep_db_input($row['values_alt']) . "')");
        }
        tep_db_free_result($properties_query);

        // create additional manufacturers_info records
        $information_query = tep_db_query("select * from " . TABLE_INFORMATION . " where languages_id = '" . (int)$fromLangId . "'");
        while ($row = tep_db_fetch_array($information_query)) {
            $row['languages_id'] = (int)$toLangId;
            $row['info_title'] = $row['info_title'];
            $row['description'] = $row['description'];
            $row['page_title'] = $row['page_title'];
            $row['meta_title'] = $row['meta_title'];
            $row['page'] = $row['page'];
            $row['page_type'] = $row['page_type'];
            $row['meta_description'] = $row['meta_description'];
            $row['meta_key'] = $row['meta_key'];
            try {
                tep_db_perform(TABLE_INFORMATION, $row);
            } catch (\Exception $e) {
                \Yii::warning($e->getMessage());
            }
        }
        tep_db_free_result($information_query);

        // create additional manufacturers_info records
        $manufacturers_query = tep_db_query("select m.manufacturers_id, mi.manufacturers_url, mi.manufacturers_seo_name from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on m.manufacturers_id = mi.manufacturers_id where mi.languages_id = '" . (int)$fromLangId . "'");
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            tep_db_query("insert into " . TABLE_MANUFACTURERS_INFO . " (manufacturers_id, languages_id, manufacturers_url, manufacturers_seo_name) values ('" . $manufacturers['manufacturers_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($manufacturers['manufacturers_url']) . "', '" . tep_db_input($manufacturers['manufacturers_seo_name']) . "')");
        }
        tep_db_free_result($manufacturers_query);

        // create additional orders_status records
        $orders_status_query = tep_db_query("select orders_status_id, orders_status_groups_id, orders_status_name, orders_status_template, automated from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$fromLangId . "'");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name, orders_status_groups_id, orders_status_template, automated) values ('" . (int)$orders_status['orders_status_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($orders_status['orders_status_name']) . "', '" . (int)$orders_status['orders_status_groups_id'] . "', '" . tep_db_input($orders_status['orders_status_template']) . "', '" . (int)$orders_status['automated'] . "')");
        }
        tep_db_free_result($orders_status_query);
        // create additional statuses
        $status_query = tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int)$fromLangId . "'");
        while ($status = tep_db_fetch_array($status_query)) {
            tep_db_query("insert into " . TABLE_ORDERS_STATUS_GROUPS . " (orders_status_groups_id, language_id, orders_status_groups_name, orders_status_groups_color) values ('" . (int)$status['orders_status_groups_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($status['orders_status_groups_name']) . "', '" . tep_db_input($status['orders_status_groups_color']) . "')");
        }
        tep_db_free_result($status_query);

        $data_query = tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " where language_id = '" . (int)$fromLangId . "'");
        while ($data = tep_db_fetch_array($data_query)) {
            $data['language_id'] = (int)$toLangId;
            try {
                tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION_TEXT, $data);
            } catch (\Exception $e) {
                \Yii::warning($e->getMessage());
            }
        }
        tep_db_free_result($data_query);

        $data_query = tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " where language_id = '" . (int)$fromLangId . "'");
        while ($data = tep_db_fetch_array($data_query)) {
            $data['language_id'] = (int)$toLangId;
            tep_db_perform(TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT, $data);
        }
        tep_db_free_result($data_query);

        $data_query = tep_db_query("select * from " . TABLE_PRODUCTS_XSELL_TYPE . " where language_id = '" . (int)$fromLangId . "'");
        while ($data = tep_db_fetch_array($data_query)) {
            $data['language_id'] = (int)$toLangId;
            tep_db_perform(TABLE_PRODUCTS_XSELL_TYPE, $data);
        }
        tep_db_free_result($data_query);

        // create additional countries records
        $countries_query = tep_db_query("select * from " . TABLE_COUNTRIES . " where language_id = '" . (int)$fromLangId . "'");
        while ($countries = tep_db_fetch_array($countries_query)) {
            //tep_db_query("insert into " . TABLE_COUNTRIES . "(countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id, language_id) values ('" .$countries['countries_id'] ."', '" .tep_db_input($countries['countries_name']) ."', '" .$countries['countries_iso_code_2'] ."', '" .$countries['countries_iso_code_3'] ."', '" .$countries['address_format_id'] ."', '" .(int)$insert_id  ."')");
            $countries['language_id'] = (int)$toLangId;
            $countries['countries_name'] = $countries['countries_name'];
            tep_db_perform(TABLE_COUNTRIES, $countries);
        }
        tep_db_free_result($countries_query);

        //date formats
        $formats_query = tep_db_query("select configuration_key, configuration_value from " . TABLE_LANGUAGES_FORMATS . " where language_id = '" . (int)$fromLangId . "'");
        while ($formats = tep_db_fetch_array($formats_query)) {
            tep_db_query("insert into " . TABLE_LANGUAGES_FORMATS . " (	configuration_key, configuration_value, language_id) values ('" . tep_db_input($formats['configuration_key']) . "', '" . tep_db_input($formats['configuration_value']) . "', '" . (int)$toLangId . "')");
        }
        tep_db_free_result($formats_query);

        //banners
        $banners_query = tep_db_query("select banners_id, platform_id, banners_title, banners_url, banners_image, banners_html_text from " . TABLE_BANNERS_LANGUAGES . " where language_id = '" . (int)$fromLangId . "'");
        while ($banners = tep_db_fetch_array($banners_query)) {
            tep_db_query("insert into " . TABLE_BANNERS_LANGUAGES . " (	banners_id, platform_id, banners_title, banners_url, banners_image, banners_html_text, language_id) values ('" . (int)$banners['banners_id'] . "', '" . (int)$banners['platform_id'] . "', '" . tep_db_input($banners['banners_title']) . "', '" . tep_db_input($banners['banners_url']) . "', '" . tep_db_input($banners['banners_image']) . "', '" . tep_db_input($banners['banners_html_text']) . "', '" . (int)$toLangId . "')");
        }
        tep_db_free_result($banners_query);

        //design settings
        $settings_query = tep_db_query("select box_id, setting_name, setting_value from " . TABLE_DESIGN_BOXES_SETTINGS . " where language_id = '" . (int)$fromLangId . "'");
        while ($settings = tep_db_fetch_array($settings_query)) {
            tep_db_query("insert into " . TABLE_DESIGN_BOXES_SETTINGS . " (	box_id, setting_name, setting_value, language_id ) values ('" . (int)$settings['box_id'] . "', '" . tep_db_input($settings['setting_name']) . "', '" . tep_db_input($settings['setting_value']) . "', '" . (int)$toLangId . "')");
        }
        $settings_query = tep_db_query("select box_id, setting_name, setting_value from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where language_id = '" . (int)$fromLangId . "'");
        while ($settings = tep_db_fetch_array($settings_query)) {
            tep_db_query("insert into " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " (	box_id, setting_name, setting_value, language_id ) values ('" . (int)$settings['box_id'] . "', '" . tep_db_input($settings['setting_name']) . "', '" . tep_db_input($settings['setting_value']) . "', '" . (int)$toLangId . "')");
        }

        //email templates
        $templates_query = tep_db_query("select email_templates_id, platform_id, affiliate_id, email_templates_subject, email_templates_body from " . TABLE_EMAIL_TEMPLATES_TEXTS . " where language_id = '" . (int)$fromLangId . "' and affiliate_id = 0");
        while ($templates = tep_db_fetch_array($templates_query)) {
            tep_db_query("insert into " . TABLE_EMAIL_TEMPLATES_TEXTS . " (	email_templates_id, platform_id, language_id, affiliate_id, email_templates_subject, email_templates_body ) values ('" . (int)$templates['email_templates_id'] . "', '" . (int)$templates['platform_id'] . "','" . (int)$toLangId . "', 0, '" . tep_db_input($templates['email_templates_subject']) . "', '" . tep_db_input($templates['email_templates_body']) . "')");
        }
        tep_db_free_result($templates_query);

        //menu titles
        $titles_query = tep_db_query("select item_id, title, link from " . TABLE_MENU_TITLES . " where language_id = '" . (int)$fromLangId . "'");
        while ($titles = tep_db_fetch_array($titles_query)) {
            tep_db_query("insert into " . TABLE_MENU_TITLES . " (	language_id, item_id, title, link ) values ('" . (int)$toLangId . "', '" . (int)$titles['item_id'] . "', '" . tep_db_input($titles['title']) . "', '" . tep_db_input($titles['link']) . "')");
        }
        tep_db_free_result($titles_query);

        //product images
        $pimages_query = tep_db_query("select products_images_id, image_title, image_alt, orig_file_name, hash_file_name, file_name, alt_file_name from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where language_id = '" . (int)$fromLangId . "'");
        while ($pimages = tep_db_fetch_array($pimages_query)) {
            tep_db_query("insert into " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " (	products_images_id, language_id, image_title, image_alt, orig_file_name, hash_file_name, file_name, alt_file_name ) values ('" . (int)$pimages['products_images_id'] . "', '" . (int)$toLangId . "', '" . tep_db_input($pimages['image_title']) . "', '" . tep_db_input($pimages['image_alt']) . "', '" . tep_db_input($pimages['orig_file_name']) . "', '" . tep_db_input($pimages['hash_file_name']) . "', '" . tep_db_input($pimages['file_name']) . "', '" . tep_db_input($pimages['alt_file_name']) . "')");
        }
        tep_db_free_result($pimages_query);
        //translations
        $translation_query = tep_db_query("select translation_key, translation_entity, translation_value, hash from " . TABLE_TRANSLATION . " where language_id = '" . (int)$fromLangId . "'");
        while ($trans = tep_db_fetch_array($translation_query)) {
            try {
                tep_db_query("insert into " . TABLE_TRANSLATION . " (	language_id, translation_key, translation_entity, translation_value, hash ) values ( '" . (int)$toLangId . "', '" . tep_db_input($trans['translation_key']) . "', '" . tep_db_input($trans['translation_entity']) . "', '" . tep_db_input($trans['translation_value']) . "', '" . tep_db_input($trans['hash']) . "')");
            } catch (\Exception $e) {
                \Yii::warning($e->getMessage());
            }
        }
        tep_db_free_result($translation_query);
    }
*/
    public static function dropLanguage(int $lID)
    {
        // moved from LanguagesController
        if ($lID) {
            $lng_query = tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_LANGUAGE . "'");
            $lng = tep_db_fetch_array($lng_query);
            if ($lng['languages_id'] == $lID) {
                throw new \Exception('Can\'t delete default language');
            }
            $code = \common\models\Languages::find()->where(['languages_id' => (int) $lID])->asArray()->one();
            tep_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_COUPONS_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_COUNTRIES . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_LANGUAGES_FORMATS . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_BANNERS_LANGUAGES . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_EMAIL_TEMPLATES_TEXTS . " where language_id = '" . (int) $lID . "' and affiliate_id = 0");
            tep_db_query("delete from " . TABLE_MENU_TITLES . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_TRANSLATION . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_PLATFORM_FORMATS . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_PROPERTIES_CATEGORIES_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_PROPERTIES_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_PROPERTIES_VALUES . " where language_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_INFORMATION . " where languages_id = '" . (int) $lID . "'");
            tep_db_query("delete from " . TABLE_PRODUCTS_XSELL_TYPE . " where language_id = '" . (int) $lID . "'");

            if (!empty($code)) {
                $pl_query = tep_db_query("select platform_id, default_language, defined_languages from " . TABLE_PLATFORMS . " where default_language = '" . tep_db_input($code['code']) . "' or defined_languages like '%" . tep_db_input($code['code']) . "%'");
                if (tep_db_num_rows($pl_query)) {

                    while ($data = tep_db_fetch_array($pl_query)) {
                        $_langs = explode(',', $data['defined_languages']);
                        $_langs = array_flip($_langs);
                        unset($_langs[$code['code']]);
                        $_langs = array_flip($_langs);
                        sort($_langs);
                        tep_db_query("update " . TABLE_PLATFORMS . " set defined_languages = '" . (implode(',', $_langs)) . "' where platform_id = '" . $data['platform_id'] . "'");
                        if (strtolower($code['code']) == strtolower($data['default_language'])) {
                            tep_db_query("update " . TABLE_PLATFORMS . " set default_language = '" . $_langs[0] . "' where platform_id = '" . $data['platform_id'] . "'");
                        }
                    }
                }
                tep_db_query("delete from " . TABLE_LANGUAGES . " where languages_id = '" . (int) $lID . "'");
            }
        }

    }

}
