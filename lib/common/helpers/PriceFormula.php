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

class PriceFormula {

    public static function defaultFormula()
    {
        static $price_formula;
        if ( !is_array($price_formula) ) {
            $price_formula = json_decode('{"text":"((PRICE-DISCOUNT)+MARGIN)+SURCHARGE","formula":[[["()M",["PRICE","-","DISCOUNT"]],"+","SURCHARGE"]]}', true);
        }
        return $price_formula;
    }

    public static function getSupplierFormula($supplierId) {
        static $cached = [];
        if (!isset($cached[intval($supplierId)])) {
            $cached[intval($supplierId)] = static::defaultFormula();
/*            $get_suppliers_data_r = tep_db_query("SELECT price_formula FROM suppliers WHERE suppliers_id='" . (int) $supplierId . "'");
            if (tep_db_num_rows($get_suppliers_data_r) > 0) {
                $suppliers_data = tep_db_fetch_array($get_suppliers_data_r);
                if (!empty($suppliers_data['price_formula'])) {
                    $supplierFormula = json_decode($suppliers_data['price_formula'], true);
                    if (is_array($supplierFormula) && isset($supplierFormula['formula'])) {
                        $cached[intval($supplierId)] = $supplierFormula;
                    }
                }
            }*/
        }
        return $cached[intval($supplierId)];
    }

    protected static function normalizeParams($paramsIn) {
        $params = [];
        if (is_array($paramsIn)) {
            foreach ($paramsIn as $k => $v) {
                $k = strtoupper($k);
                if (in_array($k, ['PRICE', 'MARGIN', 'SURCHARGE', 'DISCOUNT', 'TAX_RATE'])) {
                    $v = (float) $v;
                }
                $params[$k] = $v;
            }
        }

        return $params;
    }

    protected static function replaceParam($formulaArray, $params, $forPhp = true) {

        foreach ($formulaArray as $idx => $value) {
            if (is_array($value)) {
                $formulaArray[$idx] = static::replaceParam($value, $params, $forPhp);
            } elseif ($value == '()M' && isset($params['MARGIN'])) {
                $formulaArray[$idx] = '(' . $params['MARGIN'] . ' + 1) *';
            } elseif (isset($params[$value])) {
                $formulaArray[$idx] = $params[$value];
            } elseif (substr($value, -1) == '%') {
                if (isset($params['PRICE'])) {
                    if ($forPhp) {
                        $formulaArray[$idx] = $params['PRICE'] * substr($value, 0, -1) / 100;
                    } else {
                        $formulaArray[$idx] = $params['PRICE'] . '*' . substr($value, 0, -1) . '/100';
                    }
                } else {
                    $formulaArray[$idx] = 0;
                }
            }
        }

        return $formulaArray;
    }

    public static function arrayToFlatPhp($formulaArray) {
        $implodeParts = [];
        foreach ($formulaArray as $formulaChunk) {
            if (is_array($formulaChunk)) {
                $implodeParts[] = '(' . static::arrayToFlatPhp($formulaChunk) . ')';
            } else {
                $implodeParts[] = $formulaChunk;
            }
        }

        return implode(' ', $implodeParts);
    }

    public static function calculatePhp($formulaArray, $params) {
        $formulaArray = static::replaceParam($formulaArray, $params);

        $evalCode = static::arrayToFlatPhp($formulaArray);

        eval("\$result=$evalCode;");

        if (!isset($result) || !is_numeric($result) || $result < 0) {
            $result = false;
        }

        return $result;
    }

    public static function getJs($formulaArray, $params) {
        $params = static::normalizeParams($params);
        if (isset($formulaArray['formula'])) {
            $formulaArray = $formulaArray['formula'];
        }
        if (!is_array($formulaArray) || count($formulaArray) == 0) {
            return false;
        }
        if (isset($params['DISCOUNT']) && isset($params['PRICE'])) {
            $params['DISCOUNT'] = '(' . $params['DISCOUNT'] . '/100)*' . $params['PRICE'];
        }
        if (isset($params['MARGIN'])) {
            $params['MARGIN'] = '(' . $params['MARGIN'] . '/100)';
        }
        $formulaArray = static::replaceParam($formulaArray, $params, false);

        $evalCode = static::arrayToFlatPhp($formulaArray);
        if (empty($evalCode)) {
            return false;
        }
        return $evalCode;
    }

