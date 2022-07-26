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

namespace common\models;

use yii\db\ActiveRecord;

class CustomersErrors extends ActiveRecord {

    public static function tableName() {
        return 'customers_errors';
    }

    public function getCustomer() {
        return $this->hasOne(Customers::className(), ['customers_id' => 'customers_id']);
    }
    
    public static function find() {
        return new queries\CustomersErrorsQuery(get_called_class());
    }
}
