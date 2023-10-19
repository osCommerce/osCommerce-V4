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

use backend\models\ProductNameDecorator;
use Yii;

class ProductsattributesController extends Sceleton {

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_CATEGORIES_PRODUCTS_ATTRIBUTES', 'TEXT_PRODUCTS_ATTRIBUTES'];

    public function actionIndex() {
        $this->selectedMenu = array('catalog', 'product_attributes', 'productsattributes');

        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('productsattributes/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="#" class="btn btn-primary" onClick="return editAttribute(0)">' . IMAGE_INSERT . '</a>';
        $this->view->headingTitle = HEADING_TITLE;

        $this->view->attributesTable = array(
            array(
                'title' => TABLE_HEADING_OPT_NAME,
                'not_important' => 0
            ),
//                array(
//                    'title'         => TABLE_HEADING_OPT_SORT_ORDER,
//                    'not_important' => 1
//                ),
        );

        $row = (int) Yii::$app->request->get('row');
        $global_id = (int) Yii::$app->request->get('global_id');
        $global_type_code = Yii::$app->request->get('global_type_code', 'option');

        return $this->render('index', [
                    'row' => $row,
                    'global_id' => $global_id,
                    'global_type_code' => $global_type_code
                ]);
    }

    public function actionList() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_option_id = (int) Yii::$app->request->get('id', 0);

        if ($length == -1)
            $length = 1000;
        $keywords = $_GET['search']['value'] ?? null;

        $responseList = array();
        $subOption = $current_option_id > 0;
        if ($subOption) {
            $query = \common\models\ProductsOptionsValues::find()->alias('pov')->select(['pov.products_options_values_id', 'pov.products_options_values_name', 'pov.products_options_values_name_alias', 'pov2po.products_options_id'])
                    ->joinWith('values2Options pov2po', false)
                    ->where(['pov2po.products_options_id' => $current_option_id, 'pov.language_id' => $languages_id])
                    ->andFilterWhere(['like', 'pov.products_options_values_name', $keywords])
                    ->orderBy(['pov.products_options_values_sort_order' => SORT_ASC, 'pov.products_options_values_name' => SORT_ASC]);

            $responseList[] = array(
                '<span class="parent_cats"><i class="icon-circle"></i><i class="icon-circle"></i><i class="icon-circle"></i></span>' .
                '<input class="cell_type" type="hidden" value="root" >' .
                '<input class="cell_identify" type="hidden" value="0" data-option_id="' . $current_option_id . '">'
            );
        } else {
            $query = \common\models\ProductsOptions::find()
                    ->where(['language_id' => $languages_id])
                    ->andFilterWhere(['like', 'products_options_name', $keywords])
                    ->orderBy(['products_options_sort_order' => SORT_ASC, 'products_options_name' => SORT_ASC]);
        }

        $options_query_numrows = $query->count();
        $query->limit($length)->offset($start);
        $Qgroups = $query->asArray()->all();

        $cell_type = $subOption ? 'suboption' : 'option';
        foreach ($Qgroups as $Dgroups) {
            $responseList[] = array(
                '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="' . ($subOption ? 'optval_name' : 'cat_name') . ' cat_name_attr">' . $Dgroups[$subOption ? 'products_options_values_name' : 'products_options_name'] .
                '<input class="cell_identify" type="hidden" value="' . $Dgroups[$subOption ? 'products_options_values_id' : 'products_options_id'] . '">' .
                '<input class="cell_type" type="hidden" value="' . $cell_type . '" >' .
                '</div>' . ($subOption ?
                ($Dgroups['products_options_values_name_alias'] != "" ? '<div class="optval_name cat_name_attr"><b>Alias:</b> ' . $Dgroups['products_options_values_name_alias'] . '</div>' : '') : '') .
                '</div>'
            );
        }

        $response = array(
            'draw' => $draw,
            'recordsTotal' => $options_query_numrows,
            'recordsFiltered' => $options_query_numrows,
            'data' => $responseList
        );
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    function actionItempreedit() {
        $this->layout = FALSE;

        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/productsattributes');

        $item_id = (int) Yii::$app->request->post('item_id');
        $type_code = Yii::$app->request->post('type_code');
        $global_id = Yii::$app->request->post('global_id');

        $products_num = $values_num = 0;
        $notice = '';
        if ($type_code == 'suboption') {
            $Rec = \common\models\ProductsOptionsValues::find()->where(['products_options_values_id' => $item_id, 'language_id' => $languages_id])->limit(1)->one();
            $Dvalue['name'] = $Rec->products_options_values_name ?? '';

            $products_num = \common\models\ProductsAttributes::find()->where(['options_values_id' => $item_id])->count();
            if ($products_num > 0) {
                $notice = sprintf(TEXT_OPTION_VALUE_NOTICE, $products_num);
            }

            $option = \common\models\ProductsOptions::find()
                    ->where(['language_id' => $languages_id])
                    ->joinWith('options2Values', false)
                    ->andWhere(['products_options_values_id' => $item_id])
                    ->limit(1)
                    ->one();
            ?>
            <script type="text/javascript">
                $('.attribute-name-heading').remove();
                $('.top_bead h1').append('<span class="attribute-name-heading">: <?php echo $option->products_options_name ?? null; ?></span>')
            </script>
            <?php
        } else {
            $Rec = \common\models\ProductsOptions::find()->where(['products_options_id' => $item_id, 'language_id' => $languages_id])->limit(1)->one();
            $Dvalue['name'] = $Rec->products_options_name ?? '';

            $products_num = \common\models\ProductsAttributes::find()->where(['options_id' => $item_id])->groupBy(['products_id'])->count();

            $values_num = \common\models\ProductsOptions2ProductsOptionsValues::find()->where(['products_options_id' => $item_id])->count();
            if ($values_num > 0 OR $products_num > 0) {
                $notice = sprintf(TEXT_OPTION_NOTICE, $products_num, $values_num);
            }
            ?>
            <script type="text/javascript">
                $('.attribute-name-heading').remove()
            </script>
            <?php
        }
        return $this->renderAjax('preedit', ['Dvalue' => $Dvalue, 'notice' => $notice, 'item_id' => $item_id, 'type_code' => $type_code, 'global_id' => $global_id, 'products_num' => $products_num]);
    }

    public function actionViewProducts() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $item_id = (int) Yii::$app->request->get('item_id');
        $type_code = Yii::$app->request->get('type_code');
        $response = [];
        $subOption = $type_code == 'suboption';

        $response = \common\models\Products::find()->alias('p')
                ->joinWith('description pd', false)
                ->joinWith('productAttributes pa', false)
                ->andWhere([$subOption ? 'pa.options_values_id' : 'pa.options_id' => $item_id])
                ->select( 'p.products_model, p.products_id')
                ->addSelect(['name' => ProductNameDecorator::descriptionExpr()])
                ->asArray()
                ->distinct()
                ->all();

        foreach ($response as $num => $row) {
            $response[$num]['model'] = tep_not_null($row['products_model']) ? ' ( ' . $row['products_model'] . ' )' : '';
            $response[$num]['url'] = \yii\helpers\Url::to([FILENAME_CATEGORIES . '/productedit', 'pID' => $row['products_id']]);
        }
        return $this->renderAjax('list', ['content' => $response]);
    }

