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

namespace frontend\design\boxes\cart;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;
use frontend\design\Info;
use common\classes\platform;
use common\helpers\Product;
use common\models\OrdersProducts;

class DependedProducts extends Widget
{
  use \common\helpers\SqlTrait;

  public $file;
  public $params;
  public $settings;

  public function init()
  {
    parent::init();
  }

  public function run()
  {
    $languages_id = \Yii::$app->settings->get('languages_id');
    $params = Yii::$app->request->get();

        if ($this->settings[0]['params']) {
            $max = $this->settings[0]['params'];
        } else {
            $max = 4;
        }

        $productsIds = [];
        $notInCartIds = [];
        global $cart;
        foreach ($cart->get_products() as $product) {
            $productsIds[] = $product['id'];
        }
        
        if (count($productsIds) > 0 && \common\helpers\Acl::checkExtensionAllowed('Promotions')) {
            $promotions = \common\extensions\Promotions\models\Promotions::onlyPromotion('cart_discount', \common\classes\platform::currentId())->all();
            if ($promotions){
                $foundProduct = false;
                $foundId = 0;
                foreach($promotions as $promo) {
                    if (isset($promo->sets) && is_array($promo->sets)) {
                        foreach ($promo->sets as $set) {
                            if (in_array($set->promo_slave_id, $productsIds) && $set->promo_slave_type == 4) {
                                $foundProduct = true;
                                $foundId = $set->promo_id;
                                break;
                            }
                        }
                    }
                }
                if ($foundProduct && $foundId > 0) {
                    foreach($promotions as $promo) {
                        if (isset($promo->sets) && is_array($promo->sets)) {
                            foreach ($promo->sets as $set) {
                                if (!in_array($set->promo_slave_id, $productsIds) && $set->promo_slave_type == 0 && $set->promo_id == $foundId) {
                                    $notInCartIds[] = $set->promo_slave_id;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if (count($notInCartIds) == 0) {
            return '';
        }
        
        $q = new \common\components\ProductsQuery([
          'limit' => (int)$max,
          'customAndWhere' => ['IN', 'p.products_id', $notInCartIds],
        ]);

        $this->settings['listing_type'] = 'depended-products';
        $products = Info::getListProductsDetails($q->buildQuery()->allIds(), $this->settings);

        if (count($products) > 0) {
            $currencies = \Yii::$container->get('currencies');

            if (\common\helpers\Acl::checkExtensionAllowed('Promotions')) {
                foreach ($products as $idx => $_product) {
                    $promoPrice = \common\extensions\Promotions\models\Product\PromotionPrice::getInstance($_product['products_id']);
                    $price = $promoPrice->getPosiblePromotionPrice();
                    if ($price !== false){
                        $price_with_tax = $currencies->calculate_price($price, \common\helpers\Tax::get_tax_rate($_product['products_tax_class_id']), 1);
                        $products[$idx]['price_old'] = $products[$idx]['price'];
                        unset($products[$idx]['price']);
                        $products[$idx]['price_special'] = $currencies->format($price_with_tax, false);
                        $products[$idx]['calculated_price'] = $price_with_tax;
                        $products[$idx]['calculated_price_exc'] = $price;
                    }
                }
            }
            
            if (in_array($this->settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])) {
                return IncludeTpl::widget([
                    'file' => 'boxes/product/depended-products.tpl',
                    'params' => [
                        'products' => $products,//Yii::$container->get('products')->getAllProducts($this->settings['listing_type']),
                        'settings' => $this->settings
                    ]
                ]);
            } else {
                return \frontend\design\boxes\ProductListing::widget([
                    'products' => $products,//Yii::$container->get('products')->getAllProducts($this->settings['listing_type']),
                    'settings' => $this->settings,
                    'id' => $this->id
                ]);
            }


      } else {
        return '';
      }
    
  }
}