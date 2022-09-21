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
use yii\helpers\Url;

/**
 * default controller to handle user requests.
 */
class LanguagesController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_LOCALIZATION', 'BOX_LOCALIZATION_LANGUAGES'];

    public function __construct($id, $module = null) {
        \common\helpers\Translation::init('admin/languages');
        parent::__construct($id, $module);
    }

    public function actionIndex() {
        global $language;

        $this->selectedMenu = array('settings', 'localization', 'languages');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('currencies/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;
        $this->topButtons[] = '<a href="' . Url::to('languages/predefine') . '" class="btn btn-primary new-language">' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</a>';

        $this->view->languagesTable = array(
            array(
                'title' => TABLE_HEADING_LANGUAGE_NAME,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_LANGUAGE_CODE,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_LANGUAGE_STATUS,
                'not_important' => 0,
            ),
        );


        if (!is_dir(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/')) {
            @chmod(DIR_FS_CATALOG . DIR_WS_IMAGES, 0777);
            @mkdir(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/', 0777, true);
        }
        $messages = Yii::$app->session->getAllFlashes();
        Yii::$app->session->removeAllFlashes();


        return $this->render('index', array('messages' => $messages, 'row' => Yii::$app->request->get('row', 0)));
        unset($_SESSION['messages']);
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cID = Yii::$app->request->get('cID', 0);

        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search = " and (name like '%" . $keywords . "%' or code like '%" . $keywords . "%')";
        }

        $current_page_number = ($start / $length) + 1;
        $responseList = array();

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = " sort_order, name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 1:
                    $orderBy = "code " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "sort_order, name";
                    break;
            }
        } else {
            $orderBy = " sort_order, name";
        }

        $languages_query_raw = "select languages_id, name, code, image, directory, sort_order, languages_status, image_svg from " . TABLE_LANGUAGES . " where 1 " . $search . " order by " . $orderBy; //echo $languages_query_raw;
        $languages_split = new \splitPageResults($current_page_number, $length, $languages_query_raw, $languages_query_numrows);
        $languages_query = tep_db_query($languages_query_raw);
        while ($languages = tep_db_fetch_array($languages_query)) {

            $responseList[] = array(
                '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr">' . \yii\helpers\Html::img(DIR_WS_CATALOG . DIR_WS_ICONS . $languages['image_svg'], ['width' => 24, 'height' => 16]) . '&nbsp;' .
                (strtolower(DEFAULT_LANGUAGE) == strtolower($languages['code']) ? '<b>' . $languages['name'] . ' (' . TEXT_DEFAULT . ')</b>' : $languages['name']) . tep_draw_hidden_field('id', $languages['languages_id'], 'class="cell_identify"') . '<input class="cell_type" type="hidden" value="lang" >' . '</div></div>',
                strtoupper($languages['code']),
                ('<input type="checkbox" value="' . $languages['languages_status'] . '" data-langid = "' . $languages['languages_id'] . '" name="languages_status" class="check_on_off"' . ($languages['languages_status'] == 1 ? ' checked="checked"' : '') . '>')
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $languages_query_numrows,
            'recordsFiltered' => $languages_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionLanguageActions() {
        global $language;

        $languages_id = (int) \Yii::$app->request->post('languages_id');
        $row = (int) \Yii::$app->request->post('row', 0);
        $this->layout = false;
        if ($languages_id) {
            $lang = tep_db_fetch_array(tep_db_query("select languages_id, name, code, image_svg as image, directory, sort_order, languages_status, locale, image_svg from " . TABLE_LANGUAGES . " where languages_id ='" . (int) $languages_id . "'"));
            //	$lInfo = $this->getAdminObj($languages_id);
            $heading = array();
            $contents = array();

            if (tep_not_null($lang)) {
                echo '<div class="or_box_head">' . $lang['name'] . '</div>';
                echo '<div class="row_center">' . tep_image(DIR_WS_CATALOG_IMAGES . 'icons/' . $lang['image'], $lang['name'], '24', '16', 'class="language-icon"') . '</div>';
                echo '<div class="row_or_wrapp">';
                echo '<div class="row_or"><div>' . TEXT_INFO_LANGUAGE_NAME . '</div><div>' . $lang['name'] . '</div></div>';
                echo '<div class="row_or"><div>' . TEXT_INFO_LANGUAGE_CODE . '</div><div>' . $lang['code'] . '</div></div>';
                //echo '<div class="row_or"><div>' . TEXT_INFO_LANGUAGE_SORT_ORDER . '</div><div>' . $lang['sort_order'] . '</div></div>';				
                echo '</div>';
                echo '<div class="btn-toolbar btn-toolbar-order"><a class="btn btn-edit btn-no-margin" href="' . Url::to(['edit', 'languages_id' => $languages_id, 'row' => $row]) . '">' . IMAGE_EDIT . '</a>' .
                '<button class="btn btn-delete" onclick="languageDelete(' . $languages_id . ')">' . IMAGE_DELETE . '</button></div>';
            }
        }
    }

    public function actionEdit() {
        $this->selectedMenu = array('settings', 'localization', 'languages');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('currencies/index'), 'title' => HEADING_TITLE);

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'.language_edit  form\').trigger(\'submit\')">' . IMAGE_UPDATE . '</span>';

        $languages_id = Yii::$app->request->get('languages_id', 0);
        //$lang = tep_db_fetch_array(tep_db_query("select languages_id, name, code, image, directory, sort_order, languages_status, image_svg, locale from " . TABLE_LANGUAGES . " where languages_id ='" . (int) $languages_id . "'"));
        $lang = \common\models\Languages::find()->andWhere(['languages_id' => (int) $languages_id])->asArray()->one();

        $languages = \common\helpers\Language::get_languages(true);
        $set = [];
        if (is_array($languages)) {
            foreach ($languages as $_k => $_v) {
                if ($_v['id'] == $languages_id) {
                    $set[] = $_v;
                }
            }
        }
        $languages = $set;
        unset($set);

        exec("locale -a", $output);

        $lList = [];
        if (is_array($output) && class_exists('\ResourceBundle')) {
            $all_locales = \ResourceBundle::getLocales('');
            $locale_ids = [];
            foreach ($output as $line) {
                if (tep_not_null($line)) {
                    $ex = explode(".", $line);
                    if (in_array($ex[0], $all_locales) && !in_array($ex[0], $locale_ids)) {
                        array_push($lList, ['id' => $ex[0], 'text' => $ex[0]]);
                        $locale_ids[] = $ex[0];
                    }
                }
            }
        } else if (class_exists('\Locale')) {
            $loc = \Locale::getDefault();
            $locale = \Locale::getPrimaryLanguage($loc) . '_' . \Locale::getRegion($loc);
            $lList[] = ['id' => $locale, 'text' => $locale];
        }
        if (count($lList) == 0) {
            $lList[] = ['id' => 'en_EN', 'text' => 'en_EN'];
        }

        $_formats = [];
        $formats_query = tep_db_query("select * from " . TABLE_LANGUAGES_FORMATS . " where 1");
        if (tep_db_num_rows($formats_query)) {
            while ($row = tep_db_fetch_array($formats_query)) {
                $_formats[] = $row;
            }
        }
        // check missing
        if (!empty($lang['locale'])) {
            $list_contain_locale = false;
            foreach ($lList as $lList_opt) {
                if ($lList_opt['id'] == $lang['locale']) {
                    $list_contain_locale = true;
                    break;
                }
            }
            if (!$list_contain_locale) {
                $lList[] = array('id' => $lang['locale'], 'text' => $lang['locale']);
            }
        }


        $messages = Yii::$app->session->getAllFlashes();
        Yii::$app->session->removeAllFlashes();

        return $this->render('edit.tpl', [
                    'lang' => $lang,
                    'lList' => $lList,
                    'languages_id' => $languages_id,
                    'defined_formats' => \yii\helpers\ArrayHelper::map($_formats, 'configuration_key', 'configuration_value', 'language_id'),
                    'languages' => $languages,
                    'messages' => $messages,
                    'row' => Yii::$app->request->get('row', 0),
        ]);
    }

    public function actionGetPredefined() {
        $responseList = [];

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);

        $search = '';
        $existed_query = tep_db_query("select code from " . TABLE_LANGUAGES . " where 1");
        if (tep_db_num_rows($existed_query)) {
            $existed = [];
            while ($row = tep_db_fetch_array($existed_query)) {
                $existed[] = $row['code'];
            }
            if (count($existed))
                $search = " and language_code not in ('" . (implode("','", array_map('tep_db_input',$existed))) . "') ";
        }


        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search .= " and (language_name like '%" . $keywords . "%' or language_code like '%" . $keywords . "%' or language_iso like '%" . $keywords . "%')";
        }

        $current_page_number = ($start / $length) + 1;

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 1:
                    $orderBy = "language_code " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 2:
                    $orderBy = "language_iso " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                case 3:
                    $orderBy = "language_name " . tep_db_input(tep_db_prepare_input($_GET['order'][0]['dir']));
                    break;
                default:
                    $orderBy = "language_code";
                    break;
            }
        } else {
            $orderBy = "language_code";
        }
        $languages_query_raw = "select * from " . TABLE_LANGUAGES_DATA . " where 1 " . $search . " order by " . $orderBy; //not selected should be
        $languages_split = new \splitPageResults($current_page_number, $length, $languages_query_raw, $languages_query_numrows);
        $languages_query = tep_db_query($languages_query_raw);
        while ($languages = tep_db_fetch_array($languages_query)) {

            $responseList[] = array(
                '<input name="languages_id" type="radio" value="' . $languages['language_data_id'] . '">&nbsp;' . tep_image(DIR_WS_CATALOG . DIR_WS_ICONS . $languages['icon'], $languages['language_name'], 25, 15) . tep_draw_hidden_field('id', $languages['language_data_id'], 'class="cell_identify"'),
                $languages['language_code'],
                $languages['language_iso'],
                $languages['language_name'],
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $languages_query_numrows,
            'recordsFiltered' => $languages_query_numrows,
            'data' => $responseList
        );
        echo json_encode($response);
    }

    public function actionPredefine() {
        $this->layout = 'popup.tpl';

        Yii::$app->controller->view->predefinedTable = array(
            array(
                'title' => TABLE_HEADING_LANGUAGE_ICON,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_LANGUAGE_CODE,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_LANGUAGE_ISO,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_LANGUAGE_NAME,
                'not_important' => 0,
            ),
        );


        return $this->render('new.tpl');
    }

    public function actionSave() {

        @set_time_limit(0);
        @ignore_user_abort(true);
        
        $default_language_id = \common\helpers\Language::get_default_language_id();
        if (Yii::$app->request->isPost && isset($_POST['action']) && $_POST['action'] == 'predefine') {
            $_languages_id = Yii::$app->request->post('languages_id', 0);
            if ($_languages_id) {
                $data = tep_db_fetch_array(tep_db_query("select * from " . TABLE_LANGUAGES_DATA . " where language_data_id = '" . (int) $_languages_id . "'"));
                if (isset($data['language_data_id'])) {

                    $check = tep_db_query("select * from " . TABLE_LANGUAGES . " where code = '" . tep_db_input($data['language_code']) . "'");
                    if (tep_db_num_rows($check)) {
                        Yii::$app->session->setFlash('warning', sprintf(TEXT_LANGUAGE_CODE_ALREADY_EXISTS, $data['language_code']));
                        return $this->redirect(['index', 'languages_id' => $_languages_id]);
                    }
                    $max = tep_db_fetch_array(tep_db_query("select max(sort_order)+1 as sort_order from " . TABLE_LANGUAGES . " where 1"));
                    $sql_array = array('code' => strtolower($data['language_code']),
                        'image_svg' => $data['icon'],
                        'name' => $data['language_name'],
                        'sort_order' => $max['sort_order'],
                        'languages_status' => 0,
                    );
                    tep_db_perform(TABLE_LANGUAGES, $sql_array);
                    $insert_id = tep_db_insert_id();

                    $formats_query = tep_db_query("select * from " . TABLE_LANGUAGES_FORMATS . " where language_id = '" . (int) $default_language_id . "'");
                    if (tep_db_num_rows($formats_query)) {
                        while ($row = tep_db_fetch_array($formats_query)) {
                            tep_db_query("insert into " . TABLE_LANGUAGES_FORMATS . " set configuration_key = '" . tep_db_input($row['configuration_key']) . "', configuration_value = '" . tep_db_input($row['configuration_value']) . "', language_id = '" . (int) $insert_id . "'");
                        }
                    }
//{{
                    $languages_id = $default_language_id;
                    // create additional categories_description records
                    $categories_query = tep_db_query("select c.categories_id, cd.categories_name, cd.affiliate_id, cd.categories_seo_page_name from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id where cd.language_id = '" . (int) $languages_id . "'");
                    while ($categories = tep_db_fetch_array($categories_query)) {
                        tep_db_query("insert into " . TABLE_CATEGORIES_DESCRIPTION . " (categories_id, language_id, categories_name, affiliate_id, categories_seo_page_name) values ('" . (int) $categories['categories_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($categories['categories_name']) . "', '" . (int) $categories['affiliate_id'] . "', '" . tep_db_input($categories['categories_seo_page_name']) . "')");
                    }
                    tep_db_free_result($categories_query);

                    // create additional products_description records
                    $products_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, pd.products_url, pd.platform_id, pd.products_seo_page_name from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where pd.language_id = '" . (int) $languages_id . "'");
                    while ($products = tep_db_fetch_array($products_query)) {
                        tep_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url, platform_id, products_seo_page_name) values ('" . (int) $products['products_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($products['products_name']) . "', '" . tep_db_input($products['products_description']) . "', '" . tep_db_input($products['products_url']) . "', '" . (int) $products['platform_id'] . "', '" . tep_db_input($products['products_seo_page_name']) . "')");
                    }
                    tep_db_free_result($products_query);

                    // create additional products_options records
                    $products_options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int) $languages_id . "'");
                    while ($products_options = tep_db_fetch_array($products_options_query)) {
                        tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS . " (products_options_id, language_id, products_options_name) values ('" . (int) $products_options['products_options_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($products_options['products_options_name']) . "')");
                    }
                    tep_db_free_result($products_options_query);

                    //coupons
                    $coupons_query = tep_db_query("select coupon_id, coupon_name, coupon_description from " . TABLE_COUPONS_DESCRIPTION . " where language_id = '" . (int) $languages_id . "'");
                    while ($coupons = tep_db_fetch_array($coupons_query)) {
                        tep_db_query("insert into " . TABLE_COUPONS_DESCRIPTION . " (coupon_id, language_id, coupon_name, coupon_description) values ('" . (int) $coupons['coupon_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($coupons['coupon_name']) . "', '" . tep_db_input($coupons['coupon_description']) . "')");
                    }
                    tep_db_free_result($coupons_query);

                    // create additional products_options_values records
                    $products_options_values_query = tep_db_query("select products_options_values_id, products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int) $languages_id . "'");
                    while ($products_options_values = tep_db_fetch_array($products_options_values_query)) {
                        tep_db_query("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . " (products_options_values_id, language_id, products_options_values_name) values ('" . (int) $products_options_values['products_options_values_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($products_options_values['products_options_values_name']) . "')");
                    }
                    tep_db_free_result($products_options_values_query);

                    //property categories
                    $properties_query = tep_db_query("select categories_id, categories_name, categories_description from " . TABLE_PROPERTIES_CATEGORIES_DESCRIPTION . " where language_id = '" . (int) $languages_id . "'");
                    while ($row = tep_db_fetch_array($properties_query)) {
                        tep_db_query("insert into " . TABLE_PROPERTIES_CATEGORIES_DESCRIPTION . " (	categories_id, language_id, categories_name, categories_description ) values ('" . (int) $row['categories_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($row['categories_name']) . "', '" . tep_db_input($row['categories_description']) . "')");
                    }
                    tep_db_free_result($properties_query);

                    //properties
                    $properties_query = tep_db_query("select properties_id, properties_name, properties_description, properties_image, properties_units_id from " . TABLE_PROPERTIES_DESCRIPTION . " where language_id = '" . (int) $languages_id . "'");
                    while ($row = tep_db_fetch_array($properties_query)) {
                        tep_db_query("insert into " . TABLE_PROPERTIES_DESCRIPTION . " (	properties_id, language_id, properties_name, properties_description, properties_image, properties_units_id ) values ('" . (int) $row['properties_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($row['properties_name']) . "', '" . tep_db_input($row['properties_description']) . "', '" . tep_db_input($row['properties_image']) . "', '" . (int) $row['properties_units_id'] . "')");
                    }
                    tep_db_free_result($properties_query);

                    //properties
                    $properties_query = tep_db_query("select values_id, properties_id, values_text, values_number, values_number_upto, values_alt from " . TABLE_PROPERTIES_VALUES . " where language_id = '" . (int) $languages_id . "'");
                    while ($row = tep_db_fetch_array($properties_query)) {
                        tep_db_query("insert into " . TABLE_PROPERTIES_VALUES . " (	values_id, properties_id, language_id, values_text, values_number, values_number_upto, values_alt ) values ('" . (int) $row['values_id'] . "', '" . (int) $row['properties_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($row['values_text']) . "', '" . $row['values_number'] . "', '" . $row['values_number_upto'] . "', '" . tep_db_input($row['values_alt']) . "')");
                    }
                    tep_db_free_result($properties_query);

                    // create additional manufacturers_info records
                    $information_query = tep_db_query("select * from " . TABLE_INFORMATION . " where languages_id = '" . (int) $languages_id . "'");
                    while ($row = tep_db_fetch_array($information_query)) {
                        $row['languages_id'] = (int) $insert_id;
                        $row['info_title'] = $row['info_title'];
                        $row['description'] = $row['description'];
                        $row['page_title'] = $row['page_title'];
                        $row['meta_title'] = $row['meta_title'];
                        $row['page'] = $row['page'];
                        $row['page_type'] = $row['page_type'];
                        $row['meta_description'] = $row['meta_description'];
                        $row['meta_key'] = $row['meta_key'];
                        try {
                            tep_db_perform(TABLE_INFORMATION, $row);
                        } catch (\Exception $e) {
                            \Yii::warning($e->getMessage());
                        }
                    }
                    tep_db_free_result($information_query);

                    // create additional manufacturers_info records
                    $manufacturers_query = tep_db_query("select m.manufacturers_id, mi.manufacturers_url, mi.manufacturers_seo_name from " . TABLE_MANUFACTURERS . " m left join " . TABLE_MANUFACTURERS_INFO . " mi on m.manufacturers_id = mi.manufacturers_id where mi.languages_id = '" . (int) $languages_id . "'");
                    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
                        tep_db_query("insert into " . TABLE_MANUFACTURERS_INFO . " (manufacturers_id, languages_id, manufacturers_url, manufacturers_seo_name) values ('" . $manufacturers['manufacturers_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($manufacturers['manufacturers_url']) . "', '" . tep_db_input($manufacturers['manufacturers_seo_name']) . "')");
                    }
                    tep_db_free_result($manufacturers_query);

                    // create additional orders_status records
                    $orders_status_query = tep_db_query("select orders_status_id, orders_status_groups_id, orders_status_name, orders_status_template, automated from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "'");
                    while ($orders_status = tep_db_fetch_array($orders_status_query)) {
                        tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name, orders_status_groups_id, orders_status_template, automated) values ('" . (int) $orders_status['orders_status_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($orders_status['orders_status_name']) . "', '" . (int) $orders_status['orders_status_groups_id'] . "', '" . tep_db_input($orders_status['orders_status_template']) . "', '" . (int) $orders_status['automated'] . "')");
                    }
                    tep_db_free_result($orders_status_query);
                    // create additional statuses
                    $status_query = tep_db_query("select orders_status_groups_id, orders_status_groups_name, orders_status_groups_color from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int) $languages_id . "'");
                    while ($status = tep_db_fetch_array($status_query)) {
                        tep_db_query("insert into " . TABLE_ORDERS_STATUS_GROUPS . " (orders_status_groups_id, language_id, orders_status_groups_name, orders_status_groups_color) values ('" . (int) $status['orders_status_groups_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($status['orders_status_groups_name']) . "', '" . tep_db_input($status['orders_status_groups_color']) . "')");
                    }
                    tep_db_free_result($status_query);

                    $data_query = tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_INDICATION_TEXT . " where language_id = '" . (int) $languages_id . "'");
                    while ($data = tep_db_fetch_array($data_query)) {
                        $data['language_id'] = (int) $insert_id;
                        try {
                            tep_db_perform(TABLE_PRODUCTS_STOCK_INDICATION_TEXT, $data);
                        } catch (\Exception $e) {
                            \Yii::warning($e->getMessage());
                        }
                    }
                    tep_db_free_result($data_query);

                    $data_query = tep_db_query("select * from " . TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT . " where language_id = '" . (int) $languages_id . "'");
                    while ($data = tep_db_fetch_array($data_query)) {
                        $data['language_id'] = (int) $insert_id;
                        tep_db_perform(TABLE_PRODUCTS_STOCK_DELIVERY_TERMS_TEXT, $data);
                    }
                    tep_db_free_result($data_query);

                    $data_query = tep_db_query("select * from " . TABLE_PRODUCTS_XSELL_TYPE . " where language_id = '" . (int) $languages_id . "'");
                    while ($data = tep_db_fetch_array($data_query)) {
                        $data['language_id'] = (int) $insert_id;
                        tep_db_perform(TABLE_PRODUCTS_XSELL_TYPE, $data);
                    }
                    tep_db_free_result($data_query);

                    // create additional countries records
                    $countries_query = tep_db_query("select * from " . TABLE_COUNTRIES . " where language_id = '" . (int) $languages_id . "'");
                    while ($countries = tep_db_fetch_array($countries_query)) {
                        //tep_db_query("insert into " . TABLE_COUNTRIES . "(countries_id, countries_name, countries_iso_code_2, countries_iso_code_3, address_format_id, language_id) values ('" .$countries['countries_id'] ."', '" .tep_db_input($countries['countries_name']) ."', '" .$countries['countries_iso_code_2'] ."', '" .$countries['countries_iso_code_3'] ."', '" .$countries['address_format_id'] ."', '" .(int)$insert_id  ."')");
                        $countries['language_id'] = (int) $insert_id;
                        $countries['countries_name'] = $countries['countries_name'];
                        tep_db_perform(TABLE_COUNTRIES, $countries);
                    }
                    tep_db_free_result($countries_query);

                    //date formats
                    $formats_query = tep_db_query("select configuration_key, configuration_value from " . TABLE_LANGUAGES_FORMATS . " where language_id = '" . (int) $languages_id . "'");
                    while ($formats = tep_db_fetch_array($formats_query)) {
                        tep_db_query("insert into " . TABLE_LANGUAGES_FORMATS . " (	configuration_key, configuration_value, language_id) values ('" . tep_db_input($formats['configuration_key']) . "', '" . tep_db_input($formats['configuration_value']) . "', '" . (int) $insert_id . "')");
                    }
                    tep_db_free_result($formats_query);

                    //banners
                    $banners_query = tep_db_query("select banners_id, platform_id, banners_title, banners_url, banners_image, banners_html_text from " . TABLE_BANNERS_LANGUAGES . " where language_id = '" . (int) $languages_id . "'");
                    while ($banners = tep_db_fetch_array($banners_query)) {
                        tep_db_query("insert into " . TABLE_BANNERS_LANGUAGES . " (	banners_id, platform_id, banners_title, banners_url, banners_image, banners_html_text, language_id) values ('" . (int) $banners['banners_id'] . "', '" . (int) $banners['platform_id'] . "', '" . tep_db_input($banners['banners_title']) . "', '" . tep_db_input($banners['banners_url']) . "', '" . tep_db_input($banners['banners_image']) . "', '" . tep_db_input($banners['banners_html_text']) . "', '" . (int) $insert_id . "')");
                    }
                    tep_db_free_result($banners_query);

                    //design settings
                    $settings_query = tep_db_query("select box_id, setting_name, setting_value from " . TABLE_DESIGN_BOXES_SETTINGS . " where language_id = '" . (int) $languages_id . "'");
                    while ($settings = tep_db_fetch_array($settings_query)) {
                        tep_db_query("insert into " . TABLE_DESIGN_BOXES_SETTINGS . " (	box_id, setting_name, setting_value, language_id ) values ('" . (int) $settings['box_id'] . "', '" . tep_db_input($settings['setting_name']) . "', '" . tep_db_input($settings['setting_value']) . "', '" . (int) $insert_id . "')");
                    }
                    $settings_query = tep_db_query("select box_id, setting_name, setting_value from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where language_id = '" . (int) $languages_id . "'");
                    while ($settings = tep_db_fetch_array($settings_query)) {
                        tep_db_query("insert into " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " (	box_id, setting_name, setting_value, language_id ) values ('" . (int) $settings['box_id'] . "', '" . tep_db_input($settings['setting_name']) . "', '" . tep_db_input($settings['setting_value']) . "', '" . (int) $insert_id . "')");
                    }

                    //email templates
                    $templates_query = tep_db_query("select email_templates_id, platform_id, affiliate_id, email_templates_subject, email_templates_body from " . TABLE_EMAIL_TEMPLATES_TEXTS . " where language_id = '" . (int) $languages_id . "' and affiliate_id = 0");
                    while ($templates = tep_db_fetch_array($templates_query)) {
                        tep_db_query("insert into " . TABLE_EMAIL_TEMPLATES_TEXTS . " (	email_templates_id, platform_id, language_id, affiliate_id, email_templates_subject, email_templates_body ) values ('" . (int) $templates['email_templates_id'] . "', '" . (int) $templates['platform_id'] . "','" . (int) $insert_id . "', 0, '" . tep_db_input($templates['email_templates_subject']) . "', '" . tep_db_input($templates['email_templates_body']) . "')");
                    }
                    tep_db_free_result($templates_query);

                    //menu titles
                    $titles_query = tep_db_query("select item_id, title, link from " . TABLE_MENU_TITLES . " where language_id = '" . (int) $languages_id . "'");
                    while ($titles = tep_db_fetch_array($titles_query)) {
                        tep_db_query("insert into " . TABLE_MENU_TITLES . " (	language_id, item_id, title, link ) values ('" . (int) $insert_id . "', '" . (int) $titles['item_id'] . "', '" . tep_db_input($titles['title']) . "', '" . tep_db_input($titles['link']) . "')");
                    }
                    tep_db_free_result($titles_query);

                    //product images
                    $pimages_query = tep_db_query("select products_images_id, image_title, image_alt, orig_file_name, hash_file_name, file_name, alt_file_name from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where language_id = '" . (int) $languages_id . "'");
                    while ($pimages = tep_db_fetch_array($pimages_query)) {
                        tep_db_query("insert into " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " (	products_images_id, language_id, image_title, image_alt, orig_file_name, hash_file_name, file_name, alt_file_name ) values ('" . (int) $pimages['products_images_id'] . "', '" . (int) $insert_id . "', '" . tep_db_input($pimages['image_title']) . "', '" . tep_db_input($pimages['image_alt']) . "', '" . tep_db_input($pimages['orig_file_name']) . "', '" . tep_db_input($pimages['hash_file_name']) . "', '" . tep_db_input($pimages['file_name']) . "', '" . tep_db_input($pimages['alt_file_name']) . "')");
                    }
                    tep_db_free_result($pimages_query);
                    //translations
                    $translation_query = tep_db_query("select translation_key, translation_entity, translation_value, hash from " . TABLE_TRANSLATION . " where language_id = '" . (int) $languages_id . "'");
                    while ($trans = tep_db_fetch_array($translation_query)) {
                        try {
                            tep_db_query("insert into " . TABLE_TRANSLATION . " (	language_id, translation_key, translation_entity, translation_value, hash ) values ( '" . (int) $insert_id . "', '" . tep_db_input($trans['translation_key']) . "', '" . tep_db_input($trans['translation_entity']) . "', '" . tep_db_input($trans['translation_value']) . "', '" . tep_db_input($trans['hash']) . "')");
                        } catch (\Exception $e) {
                            \Yii::warning($e->getMessage());
                        }
                    }
                    tep_db_free_result($translation_query);

//        }}          

                    return $this->redirect(Url::to(['languages/edit', 'languages_id' => $insert_id]));
                }
            }
            return $this->redirect(Url::to(['languages/index']));
        }

        //echo '<pre>';print_r($_POST);die;
        $lID = Yii::$app->request->get('languages_id', 0);
        $name = tep_db_prepare_input(\Yii::$app->request->post('name'));
        $code = strtolower(tep_db_prepare_input(\Yii::$app->request->post('code')));
        $image = tep_db_prepare_input(\Yii::$app->request->post('image'));
        $image_svg = tep_db_prepare_input(\Yii::$app->request->post('image_svg'));
        $directory = tep_db_prepare_input(\Yii::$app->request->post('directory'));
        //$sort_order = tep_db_prepare_input(\Yii::$app->request->post('sort_order']);
        $status = tep_db_prepare_input(\Yii::$app->request->post('languages_status'));
        $locale = tep_db_prepare_input(\Yii::$app->request->post('locale'));

        /* if (!file_exists(DIR_FS_CATALOG . DIR_WS_ICONS . $image)){
          if (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $image)){
          @copy(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $image, DIR_FS_CATALOG . DIR_WS_ICONS . $image);
          } else {
          $image = '';
          }
          } */

        if (!file_exists(DIR_FS_CATALOG . DIR_WS_ICONS . $image_svg)) {
            if (file_exists(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $image_svg)) {
                @copy(\Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $image_svg, DIR_FS_CATALOG . DIR_WS_ICONS . $image_svg);
            } else {
                $image_svg = '';
            }
        }

        if (is_array(\Yii::$app->request->post('configuration_key'))) {
            foreach (\Yii::$app->request->post('configuration_key') as $lang => $data) {
                tep_db_query("delete from " . TABLE_LANGUAGES_FORMATS . " where language_id='" . (int) $lang . "'");
                foreach ($data as $key => $value) {
                    if (tep_not_null($value) && tep_not_null($_POST['configuration_value'][$lang][$key] ?? null)) {
                        $sql_array = ['configuration_key' => tep_db_prepare_input($value),
                            'configuration_value' => tep_db_prepare_input($_POST['configuration_value'][$lang][$key] ?? null),
                            'language_id' => (int) $lang,
                        ];
                        tep_db_perform(TABLE_LANGUAGES_FORMATS, $sql_array);
                        if (tep_not_null($_POST['configuration_description'][$lang][$key] ?? null)) {
                            \common\helpers\Translation::replaceTranslationValueByOldValue($value . '_DESC', 'admin/languages', $lang, trim(tep_db_prepare_input($_POST['configuration_description'][$lang][$key])));
                        }
                    }
                }
            }
        }

        if ($lID) {
            $check = tep_db_query("select * from " . TABLE_LANGUAGES . " where code = '" . tep_db_input($code) . "' and languages_id <> '" . (int)$lID . "'");
            if (tep_db_num_rows($check)) {
                Yii::$app->session->setFlash('warning', sprintf(TEXT_LANGUAGE_CODE_ALREADY_EXISTS, $code));
                return $this->redirect(['edit', 'languages_id' => $lID, 'row' => Yii::$app->request->post('row_id', 0)]);
            }
            $hide_in_admin = (int)\Yii::$app->request->post('hide_in_admin', 0);

            tep_db_query("update " . TABLE_LANGUAGES . " set name = '" . tep_db_input($name) . "', code = '" . tep_db_input($code) . "', image = '" . tep_db_input($image) . "', image_svg = '" . tep_db_input($image_svg) . "', directory = '" . tep_db_input($directory) . "'/*, sort_order = '" . tep_db_input($sort_order ?? null) . "'*/, locale = '" . tep_db_input($locale) . "', hide_in_admin = '" . $hide_in_admin . "'  where languages_id = '" . (int) $lID . "'");

            if (isset($_POST['default']) && $_POST['default'] == 'on') {
                tep_db_query("update " . TABLE_LANGUAGES . " set languages_status = 1, hide_in_admin = 0 where languages_id = " . (int) $lID);
                tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . tep_db_input($code) . "' where configuration_key = 'DEFAULT_LANGUAGE'");
            }

            if (isset($_POST['flag']) && (int)$_POST['flag'] == 1) {
                tep_db_query("update " . TABLE_LANGUAGES . " set languages_status = 1 where languages_id = '" . (int) $lID . "'");
            } elseif ($lID != \common\helpers\Language::get_default_language_id()/* DEFAULT_LANGUAGE */) {
                tep_db_query("update " . TABLE_LANGUAGES . " set languages_status = 0 where languages_id = '" . (int) $lID . "'");
            }
                

        }

        $action = 'added';

        //echo json_encode(array('message' => 'Language is ' . $code . ' ' . $action, 'messageType' => 'alert-success'));
        Yii::$app->session->setFlash('success', 'Language ' . strtoupper($code) . ' is ' . $action);
        $url = Url::to(['languages/edit', 'languages_id' => $lID, 'row' => Yii::$app->request->post('row_id', 0)]);
        //var_dump($url);die;
        return $this->redirect($url);
    }

    public function actionDelete() {
        global $language;

        $lID = Yii::$app->request->post('languages_id');

        if ($lID) {
            $lng_query = tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_LANGUAGE . "'");
            $lng = tep_db_fetch_array($lng_query);
            if ($lng['languages_id'] == $lID) {
                //tep_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '' where configuration_key = 'DEFAULT_CURRENCY'");
                Yii::$app->session->setFlash('danger', TEXT_IS_DEFAULT_LANGUAGE);
                echo 'reload';
                exit();
            } else {
                $code_query = tep_db_query("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int) $lID . "'");
                if (tep_db_num_rows($code_query)) {
                    $code = tep_db_fetch_array($code_query);
                    tep_db_query("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_COUPONS_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_ORDERS_STATUS_GROUPS . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_COUNTRIES . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_LANGUAGES_FORMATS . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_BANNERS_LANGUAGES . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_DESIGN_BOXES_SETTINGS_TMP . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_EMAIL_TEMPLATES_TEXTS . " where language_id = '" . (int) $lID . "' and affiliate_id = 0");
                    tep_db_query("delete from " . TABLE_MENU_TITLES . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_TRANSLATION . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_PLATFORM_FORMATS . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_PROPERTIES_CATEGORIES_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_PROPERTIES_DESCRIPTION . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_PROPERTIES_VALUES . " where language_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_INFORMATION . " where languages_id = '" . (int) $lID . "'");
                    tep_db_query("delete from " . TABLE_PRODUCTS_XSELL_TYPE . " where language_id = '" . (int) $lID . "'");

                    $pl_query = tep_db_query("select platform_id, default_language, defined_languages from " . TABLE_PLATFORMS . " where default_language = '" . tep_db_input($code['code']) . "' or defined_languages like '%" . tep_db_input($code['code']) . "%'");
                    if (tep_db_num_rows($pl_query)) {

                        while ($data = tep_db_fetch_array($pl_query)) {
                            $_langs = explode(',', $data['defined_languages']);
                            $_langs = array_flip($_langs);
                            unset($_langs[$code['code']]);
                            $_langs = array_flip($_langs);
                            sort($_langs);
                            tep_db_query("update " . TABLE_PLATFORMS . " set defined_languages = '" . (implode(',', $_langs)) . "' where platform_id = '" . $data['platform_id'] . "'");
                            if (strtolower($code['code']) == strtolower($data['default_language'])) {
                                tep_db_query("update " . TABLE_PLATFORMS . " set default_language = '" . $_langs[0] . "' where platform_id = '" . $data['platform_id'] . "'");
                            }
                        }
                    }
                    tep_db_query("delete from " . TABLE_LANGUAGES . " where languages_id = '" . (int) $lID . "'");
                }
            }
        }
        echo 'reset';
    }

    public function actionSwitchStatus() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $id = Yii::$app->request->post('languages_id');
        $status = Yii::$app->request->post('languages_status');
        $lng_query = tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_LANGUAGE . "'");
        $lng = tep_db_fetch_array($lng_query);
        if ($lng['languages_id'] == $id) {
            Yii::$app->session->setFlash('danger', TEXT_IS_DEFAULT_LANGUAGE);
            echo 'reload';
            exit();
        } else {
            tep_db_query("update " . TABLE_LANGUAGES . " set 	languages_status = '" . ($status == 'true' ? 1 : 0) . "' where languages_id = '" . (int) $id . "'");
            if ((int) $languages_id == (int) $id && $status != 'true') {
                tep_session_unregister('language');
                tep_session_unregister('languages_id');
                echo 'reload';
                exit();
            } else {
                echo 'reset';
                exit();
            }
        }
    }

    public function actionSortOrder() {

        $moved_id = (int) $_POST['sort_lang'];
        $ref_array = (isset($_POST['lang']) && is_array($_POST['lang'])) ? array_map('intval', $_POST['lang']) : array();
        if ($moved_id && in_array($moved_id, $ref_array)) {
            // {{ normalize
            $order_counter = 0;
            $order_list_r = tep_db_query(
                    "SELECT languages_id, sort_order " .
                    "FROM " . TABLE_LANGUAGES . " " .
                    "WHERE 1 " .
                    "ORDER BY sort_order, name"
            );
            while ($order_list = tep_db_fetch_array($order_list_r)) {
                $order_counter++;
                tep_db_query("UPDATE " . TABLE_LANGUAGES . " SET sort_order='{$order_counter}' WHERE languages_id='{$order_list['languages_id']}' ");
            }
            // }} normalize
            $get_current_order_r = tep_db_query(
                    "SELECT languages_id, sort_order " .
                    "FROM " . TABLE_LANGUAGES . " " .
                    "WHERE languages_id IN('" . implode("','", $ref_array) . "') " .
                    "ORDER BY sort_order"
            );
            $ref_ids = array();
            $ref_so = array();
            while ($_current_order = tep_db_fetch_array($get_current_order_r)) {
                $ref_ids[] = (int) $_current_order['languages_id'];
                $ref_so[] = (int) $_current_order['sort_order'];
            }

            foreach ($ref_array as $_idx => $id) {
                tep_db_query("UPDATE " . TABLE_LANGUAGES . " SET sort_order='{$ref_so[$_idx]}' WHERE languages_id='{$id}' ");
            }
        }
    }

    /* public function checkTables(){
        $_def_lang_id = \common\helpers\Language::get_default_language_id();
        $check_query = tep_db_query("select TABLE_NAME, COLUMN_NAME, DATA_TYPE, COLUMN_KEY from information_schema.COLUMNS where TABLE_SCHEMA='" . DB_DATABASE . "' and COLUMN_NAME like '%language%' and DATA_TYPE like '%int%'");
        if (tep_db_num_rows($check_query)){
            $installed_langs = count (\common\helpers\Language::get_languages(true));
            while($table = tep_db_fetch_array($check_query)){
            if (in_array($table['TABLE_NAME'], [TABLE_LANGUAGES_DATA])) continue;
                $check_langs = tep_db_num_rows(tep_db_query("select distinct " . $table['COLUMN_NAME'] . " from " . $table['TABLE_NAME']));
                if ($check_langs < $installed_langs){

                }
            }
        }
      } */
}
