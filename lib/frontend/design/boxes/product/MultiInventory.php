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
use common\helpers\Inventory as InventoryHelper;

class MultiInventory extends Widget {

    public $file;
    public $params;
    public $settings;
    
    private $products_id;

    public function init() {
        parent::init();
    }

    public function run() {
        $params = Yii::$app->request->get();

        $type = Yii::$app->request->get('type', 'product');
        $products_id = tep_db_prepare_input(Yii::$app->request->get('products_id'));
        $options_prefix = '';

        if ($type=='listing' || $type=='productListing') {
          $listid = tep_db_prepare_input(Yii::$app->request->get('listid', array()));
          if (!empty($listid[$products_id])) {
            $params['id'] = $listid[$products_id];
            $options_prefix = 'list';
          } elseif (!empty($listid)) {
            $params['id'] = $listid;
            $options_prefix = 'list';
          }
          if (!empty($params['listqty']) && is_array($params['listqty']) && count($params['listqty'])==1) {
            $params['qty'] = $params['listqty'][0];
            unset($params['listqty']);
          }
        }

        if ($params['products_id']) {
            $this->products_id = intval($params['products_id']);
            if (\common\helpers\Attributes::has_product_attributes($params['products_id'])) {
                $products = Yii::$container->get('products');
                $product = $products->getProduct($params['products_id']);
                if ($product && !ArrayHelper::getValue($product, 'settings.show_attributes_quantity') && Yii::$app->controller instanceof \frontend\controllers\CatalogController) {
                    $action = Yii::$app->controller->createAction('product-attributes');
                    return $action->runWithParams($params+['boxId' => $this->id]);
                }
                $attributes = $params['id'] ?? [];
                $details = \common\helpers\Attributes::getDetails($params['products_id'], $attributes, $params);
                if ($productDesigner = \common\helpers\Acl::checkExtensionAllowed('ProductDesigner', 'allowed')){
                    $productDesigner::productAttributes($details, $attributes, $params);
                }
                if ($details['attributes_array']) {
                    $lastOption = array_pop($details['attributes_array']);
                    $preselected = $this->_collectPreselectedAttribs($details['attributes_array'], $params);
                    array_push($details['attributes_array'], $this->_collectData($lastOption, $preselected));
                    
                    $this->updateStockIndicator($details);
                
                    if (\Yii::$app->request->isAjax || $type=='listing' || $type=='productListing') {
                        $details['image_widget'] = \frontend\design\boxes\product\Images::widget(['params'=>['uprid'=>$details['current_uprid']], 'settings' => \frontend\design\Info::widgetSettings('product\Images', false, 'product')]);
//                        $details['images'] = \frontend\design\Info::$jsGlobalData['products'][$details['products_id']]['images'] ?? null;
//                        $details['defaultImage'] = \frontend\design\Info::$jsGlobalData['products'][$details['products_id']]['defaultImage'];
                        $details['images'] = \frontend\design\Info::$jsGlobalData['products'][$params['products_id']]['images'] ?? null;
                        $details['defaultImage'] = \frontend\design\Info::$jsGlobalData['products'][$params['products_id']]['defaultImage'];
                        $details['productId'] = $params['products_id'];
                        if ($type=='productListing'){
                            $details['product_attributes'] = \frontend\design\IncludeTpl::widget([
                                'file' => 'boxes/listing-product/element/attributes.tpl',
                                'params' => [
                                    'product' => [
                                        'product_attributes_details' => $details,
                                        'product_has_attributes' => true,
                                        'products_id' => $params['products_id'],
                                        'show_attributes_quantity' => $product['settings']->show_attributes_quantity,
                                    ],
                                  'boxId' => $this->id,
                                ]
                            ]);
                        } elseif (Yii::$app->request->get('list_b2b') || $type=='listing'){
                            $details['product_attributes'] = IncludeTpl::widget(['file' => 'boxes/product/multi-attributes.tpl', 'params' => ['attributes' => $details['attributes_array'], 'isAjax' => false, 'options_prefix' => $options_prefix, 'settings' => $this->settings[0], 'boxId' => $this->id,]]);
                        } else {
                            $tmp = $details;
                            $lastRow = array_pop($tmp['attributes_array']);
                            $details['product_attributes'] = IncludeTpl::widget(['file' => 'boxes/product/attributes/mix.tpl', 'params' => ['item' => $lastRow, 'products_id' => $params['products_id'], 'isAjax' => true, 'settings' => ($this->settings[0]??null),'boxId' => $this->id,]]);
                        }
                        return json_encode($details);
                    } else {
                        return IncludeTpl::widget(['file' => 'boxes/product/multi-attributes.tpl', 'params' => ['attributes' => $details['attributes_array'], 'isAjax' => false, 'options_prefix' => $options_prefix, 'settings' => $this->settings[0],'boxId' => $this->id,]]);
                    }
                }
            }
        }
    }
    
