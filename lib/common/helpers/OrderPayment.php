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

namespace common\helpers;

class OrderPayment
{
    CONST OPYS_PENDING = 0;
    CONST OPYS_PROCESSING = 10;
    CONST OPYS_SUCCESSFUL = 20;
    CONST OPYS_REFUSED = 30;
    CONST OPYS_REFUNDED = 40;
    CONST OPYS_CANCELLED = 50;
    CONST OPYS_DISCOUNTED = 100;

/**
 * add new payment record from order AND transaction info
 * @global int $login_id admin id
 * @param \common\classes\Order $orderInstance
 * @param float|bool $orderPaymentAmount order total amount
 * @param int $ordersPaymentStatus payment status code
 * @param array $transactionInformationArray ['id' => , 'status' 'commentary' 'date', 'parent_id', 'fulljson']
 * @return \common\models\OrdersPayment|boolean
 */
    public static function createDebitFromOrder($orderInstance = null, $orderPaymentAmount = false, $ordersPaymentStatus = false, $transactionInformationArray = [], $deferred = 0)
    {
        $return = false;
        if ($orderInstance instanceof \common\classes\Order) {
            if ($orderPaymentAmount === false) {
                $orderPaymentAmount = $orderInstance->info['total_inc_tax'];
            }
            $orderPaymentAmount = (float)$orderPaymentAmount;
            if ($orderPaymentAmount == 0) {
                return $return;
            }
            $ordersPaymentStatus = (int)(($ordersPaymentStatus === false) ? self::OPYS_PENDING : $ordersPaymentStatus);
            $ordersPaymentStatusList = self::getStatusList();
            $ordersPaymentStatus = (!isset($ordersPaymentStatusList[$ordersPaymentStatus]) ? self::OPYS_PENDING : $ordersPaymentStatus);
            unset($ordersPaymentStatusList);
            $transactionInformationArray = (is_array($transactionInformationArray) ? $transactionInformationArray : []);

            if (empty($orderInstance->order_id) && !empty($orderInstance->parent_id)) {
              $order_id = (int)$orderInstance->parent_id;
            } else {
              $order_id = (int)$orderInstance->order_id;
            }
            $paymentClass = (!empty($transactionInformationArray['payment_class']) ? $transactionInformationArray['payment_class'] : $orderInstance->info['payment_class']);
            $paymentMethod = (!empty($transactionInformationArray['payment_method']) ? $transactionInformationArray['payment_method'] : $orderInstance->info['payment_method']);

            $orderPaymentRecord = new \common\models\OrdersPayment();
            $orderPaymentRecord->orders_payment_id_parent = (!empty($transactionInformationArray['parent_id']) ? $transactionInformationArray['parent_id'] : 0);
            $orderPaymentRecord->orders_payment_order_id = $order_id;
            $orderPaymentRecord->orders_payment_module = trim($paymentClass);
            $orderPaymentRecord->orders_payment_module_name = trim($paymentMethod);
            $orderPaymentRecord->orders_payment_is_credit = 0;
            $orderPaymentRecord->deferred = (int) $deferred;
            $orderPaymentRecord->orders_payment_status = $ordersPaymentStatus;
            $orderPaymentRecord->orders_payment_amount = $orderPaymentAmount;
            $orderPaymentRecord->orders_payment_currency = trim($orderInstance->info['currency']);
            $orderPaymentRecord->orders_payment_currency_rate = (float)$orderInstance->info['currency_value'];
            $orderPaymentRecord->orders_payment_snapshot = json_encode(self::getOrderPaymentSnapshot($orderInstance));
            $orderPaymentRecord->orders_payment_transaction_id = trim(isset($transactionInformationArray['id']) ? $transactionInformationArray['id'] : '');
            $orderPaymentRecord->orders_payment_transaction_status = trim(isset($transactionInformationArray['status']) ? $transactionInformationArray['status'] : '');
            $orderPaymentRecord->orders_payment_transaction_commentary = trim(isset($transactionInformationArray['commentary']) ? $transactionInformationArray['commentary'] : '');
            $orderPaymentRecord->orders_payment_transaction_date = trim(isset($transactionInformationArray['date']) ? $transactionInformationArray['date'] : '0000-00-00 00:00:00');
            if (!empty($transactionInformationArray['fulljson'])) {
              $orderPaymentRecord->orders_payment_transaction_full = trim($transactionInformationArray['fulljson']);
            }
            global $login_id;
            $orderPaymentRecord->orders_payment_admin_create = (int)$login_id;
            unset($login_id);
            $orderPaymentRecord->orders_payment_date_create = date('Y-m-d H:i:s');
            try {
                if ($orderPaymentRecord->save()) {
                    $return = $orderPaymentRecord;
                }
            } catch (\Exception $exc) {
              \Yii::warning($exc->getMessage());
            }
            unset($orderPaymentRecord);
        } else {
          \Yii::warning('createDebitFromOrder - not order: ' . get_class($orderInstance));
        }
        unset($transactionInformationArray);
        unset($ordersPaymentStatus);
        unset($orderPaymentAmount);
        unset($orderInstance);
        return $return;
    }

/**
 *
 * @global int $login_id
 * @param string $orderPaymentModule
 * @param string $orderPaymentTransactionId
 * @return \common\models\OrdersPayment|boolean
 */
    public static function searchRecord($orderPaymentModule = '', $orderPaymentTransactionId = '')
    {
        $orderPaymentModule = trim($orderPaymentModule);
        $orderPaymentTransactionId = trim($orderPaymentTransactionId);
        if ($orderPaymentModule == '' OR $orderPaymentTransactionId == '') {
            return false;
        }
        $orderPaymentRecord = \common\models\OrdersPayment::find()
            ->where(['orders_payment_module' => $orderPaymentModule])
            ->andWhere(['orders_payment_transaction_id' => $orderPaymentTransactionId])
            ->orderBy(['orders_payment_date_create' => SORT_DESC, 'orders_payment_id' => SORT_DESC])
            ->one();
        if (!($orderPaymentRecord instanceof \common\models\OrdersPayment)) {
            $orderPaymentRecord = new \common\models\OrdersPayment();
            $orderPaymentRecord->orders_payment_module = $orderPaymentModule;
            $orderPaymentRecord->orders_payment_transaction_id = $orderPaymentTransactionId;
            $orderPaymentRecord->orders_payment_status = self::OPYS_PENDING;
            global $login_id;
            $orderPaymentRecord->orders_payment_admin_create = (int)$login_id;
            unset($login_id);
        }
        return $orderPaymentRecord;
    }

