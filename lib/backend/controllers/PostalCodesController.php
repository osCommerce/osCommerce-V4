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

use backend\components\LocationSearchTrait;
use common\helpers\Translation;
use common\models\PostalCodes;
use yii;
use yii\helpers\Url;

class PostalCodesController extends Sceleton
{
    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_LOCATION', 'BOX_POSTAL_CODES'];

    use LocationSearchTrait;

    public function actionIndex() {
        Translation::init('admin/postal-codes');
        Translation::init('admin/geo_zones');
        $this->selectedMenu = array('settings', 'locations', 'postal-codes');
        $this->navigation[] = array('link' => Url::toRoute('index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="#" class="btn btn-primary" onclick="return entryEdit(0)">' . TEXT_NEW . '</a>';

        $this->view->columnTable = array(
            array(
                'title' => ENTRY_POST_CODE,
                'not_important' => 0,
            ),
            array(
                'title' => ENTRY_SUBURB,
                'not_important' => 0,
            ),
            array(
                'title' => ENTRY_CITY,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_COUNTRY_NAME,
                'not_important' => 0,
            ),
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

        $search_words = '';
        if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
            $search_words = tep_db_prepare_input($_GET['search']['value']);
        }
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        if ($length == -1)
            $length = 10000;

        $query_raw =
            \common\models\PostalCodes::find()
                ->alias('p')
                ->join('left join', \common\models\Countries::tableName().' c',"c.countries_id=p.country_id AND c.language_id='".(int)$languages_id."'")
                ->join('left join', \common\models\Cities::tableName().' t',"t.city_id=p.city_id")
                ->join('left join', \common\models\Zones::tableName().' z',"z.zone_id=p.zone_id")
                ->select(['p.id', 'p.postcode', 'p.suburb', 'c.countries_name', 't.city_name', 'z.zone_name']);
        if ( $search_words ){
            $query_raw->andWhere(['or',['like','p.postcode',$search_words],['like','p.suburb',$search_words],['like','z.zone_name',$search_words],]);
        }

        $query_raw->orderBy(['p.postcode'=>SORT_ASC]);

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            $sort_dir = strtolower($_GET['order'][0]['dir'])=='desc'?SORT_DESC:SORT_ASC;
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $query_raw->orderBy(['p.postcode'=>$sort_dir]);
                    break;
                case 1:
                    $query_raw->orderBy(['p.suburb'=>$sort_dir]);
                    break;
                case 2:
                    $query_raw->orderBy(['t.city_name'=>$sort_dir]);
                    break;
            }
        }

        $total = $query_raw->count();
        $query_raw->limit($length)->offset($start);

        $responseList = array();
        foreach ($query_raw->asArray()->all() as $dbData){
            $responseList[] = [
                $dbData['postcode'].'<input type="hidden" class="cell_identify" value="'.$dbData['id'].'">',
                $dbData['suburb'],
                (string)$dbData['city_name'],
                (string)$dbData['countries_name'],
                (string)$dbData['zone_name'],
                //'<input type="checkbox" class="uniform">' . '<input class="cell_identify" type="hidden" value="' . $dbData['id'] . '"><input class="cell_type" type="hidden" value="item">',
                //$dbData['title'].'<div class="post_labels">'.$post_labels.'</div>',
                //\common\helpers\Html::checkbox('status',$dbData['status'], ['value'=>$dbData['id'], 'class'=>'status_on_off',]),
            ];

        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $responseList,
        );
        echo json_encode($response);
    }

    public function actionActions() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        Translation::init('admin/cities');
        Translation::init('admin/zones');
        Translation::init('admin/geo_zones');

