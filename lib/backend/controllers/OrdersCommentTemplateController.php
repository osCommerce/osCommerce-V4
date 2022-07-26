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

use common\models\AccessLevels;
use common\models\Admin;
use common\models\OrdersCommentTemplate;
use common\models\OrdersCommentTemplateText;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;


class OrdersCommentTemplateController extends Sceleton  {
    
    public $acl = ['TEXT_SETTINGS', 'BOX_LOCALIZATION_ORDERS_STATUS', 'BOX_ORDERS_COMMENT_TEMPLATE'];
    
    public function actionIndex() {
        $this->selectedMenu = array('settings', 'status', 'orders-comment-template');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders-comment-template/index'), 'title' => HEADING_TITLE);

        $this->view->headingTitle = HEADING_TITLE;

        $this->topButtons[] = '<a href="'.Url::to(['orders-comment-template/edit']).'" class="btn btn-primary">'.TEXT_BTN_NEW_ORDERS_COMMENT_TEMPLATE.'</a>';

        $this->view->listingTable = array(
            array(
                'title' => TEXT_COMMENT_TEMPLATE_NAME,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 0
            ),
        );

        return $this->render('index' );
    }

    public function actionList() {
        \common\helpers\Translation::init('admin/orders-comment-template');

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $cID = Yii::$app->request->get('cID', 0);

        $current_page_number = ($start / $length) + 1;

        $responseList = [];

        $get_data_r = tep_db_query(
            "SELECT ct.comment_template_id, ct.sort_order, ct.status, ".
            " ctt.name ".
            "FROM orders_comment_template ct ".
            " INNER JOIN orders_comment_template_text ctt ON ctt.comment_template_id=ct.comment_template_id AND ctt.language_id='".(int)\Yii::$app->settings->get('languages_id')."' ".
            "WHERE 1 ".
            "ORDER BY ct.sort_order, ctt.name"
        );
        $_query_numrows = tep_db_num_rows($get_data_r);
        if ( $_query_numrows>0 ) {
            while( $data = tep_db_fetch_array($get_data_r) ) {
                $responseList[] = array(
                    '<div class="handle_cat_list"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name cat_name_attr cat_no_folder">' .
                    $data['name'] . tep_draw_hidden_field('id', $data['comment_template_id'], 'class="cell_identify"'). '<input class="cell_type" type="hidden" value="top">' .
                    '</div></div>',
                    ('<input type="checkbox" value="' . $data['comment_template_id'] . '" name="status" class="check_on_off"' . ($data['status'] == 1 ? ' checked="checked"' : '') . '>'),
                );
            }
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'draw' => $draw,
            'recordsTotal' => $_query_numrows,
            'recordsFiltered' => $_query_numrows,
            'data' => $responseList,
        ];
    }

    public function actionRowActions() {
        \common\helpers\Translation::init('admin/orders-comment-template');
        $languages_id = \Yii::$app->settings->get('languages_id');
        $this->layout = false;

        $comment_template_id = Yii::$app->request->post('id', 0);

        $model = OrdersCommentTemplate::findOne($comment_template_id);
        if ($model) {
            $_id = $model->comment_template_id;
            $description = $model->getTexts()->where(['language_id'=>$languages_id])->one();

            echo '<div class="or_box_head">' . $description->name . '</div>';

            echo '<div class="btn-toolbar btn-toolbar-order">';
            //echo '<button class="btn btn-edit btn-no-margin" onclick="itemEdit('.$_id.', \''.str_replace(['"',"'",'\\'],['&quot;',"\'",'\\\\'], $description->name).'\')">' . IMAGE_EDIT . '</button>';
            echo Html::a(IMAGE_EDIT, Yii::$app->urlManager->createUrl(['orders-comment-template/edit', 'id' => $_id]), ['class' => 'btn btn-edit btn-no-margin']);
            echo '<button class="btn btn-delete" onclick="itemDelete('.$_id.', \''.str_replace(['"',"'",'\\'],['&quot;',"\'",'\\\\'], $description->name).'\')">' . IMAGE_DELETE . '</button>';
            echo '</div>';
        }
    }
    
