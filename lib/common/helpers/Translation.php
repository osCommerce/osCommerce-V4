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

use frontend\design\Info;

class Translation
{
    public static $translations = [];
    public static $translationsKeys = [];
    public static $translationsValues = [];

    public static function init($entity = '', $language_id = '', $skipEmptyKeys = true)
    {
        global $languages_id, $language;

        if (!$language_id) $language_id = $languages_id;

        // {{ double define
        static $loaded_by_key = [];
        $key = strval($entity).'^'.(int)$language_id.'^'.($skipEmptyKeys?'1':'0');
        if ( isset($loaded_by_key[$key]) ) return;
        $loaded_by_key[$key] = 1;
        // }} double define


        $translations = \Yii::$app->getCache()->getOrSet(
            'translation_'.str_replace('/','.',$entity).'_'.(int)$language_id, function() use ($entity, $language_id){
                return \common\models\Translation::find()
                    ->select(['translation_key', 'translation_value'])
                    ->where(['translation_entity' => $entity, 'language_id' => (int)$language_id])
                    ->asArray()
                    ->all();
        },0, new \yii\caching\TagDependency(['tags'=>['translation', self::getTagNameForEntity($entity)]]));
        /*
        $translations = [];
        $translation_query = tep_db_query("select translation_key, translation_value from " . TABLE_TRANSLATION . " where translation_entity = '" . tep_db_input($entity) . "' and language_id = '" . (int)$language_id . "'");
        while ($translation = tep_db_fetch_array($translation_query)) {
            $translations[] = $translation;
        }
        */
        foreach($translations as $translation) {
            if ($skipEmptyKeys && empty($translation['translation_value'])) {
                continue;
            }

            self::defineKeys($translation, $entity);
        }

        $lang = \common\helpers\Language::get_language_id(DEFAULT_LANGUAGE);
        if (isset($lang['languages_id']) && $lang['languages_id'] !=$language_id) {
            $translation_query = tep_db_query("select translation_key, translation_value from " . TABLE_TRANSLATION . " where translation_entity = '" . tep_db_input($entity) . "' and language_id = '" . (int)$lang['languages_id'] . "'");
            while ($translation = tep_db_fetch_array($translation_query)) {
                self::defineKeys($translation, $entity);
            }
        }
    }

    public static function defineKeys($translation, $entity)
    {
        if (defined($translation['translation_key'])) {
            return false;
        }

        $translation['translation_value'] = \common\classes\TlUrl::replaceUrl($translation['translation_value']);
        $translation['translation_value'] = self::checkIncludedConstants($translation['translation_value']);

        static $define_flag;
        if ( is_null($define_flag) ){
            $define_flag = !\common\helpers\Acl::isFrontendTranslation() && !(Info::isAdmin() && method_exists(\Yii::$app->request, 'get') && \Yii::$app->request->get('texts'));
        }
        if ($define_flag) {
            define($translation['translation_key'], $translation['translation_value']);
            return true;
        }

        define($translation['translation_key'], '##' . $translation['translation_key'] . '##');

        if (isset(self::$translations[$translation['translation_key']]) && self::$translations[$translation['translation_key']]) {
            return true;
        }

        self::$translations[$translation['translation_key']] = [
            'value' => $translation['translation_value'],
            'entity' => $entity,
        ];
        self::$translationsKeys[] = '##' . $translation['translation_key'] . '##';
        self::$translationsValues[] = '<span class="translation-key" data-translation-key="' . $translation['translation_key'] . '" data-translation-entity="' . $entity . '">' . $translation['translation_value'] . '</span>';

        return true;
    }
    
    public static function checkIncludedConstants($value){
        $value = preg_replace_callback(
           '/##(.*?)##/',
            function ($found) {
              return ( defined($found[1]) ? CONSTANT($found[1]) : '');
            },
            $value
        );
        return $value;
    }

/**
 *
 * @global int $languages_id 
 * @param string $translation_key
 * @param string $translation_entity
 * @param int $language_id optional
 * @return translation or false
 */
    public static function getTranslationValue($translation_key, $translation_entity = '', $language_id = '')
    {
        global $languages_id;

        if (!$language_id) $language_id = $languages_id;
        $ret = false;
  
        $translation_query = tep_db_query("select translation_value from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        if ($translation = tep_db_fetch_array($translation_query)) {
          $ret = $translation['translation_value'];
        }

        return $ret;
    }

    public static function getValue($translation_key, $translation_entity = 'configuration', $default = '##key##')
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        if (defined($translation_key)) return constant($translation_key);

