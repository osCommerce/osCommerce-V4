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

namespace backend\controllers;

use backend\models\forms\catalogpages\AssignInformationForm;
use backend\models\forms\catalogpages\CatalogsPageContainerForm;
use backend\services\CatalogPagesService;
use common\models\repositories\InformationRepository;
use common\models\repositories\LanguagesRepository;
use common\models\repositories\NotFoundException;
use Yii;
use common\classes\platform;
use yii\base\DynamicModel;
use \yii\web\Response;

class CatalogPagesController extends Sceleton
{
    public $acl = ['BOX_HEADING_DESIGN_CONTROLS', 'BOX_HEADING_CATALOG_PAGES'];

    /** @var CatalogPagesService */
    private $catalogPagesService;
    /** @var LanguagesRepository */
    private $languagesRepository;
    /** @var InformationRepository */
    private $informationRepository;

    public function __construct(
        $id,
        $module,
        CatalogPagesService $catalogPagesService,
        LanguagesRepository $languagesRepository,
        InformationRepository $informationRepository,
        array $config = []
    )
    {
        parent::__construct($id, $module, $config);
        \common\helpers\Translation::init('admin/main');
	    \common\helpers\Translation::init('admin/catalog-pages');
        $this->catalogPagesService = $catalogPagesService;
	    $this->languagesRepository = $languagesRepository;
	    $this->informationRepository = $informationRepository;
        $this->selectedMenu = ['design_controls', 'catalog-pages'];
        $this->navigation[] = ['link' => Yii::$app->urlManager->createUrl('catalog-pages/index'), 'title' => BOX_HEADING_CATALOG_PAGES];
        $this->topButtons[] = '<a href="#" id="createPage" class="btn btn-primary addprbtn"><i class="icon-tag"></i>' . TEXT_CREATE_NEW_CATALOG_PAGE . '</a>';
    }

    public function actionIndex()
    {

        $this->view->headingTitle = BOX_HEADING_CATALOG_PAGES;
        $this->view->groupsTable = [
            ['title' => TABLE_TEXT_NAME, 'not_important' => 1],
            ['title' => TABLE_HEADING_STATUS, 'not_important' => 1],
        ];
	    /*var_dump($this->catalogPagesService->getNestedCatalogPagesById(1, 1));
	    die("\n");/**/
        $platformId = (int)Yii::$app->request->get('platform_id', 0);
        if ($platformId < 1) {
            $platformId = (int)platform::defaultId();
        }
        $row = (int)Yii::$app->request->get('row', 0);
        $id = (int)Yii::$app->request->get('item_id', 0);
        $parentId = (int)Yii::$app->request->get('parent_id', 0);
        $platforms = platform::getList(false);

        return $this->render('index.tpl', [
            'platforms' => $platforms,
            'defaultPlatformId' => platform::defaultId(),
            'isMultiPlatforms' => platform::isMulti(),
            'platformId' => $platformId,
            'id' => $id,
            'parentId' => $parentId,
            'row' => $row,
        ]);
    }

    public function actionList()
    {
        $languages_id = (int)\Yii::$app->settings->get('languages_id');

        $draw = (int)Yii::$app->request->get('draw', 1);
        $start = (int)Yii::$app->request->get('start', 0);
        $length = (int)Yii::$app->request->get('length', 10);
        $platformId = (int)Yii::$app->request->get('platform_id', 0);
        $parentId = (int)Yii::$app->request->get('parent_id', 0);

        if ($length === -1) {
            $length = 10000;
        }

        if ($platformId < 1) {
            $platformId = (int)platform::defaultId();
        }

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $formData);
        if (isset($formData['parent_id'])) {
            $parentId = (int)$formData['parent_id'];
        }
        $id = (int)$formData['item_id'];

