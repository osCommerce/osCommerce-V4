<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace backend\models\EP\Provider;

use backend\models\EP\Formatter;
use backend\models\EP;
use backend\models\EP\Messages;
use yii\base\Exception;

/**
 * manage XSell products.
 *
 * @author vlad
 */
class XSell extends ProviderAbstract implements ImportInterface, ExportInterface {
  protected $entry_counter;
  protected $fields = array();

  protected $data = array();
  protected $EPtools;
  protected $export_query;
  
  function init() {
      parent::init();
      $this->initFields();

      $this->EPtools = new EP\Tools();
  }

  protected function initFields() {
    $this->fields[] = array('prefix' =>'p', 'column_db' =>'products_model', 'ins_column_db' => 'products_id', 'set'=> 'set_products_id', 'name' => 'parent_products_model', 'value' => 'Products Model', 'is_key_part'=> true); //get'=>'get_tax_class'
    $this->fields[] = array('prefix' =>'p1', 'name' => 'products_model', 'ins_column_db' => 'xsell_id',  'set'=> 'set_products_id', 'value' => 'Child Products Model', 'is_key_part'=> true);
    $this->fields[] = array('prefix' =>'bs', 'name' => 'sort_order', 'value' => 'Products Sort order' );
    $this->fields[] = array('name' => 'xsell_type_id', 'value' => 'Type', 'set'=>'set_xsell_type_id', 'get'=>'get_xsell_type_id');
    $this->fields[] = array( 'name' => "'_remove'", 'value' => 'Delete (enter 1 to delete)',);
    
  }

  public function prepareExport($useColumns, $filter) {
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
        "SELECT {$main_source['select']}  bs.products_id ".
        "FROM " . TABLE_PRODUCTS . " p join " . TABLE_PRODUCTS_XSELL . " bs on p.products_id=bs.products_id join " . TABLE_PRODUCTS . " p1 on bs.xsell_id=p1.products_id " .
        "WHERE 1 {$filter_sql} ".
        "order by p.products_model".
        "/*LIMIT 3*/";
    $this->export_query = tep_db_query( $main_sql );
  }

  public function exportRow()
  {
      $this->data = tep_db_fetch_array($this->export_query);
      if ( !is_array($this->data) ) return $this->data;

      $data_sources = $this->data_sources;
      $export_columns = $this->export_columns;

      foreach( $export_columns as $db_key=>$export ) {
          if( isset( $export['get'] ) && method_exists($this, $export['get']) ) {
              $this->data[$db_key] = call_user_func_array(array($this, $export['get']), array($export, $this->data['products_id']));
          }
      }
      return $this->data;
  }


  public function postProcess(Messages $message)
  {
      $message->info('Processed '.$this->entry_counter.' products');
      $message->info('Done.');

      $this->EPtools->done('xsell_import');
  }


/**
 *
 * @param type $data
 * @param Messages $message
 * @return boolean
 * @throws EP\Exception
 */
  public function importRow($data, Messages $message)
  {
      $this->buildSources( array_keys($data) );

      $export_columns = $this->export_columns;
      $main_source = $this->main_source;
      $data_sources = $this->data_sources;
      $file_primary_columns = $this->file_primary_columns; /// columnS!!

      $this->data = $data;
      $is_updated = false;
      $need_touch_date_modify = true;
      $_is_product_added = false;

      if (!is_array($file_primary_columns ) || count($file_primary_columns )==0 ) {
          throw new EP\Exception('Primary key(s) not found in file ' . print_r($file_primary_columns,1));
      } elseif ( count(array_diff(array_keys($file_primary_columns), array_keys($data)))>0 ) {
        throw new EP\Exception('Primary key(s) missed in file ' . print_r(array_diff(array_keys($file_primary_columns), array_keys($data)),1));
      }
      $file_primary_value = [];
      $search = [];

      foreach ($file_primary_columns as $pk => $dbCol) {
        // error data empty
        if (empty($data[$pk])) {
          $message->info($pk . ' is empty. Skipped');
          return false;
        }
        $search[$pk] = " {$dbCol}='" . tep_db_input($data[$pk]) . "'";
        $file_primary_value[] = $data[$pk];
      }
      $get_main_data_r = tep_db_query(
          "SELECT  {$main_source['select']}  bs.* ".
           "FROM " . TABLE_PRODUCTS . " p join " . TABLE_PRODUCTS_XSELL . " bs on p.products_id=bs.products_id join " . TABLE_PRODUCTS . " p1 on bs.xsell_id=p1.products_id " .
          "WHERE 1 and " . implode(' and ', $search)
//              .          "GROUP BY bs.products_id"
      );

      $found_rows = tep_db_num_rows($get_main_data_r);
      if ($found_rows > 1) {
          // error data not unique
          $message->info(implode(' and ', $search) . ' not unique - found ' . $found_rows . ' rows. Skipped');
          return false;
      } elseif ($found_rows == 0) {

          $create_data_array = array();
          foreach ($main_source['columns'] as $file_column => $db_column) {

            if (!array_key_exists($file_column, $data) || $file_column=="'_remove'") continue;

            if (isset($file_primary_columns[$file_column])) {
              $db_column = $export_columns[$file_column]['ins_column_db'];
            }

            if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $db_column, $message));
            }
            
