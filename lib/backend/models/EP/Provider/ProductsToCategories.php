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
use common\helpers\Html;

class ProductsToCategories extends ProviderAbstract implements ImportInterface, ExportInterface
{
    protected $fields = array();
    protected $data = array();
    protected $EPtools;
    protected $_processed_pids = array();
    protected $import_folder;

    protected $entry_counter = 0;
    protected $_products_lookup = [];
    protected $_not_processed_product_categories = [];
    protected $file_primary_columns = [];
    protected $import_config_default = [
        'assign_mode' => 'replace',
        'create_category' => 'yes',
    ];

    protected $export_query;

    function init()
    {
        parent::init();
        $this->initFields();
        $this->EPtools = new EP\Tools();
    }

    protected function initFields()
    {

        $this->fields = array();
        $this->fields[] = array('name' => 'products_model', 'calculated' => true, 'value' => 'Products Model', 'is_key' => true,);
        $this->fields[] = array('name' => 'products_ean', 'calculated' => true, 'value' => 'EAN', 'is_key' => true);
        $this->fields[] = array('name' => 'products_asin', 'calculated' => true, 'value' => 'ASIN', 'is_key' => true);
        $this->fields[] = array('name' => 'products_isbn', 'calculated' => true, 'value' => 'ISBN', 'is_key' => true);
        $this->fields[] = array('name' => 'products_name', 'calculated' => true, 'value' => 'Products Name',);
        $this->fields[] = array('name' => 'sort_order', 'calculated' => true, 'value' => 'Product Sort Order',);
        $this->fields[] = array(
            'data_descriptor' => '@|linked_categories|0',
            'name' => '_categories_',
            'value' => 'Assigned Categories',
            'get' => 'get_category',
            'set' => 'set_category',
        );
    }

    public function importOptions()
    {
        $assign_mode = isset($this->import_config['assign_mode'])?$this->import_config['assign_mode']:$this->import_config_default['assign_mode'];
        $create_category = isset($this->import_config['create_category'])?$this->import_config['create_category']:$this->import_config_default['create_category'];

        return '
<div class="widget box box-no-shadow">
    <div class="widget-header"><h4>Import options</h4></div>
    <div class="widget-content">
        <div class="row form-group">
            <div class="col-md-6"><label>Assign categories mode</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[assign_mode]',$assign_mode, ['replace' => 'Replace from file', 'assign'=>'Append new, keep existing'],['class'=>'form-control']).'</div>
        </div>
        <div class="row form-group">
            <div class="col-md-6"><label>Create categories?</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[create_category]',$create_category, ['yes' => 'Yes', 'no'=>'No, skip'], ['class'=>'form-control']).'</div>
        </div>
    </div>
</div>
        ';
    }

    public function prepareExport($useColumns, $filter){
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
        $main_sql =
            "SELECT {$main_source['select']} p2c.categories_id, p2c.sort_order, p.products_id, p.products_model, p.products_ean, p.products_isbn, p.products_asin, pd.products_name " .
            "FROM ".TABLE_PRODUCTS_DESCRIPTION." pd, " . TABLE_PRODUCTS . " p " .
            " INNER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ON p2c.products_id=p.products_id ".
            "WHERE p.products_id=pd.products_id AND pd.language_id='".intval($this->languages_id)."' AND pd.platform_id='".intval(\common\classes\platform::defaultId())."' ".
            " {$filter_sql} ".
            "ORDER BY p.products_id ";

        $this->export_query = tep_db_query( $main_sql );
    }

