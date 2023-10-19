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

class CustomersCreditHistory extends ActiveRecord {

    public static function tableName() {
        return 'customers_credit_history';
    }
    
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added'],
                ],              
                 'value' => new \yii\db\Expression('NOW()'),
            ],
        ];
    }

    public function getAdmin()
    {
        return $this->hasOne(Admin::class, ['admin_id' => 'admin_id']);
    }

    public static function saveCreditHistory(int $customers_id, $amount, $prefix = '+', $currency = '', $currency_value = '', $comment = '', $type = 0, $customer_notified = 0)
    {
        try {
            \common\helpers\Assert::assert($customers_id > 0, 'Customer id is not valid: ' . $customers_id);
            $history = new self();
            $history->setAttributes([
                'customers_id' => $customers_id,
                'credit_prefix' => $prefix,
                'credit_amount' => $amount,
                'currency' => $currency,
                'currency_value' => $currency_value,
                'customer_notified' => $customer_notified,
                'comments' => $comment,
                'admin_id' => \Yii::$app->session->get('login_id', 0),
                'credit_type' => $type
            ], false);
            $history->save(false);
        } catch(\Throwable $e) {
            \common\helpers\Php::handleErrorProd($e);
        }
    }

}