        $keyword = null;
        $search = Yii::$app->request->get('search', []);
        if (!empty($search) && is_array($search)) {
            $keyword = tep_db_prepare_input($search['value']);
        }
        unset($search);
        $sort = null;
        $order = Yii::$app->request->get('order', []);
        if (isset($order[0]) && !empty($order[0])) {
            $sort = 'catalog_pages_description.name ' . $order[0]['dir'];
        }
        unset($order);
        $categoryPagesQuery = $this->catalogPagesService->getQueryByParams($platformId, $id, $languages_id, $keyword, $sort);
        $categoryPagesQueryCount = clone $categoryPagesQuery;
        $categoryPages = $categoryPagesQuery->offset($start)->limit($length)->asArray()->all();
        $total = $categoryPagesQueryCount->count();
        unset($categoryPagesQueryCount, $categoryPagesQuery);
        $response = [];

        if ($parentId > 0 || ($parentId === 0 && $id > 0)) {
            $realParentId = 0;
            if ($parentId > 0) {
                $realParent = $this->catalogPagesService->getById($parentId);
                $realParentId = $realParent->parent_id;
            }
            $response[] = [
                $this->renderPartial('./list/parent-element.tpl', [
                    'id' => $parentId,
                    'parentId' => $realParentId,
                ]),
                '',
            ];
        }
        foreach ($categoryPages as $page) {
            $element = $this->renderPartial('./list/element.tpl', ['page' => $page]);
            $status = $this->renderPartial('./list/status.tpl', ['page' => $page]);
            $response[] = [$element, $status];
        }
        $breadcrumbs = $this->catalogPagesService->getBreadcrumbsById($id, $languages_id);

