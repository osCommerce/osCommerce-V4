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

class ReviewsController extends Sceleton {

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_REVIEWS'];
    
    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {

        $this->selectedMenu = array('catalog', 'reviews');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('reviews/index'), 'title' => BOX_CATALOG_REVIEWS);
        $this->view->headingTitle = BOX_CATALOG_REVIEWS;
        $this->view->reviewsTable = array(
            array(
                'title' => '<input type="checkbox" class="uniform">',
                'not_important' => 2
            ),
            array(
                'title' => TABLE_HEADING_PRODUCTS,
                'not_important' => 1
            ),
            array(
                'title' => TEXT_REVIEW,
                'not_important' => 1
            ),
            array(
                'title' => TABLE_HEADING_RATING,
                'not_important' => 1
            ),
            array(
                'title' => TEXT_INFO_DATE_ADDED1,
                'not_important' => 0
            ),            
            array(
                'title' => TEXT_CUSTOMERS,
                'not_important' => 1
            ),
            array(
                'title' => TEXT_STATUS,
                'not_important' => 1
            ),
            
            /* array(
               'title' => 'Action',
               'not_important' => 0
               ), */
        );

        $this->view->filters = new \stdClass();
        
        $status = [
            [
                'name' => TEXT_ALL,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_NEW,
                'value' => 'new',
                'selected' => '',
            ],
            [
                'name' => TEXT_APPROVED,
                'value' => 'approved',
                'selected' => '',
            ],
            [
                'name' => TEXT_DECLINED,
                'value' => 'declined',
                'selected' => '',
            ],
        ];
        foreach ($status as $key => $value) {
            if (isset($_GET['status']) && $value['value'] == $_GET['status']) {
                $status[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->status = $status;

        $name = '';
        if (isset($_GET['name'])) {
            $name = $_GET['name'];
        }
        $this->view->filters->name = $name;
        
        $product = '';
        if (isset($_GET['product'])) {
            $product = $_GET['product'];
        }
        $this->view->filters->product = $product;
        
        $from = '';
        if (isset($_GET['from'])) {
            $from = $_GET['from'];
        }
        $this->view->filters->from = $from;
        
        $to = '';
        if (isset($_GET['to'])) {
            $to = $_GET['to'];
        }
        $this->view->filters->to = $to;
        
        $this->view->filters->row = (int)Yii::$app->request->get('row', 0);
        $cID = (int)Yii::$app->request->get('cID', 0);
        if ($cID == 0) {
            $cID = '';
        }
        $this->view->filters->cID = $cID;
        
        return $this->render('index');

    }

    public function actionList() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $platform_id = \common\classes\platform::currentId();
        $draw   = Yii::$app->request->get( 'draw', 1 );
        $start  = Yii::$app->request->get( 'start', 0 );
        $length = Yii::$app->request->get( 'length', 10 );

        $responseList = array();
        if( $length == -1 ) $length = 10000;
        $query_numrows = 0;

        $search_condition = " where r.reviews_id=rd.reviews_id /*and rd.languages_id = '$languages_id'*/ and r.products_id = p.products_id  and p.language_id = '$languages_id' and p.platform_id='" . intval(\common\classes\platform::defaultId()) . "'";

        //TODO search
        if( isset( $_GET['search']['value'] ) && tep_not_null( $_GET['search']['value'] ) ) {
            $keywords         = tep_db_input( tep_db_prepare_input( $_GET['search']['value'] ) );
            $search_condition .= " and p.products_name like '%" . $keywords . "%' ";
        }
        

        if( isset( $_GET['order'][0]['column'] ) && $_GET['order'][0]['dir'] ) {
            switch( $_GET['order'][0]['column'] ) {
                case 0:
                    $orderBy = "r.date_added " . tep_db_prepare_input( $_GET['order'][0]['dir'] );
                    break;
                default:
                    $orderBy = "r.date_added DESC";
                    break;
            }
        } else {
            $orderBy = "r.date_added DESC";
        }

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);
        
        $filter = '';
        
        if (tep_not_null($output['status'])) {
            switch ($output['status']) {
                    case 'new':
                        $filter .= " and r.new = '1'";
                        break;
                    case 'approved':
                        $filter .= " and r.status = '1'";
                        break;
                    case 'declined':
                        $filter .= " and r.status = '0'";
                        break;
                }
        }
        if (tep_not_null($output['name'])) {
          $filter .= " and r.customers_name like '%".tep_db_input($output['name'])."%'";
        }
        if (tep_not_null($output['product'])) {
          $filter .= " and p.products_name like '%".tep_db_input($output['product'])."%'";
        }
        if (tep_not_null($output['from'])) {
            $from = tep_db_prepare_input($output['from']);
            $filter .= " and to_days(r.date_added) >= to_days('" . \common\helpers\Date::prepareInputDate($from) . "')";
        }
        if (tep_not_null($output['to'])) {
            $to = tep_db_prepare_input($output['to']);
            $filter .= " and to_days(r.date_added) <= to_days('" . \common\helpers\Date::prepareInputDate($to) . "')";
        }
        if (tep_not_null($output['cID'])) {
          $filter .= " and r.customers_id = '" . $output['cID'] . "'";
        }
        
        
        $reviews_query_raw = "
            select r.reviews_id, r.products_id, r.date_added, r.last_modified, r.reviews_rating, r.status, r.new, p.products_name, rd.reviews_text, p.platform_id
            from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, ".TABLE_PRODUCTS_DESCRIPTION." p
            $search_condition $filter
            and p.platform_id = '" . (int) $platform_id . "'
            order by $orderBy ";

        $current_page_number = ( $start / $length ) + 1;
        $_split              = new \splitPageResults( $current_page_number, $length, $reviews_query_raw, $query_numrows, 'r.reviews_id' );
        $reviews_query     = tep_db_query( $reviews_query_raw );
        while( $reviews = tep_db_fetch_array( $reviews_query ) ) {
            $reviews_text_query = tep_db_query("select r.reviews_read, r.customers_name, r.customers_id, length(rd.reviews_text) as reviews_text_size from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$reviews['reviews_id'] . "' and r.reviews_id = rd.reviews_id");
            $reviews_text = tep_db_fetch_array($reviews_text_query);

            $products_image_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
            $products_image = tep_db_fetch_array($products_image_query);

           // $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
           // $products_name = tep_db_fetch_array($products_name_query);

            $reviews_average_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$reviews['products_id'] . "'");
            $reviews_average = tep_db_fetch_array($reviews_average_query);

            $review_info = array_merge($reviews_text, $reviews_average);
            $rInfo_array = array_merge($reviews, $review_info, $products_image);
            $rInfo = new \objectInfo($rInfo_array);

            $status = '';
            if( (int) $rInfo->new > 0 ){
                $status .= '<div class="ls-status-rev"><div class="st-w">' . TEXT_NEW . '</div>';
            } else {
                if( (int) $rInfo->status > 0 ){
                    $status .= '<div class="ls-status-rev"><div class="st-w st-wa">' . TEXT_APPROVED . '</div>';
                } else {
                    $status .= '<div class="ls-status-rev"><div class="st-w st-wad">' . TEXT_DECLINED . '</div>';
                }
            }
            if( (int) $rInfo->status > 0 ){
                $status .= '<input type="checkbox" name="check_status" class="check_on_off" value="' . $rInfo->reviews_id . '" checked /></div>';
            } else {
                $status .= '<input type="checkbox" name="check_status" class="check_on_off" value="' . $rInfo->reviews_id . '" /></div>';
            }

            $short_desc = $rInfo->reviews_text;
            if (strlen($short_desc) > 60) {
                $short_desc = substr($short_desc, 0, 66) . '...';
            }
            $short_desc = mb_convert_encoding($short_desc, 'UTF-8', 'UTF-8');
            			
            $responseList[] = array(
                '<input type="checkbox" class="uniform">' . '<input class="cell_identify" type="hidden" value="' . $rInfo->reviews_id . '">',
                '<div class="ls-name-rev ord-name click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['reviews/edit', 'reviews_id' => $rInfo->reviews_id]) . '"><a href="'.Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $rInfo->products_id]).'" target="_blank">'.$rInfo->products_name .'</a>'.
                '<input class="cell_identify" type="hidden" value="' . $rInfo->reviews_id . '"></div>',
                '<div class="ls-review-rev click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['reviews/edit', 'reviews_id' => $rInfo->reviews_id]) . '">' . $short_desc . '<div class="ord-total-info"><div class="ord-box-img"></div><div>' . $rInfo->reviews_text . '</div></div></div>',     
                '<div class="click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['reviews/edit', 'reviews_id' => $rInfo->reviews_id]) . '"><img src="'.Yii::$app->view->theme->baseUrl.'/img/reviews/stars_' . $rInfo->reviews_rating . '.png" /></div>',
                '<div class="ls-date-rev click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['reviews/edit', 'reviews_id' => $rInfo->reviews_id]) . '"><span>'.\common\helpers\Date::datetime_short($rInfo->date_added).'</span></div>',
                '<div class="ls-name-cus-rev click_double" data-click-double="' . \Yii::$app->urlManager->createUrl(['reviews/edit', 'reviews_id' => $rInfo->reviews_id]) . '"><a href="' . \Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $rInfo->customers_id]) . '">' . $rInfo->customers_name.'</a></div>',
                $status,
                //st-wa - add class approve
                //st-wad - add class declined
                //'<div class="ls-status-rev"><div class="st-w">New</div><input type="checkbox" class="check_on_off" checked /></div>',
            );
        }

        $response = array(
            'draw'            => $draw,
            'recordsTotal'    => $query_numrows,
            'recordsFiltered' => $query_numrows,
            'data'            => $responseList
        );
        echo json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    public function actionItempreedit()
    {
        $this->layout = FALSE;

        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/reviews');

        $item_id   = (int) Yii::$app->request->post( 'item_id' );

        $reviews_query = tep_db_query("select r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$item_id . "' and r.reviews_id = rd.reviews_id");
        $reviews = tep_db_fetch_array($reviews_query);
        if (!is_array($reviews)) {
            return;
        }

        $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
        $products = tep_db_fetch_array($products_query);
        if (!is_array($products)) {
            return;
        }

        $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
        $products_name = tep_db_fetch_array($products_name_query);
        if (!is_array($products_name)) {
            return;
        }

        $reviews_text_query = tep_db_query("select r.reviews_read, r.customers_name, length(rd.reviews_text) as reviews_text_size from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$reviews['reviews_id'] . "' and r.reviews_id = rd.reviews_id");
        $reviews_text = tep_db_fetch_array($reviews_text_query);
        if (!is_array($reviews_text)) {
            return;
        }

        $reviews_average_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$reviews['products_id'] . "'");
        $reviews_average = tep_db_fetch_array($reviews_average_query);
        if (!is_array($reviews_average)) {
            return;
        }

        $rInfo_array = array_merge($reviews, $products, $products_name, $reviews_text, $reviews_average);


        ?>
<div class="row_or_img"><?php echo  \common\helpers\Image::info_image($rInfo_array['products_image'], $rInfo_array['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></div>
        <div class="or_box_head or_box_head_no_margin"><?php echo $rInfo_array['products_name'];?></div>
        <div class="row_or"><?php echo '<div>'. TEXT_INFO_DATE_ADDED . '</div><div>' . \common\helpers\Date::date_short($reviews['date_added'])  ; ?></div></div>
        
        <div class="row_or"><?php echo '<div>' . TEXT_INFO_REVIEW_AUTHOR . '</div><div>' . $reviews['customers_name']; ?></div></div>
        <div class="row_or"><?php echo '<div>' . TEXT_INFO_REVIEW_RATING . '</div><div><img src="'.Yii::$app->view->theme->baseUrl.'/img/reviews/stars_' . $rInfo_array['reviews_rating'] . '.png" /></div></div>';?>
        <div class="row_or"><?php echo '<div>' . TEXT_INFO_REVIEW_READ . '</div><div>' . $rInfo_array['reviews_read']; ?></div></div>
        <div class="row_or"><?php echo '<div>' . TEXT_INFO_REVIEW_SIZE . '</div><div>' . $rInfo_array['reviews_text_size'] . ' bytes'; ?></div></div>
        <div class="row_or"><?php echo '<div>' . TEXT_INFO_PRODUCTS_AVERAGE_RATING . '</div><div>' . number_format($rInfo_array['average_rating'], 2) . '%'; ?></div></div>
        <div class="btn-toolbar btn-toolbar-order">
           <?php echo '<a href="' . \Yii::$app->urlManager->createUrl(['reviews/edit', 'reviews_id' => $item_id]) . '" class="btn btn-edit btn-no-margin">' . IMAGE_EDIT . '</a>'; ?>
            <button onclick="return deleteItemConfirm( <?php echo $item_id; ?>)" class="btn btn-delete btn-no-margin"><?php echo IMAGE_DELETE;?></button>
        </div>
    <?php
    }

    public function actionEdit()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/reviews');
        
        if (Yii::$app->request->isPost) {
            $item_id = (int) Yii::$app->request->post('reviews_id');
        } else {
            $item_id = (int) Yii::$app->request->get('reviews_id');
        }
        if( $item_id === 0 ) {
            die("Wrong reviews data.");
        } else {

            $reviews_query = tep_db_query("select r.reviews_id, r.products_id, r.customers_name, r.customers_id, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating, r.status from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$item_id . "' and r.reviews_id = rd.reviews_id");
            $reviews = tep_db_fetch_array($reviews_query);

            $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
            $products = tep_db_fetch_array($products_query);

            $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
            $products_name = tep_db_fetch_array($products_name_query);

            $reviews_text_query = tep_db_query("select r.reviews_read, r.customers_name, r.customers_id, length(rd.reviews_text) as reviews_text_size from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$reviews['reviews_id'] . "' and r.reviews_id = rd.reviews_id");
            $reviews_text = tep_db_fetch_array($reviews_text_query);

            $reviews_average_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$reviews['products_id'] . "'");
            $reviews_average = tep_db_fetch_array($reviews_average_query);

            $rInfo_array = array_merge($reviews, $products, $products_name, $reviews_text, $reviews_average);

            $rInfo = new \objectInfo($rInfo_array);
            
        }

        $status = '';
        if( (int) ($rInfo->new ?? null) > 0 ){
            $status .= TEXT_NEW;
        } else {
            if( (int) $rInfo->status > 0 ){
                $status .= TEXT_APPROVED;
            } else {
                $status .= TEXT_DECLINED;
            }
        }
        
        $image = (file_exists(DIR_FS_CATALOG_IMAGES . $products['products_image']) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($products['products_image'], $products_name['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>');
//        echo tep_draw_form(
//                'save_item_form',
//                'reviews/index',
//                \common\helpers\Output::get_all_get_params( array( 'action' ) ),
//                'post',
//                'id="save_item_form" onSubmit="return saveItem();"' ) .
//            tep_draw_hidden_field( 'item_id', $item_id ) ;

        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('reviews/'), 'title' => T_EDITING_REVIEW . ' ' . $rInfo->products_name);
        $this->selectedMenu = array('catalog', 'reviews');
        
        if (Yii::$app->request->isPost) {            
            $this->layout = false;
        }
        return $this->render('edit.tpl', ['rInfo' => $rInfo, 'status' => $status, 'image' => $image]);
    }

    public function actionSubmit()
    {

        \common\helpers\Translation::init('admin/properties');

        $item_id = (int) Yii::$app->request->post('reviews_id');
        $reviews_rating = tep_db_prepare_input(Yii::$app->request->post('reviews_rating'));
        $reviews_text = tep_db_prepare_input(Yii::$app->request->post('reviews_text'));
        $status = tep_db_prepare_input(Yii::$app->request->post('status', 'off'));

        if ($status == 'on') {
            $status = 1;
        } else {
            $status = 0;
        }

        $this->layout = FALSE;
        $error = FALSE;
        $message = '';
        $script = '';
        $delete_btn = '';

        $messageType = 'success';

        if( $error === FALSE ) {
            if( $item_id > 0 ) {
                // Update
                $reviews_id = $item_id;

                tep_db_query("update " . TABLE_REVIEWS . " set reviews_rating = '" . tep_db_input($reviews_rating) . "', last_modified = now(), new = 0, status = $status where reviews_id = '" . (int)$reviews_id . "'");
                tep_db_query("update " . TABLE_REVIEWS_DESCRIPTION . " set reviews_text = '" . tep_db_input($reviews_text) . "' where reviews_id = '" . (int)$reviews_id . "'");
                
                $this->afterStatusChange($reviews_id, $status);

                $message = "Item updated";
            } else {
                // Insert
                $message = "Item inserted";
            }

        }

        if( $error === TRUE ) {
            $messageType = 'warning';

            if( $message == '' ) $message = WARN_UNKNOWN_ERROR;
        }

        ?>
        <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>   
                    </div>  
                    <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
                </div> 
                <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
            </div>
            

    <?php

        return $this->actionEdit();
    }
    
    public function afterStatusChange($reviews_id, $status) {
        foreach (\common\helpers\Hooks::getList('reviews/after-status-change') as $filename) {
            include($filename);
        }
    }
    
    public function actionConfirmitemdelete()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/reviews');
        \common\helpers\Translation::init('admin/faqdesk');

        $this->layout = FALSE;

        $item_id   = (int) Yii::$app->request->post( 'item_id' );


        $message   = $name = $title = '';
        $heading   = array();
        $contents  = array();
        $parent_id = 0;


        $reviews_query = tep_db_query("select r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where r.reviews_id = '" . (int)$item_id . "' and r.reviews_id = rd.reviews_id");
        $reviews = tep_db_fetch_array($reviews_query);

        $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$reviews['products_id'] . "'");
        $products = tep_db_fetch_array($products_query);


        $products_name_query = tep_db_query("select products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$reviews['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
        $products_name = tep_db_fetch_array($products_name_query);

        $reviews = array_merge($reviews,$products,$products_name );

        $rInfo = new \objectInfo($reviews);

        echo tep_draw_form( 'item_delete', FILENAME_INVENTORY, \common\helpers\Output::get_all_get_params( array( 'action' ) ) . 'action=update', 'post', 'id="item_delete" onSubmit="return deleteItem();"' );
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_REVIEW . '</div>';
        echo '<div class="col_desc">' . TEXT_INFO_DELETE_REVIEW_INTRO . '</div>';
        echo '<div class="col_desc">' . $rInfo->products_name . '</div>';
        ?>
        <p class="btn-toolbar">
            <?php
                echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_DELETE . '" >';
                echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

                echo tep_draw_hidden_field( 'item_id', $item_id );
            ?>
        </p>
        </form>
    <?php
    }

    public function actionItemdelete()
    {
        $this->layout = FALSE;

        \common\helpers\Translation::init('admin/reviews');
        \common\helpers\Translation::init('admin/faqdesk');

        $item_id   = (int) Yii::$app->request->post( 'item_id' );

        $messageType = 'success';
        $message     = TEXT_INFO_DELETED;


        $reviews_id = $item_id;

        tep_db_query("delete from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$reviews_id . "'");
        tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int)$reviews_id . "'");


        ?>
        <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>  
                    </div>  
                    <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK;?></span></div>
                </div>
                </div>   
                <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                    $(this).parents('.pop-mess').remove();
                });
            </script>
            </div>
            

        <p class="btn-toolbar">
            <?php
                echo '<input type="button" class="btn btn-primary" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';
            ?>
        </p>
    <?php
    }
    
    public function actionDeleteSelected()
    {
        $this->layout = false;
        $selected_ids = Yii::$app->request->post('selected_ids');
        foreach ($selected_ids as $id) {
            tep_db_query("delete from " . TABLE_REVIEWS . " where reviews_id = '" . (int)$id . "'");
            tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int)$id . "'");
        }
    }
    
    public function actionApproveSelected()
    {
        $this->layout = false;
        $selected_ids = Yii::$app->request->post('selected_ids');
        foreach ($selected_ids as $id) {
            tep_db_query("update " . TABLE_REVIEWS . " set status = '1', new = '0' where reviews_id = '" . (int)$id . "'");
            $this->afterStatusChange($id, 1);
        }
    }
    
    public function actionDeclineSelected()
    {
        $this->layout = false;
        $selected_ids = Yii::$app->request->post('selected_ids');
        foreach ($selected_ids as $id) {
            tep_db_query("update " . TABLE_REVIEWS . " set status = '0', new = '0' where reviews_id = '" . (int)$id . "'");
            $this->afterStatusChange($id, 0);
        }
    }
    
    public function actionSwitchStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        tep_db_query("update " . TABLE_REVIEWS . " set status = '" . ($status == 'true' ? 1 : 0) . "', new = '0' where reviews_id = '" . (int)$id . "'");
        $this->afterStatusChange($id, $status == 'true');
    }

} 