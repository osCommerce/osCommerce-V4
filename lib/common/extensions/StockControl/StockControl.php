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


namespace common\extensions\StockControl;

use common\extensions\StockControl\models\PlatformStockControl;
use common\extensions\StockControl\models\WarehouseStockControl;
use common\extensions\StockControl\models\PlatformInventoryControl;
use common\extensions\StockControl\models\WarehouseInventoryControl;

class StockControl extends \common\classes\modules\ModuleExtensions
{
    public static function saveProduct($productRecord = false)
    {
        try {
            if ($productRecord instanceof \common\models\Products) {
                $stockControl = (int)\Yii::$app->request->post('stock_control', 0);
                if (((int)\Yii::$app->request->post('is_bundle', 0) > 0)
                    OR ((int)\Yii::$app->request->post('manual_stock_unlimited', 0) > 0)
                ) {
                    $stockControl = 0;
                }
                $productRecord->setAttributes(['stock_control' => $stockControl], false);
                switch ($stockControl) {
                    case 0:
                        break;
                    case 1:
                        foreach (\common\models\Platforms::find()->where(['status' => 1])
                            ->asArray(false)->all() as $platformRecord
                        ) {
                            $currentQuantity = (int)\Yii::$app->request->post('platform_to_qty_' . (int)$platformRecord->platform_id);
                            $pscRecord = PlatformStockControl::findOne(['products_id' => (int)$productRecord->products_id, 'platform_id' => (int)$platformRecord->platform_id]);
                            if (is_object($pscRecord)) {
                                if ($currentQuantity != (int)$pscRecord->current_quantity) {
                                    $pscRecord->current_quantity = $currentQuantity;
                                    $pscRecord->manual_quantity = $currentQuantity;
                                    $pscRecord->save(false);
                                }
                            } else {
                                $pscRecord = new PlatformStockControl();
                                $pscRecord->products_id = (int)$productRecord->products_id;
                                $pscRecord->platform_id = (int)$platformRecord->platform_id;
                                $pscRecord->current_quantity = $currentQuantity;
                                $pscRecord->manual_quantity = $currentQuantity;
                                $pscRecord->save(false);
                            }
                            unset($currentQuantity);
                            unset($pscRecord);
                        }
                        unset($platformRecord);
                        break;
                    case 2:
                        WarehouseStockControl::deleteAll(['products_id' => (int)$productRecord->products_id]);
                        foreach (\common\models\Platforms::find()->where(['status' => 1])
                            ->asArray(false)->all() as $platformRecord
                        ) {
                            $wscRecord = new WarehouseStockControl();
                            $wscRecord->products_id = (int)$productRecord->products_id;
                            $wscRecord->platform_id = (int)$platformRecord->platform_id;
                            $wscRecord->warehouse_id = (int)\Yii::$app->request->post('platform_to_warehouse_' . (int)$platformRecord->platform_id);
                            $wscRecord->save(false);
                            unset($wscRecord);
                        }
                        unset($platformRecord);
                        break;
                    default:
                        break;
                }
                unset($stockControl);
            }
        } catch (\Exception $exc) {
            \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'Error.Extension.StockControl.saveProduct');
        }
        unset($productRecord);
    }

    public static function saveAttributesAndInventorySave($uProductId)
    {
        try {
            if (\Yii::$app->request->post('inventory_control_present', 0)) {
                $stockControl = (int)\Yii::$app->request->post('inventory_control_' . $uProductId);
                if (((int)\Yii::$app->request->post('manual_stock_unlimited', 0) > 0)
                    OR ((int)\Yii::$app->request->post('is_bundle', 0) > 0)
                ) {
                    $stockControl = 0;
                }
                tep_db_query("update " . TABLE_INVENTORY . " set stock_control = '" . $stockControl . "' where products_id = '" . tep_db_input($uProductId) . "'");
                switch ($stockControl) {
                    case 0:
                        break;
                    case 1:
                        foreach (\common\models\Platforms::find()->where(['status' => 1])
                            ->asArray(false)->all() as $platformRecord
                        ) {
                            $currentQuantity = (int)\Yii::$app->request->post('platform_to_qty_' . $uProductId . '_' . (int)$platformRecord->platform_id);
                            $picRecord = PlatformInventoryControl::findOne(['products_id' => tep_db_input($uProductId), 'platform_id' => $platformRecord->platform_id]);
                            if (is_object($picRecord)) {
                                if ($currentQuantity != $picRecord->current_quantity) {
                                    $picRecord->current_quantity = $currentQuantity;
                                    $picRecord->manual_quantity = $currentQuantity;
                                    $picRecord->save(false);
                                }
                            } else {
                                $picRecord = new PlatformInventoryControl();
                                $picRecord->products_id = tep_db_input($uProductId);
                                $picRecord->platform_id = (int)$platformRecord->platform_id;
                                $picRecord->current_quantity = $currentQuantity;
                                $picRecord->manual_quantity = $currentQuantity;
                                $picRecord->save(false);
                            }
                            unset($picRecord);
                        }
                        unset($platformRecord);
                        break;
                    case 2:
                        WarehouseInventoryControl::deleteAll(['products_id' => tep_db_input($uProductId)]);
                        foreach (\common\models\Platforms::find()->where(['status' => 1])
                            ->asArray(false)->all() as $platformRecord
                        ) {
                            $wicRecord = new WarehouseInventoryControl();
                            $wicRecord->products_id = tep_db_input($uProductId);
                            $wicRecord->platform_id = (int)$platformRecord->platform_id;
                            $wicRecord->warehouse_id = (int)\Yii::$app->request->post('platform_to_warehouse_' . $uProductId . '_' . (int)$platformRecord->platform_id);
                            $wicRecord->save(false);
                            unset($wicRecord);
                        }
                        unset($platformRecord);
                        break;
                    default:
                        break;
                }
            }
        } catch (\Exception $exc) {
            \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'Error.Extension.StockControl.saveAttributesAndInventorySave');
        }
        unset($uProductId);
    }

    public static function viewProductEdit($pInfo)
    {
        return Render::widget2('admin-product-detail', [
            'pInfo' => $pInfo
        ]);
    }

    public static function viewStockTab($ikey, $inventory)
    {
        $isStockUnlimited = false;
        $productRecord = \common\helpers\Product::getRecord($inventory['uprid'] ?? 0);
        if ($productRecord instanceof \common\models\Products) {
            $isStockUnlimited = ((int)$productRecord->manual_stock_unlimited > 0);
        }
        unset($productRecord);
        return Render::widget2('admin-stock-tab', [
            'ikey' => $ikey,
            'inventory' => $inventory,
            'isStockUnlimited' => $isStockUnlimited
        ]);
    }

    public static function updateProductViewStockInfo($pInfo)
    {
        $warehouseStockControlList = [];
        foreach (WarehouseStockControl::find(['products_id' => (int)$pInfo->products_id])
            ->asArray(true)->each() as $warehouseStockControl
        ) {
            $warehouseStockControlList[$warehouseStockControl['platform_id']] = $warehouseStockControl['warehouse_id'];
        }
        $platformStockControlList = [];
        foreach (PlatformStockControl::find(['products_id' => (int)$pInfo->products_id])
            ->asArray(true)->each() as $platformStockControl
        ) {
            $platformStockControlList[$platformStockControl['platform_id']] = $platformStockControl['current_quantity'];
        }
        $platformStockList = [];
        $platformWarehouseList = [];
        foreach (\common\classes\platform::getList(true, true) as $platform) {
            $platformStockList[] = [
                'id' => $platform['id'],
                'name' => $platform['text'],
                'qty' => (isset($platformStockControlList[$platform['id']]) ? $platformStockControlList[$platform['id']] : 0),
            ];
            $platformWarehouseList[] = [
                'id' => $platform['id'],
                'name' => $platform['text'],
                'warehouse' => (isset($warehouseStockControlList[$platform['id']])
                    ? $warehouseStockControlList[$platform['id']]
                    : \common\helpers\Warehouses::get_default_warehouse()
                ),
            ];
        }
        unset($platform);
        unset($platformStockControlList);
        unset($warehouseStockControlList);
        $pInfo->platformStockList = $platformStockList;
        $pInfo->platformWarehouseList = $platformWarehouseList;
        unset($platformWarehouseList);
        unset($platformStockList);
        unset($pInfo);
    }

    public static function updateProductInventoryBox($productId)
    {
        $platformStockList = [];
        $platforWarehouseList = [];
        $productId = (int)$productId;
        $warehouseStockControlList = (WarehouseInventoryControl::find()->andWhere(['products_id' => $productId])
            ->select('warehouse_id')->asArray(true)->indexBy('platform_id')->column()
        );
        $platformStockControlList = (PlatformInventoryControl::find()->andWhere(['products_id' => $productId])
            ->select('current_quantity')->asArray(true)->indexBy('platform_id')->column()
        );
        foreach(\common\models\Platforms::find()->where(['status' => 1])->orderBy(['sort_order' => SORT_ASC])
            ->asArray(false)->all() as $platformRecord
        ) {
            $platformStockList[] = [
                'id' => $platformRecord->platform_id,
                'name' => $platformRecord->platform_name,
                'qty' => (isset($platformStockControlList[$platformRecord->platform_id]) ? $platformStockControlList[$platformRecord->platform_id] : 0),
            ];
            $platforWarehouseList[] = [
                'id' => $platformRecord->platform_id,
                'name' => $platformRecord->platform_name,
                'warehouse' => (isset($warehouseStockControlList[$platformRecord->platform_id]) ? $warehouseStockControlList[$platformRecord->platform_id] : \common\helpers\Warehouses::get_default_warehouse()),
            ];
        }
        unset($warehouseStockControlList);
        unset($platformStockControlList);
        unset($platformRecord);
        unset($productId);
        return [$platformStockList, $platforWarehouseList];
    }

    public static function updateGetProductStockInventory($uProductId, &$stockValueArray)
    {
        $uProductId = \common\helpers\Inventory::normalizeInventoryId($uProductId);
        $stockValueArray['stock_control'] = (int)($stockValueArray['stock_control'] ?? 0);
        switch ($stockValueArray['stock_control']) {
            case 1:
                $platformInventoryControl = PlatformInventoryControl::findOne(['products_id' => $uProductId, 'platform_id' => \common\classes\platform::currentId()]);
                if (is_object($platformInventoryControl)) {
                    $stockValueArray['products_quantity'] = $platformInventoryControl->current_quantity;
                }
                unset($platformInventoryControl);
                break;
            case 2:
                $warehouseInventoryControl = WarehouseInventoryControl::findOne(['products_id' => $uProductId, 'platform_id' => \common\classes\platform::currentId()]);
                if (is_object($warehouseInventoryControl)) {
                    $supplierId = (int)0;
                    $warehouseId = (int)$warehouseInventoryControl->warehouse_id;
                    $warehouses_stock_query = tep_db_query("select w.warehouse_id, w.warehouse_name, sum(wp.products_quantity) as products_quantity,"
                        . " sum(wp.allocated_stock_quantity) as allocated_stock_quantity, sum(wp.temporary_stock_quantity) as temporary_stock_quantity,"
                        . " sum(wp.warehouse_stock_quantity) as warehouse_stock_quantity, sum(wp.ordered_stock_quantity) as ordered_stock_quantity"
                        . " from  " . TABLE_WAREHOUSES . " w left join " . TABLE_WAREHOUSES_PRODUCTS . " wp on wp.warehouse_id = w.warehouse_id"
                            . (($supplierId > 0) ? " and wp.suppliers_id = '{$supplierId}'" : "")
                            . " and wp.products_id = '{$uProductId}'"
                            . " and wp.prid = '" . (int)$uProductId . "'"
                        . " where w.status = '1' and w.warehouse_id = '{$warehouseId}'"
                    );
                    if (tep_db_num_rows($warehouses_stock_query) > 0) {
                        $warehouses_stock = tep_db_fetch_array($warehouses_stock_query);
                        $stockValueArray['products_quantity'] = $warehouses_stock['products_quantity'];
                        unset($warehouses_stock);
                    }
                    unset($warehouses_stock_query);
                    unset($warehouseId);
                    unset($supplierId);
                }
                unset($warehouseInventoryControl);
                break;
        }
        unset($stockValueArray);
        unset($uProductId);
    }

    public static function updateGetProductStockProduct($productId, &$stockValueArray)
    {
        $productId = (int)$productId;
        $stockValueArray['stock_control'] = (int)($stockValueArray['stock_control'] ?? 0);
        switch ($stockValueArray['stock_control']) {
            case 1:
                $platformStockControl = PlatformStockControl::findOne(['products_id' => $productId, 'platform_id' => \common\classes\platform::currentId()]);
                if (is_object($platformStockControl)) {
                    $stockValueArray['products_quantity'] = $platformStockControl->current_quantity;
                }
                unset($platformStockControl);
                break;
            case 2:
                $warehouseStockControl = WarehouseStockControl::findOne(['products_id' => $productId, 'platform_id' => \common\classes\platform::currentId()]);
                if (is_object($warehouseStockControl)) {
                    $supplierId = (int)0;
                    $warehouseId = (int)$warehouseStockControl->warehouse_id;
                    $warehouses_stock_query = tep_db_query("select w.warehouse_id, w.warehouse_name, sum(wp.products_quantity) as products_quantity,"
                        . " sum(wp.allocated_stock_quantity) as allocated_stock_quantity, sum(wp.temporary_stock_quantity) as temporary_stock_quantity,"
                        . " sum(wp.warehouse_stock_quantity) as warehouse_stock_quantity, sum(wp.ordered_stock_quantity) as ordered_stock_quantity"
                        . " from  " . TABLE_WAREHOUSES . " w left join " . TABLE_WAREHOUSES_PRODUCTS . " wp on wp.warehouse_id = w.warehouse_id"
                            . (($supplierId > 0) ? " and wp.suppliers_id = '{$supplierId}'" : "")
                            . " and wp.products_id = '{$productId}'"
                            . " and wp.prid = '{$productId}'"
                        . " where w.status = '1' and w.warehouse_id = '{$warehouseId}'"
                    );
                    if (tep_db_num_rows($warehouses_stock_query) > 0) {
                        $warehouses_stock = tep_db_fetch_array($warehouses_stock_query);
                        $stockValueArray['products_quantity'] = $warehouses_stock['products_quantity'];
                        unset($warehouses_stock);
                    }
                    unset($warehouses_stock_query);
                    unset($warehouseId);
                    unset($supplierId);
                }
                unset($warehouseStockControl);
                break;
        }
        unset($stockValueArray);
        unset($productId);
    }

    public static function updateGetAvailable($uProductId, $platformId)
    {
        $return = false;
        $platformId = (int)$platformId;
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        if (\common\helpers\Inventory::isInventory($uProductId) != true) {
            $productRecord = \common\helpers\Product::getRecord($uProductId);
            if (($productRecord instanceof \common\models\Products) AND ($productRecord->stock_control == 1)) {
                $return = 0;
                $platformStockControl = (PlatformStockControl::find()->andWhere(['products_id' => $uProductId, 'platform_id' => $platformId])
                    ->cache((defined('ALLOW_ANY_QUERY_CACHE') AND (ALLOW_ANY_QUERY_CACHE == 'True')) ? \common\helpers\Product::PRODUCT_RECORD_CACHE : -1)
                    ->one()
                );
                if ($platformStockControl instanceof PlatformStockControl) {
                    $return = $platformStockControl->current_quantity;
                }
                unset($platformStockControl);
            }
            unset($productRecord);
        } else {
            $inventoryRecord = \common\helpers\Inventory::getRecord($uProductId);
            if (($inventoryRecord instanceof \common\models\Inventory) AND ($inventoryRecord->stock_control == 1)) {
                $return = 0;
                $platformInventoryControl = (PlatformInventoryControl::find()->andWhere(['products_id' => $uProductId, 'platform_id' => $platformId])
                    ->cache((defined('ALLOW_ANY_QUERY_CACHE') AND (ALLOW_ANY_QUERY_CACHE == 'True')) ? \common\helpers\Product::PRODUCT_RECORD_CACHE : -1)
                    ->one()
                );
                if ($platformInventoryControl instanceof PlatformInventoryControl) {
                    $return = $platformInventoryControl->current_quantity;
                }
                unset($platformInventoryControl);
            }
            unset($inventoryRecord);
        }
        unset($uProductId);
        unset($platformId);
        return $return;
    }

    public static function updateGetWarehouseIdPriorityArray($uProductId, $platformId)
    {
        $return = false;
        $platformId = (int)$platformId;
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        if (\common\helpers\Inventory::isInventory($uProductId) != true) {
            $productRecord = \common\helpers\Product::getRecord($uProductId);
            if (($productRecord instanceof \common\models\Products) AND ($productRecord->stock_control == 2)) {
                $return = [];
                $warehouseStockControlRecord = WarehouseStockControl::findOne(['products_id' => $productRecord->products_id, 'platform_id' => $platformId]);
                if ($warehouseStockControlRecord instanceof WarehouseStockControl) {
                    $return[] = (int)$warehouseStockControlRecord->warehouse_id;
                }
                unset($warehouseStockControlRecord);
            }
            unset($productRecord);
        } else {
            $inventoryRecord = \common\helpers\Inventory::getRecord($uProductId);
            if (($inventoryRecord instanceof \common\models\Inventory) AND ($inventoryRecord->stock_control == 2)) {
                $return = [];
                $warehouseInventoryControl = WarehouseInventoryControl::findOne(['products_id' => $inventoryRecord->products_id, 'platform_id' => $platformId]);
                if ($warehouseInventoryControl instanceof WarehouseInventoryControl) {
                    $return[] = (int)$warehouseInventoryControl->warehouse_id;
                }
                unset($warehouseInventoryControl);
            }
            unset($inventoryRecord);
        }
        unset($uProductId);
        unset($platformId);
        return $return;
    }

    public static function updateUpdateStockOfOrder($uProductId, $platformId)
    {
        $return = false;
        $productRecord = \common\helpers\Product::getRecord($uProductId);
        if (($productRecord instanceof \common\models\Products) AND ($productRecord->stock_control == 2)) {
            //$return = 0;
            $warehouseStockControl = WarehouseStockControl::findOne(['products_id' => $productRecord->products_id, 'platform_id' => (int)$platformId]);
            if (is_object($warehouseStockControl)) {
                $return = (int)$warehouseStockControl->warehouse_id;
            }
            unset($warehouseStockControl);
        }
        unset($productRecord);
        unset($uProductId);
        unset($platformId);
        return $return;
    }

    public static function updateStockInventoryInventory($uProductId, $platformId, $quantity)
    {
        $quantity = trim($quantity);
        $platformId = (int)$platformId;
        $uProductId = \common\helpers\Inventory::normalize_id_excl_virtual($uProductId);
        $inventoryRecord = \common\helpers\Inventory::getRecord($uProductId);
        if (($inventoryRecord instanceof \common\models\Inventory) AND ($inventoryRecord->stock_control == 1)) {
            tep_db_query("update platform_inventory_control set current_quantity = current_quantity " . $quantity
                . " where products_id = '" . tep_db_input($uProductId) . "' and platform_id='" . $platformId . "'"
            );
        }
        unset($inventoryRecord);
        unset($uProductId);
        unset($platformId);
        unset($quantity);
    }

    public static function updateStockInventoryProduct($productId, $platformId, $quantity)
    {
        $quantity = trim($quantity);
        $productId = (int)$productId;
        $platformId = (int)$platformId;
        $productRecord = \common\helpers\Product::getRecord($productId);
        if (($productRecord instanceof \common\models\Products) AND ($productRecord->stock_control == 1)) {
            tep_db_query("update platform_stock_control set current_quantity = current_quantity " . $quantity
                . " where products_id = '" . $productId . "' and platform_id='" . $platformId . "'"
            );
        }
        unset($productRecord);
        unset($platformId);
        unset($productId);
        unset($quantity);
    }

    public static function updateApiProductLoad(\common\api\Classes\Product $productClass)
    {
        $productClass->platformStockControlRecordArray = PlatformStockControl::find()->where(['products_id' => $productClass->productId])->asArray(true)->all();
        $productClass->warehouseStockControlRecordArray = WarehouseStockControl::find()->where(['products_id' => $productClass->productId])->asArray(true)->all();
    }

    public static function updateApiProductInventoryLoad(array &$inventoryRecord)
    {
        $inventoryRecord['platformInventoryControlRecordArray'] = PlatformInventoryControl::find()->where(['products_id' => $inventoryRecord['products_id']])->asArray(true)->all();
        $inventoryRecord['warehouseInventoryControlRecordArray'] = WarehouseInventoryControl::find()->where(['products_id' => $inventoryRecord['products_id']])->asArray(true)->all();
    }

    public static function updateApiProductSave(\common\api\Classes\Product $productClass)
    {
        /**
         * Platform Stock Control
         */
        $productClass->platformStockControlRecordArray = (array)($productClass->platformStockControlRecordArray ?? []);
        foreach ($productClass->platformStockControlRecordArray as $platformStockControlRecord) {
            $platformId = (int)(isset($platformStockControlRecord['platform_id']) ? $platformStockControlRecord['platform_id'] : 0);
            unset($platformStockControlRecord['products_id']);
            unset($platformStockControlRecord['platform_id']);
            if ($platformId > 0) {
                $platformStockClass = PlatformStockControl::find()->where(['products_id' => $productClass->productId, 'platform_id' => $platformId])->one();
                if (!($platformStockClass instanceof PlatformStockControl)) {
                    $platformStockClass = new PlatformStockControl();
                    $platformStockClass->loadDefaultValues();
                    $platformStockClass->products_id = $productClass->productId;
                    $platformStockClass->platform_id = $platformId;
                }
                $platformStockClass->setAttributes($platformStockControlRecord, false);
                if ($platformStockClass->save(false)) {

                } else {
                    $productClass->messageAdd($platformStockClass->getErrorSummary(true));
                }
                unset($platformStockClass);
            }
            unset($platformId);
        }
        unset($platformStockControlRecord);
        /**
         * Warehouses Stock Control
         */
        $productClass->warehouseStockControlRecordArray = (array)($productClass->warehouseStockControlRecordArray ?? []);
        foreach ($productClass->warehouseStockControlRecordArray as $warehouseStockControlRecord) {
            $platformId = (int)(isset($warehouseStockControlRecord['platform_id']) ? $warehouseStockControlRecord['platform_id'] : 0);
            unset($warehouseStockControlRecord['products_id']);
            unset($warehouseStockControlRecord['platform_id']);
            if ($platformId > 0) {
                $warehouseStockClass = WarehouseStockControl::find()->where(['products_id' => $productClass->productId, 'platform_id' => $platformId])->one();
                if (!($warehouseStockClass instanceof WarehouseStockControl)) {
                    $warehouseStockClass = new WarehouseStockControl();
                    $warehouseStockClass->loadDefaultValues();
                    $warehouseStockClass->products_id = $productClass->productId;
                    $warehouseStockClass->platform_id = $platformId;
                }
                $warehouseStockClass->setAttributes($warehouseStockControlRecord, false);
                if ($warehouseStockClass->save(false)) {

                } else {
                    $productClass->messageAdd($warehouseStockClass->getErrorSummary(true));
                }
                unset($warehouseStockClass);
            }
            unset($platformId);
        }
        unset($warehouseStockControlRecord);
    }

    public static function updateApiProductInventorySave(\common\api\Classes\Product $productClass, array $inventoryRecord, $inventoryId, $uprid)
    {
        $inventoryId = (int)$inventoryId;
        $inventoryRecord['platformInventoryControlRecordArray'] = (array)($inventoryRecord['platformInventoryControlRecordArray'] ?? []);
        if (count($inventoryRecord['platformInventoryControlRecordArray']) > 0) {
            foreach ($inventoryRecord['platformInventoryControlRecordArray'] as $platformInventoryControlRecord) {
                $platformId = (int)(isset($platformInventoryControlRecord['platform_id']) ? $platformInventoryControlRecord['platform_id'] : 0);
                unset($platformInventoryControlRecord['products_id']);
                unset($platformInventoryControlRecord['platform_id']);
                if ($platformId > 0) {
                    $inventoryClass = PlatformInventoryControl::find()->where(['products_id' => $uprid, 'platform_id' => $platformId])->one();
                    if (!($inventoryClass instanceof PlatformInventoryControl)) {
                        $inventoryClass = new PlatformInventoryControl();
                        $inventoryClass->loadDefaultValues();
                        $inventoryClass->products_id = $uprid;
                        $inventoryClass->platform_id = $platformId;
                    }
                    $inventoryClass->setAttributes($inventoryRecord, false);
                    if ($inventoryClass->save(false)) {

                    } else {
                        $productClass->messageAdd($inventoryClass->getErrorSummary(true));
                    }
                    unset($inventoryClass);
                }
                unset($platformId);
            }
            unset($platformInventoryControlRecord);
        }
        $inventoryRecord['warehouseInventoryControlRecordArray'] = (array)($inventoryRecord['warehouseInventoryControlRecordArray'] ?? []);
        if (($inventoryId > 0) AND (count($inventoryRecord['warehouseInventoryControlRecordArray']) > 0)) {
            foreach ($inventoryRecord['warehouseInventoryControlRecordArray'] as $warehouseInventoryControlRecord) {
                $platformId = (int)(isset($warehouseInventoryControlRecord['platform_id']) ? $warehouseInventoryControlRecord['platform_id'] : 0);
                unset($warehouseInventoryControlRecord['products_id']);
                unset($warehouseInventoryControlRecord['platform_id']);
                if ($platformId > 0) {
                    $inventoryClass = WarehouseInventoryControl::find()->where(['products_id' => $uprid, 'platform_id' => $platformId])->one();
                    if (!($inventoryClass instanceof WarehouseInventoryControl)) {
                        $inventoryClass = new WarehouseInventoryControl();
                        $inventoryClass->loadDefaultValues();
                        $inventoryClass->products_id = $uprid;
                        $inventoryClass->platform_id = $platformId;
                    }
                    $inventoryClass->setAttributes($warehouseInventoryControlRecord, false);
                    if ($inventoryClass->save(false)) {

                    } else {
                        $productClass->messageAdd($inventoryClass->getErrorSummary(true));
                    }
                    unset($inventoryClass);
                }
                unset($platformId);
            }
            unset($warehouseInventoryControlRecord);
        }
        unset($inventoryId);
        unset($uprid);
    }
}