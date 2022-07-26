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

namespace backend\design\editor;


use Yii;
use yii\base\Widget;

class Product extends Widget {
    
    public $manager;
    public $product;
    public $edit = false;
    
    public function init(){
        parent::init();
    }    
    
    public function run(){
        
        if ($this->product){
            
            return $this->render('product', [
                'manager' => $this->manager,
                'queryParams' => array_merge(['editor/show-basket'], Yii::$app->request->getQueryParams()),
                'tax_class_array' => \common\helpers\Tax::get_complex_classes_list(),
                'tax_address' => $this->manager->getOrderInstance()->tax_address,
                'edit' => $this->edit,
                'product' => $this->product,
            ]);
                                    
            $render = 'product_details';
            if ($this->edit) {
                /*$uprid = urldecode($params['products_id']);
                $uprid = \common\helpers\Inventory::normalize_id($uprid);
                $params['product'] = null;
                if ($this->manager->getCart()->in_cart($uprid) ) {
                    $products = $this->manager->getCart()->get_products();

                    if (count($products)) {
                        foreach ($products as $_p) {
                            if ($_p['id'] == $uprid && !$_p['ga']) {
                                $_p['products_id'] = (int) $_p['id'];
                                $_p['final_price'] = $_p['final_price'] * $currencies->get_market_price_rate(DEFAULT_CURRENCY, $currency);
                                $_p['old_name'] = addslashes($_p['name']);//addslashes(\common\helpers\Product::get_backend_products_name($_p['id'], $language_id));
                                $_p['name'] = addslashes($_p['name']);
                                $_p['qty'] = (int) $_p['quantity'];
                                $ov = $cart->getOwerwritten($uprid);
                                $_p['selected_rate'] = 0;
                                if (isset($ov['tax_selected']))
                                    $_p['selected_rate'] = $ov['tax_selected'];

                                $_p['price_manualy_modified'] = ($cart->getOwerwrittenKey($_p['id'], 'final_price') ? 'true' : 'false');
                                $params['product'] = $_p;
                                break;
                            }
                        }
                    }
                }
                $render = 'edit_product';
                $params['is_editing'] = true;*/
            }
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('PackUnits', 'allowed')) {
                $params['product_details'] = $ext::quantityBoxFrontend($params['product'], $params);
            }
            
            $params['queryParams'] = array_merge(['editor/show-basket'], Yii::$app->request->getQueryParams());
            $params['manager'] = $this->manager;
            return $this->render('product', $params);
        }
        
        
    }
    
}
