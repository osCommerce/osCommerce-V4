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
use common\helpers\Seo;

class Properties extends ProviderAbstract implements ImportInterface, ExportInterface
{
    protected $fields = array();
    protected $data = array();
    protected $EPtools;

    protected $prop_data_id = array();
    protected $prop_data_path = array();
    protected $prop_values = array();
    protected $_language_id;

    protected $last_product_lookup = [];
    protected $updated_product_ids = [];
    protected $export_query;

    function init()
    {
        parent::init();
        $this->_language_id = intval($this->languages_id);
        $this->build_prop_tree();
        $this->initFields();
        $this->EPtools = new EP\Tools();
    }

    protected function initFields(){
        $this->fields = array();
        $this->fields[] = array( 'name' => 'products_model', 'calculated'=>true, 'value' => 'Products Model', 'is_key'=>true,);
        $this->fields[] = array( 'name' => 'products_name', 'calculated'=>true, 'value' => 'Products Name', );
        $this->fields[] = array( 'name' => 'properties_type', 'calculated'=>true, 'value' => 'Properties Type', );
        $this->fields[] = array( 'name' => 'properties_id', 'calculated'=>true, 'value' => 'Categories Name;Property Name', );
        $this->fields[] = array( 'name' => 'value', 'calculated'=>true, 'value' => 'Value', );
        $this->fields[] = array( 'name' => 'extra_value', 'calculated'=>true, 'value' => 'Extra Value', );
    }

    function export(Formatter\FormatterInterface $output, $selected_fields, $filter)
    {
        $export_columns = array();

        $main_source = array(
            'select' => '',
        );
        $data_sources = array();
        foreach ($this->fields as $_field) {
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

        }

        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND p.products_id IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
        }

        //echo '<pre>'; var_dump($export_columns,$data_sources); echo '</pre>';
        $main_sql =
            "SELECT p.products_model, p.products_id, pp.properties_type, p2p.properties_id, p2p.values_id, p2p.values_flag, p2p.extra_value " .
            "FROM ".TABLE_PRODUCTS." p ".
            " INNER JOIN " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p ON p2p.products_id=p.products_id " .
            " INNER JOIN " . TABLE_PROPERTIES . " pp ON pp.properties_id=p2p.properties_id " .
            " INNER JOIN " . TABLE_PROPERTIES_DESCRIPTION . " ppd ON ppd.properties_id=p2p.properties_id AND ppd.language_id='".$this->_language_id."' " .
            "WHERE 1 {$filter_sql} ".
            "ORDER BY p.products_id, pp.sort_order, ppd.properties_name ";

        $output->write_array(array_map(function ($data) {
            return $data['value'];
        }, $export_columns));
        $write_data_empty = array();
        foreach (array_keys($export_columns) as $column) $write_data_empty[$column] = '';

        //echo '<pre>'; var_dump($main_source, $data_sources); echo '</pre>';
        $query = tep_db_query($main_sql);
        while ($this->data = tep_db_fetch_array($query)) {
            $this->data['properties_id'] = $this->prop_data_id[ $this->data['properties_id'] ]['properties_path'];
            $this->data['products_name'] = \common\helpers\Product::get_products_name($this->data['products_id'],$this->_language_id);
            if ( $this->data['properties_type']=='flag' ) {
                $this->data['value'] = (int)$this->data['values_flag'];
            }else{
                $value_data = $this->get_property_value( $this->data['values_id'] );
                $this->data['value'] = $value_data['value'];
            }
            $write_data = $write_data_empty;

            foreach( $export_columns as $db_key=>$export ) {
                if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                    $write_data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_id']));
                }
                $write_data[$db_key] = isset($this->data[$db_key])?$this->data[$db_key]:'';
            }

