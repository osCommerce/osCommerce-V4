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

namespace backend\models;


use common\helpers\PriceFormula;
use common\models\Categories;
use common\models\Manufacturers;
use common\models\Suppliers;

class SuppliersRules
{

    public function viewObjectForSupplier()
    {
        //....
    }

    protected function fillSupplierData($discountCollection, $rulesCollection, $skipRoFill=false)
    {
        $supplier_data = [];
        foreach($discountCollection as $supplierDiscount ){
            if ( !is_array($supplier_data[$supplierDiscount->suppliers_id]) ) $supplier_data[$supplierDiscount->suppliers_id] = [];
            $supplier_data[$supplierDiscount->suppliers_id]['discount_table'][] = $supplierDiscount->getAttributes();
        }
        foreach($rulesCollection as $supplierPriceRule ){
            if ( !is_array($supplier_data[$supplierPriceRule->suppliers_id] ?? null) ) $supplier_data[$supplierPriceRule->suppliers_id] = [];
            $rule_data = $supplierPriceRule->getAttributes();
            $arrPriceFormula = [];
            if ( !empty($rule_data['price_formula']) ) {
                $arrPriceFormula = json_decode($rule_data['price_formula'],true);
                if ( is_array($arrPriceFormula) && isset($arrPriceFormula['formula']) ) {

                }else{
                    $arrPriceFormula = PriceFormula::getSupplierFormula($supplierPriceRule->suppliers_id);
                }
            }else{
                $arrPriceFormula = PriceFormula::getSupplierFormula($supplierPriceRule->suppliers_id);
            }
            $rule_data['price_formula'] = json_encode($arrPriceFormula);
            $rule_data['price_formula_text'] = $arrPriceFormula['text'];
            $supplier_data[$supplierPriceRule->suppliers_id]['price_rule'][] = $rule_data;
            $supplier_data[$supplierPriceRule->suppliers_id]['rule_condition'] = $supplierPriceRule->rule_condition;
        }
        if ( count($supplier_data)>0 ) {
            $supplierInfos = \common\models\Suppliers::find()->where(['suppliers_id'=>array_keys($supplier_data)])->all();
            foreach( $supplierInfos as $supplierInfo ) {
                $supplier_data[$supplierInfo->suppliers_id]['info'] = $supplierInfo->getAttributes();
                if ( !isset($supplier_data[$supplierInfo->suppliers_id]['discount_table']) ) $supplier_data[$supplierInfo->suppliers_id]['discount_table'] = [];
                if ( !isset($supplier_data[$supplierInfo->suppliers_id]['rule_condition']) ) $supplier_data[$supplierInfo->suppliers_id]['rule_condition'] = '';
                if ( !isset($supplier_data[$supplierInfo->suppliers_id]['price_rule']) ) $supplier_data[$supplierInfo->suppliers_id]['price_rule'] = [];
                $supplier_data[$supplierInfo->suppliers_id]['currenciesVariants'] = $this->fillCurrenciesVariants($supplierInfo);
                $formula = PriceFormula::getSupplierFormula($supplierInfo->suppliers_id);
                $supplier_data[$supplierInfo->suppliers_id]['default_rule'] = [
                    'price_formula_text'=> $formula['text'],
                    'price_formula' => json_encode($formula),
                    'supplier_discount' => '0.00',
                    'surcharge_amount' => '0.00',
                    'margin_percentage' => '0.00',
                ];
            }
        }

        if ( !$skipRoFill ) {
            $supplierInfos = \common\models\Suppliers::find()->all();
            foreach ($supplierInfos as $supplierInfo) {
                if (isset($supplier_data[$supplierInfo->suppliers_id])) continue;
                $supplier_data[$supplierInfo->suppliers_id] = [
                    'info' => $supplierInfo->getAttributes(),
                    'supplierRO' => 1,
                    'discountRO' => 1,
                    'rulesRO' => 1,
                    'discount_table' => [],
                    'rule_condition' => '',
                    'price_rule' => [],
                    'currenciesVariants' => $this->fillCurrenciesVariants($supplierInfo)
                ];

                foreach ($supplierInfo->getSupplierDiscounts()->orderBy(['suppliers_id' => SORT_ASC, 'quantity_from' => SORT_ASC])->all() as $supplierDiscount) {
                    $supplier_data[$supplierDiscount->suppliers_id]['discount_table'][] = $supplierDiscount->getAttributes();
                }

                $currencies = \Yii::$container->get('currencies');

                foreach ($supplierInfo->getSupplierPriceRules()->orderBy(['suppliers_id' => SORT_ASC, 'currencies_id'=>SORT_DESC,])->all() as $supplierPriceRule) {
                    $rule_data = $supplierPriceRule->getAttributes();
                    $arrPriceFormula = [];
                    if (!empty($rule_data['price_formula'])) {
                        $arrPriceFormula = json_decode($rule_data['price_formula'], true);
                        if (is_array($arrPriceFormula) && isset($arrPriceFormula['formula'])) {

                        } else {
                            $arrPriceFormula = PriceFormula::getSupplierFormula($supplierPriceRule->suppliers_id);
                        }
                    } else {
                        $arrPriceFormula = PriceFormula::getSupplierFormula($supplierPriceRule->suppliers_id);
                    }
                    $rule_data['price_formula'] = json_encode($arrPriceFormula);
                    $rule_data['price_formula_text'] = $arrPriceFormula['text'];
                    if (is_numeric($rule_data['supplier_price_from'])){
                        $rule_data['supplier_price_from'] = $currencies->format($rule_data['supplier_price_from'], true, \common\helpers\Currencies::getCurrencyCode($rule_data['currencies_id']));
                    }
                    if (is_numeric($rule_data['supplier_price_to'])){
                        $rule_data['supplier_price_to'] = $currencies->format($rule_data['supplier_price_to'], true, \common\helpers\Currencies::getCurrencyCode($rule_data['currencies_id']));
                    }
                    if (is_numeric($rule_data['supplier_price_not_below'])){
                        $rule_data['supplier_price_not_below'] = $currencies->format($rule_data['supplier_price_not_below'], true, \common\helpers\Currencies::getCurrencyCode($rule_data['currencies_id']));
                    }
                    if (is_numeric($rule_data['surcharge_amount'])){
                        $rule_data['surcharge_amount'] = $currencies->format($rule_data['surcharge_amount'], true, \common\helpers\Currencies::getCurrencyCode($rule_data['currencies_id']));
                    }
                    $supplier_data[$supplierPriceRule->suppliers_id]['price_rule'][] = $rule_data;
                    $supplier_data[$supplierPriceRule->suppliers_id]['rule_condition'] = $supplierPriceRule->rule_condition;
                }


                $formula = PriceFormula::getSupplierFormula($supplierInfo->suppliers_id);
                $supplier_data[$supplierInfo->suppliers_id]['default_rule'] = [
                    'currencies_id' => $supplierInfo->currencies_id,
                    'price_formula_text' => $formula['text'],
                    'price_formula' => json_encode($formula),
                    'supplier_discount' => '0.00',
                    'surcharge_amount' => '0.00',
                    'surcharge_amount_formatted' => $currencies->format(0.0, true, \common\helpers\Currencies::getCurrencyCode($supplierInfo->currencies_id)),
                    'margin_percentage' => '0.00',
                ];
                if ( count($supplier_data[$supplierInfo->suppliers_id]['price_rule'])==0 ){
                    $supplier_data[$supplierInfo->suppliers_id]['price_rule'][] = $supplier_data[$supplierInfo->suppliers_id]['default_rule'];
                }
            }
        }
        if ( count($supplier_data)>1 ) {
            $_supplier_data = [];
            foreach (\common\helpers\Suppliers::orderedIds() as $orderedId){
                if ( isset($supplier_data[$orderedId]) ) {
                    $_supplier_data[$orderedId] = $supplier_data[$orderedId];
                }
            }
            $supplier_data = $_supplier_data;
        }

        return $supplier_data;
    }

