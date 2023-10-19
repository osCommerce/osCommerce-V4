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

namespace frontend\controllers;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\SplitPageResults;
use common\helpers\Product;

/**
 * Site controller
 */
class ReviewsController extends Sceleton
{

    public function actionIndex()
    {

        global $breadcrumb;
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $languages_id = \Yii::$app->settings->get('languages_id');
        $params = Yii::$app->request->get();
        $messageStack = \Yii::$container->get('message_stack');

        $rating_query = tep_db_query("select count(*) as count, AVG(reviews_rating) as average from " . TABLE_REVIEWS . " where products_id = '" . (int)$params['products_id'] . "' and status");
        $rating = tep_db_fetch_array($rating_query);

        $reviews = '';
        if ($params['products_id']) {

            $product_name = Product::get_products_name((int)$params['products_id']);
            if ( $product_name && Product::check_product((int)$params['products_id']) ) {
                $breadcrumb->add($product_name, tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . (int)$params['products_id']));
            }
            $breadcrumb->add(NAVBAR_TITLE, tep_href_link('reviews',''));

            $reviews_query_raw = "select r.reviews_id, rd.reviews_text as reviews_text, r.reviews_rating, r.date_added, r.customers_name from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where status and r.products_id = '" . (int)$params['products_id'] . "' and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$languages_id . "' order by r.reviews_id desc";

            $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);
            $products_query = tep_db_query($reviews_split->sql_query);
            $links = '';
            if (tep_db_num_rows($products_query) > 0) {

                $reviews = array();

                $reviews_query = tep_db_query($reviews_split->sql_query);
                while ($review = tep_db_fetch_array($reviews_query)) {
                    if (defined('TEXT_RATE_' . $review['reviews_rating'])) {
                      $review['reviews_rating_description'] = constant('TEXT_RATE_' . $review['reviews_rating']);
                    }
                    $review['link'] = tep_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $params['products_id'] . '&reviews_id=' . $review['reviews_id']);
                    $review['date'] = \common\helpers\Date::date_long($review['date_added']);
                    $reviews[] = $review;
                }

