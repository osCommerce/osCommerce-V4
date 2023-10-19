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
use common\models\ProductsAttributes;
use yii\helpers\ArrayHelper;

class Attributes extends ProviderAbstract implements ImportInterface, ExportInterface
{
    protected $fields = array();
    protected $data = array();
    protected $EPtools;
    protected $_processed_pids = array();
    protected $_not_updated_option_values = array();

    protected $entry_counter = 0;
    protected $last_product_lookup = array();
    protected $export_query;

    function init()
    {
        parent::init();
        $this->initFields();
        $this->EPtools = new EP\Tools();
    }

    protected function initFields(){
        $currencies = \Yii::$container->get('currencies');

        $this->fields = array();
        $this->fields[] = array( 'name' => 'products_model', 'calculated'=>true, 'value' => 'Products Model', 'is_key'=>true, 'get'=>'get_product_model' );
        $this->fields[] = array( 'name' => '_price_info', 'calculated'=>true, 'value' => 'Price (info!)', 'get'=>'get_price_info' );
        foreach( \common\helpers\Language::get_languages() as $_lang ) {
            $data_descriptor = '%|' . TABLE_PRODUCTS_OPTIONS . '|' . $_lang['id'];
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_options_name',
                'required' => true,
                'pre_lookup' => 'options_id',
                'name' => 'products_options_name_' . $_lang['code'],
                'value' => 'Option Name ' . $_lang['code'],
            );

            $data_descriptor = '%|' . TABLE_PRODUCTS_OPTIONS_VALUES . '|' . $_lang['id'];
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'products_options_values_name',
                'required' => true,
                'pre_lookup' => 'options_values_id',
                'name' => 'products_options_values_name_' . $_lang['code'],
                'value' => 'Option Value ' . $_lang['code'],
            );
        }
        $this->fields[] = array( 'name' => 'price_prefix', 'value' => 'Price Prefix' );
        if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
            foreach ($currencies->currencies as $key => $value) {
                $data_descriptor = '%|' . TABLE_PRODUCTS_ATTRIBUTES_PRICES . '|' . $value['id'] . '|0';
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'attributes_group_price',
                    'name' => 'attributes_group_price_' . $value['id'] . '_0',
                    'value' => 'Option Price ' . $key,
                    'get' => 'get_products_attributes_price', 'set' => 'set_products_attributes_price',
                    'type' => 'numeric',
                    'isMain' => DEFAULT_CURRENCY==$key,
                );
                /*
                $this->fields[] = array(
                  'data_descriptor' => $data_descriptor,
                  'column_db' => 'attributes_group_discount_price',
                  'name' => 'attributes_group_discount_price_' . $value['id'] . '_0',
                  'value' => 'Option Discount Price ' . $key,
                  'get' => 'get_products_attributes_discount_price', 'set' => 'set_products_attributes_discount_price',
                  'type' => 'numeric'
                );
                */
                foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                    $data_descriptor = '%|'.TABLE_PRODUCTS_ATTRIBUTES_PRICES.'|'.$value['id'].'|'.$groups_data['groups_id'];
                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'attributes_group_price',
                        'name' => 'attributes_group_price_' . $value['id'] . '_' . $groups_data['groups_id'],
                        'value' => 'Option Price ' . $key . ' ' . $groups_data['groups_name'],
                        'get' => 'get_products_attributes_price', 'set' => 'set_products_attributes_price',
                        'type' => 'numeric'
                    );
                    /*
                    $this->fields[] = array(
                      'data_descriptor' => $data_descriptor,
                      'column_db' => 'attributes_group_discount_price',
                      'name' => 'attributes_group_discount_price_' . $value['id'] . '_' . $groups_data['groups_id'],
                      'value' => 'Option Discount Price ' . $key . ' ' . $groups_data['groups_name'],
                      'get' => 'get_products_attributes_discount_price', 'set' => 'set_products_attributes_discount_price',
                      'type' => 'price_table'
                    );
                    */
                }
            }
        }else{
            $this->fields[] = array(
                'data_descriptor' => '%|'.TABLE_PRODUCTS_ATTRIBUTES_PRICES.'|0|0',
                'column_db' => 'attributes_group_price',
                'name' => 'attributes_group_price_0_0',
                'value' => 'Option Price',
                'get' => 'get_products_attributes_price', 'set' => 'set_products_attributes_price',
                'type' => 'numeric',
                'isMain' => true,
            );
            /*
            $this->fields[] = array(
              'data_descriptor' => $data_descriptor,
              'column_db' => 'products_attributes_discount_price',
              'name' => 'products_attributes_discount_price_0',
              'value' => 'Option Discount Price ' . $key,
              'get' => 'get_products_attributes_discount_price', 'set' => 'set_products_attributes_discount_price',
              'type' => 'numeric'
            );
            */
            foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                $data_descriptor = '%|'.TABLE_PRODUCTS_ATTRIBUTES_PRICES.'|0|'.$groups_data['groups_id'];
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'attributes_group_price',
                    'name' => 'attributes_group_price_0_' . $groups_data['groups_id'],
                    'value' => 'Option Price ' . $groups_data['groups_name'],
                    'get' => 'get_products_attributes_price', 'set' => 'set_products_attributes_price',
                    'type' => 'numeric'
                );
                /*
                $this->fields[] = array(
                  'data_descriptor' => $data_descriptor,
                  'column_db' => 'attributes_group_discount_price',
                  'name' => 'attributes_group_discount_price_' . $value['id'] . '_' . $groups_data['groups_id'],
                  'value' => 'Option Discount Price ' . $key . ' ' . $groups_data['groups_name'],
                  'get' => 'get_products_attributes_discount_price', 'set' => 'set_products_attributes_discount_price',
                  'type' => 'price_table'
                );
                */
            }
        }

        $this->fields[] = array( 'name' => 'products_attributes_weight_prefix', 'value' => 'Attributes Weight Prefix' );
        $this->fields[] = array( 'name' => 'products_attributes_weight', 'value' => 'Attributes Weight' );
        $this->fields[] = array( 'name' => 'default_option_value', 'value' => 'Select default' );
        $this->fields[] = array( 'name' => 'products_options_sort_order', 'value' => 'Product options sort order');
        if (\common\helpers\Extensions::isAllowed('TypicalOperatingTemp')) {
            $this->fields[] = array( 'name' => 'tot_prefix', 'value' => 'TOT Prefix' );
            $this->fields[] = array( 'name' => 'tot_value', 'value' => 'TOT Value' );
        }
        $this->fields[] = array('name' => 'products_attributes_filename', 'value' => 'Product Attribute\'s File');
        $this->fields[] = array('name' => 'products_attributes_maxdays', 'value' => 'Max days');
        $this->fields[] = array('name' => 'products_attributes_maxcount', 'value' => 'Max count');
        /*
        $fields_attributes[] = array( 'name' => 'products_options_sort_order', 'value' => 'Attributes Sort Order' );
        $fields_attributes[] = array( 'name' => 'product_attributes_one_time', 'value' => 'Attributes One Time' );
        */

    }

    public function importOptions()
    {

        $insert_new = isset($this->import_config['insert_new'])?$this->import_config['insert_new']:'insert';

        return '
<div class="widget box box-no-shadow">
    <div class="widget-header"><h4>Import options</h4></div>
    <div class="widget-content">
        <div class="row form-group">
            <div class="col-md-6"><label>Insert and update</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[insert_new]',$insert_new, ['insert'=>'Yes, insert new and update','replace'=>'No, replace existing'],['class'=>'form-control']).'</div>
        </div>
    </div>
</div>
        ';
    }

    function get_key_field( $field_data, $categories_id ){
        //$this->data['key_field'] = $this->EPtools->tep_get_categories_full_path((int)$this->data['categories_id']);
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
            "SELECT {$main_source['select']} p.products_model AS main_products_model, ".
            " p.without_inventory AS without_inventory, ".
            " p.products_price_full AS products_price_full, ".
            " options_id, options_values_id, products_attributes_id " .
            "FROM " . TABLE_PRODUCTS_ATTRIBUTES . " pa, ".TABLE_PRODUCTS." p " .
            "WHERE pa.products_id=p.products_id {$filter_sql} ".
            "  AND p.parent_products_id=0 ".
            "ORDER BY pa.products_id, pa.options_id, pa.options_values_id";

        $this->export_query = tep_db_query($main_sql);
        
    }
    
    public function exportRow()
    {
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;

        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;

        foreach ( $data_sources as $source_key=>$source_data ) {
            if ( $source_data['table'] ) {
                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ( $source_data['table']==TABLE_PRODUCTS_OPTIONS ) {
                    $data_sql .= "AND products_options_id='{$this->data['options_id']}' AND language_id='{$source_data['params'][0]}' ";
                }elseif ( $source_data['table']==TABLE_PRODUCTS_OPTIONS_VALUES ) {
                    $data_sql .= "AND products_options_values_id='{$this->data['options_values_id']}' AND language_id='{$source_data['params'][0]}' ";
                }elseif ( $source_data['table']==TABLE_PRODUCTS_ATTRIBUTES_PRICES ) {
                    $data_sql .= "AND products_attributes_id='{$this->data['products_attributes_id']}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                }else{
                    $data_sql .= "AND 1=0 ";
                }
                //echo $data_sql.'<hr>';
                $data_sql_r = tep_db_query($data_sql);
                if ( tep_db_num_rows($data_sql_r)>0 ) {
                    $_data = tep_db_fetch_array($data_sql_r);
                    $this->data = array_merge($this->data, $_data);
                }
            }elseif($source_data['init_function'] && method_exists($this,$source_data['init_function'])){
                call_user_func_array(array($this,$source_data['init_function']),$source_data['params']);
            }
        }

        foreach( $export_columns as $db_key=>$export ) {
            if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_attributes_id']));
            }
        }

        return $this->data;
    }

    public function importRow($data, Messages $message)
    {
        $this->buildSources( array_keys($data) );

        $replaceMode = false;
        if (is_array($this->import_config) && isset($this->import_config['insert_new']) && $this->import_config['insert_new']=='replace'){
            $replaceMode = true;
        }

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $pre_lookup = $this->pre_lookup;
        $file_primary_column = $this->file_primary_column;

        $this->data = $data;

        if (!array_key_exists($file_primary_column, $data)) {
            throw new EP\Exception('Primary key not found in file');
        }
        if ( !isset($pre_lookup['options_id']) ) {
            throw new EP\Exception('options_id key not found in file');
        }
        if ( !isset($pre_lookup['options_values_id']) ) {
            throw new EP\Exception('options_values_id key not found in file');
        }

        $file_primary_value = $this->data[$file_primary_column];
        if ( empty($file_primary_value) ) {
            $message->info('Empty "'.$export_columns[$file_primary_column]['value'].'" column. Row skipped');
            return false;
        }

        if ( !isset($this->last_product_lookup[$file_primary_value]) ) {
            $get_main_data_r = tep_db_query(
                "SELECT products_id, parent_products_id FROM " . TABLE_PRODUCTS . " WHERE products_model='" . tep_db_input($file_primary_value) . "'"
            );
            $found_rows = tep_db_num_rows($get_main_data_r);
            $found_data = $found_rows>0?tep_db_fetch_array($get_main_data_r):false;
            //{{ need update master attributes, not child
            if ( $found_data['parent_products_id']>0 ) {
                $message->info('Can\'t update child product "'.$file_primary_value.'". Skipped');
                $found_rows = 0;
                $found_data = false;
            }
            //}}
            $this->last_product_lookup = array(
                $file_primary_value => array(
                    'found_rows' => $found_rows,
                    'data' => $found_data,
                ),
            );
            if ($found_rows > 1) {
                $message->info('Product "'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            }elseif ($found_rows == 0) {
                $message->info('Product "'.$file_primary_value.'" not found. Skipped');
            }
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
            $products_id = $db_main_data['products_id'];
        }
        $this->data['products_id'] = $products_id;
        // {{
        if ( $replaceMode && !isset($this->_not_updated_option_values[(int)$this->data['products_id']]) ) {
            $this->_not_updated_option_values[(int)$this->data['products_id']] = ArrayHelper::map(ProductsAttributes::find()
                ->select(['products_attributes_id'])
                ->where(['products_id'=>$this->data['products_id']])
                ->asArray()
                ->all(),'products_attributes_id', 'products_attributes_id');
        }
        // }}
        $options_lookup = array();
        foreach ( $pre_lookup['options_id'] as $lookup_column_info ) {
            if ( isset($this->data[$lookup_column_info['name']]) ) {
                $lang_id = $data_sources[$lookup_column_info['data_descriptor']]['params'][0];
                $options_lookup[$lang_id] = $this->data[$lookup_column_info['name']];
            }
        }
        $this->data['options_id'] = $this->EPtools->get_option_by_name($options_lookup);
        if ( empty($this->data['options_id']) ) {
            $message->info('Product "'.$file_primary_value.'" - empty "'.$pre_lookup['options_id'][0]['value'].'". Skipped');
            return false;
        }

        $options_values_lookup = array();
        foreach ( $pre_lookup['options_values_id'] as $lookup_column_info ) {
            if ( isset($this->data[$lookup_column_info['name']]) ) {
                $lang_id = $data_sources[$lookup_column_info['data_descriptor']]['params'][0];
                $options_values_lookup[$lang_id] = $this->data[$lookup_column_info['name']];
            }
        }
        $this->data['options_values_id'] = $this->EPtools->get_option_value_by_name($this->data['options_id'], $options_values_lookup);
        if ( empty($this->data['options_values_id']) ) {
            $message->info('Product "'.$file_primary_value.'" - empty "'.$pre_lookup['options_values_id'][0]['value'].'". Skipped');
            return false;
        }
        $get_main_data_r = tep_db_query(
            "SELECT {$main_source['select']} products_attributes_id ".
            "FROM ".TABLE_PRODUCTS_ATTRIBUTES." ".
            "WHERE products_id='{$this->data['products_id']}' AND options_id='{$this->data['options_id']}' AND options_values_id='{$this->data['options_values_id']}' "
        );
        if ( tep_db_num_rows($get_main_data_r)>0 ) {
            $db_main_data = tep_db_fetch_array($get_main_data_r);
            $this->data['products_attributes_id'] = $db_main_data['products_attributes_id'];
            $products_attributes_id = $db_main_data['products_attributes_id'];
            $update_data_array = array();
            foreach ($main_source['columns'] as $file_column => $db_column) {
                if (!array_key_exists($file_column, $data)) continue;
                if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_attributes_id']));
                }
                $update_data_array[$db_column] = $this->data[$file_column];
            }

            if (count($update_data_array) > 0) {
                tep_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $update_data_array, 'update', "products_attributes_id='" . (int)$products_attributes_id . "'");
            }
            if ( isset($this->_not_updated_option_values[$this->data['products_id']]) ) {
                unset($this->_not_updated_option_values[$this->data['products_id']][$this->data['products_attributes_id']]);
            }
        }else{
            $insert_data_array = array();
            foreach ($main_source['columns'] as $file_column => $db_column) {
                if (!array_key_exists($file_column, $data)) continue;
                if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_attributes_id']));
                }
                $insert_data_array[$db_column] = $this->data[$file_column];
            }
            $insert_data_array['products_id'] = $this->data['products_id'];
            $insert_data_array['options_id'] = $this->data['options_id'];
            $insert_data_array['options_values_id'] = $this->data['options_values_id'];
            tep_db_perform(TABLE_PRODUCTS_ATTRIBUTES, $insert_data_array);
            $products_attributes_id = tep_db_insert_id();
            $this->data['products_attributes_id'] = $products_attributes_id;
        }
        $this->entry_counter++;

        foreach ($data_sources as $source_key => $source_data) {
            if ($source_data['table']) {

                $new_data = array();
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (!array_key_exists($file_column, $data)) continue;
                    if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_attributes_id']));
                    }
                    $new_data[$db_column] = $this->data[$file_column];
                }
                if (count($new_data) == 0) continue;

                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ($source_data['table'] == TABLE_PRODUCTS_ATTRIBUTES_PRICES) {
                    $update_pk = "products_attributes_id='{$products_attributes_id}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                    $insert_pk = array('products_attributes_id' => $products_attributes_id, 'currencies_id' => $source_data['params'][0], 'groups_id' => $source_data['params'][1]);
                    $data_sql .= "AND {$update_pk}";
                } else {
                    continue;
                }
                //echo $data_sql.'<hr>';
                $data_sql_r = tep_db_query($data_sql);
                if (tep_db_num_rows($data_sql_r) > 0) {
                    //$_data = tep_db_fetch_array($data_sql_r);
                    tep_db_free_result($data_sql_r);
                    tep_db_perform($source_data['table'], $new_data, 'update', $update_pk);
                } else {
                    tep_db_perform($source_data['table'], array_merge($new_data, $insert_pk));
                }
            } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_attributes_id']));
                    }
                }
            }
        }
        $this->_processed_pids[intval($this->data['products_id'])] = intval($this->data['products_id']);
        return true;
    }

    public function postProcess(Messages $message){
        $message->info('Processed '.$this->entry_counter.' rows');

        $get_double_defaults_r = tep_db_query(
            "SELECT products_id, options_id, GROUP_CONCAT(products_attributes_id) AS paids ".
            "FROM ".TABLE_PRODUCTS_ATTRIBUTES." ".
            "WHERE default_option_value=1 ".
            "GROUP BY products_id, options_id HAVING COUNT(*)>1"
        );
        if ( tep_db_num_rows($get_double_defaults_r)>0 ) {
            while($get_double_default = tep_db_fetch_array($get_double_defaults_r)){
                $leave_default = intval($get_double_default['paids']);
                tep_db_query(
                    "UPDATE ".TABLE_PRODUCTS_ATTRIBUTES." ".
                    "SET default_option_value=0 ".
                    "WHERE products_id='".$get_double_default['products_id']."' AND options_id='".$get_double_default['options_id']."' ".
                    " AND products_attributes_id!='".$leave_default."' "
                );
            }
        }

        if ( count($this->_processed_pids)>0 ) {
            if ( count($this->_not_updated_option_values)>0 ) {
                $removedCount = 0;
                foreach ($this->_not_updated_option_values as $productsId=>$not_updated_attributes_ids){
                    if ( count($not_updated_attributes_ids)>0 ) {
                        ProductsAttributes::deleteAll(['AND', ['products_id' => $productsId], ['IN', 'products_attributes_id', $not_updated_attributes_ids]]);
                        $removedCount += count($not_updated_attributes_ids);
                    }
                }
                $message->info('Removed '.(int)$removedCount.' option values');
            }
            $message->info('Check inventory');
            foreach( $this->_processed_pids as $_pid ) {
                $updated[(int)$_pid] = array();
            }
            $get_updated_products_r = tep_db_query(
                "SELECT products_id, options_id, options_values_id ".
                "FROM " . TABLE_PRODUCTS_ATTRIBUTES . " ".
                "WHERE products_id IN('".implode("','",$this->_processed_pids)."') ".
                "ORDER BY products_id, options_id, options_values_id"
            );
            $updated = array();
            while( $_updated_product = tep_db_fetch_array($get_updated_products_r) ) {

                if ( !isset($updated[ (int)$_updated_product['products_id'] ][ (int)$_updated_product['options_id'] ]) ) $updated[ (int)$_updated_product['products_id'] ][ (int)$_updated_product['options_id'] ] = array();
                $updated[ (int)$_updated_product['products_id'] ][ (int)$_updated_product['options_id'] ][] = (int)$_updated_product['options_values_id'];
            }
            foreach( $updated as $pid=>$attributes ) {
                $this->check_inventory_ref($pid, $attributes);
            }
        }
        $message->info('Done');

        $this->EPtools->done('attributes_import');
    }

    protected function set_products_attributes_price($field_data, $products_attributes_id)
    {
        if ( isset($field_data['isMain']) && $field_data['isMain'] ){
            $productAttributes = \common\models\ProductsAttributes::findOne(['products_attributes_id'=>$products_attributes_id]);
            if ( $productAttributes ) {
                $productAttributes->setAttributes(['options_values_price'=>$this->data[$field_data['name']]], false);
                $productAttributes->save(false);
            }
        }
    }

    protected function get_price_info($field_data, $products_id)
    {
        $this->data[$field_data['name']] = '';
        if (\common\helpers\Extensions::isAllowed('Inventory')){
            $this->data[$field_data['name']] = 'Inventory Price';
            if ($this->data['without_inventory'] || EP\Tools::getInstance()->is_option_virtual($this->data['options_id']) ) {
                $this->data[$field_data['name']] = 'Attribute Price';
            }
        }else{
            $this->data[$field_data['name']] = 'Attribute Price';
        }

        return $this->data[$field_data['name']];
    }

    private function check_inventory_ref($products_id, $attributes){
        $inventoryDisabled = \common\models\Products::find()
            ->where(['products_id'=>$products_id])
            ->andWhere(['without_inventory'=>1])
            ->count()>0;
        $uprids = array();
        if ( !$inventoryDisabled && count($attributes)>0 ) {
            $arr1 = array();
            foreach ($attributes as $oid => $val_arr){
                if (EP\Tools::getInstance()->is_option_virtual($oid)) continue;
                $arr1[] = self::__add_str($val_arr, '{' . $oid . '}');
            }
            $uprids = self::__get_all_uprid($products_id, $arr1, 0, (count($arr1)-1), array());
        }else{
            //$uprids[] = (string)$products_id; // only with attributes products in inventory
        }
        tep_db_query(
            "DELETE FROM ".TABLE_INVENTORY." ".
            "WHERE prid='".$products_id."' ".
            (count($uprids)>0?"AND products_id NOT IN ('".implode("','",array_map('tep_db_input',$uprids))."')":'')
        );
        $inventory_name_arr = array();
        $inventory_name_arr[] = \common\helpers\Product::get_products_name($products_id, \common\helpers\Language::get_default_language_id() );
        $new_records = 0;
        foreach( $uprids as $uprid ) {
            preg_match_all('/{(\d+)}(\d+)/',$uprid, $matches);
            $uprid_name = $inventory_name_arr;
            foreach( $matches[2] as $val_id ) {
                $uprid_name[] = $this->EPtools->get_option_value_name($val_id, \common\helpers\Language::get_default_language_id());
            }
            $inventory_name = implode(' ',$uprid_name);

            $get_inventory_r = tep_db_query(
                "SELECT inventory_id, products_name, products_model ".
                "FROM ".TABLE_INVENTORY." ".
                "WHERE prid='".$products_id."' AND products_id='".tep_db_input($uprid)."' "
            );
            if ( tep_db_num_rows($get_inventory_r)>0 ) {
                $_inventory = tep_db_fetch_array($get_inventory_r);
                if ( $inventory_name!=$_inventory['products_name'] ) {
                    tep_db_query("UPDATE ".TABLE_INVENTORY." SET products_name='".tep_db_input($inventory_name)."' WHERE inventory_id='".$_inventory['inventory_id']."'");
                }
            }else{
                tep_db_perform(TABLE_INVENTORY, array(
                    'products_id' => $uprid,
                    'prid' => $products_id,
                    'products_name' => $inventory_name,
                    'products_model' => '',
                ));
                $new_records++;
            }
        }
        if ( count($uprids)>0 ) {
            tep_db_query(
                "UPDATE " . TABLE_PRODUCTS . " ".
                "SET products_quantity = (SELECT SUM(IF(i.products_quantity>0 AND IFNULL(i.non_existent,0)=0,i.products_quantity,0)) FROM ".TABLE_INVENTORY." i WHERE i.prid='".(int)$products_id."' GROUP BY i.prid) ".
                "WHERE products_id = '" .$products_id. "'"
            );
        }

        return $new_records;
    }

    protected function get_product_model($field_data, $id){
        $this->data['products_model'] = $this->data['main_products_model'];
        return $this->data['main_products_model'];
    }

    private static function __get_all_uprid($str, $arr, $j, $l, $res){
        if ($l==$j){
            $res = array_merge($res, self::__add_str($arr[$j], $str));
        } elseif(sizeof($arr)>0) {
            foreach ($arr[$j] as $val){
                $res = self::__get_all_uprid($str . $val, $arr, ($j+1), $l, $res);
            }
        } else {
            $res = array($str);
        }
        $res = array_unique($res);
        return $res;
    }

    private static function __add_str($arr, $str){
        $a = array();
        foreach ($arr as $item){ $a[] = $str . $item; }
        return  $a;
    }

}