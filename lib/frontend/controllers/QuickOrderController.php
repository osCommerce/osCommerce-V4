<?php

namespace frontend\controllers;

use Yii;
use frontend\design\ListingSql;
use frontend\design\SplitPageResults;
use frontend\design\Info;
use frontend\design\boxes\FiltersAll;
use yii\helpers\Url;

/**
 * Site controller
 */
class QuickOrderController extends Sceleton {

    const MAX_COUNT = 100;

    private function getColumns() {
      $columns = [
            ['field' => 'incart', 'sortField' => '', 'sortOrder' => 8, 'class' => 'al_incart', 'title' => TEXT_IN_YOUR_CART],
            ['field' => 'products_model', 'sortField' => 'products_model', 'sortOrder' => 0, 'class' => 'al_model', 'title' => TEXT_MODEL],
            ['field' => 'products_name', 'sortField' => 'products_name', 'sortOrder' => 1, 'class' => 'al_pr_name', 'title' => TEXT_NAME_PERSONAL],
            ['field' => 'categories_name', 'sortField' => '', 'sortOrder' => 3, 'class' => 'al_category', 'title' => TEXT_MAIN_CATEGORY],
            ['field' => 'products_price', 'sortField' => 'products_price', 'sortOrder' => 5, 'class' => 'al_price', 'title' => TEXT_PRICE],
            ['field' => 'stock', 'sortField' => '', 'sortOrder' => 6, 'class' => 'al_stock', 'title' => TEXT_STOCK],
            ['field' => 'qty_box', 'sortField' => '', 'sortOrder' => 7, 'class' => 'al_qty', 'title' => TEXT_QTY]
          ];

      if (true) {
        $columns[] =  ['field' => 'attributes', 'sortField' => '', 'sortOrder' => 2, 'class' => 'al_price', 'title' => TEXT_ATTRIBUTES];
      }

      if (true) {
        $columns[] =  ['field' => 'sub_category', 'sortField' => '', 'sortOrder' => 4, 'class' => 'al_category', 'title' => TEXT_SUB_CATEGORY];
      }
        
      usort($columns, function($a, $b) {$ret = 0; if(isset($a['sortOrder']) && isset($b['sortOrder'])) $ret = $a['sortOrder']>$b['sortOrder']; return $ret; } );
      return $columns;
    }

