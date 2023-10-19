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


use yii\helpers\ArrayHelper;

class SubProduct
{
    public static function getChildrenIds($productId)
    {
        return ArrayHelper::map(
            \common\models\Products::find()
                ->where(['parent_products_id'=>$productId])
                ->select(['products_id'])
                ->asArray()
                ->all(),
            'products_id','products_id');
    }

    public static function copyParentAttributesToChildren($product_id)
    {
        $invList = static::copyInventoryAttributesList();
        $inventory_attributes = array_merge($invList['common'], $invList['price']);

        // parent
        $parentModel = \common\api\models\AR\Products::findOne($product_id);
        $data = $parentModel->exportArray(['attributes'=>['*'=>['options_id','options_values_id','is_virtual', 'products_options_sort_order']],'inventory' => ['*'=>$inventory_attributes],]);
        $parentAttributes = array_filter($data['attributes'], function($item){ return !$item['is_virtual']; });

        $children_ids = static::getChildrenIds($product_id);
        foreach ( $children_ids as $children_id ){
            if ($childProduct = \common\api\models\AR\Products::findOne($children_id)) {
                $childData = $childProduct->exportArray(['attributes'=>['*'=>['options_id','options_values_id','is_virtual', 'products_options_sort_order']]]);
                $childAttributes = array_filter($childData['attributes'], function($item){ return !!$item['is_virtual']; });
                $applyAttributes = array_merge($parentAttributes,$childAttributes);

                if ( $childProduct->products_id_price==$childProduct->parent_products_id ) {
                    // child use parent price
                    $childProduct->importArray(['attributes' => $applyAttributes, 'inventory' => $data['inventory']??null]);
                }else {
                    // child with own price
                    $common_inventory = [];
                    foreach ($data['inventory'] as $_inv_idx=>$_inventory_row) {
                        $common_inventory[$_inv_idx] = [];
                        foreach ($invList['common'] as $_copy_key1 => $_copy_key2) {
                            if (is_numeric($_copy_key1)) {
                                $common_inventory[$_inv_idx][$_copy_key2] = $_inventory_row[$_copy_key2];
                            }else{
                                $common_inventory[$_inv_idx][$_copy_key1] = $_inventory_row[$_copy_key1];
                            }
                        }
                    }
                    $childProduct->importArray(['attributes' => $applyAttributes, 'inventory'=>$common_inventory]);
                }
                $childProduct->save();
            }
        }
    }

    protected static function copyInventoryAttributesList()
    {
        return [
            'price' => ['inventory_price', 'inventory_discount_price', 'price_prefix', 'inventory_full_price','inventory_discount_full_price','inventory_tax_class_id','price'=>['*'=>['*']]],
            'common' => ['inventory_weight','stock_indication_id','stock_delivery_terms_id','stock_control','non_existent','attribute_map']
        ];
    }

    public static function copyAttributesFromParent($child_product_id)
    {
        $invList = static::copyInventoryAttributesList();
        $inventory_attributes = array_merge($invList['common'], $invList['price']);

        if ($childProduct = \common\api\models\AR\Products::findOne($child_product_id)) {
            if ($childProduct->parent_products_id>0 && $parentModel = \common\api\models\AR\Products::findOne($childProduct->parent_products_id)) {
                $data = $parentModel->exportArray(['attributes' => ['*' => ['options_id', 'options_values_id', 'is_virtual']],'inventory' => ['*'=>$inventory_attributes],]);
                if ( $childProduct->products_id_price==$childProduct->parent_products_id ){
                    // child use parent price
                    $childProduct->importArray(['attributes' => $data['attributes'], 'inventory'=>$data['inventory']]);
                }else{
                    // child with own price
                    $common_inventory = [];
                    foreach ($data['inventory'] as $_inv_idx=>$_inventory_row) {
                        $common_inventory[$_inv_idx] = [];
                        foreach ($invList['common'] as $_copy_key1 => $_copy_key2) {
                            if (is_numeric($_copy_key1)) {
                                $common_inventory[$_inv_idx][$_copy_key2] = $_inventory_row[$_copy_key2];
                            }else{
                                $common_inventory[$_inv_idx][$_copy_key1] = $_inventory_row[$_copy_key1];
                            }
                        }
                    }
                    $childProduct->importArray(['attributes' => $data['attributes'],'inventory'=>$common_inventory]);
                }

                $childProduct->save();
            }
        }
    }

    public static function afterProductSave(\common\models\Products $product)
    {
        if ( empty($product->parent_products_id) && $product->sub_product_children_count>0 ) {
            static::copyParentAttributesToChildren($product->products_id);
        }
    }

}