    public static function getProductEditJs($params) {
        $params = static::normalizeParams($params);

        $js = '';

        foreach (\common\models\Suppliers::find()->all() as $supplier) {
            $price_formula = json_decode($supplier->price_formula,true);
            if ( !is_array($price_formula) ) {
                $price_formula = static::defaultFormula();
            }

            $priceRules = $supplier->getSupplierPriceRules()->orderBy(['supplier_price_from'=>SORT_ASC])->all();
            if ( count($priceRules)>0 ) {
                $rulesJs = '';
                foreach ($priceRules as $priceRule) {
                    if ( empty($priceRule->rule_condition) ) {
                        if (!is_null($priceRule->supplier_discount)) {
                            //$params['DISCOUNT'] = $priceRule->supplier_discount;
                        }
                        if (!is_null($priceRule->surcharge_amount)) {
                            //$params['SURCHARGE'] = $priceRule->surcharge_amount;
                        }
                        if (!is_null($priceRule->margin_percentage)) {
                            //$params['MARGIN'] = $priceRule->margin_percentage;
                        }
                        if (!empty($priceRule->price_formula)) {
                            $price_formula = json_decode($priceRule->price_formula,true);
                        }
                        $rulesJs = static::getJs($price_formula, $params);
                    }else{
                        $rule_condition = ',' . $priceRule->rule_condition . ',';
                        if (strpos($rule_condition, ',fromTo,') !== false) {
                            if (!is_null($priceRule->supplier_discount)) {
                                //$params['DISCOUNT'] = $priceRule->supplier_discount;
                            }
                            if (!is_null($priceRule->surcharge_amount)) {
                                //$params['SURCHARGE'] = $priceRule->surcharge_amount;
                            }
                            if (!is_null($priceRule->margin_percentage)) {
                                //$params['MARGIN'] = $priceRule->margin_percentage;
                            }
                            if (!empty($priceRule->price_formula)) {
                                $price_formula = json_decode($priceRule->price_formula,true);
                            }

                            $supplierFormula = static::getJs($price_formula, $params);
                            if ( !empty($rulesJs) ) $rulesJs .= 'else ';

                            $lowLimit = '';
                            $topLimit = '';
                            if ( !is_null($priceRule->supplier_price_from) ) {
                                $lowLimit = $params['PRICE'].'>='.number_format(floatval($priceRule->supplier_price_from),2,'.','');
                            }
                            if ( !is_null($priceRule->supplier_price_to) ) {
                                $topLimit = $params['PRICE'].'<='.number_format(floatval($priceRule->supplier_price_to),2,'.','');
                            }
                            if ( !empty($lowLimit) && !empty($topLimit) ) {
                                $rulesJs .= 'if ('.$lowLimit.' && '. $topLimit.'){ return ' . $supplierFormula . '; }';
                            }elseif ( !empty($lowLimit) && empty($topLimit) ) {
                                $rulesJs .= 'if ('.$lowLimit.'){ return ' . $supplierFormula . '; }';
                            }elseif ( empty($lowLimit) && !empty($topLimit) ) {
                                $rulesJs .= 'if ('.$topLimit.'){ return ' . $supplierFormula . '; }';
                            }
                        }
                    }
                }
                $supplierFormula = $rulesJs;
            }else{
                $supplierFormula = static::getJs($price_formula, $params);
            }

            if (empty($supplierFormula))
                continue;
            if (!empty($js))
                $js .= "else ";
            $js .= "if (id=={$supplier->suppliers_id}){\n";
            $js .= " calcNetPrice = (function(){ ". (strpos($supplierFormula,'if')===0?'':'return ') . $supplierFormula . "; })();\n";
            $js .= "}";
        }

        return $js;
    }

    public static function apply($formulaArray, $params) {
        $params = static::normalizeParams($params);
        if (isset($formulaArray['formula'])) {
            $formulaArray = $formulaArray['formula'];
        }
        if (!is_array($formulaArray) || count($formulaArray) == 0) {
            return false;
        }

        if (isset($params['DISCOUNT']) && isset($params['PRICE'])) {
            $params['DISCOUNT'] = (((float)$params['DISCOUNT']) / 100) * ((float)$params['PRICE']);
        }
        if (isset($params['MARGIN'])) {
            $params['MARGIN'] = ((float)$params['MARGIN']) / 100;
        }

        $result = static::calculatePhp($formulaArray, $params);

        return $result;
        /*
          PRICE
          DISCOUNT
          SURCHARGE
          MARGIN
          %
          +
          -
         *
          /
          ()
         */
    }

