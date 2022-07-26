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
use frontend\design\SplitPageResults;
use frontend\design\ListingSql;
use frontend\design\boxes\Listing;
use frontend\design\Info;

/**
 * Site controller
 */
class ListDemoController extends Sceleton
{

    public function actionIndex()
    {
        $this->view->no_header_footer = true;

        $params = array(
          'listing_split' => new SplitPageResults(ListingSql::query(array('filename' => FILENAME_SPECIALS)), 1,'p.products_id'),
          'this_filename' => FILENAME_SPECIALS,
        );

        $get = Yii::$app->request->get();

        $settings[0]['show_name'] = $get['show_name'];
        $settings[0]['show_image'] = $get['show_image'];
        $settings[0]['show_stock'] = $get['show_stock'];
        $settings[0]['show_description'] = $get['show_description'];
        $settings[0]['show_model'] = $get['show_model'];
        $settings[0]['show_rating'] = $get['show_rating'];
        $settings[0]['show_rating_counts'] = $get['show_rating_counts'];
        $settings[0]['show_price'] = $get['show_price'];
        $settings[0]['show_buy_button'] = $get['show_buy_button'];
        $settings[0]['show_qty_input'] = $get['show_qty_input'];
        $settings[0]['show_view_button'] = $get['show_view_button'];
        $settings[0]['show_wishlist_button'] = $get['show_wishlist_button'];
        $settings[0]['show_compare'] = $get['show_compare'];
        $settings[0]['show_properties'] = $get['show_properties'];
        $settings[0]['show_bonus_points'] = $get['show_bonus_points'];
        $settings[0]['show_attributes'] = $get['show_attributes'];
        $settings[0]['show_paypal_button'] = $get['show_paypal_button'];
        $settings[0]['show_amazon_button'] = $get['show_amazon_button'];

        $settings[0]['show_name_rows'] = $get['show_name_rows'];
        $settings[0]['show_image_rows'] = $get['show_image_rows'];
        $settings[0]['show_stock_rows'] = $get['show_stock_rows'];
        $settings[0]['show_description_rows'] = $get['show_description_rows'];
        $settings[0]['show_model_rows'] = $get['show_model_rows'];
        $settings[0]['show_rating_rows'] = $get['show_rating_rows'];
        $settings[0]['show_rating_counts_rows'] = $get['show_rating_counts_rows'];
        $settings[0]['show_price_rows'] = $get['show_price_rows'];
        $settings[0]['show_buy_button_rows'] = $get['show_buy_button_rows'];
        $settings[0]['show_qty_input_rows'] = $get['show_qty_input_rows'];
        $settings[0]['show_view_button_rows'] = $get['show_view_button_rows'];
        $settings[0]['show_wishlist_button_rows'] = $get['show_wishlist_button_rows'];
        $settings[0]['show_compare_rows'] = $get['show_compare_rows'];
        $settings[0]['show_properties_rows'] = $get['show_properties_rows'];
        $settings[0]['show_bonus_points_rows'] = $get['show_bonus_points_rows'];
        $settings[0]['show_attributes_rows'] = $get['show_attributes_rows'];
        $settings[0]['show_paypal_button_rows'] = $get['show_paypal_button_rows'];
        $settings[0]['show_amazon_button_rows'] = $get['show_amazon_button_rows'];

        $settings[0]['show_name_b2b'] = $get['show_name_b2b'];
        $settings[0]['show_image_b2b'] = $get['show_image_b2b'];
        $settings[0]['show_stock_b2b'] = $get['show_stock_b2b'];
        $settings[0]['show_description_b2b'] = $get['show_description_b2b'];
        $settings[0]['show_model_b2b'] = $get['show_model_b2b'];
        $settings[0]['show_rating_b2b'] = $get['show_rating_b2b'];
        $settings[0]['show_rating_counts_b2b'] = $get['show_rating_counts_b2b'];
        $settings[0]['show_price_b2b'] = $get['show_price_b2b'];
        $settings[0]['show_buy_button_b2b'] = $get['show_buy_button_b2b'];
        $settings[0]['show_qty_input_b2b'] = $get['show_qty_input_b2b'];
        $settings[0]['show_view_button_b2b'] = $get['show_view_button_b2b'];
        $settings[0]['show_wishlist_button_b2b'] = $get['show_wishlist_button_b2b'];
        $settings[0]['show_compare_b2b'] = $get['show_compare_b2b'];
        $settings[0]['show_properties_b2b'] = $get['show_properties_b2b'];
        $settings[0]['show_bonus_points_b2b'] = $get['show_bonus_points_b2b'];
        $settings[0]['show_attributes_b2b'] = $get['show_attributes_b2b'];
        $settings[0]['show_paypal_button_b2b'] = $get['show_paypal_button_b2b'];
        $settings[0]['show_amazon_button_b2b'] = $get['show_amazon_button_b2b'];

        $settings[0]['col_in_row'] = 1;

        $settings[0]['list_type'] = $get['list_type'];
        $settings[0]['list_demo'] = 1;


        $q = new \common\components\ProductsQuery([
            'limit' => 1,
        ]);

        \frontend\design\Info::getListProductsDetails($q->buildQuery()->allIds(), $settings);

        $params = array(
            'listing_split' => SplitPageResults::make($q->buildQuery()->getQuery(), 1,'*', 'page', 1),
        );

        return $this->render('index.tpl', ['params' => [
            'params'=>$params,
            'products' => Yii::$container->get('products')->getAllProducts($settings['listing_type']),
            'settings' => $settings,
            'id' => '1'
        ]]);

    }



}
