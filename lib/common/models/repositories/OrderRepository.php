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

use common\models\Orders;
use common\models\OrdersStatusHistory;

class OrderRepository
{
    /**
     * @param int $order_id
     * @return OrdersStatusHistory
     */
    public function getLastHistoryStatus(int $order_id): OrdersStatusHistory
    {
        $order = $this->getById($order_id);
        $status = $order->getOrdersStatusHistory()->orderBy('orders_status_history_id DESC')->one();
        return $status;
    }

    /**
     * @param int|array $orderId
     * @param bool $asArray
     * @return Orders|Orders[]
     */
    public function getById($orderId, bool $asArray = false)
    {
        $order = $this->findById($orderId);
        if (!$order) {
            throw new \DomainException('Order not found');
        }
        return $order;
    }

    /**
     * @param int|array $orderId
     * @param bool $asArray
     * @return array|Orders|Orders[]|null
     */
    public function findById($orderId, bool $asArray = false)
    {
        $order = Orders::find()
            ->where(['orders_id' => $orderId])->asArray($asArray);
        if (is_array($orderId)) {
            return $order->all();
        }
        return $order->limit(1)->one();
    }

//    used in UpSell extension only
//    public function getCrossUpSellingProductsForDelivery()
//    {
//        $orders = Orders::find()
//            ->crossUpEmailSend(false)
//            ->joinWith("ordersProducts.upsell us")
//            ->joinWith("ordersProducts.xsell xs")
//            ->joinWith("customer c")
//            ->select([
//                'orders.customers_id',
//                'orders.customers_firstname',
//                'orders.customers_lastname',
//                'orders.customers_email_address',
//                'GROUP_CONCAT(DISTINCT orders.orders_id) as orders_ids',
//                'GROUP_CONCAT(DISTINCT xs.xsell_id) as xsells',
//                'GROUP_CONCAT(DISTINCT us.upsell_id) as upsells'
//            ])
//            ->groupBy('orders.customers_id')
//            ->asArray()
//            ->all();
//        return $orders;
//    }

    /**
     * @param Orders $order
     * @param array $params
     * @param bool $validate
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Orders $order, array $params = [], bool $validate = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$order->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $order->setAttributes($params, $safeOnly);
        if ($order->update($validate, array_keys($params)) === false) {
            return $order->getErrors();
        }
        return true;
    }

    /**
     * @param Orders $order
     * @param bool $validation
     */
    public function save(Orders $order, bool $validation = false)
    {
        if (!$order->save($validation)) {
            throw new \DomainException('Order saving  error.');
        }
    }

    /**
     * @param int $customerId
     * @param bool $asArray
     * @return array|Orders[]
     */
    public function findByCustomer(int $customerId, bool $asArray = false)
    {
        return Orders::find()->where(['customers_id' => $customerId])->asArray($asArray)->all();
    }

    /**
     * @param Orders $order
     * @param int $orderStatus
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function changeStatus(Orders $order, int $orderStatus)
    {
        return $this->edit($order, ['orders_status' => $orderStatus]);
    }

    /**
     * @param int $customerId
     * @return bool
     */
    public function existByCustomer(int $customerId): bool
    {
        return Orders::find()->where(['customers_id' => $customerId])->exists();
    }
}
