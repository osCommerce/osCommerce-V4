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

namespace frontend\design;

use Yii;
use yii\helpers\ArrayHelper;
use common\classes\shopping_cart;
use common\classes\Images;
use common\models\ProductsAttributes;

#[\AllowDynamicProperties]
class CartDecorator
{
    private $_cart;
    /** @prop $allow_checkout updated on getProducts only! */
    public $allow_checkout;
    public $oos_product_incart;
    public $bound_quantity_ordered;
    private $products = [];
    private $controllerDispatch;
    
    public function __construct(shopping_cart $cart){
        $this->_cart = $cart;
        $this->allow_checkout = true;
        $this->oos_product_incart = false;
        $this->bound_quantity_ordered = false;
        $this->controllerDispatch  = FILENAME_SHOPPING_CART;
        $this->removeAction = 'remove_product';
    }
    
    public function setContorllerDispatch($name){
        $this->controllerDispatch = $name;
    }
    
    public function getRemoveAction(){
        return $this->removeAction;
    }
    
    public function setRemoveAction($name){
        $this->removeAction = $name;
    }
    
    public function getContollerDispatch(){
        return $this->controllerDispatch;
    }

    private function decorateProducts(){
        $languages_id = \Yii::$app->settings->get('languages_id');
        /** @var \common\classes\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');
        $this->products = $this->_cart->get_products();
        foreach (\common\helpers\Hooks::getList('frontend/cart-decorator/decorate-products/products') as $filename) {
            include($filename);
        }
        $container = Yii::$container->get('products');
        for ($i=0, $n=sizeof($this->products); $i<$n; $i++) {
            $this->products[$i]['id_link'] = $this->products[$i]['id'];
            $this->products[$i]['id_platform'] = ArrayHelper::getValue($this->products, [$i,'platform_id']);
            if ( !empty($this->products[$i]['relation_type']) && $this->products[$i]['relation_type']=='linked' ) {
                if (!empty($this->products[$i]['parent'])) {
                    unset($this->products[$i]);
                    continue;
                }
                $this->products[$i]['id_link'] = intval($this->products[$i]['id_link']);
                $this->products[$i]['id_platform'] = intval($this->products[$i]['id_platform']);
            }
// {{ Products Bundle Sets
            if (!empty($this->products[$i]['parent']) && !\common\helpers\Product::check_product($this->products[$i]['id'],1,true)){
                unset($this->products[$i]);
                continue;
            }
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductBundles', 'allowed')) {
                list($bundles, $bundles_info) = $ext::inProducts($this->products[$i]);
                if (count($bundles) > 0) {
                    $this->products[$i]['bundles'] = $bundles;
                }
                if (count($bundles_info) > 0) {
                    $this->products[$i]['bundles_info'] = $bundles_info;
                }
            }
// }}

            $this->products[$i]['hidden_fields'] = '';
            $this->products[$i]['hidden_fields'] .= tep_draw_hidden_field('products_id[]', $this->products[$i]['id']);
            $this->products[$i]['hidden_fields'] .= tep_draw_hidden_field('ga[]', $this->products[$i]['ga']);

            if (is_array(ArrayHelper::getValue($this->products, [$i,'sub_products'])) && count(ArrayHelper::getValue($this->products, [$i,'sub_products'])) > 0) { // if has sub-products
              // show full configurator price
              //$wTax = Yii::$app->storage->has('taxable') ? (bool)Yii::$app->storage->get('taxable') : true;
              $wTax = \common\helpers\Tax::displayTaxable();
              $this->products[$i]['final_price_inc'] = $currencies->calculate_price($this->_cart->configurator_price($this->products[$i]['id'], null, $wTax), 0, $this->products[$i]['quantity']);
              $this->products[$i]['final_price_exc'] = $currencies->calculate_price($this->_cart->configurator_price($this->products[$i]['id'], null, 0), 0, $this->products[$i]['quantity']);
              $this->products[$i]['final_price'] = $currencies->display_price($this->_cart->configurator_price($this->products[$i]['id'], null, $wTax), 0, $this->products[$i]['quantity']);
              if ($this->products[$i]['standard_price'] !== false){
                $this->products[$i]['standard_price'] = $currencies->display_price($this->_cart->configurator_price($this->products[$i]['id'], null, $wTax, 'standard_price'), 0, $this->products[$i]['quantity']);
              }
            } elseif (ArrayHelper::getValue($this->products, [$i,'parent']) != '') { // if is sub-product
              // don't show pice
              $this->products[$i]['final_price'] = '';
              $this->products[$i]['final_price_inc'] = 0;
              $this->products[$i]['final_price_exc'] = 0;
            } else {
                if ((abs($this->products[$i]['final_price']) < 0.01 && defined('PRODUCT_PRICE_FREE') && PRODUCT_PRICE_FREE == 'true')) {
                    $this->products[$i]['final_price'] = TEXT_FREE;
                    $this->products[$i]['final_price_inc'] = 0;
                    $this->products[$i]['final_price_exc'] = 0;
                } else {
                    $this->products[$i]['final_price_inc'] = $currencies->calculate_price($this->products[$i]['final_price'], \common\helpers\Tax::get_tax_rate($this->products[$i]['tax_class_id']), $this->products[$i]['quantity']);
                    $this->products[$i]['final_price_exc'] = $currencies->calculate_price($this->products[$i]['final_price'], 0, $this->products[$i]['quantity']);
                    $this->products[$i]['final_price'] = $currencies->display_price($this->products[$i]['final_price'], \common\helpers\Tax::get_tax_rate($this->products[$i]['tax_class_id']), $this->products[$i]['quantity']);
                }
              if (ArrayHelper::getValue($this->products, [$i,'standard_price']) !== false){
                $this->products[$i]['standard_price'] = $currencies->display_price(ArrayHelper::getValue($this->products, [$i,'standard_price']), \common\helpers\Tax::get_tax_rate($this->products[$i]['tax_class_id']), $this->products[$i]['quantity']);
              }
            }
            if ($this->products[$i]['model'] == 'VIRTUAL_GIFT_CARD'){
                $this->products[$i]['link'] = tep_href_link('catalog/gift-card', 'products_id='. $this->products[$i]['id'] . '&platform_id=' . $this->products[$i]['platform_id']);
            } else {
                $this->products[$i]['link'] = tep_href_link('catalog/product', 'products_id='. $this->products[$i]['id_link'] . '&platform_id=' . $this->products[$i]['id_platform']);
            }            
            $this->products[$i]['image'] = Images::getImageUrl($this->products[$i]['id'], 'Small');
            $_p = $container->getProduct($this->products[$i]['id']);
            if (isset($_p['promo_details']) && is_array($_p['promo_details'])){
                $this->products[$i]['promo_class'] = implode(" ", \yii\helpers\ArrayHelper::getColumn($_p['promo_details'], 'promo_class'));
                $this->products[$i]['promo_icon'] = implode(" ", \yii\helpers\ArrayHelper::getColumn($_p['promo_details'], 'promo_icon'));
                $this->products[$i]['promo_message'] = implode("<br>", \yii\helpers\ArrayHelper::getColumn($_p['promo_details'], 'promo_message'));
            }
            if (ArrayHelper::getValue($this->products, [$i,'parent']) == '') { // if not sub-product
              if ( $this->products[$i]['ga'] ) {
                $this->products[$i]['remove_link'] = Yii::$app->urlManager->createAbsoluteUrl([$this->getContollerDispatch(), 'action' => 'remove_giveaway', 'product_id' => $this->products[$i]['id'] ]);
              } else {
                $this->products[$i]['remove_link'] = Yii::$app->urlManager->createAbsoluteUrl([$this->getContollerDispatch(), 'action' => $this->getRemoveAction(), 'product_id' => $this->products[$i]['id'] ]);
              }
            }

            $this->products[$i]['gift_wrap_price_formated'] = ($this->products[$i]['gift_wrap_price']<0?'-':'+') . $currencies->display_price(abs($this->products[$i]['gift_wrap_price']), \common\helpers\Tax::get_tax_rate(defined('MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS')?MODULE_ORDER_TOTAL_GIFT_WRAP_TAX_CLASS:0));

            $this->products[$i]['all_in_stock'] = 1;
            if (STOCK_CHECK == 'true'){
                if ( isset($this->products[$i]['stock_info']) ) {
                  if ( $this->bound_quantity_ordered==false ) {
                    $this->bound_quantity_ordered = $this->products[$i]['stock_info']['order_instock_bound'] ?? null;
                  }
                  if ( !$this->products[$i]['stock_info']['allow_out_of_stock_checkout'] ) {
                    $this->oos_product_incart = true;
                  }
                }
            }
            $this->products[$i]['order_quantity_data'] = \common\helpers\Product::get_product_order_quantity($this->products[$i]['id']);

            if (isset($this->products[$i]['attributes']) && is_array($this->products[$i]['attributes'])){
                $attrText = \common\classes\PropsWorkerAttrText::getAttrText($this->products[$i]['props'] ?? null);
                if (isset($this->products[$i]['virtual_gift_card']) && $this->products[$i]['virtual_gift_card'] && $this->products[$i]['attributes'][0] > 0) {
                    $virtual_gift_card = tep_db_fetch_array(tep_db_query("select vgcb.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, p.products_model, p.products_image, p.products_weight, p.products_tax_class_id, vgcb.products_price, vgcb.virtual_gift_card_recipients_name, vgcb.virtual_gift_card_recipients_email, vgcb.virtual_gift_card_message, vgcb.virtual_gift_card_senders_name, vgcb.send_card_date from " . TABLE_VIRTUAL_GIFT_CARD_BASKET . " vgcb, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id = '" . (int)$languages_id ."' and pd1.platform_id = '" . (int)Yii::$app->get('platform')->config()->getPlatformToDescription() . "' where length(vgcb.virtual_gift_card_code) = 0 and vgcb.virtual_gift_card_basket_id = '" . (int)$this->products[$i]['attributes'][0] . "' and p.products_id = vgcb.products_id and pd.platform_id = '".intval(\common\classes\platform::defaultId())."' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and " . (!Yii::$app->user->isGuest? " vgcb.customers_id = '" . (int)Yii::$app->user->getId() . "'" : " vgcb.customers_id = '0' and vgcb.session_id = '" . Yii::$app->getSession()->get('gift_handler') . "'")));
                    $this->products[$i]['attr'][0]['products_id'] = $virtual_gift_card['products_id'];
                    $this->products[$i]['attr'][0]['products_options_name'] = TEXT_GIFT_CARD_DETAILS;
                    $this->products[$i]['attr'][0]['options_values_id'] = $this->products[$i]['attributes'][0];
                    $this->products[$i]['attr'][0]['products_options_values_name'] = "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_recipients_name'])) $this->products[$i]['attr'][0]['products_options_values_name'] .= TEXT_GIFT_CARD_RECIPIENTS_NAME . ' ' . $virtual_gift_card['virtual_gift_card_recipients_name'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_recipients_email'])) $this->products[$i]['attr'][0]['products_options_values_name'] .= TEXT_GIFT_CARD_RECIPIENTS_EMAIL . ' ' . $virtual_gift_card['virtual_gift_card_recipients_email'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_message'])) $this->products[$i]['attr'][0]['products_options_values_name'] .= TEXT_GIFT_CARD_MESSAGE . ' ' . $virtual_gift_card['virtual_gift_card_message'] . "\n";
                    if (tep_not_null($virtual_gift_card['virtual_gift_card_senders_name'])) $this->products[$i]['attr'][0]['products_options_values_name'] .= TEXT_GIFT_CARD_SENDERS_NAME . ' ' . $virtual_gift_card['virtual_gift_card_senders_name'] . "\n";
                    if (tep_not_null($virtual_gift_card['send_card_date']) && $virtual_gift_card['send_card_date'] != '0000-00-00 00:00:00') $this->products[$i]['attr'][0]['products_options_values_name'] .= "<br>".TEXT_GIFT_CARD_SEND . ': ' . \common\helpers\Date::date_short ($virtual_gift_card['send_card_date']) . "\n";
                } else {
                    foreach ($this->products[$i]['attributes'] as $option => $value) {
                        $this->products[$i]['hidden_fields'] .= tep_draw_hidden_field('id[' . $this->products[$i]['id'] . '][' . $option . ']', $value);
                        
                        $option_arr = explode('-', $option);
                        $attributes_values = ProductsAttributes::find()
                                ->alias('pa')
                                ->joinWith('productsOptions')
                                ->joinWith('productsOptionsValues')
                                ->where(['pa.products_id' => (int)(ArrayHelper::getValue($option_arr, 1) > 0 ? $option_arr[1] : $this->products[$i]['id']),
                                         'pa.options_id' => (int)$option_arr[0], 
                                         'pa.options_values_id' => (int)$value])
                                ->one();                        
                        if ($attributes_values){                            
                            $this->products[$i]['attr'][$option]['products_id'] = $attributes_values->products_id;
                            $this->products[$i]['attr'][$option]['products_options_name'] = $attributes_values->productsOptions->products_options_name;
                            $this->products[$i]['attr'][$option]['options_values_id'] = $value;
                            $this->products[$i]['attr'][$option]['products_options_values_name'] = $attributes_values->productsOptionsValues->products_options_values_name;

                            $this->products[$i]['attr'][$option]['products_options_values_text'] = $attrText[$option] ?? null;
                            $options_values_price = \common\helpers\Attributes::get_options_values_price($attributes_values->products_attributes_id);
                            $this->products[$i]['attr'][$option]['options_values_price'] = $options_values_price;
                            $this->products[$i]['attr'][$option]['price_prefix'] = $attributes_values->price_prefix;
                            if ( strpos($attributes_values->price_prefix,'%')!==false ) {
                                $this->products[$i]['attr'][$option]['display_price'] = substr($attributes_values->price_prefix,0,1).''.\common\helpers\Output::percent($options_values_price);
                            }else{
                                $this->products[$i]['attr'][$option]['display_price'] = ($attributes_values->price_prefix=='-'?'-':'+'). $currencies->display_price($options_values_price, \common\helpers\Tax::get_tax_rate($this->products[$i]['tax_class_id']));
                            }
                        }
                    }
                }
            }
            
            if($ext = \common\helpers\Extensions::isAllowed('MultiCart')) {
                $ext::productsBlock($this->products[$i]);
            }
            \Yii::$app->get('PropsHelper')::describeProduct($this->products[$i]);
        }
        for ($i=0, $n=sizeof($this->products); $i<$n; $i++) {
// {{ Products Bundle Sets
            
            if (!isset($this->products[$i]['bundles_info']) || !is_array($this->products[$i]['bundles_info'])) continue;
            $this->products[$i]['is_bundle'] = false;
            foreach( $this->products[$i]['bundles_info'] as $bpid => $bundle_info ) {
                $this->products[$i]['bundles_info'][$bpid]['attr'] = array();

                if ( isset($this->products[$i]['attr']) && is_array($this->products[$i]['attr']) && count($this->products[$i]['attr'])>0) {
                    foreach ($this->products[$i]['attr'] as $__option_id=>$__option_value_data) {
                        if ( strpos($__option_id.'-', '-'.$bpid.'-')===false ) continue;
                        $this->products[$i]['bundles_info'][$bpid]['attr'][$__option_id] = $__option_value_data;
                        unset($this->products[$i]['attr'][$__option_id]);
                    }
                }
                $this->products[$i]['bundles_info'][$bpid]['with_attr'] = count($this->products[$i]['bundles_info'][$bpid]['attr'])>0; 
            }
            $this->products[$i]['is_bundle'] = true;
// }}
        }
        $this->products = array_values($this->products);
    }
    
    public function getProducts(){
        $this->decorateProducts();
        $this->allow_checkout = 
            !(
               $this->oos_product_incart
            || $this->_cart->hasBlockedProducts()
            || $this->bound_quantity_ordered);
        
        return $this->products;
    }
    
}

