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

class Warehouses {

    public static function warehouse_status_change()
    {
        $active_ids = \common\models\Warehouses::find()
            ->select(['warehouse_id'])
            ->where(['status'=>1])
            ->column();
        if ( empty($active_ids) ) $active_ids[] = 0;
        \Yii::$app->getDb()->createCommand(
            "UPDATE products p ".
            " LEFT JOIN (".
            " SELECT ".
            "  SUM(w.products_quantity) AS products_quantity, ".
            "  SUM(w.allocated_stock_quantity) AS allocated_stock_quantity, ".
            "  SUM(w.warehouse_stock_quantity) AS warehouse_stock_quantity, ".
            "  SUM(w.temporary_stock_quantity) AS temporary_stock_quantity, ".
            "  w.prid ".
            " FROM ".\common\models\WarehousesProducts::tableName()." w ".
            " WHERE w.warehouse_id IN('".implode("','",$active_ids)."') ".
            " GROUP BY w.prid ".
            ") ws ON ws.prid=p.products_id ".
            "SET ".
            " p.products_quantity=IFNULL(ws.products_quantity,0), ".
            " p.allocated_stock_quantity=IFNULL(ws.allocated_stock_quantity,0), ".
            " p.temporary_stock_quantity=IFNULL(ws.temporary_stock_quantity,0), ".
            " p.warehouse_stock_quantity=IFNULL(ws.warehouse_stock_quantity,0) "
        )->execute();
    }

    public static function getTemporaryStockTableName() {
        $freezePrefix = '';
        if (($ext = \common\helpers\Acl::checkExtensionAllowed('ReportFreezeStock')) && $ext::isFreezed()) {
            $freezePrefix = 'freeze_';
        }
        return $freezePrefix . \common\models\OrdersProductsTemporaryStock::tableName();
    }

    public static function get_warehouses_count($include_inactive = false) {
        $warehouses_query = tep_db_query("select count(*) as warehouses_count from " . TABLE_WAREHOUSES . " where 1 " . ($include_inactive ? '' : " and status = '1' ") . " limit 1");
        $warehouses = tep_db_fetch_array($warehouses_query);
        return $warehouses['warehouses_count'];
    }

    public static function get_default_warehouse() {
        static $_cached_warehouse_id = false;
        if ( $_cached_warehouse_id===false ) {
            $warehouses_query = tep_db_query("select warehouse_id from " . TABLE_WAREHOUSES . " where is_default = '1' and status = '1' limit 1");
            $warehouses = tep_db_fetch_array($warehouses_query);
            $_cached_warehouse_id = $warehouses['warehouse_id'];
        }
        return $_cached_warehouse_id;
    }

    public static function get_warehouse_name($warehouse_id) {
        $warehouses_query = tep_db_query("select warehouse_name from " . TABLE_WAREHOUSES . " where warehouse_id = '" . (int)$warehouse_id . "' limit 1");
        $warehouses = tep_db_fetch_array($warehouses_query);
        return $warehouses['warehouse_name'];
    }

    public static function get_warehouse_id($warehouse_name) {
        $warehouses_query = tep_db_query("select warehouse_id from " . TABLE_WAREHOUSES . " where warehouse_name = '" . tep_db_input($warehouse_name) . "' limit 1");
        $warehouses = tep_db_fetch_array($warehouses_query);
        return $warehouses['warehouse_id'];
    }

/**
 *
 * @staticvar array $cached
 * @param bool $include_inactive
 * @return array [id=>NN text => 'name'
 */
    public static function get_warehouses($include_inactive = false) {
        static $cached = array();
        if ( !isset($cached[$include_inactive]) ) {
            $warehouses_query = tep_db_query("select warehouse_id, warehouse_name from " . TABLE_WAREHOUSES . " where 1 " . ($include_inactive ? '' : " and status = '1' ") . " order by is_default DESC, sort_order, warehouse_name");
            while ($warehouses = tep_db_fetch_array($warehouses_query)) {
                $warehouses_array[] = array(
                    'id' => $warehouses['warehouse_id'],
                    'text' => $warehouses['warehouse_name'],
                );
            }
            $cached[$include_inactive] = $warehouses_array;
        }
        return $cached[$include_inactive];
    }

    public static function get_warehouse_address($warehouse_id, $languages_id = -1) {
        if ($languages_id<1) {
          $languages_id = \Yii::$app->settings->get('languages_id');
        }
        $address_book_query = tep_db_query(
          "SELECT w.warehouse_owner as owner, w.warehouse_telephone as telephone, w.warehouse_email_address as email_address, " .
          " wab.entry_company as company, wab.entry_company_vat, wab.entry_company_reg_number as reg_number, " .
          " wab.entry_street_address as street_address, entry_suburb as suburb, " .
          " wab.entry_city as city, wab.entry_postcode as postcode, " .
          " wab.entry_state as state, wab.entry_zone_id as zone_id, wab.entry_country_id as country_id, " .
          " c.countries_name as country_name, c.countries_iso_code_2 as country_iso_code_2, c.countries_iso_code_3 as country_iso_code_3, c.address_format_id " .
          "FROM " . TABLE_WAREHOUSES . " w, " . TABLE_WAREHOUSES_ADDRESS_BOOK . " wab " .
          " LEFT JOIN " . TABLE_COUNTRIES . " c ON c.countries_id = wab.entry_country_id and c.language_id = '" . (int) $languages_id . "' " .
          "WHERE w.warehouse_id = wab.warehouse_id and wab.is_default = '1' and w.warehouse_id = '" . (int)$warehouse_id . "' "
        );
        $address_book = tep_db_fetch_array($address_book_query);
        $name_parts = explode(' ', $address_book['owner']);
        $address_book['lastname'] = array_pop($name_parts);
        $address_book['firstname'] = implode(' ', $name_parts);
        return $address_book;
    }

