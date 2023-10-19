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

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;

class SuppliersProducts extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'suppliers_products';
    }

    public function rules() {
        return [
[['suppliers_product_name', 'suppliers_upc', 'suppliers_asin', 'suppliers_isbn','source'], 'default', 'value' => '', 'on' => ['insert', 'update']], // not null fields
            [['sort_order'], 'integer'],
            [['products_id', 'uprid', 'suppliers_id', 'suppliers_model', 'suppliers_price', 'supplier_discount', 'suppliers_surcharge_amount', 'suppliers_margin_percentage',
              'source', 'suppliers_price_discount',
              'suppliers_quantity', 'suppliers_product_name', 'suppliers_upc', 'currencies_id', 'status', 'suppliers_asin', 'suppliers_isbn', 'suppliers_ean', 'notes'] , 'safe'],
            [['suppliers_price', 'suppliers_quantity'], 'default', 'value' => 0],
            [['supplier_discount', 'suppliers_surcharge_amount', 'suppliers_margin_percentage', 'landed_price'], 'default', 'value' => null],
            [['emergency_stock', 'stock_reorder_level_on', 'stock_reorder_level', 'stock_reorder_quantity_on', 'stock_reorder_quantity'], 'default', 'value' => 0],
            [['suppliers_product_name', 'source', 'suppliers_price_discount'], 'default', 'value' => '']
        ];
    }

    public function load($data, $formName = null) {
        if ((is_null($formName) || $formName=='') && !isset($data['status'])){
            $this->status = 0;
        } elseif (!is_null($formName) && $formName!='' && !isset($data[$formName]['status'])) {
            $this->status = 0;
        }
        return parent::load($data, $formName);
    }


    public static function primaryKey() {
        return ['products_id', 'uprid', 'suppliers_id'];
    }

    public static function getDefaultFields(){
        return [/*'suppliers_surcharge_amount', 'suppliers_margin_percentage',*/ 'suppliers_id', 'currencies_id'];
    }

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added', 'last_modified'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['last_modified'],
                ],
                 'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    /**
     * one-to-one
     * @return object
     */
    public function getInventory()
    {
        return $this->hasOne(Inventory::className(), ['products_id' => 'uprid']);
    }

    public function getSupplier(){
        return $this->hasOne(Suppliers::className(), ['suppliers_id' => 'suppliers_id']);
    }

    public function getProduct(){
        return $this->hasOne(Products::className(), ['products_id' => 'products_id']);
    }

    public static function getSupplierProductsQuery($suppliers_id = null){
        $query = self::find()->joinWith('supplier s')->orderBy('suppliers_id');
        /*if ($suppliers_id){
            $query->andWhere(['suppliers_id' => (int)$suppliers_id, 'status' => 1]);
        }*/
        return $query;
    }

    public static function getSupplierProducts($products_id, $suppliers_id = null){
        $_query = self::getSupplierProductsQuery($suppliers_id)->where(['products_id' => (int)$products_id])->andWhere("length(uprid) = length(products_id)");
        if ($suppliers_id){
            $_query->andWhere([self::tableName().'.suppliers_id' => $suppliers_id]);
        }
        return $_query->orderBy('s.is_default DESC, s.sort_order, suppliers_name');
    }

    public static function getSupplierUpridProducts($uprid, $suppliers_id = null){
        $_query = self::getSupplierProductsQuery($suppliers_id)->where(['uprid' => $uprid]);
        if ($suppliers_id){
            $_query->andWhere([self::tableName().'.suppliers_id' => $suppliers_id]);
        }
        return $_query->orderBy('s.is_default DESC, s.sort_order, suppliers_name');
    }

    public function loadSupplierValues($suppliers_id){
        $supplier = Suppliers::findOne(['suppliers_id' => $suppliers_id]);
        if ($supplier){
            foreach(self::getDefaultFields() as $field){
                if ($supplier->hasAttribute($field) && $this->hasAttribute($field)){
                    $this->{$field} = $supplier->getAttribute($field);
                }
            }
            if (!$this->supplier){
                $this->getSupplier();
            }
        }
    }

    public function saveDefaultSupplierProduct($params){
        if ( $this->isNewRecord ) $this->loadDefaultValues();
        $dSupplier = Suppliers::findOne(['is_default' => 1]);
        if ($dSupplier) {
            $params['suppliers_id'] = $dSupplier->suppliers_id;
            if (!isset($params['status'])) $params['status'] = 1;
            $this->loadSupplierValues($dSupplier->suppliers_id);
            if ($this->saveSupplierProduct($params)){
                return $this;
            }
        }
        return false;
    }

