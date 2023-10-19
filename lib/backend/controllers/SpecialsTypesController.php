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

use common\models\Specials;
use common\models\SpecialsTypes;
use Yii;

class SpecialsTypesController extends Sceleton {

  public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_SPECIALS_TAGS'];

  public function actionIndex() {
    $this->selectedMenu = array('settings', 'specials-types');
    $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('specials-types/index'), 'title' => TEXT_SPECIALS_TAGS);

    $this->view->headingTitle = TEXT_SPECIALS_TAGS;
    $this->topButtons[] = '<a href="#" class="create_item" onclick="return specialsTypeEdit(0)">' . IMAGE_INSERT . '</a>';

    $this->view->specialsTypeTable = array(
      array(
        'title' => TABLE_TEXT_NAME,
        'not_important' => 0,
      ),
    );

    $messages = $_SESSION['messages']??null;
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

    if ($length == -1)
      $length = 1000;

    $specialsTypes = SpecialsTypes::find()->where([
      'language_id' => (int) $languages_id
    ]);
    if ($search['value']) {
      $keywords = tep_db_input(tep_db_prepare_input($search['value']));
      $specialsTypes->andWhere([
          'or',
          "specials_type_name like '%" . $keywords . "%'",
          "specials_type_code like '%" . $keywords . "%'",
          ]);
    }
    $orderBy = "specials_type_name";
    if (($order[0]['column']??null) === 0 && $order[0]['dir']) {
      $orderBy = "specials_type_name " . tep_db_prepare_input($order[0]['dir']);
    }
    $specialsTypes->orderBy($orderBy);

    $numRows = $specialsTypes->count();

    $specialsTypes->limit($length);
    $specialsTypes->offset($start);
    $specialsTypesArr = $specialsTypes->asArray()->all();

