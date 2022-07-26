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
declare(strict_types=1);

namespace frontend\controllers;

use common\classes\Images;
use common\classes\platform;
use common\models\repositories\ProductsRepository;
use common\services\InventoryService;
use frontend\models\repositories\CatalogPagesReadRepository;
use frontend\models\repositories\InformationReadRepository;
use frontend\services\CatalogPagesService;
use Yii;
use yii\web\NotFoundHttpException;


class CatalogPagesController extends Sceleton
{
    /** @var InformationReadRepository  */
    private $informationReadRepository;
    /** @var int */
    private $platformId;
    /** @var int */
    private $languageId;
    /** @var CatalogPagesService */
    private $catalogPagesService;

    public function __construct(
        $id,
        $module = null,
        CatalogPagesService $catalogPagesService,
        InformationReadRepository $informationReadRepository,
        array $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->informationReadRepository = $informationReadRepository;
        $this->catalogPagesService = $catalogPagesService;
        $this->languageId = (int)\Yii::$app->settings->get('languages_id');
        $this->platformId = (int)platform::currentId();
    }

    public function actionIndex()
    {
        global $breadcrumb;
        $catalogPagesCatalog = $this->catalogPagesService->getCatalog(0, $this->languageId, $this->platformId, true);
        $imagePageCatalogPath = Images::getWSCatalogImagesPath() . $this->catalogPagesService->imagesLocation();
        $breadcrumb->add(TEXT_CATEGORY_PAGE, Yii::$app->urlManager->createUrl(['catalog-pages']));
        return $this->render('index.tpl', [
            'catalogPagesCatalog' => $catalogPagesCatalog,
            'imagePageCatalogPath' => $imagePageCatalogPath,
        ]);
    }

    public function actionPost($page)
    {
        global $breadcrumb;
        $page = tep_db_prepare_input($page);

        $catalogPage = $this->catalogPagesService->getInfoBySlug($page, $this->languageId, $this->platformId);
        if (empty($catalogPage)) {
            throw new NotFoundHttpException();
        }
        $catalogPagesCatalog = $this->catalogPagesService->getCatalog($catalogPage->catalog_pages_id, $this->languageId, $this->platformId, true);
        $InfoPageProvider = $this->informationReadRepository->getInformationProvider($catalogPage->catalog_pages_id, $this->languageId, $this->platformId, false, false, true);
        $breadcrumbs = $this->catalogPagesService->getBreadcrumbsById($catalogPage->catalog_pages_id, $this->languageId);
        if ($this->catalogPagesService->getUrlPrefix()) {
            $breadcrumb->add(TEXT_CATEGORY_PAGE, Yii::$app->urlManager->createUrl(['catalog-pages']));
        }
        foreach ($breadcrumbs as $bread) {
            $breadcrumb->add($bread['name'], Yii::$app->urlManager->createUrl(['catalog-pages/post', 'page' => $page]));
        }

        $imagePageCatalogPath = Images::getWSCatalogImagesPath() . $this->catalogPagesService->imagesLocation();
        $imageInformationPath = Images::getWSCatalogImagesPath() . $this->informationReadRepository->imagesLocation();
        return $this->render('post.tpl', [
            'catalogPagesCatalog' => $catalogPagesCatalog,
            'catalogPage' => $catalogPage,
            'infoPages' => $InfoPageProvider,
            'imageInformationPath' => $imageInformationPath,
            'imagePageCatalogPath' => $imagePageCatalogPath,
        ]);
    }
}

