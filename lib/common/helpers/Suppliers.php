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

use common\models\Products;
use common\models\ProductsPrices;
use common\models\Suppliers as sModel;
use common\models\SuppliersProducts as spModel;
use yii\db\Expression;

class Suppliers {

    public static function getSuppliersCount($include_inactive = false) {
        $model = sModel::find();
        if (!$include_inactive){
            $model->where(['status' => 1]);
        }
        return $model->count();
    }
    
    public static function getDefaultSupplier(){
        return sModel::findOne(['is_default' => 1]);
    }
    
    public static function getDefaultSupplierId(){
        static $_defId = null;
        if ( is_null($_defId) ) {
            $_defId = 0;
            $supplier = sModel::findOne(['is_default' => 1]);
            if ($supplier) {
                $_defId = $supplier->suppliers_id;
            }
        }
        return $_defId;
    }

    public static function orderedIds()
    {
        static $ids;
        if ( !is_array($ids) ) {
            $ids = [];
            foreach (\common\models\Suppliers::find()
                         ->select('suppliers_id')
                         ->orderBy('is_default DESC, sort_order, suppliers_name')
                         ->asArray()
                         ->all() as $_tmp) {
                $ids[] = $_tmp['suppliers_id'];
            }
        }
        return $ids;
    }

    public static function orderedIdsForProduct($products_id)
    {
        return \common\models\SuppliersProducts::find()->alias('sp')
            ->select('sp.suppliers_id')
            ->joinWith('supplier s')
            ->where(['sp.products_id' => (int)$products_id])
            ->orderBy(new \yii\db\Expression('if(sp.sort_order is null, s.sort_order, sp.sort_order)'))
            ->column();
    }

    public static function orderedActiveIds()
    {
        static $ids;
        if ( !is_array($ids) ) {
            $ids = [];
            foreach (\common\models\Suppliers::find()
                         ->select('suppliers_id')
                          ->where(['status'=>1])
                         ->orderBy('is_default DESC, sort_order, suppliers_name')
                         ->asArray()
                         ->all() as $_tmp) {
                $ids[] = (int)$_tmp['suppliers_id'];
            }
        }
        return $ids;
    }

    /*get all active suppliers*/
    public static function getSuppliers($asArray = false){
        return sModel::find()->where(['status' => 1])->orderBy('is_default DESC, sort_order, suppliers_name')->asArray($asArray)->all();
    }
        
    /*get supplier product with related supplier */
    public static function getSuppliersToUprid($uprid){
        if (strpos($uprid, '{') !== false){
            $sModels = spModel::getSupplierUpridProducts($uprid)->all();
        } else {
            $sModels = spModel::getSupplierProducts($uprid)->all();
        }
        return $sModels;
    }
    
    /*get suppliers list for dropdown*/
    public static function getSuppliersList($uprid = null, $asArray = false){
        if (is_null($uprid)){
            return \yii\helpers\ArrayHelper::map(self::getSuppliers($asArray), 'suppliers_id', 'suppliers_name');
        } else {
            $list = [];
            $sp = self::getSuppliersToUprid($uprid);
            if ($sp){
                foreach($sp as $_sp){
                    $list[$_sp->suppliers_id] = $_sp->supplier->suppliers_name; 
                }
            }
            return $list;
        }
    }

    public static function getSupplierName($SupplierId) {
        $suppliersName = '';
        $supplier = sModel::findOne(['suppliers_id' => $SupplierId]);
        if ($supplier) {
            $suppliersName = $supplier->suppliers_name;
        }
        return $suppliersName;
    }

    public static function getSupplierIdByName($SupplierName) {
        $ret = null;
        $supplier = sModel::find()->where("suppliers_name like :name",[':name' => $SupplierName]);
        if (!is_null($supplier) && ($supplier->count() == 1)) {
            $ret = $supplier->one()->suppliers_id;
        }
        return $ret;
    }
    
