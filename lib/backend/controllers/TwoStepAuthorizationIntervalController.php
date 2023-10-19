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
class TwoStepAuthorizationIntervalController extends Sceleton
{
    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_CONFIGURATION', 'BOX_TWO_STEP_AUTH_INTERVAL'];

    public function actionIndex()
    {
        $this->selectedMenu = array('settings', 'configuration', 'two-step-authorization-interval');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('two-step-authorization-interval/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="create_item" onclick="return tsaiEdit(0);">'.TEXT_TSAI_BUTTON_NEW.'</a>';

        $this->view->tsaiTable = array(
            array(
                'title' => TEXT_TSAI_TABLE_HEADING,
                'not_important' => 0,
            ),
            array(
                'title' => TEXT_SORT_ORDER,
                'not_important' => 0,
            )
        );

        $messages = $_SESSION['messages']??null;
        unset($_SESSION['messages']);
        if (!is_array($messages)) $messages = [];
        return $this->render('index', array('messages' => $messages));
    }

    public function actionList()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $orderBy = true;
        $responseList = array();
        $select = \common\models\AdminLoginExpire::find()->where(['ale_language_id' => $languages_id])->offset($start)->limit($length);
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $select->orderBy(['ale_title' => ((strtoupper(trim($_GET['order'][0]['dir'])) == 'ASC') ? SORT_ASC : SORT_DESC)]);
                    $orderBy = false;
                break;
                case 1:
                    $select->orderBy(['ale_order' => ((strtoupper(trim($_GET['order'][0]['dir'])) == 'ASC') ? SORT_ASC : SORT_DESC)]);
                    $orderBy = false;
                break;
            }
        }
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $select->andWhere(['like', 'ale_title', trim($_GET['search']['value'])]);
        }
        if ($orderBy == true) {
            $select->orderBy(['ale_order' => SORT_ASC]);
        }
        $count = $select->count();
        foreach ($select->asArray(true)->all() as $row) {
            $responseList[] = array(
                $row['ale_title'] . tep_draw_hidden_field('id', $row['ale_id'], 'class="cell_identify"'),
                $row['ale_order']
            );
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $count,
            'recordsFiltered' => $count,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionView()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/two-step-authorization-interval');
        $ale_id = Yii::$app->request->post('ale_id', 0);
        $this->layout = false;
        if ($ale_id) {
            $oInfo = \common\models\AdminLoginExpire::findOne(['ale_id' => (int)$ale_id, 'ale_language_id' => $languages_id]);
            if ($oInfo instanceof \common\models\AdminLoginExpire) {
                echo '<div class="or_box_head">' . $oInfo->ale_title . '</div>';
                $inputs_string = '';
                $languages = \common\helpers\Language::get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                    $title = '';
                    $aleRecord = \common\models\AdminLoginExpire::findOne(['ale_id' => (int)$oInfo->ale_id, 'ale_language_id' => (int)$languages[$i]['id']]);
                    if ($aleRecord instanceof \common\models\AdminLoginExpire) {
                        $title = $aleRecord->ale_title;
                    }
                    $inputs_string .= '<div class="col_desc">' . $languages[$i]['image'] . '&nbsp;' . $title . '</div>';
                }
                echo $inputs_string;
                echo '<div class="btn-toolbar btn-toolbar-order">';
                echo '<button class="btn btn-edit btn-no-margin" onclick="tsaiEdit(' . (int)$oInfo->ale_id . ');">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="tsaiDelete(' . (int)$oInfo->ale_id . ');">' . IMAGE_DELETE . '</button>';
                echo '</div>';
            }
        }
    }

    public function actionEdit()
    {
        \common\helpers\Translation::init('admin/two-step-authorization-interval');
        $ale_id = (int)Yii::$app->request->get('ale_id', 0);
        $oInfo = \common\models\AdminLoginExpire::findOne(['ale_id' => $ale_id]);
        if (!($oInfo instanceof \common\models\AdminLoginExpire)) {
            $oInfo = new \common\models\AdminLoginExpire();
        }
        echo tep_draw_form('tsaiEditForm', 'two-step-authorization-interval/save', 'ale_id=' . (int)$oInfo->ale_id);
        if ($ale_id > 0) {
            echo '<div class="or_box_head">' . TEXT_TSAI_EDIT . '</div>';
        } else {
            echo '<div class="or_box_head">' . TEXT_TSAI_NEW . '</div>';
        }
        $inputs_string = '';
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $title = '';
            $aleRecord = \common\models\AdminLoginExpire::findOne(['ale_id' => (int)$oInfo->ale_id, 'ale_language_id' => (int)$languages[$i]['id']]);
            if ($aleRecord instanceof \common\models\AdminLoginExpire) {
                $title = $aleRecord->ale_title;
            }
            $inputs_string .= '<div class="langInput">' . $languages[$i]['image'] . tep_draw_input_field('ale_title[' . $languages[$i]['id'] . ']', $title) . '</div>';
        }
        echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
        echo '<div class="main_row"><div class="main_title">' . TEXT_TSAI_TITLE . '</div><div class="main_value">' . $inputs_string . '</div></div>';
        echo '<div class="main_row"><div class="main_title">' . TEXT_TSAI_EXPIRE_MINUTES . '</div><div class="main_value">' . tep_draw_input_field('ale_expire_minutes', trim((int)$oInfo->ale_expire_minutes)) . '</div></div>';
        echo '<div class="main_row"><div class="main_title">' . TEXT_SORT_ORDER . '</div><div class="main_value">' . tep_draw_input_field('ale_order', trim((int)$oInfo->ale_order)) . '</div></div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="tsaiSave(' . (int)((int)$oInfo->ale_id > 0 ? $oInfo->ale_id : 0) . ');"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="tsaiReset();">';
        echo '</div>';
        echo '</form>';
    }

    public function actionSave()
    {
        \common\helpers\Translation::init('admin/two-step-authorization-interval');
        $ale_id = (int)Yii::$app->request->get('ale_id', 0);
        $ale_title = Yii::$app->request->post('ale_title', array());
        $ale_title = (is_array($ale_title) ? $ale_title : array());
        $ale_expire_minutes = (int)Yii::$app->request->post('ale_expire_minutes', 0);
        $ale_expire_minutes = (($ale_expire_minutes < 0) ? 0 : $ale_expire_minutes);
        $ale_order = (int)Yii::$app->request->post('ale_order', 0);
        $ale_order = (($ale_order < 0) ? 0 : $ale_order);
        $action = 'updated';
        if ($ale_id <= 0) {
            $action = 'added';
            $ale_id = 1;
            $aleRecord = \common\models\AdminLoginExpire::find()->orderBy(['ale_id' => SORT_DESC])->one();
            if ($aleRecord instanceof \common\models\AdminLoginExpire) {
                $ale_id = ((int)$aleRecord->ale_id + 1);
            }
        }
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $language_id = (int)$languages[$i]['id'];
            $aleRecord = \common\models\AdminLoginExpire::findOne(['ale_id' => $ale_id, 'ale_language_id' => $language_id]);
            if (!($aleRecord instanceof \common\models\AdminLoginExpire)) {
                $aleRecord = new \common\models\AdminLoginExpire();
            }
            $aleRecord->ale_id = $ale_id;
            $aleRecord->ale_language_id = $language_id;
            $aleRecord->ale_title = trim(isset($ale_title[$language_id]) ? $ale_title[$language_id] : '');
            $aleRecord->ale_expire_minutes = $ale_expire_minutes;
            $aleRecord->ale_order = $ale_order;
            $aleRecord->save();
        }
        echo json_encode(array('message' => 'Authorization interval ' . $action, 'messageType' => 'alert-success'));
    }

    public function actionDelete()
    {
        \common\helpers\Translation::init('admin/two-step-authorization-interval');
        $ale_id = (int)Yii::$app->request->post('ale_id', 0);
        if ($ale_id > 0) {
            \common\models\AdminLoginExpire::deleteAll(['ale_id' => $ale_id]);
        }
    }
}