    protected function fillCurrenciesVariants($parentObj = null)
    {
        $currencies = [
            0 => defined('TEXT_ANY')?TEXT_ANY:'Any',
        ];
        if ($parentObj instanceof Suppliers){
            foreach($parentObj->getAllowedCurrencies()->asArray()->all() as $_currency){
                $currencies[$_currency['currencies_id']] = $_currency['currencies']['code'];
            }
        } else {
            foreach (\common\helpers\Currencies::get_currencies() as $_currency){
                if ( $_currency['currencies_id'] ) {
                    $currencies[$_currency['currencies_id']] = $_currency['code'];
                }
            }
        }
        return $currencies;
    }

    public function getCategoryData(Categories $parentObj, $viewObject)
    {

        $supplier_data = [];
        if ( $parentObj->categories_id ) {
            $supplier_data = $this->fillSupplierData(
                $parentObj->getSupplierDiscounts()->orderBy(['suppliers_id'=>SORT_ASC, 'quantity_from'=>SORT_ASC])->all(),
                $parentObj->getSupplierPriceRules()->orderBy(['suppliers_id'=>SORT_ASC, 'currencies_id'=>SORT_DESC,])->all()
            );
        }
        $viewObject->supplier_data = $supplier_data;
        //$viewObject->supplierCurrenciesVariants = $this->fillCurrenciesVariants();
    }

