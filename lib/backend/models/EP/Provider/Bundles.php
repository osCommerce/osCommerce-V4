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
 * manage Bundles products.
 *
 * @author vlad
 */
class Bundles extends ProviderAbstract implements ImportInterface, ExportInterface {
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
    $this->fields[] = array('prefix' =>'p', 'column_db' =>'products_model', 'ins_column_db' => 'sets_id', 'set'=> 'set_products_id', 'name' => 'parent_products_model', 'value' => 'Products Model', 'is_key_part'=> true); //get'=>'get_tax_class'
    $this->fields[] = array('prefix' =>'p', 'name' => 'use_sets_discount', 'value' => 'Use Discount Percent instead parent fixed price' );
    $this->fields[] = array('prefix' =>'p', 'name' => 'products_sets_discount', 'value' => 'Parent Discount Percent' );
    $this->fields[] = array('prefix' =>'p', 'name' => 'products_sets_price_formula', 'value' => 'Parent Price Formula' );
    $this->fields[] = array('prefix' =>'p', 'name' => 'disable_children_discount', 'value' => 'Disable child discount' );
    $this->fields[] = array('prefix' =>'p', 'name' => 'bundle_volume_calc', 'value' => 'Volume Weight Rule', 'set'=> 'setVolumeWeightRule', 'get'=>'getVolumeWeightRule' );
    $this->fields[] = array('prefix' =>'p1', 'name' => 'products_model', 'ins_column_db' => 'product_id',  'set'=> 'set_products_id', 'value' => 'Child Products Model', 'is_key_part'=> true);
    $this->fields[] = array('prefix' =>'bs', 'name' => 'num_product', 'value' => 'Child Products Quantity', 'type' => 'int' );
    $this->fields[] = array('prefix' =>'bs', 'name' => 'sort_order', 'value' => 'Child Sort order' );
    //$this->fields[] = array('prefix' =>'bs', 'name' => 'price', 'value' => 'Price' );
    $this->fields[] = array('prefix' =>'bs', 'name' => 'discount', 'value' => 'Child Discount' );
    $this->fields[] = array('prefix' =>'bs', 'name' => 'price_formula', 'value' => 'Child Price Formula' );
    
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
        "SELECT {$main_source['select']}  bs.sets_id ".
        "FROM " . TABLE_PRODUCTS . " p join " . TABLE_SETS_PRODUCTS . " bs on p.products_id=bs.sets_id join " . TABLE_PRODUCTS . " p1 on bs.product_id=p1.products_id " .
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

      $this->EPtools->done('upsell_import');
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
      $products_columns = array_filter($export_columns, function($data){
          return isset($data['prefix']) && $data['prefix']=='p' && !isset($data['is_key_part']);
      });

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
      //$file_primary_value = $data[$file_primary_column];
      $search = [];

      foreach ($file_primary_columns as $pk => $dbCol) {
        // error data empty
        if (empty($data[$pk])) {
          $message->info($pk . ' is empty. Skipped');
          return false;
        }
        $search[$pk] = " {$dbCol}='" . tep_db_input($data[$pk]) . "'";
      }
      $get_main_data_r = tep_db_query(
          "SELECT  {$main_source['select']}  bs.* ".
           "FROM " . TABLE_PRODUCTS . " p join " . TABLE_SETS_PRODUCTS . " bs on p.products_id=bs.sets_id join " . TABLE_PRODUCTS . " p1 on bs.product_id=p1.products_id " .
          "WHERE 1 and " . implode(' and ', $search) .
          "GROUP BY bs.sets_id"
      );

