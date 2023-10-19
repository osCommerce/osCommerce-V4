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

use backend\models\ProductNameDecorator;
use Yii;
use yii\helpers\ArrayHelper;
use common\classes\platform;

class StocktakingCostController extends Sceleton {

    public $acl = ['BOX_HEADING_REPORTS', 'BOX_REPORTS_STOCK_COST'];
    public $exc_cat_id = array();

    public $selected_platforms;
    public $selected_categories;
    public $showcolumns = ['sp', 'sp_', 'pp', 'pp_'];
    public $groupby = [];
    public $keywords;
    public $selected_status;
    public $selected_brand;
    public $selected_supplier;
    public $status;
    public $search = '';
    public function __construct($id, $module = null) {        
        \common\helpers\Translation::init('admin/stocktaking-cost');
//        $this->exc_cat_id[1] = 45;
//        $this->exc_cat_id[2] = 41;
        if ($id != 'console/stock-taking-cost-report') {
            parent::__construct($id, $module);
        }
    }
    

    public function actionIndex() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        \common\helpers\Inventory::updateNameByUprid([], $languages_id);

        $this->selectedMenu = array('reports', 'stocktaking-cost');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('stocktaking-cost/index'), 'title' => BOX_REPORTS_STOCK_COST);
        $this->view->headingTitle = BOX_REPORTS_STOCK_COST;

        $this->topButtons[] = '<span class="btn btn-primary" onclick="_export(this);">' . TEXT_EXPORT . '</span>';

        $platforms = platform::getList(false);


        $this->parseFilterOptions(Yii::$app->request->get());

        $this->status = [
          '' => TEXT_ALL,
          '1' => TEXT_ACTIVE,
          '-1' => TEXT_INACTIVE
        ];

        $categories = \common\helpers\Categories::get_category_tree(0, '&nbsp;&nbsp;', '0', '', true);
        foreach ($categories as $key => &$category) {
            if ($key == 0)
                unset($categories[$key]);
            $category['text'] = html_entity_decode($category['text']);
        }

        $suppliers = \common\models\Suppliers::find()->select('suppliers_name, suppliers_id')->asArray()->indexBy('suppliers_id')->column(); //[0 => TEXT_ALL] +
        $brands = \common\helpers\Manufacturers::getManufacturersList(); //[0 => TEXT_ALL] + 
