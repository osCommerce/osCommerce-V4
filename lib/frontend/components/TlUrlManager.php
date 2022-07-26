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

namespace app\components;

use common\services\CustomersService;
use yii\web\UrlManager;

class TlUrlManager extends UrlManager
{

    protected $overrideSettings = [];

    public function init()
    {
        $x = parent::init();
        $this->addRules([
          'robots.txt' => 'index/robots-txt',
          'xmlsitemap/products<page:\d+>' => 'xmlsitemap/products',
          'xmlsitemap/images<page:\d+>' => 'xmlsitemap/images',
          'xmlsitemap/delivery-location<page:\d+>' => 'xmlsitemap/delivery-location',
          'images/cached/<image:.*>' => 'image/cached',
          'api/v1/<api_action:.*>' => 'api/v1',
          'catalog-pages/<page:[\w\-]+>/page-<pageNum:\d+>' => 'catalog-pages/post',
          'catalog-pages/<page:[\w\-]+>' => 'catalog-pages/post',
          'catalog-pages' =>  'catalog-pages/index',
          'callback/webhooks.<set:[^.]*>.<module:[^.]*>' => 'callback/webhooks',
          '403' => 'index/error-forbidden',
        ],true);
        return $x;
    }

    public function setOverrideSettings($settingsArray)
    {
        if ( is_array($settingsArray) ) {
            $this->overrideSettings = $settingsArray;
        }else{
            $this->overrideSettings = [];
        }
    }

    public function createAbsoluteUrl($params, $scheme = null, $ignoreConstant = false)
    {
        //???
      // i ja togo zhe mnenija
      if (!$ignoreConstant) {
        if ($scheme == 'https' && ENABLE_SSL == true) {
            $this->setHostInfo(HTTPS_SERVER);
            $this->setBaseUrl(rtrim(DIR_WS_HTTPS_CATALOG, '/'));
        } else {
            if ( substr(HTTP_SERVER,0,6)=='https:' ) {
                $scheme = 'https';
            }
            $this->setHostInfo(HTTP_SERVER);
            $this->setBaseUrl(rtrim(DIR_WS_HTTP_CATALOG, '/'));
        }
      }
        return parent::createAbsoluteUrl($params, $scheme);
    }

