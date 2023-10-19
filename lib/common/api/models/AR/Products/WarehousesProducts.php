<?php

/*
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 * 
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2005 Holbi Group Ltd
 * 
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

namespace common\api\models\AR\Products;

use backend\models\EP\Tools;
use yii;
use yii\db\Query;
use yii\db\Expression;
use common\api\models\AR\EPMap;

class WarehousesProducts  extends EPMap
{

    protected $hideFields = [
    ];

    /**
     * @var EPMap
     */
    protected $parentObject;

    protected $qtyDelta = null;

    public function __construct(array $config = [])
    {
        if ( isset($config['keyCode']) ){
            $params = explode('_',$config['keyCode']);
            if ( $params[0]??null ) $this->warehouse_id = (int)$params[0];
            if ( $params[1]??null ) $this->suppliers_id = (int)$params[1];
            if ( $params[2]??null ) $this->location_id = (int)$params[2];
        }
        parent::__construct($config);
    }

    public static function tableName()
    {
        return 'warehouses_products';
    }

    public static function primaryKey()
    {
        return ['products_id', 'warehouse_id', 'suppliers_id', 'location_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        $this->prid = intval($parentObject->products_id);
        /*if (is_subclass_of($parentObject, 'common\api\models\AR\Products\Inventory')){
          $this->prid = $parentObject->prid;
        } else {
        }*/
        $this->parentObject = $parentObject;

        parent::parentEPMap($parentObject);
    }

    public static function getAllKeyCodes()
    {
        static $supplierIds = false;
        if ( $supplierIds===false ){
            $supplierIds = yii\helpers\ArrayHelper::map(
                \common\models\Suppliers::find()->select('suppliers_id')->orderBy(['suppliers_id'=>SORT_ASC])->all(),
                'suppliers_id','suppliers_id'
            );
        }
        static $keyCodes;
        if ( !is_array($keyCodes) ) {
            $keyCodes = [];
            foreach ($supplierIds as $suppliersId) {
                foreach (\common\helpers\Warehouses::get_warehouses(true) as $warehouse) {
                    $keyCode = (int)$warehouse['id'] . '_' . $suppliersId;
                    $keyCodes[$keyCode] = [
                        'warehouse_id' => (int)$warehouse['id'],
                        'products_id' => null,
                        'suppliers_id' => $suppliersId,
                        'location_id' => 0,
                    ];
                    foreach (Tools::getInstance()->getWarehouseLocations($warehouse['id']) as $warehouseLocation) {
                        $keyCodes[$keyCode . '_' . $warehouseLocation['location_id']] = [
                            'warehouse_id' => (int)$warehouse['id'],
                            'products_id' => null,
                            'suppliers_id' => $suppliersId,
                            'location_id' => (int)$warehouseLocation['location_id'],
                        ];
                    }
                }
            }
        }

        return $keyCodes;
    }

    public function isModified()
    {
        return false; // no last modify last date update
    }

    public function getKeyCode()
    {
        $keyCode = (int)$this->warehouse_id.'_'.(int)$this->suppliers_id;
        if ( !empty($this->location_id) ){
            $keyCode .= '_' . (int)$this->location_id;
        }
        return $keyCode;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $recalcDirty = $this->getDirtyAttributes(['products_quantity', 'warehouse_stock_quantity','allocated_stock_quantity','temporary_stock_quantity']);

        $this->qtyDelta = intval($this->getAttribute('warehouse_stock_quantity')) - intval($this->getOldAttribute('warehouse_stock_quantity'));
        if ( count($recalcDirty)>0 ) {
            // reset dynamical attributes
            foreach (array_keys($recalcDirty) as $resetKey) {
                $this->setAttribute($resetKey, $insert?0:$this->getOldAttribute($resetKey) );
            }
        }
        /*
        if ( count($recalcDirty)>0 || $insert) {
            $this->setAttribute('products_quantity', intval($this->warehouse_stock_quantity) - intval($this->allocated_stock_quantity) - intval($this->temporary_stock_quantity) );
            if ( isset($recalcDirty['warehouse_stock_quantity']) ) {
                $qtyDelta = intval($this->getAttribute('warehouse_stock_quantity')) - intval($this->getOldAttribute('warehouse_stock_quantity'));
                if ( $qtyDelta!=0 ) {
                    \common\helpers\Product::log_stock_history_before_update($this->products_id, abs($qtyDelta), $qtyDelta<0?'-':'+', [
                        'warehouse_id' => $this->warehouse_id,
                        'suppliers_id' => $this->suppliers_id,
                        'comments' => 'Automatically stock update',
                        'admin_id' => 0
                    ]);
                }
            }
        }*/

        return true;
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if ( $this->parentObject ) {
            $this->parentObject->initiateAfterSave('Product::doCache');
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ( $this->qtyDelta!=0 ) {
            $qtyDelta = $this->qtyDelta;
            \common\helpers\Warehouses::update_products_quantity(
                $this->products_id, $this->warehouse_id,
                abs($qtyDelta), ($qtyDelta> 0 ? '+' : '-'), $this->suppliers_id, $this->location_id,
                [
                    'comments' => 'Automatically stock update',
                    'admin_id' => 0
                ]
            );
            if ( is_object($this->parentObject) ) {
                $this->parentObject->initiateAfterSave('Product::doCache');
            }
        }
        //\common\helpers\Warehouses::update_products_quantity($this->products_id, $this->warehouse_id,0,'+',$this->suppliers_id);
    }


}