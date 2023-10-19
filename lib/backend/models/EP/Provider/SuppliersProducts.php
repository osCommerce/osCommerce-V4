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
use \common\helpers\Currencies;
use common\models\SuppliersProducts as sModel;

class SuppliersProducts extends ProviderAbstract implements ImportInterface, ExportInterface
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
        $this->fields[] = array('name' => 'suppliers_id', 'value' => 'Suppliers name', 'is_key_part' => true, 'set'=>'set_supplier', 'get'=>'get_supplier',);
        $this->fields[] = array('prefix'=>'p', 'name' => 'products_model', 'value' => 'Products Model', 'is_key_part' => true, 'set'=>'set_product', );
        $this->fields[] = array('prefix'=>'i', 'name' => 'products_name', 'value' => 'Products Name', ); //'get' => 'get_products_name'

        $this->fields[] = array('name' => 'suppliers_model', 'value' => 'Suppliers Model'); //'prefix'=>'sp',
        $this->fields[] = array('name' => 'suppliers_quantity', 'value' => 'Q-ty', 'type' => 'int');
        $this->fields[] = array('name' => 'status', 'value' => 'Status (put active first)', 'type' => 'int');
        $this->fields[] = array('name' => 'suppliers_price', 'value' => 'Cost', 'type' => 'numeric');
        $this->fields[] = array('name' => 'suppliers_price_discount', 'value' => 'Qty Discount', 'type' => 'price_table');
        $this->fields[] = array('name' => 'currencies_id', 'value' => 'Currency code', 'set'=>'set_currency', 'get'=>'get_currency',);
        $this->fields[] = array('name' => 'supplier_discount', 'value' => 'Discount', 'type' => 'numeric');
        $this->fields[] = array('name' => 'suppliers_surcharge_amount', 'value' => 'Surchase amount', 'type' => 'numeric');
        $this->fields[] = array('name' => 'suppliers_margin_percentage', 'value' => 'Marging percentage', 'type' => 'numeric');
        $this->fields[] = array('name' => 'suppliers_product_name', 'value' => 'Suppliers product name');
        $this->fields[] = array('prefix'=>'sp', 'name' => 'source', 'value' => 'Source');
        $this->fields[] = array('name' => 'suppliers_upc', 'value' => 'Suppliers UPC');
        $this->fields[] = array('name' => 'suppliers_asin', 'value' => 'Suppliers ASIN');
        $this->fields[] = array('name' => 'suppliers_isbn', 'value' => 'Suppliers ISBN');
        $this->fields[] = array('name' => 'suppliers_ean', 'value' => 'Suppliers EAN');
        $this->fields[] = array('name' => 'price_with_tax', 'value' => 'Price with TAX?');
        $this->fields[] = array('name' => 'tax_rate', 'value' => 'Tax rate');

    }

    public function importOptions()
    {
        $options = [];
        $options[] = [
          'title' => TEXT_PROCESS_AS,
          'description' => TEXT_PROCESS_AS_DESCRIPTION,
          'name' => 'import_config[insert_new]',
          'value' => isset($this->import_config['insert_new'])?$this->import_config['insert_new']:'insert',
          'values' => ['insert' => TEXT_INSERT_UPDATE, 'delete' => IMAGE_DELETE]
        ];

        return \Yii::$app->view->render('@app/views/easypopulate/import-options',[
                        'options' => $options,
                    ]);
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
            "SELECT {$main_source['select']} "
            . " ifnull(i.products_model, p.products_model) as products_model, concat(pd.products_name, ' ', ifnull(i.products_name, '')) as products_name " .
            "FROM " . TABLE_SUPPLIERS_PRODUCTS . " sp "
                . " join " . TABLE_PRODUCTS . " p on p.products_id=sp.products_id left join " . TABLE_INVENTORY . " i on sp.products_id=i.prid and sp.uprid=i.products_id "
                . " join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id=pd.products_id and pd.language_id ='" . (int)$this->languages_id . "' and pd.platform_id='" . \common\classes\platform::defaultId() . "'" .
            "WHERE 1 ".
            " {$filter_sql} ".
            "  AND p.parent_products_id=0 ".
            "ORDER BY sp.suppliers_id, sp.products_id, sp.uprid ";

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
                $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_id'] ?? null));
            }
        }

        return $this->data;
    }

    public function importRow($data, Messages $message) {

        $this->buildSources(array_keys($data));

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_columns = $this->file_primary_columns; /// columnS!!

        $insertDelete = 'insert';
        if (is_array($this->import_config) && isset($this->import_config['insert_new']) && $this->import_config['insert_new']=='delete'){
            $insertDelete = 'delete';
        }

        $this->data = $data;

        if (!is_array($file_primary_columns ) || count($file_primary_columns )==0 ) {
            throw new EP\Exception('Primary key(s) not found in file ' . print_r($file_primary_columns,1));
        } elseif ( count(array_diff(array_keys($file_primary_columns), array_keys($data)))>0 ) {
          throw new EP\Exception('Primary key(s) missed in file ' . print_r(array_diff(array_keys($file_primary_columns), array_keys($data)),1));
        }


        $tmp = [];
        foreach ($file_primary_columns as $pk => $dbCol) {
          // error data empty
          if (empty($data[$pk])) {
            $message->info($pk . ' is empty. Skipped');
            return false;
          }
          $tmp[] = $data[$pk];

        }
        $file_primary_value_text = implode(', ', $tmp);


        $update_data_array = array();
        foreach ($main_source['columns'] as $file_column => $db_column) {
            if (!array_key_exists($file_column, $this->data)) continue;

            if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], 0, $message));
            }

            $update_data_array[$db_column] = $this->data[$file_column];
        }
