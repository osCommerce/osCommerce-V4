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

namespace frontend\design\boxes;

use common\helpers\Seo;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use yii\helpers\ArrayHelper;
use frontend\design\EditData;

class Heading extends Widget
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
        $languages_id = \Yii::$app->settings->get('languages_id');
        global $current_category_id;
        global $cPath_array;

        if ($this->settings[0]['heading_type'] == '') {
            return '';
        }

        $params = Yii::$app->request->get();
        $full_action = Yii::$app->controller->id . '/' . Yii::$app->controller->action->id;

        $h1 = '';
        $h2 = [];
        $h3 = [];
        static $sowed = [];

        switch (true) {
            // Index page
            case $full_action=='index/' || $full_action=='index/index' || ($full_action=='catalog/index' && !($current_category_id || $params['manufacturers_id']) ) :
                $h1 = defined('HEAD_H1_TAG_DEFAULT') && tep_not_null(HEAD_H1_TAG_DEFAULT) ? HEAD_H1_TAG_DEFAULT : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_DEFAULT') && tep_not_null(HEAD_H2_TAG_DEFAULT) ? HEAD_H2_TAG_DEFAULT : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_DEFAULT') && tep_not_null(HEAD_H3_TAG_DEFAULT) ? HEAD_H3_TAG_DEFAULT : ''));

                $h1 = EditData::addEditDataTeg($h1, 'seo', 'HEAD_H1_TAG_DEFAULT');
                foreach ($h2 as $key => $value){
                    $h2[$key] = EditData::addEditDataTeg($h2[$key], 'seo', 'HEAD_H2_TAG_DEFAULT', 0, $key);
                }
                foreach ($h3 as $key => $value){
                    $h3[$key] = EditData::addEditDataTeg($h3[$key], 'seo', 'HEAD_H2_TAG_DEFAULT', 0, $key);
                }
                break;

            // categories
            case $full_action=='catalog/index' && $current_category_id > 0:
                $category_query = tep_db_query("select categories_h1_tag, categories_h2_tag, categories_h2_tag, categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$current_category_id . "'  and language_id = '" . (int)$languages_id . "'");
                $category = tep_db_fetch_array($category_query);

                if ($category['categories_h1_tag']) {
                    $h1 = $category['categories_h1_tag'];
                } else {
                    $h1 = (defined('HEAD_H1_TAG_CATEGORY') && tep_not_null(HEAD_H1_TAG_CATEGORY) ? HEAD_H1_TAG_CATEGORY : '');
                    if (strstr($h1, '##BREADCRUMB##')) {
                        $_breadcrumbs = \common\helpers\Categories::breadcrumbs($current_category_id);
                        if ( empty($_breadcrumbs) && Seo::getMetaDefaultBreadcrumb($full_action) ) {
                            $h1 = str_replace('##BREADCRUMB##', Seo::getMetaDefaultBreadcrumb($full_action), $h1);
                        }else {
                            $h1 = str_replace('##BREADCRUMB##', $_breadcrumbs, $h1);
                        }
                    }
                    if (strstr($h1, '##CATEGORY_NAME##')) {
                        $h1 = str_replace('##CATEGORY_NAME##', \common\helpers\Categories::get_categories_name($current_category_id), $h1);
                    }
                }

                if ($category['categories_h2_tag']) {
                    $h2 = $category['categories_h2_tag'];
                } else {
                    $h2 = (defined('HEAD_H2_TAG_CATEGORY') && tep_not_null(HEAD_H2_TAG_CATEGORY) ? HEAD_H2_TAG_CATEGORY : '');
                    if (strstr($h2, '##BREADCRUMB##')) {
                        $_breadcrumbs = \common\helpers\Categories::breadcrumbs($current_category_id);
                        if ( empty($_breadcrumbs) && Seo::getMetaDefaultBreadcrumb($full_action) ) {
                            $h2 = str_replace('##BREADCRUMB##', Seo::getMetaDefaultBreadcrumb($full_action), $h2);
                        }else {
                            $h2 = str_replace('##BREADCRUMB##', $_breadcrumbs, $h2);
                        }
                    }
                    if (strstr($h2, '##CATEGORY_NAME##')) {
                        $h2 = str_replace('##CATEGORY_NAME##', \common\helpers\Categories::get_categories_name($current_category_id), $h2);
                    }
                }
                $h2 = explode("\n", $h2);


                if ($category['categories_h3_tag']) {
                    $h3 = $category['categories_h3_tag'];
                } else {
                    $h3 = (defined('HEAD_H3_TAG_CATEGORY') && tep_not_null(HEAD_H3_TAG_CATEGORY) ? HEAD_H3_TAG_CATEGORY : '');
                    if (strstr($h3, '##BREADCRUMB##')) {
                        $_breadcrumbs = \common\helpers\Categories::breadcrumbs($current_category_id);
                        if ( empty($_breadcrumbs) && Seo::getMetaDefaultBreadcrumb($full_action) ) {
                            $h3 = str_replace('##BREADCRUMB##', Seo::getMetaDefaultBreadcrumb($full_action), $h3);
                        }else {
                            $h3 = str_replace('##BREADCRUMB##', $_breadcrumbs, $h3);
                        }
                    }
                    if (strstr($h3, '##CATEGORY_NAME##')) {
                        $h3 = str_replace('##CATEGORY_NAME##', \common\helpers\Categories::get_categories_name($current_category_id), $h3);
                    }
                }
                $h3 = explode("\n", $h3);
                break;

            // manufacturers
            case $full_action=='catalog/index' && $params['manufacturers_id'] > 0:
                $manufacturer_query = tep_db_query("select mi.manufacturers_h1_tag from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int)$params['manufacturers_id'] . "' and languages_id = '" . (int)$languages_id . "'");
                $manufacturer = tep_db_fetch_array($manufacturer_query);

                if ($manufacturer['manufacturers_h1_tag']) {
                    $h1 = $manufacturer['manufacturers_h1_tag'];
                } else {
                    $h1 = defined('HEAD_H1_TAG_BRAND') && tep_not_null(HEAD_H1_TAG_BRAND) ? HEAD_H1_TAG_BRAND : '';
                    if (strstr($h1, '##BRAND_NAME##')) {
                        $h1 = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $params['manufacturers_id']), $h1);
                    }
                }

                if ($manufacturer['manufacturers_h2_tag']) {
                    $h2 = $manufacturer['manufacturers_h2_tag'];
                } else {
                    $h2 = defined('HEAD_H2_TAG_BRAND') && tep_not_null(HEAD_H2_TAG_BRAND) ? HEAD_H2_TAG_BRAND : '';
                    if (strstr($h2, '##BRAND_NAME##')) {
                        $h2 = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $params['manufacturers_id']), $h2);
                    }
                }
                $h2 = explode("\n", $h2);

                if ($manufacturer['manufacturers_h3_tag']) {
                    $h3 = $manufacturer['manufacturers_h3_tag'];
                } else {
                    $h3 = defined('HEAD_H3_TAG_BRAND') && tep_not_null(HEAD_H3_TAG_BRAND) ? HEAD_H3_TAG_BRAND : '';
                    if (strstr($h3, '##BRAND_NAME##')) {
                        $h3 = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $params['manufacturers_id']), $h3);
                    }
                }
                $h3 = explode("\n", $h3);
                break;

            case ( $full_action=='catalog/product' && $params['products_id']):
                $products = Yii::$container->get('products');
                $product = $products->getProduct($params['products_id']);
                $category_id = array_pop($cPath_array);

                if ($product['products_h1_tag']) {
                    $h1 = $product['products_h1_tag'];
                } else {
                    $h1 = (defined('HEAD_H1_TAG_PRODUCT_INFO') && tep_not_null(HEAD_H1_TAG_PRODUCT_INFO) ? HEAD_H1_TAG_PRODUCT_INFO : '');
                    if (strstr($h1, '##PRODUCT_NAME##')) {
                        $h1 = str_replace('##PRODUCT_NAME##', $product['products_name'], $h1);
                    }
                    if (strstr($h1, '##BREADCRUMB##')) {
                        $_breadcrumbs = \common\helpers\Categories::breadcrumbs($category_id);
                        if ( empty($_breadcrumbs) && Seo::getMetaDefaultBreadcrumb($full_action) ) {
                            $h1 = str_replace('##BREADCRUMB##', Seo::getMetaDefaultBreadcrumb($full_action), $h1);
                        }else {
                            $h1 = str_replace('##BREADCRUMB##', $_breadcrumbs, $h1);
                        }
                    }
                    if (strstr($h1, '##CATEGORY_NAME##')) {
                        $h1 = str_replace('##CATEGORY_NAME##', \common\helpers\Categories::get_categories_name($category_id), $h1);
                    }
                    if (strstr($h1, '##BRAND_NAME##')) {
                        $h1 = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $product['manufacturers_id']), $h1);
                    }
                    if (strstr($h1, '##DOCUMENTS##')) {
                        $documents_array = array();
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductDocuments', 'allowed')) {
                            $documents = $ext::getDocuments($params['products_id']);
                            if (is_array($documents)) {
                                foreach ($documents as $doc_type) {
                                    if (is_array($doc_type['docs'])) {
                                        foreach ($doc_type['docs'] as $doc) {
                                            $documents_array[] = ($doc['title'][$languages_id] ? $doc['title'][$languages_id] : $doc['filename']);
                                        }
                                    }
                                }
                            }
                        }
                        $h1 = str_replace('##DOCUMENTS##', implode(', ', $documents_array), $h1);
                    }
                    if (strstr($h1, '##PRODUCT_TITLE_TAG##')) {
                        $h1 = str_replace('##PRODUCT_TITLE_TAG##', $product['products_head_title_tag'], $h1);
                    }

                    if (strstr($h1, '##PRODUCT_DESCRIPTION_TAG##')) {
                        $h1 = str_replace('##PRODUCT_DESCRIPTION_TAG##', $product['products_head_desc_tag'], $h1);
                    }

                    if ( preg_match_all('/##PRODUCT(_SHORT)?_DESCRIPTION(_([Nn|\d]+))?##/', $h1, $description_replace) ){
                        foreach ( $description_replace[0] as $idx=>$replace_key ){
                            $data_from_key = 'products_description'.strtolower($description_replace[1][$idx]);
                            $replace_to_value = isset($product[$data_from_key])?$product[$data_from_key]:'';
                            $replace_to_value = preg_replace('/\s{2,}/', ' ', \common\helpers\Output::strip_tags($replace_to_value));
                            if (is_numeric($description_replace[3][$idx])) {
                                $replace_to_value = \common\helpers\Output::truncate($replace_to_value, (int)$description_replace[3][$idx], '');
                            }
                            $h1 = str_replace($replace_key, $replace_to_value, $h1);
                        }
                    }

                }

                if ($product['products_h2_tag']) {
                    $h2 = $product['products_h2_tag'];
                } else {
                    $h2 = (defined('HEAD_H2_TAG_PRODUCT_INFO') && tep_not_null(HEAD_H2_TAG_PRODUCT_INFO) ? HEAD_H2_TAG_PRODUCT_INFO : '');
                    if (strstr($h2, '##PRODUCT_NAME##')) {
                        $h2 = str_replace('##PRODUCT_NAME##', $product['products_name'], $h2);
                    }
                    if (strstr($h2, '##BREADCRUMB##')) {
                        $_breadcrumbs = \common\helpers\Categories::breadcrumbs($category_id);
                        if ( empty($_breadcrumbs) && Seo::getMetaDefaultBreadcrumb($full_action) ) {
                            $h2 = str_replace('##BREADCRUMB##', Seo::getMetaDefaultBreadcrumb($full_action), $h2);
                        }else {
                            $h2 = str_replace('##BREADCRUMB##', $_breadcrumbs, $h2);
                        }
                    }
                    if (strstr($h2, '##CATEGORY_NAME##')) {
                        $h2 = str_replace('##CATEGORY_NAME##', \common\helpers\Categories::get_categories_name($category_id), $h2);
                    }
                    if (strstr($h2, '##BRAND_NAME##')) {
                        $h2 = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $product['manufacturers_id']), $h2);
                    }
                    if (strstr($h2, '##DOCUMENTS##')) {
                        $documents_array = array();
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductDocuments', 'allowed')) {
                            $documents = $ext::getDocuments($params['products_id']);
                            if (is_array($documents)) {
                                foreach ($documents as $doc_type) {
                                    if (is_array($doc_type['docs'])) {
                                        foreach ($doc_type['docs'] as $doc) {
                                            $documents_array[] = ($doc['title'][$languages_id] ? $doc['title'][$languages_id] : $doc['filename']);
                                        }
                                    }
                                }
                            }
                        }
                        $h2 = str_replace('##DOCUMENTS##', implode(', ', $documents_array), $h2);
                    }
                    if (strstr($h2, '##PRODUCT_TITLE_TAG##')) {
                        $h2 = str_replace('##PRODUCT_TITLE_TAG##', $product['products_head_title_tag'], $h2);
                    }

                    if (strstr($h2, '##PRODUCT_DESCRIPTION_TAG##')) {
                        $h2 = str_replace('##PRODUCT_DESCRIPTION_TAG##', $product['products_head_desc_tag'], $h2);
                    }

                    if ( preg_match_all('/##PRODUCT(_SHORT)?_DESCRIPTION(_([Nn|\d]+))?##/', $h2, $description_replace) ){
                        foreach ( $description_replace[0] as $idx=>$replace_key ){
                            $data_from_key = 'products_description'.strtolower($description_replace[1][$idx]);
                            $replace_to_value = isset($product[$data_from_key])?$product[$data_from_key]:'';
                            $replace_to_value = preg_replace('/\s{2,}/', ' ', \common\helpers\Output::strip_tags($replace_to_value));
                            if (is_numeric($description_replace[3][$idx])) {
                                $replace_to_value = \common\helpers\Output::truncate($replace_to_value, (int)$description_replace[3][$idx], '');
                            }
                            $h1 = str_replace($replace_key, $replace_to_value, $h2);
                        }
                    }

                }
                $h2 = explode("\n", $h2);


                if ($product['products_h3_tag']) {
                    $h3 = $product['products_h3_tag'];
                } else {
                    $h3 = (defined('HEAD_H3_TAG_PRODUCT_INFO') && tep_not_null(HEAD_H3_TAG_PRODUCT_INFO) ? HEAD_H3_TAG_PRODUCT_INFO : '');
                    if (strstr($h3, '##PRODUCT_NAME##')) {
                        $h3 = str_replace('##PRODUCT_NAME##', $product['products_name'], $h3);
                    }
                    if (strstr($h3, '##BREADCRUMB##')) {
                        $_breadcrumbs = \common\helpers\Categories::breadcrumbs($category_id);
                        if ( empty($_breadcrumbs) && Seo::getMetaDefaultBreadcrumb($full_action) ) {
                            $h3 = str_replace('##BREADCRUMB##', Seo::getMetaDefaultBreadcrumb($full_action), $h3);
                        }else {
                            $h3 = str_replace('##BREADCRUMB##', $_breadcrumbs, $h3);
                        }
                    }
                    if (strstr($h3, '##CATEGORY_NAME##')) {
                        $h3 = str_replace('##CATEGORY_NAME##', \common\helpers\Categories::get_categories_name($category_id), $h3);
                    }
                    if (strstr($h3, '##BRAND_NAME##')) {
                        $h3 = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $product['manufacturers_id']), $h3);
                    }
                    if (strstr($h3, '##DOCUMENTS##')) {
                        $documents_array = array();
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductDocuments', 'allowed')) {
                            $documents = $ext::getDocuments($params['products_id']);
                            if (is_array($documents)) {
                                foreach ($documents as $doc_type) {
                                    if (is_array($doc_type['docs'])) {
                                        foreach ($doc_type['docs'] as $doc) {
                                            $documents_array[] = ($doc['title'][$languages_id] ? $doc['title'][$languages_id] : $doc['filename']);
                                        }
                                    }
                                }
                            }
                        }
                        $h3 = str_replace('##DOCUMENTS##', implode(', ', $documents_array), $h3);
                    }
                    if (strstr($h3, '##PRODUCT_TITLE_TAG##')) {
                        $h3 = str_replace('##PRODUCT_TITLE_TAG##', $product['products_head_title_tag'], $h3);
                    }

                    if (strstr($h3, '##PRODUCT_DESCRIPTION_TAG##')) {
                        $h3 = str_replace('##PRODUCT_DESCRIPTION_TAG##', $product['products_head_desc_tag'], $h3);
                    }

                    if ( preg_match_all('/##PRODUCT(_SHORT)?_DESCRIPTION(_([Nn|\d]+))?##/', $h3, $description_replace) ){
                        foreach ( $description_replace[0] as $idx=>$replace_key ){
                            $data_from_key = 'products_description'.strtolower($description_replace[1][$idx]);
                            $replace_to_value = isset($product[$data_from_key])?$product[$data_from_key]:'';
                            $replace_to_value = preg_replace('/\s{2,}/', ' ', \common\helpers\Output::strip_tags($replace_to_value));
                            if (is_numeric($description_replace[3][$idx])) {
                                $replace_to_value = \common\helpers\Output::truncate($replace_to_value, (int)$description_replace[3][$idx], '');
                            }
                            $h1 = str_replace($replace_key, $replace_to_value, $h3);
                        }
                    }

                }
                $h3 = explode("\n", $h3);
                break;

            case ( $full_action=='catalog/all-products' ):
                $h1 = defined('HEAD_H1_TAG_PRODUCTS_ALL') && tep_not_null(HEAD_H1_TAG_PRODUCTS_ALL) ? HEAD_H1_TAG_PRODUCTS_ALL : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_PRODUCTS_ALL') && tep_not_null(HEAD_H2_TAG_PRODUCTS_ALL) ? HEAD_H2_TAG_PRODUCTS_ALL : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_PRODUCTS_ALL') && tep_not_null(HEAD_H3_TAG_PRODUCTS_ALL) ? HEAD_H3_TAG_PRODUCTS_ALL : ''));
                break;

            case ( $full_action=='catalog/featured-products' ):
                $h1 = defined('HEAD_H1_TAG_FEATURED') && tep_not_null(HEAD_H1_TAG_FEATURED) ? HEAD_H1_TAG_FEATURED : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_FEATURED') && tep_not_null(HEAD_H2_TAG_FEATURED) ? HEAD_H2_TAG_FEATURED : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_FEATURED') && tep_not_null(HEAD_H3_TAG_FEATURED) ? HEAD_H3_TAG_FEATURED : ''));
                break;

            case ( $full_action=='catalog/products-new' ):
                $h1 = defined('HEAD_H1_TAG_WHATS_NEW') && tep_not_null(HEAD_H1_TAG_WHATS_NEW) ? HEAD_H1_TAG_WHATS_NEW : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_WHATS_NEW') && tep_not_null(HEAD_H2_TAG_WHATS_NEW) ? HEAD_H2_TAG_WHATS_NEW : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_WHATS_NEW') && tep_not_null(HEAD_H3_TAG_WHATS_NEW) ? HEAD_H3_TAG_WHATS_NEW : ''));
                break;

            case ( $full_action=='catalog/sales' ):
                $h1 = defined('HEAD_H1_TAG_SPECIALS') && tep_not_null(HEAD_H1_TAG_SPECIALS) ? HEAD_H1_TAG_SPECIALS : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_SPECIALS') && tep_not_null(HEAD_H2_TAG_SPECIALS) ? HEAD_H2_TAG_SPECIALS : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_SPECIALS') && tep_not_null(HEAD_H3_TAG_SPECIALS) ? HEAD_H3_TAG_SPECIALS : ''));

                break;

            // products_reviews_info.php and products_reviews.php
            case ( strpos($full_action,'reviews/')===0 ):
                $h1 = defined('HEAD_H1_TAG_PRODUCT_REVIEWS_INFO') && tep_not_null(HEAD_H1_TAG_PRODUCT_REVIEWS_INFO)? HEAD_H1_TAG_PRODUCT_REVIEWS_INFO : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_PRODUCT_REVIEWS_INFO') && tep_not_null(HEAD_H2_TAG_PRODUCT_REVIEWS_INFO) ? HEAD_H2_TAG_PRODUCT_REVIEWS_INFO : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_PRODUCT_REVIEWS_INFO') && tep_not_null(HEAD_H3_TAG_PRODUCT_REVIEWS_INFO) ? HEAD_H3_TAG_PRODUCT_REVIEWS_INFO : ''));
                break;

            // information
            case (($full_action=='info/index' || $full_action=='info/') && $params['info_id'] ):
                $information_query = tep_db_query("select information_h1_tag, information_h2_tag, information_h3_tag from " . TABLE_INFORMATION . " where information_id = '" . (int)$params['info_id'] . "' and languages_id = '" . (int)$languages_id . "' and visible = 1 and affiliate_id = 0 and platform_id='".\common\classes\platform::currentId()."' ");
                $information = tep_db_fetch_array($information_query);
                $h1 = $information['information_h1_tag'];
                $h1 = EditData::addEditDataTeg($h1, 'info', 'page_title', $params['info_id']);
                $h2 = explode("\n", $information['information_h2_tag']);
                foreach ($h2 as $key => $value) {
                    $h2[$key] = EditData::addEditDataTeg($h2[$key], 'info', 'information_h2_tag', $params['info_id'], $key);
                }
                $h3 = explode("\n", $information['information_h3_tag']);
                foreach ($h3 as $key => $value) {
                    $h3[$key] = EditData::addEditDataTeg($h3[$key], 'info', 'information_h3_tag', $params['info_id'], $key);
                }
                break;

            // gift card
            case ( $full_action=='catalog/gift-card' ):
                $h1 = defined('HEAD_H1_TAG_GIFT_CARD') && tep_not_null(HEAD_H1_TAG_GIFT_CARD) ? HEAD_H1_TAG_GIFT_CARD : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_GIFT_CARD') && tep_not_null(HEAD_H2_TAG_GIFT_CARD) ? HEAD_H2_TAG_GIFT_CARD : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_GIFT_CARD') && tep_not_null(HEAD_H3_TAG_GIFT_CARD) ? HEAD_H3_TAG_GIFT_CARD : ''));
                break;

            // contact us
            case ( strpos($full_action,'contact/')===0 ):
                $h1 = defined('HEAD_H1_TAG_CONTACT_US') && tep_not_null(HEAD_H1_TAG_CONTACT_US) ? HEAD_H1_TAG_CONTACT_US : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_CONTACT_US') && tep_not_null(HEAD_H2_TAG_CONTACT_US) ? HEAD_H2_TAG_CONTACT_US : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_CONTACT_US') && tep_not_null(HEAD_H3_TAG_CONTACT_US) ? HEAD_H3_TAG_CONTACT_US : ''));
                break;

            // wedding registry
            case ( strpos($full_action,'wedding-registry/')===0 ):
                $h1 = defined('HEAD_H1_TAG_WEDDING_REGISTRY') && tep_not_null(HEAD_H1_TAG_WEDDING_REGISTRY) ? HEAD_H1_TAG_WEDDING_REGISTRY : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_WEDDING_REGISTRY') && tep_not_null(HEAD_H2_TAG_WEDDING_REGISTRY) ? HEAD_H2_TAG_WEDDING_REGISTRY : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_WEDDING_REGISTRY') && tep_not_null(HEAD_H3_TAG_WEDDING_REGISTRY) ? HEAD_H3_TAG_WEDDING_REGISTRY : ''));
                break;

            //CATALOG PAGES
            case ( strpos($full_action,'catalog-pages/')===0 ):
                $h1 = defined('HEAD_H1_TAG_CATALOG_PAGES') && tep_not_null(HEAD_H1_TAG_CATALOG_PAGES) ? HEAD_H1_TAG_CATALOG_PAGES : '';
                $h2 = explode("\n", (defined('HEAD_H2_TAG_CATALOG_PAGES') && tep_not_null(HEAD_H2_TAG_CATALOG_PAGES) ? HEAD_H2_TAG_CATALOG_PAGES : ''));
                $h3 = explode("\n", (defined('HEAD_H3_TAG_CATALOG_PAGES') && tep_not_null(HEAD_H3_TAG_CATALOG_PAGES) ? HEAD_H3_TAG_CATALOG_PAGES : ''));
                $platformId = platform::activeId()?platform::activeId():platform::currentId();
                $slug = tep_db_prepare_input($params['page']);
                $catalogPage = Yii::$app->db->createCommand("
                        SELECT cd.h1_tag,cd.h2_tag,cd.h3_tag
                        FROM catalog_pages_description cd
                        INNER JOIN catalog_pages cp ON cd.catalog_pages_id = cp.catalog_pages_id AND cp.platform_id = $platformId
                        WHERE (cd.languages_id = $languages_id) AND (cd.slug = '$slug')")
                    ->queryOne();
                if(!empty($catalogPage['h1_tag'])){
                    $h1 = $catalogPage['h1_tag'];
                }
                if(!empty($catalogPage['h2_tag'])){
                    $h2 = explode("\n",$catalogPage['h2_tag']);
                }
                if(!empty($catalogPage['h3_tag'])){
                    $h3 = explode("\n",$catalogPage['h3_tag']);
                }
                break;
            // all other pages not defined above
            case preg_match('/^delivery-location\//', $full_action):
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryLocation', 'allowed')) {
                    $_data = $ext::getHeadingData($full_action, $params);
                    $h1 = $_data['h1'];
                    $h2 = $_data['h2'];
                    $h3 = $_data['h3'];
                }
                break;
            case preg_match('/^blog\//', $full_action):
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('Blog', 'allowed')) {
                    $_data = $ext::getHeadingData($full_action, $params);
                    $h1 = $_data['h1'];
                    $h2 = $_data['h2'];
                    $h3 = $_data['h3'];
                }
                break;
            default:
                break;

        }


        if ($this->settings[0]['heading_type'] == 'h1') {
            if ($h1) {
                return '<h1>' . $h1 . '</h1>';
            } else {
                return '';
            }
        }

        if ($this->settings[0]['heading_item']??null) {

            if ($this->settings[0]['heading_type'] == 'h2') {
                if ($h2) {
                    $sowed['h2'][$this->settings[0]['heading_item'] - 1] = true;
                    return '<h2 class="heading-2">' . $h2[$this->settings[0]['heading_item'] - 1] . '</h2>';
                } else {
                    return '';
                }
            } elseif ($this->settings[0]['heading_type'] == 'h3') {
                if ($h3) {
                    $sowed['h3'][$this->settings[0]['heading_item'] - 1] = true;
                    return '<h3 class="heading-3">' . $h3[$this->settings[0]['heading_item'] - 1] . '</h3>';
                } else {
                    return '';
                }
            }
        } else {

            if ($this->settings[0]['heading_type'] == 'h2') {

                foreach ($h2 as $key => $heading) {
                    if (!($sowed['h2'][$key]??null)){
                        $sowed['h2'][$key] = true;
                        return '<h2 class="heading-2">' . $h2[$key] . '</h2>';
                    }
                }

            } elseif ($this->settings[0]['heading_type'] == 'h3') {

                foreach ($h3 as $key => $heading) {
                    if (!$sowed['h3'][$key]){
                        $sowed['h3'][$key] = true;
                        return '<h3 class="heading-3">' . $h3[$key] . '</h3>';
                    }
                }

            }

        }


        return '';
    }
}