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
use \common\helpers\Translation;

class ProductsAssetsFieldsController extends Sceleton {

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_PRODUCTS_ASSETS_FIELDS'];

    public function __construct($id, $module=null) {
      Translation::init('admin/products-assets-fields');
      parent::__construct($id, $module);
    }

    public function actionIndex() {
        $this->selectedMenu = array('catalog', 'products-assets-fields');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('products-assets-fields/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="create_item" onclick="return productsAssetsFieldEdit(0)">' . IMAGE_NEW_PRODUCTS_ASSETS_FIELD . '</a>';

        $this->view->productsAssetsFieldTable = array(
            array(
                'title' => TABLE_HEADING_PRODUCTS_ASSETS_FIELDS_NAME,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_PRODUCTS_ASSETS_COUNT,
                'not_important' => 0,
            ),
        );

        $messages = $_SESSION['messages'] ?? null;
        unset($_SESSION['messages']);
        if (!is_array($messages)) $messages = [];

        $pafID = Yii::$app->request->get('pafID', 0);
        return $this->render('index', array('messages' => $messages, 'pafID' => $pafID));
    }

    public function actionList() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and (paf.products_assets_fields_name like '%" . $keywords . "%')";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "paf.products_assets_fields_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "products_assets_count " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "paf.products_assets_fields_sortorder, paf.products_assets_fields_name";
                    break;
            }
        } else {
            $orderBy = "paf.products_assets_fields_sortorder, paf.products_assets_fields_name";
        }

        $products_assets_fields_query_raw = "select paf.products_assets_fields_id, paf.products_assets_fields_name, count(distinct pav.products_assets_id) as products_assets_count from " . TABLE_PRODUCTS_ASSETS_FIELDS . " paf left join " . TABLE_PRODUCTS_ASSETS_VALUES . " pav on pav.products_assets_fields_id = paf.products_assets_fields_id and length(pav.products_assets_value) > 0 where paf.language_id = '" . (int)$languages_id . "' " . $search . " group by paf.products_assets_fields_id order by " . $orderBy;
        $products_assets_fields_split = new \splitPageResults($current_page_number, $length, $products_assets_fields_query_raw, $products_assets_fields_query_numrows, 'paf.products_assets_fields_id');
        $products_assets_fields_query = tep_db_query($products_assets_fields_query_raw);

        while ($products_assets_fields = tep_db_fetch_array($products_assets_fields_query)) {
            $responseList[] = array(
                '<div class="handle_cat_list state-disabled"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="prod_name"><table class="wrapper"><tr><td><span class="prodNameC">' . $products_assets_fields['products_assets_fields_name'] . '</span></td></tr></table>' . tep_draw_hidden_field('id', $products_assets_fields['products_assets_fields_id'], 'class="cell_identify"') . '<input class="cell_type" type="hidden" value="products_assets_field"></div></div>',
                $products_assets_fields['products_assets_count'],
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $products_assets_fields_query_numrows,
            'recordsFiltered' => $products_assets_fields_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionStatusactions() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
            echo $ext::adminStatusactionsAssets();
        }
    }

    public function actionEdit() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
            echo $ext::adminEditAssets();
        }
    }

    public function actionSave() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
            $action = $ext::adminSaveAssets();
        }

        echo json_encode(array('message' => 'Products Assets Field ' . $action, 'messageType' => 'alert-success'));
    }

    public function actionConfirmdelete() {
        $this->layout = false;

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
            echo $ext::adminConfirmdeleteAssets();
        }
    }

    public function actionDelete() {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
            echo $ext::adminDeleteAssets();
        }
    }

    public function actionSortOrder() {
        $products_assets_fields_sorted = Yii::$app->request->post('products_assets_field', array());
        foreach ($products_assets_fields_sorted as $sort_order => $products_assets_fields_id) {
            tep_db_query("update " . TABLE_PRODUCTS_ASSETS_FIELDS . " set products_assets_fields_sortorder = '" . (int)($sort_order) . "' where products_assets_fields_id = '" . (int)$products_assets_fields_id . "'");
        }
    }

    public function actionOrderProductAssignShow()
    {
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
            return $ext::adminOrderProductAssign();
        }
    }
}