    public function actionAttributeedit() {

        \common\helpers\Translation::init('admin/productsattributes');
        \common\helpers\Translation::init('admin/properties');

        $type_code = Yii::$app->request->get('type_code', NULL);
        $products_options_id = (int) Yii::$app->request->get('products_options_id');
        $global_id = (int) Yii::$app->request->get('global_id', 0);

        $default_language_id = \Yii::$app->settings->get('languages_id');
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            if ($languages[$i]['code'] == DEFAULT_LANGUAGE) {
                $default_language_id = $languages[$i]['id'];
            }
        }

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'.properties form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        $options = [];
        $process_type = 'option';
        $type = '';
        if (!$products_options_id) {
            $header = TEXT_INSERT_ATTRIBUTE;
            if ($global_id > 0) {
                $header = TEXT_OPTION_VALUE_NEW_HEADING;
                $process_type = 'value';
            }
            foreach ($languages as $languages_data) {
                $lang_id = $languages_data['id'];
                $options[$lang_id] = array('option_name' => '', 'option_name_alias' => '', 'option_image' => '', 'option_color' => '');
            }
        } elseif ($products_options_id > 0) {
            $header = TEXT_EDIT_ATTRIBUTE;

            if ($type_code == 'option') {

                foreach ($languages as $languages_data) {
                    $Dvalue = \common\models\ProductsOptions::find()->where(['products_options_id' => $products_options_id, 'language_id' => $languages_data['id']])->asArray()->one();
                    if (is_array($Dvalue)) {
                        $type = $Dvalue['type'];
                        $is_virtual = $Dvalue['is_virtual'];
                        $display_filter = $Dvalue['display_filter'];
                        $display_search = $Dvalue['display_search'];
                        $options[$languages_data['id']] = [
                            'option_name' => $Dvalue['products_options_name'],
                            'option_image' => $Dvalue['products_options_image'],
                            'option_color' => $Dvalue['products_options_color'],
                        ];
                    } else {
                        $options[$languages_data['id']] = [
                            'option_name' => '',
                            'option_image' => '',
                            'option_color' => '',
                        ];
                    }
                }
            } else {
                $header = TEXT_OPTION_VALUE_EDIT_HEADING;
                $process_type = 'value';
                foreach ($languages as $languages_data) {
                    $Dvalue = \common\models\ProductsOptionsValues::find()->where(['products_options_values_id' => $products_options_id, 'language_id' => $languages_data['id']])->asArray()->one();
                    if (is_array($Dvalue)) {
                        $type = $Dvalue['custom_input_type'];
                        $options[$languages_data['id']] = [
                            'option_name' => $Dvalue['products_options_values_name'],
                            'option_name_alias' => $Dvalue['products_options_values_name_alias'],
                            'option_image' => $Dvalue['products_options_values_image'],
                            'option_color' => $Dvalue['products_options_values_color'],
                        ];
                    } else {
                        $options[$languages_data['id']] = [
                            'option_name' => '',
                            'option_name_alias' => '',
                            'option_image' => '',
                            'option_color' => '',
                        ];
                    }
                }
            }
        }

        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('productsattributes/attributesubmit'), 'title' => $header);
        $this->view->headingTitle = $header;

        return $this->render('edit.tpl', [
                    'languages' => $languages,
                    'default_language' => DEFAULT_LANGUAGE,
                    'products_options_id' => $products_options_id,
                    'options' => $options,
                    'process_type' => $process_type,
                    'type_code' => $type_code,
                    'type' => $type ?? null,
                    'is_virtual' => $is_virtual ?? null,
                    'display_filter' => $display_filter ?? null,
                    'display_search' => $display_search ?? null,
                    'global_id' => $global_id,
        ]);
    }

    function actionAttributesubmit() {

        \common\helpers\Translation::init('admin/productsattributes');

        $same_all_languages = (int) Yii::$app->request->post('same_all_languages');
        $products_options_id = (int) Yii::$app->request->post('products_options_id');
        $global_id = (int) Yii::$app->request->post('global_id', 0);
        $option_name = Yii::$app->request->post('option_name');
        $option_name_alias = Yii::$app->request->post('option_name_alias');
        $option_image = Yii::$app->request->post('option_image');
        $option_image_loaded = Yii::$app->request->post('option_image_loaded');
        $option_image_delete = Yii::$app->request->post('option_image_delete');
        $option_color = Yii::$app->request->post('option_color');
        $option_sort_order = Yii::$app->request->post('option_sort_order_eng', array());
        $type_code = Yii::$app->request->post('type_code', 'option');
        $type = Yii::$app->request->post('type', '');
        $is_virtual = Yii::$app->request->post('is_virtual', 0);
        $display_filter = Yii::$app->request->post('display_filter', 0);
        $display_search = Yii::$app->request->post('display_search', 0);

        $_l = \common\helpers\Language::get_languages(true);
        $all = [];
        foreach ($_l as $_v) {
            $all[] = $_v['id'];
        }
        $_def_l = \common\helpers\Language::get_default_language_id();

        $existed_l = [];
        $default = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {

            if ($type_code == 'suboption') {

                $insert = $products_options_id === 0;
                $id = $insert ? \common\models\ProductsOptionsValues::find()->max('products_options_values_id') + 1 : (int) $products_options_id;

                foreach ($option_name as $_language_id => $option) {

                    $existed_l[] = $_language_id;
                    if ($_def_l == $_language_id) {
                        $default = [
                            'products_options_values_name' => $option,
                            'products_options_values_name_alias' => $option_name_alias[$_language_id],
                            'sort_order' => $option_sort_order[$_language_id] ?? null,
                        ];
                    }

                    $obj = $insert ? null : \common\models\ProductsOptionsValues::findOne(['language_id' => (int) $_language_id, 'products_options_values_id' => $id]);
                    if (is_null($obj)) { // new also for updating if there isn't record for that language
                        $obj = new \common\models\ProductsOptionsValues;
                    }

                    $obj->products_options_values_id = $id;
                    $obj->products_options_values_name = $option;
                    $obj->products_options_values_name_alias = $option_name_alias[$_language_id];
                    $obj->language_id = (int) $_language_id;
                    $obj->custom_input_type = $type;
                    if ($same_all_languages) {
                        $obj->products_options_values_name = $option_name[$_def_l];
                        $obj->products_options_values_name_alias = $option_name_alias[$_def_l];
                        $obj->products_options_values_color = $option_color[$_def_l];
                    } else {
                        if (isset($option_sort_order[$_language_id])) {
                            $obj->products_options_values_sort_order = $option_sort_order[$_language_id];
                        }
                        if (isset($option_color[$_language_id])) {
                            $obj->products_options_values_color = $option_color[$_language_id];
                        }
                    }

                    $obj->products_options_values_image = \common\helpers\Image::prepareSavingImage(
                        $obj->products_options_values_image,
                        $option_image[$_language_id],
                        $option_image_loaded[$_language_id],
                        'attributes',
                        $option_image_delete[$_language_id]
                    );

                    $obj->save();
                }

                $existed_l = array_unique($existed_l);
                $all = array_diff($all, $existed_l);
                if (count($all) && count($default) > 0) {
                    foreach ($all as $_language_id) {

                        $obj = $insert ? null : \common\models\ProductsOptionsValues::findOne(['language_id' => (int) $_language_id, 'products_options_values_id' => $id]);
                        if (is_null($obj)) { // new also for updating if there isn't record for that language
                            $obj = new \common\models\ProductsOptionsValues;
                        }

                        $obj->products_options_values_id = $id;
                        $obj->language_id = (int) $_language_id;
                        $obj->products_options_values_name = $default['products_options_values_name'];
                        $obj->products_options_values_sort_order = (int) $default['sort_order'] ?? 0;
                        $obj->save();
                    }
                }

                if ($insert) {
                    $obj = new \common\models\ProductsOptions2ProductsOptionsValues;
                    $obj->products_options_id = $global_id;
                    $obj->products_options_values_id = $id;
                    $obj->save();
                }

                // ===== Option =====================================================================================================
            } else {

                $insert = $products_options_id === 0;
                $id = $insert ? \common\models\ProductsOptions::find()->max('products_options_id') + 1 : (int) $products_options_id;

                foreach ($option_name as $_language_id => $option) {

                    $existed_l[] = $_language_id;
                    if ($_def_l == $_language_id) {
                        $default = ['products_options_name' => $option];
                        $default['sort_order'] = $option_sort_order[$_language_id] ?? null;
                    }

                    $obj = $insert ? null : \common\models\ProductsOptions::findOne(['language_id' => (int) $_language_id, 'products_options_id' => $id]);
                    if (is_null($obj)) { // new also for updating if there isn't record for that language
                        $obj = new \common\models\ProductsOptions;
                    }

                    $obj->products_options_id = $id;
                    $obj->language_id = (int) $_language_id;
                    $obj->type = $type;
                    $obj->is_virtual = $is_virtual;
                    $obj->display_filter = $display_filter;
                    $obj->display_search = $display_search;
                    if ($same_all_languages) {
                        $obj->products_options_name = $option_name[$_def_l];
                        $obj->products_options_color = $option_color[$_def_l];
                    } else {
                        $obj->products_options_name = $option;
                        if (isset($option_color[$_language_id])) {
                            $obj->products_options_color = $option_color[$_language_id];
                        }
                        $obj->products_options_sort_order = $option_sort_order[$_language_id] ?? 0;
                    }

                    $obj->products_options_image = \common\helpers\Image::prepareSavingImage(
                        $obj->products_options_image,
                        $option_image[$_language_id],
                        $option_image_loaded[$_language_id],
                        'attributes',
                        $option_image_delete[$_language_id]
                    );

                    $obj->save();
                }
                $existed_l = array_unique($existed_l);
                $all = array_diff($all, $existed_l);
                if (count($all) && count($default) > 0) {
                    foreach ($all as $_language_id) {

                        $obj = $insert ? null : \common\models\ProductsOptions::findOne(['language_id' => (int) $_language_id, 'products_options_id' => $id]);
                        if (is_null($obj)) { // new also for updating if there isn't record for that language
                            $obj = new \common\models\ProductsOptions;
                        }
                        $obj->products_options_id = $id;
                        $obj->language_id = (int) $_language_id;
                        $obj->products_options_name = $default['products_options_name'];
                        $obj->products_options_sort_order = $default['sort_order'] ?? 0;
                        $obj->type = $type;
                        $obj->is_virtual = (int) $is_virtual;
                        $obj->display_filter = (int) $display_filter;
                        $obj->display_search = (int) $display_search;
                        $obj->save();
                    }
                }
            }

            $transaction->commit();
            return $this->redirect(Yii::$app->urlManager->createUrl(['productsattributes/', 'type_code' => $type_code, 'global_id' => $global_id]));
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    function actionConfirmadeleteoption() {
        $languages_id = (int) \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/productsattributes');
        \common\helpers\Translation::init('admin/faqdesk');

        $this->layout = FALSE;

        $products_options_id = (int) Yii::$app->request->post('products_options_id');
        $cell_type = Yii::$app->request->post('cell_type');

        if ($cell_type == 'root')
            $cell_type = 'suboption';

        $products_num = $values_num = 0;

        $process_item_name = '';
        if ($cell_type == 'suboption') {
            $products_num = \common\models\ProductsAttributes::find()->where(['options_values_id' => $products_options_id])->count();

            $obj = \common\models\ProductsOptionsValues::findOne(['products_options_values_id' => $products_options_id, 'language_id' => $languages_id]);

            $TEXT_INFO_HEADING = TEXT_OPTION_VALUE_DELETE_HEADING . ' ' . ($obj->products_options_values_name ?? null);
            $TEXT_INTRO = TEXT_OPTION_VALUE_DELETE_INTRO;
        } else {
            $products_num = \common\models\ProductsAttributes::find()->where(['options_id' => $products_options_id])->count();

            $values_num = \common\models\ProductsOptions2ProductsOptionsValues::find()->where(['products_options_id' => $products_options_id])->count();

            $obj = \common\models\ProductsOptions::findOne(['products_options_id' => $products_options_id, 'language_id' => $languages_id]);
            $process_item_name = $obj->products_options_name ?? null;
            $TEXT_INFO_HEADING = TEXT_INFO_HEADING_DELETE_ITEM;
            $TEXT_INTRO = TEXT_DELETE_ITEM_INTRO;
        }

        echo tep_draw_form('option_delete', 'catalog/product', \common\helpers\Output::get_all_get_params(array('action')) . 'action=delete', 'post', 'id="option_delete" onSubmit="return deleteOption();"');
        echo '<div class="or_box_head">' . $TEXT_INFO_HEADING . '</div>';
        echo '<div class="col_desc">' . $TEXT_INTRO . '<br><br><b>' . $process_item_name . '</div>';
        if ($values_num > 0 OR $products_num > 0) {
            if ($cell_type == 'suboption') {
                $notice = sprintf(TEXT_OPTION_VALUE_DELETE_NOTICE, $products_num);
            } else {
                ob_start();
                printf(TEXT_OPTION_DELETE_NOTICE, $products_num, $values_num);
                $notice = ob_get_clean();
            }
            echo '<div class="col_desc">' . $notice . '</div>';
        }
        ?>
        <div class="btn-toolbar btn-toolbar-order">
        <?php
        echo '<input type="hidden" name="cell_type" value="' . $cell_type . '"  >';
        echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button>';
        echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

        echo tep_draw_hidden_field('products_options_id', $products_options_id);
        ?>
        </div>
        </form>
            <?php
    }

    function actionOptiondelete() {
        $this->layout = FALSE;

        $products_options_id = (int)Yii::$app->request->post('products_options_id');
        $cell_type = Yii::$app->request->post('cell_type');

        $transaction = Yii::$app->db->beginTransaction();
        try {
            //TODO rebuild inventory
            if ($cell_type == 'option') {
                $obj = \common\models\ProductsOptions::findOne(['products_options_id' => $products_options_id]);
                if (!empty($obj)) {
                    $attrList = $obj->productsAttributes;

                    foreach ($attrList as $attr) {
                        if (USE_MARKET_PRICES) {
                            foreach ($attr->productsAttributesPrices as $price) {
                                $price->delete();
                            }
                        }
                        $attr->delete();
                    }

                    foreach ($obj->productsOptionsValues as $item) {
                        $item->delete();
                    }
                    foreach ($obj->options2Values as $item) {
                        $item->delete();
                    }
                    $obj->delete();
                }

            // === suboption ===================================================
            } else {
                $obj = \common\models\ProductsOptionsValues::findOne(['products_options_values_id' => $products_options_id]);
                if (!empty($obj)) {

                    foreach ($obj->productsAttributes as $attr) {
                        if (USE_MARKET_PRICES) {
                            foreach ($attr->productsAttributesPrices as $price) {
                                $price->delete();
                            }
                        }
                        $attr->delete();
                    }

                    foreach ($obj->values2Options as $item) {
                        $item->delete();
                    }
                    $obj->delete();
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function actionSortOrder() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (isset($_POST['sort_suboption'])) {
                $moved_id = (int) $_POST['sort_suboption'];
                $suboption = \Yii::$app->request->post('suboption');
                $ref_array = is_array($suboption) ? array_map('intval', $suboption) : [];
                if ($moved_id && in_array($moved_id, $ref_array)) {
                    $obj = \common\models\ProductsOptions2ProductsOptionsValues::findOne(['products_options_values_id' => $moved_id]);
                    $option_id = $obj->products_options_id ?? null;
                    // {{ normalize
                    $list = \common\models\ProductsOptionsValues::find()
                            ->alias('pv')
                            ->select('pv.products_options_values_id, pv.products_options_values_sort_order')
                            ->innerJoinWith(['values2Options po2v' =>
                                function ($query) use ($option_id) {
                                    return $query->andOnCondition(['po2v.products_options_id' => $option_id]);
                                }
                                    ], false)
                            ->where(['language_id' => $languages_id])
                            ->orderBy('pv.products_options_values_sort_order, pv.products_options_values_name')
                            ->all();

                    $order_counter = 0;
                    foreach ($list as $item) {
                        $order_counter++;
                        \common\models\ProductsOptionsValues::updateAll(['products_options_values_sort_order' => $order_counter], ['products_options_values_id' => $item->products_options_values_id]);
                    }
                    // }} normalize
                    if (is_array($ref_array) && count($ref_array) > 0) {
                        $list = \common\models\ProductsOptionsValues::find()
                                ->select('products_options_values_id, products_options_values_sort_order')
                                ->where(['products_options_values_id' => $ref_array, 'language_id' => $languages_id])
                                ->orderBy('products_options_values_sort_order')
                                ->all();
                        $ref_so = [];
                        foreach ($list as $item) {
                            $ref_so[] = (int) $item->products_options_values_sort_order;
                        }

                        foreach ($ref_array as $_idx => $id) {
                            \common\models\ProductsOptionsValues::updateAll(['products_options_values_sort_order' => $ref_so[$_idx]], ['products_options_values_id' => $id]);
                        }
                    }
                }
            // === Option =====================================================================
            } elseif ($_POST['sort_option']) {

                $moved_id = (int) $_POST['sort_option'];
                $option = \Yii::$app->request->post('option');
                $ref_array = is_array($option) ? array_map('intval', $option) : [];
                if ($moved_id && in_array($moved_id, $ref_array)) {
                    // {{ normalize
                    $list = \common\models\ProductsOptions::find()
                            ->select('products_options_id, products_options_sort_order')
                            ->where(['language_id' => $languages_id])
                            ->orderBy('products_options_sort_order, products_options_name')
                            ->all();
                    $order_counter = 0;
                    foreach ($list as $item) {
                        $order_counter++;
                        \common\models\ProductsOptions::updateAll(['products_options_sort_order' => $order_counter], ['products_options_id' => $item->products_options_id]);
                    }
                    // }} normalize
                    if (is_array($ref_array) && count($ref_array) > 0) {
                        $list = \common\models\ProductsOptions::find()
                                ->select('products_options_id, products_options_sort_order')
                                ->where(['products_options_id' => $ref_array, 'language_id' => $languages_id])
                                ->orderBy('products_options_sort_order')
                                ->all();
                        $ref_so = [];
                        foreach ($list as $item) {
                            $ref_so[] = (int) $item->products_options_sort_order;
                        }

                        foreach ($ref_array as $_idx => $id) {
                            \common\models\ProductsOptions::updateAll(['products_options_sort_order' => $ref_so[$_idx]], ['products_options_id' => $id]);
                        }
                    }
                }
            }


            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

}