    public static function update_products_quantity($products_id, $warehouse_id, $qty, $qty_prefix, $suppliers_id = 0, $location_id = 0, $parameters = array())
    {
        /**
         * NOTE!!! function return overall warehouse qty (?!)
         */
        $isError = false;
        $qty = (int)$qty;
        $qty = (($qty_prefix == '-') ? (($qty < 0) ? abs($qty) : -$qty) : $qty);
        if ($suppliers_id <= 0) {
            $supplier = \common\helpers\Suppliers::getSuppliersList($products_id);
            if (count($supplier) > 0) {
                $suppliers_id = key($supplier);
            } else {
                $supplier = \common\helpers\Suppliers::getDefaultSupplier();
                $suppliers_id = (is_object($supplier) ? $supplier->suppliers_id : 0);
            }
            unset($supplier);
        }
        $products_id = \common\helpers\Inventory::normalizeInventoryId($products_id);
        $warehouse_id = (int)$warehouse_id;
        $suppliers_id = (int)$suppliers_id;
        $location_id = (int)$location_id;
        $parameters = (is_array($parameters) ? $parameters : array());
        $layers_id = (int)(isset($parameters['layers_id']) ? $parameters['layers_id'] : 0);
        $batch_id = (int)(isset($parameters['batch_id']) ? $parameters['batch_id'] : 0);
        if ($qty == 0) {
            $isError = true;
        }
        $productRecord = \common\helpers\Product::getRecord($products_id);
        if (count(\common\helpers\Product::getChildArray($productRecord)) > 0) {
            return (int)$productRecord->warehouse_stock_quantity;
        }
        if ($isError == false AND strpos($products_id, '{') !== false) {
            $warehouseProductInventoryRecord = \common\models\WarehousesProducts::find()
                ->where(['warehouse_id' => $warehouse_id])
                ->andWhere(['suppliers_id' => $suppliers_id])
                ->andWhere(['location_id' => $location_id])
                ->andWhere(['layers_id' => $layers_id])
                ->andWhere(['batch_id' => $batch_id])
                ->andWhere(['products_id' => $products_id])
                ->andWhere(['prid' => (int)$products_id])
                ->one();
            if (!($warehouseProductInventoryRecord instanceof \common\models\WarehousesProducts)) {
                $warehouseProductInventoryRecord = new \common\models\WarehousesProducts();
                $warehouseProductInventoryRecord->warehouse_id = $warehouse_id;
                $warehouseProductInventoryRecord->suppliers_id = $suppliers_id;
                $warehouseProductInventoryRecord->location_id = $location_id;
                $warehouseProductInventoryRecord->layers_id = $layers_id;
                $warehouseProductInventoryRecord->batch_id = $batch_id;
                $warehouseProductInventoryRecord->products_id = $products_id;
                $warehouseProductInventoryRecord->prid = (int)$products_id;
            }
            $qty = ((($warehouseProductInventoryRecord->warehouse_stock_quantity + $qty) < 0) ? -$warehouseProductInventoryRecord->warehouse_stock_quantity : $qty);
            if ($qty == 0) {
                unset($warehouseProductInventoryRecord);
                $isError = true;
            } else {
                $warehouseProductInventoryRecord->warehouse_stock_quantity += $qty;
                $warehouseProductInventoryRecord->products_quantity += $qty;
                try {
                    $warehouseProductInventoryRecord->save();
                } catch (\Exception $exc) {
                    unset($warehouseProductInventoryRecord);
                    $isError = true;
                }
                if ($isError == false) {
                    $inventoryRecord = \common\helpers\Inventory::getRecord($products_id);
                    if ($inventoryRecord instanceof \common\models\Inventory) {
                        $inventoryRecord->products_quantity += $qty;
                        try {
                            $inventoryRecord->save();
                        } catch (\Exception $exc) {
                            unset($inventoryRecord);
                            $isError = true;
                        }
                    }
                }
            }
        }
        if ($isError == false) {
            $warehouseProductRecord = \common\models\WarehousesProducts::find()
                ->where(['warehouse_id' => $warehouse_id])
                ->andWhere(['suppliers_id' => $suppliers_id])
                ->andWhere(['location_id' => $location_id])
                ->andWhere(['layers_id' => $layers_id])
                ->andWhere(['batch_id' => $batch_id])
                ->andWhere(['products_id' => trim((int)$products_id)])
                ->andWhere(['prid' => (int)$products_id])
                ->one();
            if (!($warehouseProductRecord instanceof \common\models\WarehousesProducts)) {
                $warehouseProductRecord = new \common\models\WarehousesProducts();
                $warehouseProductRecord->warehouse_id = $warehouse_id;
                $warehouseProductRecord->suppliers_id = $suppliers_id;
                $warehouseProductRecord->location_id = $location_id;
                $warehouseProductRecord->layers_id = $layers_id;
                $warehouseProductRecord->batch_id = $batch_id;
                $warehouseProductRecord->products_id = (int)$products_id;
                $warehouseProductRecord->prid = (int)$products_id;
            }
            $checkQty = $qty;
            $checkQty = ((($warehouseProductRecord->warehouse_stock_quantity + $checkQty) < 0) ? -$warehouseProductRecord->warehouse_stock_quantity : $checkQty);
            if ((isset($warehouseProductInventoryRecord) AND $checkQty != $qty) OR $checkQty == 0) {
                unset($warehouseProductRecord);
                $isError = true;
            } else {
                $qty = $checkQty;
                $warehouseProductRecord->warehouse_stock_quantity += $qty;
                $warehouseProductRecord->products_quantity += $qty;
                try {
                    $warehouseProductRecord->save();
                } catch (\Exception $exc) {
                    unset($warehouseProductRecord);
                    $isError = true;
                }
            }
            unset($checkQty);
        }
        if ($isError == false) {
            if ($productRecord instanceof \common\models\Products) {
                $productRecord->products_quantity += $qty;
                try {
                    $productRecord->save();
                } catch (\Exception $exc) {
                    $isError = true;
                }
            }
        }
        unset($productRecord);
        if ($isError == true) {
            if (isset($warehouseProductRecord) AND ($warehouseProductRecord instanceof \common\models\WarehousesProducts)) {
                $warehouseProductRecord->warehouse_stock_quantity -= $qty;
                $warehouseProductRecord->products_quantity -= $qty;
                try {
                    $warehouseProductRecord->save();
                } catch (\Exception $exc) {}
            }
            if (isset($inventoryRecord) AND ($inventoryRecord instanceof \common\models\Inventory)) {
                $inventoryRecord->products_quantity -= $qty;
                try {
                    $inventoryRecord->save();
                } catch (\Exception $exc) {}
            }
            if (isset($warehouseProductInventoryRecord) AND ($warehouseProductInventoryRecord instanceof \common\models\WarehousesProducts)) {
                $warehouseProductInventoryRecord->warehouse_stock_quantity -= $qty;
                $warehouseProductInventoryRecord->products_quantity -= $qty;
                try {
                    $warehouseProductInventoryRecord->save();
                } catch (\Exception $exc) {}
            }
        }
        unset($warehouseProductInventoryRecord);
        unset($warehouseProductRecord);
        unset($inventoryRecord);
        $warehouseStockQuantity = 0;

        $active_warehouse_ids = \yii\helpers\ArrayHelper::map(static::get_warehouses(),'id','id');
        foreach (self::getProductArray($products_id) as $warehouseProductRecord) {
//            if ( $warehouse_id>0 && $warehouse_id!=$warehouseProductRecord['warehouse_id'] ) continue;
//            if ( $suppliers_id>0 && $suppliers_id!=$warehouseProductRecord['suppliers_id'] ) continue;
            if (!isset($active_warehouse_ids[$warehouseProductRecord['warehouse_id']])) continue;
            $warehouseStockQuantity += $warehouseProductRecord['warehouse_stock_quantity'];
        }
        unset($warehouseProductRecord);
        if ($isError == false) {
            $parameters['layers_id'] = $layers_id;
            $parameters['batch_id'] = $batch_id;
            $parameters['is_temporary'] = 0;
            \common\helpers\Product::writeHistory($products_id, $warehouse_id, $suppliers_id, $location_id, $qty, $parameters);
            if ($ext = \common\helpers\Acl::checkExtensionAllowed('Ebay', 'allowed')) {
                $ext::setUpdateProduct($products_id);
            }
        }
        unset($parameters);
        return $warehouseStockQuantity;
    }