    private function updateStockIndicator(&$details){
        $details['stock_indicator'] = [
            'add_to_cart' => false,
            'request_for_quote' => false,
            'ask_sample' => false,
            'notify_instock' => false,
            'quantity_max' => false
        ];
        $textPos = $textNeg = null;
        foreach($details['attributes_array'] as $attributes ){
            if (is_array($attributes['options'])){
                foreach($attributes['options'] as $option){
                    if (!empty($option['mix'])){
                        foreach ($details['stock_indicator'] as $key => &$flag){
                            $flag = $flag || ($option['mix']['flags'][$key] ?? false);
                            if ($key == 'add_to_cart'){
                                $tmp = [
                                    'stock_code' => $option['mix']['stock_code'],
                                    'stock_indicator_text' => $option['mix']['stock_indicator_text'],
                                    'stock_indicator_text_short' => $option['mix']['stock_indicator_text_short'],
                                    'text_stock_code' => $option['mix']['text_stock_code'],
                                ];
                                if ($option['mix']['flags'][$key]){
                                    $textPos = $tmp;
                                } else {
                                    $textNeg = $tmp;
                                }
                            }
                        }
                    }
                }
            }
        }
        //var_dump($textPos, $textNeg);
        $details['product_valid'] = $details['stock_indicator']['add_to_cart'] || $details['stock_indicator']['request_for_quote'];
        $tmp = $textPos ? $textPos: $textNeg;
        if (is_array($tmp)){
            $details['stock_indicator'] = array_replace($details['stock_indicator'], $tmp);
        }
    }

    private function _collectData(array $attribArray, array $preselected = []) {
        foreach ($attribArray['options'] as &$mixOPtion) {
            $params = $preselected;
            $params[$attribArray['id']] = $mixOPtion['id'];
            $products_id = InventoryHelper::normalizeInventoryId(InventoryHelper::get_uprid($this->products_id, $params));
            
            $check_inventory = \common\models\Inventory::find()
                    ->alias('i')
                    ->select(['inventory_id', 'products_name', 'products_quantity', 'stock_indication_id', 'stock_delivery_terms_id', 'stock_control'])
                    ->where(['products_id' => $products_id])
                    ->existent()->restriction()->asArray()
                    ->one();
            if ($check_inventory){
                $stock_indicator = \common\classes\StockIndication::product_info(array(
                            'products_id' => $products_id,
                            'products_quantity' => ($check_inventory['inventory_id'] ? $check_inventory['products_quantity'] : '0'),
                            'stock_indication_id' => (isset($check_inventory['stock_indication_id']) ? $check_inventory['stock_indication_id'] : null),
                            'stock_delivery_terms_id' => (isset($check_inventory['stock_delivery_terms_id']) ? $check_inventory['stock_delivery_terms_id'] : null),
                ));
                $mixOPtion['mix'] = $stock_indicator;
            } else {
                $mixOPtion['mix'] = false;
            }
        }
        return $attribArray;
    }

    private function _collectPreselectedAttribs(array $attribArray) {
        return \yii\helpers\ArrayHelper::map($attribArray, 'id', 'selected');
    }

}
