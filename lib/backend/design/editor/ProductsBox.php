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

namespace backend\design\editor;

use backend\models\ProductNameDecorator;
use Yii;
use yii\base\Widget;
use common\models\Products;

class ProductsBox extends Widget {

    public $manager;
    public $cart;
    public $post = [];

    public function init() {
        parent::init();
    }
    
    public function search($searchText){
        //$searchText = urldecode($searchText);
        $_languages=\Yii::$app->settings->get('languages_id');

        $searchBuilder = new \common\components\SearchBuilder('simple');
        $searchBuilder->setSearchInDesc(SEARCH_IN_DESCRIPTION == 'True');
        $searchBuilder->setSearchInternal(true);
        
        if (defined('BACKEND_SEARCH_ON_ALL_LANGUAGES') && BACKEND_SEARCH_ON_ALL_LANGUAGES == 'True') {
            $_languages = \common\helpers\Language::get_languages();
            $_languages=\yii\helpers\ArrayHelper::getColumn($_languages,'id');
            \Yii::$container->set('_languages',(object)$_languages);
        }

        $searchBuilder->searchInProperty = false;
        $searchBuilder->searchInAttributes = false;
        $searchBuilder->parseKeywords($searchText);
        /*
        $searchBuilder->prepareRequest($searchText);

        $filters_where = $searchBuilder->getProductsArray(false);
        /**/

        $manager = $this->manager;
        $searchBuilder->relevance_order = true;
        $productsQuery = Products::find()
                ->distinct()->alias('p')
                ->select(['p.products_id', 'p.products_model'])
                ->where(['p.products_status' => 1])
                //->groupBy('p.products_id')
                ;
        $ext = \common\helpers\Acl::checkExtensionAllowed('PlainProductsDescription', 'allowed');
        if ($ext && $ext::isEnabled()) {
            $productsQuery
                ->with(['productsDescriptions' => function ($query) use ($manager, $_languages) {
                    $_pl = array_unique([intval(\Yii::$app->get('platform')->config($manager->getPlatformId())->getPlatformToDescription()), intval(\common\classes\platform::defaultId())]);
                    $query->onCondition([
                            'language_id' => (is_array($_languages) ? $_languages : (int)$_languages),
                            'platform_id' => $_pl
                        ])
                        ->addSelect('products_id, language_id, platform_id, products_internal_name, products_name')
                        ->addSelect(['main' => new \yii\db\Expression('platform_id=1')])
                        ->addSelect('products_description, products_url, products_head_title_tag, products_description_short, products_seo_page_name, products_h1_tag, products_h2_tag, products_h3_tag, products_internal_name, platform_id, products_id, language_id, products_image_alt_tag_mask, products_head_desc_tag, products_image_title_tag_mask') // container loads them
                    ;

                    if (count($_pl)>1) {
                        $query->addOrderBy(new \yii\db\Expression("FIELD(pd1.platform_id, {$manager->getPlatformId()}) desc"));
                    }
                }]);

        } else {

            $productsQuery->joinWith('manufacturer m', false)
                ->joinWith(['productsDescriptions pd' => function ($query) use ($manager , $_languages) {
                    $query->onCondition(['pd.language_id' => (is_array($_languages) ? $_languages : (int)$_languages),
                         'pd.platform_id' => [\common\classes\platform::defaultId()]
                    ]);
                }])
                ->joinWith(['productsDescriptions pd1' => function ($query) use ($manager, $_languages) {
                    $_pl = array_unique([intval(\Yii::$app->get('platform')->config($manager->getPlatformId())->getPlatformToDescription()), intval(\common\classes\platform::defaultId())]);
                    $query->onCondition(['pd1.language_id' => (is_array($_languages) ? $_languages : (int)$_languages),
                         'pd1.platform_id' => $_pl
                    ])
                        ;
                    if (count($_pl)>1) {
                        $query->addOrderBy(new \yii\db\Expression("FIELD(pd1.platform_id, {$manager->getPlatformId()}) desc"));
                    }
                }])
                ->addSelect(['pd1.products_name', ProductNameDecorator::instance()->listingQueryExpression('pd','pd1').' as products_name'])
                ;
        }

        if (empty($this->post['suggest']) && \common\helpers\Settings::isBackendSearchAggregateProductType()) {
            $productsQuery->addSelect('p.is_bundle, p.products_pctemplates_id, p.without_inventory')
                          ->addSelect(new \yii\db\Expression('EXISTS (SELECT 1 FROM products_attributes pa WHERE pa.products_id = p.products_id) as attr_exists'));
        }

        $productsQuery->sqlProductsModelToPlatform($this->manager->getPlatformId());

        $searchBuilder->addProductsRestriction($productsQuery);
//        $productsQuery->andWhere($filters_where);

//        \Yii::warning(" #### " .print_r($productsQuery->createCommand()->rawSql, true), 'TLDEBUG');
        $products = $productsQuery->all();
        $tree = [];
        if (empty($this->post['suggest'])){ //search for tree
            if ($products){
                $pathes = [];
                
                if ( BACKEND_SEARCH_AGREGATE_PRODUCT_DATA != 'Standard') {
                    $tree =$this->buildPlain($this->manager->getPlatformId(), $products, $searchBuilder);
                } else {
                    foreach($products as $product) {
                        $path = \common\helpers\Product::get_product_path($product->products_id);
                        $_pathArray=explode("_",$path);
                        foreach ($_pathArray as $_path_id) {
                            $priorities[$_path_id] = ($priorities[$_path_id]??0) + 1;
                        }
                    }
                    $products = \yii\helpers\ArrayHelper::getColumn($products, 'products_id');
                    if (!isset($priorities[0])) $priorities[0]=0;
                    arsort($priorities);
                    //put $pathes  in priority order
                    foreach ($priorities as $_key => $_val) {
                        $pathes[$_key]=$_key;
                    }
                    $tree = $this->getChildren(0, $pathes, $products);
                }
            }
        } else { //search for suggest
            $currencies = Yii::$container->get('currencies');
            foreach($productsQuery->limit(20)->all() as $product){
                $ins = \common\models\Product\Price::getInstance($product->products_id);
                $tree[] = ['id' => $product->products_id, 'text' => $product->productsDescriptions[0]->getBackendListingName(), 'price' => $currencies->display_price($ins->getProductPrice(['qty' => 1]), 0, 1)];
            };
        }
        
        return json_encode($tree);
    }
    
