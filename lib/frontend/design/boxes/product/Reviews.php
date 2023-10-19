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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\SplitPageResults;

class Reviews extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $params = Yii::$app->request->get();
        $languages_id = \Yii::$app->settings->get('languages_id');

        if (!$params['products_id']) {
            return '';
        }

        $review_write_now = 0;
        $reviews_link = tep_href_link('reviews', 'products_id=' . $params['products_id']);
        if ( Yii::$app->request->getPathInfo()=='reviews/write' ) {
            $reviews_link = tep_href_link('reviews/write', 'products_id=' . $params['products_id']);;
            $review_write_now = 1;
        }

        $rating = tep_db_fetch_array(tep_db_query("
                select count(*) as count, 
                AVG(reviews_rating) as average 
                from " . TABLE_REVIEWS . " 
                where products_id = '" . (int)$params['products_id'] . "' and status
            "));
        if ($rating['count']) {
            \frontend\design\JsonLd::addData(['Product' => [
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => round($rating['average']??0),
                    'ratingCount' => $rating['count'],
                ]
            ]], ['Product', 'aggregateRating']);
        }


        $reviews_query_raw = "select r.reviews_id, rd.reviews_text as reviews_text, r.reviews_rating, r.date_added, r.customers_name from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd where status and r.products_id = '" . (int)$params['products_id'] . "' and r.reviews_id = rd.reviews_id and rd.languages_id = '" . (int)$languages_id . "' order by r.reviews_id desc";

        $reviews_split = new splitPageResults($reviews_query_raw, MAX_DISPLAY_NEW_REVIEWS);
        $products_query = tep_db_query($reviews_split->sql_query);

        if (tep_db_num_rows($products_query) > 0) {

            //$reviews = array();

            $reviews_query = tep_db_query($reviews_split->sql_query);
            while ($review = tep_db_fetch_array($reviews_query)) {
              /*if (defined('TEXT_RATE_' . $review['reviews_rating'])) {
                $review['reviews_rating_description'] = constant('TEXT_RATE_' . $review['reviews_rating']);
              }*/
                $review['link'] = tep_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $params['products_id'] . '&reviews_id=' . $review['reviews_id']);
                $review['date'] = \common\helpers\Date::date_long($review['date_added']);
                //$reviews[] = $review;

                \frontend\design\JsonLd::addData(['Product' => [
                    'review' => [[
                        '@type' => 'Review',
                        'datePublished' => \common\helpers\Date::date_long($review['date_added']),
                        'description' => $review['reviews_text'],
                        'reviewRating' => [
                            '@type' => 'Rating',
                            'ratingValue' => $review['reviews_rating'],
                            'worstRating' => '1',
                            'bestRating' => '5',
                        ],
                        'author' => [
                            '@type' => 'Person',
                            'name' => $review['customers_name'],
                        ],
                    ]]
                ]]);
            }

        }

        \frontend\design\Info::addJsData(['reviewsLink' => $reviews_link]);

        return IncludeTpl::widget(['file' => 'boxes/product/reviews.tpl', 'params' => [
            'reviews_link' => $reviews_link,
            'review_write_now' => $review_write_now,
        ]]);
    }
}