/***
 * delete current record if any other active supplier product exists (do not delete last active supplier product)
 * @return integer|bool false if nothing is deleted or quantity of deleted records.
 */
    public function deleteSupplierProduct() {

      $ret = false;

      if ($this->products_id && $this->uprid && $this->suppliers_id) {

        if ($this->uprid != (string)$this->products_id ){
          //suppose inventory
          $product = \common\helpers\Inventory::getRecord($this->uprid);
        } else {
          // suppose product
          $product = \common\models\Products::findOne(['products_id' => (int)$this->products_id]);
        }
        if ($product) {
          if ($product->getActiveSuppliersProducts($this->suppliers_id)->count()) {
            $ret = $this->delete();
          }
        }

      }
      return $ret;
    }


    /*products_id, uprid, suppliers_id main keys*/
    public function saveSupplierProduct($params, $isEP = false){

        if ($this->isNewRecord){
            $this->loadDefaultValues();
            $this->products_id = (int)$params['products_id'];
            $this->suppliers_id = (int)$params['suppliers_id'];
            if (empty($params['uprid']) || !isset($params['uprid'])){
                $this->uprid = (int)$this->products_id;
            } else {
                $this->uprid = $params['uprid'];
            }
        }

        if ($this->products_id && $this->uprid && $this->suppliers_id){

            $this->load($params, '') && $this->validate();
            $this->is_default = 0; //what to todo

            // check for any active
            if ($this->status == 0 ) {
              if ($this->uprid != (string)$this->products_id ){
                //suppose inventory
                $product = \common\helpers\Inventory::getRecord($this->uprid);
              } else {
                // suppose product
                $product = \common\models\Products::findOne(['products_id' => (int)$this->products_id]);
              }
              if ($product) {
                if ($product->getActiveSuppliersProducts($this->suppliers_id)->count() == 0) {
                  $this->status = 1;
                }
              } else {
                $this->status = 1;
              }
            }

            if ((int)$isEP <= 0) {
                $params['supplier_discount'] = trim(isset($params['supplier_discount']) ? $params['supplier_discount'] : '');
                $params['suppliers_surcharge_amount'] = trim(isset($params['suppliers_surcharge_amount']) ? $params['suppliers_surcharge_amount'] : '');
                $params['suppliers_margin_percentage'] = trim(isset($params['suppliers_margin_percentage']) ? $params['suppliers_margin_percentage'] : '');
                $params['tax_rate'] = trim(isset($params['tax_rate']) ? $params['tax_rate'] : '');
                $params['price_with_tax'] = (isset($params['price_with_tax']) ? 1 : 0);
            }

            if (isset($params['supplier_discount'])) {
                $params['supplier_discount'] = trim($params['supplier_discount']);
                $this->supplier_discount = (is_numeric($params['supplier_discount']) ? floatval($params['supplier_discount']) : null);
            }
            if (isset($params['suppliers_surcharge_amount'])) {
                $params['suppliers_surcharge_amount'] = trim($params['suppliers_surcharge_amount']);
                $this->suppliers_surcharge_amount = (is_numeric($params['suppliers_surcharge_amount']) ? floatval($params['suppliers_surcharge_amount']) : null);
            }
            if (isset($params['suppliers_margin_percentage'])) {
                $params['suppliers_margin_percentage'] = trim($params['suppliers_margin_percentage']);
                $this->suppliers_margin_percentage = (is_numeric($params['suppliers_margin_percentage']) ? floatval($params['suppliers_margin_percentage']) : null);
            }
            if (isset($params['tax_rate'])) {
                $params['tax_rate'] = trim($params['tax_rate']);
                $this->tax_rate = (is_numeric($params['tax_rate']) ? floatval($params['tax_rate']) : null);
            }

            if (isset($params['price_with_tax'])) {
                $this->price_with_tax = intval(trim($params['price_with_tax'])); // !!!OMFG!!! $this->price_with_tax = isset($params['price_with_tax'])?1:0;
                $supplierObj = \common\models\Suppliers::findOne($this->suppliers_id);
                if ( $supplierObj ) {
                    if ($supplierObj->supplier_prices_with_tax == $this->price_with_tax){
                        $this->price_with_tax = null;
                    }
                }
            }

            if(isset($params['currencies_id'])) {
                $this->currencies_id = intval($params['currencies_id']);
            }

            if ($this->save(false)){
                return $this;
            }
        }

        return false;
    }

    public static function getSuppliersPrice($uprid, $suppliers_id) {
        $sProduct = self::getSupplierUpridProducts($uprid, $suppliers_id)->asArray()->one();
        if (ArrayHelper::getValue($sProduct, 'currencies_id') > 0) {
            $currencies = Yii::$container->get('currencies');
            if (isset($currencies->currency_codes[$sProduct['currencies_id']])) {
                if ($currencies->currencies[$currencies->currency_codes[$sProduct['currencies_id']]]['value'] > 0) {
                    return ($sProduct['suppliers_price'] / $currencies->currencies[$currencies->currency_codes[$sProduct['currencies_id']]]['value']);
                }
            }
        }
        return $sProduct['suppliers_price'] ?? null;
    }

    public function getPriceWithTax():bool
    {
        return is_null($this->price_with_tax) || $this->price_with_tax > 0;
    }
}
