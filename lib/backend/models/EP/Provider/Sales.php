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

class Sales extends ProviderAbstract implements ImportInterface, ExportInterface {

    protected $entry_counter;
    protected $fields = array();
    protected $additional_fields = array();

    protected $additional_data = array();

    protected $data = array();
    protected $EPtools;
    protected $export_query;
    protected $enough_columns_for_create = null;

    protected $makeNetPricesOnImport = null;
    protected $exportPriceGross = false;

    function init()
    {
        parent::init();
        $this->initFields();

        $this->EPtools = new EP\Tools();
        $this->enough_columns_for_create = null;


        $ret = constant('TEXT_SALE_UNIQUE_DESCRIPTION');
        $groups = \common\helpers\Group::get_customer_groups();
        if (is_array($groups) && !empty($groups)) {
          //filter groups with discount, applicable to specials
          $groups = array_filter($groups, function ($el) { return ($el['groups_discount']>0) && $el['apply_groups_discount_to_specials']; });
          if (is_array($groups) && !empty($groups)) {
            $ret = sprintf($ret, "\n" . constant('TEXT_GROUP_WITH_SALE_DISCOUNT') . implode(', ', \yii\helpers\ArrayHelper::map($groups, 'groups_name', 'groups_discount')));

          } else {
            $ret = sprintf($ret, "\n" . constant('TEXT_NONE_GROUP_WITH_SALE_DISCOUNT'));
          }
        } else {
          $ret = sprintf($ret, '');
        }


        $this->additional_data['description']['top'] = $ret;
    }

    public function getExportDescription()
    {
      if (!empty($this->additional_data['description'])) {
        $ret = $this->additional_data['description'];
      } else {
        $ret = false;
      }

      return $ret;
    }