    public function getChildren($top, $pathes, $products){
        if (!$pathes) return;
        $level = $this->buildTree($this->manager->getPlatformId(), $top, $products);//children for $path
        
        $trees = $this->skip($level, $pathes, $products, $top);//clear level for n
        foreach($trees as &$tree){
            if (!empty($tree['folder'])){
                $children = $this->getChildren(substr($tree['key'], 1), $pathes, $products);
                if ($children){
                    $tree['children'] = $children;
                }
            }
        }
        return $trees;
    }
    
    public function skip($branch, $only, $products, $cid = null){
        $new_branch=$branch;
        if (is_array($branch)){
            foreach($branch as $key => $item){
                $branch[$key]['selected'] = 0;
                if (!empty($item['folder'])){
                    $cid = str_replace("c", "", $item['key']);
                    if ( !in_array($cid, $only) || !\common\helpers\Categories::products_in_category_count($cid)){
                        unset($branch[$key]);
                    } else {
                        $branch[$key]['expanded'] = 1;
                        $branch[$key]['lazy'] = 0;
                    }
                } else {
                    $pid = preg_replace("/^p(\d+)_(.*)/", '$1', $item['key']);
                    if ( !in_array($pid, $products)){
                        unset($branch[$key]);
                    } else {
                        unset($branch[$key]['children']);
                    }
                }
            }
            //revert position of categories in result array as it path(only) priorities, if products in list exists , no changes
            $new_branch=$branch;
            if (!isset($pid) && count($branch) > 1 && is_array($only) && count($only) > 0) {
               unset($new_branch);
               foreach ($only as $key) {
                $_inBranchPos=-1;
                foreach ($branch as $_idx => $_category) {
                    if ($_category['key'] == "c".$key) {
                          $_inBranchPos=$_idx;
                          break;
                    }
                }
                if ($_inBranchPos>-1)
                $new_branch[]=$branch[$_inBranchPos];
               }
            }
        }

        return array_values($new_branch);
    }

