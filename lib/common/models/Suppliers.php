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
use yii\behaviors\TimestampBehavior;

class Suppliers extends ActiveRecord
{
    /**
     * set table name
     * @return string
     */
    public static function tableName()
    {
        return 'suppliers';
    }
    
    public function rules() {
        return [
            [['suppliers_name', 'condition', 'condition_description', 'company','company_vat', 'send_email', 'document_format', 'contact_name', 'contact_phone', 'awrs_no', 'sage_code', 'street_address', 'suburb', 'city', 'postcode', 'state', 'country'], 'string',],
            [['suppliers_name', 'condition', 'condition_description', 'company', 'company_vat', 'contact_name', 'contact_phone', 'awrs_no', 'sage_code', 'street_address', 'suburb', 'city', 'postcode', 'state'], 'default', 'value' => '', 'on' => ['insert', 'update']], // not null fields
            [['country'], 'default', 'value' => 'GB', 'on' => ['insert', 'update']], // not null fields
            [[ 'status', 'currencies_id', 'delivery_days_min', 'delivery_days_max', 'tax_class_id', 'supplier_prices_with_tax', 'tax_rate', 'reorder_auto', 'send_qty', 'send_amount', 'warehouse_id', 'payment_delay', 'supply_delay', 'invoice_needed_to_complete_po'], 'number'],
            [['is_default', 'tax_rate', 'send_amount', 'payment_delay', 'supply_delay', ], 'default', 'value' => 0, 'skipOnEmpty' => false],
            [['status', 'send_qty', 'invoice_needed_to_complete_po'], 'default', 'value' => 1, 'skipOnEmpty' => false]
            //['price_formula', 'default'],
        ];
    }
    
