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

namespace backend\components;

use common\classes\Images;
use common\helpers\Html;
use common\models\ProductsDescription;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class ProductsCatalog extends Widget {

    public $post = [];
    public $settings = [
      'popup_product' => true,
      'extra_header' => true,
      'add_sku' => true,
      'search_suggest' => true,
      'only_active' => false,
    ];
    
    public function makeHtml($post = []) {
        if (!empty($post['settings']) && is_array($post['settings'])) {
          $this->settings = array_merge($this->settings, $post['settings']);
          unset($post['settings']);
        }
    
        $this->post = $post;
        
        if (isset($this->post['do'])) {
            return $this->tree();
        } else if (isset( $this->post['search'] ) && !empty($this->post['search']) ){
            return $this->search($this->post['search']);
        }
        
        \common\helpers\Translation::init('admin/customers');
        
        $params = [];
        if (!$this->settings['search_suggest']) {
          $params['searchsuggest'] = false;
        } else {
          $params['searchsuggest'] = \common\models\Products::find()
                        ->innerJoinWith('platform')
                        ->where(['products_status' => 1])
                        ->count() > 50;
        }
        if (!$params['searchsuggest']) {
            $category_tree_array = $this->buildTree(0);
        }
        
        $params['rates'] = \common\helpers\Tax::getOrderTaxRates();
        $params['settings'] = $this->settings;

        $params['category_tree_array'] = $category_tree_array ?? null;
        $params['queryParams'] = array_merge(['categories/seacrh-product'], Yii::$app->request->getQueryParams());
        $params['tree_server_url'] = array_merge(['categories/load-tree'], Yii::$app->request->getQueryParams());
        return $this->render('choose_product_box', $params);
    }
    
    public function make($post = []) {
        $html = $this->makeHtml($post);
        return Yii::$app->controller->renderContent($html);       
    }
    
    public function search($searchText){
        $searchBuilder = new \common\components\SearchBuilder('simple');
        $searchBuilder->setSearchInDesc(SEARCH_IN_DESCRIPTION == 'True');
        $searchBuilder->setSearchInternal(true);
        $searchBuilder->searchInProperty = false;
        $searchBuilder->searchInAttributes = false;
        $searchBuilder->parseKeywords($searchText);

        
        
        $productsQuery = \common\models\Products::find()
                ->distinct()->alias('p')
                ->select(['p.*', 'pd.*'])
                ->addSelect(['products_name_res' => new \yii\db\Expression(\backend\models\ProductNameDecorator::instance()->listingQueryExpression('pd1','pd'))] )
                ->joinWith('manufacturer m')
                ->wDescription('pd', \common\helpers\Language::get_default_language_id())
                ->wDescription('pd1')
                ;
        if (!empty($this->setting['only_active'])) {
            $productsQuery->andWhere(['p.products_status' => 1]);
        }
        
        //$productsQuery->sqlProductsModelToPlatform($this->manager->getPlatformId());

        $searchBuilder->addProductsRestriction($productsQuery);

        $tree = [];
        if (!($this->post['suggest']??null)){ //search for tree
            $products = $productsQuery->all();
            if ($products){
                $pathes = [0];

                foreach($products as $product){
                    $path = \common\helpers\Product::get_product_path($product->products_id);
                    $pathes = array_merge($pathes, explode("_",$path));
                }
                $products = \yii\helpers\ArrayHelper::getColumn($products, 'products_id');
                $pathes = array_unique($pathes);
                $tree = $this->getChildren(0, $pathes, $products);
            }
        } else { //search for suggest
            $currencies = Yii::$container->get('currencies');
            foreach($productsQuery->limit(20)->asArray()->all() as $product){
                $ins = \common\models\Product\Price::getInstance($product['products_id']);
                $tree[] = [
                  'id' => $product['products_id'],
                  //'text' => $product->productsDescriptions[0]->getBackendListingName(),
                  'text' => $product['products_name_res'] ?? '',
                  'image' => '',
                  'price' => $currencies->display_price($ins->getProductPrice(['qty' => 1]), 0, 1)
                  ];
            };
        }
        
        return json_encode($tree);
    }
    
    public function getChildren($top, $pathes, $products){
        if (!$pathes) return;
        $level = $this->buildTree($top);//children for $path
        
        $trees = $this->skip($level, $pathes, $products, $top);//clear level for n
        foreach($trees as &$tree){
            if ($tree['folder'] ?? null){
                $children = $this->getChildren(substr($tree['key'], 1), $pathes, $products);
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
                if ($item['folder'] ?? null){
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
    
    public function tree() {
        $do = $this->post['do'];
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
            $response_data['tree_data'] = $this->buildTree($category_id);

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
                        $this->tep_get_category_children($children, $cat_id);
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
                    $this->tep_get_category_children($children, $cat_id);
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

        return json_encode($response_data, JSON_INVALID_UTF8_IGNORE );
    }
    
    private function tep_get_category_children(&$children, $categories_id) {
        if (!is_array($children))
            $children = array();
        foreach ($this->load_tree_slice($categories_id) as $item) {
            $key = $item['key'];
            $children[] = $key;
            if ($item['folder']) {
                $this->tep_get_category_children($children, intval(substr($item['key'], 1)));
            }
        }
    }
    
    public function buildTree($category_id = 0) {
        //$platform_id = \common\classes\platform::defaultId();
        //return \common\helpers\Categories::load_tree_slice($platform_id, $top, true, '', true, true, true);
        
         //public static function load_tree_slice($platform_id, $category_id, $activeProducts = false, $search = '', $inner = false, $innerCategory = false, $activeCategories = false){
          $tree_init_data = array();

          $category_selected_state = true;
          if ( $category_id>0 ) {
            $_check = tep_db_fetch_array(tep_db_query(
              "SELECT COUNT(*) AS c FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id='" . (int)$category_id . "' "
            ));
            $category_selected_state = $_check['c']>0;
          }
          
          $languages_id = \Yii::$app->settings->get('languages_id');

          $get_categories_r = tep_db_query(
            "SELECT CONCAT('c',c.categories_id) as `key`, cd.categories_name as title, c.categories_image ".
            "FROM " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
            "WHERE cd.categories_id=c.categories_id and cd.language_id='" . $languages_id . "' AND cd.affiliate_id=0 and c.parent_id='" . (int)$category_id . "' ".
            "order by c.sort_order, cd.categories_name"
          );
          while ($_categories = tep_db_fetch_array($get_categories_r)) {
              //$_categories['parent'] = (int)$category_id;
              $_categories['folder'] = true;
              $_categories['lazy'] = true;
              if (is_file(Images::getFSCatalogImagesPath() . $_categories['categories_image'])) {
                  $_categories['image'] = Html::img(Images::getWSCatalogImagesPath() . $_categories['categories_image']);
              }
              $_categories['selected'] = $category_selected_state && !!ArrayHelper::getValue($_categories, 'selected');
              $tree_init_data[] = $_categories;
          }
          $get_products_r = tep_db_query(
            "SELECT concat('p',p.products_id,'_',p2c.categories_id) AS `key`, pd.products_name as title, pd.products_description, pd.products_description_short, p.products_id, p.products_model ".
            "from ".TABLE_PRODUCTS_DESCRIPTION." pd , ".TABLE_PRODUCTS_TO_CATEGORIES." p2c, ".TABLE_PRODUCTS." p ".
            "WHERE pd.products_id=p.products_id and pd.language_id='".$languages_id."' and pd.platform_id='".\common\classes\platform::defaultId()."' and p2c.products_id=p.products_id and p2c.categories_id='".(int)$category_id."' ".
            "order by p.sort_order, title"
          );
          $currencies = new \common\classes\Currencies();
          if ( tep_db_num_rows($get_products_r)>0 ) {
              while ($_product = tep_db_fetch_array($get_products_r)) {
                  if (!isset($_product['title']) || !$_product['title']) {
                      $productsDescription = ProductsDescription::find()
                          ->select('products_name')
                          ->where([
                              'products_id' => $_product['products_id']
                          ])
                          ->andWhere("products_name <> ''")
                          ->asArray()->one();
                      if (isset($productsDescription['products_name'])) {
                          $_product['title'] = $productsDescription['products_name'];
                      }
                  }
                //$_product['parent'] = (int)$category_id;
                  $price = \common\helpers\Product::get_products_price($_product['products_id']);
                $_product['selected'] = false;//$category_selected_state && !!$_product['selected'];
                $_product['price_ex'] = $currencies->display_price($price, 0, 1, false);
                $_product['image'] = \common\classes\Images::getImage($_product['products_id'], 'Small');

                if ($_product['products_description_short']) {
                    $description = $_product['products_description_short'];
                } else {
                    $description = $_product['products_description'];
                }
                if (strlen($description) > 500) {
                    $description = strip_tags($description);
                    if (strlen($description) > 500) {
                        $description = mb_substr($description, 0, 500) . '...';
                    }
                }
                $_product['description'] = trim($description);

                $thumbnail = \common\classes\Images::getImage($_product['products_id']);
                if ($thumbnail) {
                    $thumbnail = '<span style="display: none;" class="product-thumbnail">' . $thumbnail . '</span> ';
                } else {
                    $thumbnail = '<span style="display: none;" class="product-thumbnail-ico fancytree-icon icon-cubes"></span> ';
                }
                $_product['stock'] = \common\helpers\Product::get_products_stock($_product['products_id']);
                if (empty($_product['title'])){
                    $_product['title'] = \common\helpers\Product::get_products_name($_product['products_id']);
                }
                if (ArrayHelper::getValue($this->settings, 'add_sku') && !empty($_product['products_model'])) {
                  $_product['title'] .= ' (' . $_product['products_model'] . ')';
                }
                $_product['name'] = $_product['title'];
                $_product['title'] = '<span class="' . ($_product['stock'] == 0 ? ' empty-stock' : '') . '">' . $thumbnail . $_product['title'] . '</span>';
                $tree_init_data[] = $_product;
              }
          }

          return $tree_init_data;
        
    }

}
