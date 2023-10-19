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

namespace common\api\Classes;

class Stock extends AbstractClass
{
    public $stockRecordArray = array();

    private static $allowFieldList = array(
        'prid' => true,
        'products_id' => true,
        'products_status' => true,
        'manual_control_status' => true,
        'manual_stock_unlimited' => true,
        'stock_indication_id' => true,
        'stock_delivery_terms_id' => true,
        'stock_reorder_level' => true,
        'stock_reorder_quantity' => true,
        'stock_control' => true,
        'products_id_stock' => true,
        'reorder_auto' => true,
        'without_inventory' => true,
        'attributeRecordArray' => true,
        'warehouseRecordArray' => true
    );

    private function loadInventory($uProductId = '')
    {
        $uProductId = trim(\common\helpers\Inventory::normalize_id_excl_virtual($uProductId));
        if (\common\helpers\Inventory::isInventory($uProductId) == true) {
            $inventoryRecord = \common\helpers\Inventory::getRecord($uProductId);
            if ($inventoryRecord instanceof \common\models\Inventory) {
                if (!isset($this->stockRecordArray[trim($inventoryRecord->products_id)])) {
                    $attributeRecordArray = array();
                    $languageId = \common\classes\language::defaultId();
                    $languageCode = \common\classes\language::get_code($languageId, true);
                    \common\helpers\Inventory::normalizeInventoryId($inventoryRecord->products_id, $attributeArray);
                    $attributeArray = (is_array($attributeArray) ? $attributeArray : array());
                    foreach ($attributeArray as $attributeId => $attributeValueId) {
                        $attributeRecord = \common\models\ProductsOptions2ProductsOptionsValues::find()->alias('atv')
                            ->leftJoin(\common\models\ProductsOptions::tableName() . ' a', 'a.products_options_id = atv.products_options_id')
                            ->leftJoin(\common\models\ProductsOptionsValues::tableName() . ' av', 'av.products_options_values_id = atv.products_options_values_id')
                            ->where(['atv.products_options_id' => $attributeId, 'atv.products_options_values_id' => $attributeValueId,
                                'a.language_id' => $languageId, 'av.language_id' => $languageId
                            ])->select('*')->asArray(true)->one();
                        if (!is_array($attributeRecord)) {
                            return false;
                        }
                        $attributeRecord['language_code'] = $languageCode;
                        $attributeRecordArray[] = $attributeRecord;
                        unset($attributeRecord);
                    }
                    unset($attributeValueId);
                    unset($attributeArray);
                    unset($languageCode);
                    unset($attributeId);
                    unset($languageId);
                    $this->stockRecordArray[trim($inventoryRecord->products_id)] = array(
                        'inventory_id' => (int)$inventoryRecord->inventory_id,
                        'prid' => (int)$inventoryRecord->prid,
                        'products_id' => trim($inventoryRecord->products_id),
                        'products_model' => trim($inventoryRecord->products_model),
                        'stock_indication_id' => (int)$inventoryRecord->stock_indication_id,
                        'stock_delivery_terms_id' => (int)$inventoryRecord->stock_delivery_terms_id,
                        'stock_control' => (int)$inventoryRecord->stock_control,
                        'attributeRecordArray' => $attributeRecordArray,
                        'warehouseRecordArray' => (\common\models\WarehousesProducts::find()->alias('p')
                            ->leftJoin(\common\models\Warehouses::tableName() . ' w', 'w.warehouse_id = p.warehouse_id')
                            ->leftJoin(\common\models\Suppliers::tableName() . ' s', 's.suppliers_id = p.suppliers_id')
                            ->leftJoin(\common\models\Locations::tableName() . ' l', 'l.location_id = p.location_id')
                            ->leftJoin(\common\models\LocationBlocks::tableName() . ' lb', 'l.block_id = lb.block_id')
                            ->where(['prid' => (int)$inventoryRecord->prid, 'products_id' => trim($inventoryRecord->products_id)])
                            ->select(['p.*', 'w.warehouse_name', 's.suppliers_name', 'l.location_name', 'lb.block_name'])->asArray(true)->all()
                        )
                    );
                    unset($attributeRecordArray);
                }
                unset($inventoryRecord);
                unset($uProductId);
                return true;
            }
        }
        return false;
    }

