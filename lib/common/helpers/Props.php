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

namespace common\helpers;

use Yii;

/**
 * All commented prieces of code are provided only as example
 */
class Props extends \common\classes\AbstractProps {

    private static $workers = null;

    public static function getWorkers()
    {
        if (is_null(self::$workers)) {
            self::$workers = [
                /* classes of \common\classes\PropsWorkerAbstract */
                \common\classes\PropsWorkerAttrText::class
            ];
            foreach (\common\helpers\Hooks::getList('product_props/init_workers') as $filename) {
                include($filename);
            }
        }
        return self::$workers;
    }

    public static function ParamsToXml($params = array(), $productId = false) {
        $data = [];
        foreach (self::getWorkers() as $worker) {
            $data = array_merge($data, $worker::paramsToXml($params, $productId));
        }
        if (count($data) == 0) {
            return '';
        }
        return self::toXML($data);
    }

    public static function explainParams($params = [], $tax_rate = 0) {
        $attr = false;
        /* if (isset($params['recipe'])) {
          if (!is_array($attr)) $attr = [];
          $recipeInfo = '';
          $newLogoAmount = 0;
          if ( is_array($params['recipe']['data']) ) {
          if ( !empty($params['recipe']['data']['views']) && is_array($params['recipe']['data']['views']) ) {
          $views = array_values($params['recipe']['data']['views']);
          static $counter = 0;
          $counter++;
          foreach ($views as $view) {
          if ( is_array($view) && !empty($view['image']) ) {
          $recipeInfo .= ' '.\common\helpers\Html::a($view['name'],$view['image'],[
          'target' => '_blank',
          'rel' => 'fancyGroup_'.$counter,
          'class'=>'fancybox',
          ]);
          }
          if (is_array($view) && !empty($view['new_logo_count'])){
          $newLogoAmount += (int)$view['new_logo_count'];
          }
          }
          if ( !empty($recipeInfo) ) $recipeInfo = '<div class="cart-recipe-images">'.$recipeInfo.'</div>';
          }
          }
          $priceInfo = '';
          if ( isset($params['cost']) && is_array($params['cost']) && $params['cost']['additionPrice'] ){
          $currencies = Yii::$container->get('currencies');
          // (£17.93: Set up £5.94, Application £11.99)
          $priceInfo = $currencies->display_price($params['cost']['additionPrice'], $tax_rate);
          $setupInfo = ($params['cost']['setupPrice']?" ".(defined('TEXT_PERSONALISATION_SETUP_COST')?TEXT_PERSONALISATION_SETUP_COST:'Set up')." ".$currencies->display_price($params['cost']['setupPrice'], $tax_rate):'');
          $applicationInfo = ($params['cost']['applicationPrice']?" ".(defined('TEXT_PERSONALISATION_APPLY_COST')?TEXT_PERSONALISATION_APPLY_COST:'Application')." ".$currencies->display_price($params['cost']['applicationPrice'], $tax_rate):'');
          if ( $setupInfo || $applicationInfo ){
          $priceInfo .= ':'.$setupInfo;
          if ($setupInfo && $applicationInfo) $priceInfo .= ',';
          $priceInfo .= $applicationInfo;
          }
          $priceInfo = " ({$priceInfo})";
          }

          $attr[] = [
          'products_options_name' => $params['recipe']['option'],
          'products_options_values_name' => $params['recipe']['value'] . $priceInfo,
          'extra_view' => $recipeInfo,
          ];
          } */
        return $attr;
    }

    /**
     * Return normalized urpid without props
     * @param type $uprid
     * @return type
     */
    public static function normalize_id($uprid) {
        foreach (self::getWorkers() as $worker) {
            $uprid = $worker::normalize_id($uprid);
        }
        return $uprid;
    }

    /**
     * Modify Cart contens key (product_id|uprid) particulary to props
     * @param type $products_id
     * @param type $props
     * @return type
     */
    public static function cartUprid($products_id, $props) {
        $products_id = self::normalize_id($products_id);
        if (!empty($props)) {
            $propsData = self::XmlToParams($props);
            if (is_array($propsData)) {
                foreach (self::getWorkers() as $worker) {
                    $products_id = $worker::cartUprid($products_id, $propsData);
                }
            }
        }
        return $products_id;
    }

    /**
     * Modify props on cart add
     * @param $props string props xml
     * @return string props xml
     */
    public static function onCartAdd($props) {
        $propsData = empty($props)? [] : static::XmlToParams($props);
        foreach (self::getWorkers() as $worker) {
            $propsData = $worker::onCartAdd($propsData);
        }
        return static::toXML($propsData);
    }

    /**
     * validate after login
     *
     * @return bool
     */
    public static function afterCartRestore($cart) {
        return self::cartChanged($cart);
    }

