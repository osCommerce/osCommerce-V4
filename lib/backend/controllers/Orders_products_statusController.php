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
class Orders_products_statusController extends Sceleton
{
    public $acl = ['TEXT_SETTINGS', 'BOX_SETTINGS_ORDERS_PRODUCTS_STATUS', 'BOX_ORDERS_PRODUCTS_STATUS'];

    public function actionIndex()
    {
        $this->selectedMenu = array('settings', 'status', 'orders_products_status');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders_products_status/index'), 'title' => HEADING_TITLE_ORDERS_PRODUCTS_STATUS);
        $this->view->headingTitle = HEADING_TITLE_ORDERS_PRODUCTS_STATUS;
        $this->view->StatusTable = array(
            array(
                'title' => TABLE_HEADING_ORDERS_PRODUCTS_STATUS,
                'not_important' => 0
            ),
            array(
                'title' => '',
                'not_important' => 0
            )
        );
        $messages = [];
        if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
            if (!is_array($messages)) $messages = [];
        }
        return $this->render('index', array('messages' => $messages));
    }

    public function actionList()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $opsQuery = \common\models\OrdersProductsStatus::find()
            ->andWhere(['language_id' => $languages_id]);
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $opsQuery->andWhere(['or',
                ['like', 'orders_products_status_name', tep_db_input(tep_db_prepare_input($_GET['search']['value']))],
                ['like', 'orders_products_status_name_long', tep_db_input(tep_db_prepare_input($_GET['search']['value']))]
            ]);
        }
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $opsQuery->orderBy('orders_products_status_name_long ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                case 1:
                    $opsQuery->orderBy('orders_products_status_name ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                default:
                    $opsQuery->orderBy('orders_products_status_id ASC');
                break;
            }
        } else {
            $opsQuery->orderBy('orders_products_status_id ASC');
        }
        $orders_products_status_query_numrows = $opsQuery->count();
        if ($length > 0) {
            $opsQuery->limit($length)->offset($start);
        }
        $opsQuery = $opsQuery->asArray(true)->all();
        $responseList = [];
        foreach ($opsQuery as $opsRecord) {
            $responseList[] = array(
                $opsRecord['orders_products_status_name_long'] . tep_draw_hidden_field('id', $opsRecord['orders_products_status_id'], 'class="cell_identify"'),
                $opsRecord['orders_products_status_name']
            );
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_products_status_query_numrows,
            'recordsFiltered' => $orders_products_status_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionStatusactions()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/orders_products_status');
        $this->layout = false;
        $opsRecord = \common\models\OrdersProductsStatus::findOne([
            'orders_products_status_id' => Yii::$app->request->post('orders_products_status_id', 0),
            'language_id' => $languages_id
        ]);
        if ($opsRecord) {
            $opsNameOriginal = [
                'TEXT_STATUS_LONG_' . \common\helpers\OrderProduct::getStatusArray()[$opsRecord->orders_products_status_id]['key'],
                'TEXT_STATUS_' . \common\helpers\OrderProduct::getStatusArray()[$opsRecord->orders_products_status_id]['key']
            ];
            $opsNameOriginal[0] = (defined($opsNameOriginal[0]) ? constant($opsNameOriginal[0]) : $opsNameOriginal[0]);
            $opsNameOriginal[1] = (defined($opsNameOriginal[1]) ? constant($opsNameOriginal[1]) : $opsNameOriginal[1]);
            echo '<div class="or_box_head" style="color: ' . \common\helpers\OrderProduct::getStatusArray()[$opsRecord->orders_products_status_id]['colour'] . '">(' . implode(' / ', $opsNameOriginal) . ')</div>';
            echo '<div class="or_box_head" style="color: ' . $opsRecord->getColour() . '">' . $opsRecord->orders_products_status_name_long . ' / ' . $opsRecord->orders_products_status_name . '</div>';
            $orders_products_status_inputs_string = '';
            $languages = \common\helpers\Language::get_languages();
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $orders_products_status_inputs_string .= '<div class="col_desc">' . $languages[$i]['image'] . '&nbsp;' . \common\helpers\Order::get_orders_products_status_name($opsRecord->orders_products_status_id, $languages[$i]['id']) . '</div>';
            }
            echo $orders_products_status_inputs_string;
            echo '<div class="btn-toolbar btn-toolbar-order">';
            echo '<button class="btn btn-edit btn-no-margin" onclick="statusEdit(' . $opsRecord->orders_products_status_id . ')">' . IMAGE_EDIT . '</button>';
            echo '</div>';
        }
    }

    public function actionEdit()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $this->topButtons[] = '<span class="btn btn-confirm">' . IMAGE_UPDATE . '</span>';

        \common\helpers\Translation::init('admin/orders_products_status');
        $opsRecord = \common\models\OrdersProductsStatus::findOne([
            'orders_products_status_id' => Yii::$app->request->get('orders_products_status_id', 0)
        ]);
        if (!$opsRecord) {
            return $this->redirect('index');
        }
        $orders_products_status_inputs_string = [];
        $orders_products_status_inputs_string_long = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $orders_products_status_inputs_string[$languages[$i]['id']] = \yii\helpers\Html::input(
                'text', 'orders_products_status_name[' . $languages[$i]['id'] . ']',
                \common\helpers\Order::get_orders_products_status_name($opsRecord->orders_products_status_id, $languages[$i]['id'], false),
                ['class' => 'form-control']
            );
            $orders_products_status_inputs_string_long[$languages[$i]['id']] = \yii\helpers\Html::input(
                'text', 'orders_products_status_name_long[' . $languages[$i]['id'] . ']',
                \common\helpers\Order::get_orders_products_status_name($opsRecord->orders_products_status_id, $languages[$i]['id']),
                ['class' => 'form-control']
            );
        }
        $opsmmArray = ($opsRecord ? $opsRecord->getMatrixArray(true) : []);
        $orders_products_status_manual_matrix_string = [];
        foreach (\common\models\OrdersProductsStatusManual::findAll(['language_id' => $languages_id]) as $opsmRecord) {
            $opsmId = ('opsm_' . $opsmRecord->orders_products_status_manual_id);
            $orders_products_status_manual_matrix_string[] = [
                'label' => ('<label for="' . $opsmId . '">' . $opsmRecord->orders_products_status_manual_name_long . '</label>'),
                'element' => \yii\helpers\Html::checkbox(
                    'orders_products_status_manual_matrix[' . $opsmRecord->orders_products_status_manual_id . ']',
                    isset($opsmmArray[$opsmRecord->orders_products_status_manual_id]),
                    ['id' => $opsmId, 'class' => 'form-control']
                )
            ];
        }
        $this->selectedMenu = array('settings', 'status', 'orders_products_status');
        $this->view->headingTitle = TEXT_INFO_HEADING_EDIT_ORDERS_PRODUCTS_STATUS;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders_products_status/index'), 'title' => TEXT_INFO_HEADING_EDIT_ORDERS_PRODUCTS_STATUS);
        return $this->render('edit', [
            'orders_products_status_id' => $opsRecord->orders_products_status_id,
            'orders_products_status_colour' => $opsRecord->getColour(),
            'orders_products_status_inputs_string' => $orders_products_status_inputs_string,
            'orders_products_status_inputs_string_long' => $orders_products_status_inputs_string_long,
            'orders_products_status_manual_matrix_string' => $orders_products_status_manual_matrix_string,
            'languages' => $languages
        ]);
    }

    public function actionSave()
    {
        \common\helpers\Translation::init('admin/orders_products_status');
        $opsRecord = \common\models\OrdersProductsStatus::findOne([
            'orders_products_status_id' => Yii::$app->request->get('orders_products_status_id', 0)
        ]);
        if ($opsRecord) {
            $languages = \common\helpers\Language::get_languages();
            $orders_products_status_colour = trim($_POST['orders_products_status_colour']);
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $language_id = $languages[$i]['id'];
                $orders_products_status_name_array = $_POST['orders_products_status_name'];
                $orders_products_status_name_long_array = $_POST['orders_products_status_name_long'];
                $opsRecordEdit = \common\models\OrdersProductsStatus::findOne([
                    'orders_products_status_id' => $opsRecord->orders_products_status_id,
                    'language_id' => (int)$language_id
                ]);
                $action = 'updated';
                $added = false;
                if (!$opsRecordEdit) {
                    $added = $opsRecord->orders_products_status_id;
                    $action = 'added';
                    $opsRecordEdit = new \common\models\OrdersProductsStatus();
                    $opsRecordEdit->language_id = $language_id;
                    $opsRecordEdit->orders_products_status_id = $opsRecord->orders_products_status_id;
                }
                $opsRecordEdit->orders_products_status_name = tep_db_prepare_input($orders_products_status_name_array[$language_id]);
                $opsRecordEdit->orders_products_status_name_long = tep_db_prepare_input($orders_products_status_name_long_array[$language_id]);
                $opsRecordEdit->orders_products_status_colour = $orders_products_status_colour;
                try {
                    $opsRecordEdit->save(false);
                } catch (\Exception $exc) {
                    echo '<pre>';
                    print_r($exc);
                    echo '</pre>';
                }
            }
            $opsRecord = \common\models\OrdersProductsStatus::findOne([
                'orders_products_status_id' => $opsRecord->orders_products_status_id
            ]);
            if ($opsRecord) {
                if (($opsRecord = $opsRecord->setMatrixArray(array_keys((array)\Yii::$app->request->post('orders_products_status_manual_matrix')))) !== true) {
                    echo '<pre>';
                    print_r($opsRecord);
                    echo '</pre>';
                }
            }
            echo json_encode([
                'message' => 'Status ' . $action,
                'messageType' => 'alert-success',
                'added' => $added
            ]);
        } else {
            echo json_encode([
                'message' => 'Status not found',
                'messageType' => 'alert-danger',
                'added' => false
            ]);
        }
    }
}