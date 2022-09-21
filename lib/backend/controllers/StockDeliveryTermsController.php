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

namespace backend\controllers;

use Yii;

/**
 * default controller to handle user requests.
 */
class StockDeliveryTermsController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_SETTINGS_BOX_STOCK_INDICATION', 'BOX_SETTINGS_BOX_STOCK_INDICATION_DELIVERY_TERMS'];

  public function __construct($id, $module)
  {
    parent::__construct($id, $module);
    \common\helpers\Translation::init('admin/stock-indication');
  }


  public function actionIndex() {

        $this->selectedMenu = array('settings', 'product_stock_indication', FILENAME_STOCK_INDICATION_DELIVERY_TERMS);
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl(FILENAME_STOCK_INDICATION_DELIVERY_TERMS.'/index'), 'title' => HEADING_TITLE_DELIVERY_TERMS);

        $this->view->headingTitle = HEADING_TITLE_DELIVERY_TERMS;

        $this->view->ViewTable = array(
          array(
              'title' => TABLE_HEADING_DELIVERY_TEMS,
              'not_important' => 0,
          ),
        );

      $this->topButtons[] = '<span class="btn btn-primary" onClick="return itemEdit(0)">' . IMAGE_INSERT . '</span>';

        $messages = [];
        if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
            if (!is_array($messages)) $messages = [];
        }
        return $this->render('index', array('messages' => $messages));
      
    }

    public function actionList() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cID = Yii::$app->request->get('cID', 0);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and (dtt.stock_delivery_terms_text like '%" . $keywords . "%')";
        }

        $formFilter = Yii::$app->request->get('filter','');
        parse_str($formFilter, $filter);

        $current_page_number = ($start / $length) + 1;
        $responseList = array();
        
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "dtt.stock_delivery_terms_text " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "dt.sort_order";
                    break;
            }
        } else {
            $orderBy = "dt.sort_order, dtt.stock_delivery_terms_text";
        }    
        
        $orders_status_query_raw =
          "select dt.*, dtt.stock_delivery_terms_text " .
          "from " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " dt " .
          " inner join " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " dtt ON dtt.stock_delivery_terms_id=dt.stock_delivery_terms_id AND dtt.language_id='".(int)$languages_id . "' " . $search . " ".
          "order by {$orderBy}";

        $orders_status_split = new \splitPageResults($current_page_number, $length, $orders_status_query_raw, $orders_status_query_numrows);
        $orders_status_query = tep_db_query($orders_status_query_raw);
        
        while ($item_data = tep_db_fetch_array($orders_status_query)) {
    
            $responseList[] = array(
              '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder">' .
                ($item_data['is_default']? '<b>' . $item_data['stock_delivery_terms_text'] . ' (' . TEXT_DEFAULT . ')</b>': $item_data['stock_delivery_terms_text']) .
                tep_draw_hidden_field('id', $item_data['stock_delivery_terms_id'], 'class="cell_identify"').
                '<input class="cell_type" type="hidden" value="top">'.
              '</div></div>',
            );
        }
        
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_status_query_numrows,
            'recordsFiltered' => $orders_status_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);          
        
    }
    
    public function actionListActions() {
      $languages_id = \Yii::$app->settings->get('languages_id');

      $stock_delivery_terms_id = Yii::$app->request->post('stock_delivery_terms_id', 0);
      $this->layout = false;

      if (!$stock_delivery_terms_id) return;

      $odata = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " where stock_delivery_terms_id='" . (int)$stock_delivery_terms_id . "'"));
      $get_text_r = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT." WHERE stock_delivery_terms_id='" . (int)$stock_delivery_terms_id . "'");
      $odata['text'] = array();
      if ( tep_db_num_rows($get_text_r)>0 ) {
        while ($_text = tep_db_fetch_array($get_text_r)) {
          $odata['text'][ $_text['language_id'] ] = $_text;
        }
      }

      $oInfo = new \objectInfo($odata, false);

      echo '<div class="or_box_head">' . (isset($oInfo->text[$languages_id])?$oInfo->text[$languages_id]['stock_delivery_terms_text']:'&nbsp;') . '</div>';

      $delivery_terms_inputs_string = '';
      $languages = \common\helpers\Language::get_languages();
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $delivery_terms_inputs_string .= '<div class="col_desc ' . $oInfo->text_stock_code . '">' . $languages[$i]['image'] . '&nbsp;<span class="' . $oInfo->stock_code . '">&nbsp;</span>' . (isset($oInfo->text[$languages[$i]['id']])?$oInfo->text[$languages[$i]['id']]['stock_delivery_terms_text']:'') . '</div>';
      }
      echo $delivery_terms_inputs_string;

      if ($oInfo->delivery_delay > 0) {
          echo '<div class="row_or">' . TEXT_DELIVERY_DELAY . ' <b>'.($oInfo->delivery_delay).'</b></div>';
      }

      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo
        '<button class="btn btn-edit btn-no-margin" onclick="itemEdit('.$stock_delivery_terms_id.')">' . IMAGE_EDIT . '</button>'.
        '<button class="btn btn-delete" onclick="itemDelete('.$stock_delivery_terms_id.')">' . IMAGE_DELETE . '</button>';
      echo '</div>';
    }
    
    public function actionEdit() {

      $stock_delivery_terms_id = intval(Yii::$app->request->get('stock_delivery_terms_id', 0));

      $odata = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " where stock_delivery_terms_id='" . (int)$stock_delivery_terms_id . "'"));
      $get_text_r = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT." WHERE stock_delivery_terms_id='" . (int)$stock_delivery_terms_id . "'");
      $odata['text'] = array();
      if ( tep_db_num_rows($get_text_r)>0 ) {
        while ($_text = tep_db_fetch_array($get_text_r)) {
          $odata['text'][ $_text['language_id'] ] = $_text;
        }
      }

      $oInfo = new \objectInfo($odata, false);
      \common\helpers\Php8::nullObjProps($oInfo, ['stock_delivery_terms_id', 'stock_code', 'text_stock_code', 'delivery_delay', 'is_default']);

      $status_inputs_string = '';
      $status_short_inputs_string = '';
      $languages = \common\helpers\Language::get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $status_inputs_string .=
          '<div class="langInput">' . $languages[$i]['image'] . tep_draw_input_field('stock_delivery_terms_text[' . $languages[$i]['id'] . ']', $oInfo->text[$languages[$i]['id']]['stock_delivery_terms_text'] ?? null) . '</div>';
        $status_short_inputs_string .=
          '<div class="langInput">' . $languages[$i]['image'] . tep_draw_input_field('stock_delivery_terms_short_text[' . $languages[$i]['id'] . ']', $oInfo->text[$languages[$i]['id']]['stock_delivery_terms_short_text'] ?? null) . '</div>';

      }

      echo tep_draw_form('stock_indication', FILENAME_STOCK_INDICATION_DELIVERY_TERMS. '/save', 'stock_delivery_terms_id=' . $oInfo->stock_delivery_terms_id);
      if ($stock_delivery_terms_id) {
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_DELIVERY_TEMS . '</div>';
      } else {
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_DELIVERY_TEMS . '</div>';
      }
      echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
      $predefined_codes = array(
          array('id'=>'out-stock','text'=> TEXT_PRODUCT_NOT_AVAILABLE),
          array('id'=>'in-stock','text'=> TEXT_PRODUCT_AVAILABLE),
          array('id'=>'transit','text'=> TEXT_INVENTORY_TRANSIT),
          array('id'=>'pre-order','text'=> TEXT_PRE_ORDER),
      );
      echo '<div class="check_linear"><label><span>' . TEXT_SHOW_STOCK_CODE . '</span> ' . tep_draw_pull_down_menu('stock_code',array_merge($predefined_codes, (in_array($oInfo->stock_code, array('out-stock', 'in-stock', 'transit', 'pre-order'))?array():array(array('id'=>$oInfo->stock_code, 'text' => $oInfo->stock_code)))), $oInfo->stock_code) . '</label></div>';
      echo '<div class="check_linear"><label><span>' . TEXT_SHOW_STOCK_CODE_NEW . '</span> ' . tep_draw_input_field('stock_code_new', '') . '</label></div>';

      echo '<div class="check_linear"><label><span>' . TEXT_SHOW_TEXT_STOCK_CODE . '</span> ' . tep_draw_pull_down_menu('text_stock_code',array_merge($predefined_codes, (in_array($oInfo->text_stock_code, array('out-stock', 'in-stock', 'transit', 'pre-order'))?array():array(array('id'=>$oInfo->text_stock_code, 'text' => $oInfo->text_stock_code)))), $oInfo->text_stock_code) . '</label></div>';
      echo '<div class="check_linear"><label><span>' . TEXT_SHOW_TEXT_STOCK_CODE_NEW . '</span> ' . tep_draw_input_field('text_stock_code_new', '') . '</label></div>';

      echo '<div class="check_linear"><label><span>' . TEXT_DELIVERY_DELAY . '</span> ' .  tep_draw_input_field('delivery_delay', $oInfo->delivery_delay) . '</label></div>';

      if (!$oInfo->is_default) echo '<div class="check_linear"><br>' . tep_draw_checkbox_field('is_default',1) . '<span>' . TEXT_SET_DEFAULT . '</span></div>';
      echo '<div class="col_desc">' . TEXT_INFO_STOCK_INDICATOR_TEXT . '</div>';
      echo $status_inputs_string;

      echo '<div class="col_desc">' . TEXT_INFO_STOCK_INDICATOR_SHORT_TEXT . '</div>';
      echo $status_short_inputs_string;

      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo
        '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="itemSave('.($oInfo->stock_delivery_terms_id ?? 0).')">'.
        '<input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
      echo '</div>';
      echo '</form>';
    }
    
    public function actionSave() {

      $stock_delivery_terms_id = Yii::$app->request->get('stock_delivery_terms_id', 0);
      $is_default = intval(Yii::$app->request->post('is_default',0));
      $stock_code = tep_db_prepare_input(Yii::$app->request->post('stock_code_new', ''));
      if ($stock_code == '')
        $stock_code = tep_db_prepare_input(Yii::$app->request->post('stock_code','out-stock'));
      $text_stock_code = tep_db_prepare_input(Yii::$app->request->post('text_stock_code_new', ''));
      if ($text_stock_code == '')
        $text_stock_code = tep_db_prepare_input(Yii::$app->request->post('text_stock_code','out-stock'));
      $stock_delivery_terms_text = tep_db_prepare_input(Yii::$app->request->post('stock_delivery_terms_text'));
      $stock_delivery_terms_short_text = tep_db_prepare_input(Yii::$app->request->post('stock_delivery_terms_short_text'));

      $delivery_delay = intval(Yii::$app->request->post('delivery_delay',0));

      if ($stock_delivery_terms_id == 0) {
        $next_sort_order = tep_db_fetch_array(tep_db_query(
          "SELECT MAX(sort_order) AS sort_order FROM " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " where 1"
        ));
        $next_sort_order = (int)$next_sort_order['sort_order']+1;

        tep_db_perform(TABLE_PRODUCTS_STOCK_DELIVERY_TERMS,array(
          'is_default' => $is_default,
          'stock_code' => $stock_code,
          'text_stock_code' => $text_stock_code,
          'delivery_delay' => $delivery_delay,
          'sort_order' => $next_sort_order,
        ));
        $stock_delivery_terms_id = tep_db_insert_id();
      }else{
        $update_data = array(
          'stock_code' => $stock_code,
          'text_stock_code' => $text_stock_code,
          'delivery_delay' => $delivery_delay,
        );
        if ( $is_default ) {
          $update_data['is_default'] = $is_default;        
        }
        tep_db_perform(TABLE_PRODUCTS_STOCK_DELIVERY_TERMS, $update_data,'update', "stock_delivery_terms_id='".(int)$stock_delivery_terms_id."'");
      }
      if ( $is_default  && (int)$stock_delivery_terms_id>0) {
        tep_db_query("UPDATE ".TABLE_PRODUCTS_STOCK_DELIVERY_TERMS." SET is_default=0 where stock_delivery_terms_id<>'".(int)$stock_delivery_terms_id."'");
      }

      $languages = \common\helpers\Language::get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $language_id = $languages[$i]['id'];

        $text_array = array(
          'stock_delivery_terms_id' => (int)$stock_delivery_terms_id,
          'language_id' => (int)$language_id,
          'stock_delivery_terms_text' => isset($stock_delivery_terms_text[$language_id])?$stock_delivery_terms_text[$language_id]:'',
          'stock_delivery_terms_short_text' => isset($stock_delivery_terms_short_text[$language_id])?$stock_delivery_terms_short_text[$language_id]:'',
        );

        $check_text = tep_db_fetch_array(tep_db_query(
          "SELECT COUNT(*) AS c ".
          "FROM ".TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT." ".
          "WHERE stock_delivery_terms_id='".(int)$stock_delivery_terms_id."' AND language_id='".(int)$language_id."'"
        ));

        if ( $check_text['c']==0 ) {
          tep_db_perform(TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT, $text_array);
        }else{
          tep_db_perform(TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT, $text_array,'update',"stock_delivery_terms_id='".(int)$stock_delivery_terms_id."' AND language_id='".(int)$language_id."'");
        }
      }

      if ($stock_delivery_terms_id == 0) {
        $message = TEXT_DELIVERY_TEMS_ADDED;
      }else {
        $message = TEXT_DELIVERY_TEMS_UPDATED;
      }

      echo json_encode(array('message' => $message, 'messageType' => 'alert-success'));
    }
    
    
    public function actionDelete() {
      global $language;

      $stock_delivery_terms_id =  intval(Yii::$app->request->post('stock_delivery_terms_id', 0));

      if ( !$stock_delivery_terms_id ) return;

      $get_check_data_r = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_STOCK_DELIVERY_TERMS." WHERE stock_delivery_terms_id='{$stock_delivery_terms_id}'");
      if ( tep_db_num_rows($get_check_data_r)==0 ) return;
      $check_data = tep_db_fetch_array($get_check_data_r);
      $remove_status = true;
      $error = array();
      if ($check_data['is_default']) {
        $remove_status = false;
        $error = array('message' => ERROR_REMOVE_DEFAULT_DELIVERY_TERMS, 'messageType' => 'alert-danger');
      }
      if (!$remove_status) {
        ?>
        <div class="alert fade in <?=$error['messageType']?>">
          <i data-dismiss="alert" class="icon-remove close"></i>
          <span id="message_plce"><?=$error['message']?></span>
        </div>
        <?php
      } else {
        tep_db_query("UPDATE ".TABLE_PRODUCTS." SET stock_delivery_terms_id=0 WHERE stock_delivery_terms_id='{$stock_delivery_terms_id}'");
        tep_db_query("UPDATE ".TABLE_INVENTORY." SET stock_delivery_terms_id=0 WHERE stock_delivery_terms_id='{$stock_delivery_terms_id}'");
        tep_db_query("DELETE FROM " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS . " where stock_delivery_terms_id = '{$stock_delivery_terms_id}'");
        tep_db_query("DELETE FROM " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " where stock_delivery_terms_id = '{$stock_delivery_terms_id}'");
        echo 'reset';
      }
    }

   public function actionSortOrder()
   {
     $moved_id = (int)$_POST['sort_top'];
     $ref_array = (isset($_POST['top']) && is_array($_POST['top']))?array_map('intval',$_POST['top']):array();
     if ( $moved_id && in_array($moved_id, $ref_array) ) {
       // {{ normalize
       $order_counter = 0;
       $order_list_r = tep_db_query(
         "SELECT dt.stock_delivery_terms_id, dt.sort_order ".
         "FROM ". TABLE_PRODUCTS_STOCK_DELIVERY_TERMS ." dt ".
         " LEFT JOIN " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " dtt ON dtt.stock_delivery_terms_id=dt.stock_delivery_terms_id AND dtt.language_id='".(int)\Yii::$app->settings->get('languages_id') . "' " .
         "WHERE 1 ".
         "ORDER BY dt.sort_order, dtt.stock_delivery_terms_text, dtt.stock_delivery_terms_short_text"
       );
       while( $order_list = tep_db_fetch_array($order_list_r) ){
         $order_counter++;
         tep_db_query("UPDATE ".TABLE_PRODUCTS_STOCK_DELIVERY_TERMS." SET sort_order='{$order_counter}' WHERE stock_delivery_terms_id='{$order_list['stock_delivery_terms_id']}' ");
       }
       // }} normalize
       $get_current_order_r = tep_db_query(
         "SELECT stock_delivery_terms_id, sort_order ".
         "FROM ".TABLE_PRODUCTS_STOCK_DELIVERY_TERMS." ".
         "WHERE stock_delivery_terms_id IN('".implode("','",$ref_array)."') ".
         "ORDER BY sort_order"
       );
       $ref_ids = array();
       $ref_so = array();
       while($_current_order = tep_db_fetch_array($get_current_order_r)){
         $ref_ids[] = (int)$_current_order['stock_delivery_terms_id'];
         $ref_so[] = (int)$_current_order['sort_order'];
       }

       foreach( $ref_array as $_idx=>$id ) {
         tep_db_query("UPDATE ".TABLE_PRODUCTS_STOCK_DELIVERY_TERMS." SET sort_order='{$ref_so[$_idx]}' WHERE stock_delivery_terms_id='{$id}' ");
       }

     }
   }
}