    public static function cartChanged($cart) {
        $updateCart = false;
        /* if ( is_array($cart->contents) && count($cart->contents)>0 ) {
          $cartRecipeIds = [];
          foreach ($cart->contents as $_prodIdx=>$_prodData) {
          if (isset($_prodData['props']) && !empty($_prodData['props'])) {
          $props = \common\helpers\Props::XmlToParams($_prodData['props']);
          if ( is_array($props) && !empty($props['recipe']['data']['recipe_id']) ) {
          $recipe_id = $props['recipe']['data']['recipe_id'];
          if ( !isset($cartRecipeIds[$recipe_id]) ) $cartRecipeIds[$recipe_id] = [];
          $cartRecipeIds[$recipe_id][] = $_prodIdx;
          }
          }
          }
          if ( count($cartRecipeIds)>0 ) {
          $logoStat = \common\helpers\Slapp::getRecipesLogoStat(array_keys($cartRecipeIds));
          if ( is_array($logoStat) ) {
          foreach ($logoStat as $recipe_id=>$recipe_stat){
          if ( !isset($cartRecipeIds[$recipe_id]) ) continue;
          foreach ($cartRecipeIds[$recipe_id] as $_prodIdx){
          $_prodData = $cart->contents[$_prodIdx];
          if (isset($_prodData['props']) && !empty($_prodData['props']) ) {
          $props = \common\helpers\Props::XmlToParams($_prodData['props']);
          if (is_array($props) && !empty($props['recipe']['data']['recipe_id'])) {
          $props['recipe']['stat'] = $recipe_stat;
          $props['recipe']['stat']['checkTime'] = date('Y-m-d H:i:s');
          $cart->contents[$_prodIdx]['props'] = \common\helpers\Props::toXML($props);
          $updateCart = true;
          }
          }
          }

          }
          }
          if ($updateCart){
          $cart->saveContents();
          }
          }
          } */

        return $updateCart;
    }

    /**
     * Update cart product data on checkout pages
     * @param $cartProduct
     * @return mixed
     */
    public static function applyToCartProductArray($cartProduct) {
//        if (isset($cartProduct['propsData']) && is_array($cartProduct['propsData'])) {
//            if (isset($cartProduct['propsData']['recipe']) && isset($cartProduct['propsData']['recipe']['data'])) {
//                if (isset($cartProduct['propsData']['recipe']['data']['views']) && is_array($cartProduct['propsData']['recipe']['data']['views'])) {
//                    $views = array_values($cartProduct['propsData']['recipe']['data']['views']);
//                    foreach ($views as $view) {
//                        if (is_array($view) && !empty($view['image'])) {
//                            $cartProduct['image_external'] = $view['image'];
//                            break;
//                        }
//                    }
//                }
//            }
//        }
        return $cartProduct;
    }

    /**
     * Display additional information on process page accordingly to product
     * @param type $orderProduct
     * @return string
     */
    public static function adminOrderProductView($orderProduct) {
        $info = '';

        /* if ( isset($orderProduct['propsData']) && is_array($orderProduct['propsData']) ) {
          $recipeInfo = '';
          if ( isset($orderProduct['propsData']['recipe']) && isset($orderProduct['propsData']['recipe']['data']) ) {
          if ( isset($orderProduct['propsData']['recipe']['data']['views']) && is_array($orderProduct['propsData']['recipe']['data']['views']) ) {
          $views = array_values($orderProduct['propsData']['recipe']['data']['views']);
          foreach ($views as $view) {
          if ( is_array($view) && !empty($view['image']) ) {
          $recipeInfo .= ' '.\common\helpers\Html::a($view['name'],$view['image'],[
          'target' => '_blank',
          'rel' => 'fancyGroup_'.preg_replace('/[^\d]+/','_',$orderProduct['id']),
          'class'=>'fancybox',
          ]);
          }
          }
          }
          }
          if ( !empty($recipeInfo) ) {
          $info .= '<br>' . $recipeInfo;
          }
          } */
        return $info;
    }

    public static function adminOrderImageUrl($orderProduct) {
        $info = '';
        /*if (isset($orderProduct['propsData']) && is_array($orderProduct['propsData'])) {
            $recipeInfo = '';
            if (isset($orderProduct['propsData']['recipe']) && isset($orderProduct['propsData']['recipe']['data'])) {
                if (isset($orderProduct['propsData']['recipe']['data']['views']) && is_array($orderProduct['propsData']['recipe']['data']['views'])) {
                    $views = array_values($orderProduct['propsData']['recipe']['data']['views']);
                    foreach ($views as $view) {
                        if (is_array($view) && !empty($view['image'])) {
                            $recipeInfo .= $view['image'];
                        }
                    }
                }
            }
            if (!empty($recipeInfo)) {
                $info .= $recipeInfo;
            }
        }*/
        return $info;
    }

}
