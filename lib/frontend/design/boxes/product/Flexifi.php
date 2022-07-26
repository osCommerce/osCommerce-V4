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

class Flexifi extends \yii\base\Widget
{
    public $file;
    public $params;
    public $settings;

    public static function runManual($uProductId = 0)
    {
        $return = '';
        if ((int)$uProductId > 0) {
            $self = new self();
            $return = $self->run($uProductId);
            unset($self);
        }
        return $return;
    }

    public function run($uProductId = 0)
    {
        $product_id = 0;
        if ( !empty($this->params['products_id']) ){
            $product_id = (int)$this->params['products_id'];
        }elseif ( $uProductId>0 ){
            $product_id = $uProductId;
        }elseif(\Yii::$app->request->get('products_id','')){
            $product_id = intval(\Yii::$app->request->get('products_id',''));
        }
        /*$params = \Yii::$app->request->get();
        if (!$params['products_id']) {
            if ((int)$uProductId > 0) {
                $params['products_id'] = trim($uProductId);
            } else {
                return '';
            }
        }*/
        if ( !$product_id ) return '';

        $products = \Yii::$container->get('products');
        $product = $products->getProduct($product_id /*$params['products_id']*/);
        if (is_object($product)) {
            if ($product['is_bundle']) {
                $details = \common\helpers\Bundles::getDetails(['products_id' => $product['products_id']]);
                $price = $details['actual_bundle_price_clear'];
            } else {
                $priceInstance = \common\models\Product\Price::getInstance($product['products_id']);
                $product['products_price'] = $priceInstance->getInventoryPrice(['qty' => 1]);
                $product['special_price'] = $priceInstance->getInventorySpecialPrice(['qty' => 1]);
                if (!isset($product['tax_rate'])) {
                    $product['tax_rate'] = \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']);
                }
                $currencies = \Yii::$container->get('currencies');
                if (isset($product['special_price']) && $product['special_price'] !== false) {
                    $price = $currencies->display_price_clear($product['special_price'], $product['tax_rate'], 1);
                } else {
                    $price = $currencies->display_price_clear($product['products_price'], $product['tax_rate'], 1);
                }
            }
            if ($price > 0) {
                return \common\extensions\FlexiFi\FlexiFi::getPopupButtonHtml($product, $price);
            }
        }
        return '';
    }
}