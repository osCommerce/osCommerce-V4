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

namespace common\widgets;

class SupplierEdit extends \yii\base\Widget {

    public $service;
    public $baseUrl;
    
    public function init() {
        parent::init();
    }

    public function run() {

        $sInfo = new \stdClass();
        if ( true ) {
            $supplierRules = new \backend\models\SuppliersRules();
            $supplierRules->getSuppliersData($this->service->supplier, $sInfo);
        }
        $mayEditCost = false;
        if (\frontend\design\Info::isTotallyAdmin()){
            $mayEditCost = true;
        }

        $taxClassesVariants = [
            0 => TEXT_NONE,
        ];
        foreach (\common\helpers\Tax::getTaxClassesVariants() as $variant) {
            $taxClassesVariants[$variant['id']] = $variant['text'];
        }

        $days = [
            0 => TEXT_EVERYDAY,
            1 => TEXT_MONDAY,
            2 => TEXT_TUESDAY,
            3 => TEXT_WEDNESDAY,
            4 => TEXT_THURSDAY,
            5 => TEXT_FRIDAY,
            6 => TEXT_SATURDAY,
            7 => TEXT_SUNDAY,
        ];
        
        $hours = [];
        for($h = 0; $h <= 24; $h++) {
            $hours[$h] = sprintf("%'.02d", $h) . ':00';
        }
        
        $open_hours = \common\models\SuppliersDispatchTime::find()->where(['suppliers_id' => (int) $this->service->supplier->suppliers_id])->all();
        
        return $this->render('supplier-edit',[
            'service' => $this->service,
            'taxClassesVariants' => $taxClassesVariants,
            'sInfo' => $sInfo,
            'path' => $this->getPath() . \Yii::$app->controller->id.'/'.\Yii::$app->controller->action->id,
            'generatePath' => $this->getPath() . \Yii::$app->controller->id.'/generate-key',
            'cancelPath' => $this->getPath() . \Yii::$app->controller->id,
            'mayEditCost' => $mayEditCost,
            'count_open_hours' => count($open_hours),
            'open_hours' => $open_hours,
            'days' => $days,
            'hours' => $hours,
        ]);
    }
    
    public function getPath(){
        return (!is_null($this->baseUrl)?$this->baseUrl. '/':'');
    }

}
