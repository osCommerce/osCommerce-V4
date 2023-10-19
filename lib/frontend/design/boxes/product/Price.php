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
use frontend\design\IncludeTpl;
use common\helpers\Tax;
use common\helpers\Product;

class Price extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $customer_groups_id = (int) \Yii::$app->storage->get('customer_groups_id');
        $params = Yii::$app->request->get();
        if (isset($this->settings[0]['change_price']) && $this->settings[0]['change_price']) {
            $qty = $params['qty'] ?? 1;
        } else {
            $qty = 1;
        }

        $special_ex = $old_ex = $current_ex = '';
        /** @var \common\classes\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');

        if (!$params['products_id']) {
            return '';
        }

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
            $return_price = $ext::checkPackPrice($params['products_id']);
        }else{
            $return_price = true;
        }

        $products = Yii::$container->get('products');
        $product = $products->getProduct($params['products_id']);
        if (!$product->checkAttachedDetails($products::TYPE_STOCK)){
            $product_qty = Product::get_products_stock($params['products_id']);
            $stock_info = \common\classes\StockIndication::product_info(array(
                'products_id' => $params['products_id'],
                'products_quantity' => $product_qty,
            ));
            $product = $products->attachDetails($params['products_id'], [$products::TYPE_STOCK => $stock_info])->getProduct($params['products_id']);
        } else {
            $stock_info = $product[$products::TYPE_STOCK];
        }

        /**
         * $stock_indicator_public['display_price_options']
         * 0 - display
         * 1 - hide
         * 2 - hide if zero
         */
        /** @var \common\extensions\Quotations\Quotations $ext */
        if (($stock_info['flags']['request_for_quote'] && ( ($ext = \common\helpers\Extensions::isAllowed('Quotations')) && !$ext::optionIsPriceShow() ) /*&& $stock_info['flags']['display_price_options'] != 0*/) ||
            ($stock_info['flags']['display_price_options'] == 1) ||
            (abs($product['products_price']) < 0.01 && $stock_info['flags']['display_price_options'] == 2 && !$product['is_bundle']) ){
            $return_price = false;
        }

        if(!$return_price){
            return '';
        }

        if ((abs($product['products_price']) < 0.01 && defined('PRODUCT_PRICE_FREE') && PRODUCT_PRICE_FREE == 'true')) {

            if (isset($product['special_expiration_date']) && !empty($product['special_expiration_date'])) {
                $priceValidUntil = date("Y-m-d", strtotime($product['special_expiration_date']));
            } else {
                $priceValidUntil = date("Y-m-d", time() + 60*60*24*365);
            }
            \frontend\design\JsonLd::addData(['Product' => [
                'offers' => [
                    '@type' => 'Offer',
                    'url' => Yii::$app->urlManager->createAbsoluteUrl(['catalog/product', 'products_id' => $params['products_id']]),
                    'availability' => 'https://schema.org/' . ($stock_info['stock_code'] == 'out-stock' ? 'OutOfStock' : 'InStock'),
                    'price' => '0.00',
                    'priceCurrency' => \Yii::$app->settings->get('currency'),
                    'priceValidUntil' => $priceValidUntil,
                ]
            ]], ['Product', 'offers']);

            //return TEXT_FREE;
            return IncludeTpl::widget(['file' => 'boxes/product/price.tpl', 'params' => [
                'special' => '',
                'old' => '',
                'current' => TEXT_FREE,
                'stock_info' => $stock_info,
                'expires_date' => $product['special_expiration_date'] ? date("Y-m-d", strtotime($product['special_expiration_date'])) : '',
            ]]);
        }
        /*
        if ($product['is_bundle']) {
            $details = \common\helpers\Bundles::getDetails(['products_id' => $product['products_id']]);
            if ($details['full_bundle_price_clear'] > $details['actual_bundle_price_clear']) {
                $special_value = $details['actual_bundle_price'];
                $special = $details['actual_bundle_price'];
                if (!empty($details['actual_bundle_price_ex'])) {
                  $special_ex = $details['actual_bundle_price_ex'];
                }
                $old = $details['full_bundle_price'];
                if (!empty($details['full_bundle_price_ex'])) {
                  $old_ex = $details['full_bundle_price_ex'];
                }
                $current = '';
            } else {
                $special_value = 0;
                $special = '';
                $old = '';
                $current = $details['actual_bundle_price'];
                if (!empty($details['actual_bundle_price_ex'])) {
                  $current_ex = $details['actual_bundle_price_ex'];
                }
            }
            $jsonPrice = $details['actual_bundle_price_clear'];
        } else {
            $priceInstance = \common\models\Product\Price::getInstance($product['products_id']);
            $product['products_price'] = $priceInstance->getInventoryPrice(['qty' => $qty]);
            $product['special_price'] = $priceInstance->getInventorySpecialPrice(['qty' => $qty]);
            if (isset($product['special_price']) && $product['special_price'] !== false) {
                $special_value = $product['special_price'];
                $special_clear = $currencies->display_price_clear($product['special_price'], $product['tax_rate'], $qty);
                $special = $currencies->format($special_clear, false, '', '', true, true);

                $old_clear = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], $qty);
                $old = $currencies->format($old_clear, false);

                $special_one_clear = $currencies->display_price_clear($product['special_price'], $product['tax_rate'], 1);
                $special_one = $currencies->format($special_one_clear, false, '', '', true, true);

                $old_one_clear = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], 1);
                $old_one = $currencies->format($old_one_clear, false);

                if ($product['tax_rate']>0 && defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') { //&& (!\Yii::$app->storage->has('taxable') || (\Yii::$app->storage->has('taxable') && \Yii::$app->storage->get('taxable')))  - switcher from box and account ...
                  $special_ex_clear = $currencies->display_price_clear($product['special_price'], 0, $qty);
                  $special_ex = $currencies->format($special_ex_clear, false);

                  $old_ex_clear = $currencies->display_price_clear($product['products_price'], 0, $qty);
                  $old_ex = $currencies->format($old_ex_clear, false);

                  $special_ex_one_clear = $currencies->display_price_clear($product['special_price'], 0, 1);
                  $special_ex_one = $currencies->format($special_ex_one_clear, false);
                  
                  $old_ex_one_clear = $currencies->display_price_clear($product['products_price'], 0, 1);
                  $old_ex_one = $currencies->format($old_ex_one_clear, false);
                }
                $current = $current_ex = '';
                $jsonPrice = $currencies->display_price_clear($product['special_price'], $product['tax_rate'], 1);
            } else {
                $special_value = 0;
                $special = '';
                $old = '';
                $current = $currencies->display_price($product['products_price'], $product['tax_rate'], $qty, true, true);
                $current_one = $currencies->display_price($product['products_price'], $product['tax_rate'], 1, true, true);
                $jsonPrice = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], 1);
                if ($product['tax_rate']>0 && defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                  $current_ex = $currencies->display_price($product['products_price'], 0, $qty, false, false);
                  $current_ex_one = $currencies->display_price($product['products_price'], 0, 1, false, false);
                }
                
                if (\common\helpers\Customer::check_customer_groups($customer_groups_id, 'groups_price_as_special') && $product['products_price_main'] > $product['products_price']) {
                    $special_value = $product['products_price'];
                    $special_one = $current_one;
                    $special = $current;
                    $old_clear = $currencies->display_price_clear($product['products_price_main'], $product['tax_rate'], $qty);
                    $old = $currencies->format($old_clear, false);
                    
                    $old_one_clear = $currencies->display_price_clear($product['products_price_main'], $product['tax_rate'], 1);
                    $old_one = $currencies->format($old_one_clear, false);
                    
                    $special_clear = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], $qty);
                    $special_one_clear = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], 1);
                    
                    if ($product['tax_rate']>0 && defined("DISPLAY_BOTH_PRICES") && DISPLAY_BOTH_PRICES =='True') {
                        $special_ex = $current_ex;
                        $old_ex_clear = $currencies->display_price($product['products_price_main'], 0, $qty);
                        $old_ex = $currencies->format($old_ex_clear, false);
                        $old_ex_one_clear = $currencies->display_price_clear($product['products_price_main'], 0, 1, false, false);
                        $old_ex_one = $currencies->format($old_ex_one_clear, false);
                        $current_ex = '';
                        $special_ex_clear = $currencies->display_price_clear($product['products_price'], 0, $qty);
                        $special_ex_one_clear = $currencies->display_price_clear($product['products_price'], 0, 1, false, false);
                    }
                    $current = '';
                }
            }
            
        }
*/

        $pDetails = Product::getPiceDetails($product, $qty, $customer_groups_id);
        $jsonPrice = $pDetails['jsonPrice'];
        $special_value = $pDetails['special_value'];

        $special = $pDetails['formatted']['special'];
        $old = $pDetails['formatted']['old'];
        $current = $pDetails['formatted']['current_ex'];
        $special_ex = $pDetails['formatted']['special_ex'];
        $old_ex = $pDetails['formatted']['old_ex'];
        $current_ex = $pDetails['formatted']['current_ex'];

        if ($ext = \common\helpers\Acl::checkExtensionAllowed('BusinessToBusiness', 'allowed')) {
            if ($ext::changeShowPrice($customer_groups_id)) {
                $special = $old = $current = '';
                $special_ex = $old_ex = $current_ex = '';
            }
        }

        if ($jsonPrice && !(Yii::$app->user->isGuest && \common\helpers\PlatformConfig::getFieldValue('platform_please_login'))) {

            \frontend\design\JsonLd::addData(['Product' => [
                'offers' => [
                    '@type' => 'Offer',
                    'url' => Yii::$app->urlManager->createAbsoluteUrl(['catalog/product', 'products_id' => $params['products_id']]),
                    'availability' => 'https://schema.org/' . ($stock_info['stock_code'] == 'out-stock' ? 'OutOfStock' : 'InStock'),
                ]
            ]], ['Product', 'offers']);

            if (isset($product['special_expiration_date']) && !empty($product['special_expiration_date'])) {
                \frontend\design\JsonLd::addData(['Product' => [
                    'offers' => [
                        'priceValidUntil' => date("Y-m-d", strtotime($product['special_expiration_date'])),
                    ]
                ]], ['Product', 'offers', 'priceValidUntil']);
            } else {
                \frontend\design\JsonLd::addData(['Product' => [
                    'offers' => [
                        'priceValidUntil' => date("Y-m-d", time() + 60*60*24*180),
                    ]
                ]], ['Product', 'offers', 'priceValidUntil']);
            }
            \frontend\design\JsonLd::addData(['Product' => [
                'offers' => [
                    'price' => $jsonPrice,
                    'priceCurrency' => \Yii::$app->settings->get('currency'),
                ]
            ]], ['Product', 'offers', 'price']);
        }

        if ($special != '' && abs($special_value) < 0.01 && defined('PRODUCT_PRICE_FREE') && PRODUCT_PRICE_FREE == 'true') {
            $special = TEXT_FREE;
        }
        if ($special_ex != '' && abs($special_value) < 0.01 && defined('PRODUCT_PRICE_FREE') && PRODUCT_PRICE_FREE == 'true') {
            $special_ex = TEXT_FREE;
        }

        if (!empty($stock_info['eol']) && (empty($stock_info['flags']['can_add_to_cart']) || empty($stock_info['flags']['add_to_cart']))){
            return '';
        }

        return IncludeTpl::widget(['file' => 'boxes/product/price.tpl', 'params' =>  $pDetails['formatted'] + [
            'qty' => $qty,
            'id' => $this->id,
            'products_id' => $product['products_id'],
            'stock_info' => $stock_info,
            'settings' => $this->settings,
            'expires_date' => (isset($product['special_expiration_date']) && !empty($product['special_expiration_date']) ? date("Y-m-d", strtotime($product['special_expiration_date'])) : ''),
/*
            'special' => $special,
            'old' => $old,
            'current' => $current,
            'special_ex' => $special_ex,
            'old_ex' => $old_ex,
            'current_ex' => $current_ex,
            'special_one' => $special_one,
            'old_one' => $old_one,
            'current_one' => $current_one,
            'special_ex_one' => $special_ex_one,
            'old_ex_one' => $old_ex_one,
            'current_ex_one' => $current_ex_one,
*/
        ]]);
    }
}