    private function loadProduct($productId = 0)
    {
        $productId = (int)$productId;
        $productRecord = \common\helpers\Product::getRecord($productId);
        if ($productRecord instanceof \common\models\Products) {
            if (!isset($this->stockRecordArray[trim($productRecord->products_id)])) {
                $this->stockRecordArray[trim($productRecord->products_id)] = array(
                    'inventory_id' => 0,
                    'prid' => (int)$productRecord->products_id,
                    'products_id' => trim($productRecord->products_id),
                    'products_model' => trim($productRecord->products_model),
                    'products_status' => (int)$productRecord->products_status,
                    'manual_control_status' => (int)$productRecord->manual_control_status,
                    'manual_stock_unlimited' => (int)$productRecord->manual_stock_unlimited,
                    'stock_indication_id' => (int)$productRecord->stock_indication_id,
                    'stock_delivery_terms_id' => (int)$productRecord->stock_delivery_terms_id,
                    'stock_reorder_level' => (int)$productRecord->stock_reorder_level,
                    'stock_reorder_quantity' => (int)$productRecord->stock_reorder_quantity,
                    'stock_control' => (int)$productRecord->stock_control,
                    'products_id_stock' => (int)$productRecord->products_id_stock,
                    'reorder_auto' => (int)$productRecord->reorder_auto,
                    'without_inventory' => (int)$productRecord->without_inventory,
                    'warehouseRecordArray' => (\common\models\WarehousesProducts::find()->alias('p')
                        ->leftJoin(\common\models\Warehouses::tableName() . ' w', 'w.warehouse_id = p.warehouse_id')
                        ->leftJoin(\common\models\Suppliers::tableName() . ' s', 's.suppliers_id = p.suppliers_id')
                        ->leftJoin(\common\models\Locations::tableName() . ' l', 'l.location_id = p.location_id')
                        ->leftJoin(\common\models\LocationBlocks::tableName() . ' lb', 'l.block_id = lb.block_id')
                        ->where(['prid' => (int)$productRecord->products_id, 'products_id' => trim((int)$productRecord->products_id)])
                        ->select(['p.*', 'w.warehouse_name', 's.suppliers_name', 'l.location_name', 'lb.block_name'])->asArray(true)->all()
                    )
                );
            }
            $childArray = \common\helpers\Product::getChildArray($productId);
            if (count($childArray) > 0) {
                foreach ($childArray as $child) {
                    if (!isset($this->stockRecordArray[trim($child['product_id'])])) {
                        $this->loadProduct($child['product_id']);
                    }
                }
                unset($child);
            } elseif (\common\helpers\Acl::checkExtensionAllowed('Inventory', 'allowed')) {
                foreach (\common\models\Inventory::find()->where(['prid' => (int)$productRecord->products_id])->asArray(true)->all() as $inventoryRecord) {
                    if (!isset($this->stockRecordArray[trim($inventoryRecord['products_id'])])) {
                        $this->loadInventory($inventoryRecord['products_id']);
                    }
                }
                unset($inventoryRecord);
            }
            unset($productRecord);
            unset($childArray);
            unset($productId);
            return true;
        }
        return false;
    }

    public function load($uProductIdArray = array())
    {
        $this->clear();
        if (!is_array($uProductIdArray)) {
            $uProductIdArray = \common\models\Products::find()->select('products_id')->asArray(true)->column();
        }
        foreach ($uProductIdArray as $uProductId) {
            if ((trim((int)$uProductId) == trim($uProductId)) OR !\common\helpers\Extensions::isAllowed('Inventory')) {
                $this->loadProduct($uProductId);
            }
            $this->loadInventory($uProductId);
        }
        unset($uProductIdArray);
        unset($uProductId);
        return true;
    }

