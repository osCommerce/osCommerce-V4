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

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "orders_status_history".
 *
 * @property int $orders_status_history_id
 * @property int $orders_id
 * @property int $orders_status_id
 * @property string $date_added
 * @property int $customer_notified
 * @property string $comments
 * @property int $admin_id
 * @property string $smscomments
 */
class OrdersStatusHistory extends ActiveRecord {

    /**
     * set table name
     * @return string
     */
    public static function tableName() {
        return 'orders_status_history';
    }

    public function behaviors() {
        return [
            'date_added' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added'],
                ],
                'value' => new \yii\db\Expression('NOW()'),
            ]
        ];
    }

    public function getOrder() {
        return $this->hasOne(Orders::className(), ['orders_id' => 'orders_id']);
    }

    public function getStatus() {
        return $this->hasOne(OrdersStatus::className(), ['orders_status_id' => 'orders_status_id']);
    }

    public function getGroup() {
        return $this->hasOne(OrdersStatusGroups::className(), ['orders_status_groups_id' => 'orders_status_groups_id'])->via('status');
    }

    public static function create($orders_id, $orders_status_id, $customer_notified = 0, $comments = '', $admin_id = 0, $smscomments = '') {
        $raw = new static();
        $raw->orders_id = $orders_id;
        $raw->orders_status_id = $orders_status_id;
        $raw->customer_notified = $customer_notified;
        $raw->comments = $comments;
        $raw->admin_id = $admin_id;
        $raw->smscomments = $smscomments;
        return $raw;
    }
/**
 *
 * @global type $login_id
 * @param type $orderRecord
 * @param type $orderStatus
 * @param type $commentary
 * @param type $customerNotified
 * @param type $commentarySms
 * @param Expression $dateAdded
 * @return boolean
 */
    public static function write($orderRecord = 0, $orderStatus = 0, $commentary = '', $customerNotified = false, $commentarySms = '', $dateAdded = null)
    {
        $return = false;
        $orderRecord = \common\helpers\Order::getRecord($orderRecord);
        if ($orderRecord instanceof \common\models\Orders) {
            if ($orderStatus === false) {
                $orderStatus = $orderRecord->orders_status;
            }
            $orderStatus = (int)$orderStatus;
            if ( empty($dateAdded) ) {
                $dateAdded = new Expression('NOW()');
            }
            try {
                global $login_id;
                $orderStatusHistory = new self();
                $orderStatusHistory->detachBehavior('date_added');
                $orderStatusHistory->orders_id = (int)$orderRecord->orders_id;
                $orderStatusHistory->orders_status_id = $orderStatus;
                $orderStatusHistory->date_added = $dateAdded;
                $orderStatusHistory->admin_id = (int)(isset($login_id) ? $login_id : 0);
                $orderStatusHistory->comments = trim($commentary);
                $orderStatusHistory->customer_notified = ((int)$customerNotified > 0 ? 1 : 0);
                $orderStatusHistory->smscomments = trim($commentarySms);
                $orderStatusHistory->save();
                $return = true;
            } catch (\Exception $exc) {
              \Yii::warning($exc->getMessage() . ' ' . $exc->getTraceAsString());
            }
            unset($orderStatusHistory);
            unset($login_id);
        }
        unset($customerNotified);
        unset($commentarySms);
        unset($orderStatus);
        unset($orderRecord);
        unset($commentary);
        return $return;
    }
}