        $item_id = Yii::$app->request->post('item_id', 0);
        $this->layout = false;
        if ($item_id) {
            $postal = \common\models\PostalCodes::find()->where(['id'=>$item_id])->asArray()->one();
            $postal['countries_name'] = \common\helpers\Country::get_country_name($postal['country_id']);
            $postal['zone_name'] = \common\helpers\Zones::get_zone_name($postal['country_id'], $postal['zone_id'], '');
            $postal['city_name'] = \common\models\Cities::find()->where(['city_id'=>$postal['city_id']])->select('city_name')->scalar();

            $cInfo = new \objectInfo($postal, false);
            echo '<div class="or_box_head">' . $cInfo->city_name . '</div>';
            echo '<div class="row_or_wrapp">';
            echo '<div class="row_or"><div>' . ENTRY_POST_CODE . ':</div><div>' . $cInfo->postcode . ' </div></div>';
            echo '<div class="row_or"><div>' . ENTRY_SUBURB . ':</div><div>' . $cInfo->suburb . ' </div></div>';
            echo '<div class="row_or"><div>' . ENTRY_CITY . ':</div><div>' . $cInfo->city_name . ' </div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_ZONE_NAME . '</div><div>' . $cInfo->zone_name . ' </div></div>';
            echo '<div class="row_or"><div>' . TEXT_INFO_COUNTRY_NAME . '</div><div>' . $cInfo->countries_name . '</div></div>';
            echo '</div>';
            echo '<div class="btn-toolbar btn-toolbar-order"><button class="btn btn-edit btn-no-margin" onclick="entryEdit(' . $postal['id'] . ')">' . IMAGE_EDIT . '</button><button class="btn btn-delete" onclick="entryDelete(' . $postal['id'] . ')">' . IMAGE_DELETE . '</button></div>';
        }
    }

    public function actionEdit() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        Translation::init('admin/postal-codes');
        Translation::init('admin/zones');
        Translation::init('admin/geo_zones');

        $item_id = Yii::$app->request->get('item_id', 0);

        $cInfo = false;
        if ( $item_id ){
            $cInfo = PostalCodes::findOne($item_id);
        }
        if ( !$cInfo ){
            $cInfo = new PostalCodes();
            $cInfo->loadDefaultValues();
        }

        echo tep_draw_form('cities', 'save', 'item_id=' . $cInfo->id . '&action=save');
        if ($cInfo->id) {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_EDIT_POSTCODE . '</div>';
        } else {
            echo '<div class="or_box_head">' . TEXT_INFO_HEADING_NEW_POSTCODE . '</div>';
        }
        echo '<div class="col_desc">' . TEXT_INFO_EDIT_INTRO . '</div>';

        echo '<div class="main_row">';
        echo '<div class="main_title">' . ENTRY_POST_CODE . '</div>';
        echo '<div class="main_value">' . tep_draw_input_field('postcode', $cInfo->postcode) . '</div>';
        echo '</div>';
        echo '<div class="main_row">';
        echo '<div class="main_title">' . ENTRY_SUBURB . '</div>';
        echo '<div class="main_value">' . tep_draw_input_field('suburb', $cInfo->suburb) . '</div>';
        echo '</div>';


        echo '<div class="main_row">';
        echo '<div class="main_title">' . ENTRY_CITY . '</div>';
        echo '<div class="main_value">' .
            tep_draw_hidden_field('city_id', $cInfo->city_id).
            tep_draw_input_field('city_name', \common\models\Cities::find()->where(['city_id'=>$cInfo->city_id])->select('city_name')->scalar()).
            '<div id="acCityName" style="font-size: 12px"></div>'.
            '</div>';
        echo '</div>';

        echo '<div class="main_row">';
        echo '<div class="main_title">' . TEXT_INFO_COUNTRY_NAME . '</div>';
        echo '<div class="main_value">' . \common\helpers\Html::dropDownList('country_id', $cInfo->country_id, \common\helpers\Country::new_get_countries('--',true), ['onchange'=>'update_zone(this.form)']) . '</div>';
        echo '</div>';
        echo '<div class="main_row">';
        echo '<div class="main_title">' . TEXT_INFO_ZONES_NAME . '</div>';
        echo '<div class="main_value">' . \common\helpers\Html::dropDownList('zone_id', $cInfo->zone_id, \yii\helpers\ArrayHelper::map(\common\helpers\Zones::prepare_country_zones_pull_down($cInfo->country_id),'id','text')) . '</div>';
        echo '</div>';

        echo '<div class="btn-toolbar btn-toolbar-order"><input type="button" value="' . IMAGE_UPDATE . '" class="btn btn-no-margin" onclick="entrySave(' . ($cInfo->id ? $cInfo->id : 0) . ')"><input type="button" value="' . IMAGE_CANCEL . '" class="btn btn-cancel" onclick="resetStatement()"></div>';
        echo '</form>';
    }

    public function actionSave() {
        Translation::init('admin/postal-codes');

        $item_id = Yii::$app->request->get('item_id', 0);

        $postcode = tep_db_prepare_input($_POST['postcode']);
        $suburb = tep_db_prepare_input($_POST['suburb']);
        $country_id = tep_db_prepare_input($_POST['country_id']);
        $zone_id = tep_db_prepare_input($_POST['zone_id']);
        $city_id = tep_db_prepare_input($_POST['city_id']);
        $city_name = tep_db_prepare_input($_POST['city_name']);
        if ($city_name){
            $city_info = \common\models\Cities::find()
                ->where(['city_name'=>$city_name])
                ->andFilterWhere(['city_country_id'=>empty($country_id)?null:$country_id])
                ->andFilterWhere(['city_zone_id'=>empty($zone_id)?null:$zone_id])
                ->select(['city_id', 'city_zone_id', 'city_country_id'])
                ->asArray()->one();
            if ( $city_info ){
                $city_id = $city_info['city_id'];
                if (empty($zone_id)){
                    $zone_id = $city_info['city_zone_id'];
                }
            }else{
                $newCityModel = new \common\models\Cities();
                $newCityModel->setAttributes([
                    'city_country_id' => $country_id,
                    'city_zone_id' => $zone_id,
                    'city_code' => '',
                    'city_name' => $city_name,
                ],false);
                $newCityModel->save(false);
                $city_id = $newCityModel->city_id;
            }
        }else{
            $city_id = 0;
        }

        if ($item_id == 0) {
            $itemModel = new PostalCodes();
            $itemModel->setAttributes([
                'country_id' => (int) $country_id,
                'zone_id' => (int)$zone_id,
                'city_id' => (int)$city_id,
                'suburb' => (string)$suburb,
                'postcode' => (string)$postcode,
            ],false);
            $itemModel->save(false);
            $action = 'added';
        } else {
            $itemModel = PostalCodes::findOne((int)$item_id);
            if ( $itemModel ){
                $itemModel->setAttributes([
                    'country_id' => (int) $country_id,
                    'zone_id' => (int)$zone_id,
                    'city_id' => (int)$city_id,
                    'suburb' => (string)$suburb,
                    'postcode' => (string)$postcode,
                ],false);
                $itemModel->save(false);
            }
            $action = 'updated';
        }


        echo json_encode(array('message' => 'Postcode ' . $action, 'messageType' => 'alert-success'));
    }

    public function actionDelete() {
        $item_id = Yii::$app->request->post('item_id', 0);

        if ($item_id) {
            if ($itemModel = PostalCodes::findOne($item_id)){
                $itemModel->delete();
            }
        }

        echo 'reset';
    }

}