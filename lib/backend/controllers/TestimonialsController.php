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
use common\extensions\Testimonials\Testimonials;

class TestimonialsController extends Sceleton {

    public $acl = ['BOX_HEADING_MARKETING_TOOLS', 'BOX_MARKETING_TESTIMONIALS'];

    public function __construct($id, $module) {
        \common\helpers\Translation::init('testimonials');
        return parent::__construct($id, $module);
    }
    
    public function beforeAction($action)
    {
        if (false === \common\helpers\Acl::checkExtensionAllowed('Testimonials', 'allowed')) {
            $this->redirect(array('/'));
            return false;
        }
        return parent::beforeAction($action);
    }

    public function actionIndex() {

        $this->selectedMenu = array('marketing', 'testimonials');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('testimonials/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;
        
        Testimonials::initConfiguration();
        
        $platform_id = Yii::$app->request->get('platform_id', 0);

        $platforms = \common\classes\platform::getList(false);

        $this->view->tabList = [
            array(
                'title' => TABLE_HEADING_AUTHOR,
                'not_important' => 0,
            ),
            array(
                'title' => TABLE_HEADING_URL,
                'not_important' => 0,
            ),
             array(
                'title' => TABLE_HEADING_DESCRIPTION,
                'not_important' => 3,
            ),
            array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 3,
            ),
        ];
        $messages = Yii::$app->session->getAllFlashes();
        Yii::$app->session->removeAllFlashes();
        $this->view->filters = new \stdClass();
        $this->view->filters->row = Yii::$app->request->get('row', 0);
        return $this->render('index', [
                    'platforms' => $platforms,
                    'first_platform_id' => ($platform_id?$platform_id:\common\classes\platform::firstId()),
                    'default_platform_id' => \common\classes\platform::defaultId(),
                    'isMultiPlatforms' => \common\classes\platform::isMulti(),
                    'messages' => $messages
        ]);
    }

    public function actionList() {
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        
        $platform_id = Yii::$app->request->get('platform_id');

        $responseList = [];
        if ($length == -1)
            $length = 10000;
        $recordsTotal = 0;

        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " and testimonials_name like '%" . $keywords . "%' ";
        } else {
            $search_condition = " ";
        }

