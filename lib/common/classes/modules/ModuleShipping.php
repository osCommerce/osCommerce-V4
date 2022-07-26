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

abstract class ModuleShipping extends Module {
   
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
     * @return bool
     */
    public function shipment_exists (int $order_id) {
        $check_order = \common\models\Orders::find()
            ->select(['orders_id', 'shipping_class', 'tracking_number', 'parcel_label_pdf'])
            ->andWhere(['orders_id' => $order_id])
            ->asArray()
            ->one();
        list($module, $method) = explode('_', $check_order['shipping_class']);
        if ($module == $this->code) {
            if ($check_order['parcel_label_pdf'] != '') {
                return true;
            }
        } else {
            return false;
        }
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
        //$this->checkLabels(); //??!! use hasLabelModule
    }
    
    public function getPreferredLabels() {
        $visibilityAccess = [];
        $modulesLabels = \common\models\ModulesLabels::findOne(['platform_id' => $this->platform_id, 'code' => $this->code]);
        if (!is_null($modulesLabels) && !empty($modulesLabels->labels_list)) {
            $visibilityAccess = explode(',', $modulesLabels->labels_list);
        }
        return $visibilityAccess;
    }
    public function checkLabels() {
        $modulesLabels = \common\models\ModulesLabels::findOne(['platform_id' => $this->platform_id, 'code' => $this->code]);
        if (!is_null($modulesLabels) && !empty($modulesLabels->labels_list)) {
            $this->tracking = true;
        }
    }
    public function hasLabelModule()
    {
        $labels = \common\helpers\Modules::getLabelsList($this->platform_id);
        if (count($labels) > 0) {
            return true;
        }
        return false;
        /*
        $modulesLabels = \common\models\ModulesLabels::findOne(['platform_id' => $this->platform_id, 'code' => $this->code]);
        if (!is_null($modulesLabels) && !empty($modulesLabels->labels_list)) {
            return true;
        }
        return false;
        */
    }
    
    public function getLabels($platform_id) {
        if ( (int)$platform_id==0 ) return '';
        
        $labels = \common\helpers\Modules::getLabelsList($platform_id);
        
        $modulesLabels = \common\models\ModulesLabels::findOne(['platform_id' => $platform_id, 'code' => $this->code]);
        $visibilityAccess = [];
        
        if (!is_null($modulesLabels) && !empty($modulesLabels->labels_list)) {
            $visibilityAccess = explode(',', $modulesLabels->labels_list);
        }
        
        $response = '<br><br><table width="50%" id="module_labels_restriction" style="max-height:350px"><thead><tr><th>' . MODULE_SHIPPING_TRACKING_STATUS . ' ' . tep_draw_checkbox_field('enable_tracking', '1', !is_null($modulesLabels), '', 'onchange="return updateLabels(this);" class="uniform" ' ) . '</th></thead><tbody>';
        
        foreach ($labels as $id => $name) {
            $response .= '<tr><td>';
            $params = 'class="uniform" ';
            if (is_null($modulesLabels)) {
                $params .= 'disabled';
            }
            $response .= '<label>';
            $response .= tep_draw_checkbox_field('labels[]', $id, in_array($id, $visibilityAccess), '', $params );
            $response .= $name;
            $response .= '</label>';
            $response .= '</td></tr>';
        }
        
        $response .= '</tbody></table>';
        $response .= '<script type="text/javascript">function updateLabels(obj) { if ( $(obj).is(":checked") ) { $("input[name^=\'labels\']").prop("disabled", false); $("#module_labels_restriction div.checker").length && $("#module_labels_restriction div.checker.disabled").removeClass("disabled"); } else { $("input[name^=\'labels\']").prop("disabled", true); $("#module_labels_restriction div.checker").length && $("#module_labels_restriction tbody div.checker").addClass("disabled"); } }</script>';
        return $response;
        
    }
   
    public function setLabels() {
        $platform_id = (int)\Yii::$app->request->post('platform_id');
        if ( (int)$platform_id==0 ) return false;
        
        $modulesLabels = \common\models\ModulesLabels::findOne(['platform_id' => $platform_id, 'code' => $this->code]);
            
        $enable_tracking = (int)\Yii::$app->request->post('enable_tracking');
        if ($enable_tracking == 1) {
            $labels = \Yii::$app->request->post('labels', []);
            if (is_null($modulesLabels)) {
                $modulesLabels = new \common\models\ModulesLabels();
                $modulesLabels->platform_id = $platform_id;
                $modulesLabels->code = $this->code;
            }
            $modulesLabels->labels_list = implode(',', $labels);
            $modulesLabels->save();
        } else {
            if (!is_null($modulesLabels)) {
                $modulesLabels->delete();
            }
        }
    }

    /**
     * used in shipping labels
     * @return bool
     */
    public function needDeliveryDate()
    {
        return false;
    }

    /**
     * @param string $method
     * @param null|array|object $data
     * @return bool|array
     */
    public function validate(string $method = '', $data = null)
    {
        return true;
    }

    /**
     * shipping to collect
     * return false or \common\classes\VO\CollectAddress with address warehouse
     * @see \common\modules\orderShipping\np
     * @param string $method
     * @return bool|\common\classes\VO\CollectAddress
     */
    public function toCollect(string $method = '')
    {
        return false;
    }
    
    public function getExtraDisabledDays()
    {
        return false;
    }

    public function isOnline() {
        return false;
    }
}