    public function actionIndex() {
        global $navigation,  $cart;
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');

        if (Yii::$app->request->isPost) {
            return $this->redirect(Yii::$app->urlManager->createUrl(['quick-order/index']));
        }

        if (Yii::$app->user->isGuest) {
            if (is_object($navigation) && method_exists($navigation, 'set_snapshot')){
                $navigation->set_snapshot();
            }
            tep_redirect(tep_href_link('account/login', '', 'SSL'));
        }

        if (!\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_is_reseller')) {
            tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

        $columns = $this->getColumns();
        $dtColumns = [];
        foreach ($columns as $column) {
          $dtColumns[] = '{ "data": "'. $column['field']. '", "orderable": '. ((int)!empty($column['sortField'])) . ', "class": "'. $column['class']. '" }';
        }

        $currencies = \Yii::$container->get('currencies');
        return $this->render('index.tpl', [
            'url' => Url::to('quick-order/list'),
            'columns' => $columns,
            'dtColumns' => implode(',', $dtColumns),
            'total' => $currencies->format( $cart->show_total() ),
        ]);
    }

    public function actionList() {
        global $cart;

        //$customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        //$languages_id = \Yii::$app->settings->get('languages_id');
        $currencies = \Yii::$container->get('currencies');
        $currency = \Yii::$app->settings->get('currency');

        $this->layout = false;

        //not used $settings = [];
        $order = Yii::$app->request->post('order', 1);
        $search = Yii::$app->request->post('search');
        $draw = Yii::$app->request->post('draw');
        $start = Yii::$app->request->post('start', 0);
        
        $formFilter = Yii::$app->request->post('filter');
        $output = [];
        parse_str($formFilter, $output);
        /*
        if ($order) {
            if ($order[0]['dir'] == 'desc') {
                $dir = 'desc';
            } else {
                $dir = 'asc';
            }

            switch ($order[0]['column']) {
                case 0:  // model
                    $order_by = " order by products_model " . $dir;
                    break;
                default:
                case 1:  // name
                    $order_by = " order by products_name " . $dir;
                    break;
                case 2:  // attribute
                    $order_by = " order by i.products_name " . $dir;
                    break;
                case 3:  // top category
                    $order_by = " order by top_categories_name " . $dir;
                    break;
                case 4:  // sub category
                    $order_by = " order by cd.categories_name " . $dir;
                    break;
                case 5:  // price
                    $order_by = " order by products_price " . $dir . ", ip.inventory_full_price " . $dir . ", ip.inventory_group_price " . $dir;
                    break;
            }
        }

        if (tep_not_null($search['value'])) {
            parse_str($search['value'], $_GET);
        }

        global $current_category_id;
        $current_category_id = (int)$_GET['category_id'];

        $start = Yii::$app->request->post('start', 0);
        $length = Yii::$app->request->post('length', 1);
        if (!$length)
            $length = 1;

        $_GET['page'] = ($start / $length) + 1;

        $listing_sql_array = \frontend\design\ListingSql::get_listing_sql_array();
        $filters_sql_array = \frontend\design\ListingSql::get_filters_sql_array();
        
        $restrictions = '';
        if (\common\helpers\Acl::checkExtensionAllowed('UserGroupsRestrictions', 'allowed')) {
            $restrictions = "left join groups_products i2g on i.products_id = i2g.products_id and i2g.groups_id = '" . (int) $customer_groups_id . "' ";
        }
        
        $listing_sql = "select ifnull(i.products_id, p.products_id) as products_id, if(i.products_model is null or i.products_model='', p.products_model, i.products_model) as products_model, if(pp.products_group_price is null or pp.products_group_price = -2, p.products_price, pp.products_group_price) as products_price, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, i.products_name as inventory_name, p.products_tax_class_id, c.categories_id, cd.categories_name, ifnull(cd1.categories_name, cd.categories_name) as top_categories_name, p.products_date_available, p.stock_indication_id, p.is_virtual from " .
                $listing_sql_array['from'] . " " .
                TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . 
                TABLE_CATEGORIES . " c " .
                "left join " . TABLE_CATEGORIES . " c1 on c.parent_id = c1.categories_id " .
                "left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on c1.categories_id = cd1.categories_id and cd1.language_id = '" . (int) $languages_id . "', " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_PRODUCTS . " p " .
                //"left join " . TABLE_SETS_PRODUCTS . " sp on sp.sets_id = p.products_id " .
                "left join " . TABLE_INVENTORY . " i on p.products_id = i.prid " .
                $restrictions .
                "left join " . TABLE_INVENTORY_PRICES . " ip on i.products_id = ip.products_id and ip.groups_id = '" . (int) $customer_groups_id . "' ".
                "left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id = p.products_id and pd.language_id = '" . (int) $languages_id . "' and pd.platform_id = '" . intval(\common\classes\platform::defaultId()) . "' and pd.department_id=0 " .
                "left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int) $languages_id . "' and pd1.platform_id = '" . (int) Yii::$app->get('platform')->config()->getPlatformToDescription() . "' and pd1.department_id=0 " .
                "left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id " . $listing_sql_array['left_join'] . " " . $filters_sql_array['left_join'] .
                " where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.categories_status = '1' and c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "' and ifnull(i.non_existent, 0) != 1 " . $listing_sql_array['where'] . " " .
                $filters_sql_array['where'] . 
                //$filter . 
                " group by products_id " . 
                $order_by;
        
     
        $num = tep_db_num_rows(tep_db_query($listing_sql));

         */
        $length = Yii::$app->request->post('length', 1);
        if (!$length) {
            $length = 1;
        }

        $_GET['page'] = ($start / $length) + 1;

        $columns = $this->getColumns();
        $orderBy = [];

        if ($order) {
            if ($order[0]['dir'] == 'desc') {
                $dir = SORT_DESC;
            } else {
                $dir = SORT_ASC;
            }

            if (!empty($columns[$order[0]['column']]['sortField'])) {
              $orderBy = [$columns[$order[0]['column']]['sortField'] => $dir];
            }
            
        }
        
        $get = \Yii::$app->request->get();
        if (tep_not_null($search['value'])) {
            parse_str($search['value'], $get);
        }


        if (!empty($get['category_id']) && empty($get['cat'])) {
          $get['cat'] = $get['category_id'];
        }
        $q = new \common\components\ProductsQuery([
          'get' => $get,
          'withInventory' => true,
          'orderBy' => $orderBy
        ]);
        $num = $cnt = $q->getCount();
        \Yii::$app->set('productsFilterQuery', $q);
/* 2do replace with listing template
        $params = array(
          'listing_split' => SplitPageResults::make($q->buildQuery()->getQuery(), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$search_results),'*', 'page', $cnt)->withSeoRelLink(),
          'this_filename' => FILENAME_ALL_PRODUCTS
          /*,
          'sorting_options' => $sorting,
          'sorting_id' => Info::sortingId(),* /
        );*/
        $settings['listing_type'] = 'quick-order';
        $productsContainer = \Yii::$container->get('products');
        $settings[0] = [
          'show_price' => 0,
          'show_attributes' => 0,
          'show_stock' => 0,
          //'show_image' => 0,
          'show_categories' => 0,
          ];
//echo "#### <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($q->buildQuery()->getQuery()->createCommand()->rawSql, 1) ."</PRE>"; die;

        $listing_split = SplitPageResults::make($q->buildQuery()->getQuery(), (isset($_SESSION['max_items'])?$_SESSION['max_items']:$length),'*', 'page', $cnt)->withSeoRelLink();

        Info::getListProductsDetails($listing_split->sql_query->column(), $settings);
        $products = $productsContainer->getAllProducts($settings['listing_type']);
//echo "#### <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($listing_split->sql_query->column(), 1) ."</PRE>"; die;

        /*$listing_split = new SplitPageResults($listing_sql, $length);
        $products_query = tep_db_query($listing_split->sql_query);
        $list = [];

        $products = Info::getProducts($products_query);
         */
        
        $selectedFields = \yii\helpers\ArrayHelper::getColumn($columns, 'field');

        foreach ($products as $products_arr){

            if (isset($output['qty'][$products_arr['products_id']])) {
                $qty = (int)$output['qty'][$products_arr['products_id']];
            } else {
                $qty = '0';
            }


            if (\common\helpers\Acl::checkExtensionAllowed('OrderQuantityStep', 'allowed')){
                $qtyStep = \common\extensions\OrderQuantityStep\OrderQuantityStep::setLimit($products_arr['order_quantity_data']);
            } else {
                $qtyStep = 'data-step="1"';
            }

            if (
                $products_arr['stock_indicator']['quantity_max'] &&
                $products_arr['stock_indicator']['quantity_max'] > 0 &&
                !$products_arr['stock_indicator']['allow_out_of_stock_checkout']
            ) {
                $qtyMax = 'data-max="' . $products_arr['stock_indicator']['quantity_max'] . '"';
            } else {
                $qtyMax = '';
            }

            $qty_box = '';
            if ($products_arr['stock_indicator']['flags']['add_to_cart']) {
                if ($products_arr['pack_unit'] > 0 || $products_arr['packaging'] > 0) {
                    $quantity_max = $products_arr['stock_indicator']['quantity_max'];
                    $products_arr['order_quantity_data']['order_quantity_minimal'] = 0;
                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                        $pack_units_data = $ext::quantityBoxFrontend([], ['products_id' => $products_arr['products_id']]);
                    }
                    $qtyTitle = UNIT_QTY . ':';
                    $qty_box = <<<EOD
    <div class="qty-input">
        <div class="qty_t">{$qtyTitle}</div>
        <div class="input">
            <span class="price_1"><span class="priceIn">{$pack_units_data['single_price']['unit']}</span></span>
            <input type="text" data-id="{$products_arr['products_id']}" value="0" class="qty-inp check-spec-max" data-type="unit" {$qtyMax} data-min="0" {$qtyStep}>
        </div>
    </div>
EOD;
                    if ($products_arr['pack_unit'] > 0) {
                        $qtyMax = 'data-max="' . floor($quantity_max / $products_arr['pack_unit']) . '"';
                        $qtyTitle = PACK_QTY . ': <span>(' . $products_arr['pack_unit'] . ' items)</span>';
                        $qty_box .= <<<EOD
    <div class="qty-input">
        <div class="qty_t">{$qtyTitle}</div>
        <div class="input inps">
            <span class="price_1"><span class="priceIn">{$pack_units_data['single_price']['pack']}</span></span>
            <input type="text" data-id="{$products_arr['products_id']}" value="0" class="qty-inp check-spec-max" data-type="pack_unit" $qtyMax data-min="0">
        </div>
    </div>
EOD;
                    }
                    if ($products_arr['packaging'] > 0) {
                        $qtyPackaging = $products_arr['packaging'] * $products_arr['pack_unit'];
                        $qtyMax = 'data-max="' . floor($quantity_max / $qtyPackaging) . '"';
                        $qtyTitle = CARTON_QTY . ': <span>(' . $qtyPackaging . ' items)</span>';
                        $qty_box .= <<<EOD
    <div class="qty-input">
        <div class="qty_t">{$qtyTitle}</div>
        <div class="input inps">
            <span class="price_1"><span class="priceIn">{$pack_units_data['single_price']['package']}</span></span>
            <input type="text" data-id="{$products_arr['products_id']}" value="0" class="qty-inp" data-type="packaging" $qtyMax data-min="0">
        </div>
    </div>
EOD;
                    }
                } else{
                    $qty_box = '<input type="text" data-id="' . $products_arr['products_id'] . '" value="'.$qty.'" class="qty-inp" data-max="' . $qtyMax . '" data-min="0" ' . $qtyStep . '>';
                }
            }


            $stock = '<div class="stock">
      <span class="' . $products_arr['stock_indicator']['text_stock_code'] . '"><span class="' . $products_arr['stock_indicator']['stock_code'] . '-icon">&nbsp;</span>' . $products_arr['stock_indicator']['stock_indicator_text'] . '</span>
    </div>';


            $priceInstance = \common\models\Product\Price::getInstance(\common\helpers\Inventory::normalize_id($products_arr['products_id']));
            $products_price = $priceInstance->getInventoryPrice(['qty' => 1]);
            $special_price = $priceInstance->getInventorySpecialPrice(['qty' => 1]);
            if ($special_price !== false) {
                $products_price = $special_price;
            }

            $tmp = [
                'products_model' => $products_arr['products_model'],
                'products_name' => '<a href="' . Yii::$app->urlManager->createUrl(['catalog/product', 'products_id' => $products_arr['products_id']]) . '">' . $products_arr['products_name'] . '</a>',
                'attributes' => str_replace($products_arr['products_name'], '', $products_arr['inventory_name']),
                'categories_name' => $products_arr['top_categories_name'],
                'sub_category' => $products_arr['categories_name'],
                'products_price' => $currencies->display_price($products_price, \common\helpers\Tax::get_tax_rate($products_arr['products_tax_class_id'])),
                'stock' => $stock,
                'qty_box' => $qty_box,
                'incart' => (is_object($cart) && ($qty = $cart->get_quantity($products_arr['products_id'])) > 0 ? $qty : '&nbsp;'),
            ];
            if (strpos($products_arr['products_id'], '{') !== false) {
                if ($mInventory = \common\helpers\Inventory::getRecord($products_arr['products_id'])) {
                    $tmp['products_model'] = $mInventory->products_model;
                    $tmp['attributes'] = str_replace($products_arr['products_name'], '', $mInventory->products_name);
                }
            }
            foreach (array_keys($tmp) as $k) {
              if (!in_array($k, $selectedFields)) {
                unset($tmp[$k]);
              }
            }
            $list[] = $tmp;
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $num,
            'recordsFiltered' => $num,
            'data' => $list,
            'enabled' => (int) tep_session_is_registered('customer_id'),
            'currency' => array(
                'left_symbol' => $currencies->currencies[$currency]['symbol_left'],
                'right_symbol' => $currencies->currencies[$currency]['symbol_right'],
            ),
            'page' => $_GET['page'],
            'get' => $_GET,
            //'listing' => $listing_sql,
            'filters' => FiltersAll::widget(),
        ];
        //echo json_encode($response);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $response;
    }
    
