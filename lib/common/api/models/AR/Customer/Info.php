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

namespace common\api\models\AR\Customer;


use common\api\models\AR\EPMap;
use yii\db\Expression;

class Info extends EPMap
{

    protected $hideFields = [
        'customers_info_id',
        'time_long',
        'token'
    ];

    protected $parentObject;

    public static function tableName()
    {
        return TABLE_CUSTOMERS_INFO;
    }

    public static function primaryKey()
    {
        return ['customers_info_id'];
    }

    public function parentEPMap(EPMap $parentObject)
    {
        $this->customers_info_id = $parentObject->customers_id;
        //echo '<pre>'; var_dump($this->customers_info_id); echo '</pre>';
        parent::parentEPMap($parentObject);
    }

    public function matchIndexedValue(EPMap $importedObject)
    {
        $this->pendingRemoval = false;
        return true;
        //return parent::matchIndexedValue($importedObject);
    }
    
    public function importArray($data)
    {
        if ( !isset($data['global_product_notifications']) ) {
            $data['global_product_notifications'] = 1;
        }        

        $importResult = parent::importArray($data);
        return $importResult;
    }

    public function beforeSave($insert)
    {
        if ( $insert ) {
            if ( is_null($this->customers_info_date_account_created) ) {
                $this->customers_info_date_account_created = new Expression("NOW()");
            }
        }else{
            $this->customers_info_date_account_last_modified = new Expression("NOW()");
        }
        return parent::beforeSave($insert);
    }



}