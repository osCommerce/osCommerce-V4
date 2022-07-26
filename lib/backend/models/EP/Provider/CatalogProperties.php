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

namespace backend\models\EP\Provider;

use backend\models\EP\Formatter;
use backend\models\EP;
use backend\models\EP\Messages;

class CatalogProperties extends Properties implements ImportInterface, ExportInterface
{
    protected $fields = array();
    protected $data = array();
    /**
     * @var EP\Tools
     */
    protected $EPtools;

    protected $_data_units;

    protected $value_sources;
    protected $property_name_sources;

    protected $last_properties_id = 0;
    protected $last_property_data = [];
    protected $clean_up = [];

    function init()
    {
        parent::init();
        $this->initFields();
    }

    protected function initFields(){
        $this->fields = array();
        $this->fields[] = array( 'name' => 'key_field', 'value' => 'EXISTING_REF', 'is_key'=>true, 'calculated'=>true, 'get'=>'get_key_field' );
        //$this->fields[] = array( 'name' => 'categories_image', 'value' => 'Categories Image' );
        foreach( \common\helpers\Language::get_languages() as $_lang ) {
            $data_descriptor = '%|' . TABLE_PROPERTIES_DESCRIPTION . '|' . $_lang['id'];
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'properties_name',
                '_is_name_column' => true,
                'name' => 'properties_name_' . $_lang['code'],
                'value' => 'Properties Name ' . $_lang['code'],
            );

            $data_descriptor = '@|property_values|' . $_lang['id'];
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                '_lang_id' => $_lang['id'],
                '_is_value_column' => true,
                'name' => 'value_' . $_lang['code'],
                'value' => 'Values ' . $_lang['code'],
                'set' => 'set_values',
                'get' => 'get_values',
            );
        }
        $this->fields[] = array( 'name' => 'properties_type', 'value' => 'Type' );
        $this->fields[] = array( 'name' => 'sort_order', 'value' => 'Sort Order' );
        $this->fields[] = array( 'name' => 'multi_choice', 'value' => 'Is multi choice?', 'get'=>'get_flag_yes_no', 'set'=>'set_flag_yes_no' );
        $this->fields[] = array( 'name' => 'multi_line', 'value' => 'Is multi line?', 'get'=>'get_flag_yes_no', 'set'=>'set_flag_yes_no' );
        $this->fields[] = array( 'name' => 'decimals', 'value' => 'Format (decimals places)' );
        $this->fields[] = array( 'name' => 'display_product', 'value' => 'Display mode: Product info', 'get'=>'get_flag_yes_no', 'set'=>'set_flag_yes_no' );
        $this->fields[] = array( 'name' => 'display_listing', 'value' => 'Display mode: Listing', 'get'=>'get_flag_yes_no', 'set'=>'set_flag_yes_no' );
        $this->fields[] = array( 'name' => 'display_filter', 'value' => 'Display mode: Filter', 'get'=>'get_flag_yes_no', 'set'=>'set_flag_yes_no' );
        $this->fields[] = array( 'name' => 'display_search', 'value' => 'Display mode: Search', 'get'=>'get_flag_yes_no', 'set'=>'set_flag_yes_no' );
        $this->fields[] = array( 'name' => 'display_compare', 'value' => 'Display mode: Compare', 'get'=>'get_flag_yes_no', 'set'=>'set_flag_yes_no' );

        foreach( \common\helpers\Language::get_languages() as $_lang ) {
            $data_descriptor = '%|' . TABLE_PROPERTIES_DESCRIPTION . '|' . $_lang['id'];
            /*$this->fields[] = array(
              'data_descriptor' => $data_descriptor,
              'column_db' => 'properties_name',
              'name' => 'properties_name_' . $_lang['code'],
              'value' => 'Properties Name ' . $_lang['code'],
            );*/
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'properties_name_alt',
                'name' => 'properties_name_alt_' . $_lang['code'] . '_0',
                'value' => 'Properties Alternative Name ' . $_lang['code'],
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'properties_description',
                'name' => 'properties_description_' . $_lang['code'] . '_0',
                'value' => 'Properties Description ' . $_lang['code'],
            );
            /*$this->fields[] = array(
              'data_descriptor' => $data_descriptor,
              'column_db' => 'properties_image',
              'name' => 'properties_image_' . $_lang['code'] . '_0',
              'value' => 'Properties Image ' . $_lang['code'],
            );*/
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                '_lang_id' => $_lang['id'],
                'column_db' => 'properties_units_id',
                'name' => 'properties_units_id_' . $_lang['code'] . '_0',
                'value' => 'Units ' . $_lang['code'],
                'get' => 'get_units', 'set' => 'set_units'
            );
        }

    }

    function get_key_field( $field_data, $properties_id, $value_id='' ){
        //$this->data['key_field'] = $this->tep_get_properties_full_path((int)$this->data['properties_id']);
        $this->data['key_field'] = (int)$this->data['properties_id'].(empty($value_id)?'':'_'.(int)$value_id);
        return $this->data['key_field'];
    }


    protected function buildSources($useColumns)
    {
        if (parent::buildSources($useColumns)) {
            foreach ($this->fields as $_field) {
                if (isset($_field['_is_value_column']) && $_field['_is_value_column']) {
                    $value_sources[] = $_field['name'];
                }
                if (isset($_field['_is_name_column']) && $_field['_is_name_column']) {
                    $property_name_sources[] = $_field['name'];
                }
            }
            $this->value_sources = $value_sources;
            $this->property_name_sources = $property_name_sources;
            return true;
        }
        return false;
    }
    
    private function fetchPropertiesData($_prop_id)
    {
        $main_source = $this->main_source;
        $export_columns = $this->export_columns;
        $data_sources = $this->data_sources;
        
        $main_sql =
            "SELECT {$main_source['select']} properties_id " .
            "FROM " . TABLE_PROPERTIES . " " .
            "WHERE properties_id='{$_prop_id}' " .
            "";
            
        $query = tep_db_query($main_sql);
        
        if ($this->data = tep_db_fetch_array($query)) {
            foreach ($data_sources as $source_key => $source_data) {
                if ($source_data['table']) {
                    $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                    if ($source_data['table'] == TABLE_PROPERTIES_DESCRIPTION) {
                        $data_sql .= "AND properties_id='{$this->data['properties_id']}' AND language_id='{$source_data['params'][0]}' ";
                    } else {
                        $data_sql .= "AND 1=0 ";
                    }
                    //echo $data_sql.'<hr>';
                    $data_sql_r = tep_db_query($data_sql);
                    if (tep_db_num_rows($data_sql_r) > 0) {
                        $_data = tep_db_fetch_array($data_sql_r);
                        $this->data = array_merge($this->data, $_data);
                    }
                } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                    call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                }
            }
            
            foreach ($export_columns as $db_key => $export) {
                if (isset($export['get']) && method_exists($this, $export['get'])) {
                    $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['properties_id']));
                }
            }
        }
        
        return $this->data;
    }

    public function exportRow(){
//        if ( is_array($this->data) || !empty($this->data['_values']) ) {
//            $value = current($this->data['_values']);
//        }
//        echo '<pre>'; var_dump($this->data); echo '</pre>';
        $_prop_variant = current($this->export_data);

        if ( !is_array($_prop_variant) ) return false;
        
//        $this->data = current($this->export_data);
//        if ( !is_array($this->data) ) return $this->data;
        
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $value_sources = $this->value_sources;
        $export_columns = $this->export_columns;
        $file_primary_column = $this->file_primary_column;
        
        $_prop_id = $_prop_variant['id'];
        
        if ( $this->last_properties_id!=$_prop_id ) {
          $this->data = $this->fetchPropertiesData($_prop_id);
          $this->last_properties_id = $_prop_id;
        }
        
        if ( count($value_sources)==0 || !isset($this->data['_values']) || count($this->data['_values'])==0 ) {
            next($this->export_data);
            return $this->data;
        }else{
            $write_data = $this->data;
            
            $value_id = current($this->data['_values_ids']);
            $values_data = $this->data['_values'][$value_id];
            
            if ( $this->data['_values_ids'][0]!=$value_id ){
                $write_data = [];
                $write_data[$file_primary_column] = $this->data[$file_primary_column];
            }

            foreach( $value_sources as $value_column_name ) {
                $_column_lang_id = $export_columns[$value_column_name]['_lang_id'];
                if (!isset($values_data[$_column_lang_id])) continue;
                $value_array = $values_data[$_column_lang_id];
                $write_data[$value_column_name] = $value_array['values_text'];

                if ( isset($write_data[$file_primary_column]) ) {
                    $write_data[$file_primary_column] = $this->get_key_field($export_columns[$value_column_name], $this->data['properties_id'], $value_id);
                }
            }
            
            next($this->data['_values_ids']);
            if ( !current($this->data['_values_ids']) ) {
                next($this->export_data);
            }
            return $write_data;
        }
    }
    
    public function prepareExport($useColumns, $filter){
        $this->buildSources($useColumns);
        
        $this->export_data = array_filter(\common\helpers\Properties::get_properties_tree(( isset($filter['properties_id']) && $filter['properties_id']>0 )?$filter['properties_id']:0,'','',false),function($item){
            return !empty($item['id']);
        });
        reset($this->export_data);
    }

    function export(Formatter\FormatterInterface $output, $selected_fields, $filter)
    {
        $export_columns = array();

        $main_source = array(
            'select' => '',
        );
        $data_sources = array();
        $value_sources = array();
        $file_primary_column = '';
        foreach ($this->fields as $_field) {
            if (isset($_field['is_key']) && $_field['is_key'] === true) {
                $file_primary_column = (isset($_field['column_db']) ? $_field['column_db'] : $_field['name']);
            }
            // skip not configured here
            if (is_array($selected_fields) && !in_array($_field['name'], $selected_fields)) {
                continue;
            }
            if (isset($_field['data_descriptor'])) {
                if (!isset($data_sources[$_field['data_descriptor']])) {
                    $data_descriptor = explode('|', $_field['data_descriptor']);
                    $data_sources[$_field['data_descriptor']] = array(
                        'select' => '',
                        'table' => $data_descriptor[0] == '%' ? $data_descriptor[1] : false,
                        'init_function' => $data_descriptor[0] == '@' ? $data_descriptor[1] : false,
                        'params' => array_slice($data_descriptor, 2),
                    );
                }
                if ( isset($_field['calculated']) && $_field['calculated'] ) {

                }else {
                    $data_sources[$_field['data_descriptor']]['select'] .= (isset($_field['column_db']) ? "{$_field['column_db']} AS {$_field['name']}" : $_field['name']) . ", ";
                }
            } else {
                if ( isset($_field['calculated']) && $_field['calculated'] ) {

                }else {
                    $main_source['select'] .= (isset($_field['column_db']) ? "{$_field['column_db']} AS {$_field['name']}" : $_field['name']) . ", ";
                }
            }
            $export_columns[$_field['name']] = $_field;
            if ( isset($_field['_is_value_column']) && $_field['_is_value_column'] ) {
                $value_sources[] = $_field['name'];
            }
        }

        $output->write_array(array_map(function ($data) {
            return $data['value'];
        }, $export_columns));
        $write_data_empty = array();
        foreach (array_keys($export_columns) as $column) $write_data_empty[$column] = '';

        //echo '<pre>'; var_dump($main_source, $data_sources); echo '</pre>';
        foreach ( \common\helpers\Properties::get_properties_tree(( isset($filter['properties_id']) && $filter['properties_id']>0 )?$filter['properties_id']:0,'','',false) as $_prop_variant ) {
            $_prop_id = $_prop_variant['id'];

            $main_sql =
                "SELECT {$main_source['select']} properties_id " .
                "FROM " . TABLE_PROPERTIES . " " .
                "WHERE properties_id='{$_prop_id}' " .
                "";
            $query = tep_db_query($main_sql);
            while ($this->data = tep_db_fetch_array($query)) {
                foreach ($data_sources as $source_key => $source_data) {
                    if ($source_data['table']) {
                        $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                        if ($source_data['table'] == TABLE_PROPERTIES_DESCRIPTION) {
                            $data_sql .= "AND properties_id='{$this->data['properties_id']}' AND language_id='{$source_data['params'][0]}' ";
                        } else {
                            $data_sql .= "AND 1=0 ";
                        }
                        //echo $data_sql.'<hr>';
                        $data_sql_r = tep_db_query($data_sql);
                        if (tep_db_num_rows($data_sql_r) > 0) {
                            $_data = tep_db_fetch_array($data_sql_r);
                            $this->data = array_merge($this->data, $_data);
                        }
                    } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                        call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                    }
                }

                $write_data = $write_data_empty;


                foreach ($export_columns as $db_key => $export) {
                    if (isset($export['get']) && method_exists($this, $export['get'])) {
                        $write_data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['properties_id']));
                    }
                    $write_data[$db_key] = isset($this->data[$db_key]) ? $this->data[$db_key] : '';
                }
                // {{
                if (empty($this->data['key_field'])) continue;
                // }}
                if ( count($value_sources)==0 ) {
                    $output->write_array($write_data);
                }else{
                    $value_ids = array_keys($this->data['_values']);
                    if ( count($value_ids)==0 ) {
                        $output->write_array($write_data);
                    }else{
                        $value_id = current($value_ids);
                        do{
                            $values_data = $this->data['_values'][$value_id];
                            foreach( $value_sources as $value_column_name ) {
                                $_column_lang_id = $export_columns[$value_column_name]['_lang_id'];
                                if (!isset($values_data[$_column_lang_id])) continue;
                                $value_array = $values_data[$_column_lang_id];
                                $write_data[$value_column_name] = $value_array['values_text'];

                                if ( isset($write_data[$file_primary_column]) ) {
                                    $write_data[$file_primary_column] = $this->get_key_field($export_columns[$value_column_name], $this->data['properties_id'], $value_id);
                                }
                            }
                            $output->write_array($write_data);

                            $write_data = $write_data_empty;
                            $value_id = next($value_ids);
                        }while( $value_id!==false );
                    }
                }
                $output->write_array($write_data_empty);
            }
        }
    }

    public function importRow($data, Messages $message)
    {
        $this->buildSources(array_keys($data));

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;
        $property_name_sources = $this->property_name_sources;
        $value_sources = $this->value_sources;


        $this->data = $data;

        if (!array_key_exists($file_primary_column, $data)) {
            throw new EP\Exception('Primary key not found in file');
        }
        $file_primary_value = $data[$file_primary_column];
        list($properties_id, $values_id) = explode('_',$file_primary_value,2);
        $this->data['properties_id'] = $properties_id;
        if ( $this->last_properties_id ) {
            $this->data['properties_id'] = $this->last_properties_id;
        }


        $have_property_name = false;
        foreach( $property_name_sources as $prop_name_column ) {
            if ( isset( $this->data[$prop_name_column] ) && !empty($this->data[$prop_name_column]) ){
                $have_property_name = true;
                break;
            }
        }
        $have_property_value = false;
        foreach( $value_sources as $prop_value_column ) {
            if ( isset( $this->data[$prop_value_column] ) && !empty($this->data[$prop_value_column]) ){
                $have_property_value = true;
                break;
            }
        }

        if ( !$have_property_name && !$have_property_value ) {
            // empty line ?
            return false;
        }
        if ( $have_property_name ) {
            $update_data_array = array();
            foreach ($main_source['columns'] as $file_column => $db_column) {
                if (!array_key_exists($file_column, $data)) continue;
                if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['properties_id']));
                }
                $update_data_array[$db_column] = $this->data[$file_column];
            }

            if ( isset($update_data_array['properties_type']) ) $update_data_array['properties_type'] = strtolower(trim($update_data_array['properties_type']));
            if ( isset($update_data_array['properties_type']) && !in_array($update_data_array['properties_type'],array('text','number','interval','flag','file','category')) ) {
                $update_data_array['properties_type'] = 'text';
            }

            if ( (int)$properties_id>0 && tep_db_num_rows(tep_db_query("SELECT properties_id FROM ".TABLE_PROPERTIES." WHERE properties_id='".(int)$properties_id."'"))>0 ) {
                tep_db_perform(TABLE_PROPERTIES, $update_data_array, 'update', "properties_id='".(int)$properties_id."'");
                $this->last_properties_id = $properties_id;
            }else{
                $add_data = array(
                    'date_added'=>'now()'
                );
                if ( empty($update_data_array['properties_type']) ) {
                    $update_data_array['properties_type'] = 'text';
                }
                tep_db_perform(TABLE_PROPERTIES, array_merge($add_data,$update_data_array));
                $this->last_properties_id = tep_db_insert_id();
            }
            $this->data['properties_id'] = $this->last_properties_id;
            if ( !isset($this->clean_up[intval($this->data['properties_id'])]) ) {
                $this->clean_up[intval($this->data['properties_id'])] = array();
                $get_db_value_ids_r = tep_db_query("SELECT DISTINCT values_id FROM ".TABLE_PROPERTIES_VALUES." WHERE properties_id='".intval($this->data['properties_id'])."'");
                if ( tep_db_num_rows($get_db_value_ids_r)>0 ) {
                    while( $_value_data = tep_db_fetch_array($get_db_value_ids_r) ) {
                        $this->clean_up[intval($this->data['properties_id'])][ $_value_data['values_id'] ] = $_value_data['values_id'];
                    }
                }
            }
            $this->last_property_data = tep_db_fetch_array(tep_db_query(
                "SELECT * FROM ".TABLE_PROPERTIES." WHERE properties_id='".$this->last_properties_id."'"
            ));

            foreach ($data_sources as $source_key => $source_data) {
                if ($source_data['table']) {
                    $new_data = array();
                    foreach ($source_data['columns'] as $file_column => $db_column) {
                        if (!array_key_exists($file_column, $data)) continue;
                        if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                            call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['properties_id']));
                        }
                        $new_data[$db_column] = $this->data[$file_column];
                    }
                    if (count($new_data) == 0) continue;

                    $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                    if ($source_data['table'] == TABLE_PROPERTIES_DESCRIPTION) {
                        $update_pk = "properties_id='{$this->last_properties_id}' AND language_id='{$source_data['params'][0]}' ";
                        $insert_pk = array('properties_id' => $this->last_properties_id, 'language_id' => $source_data['params'][0]);
                        $data_sql .= "AND {$update_pk}";
                    } else {
                        continue;
                    }
                    //echo $data_sql.'<hr>';
                    $data_sql_r = tep_db_query($data_sql);
                    if (tep_db_num_rows($data_sql_r) > 0) {
                        //$_data = tep_db_fetch_array($data_sql_r);
                        tep_db_free_result($data_sql_r);
                        //echo '<pre>update rel '; var_dump($source_data['table'],$new_data,'update', $update_pk); echo '</pre>';
                        tep_db_perform($source_data['table'], $new_data, 'update', $update_pk);
                    } else {
                        //echo '<pre>insert rel '; var_dump($source_data['table'],array_merge($new_data,$insert_pk)); echo '</pre>';
                        tep_db_perform($source_data['table'], array_merge($new_data, $insert_pk));
                    }

                } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                    call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                    foreach ($source_data['columns'] as $file_column => $db_column) {
                        if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                            call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['properties_id']));
                        }
                    }
                }
            }

        }

        if( $this->last_properties_id && $have_property_value ){
            // process values
            $properties_type = $this->last_property_data['properties_type'];
            $prop_decimals = (int)$this->last_property_data['decimals'];
            if ( $properties_type=='flag' || $properties_type=='category' ) {
                // flag values in prop2products table
                // category w/o values
            }else{
                //'text','number','interval','file',
                if ( $values_id ) {
                    $check_values_id = tep_db_fetch_array(tep_db_query(
                        "SELECT COUNT(*) AS c FROM ".TABLE_PROPERTIES_VALUES." WHERE values_id='".(int)$values_id."' AND properties_id='".$this->last_properties_id."' "
                    ));
                    if ( $check_values_id['c']==0 ) $values_id = 0;
                }elseif( is_null($values_id) ) {
                    // insert value - empty key column
                    foreach( $value_sources as $value_field_name  ) {
                        $value = $this->data[$export_columns[$value_field_name]['name']];
                        if ( empty($value) ) continue;
                        $try_reuse_same_value_r = tep_db_query("SELECT values_id FROM ".TABLE_PROPERTIES_VALUES." WHERE properties_id='".$this->last_properties_id."' AND values_text='".tep_db_input(trim($value))."' AND language_id='".intval($export_columns[$value_field_name]['_lang_id'])."'");
                        if ( tep_db_num_rows($try_reuse_same_value_r)>0 ) {
                            $try_reuse_same_value = tep_db_fetch_array($try_reuse_same_value_r);
                            $values_id = $try_reuse_same_value['values_id'];
                            break;
                        }
                    }
                }

                if ( !$values_id ) {
                    // wrong values id from file - insert new
                    $max_value = tep_db_fetch_array(tep_db_query("SELECT MAX(values_id) AS current_max_id FROM " . TABLE_PROPERTIES_VALUES));
                    $values_id = intval($max_value['current_max_id'])+1;
                }

                foreach( $value_sources as $value_field_name  ) {
                    $value = $this->data[$export_columns[$value_field_name]['name']];

                    if ($properties_type == 'interval') {
                        list($from, $upto) = explode('-', trim($value));
                        $value_data = array(
                            'values_id' => $values_id,
                            'properties_id' => $this->last_properties_id,
                            'language_id' => $export_columns[$value_field_name]['_lang_id'],
                            'values_text' => trim($value),
                            'values_number' => (float)number_format(floatval(trim($from)), $prop_decimals, '.', ''),
                            'values_number_upto' => (float)number_format(floatval(trim($upto)), $prop_decimals, '.', ''),
                        );
                        /*tep_db_query(
                          "INSERT INTO " . TABLE_PROPERTIES_VALUES . " () ".
                          " SELECT '" . (int)$values_id . "', '" . (int)$properties_id . "', languages_id, '" . tep_db_input(trim($value)) . "', ".
                          "  '" . (float)number_format(trim($from), $prop_decimals, '.', '') . "', '" . (float)number_format(trim($upto), $prop_decimals, '.', '') . "' ".
                          " FROM ".TABLE_LANGUAGES
                        );*/
                    } elseif ($properties_type == 'number') {
                        $value_data = array(
                            'values_id' => $values_id,
                            'properties_id' => $this->last_properties_id,
                            'language_id' => $export_columns[$value_field_name]['_lang_id'],
                            'values_text' => trim($value),
                            'values_number' => (float)number_format(floatval(trim($value)), $prop_decimals, '.', ''),
                            'values_number_upto' => 0,
                        );
                        /*tep_db_query(
                          "INSERT INTO " . TABLE_PROPERTIES_VALUES . " (values_id, properties_id, language_id, values_text, values_number) ".
                          " SELECT '" . (int)$values_id . "', '" . (int)$properties_id . "', languages_id, '" . tep_db_input(trim($value)) . "', ".
                          "  '" . (float)number_format(trim($value), $prop_decimals, '.', '') . "' ".
                          " FROM ".TABLE_LANGUAGES
                        );*/
                    } else {
                        $value_data = array(
                            'values_id' => $values_id,
                            'properties_id' => $this->last_properties_id,
                            'language_id' => $export_columns[$value_field_name]['_lang_id'],
                            'values_text' => trim($value),
                            'values_number' => 0,
                            'values_number_upto' => 0,
                        );
                        /*tep_db_query(
                          "INSERT INTO " . TABLE_PROPERTIES_VALUES . " (values_id, properties_id, language_id, values_text) ".
                          " SELECT '" . (int)$values_id . "', '" . (int)$properties_id . "', languages_id, '" . tep_db_input(trim($value)) . "' ".
                          " FROM ".TABLE_LANGUAGES
                        );*/
                    }

                    tep_db_query("REPLACE INTO ".TABLE_PROPERTIES_VALUES." (".implode(',',array_keys($value_data)).") VALUES('".implode("','",array_map('tep_db_input',array_values($value_data)))."') ");

                    unset($this->clean_up[intval($this->last_properties_id)][ $values_id ]);
                }
            }
        }

    }

    public function postProcess(Messages $message)
    {

        foreach( $this->clean_up as $_clean_prop_id => $_remove_value_ids ) {
            if ( count($_remove_value_ids)==0 ) continue;

            tep_db_query("DELETE FROM ".TABLE_PROPERTIES_VALUES." WHERE properties_id='{$_clean_prop_id}' AND values_id IN('".implode("','",array_map('intval',$_remove_value_ids))."') ");
            tep_db_query("DELETE FROM ".TABLE_PROPERTIES_TO_PRODUCTS." WHERE properties_id='{$_clean_prop_id}' AND values_id IN('".implode("','",array_map('intval',$_remove_value_ids))."') ");
        }


        $message->info('Done');

        $this->EPtools->done('properties_settings_import');
    }


    function import(Formatter\FormatterInterface $input, EP\Messages $message)
    {

        while ($data = $input->read_array()) {
            $this->importRow($data, $message);
        }

    }

    protected function property_values(){
        if ( isset($this->data['_values']) ) return;
        $this->data['_values'] = array();
        $get_values_r = tep_db_query(
            "SELECT * ".
            "FROM ".TABLE_PROPERTIES_VALUES." ".
            "WHERE properties_id='".intval($this->data['properties_id'])."' ".
            "ORDER BY language_id "
        );
        if ( tep_db_num_rows($get_values_r)>0 ) {
            while( $get_value = tep_db_fetch_array($get_values_r) ) {
                $id = $get_value['values_id'];
                $_lang_id = $get_value['language_id'];
                if ( !isset($this->data['_values'][ $id ]) ) $this->data['_values'][ $id ] = array();
                $this->data['_values'][ $id ][ $_lang_id ] = array(
                    'values_text' => $get_value['values_text'],
                    'values_number' => $get_value['values_number'],
                    'values_number_upto' => $get_value['values_number_upto'],
                    'values_alt' => $get_value['values_alt'],
                );
            }
        }
        $this->data['_values_ids'] = array_keys($this->data['_values']);
        reset($this->data['_values']);
    }

    protected function get_values($field_info, $properties_id)
    {

    }

    protected function set_values($field_info, $properties_id)
    {

    }

    protected function set_units($field_info, $properties_id)
    {
        $value = trim($this->data[$field_info['name']]);
        $units_id = 0;

        static $prefetch = array();
        $key = $value;
        if ( !empty($value) ) {
            if (!isset($prefetch[$key])) {
                $get_id_r = tep_db_query("SELECT properties_units_id FROM " . TABLE_PROPERTIES_UNITS . " WHERE properties_units_title='" . tep_db_input($value) . "' LIMIT 1");
                if (tep_db_num_rows($get_id_r) > 0) {
                    $get_id = tep_db_fetch_array($get_id_r);
                    $prefetch[$key] = $get_id['properties_units_id'];
                    $units_id = $get_id['properties_units_id'];
                } else {
                    tep_db_perform(TABLE_PROPERTIES_UNITS, array('properties_units_title' => $value));
                    $units_id = tep_db_insert_id();
                    $prefetch[$key] = $units_id;
                }
            } else {
                $units_id = $prefetch[$key];
            }
        }
        $this->data[$field_info['name']] = $units_id;
        return $units_id;
    }

    protected function get_units($field_info, $properties_id)
    {
        $unit_id = $this->data[$field_info['name']];
        if ( !is_array($this->_data_units) ) {
            $this->_data_units = array();
            $get_all_r = tep_db_query("SELECT * FROM ".TABLE_PROPERTIES_UNITS);
            if ( tep_db_num_rows($get_all_r)>0 ) {
                while( $_data = tep_db_fetch_array($get_all_r) ) {
                    $this->_data_units[$_data['properties_units_id']] = $_data['properties_units_title'];
                }
            }
        }
        $unit = isset($this->_data_units[$unit_id])?$this->_data_units[$unit_id]:'';
        $this->data[$field_info['name']] = $unit;
        return $unit;
    }

    protected function tep_get_properties_full_path($properties_id)
    {
        return $this->prop_data_id[ $properties_id ]['properties_path'];
    }

    protected function get_flag_yes_no($field_info, $properties_id)
    {
        $value = $this->data[$field_info['name']];
        if ( (string)$value!=='' ) {
            $value = (!!$value?'Yes':'No');
            $this->data[$field_info['name']] = $value;
        }
        return $value;
    }

    protected function set_flag_yes_no($field_info, $properties_id)
    {
        $value = $this->data[$field_info['name']];
        if ( (string)$value!=='' ) {
            $_value = strtolower(trim($value));
            $value = ($_value==='1' || $_value=='true' || $_value=='yes' || $_value=='enabled')?1:0;
            $this->data[$field_info['name']] = $value;
        }
        return $value;
    }

}