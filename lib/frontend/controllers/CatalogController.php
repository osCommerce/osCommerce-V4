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

use common\classes\events\frontend\attributes\productAttributesInfo\ProductAttributesInfoEvent;
use common\components\Customer;
use common\helpers\CategoriesDescriptionHelper;
use common\helpers\Manufacturers;
use common\models\Customers;
use common\models\DesignBoxesSettings;
use Yii;
use common\classes\Images;
use yii\base\ErrorException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use frontend\design\SplitPageResults;
use frontend\design\ListingSql;
use frontend\design\boxes\Listing;
use common\classes\design;
use common\helpers\Product;
use common\classes\platform;
use common\helpers\Sorting;
use common\models\Product as Products;
use common\helpers\Affiliate;
/*use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;*/

/**
 * Site controller
 */
class CatalogController extends Sceleton
{

    public function actionIndex()
    {
        global $_SESSION, $languages_id, $current_category_id, $navigation;
        global $breadcrumb;
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $manufacturers_id =  (int) \Yii::$app->request->get('manufacturers_id');

        if ($current_category_id > 0) {

            // Get the category name and description

            $groupJoin = false;
            $groupWhere = false;
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
                $groupJoin = true;
                $groupWhere = true;
            }
            $category = CategoriesDescriptionHelper::getCategoriesDescriptionList($current_category_id, $languages_id, Affiliate::id(), $customer_groups_id, $groupJoin, $groupWhere);
            if (!$category) {
                throw new NotFoundHttpException('Page not found.');
            }
            if ( is_array($category) ) {
                \common\helpers\Seo::showNoindexMetaTag($category['noindex_option'], $category['nofollow_option']);
                if (!empty($category['rel_canonical'])) {
                    \app\components\MetaCannonical::instance()->setCannonical($category['rel_canonical']);
                }else{
                    \app\components\MetaCannonical::instance()->setCannonical(['catalog/index','cPath'=>str_replace('cPath=','',\common\helpers\Categories::get_path($current_category_id))]);
                }
                $parent_categories = array($category['id']);
                \common\helpers\Categories::get_parent_categories($parent_categories, $parent_categories[0]);
                $bcPath = '';
                foreach(array_reverse($parent_categories) as $_cid){
                    $bcPath.=( !empty($bcPath)?'_':'').$_cid;
                    $breadcrumb->add(\common\helpers\Categories::get_categories_name($_cid), tep_href_link('catalog/index', 'cPath='.$bcPath, 'NONSSL'));
                }
            }

            $compareCategoryId = \common\helpers\Compare::getCategoryIdByCategory($current_category_id);
            Info::addJsData(['compare' => [
                'currentCategory' => [
                    'id' => $compareCategoryId,
                    'name' => \common\models\CategoriesDescription::findOne([
                        'categories_id' => $compareCategoryId,
                        'language_id' => $languages_id
                    ])->categories_name,
                ]
            ]]);

        } elseif ($manufacturers_id > 0) {

            // Get the manufacturer name and image
            $category = Manufacturers::getCategoriesDescriptionList($languages_id, $manufacturers_id);
            \app\components\MetaCannonical::instance()->setCannonical( ['catalog/index', 'manufacturers_id'=>$category['id']] );
            $breadcrumb->add($category['categories_name'], tep_href_link('catalog/index', 'manufacturers_id='.$category['id'], 'NONSSL'));

        }else{
            \app\components\MetaCannonical::instance()->setCannonical( '/' );
            return Yii::$app->runAction('index/index');
        }

        $category['img'] = Yii::$app->request->baseUrl . '/images/' . $category['categories_image'];
        if (!is_file(Yii::getAlias('@webroot') . '/images/' . $category['categories_image'])){
            $category['img'] = 'no';
        }

        if ($manufacturers_id > 0) {
          $category_p = 0;
          if (self::showFiltersOnBrandPage()){
            $noFiltersTo = 'products';
          }
        } else {
          $category_parent = \common\models\Categories::find()
              ->select(['count(*) AS total'])
              ->where(['parent_id' => (int)$current_category_id])
              ->andWhere(['categories_status' => 1])
              ->asArray()
              ->one();
          $category_p = $category_parent['total'];
        }

        if (!empty($noFiltersTo)) {
            $page_name = $noFiltersTo;
        } else {
            $page_name = \frontend\design\Categories::pageName($current_category_id, $category_p);
        }

        $search_results = Info::widgetSettings('Listing', 'items_on_page', $page_name);
        if (!$search_results) $search_results = SEARCH_RESULTS_1;

        $view = array();
        $view[] = $search_results * 1;
        $view[] = $search_results * 2;
        $view[] = $search_results * 4;
        $view[] = $search_results * 8;

        if (!tep_session_is_registered('customers_id'))	$navigation->set_snapshot();

        $q = new \common\components\ProductsQuery([
          'get' => \Yii::$app->request->get(),
          'hasSubcategories' => $category_p,
        ]);
        $cnt = $q->getCount();
        \Yii::$app->set('productsFilterQuery', $q);

        $params = array(
          'listing_split' => SplitPageResults::make($q->buildQuery()->rebuildByGroup()->getQuery(), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'*', 'page', $cnt)->withSeoRelLink(),
          'this_filename' => 'catalog'
        );

        if ( !Yii::$app->request->isAjax ) {
            $_SESSION['lastCategoryUrl'] = Yii::$app->urlManager->createUrl(array_merge(['catalog'], Yii::$app->request->get()));
        }

        $this->view->page_name = $page_name;

