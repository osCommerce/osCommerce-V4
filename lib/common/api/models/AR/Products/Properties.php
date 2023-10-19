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

namespace common\api\models\AR\Products;

use backend\models\EP\Tools;
use common\api\models\AR\CatalogProperty\PropertyDescription;
use common\api\models\AR\EPMap;
use common\api\models\AR\Products\Properties\Name;
use common\helpers\Seo;
use common\models\PropertiesValues;

class Properties extends EPMap
{

    protected $hideFields = [
        'products_id',
        //'properties_id',
        //'values_id',
    ];
    public $property_type;

    public static function tableName()
    {
        return TABLE_PROPERTIES_TO_PRODUCTS;
    }

    public static function primaryKey()
    {
        return ['products_id', 'properties_id', 'values_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;

        parent::parentEPMap($parentObject);
    }

    public function importArray($data)
    {
        //$data['names'];
        if ( !empty($data['property_type']) ) {
            $this->property_type = $data['property_type'];
        }else{
            $this->property_type = 'text';
        }
        if ( isset($data['name_path']) ) {
            $lookupId = 0;
            $levelNames = [];
            foreach ( $data['name_path'] as $langCode=>$prop_name_path ) {
                $prop_names = explode(';', $prop_name_path);
                $langId = $langCode=='*'?0:\common\classes\language::get_id($langCode);
                foreach ($prop_names as $_level=>$_name){
                    $levelNames[$_level][$langId] = $_name;
                }
            }
            foreach ($levelNames as $__idx=>$levelNameArray) {
                $parentId = $lookupId;
                $lookupId = $this->lookupPropertiesByName(current($levelNameArray), $parentId, $langId);
                if ( $lookupId==0 ) {
                    $lookupId = $this->createProperties(($__idx+1==count($levelNames))?$this->property_type:'category', $levelNameArray, $parentId, $langId);
                }elseif(count($levelNameArray)>1){
                    foreach ($levelNameArray as $lang_id=>$prop_name){
                        if ( PropertyDescription::updateAll([
                            'properties_name' => $prop_name,
                            'properties_seo_page_name' => Seo::makePropertySlug([
                                'properties_id' => (int)$lookupId,
                                'properties_name' => $prop_name,
                            ]),
                        ],['properties_id'=>(int)$lookupId, 'language_id' => (int)$lang_id ]) ){
                            $this->lookupPropertiesByName((int)$lookupId,-1, 0);
                        }
                    }
                }
            }
            $data['properties_id'] = $lookupId;
        }

        if ( isset($data['values']) && !empty($data['properties_id']) ) {
            $lookupId = 0;

            $valueNames = [];
            foreach ( $data['values'] as $langCode=>$prop_value ) {
                $langId = $langCode=='*'?0:\common\classes\language::get_id($langCode);
                $valueNames[$langId] = $prop_value;
            }
            if ( isset($valueNames[0]) ){
                foreach(\common\classes\language::get_all() as $__lang_all){
                    if ( isset($valueNames[(int)$__lang_all['id']]) ) continue;
                    $valueNames[(int)$__lang_all['id']] = $valueNames[0];
                }
                unset($valueNames[0]);
            }
            reset($valueNames);

            $prop_value = current($valueNames);
            $get_value_id_r = tep_db_query(
                "SELECT values_id ".
                "FROM ".TABLE_PROPERTIES_VALUES." ".
                "WHERE properties_id='".$data['properties_id']."' AND values_text='".tep_db_input($prop_value)."' ".
                "LIMIT 1 "
            );
            if (tep_db_num_rows($get_value_id_r)>0){
                $_value_id = tep_db_fetch_array($get_value_id_r);
                $data['values_id'] = $_value_id['values_id'];
            }else{
                $max_value = tep_db_fetch_array(tep_db_query("SELECT MAX(values_id) AS current_max_id FROM " . TABLE_PROPERTIES_VALUES));
                $values_id = intval($max_value['current_max_id'])+1;
                $prop_value_slug = Seo::makePropertyValueSlug([
                    'properties_id' => $data['properties_id'],
                    'values_text' => $prop_value,
                ]);
                if ( $this->property_type=='number' ) {
                    tep_db_query(
                        "INSERT INTO " . TABLE_PROPERTIES_VALUES . " (values_id, properties_id, language_id, values_text, values_number, values_seo_page_name ) " .
                        "SELECT '{$values_id}', '" . $data['properties_id'] . "', languages_id, '" . tep_db_input($prop_value) . "', '".tep_db_input($prop_value)."', '" . tep_db_input($prop_value_slug) . "' FROM " . TABLE_LANGUAGES . " WHERE languages_status=1 "
                    );
                }else {
                    tep_db_query(
                        "INSERT INTO " . TABLE_PROPERTIES_VALUES . " (values_id, properties_id, language_id, values_text, values_seo_page_name ) " .
                        "SELECT '{$values_id}', '" . $data['properties_id'] . "', languages_id, '" . tep_db_input($prop_value) . "', '" . tep_db_input($prop_value_slug) . "' FROM " . TABLE_LANGUAGES . " WHERE languages_status=1 "
                    );
                }
                $data['values_id'] = $values_id;
            }

            if (count($valueNames)>1){
                foreach ($valueNames as $lang_id=>$prop_value) {
                    $prop_value_slug = Seo::makePropertyValueSlug([
                        'values_id' => $data['values_id'],
                        'language_id' => $lang_id,
                        'properties_id' => $data['properties_id'],
                        'values_text' => $prop_value,
                    ]);
                    if ( $this->property_type=='number' ) {
                        PropertiesValues::updateAll(
                            ['values_text' => $prop_value, 'values_number'=> $prop_value, 'values_seo_page_name'=>$prop_value_slug],
                            ['values_id' => $data['values_id'], 'properties_id' => $data['properties_id'], 'language_id' => $lang_id]
                        );
                    }else{
                        PropertiesValues::updateAll(
                            ['values_text' => $prop_value, 'values_seo_page_name'=>$prop_value_slug],
                            ['values_id' => $data['values_id'], 'properties_id' => $data['properties_id'], 'language_id' => $lang_id]
                        );
                    }
                }
            }
        }

        if ( empty($data['properties_id']) || empty($data['values_id']) ) {
            return false;
        }

        return parent::importArray($data);
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);
        if ( count($fields)==0 || in_array('name',$fields) ) {
            $propNames = $this->getPropertiesNameArr($this->properties_id);
            $data['names'] = $propNames['names'];
            $data['name_path'] = $propNames['names'];
            $parent_id = $propNames['parent_id'];
            while( $parent_id>0 ) {
                $propNames = $this->getPropertiesNameArr($parent_id);
                foreach ( $data['name_path'] as $langCode=>$savedPath ) {
                    $data['name_path'][$langCode] = (isset($propNames['names'][$langCode])?$propNames['names'][$langCode]:'').';'.$savedPath;
                }
                $parent_id = $propNames['parent_id'];
            }
        }
        if ( count($fields)==0 || in_array('values',$fields) ) {
            $data['values'] = [];
            $get_data_r = tep_db_query(
                "SELECT pv.language_id, pv.values_text ".
                "FROM ".TABLE_PROPERTIES_VALUES." pv ".
                "WHERE pv.values_id='".$this->values_id."' ".
                " AND pv.properties_id='".$this->properties_id."' "
            );
            if ( tep_db_num_rows($get_data_r)>0 ) {
                while( $_data = tep_db_fetch_array($get_data_r) ) {
                    $data['values'][ \common\classes\language::get_code($_data['language_id']) ] = $_data['values_text'];
                }
            }
        }

        return $data;
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        if ( intval($importedObject->properties_id)==intval($this->properties_id) && intval($importedObject->values_id)==intval($this->values_id) ) {
            $this->pendingRemoval = false;
            return true;
        }
        return false;

    }


