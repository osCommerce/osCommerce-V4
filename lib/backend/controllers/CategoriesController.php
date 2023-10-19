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

use backend\components\SourcesSearchTrait;
use backend\models\ProductEdit\ViewAttributes;
use backend\models\ProductEdit\ViewImportExport;
use backend\models\ProductEdit\ViewPriceData;
use backend\models\ProductEdit\ViewStockInfo;
use backend\models\ProductNameDecorator;
use common\classes\Images;
use common\helpers\Html;
use common\helpers\Seo;
use common\helpers\Categories;
use common\models\CategoriesImages;
use common\models\ImageTypes;
use common\models\Product\ProductsNotes;
use common\services\ProductsDocumentsService;
use common\services\ProductsNotesService;
use Yii;
use yii\db\Expression;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use backend\models\EP\Messages;
use common\models\SuppliersProducts;
use common\models\Suppliers;
use backend\design\Uploads;
use common\helpers\Manufacturers;

/**
 * default controller to handle user requests.
 */
class CategoriesController extends Sceleton {

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_CATEGORIES_PRODUCTS'];
    public $defaultCollapsed = false;
    /**
     * @var \backend\models\ProductEdit\TabAccess
     */
    public $ProductEditTabAccess;
    /** @var ProductsNotesService */
    private $productsNotesService;
    /** @var ProductsDocumentsService */
    private $productsDocumentsService;

    use SourcesSearchTrait;

    public function __construct(
        $id,
        $module = null,
        ProductsNotesService $productsNotesService = null,
        ProductsDocumentsService $productsDocumentsService = null,
        array $config = []
    )
    {
        parent::__construct($id, $module, $config);
        $this->productsNotesService = $productsNotesService;
        $this->productsDocumentsService = $productsDocumentsService;
    }


    public function init()
    {
        parent::init();
        $this->ProductEditTabAccess = new \backend\models\ProductEdit\TabAccess();
    }


    private function getCategoryTree($parent_id = '0', $platform_id = false) {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $filter_by_platform = array();
        if (is_array($platform_id)) {
            $filter_by_platform = $platform_id;
        } else {
            if (!$platform_param = Yii::$app->request->get('platform', false)) {
                $formFilter = Yii::$app->request->get('filter', '');
                $output = [];
                parse_str($formFilter, $output);
                if (isset($output['platform']) && is_array($output['platform'])) {
                    $platform_param = $output['platform'];
                }
            }

            if (isset($platform_param) && is_array($platform_param)) {
                foreach ($platform_param as $_platform_id) {
                    if ((int) $_platform_id > 0) {
                      $filter_by_platform[] = (int) $_platform_id;
                    }
                }
            }
        }

        $platform_filter_categories = '';
        if (count($filter_by_platform) > 0) {
            $platform_filter_categories .= ' and c.categories_id IN (SELECT categories_id FROM ' . TABLE_PLATFORMS_CATEGORIES . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
        }

        $filter_by_departments = array();
        $department_param = Yii::$app->request->get('departments', false);
        if (is_array($department_param)) {
            foreach( $department_param as $_department_id ) if ( (int)$_department_id>0 ) $filter_by_departments[] = (int)$_department_id;
        }
        if (count($filter_by_departments) > 0) {
            $platform_filter_categories .= ' and c.categories_id IN (SELECT categories_id FROM ' . TABLE_DEPARTMENTS_CATEGORIES . ' WHERE departments_id IN(\'' . implode("','", $filter_by_departments) . '\'))  ';
        }

        $categories_query = tep_db_query("select c.categories_level, c.categories_id as id, cd.categories_name as text, c.parent_id, c.categories_status from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "' and c1.parent_id = '" . (int) $parent_id . "' and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right) and affiliate_id = 0 {$platform_filter_categories} order by c.categories_left, c.sort_order, cd.categories_name");

        $categories_by_level = [];
        while ($categories = tep_db_fetch_array($categories_query)) {
          $categories['child'] = array();
          $categories_by_level[$categories['categories_level']][$categories['id']] = $categories;
        }

        $categoriesTree = self::buildTree($categories_by_level);
        return $categoriesTree;
    }

    //transform plain array to tree
    private static function buildTree(array &$categories_by_level) {
      $categoriesTree = [];
      if (count($categories_by_level)) {
        $levels = array_keys($categories_by_level);
        $topLevel = min($levels);
        for ($level = max($levels); $level >= $topLevel; $level--) {
          foreach ($categories_by_level[$level] as $id => $cat_info) {
            if ($level == $topLevel) {
              $categoriesTree[] = $cat_info;
            } else {
              $to_parent_id = $cat_info['parent_id'];
              $categories_by_level[$level - 1][$to_parent_id]['child'][] = $cat_info;
            }
          }
        }
      }
      return $categoriesTree;
    }

    private function getBrandsList($platform_id = false) {
        $brandsList = [];

        $filter_by_platform = array();
        if (is_array($platform_id)) {
            $filter_by_platform = $platform_id;
        } else {
            if (!$platform_param = Yii::$app->request->get('platform', false)) {
                $formFilter = Yii::$app->request->get('filter', '');
                $output = [];
                parse_str($formFilter, $output);
                if (isset($output['platform']) && is_array($output['platform'])) {
                    $platform_param = $output['platform'];
                }
            }

            if (isset($platform_param) && is_array($platform_param)) {
                foreach ($platform_param as $_platform_id) {
                    if ((int) $_platform_id > 0) {
                        $filter_by_platform[] = (int) $_platform_id;
                    }
                }
            }
        }

        $platform_filter_products = '';
//         if ( count($filter_by_platform)>0 ) {
//             $platform_filter_products .= ' and m.manufacturers_id IN (SELECT distinct p.manufacturers_id FROM '.TABLE_PRODUCTS.' p inner join '.TABLE_PLATFORMS_PRODUCTS.' pp WHERE pp.products_id=p.products_id and pp.platform_id IN(\''.implode("','",$filter_by_platform).'\'))  ';
//         }
        if (count($filter_by_platform) > 0) {
            $platform_filter_products .= ' inner join ' . TABLE_PRODUCTS . ' p on m.manufacturers_id = p.manufacturers_id inner join ' . TABLE_PLATFORMS_PRODUCTS . ' pp on pp.products_id=p.products_id and pp.platform_id IN(\'' . implode("','", $filter_by_platform) . '\')  ';
        }

        $manufacturers_query_raw = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, m.date_added, m.last_modified from " . TABLE_MANUFACTURERS . " m {$platform_filter_products} where 1  group by m.manufacturers_id order by m.sort_order, m.manufacturers_name";

        $manufacturers_query = tep_db_query($manufacturers_query_raw);
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $brandsList[] = [
                'id' => $manufacturers['manufacturers_id'],
                'text' => $manufacturers['manufacturers_name'],
            ];
        }
        return $brandsList;
    }

    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        global $login_id;

        $this->selectedMenu = array('catalog', 'categories');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('categories/index'), 'title' => HEADING_TITLE);
        if (true === \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT'])) {
            if (\common\helpers\Acl::checkExtensionAllowed('ProductBundles')) {
                $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['categories/productedit', 'bundle' => '1']) . '" class="js_create_new_product btn btn-primary addprbtn create_bundle" title="Create bundle"><i class="icon-cubes"></i>' . TEXT_CREATE_NEW_BUNDLE . '</a>';
            }
            $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('categories/productedit') . '" class="js_create_new_product btn btn-primary addprbtn create_product" title="Create product"><i class="icon-cubes"></i>' . TEXT_CREATE_NEW_PRODUCT . '</a>';
        }
        if (true === \common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_EDIT'])) {
            $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('categories/categoryedit') . '" class="js_create_new_category btn btn-primary addprbtn create_category" title="Create category"><i class="icon-folder-close-alt"></i>' . TEXT_CREATE_NEW_CATEGORY . '</a>';
        }
        if (true === \common\helpers\Acl::rule(['TEXT_LABEL_BRAND', 'IMAGE_EDIT'])) {
            $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('categories/brandedit') . '" class="btn btn-primary addprbtn create_brand" title="Create brand"><i class="icon-tag"></i>' . TEXT_CREATE_NEW_BRANDS . '</a>';
        }
        $demoProductsCounter = \common\models\Products::find()->where(['is_demo' => 1])->count();
        if ($demoProductsCounter > 0) {
            $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['categories/demo-cleanup']) . '" onclick="return confirm(\'' . TEXT_DEMO_PRODUCT_CLEAN_NOTICE . '\');" class="btn btn-primary remove_product" title="Remove demo products"><i></i>' . TEXT_DEMO_PRODUCT_CLEAN . '</a>';
        }
        $this->view->headingTitle = HEADING_TITLE;
        $this->view->catalogTable = array(
            array(
                'title' => '<div class="checker"><input class="uniform js-cat-batch js-cat-batch-master" type="checkbox"></div>',
                'not_important' => 2,
            ),
            array(
                'title' => TABLE_HEADING_CATEGORIES_PRODUCTS,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 0
            ),
                /* array(
                  'title' => TABLE_HEADING_ACTION,
                  'not_important' => 0
                  ), */
        );

        $filter_by_platform = false;
        if (false === \common\helpers\Acl::rule(['SUPERUSER'])) {
            $filter_by_platform = [];
            $platforms = \common\models\AdminPlatforms::find()->where(['admin_id' => $login_id])->asArray()->all();
            foreach ($platforms as $platform) {
                $filter_by_platform[] = $platform['platform_id'];
            }
            //$filter_by_platform[] = 0;
        }
        $this->view->categoriesTree = $this->getCategoryTree('0', $filter_by_platform);

        $this->view->brandsList = $this->getBrandsList();

        $this->view->filters = new \stdClass();

        $by = [
            [
                'name' => TEXT_ANY,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_NAME,
                'value' => 'name',
                'selected' => '',
            ],
            [
                'name' => TEXT_IN_DESCRIPTION,
                'value' => 'description',
                'selected' => '',
            ],
            [
                'name' => TEXT_CATEGORY_NAME,
                'value' => 'cname',
                'selected' => '',
            ],
            [
                'name' => TEXT_IN_CATEGORY_DESCRIPTION,
                'value' => 'cdescription',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_PAGE_TITLE,
                'value' => 'title',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_HEADER_DESC,
                'value' => 'header',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_KEYWORDS,
                'value' => 'keywords',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_MODEL,
                'value' => 'model',
                'selected' => '',
            ],
            [
                'name' => rtrim(TEXT_UPC,' :'),
                'value' => 'upc',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_EAN,
                'value' => 'ean',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_ASIN,
                'value' => 'asin',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_ISBN,
                'value' => 'isbn',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_FILE,
                'value' => 'file',
                'selected' => '',
            ],
            [
                'name' => TEXT_IMAGE_NAME,
                'value' => 'image',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_SEO_NAME,
                'value' => 'seo',
                'selected' => '',
            ],
        ];

        foreach ($by as $key => $value) {
            if (isset($_GET['by']) && $value['value'] == $_GET['by']) {
                $by[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->by = $by;

        $search = '';
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }
        $this->view->filters->search = $search;

        $autoEdit = Yii::$app->request->get('autoEdit', 0);
        if ($autoEdit && !empty($search)) {
          $p = \common\models\Products::find()->alias('p')->joinWith('productsDescriptions pd')
              ->select(new \yii\db\Expression( 'distinct p.products_id'))
              ->andWhere(['or',
                  ['like', 'pd.products_name', ($search)],
                  ['like', 'pd.products_seo_page_name', ($search)],
                  ['like', 'p.products_model', ($search)],
                  ['like', 'p.products_ean', ($search)],
                ]);
          if ($p->count('distinct p.products_id')==1) {
            $this->redirect(\Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $p->scalar()]));
          }
//2do check " for extra escapeing  echo $p->createCommand()->rawSql; die;
        }

        $brand = '';
        if (isset($_GET['brand'])) {
            $brand = $_GET['brand'];
        }
        $this->view->filters->brand = $brand;

        $supplier = '';
        if (isset($_GET['supplier'])) {
            $supplier = $_GET['supplier'];
        }
        $this->view->filters->supplier = $supplier;

        $source = '';
        if (isset($_GET['source'])) {
            $source = $_GET['source'];
        }
        $this->view->filters->source = $source;

        $stock = [
            [
                'name' => TEXT_ALL,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_AVAILABLE,
                'value' => 'y',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_NOT_AVAILABLE,
                'value' => 'n',
                'selected' => '',
            ],
        ];
        foreach ($stock as $key => $value) {
            if (isset($_GET['stock']) && $value['value'] == $_GET['stock']) {
                $stock[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->stock = $stock;

        $status = [
            [
                'name' => TEXT_ALL,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_ACTIVE,
                'value' => 'y',
                'selected' => '',
            ],
            [
                'name' => TEXT_INACTIVE,
                'value' => 'n',
                'selected' => '',
            ],
        ];
        $gstatus = \Yii::$app->request->get('status', '');
        if (!empty($gstatus)) {
            foreach ($status as $key => $value) {
                if ($value['value'] == $gstatus) {
                    $status[$key]['selected'] = 'selected';
                }
            }
        }
        $this->view->filters->status = $status;

        $price_from = '';
        if (isset($_GET['price_from'])) {
            $price_from = $_GET['price_from'];
        }
        $this->view->filters->price_from = $price_from;

        $price_to = '';
        if (isset($_GET['price_to'])) {
            $price_to = $_GET['price_to'];
        }
        $this->view->filters->price_to = $price_to;

        if (isset($_GET['weight_value']) && $_GET['weight_value'] == 'lbs') {
            $this->view->filters->weight_kg = false;
            $this->view->filters->weight_lbs = true;
        } else {
            $this->view->filters->weight_kg = true;
            $this->view->filters->weight_lbs = false;
        }

        $weight_from = '';
        if (isset($_GET['weight_from'])) {
            $weight_from = $_GET['weight_from'];
        }
        $this->view->filters->weight_from = $weight_from;

        $weight_to = '';
        if (isset($_GET['weight_to'])) {
            $weight_to = $_GET['weight_to'];
        }
        $this->view->filters->weight_to = $weight_to;

        $this->view->filters->prod_attr = (int)Yii::$app->request->get('prod_attr', 0);

        $this->view->filters->low_stock = (int)Yii::$app->request->get('low_stock', 0);
        $this->view->filters->featured = (int)Yii::$app->request->get('featured', 0);
        $this->view->filters->gift = (int)Yii::$app->request->get('gift', 0);
        $this->view->filters->virtual = (int)Yii::$app->request->get('virtual', 0);
        $this->view->filters->all_bundles = (int)Yii::$app->request->get('all_bundles', 0);
        $this->view->filters->type_listing = (int)Yii::$app->request->get('type_listing', 0);
        $this->view->filters->type_not_listing = (int)Yii::$app->request->get('type_not_listing', 0);
        $this->view->filters->sub_children = (int)Yii::$app->request->get('sub_children', 0);
        $this->view->filters->sale = (int)Yii::$app->request->get('sale', 0);
        $this->view->filters->wo_images = (int)Yii::$app->request->get('wo_images', 0);

        $this->view->filters->platform = array();
        if (isset($_GET['platform']) && is_array($_GET['platform'])) {
            foreach ($_GET['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $this->view->filters->platform[] = (int) $_platform_id;
        }

        $this->view->filters->row = (int)Yii::$app->request->get('row', 0);

        $listing_type = 'category';
        if (isset($_GET['listing_type'])) {
            $listing_type = $_GET['listing_type'];
        }
        $this->view->filters->listing_type = $listing_type;
        $this->view->filters->category_id = (int)Yii::$app->request->get('category_id', 0);
        $this->view->filters->brand_id = (int)Yii::$app->request->get('brand_id', 0);

        $this->view->categoriesOpenedTree = \common\helpers\Categories::getCategoryParentsIds($this->view->filters->category_id);
        $this->view->categoriesClosedTree = array_diff(array_map('intval', explode('|', \Yii::$app->session->get('closed_data'))), $this->view->categoriesOpenedTree);

        if (is_dir(DIR_FS_CATALOG_IMAGES)) {
            if (!is_writeable(DIR_FS_CATALOG_IMAGES)) {
                $this->view->errorMessage = sprintf(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, DIR_FS_CATALOG_IMAGES);
                $this->view->errorMessageType = 'danger';
            }
        } else {
            $this->view->errorMessage = sprintf(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, DIR_FS_CATALOG_IMAGES);
            $this->view->errorMessageType = 'danger';
        }

        $departments = false;
        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $this->view->filters->departments = [];
            if ( isset($_GET['departments']) && is_array($_GET['departments']) ){
                foreach( $_GET['departments'] as $_department_id ) if ( (int)$_department_id>0 ) $this->view->filters->departments[] = (int)$_department_id;
            }
            $departments = \common\classes\department::getList(false);
        }

        return $this->render('index', [
            'platforms' => \common\classes\platform::getList(),
            'isMultiPlatforms' => \common\classes\platform::isMulti(),
            'collapsed' => $this->defaultCollapsed,
            'departments' => $departments,
        ]);
    }

    public function actionList() {
        \common\helpers\Translation::init('admin/categories');

        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $departments = [];
            $departmentsList = \common\classes\department::getList();
            foreach ($departmentsList as $department) {
                $departments[$department['departments_id']] = $department['departments_store_name'];
            }
        }

        global $login_id;
        $languages_id = \Yii::$app->settings->get('languages_id');
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_category_id = Yii::$app->request->get('id', 0);

        if ($length == -1)
            $length = 10000;

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $categoriesQty = 0;
        $productsQty = 0;

        $current_page_number = ($start / $length) + 1;
        $responseList = [];
        $_session = Yii::$app->session;
        $_session->remove('products_query_raw');

        $list_bread_crumb = '';
        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where cd.categories_name like '%" . $keywords . "%' ";
            $searchFields = ['pd.products_name', 'pd.products_seo_page_name', 'cd.categories_seo_page_name', 'p.products_model'];
            $search = " and ( " . implode(" like '%" . tep_db_input($keywords) . "%' or ", $searchFields) . " like '%" . tep_db_input($keywords) . "%')";
        } else {
            $search_condition = " where 1 ";
        }

        $search_condition .= " and c.parent_id='" . (int) $current_category_id . "'";

        //--- Apply filter start
        $onlyCategories = false;
        $onlyProducts = false;
        $filter_cat = '';
        $filter_prod = '';
        $use_iventory = false;

        $platform_filter_categories = '';
        $platform_filter_products = '';

        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $filter_by_departments = array();
            if ( isset($output['departments']) && is_array($output['departments']) ) {
                foreach( $output['departments'] as $_department_id ) if ( (int)$_department_id>0 ) $filter_by_departments[] = (int)$_department_id;
            }

            if ( count($filter_by_departments)>0 ) {
                $platform_filter_categories .= ' and c.categories_id IN (SELECT categories_id FROM ' . TABLE_DEPARTMENTS_CATEGORIES . ' WHERE departments_id IN(\'' . implode("','", $filter_by_departments) . '\'))  ';
                $platform_filter_products .= ' and p.products_id IN (SELECT products_id FROM ' . TABLE_DEPARTMENTS_PRODUCTS . ' WHERE departments_id IN(\'' . implode("','", $filter_by_departments) . '\'))  ';
            }
        }

        $filter_by_platform = array();
        if (isset($output['platform']) && is_array($output['platform'])) {
            foreach ($output['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $filter_by_platform[] = (int) $_platform_id;
        } elseif (false === \common\helpers\Acl::rule(['SUPERUSER'])) {
            $platforms = \common\models\AdminPlatforms::find()->where(['admin_id' => $login_id])->asArray()->all();
            foreach ($platforms as $platform) {
                $filter_by_platform[] = $platform['platform_id'];
            }
            if ( count($filter_by_platform)==0 ) {
                $filter_by_platform[] = 0;
            }
        }

        if (count($filter_by_platform) > 0) {
//            $filter_cat .= ' and c.categories_id IN (SELECT categories_id FROM '.TABLE_PLATFORMS_CATEGORIES.' WHERE platform_id IN(\''.implode("','",$filter_by_platform).'\'))  ';
//            $filter_prod .= ' and p.products_id IN (SELECT products_id FROM '.TABLE_PLATFORMS_PRODUCTS.' WHERE platform_id IN(\''.implode("','",$filter_by_platform).'\'))  ';
            $platform_filter_categories .= ' and c.categories_id IN (SELECT categories_id FROM ' . TABLE_PLATFORMS_CATEGORIES . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
            $platform_filter_products .= ' and p.products_id IN (SELECT products_id FROM ' . TABLE_PLATFORMS_PRODUCTS . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
        }

        if (tep_not_null($output['search'])) {
            $search = tep_db_prepare_input($output['search']);
            switch ($output['by']) {
                case 'name':
                    $filter_prod .= " and (pd.products_name like '%" . tep_db_input($search) . "%' or pdd.products_name like '%" . tep_db_input($search) . "%') ";
                    $onlyProducts = true;
                    break;
                case 'internal_name':
                    $filter_prod .= " and (pd.products_internal_name like '%" . tep_db_input($search) . "%' or pdd.products_internal_name like '%" . tep_db_input($search) . "%') ";
                    $onlyProducts = true;
                    break;
                case 'description':
                    $filter_prod .= " and pd.products_description like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'cname': default:
                    $filter_cat .= " and (cd.categories_name like '%" . tep_db_input($search) . "%' or cdd.categories_name like '%" . tep_db_input($search) . "%') ";
                    $onlyCategories = true;
                    break;
                case 'cdescription':
                    $filter_cat .= " and cd.categories_description like '%" . tep_db_input($search) . "%' ";
                    $onlyCategories = true;
                    break;
                case 'title':
                    $filter_prod .= " and pd.products_head_title_tag like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'header':
                    $filter_prod .= " and pd.products_head_desc_tag like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'keywords':
                    $filter_prod .= " and pd.products_head_keywords_tag like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'model':
                    $filter_prod .= " and p.products_model like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'ean':
                    $filter_prod .= " and p.products_ean like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'upc':
                    $filter_prod .= " and p.products_upc like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'asin':
                    $filter_prod .= " and p.products_asin like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'isbn':
                    $filter_prod .= " and p.products_isbn like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'file':
                    $filter_prod .= " and p.products_file like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'image':
                    $filter_prod .= " and p.products_image like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " and c.categories_image like '%" . tep_db_input($search) . "%' ";
                    break;
                case 'seo':
                    $filter_prod .= " and (p.products_seo_page_name like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or pd.products_seo_page_name like '%" . tep_db_input($search) . "%' )";
                    $filter_cat .= " and c.categories_seo_page_name like '%" . tep_db_input($search) . "%' ";
                    break;

                case '':
                case 'any':
                    /** @var \common\extensions\PlainProductsDescription\PlainProductsDescription  $ext  */
                    $ext = \common\helpers\Acl::checkExtensionAllowed('PlainProductsDescription', 'allowed');
                    if ($ext && $ext::isEnabled()) {
                        $searchBuilder = new \common\components\SearchBuilder('simple');
                        $searchBuilder->setSearchInDesc(SEARCH_IN_DESCRIPTION == 'True');
                        $searchBuilder->setSearchInternal(true);
                        $searchBuilder->searchInProperty = false;
                        $searchBuilder->searchInAttributes = false;
                        $searchBuilder->parseKeywords($search);
                        $productsQuery = \common\models\Products::find()->distinct()->alias('p');
                        $searchBuilder->addProductsRestriction($productsQuery);
                        $productsQuery->select('p.products_id')->orderBy('p.products_id');

                        $filter_prod .= " and (";
                        $filter_prod .= "p.products_id in ('" . implode("','", $productsQuery->asArray()->column()) . "') ";
                        $filter_prod .= ") ";
                        
                    } else {
                        $filter_prod .= " and (";
                        $filter_prod .= " pd.products_name like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or pdd.products_name like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or pd.products_internal_name like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or pdd.products_internal_name like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or pd.products_description like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or pd.products_head_title_tag like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or pd.products_head_desc_tag like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or pd.products_head_keywords_tag like '%" . tep_db_input($search) . "%' ";

                        $filter_prod .= " or p.products_model like '%" . tep_db_input($search) . "%' ";
                        /** @var \common\extensions\Inventory\Inventory $inv */
                        if ($inv = \common\helpers\Extensions::isAllowed('Inventory')) {
                            $use_iventory = true;
                            $filter_prod .= " or i.products_model like '%" . tep_db_input($search) . "%' ";
                            // add search in suppliers
                            $filter_prod .= " or suppp.suppliers_product_name like '%" . tep_db_input($search) . "%' ";
                            $filter_prod .= " or suppp.suppliers_model like '%" . tep_db_input($search) . "%' ";
                            $filter_prod .= " or suppp.suppliers_upc like '%" . tep_db_input($search) . "%' ";
                        }

                        $filter_prod .= " or p.products_ean like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or p.products_upc like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or p.products_asin like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or p.products_isbn like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or p.products_file like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or p.products_image like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or p.products_seo_page_name like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= " or pd.products_seo_page_name like '%" . tep_db_input($search) . "%' ";
                        $filter_prod .= ") ";
                    }
                    $filter_cat .= " and (";
                    $filter_cat .= " cd.categories_name like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " or cdd.categories_name like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " or cd.categories_description like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " or c.categories_image like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " or c.categories_seo_page_name like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= ") ";
                    break;
            }
        }

        if (tep_not_null($output['brand'])) {
            $onlyProducts = true;
            //$filter_prod .= " and m.manufacturers_name like '%" . tep_db_input($output['brand']) . "%'";
            $_matched_manufacturers = \yii\helpers\ArrayHelper::map(
                    \common\models\Manufacturers::find()
                        ->where(['LIKE','manufacturers_name',$output['brand']])
                        ->select(['id' => 'manufacturers_id', 'exact_match' => new \yii\db\Expression('IF(manufacturers_name=:brand_name,1,0)',[':brand_name'=>$output['brand']])])
                        ->asArray()->all(),
                    'id','id', 'exact_match');
            if ( isset($_matched_manufacturers[1]) && count($_matched_manufacturers[1])>0 ){
                $filter_prod .= " and p.manufacturers_id in('".implode("','",$_matched_manufacturers[1])."')";
            }elseif (isset($_matched_manufacturers[0]) && count($_matched_manufacturers[0])>0){
                $filter_prod .= " and p.manufacturers_id in('".implode("','",$_matched_manufacturers[0])."')";
            }else{
                $filter_prod .= " and 1=0 /*brand filter*/ ";
            }
        }

        if (tep_not_null($output['supplier']) || tep_not_null($output['source'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(sp.products_id) FROM " . TABLE_SUPPLIERS_PRODUCTS . " as sp LEFT JOIN " . TABLE_SUPPLIERS . " as s on (sp.suppliers_id=s.suppliers_id) WHERE s.suppliers_name like '%" . tep_db_input($output['supplier']) . "%' AND sp.source like '%".tep_db_input($output['source'])."%'");
            if (tep_db_num_rows($check_products_query) > 0) {
                $featuredIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $featuredIds[] = $check_products['products_id'];
                }
                $_supplier_products_filter = "p.products_id IN (" . implode(", ", $featuredIds) . ")";
            } else {
                $_supplier_products_filter = "p.products_id = -1";
            }
            if (tep_not_null($output['source'])){
                $filter_prod .= " and ({$_supplier_products_filter} or p.source like '%" . tep_db_input($output['source']) . "%')";
            }else{
                $filter_prod .= " and {$_supplier_products_filter}";
            }
        }


        if (tep_not_null($output['stock'])) {
            switch ($output['stock']) {
                case 'y':
                    $onlyProducts = true;
                    $filter_prod .= " and p.products_quantity > 0 and p.products_id_stock=p.products_id";
                    break;
                case 'n':
                    $onlyProducts = true;
                    $filter_prod .= " and p.products_quantity <= 0 and p.products_id_stock=p.products_id";
                    break;
                default:
                    break;
            }
        }

        if (tep_not_null($output['status'])) {
            switch ($output['status']) {
                case 'y':
                    $onlyProducts = true;
                    $filter_prod .= " and p.products_status = '1' ";
                    break;
                case 'n':
                    $onlyProducts = true;
                    $filter_prod .= " and p.products_status = '0' ";
                    break;
                default:
                    break;
            }
        }

        if (isset($output['price_from']) && !empty($output['price_from'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.products_price >= '" . tep_db_input($output['price_from']) . "' ";
        }
        if (isset($output['price_to']) && !empty($output['price_to'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.products_price <= '" . tep_db_input($output['price_to']) . "' ";
        }
        if (isset($output['weight_from']) && !empty($output['weight_from'])) {
            $onlyProducts = true;
            if ($output['weight_value'] == 'lbs') {
                $filter_prod .= " and p.weight_in >= '" . tep_db_input($output['weight_from']) . "' ";
            } else {
                $filter_prod .= " and p.weight_cm >= '" . tep_db_input($output['weight_from']) . "' ";
            }
        }
        if (isset($output['weight_to']) && !empty($output['weight_to'])) {
            $onlyProducts = true;
            if ($output['weight_value'] == 'lbs') {
                $filter_prod .= " and p.weight_in <= '" . tep_db_input($output['weight_to']) . "' ";
            } else {
                $filter_prod .= " and p.weight_cm <= '" . tep_db_input($output['weight_to']) . "' ";
            }
        }

        if (isset($output['prod_attr'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(products_id) FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE 1");
            if (tep_db_num_rows($check_products_query) > 0) {
                $featuredIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $featuredIds[] = $check_products['products_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $featuredIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
        }

        if (isset($output['low_stock'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.products_quantity < '" . STOCK_REORDER_LEVEL . "' ";
        }

        if (isset($output['featured'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(products_id) FROM " . TABLE_FEATURED . " WHERE 1");
            if (tep_db_num_rows($check_products_query) > 0) {
                $featuredIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $featuredIds[] = $check_products['products_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $featuredIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
        }

        if (isset($output['gift'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(products_id) FROM " . TABLE_GIFT_WRAP_PRODUCTS . " WHERE 1");
            if (tep_db_num_rows($check_products_query) > 0) {
                $featuredIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $featuredIds[] = $check_products['products_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $featuredIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
        }

        if (isset($output['virtual'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.is_virtual = '1' ";
        }

        if (isset($output['type_listing'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.is_listing_product = '1' ";
        }

        if (isset($output['type_not_listing'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.is_listing_product = '0' ";
        }

        if (isset($output['sub_children'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.parent_products_id != 0 ";
        }

        if (isset($output['all_bundles'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.is_bundle = 1";
        }

        if (isset($output['sale'])) {
            $onlyProducts = true;
            $saleIds = \common\models\Specials::find()->select('products_id')->expired(false)->distinct()->asArray()->column();
            if (!empty($saleIds)) {
              $filter_prod .= " and p.products_id IN (" . implode(", ", $saleIds) . ")";
            } else {
              $filter_prod .= " and p.products_id = -1";
            }
            /*
            $check_products_query = tep_db_query("SELECT distinct(products_id) FROM " . TABLE_SPECIALS . " WHERE 1");
            if (tep_db_num_rows($check_products_query) > 0) {
                $saleIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $saleIds[] = $check_products['products_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $saleIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
            */
        }

        if (isset($output['wo_images'])) {
            $_wo_images_pids = Yii::$app->getDb()->createCommand(
                "SELECT p.products_id ".
                "FROM ".TABLE_PRODUCTS." p ".
                " LEFT JOIN ".TABLE_PRODUCTS_IMAGES." pi ON (p.products_id = pi.products_id) ".
                "WHERE pi.products_id IS NULL"
            )->queryColumn();
            if ( count($_wo_images_pids)>0 ) {
                $platform_filter_products .= " and p.products_id IN (" . implode(", ", $_wo_images_pids) . ")";

                $_wo_images_categories = Yii::$app->getDb()->createCommand(
                    "SELECT DISTINCT p2c.categories_id ".
                    "FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ".
                    "WHERE p2c.products_id IN (" . implode(", ", $_wo_images_pids) . ")"
                )->queryColumn();
                foreach ($_wo_images_categories as $_cat_id){
                    \common\helpers\Categories::get_parent_categories($_wo_images_categories, $_cat_id, false);
                }
                $platform_filter_categories = " AND c.categories_id IN (" . implode(", ", $_wo_images_categories) . ")";

            }else{
                $platform_filter_products .= " AND 1=0 /*wo images empty*/ ";
                $platform_filter_categories .= " AND 1=0 /*wo images empty*/ ";
            }
        }

        if (false === \common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_EDIT'])) {
            $disableCategoryItem = true;
        } else {
            $disableCategoryItem = false;
        }
        if (false === \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT'])) {
            $disableProductItem = true;
        } else {
            $disableProductItem = false;
        }

        if (!empty($filter_prod) || !empty($filter_cat)) {
            // SEARCH
            $list_bread_crumb = '';
            $rowsCounter = 0;

            if (!$onlyProducts) {
                //categories
                $orderByCategory = "c.sort_order, cd.categories_name";
                $categories_query_raw = "select distinct(c.categories_id), if(length(cd.categories_name) > 0, cd.categories_name, cdd.categories_name) as categories_name, c.categories_status, c.manual_control_status from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id=cd.categories_id left join " . TABLE_CATEGORIES_DESCRIPTION . " cdd on c.categories_id=cdd.categories_id where 1 " . $filter_cat . " and cd.language_id = '" . (int) $languages_id . "' and cdd.language_id = '" . \common\helpers\Language::get_default_language_id() . "' and cd.affiliate_id = 0 " . $platform_filter_categories . " order by " . $orderByCategory;
                $remind_page_number = $current_page_number;
                $categories_split = new \splitPageResults($current_page_number, $length, $categories_query_raw, $categories_query_numrows, 'c.categories_id');
                $categories_query = tep_db_query($categories_query_raw);
                $categoriesQty = $categories_query_numrows;

                if ($remind_page_number == $current_page_number) {// all categories showed, now show only products
                    while ($categories = tep_db_fetch_array($categories_query)) {
                        $responseList[] = array(
                            '<input type="checkbox"'.($disableCategoryItem ? ' disabled' : '').' class="'. ($categoriesQty < CATALOG_SPEED_UP_DESIGN ? 'uniform' : '' ) .' js-cat-batch" name="batch[]" value="c_'.$categories['categories_id'].'">',
                            '<div class="handle_cat_list state-disabled"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name"><b>' . $categories['categories_name'] . '</b><input class="cell_identify" type="hidden" value="' . $categories['categories_id'] . '"><input class="cell_type" type="hidden" value="category"></div></div>',
                             ($categories['categories_status'] == 1 ? '<input type="checkbox" value="' . $categories['categories_id'] . '" name="categories_status" class="'. ($categoriesQty < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' ) .'" checked="checked"'.($disableCategoryItem ? ' readonly' : '').'>' : '<input type="checkbox" value="' . $categories['categories_id'] . '" name="categories_status" class="'. ($categoriesQty < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' ) .'"'.($disableCategoryItem ? ' readonly' : '').'>')
                        );

                        if ($ext = \common\helpers\Acl::checkExtension('AutomaticallyStatus', 'allowed')) {
                            if ($ext::allowed() && !$categories['manual_control_status']) {
                                $responseList[count($responseList) - 1]['DT_RowClass'] = 'check_status_auto';
                            }
                        }

                        $rowsCounter++;
                    }
                }
            }
            if (!$onlyCategories) {
                //products
                $orderByProduct = "p2c.sort_order, pd.products_name";
                $products_query_raw = "select p.products_id, p.is_listing_product, p.sub_product_children_count, p.parent_products_id, p.products_groups_id, p.products_model, ".ProductNameDecorator::instance()->listingQueryExpression('pd','pdd')." as products_name, p.products_status, p.manual_control_status, p.products_image, p.products_quantity from " . TABLE_PRODUCTS . " p LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " as pd on p.products_id = pd.products_id LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " as pdd on p.products_id = pdd.products_id LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " as p2c on p.products_id = p2c.products_id LEFT JOIN " . TABLE_MANUFACTURERS . " as m on p.manufacturers_id=m.manufacturers_id " . ($use_iventory ? "LEFT JOIN " . TABLE_INVENTORY . " i on i.prid = p.products_id LEFT JOIN " . TABLE_SUPPLIERS_PRODUCTS . " as suppp on i.products_id = suppp.uprid" : "") . " where pd.language_id = '" . (int) $languages_id . "' and pdd.language_id = '" . \common\helpers\Language::get_default_language_id() . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pdd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.department_id=0 and pdd.department_id=0 " . $filter_prod . " {$platform_filter_products} group by p.products_id order by " . $orderByProduct;

                $products_query = tep_db_query($products_query_raw);
                $products_query_numrows = tep_db_num_rows($products_query);

                $categories_query_numrows = $categories_query_numrows ?? 0;
                $offset = $start - $categories_query_numrows;
                $products_query_raw .= " limit " . max($offset, 0) . ", " . $length;
                $products_query = tep_db_query($products_query_raw);

                $productsQty = $products_query_numrows;

                $categories_query_numrows += $products_query_numrows;
                if ($rowsCounter < $length) {
                    $products_query = tep_db_query($products_query_raw);
                    while ($products = tep_db_fetch_array($products_query)) {
                        if ( empty($products['products_name']) ) {
                            $products['products_name'] = \common\helpers\Product::get_products_name($products['products_id']);
                        }
                        // (file_exists(DIR_FS_CATALOG_IMAGES . $products['products_image']) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($products['products_image'], $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>')
                        $image = \common\classes\Images::getImage($products['products_id']);
                        //(!empty($image) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($image, $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>');
                        $product_categories_string = '';
                        if (true) {
                            $product_categories = \common\helpers\Categories::generate_category_path($products['products_id'], 'product');
                            $product_categories_string .= '';
                            for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                                $category_path = '';
                                for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                                    $category_path .= '<span class="category_path__location">' . $product_categories[$i][$j]['text'] . '</span>&nbsp;&gt;&nbsp;';
                                }
                                $category_path = substr($category_path, 0, -16);
                                $product_categories_string .= '<li class="category_path">' . $category_path . '</li>';
                            }
                            $product_categories_string = '<span class="category_path" style="display:block">' . TEXT_LIST_PRODUCT_PLACED_IN . '</span> <ul class="category_path_list">' . $product_categories_string . '</ul>';
                        }
                        $productMarkers = '';
                        if ( defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' ) {
                            if ($products['is_listing_product']) {
                                $productMarkers .= '<i class="product_list_marker product_list_marker__listing">' . TEXT_LISTING_PRODUCT . '</i> ';
                            } else {
                                $productMarkers .= '<i class="product_list_marker product_list_marker__master">' . TEXT_MASTER_PRODUCT . '</i> ';
                            }
                            if ($products['parent_products_id']) {
                                $products['products_quantity'] = \common\helpers\Product::get_products_info($products['parent_products_id'],'products_quantity');
                                $__linkContent = \common\helpers\Product::get_products_info($products['parent_products_id'], 'products_model');
                                if ( $__linkContent ) $__linkContent = "{$__linkContent} ";
                                $__linkContent .= \common\helpers\Product::get_backend_products_name($products['parent_products_id']);
                                $parent_product_name = TEXT_PARENT_PRODUCT.' '.Html::a($__linkContent,Url::to(['categories/productedit', 'pID'=> $products['parent_products_id']]));
                                $productMarkers .= '<i class="product_list_marker product_list_marker__child_of">' . TEXT_CHILD_PRODUCT . '<div class="product_list_marker__pophover">'.$parent_product_name.'</div></i> ';
                            }elseif($products['sub_product_children_count']>0){
                                $childrenProducts = \yii\helpers\ArrayHelper::map(\common\models\Products::find()->where(['parent_products_id'=>$products['products_id']])->select(['products_id','products_model'])->asArray()->all(),'products_id','products_model');
                                foreach ($childrenProducts as $childrenProductId=>$childrenProductModel){
                                    $childrenProducts[$childrenProductId] = '<div>'.TEXT_CHILD_PRODUCT.' '.Html::a(($childrenProductModel?"{$childrenProductModel} ":'').\common\helpers\Product::get_backend_products_name($childrenProductId),Url::to(['categories/productedit', 'pID'=> $childrenProductId])).'</div>';
                                }
                                $productMarkers .= '<i class="product_list_marker product_list_marker__parent_of">' . TEXT_PARENT_PRODUCT . '<div class="product_list_marker__pophover">'.implode('',$childrenProducts).'</div></i> ';
                            }
                        }
                        if ( $products['products_groups_id'] ){
                            $product_categories_string = '<i class="next-row category_path product_list__products_group">'.\common\helpers\Product::products_groups_name($products['products_groups_id']).'</i>'.$product_categories_string;
                        }

                        $responseList[] = array(
                            '<input type="checkbox"'.($disableProductItem ? ' disabled' : '').' class="'. ($productsQty < CATALOG_SPEED_UP_DESIGN ? 'uniform' : '' ) .' js-cat-batch" name="batch[]" value="p_'.$products['products_id'].'">',
                            '<div class="handle_cat_list state-disabled' . ($products['products_status'] == 1 ? '' : ' dis_prod') . '">' .
                            '<span class="handle"><i class="icon-hand-paper-o"></i></span>' .
                            '<div class="prod_name prod_name_double" data-click-double="' . tep_href_link(FILENAME_CATEGORIES . '/productedit', 'pID=' . $products['products_id']) . '">' .
                            (!empty($image) ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') .
                            '<table class="wrapper"><tr><td><span class="prodNameC">' . $products['products_name'] . $product_categories_string . '</span></td></tr></table>' .
                            '<span class="prodIDsC"><span title="' . \common\helpers\Output::output_string($products['products_model']) . '">' . ( $products['products_model'] ? TEXT_SKU . ': ' . $products['products_model'] . '<br>' : '') . TEXT_PRODUCTS_QUANTITY_INFO . ': ' . \common\helpers\Product::getVirtualItemQuantity($products['products_id'], $products['products_quantity']) . '<br>' . TABLE_HEADING_ID . ': ' . $products['products_id'] . '</span>' . $productMarkers.'</span>' .
                            '<input class="cell_identify" type="hidden" value="' . $products['products_id'] . '"><input class="cell_type" type="hidden" value="product">' .
                            '</div>' .
                            '</div>',
                            ($products['products_status'] == 1 ? '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="check_on_off" checked="checked"'.($disableProductItem ? ' readonly' : '').'>' : '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="'. ($productsQty < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' ) .'"'.($disableProductItem ? ' readonly' : '').'>')
                        );

                        if ($ext = \common\helpers\Acl::checkExtension('AutomaticallyStatus', 'allowed')) {
                            if ($ext::allowed() && !$products['manual_control_status']) {
                                $responseList[count($responseList) - 1]['DT_RowClass'] = 'check_status_auto';
                            }
                        }

                        //$categories_query_numrows++;
                        $rowsCounter++;
                        if ($rowsCounter >= $length) {
                            break;
                        }
                    }
                }
            }

            //--- Apply filter end
        } elseif ($output['listing_type'] == 'category') {
            $list_bread_crumb = TEXT_CATALOG_LIST_BREADCRUMB . ' ';

            $list_bread_crumb .= ' &gt; ' . \common\helpers\Categories::output_generated_category_path($current_category_id, 'category', '<span class="category_path__location clickable_element js-category-navigate" data-id="%1$s">%2$s</span>');

            $orderByCategory = "c.sort_order, cd.categories_name";
            $orderByProduct = "p2c.sort_order, pd.products_name";

            $rowsCounter = 0;

            $categories_query_raw = "select distinct(c.categories_id), if(length(cd.categories_name) > 0, cd.categories_name, cdd.categories_name) as categories_name, c.categories_status, c.manual_control_status, c.categories_image from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id=cd.categories_id left join " . TABLE_CATEGORIES_DESCRIPTION . " cdd on c.categories_id=cdd.categories_id " . $search_condition . " and cd.language_id = '" . (int) $languages_id . "' and cdd.language_id = '" . \common\helpers\Language::get_default_language_id() . "' and cd.affiliate_id = 0 " . $platform_filter_categories . " order by " . $orderByCategory;

            $remind_page_number = $current_page_number;

            $categories_split = new \splitPageResults($current_page_number, $length, $categories_query_raw, $categories_query_numrows, 'c.categories_id');
            $categories_query = tep_db_query($categories_query_raw);

            if ($current_category_id > 0) {
                $parrent_query = tep_db_query("select parent_id, categories_status from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $current_category_id . "'");
                if ($parrent = tep_db_fetch_array($parrent_query)) {
                    $responseList[] = array(
                        '',
                        '<span class="parent_cats"><i class="icon-circle"></i><i class="icon-circle"></i><i class="icon-circle"></i></span><input class="cell_identify" type="hidden" value="' . $parrent['parent_id'] . '"><input class="cell_type" type="hidden" value="parent">',
                        ''
                    );
                }
            }

            $categoriesQty = $categories_query_numrows;

            if ($remind_page_number == $current_page_number) {// all categories showed, now show only products
                while ($categories = tep_db_fetch_array($categories_query)) {
                    $image_path = DIR_WS_CATALOG_IMAGES . $categories['categories_image'];
                    $responseList[] = array(
                        '<input type="checkbox"'.($disableCategoryItem ? ' disabled' : '').' class="'. ($categoriesQty < CATALOG_SPEED_UP_DESIGN ? 'uniform' : '' ) .' js-cat-batch" name="batch[]" value="c_'.$categories['categories_id'].'">',
                        '<div class="handle_cat_list' . ($categories['categories_status'] == 1 ? '' : ' dis_prod') . '"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name' . ($categories['categories_image'] ? ' catNameImg' : '') . '">' . ($categories['categories_image'] ? '<span class="prodCatImg"><img src="' . $image_path . '"></span>' : '') . '<b>' . $categories['categories_name'] . '</b><input class="cell_identify" type="hidden" value="' . $categories['categories_id'] . '"><input class="cell_type" type="hidden" value="category"></div></div>',
                        ($categories['categories_status'] == 1 ? '<input type="checkbox" value="' . $categories['categories_id'] . '" name="categories_status" class="'. ($categoriesQty < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' ) .'" checked="checked"'.($disableCategoryItem ? ' readonly' : '').'>' : '<input type="checkbox" value="' . $categories['categories_id'] . '" name="categories_status" class="'. ($categoriesQty < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' ) .'"'.($disableCategoryItem ? ' readonly' : '').'>')
                    );
                    if ($ext = \common\helpers\Acl::checkExtension('AutomaticallyStatus', 'allowed')) {
                        if ($ext::allowed() && !$categories['manual_control_status']) {
                            $responseList[count($responseList) - 1]['DT_RowClass'] = 'check_status_auto';
                        }
                    }
                    $rowsCounter++;
                }
            }


            /**
             * Recalc products offset
             */
            $offset = $start - $categories_query_numrows;
            $products_in_category = \common\models\Products::find()->alias('p')
                    ->select('p.products_id, p.is_listing_product, p.sub_product_children_count, p.parent_products_id, p.products_groups_id, p.products_model, p.products_status, p.manual_control_status, p.products_image, p.products_quantity')
                    ->addSelect(['products_name' => new \yii\db\Expression(ProductNameDecorator::instance()->listingQueryExpression('pd','pdd'))] )
                    ->wDescription('pd')
                    ->wDescription('pdd', \common\helpers\Language::get_default_language_id())
                    ->innerJoinWith(['categoriesList p2c' => function ($query) use ($current_category_id) {$query->andOnCondition(['p2c.categories_id' => (int) $current_category_id ]);}]);
            $products_in_category->andWhere('1 '.$platform_filter_products);
            $productsQty = $products_query_numrows = $products_in_category->count();

            $categories_query_numrows += $products_query_numrows;
            if ($rowsCounter < $length) {
                $products_in_category = $products_in_category
                        ->orderBy($orderByProduct)
                        ->offset(max($offset, 0))
                        ->limit($length);

                $products_query_raw = $products_in_category->createCommand()->getRawSql();  // backward compatibility
                $products_all = $products_in_category->asArray()->all();
                foreach ($products_all as $products) {
                    if ( empty($products['products_name']) ) {
                        $products['products_name'] = \common\helpers\Product::get_products_name($products['products_id']);
                    }
                    // (file_exists(DIR_FS_CATALOG_IMAGES . $products['products_image']) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($products['products_image'], $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>')
                    $image = \common\classes\Images::getImage($products['products_id']);
                    $product_categories_string = '';
                    if (true) {
                        $product_categories = \common\helpers\Categories::generate_category_path($products['products_id'], 'product');
                        if (count($product_categories) > 1) {
                            $product_categories_string .= '';
                            for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                                $category_path = '';
                                if (intval($product_categories[$i][count($product_categories[$i]) - 1]['id']) == (int) $current_category_id)
                                    continue;
                                for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                                    $category_path .= '<span class="category_path__location">' . $product_categories[$i][$j]['text'] . '</span>&nbsp;&gt;&nbsp;';
                                }
                                $category_path = substr($category_path, 0, -16);
                                $product_categories_string .= '<li class="category_path">' . $category_path . '</li>';
                            }
                            $product_categories_string = '<span class="category_path" style="display:block">' . TEXT_LIST_PRODUCT_ALSO_PLACED_IN . '</span> <ul class="category_path_list">' . $product_categories_string . '</ul>';
                        }
                    }
                    $productMarkers = '';
                    if ( defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' ) {
                        if ($products['is_listing_product']) {
                            $productMarkers .= '<i class="product_list_marker product_list_marker__listing">' . TEXT_LISTING_PRODUCT . '</i> ';
                        } else {
                            $productMarkers .= '<i class="product_list_marker product_list_marker__master">' . TEXT_MASTER_PRODUCT . '</i> ';
                        }
                        if ($products['parent_products_id']) {
                            $products['products_quantity'] = \common\helpers\Product::get_products_info($products['parent_products_id'],'products_quantity');
                            $__linkContent = \common\helpers\Product::get_products_info($products['parent_products_id'], 'products_model');
                            if ( $__linkContent ) $__linkContent = "{$__linkContent} ";
                            $__linkContent .= \common\helpers\Product::get_backend_products_name($products['parent_products_id']);
                            $parent_product_name = TEXT_PARENT_PRODUCT.' '.Html::a($__linkContent,Url::to(['categories/productedit', 'pID'=> $products['parent_products_id']]));
                            $productMarkers .= '<i class="product_list_marker product_list_marker__child_of">' . TEXT_CHILD_PRODUCT . '<div class="product_list_marker__pophover">'.$parent_product_name.'</div></i> ';
                        }elseif($products['sub_product_children_count']>0){
                            $childrenProducts = \yii\helpers\ArrayHelper::map(\common\models\Products::find()->where(['parent_products_id'=>$products['products_id']])->select(['products_id','products_model'])->asArray()->all(),'products_id','products_model');
                            foreach ($childrenProducts as $childrenProductId=>$childrenProductModel){
                                $childrenProducts[$childrenProductId] = '<div>'.TEXT_CHILD_PRODUCT.' '.Html::a(($childrenProductModel?"{$childrenProductModel} ":'').\common\helpers\Product::get_backend_products_name($childrenProductId),Url::to(['categories/productedit', 'pID'=> $childrenProductId])).'</div>';
                            }
                            $productMarkers .= '<i class="product_list_marker product_list_marker__parent_of">' . TEXT_PARENT_PRODUCT . '<div class="product_list_marker__pophover">'.implode('',$childrenProducts).'</div></i> ';
                        }
                    }
                    if ( $products['products_groups_id'] ){
                        $product_categories_string = '<i class="next-row category_path product_list__products_group">'.\common\helpers\Product::products_groups_name($products['products_groups_id']).'</i>'.$product_categories_string;
                    }

                    $responseList[] = array(
                        '<input type="checkbox"'.($disableProductItem ? ' disabled' : '').' class="'. ($productsQty < CATALOG_SPEED_UP_DESIGN ? 'uniform' : '' ) .' js-cat-batch" name="batch[]" value="p_'.$products['products_id'].'">',
                        '<div class="handle_cat_list prod_handle' . ($products['products_status'] == 1 ? '' : ' dis_prod') . '">' .
                        '<span class="handle"><i class="icon-hand-paper-o"></i></span>' .
                        '<div class="prod_name prod_name_double" data-click-double="' . tep_href_link(FILENAME_CATEGORIES . '/productedit', 'pID=' . $products['products_id']) . '">' .
                        (!empty($image) ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') .
                        '<table class="wrapper"><tr><td><span class="prodNameC">' . $products['products_name'] . $product_categories_string . '</span></td></tr></table>' .
                        '<span class="prodIDsC"><span title="' . \common\helpers\Output::output_string($products['products_model']) . '">' . ($products['products_model'] ? TEXT_SKU . ': ' . $products['products_model'] . '<br>' : '') . TEXT_PRODUCTS_QUANTITY_INFO . ': ' . \common\helpers\Product::getVirtualItemQuantity($products['products_id'], $products['products_quantity']) . '<br>' . TABLE_HEADING_ID . ': ' . $products['products_id'] .'</span>' . $productMarkers.'</span>' .
                        '<input class="cell_identify" type="hidden" value="' . $products['products_id'] . '">' .
                        '<input class="cell_type" type="hidden" value="product">' .
                        '</div>' .
                        '</div>',
                        ($products['products_status'] == 1 ? '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="'. ($productsQty < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' ) .'" checked="checked"'.($disableProductItem ? ' readonly' : '').'>' : '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="'. ($productsQty < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' ) .'"'.($disableProductItem ? ' readonly' : '').'>')
                    );

                    if ($ext = \common\helpers\Acl::checkExtension('AutomaticallyStatus', 'allowed')) {
                        if ($ext::allowed() && !$products['manual_control_status']) {
                            $responseList[count($responseList) - 1]['DT_RowClass'] = 'check_status_auto';
                        }
                    }

                    //$categories_query_numrows++;
                    $rowsCounter++;
                    if ($rowsCounter >= $length) {
                        break;
                    }
                }
            }
        } else {
            // BRAND listing
            $list_bread_crumb = '';
            $ff = $search;
            $order = 'p.sort_order, pd.products_name';

            $products_query_raw = "select *, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." as products_name, p.products_groups_id from " . TABLE_PRODUCTS . " p " . (intval($output['brand_id']) == -1 ? " left join " . TABLE_MANUFACTURERS . " m ON m.manufacturers_id=p.manufacturers_id " : '') . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on (p.products_id = pd.products_id and pd.language_id='" . intval($languages_id) . "') where pd.platform_id = '".intval(\common\classes\platform::defaultId())."' " . (intval($output['brand_id']) > 0 ? " and manufacturers_id = '" . intval($output['brand_id']) . "' " : (intval($output['brand_id']) == -1 ? ' and m.manufacturers_id IS NULL' : '')) . $ff . " {$platform_filter_products} group by p.products_id ORDER BY " . $order;

            $products_split = new \splitPageResults($current_page_number, $length, $products_query_raw, $categories_query_numrows, 'p.products_id');
            $products_query = tep_db_query($products_query_raw);
            $productsQty = $categories_query_numrows;
            while ($products = tep_db_fetch_array($products_query)) {
                if ( empty($products['products_name']) ) {
                    $products['products_name'] = \common\helpers\Product::get_products_name($products['products_id']);
                }

                $image = \common\classes\Images::getImage($products['products_id']);
                $product_categories_string = '';
                if (true) {
                    $product_categories = \common\helpers\Categories::generate_category_path($products['products_id'], 'product');
                    $product_categories_string .= '';
                    for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                        $category_path = '';
                        for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                            $category_path .= '<span class="category_path__location">' . $product_categories[$i][$j]['text'] . '</span>&nbsp;&gt;&nbsp;';
                        }
                        $category_path = substr($category_path, 0, -16);
                        $product_categories_string .= '<li class="category_path">' . $category_path . '</li>';
                    }
                    $product_categories_string = '<span class="category_path" style="display:block">' . TEXT_LIST_PRODUCT_PLACED_IN . '</span> <ul class="category_path_list">' . $product_categories_string . '</ul>';
                }
                $productMarkers = '';
                if ( defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' ) {
                    if ($products['is_listing_product']) {
                        $productMarkers .= '<i class="product_list_marker product_list_marker__listing">' . TEXT_LISTING_PRODUCT . '</i> ';
                    } else {
                        $productMarkers .= '<i class="product_list_marker product_list_marker__master">' . TEXT_MASTER_PRODUCT . '</i> ';
                    }
                    if ($products['parent_products_id']) {
                        $products['products_quantity'] = \common\helpers\Product::get_products_info($products['parent_products_id'],'products_quantity');
                        $__linkContent = \common\helpers\Product::get_products_info($products['parent_products_id'], 'products_model');
                        if ( $__linkContent ) $__linkContent = "{$__linkContent} ";
                        $__linkContent .= \common\helpers\Product::get_backend_products_name($products['parent_products_id']);
                        $parent_product_name = TEXT_PARENT_PRODUCT.' '.Html::a($__linkContent, Url::to(['categories/productedit', 'pID'=> $products['parent_products_id']]));
                        $productMarkers .= '<i class="product_list_marker product_list_marker__child_of">' . TEXT_CHILD_PRODUCT . '<div class="product_list_marker__pophover">'.$parent_product_name.'</div></i> ';
                    }elseif($products['sub_product_children_count']>0){
                        $childrenProducts = \yii\helpers\ArrayHelper::map(\common\models\Products::find()->where(['parent_products_id'=>$products['products_id']])->select(['products_id','products_model'])->asArray()->all(),'products_id','products_model');
                        foreach ($childrenProducts as $childrenProductId=>$childrenProductModel){
                            $childrenProducts[$childrenProductId] = '<div>'.TEXT_CHILD_PRODUCT.' '.Html::a(($childrenProductModel?"{$childrenProductModel} ":'').\common\helpers\Product::get_backend_products_name($childrenProductId),Url::to(['categories/productedit', 'pID'=> $childrenProductId])).'</div>';
                        }
                        $productMarkers .= '<i class="product_list_marker product_list_marker__parent_of">' . TEXT_PARENT_PRODUCT . '<div class="product_list_marker__pophover">'.implode('',$childrenProducts).'</div></i> ';
                    }
                }
                if ( $products['products_groups_id'] ){
                    $product_categories_string = '<i class="next-row category_path product_list__products_group">'.\common\helpers\Product::products_groups_name($products['products_groups_id']).'</i>'.$product_categories_string;
                }

                $responseList[] = array(
                    '<input type="checkbox" class="'. ($productsQty < CATALOG_SPEED_UP_DESIGN ? 'uniform' : '' ) .' js-cat-batch" name="batch[]" value="p_'.$products['products_id'].'">',
                    '<div class="handle_cat_list' . ($products['products_status'] == 1 ? '' : ' dis_prod') . '">' .
                    '<span class="handle"><i class="icon-hand-paper-o"></i></span>' .
                    '<div class="prod_name prod_name_double" data-click-double="' . tep_href_link(FILENAME_CATEGORIES . '/productedit', 'pID=' . $products['products_id']) . '">' .
                    (!empty($image) ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') .
                    '<table class="wrapper"><tr><td><span class="prodNameC">' . $products['products_name'] . $product_categories_string . '</span></td></tr></table>' .
                    '<span class="prodIDsC"><span title="' . \common\helpers\Output::output_string($products['products_model']) . '">' . ($products['products_model'] ? TEXT_SKU . ': ' . $products['products_model'] . '<br>' : '') . TEXT_PRODUCTS_QUANTITY_INFO . ': ' . \common\helpers\Product::getVirtualItemQuantity($products['products_id'], $products['products_quantity']) . '<br>' . TABLE_HEADING_ID . ': ' . $products['products_id'] . '</span>' . $productMarkers.'</span>' .
                    '<input class="cell_identify" type="hidden" value="' . $products['products_id'] . '">' .
                    '<input class="cell_type" type="hidden" value="product" data-id="products-' . $products['products_id'] . '">' .
                    '</div>' .
                    '</div>',
                    //$products['products_status']
                    ($products['products_status'] == 1 ? '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="'. ($productsQty < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' ) .'" checked="checked">' : '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="'. ($productsQty < CATALOG_SPEED_UP_DESIGN ? 'check_on_off' : 'check_on_off_check' ) .'">')
                );

                if ($ext = \common\helpers\Acl::checkExtension('AutomaticallyStatus', 'allowed')) {
                    if ($ext::allowed() && !$products['manual_control_status']) {
                        $responseList[count($responseList) - 1]['DT_RowClass'] = 'check_status_auto';
                    }
                }

                //$categories_query_numrows++;
            }
        }
        if (tep_not_null($products_query_raw ?? null))
            $_session->set('products_query_raw', $products_query_raw);
        $response = [
            'draw' => $draw,
            'recordsTotal' => $categories_query_numrows,
            'recordsFiltered' => $categories_query_numrows,
            'data' => $responseList,
            'categories' => $categoriesQty,
            'products' => $productsQty,
            'breadcrumb' => $list_bread_crumb,
        ];
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;
    }

    public function actionCategoryactions() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        global $navigation;
        if (is_object($navigation) && method_exists($navigation, 'set_snapshot')) {
            $navigation->set_snapshot(['page' => 'categories', 'get' => Yii::$app->request->post('get')], true);
        }

        $this->layout = false;

        $categories_id = Yii::$app->request->post('categories_id', 0);

        if ($categories_id>0) {
          $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_status, c.last_xml_export from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $categories_id . "' and c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int) $languages_id . "'");
          $categories = tep_db_fetch_array($categories_query);
          $category_childs = array('childs_count' => \common\helpers\Categories::childs_in_category_count($categories['categories_id']));
          $category_products = array('products_count' => \common\helpers\Categories::products_in_category_count($categories['categories_id']));

          $cInfo_array = array_merge($categories, $category_childs, $category_products);
          $cInfo = new \objectInfo($cInfo_array);

          $cInfo->hasGrouppedProducts = Categories::hasGrouppedProducts($categories_id, true);

        $cInfo->eventInfo = null;
        if ($es = \common\helpers\Extensions::isAllowed('EventSystem')) {
            $cInfo->eventInfo = $es::event()->exec('getEventInformation',[$categories_id]);
        }
          return $this->render('categoryactions.tpl', ['cInfo' => $cInfo]);
        }
    }

    public function actionProductactions() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $currencies = Yii::$container->get('currencies');

        global $navigation;
        if (is_object($navigation) && method_exists($navigation, 'set_snapshot')) {
            $navigation->set_snapshot(['page' => 'categories', 'get' => Yii::$app->request->post('get')], true);
        }

        $this->layout = false;

        $categories_id = intval(Yii::$app->request->post('categories_id',0));
        $products_id = Yii::$app->request->post('products_id');
        $p = \common\models\Products::find()
            ->andWhere(['products_id' => (int) $products_id])
            ->with('description')
            ->with('platforms')
            ->with('localRating');
        if (tep_session_is_registered('login_vendor')) {
          global $login_id;
          $p->andWhere(['vendor_id' => $login_id]);
        }

        $pInfo = $p->one();
        if ($pInfo->parent_products_id) {
            $pInfo->products_quantity = \common\helpers\Product::get_products_info($pInfo->parent_products_id,'products_quantity');
        }

        $image = \common\classes\Images::getImage($pInfo->products_id, 'Small');

        echo '<div class="prod_box_img">' . $image . '</div>';
        echo '<div class="or_box_head prod_head_box">' . $pInfo->description->products_name . '</div>';
        echo '<div class="row_or_wrapp">';
        echo '<div class="row_or">
                    <div>' . TEXT_DATE_ADDED . '</div>
                    <div>' . \common\helpers\Date::date_short($pInfo->products_date_added) . '</div>
             </div>';
        if (tep_not_null($pInfo->products_last_modified)) {
            echo '<div class="row_or">
                <div>' . TEXT_LAST_MODIFIED . '</div>
                <div>' . \common\helpers\Date::date_short($pInfo->products_last_modified) . '</div>
         </div>';
        }
        if (date('Y-m-d') < $pInfo->products_date_available) {
            echo '<div class="row_or">
                <div>' . TEXT_DATE_AVAILABLE . '</div>
                <div>' . \common\helpers\Date::date_short($pInfo->products_date_available) . '</div>
         </div>';
        }

        if (USE_MARKET_PRICES == 'True') {
            echo '<div class="row_or">
                    <div>' . TEXT_PRODUCTS_PRICE_INFO . '</div>
                    <div>' . $currencies->format(\common\helpers\Product::get_products_price($pInfo->products_id, 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'])) . '</div>
             </div>';
            echo '<div class="row_or">
                   <div>' . TEXT_PRODUCTS_QUANTITY_INFO . '</div>
                   <div>' . \common\helpers\Product::getVirtualItemQuantity($pInfo->products_id, $pInfo->products_quantity) . '</div>
            </div>';
        } else {
            echo '<div class="row_or">
                    <div>' . TEXT_PRODUCTS_PRICE_INFO . '</div>
                    <div>' . $currencies->format($pInfo->products_price) . '</div>
             </div>';
            echo '<div class="row_or">
                    <div>' . TEXT_PRODUCTS_QUANTITY_INFO . '</div>
                    <div>' . \common\helpers\Product::getVirtualItemQuantity($pInfo->products_id, $pInfo->products_quantity) . '</div>
             </div>';
        }
        echo '<div class="row_or">
                    <div>' . TEXT_PRODUCTS_AVERAGE_RATING . '</div>
                    <div>' . number_format($pInfo->localRating[0]->average_rating ?? 0, 2) . '%</div>
             </div>';
        echo '<div class="row_or">
                    <div>' . TEXT_SORT_ORDER . '</div>
                    <div>' . $pInfo->sort_order . '</div>
             </div>';
        echo '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT'])) {
            echo '<a class="btn btn-primary btn-process-order btn-edit" href="' . tep_href_link(FILENAME_CATEGORIES . '/productedit', 'pID=' . $pInfo->products_id) . '">' . IMAGE_EDIT . '</a>';
        }
        if ( defined('LISTING_SUB_PRODUCT') && LISTING_SUB_PRODUCT=='True' ) {
            if (!$pInfo->parent_products_id && \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT'])) {
                echo '<a class="btn btn-primary btn-process-order btn-new" href="' . Yii::$app->urlManager->createUrl(['categories/productedit', 'category_id' => $categories_id, 'parentID' => $pInfo->products_id]) . '">' . BUTTON_CREATE_LISTING_PRODUCT . '</a>';
                if ( $pInfo->sub_product_children_count==0 ) {
                    echo '<a class="btn btn-primary btn-process-order btn-new actionPopup" href="' . Yii::$app->urlManager->createUrl(['categories/listing-attach', 'product_id' => $pInfo->products_id]) . '">' . BUTTON_ATTACH_TO_PARENT_LISTING_PRODUCT . '</a>';
                }
            }elseif($pInfo->parent_products_id && \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT'])) {
                echo '<a class="btn btn-primary btn-process-order btn-new actionPopup" href="' . Yii::$app->urlManager->createUrl(['categories/listing-detach', 'product_id' => $pInfo->products_id]) . '">' . BUTTON_DETACH_LISTING_PRODUCT . '</a>';
            }
        }
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_DELETE'])) {
            echo '<button class="btn btn-delete btn-no-margin" onclick="confirmDeleteProduct(' . $pInfo->products_id . ')">' . IMAGE_DELETE . '</button>';
        }
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_MOVE'])) {
            echo '<button class="btn btn-move" onclick="confirmMoveProduct(' . $pInfo->products_id . ')">' . IMAGE_MOVE . '</button>';
        }
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_COPY_TO'])) {
            echo '<button class="btn btn-copy btn-no-margin" onclick="confirmCopyProduct(' . $pInfo->products_id . ')">' . IMAGE_COPY_TO . '</button>';
        }

        if (!tep_session_is_registered('login_vendor')) {
            if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_COPY_ATTRIBUTES'])) {
                echo '<button class="btn" onclick="confirmCopyProductAttr(' . $pInfo->products_id . ')">' . IMAGE_COPY_ATTRIBUTES . '</button>';
            }
            /* if ($pID) {
              echo '<div>' . ATTRIBUTES_NAMES_HELPER . '</div>';
              } */
        }

/*EP Sync now*/
        $nsBlock = '';
        if (\common\helpers\Acl::checkExtensionAllowed('NetSuite') && \common\extensions\NetSuite\helpers\NetSuiteHelper::anyConfigured()) {
          $r = tep_db_query("select local_products_id, remote_products_id, ld.directory_id, ld.directory  "
              . " from ep_directories ld left join ep_holbi_soap_link_products lp on ld.directory_id=lp.ep_directory_id and local_products_id='" . (int)$pInfo->products_id ."'"
              . " where ld.directory_config like '%NetSuiteLink%'  and ld.directory_type='datasource' "
              . " " );
          while ($d = tep_db_fetch_array($r)) {
            $nsBlock = '<div class="ep-sync ep-sync-ns"> <div class="ns-info">' . $d['directory'] . ' ' . ((int)$d['remote_products_id']>0?'  <a class="sync" target="_blank" href="https://system.netsuite.com/app/common/item/item.nl?id=' . $d['remote_products_id'] . '">' . TEXT_VIEW_NS . '</a>':'') . '</div><div class="ns-buttons"><button class="btn btn-sync btn-no-margin" onclick="linkNS(\'' . $d['remote_products_id'] . '\',' . (int)$pInfo->products_id . ',' . (int)$d['directory_id'] . ')">' . TEXT_UPDATE_EXTERNAL_ID . '</button>'.  ((int)$d['remote_products_id']>0?' <button class="btn btn-sync btn-no-margin" onclick="confirmSyncNow(' . $d['remote_products_id'] . ',' . (int)$pInfo->products_id . ',' . $d['directory_id'] . ')">' . TEXT_SYNC_NOW . '</button>':'') . '</div></div>';
          }

          if (\common\helpers\Acl::rule(['BOX_HEADING_CATALOG', 'BOX_CATALOG_EASYPOPULATE'])) {
              echo $nsBlock;
          }
        }
/*EP Sync now*/


/* @var $ext \common\extensions\ProductEasyView\ProductEasyView */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductEasyView', 'allowed')) {
            $ext::adminAdctionProduct($pInfo->products_id);
        } else {
            echo '<a class="btn btn-primary btn-no-margin btn-easy-view dis_module" disabled href="javascript:void(0)">' . IMAGE_EASY_VIEW . '</a>';
        }

        $choosePlatformPopup = '';
        $singlePlatformLink = '';
        $platformsAssigned = 0;
        foreach (\common\classes\platform::getList(false) as $frontend) {
            if ($pInfo->platforms && isset($pInfo->platforms[$frontend['id']])) {
              $platformsAssigned++;
              $seo_url = \common\helpers\Product::getSeoName((int)$pInfo->products_id, (int)$languages_id, $frontend['id']);
              if ($seo_url) {
                $singlePlatformLink = 'http://' . $frontend['platform_url'] . '/' . $seo_url ;
                $choosePlatformPopup .= '<p><a href="http://' . $frontend['platform_url'] . '/' . $seo_url . '" target="_blank">' . $frontend['text'] . '</a></p>';
              } else {
                $singlePlatformLink = 'http://' . $frontend['platform_url'] . '/catalog/product?products_id=' . $pInfo->products_id;
                $choosePlatformPopup .= '<p><a href="http://' . $frontend['platform_url'] . '/catalog/product?products_id=' . $pInfo->products_id . '" target="_blank">' . $frontend['text'] . '</a></p>';
              }
            }
        }
        if ($singlePlatformLink != '' && $platformsAssigned>1) {
          echo '<a href="#choose-frontend" class="btn btn-primary btn-choose-frontend">' . TEXT_PREVIEW_ON_SITE . '</a>';
          echo '<div id="choose-frontend" style="display: none">
            <div class="popup-heading">Choose frontend</div>
            <div class="popup-content frontend-links">
          ' . $choosePlatformPopup . '
            </div>
            <div class="noti-btn">
              <div><button class="btn btn-cancel">Cancel</button></div>
            </div>
            <script type="text/javascript">
              (function($){
                $(function(){
                  $(\'.popup-box-wrap .frontend-links a\').on(\'click\', function(){
                    $(\'.popup-box-wrap\').remove()
                  });
                  $(\'.btn-choose-frontend\').popUp();
                })
              })(jQuery)
            </script>
          </div>';
        } elseif ($singlePlatformLink != '' && $pInfo->platforms && $platformsAssigned==1) {
          echo '<a href="' . $singlePlatformLink . '" target="_blank" class="btn btn-primary">' . TEXT_PREVIEW_ON_SITE . '</a>';
        }

        echo '<a class="btn btn-primary btn-process-order btn-new actionPopup" href="#print-product-label">Product label</a>';
        echo '<div id="print-product-label" style="display: none">
            <div class="popup-heading">Print product label</div>
            <form method="get" target="_blank" action="'.Yii::$app->urlManager->createUrl(['categories/product-label']).'">
            <div class="popup-content">
            Print '.\common\helpers\Html::textInput('count',1, ['id'=>'countLabelCopies', 'style'=>'width: 60px;display: inline-block;vertical-align: middle;']).' copies "'.$pInfo->products_model.'".
            '.\common\helpers\Html::hiddenInput('model',$pInfo->products_model).'
            </div>
            <div class="noti-btn">
              <div><button class="btn btn-cancel" type="button">Cancel</button></div>
              <div><button class="btn btn-primary" type="submit" onclick="setTimeout(function(){closePopup();},10);">Print</button></div>
            </div>
            </form>

          </div>';

        echo '</div>';
    }

    public function actionSortProducts() {
      \common\helpers\Translation::init('admin/categories');
      $ret = [];
      $this->layout = false;
      $categories_id = (int)Yii::$app->request->post('categories_id', 0);
      $recursively = (int)Yii::$app->request->post('recursively', 0);

      if ($categories_id>0) {
        if ($recursively) {
          $cats = \common\models\Categories::findOne($categories_id)->getDescendants(null, true)
          ->select('categories_id')
          ->orderBy([])->asArray()->column();
        } else {
          $cats = [$categories_id];
        }
        if (is_array($cats)) {
          foreach ($cats as $cat) {
            \common\helpers\Product::inCategorySortReindexGroupped($cat);
          }
        }
        $ret = ['status'=>"OK"];
      }

      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      Yii::$app->response->data = $ret;

    }

    public function actionConfirmCategoryMove() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $categories_id = Yii::$app->request->post('categories_id');

        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_status, c.last_xml_export from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $categories_id . "' and c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int) $languages_id . "'");
        $categories = tep_db_fetch_array($categories_query);
        $category_childs = array('childs_count' => \common\helpers\Categories::childs_in_category_count($categories['categories_id']));
        $category_products = array('products_count' => \common\helpers\Categories::products_in_category_count($categories['categories_id']));

        $cInfo_array = array_merge($categories, $category_childs, $category_products);
        $cInfo = new \objectInfo($cInfo_array);

        $categoryTree = \common\helpers\Categories::get_category_tree(0, '', $cInfo->categories_id);

        return $this->render('confirmcategorymove.tpl', ['cInfo' => $cInfo, 'categoryTree' => $categoryTree]);
    }

    public function actionCategoryMove() {
        $this->layout = false;
        if (\common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_MOVE'])) {
            $categories_id = Yii::$app->request->post('categories_id');
            $parent_id = Yii::$app->request->post('move_to_category_id');
            if ($categories_id != $parent_id && !in_array($categories_id, \common\helpers\Categories::getCategoryParentsIds($parent_id))) {
                tep_db_query("update " . TABLE_CATEGORIES . " set parent_id = '" . (int) $parent_id . "' where categories_id = '" . (int) $categories_id . "'");
            }
            \common\helpers\Categories::update_categories();
        }
        $this->view->categoriesTree = $this->getCategoryTree();
        if ($categories_id>0) {
          $this->view->categoriesOpenedTree = \common\helpers\Categories::getCategoryParentsIds($categories_id);
          \common\components\CategoriesCache::getCPC()::invalidateCategories($categories_id);
        } else {
          $this->view->categoriesOpenedTree = [];
        }
        $this->view->categoriesClosedTree = array_diff(array_map('intval', explode('|', \Yii::$app->session->get('closed_data'))), $this->view->categoriesOpenedTree);

        $collapsed = $this->defaultCollapsed;
        return $this->render('cat_main_box', ['directOutput' => true, 'collapsed' => $collapsed]);
    }

    public function actionConfirmcategorydelete() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        if (Yii::$app->request->isPost) {
            $categories_id = Yii::$app->request->post('categories_id');
        } else {
            $categories_id = Yii::$app->request->get('categories_id');
        }

        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_status, c.last_xml_export from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $categories_id . "' and c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int) $languages_id . "'");
        $categories = tep_db_fetch_array($categories_query);
        $category_childs = array('childs_count' => \common\helpers\Categories::childs_in_category_count($categories['categories_id']));
        $category_products = array('products_count' => \common\helpers\Categories::products_in_category_count($categories['categories_id']));

        $cInfo_array = array_merge($categories, $category_childs, $category_products);
        $cInfo = new \objectInfo($cInfo_array);

        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_CATEGORY . '</div>';

        echo tep_draw_form('categories', FILENAME_CATEGORIES, \common\helpers\Output::get_all_get_params(array('action')) . 'action=delete_category_confirm', 'post', 'id="categories_edit" onSubmit="return deleteCategory();"');
        echo '<div class="col_title">' . TEXT_DELETE_CATEGORY_INTRO . '</div>';
        echo '<div class="col_desc">' . $cInfo->categories_name . '</div>';
        if ($cInfo->childs_count > 0)
            echo '<div class="col_desc">' . sprintf(TEXT_DELETE_WARNING_CHILDS, $cInfo->childs_count) . '</div>';
        if ($cInfo->products_count > 0)
            echo '<div class="col_desc">' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $cInfo->products_count) . '</div>';
        ?>
        <div class="btn-toolbar btn-toolbar-order">
            <button class="btn btn-delete btn-no-margin"><?php echo IMAGE_DELETE; ?></button><button class="btn btn-cancel" onClick="return resetStatement()"><?php echo IMAGE_CANCEL; ?></button>
            <?php
            /* echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_DELETE . '" >';
              echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">'; */

            echo tep_draw_hidden_field('categories_id', $cInfo->categories_id);
            ?>
        </div>
        </form>
        <?php
    }

    public function actionConfirmProductMove() {
        global $login_id;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;
        $languages_id = \Yii::$app->settings->get('languages_id');
        $products_id = Yii::$app->request->post('products_id');

        $products_query = tep_db_query("select p.products_id, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model, p.sort_order, p.last_xml_export from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.language_id = '" . (int) $languages_id . "'  and pd.platform_id = '" .intval(\common\classes\platform::defaultId()). "' and p.products_id = '" . (int) $products_id . "'");
        $products = tep_db_fetch_array($products_query);

        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int) $products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new \objectInfo($pInfo_array);

        $pInfo->categories_id = Yii::$app->request->post('categories_id');

        // fix for brands tab
        $oRelation = \common\models\Products2Categories::find()
            ->where(['products_id' => $pInfo->products_id])
            ->limit(1)
            ->one();
        if(isset($oRelation->categories_id) && $oRelation->categories_id != 0 && $pInfo->categories_id == 0)
        {
            $pInfo->categories_id = $oRelation->categories_id;
        }
        $categoryTree = \common\helpers\Categories::get_category_tree();

        return $this->render('confirmproductmove.tpl', ['pInfo' => $pInfo, 'categoryTree' => $categoryTree]);
    }

    public function actionProductMove() {
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_MOVE'])) {
            // move_to_category_id products_id categories_id
            $products_id = Yii::$app->request->post('products_id');
            $new_parent_id = Yii::$app->request->post('move_to_category_id');
            $current_category_id = Yii::$app->request->post('categories_id');

            $duplicate_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $new_parent_id . "'");
            $duplicate_check = tep_db_fetch_array($duplicate_check_query);
            if ($duplicate_check['total'] < 1)
                tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = '" . (int) $new_parent_id . "' where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $current_category_id . "'");

            \common\components\CategoriesCache::getCPC()::invalidateCategories([(int) $new_parent_id, (int) $current_category_id]);

            if (USE_CACHE == 'true') {
                \common\helpers\System::reset_cache_block('categories');
                \common\helpers\System::reset_cache_block('also_purchased');
            }
        }
    }

    public function actionConfirmProductAttrCopy() {
        global $login_id;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;
        $languages_id = \Yii::$app->settings->get('languages_id');
        $products_id = Yii::$app->request->post('products_id');

        $products_query = tep_db_query("select p.products_id, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model, p.sort_order, p.last_xml_export from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.language_id = '" . (int) $languages_id . "'  and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and p.products_id = '" . (int) $products_id . "'");
        $products = tep_db_fetch_array($products_query);

        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int) $products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new \objectInfo($pInfo_array);

        return $this->render('confirmproductattrcopy.tpl', ['pInfo' => $pInfo]);
    }

    public function actionProductAttrCopy() {
        $ret = ['ok' => 1];
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_COPY_ATTRIBUTES'])) {
            $products_id = (int)Yii::$app->request->post('products_id', 0);
            $copy_to_products_id = (int)Yii::$app->request->post('copy_to_products_id', 0);
            if (empty($copy_to_products_id)) {//allow id in the search field
                $copy_to_products_id = (int)Yii::$app->request->post('products_name', 0);
            }
            $skip_duplicates = (bool)Yii::$app->request->post('copy_attributes_duplicates_skipped', false);
            $delete_first = (bool)Yii::$app->request->post('copy_attributes_delete_first', false);
            //\common\helpers\Attributes::copy_products_attributes($products_id, $copy_to_products_id);
            try {
                \common\helpers\Attributes::copyProductsAttributes($products_id, $copy_to_products_id, $delete_first, $skip_duplicates);
            } catch (\Exception $ex) {
                \Yii::warning(" #### " .print_r($ex->getMessage(), true), 'TLDEBUG');
                $ret = ['message' => $ex->getMessage()];
            }

        } else {
            $ret = ['message' => TEXT_ERROR_INSUFFICIENT_PERMISSIONS];
        }
        $this->layout = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $ret;
    }

    public function actionNsSync() {
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_COPY_ATTRIBUTES'])) {
            $products_id = Yii::$app->request->post('r_id');
            $directory_id = Yii::$app->request->post('d_id');
            $test  = tep_db_query("select remote_id from ep_holbi_soap_remote_products_queue where ep_directory_id='" . (int)$directory_id . "' and remote_id='" . (int)$products_id . "' limit 2");
            if (tep_db_num_rows($test)==0) {
              $sql_data = [
                'ep_directory_id' => $directory_id,
                'remote_id' => $products_id
              ];
              tep_db_perform("ep_holbi_soap_remote_products_queue", $sql_data);

              ob_start();
              $epDirectory = \backend\models\EP\Directory::loadById($directory_id);
              $providerName = $epDirectory->directory_config[0]['file_format'];
              $jobId = $epDirectory->touchImportJob($providerName . '_DownloadProducts_'.date('YmdHis'),'configured', $providerName . '\\DownloadProducts');
              $exportOrderJob = \backend\models\EP\Job::loadById($jobId);

              if ( $exportOrderJob ) {
                if ( !is_array($exportOrderJob->job_configure) ) $exportOrderJob->job_configure = [];
                $exportOrderJob->job_configure['oneTimeJob'] = true;
                $exportOrderJob->saveConfigureState();
                $exportOrderJob->setJobStartTime(time());
                $messages = new Messages([
                    'job_id' => $jobId,
                    'output' => 'db',
                ]);
                ob_start();
                try {
                    $messages->info('Run import manually');
                    $exportOrderJob->run($messages);

                    $ret['status'] = 'OK';
                    $ret['messages'] = $messages->getMessages();
                }catch (\Exception $ex){
                    $ret['messages'][] = $ex->getMessage();
                }
                ob_end_flush();
                $exportOrderJob->jobFinished();
              }
              ob_get_clean();
              //$ret = ['status'=>"OK"];
            } else {
              $ret = ['status'=>"OK", 'inqueue' => 1];
            }
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = $ret;

        }
    }

    public function actionNsSyncUpdateId() {
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_COPY_ATTRIBUTES'])) {
            $products_id = Yii::$app->request->post('r_id', 0);
            $l_id = Yii::$app->request->post('l_id', 0);
            $n_id = Yii::$app->request->post('n_id', 0);
            $directory_id = Yii::$app->request->post('d_id', 0);
            if ($products_id>0) {
              tep_db_query("delete from ep_holbi_soap_link_products where ep_directory_id='" . (int)$directory_id . "' and remote_products_id='" . (int)$products_id . "' and local_products_id='" . (int)$l_id . "' ");
            }
            if ($n_id>0) {
              $sql_data = [
                'ep_directory_id' => $directory_id,
                'local_products_id' => $l_id,
                'remote_products_id' => $n_id
              ];
              tep_db_perform("ep_holbi_soap_link_products", $sql_data);
            }
            $ret = ['status'=>"OK"];
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = $ret;

        }
    }

    public function actionConfirmProductCopy() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = Yii::$app->request->post('products_id');

        $products_query = tep_db_query("select p.products_id, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model, p.sort_order, p.last_xml_export from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.language_id = '" . (int) $languages_id . "'  and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and p.products_id = '" . (int) $products_id . "'");
        $products = tep_db_fetch_array($products_query);

        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int) $products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new \objectInfo($pInfo_array);

        $pInfo->categories_id = Yii::$app->request->post('categories_id');

        return $this->render('confirmproductcopy.tpl', ['pInfo' => $pInfo]);
    }

    public function actionProductCopy() {
        $messageStack = \Yii::$container->get('message_stack');
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_COPY_TO'])) {
            if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
                $products_id = tep_db_prepare_input($_POST['products_id']);
                $categories_id = tep_db_prepare_input($_POST['categories_id']);

                if ($_POST['copy_as'] == 'link') {
                    $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $categories_id . "'");
                    $check = tep_db_fetch_array($check_query);
                    if ($check['total'] < '1') {
                        tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $products_id . "', '" . (int) $categories_id . "')");
                    } else {
                        $messageStack->add(ERROR_CANNOT_LINK_TO_SAME_CATEGORY);
                    }
                } elseif ($_POST['copy_as'] == 'duplicate') {
                    $copyCategories = (int)\Yii::$app->request->post('copy_categories', 0);
                    $copyAttributes = (bool)\Yii::$app->request->post('copy_attributes', false);
                    \common\helpers\Product::duplicate($products_id, $categories_id, $copyAttributes, $copyCategories);
                }

                if (defined('USE_CACHE') && USE_CACHE == 'true') {
                    \common\helpers\System::reset_cache_block('categories');
                    \common\helpers\System::reset_cache_block('also_purchased');
                }
            }
        }
    }

    public function actionConfirmproductdelete() {

        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = Yii::$app->request->post('products_id');

        $products_query = tep_db_query("select p.products_id, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model, p.sort_order, p.last_xml_export from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.language_id = '" . (int) $languages_id . "'  and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and p.products_id = '" . (int) $products_id . "'");
        $products = tep_db_fetch_array($products_query);

        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int) $products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new \objectInfo($pInfo_array);

        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</div>';
        echo tep_draw_form('products', FILENAME_CATEGORIES, \common\helpers\Output::get_all_get_params(array('action')) . 'action=delete_product_confirm', 'post', 'id="products_edit" onSubmit="return deleteProduct();"');
        echo '<div class="col_title">' . TEXT_DELETE_PRODUCT_INTRO . '</div>';
        echo '<div class="col_desc"><b>' . $pInfo->products_name . '</b></div>';
        $product_categories_string = '';
        $product_categories = \common\helpers\Categories::generate_category_path($pInfo->products_id, 'product');
        for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
            $category_path = '';
            for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                $category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
            }
            $category_path = substr($category_path, 0, -16);
            $product_categories_string .= tep_draw_checkbox_field('product_categories[]', $product_categories[$i][sizeof($product_categories[$i]) - 1]['id'], true) . '&nbsp;' . $category_path . '<br>';
        }
        $product_categories_string = substr($product_categories_string, 0, -4);
        echo '<div class="col_desc">' . $product_categories_string . '</div>';
        ?>
        <p class="btn-toolbar btn-toolbar-order">
            <?php
            echo '<button class="btn btn-delete btn-no-margin"><span>' . IMAGE_DELETE . '</span></button>';
            echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

            echo tep_draw_hidden_field('products_id', $pInfo->products_id);
            ?>
        </p>
        </form>
        <?php
    }
/*
 * remove category with all subcategories and products. O_O - tooo danger, no warning.
 */
    public function actionCategorydelete() {
      $this->layout = false;
      if (\common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_DELETE'])) {
        if (isset($_POST['categories_id']) && $_POST['categories_id'] > 0) {
          $categories_id = tep_db_prepare_input($_POST['categories_id']);
          $catList = \common\helpers\Categories::getCategoryParentsIds($categories_id);

          if (\common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
            $logger = new \common\extensions\ReportChangesHistory\classes\Logger();
            $beforeObject = new \common\api\Classes\Category();
            $beforeObject->load($categories_id);
            $logger->setBeforeObject($beforeObject);
            unset($beforeObject);
          }

//2do if _left and _right is updated everywhere - replace wth 2 queries.
          $categories = \common\helpers\Categories::get_category_tree($categories_id, '', '0', '', true);
          $products = array();
          $products_delete = array();

          for ($i = 0, $n = sizeof($categories); $i < $n; $i++) {
            $product_ids_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int) $categories[$i]['id'] . "'");

            while ($product_ids = tep_db_fetch_array($product_ids_query)) {
              $products[$product_ids['products_id']]['categories'][] = $categories[$i]['id'];
            }
          }

          foreach ($products as $key => $value) {
            $category_ids = '';

            for ($i = 0, $n = sizeof($value['categories']); $i < $n; $i++) {
              $category_ids .= "'" . (int) $value['categories'][$i] . "', ";
            }
            $category_ids = substr($category_ids, 0, -2);

            $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $key . "' and categories_id not in (" . $category_ids . ")");
            $check = tep_db_fetch_array($check_query);
            if ($check['total'] < '1') {
              $products_delete[$key] = $key;
            }
          }

          // removing categories can be a lengthy process
          set_time_limit(0);
          $sdn = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed');
          for ($i = 0, $n = sizeof($categories); $i < $n; $i++) {
            \common\helpers\Categories::remove_category($categories[$i]['id'], false);
            if ($sdn) {
              $sdn::deleteCategoryLinks($categories[$i]['id']);
            }
          }

          foreach ($products_delete as $key) {
            \common\helpers\Product::remove_product($key);
            if ($sdn) {
              $sdn::deleteProductLinks($key);
            }
          }
          \common\components\CategoriesCache::getCPC()::invalidateCategories($catList);

        }

        if (USE_CACHE == 'true') {
          \common\helpers\System::reset_cache_block('categories');
          \common\helpers\System::reset_cache_block('also_purchased');
        }
        //It's not required as branch is deleted completely. Left, right are not concequent, but correct. It's very slow operation.
        //\common\helpers\Categories::update_categories();
        //
        }

        if (isset($logger) && \common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
            $afterObject = new \common\api\Classes\Category();
            $afterObject->load(0);
            $logger->setAfterObject($afterObject);
            unset($afterObject);
            $logger->run();
        }

      $this->view->categoriesTree = $this->getCategoryTree();
      $this->view->categoriesOpenedTree = [];
      $this->view->categoriesClosedTree = array_map('intval', explode('|', \Yii::$app->session->get('closed_data')));

      $collapsed = $this->defaultCollapsed;
      return $this->render('cat_main_box', ['directOutput' => true, 'collapsed' => $collapsed]);
    }

    public function actionProductdelete() {
        $this->layout = false;
        if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_DELETE'])) {
            $product_id = Yii::$app->request->post('products_id');
            $listCatId = \common\helpers\Product::getCategoriesIdListWithParents($product_id);

            if (\common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
                $logger = new \common\extensions\ReportChangesHistory\classes\Logger();
                $beforeObject = new \common\api\Classes\Product();
                $beforeObject->load($product_id);
                $logger->setBeforeObject($beforeObject);
                unset($beforeObject);
            }

            $product_categories_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "'");
            $count_product_categories = tep_db_fetch_array($product_categories_query);

            $remove_complete = true;
            if (isset($_POST['product_categories']) && is_array($_POST['product_categories'])) {
                $product_categories = $_POST['product_categories'];
                if ($count_product_categories['total']!=count($product_categories)) {
                    for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                        tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "' and categories_id = '" . (int) $product_categories[$i] . "'");
                    }
                    $remove_complete = false;
                }
            }
            $product_categories_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "'");
            $product_categories = tep_db_fetch_array($product_categories_query);
            if ($remove_complete || $product_categories['total'] == '0') {
                \common\helpers\Product::remove_product($product_id);
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                   $ext::deleteProductLinks($product_id);
                }
            }
            if (defined('USE_CACHE') && USE_CACHE == 'true') {
                \common\helpers\System::reset_cache_block('categories');
                \common\helpers\System::reset_cache_block('also_purchased');
            }

            if (isset($logger) && \common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
                $afterObject = new \common\api\Classes\Product();
                $afterObject->load(0);
                $logger->setAfterObject($afterObject);
                unset($afterObject);
                $logger->run();
            }
            \common\components\CategoriesCache::getCPC()::invalidateCategories($listCatId);
        }
    }

    public function actionProductedit() {
        if (false === \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT'])) {
            $this->redirect(\yii\helpers\Url::toRoute('categories/'));
        }

        $languages_id = \Yii::$app->settings->get('languages_id');

        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
        $selectedDepartmentId =  (int)Yii::$app->request->get('department_id', 0);
        } else {
            $selectedDepartmentId = 0;
        }
        $_session = Yii::$app->session;

        $service = new \common\services\SupplierService();
        Yii::configure($service, [
            'allow_change_status' => true,
            'allow_change_default' => true,
            'allow_change_surcharge' => true,
            'allow_change_margin' => true,
            'allow_change_price_formula' => true,
            'allow_change_auth' => true,
        ]);

        \common\helpers\Translation::init('admin/categories');

        // search in top

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="$(\'#save_product_form\').trigger(\'submit\')">' . IMAGE_SAVE . '</span>';

        $this->topButtons[] = '<div class="btn-quick-search" style="float: right;">' .
            \common\helpers\Html::beginForm( \Yii::$app->urlManager->createUrl('categories'), 'get' ) .
            \common\helpers\Html::hiddenInput('autoEdit', 1) .
            \common\helpers\Html::beginTag('div',['class' => 'box-head-search']) .
            \common\helpers\Html::textInput('search') .
            \common\helpers\Html::button('', ['class' => 'edit-product-quick-search', 'onclick' => 'this.form.submit();']) .
            \common\helpers\Html::endTag('div') .
            \common\helpers\Html::endForm() .
            '</div>';

        if (\common\helpers\Acl::checkExtensionAllowed('ProductBundles') && \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT', 'TAB_BUNDLES'])) {
          $editProductBundleSwitcher = true;
        } else {
          $editProductBundleSwitcher = false;
        }

        $currencies = Yii::$container->get('currencies');
        $products_id = (int) Yii::$app->request->get('pID', 0); //products_id
        $languages = \common\helpers\Language::get_languages();

        $in_category_id = intval(Yii::$app->request->get('category_id', 0));

        $isBundle = false;
        if ($products_id > 0) {
          $productRecord = \common\helpers\Product::getRecord($products_id, true);
          $isBundle = (count(\common\helpers\Product::getChildArray($productRecord)) > 0);
          unset($productRecord);
          $product_query = tep_db_query("select p.*, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name, pd.products_viewed, m.manufacturers_name, pf.status as featured_status, pf.expires_date as featured_expires_date, p.products_sets_discount from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id='".intval(\common\classes\platform::defaultId())."' left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id=m.manufacturers_id left join " . TABLE_FEATURED . " pf on p.products_id = pf.products_id where p.products_id = '" . (int) $products_id . "' ");
          $product = tep_db_fetch_array($product_query);
          if (empty($product)) {
              $products_id = 0;
          }else{
              if (empty($product['products_name'])) {
                  $product['products_name'] = \common\helpers\Product::get_products_name($product['products_id']);
              }
              if ($product['is_bundle'] > 0) { // allow to unbundle even if ProductsBundles is not installed
                  $editProductBundleSwitcher = true;
              }
          }
          $pInfo = new \objectInfo($product);
        } //else {
        if ($products_id <= 0) {
            $parentID = intval(Yii::$app->request->get('parentID',0));
            if ( $parentID && $parentProductModel = \common\models\Products::findOne($parentID)) {
                $parent_data = $parentProductModel->getAttributes(\common\helpers\Product::subProductMainAttributesShare());
                $parent_data['manufacturers_name'] = $parentProductModel->manufacturer->manufacturers_name;
            }else{
                $parentID = 0;
                $parent_data = false;
            }
            if ( !is_array($parent_data) ) {
                // new common product
                $defaultValuesObj = new \common\models\Products();
                $defaultValuesObj->loadDefaultValues();
                $parent_data = $defaultValuesObj->getAttributes();
                unset($defaultValuesObj);
            }
            $pInfo = new \objectInfo(array_merge($parent_data,[
                'products_id' => 0,
                'parent_products_id' => $parentID,
                'products_id_stock' => $parentID,
                'products_id_price' => $parentID,
                'products_popularity' => 0, 'popularity_simple' => 0, 'popularity_bestseller' => 0,
                'use_sets_discount' => 0,
                'is_bundle' => Yii::$app->request->get('bundle', 0),
                'without_inventory' => (int) (!\common\helpers\Extensions::isAllowed('Inventory')),
                'products_tax_class_id' => \common\helpers\Tax::getDefaultTaxClassIdForProducts(),
                'products_sets_discount' => 0,
                'products_name' => '',
            ]));
        }
        $infoSubProducts = '';
        \common\helpers\Php8::nullProps($pInfo, ['parent_products_id', 'products_model', 'products_name', 'products_id', 'products_quantity', 'allocated_stock_quantity', 'temporary_stock_quantity', 'warehouse_stock_quantity', 'ordered_stock_quantity', 'suppliers_stock_quantity', 'stock_reorder_level', 'stock_reorder_quantity', 'stock_limit']);
        if ( $pInfo->parent_products_id ) {
            $this->ProductEditTabAccess->setProduct($pInfo);
            $editProductBundleSwitcher = false;
            $infoSubProducts = '<b>'.TEXT_CHILD_PRODUCT.'</b> '.TEXT_SUB_PRODUCT_CONNECTED_TO_PARENT .' <i class="product_list_marker">' . Html::a(\common\helpers\Product::get_backend_products_name($pInfo->parent_products_id), Url::to(['categories/productedit', 'pID'=> $pInfo->parent_products_id])).'</i>';
        }elseif( $pInfo->products_id ) {
            $children_ids = \common\helpers\SubProduct::getChildrenIds($pInfo->products_id);
            if ( count($children_ids)>0 ) {
                foreach ($children_ids as $child_id){
                    if ( empty($infoSubProducts) ){
                        $infoSubProducts = '<b>'.TEXT_PARENT_PRODUCT.'</b> '.TEXT_SUB_PRODUCT_CONNECTED_CHILDREN .'<i class="product_list_marker">' . Html::a(\common\helpers\Product::get_backend_products_name($child_id), Url::to(['categories/productedit', 'pID'=> $child_id])).'</i>';
                        if ( count($children_ids)>1 ) $infoSubProducts .= '<i class="product_list_marker" style="position: relative;"><b>'.TEXT_SUB_PRODUCT_SEE_ALL_CHILDREN.'</b><div class="product_list_marker__pophover">';
                    }else{
                        $infoSubProducts .= ' <i class="product_list_marker">' . Html::a(\common\helpers\Product::get_backend_products_name($child_id), Url::to(['categories/productedit', 'pID'=> $child_id])).'</i>';
                    }
                }
                if ( count($children_ids)>1 ) $infoSubProducts .= '</div></i>';
            }
        }

        $pInfo->stockInfo = new ViewStockInfo($pInfo);

        if (!empty($pInfo->products_date_available)) {
            $pInfo->products_date_available = \common\helpers\Date::date_short($pInfo->products_date_available);
        }

        if (!empty($pInfo->products_new_until)) {
            $pInfo->products_new_until = \common\helpers\Date::date_short($pInfo->products_new_until);
        }

        $pInfo->settings = \common\helpers\Product::getSettings($pInfo->products_id);

        $this->selectedMenu = array('catalog', 'categories');

        $str_full = strlen($pInfo->products_model);
        if ($str_full > 20) {
            $st_full_name = mb_substr($pInfo->products_model, 0, 20);
            $st_full_name .= '...';
            $st_full_model_view = '<span title="' . $pInfo->products_model . '">' . $st_full_name . '</span>';
        } else {
            $st_full_model_view = $pInfo->products_model;
        }

        $str_full = strlen($pInfo->products_name);
        if ($str_full > 35) {
            $st_full_name = mb_substr($pInfo->products_name, 0, 35);
            $st_full_name .= '...';
            $st_full_name_view = '<span title="' . $pInfo->products_name . '">' . $st_full_name . '</span>';
        } else {
            $st_full_name_view = $pInfo->products_name;
        }
        $text_new_or_edit = ($products_id == 0) ? TEXT_NEW_PRODUCT : T_EDIT_PROD . ' ' . $st_full_model_view . ' "' . $st_full_name_view . '"';
        if ( $products_id == 0 ) {
            $editProductInPath = (defined('TEXT_PRODUCT_CREATE_IN')?TEXT_PRODUCT_CREATE_IN:'').' '.'<ul class="category_path_list top_bead-items"><li class="category_path">'.\common\helpers\Categories::output_generated_category_path($in_category_id,'category','<span class="category_path__location">%2$s</span>','</li><li class="category_path onemore">').'</li></ul>';
        }else{
            $editProductInPath = (defined('TEXT_PRODUCT_PLACED_IN')?TEXT_PRODUCT_PLACED_IN:'').' '.'<ul class="category_path_list top_bead-items"><li class="category_path">'.\common\helpers\Categories::output_generated_category_path($products_id,'product','<a class="category_path__location" href="categories?category_id=%1$s">%2$s</a>','</li><li class="category_path onemore">').'</li></ul>';
        }
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders/productedit'), 'title' => $text_new_or_edit);

//// extensions
        $this->view->groups = [];
        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $ext::getGroups();
        }

        $bundlesProducts = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
            $bundlesProducts = $ext::getProducts($pInfo);
        }
        $this->view->bundlesProducts = $bundlesProducts;

        $documents = [];
        /** @var \common\extensions\ProductDocuments\ProductDocuments $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('ProductDocuments')) {
            $documents = $ext::getDocuments($products_id);
        }
        $this->view->documents = $documents;
        /** @var \common\extensions\Inventory\Inventory $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('Inventory')) {
            $ext::getInventory($products_id, $languages_id);
        } else {
            $this->view->showInventory = false;
        }

        $this->view->templates = [
            'list' => \common\classes\platform::getList(false),
            'show_block' => 1
        ];
        /** @var \common\extensions\ProductTemplates\ProductTemplates $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('ProductTemplates')) {
            $this->view->templates = $ext::productedit($products_id);
        }

        if ($this->ProductEditTabAccess->tabView('TAB_IMPORT_EXPORT')){
            $this->view->import_export = new ViewImportExport($pInfo);
        }


///////////// other lists (for both new and edit product - attributes, properties, x-sell)

        $this->view->tax_classes = ['0' => TEXT_NONE];
        $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
        while ($tax_class = tep_db_fetch_array($tax_class_query)) {
            $this->view->tax_classes[$tax_class['tax_class_id']] = $tax_class['tax_class_title'];
        }

        $attribute_templates = [
            'label' => BOX_CATALOG_CATEGORIES_OPTIONS_TEMPLATES,
            'options' => array_map(
                function($data){
                    return [
                        'value' => $data['options_templates_id'],
                        'name' => htmlspecialchars($data['options_templates_name']),
                    ];
                },
                \common\models\OptionsTemplates::find()->orderBy(['options_templates_name'=>SORT_ASC])->asArray()->all()
            )
        ];
        $this->view->attributeTemplates = $attribute_templates;

        $attributes = [];
//improve - 1 query
        $options_query = tep_db_query("select products_options_id, products_options_name, is_virtual from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_sort_order, products_options_name");
        while ($options = tep_db_fetch_array($options_query)) {
            $values_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " p2p where pov.products_options_values_id = p2p.products_options_values_id and p2p.products_options_id = '" . $options['products_options_id'] . "' and pov.language_id = '" . $languages_id . "' order by products_options_values_sort_order, products_options_values_name");
            $option = [];
            while ($values = tep_db_fetch_array($values_query)) {
                $option[] = [
                    'value' => $values['products_options_values_id'],
                    'name' => htmlspecialchars($values['products_options_values_name'])
                ];
            }
            $attributes[] = [
                'id' => $options['products_options_id'],
                'label' => htmlspecialchars($options['products_options_name']),
                'is_virtual' => !!$options['is_virtual'],
                'disable' => (!$options['is_virtual'] && $pInfo->parent_products_id),
                'options' => $option,
            ];
        }
        $this->view->attributes = $attributes;

        $this->view->defaultCurrency = $currencies->currencies[DEFAULT_CURRENCY]['id'];
        $this->view->useMarketPrices = (USE_MARKET_PRICES == 'True');

        $this->view->give_away = 0;
        $this->view->shopping_cart_price = '';
        $this->view->buy_qty = '';
        $this->view->products_qty = '';
        $this->view->use_in_qty_discount = 0;

        $this->view->featured = 0;
        $this->view->featured_expires_date = '';

        $upload_path = \Yii::getAlias('@web');
        $upload_path .= '/uploads/';
        $this->view->upload_path = $upload_path;

        $pDescription = [];
        $_pQ = \common\models\Platforms::getPlatformsByType("non-virtual")->orderBy("is_marketplace, sort_order");
        if (!(isset($this->view->sph) && $this->view->sph)){
            $_pQ->andWhere(['status' => 1]);
        }
        $admin_available_platform_ids = \yii\helpers\ArrayHelper::getColumn(\common\classes\platform::getList(), 'id');
        $_pQ->andWhere(['IN','platform_id',$admin_available_platform_ids]);
        $platforms = $_pQ->all();
        $def_platformId = \common\classes\platform::defaultId();
        if ( !in_array($def_platformId, $admin_available_platform_ids) ){
            $def_platformId = reset($admin_available_platform_ids);
        }
        $platformConfigs = [];
        $this->view->platform_languages = [];
      ///Description
        if (isset($_GET['shp'])){
            $_session->set('shp', (int)$_GET['shp']);
        }
        $this->view->sph = $_session->has('shp') ? $_session->get('shp') : 0;
        if (isset($_GET['shpl'])){
            $_session->set('shpl', $_GET['shpl']);
        }
        $this->view->sphl = $_session->has('shpl') ? $_session->get('shpl') : 0;

        $description_products_id = $products_id;
        // {{ create sub product
        if ( $pInfo->products_id==0 && $pInfo->parent_products_id ) {
            $description_products_id = $pInfo->parent_products_id;
        }
        // }} create sub product
        if (count($platforms) > 1){
            foreach($platforms as $platform){
                if (empty($this->view->sphl[$platform->platform_id]) && !$platform->is_marketplace){
                  $_pLans = Yii::$app->get('platform')->getConfig($platform->platform_id)->getAllowedLanguages();
                  if ($_pLans){
                      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                         if (in_array($languages[$i]['code'], $_pLans)){
                             $pDescriptionObject = \common\models\ProductsDescription::find()->where(['products_id' => $description_products_id, 'language_id' => $languages[$i]['id'], 'platform_id' => $platform->platform_id, 'department_id' => 0 ])->one();
                             if ($selectedDepartmentId > 0) {
                                 $pDescriptionOverrideObject = \common\models\ProductsDescription::find()->where(['products_id' => $description_products_id, 'language_id' => $languages[$i]['id'], 'platform_id' => $platform->platform_id, 'department_id' => $selectedDepartmentId ])->one();
                                 if (is_object($pDescriptionOverrideObject)) {
                                     if (!empty($pDescriptionOverrideObject->products_name)) {
                                         $pDescriptionObject->products_name = $pDescriptionOverrideObject->products_name;
                                     }
                                     if (!empty($pDescriptionOverrideObject->products_internal_name)) {
                                         $pDescriptionObject->products_internal_name = $pDescriptionOverrideObject->products_internal_name;
                                     }
                                     if (!empty($pDescriptionOverrideObject->products_description_short)) {
                                         $pDescriptionObject->products_description_short = $pDescriptionOverrideObject->products_description_short;
                                     }
                                     if (!empty($pDescriptionOverrideObject->products_description)) {
                                         $pDescriptionObject->products_description = $pDescriptionOverrideObject->products_description;
                                     }
                                     /*if (!empty($pDescriptionOverrideObject->products_seo_page_name)) {
                                         $pDescriptionObject->products_seo_page_name = $pDescriptionOverrideObject->products_seo_page_name;
                                     }*/
                                 }
                             }
                             $pDescription[$platform->platform_id][] = $pDescriptionObject;
                             $this->view->platform_languages[$platform->platform_id][] = $languages[$i];
                         }
                      }
                  }
                } else {
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                        $pDescriptionObject = \common\models\ProductsDescription::find()->where(['products_id' => $description_products_id, 'language_id' => $languages[$i]['id'], 'platform_id' => $platform->platform_id, 'department_id' => 0 ])->one();
                        if ($selectedDepartmentId > 0) {
                            $pDescriptionOverrideObject = \common\models\ProductsDescription::find()->where(['products_id' => $description_products_id, 'language_id' => $languages[$i]['id'], 'platform_id' => $platform->platform_id, 'department_id' => $selectedDepartmentId ])->one();
                            if (is_object($pDescriptionOverrideObject)) {
                                if (!empty($pDescriptionOverrideObject->products_name)) {
                                    $pDescriptionObject->products_name = $pDescriptionOverrideObject->products_name;
                                }
                                if (!empty($pDescriptionOverrideObject->products_internal_name)) {
                                    $pDescriptionObject->products_internal_name = $pDescriptionOverrideObject->products_internal_name;
                                }
                                if (!empty($pDescriptionOverrideObject->products_description_short)) {
                                    $pDescriptionObject->products_description_short = $pDescriptionOverrideObject->products_description_short;
                                }
                                if (!empty($pDescriptionOverrideObject->products_description)) {
                                    $pDescriptionObject->products_description = $pDescriptionOverrideObject->products_description;
                                }
                            }
                        }
                        $pDescription[$platform->platform_id][$i] = $pDescriptionObject;
                    }
                    $this->view->platform_languages[$platform->platform_id] = $languages;
                }
            }
        } else {
            if (!(isset($this->view->sphl[$def_platformId]) && $this->view->sphl[$def_platformId])) {
                $_pLans = Yii::$app->get('platform')->getConfig($def_platformId)->getAllowedLanguages();
                if ($_pLans) {
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                        if (in_array($languages[$i]['code'], $_pLans)) {
                            $pDescriptionObject = \common\models\ProductsDescription::find()->where(['products_id' => $description_products_id, 'language_id' => $languages[$i]['id'], 'platform_id' => $def_platformId, 'department_id' => 0])->one();
                            if ($selectedDepartmentId > 0) {
                                $pDescriptionOverrideObject = \common\models\ProductsDescription::find()->where(['products_id' => $description_products_id, 'language_id' => $languages[$i]['id'], 'platform_id' => $def_platformId, 'department_id' => $selectedDepartmentId])->one();
                                if (is_object($pDescriptionOverrideObject)) {
                                    if (!empty($pDescriptionOverrideObject->products_name)) {
                                        $pDescriptionObject->products_name = $pDescriptionOverrideObject->products_name;
                                    }
                                    if (!empty($pDescriptionOverrideObject->products_internal_name)) {
                                        $pDescriptionObject->products_internal_name = $pDescriptionOverrideObject->products_internal_name;
                                    }
                                    if (!empty($pDescriptionOverrideObject->products_description_short)) {
                                        $pDescriptionObject->products_description_short = $pDescriptionOverrideObject->products_description_short;
                                    }
                                    if (!empty($pDescriptionOverrideObject->products_description)) {
                                        $pDescriptionObject->products_description = $pDescriptionOverrideObject->products_description;
                                    }
                                }
                            }
                            $pDescription[$def_platformId][] = $pDescriptionObject;
                            $this->view->platform_languages[$def_platformId][] = $languages[$i];
                        }
                    }
                }
            } else {
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                    $pDescriptionObject = \common\models\ProductsDescription::find()->where(['products_id' => $description_products_id, 'language_id' => $languages[$i]['id'], 'platform_id' => \common\classes\platform::defaultId(), 'department_id' => 0])->one();
                    if ($selectedDepartmentId > 0) {
                        $pDescriptionOverrideObject = \common\models\ProductsDescription::find()->where(['products_id' => $description_products_id, 'language_id' => $languages[$i]['id'], 'platform_id' => \common\classes\platform::defaultId(), 'department_id' => $selectedDepartmentId])->one();
                        if (is_object($pDescriptionOverrideObject)) {
                            if (!empty($pDescriptionOverrideObject->products_name)) {
                                $pDescriptionObject->products_name = $pDescriptionOverrideObject->products_name;
                            }
                            if (!empty($pDescriptionOverrideObject->products_internal_name)) {
                                $pDescriptionObject->products_internal_name = $pDescriptionOverrideObject->products_internal_name;
                            }
                            if (!empty($pDescriptionOverrideObject->products_description_short)) {
                                $pDescriptionObject->products_description_short = $pDescriptionOverrideObject->products_description_short;
                            }
                            if (!empty($pDescriptionOverrideObject->products_description)) {
                                $pDescriptionObject->products_description = $pDescriptionOverrideObject->products_description;
                            }
                        }
                    }
                    $pDescription[$def_platformId][$i] = $pDescriptionObject;
                }
                $this->view->platform_languages[$def_platformId] = $languages;
            }
        }
        // create sub product
        if ( $pInfo->products_id==0 && $pInfo->parent_products_id ) {
            foreach($pDescription as $__nestDesc) {
                foreach ($__nestDesc as $__DescModel) {
                    if (!is_object($__DescModel)) continue;
                    $__new_data = array_fill_keys(array_keys($__DescModel->getAttributes()),'');
                    $__new_data = array_merge($__new_data, $__DescModel->getAttributes(['products_name','products_internal_name','products_description_short','products_description']));
                    $__DescModel->setAttributes($__new_data, false);
                }
            }
        }

        $this->view->platform_activate_categories = [];
        $this->view->department_activate_categories = [];
        $this->view->price_tabs_data = null;
        $this->view->gaw = [];
        $this->view->properties_tree = [];
        $this->view->properties_tree = \common\helpers\Properties::get_properties_tree('0', '&nbsp;&nbsp;&nbsp;&nbsp;', '', false);
        $this->view->properties_hiddens = '';
        $this->view->properties_array = [];
        $this->view->values_array = [];
        $this->view->extra_values = [];
        $this->view->properties_tree_array = [];
/// default view values
        if ($products_id == 0) {

          $this->view->platform_assigned = [];
          if ($in_category_id > 0) {
              $get_assigned_platforms_r = tep_db_query("SELECT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id = '" . intval($in_category_id) . "' ");
              if (tep_db_num_rows($get_assigned_platforms_r) > 0) {
                  while ($_assigned_platform = tep_db_fetch_array($get_assigned_platforms_r)) {
                      $this->view->platform_assigned[(int) $_assigned_platform['platform_id']] = (int) $_assigned_platform['platform_id'];
                  }
              }
          } else {
              foreach (\common\classes\platform::getProductsAssignList() as $___data) {
                  $this->view->platform_assigned[intval($___data['id'])] = intval($___data['id']);
              }
          }

          $this->view->showStatistic = false;
          $this->view->imagesQty = 0;

          $this->view->suppliers = [];
          $dSupplier = Suppliers::findOne(['is_default' => 1]);
          if ($dSupplier){
              $sProduct = new \common\models\SuppliersProducts();
              $sProduct->loadDefaultValues();
              $sProduct->loadSupplierValues($dSupplier->suppliers_id);
              $service->get('\common\models\SuppliersProducts', 'sProduct');
              $this->view->suppliers[$dSupplier->suppliers_id] = $sProduct;
          }

          if ( $pInfo->parent_products_id ) {
              $priceViewObj = new ViewPriceData(\common\models\Products::findOne($pInfo->products_id_price));
              $priceViewObj->populateView($this->view, $currencies);
              if (count($this->view->attributes) > 0) { // attributes added to system
                  $parentProductModel = \common\models\Products::findOne($pInfo->parent_products_id);
                  $attributes = new ViewAttributes($parentProductModel, true);
                  $attributes->populateView($this->view);
              }
          }

        } else {
/// product exists - edit
/// statistics
          $this->view->showStatistic = true;
          $this->view->statistic = new \stdClass();
          $this->view->statistic->price = $currencies->format(\common\helpers\Product::get_products_price($pInfo->products_id));
          $this->view->statistic->products_date_added = \common\helpers\Date::datetime_short($pInfo->products_date_added);
          $this->view->statistic->products_last_modified = \common\helpers\Date::datetime_short($pInfo->products_last_modified);
          $this->view->statistic->products_viewed = $pInfo->products_viewed;

          if ($this->view->showInventory) {
              $inventoryListing = [];
              $inventory_query = tep_db_query("select * from " . TABLE_INVENTORY . " where prid = '" . (int) $pInfo->products_id . "'");
              while ($inventory_data = tep_db_fetch_array($inventory_query)) {
                  $arr = preg_split("/[{}]/", $inventory_data['products_id']);
                  $label = '';
                  for ($i = 1, $n = sizeof($arr); $i < $n; $i = $i + 2) {
                      $options_name_data = tep_db_fetch_array(tep_db_query("select products_options_name as name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $arr[$i] . "' and language_id  = '" . (int) $languages_id . "'"));
                      $options_values_name_data = tep_db_fetch_array(tep_db_query("select products_options_values_name as name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id  = '" . $arr[$i + 1] . "' and language_id  = '" . (int) $languages_id . "'"));
                      if ($label == '') {
                          $label = $options_name_data['name'] . ' : ' . $options_values_name_data['name'];
                      } else {
                          $label .= ', ' . $options_name_data['name'] . ' : ' . $options_values_name_data['name'];
                      }
                  }

                  $inventoryListing[] = [
                      'label' => $label,
                      'price' => $currencies->format(\common\helpers\Product::get_products_price($pInfo->products_id)),
                  ];
              }
              $this->view->statistic->inventory = $inventoryListing;
          }

          $orders_data_array = array('ordered' => array(), 'price' => array());
          $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') + 1, 1, date('Y') - 1));
          $orders_query = tep_db_query("select year(o.date_purchased) as date_year, month(o.date_purchased) as date_month, count(*) as total_orders, avg(op.products_price) as price, sum(op.products_quantity) as total from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_PRODUCTS . " op on (o.orders_id = op.orders_id and op.products_id = '" . $pInfo->products_id . "') where o.date_purchased >= '" . tep_db_input($date_from) . "' group by year(o.date_purchased), month(o.date_purchased) order by year(o.date_purchased), month(o.date_purchased)");
          while ($orders = tep_db_fetch_array($orders_query)) {
              $orders_data_array['ordered'][] = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total'] . ']';
              $orders_data_array['price'][] = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['price'] . ']';
          }
          $this->view->statistic->orderedGrid = implode(" , ", $orders_data_array['ordered']);
          $this->view->statistic->priceGrid = implode(" , ", $orders_data_array['price']);

/// price and cost
          if ( $pInfo->products_id_price && $pInfo->products_id!=$pInfo->products_id_price ) {
              $priceViewObj = new ViewPriceData(\common\models\Products::findOne($pInfo->products_id_price));
          }else {
              $priceViewObj = new ViewPriceData($pInfo);
          }
          $priceViewObj->populateView($this->view, $currencies);

/// assigned attributes
          if (count($this->view->attributes)>0) { // attributes added to system
              $attributes = new ViewAttributes($pInfo, $pInfo->parent_products_id>0);
              $attributes->populateView($this->view);
          }


          if (tep_not_null($pInfo->featured_status)) {
              $this->view->featured = $pInfo->featured_status;
              $this->view->featured_expires_date = \common\helpers\Date::date_short($pInfo->featured_expires_date);
          }

          $productFile = '';
          if ($pInfo->products_file != '') {
              $productFile .= '<a href="' . tep_href_link(FILENAME_DOWNLOAD, 'filename=' . $pInfo->products_file) . '">' . $pInfo->products_file . '</a><br>';
              $productFile .= tep_draw_hidden_field('products_previous_file', $pInfo->products_file) . '<input type="checkbox" name="delete_products_file" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT;
          }
          $this->view->productFile = $productFile;

          $this->view->platform_assigned = [];
          $get_assigned_platforms_r = tep_db_query("SELECT platform_id FROM " . TABLE_PLATFORMS_PRODUCTS . " WHERE products_id = '" . intval($pInfo->products_id) . "' ");
          if (tep_db_num_rows($get_assigned_platforms_r) > 0) {
              while ($_assigned_platform = tep_db_fetch_array($get_assigned_platforms_r)) {
                  $this->view->platform_assigned[(int) $_assigned_platform['platform_id']] = (int) $_assigned_platform['platform_id'];
              }
          }

          $this->view->platform_activate_categories = [];
          foreach (\common\classes\platform::getCategoriesAssignList() as $__category_platform) {
              if (isset($this->view->platform_assigned[$__category_platform['id']]))
                  continue;
              $get_notactive_categories_r = tep_db_query(
                      "SELECT p2c.categories_id, plc.platform_id " .
                      "FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c " .
                      "  LEFT JOIN " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id and plc.platform_id='" . $__category_platform['id'] . "'  " .
                      "WHERE p2c.products_id='" . intval($pInfo->products_id) . "' " .
                      "  /*AND plc.platform_id IS NULL*/"
              );
              while ($_notactive_category = tep_db_fetch_array($get_notactive_categories_r)) {
                  foreach (\common\helpers\Categories::generate_category_path($_notactive_category['categories_id']) as $_category_path_array) {
                      if (!isset($this->view->platform_activate_categories[$__category_platform['id']])) {
                          $this->view->platform_activate_categories[$__category_platform['id']] = array();
                      }
                      $this->view->platform_activate_categories[$__category_platform['id']][$_category_path_array[0]['id']] = array(
                          'label' => implode(' &gt; ', array_reverse(array_map(function ($_in) {
                                                      return $_in['text'];
                                                  }, $_category_path_array))),
                          'selected' => !is_null($_notactive_category['platform_id']),
                      );
                  }
              }
          }

            $this->view->department_activate_categories = [];
            $this->view->department_assigned = [];
            if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
                // {{ department tab
                if (isset($pInfo->products_id) && intval($pInfo->products_id) > 0) {
                    $get_assigned_department_r = tep_db_query("SELECT departments_id FROM " . TABLE_DEPARTMENTS_PRODUCTS . " WHERE products_id = '" . intval($pInfo->products_id) . "' ");
                    if (tep_db_num_rows($get_assigned_department_r) > 0) {
                        while ($_assigned_department = tep_db_fetch_array($get_assigned_department_r)) {
                            $this->view->department_assigned[(int) $_assigned_department['departments_id']] = (int) $_assigned_department['departments_id'];
                        }
                    }
                } elseif ($in_category_id > 0) {
                    $get_assigned_department_r = tep_db_query("SELECT departments_id FROM " . TABLE_DEPARTMENTS_CATEGORIES . " WHERE categories_id = '" . intval($in_category_id) . "' ");
                    if (tep_db_num_rows($get_assigned_department_r) > 0) {
                        while ($_assigned_department = tep_db_fetch_array($get_assigned_department_r)) {
                            $this->view->department_assigned[(int) $_assigned_department['departments_id']] = (int) $_assigned_department['departments_id'];
                        }
                    }
                } else {
                    foreach (\common\classes\department::getCatalogAssignList() as $___data) {
                        $this->view->department_assigned[intval($___data['id'])] = intval($___data['id']);
                    }
                }

                foreach (\common\classes\department::getCatalogAssignList() as $__category_department) {
                    if (isset($this->view->department_assigned[$__category_department['id']]))
                        continue;
                    $get_notactive_categories_r = tep_db_query(
                        "SELECT p2c.categories_id, plc.departments_id " .
                        "FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c " .
                        "  LEFT JOIN " . TABLE_DEPARTMENTS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id and plc.departments_id='" . $__category_department['id'] . "'  " .
                        "WHERE p2c.products_id='" . intval($pInfo->products_id) . "' " .
                        "  /*AND plc.departments_id IS NULL*/"
                    );
                    while ($_notactive_category = tep_db_fetch_array($get_notactive_categories_r)) {
                        foreach (\common\helpers\Categories::generate_category_path($_notactive_category['categories_id']) as $_category_path_array) {
                            if (!isset($this->view->department_activate_categories[$__category_platform['id']])) {
                                $this->view->department_activate_categories[$__category_department['id']] = array();
                            }
                            $this->view->department_activate_categories[$__category_department['id']][$_category_path_array[ count($_category_path_array)-1 ]['id']] = array(
                                'label' => implode(' &gt; ', array_reverse(array_map(function ($_in) {
                                    return $_in['text'];
                                }, $_category_path_array))),
                                'selected' => !is_null($_notactive_category['departments_id']),
                            );
                        }
                    }
                }
                // }} department tab
            }

          $this->view->suppliers = [];
          $service->get('\common\models\SuppliersProducts', 'sProduct');

          if (!\common\helpers\Attributes::has_product_attributes($pInfo->products_id ) || $pInfo->without_inventory || !\common\helpers\Extensions::isAllowed('Inventory') ){
              //$sProducts = \common\models\SuppliersProducts::getSupplierProducts((int)$pInfo->products_id)->all();
              $sProducts = \common\models\SuppliersProducts::find()->alias('sp')
                  ->joinWith('supplier s')
                  ->where(['sp.products_id' => (int)$pInfo->products_id ])
                  ->orderBy(new \yii\db\Expression('if(sp.sort_order is null, s.sort_order, sp.sort_order)'))
                  ->all();
              $pInfo->supplier_default_sort = 1;
              if (!$sProducts){
                  $sProduct = (new \common\models\SuppliersProducts())->saveDefaultSupplierProduct(['products_id' => (int)$pInfo->products_id]);
                  if ($sProduct) $sProducts = [$sProduct];
              } else {
                  if (count($sProducts) > 1) {
                    foreach ($sProducts as $sProduct) {
                        if (!is_null($sProduct->sort_order)) {
                            $pInfo->supplier_default_sort = 0;
                            break;
                        }
                    }
                  }
              }
          }
          if (!empty($sProducts)){
              foreach($sProducts as $sProduct){
                  $this->view->suppliers[$sProduct->suppliers_id] = $sProduct;
              }
          }

          $this->view->properties_hiddens = '';
          $this->view->properties_array = array();
          $this->view->values_array = array();
          $this->view->extra_values = array();
          $properties_query = tep_db_query("select properties_id, if(values_id > 0, values_id, values_flag) as values_id, extra_value from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id = '" . (int) $pInfo->products_id . "'");
          while ($properties = tep_db_fetch_array($properties_query)) {
              if (!in_array($properties['properties_id'], $this->view->properties_array)) {
                  $this->view->properties_array[] = $properties['properties_id'];
                  $this->view->properties_hiddens .= tep_draw_hidden_field('prop_ids[]', $properties['properties_id']);
              }
              $this->view->values_array[$properties['properties_id']][] = $properties['values_id'];
              $this->view->extra_values[$properties['properties_id']][] = $properties['extra_value'];
              $this->view->properties_hiddens .= tep_draw_hidden_field('val_ids[' . $properties['properties_id'] . '][]', $properties['values_id']);
              $this->view->properties_hiddens .= tep_draw_hidden_field('val_extra[' . $properties['properties_id'] . '][]', $properties['extra_value']);
          }
          $this->view->properties_tree_array = \common\helpers\Properties::generate_properties_tree(0, $this->view->properties_array, $this->view->values_array, '', '', $this->view->extra_values);

          $videos = [];
          $productsImages = \common\models\ProductsVideos::find()->where(['products_id' => $pInfo->products_id])->asArray()->all();
          foreach ($productsImages as $productsImage) {
              if ($productsImage['type'] == 1) {
                  $productsImage['src'] = '..' . DIRECTORY_SEPARATOR . DIR_WS_IMAGES . 'products'
                      . DIRECTORY_SEPARATOR . $pInfo->products_id
                      . DIRECTORY_SEPARATOR . 'videos'
                      . DIRECTORY_SEPARATOR . $productsImage['video'];
              }
              $videos[$productsImage['language_id']][] = $productsImage;
          }
          $this->view->videos = $videos;

          if (Yii::$app->request->isPost) {
              $this->layout = false;
          }

          \common\helpers\Thumb::setProductPagination($_session, $this->view, (int) $pInfo->products_id);

//improve

          $frontends = array();
          foreach (\common\classes\platform::getList(false) as $frontend) {
              if (isset($this->view->platform_assigned[$frontend['id']])) {
                  $seo_url = tep_db_fetch_array(tep_db_query("select products_seo_page_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . $products_id . "' and language_id = '" . (int) \common\helpers\Language::get_default_language_id() . "' and platform_id = '".(int)$frontend['id']."'"));
                  if ($seo_url['products_seo_page_name'] ?? null) {
                      $this->view->preview_link[] = [
                          'link' => 'http://' . $frontend['platform_url'] . '/' . $seo_url['products_seo_page_name'],
                          'name' => $frontend['text']
                      ];
                  } else {
                      $this->view->preview_link[] = [
                          'link' => 'http://' . $frontend['platform_url'] . '/catalog/product?products_id=' . $pInfo->products_id,
                          'name' => $frontend['text']
                      ];
                  }
                  $frontends[] = $frontend;
              }
          }

          \common\helpers\Gifts::prepareGWA($this->view, $pInfo->products_id);

        }

        //{{ insert and update
        if ( !empty($pInfo->products_id) || !empty($pInfo->parent_products_id) ) {
            $imageEditObj = new \backend\models\ProductEdit\ViewImages($pInfo);
            $imageEditObj->populateView($this->view);
        }
        //}} insert and update

        $this->view->platforms = $platforms;
        $this->view->def_platform_id = $def_platformId;

/// re-arrange data arrays for design templates
// init price tabs
        $this->view->price_tabs = $this->view->price_tabparams = [];
////currencies tabs and params
        $this->view->currenciesTabs = [];
        if ($this->view->useMarketPrices) {
          foreach ($currencies->currencies as $value) {
            $value['def_data'] = ['currencies_id' => $value['id']];
            $value['title'] = $value['symbol_left'] . ' ' . $value['code'] . ' ' . $value['symbol_right'];
            $this->view->currenciesTabs[] = $value;
          }
          $this->view->price_tabs[] = $this->view->currenciesTabs;
          $this->view->price_tabparams[] =  [
              'cssClass' => 'tabs-currencies',
              'tabs_type' => 'hTab',
              //'maxWidth' => '520px',
              //'include' => 'test/test.tpl',
          ];
        }
        $this->view->currenciesFormats = []; // used to format currency in js
        foreach ($currencies->currencies as $value) {
            $value['def_data'] = ['currencies_id' => $value['id']];
            $value['title'] = $value['symbol_left'] . ' ' . $value['code'] . ' ' . $value['symbol_right'];
            $this->view->currenciesFormats[] = $value;
        }

    //// groups tabs and params
        if (\common\helpers\Extensions::isCustomerGroupsAllowed()) {
          $this->view->groups_m = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), array_filter($this->view->groups, function($e) { return $e['per_product_price']; }));
          $tmp = [];
          foreach ($this->view->groups_m as $value) {
            $value['id'] = $value['groups_id'];
            $value['title'] = $value['groups_name'];
            $value['def_data'] = ['groups_id' => $value['id']];
            unset($value['groups_name']);
            unset($value['groups_id']);
            $tmp[] = $value;
          }
          $this->view->price_tabs[] = $tmp;
          unset($tmp);
          $this->view->price_tabparams[] = [
              'cssClass' => 'tabs-groups', // add to tabs and tab-pane
              //'callback' => 'productPriceBlock', // smarty function which will be called before children tabs , data passed as params params
              'callback_bottom' => '',
              'tabs_type' => 'lTab',
              'aboveTabs' => (count($this->view->groups_m)<(1+count($this->view->groups))? 'productedit/edit-price-link.tpl':''),
              'all_hidden' => (count($this->view->groups_m)==1),
              'maxHeight' => '400px',
          ];
        }

        foreach (\common\helpers\Hooks::getList('categories/productedit/before-render') as $filename) {
            include($filename);
        }

        if ($pdExt = \common\helpers\Acl::checkExtensionAllowed('ProductDesigner', 'allowed')){
            $pdExt::productEdit($pInfo, $this->view);
        }

        $pInfo->current_assigned_categories = [];
        if ( $pInfo->products_id ) {
            foreach ( \common\models\Products::findOne($pInfo->products_id)->categoriesList as $_catTmp) {
                $pInfo->current_assigned_categories[] = $_catTmp->categories_id;
            }
            $pInfo->current_assigned_categories = array_map('intval',$pInfo->current_assigned_categories);
        }else{
            $pInfo->current_assigned_categories[] = (int)$in_category_id;
        }

        $departments = false;
        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $departments = \common\classes\department::getList(false);
        }

        $productGroupVariants = [];
        $productGroupName = '';
        if ( $pInfo->products_groups_id ) {
            $productGroupVariants = [
                'items' => [],
                'selected' => Url::to(['categories/productedit', 'pID'=>$pInfo->products_id]),
            ];

            $productGroupName = \common\helpers\Product::products_groups_name($pInfo->products_groups_id);
            foreach( \common\models\Products::find()
                         ->where(['products_groups_id'=>$pInfo->products_groups_id])
                         ->select(['products_id'])
                         ->asArray()->all() as $_prod ){
                $productGroupVariants['items'][Url::to(['categories/productedit', 'pID'=>$_prod['products_id']]) ] = \common\helpers\Product::get_products_name($_prod['products_id']);
            }
        }

        $productsNotes = $this->productsNotesService->findByProductId((int)$pInfo->products_id);

        $languageNames = [];
        $languageNames[0] = '';
        foreach ($languages as $language) {
            $languageNames[$language['id']] = $language['name'];
        }

        $video = [];
        $video[0] = '';
        if ($pInfo->products_id > 0) {
            $productsVideos = \common\models\ProductsVideos::find()->where(['products_id' => $pInfo->products_id])->asArray()->all();
            foreach ($productsVideos as $productsVideo) {
                $video[$languageNames[$productsVideo['language_id']]][$productsVideo['video_id']] = 'Video#' . $productsVideo['video_id'];
            }
        }

        $hidden_admin_language = \common\helpers\Language::getAdminHiddenLanguages();
        if (!empty($hidden_admin_language)) {
            foreach ($this->view->platform_languages as $_pl => $_lngs) {
                $show_hidden_admin_language = false;
                if (!empty($this->view->sphl) && is_array($this->view->sphl) && !empty($this->view->sphl[$_pl])) {
                    continue;
                }
                if (is_array($_lngs)) {
                    $this->view->platform_languages[$_pl] = array_values(
                        array_filter($_lngs,
                            function ($el) use($hidden_admin_language) { return !in_array($el['id'], $hidden_admin_language);}
                            ));
                }
            }
        }

        global $navigation;
        if (sizeof($navigation->snapshot) > 0) {
            $backUrl = Yii::$app->urlManager->createUrl(array_merge([$navigation->snapshot['page']], $navigation->snapshot['get']));
        } else {
            $categoryId = \common\models\Products2Categories::findOne(['products_id' => $pInfo->products_id])->categories_id ?? null;
            $backUrl = Yii::$app->urlManager->createUrl(['category', 'category_id' => $categoryId]);
        }

        return $this->render('productedit.tpl', [
            'infoBreadCrumb' => $editProductInPath,
            'infoSubProducts' => $infoSubProducts,
            'editProductBundleSwitcher' => $editProductBundleSwitcher, // allowed ProductBundles ext or product->is_bundle
            'default_currency' => $currencies->currencies[DEFAULT_CURRENCY],
            'currencies' => $currencies,
            'languages' => $languages,
            'languages_id' => $languages_id,
            'pInfo' => $pInfo,
            'pDescription' => $pDescription,
            'categories_id' => $in_category_id,
            'json_platform_activate_categories' => json_encode($this->view->platform_activate_categories),
            'json_department_activate_categories' => json_encode($this->view->department_activate_categories),
            'departments' => $departments,
            'selected_department_id' => $selectedDepartmentId,
            'service' => $service,
            'productGroupName' => $productGroupName,
            'productGroupVariants' => $productGroupVariants,
            'TabAccess' => $this->ProductEditTabAccess,
            'isBundle' => $isBundle,
            'productsNotes' => $productsNotes,
            'video' => $video,
            'popup' => false,
            'hideSuppliersPart' => false,
            'hidden_admin_language' => $hidden_admin_language,
            'backUrl' => $backUrl,
        ]);
    }

    public function actionPropertyValues() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = (int) Yii::$app->request->post('products_id');
        $properties_id = (int) Yii::$app->request->post('properties_id');

        $values = array();
        $property = tep_db_fetch_array(tep_db_query("select properties_id, properties_type, multi_choice, multi_line, decimals, extra_values from " . TABLE_PROPERTIES . " where properties_id = '" . (int) $properties_id . "'"));
        $property['properties_name'] = \common\helpers\Properties::get_properties_name($property['properties_id'], $languages_id);

        if ($property['properties_type'] == 'flag') {
            $values = array();
            $values[] = array('values_id' => '1', 'values' => TEXT_PROP_FLAG_YES);
            $values[] = array('values_id' => '0', 'values' => TEXT_PROP_FLAG_NO);
        } else {
            $properties_values_query = tep_db_query("select values_id, values_text, values_number, values_number_upto, values_alt, values_prefix, values_postfix from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int) $properties_id . "' and language_id = '" . (int) $languages_id . "' order by sort_order, " . ($property['properties_type'] == 'number' || $property['properties_type'] == 'interval' ? 'values_number' : 'values_text'));
            $thousandsSeparator = '';
            $decimalSeparator = '.';
            while ($properties_values = tep_db_fetch_array($properties_values_query)) {
                if ($property['properties_type'] == 'interval') {
                    $properties_values['values'] = number_format($properties_values['values_number'], $property['decimals'], $decimalSeparator, $thousandsSeparator) . ' - ' . number_format($properties_values['values_number_upto'], $property['decimals'], $decimalSeparator, $thousandsSeparator);
                } elseif ($property['properties_type'] == 'number') {
                    $properties_values['values'] = number_format($properties_values['values_number'], $property['decimals'], $decimalSeparator, $thousandsSeparator);
                } else {
                    $properties_values['values'] = $properties_values['values_text'];
                }
                $values[$properties_values['values_id']] = $properties_values;
            }
        }
        // extra_values for extra fields
        // values_prefix values_postfix

        return $this->render('property-values.tpl', [
                    'property' => $property,
                    'values' => $values,
        ]);
    }

    public function actionUpdatePropertyValues() {
        $properties_array = Yii::$app->request->post('properties_array', array());
        $values_array = Yii::$app->request->post('values_array', array());
        $extra_values = Yii::$app->request->post('extra_values', array());

        $values_ids = array();
        $val_extra = [];
        $properties_hiddens = '';
        foreach ($properties_array as $key => $properties_id) {
            if ($properties_id > 0) {
                $properties_hiddens .= tep_draw_hidden_field('prop_ids[]', $properties_id);
                foreach ($values_array[$key] as $values_key =>$values_id) {
                    $properties_id;
                    $property = \common\models\Properties::findOne($properties_id);
                    if ($values_id > 0 || $property->properties_type == 'flag') {
                        $properties_hiddens .= tep_draw_hidden_field('val_ids[' . $properties_id . '][]', $values_id);
                        $properties_hiddens .= tep_draw_hidden_field('val_extra[' . $properties_id . '][]', $extra_values[$key][$values_key] ?? null);
                        $values_ids[$properties_id][] = $values_id;
                        $val_extra[$properties_id][] = $extra_values[$key][$values_key] ?? null;
                    }
                    unset($property);
                }
            }
        }
        
        $this->layout = false;

        return $this->render('property-values-selected.tpl', [
                    'properties_hiddens' => $properties_hiddens,
                    'properties_tree_array' => \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_ids, '', '', $val_extra),
        ]);
    }

    public function actionProductNewOption() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = (int) Yii::$app->request->post('products_id');
        $inventoryDisable = Yii::$app->request->post('without_inventory',0)==1;

/*
        if (count(\common\helpers\Product::getChildArray($products_id)) > 0) {
            return json_encode([]);
        }
*/
        $products_options_ids = array_unique( explode(',', Yii::$app->request->post('products_options_id')));
        $products_options_values_ids = array_unique( explode(',', Yii::$app->request->post('products_options_values_id')));
        foreach ($products_options_ids as $k => $v) {
          if (intval($v)==0) {
            unset($products_options_ids[$k]);
          } else {
            $products_options_ids[$k] = intval($v);
          }
        }
        foreach ($products_options_values_ids as $k => $v) {
          if (intval($v)==0) {
            unset($products_options_values_ids[$k]);
          } else {
            $products_options_values_ids[$k] = intval($v);
          }
        }

        $this->view->groups = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $ext::getGroups(); //fills in $this->view->groups
        }

        $this->view->images = \common\helpers\Product::getProductImages($products_id);

        $ret = [];
        $attributes = [];
        $products_options_id = false;
        $currencies = Yii::$container->get('currencies');

/// re-arrange data arrays for design templates
// init price tabs
        $this->view->useMarketPrices = (USE_MARKET_PRICES == 'True');
        $this->view->price_tabs = $this->view->price_tabparams = [];
////currencies tabs and params
        if ($this->view->useMarketPrices) {
          $this->view->currenciesTabs = [];
          foreach ($currencies->currencies as $value) {
            $value['def_data'] = ['currencies_id' => $value['id']];
            $value['title'] = $value['symbol_left'] . ' ' . $value['code'] . ' ' . $value['symbol_right'];
            $this->view->currenciesTabs[] = $value;
          }
          $this->view->price_tabs[] = $this->view->currenciesTabs;
          $this->view->price_tabparams[] =  [
              'cssClass' => 'tabs-currencies',
              'tabs_type' => 'hTab',
              //'include' => 'test/test.tpl',
          ];
        }

    //// groups tabs and params
        if (\common\helpers\Extensions::isCustomerGroupsAllowed() && count($this->view->groups)>0) {
          $this->view->groups_m = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), $this->view->groups);
          $tmp = [];
          foreach ($this->view->groups_m as $value) {
            $value['id'] = $value['groups_id'];
            $value['title'] = $value['groups_name'];
            $value['def_data'] = ['groups_id' => $value['id']];
            unset($value['groups_name']);
            unset($value['groups_id']);
            $tmp[] = $value;
          }
          $this->view->price_tabs[] = $tmp;
          unset($tmp);
          $this->view->price_tabparams[] = [
              'cssClass' => 'tabs-groups', // add to tabs and tab-pane
              //'callback' => 'productPriceBlock', // smarty function which will be called before children tabs , data passed as params params
              'callback_bottom' => '',
              'tabs_type' => 'lTab',
              'aboveTabs' => (count($this->view->groups_m)<(1+count($this->view->groups))? 'productedit/edit-price-link.tpl':''),
              'all_hidden' => (count($this->view->groups_m)==1),
              'maxHeight' => '400px',
          ];
        }

        $values_query = tep_db_query("select po.products_options_id, po.products_options_name, pov.products_options_values_id, pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " p2p, " . TABLE_PRODUCTS_OPTIONS ." po where po.language_id = '" . $languages_id . "' and po.products_options_id in ('" . implode("', '", $products_options_ids) . "') and pov.products_options_values_id = p2p.products_options_values_id and p2p.products_options_id = po.products_options_id and  pov.products_options_values_id in ('" . implode("','" , $products_options_values_ids) . "') and pov.language_id = '" . $languages_id . "' order by po.products_options_name, po.products_options_id, pov.products_options_values_sort_order, pov.products_options_values_name, pov.products_options_values_id ");
        while ($values = tep_db_fetch_array($values_query)) {
          if ($products_options_id != $values['products_options_id']) {
            if ($products_options_id) {
              $is_virtual_option = \common\helpers\Attributes::is_virtual_option($products_options_id);
              /** @var \common\extensions\Inventory\Inventory $ext */
              if (!$inventoryDisable && ($ext = \common\helpers\Extensions::isAllowed('Inventory')) && !$is_virtual_option) {
                $ret[] = ['data' => $ext::getProductNewOption($products_id, $attributes),
                        'is_virtual_option' => $is_virtual_option,
                        'products_options_id' => $attributes[0]['products_options_id'],
                        'products_options_values_id' => $attributes[0]['values'][0]['products_options_values_id']
                       ];
              } else {
                $ret[] = ['data' => $this->render('product-new-option.tpl', [
                                                'products_id' => $products_id,
                                                'default_currency' => $currencies->currencies[DEFAULT_CURRENCY],
                                                'currencies' => $currencies,
                                                'attributes' => $attributes,
                                    ]),
                        'is_virtual_option' => $is_virtual_option,
                        'products_options_id' => $attributes[0]['products_options_id'],
                        'products_options_values_id' => $attributes[0]['values'][0]['products_options_values_id']
                       ];
              }
              $attributes = [];
            }

            $products_options_id = $values['products_options_id'];
            $attributes[0] = [
              'is_virtual_option' => \common\helpers\Attributes::is_virtual_option($products_options_id),
              'products_options_id' => $values['products_options_id'],
              'net_price_formatted' => $currencies->display_price(0, 0, 1 ,false),
              'gross_price_formatted' => $currencies->display_price(0, 0, 1 ,false),
              'products_options_name' => htmlspecialchars($values['products_options_name']),
              'values' => [],
            ];

          }
          $attributes[0]['values'][] = [
              'products_options_values_id' => $values['products_options_values_id'],
              'net_price_formatted' => $currencies->display_price(0, 0, 1 ,false),
              'gross_price_formatted' => $currencies->display_price(0, 0, 1 ,false),
              'products_options_values_name' => htmlspecialchars($values['products_options_values_name'])
          ];

        }
        if ($products_options_id) {
          $is_virtual_option = \common\helpers\Attributes::is_virtual_option($products_options_id);
          /** @var \common\extensions\Inventory\Inventory $ext */
          if (!$inventoryDisable && ($ext = \common\helpers\Extensions::isAllowed('Inventory')) && !$is_virtual_option) {
            $ret[] = ['data' => $ext::getProductNewOption($products_id, $attributes),
                    'is_virtual_option' => $is_virtual_option,
                    'products_options_id' => $attributes[0]['products_options_id'],
                    'products_options_values_id' => $attributes[0]['values'][0]['products_options_values_id']
                   ];
          } else {
            $ret[] = ['data' => $this->render('product-new-option.tpl', [
                                            'products_id' => $products_id,
                                            'default_currency' => $currencies->currencies[DEFAULT_CURRENCY],
                                            'currencies' => $currencies,
                                            'attributes' => $attributes,
                                ]),
                    'is_virtual_option' => $is_virtual_option,
                    'products_options_id' => $attributes[0]['products_options_id'],
                    'products_options_values_id' => $attributes[0]['values'][0]['products_options_values_id']
                   ];
          }
        }

        return json_encode($ret);

    }

    public function actionProductInventoryBox() {
        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $this->view->tax_classes = ['0' => TEXT_NONE];
        $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
        while ($tax_class = tep_db_fetch_array($tax_class_query)) {
            $this->view->tax_classes[$tax_class['tax_class_id']] = $tax_class['tax_class_title'];
        }

        /* @var $ext  \common\extensions\Inventory\Inventory */
        if ($ext = \common\helpers\Extensions::isAllowed('Inventory')) {
            return $ext::productInventoryBox();
        }
    }

    public function actionSelectedAttributes()
    {
        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;
        $products_id = (int) Yii::$app->request->post('products_id');
        $inventoryDisable = Yii::$app->request->post('without_inventory',0)==1;
        $pInfo = \common\models\Products::findOne($products_id);
        if ( !$pInfo ){
            $pInfo = new \common\models\Products();
            $pInfo->loadDefaultValues();
            $pInfo->without_inventory = $inventoryDisable?1:0;
        }
        $pInfo = new \objectInfo(array_merge($pInfo->getAttributes(), [
            'options_templates_id' => (int) Yii::$app->request->post('options_templates_id', 0),
            'products_tax_class_id' => (int) Yii::$app->request->post('products_tax_class_id', 0),
        ]));

        $this->view->groups = [];
        /** @var \common\extensions\UserGroups\UserGroups $ext */
        if ($ext = \common\helpers\Extensions::isAllowed('UserGroups')) {
            $ext::getGroups();
        }

        $this->view->images = \common\helpers\Product::getProductImages($products_id);

        $ret = [];
        $currencies = Yii::$container->get('currencies');
        $this->view->defaultCurrency = $currencies->currencies[DEFAULT_CURRENCY]['id'];
        $this->view->useMarketPrices = (USE_MARKET_PRICES == 'True');
        $this->view->price_tabs = $this->view->price_tabparams = [];
////currencies tabs and params
        if ($this->view->useMarketPrices) {
            $this->view->currenciesTabs = [];
            foreach ($currencies->currencies as $value) {
                $value['def_data'] = ['currencies_id' => $value['id']];
                $value['title'] = $value['symbol_left'] . ' ' . $value['code'] . ' ' . $value['symbol_right'];
                $this->view->currenciesTabs[] = $value;
            }
            $this->view->price_tabs[] = $this->view->currenciesTabs;
            $this->view->price_tabparams[] =  [
                'cssClass' => 'tabs-currencies',
                'tabs_type' => 'hTab',
                //'include' => 'test/test.tpl',
            ];
        }

        //// groups tabs and params
        if (\common\helpers\Extensions::isCustomerGroupsAllowed() && count($this->view->groups)>0) {
            $this->view->groups_m = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), $this->view->groups);
            $tmp = [];
            foreach ($this->view->groups_m as $value) {
                $value['id'] = $value['groups_id'];
                $value['title'] = $value['groups_name'];
                $value['def_data'] = ['groups_id' => $value['id']];
                unset($value['groups_name']);
                unset($value['groups_id']);
                $tmp[] = $value;
            }
            $this->view->price_tabs[] = $tmp;
            unset($tmp);
            $this->view->price_tabparams[] = [
                'cssClass' => 'tabs-groups', // add to tabs and tab-pane
                //'callback' => 'productPriceBlock', // smarty function which will be called before children tabs , data passed as params params
                'callback_bottom' => '',
                'tabs_type' => 'lTab',
                'aboveTabs' => (count($this->view->groups_m)<(1+count($this->view->groups))? 'productedit/edit-price-link.tpl':''),
                'all_hidden' => (count($this->view->groups_m)==1),
                'maxHeight' => '400px',
            ];
        }

        $attributes = new ViewAttributes($pInfo);
        $attributes->populateView($this->view);

        /** @var \common\extensions\Inventory\Inventory $ext */
        if (!$inventoryDisable && $ext = (\common\helpers\Extensions::isAllowed('Inventory')) ) {
            return $ext::productAttributesBox($pInfo);
        }else{
            return $this->render('product-new-option.tpl', [
                'attributes' => $this->view->selectedAttributes,
                'products_id' => $pInfo->products_id,
                'currencies' => $currencies,
            ]);
        }
    }

    public function actionProductNewAttribute() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = (int) Yii::$app->request->post('products_id');
        $inventoryDisable = Yii::$app->request->post('without_inventory',0)==1;
/*
        if (count(\common\helpers\Product::getChildArray($products_id)) > 0) {
            return json_encode([]);
        }
*/
        /*arrays of new options & values */
        $products_options_ids = array_unique( explode(',', Yii::$app->request->post('products_options_id')));
        $products_options_values_ids = array_unique( explode(',', Yii::$app->request->post('products_options_values_id')));
        foreach ($products_options_ids as $k => $v) {
          if (intval($v)==0) {
            unset($products_options_ids[$k]);
          } else {
            $products_options_ids[$k] = intval($v);
          }
        }
        foreach ($products_options_values_ids as $k => $v) {
          if (intval($v)==0) {
            unset($products_options_values_ids[$k]);
          } else {
            $products_options_values_ids[$k] = intval($v);
          }
        }

        $this->view->groups = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
            $ext::getGroups();
        }

        $this->view->images = \common\helpers\Product::getProductImages($products_id);

        $ret = [];
        $currencies = Yii::$container->get('currencies');

/// re-arrange data arrays for design templates
// init price tabs
        $this->view->useMarketPrices = (USE_MARKET_PRICES == 'True');
        $this->view->price_tabs = $this->view->price_tabparams = [];
////currencies tabs and params
        if ($this->view->useMarketPrices) {
          $this->view->currenciesTabs = [];
          foreach ($currencies->currencies as $value) {
            $value['def_data'] = ['currencies_id' => $value['id']];
            $value['title'] = $value['symbol_left'] . ' ' . $value['code'] . ' ' . $value['symbol_right'];
            $this->view->currenciesTabs[] = $value;
          }
          $this->view->price_tabs[] = $this->view->currenciesTabs;
          $this->view->price_tabparams[] =  [
              'cssClass' => 'tabs-currencies',
              'tabs_type' => 'hTab',
              //'include' => 'test/test.tpl',
          ];
        }

    //// groups tabs and params
        if (\common\helpers\Extensions::isCustomerGroupsAllowed() && count($this->view->groups)>0) {
          $this->view->groups_m = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), $this->view->groups);
          $tmp = [];
          foreach ($this->view->groups_m as $value) {
            $value['id'] = $value['groups_id'];
            $value['title'] = $value['groups_name'];
            $value['def_data'] = ['groups_id' => $value['id']];
            unset($value['groups_name']);
            unset($value['groups_id']);
            $tmp[] = $value;
          }
          $this->view->price_tabs[] = $tmp;
          unset($tmp);
          $this->view->price_tabparams[] = [
              'cssClass' => 'tabs-groups', // add to tabs and tab-pane
              //'callback' => 'productPriceBlock', // smarty function which will be called before children tabs , data passed as params params
              'callback_bottom' => '',
              'tabs_type' => 'lTab',
              'aboveTabs' => (count($this->view->groups_m)<(1+count($this->view->groups))? 'productedit/edit-price-link.tpl':''),
              'all_hidden' => (count($this->view->groups_m)==1),
              'maxHeight' => '400px',
          ];
        }

        $values_query = tep_db_query("select p2p.products_options_id, pov.products_options_values_id, pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " p2p where pov.products_options_values_id = p2p.products_options_values_id and p2p.products_options_id in ('" . implode("','", $products_options_ids) . "') and  pov.products_options_values_id in ('" . implode("','", $products_options_values_ids) . "') and pov.language_id = '" . $languages_id . "' order by pov.products_options_values_sort_order, pov.products_options_values_name ");
        while ($values = tep_db_fetch_array($values_query)) {
            $values['net_price_formatted'] =  $currencies->display_price(0, 0, 1 ,false);
            $values['gross_price_formatted'] =  $currencies->display_price(0, 0, 1 ,false);
            $option[0] = $values;
            $is_virtual_option = \common\helpers\Attributes::is_virtual_option($values['products_options_id']);
            /** @var \common\extensions\Inventory\Inventory */
            if (!$inventoryDisable && ($ext = \common\helpers\Extensions::isAllowed('Inventory')) && !$is_virtual_option) {
              $ret[] = ['data' => $ext::getProductNewAttribute($products_id, $option, $values['products_options_id']),
                        'is_virtual_option' => $is_virtual_option,
                        'products_options_values_id' => $values['products_options_values_id'],
                        'products_options_id' => $values['products_options_id']
                       ];
            } else {
              $ret[] = ['data' => $this->render('product-new-attribute.tpl', [
                                              'options' => $option,
                                              'products_id' => $products_id,
                                              'default_currency' => $currencies->currencies[DEFAULT_CURRENCY],
                                              'currencies' => $currencies,
                                              'products_options_id' => $values['products_options_id'],
                                ]),
                        'is_virtual_option' => $is_virtual_option,
                        'products_options_values_id' => $values['products_options_values_id'],
                        'products_options_id' => $values['products_options_id']
                       ];
          }
        }

        return json_encode($ret);
    }

    public function actionProductNewImage($id) {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $pid = (int)Yii::$app->request->get('pid');

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;
        $languages = \common\helpers\Language::get_languages();

        $languageNames = [];
        $languageNames[0] = '';
        foreach ($languages as $language) {
            $languageNames[$language['id']] = $language['name'];
        }

        $video = [];
        $video[0] = '';
        if ($pid > 0) {
            $productsVideos = \common\models\ProductsVideos::find()->where(['products_id' => $pid])->asArray()->all();
            foreach ($productsVideos as $productsVideo) {
                $video[$languageNames[$productsVideo['language_id']]][$productsVideo['video_id']] = 'Video#' . $productsVideo['video_id'];
            }
        }

        $attributes = [];
        $options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_sort_order, products_options_name");
        if (tep_db_num_rows($options_query)) {
            $options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_sort_order, products_options_name");
            while ($options = tep_db_fetch_array($options_query)) {
                $values_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " p2p where pov.products_options_values_id = p2p.products_options_values_id and p2p.products_options_id = '" . $options['products_options_id'] . "' and pov.language_id = '" . $languages_id . "' order by products_options_values_sort_order, products_options_values_name");
                $option = [];
                while ($values = tep_db_fetch_array($values_query)) {
                    $option[] = [
                        'value' => $values['products_options_values_id'],
                        'name' => htmlspecialchars($values['products_options_values_name'])
                    ];
                }
                $attributes[] = [
                    'id' => $options['products_options_id'],
                    'label' => htmlspecialchars($options['products_options_name']),
                    'options' => $option,
                ];
            }
        }
        $this->view->attributes = $attributes;

        $image_path = \Yii::getAlias('@web');
        $image_path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        $file_name = Yii::$app->request->get('name');

        // {{
        $use_external_images = false;// !!Yii::$app->request->get('external',0);
        $_ext_images_array = [];
        foreach (Images::getImageTypes() as $image_type) {
            $_ext_images_array[$image_type['image_types_id']] = [
                'image_types_id' => $image_type['image_types_id'],
                'image_types_name' => $image_type['image_types_name'],
                'image_size' => $image_type['image_types_x'] . 'x' . $image_type['image_types_y'],
                'image_url' => '',
            ];
        }
        // }}

        $Item = [
            'products_images_id' => 0,
            'default_image' => 0,
            'image_status' => 1,
            'image_name' => (empty($file_name) ? '' : $image_path . $file_name),
            // for language_id = 0
            'image_title' => '',
            'image_alt' => '',
            'orig_file_name' => $file_name,
            'use_origin_image_name' => 0,
            'hash_file_name' => '',
            'file_name' => '',
            'alt_file_name' => '',
            'no_watermark' => 0,
            'use_external_images' => $use_external_images,
            'external_image_original' => '',
            'external_images' => $_ext_images_array,
        ];
        $description = [];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $description[$i] = [
                'key' => ($i + 1),
                'id' => $languages[$i]['id'],
                'code' => $languages[$i]['code'],
                'name' => $languages[$i]['name'],
                'logo' => $languages[$i]['image'],
                'image_title' => '',
                'image_alt' => '',
                'orig_file_name' => '',
                'use_origin_image_name' => 0,
                'hash_file_name' => '',
                'file_name' => '',
                'alt_file_name' => '',
                'no_watermark' => 0,
                'image_name' => '',
                'use_external_images' => $use_external_images,
                'external_image_original' => '',
                'external_images' => $_ext_images_array,
            ];
        }

        return $this->render('product-new-image.tpl', [
                    'Item' => $Item,
                    'description' => $description,
                    'Key' => $id,
                    'video' => $video,
        ]);
    }

    /**
     * check & returns data from marketing tabs if any
     * @field ['db' => 'products_price_discount_pack_unit', db field name --- not required at all :(
     * 'postreindex' => 'discount_qty_pack_unit', POST - change array keys to
     * 'post' => 'discount_price_pack_unit', POST key. Required!
     * 'flag' => 'qty_discount_status_pack_unit', POST switcher flag (1 - on!!! someone use yes o_O )
     * 'f' => ['self', 'formatDiscountString']] - validator - callback
     */
    private static function getFromPostArrays($field, $curr_id, $group_id=0) {
        return \backend\models\ProductEdit\PostArrayHelper::getFromPostArrays($field, $curr_id, $group_id);
    }

    public function actionProductSubmit() {
        if (false === \common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT'])) {
            die();
        }
        $languages_id = \Yii::$app->settings->get('languages_id');
        $isNewProduct = false;

        \common\helpers\Translation::init('admin/categories');

        $currencies = Yii::$container->get('currencies');

        $path = \Yii::getAlias('@webroot');
        $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        $selectedDepartmentId =  (int)Yii::$app->request->post('department_id', 0);
        $old_products_id = $products_id = (int) Yii::$app->request->post('products_id');

        if (\common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
            $logger = new \common\extensions\ReportChangesHistory\classes\Logger();
            $beforeObject = new \common\api\Classes\Product();
            $beforeObject->load($products_id);
            $logger->setBeforeObject($beforeObject);
            unset($beforeObject);
        }

        if ((int) $products_id > 0) {
            $action = 'update_product';
        } else {
            $action = 'insert_product';
        }

        $TabAccess = $this->ProductEditTabAccess;

        $currencies_ids = $groups = $groups_price = [];
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
          $groups = $ext::getGroupsArray();
          if (!isset($groups['0'])) {
            $groups['0'] = ['groups_id' => 0, 'per_product_price' => 1];
          }
          $groups_price = array_filter($groups, function($e) { return $e['per_product_price'];} );
          if ($groups_price==$groups) {
            $groups_price = null; //php8: was unset($groups_price);
          }
        }
        $_def_curr_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];

        if (USE_MARKET_PRICES == 'True') {
          foreach ($currencies->currencies as $key => $value)  {
            $currencies_ids[$currencies->currencies[$key]['id']] = $currencies->currencies[$key]['id'];
          }
        } else {
          $currencies_ids[$_def_curr_id] = '0'; /// here is the post and db currencies_id are different.
        }

        if ($action=='update_product'){
            $productModel = \common\models\Products::findOne((int)$products_id);
        }else{
            $productModel = new \common\models\Products();
            $productModel->loadDefaultValues();
            $productModel->parent_products_id = intval(Yii::$app->request->post('parent_products_id',0));
            //['products_model','products_price','products_price_rrp','products_weight'];
            //$productModel->
        }

        $_products_id_price = intval(Yii::$app->request->post('products_id_price',-1));
        if ( $_products_id_price>=0 ) { $productModel->products_id_price = $_products_id_price; }

        $TabAccess->setProduct($productModel);
        /**
         * Main details
         */
        $sql_data_array = [];
        if ( $TabAccess->tabDataSave('TEXT_MAIN_DETAILS') ) {
            if ($productModel->parent_products_id) {
                $mainDetails = new \backend\models\ProductEdit\SaveSubProductMainDetails($productModel);
            }else {
                $mainDetails = new \backend\models\ProductEdit\SaveMainDetails($productModel);
            }
            $mainDetails->prepareSave();
        }

        /**
         * Size and Packaging
         */
        if ($TabAccess->tabDataSave('TEXT_SIZE_PACKAGING')) {
            $packaging = new \backend\models\ProductEdit\SaveSizeAndPackaging($productModel);
            $packaging->prepareSave();
        }


        if ($TabAccess->tabDataSave('TAB_BUNDLES')) {
            if (\common\helpers\Acl::checkExtensionAllowed('ProductBundles')) {
                $sql_data_array['is_bundle'] = tep_db_prepare_input(Yii::$app->request->post('is_bundle'));
                //$sql_data_array['products_sets_price'] = tep_db_prepare_input(Yii::$app->request->post('products_sets_price');
                $sql_data_array['use_sets_discount'] = tep_db_prepare_input(Yii::$app->request->post('use_sets_discount'));
                $sql_data_array['products_sets_discount'] = tep_db_prepare_input(Yii::$app->request->post('products_sets_discount'));
                $sql_data_array['products_sets_price_formula'] = tep_db_prepare_input(Yii::$app->request->post('products_sets_price_formula'));
            } elseif (Yii::$app->request->post('is_bundle') == 0) { // allow to reset bundle
                $sql_data_array['is_bundle'] = tep_db_prepare_input(Yii::$app->request->post('is_bundle'));
            }
        }

        $categories_id = (int) Yii::$app->request->post('categories_id');
        if ($action == 'insert_product') {
            $sql_data_array['products_date_added'] = new Expression('NOW()');
            //tep_db_perform(TABLE_PRODUCTS, $sql_data_array);
            $productModel->setAttributes($sql_data_array, false);
            $productModel->save(false);
            $productModel->refresh();
            if ($productModel->parent_products_id) {
                $productModel->products_id_stock = $productModel->parent_products_id;
                $productModel->products_id_price = $productModel->parent_products_id;
            }else {
                $productModel->products_id_stock = $productModel->products_id;
                $productModel->products_id_price = $productModel->products_id;
            }
            //$products_id = tep_db_insert_id();
            $products_id = $productModel->products_id;
            $isNewProduct = true;
            tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $products_id . "', '" . (int) $categories_id . "')");

            // {{ mark_parent_as_master
            if (Yii::$app->request->post('mark_parent_as_master',0) && $productModel->parent_products_id){
                if ($parentModel = \common\models\Products::findOne($productModel->parent_products_id)){
                    $parentModel->is_listing_product = 0;
                    $parentModel->save(false);
                }
            }
            // }}
        } elseif ($action == 'update_product') {
            $sql_data_array['products_last_modified'] = new Expression('NOW()');
            //tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "'");
            $productModel->setAttributes($sql_data_array, false);
            $productModel->save(false);
            $productModel->refresh();

            $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "'");
            $check = tep_db_fetch_array($check_query);
            if ($check['total'] < '1') {
                tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $products_id . "', '" . (int) $categories_id . "')");
            }
        }
        if ($TabAccess->tabDataSave('TAB_NOTES')) {
            $productsNotes = $this->productsNotesService->findByProductId($productModel->products_id);
            if ($productsNotes) {
                $productsNoteForm = [$this->productsNotesService->newProductsNoteByArray()];
                $productsNoteSave = [];
                foreach (Yii::$app->request->post($productsNoteForm[0]->formName(), []) as $i => $data) {
                    /** @var ProductsNotes $productsNoteSave[$i] */
                    $productsNoteSave[$i] = $this->productsNotesService->getById($i);
                    if ($productsNoteSave[$i]->load($data, '') && $productsNoteSave[$i]->validate()) {
                        $this->productsNotesService->save($productsNoteSave[$i]);
                    } else {
                        unset($productsNoteSave[$i]);
                    }
                }
                $deleteNotes = array_diff(
                    array_keys($productsNotes),
                    array_keys($productsNoteSave)
                );
                foreach ($deleteNotes as $note) {
                    $this->productsNotesService->remove($productsNotes[$note]);
                }
            }
            $newNotePostForm = [$this->productsNotesService->newProductsNoteFormByArray()];
            foreach (Yii::$app->request->post($newNotePostForm[0]->formName(), []) as $data) {
                $newNoteForm = $this->productsNotesService->newProductsNoteFormByArray();
                if ($newNoteForm->load($data, '') && $newNoteForm->validate()) {
                    $newProductNote = $this->productsNotesService->newProductNote($productModel->products_id, $newNoteForm->note);
                    $this->productsNotesService->save($newProductNote);
                }
            }
        }
        if ($TabAccess->tabDataSave('TEXT_SEO')) {
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                $ext::saveProductLinks($products_id, $_POST);
            }
        }

        // Update stock quantity
        /*if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_EDIT', 'TEXT_MAIN_DETAILS'])) {
            $products_quantity_update = (int) Yii::$app->request->post('products_quantity_update');
            $products_quantity_update_prefix = (Yii::$app->request->post('products_quantity_update_prefix') == '-' ? '-' : '+');
            if ($products_quantity_update > 0) {
                global $login_id;
                $warehouse_id = (int) Yii::$app->request->post('warehouse_id');
                $w_suppliers_id = (int)Yii::$app->request->post('w_suppliers_id', 0);
                $stock_comments = Yii::$app->request->post('stock_comments');
                $location_id = 0;
                $locationIds = Yii::$app->request->post('box_location');
                if (is_array($locationIds)) {
                    foreach ($locationIds as $lid) {
                        if ($lid > 0) {
                            $location_id = $lid;
                        }
                    }
                }
                //\common\helpers\Product::log_stock_history_before_update($products_id, $products_quantity_update, $products_quantity_update_prefix, ['warehouse_id' => $warehouse_id, 'comments' => TEXT_MANUALL_STOCK_UPDATE . (trim($stock_comments) != '' ? ': ' . $stock_comments : ''), 'admin_id' => $login_id]);
                if ($warehouse_id > 0) {
                    $parameters = [
                        'admin_id' => $login_id,
                        'comments' => (TEXT_MANUALL_STOCK_UPDATE . (trim($stock_comments) != '' ? ': ' . $stock_comments : ''))
                    ];
                    \common\helpers\Warehouses::update_products_quantity($products_id, $warehouse_id, $products_quantity_update, $products_quantity_update_prefix, $w_suppliers_id, $location_id, $parameters);
                    \common\helpers\Warehouses::get_allocated_stock_quantity($products_id);
                    \common\helpers\Warehouses::get_temporary_stock_quantity($products_id);
                } else {
                    tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity " . $products_quantity_update_prefix . $products_quantity_update . " where products_id = '" . (int) $products_id . "'");
                }
            }
        }*/

        //Give away
        if ($TabAccess->tabDataSave('TEXT_MARKETING')) {
            $marketingData = new \backend\models\ProductEdit\SaveMarketingData($productModel);
            $marketingData->prepareSave();
        }

        //Gift wrap
        if ($TabAccess->tabDataSave('TEXT_MAIN_DETAILS')) {
          if ($old_products_id>0) {
            if ($groups_price) {
              \common\models\GiftWrapProducts::deleteAll([
                'products_id' => (int) $old_products_id,
                'groups_id' => array_keys($groups_price),
              ]);
            } else {
              tep_db_query("delete from " . TABLE_GIFT_WRAP_PRODUCTS . " where products_id = '" . (int) $old_products_id . "'");
            }
          }
          $gift_wrap = Yii::$app->request->post('gift_wrap', 0);
          if (is_array($gift_wrap) || $gift_wrap>0) {
            if (is_array($gift_wrap) && (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed())) {
              foreach ($currencies_ids as $post_currencies_id => $currencies_id)  {
                foreach (($groups_price?$groups_price:$groups) as $groups_id => $non) {
                  $sql_data_array = ['products_id' => (int)$products_id,
                                     'groups_id' => (int)$groups_id,
                                     'currencies_id' => (int)$currencies_id,
                                    ];
                  $field = ['db' => 'gift_wrap_price', 'dbdef' => 0, 'post' => 'gift_wrap_price', 'flag' => 'gift_wrap'];
                  if (self::getFromPostArrays(['post' => 'gift_wrap'], (int)$post_currencies_id, (int)$groups_id)==1) {
                    $sql_data_array[$field['db']] = self::getFromPostArrays($field, (int)$post_currencies_id, (int)$groups_id);
                    tep_db_perform(TABLE_GIFT_WRAP_PRODUCTS, $sql_data_array);
                  }
                }
              }

            } else {
              $sql_data_array = ['products_id' => (int)$products_id,
                                'groups_id' => 0,
                                'currencies_id' => 0,
                                ];
              $field = ['db' => 'gift_wrap_price', 'dbdef' => 0, 'post' => 'gift_wrap_price', 'flag' => 'gift_wrap'];
              if (self::getFromPostArrays(['post' => 'gift_wrap'], 0)==1) {
                $sql_data_array[$field['db']] = self::getFromPostArrays($field, 0);
                tep_db_perform(TABLE_GIFT_WRAP_PRODUCTS, $sql_data_array);
              }
            }
          }
        }

        // Featured
        if ($TabAccess->tabDataSave('TEXT_MAIN_DETAILS')) {
            $featured = (int) Yii::$app->request->post('featured');
            $featured_expires_date = Yii::$app->request->post('featured_expires_date');
            if ($featured == 0) {
                tep_db_query("delete from " . TABLE_FEATURED . " where products_id = '" . (int) $products_id . "'");
            } else {
                if (!empty($featured_expires_date)) {
                    $featured_expires_date = \common\helpers\Date::prepareInputDate($featured_expires_date);
                }
                $check_data = tep_db_query("select * from " . TABLE_FEATURED . " where products_id ='" . (int) $products_id . "'");
                if (tep_db_num_rows($check_data) > 0) {
                    $check = tep_db_fetch_array($check_data);
                    tep_db_query("update " . TABLE_FEATURED . " set featured_last_modified = now(), status = '1', expires_date = '" . tep_db_input($featured_expires_date) . "' where featured_id = '" . (int) $check['featured_id'] . "'");
                } else {
                    tep_db_query("insert into " . TABLE_FEATURED . " (products_id, featured_date_added, expires_date, status, affiliate_id) values ('" . (int) $products_id . "', now(), '" . tep_db_input($featured_expires_date) . "', '1', '0')");
                }
            }
        }

        /**
         * Price and Cost
         */
        if  ($TabAccess->tabDataSave('TEXT_PRICE_COST_W')) {
          $productModel->disable_discount = intval(Yii::$app->request->post('disable_discount',0));
          ///1 nya in product table: shipping_surcharge_price etc
          $sql_data_array = [];
          $fields = [
                      ['db' => 'products_price', 'dbdef' => 0, 'post' => 'products_group_price'],
                      ['db' => 'products_price_full', 'dbdef' => 0, 'post' => 'products_price_full'],
                      ['db' => 'products_price_rrp', 'dbdef' => 0, 'post' => 'products_price_rrp'],
                      ['db' => 'products_tax_class_id', 'dbdef' => 0, 'post' => 'products_tax_class_id'],
                      ['db' => 'products_price_pack_unit', 'dbdef' => -2, 'post' => 'products_group_price_pack_unit', 'f' => ['self', 'defGroupPrice']],
                      ['db' => 'products_price_packaging', 'dbdef' => -2, 'post' => 'products_group_price_packaging', 'f' => ['self', 'defGroupPrice']],
                      ['db' => 'supplier_price_manual', 'dbdef' => 'null', 'post' => 'supplier_auto_price'],
                      ['db' => 'shipping_surcharge_price', 'dbdef' => 0, 'post' => 'shipping_surcharge_price', 'flag' => 'shipping_surcharge'],
                      ['db' => 'bonus_points_price', 'dbdef' => 0, 'post' => 'bonus_points_price', 'flag' => 'bonus_points_status'],
                      ['db' => 'bonus_points_cost', 'dbdef' => 0, 'post' => 'bonus_points_cost', 'flag' => 'bonus_points_status'],
                      ['db' => 'products_price_discount', 'dbdef' => '', 'postreindex' => 'discount_qty', 'post' => 'discount_price', 'flag' => 'qty_discount_status', 'f' => ['self', 'formatDiscountString']],
                      ['db' => 'products_price_discount_pack_unit', 'dbdef' => '', 'postreindex' => 'discount_qty_pack_unit', 'post' => 'discount_price_pack_unit', 'flag' => 'qty_discount_status_pack_unit', 'f' => ['self', 'formatDiscountString']],
                      ['db' => 'products_price_discount_packaging', 'dbdef' => '', 'postreindex' => 'discount_qty_packaging', 'post' => 'discount_price_packaging', 'flag' => 'qty_discount_status_packaging', 'f' => ['self', 'formatDiscountString']],
            ];
          //products_weight - saved above
          ///????products_sets_price

          foreach ($fields as $field) {
            $sql_data_array[$field['db']] = self::getFromPostArrays($field, $_def_curr_id, 0);
          }
          $sql_data_array['supplier_price_manual'] = $sql_data_array['supplier_price_manual']=='1'?0:1;
          // reset matched with current config
          if ( ($sql_data_array['supplier_price_manual']==1 && SUPPLIER_UPDATE_PRICE_MODE=='Manual') || ($sql_data_array['supplier_price_manual']==0 && SUPPLIER_UPDATE_PRICE_MODE=='Auto') ) {
            $sql_data_array['supplier_price_manual'] = 'null';
          }

          tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "'");
//2 group prices specials. etc

          if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
            if ($groups_price ?? null) {
              \common\models\ProductsPrices::deleteAll([
                'products_id' => (int) $old_products_id,
                'groups_id' => array_keys($groups_price),
              ]);
            } else {
              tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "'");
            }

            foreach ($currencies_ids as $post_currencies_id => $currencies_id)  {
              foreach (($groups_price?$groups_price:$groups) as $groups_id => $non) {
                $sql_data_array = ['products_id' => (int)$products_id,
                                   'groups_id' => (int)$groups_id,
                                   'currencies_id' => (int)$currencies_id,
                  ];
                $fields = [
                            ['db' => 'products_sets_discount', 'dbdef' => 0, 'post' => 'products_group_sets_discount'],
                            ['db' => 'products_group_price', 'dbdef' => ($groups_id==0?0:-2), 'post' => 'products_group_price'],
                            ['db' => 'bonus_points_price', 'dbdef' => 0, 'post' => 'bonus_points_price', 'flag' => 'bonus_points_status'],
                            ['db' => 'bonus_points_cost', 'dbdef' => 0, 'post' => 'bonus_points_cost', 'flag' => 'bonus_points_status'],
                            ['db' => 'products_group_price_pack_unit', 'dbdef' => -2, 'post' => 'products_group_price_pack_unit', 'f' => ['self', 'defGroupPrice']],
                            ['db' => 'products_group_price_packaging', 'dbdef' => -2, 'post' => 'products_group_price_packaging', 'f' => ['self', 'defGroupPrice']],
                            ['db' => 'supplier_price_manual', 'dbdef' => 'null', 'post' => 'supplier_auto_price'],
                            ['db' => 'shipping_surcharge_price', 'dbdef' => 0, 'post' => 'shipping_surcharge_price', 'flag' => 'shipping_surcharge'],
                            ['db' => 'products_group_discount_price', 'dbdef' => '', 'postreindex' => 'discount_qty', 'post' => 'discount_price', 'flag' => 'qty_discount_status', 'f' => ['self', 'formatDiscountString']],
                            ['db' => 'products_group_discount_price_pack_unit', 'dbdef' => '', 'postreindex' => 'discount_qty_pack_unit', 'post' => 'discount_price_pack_unit', 'flag' => 'qty_discount_status_pack_unit', 'f' => ['self', 'formatDiscountString']],
                            ['db' => 'products_group_discount_price_packaging', 'dbdef' => '', 'postreindex' => 'discount_qty_packaging', 'post' => 'discount_price_packaging', 'flag' => 'qty_discount_status_packaging', 'f' => ['self', 'formatDiscountString']],
                  ];
                //2do products_price_configurator

                foreach ($fields as $field) {
                  $sql_data_array[$field['db']] = self::getFromPostArrays($field, (int)$post_currencies_id, (int)$groups_id);
                }
                if ( $groups_id==0 ) {
                    // posted auto, make manual
                    $sql_data_array['supplier_price_manual'] = $sql_data_array['supplier_price_manual']=='1'?0:1;
                    // reset matched with current config
                    if ( ($sql_data_array['supplier_price_manual']==1 && SUPPLIER_UPDATE_PRICE_MODE=='Manual') || ($sql_data_array['supplier_price_manual']==0 && SUPPLIER_UPDATE_PRICE_MODE=='Auto') ) {
                        unset($sql_data_array['supplier_price_manual']);
                    }
                }else{
                    unset($sql_data_array['supplier_price_manual']);
                }

                tep_db_perform(TABLE_PRODUCTS_PRICES, $sql_data_array);
              }
            }
          }

          \common\helpers\Specials::saveFromPost($products_id, 1);

          if ($ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryOptions', 'allowed')) {
                $ext::saveProduct($products_id);
            }
        }

        if ( true /*\common\helpers\Acl::rule([])*/) {
////////////////////////////////////
            $_platform_list = \common\classes\platform::getProductsAssignList();
            $admin_available_platform_ids = \yii\helpers\ArrayHelper::map($_platform_list,'id','id');
            $all_platform_ids = \yii\helpers\ArrayHelper::map(\common\models\Platforms::getPlatformsByType("non-virtual")->select('platform_id')->asArray()->all(),'platform_id','platform_id');

            $assign_platform = array();
            if (count($_platform_list) == 1) {
                $assign_platform[] = (int) $_platform_list[0]['id'];
            } else {
                $assign_platform = array_map('intval', Yii::$app->request->post('platform', array()));
            }

            $dbAssignedPlatforms = \common\models\PlatformsProducts::find()
                ->where(['products_id' => (int) $products_id])
                ->indexBy('platform_id')
                ->all();

            foreach (array_keys($dbAssignedPlatforms) as $_platform_id){
                if ( isset($all_platform_ids[$_platform_id]) && !isset($admin_available_platform_ids[$_platform_id]) ){
                    unset($dbAssignedPlatforms[$_platform_id]);
                }
            }

            foreach ($assign_platform as $_platform_id){
                if ( isset($dbAssignedPlatforms[$_platform_id]) ){
                    unset($dbAssignedPlatforms[$_platform_id]);
                }else {
                    $newAssignToPlatform = new \common\models\PlatformsProducts([
                        'products_id' => (int) $products_id,
                        'platform_id' => (int) $_platform_id,
                    ]);
                    $newAssignToPlatform->loadDefaultValues();
                    $newAssignToPlatform->save(false);
                }
            }
            foreach ($dbAssignedPlatforms as $notUpdatedLinkModel){
                $notUpdatedLinkModel->delete();
            }

            $activate_parent_categories = Yii::$app->request->post('activate_parent_categories', array());
            $__assigned_platform_check = array_flip($assign_platform);
            foreach (\common\classes\platform::getCategoriesAssignList() as $__category_platform) {
                if (!isset($activate_parent_categories[$__category_platform['id']]) || empty($activate_parent_categories[$__category_platform['id']]))
                    continue;
                if (!isset($__assigned_platform_check[$__category_platform['id']]))
                    continue;
                foreach (explode(',', $activate_parent_categories[$__category_platform['id']]) as $activate_category_id) {
                    do {
                        tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_CATEGORIES . " (categories_id, platform_id) VALUES('" . (int) $activate_category_id . "','" . (int) $__category_platform['id'] . "')");
                        $_move_upp = tep_db_fetch_array(tep_db_query("SELECT parent_id FROM " . TABLE_CATEGORIES . " WHERE categories_id='" . (int) $activate_category_id . "' "));
                        $activate_category_id = is_array($_move_upp) ? (int) $_move_upp['parent_id'] : 0;
                    } while ($activate_category_id);
                }
            }
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
                $ext::saveProduct((int)$products_id);
            }
        }

        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            // {{ departments assign
            if (Yii::$app->request->post('department_assign_present', 0)) {
                $_departments_list = \common\classes\department::getCatalogAssignList();
                $assign_departments = array();
                if (count($_departments_list) == 1) {
                    $assign_departments[] = (int)$_departments_list[0]['id'];
                } else {
                    $assign_departments = array_map('intval', Yii::$app->request->post('departments', array()));
                }
                if (count($assign_departments) > 0) {
                    tep_db_query("DELETE FROM " . TABLE_DEPARTMENTS_PRODUCTS . " WHERE products_id='" . (int)$products_id . "' AND departments_id NOT IN('" . implode("','", $assign_departments) . "') ");
                } else {
                    tep_db_query("DELETE FROM " . TABLE_DEPARTMENTS_PRODUCTS . " WHERE products_id='" . (int)$products_id . "'");
                }
                foreach ($assign_departments as $assign_department_id) {
                    $_check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM " . TABLE_DEPARTMENTS_PRODUCTS . " WHERE products_id='" . (int)$products_id . "' AND departments_id='" . $assign_department_id . "' "));
                    if ($_check['c'] == 0) {
                        tep_db_perform(TABLE_DEPARTMENTS_PRODUCTS, array(
                            'products_id' => (int)$products_id,
                            'departments_id' => $assign_department_id,
                        ));
                    }
                }
                $department_activate_parent_categories = Yii::$app->request->post('department_activate_parent_categories', array());
                $__assigned_department_check = array_flip($assign_departments);
                foreach ($_departments_list as $__category_department_info) {
                    if (!isset($department_activate_parent_categories[$__category_department_info['id']]) || empty($department_activate_parent_categories[$__category_department_info['id']]))
                        continue;
                    if (!isset($__assigned_department_check[$__category_department_info['id']]))
                        continue;
                    foreach (explode(',', $department_activate_parent_categories[$__category_department_info['id']]) as $activate_category_id) {
                        do {
                            tep_db_query("REPLACE INTO " . TABLE_DEPARTMENTS_CATEGORIES . " (categories_id, departments_id) VALUES('" . (int)$activate_category_id . "','" . (int)$__category_department_info['id'] . "')");
                            $_move_upp = tep_db_fetch_array(tep_db_query("SELECT parent_id FROM " . TABLE_CATEGORIES . " WHERE categories_id='" . (int)$activate_category_id . "' "));
                            $activate_category_id = is_array($_move_upp) ? (int)$_move_upp['parent_id'] : 0;
                        } while ($activate_category_id);
                    }
                }
            } else {
                // only one active, or ACL hide tab ????
                $_departments_list = \common\classes\department::getCatalogAssignList();
                if (count($_departments_list) == 1) {
                    tep_db_query("INSERT IGNORE INTO " . TABLE_DEPARTMENTS_PRODUCTS . " (products_id, departments_id) VALUES('" . (int)$products_id . "','" . (int)$_departments_list[0]['id'] . "')");
                }
            }
            // }} departments assign
        }
        /*if  ($TabAccess->tabDataSave('TEXT_PRICE_COST_W') && $TabAccess->allowSuppliersData()) {
            $suppliers_id = Yii::$app->request->post('suppliers_id', array());
            $suppliers_model = Yii::$app->request->post('suppliers_model', array());
            $suppliers_quantity = Yii::$app->request->post('suppliers_quantity', array());
            $suppliers_price = Yii::$app->request->post('suppliers_price', array());
            $supplier_discount = Yii::$app->request->post('supplier_discount', array());
            $suppliers_surcharge_amount = Yii::$app->request->post('suppliers_surcharge_amount', array());
            $suppliers_margin_percentage = Yii::$app->request->post('suppliers_margin_percentage', array());
            $suppliers_data_query = tep_db_query("select * from " . TABLE_SUPPLIERS . " order by suppliers_id");
            while ($suppliers_data = tep_db_fetch_array($suppliers_data_query)) {
                if ($suppliers_id[$suppliers_data['suppliers_id']]) {
                    $sql_data_array = [];
                    $sql_data_array['source'] = $suppliers_model[$suppliers_data['source']];
                    $sql_data_array['suppliers_model'] = $suppliers_model[$suppliers_data['suppliers_id']];
                    $sql_data_array['suppliers_price'] = $suppliers_price[$suppliers_data['suppliers_id']];
                    $sql_data_array['suppliers_quantity'] = $suppliers_quantity[$suppliers_data['suppliers_id']];
                    $sql_data_array['supplier_discount'] = $supplier_discount[$suppliers_data['suppliers_id']];
                    $sql_data_array['suppliers_surcharge_amount'] = $suppliers_surcharge_amount[$suppliers_data['suppliers_id']];
                    $sql_data_array['suppliers_margin_percentage'] = $suppliers_margin_percentage[$suppliers_data['suppliers_id']];
                    $check = tep_db_fetch_array(tep_db_query("select count(*) as suppliers_product_exists from " . TABLE_SUPPLIERS_PRODUCTS . " where products_id = '" . (int) $products_id . "' and uprid = '" . (int) $products_id . "' and suppliers_id = '" . (int) $suppliers_data['suppliers_id'] . "'"));
                    if ($check['suppliers_product_exists']) {
                        $sql_data_array['last_modified'] = 'now()';
                        tep_db_perform(TABLE_SUPPLIERS_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "' and uprid = '" . (int) $products_id . "' and suppliers_id = '" . (int) $suppliers_data['suppliers_id'] . "'");
                    } else {
                        $sql_data_array['date_added'] = 'now()';
                        $sql_data_array['products_id'] = $products_id;
                        $sql_data_array['uprid'] = $products_id;
                        $sql_data_array['suppliers_id'] = $suppliers_data['suppliers_id'];
                        tep_db_perform(TABLE_SUPPLIERS_PRODUCTS, $sql_data_array);
                    }
                } else {
                    tep_db_query("delete from " . TABLE_SUPPLIERS_PRODUCTS . " where products_id = '" . (int) $products_id . "' and uprid = '" . (int) $products_id . "' and suppliers_id = '" . (int) $suppliers_data['suppliers_id'] . "'");
                }
            }
        }*/

        /**
         * Split by languages
         */
        $languages = \common\helpers\Language::get_languages();
        if  ($TabAccess->tabDataSave('TEXT_NAME_DESCRIPTION') || $TabAccess->tabDataSave('TEXT_SEO')) {
            $descriptionSave = new \backend\models\ProductEdit\SaveDescription($productModel, $selectedDepartmentId);
            $descriptionSave->save();
        }

        /**
         * Attributes and inventory (variations)
         */
        $all_inventory_uprids_array = [];
        if  ($TabAccess->tabDataSave('TEXT_ATTR_INVENTORY')) {
            $attributesAndInventorySave = new \backend\models\ProductEdit\SaveAttributesAndInventory($productModel);
            $all_inventory_uprids_array = $attributesAndInventorySave->save();
        }
////////////////////////////////

        //suppliers
        if  ($TabAccess->tabDataSave('TEXT_PRICE_COST_W') && $TabAccess->allowSuppliersData()) {
            $suppliers_data = Yii::$app->request->post('suppliers_data', []);
            $suppliers_discount = Yii::$app->request->post('suppliers_discount', []);
            foreach (\common\models\SuppliersProducts::find()
                ->where(['products_id' => (int)$products_id])
                ->andWhere(['NOT IN', 'uprid', array_map('strval', array_unique(array_merge(array_keys($suppliers_data), $all_inventory_uprids_array)))])
                ->all() as $spRecord
            ) {
                $spRecord->delete();
            }
            unset($spRecord);
            if (!\common\helpers\Attributes::has_product_attributes($products_id) || $productModel->without_inventory){
                \common\helpers\Suppliers::removeUprids($products_id);
                $sProducts = \yii\helpers\ArrayHelper::index(SuppliersProducts::getSupplierProducts($products_id)->all(), 'suppliers_id');
                if (is_array($suppliers_data) && count($suppliers_data)){
                    foreach($suppliers_data as $supplier_uprid => $unused) {
                        break;
                    }
                    if (isset($suppliers_data[0]) && !isset($suppliers_data[$products_id])) {
                        $suppliers_data[$products_id] = $suppliers_data[0];
                        unset($suppliers_data[0]);
                    } elseif (strpos($supplier_uprid, '{') !== false && \common\helpers\Inventory::get_prid($supplier_uprid) == $products_id) {
                        $suppliers_data[$products_id] = $suppliers_data[$supplier_uprid];
                        unset($suppliers_data[$supplier_uprid]);
                    }
                    $sort_order = \Yii::$app->request->post('suppliers-default-sort', 0) ? null : 0;
                    foreach($suppliers_data[$products_id] as $suppliers_id => $data) {
                        if (isset($sProducts[$suppliers_id])){
                            $sProduct = $sProducts[$suppliers_id];
                            unset($sProducts[$suppliers_id]);
                        } else {
                            $sProduct = new SuppliersProducts();
                            $sProduct->loadDefaultValues();
                            $data['suppliers_id'] = $suppliers_id;
                            $data['products_id'] = $products_id;
                        }
                        $data['suppliers_price_discount'] = \common\helpers\Suppliers::getDiscountValuesTable($suppliers_discount[$products_id][$suppliers_id] ?? null);
                        $sProduct->load($data, null);
                        $sProduct->sort_order = is_null($sort_order) ? null : $sort_order++;
                        $sProduct->saveSupplierProduct($data);
                        if ($ext = \common\helpers\Acl::checkExtensionAllowed('SupplierPurchase', 'allowed')) {
                            if (method_exists($ext, 'checkWSProductStock')) {
                                $ext::checkWSProductStock($products_id, \common\helpers\Warehouses::get_default_warehouse(), $suppliers_id);
                            }
                        }
                    }
                    foreach($sProducts as $sProduct) $sProduct->delete();
                } else {
                    foreach($sProducts as $sProduct) $sProduct->delete();
                    (new SuppliersProducts)->saveDefaultSupplierProduct(['products_id' => $products_id]);
                }
            } else { //inv
                if (\common\helpers\Extensions::isAllowed('Inventory')) {
                    //{{delete single sup_prid
                    foreach(SuppliersProducts::getSupplierProducts($products_id)->all() as $sP) $sP->delete();
                    //}}end
                    $inventories = \common\models\Inventory::findAll(['prid' => $products_id]);
                    if ($inventories){
                        foreach($inventories as $inventory){
                            if (!isset($suppliers_data[$inventory->products_id])){//new product, prid undefined
                                $reg = preg_replace("/^".$inventory->prid."\{/", "0{", $inventory->products_id);
                                if (isset($suppliers_data[$reg])){
                                    $suppliers_data[$inventory->products_id] = $suppliers_data[$reg];
                                }
                            }
                            if (isset($suppliers_data[$inventory->products_id])){
                                $sProducts = \yii\helpers\ArrayHelper::index(SuppliersProducts::getSupplierUpridProducts($inventory->products_id)->all(), 'suppliers_id');
                                foreach($suppliers_data[$inventory->products_id] as $suppliers_id => $data) {
                                    $sProduct = null;
                                    if (isset($sProducts[$suppliers_id])){
                                        $sProduct = $sProducts[$suppliers_id];
                                        unset($sProducts[$suppliers_id]);
                                    }
                                    if (!$sProduct){
                                        $sProduct = new SuppliersProducts();
                                        $sProduct->loadDefaultValues();
                                        $data['suppliers_id'] = $suppliers_id;
                                        $data['products_id'] = $products_id;
                                        $data['uprid'] = $inventory->products_id;
                                    }
                                    $data['suppliers_price_discount'] = \common\helpers\Suppliers::getDiscountValuesTable($suppliers_discount[$inventory->products_id][$suppliers_id] ?? null);
                                    $sProduct->load($data, null);
                                    $sProduct->saveSupplierProduct($data);
                                    /** @var \common\extensions\SupplierPurchase\SupplierPurchase $ext */
                                    if ($ext = \common\helpers\Extensions::isAllowed('SupplierPurchase')) {
                                        if (method_exists($ext, 'checkWSProductStock')) {
                                            $ext::checkWSProductStock($inventory->products_id, \common\helpers\Warehouses::get_default_warehouse(), $suppliers_id);
                                        }
                                    }
                                }
                                foreach($sProducts as $sProduct) $sProduct->delete();
                            } else { //
                                if (Yii::$app->request->post('products_id') == 0) {
                                    $post_uprid = preg_replace("/^".$inventory->prid."\{/", "0{", $inventory->products_id);
                                } else {
                                    $post_uprid = $inventory->products_id;
                                }
                                if (Yii::$app->request->post('inventoryexistent_' . $post_uprid) === null && Yii::$app->request->post('inventorymodel_' . $post_uprid) === null) {
                                    // If inventory data is not set - skip it
                                    continue;
                                }
                                foreach(SuppliersProducts::getSupplierUpridProducts($inventory->products_id)->all() as $sProduct) $sProduct->delete();
                                (new SuppliersProducts)->saveDefaultSupplierProduct(['products_id' => $products_id, 'uprid' => $inventory->products_id]);
                            }
                        }
                    }
                }
            }
            if (!empty(\common\helpers\PriceFormula::getProductModelForAutoUpdate($products_id))) {
                \common\helpers\PriceFormula::applyDb($products_id);
            }
        }

        /**
         * Images
         */
        if ($TabAccess->tabDataSave('TAB_IMAGES')) {
            $productImages = new \backend\models\ProductEdit\SaveProductImages($productModel, $path);
            $productImages->save();
        }

        /**
         * Properties
         */
        if ($TabAccess->tabDataSave('TAB_PROPERTIES')) {
            $productProperties = new \backend\models\ProductEdit\SaveProductProperties($productModel);
            $productProperties->save();
        }

        /**
         * Videos
         */
        if ($TabAccess->tabDataSave('TEXT_VIDEO')) {
            $productVideos = new \backend\models\ProductEdit\SaveProductVideos($productModel, $path);
            $productVideos->save();
        }

        if ($TabAccess->tabView('TAB_IMPORT_EXPORT')){
            $importExportData = new \backend\models\ProductEdit\SaveImportExport($productModel);
            $importExportData->save();
        }

        foreach (\common\helpers\Hooks::getList('categories/productedit-beforesave') as $filename) {
            include($filename);
        }

        $productModel->save(false);

        foreach (\common\helpers\Hooks::getList('categories/productedit') as $filename) {
            include($filename);
        }

        if ($TabAccess->tabDataSave('TEXT_PRODUCT_SOAP_CONFIG')
             && class_exists('\backend\models\EP\Datasource\HolbiSoap') ) {
            \backend\models\EP\Datasource\HolbiSoap::productUpdate($products_id, Yii::$app->request->post('soap_config', []));
        }

        $productModel->save(false);

        \common\helpers\Product::fillGlobalSort(0, $products_id);

        \common\helpers\SubProduct::afterProductSave($productModel);

        \common\components\Popularity::calculatePopularity($productModel->products_id);

        \common\components\CategoriesCache::getCPC()::invalidateProducts((int)$products_id);

        $message = TEXT_PRODUCT_UPDATED_NOTICE;
        $messageType = 'success';
        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
        <?= $message ?>
                    </div>
                </div>
                <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                </div>
            </div>
            <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                    $(this).parents('.pop-mess').remove();
                });
            </script>
        </div>


        <?php
        if ($isNewProduct){
            echo '<script> var url= "' . Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $products_id]). '"+window.location.hash; window.location.href=url;</script>';
        } else {
            echo '<script>window.location.reload();</script>';
        }

        \common\helpers\Product::doAllocateAutomatic($products_id, true);
        //return $this->redirect(Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $products_id]));

//        if ($action == 'update_product') {
        if (isset($logger) && \common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
            $afterObject = new \common\api\Classes\Product();
            $afterObject->load($products_id);
            $logger->setAfterObject($afterObject);
            unset($afterObject);
            $logger->run();
        }
      }

    public function actionProductSearch() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $q = Yii::$app->request->get('q');
        $products_id = (int) Yii::$app->request->get('not');
        $bundle_skip = (int) Yii::$app->request->get('bundle_skip');
        $linked_skip = (int) Yii::$app->request->get('linked_skip',0);
        $with_images = (int) Yii::$app->request->get('with_images',0);
        $bycustomer = (int) Yii::$app->request->get('bycustomer',0);
        $child_skip = (int) Yii::$app->request->get('child_skip',0);

        $products_string = '';

        if ($this->defaultCollapsed) {
              $pQ = (new \yii\db\Query())
                  ->select("p.products_id, p.products_status ")
                  ->addSelect(['products_name' => (new Expression(ProductNameDecorator::instance()->listingQueryExpression('pd','')))])
                  ->from(['p' => TABLE_PRODUCTS])
                  ->leftJoin(TABLE_PRODUCTS_DESCRIPTION . " pd",
                      'p.products_id = pd.products_id and pd.language_id =:lid and pd.platform_id = :pid',
                      [':lid' => (int) $languages_id, ':pid' => intval(\common\classes\platform::defaultId())] )
                  ->andWhere("p.products_id != :prid", [':prid' => (int) $products_id])
                  ->distinct()
                  ->orderBy("p.sort_order ")
                  ->addOrderBy(new Expression(ProductNameDecorator::instance()->listingQueryExpression('pd','')))
                  ->limit(500)
              ;

          if (!empty($q)) {
            $pQ->andWhere([
              'or',
              ['like', "p.products_model", tep_db_input($q) ],
              ['like', "pd.products_name", tep_db_input($q) ],
              ['like', "pd.products_internal_name", tep_db_input($q) ]
            ]);
          }

          $filter_by_platform = \common\helpers\Admin::limitedPlatformList();
          if ( is_array($filter_by_platform) && count($filter_by_platform)>0 ){
              $pQ->andWhere(['EXISTS', (new \yii\db\Query())
                  ->from(\common\models\PlatformsProducts::tableName() . ' p2pl')
                  ->andWhere('p2pl.products_id=p.products_id')
                  ->andWhere(['IN', 'p2pl.platform_id', $filter_by_platform])
              ])->distinct();
          }

          if ($bundle_skip > 0 ) {
            $pQ->andWhere(" p.is_bundle = 0");
          }
          if ($linked_skip > 0 ) {
            $pQ->leftJoin("products_linked_parent lp", "lp.product_id=p.products_id ")
                ->andWhere(" lp.product_id IS NULL");
          }
          if ($child_skip>0 ){
            $pQ->andWhere(" p.parent_products_id = 0");
          }

          if ($bycustomer > 0 ) {

            /** @var \common\extensions\CustomerProducts\CustomerProducts $ext  */
            if ($ext = \common\helpers\Acl::checkExtension('CustomerProducts', 'allowed')) {
              if($ext::allowed() ) {
                $pQ->andWhere(['p.products_id' =>
                  \common\extensions\CustomerProducts\models\CustomerProducts::find()->where(['customer_id' => $bycustomer])->select('product_id')]);
              }
            } else {
              $bycustomer = 0;
            }
          }

          $productsAll = $pQ->all();

          if (is_array($productsAll) && !empty($productsAll)) {
             foreach( $productsAll as $products) {
                if ( empty($products['products_name']) ) {
                    $products['products_name'] = \common\helpers\Product::get_products_name($products['products_id']);
                }
                $option_attributes = ($products['products_status'] == 0 ? ' class="dis_prod"' : '');
                if ( $with_images ) {
                    $option_attributes .= ' data-image-src="'.\common\classes\Images::getImageUrl($products['products_id']).'"';
                }
                if ($bycustomer>0) {
                  $option_attributes .= ' selected ';
                }
                $products_string .= '<option value="' . $products['products_id'] . '" '. $option_attributes . '>' . $products['products_name'] . '</option>';
              }
          }
        } else {
            $categories = \common\helpers\Categories::get_category_tree(0, '', '0', '', true);
            $categories_idx = \yii\helpers\ArrayHelper::index($categories,'id');

            $pQ = (new \yii\db\Query())
                ->select("p.products_id, p.products_status ")
                ->addSelect(['products_name' => (new Expression(ProductNameDecorator::instance()->listingQueryExpression('pd','')))])
                ->from(['p' => TABLE_PRODUCTS])
                ->join('left join', \common\models\Products2Categories::tableName().' p2c', 'p2c.products_id=p.products_id')
                ->join('left join', \common\models\Categories::tableName().' c', 'c.categories_id=p2c.categories_id')
                ->addSelect(['p2c.categories_id'])
                ->leftJoin(TABLE_PRODUCTS_DESCRIPTION . " pd",
                    'p.products_id = pd.products_id and pd.language_id =:lid and pd.platform_id = :pid',
                    [':lid' => (int) $languages_id, ':pid' => intval(\common\classes\platform::defaultId())] )
                ->andWhere("p.products_id != :prid", [':prid' => (int) $products_id])
                ->distinct()
                ->orderBy([new \yii\db\Expression('IFNULL(c.categories_left,10000000)'), "p2c.sort_order"=>SORT_ASC])
                ->addOrderBy(new Expression(ProductNameDecorator::instance()->listingQueryExpression('pd','')))
                ->limit(500)
            ;

            if (!empty($q)) {
                $pQ->andWhere([
                    'or',
                    ['like', "p.products_model", tep_db_input($q) ],
                    ['like', "pd.products_name", tep_db_input($q) ],
                    ['like', "pd.products_internal_name", tep_db_input($q) ]
                ]);
            }else{
                //$pQ->andWhere('/*empty search term*/1=0');
            }

            if ($bundle_skip > 0 ) {
                $pQ->andWhere(" p.is_bundle = 0");
            }

            $filter_by_platform = \common\helpers\Admin::limitedPlatformList();
            if ( is_array($filter_by_platform) && count($filter_by_platform)>0 ){
                $pQ
                    ->andWhere(['EXISTS', (new \yii\db\Query())
                        ->from(\common\models\PlatformsProducts::tableName() . ' p2pl')
                        ->andWhere('p2pl.products_id=p.products_id')
                        ->andWhere(['IN', 'p2pl.platform_id', $filter_by_platform])
                    ])
                    ->andWhere(['EXISTS', (new \yii\db\Query())
                        ->from(\common\models\PlatformsCategories::tableName() . ' c2pl')
                        ->andWhere('c2pl.categories_id=c.categories_id')
                        ->andWhere(['IN', 'c2pl.platform_id', $filter_by_platform])
                    ])
                    ->distinct();
            }

            if ($linked_skip > 0 ) {
                $pQ->leftJoin("products_linked_parent lp", "lp.product_id=p.products_id ")
                    ->andWhere(" lp.product_id IS NULL");
            }
            if ($child_skip>0 ){
                $pQ->andWhere(" p.parent_products_id = 0");
            }

            if ($bycustomer > 0 ) {

                /** @var \common\extensions\CustomerProducts\CustomerProducts $ext  */
                if ($ext = \common\helpers\Acl::checkExtension('CustomerProducts', 'allowed')) {
                    if($ext::allowed() ) {
                        $pQ->andWhere(['p.products_id' =>
                            \common\extensions\CustomerProducts\models\CustomerProducts::find()->where(['customer_id' => $bycustomer])->select('product_id')]);
                    }
                } else {
                    $bycustomer = 0;
                }
            }

            $productsAll = $pQ->all();

            if (is_array($productsAll) && !empty($productsAll)) {
                $group_cat_id = -1;

                foreach( $productsAll as $_idx=>$products) {
                    if ( empty($products['products_name']) ) {
                        $products['products_name'] = \common\helpers\Product::get_products_name($products['products_id']);
                    }
                    if ( (int)$products['categories_id']!=$group_cat_id ){
                        $group_cat_id = (int)$products['categories_id'];
                        $products_string .= '<optgroup label="' . ($categories_idx[$group_cat_id]['text'] ?? null). '">';
                    }
                    $option_attributes = ($products['products_status'] == 0 ? ' class="dis_prod"' : '');
                    if ( $with_images ) {
                        $option_attributes .= ' data-image-src="'.\common\classes\Images::getImageUrl($products['products_id']).'"';
                    }
                    if ($bycustomer>0) {
                        $option_attributes .= ' selected ';
                    }
                    $products_string .= '<option value="' . $products['products_id'] . '" '. $option_attributes . '>' . $products['products_name'] . '</option>';
                    if ( ($_idx+1)>=count($productsAll) || (int)$productsAll[$_idx+1]['categories_id']!=$group_cat_id ){
                        $products_string .= '</optgroup>';
                    }
                }
            }
        }

        echo $products_string;
    }

    private static function getProductsDetails($products_id) {
      $languages_id = \Yii::$app->settings->get('languages_id');
      if ($products_id>0) {
      //probably random platform
        $query = tep_db_query("select p.products_id, p.products_quantity, p.products_model, p.products_status_bundle, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name, p.products_status from " . TABLE_PRODUCTS_DESCRIPTION . " pd," . TABLE_PRODUCTS . " p where language_id = '" . $languages_id . "' and platform_id = '".intval(\common\classes\platform::defaultId())."' and  p.products_id = '" . $products_id . "' and pd.products_id = '" . $products_id . "' limit 1");
        if (tep_db_num_rows($query) > 0) {
          $ret = tep_db_fetch_array($query);
          if (empty($ret['products_name'])){
              $ret['products_name'] = \common\helpers\Product::get_products_name($ret['products_id']);
          }
        } else {
          $ret = array();
        }
      } else {
        $ret = array();
      }
      return $ret;
    }

    public function actionProductNewBundles() {
        $currencies = \Yii::$container->get('currencies');

        $this->layout = false;

        $products_id = (int) Yii::$app->request->post('products_id');
        $data = self::getProductsDetails($products_id);

        if (count($data) > 0) {
            $bundlesProducts = [
                'bundles_id' => $data['products_id'],
                'products_name' => $data['products_name'],
                'num_product' => '1',
                'price' => '0.00',
                'discount' => '',
                'image' => \common\classes\Images::getImage($data['products_id'], 'Small'),
                'status_class' => ($data['products_status'] == 0 ? 'dis_prod' : ''),
                'products_status_bundle' => (int)$data['products_status_bundle'],
                'products_quantity' => (int)$data['products_quantity'],
                'products_model' => $data['products_model'],
                'products_qty' => \common\helpers\Product::get_products_stock($data['products_id']),
                'products_price' => $currencies->format(\common\helpers\Product::get_products_price($data['products_id'])),
            ];

            return $this->render('product-new-bundles.tpl', [
                        'bundles' => $bundlesProducts,
            ]);
        }
    }

//    public function actionProductNewXsell() {
//
//        $this->layout = false;
//
//        $currencies = Yii::$container->get('currencies');
//
//        $products_id = (int) Yii::$app->request->post('products_id');
//        $xsell_type_id = (int) Yii::$app->request->post('xsell_type');
//        $data = self::getProductsDetails($products_id);
//
//        if (count($data) > 0) {
//            $backlink = 0;
//            if ($parent_products_id = (int) Yii::$app->request->post('parent_products_id')) {
//                $backlink = \common\models\ProductsXsell::find()
//                    ->where(['xsell_type_id' => $xsell_type_id, 'xsell_id' => $parent_products_id, 'products_id' => $data['products_id']])
//                    ->select(['xsell_id'])->scalar();
//            }
//
//            $xsellProduct = [
//                'xsell_id' => $data['products_id'],
//                'xsell_type_id' => $xsell_type_id,
//                'products_name' => $data['products_name'],
//                'image' => \common\classes\Images::getImage($data['products_id'], 'Small'),
//                'price' => $currencies->format(\common\helpers\Product::get_products_price($data['products_id'])),
//                'status_class' => ($data['products_status'] == 0 ? 'dis_prod' : ''),
//                'backlink' => $backlink,
//            ];
//
//            return $this->render('product-new-xsell.tpl', [
//                'xsell_type_id' => $xsell_type_id,
//                'xsell' => $xsellProduct,
//            ]);
//        }
//    }

//    public function actionProductNewUpsell() {
//
//        $this->layout = false;
//
//        $currencies = Yii::$container->get('currencies');
//
//        $products_id = (int) Yii::$app->request->post('products_id');
//
//        $data = self::getProductsDetails($products_id);
//
//        if (count($data) > 0) {
//            $upsellProduct = [
//                'upsell_id' => $data['products_id'],
//                'products_name' => $data['products_name'],
//                'image' => \common\classes\Images::getImage($data['products_id'], 'Small'),
//                'price' => $currencies->format(\common\helpers\Product::get_products_price($data['products_id'])),
//                'status_class' => ($data['products_status'] == 0 ? 'dis_prod' : ''),
//            ];
//
//            return $this->render('product-new-upsell.tpl', [
//                        'upsell' => $upsellProduct,
//            ]);
//        }
//    }

    public function actionProductImageGenerator() {

        // product-image-generator
        $Images = new \common\classes\Images();

        $path = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES;

        //$languages = \common\helpers\Language::get_languages();

        //TRUNCATE TABLE `products_images`
        //TRUNCATE TABLE `products_images_description`

        $check_product_query = tep_db_query("SELECT products_id, products_image, products_image_lrg, products_image_xl_1, products_image_xl_2, products_image_xl_3, products_image_xl_4, products_image_xl_5, products_image_xl_6, products_seo_page_name FROM " . TABLE_PRODUCTS . " WHERE 1");
        if (tep_db_num_rows($check_product_query) > 0) {
            while ($product = tep_db_fetch_array($check_product_query)) {

                $orig_file = $product['products_image_lrg'];

                $check = tep_db_fetch_array(tep_db_query("select pi.products_images_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid where pi.products_id = '" . (int) $product['products_id'] . "' and pi.products_images_id = pid.products_images_id and pid.language_id = '0' and pid.orig_file_name like '%" . tep_db_input($orig_file) . "'"));

                $tmp_name = $path . $orig_file;

                if (!empty($orig_file) && file_exists($tmp_name) && !($check['products_images_id'] > 0)) {

                    $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $product['products_id'] . DIRECTORY_SEPARATOR;
                    if (!file_exists($image_location)) {
                        mkdir($image_location, 0777, true);
                    }

                    $sql_data_array = [];
                    $sql_data_array['default_image'] = 1;
                    $sql_data_array['image_status'] = 1;
                    $sql_data_array['products_id'] = (int) $product['products_id'];
                    tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
                    $imageId = tep_db_insert_id();

                    $image_location .= $imageId . DIRECTORY_SEPARATOR;
                    if (!file_exists($image_location)) {
                        mkdir($image_location, 0777, true);
                    }

                    $sql_data_array = [];
                    $sql_data_array['language_id'] = 0;

                    $file_name = ($product['products_seo_page_name'] ? $product['products_seo_page_name'] : Seo::makeSlug(\common\helpers\Product::get_products_name($product['products_id'])));
                    $uploadExtension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
                    $file_name .= '.' . $uploadExtension;
                    $sql_data_array['file_name'] = $file_name;

                    $hashName = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
                    $new_name = $image_location . $hashName;

                    copy($tmp_name, $new_name);
                    $sql_data_array['hash_file_name'] = $hashName;

                    $sql_data_array['orig_file_name'] = $orig_file;

                    $sql_data_array['image_title'] = '';
                    $sql_data_array['image_alt'] = '';

                    $lang = '';
                    $Images->createImages($product['products_id'], $imageId, $hashName, $file_name, $lang); //$orig_file

                    $sql_data_array['products_images_id'] = (int) $imageId;
                    $sql_data_array['language_id'] = (int) $language_id;
                    tep_db_perform(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $sql_data_array);

                    /* for( $i = 0, $n = sizeof( $languages ); $i < $n; $i++ ) {



                      } */
                }


                for ($im = 1; $im <= 6; $im++) {

                    $orig_file = $product['products_image_xl_' . $im];

                    $check = tep_db_fetch_array(tep_db_query("select pi.products_images_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid where pi.products_id = '" . (int) $product['products_id'] . "' and pi.products_images_id = pid.products_images_id and pid.language_id = '0' and pid.orig_file_name like '%" . tep_db_input($orig_file) . "'"));

                    $tmp_name = $path . $orig_file;

                    if (!empty($orig_file) && file_exists($tmp_name) && !($check['products_images_id'] > 0)) {

                        $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $product['products_id'] . DIRECTORY_SEPARATOR;
                        if (!file_exists($image_location)) {
                            mkdir($image_location, 0777, true);
                        }

                        $sql_data_array = [];
                        $sql_data_array['default_image'] = 0;
                        $sql_data_array['image_status'] = 1;
                        $sql_data_array['sort_order'] = $im;
                        $sql_data_array['products_id'] = (int) $product['products_id'];
                        tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
                        $imageId = tep_db_insert_id();

                        $image_location .= $imageId . DIRECTORY_SEPARATOR;
                        if (!file_exists($image_location)) {
                            mkdir($image_location, 0777, true);
                        }

                        $sql_data_array = [];
                        $sql_data_array['language_id'] = 0;

                        $file_name = ($product['products_seo_page_name'] ? $product['products_seo_page_name'] : Seo::makeSlug(\common\helpers\Product::get_products_name($product['products_id'])));
                        $uploadExtension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
                        $file_name .= '.' . $uploadExtension;
                        $sql_data_array['file_name'] = $file_name;

                        $hashName = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
                        $new_name = $image_location . $hashName;

                        copy($tmp_name, $new_name);
                        $sql_data_array['hash_file_name'] = $hashName;

                        $sql_data_array['orig_file_name'] = $orig_file;

                        $sql_data_array['image_title'] = '';
                        $sql_data_array['image_alt'] = '';

                        $lang = '';
                        $Images->createImages($product['products_id'], $imageId, $hashName, $file_name, $lang); //$orig_file

                        $sql_data_array['products_images_id'] = (int) $imageId;
                        $sql_data_array['language_id'] = (int) $language_id;
                        tep_db_perform(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $sql_data_array);

                        /* for( $i = 0, $n = sizeof( $languages ); $i < $n; $i++ ) {



                          } */
                    }
                }
            }
        }
    }

    public function actionCategoryedit() {
        if (false === \common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_EDIT'])) {
            $this->redirect(\yii\helpers\Url::toRoute('categories/'));
        }
        $affiliate_id = \Yii::$app->settings->get('affiliate_id');
        $languages_id = \Yii::$app->settings->get('languages_id');

        $this->topButtons[] = '<span class="btn btn-confirm" onclick="return saveCategory()">' . IMAGE_SAVE . '</span>';

        $this->view->usePopupMode = false;
        if (Yii::$app->request->isAjax) {
            $this->layout = false;
            $this->view->usePopupMode = true;
        }

        \common\helpers\Translation::init('admin/categories');

        $popup = 0;
        if (Yii::$app->request->isPost) {
            $categories_id = (int) Yii::$app->request->getBodyParam('categories_id');
            $popup = (int) Yii::$app->request->post('popup');
            if ($popup == 0) {
                $this->view->usePopupMode = false;
            }
        } else {
            $categories_id = (int) Yii::$app->request->get('categories_id');
        }
        $this->view->contentAlreadyLoaded = $popup;

        $category = [];
        if ($categories_id > 0) {
            $categories_query = tep_db_query("select c.*, cd.categories_name, cd.categories_heading_title, cd.categories_description, cd.categories_head_title_tag, cd.categories_head_desc_tag, cd.categories_head_keywords_tag, cd.categories_h1_tag, cd.categories_h2_tag, cd.categories_h3_tag, cd.categories_image_alt_tag_mask, cd.categories_image_title_tag_mask, c.categories_image,  c.categories_image_2, categories_image_3, categories_image_4, c.show_on_home, c.parent_id, c.categories_seo_page_name, c.sort_order, c.date_added, c.last_modified, c.categories_status, c.categories_old_seo_page_name, c.maps_id, c.banners_group from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . $categories_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . $languages_id . "' and cd.affiliate_id = 0 order by c.sort_order, cd.categories_name");
            $category = tep_db_fetch_array($categories_query);
        } else {
            $category['parent_id'] = (int) Yii::$app->request->get('category_id', 0);
            $category['manual_control_status'] = 1;
            $category['maps_id'] = 0;
            $category['categories_image'] = '';
            $category['categories_image_2'] = '';
            $category['categories_image_3'] = '';
            $category['categories_image_4'] = '';
            $category['categories_name'] = '';
        }
        $cInfo = new \objectInfo($category);
        if (!isset($cInfo->default_sort_order)) {
            $cInfo->default_sort_order = 0;
        }

        $mapImage = null;
        $mapTitle = '';

        /**
         * @var $imageMaps \common\extensions\ImageMaps\models\ImageMaps
         */
        if ($imageMaps = \common\helpers\Extensions::getModel('ImageMaps', 'ImageMaps')) {
            if (!isset($cInfo->maps_id)) {
                $cInfo->maps_id = 0;
            }
            if ($cInfo->maps_id > 0 && !empty($imageMaps)) {
                $map = $imageMaps::findOne((int)$cInfo->maps_id);
                if ($map) {
                    $mapImage = $map->image;
                    $mapTitle = $map->getTitle($languages_id);
                }
            }
        }

        $pSettings = \common\models\CategoriesPlatformSettings::find()->andWhere(['categories_id' => $categories_id])->indexBy('platform_id')->asArray()->all();
        if (!$pSettings) {
          $pSettings = [];
        }
        $pSettings[0] = [
          'platform_id' => 0,
          'categories_image' => (isset($cInfo->categories_image) ? $cInfo->categories_image : ''),
          'categories_image_2' => (isset($cInfo->categories_image_2) ? $cInfo->categories_image_2 : ''),
          'categories_image_3' => (isset($cInfo->categories_image_3) ? $cInfo->categories_image_3 : ''),
          'categories_image_4' => (isset($cInfo->categories_image_4) ? $cInfo->categories_image_4 : ''),
          'show_on_home' => (isset($cInfo->show_on_home) ? $cInfo->show_on_home : 0),
          'maps_id' => $cInfo->maps_id,
          'imageMap' => [
            'image' => $mapImage,
          ],
          'imageMapTitle' => [
            'title' => $mapTitle,
          ]
        ];

        $tmp = [];
        foreach (array_merge([['id' => 0, 'text' => TEXT_MAIN]], \common\classes\platform::getList(false)) as $__platform) {
            $__platform['title'] = $__platform['text'];
            if (isset($pSettings[$__platform['id']])) {
              $__platform['cssClass'] = ' changed';
            }
            $__platform['def_data'] = ['platform_id' => $__platform['id']];
            unset($__platform['need_login']);
            $tmp[] = $__platform;
        }

        $this->view->platformsettings_tabs[] = $tmp;
        $this->view->platformsettings_tabs_data = $pSettings;
        $this->view->platformsettings_tabparams[] = [
              'cssClass' => 'tabs-platforms-settings', // add to tabs and tab-pane
              //'callback' => 'productPriceBlock', // smarty function which will be called before children tabs , data passed as params params
              'callback_bottom' => '',
              'tabs_type' => 'hTab',
          ];
          unset($tmp);


        $cDescription = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            $cDescription[$i]['code'] = $languages[$i]['code'];
            $cDescription[$i]['languageId'] = $languages[$i]['id'];

            $category_description_query = tep_db_query("select * from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . $categories_id . "' and language_id = '" . (int) $languages[$i]['id'] . "' and affiliate_id = '" . (int) $affiliate_id . "'");
            $category_description = tep_db_fetch_array($category_description_query);
            $categoryDescription = new \objectInfo($category_description);
            $cDescription[$i]['categories_name'] = tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', (isset($categoryDescription->categories_name) ? $categoryDescription->categories_name : ''), 'class="form-control"');
            $cDescription[$i]['categories_description'] = \common\helpers\Html::textarea('categories_description[' . $languages[$i]['id'] . ']', $categoryDescription->categories_description ?? '',
                ['wrap' => 'soft', 'cols' => '70', 'rows' => '15', 'class' => 'form-control ckeditor',  'id' => 'txt_category_description_'.$languages[$i]['id'] ]);
            $cDescription[$i]['categories_seo_page_name'] = tep_draw_input_field('categories_seo_page_name[' . $languages[$i]['id'] . ']', (isset($categoryDescription->categories_seo_page_name) ? $categoryDescription->categories_seo_page_name : ''), 'class="form-control"');
            $cDescription[$i]['noindex_option'] = tep_draw_checkbox_field('noindex_option[' . $languages[$i]['id'] . ']', '1', (isset($categoryDescription->noindex_option) && $categoryDescription->noindex_option == 1), '', 'class="check_on_off"');
            $cDescription[$i]['nofollow_option'] = tep_draw_checkbox_field('nofollow_option[' . $languages[$i]['id'] . ']', '1', (isset($categoryDescription->nofollow_option) && $categoryDescription->nofollow_option == 1), '', 'class="check_on_off"');
            $cDescription[$i]['rel_canonical'] = tep_draw_input_field('rel_canonical[' . $languages[$i]['id'] . ']', (isset($categoryDescription->rel_canonical) ? $categoryDescription->rel_canonical : ''), 'class="form-control form-control-small"');
            $cDescription[$i]['categories_head_title_tag'] = tep_draw_input_field('categories_head_title_tag[' . $languages[$i]['id'] . ']', (isset($categoryDescription->categories_head_title_tag) ? $categoryDescription->categories_head_title_tag : ''), 'class="form-control"');
            $cDescription[$i]['categories_head_desc_tag'] = tep_draw_textarea_field('categories_head_desc_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', (isset($categoryDescription->categories_head_desc_tag) ? $categoryDescription->categories_head_desc_tag : ''), 'class="form-control"');
            $cDescription[$i]['categories_head_keywords_tag'] = tep_draw_textarea_field('categories_head_keywords_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', (isset($categoryDescription->categories_head_keywords_tag) ? $categoryDescription->categories_head_keywords_tag : ''), 'class="form-control"');
            $cDescription[$i]['categories_h1_tag'] = tep_draw_input_field('categories_h1_tag[' . $languages[$i]['id'] . ']', (isset($categoryDescription->categories_h1_tag) ? $categoryDescription->categories_h1_tag : ''), 'class="form-control"');
            $cDescription[$i]['categories_h2_tag'] = (isset($categoryDescription->categories_h2_tag) ? $categoryDescription->categories_h2_tag : '');
            $cDescription[$i]['categories_h3_tag'] = (isset($categoryDescription->categories_h3_tag) ? $categoryDescription->categories_h3_tag : '');
            $cDescription[$i]['categories_image_alt_tag_mask'] = tep_draw_input_field('categories_image_alt_tag_mask[' . $languages[$i]['id'] . ']', (isset($categoryDescription->categories_image_alt_tag_mask) ? $categoryDescription->categories_image_alt_tag_mask : ''), 'class="form-control"');
            $cDescription[$i]['categories_image_title_tag_mask'] = tep_draw_input_field('categories_image_title_tag_mask[' . $languages[$i]['id'] . ']', (isset($categoryDescription->categories_image_title_tag_mask) ? $categoryDescription->categories_image_title_tag_mask : ''), 'class="form-control"');
        }

        $this->view->platform_assigned = [];
        $this->view->platform_switch_notice = [];
        if (isset($cInfo->categories_id) && intval($cInfo->categories_id) > 0) {
            $get_assigned_platforms_r = tep_db_query("SELECT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id = '" . intval($cInfo->categories_id) . "' ");
            if (tep_db_num_rows($get_assigned_platforms_r) > 0) {
                while ($_assigned_platform = tep_db_fetch_array($get_assigned_platforms_r)) {
                    $this->view->platform_assigned[(int) $_assigned_platform['platform_id']] = (int) $_assigned_platform['platform_id'];
                }
            }

            foreach (\common\classes\platform::getList() as $__platform) {
                $this->view->platform_switch_notice[strval($__platform['id'])] = array(
                    'categories' => [0, 0],
                    'products' => [0, 0],
                    'original_state' => isset($this->view->platform_assigned[(int) $__platform['id']]),
                );
            }
            $sub_categories = array();
            \common\helpers\Categories::get_subcategories($sub_categories, $cInfo->categories_id, true);
            if (count($sub_categories) > 0) {
                foreach (\common\classes\platform::getCategoriesAssignList() as $_check_notice_platform) {
                    //category assigned, can switch OFF - check assigned subcategories
                    $__check = tep_db_fetch_array(tep_db_query(
                                    "SELECT COUNT(*) AS c " .
                                    "FROM " . TABLE_PLATFORMS_CATEGORIES . " " .
                                    "WHERE platform_id='" . $_check_notice_platform['id'] . "' AND categories_id IN('" . implode("','", $sub_categories) . "') "
                    ));
                    if ($__check['c'] > 0) {
                        $this->view->platform_switch_notice[$_check_notice_platform['id']]['categories'][1] = $__check['c'];
                    }
                    //category not assigned, can switch ON - check not assigned subcategories
                    $__check = tep_db_fetch_array(tep_db_query(
                                    "SELECT COUNT(*) AS c " .
                                    "FROM " . TABLE_CATEGORIES . " c " .
                                    " LEFT JOIN " . TABLE_PLATFORMS_CATEGORIES . " pc ON pc.categories_id=c.categories_id AND pc.platform_id='" . $_check_notice_platform['id'] . "' " .
                                    "WHERE c.categories_id IN('" . implode("','", $sub_categories) . "') AND pc.categories_id IS NULL "
                    ));
                    if ($__check['c'] > 0) {
                        $this->view->platform_switch_notice[$_check_notice_platform['id']]['categories'][0] = $__check['c'];
                    }
                }
            }

            $sub_categories[] = $cInfo->categories_id;
            foreach (\common\classes\platform::getProductsAssignList() as $_check_notice_platform) {
                //category assigned, can switch OFF - check assigned products
                $__check = tep_db_fetch_array(tep_db_query(
                                "SELECT COUNT(*) AS c " .
                                "FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PLATFORMS_PRODUCTS . " plp " .
                                "WHERE p2c.products_id=p.products_id AND p2c.categories_id IN('" . implode("','", $sub_categories) . "') " .
                                "  AND plp.platform_id='" . $_check_notice_platform['id'] . "' AND plp.products_id=p.products_id "
                ));
                if ($__check['c'] > 0) {
                    $this->view->platform_switch_notice[$_check_notice_platform['id']]['products'][1] = $__check['c'];
                }
                //category not assigned, can switch ON - check not assigned products
                $__check = tep_db_fetch_array(tep_db_query(
                                "SELECT COUNT(*) AS c " .
                                "FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCTS . " p " .
                                "  LEFT JOIN " . TABLE_PLATFORMS_PRODUCTS . " plp ON plp.platform_id='" . $_check_notice_platform['id'] . "' AND plp.products_id=p.products_id " .
                                "WHERE p2c.products_id=p.products_id AND p2c.categories_id IN('" . implode("','", $sub_categories) . "') " .
                                "  AND plp.products_id IS NULL "
                ));
                if ($__check['c'] > 0) {
                    $this->view->platform_switch_notice[$_check_notice_platform['id']]['products'][0] = $__check['c'];
                }
            }
        } elseif (isset($cInfo->parent_id) && !empty($cInfo->parent_id)) {
            $get_assigned_platforms_r = tep_db_query("SELECT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id = '" . intval($cInfo->parent_id) . "' ");
            if (tep_db_num_rows($get_assigned_platforms_r) > 0) {
                while ($_assigned_platform = tep_db_fetch_array($get_assigned_platforms_r)) {
                    $this->view->platform_assigned[(int) $_assigned_platform['platform_id']] = (int) $_assigned_platform['platform_id'];
                }
            }
        } else {
            foreach (\common\classes\platform::getCategoriesAssignList() as $___data) {
                $this->view->platform_assigned[intval($___data['id'])] = intval($___data['id']);
            }
        }

        $departments = false;
        $this->view->department_assigned = [];
        $this->view->department_switch_notice = [];
        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            $departments = true;
            // {{ departments assign
            if (isset($cInfo->categories_id) && intval($cInfo->categories_id) > 0) {
                $get_assigned_departments_r = tep_db_query("SELECT departments_id FROM " . TABLE_DEPARTMENTS_CATEGORIES . " WHERE categories_id = '" . intval($cInfo->categories_id) . "' ");
                if (tep_db_num_rows($get_assigned_departments_r) > 0) {
                    while ($_assigned_department = tep_db_fetch_array($get_assigned_departments_r)) {
                        $this->view->department_assigned[(int) $_assigned_department['departments_id']] = (int) $_assigned_department['departments_id'];
                    }
                }

                foreach (\common\classes\department::getCatalogAssignList() as $__department) {
                    $this->view->department_switch_notice[strval($__department['id'])] = array(
                        'categories' => [0, 0],
                        'products' => [0, 0],
                        'original_state' => isset($this->view->department_assigned[(int) $__department['id']]),
                    );
                }
                $sub_categories = array();
                \common\helpers\Categories::get_subcategories($sub_categories, $cInfo->categories_id, true);
                if (count($sub_categories) > 0) {
                    foreach (\common\classes\department::getCatalogAssignList() as $_check_notice_department) {
                        //category assigned, can switch OFF - check assigned subcategories
                        $__check = tep_db_fetch_array(tep_db_query(
                            "SELECT COUNT(*) AS c " .
                            "FROM " . TABLE_DEPARTMENTS_CATEGORIES . " " .
                            "WHERE departments_id='" . $_check_notice_department['id'] . "' AND categories_id IN('" . implode("','", $sub_categories) . "') "
                        ));
                        if ($__check['c'] > 0) {
                            $this->view->department_switch_notice[$_check_notice_department['id']]['categories'][1] = $__check['c'];
                        }
                        //category not assigned, can switch ON - check not assigned subcategories
                        $__check = tep_db_fetch_array(tep_db_query(
                            "SELECT COUNT(*) AS c " .
                            "FROM " . TABLE_CATEGORIES . " c " .
                            " LEFT JOIN " . TABLE_DEPARTMENTS_CATEGORIES . " pc ON pc.categories_id=c.categories_id AND pc.departments_id='" . $_check_notice_department['id'] . "' " .
                            "WHERE c.categories_id IN('" . implode("','", $sub_categories) . "') AND pc.categories_id IS NULL "
                        ));
                        if ($__check['c'] > 0) {
                            $this->view->department_switch_notice[$_check_notice_department['id']]['categories'][0] = $__check['c'];
                        }
                    }
                }

                $sub_categories[] = $cInfo->categories_id;
                foreach (\common\classes\department::getCatalogAssignList() as $_check_notice_platform) {
                    //category assigned, can switch OFF - check assigned products
                    $__check = tep_db_fetch_array(tep_db_query(
                        "SELECT COUNT(*) AS c " .
                        "FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_DEPARTMENTS_PRODUCTS . " plp " .
                        "WHERE p2c.products_id=p.products_id AND p2c.categories_id IN('" . implode("','", $sub_categories) . "') " .
                        "  AND plp.departments_id='" . $_check_notice_platform['id'] . "' AND plp.products_id=p.products_id "
                    ));
                    if ($__check['c'] > 0) {
                        $this->view->department_switch_notice[$_check_notice_platform['id']]['products'][1] = $__check['c'];
                    }
                    //category not assigned, can switch ON - check not assigned products
                    $__check = tep_db_fetch_array(tep_db_query(
                        "SELECT COUNT(*) AS c " .
                        "FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCTS . " p " .
                        "  LEFT JOIN " . TABLE_DEPARTMENTS_PRODUCTS . " plp ON plp.departments_id='" . $_check_notice_platform['id'] . "' AND plp.products_id=p.products_id " .
                        "WHERE p2c.products_id=p.products_id AND p2c.categories_id IN('" . implode("','", $sub_categories) . "') " .
                        "  AND plp.products_id IS NULL "
                    ));
                    if ($__check['c'] > 0) {
                        $this->view->department_switch_notice[$_check_notice_platform['id']]['products'][0] = $__check['c'];
                    }
                }
            } elseif (isset($cInfo->parent_id) && !empty($cInfo->parent_id)) {
                $get_assigned_departments_r = tep_db_query("SELECT departments_id FROM " . TABLE_DEPARTMENTS_CATEGORIES . " WHERE categories_id = '" . intval($cInfo->parent_id) . "' ");
                if (tep_db_num_rows($get_assigned_departments_r) > 0) {
                    while ($_assigned_department = tep_db_fetch_array($get_assigned_departments_r)) {
                        $this->view->department_assigned[(int) $_assigned_department['departments_id']] = (int) $_assigned_department['departments_id'];
                    }
                }
            } else {
                foreach (\common\classes\department::getCatalogAssignList() as $___data) {
                    $this->view->department_assigned[intval($___data['id'])] = intval($___data['id']);
                }
            }
            // }} departments assign

            $cInfo->department_category_price = [];
            foreach( \common\classes\department::getCatalogAssignList() as $_dep_data ) {
                $department_id = $_dep_data['id'];
                $_price_formula = tep_db_fetch_array(tep_db_query(
                    "SELECT ".
                    " api_outgoing_price_formula as formula, ".
                    " api_outgoing_price_discount as discount, ".
                    " api_outgoing_price_surcharge as surcharge, ".
                    " api_outgoing_price_margin as margin ".
                    "FROM ".TABLE_DEPARTMENTS." WHERE departments_id='".(int)$department_id."' "
                ));
                if ( !is_array($_price_formula) ) $_price_formula = [];

                if (isset($cInfo->categories_id) && intval($cInfo->categories_id) > 0) {
                    $_category_formula = \common\classes\ApiDepartment::getCategoryFormulaData((int)$department_id,($cInfo->categories_id?intval($cInfo->categories_id):intval($cInfo->parent_id)));
                    if ( is_array($_category_formula) ) {
                        $_price_formula = $_category_formula;
                    }
                }
                $_price_formula['formula_text'] = '';
                if ( !empty($_price_formula['formula']) ) {
                    $_price_formula_arr = json_decode($_price_formula['formula'],true);
                    if ( is_array($_price_formula_arr) ) $_price_formula['formula_text'] = $_price_formula_arr['text'];
                }

                $cInfo->department_category_price[$_dep_data['id']] = $_price_formula;
            }
        }

        // {{ ep soap
        if ( $categories_id ) {
            $get_linked_r = tep_db_query(
                "SELECT c.ep_holbi_soap_disable_update ".
                "FROM ".TABLE_CATEGORIES." c ".
                " INNER JOIN ep_holbi_soap_link_categories lc ON lc.local_category_id=c.categories_id ".
                "WHERE c.categories_id='".(int)$categories_id."' ".
                "LIMIT 1 "
            );
            if ( tep_db_num_rows($get_linked_r)>0 ) {
                $cInfo->ep_holbi_soap_present = 1;
                $get_linked = tep_db_fetch_array($get_linked_r);
                $cInfo->ep_holbi_soap_disable_update = (int)$get_linked['ep_holbi_soap_disable_update'];
            }
        }
        if ( class_exists('\backend\models\EP\Datasource\HolbiSoap') ) {
            \backend\models\EP\Datasource\HolbiSoap::categoryEdit($cInfo);
        }
        // }} ep soap

        $this->selectedMenu = array('catalog', 'categories');
        $text_new_or_edit = ($categories_id == 0) ? TEXT_INFO_HEADING_NEW_CATEGORY : (TEXT_INFO_HEADING_EDIT_CATEGORY . (empty($cInfo->categories_name) ? '' : ' &quot;' . $cInfo->categories_name . '&quot;'));
        if ( $categories_id == 0 ) {
            $editCategoryInPath = (defined('TEXT_CATEGORY_CREATE_IN')?TEXT_CATEGORY_CREATE_IN:'').' '.'<ul class="category_path_list top_bead-items"><li class="category_path">'.\common\helpers\Categories::output_generated_category_path($cInfo->parent_id,'category','<a href="' . Yii::$app->urlManager->createUrl('categories') . '?category_id=%s" class="category_path__location">%2$s</a>','</li><li class="category_path onemore">').'</li></ul>';
        }else{
            $editCategoryInPath = (defined('TEXT_CATEGORY_PLACED_IN')?TEXT_CATEGORY_PLACED_IN:'').' '.'<ul class="category_path_list top_bead-items"><li class="category_path">'.\common\helpers\Categories::output_generated_category_path($cInfo->parent_id,'category','<a href="' . Yii::$app->urlManager->createUrl('categories') . '?category_id=%s" class="category_path__location">%2$s</a>','</li><li class="category_path onemore">').'</li></ul>';
        }
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('categories/index'), 'title' => sprintf($text_new_or_edit, \common\helpers\Categories::output_generated_category_path($categories_id)));

        $seo_url = tep_db_fetch_array(tep_db_query("select categories_seo_page_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . $categories_id . "' and language_id = '" . (int) \common\helpers\Language::get_default_language_id() . "'"));
        foreach (\common\classes\platform::getList(false) as $frontend) {
            if ($this->view->platform_assigned[$frontend['id']] ?? null) {
                if (isset($seo_url['categories_seo_page_name']) && !empty($seo_url['categories_seo_page_name'])) {
                    $this->view->preview_link[] = [
                        'link' => '//' . $frontend['platform_url'] . '/' . $seo_url['categories_seo_page_name'],
                        'name' => $frontend['text']
                    ];
                } else {
                    $this->view->preview_link[] = [
                        'link' => '//' . $frontend['platform_url'] . '/catalog/index?cPath=' . $categories_id,
                        'name' => $frontend['text']
                    ];
                }
            }
        }
        if (isset($this->view->preview_link) && count($this->view->preview_link) > 1) {
            $this->topButtons[] = '<a href="#choose-frontend" class="btn btn-primary btn-choose-frontend">' . TEXT_PREVIEW_ON_SITE . '</a>';
        } else {
            $this->topButtons[] = '<a href="' . ($this->view->preview_link[0]['link']??null) . '" target="_blank" class="btn btn-primary">' . TEXT_PREVIEW_ON_SITE . '</a>';
        }


        // {{ suppliers
        $supplierRules = new \backend\models\SuppliersRules();
        if ( $categories_id ) {
            $categoryObj = \common\models\Categories::findOne(['categories_id' => $categories_id]);
        }else{
            $categoryObj = new \common\models\Categories();
        }
        $supplierRules->getCategoryData($categoryObj, $cInfo);
        // }} suppliers

        if (isset($cInfo->stock_limit) && $cInfo->stock_limit >= 0) {
            $cInfo->stock_limit_on = true;
        } else {
            $cInfo->stock_limit_on = false;
            $cInfo->stock_limit = (int)ADDITIONAL_STOCK_LIMIT;
        }

        $bannerGroups[] = '';
        $banners = \common\models\BannersGroups::find()->asArray()->all();
        foreach ($banners as $banner) {
            $bannerGroups[$banner['id']] = $banner['banners_group'];
        }

//        $xsellProducts = [0=>[]];
//        $this->view->xsellTypes = [];
//        $get_xsell_types_r = tep_db_query("SELECT xsell_type_id, xsell_type_name FROM ".TABLE_PRODUCTS_XSELL_TYPE." WHERE language_id='".$languages_id."' ORDER BY xsell_type_name");
//        if ( tep_db_num_rows($get_xsell_types_r)>0 ) {
//            while ( $_xsell_type = tep_db_fetch_array($get_xsell_types_r) ) {
//                $this->view->xsellTypes[$_xsell_type['xsell_type_id']] = $_xsell_type['xsell_type_name'];
//                $xsellProducts[$_xsell_type['xsell_type_id']] = [];
//            }
//        }
//        $this->view->xsellProducts = $xsellProducts;
//
//        $currencies = Yii::$container->get('currencies');
//        $query = tep_db_query("select cpxs.xsell_products_id as xsell_id, cpxs.xsell_type_id, cpxs.sort_order, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name, p.products_status from  " . TABLE_CATS_PRODUCTS_XSELL . " cpxs, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where cpxs.xsell_products_id = p.products_id and cpxs.xsell_products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and cpxs.categories_id = '" . (int) $categories_id . "' order by cpxs.xsell_type_id, cpxs.sort_order");
//        while ($data = tep_db_fetch_array($query)) {
//            if ( !isset($xsellProducts[$data['xsell_type_id']]) ) continue;
//            if (empty($data['products_name'])) {
//                $data['products_name'] = \common\helpers\Product::get_products_name($data['xsell_id']);
//            }
//            $xsellProducts[$data['xsell_type_id']][] = [
//                'xsell_id' => $data['xsell_id'],
//                'id' => $data['xsell_id'],
//                'products_name' => $data['products_name'],
//                'name' => $data['products_name'],
//                'image' => \common\classes\Images::getImage($data['xsell_id'], 'Small'),
//                'price' => $currencies->format(\common\helpers\Product::get_products_price($data['xsell_id'])),
//                'status_class' => ($data['products_status'] == 0 ? 'dis_prod' : ''),
//            ];
//        }
//        $this->view->xsellProducts = $xsellProducts;
        foreach (\common\helpers\Hooks::getList('categories/categoryedit/before-render') as $filename) {
            include($filename);
        }
        global $navigation;
        if (sizeof($navigation->snapshot) > 0) {
            $backUrl = Yii::$app->urlManager->createUrl(array_merge([$navigation->snapshot['page']], $navigation->snapshot['get']));
        } else {
            $backUrl = Yii::$app->urlManager->createUrl(['category', 'category_id' => $cInfo->parent_id]);
        }

        $heroImages = ImageTypes::find()
            ->where(['image_types_name' => 'Category hero'])
            ->andWhere(['not', ['parent_id' => 0]])
            ->asArray()->all();
        if (is_array($heroImages)) {
            foreach ($heroImages as $key => $size) {
                $categoriesImages = CategoriesImages::find()->where([
                    'categories_id' => $categories_id,
                    'image_types_id' => $size['image_types_id']
                ])->asArray()->all();
                if (is_array($categoriesImages)) {
                    foreach ($categoriesImages as $categoriesImage) {
                        $heroImages[$key]['images'][$categoriesImage['image_types_id']][$categoriesImage['platform_id']] = $categoriesImage;
                    }
                }
            }
        }

        return $this->render('categoryedit', [
                    'infoBreadCrumb' => $editCategoryInPath,
                    'categories_id' => $categories_id,
                    'cInfo' => $cInfo,
                    'languages' => $languages,
                    'cDescription' => $cDescription,
                    'js_platform_switch_notice' => json_encode($this->view->platform_switch_notice),
                    'departments' => $departments,
                    'js_department_switch_notice' => json_encode($this->view->department_switch_notice),
                    'templates' => \backend\design\CategoryTemplate::categoryedit($categories_id),
                    'upload_path' => \Yii::getAlias('@web') . '/uploads/',
                    'images' => \common\helpers\Image::getCategoriesAdditionalImages($categories_id),
                    'bannerGroups' => $bannerGroups,
                    'backUrl' => $backUrl,
                    'heroImages' => $heroImages,
        ]);
    }

    public function actionCategorySubmit() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/categories');
        $messageStack = \Yii::$container->get('message_stack');

        $this->view->errorMessageType = 'success';
        $this->view->errorMessage = '';
        $this->layout = false;

        $current_category_id = (int) Yii::$app->request->post('parent_category_id', 0); //can change current category
        $popup = (int) Yii::$app->request->post('popup');
        $categories_id = (int) Yii::$app->request->post('categories_id');

        if ($categories_id > 0) {
            $action = 'update_category';
            $catInfo = \common\models\Categories::findOne($categories_id);
            $category = $catInfo;
            if (!$catInfo) {
              $categories_id = null;
              $action = 'insert_category';
            }
        } else {
            $action = 'insert_category';
        }

        //if ($action == 'update_category') {
        if (\common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
            $logger = new \common\extensions\ReportChangesHistory\classes\Logger();
            $beforeObject = new \common\api\Classes\Category();
            $beforeObject->load($categories_id);
            $logger->setBeforeObject($beforeObject);
            unset($beforeObject);
        }
        //}

        $categories_status = (int)tep_db_prepare_input(Yii::$app->request->post('categories_status', ''));
        $default_sort_order = tep_db_prepare_input(Yii::$app->request->post('default_sort_order', ''));

        $post = Yii::$app->request->post();
        if ($categories_id>0) {
          $pSettings = \common\models\CategoriesPlatformSettings::find()->andWhere(['categories_id' => $categories_id])->indexBy('platform_id')->all();
          if ($pSettings) {
            foreach($pSettings as $platform_id => $ps) {
              if (!isset($post['plaformsettings'][$platform_id])) {
                  foreach (['', '_2', '_3', '_4'] as $mod) {
                      $imageName = 'categories_image' . $mod;
                      if (!empty($ps->$imageName)) {
                          $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . $ps->$imageName;
                          if (file_exists($image_location)) @unlink($image_location);
                          Images::removeResizeImages($ps->$imageName);
                          Images::removeWebp($ps->$imageName);
                      }
                  }
                $ps->delete();
              } else {
                $post['plaformsettings'][$platform_id] = $ps;
              }
            }
            unset($pSettings);
          }
        }

        \common\helpers\Image::saveCategoriesAdditionalImages(Yii::$app->request->post('additional_categories'), $categories_id);

        if ($action == 'insert_category') {
            $sql_data_array = [
                'parent_id' => $current_category_id,
                'date_added' => 'now()'
            ];
            tep_db_perform(TABLE_CATEGORIES, $sql_data_array);
            $categories_id = tep_db_insert_id();
            /** @var \common\extensions\UserGroupsRestrictions\UserGroupsRestrictions $ext */
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
                if ( $groupService = $ext::getGroupsService() ){
                    $groupService->addCategoryToAllGroups($categories_id);
                }
            }
            Yii::$app->request->setBodyParams(array_merge(Yii::$app->request->getBodyParams(), ['categories_id' => $categories_id, 'popup' => $popup]));

        }
        $heroImageMain = [];
        $heroImageMainUpdate = [];

/* platform settings main tab*/
        if (!empty($post['plaformsettings']) && is_array($post['plaformsettings'])) {
          foreach ($post['plaformsettings'] as $platform_id => $ps) {

            $maps_id = (int)tep_db_prepare_input($_POST['maps_id'][$platform_id]);

            foreach (['gallery' => '', 'hero' => '_2', 'homepage' => '_3', 'menu' => '_4'] as $imageType => $mod) {
                $imageName = 'categories_image' . $mod;
                $oldImage = ($platform_id == 0 ? ($category->$imageName ?? '') : ($ps->$imageName ?? ''));
                $deleteImage = (boolean)($post['delete_image' . $mod][$platform_id] ?? false);

                $sql_data_array[$imageName] = \common\helpers\Image::prepareSavingImage(
                    $oldImage,
                    $post[$imageName][$platform_id],
                    $post['categories_image_loaded' . $mod][$platform_id],
                    'categories' . DIRECTORY_SEPARATOR . $categories_id . DIRECTORY_SEPARATOR . $imageType,
                    $deleteImage
                );
                if ($imageType == 'hero') {
                    $heroImageMain[$platform_id] = $sql_data_array[$imageName];
                    if ($sql_data_array[$imageName] == $oldImage) {
                        $heroImageMainUpdate[$platform_id] = false;
                    } else {
                        $heroImageMainUpdate[$platform_id] = true;
                    }
                }

                if ($deleteImage && $oldImage) {
                    Images::removeResizeImages($oldImage);
                }
                Images::createResizeImages($sql_data_array[$imageName], 'Category ' . $imageType);
            }

            $sql_data_array['maps_id'] = $maps_id;

            $sql_data_array['show_on_home'] = (!empty($post['show_on_home'][$platform_id]) ? 1 : 0);
            if ($platform_id==0) {
              $_catData = $sql_data_array;
            } else {
              try {
                if (!is_object($ps)) {
                  $ps = new \common\models\CategoriesPlatformSettings();
                }
                if ($ps) {
                  $sql_data_array['platform_id'] = $platform_id;
                  if ($ps->load($sql_data_array, "") ) {
                      $ps->categories_id = $categories_id;
                      $ps->save();
                      unset($ps);
                  } else {
                    Yii::warning(print_r($ps->getErrors(),1), 'CATEGORYPLATFORMSETTINGS');
                  }
                }
              } catch (\Exception $e) {
                Yii::warning(print_r($e,1), 'CATEGORYPLATFORMSETTINGS');
              }
            }
          }
          if ($_catData) {
            $sql_data_array = $_catData;
          } else {
            $sql_data_array = [];
          }
          unset($post['plaformsettings'][$platform_id]);
        }

        $sql_data_array['categories_status'] = $categories_status;
        $sql_data_array['default_sort_order'] = $default_sort_order;
        $sql_data_array['banners_group'] = tep_db_prepare_input(Yii::$app->request->post('banners_group', ''));

        $sql_data_array['stock_limit'] = (int)Yii::$app->request->post('stock_limit', -1);

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('AutomaticallyStatus', 'allowed')) {
            $_sql_data = $ext::onCategorySave();
            if ( is_array($_sql_data) ) $sql_data_array = array_merge($sql_data_array,$_sql_data);
        }

        // Moved to SeoRedirectsNamed
        // $sql_data_array['categories_old_seo_page_name'] = tep_db_prepare_input($_POST['categories_old_seo_page_name']);

        $update_sql_data = [
            'last_modified' => 'now()',
        ];
        \common\helpers\Categories::set_categories_status($categories_id, $categories_status);
        $sql_data_array = array_merge($sql_data_array, $update_sql_data);
        tep_db_perform(TABLE_CATEGORIES, $sql_data_array, 'update', "categories_id = '" . (int) $categories_id . "'");
        $this->view->errorMessage = TEXT_INFO_UPDATED;


        if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
            $ext::saveCategoryLinks($categories_id, $_POST);
        }

        $categories_name = Yii::$app->request->post('categories_name');
        $categories_description = Yii::$app->request->post('categories_description');
        $categories_seo_page_name = Yii::$app->request->post('categories_seo_page_name');
        $noindex_option = Yii::$app->request->post('noindex_option');
        $nofollow_option = Yii::$app->request->post('nofollow_option');
        $rel_canonical = Yii::$app->request->post('rel_canonical');
        $categories_head_title_tag = Yii::$app->request->post('categories_head_title_tag');
        $categories_head_desc_tag = Yii::$app->request->post('categories_head_desc_tag');
        $categories_head_keywords_tag = Yii::$app->request->post('categories_head_keywords_tag');
        $categories_h1_tag = Yii::$app->request->post('categories_h1_tag');
        $categories_h2_tag = Yii::$app->request->post('categories_h2_tag');
        $categories_h3_tag = Yii::$app->request->post('categories_h3_tag');
        $categories_image_alt_tag_mask = Yii::$app->request->post('categories_image_alt_tag_mask');
        $categories_image_title_tag_mask = Yii::$app->request->post('categories_image_title_tag_mask');

        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = [
                'categories_name' => tep_db_prepare_input($categories_name[$language_id]),
                'categories_description' => tep_db_prepare_input($categories_description[$language_id]),
                'categories_seo_page_name' => tep_db_prepare_input($categories_seo_page_name[$language_id]),
                'noindex_option' => (isset($noindex_option[$language_id]) ? (int)$noindex_option[$language_id] : 0),
                'nofollow_option' => (isset($nofollow_option[$language_id]) ? (int)$nofollow_option[$language_id] : 0),
                'rel_canonical' => tep_db_prepare_input($rel_canonical[$language_id]),
                'categories_head_title_tag' => tep_db_prepare_input($categories_head_title_tag[$language_id]),
                'categories_head_desc_tag' => tep_db_prepare_input($categories_head_desc_tag[$language_id]),
                'categories_head_keywords_tag' => tep_db_prepare_input($categories_head_keywords_tag[$language_id]),
                'categories_h1_tag' => tep_db_prepare_input($categories_h1_tag[$language_id]),
                'categories_h2_tag' => tep_db_prepare_input(is_array($categories_h2_tag[$language_id]) ? implode("\n", $categories_h2_tag[$language_id]) : $categories_h2_tag[$language_id]),
                'categories_h3_tag' => tep_db_prepare_input(is_array($categories_h3_tag[$language_id]) ? implode("\n", $categories_h3_tag[$language_id]) : $categories_h3_tag[$language_id]),
                'categories_image_alt_tag_mask' => (isset($categories_image_alt_tag_mask[$language_id]) ? tep_db_prepare_input($categories_image_alt_tag_mask[$language_id]) : ''),
                'categories_image_title_tag_mask' => (isset($categories_image_title_tag_mask[$language_id]) ? tep_db_prepare_input($categories_image_title_tag_mask[$language_id]) : ''),
            ];

            if (empty($sql_data_array['categories_seo_page_name'])) {
                $sql_data_array['categories_seo_page_name'] = Seo::makeSlug(tep_db_prepare_input($_POST['categories_name'][$languages_id]));
            }

            $check_category = tep_db_query("select * from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . $categories_id . "' and language_id = '" . $languages[$i]['id'] . "' and affiliate_id = 0");
            if ($action == 'insert_category' || !tep_db_num_rows($check_category)) {
                $insert_sql_data = [
                    'categories_id' => $categories_id,
                    'language_id' => $languages[$i]['id']
                ];
                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
                tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array);
            } elseif ($action == 'update_category') {
                $check_category_data = tep_db_fetch_array($check_category);
                tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array, 'update', "categories_id = '" . (int) $categories_id . "' and language_id = '" . (int) $languages[$i]['id'] . "' and affiliate_id = 0");
                if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                    $ext::trackCategoryLinks($categories_id, $language_id, null, $sql_data_array, $check_category_data);
                }
            }
        }

        $_platform_list = \common\classes\platform::getCategoriesAssignList();
        $assign_platform = array();
        if (count($_platform_list) == 1) {
            $assign_platform[] = (int) $_platform_list[0]['id'];
        } else {
            $assign_platform = array_map('intval', Yii::$app->request->post('platform', array()));
        }
        $category_product_assign = Yii::$app->request->post('category_product_assign', array());
        $sub_categories = array((int) $categories_id);
        \common\helpers\Categories::get_subcategories($sub_categories, (int) $categories_id);
        $removed_mapping_pool = array();
        if (count($assign_platform) > 0) {
            $get_removed_r = tep_db_query(
                    "SELECT DISTINCT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " " .
                    "WHERE categories_id IN('" . implode("','", $sub_categories) . "') AND platform_id NOT IN('" . implode("','", $assign_platform) . "') "
            );
            while ($_removed = tep_db_fetch_array($get_removed_r)) {
                $removed_mapping_pool[] = $_removed;
            }
            tep_db_query("DELETE FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id IN('" . implode("','", $sub_categories) . "') AND platform_id NOT IN('" . implode("','", $assign_platform) . "') ");
        } else {
            $get_removed_r = tep_db_query(
                    "SELECT DISTINCT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " " .
                    "WHERE categories_id IN('" . implode("','", $sub_categories) . "') "
            );
            while ($_removed = tep_db_fetch_array($get_removed_r)) {
                $removed_mapping_pool[] = $_removed;
            }
            tep_db_query("DELETE FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id IN('" . implode("','", $sub_categories) . "')");
        }
        if (count($removed_mapping_pool) > 0) {
            foreach ($removed_mapping_pool as $removed_mapping) {
                $__remove_ids = array();
                $get_cleanup_ids_r = tep_db_query(
                        "  SELECT /*count(*) as ttl,*/ plp.products_id/*,  max(IF(plc.categories_id is null , if(p2c.categories_id=0,0,-1), plc.categories_id)) AS plc_categories_id*/ " .
                        "  FROM " . TABLE_PLATFORMS_PRODUCTS . " plp " .
                        "    INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c on p2c.products_id=plp.products_id " .
                        "    LEFT JOIN " . TABLE_PLATFORMS_CATEGORIES . " plc on plc.categories_id=p2c.categories_id AND plc.platform_id=plp.platform_id " .
                        "  WHERE plp.platform_id='{$removed_mapping['platform_id']}' " .
                        "  GROUP BY plp.products_id HAVING MAX(IF(plc.categories_id IS NULL, IF(p2c.categories_id=0,0,-1), plc.categories_id))=-1 "
                );
                while ($_cleanup_ids = tep_db_fetch_array($get_cleanup_ids_r)) {
                    $__remove_ids[] = $_cleanup_ids['products_id'];
                    if (count($__remove_ids) > 99) {
                        tep_db_query(
                                "DELETE FROM " . TABLE_PLATFORMS_PRODUCTS . " " .
                                "WHERE platform_id='{$removed_mapping['platform_id']}' AND products_id IN(" . implode(',', $__remove_ids) . ") "
                        );
                        $__remove_ids = array();
                    }
                }
                if (count($__remove_ids) > 0) {
                    tep_db_query(
                            "DELETE FROM " . TABLE_PLATFORMS_PRODUCTS . " " .
                            "WHERE platform_id='{$removed_mapping['platform_id']}' AND products_id IN(" . implode(',', $__remove_ids) . ") "
                    );
                    $__remove_ids = array();
                }
            }
        }
        foreach ($assign_platform as $assign_platform_id) {
            $_check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id='" . (int) $categories_id . "' AND platform_id='" . $assign_platform_id . "' "));
            if ($_check['c'] == 0) {
                tep_db_perform(TABLE_PLATFORMS_CATEGORIES, array(
                    'categories_id' => (int) $categories_id,
                    'platform_id' => $assign_platform_id,
                ));
            }
            if (isset($category_product_assign[$assign_platform_id]) && $category_product_assign[$assign_platform_id] == 'yes') {
                tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_PRODUCTS . " (products_id, platform_id) SELECT p2c.products_id, '" . $assign_platform_id . "' FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c WHERE p2c.categories_id='" . (int) $categories_id . "' ");
                foreach( $sub_categories as $__sub_category_id ) {
                    if ( (int)$__sub_category_id==(int)$categories_id ) continue;
                    tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_CATEGORIES . " (categories_id, platform_id) VALUES('" . (int)$__sub_category_id . "','" . $assign_platform_id . "') ");
                    tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_PRODUCTS . " (products_id, platform_id) SELECT p2c.products_id, '" . $assign_platform_id . "' FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c WHERE p2c.categories_id='" . (int)$__sub_category_id . "' ");
                }
            }
        }

        if (defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True) {
            // {{ departments assign
            $_department_list = \common\classes\department::getCatalogAssignList();
            $assign_department = array();
            if (count($_department_list) == 1) {
                $assign_department[] = (int) $_department_list[0]['id'];
            } else {
                $assign_department = array_map('intval', Yii::$app->request->post('departments', array()));
            }
            $department_category_product_assign = Yii::$app->request->post('department_category_product_assign', array());
            $sub_categories = array((int) $categories_id);
            \common\helpers\Categories::get_subcategories($sub_categories, (int) $categories_id);
            $removed_mapping_pool = array();
            if (count($assign_department) > 0) {
                $get_removed_r = tep_db_query(
                    "SELECT DISTINCT departments_id FROM " . TABLE_DEPARTMENTS_CATEGORIES. " " .
                    "WHERE categories_id IN('" . implode("','", $sub_categories) . "') AND departments_id NOT IN('" . implode("','", $assign_department) . "') "
                );
                while ($_removed = tep_db_fetch_array($get_removed_r)) {
                    $removed_mapping_pool[] = $_removed;
                }
                tep_db_query("DELETE FROM " . TABLE_DEPARTMENTS_CATEGORIES . " WHERE categories_id!=0 AND categories_id IN('" . implode("','", $sub_categories) . "') AND departments_id NOT IN('" . implode("','", $assign_department) . "') ");
            } else {
                $get_removed_r = tep_db_query(
                    "SELECT DISTINCT departments_id FROM " . TABLE_DEPARTMENTS_CATEGORIES . " " .
                    "WHERE categories_id IN('" . implode("','", $sub_categories) . "') "
                );
                while ($_removed = tep_db_fetch_array($get_removed_r)) {
                    $removed_mapping_pool[] = $_removed;
                }
                tep_db_query("DELETE FROM " . TABLE_DEPARTMENTS_CATEGORIES . " WHERE categories_id!=0 AND categories_id IN('" . implode("','", $sub_categories) . "')");
            }
            if (count($removed_mapping_pool) > 0) {
                foreach ($removed_mapping_pool as $removed_mapping) {
                    $__remove_ids = array();
                    $get_cleanup_ids_r = tep_db_query(
                        "  SELECT /*count(*) as ttl,*/ plp.products_id/*,  max(IF(plc.categories_id is null , if(p2c.categories_id=0,0,-1), plc.categories_id)) AS plc_categories_id*/ " .
                        "  FROM " . TABLE_DEPARTMENTS_PRODUCTS . " plp " .
                        "    INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c on p2c.products_id=plp.products_id " .
                        "    LEFT JOIN " . TABLE_DEPARTMENTS_CATEGORIES . " plc on plc.categories_id=p2c.categories_id AND plc.departments_id=plp.departments_id " .
                        "  WHERE plp.departments_id='{$removed_mapping['departments_id']}' " .
                        "  GROUP BY plp.products_id HAVING MAX(IF(plc.categories_id IS NULL, IF(p2c.categories_id=0,0,-1), plc.categories_id))=-1 "
                    );
                    while ($_cleanup_ids = tep_db_fetch_array($get_cleanup_ids_r)) {
                        $__remove_ids[] = $_cleanup_ids['products_id'];
                        if (count($__remove_ids) > 99) {
                            tep_db_query(
                                "DELETE FROM " . TABLE_DEPARTMENTS_PRODUCTS . " " .
                                "WHERE departments_id='{$removed_mapping['departments_id']}' AND products_id IN(" . implode(',', $__remove_ids) . ") "
                            );
                            $__remove_ids = array();
                        }
                    }
                    if (count($__remove_ids) > 0) {
                        tep_db_query(
                            "DELETE FROM " . TABLE_DEPARTMENTS_PRODUCTS . " " .
                            "WHERE departments_id='{$removed_mapping['departments_id']}' AND products_id IN(" . implode(',', $__remove_ids) . ") "
                        );
                        $__remove_ids = array();
                    }
                }
            }
            foreach ($assign_department as $assign_department_id) {
                $_check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM " . TABLE_DEPARTMENTS_CATEGORIES . " WHERE categories_id='" . (int) $categories_id . "' AND departments_id='" . $assign_department_id . "' "));
                if ($_check['c'] == 0) {
                    tep_db_perform(TABLE_DEPARTMENTS_CATEGORIES, array(
                        'categories_id' => (int) $categories_id,
                        'departments_id' => $assign_department_id,
                    ));
                }
                if (isset($department_category_product_assign[$assign_department_id]) && $department_category_product_assign[$assign_department_id] == 'yes') {
                    tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_PRODUCTS . " (products_id, departments_id) SELECT p2c.products_id, '" . $assign_department_id . "' FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c WHERE p2c.categories_id='" . (int) $categories_id . "' ");
                    for ($_sub_category_idx = 1; $i < count($sub_categories) - 1; $_sub_category_idx++) {
                        $sub_categories[$_sub_category_idx];
                        tep_db_query("REPLACE INTO " . TABLE_DEPARTMENTS_CATEGORIES . " (categories_id, departments_id) VALUES('" . (int) $sub_categories[$_sub_category_idx] . "','" . $assign_department_id . "') ");
                        tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_PRODUCTS . " (products_id, departments_id) SELECT p2c.products_id, '" . $assign_department_id . "' FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c WHERE p2c.categories_id='" . (int) $sub_categories[$_sub_category_idx] . "' ");
                    }
                }
            }
            // }} departments assign
            // {{ departments_categories_price_formula
            $department_category_price = Yii::$app->request->post('department_category_price');
            if ( is_array($department_category_price) ) {
                $department_category_price = tep_db_prepare_input($department_category_price);
                foreach ($department_category_price as $department_id=>$price_config){
                    $parent_formula_data = tep_db_fetch_array(tep_db_query(
                        "SELECT ".
                        " api_outgoing_price_formula as formula, ".
                        " api_outgoing_price_discount as discount, ".
                        " api_outgoing_price_surcharge as surcharge, ".
                        " api_outgoing_price_margin as margin ".
                        "FROM ".TABLE_DEPARTMENTS." WHERE departments_id='".(int)$department_id."' "
                    ));
                    if ( !is_array($parent_formula_data) ) continue;

                    $_category_formula = \common\classes\ApiDepartment::getCategoryFormulaData((int)$department_id, intval($categories_id), true);
                    if ( is_array($_category_formula) ) {
                        $parent_formula_data['formula'] = $_category_formula['formula'];
                        $parent_formula_data['discount'] = $_category_formula['discount'];
                        $parent_formula_data['surcharge'] = $_category_formula['surcharge'];
                        $parent_formula_data['margin'] = $_category_formula['margin'];
                    }

                    $_extracted_price_config = json_decode($price_config['formula'], true);
                    if ( !is_array($_extracted_price_config) || empty($_extracted_price_config['formula']) || (isset($_extracted_price_config['formula'][0]) && empty($_extracted_price_config['formula'][0])) ) $price_config['formula'] = '';

                    $department_category_price_formula = [
                        'formula' => $price_config['formula'],
                        'discount' => number_format(floatval($price_config['discount']),2,'.',''),
                        'surcharge' =>  number_format(floatval($price_config['surcharge']),2,'.',''),
                        'margin' =>  number_format(floatval($price_config['margin']),2,'.',''),
                    ];

                    tep_db_query("DELETE FROM departments_categories_price_formula WHERE departments_id='".(int)$department_id."' AND categories_id='".(int)$categories_id."'");

                    if ( $department_category_price_formula['formula']=='' ) continue;
                    if ( $parent_formula_data!=$department_category_price_formula ) {
                        tep_db_perform('departments_categories_price_formula',array_merge([
                            'departments_id' => (int)$department_id,
                            'categories_id' => (int)$categories_id,
                        ],$department_category_price_formula));
                    }
                }
            }
            // }} departments_categories_price_formula
        }
        /* if (SUPPLEMENT_STATUS == 'True') {
          tep_db_query("delete from " . TABLE_CATS_PRODUCTS_XSELL . " where categories_id = '" . (int)$categories_id . "'");
          if (is_array($_POST['xsell_product_id'])){
          foreach ($_POST['xsell_product_id'] as $key => $value){
          tep_db_query("insert into " . TABLE_CATS_PRODUCTS_XSELL . " (categories_id, xsell_products_id, sort_order) values ('" . tep_db_input($categories_id) . "', '" . tep_db_input($value) . "', '" . tep_db_input($_POST['xsell_products_sort_order'][$key]). "')");
          }
          }
          tep_db_query("delete from " . TABLE_CATS_PRODUCTS_UPSELL . " where categories_id = '" . (int)$categories_id . "'");
          if (is_array($_POST['upsell_product_id'])){
          foreach ($_POST['upsell_product_id'] as $key => $value){
          tep_db_query("insert into " . TABLE_CATS_PRODUCTS_UPSELL . " (categories_id, upsell_products_id, sort_order) values ('" . tep_db_input($categories_id) . "', '" . tep_db_input($value) . "', '" . tep_db_input($_POST['upsell_products_sort_order'][$key]). "')");
          }
          }

          tep_db_query("delete from " . TABLE_CATEGORIES_UPSELL . " where categories_id = '" . (int)$categories_id . "'");
          if (is_array($_POST['upsell_category_id'])){
          foreach ($_POST['upsell_category_id'] as $key => $value){
          tep_db_query("insert into " . TABLE_CATEGORIES_UPSELL . " (categories_id, upsell_id, sort_order) values ('" . tep_db_input($categories_id) . "', '" . tep_db_input($value) . "', '" . tep_db_input($_POST['upsell_category_sort_order'][$key]). "')");
          }
          }

          } */

        foreach (\common\helpers\Hooks::getList('categories/categoryedit') as $filename) {
            include($filename);
        }

        \backend\design\CategoryTemplate::categorySubmit($categories_id);

        if ( Yii::$app->request->post('supplier_price_rule_present',0) ) {
            $categoryObj = \common\models\Categories::findOne(['categories_id'=>$categories_id]);
            $supplierRules = new \backend\models\SuppliersRules();
            $supplierRules->saveCategoryData($categoryObj, Yii::$app->request->post('suppliers_data',[]));
        }

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }

        if ($popup != 1) {
          \common\helpers\Categories::update_categories();
        }

        //if ($action == 'update_category') {
        if (isset($logger) && \common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
            $afterObject = new \common\api\Classes\Category();
            $afterObject->load($categories_id);
            $logger->setAfterObject($afterObject);
            unset($afterObject);
            $logger->run();
        }

        $heroImages = Yii::$app->request->post('heroImage', []);
        $heroImagesLoaded = Yii::$app->request->post('heroImage_loaded', []);
        $heroImagesDelete = Yii::$app->request->post('heroImage_delete', []);
        $heroImagesPosition = Yii::$app->request->post('heroImage_position', []);
        $heroImagesFit = Yii::$app->request->post('heroImage_fit', []);
        if (count($heroImages)) {
            foreach ($heroImages as $typeId => $heroImagePlatforms) {
                $imageTypes = ImageTypes::find()->where(['image_types_id' => $typeId])->asArray()->one();
                foreach ($heroImagePlatforms as $platformId => $heroImage) {
                    $categoriesImages = CategoriesImages::findOne([
                            'categories_id' => $categories_id,
                            'platform_id' => $platformId,
                            'image_types_id' => $typeId,
                    ]);
                    if ((($heroImageMainUpdate[$platformId] ?? false) && !($heroImagesLoaded[$typeId][$platformId] ?? false)) ||
                        (!$heroImage && !($heroImagesLoaded[$typeId][$platformId] ?? false) && ($heroImageMain[$platformId] ?? false))
                    ) {
                        $heroImagesLoaded[$typeId][$platformId] = DIR_WS_IMAGES . $heroImageMain[$platformId];
                    }
                    $image = \common\helpers\Image::prepareSavingImage(
                        $categoriesImages->image ?? '',
                        $heroImage,
                        $heroImagesLoaded[$typeId][$platformId],
                        'categories' . DIRECTORY_SEPARATOR . $categories_id . DIRECTORY_SEPARATOR . 'hero',
                        $heroImagesDelete[$typeId][$platformId],
                        false,
                        [
                            'width' => $imageTypes['image_types_x'],
                            'height' => $imageTypes['image_types_y'],
                            'fit' => $heroImagesFit[$typeId][$platformId]
                        ]
                    );
                    if (!$categoriesImages && $image) {
                        $categoriesImages = new CategoriesImages();
                        $categoriesImages->categories_id = $categories_id;
                        $categoriesImages->image = $image;
                        $categoriesImages->platform_id = $platformId;
                        $categoriesImages->image_types_id = $typeId;
                        $categoriesImages->position = $heroImagesPosition[$typeId][$platformId];
                        $categoriesImages->fit = $heroImagesFit[$typeId][$platformId];
                        $categoriesImages->save();
                    } elseif ($categoriesImages && !$image) {
                        $categoriesImages->delete();
                    } elseif ($categoriesImages && $image) {
                        $categoriesImages->image = $image;
                        $categoriesImages->position = $heroImagesPosition[$typeId][$platformId];
                        $categoriesImages->fit = $heroImagesFit[$typeId][$platformId];
                        $categoriesImages->save();
                    }
                }
            }
        }

        \common\components\CategoriesCache::getCPC()::invalidateCategories((int)$categories_id);

        if ($popup == 1) {
            $this->view->categoriesTree = $this->getCategoryTree();
            if ($categories_id>0) {
              $this->view->categoriesOpenedTree = \common\helpers\Categories::getCategoryParentsIds($categories_id);
            } else {
              $this->view->categoriesOpenedTree = [];
            }
            $this->view->categoriesClosedTree = array_map('intval', explode('|', \Yii::$app->session->get('closed_data')));
            $collapsed = $this->defaultCollapsed;
            return $this->render('cat_main_box', ['directOutput' => true, 'collapsed' => $collapsed]);
        }

        if ($messageStack->size() > 0) {
            $this->view->errorMessage = $messageStack->output(true);
            $this->view->errorMessageType = $messageStack->messageType;
        }
        echo $this->render('error');
        echo '<script> window.location.href="' . Yii::$app->urlManager->createUrl(['categories/categoryedit', 'categories_id' => $categories_id]) . '";</script>';
        //die();
        //return $this->actionCategoryedit();
    }

    public function actionBundleSearch() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $q = Yii::$app->request->getParam('q');
        $prid = Yii::$app->request->getParam('prid', 0);

        $products_string = '';
        $products_query = tep_db_query("select distinct p.products_id, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name, count(sp.sets_id) is_bundle_set from " . TABLE_PRODUCTS . " p left join " . TABLE_SETS_PRODUCTS . " sp on sp.sets_id = p.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and (p.products_model like '%" . tep_db_input($q) . "%' or pd.products_name like '%" . tep_db_input($q) . "%') and p.products_id <> '" . (int) $prid . "' group by p.products_id having is_bundle_set = 0 order by p.sort_order, pd.products_name");
        while ($products = tep_db_fetch_array($products_query)) {
            if (empty($products['products_name'])) {
                $products['products_name'] = \common\helpers\Product::get_products_name($products['products_id']);
            }
            $products_string .= '<option id="' . $products['products_id'] . '" value="prod_' . $products['products_id'] . '" style="COLOR:#555555">' . $products['products_name'] . '</option>';
        }

        echo json_encode(array(
            'tf' => '<select name="sets_select" size="16" style="width:100%">' . $products_string . '</select>'
        ));
    }

    /* public function actionEditcategorypopup() {

      \common\helpers\Translation::init('admin/categories');

      $this->layout = false;
      return $this->render('editcategorypopup');
      } */

    public function actionDeleteBatch()
    {
        $this->layout = false;
        $current_categories_id = (int)Yii::$app->request->post('categories_id',0);

        $items =  Yii::$app->request->post('batch',[]);
        if ( is_array($items) && count($items)>0 ) {
            foreach ($items as $item) {
                list($what, $id) = explode('_',$item,2);
                if ( $what=='p' && $id > 0) {
                    if (\common\helpers\Acl::rule(['TABLE_HEADING_PRODUCTS', 'IMAGE_DELETE'])) {
                        $product_id = $id;

                        if (\common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
                            $logger = new \common\extensions\ReportChangesHistory\classes\Logger();
                            $beforeObject = new \common\api\Classes\Product();
                            $beforeObject->load($product_id);
                            $logger->setBeforeObject($beforeObject);
                            unset($beforeObject);
                        }

                        tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "' and categories_id = '" . (int) $current_categories_id . "'");
                        $product_categories_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "'");
                        $product_categories = tep_db_fetch_array($product_categories_query);
                        if ($product_categories['total'] == '0') {
                            \common\helpers\Product::remove_product($product_id);
                            if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                               $ext::deleteProductLinks($product_id);
                            }
                        }
                        if (defined('USE_CACHE') && USE_CACHE == 'true') {
                            \common\helpers\System::reset_cache_block('categories');
                            \common\helpers\System::reset_cache_block('also_purchased');
                        }

                        if (isset($logger) && \common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
                            $afterObject = new \common\api\Classes\Product();
                            $afterObject->load(0);
                            $logger->setAfterObject($afterObject);
                            unset($afterObject);
                            $logger->run();
                        }
                    }
                } elseif( $what=='c' && $id > 0) {
                    if (\common\helpers\Acl::rule(['TEXT_CATEGORIES', 'IMAGE_DELETE'])) {
                        $categories_id = $id;

                        if (\common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')) {
                            $logger = new \common\extensions\ReportChangesHistory\classes\Logger();
                            $beforeObject = new \common\api\Classes\Category();
                            $beforeObject->load($categories_id);
                            $logger->setBeforeObject($beforeObject);
                            unset($beforeObject);
                        }

                        $categories = \common\helpers\Categories::get_category_tree($categories_id, '', '0', '', true);
                        $products = array();
                        $products_delete = array();

                        for ($i = 0, $n = sizeof($categories); $i < $n; $i++) {
                            $product_ids_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int) $categories[$i]['id'] . "'");

                            while ($product_ids = tep_db_fetch_array($product_ids_query)) {
                                $products[$product_ids['products_id']]['categories'][] = $categories[$i]['id'];
                            }
                        }

                        foreach ($products as $key => $value) {
                            $category_ids = '';

                            for ($i = 0, $n = sizeof($value['categories']); $i < $n; $i++) {
                                $category_ids .= "'" . (int) $value['categories'][$i] . "', ";
                            }
                            $category_ids = substr($category_ids, 0, -2);

                            $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $key . "' and categories_id not in (" . $category_ids . ")");
                            $check = tep_db_fetch_array($check_query);
                            if ($check['total'] < '1') {
                                $products_delete[$key] = $key;
                            }
                        }

                        set_time_limit(0);
                        $sdn = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed');
                        for ($i = 0, $n = sizeof($categories); $i < $n; $i++) {
                            \common\helpers\Categories::remove_category($categories[$i]['id'], false);
                            if ($sdn) {
                                $sdn::deleteCategoryLinks($categories[$i]['id']);
                            }
                        }

                        foreach ($products_delete as $key) {
                            \common\helpers\Product::remove_product($key);
                            if ($sdn) {
                                $sdn::deleteProductLinks($key);
                            }
                        }


                        if (USE_CACHE == 'true') {
                            \common\helpers\System::reset_cache_block('categories');
                            \common\helpers\System::reset_cache_block('also_purchased');
                        }
                        //It's not required as branch is deleted completely. Left, right are not concequent, but correct. It's very slow operation.
                        //\common\helpers\Categories::update_categories();
                    }
                }
            }
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'status' => 'ok',
        ];
    }

    public function actionSwitchStatusBatch()
    {
        $this->layout = false;
        $status = Yii::$app->request->post('state',0);
        $status = ( $status )?'true':'false';
        $items =  Yii::$app->request->post('batch',[]);
        if ( is_array($items) && count($items)>0 ) {
            foreach ($items as $item) {
                list($what, $id) = explode('_',$item,2);
                if ( $what=='p' ) {
                    \common\helpers\Product::set_status((int) $id, ($status == 'true' ? 1 : 0));
                }elseif( $what=='c' ) {
                    \common\helpers\Categories::set_categories_status((int) $id, ($status == 'true' ? 1 : 0));
                }
            }
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'status' => 'ok',
        ];
    }

    public function actionSwitchStatus() {
        $type = Yii::$app->request->post('type');
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        switch ($type) {
            case 'products_status':
                \common\helpers\Product::set_status((int) $id, ($status == 'true' ? 1 : 0));
                break;
            case 'categories_status':
                \common\helpers\Categories::set_categories_status((int) $id, ($status == 'true' ? 1 : 0));
                break;
            default:
                break;
        }
        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
    }

    private function changeCategoryTree($categories = [], $parent_id = 0) {
        if (is_array($categories)) {
            foreach ($categories as $sortOrder => $category) {
                if (isset($category['id'])) {
                    tep_db_query("update " . TABLE_CATEGORIES . " set sort_order = '" . (int) $sortOrder . "', parent_id = '" . (int) $parent_id . "' where categories_id = '" . (int) $category['id'] . "'");
                    if (isset($category['children'])) {
                        $this->changeCategoryTree($category['children'], $category['id']);
                    }
                }
            }
        }
    }

    /*
     * sort sub-categories, brands (manufacturers), products, re-arrange category tree.
     */
    public function actionSortOrder() {
        global $login_id;
        $languages_id = \Yii::$app->settings->get('languages_id');
        $this->layout = false;
        //sort brands (manufacturers)
        if (isset($_POST['brands'])) {
            $brands = Yii::$app->request->post('brands');
            foreach ($brands as $key => $value) {
                tep_db_query("update " . TABLE_MANUFACTURERS . " set sort_order = '" . $key . "' where manufacturers_id = '" . (int) $value . "'");
            }
        }
        //re-arrange category tree
        if (isset($_POST['categories'])) {
            $categories = Yii::$app->request->post('categories');
            $categories = stripslashes($categories);
            $categories = json_decode($categories, true);
            $this->changeCategoryTree($categories);
            if (USE_CACHE == 'true') {
                \common\helpers\System::reset_cache_block('categories');
                \common\helpers\System::reset_cache_block('also_purchased');
            }
            \common\helpers\Categories::update_categories();
        }
        if (isset($_GET['listing_type']) && $_GET['listing_type'] == 'category') {
          //sort sub-categories
            $parent_id = (int)Yii::$app->request->get('category_id', 0);
            $categories = Yii::$app->request->post('category');
            if (is_array($categories)) {

                $orderByCategory = "c.sort_order, cd.categories_name";
                $search_condition = " where 1 ";
                $search_condition .= " and c.parent_id='" . $parent_id . "'";
                $categories_query_raw = "select distinct(c.categories_id), cd.categories_name, c.categories_status from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id=cd.categories_id " . $search_condition . " and cd.language_id = '" . (int) $languages_id . "' and cd.affiliate_id = 0 " . " order by " . $orderByCategory;
                $categories_query = tep_db_query($categories_query_raw);
                $sortOrder = 0;
                $offsets = array_flip($categories);
                $gridOffset = 0;
                while ($category = tep_db_fetch_array($categories_query)) {
                    $categoryId = $category['categories_id'];
                    if (isset($offsets[$categoryId])) {
                        tep_db_query("update " . TABLE_CATEGORIES . " set sort_order = '" . (int) ($sortOrder + $offsets[$categoryId]) . "' where parent_id = '" . $parent_id . "'  and categories_id = '" . (int) $categoryId . "'");
                        $gridOffset++;
                    } else {
                        $sortOrder += $gridOffset;
                        $gridOffset = 0;
                        tep_db_query("update " . TABLE_CATEGORIES . " set sort_order = '" . (int) $sortOrder . "' where parent_id = '" . $parent_id . "'  and categories_id = '" . (int) $categoryId . "'");
                        $sortOrder++;
                    }
                }
                /// its impossible to link category to another branch now.
                /// update all tree if it's changed.
                \common\helpers\Categories::update_categories($parent_id);

            }
            //sort products within category
            $products = Yii::$app->request->post('product');
            if (is_array($products)) {

                $orderByProduct = "p2c.sort_order, pd.products_name";
                $products_query_raw = "select p.products_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and p.products_id = p2c.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and p2c.categories_id = '" . (int) $parent_id . "' order by " . $orderByProduct;
                $products_query = tep_db_query($products_query_raw);
                $sortOrder = 0;
                $offsets = array_flip($products);
                $gridOffset = 0;
                while ($product = tep_db_fetch_array($products_query)) {
                    $productId = $product['products_id'];
                    if (isset($offsets[$productId])) {
                        tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set sort_order = '" . (int) ($sortOrder + $offsets[$productId]) . "' where categories_id = '" . (int) $parent_id . "'  and products_id = '" . (int) $productId . "'");
                        $gridOffset++;
                    } else {
                        $sortOrder += $gridOffset;
                        $gridOffset = 0;
                        tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set sort_order = '" . (int) $sortOrder . "' where categories_id = '" . (int) $parent_id . "'  and products_id = '" . (int) $productId . "'");
                        $sortOrder++;
                    }
                }

            }

            $this->view->categoriesTree = $this->getCategoryTree();
            if ($parent_id>0) {
              $this->view->categoriesOpenedTree = \common\helpers\Categories::getCategoryParentsIds($parent_id);
            } else {
              $this->view->categoriesOpenedTree = [];
            }
            $this->view->categoriesClosedTree = array_diff(array_map('intval', explode('|', \Yii::$app->session->get('closed_data'))), $this->view->categoriesOpenedTree);
            $collapsed = $this->defaultCollapsed;

            return $this->render('cat_main_box', ['directOutput' => true, 'collapsed' => $collapsed]);
        }
        if (isset($_GET['listing_type']) && $_GET['listing_type'] == 'brand') {
          //sort products within brand
            $brandId = Yii::$app->request->get('brand_id');
            $products = Yii::$app->request->post('product');
            if (is_array($products)) {
                $ff = '';
                $order = "p.sort_order, pd.products_name";
                $products_query_raw = "select p.products_id from " . TABLE_PRODUCTS . " p " . (intval($brandId) == -1 ? " left join " . TABLE_MANUFACTURERS . " m ON m.manufacturers_id=p.manufacturers_id " : '') . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on (p.products_id = pd.products_id and pd.language_id='" . intval($languages_id) . "') where pd.platform_id = '".intval(\common\classes\platform::defaultId())."' " . (intval($brandId) > 0 ? " and manufacturers_id = '" . intval($brandId) . "' " : (intval($brandId) == -1 ? ' and m.manufacturers_id IS NULL' : '')) . $ff . " group by p.products_id ORDER BY " . $order;

                $products_query = tep_db_query($products_query_raw);
                $sortOrder = 0;
                $offsets = array_flip($products);
                $gridOffset = 0;
                while ($product = tep_db_fetch_array($products_query)) {
                    $productId = $product['products_id'];
                    if (isset($offsets[$productId])) {
                        tep_db_query("update " . TABLE_PRODUCTS . " set sort_order = '" . (int) ($sortOrder + $offsets[$productId]) . "' where products_id = '" . (int) $productId . "'");
                        $gridOffset++;
                    } else {
                        $sortOrder += $gridOffset;
                        $gridOffset = 0;
                        tep_db_query("update " . TABLE_PRODUCTS . " set sort_order = '" . (int) $sortOrder . "' where products_id = '" . (int) $productId . "'");
                        $sortOrder++;
                    }
                }
            }
        }
    }

    public function actionCopyMove() {
        $this->layout = false;
        $type = Yii::$app->request->post('type');
        $catUpdated = false;
        switch ($type) {
            case 'mixed':
                $items =  Yii::$app->request->post('batch',[]);
                if ( is_array($items) && count($items)>0 ) {
                    $current_category_id = Yii::$app->request->post('current_category_id');//где мы
                    $categories_id = Yii::$app->request->post('categories_id');//куда
                    $copy_to = Yii::$app->request->post('copy_to');
                    $copy_attributes = Yii::$app->request->post('copy_attributes');
                    foreach ($items as $item) {
                        list($what, $id) = explode('_',$item,2);
                        if ( $what=='p' ) {
                            switch ($copy_to) {
                                case 'move':

                                    $p2c = \common\models\Products2Categories::findOne(['products_id' => (int) $id, 'categories_id' => (int) $categories_id ]);
                                    if ($current_category_id>0 && !$p2c && $current_category_id!=(int)$categories_id) {
                                        tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = '" . (int) $categories_id . "' where products_id = '" . (int) $id . "' and categories_id = '" . (int) $current_category_id . "'");
                                    } else {
                                      // extra link from search
                                      try {
                                        $p2c = new \common\models\Products2Categories();
                                        $p2c->categories_id = (int) $categories_id;
                                        $p2c->products_id = (int) $id;
                                        $p2c->sort_order = 0;
                                        $p2c->save();
                                      } catch (\Exception $e) {
                                       \Yii::warning($e->getMessage());
                                      }

                                    }

                                    break;
                                case 'link':
                                    $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $id . "' and categories_id = '" . (int) $categories_id . "'");
                                    $check = tep_db_fetch_array($check_query);
                                    if ($check['total'] < '1') {
                                        tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $id . "', '" . (int) $categories_id . "')");
                                    }
                                    break;
                                case 'dublicate':
                                    \common\helpers\Product::duplicate($id, $categories_id, $copy_attributes);
                                    break;
                            }
                        } elseif ( $what=='c' ) {

                          if ($id != $categories_id) {
                            $cat = \common\models\Categories::findOne((int)$id);
                            if ($categories_id>0) {
                              $catTo = \common\models\Categories::findOne((int)$categories_id);
                            }
                            if ($cat && ($categories_id==0 || $catTo)) {
                              $cat->parent_id = $categories_id;
                              try {
                                if ($categories_id>0) {
                                  $cat->appendTo($catTo);
                                }
                                $cat->save();
                                $catUpdated = true;
                              } catch (\Exception $ex) {
                                \Yii::warning("$id => $categories_id " . $ex->getMessage());
                              }
                            }
                          }
                        }
                    }
                    if ($catUpdated) {
                      \common\helpers\Categories::update_categories(0);
                    }
                    if (USE_CACHE == 'true') {
                        \common\helpers\System::reset_cache_block('categories');
                        \common\helpers\System::reset_cache_block('also_purchased');
                    }
                    $this->view->categoriesTree = $this->getCategoryTree();
                    $this->view->categoriesOpenedTree = [];
                    $this->view->categoriesClosedTree = array_diff(array_map('intval', explode('|', \Yii::$app->session->get('closed_data'))), $this->view->categoriesOpenedTree);
                    return $this->render('cat_main_box', ['directOutput' => true, 'collapsed' => $this->defaultCollapsed]);
                }
                break;
            case 'product':
                $copy_to = Yii::$app->request->post('copy_to');
                $products_id = Yii::$app->request->post('products_id');
                $categories_id = Yii::$app->request->post('categories_id');
                switch ($copy_to) {
                    case 'move':
                        $current_category_id = Yii::$app->request->post('current_category_id');
                        if (!\common\models\Products2Categories::findOne(['products_id' => (int) $products_id, 'categories_id' => (int) $categories_id ])) {
                            if ('0'===$current_category_id /*Top*/ || $current_category_id>0) {
                                tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = '" . (int) $categories_id . "' where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $current_category_id . "'");
                            } else {
                              // extra link from search
                              try {
                                $p2c = new \common\models\Products2Categories();
                                $p2c->categories_id = (int) $categories_id;
                                $p2c->products_id = (int) $products_id;
                                $p2c->sort_order = 0;
                                $p2c->save();
                              } catch (\Exception $e) {
                               \Yii::warning($e->getMessage());
                              }

                            }
                        }


                        break;
                    case 'link':
                        $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $categories_id . "'");
                        $check = tep_db_fetch_array($check_query);
                        if ($check['total'] < '1') {
                            tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $products_id . "', '" . (int) $categories_id . "')");
                        }
                        break;
                    case 'dublicate':
                        $copy_attributes = Yii::$app->request->post('copy_attributes');
                        \common\helpers\Product::duplicate($products_id, $categories_id, $copy_attributes);
                        break;
                }
                if (USE_CACHE == 'true') {
                    \common\helpers\System::reset_cache_block('categories');
                    \common\helpers\System::reset_cache_block('also_purchased');
                }
                break;
            case 'category':
                $categories_id = Yii::$app->request->post('categories_id');
                $parent_id = Yii::$app->request->post('parent_id');
                if ($categories_id != $parent_id) {
                //    tep_db_query("update " . TABLE_CATEGORIES . " set parent_id = '" . (int) $parent_id . "' where categories_id = '" . (int) $categories_id . "'");
                  $cat = \common\models\Categories::findOne((int)$categories_id);
                  if ($parent_id>0) {
                    $catTo = \common\models\Categories::findOne((int)$parent_id);
                  }
                  if ($cat && ($parent_id==0 || $catTo)) {
                    $cat->parent_id = $parent_id;
                    try {
                      if ($parent_id>0) {
                        $cat->appendTo($catTo);
                      }
                      $cat->save();
                    } catch (\Exception $ex) {
                      \Yii::warning("$categories_id => $parent_id " . $ex->getMessage());
                    }
                  }
                }
                if ($parent_id==0) {
                  \common\helpers\Categories::update_categories(0);
                }
                $this->view->categoriesTree = $this->getCategoryTree();

                if ($categories_id>0) {
                  $this->view->categoriesOpenedTree = \common\helpers\Categories::getCategoryParentsIds($categories_id);
                } else {
                  $this->view->categoriesOpenedTree = [];
                }
                $this->view->categoriesClosedTree = array_diff(array_map('intval', explode('|', \Yii::$app->session->get('closed_data'))), $this->view->categoriesOpenedTree);

                $collapsed = $this->defaultCollapsed;
                return $this->render('cat_main_box', ['directOutput' => true, 'collapsed' => $collapsed]);
                break;
            case 'brand':
                // products_id brand_id
                $brandId = Yii::$app->request->post('brand_id');
                $productId = Yii::$app->request->post('products_id');
                if ($brandId >= 0) {
                    tep_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = '" . (int) $brandId . "' where products_id 	 = '" . (int) $productId . "'");
                } else {
                    tep_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = NULL where products_id 	 = '" . (int) $productId . "'");
                }
                break;
            default:
                break;
        }
    }

    /**
     * Autocomplette
     */
    public function actionBrands() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));
        $with = Yii::$app->request->get('with','');

        $search = "1";
        if (!empty($term)) {
            $search = "manufacturers_name like '%" . tep_db_input($term) . "%'";
        }

        $brands = [];

        if ( !empty($with) ) {
            $brands_query = tep_db_query("select manufacturers_id as id, manufacturers_name as `value` from " . TABLE_MANUFACTURERS . " where " . $search . " order by manufacturers_name");
            while ($response = tep_db_fetch_array($brands_query)) {
                $response['text'] = $response['value'];
                $brands[] = $response;
            }
        }else{
            $brands_query = tep_db_query("select manufacturers_name  from " . TABLE_MANUFACTURERS . " where " . $search . " group by manufacturers_name order by manufacturers_name");
            while ($response = tep_db_fetch_array($brands_query)) {
                $brands[] = $response['manufacturers_name'];
            }
        }

        echo json_encode($brands);
    }

    public function actionSuppliers() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));

        $search = "1";
        if (!empty($term)) {
            $search = "suppliers_name like '%" . tep_db_input($term) . "%'";
        }

        $suppliers = [];
        $suppliers_query = tep_db_query("select suppliers_name  from " . TABLE_SUPPLIERS . " where " . $search . " group by suppliers_name order by suppliers_name");
        while ($response = tep_db_fetch_array($suppliers_query)) {
            $suppliers[] = $response['suppliers_name'];
        }
        echo json_encode($suppliers);
    }

    public function actionBrandedit() {
        if (false === \common\helpers\Acl::rule(['TEXT_LABEL_BRAND', 'IMAGE_EDIT'])) {
            $this->redirect(\yii\helpers\Url::toRoute('categories/'));
        }
        $languages_id = \Yii::$app->settings->get('languages_id');

        $this->view->usePopupMode = false;
        if (Yii::$app->request->isAjax) {
            $this->layout = false;
            $this->view->usePopupMode = true;
        }

        \common\helpers\Translation::init('admin/categories');

        $popup = 0;
        if (Yii::$app->request->isPost) {
            $manufacturers_id = (int) Yii::$app->request->getBodyParam('manufacturers_id');
            $popup = (int) Yii::$app->request->post('popup');
            if ($popup == 0) {
                $this->view->usePopupMode = false;
            }
        } else {
            $manufacturers_id = (int) Yii::$app->request->get('manufacturers_id');
        }
        $this->view->contentAlreadyLoaded = $popup;

        $manufacturers = [];

        if ($manufacturers_id > 0) {
            $manufacturers_query_raw = "select * from " . TABLE_MANUFACTURERS . "  where manufacturers_id = '" . $manufacturers_id . "'";
            $manufacturers_query = tep_db_query($manufacturers_query_raw);
            $manufacturers = tep_db_fetch_array($manufacturers_query);
        }

        \common\helpers\Php8::nullArrProps($manufacturers, ['manufacturers_id', 'maps_id', 'manufacturers_name', 'manufacturers_image', 'manufacturers_image_2', 'stock_limit', 'mapsId', 'mapsImage', 'mapsTitle', 'brand_id']);

        /**
         * @var $imageMaps \common\extensions\ImageMaps\models\ImageMaps
         */
        if ($imageMaps = \common\helpers\Extensions::getModel('ImageMaps', 'ImageMaps')) {
            if ($manufacturers['maps_id'] && !empty($imageMaps)) {
              if ($map = $imageMaps::findOne($manufacturers['maps_id'])) {
                $manufacturers['mapsId'] = $manufacturers['maps_id'];
                $manufacturers['mapsImage'] = $map->image;
                $manufacturers['mapsTitle'] = $map->getTitle($languages_id);
              }
            }
        }

        $mInfo = new \objectInfo($manufacturers);

        if ($mInfo->manufacturers_image) {
            $image_path = DIR_WS_CATALOG_IMAGES . $mInfo->manufacturers_image;
        }
        if ($mInfo->manufacturers_image_2) {
            $image_path = DIR_WS_CATALOG_IMAGES . $mInfo->manufacturers_image_2;
        }

        $mDescription = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            $mDescription[$i]['code'] = $languages[$i]['code'];
            $mDescription[$i]['languageId'] = $languages[$i]['id'];
            $mDescription[$i]['manufacturers_url'] = tep_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturer_url($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_seo_name'] = tep_draw_input_field('manufacturers_seo_name[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturer_seo_name($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_meta_description'] = tep_draw_textarea_field('manufacturers_meta_description[' . $languages[$i]['id'] . ']', 'soft', '25', '7', \common\helpers\Manufacturers::get_manufacturer_meta_descr($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
//            $mDescription[$i]['manufacturers_description'] = tep_draw_textarea_field('manufacturers_description[' . $languages[$i]['id'] . ']', 'soft', '25', '7', \common\helpers\Manufacturers::getManufacturerDescription($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control ckeditor text-dox-02" id="txt_brand_description_'.$languages[$i]['id'].'"');
            $mDescription[$i]['manufacturers_description'] = \common\helpers\Html::textarea('manufacturers_description[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::getManufacturerDescription($mInfo->manufacturers_id, $languages[$i]['id']),
                ['wrap' => 'soft', 'cols' => '25', 'rows' => '7', 'class' => 'form-control ckeditor text-dox-02', 'id' => 'txt_brand_description_'.$languages[$i]['id'] ]);
            $mDescription[$i]['manufacturers_meta_key'] = tep_draw_textarea_field('manufacturers_meta_key[' . $languages[$i]['id'] . ']', 'soft', '25', '7', \common\helpers\Manufacturers::get_manufacturer_meta_key($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_meta_title'] = tep_draw_input_field('manufacturers_meta_title[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturer_meta_title($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_h1_tag'] = tep_draw_input_field('manufacturers_h1_tag[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturers_h1_tag($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_h2_tag'] = \common\helpers\Manufacturers::get_manufacturers_h2_tag($mInfo->manufacturers_id, $languages[$i]['id']); //tep_draw_input_field('manufacturers_h2_tag[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturers_h2_tag($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_h3_tag'] = \common\helpers\Manufacturers::get_manufacturers_h3_tag($mInfo->manufacturers_id, $languages[$i]['id']); //tep_draw_input_field('manufacturers_h3_tag[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturers_h3_tag($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
        }

        // {{ suppliers
        $supplierRules = new \backend\models\SuppliersRules();
        if ( $manufacturers_id ) {
            $brandObj = \common\models\Manufacturers::findOne(['manufacturers_id' => $manufacturers_id]);
        }else{
            $brandObj = new \common\models\Manufacturers();
        }
        $supplierRules->getManufacturerData($brandObj, $mInfo);
        // }} suppliers

        $this->selectedMenu = array('catalog', 'categories');
        $text_new_or_edit = ($manufacturers_id == 0) ? TEXT_INFO_HEADING_NEW_BRAND : TEXT_INFO_HEADING_EDIT_BRAND;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('categories/index'), 'title' => $text_new_or_edit);

        if ((int)$mInfo->stock_limit < 0) {
            $mInfo->stock_limit = (int)ADDITIONAL_STOCK_LIMIT;
        } else {
            $mInfo->stock_limit_on = true;
        }

        return $this->render('brandedit', [
                'manufacturers_id' => $manufacturers_id,
            'mInfo' => $mInfo,
            'languages' => $languages,
            'mDescription' => $mDescription
        ]);
    }

    function actionBrandSubmit() {

        \common\helpers\Translation::init('admin/manufacturers');

        $this->layout = FALSE;
        $error = FALSE;
        $message = '';
        $script = '';

        $messageType = 'success';

        $popup = (int) Yii::$app->request->post('popup');
        $manufacturers_id = (int) Yii::$app->request->post('manufacturers_id');
        $maps_id = (int) Yii::$app->request->post('maps_id', 0);
        $manufacturers_name = tep_db_prepare_input(Yii::$app->request->post('manufacturers_name'));
        $manufacturers_url = Yii::$app->request->post('manufacturers_url');
        $manufacturers_old_seo_page_name = Yii::$app->request->post('manufacturers_old_seo_page_name');
        $manufacturers_meta_title = Yii::$app->request->post('manufacturers_meta_title');
        $manufacturers_meta_description = Yii::$app->request->post('manufacturers_meta_description');
        $manufacturers_description = Yii::$app->request->post('manufacturers_description');
        $manufacturers_meta_key = Yii::$app->request->post('manufacturers_meta_key');
        $manufacturers_h1_tag = Yii::$app->request->post('manufacturers_h1_tag');
        $manufacturers_h2_tag = Yii::$app->request->post('manufacturers_h2_tag');
        $manufacturers_h3_tag = Yii::$app->request->post('manufacturers_h3_tag');
        $manufacturers_seo_name = Yii::$app->request->post('manufacturers_seo_name');

        $sql_data_array = array('manufacturers_name' => $manufacturers_name);
        // Moved to SeoRedirectsNamed
        // $sql_data_array['manufacturers_old_seo_page_name'] = $manufacturers_old_seo_page_name;
        $sql_data_array['maps_id'] = $maps_id;

        $sql_data_array['stock_limit'] = (int)Yii::$app->request->post('stock_limit', -1);

        $action = '';
        if ($error === FALSE) {
            if ($manufacturers_id > 0) {
                // Update
                $action = 'update';
                $update_sql_data = array('last_modified' => 'now()');

                $sql_data_array = array_merge($sql_data_array, $update_sql_data);

                tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int) $manufacturers_id . "'");

                $message = TEXT_INFO_UPDATED;
            } else {
                // Insert
                $action = 'insert';
                $insert_sql_data = array('date_added' => 'now()');

                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
                $manufacturers_id = (int) tep_db_insert_id();
                Yii::$app->request->setBodyParams(array_merge(Yii::$app->request->getBodyParams(), ['manufacturers_id' => $manufacturers_id, 'popup' => $popup]));

                if ($manufacturers_id > 0) {
                    $script = '
                     <script type="text/javascript">
                        setTimeout(function(data){
                            $("form[name=save_manufacturer_form] input[name=manufacturers_id]").val(' . $manufacturers_id . ');
                        }, 500);
                     </script>
                    ';
                }

                $message = TEXT_INFO_SAVED;
            }
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                $ext::saveBrandLinks($manufacturers_id, $_POST);
            }
        }

        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $manufacturers_url_array = $manufacturers_url;
            $language_id = $languages[$i]['id'];

            if (!tep_not_null($manufacturers_seo_name[$language_id])) {
                $manufacturers_seo_name[$language_id] = Seo::makeSlug($manufacturers_name);
            }

            $check_seo = tep_db_fetch_array(tep_db_query("SELECT count(*) AS c FROM ".TABLE_MANUFACTURERS_INFO." WHERE manufacturers_id != '" . (int) $manufacturers_id . "' AND manufacturers_seo_name='".tep_db_input($manufacturers_seo_name[$language_id])."'"));
            if ( $check_seo['c']>0 ) {
                $manufacturers_seo_name[$language_id] = trim($manufacturers_seo_name[$language_id].'-'.$manufacturers_id,'-');
            }

            $sql_data_array = array('manufacturers_url' => tep_db_prepare_input($manufacturers_url_array[$language_id]),
                'manufacturers_meta_description' => tep_db_prepare_input($manufacturers_meta_description[$language_id]),
                'manufacturers_description' => tep_db_prepare_input($manufacturers_description[$language_id]),
                'manufacturers_meta_key' => tep_db_prepare_input($manufacturers_meta_key[$language_id]),
                'manufacturers_meta_title' => tep_db_prepare_input($manufacturers_meta_title[$language_id]),
                'manufacturers_h1_tag' => tep_db_prepare_input($manufacturers_h1_tag[$language_id]),
                'manufacturers_h2_tag' => tep_db_prepare_input(is_array($manufacturers_h2_tag[$language_id]) ? implode("\n", $manufacturers_h2_tag[$language_id]) : $manufacturers_h2_tag[$language_id]),
                'manufacturers_h3_tag' => tep_db_prepare_input(is_array($manufacturers_h3_tag[$language_id]) ? implode("\n", $manufacturers_h3_tag[$language_id]) : $manufacturers_h3_tag[$language_id]),
                'manufacturers_seo_name' => tep_db_prepare_input($manufacturers_seo_name[$language_id]));

            $_check_info_exists_r = tep_db_query(
                    "SELECT * ".
                    "FROM ".TABLE_MANUFACTURERS_INFO." ".
                    "WHERE manufacturers_id = '" . (int) $manufacturers_id . "' and languages_id = '" . (int) $language_id . "'"
            );

            if (tep_db_num_rows($_check_info_exists_r)==0) {
                $insert_sql_data = array(
                    'manufacturers_id' => $manufacturers_id,
                    'languages_id' => $language_id,
                );

                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array);
            } else {
                $_info_exist = tep_db_fetch_array($_check_info_exists_r);
                tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array, 'update', "manufacturers_id = '" . (int) $manufacturers_id . "' and languages_id = '" . (int) $language_id . "'");

                if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
                    $ext::trackBrandLinks($manufacturers_id, $language_id, null, $sql_data_array, $_info_exist);
                }
            }
        }

        Manufacturers::saveImage($manufacturers_id, '', 'gallery');
        Manufacturers::saveImage($manufacturers_id, '_2', 'hero');


        foreach (\common\helpers\Hooks::getList('categories/brandedit') as $filename) {
            include($filename);
        }

        $supplierRules = new \backend\models\SuppliersRules();
        $manufacturerObject = \common\models\Manufacturers::findOne($manufacturers_id);
        $supplierRules->saveManufacturersData($manufacturerObject, Yii::$app->request->post('suppliers_data',[]));

        if (defined('USE_CACHE') && USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('manufacturers');
        }

        if ($error === TRUE) {
            $messageType = 'warning';

            if ($message == '')
                $message = WARN_UNKNOWN_ERROR;
        }

        if ($popup == 1) {
            $this->view->brandsList = $this->getBrandsList();
            return $this->render('brand_box');
        }
        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
        <?= $message ?>
        <?= $script ?>
                    </div>
                </div>
                <div class="noti-btn">
                    <div></div>
                    <div><a href="javascript:void(0)" class="btn btn-primary" onClick="return backStatement();"><?php echo TEXT_BTN_OK; ?></a></div>
                </div>
            </div>
            <script>
                $('body').scrollTop(0);
                /* $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                 $(this).parents('.pop-mess').remove();
                 }); */
            </script>
        </div>

        <?php
        return $this->actionBrandedit();
    }

    public function actionConfirmManufacturerDelete() {
        \common\helpers\Translation::init('admin/manufacturers');
        \common\helpers\Translation::init('admin/faqdesk');

        $this->layout = FALSE;

        $manufacturers_id = Yii::$app->request->get('manufacturers_id');

        $message = '';

        $manufacturers_query_raw = "select manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified from " . TABLE_MANUFACTURERS . " where  manufacturers_id = '$manufacturers_id' ";
        $manufacturers_query = tep_db_query($manufacturers_query_raw);
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $manufacturer_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int) $manufacturers['manufacturers_id'] . "'");
            $manufacturer_products = tep_db_fetch_array($manufacturer_products_query);
            $mInfo_array = array_merge($manufacturers, $manufacturer_products);
            $mInfo = new \objectInfo($mInfo_array);
        }

        if ($mInfo->products_count > 0) {
            $messageType = 'warning';
            $message = sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count);
            ?>
            <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
            <?= $message ?>
                        </div>
                    </div>
                    <div class="noti-btn">
                        <div></div>
                        <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                    </div>
                </div>
                <script>
                    $('body').scrollTop(0);
                    $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                        $(this).parents('.pop-mess').remove();
                    });
                </script>
            </div>

            <?php
        }

        echo '<div class="brand_pad">';
        echo tep_draw_form('manufacturer_delete', FILENAME_MANUFACTURERS, \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="manufacturer_delete" onSubmit="return deleteManufacturer();"');
        echo '<div class="or_box_head">' . TEXT_HEADING_DELETE_MANUFACTURER . '</div>';
        echo '<div class="col_desc">' . TEXT_DELETE_MANUFACTURER . ' <b>' . $mInfo->manufacturers_name . '</b></div>';
        //echo '<div class="check_linear">' . tep_draw_checkbox_field('delete_image', '', TRUE) . ' <span>' . TEXT_DELETE_IMAGE . '</span></div>';
        ?>
        <div class="btn-toolbar btn-toolbar-order">
        <?php
        echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button>';
        echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return closePopup()">';

        echo tep_draw_hidden_field('manufacturers_id', $manufacturers_id);
        ?>
        </div>
        </form>
        </div>
        <?php
    }

    //manufacturer-delete
    public function actionManufacturerDelete() {
        \common\helpers\Translation::init('admin/manufacturers');
        $this->layout = FALSE;

        $manufacturers_id = (int) Yii::$app->request->post('manufacturers_id');
        $delete_products = Yii::$app->request->post('delete_products');

        $brandFolder = DIR_FS_CATALOG_IMAGES . 'brands' . DIRECTORY_SEPARATOR . $manufacturers_id;
        \yii\helpers\FileHelper::removeDirectory($brandFolder);

        tep_db_query("delete from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int) $manufacturers_id . "'");
        tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturers_id . "'");

        if ( (int)$manufacturers_id>0 ) {
            tep_db_query("delete from " . TABLE_FILTERS . " where manufacturers_id = '" . (int)$manufacturers_id . "'");
        }

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')){
            $ext::deleteBrandLinks($manufacturers_id);
        }

        if (isset($delete_products) && ( $delete_products == 'on' )) {
            $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int) $manufacturers_id . "'");
            while ($products = tep_db_fetch_array($products_query)) {
                \common\helpers\Product::remove_product($products['products_id']);
            }
        } else {
            tep_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = '' where manufacturers_id = '" . (int) $manufacturers_id . "'");
        }

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('manufacturers');
        }

        $this->view->brandsList = $this->getBrandsList();
        return $this->render('brand_box');
    }

    public function actionTemporaryUpload() {
        $path = \Yii::getAlias('@webroot');
        $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        $filename = '';
        $status = 0;

        if (isset($_FILES['filedrop_files']['name'])) {
            if ((int) $_FILES['filedrop_files']['error'] === 0) {

                $tmp_name = $_FILES['filedrop_files']['tmp_name'];

                //$image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES;
                $new_name = $path . $_FILES['filedrop_files']['name'];
                copy($tmp_name, $new_name);
                $filename = $_FILES['filedrop_files']['name'];
                $status = 1;
            }
        }

        $response = array('status' => $status, 'filename' => $filename);
        echo json_encode($response);
    }

    public function actionSupplierSelect() {

        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/suppliers');

        $this->layout = false;

        $mode = Yii::$app->request->get('mode','product');
        $except = Yii::$app->request->get('except','');
        if ( !empty($except) ) {
            $except = preg_split(',',$except,-1,PREG_SPLIT_NO_EMPTY);
        }else{
            $except = array();
        }

        $this->view->suppliers = ['0' => TEXT_NEW_SUPPLIER];
        $this->view->suppliers_js = "arSurcharge = []; arMargin = [];\n";
        $suppliers = \common\helpers\Suppliers::getSuppliers();
        if ($suppliers){
            foreach($suppliers as $supplier){
                if ( in_array($supplier->suppliers_id,$except) ) continue;
                $this->view->suppliers[$supplier->suppliers_id] = $supplier->suppliers_name;
                $this->view->suppliers_js .= "arSurcharge[$supplier->suppliers_id] = '$supplier->suppliers_surcharge_amount'; arMargin[$supplier->suppliers_id] = '$supplier->suppliers_margin_percentage';\n";
            }
        }

        return $this->render(
                'supplierselect', [
                        'endpointUrl' => Yii::$app->urlManager->createUrl(['categories/supplier-add','mode'=>$mode]),
                        'mode' => $mode,
                        'uprid' => \Yii::$app->request->get('uprid'),
                    ]
        );
    }

    public function actionCalculateSupplierPrice()
    {
        $this->layout = false;

        $data = Yii::$app->request->post('queue');
        foreach ( $data as $_supplierId=>$calculateData ) {
            $params = [
                'products_id' => isset($calculateData['products_id'])?intval($calculateData['products_id']):0,
                'categories_id' => (isset($calculateData['categories_id']) && is_array($calculateData['categories_id']))?array_map('intval',$calculateData['categories_id']):[],
                'manufacturers_id' => isset($calculateData['manufacturers_id'])?intval($calculateData['manufacturers_id']):0,
                'currencies_id' => isset($calculateData['currencies_id'])?intval($calculateData['currencies_id']):0,
                'PRICE' => isset($calculateData['PRICE'])?floatval($calculateData['PRICE']):0,
                'MARGIN' => !empty($calculateData['MARGIN'])?$calculateData['MARGIN']:null,
                'SURCHARGE' => !empty($calculateData['SURCHARGE'])?$calculateData['SURCHARGE']:null,
                'DISCOUNT' => !empty($calculateData['DISCOUNT'])?$calculateData['DISCOUNT']:null,
                'tax_rate' => isset($calculateData['tax_rate'])?$calculateData['tax_rate']:null,
                'price_with_tax' => isset($calculateData['price_with_tax'])?$calculateData['price_with_tax']:null,
            ];

            if ( $params['PRICE']>=0 ) {
                $data[$_supplierId]['result'] = \common\helpers\PriceFormula::applyRules($params, $_supplierId);
                $data[$_supplierId]['result']['SUPPLIER_COST'] = \common\helpers\PriceFormula::correctSupplierValueByCurrencyRisks($_supplierId, $data[$_supplierId]['currencies_id'], $data[$_supplierId]['PRICE']??0);
                $data[$_supplierId]['result']['LANDED_PRICE'] = \common\helpers\PriceFormula::correctSupplierValueByCurrencyRisks($_supplierId, $data[$_supplierId]['currencies_id'], $data[$_supplierId]['LANDED_PRICE']??0);
                if ( $data[$_supplierId]['result']===false ) {
                    $data[$_supplierId]['error'] = 'No applicable rule found';
                }
            }else{
                $data[$_supplierId]['result'] = false;
                $data[$_supplierId]['error'] = '';
            }
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = array(
            'data' => $data
        );
    }

    public function actionSupplierPrice() {

        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/suppliers');

        $currencies = Yii::$container->get('currencies');

        $this->layout = false;

        $this->view->suppliers = [];
        $target_id = Yii::$app->request->get('tID', 0);
        $products_tax_class_id = Yii::$app->request->post('products_tax_class_id', 'products_group_price');

        $manufacturers_id = Yii::$app->request->post('manufacturers_id',0);
        $products_id = Yii::$app->request->post('products_id',0);

        $suppliers_load = Yii::$app->request->post('suppliers_data', array());

        $inventoryUprid = Yii::$app->request->get('inventoryUprid', '');
        if ( !empty($inventoryUprid) ) {
            $suppliers_load = $suppliers_load[$inventoryUprid];
        }
        if (isset($suppliers_load[$products_id])){
            $suppliers_load = $suppliers_load[$products_id];
        }

        $suppliers_id = Yii::$app->request->post('suppliers_id', array());

        $calculatedPrices = [];

        $suppliers_data_query = tep_db_query("select * from " . TABLE_SUPPLIERS . " order by is_default DESC, sort_order, suppliers_name");
        while ($suppliers_data = tep_db_fetch_array($suppliers_data_query)) {
            if ( !isset($suppliers_load[$suppliers_data['suppliers_id']]['suppliers_price']) || $suppliers_load[$suppliers_data['suppliers_id']]['suppliers_price']<0 ) continue;

            $calculateData = $suppliers_load[$suppliers_data['suppliers_id']];
            if ( !isset($calculateData['status']) ) continue;

            $this->view->suppliers[$suppliers_data['suppliers_id']] = $calculateData;

            //$suppliers_load[$suppliers_data['suppliers_id']]['suppliers_price'] *= $currencies->get_market_price_rate(\common\helpers\Currencies::getCurrencyCode($suppliers_load[$suppliers_data['suppliers_id']]['currencies_id']), DEFAULT_CURRENCY);

            $params = [
                'products_id' => 0,
                'categories_id' => (isset($calculateData['categories_id']) && is_array($calculateData['categories_id']))?array_map('intval',$calculateData['categories_id']):[],
                'manufacturers_id' => $manufacturers_id,
                'currencies_id' => isset($calculateData['currencies_id'])?intval($calculateData['currencies_id']):0,
                'PRICE' => isset($calculateData['suppliers_price'])?((float)$calculateData['suppliers_price']):0,
                'MARGIN' => isset($calculateData['suppliers_margin_percentage'])? ((float)$calculateData['suppliers_margin_percentage']):null,
                'SURCHARGE' => isset($calculateData['suppliers_surcharge_amount'])? ((float)$calculateData['suppliers_surcharge_amount']):null,
                'DISCOUNT' => isset($calculateData['supplier_discount'])?((float)$calculateData['supplier_discount']):null,
                'tax_rate' => isset($calculateData['tax_rate'])?((float)$calculateData['tax_rate']):null,
                'price_with_tax' => isset($calculateData['price_with_tax'])?$calculateData['price_with_tax']:null,
            ];

            $result = \common\helpers\PriceFormula::applyRules($params, $suppliers_data['suppliers_id']);
            if ( $result===false ) continue;

            $result['product'] = [
                'suppliers_id' => $suppliers_data['suppliers_id'],
                'qty' => $calculateData['suppliers_quantity'],
                'status' => $calculateData['status'],
                'is_default' => false,//$product_data['is_default'],
            ];

            $calculatedPrices[$suppliers_data['suppliers_id']] = $result;

            $suppliers_calculated_price = $result['resultPrice'];

            $suppliers_sale_price = $result['applyParams']['PRICE'] * (1 - $result['applyParams']['DISCOUNT']/100);

            $this->view->suppliers[$suppliers_data['suppliers_id']]['target_id'] = $target_id;
            $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_name'] = $suppliers_data['suppliers_name'];
            $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_calculated_price_net'] = $currencies->display_price($suppliers_calculated_price, 0);
            $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_calculated_price_gross'] = $currencies->display_price($suppliers_calculated_price, \common\helpers\Tax::get_tax_rate_value($products_tax_class_id));
            $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_calculated_profit'] = $currencies->format($suppliers_calculated_price - $suppliers_sale_price);

        }

        foreach (\common\helpers\Hooks::getList('categories/supplier-price') as $filename) {
            include($filename);
        }

        return $this->render('supplierprice');
    }

    public function actionAutoSupplierPrice() {
        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/suppliers');

        $this->layout = false;

        $this->view->suppliers = [];
        $target_id = Yii::$app->request->get('tID', 0);
        $products_tax_class_id = Yii::$app->request->post('products_tax_class_id', 'products_group_price');

        $manufacturers_id = Yii::$app->request->post('manufacturers_id',0);
        $products_id = Yii::$app->request->post('products_id',0);

        $suppliers_load = Yii::$app->request->post('suppliers_data', array());

        $inventoryUprid = Yii::$app->request->get('inventoryUprid', '');
        if ( !empty($inventoryUprid) ) {
            $suppliers_load = $suppliers_load[$inventoryUprid];
        }
        if (isset($suppliers_load[$products_id])){
            $suppliers_load = $suppliers_load[$products_id];
        }

        $calculatedPrices = [];

        $suppliers_data_query = tep_db_query("select * from " . TABLE_SUPPLIERS . " order by is_default DESC, sort_order, suppliers_name");
        while ($suppliers_data = tep_db_fetch_array($suppliers_data_query)) {
            if ( !isset($suppliers_load[$suppliers_data['suppliers_id']]['suppliers_price']) || $suppliers_load[$suppliers_data['suppliers_id']]['suppliers_price']<=0 ) continue;

            $calculateData = $suppliers_load[$suppliers_data['suppliers_id']];
            if ( !isset($calculateData['status']) ) continue;

            $this->view->suppliers[$suppliers_data['suppliers_id']] = $calculateData;

            $params = [
                'products_id' => 0,
                'categories_id' => (isset($calculateData['categories_id']) && is_array($calculateData['categories_id']))?array_map('intval',$calculateData['categories_id']):[],
                'manufacturers_id' => $manufacturers_id,
                'currencies_id' => isset($calculateData['currencies_id'])?intval($calculateData['currencies_id']):0,
                'PRICE' => isset($calculateData['suppliers_price'])?floatval($calculateData['suppliers_price']):0,
                'MARGIN' => isset($calculateData['suppliers_margin_percentage'])?$calculateData['suppliers_margin_percentage']:null,
                'SURCHARGE' => isset($calculateData['suppliers_surcharge_amount'])?$calculateData['suppliers_surcharge_amount']:null,
                'DISCOUNT' => isset($calculateData['supplier_discount'])?$calculateData['supplier_discount']:null,
                'tax_rate' => isset($calculateData['tax_rate'])?$calculateData['tax_rate']:null,
                'price_with_tax' => isset($calculateData['price_with_tax'])?$calculateData['price_with_tax']:null,
            ];

            $result = \common\helpers\PriceFormula::applyRules($params, $suppliers_data['suppliers_id']);
            if ( $result===false ) continue;

            $result['product'] = [
                'suppliers_id' => $suppliers_data['suppliers_id'],
                'qty' => $calculateData['suppliers_quantity'],
                'status' => $calculateData['status'],
                'is_default' => false,
            ];

            $calculatedPrices[$suppliers_data['suppliers_id']] = $result;
        }

        $selectedSupplierId = 0;

        foreach (\common\helpers\Hooks::getList('categories/auto-supplier-price') as $filename) {
            include($filename);
        }

        $response = [
            'id' => $selectedSupplierId
        ];
        echo json_encode($response);
    }

    public function actionSupplierAdd() {

        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/suppliers');

        $currencies = Yii::$container->get('currencies');

        $suppliers_id = Yii::$app->request->post('suppliers_id', 0);
        $mode = Yii::$app->request->get('mode', 'product');
        $mode = Yii::$app->request->post('mode', $mode);
        if (!$suppliers_id) {
            $suppliers_data = Yii::$app->request->post('suppliers_data', []);
            $supplier = new Suppliers();
            if ($supplier->load($suppliers_data, '') && $supplier->validate()){
                if ($supplier->saveSupplier($suppliers_data)){
                    $suppliers_id = $supplier->suppliers_id;
                }
            }
        }

        if ($suppliers_id > 0) {
            $service = new \common\services\SupplierService();
            Yii::configure($service, [
                'allow_change_status' => true,
                'allow_change_default' => true,
                'allow_change_surcharge' => true,
                'allow_change_margin' => true,
                'allow_change_price_formula' => true,
                'allow_change_auth' => true,
            ]);

            $this->layout = false;
            if ( $mode=='category' ) {
                $rulesModel = new \backend\models\SuppliersRules();
                $sInfo = new \stdClass();
                $rulesModel->getSuppliersData(\common\models\Suppliers::findOne(['suppliers_id'=>$suppliers_id]), $sInfo);

                return $this->render('category-supplier-block.tpl', [
                        'sInfo' => $sInfo->supplier_data[$suppliers_id],
                        'mayEditCost' => true,
                        //'currenciesVariants'=>$sInfo->supplierCurrenciesVariants,
                    ]
                );
            }else {
                $service->get('\common\models\SuppliersProducts', 'sProduct');
                $sInfo = new SuppliersProducts;
                $sInfo->loadDefaultValues();
                $sInfo->loadSupplierValues($suppliers_id);
                $sInfo->status = 1;
                $sInfo->products_id = (int)$_POST['uprid'];
                $sInfo->uprid = $_POST['uprid'];
                $this->layout = false;
                if (strpos($_POST['uprid'], '{') !== false) {
                    return $this->render('supplierinventory', ['sInfo' => $sInfo, 'uprid' => $_POST['uprid'], 'currencies' => $currencies, 'service' => $service]);
                } else {
                    return $this->render('supplierproduct', ['sInfo' => $sInfo, 'uprid' => (int)$_POST['uprid'], 'currencies' => $currencies, 'cMap' => \yii\helpers\ArrayHelper::map($currencies->currencies, 'id', 'title'), 'service' => $service]);
                }
            }
        }
    }

    public function actionFilterTabList() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $draw = Yii::$app->request->get('draw', 1);
        $categories_id = Yii::$app->request->get('cID', 0);

        $categories_array = array($categories_id => $categories_id);
        \common\helpers\Categories::get_subcategories($categories_array, $categories_id);

        $responseList = array();
        $filters_query = tep_db_query("

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_KEYWORDS) . "' as name, '' as values_array, 'keywords' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_FILTERS . " f on f.filters_type = 'keywords' and f.categories_id = '" . (int) $categories_id . "' and f.filters_of = 'category' where p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by id)

union

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_PRICE) . "' as name, group_concat(distinct round(p.products_price, 2) order by p.products_price asc separator ',') as values_array, 'price' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_FILTERS . " f on f.filters_type = 'price' and f.categories_id = '" . (int) $categories_id . "' and f.filters_of = 'category' where p.products_price > 0 and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by id)

union

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_CATEGORY) . "' as name, group_concat(distinct c.categories_id order by c.categories_id asc separator ',') as values_array, 'category' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c2 on p.products_id = p2c2.products_id left join " . TABLE_CATEGORIES . " c on p2c2.categories_id = c.categories_id and c.parent_id = '" . (int) $categories_id . "', " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_FILTERS . " f on f.filters_type = 'category' and f.categories_id = '" . (int) $categories_id . "' and f.filters_of = 'category' where c.categories_id > 0 and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by id)

union

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_MANUFACTURER) . "' as name, group_concat(distinct p.manufacturers_id order by p.manufacturers_id asc separator ',') as values_array, 'brand' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_FILTERS . " f on f.filters_type = 'brand' and f.categories_id = '" . (int) $categories_id . "' and f.filters_of = 'category' where p.manufacturers_id > 0 and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by id)

union

(select po.products_options_id as id, concat('" . tep_db_input(TEXT_ATTRIBUTE . ': ') . "', po.products_options_name) as name, group_concat(distinct pa.options_values_id order by pa.options_values_id asc separator ',') as values_array, 'attribute' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS_OPTIONS . " po left join " . TABLE_FILTERS . " f on f.options_id = po.products_options_id and f.filters_type = 'attribute' and f.categories_id = '" . (int) $categories_id . "' and f.filters_of = 'category', " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where po.products_options_id = pa.options_id and po.display_filter = '1' and po.language_id = '" . (int) $languages_id . "' and pa.products_id = p.products_id and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by po.products_options_id order by f.status desc, f.sort_order, po.products_options_sort_order, po.products_options_name)

union

(select pr.properties_id as id, concat('" . tep_db_input(TEXT_PROPERTY . ': ') . "', if(length(prd.properties_name_alt) > 0, prd.properties_name_alt, prd.properties_name)) as name, group_concat(distinct pr2p.values_id order by pr2p.values_id asc separator ',') as values_array, 'property' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PROPERTIES . " pr left join " . TABLE_FILTERS . " f on f.properties_id = pr.properties_id and f.filters_type = 'property' and f.categories_id = '" . (int) $categories_id . "' and f.filters_of = 'category', " . TABLE_PROPERTIES_DESCRIPTION . " prd, " . TABLE_PROPERTIES_TO_PRODUCTS . " pr2p, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where pr.properties_id = pr2p.properties_id and pr.display_filter = '1' and pr.properties_id = prd.properties_id and prd.language_id = '" . (int) $languages_id . "' and pr2p.products_id = p.products_id and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by pr.properties_id order by f.status desc, f.sort_order, pr.sort_order, prd.properties_name)

order by status desc, sort_order

");

        while ($filters = tep_db_fetch_array($filters_query)) {
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductPropertiesFilters', 'allowed')) {
                $responseList[] = $ext::getRowData($filters);
            } else {
                $responseList[] = array(
                    '<div class="handle_cat_list dis_module"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="module_title">' . $filters['name'] . '</div></div>',
                    '<div class="count_block dis_module">' . (tep_not_null($filters['values_array']) ? '<span class="count_values">' . count(explode(',', $filters['values_array'])) . '</span><a href="javascript:void(0)" class="view_filter_values">' . TEXT_VIEW_VALUES . '</a>' : '&nbsp;') . '</div>',
                    '<input type="checkbox" value="1" class="check_on_off_filters" disabled>'
                );
            }
        }

        $response = [
            'draw' => $draw,
            'data' => $responseList
        ];
        echo json_encode($response);
    }

    public function actionFilterBrandTabList() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        \common\helpers\Translation::init('admin/categories');

        $draw = Yii::$app->request->get('draw', 1);
        $manufacturers_id = Yii::$app->request->get('mID', 0);

        $responseList = array();
        $filters_query = tep_db_query("

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_KEYWORDS) . "' as name, '' as values_array, 'keywords' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p left join " . TABLE_FILTERS . " f on f.filters_type = 'keywords' and f.manufacturers_id = '" . (int) $manufacturers_id . "' and f.filters_of = 'brand' where 1 " . ($manufacturers_id > 0 ? " and p.manufacturers_id = '" . (int) $manufacturers_id . "'" : '') . " group by id)

union

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_PRICE) . "' as name, group_concat(distinct round(p.products_price, 2) order by p.products_price asc separator ',') as values_array, 'price' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p left join " . TABLE_FILTERS . " f on f.filters_type = 'price' and f.manufacturers_id = '" . (int) $manufacturers_id . "' and f.filters_of = 'brand' where p.products_price > 0 " . ($manufacturers_id > 0 ? " and p.manufacturers_id = '" . (int) $manufacturers_id . "'" : '') . " group by id)

union

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_CATEGORY) . "' as name, group_concat(distinct c.categories_id order by c.categories_id asc separator ',') as values_array, 'category' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c2 on p.products_id = p2c2.products_id left join " . TABLE_CATEGORIES . " c on p2c2.categories_id = c.categories_id left join " . TABLE_FILTERS . " f on f.filters_type = 'category' and f.manufacturers_id = '" . (int) $manufacturers_id . "' and f.filters_of = 'brand' where c.categories_id > 0 " . ($manufacturers_id > 0 ? " and p.manufacturers_id = '" . (int) $manufacturers_id . "'" : '') . " group by id)

union

(select po.products_options_id as id, concat('" . tep_db_input(TEXT_ATTRIBUTE . ': ') . "', po.products_options_name) as name, group_concat(distinct pa.options_values_id order by pa.options_values_id asc separator ',') as values_array, 'attribute' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS_OPTIONS . " po left join " . TABLE_FILTERS . " f on f.options_id = po.products_options_id and f.filters_type = 'attribute' and f.manufacturers_id = '" . (int) $manufacturers_id . "' and f.filters_of = 'brand', " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS . " p where po.products_options_id = pa.options_id and po.display_filter = '1' and po.language_id = '" . (int) $languages_id . "' and pa.products_id = p.products_id " . ($manufacturers_id > 0 ? " and p.manufacturers_id = '" . (int) $manufacturers_id . "'" : '') . " group by po.products_options_id order by f.status desc, f.sort_order, po.products_options_sort_order, po.products_options_name)

union

(select pr.properties_id as id, concat('" . tep_db_input(TEXT_PROPERTY . ': ') . "', if(length(prd.properties_name_alt) > 0, prd.properties_name_alt, prd.properties_name)) as name, group_concat(distinct pr2p.values_id order by pr2p.values_id asc separator ',') as values_array, 'property' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PROPERTIES . " pr left join " . TABLE_FILTERS . " f on f.properties_id = pr.properties_id and f.filters_type = 'property' and f.manufacturers_id = '" . (int) $manufacturers_id . "' and f.filters_of = 'brand', " . TABLE_PROPERTIES_DESCRIPTION . " prd, " . TABLE_PROPERTIES_TO_PRODUCTS . " pr2p, " . TABLE_PRODUCTS . " p where pr.properties_id = pr2p.properties_id and pr.display_filter = '1' and pr.properties_id = prd.properties_id and prd.language_id = '" . (int) $languages_id . "' and pr2p.products_id = p.products_id " . ($manufacturers_id > 0 ? " and p.manufacturers_id = '" . (int) $manufacturers_id . "'" : '') . " group by pr.properties_id order by f.status desc, f.sort_order, pr.sort_order, prd.properties_name)

order by status desc, sort_order

");

        while ($filters = tep_db_fetch_array($filters_query)) {
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductPropertiesFilters', 'allowed')) {
                $responseList[] = $ext::getRowData($filters);
            } else {
                $responseList[] = array(
                    '<div class="handle_cat_list dis_module"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="module_title">' . $filters['name'] . '</div></div>',
                    '<div class="count_block dis_module">' . (tep_not_null($filters['values_array']) ? '<span class="count_values">' . count(explode(',', $filters['values_array'])) . '</span><a href="javascript:void(0)" class="view_filter_values">' . TEXT_VIEW_VALUES . '</a>' : '&nbsp;') . '</div>',
                    '<input type="checkbox" value="1" class="check_on_off_filters" disabled>'
                );
            }
        }

        $response = [
            'draw' => $draw,
            'data' => $responseList
        ];
        echo json_encode($response);
    }

    public function actionViewvalues() {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $this->layout = false;
        $this->view->usePopupMode = true;

        $type = Yii::$app->request->get('type');
        $id = Yii::$app->request->get('id');
        $values = Yii::$app->request->get('values', array());

        $values_html = '';
        switch ($type) {
            case 'price':
                $currencies = Yii::$container->get('currencies');
                foreach (explode(',', $values) as $price) {
                    $values_html .= '<div>' . $currencies->format($price) . '</div>';
                }
                break;
            case 'category':
                $values_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id in ('" . implode("','", explode(',', $values)) . "') and language_id = '" . (int) $languages_id . "' order by categories_name");
                while ($values = tep_db_fetch_array($values_query)) {
                    $values_html .= '<div>' . $values['categories_name'] . '</div>';
                }
                break;
            case 'brand':
                $values_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id in ('" . implode("','", explode(',', $values)) . "') order by manufacturers_name");
                while ($values = tep_db_fetch_array($values_query)) {
                    $values_html .= '<div>' . $values['manufacturers_name'] . '</div>';
                }
                break;
            case 'attribute':
                $values_query = tep_db_query("select pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pov2po.products_options_values_id = pov.products_options_values_id and pov2po.products_options_id = '" . (int) $id . "' and pov.products_options_values_id in ('" . implode("','", explode(',', $values)) . "') and pov.language_id = '" . (int) $languages_id . "' order by pov.products_options_values_name");
                while ($values = tep_db_fetch_array($values_query)) {
                    $values_html .= '<div>' . $values['products_options_values_name'] . '</div>';
                }
                break;
            case 'property':
                $values_query = tep_db_query("select p.properties_type, p.decimals, pv.values_text, pv.values_number, pv.values_number_upto, pv.values_alt from " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_VALUES . " pv where p.properties_id = pv.properties_id and pv.properties_id = '" . (int) $id . "' and pv.values_id in ('" . implode("','", explode(',', $values)) . "') and pv.language_id = '" . (int) $languages_id . "' order by pv.values_number, pv.values_text");
                while ($values = tep_db_fetch_array($values_query)) {
                    if ($values['properties_type'] == 'number' || $values['properties_type'] == 'interval') {
                        $values_html .= '<div>' . (float) number_format($values['values_number'], $values['decimals']) . '</div>';
                    } elseif ($values['properties_type'] == 'interval') {
                        $values_html .= '<div>' . (float) number_format($values['values_number'], $values['decimals']) . ' - ' . (float) number_format($values['values_number_upto'], $values['decimals']) . '</div>';
                    } else {
                        $values_html .= '<div>' . $values['values_text'] . '</div>';
                    }
                }
                break;
        }

        $html = '<div class="viewContent">' . $values_html . '</div>';

        return $html;
    }

    public function actionFileManager() {
        $this->layout = false;

        unset($_SESSION['uploaded_file_name']);

        $fsPath = DIR_FS_CATALOG . 'documents/';
        $wsPath = DIR_WS_CATALOG . 'documents/';

        $fileList = [];
        $downloadList = array_diff(scandir($fsPath), array('..', '.'));
        foreach ($downloadList as $downloadFile) {
            if (is_file($fsPath . '/' . $downloadFile)) {
                $fileList[] = $downloadFile;
            }
        }
        return $this->render('file-manager', [
                    'fileList' => $fileList,
        ]);
    }

    public function actionFileManagerUpload() {
        $response = ['status' => 'error'];
        if (isset($_FILES['files'])) {
            $path = DIR_FS_CATALOG . 'documents/';
            $uploadfile = $path . \common\helpers\Output::mb_basename($_FILES['files']['name']);

            if (move_uploaded_file($_FILES['files']['tmp_name'], $uploadfile)) {
                $text = '';
                $_SESSION['uploaded_file_name'][] = $_FILES['files']['name'];
                $response = ['status' => 'ok', 'text' => $text, 'file_name' => $_FILES['files']['name']];
            }
        }
        echo json_encode($response);
    }

    public function actionFileManagerListing() {
        $this->layout = false;
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/categories');

        $fsPath = DIR_FS_CATALOG . 'documents/';
        $wsPath = DIR_WS_CATALOG . 'documents/';

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_category_id = Yii::$app->request->get('id', 0);
        $search = Yii::$app->request->get('search');

        if ($length == -1)
            $length = 10000;

        $documents = [];
        $documents[] = array('id' => '', 'text' => 'Please choose group to link');
        $documents_data_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " where language_id='" . $languages_id . "' order by document_types_name");
        while ($documents_data = tep_db_fetch_array($documents_data_query)) {
            $documents[] = array('id' => $documents_data['document_types_id'], 'text' => $documents_data['document_types_name']);
        }
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);
        $files_arr = array();
        if (is_array($output['filename'])) {
            foreach ($output['filename'] as $item1) {
                foreach ($item1 as $item2) {
                    $files_arr[] = $item2;
                }
            }
        }

        /**
         * products_documents_id
         * document_types_id
         * filename
         * title
         */
        $products_id = (int) $output['global_id'];

        $fileList = [];
        try {
            $downloadList = array_map('basename',FileHelper::findFiles($fsPath, [])); // array_diff(scandir($fsPath), array('..', '.'));
        }catch(\Exception $ex){
            $downloadList = [];
        }
        $uploaded_file_names = $_SESSION['uploaded_file_name'];
        if ($uploaded_file_names) {
            $downloadList = array_unique(array_merge($uploaded_file_names, $downloadList));
            $new_files = count($uploaded_file_names);
        } else {
            $uploaded_file_names = array();
            $new_files = 0;
        }
        $counter = 0;
        foreach ($downloadList as $downloadFile) {
            if ($search['value']) {
                if (strpos(strtolower($downloadFile), strtolower($search['value'])) === false) {
                    continue;
                }
            }
            if ($counter > $new_files && in_array($downloadFile, $uploaded_file_names)) {
                continue;
            }
            if (is_file($fsPath . '/' . $downloadFile)) {
                //file not used?
                $docs_data_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_DOCUMENTS . " where filename='" . tep_db_input($downloadFile) . "'");
                $docs_data = tep_db_fetch_array($docs_data_query);
                $actions = '';
                $delete = '';
                if ($docs_data['total'] == 0 && !in_array($downloadFile, $files_arr)) {
                    $delete = '<span class="file-remove" onclick="deleteFile(\'' . $downloadFile . '\')" title="' . IMAGE_DELETE . '"></span>';
                }
                /* $docs_data_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_DOCUMENTS . " where filename='" . tep_db_input($downloadFile) . "' and products_id=" . (int)$products_id);
                  $docs_data = tep_db_fetch_array($docs_data_query); */

                if (!in_array($downloadFile, $files_arr)/* $docs_data['total'] == 0 */) {
                    $actions .= tep_draw_pull_down_menu('doc_type_' . $counter, $documents, '', 'class="form-control"') . '<span onclick="addFile(\'' . addslashes($downloadFile) . '\', \'' . $products_id . '\', \'' . 'doc_type_' . $counter . '\')" class="btn">' . TEXT_ADD . '</span>';
                } else {
                    $actions .= '&nbsp;<span onclick="removeFile(\'' . addslashes($downloadFile) . '\', \'' . $products_id . '\')" class="unlink">' . UNLINK_FROM_PRODUCT . '</span>';
                }

                $downloadFile = '<span onclick="renameFile(\'' . $downloadFile . '\')" class="btn-edit-file" title="' . EDIT_FILE_NAME . '"></span><span class="file-name" data-name="' . $downloadFile . '">' . $downloadFile . '</span>';

                if ($counter == $new_files - 1) {
                    $downloadFile .= '
<script type="text/javascript">
  $("#document_list tbody tr").each(function(i){
    if (i < ' . $new_files . ') $(this).addClass("new-file")
  })
</script>';
                }


                $fileList[] = [
                    $downloadFile,
                    $actions,
                    $delete
                ];
                $counter++;
            }
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $counter,
            'recordsFiltered' => $counter,
            'data' => $fileList
        ];
        echo json_encode($response);
    }

    public function actionFileManagerDelete() {
        $this->layout = false;

        $fsPath = DIR_FS_CATALOG . 'documents/';

        $downloadFile = Yii::$app->request->get('name');
        $downloadFile = \common\helpers\Output::mb_basename($downloadFile);
        if (is_file($fsPath . '/' . $downloadFile)) {
            @unlink($fsPath . '/' . $downloadFile);
        }
    }

    public function actionFileManagerRemove() {
        $this->layout = false;

        $products_id = (int) Yii::$app->request->post('id');
        $downloadFile = tep_db_prepare_input(Yii::$app->request->post('name'));

        $query = tep_db_query("select products_documents_id from " . TABLE_PRODUCTS_DOCUMENTS . " where products_id  = '" . (int) $products_id . "'");
        while ($item = tep_db_fetch_array($query)) {
            tep_db_query("delete from " . TABLE_PRODUCTS_DOCUMENTS_TITLES . " where products_documents_id  = '" . (int) $item['products_documents_id'] . "'");
        }
        tep_db_query("delete from " . TABLE_PRODUCTS_DOCUMENTS . " where products_id = '" . $products_id . "' and filename = '" . tep_db_input($downloadFile) . "'");
    }

    public function actionFileManagerAdd() {
        $this->layout = false;
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/categories');

        $formFilter = Yii::$app->request->post('filter');
        parse_str($formFilter, $output);
        $products_id = (int) $output['global_id'];

        $name = Yii::$app->request->post('name');
        $type = (int) Yii::$app->request->post('type');
        $isLink = (int) Yii::$app->request->post('isLink');

        /**
         * products_documents_id
         * document_types_id
         * filename
         * title
         */
        $products_documents_id = $output['products_documents_id'];
        $document_types_id = $output['document_types_id'];
        $filename = $output['filename'];
        $is_link = $output['is_link'];
        $title = $output['title'];
        $sort_order = $output['sort_order'];

        $languages = \common\helpers\Language::get_languages();
        $this->view->documents = [];
        $documents_data_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " where language_id='" . $languages_id . "' order by document_types_name");
        while ($documents_data = tep_db_fetch_array($documents_data_query)) {
            $docs = [];
            if (isset($products_documents_id[$documents_data['document_types_id']]) && is_array($products_documents_id[$documents_data['document_types_id']])) {
                foreach ($products_documents_id[$documents_data['document_types_id']] as $key => $value) {
                    $doc_title = array();
                    foreach ($languages as $language) {
                        $doc_title[$language['id']] = $title[$language['id']][$documents_data['document_types_id']][$key];
                    }
                    $docs[] = [
                        'products_documents_id' => $value,
                        'document_types_id' => $document_types_id[$documents_data['document_types_id']][$key],
                        'filename' => $filename[$documents_data['document_types_id']][$key],
                        'is_link' => $is_link[$documents_data['document_types_id']][$key],
                        'title' => $doc_title,
                        'sort_order' => $sort_order[$documents_data['document_types_id']][$key],
                    ];
                }
            }
            if ($documents_data['document_types_id'] == $type) {
                $docs[] = [
                    'products_documents_id' => '',
                    'document_types_id' => $type,
                    'filename' => $name,
                    'is_link' => $isLink,
                    'title' => '',
                ];
            }

            /* $docs_data_query = tep_db_query("select * from " . TABLE_PRODUCTS_DOCUMENTS . " where document_types_id=" . $documents_data['document_types_id'] . " and products_id=" . (int)$products_id);
              while ($docs_data = tep_db_fetch_array($docs_data_query)) {
              $docs[] = $docs_data;
              } */
            $this->view->documents[$documents_data['document_types_id']] = [
                'id' => $documents_data['document_types_id'],
                'title' => $documents_data['document_types_name'],
                'docs' => $docs,
            ];
        }

        return $this->render('file-manager-add', [
                    'global_id' => $products_id,
                    'languages' => $languages,
                    'languages_id' => $languages_id,
        ]);
    }

    public function actionFileAddExternalLink()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Translation::init('admin/categories');
        $this->layout = false;

        if ( Yii::$app->request->isPost ) {

        }

        $languages = \common\helpers\Language::get_languages();

        $documentTypeVariants = [];
        $documents_data_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " where language_id='" . $languages_id . "' order by document_types_name");
        while ($documents_data = tep_db_fetch_array($documents_data_query)) {
            $documentTypeVariants[$documents_data['document_types_id']] = $documents_data['document_types_name'];
        }
        return $this->render('file-add-external-url.tpl', [
                'form_action_href' => Yii::$app->urlManager->createUrl('categories/file-add-external-link'),
                'documentTypeVariants' => $documentTypeVariants,
            ]);

    }

    public function actionFileGroups() {
        \common\helpers\Translation::init('admin/categories');
        $languages_id = \Yii::$app->settings->get('languages_id');
        $this->layout = false;
        $languages = \common\helpers\Language::get_languages();


        $types = array();
        $types_list = array();
        $documents_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " order by document_types_name");
        while ($documents = tep_db_fetch_array($documents_query)) {
            $types[$documents['language_id']][$documents['document_types_id']] = $documents;
            $types_list[$documents['document_types_id']] = $documents['document_types_id'];
        }

        return $this->render('file-groups', [
                    'languages' => $languages,
                    'languages_id' => $languages_id,
                    'types' => $types,
                    'types_list' => $types_list,
        ]);
    }

    public function actionFileGroupsSave() {
        $this->layout = false;
        $languages = \common\helpers\Language::get_languages();

        $types = Yii::$app->request->post('type');

        foreach ($languages as $language) {
            foreach ($types[$language['id']] as $id => $type) {

                $types_icon = '';
                if ($type['document_types_icon']) {
                    $icon = tep_db_fetch_array(tep_db_query("select document_types_icon from " . TABLE_DOCUMENT_TYPES . " where document_types_id = '" . (int) $id . "' and language_id = '" . $language['id'] . "'"));
                    if ($icon['document_types_icon'] == $type['document_types_icon']) {
                        $types_icon = $type['document_types_icon'];
                    } else {

                        $path = \Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
                        if (!is_file($path . $type['document_types_icon'])) {
                            $types_icon = 'images/' . Uploads::move($type['document_types_icon']);
                        } else {
                            $types_icon = $type['document_types_icon'];
                        }
                    }
                }

                $document_types = tep_db_query("select document_types_icon from " . TABLE_DOCUMENT_TYPES . " where document_types_id = '" . (int) $id . "' and language_id = '" . $language['id'] . "'");
                if (tep_db_num_rows($document_types) > 0) {
                    $sql_data_array = array(
                        'document_types_name' => $type['document_types_name'],
                        'document_types_icon' => $types_icon,
                    );
                    tep_db_perform(TABLE_DOCUMENT_TYPES, $sql_data_array, 'update', "document_types_id = '" . (int) $id . "' and language_id = '" . $language['id'] . "'");
                } else {
                    $sql_data_array = array(
                        'document_types_id' => $id,
                        'language_id' => $language['id'],
                        'document_types_name' => $type['document_types_name'],
                        'document_types_icon' => $types_icon,
                    );
                    tep_db_perform(TABLE_DOCUMENT_TYPES, $sql_data_array);
                }
            }
        }

        $types = array();
        $types_list = array();
        $documents_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " order by document_types_name");
        while ($documents = tep_db_fetch_array($documents_query)) {
            $types[$documents['language_id']][$documents['document_types_id']] = $documents;
            $types_list[$documents['document_types_id']] = $documents['document_types_id'];
        }

        return '';
    }

    public function actionFileGroupsAdd() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $this->layout = false;
        $languages = \common\helpers\Language::get_languages();

        $documents = tep_db_fetch_array(tep_db_query("select max(document_types_id) as id from " . TABLE_DOCUMENT_TYPES . " "));

        $sql_data_array = array(
            'document_types_id' => $documents['id'] + 1,
            'language_id' => $languages_id,
            'document_types_name' => '',
            'document_types_icon' => '',
        );
        tep_db_perform(TABLE_DOCUMENT_TYPES, $sql_data_array);

        $types = array();
        $types_list = array();
        $documents_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " order by document_types_name");
        while ($documents = tep_db_fetch_array($documents_query)) {
            $types[$documents['language_id']][$documents['document_types_id']] = $documents;
            $types_list[$documents['document_types_id']] = $documents['document_types_id'];
        }

        return $this->render('file-groups', [
            'languages' => $languages,
            'languages_id' => $languages_id,
            'types' => $types,
            'types_list' => $types_list,
        ]);
    }

    public function actionFileGroupsRemove() {
        $this->layout = false;

        $document_types_id = Yii::$app->request->get('document_types_id');

        $query = tep_db_query("select products_documents_id from " . TABLE_PRODUCTS_DOCUMENTS . " where document_types_id  = '" . (int) $document_types_id . "'");
        while ($item = tep_db_fetch_array($query)) {
            tep_db_query("delete from " . TABLE_PRODUCTS_DOCUMENTS_TITLES . " where products_documents_id  = '" . (int) $item['products_documents_id'] . "'");
        }
        tep_db_query("delete from " . TABLE_DOCUMENT_TYPES . " where document_types_id = '" . (int) $document_types_id . "'");


        return json_encode('ok');
    }

    public function actionFileManagerRename() {
        $this->layout = false;

        $name = Yii::$app->request->get('name');
        $new_name = Yii::$app->request->get('new_name');
        $new_name = \common\helpers\Output::mb_basename($new_name);

        $sql_data_array = array(
            'filename' => $new_name,
        );
        tep_db_perform(TABLE_PRODUCTS_DOCUMENTS, $sql_data_array, 'update', "filename='" . $name . "'");

        $fsPath = DIR_FS_CATALOG . 'documents/';

        if (is_file($fsPath . '/' . $name)) {
            rename($fsPath . '/' . $name, $fsPath . '/' . $new_name);
        }

        return $new_name;
    }

    public function actionStockHistory() {
        $this->layout = false;
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductStockHistory', 'allowed')) {
            return $ext::actionStockHistory();
        }
    }

    public function actionStockInfo() {
        $this->layout = false;

        $prid = Yii::$app->request->get('prid');

        $warehouseNames = \yii\helpers\ArrayHelper::map(\common\helpers\Warehouses::get_warehouses(true), 'id', 'text');
        $blocks =\common\models\LocationBlocks::find()->asArray()->all();
        $blocksList = [];
        foreach ($blocks as $value) {
            $blocksList[$value['block_id']] = $value['block_name'];
        }

        $existStock = \common\models\WarehousesProducts::find()
            ->select(['warehouse_id', 'suppliers_id', 'location_id', 'layers_id', 'batch_id', 'products_quantity'])
            ->where(['products_id' => $prid])
            ->orderBy(['warehouse_id' => SORT_ASC, 'suppliers_id' => SORT_ASC, 'location_id' => SORT_ASC, 'layers_id' => SORT_ASC, 'batch_id' => SORT_ASC])
            ->asArray()
            ->all();

        $stockList = [];
        foreach ($existStock as $stock) {
            if ($stock['products_quantity'] <= 0) {
                continue;
            }
            $location = \common\helpers\Warehouses::getLocationPath($stock['location_id'], $stock['warehouse_id'], $blocksList);
            if (empty($location)) {
                $location = 'N/A';
            }
            $layer = 'N/A';
            if ($stock['layers_id']) {
                $layer = \common\helpers\Date::date_short(\common\helpers\Warehouses::getExpiryDateByLayersID($stock['layers_id'])); 
            }
            $batch = 'N/A';
            if ($stock['batch_id']) {
                $batch = \common\helpers\Warehouses::getBatchNameByBatchID($stock['batch_id']); 
            }
            $stockList[] = [
                'id' => $stock['location_id'] . '_' . $stock['layers_id'] . '_' . $stock['batch_id'],
                'warehouse' => isset($warehouseNames[$stock['warehouse_id']]) ? $warehouseNames[$stock['warehouse_id']] : '',
                'supplier' => \common\helpers\Suppliers::getSupplierName($stock['suppliers_id']),
                'location' => $location,
                'layer' => $layer,
                'batch' => $batch,
                'qty' => $stock['products_quantity'],
            ];
        }

        return $this->renderAjax('stock-info', ['stockList' => $stockList]);
    }

    public function actionProductQuantityUpdate() {
        \common\helpers\Translation::init('admin/categories');

        $box_location = Yii::$app->request->post('box_location');
        $isAutoallocate = (int)Yii::$app->request->post('is_autoallocate');

        $locationIds = explode(",", $box_location);
        $location_id = 0;
        if (is_array($locationIds)) {
            foreach ($locationIds as $id) {
                if ($id > 0) {
                    $location_id = $id;
                }
            }
        }
        $expiry_date = Yii::$app->request->post('expiry_date', '');
        $expiry_date = \common\helpers\Date::prepareInputDate($expiry_date);
        $layers_id = \common\helpers\Warehouses::getWarehousesProductsLayersIDbyExpiryDate($expiry_date);
        $batch_name = Yii::$app->request->post('batch_name', '');
        $batch_id = \common\helpers\Warehouses::getWarehousesProductsBatchIDbyBatchName($batch_name);

        $warehouse_id = (int) $_POST['warehouse_id'];
        $w_suppliers_id = (int)Yii::$app->request->post('w_suppliers_id', 0);
        $stock_comments = $_POST['stock_comments'];

        if (count(\common\helpers\Product::getChildArray($_POST['uprid'])) > 0) {
            return json_encode([]);
        }
        $response = [];
        $updateData = [];
        if (strpos($_POST['uprid'], '{') !== false && \common\helpers\Inventory::get_prid($_POST['uprid']) > 0) {
            $inventory_quantity_update = (int) $_POST['inventoryqtyupdate_' . $_POST['uprid']];
            $inventory_quantity_update_prefix = ($_POST['inventoryqtyupdateprefix_' . $_POST['uprid']] == '-' ? '-' : '+');

            if ($inventory_quantity_update_prefix == '-') {
                $existLocations = \common\models\WarehousesProducts::find()
                    ->select(['location_id', 'layers_id', 'batch_id', 'warehouse_stock_quantity'])
                    ->where(['warehouse_id' => $warehouse_id, 'suppliers_id' => $w_suppliers_id, 'products_id' => $_POST['uprid']])
                    ->orderBy(['location_id' => SORT_ASC, 'layers_id' => SORT_ASC, 'batch_id' => SORT_ASC])
                    ->asArray()
                    ->all();
                foreach ($existLocations as $location) {
                    if (isset($_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']]) && $_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']] > 0) {
                        $updateData[] = [
                            'quantity' => (int) $_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']],
                            'prefix' => $inventory_quantity_update_prefix,
                            'location' => $location['location_id'],
                            'layers_id' => $location['layers_id'],
                            'batch_id' => $location['batch_id'],
                        ];
                    }
                }
            } else {
                $updateData[] = [
                    'quantity' => $inventory_quantity_update,
                    'prefix' => $inventory_quantity_update_prefix,
                    'location' => $location_id,
                    'layers_id' => $layers_id,
                    'batch_id' => $batch_id,
                ];
            }

            foreach ($updateData as $updateItem) {
                if ($updateItem['quantity'] > 0) {
                    $check_data = tep_db_fetch_array(tep_db_query("select products_quantity, ordered_stock_quantity, suppliers_stock_quantity from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($_POST['uprid']) . "'"));
                    if (!$check_data) {
                        tep_db_query("insert into " . TABLE_INVENTORY . " set inventory_id = '', products_id = '" . tep_db_input($_POST['uprid']) . "', prid = '" . (int) \common\helpers\Inventory::get_prid($_POST['uprid']) . "'");
                        $check_data['products_quantity'] = 0;
                    }

                    global $login_id;
                    //\common\helpers\Product::log_stock_history_before_update($_POST['uprid'], $updateItem['quantity'], $updateItem['prefix'], ['warehouse_id' => $warehouse_id, 'comments' => TEXT_MANUALL_STOCK_UPDATE . (trim($stock_comments) != '' ? ': ' . $stock_comments : ''), 'admin_id' => $login_id]);

                    if ($warehouse_id > 0) {
                        $parameters = [
                            'layers_id' => $updateItem['layers_id'],
                            'batch_id' => $updateItem['batch_id'],
                            'admin_id' => $login_id,
                            'comments' => (TEXT_MANUALL_STOCK_UPDATE . (trim($stock_comments) != '' ? ': ' . $stock_comments : ''))
                        ];
                        $check_data['warehouse_quantity'] = \common\helpers\Warehouses::update_products_quantity($_POST['uprid'], $warehouse_id, $updateItem['quantity'], $updateItem['prefix'], $w_suppliers_id, $updateItem['location'], $parameters);
                        if ($isAutoallocate) {
                            \common\helpers\Product::doAllocateAutomatic($_POST['uprid'], true);
                        }
                        $check_data['allocated_quantity'] = \common\helpers\Product::getAllocated($_POST['uprid']);
                        $check_data['temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($_POST['uprid']);
                        $check_data['allocated_temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($_POST['uprid'], true);
                        $check_data['deficit_quantity'] = \common\helpers\Product::getStockDeficit($_POST['uprid']);
                    } else {
                        tep_db_query("update " . TABLE_INVENTORY . " set products_quantity = products_quantity " . $updateItem['prefix'] . $updateItem['quantity'] . " where products_id = '" . tep_db_input($_POST['uprid']) . "'");

                        if ($updateItem['prefix'] == '-') {
                            $check_data['warehouse_quantity'] -= $updateItem['quantity'];
                        } else {
                            $check_data['warehouse_quantity'] += $updateItem['quantity'];
                        }
                        if ($isAutoallocate) {
                            \common\helpers\Product::doAllocateAutomatic($_POST['uprid'], true);
                        }
                        $check_data['allocated_quantity'] = \common\helpers\Product::getAllocated($_POST['uprid']);
                        $check_data['temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($_POST['uprid']);
                        $check_data['allocated_temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($_POST['uprid'], true);
                        $check_data['deficit_quantity'] = \common\helpers\Product::getStockDeficit($_POST['uprid']);
                    }
                    $check_data['products_quantity'] = $check_data['warehouse_quantity'] - ($check_data['allocated_quantity'] + $check_data['temporary_quantity']);
                    $check_data['ordered_quantity'] = $check_data['ordered_stock_quantity'];
                    $check_data['suppliers_quantity'] = $check_data['suppliers_stock_quantity'];

                    $response = $check_data;
                }
            }
        } elseif ($_POST['uprid'] > 0) {
            $products_quantity_update = (int) $_POST['products_quantity_update'];
            $products_quantity_update_prefix = ($_POST['products_quantity_update_prefix'] == '-' ? '-' : '+');

            if ($products_quantity_update_prefix == '-') {
                $existLocations = \common\models\WarehousesProducts::find()
                    ->select(['location_id', 'layers_id', 'batch_id', 'warehouse_stock_quantity'])
                    ->where(['warehouse_id' => $warehouse_id, 'suppliers_id' => $w_suppliers_id, 'products_id' => $_POST['uprid']])
                    ->orderBy(['location_id' => SORT_ASC, 'layers_id' => SORT_ASC, 'batch_id' => SORT_ASC])
                    ->asArray()
                    ->all();
                foreach ($existLocations as $location) {
                    if (isset($_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']]) && $_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']] > 0) {
                        $updateData[] = [
                            'quantity' => (int) $_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']],
                            'prefix' => $products_quantity_update_prefix,
                            'location' => $location['location_id'],
                            'layers_id' => $location['layers_id'],
                            'batch_id' => $location['batch_id'],
                        ];
                    }
                }
            } else {
                $updateData[] = [
                    'quantity' => $products_quantity_update,
                    'prefix' => $products_quantity_update_prefix,
                    'location' => $location_id,
                    'layers_id' => $layers_id,
                    'batch_id' => $batch_id,
                ];
            }

            foreach ($updateData as $updateItem) {
                if ($updateItem['quantity'] > 0) {
                    $check_data = tep_db_fetch_array(tep_db_query("select products_quantity, ordered_stock_quantity, suppliers_stock_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int) $_POST['uprid'] . "'"));

                    global $login_id;
                    //\common\helpers\Product::log_stock_history_before_update($_POST['uprid'], $updateItem['quantity'], $updateItem['prefix'], ['warehouse_id' => $warehouse_id, 'comments' => TEXT_MANUALL_STOCK_UPDATE . (trim($stock_comments) != '' ? ': ' . $stock_comments : ''), 'admin_id' => $login_id]);

                    if ($warehouse_id > 0) {
                        $parameters = [
                            'layers_id' => $updateItem['layers_id'],
                            'batch_id' => $updateItem['batch_id'],
                            'admin_id' => $login_id,
                            'comments' => (TEXT_MANUALL_STOCK_UPDATE . (trim($stock_comments) != '' ? ': ' . $stock_comments : ''))
                        ];
                        $check_data['warehouse_quantity'] = \common\helpers\Warehouses::update_products_quantity($_POST['uprid'], $warehouse_id, $updateItem['quantity'], $updateItem['prefix'], $w_suppliers_id, $updateItem['location'], $parameters);
                        if ($isAutoallocate) {
                            \common\helpers\Product::doAllocateAutomatic($_POST['uprid'], true);
                        }
                        $check_data['allocated_quantity'] = \common\helpers\Product::getAllocated($_POST['uprid']);
                        $check_data['temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($_POST['uprid']);
                        $check_data['allocated_temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($_POST['uprid'], true);
                        $check_data['deficit_quantity'] = \common\helpers\Product::getStockDeficit($_POST['uprid']);
                    } else {
                        tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity " . $updateItem['prefix'] . $updateItem['quantity'] . " where products_id = '" . (int) $_POST['uprid'] . "'");

                        if ($updateItem['prefix'] == '-') {
                            $check_data['warehouse_quantity'] -= $updateItem['quantity'];
                        } else {
                            $check_data['warehouse_quantity'] += $updateItem['quantity'];
                        }
                        if ($isAutoallocate) {
                            \common\helpers\Product::doAllocateAutomatic($_POST['uprid'], true);
                        }
                        $check_data['allocated_quantity'] = \common\helpers\Product::getAllocated($_POST['uprid']);
                        $check_data['temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($_POST['uprid']);
                        $check_data['allocated_temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($_POST['uprid'], true);
                        $check_data['deficit_quantity'] = \common\helpers\Product::getStockDeficit($_POST['uprid']);
                    }
                    $check_data['products_quantity'] = $check_data['warehouse_quantity'] - ($check_data['allocated_quantity'] + $check_data['temporary_quantity']);
                    $check_data['ordered_quantity'] = $check_data['ordered_stock_quantity'];
                    $check_data['suppliers_quantity'] = $check_data['suppliers_stock_quantity'];
                    $response = $check_data;
                }
            }
        }
        if ($response){
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')){
                if ($ext::getControlInstance($_POST['uprid'])->needStockControl()){
                    $response['warehouse_quantity'] = $ext::checkStock($_POST['uprid']);
                    $response['products_quantity'] = $response['warehouse_quantity'] - ($response['allocated_quantity'] + $response['temporary_quantity']);
                }
            }
        }
        if (isset($response['allocated_quantity'])) {
            $response['allocated_quantity'] -= $response['allocated_temporary_quantity'];
        }
        foreach ($response as &$value) {
            $value = \common\helpers\Product::getVirtualItemQuantity($_POST['uprid'], $value);
        }
        unset($value);
        echo json_encode($response);
    }

    public function actionProductStockDetails() {

        $uprid = Yii::$app->request->post('uprid');
        /** @var \common\extensions\Inventory\Inventory $invAllowed */
        $invAllowed = \common\helpers\Extensions::isAllowed('Inventory');
        if ($invAllowed) {
            $uprid = \common\helpers\Inventory::normalizeInventoryId($uprid);
        }

        $pInfo = \common\models\Products::findOne((int)$uprid)->getAttributes();
        if ( ($pInfo->parent_products_id??null) && ($pInfo->products_id_stock??null) ) {
            $prid = $pInfo->products_id_stock ?? null;
            $pInfo = \common\models\Products::findOne($prid)->getAttributes();

            if ($invAllowed) {
                $uprid = $prid . substr($uprid, strpos($uprid, '{'));
            } else {
                $uprid = $prid;
            }
        }

        if ($invAllowed && strpos($uprid, '{') !== false && \common\helpers\Inventory::get_prid($uprid) > 0) {
            $_data = tep_db_fetch_array(tep_db_query("select products_quantity, ordered_stock_quantity, suppliers_stock_quantity from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($uprid) . "'"));

            $check_data['warehouse_quantity'] = \common\helpers\Product::getQuantity($uprid);
            
        } else {
            $_data = (array)$pInfo;
            $check_data['warehouse_quantity'] = $_data['warehouse_stock_quantity'];
        }

        $check_data['ordered_quantity'] = $_data['ordered_stock_quantity']??0;
        $check_data['suppliers_quantity'] = $_data['suppliers_stock_quantity']??0;

        $check_data['allocated_quantity'] = \common\helpers\Product::getAllocated($uprid);
        $check_data['temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($uprid);
        $check_data['allocated_temporary_quantity'] = \common\helpers\Product::getAllocatedTemporary($uprid, true);
        $check_data['deficit_quantity'] = \common\helpers\Product::getStockDeficit($uprid);

        $check_data['products_quantity'] = $check_data['warehouse_quantity'] - ($check_data['allocated_quantity'] + $check_data['temporary_quantity']);

        
        $response = $check_data;
        
        if (!empty($response)){
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')){
                if ($ext::getControlInstance($uprid)->needStockControl()){
                    $response['warehouse_quantity'] = $ext::checkStock($uprid);
                    $response['products_quantity'] = $response['warehouse_quantity'] - ($response['allocated_quantity'] + $response['temporary_quantity']);
                }
            }
        }
        if (isset($response['allocated_quantity'])) {
            $response['allocated_quantity'] -= $response['allocated_temporary_quantity'];
        }
        foreach ($response as &$value) {
            $value = \common\helpers\Product::getVirtualItemQuantity($uprid, $value);
        }
        unset($value);

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $response;

    }

    public function actionStock() {
        \common\helpers\Translation::init('admin/categories');

        $prid = Yii::$app->request->get('prid');
        $suppliers_id = (int)Yii::$app->request->get('suppliers_id', 0);
        $warehouse_id = (int) Yii::$app->request->get('warehouse_id', 0);

        return $this->renderAjax('stock', ['prid' => $prid, 'suppliers_id' => $suppliers_id, 'warehouse_id' => $warehouse_id]);
    }

    public function actionWarehousesStock() {
        \common\helpers\Translation::init('admin/categories');

        if (Yii::$app->request->isPost) {
            $prid = Yii::$app->request->post('prid');
            $suppliers_id = (int)Yii::$app->request->post('suppliers_id', 0);
            $empty_row = (int) Yii::$app->request->post('empty_row');
            if (count(\common\helpers\Product::getChildArray($prid)) == 0) {
                $quantity_update = Yii::$app->request->post('quantity_update', []);
                $quantity_prefix = Yii::$app->request->post('quantity_prefix', []);
                $stock_comments = Yii::$app->request->post('stock_comments', []);
                foreach ($quantity_update as $warehouse_id => $quantity) {
                    if ($quantity > 0) {
                        $prefix = ($quantity_prefix[$warehouse_id] == '-' ? '-' : '+');
                        $comments = $stock_comments[$warehouse_id];
                        if (strpos($prid, '{') !== false) {
                            $check_data = tep_db_fetch_array(tep_db_query("select products_quantity from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($prid) . "'"));
                            if (!$check_data) {
                                tep_db_query("insert into " . TABLE_INVENTORY . " set inventory_id = '', products_id = '" . tep_db_input($prid) . "', prid = '" . (int) \common\helpers\Inventory::get_prid($prid) . "'");
                            }
                        }
                        global $login_id;
                        //\common\helpers\Product::log_stock_history_before_update($prid, $quantity, $prefix, ['warehouse_id' => $warehouse_id, 'suppliers_id' => $suppliers_id, 'comments' => TEXT_MANUALL_STOCK_UPDATE . (trim($comments) != '' ? ': ' . $comments : ''), 'admin_id' => $login_id]);
                        $parameters = [
                            'admin_id' => $login_id,
                            'comments' => (TEXT_MANUALL_STOCK_UPDATE . (trim($comments) != '' ? ': ' . $comments : ''))
                        ];
                        \common\helpers\Warehouses::update_products_quantity($prid, $warehouse_id, $quantity, $prefix, $suppliers_id, 0, $parameters);
                    }
                }
            }
        } else {
            $prid = Yii::$app->request->get('prid');
            $suppliers_id = (int)Yii::$app->request->get('suppliers_id', 0);
            $empty_row = (int) Yii::$app->request->get('empty_row');
        }

        $productAllocatedArray = [];
        foreach (\common\helpers\Product::getAllocatedArray($prid) as $productAllocatedRecord) {
            $productAllocatedArray[$productAllocatedRecord['warehouse_id']][$productAllocatedRecord['suppliers_id']] += ($productAllocatedRecord['allocate_received'] - $productAllocatedRecord['allocate_dispatched']);
            if ($suppliers_id == 0) {
                $productAllocatedArray[$productAllocatedRecord['warehouse_id']][$suppliers_id] += ($productAllocatedRecord['allocate_received'] - $productAllocatedRecord['allocate_dispatched']);
            }
        }

        $productAllocatedTemporaryArray = [];
        foreach (\common\helpers\Product::getAllocatedTemporaryArray($prid) as $productAllocatedTemporaryRecord) {
            $productAllocatedTemporaryArray[$productAllocatedTemporaryRecord['warehouse_id']][$productAllocatedTemporaryRecord['suppliers_id']] += $productAllocatedTemporaryRecord['temporary_stock_quantity'];
            if ($suppliers_id == 0) {
                $productAllocatedTemporaryArray[$productAllocatedTemporaryRecord['warehouse_id']][$suppliers_id] += $productAllocatedTemporaryRecord['temporary_stock_quantity'];
            }
        }

        $supplier = \common\helpers\Suppliers::getSuppliersList($prid);
        if ($suppliers_id == 0 && count ($supplier) > 1) {
            $master = 1;
        } else {
            $master = 0;
        }

        $warehouses = [];
        $products_quantity = $allocated_quantity = $temporary_quantity = $warehouse_quantity = $ordered_quantity = 0;

        $spq = \common\models\SuppliersProducts::find()
            ->addSelect('status as sp_status, suppliers_id')
            ;
        if (strpos($prid, '{') !== false) {
            $spq->andWhere(['uprid' => tep_db_input($prid), 'products_id' => (int) $prid]);
        } else {
            $spq->andWhere(['products_id' => (int) $prid]);
        }
        $spdata = $spq->indexBy('suppliers_id')->asArray()->all();

        $warehousesQuery = \common\models\Warehouses::find()
                ->select(['warehouse_id', 'warehouse_name', 'sort_order', 'status'])
                //->where(['status' => 1])
                ->orderBy(['sort_order' => SORT_ASC, 'warehouse_name' => SORT_ASC])
                ->asArray()
                ->all();
        foreach ($warehousesQuery as $warehousesRecord) {

            $warehousesStockQuery = \common\models\WarehousesProducts::find()
                    ->select(['sum(warehouse_stock_quantity) as warehouse_stock_quantity', 'sum(ordered_stock_quantity) as ordered_stock_quantity'])
                    ->where(['warehouse_id' => $warehousesRecord['warehouse_id']]);
            if ($suppliers_id > 0) {
                $warehousesStockQuery->andWhere(['suppliers_id' => (int) $suppliers_id]);
            }
            if (strpos($prid, '{') !== false) {
                $warehousesStockQuery->andWhere(['products_id' => tep_db_input($prid)]);
            } else {
                $warehousesStockQuery->andWhere(['products_id' => (int) $prid]);
            }
            $warehousesStock = $warehousesStockQuery->asArray()->one();

            $warehousesItem = [
                'id' => $warehousesRecord['warehouse_id'],
                'name' => $warehousesRecord['warehouse_name'],
                'sort_order' => $warehousesRecord['sort_order'],
                'allocated_quantity' => (isset($productAllocatedArray[$warehousesRecord['warehouse_id']][$suppliers_id]) ? $productAllocatedArray[$warehousesRecord['warehouse_id']][$suppliers_id] : 0),
                'temporary_quantity' => (isset($productAllocatedTemporaryArray[$warehousesRecord['warehouse_id']][$suppliers_id]) ? $productAllocatedTemporaryArray[$warehousesRecord['warehouse_id']][$suppliers_id] : 0),
                'warehouse_quantity' => (int)$warehousesStock['warehouse_stock_quantity'],
                'ordered_quantity' => (int)$warehousesStock['ordered_stock_quantity'],
                'master' => $master,
                'actions' => '',
                'warehouse_disabled' => !$warehousesRecord['status'],
            ];
            $warehousesItem['products_quantity'] = $warehousesItem['warehouse_quantity'] - ($warehousesItem['allocated_quantity'] + $warehousesItem['temporary_quantity']);

            if ($warehousesRecord['status']) {
                $products_quantity += $warehousesItem['products_quantity'];
                $allocated_quantity += $warehousesItem['allocated_quantity'];
                $temporary_quantity += $warehousesItem['temporary_quantity'];
                $warehouse_quantity += $warehousesItem['warehouse_quantity'];
                $ordered_quantity += $warehousesItem['ordered_quantity'];
            }

            $showRow = false;
            if ($empty_row == 1) {
                $showRow = true;
            } elseif ($warehousesItem['products_quantity'] > 0 || $warehousesItem['allocated_quantity'] > 0 || $warehousesItem['temporary_quantity'] > 0 || $warehousesItem['warehouse_quantity'] > 0 || $warehousesItem['ordered_quantity'] > 0) {
                $showRow = true;
            }

            if ($showRow) {
                $warehouses[] = $warehousesItem;
                if ($master == 1) {
                    foreach ($supplier as $sId => $sName) {
                        $warehousesStockQuery = \common\models\WarehousesProducts::find()
                            ->select(['sum(warehouse_stock_quantity) as warehouse_stock_quantity', 'sum(ordered_stock_quantity) as ordered_stock_quantity'])
                            ->where(['warehouse_id' => $warehousesRecord['warehouse_id']]);
                        $warehousesStockQuery->andWhere(['suppliers_id' => (int) $sId]);
                        if (strpos($prid, '{') !== false) {
                            $warehousesStockQuery->andWhere(['products_id' => tep_db_input($prid)]);
                        } else {
                            $warehousesStockQuery->andWhere(['products_id' => (int) $prid]);
                        }
                        $warehousesStock = $warehousesStockQuery->asArray()->one();

                        if (!empty($spdata[$sId]['sp_status'])) {
                          $sName = '&nbsp;&nbsp;' . $sName;
                        } else {
                          $sName = '<div class="dis_module">'. '&nbsp;&nbsp;' . $sName . '</div>';
                        }

                        $warehousesItem = [
                            'id' => $warehousesRecord['warehouse_id'],
                            'name' => $sName,
                            'sort_order' => $warehousesRecord['sort_order'],
                            'allocated_quantity' => (isset($productAllocatedArray[$warehousesRecord['warehouse_id']][$sId]) ? $productAllocatedArray[$warehousesRecord['warehouse_id']][$sId] : 0),
                            'temporary_quantity' => (isset($productAllocatedTemporaryArray[$warehousesRecord['warehouse_id']][$sId]) ? $productAllocatedTemporaryArray[$warehousesRecord['warehouse_id']][$sId] : 0),
                            'warehouse_quantity' => (int)$warehousesStock['warehouse_stock_quantity'],
                            'ordered_quantity' => (int)$warehousesStock['ordered_stock_quantity'],
                            'master' => 0,
                            'actions' => ' <a href="' . Yii::$app->urlManager->createUrl(['categories/update-stock', 'products_id' => $prid, 'suppliers_id' => $sId, 'warehouse_id' => $warehousesRecord['warehouse_id']]) . '" class="right-link" data-class="update-stock-popup">' . TEXT_UPDATE_STOCK . '</a>',
                        ];
                        $warehousesItem['products_quantity'] = $warehousesItem['warehouse_quantity'] - ($warehousesItem['allocated_quantity'] + $warehousesItem['temporary_quantity']);

                        if ($empty_row == 1) {
                            $warehouses[] = $warehousesItem;
                        } elseif ($warehousesItem['products_quantity'] > 0 || $warehousesItem['allocated_quantity'] > 0 || $warehousesItem['temporary_quantity'] > 0 || $warehousesItem['warehouse_quantity'] > 0 || $warehousesItem['ordered_quantity'] > 0) {
                            $warehouses[] = $warehousesItem;
                        }
                    }
                }
            }

        }
        // Total
        $qrapStart = '<b>';
        $qrapEnd = '</b>';
        $warehouses[] = [
            'id' => 0,
            'name' => $qrapStart . TEXT_TOTAL . $qrapEnd,
            'sort_order' => 777777777,
            'products_quantity' => $qrapStart . (int)$products_quantity . $qrapEnd,
            'allocated_quantity' => $qrapStart . (int)$allocated_quantity . $qrapEnd,
            'temporary_quantity' => $qrapStart . (int)$temporary_quantity . $qrapEnd,
            'warehouse_quantity' => $qrapStart . (int)$warehouse_quantity . $qrapEnd,
            'ordered_quantity' => $qrapStart . (int)$ordered_quantity . $qrapEnd,
            'master' => $master,
            'actions' => '',
        ];

        if ($suppliers_id == 0 && count ($supplier) <= 1) {
            if (count($supplier) == 1) {
                $suppliers_id = key($supplier);
            } else {
                $suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
            }
        }
        return $this->renderAjax('warehouses-stock', ['warehouses' => $warehouses, 'prid' => $prid, 'suppliers_id' => $suppliers_id, 'empty_row' => $empty_row, 'master' => $master]);
    }

    public function actionWarehousesRelocate() {
        \common\helpers\Translation::init('admin/categories');

        if (Yii::$app->request->isPost) {
            $prid = Yii::$app->request->post('prid');
            $suppliers_id = (int)Yii::$app->request->post('suppliers_id', 0);
            if (count(\common\helpers\Product::getChildArray($prid)) == 0) {
                $from_warehouse = (int)Yii::$app->request->post('from_warehouse');
                $to_warehouse = (int)Yii::$app->request->post('to_warehouse');

                $quantity_update = 0;
                $updateData = [];

                $existLocations = \common\models\WarehousesProducts::find()
                    ->select(['location_id', 'layers_id', 'batch_id', 'warehouse_stock_quantity'])
                    ->where(['warehouse_id' => $from_warehouse, 'suppliers_id' => $suppliers_id, 'products_id' => $prid])
                    ->orderBy(['location_id' => SORT_ASC])
                    ->asArray()
                    ->all();
                foreach ($existLocations as $location) {
                    if (isset($_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']]) &&
                        $_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']] > 0) {
                        $updateData[] = [
                            'quantity' => (int) $_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']],
                            'prefix' => '-',
                            'location' => $location['location_id'],
                            'layer' => $location['layers_id'],
                            'batch' => $location['batch_id'],
                            'warehouse_id' => $from_warehouse,
                        ];
                        $quantity_update += (int) $_POST['stock_minus_qty_' . $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id']];
                    }
                }

                if (strpos($prid, '{') !== false) {
                    $check_data = tep_db_fetch_array(tep_db_query("select products_quantity from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($prid) . "'"));
                    if (!$check_data) {
                        tep_db_query("insert into " . TABLE_INVENTORY . " set inventory_id = '', products_id = '" . tep_db_input($prid) . "', prid = '" . (int) \common\helpers\Inventory::get_prid($prid) . "'");
                    }
                }
                if ($quantity_update > 0 /* && $from_warehouse != $to_warehouse */) {

                    $location_id = 0;
                    $locationIds = Yii::$app->request->post('box_location');
                    if (is_array($locationIds)) {
                        foreach ($locationIds as $id) {
                            if ($id > 0) {
                                $location_id = $id;
                            }
                        }
                    }
                    $expiry_date = Yii::$app->request->post('expiry_date', '');
                    $expiry_date = \common\helpers\Date::prepareInputDate($expiry_date);
                    $layers_id = \common\helpers\Warehouses::getWarehousesProductsLayersIDbyExpiryDate($expiry_date);
                    $batch_name = Yii::$app->request->post('batch_name', '');
                    $batch_id = \common\helpers\Warehouses::getWarehousesProductsBatchIDbyBatchName($batch_name);

                    $updateData[] = [
                        'quantity' => $quantity_update,
                        'prefix' => '+',
                        'location' => $location_id,
                        'layer' => $layers_id,
                        'batch' => $batch_id,
                        'warehouse_id' => $to_warehouse,
                    ];
                    //$quantity_update = min($quantity_update, \common\helpers\Warehouses::get_products_quantity($prid, $from_warehouse, $suppliers_id));

                    global $login_id;
                    $comments = sprintf(TEXT_MANUAL_STOCK_RELOCATE, \common\helpers\Warehouses::get_warehouse_name($from_warehouse), \common\helpers\Warehouses::get_warehouse_name($to_warehouse));
                    $parameters = [
                        'admin_id' => $login_id,
                        'comments' => $comments
                    ];

                    foreach ($updateData as $updateItem) {
                        $parameters['layers_id'] = $updateItem['layer'];
                        $parameters['batch_id'] = $updateItem['batch'];
                        \common\helpers\Warehouses::update_products_quantity($prid, $updateItem['warehouse_id'], $updateItem['quantity'], $updateItem['prefix'], $suppliers_id, $updateItem['location'], $parameters);
                    }
                }
            }
        } else {
            $prid = Yii::$app->request->get('prid');
            $suppliers_id = (int)Yii::$app->request->get('suppliers_id', 0);
        }

        $productAllocatedArray = [];
        foreach (\common\helpers\Product::getAllocatedArray($prid) as $productAllocatedRecord) {
            $tmpSupplierId = ($suppliers_id > 0) ? $productAllocatedRecord['suppliers_id'] : 0;
            if (!isset($productAllocatedArray[$productAllocatedRecord['warehouse_id']][$tmpSupplierId])) {
                $productAllocatedArray[$productAllocatedRecord['warehouse_id']][$tmpSupplierId] = 0;
            }
            $productAllocatedArray[$productAllocatedRecord['warehouse_id']][$tmpSupplierId] += ($productAllocatedRecord['allocate_received'] - $productAllocatedRecord['allocate_dispatched']);
        }

        $productAllocatedTemporaryArray = [];
        foreach (\common\helpers\Product::getAllocatedTemporaryArray($prid) as $productAllocatedTemporaryRecord) {
            $tmpSupplierId = ($suppliers_id > 0) ? $productAllocatedTemporaryRecord['suppliers_id'] : 0;
            if (!isset($productAllocatedTemporaryArray[$productAllocatedTemporaryRecord['warehouse_id']][$tmpSupplierId])) {
                $productAllocatedTemporaryArray[$productAllocatedTemporaryRecord['warehouse_id']][$tmpSupplierId] = 0;
            }
            $productAllocatedTemporaryArray[$productAllocatedTemporaryRecord['warehouse_id']][$tmpSupplierId] += $productAllocatedTemporaryRecord['temporary_stock_quantity'];
        }

        $warehouses = [];
        $products_quantity = $allocated_quantity = $temporary_quantity = $warehouse_quantity = $ordered_quantity = 0;

        $warehousesQuery = \common\models\Warehouses::find()
                ->select(['warehouse_id', 'warehouse_name', 'sort_order'])
                ->where(['status' => 1])
                ->orderBy(['sort_order' => SORT_ASC, 'warehouse_name' => SORT_ASC])
                ->asArray()
                ->all();
        foreach ($warehousesQuery as $warehousesRecord) {

            $warehousesStockQuery = \common\models\WarehousesProducts::find()
                    ->select(['sum(warehouse_stock_quantity) as warehouse_stock_quantity', 'sum(ordered_stock_quantity) as ordered_stock_quantity'])
                    ->where(['warehouse_id' => $warehousesRecord['warehouse_id']]);
            if ($suppliers_id > 0) {
                $warehousesStockQuery->andWhere(['suppliers_id' => (int) $suppliers_id]);
            }
            if (strpos($prid, '{') !== false) {
                $warehousesStockQuery->andWhere(['products_id' => tep_db_input($prid)]);
            } else {
                $warehousesStockQuery->andWhere(['products_id' => (int) $prid]);
            }
            $warehousesStock = $warehousesStockQuery->asArray()->one();

            $warehousesItem = [
                'id' => $warehousesRecord['warehouse_id'],
                'name' => $warehousesRecord['warehouse_name'],
                'sort_order' => $warehousesRecord['sort_order'],
                'allocated_quantity' => (isset($productAllocatedArray[$warehousesRecord['warehouse_id']][$suppliers_id]) ? $productAllocatedArray[$warehousesRecord['warehouse_id']][$suppliers_id] : 0),
                'temporary_quantity' => (isset($productAllocatedTemporaryArray[$warehousesRecord['warehouse_id']][$suppliers_id]) ? $productAllocatedTemporaryArray[$warehousesRecord['warehouse_id']][$suppliers_id] : 0),
                'warehouse_quantity' => (int)$warehousesStock['warehouse_stock_quantity'],
                'ordered_quantity' => (int)$warehousesStock['ordered_stock_quantity'],
            ];
            $warehousesItem['products_quantity'] = $warehousesItem['warehouse_quantity'] - ($warehousesItem['allocated_quantity'] + $warehousesItem['temporary_quantity']);

            $products_quantity += $warehousesItem['products_quantity'];
            $allocated_quantity += $warehousesItem['allocated_quantity'];
            $temporary_quantity += $warehousesItem['temporary_quantity'];
            $warehouse_quantity += $warehousesItem['warehouse_quantity'];
            $ordered_quantity += $warehousesItem['ordered_quantity'];

            $warehouses[] = $warehousesItem;
        }
        // Total
        $warehouses[] = [
            'id' => 0,
            'name' => TEXT_TOTAL,
            'sort_order' => 777777777,
            'products_quantity' => (int)$products_quantity,
            'allocated_quantity' => (int)$allocated_quantity,
            'temporary_quantity' => (int)$temporary_quantity,
            'warehouse_quantity' => (int)$warehouse_quantity,
            'ordered_quantity' => (int)$ordered_quantity,
        ];

        $supp_id = $suppliers_id;
        if ($supp_id == 0) {
            $sp = \common\helpers\Suppliers::getSuppliersToUprid($prid);
            if ($sp){
                foreach($sp as $_sp){
                    $supp_id = $_sp->suppliers_id;
                    break;
                }
            }
            unset($sp);
        }
        return $this->renderAjax('warehouses-relocate', ['warehouses' => $warehouses, 'prid' => $prid, 'suppliers_id' => $suppliers_id, 'supp_id' => $supp_id]);
    }

    public function actionSuppliersStock() {
        \common\helpers\Translation::init('admin/categories');

        if (Yii::$app->request->isPost) {
            $prid = Yii::$app->request->post('prid');
            $warehouse_id = (int) Yii::$app->request->post('warehouse_id', 0);
            $empty_row = (int) Yii::$app->request->post('empty_row');
            if (count(\common\helpers\Product::getChildArray($prid)) == 0) {
                $quantity_update = Yii::$app->request->post('quantity_update', []);
                $quantity_prefix = Yii::$app->request->post('quantity_prefix', []);
                $stock_comments = Yii::$app->request->post('stock_comments', []);
                foreach ($quantity_update as $suppliers_id => $quantity) {
                    if ($quantity > 0) {
                        $prefix = ($quantity_prefix[$suppliers_id] == '-' ? '-' : '+');
                        $comments = $stock_comments[$suppliers_id];
                        if (strpos($prid, '{') !== false) {
                            $check_data = tep_db_fetch_array(tep_db_query("select products_quantity from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($prid) . "'"));
                            if (!$check_data) {
                                tep_db_query("insert into " . TABLE_INVENTORY . " set inventory_id = '', products_id = '" . tep_db_input($prid) . "', prid = '" . (int) \common\helpers\Inventory::get_prid($prid) . "'");
                            }
                        }
                        global $login_id;
                        //\common\helpers\Product::log_stock_history_before_update($prid, $quantity, $prefix, ['warehouse_id' => $warehouse_id, 'suppliers_id' => $suppliers_id, 'comments' => TEXT_MANUALL_STOCK_UPDATE . (trim($comments) != '' ? ': ' . $comments : ''), 'admin_id' => $login_id]);
                        $parameters = [
                            'admin_id' => $login_id,
                            'comments' => (TEXT_MANUALL_STOCK_UPDATE . (trim($comments) != '' ? ': ' . $comments : ''))
                        ];
                        \common\helpers\Warehouses::update_products_quantity($prid, $warehouse_id, $quantity, $prefix, $suppliers_id, 0, $parameters);
                    }
                }
            }
        } else {
            $prid = Yii::$app->request->get('prid');
            $warehouse_id = (int) Yii::$app->request->get('warehouse_id', 0);
            $empty_row = (int) Yii::$app->request->get('empty_row');
        }

        $productAllocatedArray = [];
        foreach (\common\helpers\Product::getAllocatedArray($prid) as $productAllocatedRecord) {
            $productAllocatedArray[$productAllocatedRecord['suppliers_id']][$productAllocatedRecord['warehouse_id']] += ($productAllocatedRecord['allocate_received'] - $productAllocatedRecord['allocate_dispatched']);
            if ($warehouse_id == 0) {
                $productAllocatedArray[$productAllocatedRecord['suppliers_id']][$warehouse_id] += ($productAllocatedRecord['allocate_received'] - $productAllocatedRecord['allocate_dispatched']);
            }
        }

        $productAllocatedTemporaryArray = [];
        foreach (\common\helpers\Product::getAllocatedTemporaryArray($prid) as $productAllocatedTemporaryRecord) {
            $productAllocatedTemporaryArray[$productAllocatedTemporaryRecord['suppliers_id']][$productAllocatedTemporaryRecord['warehouse_id']] += $productAllocatedTemporaryRecord['temporary_stock_quantity'];
            if ($warehouse_id == 0) {
                $productAllocatedTemporaryArray[$productAllocatedTemporaryRecord['suppliers_id']][$warehouse_id] += $productAllocatedTemporaryRecord['temporary_stock_quantity'];
            }
        }

        if ($warehouse_id == 0 && \common\helpers\Warehouses::get_warehouses_count() > 1) {
            $master = 1;
        } else {
            $master = 0;
        }

        $suppliers = [];
        $products_quantity = $allocated_quantity = $temporary_quantity = $warehouse_quantity = $ordered_quantity = 0;

        $spq = \common\models\SuppliersProducts::find()
            ->addSelect('status as sp_status, suppliers_id')
            ;
        if (strpos($prid, '{') !== false) {
            $spq->andWhere(['uprid' => tep_db_input($prid), 'products_id' => (int) $prid]);
        } else {
            $spq->andWhere(['products_id' => (int) $prid]);
        }
        $spdata = $spq->indexBy('suppliers_id')->asArray()->all();

        $suppliersQuery = \common\models\Suppliers::find()
                ->select(['suppliers_id', 'suppliers_name', 'sort_order'])
                ->where(['status' => 1])
                ->orderBy(['sort_order' => SORT_ASC, 'suppliers_name' => SORT_ASC])
                ->asArray()
                ->all();
        foreach ($suppliersQuery as $suppliersRecord) {
            $suppliersStockQuery = \common\models\WarehousesProducts::find()
                    ->select(['sum(warehouse_stock_quantity) as warehouse_stock_quantity', 'sum(ordered_stock_quantity) as ordered_stock_quantity'])
                    ->where(['suppliers_id' => $suppliersRecord['suppliers_id']]);
            if ($warehouse_id > 0) {
                $suppliersStockQuery->andWhere(['warehouse_id' => (int) $warehouse_id]);
            }
            if (strpos($prid, '{') !== false) {
                $suppliersStockQuery->andWhere(['products_id' => tep_db_input($prid)]);
            } else {
                $suppliersStockQuery->andWhere(['products_id' => (int) $prid]);
            }
            $suppliersStock = $suppliersStockQuery->asArray()->one();

            if (!empty($spdata[$suppliersRecord['suppliers_id']]['sp_status'])) {
              $tmpName = $suppliersRecord['suppliers_name'];
            } else {
              $tmpName = '<div class="dis_module">'. $suppliersRecord['suppliers_name'] . '</div>';
            }
            $suppliersItem = [
                'id' => $suppliersRecord['suppliers_id'],
                'name' => $tmpName,
                'sort_order' => $suppliersRecord['sort_order'],
                'allocated_quantity' => (isset($productAllocatedArray[$suppliersRecord['suppliers_id']][$warehouse_id]) ? $productAllocatedArray[$suppliersRecord['suppliers_id']][$warehouse_id] : 0),
                'temporary_quantity' => (isset($productAllocatedTemporaryArray[$suppliersRecord['suppliers_id']][$warehouse_id]) ? $productAllocatedTemporaryArray[$suppliersRecord['suppliers_id']][$warehouse_id] : 0),
                'warehouse_quantity' => (int)$suppliersStock['warehouse_stock_quantity'],
                'ordered_quantity' => (int)$suppliersStock['ordered_stock_quantity'],
                'master' => $master,
                'actions' => '',
            ];
            $suppliersItem['products_quantity'] = $suppliersItem['warehouse_quantity'] - ($suppliersItem['allocated_quantity'] + $suppliersItem['temporary_quantity']);

            $products_quantity += $suppliersItem['products_quantity'];
            $allocated_quantity += $suppliersItem['allocated_quantity'];
            $temporary_quantity += $suppliersItem['temporary_quantity'];
            $warehouse_quantity += $suppliersItem['warehouse_quantity'];
            $ordered_quantity += $suppliersItem['ordered_quantity'];

            $showRow = false;
            if ($empty_row == 1) {
                $showRow = true;
            } elseif ($suppliersItem['products_quantity'] > 0 || $suppliersItem['allocated_quantity'] > 0 || $suppliersItem['temporary_quantity'] || $suppliersItem['warehouse_quantity'] || $suppliersItem['ordered_quantity']) {
                $showRow = true;
            }

            if ($showRow) {
                $suppliers[] = $suppliersItem;
                if ($master == 1) {
                    $warehouses = \common\helpers\Warehouses::get_warehouses();
                    foreach ($warehouses as $whItem) {
                        $wId = $whItem['id'];
                        $wName = $whItem['text'];
                        $suppliersStockQuery = \common\models\WarehousesProducts::find()
                            ->select(['sum(warehouse_stock_quantity) as warehouse_stock_quantity', 'sum(ordered_stock_quantity) as ordered_stock_quantity'])
                            ->where(['suppliers_id' => $suppliersRecord['suppliers_id']]);
                        $suppliersStockQuery->andWhere(['warehouse_id' => (int) $wId]);
                        if (strpos($prid, '{') !== false) {
                            $suppliersStockQuery->andWhere(['products_id' => tep_db_input($prid)]);
                        } else {
                            $suppliersStockQuery->andWhere(['products_id' => (int) $prid]);
                        }
                        $suppliersStock = $suppliersStockQuery->asArray()->one();

                        $suppliersItem = [
                            'id' => $suppliersRecord['suppliers_id'],
                            'name' => '&nbsp;&nbsp;' . $wName,
                            'sort_order' => $suppliersRecord['sort_order'],
                            'allocated_quantity' => (isset($productAllocatedArray[$suppliersRecord['suppliers_id']][$warehouse_id]) ? $productAllocatedArray[$suppliersRecord['suppliers_id']][$warehouse_id] : 0),
                            'temporary_quantity' => (isset($productAllocatedTemporaryArray[$suppliersRecord['suppliers_id']][$warehouse_id]) ? $productAllocatedTemporaryArray[$suppliersRecord['suppliers_id']][$warehouse_id] : 0),
                            'warehouse_quantity' => (int)$suppliersStock['warehouse_stock_quantity'],
                            'ordered_quantity' => (int)$suppliersStock['ordered_stock_quantity'],
                            'master' => 0,
                            'actions' => ' <a href="' . Yii::$app->urlManager->createUrl(['categories/update-stock', 'products_id' => $prid, 'suppliers_id' => $suppliersRecord['suppliers_id'], 'warehouse_id' => $wId]) . '" class="right-link" data-class="update-stock-popup">' . TEXT_UPDATE_STOCK . '</a>',
                        ];
                        $suppliersItem['products_quantity'] = $suppliersItem['warehouse_quantity'] - ($suppliersItem['allocated_quantity'] + $suppliersItem['temporary_quantity']);

                        if ($empty_row == 1) {
                            $suppliers[] = $suppliersItem;
                        } elseif ($suppliersItem['products_quantity'] > 0 || $suppliersItem['allocated_quantity'] > 0 || $suppliersItem['temporary_quantity'] || $suppliersItem['warehouse_quantity'] || $suppliersItem['ordered_quantity']) {
                            $suppliers[] = $suppliersItem;
                        }


                    }
                }
            }
        }
        // Total
        $qrapStart = '<b>';
        $qrapEnd = '</b>';
        $suppliers[] = [
            'id' => 0,
            'name' => $qrapStart . TEXT_TOTAL . $qrapEnd,
            'sort_order' => 777777777,
            'products_quantity' => $qrapStart . (int)$products_quantity . $qrapEnd,
            'allocated_quantity' => $qrapStart . (int)$allocated_quantity . $qrapEnd,
            'temporary_quantity' => $qrapStart . (int)$temporary_quantity . $qrapEnd,
            'warehouse_quantity' => $qrapStart . (int)$warehouse_quantity . $qrapEnd,
            'ordered_quantity' => $qrapStart . (int)$ordered_quantity . $qrapEnd,
            'master' => $master,
            'actions' => '',
        ];
        return $this->renderAjax('suppliers-stock', ['suppliers' => $suppliers, 'prid' => $prid, 'warehouse_id' => $warehouse_id, 'empty_row' => $empty_row, 'master' => $master]);
    }

    public function actionProductAssets() {
        \common\helpers\Translation::init('admin/categories');

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
            return $ext::adminProductPopup();
        }
    }

    private function searchCategoryTree($searchTerm, $platform_id = false) {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $filter_by_platform = array();
        if (is_array($platform_id)) {
            $filter_by_platform = $platform_id;
        } else {
            if (!$platform_param = Yii::$app->request->get('platform', false)) {
                $formFilter = Yii::$app->request->get('filter', '');
                $output = [];
                parse_str($formFilter, $output);
                if (isset($output['platform']) && is_array($output['platform'])) {
                    $platform_param = $output['platform'];
                }
            }

            if (isset($platform_param) && is_array($platform_param)) {
                foreach ($platform_param as $_platform_id) {
                    if ((int) $_platform_id > 0) {
                      $filter_by_platform[] = (int) $_platform_id;
                    }
                }
            }
        }

        $platform_filter_categories = '';
        if (count($filter_by_platform) > 0) {
            $platform_filter_categories .= ' and c.categories_id IN (SELECT categories_id FROM ' . TABLE_PLATFORMS_CATEGORIES . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
        }

        $categories_query = tep_db_query("select distinct c.categories_level, c.categories_id as id, c.parent_id, c.categories_left,  c.categories_status, cd.categories_name  as text from  " . TABLE_CATEGORIES . " c1 join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on c1.categories_id=cd1.categories_id and cd1.language_id='" . (int) $languages_id . "' join " . TABLE_CATEGORIES . "  c on c.categories_left<=c1.categories_left and c.categories_right>=c1.categories_right join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id=cd.categories_id and  cd.language_id='" . (int) $languages_id . "' and cd1.categories_name like '%" . $searchTerm . "%' {$platform_filter_categories} order by c.categories_left, c.sort_order, cd.categories_name");

        $categories_by_level = [];
        while ($categories = tep_db_fetch_array($categories_query)) {
          $categories['child'] = array();
          $categories_by_level[$categories['categories_level']][$categories['id']] = $categories;
        }

        $categoriesTree = self::buildTree($categories_by_level);
        return $categoriesTree;
    }

    public function actionCategoryfilter() {
        $this->layout = false;

        $categorysearch = trim(tep_db_input(tep_db_prepare_input(Yii::$app->request->post('categorysearch', ''))));
        $collapsed = (bool)Yii::$app->request->post('collapsed', $this->defaultCollapsed);

        if ($categorysearch == '') {
          $this->view->categoriesTree = $this->getCategoryTree();
          $collapsed = $this->defaultCollapsed;
          $this->view->categoriesClosedTree = array_map('intval', explode('|', \Yii::$app->session->get('closed_data')));
        } else {
          $this->view->categoriesTree = $this->searchCategoryTree($categorysearch);
          $this->view->categoriesClosedTree = [];
          $collapsed = false; // always expanded - as unclear what's search result
        }
        $categories_id = (int)Yii::$app->request->get('category_id', 0);
        if ($categories_id>0) {
          $this->view->categoriesOpenedTree = \common\helpers\Categories::getCategoryParentsIds($categories_id);
        } else {
          $this->view->categoriesOpenedTree = [];
        }

        return $this->render('cat_main_box', ['directOutput' => true, 'collapsed' => $collapsed]);

    }

    public function actionEasyView(){
        $this->layout = false;
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductEasyView', 'allowed')) {
            return $ext::adminActionEasyView();
        }
    }

    public function actionListingAttach()
    {
        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;
        $this->view->usePopupMode = true;

        $product_id = Yii::$app->request->get('product_id', 0);
        $productModel = \common\models\Products::findOne($product_id);
        if ( $productModel && !$productModel->parent_products_id ){
            if ( Yii::$app->request->isPost ){
                $parent_product_id = intval(Yii::$app->request->post('parent_product_id', 0));

                \common\helpers\Product::childAttach($product_id, $parent_product_id);
                if ( Yii::$app->request->post('mark_parent_as_master', 0) ){
                    if ( $parentModel = \common\models\Products::findOne($parent_product_id) ){
                        $parentModel->is_listing_product = 0;
                        $parentModel->save(false);
                    }
                }
                return 'ok';
            }
            return $this->render('popup-listing-attach.tpl',[
                'product_id' => $product_id,
                'product_name' => \common\helpers\Product::get_backend_products_name($product_id),
            ]);
        }
    }

    public function actionListingDetach()
    {
        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;
        $this->view->usePopupMode = true;

        $product_id = Yii::$app->request->get('product_id', 0);
        $productModel = \common\models\Products::findOne($product_id);
        if ( $productModel && $productModel->parent_products_id ){
            if ( Yii::$app->request->isPost ){
                \common\helpers\Product::childDetach($product_id);
                return 'ok';
            }
            return $this->render('popup-listing-detach.tpl',[
                'product_id' => $product_id,
                'product_name' => \common\helpers\Product::get_backend_products_name($product_id),
                'parent_product_name' => \common\helpers\Product::get_backend_products_name($productModel->parent_products_id),
            ]);
        }
    }

    public function actionSold(){
        \common\helpers\Translation::init('admin/categories');
        $prid = Yii::$app->request->get('pID');
        $sold = [];
        $op = [];
        $product = null;
        if ($prid){
            $product = (new \yii\db\Query)->select('products_date_added')->from(TABLE_PRODUCTS)->where('products_id=:prid', [':prid' => (int)$prid])->one();
            $op = (new \yii\db\Query)->select('products_name')->from(TABLE_ORDERS_PRODUCTS)->where('uprid=:prid', [':prid' => $prid])->limit(1)->one();
            $sold[] = \backend\models\ProductSold::fromPeriodSold($this, 'sold', $prid, 'DATE_SUB(CURDATE(), INTERVAL 7 DAY)', 'CURDATE()',  TEXT_LAST_WEEK);
            $sold[] = \backend\models\ProductSold::fromPeriodSold($this, 'sold', $prid, 'DATE_SUB(CURDATE(), INTERVAL 14 DAY)', 'DATE_SUB(CURDATE(), INTERVAL 7 day)',  TEXT_WEEK_BEFORE);
            //$sold[] = '&nbsp;';
            $sold[] = \backend\models\ProductSold::fromPeriodSold($this, 'sold', $prid, 'DATE_SUB(CURDATE(), INTERVAL 1 MONTH)', 'CURDATE()',  TEXT_LAST_MONTH);
            for($i = 2; $i< 7; $i++){
                $j = $i-1;
                $sold[] = \backend\models\ProductSold::fromPeriodSold($this, 'sold', $prid, "DATE_SUB(CURDATE(), INTERVAL {$i} MONTH)", "DATE_SUB(CURDATE(), INTERVAL {$j} MONTH)",  TEXT_MONTH_BEFORE);
            }
        }
        return $this->renderAjax('sold-view', ['sold' => $sold, 'name' => $op['products_name'], 'date_added' => $product['products_date_added'] ]);
    }

    public function actionEasySave(){
        if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductEasyView', 'allowed')) {
            return $ext::adminActionEasySave();
        }
    }

    public function actionAllList()
    {
        $languages_id = \Yii::$app->settings->get('languages_id');

        $categories = \common\helpers\Categories::get_category_tree(0,'','','',false,false, 0, false, false, $languages_id);
        $categoriesArr = [];
        foreach ($categories as $category) {
            $category['text'] = trim(str_replace('&nbsp;', ' ', $category['text']));
            unset($category['products']);
            $categoriesArr[] = $category;
        }

        return json_encode($categoriesArr);
    }

    public function actionBrandsList()
    {
        $manufacturers = \common\models\Manufacturers::find()
            ->select([
                'id' => 'manufacturers_id',
                'text' => 'manufacturers_name',
            ])
            ->asArray()
            ->all();

        return json_encode($manufacturers);
    }

    public function actionWarehouseLocation() {
        $this->layout = false;

        $blocks =\common\models\LocationBlocks::find()->asArray()->all();
        $blocksList = [];
        foreach ($blocks as $value) {
            $blocksList[$value['block_id']] = $value['block_name'];
        }

        $warehouse_id = (int)Yii::$app->request->post('warehouse_id');
        $suppliers_id = (int)Yii::$app->request->post('suppliers_id');
        $products_id = (string)Yii::$app->request->post('products_id');

        $prefix = Yii::$app->request->post('prefix');
        if ($prefix == '-') {
            $existLocations = \common\models\WarehousesProducts::find()
                ->select(['location_id', 'layers_id', 'batch_id', 'products_quantity'])
                ->where(['warehouse_id' => $warehouse_id, 'suppliers_id' => $suppliers_id, 'products_id' => $products_id])
                ->orderBy(['location_id' => SORT_ASC, 'layers_id' => SORT_ASC, 'batch_id' => SORT_ASC])
                ->asArray()
                ->all();

            $locationList = [];
            foreach ($existLocations as $location) {
                if ($location['products_quantity'] <= 0) {
                    continue;
                }
                $name = \common\helpers\Warehouses::getLocationPath($location['location_id'], $warehouse_id, $blocksList);
                if (empty($name)) {
                    $name = 'N/A';
                }
                if ($location['layers_id']) {
                    $name .= ', ' . \common\helpers\Translation::getTranslationValue('TEXT_EXPIRY_DATE', 'admin/categories') . ' ' . \common\helpers\Date::date_short(\common\helpers\Warehouses::getExpiryDateByLayersID($location['layers_id'])); 
                }
                if ($location['batch_id']) {
                    $name .= ', ' . TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME . ' ' . \common\helpers\Warehouses::getBatchNameByBatchID($location['batch_id']); 
                }
                $locationList[] = [
                    'id' => $location['location_id'] . '_' . $location['layers_id'] . '_' . $location['batch_id'],
                    'name' => $name,
                    'qty' => $location['products_quantity'],
                ];
            }
            return $this->render('warehouse-location-minus', ['locationList' => $locationList]);
        }

        $selectedLocationId = '';
        $sublocation = [];

        $existLocations = \common\models\WarehousesProducts::find()
                ->select('location_id')
                ->where(['warehouse_id' => $warehouse_id, 'suppliers_id' => $suppliers_id, 'products_id' => $products_id])
                ->orderBy(['location_id' => SORT_DESC])
                ->asArray()
                ->one();
        if (isset($existLocations['location_id']) && $existLocations['location_id'] > 0) {
            $selectedLocationId = $existLocations['location_id'];
            //build back tree
            $backBuild = [];

            $loc = \common\models\Locations::find()->where(['warehouse_id' => $warehouse_id, 'location_id' => $existLocations['location_id']])->asArray()->one();
            if (is_array($loc)) {
                $backBuild[] = $loc;
                while(isset($loc['parrent_id']) && $loc['parrent_id'] > 0) {
                    $loc = \common\models\Locations::find()->where(['warehouse_id' => $warehouse_id, 'location_id' => $loc['parrent_id']])->asArray()->one();
                    if (is_array($loc)) {
                        $backBuild[] = $loc;
                        $selectedLocationId = $loc['location_id'];
                    }
                }
            }

            $backBuild = array_reverse($backBuild);
            if (count($backBuild) > 1) {
                unset($backBuild[0]);
                foreach ($backBuild as $backKey =>$backItem) {

                    $locationId = '';
                    $locationList = [];

                    $locations = \common\models\Locations::find()->where(['warehouse_id' => $warehouse_id, 'parrent_id' => $backItem['parrent_id']])->orderBy('sort_order')->asArray()->all();
                    if (is_array($locations) && count($locations) > 0) {
                        $locationList[''] = PULL_DOWN_DEFAULT;
                        foreach ($locations as $location) {
                            $locationList[$location['location_id']] = $blocksList[$location['block_id']] . ': ' . $location['location_name'];
                        }
                        $locationId = $backItem['location_id'];
                    }

                    $sublocation[] = [
                        'locationList' => $locationList,
                        'location_id' => $locationId
                    ];
                }
            }
        }

        $locations = \common\models\Locations::find()->where(['warehouse_id' => $warehouse_id, 'parrent_id' => 0])->orderBy('sort_order')->asArray()->all();
        if (is_array($locations) && count($locations) > 0) {
            $locationList = [];
            $locationList[''] = PULL_DOWN_DEFAULT;
            foreach ($locations as $location) {
                $locationList[$location['location_id']] = $blocksList[$location['block_id']] . ': ' . $location['location_name'];
            }

            return $this->render('warehouse-location', ['locationList' => $locationList, 'location_id' => $selectedLocationId, 'sublocation' => $sublocation, 'warehouse_id' => $warehouse_id]);
        }
    }

    public function actionWarehouseLocationChild() {
        $this->layout = false;
        $warehouse_id = (int)Yii::$app->request->post('warehouse_id');
        $location_id = (int)Yii::$app->request->post('location_id');
        if ($location_id == 0) {
            return '';
        }

        $locations = \common\models\Locations::find()->where(['warehouse_id' => $warehouse_id, 'parrent_id' => $location_id])->orderBy('sort_order')->asArray()->all();
        if (is_array($locations) && count($locations) > 0) {

            $blocks =\common\models\LocationBlocks::find()->asArray()->all();
            $blocksList = [];
            foreach ($blocks as $value) {
                $blocksList[$value['block_id']] = $value['block_name'];
            }

            $locationList = [];
            $locationList[''] = PULL_DOWN_DEFAULT;
            foreach ($locations as $location) {
                $locationList[$location['location_id']] = $blocksList[$location['block_id']] . ': ' . $location['location_name'];
            }

            return $this->render('warehouse-location-child', ['locationList' => $locationList, 'warehouse_id' => $warehouse_id]);
        }
    }

    public function actionUpdateStock() {
        $this->layout = false;
        \common\helpers\Translation::init('admin/categories');

        $products_id = (string)Yii::$app->request->get('products_id');
        $suppliers_id = (int)Yii::$app->request->get('suppliers_id', 0);
        $warehouse_id = (int)Yii::$app->request->get('warehouse_id', 0);

        if (strpos($products_id, '{') !== false) {
            $action = 'inventory_quantity_update';
        } else {
            $action = 'products_quantity_update';
        }

        if ($suppliers_id == 0) {
            $supplier = \common\helpers\Suppliers::getSuppliersList($products_id);
            if (count($supplier) == 1) {
                $suppliers_id = key($supplier);
            } else {
                $suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
            }
        }

        if ($warehouse_id == 0) {
            $warehouse_id = \common\helpers\Warehouses::get_default_warehouse();
        }

        return $this->render('update-product-stock', [
            'products_id' => $products_id,
            'warehouse_id' => $warehouse_id,
            'suppliers_id' => $suppliers_id,
            'action' => $action,
        ]);
    }

    public function actionOrderReallocate()
    {
        \common\helpers\Translation::init('admin/categories');
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual(Yii::$app->request->get('prid'));
        if (Yii::$app->request->isPost) {
            $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual(Yii::$app->request->post('prid'));
        }
        \common\helpers\Product::isValidAllocated($uProductId);
        $warehouseNameList = [];
        foreach (\common\models\Warehouses::find()->asArray(true)->all() as $warehouseRecord) {
            $warehouseNameList[$warehouseRecord['warehouse_id']] = $warehouseRecord['warehouse_name'];
        }
        unset($warehouseRecord);
        $supplierNameList = [];
        foreach (\common\models\Suppliers::find()->asArray(true)->all() as $supplierRecord) {
            $supplierNameList[$supplierRecord['suppliers_id']] = $supplierRecord['suppliers_name'];
        }
        unset($supplierRecord);
        $locationBlockList = [];
        foreach (\common\models\LocationBlocks::find()->asArray(true)->all() as $locationBlockRecord) {
            $locationBlockList[$locationBlockRecord['block_id']] = $locationBlockRecord['block_name'];
        }
        unset($locationBlockRecord);
        $productAllocatedTemporaryArray = [];
        foreach (\common\helpers\Product::getAllocatedTemporaryArray($uProductId) as $productAllocatedTemporaryRecord) {
            $productAllocatedTemporaryArray[$productAllocatedTemporaryRecord['warehouse_id']][$productAllocatedTemporaryRecord['suppliers_id']][$productAllocatedTemporaryRecord['location_id']][$productAllocatedTemporaryRecord['layers_id']][$productAllocatedTemporaryRecord['batch_id']][] = $productAllocatedTemporaryRecord;
        }
        unset($productAllocatedTemporaryRecord);
        $warehouseProductArray = [];
        foreach (\common\helpers\Warehouses::getProductArray($uProductId) as $warehouseProductRecord) {
            $warehouseId = $warehouseProductRecord['warehouse_id'];
            $supplierId = $warehouseProductRecord['suppliers_id'];
            $locationId = $warehouseProductRecord['location_id'];
            $layersId = $warehouseProductRecord['layers_id'];
            $batchId = $warehouseProductRecord['batch_id'];
            $warehouseAvailable = $warehouseProductRecord['warehouse_stock_quantity'];
            if (isset($productAllocatedTemporaryArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId])) {
                foreach ($productAllocatedTemporaryArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId] as $productAllocatedTemporaryRecord) {
                    $warehouseAvailable -= $productAllocatedTemporaryRecord['temporary_stock_quantity'];
                }
                unset($productAllocatedTemporaryArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId]);
                unset($productAllocatedTemporaryRecord);
            }
            $warehouseProductArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId] = [
                'quantity' => $warehouseAvailable,
                'allocated_real' => 0,
                'allocated_update' => 0
            ];
            unset($warehouseAvailable);
            unset($warehouseId);
            unset($supplierId);
            unset($locationId);
            unset($layersId);
            unset($batchId);
        }
        unset($productAllocatedTemporaryArray);
        unset($warehouseProductRecord);
        $orderProductAllocatedArray = [];
        foreach ($warehouseProductArray as $warehouseId => $supplierArray) {
            foreach ($supplierArray as $supplierId => $locationArray) {
                foreach ($locationArray as $locationId => $layersArray) {
                    foreach ($layersArray as $layersId => $batchArray) {
                        foreach ($batchArray as $batchId => $warehouseProductRecord) {
                            $locationName = trim(\common\helpers\Warehouses::getLocationPath($locationId, $warehouseId, $locationBlockList));
                            if ($layersId) {
                                $locationName .= ', ' . \common\helpers\Translation::getTranslationValue('TEXT_EXPIRY_DATE', 'admin/categories') . ' ' . \common\helpers\Date::date_short(\common\helpers\Warehouses::getExpiryDateByLayersID($layersId)); 
                            }
                            if ($batchId) {
                                $locationName .= ', ' . TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME . ' ' . \common\helpers\Warehouses::getBatchNameByBatchID($batchId); 
                            }
                            $orderProductAllocatedArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId] = [
                                'allocated_real' => 0,
                                'allocated_update' => 0,
                                'warehouseName' => (isset($warehouseNameList[$warehouseId]) ? $warehouseNameList[$warehouseId] : 'N/A'),
                                'supplierName' => (isset($supplierNameList[$supplierId]) ? $supplierNameList[$supplierId] : 'N/A'),
                                'locationName' => (($locationName != '') ? $locationName : 'N/A')
                            ];
                            unset($locationName);
                        }
                    }
                }
                unset($warehouseProductRecord);
                unset($locationId);
            }
            unset($locationArray);
            unset($supplierId);
        }
        unset($supplierArray);
        unset($warehouseId);
        $orderProductIdArray = [];
        foreach (\common\helpers\Product::getAllocatedArray($uProductId) as $orderProductAllocateRecord) {
            $orderProductIdArray[$orderProductAllocateRecord['orders_products_id']] = $orderProductAllocateRecord['orders_products_id'];
        }
        unset($orderProductAllocateRecord);
        if (($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezed()) {
        //skip?
        } else {
            foreach (\common\models\OrdersProducts::find()
                ->select(['orders_products_id', 'uprid'])
                ->andWhere(['products_id' => (int)$uProductId])
                ->andWhere(['IN', 'orders_products_status', [
                    \common\helpers\OrderProduct::OPS_QUOTED,
                    \common\helpers\OrderProduct::OPS_STOCK_DEFICIT,
                    \common\helpers\OrderProduct::OPS_STOCK_ORDERED,
                    \common\helpers\OrderProduct::OPS_RECEIVED
                ]])
                ->asArray(true)->all() as $orderProductData
            ) {
                if (\common\helpers\Inventory::getInventoryId($uProductId) === \common\helpers\Inventory::getInventoryId($orderProductData['uprid'])) {
                    $orderProductIdArray[$orderProductData['orders_products_id']] = $orderProductData['orders_products_id'];
                }
            }
            unset($orderProductData);
        }
        $orderProductArray = [];
        foreach ($orderProductIdArray as $orderProductId) {
            $orderProductRecord = \common\helpers\OrderProduct::getRecord($orderProductId);
            if (!($orderProductRecord instanceof \common\models\OrdersProducts)) {
                continue;
            }
            $orderRecord = \common\helpers\Order::getRecord($orderProductRecord->orders_id);
            if (!($orderRecord instanceof \common\models\Orders)) {
                continue;
            }
            $orderProductArray[$orderProductId] = [
                'orderId' => $orderRecord->orders_id,
                'datePurchased' => $orderRecord->date_purchased,
                'platformId' => $orderRecord->platform_id,
                'model' => $orderProductRecord->products_model,
                'quantity' => \common\helpers\OrderProduct::getQuantityReal($orderProductRecord),
                'allocated_real' => 0,
                'allocated_update' => 0,
                'allocated_parent' => (\common\helpers\OrderProduct::getQuantityReal($orderProductRecord) - (int)$orderProductRecord->qty_rcvd),
                'allocatedArray' => $orderProductAllocatedArray
            ];
            foreach (\common\helpers\OrderProduct::getAllocatedArray($orderProductRecord) as $orderProductAllocateRecord) {
                $productAllocated = ($orderProductAllocateRecord['allocate_received'] - $orderProductAllocateRecord['allocate_dispatched']);
                $orderProductArray[$orderProductId]['quantity'] -= $orderProductAllocateRecord['allocate_dispatched'];
                if ($productAllocated == 0) {
                    continue;
                }
                $orderProductArray[$orderProductId]['allocated_real'] += $productAllocated;
                $orderProductArray[$orderProductId]['allocated_update'] = $orderProductArray[$orderProductId]['allocated_real'];
                $warehouseId = $orderProductAllocateRecord['warehouse_id'];
                $supplierId = $orderProductAllocateRecord['suppliers_id'];
                $locationId = $orderProductAllocateRecord['location_id'];
                $layersId = $orderProductAllocateRecord['layers_id'];
                $batchId = $orderProductAllocateRecord['batch_id'];
                $locationName = trim(\common\helpers\Warehouses::getLocationPath($locationId, $warehouseId, $locationBlockList));
                if ($layersId) {
                    $locationName .= ', ' . \common\helpers\Translation::getTranslationValue('TEXT_EXPIRY_DATE', 'admin/categories') . ' ' . \common\helpers\Date::date_short(\common\helpers\Warehouses::getExpiryDateByLayersID($layersId)); 
                }
                if ($batchId) {
                    $locationName .= ', ' . TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME . ' ' . \common\helpers\Warehouses::getBatchNameByBatchID($batchId); 
                }
                $orderProductArray[$orderProductId]['allocatedArray'][$warehouseId][$supplierId][$locationId][$layersId][$batchId] = [
                    'allocated_real' => $productAllocated,
                    'allocated_update' => $productAllocated,
                    'warehouseName' => (isset($warehouseNameList[$warehouseId]) ? $warehouseNameList[$warehouseId] : 'N/A'),
                    'supplierName' => (isset($supplierNameList[$supplierId]) ? $supplierNameList[$supplierId] : 'N/A'),
                    'locationName' => (($locationName != '') ? $locationName : 'N/A')
                ];
                unset($locationName);
                if (!isset($warehouseProductArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId])) {
                    $warehouseProductArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId] = [
                        'quantity' => 0,
                        'allocated_real' => 0,
                        'allocated_update' => 0
                    ];
                }
                $warehouseProductArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId]['allocated_real'] += $productAllocated;
                $warehouseProductArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId]['allocated_update'] += $productAllocated;
                unset($productAllocated);
                unset($warehouseId);
                unset($supplierId);
                unset($locationId);
                unset($layersId);
                unset($batchId);
            }
            unset($orderProductAllocateRecord);
            unset($orderProductRecord);
            unset($orderRecord);
        }
        unset($orderProductAllocatedArray);
        unset($orderProductIdArray);
        unset($locationBlockList);
        unset($supplierNameList);
        unset($orderProductId);
        if (Yii::$app->request->isPost) {
            $allocatedUpdateArray = Yii::$app->request->post('allocated_update', []);
            $allocatedUpdateArray = (is_array($allocatedUpdateArray) ? $allocatedUpdateArray : []);
            foreach ($allocatedUpdateArray as $orderProductId => $warehouseArray) {
                if (isset($orderProductArray[$orderProductId])) {
                    $orderProductArray[$orderProductId]['allocated_update'] = 0;
                } else {
                    continue;
                }
                foreach ($warehouseArray as $warehouseId => $supplierArray) {
                    foreach ($supplierArray as $supplierId => $locationArray) {
                        foreach ($locationArray as $locationId => $layersArray) {
                            foreach ($layersArray as $layersId => $batchArray) {
                                foreach ($batchArray as $batchId => $allocatedUpdate) {
                                    if (isset($orderProductArray[$orderProductId]['allocatedArray'][$warehouseId][$supplierId][$locationId][$layersId][$batchId])) {
                                        $orderProductArray[$orderProductId]['allocated_update'] += (int)$allocatedUpdate;
                                        $orderProductArray[$orderProductId]['allocatedArray'][$warehouseId][$supplierId][$locationId][$layersId][$batchId]['allocated_update'] = (int)$allocatedUpdate;
                                    }
                                }
                            }
                        }
                        unset($allocatedUpdate);
                        unset($locationId);
                    }
                    unset($locationArray);
                    unset($supplierId);
                }
                unset($supplierArray);
                unset($warehouseId);
            }
            unset($allocatedUpdateArray);
            unset($warehouseArray);
            unset($orderProductId);
            foreach ($warehouseProductArray as $warehouseId => $supplierArray) {
                foreach ($supplierArray as $supplierId => $locationArray) {
                    foreach ($locationArray as $locationId => $layersArray) {
                        foreach ($layersArray as $layersId => $batchArray) {
                            foreach ($batchArray as $batchId => $warehouseProductRecord) {
                                $warehouseProductArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId]['allocated_update'] = 0;
                                foreach ($orderProductArray as $orderProductId => $orderProductData) {
                                    if (isset($orderProductData['allocatedArray'][$warehouseId][$supplierId][$locationId][$layersId][$batchId])) {
                                        $warehouseProductArray[$warehouseId][$supplierId][$locationId][$layersId][$batchId]['allocated_update'] += $orderProductData['allocatedArray'][$warehouseId][$supplierId][$locationId][$layersId][$batchId]['allocated_update'];
                                    }
                                }
                                unset($orderProductData);
                                unset($orderProductId);
                            }
                        }
                    }
                    unset($warehouseProductRecord);
                    unset($locationId);
                }
                unset($locationArray);
                unset($supplierId);
            }
            unset($supplierArray);
            unset($warehouseId);
            $return = ['status' => 'ok', 'message' => ''];
            foreach ($orderProductArray as $orderProductId => $orderProductData) {
                if ($orderProductData['quantity'] < $orderProductData['allocated_update']) {
                    $return = ['status' => 'error', 'message' => TEXT_OPR_ERROR_INVALID];
                    break;
                }
            }
            unset($orderProductData);
            unset($orderProductId);
            if ($return['status'] == 'ok') {
                foreach ($warehouseProductArray as $warehouseId => $supplierArray) {
                    foreach ($supplierArray as $supplierId => $locationArray) {
                        foreach ($locationArray as $locationId => $layersArray) {
                            foreach ($layersArray as $layersId => $batchArray) {
                                foreach ($batchArray as $batchId => $warehouseProductRecord) {
                                    if ($warehouseProductRecord['allocated_update'] > 0 AND $warehouseProductRecord['quantity'] < $warehouseProductRecord['allocated_update']) {
                                        foreach ($orderProductArray as $orderProductId => $orderProductData) {
                                            if (isset($orderProductData['allocatedArray'][$warehouseId][$supplierId][$locationId][$layersId][$batchId])) {
                                                if ($orderProductData['allocatedArray'][$warehouseId][$supplierId][$locationId][$layersId][$batchId]['allocated_update'] > $orderProductData['allocatedArray'][$warehouseId][$supplierId][$locationId][$layersId][$batchId]['allocated_real']) {
                                                    $return = ['status' => 'error', 'message' => TEXT_OPR_ERROR_INVALID];
                                                    break 6;
                                                }
                                            }
                                        }
                                        unset($orderProductData);
                                        unset($orderProductId);
                                    }
                                }
                            }
                        }
                        unset($warehouseProductRecord);
                        unset($locationId);
                    }
                    unset($locationArray);
                    unset($supplierId);
                }
                unset($supplierArray);
                unset($warehouseId);
            }
            unset($warehouseProductArray);
            if ($return['status'] == 'ok') {
                foreach ($orderProductArray as $orderProductId => $orderProductData) {
                    foreach ($orderProductData['allocatedArray'] as $warehouseId => $supplierArray) {
                        foreach ($supplierArray as $supplierId => $locationArray) {
                            foreach ($locationArray as $locationId => $layersArray) {
                                foreach ($layersArray as $layersId => $batchArray) {
                                    foreach ($batchArray as $batchId => $allocatedUpdate) {
                                        $orderProductAllocateRecord = \common\models\OrdersProductsAllocate::find()
                                            ->where(['orders_products_id' => $orderProductId])
                                            ->andWhere(['warehouse_id' => $warehouseId])
                                            ->andWhere(['suppliers_id' => $supplierId])
                                            ->andWhere(['location_id' => $locationId])
                                            ->andWhere(['layers_id' => $layersId])
                                            ->andWhere(['batch_id' => $batchId])
                                            ->one();
                                        if ($allocatedUpdate['allocated_update'] <= 0) {
                                            if ($orderProductAllocateRecord instanceof \common\models\OrdersProductsAllocate) {
                                                try {
                                                    if ($orderProductAllocateRecord->allocate_dispatched > 0) {
                                                        $orderProductAllocateRecord->allocate_received = $orderProductAllocateRecord->allocate_dispatched;
                                                        $orderProductAllocateRecord->save();
                                                    } else {
                                                        $orderProductAllocateRecord->delete();
                                                    }
                                                } catch (\Exception $exc) {}
                                            }
                                        } else {
                                            if (!($orderProductAllocateRecord instanceof \common\models\OrdersProductsAllocate)) {
                                                $orderProductAllocateRecord = new \common\models\OrdersProductsAllocate();
                                                $orderProductAllocateRecord->orders_products_id = $orderProductId;
                                                $orderProductAllocateRecord->warehouse_id = $warehouseId;
                                                $orderProductAllocateRecord->suppliers_id = $supplierId;
                                                $orderProductAllocateRecord->location_id = $locationId;
                                                $orderProductAllocateRecord->layers_id = $layersId;
                                                $orderProductAllocateRecord->batch_id = $batchId;
                                                $orderProductAllocateRecord->platform_id = $orderProductData['platformId'];
                                                $orderProductAllocateRecord->orders_id = $orderProductData['orderId'];
                                                $orderProductAllocateRecord->prid = (int)$uProductId;
                                                $orderProductAllocateRecord->products_id = $uProductId;
                                                $orderProductAllocateRecord->suppliers_price = \common\models\SuppliersProducts::getSuppliersPrice($uProductId, $supplierId);
                                                $orderProductAllocateRecord->is_temporary = \common\helpers\Order::isAllocateTemporary($orderProductData['orderId']);
                                                $orderProductAllocateRecord->datetime = date('Y-m-d H:i:s');
                                            }
                                            $orderProductAllocateRecord->allocate_received = ($orderProductAllocateRecord->allocate_dispatched + $allocatedUpdate['allocated_update']);
                                            try {
                                                $orderProductAllocateRecord->save();
                                            } catch (\Exception $exc) {}
                                        }
                                        unset($orderProductAllocateRecord);
                                    }
                                }
                            }
                            unset($allocatedUpdate);
                            unset($locationId);
                        }
                        unset($locationArray);
                        unset($supplierId);
                    }
                    unset($orderProductRecord);
                    unset($supplierArray);
                    unset($warehouseId);
                    \common\helpers\OrderProduct::evaluate($orderProductId);
                    \common\helpers\Order::evaluate($orderProductData['orderId']);
                }
                unset($orderProductData);
                unset($orderProductId);
                \common\helpers\Product::isValidAllocated($uProductId);
                $productRecord = \common\helpers\Product::getRecord($uProductId, true);
                if ($productRecord instanceof \common\models\Products) {
                    $return['allocated_temporary'] = \common\helpers\Product::getAllocatedTemporary($uProductId, true);
                    $return['deficit'] = \common\helpers\Product::getVirtualItemQuantity($uProductId, \common\helpers\Product::getStockDeficit($uProductId));
                    $return['available'] = \common\helpers\Product::getVirtualItemQuantity($uProductId, $productRecord->products_quantity);
                    $return['allocated'] = \common\helpers\Product::getVirtualItemQuantity($uProductId, ($productRecord->allocated_stock_quantity - $return['allocated_temporary']));
                    $return['allocated_temporary'] = \common\helpers\Product::getVirtualItemQuantity($uProductId, $return['allocated_temporary']);
                }
                unset($productRecord);
            }
            unset($orderProductArray);
            return json_encode($return);
        }
//numeric index :( replaced with 0,1,2....      \yii\helpers\ArrayHelper::multisort($orderProductArray, ['datePurchased', 'orderId']);
        uasort($orderProductArray, function ($a, $b) { return strnatcmp($a["datePurchased"].$a['orderId'], $b["datePurchased"].$b['orderId']); } );

        return $this->renderAjax('order-reallocate', [
            'orderProductArray' => $orderProductArray,
            'warehouseProductArray' => $warehouseProductArray,
            'warehouseNameList' => $warehouseNameList,
            'prid' => $uProductId,
            'isParent' => (count(\common\helpers\Product::getChildArray($uProductId)) > 0)
        ]);
    }

    public function actionTemporaryStock()
    {
        $this->layout = false;
        if (Yii::$app->request->post('action', '') == 'delete') {
            $temporary_stock_id = (int)Yii::$app->request->post('temporary_stock_id', 0);
            $opTemporaryRecord = \common\models\OrdersProductsTemporaryStock::findOne(['temporary_stock_id' => $temporary_stock_id]);
            $temporary_stock_id = 0;
            if ($opTemporaryRecord instanceof \common\models\OrdersProductsTemporaryStock) {
                try {
                    $opTemporaryRecord->delete();
                    $temporary_stock_id = (int)$opTemporaryRecord->temporary_stock_id;
                } catch (\Exception $exc) {}
            }
            echo json_encode([
                'id' => $temporary_stock_id
            ]);
            die();
        }
        \common\helpers\Translation::init('admin/categories');
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual(Yii::$app->request->get('prid'));
        $warehouseNameList = [];
        foreach (\common\models\Warehouses::find()->asArray(true)->all() as $warehouseRecord) {
            $warehouseNameList[$warehouseRecord['warehouse_id']] = $warehouseRecord['warehouse_name'];
        }
        unset($warehouseRecord);
        $temporaryArray = [];
        foreach (\common\helpers\Product::getAllocatedTemporaryArray($uProductId) as $opTemporaryRecord) {
            if ((int)$opTemporaryRecord['customers_id'] > 0) {
                $customerRecord = \common\helpers\Customer::getCustomerData($opTemporaryRecord['customers_id']);
                if (is_array($customerRecord) AND isset($customerRecord['customers_lastname'])) {
                    $opTemporaryRecord['customer_name'] = '<a href="' . tep_href_link('customers/customeredit', 'customers_id=' . $customerRecord['customers_id']) . '" target="_blank">' . trim(trim($customerRecord['customers_firstname']) . ' ' . trim($customerRecord['customers_lastname'])) . '</a>';
                }
                unset($customerRecord);
            }
            $opTemporaryRecord['warehouse_name'] = (isset($warehouseNameList[$opTemporaryRecord['warehouse_id']]) ? $warehouseNameList[$opTemporaryRecord['warehouse_id']] : '');
            $temporaryArray[] = $opTemporaryRecord;
        }
        unset($opTemporaryRecord);
        unset($warehouseNameList);
        return $this->render('temporary-stock', [
            'temporaryArray' => $temporaryArray
        ]);
    }

    public function actionOrdersProductsStock()
    {
        $this->layout = false;
        \common\helpers\Translation::init('admin/categories');
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual(Yii::$app->request->get('prid'));
        $warehouseNameList = [];
        foreach (\common\models\Warehouses::find()->asArray(true)->all() as $warehouseRecord) {
            $warehouseNameList[$warehouseRecord['warehouse_id']] = $warehouseRecord['warehouse_name'];
        }
        unset($warehouseRecord);
        $supplierNameList = [];
        foreach (\common\models\Suppliers::find()->asArray(true)->all() as $supplierRecord) {
            $supplierNameList[$supplierRecord['suppliers_id']] = $supplierRecord['suppliers_name'];
        }
        unset($supplierRecord);
        $locationBlockList = [];
        foreach (\common\models\LocationBlocks::find()->asArray(true)->all() as $locationBlockRecord) {
            $locationBlockList[$locationBlockRecord['block_id']] = $locationBlockRecord['block_name'];
        }
        unset($locationBlockRecord);
        $allocationArray = [];
        foreach (\common\helpers\Product::getAllocatedArray($uProductId, true, true) as $opaRecord) {
            if ((int)$opaRecord['is_temporary'] > 0) {
                continue;
            }
            $opaRecord['order_link'] = ('<a target="_blank" href="'
                . tep_href_link('orders/process-order', 'orders_id=' . $opaRecord['orders_id'])
                . '">' . $opaRecord['orders_id'] . '</a>'
            );
            $orderRecord = \common\models\Orders::find()->where(['orders_id' => $opaRecord['orders_id']])->asArray(true)->one();
            if (is_array($orderRecord) AND isset($orderRecord['customers_lastname'])) {
                $opaRecord['customer_name'] = ('<a target="_blank" href="'
                    . tep_href_link('customers/customeredit', 'customers_id=' . $orderRecord['customers_id'])
                    . '">' . trim(trim($orderRecord['customers_firstname']) . ' ' . trim($orderRecord['customers_lastname']))
                    . '</a>'
                );
            }
            unset($orderRecord);
            $opaRecord['warehouse_name'] = ($warehouseNameList[$opaRecord['warehouse_id']] ?? '');
            $opaRecord['supplier_name'] = ($supplierNameList[$opaRecord['suppliers_id']] ?? '');
            $opaRecord['location_name'] = trim(\common\helpers\Warehouses::getLocationPath($opaRecord['location_id'], $opaRecord['warehouse_id'], $locationBlockList));
            $allocationArray[] = $opaRecord;
        }
        unset($locationBlockList);
        unset($warehouseNameList);
        unset($supplierNameList);
        unset($opaRecord);
        return $this->render('orders-products-stock', [
            'allocationArray' => $allocationArray
        ]);
    }

    public function actionOrdersProductsDeficit()
    {
        $this->layout = false;
        \common\helpers\Translation::init('admin/categories');
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual(Yii::$app->request->get('prid'));
        $deficitArray = [];
        foreach (\common\models\OrdersProducts::find()
            ->select(['*', '(products_quantity - (qty_cnld + qty_rcvd)) AS deficit'])
            ->where(['uprid' => $uProductId])
            ->andWhere(['>', '(products_quantity - (qty_cnld + qty_rcvd))', 0])
            ->andWhere(['NOT IN', 'orders_products_status', [
                \common\helpers\OrderProduct::OPS_QUOTED,
            ]])
            ->asArray(true)->all() as $opRecord
        ) {
            $opRecord['order_link'] = ('<a target="_blank" href="'
                . tep_href_link('orders/process-order', 'orders_id=' . $opRecord['orders_id'])
                . '">' . $opRecord['orders_id'] . '</a>'
            );
            $orderRecord = \common\models\Orders::find()->where(['orders_id' => $opRecord['orders_id']])->asArray(true)->one();
            if (is_array($orderRecord) AND isset($orderRecord['customers_lastname'])) {
                $opRecord['datetime'] = $orderRecord['date_purchased'];
                $opRecord['customer_name'] = ('<a target="_blank" href="'
                    . tep_href_link('customers/customeredit', 'customers_id=' . $orderRecord['customers_id'])
                    . '">' . trim(trim($orderRecord['customers_firstname']) . ' ' . trim($orderRecord['customers_lastname']))
                    . '</a>'
                );
            }
            unset($orderRecord);
            $deficitArray[] = $opRecord;
        }
        unset($opRecord);
        return $this->render('orders-products-deficit', [
            'deficitArray' => $deficitArray
        ]);
    }

    public function actionOrdersProductsTemporaryStock()
    {
        $this->layout = false;
        if (Yii::$app->request->post('action', '') == 'delete') {
            try {
                $allocationId = trim(Yii::$app->request->post('allocation_id', ''));
                $searchArray = explode('_', $allocationId);
                $opAllocateRecord = \common\models\OrdersProductsAllocate::find()
                    ->where(['is_temporary' => 1])
                    ->andWhere(['allocate_dispatched' => 0])
                    ->andWhere(['orders_products_id' => ($searchArray[0] ?? -1)])
                    ->andWhere(['warehouse_id' => ($searchArray[1] ?? -1)])
                    ->andWhere(['suppliers_id' => ($searchArray[2] ?? -1)])
                    ->andWhere(['location_id' => ($searchArray[3] ?? -1)])
                    ->asArray(false)->one();
                unset($searchArray);
                if ($opAllocateRecord instanceof \common\models\OrdersProductsAllocate) {
                    $ordersProductsId = $opAllocateRecord->orders_products_id;
                    $opAllocateRecord->delete();
                    \common\helpers\OrderProduct::evaluate($ordersProductsId);
                    unset($ordersProductsId);
                    echo json_encode([
                        'id' => $allocationId
                    ]);
                }
                unset($opAllocateRecord);
            } catch (\Exception $exc) {
                \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'Error.Backend.Controller.Categories.actionOrdersProductsTemporaryStock.delete');
            }
            die();
        }
        \common\helpers\Translation::init('admin/categories');
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual(Yii::$app->request->get('prid'));
        $warehouseNameList = [];
        foreach (\common\models\Warehouses::find()->asArray(true)->all() as $warehouseRecord) {
            $warehouseNameList[$warehouseRecord['warehouse_id']] = $warehouseRecord['warehouse_name'];
        }
        unset($warehouseRecord);
        $supplierNameList = [];
        foreach (\common\models\Suppliers::find()->asArray(true)->all() as $supplierRecord) {
            $supplierNameList[$supplierRecord['suppliers_id']] = $supplierRecord['suppliers_name'];
        }
        unset($supplierRecord);
        $locationBlockList = [];
        foreach (\common\models\LocationBlocks::find()->asArray(true)->all() as $locationBlockRecord) {
            $locationBlockList[$locationBlockRecord['block_id']] = $locationBlockRecord['block_name'];
        }
        unset($locationBlockRecord);
        $orderStatusExpiredDurationHours = (int)\common\helpers\Configuration::get_configuration_key_value('ORDER_STATUS_TEMPORARY_ALLOCATION_EXPIRED_DURATION');
        if ($orderStatusExpiredDurationHours < 1) {
            $orderStatusExpiredDurationHours = 1;
        }
        $temporaryArray = [];
        foreach (\common\helpers\Product::getAllocatedTemporaryArray($uProductId, true, true) as $opTemporaryRecord) {
            $opTemporaryRecord['allocation_id'] = "{$opTemporaryRecord['orders_products_id']}_{$opTemporaryRecord['warehouse_id']}_{$opTemporaryRecord['suppliers_id']}_{$opTemporaryRecord['location_id']}";
            $opTemporaryRecord['orders_link'] = '<a href="' . tep_href_link('orders/process-order', 'orders_id=' . $opTemporaryRecord['orders_id']) . '" target="_blank">' . $opTemporaryRecord['orders_id'] . '</a>';
            $orderRecord = \common\models\Orders::find()->where(['orders_id' => $opTemporaryRecord['orders_id']])->asArray()->one();
            if (is_array($orderRecord) && isset($orderRecord['customers_lastname'])) {
                $opTemporaryRecord['customer_name'] = '<a href="' . tep_href_link('customers/customeredit', 'customers_id=' . $orderRecord['customers_id']) . '" target="_blank">' . trim($orderRecord['customers_firstname']) . ' ' . trim($orderRecord['customers_lastname']) . '</a>';
            }
            unset($orderRecord);
            $opTemporaryRecord['warehouse_name'] = ($warehouseNameList[$opTemporaryRecord['warehouse_id']] ?? '');
            $opTemporaryRecord['supplier_name'] = ($supplierNameList[$opTemporaryRecord['suppliers_id']] ?? '');
            $opTemporaryRecord['location_name'] = trim(\common\helpers\Warehouses::getLocationPath($opTemporaryRecord['location_id'], $opTemporaryRecord['warehouse_id'], $locationBlockList));
            $timeExpire = (strtotime($opTemporaryRecord['datetime']) + $orderStatusExpiredDurationHours * 60 * 60);
            $timeExpire = ((($timeExpire - time()) < 0) ? ' style="color: red;"' : '');
            $opTemporaryRecord['allocate_time'] = ('<span' . $timeExpire . '>' . \common\helpers\Date::timeHumanize($opTemporaryRecord['datetime']) . '</span>');
            unset($timeExpire);
            $temporaryArray[] = $opTemporaryRecord;
        }
        unset($orderStatusExpiredDurationHours);
        unset($opTemporaryRecord);
        unset($locationBlockList);
        unset($warehouseNameList);
        unset($supplierNameList);
        return $this->render('orders-products-temporary-stock', [
            'temporaryArray' => $temporaryArray
        ]);
    }

    public function actionProductInBundleStatus()
    {
        $this->layout = false;
        $return = ['status' => 'error'];
        $productRecord = \common\helpers\Product::getRecord((int)Yii::$app->request->post('pID'));
        if ($productRecord instanceof \common\models\Products) {
            $productRecord->products_status_bundle = (int)Yii::$app->request->post('status');
            try {
                $productRecord->save();
                $return = ['status' => 'ok'];
            } catch (\Exception $exc) {}
        }
        unset($productRecord);
        echo json_encode($return);
        die();
    }

    public function actionProductLabel()
    {
        $model = trim(Yii::$app->request->get('model', ''));
        if ( strlen($model)==0 ) $model = '-';
        $count = Yii::$app->request->get('count', 1);
        $labelData = \common\helpers\ProductLabel::label($model, max(1,(int)$count));

        $this->layout = false;
        Yii::$app->response->sendContentAsFile(
                $labelData,
                preg_replace('/[^\da-z-_]+/i', '_', $model).'.pdf',
                [
                    'mimeType'=>'application/pdf',
                    'inline'=>true,
                ]
        );
    }

    public function actionCheckSupplierDelete()
    {
        $this->layout = false;
        $return = ['status' => 'error'];
        $pId = \common\helpers\Inventory::normalize_id_excl_virtual(Yii::$app->request->post('pId'));
        $productRecord = \common\helpers\Product::getRecord($pId);
        if ($productRecord instanceof \common\models\Products) {
            $return = ['status' => 'ok'];
            $sId = (int)Yii::$app->request->post('sId');
            foreach (\common\models\WarehousesProducts::find()
                ->andWhere(['products_id' => $pId])
                ->andWhere(['prid' => (int)$pId])
                ->andWhere(['suppliers_id' => $sId])
                ->asArray(true)->all() as $wpRecord
            ) {
                $return = ['status' => 'error', 'message' => MESSAGE_ERROR_SUPPLIER_DELETE_STOCK];
                break;
            }
        }
        echo json_encode($return);
        die();
    }
    public function actionFileFilterForm()
    {
        $editorId = \Yii::$app->request->get('editorId');
        $host = tep_catalog_href_link('', '', 'SSL', \common\classes\platform::defaultId());
        // $items = [['link'=>'link', 'text'=> 'text']];

        return $this->renderPartial('productedit/notes/document-links.tpl', [
            //'items' => $items,
            'editorId' => $editorId,
            'host' => $host,
            'suggest' => true
        ]);
    }
    public function actionFileFilter()
    {
        $languageId = \Yii::$app->settings->get('languages_id');
        $editorId = \Yii::$app->settings->get('editorId');
        if (!$languageId) {
            $languageId = (int)\common\classes\language::defaultId();
        }

        $keywords = \Yii::$app->request->get('keywords', '');
        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/design');
        $fsPath = DIR_FS_CATALOG . 'documents/';
        $link = DIR_WS_CATALOG;
        if (mb_strpos($link, '/') === 0) {
            $link = substr($link, 1);
        }
        $wsPath = $link . 'documents/';

        $documents = $this->productsDocumentsService->findByFileName($keywords, $languageId, true, 10, true);

        $resultDocuments = [];
        $documentNames = [];
        if ($documents) {
            foreach ($documents as $id => $document) {
                $documents[$id]['exist'] = true;
                $documents[$id]['name'] = $document['title']['title'] ?: $document['filename'];
                $documents[$id]['download'] = $document['filename'];
                if (!$document['is_link']) {
                    $documents[$id]['download'] = $wsPath . $document['filename'];
                    $documents[$id]['exist'] = false;
                    if (is_file($fsPath . $document['filename'])) {
                        $documents[$id]['exist'] = true;
                    }
                }
                if (in_array($documents[$id]['name'], $documentNames, true)) {
                    continue;
                }
                $documentNames[] = $documents[$id]['name'];
                $resultDocuments[] = $documents[$id];
            }
        }
        $diskDocuments = array_map('basename', FileHelper::findFiles($fsPath, ['recursive' => false, 'only' => ['pattern' => "*{$keywords}*"]]));
        if ($diskDocuments) {
            $diskDocuments = array_slice($diskDocuments, 0, 10);
            foreach ($diskDocuments as $document) {
                if (in_array($document, $documentNames, true)) {
                    continue;
                }
                $documentNames[] = $document;
                $resultDocuments[] = [
                    'exist' => true,
                    'name' => $document,
                    'filename' => $document,
                    'download' => $wsPath . $document,
                ];
            }
        }
        return $this->renderPartial('productedit/notes/document-search.tpl', [
            'documents' => $resultDocuments,
            'editorId' => $editorId,
        ]);
    }

    public function actionLoadTree() {
        \common\helpers\Translation::init('admin/platforms');
        $this->layout = false;

        $post = Yii::$app->request->post();
        $catalog =  new \backend\components\ProductsCatalog();
        $catalog->settings['add_sku'] = false;
        return $catalog->make($post);
    }

    public function actionSeacrhProduct() {
      $languages_id = \Yii::$app->settings->get('languages_id');
      $seacrh = Yii::$app->request->get('search', null);

      if (!empty($seacrh)){
        $catalog = new \backend\components\ProductsCatalog();
        //$catalog->post['suggest'] = 1;
        if (!($catalog->post['suggest']??null)) {
            $catalog->post['suggest'] = Yii::$app->request->get('suggest');
        }
        return $catalog->search($seacrh);
      }
    }

    public function actionDemoCleanup() {
        set_time_limit(0);
        $sdn = \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed');
        $demoProducts = \common\models\Products::find()->select(['products_id'])->where(['is_demo' => 1])->asArray()->all();
        foreach ($demoProducts as $product) {
            $key = $product['products_id'];
            \common\helpers\Product::remove_product($key);
            if ($sdn) {
                $sdn::deleteProductLinks($key);
            }
        }
        return $this->redirect(Yii::$app->urlManager->createUrl(['categories/']));
    }

    /**
     * works only with customers groups.
     */
    public function actionProductPriceEdit() {

        if (!\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            return;
        }
        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/categories/productedit');
        $currencies = Yii::$container->get('currencies');

        $this->layout = false;
        $currencies_id = \Yii::$app->request->post('currencies_id', \Yii::$app->request->get('currencies_id', 0));
        $products_id = \Yii::$app->request->post('products_id', \Yii::$app->request->get('products_id', 0));
        $group_id = \Yii::$app->request->post('group_id', 0);
        $only_price = \Yii::$app->request->post('only_price', 0);

        $no_price = true;
        if ($group_id>0) {
            $no_price = false;
        }

////currencies tabs and params
        $this->view->price_tabs = $this->view->price_tabparams = [];
        $this->view->currenciesTabs = [];
/*
        if ($this->view->useMarketPrices) {
          foreach ($currencies->currencies as $value) {
            $value['def_data'] = ['currencies_id' => $value['id']];
            $value['title'] = $value['symbol_left'] . ' ' . $value['code'] . ' ' . $value['symbol_right'];
            $this->view->currenciesTabs[] = $value;
          }
          $this->view->price_tabs[] = $this->view->currenciesTabs;
          $this->view->price_tabparams[] =  [
              'cssClass' => 'tabs-currencies',
              'tabs_type' => 'hTab',
              //'maxWidth' => '520px',
              //'include' => 'test/test.tpl',
          ];
        }
 */
    //// groups tabs and params
          $this->view->groups = [];
          /** @var \common\extensions\UserGroups\UserGroups $ext */
          if ($ext = \common\helpers\Acl::checkExtensionAllowed('UserGroups', 'allowed')) {
              $ext::getGroups();
          }

          $this->view->groups_m = $this->view->groups;
          $tabdata = $groups = $tmp = [];
          foreach ($this->view->groups_m as $value) {
            $value['id'] = $value['groups_id'];
            $value['title'] = $value['groups_name'];
            $value['def_data'] = ['groups_id' => $value['id']];
            unset($value['groups_name']);
            unset($value['groups_id']);
            $tmp[] = $value;
            if ($group_id == $value['id']) {
                $tabdata = $value;
            }
            if ($value['per_product_price']==0) {
                $groups[$value['id']] = $value['title'];
            }
          }
          //$this->view->price_tabs[] = $tmp;
          $this->view->price_tabs = $tabdata;
          unset($tmp);


          $this->view->price_tabparams[] = [
              'cssClass' => 'tabs-groups', // add to tabs and tab-pane
              //'callback' => 'productPriceBlock', // smarty function which will be called before children tabs , data passed as params params
              'callback_bottom' => '',
              'tabs_type' => 'lTab',
//              'aboveTabs' => (count($this->view->groups_m)<(1+count($this->view->groups))? '../productedit/edit-price-link.tpl':''),
//              'all_hidden' => (count($this->view->groups_m)==1),
//              'maxHeight' => '400px',
          ];

        $this->view->useMarketPrices = (USE_MARKET_PRICES == 'True');

        $groups = [0 => TEXT_CHOOSE_GROUP] + $groups;

        $this->view->tax_classes = ['0' => TEXT_NONE];
        $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
        while ($tax_class = tep_db_fetch_array($tax_class_query)) {
            $this->view->tax_classes[$tax_class['tax_class_id']] = $tax_class['tax_class_title'];
        }
        $p = \common\models\Products::find()
            ->andWhere(['products_id' => (int) $products_id])
//            ->with('description')
//            ->with('platforms')
//            ->with('localRating')
            ;
        if (tep_session_is_registered('login_vendor')) {
          global $login_id;
          $p->andWhere(['vendor_id' => $login_id]);
        }

        $pInfo = $p->one();
        $this->ProductEditTabAccess->setProduct($pInfo);

        if ($only_price) {
            if ( $pInfo->products_id_price && $pInfo->products_id != $pInfo->products_id_price ) {
                $priceViewObj = new ViewPriceData(\common\models\Products::findOne($pInfo->products_id_price));
            }else {
                $priceViewObj = new ViewPriceData($pInfo);
            }
            $priceViewObj->populateView($this->view);
            if ($this->view->useMarketPrices) {
                $data = $this->view->price_tabs_data[$currencies_id][$group_id];
            } else {
                $data = $this->view->price_tabs_data[$group_id] ?? null;
            }
            $data['tabdata'] = $tabdata;
            $data['groups_id'] = $group_id;

            unset($this->view->price_tabs);
            unset($this->view->price_tabs_data);
            $this->view->price_tabs_data = $data;
            $ret = $this->render('productedit/price', [
              'currencies' => $currencies,
              'pInfo' => $pInfo,
              'TabAccess' => $this->ProductEditTabAccess,
              'idSuffix' => '_' . ($this->view->useMarketPrices?$currencies_id . '_':'') . $group_id,
              'fieldSuffix' => ($this->view->useMarketPrices?'[' . $currencies_id . ']':'') . '[' . $group_id . ']',
              'default_currency' => $currencies->currencies[DEFAULT_CURRENCY],
              'hideSuppliersPart' => 1,
              'popup' => 1,
            ]);
        } else {
            $ret = $this->render('productedit/edit-price-popup', [
              'currencies' => $currencies,
              'currencies_id' => $currencies_id,
              'products_id' => $products_id,
              'pInfo' => $pInfo,
              'groups' => $groups
            ]);
        }

        return $ret;

    }

    public function actionGroupPriceSubmit() {

        if (!\common\helpers\Extensions::isCustomerGroupsAllowed()) {
            return;
        }

        $res = ['result' => 0, 'message' => 'error'];

        \common\helpers\Translation::init('admin/categories');
        $currencies = Yii::$container->get('currencies');
        $TabAccess = $this->ProductEditTabAccess;

        $this->layout = false;
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        //$currencies_id = \Yii::$app->request->post('currencies_id', \Yii::$app->request->get('currencies_id', 0));
        $old_products_id = $products_id = \Yii::$app->request->post('products_id', 0);
        $group_id = \Yii::$app->request->post('group_id', 0);
        $_def_curr_id = $currencies->currencies[DEFAULT_CURRENCY]['id'];
        if (USE_MARKET_PRICES == 'True') {
          foreach ($currencies->currencies as $key => $value)  {
            $currencies_ids[$currencies->currencies[$key]['id']] = $currencies->currencies[$key]['id'];
          }
        } else {
          $currencies_ids[$_def_curr_id] = '0'; /// here is the post and db currencies_id are different.
        }

        $productModel = \common\models\Products::findOne((int)$products_id);
//        $_products_id_price = intval(Yii::$app->request->post('products_id_price',-1));
//       if ( $_products_id_price>=0 ) { $productModel->products_id_price = $_products_id_price; }

        $TabAccess->setProduct($productModel);
        $groups_price = $groups = [$group_id => 'dummy'];

        try {

        //Gift wrap
            if ($TabAccess->tabDataSave('TEXT_MAIN_DETAILS')) {
                if ($old_products_id > 0) {
                    if ($groups_price) {
                        \common\models\GiftWrapProducts::deleteAll([
                          'products_id' => (int) $old_products_id,
                          'groups_id' => array_keys($groups_price),
                        ]);
                    } else {
                        tep_db_query("delete from " . TABLE_GIFT_WRAP_PRODUCTS . " where products_id = '" . (int) $old_products_id . "'");
                    }
                }
                $gift_wrap = Yii::$app->request->post('gift_wrap', 0);
                if (is_array($gift_wrap) || $gift_wrap > 0) {
                    if (is_array($gift_wrap) && (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed())) {
                        foreach ($currencies_ids as $post_currencies_id => $currencies_id) {
                            foreach (($groups_price ? $groups_price : $groups) as $groups_id => $non) {
                                $sql_data_array = ['products_id' => (int) $products_id,
                                  'groups_id' => (int) $groups_id,
                                  'currencies_id' => (int) $currencies_id,
                                ];
                                $field = ['db' => 'gift_wrap_price', 'dbdef' => 0, 'post' => 'gift_wrap_price', 'flag' => 'gift_wrap'];
                                if (self::getFromPostArrays(['post' => 'gift_wrap'], (int) $post_currencies_id, (int) $groups_id) == 1) {
                                    $sql_data_array[$field['db']] = self::getFromPostArrays($field, (int) $post_currencies_id, (int) $groups_id);
                                    tep_db_perform(TABLE_GIFT_WRAP_PRODUCTS, $sql_data_array);
                                }
                            }
                        }
                    } else {
                        $sql_data_array = ['products_id' => (int) $products_id,
                          'groups_id' => 0,
                          'currencies_id' => 0,
                        ];
                        $field = ['db' => 'gift_wrap_price', 'dbdef' => 0, 'post' => 'gift_wrap_price', 'flag' => 'gift_wrap'];
                        if (self::getFromPostArrays(['post' => 'gift_wrap'], 0) == 1) {
                            $sql_data_array[$field['db']] = self::getFromPostArrays($field, 0);
                            tep_db_perform(TABLE_GIFT_WRAP_PRODUCTS, $sql_data_array);
                        }
                    }
                }
            }

            if  ($TabAccess->tabDataSave('TEXT_PRICE_COST_W')) {
                $productModel->disable_discount = intval(Yii::$app->request->post('disable_discount', 0));

    //2 group prices specials. etc

                if (USE_MARKET_PRICES == 'True' || \common\helpers\Extensions::isCustomerGroupsAllowed()) {
                    if ($groups_price ?? null) {
                        \common\models\ProductsPrices::deleteAll([
                          'products_id' => (int) $old_products_id,
                          'groups_id' => array_keys($groups_price),
                        ]);
                    } else {
                        tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "'");
                    }

                    foreach ($currencies_ids as $post_currencies_id => $currencies_id) {
                        foreach (($groups_price ? $groups_price : $groups) as $groups_id => $non) {
                            $sql_data_array = ['products_id' => (int) $products_id,
                              'groups_id' => (int) $groups_id,
                              'currencies_id' => (int) $currencies_id,
                            ];
                            $fields = [
                              ['db' => 'products_sets_discount', 'dbdef' => 0, 'post' => 'products_group_sets_discount'],
                              ['db' => 'products_group_price', 'dbdef' => ($groups_id == 0 ? 0 : -2), 'post' => 'products_group_price'],
                              ['db' => 'bonus_points_price', 'dbdef' => 0, 'post' => 'bonus_points_price', 'flag' => 'bonus_points_status'],
                              ['db' => 'bonus_points_cost', 'dbdef' => 0, 'post' => 'bonus_points_cost', 'flag' => 'bonus_points_status'],
                              ['db' => 'products_group_price_pack_unit', 'dbdef' => -2, 'post' => 'products_group_price_pack_unit', 'f' => ['self', 'defGroupPrice']],
                              ['db' => 'products_group_price_packaging', 'dbdef' => -2, 'post' => 'products_group_price_packaging', 'f' => ['self', 'defGroupPrice']],
                              ['db' => 'supplier_price_manual', 'dbdef' => 'null', 'post' => 'supplier_auto_price'],
                              ['db' => 'shipping_surcharge_price', 'dbdef' => 0, 'post' => 'shipping_surcharge_price', 'flag' => 'shipping_surcharge'],
                              ['db' => 'products_group_discount_price', 'dbdef' => '', 'postreindex' => 'discount_qty', 'post' => 'discount_price', 'flag' => 'qty_discount_status', 'f' => ['self', 'formatDiscountString']],
                              ['db' => 'products_group_discount_price_pack_unit', 'dbdef' => '', 'postreindex' => 'discount_qty_pack_unit', 'post' => 'discount_price_pack_unit', 'flag' => 'qty_discount_status_pack_unit', 'f' => ['self', 'formatDiscountString']],
                              ['db' => 'products_group_discount_price_packaging', 'dbdef' => '', 'postreindex' => 'discount_qty_packaging', 'post' => 'discount_price_packaging', 'flag' => 'qty_discount_status_packaging', 'f' => ['self', 'formatDiscountString']],
                            ];
                            //2do products_price_configurator

                            foreach ($fields as $field) {
                                $sql_data_array[$field['db']] = self::getFromPostArrays($field, (int) $post_currencies_id, (int) $groups_id);
                            }
                            if ($groups_id == 0) {
                                // posted auto, make manual
                                $sql_data_array['supplier_price_manual'] = $sql_data_array['supplier_price_manual'] == '1' ? 0 : 1;
                                // reset matched with current config
                                if (($sql_data_array['supplier_price_manual'] == 1 && SUPPLIER_UPDATE_PRICE_MODE == 'Manual') || ($sql_data_array['supplier_price_manual'] == 0 && SUPPLIER_UPDATE_PRICE_MODE == 'Auto')) {
                                    unset($sql_data_array['supplier_price_manual']);
                                }
                            } else {
                                unset($sql_data_array['supplier_price_manual']);
                            }

                            tep_db_perform(TABLE_PRODUCTS_PRICES, $sql_data_array);
                        }
                    }
                }
                /*
                  if ($ext = \common\helpers\Acl::checkExtensionAllowed('DeliveryOptions', 'allowed')) {
                  $ext::saveProduct($products_id);
                  } */
            }
            $res['result'] = 1;
        } catch (\Exception $e) {
            \Yii::warning(" #### " .print_r($e, true), 'TLDEBUG');
            $res['message'] = $e->getMessage();
        }

        return $res;

    }

    public function actionSetSuppliersStock() {
        $ret = [];
        ///suppliers_data[362][9][suppliers_quantity]
        $suppliers_data = \Yii::$app->request->post('suppliers_data', []);

        $cnt = 0; $qty = 0;

        if (!empty($suppliers_data) && is_array($suppliers_data)) {
            foreach ($suppliers_data as $products_id => $suppliers ) {
                if (!empty($suppliers) && is_array($suppliers)) {
                    foreach ($suppliers as $supplier_id => $data) {
                        try {
                            $qty = intval($data['suppliers_quantity']);
                            $products_id = \common\helpers\Inventory::normalize_id_excl_virtual($products_id);
                            $m = SuppliersProducts::findOne([
                                  'suppliers_id' => (int)$supplier_id,
                                  'products_id' => (int)$products_id,
                                  'uprid' => $products_id,
                                ]);
                            if (empty($m )) {
                                $m = new SuppliersProducts([
                                  'suppliers_id' => (int)$supplier_id,
                                  'products_id' => (int)$products_id,
                                  'uprid' => $products_id,
                                ]);
                                $m->loadDefaultValues();
                            }
                            $m->suppliers_quantity = $qty;
                            $m->save(false);

                            if (!isset($ret[$products_id])) {
                                $ret[$products_id] = [];
                            }
                            $ret[$products_id][$supplier_id] = ['value' => $qty];
                        } catch (\Exception $e) {
                            \Yii::warning(" #### " .print_r($e->getMessage() . $e->getTraceAsString(), true), 'TLDEBUG');
                        }
                        
                        $cnt++;
                    }
                }
                \common\helpers\Product::doCache($products_id);
            }
        }
        //only 1 qty updated = simple response.
        if ($cnt == 1 ) {
            $ret = ['value' => $qty];
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $ret;
    }

    public function actionSaveSupplierFields()
    {
        $productId = \Yii::$app->request->post('save_products_id');
        $uprid = \Yii::$app->request->post('uprid', $productId);
        $supplierId = \Yii::$app->request->post('save_suppliers_id');
        $supplierData = \Yii::$app->request->post('suppliers_data');
        if (empty($productId) || empty($supplierId) || empty($supplierData)) return;

        $supplier = \common\models\SuppliersProducts::findOne(['products_id' => $productId, 'uprid' => $uprid, 'suppliers_id' => $supplierId]);
        if (empty($supplier)) return;
        foreach(['supplier_discount', 'suppliers_surcharge_amount', 'suppliers_margin_percentage'] as $field) {
            $supplier->$field = empty($supplierData[$productId][$supplierId][$field]) ? null : $supplierData[$productId][$supplierId][$field];
        }
        $supplier->save(false);
    }

}