            $output->write_array($write_data);
        }
    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);
        
        $main_source = $this->main_source;

        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['products_id']) && is_array($filter['products_id']) && count($filter['products_id'])>0 ) {
                $filter_sql .= "AND p.products_id IN ('".implode("','", array_map('intval',$filter['products_id']))."') ";
            }
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND p.products_id IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
        }

        //echo '<pre>'; var_dump($export_columns,$data_sources); echo '</pre>';
        $main_sql =
            "SELECT p.products_model, p.products_id, pp.properties_type, p2p.properties_id, p2p.values_id, p2p.values_flag, p2p.extra_value " .
            "FROM ".TABLE_PRODUCTS." p ".
            " INNER JOIN " . TABLE_PROPERTIES_TO_PRODUCTS . " p2p ON p2p.products_id=p.products_id " .
            " INNER JOIN " . TABLE_PROPERTIES . " pp ON pp.properties_id=p2p.properties_id " .
            " INNER JOIN " . TABLE_PROPERTIES_DESCRIPTION . " ppd ON ppd.properties_id=p2p.properties_id AND ppd.language_id='".$this->_language_id."' " .
            "WHERE 1 {$filter_sql} ".
            "ORDER BY p.products_id, pp.sort_order, ppd.properties_name ";

        $this->export_query = tep_db_query( $main_sql );
    }
    
    public function exportRow()
    {
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;
        
        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;
        
        $this->data['properties_id'] = $this->prop_data_id[ $this->data['properties_id'] ]['properties_path'];
        $this->data['products_name'] = \common\helpers\Product::get_products_name($this->data['products_id'],$this->_language_id);
        if ( $this->data['properties_type']=='flag' ) {
            $this->data['value'] = (int)$this->data['values_flag'];
        }else{
            $value_data = $this->get_property_value( $this->data['values_id'] );
            $this->data['value'] = $value_data['value'];
        }

        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_id']));
            }
        }
        
        return $this->data;        
    }

    public function importRow($data, Messages $message)
    {
        $this->buildSources( array_keys($data) );

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;

        $this->data = $data;

        static $check_required_columns = true; //??

        if ( $check_required_columns ) {
            $error = false;
            foreach( array('products_model', 'properties_id', 'value') as $required_file_column ) {
                if (!array_key_exists($required_file_column, $this->data)) {
                    $message->info('Required column "'.$export_columns[$required_file_column]['value'].'" not found in file');
                }
            }
            if ( $error ) {
                return;
            }
            $check_required_columns = false;
        }

        $file_primary_value = $this->data[$file_primary_column];
        if ( empty($file_primary_value) ) {
            $message->info('Empty "'.$export_columns[$file_primary_column]['value'].'" column. Row skipped');
            return false;
        }

        if ( !isset($this->last_product_lookup[$file_primary_value]) ) {
            $get_main_data_r = tep_db_query(
                "SELECT products_id FROM " . TABLE_PRODUCTS . " WHERE products_model='" . tep_db_input($file_primary_value) . "'"
            );
            $found_rows = tep_db_num_rows($get_main_data_r);
            $this->last_product_lookup = array(
                $file_primary_value => array(
                    'found_rows' => $found_rows,
                    'data' => $found_rows>0?tep_db_fetch_array($get_main_data_r):false,
                ),
            );
            if ($found_rows > 1) {
                $message->info('Product "'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            }elseif ($found_rows == 0) {
                $message->info('Product "'.$file_primary_value.'" not found. Skipped');
            }
            //$entry_counter++;
        }
        $found_rows = $this->last_product_lookup[$file_primary_value]['found_rows'];
        if ($found_rows > 1) {
            // error data not unique
            //$message->info('Product "'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            return false;
        }elseif ($found_rows == 0) {
            // dummy
            //$message->info('Product "'.$file_primary_value.'" not found. Skipped');
            return false;
        }else{
            $db_main_data = $this->last_product_lookup[$file_primary_value]['data'];
            $products_id = (int)$db_main_data['products_id'];
        }
        if ( !isset($this->updated_product_ids[$products_id]) ) {
            // replace properties
            tep_db_query("DELETE FROM ".TABLE_PROPERTIES_TO_PRODUCTS." WHERE products_id='".(int)$products_id."'");
            $this->updated_product_ids[$products_id] = $products_id;
        }
        $this->data['products_id'] = $products_id;
        $this->data['properties_id'] = $this->get_property_by_path($message, $this->data['properties_id']);
        $this->data['values_id'] = 0;
        $this->data['values_flag'] = 'null';
        //$this->data['extra_value'] = '';

        $properties_type = $this->prop_data_id[$this->data['properties_id'] ?? null]['properties_type'] ?? null;
        if ( $properties_type=='flag' ) {
            $this->data['values_flag'] = (int)$this->data['value'];
        }else {
            if ( trim($this->data['value'])==='' ) {
                $message->info('Empty value for "'.$file_primary_value.'" "'.$this->prop_data_id[$this->data['properties_id']]['properties_path'].'"');
                return false;
            }else {
                $this->data['values_id'] = $this->get_property_value_id($message, $this->data['properties_id'], $this->data['value']);
            }
        }
        tep_db_query(
            "REPLACE INTO ".TABLE_PROPERTIES_TO_PRODUCTS." (products_id, properties_id, values_id, values_flag, extra_value) ".
            "VALUES ('{$this->data['products_id']}', '{$this->data['properties_id']}', '{$this->data['values_id']}', {$this->data['values_flag']}, {$this->data['extra_value']})"
        );

        return true;
    }

    public function postProcess(Messages $message)
    {
        \common\helpers\ProductsGroupSortCache::update();

        $message->info('Done');

        $this->EPtools->done('properties_import');
    }

    function import(Formatter\FormatterInterface $input, EP\Messages $message)
    {
        $entry_counter = 0;
        $this->last_product_lookup = array();
        $this->updated_product_ids = array();
        //$check_required_columns = true;
        while ($data = $input->read_array()) {
            if ( $this->importRow($data) ) {

            }
        }
        $this->postProcess($message);
    }

    protected function build_prop_tree($parent_id = '0', $prop_path = '')
    {
        $categories_query = tep_db_query(
            "select p.properties_id, p.properties_type, p.decimals, p.parent_id, pd.properties_name, p.decimals ".
            "from " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_DESCRIPTION . " pd ".
            "where p.parent_id = '" . (int)$parent_id . "' and p.properties_id = pd.properties_id and pd.language_id = '" . (int)$this->_language_id . "' ".
            "order by (p.properties_type = 'category'), p.sort_order, pd.properties_name"
        );
        while ($categories = tep_db_fetch_array($categories_query)) {
            $temp_prop_path = $prop_path;
            $temp_prop_path .= (empty($temp_prop_path)?'':';').$categories['properties_name'];
            $prop_data = array(
                'properties_id' => $categories['properties_id'],
                'properties_name' => $categories['properties_name'],
                'properties_type' => $categories['properties_type'],
                'decimals' => $categories['decimals'],
                'parent_id' => $parent_id,
                'properties_path' => $temp_prop_path,
            );
            $this->prop_data_id[$categories['properties_id']] = $prop_data;
            $this->prop_data_path[$temp_prop_path] = $prop_data;

            $this->build_prop_tree($categories['properties_id'], $temp_prop_path);
        }
    }

    protected function get_property_value($values_id) {
        if ( !isset($this->prop_values[ (int)$values_id ]) ) {
            $prop_value = array();
            $get_value_r = tep_db_query(
                "SELECT properties_id, values_text, values_number, values_number_upto ".
                "FROM ".TABLE_PROPERTIES_VALUES." ".
                "WHERE values_id='".(int)$values_id."' AND language_id='".$this->_language_id."' "
            );
            if ( tep_db_num_rows($get_value_r)>0 ) {
                $prop_value = tep_db_fetch_array($get_value_r);
                $prop_type = $this->prop_data_id[$prop_value['properties_id']]['properties_type'];
                $prop_decimals = $this->prop_data_id[$prop_value['properties_id']]['decimals'];

                if ( $prop_type=='interval' ) {
                    $prop_value['value'] = number_format($prop_value['values_number'], $prop_decimals,'.','') . ' - ' . number_format($prop_value['values_number_upto'], $prop_decimals,'.','');
                }elseif( $prop_type=='number' ){
                    $prop_value['value'] = number_format($prop_value['values_number'], $prop_decimals,'.','');
                }else{
                    $prop_value['value'] = $prop_value['values_text'];
                }
            }

            $this->prop_values[ (int)$values_id ] = $prop_value;
        }
        return $this->prop_values[ (int)$values_id ];
    }

    protected function get_property_by_path(EP\Messages $message, $path){
        $path = preg_replace('/;\s*$/','',$path);
        if ( !isset($this->prop_data_path[$path]) ) {
            $path_arr = explode(';', $path);
            $walk_path = '';
            $walk_property_id = 0;
            foreach( $path_arr as $idx=>$path_chunk ) {
                $walk_path_check = $walk_path.(empty($walk_path)?'':';').$path_chunk;
                if ( isset($this->prop_data_path[$walk_path_check]) ) {
                    $walk_property_id = $this->prop_data_path[$walk_path_check]['properties_id'];
                }else {
                    // not found in array - db lookup
                    $possible_names = array($path_chunk);
                    $possible_names[] = rtrim($path_chunk);
                    $possible_names[] = trim($path_chunk);
                    $possible_names = array_unique($possible_names);

                    $lookup_r = tep_db_query(
                        "SELECT p.properties_id, p.properties_type, p.decimals, p.parent_id, pd.properties_name, p.decimals ".
                        "FROM " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_DESCRIPTION . " pd ".
                        "WHERE p.parent_id = '" . (int)$walk_property_id . "' AND p.properties_id = pd.properties_id AND pd.language_id = '" . (int)$this->_language_id . "' ".
                        " AND pd.properties_name IN('".implode("','",array_map('tep_db_input',$possible_names))."') ".
                        "ORDER BY IF(pd.properties_name='".tep_db_input($path_chunk)."',0,1) ".
                        "LIMIT 1"
                    );
                    if ( tep_db_num_rows($lookup_r)>0 ) {
                        $categories = tep_db_fetch_array($lookup_r);
                        $prop_data = array(
                            'properties_id' => $categories['properties_id'],
                            'properties_name' => $categories['properties_name'],
                            'properties_type' => $categories['properties_type'],
                            'decimals' => $categories['decimals'],
                            'parent_id' => $categories['parent_id'],
                            'properties_path' => $walk_path_check,
                        );
                        $this->prop_data_path[$walk_path_check] = $prop_data;
                        $walk_property_id = $categories['properties_id'];
                    }else{
                        $parent_id = $walk_property_id;
                        $new_properties_type = 'text';
                        if ( isset($this->data['properties_type']) && in_array($this->data['properties_type'],array('text','number','interval','flag','file')) ){
                            $new_properties_type = $this->data['properties_type'];
                        }
                        if (count($path_arr)>1 && ($idx!=count($path_arr)-1) ) {
                            $new_properties_type = 'category';
                        }

                        tep_db_perform(TABLE_PROPERTIES, array(
                            'parent_id' => $parent_id,
                            'properties_type' => $new_properties_type,
                            'date_added' => 'now()',
                        ));
                        $walk_property_id = tep_db_insert_id();
                        $property_seo_name = Seo::makePropertySlug([
                            'properties_id' => (int)$walk_property_id,
                            'properties_name' => trim($path_chunk),
                        ]);
                        tep_db_query(
                            "INSERT INTO ".TABLE_PROPERTIES_DESCRIPTION." (properties_id, language_id, properties_name, properties_seo_page_name) ".
                            "SELECT '{$walk_property_id}', languages_id, '".tep_db_input(trim($path_chunk))."', '".tep_db_input($property_seo_name)."' FROM ".TABLE_LANGUAGES." "
                        );
                        $prop_data = array(
                            'properties_id' => $walk_property_id,
                            'properties_name' => trim($path_chunk),
                            'properties_type' => $new_properties_type,
                            'decimals' => 2,
                            'parent_id' => $parent_id,
                            'properties_path' => $walk_path_check,
                        );
                        $this->prop_data_path[$walk_path_check] = $prop_data;
                        $message->info('Added new property "'.$path_chunk.'"');
                    }
                }

                $walk_path = $walk_path_check;
            }
        }
        return $this->prop_data_path[$path]['properties_id'];
    }

    protected function get_property_value_id(EP\Messages $message, $properties_id, $value)
    {
        $type = $this->prop_data_id[$properties_id]['properties_type'] ?? null;
        $prop_decimals = $this->prop_data_id[$properties_id]['decimals'] ?? null;

        if ($type == 'interval') {
            list($from, $upto) = explode('-', trim($value));
            $result_values_id = tep_db_query(
                "SELECT values_id ".
                "FROM " . TABLE_PROPERTIES_VALUES . " ".
                "WHERE properties_id = '" . (int)$properties_id . "' AND language_id = '" . (int)$this->_language_id . "' ".
                " AND ABS(ROUND(values_number, " . (int)$prop_decimals . ") - " . (float)number_format(trim($from), $prop_decimals, '.', '') . ") < " . (float)(pow(10, -($prop_decimals + 1))) . " ".
                " AND ABS(ROUND(values_number_upto, " . (int)$prop_decimals . ") - " . (float)number_format(trim($upto), $prop_decimals, '.', '') . ") < " . (float)(pow(10, -($prop_decimals + 1)))." "
            );
        } elseif ($type == 'number') {
            $result_values_id = tep_db_query(
                "SELECT values_id ".
                "FROM " . TABLE_PROPERTIES_VALUES . " ".
                "WHERE properties_id = '" . (int)$properties_id . "' AND language_id = '" . (int)$this->_language_id . "' ".
                " AND ABS(ROUND(values_number, " . (int)$prop_decimals . ") - " . (float)number_format(trim($value), $prop_decimals, '.', '') . ") < " . (float)(pow(10, -($prop_decimals + 1)))." "
            );
        } else {
            $result_values_id = tep_db_query(
                "SELECT values_id ".
                "FROM " . TABLE_PROPERTIES_VALUES . " ".
                "WHERE properties_id = '" . (int)$properties_id . "' AND language_id = '" . (int)$this->_language_id. "' ".
                " AND values_text = '" . tep_db_input(trim($value)) . "'"
            );
        }

        if (tep_db_num_rows($result_values_id) > 0) {
            $array_values_id = tep_db_fetch_array($result_values_id);
            $values_id = $array_values_id['values_id'];
        } else {
            $max_value = tep_db_fetch_array(tep_db_query("SELECT MAX(values_id) AS current_max_id FROM " . TABLE_PROPERTIES_VALUES));
            $values_id = intval($max_value['current_max_id'])+1;

            if ($type == 'interval') {
                list($from, $upto) = explode('-', trim($value));
                tep_db_query(
                    "INSERT INTO " . TABLE_PROPERTIES_VALUES . " (values_id, properties_id, language_id, values_text, values_number, values_number_upto, values_seo_page_name) ".
                    " SELECT '" . (int)$values_id . "', '" . (int)$properties_id . "', languages_id, '" . tep_db_input(trim($value)) . "', ".
                    "  '" . (float)number_format(trim($from), $prop_decimals, '.', '') . "', '" . (float)number_format(trim($upto), $prop_decimals, '.', '') . "', '".tep_db_input(Seo::makeSlug(trim($value)))."' ".
                    " FROM ".TABLE_LANGUAGES
                );
            } elseif ($type == 'number') {
                tep_db_query(
                    "INSERT INTO " . TABLE_PROPERTIES_VALUES . " (values_id, properties_id, language_id, values_text, values_number, values_seo_page_name) ".
                    " SELECT '" . (int)$values_id . "', '" . (int)$properties_id . "', languages_id, '" . tep_db_input(trim($value)) . "', ".
                    "  '" . (float)number_format(trim($value), $prop_decimals, '.', '') . "', '".tep_db_input(Seo::makeSlug(trim($value)))."' ".
                    " FROM ".TABLE_LANGUAGES
                );
            } else {
                tep_db_query(
                    "INSERT INTO " . TABLE_PROPERTIES_VALUES . " (values_id, properties_id, language_id, values_text, values_seo_page_name) ".
                    " SELECT '" . (int)$values_id . "', '" . (int)$properties_id . "', languages_id, '" . tep_db_input(trim($value)) . "', '".tep_db_input(Seo::makeSlug(trim($value)))."' ".
                    " FROM ".TABLE_LANGUAGES
                );
            }
            $message->info('Added new property value "'.$value.'"');
        }

        return $values_id;
    }

}