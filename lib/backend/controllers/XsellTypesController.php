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

use common\classes\language;
use common\helpers\Html;
use Yii;

/**
 * default controller to handle user requests.
 */
class XsellTypesController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_LOCALIZATION_XSELL_TYPES'];
    
    public function actionIndex() {
        $this->selectedMenu = array('settings', 'xsell-types');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('xsell-types/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="btn btn-primary" onclick="return xsellTypeEdit(0)">'.IMAGE_INSERT.'</a>';

        $this->view->xsellTypeTable = array(
            array(
                'title' => TABLE_TEXT_NAME,
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
        
        if( $length == -1 ) $length = 1000;

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search = " and (xsell_type_name like '%" . tep_db_input($keywords) . "%')";
        }
		
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "xsell_type_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "xsell_type_name";
                    break;
            }
        } else {
            $orderBy = "xsell_type_name";
        }	
		
        $current_page_number = ($start / $length) + 1;
        $responseList = array();
		
        $list_query_raw = "select * from " . TABLE_PRODUCTS_XSELL_TYPE . " where language_id='".(int)$languages_id."' " . $search . " order by " . $orderBy;
        $list_split = new \splitPageResults($current_page_number, $length, $list_query_raw, $list_query_numrows);
        $list_query = tep_db_query($list_query_raw);
        while ($list_item = tep_db_fetch_array($list_query)) {

              $responseList[] = array(
                      $list_item['xsell_type_name'] . tep_draw_hidden_field('id', $list_item['xsell_type_id'], 'class="cell_identify"'),
              );
        }

      $response = array(
            'draw' => $draw,
            'recordsTotal' => $list_query_numrows,
            'recordsFiltered' => $list_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);		  
		
    }
	
    public function actionActions(){
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/xsell-types');
				
		$xsell_type_id = Yii::$app->request->post('xsell_type_id', 0);
		$this->layout = false;
		if ($xsell_type_id){
			$xsell_type = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_XSELL_TYPE . " where xsell_type_id ='" . (int)$xsell_type_id. "' and language_id='".(int)$languages_id."'"));
			$cInfo = new \objectInfo($xsell_type, false);

			echo '<div class="or_box_head">' . $cInfo->xsell_type_name . '</div>';
			echo '<div class="row_or_wrapp">';
			echo '</div>';
			echo '<div class="btn-toolbar btn-toolbar-order">';
			echo
                '<button class="btn btn-primary btn-edit btn-no-margin" onclick="xsellTypeEdit('.(int)$xsell_type_id.')">' . IMAGE_EDIT . '</button>'.
                '<button class="btn btn-delete" onclick="xsellTypeDelete('.(int)$xsell_type_id.')">' . IMAGE_DELETE . '</button>';
			echo '</div>';
		}
	}
	
    public function actionEdit(){
        \common\helpers\Translation::init('admin/xsell-types');

        $xsell_type_id = Yii::$app->request->get('xsell_type_id', 0);

        $xsell_type_code = '';
        $xsell_type_names = [];
        if ( $xsell_type_id ) {
            $xsell_type_r = tep_db_query(
                "select * from " . TABLE_PRODUCTS_XSELL_TYPE . " where xsell_type_id ='" . (int)$xsell_type_id . "'"
            );
            if (tep_db_num_rows($xsell_type_r) > 0) {
                while ($xsell_type = tep_db_fetch_array($xsell_type_r)) {
                    $xsell_type_names[$xsell_type['language_id']] = $xsell_type['xsell_type_name'];
                    if ( (empty($xsell_type_code) || $xsell_type['language_id']==\common\helpers\Language::systemLanguageId() ) && !empty($xsell_type['xsell_type_code']) ){
                        $xsell_type_code = $xsell_type['xsell_type_code'];
                    }
                }
            }
        }

        $xsell_type_name_inputs_string = '';
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $xsell_type_name_inputs_string .=
                '<div class="langInput">' .
                $languages[$i]['image'] .
                tep_draw_input_field('xsell_type_name[' . $languages[$i]['id'] . ']', isset($xsell_type_names[$languages[$i]['id']])?$xsell_type_names[$languages[$i]['id']]:'') .
                '</div>';
        }

        echo tep_draw_form('xsell_type', 'xsell-type/save', 'xsell_type_id=' . $xsell_type_id . '&action=save');
		if($xsell_type_id){
		    echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_XSELL_TYPE . '</div>';
		} else {
		    echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_XSELL_TYPE . '</div>';
		}

		echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';

        echo '<div class="col_desc">' . TEXT_XSELL_TYPE_CODE . '</div>';
        echo Html::textInput('xsell_type_code', $xsell_type_code, ['maxlength'=>16,'class'=>'form-control']);

        echo '<div class="col_desc">' . TEXT_INFO_XSELL_TYPE_NAME . '</div>';
        echo $xsell_type_name_inputs_string;

		echo '<div class="btn-toolbar btn-toolbar-order">';
		echo
            '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="xsellTypeSave('.($xsell_type_id?$xsell_type_id:0).')">'.
            '<input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
		echo '</div>';
		echo '</form>';

	}
	
    public function actionSave(){
        \common\helpers\Translation::init('admin/xsell-types');
        
        $xsell_type_id = Yii::$app->request->get('xsell_type_id', 0);

        $xsell_type_code = tep_db_prepare_input(Yii::$app->request->post('xsell_type_code',''));
        $xsell_type_name = tep_db_prepare_input(Yii::$app->request->post('xsell_type_name',[]));
        if ( empty($xsell_type_code) ){
            $xsell_type_code = substr(\yii\helpers\Inflector::transliterate($xsell_type_name[\common\helpers\Language::systemLanguageId()]),0,16);
            $xsell_type_code = \yii\helpers\Inflector::slug($xsell_type_code,'_', false);
        }

        if ( empty($xsell_type_id) ) {
            $get_current = tep_db_fetch_array(tep_db_query(
                "SELECT MAX(xsell_type_id) AS current_id FROM ".TABLE_PRODUCTS_XSELL_TYPE
            ));
            $xsell_type_id = intval(is_array($get_current)?$get_current['current_id']:0)+1;
        }

        $languages = \common\helpers\Language::get_languages();
        $default_language_id = \common\helpers\Language::get_default_language_id();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $sql_data_array = [
                'xsell_type_code' => strtoupper($xsell_type_code),
                'xsell_type_name' => isset($xsell_type_name[$languages[$i]['id']])?$xsell_type_name[$languages[$i]['id']]:$xsell_type_name[$default_language_id],
            ];
            $check_exist = tep_db_fetch_array(tep_db_query(
                "SELECT COUNT(*) AS c " .
                "FROM " . TABLE_PRODUCTS_XSELL_TYPE . " " .
                "WHERE language_id='".$languages[$i]['id']."' AND xsell_type_id='".(int)$xsell_type_id."'"
            ));
            if ( $check_exist['c']>0 ) {
                tep_db_perform(TABLE_PRODUCTS_XSELL_TYPE, $sql_data_array, 'update', "language_id='".$languages[$i]['id']."' AND xsell_type_id='".(int)$xsell_type_id."'");
            }else{
                $sql_data_array['xsell_type_id'] = $xsell_type_id;
                $sql_data_array['language_id'] = $languages[$i]['id'];
                tep_db_perform(TABLE_PRODUCTS_XSELL_TYPE, $sql_data_array);
            }
        }

		echo json_encode(array('message' => 'success', 'messageType' => 'alert-success'));
        
	}
	
    public function actionDelete(){
        \common\helpers\Translation::init('admin/xsell-types');
      
        $xsell_type_id = tep_db_prepare_input(Yii::$app->request->post('xsell_type_id'));
        tep_db_query("DELETE FROM ".TABLE_PRODUCTS_XSELL." WHERE xsell_type_id='".(int)$xsell_type_id."'");
        tep_db_query("DELETE FROM ".TABLE_PRODUCTS_XSELL_TYPE." WHERE xsell_type_id='".(int)$xsell_type_id."'");

		echo 'reset';
    }

}