    public static function removeUprids($products_id){
        $_uprids = spModel::find()->where(['products_id' => (int)$products_id])
                ->all();
        foreach($_uprids as $eProduct ){
            if (strval($eProduct->uprid) != strval($eProduct->products_id))
                $eProduct->delete();
            }
    }

    public static function onUpdatePriceModeSwitch($newValue)
    {
        if ($newValue=='Auto'){
            // recalculate price
        }
    }

    public static function updateProductPrice($product)
    {
        $productQuery = ProductsPrices::find()
            ->where(['products_id'=>(int)$product,'groups_id'=>0]);
        if ( SUPPLIER_UPDATE_PRICE_MODE=='Auto' ) {
            $productQuery->andWhere(['OR', ['IS', 'supplier_price_manual', new Expression('NULL')], ['supplier_price_manual'=>0]]);
        }else{
            $productQuery->andWhere(['OR', ['IS NOT', 'supplier_price_manual', new Expression('NULL')], ['supplier_price_manual'=>0]]);
        }
        if ( defined('USE_MARKET_PRICES') && USE_MARKET_PRICES=='True' ) {
            $productQuery->andWhere(['currencies_id'=>\common\helpers\Currencies::getCurrencyId(\common\helpers\Currencies::systemCurrencyCode())]);
        }else{
            $productQuery->andWhere(['currencies_id'=>0]);
        }
        //echo $productQuery->createCommand()->getRawSql();
        $productQuery->all();

    }

    public static function getDefaultProductPrice($productId, $qty = 0)
    {
      static $cache = [];
      $key = $productId . '_' . $qty;
      $price = 0;
      if (!isset($cache[$key])) {
        $q = spModel::find()->andWhere([
          'products_id' => \common\helpers\Inventory::get_prid($productId),
          'uprid' => $productId,
          'is_default' => 1,
        ]);
        $sp = $q->asArray()->one();
        if ($sp) {
          //?? round?? $currencies = Yii::$container->get('currencies');
          //?? $currencies->format_clear($totalItemCountryPrice, true, $products['currency'], $products['currency_value']);
          //?? \common\helpers\Tax::add_tax_always($products['final_price'], $products['products_tax'])
          $price = $sp['suppliers_price'];
        }
        $cache[$key] = $price;
      }
      return $cache[$key];
    }

    public static function getDiscountValuesArray($suppliers_price_discount)
    {
        $suppliers_qty_discounts = [];
        foreach (explode(';', $suppliers_price_discount) as $qty_discount) {
            if (!empty($qty_discount)) {
                $arr = explode(':', $qty_discount);
                $qty = $arr[0] ?? null;
                $price = $arr[1] ?? null;
                if ($qty > 0 && $price > 0) {
                    $suppliers_qty_discounts[] = ['qty' => $qty, 'price' => $price];
                }
            }
        }
        return $suppliers_qty_discounts;
    }

    public static function getDiscountValuesTable($suppliers_price_discount_post)
    {
        $suppliers_price_discount = '';
        if (is_array($suppliers_price_discount_post) && ($suppliers_price_discount_post['status'] ?? false)) {
            $suppliers_price_discount_array = array();
            $discount_qty = $suppliers_price_discount_post['qty'];
            $discount_price = $suppliers_price_discount_post['price'];
            if (is_array($discount_qty) && is_array($discount_price)) {
                foreach ($discount_qty as $key => $val) {
                    if ($discount_qty[$key] > 0 && $discount_price[$key] > 0) {
                        $suppliers_price_discount_array[$discount_qty[$key]] = $discount_price[$key];
                    }
                }
            }
            ksort($suppliers_price_discount_array, SORT_NUMERIC);
            foreach ($suppliers_price_discount_array as $qty => $price) {
              $suppliers_price_discount .= $qty . ':' . $price . ';';
            }
        }
        return $suppliers_price_discount;
    }
}