    public static function getArrayByOrderId($orderId = 0, $asArray = true)
    {
        $return = [];
        foreach ((\common\models\OrdersPayment::find()
            ->where(['orders_payment_order_id' => (int)$orderId])
            ->orderBy(['orders_payment_date_create' => SORT_ASC])
            ->asArray($asArray)->all())
                as $orderPaymentRecord
        ) {
            $return[] = $orderPaymentRecord;
        }
        unset($orderPaymentRecord);
        return $return;
    }

    public static function getArrayParentByOrderId($orderId = 0, $asArray = true)
    {
        $return = [];
        foreach ((\common\models\OrdersPayment::find()
            ->where(['orders_payment_order_id' => (int)$orderId])
            ->andWhere(['orders_payment_id_parent' => 0])
            ->asArray($asArray)->all())
                as $orderPaymentRecord
        ) {
            $return[] = $orderPaymentRecord;
        }
        unset($orderPaymentRecord);
        return $return;
    }

    public static function getArrayChildByParentId($orderPaymentIdParent = 0, $asArray = true)
    {
        $return = [];
        foreach ((\common\models\OrdersPayment::find()
            ->where(['orders_payment_id_parent' => (int)$orderPaymentIdParent])
            ->asArray($asArray)->all())
                as $orderPaymentRecord
        ) {
            $return[] = $orderPaymentRecord;
        }
        unset($orderPaymentRecord);
        return $return;
    }

