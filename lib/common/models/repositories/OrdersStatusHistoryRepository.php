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

namespace common\models\repositories;

use common\models\OrdersStatusHistory;

class OrdersStatusHistoryRepository
{
    /**
     * @param int|array $orderId
     * @param bool $asArray
     * @return array
     */
    public function findByOrderId($orderId, bool $asArray = false): array
    {
        $statuses = OrdersStatusHistory::find()
            ->where(['orders_id' => $orderId])
            ->asArray($asArray);
        return $statuses->all();
    }

    /**
     * @param $orderId
     * @param bool $asArray
     * @return array
     */
    public function getByOrderId($orderId, bool $asArray = false): array
    {
        $statuses = $this->findByOrderId($orderId, $asArray);
        if (!$statuses) {
            throw new NotFoundException('Order Status History not found');
        }
        return $statuses;
    }

    /**
     * @param OrdersStatusHistory $ordersStatusHistory
     * @param array $params
     * @param bool $validate
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(OrdersStatusHistory $ordersStatusHistory, array $params = [], bool $validate = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$ordersStatusHistory->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $ordersStatusHistory->setAttributes($params, $safeOnly);
        if ($ordersStatusHistory->update($validate, array_keys($params)) === false) {
            return $ordersStatusHistory->getErrors();
        }
        return true;
    }

    /**
     * @param OrdersStatusHistory $ordersStatusHistory
     * @param bool $validate
     */
    public function save(OrdersStatusHistory $ordersStatusHistory, bool $validate = false)
    {
        if (!$ordersStatusHistory->save($validate)) {
            throw new \RuntimeException('Order Status History saving  error.');
        }
    }

    /**
     * @param int $orderId
     * @param int $orderStatusId
     * @param string $comments
     * @param int $customerNotified
     * @param int $adminId
     * @param string $smsComments
     * @return OrdersStatusHistory
     */
    public function create(int $orderId, int $orderStatusId, string $comments = '', int $customerNotified = 0, int $adminId = 0, string $smsComments = ''): OrdersStatusHistory
    {
        $ordersStatusHistory = new OrdersStatusHistory();
        $ordersStatusHistory->orders_id = $orderId;
        $ordersStatusHistory->orders_status_id = $orderStatusId;
        $ordersStatusHistory->customer_notified = $customerNotified;
        $ordersStatusHistory->comments = $comments;
        $ordersStatusHistory->admin_id = $adminId;
        $ordersStatusHistory->smscomments = $smsComments;
        return $ordersStatusHistory;
    }
}