    public function parseRequest($request) {
        global $lng, $languages_id;
        $currencies = \Yii::$container->get('currencies');
        $currency = \Yii::$app->settings->get('currency');
        $currency_id = \Yii::$app->settings->get('currency_id');
        $settings = $this->getSettings();
        static $parsed = false;
        $urlWithParts = [];
        if ($settings['search_engine_friendly_urls'] && $settings['search_engine_unhide']){
            $seo_path = trim($request->getPathInfo());
//// image on Nginx w/o Apache fix
            $imageMatch = [];
            if ( preg_match('#^images/(.*/\d+/[^/]*x[^/]*/.*)$#', $seo_path, $imageMatch) ){
                return ['image/cached',['image'=>$imageMatch[1]]];
            }

            if ( $settings['seo_url_parts_language'] ) {
                preg_match("/^([a-zA-Z]{2})(\/(.*))?$/", $seo_path, $found);
                if ($found && is_array($found) && isset($found[1])) {
                    $selected_language_code = $found[1];
                    if (isset($lng->catalog_languages[$selected_language_code]) && in_array($selected_language_code, $lng->paltform_languages)) {
                        $languages_id = $lng->catalog_languages[$selected_language_code]['id'];
                        //$seo_path = preg_replace("/^([a-zA-Z]{2})\//", "", $seo_path);
                        $seo_path = isset($found[3]) ? $found[3] : '';
                        $request->setPathInfo($seo_path);
                        $urlWithParts['language'] = 'language';
                    }
                    $parsed = true;
                } else if ($lng->dp_language && !$parsed) {
                    $languages_id = $lng->catalog_languages[$lng->dp_language]['id'];
                }
                $oldLanguage = (int)\Yii::$app->settings->get('languages_id');
                \Yii::$app->settings->set('languages_id', $languages_id);
                if (!\Yii::$app->user->isGuest && $oldLanguage !== (int)$languages_id) {
                    /** @var CustomersService $customersService */
                    $customersService = \Yii::createObject(CustomersService::class);
                    if ($customersService->changeLanguage(\Yii::$app->user->getIdentity() ,(int)$languages_id) !== true) {
                        \Yii::error('Customer language not changed');
                    }
                }
            }
            if ( $settings['seo_url_parts_currency'] ) {
                $currencyFoundAndHandled = false;
                if ( preg_match("/^([a-zA-Z]{3})(\/(.*))?$/", $seo_path, $found) ) {
                    $selected_currency_code = $found[1];
                    if (\common\helpers\Currencies::currency_exists($selected_currency_code)) {
                        if (in_array($selected_currency_code, $currencies->platform_currencies)) {
                            $currency_id = $currencies->currencies[$selected_currency_code]['id'];
                            $currency = $currencies->currencies[$selected_currency_code]['code'];
                            \Yii::$app->settings->set('currency_id', $currency_id);
                            \Yii::$app->settings->set('currency', $currency);
                            $currencyFoundAndHandled = true;
                        }
                        $seo_path = isset($found[3]) ? $found[3] : '';
                        $request->setPathInfo($seo_path);
                        $_params = $request->getQueryParams();
                        if (!is_array($_params)) $_params = [];
                        $_params['currency'] = $currency;
                        $request->setQueryParams($_params);
                        $urlWithParts['currency'] = 'currency';
                    }
                }
                if ( !$currencyFoundAndHandled && !$request->get('currency','') && isset($currencies->currencies[$currencies->dp_currency]) ) {
                    $currency_id = $currencies->currencies[$currencies->dp_currency]['id'];
                    $currency = $currencies->currencies[$currencies->dp_currency]['code'];
                    \Yii::$app->settings->set('currency_id', $currency_id);
                    \Yii::$app->settings->set('currency', $currency);
                }
            }
        }

        $language_map = \yii\helpers\ArrayHelper::map($lng->catalog_languages, 'id', 'code');
        if (isset($language_map[$languages_id])){
            if ( $lng->catalog_languages[$language_map[$languages_id]]['locale'] ) {
                \Yii::$app->language = str_replace('_', '-', $lng->catalog_languages[$language_map[$languages_id]]['locale']);
                \Yii::$app->settings->set('locale', $lng->catalog_languages[$language_map[$languages_id]]['locale']);
            }else{
                \Yii::$app->language = $language_map[$languages_id];
            }
        }
        $parsedRequest = parent::parseRequest($request);

        if ( ($parsedRequest[0]=='index/robots-txt' || preg_match('/^xmlsitemap\//',$parsedRequest[0])) && count($urlWithParts)>0 ) {
            \Yii::$app->urlManager->setOverrideSettings(['seo_url_parts_currency'=>false, 'seo_url_parts_language'=>false]);
            $robotsUrl = \Yii::$app->urlManager->createAbsoluteUrl([$parsedRequest[0]]+$parsedRequest[1]);
            \Yii::$app->urlManager->setOverrideSettings([]);
            throw new \yii\web\UrlNormalizerRedirectException($robotsUrl, 301);
        }

        if ( is_array($parsedRequest) && count($parsedRequest)>1 ) {
            $request->setQueryParams(array_merge($_GET, $request->getQueryParams(), $parsedRequest[1]));
            $_GET = $request->getQueryParams();
        }else{
            $request->setQueryParams(array_merge($_GET, $request->getQueryParams()));
            $_GET = $request->getQueryParams();
        }


        if ( $request->isGet && strpos($request->getUrl(),'/index')!==false && preg_match('#/index$#',$parsedRequest[0]) && empty($_GET)) {
            $realUrl = \Yii::$app->getUrlManager()->createUrl([$parsedRequest[0]] + $parsedRequest[1]);
            if ( empty($realUrl) ) $realUrl = '/';
            if ( $realUrl!=$request->getUrl() && strpos($realUrl,'/index')===false ) {
                throw new \yii\web\UrlNormalizerRedirectException($realUrl, 301);
            }
        }


        return $parsedRequest;
    }
    
    public function getSettings() {
       return array_merge([
           'seo_url_parts_language' => (SEO_URL_PARTS_LANGUAGE == 'True'? true : false),
           'seo_url_parts_currency' => (defined('SEO_URL_PARTS_CURRENCY') && SEO_URL_PARTS_CURRENCY == 'True'? true : false),
           'search_engine_friendly_urls' => (SEARCH_ENGINE_FRIENDLY_URLS == 'true'? true : false),
           'search_engine_unhide' => (SEARCH_ENGINE_UNHIDE == 'True' ? true : false),
           'seo_url_full_categories_path' => (SEO_URL_FULL_CATEGORIES_PATH == 'True'? true: false),
           'use_hraflang_metatag' => (USE_HREFLANG_METATAG == 'True'? true: false),
           'seo_url_parts_language_except' => [
               '#^xmlsitemap#',
           ],
           'seo_url_parts_currency_except' => [
               '#^xmlsitemap#',
           ],
       ], $this->overrideSettings);
    }