    public static function calculateSupplierProducts($productId)
    {
        $currencies = \Yii::$container->get('currencies');
        $perSupplier = [];
        if ( strpos($productId,'{')!==false ) {
            $get_product_info_r = tep_db_query(
                "SELECT sp.suppliers_id, " .
                "  sp.suppliers_quantity, sp.is_default, sp.status, " .
                "  sp.suppliers_price, sp.currencies_id, " .
                "  sp.supplier_discount, sp.suppliers_surcharge_amount, sp.suppliers_margin_percentage, " .
                "  sp.tax_rate, sp.price_with_tax, ".
                "  i.products_id, p.manufacturers_id, " .
                "  GROUP_CONCAT(DISTINCT p2c.categories_id SEPARATOR ',') AS assigned_categories " .
                "FROM " . TABLE_PRODUCTS . " p " .
                "  INNER JOIN " . TABLE_INVENTORY." i ON i.prid=p.products_id ".
                "  INNER JOIN " . TABLE_SUPPLIERS_PRODUCTS . " sp ON sp.products_id=p.products_id AND sp.uprid=i.products_id AND sp.suppliers_price>0 " .
                "  LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p.products_id=p2c.products_id " .
                "WHERE p.products_id='" . (int)$productId . "' AND i.products_id='" . tep_db_input($productId) . "' " .
                "GROUP BY i.products_id, sp.suppliers_id " .
                "ORDER BY IF(sp.suppliers_quantity>0,0,1), IF(sp.is_default=1,0,1) "
            );
        }else {
            $get_product_info_r = tep_db_query(
                "SELECT sp.suppliers_id, " .
                "  sp.suppliers_quantity, sp.is_default, sp.status, " .
                "  sp.suppliers_price, sp.currencies_id, " .
                "  sp.supplier_discount, sp.suppliers_surcharge_amount, sp.suppliers_margin_percentage, " .
                "  sp.tax_rate, sp.price_with_tax, ".
                "  p.products_id, p.manufacturers_id, " .
                "  GROUP_CONCAT(DISTINCT p2c.categories_id SEPARATOR ',') AS assigned_categories " .
                "FROM " . TABLE_PRODUCTS . " p " .
                "  INNER JOIN " . TABLE_SUPPLIERS_PRODUCTS . " sp ON sp.products_id=p.products_id AND sp.uprid=CONCAT('',p.products_id) AND sp.suppliers_price>=0 AND status=1" .
                "  LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p.products_id=p2c.products_id " .
                "  LEFT JOIN " . TABLE_SUPPLIERS . " s ON s.suppliers_id=sp.suppliers_id " .
                "WHERE p.products_id='" . (int)$productId . "' " .
                "GROUP BY p.products_id, sp.suppliers_id " .
                "ORDER BY IF(sp.sort_order IS NULL,s.sort_order,sp.sort_order)"
//                "ORDER BY IF(sp.suppliers_quantity>0,0,1), IF(sp.is_default=1,0,1) "
            );
        }
        if ( tep_db_num_rows($get_product_info_r)>0 ) {
            while($product_data = tep_db_fetch_array($get_product_info_r)){
                $params = [
                    'products_id' => $product_data['products_id'],
                    'categories_id' => preg_split('/,/',$product_data['assigned_categories'],-1,PREG_SPLIT_NO_EMPTY),
                    'manufacturers_id' => $product_data['manufacturers_id'],
                    'currencies_id' => $product_data['currencies_id'],
                    'PRICE' => $product_data['suppliers_price'],// * $currencies->get_market_price_rate(\common\helpers\Currencies::getCurrencyCode($product_data['currencies_id']), \common\helpers\Currencies::systemCurrencyCode()),
                    'DISCOUNT' => $product_data['supplier_discount'],
                    'SURCHARGE' => $product_data['suppliers_surcharge_amount'],
                    'MARGIN' => $product_data['suppliers_margin_percentage'],
                    'tax_rate' => $product_data['tax_rate'],
                    'price_with_tax' => $product_data['price_with_tax'],
                    'product' => [
                        'suppliers_id' => $product_data['suppliers_id'],
                        'qty' => $product_data['suppliers_quantity'],
                        'status' => $product_data['status'],
                        'is_default' => $product_data['is_default'],
                    ]
                ];

                $appliedRules = self::applyRules($params, $product_data['suppliers_id']);
                $perSupplier[$product_data['suppliers_id']] = $appliedRules;
            }
        }

        return $perSupplier;
    }

