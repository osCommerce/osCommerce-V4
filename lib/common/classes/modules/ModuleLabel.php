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

namespace common\classes\modules;

use common\models\OrdersLabel;

abstract class ModuleLabel extends Module {

    public $shipping_weight;
    public $shipping_num_boxes;

    public $platform_id;
    public $tracking = false;

    public function possibleMethods()
    {
        return [];
    }

    function quote($method = '') {
        
    }

    /**
     * @param int $order_id
     * @param int $orders_label_id
     * @return bool
     */
    public function shipment_exists (int $order_id, int $orders_label_id) {
        $check_label = \common\models\OrdersLabel::find()
            ->select(['orders_id', 'label_class', 'tracking_number', 'parcel_label_pdf'])
            ->andWhere(['orders_id' => $order_id, 'orders_label_id' => $orders_label_id])
            ->asArray()
            ->one();
        list($module, $method) = explode('_', $check_label['label_class']);
        if ($module == $this->code) {
            if ($check_label['parcel_label_pdf'] != '') {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * @param int $order_id
     * @param int $orders_label_id
     * @return float
     */
    public function shipment_total (int $order_id, int $orders_label_id) {
        $shipment_total = 0;
        $oLabel = \common\models\OrdersLabel::findOne(['orders_id' => $order_id, 'orders_label_id' => $orders_label_id]);
        foreach ($oLabel->getOrdersLabelProducts() as $orders_products_id => $qty) {
            $oProduct = \common\models\OrdersProducts::findOne(['orders_id' => $order_id, 'orders_products_id' => $orders_products_id]);
            if ($oProduct->final_price > 0 ) {
                $shipment_total += \common\helpers\Tax::add_tax_always($oProduct->final_price,$oProduct->products_tax) * $qty;
            }
        }
        return $shipment_total;
    }

    /**
     * @param int $order_id
     * @param int $orders_label_id
     * @return float
     */
    public function shipment_weight (int $order_id, int $orders_label_id) {
        $shipment_weight = 0;
        $oLabel = \common\models\OrdersLabel::findOne(['orders_id' => $order_id, 'orders_label_id' => $orders_label_id]);
        foreach ($oLabel->getOrdersLabelProducts() as $orders_products_id => $qty) {
            $oProduct = \common\models\OrdersProducts::findOne(['orders_id' => $order_id, 'orders_products_id' => $orders_products_id]);
            if ($oProduct->products_weight > 0) {
                $shipment_weight += $oProduct->products_weight * $qty;
            } else {
                $shipment_weight += \common\helpers\Product::get_products_weight($oProduct->products_id) * $qty;
            }
        }
        return $shipment_weight;
    }

    /**
     * @param int $order_id
     * @param int $orders_label_id
     * @return float|int
     */
    public function shipment_volume_weight(int $order_id, int $orders_label_id) {
        $shipment_volume = 0;
        $oLabel = \common\models\OrdersLabel::findOne(['orders_id' => $order_id, 'orders_label_id' => $orders_label_id]);
        foreach ($oLabel->getOrdersLabelProducts() as $orders_products_id => $qty) {
            $oProduct = \common\models\OrdersProducts::findOne(['orders_id' => $order_id, 'orders_products_id' => $orders_products_id]);
            $shipment_volume += \common\helpers\Product::get_products_volume($oProduct->products_id, true) * $qty;
        }
        return $shipment_volume;
    }

    /**
     * check delivery date
     * @param type $delivery_date
     * @return boolean
     */
    public function checkDeliveryDate($delivery_date) {
        if (tep_not_null($delivery_date) && $delivery_date != '0000-00-00')
            return true;
        return false;
    }
    
    /*if used physical delivery*/
    public function useDelivery() {
        return true;
    }
    
    public function setWeight($weight){
        $this->shipping_weight = $weight;
    }
    
    public function setNumBoxes($numBoxes){
        $this->shipping_num_boxes = $numBoxes;
    }
        
    public function setPlatform(int $platform_id){
        $this->platform_id = $platform_id;
    }
    
    public function getGroupRestriction($platform_id) {
        return '';
    }
    
    public function getRestriction($platform_id, $languages_id, $ignoreVisibility = false) {
        return parent::getRestriction($platform_id, $languages_id, true);
    }

    public function withoutSettings(OrdersLabel $ordersLabel): bool
    {
        return false;
    }
    
    /**
     * Allows to show extra params for label
     * @param $order_label_id
     * @return string html
     *
     * Supports simplified method getExtraParamsArray like this:
     *
     * public function getExtraParamsArray($order_label_id)
     * {
     *    return [
     *      'param1' => [
     *          'label' => 'Param1',
     *          'type'  => 'edit',
     *          'value' => 'sample text',
     *      ],
     *      'param2' => [
     *          'label' => 'Param2',
     *          'type'  => 'checkbox',
     *          'value' => true,
     *      ],
     *      'param3' => [
     *          'label' => 'Param3',
     *          'type'  => 'dropdown',
     *          'dropdown' => ['one' => 'Name1', 'two' => 'Name2'],
     *          'value' => 'one',
     *      ],
     *  ];
     * }
     *
     */
    public function getExtraParams($order_label_id)
    {
        $html = '';
        if (method_exists($this, 'getExtraParamsArray') && !empty($extraParams = $this->getExtraParamsArray($order_label_id))) {
            $moduleCode = static::getModuleCode();
            $html .= '<div class="label-params">';
            foreach ($extraParams as $name => $info) {
                $paramName = $moduleCode . '_' . $name;
                $html .= '<div class="label-param">';
                try {
                    if (!empty($info['label']) && $info['type'] != 'checkbox') {
                        $html .= sprintf('<label for="%s">%s</label>', $paramName, $info['label']);
                    }
                    switch ($info['type']) {
                        case 'edit':
                            $html .= \common\helpers\Html::textInput($paramName, $info['value']??null, ['id' => $paramName]);
                            break;
                        case 'checkbox':
                            $html .= \common\helpers\Html::checkbox($paramName, $info['value']??false, ['id' => $paramName]);
                            if (!empty($info['label'])) {
                                $html .= sprintf('<label for="%s">%s</label>', $paramName, $info['label']);
                            }
                            break;
                        case 'dropdown':
                            $html .= \common\helpers\Html::dropDownList($paramName, $info['value']??null, $info['dropdown'], ['id' => $paramName]);
                            break;
                    }
                    
                } catch (\Throwable $e) {
                    \common\helpers\Php::handleErrorProd($e, __FUNCTION__, "label/$moduleCode");
                }
                $html .= '</div>';
            }
            $html .= '</div';
        }
        return $html;
    }
    
    public function extractExtraParamsValues($params)
    {
        $moduleCode = static::getModuleCode();
        if (method_exists($this, 'getExtraParamsArray') && !empty($extraParams = $this->getExtraParamsArray())) {
            $res = [];
            foreach ($extraParams as $name => $info) {
                $paramName = $moduleCode . '_' . $name;
                if (isset($params[$paramName])) {
                    $res[$name] = $params[$paramName];
                }
            }
            return $res;
        }
    }

    public function loadExtraParams($order_label_id)
    {
        $rec = OrdersLabel::findOne(['orders_label_id' => $order_label_id]);
        if (!empty($rec) && !empty($rec->extra_params)) {
            return @json_decode($rec->extra_params, true);
        }
    }
    
    public function isExtraParamsAvailable()
    {
        return method_exists($this, 'getExtraParamsArray') && !empty($this->getExtraParamsArray());
    }
    
}