    protected function initFields()
    {
        $currencies = \Yii::$container->get('currencies');

        $checkUniqKey = \Yii::$app->getDb()->createCommand("select count(*) as c from products group by products_model having count(*)>1 or products_model=''")->queryScalar();
        if ( $checkUniqKey ){
            $this->fields[] = array( 'name' => 'products_id', 'value' => 'Products Id', 'is_key'=>true, 'is_key_part'=>true, 'calculated'=>true );
            $this->fields[] = array( 'name' => 'products_model', 'value' => 'Products Model', 'is_key_part'=>true );
        }else{
            $this->fields[] = array( 'name' => 'products_model', 'value' => 'Products Model', 'is_key'=>true );
        }

        $this->fields[] = array( 'name' => 'status', 'value' => 'Status', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'specials_enabled', 'value' => 'Manually Enabled', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'specials_disabled', 'value' => 'Manually Disabled', 'type' => 'int' );
        $this->fields[] = array( 'name' => 'start_date', 'value' => 'Start Date', 'get' => 'get_formatted_datetime', 'set' => 'set_formatted_datetime', );
        $this->fields[] = array( 'name' => 'expires_date', 'value' => 'Expires Date', 'get' => 'get_formatted_datetime', 'set' => 'set_formatted_datetime', );
        $this->fields[] = array( 'name' => 'total_qty', 'value' => TEXT_MAX_QTY_TO_SELL, 'type' => 'int' );
        $this->fields[] = array( 'name' => 'max_per_order', 'value' => TEXT_MAX_QTY_TO_SELL_IN_ORDER, 'type' => 'int' );
        $this->fields[] = array( 'name' => 'promote_type', 'value' => TEXT_SALE_PROMOTE_EP, 'get' => 'get_promote_type', 'set' => 'set_promote_type', );

        if (defined('USE_MARKET_PRICES') && USE_MARKET_PRICES == 'True') {
            foreach ($currencies->currencies as $key => $value) {

                $data_descriptor = '%|'.TABLE_SPECIALS_PRICES.'|'.$value['id'].'|0';
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'specials_new_products_price',
                    'name' => 'products_price_' . $value['id'] . '_0',
                    'value' => 'Sale Price ' . $key . ' ' . TEXT_MAIN,
                    'get' => 'get_products_price', 'set' => 'set_products_price',
                    'type' => 'numeric'
                );

                foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                    $data_descriptor = '%|'.TABLE_SPECIALS_PRICES.'|'.$value['id'].'|'.$groups_data['groups_id'];
                    $this->fields[] = array(
                        'data_descriptor' => $data_descriptor,
                        'column_db' => 'products_group_price',
                        'name' => 'products_price_' . $value['id'] . '_' . $groups_data['groups_id'],
                        'value' => 'Sale Price ' . $key . ' ' . $groups_data['groups_name'],
                        'get' => 'get_products_price', 'set' => 'set_products_price',
                        'type' => 'numeric'
                    );

                }

            }
        } else {
            $this->fields[] = array(
                'column_db' => 'specials_new_products_price',
                'name' => 'products_price_0',
                'value' => 'Sale Price' . ' ' . TEXT_MAIN,
                'get' => 'get_products_price', 'set' => 'set_products_price',
                'type' => 'numeric',
                'get' => 'get_products_price_main', 'set' => 'set_products_price_main',
            );

            foreach(\common\helpers\Group::get_customer_groups() as $groups_data ) {
                $data_descriptor = '%|'.TABLE_SPECIALS_PRICES.'|0|'.$groups_data['groups_id'];
                $this->fields[] = array(
                    'data_descriptor' => $data_descriptor,
                    'column_db' => 'specials_new_products_price',
                    'name' => 'products_price_' . $groups_data['groups_id'],
                    'value' => 'Sale Price ' . $groups_data['groups_name'],
                    'get' => 'get_products_price', 'set' => 'set_products_price',
                    'type' => 'numeric'
                );
            }
        }
        $this->fields[] = array( 'name' => "'_remove'", 'value' => 'Delete (enter 1 to delete)',);
        
        //clean up disabled options in this particular installation.
        parent::initFields();

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
            if ( isset($filter['price_tax']) && $filter['price_tax']>0 ) {
                $this->exportPriceGross = true;
            }
        }
        $main_sql =
            "SELECT {$main_source['select']} p.products_id, s.specials_id, p.products_tax_class_id AS _tax_id ".
            "FROM ".TABLE_PRODUCTS." p join ". TABLE_SPECIALS . " s on p.products_id=s.products_id ".
            "WHERE 1 {$filter_sql} ".
            "order by p.products_id, s.status desc, s.start_date ".
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
                if ( $source_data['table']==TABLE_SPECIALS_PRICES ) {
                    $data_sql .= "AND specials_id='{$this->data['specials_id']}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
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
        $this->buildSources( array_keys($data) );

        if ( is_null($this->makeNetPricesOnImport) ){
            $this->makeNetPricesOnImport = false;
            if (is_array($this->import_config) && isset($this->import_config['imported_price_is_gross']) && $this->import_config['imported_price_is_gross']=='yes'){
                $this->makeNetPricesOnImport = true;
            }
        }

        $export_columns = $this->export_columns;
        $main_source = $this->main_source;
        $data_sources = $this->data_sources;
        $file_primary_column = $this->file_primary_column;
        $file_primary_columns = $this->file_primary_columns;

        $this->data = $data;
        $is_updated = false;
        $need_touch_date_modify = true;
        $specials_id = false;

        $mkey_data = [];
        if ( !empty($file_primary_columns) ) {
            $mkey_data = array_intersect_key($data, $file_primary_columns);
            if (count($mkey_data) != count($file_primary_columns)) {
                $mkey_data = [];
            }
        }

        $lookup_by = [];
        if (array_key_exists($file_primary_column, $data)) {
            if ($file_primary_column=='products_id' && intval($data[$file_primary_column])==0){
                $lookup_by = $mkey_data;
            }else {
                $lookup_by = [$file_primary_column => $data[$file_primary_column]];
            }
        }else{
            $lookup_by = $mkey_data;
        }
        if ( count($lookup_by)==0 ){
            throw new EP\Exception('Primary key not found in file');
        }

        $file_primary_value = implode(' - ',$lookup_by);
        $product_where = '';
        foreach ($lookup_by as $_l_column=>$_l_value){
            if ( !empty($product_where) ) $product_where .= " AND ";
            $product_where .= "p.{$_l_column}='" . tep_db_input($_l_value) . "' ";
        }

        $get_main_data_r = tep_db_query(
            "SELECT p.* ".
            "FROM " . TABLE_PRODUCTS . " p ".
            "WHERE {$product_where} "
        );
        $found_rows = tep_db_num_rows($get_main_data_r);
        if ($found_rows > 1) {
            // error data not unique
            $message->info('"'.$file_primary_value.'" not unique - found '.$found_rows.' rows. Skipped');
            return false;
        } elseif ($found_rows == 0) {
          
            $message->info('"'.$file_primary_value.'" - not found ');
            return false;
        } else {

          $db_main_data = tep_db_fetch_array($get_main_data_r);
          $products_id = $db_main_data['products_id'];
          $this->data['products_id'] = $db_main_data['products_id'];
          $this->data['products_tax_class_id'] = $db_main_data['products_tax_class_id'];
          $update_data_array = array();
          foreach ($main_source['columns'] as $file_column => $db_column) {
              if (!array_key_exists($file_column, $data)) continue;
              if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                  call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_id'], $message));
              }
              elseif (isset($export_columns[$file_column]['type'])){
                if ( array_key_exists($file_column, $this->data) ) {
                    switch ($export_columns[$file_column]['type']) {
                      case 'integer':
                      case 'int':
                        $this->data[$file_column] = intval($this->data[$file_column]);
                        break;
                      case 'numeric':
                        $this->data[$file_column] = floatval($this->data[$file_column]);
                        break;
                    }
                }
            }

              $update_data_array[$db_column] = $this->data[$file_column];
          }
//echo "#### <PRE>" .print_r($update_data_array, 1) ."</PRE>";

          if (isset($update_data_array['products_model'])) {
            unset($update_data_array['products_model']);
          }

          if (count($update_data_array) > 0) {
            if (empty($update_data_array['start_date'])) {
              $update_data_array['start_date'] = 'null';
            }
            if (empty($update_data_array['expires_date'])) {
              $update_data_array['expires_date'] = 'null';
            }
            if ( $update_data_array['start_date'] != 'null' && $update_data_array['start_date'] > date(\common\helpers\Date::DATABASE_DATETIME_FORMAT) ) {
              $update_data_array['status'] = 0;
            }
            // check for full match (most probably mistake - do not duplicate)
            $wSql = "where products_id='" . (int)$products_id . "'";
            foreach ($update_data_array as $key => $value) {
              if (in_array($key, ['specials_new_products_price', "'_remove'", '_remove', 
                'status', 'specials_enabled', 'specials_disabled', 'promote_type', 'total_qty', 'max_per_order'])) {
                continue;
              }
              if (strtolower($value) == 'null' ) {
                $wSql .= " and $key is null";
              } else {
                $wSql .= " and $key = '" . tep_db_input($value). "'";
              }
            }
//echo "select specials_id, specials_new_products_price, status from " . TABLE_SPECIALS . " " . $wSql;
            $special_q = tep_db_query("select specials_id, specials_new_products_price, status from " . TABLE_SPECIALS . " " . $wSql . " order by specials_id desc");
            if (tep_db_num_rows($special_q)>1) {
              $message->info('<b>"'.$file_primary_value.'"</b> - several (' . tep_db_num_rows($special_q) . ') sales ' . $wSql . ' only last one is updated');
            }
            $special = tep_db_fetch_array($special_q);

            // delete or insert
            if (!empty($update_data_array["'_remove'"])) {
              if (!empty($special['specials_id'])) {
                tep_db_query("delete from " . TABLE_SPECIALS . " where specials_id ='" . (int)$special['specials_id'] . "'");
                tep_db_query("delete from " . TABLE_SPECIALS_PRICES . " where specials_id ='" . (int)$special['specials_id'] . "'");

              } else {
                //not found
                $message->info('"'.$file_primary_value.'" - not found special: ' . $wSql);
                return false;
              }

            } else {
              
              if (isset($update_data_array["'_remove'"])) {
                unset($update_data_array["'_remove'"]);
              }

          // expire < start - mistake - skip
          // expire < now()  - skip - should be off and never on (trash in DB)
          // start > now()  - switch off - auto on by script later
              if (  ( $update_data_array['start_date'] != 'null' && $update_data_array['expires_date'] != 'null'
                        && $update_data_array['start_date'] > $update_data_array['expires_date'] )
                     || (empty($special['status']) && $update_data_array['expires_date'] != 'null' && $update_data_array['expires_date'] <= date(\common\helpers\Date::DATABASE_DATETIME_FORMAT) )
                  ) {
                $message->info('"'.$file_primary_value.'" - skipped - incorrect start or expire date/time');
                return false;
              }

              $update_data_array['products_id'] = (int)$products_id ;
              
              if (!empty($special['specials_id'])) {
                $addSql = " and specials_id<>'" . (int)$special['specials_id'] . "'";
              } else {
                $addSql = "";
              }

              if (defined('SALE_STRICT_DATE') && SALE_STRICT_DATE=='True') {
              //check existing specials for date intersection
              //last (current) is master - reduce existing
                if ($update_data_array['start_date'] != 'null') { //expire other before me
                  $r = tep_db_query("select specials_id, start_date from " . TABLE_SPECIALS . " where products_id='" . (int)$products_id . "' $addSql "
                      . " and (start_date is null or start_date = '0000-00-00 00:00:00' or start_date<='" . tep_db_input($update_data_array['start_date']) . "') "
                      . " and (expires_date is null or expires_date = '0000-00-00 00:00:00' or expires_date>='" . tep_db_input($update_data_array['start_date']) . "') ");

                  while ($e = tep_db_fetch_array($r)) {
                    if ($e['start_date'] == $update_data_array['start_date']) { // 2check
                      //if ==  then new expire date could be < start
                      $sU = ", start_date = DATE_SUB('" . tep_db_input($update_data_array['start_date']) . "', INTERVAL 1 SECOND) ";
                    } else {
                      $sU = '';
                    }
                    tep_db_query("update ". TABLE_SPECIALS . " set specials_last_modified=now(), expires_date = DATE_SUB('" . tep_db_input($update_data_array['start_date']) . "', INTERVAL 1 SECOND) {$sU} where specials_id='" . (int)$e['specials_id'] . "'");
                    $message->info('"'.$file_primary_value.'" - expire date/time of existing special has been changed');
                  }
                }
                
                if ($update_data_array['expires_date'] != 'null') {
                  //start other after me
                  $r = tep_db_query("select specials_id from " . TABLE_SPECIALS . " where products_id='" . (int)$products_id . "' $addSql "
                      . " and (start_date is null or start_date = '0000-00-00 00:00:00' or start_date<='" . tep_db_input($update_data_array['expires_date']) . "') "
                      . " and (expires_date is null or expires_date = '0000-00-00 00:00:00' or expires_date>='" . tep_db_input($update_data_array['expires_date']) . "') ");
                  while ($e = tep_db_fetch_array($r)) {
                    tep_db_query("update ". TABLE_SPECIALS . " set specials_last_modified=now(), start_date = DATE_ADD('" . tep_db_input($update_data_array['expires_date']) . "', INTERVAL 1 SECOND) where specials_id='" . (int)$e['specials_id'] . "'");
                    $message->info('"'.$file_primary_value.'" - start date/time of existing special has been changed');
                  }
                }
                //covers - deactivate and set start=expire
                $r = tep_db_query("select specials_id, start_date from " . TABLE_SPECIALS . " where products_id='" . (int)$products_id . "' $addSql "
                    . " and (status=1 or start_date is null or start_date = '0000-00-00 00:00:00' " . ($update_data_array['start_date']!='null'?
                              " or start_date>='" . tep_db_input($update_data_array['start_date']) . "'":
                              " or start_date>now() ")
                    . "     ) "
                    . " and (status=1 or expires_date is null or expires_date = '0000-00-00 00:00:00' " . ($update_data_array['expires_date']!='null'?
                              " or expires_date<='" . tep_db_input($update_data_array['expires_date']) . "'":
                              " or expires_date>now() ")
                    . "     ) "
                    );
                while ($e = tep_db_fetch_array($r)) {
                  tep_db_query("update ". TABLE_SPECIALS . " set specials_last_modified=now(), expires_date=start_date, status=0 where specials_id='" . (int)$e['specials_id'] . "'");
                }
              }

              if ($update_data_array['specials_disabled'] && !empty($update_data_array['status'])) {
                $update_data_array['status'] = 0;

              } elseif ( empty($update_data_array['specials_disabled']) && !empty($update_data_array['specials_enabled']) ) {
                $update_data_array['status'] = 1;
                
              }

              if (empty($special['specials_id'])) {
                $update_data_array['specials_date_added'] = 'now()';

                tep_db_perform(TABLE_SPECIALS, $update_data_array);
                $specials_id = tep_db_insert_id();
                tep_db_perform(TABLE_SPECIALS_PRICES, ['specials_new_products_price' => $update_data_array['specials_new_products_price'],
                    'specials_id'=>  (int)$specials_id ,
                    'groups_id' => 0,
                    'currencies_id' => 0
                    ]); //kostyl no marketing price
                if ($update_data_array['status']) {
                  $is_updated = true;
                }
              } else {
                $specials_id = $special['specials_id'];

                $update_data_array['specials_last_modified'] = 'now()';
                tep_db_perform(TABLE_SPECIALS, $update_data_array, 'update', "specials_id='" . (int)$specials_id . "'");
                if (abs($update_data_array['specials_new_products_price'] - $special['specials_new_products_price']) > 0.00001) {
                  tep_db_perform(TABLE_SPECIALS_PRICES, ['specials_new_products_price' => $update_data_array['specials_new_products_price']], 'update', "specials_id='" . (int)$specials_id . "' and groups_id=0 and currencies_id=0"); //kostyl no marketing price
                }
                if (isset($update_data_array['status']) && $special['status'] != (int)$update_data_array['status']) {
                  $is_updated = true;
                }

              }
            }
            // fix possible errors with dates (set the same and deactivate.
            tep_db_query("update ". TABLE_SPECIALS . " set expires_date=start_date, status=0 where expires_date<start_date and products_id='" . (int)$products_id . "'");


          }
        }
        
        if ($specials_id) {
          $this->data['specials_id'] = $specials_id;

          foreach ($data_sources as $source_key => $source_data) {
              if ($source_data['table']) {

                  $new_data = array();
                  foreach ($source_data['columns'] as $file_column => $db_column) {
                      if (!array_key_exists($file_column, $data)) continue;
                      if (isset($export_columns[$file_column]['set']) && method_exists($this, $export_columns[$file_column]['set'])) {
                          call_user_func_array(array($this, $export_columns[$file_column]['set']), array($export_columns[$file_column], $this->data['products_id'], $message));
                      }
                      $new_data[$db_column] = $this->data[$file_column];
                  }
                  if (count($new_data) == 0) continue;

                  $data_sql = "SELECT {$source_data['select_raw']} 1 AS _dummy FROM {$source_data['table']} WHERE 1 ";

                  if ($source_data['table'] == TABLE_SPECIALS_PRICES) {
                      $update_pk = "specials_id='{$specials_id}' AND currencies_id='{$source_data['params'][0]}' AND groups_id='{$source_data['params'][1]}'";
                      $insert_pk = array('specials_id' => $specials_id, 'currencies_id' => $source_data['params'][0], 'groups_id' => $source_data['params'][1]);
                      $data_sql .= "AND {$update_pk}";
                  } else {
                      continue;
                  }
//echo $data_sql.'<hr>';
                  $data_sql_r = tep_db_query($data_sql);
                  if (tep_db_num_rows($data_sql_r) > 0) {
                      //$_old_data = tep_db_fetch_array($data_sql_r);
                      tep_db_free_result($data_sql_r);
                      //echo '<pre>update rel '; var_dump($source_data['table'],$new_data,'update', $update_pk); echo '</pre>';
                      //$message->info("#### $update_pk <PRE>". print_r($new_data, 1) ."</PRE>");
                      tep_db_perform($source_data['table'], $new_data, 'update', $update_pk);

                  } else {
                      //echo '<pre>insert rel '; var_dump($source_data['table'],array_merge($new_data,$insert_pk)); echo '</pre>';
                      //$message->info("#### insert <PRE>". print_r(array_merge($new_data,$insert_pk), 1) ."</PRE>");
                      tep_db_perform($source_data['table'], array_merge($new_data, $insert_pk));
                  }
                  $is_updated = true;
              } elseif ($source_data['init_function'] && method_exists($this, $source_data['init_function'])) {
                  call_user_func_array(array($this, $source_data['init_function']), $source_data['params']);
                  foreach ($source_data['columns'] as $file_column => $db_column) {
                      if (isset($export_columns[$db_column]['set']) && method_exists($this, $export_columns[$db_column]['set'])) {
                          call_user_func_array(array($this, $export_columns[$db_column]['set']), array($export_columns[$db_column], $this->data['products_id'], $message));
                      }
                  }
              }
          }
          $this->entry_counter++;
        }

        if ($is_updated) {
            tep_db_perform(TABLE_PRODUCTS, array(
                'products_last_modified' => 'now()',
            ), 'update', "products_id='" . (int)$products_id . "'");

            /** @var \common\extensions\ProductPriceIndex\ProductPriceIndex  $ext */
            if($ext = \common\helpers\Extensions::isAllowed('ProductPriceIndex')) {
                $ext::reindex((int)$products_id);
            }

        }

        return true;
    }

    public function postProcess(Messages $message)
    {
        $message->info('Processed '.$this->entry_counter.' rows');
        $message->info('Done.');

        $this->EPtools->done('products_import');
    }

