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
 * Price for all conditions
 */

namespace common\components;

use Yii;
use common\models\Products;

class DisabledSettings {
    /*probably would be some configurations for each case*/
    private $cases = [
        'promotion' => false, 
        'qty_discount' => false,
        'group_discount' => false,
        'apply_coupon' => false,
        'sale' => false,
        'bundle_discount' => false,
        'configurator_discount' => false,
    ];

    public function __construct($products_id) {
        $status = 0;
        $tmp = Yii::$container->get('products')->getProduct((int)$products_id);//->getArrayCopy();
        $status = !!($tmp['disable_discount'] ?? null);
//        if ($tmp && isset($tmp['disable_discount'])) {
//          $check = $tmp;
//        } else
//
//        $check = Products::find()->select('disable_discount')
//                ->where('products_id = :id', [':id' => (int)$products_id])
//                ->asArray()->one();
//        if ($check){
//            $status = $check['disable_discount'];
//        }
        if ($status){
            $this->cases = [
                'promotion' => true,
                'qty_discount' => true,
                'group_discount' => true,
                'apply_coupon' => true,
                'sale' => true,
                'bundle_discount' => true,
                'configurator_discount' => true,
            ];
        }
        
        return $this;
    }
    
    public function applyPromotion(){
        return !$this->cases['promotion'];
    }
    
    public function applyQtyDiscount(){
        return !$this->cases['qty_discount'];
    }
    
    public function applyGroupDiscount(){
        return !$this->cases['group_discount'];
    }
    
    public function applyCoupon(){
        return !$this->cases['apply_coupon'];
    }
    
    public function applySale(){
        return !$this->cases['sale'];
    }
    
    public function applyBundleDiscount(){
        return !$this->cases['bundle_discount'];
    }
    
    public function applyConfiguratorDiscount(){
        return !$this->cases['configurator_discount'];
    }
}
