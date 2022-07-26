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

use common\helpers\Translation;
use Yii;

/**
 * default controller to handle user requests.
 */
class CitiesController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_LOCATION', 'BOX_CITIES'];

    public function actionIndex() {
        Translation::init('admin/geo_zones');
        $this->selectedMenu = array('settings', 'locations', 'cities');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('cities/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="btn btn-primary" onclick="return cityEdit(0)">' . TEXT_INFO_HEADING_NEW_CITY . '</a>';

        $this->view->citiesTable = array(
            array(
                'title' => TABLE_HEADING_COUNTRY_NAME,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_CITY_NAME,
                'not_important' => 0,
            ),
                /* array(
                  'title' => TABLE_HEADING_CITY_CODE,
                  'not_important' => 0,
                  ), */
            array(
                'title' => TABLE_HEADING_ZONE_NAME,
                'not_important' => 0,
            ),
        );

        return $this->render('index');
    }

    public function actionList() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cID = Yii::$app->request->get('cID', 0);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_prepare_input($_GET['search']['value']);
            $search = " and (c.countries_name like '%" . tep_db_input($keywords) . "%' or c.countries_iso_code_2 like '%" . tep_db_input($keywords) . "%' or c.countries_iso_code_3 like '%" . tep_db_input($keywords) . "%' or z.city_name like '%" . tep_db_input($keywords) . "%' or zz.zone_name like '%" . tep_db_input($keywords) . "%')";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "c.countries_name " . tep_db_prepare_input($_GET['order'][0]['dir']).', z.city_name';
                    break;
                case 1:
                    $orderBy = "z.city_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                case 2:
                    $orderBy = "IFNULL(zz.zone_name,'') " . tep_db_prepare_input($_GET['order'][0]['dir']).', c.countries_name, z.city_name';
                    break;
                default:
                    $orderBy = "c.countries_name, z.city_name";
                    break;
            }
        } else {
            $orderBy = "c.countries_name, z.city_name";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();

        $cities_query_raw =
            "select z.city_id, c.countries_id, c.countries_name, z.city_name, z.city_code, z.city_country_id, zz.zone_name ".
            "from " . TABLE_COUNTRIES . " c, " . TABLE_CITIES . " z ".
            " left join ".TABLE_ZONES." zz ON zz.zone_id=z.city_zone_id and zz.zone_country_id=z.city_country_id ".
            "where z.city_country_id = c.countries_id and c.language_id = '" . $languages_id . "' " . $search . " ".
            "order by " . $orderBy;
        $cities_split = new \splitPageResults($current_page_number, $length, $cities_query_raw, $cities_query_numrows);
        $cities_query = tep_db_query($cities_query_raw);

        while ($cities = tep_db_fetch_array($cities_query)) {

            $responseList[] = array(
                $cities['countries_name'] . tep_draw_hidden_field('id', $cities['city_id'], 'class="cell_identify"'),
                $cities['city_name'],
                    //$cities['city_code']
                $cities['zone_name'],
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $cities_query_numrows,
            'recordsFiltered' => $cities_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionCitiesactions() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        Translation::init('admin/cities');
        Translation::init('admin/zones');
        Translation::init('admin/geo_zones');

        $cities_id = Yii::$app->request->post('cities_id', 0);
        $this->layout = false;
        if ($cities_id) {
            $city = tep_db_fetch_array(tep_db_query(
                "select z.city_id, c.countries_id, c.countries_name, z.city_name, z.city_code, z.city_country_id, zz.zone_name ".
                "from " . TABLE_COUNTRIES . " c, " . TABLE_CITIES . " z ".
                " left join ".TABLE_ZONES." zz ON zz.zone_id=z.city_zone_id and zz.zone_country_id=z.city_country_id ".
                "where z.city_country_id = c.countries_id and c.language_id = '" . $languages_id . "' and z.city_id = '" . (int) $cities_id . "'"
            ));
            $cInfo = new \objectInfo($city, false);
            echo '<div class="or_box_head">' . $cInfo->city_name . '</div>';
            echo '<div class="row_or_wrapp">';
            echo '<div class="row_or"><div>' . TEXT_INFO_CITIES_NAME . '</div><div>' . $cInfo->city_name . ' </div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_ZONE_NAME . '</div><div>' . $cInfo->zone_name . ' </div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_COUNTRY_NAME . '</div><div>' . $cInfo->countries_name . '</div></div>';
            echo '</div>';
            echo '<div class="btn-toolbar btn-toolbar-order"><button class="btn btn-edit btn-no-margin" onclick="cityEdit(' . $cities_id . ')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="cityDelete(' . $cities_id . ')">' . IMAGE_DELETE . '</button></div>';
        }
    }

    public function actionEdit() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        Translation::init('admin/cities');
        Translation::init('admin/zones');
        Translation::init('admin/geo_zones');

        $cities_id = Yii::$app->request->get('cities_id', 0);
        $city = tep_db_fetch_array(tep_db_query(
            "select z.city_id, c.countries_id, c.countries_name, z.city_name, z.city_code, z.city_country_id, z.city_zone_id, zz.zone_id ".
            "from " . TABLE_COUNTRIES . " c, " . TABLE_CITIES . " z ".
            " left join ".TABLE_ZONES." zz ON zz.zone_id=z.city_zone_id and zz.zone_country_id=z.city_country_id ".
            "where z.city_country_id = c.countries_id and c.language_id = '" . $languages_id . "' and z.city_id = '" . (int) $cities_id . "'"
        ));
        $cInfo = new \objectInfo($city, false);
        $cInfo->city_id = $cInfo->city_id ?? null;
        $cInfo->countries_id = $cInfo->countries_id ?? null;

        echo tep_draw_form('cities', 'cities', 'page=' . \Yii::$app->request->get('page') . '&cID=' . $cInfo->city_id . '&action=save');
        if ($cities_id) {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_CITY . '</div>';
        } else {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_CITY . '</div>';
        }
        echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';
        echo '<div class="main_row">';
        echo '<div class="main_title">' . TEXT_INFO_CITIES_NAME . '</div>';
        echo '<div class="main_value">' . tep_draw_input_field('city_name', $cInfo->city_name ?? null) . '</div>';
        echo '</div>';
        //echo '<div class="main_row">';
        //echo '<div class="main_title">' . TEXT_INFO_CITIES_CODE . '</div>';
        //echo '<div class="main_value">' . tep_draw_input_field('city_code', $cInfo->city_code) . '</div>';
        //echo '</div>';
        echo '<div class="main_row">';
        echo '<div class="main_title">' . TEXT_INFO_ZONES_NAME . '</div>';
        echo '<div class="main_value">' . \common\helpers\Html::dropDownList('city_zone_id', $cInfo->zone_id ?? null, \yii\helpers\ArrayHelper::map(\common\helpers\Zones::prepare_country_zones_pull_down($cInfo->countries_id),'id','text')) . '</div>';
        echo '</div>';
        echo '<div class="main_row">';
        echo '<div class="main_title">' . TEXT_INFO_COUNTRY_NAME . '</div>';
        echo '<div class="main_value">' . \common\helpers\Html::dropDownList('city_country_id', $cInfo->countries_id, \common\helpers\Country::new_get_countries('',true), ['onchange'=>'update_zone(this.form)']) . '</div>';
        echo '</div>';

        echo '<div class="btn-toolbar btn-toolbar-order"><input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="citySave(' . ($cInfo->city_id ? $cInfo->city_id : 0) . ')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()"></div>';
        echo '</form>';
    }

    public function actionSave() {
        global $language;
        Translation::init('admin/cities');

        $cities_id = Yii::$app->request->get('cities_id', 0);

        $city_country_id = tep_db_prepare_input(\Yii::$app->request->post('city_country_id'));
        $city_zone_id = tep_db_prepare_input(\Yii::$app->request->post('city_zone_id'));
        $city_code = tep_db_prepare_input(\Yii::$app->request->post('city_code'));
        $city_name = tep_db_prepare_input(\Yii::$app->request->post('city_name'));

        if ($cities_id == 0) {
            $cityModel = new \common\models\Cities();
            $cityModel->setAttributes([
                'city_country_id' => (int) $city_country_id,
                'city_zone_id' => (int)$city_zone_id,
                'city_code' => (string)$city_code,
                'city_name' => (string)$city_name,
            ],false);
            $cityModel->save(false);
            $action = 'added';
        } else {
            $cityModel = \common\models\Cities::find()->where(['city_id' => (int)$cities_id])->one();
            if ( $cityModel ){
                $cityModel->setAttributes([
                    'city_country_id' => (int) $city_country_id,
                    'city_zone_id' => (int)$city_zone_id,
                    'city_code' => (string)$city_code,
                    'city_name' => (string)$city_name,
                ],false);
                $cityModel->save(false);
            }
            $action = 'updated';
        }


        echo json_encode(array('message' => 'City ' . $action, 'messageType' => 'alert-success'));
    }

    public function actionDelete() {
        global $language;
        Translation::init('admin/cities');
        $cities_id = Yii::$app->request->post('cities_id', 0);

        if ($cities_id)
            tep_db_query("delete from " . TABLE_CITIES . " where city_id = '" . (int) $cities_id . "'");

        echo 'reset';
    }

}
