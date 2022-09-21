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

namespace backend\models\ProductEdit;

use common\models\Products;

class ViewStockInfo
{

    /**
     * @var \objectInfo
     */
    protected $productInfoRef;

    public function __construct($productInfo)
    {
        $this->productInfoRef = $productInfo;
        $this->wrap($this->productInfoRef);
    }

    protected function wrap($pInfo)
    {
        $products_id = $pInfo->products_id;
        if ( $pInfo->parent_products_id && $pInfo->products_id_stock ) {
            $products_id = $pInfo->products_id_stock;
            $pDataInfo = new \objectInfo(\common\models\Products::findOne($products_id)->getAttributes());
        }else{
            $pDataInfo = $pInfo;
        }

        $allocatedTemporary = \common\helpers\Product::getAllocatedTemporary($products_id, true);

        $pInfo->products_quantity = $pDataInfo->products_quantity;
        $pInfo->allocated_quantity = ($pDataInfo->allocated_stock_quantity - $allocatedTemporary);
        $pInfo->allocated_temporary_quantity = $allocatedTemporary;
        $pInfo->temporary_quantity = $pDataInfo->temporary_stock_quantity;
        $pInfo->warehouse_quantity = $pDataInfo->warehouse_stock_quantity;
        //$pInfo->ordered_quantity = $pDataInfo->ordered_stock_quantity;
        $pInfo->ordered_quantity = \common\helpers\Product::getStockOrdered($products_id);
        $pInfo->suppliers_quantity = $pDataInfo->suppliers_stock_quantity;
        $pInfo->deficit_quantity = \common\helpers\Product::getStockDeficit($products_id);

        if ((int)$pDataInfo->stock_reorder_level < 0) {
            $pInfo->stock_reorder_level = (int)STOCK_REORDER_LEVEL;
        } else {
            $pInfo->stock_reorder_level_on = true;
        }
        if ((int)$pDataInfo->stock_reorder_quantity < 0) {
            $pInfo->stock_reorder_quantity = (int)STOCK_REORDER_QUANTITY;
        } else {
            $pInfo->stock_reorder_quantity_on = true;
        }

        if ((int)$pDataInfo->stock_limit < 0) {
            $pInfo->stock_limit = (int)ADDITIONAL_STOCK_LIMIT;
        } else {
            $pInfo->stock_limit_on = true;
        }

        $pInfo->platformStockList = [];
        $pInfo->platformWarehouseList = [];
        if ($extScl = \common\helpers\Acl::checkExtensionAllowed('StockControl', 'allowed')) {
            $extScl::updateProductViewStockInfo($pInfo);
        }
    }

}