      $found_rows = tep_db_num_rows($get_main_data_r);
      if ($found_rows > 1) {
          // error data not unique
          $message->info(implode(' and ', $search) . ' not unique - found ' . $found_rows . ' rows. Skipped');
          return false;
      } elseif ($found_rows == 0) {

          $create_data_array = array();
          foreach ($main_source['columns'] as $file_column => $db_column) {
            if ( isset($products_columns[$file_column]) ) continue;
            if (!array_key_exists($file_column, $data)) continue;

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

          tep_db_perform(TABLE_SETS_PRODUCTS, $create_data_array);
          $parent_product_id = $create_data_array['sets_id'];

          // {{ main product update
          $product_data_array = array();
          foreach ($products_columns as $file_column=>$column_info){
              if (!array_key_exists($file_column, $this->data)) continue;
              if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                  call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $file_column, $message));
              }
              $product_data_array[$file_column] = $this->data[$file_column];
          }
          if ( count($product_data_array)>0 ){
              tep_db_perform(TABLE_PRODUCTS,$product_data_array,'update',"products_id='".$parent_product_id."'");
          }
          // }} main product update

          $message->info('Create "' . implode(',', $search) . '"');
      } else {
          // update

          $db_main_data = tep_db_fetch_array($get_main_data_r);
          $products_id = $db_main_data['products_id'];
          $update_data_array = array();
          $where_sql = '';
          foreach ($main_source['columns'] as $file_column => $db_column) {
            if ( isset($products_columns[$file_column]) ) continue;
            if (!array_key_exists($file_column, $data)) continue;
            
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

          if (count($update_data_array) > 0) {

            tep_db_perform(TABLE_SETS_PRODUCTS, $update_data_array, 'update', " 1 " . $where_sql);
            $message->info('Updated "' . implode(',', $search) . '"');
          } else {
            $message->info('Skipped (nothing to change)"' . implode(',', $search) . '"');
          }

          $parent_product_id = $this->data['sets_id'];

          // {{ main product update
          $product_data_array = array();
          foreach ($products_columns as $file_column=>$column_info){
              if (!array_key_exists($file_column, $this->data)) continue;
              if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                  call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $file_column, $message));
              }
              $product_data_array[$file_column] = $this->data[$file_column];
          }
          if ( count($product_data_array)>0 ){
              tep_db_perform(TABLE_PRODUCTS,$product_data_array,'update',"products_id='".$parent_product_id."'");
          }
          // }} main product update
      }

      return true;
  }

  function set_products_id( $field_data, $db_column) {
    $ret = 0; // actually fills in $this->data
    $field_value = $this->data[$field_data['name']];
    if (!empty(trim($field_value))) {
      $ret = $this->EPtools->lookupProductId($field_value);


      if (!is_null($ret)){
        switch ($db_column) {
          case 'product_id':
          // check for loop in parent/child 2do ;)
  //        $d = \common\models\SetsProducts::findOne(['sets_id'=>$ret]);
  //        if (!is_null($d) ) {
          tep_db_query("SET SESSION group_concat_max_len = 1000000");
          // all children
          $d = tep_db_fetch_array(tep_db_query("select FIND_IN_SET('" . $ret . "', (SELECT GROUP_CONCAT(lv SEPARATOR ',') FROM ( SELECT @pv:=(SELECT GROUP_CONCAT(product_id SEPARATOR ',') FROM sets_products WHERE FIND_IN_SET(sets_id, @pv)) AS lv FROM sets_products JOIN (SELECT @pv:='" . $ret . "') tmp ) a)) as count"));
          if (!empty($d['count']) && $d['count']>0 ) {
            throw new EP\Exception('Incorrect product (loop): ' . $field_value . ' => ' . $db_column . " select FIND_IN_SET('" . $ret . "', (SELECT GROUP_CONCAT(lv SEPARATOR ',') FROM ( SELECT @pv:=(SELECT GROUP_CONCAT(product_id SEPARATOR ',') FROM sets_products WHERE FIND_IN_SET(sets_id, @pv)) AS lv FROM sets_products JOIN (SELECT @pv:='" . $ret . "') tmp ) a)) as count");
          }
          break;
          case 'sets_id':
            // update flag on product.
            try {
              $p = \common\models\Products::findOne($ret);
              $p->is_bundle = 1;
              $p->save();
            } catch (\Exception $e) {
              throw new EP\Exception('Cant update bundle status: ' . $field_value );
            }
            break;
        }
      }
    }
    if ((int)$ret==0) {
      throw new EP\Exception('Incorrect product: ' . $field_value . ' => ' . $db_column);
    } else {
      $this->data[$db_column] = $ret;
    }
  }

    function getVolumeWeightRule( $field_data, $db_column)
    {
        $value = $this->data[$field_data['name']];
        if ( $value==2 ){
            return 'own and from children';
        }elseif ( $value==1 ){
            return 'its own';
        }
        return 'from children';
    }

    function setVolumeWeightRule( $field_data, $db_column, $message)
    {
        $value = $this->data[$field_data['name']];
        $db_value = 0;
        if (strlen($value)>0) {
            if ($value == 2 || strtolower(trim($value)) == 'own and from children') {
                $db_value = 2;
            } elseif ($value == 1 || strtolower(trim($value)) == 'its own') {
                $db_value = 1;
            } elseif ($value == 0 || strtolower(trim($value)) == 'from children') {
                $db_value = 0;
            }else{
                $message->info('Unknown '.$field_data['value'].' value "'.$value.'"');
                $db_value = 0;
            }
            $this->data[$field_data['name']] = $db_value;
            return $db_value;
        }
        $this->data[$field_data['name']] = $db_value;
        return $db_value;
    }
}