    public function exportRow(){
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;
        
        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;
        
        foreach ( $data_sources as $source_key=>$source_data ) {
            if($source_data['init_function'] && method_exists($this,$source_data['init_function'])){
                call_user_func_array(array($this,$source_data['init_function']),$source_data['params']);
            }
        }

        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_id']));
            }
        }
        
        return $this->data;
    }
    
    function export(Formatter\FormatterInterface $output, $selected_fields, $filter)
    {
        $export_columns = array();

        $main_source = array(
            'select' => '',
        );
        $data_sources = array();
        foreach( $this->fields as $_field ) {
            // skip not configured here
            if ( is_array($selected_fields) && !in_array($_field['name'],$selected_fields) ) {
                continue;
            }
            if ( isset($_field['data_descriptor']) ) {
                if ( !isset($data_sources[$_field['data_descriptor']]) ) {
                    $data_descriptor = explode('|',$_field['data_descriptor']);
                    $data_sources[$_field['data_descriptor']] = array(
                        'select' => '',
                        'table' => $data_descriptor[0]=='%'?$data_descriptor[1]:false,
                        'init_function' => $data_descriptor[0]=='@'?$data_descriptor[1]:false,
                        'params' => array_slice($data_descriptor,2),
                    );
                }
                if ( isset($_field['calculated']) && $_field['calculated'] ) {

                }else {
                    $data_sources[$_field['data_descriptor']]['select'] .= (isset($_field['column_db']) ? "{$_field['column_db']} AS {$_field['name']}" : $_field['name']) . ", ";
                }
            }else{
                if ( isset($_field['calculated']) && $_field['calculated'] ) {

                }else {
                    $main_source['select'] .= (isset($_field['column_db']) ? "{$_field['column_db']} AS {$_field['name']}" : $_field['name']) . ", ";
                }
            }
            $export_columns[ $_field['name'] ] = $_field;

        }

        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND p.products_id IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
        }
        $main_sql =
            "SELECT {$main_source['select']} p2c.categories_id, p.products_id, p.products_model, p.products_ean, p.products_isbn, p.products_asin, pd.products_name " .
            "FROM ".TABLE_PRODUCTS_DESCRIPTION." pd, " . TABLE_PRODUCTS . " p " .
            " INNER JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ON p2c.products_id=p.products_id ".
            "WHERE p.products_id=pd.products_id AND pd.language_id='".intval($this->languages_id)."' AND pd.platform_id='".intval(\common\classes\platform::defaultId())."' ".
            " {$filter_sql} ".
            "ORDER BY p.products_id ";
        //echo $main_sql;

        $output->write_array(array_map(function($data){ return $data['value']; },$export_columns));

        $write_data_empty = array();
        foreach( array_keys($export_columns) as $column ) $write_data_empty[$column] = '';

        $query = tep_db_query( $main_sql );
        while( $this->data = tep_db_fetch_array( $query ) ) {

            foreach ( $data_sources as $source_key=>$source_data ) {
                if($source_data['init_function'] && method_exists($this,$source_data['init_function'])){
                    call_user_func_array(array($this,$source_data['init_function']),$source_data['params']);
                }
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

    protected function buildSources($useColumns)
    {
        if (parent::buildSources($useColumns)){
            $this->file_primary_columns = [];
            foreach ($this->fields as $_field) {
                if (isset($_field['is_key']) && $_field['is_key'] === true ) {
                    $this->file_primary_columns[] = (isset($_field['column_db']) ? $_field['column_db'] : $_field['name']);
                }
            }
            return true;
        }
        return false;
    }

    public function importRow($data, Messages $message)
    {

        $this->buildSources( array_keys($data) );

        $this->data = $data;

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_columns = $this->file_primary_columns;

        if ( $this->entry_counter==0 ){
            if ( count(array_intersect($file_primary_columns, array_keys($this->data))) == 0 ) {
                $show_key = '';
                foreach ($file_primary_columns as $_primary_column) {
                    if (!empty($show_key)) $show_key .= ', ';
                    $show_key .= '"' . $export_columns[$_primary_column]['value'] . '"';
                }
                throw new EP\Exception('Primary key(s) not found in file. Expect one of: ' . $show_key);

            }

            if ( !array_key_exists('_categories_',$this->data) ) {
                throw new EP\Exception('Missing categories column - "'.$export_columns['_categories_']['value'].'"');
            }
        }

        /*
        if (!array_key_exists($file_primary_column, $data)) {
        }*/
        $file_primary_value = [];

        foreach($file_primary_columns as $key => $file_primary_column_key){
            if ( isset($this->data[$file_primary_column_key]) && !empty($this->data[$file_primary_column_key])){
                $file_primary_value[$file_primary_column_key] = $this->data[$file_primary_column_key];
            }
        }

        if ( empty($file_primary_value) || count($file_primary_value) == 0) {
            $message->info('Key fields are empty. Row skipped');
            return false;
        }

        $_result = $this->_checkEmptyKeys($file_primary_value);

        if ( is_array($_result) && isset($_result['primary_value'])){
            $file_primary_value = $_result['primary_value'];
        }else {
            $message->info('Lost primary value. Row skipped');
            return false;
        }

        if (is_array($_result) && !isset($this->_products_lookup[$_result['primary_value']]) ) {
            $found_rows = $_result['found_rows'];
            $this->_products_lookup[$file_primary_value] = array(
                'found_rows' => (int)$_result['found_rows'],
                'data' => $_result['data'],
            );

            if ($found_rows > 1) {
                $message->info('Product '. $_result['key']. ' "'.$file_primary_value.'"  not unique - found '.$found_rows.' rows. Skipped');
            }elseif ($found_rows == 0) {
                $message->info('Product '. $_result['key']. ' "'.$file_primary_value.'" not found. Skipped');
            }else{
                $products_id = $this->_products_lookup[$file_primary_value]['data']['products_id'];

                if ( !isset($this->_not_processed_product_categories[$products_id]) ) {
                    $this->_not_processed_product_categories[$products_id]= array();
                    $get_assigned_categories_r = tep_db_query("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".(int)$products_id."'");
                    while( $_assigned_category = tep_db_fetch_array($get_assigned_categories_r) ) {
                        $this->_not_processed_product_categories[$products_id][$_assigned_category['categories_id']] = $_assigned_category['categories_id'];
                    }
                    //$entry_counter++;
                }
            }
        }

        $found_rows = (isset($this->_products_lookup[$file_primary_value]['found_rows']) ? $this->_products_lookup[$file_primary_value]['found_rows'] : 0);

        if ($found_rows > 1) {
            // error data not unique
            //$message->info('Product "'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            return false;
        }elseif ($found_rows == 0) {
            // dummy
            //$message->info('Product "'.$file_primary_value.'" not found. Skipped');
            return false;
        }else{
            $db_main_data = $this->_products_lookup[$file_primary_value]['data'];
            $products_id = $db_main_data['products_id'];
        }

        $allowCreateCategory = 'no'!=(isset($this->import_config['create_category'])?$this->import_config['create_category']:$this->import_config_default['create_category']);

        $this->data['products_id'] = $products_id;

        $import_category_path = $this->data['_categories_'];
        if ( trim($import_category_path)=='' ) {
            $category_id = 0;
        }else {
            $category_id = $this->EPtools->tep_get_categories_by_name($import_category_path, $message, $allowCreateCategory);
            if ( !$allowCreateCategory && !is_numeric($category_id) && !empty($import_category_path) ) {
                $message->info('Category "'.$import_category_path.'" for product "'.$file_primary_value.'" - not found. Skipped');
            }
        }

        if ( is_numeric($category_id) ) {
            unset($this->_not_processed_product_categories[$products_id][$category_id]);

            if ( isset($this->data['sort_order']) ) {
                tep_db_query(
                    "INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " ".
                    "  (products_id, categories_id, sort_order) ".
                    " VALUES".
                    "  ('" . (int)$products_id . "', '" . (int)$category_id . "','".intval($this->data['sort_order'])."') ".
                    " ON DUPLICATE KEY UPDATE sort_order='".intval($this->data['sort_order'])."' "
                );
            }else {
                tep_db_query(
                    "INSERT IGNORE INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) VALUES('" . (int)$products_id . "', '" . (int)$category_id . "')"
                );
            }
            $this->entry_counter++;
        }

        $this->_processed_pids[intval($this->data['products_id'])] = intval($this->data['products_id']);

        return true;
    }

    public function postProcess(Messages $message)
    {
        $assign_mode = isset($this->import_config['assign_mode'])?$this->import_config['assign_mode']:$this->import_config_default['assign_mode'];

        if ( $assign_mode=='replace' ) {
            foreach ($this->_not_processed_product_categories as $_clean_pid => $_clean_categories) {
                if (count($_clean_categories) == 0) continue;

                tep_db_query("DELETE FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id='{$_clean_pid}' AND categories_id IN('" . implode("','", $_clean_categories) . "') ");
                $check_ref = tep_db_fetch_array(tep_db_query(
                    "SELECT COUNT(*) AS assigned_count FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " WHERE products_id='{$_clean_pid}' "
                ));
                if ($check_ref['assigned_count'] == 0) {
                    tep_db_perform(TABLE_PRODUCTS_TO_CATEGORIES, array(
                        'products_id' => $_clean_pid,
                        'categories_id' => 0,
                    ));
                }
            }
        }

        $message->info('Processed '.$this->entry_counter.' products');

        $message->info('Done');

        $this->EPtools->done('products_to_categories_import');
    }

    function import(Formatter\FormatterInterface $input, EP\Messages $message)
    {

        $this->entry_counter = 0;
        $this->_products_lookup = array();
        $this->_not_processed_product_categories = array();

        while ($data = $input->read_array()) {
            if ($this->importRow($data, $message)){
                $this->entry_counter++;
            }
        }

        $this->postProcess($message);

    }

    private function _checkEmptyKeys(array $primary_value){
        foreach($primary_value as $field => $value){
            $get_main_data_r = tep_db_query(
                "SELECT products_id FROM " . TABLE_PRODUCTS . " WHERE {$field} = '" . tep_db_input($value) . "'"
            );
            if (tep_db_num_rows($get_main_data_r)>0){
                $found_rows = tep_db_num_rows($get_main_data_r);
                $ex = explode("_", $field);
                return [
                    'primary_value' => $value,
                    'found_rows' => $found_rows,
                    'key' => $ex[1],
                    'data' => $found_rows>0?tep_db_fetch_array($get_main_data_r):false,
                ];
            }
        }
        return false;
    }

    function linked_categories($link){
        return;
        if ( !isset($this->data['assigned_categories']) ) {
            $this->data['assigned_categories'] = array();
            $this->data['assigned_categories_ids'] = array();
            $get_categories_r = tep_db_query("SELECT categories_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE products_id='".$this->data['products_id']."' ORDER BY categories_id");
            while( $get_category = tep_db_fetch_array($get_categories_r) ) {
                $this->data['assigned_categories_ids'][] = (int)$get_category['categories_id'];
                $this->data['assigned_categories'][] = $this->EPtools->tep_get_categories_full_path((int)$get_category['categories_id']);
            }
            $this->data['assign_categories_ids'] = array();
        }
    }

    function get_category($field_data){
        $this->data['_categories_'] = $this->EPtools->tep_get_categories_full_path((int)$this->data['categories_id']);
        return $this->data['_categories_'];
        $idx = intval(str_replace('_categories_','', $field_data['name']));
        $this->data[$field_data['name']] = isset($this->data['assigned_categories'][$idx])?$this->data['assigned_categories'][$idx]:'';
        return $this->data[$field_data['name']];
    }

    function set_category($field_data, $products_id){
        $import_category_path = $this->data[$field_data['name']];
        $category_id = $this->EPtools->tep_get_categories_by_name($import_category_path);
        if ( is_numeric($category_id) ) {
            $this->data['assign_categories_ids'] = $category_id;
            tep_db_query("REPLACE INTO ".TABLE_PRODUCTS_TO_CATEGORIES." (products_id, categories_id) VALUES('".(int)$products_id."', '".(int)$category_id."')");
        }
        return ;
    }

}