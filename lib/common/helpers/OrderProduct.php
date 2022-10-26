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

class OrderProduct
{
    const OPS_QUOTED = 1;
    const OPS_STOCK_DEFICIT = 10;
    const OPS_STOCK_PENDING = 12;
    const OPS_STOCK_ORDERED = 15;
    const OPS_RECEIVED = 20;
    const OPS_DISPATCHED = 30;
    const OPS_DELIVERED = 40;
    const OPS_CANCELLED = 50;

    /**
     * Automatically allocating stock for Order Product.
     * Rules: Product Record exists, Order Record exists, Order Product Record exists, Order Product Status acceptable, Order updateAllocateAllow passed, Product isValidAllocated passed.
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @return mixed false on error or current Order Product Status Id
     */
    public static function doAllocateAutomatic($orderProductRecord = 0, $doCache = false)
    {
        $orderProductRecord = self::getRecord($orderProductRecord);
        if (!($orderProductRecord instanceof \common\models\OrdersProducts)) {
            return false;
        }
        $orderRecord = \common\models\Orders::findOne($orderProductRecord->orders_id);
        if (!($orderRecord instanceof \common\models\Orders)) {
            return false;
        }
        if (\common\helpers\Order::isAllocateTemporary($orderRecord) == true) {
            \common\helpers\Order::updateAllocateAllow($orderRecord, true);
        }
        if (\common\helpers\Order::updateAllocateAllow($orderRecord) <= 0) {
            return false;
        }
        $productRecord = \common\helpers\Product::getRecord($orderProductRecord->products_id, false, true);
        if (!($productRecord instanceof \common\models\Products)) {
            return false;
        }
        if (($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezed()) {
            $ext::doAllocateAutomaticWarehouseFreeze($orderProductRecord, $orderRecord, $productRecord);
            \common\helpers\Product::doCache($productRecord);
            return false;
        }
        if (\common\helpers\Product::isValidAllocated($orderProductRecord->uprid) != true) {
            return false;
        }
        if (defined('STOCK_LIMITED') AND STOCK_LIMITED == 'true') {
            $return = self::doAllocateAutomaticWarehouse($orderProductRecord, $orderRecord, $productRecord);
        } else {
            $return = self::doAllocateAutomaticUnlimited($orderProductRecord, $orderRecord, $productRecord);
        }
        unset($orderRecord);
        $orderProductStatusId = self::evaluate($orderProductRecord);
        unset($orderProductRecord);
        if ($return !== false) {
            $return = $orderProductStatusId;
        }
        unset($orderProductStatusId);
        if ((int)$doCache > 0) {
            \common\helpers\Product::doCache($productRecord);
        }
        unset($productRecord);
        unset($doCache);
        return $return;
    }

    private static function collectPointAutoRelocate()
    {

    }

    /**
     * Automatically allocating stock for Order Product from Warehouse stock.
     * Rules: Received Dispatched Allocations locked on amount of Dispatched quantity.
     * Behaviour: updating already present Allocations by Warehouse/Supplier/Location/Layer/Batch (W/S/L/L/B) priority, cleaning up unavailable Allocations, Allocating deficit by W/S/L/L/B priority
     * @param \common\models\OrdersProducts $orderProductRecord
     * @param \common\models\Orders $orderRecord
     * @param \common\models\Products $productRecord
     * @return boolean false on error, true on success
     */
    private static function doAllocateAutomaticWarehouse(\common\models\OrdersProducts $orderProductRecord, \common\models\Orders $orderRecord, \common\models\Products $productRecord)
    {
        $uProductId = \common\helpers\Inventory::getInventoryId($orderProductRecord->uprid);
        $isTemporary = \common\helpers\Order::isAllocateTemporary($orderRecord);
        $productQuantityReal = self::getQuantityReal($orderProductRecord);

        $force_sell_from_collect_warehouse_id = false;
        if (\common\helpers\Acl::checkExtensionAllowed('CollectionPoints') && preg_match('/^collect_(\d+)$/', $orderRecord->shipping_class, $collectIdMatch)) {
            $CollectionPoint = \common\extensions\CollectionPoints\models\CollectionPoints::findOne($collectIdMatch[1]);
            if ($CollectionPoint instanceof \common\extensions\CollectionPoints\models\CollectionPoints && $CollectionPoint->warehouses_address_book_id > 0) {
                $warehouses_address_book_id = $CollectionPoint->warehouses_address_book_id;
                $collectWarehouse = \common\models\Warehouses::find()
                        ->innerJoinWith('address')
                        ->where([\common\models\WarehousesAddressBook::tableName() . '.warehouses_address_book_id' => $warehouses_address_book_id])
                        ->one();

                if ($collectWarehouse) {
                    $collect_warehouse_id = (int) $collectWarehouse->warehouse_id;
                    if ($CollectionPoint->notify_warehouse == 1 && !empty($collectWarehouse->warehouse_email_address)) {

                        $platform_config = \Yii::$app->get('platform')->config($orderRecord->platform_id);

                        $STORE_NAME = $platform_config->const_value('STORE_NAME');
                        $STORE_OWNER_EMAIL_ADDRESS = $platform_config->const_value('STORE_OWNER_EMAIL_ADDRESS');
                        $STORE_OWNER = $platform_config->const_value('STORE_OWNER');

                        $email_params = array();
                        $email_params['STORE_NAME'] = $STORE_NAME;
                        $email_params['ORDER_NUMBER'] = method_exists($orderRecord, 'getOrderNumber')?$orderRecord->getOrderNumber():$orderRecord->orders_id;
                        $email_params['PRODUCTS_ORDERED'] = $orderProductRecord->products_name . ' (' . $orderProductRecord->products_model . ') X ' . $orderProductRecord->products_quantity;

                        list($email_subject, $email_text) = \common\helpers\Mail::get_parsed_email_template('Warehouse notification', $email_params);
                        \common\helpers\Mail::send($collectWarehouse->warehouse_owner, $collectWarehouse->warehouse_email_address, $email_subject, $email_text, $STORE_OWNER, $STORE_OWNER_EMAIL_ADDRESS, $email_params);
                    }
                    if ($CollectionPoint->relocate_warehouse_id && (int) $collectWarehouse->warehouse_id != (int) $CollectionPoint->relocate_warehouse_id) {
                        $force_sell_from_collect_warehouse_id = $collect_warehouse_id;
                        // {{ relocate missing stock
                        $warehouse_uprid = \common\helpers\Inventory::normalizeInventoryId($uProductId);
                        $need_qty = $productQuantityReal - \common\helpers\Warehouses::get_products_quantity($warehouse_uprid, $collect_warehouse_id);
                        if ($need_qty > 0) {
                            \common\helpers\Warehouses::relocateQty(
                                    $warehouse_uprid,
                                    $CollectionPoint->relocate_warehouse_id,
                                    $collect_warehouse_id,
                                    $need_qty
                            );
                        }
                        // }} relocate missing stock
                    }
                }
            }
        }

        $warehouseIdArray = \common\helpers\Product::getWarehouseIdPriorityArray($uProductId, $productQuantityReal, $orderRecord->platform_id);

        if ( $force_sell_from_collect_warehouse_id!==false ){
            $_existIdx = array_search((int)$force_sell_from_collect_warehouse_id,$warehouseIdArray);
            if ( $_existIdx!==false ){
                unset($warehouseIdArray[$_existIdx]);
            }
            array_unshift($warehouseIdArray, (int)$force_sell_from_collect_warehouse_id);
        }

        $supplierIdArray = \common\helpers\Product::getSupplierIdPriorityArray($uProductId);
        $locationIdArray = \common\helpers\Product::getLocationIdPriorityArray($uProductId);
        $layerIdArray = \common\helpers\Product::getLayersIdPriorityArray($uProductId);
        $batchIdArray = \common\helpers\Product::getBatchIdPriorityArray($uProductId);

        $warehouseProductArray = [];
        foreach (\common\helpers\Warehouses::getProductArray($uProductId, $orderRecord->platform_id) as $warehouseProductRecord) {
            $warehouseProductArray[$warehouseProductRecord['layers_id']][$warehouseProductRecord['warehouse_id']][$warehouseProductRecord['suppliers_id']][$warehouseProductRecord['location_id']][$warehouseProductRecord['batch_id']] = $warehouseProductRecord;
        }
        unset($warehouseProductRecord);
        $warehouseProductPriorityArray = [];
        foreach ($layerIdArray as $layerId) {
            foreach ($warehouseIdArray as $warehouseId) {
                foreach ($supplierIdArray as $supplierId) {
                    foreach ($locationIdArray as $locationId) {
                        foreach ($batchIdArray as $batchId) {
                            if (isset($warehouseProductArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId])) {
                                $warehouseProduct = $warehouseProductArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId];
                                if (($warehouseProduct['layers_id'] == $layerId)
                                    AND ($warehouseProduct['warehouse_id'] == $warehouseId)
                                    AND ($warehouseProduct['suppliers_id'] == $supplierId)
                                    AND ($warehouseProduct['location_id'] == $locationId)
                                    AND ($warehouseProduct['batch_id'] == $batchId)
                                ) {
                                    $key = "{$warehouseProduct['layers_id']}_{$warehouseProduct['warehouse_id']}_{$warehouseProduct['suppliers_id']}_{$warehouseProduct['location_id']}_{$warehouseProduct['batch_id']}";
                                    $warehouseProductPriorityArray[$key] = $warehouseProduct;
                                    unset($key);
                                }
                            }
                            unset($warehouseProduct);
                        }
                        unset($batchId);
                    }
                    unset($locationId);
                }
                unset($supplierId);
            }
            unset($warehouseId);
        }
        unset($warehouseProductArray);
        unset($layerId);