        $res = self::getTranslationValue($translation_key, $translation_entity, $languages_id);
        $defLanguageId = \common\helpers\Language::get_default_language_id();
        if (!$res && $languages_id != $defLanguageId) {
            $res = self::getTranslationValue($translation_key, $translation_entity, $defLanguageId);
        }

        // return
        if ($res) return $res;
        if (is_null($default)) return false;
        if ($default=='##key##') return $translation_key;
        return $default;
    }

    public static function setTranslationValue($translation_key, $translation_entity, $language_id, $translation_value)
    {
        $translation_query = tep_db_query("select * from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        if (tep_db_num_rows($translation_query) > 0) {
            $sql_data_array = [
                'translation_value' => $translation_value,
            ];
            tep_db_perform(TABLE_TRANSLATION, $sql_data_array, 'update', "language_id = '" . (int)$language_id . "' and translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "'");
        } else {
            $hash = md5($translation_key . '-' . $translation_entity);
            $sql_data_array = [
                'language_id' => (int)$language_id,
                'translation_key' => $translation_key,
                'translation_entity' => $translation_entity,
                'translation_value' => $translation_value,
                'hash' => $hash,
            ];
            tep_db_perform(TABLE_TRANSLATION, $sql_data_array);
        }
    }
    
    public static function replaceTranslationValueByKey($translation_key, $translation_entity, $language_id, $translation_value)
    {
        $translation_query = tep_db_query("select * from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        if (tep_db_num_rows($translation_query) == 0) {
            $hash = md5($translation_key . '-' . $translation_entity);
            $sql_data_array = [
                'language_id' => (int)$language_id,
                'translation_key' => $translation_key,
                'translation_entity' => $translation_entity,
                'translation_value' => $translation_value,
                'hash' => $hash,
            ];
            tep_db_perform(TABLE_TRANSLATION, $sql_data_array);
        }
        $sql_data_array = [
            'translation_value' => $translation_value,
        ];
        tep_db_perform(TABLE_TRANSLATION, $sql_data_array, 'update', "language_id = '" . (int)$language_id . "' and translation_key = '" . tep_db_input($translation_key) . "'");
    }
    
    public static function replaceTranslationValueByOldValue($translation_key, $translation_entity, $language_id, $translation_value)
    {
        $translation_query = tep_db_query("select * from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        if (tep_db_num_rows($translation_query) > 0) {
            $translation = tep_db_fetch_array($translation_query);
            $old_translation_value = $translation['translation_value'];
            if (!empty($old_translation_value)) {
                $sql_data_array = [
                    'translation_value' => $translation_value,
                ];
                tep_db_perform(TABLE_TRANSLATION, $sql_data_array, 'update', "language_id = '" . (int)$language_id . "' and translation_value = '" . tep_db_input($old_translation_value) . "'");
            } else {
                $sql_data_array = [
                    'translation_value' => $translation_value,
                ];
                tep_db_perform(TABLE_TRANSLATION, $sql_data_array, 'update', "language_id = '" . (int)$language_id . "' and translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "'");
            }
        } else {
            $hash = md5($translation_key . '-' . $translation_entity);
            $sql_data_array = [
                'language_id' => (int)$language_id,
                'translation_key' => $translation_key,
                'translation_entity' => $translation_entity,
                'translation_value' => $translation_value,
                'hash' => $hash,
            ];
            tep_db_perform(TABLE_TRANSLATION, $sql_data_array);
        }
    }
    
    public static function loadJS($translation_entity, $language_id = 0){
      global $languages_id, $lng;
      
      $language_id =  !$language_id ? $languages_id : $language_id;

      $translation_query = tep_db_query("select t1.translation_key, if(length(t1.translation_value)>0, t1.translation_value, t2.translation_value) as translation_value from " . TABLE_TRANSLATION . " t1 left join " . TABLE_TRANSLATION . " t2 on (t2.language_id = (select l.languages_id from " . TABLE_LANGUAGES . " l where l.code = '" . DEFAULT_LANGUAGE . "') and t1.translation_key = t2.translation_key and t1.translation_entity = t2.translation_entity) where t1.translation_entity = '" . tep_db_input($translation_entity) . "' and t1.language_id = '" . (int)$language_id . "'");

      $translations = [];
      
      if (tep_db_num_rows($translation_query)){
            while ($translation = tep_db_fetch_array($translation_query)) {
                if (!isset($translations[$translation['translation_key']])) {
                    $translations[$translation['translation_key']] = $translation['translation_value'];
                }
            }        
      }
      
      return $translations;
    }
    
    public static function isTranslated($translation_key, $translation_entity = '', $language_id = '')
    {
        global $languages_id;

        if (!$language_id) $language_id = $languages_id;

        $translation_query = tep_db_query("select translated from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        $translation = tep_db_fetch_array($translation_query);
        return $translation['translated'] ?? null;
        
    }

    public static function setTranslated($translation_key, $translation_entity, $language_id, $status = 0)
    {
      tep_db_query("update " . TABLE_TRANSLATION . " set translated = " . (int)$status . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
    } 
    
    public static function isChecked($translation_key, $translation_entity = '', $language_id = '')
    {
        global $languages_id;

        if (!$language_id) $language_id = $languages_id;

        $translation_query = tep_db_query("select checked from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        $translation = tep_db_fetch_array($translation_query);
        return $translation['checked'] ?? null;
        
    }    
    
    public static function setChecked($translation_key, $translation_entity, $language_id, $status = 0)
    {
      tep_db_query("update " . TABLE_TRANSLATION . " set checked = " . (int)$status . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
    }

    public static function translationsForJs($keys, $json = true)
    {
        if (!$keys || !is_array($keys)) return [];

        $jsKeys = [];
        foreach ($keys as $key){
            if (defined($key)) {
                $jsKeys[$key] = constant($key);
            } else {
                $jsKeys[$key] = $key;
            }
        }

        if ($json) {
            return json_encode($jsKeys);
        } else {
            return $jsKeys;
        }
    }

    public static function frontendTranslation($content)
    {
        if (!\common\helpers\Acl::isFrontendTranslation() && !(Info::isAdmin() && \Yii::$app->request->get('texts'))) {
            return $content;
        }

        $content = preg_replace_callback('|([a-zA-Z\-]+)=\"[\s]{0,}(##([A-Z0-9_]+)##)[\s]{0,}\"|', function($matches){

            return str_replace($matches[2], self::$translations[$matches[3]]['value'], $matches[0])
                . ' data-translation'
                . ' data-translation-key-' . $matches[1] . '="' . $matches[3] . '"'
                . ' data-translation-entity-' . $matches[1] . '="' . self::$translations[$matches[3]]['entity'] . '"';

        }, $content);

        $content = preg_replace_callback('|([a-zA-Z\-]+)=\'[\s]{0,}(##([A-Z0-9_]+)##)[\s]{0,}\'|', function($matches){

            return str_replace($matches[2], self::$translations[$matches[3]]['value'], $matches[0])
                . " data-translation"
                . " data-translation-key-' . $matches[1] . '='" . $matches[3] . "'"
                . " data-translation-entity-' . $matches[1] . '='" . self::$translations[$matches[3]]['entity'] . "'";

        }, $content);

        $content = preg_replace_callback('|<option([^>]+)>(.*(##([A-Z0-9_]+)##).*?)</option>[\s\n]{0,}|', function($matches){

            return '<option class="translation-key-option" '
                . $matches[1]
                . ' data-translation-key="' . $matches[4] . '"'
                . ' data-translation-entity="' . self::$translations[$matches[4]]['entity'] . '">'
                . str_replace($matches[3], self::$translations[$matches[4]]['value'], $matches[2])
                . '</option>';

        }, $content);

        $content = str_replace(self::$translationsKeys, self::$translationsValues, $content);

        \frontend\design\Info::addJsData(\frontend\design\EditData::jsData());

        $entryDataPlaceHolder = 'var entryData = JSON.parse(\'' . addslashes(json_encode(\frontend\design\Info::$jsGlobalData)) . '\');';
        if (Info::isAdmin() && \Yii::$app->request->get('texts')) {
            $entryDataPlaceHolder .= '
    window.parent.postMessage(entryData, \'*\');
            ';
        }
        $content = str_replace('var entryDataPlaceHolder;', $entryDataPlaceHolder, $content);

        return $content;
    }

    public static function resetCache()
    {
        \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),'translation');
    }

    public static function resetCacheEnity($entity)
    {
        \yii\caching\TagDependency::invalidate(\Yii::$app->getCache(),self::getTagNameForEntity($entity));
    }

    private static function getTagNameForEntity($entity)
    {
        return 'translate_'.str_replace('/','.',$entity);
    }


    public static function forceConst($constNames, $entity)
    {
        if (!is_array($constNames)) {
            $const = array($constNames);
        }
        foreach ($constNames as $constName) {
            defined($constName) or define($constName, self::getValue($constName, $entity));
        }
    }
}
