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
use common\models\ProductsAssets;
use common\models\ProductsAssetsFields;
use common\models\ProductsAssetsValues;

class Assets extends ProviderAbstract implements ImportInterface, ExportInterface
{
    protected $fields = array();
    protected $data = array();
    protected $EPtools;
    
    protected $_language_id;

    protected $last_product_lookup = [];    
    protected $updated_product_ids = [];
    protected $export_query;

    function init()
    {
        parent::init();
        $this->_language_id = intval($this->languages_id);
        $this->initFields();
        $this->EPtools = new EP\Tools();
    }

    protected function initFields(){        
        $this->fields = array();
        $this->fields[] = array( 'name' => 'products_model', 'calculated'=>true, 'value' => 'Products Model', 'is_key'=>true,);
        $this->fields[] = array( 'name' => 'products_name', 'calculated'=>true, 'value' => 'Products Name', );
        $this->fields[] = array( 'name' => 'warehouse_id', 'calculated'=>true, 'value' => 'Warehouse Name', );
        $this->fields[] = array( 'name' => 'products_assets_fields_name', 'calculated'=>true, 'value' => 'Field', );
        $this->fields[] = array( 'name' => 'products_assets_value', 'calculated'=>true, 'value' => 'Value', );
    }

    public function prepareExport($useColumns, $filter)
    {
        $this->buildSources($useColumns);
        
        $main_source = $this->main_source;

        $filter_sql = "";
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
            "SELECT pa.products_assets_id, p.products_id, pa.warehouse_id, pa.suppliers_id, ifnull(i.products_model, p.products_model) as products_model, paf.products_assets_fields_name, pav.products_assets_value, if(i.products_id,1,0) as is_uprid, pa.uprid " .
            "FROM ".ProductsAssets::tableName()." pa ".
            " LEFT JOIN " . ProductsAssetsFields::tableName() . " paf ON paf.language_id='".$this->_language_id."' " .
            " LEFT JOIN " . ProductsAssetsValues::tableName() . " pav ON pav.products_assets_id=pa.products_assets_id AND paf.products_assets_fields_id = pav.products_assets_fields_id" .
            " INNER JOIN " . TABLE_PRODUCTS . " p on p.products_id = pa.products_id " .
            " LEFT JOIN " . TABLE_INVENTORY . " i on pa.uprid = i.products_id " .
            " WHERE pa.orders_id = 0 and 1 {$filter_sql} ".
            " ORDER BY p.products_id, pa.products_assets_id ";

        $this->export_query = tep_db_query( $main_sql );
    }
    