    public function validate()
    {
        if (!is_array($this->stockRecordArray)) {
            return false;
        }
        if (!parent::validate()) {
            return false;
        }
        $warehouseNameList = [];
        foreach (\common\models\Warehouses::find()->asArray(true)->all() as $warehouseRecord) {
            $warehouseNameList[$warehouseRecord['warehouse_id']] = $warehouseRecord['warehouse_name'];
        }
        unset($warehouseRecord);
        $supplierNameList = [];
        foreach (\common\models\Suppliers::find()->asArray(true)->all() as $supplierRecord) {
            $supplierNameList[$supplierRecord['suppliers_id']] = $supplierRecord['suppliers_name'];
        }
        unset($supplierRecord);
        $locationList = [];
        foreach (\common\models\Locations::find()->asArray(true)->all() as $locationRecord) {
            $locationList[$locationRecord['location_id']] = $locationRecord['location_name'];
        }
        unset($locationRecord);
        /*$locationBlockList = [];
        foreach (\common\models\LocationBlocks::find()->asArray(true)->all() as $locationBlockRecord) {
            $locationBlockList[$locationBlockRecord['block_id']] = $locationBlockRecord['block_name'];
        }
        unset($locationBlockRecord);*/
        foreach ($this->stockRecordArray as $key => &$stockRecord) {
            $stockRecord['products_model'] = trim(isset($stockRecord['products_model']) ? $stockRecord['products_model'] : '');
            $stockRecord['attributeRecordArray'] = ((isset($stockRecord['attributeRecordArray']) AND is_array($stockRecord['attributeRecordArray']))
                ? $stockRecord['attributeRecordArray'] : array()
            );
            $stockRecord['warehouseRecordArray'] = ((isset($stockRecord['warehouseRecordArray']) AND is_array($stockRecord['warehouseRecordArray']))
                ? $stockRecord['warehouseRecordArray'] : array()
            );
            if ($stockRecord['products_model'] != '') {
                /*if ((count($stockRecord['attributeRecordArray']) > 0) == true) {}*/
                $searchRecord = \common\models\Inventory::find()->where(['products_model' => $stockRecord['products_model']])->asArray(true)->all();
                if (count($searchRecord) == 0) {
                    $searchRecord = \common\models\Products::find()->where(['products_model' => $stockRecord['products_model']])->asArray(true)->all();
                }
                $uProductId = trim(isset($stockRecord['products_id']) ? $stockRecord['products_id'] : '');
                if (($uProductId != '') AND (count($searchRecord) > 1)) {
                    foreach ($searchRecord as $exactRecord) {
                        if ($uProductId === trim($exactRecord['products_id'])) {
                            $searchRecord = array($exactRecord);
                            break;
                        }
                    }
                    unset($exactRecord);
                }
                unset($uProductId);
                if (count($searchRecord) != 1) {
                    unset($this->stockRecordArray[$key]);
                    continue;
                }
                $searchRecord = $searchRecord[0];
                $stockRecord['prid'] = (int)(isset($searchRecord['prid']) ? $searchRecord['prid'] : $searchRecord['products_id']);
                $stockRecord['products_id'] = trim($searchRecord['products_id']);
                unset($searchRecord);
            }
            $stockRecord['products_id'] = trim(isset($stockRecord['products_id']) ? $stockRecord['products_id'] : '0');
            $stockRecord['prid'] = (int)(isset($stockRecord['prid']) ? $stockRecord['prid'] : $stockRecord['products_id']);
            if (($stockRecord['prid'] <= 0) OR ($stockRecord['prid'] != (int)$stockRecord['products_id'])) {
                unset($this->stockRecordArray[$key]);
                continue;
            }
            foreach ($stockRecord as $field => $null) {
                if (!isset(self::$allowFieldList[$field])) {
                    unset($stockRecord[$field]);
                }
            }
            unset($field);
            unset($null);
            foreach ($stockRecord['warehouseRecordArray'] as $keyW => &$warehouseRecord) {
                $warehouseRecord['prid'] = $stockRecord['prid'];
                $warehouseRecord['products_id'] = $stockRecord['products_id'];
                if (isset($warehouseRecord['products_model'])) {
                    $warehouseRecord['products_model'] = trim($warehouseRecord['products_model']);
                }
                if (isset($warehouseRecord['warehouse_name']) AND (trim($warehouseRecord['warehouse_name']) != '')) {
                    $warehouseRecord['warehouse_id'] = (int)array_search($warehouseRecord['warehouse_name'], $warehouseNameList);
                }
                if (isset($warehouseRecord['suppliers_name']) AND (trim($warehouseRecord['suppliers_name']) != '')) {
                    $warehouseRecord['suppliers_id'] = (int)array_search($warehouseRecord['suppliers_name'], $supplierNameList);
                }
                if (isset($warehouseRecord['location_name'])) {
                    $warehouseRecord['location_id'] = (int)array_search($warehouseRecord['location_name'], $locationList);
                }
                /*if (isset($warehouseRecord['block_name']) AND (trim($warehouseRecord['block_name']) != '')) {
                    $warehouseRecord['block_id'] = (int)array_search($warehouseRecord['block_name'], $locationBlockList);
                }*/
                $warehouseRecord['warehouse_id'] = (int)(isset($warehouseRecord['warehouse_id']) ? $warehouseRecord['warehouse_id'] : 0);
                $warehouseRecord['suppliers_id'] = (int)(isset($warehouseRecord['suppliers_id']) ? $warehouseRecord['suppliers_id'] : 0);
                $warehouseRecord['location_id'] = (int)(isset($warehouseRecord['location_id']) ? $warehouseRecord['location_id'] : 0);
                $warehouseRecord['block_id'] = (int)(isset($warehouseRecord['block_id']) ? $warehouseRecord['block_id'] : 0);
                $warehouseRecord['warehouse_id'] = (int)(($warehouseRecord['warehouse_id'] <= 0) ? \common\helpers\Warehouses::get_default_warehouse() : $warehouseRecord['warehouse_id']);
                $warehouseRecord['suppliers_id'] = (int)(($warehouseRecord['suppliers_id'] <= 0) ? \common\helpers\Suppliers::getDefaultSupplierId() : $warehouseRecord['suppliers_id']);
                $warehouseRecord['location_id'] = (int)(($warehouseRecord['location_id'] <= 0) ? 0 : $warehouseRecord['location_id']);
                $warehouseRecord['block_id'] = (int)(($warehouseRecord['block_id'] <= 0) ? 0 : $warehouseRecord['block_id']);
            }
            unset($warehouseRecord);
            unset($keyW);
        }
        unset($warehouseNameList);
        //unset($locationBlockList);
        unset($supplierNameList);
        unset($locationList);
        unset($stockRecord);
        unset($key);
        if (count($this->stockRecordArray) == 0) {
            return false;
        }
        return true;
    }

