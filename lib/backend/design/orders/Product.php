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

namespace backend\design\orders;


use Yii;
use yii\base\Widget;
use common\helpers\OrderProduct;
use common\classes\Images;

class Product extends Widget {

    public $product;
    public $manager;
    public $opsArray;
    public $handlers_array = [];
    public $iter;
    public $order;
    public $currency;
    public $currency_value;
    public $warehouseList;
    public $locationBlockList;

    public $warehouses_allocated_array = [];
    public $suppliers_allocated_array = [];

    public function init(){
        parent::init();
        if (!$this->currency)
            $this->currency = $this->order->info['currency'];
        if (!$this->currency_value)
            $this->currency_value = $this->order->info['currency_value'];
    }

    public function run(){
        global $languages_id;

        $isTemporary = false;
        foreach (\common\helpers\OrderProduct::getAllocatedArray($this->product['orders_products_id'], true) as $opaRecord) {
            if ((int)$opaRecord['is_temporary'] > 0 AND (int)$opaRecord['allocate_received'] > (int)$opaRecord['allocate_dispatched']) {
                $isTemporary = true;
                break;
            }
        }
        unset($opaRecord);

        $opsmArray = [];
        if (isset($this->opsArray[$this->product['status']])) {
            $opsmArray = $this->opsArray[$this->product['status']]->getMatrixArray();
        }

        /**
         * @var $ext \common\extensions\Handlers\Handlers
         */
        if ( ($ext = \common\helpers\Acl::checkExtensionAllowed('Handlers', 'allowed')) && !$ext::checkAccess((int) $this->product['id'], $this->handlers_array) ) {
            return ;
        }

        $rowClass = '';
        if (!(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_WAREHOUSES']))) {
            $foundInAllocations = false;
            foreach (\common\helpers\OrderProduct::getAllocatedArray($this->product['orders_products_id']) as $orderProductAllocateRecord) {
                if (in_array($orderProductAllocateRecord['warehouse_id'], $this->warehouses_allocated_array)) {
                    $foundInAllocations = true;
                    break;
                }
            }
            unset($orderProductAllocateRecord);
            if (!$foundInAllocations) {
                $rowClass = 'dis_module';
            }
        }

        if (empty($rowClass) && !(\common\helpers\Acl::rule(['BOX_HEADING_CUSTOMERS', 'BOX_CUSTOMERS_ORDERS', 'RULE_ALLOW_SUPPLIERS']))) {
            $foundInAllocations = false;
            foreach (\common\helpers\OrderProduct::getAllocatedArray($this->product['orders_products_id']) as $orderProductAllocateRecord) {
                if (in_array($orderProductAllocateRecord['suppliers_id'], $this->suppliers_allocated_array)) {
                    $foundInAllocations = true;
                    break;
                }
            }
            unset($orderProductAllocateRecord);
            if (!$foundInAllocations) {
                $rowClass = 'dis_module';
            }
        }

        $location = '';
        foreach (\common\helpers\OrderProduct::getAllocatedArray($this->product['orders_products_id']) as $orderProductAllocateRecord) {
            $locationName = trim(\common\helpers\Warehouses::getLocationPath($orderProductAllocateRecord['location_id'], $orderProductAllocateRecord['warehouse_id'], $this->locationBlockList));
            if ($orderProductAllocateRecord['layers_id']) {
                $locationName .= ', ' . \common\helpers\Translation::getTranslationValue('TEXT_EXPIRY_DATE', 'admin/categories') . ' ' . \common\helpers\Date::date_short(\common\helpers\Warehouses::getExpiryDateByLayersID($orderProductAllocateRecord['layers_id'])); 
            }
            if ($orderProductAllocateRecord['batch_id']) {
                $locationName .= ', ' . TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME . ' ' . \common\helpers\Warehouses::getBatchNameByBatchID($orderProductAllocateRecord['batch_id']); 
            }
            $location .= '<div>'
                . (isset($this->warehouseList[$orderProductAllocateRecord['warehouse_id']]) ? $this->warehouseList[$orderProductAllocateRecord['warehouse_id']] : 'N/A')
                . ', ' . ($locationName != '' ? $locationName : 'N/A') . ': '
                . '<b>' . $orderProductAllocateRecord['allocate_received'] . '</b>'
                . '</div>';
            unset($locationName);
        }
        unset($orderProductAllocateRecord);

        $gv_state_label = '';
        if ($this->product['gv_state'] != 'none') {
            $_inner_gv_state_label = (defined('TEXT_ORDERED_GV_STATE_' . strtoupper($this->product['gv_state'])) ? constant('TEXT_ORDERED_GV_STATE_' . strtoupper($this->product['gv_state'])) : $this->product['gv_state']);
            if ($this->product['gv_state'] == 'pending' || $this->product['gv_state'] == 'canceled') {
                $_inner_gv_state_label = '<a class="js_gv_state_popup" href="' . Yii::$app->urlManager->createUrl(['orders/gv-change-state', 'opID' => $this->product['orders_products_id']]) . '">' . $_inner_gv_state_label . '</a>';
            }
            $gv_state_label = '<span class="ordered_gv_state ordered_gv_state-' . $this->product['gv_state'] . '">' . $_inner_gv_state_label . '</span>';
        }

        $asset = null;
        if ($this->product['promo_id'] && \common\helpers\Acl::checkExtensionAllowed('Promotions')){
            $asset = \common\extensions\Promotions\models\PromotionService::getAsset($this->product['promo_id'], $this->product['id']);
        }

        $opsArray = array();
        foreach (\common\models\OrdersProductsStatus::findAll(['language_id' => (int)$languages_id]) as $opsRecord) {
            $opsArray[$opsRecord->orders_products_status_id] = $opsRecord;
        }
        unset($opsRecord);

        $suppliersPricesArray = array();
        foreach (\common\models\OrdersProductsAllocate::findAll(['orders_products_id' => (int)$this->product['orders_products_id']]) as $opaRecord) {
            if ($opaRecord->suppliers_price > 0) {
                if (!isset($suppliersPricesArray[$opaRecord->suppliers_id])) {
                    $suppliersPricesArray[$opaRecord->suppliers_id] = $opaRecord;
                } else {
                    $suppliersPricesArray[$opaRecord->suppliers_id]->allocate_received += $opaRecord->allocate_received;
                }
            }
        }

        return $this->render('product',[
            'rowClass' => $rowClass,
            'manager' => $this->manager,
            'order' => $this->order,
            'opsmArray' => $opsmArray,
            'product' => $this->product,
            'image' => Images::getImage($this->product['id'], 'Small'),
            'image_url' => Images::getImageUrl($this->product['id'], 'Large'),
            'iter' => $this->iter,
            'currency' => $this->currency,
            'currency_value' => $this->currency_value,
            'location' => $location,
            'gv_state_label' => $gv_state_label,
            'asset' => $asset,
            'color' => (isset($this->opsArray[$this->product['status']]) ? $this->opsArray[$this->product['status']]->getColour() : '#000000'),
            'status' => (isset($this->opsArray[$this->product['status']]) ? $this->opsArray[$this->product['status']]->orders_products_status_name : ''),
            'isTemporary' => $isTemporary,
            'headers' => [
                'cancel'     => $opsArray[OrderProduct::OPS_CANCELLED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_CANCELLED,
                'ordered'    => $opsArray[OrderProduct::OPS_STOCK_ORDERED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_STOCK_ORDERED,
                'deficit'    => $opsArray[OrderProduct::OPS_STOCK_DEFICIT]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_STOCK_DEFICIT,
                'received'   => $opsArray[OrderProduct::OPS_RECEIVED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_RECEIVED,
                'dispatched' => $opsArray[OrderProduct::OPS_DISPATCHED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_DISPATCHED,
                'delivered'  => $opsArray[OrderProduct::OPS_DELIVERED]->orders_products_status_name_long ?? TEXT_STATUS_LONG_OPS_DELIVERED
            ],
            'suppliersPricesArray' => $suppliersPricesArray,
        ]);
    }
}
