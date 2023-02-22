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

namespace common\classes;

use Yii;

class TlUrl {

    public static function replaceUrl($text)
    {
        if ( !empty($text) && strpos($text,'##URL##')!==false ) {
            $text = preg_replace_callback("/\#\#URL\#\#([^\"]+)/", self::class ."::addUrl", $text);
        }

        return $text;
    }

    public static function addUrl($matches)
    {

        $arr = explode('?', str_replace('&amp;', '&', $matches[1]));

        $url = [];
        $url[0] = @$arr[0];

        if (isset($arr[1])) {
            $gets = explode('&', $arr[1]);
            foreach ($gets as $get){
                $nameVal = explode('=', $get);
                $url[$nameVal[0]] = $nameVal[1];
            }
        }
        if (\frontend\design\Info::isTotallyAdmin()){
            $link = Yii::$app->get('platform')->config()->getCatalogBaseUrl( true );
            $params = '';
            if (count($url) > 1) {
                $params = '?';
                foreach ($url as $name => $val) {
                    if ($name === 0) continue;
                    $params .= ($params != '?' ? '&' : '') . $name . '=' . $val;
                }
            }
            return rtrim($link,'/') . '/' . $url[0] . $params;
        }
        return Yii::$app->urlManager->createUrl($url);
    }

    public static function buttons($editor, $platform_id, $languages_id, $field = '')
    {
        $action = 'information_manager/page-links';

        $links = [
            [
                'name' => TEXT_PAGE_LINKS,
                'class' => '',
                'url' => \Yii::$app->urlManager->createUrl([
                    $action,
                    'name'=>'info',
                    'editor_id' => $editor,
                    'field' => $field,
                    'languages_id' => $languages_id,
                    'platform_id' => $platform_id
                ])
            ],[
                'name' => TEXT_PRODUCTS_LINKS,
                'class' => '',
                'url' => \Yii::$app->urlManager->createUrl([
                    $action,
                    'name'=>'product',
                    'editor_id' => $editor,
                    'field' => $field,
                    'languages_id' => $languages_id,
                    'platform_id' => $platform_id
                ])
            ],[
                'name' => TEXT_CATEGORIES_LINKS,
                'class' => '',
                'url' => \Yii::$app->urlManager->createUrl([
                    $action,
                    'name'=>'category',
                    'editor_id' => $editor,
                    'field' => $field,
                    'languages_id' => $languages_id,
                    'platform_id' => $platform_id
                ])
            ],[
                'name' => TEXT_BRANDS,
                'class' => '',
                'url' => \Yii::$app->urlManager->createUrl([
                    $action,
                    'name'=>'brand',
                    'editor_id' => $editor,
                    'field' => $field,
                    'languages_id' => $languages_id,
                    'platform_id' => $platform_id
                ]),
            ],[
                'name' => COMMON_LINKS,
                'class' => '',
                'url' => \Yii::$app->urlManager->createUrl([
                    $action,
                    'name'=>'common',
                    'editor_id' => $editor,
                    'field' => $field,
                    'languages_id' => $languages_id,
                    'platform_id' => $platform_id
                ]),
            ]];

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryLocation', 'allowed')) {
            $links[] = [
                'name' => TEXT_DELIVERY_LOCATION_LINKS,
                'class' => '',
                'url' => \Yii::$app->urlManager->createUrl([
                    $action,
                    'name' => 'location',
                    'editor_id' => $editor,
                    'field' => $field,
                    'languages_id' => $languages_id,
                    'platform_id' => $platform_id
                ]),
            ];
        }

        return $links;
    }

    public static function pageLinks()
    {
        global $languages_id;
        $get = Yii::$app->request->get();

        $platform_id = $get['platform_id'] ? $get['platform_id'] : \common\classes\platform::firstId();
        $lang_id = $get['languages_id'] ? $get['languages_id'] : $languages_id;

        $items = array();
        $suggest = false;

        switch ($get['name']) {
            case 'info':
                $items = self::info($platform_id, $lang_id);
                break;

            case 'product':
                $suggest = 'index/search-suggest';
                break;

            case 'category':
                $items = self::category($platform_id, $lang_id);
                break;

            case 'location':
                $items = self::location($platform_id, $lang_id);
                break;

            case 'brand':
                $items = self::brand();
                break;

            case 'common':
                $items = self::common();
                break;

            case 'all':
                $items['info'] = ['title' => TEXT_PAGE_LINKS, 'links' => self::info($platform_id, $lang_id)];
                $items['product'] = ['title' => TEXT_PRODUCTS_LINKS, 'suggest' => 'index/search-suggest'];
                $items['category'] = ['title' => TEXT_CATEGORIES_LINKS, 'links' => self::category($platform_id, $lang_id)];
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryLocation', 'allowed')) {
                    $items['location'] = ['title' => TEXT_DELIVERY_LOCATION_LINKS, 'links' => self::location($platform_id, $lang_id)];
                }
                $items['brand'] = ['title' => TEXT_BRANDS, 'links' => self::brand()];
                $items['common'] = ['title' => COMMON_LINKS, 'links' => self::common()];
                break;
        }

        return ['items' => $items, 'suggest' => $suggest];
    }

    public static function info($platform_id, $languages_id)
    {
        $db_query = tep_db_query(
            "select * ".
            "from " . TABLE_INFORMATION . " ".
            "where languages_id='".$languages_id."' and platform_id=" . $platform_id . " and affiliate_id = 0 ".
            "order by v_order, info_title ");

        $items = [];

        if (tep_db_num_rows($db_query)>0){
            while( $val = tep_db_fetch_array($db_query) ) {
                $items['info/index?info_id=' . $val['information_id']] = $val['page_title'] ? $val['page_title'] : $val['info_title'];
            }
        }

        return $items;
    }

    public static function category($platform_id, $languages_id)
    {
        $items = [];

        $all_data = \common\helpers\Categories::get_category_tree(0,'','','',false,false, $platform_id, false, false, $languages_id);
        foreach ($all_data as $item) {
            $items['catalog/index?cPath=' . $item['id']] = $item['text'];
        }

        return $items;
    }

    public static function location($platform_id, $languages_id)
    {
        $items = [];
        if ( $ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryLocation', 'enabled') ){
            $items = $ext::htmlEditorLinkList($items, $platform_id, $languages_id);
        }
        return $items;
    }

    public static function brand()
    {
        $items = [];

        $brands = \common\helpers\MenuHelper::getBrandsList();

        foreach ($brands as $item) {
            $items['catalog/index?manufacturers_id=' . $item['manufacturers_id']] = $item['manufacturers_name'];
        }

        return $items;
    }

    public static function common()
    {
        $items = [
            'index/index' => TEXT_HOME,
            'account/login' => TEXT_SIGN_IN,
            'account/index' => TEXT_MY_ACCOUNT,
            'checkout/index' => TEXT_CHECKOUT,
            'shopping-cart/index' => TEXT_SHOPPING_CART,
            'catalog/products-new' => IMAGE_NEW_PRODUCT,
            'catalog/featured-products' => BOX_CATALOG_FEATURED,
            'catalog/sales' => TEXT_SPECIALS_PRODUCTS,
            'catalog/gift-card' => TEXT_GIFT_CARD,
            'catalog/all-products' => TEXT_ALL_PRODUCTS,
            'sitemap/index' => TEXT_SITE_MAP,
            'promotions/index' => BOX_PROMOTIONS,
        ];

        return $items;
    }
}