    public function create()
    {
        return $this->save();
    }

    public function save($isReplace = false)
    {
        $return = false;
        if (!$this->validate()) {
            return $return;
        }
        $doCacheList = array();
        foreach ($this->stockRecordArray as $key => &$stockRecord) {
            $isSave = false;
            try {
                $searchRecord = \common\models\Inventory::find()->where(['products_id' => $stockRecord['products_id']])->asArray(false)->all();
                if (count($searchRecord) == 0) {
                    $searchRecord = \common\models\Products::find()->where(['products_id' => $stockRecord['prid']])->asArray(false)->all();
                }
                if (count($searchRecord) == 1) {
                    $searchRecord = $searchRecord[0];
                    $searchRecord->setAttributes($stockRecord, false);
                    if ($searchRecord->save(false)) {
                        $isSave = true;
                        foreach ($stockRecord['warehouseRecordArray'] as $keyW => &$warehouseRecord) {
                            $isSaveW = false;
                            try {
                                $warehouseClass = \common\models\WarehousesProducts::find()
                                ->where(['prid' => $warehouseRecord['prid'], 'products_id' => $warehouseRecord['products_id'],
                                    'warehouse_id' => $warehouseRecord['warehouse_id'], 'suppliers_id' => $warehouseRecord['suppliers_id'],
                                    'location_id' => $warehouseRecord['location_id']
                                ])->asArray(false)->one();
                                if (!($warehouseClass instanceof \common\models\WarehousesProducts)) {
                                    $warehouseClass = new \common\models\WarehousesProducts();
                                    $warehouseClass->loadDefaultValues();
                                }
                                $warehouseClass->setAttributes($warehouseRecord, false);
                                if ($warehouseClass->save() == true) {
                                    $isSaveW = true;
                                    if ((float)$warehouseClass->warehouse_stock_quantity <= 0) {
                                        unset($stockRecord['warehouseRecordArray'][$keyW]);
                                        $warehouseClass->delete();
                                    } else {
                                        $warehouseRecord = ($warehouseClass->toArray() + $warehouseRecord);
                                    }
                                } else {
                                    $this->messageAdd($warehouseClass->getErrorSummary(true));
                                }
                            } catch (\Exception $exc) {
                                $this->messageAdd($exc->getMessage());
                            }
                            unset($warehouseClass);
                            if ($isSaveW != true) {
                                unset($stockRecord['warehouseRecordArray'][$keyW]);
                            }
                            unset($isSaveW);
                        }
                        unset($warehouseRecord);
                        unset($keyW);
                        foreach ($stockRecord as $field => $null) {
                            if (isset($searchRecord->{$field})) {
                                $stockRecord[$field] = $searchRecord->{$field};
                            } elseif (!is_array($stockRecord[$field])) {
                                unset($stockRecord[$field]);
                            }
                        }
                        unset($field);
                        unset($null);
                    } else {
                        $this->messageAdd($searchRecord->getErrorSummary(true));
                    }
                }
            } catch (\Exception $exc) {
                $this->messageAdd($exc->getMessage());
            }
            unset($searchRecord);
            if ($isSave != true) {
                unset($this->stockRecordArray[$key]);
            } else {
                $doCacheList[(int)$stockRecord['products_id']] = (int)$stockRecord['products_id'];
            }
            unset($isSave);
        }
        unset($stockRecord);
        unset($key);
        $return = true;
        foreach ($doCacheList as $productId) {
            $return = (\common\helpers\Product::doCache($productId) AND $return);
        }
        unset($doCacheList);
        unset($productId);
        unset($isReplace);
        return $return;
    }
}