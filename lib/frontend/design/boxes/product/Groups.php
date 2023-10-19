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

namespace frontend\design\boxes\product;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use frontend\design\IncludeTpl;

class Groups extends Widget {

    public $file;
    public $params;
    public $settings;

    public function init() {
        parent::init();
    }

    public static function prepareParams($products_id, $groupCountLimit=1, $hideNotAvailable=false, $limit_products=[], $hideSingleValueProperty=true)
    {
        $languages_id = \Yii::$app->settings->get('languages_id');
        $currencies = \Yii::$container->get('currencies');
        $iconsTypes = 'Thumbnail';
        $iconsSize = \common\classes\Images::getImageTypes($iconsTypes);
        $result_def = [];

        $group_id = \common\models\Products::findOne($products_id)->products_groups_id ?? null;
        if ($group_id <= 0) {
            return $result_def;
        }
        $params_def = [
            'page' => FILENAME_ALL_PRODUCTS,
            'active' => true,
            'currentPlatform' => true,
            'currentCategory' => false,
            'currentCustomerGroup' => false,
            'groupProductGroups' => false, // works only if params in buildQuery
            'outOfStock' => true,
            'orderBy' => ['fake' => false],
            'customAndWhere' => ['p.products_groups_id' => $group_id]
        ];

        $q = (new \common\components\ProductsQuery())->buildQuery($params_def)->getQuery();
        $products_count = $q->count();
        if ($products_count <= $groupCountLimit) {
            return $result_def;
        }

        $products_array = array();
        $properties_array = array();
        $products_all = $q->addSelect('p.stock_indication_id')  // extra fields
                ->addSelect('p.products_price, products_tax_class_id')
                ->addFrontendDescription()
                ->orderBy('p.products_groups_sort')
                ->asArray()
                ->all();
        foreach($products_all as $products) {
            $products_array[$products['products_id']] = array(
                'id' => $products['products_id'],
                'name' => $products['products_name'],
                'selected' => ($products['products_id'] == (int)$products_id),
                'lazy' => new GroupArrayLazyItem( array(
                    'id' => $products['products_id'],
                    '.products_price' => $products['products_price'],
                    '.products_tax_class_id' => $products['products_tax_class_id'],
                    '.stock_indication_id' => $products['stock_indication_id'],
                    ) ),
            );
        }
        unset($products_all);

        $pids = array_keys($products_array);
        if ( !is_array($limit_products) ) $limit_products = [];
        if ( count($limit_products)>0 ) {
            $pids = array_intersect($pids, $limit_products);
        }

        // optionally using temp table for product's ids instead of IN operator
        $pids_table = count($pids) > 1000;
        if ($pids_table) {
            \Yii::$app->db->createCommand('DROP TABLE IF EXISTS pids')->execute();
            \Yii::$app->db->createCommand('CREATE TEMPORARY TABLE pids (products_id INT NOT NULL PRIMARY KEY) ENGINE=Memory')->execute();
            \Yii::$app->db->createCommand('INSERT INTO pids (products_id) VALUES ('. implode('),(', $pids).')')->execute();
        }

        $properties_array = \common\models\Properties::find()->alias('pr');
        if ($pids_table) {
            $properties_array->innerJoinWith(['properties2Products pr2p'], false)
                    ->innerJoin('pids', 'pids.products_id = pr2p.products_id');
        } else {
            $properties_array->innerJoinWith(['properties2Products pr2p' =>
                function ($query) use ($pids) {
                   return $query->andOnCondition(['pr2p.products_id' => $pids]);
                }
            ], false);
        }
        $properties_array = $properties_array
                ->joinWith('frontendname prd', false)
                ->select('pr.properties_id, pr.properties_type, prd.properties_name, pr.parent_id, prd.properties_color, prd.properties_image, pr.display_as_image, pr.sort_order')
                ->addSelect(['products_count' => new \yii\db\Expression("count(distinct pr2p.products_id)")])
                ->addSelect(['values_id' => new \yii\db\Expression("group_concat(distinct pr2p.values_id separator ',')")])
                ->where('pr.products_groups = 1')
                ->groupBy('pr.properties_id')
                //->indexBy('properties_id') // index by PHP later (multisort - breaks index keys)
                // order was removed to reduce mysql tmp_files. it should not be more that ~20 records, so php sort must be quick
                //->orderBy('pr.parent_id, pr.sort_order, prd.properties_name')
                ->asArray()
                ->all();
        $props_all = $properties_array;
        ArrayHelper::multisort($props_all, ['parent_id', 'sort_order', 'properties_name']);
        unset($properties_array);
        foreach ($props_all as $property) {
            $properties_array[$property['properties_id']] = $property;
        }

        if (isset($properties_array) && is_array($properties_array) && count($properties_array) > 0) {
            $prop_ids = array_keys($properties_array);
            $pruid_mask = [];
            $properties_names = [];
            $values_for_selected_product = \common\models\Properties2Propducts::find()
//                    ->select('properties_id, values_id, values_id as values_array')
                    ->select('properties_id, MIN(values_id) as values_id, GROUP_CONCAT(values_id) as values_array') // to handle multivalue for same property
                    ->where(['products_id' => (int)$products_id])
                    ->groupBy('properties_id')
                    ->indexBy('properties_id')
                    ->asArray()
                    ->all();
            foreach ($properties_array as $id=>$property) {
                if ( $hideSingleValueProperty && strpos($property['values_id'],',')===false ) {
                    unset($properties_array[$id]);
                    continue;
                }
                // fix for product with multiple values for same property
                // all values will be selected at the page to let user know that the product has been configurated inaccurately
                $property['current_value_array'] = empty($values_for_selected_product[$id]['values_array']) ? array(): explode(',', $values_for_selected_product[$id]['values_array']) ;
                if (count($property['current_value_array']) > 1) {
                    \Yii::warning("The product (id=$products_id) has multiple values (ids=" . $values_for_selected_product[$id]['values_array'] . ") for property (id=$id)");
                }

                $property['current_value'] = $values_for_selected_product[$id]['values_id'] ?? null;
                $properties_array[$id] = $property;
                $pruid_mask[$property['properties_id']] = '{'.$property['properties_id'].'}'.$property['current_value'].'|';
                $properties_names[$property['properties_id']] = $property['properties_name'];
            }

            $products_with_sorted_props = \common\models\Properties2Propducts::find()->alias('p2p')
                    ->select('p2p.products_id, p2p.properties_id, p2p.values_id')
                    ->joinWith('properties pr', false)
                    ->withPropertiesDescription()
                    ->addSelect('pr.parent_id, prd.properties_name, pr.sort_order')
                    ->withPropertiesValue()
                    ->addSelect('pv.values_text')
                    ->where(['p2p.properties_id' => $prop_ids]);
                    // order was removed to reduce mysql tmp_files. it should not be more that ~20 records, so php sort must be quick
                    //->orderBy('products_id, parent_id, sort_order, properties_name') // must be the same as for @property_array
            if ($pids_table) {
                $products_with_sorted_props->innerJoin('pids', 'pids.products_id = p2p.products_id');
            } else {
                $products_with_sorted_props->andWhere(['p2p.products_id' => $pids]);
            }
            $products_with_sorted_props = $products_with_sorted_props->asArray()->all();
            ArrayHelper::multisort($products_with_sorted_props, ['products_id', 'parent_id', 'sort_order', 'properties_name', 'values_id']);

            foreach ($products_with_sorted_props as $_product_property){
                if ( empty($products_array[$_product_property['products_id']]['pruid']) ) {
                    $products_array[$_product_property['products_id']]['pruid'] = $_product_property['products_id'];
                    $products_array[$_product_property['products_id']]['property_ids'] = [];
                }
                $products_array[$_product_property['products_id']]['pruid'] .= '{'.$_product_property['properties_id'].'}'.$_product_property['values_id'].'|';
                $products_array[$_product_property['products_id']]['property_ids'][$_product_property['properties_id']] = $_product_property['values_id'];
                $value_names[$_product_property['values_id']] = $_product_property['values_text'];
            }
            $all_pruids = ArrayHelper::map($products_array, 'id', 'pruid');

            foreach ($properties_array as $prop_id1 => $prop1) {
                $values_all = \common\models\Properties2Propducts::find()->alias('pr2p')
                        ->withPropertiesValue()
                        ->select('pv.values_id, pv.values_text, pv.values_color as color, values_alt, pv.values_number, pv.values_number_upto, pr2p.values_flag, pv.values_color, pv.values_image, pv.sort_order')
                        ->addSelect(['products_count' => new \yii\db\Expression('count(distinct pr2p.products_id)')])
                        ->addSelect(['products_ids' => new \yii\db\Expression('group_concat(distinct pr2p.products_id)')])
                        ->where(['pr2p.properties_id' => $prop_id1])
                        ->groupBy('values_id')
                        // order was removed to reduce mysql tmp_files. it should not be more that ~20 records, so php sort must be quick
                        //->orderBy('pv.sort_order, pv.values_text')
                        ;
                if ($pids_table) {
                    $values_all->innerJoin('pids', 'pids.products_id = pr2p.products_id');
                } else {
                    $values_all->andWhere(['pr2p.products_id' => $pids]);
                }
                $values_all = $values_all->asArray()->all();
                ArrayHelper::multisort($values_all, ['sort_order', 'values_text']);

                foreach ($values_all as $values) {
                    if ($prop1['properties_type'] == 'file') {
                        $values['image'] = $values['values_text'];
                        $values['text'] = $values['values_alt'];
                    } else {
                        $values['text'] = $values['values_text'];
                    }

                    // find the nearest product for "Awailable with"
                    if ($pids_table) {
                        $nearest_products = (new \yii\db\Query())->from('pids p');
                    } else {
                        $nearest_products = \common\models\Products::find()->alias('p')
                            ->select('p.products_id')
                            ->where(['p.products_id' => $pids]);
                    }
                    foreach ($properties_array as $id2 => $prop2) {
                        $pr2p = "pr2p$id2";
                        $val = (int)($prop1['properties_id'] == $prop2['properties_id'] ? $values['values_id'] : $prop2['current_value']);
                        $nearest_products = $nearest_products->innerJoin(\common\models\Properties2Propducts::tablename() . " $pr2p",
                                "$pr2p.products_id = p.products_id and $pr2p.properties_id =" . (int) $prop2['properties_id'] . " and $pr2p.values_id = $val");
                    }
                    $nearest_products_pids = $nearest_products->limit(1)->one();

                    if (!empty($nearest_products_pids)) {
                        $pid1 = $nearest_products_pids['products_id'];
                        $values['product'] = $products_array[$pid1];
                        $values['product']['selected'] = in_array($values['values_id'], $prop1['current_value_array']);
                        $properties_array[$prop_id1]['values'][$values['values_id']] = array_merge($values,
                            array(
                                'icon_w' => (!empty($iconsSize['image_types_x']) ? $iconsSize['image_types_x'] : 50),
                                'icon_h' => (!empty($iconsSize['image_types_y']) ? $iconsSize['image_types_y'] : 50),
                            )
                        );
                    } elseif (!empty($values['products_ids']) /*&& empty($limit_products_sql)*/) {
                        $pruid_with_option = preg_grep('#\{'.$prop_id1.'\}'.$values['values_id'].'\|#',$all_pruids);

                        // Find a product with the closest property values
                        $closest_diff = 999999; $closest_pid = 0;
                        foreach (array_keys($pruid_with_option) as $pid) {
                            if (is_array($products_array[$pid]['property_ids']) && is_array($products_array[$products_id]['property_ids'])) {
                                if (($current_diff = count(array_diff_assoc($products_array[$pid]['property_ids'], $products_array[$products_id]['property_ids']))) < $closest_diff) {
                                    $closest_diff = $current_diff;
                                    $closest_pid = $pid;
                                }
                            }
                        }

                        $availableWith = '';
                        if ($closest_pid > 0) {
                            foreach ($products_array[$closest_pid]['property_ids'] as $_pId => $_vId) {
                                if ($_pId == $prop_id1) continue;
                                if ($properties_array[$_pId]['current_value'] != $_vId) {
                                    $availableWith .= ' <span>' . $properties_names[$_pId] . ': <b>' . $value_names[$_vId] . '</b></span>' . "\n";
                                } else {
                                    //$availableWith .= ' <span>' . $properties_names[$_pId] . ': ' . $value_names[$_vId] . '</span>' . "\n";
                                }
                            }
                            $pid1 = $closest_pid;
                        } else {
                            foreach (array_keys($pruid_with_option) as $pid) {
                                foreach ($products_array[$pid]['property_ids'] as $_pId=>$_vId){
                                    if ( $_pId==$prop_id1 ) continue;
                                    $availableWith .= ' <span>'.$value_names[$_vId]."</span>";
                                }
                            }
                            $pid1 = (int)$values['products_ids'];
                        }

                        if ((int)$pid1 > 0 && (count($limit_products)==0 || count($limit_products)>0 && in_array((int)$pid1, $limit_products) )) {
                            $values['product'] = $products_array[$pid1];
                            $values['product']['id'] = $pid1;
                            $values['product']['selected'] = ($values['values_id'] == $prop1['current_value']);
                            $values['product']['availableWith'] = $availableWith;
                            $values['product']['other_options'] = 1;
                            $values['product']['link'] = $products_array[$pid1]['link'];
                            $properties_array[$prop_id1]['values'][$values['values_id']] = array_merge($values,
                                array(
                                    'icon_w' => (!empty($iconsSize['image_types_x']) ? $iconsSize['image_types_x'] : 50),
                                    'icon_h' => (!empty($iconsSize['image_types_y']) ? $iconsSize['image_types_y'] : 50),
                                )
                            );
                        }
                    }
                }
            }
        }
        if ($hideNotAvailable){
            foreach ($properties_array as $propId=>$propData){
                $values = $propData['values'];
                foreach($values as $valId=>$valData){
                    if (!$valData['product']['stock_indicator']['flags']['add_to_cart']){
                        unset($values[$valId]);
                    }
                }
                if ( count($values)>0 ){
                    $properties_array[$propId]['values'] = $values;
                }
            }
        }
        $changed_properties_text = '';
        $products_groups_prev_pid = \Yii::$app->session->get('products_groups_prev_pid');
        if ($products_groups_prev_pid > 0 && is_array($products_array[$products_groups_prev_pid]??null)) {
            if (is_array($products_array[$products_groups_prev_pid]['property_ids']??null) && is_array($products_array[$products_id]['property_ids']??null)) {
                $changed_properties_ids = array_diff_assoc($products_array[$products_id]['property_ids'], $products_array[$products_groups_prev_pid]['property_ids']);
                if (is_array($changed_properties_ids) && count($changed_properties_ids) > 0) {
                    foreach ($changed_properties_ids as $_pId => $_vId) {
                        $changed_properties_text .= ' <span>' . $properties_names[$_pId] . ': <b>' . $value_names[$_vId] . '</b></span>' . "\n";
                    }
                }
            }
        }

        return ['products' => $products_array, 'properties' => $properties_array??null, 'changed_properties' => $changed_properties_text];
    }

    
    public function run() {
        $params = Yii::$app->request->get();
        if ( is_array($this->params) && !empty($this->params['products_id']) ){
            $params = $this->params;
        }
        if ($propData = static::prepareParams($params['products_id'], 0)) {
            $_SESSION['products_groups_prev_pid'] = $params['products_id'];

            $noPropertyIds = explode(',', $this->settings[0]['no_property_ids']);
            foreach ($noPropertyIds as $key => $id) {
                $noPropertyIds[$key] = trim($id);
            }
            $propertyCategoriesIds = [];
            foreach ($propData['properties'] as $_prop_id=>$_usedPropData){
                $propertyCategoriesIds[(int)$_usedPropData['parent_id']] = (int)$_usedPropData['parent_id'];
            }
            $propertyCategoriesIds = array_values($propertyCategoriesIds);

            $noPropertyIds = array_merge([0], $noPropertyIds);
            $propertyCategories = \common\models\PropertiesDescription::find()
                ->where(['in', 'properties_id', $propertyCategoriesIds])
                ->andWhere(['not in', 'properties_id', $noPropertyIds])
                ->andWhere(['language_id' => \Yii::$app->settings->get('languages_id') ])
                ->asArray()
                ->all();

            $propertyCategoriesById = [];
            foreach ($propertyCategories as $propertyCategory) {
                $propertyCategoriesById[$propertyCategory['properties_id']] = $propertyCategory;
            }
            $propData['propertyCategories'] = $propertyCategoriesById;

            $widgetParam = $propData;
            $widgetParam['id'] = $this->id;
            $widgetParam['params'] = $this->params;
            $widgetParam['settings'] = $this->settings;

            return IncludeTpl::widget(['file' => 'boxes/product/groups.tpl', 'params' => $widgetParam]);
        }
    }

    }