    $responseList = [];
    foreach ($specialsTypesArr as $specialsType) {
      $responseList[] = [
        $specialsType['specials_type_name'] . tep_draw_hidden_field('id', $specialsType['specials_type_id'], 'class="cell_identify"'),
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

  public function actionActions() {
    $languagesId = (int) \Yii::$app->settings->get('languages_id');
    \common\helpers\Translation::init('admin/specials-types');

    $specialsTypeId = (int) Yii::$app->request->post('specials_type_id', 0);
    $this->layout = false;
    if ($specialsTypeId) {

      $specials_type = SpecialsTypes::find()->where([
            'specials_type_id' => $specialsTypeId,
            'language_id' => $languagesId,
          ])->asArray()->one();

      $cInfo = new \objectInfo($specials_type, false);

      echo '<div class="or_box_head">' . $cInfo->specials_type_name . '</div>';
      echo '<div class="or_box_head">' . $cInfo->specials_type_code . '</div>';
      echo '<div class="row_or_wrapp">';
      echo '</div>';
      echo '<div class="btn-toolbar btn-toolbar-order">';
      echo
      '<button class="btn btn-primary btn-edit btn-no-margin" onclick="specialsTypeEdit(' . $specialsTypeId . ')">' . IMAGE_EDIT . '</button>' .
      '<button class="btn btn-delete" onclick="specialsTypeDelete(' . $specialsTypeId . ')">' . IMAGE_DELETE . '</button>';
      echo '</div>';
    }
  }

  public function actionEdit() {
    \common\helpers\Translation::init('admin/specials-types');

    $specialsTypeId = Yii::$app->request->get('specials_type_id', 0);

    $specialsTypeNames = [];
    if ($specialsTypeId) {
      $specialsTypeNames = SpecialsTypes::find()->where(['specials_type_id' => $specialsTypeId])->asArray()->indexBy('language_id')->all();
    }

    $specials_type_name_inputs_string = '';
    $specials_type_code_inputs_string = '';
    $languages = \common\helpers\Language::get_languages();
    foreach ($languages as $languages) {
      $specials_type_name_inputs_string .= '<div class="langInput">' .
          $languages['image'] .
          tep_draw_input_field('specials_type_name[' . $languages['id'] . ']', isset($specialsTypeNames[$languages['id']]['specials_type_name']) ? $specialsTypeNames[$languages['id']]['specials_type_name'] : '') .
          '</div>';
      $specials_type_code_inputs_string .= '<div class="langInput">' .
          $languages['image'] .
          tep_draw_input_field('specials_type_code[' . $languages['id'] . ']', isset($specialsTypeNames[$languages['id']]['specials_type_code']) ? $specialsTypeNames[$languages['id']]['specials_type_code'] : '') .
          '</div>';
    }

    echo tep_draw_form('specials_type', 'specials-type/save', 'specials_type_id=' . $specialsTypeId . '&action=save');
    if ($specialsTypeId) {
      echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_SPECIALS_TAG . '</div>';
    } else {
      echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_SPECIALS_TAG . '</div>';
    }

    //echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';

    echo '<div class="col_desc">' . TEXT_INFO_SPECIALS_TAG_NAME . '</div>';
    echo $specials_type_name_inputs_string;

    echo '<div class="col_desc">' . TEXT_INFO_SPECIALS_TAG_CODE . '</div>';
    echo $specials_type_code_inputs_string;

    echo '<div class="btn-toolbar btn-toolbar-order">';
    echo
    '<input type="button" value="' . ($specialsTypeId?IMAGE_UPDATE:IMAGE_SAVE) . '" class="btn btn-no-margin" onclick="specialsTypeSave(' . ($specialsTypeId ? $specialsTypeId : 0) . ')">' .
    '<input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()">';
    echo '</div>';
    echo '</form>';
  }

  public function actionSave() {
    \common\helpers\Translation::init('admin/specials-types');

    $specialsTypeId = Yii::$app->request->get('specials_type_id', 0);

    $specialsTypeNames = tep_db_prepare_input(Yii::$app->request->post('specials_type_name', []));
    $specialsTypeCodes = tep_db_prepare_input(Yii::$app->request->post('specials_type_code', []));
    $new = false;

    if (empty($specialsTypeId)) {
      $specialsTypeId = SpecialsTypes::find()->max('specials_type_id') + 1;
      $new = true;
    }

    $languages = \common\helpers\Language::get_languages();
    $defaultLanguageId = \common\helpers\Language::get_default_language_id();
    $res = array('message' => 'success', 'messageType' => 'alert-success');

    foreach ($languages as $language) {
      $type = SpecialsTypes::findOne([
            'specials_type_id' => $specialsTypeId,
            'language_id' => $language['id']
      ]);
      if (!$type) {
        $type = new SpecialsTypes();
      }
      try {
        $type->attributes = [
          'specials_type_id' => $specialsTypeId,
          'language_id' => $language['id'],
          'specials_type_name' => $specialsTypeNames[$language['id']] ? $specialsTypeNames[$language['id']] : $specialsTypeNames[$defaultLanguageId],
          'specials_type_code' => $specialsTypeCodes[$language['id']] ? $specialsTypeCodes[$language['id']] : $specialsTypeCodes[$defaultLanguageId]
        ];
        $r = $type->save(false);
        if (!$r) {
          $res = array('message' => 'not validated' . $e->getMessage(), 'messageType' => 'alert-warning');
        }
      } catch (\Exception $e) {
        $res = array('message' => $e->getMessage(), 'messageType' => 'alert-warning');
        if ($new ) {
          SpecialsTypes::deleteAll(['specials_type_id' => $specialsTypeId]);
          break;
        }
        
      }
    }

    echo json_encode($res);
  }

  public function actionDelete() {
    \common\helpers\Translation::init('admin/specials-types');
    $specialsTypeId = (int) Yii::$app->request->post('specials_type_id');

    //Specials::findAll(['specials_type_id' => $specialsTypeId])->delete();
    SpecialsTypes::deleteAll(['specials_type_id' => $specialsTypeId]);
    tep_db_query("update " . TABLE_SPECIALS . " set specials_type_id=0 where specials_type_id='" . (int)$specialsTypeId . "'");

    echo 'reset';
  }

}