        if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
            switch ($_GET['order'][0]['column']) {
                case 0:
                    $orderBy = "testimonials_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
                    break;
                default:
                    $orderBy = "testimonials_name";
                    break;
            }
        } else {
            $orderBy = "date_added desc";
        }

        $current_page_number = ( $start / $length ) + 1;
        $testimonialsRaw = Testimonials::getQueryRaw($platform_id, false) . $search_condition . ' order by '. $orderBy;//echo $testimonialsRaw;
        $_split = new \splitPageResults($current_page_number, $length, $testimonialsRaw, $recordsTotal, 'testimonials_id');
        $testimonialsQuery = tep_db_query($testimonialsRaw);
        while ($testimonial = tep_db_fetch_array($testimonialsQuery)) {
            
            $status = '';
            if( (int) $testimonial['status'] > 0 ){
                    $status .= '<div class="ls-status-rev"><div class="st-w st-wa">' . TEXT_APPROVED . '</div>';
                } else {
                    $status .= '<div class="ls-status-rev"><div class="st-w">' . TEXT_NEW . '</div>';
            }
            $status .= '<input name="active" type="checkbox" class="check_on_off" ' . ($testimonial['status'] ? " checked" : "") . '>';
            
            $responseList[] = array(
                iconv('UTF-8', 'UTF-8', ($testimonial['testimonials_anonymus'] ? TEXT_ANONIMUS : $testimonial['testimonials_name'] )) . '<input class="cell_identify" type="hidden" value="' . $testimonial['testimonials_id'] . '">',
                $testimonial['testimonials_url_title'],
                iconv('UTF-8', 'UTF-8', substr(stripslashes($testimonial['testimonials_html_text']), 0, 25). ' [' . TEXT_MORE . ']'),
                $status,
            );
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $responseList,
        ];
        echo json_encode($response);
    }

    public function actionPreview() {
        $this->layout = false;
        $testimonials_id = (int) Yii::$app->request->get('tID', 0);
        $row = (int) Yii::$app->request->get('row', 0);
        
        $testimonial = Testimonials::findOne($testimonials_id);
        if (is_array($testimonial)) {
          $singlePlatformLink = '';
          if (!empty($testimonial['status']) ) {
            $p = \yii\helpers\ArrayHelper::index(\common\classes\platform::getList(false), 'id') ;
            if (isset($p[$testimonial['platform_id']]) ) {
              $frontend = $p[$testimonial['platform_id']];
            } else {
              $frontend = array_shift($p);
            }
            $singlePlatformLink = 'http://' . $frontend['platform_url'] . '/testimonials?testimonials_id=' . $testimonial['testimonials_id'];
          }
                

            echo '<div class="or_box_head">' . ($testimonial['testimonials_anonymus'] ? TEXT_ANONIMUS : $testimonial['testimonials_name'] ) . '</div>';
            echo '<div class="row_or"><div>'. TEXT_SUBJECT . '</div><div>' . (empty($testimonial['testimonials_title'])? TEXT_UNKNOWN :$testimonial['testimonials_title']) .'</div></div>';
            echo '<div class="row_or"><div>'. TEXT_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($testimonial['date_added']) .'</div></div>';
            echo '<div class="row_or"><div>'. TABLE_HEADING_DESCRIPTION . ':</div><div>' . strip_tags($testimonial['testimonials_html_text']) .'</div></div>';
            if (intval($testimonial['testimonials_rating'])>0){
                echo '<div class="row_or"><div>'. TEXT_INFO_REVIEW_RATING . '</div><div><img src="'.Yii::$app->view->theme->baseUrl.'/img/reviews/stars_' . (int)$testimonial['testimonials_rating'] . '.png" /></div></div>';
            }
            echo '<div class="btn-toolbar btn-toolbar-order">';
            echo '<a class="btn btn-edit btn-no-margin" href="' . \yii\helpers\Url::to(['testimonials/edit', 'tID' => $testimonial['testimonials_id'], 'row' => $row]) . '">' . IMAGE_EDIT . '</a>';
            echo '<button class="btn btn-delete" onclick="testimonialDelete(\'' . $testimonial['testimonials_id'] . '\')">' . IMAGE_DELETE . '</button>';
            if (!empty($singlePlatformLink)) {
              echo '<a class="btn btn-edit btn-no-margin" target="_blank" href="' . $singlePlatformLink . '">' . IMAGE_PREVIEW . '</a>';
            }
            echo '</div>';
        }
    }


    public function actionEdit() {
        \common\helpers\Translation::init('admin/adminfiles');

        $this->selectedMenu = array('marketing', 'testimonials');
        $this->navigation[] = array('link' => \Yii::$app->urlManager->createUrl('testimonials/index'), 'title' => HEADING_TITLE);
        $this->view->headingTitle = HEADING_TITLE;

        $testimonials_id = (int) Yii::$app->request->get('tID', 0);
        $row = (int) Yii::$app->request->get('row', 0);

        $testimonial = Testimonials::findOne($testimonials_id);        
       
        $messages = Yii::$app->session->getAllFlashes();
        Yii::$app->session->removeAllFlashes();
        
        return $this->render('edit', [
                    'testimonial' => $testimonial,
                    'messages' => $messages,
                    'row' => $row
        ]);
    }

    public function actionSave() {

        $testimonials_id = (int) Yii::$app->request->post('testimonials_id');
        $testimonials_title =  Yii::$app->request->post('testimonials_title', '');
        $testimonials_name =  Yii::$app->request->post('testimonials_name');
        $testimonials_url =  Yii::$app->request->post('testimonials_url','');
        $testimonials_url_title =  Yii::$app->request->post('testimonials_url_title','');
        $testimonials_html_text =  Yii::$app->request->post('testimonials_html_text');
        $testimonials_answer =  Yii::$app->request->post('testimonials_answer', '');
        $testimonials_rating = Yii::$app->request->post('testimonials_rating', 0);
        $status = Yii::$app->request->post('status');
        $row = Yii::$app->request->post('row',0);
        
        if ($testimonials_id){
            $sql_data_array = [
                    'testimonials_title' => $testimonials_title,
                    'testimonials_name' => $testimonials_name,
                    'testimonials_url' => $testimonials_url,
                    'testimonials_url_title' => $testimonials_url_title,
                    'testimonials_html_text' => $testimonials_html_text,
                    'testimonials_answer' => $testimonials_answer,
                    'testimonials_rating' => $testimonials_rating,
                    'status' => ($status == 'on'?1:0)
                ];
            tep_db_perform(TABLE_CUSTOMER_TESTIMONIALS, $sql_data_array, 'update', "testimonials_id = '" . (int) $testimonials_id . "'");
            Yii::$app->session->setFlash('success', TEXT_MESSEAGE_SUCCESS);
        } else {
            return $this->redirect(['testimonials/']);
        }
        
        return $this->redirect(['testimonials/edit', 'tID' => $testimonials_id, 'row' =>$row]);
    }

    public function actionDelete() {
        $testimonials_id = (int) Yii::$app->request->post('tID');
        tep_db_query("delete from " . TABLE_CUSTOMER_TESTIMONIALS . " where testimonials_id = '" . (int) $testimonials_id . "'");
        echo 'ok';
    }

    public function actionChange() {
        $testimonials_id = (int) Yii::$app->request->post('tID');
        $status = Yii::$app->request->post('status', 'false') == 'true' ? 1 : 0;
        $message = '';
        $error = true;
        if ( $testimonials_id) {
            tep_db_query("update " . TABLE_CUSTOMER_TESTIMONIALS . " set status='" . (int) $status . "' where testimonials_id = '" . (int) $testimonials_id . "'");
            $message = TEXT_MESSEAGE_SUCCESS;
            $error = false;
        } else {
            $message = TEXT_CHECK_SOCIAL_MODULE_SETTINGS;
        }

        return $this->renderAjax('message', ['type' => ($error ? 'error' : 'success'), 'message' => $message, 'title' => TEXT_EDIT_TESTIMONIAL]);
    }

}
