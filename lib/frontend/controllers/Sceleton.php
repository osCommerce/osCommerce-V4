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

use common\classes\platform;
use common\components\InformationPage;
use common\helpers\Seo;
use frontend\design\Block;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use frontend\design\Info;
use common\helpers\Product;
use common\helpers\Affiliate;
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Sceleton extends Controller {
    use \common\helpers\SqlTrait;

    //public $enableCsrfValidation = false;

    /**
     * @var array the breadcrumbs of the current page.
     */
    public $navigation = array();

    /**
     * @var stdClass the variables for smarty.
     */
    public $view = null;

    public $promoActionsObs = null;

    /**
     * Selected items in menu
     * @var array
     */
    public $selectedMenu = array();

    private $use_social = false;
    public $show_socials = false;

    function __construct($id,$module=null) {

        global $cart;

        $lng = new \common\classes\language();
        $lng->set_locale();
        $lng->load_vars();

        $params = Yii::$app->request->get();

        if (class_exists('\common\models\Restriction')){
            \common\models\Restriction::verifyAddress();
        }

        $allowedClient = false;
        foreach (\common\helpers\Hooks::getList('frontend/sceleton/construct') as $filename) {
            include($filename);
        }

        // {{ dev basic auth
        if ( strpos(\Yii::$app->id,'frontend')!==false && defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE=='True' && empty($allowedClient)) {
            $requestRoute = $id.'/index';
            if ( is_object($module) && $module instanceof yii\web\Application) {
                $requestRoute = $module->requestedRoute;
            }
            if ( !Info::isAdmin() && !preg_match('#^(email-template|callback|multisafe|global-payments/payment|checkout|image/|account/order-barcode|account/order-qrcode|api\-)#',$requestRoute) ) {
                if ( \Yii::$app->response->getIsNotFound() ) {
                    header('HTTP/1.0 404 Not Found');
                    die;
                }
                header('Cache-Control: no-cache, must-revalidate, max-age=0');
                if (
                    strval(\Yii::$app->request->getAuthUser()) != DEVELOPMENT_MODE_USERNAME ||
                    strval(\Yii::$app->request->getAuthPassword()) != DEVELOPMENT_MODE_PASSWORD
                ) {
                    header('HTTP/1.1 401 Authorization Required');
                    header('WWW-Authenticate: Basic realm="' . \common\helpers\Output::output_string('Development mode - access denied') . '", charset="UTF-8"');
                    die;
                }
            }
        }
        // }} dev basic auth

        if (isset($params['theme_name']) && !empty($params['theme_name'])) {
          $theme = preg_replace('/[^A-Za-z0-9\-_]/', '', $params['theme_name']);
          if ($theme !== $params['theme_name']) {
            header("HTTP/1.1 404 Not Found");
            die;
          }
        } else {
            /**
             * Switch the theme via the platform
             */
            //PLATFORM_ID
            $theme = Info::getThemeName(PLATFORM_ID);

        }
        $themes_path = Info::getThemesPath(array($theme));
        if (substr($theme, -7) == '-mobile') {
            $themes_path = array_merge($themes_path, Info::getThemesPath([substr($theme, 0, -7)]));
        }
        $themes_path[] = 'basic';
        Info::$themeMap = $themes_path;

        $pathMapView = array();
        foreach ($themes_path as $item){
          $pathMapView[] = '@app/themes/' . $item;
        }
        Yii::$app->view->theme = new \yii\base\Theme([
          'pathMap' => [
            '@app/views' => $pathMapView
          ],
          'baseUrl' => '@web/themes/' . $theme,
        ]);

        Yii::setAlias('@webThemes', '@web/themes');
        Yii::setAlias('@theme', '@app/themes/' . $theme);
        Yii::setAlias('@webTheme', '@web/themes/' . $theme);
        Yii::setAlias('@themeImages', '@web/themes/' . $theme . '/img/');
        defined('DIR_WS_THEME') or define('DIR_WS_THEME', Yii::getAlias('@webTheme'));
        defined('THEME_NAME') or define('THEME_NAME', $theme);
        defined('DIR_WS_THEME_IMAGES') or define('DIR_WS_THEME_IMAGES', Yii::getAlias('@themeImages'));

        global $request_type;
        defined('BASE_URL') or define('BASE_URL', (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_HTTP_CATALOG));

        \Yii::$app->view->title = \Yii::$app->name;
        $this->view = new \stdClass();
        $this->view->wp_head = null;
        $this->view->wp_footer = null;
        $this->view->no_header_footer = null;
        $this->view->page_name = null;
        parent::__construct($id,$module);

        if (isset($params['gl'])){
            $_SESSION['gl'] = tep_db_prepare_input($params['gl']);
        }
        if (!isset($_SESSION['gl'])){
            $_SESSION['gl'] = 'grid';
        }

        if (isset($params['max_items'])){
            $_SESSION['max_items'] = intval($params['max_items']);
        }
        if ($ext = \common\helpers\Extensions::isAllowed('ShowInactive')) {
            $ext::changeStatus();
        }

        $cookies = Yii::$app->request->cookies;

        if (!Yii::$app->user->isGuest) {
            $customer = 'logged';
        } else {
            if (!$cookies->has('was_visit')) {
                $customer = 'first_view';
            } else {
                $customer = 'more_view';
            }
        }
        Info::addBlockToPageName($customer);

        Info::includeJsFile('modules/window-sizes');
        Info::includeJsFile('modules/tl-init');
        Info::addJsData(['jsPathUrl' => rtrim(DIR_WS_CATALOG, '/') . '/themes/basic/js']);
        Info::addJsData(['themeVersion' => Info::themeSetting('theme_version', 'hide')]);
        Info::addJsData(['mainUrl' => Yii::$app->urlManager->createAbsoluteUrl('')]);

        Info::includeJsFile('reducers/account');
        Info::includeJsFile('reducers/themeSettings');

        if ($cart && false) {
            $cartProducts = [];
            $cart_products = $cart->get_products();
            foreach ($cart_products as $product) {
                $cartProducts[$product['stock_products_id']] = [
                    'id' => $product['id'],
                    'qty' => $product['quantity'],
                ];
            }
            Info::addJsData(['productListings' => ['cart' => ['products' => $cartProducts]]]);
        }

        Info::addJsData(['account' => [
            'isGuest' => Yii::$app->user->isGuest
        ]]);
        Info::addJsData(['themeSettings' => [
            'useProductsCarousel' => Info::themeSetting('products_carousel'),
            'showInCartButton' => Info::themeSetting('show_in_cart_button'),
        ]]);

        if (defined("SEO_H_TAG_INLINE") && SEO_H_TAG_INLINE === 'True'){
            Info::addBoxToCss('h-inline');
        }

        \common\components\Socials::loadSocialAddons(PLATFORM_ID);
        $platform_config = new \common\classes\platform_config(\common\classes\platform::currentId());
        $this->use_social = $platform_config->checkNeedSocials();
        if ($this->use_social) {
            \common\components\Socials::loadComponents(PLATFORM_ID);
            $this->show_socials = true;
        }

        if (\common\helpers\Acl::checkExtensionAllowed('BonusActions')) {
            $this->promoActionsObs = \common\extensions\BonusActions\models\PromotionsBonusObserver::getInstance();
            $this->promoActionsObs->checkRequestPromoAction();
        }
        //$this->setMeta();
    }


    public function runAction($id, $params = [])
    {
      if (!Yii::$app->request->isAjax){
        $this->setMeta($id, $params);

        /** @var \common\extensions\FrontendsSession\FrontendsSession $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('FrontendsSession')) {
            $ext::registerJsFile($this->getView());
        }

      }
      return parent::runAction($id, $params);
    }


    protected function setMeta($id, $params)
    {

        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $languages_id = \Yii::$app->settings->get('languages_id');

        $params = !is_array($params)?array():$params;
        $controller = $this->id;
        $full_action = $this->id.'/'.$id;
//        echo '<pre>'; var_dump($full_action, $params); echo '</pre>';
        // catalog/featured_products
        //catalog/specials
        //catalog/products_new
        // reviews/ || reviews/index

        $get_def_q = tep_db_query(
          "select m.meta_tags_key, m.meta_tags_value ".
          "from " . TABLE_META_TAGS . " m ".
          "where m.language_id = '".(int)$languages_id."' and m.platform_id='".platform::currentId()."' and m.affiliate_id = 0"
        );
        if (tep_db_num_rows($get_def_q)>0) {
            while($get_def = tep_db_fetch_array($get_def_q)) {
                if ( defined(trim($get_def['meta_tags_key'])) ) continue;
                define(trim($get_def['meta_tags_key']), $get_def['meta_tags_value']);
            }
        }

        $HEAD_DESC_TAG_ALL = defined('HEAD_DESC_TAG_ALL')?HEAD_DESC_TAG_ALL:'';
        $HEAD_KEY_TAG_ALL = defined('HEAD_KEY_TAG_ALL')?HEAD_KEY_TAG_ALL:'';
        $HEAD_TITLE_TAG_ALL = defined('HEAD_TITLE_TAG_ALL')?HEAD_TITLE_TAG_ALL:'';

        $the_desc = defined('HEAD_DESC_TAG_ALL')?HEAD_DESC_TAG_ALL:'';
        $the_key_words = defined('HEAD_KEY_TAG_ALL')?HEAD_KEY_TAG_ALL:'';
        $the_title = defined('HEAD_TITLE_TAG_ALL')?HEAD_TITLE_TAG_ALL:'';
        $selfService = '';

        global $current_category_id;
        $_current_category_id = $current_category_id;

        //$with_category_path = (isset($params['cPath']) && !empty($params['cPath']));
        $with_category = isset($_current_category_id) && $_current_category_id>0;
        $with_manufacturer = (isset($params['manufacturers_id']) && !empty($params['manufacturers_id']));

        // Define specific settings per page:
        switch (true) {
            // Index page
            case $full_action=='index/' || $full_action=='index/index' || ($full_action=='catalog/index' && !($with_category || $with_manufacturer) ) :
                $the_title = (defined('HEAD_TITLE_TAG_DEFAULT') && tep_not_null(HEAD_TITLE_TAG_DEFAULT)?HEAD_TITLE_TAG_DEFAULT:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_DEFAULT') && tep_not_null(HEAD_KEY_TAG_DEFAULT)?HEAD_KEY_TAG_DEFAULT:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_DEFAULT') && tep_not_null(HEAD_DESC_TAG_DEFAULT)?HEAD_DESC_TAG_DEFAULT:$HEAD_DESC_TAG_ALL);
                break;
            // categories
            case $full_action=='catalog/index' && ($with_category || $with_manufacturer):
                $the_data = false;
                if ( $with_category ) {
                    $the_category_query = tep_db_query(
                      "select if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) AS name, ".
                      " if(length(cd1.categories_head_title_tag), cd1.categories_head_title_tag, cd.categories_head_title_tag) as head_title_tag, ".
                      " if(length(cd1.categories_head_desc_tag), cd1.categories_head_desc_tag, cd.categories_head_desc_tag) as head_desc_tag, ".
                      " if(length(cd1.categories_head_keywords_tag), cd1.categories_head_keywords_tag, cd.categories_head_keywords_tag) as head_keywords_tag ".
                      "from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
                      " left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id='" . (int)$languages_id . "' and cd1.affiliate_id = '" . Affiliate::id() . "' ".
                      "where c.categories_id = '" . (int)$_current_category_id . "' and cd.categories_id = c.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int)$languages_id . "'"
                    );
                    if ( tep_db_num_rows($the_category_query) ) {
                        $the_data = tep_db_fetch_array($the_category_query);
                    }
                } else {
                    $the_manufacturers_query= tep_db_query(
                      "select m.manufacturers_name AS name, ".
                      " mi.manufacturers_meta_description AS head_desc_tag, ".
                      " mi.manufacturers_meta_key AS head_keywords_tag, ".
                      " mi.manufacturers_meta_title AS head_title_tag ".
                      "from " . TABLE_MANUFACTURERS . " m ".
                      " left join ".TABLE_MANUFACTURERS_INFO." mi ON mi.manufacturers_id = m.manufacturers_id AND mi.languages_id='".(int)$languages_id."' ".
                      "where m.manufacturers_id = '" . (int)$params['manufacturers_id'] . "'"
                    );
                    if ( tep_db_num_rows($the_manufacturers_query) ) {
                        $the_data = tep_db_fetch_array($the_manufacturers_query);
                    }
                }

                if ( !is_array($the_data) || empty($the_data['head_title_tag'])) {
                    if ( $with_category ) {
                        $the_title = (defined('HEAD_TITLE_TAG_CATEGORY') && tep_not_null(HEAD_TITLE_TAG_CATEGORY) ? HEAD_TITLE_TAG_CATEGORY : $HEAD_TITLE_TAG_ALL);
                        if (strstr($the_title, '##BREADCRUMB##')) {
                            $breadcrumb_array = $parent_categories = array();
                            \common\helpers\Categories::get_parent_categories($parent_categories, $_current_category_id);
                            foreach (array_reverse($parent_categories) as $cat_id) {
                                $breadcrumb_array[] = \common\helpers\Categories::get_categories_name($cat_id);
                            }
                            if ( count($breadcrumb_array)==0 && Seo::getMetaDefaultBreadcrumb($full_action) ) $breadcrumb_array[] = Seo::getMetaDefaultBreadcrumb($full_action);
                            $the_title = str_replace('##BREADCRUMB##', implode(' / ', $breadcrumb_array), $the_title);
                        }
                        if (strstr($the_title, '##CATEGORY_NAME##')) {
                            $the_title = str_replace('##CATEGORY_NAME##', \common\helpers\Categories::get_categories_name($_current_category_id), $the_title);
                        } else {
                            $the_title = $the_title . ' ' . $the_data['name'];
                        }
                    } else {
                        $the_title = (defined('HEAD_TITLE_TAG_BRAND') && tep_not_null(HEAD_TITLE_TAG_BRAND) ? HEAD_TITLE_TAG_BRAND : $HEAD_TITLE_TAG_ALL);
                        if (strstr($the_title, '##BRAND_NAME##')) {
                            $the_title = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $params['manufacturers_id']), $the_title);
                        } else {
                            $the_title = $the_title . ' ' . $the_data['name'];
                        }
                    }
                } else {
                    $the_title = $the_data['head_title_tag'];
                }

                if ( !is_array($the_data) || empty($the_data['head_keywords_tag'])) {
                    if ( $with_category ) {
                        $the_key_words = (defined('HEAD_KEY_TAG_CATEGORY') && tep_not_null(HEAD_KEY_TAG_CATEGORY) ? HEAD_KEY_TAG_CATEGORY : $HEAD_KEY_TAG_ALL);
                        if (strstr($the_key_words, '##BREADCRUMB##')) {
                            $breadcrumb_array = $parent_categories = array();
                            \common\helpers\Categories::get_parent_categories($parent_categories, $_current_category_id);
                            foreach (array_reverse($parent_categories) as $cat_id) {
                                $breadcrumb_array[] = \common\helpers\Categories::get_categories_name($cat_id);
                            }
                            if ( count($breadcrumb_array)==0 && Seo::getMetaDefaultBreadcrumb($full_action) ) $breadcrumb_array[] = Seo::getMetaDefaultBreadcrumb($full_action);
                            $the_key_words = str_replace('##BREADCRUMB##', implode(' / ', $breadcrumb_array), $the_key_words);
                        }
                        if (strstr($the_key_words, '##CATEGORY_NAME##')) {
                            $the_key_words = str_replace('##CATEGORY_NAME##', \common\helpers\Categories::get_categories_name($_current_category_id), $the_key_words);
                        } else {
                            $the_key_words = $the_data['name'] . ', ' . $the_key_words;
                        }
                    } else {
                        $the_key_words = (defined('HEAD_KEY_TAG_BRAND') && tep_not_null(HEAD_KEY_TAG_BRAND) ? HEAD_KEY_TAG_BRAND : $HEAD_KEY_TAG_ALL);
                        if (strstr($the_key_words, '##BRAND_NAME##')) {
                            $the_key_words = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $params['manufacturers_id']), $the_key_words);
                        } else {
                            $the_key_words = $the_data['name'] . ', ' . $the_key_words;
                        }
                    }
                } else {
                    $the_key_words = $the_data['head_keywords_tag'];
                }

                if ( !is_array($the_data) || empty($the_data['head_desc_tag'])) {
                    if ( $with_category ) {
                        $the_desc = (defined('HEAD_DESC_TAG_CATEGORY') && tep_not_null(HEAD_DESC_TAG_CATEGORY) ? HEAD_DESC_TAG_CATEGORY : $HEAD_DESC_TAG_ALL);
                        if (strstr($the_desc, '##BREADCRUMB##')) {
                            $breadcrumb_array = $parent_categories = array();
                            \common\helpers\Categories::get_parent_categories($parent_categories, $_current_category_id);
                            foreach (array_reverse($parent_categories) as $cat_id) {
                                $breadcrumb_array[] = \common\helpers\Categories::get_categories_name($cat_id);
                            }
                            if ( count($breadcrumb_array)==0 && Seo::getMetaDefaultBreadcrumb($full_action) ) $breadcrumb_array[] = Seo::getMetaDefaultBreadcrumb($full_action);
                            $the_desc = str_replace('##BREADCRUMB##', implode(' / ', $breadcrumb_array), $the_desc);
                        }
                        if (strstr($the_desc, '##CATEGORY_NAME##')) {
                            $the_desc = str_replace('##CATEGORY_NAME##', \common\helpers\Categories::get_categories_name($_current_category_id), $the_desc);
                        } else {
                            $the_desc = $the_data['name'] . ' ' . $the_desc;
                        }
                    } else {
                        $the_desc = (defined('HEAD_DESC_TAG_BRAND') && tep_not_null(HEAD_DESC_TAG_BRAND) ? HEAD_DESC_TAG_BRAND : $HEAD_DESC_TAG_ALL);
                        if (strstr($the_desc, '##BRAND_NAME##')) {
                            $the_desc = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $params['manufacturers_id']), $the_desc);
                        } else {
                            $the_desc = $the_data['name'] . ' ' . $the_desc;
                        }
                    }
                } else {
                    $the_desc = $the_data['head_desc_tag'];
                }
                break;

            // PRODUCT_INFO
            case ( $full_action=='catalog/product' &&  isset($params['products_id']) && $params['products_id']>0 ):
                $the_product_info = false;
                if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                    $the_product_info_query = tep_db_query("select pd.language_id, p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description), pd1.products_description, pd.products_description) as products_description, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, if(length(pd1.products_head_title_tag), pd1.products_head_title_tag, pd.products_head_title_tag) as products_head_title_tag, if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) as products_head_keywords_tag, if(length(pd1.products_head_desc_tag), pd1.products_head_desc_tag, pd.products_head_desc_tag) as products_head_desc_tag, pd.overwrite_head_title_tag, pd.overwrite_head_desc_tag, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id, pd.products_self_service from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "' " . " left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True' ? (int)\Yii::$app->settings->get('currency_id') : '0'). "' where p.products_id = '" . (int)$params['products_id'] . "'  " . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and pd.products_id = '" . (int)$params['products_id'] . "'" . " and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.language_id ='" .  (int)$languages_id . "'");
                } else {
                    $the_product_info_query = tep_db_query("select pd.language_id, p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description), pd1.products_description, pd.products_description) as products_description, if(length(pd1.products_description_short), pd1.products_description_short, pd.products_description_short) as products_description_short, if(length(pd1.products_head_title_tag), pd1.products_head_title_tag, pd.products_head_title_tag) as products_head_title_tag, if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) as products_head_keywords_tag, if(length(pd1.products_head_desc_tag), pd1.products_head_desc_tag, pd.products_head_desc_tag) as products_head_desc_tag, pd.overwrite_head_title_tag, pd.overwrite_head_desc_tag, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id, pd.products_self_service from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "'  " . " where p.products_id = '" . (int)$params['products_id'] . "' " . " and pd.products_id = '" . (int)$params['products_id'] . "'" . " and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.language_id ='" .  (int)$languages_id . "'");
                }
                if ( tep_db_num_rows($the_product_info_query)>0 ) {
                    $the_product_info = tep_db_fetch_array($the_product_info_query);
                }
                \common\helpers\Php8::nullArrProps($the_product_info, ['products_id', 'products_head_title_tag', 'products_model', 'manufacturers_id', 'products_description', 'products_description_short', 'products_name', 'products_head_title_tag', 'products_self_service', 'products_head_keywords_tag', 'overwrite_head_desc_tag']);

                if (!is_array($the_product_info) || empty($the_product_info['products_head_title_tag']) || !$the_product_info['overwrite_head_title_tag']) {
                    $the_title = (defined('HEAD_TITLE_TAG_PRODUCT_INFO') && tep_not_null(HEAD_TITLE_TAG_PRODUCT_INFO) ? HEAD_TITLE_TAG_PRODUCT_INFO : $HEAD_TITLE_TAG_ALL);
                    if (strstr($the_title, '##DOCUMENTS##')) {
                        $documents_array = array();
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductDocuments', 'allowed')) {
                            $documents = $ext::getDocuments($the_product_info['products_id']);
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
                        $the_title = str_replace('##DOCUMENTS##', implode(', ', $documents_array), $the_title);
                    }
                    if (strstr($the_title, '##PRODUCT_MODEL##')) {
                        $the_title = str_replace('##PRODUCT_MODEL##', $the_product_info['products_model'], $the_title);
                    }
                    if (strstr($the_title, '##BRAND_NAME##')) {
                        $the_title = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $the_product_info['manufacturers_id'] ?? null), $the_title);
                    }
                    if (strstr($the_title, '##CATEGORY_NAME##') || strstr($the_title, '##BREADCRUMB##')) {
                        $product_cPath = \common\helpers\Product::get_product_path($the_product_info['products_id'] ?? null);
                        $cats_array = empty($product_cPath)?[]:explode('_', $product_cPath);
                        $breadcrumb_array = array();
                        foreach ($cats_array as $cat_id) {
                            $breadcrumb_array[] = \common\helpers\Categories::get_categories_name($cat_id);
                        }
                        if ( count($breadcrumb_array)==0 && Seo::getMetaDefaultBreadcrumb($full_action) ) $breadcrumb_array[] = Seo::getMetaDefaultBreadcrumb($full_action);
                        $the_title = str_replace('##BREADCRUMB##', implode(' / ', $breadcrumb_array), $the_title);
                        $the_title = str_replace('##CATEGORY_NAME##', $breadcrumb_array[count($breadcrumb_array)-1], $the_title);
                    }
                    if (strstr($the_title, '##PRODUCT_TITLE_TAG##')) {
                        $the_title = str_replace('##PRODUCT_TITLE_TAG##', $the_product_info['products_head_title_tag'] ?? null, $the_title);
                    }
                    if ( preg_match_all('/##PRODUCT(_SHORT)?_DESCRIPTION(_([Nn|\d]+))?##/', $the_title, $description_replace) ){
                        foreach ( $description_replace[0] as $idx=>$replace_key ){
                            $data_from_key = 'products_description'.strtolower($description_replace[1][$idx]);
                            $replace_to_value = isset($the_product_info[$data_from_key])?$the_product_info[$data_from_key]:'';
                            $replace_to_value = preg_replace('/\s{2,}/', ' ', \common\helpers\Output::strip_tags($replace_to_value));
                            if (is_numeric($description_replace[3][$idx])) {
                                $replace_to_value = \common\helpers\Output::truncate($replace_to_value, (int)$description_replace[3][$idx], '');
                            }
                            $the_title = str_replace($replace_key, $replace_to_value, $the_title);
                        }
                    }
                    if (strstr($the_title, '##PRODUCT_NAME##')) {
                        $the_title = str_replace('##PRODUCT_NAME##', $the_product_info['products_name'] ?? null, $the_title);
                    } else {
                        $the_title = $the_title . ' ' . ($the_product_info['products_name'] ?? null);
                    }
                } else {
                    $the_title = $the_product_info['products_head_title_tag'] ?? null;
                }

                if (is_array($the_product_info) || !empty($the_product_info['products_self_service'])) {
                    $selfService = $the_product_info['products_self_service'];
                }

                if (!is_array($the_product_info) || empty($the_product_info['products_head_keywords_tag'])) {
                    $the_key_words = (defined('HEAD_KEY_TAG_PRODUCT_INFO') && tep_not_null(HEAD_KEY_TAG_PRODUCT_INFO) ? HEAD_KEY_TAG_PRODUCT_INFO : $HEAD_KEY_TAG_ALL);
                    if (strstr($the_key_words, '##DOCUMENTS##')) {
                        $documents_array = array();
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductDocuments', 'allowed')) {
                            $documents = $ext::getDocuments($the_product_info['products_id'] ?? null);
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
                        $the_key_words = str_replace('##DOCUMENTS##', implode(', ', $documents_array), $the_key_words);
                    }
                    if (strstr($the_key_words, '##BRAND_NAME##')) {
                        $the_key_words = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $the_product_info['manufacturers_id'] ?? null), $the_key_words);
                    }
                    if (strstr($the_key_words, '##CATEGORY_NAME##') || strstr($the_key_words, '##BREADCRUMB##')) {
                        $product_cPath = \common\helpers\Product::get_product_path($the_product_info['products_id'] ?? null);
                        $cats_array = empty($product_cPath)?[]:explode('_', $product_cPath);
                        $breadcrumb_array = array();
                        foreach ($cats_array as $cat_id) {
                            $breadcrumb_array[] = \common\helpers\Categories::get_categories_name($cat_id);
                        }
                        if ( count($breadcrumb_array)==0 && Seo::getMetaDefaultBreadcrumb($full_action) ) $breadcrumb_array[] = Seo::getMetaDefaultBreadcrumb($full_action);
                        $the_key_words = str_replace('##BREADCRUMB##', implode(' / ', $breadcrumb_array), $the_key_words);
                        $the_key_words = str_replace('##CATEGORY_NAME##', $breadcrumb_array[count($breadcrumb_array)-1], $the_key_words);
                    }
                    if (strstr($the_key_words, '##PRODUCT_NAME##')) {
                        $the_key_words = str_replace('##PRODUCT_NAME##', $the_product_info['products_name'] ?? null, $the_key_words);
                    } else {
                        $the_key_words = ($the_product_info['products_name'] ?? null). ', ' . $the_key_words;
                    }
                } else {
                    $the_key_words = $the_product_info['products_head_keywords_tag'] ?? null;
                }

                if (!is_array($the_product_info) || empty($the_product_info['products_head_desc_tag']) || !($the_product_info['overwrite_head_desc_tag'] ?? null)) {
                    $the_desc = (defined('HEAD_DESC_TAG_PRODUCT_INFO') && tep_not_null(HEAD_DESC_TAG_PRODUCT_INFO) ? HEAD_DESC_TAG_PRODUCT_INFO : $HEAD_DESC_TAG_ALL);
                    if (strstr($the_desc, '##DOCUMENTS##')) {
                        $documents_array = array();
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductDocuments', 'allowed')) {
                            $documents = $ext::getDocuments($the_product_info['products_id'] ?? null);
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
                        $the_desc = str_replace('##DOCUMENTS##', implode(', ', $documents_array), $the_desc);
                    }
                    if (strstr($the_desc, '##BRAND_NAME##')) {
                        $the_desc = str_replace('##BRAND_NAME##', \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $the_product_info['manufacturers_id'] ?? null) ?? '', $the_desc);
                    }
                    if (strstr($the_desc, '##CATEGORY_NAME##') || strstr($the_desc, '##BREADCRUMB##')) {
                        $product_cPath = \common\helpers\Product::get_product_path($the_product_info['products_id'] ?? null);
                        $cats_array = empty($product_cPath)?[]:explode('_', $product_cPath);
                        $breadcrumb_array = array();
                        foreach ($cats_array as $cat_id) {
                            $breadcrumb_array[] = \common\helpers\Categories::get_categories_name($cat_id);
                        }
                        if ( count($breadcrumb_array)==0 && Seo::getMetaDefaultBreadcrumb($full_action) ) $breadcrumb_array[] = Seo::getMetaDefaultBreadcrumb($full_action);
                        $the_desc = str_replace('##BREADCRUMB##', implode(' / ', $breadcrumb_array), $the_desc);
                        $the_desc = str_replace('##CATEGORY_NAME##', $breadcrumb_array[count($breadcrumb_array)-1] ?? null, $the_desc);
                    }
                    if (strstr($the_desc, '##PRODUCT_DESCRIPTION_TAG##')) {
                        $the_desc = str_replace('##PRODUCT_DESCRIPTION_TAG##', $the_product_info['products_head_desc_tag'] ?? null, $the_desc);
                    }

                    if ( preg_match_all('/##PRODUCT(_SHORT)?_DESCRIPTION(_([Nn|\d]+))?##/', $the_desc, $description_replace) ){
                        foreach ( $description_replace[0] as $idx=>$replace_key ){
                            $data_from_key = 'products_description'.strtolower($description_replace[1][$idx]);
                            $replace_to_value = isset($the_product_info[$data_from_key])?$the_product_info[$data_from_key]:'';
                            $replace_to_value = preg_replace('/\s{2,}/', ' ', \common\helpers\Output::strip_tags($replace_to_value));
                            if (is_numeric($description_replace[3][$idx])) {
                                $replace_to_value = \common\helpers\Output::truncate($replace_to_value, (int)$description_replace[3][$idx], '');
                            }
                            $the_desc = str_replace($replace_key, $replace_to_value, $the_desc);
                        }
                    }
                    if (strstr($the_desc, '##PRODUCT_NAME##')) {
                        $the_desc = str_replace('##PRODUCT_NAME##', $the_product_info['products_name'] ?? null, $the_desc);
                    } else {
                        $the_desc = ($the_product_info['products_name']  ?? null). ' ' . $the_desc;
                    }
                } else {
                    $the_desc = $the_product_info['products_head_desc_tag'] ?? null;
                }
                break;

            // ALL PRODUCTS
            case ( $full_action=='catalog/all-products' ):
                $the_title = (defined('HEAD_TITLE_TAG_PRODUCTS_ALL') && tep_not_null(HEAD_TITLE_TAG_PRODUCTS_ALL)?HEAD_TITLE_TAG_PRODUCTS_ALL:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_PRODUCTS_ALL') && tep_not_null(HEAD_KEY_TAG_PRODUCTS_ALL)?HEAD_KEY_TAG_PRODUCTS_ALL:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_PRODUCTS_ALL') && tep_not_null(HEAD_DESC_TAG_PRODUCTS_ALL)?HEAD_DESC_TAG_PRODUCTS_ALL:$HEAD_DESC_TAG_ALL);
                break;

            // FREE SAMPLES
            case ( $full_action=='catalog/free-samples' ):
                $the_title = (defined('HEAD_TITLE_TAG_FREE_SAMPLES') && tep_not_null(HEAD_TITLE_TAG_FREE_SAMPLES)?HEAD_TITLE_TAG_FREE_SAMPLES:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_FREE_SAMPLES') && tep_not_null(HEAD_KEY_TAG_FREE_SAMPLES)?HEAD_KEY_TAG_FREE_SAMPLES:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_FREE_SAMPLES') && tep_not_null(HEAD_DESC_TAG_FREE_SAMPLES)?HEAD_DESC_TAG_FREE_SAMPLES:$HEAD_DESC_TAG_ALL);
                break;

            // FEATURED
            case ( $full_action=='catalog/featured-products' ):
                $the_title = (defined('HEAD_TITLE_TAG_FEATURED') && tep_not_null(HEAD_TITLE_TAG_FEATURED)?HEAD_TITLE_TAG_FEATURED:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_FEATURED') && tep_not_null(HEAD_KEY_TAG_FEATURED)?HEAD_KEY_TAG_FEATURED:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_FEATURED') && tep_not_null(HEAD_DESC_TAG_FEATURED)?HEAD_DESC_TAG_FEATURED:$HEAD_DESC_TAG_ALL);
                break;

            // PRODUCTS_NEW
            case ( $full_action=='catalog/products-new' ):
                $the_title = (defined('HEAD_TITLE_TAG_WHATS_NEW') && tep_not_null(HEAD_TITLE_TAG_WHATS_NEW)?HEAD_TITLE_TAG_WHATS_NEW:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_WHATS_NEW') && tep_not_null(HEAD_KEY_TAG_WHATS_NEW)?HEAD_KEY_TAG_WHATS_NEW:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_WHATS_NEW') && tep_not_null(HEAD_DESC_TAG_WHATS_NEW)?HEAD_DESC_TAG_WHATS_NEW:$HEAD_DESC_TAG_ALL);
                break;

            // SPECIALS.PHP
            case ( $full_action=='catalog/sales' ):
                $the_title = (defined('HEAD_TITLE_TAG_SPECIALS') && tep_not_null(HEAD_TITLE_TAG_SPECIALS)?HEAD_TITLE_TAG_SPECIALS:$HEAD_TITLE_TAG_ALL);

                $products_join = '';
                if ( platform::activeId() ) {
                  $products_join .= $this->sqlProductsToPlatformCategories();
                }

                $new = tep_db_query(
                  "select distinct if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name ".
                  "from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS . " p {$products_join} " . "  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "' where " . Product::getState() . Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and s.products_id = p.products_id and p.products_id = pd.products_id  " . " and pd.language_id = '" . (int)$languages_id . "' and s.status = '1' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' order by s.specials_date_added DESC ".
                  "limit 10"
                );

                $the_specials='';
                while ($new_values = tep_db_fetch_array($new)) {
                    $the_specials .= $new_values['products_name'] . ', ';
                }
                $the_key_words = $the_specials . ', ' . (defined('HEAD_KEY_TAG_SPECIALS') && tep_not_null(HEAD_KEY_TAG_SPECIALS)?HEAD_KEY_TAG_SPECIALS:$HEAD_KEY_TAG_ALL);

                $the_desc = (defined('HEAD_DESC_TAG_SPECIALS') && tep_not_null(HEAD_DESC_TAG_SPECIALS)?HEAD_DESC_TAG_SPECIALS:$HEAD_DESC_TAG_ALL);
                break;

// PRODUCTS_REVIEWS_INFO.PHP and PRODUCTS_REVIEWS.PHP
            case ( strpos($full_action,'reviews/')===0 ):
                $the_title = (defined('HEAD_TITLE_TAG_PRODUCT_REVIEWS_INFO') && tep_not_null(HEAD_TITLE_TAG_PRODUCT_REVIEWS_INFO)?HEAD_TITLE_TAG_PRODUCT_REVIEWS_INFO:$HEAD_TITLE_TAG_ALL) . ' ' . \common\helpers\HeaderTags::get_header_tag_products_title($_GET['products_id']);
                $the_key_words = \common\helpers\HeaderTags::get_header_tag_products_keywords($_GET['products_id']) . ', ' . (defined('HEAD_KEY_TAG_PRODUCT_REVIEWS_INFO') && tep_not_null(HEAD_KEY_TAG_PRODUCT_REVIEWS_INFO)?HEAD_KEY_TAG_PRODUCT_REVIEWS_INFO:$HEAD_KEY_TAG_ALL);
                $the_desc = \common\helpers\HeaderTags::get_header_tag_products_desc($_GET['products_id']) . ' ' . (defined('HEAD_DESC_TAG_PRODUCT_REVIEWS_INFO') && tep_not_null(HEAD_DESC_TAG_PRODUCT_REVIEWS_INFO)?HEAD_DESC_TAG_PRODUCT_REVIEWS_INFO:$HEAD_DESC_TAG_ALL);
                break;

// INFORMATION.PHP
      case (($full_action=='info/index' || $full_action=='info/') && (isset($params['info_id']) && $params['info_id']>0) ):
        $row_info_page = InformationPage::getFrontendDataVisible((int)$params['info_id']);
        if(!empty($row_info_page))
        {
            $the_desc = (strlen($row_info_page['meta_description'])>0?$row_info_page['meta_description']:$HEAD_DESC_TAG_ALL);
            $the_key_words = (strlen($row_info_page['meta_key'])>0?$row_info_page['meta_key']:$HEAD_KEY_TAG_ALL);
            if ( !empty($row_info_page['meta_title']) ) {
                $the_title = $row_info_page['meta_title'];
            }else {
                $the_title = strlen($row_info_page['page_title']) > 0 ? $row_info_page['page_title'] . ' ' . $HEAD_TITLE_TAG_ALL : $HEAD_TITLE_TAG_ALL;
            }
        }

        break;
// CATALOG PAGES
            case ( strpos($full_action,'catalog-pages/')===0 ):
                $the_title = (defined('HEAD_TITLE_TAG_CATALOG_PAGES') && tep_not_null(HEAD_TITLE_TAG_CATALOG_PAGES)?HEAD_TITLE_TAG_CATALOG_PAGES:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_CATALOG_PAGES') && tep_not_null(HEAD_KEY_TAG_CATALOG_PAGES)?HEAD_KEY_TAG_CATALOG_PAGES:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_CATALOG_PAGES') && tep_not_null(HEAD_DESC_TAG_CATALOG_PAGES)?HEAD_DESC_TAG_CATALOG_PAGES:$HEAD_DESC_TAG_ALL);
                $platformId = platform::activeId()?platform::activeId():platform::currentId();
                $slug = tep_db_prepare_input($params['page']);
                $catalogPage = Yii::$app->db->createCommand("
                        SELECT cd.meta_title,cd.meta_description,cd.meta_keyword
                        FROM catalog_pages_description cd
                        INNER JOIN catalog_pages cp ON cd.catalog_pages_id = cp.catalog_pages_id AND cp.platform_id = $platformId
                        WHERE (cd.languages_id = $languages_id) AND (cd.slug = '" . tep_db_input($slug) . "')")
                    ->queryOne();
                if(!empty($catalogPage['meta_title'])){
                    $the_title = $catalogPage['meta_title'];
                }
                if(!empty($catalogPage['meta_keyword'])){
                    $the_key_words = $catalogPage['meta_keyword'];
                }
                if(!empty($catalogPage['meta_description'])){
                    $the_desc = $catalogPage['meta_description'];
                }
                break;
// GIFT CARD
            case ( $full_action=='catalog/gift-card' ):
                $the_title = (defined('HEAD_TITLE_TAG_GIFT_CARD') && tep_not_null(HEAD_TITLE_TAG_GIFT_CARD)?HEAD_TITLE_TAG_GIFT_CARD:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_GIFT_CARD') && tep_not_null(HEAD_KEY_TAG_GIFT_CARD)?HEAD_KEY_TAG_GIFT_CARD:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_GIFT_CARD') && tep_not_null(HEAD_DESC_TAG_GIFT_CARD)?HEAD_DESC_TAG_GIFT_CARD:$HEAD_DESC_TAG_ALL);
                break;

// CONTACT US
            case ( strpos($full_action,'contact/')===0 ):
                $the_title = (defined('HEAD_TITLE_TAG_CONTACT_US') && tep_not_null(HEAD_TITLE_TAG_CONTACT_US)?HEAD_TITLE_TAG_CONTACT_US:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_CONTACT_US') && tep_not_null(HEAD_KEY_TAG_CONTACT_US)?HEAD_KEY_TAG_CONTACT_US:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_CONTACT_US') && tep_not_null(HEAD_DESC_TAG_CONTACT_US)?HEAD_DESC_TAG_CONTACT_US:$HEAD_DESC_TAG_ALL);
        break;

// WEDDING REGISTRY
            case ( strpos($full_action,'wedding-registry/')===0 ):
                $the_title = (defined('HEAD_TITLE_TAG_WEDDING_REGISTRY') && tep_not_null(HEAD_TITLE_TAG_WEDDING_REGISTRY)?HEAD_TITLE_TAG_WEDDING_REGISTRY:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_WEDDING_REGISTRY') && tep_not_null(HEAD_KEY_TAG_WEDDING_REGISTRY)?HEAD_KEY_TAG_WEDDING_REGISTRY:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_WEDDING_REGISTRY') && tep_not_null(HEAD_DESC_TAG_WEDDING_REGISTRY)?HEAD_DESC_TAG_WEDDING_REGISTRY:$HEAD_DESC_TAG_ALL);
        break;

// ALL OTHER PAGES NOT DEFINED ABOVE

    default:

        // SEO addon
/*        $query_infromation_page = tep_db_query(
          "select page_title, meta_description, meta_key from ".TABLE_INFORMATION." WHERE page = '" . tep_db_input($full_action) . "' AND visible = '1' AND languages_id = '" . $languages_id . "' AND affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "'"
        );*/
        $query_infromation_page = tep_db_query(
            "select if(length(i1.info_title), i1.info_title, i.info_title) as info_title, ".
            "if(length(i1.page_title), i1.page_title, i.page_title) as page_title, ".
            "if(length(i1.meta_title), i1.meta_title, i.meta_title) as meta_title, ".
            "if(length(i1.meta_description), i1.meta_description, i.meta_description) as meta_description, ".
            "if(length(i1.meta_key), i1.meta_key, i.meta_key) as meta_key, ".
            "i.information_id ".
            "from " . TABLE_INFORMATION . " i LEFT JOIN " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id  and i1.languages_id = '" . (int)$languages_id . "' and i1.affiliate_id = '" . Affiliate::id() . "'  where i.page = '" . tep_db_input($full_action) . "' and i.languages_id = '" . (int)$languages_id . "' and i.visible = 1 and i.affiliate_id = 0 ".
            "limit 1"
        );
        if(tep_db_num_rows($query_infromation_page))
        {
            $row_info_page = tep_db_fetch_array($query_infromation_page);
            $the_desc = (strlen($row_info_page['meta_description'])>0?$row_info_page['meta_description']:$HEAD_DESC_TAG_ALL);
            $the_key_words = (strlen($row_info_page['meta_key'])>0?$row_info_page['meta_key']:$HEAD_KEY_TAG_ALL);
            $the_title = strlen($row_info_page['page_title'])>0?$row_info_page['page_title'] . ' ' . $HEAD_TITLE_TAG_ALL:$HEAD_TITLE_TAG_ALL;
        }

        // eof SEO addon
        break;

}
/*function prepare_tags($value) {
    $value = \common\helpers\Output::unhtmlentities($value);
    $value = str_replace('"', "'", $value);
    $value = str_replace(array("\n","\r","\r\n","\n\r"), " ", $value);
    $value = strip_tags($value);
    return $value;
}*/

//echo '  <title>' . prepare_tags($the_title) . '</title>' . "\n";
//echo '  <META NAME="Description" Content="' . prepare_tags($the_desc) . '">' . "\n";
//echo '  <META NAME="Keywords" CONTENT="' . prepare_tags($the_key_words) . '">' . "\n";

        $the_title = strip_tags(str_replace(array("\n","\r","\r\n","\n\r"), " ", str_replace('<', ' <', $the_title)));
        $the_title = preg_replace('/##[A-Z_]+##/', '', $the_title);
        $the_title = preg_replace('/\s{2,}/', ' ', $the_title);
        $the_title = trim($the_title);
        $META_TITLE_MAX_TAG_LENGTH = (defined('META_TITLE_MAX_TAG_LENGTH') && (int)META_TITLE_MAX_TAG_LENGTH>0)?intval(META_TITLE_MAX_TAG_LENGTH):75;
        if (mb_strlen($the_title) > $META_TITLE_MAX_TAG_LENGTH) {
            // Title should be 75 characters or less
            $the_title = mb_substr($the_title, 0 , max(0,$META_TITLE_MAX_TAG_LENGTH-3)) . '...';
        }

        $the_key_words = strip_tags(str_replace(array("\n","\r","\r\n","\n\r"), " ", str_replace('<', ' <', $the_key_words)));
        $the_key_words = preg_replace('/##[A-Z_]+##/', '', $the_key_words);
        $the_key_words = preg_replace('/\s{2,}/', ' ', $the_key_words);
        $the_key_words = trim(trim($the_key_words),',');

        $the_desc = strip_tags(str_replace(array("\n","\r","\r\n","\n\r"), " ", str_replace('<', ' <', $the_desc)));
        $the_desc = preg_replace('/##[A-Z_]+##/', '', $the_desc);
        $the_desc = preg_replace('/\s{2,}/', ' ', $the_desc);
        $the_desc = trim(trim($the_desc),',');

        foreach (\common\helpers\Hooks::getList('sceleton/set-meta', '') as $filename) {
            include($filename);
        }
        foreach (\common\helpers\Hooks::getList('frontend/sceleton/set-meta', '') as $filename) {
            include($filename);
        }

        if ( !empty($the_title) ) {
            \Yii::$app->view->title = $the_title;
        }else{
            \Yii::$app->view->title = STORE_NAME;
        }
        if ( !empty($the_desc) ) {
            $this->getView()->registerMetaTag([
              'name' => 'Description',
              'content' => $the_desc
            ], 'Description');
        }
/* Don't show keywords meta tag
        if ( !empty($the_key_words) ) {
            $this->getView()->registerMetaTag([
              'name' => 'Keywords',
              'content' => $the_key_words
            ], 'Keywords');
        }
*/
        $this->getView()->registerMetaTag([
          'name' => 'Reply-to',
          'content' => STORE_OWNER_EMAIL_ADDRESS
        ],'Reply-to');
        $this->getView()->registerMetaTag([
          'name' => 'Author',
          'content' => STORE_OWNER
        ],'Author');
        $this->getView()->registerMetaTag([
          'name' => 'Robots',
          'content' => 'index,follow'
        ],'Robots');

        $this->registerHrefLang($full_action);

        if (class_exists('\common\components\google\widgets\GoogleVerification')){
            \common\components\google\widgets\GoogleVerification::verify();
        }

        if ( false && defined('TRUSTPILOT_VERIFY_META_TAG') && TRUSTPILOT_VERIFY_META_TAG!='' ){
            if ( preg_match('/name="([^"]+)"/i',TRUSTPILOT_VERIFY_META_TAG, $nameMatch) && preg_match('/content="([^"]+)"/i',TRUSTPILOT_VERIFY_META_TAG, $contentMatch) ) {
                $this->getView()->registerMetaTag([
                    'name' => $nameMatch[1],
                    'content' => $contentMatch[1]
                ],'Trustpilot');
            }
        }

        if ( !empty($selfService) ) {
            $this->getView()->metaTags[] = $selfService;
        }
    }

    public function registerHrefLang($action){
        global $request_type;
        global $lng, $languages_id;
        $settings = Yii::$app->urlManager->getSettings();
        if ($settings['use_hraflang_metatag']){
            $_l = \yii\helpers\ArrayHelper::index($lng->catalog_languages, 'code');
            $platforms_languages = array_intersect_key($_l, array_flip($lng->paltform_languages));
            //if(count($platforms_languages) == 1) return;
            $map = \yii\helpers\ArrayHelper::getColumn($platforms_languages, 'id');
            $pageParams = [$action];
            $params = Yii::$app->request->getQueryParams();
            if ( !is_array($params) ) $params = [];
            $pageParams = array_merge($pageParams, $params);

            if (count($platforms_languages) > 1)
            foreach ( $map as $_lang_code=>$_lang_id ) {

                $_hrefLang = $platforms_languages[$_lang_code]['code'];
                if ( $lng->catalog_languages[$platforms_languages[$_lang_code]['code']]['locale'] ) {
                    $_hrefLang = str_replace('_','-',$lng->catalog_languages[$platforms_languages[$_lang_code]['code']]['locale']);
                }

                $pageParams['language'] = $_lang_code;
                $url = Yii::$app->getUrlManager()->createAbsoluteUrl($pageParams, ($request_type == 'SSL'? 'https' : 'http'));

                $this->getView()->registerLinkTag([
                    'rel' => 'alternate',
                    'hreflang' => $_hrefLang,
                    'href' => $url,
                ],'alternate_lang_'.$_hrefLang);
            }

            foreach (\common\helpers\Hooks::getList('sceleton/register-href-lang', '') as $filename) {
                include($filename);
            }
            foreach (\common\helpers\Hooks::getList('frontend/sceleton/register-href-lang', '') as $filename) {
                include($filename);
            }

            return ;


            if (false && method_exists($this, 'getHerfLang')){
                $_l = \yii\helpers\ArrayHelper::index($lng->catalog_languages, 'code');
                $platforms_languages = array_intersect_key($_l, array_flip($lng->paltform_languages));
                if(count($platforms_languages) == 1) return;
                $map = \yii\helpers\ArrayHelper::getColumn($platforms_languages, 'id');
                $platforms_languages = \yii\helpers\ArrayHelper::index($platforms_languages, 'id');
                $xDefault = '';
                foreach($this->getHerfLang($map) as $l => $seo_name_data) {
                    $except = [];
                    if (is_array($seo_name_data)){
                        $seo_name = $seo_name_data[0];
                        $except = $seo_name_data[1];
                    } else {
                        $seo_name = $seo_name_data;
                    }
                    $queryParams = Yii::$app->request->getQueryParams();
                    $queryParams['language'] = $platforms_languages[$l]['code'];
                    $queryParams = array_diff($queryParams, $except);
                    array_unshift($queryParams, $seo_name);
                    $stripedQueryParams = [];
                    foreach ($queryParams as $key => $value) {
                        $key = preg_replace('#<script[^>]*>(.*?)</script>#is', '', $key);
                        $value = preg_replace('#<script[^>]*>(.*?)</script>#is', '', $value);
                        $key = preg_replace(['/\'\'/'], ['',''], $key);
                        $value = preg_replace([], ['',''], $value);
                        $stripedQueryParams[strip_tags($key)] = strip_tags($value);
                    }
                    $url = Yii::$app->getUrlManager()->createAbsoluteUrl($stripedQueryParams, ($request_type == 'SSL'? 'https' : 'http'));

                    if ($lng->dp_language && $lng->catalog_languages[$lng->dp_language]['id'] == $l){
                        if (!($settings['search_engine_friendly_urls'] && $settings['search_engine_unhide'] && $settings['seo_url_parts_language'])){
                            unset($stripedQueryParams['language']);
                        }
                        //$xDefault = Yii::$app->getUrlManager()->createAbsoluteUrl($queryParams, ($request_type == 'SSL'? 'https' : 'http'));
                        //continue;
                    }

                    $_hrefLang = $platforms_languages[$l]['code'];
                    if ( $lng->catalog_languages[$platforms_languages[$l]['code']]['locale'] ) {
                        $_hrefLang = str_replace('_','-',$lng->catalog_languages[$platforms_languages[$l]['code']]['locale']);
                    }

                    $this->getView()->registerLinkTag([
                        'rel' => 'alternate',
                        'hreflang' => $_hrefLang,
                        'href' => $url,
                      ],'alternate_lang_'.$_hrefLang);
                }
                /*if(!empty($xDefault)){
                    $this->getView()->registerLinkTag([
                        'rel' => 'alternate',
                        'hreflang' => 'x-default',
                        'href' => $xDefault,
                      ]);
                }*/
            }
        }
        //echo '<pre>';print_r(Yii::$app);die;
    }

    public function bindActionParams($action, $params)
    {
        global $language, $languages_id;

        foreach (\common\helpers\Hooks::getList('frontend/sceleton/bindactionparams') as $filename) {
            include($filename);
        }

        if ( IS_IMAGE_CDN_SERVER && $action->controller->id!='image' ) {
          tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            $ext::bindParams($action);
        }

        if (Yii::$app->request->isAjax) {// getIsAjax()

            $this->layout = 'ajax.tpl';

        } else {

            $this->view->page_layout = 'custom'; //use 'default'  page_layout for non designer pages
            //$this->view->page_layout = 'default';

          $this->layout = 'main.tpl';
        }

        $cookies = Yii::$app->response->cookies;
        $cookies->add(new \yii\web\Cookie(array_merge(\common\helpers\System::get_cookie_params(), [
            'name' => 'was_visit',
            'value' => '1',
            'expire' => time() + 3600*24*365,
        ])));

        $get_block = Yii::$app->request->get('get_block', false); //VL - suppose $params (overwrite) here was by mistake
        if (!empty($get_block)) {
          $this->view->block_id = filter_var($get_block, FILTER_SANITIZE_STRING);
          $params = array_merge(Yii::$app->request->get(), $params); //VL just in case 
          $this->layout = 'get-block.tpl';
        }

        if ($action->id == 'index') {
            \common\helpers\Translation::init($action->controller->id);
        } else {
            \common\helpers\Translation::init($action->controller->id . '/' . $action->id);
        }
        \common\helpers\Translation::init('main');

		//if (!\frontend\design\Info::isAdminOrders())
			\app\components\CartFactory::work();

        /*\Yii::$app->view->on(\yii\web\View::EVENT_END_BODY, function(){
            \frontend\assets\AppAsset::register($this->getView());
        });*/

        return parent::bindActionParams($action, $params);
    }

    public function render($view, $params = [])
    {
        if (\app\components\MetaCannonical::getStatus() != 200) {
            $this->getView()->clear();
        }
        $this->view->page_params = (isset($params['params']) ? $params['params'] : []);
        //paypal fraudnet
        if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_FRAUDNET_SI') && !empty(MODULE_PAYMENT_PAYPAL_PARTNER_FRAUDNET_SI)) {
            \common\modules\orderPayment\paypal_partner::fraudnetInit();
        }

        //$headers = Yii::$app->response->headers;
        //$headers->set('X-Frame-Options', 'SAMEORIGIN');
        $content = $this->getView()->render($view, $params, $this);

        $renderContent = \common\helpers\Translation::frontendTranslation($this->renderContent($content));
        return $renderContent;
    }

    public function getHerfLang($platforms_languages){
        $list = [];
        if (is_array($platforms_languages)){
            $route = Yii::$app->urlManager->parseRequest(Yii::$app->request);
            foreach($platforms_languages as $pl){
                $list[$pl] = (isset($route[0])?$route[0]:$this->getRoute());
            }
        }

        return $list;
    }
    
    public function actions() {
        $actions = parent::actions();
        $actions = array_merge($actions, \common\helpers\Acl::getExtensionActions($this->id));
        return $actions;
    }

    public function beforeAction($action) {
        if (stripos(\Yii::$app->request->getUserAgent(), 'MSIE')) {
            $this->getView()->registerJsFile(Info::themeFile('/js/promise/es6-promise.auto.min.js'), ['position' => \yii\web\View::POS_HEAD], 'promise_polyfill');
        }
        $response =  parent::beforeAction($action);
        if ( \Yii::$app->request->isAjax ) {
            return $response;
        }
        if($this->enableCsrfValidation && \Yii::$app->request->isPost && \Yii::$app->request->validateCsrfToken()) {
            \Yii::$app->request->getCsrfToken(true);
        }
        return $response;
    }
}