    public function saveCategoryData(Categories $categoryObj, $data)
    {
        $categories_id = $categoryObj->categories_id;

        \common\models\SuppliersCatalogPriceRules::deleteAll(['category_id'=>$categories_id]);
        \common\models\SuppliersCatalogDiscount::deleteAll(['category_id'=>$categories_id]);
        foreach ($data as $supplierId=>$_data){
            if ( isset($_data['price_rule']) && is_array($_data['price_rule']) ) {
                foreach ($_data['price_rule'] as $rule){
                    $__model = new \common\models\SuppliersCatalogPriceRules();
                    $__model->setAttributes($rule, false);
                    $__model->suppliers_id = $supplierId;
                    $__model->category_id = $categories_id;
                    $__model->rule_condition = isset($_data['rule_condition'])?$_data['rule_condition']:'';
                    if ( !empty($__model->price_formula) ) {
                        $defFormula = json_encode(PriceFormula::getSupplierFormula($supplierId));
                        if ( $defFormula==$__model->price_formula ) {
                            $__model->setAttribute('price_formula',null);
                        }
                    }
                    if ( is_null($__model->price_formula) && is_null($__model->surcharge_amount) && is_null($__model->margin_percentage) && is_null($__model->supplier_discount) ) {
                        continue;
                    }
                    $__model->save();
                    //if ( empty($_data['rule_condition']) ) break;
                }
            }
            if (isset($_data['has_discount_table']) && $_data['has_discount_table'] && isset($_data['discount_table']) && is_array($_data['discount_table'])){
                foreach ($_data['discount_table'] as $table){
                    $__model = new \common\models\SuppliersCatalogDiscount();
                    if ( strlen($table['quantity_from'])==0 ) unset($table['quantity_from']);
                    if ( strlen($table['quantity_to'])==0 ) unset($table['quantity_to']);
                    if (!isset($table['quantity_from']) && !isset($table['quantity_to'])) continue;
                    $__model->setAttributes($table, false);
                    $__model->suppliers_id = $supplierId;
                    $__model->category_id = $categories_id;
                    $__model->save();
                }
            }
        }
    }

    public function getManufacturerData(Manufacturers $parentObj, $viewObject)
    {

        $supplier_data = [];
        if ( $parentObj->manufacturers_id ) {
            $supplier_data = $this->fillSupplierData(
                $parentObj->getSupplierDiscounts()->orderBy(['suppliers_id'=>SORT_ASC, 'quantity_from'=>SORT_ASC])->all(),
                $parentObj->getSupplierPriceRules()->orderBy(['suppliers_id'=>SORT_ASC, 'currencies_id'=>SORT_DESC,])->all()
            );
        }
        $viewObject->supplier_data = $supplier_data;
        //$viewObject->supplierCurrenciesVariants = $this->fillCurrenciesVariants();
    }

    public function saveManufacturersData(Manufacturers $parentObj, $data)
    {
        $manufacturers_id = $parentObj->manufacturers_id;

        \common\models\SuppliersCatalogPriceRules::deleteAll(['manufacturer_id'=>$manufacturers_id]);
        \common\models\SuppliersCatalogDiscount::deleteAll(['manufacturer_id'=>$manufacturers_id]);

        foreach ($data as $supplierId=>$_data){
            if ( isset($_data['price_rule']) && is_array($_data['price_rule']) ) {
                foreach ($_data['price_rule'] as $rule){
                    $__model = new \common\models\SuppliersCatalogPriceRules();
                    $__model->setAttributes($rule, false);
                    $__model->suppliers_id = $supplierId;
                    $__model->manufacturer_id = $manufacturers_id;
                    $__model->rule_condition = isset($_data['rule_condition'])?$_data['rule_condition']:'';
                    if ( !empty($__model->price_formula) ) {
                        $defFormula = json_encode(PriceFormula::getSupplierFormula($supplierId));
                        if ( $defFormula==$__model->price_formula ) {
                            $__model->setAttribute('price_formula',null);
                        }
                    }
                    if ( is_null($__model->price_formula) && is_null($__model->surcharge_amount) && is_null($__model->margin_percentage) && is_null($__model->supplier_discount) ) {
                        continue;
                    }
                    $__model->save();
                    //if ( empty($_data['rule_condition']) ) break;
                }
            }
            if (isset($_data['has_discount_table']) && $_data['has_discount_table'] && isset($_data['discount_table']) && is_array($_data['discount_table'])){
                foreach ($_data['discount_table'] as $table){
                    if ( strlen($table['quantity_from'])==0 ) unset($table['quantity_from']);
                    if ( strlen($table['quantity_to'])==0 ) unset($table['quantity_to']);
                    if (!isset($table['quantity_from']) && !isset($table['quantity_to'])) continue;
                    $__model = new \common\models\SuppliersCatalogDiscount();
                    $__model->setAttributes($table, false);
                    $__model->suppliers_id = $supplierId;
                    $__model->manufacturer_id = $manufacturers_id;
                    $__model->save();
                }
            }
        }

    }