    public function load($data, $formName = null) {
        if (!isset($data['status'])){
            $this->status = 0;
        }
        if (isset($data['delivery_days_min']) && $data['delivery_days_min']==''){
            $data['delivery_days_min'] = null;
        }
        if (isset($data['delivery_days_max']) && $data['delivery_days_max']==''){
            $data['delivery_days_max'] = null;
        }
        return parent::load($data, $formName);
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
    
    public function getSupplierProducts(){
        return $this->hasMany(SuppliersProducts::className(), ['suppliers_id' => 'suppliers_id']);
    }
    
    public function getGrouppedSupplierProducts(){
        return $this->hasMany(SuppliersProducts::className(), ['suppliers_id' => 'suppliers_id'])->groupBy(['products_id']);
    }
    
    public function getGrouppedSupplierProductsCount(){
        return $this->getGrouppedSupplierProducts()->count();
    }
    
    public function getSupplierProductsOptions(){
        return $this->hasMany(SuppliersProductsOptions::className(), ['suppliers_id' => 'suppliers_id']);
    }
    
    public function getSupplierProductsOptionsValues(){
        return $this->hasMany(SuppliersProductsOptionsValues::className(), ['suppliers_id' => 'suppliers_id']);
    }


    public function getSupplierDiscounts()
    {
        return $this->hasMany(SuppliersCatalogDiscount::className(),['suppliers_id' => 'suppliers_id'])->where(['category_id'=>0, 'manufacturer_id'=>0]);
    }

    public function getSupplierPriceRules()
    {
        return $this->hasMany(SuppliersCatalogPriceRules::className(),['suppliers_id' => 'suppliers_id'])->where(['category_id'=>0, 'manufacturer_id'=>0]);
    }
    
    public function getAuthData(){
        return $this->hasOne(SuppliersAuthData::className(), ['suppliers_id' => 'suppliers_id']);
    }

    public function getSupplierCurrencies(){
        return $this->hasMany(SuppliersCurrencies::className(), ['suppliers_id' => 'suppliers_id'])->joinWith('currencies');
    }

    public function getAllowedCurrencies(){
        return $this->getSupplierCurrencies()->where([SuppliersCurrencies::tableName().'.status' => 1 ]);
    }

    public function beforeDelete() {
        
        if ($this->supplierProducts){
            foreach($this->supplierProducts as $sProduct) $sProduct->delete();
        }
        if ($this->supplierProductsOptions){
            foreach($this->supplierProductsOptions as $sO) $sO->delete();
        }
        
        if ($this->supplierProductsOptionsValues){
            foreach($this->supplierProductsOptionsValues as $sOV) $sOV->delete();
        }
        
        $_auth = $this->getAuthData()->one();
        if ($_auth) $_auth->delete();
        
        //what to do with warehouse link??
        
        return parent::beforeDelete();
    }
    
    public function saveSupplier($params){

        if ($this->isAttributeChanged('is_default') && $this->is_default){
            $dSupplier = self::findOne(['is_default' => 1]);
            if ($dSupplier){
                $dSupplier->is_default = 0;
                $dSupplier->save();
            }
        }
        if ($this->is_default) $this->status = 1;
        if (!$this->currencies_id) $this->currencies_id = \common\helpers\Currencies::getCurrencyId(DEFAULT_CURRENCY);

        /*
        $partFormulaChanged = $this->isAttributeChanged('suppliers_surcharge_amount') || $this->isAttributeChanged('suppliers_margin_percentage');
        $formulaChanged = $this->isAttributeChanged('price_formula');
        
        $old_margin = $old_surcharge = 0;
        if ($partFormulaChanged){
            $old_margin = $this->getOldAttribute('suppliers_margin_percentage');
            $old_surcharge = $this->getOldAttribute('suppliers_surcharge_amount');
        }
        */

        if ($this->save(false)){
            $this->saveAuthData($params);
            $this->saveCurrencies([\common\helpers\Currencies::getCurrencyId(DEFAULT_CURRENCY) => ['status' => 1, 'use_default' => 1]]);
            /*if ($partFormulaChanged){
                $this->updateProductsMarginAmount($old_margin, $old_surcharge);
            }*/

            $supplierRules = new \backend\models\SuppliersRules();
            $supplierRules->saveSupplierData($this, $params);

            /*if ($formulaChanged || $partFormulaChanged){
                $this->recalculateProductsPrice();
            }*/
            return true;
        }
        return false;
    }
    
    public function saveAuthData($params){
        $auth = $this->getAuthData()->one();
        if (!$auth){
            $auth = new SuppliersAuthData();
        }
        $auth->setAttribute('email_address', $params['email_address'] ?? null);
        $auth->setAttribute('password', $params['password'] ?? null);
        $this->link('authData', $auth);
    }
    
    public function saveCurrencies($curList = array()){
        if (is_array($curList)){
            foreach ($curList as $currencies_id => $cur){
                $sc = SuppliersCurrencies::create($this->suppliers_id, $currencies_id);
                $sc->prepareData($cur);
                $sc->save();
            }
        }
    }

    public function clearCurrencies(){
        SuppliersCurrencies::deleteAll(['suppliers_id' => $this->suppliers_id]);
    }


    public function updateProductsMarginAmount($old_margin, $old_surcharge){
/*        tep_db_query(
                "UPDATE " . TABLE_SUPPLIERS_PRODUCTS . " " .
                " SET last_modified=IF((suppliers_surcharge_amount='" . tep_db_input($old_surcharge) . "' OR suppliers_margin_percentage='" . tep_db_input($old_margin )."'),NOW(),last_modified), ".
                " suppliers_surcharge_amount=IF(suppliers_surcharge_amount='" . tep_db_input($old_surcharge) . "', '".tep_db_input($this->suppliers_surcharge_amount)."', suppliers_surcharge_amount), " .
                " suppliers_margin_percentage=IF(suppliers_margin_percentage='" . tep_db_input($old_margin) . "', '".tep_db_input($this->suppliers_margin_percentage)."', suppliers_margin_percentage) " .
                "WHERE suppliers_id='" . (int)$this->suppliers_id . "' "
            );*/
    }
    
    public function recalculateProductsPrice(){
        $sProducts = $this->getSupplierProducts()->asArray()->all();
        if ( $sProducts ) {
            foreach($sProducts as $sProduct){
                \common\helpers\PriceFormula::applyDb($sProduct['products_id']);
            }
        }
    }

    public function getsuppliers_surcharge_amount()
    {
        return 0.0;
    }

    public function getsuppliers_margin_percentage()
    {
        return 0.0;
    }

}