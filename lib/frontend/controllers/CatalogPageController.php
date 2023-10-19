<?php
namespace frontend\controllers;

use common\classes\Images;
use common\classes\platform;
use frontend\models\repositories\CatalogPagesReadRepository;
use Yii;
use yii\web\NotFoundHttpException;


class CatalogPageController extends Sceleton
{
	/**
	 * @var CatalogPagesReadRepository
	 */
	private $catalogPagesRepository;
	private $platformId;
	private $languageId;

	public function __construct( $id, $module = null, CatalogPagesReadRepository $catalogPagesRepository, array $config = [] ) {
		global $languages_id;

		parent::__construct( $id, $module, $config );
		$this->catalogPagesRepository = $catalogPagesRepository;
		$this->languageId = (int)$languages_id;
		$this->platformId = (int)platform::activeId();

	}

    public function actionIndex($page='')
    {
    	$page = tep_db_prepare_input($page);
	    $breadcumbs = '';
    	if($page === ''){
		    $catalogPage = null;
		    $catalogPagesCatalog = $this->catalogPagesRepository->getCatalog(0,$this->languageId,$this->platformId);
	    }else{
		    $catalogPage = $this->catalogPagesRepository->getFullInfoBySlug($page,$this->languageId,$this->platformId);
		    if(empty($catalogPage)){
			    throw new NotFoundHttpException();
		    }
		    $catalogPagesCatalog = $this->catalogPagesRepository->getCatalog($catalogPage->catalog_pages_id,$this->languageId,$this->platformId);
		    $breadcumbs = $this->catalogPagesRepository->getBreadcrumbsById($catalogPage->catalog_pages_id,$this->languageId);
	    }
	    $imagePageCatalogPath = Images::getWSCatalogImagesPath().$this->catalogPagesRepository->imagesLocation();
    	return $this->render('index.tpl',[
    		'catalogPagesCatalog' => $catalogPagesCatalog,
			'catalogPage' => $catalogPage,
		    'breadcumbs' => $breadcumbs,
		    'imagePageCatalogPath' => $imagePageCatalogPath,
	    ]);
    }

}