                $links = $reviews_split->display_links(MAX_DISPLAY_PAGE_LINKS, \common\helpers\Output::get_all_get_params(array('page', 'info', 'x', 'y', 'ajax', 't', 'filter', 'split')), 'reviews');

            }

            return $this->render('index.tpl', [
                'reviews' => $reviews,
                'link_write' => tep_href_link('reviews/write', 'products_id='. $params['products_id']),
                'rating' => round($rating['average']??0),
                'count' => $rating['count'],
                'number_of_rows' => $reviews_split->number_of_rows,
                'links' => $links,
                'counts' => $reviews_split->display_count(TEXT_DISPLAY_NUMBER_OF_REVIEWS),
                'message_review' => $messageStack->size('review')>0?$messageStack->output('review'):'',
                'logged' => (!Yii::$app->user->isGuest ? 1 : ''),
                'reviews_split' => $reviews_split
            ]);

        } else {

            $products_join = '';
            if ( \common\classes\platform::activeId() ) {
              $products_join .= $this->sqlProductsToPlatformCategories();
            }

            if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()){
                $reviews_query_raw = "select distinct r.reviews_id, ".
                  "rd.reviews_text as reviews_text, r.reviews_rating, r.date_added, ".
                  "p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, ".
                  "p.products_image, p.products_price, p.products_tax_class_id, ".
                  "r.customers_name ".
                  "from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "'  " . " left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)\Yii::$app->settings->get('currency_id') : '0'). "' where status " . Product::getState(true) . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and p.products_id = r.products_id and r.reviews_id = rd.reviews_id  " . "  and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and rd.languages_id = '" . (int)$languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' order by r.reviews_id DESC";
            }else{
                $reviews_query_raw = "select distinct r.reviews_id, ".
                  "rd.reviews_text as reviews_text, r.reviews_rating, r.date_added, ".
                  "p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, ".
                  "p.products_image, p.products_price, p.products_tax_class_id, ".
                  "r.customers_name ".
                  "from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p {$products_join} left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . $languages_id ."' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "'  " . " where status " . Product::getState(true) . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and p.products_id = r.products_id and r.reviews_id = rd.reviews_id and p.products_id = pd.products_id  " . " and pd.language_id = '" . (int)$languages_id . "' and rd.languages_id = '" . (int)$languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' order by r.reviews_id DESC";
            }

            $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);
            $products_query = tep_db_query($reviews_split->sql_query);
            if (tep_db_num_rows($products_query) > 0) {

                $reviews = [];
                while($res_rev = tep_db_fetch_array($products_query)){
                    if (defined('TEXT_RATE_' . $res_rev['reviews_rating'])) {
                      $res_rev['reviews_rating_description'] = constant('TEXT_RATE_' . $res_rev['reviews_rating']);
                    }
                    $res_rev['link'] = Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $res_rev['products_id']]);

                    $res_rev['img'] = \common\classes\Images::getImage($res_rev['products_id'], 'Small');

                    $res_rev['date'] = \common\helpers\Date::date_long($res_rev['date_added']);

                    $reviews[] = $res_rev;

                }
            }


            return $this->render('all-reviews.tpl', [
                'params' => [
                    'reviews' => $reviews,
                    'reviews_split' => $reviews_split
                ],
                'reviews' => $reviews,
                'reviews_split' => $reviews_split
            ]);
        }
    }


    public function actionWrite()
    {
        global $navigation;
        $params = Yii::$app->request->get();

        if (Yii::$app->user->isGuest) {
            if (\Yii::$app->request->isAjax){
                $navigation->set_snapshot();

                $authContainer = new \frontend\forms\registration\AuthContainer();

                return $this->render('not-logged.tpl', [
                    'params' => [
                        'enterModels' => $authContainer->getForms('account/login-box'),
                        'action' => tep_href_link('account/login', 'action=process', 'SSL'),
                    ],
                ]);
            }else {
                $navigation->set_snapshot();
                tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
            }
        }

        if ( empty($params['products_id']) ) die;

        
        $error = false;
        $rating = '';
        $review = '';
        $messageStack = \Yii::$container->get('message_stack');
        if (isset($params['action']) && ($params['action'] == 'process') && !Yii::$app->user->isGuest) {
            $customer_query = tep_db_query(
              "select customers_firstname, customers_lastname ".
              "from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)Yii::$app->user->getId() . "'"
            );
            $customer = tep_db_fetch_array($customer_query);

            $rating = tep_db_prepare_input($_POST['rating']);
            $review = tep_db_prepare_input(Yii::$app->request->post('review'));


            if (($rating < 1) || ($rating > 5)) {
                $error = true;

                $messageStack->add(TEXT_REVIEW_RATING_ERROR, 'review');
            }

            if (strlen($review) < REVIEW_TEXT_MIN_LENGTH) {
                $error = true;

                $messageStack->add(TEXT_REVIEW_TEXT_ERROR, 'review');
            }

            if ($error == false)
            {
                tep_db_perform(TABLE_REVIEWS, array(
                  'products_id' => $params['products_id'],
                  'customers_id' => Yii::$app->user->getId(),
                  'customers_name' => $customer['customers_firstname'] . ' ' . $customer['customers_lastname'],
                  'reviews_rating' => $rating,
                  'date_added' => 'now()',
                ));
                $insert_id = tep_db_insert_id();

                tep_db_perform(TABLE_REVIEWS_DESCRIPTION, array(
                  'reviews_id' => $insert_id,
                  'languages_id' => (int)\Yii::$app->settings->get('languages_id'),
                  'reviews_text' => $review,
                ));

                $messageStack->add_session(REVIEW_ADDED, 'review', 'success');
                tep_redirect(tep_href_link('reviews/index', 'products_id=' . $params['products_id']));
            }
        }

        if (\Yii::$app->request->isAjax) {
            $message_review = '';
            if ( $messageStack->size('review')>0 ) {
                $message_review = $messageStack->output('review');
            }
            return $this->render('write.tpl', [
              'message_review' => $message_review,
              'review_rate' => $rating,
              'review_text' => $review,
              'link' => tep_href_link('reviews'),
              'link_cancel' => tep_href_link('reviews/index', 'products_id=' . $params['products_id']),
              'link_write' => tep_href_link('reviews/write', 'products_id=' . $params['products_id'].'&action=process'),
              'products_id' => $params['products_id']
            ]);
            
        }else{
            //return Yii::$app->runAction('catalog/product',['products_id'=>$params['products_id']]);
            return $this->redirect(['catalog/product','products_id'=>$params['products_id']]);
        }
    }


    public function actionInfo()
    {
        $reviews_id = intval(\Yii::$app->request->get('reviews_id',0));
        $reviews = \common\models\Reviews::findOne($reviews_id);

        Yii::$app->response->redirect(['catalog/product', 'products_id' => $reviews->products_id]);
    }

}
