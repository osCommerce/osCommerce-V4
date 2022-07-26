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
use yii\base\Exception;

class Stock extends ProviderAbstract implements ImportInterface, ExportInterface
{
    protected $fields = array();

    protected $data = array();
    protected $EPtools;
    protected $entry_counter = 0;
    protected $export_query;

    protected $stock_updated = 0;

    function init()
    {
        parent::init();
        $this->initFields();

        $this->EPtools = new EP\Tools();
    }

    public static function isImportAvailable()
    {
        $allowImport = true;
        $warehouses = \common\helpers\Warehouses::get_warehouses(false);
        if ( count($warehouses)>1 ) {
            $allowImport = false;
        }elseif(\common\helpers\Suppliers::getSuppliersCount(false)>1){
            $allowImport = false;
        }
        return $allowImport;
    }

    protected function initFields()
    {

        $this->fields[] = array('name' => 'products_id', 'value' => 'STOCK_KEY', 'prefix' => 'i', 'is_key' => true);
        $this->fields[] = array('name' => 'products_quantity', 'value' => 'Quantity', 'calculated' => true, );
        $this->fields[] = array('name' => 'products_name', 'value' => 'Name', 'calculated' => true, );
        $this->fields[] = array('name' => 'products_model', 'value' => 'Model', 'calculated' => true,);
        /*
        $this->fields[] = array('name' => 'products_ean', 'value' => 'EAN', 'calculated' => true,);
        $this->fields[] = array('name' => 'products_upc', 'value' => 'UPC', 'calculated' => true,);
        $this->fields[] = array('name' => 'products_asin', 'value' => 'ASIN', 'calculated' => true,);
        $this->fields[] = array('name' => 'products_isbn', 'value' => 'ISBN', 'calculated' => true,);
        */
        $this->fields[] = array('name' => 'p_products_model', 'value' => 'Main Model', 'calculated' => true,);
        /*
        $this->fields[] = array('name' => 'p_products_ean', 'value' => 'Main EAN', 'calculated' => true,);
        $this->fields[] = array('name' => 'p_products_upc', 'value' => 'Main UPC', 'calculated' => true,);
        $this->fields[] = array('name' => 'p_products_asin', 'value' => 'Main ASIN', 'calculated' => true,);
        $this->fields[] = array('name' => 'p_products_isbn', 'value' => 'Main ISBN', 'calculated' => true,);

        $get_options_r = tep_db_query(
            "SELECT products_options_id, products_options_name " .
            "FROM " . TABLE_PRODUCTS_OPTIONS . " " .
            "WHERE language_id='" . $this->languages_id . "' " .
            "ORDER BY products_options_sort_order, products_options_name "
        );
        if (tep_db_num_rows($get_options_r) > 0) {
            while ($_option = tep_db_fetch_array($get_options_r)) {
                $this->fields[] = array(
                    'name' => 'option_' . $_option['products_options_id'],
                    'value' => 'Option - ' . $_option['products_options_name'],
                    'calculated' => true,
                    'get' => 'get_products_option_value',
                );
            }
        }
        */
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
            " i.inventory_price AS products_price_def/*, i.products_price_discount AS i.products_price_discount_def*/ ".
            "FROM ".TABLE_PRODUCTS." p ".
            " LEFT JOIN ".TABLE_INVENTORY." i ON p.products_id=i.prid ".
            " LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON pd.products_id=p.products_id AND pd.language_id='".$this->languages_id."' AND pd.platform_id='".intval(\common\classes\platform::defaultId())."' ".
            "WHERE 1 {$filter_sql} ".
            "  AND p.parent_products_id=0 ".
            "ORDER BY IFNULL(i.prid,p.products_id), IFNULL(i.products_id,'') ".
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
                if ( 0  ) {

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
        global $login_id;
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
        if ( !array_key_exists('products_quantity', $this->data) ){
            throw new EP\Exception('Quantity field not found in file');
        }
        $file_primary_value = $this->data[$file_primary_column];
        $get_main_data_r = tep_db_query(
            "SELECT " .
            " p.products_id, p.products_quantity AS p_qty, " .
            " i.inventory_id, i.products_quantity AS i_qty, i.products_id AS uprid " .
            "FROM " . TABLE_PRODUCTS . " p " .
            " LEFT JOIN " . TABLE_INVENTORY . " i ON i.prid=p.products_id " .
            "WHERE IFNULL(i.products_id, p.products_id)='".tep_db_input($file_primary_value)."' "
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
                    "WHERE 1 AND (".implode(" OR ",$lookupProductConditions).") "
                );
                if ( tep_db_num_rows($get_product_r)==1 ){
                    $_product = tep_db_fetch_array($get_product_r);
                    $uprid = \common\helpers\Inventory::get_uprid($_product['products_id'], $file_assigned_options);
                    $get_main_data_r = tep_db_query(
                        "SELECT * FROM " . TABLE_INVENTORY . " WHERE products_id='" . tep_db_input($uprid) . "'"
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

        if ( !is_array($db_main_data) ){
            $message->info('"' . $file_primary_value . '" not found. Skipped');
            return false;
        }

        $this->entry_counter++;

        $file_quantity = $this->data['products_quantity'];
        $quantity = (int)$file_quantity;
        if ($db_main_data['inventory_id']) {
            //if ((int)$db_main_data['i_qty'] != (int)$file_quantity) {
                //$products_quantity_update = (int)$file_quantity - (int)$db_main_data['i_qty'];
                //if ($products_quantity_update != 0) {
                if ($quantity >= 0) {
                    $inventoryId = trim($db_main_data['uprid']);
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
                            if ($quantity >= 0) {
                                \common\helpers\Product::doCache($inventoryId);
                                $this->stock_updated ++;
                            }
                            break 2;
                        }
                    }
                    /*\common\helpers\Product::log_stock_history_before_update(
                        $db_main_data['uprid'], abs($products_quantity_update), $products_quantity_update > 0 ? '+' : '-',
                        ['comments' => 'EP Stock feed update', 'admin_id' => (isset($_SESSION) && isset($_SESSION['login_id'])?$_SESSION['login_id']:0)]
                    );*/
                }
                /*tep_db_query(
                    "UPDATE " . TABLE_INVENTORY . " " .
                    "SET products_quantity='" . (int)$file_quantity . "' " .
                    "WHERE inventory_id='{$db_main_data['inventory_id']}'"
                );
                tep_db_query(
                    "UPDATE " . TABLE_PRODUCTS . " p " .
                    " INNER JOIN (" .
                    "   SELECT i.prid, SUM(IF(i.products_quantity>0 AND IFNULL(i.non_existent,0)=0,i.products_quantity,0)) AS summary_quantity FROM " . TABLE_INVENTORY . " i WHERE i.prid='" . $db_main_data['products_id'] . "' GROUP BY i.prid " .
                    " ) isum ON isum.prid=p.products_id " .
                    "SET p.products_quantity = IFNULL(isum.summary_quantity,0) " .
                    "WHERE p.products_id='" . $db_main_data['products_id'] . "'"
                );

                $this->stock_updated++;

            }*/
        } elseif ($db_main_data['products_id']) {
            //if ((int)$db_main_data['p_qty'] != (int)$file_quantity) {
                //$products_quantity_update = (int)$file_quantity - (int)$db_main_data['p_qty'];
                //if ($products_quantity_update > 0) {
                if ($quantity >= 0) {
                    $productId = trim((int)$db_main_data['products_id']);
                    \common\models\WarehousesProducts::deleteAll([
                        'prid' => $productId,
                        'products_id' => $productId
                    ]);
                    $locationId = 0;
                    foreach (\common\helpers\Product::getWarehouseIdPriorityArray($productId, $quantity, false) as $warehouseId) {
                        foreach (\common\helpers\Product::getSupplierIdPriorityArray($productId) as $supplierId) {
                            $quantity = \common\helpers\Warehouses::update_products_quantity($productId, $warehouseId, $quantity, ($quantity > 0 ? '+' : '-'), $supplierId, $locationId, [
                                'comments' => 'EP Stock feed update',
                                'admin_id' => $login_id
                            ]);
                            if ($quantity >= 0) {
                                \common\helpers\Product::doCache($productId);
                                $this->stock_updated ++;
                            }
                            break 2;
                        }
                    }
                    /*\common\helpers\Product::log_stock_history_before_update(
                        $db_main_data['products_id'], abs($products_quantity_update), $products_quantity_update > 0 ? '+' : '-',
                        ['comments' => 'EP Stock feed update', 'admin_id' => (isset($_SESSION) && isset($_SESSION['login_id'])?$_SESSION['login_id']:0)]
                    );*/
                }
                /*tep_db_query(
                    "UPDATE " . TABLE_PRODUCTS . " " .
                    "SET products_quantity='" . (int)$file_quantity . "' " .
                    "WHERE products_id='{$db_main_data['products_id']}'"
                );
                $this->stock_updated++;
            }*/
        }
    }

    public function postProcess(Messages $message)
    {
        $message->info("Matched products {$this->entry_counter} and stock updated {$this->stock_updated}");
    }

    function get_products_option_value($field_data, $products_id)
    {
        if (strpos($field_data['name'],'option_')!==0) return '';
        $id = intval(substr($field_data['name'],7));
        if ( strpos($products_id, '{'.$id.'}' )!==false && preg_match('/\{'.$id.'\}(\d+)/',$products_id, $matchValueId) ){
            return $this->EPtools->get_option_value_name($matchValueId[1], $this->languages_id);
        }
    }

}