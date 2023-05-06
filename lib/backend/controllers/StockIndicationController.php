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
class StockIndicationController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_SETTINGS_BOX_STOCK_INDICATION', 'BOX_SETTINGS_BOX_STOCK_INDICATION_INDICATION'];

  public function __construct($id, $module)
  {
    parent::__construct($id, $module);
    \common\helpers\Translation::init('admin/stock-indication');
  }


  public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'product_stock_indication', FILENAME_STOCK_INDICATION_INDICATION);
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl(FILENAME_STOCK_INDICATION_INDICATION.'/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;

      $this->topButtons[] = '<a class="btn btn-primary" href="' . \yii\helpers\Url::toRoute('edit') . '">' . IMAGE_INSERT . '</a>';
      
      $this->view->ViewTable = array(
            array(
                'title' => TABLE_HEADING_STOCK_INDICATION,
                'not_important' => 0,
            ),
            array(
              'title' => TEXT_ALLOW_OUT_OF_STOCK_CHECKOUT,
              'not_important' => 0,
            ),
            array(
              'title' => TEXT_ALLOW_OUT_OF_STOCK_ADD_TO_CART,
              'not_important' => 0,
            ),
            array(
              'title' => TABLE_HEADING_ASSIGNED_STOCK_TERMS,
              'not_important' => 0,
            ),
        );

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
            $search .= " and (st.stock_indication_text like '%" . $keywords . "%')";
        }

        $formFilter = Yii::$app->request->get('filter','');
        parse_str($formFilter, $filter);

        $current_page_number = ($start / $length) + 1;
        $responseList = array();
        
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "st.stock_indication_text " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "s.sort_order";
                    break;
            }
        } else {
            $orderBy = "s.sort_order";
        }    
        
        $orders_status_query_raw =
          "select s.*, st.stock_indication_text " .
          "from " . TABLE_PRODUCTS_STOCK_INDICATION . " s " .
          " inner join " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " st ON st.stock_indication_id=s.stock_indication_id AND st.language_id='".(int)$languages_id . "' " . $search . " ".
          "order by {$orderBy}";

        $orders_status_split = new \splitPageResults($current_page_number, $length, $orders_status_query_raw, $orders_status_query_numrows);
        $orders_status_query = tep_db_query($orders_status_query_raw);
        
        while ($item_data = tep_db_fetch_array($orders_status_query)) {
            $item_data['assigned_terms'] = array_map(
               function($row){
                   if ( $row['is_default_term'] ){
                       $row['stock_delivery_terms_text'] = '<b>'.$row['stock_delivery_terms_text'].'</b>';
                   }
                   return $row['stock_delivery_terms_text'];
               },
                \common\models\ProductsStockStatusesCrossLink::find()
                    ->alias('l')
                    ->join('inner join', \common\models\ProductsStockDeliveryTerms::tableName().' t', 't.stock_delivery_terms_id=l.stock_delivery_terms_id')
                    ->join('left join', \common\models\ProductsStockDeliveryTermsText::tableName().' tt', 't.stock_delivery_terms_id=tt.stock_delivery_terms_id and tt.language_id=\''.(int)$languages_id.'\'')
                    ->where(['l.stock_indication_id'=>$item_data['stock_indication_id']])
                    ->select(['tt.stock_delivery_terms_text', 'l.is_default_term'])
                    ->asArray()->all()
            );
    
            $responseList[] = array(
              '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder">' .
                ($item_data['is_default']? '<b>' . $item_data['stock_indication_text'] . ' (' . TEXT_DEFAULT . ')</b>': $item_data['stock_indication_text']) .
                tep_draw_hidden_field('id', $item_data['stock_indication_id'], 'class="cell_identify"').
                '<input class="cell_type" type="hidden" value="top">'.
              '</div></div>',
              '<div>'.($item_data['allow_out_of_stock_checkout']?TEXT_STOCK_INDICATION_YES:'').'</div>',
              '<div>'.($item_data['allow_out_of_stock_add_to_cart']?TEXT_STOCK_INDICATION_YES:'').'</div>',
              '<div>'.(join(',', $item_data['assigned_terms'])).'</div>',
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

      $stock_indication_id = Yii::$app->request->post('stock_indication_id', 0);
      $this->layout = false;

      if (!$stock_indication_id) return;

      $odata = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_INDICATION . " where stock_indication_id='" . (int)$stock_indication_id . "'"));
      $get_text_r = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_STOCK_INDICATION_TEXT." WHERE stock_indication_id='" . (int)$stock_indication_id . "'");
      $odata['text'] = array();
      if ( tep_db_num_rows($get_text_r)>0 ) {
        while ($_text = tep_db_fetch_array($get_text_r)) {
          $odata['text'][ $_text['language_id'] ] = $_text;
        }
      }

      $oInfo = new \objectInfo($odata, false);

      echo '<div class="or_box_head">' . (isset($oInfo->text[$languages_id])?$oInfo->text[$languages_id]['stock_indication_text']:'&nbsp;') . '</div>';

      echo '<div class="row_or">' . TEXT_ALLOW_OUT_OF_STOCK_CHECKOUT . ' <b>'.(!!$oInfo->allow_out_of_stock_checkout?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      echo '<div class="row_or">' . TEXT_ALLOW_OUT_OF_STOCK_ADD_TO_CART . ' <b>'.(!!$oInfo->allow_out_of_stock_add_to_cart?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';

      echo '<div class="row_or">' . TEXT_ALLOW_IN_STOCK_NOTIFY . ' <b>'.(!!$oInfo->allow_in_stock_notify?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      echo '<div class="row_or">' . TEXT_REQUEST_FOR_QUOTE . ' <b>'.(!!$oInfo->request_for_quote?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      echo '<div class="row_or">' . TEXT_IS_HIDDEN . ' <b>'.(!!$oInfo->is_hidden?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      echo '<div class="row_or">' . TEXT_DISABLE_PRODUCT_ON_OOS . ' <b>'.(!!$oInfo->disable_product_on_oos?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      echo '<div class="row_or">' . TEXT_RESET_STATUS_ON_OOS . ' <b>'.(!!$oInfo->reset_status_on_oos?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      if ($extClass = \common\helpers\Acl::checkExtensionAllowed('ObsoleteProducts', 'allowed')) {
        echo '<div class="row_or">' . TEXT_IS_OBSOLETE . ' <b>'.(!!$oInfo->is_obsolete?TEXT_BTN_YES:TEXT_BTN_NO).'</b></div>';
      }
      echo '<div class="row_or"><br>' . ($oInfo->display_price_options==2?TEXT_STOCK_INTICATION_PRICE_ZERO:($oInfo->display_price_options==1?TEXT_STOCK_INTICATION_PRICE_OFF:TEXT_STOCK_INTICATION_PRICE_ON)).'</div>';
      echo '<div class="row_or"><br>' . ($oInfo->display_virtual_options==2?TEXT_STOCK_INTICATION_VIRTUAL_OFF:($oInfo->display_virtual_options==1?TEXT_STOCK_INTICATION_VIRTUAL_ON:TEXT_STOCK_INTICATION_VIRTUAL_INC)).'</div>';

      echo '<div class="btn-toolbar btn-toolbar-order">';
      $gets = array_filter(\Yii::$app->request->getQueryParams());
      $gets['stock_indication_id'] = $stock_indication_id;
      echo
        '<a class="btn btn-edit btn-no-margin" href="'.\Yii::$app->urlManager->createUrl(['stock-indication/edit'] + $gets).'">' . IMAGE_EDIT . '</a>'.
        '<button class="btn btn-delete" onclick="itemDelete('.$stock_indication_id.')">' . IMAGE_DELETE . '</button>';
      echo '</div>';
    }
    
    public function actionEdit() {
        $stock_indication_id = intval(Yii::$app->request->get('stock_indication_id', 0));

        if ($stock_indication_id) {
            $title = TEXT_INFO_HEADING_EDIT_STOCK_INDICATION;
        } else {
            $title =  TEXT_INFO_HEADING_NEW_STOCK_INDICATION;
        }

        $this->topButtons[] = '<span class="btn btn-confirm">' . IMAGE_UPDATE . '</span>';

        $this->view->headingTitle = $title;
        $this->selectedMenu = array('settings', 'product_stock_indication', FILENAME_STOCK_INDICATION_INDICATION);
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl(FILENAME_STOCK_INDICATION_INDICATION.'/index'), 'title' => $title);

        $odata = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_INDICATION . " where stock_indication_id='" . (int)$stock_indication_id . "'"));
        $get_text_r = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_STOCK_INDICATION_TEXT." WHERE stock_indication_id='" . (int)$stock_indication_id . "'");
        $odata['text'] = array();
        if ( tep_db_num_rows($get_text_r)>0 ) {
            while ($_text = tep_db_fetch_array($get_text_r)) {
                $odata['text'][ $_text['language_id'] ] = $_text;
            }
        }
        $odata['linked_stock_terms'] = empty($odata['stock_indication_id']) ? null : \common\models\ProductsStockStatusesCrossLink::find()->where(['stock_indication_id'=>$odata['stock_indication_id']])->asArray()->indexBy('stock_delivery_terms_id')->all();
        $oInfo = new \objectInfo($odata, false);

        $stock_indication_text_inputs = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $stock_indication_text_inputs[$languages[$i]['id']] =  \common\helpers\Html::textInput(
                    'stock_indication_text[' . $languages[$i]['id'] . ']',
                    $oInfo->text[$languages[$i]['id']]['stock_indication_text'] ?? null,
                    ['class'=>'form-control']
            );
        }

        $gets = array_filter(\Yii::$app->request->getQueryParams());
        $gets['stock_indication_id'] = $oInfo->stock_indication_id ?? null;

        return $this->render('edit', [
            'actionUrl' => Yii::$app->urlManager->createUrl(['stock-indication/save'] + $gets),
            'cancelUrl' => Yii::$app->urlManager->createUrl(['stock-indication/index'] + $gets),
            'languages' => \common\helpers\Language::get_languages(),
            'stock_indication_text_inputs' => $stock_indication_text_inputs,
            'oInfo' => $oInfo,
            'stockCodeVariants' => array(
                array('id'=>'out-stock','text'=> TEXT_PRODUCT_NOT_AVAILABLE),
                array('id'=>'in-stock','text'=> TEXT_PRODUCT_AVAILABLE),
                array('id'=>'eol','text'=> 'EOL'),
                //array('id'=>'transit','text'=> TEXT_INVENTORY_TRANSIT),
                array('id'=>'pre-order','text'=> TEXT_PRE_ORDER),
            )
        ]);

    }
    
    public function actionSave() {

      $stock_indication_id = Yii::$app->request->get('stock_indication_id', 0);

      $is_default = intval(Yii::$app->request->post('is_default',0));
      $allow_out_of_stock_add_to_cart = intval(Yii::$app->request->post('allow_out_of_stock_add_to_cart',0));
      $allow_out_of_stock_checkout = intval(Yii::$app->request->post('allow_out_of_stock_checkout',0));

      $allow_in_stock_notify = intval(Yii::$app->request->post('allow_in_stock_notify',0));
      $request_for_quote = intval(Yii::$app->request->post('request_for_quote',0));
      $is_hidden = intval(Yii::$app->request->post('is_hidden',0));
      $disable_product_on_oos = intval(Yii::$app->request->post('disable_product_on_oos',0));
      $limit_cart_qty_by_stock = (int)(Yii::$app->request->post('limit_cart_qty_by_stock',0));
      $reset_status_on_oos = intval(Yii::$app->request->post('reset_status_on_oos',0));
      $display_price_options = intval(Yii::$app->request->post('display_price_options',0));
      $display_virtual_options = intval(Yii::$app->request->post('display_virtual_options',0));

      $stock_code = Yii::$app->request->post('stock_code','out-stock');


      $stock_indication_text = tep_db_prepare_input(Yii::$app->request->post('stock_indication_text'));

      $added = false;
      if ($stock_indication_id == 0) {
        $next_sort_order = tep_db_fetch_array(tep_db_query(
          "SELECT MAX(sort_order) AS sort_order FROM " . TABLE_PRODUCTS_STOCK_INDICATION . " where 1"
        ));
        $next_sort_order = (int)$next_sort_order['sort_order']+1;

        tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION,array(
          'is_default' => $is_default,
          'stock_code' => $stock_code,
          'allow_out_of_stock_checkout' => $allow_out_of_stock_checkout,
          'allow_out_of_stock_add_to_cart' => $allow_out_of_stock_add_to_cart,
          'allow_in_stock_notify' => $allow_in_stock_notify,
          'request_for_quote' => $request_for_quote,
          'is_hidden' => $is_hidden,
          'disable_product_on_oos' => $disable_product_on_oos,
          'limit_cart_qty_by_stock' => $limit_cart_qty_by_stock,
          'reset_status_on_oos' => $reset_status_on_oos,
          'display_price_options' => $display_price_options,
          'display_virtual_options' => $display_virtual_options,
          'sort_order' => $next_sort_order,
        ));
        $insert_id = tep_db_insert_id();
        $added = $insert_id;
      }else{
        $update_data = array(
          //'is_default' => $is_default,
          'stock_code' => $stock_code,
          'allow_out_of_stock_checkout' => $allow_out_of_stock_checkout,
          'allow_out_of_stock_add_to_cart' => $allow_out_of_stock_add_to_cart,
          'allow_in_stock_notify' => $allow_in_stock_notify,
          'request_for_quote' => $request_for_quote,
          'is_hidden' => $is_hidden,
          'display_price_options' => $display_price_options,
          'display_virtual_options' => $display_virtual_options,
          'disable_product_on_oos' => $disable_product_on_oos,
          'limit_cart_qty_by_stock' => $limit_cart_qty_by_stock,
          'reset_status_on_oos' => $reset_status_on_oos,
        );
        if ( $is_default ) {
          $update_data['is_default'] = $is_default;
        }
        tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION, $update_data,'update', "stock_indication_id='".(int)$stock_indication_id."'");

        $insert_id = $stock_indication_id;
      }

      if ($extClass = \common\helpers\Acl::checkExtensionAllowed('ObsoleteProducts', 'allowed')) {
        $extClass::stockIndicationSave($insert_id);
      }

      $languages = \common\helpers\Language::get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        $language_id = $languages[$i]['id'];

        $text_array = array(
          'stock_indication_id' => (int)$insert_id,
          'language_id' => (int)$language_id,
          'stock_indication_text' => isset($stock_indication_text[$language_id])?$stock_indication_text[$language_id]:'',
        );

        $check_text = tep_db_fetch_array(tep_db_query(
          "SELECT COUNT(*) AS c ".
          "FROM ".TABLE_PRODUCTS_STOCK_INDICATION_TEXT." ".
          "WHERE stock_indication_id='".(int)$insert_id."' AND language_id='".(int)$language_id."'"
        ));

        if ( $check_text['c']==0 ) {
          tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION_TEXT, $text_array);
        }else{
          tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION_TEXT, $text_array,'update',"stock_indication_id='".(int)$insert_id."' AND language_id='".(int)$language_id."'");
        }
      }

      if ( $is_default && $insert_id!=0 ) {
        tep_db_query("UPDATE ".TABLE_PRODUCTS_STOCK_INDICATION." SET is_default=IF(stock_indication_id='".(int)$insert_id."',1,0)");
      }

      $linked_stock_terms = Yii::$app->request->post('linked_stock_terms', []);
      $is_default_term = Yii::$app->request->post('is_default_term', 0);
      $db_terms = \yii\helpers\ArrayHelper::index(
          \common\models\ProductsStockStatusesCrossLink::find()->where(['stock_indication_id'=>$insert_id])->all(),
          'stock_delivery_terms_id'
      );
      foreach ($linked_stock_terms as $assign_term_id){
          if ( isset($db_terms[$assign_term_id]) ){
              $linkModel = $db_terms[$assign_term_id];
          }else{
              $linkModel = new \common\models\ProductsStockStatusesCrossLink([
                  'stock_indication_id' => (int)$insert_id,
                  'stock_delivery_terms_id' => (int)$assign_term_id,
              ]);
              $linkModel->loadDefaultValues();
          }
          $linkModel->is_default_term = ( $linkModel->stock_delivery_terms_id==(int)$is_default_term)?1:0;
          $linkModel->save(false);
          unset($db_terms[$assign_term_id]);
      }
      foreach ($db_terms as $notUpdatedModel) {
          $notUpdatedModel->delete();
      }

      if ($stock_indication_id == 0) {
        $action = 'added';
      }else {
        $action = 'updated';
      }

      echo json_encode(array('message' => 'Stock indication status has been  ' . $action, 'messageType' => 'alert-success', 'added' => $added,));
    }
    
    
    public function actionDelete() {
      global $language;

      $stock_indication_id =  intval(Yii::$app->request->post('stock_indication_id', 0));

      if ( !$stock_indication_id ) return;

      $get_check_data_r = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_STOCK_INDICATION." WHERE stock_indication_id='{$stock_indication_id}'");
      if ( tep_db_num_rows($get_check_data_r)==0 ) return;
      $check_data = tep_db_fetch_array($get_check_data_r);
      $remove_status = true;
      $error = array();
      if ($check_data['is_default']) {
        $remove_status = false;
        $error = array('message' => ERROR_REMOVE_DEFAULT_STOCK_INDICATION, 'messageType' => 'alert-danger');
      }
      if (!$remove_status) {
        ?>
        <div class="alert fade in <?=$error['messageType']?>">
          <i data-dismiss="alert" class="icon-remove close"></i>
          <span id="message_plce"><?=$error['message']?></span>
        </div>
        <?php
      } else {
        tep_db_query("UPDATE ".TABLE_PRODUCTS." SET stock_indication_id=0 WHERE stock_indication_id='{$stock_indication_id}'");
        tep_db_query("UPDATE ".TABLE_INVENTORY." SET stock_indication_id=0 WHERE stock_indication_id='{$stock_indication_id}'");
        tep_db_query("DELETE FROM " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " where stock_indication_id = '{$stock_indication_id}'");
        tep_db_query("DELETE FROM " . TABLE_PRODUCTS_STOCK_INDICATION . " where stock_indication_id = '{$stock_indication_id}'");
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
         "SELECT s.stock_indication_id, s.sort_order ".
         "FROM ". TABLE_PRODUCTS_STOCK_INDICATION ." s ".
         " LEFT JOIN " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " st ON st.stock_indication_id=s.stock_indication_id AND st.language_id='".(int)\Yii::$app->settings->get('languages_id') . "' " .
         "WHERE 1 ".
         "ORDER BY s.sort_order, st.stock_indication_text"
       );
       while( $order_list = tep_db_fetch_array($order_list_r) ){
         $order_counter++;
         tep_db_query("UPDATE ".TABLE_PRODUCTS_STOCK_INDICATION." SET sort_order='{$order_counter}' WHERE stock_indication_id='{$order_list['stock_indication_id']}' ");
       }
       // }} normalize
       $get_current_order_r = tep_db_query(
         "SELECT stock_indication_id, sort_order ".
         "FROM ".TABLE_PRODUCTS_STOCK_INDICATION." ".
         "WHERE stock_indication_id IN('".implode("','",$ref_array)."') ".
         "ORDER BY sort_order"
       );
       $ref_ids = array();
       $ref_so = array();
       while($_current_order = tep_db_fetch_array($get_current_order_r)){
         $ref_ids[] = (int)$_current_order['stock_indication_id'];
         $ref_so[] = (int)$_current_order['sort_order'];
       }

       foreach( $ref_array as $_idx=>$id ) {
         tep_db_query("UPDATE ".TABLE_PRODUCTS_STOCK_INDICATION." SET sort_order='{$ref_so[$_idx]}' WHERE stock_indication_id='{$id}' ");
       }

     }
   }
}
