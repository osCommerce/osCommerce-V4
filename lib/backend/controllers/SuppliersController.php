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

use common\helpers\PriceFormula;
use Yii;
use yii\helpers\Html;
use common\models\Suppliers;

/**
 * default controller to handle user requests.
 */
class SuppliersController extends Sceleton  {

  public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_SUPPIERS'];

  private $_supplerService;

  public function __construct($id, $module, \common\services\SupplierService $service) {
      $this->_supplerService = $service;
      Yii::configure($this->_supplerService, [
          'allow_change_status' => true,
          'allow_change_default' => true,
          'allow_change_surcharge' => true,
          'allow_change_margin' => true,
          'allow_change_price_formula' => true,
          'allow_change_auth' => true,
      ]);

      parent::__construct($id, $module);
  }


  public function actionIndex() {
    $this->selectedMenu = array('catalog', 'suppliers');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('suppliers/index'), 'title' => HEADING_TITLE);
    $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('suppliers/edit') . '" class="btn btn-primary"><i class="icon-file-text"></i>' . TEXT_CREATE_NEW_SUPPLIER . '</a>';
    $this->view->headingTitle = HEADING_TITLE;

    $this->view->SupplierTable = array(
        array(
            'title' => TABLE_HEADING_SUPPLIERS,
            'not_important' => 0,
        ),
        array(
            'title' => TABLE_HEADING_STATUS,
            'not_important' => 0,
        ),
    );

    $messages = [];
    if (isset($_SESSION['messages'])) {
        $messages = $_SESSION['messages'];
        unset($_SESSION['messages']);
    }
    if (!is_array($messages)) $messages = [];

