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

class ProductsGroupsController extends Sceleton {

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_PRODUCTS_GROUPS'];
    
    public function __construct($id, $module=null) {
      Translation::init('admin/products-groups');
      parent::__construct($id, $module);
    }    

    public function actionIndex() {
        $this->selectedMenu = array('catalog', 'products-groups');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('products-groups/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="btn btn-primary" onclick="return productsGroupEdit(0)">' . IMAGE_NEW_PRODUCTS_GROUP . '</a>';

        $this->view->productsGroupTable = array(
            array(
                'title' => TABLE_HEADING_PRODUCTS_GROUPS_NAME,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_PRODUCTS_COUNT,
                'not_important' => 0,
            ),
        );

        $messages = [];
        if (isset($_SESSION['messages'])) {
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
        }
        if (!is_array($messages)) $messages = [];

        $eID = Yii::$app->request->get('eID', 0);
        return $this->render('index', array('messages' => $messages, 'eID' => $eID));
    }

    public function actionList() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and (pg.products_groups_name like '%" . $keywords . "%')";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "pg.products_groups_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "products_count " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "pg.products_groups_name";
                    break;
            }
        } else {
            $orderBy = "pg.products_groups_name";
        }

        $products_groups_query_raw = "select pg.products_groups_id, pg.products_groups_name, count(p.products_id) as products_count from " . TABLE_PRODUCTS_GROUPS . " pg left join " . TABLE_PRODUCTS . " p on pg.products_groups_id = p.products_groups_id where pg.language_id = '" . (int)$languages_id . "' " . $search . " group by pg.products_groups_id order by " . $orderBy;
        $products_groups_split = new \splitPageResults($current_page_number, $length, $products_groups_query_raw, $products_groups_query_numrows, 'pg.products_groups_id');
        $products_groups_query = tep_db_query($products_groups_query_raw);

        while ($products_groups = tep_db_fetch_array($products_groups_query)) {
//            $image = \common\helpers\Image::info_image($products_groups['products_groups_image'], $products_groups['products_groups_name'], 50, 50);
            $responseList[] = array(
                /* (tep_not_null($image) && $image != TEXT_IMAGE_NONEXISTENT ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') . */ '<table class="wrapper"><tr><td><span class="prodNameC">' . $products_groups['products_groups_name'] . '</span></td></tr></table>' . tep_draw_hidden_field('id', $products_groups['products_groups_id'], 'class="cell_identify"') . '<input class="cell_type" type="hidden" value="products_group">',
                $products_groups['products_count'],
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $products_groups_query_numrows,
            'recordsFiltered' => $products_groups_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionStatusactions() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/products-groups');

        $products_groups_id = Yii::$app->request->post('products_groups_id', 0);
        $this->layout = false;
        if ($products_groups_id) {
            $products_groups = tep_db_fetch_array(tep_db_query("select products_groups_id, products_groups_name from " . TABLE_PRODUCTS_GROUPS . " where products_groups_id = '" . (int) $products_groups_id . "' and language_id = '" . (int)$languages_id . "'"));
            $eInfo = new \objectInfo($products_groups, false);
            $heading = array();
            $contents = array();

            if (is_object($eInfo)) {
                echo '<div class="or_box_head">' . $eInfo->products_groups_name . '</div>';

                $products_group_inputs_string = '';
                $languages = \common\helpers\Language::get_languages();
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $products_group_inputs_string .= '<div class="col_desc">' . $languages[$i]['image'] . '&nbsp;' . \common\helpers\Product::products_groups_name($eInfo->products_groups_id, $languages[$i]['id']) . '</div>';
                }
                echo $products_group_inputs_string;

                echo '<div class="btn-toolbar btn-toolbar-order">';
                echo '<a class="btn btn-primary btn-process-order btn-edit" href="' . Yii::$app->urlManager->createUrl(['products-groups/products', 'products_groups_id' => $products_groups_id]) . '">' . FIELDSET_ASSIGNED_PRODUCTS . '</a>';
                echo '<button class="btn btn-edit btn-no-margin" onclick="productsGroupEdit(' . $products_groups_id . ')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="productsGroupDeleteConfirm(' . $products_groups_id . ')">' . IMAGE_DELETE . '</button>';
                echo '</div>';
            }

        }
    }

    public function actionEdit() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/products-groups');

        $products_groups_id = Yii::$app->request->get('products_groups_id', 0);
        $products_groups = tep_db_fetch_array(tep_db_query("select products_groups_id, products_groups_name from " . TABLE_PRODUCTS_GROUPS . " where products_groups_id = '" . (int) $products_groups_id . "' and language_id = '" . (int)$languages_id . "'"));
        $eInfo = new \objectInfo($products_groups, false);

        echo tep_draw_form('products_group', FILENAME_PRODUCTS_GROUPS . '/save', 'products_groups_id=' . ($eInfo->products_groups_id ?? null), 'post', 'onsubmit="return productsGroupSave(' . ($eInfo->products_groups_id ?? 0) . ');"');

        if ($products_groups_id) {
            echo '<div class="or_box_head">' . TEXT_EDIT_INTRO . '</div>';
        } else {
            echo '<div class="or_box_head">' . TEXT_NEW_INTRO . '</div>';
        }

        $products_group_inputs_string = '';
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $products_group_inputs_string .= '<div class="langInput">' . $languages[$i]['image'] . tep_draw_input_field('products_groups_name[' . $languages[$i]['id'] . ']', \common\helpers\Product::products_groups_name($eInfo->products_groups_id ?? null, $languages[$i]['id'])) . '</div>';
        }
        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_PRODUCTS_GROUPS_NAME . '</div><div class="main_value">' . $products_group_inputs_string . '</div></div>';