    /** @deprecated hueta
     * calculates only transactions w/o parent_id (s kakogo X??)
     * [ PP order -> ] authorisation -> capture -> refund - ZHOPA
    */
    public static function getArrayStatusByOrderIdTotal($orderId = 0, $orderTotal = 0)
    {
        $return = false;
        $orderId = (int)$orderId;
        $orderTotal = (float)$orderTotal;
        if ($orderTotal < 0) {
            $orderTotal = 0;
        }
        $debit = 0;
        $credit = 0;
        $discount = 0;
        foreach (self::getArrayParentByOrderId($orderId) as $orderPaymentParentRecord) {
            $orderPaymentParentRecord['orders_payment_amount'] = (float)(((float)$orderPaymentParentRecord['orders_payment_amount'] <= 0)
                ? 0 : $orderPaymentParentRecord['orders_payment_amount']
            );
            $orderPaymentParentRecord['orders_payment_currency_rate'] = (float)(((float)$orderPaymentParentRecord['orders_payment_currency_rate'] <= 0)
                ? 1 : $orderPaymentParentRecord['orders_payment_currency_rate']
            );
            if (in_array((int)$orderPaymentParentRecord['orders_payment_status'], [self::OPYS_SUCCESSFUL, self::OPYS_REFUNDED, self::OPYS_DISCOUNTED])) {
                if ((int)$orderPaymentParentRecord['orders_payment_status'] == self::OPYS_SUCCESSFUL) {
                    $debit += ($orderPaymentParentRecord['orders_payment_amount'] / $orderPaymentParentRecord['orders_payment_currency_rate']);
                } elseif ((int)$orderPaymentParentRecord['orders_payment_status'] == self::OPYS_REFUNDED) {
                    $credit += ($orderPaymentParentRecord['orders_payment_amount'] / $orderPaymentParentRecord['orders_payment_currency_rate']);
                } elseif ((int)$orderPaymentParentRecord['orders_payment_status'] == self::OPYS_DISCOUNTED) {
                    $discount += ($orderPaymentParentRecord['orders_payment_amount'] / $orderPaymentParentRecord['orders_payment_currency_rate']);
                }
                foreach (self::getArrayChildByParentId($orderPaymentParentRecord['orders_payment_id']) as $orderPaymentChildRecord) {
                    if (in_array((int)$orderPaymentChildRecord['orders_payment_status'], [self::OPYS_REFUNDED, self::OPYS_DISCOUNTED])) {
                        $orderPaymentChildRecord['orders_payment_amount'] = (float)(((float)$orderPaymentChildRecord['orders_payment_amount'] <= 0)
                            ? 0 : $orderPaymentChildRecord['orders_payment_amount']
                        );
                        $orderPaymentChildRecord['orders_payment_currency_rate'] = (float)((float)$orderPaymentChildRecord['orders_payment_currency_rate'] <= 0
                            ? 1 : $orderPaymentChildRecord['orders_payment_currency_rate']
                        );
                        if ((int)$orderPaymentParentRecord['orders_payment_status'] == self::OPYS_SUCCESSFUL) {
                            if ((int)$orderPaymentChildRecord['orders_payment_status'] == self::OPYS_DISCOUNTED) {
                                $discount += ($orderPaymentChildRecord['orders_payment_amount'] / $orderPaymentChildRecord['orders_payment_currency_rate']);
                            } elseif ((int)$orderPaymentChildRecord['orders_payment_status'] == self::OPYS_REFUNDED) {
                                $credit += ($orderPaymentChildRecord['orders_payment_amount'] / $orderPaymentChildRecord['orders_payment_currency_rate']);
                            }
                        } elseif ((int)$orderPaymentParentRecord['orders_payment_status'] == self::OPYS_REFUNDED) {
                            // ???
                        } elseif ((int)$orderPaymentParentRecord['orders_payment_status'] == self::OPYS_DISCOUNTED) {
                            if ((int)$orderPaymentChildRecord['orders_payment_status'] == self::OPYS_REFUNDED) {
                                $discount -= ($orderPaymentChildRecord['orders_payment_amount'] / $orderPaymentChildRecord['orders_payment_currency_rate']);
                            }
                        }
                    }
                }
                unset($orderPaymentChildRecord);
            } elseif (in_array((int)$orderPaymentParentRecord['orders_payment_status'], [])) {

            }
        }
        unset($orderPaymentParentRecord);
        $discount = self::toAmount($discount);
        $discount = ($discount <= 0 ? 0 : $discount);
        $return = [
            'status' => 0,
            'total' => $orderTotal,
            'debit' => self::toAmount($debit),
            'credit' => self::toAmount($credit),
            'discount' => $discount,
            'paid' => 0,
            'due' => 0,
            'over' => 0
        ];
        $return['paid'] = (($return['debit'] + $return['discount']) - $return['credit']);
        $return['due'] = ($return['total'] - $return['paid']);
        if ($return['due'] > 0) {
            $return['status'] = 1;
        } elseif ($return['due'] < 0) {
            $return['status'] = -1;
            $return['over'] = abs($return['due']);
            $return['due'] = 0;
        }
        unset($credit);
        unset($debit);
        return $return;
    }