    private static function autoSelectSupplier($productId)
    {
        if ( !defined('SUPPLIER_PRICE_SELECTION') || SUPPLIER_PRICE_SELECTION=='Disabled' ) return;

        $product_price = false;
        $selected_supplier_id = false;

        $calculatedPrices = self::calculateSupplierProducts($productId);
        if ( SUPPLIER_PRICE_SELECTION=='Cheapest, In stock' || SUPPLIER_PRICE_SELECTION=='Supplier order' ) {
            // filter in stock with price
            $in_stock_sort = [];
            foreach ($calculatedPrices as $_supplierId=>$calculatedPrice){
                if (!is_array($calculatedPrice['product']) /*|| $calculatedPrice['product']['qty']<1*/) continue;
                if ( $calculatedPrice['resultPrice']>0 ) {
                    $in_stock_sort[$_supplierId] = (float)$calculatedPrice['resultPrice'];
                }
            }

            if ( count($in_stock_sort)>0 ) {
                if ( SUPPLIER_PRICE_SELECTION=='Supplier order' ) {
                    foreach (\common\helpers\Suppliers::orderedIdsForProduct($productId) as $orderedSupplierId){
                        if ( isset($in_stock_sort[$orderedSupplierId]) ) {
                            $selected_supplier_id = (int)$orderedSupplierId;
                            $product_price = $calculatedPrices[$orderedSupplierId]['resultPrice'];
                            break;
                        }
                    }
                }else {
                    asort($in_stock_sort,SORT_NUMERIC);
                    $selected_supplier_id = key($in_stock_sort);
                    $product_price = $calculatedPrices[$selected_supplier_id]['resultPrice'];
                }
            }

        }elseif (SUPPLIER_PRICE_SELECTION=='Based on priority rules') {
            if (count($calculatedPrices)>0) {
                if ( $ext = \common\helpers\Acl::checkExtensionAllowed('SupplierPriority', 'getInstance') ) {
                    $calculatedPrices = $ext::getInstance()->arrangeVariants($calculatedPrices);
                    foreach ($calculatedPrices as $_supplierId => $calculatedPrice) {
                        if ($calculatedPrice['priority'] && $calculatedPrice['priority']['is_preferred']) {
                            $selected_supplier_id = $_supplierId;
                            $product_price = $calculatedPrice['resultPrice'];
                            break;
                        }
                    }
                } else {
                    self::logAutoUpdateProduct($productId, "Error: extension SupplierPriority is needed to use '" . SUPPLIER_PRICE_SELECTION . "'");
                }
            }
        }

        return ['product_price' => $product_price, 'selected_supplier_id' => $selected_supplier_id ?? null, 'calculatedPrice' => $calculatedPrice ?? null];
    }

    private static function addAutoUpdateWhere($productQuery)
    {
        if (SUPPLIER_UPDATE_PRICE_MODE == 'Auto') {
            $productQuery->andWhere(['OR', ['IS', 'supplier_price_manual', new \yii\db\Expression('NULL')], ['supplier_price_manual' => 0]]);
        } else {
            $productQuery->andWhere(['supplier_price_manual' => 0]);
        }
        return $productQuery;
    }

    /**
     * Checks if product ready for auto update
     * @param $productId
     * @return \common\models\Products|\yii\db\ActiveRecord|null
     */
    public static function getProductModelForAutoUpdate($productId)
    {
        $productQuery = \common\models\Products::find()
            ->select(['products_price', 'products_id', 'supplier_price_manual', 'products_price_full'])
            ->where(['products_id' => (int)$productId, 'products_id_price' => [(int)$productId, 0]]);
        return self::addAutoUpdateWhere($productQuery)->one();
    }

    private static function logAutoUpdate($msg, $echoForConsole = true)
    {
        \Yii::info($msg,'suppliers/auto-update-price');
        if (\common\helpers\System::isConsole() && $echoForConsole) {
            echo $msg . "\n";
        }
    }

    private static function logAutoUpdateProduct($productId, $msg)
    {
        self::logAutoUpdate("Autoupdate price for product #{$productId}: #{$msg}", false);
    }

