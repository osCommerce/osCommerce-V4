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

use common\models\Customers;
use common\models\GlobalPaymentsTransactions;

class GlobalPaymentsTransactionsRepository
{
    /**
     * @param int|array $orderId
     * @param bool $asArray
     * @return array|GlobalPaymentsTransactions[]
     */
    public function findByOrderId($orderId, bool $asArray = false): array
    {
        $gpTransactions = GlobalPaymentsTransactions::find()
            ->where(['orders_id' => $orderId])
            ->asArray($asArray);
        return $gpTransactions->all();
    }

    /**
     * @param string $transactionId
     * @param bool $asArray
     * @return array|GlobalPaymentsTransactions|null
     */
    public function findTransaction(string $transactionId, bool $asArray = false)
    {
        $gpTransaction = GlobalPaymentsTransactions::find()
            ->where(['transaction_id' => $transactionId])
            ->asArray($asArray);
        return $gpTransaction->limit(1)->one();
    }
    /**
     * @param int|array $orderId
     * @param bool $asArray
     * @return GlobalPaymentsTransactions[]
     */
    public function getByOrderId($orderId, bool $asArray = false): array
    {
        $gpTransactions = $this->findByOrderId($orderId, $asArray);
        if (!$gpTransactions) {
            throw new NotFoundException('Order Status History not found');
        }
        return $gpTransactions;
    }

    /**
     * @param GlobalPaymentsTransactions $gpTransaction
     * @param array $params
     * @param bool $validate
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(GlobalPaymentsTransactions $gpTransaction, array $params = [], bool $validate = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$gpTransaction->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $gpTransaction->setAttributes($params, $safeOnly);
        if ($gpTransaction->update($validate, array_keys($params)) === false) {
            return $gpTransaction->getErrors();
        }
        return true;
    }

    /**
     * @param GlobalPaymentsTransactions $gpTransaction
     * @param bool $validate
     */
    public function save(GlobalPaymentsTransactions $gpTransaction, bool $validate = false)
    {
        if (!$gpTransaction->save($validate)) {
            throw new \RuntimeException('Global Payments Transactions saving  error.');
        }
    }

    /**
     * @param string $transactionId
     * @param string $gpOrderId
     * @param string $storeName
     * @param Customers $customer
     * @param array $responseValues
     * @return GlobalPaymentsTransactions
     */
    public function create (string $transactionId, string $gpOrderId, string $storeName, Customers $customer, array $responseValues): GlobalPaymentsTransactions
    {
        $transaction = GlobalPaymentsTransactions::findOne(['transaction_id' => $transactionId]);
        if (empty($transaction)) {
          $transaction = new GlobalPaymentsTransactions();
        }
        $customer_name = (!empty($customer->customers_firstname)?$customer->customers_firstname . ' ':'');
        $customer_name .= ($customer->customers_firstname??'');
        
        $transaction->transaction_id = $transactionId;
        $transaction->gp_order_id = $responseValues['ORDER_ID'] ?? '';
        $transaction->order_id = $gpOrderId;
        $transaction->customer_id = ($customer->customers_id??0);
        $transaction->raw = json_encode($responseValues);
        $transaction->customer_name = $customer_name;
        $transaction->store = $storeName;
        $transaction->card_details = $responseValues['SAVED_PMT_DIGITS'] ?? '';
        $transaction->exp_date = $responseValues['SAVED_PMT_EXPDATE'] ?? '';
        $transaction->card_holder_name = $responseValues['SAVED_PMT_NAME'] ?? '';
        $transaction->card_ref = $responseValues['SAVED_PMT_REF'] ?? '';
        $transaction->customer_ref = $responseValues['SAVED_PAYER_REF'] ?? '';
        $transaction->card_type = $responseValues['SAVED_PMT_TYPE'] ?? '';
        $transaction->code = $responseValues['RESULT'] ?? '';
        $transaction->srd = $responseValues['SRD'] ?? '';
        $transaction->amount = isset($responseValues['AMOUNT']) ? (int)$responseValues['AMOUNT'] : 0;
        return $transaction;
    }
}