    /**
     * [draft] - calculate paid and refund total values in order payment table
     * @param int $orderId
     * @param float $orderTotal
     * @return array|false array:     [status] => 1 -  has due 0 eq -1 overpay
    [total] => 17.45 - order total (NOT calculated, passed in params)
    [debit] => 17.45 - successful payment transactions (OPYS_SUCCESSFUL)
    [credit] => 1.15  - refunded transactions (OPYS_REFUNDED)
    [discount] => 0 ??
    [paid] => 16.3,      [due] => 1.15   [over] => 0 calculated based on values above
     */
    public static function getTotalStatusArray($orderId = 0, $orderTotal = 0)
    {
        $return = false;
        $orderId = (int)$orderId;
        $orderTotal = (float)$orderTotal;
        if ($orderTotal < 0) {
            $orderTotal = 0;
        }
        $debit = 0;
        $credit = 0;
        $discount = 0;
        $q = \common\models\OrdersPayment::find()
            ->where(['orders_payment_order_id' => (int)$orderId])
            ->asArray();
        foreach ($q->all() as $orderPaymentParentRecord) {
            $orderPaymentParentRecord['orders_payment_amount'] = (float)(((float)$orderPaymentParentRecord['orders_payment_amount'] <= 0)
                ? 0 : $orderPaymentParentRecord['orders_payment_amount']
            );
            $orderPaymentParentRecord['orders_payment_currency_rate'] = (float)(((float)$orderPaymentParentRecord['orders_payment_currency_rate'] <= 0)
                ? 1 : $orderPaymentParentRecord['orders_payment_currency_rate']
            );
            if (in_array((int)$orderPaymentParentRecord['orders_payment_status'], [self::OPYS_SUCCESSFUL, self::OPYS_REFUNDED, self::OPYS_DISCOUNTED])) {
                if ((int)$orderPaymentParentRecord['orders_payment_status'] == self::OPYS_SUCCESSFUL) {
                    $debit += ($orderPaymentParentRecord['orders_payment_amount'] / $orderPaymentParentRecord['orders_payment_currency_rate']);
                } elseif ((int)$orderPaymentParentRecord['orders_payment_status'] == self::OPYS_REFUNDED) {
                    $credit += ($orderPaymentParentRecord['orders_payment_amount'] / $orderPaymentParentRecord['orders_payment_currency_rate']);
                } elseif ((int)$orderPaymentParentRecord['orders_payment_status'] == self::OPYS_DISCOUNTED) {
                    $discount += ($orderPaymentParentRecord['orders_payment_amount'] / $orderPaymentParentRecord['orders_payment_currency_rate']);
                }
            } elseif (in_array((int)$orderPaymentParentRecord['orders_payment_status'], [])) {

            }
        }
        unset($orderPaymentParentRecord);
        $discount = self::toAmount($discount);
        $discount = ($discount <= 0 ? 0 : $discount);
        $return = [
            'status' => 0,
            'total' => $orderTotal,
            'debit' => self::toAmount($debit),
            'credit' => self::toAmount($credit),
            'discount' => $discount,
            'paid' => 0,
            'due' => 0,
            'over' => 0
        ];
        $return['paid'] = (($return['debit'] + $return['discount']) - $return['credit']);
        $return['due'] = ($return['total'] - $return['paid']);
        if ($return['due'] > 0) {
            $return['status'] = 1;
        } elseif ($return['due'] < 0) {
            $return['status'] = -1;
            $return['over'] = abs($return['due']);
            $return['due'] = 0;
        }
        unset($credit);
        unset($debit);
        return $return;
    }