/**
 * format date mysql > locale
 * @param array $field_data
 * @param int $products_id
 */
    function get_formatted_datetime( $field_data, $products_id ) {
      if ( !isset($this->data[$field_data['name']]) || $this->data[$field_data['name']]==='' ||  substr($this->data[$field_data['name']], 0, 4) == '0000' ) {
        $this->data[$field_data['name']] = '';
      } else {
        $this->data[$field_data['name']] = \common\helpers\Date::datetime_short($this->data[$field_data['name']]);
      }

      return $this->data[$field_data['name']];

    }

    /**
     * format date time from locale to mysql (null if empty, 0000-00-00)
     * @param array $field_data
     * @param int $products_id //not used
     * @return string
     */
    function set_formatted_datetime( $field_data, $products_id ) {
      if( $this->data[$field_data['name']]==='' ) {
        $this->data[$field_data['name']] = 'null';
      } else {
        //add default time if seems not found: start 00:00 (could be ommitted) expiire 23:59
        if (!preg_match("/\d{1,2}\s*\:\s*\d{1,2}/", ($this->data[$field_data['name']]))) {
          if ('expires_date' == $field_data['name']) {
            $this->data[$field_data['name']] .= ' 23:59:00';
          } else {
            $this->data[$field_data['name']] .= ' 00:00:00';
          }
        }
        $tmp = \common\helpers\Date::prepareInputDate($this->data[$field_data['name']], true);
        // check for '0000'
        if ($tmp=='' || substr($tmp, 0, 4) == '0000') {
          $this->data[$field_data['name']] = 'null';
        } else {
          $this->data[$field_data['name']] = $tmp;
        }
      }
      return '';
    }

    protected function getTaxRate()
    {
        $taxRate = 0;
        if ( isset($this->data['_tax_rate']) ) {
            $taxRate = $this->data['_tax_rate'];
        }else {
            $tax_class_id = 0;
            if( $this->data['products_id'] ){
                $tax_class_id = \common\helpers\Product::get_products_info($this->data['products_id'],'products_tax_class_id');
            }
            if ( $tax_class_id ) {
                $defaultAddress = \Yii::$app->get('platform')->getConfig(\common\classes\platform::defaultId())->getPlatformAddress();
                $country_id = (int) $defaultAddress['country_id'];
                $zone_id = (int) $defaultAddress['zone_id'];

                $taxRate = \common\helpers\Tax::get_tax_rate((int) $tax_class_id, $country_id, $zone_id, '', false);
            }
            $this->data['_tax_rate'] = $taxRate;
        }
        return $this->data['_tax_rate'];
    }

    function get_products_price( $field_data, $products_id )
    {
        if( !isset($this->data[$field_data['name']]) || $this->data[$field_data['name']]==='' ) {
            //$this->data[$field_data['name']] = 'same'/*'-2'*/;
            $this->data[$field_data['name']] = TEXT_EP_PRICE_GROUP_DISCOUNTED;
        }elseif( floatval($this->data[$field_data['name']])==-2 ) {
            //$this->data[$field_data['name']] = 'same';
            $this->data[$field_data['name']] = TEXT_EP_PRICE_GROUP_DISCOUNTED;
        //}elseif( floatval($this->data[$field_data['name']])==-1 ) {
        }elseif( floatval($this->data[$field_data['name']])<=0 ) {
            //$this->data[$field_data['name']] = 'disabled';
            $this->data[$field_data['name']] = TEXT_DISABLED;
        }
        if ( $this->exportPriceGross && is_numeric($this->data[$field_data['name']]) && $this->data[$field_data['name']]>0 ) {
            if (!array_key_exists('products_tax_class_id', $this->data)) {
                $this->data['products_tax_class_id'] = $this->data['_tax_id'];
            }
            $this->data[$field_data['name']] = number_format($this->data[$field_data['name']] * ((100 + $this->getTaxRate()) / 100), 6, '.', '');
        }
        return $this->data[$field_data['name']];
    }

    function set_products_price( $field_data, $products_id )
    {
        if( $this->data[$field_data['name']]==='' ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-2 || $this->data[$field_data['name']]=='same' || $this->data[$field_data['name']]== TEXT_EP_PRICE_GROUP_DISCOUNTED ) {
            $this->data[$field_data['name']] = '-2';
        }elseif( floatval($this->data[$field_data['name']])==-1 || $this->data[$field_data['name']]=='disabled'  || $this->data[$field_data['name']]==TEXT_DISABLED) {
            $this->data[$field_data['name']] = '-1';
        }

        if ( $this->makeNetPricesOnImport && floatval($this->data[$field_data['name']])>0 ) {
            if($taxRate = $this->getTaxRate()){
//echo "#### \$taxRate <PRE>" . __FILE__ . ':' . __LINE__ . ' ' . print_r($taxRate, true) . "</PRE>";
              $this->data[$field_data['name']] = $this->data[$field_data['name']]*(100/(100+$taxRate));
            }
        }
        return '';
    }

    function get_products_price_main( $field_data, $products_id )
    {   
        if( floatval($this->data[$field_data['name']])<=0 ) {
            //$this->data[$field_data['name']] = 'disabled';
            $this->data[$field_data['name']] = TEXT_DISABLED;
        }
        if ( $this->exportPriceGross && is_numeric($this->data[$field_data['name']]) && $this->data[$field_data['name']]>0 ) {
            if (!array_key_exists('products_tax_class_id', $this->data)) {
                $this->data['products_tax_class_id'] = $this->data['_tax_id'];
            }
            $this->data[$field_data['name']] = number_format($this->data[$field_data['name']] * ((100 + $this->getTaxRate()) / 100), 6, '.', '');
        }
        return $this->data[$field_data['name']];
    }
    
    function set_products_price_main( $field_data, $products_id )
    {
        if ( $this->makeNetPricesOnImport && floatval($this->data[$field_data['name']])>0 ) {
            if($taxRate = $this->getTaxRate()){
                $this->data[$field_data['name']] = $this->data[$field_data['name']]*(100/(100+$taxRate));
            }
        } elseif ( floatval($this->data[$field_data['name']])<=0  ) {
          $this->data[$field_data['name']] = -1;
        }
        return $this->data[$field_data['name']];
    }

    function get_promote_type( $field_data, $products_id )
    {
        if( !isset($this->data[$field_data['name']]) || $this->data[$field_data['name']]==='' ) {
            $this->data[$field_data['name']] = TEXT_EP_DEFAULT;
        }elseif( floatval($this->data[$field_data['name']])==0 ) {
            $this->data[$field_data['name']] = TEXT_EP_DEFAULT;
        }elseif( floatval($this->data[$field_data['name']])==-1 ) {
            $this->data[$field_data['name']] = TEXT_EP_DISABLE;
        }elseif( floatval($this->data[$field_data['name']])==1 ) {
            $this->data[$field_data['name']] = TEXT_EP_PERCENT;
        }elseif( floatval($this->data[$field_data['name']])==2 ) {
            $this->data[$field_data['name']] = TEXT_EP_FIXED;
        }
        return $this->data[$field_data['name']];
    }

    function set_promote_type( $field_data, $products_id )
    {
        if( $this->data[$field_data['name']]==='' ) {
            $this->data[$field_data['name']] = 0;
        }elseif( intval($this->data[$field_data['name']])==0 || $this->data[$field_data['name']]== TEXT_EP_DEFAULT ) {
            $this->data[$field_data['name']] = 0;
        }elseif( intval($this->data[$field_data['name']])==-1 || $this->data[$field_data['name']]=='disable'  || $this->data[$field_data['name']]==TEXT_EP_DISABLE) {
            $this->data[$field_data['name']] = -1;
        }elseif( intval($this->data[$field_data['name']])==1 || $this->data[$field_data['name']]=='percent'  || $this->data[$field_data['name']]==TEXT_EP_PERCENT) {
            $this->data[$field_data['name']] = 1;
        }elseif( intval($this->data[$field_data['name']])==2 || $this->data[$field_data['name']]=='fixed'  || $this->data[$field_data['name']]==TEXT_EP_FIXED) {
            $this->data[$field_data['name']] = 2;
        }
        return '';
    }

}