//$message->info("data #### <PRE>" .print_r($this->data, 1) ."</PRE>");


        try {
          $pk = [];
          foreach (['suppliers_id', 'products_id', 'uprid'] as $key) {
            if (empty($this->data[$key])) {
              $message->info($key. ' is empty. ' . $file_primary_value_text . ' Skipped');
              return false;
            }
            $pk[$key] = $this->data[$key];
          }
          $records = sModel::find()->where($pk);
//echo $records->createCommand()->getRawSql();die;
          $recordOk = false;
          if ($records->count() > 1 ) {
            //second chance - supplier could have same product with different models :(
            if (!empty($update_data_array['suppliers_model'])) {
              $records = sModel::find()->where(array_merge($pk, ['suppliers_model' => $update_data_array['suppliers_model']]));
              if ($records && $records->count()<=1) {
                $recordOk = true;
              }
            }
          } else {
            $recordOk = true;
          }

          if (!$recordOk) {
            $message->info('"' . $file_primary_value_text . '" not unique - found '. $records->count() . ' rows. Skipped');
            return false;

          } else {

            $record = $records->one();
            if ($insertDelete == 'delete') {
              if (is_null($record)) {
                // not found
                $message->info('"' . $file_primary_value_text . '" not found');
                return false;
              } else {
                if ($qty = $record->deleteSupplierProduct() ) {
                  $message->info('"' . $file_primary_value_text . '" has been deleted ' . ($qty>1?$qty:''));
                  $this->entry_counter++;
                } else {
                  $message->info('"' . $file_primary_value_text . '" can\'t be deleted');
                }
              }
            } else {
              if (is_null($record)) {
                //$record  = new sModel;
                $record = new sModel(['scenario' => 'insert']);
                if (!isset($update_data_array['status']) || $update_data_array['status']==0) {
                  $update_data_array['status'] = 1;
                }
                $update_data_array = array_merge($update_data_array, $pk);

              } else {
                $record->scenario = 'update';
              }
              if ($record->saveSupplierProduct($update_data_array, true) ) {
                //$record->load($update_data_array, '') && $record->save()) {// now the following is not good (reset columns if not specified)
  //ToDo something in AR (default + status) and double validation
                 $this->entry_counter++;
              } else {
                $message->info('"' . $file_primary_value_text . '" ' . (count($record->getErrors())>0?print_r($record->getErrors(),1):'') .  ' Skipped');
                return false;
              }
            }

          }
        } catch (Exception $e) {
            $message->info('"' . $file_primary_value_text . '"' . print_r($e->getMessage(),1) .  ' Skipped');
            return false;
        }
        if ($this->data['products_id'] ?? null) {
            tep_db_perform(TABLE_PRODUCTS, array(
                'products_last_modified' => 'now()',
            ), 'update', "products_id='" . (int)$this->data['products_id'] . "'");
        }

        return true;

    }

    public function postProcess(Messages $message)
    {
        $message->info('Processed '.$this->entry_counter.' rows');

        $message->info('Done');

    }

    function get_currency($field_data){
      $ret = false;
      if (!empty($this->data[$field_data['name']])) {
        $ret = Currencies::getCurrencyCode($this->data[$field_data['name']]);
      }
      if( $ret === false ) {
        $ret = Currencies::systemCurrencyCode();
      }
      return $ret;
    }

    function set_currency($field_data, $products_id){
        if( $this->data[$field_data['name']]==='' ) {
          $this->data[$field_data['name']] = Currencies::getCurrencyId(Currencies::systemCurrencyCode());
        } else {
          $code = $this->data[$field_data['name']];
          $this->data[$field_data['name']] = Currencies::getCurrencyId($code);
          if ($this->data[$field_data['name']] === false) {
            $this->data[$field_data['name']] = Currencies::getCurrencyIdByName($code);
          }
          if ($this->data[$field_data['name']] === false) {
            $this->data[$field_data['name']] = Currencies::getCurrencyId(Currencies::systemCurrencyCode());
          }
        }
        return $this->data[$field_data['name']];
    }

    function get_supplier($field_data, $products_id){
      $file_value = intval($this->data[$field_data['name']]);
      $tmp = \common\helpers\Suppliers::getSupplierName($file_value);
      if (!is_null($tmp) && !empty($tmp)) {
        $ret = $tmp;
      } else {
        $ret = 'ERROR: Empty Supplier name "' . $file_value . '" Row couldnt be imported';
      }
      return $ret;
    }

    function set_product($field_data, $products_id, $message) {
      $file_value = trim($this->data[$field_data['name']]);
      $inv = \common\models\Inventory::find()->where(['products_model' => $file_value]);
      $p = \common\models\Products::find()->where(['products_model' => $file_value]);
      if (
          (is_null($p) && is_null($inv) ) ||
          (!is_null($p) && $p->count()>1 ) ||
          (!is_null($inv) && $inv->count()>1 ) ||
          (!is_null($inv) && $inv->count()==1 && !is_null($p) && $p->count()==1 )
          )
      {
        $message->info('Not unique model "' . $file_value . '". ');
        $ret = false;
      }
      if (!is_null($p) && $p->count()==1) {
        $d = $p->one();
        if ($d && $d->products_id>0) {
          $this->data['products_id'] = $this->data['uprid'] = $d->products_id;
        } else {
          $message->info('Product not found "' . $file_value . '". ');
          $ret = false;
        }
      } else {
        $d = $inv->one();
        if ($d && $d->prid>0 && !empty($d->products_id)) {
          $this->data['products_id']  = $d->prid;
          $this->data['uprid'] = $d->products_id;
        } else {
          $message->info('Variation not found "' . $file_value . '". ');
          $ret = false;
        }
      }

    }

    function set_supplier($field_data, $products_id, $message){
      static $fetched_map = array();
      $file_value = trim($this->data[$field_data['name']]);

      $ret = false;
      if ( !isset($fetched_map[$file_value]) ) {
        $tmp = \common\helpers\Suppliers::getSupplierIdByName($file_value);
        if (!is_null($tmp) && (int)$tmp>0) {
          $fetched_map[$file_value] = $ret = $tmp;
        } else {
          $message->info('Supplier "' . $file_value . '" not found');
          $ret = false;
        }
      }
      $ret = $this->data[$field_data['name']] = $fetched_map[$file_value];
      return $ret;
    }

}