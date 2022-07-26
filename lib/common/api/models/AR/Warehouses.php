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

namespace common\api\models\AR;

use common\api\models\AR\Warehouses\Address;
use common\api\models\AR\Warehouses\Info;
use yii\db\Expression;

class Warehouses extends EPMap
{

    protected $hideFields = [
    ];

    protected $childCollections = [
        'addresses' => false,
        'info' => false,
    ];

    protected $indexedCollections = [
        'addresses' => 'common\api\models\AR\Warehouses\Address',
        'info' => 'common\api\models\AR\Warehouses\Info',
    ];

    public static function tableName()
    {
        return TABLE_WAREHOUSES;
    }

    public static function primaryKey()
    {
        return ['warehouse_id'];
    }

    public function rules() {
        return array_merge(
            parent::rules(),
            [
             ///   ['customers_company_vat', 'default', 'value' => '']
            ]
        );
    }

    public function initCollectionByLookupKey_Addresses($lookupKeys)
    {
        if ( !is_array($this->childCollections['addresses']) ) {
            $this->childCollections['addresses'] = [];
            if ($this->warehouse_id) {
                $this->childCollections['addresses'] =
                    Address::find()
                        ->addSelect(['*'])
                        ->where(['warehouse_id' => $this->warehouse_id])
                        ->all();
            }
        }
        return $this->childCollections['addresses'];
    }


    public function initCollectionByLookupKey_Info($lookupKeys)
    {
        if ( !is_array($this->childCollections['info'])) {
            $this->childCollections['info'] = [];
            if ($this->warehouse_id) {
                $this->childCollections['info'][] =
                    Info::findOne(['warehouse_id' => $this->warehouse_id]);
            }
        }
        return $this->childCollections['info'];
    }

    public function exportArray(array $fields = [])
    {
        $export = parent::exportArray($fields);

        return $export;
    }

    public function importArray($data)
    {
/*        if ( array_key_exists('customers_currency', $data) ) {
            $data['customers_currency_id'] = \common\helpers\Currencies::getCurrencyId($data['customers_currency']);
        }
*/
        $importResult = parent::importArray($data);
        return $importResult;
    }


    public function beforeSave($insert)
    {
        if ( $insert && (!is_array($this->childCollections['info']) || count($this->childCollections['info'])==0) ) {
            $this->childCollections['info'] = [];
            $this->childCollections['info'][] = new Info();
        }
        if ( $insert ) {
            if ( is_null($this->date_added) ) {
                $this->date_added = new Expression("NOW()");
            }
        }else{
            $this->last_modified = new Expression("NOW()");
        }
        /*
        if ( !$insert ) {
            $creditChanged = $this->getDirtyAttributes(['credit_amount']);
            if ( count($creditChanged)>0 ) {
                $old_credit_amount = $this->getOldAttribute('credit_amount');
                if ( number_format($old_credit_amount,4,'.','')!=number_format($old_credit_amount,4,'.','') ) {
                    tep_db_perform(TABLE_CUSTOMERS_CREDIT_HISTORY,[
| warehouse_id                | int(11)       | NO     |       |    <null> |                |
| credit_prefix               | varchar(1)    | NO     |       |    <null> |                |
| credit_amount               | decimal(11,2) | NO     |       |    <null> |                |
| currency                    | char(3)       | NO     |       |    <null> |                |
| currency_value              | decimal(14,6) | NO     |       |    <null> |                |
| customer_notified           | tinyint(1)    | NO     |       |    <null> |                |
| comments                    | mediumtext    | NO     |       |    <null> |                |
| date_added                  | datetime      | NO     |       |    <null> |                |
| admin_id
                    ]);
                }
            }
        }
        */
        return parent::beforeSave($insert);
    }

}