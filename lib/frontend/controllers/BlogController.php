<?php
namespace frontend\controllers;

use Yii;

/**
 * Site controller
 */
class BlogController extends Sceleton
{
	public function actionIndex()
	{
        global $Blog;
        if (!\frontend\design\Info::hasBlog() || !is_object($Blog)){
            return '';
        }

		$this->view->wp_head = $Blog->head();

        // {{ blog canonical - ONLY for base language - BLOG SINGLE LANGUAGE
        \Yii::$app->urlManager->setOverrideSettings(['seo_url_parts_currency'=>false, /*{{ comment this if blog multilingual */'seo_url_parts_language'=>false/* }}*/]);
        $routeParams = ['blog/index']+$_GET;
        if ( isset($routeParams['currency']) ) unset($routeParams['currency']);
        \app\components\MetaCannonical::instance()->setCannonical(\Yii::$app->urlManager->createAbsoluteUrl($routeParams));
        \Yii::$app->urlManager->setOverrideSettings([]);
        // and remove link alternate - only one base language for blog
        $alternateLanguages = array_filter(array_keys($this->getView()->linkTags), function ($key){ return strpos($key,'alternate_lang_')===0; });
        foreach( $alternateLanguages as $alternateLanguage ) {
            unset($this->getView()->linkTags[$alternateLanguage]);
        }
        // }} blog canonical - ONLY for base language - BLOG SINGLE LANGUAGE

		if ( preg_match('/\s+rel=[\'"]canonical[\'"]/i', $this->view->wp_head) ) {
		    // use shop canonical always
            $this->view->wp_head = preg_replace('#<link[^>]*rel="canonical"[^>]*>#','',$this->view->wp_head);
        }
        // {{ use WP meta tags
        $registeredMetaKeys = array_filter(array_keys($this->getView()->metaTags), function ($key){ return !is_numeric($key); });
		foreach ($registeredMetaKeys as $registeredMetaKey) {
		    $isPresentInWP = preg_match('#<meta[^>]*name="'.preg_quote($registeredMetaKey,'#').'"#i',$this->view->wp_head);
		    if ( $isPresentInWP ) {
                unset($this->getView()->metaTags[$registeredMetaKey]);
            }
        }
        // }} use WP meta tags

		$this->view->wp_footer = $Blog->footer();

		\frontend\design\Info::addBlockToWidgetsList('catalog-paging');

		return $this->render('index.tpl', [
			'page_name' => 'blog'
		]);
	}

    public function beforeAction($action)
    {
        global $Blog;
        if (!\frontend\design\Info::hasBlog() || !is_object($Blog)){
            throw new \yii\web\NotFoundHttpException();
        }

        return parent::beforeAction($action);
    }

}