    protected function getPropertiesNameArr($id)
    {
        $result = [
            'parent_id' => 0,
            'names' => [],
        ];

        $get_data_r = tep_db_query(
            "SELECT p.parent_id, pd.language_id, pd.properties_name ".
            "FROM ".TABLE_PROPERTIES." p, ".TABLE_PROPERTIES_DESCRIPTION ." pd ".
            "WHERE p.properties_id=pd.properties_id ".
            " AND p.properties_id='".$id."' "
        );
        if ( tep_db_num_rows($get_data_r)>0 ) {
            while( $_data = tep_db_fetch_array($get_data_r) ) {
                $result['parent_id'] = $_data['parent_id'];
                $result['names'][ \common\classes\language::get_code($_data['language_id']) ] = $_data['properties_name'];
            }
        }

        return $result;
    }

    protected function lookupPropertiesByName($prop_name, $parentId, $langId)
    {
        static $lookups = [];
        if ( $parentId===-1 ) {
            $idx = array_search($prop_name, $lookups);
            if ( $idx!==false ) unset($lookups[$idx]);
            return;
        }
        $key = (int)$parentId.'^'.(int)$langId.'^'.$prop_name;
        if ( isset($lookups[$key]) ) return $lookups[$key];

        $propId = 0;
        $get_data_r = tep_db_query(
            "SELECT p.properties_id ".
            "FROM ".TABLE_PROPERTIES." p, ".TABLE_PROPERTIES_DESCRIPTION ." pd ".
            "WHERE p.properties_id=pd.properties_id ".
            " AND pd.properties_name = '".tep_db_input($prop_name)."' ".
            " AND p.parent_id='".$parentId."' ".
            "LIMIT 1"
        );
        if ( tep_db_num_rows($get_data_r)>0 ) {
            $get_data = tep_db_fetch_array($get_data_r);
            $propId = $get_data['properties_id'];
            $lookups[$key] = $propId;
        }
        return $propId;
    }

    protected function createProperties($type, $prop_name, $parentId, $langId)
    {
        tep_db_perform(TABLE_PROPERTIES, [
            'parent_id' => $parentId,
            'properties_type' => $type,
            'date_added' => 'now()',
        ]);
        $propId = tep_db_insert_id();

        if ( is_array($prop_name) ) {
            $prop_names = $prop_name;
            $prop_name = '';
            foreach ($prop_names as $langId=>$_prop_name ){
                if ( empty($prop_name) || $langId==\common\classes\language::defaultId() ) $prop_name = $_prop_name;
                $property_seo_name = Seo::makePropertySlug([
                    'properties_id' => (int)$propId,
                    'properties_name' => $_prop_name,
                ]);
                tep_db_query(
                    "INSERT IGNORE INTO " . TABLE_PROPERTIES_DESCRIPTION . " (properties_id, language_id, properties_name, properties_seo_page_name) " .
                    "VALUES ('" . (int)$propId . "', '".(int)$langId."', '" . tep_db_input($_prop_name) . "', '" . tep_db_input($property_seo_name) . "')"
                );
            }
        }
        $property_seo_name = Seo::makePropertySlug([
            'properties_id' => (int)$propId,
            'properties_name' => $prop_name,
        ]);
        tep_db_query(
            "INSERT IGNORE INTO " . TABLE_PROPERTIES_DESCRIPTION . " (properties_id, language_id, properties_name, properties_seo_page_name) " .
            "SELECT '" . (int)$propId . "', languages_id, '" . tep_db_input($prop_name) . "', '" . tep_db_input($property_seo_name) . "' FROM " . TABLE_LANGUAGES . " WHERE languages_status=1"
        );

        return $propId;
    }

}