        unset($categoryPages);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'breadcrumbs' => $this->renderPartial('./list/breadcrumbs.tpl', [
                'breadcrumbs' => $breadcrumbs,
            ]),
            'data' => $response,
        ];
    }

    public function actionChangeStatus()
    {
        $id = (int)Yii::$app->request->post('id', 0);
        $status = Yii::$app->request->post('status', 'false');
        if ($id < 1) {
            throw new NotFoundException('Catalog Pages not find.');
        }
        $catalogPage = $this->catalogPagesService->getById($id);
        if ($status === 'true') {
            $this->catalogPagesService->setActive($catalogPage);
        } else {
            $this->catalogPagesService->setDisable($catalogPage);
        }
    }

    public function actionView()
    {
        $languages_id = (int)\Yii::$app->settings->get('languages_id');

        $id = (int)Yii::$app->request->post('id');
        if ($id < 1) {
            return '';
        }
        $catalogPage = $this->catalogPagesService->getShortInfo($id, $languages_id, true);
        return $this->renderAjax('view.tpl', [
            'catalogPage' => $catalogPage,
        ]);

    }

    public function actionDelete()
    {
        $languages_id = (int)\Yii::$app->settings->get('languages_id');

        $id = (int)Yii::$app->request->post('id');
        if ($id < 1) {
            throw new NotFoundException('Catalog Pages not find.');
        }
        $catalogPage = $this->catalogPagesService->getShortInfo($id, $languages_id, true);
        $confirm = Yii::$app->request->post('confirm');
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($confirm === 'false') {
            return [
                'reload' => false,
                'data' => $this->renderAjax('confirm.tpl', [
                    'catalogPage' => $catalogPage,
                ]),
            ];
        }
        $this->catalogPagesService->deleteByEntity($catalogPage);
        return ['reload' => true];
    }

    public function actionSort()
    {
        $languages_id = (int)\Yii::$app->settings->get('languages_id');
        $sort = Yii::$app->request->post('folder', []);
        $platformId = (int)Yii::$app->request->post('platform_id', 0);
        $parentId = (int)Yii::$app->request->post('parent_id', 0);
        $model = DynamicModel::validateData(['sort' => $sort, 'parentId' => $parentId, 'platformId' => $platformId,],
            [
                ['sort', 'each', 'rule' => ['integer']],
                ['platformId', 'integer', 'min' => 1],
                ['parentId', 'integer']
            ]);
        if ($model->hasErrors()) {
            throw new \RuntimeException('Sort Error Data.');
        }
        $categoryPagesQuery = $this->catalogPagesService->getQueryByParams($platformId, $parentId, $languages_id);
        $this->catalogPagesService->sortBySourceQuery($categoryPagesQuery, $sort);
    }

    public function actionEdit( $id = 0, $platform_id = 0, $parent_id=0)
    {
    	$languages_id = (int)\Yii::$app->settings->get('languages_id');
        $id = (int)$id;
        $platform_id = (int)$platform_id;
        $parent_id = (int)$parent_id;

        if ($platform_id < 1) {
            throw new NotFoundException('Platform not find.');
        }

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#frm\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        if ($id < 1) {
            if ($parent_id < 1) {
                $this->view->headingTitle = TITLE_CREATE_NEW_CATALOG_PAGE . ' (<b>' . TEXT_TOP . '</b>)';
            } else {
                $parent = $this->catalogPagesService->getShortInfo($parent_id, $languages_id);
                $this->view->headingTitle = TITLE_CREATE_NEW_CATALOG_PAGE . ' (' . PARENT_TEXT . ': <b>' . $parent->descriptionLanguageId->name . ')</b>';
            }

        } else {
            $current = $this->catalogPagesService->getShortInfo($id, $languages_id);
            $this->view->headingTitle = TITLE_CREATE_EDIT_CATALOG_PAGE . ': <b>' . $current->descriptionLanguageId->name . '</b>';
        }

        $catalogPageForm = new CatalogsPageContainerForm($id, $platform_id, $parent_id, $languages_id, $this->languagesRepository, $this->catalogPagesService);

        if (Yii::$app->request->isPost) {
            if ($catalogPageForm->load(Yii::$app->request->post()) && $catalogPageForm->validate()) {
                $id = $this->catalogPagesService->saveByCatalogPageForm($id, $catalogPageForm, $languages_id);
                return $this->redirect(Yii::$app->urlManager->createUrl(['catalog-pages/edit', 'id' => $id, 'platform_id' => $platform_id]));
            } else {
                /*
                var_dump($catalogPageForm->getErrorData());
                die("\n");/**/
            }

        }
        $languages = $this->languagesRepository->getAll();
        $breadcrumbs = $this->catalogPagesService->getBreadcrumbsById($id, $languages_id);
        $parentParentId = 0;
        if ($parent_id) {
            try {
                $parentCatalogPage = $this->catalogPagesService->getById($parent_id);
                $parentParentId = $parentCatalogPage->parent_id;
            } catch (\Exception $ex) {}
        }
        return $this->render('edit.tpl', [
            'platform_id' => $platform_id,
            'parent_id' => $parent_id,
            'parent_parent_id' => $parentParentId,
            'breadcrumbs' => $this->renderPartial('./list/breadcrumbs.tpl', [
                'breadcrumbs' => $breadcrumbs,
            ]),
            'catalogPageForm' => $catalogPageForm,
            'dirImages' => Yii::getAlias('@webCatalogImages/' . $this->catalogPagesService->imagesLocation()),
            'default_language' => DEFAULT_LANGUAGE,//\common\classes\language::defaultId(),
            'languages' => $languages,
        ]);

    }
    public function actionGetInfoPages()
    {
        global $login_id;
        $languages_id = (int)\Yii::$app->settings->get('languages_id');
        $login_id = (int)$login_id;
        $platformId = (int)Yii::$app->request->post('platform_id', 0);
        $term = Yii::$app->request->post('term', '');
        if ($platformId < 1) {
            throw new NotFoundException('Platform not find.');
        }
        $information = $this->informationRepository->getAllByPlatformAndLanguageInAdmin($platformId, $languages_id, true, false, true, $login_id);
        return $this->renderAjax('./edit/generate-info-select.tpl', [
            'information' => $information,
        ]);
    }
    public function actionSetInfoPages()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $informationId = (int)Yii::$app->request->post('information_id', 0);
        $platformId = (int)Yii::$app->request->post('platform_id', 0);
        if ($platformId < 1 || $informationId < 1) {
            throw new NotFoundException('Platform or Info Page not find.');
        }
        $information = $this->informationRepository->get($informationId, $platformId, $languages_id);
        if (empty($information)) {
            throw new NotFoundException('Info Page not find.');
        }
        $assignInformationForm = new AssignInformationForm([
            'page_title' => $information->page_title,
            'information_id' => $information->information_id,
            'hide' => $information->hide,
        ]);
        return $this->renderAjax('./edit/set-select-info.tpl', [
            'assignInformationForm' => $assignInformationForm,
        ]);
    }
}