    public static function getAmountAvailable($orderPaymentRecord = 0)
    {
        $return = 0;
        $orderPaymentRecord = self::getRecord($orderPaymentRecord);
        if ($orderPaymentRecord instanceof \common\models\OrdersPayment) {
            if ($orderPaymentRecord->orders_payment_id_parent == 0) {
                $return = (float)$orderPaymentRecord->orders_payment_amount;
                foreach (self::getArrayChildByParentId($orderPaymentRecord->orders_payment_id) as $paymentChildRecord) {
                    if (in_array($paymentChildRecord['orders_payment_status'], [
                        self::OPYS_REFUNDED,
                        self::OPYS_DISCOUNTED
                    ])) {
                        $return -= (float)$paymentChildRecord['orders_payment_amount'];
                    }
                }
                unset($paymentChildRecord);
            } else {
                $return = self::getAmountAvailable($orderPaymentRecord->orders_payment_id_parent);
            }
        }
        return $return;
    }

    private static function toAmount($amount = 0)
    {
        return round((float)$amount, 2);
    }

    public static function getRecord($orderPaymentId = 0)
    {
        return ($orderPaymentId instanceof \common\models\OrdersPayment
            ? $orderPaymentId
            : \common\models\OrdersPayment::findOne(['orders_payment_id' => (int)$orderPaymentId])
        );
    }

    public static function getStatusList($forStatus = false, $isCredit = false)
    {
        $return = [
            self::OPYS_PENDING => TEXT_STATUS_OPYS_PENDING,
            self::OPYS_PROCESSING => TEXT_STATUS_OPYS_PROCESSING,
            self::OPYS_SUCCESSFUL => TEXT_STATUS_OPYS_SUCCESSFUL,
            self::OPYS_REFUSED => TEXT_STATUS_OPYS_REFUSED,
            self::OPYS_REFUNDED => TEXT_STATUS_OPYS_REFUNDED,
            self::OPYS_CANCELLED => TEXT_STATUS_OPYS_CANCELLED,
            self::OPYS_DISCOUNTED => TEXT_STATUS_OPYS_DISCOUNTED
        ];
        $isCredit = ((int)$isCredit > 0 ? true : false);
        if ($forStatus !== false) {
            switch ($forStatus) {
                case self::OPYS_PENDING:
                    unset($return[self::OPYS_REFUSED]);
                    unset($return[self::OPYS_REFUNDED]);
                    unset($return[self::OPYS_DISCOUNTED]);
                break;
                case self::OPYS_PROCESSING:
                    unset($return[self::OPYS_REFUNDED]);
                    unset($return[self::OPYS_CANCELLED]);
                    unset($return[self::OPYS_DISCOUNTED]);
                break;
                case self::OPYS_SUCCESSFUL:
                    //unset($return[self::OPYS_PENDING]);
                    //unset($return[self::OPYS_PROCESSING]);
                    //unset($return[self::OPYS_REFUSED]);
                    unset($return[self::OPYS_REFUNDED]);
                    unset($return[self::OPYS_CANCELLED]);
                    unset($return[self::OPYS_DISCOUNTED]);
                break;
                case self::OPYS_REFUSED:
                    unset($return[self::OPYS_PROCESSING]);
                    unset($return[self::OPYS_SUCCESSFUL]);
                    unset($return[self::OPYS_REFUNDED]);
                    unset($return[self::OPYS_CANCELLED]);
                    unset($return[self::OPYS_DISCOUNTED]);
                break;
                case self::OPYS_REFUNDED:
                    unset($return[self::OPYS_PENDING]);
                    unset($return[self::OPYS_PROCESSING]);
                    unset($return[self::OPYS_SUCCESSFUL]);
                    unset($return[self::OPYS_REFUSED]);
                    unset($return[self::OPYS_CANCELLED]);
                break;
                case self::OPYS_CANCELLED:
                    unset($return[self::OPYS_PROCESSING]);
                    unset($return[self::OPYS_SUCCESSFUL]);
                    unset($return[self::OPYS_REFUSED]);
                    unset($return[self::OPYS_REFUNDED]);
                    unset($return[self::OPYS_DISCOUNTED]);
                break;
                case self::OPYS_DISCOUNTED:
                    unset($return[self::OPYS_PENDING]);
                    unset($return[self::OPYS_PROCESSING]);
                    unset($return[self::OPYS_SUCCESSFUL]);
                    unset($return[self::OPYS_REFUSED]);
                    //unset($return[self::OPYS_REFUNDED]);
                    unset($return[self::OPYS_CANCELLED]);
                break;
            }
        }
        return $return;
    }