    public static function applyDb($productId, $checkAutoField = true)
    {
        if ($checkAutoField) {
            $productModel = self::getProductModelForAutoUpdate($productId);
            if (empty($productModel)) {
                self::logAutoUpdateProduct($productId, 'Canceled - product does not meet auto the update conditions');
                return;
            }
        } else {
            $productModel = \common\models\Products::findOne($productId);
            if (empty($productModel)) {
                self::logAutoUpdateProduct($productId, 'Product does not exists');
                return;
            }
        }

        extract( self::autoSelectSupplier($productId) );
        if ( $product_price === false ) {
            self::logAutoUpdateProduct($productId, 'Canceled - supplier price is empty' );
            return;
        }

        $log_string = "result_price={$product_price}; SupplierId={$selected_supplier_id}; config [".SUPPLIER_PRICE_SELECTION."]; ";
        if ( is_array($calculatedPrice) ){
            $log_string .= "Applied {$calculatedPrice['label']} ".\json_encode($calculatedPrice['applyParams']);
            $log_string .= " DATA=".\json_encode($calculatedPrice);
        }
        self::logAutoUpdateProduct($productId, $log_string );

        return self::updateProductPriceByModel($productModel, $product_price, $selected_supplier_id);
    }

    public static function batchProductAutoCalcPriceBySupplier($LIMIT_RECORDS = 1000, $LIMIT_TIME = 3000)
    {
        self::logAutoUpdate('Batch update started for ' . (int)$LIMIT_RECORDS . ' products' );
        $productQuery = \common\models\Products::find()->alias('p')
            ->select('products_id')
            ->where("auto_price_modified IS NULL OR auto_price_modified < COALESCE(last_xml_import, '1000-01-01 00:00:00') OR auto_price_modified < COALESCE(products_last_modified, '1000-01-01 00:00:00')")
            ->limit($LIMIT_RECORDS);
        $startTime = microtime(true);
        $count = $updated = 0;
        foreach(self::addAutoUpdateWhere($productQuery)->column() as $pid) {
            $updated += self::applyDb($pid, false) ? 1 : 0;
            $count++;
            if ((microtime(true) - $startTime) > $LIMIT_TIME) {
                self::logAutoUpdate('Batch update is interrupted due time limit');
                break;
            }
        }
        $elapsedTime = microtime(true) - $startTime;
        self::logAutoUpdate("Batch update finished. Products reviewed: $count updated: $updated. Elapsed time: $elapsedTime");
    }

    public static function getSupplierRulesCollection($supplierId)
    {
        $supplier = \common\models\Suppliers::findOne($supplierId);

        $rules = [
            'category' => [],
            'brand' => [],
            'supplier' => [],
        ];
        $allRules = \common\models\SuppliersCatalogPriceRules::find()->where(['suppliers_id'=>$supplier->suppliers_id])->orderBy(['currencies_id'=>SORT_DESC]);

        foreach ( $allRules->all() as $rule){
            $formula = is_null($rule->price_formula)?false:json_decode($rule->price_formula,true);
            if ( !is_array($formula) ) {
                $formula = static::defaultFormula();
            }
            $ruleArray = [
                'category_id' => $rule->category_id,
                'manufacturer_id' => $rule->manufacturer_id,
                'currencies_id' => $rule->currencies_id,
                'rule_condition' => $rule->rule_condition,
                'cost_from' => $rule->supplier_price_from,
                'cost_to' => $rule->supplier_price_to,
                'result_price_not_below' => $rule->supplier_price_not_below,
                'DISCOUNT' => is_null($rule->supplier_discount)?0.00:$rule->supplier_discount,
                'SURCHARGE' => is_null($rule->surcharge_amount)?0.00:$rule->surcharge_amount,
                'MARGIN' => is_null($rule->margin_percentage)?0.00:$rule->margin_percentage,
                'tax_rate' => $supplier->tax_rate,
                'price_with_tax' => $supplier->supplier_prices_with_tax,
                'formula' => $formula,
            ];
            if (!empty($rule->category_id)) {
                if ( !is_array($rules['category'][$rule->category_id] ?? null) ) $rules['category'][$rule->category_id] = [];
                $ruleArray['appliedToCategories'] = [];

                $subcategoriesQuery = \common\models\Categories::find()
                    ->select([\common\models\Categories::tableName().'.categories_id',\common\models\Categories::tableName().'.categories_level'])
                    ->innerJoin(\common\models\Categories::tableName().' cc','cc.categories_id=:catId AND '.\common\models\Categories::tableName().'.categories_left>=cc.categories_left AND '.\common\models\Categories::tableName().'.categories_right<=cc.categories_right',['catId'=>$rule->category_id])
                    ->orderBy([\common\models\Categories::tableName().'.categories_left'=>SORT_ASC]);

                foreach ($subcategoriesQuery->all() as $cat){
                    $ruleArray['appliedToCategories'][(int)$cat['categories_id']] = (int)$cat['categories_level'];
                }
                $ruleArray['label'] = 'Category "'. \common\helpers\Categories::output_generated_category_path($rule->category_id) .'" rule';
                $rules['category'][$rule->category_id][] = $ruleArray;
            }elseif(!empty($rule->manufacturer_id)){
                if ( !is_array($rules['brand'][$rule->manufacturer_id]) ) $rules['brand'][$rule->manufacturer_id] = [];
                $rules['brand'][$rule->manufacturer_id][] = $ruleArray;
            }else{
                $ruleArray['label'] = 'Supplier rule';
                $rules['supplier'][] = $ruleArray;
            }
        }
        if ( count($rules['supplier'])==0 ) {
            $formula = static::defaultFormula();
            $rules['supplier'][] = [
                'currencies_id' => 0, // any currency for default formula
                'label' => 'Default supplier rule',
                'DISCOUNT' => 0.00,
                'SURCHARGE' => 0.00,
                'MARGIN' => 0.00,
                'tax_rate' => $supplier->tax_rate,
                'price_with_tax' => $supplier->supplier_prices_with_tax,
                'formula' => $formula,
            ];
        }

        return $rules;
    }