    public function buildPlain($platform_id, $products = [], $searchBuilder = null) {
        $_init_data=[];
        $productIds=\yii\helpers\ArrayHelper::getColumn($products, 'products_id');
        $manager = $this->manager;

        $_assignedCategories=\yii\helpers\ArrayHelper::map((new yii\db\Query())
               ->select('p2c.products_id,p2c.categories_id')
               ->from(\common\models\Products2Categories::tableName()." p2c ")
               ->innerJoin(\common\models\PlatformsCategories::tableName()." pc " , " (pc.categories_id=p2c.categories_id and pc.platform_id in (".(join(",",[intval(\Yii::$app->get('platform')->config($manager->getPlatformId())->getPlatformToDescription()), intval(\common\classes\platform::defaultId())])).")) ")
               ->where(['products_id' => $productIds])
               ->all(),
               'categories_id','categories_id','products_id');

        $pAll = Products::find()->where(['products_id' => $productIds])->asArray()->indexBy('products_id')->all();
        $container = Yii::$container->get('products');
        $_currentLangvId=\Yii::$app->settings->get('languages_id');
        foreach ($products as $product) {

            $categories=[];
            if (isset($_assignedCategories[$product->products_id]))
                 $categories=$_assignedCategories[$product->products_id];
                    
            if (count($product->productsDescriptions) > 1 && !is_array($_currentLangvId) && (int)$_currentLangvId > 0) {
                foreach ($product->productsDescriptions as $_productDescription) {
                    if($_productDescription->language_id == (int)$_currentLangvId) {
                        $description = (!empty($_productDescription->products_internal_name)?
                            $_productDescription->products_internal_name
                            :$_productDescription->products_name??'');
                        $tmpDesc = $_productDescription->attributes;
                        break;
                    }
                }

                if (empty($description)) {
                    $k = 'products_internal_name';
                    $_pda = json_decode(json_encode($product->productsDescriptions), true);

                    $tmpName = array_values(array_filter($_pda, function($el) { return !empty($el['products_internal_name']); }));
                    if (empty($tmpName)) {
                        $k = 'products_name';
                        $tmpName = array_values(array_filter($_pda, function($el) { return !empty($el['products_name']); }));
                    }
                    if (!empty($tmpName)) {
                        $description = $tmpName[0][$k];
                        $tmpDesc = $tmpName[0];
                    }
                }
            } else {
                $description =  (!empty($product->productsDescriptions[0]->products_internal_name)?$product->productsDescriptions[0]->products_internal_name:
                    $product->productsDescriptions[0]->products_name);
                $tmpDesc = $product->productsDescriptions[0]->attributes;
            }

            if (!empty($tmpDesc)) {
                $pInfo = $pAll[$product->products_id] + $tmpDesc;
            } else {
                $pInfo = $pAll[$product->products_id];
            }

            $container->loadProducts($pInfo);
            unset($pAll[$product->products_id]);
            unset($pInfo);
            $products_model = $product->products_model;
            if ( !empty($searchBuilder) && !empty($searchBuilder->getParsedKeywords()) ) {
                $description = \common\helpers\Output::highlight_text($description, $searchBuilder->getParsedKeywords());
                $products_model = \common\helpers\Output::highlight_text($product->products_model, $searchBuilder->getParsedKeywords());
            }
            
            $_product=[
                'key' => "p".$product->products_id.(count($categories) > 0 ?"_".key($categories):""),
                'products_id' => $product->products_id,
                'model' => $products_model,
                'title' => $description,
            ];

            $_product=\common\helpers\Categories::setProductData($_product);
            $_init_data[]=$_product;
        }
        return $_init_data;
    }

    public function buildTree($platform_id, $top = 0 , $products = []) {
        return \common\helpers\Categories::load_tree_slice($platform_id, $top, true, '', true, true, true);
    }

