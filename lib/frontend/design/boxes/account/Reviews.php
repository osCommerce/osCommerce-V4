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

namespace frontend\design\boxes\account;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\SplitPageResults;
use common\helpers\Date as DateHelper;

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
        global $navigation;

        if ( Yii::$app->user->isGuest ) {
            $navigation->set_snapshot();
            tep_redirect(tep_href_link('account/login','','SSL'));
        }

        $maxItems = (isset($this->settings[0]['max_items']) ? $this->settings[0]['max_items'] : (int)MAX_DISPLAY_NEW_REVIEWS);

        $history_query_raw =
            "select r.* ".
            "from " . TABLE_REVIEWS . " r " .
            " inner join ".TABLE_PRODUCTS." p on p.products_id=r.products_id ".
            "where r.customers_id = '" . (int)Yii::$app->user->getId() . "' ".
            "order by r.reviews_id DESC";
        $history_split = new splitPageResults($history_query_raw, $maxItems);
        $history_query = tep_db_query($history_split->sql_query);
        $customer_reviews = array();
        while ($customer_review = tep_db_fetch_array($history_query)) {
            $customer_review['products_link'] = '';
            if ( \common\helpers\Product::check_product($customer_review['products_id']) ) {
                $customer_review['products_link'] = tep_href_link(FILENAME_PRODUCT_INFO,'products_id='.$customer_review['products_id'],'');
            }
            $customer_review['products_name'] = \common\helpers\Product::get_products_name($customer_review['products_id']);
            if (defined('TEXT_RATE_' . $customer_review['reviews_rating'])) {
              $customer_review['reviews_rating_description'] = constant('TEXT_RATE_' . $customer_review['reviews_rating']);
            }
            $customer_review['date_added_str'] = DateHelper::date_short($customer_review['date_added']);
            if ($customer_review['status']){
                $customer_review['status_name'] = TEXT_REVIEW_STATUS_APPROVED;
            }else{
                $customer_review['status_name'] = TEXT_REVIEW_STATUS_NOT_APPROVED;
            }
            $customer_review['view'] = tep_href_link('reviews/info','reviews_id='.$customer_review['reviews_id'].'&back=account-products-reviews'.(isset($_GET['page']) && (int)$_GET['page']>1?'-'.(int)$_GET['page']:''));
            //$back = array('account/products-reviews', isset($_GET['page'])?'page='.$_GET['page']:'','SSL');
            $customer_reviews[] = $customer_review;
        }

        $params = array(
            'listing_split' => $history_split,
            'this_filename' => 'account',
            'listing_display_count_format' => TEXT_DISPLAY_NUMBER_OF_REVIEWS,
        );
        
        $this->settings[0]['show_pagenation'] = (isset($this->settings[0]['show_pagenation']) ? $this->settings[0]['show_pagenation'] : 0);
        

        return IncludeTpl::widget(['file' => 'boxes/account/reviews.tpl', 'params' => [
            'settings' => $this->settings,
            'id' => $this->id,
            'reviews' => $customer_reviews,
            'params' => ['params'=>$params]
        ]]);
    }
}