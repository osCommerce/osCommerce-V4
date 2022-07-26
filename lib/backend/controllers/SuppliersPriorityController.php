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

use yii;

class SuppliersPriorityController extends Sceleton
{

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_SUPPLIER_PRIORITY'];

    public function actionIndex()
    {
        $this->selectedMenu = array('catalog', 'suppliers-priority');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('suppliers-priority/index'), 'title' => HEADING_TITLE);
        //$this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('suppliers/edit') . '" class="create_item"><i class="icon-file-text"></i>' . TEXT_CREATE_NEW_SUPPLIER . '</a>';
        $this->view->headingTitle = HEADING_TITLE;

        $supplier_priorities = [];
        $priorityModel = new \common\extensions\SupplierPriority\SupplierPriority();
        $supplier_priorities = $priorityModel->getModules();

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#frmMain\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        if ( Yii::$app->request->isPost ) {
            $this->layout = false;

            $update_data_array = Yii::$app->request->post('priority',[]);
            if ( is_array($update_data_array) ) {
                foreach ($update_data_array as $priorityClass=>$update_data){
                    $priorityModel->updateModule($priorityClass, $update_data);
                }
            }
            if ( Yii::$app->request->isAjax ) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                Yii::$app->response->data = [
                    'status' => 'ok',
                    'message' => TEXT_MESSEAGE_SUCCESS,
                ];
                return ;
            }else{
                return $this->redirect(['suppliers-priority/index']);
            }
        }

        return $this->render('index', array('supplier_priorities' => $supplier_priorities,));
    }

}