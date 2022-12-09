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
class Tax_ratesController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TAXES', 'BOX_TAXES_TAX_RATES'];
    
    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'taxes', 'tax_rates');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('tax_rates/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;
      $this->topButtons[] = '<a href="#" class="btn btn-primary" onclick="return taxEdit(0)">'.TEXT_INFO_HEADING_NEW_TAX_RATE.'</a>';
	  
      $this->view->tax_ratesTable = array(
      array(
        'title' => TABLE_HEADING_TAX_RATE_PRIORITY,
        'not_important' => 0,
      ),
      array(
        'title' => TABLE_HEADING_TAX_CLASS_TITLE,
        'not_important' => 0,
      ),
      array(
        'title' => TABLE_HEADING_ZONE,
        'not_important' => 0,
      ),
      array(
        'title' => TABLE_HEADING_TAX_RATE,
        'not_important' => 0,
      ),
      );

      $params = [
          'ns_tax_rate' => '',
          'ns_tax_rates' => []
      ];
      if (\common\helpers\Acl::checkExtensionAllowed('NetSuite') && \common\extensions\NetSuite\helpers\NetSuiteHelper::anyConfigured()) {
        $r = tep_db_query("select ld.directory_id, ld.directory  "
            . " from ep_directories ld "
            . " where ld.directory_config like '%NetSuiteLink%'  and ld.directory_type='datasource' "
            . " " );
        while ($d = tep_db_fetch_array($r)) {
          $params['ns_tax_rates'][$d['directory_id']] = \common\extensions\NetSuite\helpers\NetSuiteHelper::getKVArray($d['directory_id'], 'nsSaleTaxItem_');
        }
      }
      return $this->render('index', $params);
    }

    public function actionList(){
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search = " and (tc.tax_class_title like '%" . $keywords . "%' or tc.tax_class_description like '%" . $keywords . "%' or r.tax_description like '%" . $keywords . "%' or z.geo_zone_name like '%" . $keywords . "%' )";
        }
		
		$current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "r.tax_priority " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 1:
                    $orderBy = "tc.tax_class_title " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 2:
                    $orderBy = "z.geo_zone_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;					
                default:
                    $orderBy = "tc.tax_class_title";
                    break;
            }
        } else {
            $orderBy = "tc.tax_class_title";
        }		

 	    $rates_query_raw = "select r.tax_rates_id, z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.tax_priority, r.tax_rate, r.tax_description, r.date_added, r.last_modified from " . TABLE_TAX_CLASS . " tc, " . TABLE_TAX_RATES . " r left join " . TABLE_TAX_ZONES . " z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id $search order by ". $orderBy;
	    $rates_split = new \splitPageResults($current_page_number, $length, $rates_query_raw, $rates_query_numrows);
	    $rates_query = tep_db_query($rates_query_raw);
		
		while ($rates = tep_db_fetch_array($rates_query)) {
	
			$responseList[] = array(
				$rates['tax_priority'] . tep_draw_hidden_field('id', $rates['tax_rates_id'], 'class="cell_identify"'),
				$rates['tax_class_title'],
				$rates['geo_zone_name'],
				\common\helpers\Tax::display_tax_value($rates['tax_rate']).'%',
			);
		}
		
		$response = array(
            'draw' => $draw,
            'recordsTotal' => $rates_query_numrows,
            'recordsFiltered' => $rates_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);		  
		
	}
	
    public function actionTax_ratesactions(){
        \common\helpers\Translation::init('admin/tax_rates');
		
		$tax_rates_id = Yii::$app->request->post('tax_rates_id', 0);
		$this->layout = false;
		if ($tax_rates_id){
			$rates = tep_db_fetch_array(tep_db_query("select z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.* from " . TABLE_TAX_CLASS . " tc, " . TABLE_TAX_RATES . " r left join " . TABLE_TAX_ZONES . " z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id and r.tax_rates_id = '" . (int)$tax_rates_id . "'"));
			$trInfo = new \objectInfo($rates);
			$heading = array();
			$contents = array();		

/*EP NS Sync */
      $nsBlock = '';
      if (\common\helpers\Acl::checkExtensionAllowed('NetSuite') && \common\extensions\NetSuite\helpers\NetSuiteHelper::anyConfigured()) {
        $r = tep_db_query("select key_value, mp.remote_id, ld.directory_id, ld.directory  "
            . " from ep_directories ld "
            . " left join ep_holbi_soap_mapping mp on ld.directory_id=mp.ep_directory_id and mapping_type='taxrate' and local_id='" . tep_db_input($trInfo->tax_rates_id ) ."'"
            . " left join ep_holbi_soap_kv_storage lp on ld.directory_id=lp.ep_directory_id and key_name= concat('nsSaleTaxItem_', mp.remote_id) "
            . " where ld.directory_config like '%NetSuiteLink%'  and ld.directory_type='datasource' "
            . " " );
        $nsBlock .= '<br />';
        while ($d = tep_db_fetch_array($r)) {
          $nsBlock .= '<div class="ep-sync ep-sync-ns"> <div class="ns-info">' . $d['directory'] . ' ' . (!empty($d['key_value'])?$d['key_value']:'') . '</div><div class="ns-buttons"><button class="btn btn-sync btn-no-margin" onclick="linkNS(\'' . $d['remote_id'] . '\',\'' . $trInfo->tax_rates_id . '\',' . (int)$d['directory_id'] . ')">' . TEXT_UPDATE_EXTERNAL_ID . '</button>'.   '</div></div>';
        }
      }
/*EP NS Sync */

			$heading[] = array('text' => '<b>' . $trInfo->tax_class_title . '</b>');
			echo '<div class="or_box_head">' . $trInfo->tax_class_title . '</div>';
			echo '<div class="row_or_wrapp">';
				echo '<div class="row_or"><div>' . TEXT_INFO_DATE_ADDED . '</div><div>' .  \common\helpers\Date::date_short($trInfo->date_added) . '</div></div>';
				echo '<div class="row_or"><div>' . TEXT_INFO_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($trInfo->last_modified) . '</div></div>';
				echo '<div class="row_or"><div>' . TEXT_INFO_RATE_DESCRIPTION . '</div><div>' . $trInfo->tax_description . '</div></div>';
                if (!empty($trInfo->min_total)) {
                    echo '<div class="row_or"><div>' . TEXT_MIN_ORDER_TOTAL . '</div><div>' . number_format($trInfo->min_total, 2) . '</div></div>';
                }
                if (!is_null($trInfo->max_total) && $trInfo->max_total>=0) {
                    echo '<div class="row_or"><div>' . TEXT_MAX_ORDER_TOTAL . '</div><div>' . number_format($trInfo->max_total, 2) . '</div></div>';
                }
                if (!empty($trInfo->company_name)) {
                    echo '<div class="row_or"><div>' . TEXT_COMPANY. '</div><div>' . $trInfo->company_name . '</div></div>';
                }
                if (!empty($trInfo->company_number)) {
                    echo '<div class="row_or"><div>' . ENTRY_BUSINESS . '</div><div>' . $trInfo->company_number . '</div></div>';
                }
                if (!empty($trInfo->company_address)) {
                    echo '<div class="row_or"><div>' . CATEGORY_ADDRESS . '</div><div>' . $trInfo->company_address . '</div></div>';
                }
				echo '</div>';
			echo '<div class="btn-toolbar btn-toolbar-order">';
			echo '<button class="btn btn-edit btn-no-margin" onclick="taxEdit('.$tax_rates_id.')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="taxDelete('.$tax_rates_id.')">' . IMAGE_DELETE . '</button>' . $nsBlock;
			echo '</div>';

		}
	  
	}
	
    public function actionEdit(){
      \common\helpers\Translation::init('admin/tax_rates');
	  
	  $tax_rates_id = Yii::$app->request->get('tax_rates_id', 0);
	  $rates = tep_db_fetch_array(tep_db_query("select z.geo_zone_id, z.geo_zone_name, tc.tax_class_title, tc.tax_class_id, r.* from " . TABLE_TAX_CLASS . " tc, " . TABLE_TAX_RATES . " r left join " . TABLE_TAX_ZONES . " z on r.tax_zone_id = z.geo_zone_id where r.tax_class_id = tc.tax_class_id and r.tax_rates_id = '" . (int)$tax_rates_id . "'"));
	  $trInfo = new \objectInfo($rates);
          $trInfo->tax_rates_id = $trInfo->tax_rates_id ?? null;
	  
	  if($tax_rates_id){
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_TAX_RATE . '</div>';
	  } else {
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_TAX_RATE . '</div>';
	  }
		echo tep_draw_form('rates', FILENAME_TAX_RATES, 'page=' . \Yii::$app->request->get('page') . '&tID=' . $trInfo->tax_rates_id  . '&action=save');
		echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CLASS_TITLE . '</div><div class="main_value">' . \common\helpers\Tax::tax_classes_pull_down('name="tax_class_id" style="font-size:10px" class="form-control"', $trInfo->tax_class_id ?? null) . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_ZONE_NAME . '</div><div class="main_value">' . \common\helpers\Zones::geo_zones_pull_down('name="tax_zone_id" style="font-size:10px" class="form-control"', $trInfo->geo_zone_id ?? null) . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_TAX_RATE . '</div><div class="main_value">' . tep_draw_input_field('tax_rate', $trInfo->tax_rate ?? null, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_RATE_DESCRIPTION . '</div><div class="main_value">' . tep_draw_input_field('tax_description', $trInfo->tax_description ?? null, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_TAX_RATE_PRIORITY . '</div><div class="main_value">' . tep_draw_input_field('tax_priority', $trInfo->tax_priority ?? null, 'class="form-control"') . '</div></div>';
        
		echo '<div class="main_row"><div class="main_title">' . TEXT_MIN_ORDER_TOTAL . '</div><div class="main_value">' . tep_draw_input_field('min_total', $trInfo->min_total ?? null, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_MAX_ORDER_TOTAL . '</div><div class="main_value">' . tep_draw_input_field('max_total', $trInfo->max_total ?? null, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . TEXT_COMPANY . '</div><div class="main_value">' . tep_draw_input_field('company_name', $trInfo->company_name ?? null, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . ENTRY_BUSINESS . '</div><div class="main_value">' . tep_draw_input_field('company_number', $trInfo->company_number ?? null, 'class="form-control"') . '</div></div>';
		echo '<div class="main_row"><div class="main_title">' . CATEGORY_ADDRESS . '</div><div class="main_value">' . tep_draw_textarea_field('company_address', 'soft', 50, 3, $trInfo->company_address?? null, 'class="form-control"') . '</div></div>';
		echo '<div class="btn-toolbar btn-toolbar-order">';
		echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="taxSave('.($trInfo->tax_rates_id?$trInfo->tax_rates_id:0).')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
		echo '</div>';
		echo '</form>';
	}
	
    public function actionSave(){
      global $language;
        \common\helpers\Translation::init('admin/tax_rates');
	  
        $tax_rates_id = Yii::$app->request->get('tax_rates_id', 0);
        $tax_zone_id = tep_db_prepare_input($_POST['tax_zone_id']);
        $tax_class_id = tep_db_prepare_input($_POST['tax_class_id']);
        $tax_rate = tep_db_prepare_input($_POST['tax_rate']);
        $tax_description = tep_db_prepare_input($_POST['tax_description']);
        $tax_priority = tep_db_prepare_input($_POST['tax_priority']);
        
        $minTotal = (float)\Yii::$app->request->post('min_total', 0);
        $maxTotal = \Yii::$app->request->post('max_total', '');
        $company_number = \Yii::$app->request->post('company_number', '');
        $company_name = \Yii::$app->request->post('company_name', '');
        $company_address = \Yii::$app->request->post('company_address', '');
        if (!is_numeric($maxTotal)) {
            $maxTotal = 'null';
        }
        
        //description should be unique :(
        $q = \common\models\TaxRates::find()->andWhere(['tax_description' => tep_db_input($tax_description)]);
        if($tax_rates_id > 0){
           $q->andWhere(['<>', 'tax_rates_id', $tax_rates_id]);
        }
        if ($q->exists()) {
            echo json_encode(array('message' => 'Tax rate description should be unique ', 'messageType' => 'alert-danger', 'error' => 1));
            exit();
        }

        if($tax_rates_id == 0){

          tep_db_query("insert into " . TABLE_TAX_RATES . " (tax_zone_id, tax_class_id, tax_rate, tax_description, tax_priority, date_added, min_total, max_total, company_number, company_name, company_address) values ('" . (int)$tax_zone_id . "', '" . (int)$tax_class_id . "', '" . tep_db_input($tax_rate) . "', '" . tep_db_input($tax_description) . "', '" . tep_db_input($tax_priority) . "', now(), '" . $minTotal . "', " . $maxTotal . ", '" . tep_db_input($company_number) . "', '" . tep_db_input($company_name) . "', '" . tep_db_input($company_address) . "')");

          $action		 = 'added';
        } else {

          tep_db_query("update " . TABLE_TAX_RATES . " set tax_rates_id = '" . (int)$tax_rates_id . "', tax_zone_id = '" . (int)$tax_zone_id . "', tax_class_id = '" . (int)$tax_class_id . "', tax_rate = '" . tep_db_input($tax_rate) . "', tax_description = '" . tep_db_input($tax_description) . "', tax_priority = '" . tep_db_input($tax_priority) . "', last_modified = now(), min_total={$minTotal}, max_total={$maxTotal}, company_number='" . tep_db_input($company_number) . "', company_name='" . tep_db_input($company_name) . "' , company_address='" . tep_db_input($company_address) . "' where tax_rates_id = '" . (int)$tax_rates_id . "'");

          $action		 = 'updated';
        }

      echo json_encode(array('message' => 'Tax rate is ' . $action, 'messageType' => 'alert-success'));
        
	}

	
    public function actionDelete(){
      global $language;
      \common\helpers\Translation::init('admin/tax_rates');	
        $tax_rates_id = Yii::$app->request->post('tax_rates_id', 0);
		
		if ($tax_rates_id)
			tep_db_query("delete from " . TABLE_TAX_RATES . " where tax_rates_id = '" . (int)$tax_rates_id . "'");

		echo 'reset';
		
	}

  public function actionNsSyncUpdateId() {
    if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_EASYPOPULATE'])) {
        $l_id = Yii::$app->request->post('l_id', ''); 
        $n_id = Yii::$app->request->post('n_id', '');
        $d_id = Yii::$app->request->post('d_id', 0);


        if ($d_id>0 && !empty($n_id) && !empty($n_id) && !empty($n_id) ) {
          tep_db_query("delete FROM ep_holbi_soap_mapping WHERE ep_directory_id='" . (int)$d_id . "' AND mapping_type='taxrate' and local_id='" . (int)$l_id . "'");
          $data = [
            'ep_directory_id' => (int)$d_id,
            'local_id' => (int)$l_id,
            'remote_id' => (int)$n_id,
            'mapping_type' => 'taxrate',
              ];
          tep_db_perform('ep_holbi_soap_mapping', $data);
          $ret = ['status'=>"OK"];
        } else {
          $ret = ['status'=>"OK", 'data' => 'error'];
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $ret;
    }
  }

}