    // $warehouse_id = 0 - all warehouses, $suppliers_id = 0 - all suppliers
    public static function get_products_quantity($products_id, $warehouse_id = 0, $suppliers_id = 0) {
        if (strpos(\common\helpers\Inventory::normalizeInventoryId($products_id), '{') !== false) {
            $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wp.products_quantity) as products_quantity from " . TABLE_WAREHOUSES_PRODUCTS . " wp, " . TABLE_WAREHOUSES . " w where wp.warehouse_id = w.warehouse_id and w.status = '1' " . ($warehouse_id > 0 ? " and w.warehouse_id = '" . (int) $warehouse_id . "' " : '') . " " . ($suppliers_id > 0 ? " and wp.suppliers_id = '" . (int) $suppliers_id . "' " : '') . " and wp.products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "'"));
        } else {
            $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wp.products_quantity) as products_quantity from " . TABLE_WAREHOUSES_PRODUCTS . " wp, " . TABLE_WAREHOUSES . " w where wp.warehouse_id = w.warehouse_id and w.status = '1' " . ($warehouse_id > 0 ? " and w.warehouse_id = '" . (int) $warehouse_id . "' " : '') . " " . ($suppliers_id > 0 ? " and wp.suppliers_id = '" . (int) $suppliers_id . "' " : '') . " and wp.products_id = '" . (int)$products_id . "' and wp.prid = '" . (int)$products_id . "'"));
        }
        return $warehouses_stock['products_quantity'];
    }

    public static function getQuantityInfoData($products_id)
    {
        $quantityData = [];
        if (strpos(\common\helpers\Inventory::normalizeInventoryId($products_id), '{') !== false) {
            $productsCondition = "wp.products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "'";
        } else {
            $productsCondition = "wp.products_id = '" . (int)$products_id . "' and wp.prid = '" . (int)$products_id . "'";
        }
        $product_stock_r = tep_db_query(
            "SELECT wp.products_quantity, wp.warehouse_id, wp.suppliers_id, wp.location_id ".
            "FROM " . TABLE_WAREHOUSES_PRODUCTS . " wp ".
            "  INNER JOIN " . TABLE_WAREHOUSES . " w ON wp.warehouse_id = w.warehouse_id and w.status = '1' ".
            "WHERE {$productsCondition}"

        );
        if ( tep_db_num_rows($product_stock_r)>0 ) {
            while ($product_stock = tep_db_fetch_array($product_stock_r)){
                $quantityData[] = $product_stock;
            }
        }
        return $quantityData;
    }

    // $warehouse_id = 0 - all warehouses, $suppliers_id = 0 - all suppliers
    public static function get_allocated_stock_quantity($products_id, $warehouse_id = 0, $suppliers_id = 0) {
        return 0;
        $allocated_stock = 0;
        $orders_status_array = array(); // not Completed and not Cancelled orders
        $orders_status_query = tep_db_query("select distinct orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_groups_id not in (4,5)");
        while ($orders_status = tep_db_fetch_array($orders_status_query)) {
          $orders_status_array[] = $orders_status['orders_status_id'];
        }
        $warehouses_query = tep_db_query("select warehouse_id from " . TABLE_WAREHOUSES . " where status = '1' " . ($warehouse_id > 0 ? " and warehouse_id = '" . (int) $warehouse_id . "' " : '') . " order by sort_order, warehouse_name");
        while ($warehouses = tep_db_fetch_array($warehouses_query)) {
            $suppliers_query = tep_db_query("select suppliers_id from " . TABLE_SUPPLIERS . " where status = '1' " . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "' " : '') . " order by sort_order, suppliers_name");
            while ($suppliers = tep_db_fetch_array($suppliers_query)) {
                if (strpos(\common\helpers\Inventory::normalizeInventoryId($products_id), '{') !== false) {
                    $allocated_stock_data = tep_db_fetch_array(tep_db_query("select sum(wop.products_quantity) as allocated_stock_quantity from " . TABLE_INVENTORY . " i left join " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " wop on wop.uprid = i.products_id and wop.products_id = i.prid and wop.warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and wop.suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' left join " . TABLE_ORDERS . " o on o.orders_id = wop.orders_id where i.products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "' and o.stock_updated = '1' and o.orders_status in ('" . implode("','", $orders_status_array) . "') group by i.products_id"));
                    tep_db_query("update " . TABLE_WAREHOUSES_PRODUCTS . " set allocated_stock_quantity = '" . (int)$allocated_stock_data['allocated_stock_quantity'] . "', warehouse_stock_quantity =  products_quantity + '" . (int)$allocated_stock_data['allocated_stock_quantity'] . "' + temporary_stock_quantity where warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' and products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "'");
                    $allocated_stock += $allocated_stock_data['allocated_stock_quantity'];
                } else {
                    $allocated_stock_data = tep_db_fetch_array(tep_db_query("select sum(wop.products_quantity) as allocated_stock_quantity from " . TABLE_PRODUCTS . " p left join " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " wop on wop.products_id = p.products_id and wop.warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and wop.suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' left join " . TABLE_ORDERS . " o on o.orders_id = wop.orders_id where p.products_id = '" . (int)$products_id . "' and o.stock_updated = '1' and o.orders_status in ('" . implode("','", $orders_status_array) . "') group by p.products_id"));
                    tep_db_query("update " . TABLE_WAREHOUSES_PRODUCTS . " set allocated_stock_quantity = '" . (int)$allocated_stock_data['allocated_stock_quantity'] . "', warehouse_stock_quantity =  products_quantity + '" . (int)$allocated_stock_data['allocated_stock_quantity'] . "' + temporary_stock_quantity where warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' and products_id = '" . (int)$products_id . "'");
                    $allocated_stock += $allocated_stock_data['allocated_stock_quantity'];
                }
            }
        }
        // Update products stock as summa of active warehouses stock (for now)
        if (strpos(\common\helpers\Inventory::normalizeInventoryId($products_id), '{') !== false) {
            if ($warehouse_id > 0 || $suppliers_id > 0) {
                $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wp.allocated_stock_quantity) as allocated_stock_quantity from " . TABLE_WAREHOUSES_PRODUCTS . " wp, " . TABLE_WAREHOUSES . " w where wp.warehouse_id = w.warehouse_id and w.status = '1' and wp.products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "'"));
                $warehouses_allocated_stock = $warehouses_stock['allocated_stock_quantity'];
            } else {
                $warehouses_allocated_stock = $allocated_stock;
            }
            tep_db_query("update " . TABLE_INVENTORY . " set allocated_stock_quantity = '" . (int)$warehouses_allocated_stock . "', warehouse_stock_quantity =  products_quantity + '" . (int)$warehouses_allocated_stock . "' + temporary_stock_quantity where products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "'");
        } else {
            if ($warehouse_id > 0 || $suppliers_id > 0) {
                $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wp.allocated_stock_quantity) as allocated_stock_quantity from " . TABLE_WAREHOUSES_PRODUCTS . " wp, " . TABLE_WAREHOUSES . " w where wp.warehouse_id = w.warehouse_id and w.status = '1' and wp.products_id = '" . (int)$products_id . "' and wp.prid = '" . (int)$products_id . "'"));
                $warehouses_allocated_stock = $warehouses_stock['allocated_stock_quantity'];
            } else {
                $warehouses_allocated_stock = $allocated_stock;
            }
            tep_db_query("update " . TABLE_PRODUCTS . " set allocated_stock_quantity = '" . (int)$warehouses_allocated_stock . "', warehouse_stock_quantity =  products_quantity + '" . (int)$warehouses_allocated_stock . "' + temporary_stock_quantity where products_id = '" . (int)$products_id . "'");
        }
        return $allocated_stock;
    }

    // $warehouse_id = 0 - all warehouses, $suppliers_id = 0 - all suppliers
    public static function get_temporary_stock_quantity($products_id, $warehouse_id = 0, $suppliers_id = 0) {
        return 0;
        $temporary_stock = 0;
        $warehouses_query = tep_db_query("select warehouse_id from " . TABLE_WAREHOUSES . " where status = '1' " . ($warehouse_id > 0 ? " and warehouse_id = '" . (int) $warehouse_id . "' " : '') . " order by sort_order, warehouse_name");
        while ($warehouses = tep_db_fetch_array($warehouses_query)) {
            $suppliers_query = tep_db_query("select suppliers_id from " . TABLE_SUPPLIERS . " where status = '1' " . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "' " : '') . " order by sort_order, suppliers_name");
            while ($suppliers = tep_db_fetch_array($suppliers_query)) {
                if (strpos(\common\helpers\Inventory::normalizeInventoryId($products_id), '{') !== false) {
                    $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(temporary_stock_quantity) as temporary_stock_quantity from " . self::getTemporaryStockTableName() . " where warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' and if(length(normalize_id) > 0, normalize_id, products_id) = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "' group by if(length(normalize_id) > 0, normalize_id, products_id)"));
                    tep_db_query("update " . TABLE_WAREHOUSES_PRODUCTS . " set temporary_stock_quantity = '" . (int)$temporary_stock_data['temporary_stock_quantity'] . "', warehouse_stock_quantity = products_quantity + allocated_stock_quantity + '" . (int)$temporary_stock_data['temporary_stock_quantity'] . "' where warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' and products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "'");
                    $temporary_stock += $temporary_stock_data['temporary_stock_quantity'];
                } else {
                    $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(temporary_stock_quantity) as temporary_stock_quantity from " . self::getTemporaryStockTableName() . " where warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' and prid = '" . (int)$products_id . "' group by prid"));
                    tep_db_query("update " . TABLE_WAREHOUSES_PRODUCTS . " set temporary_stock_quantity = '" . (int)$temporary_stock_data['temporary_stock_quantity'] . "', warehouse_stock_quantity = products_quantity + allocated_stock_quantity + '" . (int)$temporary_stock_data['temporary_stock_quantity'] . "' where warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' and products_id = '" . (int)$products_id . "'");
                    $temporary_stock += $temporary_stock_data['temporary_stock_quantity'];
                }
            }
        }
        // Update products stock as summa of active warehouses stock (for now)
        if (strpos(\common\helpers\Inventory::normalizeInventoryId($products_id), '{') !== false) {
            if ($warehouse_id > 0 || $suppliers_id > 0) {
                $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wp.temporary_stock_quantity) as temporary_stock_quantity from " . TABLE_WAREHOUSES_PRODUCTS . " wp, " . TABLE_WAREHOUSES . " w where wp.warehouse_id = w.warehouse_id and w.status = '1' and wp.products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "'"));
                $warehouses_temporary_stock = $warehouses_stock['temporary_stock_quantity'];
            } else {
                $warehouses_temporary_stock = $temporary_stock;
            }
            tep_db_query("update " . TABLE_INVENTORY . " set temporary_stock_quantity = '" . (int)$warehouses_temporary_stock . "', warehouse_stock_quantity = products_quantity + allocated_stock_quantity + '" . (int)$warehouses_temporary_stock . "' where products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "'");
        } else {
            if ($warehouse_id > 0 || $suppliers_id > 0) {
                $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wp.temporary_stock_quantity) as temporary_stock_quantity from " . TABLE_WAREHOUSES_PRODUCTS . " wp, " . TABLE_WAREHOUSES . " w where wp.warehouse_id = w.warehouse_id and w.status = '1' and wp.products_id = '" . (int)$products_id . "' and wp.prid = '" . (int)$products_id . "'"));
                $warehouses_temporary_stock = $warehouses_stock['temporary_stock_quantity'];
            } else {
                $warehouses_temporary_stock = $temporary_stock;
            }
            tep_db_query("update " . TABLE_PRODUCTS . " set temporary_stock_quantity = '" . (int)$warehouses_temporary_stock . "', warehouse_stock_quantity = products_quantity + allocated_stock_quantity + '" . (int)$warehouses_temporary_stock . "' where products_id = '" . (int)$products_id . "'");
        }
        return $temporary_stock;
    }

    public static function update_sum_of_inventory_quantity($products_id, $warehouse_id = 0, $suppliers_id = 0) {
        $warehouses_query = tep_db_query("select warehouse_id from " . TABLE_WAREHOUSES . " where status = '1' " . ($warehouse_id > 0 ? " and warehouse_id = '" . (int) $warehouse_id . "' " : '') . " order by sort_order, warehouse_name");
        while ($warehouses = tep_db_fetch_array($warehouses_query)) {
            $suppliers_query = tep_db_query("select suppliers_id from " . TABLE_SUPPLIERS . " where status = '1' " . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "' " : '') . " order by sort_order, suppliers_name");
            while ($suppliers = tep_db_fetch_array($suppliers_query)) {
                $all_inventory_stock = tep_db_fetch_array(tep_db_query("select count(*) as inventory_count, sum(products_quantity) as products_quantity from " . TABLE_WAREHOUSES_PRODUCTS . " where warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' and products_id != '" . (int) \common\helpers\Inventory::get_prid($products_id) . "' and prid = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "'"));
                if ($all_inventory_stock['inventory_count'] > 0) {
                    $check_warehouse = tep_db_fetch_array(tep_db_query("select count(*) as stock_exists from " . TABLE_WAREHOUSES_PRODUCTS . " where warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' and products_id = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "' and prid = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "'"));
                    if ($check_warehouse['stock_exists']) {
                        tep_db_query("update " . TABLE_WAREHOUSES_PRODUCTS . " set products_quantity = '" . (int) $all_inventory_stock['products_quantity'] . "' where warehouse_id = '" . (int) $warehouses['warehouse_id'] . "' and suppliers_id = '" . (int) $suppliers['suppliers_id'] . "' and products_id = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "' and prid = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "'");
                    } else {
                        $data = tep_db_fetch_array(tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'"));
                        tep_db_query("insert into " . TABLE_WAREHOUSES_PRODUCTS . " set products_model = '" . tep_db_input($data['products_model']) . "', products_quantity = '" . (int) $all_inventory_stock['products_quantity'] . "', warehouse_id = '" . (int) $warehouses['warehouse_id'] . "', suppliers_id = '" . (int) $suppliers['suppliers_id'] . "', products_id = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "', prid = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "'");
                    }
                }
            }
        }
    }

    public static function update_customers_temporary_stock_quantity($products_id, $qty) {
        $original_products_id = $products_id;
        $products_id = \common\helpers\Inventory::normalizeInventoryId($products_id);
        if (defined('STOCK_LIMITED') && defined('TEMPORARY_STOCK_ENABLE') && STOCK_LIMITED == 'true' && TEMPORARY_STOCK_ENABLE == 'true') {
            $current_platform_id = \Yii::$app->get('platform')->config()->getId();
            $platformWarehousedOrdered = \Yii::$app->get('platform')->config()->assignedWarehouses();
            $suppliers_id = 0; // all suppliers
            if ($ext = \common\helpers\Extensions::isAllowed('SupplierPurchase')) {
                $suppliers_id = $ext::getSupplierFromUprid($original_products_id);
            }
            $active_suppliers = \common\helpers\Suppliers::orderedActiveIds();
            if ( $suppliers_id>0 ) {
                if ( in_array((int)$suppliers_id, $active_suppliers) ){
                    $active_suppliers = [(int)$suppliers_id];
                }else{
                    $active_suppliers = [];
                }
            }

            $temporary_info = static::getCustomerTemporaryStockInfo($products_id, $original_products_id);
            $temporary_stock_quantity = 0;
            $_per_warehouse_supplier_temporary = [];
            foreach ($temporary_info as $temporary_info_row){
                $temporary_stock_quantity += intval($temporary_info_row['temporary_stock_quantity']);
                $whs_key = (int)$temporary_info_row['warehouse_id'].'|'.(int)$temporary_info_row['suppliers_id'];
                if (!isset($_per_warehouse_supplier_temporary[$whs_key])) {
                    $_per_warehouse_supplier_temporary[$whs_key] = 0;
                }
                $_per_warehouse_supplier_temporary[$whs_key] += intval($temporary_info_row['temporary_stock_quantity']);
            }

            if ($qty > $temporary_stock_quantity) {
                $groupedAvailableQuantity = [];
                foreach (static::getQuantityInfoData($products_id) as $productQuantityData ){
                    $whs_key = (int)$productQuantityData['warehouse_id'].'|'.(int)$productQuantityData['suppliers_id'];
                    if (!isset($groupedAvailableQuantity[$whs_key])) {
                        $groupedAvailableQuantity[$whs_key] = 0;
                    }
                    $groupedAvailableQuantity[$whs_key] += $productQuantityData['products_quantity'];
                }
                // increase temporary stock
                $update_qty = $qty - $temporary_stock_quantity;
                foreach ($platformWarehousedOrdered as $warehouseId){ // update warehouses by sort order ascending
                    foreach ($active_suppliers as $supplierId) {
                        $whs_key = (int)$warehouseId.'|'.(int)$supplierId;
                        if ($update_qty > 0) {
                            $warehouse_temporary_stock_quantity = isset($_per_warehouse_supplier_temporary[$whs_key])?$_per_warehouse_supplier_temporary[$whs_key]:0;
                            $available_products_quantity = isset($groupedAvailableQuantity[$whs_key])?$groupedAvailableQuantity[$whs_key]:0;
                            if ($available_products_quantity > 0) {
                                if ($update_qty <= $available_products_quantity) {
                                    \common\helpers\Product::update_customers_temporary_stock_quantity($products_id, $warehouse_temporary_stock_quantity + $update_qty, $warehouseId, $supplierId, false, $original_products_id);
                                    $update_qty = 0; break;
                                } else {
                                    \common\helpers\Product::update_customers_temporary_stock_quantity($products_id, $warehouse_temporary_stock_quantity + $available_products_quantity, $warehouseId, $supplierId, false, $original_products_id);
                                    $update_qty -= $available_products_quantity;
                                }
                            }
                        } else {
                            break;
                        }
                    }
                }
// {{           // if no available stock left - update default warehouse & supplier
                if ($update_qty > 0) {
                    $default_warehouse_id = self::get_default_warehouse();
                    $default_suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
                    $warehouse_temporary_stock_quantity = self::get_customers_temporary_stock_quantity($products_id, $default_warehouse_id, $default_suppliers_id, $original_products_id);
                    \common\helpers\Product::update_customers_temporary_stock_quantity($products_id, $warehouse_temporary_stock_quantity + $update_qty, $default_warehouse_id, $default_suppliers_id, true, $original_products_id);
                }
// }}
            } elseif ($qty < $temporary_stock_quantity) {
                // decrease temporary stock
                $update_qty = $temporary_stock_quantity - $qty;
// {{           // if not available stock > 0 - update default warehouse & supplier
                $default_warehouse_id = self::get_default_warehouse();
                $default_suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
                $warehouse_temporary_not_available_quantity = self::get_customers_not_available_quantity($products_id, $default_warehouse_id, $default_suppliers_id);
                if ($warehouse_temporary_not_available_quantity > 0) {
                    $warehouse_temporary_stock_quantity = self::get_customers_temporary_stock_quantity($products_id, $default_warehouse_id, $default_suppliers_id, $original_products_id);
                    if ($warehouse_temporary_stock_quantity > 0) {
                        if ($update_qty <= $warehouse_temporary_not_available_quantity) {
                            \common\helpers\Product::update_customers_temporary_stock_quantity($products_id, $warehouse_temporary_stock_quantity - $update_qty, $default_warehouse_id, $default_suppliers_id, true, $original_products_id);
                            $update_qty = 0;
                        } else {
                            \common\helpers\Product::update_customers_temporary_stock_quantity($products_id, $warehouse_temporary_stock_quantity - $warehouse_temporary_not_available_quantity, $default_warehouse_id, $default_suppliers_id, true, $original_products_id);
                            $update_qty -= $warehouse_temporary_not_available_quantity;
                        }
                    }
                }
// }}
                $temporary_info = static::getCustomerTemporaryStockInfo($products_id, $original_products_id);
                $_per_warehouse_supplier_temporary = [];
                foreach ($temporary_info as $temporary_info_row){
                    $whs_key = (int)$temporary_info_row['warehouse_id'].'|'.(int)$temporary_info_row['suppliers_id'];
                    if (!isset($_per_warehouse_supplier_temporary[$whs_key])) {
                        $_per_warehouse_supplier_temporary[$whs_key] = 0;
                    }
                    $_per_warehouse_supplier_temporary[$whs_key] += intval($temporary_info_row['temporary_stock_quantity']);
                }

                foreach (array_reverse($platformWarehousedOrdered) as $warehouseId){
                    foreach (array_reverse($active_suppliers) as $supplierId){
                        if ($update_qty > 0) {
                            $whs_key = (int)$warehouseId.'|'.(int)$supplierId;
                            $warehouse_temporary_stock_quantity = isset($_per_warehouse_supplier_temporary[$whs_key])?$_per_warehouse_supplier_temporary[$whs_key]:0;
                            if ($warehouse_temporary_stock_quantity > 0) {
                                if ($update_qty <= $warehouse_temporary_stock_quantity) {
                                    \common\helpers\Product::update_customers_temporary_stock_quantity($products_id, $warehouse_temporary_stock_quantity - $update_qty, $warehouseId, $supplierId, false, $original_products_id);
                                    $update_qty = 0; break;
                                } else {
                                    \common\helpers\Product::update_customers_temporary_stock_quantity($products_id, 0, $warehouseId, $supplierId, false, $original_products_id);
                                    $update_qty -= $warehouse_temporary_stock_quantity;
                                }
                            }
                        } else {
                            break;
                        }
                    }
                }
            }
        }
    }

    // $warehouse_id = 0 - all warehouses, $suppliers_id = 0 - all suppliers
    public static function get_customers_temporary_stock_quantity($products_id, $warehouse_id = 0, $suppliers_id = 0, $original_products_id = '') {
        $products_id = \common\helpers\Inventory::normalizeInventoryId($products_id);
        $original_products_id = trim($original_products_id);
        //$the_session_id = tep_session_id();
        if (\Yii::$app->id=='app-console') {
          $the_session_id = \Yii::$app->storage->get('guid');
        } else {
          $the_session_id = tep_session_id();
        }
        if (!\Yii::$app->user->isGuest) {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(temporary_stock_quantity) as temporary_stock_quantity from " . self::getTemporaryStockTableName() . " where (customers_id = '" . (int)\Yii::$app->user->getId() . "' or (customers_id = '0' and session_id = '" . tep_db_input($the_session_id) . "'))" . ($original_products_id != '' ? (" and child_id = '" . tep_db_input($original_products_id) . "'") : '') . " and products_id = '" . tep_db_input($products_id) . "'" . ($warehouse_id > 0 ? " and warehouse_id = '" . (int) $warehouse_id . "'" : '') . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "'" : '')));
        } else {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(temporary_stock_quantity) as temporary_stock_quantity from " . self::getTemporaryStockTableName() . " where session_id = '" . tep_db_input($the_session_id) . "'" . ($original_products_id != '' ? (" and child_id = '" . tep_db_input($original_products_id) . "'") : '') . " and products_id = '" . tep_db_input($products_id) . "'" . ($warehouse_id > 0 ? " and warehouse_id = '" . (int) $warehouse_id . "'" : '') . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "'" : '')));
        }
        return $temporary_stock_data['temporary_stock_quantity'];
    }

    protected static function getCustomerTemporaryStockInfo($products_id, $original_products_id = '')
    {
        $temporary_stock_data = [];

        $original_products_id = trim($original_products_id);
        $products_id = \common\helpers\Inventory::normalizeInventoryId($products_id);
        //$session_id = tep_session_id();
        if (\Yii::$app->id=='app-console') {
          $the_session_id = \Yii::$app->storage->get('guid');
        } else {
          $the_session_id = tep_session_id();
        }

        if (\Yii::$app->user->isGuest) {
            $searchCustomerCondition = "session_id = '" . tep_db_input($the_session_id) . "'";
        }else{
            $searchCustomerCondition = "(customers_id = '" . (int)\Yii::$app->user->getId() . "' or (customers_id = '0' and session_id = '" . tep_db_input($the_session_id) . "'))";
        }
        $get_temporary_stock_r = tep_db_query(
            "SELECT temporary_stock_quantity, warehouse_id, suppliers_id, location_id ".
            "FROM ". self::getTemporaryStockTableName() . " ".
            "WHERE products_id = '" . tep_db_input($products_id) . "'"
                . ($original_products_id != '' ? (" AND child_id = '" . tep_db_input($original_products_id) . "'") : '') .
            " AND {$searchCustomerCondition}"
        );
        if ( tep_db_num_rows($get_temporary_stock_r)>0 ){
            while($temporary_stock = tep_db_fetch_array($get_temporary_stock_r)){
                $temporary_stock_data[] = $temporary_stock;
            }
        }

        return $temporary_stock_data;
    }

    public static function remove_customers_temporary_stock_quantity($products_id) {
        self::update_customers_temporary_stock_quantity($products_id, 0);
    }

    public static function update_stock_of_order($orders_id, $products_id, $qty, $warehouse_id = 0, $suppliers_id = 0, $platform_id = 0) {
        global $login_id;
        if ($platform_id > 0) {
            $current_platform_id = $platform_id;
        } elseif (defined('PLATFORM_ID') && PLATFORM_ID > 0) {
            $current_platform_id = PLATFORM_ID;
        } else {
            $current_platform_id = \common\classes\platform::defaultId();
        }
        /*if ($warehouse_id == 0) {
            $warehouse_id = \common\helpers\Warehouses::get_default_warehouse();
        }*/
        //$suppliers_id = 0; // all suppliers
        if (($ext = \common\helpers\Extensions::isAllowed('SupplierPurchase')) && $suppliers_id == 0) {
            $suppliers_id = $ext::getSupplierFromUprid($products_id);
        }
        $products_id = \common\helpers\Inventory::normalizeInventoryId($products_id);
// {{   // needed to calculate allocated stock
        tep_db_query("update " . TABLE_ORDERS . " set stock_updated = '1' where orders_id = '" . (int) $orders_id . "'");
// }}
        $orders_products_quantity = self::get_orders_products_quantity($products_id, $orders_id);
        if ($qty > $orders_products_quantity) {
            // increase orders products quantity
            $update_qty = $qty - $orders_products_quantity;

            if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
                $warehouseIdCheck = $extScl::updateUpdateStockOfOrder($products_id, $current_platform_id);
                if ($warehouseIdCheck !== false) {
                    $warehouse_id = $warehouseIdCheck;
                }
                unset($warehouseIdCheck);
            }

            $warehousesIds = [];
            if ($warehouse_id > 0) {
                //$warehouses_query = tep_db_query("select warehouse_id from " . TABLE_WAREHOUSES . " where status = '1' and warehouse_id='" . $warehouse_id . "'");
                $warehousesIds[] = $warehouse_id;
            } else {
                $warehouses_query = tep_db_query("select w.warehouse_id from " . TABLE_WAREHOUSES . " w left join " . TABLE_WAREHOUSES_TO_PLATFORMS . " w2p on w.warehouse_id = w2p.warehouse_id and w2p.platform_id = '" . (int)$current_platform_id . "' where ifnull(w2p.status, w.status) = '1' order by ifnull(w2p.sort_order, w.sort_order), w.warehouse_name");
                while ($warehouses = tep_db_fetch_array($warehouses_query)) {
                    $warehousesIds[] = $warehouses['warehouse_id'];
                }

                /**
                 * @var $ext \common\extensions\WarehousePriority\WarehousePriority
                 */
                if ($ext = \common\helpers\Extensions::isAllowed('WarehousePriority')) {
                    $orderedProduct = [
                        'products_id' => $products_id,
                        'products_quantity' => $update_qty,
                        ];
                    $prefferedWarehouseIds = $ext::getInstance()->getPreferredWarehouseId($orderedProduct, $platform_id);
                    if (count($prefferedWarehouseIds) > 0) {
                        $warehousesIds = $prefferedWarehouseIds;
                    }
                }

            }

            $suppliersIds = [];
            if ($suppliers_id > 0) {
                $suppliersIds[] = $suppliers_id;
            } else {
                $suppliers_query = tep_db_query("select suppliers_id from " . TABLE_SUPPLIERS . " where status = '1' " . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "'" : '') . " order by sort_order, suppliers_name");
                while ($suppliers = tep_db_fetch_array($suppliers_query)) { // update suppliers by sort order ascending
                    $suppliersIds[] = $suppliers['suppliers_id'];
                }
                $calculatedPrices = [];//TODO /lib/backend/controllers/CategoriesController.php actionAutoSupplierPrice
                if (count($calculatedPrices)>0) {
                    if ($ext = \common\helpers\Acl::checkExtensionAllowed('SupplierPriority', 'getInstance')) {
                        $preferredSuppliersIds = $ext::getInstance()->getPreferredSupplierId($calculatedPrices);
                        if (count($preferredSuppliersIds) > 0) {
                            $suppliersIds = $preferredSuppliersIds;
                        }
                    }
                }
            }

            foreach ($warehousesIds as $warehousesId) {
                foreach ($suppliersIds as $suppliersId) {
                    if ($update_qty > 0) {
                        $available_products_quantity = self::get_products_quantity($products_id, $warehousesId, $suppliersId);
                        if ($available_products_quantity > 0) {
                            if ($update_qty <= $available_products_quantity) {
                                \common\helpers\Product::log_stock_history_before_update($products_id, $update_qty, '-', ['warehouse_id' => $warehousesId, 'suppliers_id' => $suppliersId, 'comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $orders_id]);
                                \common\helpers\Product::update_stock($products_id, 0, $update_qty, $warehousesId, $suppliersId, $current_platform_id);
                                self::update_orders_products_quantity($products_id, $orders_id, $warehousesId, $update_qty, '+', $suppliersId);
                                self::get_allocated_stock_quantity($products_id, $warehousesId, $suppliersId);
                                $update_qty = 0; break;
                            } else {
                                \common\helpers\Product::log_stock_history_before_update($products_id, $available_products_quantity, '-', ['warehouse_id' => $warehousesId, 'suppliers_id' => $suppliersId, 'comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $orders_id]);
                                \common\helpers\Product::update_stock($products_id, 0, $available_products_quantity, $warehousesId, $suppliersId, $current_platform_id);
                                self::update_orders_products_quantity($products_id, $orders_id, $warehousesId, $available_products_quantity, '+', $suppliersId);
                                self::get_allocated_stock_quantity($products_id, $warehousesId, $suppliersId);
                                $update_qty -= $available_products_quantity;
                            }
                        }
                    } else {
                        break;
                    }
                }
            }
// {{       // if no available stock left - update default warehouse & supplier
            if ($update_qty > 0) {
                $default_warehouse_id = self::get_default_warehouse();
                $default_suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
                \common\helpers\Product::log_stock_history_before_update($products_id, $update_qty, '-', ['warehouse_id' => $default_warehouse_id, 'suppliers_id' => $default_suppliers_id, 'comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $orders_id]);
                \common\helpers\Product::update_stock($products_id, 0, $update_qty, $default_warehouse_id, $default_suppliers_id);
                self::update_orders_products_quantity($products_id, $orders_id, $default_warehouse_id, $update_qty, '+', $default_suppliers_id, true);
                self::get_allocated_stock_quantity($products_id, $default_warehouse_id, $default_suppliers_id);
            }
// }}
        } elseif ($qty < $orders_products_quantity) {
            // decrease orders products quantity
            $update_qty = $orders_products_quantity - $qty;
// {{       // if not available stock > 0 - update default warehouse & supplier
            $default_warehouse_id = self::get_default_warehouse();
            $default_suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
            $warehouse_orders_not_available_quantity = self::get_orders_not_available_quantity($products_id, $orders_id, $default_warehouse_id, $default_suppliers_id);
            if ($warehouse_orders_not_available_quantity > 0) {
                $warehouse_orders_products_quantity = self::get_orders_products_quantity($products_id, $orders_id, $default_warehouse_id, $default_suppliers_id);
                if ($warehouse_orders_products_quantity > 0) {
                    if ($update_qty <= $warehouse_orders_not_available_quantity) {
                        \common\helpers\Product::log_stock_history_before_update($products_id, $update_qty, '+', ['warehouse_id' => $default_warehouse_id, 'suppliers_id' => $default_suppliers_id, 'comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $orders_id]);
                        \common\helpers\Product::update_stock($products_id, $update_qty, 0, $default_warehouse_id, $default_suppliers_id);
                        self::update_orders_products_quantity($products_id, $orders_id, $default_warehouse_id, $update_qty, '-', $default_suppliers_id, true);
                        self::get_allocated_stock_quantity($products_id, $default_warehouse_id, $default_suppliers_id);
                        $update_qty = 0;
                    } else {
                        \common\helpers\Product::log_stock_history_before_update($products_id, $warehouse_orders_not_available_quantity, '+', ['warehouse_id' => $default_warehouse_id, 'suppliers_id' => $default_suppliers_id, 'comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $orders_id]);
                        \common\helpers\Product::update_stock($products_id, $warehouse_orders_not_available_quantity, 0, $default_warehouse_id, $default_suppliers_id);
                        self::update_orders_products_quantity($products_id, $orders_id, $default_warehouse_id, $warehouse_orders_not_available_quantity, '-', $default_suppliers_id, true);
                        self::get_allocated_stock_quantity($products_id, $default_warehouse_id, $default_suppliers_id);
                        $update_qty -= $warehouse_orders_not_available_quantity;
                    }
                }
            }
// }}
            //$warehouses_query = tep_db_query("select warehouse_id from " . TABLE_WAREHOUSES . " where status = '1' order by sort_order desc, warehouse_name desc");
            $warehouses_query = tep_db_query("select w.warehouse_id from " . TABLE_WAREHOUSES . " w left join " . TABLE_WAREHOUSES_TO_PLATFORMS . " w2p on w.warehouse_id = w2p.warehouse_id and w2p.platform_id = '" . (int)$current_platform_id . "' where ifnull(w2p.status, w.status) = '1' order by ifnull(w2p.sort_order, w.sort_order) desc, w.warehouse_name desc");
            while ($warehouses = tep_db_fetch_array($warehouses_query)) { // update warehouses by sort order descending
                $suppliers_query = tep_db_query("select suppliers_id from " . TABLE_SUPPLIERS . " where status = '1' " . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "'" : '') . " order by sort_order desc, suppliers_name desc");
                while ($suppliers = tep_db_fetch_array($suppliers_query)) { // update suppliers by sort order descending
                    if ($update_qty > 0) {
                        $warehouse_orders_products_quantity = self::get_orders_products_quantity($products_id, $orders_id, $warehouses['warehouse_id'], $suppliers['suppliers_id']);
                        if ($warehouse_orders_products_quantity > 0) {
                            if ($update_qty <= $warehouse_orders_products_quantity) {
                                \common\helpers\Product::log_stock_history_before_update($products_id, $update_qty, '+', ['warehouse_id' => $warehouses['warehouse_id'], 'suppliers_id' => $suppliers['suppliers_id'], 'comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $orders_id]);
                                \common\helpers\Product::update_stock($products_id, $update_qty, 0, $warehouses['warehouse_id'], $suppliers['suppliers_id']);
                                self::update_orders_products_quantity($products_id, $orders_id, $warehouses['warehouse_id'], $update_qty, '-', $suppliers['suppliers_id']);
                                self::get_allocated_stock_quantity($products_id, $warehouses['warehouse_id'], $suppliers['suppliers_id']);
                                $update_qty = 0; break;
                            } else {
                                \common\helpers\Product::log_stock_history_before_update($products_id, $warehouse_orders_products_quantity, '+', ['warehouse_id' => $warehouses['warehouse_id'], 'suppliers_id' => $suppliers['suppliers_id'], 'comments' => TEXT_ORDER_STOCK_UPDATE, 'admin_id' => $login_id, 'orders_id' => $orders_id]);
                                \common\helpers\Product::update_stock($products_id, $warehouse_orders_products_quantity, 0, $warehouses['warehouse_id'], $suppliers['suppliers_id']);
                                self::update_orders_products_quantity($products_id, $orders_id, $warehouses['warehouse_id'], $warehouse_orders_products_quantity, '-', $suppliers['suppliers_id']);
                                self::get_allocated_stock_quantity($products_id, $warehouses['warehouse_id'], $suppliers['suppliers_id']);
                                $update_qty -= $warehouse_orders_products_quantity;
                            }
                        }
                    } else {
                        break;
                    }
                }
            }
        }
    }

    public static function relocateQty($uprid, $from_warehouse_id, $to_warehouse_id, $qty, $supplier_id=0, $from_location_id=0, $to_location_id=0)
    {
        $availableQty = \common\helpers\Warehouses::get_products_quantity($uprid, $from_warehouse_id, $supplier_id);
        if ( $qty>$availableQty ) {
            $qty = $availableQty;
        }
        $qty = max(0, $qty);
        if ( $qty>0 ) {
            $TEXT_AUTO_STOCK_RELOCATE = defined('TEXT_AUTO_STOCK_RELOCATE')?TEXT_AUTO_STOCK_RELOCATE:'Auto Warehouses Relocate from %s to %s';
            $comments = sprintf($TEXT_AUTO_STOCK_RELOCATE, \common\helpers\Warehouses::get_warehouse_name($from_warehouse_id), \common\helpers\Warehouses::get_warehouse_name($to_warehouse_id));
            $parameters = [
                'comments' => $comments
            ];
            \common\helpers\Warehouses::update_products_quantity($uprid, $from_warehouse_id, $qty, '-', $supplier_id, $from_location_id, $parameters);
            \common\helpers\Warehouses::update_products_quantity($uprid, $to_warehouse_id, $qty, '+', $supplier_id, $to_location_id, $parameters);
        }
    }

    // $warehouse_id = 0 - all warehouses, $suppliers_id = 0 - all suppliers
    public static function get_orders_products_quantity($products_id, $orders_id, $warehouse_id = 0, $suppliers_id = 0) {
        return 0;
        if (strpos(\common\helpers\Inventory::normalizeInventoryId($products_id), '{') !== false) {
            $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wop.products_quantity) as products_quantity from " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " wop, " . TABLE_WAREHOUSES . " w where wop.warehouse_id = w.warehouse_id and w.status = '1' " . ($warehouse_id > 0 ? " and w.warehouse_id = '" . (int) $warehouse_id . "' " : '') . ($suppliers_id > 0 ? " and wop.suppliers_id = '" . (int) $suppliers_id . "' " : '') . " and wop.orders_id = '" . (int) $orders_id . "' and wop.uprid = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "' and wop.template_uprid = '" . tep_db_input($products_id) . "' and wop.products_id = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "'"));
        } else {
            $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wop.products_quantity) as products_quantity from " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " wop, " . TABLE_WAREHOUSES . " w where wop.warehouse_id = w.warehouse_id and w.status = '1' " . ($warehouse_id > 0 ? " and w.warehouse_id = '" . (int) $warehouse_id . "' " : '') . ($suppliers_id > 0 ? " and wop.suppliers_id = '" . (int) $suppliers_id . "' " : '') . " and wop.orders_id = '" . (int) $orders_id . "' and wop.uprid = '" . (int)$products_id . "' and wop.template_uprid = '" . tep_db_input($products_id) . "' and wop.products_id = '" . (int)$products_id . "'"));
        }
        return $warehouses_stock['products_quantity'];
    }

    public static function update_orders_products_quantity($products_id, $orders_id, $warehouse_id, $qty, $qty_prefix, $suppliers_id = 0, $not_available = false) {
        return 0;
        if ($qty_prefix != '-') $qty_prefix = '+';
        if ($suppliers_id == 0) $suppliers_id = \common\helpers\Suppliers::getDefaultSupplierId();
        if (strpos(\common\helpers\Inventory::normalizeInventoryId($products_id), '{') !== false) {
            $check_warehouse = tep_db_fetch_array(tep_db_query("select count(*) as stock_exists from " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " where warehouse_id = '" . (int) $warehouse_id . "' and suppliers_id = '" . (int) $suppliers_id . "' and orders_id = '" . (int) $orders_id . "' and uprid = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "' and template_uprid = '" . tep_db_input($products_id) . "' and products_id = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "'"));
            if ($check_warehouse['stock_exists']) {
                tep_db_query("update " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " set products_quantity = products_quantity " . $qty_prefix . (int) $qty . ($not_available ? ", not_available_quantity = not_available_quantity " . $qty_prefix . (int) $qty : '') . " where warehouse_id = '" . (int) $warehouse_id . "' and suppliers_id = '" . (int) $suppliers_id . "' and orders_id = '" . (int) $orders_id . "' and uprid = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "' and template_uprid = '" . tep_db_input($products_id) . "' and products_id = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "'");
            } else {
                $data = tep_db_fetch_array(tep_db_query("select products_model from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "' and prid = '" . (int)\common\helpers\Inventory::get_prid($products_id) . "'"));
                tep_db_query("insert into " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " set products_model = '" . tep_db_input($data['products_model']) . "', products_quantity = " . $qty_prefix . (int) $qty . ($not_available ? ", not_available_quantity = " . $qty_prefix . (int) $qty : '') . ", warehouse_id = '" . (int) $warehouse_id . "', suppliers_id = '" . (int) $suppliers_id . "', orders_id = '" . (int) $orders_id . "', uprid = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "', template_uprid = '" . tep_db_input($products_id) . "', products_id = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "'");
            }
        } else {
            $check_warehouse = tep_db_fetch_array(tep_db_query("select count(*) as stock_exists from " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " where warehouse_id = '" . (int) $warehouse_id . "' and suppliers_id = '" . (int) $suppliers_id . "' and orders_id = '" . (int) $orders_id . "' and uprid = '" . (int) $products_id . "' and template_uprid = '" . tep_db_input($products_id) . "' and products_id = '" . (int) $products_id . "'"));
            if ($check_warehouse['stock_exists']) {
                tep_db_query("update " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " set products_quantity = products_quantity " . $qty_prefix . (int) $qty . ($not_available ? ", not_available_quantity = not_available_quantity " . $qty_prefix . (int) $qty : '') . " where warehouse_id = '" . (int) $warehouse_id . "' and suppliers_id = '" . (int) $suppliers_id . "' and orders_id = '" . (int) $orders_id . "' and uprid = '" . (int) $products_id . "' and template_uprid = '" . tep_db_input($products_id) . "' and products_id = '" . (int) $products_id . "'");
            } else {
                $data = tep_db_fetch_array(tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'"));
                tep_db_query("insert into " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " set products_model = '" . tep_db_input($data['products_model']) . "', products_quantity = " . $qty_prefix . (int) $qty . ($not_available ? ", not_available_quantity = " . $qty_prefix . (int) $qty : '') . ", warehouse_id = '" . (int) $warehouse_id . "', suppliers_id = '" . (int) $suppliers_id . "', orders_id = '" . (int) $orders_id . "', uprid = '" . (int) $products_id . "', template_uprid = '" . tep_db_input($products_id) . "', products_id = '" . (int) $products_id . "'");
            }
        }
    }

    // $warehouse_id = 0 - all warehouses, $suppliers_id = 0 - all suppliers
    public static function get_customers_not_available_quantity($products_id, $warehouse_id = 0, $suppliers_id = 0) {

        //$the_session_id = tep_session_id();
        if (\Yii::$app->id=='app-console') {
          $the_session_id = \Yii::$app->storage->get('guid');
        } else {
          $the_session_id = tep_session_id();
        }
        if (!\Yii::$app->user->isGuest) {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(not_available_quantity) as not_available_quantity from " . self::getTemporaryStockTableName() . " where (customers_id = '" . (int)\Yii::$app->user->getId() . "' or (customers_id = '0' and session_id = '" . tep_db_input($the_session_id) . "')) and products_id = '" . tep_db_input($products_id) . "'" . ($warehouse_id > 0 ? " and warehouse_id = '" . (int) $warehouse_id . "'" : '') . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "'" : '')));
        } else {
            $temporary_stock_data = tep_db_fetch_array(tep_db_query("select sum(not_available_quantity) as not_available_quantity from " . self::getTemporaryStockTableName() . " where session_id = '" . tep_db_input($the_session_id) . "' and products_id = '" . tep_db_input($products_id) . "'" . ($warehouse_id > 0 ? " and warehouse_id = '" . (int) $warehouse_id . "'" : '') . ($suppliers_id > 0 ? " and suppliers_id = '" . (int) $suppliers_id . "'" : '')));
        }
        return $temporary_stock_data['not_available_quantity'];
    }

    // $warehouse_id = 0 - all warehouses, $suppliers_id = 0 - all suppliers
    public static function get_orders_not_available_quantity($products_id, $orders_id, $warehouse_id = 0, $suppliers_id = 0) {
        return 0;
        if (strpos(\common\helpers\Inventory::normalizeInventoryId($products_id), '{') !== false) {
            $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wop.not_available_quantity) as not_available_quantity from " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " wop, " . TABLE_WAREHOUSES . " w where wop.warehouse_id = w.warehouse_id and w.status = '1' " . ($warehouse_id > 0 ? " and w.warehouse_id = '" . (int) $warehouse_id . "' " : '') . ($suppliers_id > 0 ? " and wop.suppliers_id = '" . (int) $suppliers_id . "' " : '') . " and wop.orders_id = '" . (int) $orders_id . "' and wop.uprid = '" . tep_db_input(\common\helpers\Inventory::normalizeInventoryId($products_id)) . "' and wop.template_uprid = '" . tep_db_input($products_id) . "' and wop.products_id = '" . (int) \common\helpers\Inventory::get_prid($products_id) . "'"));
        } else {
            $warehouses_stock = tep_db_fetch_array(tep_db_query("select sum(wop.not_available_quantity) as not_available_quantity from " . TABLE_WAREHOUSES_ORDERS_PRODUCTS . " wop, " . TABLE_WAREHOUSES . " w where wop.warehouse_id = w.warehouse_id and w.status = '1' " . ($warehouse_id > 0 ? " and w.warehouse_id = '" . (int) $warehouse_id . "' " : '') . ($suppliers_id > 0 ? " and wop.suppliers_id = '" . (int) $suppliers_id . "' " : '') . " and wop.orders_id = '" . (int) $orders_id . "' and wop.uprid = '" . (int)$products_id . "' and wop.template_uprid = '" . tep_db_input($products_id) . "' and wop.products_id = '" . (int)$products_id . "'"));
        }
        return $warehouses_stock['not_available_quantity'];
    }

    /**
     * Get Warehouse Products for specific enabled Platform
     * @param mixed $uProductId Product Id or Product Inventory Id
     * @param mixed $platformId Platform Id. If false - calculate for all platforms; if equals 0 - front-end mode; if greater than 0 - calculate for specific platform
     * @param boolean $asArray switching return type between array of arrays or array of instances of WarehousesProducts
     * @return array array of mixed depending on $asArray parameter
     */
    public static function getProductArray($uProductId = 0, $platformId = false, $asArray = true)
    {
        $return = [];
        $uProductId = \common\helpers\Inventory::normalizeInventoryId($uProductId);
        if ($platformId !== false) {
            if ($platformId <= 0) {
                if (defined('PLATFORM_ID') AND (int)PLATFORM_ID > 0) {
                    $platformId = PLATFORM_ID;
                } elseif (\common\classes\platform::defaultId() > 0) {
                    $platformId = \common\classes\platform::defaultId();
                } else {
                    $platformId = \common\classes\platform::currentId();
                }
            }
            $platformId = (int)$platformId;
        }
        $supplierIdArray = \common\helpers\Product::getSupplierIdPriorityArray($uProductId);
        $warehouseProductQuery = \common\models\WarehousesProducts::find()
            ->andWhere(['products_id' => $uProductId])
            ->andWhere(['prid' => (int)$uProductId])
            ->andWhere(['IN', 'suppliers_id', $supplierIdArray])
            ->asArray($asArray);
        unset($supplierIdArray);
        if ($platformId !== false) {
            $warehouseIdArray = \common\helpers\Product::getWarehouseIdPriorityArray($uProductId, 1, $platformId);
            $warehouseProductQuery->andWhere(['IN', 'warehouse_id', $warehouseIdArray]);
            unset($warehouseIdArray);
        }
        foreach ($warehouseProductQuery->all() as $warehouseProductRecord) {
            $return[] = $warehouseProductRecord;
        }
        unset($warehouseProductRecord);
        unset($warehouseProductQuery);
        unset($platformId);
        unset($uProductId);
        unset($asArray);
        return $return;
    }

    public static function getLocationPath($location_id = 0, $warehouse_id = 0, $blocksList = []) {
        $item = '';
        if ($location_id == 0) {
            return $item;
        }
        $location = \common\models\Locations::find()->where(['warehouse_id' => $warehouse_id, 'location_id' => $location_id])->asArray()->one();

        $item .= self::getLocationPath($location['parrent_id'], $warehouse_id, $blocksList);
        if (!empty($item)) {
            $item .= ', ';
        }
        $item .= $blocksList[$location['block_id']] . ': ' . $location['location_name'];

        return $item;
    }

    public static function isRelocationPossible()
    {
        $wh = self::get_warehouses();
        if (!is_array($wh) || count($wh) == 0) {
            return false;
        } elseif (count($wh) > 1) {
            return true;
        } else {
            return \common\models\Locations::find()->where(['warehouse_id' => $wh[0]['id']??null, 'is_final' => 1])->count() > 1;
        }
    }

    public static function getLocations($warehouse_id, $with_blocks = true, $only_final = true, $separator = ', ') {
        $blocks = [];
        $locations = [];
        $parentMap = [];

        $locationList = [];
        foreach (\common\models\Locations::find()
                ->where(['warehouse_id' => $warehouse_id])
                ->orderBy(['parrent_id' => SORT_ASC, 'location_name' => SORT_ASC])
                ->all() as $location) {
            $locations[$location->location_id] = $location;
            if (!isset($parentMap[$location->parrent_id]))
                $parentMap[$location->parrent_id] = [];
            $parentMap[$location->parrent_id][] = $location->location_id;
            if (!isset($blocks[$location->block_id])) {
                $blocks[$location->block_id] = $location->locationBlock->block_name;
            }

            $locationList[$location->location_id] = [
                'location_id' => $location->location_id,
                'parent_id' => $location->parrent_id,
                'is_final' => $location->is_final,
                'block_name' => $blocks[$location->block_id],
                'location_name' => $location->location_name,
                'complete_name' => [], //[$blocks[$location->block_id].': '.$location->location_name]
            ];
        }

        $maxLevel = 0;
        foreach ($locationList as $_locId => $locationVariant) {
            $level = 0;
            $parent_id = $locationVariant['parent_id'];
            $weight = [];
            $weight[] = array_search($_locId, $parentMap[$parent_id]) + 1;
            $completeName = [];
            $completeName[] = ($with_blocks ? $locationVariant['block_name'] . ': ' : '') . $locationVariant['location_name'];
            while ($parent_id != 0) {
                if (!isset($locationList[$parent_id])) {
                    $level = false;
                    break;
                }
                $completeName[] = ($with_blocks ? $locationList[$parent_id]['block_name'] . ': ' : '') . $locationList[$parent_id]['location_name'];
                $parent_id = $locationList[$parent_id]['parent_id'];
                $weight[] = array_search($_locId, $parentMap[$parent_id]) + 1;
                $level++;
            }
            if ($level === false) {
                unset($locationList[$_locId]);
            } else {
                $maxLevel = max($maxLevel, $level);
                $locationList[$_locId]['level'] = $level;
                $locationList[$_locId]['complete_name'] = implode($separator, array_reverse($completeName));
            }
        }

        if ($only_final) {
            foreach ($locationList as $_locId => $locationVariant) {
                if (!$locationVariant['is_final']) {
                    unset($locationList[$_locId]);
                }
            }        
        }

        return /* array_values */($locationList);
    }

    public static function getWarehousesProductsLayersIDbyExpiryDate($expiry_date) {
        list($year, $month, $day) = array_pad(explode('-', $expiry_date), 3, null);
        if (checkdate((int)$month, (int)$day, (int)$year)) {
            $warehousesProductsLayersRecord = \common\models\WarehousesProductsLayers::findOne(['expiry_date' => $expiry_date]);
            if (!($warehousesProductsLayersRecord instanceof \common\models\WarehousesProductsLayers)) {
                $warehousesProductsLayersRecord = new \common\models\WarehousesProductsLayers();
                $warehousesProductsLayersRecord->layers_name = \common\helpers\Date::date_short($expiry_date);
                $warehousesProductsLayersRecord->expiry_date = $expiry_date;
                $warehousesProductsLayersRecord->save(false);
            }
            return $warehousesProductsLayersRecord->layers_id;
        }
        return 0;
    }

    public static function getExpiryDateByLayersID($layers_id) {
        $warehousesProductsLayersRecord = \common\models\WarehousesProductsLayers::findOne(['layers_id' => $layers_id]);
        if ($warehousesProductsLayersRecord instanceof \common\models\WarehousesProductsLayers) {
            return $warehousesProductsLayersRecord->expiry_date;
        }
    }

    public static function getWarehousesProductsBatchIDbyBatchName($batch_name) {
        if ($batch_name != '') {
            $WarehousesProductsBatchesRecord = \common\models\WarehousesProductsBatches::findOne(['batch_name' => $batch_name]);
            if (!($WarehousesProductsBatchesRecord instanceof \common\models\WarehousesProductsBatches)) {
                $WarehousesProductsBatchesRecord = new \common\models\WarehousesProductsBatches();
                $WarehousesProductsBatchesRecord->batch_name = $batch_name;
                $WarehousesProductsBatchesRecord->save(false);
            }
            return $WarehousesProductsBatchesRecord->batch_id;
        }
        return 0;
    }

    public static function getBatchNameByBatchID($batch_id) {
        $WarehousesProductsBatchesRecord = \common\models\WarehousesProductsBatches::findOne(['batch_id' => $batch_id]);
        if ($WarehousesProductsBatchesRecord instanceof \common\models\WarehousesProductsBatches) {
            return $WarehousesProductsBatchesRecord->batch_name;
        }
    }
}
