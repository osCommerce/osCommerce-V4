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

namespace common\api\models\AR\Products;


use backend\models\EP\Tools;
use common\api\models\AR\EPMap;
use common\api\models\AR\Products;
use common\api\models\AR\Products\Inventory\Prices as Inventory_Prices;
use common\api\models\AR\Products\WarehousesProducts;
use yii\db\Expression;

class Inventory extends EPMap
{

    protected $hideFields = [
        'inventory_id',
        //'products_id',
        //'prid',
    ];

    protected $childCollections = [
        'prices' => [],
        'warehouses_products' => [],
    ];

    protected $indexedCollections = [
        'warehouses_products' => 'common\api\models\AR\Products\WarehousesProducts',
    ];

    protected $optionValuesList = [

    ];

    /**
     * @var Products
     */
    protected $parentObject;

    protected $updateProductStock = false;

    public function __construct(array $config = [])
    {
        $marketPresent = defined('USE_MARKET_PRICES') && USE_MARKET_PRICES=='True';
        $groupsPresent = \common\helpers\Extensions::isCustomerGroupsAllowed();
        if ( !$marketPresent && !$groupsPresent ) {
            unset($this->childCollections['prices']);
        }

        $this->afterSaveHooks['Product::doCache'] = 'reCalculateStockProduct';

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return TABLE_INVENTORY;
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['inventory_id'];
    }

    public function fillOptionValueList()
    {
        $this->optionValuesList = $optValMatch = [];
        if ( preg_match_all('/{(\d+)}(\d+)/', $this->products_id, $optValMatch) ){
            foreach( $optValMatch[1] as $_idx=>$optId ) {
                $valId = $optValMatch[2][$_idx];
                $int_key = $optId.'-'.$valId;
                $this->optionValuesList[$int_key] = [
                    'options_id' => $optId,
                    'options_values_id' => $valId,
                ];
            }
        }
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->fillOptionValueList();
    }


    public function exportArray(array $fields = [])
    {
        $tools = new \backend\models\EP\Tools();
        $export = parent::exportArray($fields);
        if ( array_key_exists('stock_delivery_terms_id', $export) || in_array('stock_delivery_terms_text',$fields) ){
            $export['stock_delivery_terms_text'] = $tools->getStockDeliveryTerms($this->stock_delivery_terms_id);
        }
        if ( array_key_exists('stock_indication_id', $export) || in_array('stock_indication_text',$fields) ){
            $export['stock_indication_text'] = $tools->getStockIndication($this->stock_indication_id);
        }
        $export['attribute_map'] = array_values($this->optionValuesList);
        foreach ($export['attribute_map'] as $idx=>$optionValue) {
            $export['attribute_map'][$idx]['options_name'] = $tools->get_option_name($optionValue['options_id'], \common\classes\language::defaultId() );
            $export['attribute_map'][$idx]['options_values_name'] = $tools->get_option_value_name($optionValue['options_values_id'], \common\classes\language::defaultId() );
        }

        return $export;
    }

    public function importArray($data)
    {
        $validAttributes = false;
        if ( is_object($this->parentObject) ) {
            $validAttributes = $this->parentObject->getAssignedAttributeIds();
        }

        $tools = new \backend\models\EP\Tools();
        if ( array_key_exists('stock_delivery_terms_text', $data) ){
            $data['stock_delivery_terms_id'] = $tools->lookupStockDeliveryTermId($data['stock_delivery_terms_text']);
        }
        if ( array_key_exists('stock_indication_text', $data) ){
            $data['stock_indication_id'] = $tools->lookupStockIndicationId($data['stock_indication_text']);
        }
        if (isset($data['attribute_map']) && is_array($data['attribute_map']) ){
            foreach( $data['attribute_map'] as $idx=>$attrInfo ) {
                $data['attribute_map'][$idx]['options_id'] = $tools->get_option_by_name($attrInfo['options_name']);
                $data['attribute_map'][$idx]['options_values_id'] = $tools->get_option_value_by_name($data['attribute_map'][$idx]['options_id'], $attrInfo['options_values_name']);
            }
            $this->optionValuesList = [];
            foreach( $data['attribute_map'] as $idx=>$attrInfo ) {
                if ( is_array($validAttributes) && !isset($validAttributes[$attrInfo['options_id']]) ) return false;
                if ( is_array($validAttributes) && !in_array($attrInfo['options_values_id'],$validAttributes[$attrInfo['options_id']]) ) return false;

                $int_key = $attrInfo['options_id'].'-'.$attrInfo['options_values_id'];
                $this->optionValuesList[$int_key] = $attrInfo;
            }

            $this->regenerateFields(true);
        }elseif (preg_match_all('/{(\d+)}(\d+)/',$data['products_id'], $_import_attr)){
            $data['attribute_map'] = [];
            $this->optionValuesList = [];
            foreach ($_import_attr[1] as $__idx=>$_optId){
                $_valId = $_import_attr[2][$__idx];

                if ( is_array($validAttributes) && !isset($validAttributes[$_optId]) ) return false;
                if ( is_array($validAttributes) && !in_array($_valId,$validAttributes[$_optId]) ) return false;

                $int_key = $_optId.'-'.$_valId;
                $attrInfo = [
                    'options_id' => $_optId,
                    'options_values_id' => $_valId,
                ];
                $this->optionValuesList[$int_key] = $attrInfo;
                $data['attribute_map'][] = $attrInfo;
            }
            $this->regenerateFields(true);
        }

        if ( strpos((string)$this->products_id,'{')===false ) return false;

        if ( isset($data['warehouses_products']) && is_array($data['warehouses_products']) ) {
            unset($data['products_quantity']);
        }

        $result = parent::importArray($data);

        $this->regenerateFields();

        return $result;
    }