    public function actionEdit() {

        \common\helpers\Translation::init('admin/orders-comment-template');
        $languages_id = \Yii::$app->settings->get('languages_id');
        $template_id = Yii::$app->request->get('id', 0);

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#form_management_data form\').trigger(\'submit\')">' . IMAGE_UPDATE . '</span>';

        $model = OrdersCommentTemplate::findOne($template_id);
        if ( !$model ) {
            $model = new OrdersCommentTemplate();
            $model->loadDefaultValues();
        }
        $description = $model->getTexts()->where(['language_id'=>$languages_id])->one();
        $descriptions = [];
        foreach ($model->texts as $_desc){
            $descriptions[$_desc->language_id] = $_desc;
        }
        foreach (\common\helpers\Language::get_languages() as $_lang){
            if ( !isset($descriptions[$_lang['id']]) ) {
                $descriptions[$_lang['id']] = new OrdersCommentTemplateText();
                $descriptions[$_lang['id']]->loadDefaultValues();
                $descriptions[$_lang['id']]->language_id = $_lang['id'];
            }
        }

        if ( Yii::$app->request->isPost ) {
            // update stuff
            $posted_template = Yii::$app->request->post('template');
            if (!isset($posted_template['status'])) $posted_template['status'] = 0;
            if ( isset($posted_template['visibility']) && is_array($posted_template['visibility']) ) {
                $posted_template['visibility'] = ',' . implode(',', $posted_template['visibility']) . ',';
            }else{
                $posted_template['visibility'] = '';
            }
            if ( isset($posted_template['hide_for_platforms']) && is_array($posted_template['hide_for_platforms']) ) {
                $posted_template['hide_for_platforms'] = ',' . implode(',', $posted_template['hide_for_platforms']) . ',';
            }else{
                $posted_template['hide_for_platforms'] = '';
            }
            if ( isset($posted_template['hide_from_admin']) && is_array($posted_template['hide_from_admin']) ) {
                $posted_template['hide_from_admin'] = ',' . implode(',', $posted_template['hide_from_admin']) . ',';
            }else{
                $posted_template['hide_from_admin'] = '';
            }
            if ( isset($posted_template['show_for_admin_group']) && is_array($posted_template['show_for_admin_group']) ) {
                $posted_template['show_for_admin_group'] = ',' . implode(',', $posted_template['show_for_admin_group']) . ',';
            }else{
                $posted_template['show_for_admin_group'] = '';
            }

            $posted_descriptions = Yii::$app->request->post('description',[]);
            $model->setAttributes($posted_template,false);
            if ( $model->isNewRecord ) {
                $model->sort_order = intval(OrdersCommentTemplate::find()->max('sort_order'))+1;
                $model->date_added = new Expression('NOW()');
            }else{
                $model->date_modified = new Expression('NOW()');
            }
            $model->save();
            $model->refresh();
            foreach ($posted_descriptions as $lang_id=>$posted_description){
                $descriptions[$lang_id]->comment_template_id = $model->comment_template_id;
                $descriptions[$lang_id]->language_id = $lang_id;
                $descriptions[$lang_id]->setAttributes($posted_description,false);
                $descriptions[$lang_id]->save();
            }

            $this->redirect(['orders-comment-template/edit','id'=>$model->comment_template_id]);
        }

        $this->navigation[] = array(
            'link' => Yii::$app->urlManager->createUrl('orders-comment-template/index'),
            'title' => ($model->isNewRecord? TEXT_HEADING_NEW_ORDERS_COMMENT_TEMPLATE:TEXT_HEADING_EDIT_ORDERS_COMMENT_TEMPLATE.' "'.($description->name ?? null).'"')
        );
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
        }

        $platformsVariants = ArrayHelper::map(\common\classes\platform::getList(true, true),'id','text');
        $adminMemberVariants = ArrayHelper::map(
            Admin::find()
                ->select(['admin_id','admin_firstname', 'admin_lastname'])
                ->orderBy(['admin_firstname' => SORT_ASC, 'admin_lastname'=>SORT_ASC])->all(),
            'admin_id',function($obj){ return trim(strval($obj->admin_firstname).' '.strval($obj->admin_lastname)); });

        $accessLevelsVariants = ArrayHelper::map(
            AccessLevels::find()->select(['access_levels_id','access_levels_name'])->orderBy(['access_levels_name'=>SORT_ASC])->all(),
        'access_levels_id', 'access_levels_name'
        );
        return $this->render('edit',[
            'model' => $model,
            'descriptions' => $descriptions,
            'languages' => $languages,
            'platformsVariants' => $platformsVariants,
            'adminMemberVariants' => $adminMemberVariants,
            'accessLevelsVariants' => $accessLevelsVariants,
        ]);
    }

    public function actionDelete() {
        $this->layout = false;

        \common\helpers\Translation::init('admin/orders-comment-template');

        $comment_template_id =  Yii::$app->request->post('id', 0);

        $model = OrdersCommentTemplate::findOne($comment_template_id);
        if($model) {
            $model->delete();
            echo 'reset';
        }
    }

    public function actionSwitchStatus()
    {
        $this->layout = false;
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');

        $model = \common\models\OrdersCommentTemplate::findOne(['comment_template_id' => (int)$id ]);

        $model->status = ($status == 'true' ? 1 : 0);
        $model->save(false);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'status' => 'ok',
        ];
    }

    public function actionSortOrder()
    {
        $this->layout = false;
        $moved_id = (int)$_POST['sort_top'];
        $ref_array = (isset($_POST['top']) && is_array($_POST['top']))?array_map('intval',$_POST['top']):array();
        if ( $moved_id && in_array($moved_id, $ref_array) ) {
            // {{ normalize
            $order_counter = 0;
            $order_list_r = tep_db_query(
                "SELECT ct.comment_template_id ".
                "FROM orders_comment_template ct ".
                " INNER JOIN orders_comment_template_text ctt ON ctt.comment_template_id=ct.comment_template_id AND ctt.language_id='".(int)\Yii::$app->settings->get('languages_id')."' ".
                "WHERE 1 ".
                "ORDER BY ct.sort_order, ctt.name"
            );
            while( $order_list = tep_db_fetch_array($order_list_r) ){
                $order_counter++;
                tep_db_query("UPDATE orders_comment_template SET sort_order='{$order_counter}' WHERE comment_template_id='{$order_list['comment_template_id']}' ");
            }
            // }} normalize
            $get_current_order_r = tep_db_query(
                "SELECT comment_template_id, sort_order ".
                "FROM orders_comment_template ".
                "WHERE comment_template_id IN('".implode("','",$ref_array)."') ".
                "ORDER BY sort_order"
            );
            $ref_ids = array();
            $ref_so = array();
            while($_current_order = tep_db_fetch_array($get_current_order_r)){
                $ref_ids[] = (int)$_current_order['comment_template_id'];
                $ref_so[] = (int)$_current_order['sort_order'];
            }

            foreach( $ref_array as $_idx=>$id ) {
                tep_db_query("UPDATE orders_comment_template SET sort_order='{$ref_so[$_idx]}' WHERE comment_template_id='{$id}' ");
            }
        }

        return 'ok';
    }
}