    public static function getOrderPaymentSnapshot($orderInstance = null)
    {
        $return = [
            'product' => [],
            'total' => [
                'subtotal' => [
                    'price_exc' => 0,
                    'price_inc' => 0
                ],
                'shipping' => [
                    'module' => '',
                    'price_exc' => 0,
                    'price_inc' => 0
                ],
                'tax' => [
                    'price_exc' => 0,
                    'price_inc' => 0
                ],
                'discount' => [
                    'price_exc' => 0,
                    'price_inc' => 0
                ],
                'coupon' => [
                    'id' => 0,
                    'type' => '',
                    'price_exc' => 0,
                    'price_inc' => 0
                ],
                'total' => [
                    'price_exc' => 0,
                    'price_inc' => 0
                ],
                'paid' => [
                    'price_exc' => 0,
                    'price_inc' => 0
                ],
                'due' => [
                    'price_exc' => 0,
                    'price_inc' => 0
                ],
                'refund' => [
                    'price_exc' => 0,
                    'price_inc' => 0
                ]
            ]
        ];
        if ($orderInstance instanceof \common\classes\Order) {
            foreach ($orderInstance->products as $orderProduct) {
                $orderProductArray = [
                    'prid' => (int)$orderProduct['id'],
                    'uprid' => \common\helpers\Inventory::normalize_id_excl_virtual($orderProduct['template_uprid']),
                    'model' => trim($orderProduct['model']),
                    'tax_rate' => (float)$orderProduct['tax'],
                    'price_exc' => (float)$orderProduct['final_price'],
                    'price_inc' => (float)$orderProduct['final_price'],
                    'qty' => (int)$orderProduct['qty'],
                    'qty_cnld' => (int)($orderProduct['qty_cnld'] ?? 0),
                    'qty_rcvd' => (int)($orderProduct['qty_rcvd'] ?? 0),
                    'qty_dspd' => (int)($orderProduct['qty_dspd'] ?? 0),
                    'qty_dlvd' => (int)($orderProduct['qty_dlvd'] ?? 0)
                ];
                $orderProductArray['price_inc'] = round((float)$orderProductArray['price_exc'] * (1 + $orderProductArray['tax_rate'] / 100), 2);
                $return['product'][] = $orderProductArray;
                unset($orderProductArray);
            }
            unset($orderProduct);
            foreach ($orderInstance->totals as $orderTotal) {
                $code = strtolower(isset($orderTotal['code']) ? substr($orderTotal['code'], 3) : '');
                switch ($code) {
                    case 'subtotal':
                    case 'shipping':
                    case 'tax':
                    case 'total':
                        $orderTotalArray = [
                            'price_exc' => (float)$orderTotal['value_exc_vat'],
                            'price_inc' => (float)$orderTotal['value_inc_tax']
                        ];
                        if ($code == 'shipping') {
                            $orderTotalArray['module'] = trim($orderInstance->info['shipping_class']);
                        } elseif ($code == 'coupon') {
                            $orderTotalArray['id'] = 0; //???
                            $orderTotalArray['type'] = ''; //???
                        }
                        $return['total'][$code] = $orderTotalArray;
                        unset($orderTotalArray);
                    break;
                    case 'paid':
                    case 'due':
                    case 'refund':
                        $orderTotalArray = [
                            'price_exc' => (float)$orderTotal['value_inc_tax'],
                            'price_inc' => (float)$orderTotal['value_inc_tax']
                        ];
                        $return['total'][$code] = $orderTotalArray;
                        unset($orderTotalArray);
                    break;
                }
            }
            unset($orderTotal);
        }
        return $return;
    }

/**
 * update transaction details, order paid/due/refunded totals, <order status, and send notification>.
 * payment class getTransactionDetails and parseTransactionDetails are called
 * init payment class according appropriate platform details.
 * @param array|common\models\OrdersPayment $data
 * @param \common\classes\modules\TransactionalInterface $class
 * @param \common\services\OrderManager $orderManager
 * @param bool $updateStatusAndNotify
 * @return true|string true or error message
 */
    public static function updateTransactionDetails($data, &$class, &$orderManager, $updateStatusAndNotify = true) {
      $rs = true;

      if ($data instanceof \common\models\OrdersPayment) {
        $data = $data->attributes;
      }
      
      if (is_object($class)
        && $class instanceof \common\classes\modules\TransactionalInterface
        && method_exists($class, 'parseTransactionDetails') ) {
        try {

          $details = $class->getTransactionDetails($data['orders_payment_transaction_id']);
          /** @var \common\services\PaymentTransactionManager $tManager */
          $tManager = $orderManager->getTransactionManager($class);

          $response = $class->parseTransactionDetails($details);
          
          /** @var \common\classes\Order $order */
          $order = $orderManager->getOrderInstanceWithId('\common\classes\Order', $data['orders_payment_order_id']);

          $ret = $tManager->updatePaymentTransaction($response['transaction_id'], array_merge($data, $response));
          if ($ret) { //updated transaction - update totals, order status and notify customer if required
            $updated = false;
            if ($order) {
              $updated = $order->updatePaidTotals();
            }
            if ($updated) { //update order status and notify customer if required
              $status = '';
              if (isset($updated['paid']) && $updated['details']['debit']>0) {
                //if ($updated['details']['status']>0) {// has due
                if (abs(
                    round($updated['details']['total'], 2)-
                    round($updated['details']['debit'], 2)
                    ) < 0.01) {
                  $status = $class->paidOrderStatus();
                } else {
                  $status = $class->partlyPaidOrderStatus();
                }
              } elseif (isset($updated['refund']) && ($updated['details']['credit']>0 || $updated['details']['due']>0)) {
                  $tmp = (($updated['details']['credit']??0)>0 ? $updated['details']['credit'] : $updated['details']['due']);
                if (abs(
                    round($updated['details']['total'], 2) - 
                    round($tmp, 2)
                    //round($updated['details']['credit'], 2)
                    ) < 0.01) {
                  $status = $class->refundOrderStatus();
                } else {
                  $status = $class->partialRefundOrderStatus();
                }
              }
              if ($updateStatusAndNotify && !empty($status) && $status != $order->info['order_status']) {
                $order->update_status_and_notify($status);
              }

            }
          } elseif (is_null($ret)) {
            \Yii::warning(" #### " .print_r($response, 1), 'TLDEBUG');
            $rs = $data['orders_payment_transaction_id'] . ' - ' . $class->code . ' - ' .  TEXT_MESSAGE_ERROR_INCORRECT_TRANSACTION;
          }

        } catch (Exception $ex) {
          \Yii::warning(" #### " .print_r($ex->getMessage(), 1), 'TLDEBUG');
          $rs = $data['orders_payment_transaction_id'] . ' - ' . $class->code . ' - ' .  $ex->getMessage();
        }
      } else {
        //backward compatibility
        /** @var \common\services\PaymentTransactionManager $tManager */
        $tManager = $orderManager->getTransactionManager($class);

        $details = $class->getTransactionDetails($data['orders_payment_transaction_id'], $tManager);

      }
      return $rs;
    }

    /**
     *
     * @param int $id
     * @return int 
     */
    public static function hasChildren($id) {
      $q = \common\models\OrdersPayment::find()->andWhere(['orders_payment_id_parent' => (int)$id]);
      return $q->count();
    }


}