/*
        echo '<div class="main_row"><div class="main_title">' . TEXT_INFO_PRODUCTS_GROUPS_IMAGE . '</div><div class="main_value"><div class="products_groups_image" data-name="products_groups_image" data-value="' . $eInfo->products_groups_image . '"></div></div></div>
<script type="text/javascript">
$(".products_groups_image").image_uploads();
</script>';
*/
        echo '<div class="btn-toolbar btn-toolbar-order">';
        if ($products_groups_id) {
            echo '<input type="submit" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement(' . (int)($eInfo->products_groups_id ?? null). ')">';
        } else {
            echo '<input type="submit" value="' . IMAGE_NEW . '" class="btn btn-no-margin"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement(' . (int)($eInfo->products_groups_id ?? null) . ')">';
        }

        echo '</div>';
        echo '</form>';
    }

    public function actionSave() {
        \common\helpers\Translation::init('admin/products-groups');
        $products_groups_id = intval(Yii::$app->request->get('products_groups_id', 0));
        $products_groups_name = tep_db_prepare_input(Yii::$app->request->post('products_groups_name', array()));
//        $products_groups_image = tep_db_prepare_input(Yii::$app->request->post('products_groups_image', ''));

        if ($products_groups_id == 0) {
            $next_id_query = tep_db_query("select max(products_groups_id) as products_groups_id from " . TABLE_PRODUCTS_GROUPS . " where 1");
            $next_id = tep_db_fetch_array($next_id_query);
            $insert_id = $next_id['products_groups_id'] + 1;
        }
/*
        if ($products_groups_image == 'del') {
            $products_groups_image = '';
        } elseif ($products_groups_image != '') {
          $path = \Yii::getAlias('@webroot');
          $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
          $tmp_name = $path . $products_groups_image;
          $new_name = DIR_FS_CATALOG_IMAGES . $products_groups_id . '-' . $products_groups_image;
          @copy($tmp_name, $new_name);
          @unlink($tmp_name);
          $products_groups_image = $products_groups_id . '-' . $products_groups_image;
        }
*/
        $languages = \common\helpers\Language::get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = array('products_groups_name' => $products_groups_name[$language_id],
                                    /* 'products_groups_image' => $products_groups_image */);

            if ($products_groups_id == 0) {
                $insert_sql_data = array('products_groups_id' => $insert_id,
                                         'language_id' => $language_id,
                                         'date_added' => 'now()');
                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                tep_db_perform(TABLE_PRODUCTS_GROUPS, $sql_data_array);
                $action = 'Added';
            } else {
                $update_sql_data = array('date_last_modified' => 'now()');
                $sql_data_array = array_merge($sql_data_array, $update_sql_data);

                $check = tep_db_fetch_array(tep_db_query("select count(products_groups_id) as products_groups_exists from " . TABLE_PRODUCTS_GROUPS . " where products_groups_id = '" . (int)$products_groups_id . "' and language_id = '" . (int)$language_id . "'"));
                if (!$check['products_groups_exists']) {
                    $insert_sql_data = array('products_groups_id' => $products_groups_id,
                                             'language_id' => $language_id,
                                             'date_added' => 'now()');
                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                    tep_db_perform(TABLE_PRODUCTS_GROUPS, $sql_data_array);
                } else {
                    tep_db_perform(TABLE_PRODUCTS_GROUPS, $sql_data_array, 'update', "products_groups_id = '" . (int) $products_groups_id . "' and language_id = '" . (int)$language_id . "'");
                }
                $action = 'Updated';
            }
        }
        \common\helpers\ProductsGroupSortCache::update();

        echo json_encode(array('message' => 'Products Group ' . $action, 'messageType' => 'alert-success'));
    }

    public function actionConfirmdelete() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $this->layout = false;

        $products_groups_id = Yii::$app->request->post('products_groups_id', 0);

        if ($products_groups_id > 0) {
            $products_groups = tep_db_fetch_array(tep_db_query("select products_groups_id, products_groups_name from " . TABLE_PRODUCTS_GROUPS . " where language_id = '" . (int)$languages_id . "' and products_groups_id = '" . (int)$products_groups_id . "'"));
            $eInfo = new \objectInfo($products_groups, false);

            echo tep_draw_form('products_groups', FILENAME_PRODUCTS_GROUPS, \common\helpers\Output::get_all_get_params(array('eID', 'action')) . 'dID=' . $eInfo->products_groups_id . '&action=deleteconfirm', 'post', 'id="item_delete" onSubmit="return productsGroupDelete();"');

            echo '<div class="or_box_head">' . $eInfo->products_groups_name . '</div>';
            echo TEXT_DELETE_INTRO . '<br>';
            echo '<div class="btn-toolbar btn-toolbar-order">';
            echo '<button type="submit" class="btn btn-primary btn-no-margin">' . IMAGE_CONFIRM . '</button>';
            echo '<button class="btn btn-cancel" onClick="return resetStatement(' . (int)$products_groups_id . ')">' . IMAGE_CANCEL . '</button>';      

            echo tep_draw_hidden_field('products_groups_id', $products_groups_id);
            echo '</div></form>';
        }
    }

    public function actionDelete() {
        \common\helpers\Translation::init('admin/products-groups');

        $products_groups_id = Yii::$app->request->post('products_groups_id', 0);

        if ($products_groups_id) {
            tep_db_query("delete from " . TABLE_PRODUCTS_GROUPS . " where products_groups_id = '" . (int)$products_groups_id . "'");
            tep_db_query("update " . TABLE_PRODUCTS . " set products_groups_id = '0' where products_groups_id = '" . (int)$products_groups_id . "'");
            \common\helpers\ProductsGroupSortCache::update();
            echo 'reset';
        }
    }

    public function actionProducts() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/categories');

        $currencies = Yii::$container->get('currencies');

        $this->selectedMenu = array('catalog', 'products-groups');

        $products_groups_id = Yii::$app->request->get('products_groups_id', 0);
        $products_groups = tep_db_fetch_array(tep_db_query("select products_groups_id, products_groups_name from " . TABLE_PRODUCTS_GROUPS . " where products_groups_id = '" . (int) $products_groups_id . "' and language_id = '" . (int)$languages_id . "'"));
        $eInfo = new \objectInfo($products_groups, false);

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#products_groups_products\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        $this->navigation[]       = array('link' => Yii::$app->urlManager->createUrl('products-groups/products'), 'title' => sprintf(HEADING_TITLE_EDIT_PRODUCTS, $eInfo->products_groups_name));
        $this->view->headingTitle = sprintf(HEADING_TITLE_EDIT_PRODUCTS, $eInfo->products_groups_name);

        $this->view->usePopupMode = false;
        if (Yii::$app->request->isAjax) {
          $this->layout = false;
          $this->view->usePopupMode = true;
        }

        $groupProducts = [];
        $query = tep_db_query("select p.products_id, p.products_model, p.products_status, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = p.products_id and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and p.products_groups_id = '" . (int)$eInfo->products_groups_id . "' order by p.products_groups_sort, pd.products_name");
        while ($data = tep_db_fetch_array($query)) {
            $groupProducts[] = [
                'products_id' => $data['products_id'],
                'id' => $data['products_id'],
                'products_name' => $data['products_name'],
                'name' => $data['products_name'],
                'products_model' => $data['products_model'],
                'image' => \common\classes\Images::getImage($data['products_id'], 'Small'),
                'price' => $currencies->format(\common\helpers\Product::get_products_price($data['products_id'])),
                'status_class' => ($data['products_status'] == 0 ? 'dis_prod' : ''),
            ];
        }
        $this->view->groupProducts = $groupProducts;

        return $this->render('products.tpl', ['eInfo' => $eInfo]);
    }

    public function actionProductsUpdate() {
        $products_groups_id = Yii::$app->request->post('products_groups_id');
        $products_group_products_id = Yii::$app->request->post('products_group_products_id', array());

        $all_products_array = array();
        foreach ($products_group_products_id as $sort_order => $products_id) {
            tep_db_query("update " . TABLE_PRODUCTS . " set products_groups_id = '" . (int)$products_groups_id . "', products_groups_sort = '" . (int)$sort_order . "' where products_id = '" . (int)$products_id . "'");
            $all_products_array[] = (int) $products_id;
        }
        tep_db_query("update " . TABLE_PRODUCTS . " set products_groups_id = '0', products_groups_sort = '0' where products_groups_id = '" . (int)$products_groups_id . "' and products_id not in ('" . implode("','", $all_products_array) . "')");

        if (Yii::$app->request->isAjax) {
//          $this->layout = false;
        } else {
            return $this->redirect(Yii::$app->urlManager->createUrl(['products-groups/index', 'eID' => $products_groups_id]));
        }
    }

    public function actionProductSearch() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $q = Yii::$app->request->get('q');

        $products_string = '';

        $categories = \common\helpers\Categories::get_category_tree(0, '', '0', '', true);
        foreach ($categories as $category) {
            $products_query = tep_db_query("select distinct p.products_id, p.products_model, pd.products_name, p.products_status from " . TABLE_PRODUCTS . " p LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p.products_id = p2c.products_id LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id where p2c.categories_id = '" . $category['id'] . "' and pd.language_id = '" . (int) $languages_id . "' and p.products_groups_id = '0' and (p.products_model like '%" . tep_db_input($q) . "%' or pd.products_name like '%" . tep_db_input($q) . "%') group by p.products_id order by p.sort_order, pd.products_name limit 0, 100");
            if (tep_db_num_rows($products_query) > 0) {
                $products_string .= '<optgroup label="' . $category['text'] . '">';
                while ($products = tep_db_fetch_array($products_query)) {
                    $products_string .= '<option value="' . $products['products_id'] . '" ' . ($products['products_status'] == 0 ? ' class="dis_prod"' : '') . '>' . $products['products_name'] . (empty($products['products_model'])?'':(' '.$products['products_model'])) . '</option>';
                }
                $products_string .= '</optgroup>';
            }
        }

        echo $products_string;
    }

    public function actionNewProduct() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $this->layout = false;

        $currencies = Yii::$container->get('currencies');

        $products_id = (int) Yii::$app->request->post('products_id');
        $query = tep_db_query("select p.products_id, p.products_model, pd.products_name, p.products_status from " . TABLE_PRODUCTS_DESCRIPTION . " pd," . TABLE_PRODUCTS . " p where language_id = '" . (int)$languages_id . "' and platform_id = '".intval(\common\classes\platform::defaultId())."' and p.products_id = '" . (int)$products_id . "' and p.products_id =  pd.products_id limit 1");
        if (tep_db_num_rows($query) > 0) {
            $data = tep_db_fetch_array($query);
        } else {
            $data = array();
        }

        if (count($data) > 0) {
            $groupProduct = [
                'products_id' => $data['products_id'],
                'products_name' => $data['products_name'],
                'products_model' => $data['products_model'],
                'image' => \common\classes\Images::getImage($data['products_id'], 'Small'),
                'price' => $currencies->format(\common\helpers\Product::get_products_price($data['products_id'])),
                'status_class' => ($data['products_status'] == 0 ? 'dis_prod' : ''),
            ];

            return $this->render('new-product.tpl', [
                        'group_product' => $groupProduct,
            ]);
        }
    }

}