    public static function correctSupplierValueByCurrencyRisks($suppliers_id, $currencyId, $value){
        $sCurrency = \common\models\SuppliersCurrencies::find()->alias('s')->where(['suppliers_id' => $suppliers_id, 's.currencies_id' => $currencyId])
            ->joinWith('currencies c')->one();
        if ($sCurrency) {
            if ($sCurrency['use_custom_currency_value']){
                $value /= $sCurrency['currency_value'];
            } else {
                $value /= $sCurrency->currencies->value;
            }
            if ($sCurrency['margin_value']){
                if ($sCurrency['margin_type'] == '%'){
                    $value += ($sCurrency['margin_value'] / 100) * $value;
                } else {
                    $value += $sCurrency['margin_value'];
                }
            }
        }
        return $value;
    }

    protected static function correctSupplierPriceByCurrencyRisks($suppliers_id, $data) {
        return self::correctSupplierValueByCurrencyRisks($suppliers_id, $data['currencies_id'], $data['PRICE']);
    }

    protected static function addTaxRate($amount, $taxRate=0)
    {
        return round($amount*( (100+$taxRate)/100 ),6);
    }

    protected static function applySupplierRule( $priceRule, $data, $onlySupplierId){
        $resultCost = false;

        if (isset($priceRule['currencies_id']) && $priceRule['currencies_id']!=0 && $priceRule['currencies_id']!=$data['currencies_id']) return false;

        $params = [
            'PRICE' => static::correctSupplierPriceByCurrencyRisks($onlySupplierId, $data),
            'MARGIN' => isset($data['MARGIN'])?$data['MARGIN']:$priceRule['MARGIN'],
            'SURCHARGE' => isset($data['SURCHARGE'])?$data['SURCHARGE']:$priceRule['SURCHARGE'],
            'DISCOUNT' => isset($data['DISCOUNT'])?$data['DISCOUNT']:$priceRule['DISCOUNT'],
            'tax_rate' => isset($data['tax_rate'])?$data['tax_rate']:$priceRule['tax_rate'],
            'price_with_tax' => isset($data['price_with_tax'])?$data['price_with_tax']:$priceRule['price_with_tax'],
        ];
        if ( isset($params['tax_rate']) && !$params['price_with_tax'] ) {
            $params['PRICE'] = static::addTaxRate($params['PRICE'], $params['tax_rate']);
        }

        // check restrict
        if ( !empty($priceRule['category_id']) ) {
            // category not match
            $matchedCategoryLevel = -1;
            foreach ( $data['categories_id'] as $checkAssignedId ) {
                if (isset($priceRule['appliedToCategories'][$checkAssignedId])) {
                    $matchedCategoryLevel = max($matchedCategoryLevel,$priceRule['appliedToCategories'][$checkAssignedId]);
                }
            }
            if ( $matchedCategoryLevel==-1 ) return false;
            $priceRule['categoryLevel'] = $matchedCategoryLevel;
        }

// limited rule
        if ( !empty($priceRule['rule_condition']) && strpos(",{$priceRule['rule_condition']},",',fromTo,')!==false ) {
            $passLo = null;
            $passHi = null;
            if ( !is_null($priceRule['cost_from']) ) {
                $passLo = ($params['PRICE'] >= number_format(floatval($priceRule['cost_from']),2,'.',''));
            }
            if ( !is_null($priceRule['cost_to']) ) {
                $passHi = ($params['PRICE'] <= number_format(floatval($priceRule['cost_to']),2,'.',''));
            }

            if ( !is_null($passLo) && is_null($passHi) ) {
                // only low limit
                if ( !$passLo ) return false;
            }elseif ( is_null($passLo) && !is_null($passHi) ) {
                // only high limit
                if ( !$passHi ) return false;
            }else{
                if ( $passLo!==true && $passHi!==true ) {
                    return false;
                }
            }
        }

        $resultCost = \common\helpers\PriceFormula::apply($priceRule['formula'],$params);
        if ( $resultCost!==false && !empty($priceRule['rule_condition']) && strpos(",{$priceRule['rule_condition']},",',notBelow,')!==false ) {
            // result price must be greater then not_below
            if ($resultCost< ($priceRule['result_price_not_below']??0)) {
                $resultCost = false;
            }
        }

        if ( $resultCost!==false ) {
            $priceRule['resultPrice'] = $resultCost;
            $priceRule['applyParams'] = $params;
            if ( isset($data['product']) ) {
                $priceRule['product'] = $data['product'];
            }
            return $priceRule;
        }

        return false;
    }

