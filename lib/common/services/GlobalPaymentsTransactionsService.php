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

namespace common\services;


use common\models\Customers;
use common\models\GlobalPaymentsTransactions;
use common\models\repositories\GlobalPaymentsTransactionsRepository;


class GlobalPaymentsTransactionsService
{

    /** @var GlobalPaymentsTransactionsRepository */
    private $globalPaymentsTransactionsRepository;

    public function __construct(GlobalPaymentsTransactionsRepository $globalPaymentsTransactionsRepository)
    {

        $this->globalPaymentsTransactionsRepository = $globalPaymentsTransactionsRepository;
    }

    /**
     * @param int|array $orderId
     * @param bool $asArray
     * @return GlobalPaymentsTransactions[]
     */
    public function getByOrderId($orderId, bool $asArray = false): array
    {
        return $this->globalPaymentsTransactionsRepository->getByOrderId($orderId, $asArray);
    }

    public function findTransaction(string $transactionId, bool $asArray = false)
    {
        return $this->globalPaymentsTransactionsRepository->findTransaction($transactionId, $asArray);
    }
    public function edit(GlobalPaymentsTransactions $gpTransactions, array $params = [], $safeOnly = false)
    {
        return $this->globalPaymentsTransactionsRepository->edit($gpTransactions, $params, $safeOnly);
    }

    public function save(GlobalPaymentsTransactions $gpTransactions)
    {
        $this->globalPaymentsTransactionsRepository->save($gpTransactions);
    }

    public function addGlobalPaymentsTransaction(string $transactionId, string $gpOrderId, string $storeName, Customers $customer, array $responseValues): GlobalPaymentsTransactions
    {
        $transaction = $this->globalPaymentsTransactionsRepository->create($transactionId, $gpOrderId, $storeName, $customer, $responseValues);
        $this->globalPaymentsTransactionsRepository->save($transaction);
        return $transaction;
    }
}
