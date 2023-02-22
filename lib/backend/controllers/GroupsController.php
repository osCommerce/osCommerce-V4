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
use yii\web\Response;

class GroupsController extends Sceleton {

    public $acl = ['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_GROUPS'];

    public function actionIndex() {
        $this->selectedMenu = array('customers', 'groups');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('groups/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $this->view->groupsTable = array(
            array(
                'title' => TABLE_HEADING_GROUPS,
                'not_important' => 1
            ),
            array(
                'title' => TABLE_HEADING_DISCOUNT,
                'not_important' => 1
            ),
        );

        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            return $ext::adminGroups();
        }
        $row = Yii::$app->request->get('row', 0);
        $messages = Yii::$app->session->getAllFlashes();
        return $this->render('index', ['messages' => $messages]);
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $gets = Yii::$app->request->get();

        $responseList = array();
        if ($length == -1)
            $length = 10000;
        $query_numrows = 0;

        $q = \common\models\Groups::find()->select('groups_id, groups_name, groups_discount')->asArray();

        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $q->andWhere(['like', 'groups_name', $keywords]);
        }

        $filters = [];
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $filters);

        /** @var \common\extensions\ExtraGroups\ExtraGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtension('ExtraGroups', 'allowed')) {
          if ($ext::allowed() && isset($filters['groups_type_id'])) {
            $q->andWhere(['groups_type_id' => (int)$filters['groups_type_id']]);
          }
        }

        if (!empty($gets['order']) && is_array($gets['order'])) {
          foreach ($gets['order'] as $so) {
            if (!empty($so['dir']) && strtolower($so['dir']) == 'desc') {
              $sd = SORT_DESC;
            } else {
              $sd = SORT_ASC;
            }

            switch ($so['column']) {
              default:
              case 0:
                $q->addOrderBy(['groups_name' => $sd]);
                break;
              case 1:
                $q->addOrderBy(['groups_discount' => $sd]);
                break;
            }
          }
        } else {
            $q->orderBy(['groups_name' => SORT_ASC]);
        }

//echo $q->createCommand()->rawSql; die;
        $current_page_number = ( $start / $length ) + 1;
        new \splitPageResults($current_page_number, $length, $q, $query_numrows, 'groups_id');
        
        foreach ( $q->all() as $groups ) {
          $divDbl = '<div class="click_double" data-click-double="' . \yii\helpers\Url::to(['groups/itemedit', 'item_id' =>  $groups['groups_id'], 'row_id' => Yii::$app->request->post('row_id',0)]) . '">';
            $responseList[] = array(

                $divDbl. $groups['groups_name'] .
                '<input class="cell_identify" type="hidden" value="' . $groups['groups_id'] . '"></div>',

                $divDbl. trim(rtrim($groups['groups_discount'], '0'), '.') . '% </div>',
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $query_numrows,
            'recordsFiltered' => $query_numrows,
            'data' => $responseList
        );
        return json_encode($response);
    }

    public function actionItempreedit() {
        $this->layout = false;
        $html = '';
        $restrictionsHtml = '';
        if ($ext = \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'allowed')) {
            if($ext::allowed()){
                $restrictionsHtml = $ext::adminShow();
            }
        }

        /** @var \common\extensions\ExtraGroups\ExtraGroups $ExtraGroups */
        if ($ExtraGroups = \common\helpers\Acl::checkExtensionAllowed('ExtraGroups', 'allowed')) {
          if (!$ExtraGroups::allowedProductRestriction(\Yii::$app->request->post('item_id', 0))) {
            $restrictionsHtml = '';
          }
        }

        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $html = $ext::adminPreeditGroups();
        }
        return $html.$restrictionsHtml;
    }

    public function actionItemedit() {
        \common\helpers\Translation::init('admin/groups');
        $this->selectedMenu = array('customers', 'groups');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('groups/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = TEXT_HEADING_EDIT_GROUP;
        $content = '';
        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $content = $ext::adminEditGroups();
        }
        if (Yii::$app->request->isAjax){
            return $this->renderAjax('edit', ['content' => $content]);
        } else {
            return $this->render('edit', ['content' => $content]);
        }
    }
   
    public function actionConfirmitemdelete() {
        $this->layout = false;

        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            return $ext::adminConfirmDeleteGroups();
        }
    }

    public function actionSubmit() {
        $this->layout = false;

        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $ext::adminSubmitGroups();
        }
        return $this->redirect(['groups/index', 
          'row' => Yii::$app->request->post('row_id', 0),
          'groups_type_id' => Yii::$app->request->post('groups_type_id', 0)
          ]);
    }

    public function actionItemdelete() {
        $this->layout = false;

        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            return $ext::adminDeleteGroups();
        }
    }

    public function actionCustomers() {
        $this->layout = false;

        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            return $ext::adminCustomersGroups();
        }
    }

    public function actionCustomersAdd() {
        $groups_id = Yii::$app->request->get('groups_id');
        $customers_id = Yii::$app->request->get('customers_id');
        tep_db_query("update " . TABLE_CUSTOMERS . " set groups_id = '" . (int) $groups_id . "' where customers_id = '" . (int) $customers_id . "'");
        return $this->actionCustomers();
    }

    public function actionCustomersDelete() {
        $customers_id = Yii::$app->request->get('customers_id');
        tep_db_query("update " . TABLE_CUSTOMERS . " set groups_id = '0' where customers_id = '" . (int) $customers_id . "'");
        return $this->actionCustomers();
    }

    public function actionRestrictions()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $html = '';
        if ($ext = \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'allowed')) {
            if($ext::allowed()){
                $groupId = (int)Yii::$app->request->get('groupId',0);
                $html = $ext::adminShowForm($groupId,$languages_id);
            }
        }
        return $html;
    }

    public function actionLoadTree() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $response_data = [];
        if ($ext = \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'allowed')) {
            if($ext::allowed()){
                $do = Yii::$app->request->post('do', '');
                $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
                $response_data = $ext::loadTree($do,$req_selected_data,$languages_id);
            }
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $response_data;
    }



    function actionUpdateCatalogSelection() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        if ($ext = \common\helpers\Acl::checkExtension('UserGroupsRestrictions', 'allowed')) {
            if($ext::allowed()){
                $req_selected_data = tep_db_prepare_input(Yii::$app->request->post('selected_data'));
                $groupId = (int)Yii::$app->request->get('groupId',0);
                $response_data = $ext::updateSelection($groupId,$req_selected_data,$languages_id);
            }
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        Yii::$app->response->data = array(
            'status' => 'ok'
        );
    }

    public function actionManufacturerSearch() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsExtraDiscounts', 'allowed')) {
            return $ext::adminManufacturerSearch();
        }
    }

    public function actionNewManufacturer() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsExtraDiscounts', 'allowed')) {
            return $ext::adminNewManufacturer();
        }
    }

    public function actionProductSearch() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsExtraDiscounts', 'allowed')) {
            return $ext::adminProductSearch();
        }
    }

    public function actionNewProduct() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsExtraDiscounts', 'allowed')) {
            return $ext::adminNewProduct();
        }
    }

    public function actionCategorySearch() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsExtraDiscounts', 'allowed')) {
            return $ext::adminCategorySearch();
        }
    }

    public function actionNewCategory() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsExtraDiscounts', 'allowed')) {
            return $ext::adminNewCategory();
        }
    }

}