    public function actionRecalc() {
        $qtyArray = Yii::$app->request->get('qty', []);
        $totalArray = [];
        if (is_array($qtyArray)) {
            global $cart;
            $currencies = \Yii::$container->get('currencies');
            if( ! is_object( $cart ) || ! is_object( $currencies ) ) {
                    return '';
            }
            $totalArray['current'] = $cart->show_total();
            $cartNew = new \common\classes\shopping_cart();
            foreach ($qtyArray as $uprid => $qty) {
                $attrib = array();
                $ar = preg_split('/[\{\}]/', $uprid);
                for ($i = 1; $i < sizeof($ar); $i = $i + 2) {
                    if (isset($ar[$i + 1])) {
                        $attrib[$ar[$i]] = $ar[$i + 1];
                    }
                }
                if (is_array($qty)) {
                    $packQty = [
                        'unit' => (int)$qty[0],
                        'pack_unit' => (int)$qty[1],
                        'packaging' => (int)$qty[2],
                    ];
                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                        $packQty['qty'] = $ext::recalcQauntity(\common\helpers\Inventory::get_prid($uprid), $packQty);
                    }
                    $cartNew->add_cart(\common\helpers\Inventory::get_prid($uprid), $packQty, $attrib, false);
                } elseif ($qty > 0) {
                    $cartNew->add_cart(\common\helpers\Inventory::get_prid($uprid), $cartNew->get_quantity($uprid) + $qty, $attrib, false);
                }
            }
            $totalArray['selected'] = $cartNew->show_total();
            $totalArray['total'] = $totalArray['selected'] + $totalArray['current'];
            foreach ($totalArray as $key => $value) {
                $totalArray[$key] = $currencies->format($value);
            }
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $totalArray;
    }

}