    public function getSuppliersData(Suppliers $parentObj, $viewObject)
    {
        $supplier_data = [];
        if ( $parentObj->suppliers_id ) {
            $supplier_data = $this->fillSupplierData(
                $parentObj->getSupplierDiscounts()->orderBy(['suppliers_id'=>SORT_ASC, 'quantity_from'=>SORT_ASC])->all(),
                $parentObj->getSupplierPriceRules()->orderBy(['suppliers_id'=>SORT_ASC, 'currencies_id'=>SORT_DESC, ])->all(),
                true
            );
            if ( empty($supplier_data) ) {
                $defaultFormula = PriceFormula::defaultFormula();
                $supplier_data[$parentObj->suppliers_id] = [
                    'info' => $parentObj->getAttributes(),
                    'price_rule' => [
                        [
                            'currencies_id' => 0,
                            'price_formula_text' => $defaultFormula['text'],
                            'price_formula' => json_encode($defaultFormula),
                            'supplier_discount' => '0.00',
                            'surcharge_amount' => '0.00',
                            'margin_percentage' => '0.00',
                        ]
                    ],
                    'discount_table' => [],
                    'default_rule' => [
                        'currencies_id' => 0,
                        'price_formula_text' => $defaultFormula['text'],
                        'price_formula' => json_encode($defaultFormula),
                        'supplier_discount' => '0.00',
                        'surcharge_amount' => '0.00',
                        'margin_percentage' => '0.00',
                    ],
                    'currenciesVariants' => $this->fillCurrenciesVariants($parentObj)
                ];
            }
        }
        $viewObject->supplier_data = $supplier_data;

        //$viewObject->supplierCurrenciesVariants = $this->fillCurrenciesVariants();
    }

    public function saveSupplierData(Suppliers $parentObj, $data)
    {
        $suppliers_id = $parentObj->suppliers_id;

        \common\models\SuppliersCatalogPriceRules::deleteAll(['suppliers_id'=>$suppliers_id,'category_id'=>0,'manufacturer_id'=>0]);
        \common\models\SuppliersCatalogDiscount::deleteAll(['suppliers_id'=>$suppliers_id,'category_id'=>0,'manufacturer_id'=>0]);

        if (isset($data[$suppliers_id])) {
            $supplierId = $suppliers_id;
            $_data = $data[$suppliers_id];

            if ( isset($_data['price_rule']) && is_array($_data['price_rule']) ) {
                foreach ($_data['price_rule'] as $rule){
                    $__model = new \common\models\SuppliersCatalogPriceRules();
                    $__model->setAttributes($rule, false);
                    $__model->suppliers_id = $supplierId;
                    $__model->manufacturer_id = 0;
                    $__model->category_id = 0;
                    $__model->rule_condition = isset($_data['rule_condition'])?$_data['rule_condition']:'';
                    if ( !empty($__model->price_formula) ) {
                        $defFormula = json_encode(PriceFormula::getSupplierFormula($supplierId));
                        if ( $defFormula==$__model->price_formula ) {
                            $__model->setAttribute('price_formula',null);
                        }
                    }
                    if ( is_null($__model->price_formula) && is_null($__model->surcharge_amount) && is_null($__model->margin_percentage) && is_null($__model->supplier_discount) ) {
                        continue;
                    }
                    $__model->save();
                    //if ( empty($_data['rule_condition']) ) break; // currencies
                }
            }
            if (isset($_data['has_discount_table']) && $_data['has_discount_table'] && isset($_data['discount_table']) && is_array($_data['discount_table'])){
                foreach ($_data['discount_table'] as $table){
                    if ( strlen($table['quantity_from'])==0 ) unset($table['quantity_from']);
                    if ( strlen($table['quantity_to'])==0 ) unset($table['quantity_to']);
                    if (!isset($table['quantity_from']) && !isset($table['quantity_to'])) continue;
                    $__model = new \common\models\SuppliersCatalogDiscount();
                    $__model->setAttributes($table, false);
                    $__model->suppliers_id = $supplierId;
                    $__model->category_id = 0;
                    $__model->manufacturer_id = 0;
                    $__model->save();
                }
            }

            static::applyRules(['suppliers_id'=>$supplierId]);
        }

    }

    public static function applyRules($filter)
    {
        $getRules = \common\models\SuppliersCatalogPriceRules::find()->where($filter)->orderBy(['supplier_price_from'=>SORT_ASC])->all();
//        echo '<pre>'; var_dump($getRules); echo '</pre>';
//        die;
    }


}