    public function tree() {
        $do = $this->post['do'];
        $platform_id = $this->post['platform_id'];
        $response_data = array();

        if ($do == 'missing_lazy') {
            $category_id = $this->post['id'];
            $selected = $this->post['selected'];
            $req_selected_data = $this->post['selected_data'] ?? '';
            $selected_data = json_decode($req_selected_data, true);
            $products_id = 0;
            if (!is_array($selected_data)) {
                $selected_data = json_decode($selected_data, true);
            }
            if (is_array($selected_data)) {
                $products_id = (int) $selected_data[0];
            }
            if (substr($category_id, 0, 1) == 'c')
                $category_id = intval(substr($category_id, 1));
            // 
            $response_data['tree_data'] = $this->buildTree($platform_id, $category_id);

            foreach ($response_data['tree_data'] as $_idx => $_data) {
                $response_data['tree_data'][$_idx]['selected'] = preg_match("/^p{$products_id}_*/", $_data['key']);
            }
            $response_data = $response_data['tree_data'];
        }
        
        if ($do == 'update_selected') {
            $id = $this->post['id'];
            $selected = $this->post['selected'];
            $select_children = $this->post['select_children'];
            $req_selected_data = $this->post['selected_data'];
            $selected_data = json_decode($req_selected_data, true);
            if (!is_array($selected_data)) {
                $selected_data = json_decode($selected_data, true);
            }

            if (substr($id, 0, 1) == 'p') {
                list($ppid, $cat_id) = explode('_', $id, 2);
                if ($selected) {
                    // check parent categories
                    $parent_ids = array((int) $cat_id);
                    \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0], false);
                    foreach ($parent_ids as $parent_id) {
                        if (!isset($selected_data['c' . (int) $parent_id])) {
                            $response_data['update_selection']['c' . (int) $parent_id] = true;
                            $selected_data['c' . (int) $parent_id] = 'c' . (int) $parent_id;
                        }
                    }
                    if (!isset($selected_data[$id])) {
                        $response_data['update_selection'][$id] = true;
                        $selected_data[$id] = $id;
                    }
                } else {
                    if (isset($selected_data[$id])) {
                        $response_data['update_selection'][$id] = false;
                        unset($selected_data[$id]);
                    }
                }
            } elseif (substr($id, 0, 1) == 'c') {
                $cat_id = (int) substr($id, 1);
                if ($selected) {
                    $parent_ids = array((int) $cat_id);
                    \common\helpers\Categories::get_parent_categories($parent_ids, $parent_ids[0], false);
                    foreach ($parent_ids as $parent_id) {
                        if (!isset($selected_data['c' . (int) $parent_id])) {
                            $response_data['update_selection']['c' . (int) $parent_id] = true;
                            $selected_data['c' . (int) $parent_id] = 'c' . (int) $parent_id;
                        }
                    }
                    if ($select_children) {
                        $children = array();
                        $this->tep_get_category_children($children, $platform_id, $cat_id);
                        foreach ($children as $child_key) {
                            if (!isset($selected_data[$child_key])) {
                                $response_data['update_selection'][$child_key] = true;
                                $selected_data[$child_key] = $child_key;
                            }
                        }
                    }
                    if (!isset($selected_data[$id])) {
                        $response_data['update_selection'][$id] = true;
                        $selected_data[$id] = $id;
                    }
                } else {
                    $children = array();
                    $this->tep_get_category_children($children, $platform_id, $cat_id);
                    foreach ($children as $child_key) {
                        if (isset($selected_data[$child_key])) {
                            $response_data['update_selection'][$child_key] = false;
                            unset($selected_data[$child_key]);
                        }
                    }
                    if (isset($selected_data[$id])) {
                        $response_data['update_selection'][$id] = false;
                        unset($selected_data[$id]);
                    }
                }
            }

            $response_data['selected_data'] = $selected_data;
        }

        return json_encode($response_data);
    }

    private function tep_get_category_children(&$children, $platform_id, $categories_id) {
        if (!is_array($children))
            $children = array();
        foreach ($this->load_tree_slice($platform_id, $categories_id) as $item) {
            $key = $item['key'];
            $children[] = $key;
            if ($item['folder']) {
                $this->tep_get_category_children($children, $platform_id, intval(substr($item['key'], 1)));
            }
        }
    }

    public function run() {

        if (isset($this->post['do'])) {
            return $this->tree();
        } else if (isset( $this->post['search'] ) && !empty($this->post['search']) ){
            return $this->search($this->post['search']);
        }

        $params['searchsuggest'] = \common\models\Products::find()
                        ->innerJoinWith('platform')
                        ->where(['platform_id' => $this->manager->getPlatformId(), 'products_status' => 1])
                        ->count() > 5000;
        if (!$params['searchsuggest']) {
            $category_tree_array = $this->buildTree($this->manager->getPlatformId(), 0);
        }

        $params['rates'] = $this->manager->getOrderTaxRates();

        $params['category_tree_array'] = $category_tree_array;
        $params['queryParams'] = array_merge(['editor/show-basket'], Yii::$app->request->getQueryParams());
        $params['tree_server_url'] = array_merge(['editor/load-tree', 'platform_id' => $this->manager->getPlatformId()], Yii::$app->request->getQueryParams());

        $params['product_display_entities'] = json_encode((defined('BACKEND_SEARCH_SHOW_DATA') ?  array_fill_keys(array_map('trim',explode(",",BACKEND_SEARCH_SHOW_DATA)),true) :[])); 
        $params['product_display_format'] = (defined('BACKEND_SEARCH_AGREGATE_PRODUCT_DATA') ? BACKEND_SEARCH_AGREGATE_PRODUCT_DATA :"Standard");
        $params['min_search_text_lenght'] = (defined('BACKEND_MSEARCH_WORD_LENGTH') && (int)BACKEND_MSEARCH_WORD_LENGTH>0 ? ((int)BACKEND_MSEARCH_WORD_LENGTH-1) : 2);

        $totals = [];
        foreach ($this->manager->getTotalOutput(false) as $total) {
            $totals[$total['code']] = $total;
        }
        $params['totals'] = $totals;

        return $this->render('products-box', $params);
    }

}
