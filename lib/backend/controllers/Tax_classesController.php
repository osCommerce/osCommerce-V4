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
class Tax_classesController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TAXES', 'BOX_TAXES_TAX_CLASSES'];
    
    public function actionIndex() {
      global $language;
      
      $this->selectedMenu = array('settings', 'taxes', 'tax_classes');
      $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('tax_classes/index'), 'title' => HEADING_TITLE);
      
      $this->view->headingTitle = HEADING_TITLE;
      $this->topButtons[] = '<a href="#" class="btn btn-primary" onclick="return taxEdit(0)">'.TEXT_INFO_HEADING_NEW_TAX_CLASS.'</a>';
	  
	  $this->view->tax_classesTable = array(
		array(
			'title' => TABLE_HEADING_TAX_CLASSES,
			'not_important' => 0,
		),
	  );

      return $this->render('index');
    }

    public function actionList(){
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search = " and (tax_class_title like '%" . $keywords . "%' or tax_class_description like '%" . $keywords . "%')";
        }
		
		$current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "tax_class_title " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "tax_class_title";
                    break;
            }
        } else {
            $orderBy = "c.countries_name, z.zone_name";
        }		

		$classes_query_raw = "select tax_class_id, tax_class_title, tax_class_description, last_modified, date_added from " . TABLE_TAX_CLASS . " where 1 " . $search . " order by " . $orderBy;
		$classes_split = new \splitPageResults($current_page_number, $length, $classes_query_raw, $classes_query_numrows);
		$classes_query = tep_db_query($classes_query_raw);
		
		while ($classes = tep_db_fetch_array($classes_query)) {
	
			$responseList[] = array(
				$classes['tax_class_title'] . tep_draw_hidden_field('id', $classes['tax_class_id'], 'class="cell_identify"'),
			);
		}
		
		$response = array(
            'draw' => $draw,
            'recordsTotal' => $classes_query_numrows,
            'recordsFiltered' => $classes_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);		  
		
	}
	
    public function actionTax_classesactions(){
        \common\helpers\Translation::init('admin/tax_classes');
		
		$tax_classes_id = Yii::$app->request->post('tax_classes_id', 0);
		$this->layout = false;
		if ($tax_classes_id){
			$tax = tep_db_fetch_array(tep_db_query("select tax_class_id, tax_class_title, tax_class_description, last_modified, date_added from " . TABLE_TAX_CLASS . " where tax_class_id = '" . (int)$tax_classes_id . "'"));
			$tcInfo = new \objectInfo($tax, false);

			echo '<div class="or_box_head">' . $tcInfo->tax_class_title . '</div>';
			echo '<div class="row_or_wrapp">';
                        echo '<div class="row_or"><div>' . TEXT_INFO_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($tcInfo->date_added) . '</div></div>';
                        echo '<div class="row_or"><div>' . TEXT_INFO_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($tcInfo->last_modified) . '</div></div>';
                        echo '<div class="row_or"><div>' . TEXT_INFO_CLASS_DESCRIPTION . '</div><div>' . $tcInfo->tax_class_description . '</div></div>';
			echo '</div>';
			echo '<div class="btn-toolbar btn-toolbar-order">';
			echo '<button class="btn btn-no-margin btn-edit" onclick="taxEdit('.$tax_classes_id.')">' . IMAGE_EDIT . '</button>';
			echo '<button class="btn btn-delete" onclick="taxDelete('.$tax_classes_id.')">' . IMAGE_DELETE . '</button>';
			echo '</div>';
		}
	  
	}
	
	public function actionEdit(){
      \common\helpers\Translation::init('admin/tax_classes');
	  
	  $tax_classes_id = Yii::$app->request->get('tax_classes_id', 0);
 	  $tax = tep_db_fetch_array(tep_db_query("select tax_class_id, tax_class_title, tax_class_description, last_modified, date_added from " . TABLE_TAX_CLASS . " where tax_class_id = '" . (int)$tax_classes_id . "'"));
	  $tcInfo = new \objectInfo($tax, false);
          $tcInfo->tax_class_id = $tcInfo->tax_class_id ?? null;

	  if($tax_classes_id){
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_TAX_CLASS . '</div>';
	  } else {
		echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_TAX_CLASS . '</div>';
	  }
		echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
		echo tep_draw_form('classes', FILENAME_TAX_CLASSES, 'page=' . \Yii::$app->request->get('page') . '&tID=' . $tcInfo->tax_class_id . '&action=save');
		echo '<div class="row_or_wrapp">';
                echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CLASS_TITLE . '</div><div class="main_value">' . tep_draw_input_field('tax_class_title', $tcInfo->tax_class_title ?? null, 'class="form-control"') . '</div></div>';
                echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_CLASS_DESCRIPTION . '</div><div class="main_value">' . tep_draw_input_field('tax_class_description', $tcInfo->tax_class_description ?? null, 'class="form-control"') . '</div></div>';
                echo '</div>';
		echo '<div class="btn-toolbar btn-toolbar-order">';
		echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="taxSave('.($tcInfo->tax_class_id?$tcInfo->tax_class_id:0).')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
		echo '</div>';
		echo '</form>';
	}
	
    public function actionSave(){
      global $language;
      \common\helpers\Translation::init('admin/tax_classes');
	  
	  $tax_class_id = Yii::$app->request->get('tax_classes_id', 0);

	  if($tax_class_id == 0){
        $tax_class_title = tep_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = tep_db_prepare_input($_POST['tax_class_description']);

        tep_db_query("insert into " . TABLE_TAX_CLASS . " (tax_class_title, tax_class_description, date_added) values ('" . tep_db_input($tax_class_title) . "', '" . tep_db_input($tax_class_description) . "', now())");

		$action		 = 'added';
	  } else {
        $tax_class_title = tep_db_prepare_input($_POST['tax_class_title']);
        $tax_class_description = tep_db_prepare_input($_POST['tax_class_description']);

        tep_db_query("update " . TABLE_TAX_CLASS . " set tax_class_id = '" . (int)$tax_class_id . "', tax_class_title = '" . tep_db_input($tax_class_title) . "', tax_class_description = '" . tep_db_input($tax_class_description) . "', last_modified = now() where tax_class_id = '" . (int)$tax_class_id . "'");

		$action		 = 'updated';
	  }
	
	echo json_encode(array('message' => 'Tax class is ' . $action, 'messageType' => 'alert-success'));
        
	}

	
    public function actionDelete(){
      global $language;
      \common\helpers\Translation::init('admin/tax_classes');	
        $tax_classes_id = Yii::$app->request->post('tax_classes_id', 0);
		
		if ($tax_classes_id)
			tep_db_query("delete from " . TABLE_TAX_CLASS . " where tax_class_id = '" . (int)$tax_classes_id . "'");

		echo 'reset';
		
	}
}