    public function createUrl($params) {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $currencies = \Yii::$container->get('currencies');

        $settings = $this->getSettings();
        if ($settings['search_engine_friendly_urls'] && $settings['search_engine_unhide']) {
            if (!is_array($params)) $params = [$params];

            $baseUrl = $this->getBaseUrl();
            $rebasePrefix = '';

            $replaceParamRegex = [];
            if ($settings['seo_url_parts_language'] && $this->_urlPartsPass('language', $params)) {
                if (!isset($params['language'])) $params['language'] = \common\classes\language::get_code($languages_id);

                if ($params['language'] != \common\classes\language::get_code(\common\classes\language::defaultId())) {
                    $rebasePrefix .= '/' . $params['language'];
                }else{
                    //unset($params['language']);
                }
                $replaceParamRegex[] = "language={$params['language']}";
            }

            if ($settings['seo_url_parts_currency'] && $this->_urlPartsPass('currency', $params)) {
                if (!isset($params['currency'])) $params['currency'] = \Yii::$app->settings->get('currency_id')?\common\helpers\Currencies::getCurrencyCode(\Yii::$app->settings->get('currency_id')):$currencies->dp_currency;

                $replaceParamRegex[] = "currency={$params['currency']}";
                if ($params['currency'] != $currencies->dp_currency) {
                    $rebasePrefix .= '/' . $params['currency'];
                }else{
                    unset($params['currency']);
                }
            }

            if ( $params[0]=='index/index' ) $params[0] = '/';

            $url = parent::createUrl($params);

            if ( $rebasePrefix ) {
                if (strpos($url, '://') !== false) {
                    // site rules return absolute url
                    if ($baseUrl !== '' && ($pos = strpos($url, $baseUrl, 8)) !== false) {
                        $url = $this->_removeUrlParams($url, $replaceParamRegex);
                        $url = substr($url, 0, $pos) . $baseUrl . $rebasePrefix . substr($url, $pos + strlen($baseUrl));
                    }
                } elseif (strpos($url, '//') === 0) {
                    // site rules return absolute url
                    if ($baseUrl !== '' && ($pos = strpos($url, '/', 2)) !== false) {
                        $url = $this->_removeUrlParams($url, $replaceParamRegex);
                        $url = substr($url, 0, $pos) . $baseUrl . $rebasePrefix . substr($url, $pos + strlen($baseUrl));
                    }
                } else {
                    if (!empty($baseUrl)) {
                        $url = $this->_removeUrlParams($url, $replaceParamRegex);
                        $url = substr($url, 0, strlen($baseUrl)) . $rebasePrefix . substr($url, strlen($baseUrl));
                    } else {
                        $url = $this->_removeUrlParams($url, $replaceParamRegex);
                        $url = $rebasePrefix . substr($url, strlen($baseUrl));
                    }
                }
            }elseif(!empty($replaceParamRegex)){
                $url = $this->_removeUrlParams($url, $replaceParamRegex);
            }
        }else{
            $url = parent::createUrl($params);
        }
        if ( $url==='/' /*rtrim($this->getBaseUrl(),'/')===rtrim($url, '/')*/ ) {
            $url = rtrim($url, '/');
        }
        return $url;
    }

    protected function _urlPartsPass($group, $routeParams)
    {
        $pass = true;

        $seoOptions = $this->getSettings();
        $checkRules = [];
        if ( isset( $seoOptions['seo_url_parts_'.$group.'_except'] ) && is_array($seoOptions['seo_url_parts_'.$group.'_except']) ) {
            $checkRules = $seoOptions['seo_url_parts_'.$group.'_except'];
        }
        foreach ( $checkRules as $checkRule ) {
            if ( substr($checkRule,0,1)=='#' ) {
                if (preg_match($checkRule, $routeParams[0])){
                    $pass = false;
                    break;
                }
            }
        }
        return $pass;
    }

    protected function _removeUrlParams($url, $removeParams)
    {
        if ( count($removeParams)>0 && strpos($url,'?')!==false ) {
            $cutIdx = strpos($url,'?');
            $params = preg_replace('/(&?'.implode('|&?',$removeParams).')/','', substr( $url, $cutIdx+1 ));
            $params = trim($params,'&');
            if ( $params!='' ) {
                $url = substr($url,0, $cutIdx+1).$params;
            }else{
                $url = substr($url,0, $cutIdx);
            }
        }
        return $url;
    }

}