    protected function regenerateFields($onlyUprid=false)
    {
        if (!is_object($this->parentObject)) return;
        $attr = [];
        foreach($this->optionValuesList as $optValInfo){
            $attr[ $optValInfo['options_id'] ] = $optValInfo['options_values_id'];
        }
        ksort($attr);

        $this->products_id = \common\helpers\Inventory::normalize_id(\common\helpers\Inventory::get_uprid($this->parentObject->products_id, $attr));
        if ( !$onlyUprid ) {
            $tools = new Tools();
            $this->products_name = \common\helpers\Product::get_products_name($this->parentObject->products_id, \common\classes\language::defaultId());
            foreach ( $attr as $value_id ) {
                $this->products_name .= ' '.$tools->get_option_value_name($value_id, \common\classes\language::defaultId());
            }
        }
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->prid = $parentObject->products_id;

        $this->parentObject = $parentObject;
        $this->regenerateFields();
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {

        $matchedAttrKeys = array_intersect(array_keys($this->optionValuesList),array_keys($importedObject->optionValuesList));
        $objectMatch = count($matchedAttrKeys)==count($this->optionValuesList);

        if ( $objectMatch ) {
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function initCollectionByLookupKey_Prices($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        if (true) {
            if ( !is_null($this->inventory_id) ) {
                $dbMapCollect = [];
                foreach(Inventory_Prices::findAll(['inventory_id' => $this->inventory_id]) as $obj){
                    $keyCode = $obj->currencies_id.'_'.$obj->groups_id;
                    $dbMapCollect[$keyCode] = $obj;
                }
                foreach(Inventory_Prices::getAllKeyCodes() as $keyCode=>$lookupPK){
                    if( $loadAll || in_array($keyCode,$lookupKeys) ) {
                        $dbKeyCode = $lookupPK['currencies_id'].'_'.$lookupPK['groups_id'];
                        if (isset($dbMapCollect[$dbKeyCode])) {
                            $this->childCollections['prices'][$keyCode] = $dbMapCollect[$dbKeyCode];
                        } else {
                            $lookupPK['inventory_id'] = $this->inventory_id;
                            $this->childCollections['prices'][$keyCode] = new Inventory_Prices($lookupPK);
                        }
                    }
                }
                unset($dbMapCollect);
            }else{
                foreach(Prices::getAllKeyCodes() as $keyCode=>$lookupPK){
                    $this->childCollections['prices'][$keyCode] = new Inventory_Prices($lookupPK);
                }
            }
        }else {
            foreach (Inventory_Prices::getAllKeyCodes() as $keyCode => $lookupPK) {
                $this->childCollections['prices'][$keyCode] = null;
                if (is_null($this->inventory_id)) {
                    $this->childCollections['prices'][$keyCode] = new Inventory_Prices($lookupPK);
                } elseif ($loadAll || in_array($keyCode, $lookupKeys)) {
                    if (!isset($this->childCollections['prices'][$keyCode])) {
                        $lookupPK['inventory_id'] = $this->inventory_id;
                        $this->childCollections['prices'][$keyCode] = Inventory_Prices::findOne($lookupPK);
                        if (!is_object($this->childCollections['prices'][$keyCode])) {
                            $this->childCollections['prices'][$keyCode] = new Inventory_Prices($lookupPK);
                        }
                    }
                }
            }
        }
        return $this->childCollections['prices'];
    }

    public function initCollectionByLookupKey_WarehousesProducts($lookupKeys)
    {
        $loadAll = in_array('*',$lookupKeys);
        if ( false ) {
            if (!is_null($this->products_id)) {
                $dbMapCollect = [];
                foreach (WarehousesProducts::findAll(['products_id' => $this->products_id]) as $obj) {
                    $keyCode = $obj->warehouse_id . '_' . $obj->suppliers_id;
                    $dbMapCollect[$keyCode] = $obj;
                }
                foreach (WarehousesProducts::getAllKeyCodes() as $keyCode => $lookupPK) {
                    $lookupPK['products_id'] = $this->products_id;
                    if ($loadAll || in_array($keyCode, $lookupKeys)) {
                        if (isset($dbMapCollect[$keyCode])) {
                            $this->childCollections['warehouses_products'][$keyCode] = $dbMapCollect[$keyCode];
                        } else {
                            $this->childCollections['warehouses_products'][$keyCode] = new WarehousesProducts($lookupPK);
                        }
                    }
                }
            } else {
                foreach (WarehousesProducts::getAllKeyCodes() as $keyCode => $lookupPK) {
                    $this->childCollections['warehouses_products'][$keyCode] = new WarehousesProducts($lookupPK);
                }
            }
        }else {
            if (!is_null($this->products_id)) {
                $dbMapCollect = [];
                foreach (WarehousesProducts::findAll(['products_id' => strval($this->products_id)]) as $obj) {
                    $keyCode = $obj->warehouse_id . '_' . $obj->suppliers_id;
                    if ( !empty($obj->location_id) ) $keyCode .= '_'.$obj->location_id;
                    $dbMapCollect[$keyCode] = $obj;
                }
                foreach (WarehousesProducts::getAllKeyCodes() as $keyCode => $lookupPK) {
                    if ($loadAll || in_array($keyCode, $lookupKeys)) {
                        if (isset($dbMapCollect[$keyCode])) {
                            $this->childCollections['warehouses_products'][$keyCode] = $dbMapCollect[$keyCode];
                        }
                    }
                }
            }
        }
        return $this->childCollections['warehouses_products'];
    }

    public function beforeSave($insert)
    {
        if ( $insert ) {
            if ( is_null($this->products_name) ) {
                $this->products_name = strval($this->parentObject->getCollectionProductName());
                $tools = Tools::getInstance();
                $attr = [];
                foreach($this->optionValuesList as $optValInfo){
                    $attr[ $optValInfo['options_id'] ] = $optValInfo['options_values_id'];
                }
                ksort($attr);
                foreach ($attr as $value_id) {
                    $this->products_name .= ' ' . $tools->get_option_value_name($value_id, \common\classes\language::defaultId());
                }
            }

            if ( is_null($this->products_model) ) $this->products_model = '';
            if ( is_null($this->products_ean) ) $this->products_ean = '';
            if ( is_null($this->products_asin) ) $this->products_asin = '';
            if ( is_null($this->products_isbn) ) $this->products_isbn = '';
            if ( is_null($this->products_upc) ) $this->products_upc = '';
            if ( is_null($this->non_existent) ) $this->non_existent = 0;
        }
        if ($this->getDirtyAttributes(['products_quantity'])) {
            $this->updateProductStock = true;
            $default_warehouse_id = intval(\common\helpers\Warehouses::get_default_warehouse());
            $defaultWH = $default_warehouse_id . '_' . \common\helpers\Suppliers::getDefaultSupplierId();
            if ( count($this->childCollections['warehouses_products'])==0 ) {
                $this->initCollectionByLookupKey_WarehousesProducts(['*']);
            }
            if ( !isset($this->childCollections['warehouses_products'][$defaultWH]) ) {
                $this->childCollections['warehouses_products'][$defaultWH] = new WarehousesProducts([]);
                $this->childCollections['warehouses_products'][$defaultWH]->warehouse_id = $default_warehouse_id;
                $this->childCollections['warehouses_products'][$defaultWH]->suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
                $this->childCollections['warehouses_products'][$defaultWH]->parentEPMap($this);
            }
            $this->childCollections['warehouses_products'][$defaultWH]->warehouse_stock_quantity = $this->products_quantity;
            $this->reCalculateStockProduct();
            unset($this->products_quantity);
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->updateProductStock){
            $inventory_quantity = tep_db_fetch_array(tep_db_query(
                "SELECT SUM(products_quantity) AS left_quantity " .
                "FROM " . TABLE_INVENTORY . " " .
                "WHERE prid = '" . (int)$this->prid . "' AND IFNULL(non_existent,0)=0 " .
                " AND products_quantity>0"
            ));
            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int) $inventory_quantity['left_quantity'] . "' where products_id = '" . (int)$this->prid . "'");
            \common\helpers\Warehouses::update_sum_of_inventory_quantity((int)$this->prid);

            $this->updateProductStock = false;
        }
    }

    public function reCalculateStockProduct()
    {
        if ( is_object($this->parentObject) ) {
            $this->parentObject->initiateAfterSave('Product::doCache');
        }
    }

}