    public function exportRow()
    {
        $this->data = tep_db_fetch_array($this->export_query);
        if ( !is_array($this->data) ) return $this->data;
        
        $data_sources = $this->data_sources;
        $export_columns = $this->export_columns;
        
        $this->data['warehouse_id'] = \common\helpers\Warehouses::get_warehouse_name($this->data['warehouse_id']);
        $this->data['products_name'] = \common\helpers\Product::get_products_name($this->data['products_id'],$this->_language_id);
        if ($this->data['is_uprid']){
            $this->data['products_name'] .= " " . \common\helpers\Inventory::get_inventory_name_by_uprid($this->data['uprid']);
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
            foreach( array('products_model', 'products_name','products_assets_fields_name', 'products_assets_value') as $required_file_column ) {
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
            
            $get_main_data_r = \common\models\Products::find()
                    ->select(['products_id', 'stock_indication_id'])
                    ->where(['products_model' => $file_primary_value]);
            $found_rows = $get_main_data_r->count();
            $isInventory = false;            
            if (!$found_rows){
                $get_main_data_r = \common\models\Inventory::find()
                    ->select(['products_id', 'stock_indication_id'])
                    ->where(['products_model' => $file_primary_value]);
                $found_rows = $get_main_data_r->count();
                $isInventory = $found_rows > 0;
            }
            $this->last_product_lookup = array(
                $file_primary_value => array(
                    'found_rows' => $found_rows,
                    'data' => $found_rows>0?$get_main_data_r->one():false,
                    'isInventory' => $isInventory
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
            $products_id = $db_main_data->products_id;
            if (\common\helpers\Attributes::has_product_attributes($products_id) && \common\helpers\Extensions::isAllowed('Inventory') && !$isInventory){
                $products_id = \common\helpers\Inventory::get_first_invetory($products_id);
                $db_main_data = \common\models\Inventory::find()->where(['products_id' => $products_id, 'prid' => (int)$products_id])->one();
                ProductsAssets::updateAll(['uprid' => $products_id], ['products_id' => (int)$products_id, 'uprid' => (int)$products_id]);
            }
            $isInventory = $this->last_product_lookup[$file_primary_value]['isInventory'];
        }
        
        $this->data['products_id'] = $products_id;
        $this->data['warehouse_id'] = $this->getWarehouseId($this->data['warehouse_id']);
        $this->data['field_id'] = $this->getField($this->data['products_assets_fields_name']);
        $this->data['products_assets_value'] = trim($this->data['products_assets_value']);
        if (!$this->data['field_id']){
            $message->info('Asset field name incorrect. Skipped');
            return false;
        }
        
        if (!isset($this->updated_product_ids[(int)$products_id."_".$products_id])){
            $this->updated_product_ids[(int)$products_id."_".$products_id] = [];            
        }
        
        $tmp = [
            'field_id' => $this->data['field_id'],
            'value' => $this->data['products_assets_value'],
            'db_data' => $db_main_data,
        ];
        
        $assets = ProductsAssets::find()->where(['products_id' => (int)$products_id, 'uprid'=> $products_id])
                ->joinWith('assetValues')->all();
        
        $insert = false;
        if ($assets){
            $found = false;
            foreach($assets as $asset){
                if ($this->data['products_assets_value'] == $asset->assetValues[0]->products_assets_value){
                    $found = $asset;
                    $tmp['asset_id'] = $asset->products_assets_id;
                    break;
                }
            }
            unset($assets);
            if ($found){
                $found->warehouse_id = $this->data['warehouse_id'];
                $found->suppliers_id = 0;
                $found->save(false);
            } else {
                $insert = true;
            }
        } else {
            $insert = true;            
        }
        
        if ($insert){
            $productAsset = new ProductsAssets();
            $productAsset->setAttributes([
                'products_id' => (int)$this->data['products_id'],
                'uprid' => $this->data['products_id'],
                'warehouse_id' => $this->data['warehouse_id'],
                'suppliers_id' => 0,
                'orders_id' => 0,
            ], false);
            $productAsset->insert(false);
            $productAssetValue = new ProductsAssetsValues();
            $productAssetValue->products_assets_fields_id = $this->data['field_id'];
            $productAssetValue->products_assets_value = $this->data['products_assets_value'];
            $productAsset->link('assetValues', $productAssetValue);
            $tmp['asset_id'] = $productAsset->products_assets_id;
            unset($productAsset);
            unset($productAssetValue);
        }
        
        $this->updated_product_ids[(int)$products_id."_".$products_id][] = $tmp;
        
        return true;
    }
    
    private function getWarehouseId($warehouseName = ''){
        static $warehouses = [];
        static $default = null;
        if (!$warehouses){
            $warehouses = \yii\helpers\ArrayHelper::index(\common\helpers\Warehouses::get_warehouses(true), 'text', 'id');
            $default = \common\helpers\Warehouses::get_default_warehouse();
        }
        if ($warehouseName && isset($warehouses[$warehouseName])){
            return $warehouses[$warehouseName];
        } else {
            return $default;
        }
    }
    
    private function getField(string $fieldName = ''){
        static $fields = [];
        if ($fieldName){
            if (!isset($fields[$fieldName])){
                $aField = ProductsAssetsFields::find()->where(['products_assets_fields_name' => $fieldName, 'language_id' => $this->_language_id])->one();
                if (!$aField){
                    foreach(\common\helpers\Language::get_languages(true) as $language){
                        $af = new ProductsAssetsFields();
                        $af->setAttributes([
                            'language_id' => $language['id'],
                            'products_assets_fields_name' => $fieldName,
                            'date_added' => new \yii\db\Expression("now()"),
                        ], false);
                        $af->insert(false);
                    }
                    unset($af);
                    if (!$aField) {
                        $aField = ProductsAssetsFields::find()->where(['products_assets_fields_name' => $fieldName, 'language_id' => $this->_language_id])->one();
                    }
                }
                $fields[$fieldName] = $aField->products_assets_fields_id;
            }
        }
        return $fields[$fieldName] ?? false;
    }

    public function postProcess(Messages $message)
    {
        $this->endingProcess();
        $message->info('Done');

        $this->EPtools->done('properties_import');
    }
    
    private function endingProcess(){
        if ($this->updated_product_ids){
            $ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed');
            foreach ($this->updated_product_ids as $pridsKey => $data){
                $ex = explode("_", $pridsKey);
                $prid = (int)$ex[0];
                $uprid = $ex[1];
                $ids = \yii\helpers\ArrayHelper::getColumn($data, 'asset_id');
                foreach(ProductsAssets::find()->where(['and', ['orders_id' => 0, 'products_id' => $prid, 'uprid' => $uprid ], ['not in', 'products_assets_id', $ids] ])->all() as $toDelete){
                    $toDelete->delete();
                }
                unset($toDelete);
                if ($ext){                    
                    if (is_object($data[0]['db_data'])){
                        try{
                            $response = $ext::checkStock($data[0]['db_data']->products_id, true);
                        } catch (\Exception $ex) {                            
                        }
                    }
                }
            }
            unset($data);
            $this->updated_product_ids = [];
        }
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

    public static function isExportAvailable()
    {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')){
            return $ext::allowed();
        }
        return false;
    }
    
    public static function isImportAvailable()
    {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')){
            return $ext::allowed();
        }
        return false;
    }

}