    /**
     * @param $data
     * $apply = [
        'products_id' => 0,
        'categories_id' => [276],
        'manufacturers_id' => 0,
        'currencies_id' => 15,
        'PRICE' => 100.01,
        'MARGIN' => null,
        'SURCHARGE' => 5,
        ];
     * @param null $onlySupplierId
     */
    public static function applyRules($data, $onlySupplierId)
    {

        $supplierRules = \common\helpers\PriceFormula::getSupplierRulesCollection($onlySupplierId);

        $rulesPriority = ['Category', 'Brand', 'Supplier'];
        if ( defined('SUPPLIER_PRICE_RULE_PRIORITY') && SUPPLIER_PRICE_RULE_PRIORITY!='' ) {
            $rulesPriority = explode(',', SUPPLIER_PRICE_RULE_PRIORITY);
        }

        $applyResult = false;
        foreach ($rulesPriority as $ruleProcess) {
            if ( $ruleProcess=='Category' ) {
                $appliedCategoriesGroup = [];
                foreach ($supplierRules['category'] as $categoryRules) {
                    foreach ($categoryRules as $categoryRule) {
                        $applyResult = static::applySupplierRule($categoryRule, $data, $onlySupplierId);
                        if ($applyResult !== false) {
                            $appliedCategoriesGroup[] = $applyResult;
                        }
                    }
                }
                if ($applyResult !== false && count($appliedCategoriesGroup) > 1) {
                    foreach ($appliedCategoriesGroup as $appliedInGroupItem) {
                        if ($appliedInGroupItem['categoryLevel'] > $applyResult['categoryLevel']) {
                            $applyResult = $appliedInGroupItem;
                        } elseif ($appliedInGroupItem['categoryLevel'] == $applyResult['categoryLevel'] && $appliedInGroupItem['resultPrice'] > $applyResult['resultPrice']) {
                            $applyResult = $appliedInGroupItem;
                        }
                    }
                }
            }elseif ($ruleProcess=='Brand') {
                if (!empty($data['manufacturers_id']) && isset($supplierRules['brand'][$data['manufacturers_id']])) {
                    $brandRules = $supplierRules['brand'][$data['manufacturers_id']];
                    //foreach ($supplierRules['brand'] as $brandId => $brandRules) {
                    foreach ($brandRules as $brandRule) {
                        $applyResult = static::applySupplierRule($brandRule, $data, $onlySupplierId);
                        if (is_array($applyResult)) break;
                    }
                    //}
                }
            }elseif ($ruleProcess=='Supplier') {
                foreach ($supplierRules['supplier'] as $supplierRule) {
                    $applyResult = static::applySupplierRule($supplierRule, $data, $onlySupplierId);
                    if (is_array($applyResult)) break;
                }
            }
            if ($applyResult !== false) break;
        }

        return $applyResult;
    }
    