        $productQuantityReceived = 0;
        $orderProductAllocatedArray = [];
        // REMOVE EXISTING NON PRIORITY ALLOCATION
        foreach (self::getAllocatedArray($orderProductRecord, false) as $orderProductAllocated) {
            $key = "{$orderProductAllocated->layers_id}_{$orderProductAllocated->warehouse_id}_{$orderProductAllocated->suppliers_id}_{$orderProductAllocated->location_id}_{$orderProductAllocated->batch_id}";
            if (!isset($warehouseProductPriorityArray[$key])) {
                if ($orderProductAllocated->allocate_dispatched > 0) {
                    try {
                        $orderProductAllocated->allocate_received = $orderProductAllocated->allocate_dispatched;
                        $orderProductAllocated->is_temporary = 0;
                        $orderProductAllocated->datetime = date('Y-m-d H:i:s');
                        $orderProductAllocated->save(false);
                    } catch (\Exception $exc) {
                        \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'Error.Helper.OrderProduct.doAllocateAutomaticWarehouse.RENPA.update');
                    }
                } else {
                    try {
                        $orderProductAllocated->allocate_dispatched = 0;
                        $orderProductAllocated->allocate_received = 0;
                        $orderProductAllocated->delete();
                    } catch (\Exception $exc) {
                        \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'Error.Helper.OrderProduct.doAllocateAutomaticWarehouse.RENPA.delete');
                    }
                }
            } else {
                $orderProductAllocatedArray[$key] = $orderProductAllocated;
            }
            $productQuantityReal -= (int)$orderProductAllocated->allocate_dispatched;
            $productQuantityReceived += ((int)$orderProductAllocated->allocate_received - (int)$orderProductAllocated->allocate_dispatched);
            unset($key);
        }
        unset($orderProductAllocated);
        // EOF REMOVE EXISTING NON PRIORITY ALLOCATION

        if ($productQuantityReal < 0) {
            return false;
        }

        $productAllocatedTemporaryArray = [];
        /*foreach (\common\helpers\Product::getAllocatedTemporaryArray($uProductId) as $productAllocatedTemporaryRecord) {
            $productAllocatedTemporaryArray[$productAllocatedTemporaryRecord['layers_id']][$productAllocatedTemporaryRecord['warehouse_id']][$productAllocatedTemporaryRecord['suppliers_id']][$productAllocatedTemporaryRecord['location_id']][$productAllocatedTemporaryRecord['batch_id']][] = $productAllocatedTemporaryRecord;
        }
        unset($productAllocatedTemporaryRecord);*/

        // UPDATE INVALID ALLOCATION
        if ($productQuantityReceived > $productQuantityReal) {
            $productAllocatedArray = [];
            foreach (\common\helpers\Product::getAllocatedArray($uProductId, false) as $productAllocatedRecord) {
                $productAllocatedArray[$productAllocatedRecord->layers_id][$productAllocatedRecord->warehouse_id][$productAllocatedRecord->suppliers_id][$productAllocatedRecord->location_id][$productAllocatedRecord->batch_id][] = $productAllocatedRecord;
            }
            unset($productAllocatedRecord);
            foreach (['update', 'remove'] as $type) {
                foreach (array_reverse($warehouseProductPriorityArray, true) as $key => $warehouseProduct) {
                    if (isset($orderProductAllocatedArray[$key])) {
                        $orderProductAllocated = $orderProductAllocatedArray[$key];
                        if ($type == 'update') {
                            $layerId = (int)$orderProductAllocated->layers_id;
                            $warehouseId = (int)$orderProductAllocated->warehouse_id;
                            $supplierId = (int)$orderProductAllocated->suppliers_id;
                            $locationId = (int)$orderProductAllocated->location_id;
                            $batchId = (int)$orderProductAllocated->batch_id;
                            $stockQuantity = (int)$warehouseProduct['warehouse_stock_quantity'];
                            if (isset($productAllocatedTemporaryArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId])) {
                                foreach ($productAllocatedTemporaryArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId] as $productAllocatedTemporaryRecord) {
                                    $stockQuantity -= (($productAllocatedTemporaryRecord['temporary_stock_quantity'] > 0) ? $productAllocatedTemporaryRecord['temporary_stock_quantity'] : 0);
                                }
                                unset($productAllocatedTemporaryRecord);
                            }
                            if (isset($productAllocatedArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId])) {
                                foreach ($productAllocatedArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId] as $productAllocated) {
                                    if ($orderProductAllocated->orders_products_id == $productAllocated->orders_products_id) {
                                        continue;
                                    }
                                    $stockQuantity -= ($productAllocated->allocate_received - $productAllocated->allocate_dispatched);
                                }
                                unset($productAllocated);
                            }
                            $receivedQuantity = $orderProductAllocated->allocate_received;
                            if ($orderProductAllocated->allocate_received > ($stockQuantity + $orderProductAllocated->allocate_dispatched)) {
                                $orderProductAllocated->allocate_received = ($stockQuantity + $orderProductAllocated->allocate_dispatched);
                            }
                            if ($orderProductAllocated->allocate_dispatched > $orderProductAllocated->allocate_received) {
                                $orderProductAllocated->allocate_received = $orderProductAllocated->allocate_dispatched;
                            }
                            $productQuantityReceived += ($orderProductAllocated->allocate_received - $receivedQuantity);
                            unset($receivedQuantity);
                            unset($stockQuantity);
                            unset($warehouseId);
                            unset($supplierId);
                            unset($locationId);
                            unset($batchId);
                            unset($layerId);
                        } elseif ($type == 'remove') {
                            if ($productQuantityReceived <= $productQuantityReal) {
                                break 2;
                            }
                            $receivedQuantity = ($orderProductAllocated->allocate_received - $orderProductAllocated->allocate_dispatched);
                            if (($productQuantityReceived - $receivedQuantity) < $productQuantityReal) {
                                $receivedQuantity = ($productQuantityReceived - $productQuantityReal);
                            }
                            $productQuantityReceived -= $receivedQuantity;
                            $orderProductAllocated->allocate_received -= $receivedQuantity;
                            unset($receivedQuantity);
                        }
                        try {
                            (($orderProductAllocated->allocate_received > 0)
                                ? $orderProductAllocated->save(false)
                                : $orderProductAllocated->delete()
                            );
                        } catch (\Exception $exc) {
                            \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'Error.Helper.OrderProduct.doAllocateAutomaticWarehouse.UIA.update');
                        }
                    }
                }
                $orderProductAllocatedArray = [];
                foreach (self::getAllocatedArray($orderProductRecord, false) as $orderProductAllocated) {
                    $key = "{$orderProductAllocated->layers_id}_{$orderProductAllocated->warehouse_id}_{$orderProductAllocated->suppliers_id}_{$orderProductAllocated->location_id}_{$orderProductAllocated->batch_id}";
                    if (isset($warehouseProductPriorityArray[$key])) {
                        $orderProductAllocatedArray[$key] = $orderProductAllocated;
                    }
                    unset($key);
                }
                unset($orderProductAllocated);
            }
            unset($orderProductAllocated);
            unset($productAllocatedArray);
            unset($warehouseProduct);
            unset($type);
            unset($key);
        }
        unset($orderProductAllocatedArray);
        // EOF UPDATE INVALID ALLOCATION

        // ALLOCATE BY PRIORITY
        foreach (['pallet', 'pack', 'item'] as $type) {
            $multiplier = 1;
            if ($type == 'pallet') {
                $multiplier = ((int)$productRecord->pack_unit * (int)$productRecord->packaging);
            } elseif ($type == 'pack') {
                $multiplier = (int)$productRecord->pack_unit;
            }
            if ($multiplier <= 0) {
                continue;
            }
            $productAllocatedArray = [];
            foreach (\common\helpers\Product::getAllocatedArray($uProductId, false) as $productAllocatedRecord) {
                $productAllocatedArray[$productAllocatedRecord->layers_id][$productAllocatedRecord->warehouse_id][$productAllocatedRecord->suppliers_id][$productAllocatedRecord->location_id][$productAllocatedRecord->batch_id][] = $productAllocatedRecord;
            }
            unset($productAllocatedRecord);
            foreach ($warehouseProductPriorityArray as $warehouseProduct) {
                if ($productQuantityReceived >= $productQuantityReal) {
                    break 2;
                }
                $layerId = (int)$warehouseProduct['layers_id'];
                $warehouseId = (int)$warehouseProduct['warehouse_id'];
                $supplierId = (int)$warehouseProduct['suppliers_id'];
                $locationId = (int)$warehouseProduct['location_id'];
                $batchId = (int)$warehouseProduct['batch_id'];
                $stockQuantity = (int)$warehouseProduct['warehouse_stock_quantity'];
                if (isset($productAllocatedTemporaryArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId])) {
                    foreach ($productAllocatedTemporaryArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId] as $productAllocatedTemporaryRecord) {
                        $stockQuantity -= (($productAllocatedTemporaryRecord['temporary_stock_quantity'] > 0) ? $productAllocatedTemporaryRecord['temporary_stock_quantity'] : 0);
                    }
                    unset($productAllocatedTemporaryRecord);
                }
                $orderProductAllocateRecord = false;
                if (isset($productAllocatedArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId])) {
                    foreach ($productAllocatedArray[$layerId][$warehouseId][$supplierId][$locationId][$batchId] as $productAllocated) {
                        $stockQuantity -= ((int)$productAllocated->allocate_received - (int)$productAllocated->allocate_dispatched);
                        if ($orderProductRecord->orders_products_id == $productAllocated->orders_products_id) {
                            $orderProductAllocateRecord = $productAllocated;
                        }
                    }
                    unset($productAllocated);
                }
                if ($stockQuantity < $multiplier) {
                    continue;
                }
                $stockQuantity = (int)(floor($stockQuantity / $multiplier) * $multiplier);
                if ($stockQuantity > 0) {
                    if (($productQuantityReceived + $stockQuantity) > $productQuantityReal) {
                        $stockQuantity = ($productQuantityReal - $productQuantityReceived);
                        $stockQuantity = (int)(floor($stockQuantity / $multiplier) * $multiplier);
                    }
                }
                if ($stockQuantity > 0) {
                    $productQuantityReceived += $stockQuantity;
                    try {
                        if (!($orderProductAllocateRecord instanceof \common\models\OrdersProductsAllocate)) {
                            $orderProductAllocateRecord = new \common\models\OrdersProductsAllocate();
                            $orderProductAllocateRecord->orders_products_id = $orderProductRecord->orders_products_id;
                            $orderProductAllocateRecord->layers_id = $layerId;
                            $orderProductAllocateRecord->warehouse_id = $warehouseId;
                            $orderProductAllocateRecord->suppliers_id = $supplierId;
                            $orderProductAllocateRecord->location_id = $locationId;
                            $orderProductAllocateRecord->batch_id = $batchId;
                            $orderProductAllocateRecord->platform_id = $orderRecord->platform_id;
                            $orderProductAllocateRecord->orders_id = $orderRecord->orders_id;
                            $orderProductAllocateRecord->prid = $productRecord->products_id;
                            $orderProductAllocateRecord->products_id = $uProductId;
                        }
                        $orderProductAllocateRecord->allocate_received += $stockQuantity;
                        $orderProductAllocateRecord->is_temporary = $isTemporary;
                        $orderProductAllocateRecord->datetime = date('Y-m-d H:i:s');
                        $orderProductAllocateRecord->suppliers_price = \common\models\SuppliersProducts::getSuppliersPrice($uProductId, $supplierId);
                        $orderProductAllocateRecord->save(false);
                    } catch (\Exception $exc) {
                        \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'Error.Helper.OrderProduct.doAllocateAutomaticWarehouse.ABP.save');
                    }
                    unset($orderProductAllocateRecord);
                }
                unset($stockQuantity);
            }
        }
        unset($productAllocatedArray);
        unset($warehouseProduct);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($batchId);
        unset($multiplier);
        unset($layerId);
        unset($type);
        // EOF ALLOCATE BY PRIORITY

        unset($productAllocatedTemporaryArray);
        unset($warehouseProductPriorityArray);
        unset($productQuantityReal);
        unset($warehouseIdArray);
        unset($supplierIdArray);
        unset($locationIdArray);
        unset($batchIdArray);
        unset($productRecord);
        unset($layerIdArray);
        unset($orderRecord);
        unset($isTemporary);
        unset($uProductId);
        try {
            $orderProductRecord->qty_rcvd = $productQuantityReceived;
            $orderProductRecord->save();
        } catch (\Exception $exc) {
            \Yii::warning(($exc->getMessage() . ' ' . $exc->getTraceAsString()), 'Error.Helper.OrderProduct.doAllocateAutomaticWarehouse.orderProductRecord.save');
        }
        unset($productQuantityReceived);
        unset($orderProductRecord);
        return true;
    }

    /**
     * Automatically allocating stock for Order Product from Warehouse unlimited stock.
     * Rules: Received Dispatched Allocations locked on amount of Dispatched quantity.
     * Behaviour: updating already present Allocations by Warehouse/Supplier/Location/Layers/Batch (W/S/L/L/B) priority, cleaning up unavailable Allocations, Allocating deficit by W/S/L/L/B priority
     * @param \common\models\OrdersProducts $orderProductRecord
     * @param \common\models\Orders $orderRecord
     * @param \common\models\Products $productRecord
     * @return boolean false on error, true on success
     */
    private static function doAllocateAutomaticUnlimited(\common\models\OrdersProducts $orderProductRecord, \common\models\Orders $orderRecord, \common\models\Products $productRecord)
    {
        $uProductId = \common\helpers\Inventory::getInventoryId($orderProductRecord->uprid);
        $productQuantityReal = self::getQuantityReal($orderProductRecord);
        $warehouseIdArray = \common\helpers\Product::getWarehouseIdPriorityArray($uProductId, $productQuantityReal, $orderRecord->platform_id);
        $supplierIdArray = \common\helpers\Product::getSupplierIdPriorityArray($uProductId);
        $locationIdArray = \common\helpers\Product::getLocationIdPriorityArray($uProductId);
        $layerIdArray = \common\helpers\Product::getLayersIdPriorityArray($uProductId);
        $batchIdArray = \common\helpers\Product::getBatchIdPriorityArray($uProductId);
        $productQuantityReceived = 0;
        $orderProductAllocatedArray = [];
        foreach (self::getAllocatedArray($orderProductRecord, false) as $orderProductAllocatedRecord) {
            $orderProductAllocatedArray[$orderProductAllocatedRecord->warehouse_id][$orderProductAllocatedRecord->suppliers_id][$orderProductAllocatedRecord->location_id][$orderProductAllocatedRecord->layers_id][$orderProductAllocatedRecord->batch_id] = $orderProductAllocatedRecord;
            $productQuantityReceived += $orderProductAllocatedRecord->allocate_dispatched;
        }
        unset($orderProductAllocatedRecord);

        // UPDATE EXISTING ALLOCATION BY PRIORITY
        if (count($orderProductAllocatedArray) > 0) {
            foreach ($layerIdArray as $layerId) {
                foreach ($warehouseIdArray as $warehouseId) {
                    foreach ($supplierIdArray as $supplierId) {
                        foreach ($locationIdArray as $locationId) {
                            foreach ($batchIdArray as $batchId) {
                                if ($productQuantityReceived >= $productQuantityReal) {
                                    break 5;
                                }
                                if (isset($orderProductAllocatedArray[$warehouseId][$supplierId][$locationId][$layerId][$batchId])) {
                                    $orderProductAllocated = $orderProductAllocatedArray[$warehouseId][$supplierId][$locationId][$layerId][$batchId];
                                    $orderProductAllocated->products_id = $uProductId;
                                    $stockReceived = ($productQuantityReal - $productQuantityReceived + $orderProductAllocated->allocate_dispatched);
                                    $productQuantityReceived += ($stockReceived - $orderProductAllocated->allocate_dispatched);
                                    $orderProductAllocated->allocate_received = $stockReceived;
                                    try {
                                        ($stockReceived > 0 ? $orderProductAllocated->save() : $orderProductAllocated->delete());
                                    } catch (\Exception $exc) {}
                                    unset($stockReceived);
                                    unset($orderProductAllocated);
                                    unset($orderProductAllocatedArray[$warehouseId][$supplierId][$locationId][$layerId][$batchId]);
                                }
                            }
                        }
                    }
                }
            }
            unset($warehouseId);
            unset($supplierId);
            unset($locationId);
            unset($layerId);
            unset($batchId);
        }
        // EOF UPDATE EXISTING ALLOCATION BY PRIORITY

        // UPDATE EXISTING NON PRIORITY ALLOCATION
        foreach ($orderProductAllocatedArray as $warehouseId => $supplierArray) {
            foreach ($supplierArray as $supplierId => $locationArray) {
                foreach ($locationArray as $locationId => $layersArray) {
                    foreach ($layersArray as $layerId => $batchArray) {
                        foreach ($batchArray as $batchId => $orderProductAllocated) {
                            if ($productQuantityReceived >= $productQuantityReal AND $productQuantityReceived == self::getReceived($orderProductRecord, true)) {
                                break 5;
                            }
                            if ($orderProductAllocated->allocate_dispatched > 0) {
                                $orderProductAllocated->allocate_received = $orderProductAllocated->allocate_dispatched;
                                try {
                                    $orderProductAllocated->save();
                                } catch (\Exception $exc) {}
                            } else {
                                try {
                                    $orderProductAllocated->delete();
                                } catch (\Exception $exc) {}
                            }
                        }
                    }
                }
            }
        }
        unset($orderProductAllocated);
        unset($locationArray);
        unset($layersArray);
        unset($batchArray);
        unset($supplierArray);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($layerId);
        unset($batchId);
        // EOF UPDATE EXISTING NON PRIORITY ALLOCATION

        unset($orderProductAllocatedArray);

        // ALLOCATE BY PRIORITY
        $productAllocatedArray = [];
        foreach (\common\helpers\Product::getAllocatedArray($uProductId) as $productAllocatedRecord) {
            $productAllocatedArray[$productAllocatedRecord['warehouse_id']][$productAllocatedRecord['suppliers_id']][$productAllocatedRecord['location_id']][$productAllocatedRecord['layers_id']][$productAllocatedRecord['batch_id']][] = $productAllocatedRecord;
        }
        unset($productAllocatedRecord);
        foreach ($layerIdArray as $layerId) {
            foreach ($warehouseIdArray as $warehouseId) {
                foreach ($supplierIdArray as $supplierId) {
                    foreach ($locationIdArray as $locationId) {
                        foreach ($batchIdArray as $batchId) {
                            if ($productQuantityReceived >= $productQuantityReal) {
                                break 5;
                            }
                            if (isset($productAllocatedArray[$warehouseId][$supplierId][$locationId][$layerId][$batchId])) {
                                foreach ($productAllocatedArray[$warehouseId][$supplierId][$locationId][$layerId][$batchId] as $productAllocated) {
                                    if ($productAllocated['orders_products_id'] == $orderProductRecord->orders_products_id) {
                                        unset($productAllocated);
                                        continue 2;
                                    }
                                }
                                unset($productAllocated);
                            }
                            $stockReceived = $productQuantityReal - $productQuantityReceived;
                            $productQuantityReceived += $stockReceived;
                            $orderProductAllocateRecord = new \common\models\OrdersProductsAllocate();
                            try {
                                $orderProductAllocateRecord->orders_products_id = $orderProductRecord->orders_products_id;
                                $orderProductAllocateRecord->warehouse_id = $warehouseId;
                                $orderProductAllocateRecord->suppliers_id = $supplierId;
                                $orderProductAllocateRecord->location_id = $locationId;
                                $orderProductAllocateRecord->layers_id = $layerId;
                                $orderProductAllocateRecord->batch_id = $batchId;
                                $orderProductAllocateRecord->platform_id = $orderRecord->platform_id;
                                $orderProductAllocateRecord->orders_id = $orderRecord->orders_id;
                                $orderProductAllocateRecord->prid = $productRecord->products_id;
                                $orderProductAllocateRecord->products_id = $uProductId;
                                $orderProductAllocateRecord->allocate_received = $stockReceived;
                                $orderProductAllocateRecord->suppliers_price = \common\models\SuppliersProducts::getSuppliersPrice($uProductId, $supplierId);
                                $orderProductAllocateRecord->save();
                                unset($orderProductAllocateRecord);
                            } catch (\Exception $exc) {}
                            unset($stockReceived);
                        }
                    }
                }
            }
        }
        unset($productAllocatedArray);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($layerId);
        unset($batchId);
        // EOF ALLOCATE BY PRIORITY

        unset($productQuantityReal);
        unset($warehouseIdArray);
        unset($supplierIdArray);
        unset($locationIdArray);
        unset($layerIdArray);
        unset($batchIdArray);
        unset($productRecord);
        unset($orderRecord);
        unset($uProductId);
        try {
            $orderProductRecord->qty_rcvd = $productQuantityReceived;
            $orderProductRecord->save();
        } catch (\Exception $exc) {}
        unset($productQuantityReceived);
        return true;
    }

    /**
     * Allocate Order Product specific quantity. Will restock Dispatched products
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param integer $quantity pointer to Quantity to Quote. Will be updated with real Received quantity
     * @param mixed $warehouseId Warehouse id
     * @param mixed $supplierId Supplier id
     * @param mixed $locationId Location id
     * @param mixed $layerId Layer id
     * @param mixed $batchId Batch id
     * @return boolean false on error, true on success
     */
    public static function doAllocateSpecific($orderProductRecord = 0, &$quantity = 0, $warehouseId = 0, $supplierId = 0, $locationId = 0, $layerId = 0, $batchId = 0)
    {
        global $login_id;
        $warehouseId = (int)$warehouseId;
        $supplierId = (int)$supplierId;
        $locationId = (int)$locationId;
        $layerId = (int)$layerId;
        $batchId = (int)$batchId;
        $quantityAwaiting = (int)$quantity;
        $quantity = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($quantityAwaiting >= 0 AND $orderProductRecord instanceof \common\models\OrdersProducts) {
            if (self::isValidAllocated($orderProductRecord) == true) {
                $opAllocateRecord = \common\models\OrdersProductsAllocate::find()
                    ->where(['orders_products_id' => $orderProductRecord->orders_products_id])
                    ->andWhere(['warehouse_id' => $warehouseId])
                    ->andWhere(['suppliers_id' => $supplierId])
                    ->andWhere(['location_id' => $locationId])
                    ->andWhere(['layers_id' => $layerId])
                    ->andWhere(['batch_id' => $batchId])
                    ->asArray(false)->one();
                if ($opAllocateRecord instanceof \common\models\OrdersProductsAllocate) {
                    if ($quantityAwaiting > (int)$opAllocateRecord->allocate_received) {
                        $quantityReal = self::getQuantityReal($orderProductRecord);
                        $quantityReceived = self::getReceived($orderProductRecord, true);
                        if ($quantityAwaiting > ($quantityReal - $quantityReceived + (int)$opAllocateRecord->allocate_received)) {
                            $quantityAwaiting = ($quantityReal - $quantityReceived + (int)$opAllocateRecord->allocate_received);
                        }
                        unset($quantityReceived);
                        unset($quantityReal);
                        $quantityAwaiting -= (int)$opAllocateRecord->allocate_received;
                        $wpRecord = \common\models\WarehousesProducts::find()
                            ->where(['prid' => $opAllocateRecord->prid])
                            ->andWhere(['products_id' => $opAllocateRecord->products_id])
                            ->andWhere(['warehouse_id' => $warehouseId])
                            ->andWhere(['suppliers_id' => $supplierId])
                            ->andWhere(['location_id' => $locationId])
                            ->andWhere(['layers_id' => $layerId])
                            ->andWhere(['batch_id' => $batchId])
                            ->asArray(true)->one();
                        if ($quantityAwaiting > 0 AND is_array($wpRecord) AND (int)$wpRecord['products_quantity'] > 0) {
                            if ($quantityAwaiting > (int)$wpRecord['products_quantity']) {
                                $quantityAwaiting = (int)$wpRecord['products_quantity'];
                            }
                            $opAllocateRecord->allocate_received += $quantityAwaiting;
                            try {
                                $opAllocateRecord->save();
                            } catch (\Exception $exc) {
                                $quantityAwaiting = 0;
                            }
                        } else {
                            $quantityAwaiting = 0;
                        }
                        unset($wpRecord);
                    } elseif ($quantityAwaiting < $opAllocateRecord->allocate_received) {
                        $quantityAwaiting = ((int)$opAllocateRecord->allocate_received - $quantityAwaiting);
                        self::doQuoteSpecific($orderProductRecord, $quantityAwaiting, $warehouseId, $supplierId, $locationId, $layerId, $batchId);
                    }
                } elseif ($quantityAwaiting > 0) {
                    $orderRecord = \common\helpers\Order::getRecord($orderProductRecord->orders_id);
                    if ($orderRecord instanceof \common\models\Orders) {
                        $uProductId = \common\helpers\Inventory::getInventoryId($orderProductRecord->uprid);
                        $wpRecord = \common\models\WarehousesProducts::find()
                            ->where(['prid' => (int)$uProductId])
                            ->andWhere(['products_id' => $uProductId])
                            ->andWhere(['warehouse_id' => $warehouseId])
                            ->andWhere(['suppliers_id' => $supplierId])
                            ->andWhere(['location_id' => $locationId])
                            ->andWhere(['layers_id' => $layerId])
                            ->andWhere(['batch_id' => $batchId])
                            ->asArray(true)->one();
                        if (is_array($wpRecord)) {
                            if ((int)$wpRecord['products_quantity'] > 0) {
                                if ($quantityAwaiting > (int)$wpRecord['products_quantity']) {
                                    $quantityAwaiting = (int)$wpRecord['products_quantity'];
                                }
                                $opAllocateRecord = new \common\models\OrdersProductsAllocate();
                                $opAllocateRecord->orders_products_id = $orderProductRecord->orders_products_id;
                                $opAllocateRecord->warehouse_id = $warehouseId;
                                $opAllocateRecord->suppliers_id = $supplierId;
                                $opAllocateRecord->location_id = $locationId;
                                $opAllocateRecord->layers_id = $layerId;
                                $opAllocateRecord->batch_id = $batchId;
                                $opAllocateRecord->platform_id = $orderRecord->platform_id;
                                $opAllocateRecord->orders_id = $orderRecord->orders_id;
                                $opAllocateRecord->prid = $wpRecord['prid'];
                                $opAllocateRecord->products_id = $wpRecord['products_id'];
                                $opAllocateRecord->allocate_received += $quantityAwaiting;
                                $opAllocateRecord->suppliers_price = \common\models\SuppliersProducts::getSuppliersPrice($wpRecord['products_id'], $supplierId);
                                try {
                                    $opAllocateRecord->save();
                                } catch (\Exception $exc) {
                                    $quantityAwaiting = 0;
                                }
                            } else {
                                $quantityAwaiting = 0;
                            }
                        }
                        unset($uProductId);
                        unset($wpRecord);
                    }
                    unset($orderRecord);
                }
                unset($opAllocateRecord);
                if ($quantityAwaiting > 0) {
                    $quantity = $quantityAwaiting;
                    self::evaluate($orderProductRecord);
                    \common\helpers\Product::doCache($orderProductRecord->products_id);
                }
            }
        }
        unset($orderProductRecord);
        unset($quantityAwaiting);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($layerId);
        unset($batchId);
        unset($login_id);
        return ($quantity > 0 ? true : false);
    }

    /**
     * Dispatch Order Product
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param boolean $isForced defines should be Order Product set as Dispatched even if there is no stock available
     * @return boolean false on error, true on success
     */
    public static function doDispatch($orderProductRecord = 0, $isForced = false)
    {
        global $login_id;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            if (self::isValidAllocated($orderProductRecord) != true) {
                return false;
            }
            $quantityDispatch = (self::getQuantityReal($orderProductRecord) - self::getDispatched($orderProductRecord));
            if ($quantityDispatch <= 0) {
                return true;
            }
            $updateStockParams = [
                'orders_id' => $orderProductRecord->orders_id,
                'admin_id' => $login_id
            ];
            if ($orderProductRecord->hasMethod('stockUpdateExtraParams')){
                $orderProductStockParams = $orderProductRecord->stockUpdateExtraParams();
                $updateStockParams = array_merge($orderProductStockParams, $updateStockParams);
            }

            $uProductId = \common\helpers\Inventory::getInventoryId($orderProductRecord->uprid);
            $warehouseStockQuantity = 0;
            foreach (\common\helpers\Warehouses::getProductArray($uProductId) as $warehouseProductRecord) {
                $warehouseStockQuantity += $warehouseProductRecord['warehouse_stock_quantity'];
            }
            unset($warehouseProductRecord);
            foreach (\common\models\OrdersProductsAllocate::find()
                ->where(['orders_products_id' => $orderProductRecord->orders_products_id])
                ->andWhere('allocate_received > allocate_dispatched')
                ->all() as $orderProductAllocateRecord
            ) {
                if ($uProductId !== $orderProductAllocateRecord->products_id) {
                    continue;
                }
                $quantityAwaiting = ((int)$orderProductAllocateRecord->allocate_received - (int)$orderProductAllocateRecord->allocate_dispatched);
                if ($quantityAwaiting > $quantityDispatch) {
                    $quantityAwaiting = $quantityDispatch;
                }
                $warehouseStockQuantityNew = \common\helpers\Warehouses::update_products_quantity(
                    $uProductId,
                    $orderProductAllocateRecord->warehouse_id,
                    $quantityAwaiting, '-',
                    $orderProductAllocateRecord->suppliers_id,
                    $orderProductAllocateRecord->location_id,
                    array_merge($updateStockParams, [
                        'layers_id' => $orderProductAllocateRecord->layers_id,
                        'batch_id' => $orderProductAllocateRecord->batch_id
                    ])
                );
                if ($warehouseStockQuantityNew < $warehouseStockQuantity) {
                    $quantityAwaiting = ($warehouseStockQuantity - $warehouseStockQuantityNew);
                    $orderProductAllocateRecord->allocate_dispatched += $quantityAwaiting;
                    try {
                        $orderProductAllocateRecord->save();
                        $warehouseStockQuantity = $warehouseStockQuantityNew;
                    } catch (\Exception $exc) {
                        $warehouseStockQuantity = \common\helpers\Warehouses::update_products_quantity(
                            $uProductId,
                            $orderProductAllocateRecord->warehouse_id,
                            $quantityAwaiting, '+',
                            $orderProductAllocateRecord->suppliers_id,
                            $orderProductAllocateRecord->location_id,
                            array_merge($updateStockParams, [
                                'layers_id' => $orderProductAllocateRecord->layers_id,
                                'batch_id' => $orderProductAllocateRecord->batch_id,
                                'comments' => TEXT_ORDER_PRODUCT_DO_DISPATCH_ERROR_RESTOCK
                            ])
                        );
                        $quantityAwaiting = 0;
                    }
                    $quantityDispatch -= $quantityAwaiting;
                }
                if ($quantityDispatch <= 0) {
                    break;
                }
            }
            unset($orderProductAllocateRecord);
            unset($warehouseStockQuantityNew);
            unset($warehouseStockQuantity);
            unset($quantityAwaiting);
            unset($login_id);
            if ((int)$isForced > 0 AND $quantityDispatch > 0) {
                $orderRecord = \common\helpers\Order::getRecord($orderProductRecord->orders_id);
                if ($orderRecord instanceof \common\models\Orders) {
                    $warehouseIdArray = \common\helpers\Product::getWarehouseIdPriorityArray($uProductId, $quantityDispatch, $orderRecord->platform_id);
                    $supplierIdArray = \common\helpers\Product::getSupplierIdPriorityArray($uProductId);
                    foreach ($warehouseIdArray as $warehouseId) {
                        foreach ($supplierIdArray as $supplierId) {
                            $locationId = 0;
                            $layerId = 0;
                            $batchId = 0;
                            $orderProductAllocateRecord = \common\models\OrdersProductsAllocate::find()
                                ->where(['orders_products_id' => $orderProductRecord->orders_products_id])
                                ->andWhere(['warehouse_id' => $warehouseId])
                                ->andWhere(['suppliers_id' => $supplierId])
                                ->andWhere(['location_id' => $locationId])
                                ->andWhere(['layers_id' => $layerId])
                                ->andWhere(['batch_id' => $batchId])
                                ->one();
                            if (!($orderProductAllocateRecord instanceof \common\models\OrdersProductsAllocate)) {
                                $orderProductAllocateRecord = new \common\models\OrdersProductsAllocate();
                                $orderProductAllocateRecord->orders_products_id = $orderProductRecord->orders_products_id;
                                $orderProductAllocateRecord->warehouse_id = $warehouseId;
                                $orderProductAllocateRecord->suppliers_id = $supplierId;
                                $orderProductAllocateRecord->location_id = $locationId;
                                $orderProductAllocateRecord->layers_id = $layerId;
                                $orderProductAllocateRecord->batch_id = $batchId;
                                $orderProductAllocateRecord->platform_id = $orderRecord->platform_id;
                                $orderProductAllocateRecord->orders_id = $orderRecord->orders_id;
                                $orderProductAllocateRecord->prid = $orderProductRecord->products_id;
                                $orderProductAllocateRecord->products_id = $uProductId;
                                $orderProductAllocateRecord->suppliers_price = \common\models\SuppliersProducts::getSuppliersPrice($uProductId, $supplierId);
                            }
                            $orderProductAllocateRecord->allocate_dispatched += $quantityDispatch;
                            $orderProductAllocateRecord->allocate_received = $orderProductAllocateRecord->allocate_dispatched;
                            try {
                                $orderProductAllocateRecord->save();
                                $quantityDispatch = 0;
                            } catch (\Exception $exc) {}
                            unset($orderProductAllocateRecord);
                            if ($quantityDispatch == 0) {
                                break 2;
                            }
                        }
                    }
                    unset($warehouseIdArray);
                    unset($supplierIdArray);
                    unset($warehouseId);
                    unset($supplierId);
                    unset($locationId);
                }
                unset($orderRecord);
            }
            if ($quantityDispatch <= 0) {
                \common\helpers\Order::updateAllocateAllow($orderProductRecord->orders_id, 1);
                self::doAllocateAutomatic($orderProductRecord, true);
            } else {
                self::evaluate($orderProductRecord);
                \common\helpers\Product::doCache($orderProductRecord->products_id);
            }
            unset($orderProductRecord);
            unset($quantityDispatch);
            unset($uProductId);
            unset($isForced);
            return true;
        }
        return false;
    }

    /**
     * Dispatch Order Product specific quantity
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param integer $quantity pointer to Quantity to Dispatch. Will be updated with real Dispatched quantity
     * @param mixed $warehouseId Warehouse id. Dispatch from specific Warehouse if passed
     * @param mixed $supplierId Supplier id. Dispatch from specific Supplier if passed
     * @param mixed $locationId Location id. Dispatch from specific Location if passed
     * @param mixed $layerId Layer id. Dispatch from specific Layer if passed
     * @param mixed $batchId Batch id. Dispatch from specific Batch if passed
     * @return boolean false on error, true on success
     */
    public static function doDispatchSpecific($orderProductRecord = 0, &$quantity = 0, $warehouseId = false, $supplierId = false, $locationId = false, $layerId = false, $batchId = false)
    {
        global $login_id;
        $quantityAwaiting = (int)$quantity;
        $quantity = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if (($quantityAwaiting > 0) AND ($orderProductRecord instanceof \common\models\OrdersProducts)) {
            if (self::isValidAllocated($orderProductRecord) == true) {
                $updateStockParams = [
                    'orders_id' => $orderProductRecord->orders_id,
                    'admin_id' => $login_id
                ];
                if ($orderProductRecord->hasMethod('stockUpdateExtraParams')){
                    $orderProductStockParams = $orderProductRecord->stockUpdateExtraParams();
                    $updateStockParams = array_merge($orderProductStockParams, $updateStockParams);
                }

                $quantityDispatched = 0;
                foreach (self::getAllocatedArray($orderProductRecord, false) as $opAllocateRecord) {
                    if ($quantityAwaiting <= 0) {
                        break;
                    }
                    if ($warehouseId !== false AND $warehouseId != $opAllocateRecord->warehouse_id) {
                        continue;
                    }
                    if ($supplierId !== false AND $supplierId != $opAllocateRecord->suppliers_id) {
                        continue;
                    }
                    if ($locationId !== false AND $locationId != $opAllocateRecord->location_id) {
                        continue;
                    }
                    if ($layerId !== false AND $layerId != $opAllocateRecord->layers_id) {
                        continue;
                    }
                    if ($batchId !== false AND $batchId != $opAllocateRecord->batch_id) {
                        continue;
                    }
                    $awaitingDispatch = ((int)$opAllocateRecord->allocate_received - (int)$opAllocateRecord->allocate_dispatched);
                    if ($awaitingDispatch > 0) {
                        if ($awaitingDispatch > $quantityAwaiting) {
                            $awaitingDispatch = $quantityAwaiting;
                        }
                        $quantityWarehouse = \common\helpers\Warehouses::update_products_quantity($opAllocateRecord->products_id, 0, 0, '+');
                        $quantityWarehouseNew = \common\helpers\Warehouses::update_products_quantity(
                            $opAllocateRecord->products_id,
                            $opAllocateRecord->warehouse_id,
                            $awaitingDispatch, '-',
                            $opAllocateRecord->suppliers_id,
                            $opAllocateRecord->location_id,
                            array_merge($updateStockParams, [
                                'layers_id' => $opAllocateRecord->layers_id,
                                'batch_id' => $opAllocateRecord->batch_id,
                            ])
                        );
                        $quantityWarehouse -= $quantityWarehouseNew;
                        $awaitingDispatch = (($quantityWarehouse >= 0 AND $quantityWarehouse <= $awaitingDispatch) ? $quantityWarehouse : $awaitingDispatch);
                        unset($quantityWarehouseNew);
                        unset($quantityWarehouse);
                        if ($awaitingDispatch > 0) {
                            $opAllocateRecord->allocate_dispatched += $awaitingDispatch;
                            try {
                                $opAllocateRecord->save();
                                $quantityAwaiting -= $awaitingDispatch;
                                $quantityDispatched += $awaitingDispatch;
                            } catch (\Exception $exc) {
                                \common\helpers\Warehouses::update_products_quantity(
                                    $opAllocateRecord->products_id,
                                    $opAllocateRecord->warehouse_id,
                                    $awaitingDispatch, '+',
                                    $opAllocateRecord->suppliers_id,
                                    $opAllocateRecord->location_id,
                                    array_merge($updateStockParams, [
                                        'layers_id' => $opAllocateRecord->layers_id,
                                        'batch_id' => $opAllocateRecord->batch_id,
                                        'comments' => TEXT_ORDER_PRODUCT_DO_DISPATCH_ERROR_RESTOCK
                                    ])
                                );
                            }
                        }
                    }
                    unset($awaitingDispatch);
                }
                unset($opAllocateRecord);
                if ($quantityDispatched > 0) {
                    $quantity = $quantityDispatched;
                    self::evaluate($orderProductRecord);
                    \common\helpers\Product::doCache($orderProductRecord->products_id);
                }
                unset($quantityDispatched);
            }
        }
        unset($orderProductRecord);
        unset($quantityAwaiting);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($layerId);
        unset($batchId);
        unset($login_id);
        return ($quantity > 0 ? true : false);
    }

    /**
     * Deliver Order Product
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param boolean $isForced defines should be Order Product set as Delivered even if there is quantity awaiting for Dispatch
     * @return boolean false on error, true on success
     */
    public static function doDeliver($orderProductRecord = 0, $isForced = false)
    {
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            if ((int)$isForced > 0) {
                self::doDispatch($orderProductRecord, true);
            }
            foreach (self::getAllocatedArray($orderProductRecord, false) as $opAllocateRecord) {
                $opAllocateRecord->allocate_delivered = $opAllocateRecord->allocate_dispatched;
                try {
                    $opAllocateRecord->save();
                } catch (\Exception $exc) {}
            }
            unset($opAllocateRecord);
            self::evaluate($orderProductRecord);
            unset($orderProductRecord);
            unset($isForced);
            return true;
        }
        return false;
    }

    /**
     * Deliver Order Product specific quantity
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param integer $quantity pointer to Quantity to Deliver. Will be updated with real Delivered quantity
     * @param mixed $warehouseId Warehouse id
     * @param mixed $supplierId Supplier id
     * @param mixed $locationId Location id
     * @param mixed $layerId Layer id
     * @param mixed $batchId Batch id
     * @return boolean false on error, true on success
     */
    public static function doDeliverSpecific($orderProductRecord = 0, &$quantity = 0, $warehouseId = 0, $supplierId = 0, $locationId = 0, $layerId = 0, $batchId = 0)
    {
        global $login_id;
        $warehouseId = (int)$warehouseId;
        $supplierId = (int)$supplierId;
        $locationId = (int)$locationId;
        $layerId = (int)$layerId;
        $batchId = (int)$batchId;
        $quantityAwaiting = (int)$quantity;
        $quantity = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if (($quantityAwaiting > 0) AND ($orderProductRecord instanceof \common\models\OrdersProducts)) {
            if (self::isValidAllocated($orderProductRecord) == true) {
                $opAllocateRecord = \common\models\OrdersProductsAllocate::find()
                    ->where(['orders_products_id' => $orderProductRecord->orders_products_id])
                    ->andWhere(['warehouse_id' => $warehouseId])
                    ->andWhere(['suppliers_id' => $supplierId])
                    ->andWhere(['location_id' => $locationId])
                    ->andWhere(['layers_id' => $layerId])
                    ->andWhere(['batch_id' => $batchId])
                    ->asArray(false)->one();
                if ($opAllocateRecord instanceof \common\models\OrdersProductsAllocate) {
                    if ($quantityAwaiting > ((int)$opAllocateRecord->allocate_dispatched - (int)$opAllocateRecord->allocate_delivered)) {
                        $quantityAwaiting = ((int)$opAllocateRecord->allocate_dispatched - (int)$opAllocateRecord->allocate_delivered);
                    }
                    $opAllocateRecord->allocate_delivered += $quantityAwaiting;
                    try {
                        $opAllocateRecord->save();
                    } catch (\Exception $exc) {
                        $quantityAwaiting = 0;
                    }
                    if ($quantityAwaiting > 0) {
                        $quantity = $quantityAwaiting;
                        self::evaluate($orderProductRecord);
                    }
                }
                unset($opAllocateRecord);
            }
        }
        unset($orderProductRecord);
        unset($quantityAwaiting);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($layerId);
        unset($batchId);
        unset($login_id);
        return ($quantity > 0 ? true : false);
    }

    /**
     * Cancel Order Product
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param boolean $isRestock defines should Dispatched quantity be returned to stock
     * @param string $messageStock returning to stock message
     * @return boolean false on error, true on success
     */
    public static function doCancel($orderProductRecord = 0, $isRestock = false, $messageStock = '')
    {
        global $login_id;
        $messageStock = trim($messageStock);
        if ($messageStock == '') {
            $messageStock = TEXT_ORDER_PRODUCT_DO_CANCEL_RESTOCK;
        }
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            if (self::isValidAllocated($orderProductRecord) != true) {
                return false;
            }
            $updateStockParams = [
                'orders_id' => $orderProductRecord->orders_id,
                'admin_id' => $login_id
            ];
            if ($orderProductRecord->hasMethod('stockUpdateExtraParams')){
                $orderProductStockParams = $orderProductRecord->stockUpdateExtraParams();
                $updateStockParams = array_merge($orderProductStockParams, $updateStockParams);
            }
            $return = true;
            $allocateDispatched = 0;
            foreach (self::getAllocatedArray($orderProductRecord, false) as $orderProductAllocateRecord) {
                if ((int)$isRestock > 0) {
                    if ($orderProductAllocateRecord->allocate_dispatched > 0) {
                        \common\helpers\Warehouses::update_products_quantity(
                            $orderProductAllocateRecord->products_id,
                            $orderProductAllocateRecord->warehouse_id,
                            $orderProductAllocateRecord->allocate_dispatched, '+',
                            $orderProductAllocateRecord->suppliers_id,
                            $orderProductAllocateRecord->location_id,
                            array_merge($updateStockParams, [
                                'layers_id' => $orderProductAllocateRecord->layers_id,
                                'batch_id' => $orderProductAllocateRecord->batch_id,
                                'comments' => $messageStock
                            ])
                        );
                    }
                    try {
                        $orderProductAllocateRecord->delete();
                    } catch (\Exception $exc) {
                        $return = false;
                    }
                } else {
                    $allocateDispatched += $orderProductAllocateRecord->allocate_dispatched;
                    $orderProductAllocateRecord->allocate_received = $orderProductAllocateRecord->allocate_dispatched;
                    $orderProductAllocateRecord->is_temporary = 0;
                    $orderProductAllocateRecord->datetime = date('Y-m-d H:i:s');
                    try {
                        $orderProductAllocateRecord->save();
                    } catch (\Exception $exc) {
                        $return = false;
                    }
                }
            }
            unset($orderProductAllocateRecord);
            $orderProductRecord->qty_cnld = ($orderProductRecord->products_quantity - $allocateDispatched);
            unset($allocateDispatched);
            try {
                $orderProductRecord->save();
            } catch (\Exception $exc) {
                $return = false;
            }
            self::evaluate($orderProductRecord);
            \common\helpers\Product::doCache($orderProductRecord->products_id);
            unset($orderProductRecord);
            unset($isRestock);
            unset($login_id);
            return $return;
        }
        return false;
    }

    /**
     * Cancel Order Product specific quantity. Will restock Dispatched products
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param integer $quantity pointer to Quantity to Cancel. Will be updated with real Cancelled (Quoted) quantity
     * @param boolean $isUseDeficit if true - stock deficit will be used in cancellation. If false - function will try to quote, restock and cancel $quantity
     * @param mixed $warehouseId Warehouse id for specific warehouse
     * @param mixed $supplierId Supplier id for specific supplier
     * @param mixed $locationId Location id for specific location
     * @param mixed $layerId Layer id for specific layer
     * @param mixed $batchId Batch id for specific batch
     * @return boolean false on error, true on success
     */
    public static function doCancelSpecific($orderProductRecord = 0, &$quantity = 0, $isUseDeficit = false, $warehouseId = 0, $supplierId = 0, $locationId = 0, $layerId = 0, $batchId = 0)
    {
        $warehouseId = (int)$warehouseId;
        $supplierId = (int)$supplierId;
        $locationId = (int)$locationId;
        $layerId = (int)$layerId;
        $batchId = (int)$batchId;
        $quantityQuote = (int)$quantity;
        $quantity = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if (($quantityQuote > 0) AND ($orderProductRecord instanceof \common\models\OrdersProducts)) {
            if (self::isValidAllocated($orderProductRecord) == true) {
                if ((int)$isUseDeficit > 0) {
                    $quantityQuoteTmp = ($quantityQuote - self::getStockDeficit($orderProductRecord));
                    $quantityQuoteTmp = (($quantityQuoteTmp > 0) ? $quantityQuoteTmp : 0);
                } else {
                    $quantityQuoteTmp = $quantityQuote;
                }
                unset($isUseDeficit);
                if ($quantityQuoteTmp > 0) {
                    $opAllocateQuery = \common\models\OrdersProductsAllocate::find()
                        ->where(['orders_products_id' => $orderProductRecord->orders_products_id]);
                    if ($warehouseId > 0) {
                        $opAllocateQuery->andWhere(['warehouse_id' => $warehouseId]);
                    }
                    if ($supplierId > 0) {
                        $opAllocateQuery->andWhere(['suppliers_id' => $supplierId]);
                    }
                    if ($locationId > 0) {
                        $opAllocateQuery->andWhere(['location_id' => $locationId]);
                    }
                    if ($layerId > 0) {
                        $opAllocateQuery->andWhere(['layers_id' => $layerId]);
                    }
                    if ($batchId > 0) {
                        $opAllocateQuery->andWhere(['batch_id' => $batchId]);
                    }
                    foreach ($opAllocateQuery->asArray(true)->all() as $opAllocateRecord) {
                        $quantityAwaiting = $quantityQuoteTmp;
                        if (\common\helpers\OrderProduct::doQuoteSpecific($orderProductRecord, $quantityAwaiting, $opAllocateRecord['warehouse_id'], $opAllocateRecord['suppliers_id'], $opAllocateRecord['location_id'], $opAllocateRecord['layers_id'], $opAllocateRecord['batch_id'])) {
                            $quantityQuoteTmp -= $quantityAwaiting;
                            if ($quantityQuoteTmp <= 0) {
                                $quantityQuoteTmp = 0;
                                break;
                            }
                        }
                    }
                    unset($opAllocateRecord);
                    unset($opAllocateQuery);
                }
                unset($quantityAwaiting);
                $quantity = ($quantityQuote - $quantityQuoteTmp);
                unset($quantityQuoteTmp);
                $orderProductRecord->qty_cnld += $quantity;
                $orderProductRecord->qty_cnld = (($orderProductRecord->qty_cnld > $orderProductRecord->products_quantity) ? $orderProductRecord->products_quantity : $orderProductRecord->qty_cnld);
                try {
                    $orderProductRecord->save();
                } catch (\Exception $exc) {}
                self::evaluate($orderProductRecord);
                \common\helpers\Product::doCache($orderProductRecord->products_id);
            }
        }
        unset($orderProductRecord);
        unset($quantityQuote);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($layerId);
        unset($batchId);
        return ($quantity > 0 ? true : false);
    }

    /**
     * Quote Order Product
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param boolean $isReset defines should Cancelled quantity be reset to 0
     * @return boolean false on error, true on success
     */
    public static function doQuote($orderProductRecord = 0, $isReset = false)
    {
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            $qtyCnld = $orderProductRecord->qty_cnld;
            if (self::doCancel($orderProductRecord, true, TEXT_ORDER_PRODUCT_DO_QUOTE_RESTOCK) != true) {
                return false;
            }
            if ((int)$isReset > 0) {
                $qtyCnld = 0;
            }
            $orderProductRecord->qty_cnld = $qtyCnld;
            unset($qtyCnld);
            try {
                $orderProductRecord->save();
            } catch (\Exception $exc) {
                return false;
            }
            self::evaluate($orderProductRecord);
            if ($orderProductRecord->qty_rcvd == 0 AND $orderProductRecord->orders_products_status == self::OPS_STOCK_DEFICIT) {
                $orderProductRecord->orders_products_status = self::OPS_QUOTED;
                $orderProductRecord->orders_products_status_manual = 0;
                try {
                    $orderProductRecord->save();
                } catch (\Exception $exc) {}
            }
            unset($orderProductRecord);
            unset($isReset);
            return true;
        }
        return false;
    }

    /**
     * Quote Order Product specific quantity. Will restock Dispatched products
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param integer $quantity pointer to Quantity to Quote. Will be updated with real Quoted quantity
     * @param mixed $warehouseId Warehouse id
     * @param mixed $supplierId Supplier id
     * @param mixed $locationId Location id
     * @param mixed $layerId Layer id
     * @param mixed $batchId Batch id
     * @return boolean false on error, true on success
     */
    public static function doQuoteSpecific($orderProductRecord = 0, &$quantity = 0, $warehouseId = 0, $supplierId = 0, $locationId = 0, $layerId = 0, $batchId = 0)
    {
        global $login_id;
        $warehouseId = (int)$warehouseId;
        $supplierId = (int)$supplierId;
        $locationId = (int)$locationId;
        $layerId = (int)$layerId;
        $batchId = (int)$batchId;
        $quantityAwaiting = (int)$quantity;
        $quantity = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if (($quantityAwaiting > 0) AND ($orderProductRecord instanceof \common\models\OrdersProducts)) {
            if (self::isValidAllocated($orderProductRecord) == true) {
                $updateStockParams = [
                    'orders_id' => $orderProductRecord->orders_id,
                    'admin_id' => $login_id
                ];
                if ($orderProductRecord->hasMethod('stockUpdateExtraParams')){
                    $orderProductStockParams = $orderProductRecord->stockUpdateExtraParams();
                    $updateStockParams = array_merge($orderProductStockParams, $updateStockParams);
                }
                $opAllocateRecord = \common\models\OrdersProductsAllocate::find()
                    ->where(['orders_products_id' => $orderProductRecord->orders_products_id])
                    ->andWhere(['warehouse_id' => $warehouseId])
                    ->andWhere(['suppliers_id' => $supplierId])
                    ->andWhere(['location_id' => $locationId])
                    ->andWhere(['layers_id' => $layerId])
                    ->andWhere(['batch_id' => $batchId])
                    ->asArray(false)->one();
                if ($opAllocateRecord instanceof \common\models\OrdersProductsAllocate) {
                    if ($quantityAwaiting > (int)$opAllocateRecord->allocate_received) {
                        $quantityAwaiting = (int)$opAllocateRecord->allocate_received;
                    }
                    $quantityRestock = ((int)$opAllocateRecord->allocate_received - (int)$opAllocateRecord->allocate_dispatched);
                    $quantityRestock = ($quantityAwaiting - $quantityRestock);
                    $quantityRestock = ($quantityRestock > 0 ? $quantityRestock : 0);
                    if ($quantityRestock > 0) {
                        $quantityWarehouse = \common\helpers\Warehouses::update_products_quantity($opAllocateRecord->products_id, 0, 0, '+');
                        $quantityWarehouseNew = \common\helpers\Warehouses::update_products_quantity(
                            $opAllocateRecord->products_id,
                            $opAllocateRecord->warehouse_id,
                            $quantityRestock, '+',
                            $opAllocateRecord->suppliers_id,
                            $opAllocateRecord->location_id,
                            array_merge($updateStockParams, [
                                'layers_id' => $opAllocateRecord->layers_id,
                                'batch_id' => $opAllocateRecord->batch_id,
                                'comments' => TEXT_ORDER_PRODUCT_DO_QUOTE_RESTOCK
                            ])
                        );
                        if ($quantityWarehouse == $quantityWarehouseNew) {
                            $quantityRestock = 0;
                        }
                        unset($quantityWarehouseNew);
                        unset($quantityWarehouse);
                    }
                    $opAllocateRecord->allocate_dispatched -= $quantityRestock;
                    unset($quantityRestock);
                    if ($opAllocateRecord->allocate_delivered > $opAllocateRecord->allocate_dispatched) {
                        $opAllocateRecord->allocate_delivered = $opAllocateRecord->allocate_dispatched;
                    }
                    $opAllocateRecord->allocate_received -= $quantityAwaiting;
                    try {
                        if ($opAllocateRecord->allocate_received <= 0) {
                            $opAllocateRecord->delete();
                        } else {
                            $opAllocateRecord->save();
                        }
                    } catch (\Exception $exc) {
                        $quantityAwaiting = 0;
                    }
                    if ($quantityAwaiting > 0) {
                        $quantity = $quantityAwaiting;
                        self::evaluate($orderProductRecord);
                        \common\helpers\Product::doCache($orderProductRecord->products_id);
                        if ($orderProductRecord->qty_rcvd == 0 AND $orderProductRecord->orders_products_status == self::OPS_STOCK_DEFICIT) {
                            $orderProductRecord->orders_products_status = self::OPS_QUOTED;
                            $orderProductRecord->orders_products_status_manual = 0;
                            try {
                                $orderProductRecord->save();
                            } catch (\Exception $exc) {}
                        }
                    }
                }
                unset($opAllocateRecord);
            }
        }
        unset($orderProductRecord);
        unset($quantityAwaiting);
        unset($warehouseId);
        unset($supplierId);
        unset($locationId);
        unset($layerId);
        unset($batchId);
        unset($login_id);
        return ($quantity > 0 ? true : false);
    }

    /**
     * Validate and updating Order Product Allocation records.
     * Updating Dispatched based on Delivered and Received based on Disptached.
     * Deleting empty allocation records where Received equals 0.
     * Rule: Received >= Dispatched >= Delivered
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @return boolean false on error or Dispatched > Order Product Quantity Real, true - if validation is passed
     */
    public static function isValidAllocated($orderProductRecord = 0)
    {
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            $orderProductQuantityReal = self::getQuantityReal($orderProductRecord);
            $orderProductQuantityReceived = 0;
            $orderProductQuantityDispatched = 0;
            $orderProductQuantityDelivered = 0;
            foreach (self::getAllocatedArray($orderProductRecord, false) as $orderProductAllocated) {
                if ($orderProductAllocated->allocate_dispatched > 0
                    OR $orderProductAllocated->allocate_delivered > 0
                ) {
                    $isSave = false;
                    if ($orderProductAllocated->allocate_delivered > $orderProductAllocated->allocate_dispatched) {
                        $orderProductAllocated->allocate_dispatched = $orderProductAllocated->allocate_delivered;
                        $isSave = true;
                    }
                    if ($orderProductAllocated->allocate_dispatched > $orderProductAllocated->allocate_received) {
                        $orderProductAllocated->allocate_received = $orderProductAllocated->allocate_dispatched;
                        $isSave = true;
                    }
                    if ($isSave == true) {
                        try {
                            $orderProductAllocated->save();
                        } catch (\Exception $exc) {
                            return false;
                        }
                    }
                    unset($isSave);
                } elseif ($orderProductAllocated->allocate_received == 0) {
                    try {
                        $orderProductAllocated->delete();
                        continue;
                    } catch (\Exception $exc) {
                        return false;
                    }
                }
                $orderProductQuantityReceived += $orderProductAllocated->allocate_received;
                $orderProductQuantityDispatched += $orderProductAllocated->allocate_dispatched;
                $orderProductQuantityDelivered += $orderProductAllocated->allocate_delivered;
            }
            unset($orderProductAllocated);
            if ($orderProductRecord->qty_rcvd != $orderProductQuantityReceived
                OR $orderProductRecord->qty_dspd != $orderProductQuantityDispatched
                OR $orderProductRecord->qty_dlvd != $orderProductQuantityDelivered
            ) {
                try {
                    $orderProductRecord->qty_rcvd = $orderProductQuantityReceived;
                    $orderProductRecord->qty_dspd = $orderProductQuantityDispatched;
                    $orderProductRecord->qty_dlvd = $orderProductQuantityDelivered;
                    $orderProductRecord->save();
                } catch (\Exception $exc) {
                    return false;
                }
            }
            unset($orderProductQuantityDelivered);
            unset($orderProductQuantityReceived);
            // PRODUCT ASSET AUTO ASSIGN
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('ProductAssets', 'allowed')) {
                try {
                    $ext::validateAssign($orderProductRecord);
                } catch (\Exception $exc) {
                    \Yii::warning($exc->getMessage() . ' ' . $exc->getTraceAsString(), 'ErrorProductAssetsValidateAssign');
                }
            }
            // EOF PRODUCT ASSET AUTO ASSIGN
            unset($orderProductRecord);
            return ($orderProductQuantityDispatched <= $orderProductQuantityReal);
        }
        return false;
    }

    /**
     * Get Order Product Parent
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param boolean $asArray switching return type between array or instance of OrdersProducts
     * @return mixed dependent on $asArray parameter
     */
    public static function getParent($orderProductRecord = 0, $asArray = true)
    {
        $return = false;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            if (trim($orderProductRecord->parent_product) != '') {
                $return = \common\models\OrdersProducts::find()
                    ->where(['orders_id' => $orderProductRecord->orders_id])
                    ->andWhere(['template_uprid' => trim($orderProductRecord->parent_product)])
                    ->andWhere(['!=', 'orders_products_id', $orderProductRecord->orders_products_id])
                    ->asArray($asArray)->one();
            }
        }
        unset($orderProductRecord);
        unset($asArray);
        return $return;
    }

    /**
     * Get Order Product Child array
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param boolean $asArray switching return type between array of arrays or array of instances of OrdersProducts
     * @return array array of mixed depending on $asArray parameter
     */
    public static function getChildArray($orderProductRecord = 0, $asArray = true)
    {
        $return = [];
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            if (trim($orderProductRecord->sub_products) != '' && $orderProductRecord->relation_type!='linked') {
                foreach ((\common\models\OrdersProducts::find()
                    ->where(['orders_id' => $orderProductRecord->orders_id])
                    ->andWhere(['parent_product' => trim($orderProductRecord->template_uprid)])
                    ->andWhere(['!=', 'orders_products_id', $orderProductRecord->orders_products_id])
                    ->asArray($asArray)->all()) as $orderProductChildRecord
                ) {
                    $return[] = $orderProductChildRecord;
                }
                unset($orderProductChildRecord);
            }
        }
        unset($orderProductRecord);
        unset($asArray);
        return $return;
    }

    /**
     * Automatically update Order Product Status based on Order Product Allocation
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @return mixed false on error or current Order Product Status Id
     */
    public static function evaluate($orderProductRecord = 0)
    {
        $return = false;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            if (self::isValidAllocated($orderProductRecord) != true) {
                return $return;
            }
            $orderProductStatus = $orderProductRecord->orders_products_status;
            $return = $orderProductStatus;
            $isParent = false;
            $orderProductQuantityReal = -1;
            $orderProductReceived = -1;
            $orderProductDispatched = -1;
            $orderProductDelivered = -1;
            $orderProductCancelled = self::getCancelled($orderProductRecord);
            foreach (self::getChildArray($orderProductRecord, false) as $orderProductChildRecord) {
                $isParent = true;
                $opcQuantityMultiplier = 1;
                if ((int)$orderProductRecord->products_quantity > 0) {
                    $opcQuantityMultiplier = (int)ceil((int)$orderProductChildRecord->products_quantity / (int)$orderProductRecord->products_quantity);
                }
                $opcQuantityReal = (int)floor(self::getQuantityReal($orderProductChildRecord) / $opcQuantityMultiplier);
                if (($orderProductQuantityReal < 0) OR ($orderProductQuantityReal > $opcQuantityReal)) {
                    $orderProductQuantityReal = $opcQuantityReal;
                }
                $opcReceived = (int)floor(self::getReceived($orderProductChildRecord) / $opcQuantityMultiplier);
                if (($orderProductReceived < 0) OR ($orderProductReceived > $opcReceived)) {
                    $orderProductReceived = $opcReceived;
                }
                $opcDispatched = (int)floor(self::getDispatched($orderProductChildRecord) / $opcQuantityMultiplier);
                if (($orderProductDispatched < 0) OR ($orderProductDispatched > $opcDispatched)) {
                    $orderProductDispatched = $opcDispatched;
                }
                $opcDelivered = (int)floor(self::getDelivered($orderProductChildRecord) / $opcQuantityMultiplier);
                if (($orderProductDelivered < 0) OR ($orderProductDelivered > $opcDelivered)) {
                    $orderProductDelivered = $opcDelivered;
                }
            }
            unset($orderProductChildRecord);
            unset($opcQuantityMultiplier);
            unset($opcQuantityReal);
            unset($opcDispatched);
            unset($opcDelivered);
            unset($opcReceived);
            if ($isParent == true) {
                $orderProductCancelled = ((int)$orderProductRecord->products_quantity - $orderProductQuantityReal);
                foreach (self::getAllocatedArray($orderProductRecord, false) as $orderProductAllocated) {
                    try {
                        $orderProductAllocated->delete();
                    } catch (\Exception $exc) {}
                }
                unset($orderProductAllocated);
            } else {
                $orderProductQuantityReal = self::getQuantityReal($orderProductRecord);
                $orderProductReceived = 0;
                $orderProductDispatched = 0;
                $orderProductDelivered = 0;
            }
            if ($orderProductQuantityReal <= 0) {
                $return = self::OPS_CANCELLED;
                foreach (self::getAllocatedArray($orderProductRecord, false) as $orderProductAllocated) {
                    try {
                        $orderProductAllocated->delete();
                    } catch (\Exception $exc) {}
                }
                unset($orderProductAllocated);
            } else {
                foreach (self::getAllocatedArray($orderProductRecord) as $orderProductAllocated) {
                    $orderProductReceived += $orderProductAllocated['allocate_received'];
                    $orderProductDispatched += $orderProductAllocated['allocate_dispatched'];
                    $orderProductDelivered += $orderProductAllocated['allocate_delivered'];
                }
                unset($orderProductAllocated);
                if ($orderProductQuantityReal == $orderProductDelivered) {
                    $return = self::OPS_DELIVERED;
                } elseif ($orderProductQuantityReal == $orderProductDispatched) {
                    $return = self::OPS_DISPATCHED;
                } elseif ($orderProductQuantityReal == $orderProductReceived) {
                    $return = self::OPS_RECEIVED;
                } else {
                    $return = self::OPS_STOCK_DEFICIT;
                    if ($orderProductStatus == self::OPS_STOCK_ORDERED) {
                        if (self::getStockOrdered($orderProductRecord) > 0) {
                            $return = $orderProductStatus;
                        }
                    }
                }
            }
            if ($orderProductRecord->qty_rcvd != $orderProductReceived
                OR $orderProductRecord->qty_dspd != $orderProductDispatched
                OR $orderProductRecord->qty_dlvd != $orderProductDelivered
                OR $orderProductRecord->qty_cnld != $orderProductCancelled
                OR $return != $orderProductStatus
            ) {
                $orderProductRecord->qty_rcvd = $orderProductReceived;
                $orderProductRecord->qty_dspd = $orderProductDispatched;
                $orderProductRecord->qty_dlvd = $orderProductDelivered;
                $orderProductRecord->qty_cnld = $orderProductCancelled;
                $orderProductRecord->orders_products_status = $return;
                $orderProductRecord->orders_products_status_manual = 0;
                try {
                    $orderProductRecord->save();
                } catch (\Exception $exc) {
                    $return = $orderProductStatus;
                }
            }
            unset($orderProductQuantityReal);
            unset($orderProductDispatched);
            unset($orderProductDelivered);
            unset($orderProductCancelled);
            unset($orderProductReceived);
            unset($orderProductStatus);
            if ($isParent == false) {
                $isParent = self::getParent($orderProductRecord, false);
                if ($isParent instanceof \common\models\OrdersProducts) {
                    self::evaluate($isParent);
                }
            } else {
                \common\helpers\Product::doCache($orderProductRecord->products_id);
            }
            unset($isParent);
        }
        unset($orderProductRecord);
        return $return;
    }

    /**
     * Get Order Product allocated quantity
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param boolean $isCalculate define should allocated quantity be calculated or gathered from cache
     * @return integer calculated allocated quantity
     */
    public static function getAllocated($orderProductRecord = 0, $isCalculate = false)
    {
        $return = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            if ((int)$isCalculate > 0) {
                foreach (\common\models\OrdersProductsAllocate::findAll(['orders_products_id' => $orderProductRecord->orders_products_id]) as $opAllocateRecord) {
                    $return += ((int)$opAllocateRecord->allocate_received - (int)$opAllocateRecord->allocate_dispatched);
                }
                unset($opAllocateRecord);
            } else {
                $return = (self::getReceived($orderProductRecord) - self::getDispatched($orderProductRecord));
            }
        }
        unset($orderProductRecord);
        unset($isCalculate);
        return $return;
    }

    /**
     * Get Order Product Allocation array
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param boolean $asArray switching return type between array of arrays or array of instances of OrdersProductsAllocate
     * @return array array of mixed depending on $asArray parameter
     */
    public static function getAllocatedArray($orderProductRecord = 0, $asArray = true)
    {
        $return = [];
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            foreach ((\common\models\OrdersProductsAllocate::find()
                ->where(['orders_products_id' => $orderProductRecord->orders_products_id])
                ->asArray($asArray)->all())
                    as $opAllocateRecord
            ) {
                $return[] = $opAllocateRecord;
            }
            unset($opAllocateRecord);
        }
        unset($orderProductRecord);
        unset($asArray);
        return $return;
    }

    /**
     * Get product's cancelled quantity
     * (Cancelled <= Product quantity [0 -> Product quantity])
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @return int product's cancelled quantity
     */
    public static function getCancelled($orderProductRecord = 0)
    {
        $return = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            $return = (int)$orderProductRecord->qty_cnld;
        }
        unset($orderProductRecord);
        return $return;
    }

    /**
     * Get product's real quantity
     * (Real quantity = Product quantity - Cancelled [Product quantity -> 0])
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @return int product's real quantity
     */
    public static function getQuantityReal($orderProductRecord = 0)
    {
        $return = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            $return = ((int)$orderProductRecord->products_quantity - self::getCancelled($orderProductRecord));
        }
        unset($orderProductRecord);
        return $return;
    }

    /**
     * Get product's received quantity
     * (Received <= Real quantity [0 -> Real quantity])
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @param boolean $isCalculate define should received quantity be calculated or gathered from cache
     * @return int product's received quantity
     */
    public static function getReceived($orderProductRecord = 0, $isCalculate = false)
    {
        $return = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            if ((int)$isCalculate > 0) {
                foreach (\common\models\OrdersProductsAllocate::findAll(['orders_products_id' => $orderProductRecord->orders_products_id]) as $opAllocateRecord) {
                    $return += (int)$opAllocateRecord->allocate_received;
                }
                unset($opAllocateRecord);
            } else {
                $return = (int)$orderProductRecord->qty_rcvd;
            }
        }
        unset($orderProductRecord);
        unset($isCalculate);
        return $return;
    }

    /**
     * Get product's stock deficit quantity
     * (Stock deficit = Real quantity - Received [Real quantity -> 0])
     * (Stock deficit >= Stock pending + Stock ordered)
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @return int product's stock deficit quantity
     */
    public static function getStockDeficit($orderProductRecord = 0)
    {
        $return = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            $return = (self::getQuantityReal($orderProductRecord) - self::getReceived($orderProductRecord));
        }
        unset($orderProductRecord);
        return $return;
    }

    /**
     * Get product's stock ordered quantity
     * (Dependent on pending Purchase Orders Products)
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @return int product's stock ordered quantity
     */
    public static function getStockOrdered($orderProductRecord = 0)
    {
        $return = 0;
        if (\common\helpers\Acl::checkExtensionAllowed('PurchaseOrders')) {
            $orderProductRecord = self::getRecord($orderProductRecord);
            if ($orderProductRecord instanceof \common\models\OrdersProducts) {
                $return = \common\extensions\PurchaseOrders\helpers\PurchaseOrder::getStockOrdered($orderProductRecord->uprid, false);
            }
            unset($orderProductRecord);
        }
        return $return;
    }

    /**
     * Get product's dispatched quantity
     * (Dispatched <= Received [0 -> Received])
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @return int product's dispatched quantity
     */
    public static function getDispatched($orderProductRecord = 0)
    {
        $return = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            $return = (int)$orderProductRecord->qty_dspd;
        }
        unset($orderProductRecord);
        return $return;
    }

    /**
     * Get product's delivered quantity
     * (Delivered <= Received [0 -> Received])
     * @param mixed $orderProductRecord Order Product Id or instance of OrdersProducts model
     * @return int product's delivered quantity
     */
    public static function getDelivered($orderProductRecord = 0)
    {
        $return = 0;
        $orderProductRecord = self::getRecord($orderProductRecord);
        if ($orderProductRecord instanceof \common\models\OrdersProducts) {
            $return = (int)$orderProductRecord->qty_dlvd;
        }
        unset($orderProductRecord);
        return $return;
    }

    /**
     * Get Order Product record
     * @param mixed $orderProductId Order Product Id or instance of OrdersProducts model
     * @return mixed instance of OrdersProducts model or null
     */
    public static function getRecord($orderProductId = 0)
    {
        return ($orderProductId instanceof \common\models\OrdersProducts
            ? $orderProductId
            : \common\models\OrdersProducts::findOne(['orders_products_id' => (int)$orderProductId])
        );
    }

    /**
     * Get configuration array of possible automated statuses
     * @return array configuration array of possible automated statuses
     */
    public static function getStatusArray()
    {
        return [
            self::OPS_QUOTED => [
                'long' => 'Quoted',
                'short' => 'Qted',
                'colour' => '#667981',
                'key' => 'OPS_QUOTED'
            ],
            self::OPS_STOCK_DEFICIT => [
                'long' => 'Stock deficit',
                'short' => 'StckDfct',
                'colour' => '#ff9100',
                'key' => 'OPS_STOCK_DEFICIT'
            ],
            self::OPS_STOCK_PENDING => [
                'long' => 'Stock pending',
                'short' => 'StckPndg',
                'colour' => '#8e8d0d',
                'key' => 'OPS_STOCK_PENDING'
            ],
            self::OPS_STOCK_ORDERED => [
                'long' => 'Stock ordered',
                'short' => 'StckOrdr',
                'colour' => '#aa00ff',
                'key' => 'OPS_STOCK_ORDERED'
            ],
            self::OPS_RECEIVED => [
                'long' => 'Received',
                'short' => 'Rcvd',
                'colour' => '#283593',
                'key' => 'OPS_RECEIVED'
            ],
            self::OPS_DISPATCHED => [
                'long' => 'Dispatched',
                'short' => 'Dspd',
                'colour' => '#2962ff',
                'key' => 'OPS_DISPATCHED'
            ],
            self::OPS_DELIVERED => [
                'long' => 'Delivered',
                'short' => 'Dlvd',
                'colour' => '#028908',
                'key' => 'OPS_DELIVERED'
            ],
            self::OPS_CANCELLED => [
                'long' => 'Cancelled',
                'short' => 'Cnld',
                'colour' => '#ff0000',
                'key' => 'OPS_CANCELLED'
            ]
        ];
    }
}