            if (isset($file_primary_columns[$file_column])) {
              $create_data_array[$db_column] = $this->data[$db_column];
            } else {
              $create_data_array[$db_column] = $this->data[$file_column];
            }
            
          }
          // delete or insert
          if (!empty($create_data_array["'_remove'"])) {
              //not found
              $message->info(' ' . implode(' ', $file_primary_value) . ' - not found');
              return false;
          } else {
            tep_db_perform(TABLE_PRODUCTS_XSELL, $create_data_array);
            $message->info('Create "' . implode(',', $search) . '"');
          }

      } else {
          // update

          $db_main_data = tep_db_fetch_array($get_main_data_r);
          $products_id = $db_main_data['products_id'];
          $update_data_array = array();
          $where_sql = '';
          foreach ($main_source['columns'] as $file_column => $db_column) {
            if (!array_key_exists($file_column, $data) || $file_column=="'_remove'") continue;
            
            if (isset($file_primary_columns[$file_column])) {
              $db_column = $export_columns[$file_column]['ins_column_db'];
            }

            if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $db_column, $message));
            }
            if (isset($file_primary_columns[$file_column])) {
              $where_sql .= ' and ' . $db_column . "='" . $this->data[$db_column] . "'";
            } else {
              $update_data_array[$db_column] = $this->data[$file_column];
            }
          }

          if (!empty($data["'_remove'"])) {
              tep_db_query("delete from " . TABLE_PRODUCTS_XSELL . " where 1 " . $where_sql);
              $message->info('deleted ' . implode(' ', $file_primary_value) . ' ');
          } else {

            if (count($update_data_array) > 0) {

              tep_db_perform(TABLE_PRODUCTS_XSELL, $update_data_array, 'update', " 1 " . $where_sql);
              $message->info('Updated "' . implode(' ', $file_primary_value) . '"');
            } else {
              $message->info('Skipped (nothing to change)"' . implode(' ', $file_primary_value) . '"');
            }
          }
      }

      return true;
  }

  function set_products_id( $field_data, $db_column, $message) {
    $ret = 0; // actually fills in $this->data
    $field_value = $this->data[$field_data['name']];
    if (!empty(trim($field_value))) {
      $ret = $this->EPtools->lookupProductId($field_value);

    }
    if ((int)$ret==0) {
      $message->info('Incorrect product: ' . $field_value . ' => ' . $db_column);
    } else {
      $this->data[$db_column] = $ret;
    }
  }


    function set_xsell_type_id($field_data, $products_id, $message){
      static $fetched_map = array();
      $file_value = trim($this->data['xsell_type_id']);
      $xsell_type_id = 0;

      if ( !isset($fetched_map[$file_value]) ) {
          if ( empty($file_value) ) {
              $xsell_type_id = 0;
          }else
              if ( is_numeric($file_value) ) {
                  $check_number = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS cnt FROM " . TABLE_PRODUCTS_XSELL_TYPE . " WHERE xsell_type_id='" . (int)$file_value . "' "));
                  if (is_array($check_number) && $check_number['cnt'] > 0) {
                      $fetched_map[$file_value] = (int)$file_value;
                      $xsell_type_id = (int)$file_value;
                  } elseif (is_object($message) && $message instanceof EP\Messages) {
                      $message->info("Unknown xsell type - '" . \common\helpers\Output::output_string($file_value) . "' ");
                      $fetched_map[$file_value] = 0;
                  }
              }else{
                  $get_by_name_r = tep_db_query(
                      "SELECT xsell_type_id FROM " . TABLE_PRODUCTS_XSELL_TYPE . " WHERE xsell_type_name='" . tep_db_input($file_value) . "' LIMIT 1"
                  );
                  if (tep_db_num_rows($get_by_name_r)>0) {
                      $get_by_name = tep_db_fetch_array($get_by_name_r);
                      $fetched_map[$file_value] = $get_by_name['xsell_type_id'];
                      $xsell_type_id = $get_by_name['xsell_type_id'];
                  } elseif (is_object($message) && $message instanceof EP\Messages) {
                      $message->info("Unknown xsell type - '" . \common\helpers\Output::output_string($file_value) . "' ");
                      $fetched_map[$file_value] = 0;
                  }
              }
      }else{
          $xsell_type_id = $fetched_map[$file_value];
      }
      $this->data['xsell_type_id'] = $xsell_type_id;
      return $xsell_type_id;
    }

    function get_xsell_type_id($field_data, $products_id){
        static $fetched = false;
        if ( !is_array($fetched) ) {
            $fetched = array();
            $tax_class_query = tep_db_query("select xsell_type_id, xsell_type_name from " . TABLE_PRODUCTS_XSELL_TYPE );
            if ( tep_db_num_rows($tax_class_query)>0 ) {
                while ($tax_class = tep_db_fetch_array($tax_class_query)) {
                    $fetched[$tax_class['xsell_type_id']] = $tax_class['xsell_type_name'];
                }
            }
        }
        $this->data['xsell_type_id'] = isset( $fetched[$this->data['xsell_type_id']] )?$fetched[$this->data['xsell_type_id']]:'';
        return $this->data['xsell_type_id'];
    }

}