    $sID = Yii::$app->request->get('suppliers_id', 0);
    return $this->render('index', array('messages' => $messages, 'suppliers_id' => $sID));
  }

  public function actionList() {
    $draw = Yii::$app->request->get('draw', 1);
    $start = Yii::$app->request->get('start', 0);
    $length = Yii::$app->request->get('length', 10);

    $currencies = Yii::$container->get('currencies');

    $sQuery = Suppliers::find()->where('1');
    if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
        $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
        $sQuery->andWhere(['like', 'suppliers_name', "{$keywords}"]);      
    }
    
    $responseList = array();

    if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
      switch ($_GET['order'][0]['column']) {
        case 0:
            $sQuery->orderBy("suppliers_name " . tep_db_prepare_input($_GET['order'][0]['dir']));
          break;
        default:
            $sQuery->orderBy("sort_order, suppliers_name");
          break;
      }
    } else {
        $sQuery->orderBy("is_default DESC, sort_order, suppliers_id");
    }
    $numRows = $sQuery->count();
    $suppliers = $sQuery->offset($start)->limit($length)->all();
    if ($suppliers){
        foreach($suppliers as $supplier){
            $status = '<input type="checkbox" value="' . $supplier->suppliers_id . '" name="status" class="check_on_off" ' . ($supplier->status?" checked":'') . ((int) $supplier->is_default ? ' checked readonly="readonly"' : '') . '>';
            $responseList[] = array(
                '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['suppliers/edit', 'suppliers_id' => $supplier->suppliers_id]) . '">' .
                $supplier->suppliers_name . ($supplier->is_default? "&nbsp;(" . TEXT_DEFAULT .")" : "") . tep_draw_hidden_field('id', $supplier->suppliers_id, 'class="cell_identify"'). '<input class="cell_type" type="hidden" value="top">' .
                '</div></div>',
                $status
            );
        }
    }

    $response = array(
        'draw' => $draw,
        'recordsTotal' => $numRows,
        'recordsFiltered' => $numRows,
        'data' => $responseList
    );
    echo json_encode($response);
  }

  public function actionStatusactions() {
    \common\helpers\Translation::init('admin/suppliers');

    $suppliers_id = Yii::$app->request->post('suppliers_id', 0);
    $this->layout = false;

    if ($suppliers_id > 0) {
      $supplier = Suppliers::find()->where(['suppliers.suppliers_id' => (int) $suppliers_id])->one();
      if ($supplier) {
        echo '<div class="or_box_head">' . $supplier->suppliers_name . '</div>';
        echo '<div class="main_row">' . sprintf(TEXT_PRODUCTS_LINKED_TO_SUPPLIER, $supplier->getGrouppedSupplierProductsCount()) . '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo Html::a(IMAGE_EDIT, Yii::$app->urlManager->createUrl(['suppliers/edit', 'suppliers_id' => $suppliers_id]), ['class' => 'btn btn-edit btn-no-margin']);
        if (!$supplier->is_default){
            echo '<button class="btn btn-delete" onclick="supplierDeleteConfirm(' . $suppliers_id . ')">' . IMAGE_DELETE . '</button>';
        }
        echo '</div>';
      }
    }
  }

  public function actionEdit() {
    \common\helpers\Translation::init('admin/suppliers');
    \common\helpers\Translation::init('admin/platforms');
    \common\helpers\Translation::init('admin/categories');

    $suppliers_id = Yii::$app->request->get('suppliers_id', 0);

      $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#suppliers_management_data form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

    $this->_supplerService->loadSupplier($suppliers_id);

    $supplier = $this->_supplerService->get('supplier');
    $this->_supplerService->currenciesMap = \yii\helpers\ArrayHelper::index($supplier->supplierCurrencies, 'currencies_id');
    if (Yii::$app->request->isPost){
        $suppliers_data = tep_db_prepare_input(Yii::$app->request->post('suppliers_data', array()));
        if ($supplier->load($suppliers_data, '') && $supplier->validate()){
            if ($supplier->saveSupplier($suppliers_data)){
                if (is_array($suppliers_data['currencies'])){
                    $supplier->clearCurrencies();
                    $supplier->saveCurrencies($suppliers_data['currencies']);
                }
                if ($es = \common\helpers\Extensions::isAllowed('EventSystem')) {
                    $es::partner()->exec('savePartnerAdditionalFields', [$supplier->suppliers_id, Yii::$app->request->post()]);
                }
                if ($supplier->suppliers_id > 0) {
                    $item_id = $supplier->suppliers_id;
                    $suppliers_dispatch_time_ids = Yii::$app->request->post('send_hours_id', array());
                    $suppliers_dispatch_time_keys = Yii::$app->request->post('send_hours_key', array());
                    $open_time_from = Yii::$app->request->post('time_from');
                    $open_time_to = Yii::$app->request->post('time_to');
                
                    if (!is_array($suppliers_dispatch_time_ids)) {
                        $suppliers_dispatch_time_ids = [];
                    }
                    $active_open_hours_ids = [];
                    foreach ($suppliers_dispatch_time_ids as $suppliers_dispatch_time_key => $suppliers_dispatch_time_id) {
                        $open_days = (int)Yii::$app->request->post('open_days_' . $suppliers_dispatch_time_keys[$suppliers_dispatch_time_key]);
                        $sql_data_array = [
                            'days' => $open_days,
                            'time_from' => $open_time_from[$suppliers_dispatch_time_key],
                            'time_to' => $open_time_to[$suppliers_dispatch_time_key],
                        ];
                        if ((int)$suppliers_dispatch_time_id > 0) {
                            tep_db_perform('suppliers_dispatch_time', $sql_data_array, 'update', "suppliers_id = '" . (int) $item_id . "' and suppliers_dispatch_time_id = '" . (int) $suppliers_dispatch_time_id . "'");
                            $active_open_hours_ids[] = $suppliers_dispatch_time_id;
                        } else {
                            tep_db_perform('suppliers_dispatch_time', array_merge($sql_data_array, array('suppliers_id' => $item_id)));
                            $new_open_hours_id = tep_db_insert_id();
                            $active_open_hours_ids[] = $new_open_hours_id;
                        }
                    }
                    if (count($active_open_hours_ids) > 0) {
                        \common\models\SuppliersDispatchTime::deleteAll( ['AND', ['suppliers_id' => (int) $item_id], ['NOT IN', 'suppliers_dispatch_time_id', $active_open_hours_ids]] );
                    }
                }
                if (!$suppliers_id){
                    $action = 'added';
                } else {
                    $action = 'updated';
                }
                $type = 'success';
            } else {
                $action = 'error';
            }
        }
        $message_stack = Yii::$container->get('message_stack');
        $message_stack->add_session('Supplier ' . $action, 'header', $type);
        return $this->redirect(['edit', 'suppliers_id' => $supplier->suppliers_id]);
    }

    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('suppliers/'), 'title' => ($supplier->isNewRecord? TEXT_HEADING_NEW_SUPPLIER:TEXT_HEADING_EDIT_SUPPLIER) . ' ' . $supplier->suppliers_name);

    $message_stack = Yii::$container->get('message_stack');
    $messages = '';
    if ($message_stack->size() ){
        $messages = $message_stack->outputHead();
    }

    return $this->render('edit', [
        'content' => $this->_supplerService->render('\common\widgets\SupplierEdit'),
        'messages' => $messages,
        ]);

  }

  public function actionConfirmdelete() {
    global $language;
    \common\helpers\Translation::init('admin/suppliers');

    $this->layout = false;

    $suppliers_id = Yii::$app->request->post('suppliers_id');

    if ($suppliers_id > 0) {
      $suppliers = tep_db_fetch_array(tep_db_query("select suppliers_id, suppliers_name, date_added, last_modified from " . TABLE_SUPPLIERS . " where suppliers_id = '" . (int) $suppliers_id . "'"));
      $sInfo = new \objectInfo($suppliers, false);

      echo tep_draw_form('suppliers', 'suppliers/', \common\helpers\Output::get_all_get_params(array('sID', 'action')) . 'dID=' . $sInfo->suppliers_id . '&action=deleteconfirm', 'post', 'id="item_delete" onSubmit="return supplierDelete();"');

      echo '<div class="or_box_head">' . TEXT_HEADING_DELETE_SUPPLIER . '</div>';
      echo \common\helpers\Translation::getTranslationValue( 'TEXT_DELETE_INTRO', 'admin/suppliers') . '<br><br><b>' . $sInfo->suppliers_name . '</b>';
      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo '<button type="submit" class="btn btn-primary btn-no-margin">' . IMAGE_CONFIRM . '</button>';
      echo '<button class="btn btn-cancel" onClick="return resetStatement(' . (int)$suppliers_id . ')">' . IMAGE_CANCEL . '</button>';      

      echo tep_draw_hidden_field('suppliers_id', $suppliers_id);
      echo '</div></form>';
    }
  }

  public function actionDelete() {
    global $language;
    \common\helpers\Translation::init('admin/suppliers');

    $suppliers_id = Yii::$app->request->post('suppliers_id', 0);
    if ($suppliers_id > 0) {
      Suppliers::findOne(['suppliers_id' => (int)$suppliers_id])->delete();
      echo 'reset';
    }
  }

  public function actionSortOrder() {
    $moved_id = (int)$_POST['sort_top'];
    $ref_array = (isset($_POST['top']) && is_array($_POST['top']))?array_map('intval',$_POST['top']):array();
    if ( $moved_id && in_array($moved_id, $ref_array) ) {
      // {{ normalize
      $order_counter = 0;
      $order_list_r = tep_db_query(
        "SELECT suppliers_id, sort_order ".
        "FROM ". TABLE_SUPPLIERS ." ".
        "WHERE 1 ".
        "ORDER BY sort_order, suppliers_name"
      );
      while( $order_list = tep_db_fetch_array($order_list_r) ){
        $order_counter++;
        tep_db_query("UPDATE ".TABLE_SUPPLIERS." SET sort_order='{$order_counter}' WHERE suppliers_id='{$order_list['suppliers_id']}' ");
      }
      // }} normalize
      $get_current_order_r = tep_db_query(
        "SELECT suppliers_id, sort_order ".
        "FROM ".TABLE_SUPPLIERS." ".
        "WHERE suppliers_id IN('".implode("','",$ref_array)."') ".
        "ORDER BY sort_order"
      );
      $ref_ids = array();
      $ref_so = array();
      while($_current_order = tep_db_fetch_array($get_current_order_r)){
        $ref_ids[] = (int)$_current_order['suppliers_id'];
        $ref_so[] = (int)$_current_order['sort_order'];
      }

      foreach( $ref_array as $_idx=>$id ) {
        tep_db_query("UPDATE ".TABLE_SUPPLIERS." SET sort_order='{$ref_so[$_idx]}' WHERE suppliers_id='{$id}' ");
      }
    }
  }

  public function actionSwitchStatus(){
    $id = Yii::$app->request->post('id');
    $status = strtolower(Yii::$app->request->post('status', 'false'));
    if ($id){
        $supplier = Suppliers::findOne(['suppliers_id' => $id]);
        if ($supplier){
            $supplier->status = ($status == 'true' ? 1 : 0);
            $supplier->save(false);
        }
    }
  }

  public function actionGenerateKey(){
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return ['password' => \common\helpers\Password::create_random_value(6)];
  }
}

