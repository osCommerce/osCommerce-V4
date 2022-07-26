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
        $searchBuilder = new \common\components\SearchBuilder('simple');
        $searchBuilder->setSearchInDesc(SEARCH_IN_DESCRIPTION == 'True');
        $searchBuilder->setSearchInternal(true);
        $searchBuilder->searchInProperty = false;
        $searchBuilder->searchInAttributes = false;
        $searchBuilder->parseKeywords($searchText);
        /*
        $searchBuilder->prepareRequest($searchText);

        $filters_where = $searchBuilder->getProductsArray(false);
        /**/

        $manager = $this->manager;
        $productsQuery = Products::find()
                ->distinct()->alias('p')
                ->select(['p.*', 'pd1.*', ProductNameDecorator::instance()->listingQueryExpression('pd','pd1').' as products_name'])
                ->joinWith('manufacturer m')
                ->joinWith(['productsDescriptions pd' => function ($query) use ($manager) {
                    $query->onCondition(['pd.language_id' => (int)$manager->get('languages_id'), 
                         'pd.platform_id' => [\common\classes\platform::defaultId()]
                    ]);
                }])
                ->joinWith(['productsDescriptions pd1' => function ($query) use ($manager) {
                    $query->onCondition(['pd1.language_id' => (int)$manager->get('languages_id'), 
                         'pd1.platform_id' => [intval(\Yii::$app->get('platform')->config($manager->getPlatformId())->getPlatformToDescription()), intval(\common\classes\platform::defaultId())]
                    ])->orderBy(new \yii\db\Expression("FIELD(pd1.platform_id, {$manager->getPlatformId()}) desc"));
                }])
                ->where(['p.products_status' => 1]);
        
        $productsQuery->sqlProductsModelToPlatform($this->manager->getPlatformId());

        $searchBuilder->addProductsRestriction($productsQuery);
//        $productsQuery->andWhere($filters_where);

        $products = $productsQuery->all();
        $tree = [];
        if (!$this->post['suggest']){ //search for tree
            if ($products){
                $pathes = [0];

                foreach($products as $product){
                    $path = \common\helpers\Product::get_product_path($product->products_id);
                    $pathes = array_merge($pathes, explode("_",$path));
                }
                $products = \yii\helpers\ArrayHelper::getColumn($products, 'products_id');
                $pathes = array_unique($pathes);
                $tree = $this->getChildrend(0, $pathes, $products);
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
    
    public function getChildrend($top, $pathes, $products){
        if (!$pathes) return;
        $level = $this->buildTree($this->manager->getPlatformId(), $top);//children for $path
        
        $trees = $this->skip($level, $pathes, $products, $top);//clear level for n
        foreach($trees as &$tree){
            if ($tree['folder']){
                $children = $this->getChildrend(substr($tree['key'], 1), $pathes, $products);
                if ($children){
                    $tree['children'] = $children;
                }
            }
        }
        return $trees;
    }
    
    public function skip($branch, $only, $products, $cid = null){
        if (is_array($branch)){
            foreach($branch as $key => $item){
                $branch[$key]['selected'] = 0;
                if ($item['folder']){
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
        }
        return array_values($branch);
    }

    public function buildTree($platform_id, $top = 0) {
        return \common\helpers\Categories::load_tree_slice($platform_id, $top, true, '', true, true, true);
    }

    public function tree() {
        $do = $this->post['do'];
        $platform_id = $this->post['platform_id'];
        $response_data = array();

        if ($do == 'missing_lazy') {
            $category_id = $this->post['id'];
            $selected = $this->post['selected'];
            $req_selected_data = $this->post['selected_data'] ?? null;
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

        $params['rates'] = \common\helpers\Tax::getOrderTaxRates();

        $params['category_tree_array'] = $category_tree_array;
        $params['queryParams'] = array_merge(['editor/show-basket'], Yii::$app->request->getQueryParams());
        $params['tree_server_url'] = array_merge(['editor/load-tree', 'platform_id' => $this->manager->getPlatformId()], Yii::$app->request->getQueryParams());
        return $this->render('products-box', $params);
    }

}
