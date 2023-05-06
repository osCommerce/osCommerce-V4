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

use common\models\Featured;
use common\models\FeaturedTypes;
use Yii;

/**
 * default controller to handle user requests.
 */
class FeaturedTypesController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_FEATURED_TYPES'];
    
    public function actionIndex() {
        $this->selectedMenu = array('settings', 'featured-types');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('featured-types/index'), 'title' => TEXT_FEATURED_TYPES);

        $this->view->headingTitle = TEXT_FEATURED_TYPES;
        $this->topButtons[] = '<a href="#" class="btn btn-primary" onclick="return featuredTypeEdit(0)">'.IMAGE_INSERT.'</a>';

        $this->view->featuredTypeTable = array(
            array(
                'title' => TABLE_TEXT_NAME,
                'not_important' => 0,
            ),
        );

        $messages = Yii::$app->session->get('messages');
        unset($_SESSION['messages']);
        return $this->render('index', array('messages' => $messages));
    }

    public function actionList() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $search = Yii::$app->request->get('search', []);
        $order = Yii::$app->request->get('order', []);

        if( $length == -1 ) $length = 1000;

        $featuredTypes = FeaturedTypes::find()->where([
            'language_id' => (int)$languages_id
        ]);
        if ($search['value']) {
            $keywords = tep_db_input(tep_db_prepare_input($search['value']));
            $featuredTypes->andWhere("featured_type_name like '%" . $keywords . "%'");
        }
        $orderBy = "featured_type_name";
        if (($order[0]['column']??null) === 0 && ($order[0]['dir']??null)) {
            $orderBy = "featured_type_name " . tep_db_prepare_input($order[0]['dir']);
        }
        $featuredTypes->orderBy($orderBy);

        $numRows = $featuredTypes->count();

        $featuredTypes->limit($length);
        $featuredTypes->offset($start);
        $featuredTypesArr = $featuredTypes->asArray()->all();

        $responseList = [];
        foreach ($featuredTypesArr as $featuredType) {
            $responseList[] = [
                $featuredType['featured_type_name'] . tep_draw_hidden_field('id', $featuredType['featured_type_id'], 'class="cell_identify"'),
            ];
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $numRows,
            'recordsFiltered' => $numRows,
            'data' => $responseList
        );
        echo json_encode($response);
		
    }
	
    public function actionActions(){
        $languagesId = (int)\Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/featured-types');
				
		$featuredTypeId = (int)Yii::$app->request->post('featured_type_id', 0);
		$this->layout = false;
		if ($featuredTypeId){

            $featured_type = FeaturedTypes::find()->where([
                'featured_type_id' => $featuredTypeId,
                'language_id' => $languagesId,
            ])->asArray()->one();

			$cInfo = new \objectInfo($featured_type, false);

			echo '<div class="or_box_head">' . $cInfo->featured_type_name . '</div>';
			echo '<div class="row_or_wrapp">';
			echo '</div>';
			echo '<div class="btn-toolbar btn-toolbar-order">';
			echo
                '<button class="btn btn-primary btn-edit btn-no-margin" onclick="featuredTypeEdit(' . $featuredTypeId.')">' . IMAGE_EDIT . '</button>'.
                '<button class="btn btn-delete" onclick="featuredTypeDelete(' . $featuredTypeId . ')">' . IMAGE_DELETE . '</button>';
			echo '</div>';
		}
	}
	
    public function actionEdit(){
        \common\helpers\Translation::init('admin/featured-types');

        $featuredTypeId = Yii::$app->request->get('featured_type_id', 0);

        $featuredTypeNames = [];
        if ( $featuredTypeId ) {

            $featuredTypes = FeaturedTypes::find()->where(['featured_type_id' => $featuredTypeId])->asArray()->all();
            foreach ($featuredTypes as $featuredType) {
                $featuredTypeNames[$featuredType['language_id']] = $featuredType['featured_type_name'];
            }
        }

        $featured_type_name_inputs_string = '';
        $languages = \common\helpers\Language::get_languages();
        foreach ($languages as $languages) {
            $featured_type_name_inputs_string .=
                '<div class="langInput">' .
                $languages['image'] .
                tep_draw_input_field('featured_type_name[' . $languages['id'] . ']', isset($featuredTypeNames[$languages['id']]) ? $featuredTypeNames[$languages['id']] : '') .
                '</div>';
        }

        echo tep_draw_form('featured_type', 'featured-type/save', 'featured_type_id=' . $featuredTypeId . '&action=save');
		if($featuredTypeId){
		    echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_FEATURED_TYPE . '</div>';
		} else {
		    echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_FEATURED_TYPE . '</div>';
		}

		//echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';

        echo '<div class="col_desc">' . TEXT_INFO_FEATURED_TYPE_NAME . '</div>';
        echo $featured_type_name_inputs_string;

		echo '<div class="btn-toolbar btn-toolbar-order">';
		echo
            '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="featuredTypeSave('.($featuredTypeId?$featuredTypeId:0).')">'.
            '<input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
		echo '</div>';
		echo '</form>';

	}
	
    public function actionSave(){
        \common\helpers\Translation::init('admin/featured-types');

        $featuredTypeId = Yii::$app->request->get('featured_type_id', 0);

        $featuredTypeNames = tep_db_prepare_input(Yii::$app->request->post('featured_type_name',[]));

        if ( empty($featuredTypeId) ) {
            $featuredTypeId = FeaturedTypes::find()->max('featured_type_id') + 1;
        }

        $languages = \common\helpers\Language::get_languages();
        $defaultLanguageId = \common\helpers\Language::get_default_language_id();

        foreach ($languages as $language) {
            $type = FeaturedTypes::findOne([
                'featured_type_id' => $featuredTypeId,
                'language_id' => $language['id']
            ]);
            if (!$type) {
                $type = new FeaturedTypes();
            }
            $type->attributes = [
                'featured_type_id' => $featuredTypeId,
                'language_id' => $language['id'],
                'featured_type_name' => $featuredTypeNames[$language['id']] ? $featuredTypeNames[$language['id']] : $featuredTypeNames[$defaultLanguageId]
            ];
            $type->save();
        }

		echo json_encode(array('message' => 'success', 'messageType' => 'alert-success'));
        
	}
	
    public function actionDelete(){
        \common\helpers\Translation::init('admin/featured-types');
        $featuredTypeId = (int)Yii::$app->request->post('featured_type_id');

        foreach(Featured::findAll(['featured_type_id' => $featuredTypeId]) as $featuredModel){
            $featuredModel->delete();
        }
        if ($featuredTypeModel = FeaturedTypes::findOne(['featured_type_id' => $featuredTypeId])){
            $featuredTypeModel->delete();
        }

		echo 'reset';
    }

}
