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


use backend\models\EP\Exception;
use backend\models\EP\Messages;
use backend\models\EP\Tools;
use common\models\Warehouses;
use common\models\WarehousesProducts;
use yii\db\ActiveRecord;

class WarehouseStock extends ProviderAbstract implements ImportInterface, ExportInterface
{

    protected $fields = array();

    protected $data = array();
    /**
     * @var Tools
     */
    protected $EPtools;
    protected $entry_counter = 0;
    protected $export_query;

    protected $stock_updated = 0;

    function init()
    {
        parent::init();
        $this->EPtools = new Tools();
        $this->initFields();
    }

    protected function initFields()
    {
        $this->fields[] = array('name' => 'p_products_model', 'value' => 'Main Model', 'calculated' => true, 'is_key_part'=>true);
        $this->fields[] = array('name' => 'products_model', 'value' => 'Inventory Model', 'calculated' => true, /*'is_key_part'=>true*/);
        /*
        $this->fields[] = array('name' => 'products_ean', 'value' => 'EAN', 'calculated' => true,);
        $this->fields[] = array('name' => 'products_upc', 'value' => 'UPC', 'calculated' => true,);
        $this->fields[] = array('name' => 'products_asin', 'value' => 'ASIN', 'calculated' => true,);
        $this->fields[] = array('name' => 'products_isbn', 'value' => 'ISBN', 'calculated' => true,);
        */

        /*
        $this->fields[] = array('name' => 'p_products_ean', 'value' => 'Main EAN', 'calculated' => true,);
        $this->fields[] = array('name' => 'p_products_upc', 'value' => 'Main UPC', 'calculated' => true,);
        $this->fields[] = array('name' => 'p_products_asin', 'value' => 'Main ASIN', 'calculated' => true,);
        $this->fields[] = array('name' => 'p_products_isbn', 'value' => 'Main ISBN', 'calculated' => true,);
        */
        $this->fields[] = array('name' => 'supplier', 'value' => 'Supplier', 'calculated' => true,'get'=>'get_supplier', 'set'=>'set_supplier', /*'is_key_part'=>true*/);
        $this->fields[] = array('name' => 'supplier_model', 'value' => 'Supplier Model', 'calculated' => true,);

        foreach (\common\helpers\Warehouses::get_warehouses(false) as $warehouse) {
            $warehouse['id'];
            $warehouse['text'];
            $this->fields[] = array(
                'data_descriptor' => '@|warehouse_qty|',
                'name' => 'stock_'.$warehouse['id'].'_0',
                '_warehouse_id' => $warehouse['id'],
                '_location_id' => 0,
                'value' => 'Warehouse '.$warehouse['text'],
                'calculated' => true,
                'set' => 'set_warehouse_qty',
            );

            $locationList = $this->EPtools->getWarehouseLocations($warehouse['id']);
            if ( count($locationList)>0 ) {
                foreach ($locationList as $location){
                    $this->fields[] = array(
                        'data_descriptor' => '@|warehouse_qty|',
                        'name' => 'stock_'.$warehouse['id'].'_'.$location['location_id'],
                        '_warehouse_id' => $warehouse['id'],
                        '_location_id' => $location['location_id'],
                        'value' => 'Warehouse '.$warehouse['text'].' - '.$location['complete_name'],
                        'calculated' => true,
                        'set' => 'set_warehouse_qty',
                    );
                }
            }
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

        $main_sql =
            "SELECT {$main_source['select']} IFNULL(i.products_id, p.products_id) AS products_id, ".
            " IFNULL(i.products_quantity, p.products_quantity) AS products_quantity, ".
            " IFNULL(i.products_name, pd.products_name) AS products_name, ".
            " IFNULL(i.products_model, p.products_model) AS products_model, ".
            " IFNULL(i.products_ean, p.products_ean) AS products_ean, ".
            " IFNULL(i.products_upc, p.products_upc) AS products_upc, ".
            " IFNULL(i.products_asin, p.products_asin) AS products_asin, ".
            " IFNULL(i.products_isbn, p.products_isbn) AS products_isbn, ".
            " p.products_model AS p_products_model, p.products_ean AS p_products_ean, ".
            " p.products_upc AS p_products_upc, p.products_asin AS p_products_asin, p.products_isbn AS p_products_isbn, ".
            " IFNULL(sp.suppliers_id,'".\common\helpers\Suppliers::getDefaultSupplierId()."') AS suppliers_id, ".
            " IFNULL(sp.suppliers_id,'".\common\helpers\Suppliers::getDefaultSupplierId()."') as supplier, ".
            " sp.suppliers_model ".
            "FROM ".TABLE_PRODUCTS." p ".
            " LEFT JOIN ".TABLE_INVENTORY." i ON p.products_id=i.prid ".
            " LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON pd.products_id=p.products_id AND pd.language_id='".$this->languages_id."' AND pd.platform_id='".intval(\common\classes\platform::defaultId())."' ".
            " LEFT JOIN ".TABLE_SUPPLIERS_PRODUCTS." sp ON sp.products_id=p.products_id AND sp.uprid=IFNULL(i.products_id, CONCAT('',p.products_id)) ".
            "WHERE 1 {$filter_sql} ".
            //"  AND sp.suppliers_id IS NOT NULL ".
            "  AND p.parent_products_id=0 ".
            "ORDER BY IFNULL(i.prid,p.products_id), IFNULL(i.products_id,'') ".
            "/*LIMIT 3*/";
//echo '<pre>'; var_dump($main_sql); echo '</pre>'."\n\n";
        $this->export_query = tep_db_query( $main_sql );

    }

    public function exportRow()
    {
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

    public function importRow($data, Messages $message)
    {
        global $login_id;

        $this->buildSources(array_keys($data));

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_columns = $this->file_primary_columns;
        $property_name_sources = $this->property_name_sources ?? null;
        $value_sources = $this->value_sources ?? null;


        $this->data = $data;

        foreach (array_keys($file_primary_columns) as $file_column) {
            if ( !isset($this->data[$file_column]) ) {
                throw new Exception('Primary key "' . $file_column . '" not found in file');
            }
        }

        $product_model = $this->data['p_products_model'];
        $inventory_product_model = $this->data['products_model']??'';
        
        $get_main_data_r = tep_db_query(
            "SELECT " .
            " p.products_id, " .
            " p.parent_products_id, ".
            " IFNULL(i.products_id,p.products_id) AS uprid, ".
            " i.inventory_id " .
            "FROM " . TABLE_PRODUCTS . " p " .
            " LEFT JOIN " . TABLE_INVENTORY . " i ON i.prid=p.products_id " .
            "WHERE p.products_model='".tep_db_input($product_model)."' ".
            " AND IF(i.inventory_id IS NULL, 1, i.products_model='".tep_db_input($inventory_product_model)."') "
        );
        $found_rows = tep_db_num_rows($get_main_data_r);
        if ($found_rows > 1) {
            // error data not unique
            $message->info('"'.$product_model.'" "'.$inventory_product_model.'" not unique - found '.$found_rows.' rows. Skipped');
            return false;
        } elseif ($found_rows == 0) {
            // error data not unique
            $message->info('"'.$product_model.'" "'.$inventory_product_model.'" not found. Skipped');
            return false;
        }
        $pdata = tep_db_fetch_array($get_main_data_r);
        if ( defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' ) {
            if ($pdata['parent_products_id']!=0) {
                $message->info('"'.$product_model.'" "'.$inventory_product_model.'" - sub product child. Skipped');
                return false;
            }
        }

        //$pdata['products_id'];
        $this->data['products_id'] = $pdata['uprid'];

        $suppliersForUprid = \common\helpers\Suppliers::getSuppliersList($pdata['uprid']);
        if ( count($suppliersForUprid)==0 ) {
            $suppliersForUprid[\common\helpers\Suppliers::getDefaultSupplierId()] = 'Def';
        }
        
        if ((!isset($this->data['supplier']) || $this->data['supplier'] == 'Unknown' || $this->data['supplier'] == '') && count($suppliersForUprid) > 0) {
            if (!function_exists('array_key_first')) {
                foreach($suppliersForUprid as $key => $unused) {
                    $this->data['suppliers_id'] = $key;
                    break;
                }
            } else {
            $this->data['suppliers_id'] = array_key_first($suppliersForUprid);
            }
        } else {
            $this->data['suppliers_id'] = $this->EPtools->getSupplierIdByName($this->data['supplier']);
        }
        if ( empty($this->data['suppliers_id']) ) {
            $message->info('Supplier "'.$this->data['supplier'].'" not found');
            return false;
        }
        
        if ( !isset($suppliersForUprid[$this->data['suppliers_id']]) ) {
            $message->info('Supplier "'.$this->data['supplier'].'" not assigned for "'.$product_model.'" "'.$inventory_product_model.'". Skipped');
            return false;
        }

        $this->data['warehouse_collection'] = [];
        foreach( \common\models\WarehousesProducts::find()
                     ->andWhere(['prid' => (int)$pdata['products_id']])
                     ->andWhere(['suppliers_id' => $this->data['suppliers_id']])
                     ->andWhere(['products_id' => $pdata['uprid']])
                     ->all() as $whModel )
        {
            $key = 'stock_'.(int)$whModel->warehouse_id.'_'.(int)$whModel->location_id;
            $this->data['warehouse_collection'][$key] = $whModel;
        }

        foreach (array_keys($export_columns) as $file_column) {
            if ( strpos($file_column,'stock_')!==0 ) continue;
            $db_column = $file_column;
            if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_id'], $message));
            }
        }

        $callDoCache = false;
        foreach ($this->data['warehouse_collection'] as $model){
            $qtyDelta = 0;
            /**
             * @var $model ActiveRecord
             */
            if ($model->getIsNewRecord()){
                $qtyDelta = (int)$model->warehouse_stock_quantity;
                $model->warehouse_stock_quantity = 0;
                $model->save(false);
                $model->refresh();
                $callDoCache = true;
            }else{
                if ($model->getDirtyAttributes(['warehouse_stock_quantity'])) {
                    $qtyDelta = $model->getAttribute('warehouse_stock_quantity') - $model->getOldAttribute('warehouse_stock_quantity');
                }
            }
            if ( $qtyDelta==0 ) continue;
            $callDoCache = true;
            \common\helpers\Warehouses::update_products_quantity(
                $model->products_id, $model->warehouse_id,
                abs($qtyDelta), ($qtyDelta> 0 ? '+' : '-'), $model->suppliers_id, $model->location_id,
                [
                    'comments' => 'EP Warehouse Stock feed update',
                    'admin_id' => $login_id
                ]
            );
        }
        if ( $callDoCache ) {
            \common\helpers\Product::doCache($pdata['uprid']);
        }

        $this->entry_counter++;

    }

    public function postProcess(Messages $message)
    {
        $message->info('Processed '.$this->entry_counter.' rows');
        $message->info('Done.');

        $this->EPtools->done('warehouse_stock');

    }

    public function warehouse_qty(){
        foreach (WarehousesProducts::find()
            ->where(['prid'=>(int)$this->data['products_id'], 'products_id'=>$this->data['products_id'],'suppliers_id'=>(int)$this->data['suppliers_id']])
            ->select(['warehouse_id','location_id','warehouse_stock_quantity'])
            ->asArray()
            ->all() as $warehouseData){
            $this->data['stock_'.$warehouseData['warehouse_id'].'_'.$warehouseData['location_id']] = (int)$warehouseData['warehouse_stock_quantity'];
        }
    }

    public function set_warehouse_qty($field_data, $products_id, $message){
        $key = $field_data['name'];
        $whModel = isset($this->data['warehouse_collection'][$key])?$this->data['warehouse_collection'][$key]:false;
        $quantity = intval($this->data[$field_data['name']]);
        if ( $quantity!=0 ) {
            if ( $whModel ) {
                $whModel->warehouse_stock_quantity = $quantity;
            }else{
                $whModel = new WarehousesProducts();
                $whModel->loadDefaultValues();
                $whModel->setAttributes([
                    'prid' => (int)$this->data['products_id'],
                    'products_id' => $this->data['products_id'],
                    'suppliers_id' => (int)$this->data['suppliers_id'],
                    'warehouse_id' => $field_data['_warehouse_id'],
                    'location_id' => $field_data['_location_id'],
                    'products_model' => (string)$this->data['products_model'],
                    'warehouse_stock_quantity' => $quantity,
                ],false);
                $this->data['warehouse_collection'][$key] = $whModel;
            }
        }elseif($whModel && $whModel->warehouse_stock_quantity!=$quantity){
            $whModel->warehouse_stock_quantity = $quantity;
        }
    }

    function get_supplier($field_data, $products_id){
        $value = $this->EPtools->getSupplierName(intval($this->data[$field_data['name']]));
        return $value;
    }

    function set_supplier($field_data, $products_id){
        $value = $this->data[$field_data['name']];
        if ( (string)$value!=='' ) {
            $value = $this->EPtools->getSupplierIdByName(intval($this->data[$field_data['name']]));
            $this->data[$field_data['name']] = $value;
        }
        return $value;
    }

}