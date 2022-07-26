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
use yii\base\Exception;

class Inventory extends ProviderAbstract implements ImportInterface, ExportInterface
{

    protected $fields = array();

    protected $data = array();
    protected $EPtools;
    protected $entry_counter = 0;
    protected $export_query;

    protected $makeNetPricesOnImport = null;
    protected $exportPriceGross = false;

    protected $stock_warning = null;

    function init()
    {
        parent::init();
        $this->initFields();

        $this->EPtools = new EP\Tools();
    }

    public function importOptions()
    {

        $imported_price_is_gross = isset($this->import_config['imported_price_is_gross'])?$this->import_config['imported_price_is_gross']:'no';

        return '
<div class="widget box box-no-shadow">
    <div class="widget-header"><h4>Import options</h4></div>
    <div class="widget-content">
        <div class="row">
            <div class="col-md-6"><label>Imported Prices with tax?</label></div>
            <div class="col-md-6">'.Html::dropDownList('import_config[imported_price_is_gross]',$imported_price_is_gross, ['no'=>'No, Net Price','yes'=>'Yes, price contain TAX value'],['class'=>'form-control']).'</div>
        </div>
    </div>
</div>
        ';
    }

    protected function initFields()
    {
        $currencies = \Yii::$container->get('currencies');

        $this->fields[] = array( 'name' => 'products_id', 'value' => 'KEY', 'prefix'=>'i', 'is_key'=>true );
        $this->fields[] = array( 'name' => 'p_products_model', 'value' => 'Main Model', 'calculated'=>true, );
        $this->fields[] = array( 'name' => 'p_products_ean', 'value' => 'Main EAN', 'calculated'=>true, );
        $this->fields[] = array( 'name' => 'p_products_upc', 'value' => 'Main UPC', 'calculated'=>true, );
        $this->fields[] = array( 'name' => 'p_products_asin', 'value' => 'Main ASIN', 'calculated'=>true, );
        $this->fields[] = array( 'name' => 'p_products_isbn', 'value' => 'Main ISBN', 'calculated'=>true, );
        $get_options_r = tep_db_query(
            "SELECT products_options_id, products_options_name ".
            "FROM ".TABLE_PRODUCTS_OPTIONS." ".
            "WHERE language_id='".$this->languages_id."' AND is_virtual=0 ".
            "ORDER BY products_options_sort_order, products_options_name "
        );
        if ( tep_db_num_rows($get_options_r)>0 ) {
            while( $_option = tep_db_fetch_array($get_options_r) ) {
                $this->fields[] = array(
                    'name' => 'option_'.$_option['products_options_id'],
                    'value' => 'Option - '.$_option['products_options_name'],
                    'calculated'=>true,
                    'get' => 'get_products_option_value',
                );
            }
        }
        $this->fields[] = array( 'name' => 'products_name', 'value' => 'Inventory Name', 'prefix'=>'i', );
        $this->fields[] = array( 'name' => 'products_quantity', 'value' => 'Quantity', 'prefix'=>'i',);
        //$this->fields[] = array( 'name' => 'products_quantity_diff', 'value' => 'Quantity Difference', 'calculated'=>true, );
        $this->fields[] = array( 'name' => 'products_model', 'value' => 'Inventory Model', 'prefix'=>'i',);
        $this->fields[] = array( 'name' => 'products_ean', 'value' => 'EAN', 'prefix'=>'i',);
        $this->fields[] = array( 'name' => 'products_upc', 'value' => 'UPC', 'prefix'=>'i', );
        $this->fields[] = array( 'name' => 'products_asin', 'value' => 'ASIN', 'prefix'=>'i', );
        $this->fields[] = array( 'name' => 'products_isbn', 'value' => 'ISBN', 'prefix'=>'i', );
        $this->fields[] = array( 'name' => 'products_date_available', 'value' => 'Date Available', 'prefix'=>'i', 'type' => 'date', 'set'=>'set_date', );
        $this->fields[] = array( 'name' => 'inventory_weight', 'value' => 'Weight', 'prefix'=>'i', );
        $this->fields[] = array( 'name' => 'non_existent', 'value' => 'non_existent', 'prefix'=>'i', );
        $this->fields[] = array( 'name' => 'inventory_tax_class_id', 'value' => 'Variant Tax Class', 'set'=>'set_tax_class', 'get'=>'get_tax_class');
        $this->fields[] = array( 'name' => '_price_info', 'calculated'=>true, 'value' => 'Price (info!)', 'get'=>'get_price_info' );

        if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
            foreach ($currencies->currencies as $key => $value) {

                $data_descriptor = '%|'.TABLE_INVENTORY_PRICES.'|'.$value['id'].'|0';
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'inventory_group_price',
                    'column_db_main' => 'inventory_price',
                    'name' => 'inventory_group_price_' . $value['id'] . '_0',
                    'value' => 'Inventory Price ' . $key,
                    'get' => 'get_products_price', 'set' => 'set_products_price',
                    'type' => 'numeric',
                    'isMain' => DEFAULT_CURRENCY==$key,
                );

                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'inventory_group_discount_price',
                    'column_db_main' => 'inventory_discount_price',
                    'name' => 'inventory_group_discount_price_' . $value['id'] . '_0',
                    'value' => 'Inventory Discount Price ' . $key,
                    'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                    'type' => 'price_table',
                    'isMain' => DEFAULT_CURRENCY==$key,
                );

                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'inventory_full_price',
                    'column_db_main' => 'inventory_full_price',
                    'name' => 'inventory_full_price_' . $value['id'] . '_0',
                    'value' => 'Inventory Full Price ' . $key,
                    'get' => 'get_products_full_price', 'set' => 'set_products_full_price',
                    'type' => 'numeric',
                    'isMain' => DEFAULT_CURRENCY==$key,
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'inventory_discount_full_price',
                    'column_db_main' => 'inventory_discount_full_price',
                    'name' => 'inventory_discount_full_price_' . $value['id'] . '_0',
                    'value' => 'Inventory Discount Full Price '. $key,
                    'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                    'type' => 'price_table',
                    'isMain' => DEFAULT_CURRENCY==$key,
                );

                foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                    $data_descriptor = '%|'.TABLE_INVENTORY_PRICES.'|'.$value['id'].'|'.$groups_data['groups_id'];
                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'inventory_group_price',
                        'name' => 'inventory_group_price_' . $value['id'] . '_' . $groups_data['groups_id'],
                        'value' => 'Inventory Price ' . $key . ' ' . $groups_data['groups_name'],
                        'get' => 'get_products_price', 'set' => 'set_products_price',
                        'type' => 'numeric'
                    );

                    $this->fields[] = array(
                      'data_descriptor' => $data_descriptor,
                      'column_db' => 'inventory_group_discount_price',
                      'name' => 'products_price_discount_' . $value['id'] . '_' . $groups_data['groups_id'],
                      'value' => 'Inventory Discount Price ' . $key . ' ' . $groups_data['groups_name'],
                      'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                      'type' => 'price_table'
                    );

                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'inventory_full_price',
                        'name' => 'inventory_full_price_' . $value['id'] . '_'.$groups_data['groups_id'],
                        'value' => 'Inventory Full Price '.$key.' '.$groups_data['groups_name'],
                        'get' => 'get_products_full_price', 'set' => 'set_products_full_price',
                        'type' => 'numeric'
                    );
                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'inventory_discount_full_price',
                        'name' => 'inventory_discount_full_price_' . $value['id'] . '_'. $groups_data['groups_id'],
                        'value' => 'Inventory Discount Full Price '.$key.' '.$groups_data['groups_name'],
                        'get' => 'get_products_discount_full_price', 'set' => 'set_products_discount_full_price',
                        'type' => 'price_table'
                    );

                }

            }
        } else {
            $data_descriptor = '%|'.TABLE_INVENTORY_PRICES.'|0|0';
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'inventory_group_price',
                'column_db_main' => 'inventory_price',
                'name' => 'inventory_price_0',
                'value' => 'Inventory Price',
                'get' => 'get_products_price', 'set' => 'set_products_price',
                'type' => 'numeric',
                'isMain' => true,
            );

            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'inventory_group_discount_price',
                'column_db_main' => 'inventory_discount_price',
                'name' => 'inventory_discount_price_0',
                'value' => 'Inventory Discount Price',
                'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                'type' => 'price_table',
                'isMain' => true,
            );

            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'inventory_full_price',
                'column_db_main' => 'inventory_full_price',
                'name' => 'inventory_full_price_0',
                'value' => 'Inventory Full Price',
                'get' => 'get_products_full_price', 'set' => 'set_products_full_price',
                'type' => 'numeric',
                'isMain' => true,
            );
            $this->fields[] = array(
                'data_descriptor' => $data_descriptor,
                'column_db' => 'inventory_discount_full_price',
                'column_db_main' => 'inventory_discount_full_price',
                'name' => 'inventory_discount_full_price_0',
                'value' => 'Inventory Discount Full Price',
                'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                'type' => 'price_table',
                'isMain' => true,
            );

            foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                $data_descriptor = '%|'.TABLE_INVENTORY_PRICES.'|0|'.$groups_data['groups_id'];
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'inventory_group_price',
                    'name' => 'inventory_group_price_' . $groups_data['groups_id'],
                    'value' => 'Inventory Price ' . $groups_data['groups_name'],
                    'get' => 'get_products_price', 'set' => 'set_products_price',
                    'type' => 'numeric'
                );

                $this->fields[] = array(
                  'data_descriptor' => $data_descriptor,
                  'column_db' => 'inventory_group_discount_price',
                  'name' => 'inventory_group_discount_price_' . $groups_data['groups_id'],
                  'value' => 'Inventory Discount Price ' . $groups_data['groups_name'],
                  'get' => 'get_products_discount_price', 'set' => 'set_products_discount_price',
                  'type' => 'price_table'
                );

                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'inventory_full_price',
                    'name' => 'inventory_full_price_'.$groups_data['groups_id'],
                    'value' => 'Inventory Full Price '.$groups_data['groups_name'],
                    'get' => 'get_products_full_price', 'set' => 'set_products_full_price',
                    'type' => 'numeric'
                );
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'inventory_discount_full_price',
                    'name' => 'inventory_discount_full_price_'. $groups_data['groups_id'],
                    'value' => 'Inventory Discount Full Price '.$groups_data['groups_name'],
                    'get' => 'get_products_discount_full_price', 'set' => 'set_products_discount_full_price',
                    'type' => 'price_table'
                );

            }
        }
        $this->fields[] = array( 'name' => 'stock_indication_id', 'value' => 'Stock Availability', 'prefix'=>'i', 'get' => 'get_stock_indication', 'set' => 'set_stock_indication', );
        $this->fields[] = array( 'name' => 'stock_delivery_terms_id', 'value' => 'Stock Delivery Terms', 'prefix'=>'i', 'get' => 'get_delivery_terms', 'set' => 'set_delivery_terms', );

    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);

        $main_source = $this->main_source;

        $filter_sql = '';
        if ( is_array($filter) ) {
            if ( isset($filter['products_id']) && is_array($filter['products_id']) && count($filter['products_id'])>0 ) {
                $filter_sql .= "AND i.prid IN ('".implode("','", array_map('intval',$filter['products_id']))."') ";
            }
            if ( isset($filter['category_id']) && $filter['category_id']>0 ) {
                $categories = array((int)$filter['category_id']);
                \common\helpers\Categories::get_subcategories($categories, $categories[0]);
                $filter_sql .= "AND i.prid IN(SELECT products_id FROM ".TABLE_PRODUCTS_TO_CATEGORIES." WHERE categories_id IN('".implode("','",$categories)."')) ";
            }
            if ( isset($filter['price_tax']) && $filter['price_tax']>0 ) {
                $this->exportPriceGross = true;
            }
        }

        $main_sql =
            "SELECT {$main_source['select']} i.products_id, i.inventory_id, ".
            " p.products_model AS p_products_model, p.products_ean AS p_products_ean, ".
            " p.products_upc AS p_products_upc, p.products_asin AS p_products_asin, p.products_isbn AS p_products_isbn, ".
            " p.products_price_full AS products_price_full, ".
            " i.inventory_tax_class_id as _inventory_tax_class_id, p.products_tax_class_id AS _products_tax_class_id, ".
            " i.inventory_price AS inventory_price_def, i.inventory_discount_price AS inventory_discount_price_def, ".
            " i.inventory_full_price AS inventory_full_price_def, i.inventory_discount_full_price AS inventory_discount_full_price_def ".
            "FROM ".TABLE_INVENTORY." i ".
            " INNER JOIN ".TABLE_PRODUCTS." p ON p.products_id=i.prid ".
            " INNER JOIN (SELECT DISTINCT products_id FROM ".TABLE_PRODUCTS_ATTRIBUTES.") pa ON pa.products_id=i.prid ".
            "WHERE 1 {$filter_sql} ".
            "  AND p.without_inventory=0 ".
            "  AND p.parent_products_id=0 ".
            "ORDER BY i.prid, i.products_id ".
            "/*LIMIT 3*/";

        $this->export_query = tep_db_query( $main_sql );
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
                if ( $source_data['table']==TABLE_INVENTORY_PRICES  ) {
                    $data_sql .= "AND inventory_id='{$this->data['inventory_id']}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
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
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_id']));
            }
        }

        return $this->data;
    }

    public function importRow($data, Messages $message)
    {

        if ( is_null($this->makeNetPricesOnImport) ){
            $this->makeNetPricesOnImport = false;
            if (is_array($this->import_config) && isset($this->import_config['imported_price_is_gross']) && $this->import_config['imported_price_is_gross']=='yes'){
                $this->makeNetPricesOnImport = true;
            }
        }

        $this->buildSources(array_keys($data));

        $this->data = $data;

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;

        $is_updated = false;
        $prid = 0;
        $db_main_data = false;

        if (!array_key_exists($file_primary_column, $this->data)) {
            throw new EP\Exception('Primary key not found in file');
        }
        $file_primary_value = $this->data[$file_primary_column];
        $get_main_data_r = tep_db_query(
            "SELECT i.*, ".
            " i.inventory_tax_class_id as _inventory_tax_class_id, p.products_tax_class_id AS _products_tax_class_id ".
            "FROM " . TABLE_INVENTORY . " i ".
            " INNER JOIN ".TABLE_PRODUCTS." p ON p.products_id=i.prid ".
            "WHERE i.{$file_primary_column}='" . tep_db_input($file_primary_value) . "'"
        );
        $found_rows = tep_db_num_rows($get_main_data_r);
        if ($found_rows > 1) {
            // error data not unique
            $message->info('"'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            return false;
        } elseif ($found_rows == 0) {
            // find by model and option, value name
            $lookupProductConditions = [];
            if ( isset($this->data['p_products_model']) && !empty($this->data['p_products_model']) ) {
                $lookupProductConditions[] = "p.products_model='".tep_db_input($this->data['p_products_model'])."'";
            }
            if ( isset($this->data['p_products_ean']) && !empty($this->data['p_products_ean']) ) {
                $lookupProductConditions[] = "p.products_ean='".tep_db_input($this->data['p_products_ean'])."'";
            }
            if ( isset($this->data['p_products_upc']) && !empty($this->data['p_products_upc']) ) {
                $lookupProductConditions[] = "p.products_upc='".tep_db_input($this->data['p_products_upc'])."'";
            }
            if ( isset($this->data['p_products_asin']) && !empty($this->data['p_products_asin']) ) {
                $lookupProductConditions[] = "p.products_asin='".tep_db_input($this->data['p_products_asin'])."'";
            }
            if ( isset($this->data['p_products_isbn']) && !empty($this->data['p_products_isbn']) ) {
                $lookupProductConditions[] = "p.products_isbn='".tep_db_input($this->data['p_products_isbn'])."'";
            }
            $file_assigned_options = [];
            foreach ( preg_grep('/^option_\d+$/',array_keys($this->data)) as $option_key ) {
                if ( empty($this->data[$option_key]) ) continue;
                $option_id = (int)substr($option_key,7);
                $value_id = $this->EPtools->get_option_value_by_name($option_id, $this->data[$option_key]);
                $file_assigned_options[ $option_id ] = $value_id;
            }
            ksort($file_assigned_options);

            if ( count($lookupProductConditions)>0 ) {
                $get_product_r = tep_db_query(
                    "SELECT p.products_id ".
                    "FROM ".TABLE_PRODUCTS." p ".
                    "WHERE p.without_inventory=0 AND (".implode(" OR ",$lookupProductConditions).") "
                );
                if ( tep_db_num_rows($get_product_r)==1 ){
                    $_product = tep_db_fetch_array($get_product_r);
                    $uprid = \common\helpers\Inventory::get_uprid($_product['products_id'], $file_assigned_options);
                    $get_main_data_r = tep_db_query(
                        "SELECT i.*, ".
                        " i.inventory_tax_class_id as _inventory_tax_class_id, p.products_tax_class_id AS _products_tax_class_id ".
                        "FROM " . TABLE_INVENTORY . " i ".
                        " INNER JOIN ".TABLE_PRODUCTS." p ON p.products_id=i.prid ".
                        "WHERE i.products_id='" . tep_db_input($uprid) . "'"
                    );
                    $found_rows = tep_db_num_rows($get_main_data_r);
                    if ( $found_rows==1 ) {
                        $db_main_data = tep_db_fetch_array($get_main_data_r);
                        $this->data[$file_primary_column] = $db_main_data['products_id'];
                        $file_primary_value = $this->data[$file_primary_column];
                    }
                }
            }
        }else{
            $db_main_data = tep_db_fetch_array($get_main_data_r);
        }
        if ( is_array($db_main_data) ) {
            $this->data['_inventory_tax_class_id'] = $db_main_data['_inventory_tax_class_id'];
            $this->data['_products_tax_class_id'] = $db_main_data['_products_tax_class_id'];
        }

        if (is_null($this->stock_warning)){
            $this->stock_warning = false;
            if (array_key_exists('products_quantity', $this->data)){
                $warehouses = \common\helpers\Warehouses::get_warehouses(false);
                if ( count($warehouses)>1 ) {
                    $message->info("The update of 'Product quantity' is skipped. Multiple warehouses detected. Instead use Warehouse Stock feed.");
                    $this->stock_warning = true;
                }elseif(\common\helpers\Suppliers::getSuppliersCount(false)>1){
                    $message->info("The update of 'Product quantity' is skipped. Multiple suppliers detected. Instead use Warehouse Stock feed.");
                    $this->stock_warning = true;
                }
            }
        }elseif ($this->stock_warning===true){
            unset($this->data['products_quantity']);
        }

        if ( is_array($db_main_data) ){
            $this->entry_counter++;
            // update
            $inventory_id = $db_main_data['inventory_id'];
            $prid = $db_main_data['prid'];
            $update_data_array = array();
            foreach ($main_source['columns'] as $file_column => $db_column) {
                if (!array_key_exists($file_column, $this->data)) continue;

                if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                    call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['inventory_id'] ?? null, $message));
                }

                if ( array_key_exists($file_column, $this->data) ) {
                    $update_data_array[$db_column] = $this->data[$file_column];
                }
            }
            if (count($update_data_array) > 0) {
                if ( isset($update_data_array['products_quantity']) && $update_data_array['products_quantity']>0 ) {
                    $update_data_array['send_notification'] = '1';
                }
                tep_db_perform(TABLE_INVENTORY, $update_data_array, 'update', "inventory_id='" . (int)$inventory_id . "'");
                $is_updated = true;
            }
        }else{
            $message->info('"' . $file_primary_value . '" not found. Skipped');
            return false;
        }
        //$entry_counter++;
        $this->data['inventory_id'] = $inventory_id;

        foreach ($data_sources as $source_key => $source_data) {
            if ($source_data['table']) {

                $new_data = array();
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (!array_key_exists($file_column, $this->data)) continue;
                    if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['inventory_id'], $message));
                    }

                    if ( array_key_exists($file_column, $this->data) ) {
                        $new_data[$db_column] = $this->data[$file_column];
                    }
                }

                if (count($new_data) == 0) continue;

                $data_sql = "SELECT {$source_data['select']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";
                if ($source_data['table'] == TABLE_INVENTORY_PRICES) {
                    $update_pk = "inventory_id='{$inventory_id}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                    $insert_pk = array('inventory_id' => $inventory_id, 'currencies_id' => $source_data['params'][0], 'groups_id' => $source_data['params'][1]);
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
                $is_updated = true;
            } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                foreach ($source_data['columns'] as $file_column => $db_column) {
                    if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                        call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['inventory_id'], $message));
                    }
                }
            }
        }
        if ($is_updated) {
            /*tep_db_query(
                "UPDATE ".TABLE_PRODUCTS." p ".
                " INNER JOIN (".
                "   SELECT i.prid, SUM(IF(i.products_quantity>0 AND IFNULL(i.non_existent,0)=0,i.products_quantity,0)) AS summary_quantity FROM ".TABLE_INVENTORY." i WHERE i.prid='".$prid."' GROUP BY i.prid ".
                " ) isum ON isum.prid=p.products_id ".
                "SET p.products_quantity = IFNULL(isum.summary_quantity,0) ".
                "WHERE p.products_id='".$prid."'"
            );
            if ( false ) {
                $switch_off_stock_ids = \common\classes\StockIndication::productDisableByStockIds();
                tep_db_query(
                    "update " . TABLE_PRODUCTS . " " .
                    "set products_status = 0 " .
                    "where products_id = '" . (int)$prid . "' " .
                    " AND products_quantity<=0 " .
                    " AND stock_indication_id IN ('" . implode("','", $switch_off_stock_ids) . "')"
                );
            }*/
            if (trim($db_main_data['products_id']) != '' && array_key_exists('products_quantity', $this->data) && (int)$this->data['products_quantity'] >= 0) {
                global $login_id;
                $quantity = (int)$this->data['products_quantity'];
                $inventoryId = trim($db_main_data['products_id']);
                \common\models\WarehousesProducts::deleteAll([
                    'prid' => (int)$inventoryId,
                    'products_id' => $inventoryId
                ]);
                $locationId = 0;
                foreach (\common\helpers\Product::getWarehouseIdPriorityArray($inventoryId, $quantity, false) as $warehouseId) {
                    foreach (\common\helpers\Product::getSupplierIdPriorityArray($inventoryId) as $supplierId) {
                        $quantity = \common\helpers\Warehouses::update_products_quantity($inventoryId, $warehouseId, $quantity, ($quantity > 0 ? '+' : '-'), $supplierId, $locationId, [
                            'comments' => 'EP Stock feed update',
                            'admin_id' => $login_id
                        ]);
                        if ($quantity > 0) {
                            \common\helpers\Product::doCache($inventoryId);
                        }
                        break 2;
                    }
                }
            }
        }
        return $is_updated;
    }

    public function postProcess(Messages $message)
    {
        $message->info('Processed '.$this->entry_counter.' products');
        $message->info('Done.');

        $this->EPtools->done('inventory_import');
    }

    function import(Formatter\FormatterInterface $input, EP\Messages $message)
    {
        $this->entry_counter = 0;
        while ($data = $input->read_array()) {
            if ( $this->importRow($data, $message) ) {
                $this->entry_counter++;
            }
        }
        $this->postProcess($message);
    }

    function get_products_price( $field_data, $products_id )
    {
        if( !isset($this->data[$field_data['name']]) || $this->data[$field_data['name']]==='' ) {
            if ( isset($field_data['isMain']) && $field_data['isMain'] && $field_data['column_db_main'] ) {
                if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
                    $this->data[$field_data['name']] = '0.00';
                } else {
                    $this->data[$field_data['name']] = $this->data[$field_data['column_db_main'] . '_def'];
                }
            }else {
                $this->data[$field_data['name']] = 'same'/*'-2'*/;
            }
        }elseif( floatval($this->data[$field_data['name']])==-2 ) {
            $this->data[$field_data['name']] = 'same';
        }elseif( floatval($this->data[$field_data['name']])==-1 ) {
            $this->data[$field_data['name']] = 'disabled';
        }
        if ( $this->exportPriceGross && is_numeric($this->data[$field_data['name']]) && $this->data[$field_data['name']]>0 ){
            if (!array_key_exists('inventory_tax_class_id', $this->data)) {
                $this->data['inventory_tax_class_id'] = $this->data['_inventory_tax_class_id'];
            }
            if (!array_key_exists('products_tax_class_id', $this->data)) {
                $this->data['products_tax_class_id'] = $this->data['_products_tax_class_id'];
            }
            $this->data[$field_data['name']] = number_format($this->data[$field_data['name']]*((100+$this->getTaxRate())/100),6,'.','');
        }
        return $this->data[$field_data['name']];
    }

    function set_products_price( $field_data, $inventory_id )
    {
        if( $this->data[$field_data['name']]==='' ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-2 || $this->data[$field_data['name']]=='same' ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-1 || $this->data[$field_data['name']]=='disabled' ) {
            $this->data[$field_data['name']] = '-1';
        }
        if ( floatval($this->data[$field_data['name']])>0 ) {
            if($this->makeNetPricesOnImport && $taxRate = $this->getTaxRate()){
                $this->data[$field_data['name']] = $this->data[$field_data['name']]*(100/(100+$taxRate));
            }
        }
        if ( isset($field_data['isMain']) && $field_data['isMain'] && $field_data['column_db_main'] ){
            $productInventory = \common\models\Inventory::findOne(['inventory_id'=>$inventory_id]);
            if ( $productInventory ) {
                $productInventory->setAttributes([$field_data['column_db_main']=>$this->data[$field_data['name']]], false);
                $productInventory->save(false);
            }
        }
        return $this->data[$field_data['name']];
    }


    function get_products_discount_price( $field_data, $products_id )
    {
        if( !isset($this->data[$field_data['name']]) || $this->data[$field_data['name']]==='' ) {
            if ( isset($field_data['isMain']) && $field_data['isMain'] && $field_data['column_db_main'] ) {
                if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
                    $this->data[$field_data['name']] = '';
                } else {
                    $this->data[$field_data['name']] = $this->data[$field_data['column_db_main'] . '_def'];
                }
            }
        }
        if ( $this->exportPriceGross && !empty($this->data[$field_data['name']]) ) {
            if (!array_key_exists('inventory_tax_class_id', $this->data)) {
                $this->data['inventory_tax_class_id'] = $this->data['_inventory_tax_class_id'];
            }
            if (!array_key_exists('products_tax_class_id', $this->data)) {
                $this->data['products_tax_class_id'] = $this->data['_products_tax_class_id'];
            }
            $taxRate = $this->getTaxRate();
            if ( $taxRate ) {
                $ar = preg_split('/[; :]/', $this->data[$field_data['name']], -1, PREG_SPLIT_NO_EMPTY);
                $clean = '';
                if (is_array($ar) && count($ar) > 0 && count($ar) % 2 == 0) {
                    $tmp = [];
                    for ($i = 0, $n = sizeof($ar); $i < $n; $i = $i + 2) {
                        $tmp[intval($ar[$i])] = floatval($ar[$i + 1]);
                    }
                    ksort($tmp, SORT_NUMERIC);
                    foreach ($tmp as $key => $value) {
                        if ($key < 2) { // q-ty discount should start from 2+
                            $clean = '';
                            break;
                        }
                        if (is_numeric($value) && $value > 0) {
                            $value = $value * ((100 + $taxRate) / 100);
                        }
                        $clean .= $key . ':' . $value . ';';
                    }
                }
                $this->data[$field_data['name']] = $clean;
            }
        }
        return $this->data[$field_data['name']] ?? null;
    }

    function get_products_option_value($field_data, $products_id)
    {
        if (strpos($field_data['name'],'option_')!==0) return '';
        $id = intval(substr($field_data['name'],7));
        if ( strpos($products_id, '{'.$id.'}' )!==false && preg_match('/\{'.$id.'\}(\d+)/',$products_id, $matchValueId) ){
            return $this->EPtools->get_option_value_name($matchValueId[1], $this->languages_id);
        }
    }

    function get_price_info($field_data, $products_id)
    {
        $this->data[$field_data['name']] = '';
        $this->data[$field_data['name']] = 'Additional price';
        if ( $this->data['products_price_full'] ){
            $this->data[$field_data['name']] = 'Full price';
        }
        return $this->data[$field_data['name']];
    }

    function get_products_full_price($field_data, $products_id)
    {
        if( !isset($this->data[$field_data['name']]) || $this->data[$field_data['name']]==='' ) {
            if ( isset($field_data['isMain']) && $field_data['isMain'] && $field_data['column_db_main'] ){
                if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
                    $this->data[$field_data['name']] = '0.00';
                } else {
                    $this->data[$field_data['name']] = $this->data[$field_data['column_db_main'] . '_def'];
                }
            }else {
                $this->data[$field_data['name']] = 'same'/*'-2'*/;
            }
        }elseif( floatval($this->data[$field_data['name']])==-2 ) {
            $this->data[$field_data['name']] = 'same';
        }elseif( floatval($this->data[$field_data['name']])==-1 ) {
            $this->data[$field_data['name']] = 'disabled';
        }
        if ( $this->exportPriceGross && is_numeric($this->data[$field_data['name']]) && $this->data[$field_data['name']]>0 ){
            if (!array_key_exists('inventory_tax_class_id', $this->data)) {
                $this->data['inventory_tax_class_id'] = $this->data['_inventory_tax_class_id'];
            }
            if (!array_key_exists('products_tax_class_id', $this->data)) {
                $this->data['products_tax_class_id'] = $this->data['_products_tax_class_id'];
            }
            $this->data[$field_data['name']] = number_format($this->data[$field_data['name']]*((100+$this->getTaxRate())/100),6,'.','');
        }
        return $this->data[$field_data['name']];
    }

    function set_products_full_price($field_data, $inventory_id)
    {
        if( $this->data[$field_data['name']]==='' ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-2 || $this->data[$field_data['name']]=='same' ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-1 || $this->data[$field_data['name']]=='disabled' ) {
            $this->data[$field_data['name']] = '-1';
        }
        if( floatval($this->data[$field_data['name']])>0 ) {
            if($this->makeNetPricesOnImport && $taxRate = $this->getTaxRate()){
                $this->data[$field_data['name']] = $this->data[$field_data['name']]*(100/(100+$taxRate));
            }
        }
        if ( isset($field_data['isMain']) && $field_data['isMain'] && $field_data['column_db_main'] ){
            $productInventory = \common\models\Inventory::findOne(['inventory_id'=>$inventory_id]);
            if ( $productInventory ) {
                $productInventory->setAttributes([$field_data['column_db_main']=>$this->data[$field_data['name']]], false);
                $productInventory->save(false);
            }
        }
        return $this->data[$field_data['name']];
    }

    function get_products_discount_full_price($field_data, $products_id)
    {

    }

    function set_products_discount_full_price($field_data, $inventory_id)
    {
        if ( isset($field_data['isMain']) && $field_data['isMain'] && $field_data['column_db_main'] ){
            $productInventory = \common\models\Inventory::findOne(['inventory_id'=>$inventory_id]);
            if ( $productInventory ) {
                $productInventory->setAttributes([$field_data['column_db_main']=>$this->data[$field_data['name']]], false);
                $productInventory->save(false);
            }
        }
    }

    function get_delivery_terms($field_data, $products_id)
    {
        if ( empty($this->data[$field_data['name']]) ) {
            $this->data[$field_data['name']] = '';
        }else{
            $this->data[$field_data['name']] = $this->EPtools->getStockDeliveryTerms($this->data[$field_data['name']]);
        }
        return $this->data[$field_data['name']];
    }

    function set_delivery_terms($field_data, $products_id, $message = false)
    {
        $textValue = $this->data[$field_data['name']];
        $idValue = 0;

        if ( !empty($textValue) ) {
            $idValue = $this->EPtools->lookupStockDeliveryTermId($textValue);
            if ( empty($idValue) && is_object($message) && $message instanceof Messages) {
                $message->info($field_data['value'].' - "'.$textValue.'" not found');
            }
        }

        $this->data[$field_data['name']] = $idValue;
        return $idValue;
    }

    function get_stock_indication($field_data, $products_id)
    {
        if ( empty($this->data[$field_data['name']]) ) {
            $this->data[$field_data['name']] = '';
        }else{
            $this->data[$field_data['name']] = $this->EPtools->getStockIndication($this->data[$field_data['name']]);
        }
        return $this->data[$field_data['name']];
    }

    function set_stock_indication($field_data, $products_id, $message = false)
    {
        $textValue = $this->data[$field_data['name']];
        $idValue = 0;

        if ( !empty($textValue) ) {
            $idValue = $this->EPtools->lookupStockIndicationId($textValue);
            if ( empty($idValue) && is_object($message) && $message instanceof Messages) {
                $message->info($field_data['value'].' - "'.$textValue.'" not found');
            }
        }

        $this->data[$field_data['name']] = $idValue;
        return $idValue;
    }

    function set_tax_class($field_data, $products_id, $message){
        static $fetched_map = array();
        $file_value = trim($this->data['inventory_tax_class_id']);
        if ( $file_value==='' || preg_match('/^product( tax class)?/i',$file_value) ) {
            $tax_class_id = 'null';
        }else {
            $tax_class_id = 'null';

            if (!isset($fetched_map[$file_value])) {
                if (is_numeric($file_value)) {
                    $check_number = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS cnt FROM " . TABLE_TAX_CLASS . " WHERE tax_class_id='" . (int)$file_value . "' "));
                    if (is_array($check_number) && $check_number['cnt'] > 0) {
                        $fetched_map[$file_value] = (int)$file_value;
                        $tax_class_id = (int)$file_value;
                    } elseif (is_object($message) && $message instanceof EP\Messages) {
                        $message->info("Unknown tax class - '" . \common\helpers\Output::output_string($file_value) . "' ");
                        $fetched_map[$file_value] = 'null';
                    }
                } else {
                    $get_by_name_r = tep_db_query(
                        "SELECT tax_class_id FROM " . TABLE_TAX_CLASS . " WHERE tax_class_title='" . tep_db_input($file_value) . "' LIMIT 1"
                    );
                    if (tep_db_num_rows($get_by_name_r) > 0) {
                        $get_by_name = tep_db_fetch_array($get_by_name_r);
                        $fetched_map[$file_value] = $get_by_name['tax_class_id'];
                        $tax_class_id = $get_by_name['tax_class_id'];
                    }elseif ( $file_value=='--none--' || preg_match('/^--/',$file_value) ){
                        $fetched_map[$file_value] = 0;
                        $tax_class_id = $fetched_map[$file_value];
                    } elseif (is_object($message) && $message instanceof EP\Messages) {
                        $message->info("Unknown tax class - '" . \common\helpers\Output::output_string($file_value) . "' ");
                        $fetched_map[$file_value] = 'null';
                        $tax_class_id = $fetched_map[$file_value];
                    }
                }
            } else {
                $tax_class_id = $fetched_map[$file_value];
            }
        }

        $this->data['inventory_tax_class_id'] = $tax_class_id;
        return $tax_class_id;
    }

    function get_tax_class($field_data, $products_id){
        static $fetched = false;
        if ( !is_array($fetched) ) {
            $fetched = array(
                0 => '--none--',
            );
            $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS );
            if ( tep_db_num_rows($tax_class_query)>0 ) {
                while ($tax_class = tep_db_fetch_array($tax_class_query)) {
                    $fetched[$tax_class['tax_class_id']] = $tax_class['tax_class_title'];
                }
            }
        }
        if ( is_null($this->data['inventory_tax_class_id']) ) {
            $this->data['inventory_tax_class_id'] = 'product tax class';
        }else{
            $this->data['inventory_tax_class_id'] = isset( $fetched[$this->data['inventory_tax_class_id']] )?$fetched[$this->data['inventory_tax_class_id']]:'--none--';
        }
        return $this->data['inventory_tax_class_id'];
    }
    
    function set_products_discount_price( $field_data, $products_id )
    {
        if( !empty($this->data[$field_data['name']])) {
          $ar = preg_split('/[; :]/', $this->data[$field_data['name']], -1, PREG_SPLIT_NO_EMPTY);
          $clean = '';
          if (is_array($ar) && count($ar)>0 && count($ar)%2==0) {
             $tmp = [];
             for ($i=0, $n=sizeof($ar); $i<$n; $i=$i+2) {
                $tmp[intval($ar[$i])] = floatval($ar[$i+1]);
             }
             ksort($tmp, SORT_NUMERIC);
             foreach ($tmp as $key => $value) {
               if ($key<2) { // q-ty discount should start from 2+
                 $clean = '';
                 break;
               }
               if (is_numeric($value) && $value>0) {
                 if( $this->makeNetPricesOnImport && $taxRate = $this->getTaxRate()){
                   $value = $value*(100/(100+$taxRate));
                 }
               }
               $clean .= $key . ':' .$value.';';
             }
          }
          $this->data[$field_data['name']] = $clean;

        }
        return '';
    }

    public function set_date($field_data, $products_id, $message)
    {
        $file_value = $this->data[$field_data['name']];
        if ( empty($this->data[$field_data['name']]) ) {
            unset($this->data[$field_data['name']]);
        }else{
            $this->data[$field_data['name']] = EP\Tools::getInstance()->parseDate($file_value);
            if ((int)$this->data[$field_data['name']] < 1980) {
                unset($this->data[$field_data['name']]);
                $message->info($field_data['value'] . ' "' . $file_value . '" not valid');
            }
        }
        return $this->data[$field_data['name']];
    }

    protected function getTaxRate()
    {
        $taxRate = 0;
        if ( !isset($this->data['_tax_rate']) ) {
            if (array_key_exists('inventory_tax_class_id', $this->data) && !array_key_exists('p_products_model', $this->data)) {
                if ( !is_numeric($this->data['inventory_tax_class_id']) && $this->data['inventory_tax_class_id']!=='null' ) {
                    $this->set_tax_class([],null,null);
                }
                $tax_class_id = $this->data['inventory_tax_class_id'];
                if ($tax_class_id==='null') {
                    $tax_class_id = null;
                }else{
                    //echo '<pre>'; var_dump($tax_class_id); echo '</pre>';
                }
            }else{
                $tax_class_id = $this->data['_inventory_tax_class_id'];
            }
            if ( is_null($tax_class_id) ){
                $tax_class_id = (int)$this->data['_products_tax_class_id'];
            }

//            if ( is_null($this->data['_inventory_tax_class_id']) ){
//                $tax_class_id = $this->data['_products_tax_class_id'];
//            }else{
//                $tax_class_id = $this->data['_inventory_tax_class_id'];
//            }

            if ( $tax_class_id ) {
                static $_rates = [];
                if ( !isset($_rates[$tax_class_id]) ) {
                    $_rates[$tax_class_id] = 0;
                    $defaultAddress = \Yii::$app->get('platform')->getConfig(\common\classes\platform::defaultId())->getPlatformAddress();
                    $country_id = $defaultAddress['country_id'];
                    $zone_id = $defaultAddress['zone_id'];

                    $tax_query = tep_db_query(
                        "select sum(tax_rate) as tax_rate " .
                        "from " . TABLE_TAX_RATES . " tr " .
                        "  left join " . TABLE_ZONES_TO_TAX_ZONES . " za on (tr.tax_zone_id = za.geo_zone_id) " .
                        "  left join " . TABLE_TAX_ZONES . " tz on (tz.geo_zone_id = tr.tax_zone_id) " .
                        "where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') " .
                        " and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$zone_id . "') " .
                        " and tr.tax_class_id = '" . (int)$tax_class_id . "' " .
                        "group by tr.tax_priority"
                    );

                    if (tep_db_num_rows($tax_query)) {
                        $tax_multiplier = 1.0;
                        while ($tax = tep_db_fetch_array($tax_query)) {
                            $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
                        }
                        $_rates[$tax_class_id] = ($tax_multiplier - 1.0) * 100;
                    }
                }
                $taxRate = $_rates[$tax_class_id];
            }
            $this->data['_tax_rate'] = $taxRate;
        }
        return $this->data['_tax_rate'];
    }

}