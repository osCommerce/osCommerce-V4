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
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "customers_info".
 *
 * @property integer $customers_info_id
 * @property string $customers_info_date_of_last_logon
 * @property integer $customers_info_number_of_logons
 * @property string $customers_info_date_account_created
 * @property string $customers_info_date_account_last_modified
 * @property integer $global_product_notifications
 * @property string $time_long
 * @property string $token
 */

class CustomersInfo extends ActiveRecord {

    public static function tableName() {
        return 'customers_info';
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['customers_info_date_account_created', 'customers_info_date_account_last_modified'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['customers_info_date_account_last_modified'],
                ],
                 'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }
    
    public function updateTimeLong(){
        $this->time_long = new \yii\db\Expression('NOW()');
        $this->update();
    }
    
    public function updateToken(){
        $this->token = 'CT-' . strtoupper(substr(md5(microtime()), 0, 45));
        $this->update();
    }
    
    public function getToken(){
        return $this->token;
    }

    public function editCustomersInfoDateAccountLastModified(): void
    {
        $this->customers_info_date_account_last_modified = date("Y-m-d H:i:s");
        $this->save();
    }
    
}