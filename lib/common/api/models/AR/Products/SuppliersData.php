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

namespace common\api\models\AR\Products;


use backend\models\EP\Tools;
use common\api\models\AR\EPMap;
use common\models\Suppliers;
use common\helpers\PriceFormula;
use yii\db\Expression;

class SuppliersData extends EPMap
{
    public $suppliers_name;

    protected $hideFields = [
        'products_id',
        //'uprid',
    ];

    protected $parentObject;

    public static function primaryKey()
    {
        return ['products_id', 'uprid', 'suppliers_id'];
    }


    public static function tableName()
    {
        return 'suppliers_products';
    }

    public function customFields()
    {
        $fields = parent::customFields();
        $fields[] = 'suppliers_name';
        return $fields;
    }

    public function isModified()
    {
        // prevent product modify
        return false;
        //return parent::isModified();
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->products_id = $parentObject->products_id;
        if ( isset($parentObject->uprid) ) {
            $this->uprid = $parentObject->uprid;
        }else{
            $this->uprid = $this->products_id;
        }
        $this->parentObject = $parentObject;

        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        $objectMatch = ($importedObject->products_id==$this->products_id) && ($importedObject->uprid==$this->uprid) && ($importedObject->suppliers_id == $this->suppliers_id);

        if ( $objectMatch ) {
            $this->pendingRemoval = false;
            return true;
        }
        return false;
    }

    public function exportArray(array $fields = [])
    {
        $data = parent::exportArray($fields);
        if ( count($fields)==0 || array_key_exists('suppliers_name', $fields) ) {
            static $fetched = [];
            if ( !isset($fetched[$this->suppliers_id]) ) {
                $fetched[$this->suppliers_id] = '';
                $supplierName = Suppliers::find()->select('suppliers_name')->where(['suppliers_id' => $this->suppliers_id])->asArray(true)->one();
                if (is_array($supplierName)) {
                    $fetched[$this->suppliers_id] = $supplierName['suppliers_name'];
                }
            }
            $this->suppliers_name = $fetched[$this->suppliers_id];
            $data['suppliers_name'] = $this->suppliers_name;
        }
        return $data;
    }


    public function beforeSave($insert)
    {
        if ( $insert ) {
            if ( empty($this->date_added) ) {
                $this->date_added = new Expression("NOW()");
            }
            if ( is_null($this->suppliers_surcharge_amount) || is_null($this->suppliers_margin_percentage) ) {
                $supplierData = Tools::getInstance()->supplierData($this->suppliers_id);
                if ( is_array($supplierData) ) {
                    if ( is_null($this->suppliers_surcharge_amount) && $supplierData['suppliers_surcharge_amount'] ) {
                        $this->suppliers_surcharge_amount = $supplierData['suppliers_surcharge_amount'];
                    }
                    if ( is_null($this->suppliers_margin_percentage) && $supplierData['suppliers_margin_percentage'] ) {
                        $this->suppliers_margin_percentage = $supplierData['suppliers_margin_percentage'];
                    }
                }
            }
        }else{
            if ( $this->isModified() ) {
                $this->last_modified = new Expression("NOW()");
            }
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ( count($changedAttributes)>0 ) {
            PriceFormula::applyDb($this->products_id);
        }
    }


}