        /*$option = tep_db_fetch_array(tep_db_query("select noindex_option, nofollow_option, rel_canonical from categories_description where categories_id = '" . $current_category_id . "'"));
        \common\helpers\Seo::showNoindexMetaTag($option['noindex_option'], $option['nofollow_option']);
        if (!empty($option['rel_canonical'])) {
            \app\components\MetaCannonical::instance()->setCannonical($option['rel_canonical']);
        }*/
        $params['page_name'] = $page_name;

        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyFilter')) {
            if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'inFilters')) {
                if ($ext::allowed()) {
                    $widgetSettings = Info::widgetSettingsById(Yii::$app->request->get('widget_id'));
                    return $ext::inFilters($params, $widgetSettings);
                }
            }
        }

        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyProducts')) {
            $this->layout = false;
            return Listing::widget([
                'params' => $params,
                'settings' => array_merge(Info::widgetSettings('Listing', false, $page_name), ['onlyProducts' => true])
            ]);
        }

        if (Yii::$app->request->isAjax && Yii::$app->request->get('fbl')) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing', false, $page_name)]);
        }

        $params['banners_group'] = $category['banners_group'] ?? null;

        Info::addBlockToPageName($page_name);

        return $this->render('index.tpl', [
          'category' => $category,
          'category_parent' => $category_p,
          'params' => $params,
          'page_name' => $page_name
        ]);
    }

    private static function showFiltersOnBrandPage()
    {
        if($ext = \common\helpers\Acl::checkExtensionAllowed('ProductPropertiesFilters'))
        {
            return (method_exists($ext, 'optionShowFiltersOnBrandPage')) ? $ext::optionShowFiltersOnBrandPage() : (defined('ALWAYS_SHOW_FILTERS_ON_BRAND_PAGE') && constant('ALWAYS_SHOW_FILTERS_ON_BRAND_PAGE') == 'True');
        }
        return false;
    }

    public function actionListProduct()
    {
        if ( !Yii::$app->request->isAjax || !Yii::$app->request->get('products_id','') ){
            throw new BadRequestHttpException();
        }

        $extra_settings = [0=>[]];
        $listParam = Yii::$app->request->get('listParam','');
        $bundle_data = [];
        if ( preg_match('/bundle-(\d+)/',$listParam, $mm) ){
            $bundle_id = (int)$mm[1];
            $bundle_products_groups_id = \common\models\Products::find()->where(['products_id'=>$bundle_id])->select(['products_groups_id'])->scalar();
            $bundle_products_ids = [];
            $get_sss_ = tep_db_query(
                "select distinct bs.product_id ".
                "from ".TABLE_SETS_PRODUCTS." bs ".
                " inner join products p ON p.products_id=bs.sets_id ".
                "where p.products_groups_id='".$bundle_products_groups_id."' "
            );
            if ( tep_db_num_rows($get_sss_)>0 ){
                while ($get_ss = tep_db_fetch_array($get_sss_)){
                    $bundle_products_ids[] = $get_ss['product_id'];
                }
            }
            $extra_settings[0]['limit_group_products'] = $bundle_products_ids;
            $extra_settings[0]['hide_out_of_stock_groups'] = false;
            //$extra_settings[0]['hide_single_property'] = true;

            $get_group_products = tep_db_fetch_array(tep_db_query("select group_concat(products_id) as group_products from products where products_groups_id ='".(int)$bundle_products_groups_id."'"));
            $clicked_products_id = (int)Yii::$app->request->get('products_id','');
            $get_target_product_r = tep_db_query(" select sets_id from sets_products where sets_id IN('" . implode("','", explode(',', $get_group_products['group_products'])) . "') and product_id='" . (int)$clicked_products_id . "'");
            $bundle_pids = \Yii::$app->request->get('bundle_pids',[]);
            if ( count($bundle_pids)>1 ){
                $join_parts = '';
                foreach ( $bundle_pids as $__idx=>$bundle_child_pid ){
                    $__idx = (int)$__idx;
                    $join_parts .= " inner join sets_products bc{$__idx} on bc{$__idx}.sets_id=ps.sets_id AND bc{$__idx}.product_id='".(int)$bundle_child_pid."' ";
                }
                $get_exact_target_product_r = tep_db_query(
                    "select ps.sets_id ".
                    "from sets_products ps {$join_parts} ".
                    "where ps.sets_id IN('" . implode("','", explode(',', $get_group_products['group_products'])) . "') ".
                    "and ps.product_id='" . (int)$clicked_products_id . "'"
                );
                if ( tep_db_num_rows($get_exact_target_product_r)==1 ){
                    $get_target_product_r = $get_exact_target_product_r;
                }
            }
            $target_count = tep_db_num_rows($get_target_product_r);
            if ( tep_db_num_rows($get_target_product_r)>0 ){
                $get_target_product = tep_db_fetch_array($get_target_product_r);

                /*$bundlePriceObj = \common\models\Product\Price::getInstance($get_target_product['sets_id']);
                $bundle_price = $bundlePriceObj->getInventoryPrice(['qty' => 1]);
                $bundle_price_special = $bundlePriceObj->getInventorySpecialPrice(['qty' => 1]);*/

                $bundle_price_details = \common\helpers\Bundles::getDetails(['products_id' => $get_target_product['sets_id']]);
                if ($bundle_price_details['full_bundle_price_clear'] > $bundle_price_details['actual_bundle_price_clear']) {
                    $special = $bundle_price_details['actual_bundle_price'];
                    if (!empty($bundle_price_details['actual_bundle_price_ex'])) {
                        $special_ex = $bundle_price_details['actual_bundle_price_ex'];
                    }
                    $old = $bundle_price_details['full_bundle_price'];
                    if (!empty($bundle_price_details['full_bundle_price_ex'])) {
                        $old_ex = $bundle_price_details['full_bundle_price_ex'];
                    }
                    $current = '';
                } else {
                    $special = '';
                    $old = '';
                    $current = $bundle_price_details['actual_bundle_price'];
                    if (!empty($bundle_price_details['actual_bundle_price_ex'])) {
                        $current_ex = $bundle_price_details['actual_bundle_price_ex'];
                    }
                }
                $jsonPrice = $bundle_price_details['actual_bundle_price_clear'];

                $bundle_data = [
                    'bundleCell' => Yii::$app->request->get('bundleCell',''),
                    'name' => \common\helpers\Product::get_products_name($get_target_product['sets_id']),
                    'price_special' => $special,
                    'price_old' => $old,
                    'price_current' => $current,
                    'price_clear' => $jsonPrice,
                    'reload_url' => \yii\helpers\Url::to(['catalog/product','products_id'=>$get_target_product['sets_id']]),
                    'target_count' => $target_count,
                    'bundle_pids' => $bundle_pids,
                ];
            }
        }

        $q = new \common\components\ProductsQuery([
            'get' => \Yii::$app->request->get(),
        ]);
		$get_params = \Yii::$app->request->get();
        $cnt = $q->getCount();
        \Yii::$app->set('productsFilterQuery', $q);

        $page_name = 'products';
        $params['page_name'] = $page_name;

        $search_results = 10;
        $params = array(
            'listing_split' => SplitPageResults::make($q->buildQuery()->getQuery(), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'*', 'page', $cnt)->withSeoRelLink(),
            'this_filename' => 'catalog'
        );
        $settings = array_merge(Info::widgetSettings('Listing', false, $page_name), ['onlyProducts' => true]);
        if ($boxId = \Yii::$app->request->get('boxId','') ){
            $boxId = preg_replace('/[^\d]/','',$boxId);
            $listSetting = Info::widgetSettingsById($boxId, false);
            if ( !empty($listSetting) && is_array($listSetting) ) {
                $settings = array_merge($listSetting, ['onlyProducts' => true]);
            }
        }

        //$settings[0]['listing_type'] = 'cross-sell-0';
		$settings[0]['listing_type'] = $get_params['listType'];
        $settings[0] = array_merge($settings[0],$extra_settings[0]);
        $this->layout = false;
        $response = Listing::widget([
            'params' => $params,
            'settings' => $settings,
        ]);
        if ( $bundle_data && is_string($response) && substr($response,0,1)=='{' ){
            if($resp_array = \json_decode($response,true)){
                $resp_array['bundle_data'] = $bundle_data;
                $response = \json_encode($resp_array);
            }
        }
        return $response;
        return Listing::widget([
            'params' => $params,
            'settings' => $settings,
        ]);
    }

    public function actionProduct()
    {
        global $breadcrumb, $cPath_array, $languages_id, $navigation;


        $params = Yii::$app->request->get();

        if (!isset($_SESSION['viewed_products']) || !is_array($_SESSION['viewed_products'])){
            $_SESSION['viewed_products'] = [];
        }

        (new \common\components\Popularity())->updateProductVisit($params['products_id']);

        /*if ( isset($_SESSION['viewed_products'][(int)$params['products_id']]) ) {
            unset($_SESSION['viewed_products'][(int)$params['products_id']]);
        }
        $_SESSION['viewed_products'][(int)$params['products_id']] = (int)$params['products_id'];
        */
        if ( count($_SESSION['viewed_products'])>40 ) {
            // {{ fastest way remove first pid
            reset($_SESSION['viewed_products']);
            $_removeProductId = current($_SESSION['viewed_products']);
            unset($_SESSION['viewed_products'][$_removeProductId]);
            // }} fastest way remove first pid
        }

        $check_status = 1;
        if (Info::isAdmin()){
                $check_status = 0;
        }
        if ( !isset($params['products_id']) || !Product::check_product($params['products_id'], $check_status, true) ) {
            throw new NotFoundHttpException('Page not found.');
        }
        if ($extClass = \common\helpers\Acl::checkExtensionAllowed('ObsoleteProducts', 'allowed')) {
            if ($redirectURL = $extClass::getRedirectObsoleteProductURL($params['products_id'])) {
                return $this->redirect($redirectURL);
            }
        }
        $message = '';
        if (isset($_SESSION['product_info'])) {
            $message = $_SESSION['product_info'];
            unset($_SESSION['product_info']);
        }
        $products = Yii::$container->get('products');
        $product = $products->loadProducts(['products_id' => $params['products_id']])->getProduct($params['products_id']);

        $review_write_now = 0;
        if ( Yii::$app->request->getPathInfo()=='reviews/write' ) {
            $review_write_now = 1;
        }else{
            if (!is_array($cPath_array)) {
                $cPath_array = explode("_", Product::get_product_path($params['products_id'], 1, true));
            }
            if (isset($cPath_array)) {
                for ($i=0, $n=sizeof($cPath_array); $i<$n; $i++) {
                    $categories_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$cPath_array[$i] . "' and language_id = '" . (int)$languages_id . "'");
                    if (tep_db_num_rows($categories_query) > 0) {
                        $categories = tep_db_fetch_array($categories_query);
                        //$breadcrumb->add($categories['categories_name'], tep_href_link(FILENAME_DEFAULT, 'cPath=' . implode('_', array_slice($cPath_array, 0, ($i+1)))));
			$breadcrumb->add($categories['categories_name'], tep_href_link('catalog/index', 'cPath='.implode('_', array_slice($cPath_array, 0, ($i+1)))));
                    } else {
                        break;
                    }
                }
            }
            $breadcrumb->add($product['products_name'],tep_href_link(FILENAME_PRODUCT_INFO,'products_id='.$params['products_id']));
        }

        $compareCategoryId = \common\helpers\Compare::getCategoryId($params['products_id']);
        Info::addJsData(['compare' => [
            'currentCategory' => [
                'id' => $compareCategoryId,
                'name' => \common\models\CategoriesDescription::findOne([
                    'categories_id' => $compareCategoryId,
                    'language_id' => $languages_id
                ])->categories_name ?? null,
            ]
        ]]);

        if (!tep_session_is_registered('customers_id'))	$navigation->set_snapshot();

        global $cart;
        if ($cart->in_cart($params['products_id'])/*Info::checkProductInCart($params['products_id'])*/){
            $message .= '<div>' . \common\helpers\Product::getVirtualItemQuantity($params['products_id'], $cart->in_cart($params['products_id'])) . ' ' . TEXT_ADDED_1 . ' <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '">' . TEXT_ADDED_2 . '</a>. <a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING) . '">' . TEXT_ADDED_3 . '</a>. ' . TEXT_ADDED_4 . '</div>';
        }

        $product_in_orders = 0;
        if (!Yii::$app->user->isGuest){
            $query = tep_db_fetch_array(tep_db_query("select count(op.products_id) as in_orders from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op where o.orders_id = op.orders_id and o.customers_id = " . (int)Yii::$app->user->getId() . " and op.products_id = '" . (int)$params['products_id'] . "'"));
            $product_in_orders = $query['in_orders'];
            if ($product_in_orders == 1){
                $message .= '<div>' . TEXT_YOU_BOUGHT_THIS_ITEM . '</div>';
            }
            if ($product_in_orders > 1){
                $message .= '<div>' . TEXT_PURCHASED_MORE_THAN . '</div>';
            }
        }


        \Yii::$app->getView()->registerMetaTag([
            'property' => 'og:type',
            'content' => 'product'
        ],'og:type');
        \Yii::$app->getView()->registerMetaTag([
            'property' => 'og:url',
            'content' => tep_href_link('catalog/product', 'products_id=' . $params['products_id'])
        ],'og:url');

        switch ($product['jsonld_product_type']) {
            case 1: $jsonldProductType = 'Product'; break;
            case 2: $jsonldProductType = 'Service'; break;
            default: $jsonldProductType = JSONLD_PRODUCT_TYPE;
        }
        \frontend\design\JsonLd::addData(['Product' => [
            '@type' => $jsonldProductType
        ]]);

        $page_name = \frontend\design\Product::pageName($params['products_id'], $cPath_array);
        $this->view->page_name = $page_name;

        \common\helpers\Seo::showNoindexMetaTag($product['noindex_option'], $product['nofollow_option']);
        if (!empty($product['rel_canonical'])) {
            \app\components\MetaCannonical::instance()->setCannonical($product['rel_canonical']);
        }else{
            \app\components\MetaCannonical::instance()->setCannonical(['catalog/product','products_id'=>(int)$params['products_id']]);
        }

        \common\components\google\widgets\GoogleTagmanger::setEvent('productPage');

        foreach (\common\helpers\Hooks::getList('frontend/catalog/product') as $filename) {
            include($filename);
        }

        Info::addBlockToPageName($page_name);

        return $this->render('product.tpl', [
          'action' => tep_href_link('catalog/product', \common\helpers\Output::get_all_get_params(array('action')) . 'action=add_product'),
          'products_id' => $params['products_id'],
          'products_prid' => \common\helpers\Inventory::get_prid($params['products_id']),
          'review_write_now' => $review_write_now,
          'message' => $message,
          'page_name' => $page_name
        ]);
    }

    public function actionProductAttributes($boxId = 0)
    {
        \common\helpers\Translation::init('catalog/product');

        $params = tep_db_prepare_input(Yii::$app->request->get());
        $products_id = tep_db_prepare_input(Yii::$app->request->get('products_id', 0));
        $attributes = tep_db_prepare_input(Yii::$app->request->get('id', array()));
        $type = Yii::$app->request->get('type', 'product');
        $boxId = Yii::$app->request->get('boxId', $boxId);
        $attrText = Yii::$app->request->get('attr_text');
        $options_prefix = '';
        if (empty($products_id)) {
          return false;
        }

        if ($type=='listing' || $type == 'productListing') {
          $listid = tep_db_prepare_input(Yii::$app->request->get('listid', array()));
          if (!empty($listid[$products_id])) {
            $attributes = $listid[$products_id];
            $options_prefix = 'list';
          } elseif (!empty($listid)) {
            $attributes = $listid;
            $options_prefix = 'list';
          }
          if (!empty($params['listqty']) && is_array($params['listqty']) && count($params['listqty'])==1) {
            $params['qty'] = $params['listqty'][0];
            unset($params['listqty']);
          }
        }

        if (!$attributes && strpos($products_id, '{') !== false){
            \common\helpers\Inventory::normalize_id($products_id, $attributes);
        }

        global $cPath_array;

        $page_name = Yii::$app->request->get('page_name');
        if (!$page_name) {
            $page_name = \frontend\design\Product::pageName($params['products_id'], $cPath_array);
            $this->view->page_name = $page_name;
        }

        $noAttr = false;
        if (is_array($attributes) && count($attributes) == 0) {
            $noAttr = true;
        }

        $details = \common\helpers\Attributes::getDetails($products_id, $attributes, $params);

        if ($noAttr) {
            $runAttributes = false;
            foreach ($details['attributes_array'] as $attr) {
                if ( $attr['selected'] ) {
                    $attributes[$attr['id']] = $attr['selected'];
                }else {
                    $attributes[$attr['id']] = $attr['options'][0]['id'];
                }
            }
            if ( $runAttributes ) {
                $details = \common\helpers\Attributes::getDetails($products_id, $attributes, $params);
            }
        }

        if (defined('CONDITION_2LEVEL_ATTRIBUTE_SELECTION') && CONDITION_2LEVEL_ATTRIBUTE_SELECTION > 0) {
            $details['attributes_array'] = \common\helpers\Attributes::add2LevelAttributeOptions($details['attributes_array']);
        }

        if ($productDesigner = \common\helpers\Acl::checkExtensionAllowed('ProductDesigner', 'allowed')){
            $productDesigner::productAttributes($details, $attributes, Yii::$app->request->get());
        }

        Yii::$container->get('products')->loadProducts(['products_id' => $details['current_uprid']])
                ->attachDetails($details['current_uprid'], ['attributes_array' => $details['attributes_array']]);
        try {
            $event = new ProductAttributesInfoEvent(
                $details,
                Yii::$app->user->isGuest ? false : \Yii::$app->user->getId()
            );
            \Yii::$container->get('eventDispatcher')->dispatch($event);
            $details = $event->getProductAttributes();
        } catch (\Exception $e) {
            \Yii::error($e->getMessage());
        }

        if ($type == 'product' ){
            if ( $boxId ){
                $settings = \frontend\design\Info::widgetSettingsById($boxId);
            } else {
                $settings = \frontend\design\Info::widgetSettings('product\MultiInventory');
            }
        } else {
            $settings = \frontend\design\Info::widgetSettings('productListing\attributes', false, 'row');
        }

        $product = Yii::$container->get('products')->getProduct($details['current_uprid']);
        if (Yii::$app->request->isAjax) {
            $use_attributes_quantity = true;
            if ( $boxId ){
                $settingsBlock = \frontend\design\Info::widgetSettingsById($boxId);
                if ( !empty($settingsBlock[0]['force_disable_attributes_quantity'])){
                    $use_attributes_quantity = false;
                }
                if ($type != 'product' &&  !empty($settingsBlock[0]) ) {
                    $list_type = \frontend\design\Info::listType($settingsBlock[0]);
                    $settings = \frontend\design\Info::widgetSettings('productListing\attributes', false, $list_type);
                }
            }

            if ($use_attributes_quantity && $product && ($product['settings']->show_attributes_quantity ?? null) && \common\helpers\Extensions::isAllowed('Inventory')){
                return \frontend\design\boxes\product\MultiInventory::widget();
            } else {
                if ($ext = \common\helpers\Extensions::isAllowed('SupplierPurchase')) {
                    $details['sp_collection'] = $ext::getSpCollection($details['current_uprid']);
                }
                $details['image_widget'] = \frontend\design\boxes\product\Images::widget(['params'=>['uprid'=>$details['current_uprid']], 'settings' => \frontend\design\Info::widgetSettings('product\Images', false, 'product')]);
                $details['images'] = \frontend\design\Info::$jsGlobalData['products'][$products_id]['images'];
                $details['defaultImage'] = \frontend\design\Info::$jsGlobalData['products'][$products_id]['defaultImage'];
                $details['productId'] = $products_id;
                if ($type == 'productListing'){
                    $details['product_attributes'] = \frontend\design\IncludeTpl::widget([
                        'file' => 'boxes/listing-product/element/attributes.tpl',
                        'params' => [
                            'product' => [
                                'product_attributes_details' => $details,
                                'product_has_attributes' => true,
                                'products_id' => $products_id,
                            ],
                        'settings' => $settings[0]??[]
                        ]
                    ]);
                } elseif ($type == 'listing'){
                    $details['product_attributes'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/listing-product/attributes.tpl', 'params' => ['attributes' => $details['attributes_array'], 'isAjax' => true, 'products_id' => $products_id, 'options_prefix' => $options_prefix, 'settings' => $settings[0]??[]]]);
                } else {
                    $details['product_attributes'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/attributes.tpl', 'params' => ['attributes' => $details['attributes_array'], 'isAjax' => true, 'settings' => $settings[0]??[], 'boxId' => $boxId??'', 'attrText' => $attrText]]);
                }
                $details['product_name'] = $product['products_name'];

                /**
                 * @var $ext \common\extensions\FlexiFi\FlexiFi
                 */
                if ($ext = \common\helpers\Extensions::isAllowed('FlexiFi')) {
                    $details['flexifi_credit_plan_button'] = $ext::getPopupButtonHtml((['products_id' => $details['current_uprid']] + $details), $details['product_unit_price'], true);
                } else {
                    $details['flexifi_credit_plan_button'] = '';
                }
                return json_encode($details);
            }
        } else {
            if (count($details['attributes_array']) > 0) {
                return IncludeTpl::widget(['file' => 'boxes/product/attributes.tpl', 'params' => ['attributes' => $details['attributes_array'], 'isAjax' => false, 'product' => $product, 'settings' => $settings[0]??[], 'boxId' => $boxId??'', 'attrText' => $attrText]]);
            } else {
                return '';
            }
        }
    }

    public function actionProductNotify()
    {
        \common\helpers\Translation::init('catalog/product');

        $params = tep_db_prepare_input(Yii::$app->request->get());
        $suppliers_id = (int) ($params['supplier_id']??0);
        // Inventory widget bof
        if (!empty($params['uprid'])) {
            if (strpos($params['uprid'], '{') !== false) {
                $attrib = array();
                $ar = preg_split('/[\{\}]/', $params['uprid']);
                for ($i = 1; $i < sizeof($ar); $i = $i + 2) {
                    if (isset($ar[$i + 1])) {
                        $attrib[$ar[$i]] = $ar[$i + 1];
                    }
                }
                $params['id'] = $attrib;
            }
        }
        // Inventory widget eof
        $uprid = tep_db_input(\common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($params['products_id'], $params['id']??null)));
        if (empty($params['id'])) {
            $check_item = tep_db_fetch_array(tep_db_query("select products_id, products_quantity from " . TABLE_PRODUCTS . " where products_id = '{$uprid}' limit 1"));
            $out_of_stock = $check_item['products_id'] && !\common\helpers\Product::isAvailableForSaleNow($uprid, platform::currentId(), 0, $suppliers_id);
            $item_found = $check_item['products_id'];
        } else {
            $check_item = tep_db_fetch_array(tep_db_query("select inventory_id, products_quantity from " . TABLE_INVENTORY . " where products_id like '{$uprid}' limit 1"));
            $out_of_stock = $check_item['inventory_id'] && !\common\helpers\Product::isAvailableForSaleNow($uprid, platform::currentId(), 0, $suppliers_id);
            $item_found = $check_item['inventory_id'];
        }
        if ($out_of_stock) {
            $products_notify_name = tep_db_input(tep_db_prepare_input($params['name']));
            $products_notify_email = tep_db_input(tep_db_prepare_input($params['email']));
            //$check_notify = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS_NOTIFY . " where products_notify_products_id like '{$uprid}' and products_notify_email = '{$products_notify_email}' and suppliers_id = '{$suppliers_id}' limit 1"));
            $check_notify = \common\models\ProductsNotify::find()
                ->select('products_notify_id')
                ->andWhere([
                  'products_notify_products_id' => $uprid,
                  'products_notify_email' => $products_notify_email,
                  'suppliers_id' => $suppliers_id
                ])
                ->andWhere('products_notify_sent is null')
                ->one();
            if (empty($check_notify['products_notify_id'])) {
                tep_db_query("insert into " . TABLE_PRODUCTS_NOTIFY . " set products_notify_products_id = '{$uprid}', products_notify_email = '{$products_notify_email}', products_notify_name = '{$products_notify_name}', products_notify_customers_id = '". Yii::$app->user->getId()."', suppliers_id = '{$suppliers_id}', platform_id = '" . (int)platform::currentId() . "', products_notify_date = now(), products_notify_sent = null");
                return YOU_WILL_BE_NOTIFIED;
            } else {
                return YOU_ALREADY_GOT_NOTIFY;
            }
        } else {
            return ($item_found ? ITEM_IS_IN_STOCK : ITEM_NOT_FOUND);
        }
    }

    public function actionProductRequestForQuote()
    {
        $messageStack = \Yii::$container->get('message_stack');

        \common\helpers\Translation::init('catalog/product');

        $params = tep_db_prepare_input(Yii::$app->request->post());
        if ( !Yii::$app->user->isGuest ) {
          $customer_info = tep_db_fetch_array(tep_db_query(
            "SELECT customers_firstname, customers_email_address FROM ".TABLE_CUSTOMERS." WHERE customers_id='".(int)Yii::$app->user->getId()."'"
          ));
          $customers_name = $customer_info['customers_firstname'];
          $customers_email = $customer_info['customers_email_address'];
        }else{
          $customers_name = $params['name'] ?? null;
          $customers_email = $params['email'] ?? null;
        }

        $check_error = false;
        if (strlen($customers_name) < ENTRY_FIRST_NAME_MIN_LENGTH) {
          $check_error = true;
          $messageStack->add(sprintf(NAME_IS_TOO_SHORT, ENTRY_FIRST_NAME_MIN_LENGTH), 'rfq_send');
        }
        if ( empty($customers_email) || !\common\helpers\Validations::validate_email($customers_email) ) {
          $check_error = true;
          $messageStack->add(ENTER_VALID_EMAIL, 'rfq_send');
        }
        if ( empty($params['message']) ) {
          $check_error = true;
          $messageStack->add(REQUEST_MESSAGE_IS_TOO_SHORT, 'rfq_send');
        }
        if ( $check_error ) {
          return $messageStack->output('rfq_send');
        }else {
          $uprid = tep_db_input(\common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($params['products_id'], $params['id'])));
          $product_name = Product::get_products_name($uprid);
          if ( strpos($uprid,'{')!==false ) {
            $check_item = tep_db_fetch_array(tep_db_query("select products_name from " . TABLE_INVENTORY . " where products_id like '{$uprid}' limit 1"));
            if( !empty($check_item['products_name']) ) {
              $product_name = $check_item['products_name'];
            }
          }


          $email_params = array();
          $email_params['STORE_NAME'] = STORE_NAME;
          $email_params['STORE_OWNER_EMAIL_ADDRESS'] = STORE_OWNER_EMAIL_ADDRESS;
          $email_params['CUSTOMER_NAME'] = $customers_name;
          $email_params['CUSTOMER_EMAIL'] = $customers_email;
          $email_params['PRODUCT_NAME'] = $product_name;
          $email_params['PRODUCT_URL'] = tep_href_link('catalog/product', 'products_id=' . $uprid);
          $email_params['REQUEST_MESSAGE'] = $params['message'];

          list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Request for quote', $email_params);

          \common\helpers\Mail::send(
            STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS,
            $email_subject, $email_text,
            $customers_name, $customers_email
          );

          return REQUEST_FOR_QUOTE_MESSAGE_SENT;
        }
    }

    public function actionSearch()
    {

        return '';
    }


    public function actionSearchSuggest()
    {
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $languages_id = \Yii::$app->settings->get('languages_id');
        $currency_id = \Yii::$app->settings->get('currency_id');
        $response = array();

        if (isset($_GET['keywords']) && $_GET['keywords'] != '') {

            $searchBuilder = new \common\components\SearchBuilder('complex');

            $keywords = \common\helpers\Output::output_string($_GET['keywords']);

            $searchBuilder->setSearchInDesc(SEARCH_IN_DESCRIPTION == 'True');
            $searchBuilder->prepareRequest($keywords);


            $_SESSION['keywords'] = $keywords;
            //Add slashes to any quotes to avoid SQL problems.
            // $search = preg_replace("/\//",'',tep_db_input(tep_db_prepare_input($_GET['keywords'])));  //???
            $search = $keywords;

            $replace_keywords = $searchBuilder->replaceWords;

            $sql_manufacturers = "select *, if(position('" . tep_db_input($search) . "' IN manufacturers_name), position('" . tep_db_input($search) . "' IN manufacturers_name), 100) as pos from " . TABLE_MANUFACTURERS . " where 1 " . $searchBuilder->getManufacturersArray() . " order by pos limit 0, 3";

            $sql_information = "select i.information_id, if(length(i1.info_title), i1.info_title, i.info_title) as info_title,  (if(length(i1.info_title), if(position('" . tep_db_input($search) . "' IN i1.info_title), position('" . tep_db_input($search) . "' IN i1.info_title), 100), if(position('" . tep_db_input($search) . "' IN i.info_title), position('" . tep_db_input($search) . "' IN i.info_title), 100))) as pos, 1 as is_category  from " . TABLE_INFORMATION . " i LEFT join " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id ".(platform::activeId()?" AND i1.platform_id='".platform::currentId()."' ":'')." and i1.affiliate_id = '" . Affiliate::id() . "' and i1.languages_id='" . $languages_id ."'  where i.visible = 1 " . (Affiliate::id()>0?" and i1.affiliate_id is not null ":'') . " and i.affiliate_id = 0 ".(platform::activeId()?" AND i.platform_id='".platform::currentId()."' ":'')." and i.languages_id = '" . $languages_id . "' " . $searchBuilder->getInformationsArray() . " order by pos limit 0, 3" ;

            $ssboDef = ['Products', 'Categories', 'Manufacturers', 'Information'];
            $ssbo = [];
            if (defined('SEARCH_SUGGEST_BLOCKS_ORDER')) {
              $tmp = array_map('trim', explode(',', constant('SEARCH_SUGGEST_BLOCKS_ORDER')));
              foreach ($tmp as $t) {
                if (in_array($t, $ssboDef)) {
                  $ssbo[] = $t;
                }
              }
            }
            if (empty($ssbo)) {
              $ssbo = $ssboDef;
            }


            $mResponse = [];
            if (in_array('Manufacturers', $ssbo)) {
              $manufacturers_query = tep_db_query($sql_manufacturers);
              while ($manufacturers_array = tep_db_fetch_array($manufacturers_query)) {
                  $mResponse[] = array(
                      'type' => BOX_HEADING_MANUFACTURERS,
                      'type_class' => 'brands',
                      'link' => tep_href_link('catalog', 'manufacturers_id=' . $manufacturers_array['manufacturers_id']),
                      'image' => DIR_WS_IMAGES . $manufacturers_array['manufacturers_image'],
                      'title' => \common\helpers\Output::highlight_text(strip_tags($manufacturers_array['manufacturers_name']), $replace_keywords),
                  );
              }
            }

            $iResponse = [];
            if (in_array('Information', $ssbo)) {
              $info_query = tep_db_query($sql_information);
              while ($info_array = tep_db_fetch_array($info_query)) {
                  $iResponse[] = array(
                    'type' => TEXT_INFORMATION,
                    'type_class' => 'info-suggest',
                    'link' => tep_href_link('info', 'info_id=' . $info_array['information_id']),
                    'title' => \common\helpers\Output::highlight_text(strip_tags($info_array['info_title']), $replace_keywords),
                  );
              }
            }

            $cResponse = [];
            if (in_array('Categories', $ssbo)) {
              $cArray = \common\helpers\Categories::searchCategoryTreePlain($keywords, 0);
              foreach($cArray  as $info_array) {
                  $cResponse[] = array(
                    'type' => TEXT_CATEGORIES,
                    'type_class' => 'categories',
                    'link' => tep_href_link('catalog', 'cPath=' . $info_array['cPath']),
                    'title' => \common\helpers\Output::highlight_text(strip_tags($info_array['text']), $replace_keywords),
                    'extra' =>  (!empty($info_array['parents']) && is_array($info_array['parents'])?
                            '<span class="brackets">(</span>' . implode('<span class="comma-sep">, </span>', \yii\helpers\ArrayHelper::getColumn($info_array['parents'], 'text')) .
                            '<span class="brackets">)</span>':''),
                  );
              }
            }

            $pResponse = [];
            if (in_array('Products', $ssbo)) {
              $q = new \common\components\ProductsQuery([
                'filters' => ['keywords' => $keywords],
                'limit' => 10,
              ]);

              $products = Info::getListProductsDetails($q->buildQuery()->allIds());
              /** @var \common\extensions\SearchPlus\SearchPlus $ext  */
              if ($ext = \common\helpers\Extensions::isAllowed('SearchPlus')) {
                  $ext::saveStat(\common\helpers\Output::output_string($_GET['keywords']), count($products));
              }
              foreach ($products as $product_array) {
                  $pResponse[] = array(
                    'type' => TEXT_PRODUCTS,
                    'type_class' => 'products',
                    'link' => tep_href_link('catalog/product', 'products_id=' . $product_array['products_id']),
                    'image' => Images::getImageUrl($product_array['products_id'], 'Small'),
                    'title' => \common\helpers\Output::highlight_text(strip_tags($product_array['products_name']), $replace_keywords),
                  );
              }
            }

            foreach ($ssbo as $t) {
              switch ($t) {
                case 'Products':
                  $response = array_merge($response, $pResponse);
                  break;
                case 'Manufacturers':
                  $response = array_merge($response, $mResponse);
                  break;
                case 'Information':
                  $response = array_merge($response, $iResponse);
                  break;
                case 'Categories':
                  $response = array_merge($response, $cResponse);
                  break;
              }
            }

            foreach (\common\helpers\Hooks::getList('catalog/search-suggest') as $filename) {
                include($filename);
            }
            foreach (\common\helpers\Hooks::getList('frontend/catalog/search-suggest') as $filename) {
                include($filename);
            }
        }

        return $this->render('search.tpl', ['list' => $response]);
    }

    public function actionSpecials(){
        return $this->redirect(Yii::$app->urlManager->createUrl(["catalog/sales"])) ;
    }

    public function actionSales(){
        global $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE,tep_href_link(FILENAME_SPECIALS));

        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) $search_results = SEARCH_RESULTS_1;

        $q = new \common\components\ProductsQuery([
          'get' => \Yii::$app->request->get(),
          'page' => 'sales'
        ]);
        $cnt = $q->getCount();
        \Yii::$app->set('productsFilterQuery', $q);

        $params = array(
          'listing_split' => SplitPageResults::make($q->buildQuery()->rebuildByGroup()->getQuery(), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'*', 'page', $cnt)->withSeoRelLink(),
          'this_filename' => FILENAME_SPECIALS
          /*,
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),*/
        );
        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyFilter')) {
            if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'inFilters')) {
                if ($ext::allowed()) {
                    return $ext::inFilters($params, []);
                }
            }
        }

        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyProducts')) {
            $this->layout = false;
            return Listing::widget([
                'params' => $params,
                'settings' => array_merge(Info::widgetSettings('Listing', false, $page_name??null), ['onlyProducts' => true])
            ]);
        }
        $params['page_name'] = 'products';

        if (Yii::$app->request->isAjax && Yii::$app->request->get('fbl')) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }
      return $this->render('specials.tpl', ['params' => ['params'=>$params]]);
		}

    public function actionFeaturedProducts(){
        global $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_FEATURED_PRODUCTS));

        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) $search_results = SEARCH_RESULTS_1;

        $q = new \common\components\ProductsQuery([
          'get' => \Yii::$app->request->get(),
          'page' => 'featured',
          'orderBy' => ['fake' => 1],
        ]);

        $cnt = $q->getCount();
        \Yii::$app->set('productsFilterQuery', $q);
        $s = $q->buildQuery()->rebuildByGroup()->getQuery()->orderBy("featured.sort_order, featured.featured_date_added");
        $params = array(
          'listing_split' => SplitPageResults::make($s, (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'*', 'page', $cnt)->withSeoRelLink(),
          'this_filename' => FILENAME_FEATURED_PRODUCTS
          /*,
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),*/
        );
        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyFilter')) {
            if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'inFilters')) {
                if ($ext::allowed()) {
                    return $ext::inFilters($params, []);
                }
            }
        }
        $params['page_name'] = 'products';

        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyProducts')) {
            $this->layout = false;
            return Listing::widget([
                'params' => $params,
                'settings' => array_merge(Info::widgetSettings('Listing', false, $page_name ?? null), ['onlyProducts' => true])
            ]);
        }

        if (Yii::$app->request->get('fbl')) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }
        return $this->render('featured-products.tpl', ['params' => ['params'=>$params]]);
    }

    public function actionProductsNew(){
        global $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE,tep_href_link(FILENAME_PRODUCTS_NEW));

        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) {
          $search_results = SEARCH_RESULTS_1;
        }

        if (defined('NEW_MARK_UNTIL_DAYS') && intval(constant('NEW_MARK_UNTIL_DAYS'))>0) {
          $customAndWhere = ['>=', 'p.products_new_until', 'now()'];
        } else {
          $customAndWhere = false;
        }

        $q = new \common\components\ProductsQuery([
          'orderBy' => ['products_date_added' => SORT_DESC],
          'get' => \Yii::$app->request->get(),
          'customAndWhere' => $customAndWhere
        ]);
        $cnt = $q->getCount();
        \Yii::$app->set('productsFilterQuery', $q);

        $params = array(
          'listing_split' => SplitPageResults::make($q->buildQuery()->rebuildByGroup()->getQuery(), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'*', 'page', $cnt)->withSeoRelLink(),
          'this_filename' => FILENAME_PRODUCTS_NEW,
          'sorting_id' => \Yii::$app->request->get('sort') ?? 'dd',
        );
        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyFilter')) {
            if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'inFilters')) {
                if ($ext::allowed()) {
                    return $ext::inFilters($params, []);
                }
            }
        }
        $params['page_name'] = 'products';

        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyProducts')) {
            $this->layout = false;
            return Listing::widget([
                'params' => $params,
                'settings' => array_merge(Info::widgetSettings('Listing', false, $page_name ?? null), ['onlyProducts' => true])
            ]);
        }

        if (Yii::$app->request->isAjax && Yii::$app->request->get('fbl')) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }
        return $this->render('products-new.tpl', ['params' => ['params'=>$params]]);
    }

    public function actionAllProducts(){
        global $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE,tep_href_link(FILENAME_ALL_PRODUCTS));

        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) {
          $search_results = SEARCH_RESULTS_1;
        }

        $q = new \common\components\ProductsQuery([
          'get' => \Yii::$app->request->get(),
        ]);
        $cnt = $q->getCount();
        \Yii::$app->set('productsFilterQuery', $q);

        $params = array(
          'listing_split' => SplitPageResults::make($q->buildQuery()->rebuildByGroup()->getQuery(), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'*', 'page', $cnt)->withSeoRelLink(),
          'this_filename' => FILENAME_ALL_PRODUCTS
          /*,
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),*/
        );

        $page_name = filter_var(Yii::$app->request->get('page_name', ''), FILTER_SANITIZE_STRING);
        if (empty($page_name)) {
            $page_name = 'products';
        }
        $params['page_name'] = $page_name;

        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyFilter')) {
            if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'inFilters')) {
                if ($ext::allowed()) {
                    return $ext::inFilters($params, Info::widgetSettings('Filters'));
                }
            }
        }

        if (Yii::$app->request->isAjax && Yii::$app->request->get('onlyProducts')) {
            $this->layout = false;
            return Listing::widget([
                'params' => $params,
                'settings' => array_merge(Info::widgetSettings('Listing', false, $page_name), ['onlyProducts' => true])
            ]);
        }

        $keywords = filter_var(Yii::$app->request->get('keywords', ''), FILTER_SANITIZE_STRING);
        if (!empty($keywords)) {
          /** @var \common\extensions\SearchPlus\SearchPlus $ext  */
          if ($ext = \common\helpers\Acl::checkExtension('SearchPlus', 'saveStat')) {
            if ($ext::allowed()) {
              $ext::saveStat(tep_db_input(tep_db_prepare_input(strip_tags($keywords))), $params['listing_split']->number_of_rows);
            }
          }
        }

        if (Yii::$app->request->isAjax && Yii::$app->request->get('fbl')) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }

        return $this->render('all-products.tpl', [
            'page_name' => $page_name,
            'params' => [
                'params'=>$params,
                'type' => 'catalog',
            ]]);
    }

    public function actionAdvancedSearch()
    {
        global $breadcrumb, $languages_id;
        $messageStack = \Yii::$container->get('message_stack');
        $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_ADVANCED_SEARCH));

        $messages_search = '';
        if ($messageStack->size('search') > 0) {
            $messages_search = $messageStack->output('search');
        }
        $controls = array(
          'keywords' => tep_draw_input_field('keywords', '', ''),
          'search_in_description' => tep_draw_checkbox_field('search_in_description', '1', SEARCH_IN_DESCRIPTION == 'True', 'id="search_in_description"'),
          'categories' => tep_draw_pull_down_menu('categories_id', \common\helpers\Categories::get_categories(array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES)))),
           'inc_subcat' => tep_draw_checkbox_field('inc_subcat', '1', true, 'id="include_subcategories"'),
           'manufacturers' => '',
           'price_from' => tep_draw_input_field('pfrom'),
           'price_to' => tep_draw_input_field('pto'),
           //'date_from' => tep_draw_input_field('dfrom', '', 'placeholder="' . \common\helpers\Output::output_string(DOB_FORMAT_STRING) . '"'),
           //'date_to' => tep_draw_input_field('dto', '', 'placeholder="' . \common\helpers\Output::output_string(DOB_FORMAT_STRING) . '"'),
        );

        $site_manufacturers = \common\helpers\Manufacturers::get_manufacturers();
        if ( count($site_manufacturers)>0 ) {
           $site_manufacturers = array_merge(array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS)), $site_manufacturers);
           $controls['manufacturers'] = tep_draw_pull_down_menu('manufacturers_id', $site_manufacturers);
        }

        $searchable_properties = array();
        if (PRODUCTS_PROPERTIES == 'True') {

			$p_types = array_keys(\common\helpers\PropertiesTypes::getTypes('search'));

            $properties_yes_no_array = array(array('id' => '', 'text' => OPTION_NONE), array('id' => 'true', 'text' => OPTION_TRUE), array('id' => 'false', 'text' => OPTION_FALSE));
            $properties_query = tep_db_query("select pr.properties_id, pr.properties_type, prd.properties_name, prd.properties_description, pr.multi_choice, pr.decimals from " . TABLE_PROPERTIES_DESCRIPTION . " prd, " . TABLE_PROPERTIES . " pr where pr.properties_id = prd.properties_id and prd.language_id = '" . (int)$languages_id . "' and pr.properties_type in ('".implode("', '", $p_types)."') and pr.display_search = 1 order by pr.sort_order, prd.properties_name");
            if (tep_db_num_rows($properties_query) > 0) {//need to do
                while ($properties_array = tep_db_fetch_array($properties_query)) {
                    $properties_array['control'] = '';

                    switch ($properties_array['properties_type']){
                        case 'text':
						case 'number':
						case 'interval':

							$properties_values_query = tep_db_query("select values_id, values_text, values_number, values_number_upto, values_alt from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int)$properties_array['properties_id'] . "' and language_id = '" . (int)$languages_id . "' order by " . ($properties_array['properties_type'] == 'number' || $properties_array['properties_type'] == 'interval' ? 'values_number' : 'values_text'));

							if ($properties_array['multi_choice']){
								$f = 'tep_draw_checkbox_field';
							} else {
								$f = 'tep_draw_radio_field';
							}

							if (tep_db_num_rows($properties_values_query)){
								while ($property_values = tep_db_fetch_array($properties_values_query)){//echo '<pre>';print_r($property_values);
									if ($properties_array['properties_type'] == 'interval'){
										$properties_array['control'] .= $f($properties_array['properties_id']) . (float)number_format($property_values['values_number'], $properties_array['decimals']) . ' - ' . (float)number_format($property_values['values_number_upto'], $properties_array['decimals']);
									} elseif($properties_array['properties_type'] == 'number'){
										$properties_array['control'] .= $f($properties_array['properties_id'] ) .(float)number_format($property_values['values_number'], $properties_array['decimals']);
									} else {
										$properties_array['control'] .= $f($properties_array['properties_id'] ) .  $property_values['values_text'];
									}
								}
							}


                        break;
                        case 'flag':
                            $properties_array['control'] .= tep_draw_pull_down_menu($properties_array['properties_id'], $properties_yes_no_array);
                            break;
                    }
                    $searchable_properties[] = $properties_array;
                }
            }
        }
        return $this->render('advanced_search.tpl', [
          'messages_search' => $messages_search,
          'controls' => $controls,
          'searchable_properties' => $searchable_properties,
          'search_result_page_link' => tep_href_link(FILENAME_ADVANCED_SEARCH_RESULT,'','NONSSL'),

            //'params' => ['params'=>$params]
          ]);
    }

    public function actionManufacturers(){
      return Yii::$app->runAction('catalog/brands');
    }

    public function actionBrands()
    {
        global $breadcrumb;
        \common\helpers\Translation::init('catalog/manufacturers');
        $breadcrumb->add(NAVBAR_TITLE, \Yii::$app->urlManager->createUrl(['catalog/brands']));
        $page_name = 'manufacturers';
        $params = array();
        $params['page_name'] = $page_name;

        return $this->render('manufacturers.tpl',[
          'params' => $params,
          'page_name' => $page_name
        ]);
    }

    public function actionCompare()
    {
        return $this->render('compare.tpl', [
        ]);
    }

    public function actionUpdateCompare()
    {
        $compare = Yii::$app->request->get('compare', []);
        foreach ($compare as $key1 => $val1) {
            foreach ($val1 as $key2 => $val2) {
                $compare[$key1][$key2] = (int)$val2;
            }
        }
        $_SESSION['compare'] = $compare;

        return json_encode($_SESSION['compare']);
    }

    public function actionAddToCompare()
    {
        $productId = (int)Yii::$app->request->get('productId');
        $categoryId = (int)Yii::$app->request->get('categoryId');

        if (!$productId) {
            return json_encode($_SESSION['compare']);
        }
        if (!$categoryId) {
            $categoryId = \common\helpers\Compare::getCategoryId($productId);
        }
        if (!is_array($_SESSION['compare'][$categoryId] ?? null)) {
            $_SESSION['compare'][$categoryId] = [];
        }
        if (!in_array($productId, $_SESSION['compare'][$categoryId] ?? null)) {
            $_SESSION['compare'][$categoryId][] = $productId;
        }

        return json_encode($_SESSION['compare']);
    }

    public function actionRemoveFromCompare()
    {
        $productId = (int)Yii::$app->request->get('productId');
        $categoryId = (int)Yii::$app->request->get('categoryId');

        $compare = \Yii::$app->session->get('compare');
        if (!$productId) {
            return json_encode($compare);
        }
        if ($categoryId && is_array($compare[$categoryId] ?? null)) {
            $key = array_search($categoryId, $compare[$categoryId]);
            if ($key !== false) {
                unset($compare[$categoryId][$key]);
            }
        } elseif (is_array($compare)) {
            foreach ($compare as $catId => $products) {
                $key = array_search($catId, $compare[$catId]);
                if ($key !== false)
                    unset($compare[$catId][$key]);
            }
        }
        \Yii::$app->session->set('compare', $compare);
        return json_encode($compare);
    }

    public function actionClearCompare()
    {
        $_SESSION['compare'] = [];
        return json_encode($_SESSION['compare']);
    }

    public function actionGetCompareProducts()
    {
        return json_encode($_SESSION['compare']);
    }

    public function actionGetProducts()
    {
        $productIds = Yii::$app->request->get('productIds');

        $settings['listing_type'] = 'compare';
        $settings['productsInArray'] = true;
        $settings = ['itemElements' => [
            'image' => true,
            'price' => true,
        ]];
        Info::getListProductsDetails($productIds, $settings);

        $products = Yii::$container->get('products')->getAllProducts($settings['listing_type']);

        $productsArr = [];
        foreach ($products as $product) {
            $productsArr[$product['products_id']] = [
                'products_id' => $product['products_id'],
                'image' => $product['image'],
                'image_alt' => $product['image_alt'],
                'image_title' => $product['image_title'],
                'srcset' => $product['srcset'],
                'sizes' => $product['sizes'],
                'products_name' => $product['products_name'],
                'link' => $product['link'],
                'is_virtual' => $product['is_virtual'],
                'stock_indicator' => $product['stock_indicator'],
                'product_has_attributes' => $product['product_has_attributes'],
                'isBundle' => !$product['products_status_bundle'],
                'bonus_points_price' => floor($product['bonus_points_price']),
                'bonus_points_cost' => floor($product['bonus_points_cost']),
                'product_in_cart' => $product['product_in_cart'],
                'show_attributes_quantity' => $product['show_attributes_quantity'],
                'in_wish_list' => $product['in_wish_list'],
                'please_login' => (Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login') ? sprintf(TEXT_PLEASE_LOGIN, tep_href_link(FILENAME_LOGIN,'','SSL')) : false),
                'price' => [
                    'current' => $product['price'],
                    'special' => $product['price_special'],
                    'old' => $product['price_old'],
                ],
            ];
        }

        return json_encode($productsArr);
    }

    public function actionCompareBox()
    {
        $boxId = Yii::$app->request->get('box_id');

        $settings = [];
        $boxesSettings = \common\models\DesignBoxesSettings::find()->where([
            'box_id' => $boxId,
            'language_id' => 0,
        ])->asArray()->all();

        foreach ($boxesSettings as $setting) {
            $settings[$setting['setting_name']] = $setting['setting_value'];
        }
        $settings = [$settings];

        $html = \frontend\design\boxes\catalog\Compare::widget(['settings' => $settings]);
        $returnData = [
            'entryData' => Info::$jsGlobalData,
            'html' => $html,
            'css' => \frontend\design\boxes\ProductListing::getStyles()
        ];
        return json_encode($returnData);
    }

    public function actionGiftCard()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('js');
        $product_info = tep_db_fetch_array(tep_db_query("select p.products_id, p.products_tax_class_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description), pd1.products_description, pd.products_description) as products_description from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int)$languages_id ."' and pd1.platform_id = '".(int)Yii::$app->get('platform')->config()->getPlatformToDescription()."', " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_model = 'VIRTUAL_GIFT_CARD' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id ."' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."'"));

        if ( !($product_info['products_id'] > 0) ) {
          return $this->redirect(Yii::$app->urlManager->createUrl('/'));
        }
        $messageStack = \Yii::$container->get('message_stack');
        $currencies = Yii::$container->get('currencies');
        $currency = \Yii::$app->settings->get('currency');

        if (isset($_GET['action']) && ($_GET['action'] == 'add_gift_card')) {
          $virtual_gift_card = tep_db_fetch_array(tep_db_query("select virtual_gift_card_basket_id, products_price as gift_card_price, virtual_gift_card_recipients_name, virtual_gift_card_recipients_email, virtual_gift_card_message, virtual_gift_card_senders_name from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " where length(virtual_gift_card_code) = 0 and virtual_gift_card_basket_id = '" . (int)preg_replace("/\d+\{0\}/","", Yii::$app->request->get('products_id')) . "' and products_id = '" . (int)$product_info['products_id'] . "' and currencies_id = '" . (int)$currencies->currencies[$currency]['id'] . "' and " . (!Yii::$app->user->isGuest ? " customers_id = '" . (int)Yii::$app->user->getId() . "'" : " session_id = '" . Yii::$app->getSession()->get('gift_handler') . "'")));

          $gift_card_price = tep_db_prepare_input($_POST['gift_card_price']);
          $virtual_gift_card_recipients_name = tep_db_prepare_input($_POST['virtual_gift_card_recipients_name']);
          $virtual_gift_card_recipients_email = tep_db_prepare_input($_POST['virtual_gift_card_recipients_email']);
          $virtual_gift_card_confirm_email = tep_db_prepare_input($_POST['virtual_gift_card_confirm_email']);
          $virtual_gift_card_message = tep_db_prepare_input($_POST['virtual_gift_card_message']);
          $virtual_gift_card_senders_name = tep_db_prepare_input($_POST['virtual_gift_card_senders_name']);
          $gift_card_design = tep_db_prepare_input($_POST['gift_card_design']);

          $error = false;

          if (strlen($virtual_gift_card_recipients_email) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
            $error = true;
            $messageStack->add(ENTRY_RECIPIENTS_EMAIL_ERROR, 'virtual_gift_card');
          }

          if (!\common\helpers\Validations::validate_email($virtual_gift_card_recipients_email)) {
            $error = true;
            $messageStack->add(ENTRY_RECIPIENTS_EMAIL_CHECK_ERROR, 'virtual_gift_card');
          }

          if ($virtual_gift_card_recipients_email != $virtual_gift_card_confirm_email) {
            $error = true;
            $messageStack->add(ENTRY_CONFIRM_EMAIL_ERROR, 'virtual_gift_card');
          }

          $send_card_date_value='00-00-0000';
          if ((int)$_POST['send_card_date']>0){
            $send_card_date = (int)$_POST['send_card_date'];
            $_date = \common\helpers\Date::prepareInputDate($_POST['send_card_date_value']);
            if (strtotime($_date)<time()){
                $error = true;
                $messageStack->add(ENTRY_THE_SEND_CART_DATE_ERROR, 'virtual_gift_card');
            }else {
                $send_card_date_value=date('Y-m-d',strtotime($_date));
            }
          }


          if ($error == false) {
            $_price = \common\models\VirtualGiftCardPrices::find()->where(['products_id' => $product_info['products_id'], 'currencies_id' => $currencies->currencies[$currency]['id'], 'products_price' => $gift_card_price])->one();
            $sql_data_array = array('customers_id' => Yii::$app->user->getId(),
                                    'session_id' => Yii::$app->user->getId() > 0 ? '' : Yii::$app->getSession()->get('gift_handler'),
                                    'currencies_id' => $currencies->currencies[$currency]['id'],
                                    'products_id' => $product_info['products_id'],
                                    'products_price' => $gift_card_price,
                                    'products_discount_price' => $_price->products_discount_price,
                                    'virtual_gift_card_recipients_name' => $virtual_gift_card_recipients_name,
                                    'virtual_gift_card_recipients_email' => $virtual_gift_card_recipients_email,
                                    'virtual_gift_card_message' => $virtual_gift_card_message,
                                    'virtual_gift_card_senders_name' => $virtual_gift_card_senders_name,
                                    'send_card_date' => $send_card_date_value,
                                    'virtual_gift_card_code' => '',
                                    'gift_card_design' => $gift_card_design);

            if ($virtual_gift_card['virtual_gift_card_basket_id'] > 0) {
              tep_db_perform(TABLE_VIRTUAL_GIFT_CARD_BASKET, $sql_data_array, 'update', "virtual_gift_card_basket_id = '" . (int)$virtual_gift_card['virtual_gift_card_basket_id'] . "'");
            } else {
              tep_db_perform(TABLE_VIRTUAL_GIFT_CARD_BASKET, $sql_data_array);
            }

            return $this->redirect(Yii::$app->urlManager->createUrl('shopping-cart/'));
          }
        }
        if (Yii::$app->user->isGuest && !Yii::$app->getSession()->has('gift_handler')){
            Yii::$app->getSession()->set('gift_handler', Yii::$app->getSecurity()->generateRandomString());
        }
        $params = [];

        return $this->render('gift-card.tpl', ['params' => $params]);
    }

    public function actionGift(){

        $this->layout = false;

        $page_name = 'gift_card';
        $params = tep_db_prepare_input(Yii::$app->request->get());
        if ($params['page_name']){
            if ($params['page_name'] == 'gift_card_pdf') {
                $page_name = 'gift_card_pdf';
            }

            $templates = \common\models\ThemesSettings::find()
                ->select(['setting_value'])
                ->where([
                    'theme_name' => THEME_NAME,
                    'setting_group' => 'added_page',
                    'setting_name' => 'gift_card',
                ])
                ->asArray()
                ->all();

            foreach ($templates as $template) {
                if (
                    \common\classes\design::pageName($template['setting_value']) == $params['page_name'] ||
                    \common\classes\design::pageName($template['setting_value']) . '_pdf' == $params['page_name']
                ) {
                    $page_name = $params['page_name'];
                }
            }

        }

        $page_name = \common\classes\design::pageName($page_name);
        return $this->render('gift.tpl', [
            'page_name' => $page_name,
            'params' => ['absoluteUrl' => true]
        ]);
    }

    public function actionGetPrice() {
        $this->layout = false;
        $params = tep_db_prepare_input(Yii::$app->request->post());
        if (empty($params['pid'])) {
          return ;
        }
        /** @var \common\extensions\PackUnits\PackUnits $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
            $listid = tep_db_prepare_input(Yii::$app->request->post('listid', array()));
            if (is_array(\yii\helpers\ArrayHelper::getValue($listid, $params['pid']))) {
              $params['id'] = $listid[$params['pid']];
            } elseif (!empty($listid)) {
              $params['id'] = $listid;
            }
            $ext::getPricePack(0, false, $params);
        }
        else {
          \common\helpers\Translation::init('catalog/product');

          $products_id = $params['pid'];
          $attributes = ($params['id']?$params['id']:[]);
          $listid = tep_db_prepare_input(Yii::$app->request->post('listid', array()));
          if (!empty($listid[$products_id])) {
            $attributes = $listid[$products_id];
          } elseif (!empty($listid)) {
            $attributes = $listid;
          }

          $type = ($params['type']?$params['type']:'product');
          if (!$attributes && strpos($products_id, '{') !== false){
              \common\helpers\Inventory::normalize_id($products_id, $attributes);
          }
/*
          global $cPath_array;

          $page_name = Yii::$app->request->get('page_name');
          if (!$page_name) {
              $page_name = \frontend\design\Product::pageName($params['products_id'], $cPath_array);
              $this->view->page_name = $page_name;
          }
*/
          $noAttr = false;
          if (is_array($attributes) && count($attributes) == 0) {
              $noAttr = true;
          }

          $details = \common\helpers\Attributes::getDetails($products_id, $attributes, $params);

          if ($noAttr) {
              foreach ($details['attributes_array'] as $attr) {
                  $attributes[$attr['id']] = $attr['options'][0]['id'];
              }
              $details = \common\helpers\Attributes::getDetails($products_id, $attributes, $params);
          }

          Yii::$container->get('products')->loadProducts(['products_id' => $details['current_uprid']])
                  ->attachDetails($details['current_uprid'], ['attributes_array' => $details['attributes_array']]);
          try {
              $event = new ProductAttributesInfoEvent(
                  $details,
                  Yii::$app->user->isGuest ? false : \Yii::$app->user->getId()
              );
              \Yii::$container->get('eventDispatcher')->dispatch($event);
              $details = $event->getProductAttributes();
          } catch (\Exception $e) {
              \Yii::error($e->getMessage());
          }


          $product = Yii::$container->get('products')->getProduct($details['current_uprid']);

          if (0 && $product && $product['settings']->show_attributes_quantity){
              return \frontend\design\boxes\product\MultiInventory::widget();
          } else {
/*              $details['image_widget'] = \frontend\design\boxes\product\Images::widget(['params'=>['uprid'=>$details['current_uprid']], 'settings' => \frontend\design\Info::widgetSettings('product\Images', false, 'product')]);
              if ($type == 'listing'){
                  $details['product_attributes'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/listing-product/attributes.tpl', 'params' => ['attributes' => $details['attributes_array'], 'isAjax' => true, 'products_id' => $products_id]]);
              } else {
                  $details['product_attributes'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/attributes.tpl', 'params' => ['attributes' => $details['attributes_array'], 'isAjax' => true]]);
              }
              $details['product_name'] = $product['products_name'];*/
              return json_encode($details);
          }


        }
    }

    public function actionProductInventory()
    {
        \common\helpers\Translation::init('catalog/product');
        $params = Yii::$app->request->get();
        global $cPath_array;
        $page_name = \frontend\design\Product::pageName($params['products_id'], $cPath_array);
        $this->view->page_name = $page_name;

        $params = Yii::$app->request->get();
        $products_id = Yii::$app->request->get('products_id');
        $inv_uprid = Yii::$app->request->get('inv_uprid');

        $details = \common\helpers\Inventory::getDetails($products_id, $inv_uprid, $params);

        if (Yii::$app->request->isAjax) {
//            $details['image_widget'] = \frontend\design\boxes\product\Images::widget(['params'=>['uprid'=>$details['current_uprid']], 'settings' => \frontend\design\Info::widgetSettings('product\Images', false, 'product')]);
            $details['product_inventory'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/inventory.tpl', 'params' => ['inventory' => $details['inventory_array'], 'isAjax' => true]]);
            return json_encode($details);
        } else {
            if (count($details['inventory_array']) > 0) {
                return IncludeTpl::widget(['file' => 'boxes/product/inventory.tpl', 'params' => ['inventory' => $details['inventory_array'], 'isAjax' => false,]]);
            } else {
                return '';
            }
        }
    }

    public function actionProductBundle()
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('ProductBundles')) {
            return '';
        }
        \common\helpers\Translation::init('catalog/product');

        $params = Yii::$app->request->get();
        $products_id = tep_db_prepare_input(Yii::$app->request->get('products_id'));
        $attributes = tep_db_prepare_input(Yii::$app->request->get('id', array()));
        global $cPath_array;
        $page_name = \frontend\design\Product::pageName($params['products_id'], $cPath_array);
        $this->view->page_name = $page_name;

        $attributes_details = \common\helpers\Attributes::getDetails($products_id, $attributes, $params);
        $details = \common\helpers\Bundles::getDetails($params, $attributes_details);

        if (Yii::$app->request->isAjax) {
            $details['product_bundle'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/bundle.tpl', 'params' => ['products' => $details['bundle_products'], 'isAjax' => true]]);
            $details['product_attributes'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/attributes.tpl', 'params' => ['attributes' => $attributes_details['attributes_array'], 'isAjax' => true]]);

            /**
             * @var $ext \common\extensions\FlexiFi\FlexiFi
             */
            if ($ext = \common\helpers\Extensions::isAllowed('FlexiFi')) {
                $details['flexifi_credit_plan_button'] = $ext::getPopupButtonHtml((['products_id' => $products_id] + $details), $details['actual_bundle_price_unit'], true);
            } else {
                $details['flexifi_credit_plan_button'] = '';
            }
            return json_encode($details);
        } else {
            if (isset($details['bundle_products']) && count($details['bundle_products']) > 0) {
                return IncludeTpl::widget(['file' => 'boxes/product/bundle.tpl', 'params' => ['products' => $details['bundle_products'], 'isAjax' => false,]]);
            } else {
                return '';
            }
        }
    }

    public function actionProductConfigurator()
    {
        if (!\common\helpers\Acl::checkExtensionAllowed('ProductConfigurator')) {
            return '';
        }

        \common\helpers\Translation::init('catalog/product');

        $params = Yii::$app->request->get();
        global $cPath_array;
        $page_name = \frontend\design\Product::pageName($params['products_id']??null, $cPath_array);
        $this->view->page_name = $page_name;

        $params = tep_db_prepare_input(Yii::$app->request->get());
        $products_id = tep_db_prepare_input(Yii::$app->request->get('products_id'));
        $attributes = tep_db_prepare_input(Yii::$app->request->get('id', array()));
        $only_element = intval(Yii::$app->request->get('only_element', 0));

        $attributes_details = \common\helpers\Attributes::getDetails($products_id, $attributes, $params);
        Yii::$container->get('products')->loadProducts(['products_id' => $attributes_details['current_uprid']]);
        $details = \common\extensions\ProductConfigurator\helpers\Configurator::getDetails($params, $attributes_details, $only_element);
        if (!is_array($details)) {
            return '';
        }
        $product =  Yii::$container->get('products') ? Yii::$container->get('products')->getProduct($details['current_uprid']??null) : null;
        if (Yii::$app->request->isAjax) {
            $details['product_configurator'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/configurator.tpl', 'params' => ['settings' => Info::widgetSettings('product\\Configurator'), 'elements' => $details['configurator_elements'], 'pctemplates_id' => $details['pctemplates_id'], 'only_element' => $only_element, 'isAjax' => true]]);
            if ($product['settings']->show_attributes_quantity ?? null) {
                $details['product_attributes'] = \frontend\design\boxes\product\MultiInventory::widget();
            } else {
                $details['product_attributes'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/attributes.tpl', 'params' => ['settings' => Info::widgetSettings('product\\Configurator'), 'attributes' => $attributes_details['attributes_array'], 'isAjax' => true, 'product' => $product]]);
            }

            /**
             * @var $ext \common\extensions\FlexiFi\FlexiFi
             */
            if ($ext = \common\helpers\Extensions::isAllowed('FlexiFi')) {
                $details['flexifi_credit_plan_button'] = $ext::getPopupButtonHtml((['products_id' => $products_id] + $details), $details['configurator_price_unit'], true);
            } else {
                $details['flexifi_credit_plan_button'] = '';
            }
            return json_encode($details);
        } else {
            if (isset($details['configurator_elements']) && count($details['configurator_elements']) > 0) {
                return IncludeTpl::widget(['file' => 'boxes/product/configurator.tpl', 'params' => ['settings' => Info::widgetSettings('product\\Configurator'), 'elements' => $details['configurator_elements'], 'pctemplates_id' => $details['pctemplates_id'], 'isAjax' => false, 'product' => $product ]]);
            } else {
                return '';
            }
        }
    }

    public function actionProductConfiguratorInfo()
    {

        global $cPath_array;
        $languages_id = \Yii::$app->settings->get('languages_id');
        $params = Yii::$app->request->get();
        $page_name = \frontend\design\Product::pageName($params['products_id']??null, $cPath_array);
        $this->view->page_name = $page_name;

        \common\helpers\Translation::init('catalog/product');

        $params = Yii::$app->request->get();

        if ($params['tID'] > 0 && $params['eID'] > 0) {
            $product_info_values = array();
            $product_info_sql = "select p.products_id, p.products_status, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description), pd1.products_description, pd.products_description) as products_description, p.products_model, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from " . TABLE_PRODUCTS_TO_PCTEMPLATES_TO_ELEMENTS . " ppe, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int)$languages_id . "' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "' where p.products_id = ppe.products_id and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and ppe.pctemplates_id = '" . (int)$params['tID'] . "' and ppe.elements_id = '" . (int)$params['eID'] . "'";
            if ($params['pID'] > 0) {
                $product_info_sql .= " and p.products_id = '" . (int) $params['pID'] . "'";
            }
            $product_info_query = tep_db_query($product_info_sql);
            while ($product_info = tep_db_fetch_array($product_info_query)) {
                $product_info_values[] = $product_info;
            }
            return IncludeTpl::widget(['file' => 'boxes/product/pc_info.tpl', 'params' => ['product_info_values' => $product_info_values]]);
        } else {
            return '';
        }
    }

    public function getHerfLang($platforms_languages){
        $except = [];
        if (isset($_GET['products_id'])){
            $pages = tep_db_query("select products_seo_page_name as seo_page_name, language_id from " . TABLE_PRODUCTS_DESCRIPTION . " where platform_id = '".(int)Yii::$app->get('platform')->config()->getPlatformToDescription()."' and products_id = '" . (int)$_GET['products_id'] . "' and language_id in (" . implode(",", array_values($platforms_languages)) . ")");
            $except[] = $_GET['products_id'];
        } else if (isset($_GET['cPath'])){
            $ex = explode("_", $_GET['cPath']);
            $pages = tep_db_query("select categories_seo_page_name as seo_page_name, language_id from " . TABLE_CATEGORIES_DESCRIPTION . " where affiliate_id = 0 and categories_id = '" . (int)$ex[count($ex)-1] . "' and language_id in (" . implode(",", array_values($platforms_languages)) . ")");
            $except[] = $_GET['cPath'];
        } else {
            $list = [];
            if (is_array($platforms_languages)){
                $route = Yii::$app->urlManager->parseRequest(Yii::$app->request);
                foreach($platforms_languages as $pl){
                    $list[$pl] = (isset($route[0])?$route[0]:$this->getRoute());
                }
            }
            return $list;
        }

        $list = [];
        if (tep_db_num_rows($pages)){
            while($page = tep_db_fetch_array($pages)){
                $list[$page['language_id']] = [$page['seo_page_name'], $except];
            }
        }
        return $list;
    }

    public function actionProductCollection()
    {
        \common\helpers\Translation::init('catalog/product');

        $params = Yii::$app->request->get();
        global $cPath_array;
        $page_name = \frontend\design\Product::pageName($params['products_id'], $cPath_array);
        $this->view->page_name = $page_name;

        if ($collections = \common\helpers\Acl::checkExtensionAllowedClass('ProductsCollections', 'helpers\Collections')) {
            $details = $collections::getDetails($params);
        } else {
            return '';
        }

        if (Yii::$app->request->isAjax) {
            $details['product_collection'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/collection.tpl',
                'params' => [
                    'products' => $details['all_products'],
                    'product' => $details['curr_product'],
                    'chosenProducts' => $details['collection_products'],
                    'old' => $details['collection_full_price'],
                    'price' => $details['collection_full_price'],
                    'special' => $details['collection_discount_price'],
                    'save' => $details['collection_discount_percent'],
                    'savePrice' => $details['collection_save_price'],
                    'isAjax' => true
                ]
            ]);
            return json_encode($details);
        } else {
            if (isset($details['all_products']) && count($details['all_products']) > 0) {
                return IncludeTpl::widget(['file' => 'boxes/product/collection.tpl',
                    'params' => [
                        'products' => $details['all_products'],
                        'product' => $details['curr_product'],
                        'chosenProducts' => $details['collection_products'],
                        'old' => $details['collection_full_price'],
                        'price' => $details['collection_full_price'],
                        'special' => $details['collection_discount_price'],
                        'save' => $details['collection_discount_percent'],
                        'savePrice' => $details['collection_save_price'],
                        'isAjax' => false
                    ]
                ]);
            } else {
                return '';
            }
        }
    }

    public function actionFreeSamples() {
        global $breadcrumb;

        $breadcrumb->add(NAVBAR_TITLE,tep_href_link('catalog/free-samples'));

        $sorting = Sorting::getSortingList();

        $sorting[] = array('id' => 'da', 'title' => TEXT_BY_DATE . ' &darr;');
        $sorting[] = array('id' => 'dd', 'title' => TEXT_BY_DATE . ' &uarr;');

        $search_results = Info::widgetSettings('Listing', 'items_on_page', 'products');
        if (!$search_results) $search_results = SEARCH_RESULTS_1;

        $params = array(
          'listing_split' => SplitPageResults::make(ListingSql::query(array('filename' => 'catalog/free-samples', 'only_samples' => true)), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'p.products_id')->withSeoRelLink(),
          'this_filename' => 'catalog/free-samples',
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),
        );

        if (Yii::$app->request->get('fbl')) {
            $this->layout = 'ajax.tpl';
            return Listing::widget(['params' => $params, 'settings' => Info::widgetSettings('Listing')]);
        }

        $page_name = filter_var(Yii::$app->request->get('page_name', ''), FILTER_SANITIZE_STRING);
        if (empty($page_name)) {
            $page_name = 'products';
        }
        $params['page_name'] = $page_name;

        return $this->render('free-samples.tpl', [
            'page_name' => $page_name,
            'params' => [
                'params'=>$params,
                'type' => 'catalog',
        ]]);
    }

    public function actionProductCustomBundle()
    {
        \common\helpers\Translation::init('catalog/product');

        $params = Yii::$app->request->get();

        $details = \common\helpers\CustomBundles::getDetails($params);

        if (Yii::$app->request->isAjax) {
            $details['product_custom_bundle'] = \frontend\design\IncludeTpl::widget(['file' => 'boxes/product/custom-bundle.tpl',
                'params' => [
                    'products' => $details['all_products'],
                    'chosenProducts' => $details['custom_bundle_products'],
                    'old' => $details['custom_bundle_full_price'],
                    'price' => $details['custom_bundle_full_price'],
                    'isAjax' => true,
                    'id' => $params['box_id']
                ]
            ]);
            return json_encode($details);
        } else {
            if (isset($details['all_products']) && count($details['all_products']) > 0) {
                return IncludeTpl::widget(['file' => 'boxes/product/custom-bundle.tpl',
                    'params' => [
                        'products' => $details['all_products'],
                        'chosenProducts' => $details['custom_bundle_products'],
                        'old' => $details['custom_bundle_full_price'],
                        'price' => $details['custom_bundle_full_price'],
                        'isAjax' => false,
                        'id' => $params['box_id']
                    ]
                ]);
            } else {
                return '';
            }
        }
    }

    public function actionProductListing()
    {
        $page_name = Yii::$app->request->get('page_name', 'productListing');
        Info::addBoxToCss('quantity');
        Info::addBoxToCss('products-listing');

        return $this->render('product-listing.tpl', [
            'page_name' => $page_name,
            'params' => []
        ]);
    }

    public function actionSameCategoryProducts()
    {
        $productId = Yii::$app->request->get('id');
        $categoryId = \common\models\Products2Categories::findOne(['products_id' => $productId])->categories_id;

        $response = [];
        $q = new \common\components\ProductsQuery([
            'filters'=> ['categories' => [$categoryId]],
        ]);
        $currencies = \Yii::$container->get('currencies');
        $settings['itemElements']['image'] = true;
        $products = Info::getListProductsDetails($q->buildQuery()->rebuildByGroup()->allIds(), $settings);
        foreach ($products as $product) {

            $special_price = \common\helpers\Product::get_products_special_price($product['products_id']);
            if ($special_price) {
                $product['price_old'] = $currencies->display_price(\common\helpers\Product::get_products_price($product['products_id'], 1, $product['products_price']), \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));
                $product['price_special'] = $currencies->display_price($special_price, \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));
                $product['calculated_price'] = $special_price;
            } else {
                $product['price'] = $currencies->display_price(\common\helpers\Product::get_products_price($product['products_id'], 1, $product['products_price']), \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));
                $product['calculated_price'] = $currencies->calculate_price(\common\helpers\Product::get_products_price($product['products_id'], 1, $product['products_price']), \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']));
            }

            $product['price_special'] = (isset($product['price_special']) ? $product['price_special'] : false);
            $response[] = [
                'id' => $product['id'],
                'link' => $product['link'],
                'name' => $product['products_name'],
                'img' => '<img src="' . Images::getImageUrl($product['products_id'], 'Small') . '">',
                'price' => '<div class="price">'
                    . ($product['price_special'] ? '<span class="old">' . $product['price_old'] . '</span>' : '')
                    . ($product['price_special'] ? '<span class="specials">' . $product['price_special'] . '</span>' : '')
                    . (!$product['price_special'] ? '<span class="current">' . $product['price'] . '</span>' : '') .
                    '</div>',
            ];
        }

        return json_encode($response);
    }

    public function actionProductImages()
    {
        $id = Yii::$app->request->get('id', false);
        if (!$id) return json_encode([]);
        return \frontend\design\boxes\product\ImagesAdditional::widget([
            'params' => [
                'uprid' => $id,
                'no_tpl' => true
            ]
        ]);
    }
}