    public static function isValidFormula($formula){
        $formula = json_decode($formula, true);
        $valid = true;
        if (is_array($formula) && is_array($formula['formula'])){
            foreach($formula['formula'] as $item){
                $valid = is_array($item) && count($item) && $valid;
            }
        }
        return $valid;
    }
    
    public static function calculateExtraOrderPrice(){
        $args = func_get_args();
        if ((isset($args[0])  ) && isset($args[1])){// 0=> fields, 1=> obtained data
            $field = $args[0];
            if (is_array($args[1])){
                $params = $args[1];
                if ( isset($params['action'])){
                    switch ($params['action']){
                        case 'extra_charge':
                            $response = str_replace(
                                array_map(function ($i){return '{'.$i.'}';}, array_keys($params['vars'])),
                                array_values($params['vars']),
                                $params['formula']
                            );
                            $response = str_replace('--', '+', $response);
                            
                            try{
                                eval("\$result=$response;");
                            } catch (\Exception $ex) {
                                
                            }
                            if (is_scalar($result)){
                                return $result;
                            } else {
                                return $params['vars']['init_value'];
                            }
                            break;
                    }
                }
            }
        }
    }

    /**
     * @param \common\models\Products $productModel
     * @param $product_price
     * @param $supplierId don't used at this moment
     * @return true
     * @throws \yii\db\Exception
     */
    public static function updateProductPriceByModel($productModel, $product_price, $supplierId = null): bool
    {
        $productId = $productModel->products_id;

        $currencyId = (int)((USE_MARKET_PRICES == 'True' ? \common\helpers\currencies::getCurrencyId(DEFAULT_CURRENCY) : 0));

        if (strpos($productId, '{') !== false) {
            $_main_price = $productModel->getAttributes(['products_price', 'products_price_full']);

            //inventory_group_price
            //inventory_full_price
            $update_inventory = "price_prefix = '+', inventory_full_price='" . tep_db_input($product_price) . "'";
            $update_inventory_prices = "price_prefix = '+', inventory_full_price='" . tep_db_input($product_price) . "'";
            if (!$_main_price['products_price_full']) {
                $_prefix = $product_price < $_main_price['products_price'] ? '-' : '+';
                $_price_delta = abs($_main_price['products_price'] - $product_price);
                $update_inventory = "price_prefix = '{$_prefix}', inventory_full_price='" . tep_db_input($_price_delta) . "'";
                $update_inventory_prices = "price_prefix = '{$_prefix}', inventory_full_price='" . tep_db_input($_price_delta) . "'";
            }

            \Yii::$app->db->createCommand(
                "UPDATE " . TABLE_INVENTORY_PRICES . " " .
                "SET {$update_inventory_prices} " .
                "WHERE prid='" . (int)$productId . "' AND products_id='" . tep_db_input($productId) . "' " .
                " AND groups_id='0' AND currencies_id='" . $currencyId .
                " AND products_group_price!=-1 "
            )->execute();
            //inventory_full_price
            //inventory_price
            \Yii::$app->db->createCommand(
                "UPDATE " . TABLE_INVENTORY . " " .
                "SET {$update_inventory} " .
                "WHERE prid='" . (int)$productId . "' AND products_id='" . tep_db_input($productId) . "'"
            )->execute();
        } else {
            \common\models\ProductsPrices::updateAll(['products_group_price' => $product_price],
                "products_id='" . (int)$productId . "' AND groups_id=0 AND currencies_id='" . $currencyId . "' AND products_group_price!=-1"
            );
            $productModel->products_price = $product_price;
            $productModel->auto_price_modified = (new \DateTime())->format(\DateTime::ATOM);
            $productModel->save(false);

            /** @var $extP \common\extensions\ProductPriceIndex\ProductPriceIndex */
            if ($extP = \common\helpers\Extensions::isAllowed('ProductPriceIndex')) {
                $extP::reindex((int)$productId);
            }
        }
        self::logAutoUpdateProduct($productId, "Price changed: price={$product_price}; supplierId={$supplierId}");
        return true;
    }

    public static function updateProductPriceById($productId, $productPrice, $supplierId = null): bool
    {
        if ($productId > 0) {
            $productModel = \common\models\Products::findOne($productId);
            if (!empty($productModel)) {
                return self::updateProductPriceByModel($productModel, $productPrice, $supplierId);
            }
        }
        return false;
    }
}
