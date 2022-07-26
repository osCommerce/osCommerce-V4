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
 * Controller to handle user requests.
 */
class TaxZonesController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TAXES', 'BOX_TAXES_GEO_ZONES'];
    
    public function actionIndex() {
        \common\helpers\Translation::init('admin/geo_zones');

        $this->selectedMenu = array('settings', 'taxes', 'tax-zones');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('tax-zones/index'), 'title' => BOX_TAXES_GEO_ZONES);
        $this->topButtons[] = '<a href="#" id="add_cat" class="btn btn-primary" onclick="return editCategory(0)">'.IMAGE_INSERT.'</a>';
        $this->topButtons[] = '<a href="#" id="add_prop" class="btn btn-primary" onclick="return editProduct(0)" style="display: none;">'.IMAGE_INSERT.'</a>';

        $this->view->headingTitle = HEADING_TITLE;
        $this->view->catalogTable = array(
            array(
                'title' => TABLE_HEADING_TAX_ZONES,
                'not_important' => 0
            ),
        );
        $this->view->zoneTable = array(
            array(
                'title' => TABLE_HEADING_COUNTRY_NAME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_COUNTRY_ZONE,
                'not_important' => 0
            ),
        );
        return $this->render('index');
    }

    public function actionList() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        
        \common\helpers\Translation::init('admin/geo_zones');
        
        $current_category_id = Yii::$app->request->get('id', 0);
        
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        if( $length == -1 ) $length = 10000;
        
        $responseList = array();
        $zones_query_numrows = 0;

        if ($current_category_id > 0) {
            $responseList[] = [
                '<span class="parent_cats"><i class="icon-circle"></i><i class="icon-circle"></i><i class="icon-circle"></i></span><input class="cell_identify" type="hidden" value="' . 0 . '"><input class="cell_type" type="hidden" value="parent">',
            ];
            
            $search = '';
            if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
                $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
                $search = " and (c.countries_name like '%" . $keywords . "%' or z.zone_name like '%" . $keywords . "%')";
            }
            
            $zones_query_raw = "select a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.geo_zone_id, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_TAX_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.geo_zone_id = " . $current_category_id . " $search order by association_id";
            $current_page_number = ($start / $length) + 1;
            $zones_split = new \splitPageResults($current_page_number, $length, $zones_query_raw, $zones_query_numrows, 'a.association_id');
            $zones_query = tep_db_query($zones_query_raw);
            while ($zones = tep_db_fetch_array($zones_query)) {
                $responseList[] = array(
                    (($zones['countries_name']) ? $zones['countries_name'] : TEXT_ALL_COUNTRIES) . '&nbsp;/&nbsp;' . 
                    (($zones['zone_id']) ? $zones['zone_name'] : PLEASE_SELECT) .
                    '<input class="cell_identify" type="hidden" value="' . $zones['association_id'] . '"><input class="cell_type" type="hidden" value="product">'
                );
            }
        } else {
            $search = '1';
            if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
                $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
                $search = " (geo_zone_name like '%" . $keywords . "%' or geo_zone_description like '%" . $keywords . "%')";
            }
            
            $zones_query_raw = "select geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added from " . TABLE_TAX_ZONES . " where $search order by geo_zone_name";
            $current_page_number = ($start / $length) + 1;
            $zones_split = new \splitPageResults($current_page_number, $length, $zones_query_raw, $zones_query_numrows, 'geo_zone_id');
            $zones_query = tep_db_query($zones_query_raw);
            while ($zones = tep_db_fetch_array($zones_query)) {
                $responseList[] = array(
                    '<div class="cat_name cat_name_attr">'.$zones['geo_zone_name'] . '<input class="cell_identify" type="hidden" value="' . $zones['geo_zone_id'] . '"><input class="cell_type" type="hidden" value="category"></div>',
                );
            }
        }
        
        $response = array(
            'draw' => $draw,
            'recordsTotal' => $zones_query_numrows,
            'recordsFiltered' => $zones_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }
    
    /*public function actionContent() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/geo_zones');
        
        $current_category_id = Yii::$app->request->get('id', 0);
        
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        if( $length == -1 ) $length = 10000;
        
        $responseList = array();
        $zones_query_numrows = 0;
        
        $zones_query_raw = "select a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.geo_zone_id, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_TAX_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.geo_zone_id = " . $current_category_id . " order by association_id";
        $current_page_number = ($start / $length) + 1;
        $zones_split = new \splitPageResults($current_page_number, $length, $zones_query_raw, $zones_query_numrows, 'a.association_id');
        $zones_query = tep_db_query($zones_query_raw);
        while ($zones = tep_db_fetch_array($zones_query)) {
            $responseList[] = array(
                (($zones['countries_name']) ? $zones['countries_name'] : TEXT_ALL_COUNTRIES),
                (($zones['zone_id']) ? $zones['zone_name'] : PLEASE_SELECT),
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $zones_query_numrows,
            'recordsFiltered' => $zones_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }*/

    public function actionCategoryactions() {

        \common\helpers\Translation::init('admin/geo_zones');

        $this->layout = false;

        $categories_id = Yii::$app->request->post('categories_id');
        
        $zones_query = tep_db_query("select geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added from " . TABLE_TAX_ZONES . " where geo_zone_id='" . (int)$categories_id . "'");
        $zones = tep_db_fetch_array($zones_query);
        
        $num_zones_query = tep_db_query("select count(*) as num_zones from " . TABLE_ZONES_TO_TAX_ZONES . " where geo_zone_id = '" . (int)$zones['geo_zone_id'] . "' group by geo_zone_id");
        $num_zones = tep_db_fetch_array($num_zones_query);

        if (($num_zones['num_zones'] ?? null) > 0) {
          $zones['num_zones'] = $num_zones['num_zones'];
        } else {
          $zones['num_zones'] = 0;
        }
        $zInfo = new \objectInfo($zones);
        $zInfo->geo_zone_id = $zInfo->geo_zone_id ?? null;
        $zInfo->last_modified = $zInfo->last_modified ?? null;
        
				echo '<div class="or_box_head">' . $zInfo->geo_zone_name ?? null . '</div>';
				echo '<div class="row_or_wrapp">';
				echo '<div class="row_or"><div>' . TEXT_INFO_NUMBER_ZONES . '</div><div>' . ($zInfo->num_zones ?? null) . '</div></div>';
				echo '<div class="row_or"><div>' . TEXT_INFO_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($zInfo->date_added ?? null) . '</div></div>';
				echo '<div class="row_or"><div>' . TEXT_INFO_ZONE_DESCRIPTION . '</div><div>' . ($zInfo->geo_zone_description ?? null) . '</div></div>';
				if (tep_not_null($zInfo->last_modified)) echo '<div class="row_or"><div>' . TEXT_INFO_LAST_MODIFIED . '</div><div>' . \common\helpers\Date::date_short($zInfo->last_modified) . '</div></div>';
				echo '</div>';
				echo '<div class="btn-toolbar btn-toolbar-order">';
                                echo '<button onclick="return editCategory('.$zInfo->geo_zone_id.')" class="btn btn-no-margin btn-primary btn-edit">Edit</button>';
                                echo '<button onclick="return confirmDeleteCategory('.$zInfo->geo_zone_id.')" class="btn btn-delete">Delete</button>';
				//echo '<a class="btn btn-primary btn-process-order btn-edit" href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=edit_zone') . '">' . IMAGE_EDIT . '</a><a class="btn btn-delete btn-no-margin" href="' . tep_href_link(FILENAME_GEO_ZONES, 'zpage=' . $_GET['zpage'] . '&zID=' . $zInfo->geo_zone_id . '&action=delete_zone') . '">' . IMAGE_DELETE . '</a>';
				echo '</div>';
    }
    
    public function actionConfirmcategorydelete() {
        \common\helpers\Translation::init('admin/geo_zones');
        
        $this->layout = false;

        $categories_id = Yii::$app->request->post('category_id');
        
        $zones_query = tep_db_query("select geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added from " . TABLE_TAX_ZONES . " where geo_zone_id='" . (int)$categories_id . "'");
        $zones = tep_db_fetch_array($zones_query);
        
        $num_zones_query = tep_db_query("select count(*) as num_zones from " . TABLE_ZONES_TO_TAX_ZONES . " where geo_zone_id = '" . (int)$zones['geo_zone_id'] . "' group by geo_zone_id");
        $num_zones = tep_db_fetch_array($num_zones_query);

        if ($num_zones['num_zones'] > 0) {
          $zones['num_zones'] = $num_zones['num_zones'];
        } else {
          $zones['num_zones'] = 0;
        }
        $zInfo = new \objectInfo($zones);
        
        echo '<div class="or_box_head">'.TEXT_INFO_HEADING_DELETE_ZONE.'</div>';
        echo tep_draw_form('zones', 'geo_zones', 'zpage=' . (int)\Yii::$app->request->get('zpage') . '&zID=' . ($zInfo->geo_zone_id ?? null) . '&action=deleteconfirm_zone', 'post', 'id="option_delete" onsubmit="return deleteCategory();"');
        echo '<div class="col_desc">' . TEXT_INFO_DELETE_ZONE_INTRO . '</div>';
        echo '<div class="col_desc">' . ($zInfo->geo_zone_name ?? null) . '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">' . tep_draw_hidden_field( 'category_id', $categories_id ) . '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()"></div>';
        echo '</form>';
    }
    
    public function actionCategorydelete() {
        $zID = (int)Yii::$app->request->post('category_id');
        tep_db_query("delete from " . TABLE_TAX_ZONES . " where geo_zone_id = '" . (int)$zID . "'");
        tep_db_query("delete from " . TABLE_ZONES_TO_TAX_ZONES . " where geo_zone_id = '" . (int)$zID . "'");
    }
    
    public function actionCategoryedit() {

        \common\helpers\Translation::init('admin/geo_zones');
        
        $this->layout = false;

        $categories_id = (int)Yii::$app->request->post('category_id');
        
        $zones_query = tep_db_query("select geo_zone_id, geo_zone_name, geo_zone_description, last_modified, date_added from " . TABLE_TAX_ZONES . " where geo_zone_id='" . (int)$categories_id . "'");
        $zones = tep_db_fetch_array($zones_query);
        
        $num_zones_query = tep_db_query("select count(*) as num_zones from " . TABLE_ZONES_TO_TAX_ZONES . " where geo_zone_id = '" . (int)($zones['geo_zone_id'] ?? null) . "' group by geo_zone_id");
        $num_zones = tep_db_fetch_array($num_zones_query);

        if (($num_zones['num_zones'] ?? null) > 0) {
          $zones['num_zones'] = $num_zones['num_zones'];
        } else {
          $zones['num_zones'] = 0;
        }
        $zInfo = new \objectInfo($zones);
        $zInfo->geo_zone_id = $zInfo->geo_zone_id ?? null;
        
        $heading = array();
        $contents = array();
        
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_ZONE . '</b>');

        echo tep_draw_form('zones', FILENAME_GEO_ZONES, 'zpage=' . \Yii::$app->request->get('zpage') . '&zID=' . $zInfo->geo_zone_id . '&action=save_zone', 'post', 'id="option_save" onsubmit="return checkCategoryForm();"');
        echo '<div class="or_box_head">'.TEXT_INFO_EDIT_ZONE_INTRO.'</div>';
        echo '<div class="row_or row_or_block"><label class="main">' . TEXT_INFO_ZONE_NAME . '</label>' . tep_draw_input_field('geo_zone_name', $zInfo->geo_zone_name ?? null, 'class="form-control"').'</div>';
        echo '<div class="row_or row_or_block"><label class="main">' . TEXT_INFO_ZONE_DESCRIPTION . '</label>' . tep_draw_input_field('geo_zone_description', $zInfo->geo_zone_description ?? null, 'class="form-control"').'</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">' . tep_draw_hidden_field( 'category_id', $categories_id ) . '<button class="btn btn-no-margin">' . IMAGE_UPDATE . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()"></div>';
        echo '</form>';
    }
    
    public function actionCategorysubmit() {
        $zID = (int)Yii::$app->request->post('category_id');
        $geo_zone_name = Yii::$app->request->post('geo_zone_name');
        $geo_zone_description = Yii::$app->request->post('geo_zone_description');
        
        if ($zID == 0) {
            tep_db_query("insert into " . TABLE_TAX_ZONES . " (geo_zone_name, geo_zone_description, date_added) values ('" . tep_db_input($geo_zone_name) . "', '" . tep_db_input($geo_zone_description) . "', now())");
        } else {
            tep_db_query("update " . TABLE_TAX_ZONES . " set geo_zone_name = '" . tep_db_input($geo_zone_name) . "', geo_zone_description = '" . tep_db_input($geo_zone_description) . "', last_modified = now() where geo_zone_id = '" . (int)$zID . "'");
        }
    }
    
    public function actionProductactions() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/geo_zones');

        $this->layout = false;

        $products_id = (int)Yii::$app->request->post('products_id');
        
        $zones_query = tep_db_query("select a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.geo_zone_id, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_TAX_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.association_id = " . $products_id);
        $zones = tep_db_fetch_array($zones_query);
        $sInfo = new \objectInfo($zones);
        
        echo '<div class="or_box_head">' . $sInfo->countries_name . '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order"><button onclick="return editProduct('.$products_id.')" class="btn btn-no-margin btn-primary btn-edit">Edit</button><button onclick="return confirmDeleteProduct('.$products_id.')" class="btn btn-delete">Delete</button></div>';
        echo '<br>' . TEXT_INFO_DATE_ADDED . ' ' . \common\helpers\Date::date_short($sInfo->date_added);
        if (tep_not_null($sInfo->last_modified)) echo TEXT_INFO_LAST_MODIFIED . ' ' . \common\helpers\Date::date_short($sInfo->last_modified);
        
    }
    
    public function actionConfirmproductdelete() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/geo_zones');

        $this->layout = false;

        $products_id = (int)Yii::$app->request->post('products_id');
        
        $zones_query = tep_db_query("select a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.geo_zone_id, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_TAX_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.association_id = " . $products_id);
        $zones = tep_db_fetch_array($zones_query);
        $sInfo = new \objectInfo($zones);
        
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_SUB_ZONE . '</div>';
        echo tep_draw_form('zones', FILENAME_GEO_ZONES, \common\helpers\Output::get_all_get_params( array( 'action' ) ), 'post', 'id="option_delete" onsubmit="return deleteProduct();"');
        echo '<div class="col_desc">' . TEXT_INFO_DELETE_SUB_ZONE_INTRO . '</div>';
        echo '<div class="col_desc">' . $sInfo->countries_name . '</div>';
        echo '<br>' . tep_draw_hidden_field( 'association_id', $sInfo->association_id ) . '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
        echo '</form>';
    }

    public function actionProductdelete() {
        $sID = (int)Yii::$app->request->post('association_id');
        tep_db_query("delete from " . TABLE_ZONES_TO_TAX_ZONES . " where association_id = '" . $sID . "'");
    }
    
    public function actionProductedit() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/geo_zones');

        $this->layout = false;

        $products_id = (int)Yii::$app->request->post('products_id');
        $zID = (int)Yii::$app->request->post('geo_zone_id');
        
        $zones_query = tep_db_query("select a.association_id, a.zone_country_id, c.countries_name, a.zone_id, a.geo_zone_id, a.last_modified, a.date_added, z.zone_name from " . TABLE_ZONES_TO_TAX_ZONES . " a left join " . TABLE_COUNTRIES . " c on a.zone_country_id = c.countries_id and c.language_id = '" . (int)$languages_id . "' left join " . TABLE_ZONES . " z on a.zone_id = z.zone_id where a.association_id = " . $products_id);
        $zones = tep_db_fetch_array($zones_query);
        $sInfo = new \objectInfo($zones);
        $sInfo->geo_zone_id = $sInfo->geo_zone_id ?? null;
        $sInfo->zone_country_id = $sInfo->zone_country_id ?? null;
        
        if ($sInfo->geo_zone_id <= 0) {
            $sInfo->geo_zone_id = $zID;
        }
        
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_SUB_ZONE . '</div>';
        echo '<div class="col_desc">' . ($sInfo->countries_name ?? null). '</div>';
        echo tep_draw_form('zones', FILENAME_GEO_ZONES, \common\helpers\Output::get_all_get_params( array( 'action' ) ), 'post', 'id="option_save" onsubmit="return checkProductForm();"' );
        echo '<div class="col_desc">' . TEXT_INFO_EDIT_SUB_ZONE_INTRO . '</div>';
        echo '<div class="row_or row_or_block"><label>' . TEXT_INFO_COUNTRY . '</label>' . tep_draw_pull_down_menu('zone_country_id', \common\helpers\Country::get_countries('', false, TEXT_ALL_COUNTRIES), $sInfo->zone_country_id, 'onChange="update_zone(this.form);" class="form-control"').'</div>';
        echo '<div class="row_or row_or_block"><label>' . TEXT_INFO_COUNTRY_ZONE . '</label>' . tep_draw_pull_down_menu('zone_id', \common\helpers\Zones::prepare_country_zones_pull_down($sInfo->zone_country_id), $sInfo->zone_id ?? null, 'class="form-control"').'</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">' . tep_draw_hidden_field( 'association_id', $sInfo->association_id ?? null) . tep_draw_hidden_field( 'geo_zone_id', $sInfo->geo_zone_id ) . tep_draw_hidden_field( 'association_id', $sInfo->association_id ?? null) . '<button class="btn btn-no-margin">' . IMAGE_UPDATE . '</button><input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()"></div>';
        echo '</form>';
    }
    
    public function actionProductsubmit() {
        $sID = (int)Yii::$app->request->post('association_id');
        
        $zID = (int)Yii::$app->request->post('geo_zone_id');
        $zone_country_id = (int)Yii::$app->request->post('zone_country_id');
        $zone_id = (int)Yii::$app->request->post('zone_id');
        if ($sID == 0) {
			//check 
			$check = tep_db_fetch_array(tep_db_query("select association_id from " . TABLE_ZONES_TO_TAX_ZONES . " where geo_zone_id = '" . (int)$zID . "' and zone_country_id = '" . (int)$zone_country_id . "' and zone_id = '" . (int)$zone_id . "'"));
			if (!$check)
				tep_db_query("insert into " . TABLE_ZONES_TO_TAX_ZONES . " (zone_country_id, zone_id, geo_zone_id, date_added) values ('" . (int)$zone_country_id . "', '" . (int)$zone_id . "', '" . (int)$zID . "', now())");
        } else {
            tep_db_query("update " . TABLE_ZONES_TO_TAX_ZONES . " set geo_zone_id = '" . (int)$zID . "', zone_country_id = '" . (int)$zone_country_id . "', zone_id = " . (tep_not_null($zone_id) ? "'" . (int)$zone_id . "'" : 'null') . ", last_modified = now() where association_id = '" . (int)$sID . "'");
        }
    }

}
