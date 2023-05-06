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

namespace common\components;

use yii\base\BootstrapInterface;
use yii\base\Application;
use app\components\InitFactory;
use frontend\design\Info;

class SessionFlow implements BootstrapInterface {
  
    public function preload(){
        \Yii::$container->setSingleton('products', '\common\components\ProductsContainer');
        \Yii::$container->setSingleton('currencies', '\common\classes\Currencies');
        \Yii::$container->setSingleton('message_stack', '\common\classes\MessageStack');
    }
    
  public function bootstrap($app){
      $this->preload();
      
    global $session_started, $request_type, $cookie_path, $cookie_domain;


    $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
        if ( (defined('IS_IMAGE_CDN_SERVER') && IS_IMAGE_CDN_SERVER) || ( defined('IS_NESTED_IMAGE_HANDLER') )  ) {
            $app->defaultRoute = 'image/cached';
            if ( $app instanceof \yii\web\Application ) {
                $app->catchAll = ['image/cached'];
            }
        }
    });

    if ( defined('IS_IMAGE_CDN_SERVER') && IS_IMAGE_CDN_SERVER ) {
        // no cookie, no session - make image
        //return;
    }
    if ( defined('DIR_WS_CATALOG_IMAGES') ) {
        \Yii::setAlias('@webCatalogImages', DIR_WS_CATALOG_IMAGES);
    }else{
        \Yii::setAlias('@webCatalogImages', DIR_WS_CATALOG.DIR_WS_IMAGES);
    }

    // set the session ID if it exists
    if (isset($_POST[tep_session_name()])) {
      tep_session_id($_POST[tep_session_name()]);
    } elseif ( ($request_type == 'SSL' || in_array($_SERVER['HTTP_HOST'], ['127.0.0.1', 'localhost'])) && isset($_GET[tep_session_name()]) ) {
      // for development
      tep_session_id($_GET[tep_session_name()]);
    }

    /** @var \common\extensions\FrontendsSession\FrontendsSession $frontendsSession */
    if ($frontendsSession = \common\helpers\Acl::checkExtension('FrontendsSession', 'allowed')) {
      if ($frontendsSession::allowed() ) {
        /// workaround - Yii changes session ID after customer login
        if (!empty($_COOKIE[tep_session_name()]) && $_COOKIE[tep_session_name()] != tep_session_id() ) {
          //\common\helpers\System::setcookie(tep_session_name(), tep_session_id(), 0, $cookie_path, $cookie_domain);
        }
      }
    }

// start the session
  $session_started = false;
  if (\Yii::$app->id=='app-pos'){
	tep_session_start();
	$session_started = true;
  }elseif (\frontend\design\Info::isTotallyAdmin()){
	tep_session_start();
	$session_started = true;
  } else {
	if (SESSION_FORCE_COOKIE_USE == 'True') {
		\common\helpers\System::setcookie('cookie_test', 'please_accept_for_session', time()+60*60*24*30, $cookie_path, $cookie_domain);

		if (isset($_COOKIE['cookie_test'])) {
		  tep_session_start();
		  if (!tep_session_is_registered('referer_url')) { 
			$referer_url = @$_SERVER['HTTP_REFERER']; 
			if ($referer_url) { 
			  tep_session_register('referer_url'); 
			} 
		  }
		  $session_started = true;
		}
	  } elseif (SESSION_BLOCK_SPIDERS == 'True') {
		$user_agent = strtolower(getenv('HTTP_USER_AGENT'));
		$spider_flag = false;

		if (tep_not_null($user_agent) && !\frontend\design\Info::isTotallyAdmin()) {
		  $spiders = file(DIR_WS_INCLUDES . 'spiders.txt');

		  for ($i=0, $n=sizeof($spiders); $i<$n; $i++) {
			if (tep_not_null($spiders[$i])) {
			  if (is_integer(strpos($user_agent, trim($spiders[$i])))) {
				$spider_flag = true;
				break;
			  }
			}
		  }
		}

		if ($spider_flag == false) {
		  tep_session_start();
		  if (!tep_session_is_registered('referer_url')) { 
			$referer_url = $_SERVER['HTTP_REFERER'] ?? null;
			if ($referer_url) { 
			  tep_session_register('referer_url'); 
			} 
		  }
		  $session_started = true;
		}
	  } else {
		tep_session_start();
		if (!tep_session_is_registered('referer_url')) { 
		  $referer_url = $_SERVER['HTTP_REFERER'] ?? null;
		  if ($referer_url) { 
			tep_session_register('referer_url'); 
		  } 
		}
		$session_started = true;
	  }	  
  }
  
  
  global $PHP_SELF;
  
  if (basename($PHP_SELF) == 'index.php' && !\frontend\design\Info::isTotallyAdmin()){

    $pl_currs = Info::platformCurrencies();
    $currency = \Yii::$app->settings->get('currency');
    if (!Info::isAdmin() && $currency !== false && (is_array($pl_currs) && !in_array($currency, $pl_currs) || (!$pl_currs and $currency != DEFAULT_CURRENCY))){

      /// I don't know why session is destroyed instead of changing its data (currency, language) and clean up cart.....
      if ($frontendsSession && $frontendsSession::allowed() ) {
        if (!$pl_currs || !in_array($currency, $pl_currs)) {
          // session currency is not available for frontend
          \Yii::$app->settings->set('currency', DEFAULT_CURRENCY);
        }
        if (!$session_started) {
          tep_session_start();
        }

      } else {
        tep_session_start(true);// be sure currency from payment is in platform currencies list
      }
    }
    $pl_langs = Info::platformLanguages();
    $languages_id = (int)\Yii::$app->settings->get('languages_id');
    $_code = \common\helpers\Language::get_language_code($languages_id);
    if (!Info::isAdmin() && $languages_id > 0 && (is_array($pl_langs) && !in_array($_code['code'], $pl_langs) || (!$pl_langs and !in_array($languages_id,  \common\helpers\Language::get_language_id(strtolower(DEFAULT_LANGUAGE)))))){

      /// I don't know why session is destroyed instead of changing its data (currency, language) and clean up cart.....
      if ($frontendsSession && $frontendsSession::allowed() ) {
        if (!$pl_langs || !!in_array($_code['code'], $pl_langs)) {
          // session language is not available for frontend
          \Yii::$app->settings->set('languages_id', \common\helpers\Language::get_language_id(strtolower(DEFAULT_LANGUAGE)));
        }
        if (!$session_started) {
          tep_session_start();
        }

      } else {

        tep_session_start(true);
      }
    }

  }
  
  if (\Yii::$app->id=='app-pos'){
	  \pos\components\AdminFactory::init();  
  } elseif (\frontend\design\Info::isTotallyAdmin()) {
	  \backend\components\AdminFactory::init();  
  } else if (\Yii::$app->id=='app-frontend'){
	  InitFactory::init();  
  }
	  
  }
}