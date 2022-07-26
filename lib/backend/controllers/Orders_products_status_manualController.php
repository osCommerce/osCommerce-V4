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
class Orders_products_status_manualController extends Sceleton
{
    public $acl = ['TEXT_SETTINGS', 'BOX_SETTINGS_ORDERS_PRODUCTS_STATUS', 'BOX_ORDERS_PRODUCTS_STATUS_MANUAL'];

    public function actionIndex()
    {
        $this->selectedMenu = array('settings', 'status', 'orders_products_status_manual');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders_products_status_manual/index'), 'title' => HEADING_TITLE_ORDERS_PRODUCTS_STATUS);
        $this->view->headingTitle = HEADING_TITLE_ORDERS_PRODUCTS_STATUS;
        $this->topButtons[] = '<a href="#" class="btn btn-primary" onclick="return statusEdit(0)">' . TEXT_INFO_HEADING_NEW_ORDERS_PRODUCTS_STATUS . '</a>';
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
        $opsmQuery = \common\models\OrdersProductsStatusManual::find()
            ->andWhere(['language_id' => $languages_id]);
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $opsmQuery->andWhere(['or',
                ['like', 'orders_products_status_manual_name', tep_db_input(tep_db_prepare_input($_GET['search']['value']))],
                ['like', 'orders_products_status_manual_name_long', tep_db_input(tep_db_prepare_input($_GET['search']['value']))]
            ]);
        }
        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $opsmQuery->orderBy('orders_products_status_manual_name_long ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                case 1:
                    $opsmQuery->orderBy('orders_products_status_manual_name ' . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir'])));
                break;
                default:
                    $opsmQuery->orderBy('orders_products_status_manual_id ASC');
                break;
            }
        } else {
            $opsmQuery->orderBy('orders_products_status_manual_id ASC');
        }
        $orders_products_status_manual_query_numrows = $opsmQuery->count();
        if ($length > 0) {
            $opsmQuery->limit($length)->offset($start);
        }
        $opsmQuery = $opsmQuery->asArray(true)->all();
        $responseList = [];
        foreach ($opsmQuery as $opsmRecord) {
            $responseList[] = array(
                $opsmRecord['orders_products_status_manual_name_long'] . tep_draw_hidden_field('id', $opsmRecord['orders_products_status_manual_id'], 'class="cell_identify"'),
                $opsmRecord['orders_products_status_manual_name']
            );
        }
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $orders_products_status_manual_query_numrows,
            'recordsFiltered' => $orders_products_status_manual_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionStatusactions()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/orders_products_status_manual');
        $this->layout = false;
        $opsmRecord = \common\models\OrdersProductsStatusManual::findOne([
            'orders_products_status_manual_id' => Yii::$app->request->post('orders_products_status_manual_id', 0),
            'language_id' => $languages_id
        ]);
        if ($opsmRecord) {
            echo '<div class="or_box_head" style="color: ' . $opsmRecord->getColour() . '">' . $opsmRecord->orders_products_status_manual_name_long . ' / ' . $opsmRecord->orders_products_status_manual_name . '</div>';
            $orders_products_status_manual_inputs_string = '';
            $languages = \common\helpers\Language::get_languages();
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $orders_products_status_manual_inputs_string .= '<div class="col_desc">' . $languages[$i]['image'] . '&nbsp;' . \common\helpers\Order::get_orders_products_status_manual_name($opsmRecord->orders_products_status_manual_id, $languages[$i]['id']) . '</div>';
            }
            echo $orders_products_status_manual_inputs_string;
            echo '<div class="btn-toolbar btn-toolbar-order">';
            echo '<button class="btn btn-edit btn-no-margin" onclick="statusEdit(' . $opsmRecord->orders_products_status_manual_id . ')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="statusDelete(' . $opsmRecord->orders_products_status_manual_id . ')">' . IMAGE_DELETE . '</button>';
            echo '</div>';
        }
    }

    public function actionEdit()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $this->topButtons[] = '<span class="btn btn-confirm">' . IMAGE_SAVE . '</span>';

        \common\helpers\Translation::init('admin/orders_products_status_manual');
        $opsmRecord = \common\models\OrdersProductsStatusManual::findOne([
            'orders_products_status_manual_id' => Yii::$app->request->get('orders_products_status_manual_id', 0),
            'language_id' => $languages_id
        ]);
        $orders_products_status_manual_id = 0;
        $orders_products_status_manual_colour = '#000000';
        if ($opsmRecord) {
            $orders_products_status_manual_id = $opsmRecord->orders_products_status_manual_id;
            $orders_products_status_manual_colour = $opsmRecord->getColour();
        }
        $orders_products_status_manual_inputs_string = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $orders_products_status_manual_inputs_string[$languages[$i]['id']] = \yii\helpers\Html::input(
                'text', 'orders_products_status_manual_name[' . $languages[$i]['id'] . ']',
                \common\helpers\Order::get_orders_products_status_manual_name($orders_products_status_manual_id, $languages[$i]['id'], false),
                ['class' => 'form-control']
            );
            $orders_products_status_manual_inputs_string_long[$languages[$i]['id']] = \yii\helpers\Html::input(
                'text', 'orders_products_status_manual_name_long[' . $languages[$i]['id'] . ']',
                \common\helpers\Order::get_orders_products_status_manual_name($orders_products_status_manual_id, $languages[$i]['id']),
                ['class' => 'form-control']
            );
        }
        $opsmmArray = ($opsmRecord ? $opsmRecord->getMatrixArray(true) : []);
        $orders_products_status_matrix_string = [];
        foreach (\common\models\OrdersProductsStatus::findAll(['language_id' => $languages_id]) as $opsRecord) {
            $opsId = ('ops_' . $opsRecord->orders_products_status_id);
            $orders_products_status_matrix_string[] = [
                'label' => ('<label for="' . $opsId . '">' . $opsRecord->orders_products_status_name_long . '</label>'),
                'element' => \yii\helpers\Html::checkbox(
                    'orders_products_status_matrix[' . $opsRecord->orders_products_status_id . ']',
                    isset($opsmmArray[$opsRecord->orders_products_status_id]),
                    ['id' => $opsId, 'class' => 'form-control']
                )
            ];
        }
        if ($orders_products_status_manual_id) {
            $title = TEXT_INFO_HEADING_EDIT_ORDERS_PRODUCTS_STATUS;
        } else {
            $title = TEXT_INFO_HEADING_NEW_ORDERS_PRODUCTS_STATUS;
        }
        $this->selectedMenu = array('settings', 'status', 'orders_products_status_manual');
        $this->view->headingTitle = $title;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders_products_status_manual/index'), 'title' => $title);
        return $this->render('edit', [
            'orders_products_status_manual_id' => $orders_products_status_manual_id,
            'orders_products_status_manual_colour' => $orders_products_status_manual_colour,
            'orders_products_status_manual_inputs_string' => $orders_products_status_manual_inputs_string,
            'orders_products_status_manual_inputs_string_long' => $orders_products_status_manual_inputs_string_long,
            'orders_products_status_matrix_string' => $orders_products_status_matrix_string,
            'languages' => $languages
        ]);
    }

    public function actionSave()
    {
        \common\helpers\Translation::init('admin/orders_products_status_manual');
        $insert_id = $orders_products_status_manual_id = intval(Yii::$app->request->get('orders_products_status_manual_id', 0));
        if ($orders_products_status_manual_id == 0) {
            $next_id = \common\models\OrdersProductsStatusManual::find()->select('max(orders_products_status_manual_id) AS count')->asArray(true)->one();
            $insert_id = $next_id['count'] + 1;
        }
        $languages = \common\helpers\Language::get_languages();
        $orders_products_status_manual_colour = trim($_POST['orders_products_status_manual_colour']);
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $orders_products_status_manual_name_array = $_POST['orders_products_status_manual_name'];
            $orders_products_status_manual_name_long_array = $_POST['orders_products_status_manual_name_long'];
            $language_id = $languages[$i]['id'];
            $opsmRecord = \common\models\OrdersProductsStatusManual::findOne([
                'orders_products_status_manual_id' => $orders_products_status_manual_id,
                'language_id' => (int)$language_id
            ]);
            $action = 'updated';
            $added = false;
            if (!$opsmRecord) {
                $added = $insert_id;
                $action = 'added';
                $opsmRecord = new \common\models\OrdersProductsStatusManual();
                $opsmRecord->language_id = $language_id;
                $opsmRecord->orders_products_status_manual_id = $orders_products_status_manual_id == 0 ? $insert_id : $orders_products_status_manual_id;
            }
            $opsmRecord->orders_products_status_manual_name = tep_db_prepare_input($orders_products_status_manual_name_array[$language_id]);
            $opsmRecord->orders_products_status_manual_name_long = tep_db_prepare_input($orders_products_status_manual_name_long_array[$language_id]);
            $opsmRecord->orders_products_status_manual_colour = $orders_products_status_manual_colour;
            try {
                $opsmRecord->save(false);
            } catch (\Exception $e) {
                echo '<pre>';
                print_r($e);
                echo '</pre>';
            }
        }
        $opsmRecord = \common\models\OrdersProductsStatusManual::findOne([
            'orders_products_status_manual_id' => ($orders_products_status_manual_id == 0 ? $insert_id : $orders_products_status_manual_id)
        ]);
        if ($opsmRecord) {
            if (($opsmRecord = $opsmRecord->setMatrixArray(array_keys((array)$_POST['orders_products_status_matrix']))) !== true) {
                echo '<pre>';
                print_r($opsmRecord);
                echo '</pre>';
            }
        }
        echo json_encode([
            'message' => 'Status ' . $action,
            'messageType' => 'alert-success',
            'added' => $added,
        ]);
    }

    public function actionDelete()
    {
        \common\helpers\Translation::init('admin/orders_products_status_manual');
        $orders_products_status_manual_id = Yii::$app->request->post('orders_products_status_manual_id', 0);
        if ($orders_products_status_manual_id) {
            $remove_status = true;
            $product = \common\models\OrdersProducts::find()->select('COUNT(*) AS count')->andWhere(['orders_products_status_manual' => $orders_products_status_manual_id])->asArray(true)->one();
            $error = array();
            if ($product['count'] > 0) {
                $remove_status = false;
                $error = array('message' => ERROR_ORDERS_PRODUCTS_STATUS_USED_IN_ORDERS_PRODUCTS, 'messageType' => 'alert-danger');
            } else {
                $history = \common\models\OrdersProductsStatusHistory::find()->select('COUNT(*) AS count')->andWhere(['orders_products_status_manual_id' => $orders_products_status_manual_id])->asArray(true)->one();
                if ($history['count'] > 0) {
                    $remove_status = false;
                    $error = array('message' => ERROR_ORDERS_PRODUCTS_STATUS_USED_IN_ORDERS_PRODUCTS_HISTORY, 'messageType' => 'alert-danger');
                }
            }
            if (!$remove_status) {
                ?>
                <div class="alert fade in <?= $error['messageType'] ?>">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"><?= $error['message'] ?></span>
                </div>
                <?php
            } else {
                $opsmRecord = \common\models\OrdersProductsStatusManual::findOne([
                    'orders_products_status_manual_id' => $orders_products_status_manual_id
                ]);
                if ($opsmRecord) {
                    $opsmRecord = $opsmRecord->setMatrixArray(array());
                }
                \common\models\OrdersProductsStatusManual::deleteAll(['orders_products_status_manual_id' => $orders_products_status_manual_id]);
                echo 'reset';
            }
        }
    }
}