class GroupArrayLazyItem implements \ArrayAccess
{
    private $_data = array();

    static $lazy_calc = [
        'image' => 'getImage',
        'link' => 'getLink',
        'price' => 'getPrice',
        'stock_indicator' => 'getStockIndicator',
    ];
    public function __construct($data=null)
    {
        if ( is_array($data) ){
            $this->_data = $data;
        }
    }
    //'image' => \common\classes\Images::getImageUrl($products['products_id'], $iconsTypes),
    //'link' => tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products['products_id']),
    //'price' => $currencies->display_price($price, \common\helpers\Tax::get_tax_rate($products['products_tax_class_id']), 1, false),

    public function getImage()
    {
        $this->_data['image'] = \common\classes\Images::getImageUrl($this->_data['id'], 'Thumbnail');
    }
    public function getLink()
    {
        $this->_data['link'] =  tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $this->_data['id']);
    }
    public function getPrice()
    {
        /** @var \common\classes\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');
        if ($special_price = \common\helpers\Product::get_products_special_price($this->_data['id'], 1)) {
            $price = $special_price;
        } else {
            $price = \common\helpers\Product::get_products_price($this->_data['id'], 1, $this->_data['.products_price']);
        }
        $this->_data['price'] = $currencies->display_price($price, \common\helpers\Tax::get_tax_rate($this->_data['.products_tax_class_id']), 1, false);
    }

    public function getStockIndicator()
    {
        $products_quantity = \common\helpers\Product::get_products_stock($this->_data['id']);
        $stock_info = \common\classes\StockIndication::product_info(array(
            'products_id' => $this->_data['id'],
            'products_quantity' => $products_quantity,
            'stock_indication_id' => (isset($this->_data['.stock_indication_id']) ? $this->_data['.stock_indication_id'] : null),
        ));
        $this->_data['stock_indicator'] = $stock_info;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset) {
        return isset($this->_data[$offset]) || isset(static::$lazy_calc[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset) {
        if ( !isset($this->_data[$offset]) && isset(static::$lazy_calc[$offset]) ) {
            call_user_func([$this, static::$lazy_calc[$offset]]);
        }
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset) {
        unset($this->_data[$offset]);
    }

}