//        $stockIndications =\common\classes\StockIndication::get_variants();
//        $stockDeliveryTerms =\common\classes\StockIndication::get_delivery_terms();

        $dirScan = self::filesRoot();
        $filesList = array();
        if ( is_dir($dirScan) ) {
            $files = \yii\helpers\FileHelper::findFiles($dirScan, [
                'recursive' => false,
                'except' => ['.num', '.svn', 'images', 'processed'],
                //'only' => ['*.*'],
            ]);
            $files = array_map('basename', $files);
            rsort($files, SORT_NATURAL);
            foreach($files as $file) {
                $filesList[$file] = $file;
            }
        }

        $this->view->CostTable = $this->getTable($this->showcolumns);
        return $this->render('index', [
                    'platforms' => \yii\helpers\ArrayHelper::map($platforms, 'id', 'text'),
                    'first_platform_id' => platform::firstId(),
                    'default_platform_id' => platform::defaultId(),
                    'isMultiPlatforms' => platform::isMulti(),
                    'suppliers' => $suppliers,
                    'brands' => $brands,
                    'filesList' => $filesList,
                    //'excluded' => EXCLUDE_ID . implode(", ", $exc_cat_name),
		  ]);
    }
    
    public function getTable($columns = false) {
        $all = array(
          'cat' =>
            array(
                'title' => TABLE_HEADING_CATEGORY,
                'not_important' => 0,
                'width' => '23%'
            ),
          '_1' =>
            array(
                'title' => TEXT_SKU,
                'not_important' => 0,
                'width' => '7%',
            ),
          '_2' =>
            array(
                'title' => TABLE_HEADING_PRODUCTS,
                'not_important' => 0,
                'width' => '33%',
            ),
          '_3' =>
            array(
                'title' => TABLE_HEADING_QUANTITY,
                'not_important' => 0,
                'width' => '5%'
            ),
          '_4' =>
            array(
                'title' => TABLE_HEADING_TAX,
                'not_important' => 0,
                'width' => '5%'
            ),
          'sp' =>
            array(
                'title' => TABLE_HEADING_SP,
                'not_important' => 0,
                'width' => '5%'
            ),
          'sp_' =>
            array(
                'title' => TABLE_HEADING_TSP,
                'not_important' => 0,
                'width' => '5%'
            ),
          'pp' =>
            array(
                'title' => TABLE_HEADING_PP,
                'not_important' => 0,
                'width' => '5%'
            ),
          'pp_' =>
            array(
                'title' => TABLE_HEADING_TPP,
                'not_important' => 0,
                'width' => '5%'
            ),
          '_5' =>
            array(
                'title' => TEXT_LABEL_BRAND,
                'not_important' => 0,
                'width' => '10%'
            ),
          '_6' =>
            array(
                'title' => TEXT_SUPPLIER,
                'not_important' => 0,
                'width' => '10%'
            ),
          '_7' =>
            array(
                'title' => TEXT_STOCK_INDICATION,
                'not_important' => 0,
                'width' => '10%'
            ),
          '_8' =>
            array(
                'title' => TEXT_STOCK_DELIVERY_TERMS,
                'not_important' => 0,
                'width' => '10%'
            ),
        );

        $ret = [];

        if (!is_array($columns)) {
          $ret = $all;

        } else {
          foreach ($all as $k => $v) {
            if (substr($k,0,1)=='_' || in_array(trim($k, ' ._'), $columns)) {
              $ret[] = $v;
            }
          }
        }
        return $ret;

    }
    
    public function build() {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $responseList = [];
        $head = new \stdClass();
        $categories = [];
        if ($this->selected_categories) {
            foreach ($this->selected_categories as $category) {
                $categories[] = $category;
                \common\helpers\Categories::get_subcategories($categories, $category);
            }
        } else {
            \common\helpers\Categories::get_subcategories($categories, 0);
        }

        $included_categories_query = tep_db_query("SELECT c.categories_id, c.parent_id, cd.categories_name
				FROM " . TABLE_CATEGORIES . " c  
				LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON c.categories_id = cd.categories_id
				WHERE cd.language_id = FLOOR($languages_id) AND cd.affiliate_id=0
            ");

        $inc_cat = array();
        while ($included_categories = tep_db_fetch_array($included_categories_query)) {
          $inc_cat[] = array (
             'id' => $included_categories['categories_id'],
             'parent' => $included_categories['parent_id'],
             'name' => $included_categories['categories_name']);
          }
        $cat_info = array();

        $cat_info[0] = array (
          'parent'=> 0,
          'name'  => defined('TEXT_TOP')?TEXT_TOP : 'Top',
          'path'  => 0,
          'link'  => '<a target="_blank" href="' . tep_href_link('categories', 'listing_type=category&category_id=0') . '"><nobr>' . (defined('TEXT_TOP')?TEXT_TOP : 'Top') . '</nobr></a>&nbsp;&raquo;&nbsp; ',
          'cleanlink'  => defined('TEXT_TOP')?TEXT_TOP : 'Top'
           );

        for ($i=0; $i<sizeof($inc_cat); $i++) {
          $cat_info[$inc_cat[$i]['id']] = array (
            'parent'=> $inc_cat[$i]['parent'],
            'name'  => $inc_cat[$i]['name'],
            'path'  => $inc_cat[$i]['id'],
            'link'  => '',
            'cleanlink'  => ''
             );
        }

        for ($i=0; $i<sizeof($inc_cat); $i++) {
          $cat_id = $inc_cat[$i]['id'];
          while ($cat_info[$cat_id]['parent'] != 0){
            $cat_info[$inc_cat[$i]['id']]['path'] = $cat_info[$cat_id]['parent'] . '_' . $cat_info[$inc_cat[$i]['id']]['path'];
            $cat_id = $cat_info[$cat_id]['parent'];
            }
          $link_array = explode('_', $cat_info[$inc_cat[$i]['id']] ['path']);
          for ($j=0; $j<sizeof($link_array); $j++) {
            $cat_info[$inc_cat[$i]['id']]['link'] .= '<a target="_blank" href="' . tep_href_link('categories', 'listing_type=category&category_id=' . $link_array[$j]) . '"><nobr>' . $cat_info[$link_array[$j]]['name'] . '</nobr></a>&nbsp;&raquo;&nbsp; ';
            $cat_info[$inc_cat[$i]['id']]['cleanlink'].= $cat_info[$link_array[$j]]['name'] . '/';
            }
          }

/*
        $products_query = tep_db_query("SELECT p.products_id, p.products_quantity, sp.specials_new_products_price, p.products_price, p2c.categories_id, min(sup.suppliers_price) as suppliers_price, ".ProductNameDecorator::instance()->listingQueryExpression('pd','')." AS products_name FROM " .TABLE_PRODUCTS." p
                         LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd ON p.products_id = pd.products_id 
                         LEFT JOIN ".TABLE_SPECIALS ." sp ON p.products_id = sp.products_id 
                         LEFT JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ON p.products_id = p2c.products_id 
                         LEFT JOIN ".TABLE_SUPPLIERS_PRODUCTS." sup ON sup.products_id = p.products_id 
                         WHERE p.products_status = 1 
                         AND pd.language_id = FLOOR($languages_id) AND pd.platform_id='".intval(\common\classes\platform::defaultId())."'
                         AND p2c.categories_id not in (" . implode(",", $this->exc_cat_id) . ")
                         group by p.products_id
                         ORDER BY p2c.categories_id, pd.products_name");

*/
        $selectColumns = [
          'p.products_id', 'p.products_tax_class_id', 'p.products_price', 'p.manufacturers_id',
          'backend_products_name' => new \yii\db\Expression(ProductNameDecorator::instance()->listingQueryExpression(TABLE_PRODUCTS_DESCRIPTION,'')),
          'uprid' => new \yii\db\Expression('ifnull(i.products_id, p.products_id)'),
          'sku' => new \yii\db\Expression('ifnull(i.products_model, p.products_model)'),
          'stock_indication_id' => new \yii\db\Expression('ifnull(i.stock_indication_id, p.stock_indication_id)'),
          'stock_delivery_terms_id' => new \yii\db\Expression('ifnull(i.stock_delivery_terms_id, p.stock_delivery_terms_id)'),
          'stock' => new \yii\db\Expression('ifnull(i.products_quantity, p.products_quantity)'),
          ];

        $pq = \common\models\Products::find()->alias('p')
            ->joinWith(['backendDescription'], false)
            ->leftJoin(['i' => TABLE_INVENTORY], ' p.products_id = i.prid and i.non_existent=0 ')
//->andWhere('i.non_existent=0 ')// debug only inventory
//->andWhere('p.products_id=32 ')// debug 
            ->addSelect($selectColumns)
            ->groupBy($selectColumns)
            ;
        if (!empty($this->search)) {
            $pq->andWhere([
              'or',
              ['like', 'i.products_model', $this->search],
              ['like', 'p.products_model', $this->search],
              ['like', TABLE_PRODUCTS_DESCRIPTION . '.products_name', $this->search],
            ]);
        }


        if ($this->selected_categories || in_array('cat', $this->showcolumns)) {
          $pq->leftJoin(['p2c' => TABLE_PRODUCTS_TO_CATEGORIES], ' p.products_id = p2c.products_id ')
              ->addSelect('p2c.categories_id');
        }
        if (in_array('cat', $this->showcolumns)) {
             $pq->addOrderBy(new \yii\db\Expression('ifnull(p2c.categories_id, 0)'))
              ;
        }

        if (in_array('pp', $this->showcolumns)) {
          $pq->leftJoin(['spp' => 'suppliers_products'], ' p.products_id = spp.products_id and spp.uprid=ifnull(i.products_id, p.products_id) and spp.status=1 /*and spp.is_default=1*/')
              ->addSelect('spp.suppliers_id, spp.suppliers_price')
              ;
        }

        if ($this->selected_categories) {
            $pq->andWhere(['p2c.categories_id' => $categories]);
        }

        if (!empty($this->selected_platforms)) {
            $pq->leftJoin(['p2p' => TABLE_PLATFORMS_PRODUCTS], ' p.products_id = p2p.products_id ');
            $pq->andWhere(['p2p.platform_id' => $this->selected_platforms]);
        }

        if (!empty($this->selected_status)) {
          $pq->andWhere(['p.products_status' => ($this->selected_status>0?1:0)]);
        }

        if (!empty($this->selected_brand)) {
            $pq->andWhere(['p.manufacturers_id' => $this->selected_brand]);
        }

        if (!empty($this->selected_supplier)) {
            if (!in_array('pp', $this->showcolumns)) {
                $pq->leftJoin(['spp' => 'suppliers_products'], ' p.products_id = spp.products_id and spp.uprid=ifnull(i.products_id, p.products_id) and spp.status=1 /*and spp.is_default=1*/')
              ;
            }
            $pq->andWhere(['spp.suppliers_id' => $this->selected_supplier]);
        }

        if (in_array('cat', $this->groupby)) {
          $pq->addOrderBy('p2c.categories_id');
        }

        $pq->addOrderBy('backend_products_name, sku');
//echo $pq->createCommand()->rawSql;die;

        /** @var \common\classes\Currencies $currencies*/
        $currencies = Yii::$container->get('currencies');

        $suppliers = \common\models\Suppliers::find()->select('suppliers_name, suppliers_id')->asArray()->indexBy('suppliers_id')->column();
        $brand = \common\helpers\Manufacturers::getManufacturersList();
        $stockIndications = \common\classes\StockIndication::get_variants();
        if ($stockIndications[0]['id']=='') {
            $stockIndications[0]['id'] = 0;
        }
        $stockIndications = \yii\helpers\ArrayHelper::index($stockIndications, 'id');
        $stockDeliveryTerms = \common\classes\StockIndication::get_delivery_terms();
        if ($stockDeliveryTerms[0]['id']=='') {
            $stockDeliveryTerms[0]['id'] = 0;
        }
        $stockDeliveryTerms = \yii\helpers\ArrayHelper::index($stockDeliveryTerms, 'id');

        $ttls = [];
        $tqt_item = $tprice_item = $tcost_item = 0;

        $result = Yii::$app->getDb()->createCommand($pq->createCommand()->rawSql)->queryAll(); //model skips all variations 
            //$pq->asArray()->all(); //no time to find out solution 
        $curCat = $curProd = null;

        foreach ($result as $products) {

            //$products['suppliers_price'] = $currencies->format_clear(\common\helpers\Suppliers::getDefaultProductPrice($products['uprid']));
            $products['suppliers_price'] = $currencies->format_clear($products['suppliers_price']);
            $cost_item = $currencies->format_clear($products['suppliers_price'] * $products['stock']);
            if ($products['products_tax_class_id']>0) {
                $rate = \common\helpers\Tax::get_tax_rate($products['products_tax_class_id']);
                $price = \common\helpers\Tax::add_tax_always(\common\helpers\Product::get_products_price($products['uprid']), $rate);
                $products['products_price'] = $currencies->format_clear($price, 1);
            } else {
                $products['products_price'] = $currencies->format_clear(\common\helpers\Product::get_products_price($products['uprid'], 1));
            }
            $price_item = $currencies->format_clear($products['products_price'] * $products['stock']);
            if ($products['uprid'] != $products['products_id']) {
                $productName = \common\helpers\Inventory::get_inventory_name_by_uprid($products['uprid']);
            } else {
                $productName = $products['backend_products_name'];
            }
            $tqt_item += $products['stock'];
            $tprice_item += $price_item;
            $tcost_item += $cost_item;
            $catName = '';
            if (in_array('cat', $this->showcolumns) /*&& isset($products['categories_id'])*/) {
                $catName = $cat_info[(int)$products['categories_id']]['link']??'';
            }

            if (in_array('cat', $this->groupby)) {
                if (!isset($ttls['cat'][$products['categories_id']])) {
                    if (is_null($curCat)) {
                        $curCat = (int)$products['categories_id'];
                    }
                    $ttls['cat'][$products['categories_id']] =
                        ['stock' => $products['stock'], 'cost' => $cost_item, 'price' => $price_item, 'num' => 0];
                } else {
                    $ttls['cat'][$products['categories_id']]['stock'] += $products['stock'];
                    $ttls['cat'][$products['categories_id']]['cost'] += $cost_item;
                    $ttls['cat'][$products['categories_id']]['price'] += $price_item;
                    $ttls['cat'][$products['categories_id']]['num'] ++;
                }
            }
            // calc variation totals
            if (in_array('prod', $this->groupby) && $products['products_id'] != $products['uprid']) {
                //calc total of all variations
                if (!isset($ttls['prod'][$products['products_id']])) {
                    if (is_null($curProd)) {
                        $curProd = $products['products_id'];
                    }
                    $ttls['prod'][$products['products_id']] = array_merge($products,
                        ['stock' => $products['stock'], 'cost' => $cost_item, 'price' => $price_item, 'name' => $products['backend_products_name'], 'num' => 0]);
                } else {
                    $ttls['prod'][$products['products_id']]['stock'] += $products['stock'];
                    $ttls['prod'][$products['products_id']]['cost'] += $cost_item;
                    $ttls['prod'][$products['products_id']]['price'] += $price_item;
                    $ttls['prod'][$products['products_id']]['num'] ++;
                }
            }

            if ($curProd != $products['products_id'] && isset($ttls['prod'][$curProd]) ){
                if ($ttls['prod'][$curProd]['num']>0) {
                // show product variations total
                    $r = [];
                    if (in_array('cat', $this->showcolumns)) {
                        $r[] = '<div class="orange">&nbsp;&nbsp;&nbsp;' . TEXT_TOTAL . '</div>';
                    }
                    $r = array_merge($r, [
                        '<div class="orange"></div>',//sku
                        '<div class="orange"><a href="' . tep_href_link('categories/productedit', 'pID=' . $curProd) . '" target="_blank">' . $ttls['prod'][$curProd]['backend_products_name'] . '</a></div>', //product
                        '<div class="orange">' . $ttls['prod'][$curProd]['stock'] . '</div>', //qty
                        '<div class="orange">' . \common\helpers\Tax::get_tax_class_title($ttls['prod'][$curProd]['products_tax_class_id']) . '</div>' //tax
                      ]);
                    if (in_array('sp', $this->showcolumns)) {
                        $r = array_merge($r, [
                            '<div class="orange">' . '</div>', //sale price
                            '<div class="orange">' . $ttls['prod'][$curProd]['price'] . '</div>', //sale total
                          ]);
                    }
                    if (in_array('pp', $this->showcolumns)) {
                        $r = array_merge($r, [
                            '<div class="orange">' . '</div>', //purchase price
                            '<div class="orange">' . $ttls['prod'][$curProd]['cost'] . '</div>', //purchase total
                          ]);
                    }
                    $r = array_merge($r, [
                        '<div class="orange">' . ($brand[$ttls['prod'][$curProd]['manufacturers_id']]??''). '</div>', //brand
                        '<div class="orange">' . ($suppliers[$ttls['prod'][$curProd]['suppliers_id']]??''). '</div>', //supplier
                        '<div class="orange"></div>', //indication
                        '<div class="orange"></div>', //delivery term
                    ]);
                    $responseList[] = $r;
                }
                $curProd = $products['products_id'];
            }

            // show cat total
            if ($curCat != (int)ArrayHelper::getValue($products, 'categories_id') && in_array('cat', $this->groupby) ) {
                $r = [
                    '<div class="orange">' . TEXT_TOTAL . ' ' . $cat_info[$curCat]['link'] . '</div>', //cat
                    '<div class="orange"></div>',//sku
                    '<div class="orange"></div>',//product
                    '<div class="orange">' . $ttls['cat'][$curCat]['stock'] . '</div>', //qty
                    '<div class="orange"></div>' //tax
                  ];
                if (in_array('sp', $this->showcolumns)) {
                    $r = array_merge($r, [
                        '<div class="orange"></div>', //sale price
                        '<div class="orange">' . $ttls['cat'][$curCat]['price'] . '</div>', //sale total
                      ]);
                }
                if (in_array('pp', $this->showcolumns)) {
                    $r = array_merge($r, [
                        '<div class="orange"></div>', //purchase price
                        '<div class="orange">' . $ttls['cat'][$curCat]['cost'] . '</div>', //purchase total
                      ]);
                }
                $r = array_merge($r, [
                    '<div class="orange"></div>', //brand
                    '<div class="orange"></div>', //supplier
                    '<div class="orange"></div>', //indication
                    '<div class="orange"></div>', //delivery term 
                ]);
                $responseList[] = $r;
                $curCat = (int)$products['categories_id'];
            }

            // product/variation line
            $r = [];
            if (in_array('cat', $this->showcolumns)) {
                if (!in_array('cat', $this->groupby) || $ttls['cat'][$curCat]['num']==0) {
                    $r[] = '<div class="orange">' . $catName . '</div>'; //cat
                } else {
                    $r[] = '<div class="orange">' .  '</div>'; //cat
                }
                $ttls['cat'][$curCat]['num']++;
            }
            $r = array_merge($r, [
                '<div class="orange">' . $products['sku'] . '</div>',//sku
                '<div class="orange"><a href="' . tep_href_link('categories/productedit', 'pID=' . $products['products_id']) . '" target="_blank">' . $productName . '</div>', //product
                '<div class="orange">' . $products['stock'] . '</div>', //qty
                '<div class="orange">' . \common\helpers\Tax::get_tax_class_title($products['products_tax_class_id']) . '</div>' //tax
              ]);
            if (in_array('sp', $this->showcolumns)) {
                $r = array_merge($r, [
                    '<div class="orange">' . $products['products_price'] . '</div>', //sale price
                    '<div class="orange">' . $price_item . '</div>', //sale total
                  ]);
            }
            if (in_array('pp', $this->showcolumns)) {
                $r = array_merge($r, [
                    '<div class="orange">' . $products['suppliers_price'] . '</div>', //purchase price
                    '<div class="orange">' . $cost_item . '</div>', //purchase total
                  ]);
            }
            $r = array_merge($r, [
                '<div class="orange">' . ($brand[$products['manufacturers_id']]??''). '</div>', //brand
                '<div class="orange">' . ($suppliers[$products['suppliers_id']]??''). '</div>', //supplier
                '<div class="orange">' . ($stockIndications[$products['stock_indication_id']]['text']??''). '</div>', //indication
                '<div class="orange">' . ($stockDeliveryTerms[$products['stock_delivery_terms_id']]['text']??''). '</div>', //delivery term
            ]);
            $responseList[] = $r;

        }

        /// final by variations
        if (isset($ttls['prod'][$curProd]) && $ttls['prod'][$curProd]['num']>0) {// show product variations total
            $r = [];
            if (in_array('cat', $this->showcolumns)) {
                $r[] = '<div class="orange">' . TEXT_TOTAL . '</div>'; //cat
            }
            $r = array_merge($r, [
                '<div class="orange"></div>',//sku
                '<div class="orange"><a href="' . tep_href_link('categories/productedit', 'pID=' . $curProd) . '" target="_blank">' . $ttls['prod'][$curProd]['backend_products_name'] . '</a></div>', //product
                '<div class="orange">' . $ttls['prod'][$curProd]['stock'] . '</div>', //qty
                '<div class="orange">' . \common\helpers\Tax::get_tax_class_title($ttls['prod'][$curProd]['products_tax_class_id']) . '</div>' //tax
              ]);
            if (in_array('sp', $this->showcolumns)) {
                $r = array_merge($r, [
                    '<div class="orange">' . '</div>', //sale price
                    '<div class="orange">' . $ttls['prod'][$curProd]['price'] . '</div>', //sale total
                  ]);
            }
            if (in_array('pp', $this->showcolumns)) {
                $r = array_merge($r, [
                    '<div class="orange">' . '</div>', //purchase price
                    '<div class="orange">' . $ttls['prod'][$curProd]['cost'] . '</div>', //purchase total
                  ]);
            }
            $r = array_merge($r, [
                '<div class="orange">' . ($brand[$ttls['prod'][$curProd]['manufacturers_id']]??''). '</div>', //brand
                '<div class="orange">' . ($suppliers[$ttls['prod'][$curProd]['suppliers_id']]??''). '</div>', //supplier
                '<div class="orange"></div>', //indication
                '<div class="orange"></div>', //delivery term
            ]);
            $responseList[] = $r;
        }


        /// final by category
        if (in_array('cat', $this->groupby)) {
            $r = [
                '<div class="orange">' . TEXT_TOTAL . ' ' . $cat_info[$curCat]['link'] . '</div>', //cat
                '<div class="orange"></div>',//sku
                '<div class="orange"></div>',//product
                '<div class="orange">' . $ttls['cat'][$curCat]['stock'] . '</div>', //qty
                '<div class="orange"></div>' //tax
              ];
            if (in_array('sp', $this->showcolumns)) {
                $r = array_merge($r, [
                    '<div class="orange"></div>', //sale price
                    '<div class="orange">' . $ttls['cat'][$curCat]['price'] . '</div>', //sale total
                  ]);
            }
            if (in_array('pp', $this->showcolumns)) {
                $r = array_merge($r, [
                    '<div class="orange"></div>', //purchase price
                    '<div class="orange">' . $ttls['cat'][$curCat]['cost'] . '</div>', //purchase total
                  ]);
            }
            $r = array_merge($r, [
                '<div class="orange"></div>', //brand
                '<div class="orange"></div>', //supplier
                '<div class="orange"></div>', //indication
                '<div class="orange"></div>', //вудшмукн еукьы
            ]);
            $responseList[] = $r;
        }

    
        $head->list = [
            TABLE_HEADING_TQT_ITEM => $tqt_item,
            TABLE_HEADING_TCOST_PURCHASE_PRICE => number_format($tcost_item, 2),
            TABLE_HEADING_TCOST_SALE_PRICE => number_format($tprice_item, 2)
        ];
        return ['responseList' => $responseList, 'head' => $head];
    }

    public function actionList() {        

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_page_number = ($start / $length) + 1;


        $this->parseFilterOptions(Yii::$app->request->get());
        $data = $this->build();
        $responseList = $data['responseList'];
                
        $response = array(
            'draw' => $draw,
            'recordsTotal' => count($responseList),
            'recordsFiltered' => count($responseList),
            'data' => $responseList,
            'head' => $data['head']
        );
        echo json_encode($response);
    }

    public function actionDownload() {
        $file = Yii::$app->request->get('file', '');
        if (!empty($file)) {
            $file = basename($file);
            $dir = self::filesRoot();
            if (is_file($dir.$file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($dir.$file));
                readfile($dir.$file);
                exit;
            }
        }
    }

    public function actionExport() {
        $this->parseFilterOptions(Yii::$app->request->get());
        $data = $this->build();
        $head = $this->getTable($this->showcolumns);

        $header = [];
        foreach($head as $m){
          $header[] = $m['title'];
        }

        $filename = 'stocktaking_report_' . strftime('%Y%b%d_%H%M');
        $writer = \common\helpers\Export::getWriter($filename);
        \common\helpers\Export::addRowToWriter($writer, $header);

        foreach($data['responseList'] as $row){
            $newArray = [];
            foreach ($row as $k => $v) {
                $vv = trim(strip_tags($v));
                $vv = str_replace(['&nbsp;&raquo;&nbsp;', '&nbsp;', ], [' / ', '', ], $vv);
                if ($k>1) {//skip SKU column to save leading zeros
                    //type cast
                    if (preg_match('/^[0-9\-]+$/', $vv)) {
                        $vv = intval($vv);
                    } elseif (preg_match('/^[0-9\.\-]+$/', $vv)) {
                        $vv = floatval($vv);
                    }

                }
                $newArray[] = $vv;
            }
            /*
            $newArray = array_map(function($v){
                $vv = trim(strip_tags($v));
                $vv = str_replace(['&nbsp;&raquo;&nbsp;', '&nbsp;', ], [' / ', '', ], $vv);
                return $vv;
            }, $row);*/
            \common\helpers\Export::addRowToWriter($writer, $newArray);
        }
        
        $writer->close();
        exit;

    }

    public function actionGenerate() {
        global $languages_id, $language;
        $languages_id = \common\helpers\Language::get_default_language_id();
        \Yii::$app->settings->set('languages_id', $languages_id);

        \common\helpers\Translation::init('admin/main');
        \Yii::$app->set('session', 'yii\web\Session');
        \Yii::$container->setSingleton('products', '\common\components\ProductsContainer');
        \Yii::$app->settings->set('currency', DEFAULT_CURRENCY);

        \common\helpers\Inventory::updateNameByUprid([], $languages_id);

        $this->parseFilterOptions([]);
        $data = $this->build();
        $head = $this->getTable($this->showcolumns);

        $dir = self::filesRoot();

        //$writer = new \backend\models\EP\Formatter\CSV('write', ['save_file' => true], $dir. date('Ymd_His') . '.csv');
        $filename = $dir . 'stocktaking_report_' . strftime('%Y%b%d_%H%M');
        $writer = \common\helpers\Export::getWriter($filename, false);

        $a = [];
        foreach($head as $m){
          $a[] = $m['title'];
        }
        $writer->addRow($a);

        foreach($data['responseList'] as $row){
            $newArray = [];
            foreach ($row as $k => $v) {
                $vv = trim(strip_tags($v));
                $vv = str_replace(['&nbsp;&raquo;&nbsp;', '&nbsp;', ], [' / ', '', ], $vv);
                if ($k>1) {
                    //type cast
                    if (preg_match('/^[0-9\-]+$/', $vv)) {
                        $vv = intval($vv);
                    } elseif (preg_match('/^[0-9\.\-]+$/', $vv)) {
                        $vv = floatval($vv);
                    }

                }
                $newArray[] = $vv;
            }
            $writer->addRow($newArray);
        }
        $writer->close();
        exit();
    }

        /**
     *
     * @param string $type default 'auto_export/fedex_orders'
     * @return string
     */
    public static function filesRoot($type = '')
    {
        if (empty($type)) {
            $type = 'auto_export/stocktaking_report';
        }
        $globalRoot = Yii::getAlias('@ep_files/');
        $filesRoot = $globalRoot . $type . '/';
        if ( !is_dir($filesRoot) ) {
            try{
                \yii\helpers\FileHelper::createDirectory($filesRoot, 0777, true);
            }catch(\Exception $ex){

            }
        }else{
            @chmod($filesRoot, 0777);
        }

        return $filesRoot;
    }

    private function parseFilterOptions($get) {
        $this->search = $get['search']['value'] ?? "";
        $filters = [];
        if (!empty($get['filter'])) {
            //list
            parse_str($get['filter'], $filters);
        } else {
            //index
            $filters = $get;
        }

        if (!empty($filters)) {
            foreach (['showcolumns', 'groupby', 'platforms', ] as $k) {
                if (isset($filters[$k]) && !is_array($filters[$k])) {
                    unset($filters[$k]);
                }
            }
            foreach([
              'selected_categories' => 'categories',
              'selected_platforms' => 'platforms',
              'selected_status' => 'status',
              'selected_brand' => 'brand',
              'selected_supplier' => 'supplier',
              'groupby' => 'groupby',
            ] as $k => $v) {
              if (!empty($v) && is_scalar($v) && is_scalar($k) && isset($filters[$v])) {
                $this->$k = $filters[$v] ?? [];
                if (is_array($this->$k) && !empty($this->$k)) {
                    $this->$k = array_map('intval', $this->$k);
                }
              }
            }
            //$this->selected_status = $filters['status'] ?? 0;
            $this->showcolumns = $filters['showcolumns']?? ['sp', 'sp_', 'pp', 'pp_'];//array_keys($this->getTable(['_1', '_2', 'sp', 'sp_', 'pp', 'pp_']));
            $this->groupby = $filters['groupby']?? [];
            if (in_array('cat', $this->groupby) && !in_array('cat', $this->showcolumns)) {
                $this->showcolumns[] = 'cat';
            }
        }
    }
}
