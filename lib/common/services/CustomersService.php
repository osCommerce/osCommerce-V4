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

use common\components\Customer;
use common\models\AddressBook;
use common\models\Customers;
use common\models\CustomersBasket;
use common\models\CustomersBasketAttributes;
use common\models\CustomersInfo;
use common\models\Orders;
use common\models\repositories\AddressBookRepository;
use common\models\repositories\CustomersInfoRepository;
use common\models\repositories\CustomersRepository;
use common\models\repositories\OrderRepository;


class CustomersService
{

    /** @var CustomersRepository */
    private $customersRepository;
    /** @var CustomersInfoRepository */
    private $customersInfoRepository;
    /** @var TransactionManager */
    private $transactionManager;
    /** @var AddressBookRepository */
    private $addressBookRepository;
    /** @var OrderRepository */
    private $orderRepository;

    public function __construct(
        CustomersRepository $customersRepository,
        CustomersInfoRepository $customersInfoRepository,
        TransactionManager $transactionManager,
        AddressBookRepository $addressBookRepository,
        OrderRepository $orderRepository
    )
    {
        $this->customersRepository = $customersRepository;
        $this->customersInfoRepository = $customersInfoRepository;
        $this->transactionManager = $transactionManager;
        $this->addressBookRepository = $addressBookRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Customers $customer
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function setActive(Customers $customer)
    {
        if($customer->customers_status){
            return true;
        }
        $this->customersRepository->edit($customer,['customers_status' => Customers::STATUS_ACTIVE]);
    }

    /**
     * @param Customers $customer
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function setDisable(Customers $customer)
    {
        if($customer->customers_status){
            return true;
        }
        $this->customersRepository->edit($customer,['customers_status' => Customers::STATUS_DISABLE]);
    }

    public function getMutualSettlementList($start,$length,$platformId = false, $active = false, $isArray = false)
    {
        return $this->customersRepository->getMutualSettlementList($start,$length,$platformId,$active,$isArray);
    }

    public function getMutualSettlementListTotal($platformId = false, $active = false)
    {
        return $this->customersRepository->getMutualSettlementListTotal($platformId,$active);
    }

    /**
     * @param $id
     * @param bool $asArray
     * @return Customers|Customers[]
     */
    public function getById($id, bool $asArray = false)
    {
        return $this->customersRepository->getById($id, $asArray);
    }

    /**
     * @param int $id
     * @param bool $asArray
     * @return Customer|Customer[]
     */
    public function getIdentityById(int $id, bool $asArray = false)
    {
        return $this->customersRepository->getIdentityById($id, $asArray);
    }
    /**
     * @param $id
     * @param bool $asArray
     * @return Customers|Customers[]
     */
    public function findById($id, bool $asArray = false)
    {
        return $this->customersRepository->findById($id);
    }

    public function generateLoginToken()
    {
        return 'CT-' . strtoupper(substr(md5(microtime()), 0, 45));
    }

    /**
     * @param Customers $customer
     * @return string
     */
    public function setLoginToken(Customers $customer)
    {
        $token = $this->generateLoginToken();
        $customerInfo = $this->customersInfoRepository->getByCustomer($customer->customers_id);
        $this->customersInfoRepository->edit($customerInfo,['token'=> $token,'time_long' => new \yii\db\Expression('NOW()')]);
        return $token;
    }

    /**
     * @param string|null $term
     * @param bool $active
     * @param int $limit
     * @param int $offset
     * @param bool $asArray
     * @param array $fields
     * @param string $index
     * @return array|Customers[]
     */
    public function findAllByTermLimit(string $term = null, bool $active = true, int $limit = 20, int $offset = 0 ,  bool $asArray = false , array $fields = [], string $index = null)
    {
        return $this->customersRepository->findAllByTermLimit($term, $active, $limit, $offset, $asArray, $fields, $index);
    }

    /**
     * @param int $customerId
     * @return bool
     */
    public function deleteCustomersWhoseNotMakeOrders(int $customerId): bool
    {
        if (!$this->orderRepository->existByCustomer($customerId)) {
            $this->deleteCustomerInfo($customerId, true);
            return true;
        }
        return false;
    }

    /**
     * @param int $mainCustomerId
     * @param int $mergeCustomerId
     */
    public function mergeCustomers(int $mainCustomerId, int $mergeCustomerId)
    {
        try{
            $this->transactionManager->wrap(function() use ($mainCustomerId, $mergeCustomerId) {

                $originABs = $this->addressBookRepository->findByCustomer($mainCustomerId, true);
                $mergeABs = $this->addressBookRepository->findByCustomer($mergeCustomerId, true);

                if(count($originABs) !== 1 || count($mergeABs) !== 1) {
                    $orders = Orders::updateAll(['customers_id' => $mainCustomerId],[
                        'customers_id' => $mergeCustomerId,
                        'billing_address_book_id' => 0,
                        'delivery_address_book_id' => 0,
                    ]);
                    if ($orders > 0 ) {
                        $this->deleteCustomerInfo($mergeCustomerId);
                        return true;
                    }
                    return false;
                }

                AddressBook::updateAll(['customers_id' => $mainCustomerId],['customers_id' => $mergeCustomerId]);
                Orders::updateAll(['customers_id' => $mainCustomerId],['customers_id' => $mergeCustomerId]);
                $this->deleteCustomerInfo($mergeCustomerId);

            });
        }catch (\Exception $e) {
            throw new \RuntimeException( $e->getMessage() );
        }
    }

    public function deleteCustomerInfo(int $customerId, bool $deleteAddressBook = false)
    {
        Customers::deleteAll(['customers_id' => $customerId]);
        CustomersInfo::deleteAll(['customers_info_id' => $customerId]);
        CustomersBasket::deleteAll(['customers_id' => $customerId]);
        CustomersBasketAttributes::deleteAll(['customers_id' => $customerId]);
        if ($deleteAddressBook) {
            AddressBook::deleteAll(['customers_id' => $customerId]);
        }
    }

    /**
     * @param Customers $customer
     * @param string $payerReference
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function savePayerReference(Customers $customer, string $payerReference)
    {
        return $this->customersRepository->edit($customer, ['payerreference' => $payerReference]);
    }

    /**
     * @param int $customerId
     * @param int $languageId
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function changeLanguageByCustomerId(int $customerId, int $languageId)
    {
        $customer = $this->findById($customerId);
        return $this->changeLanguage($customer, $languageId);
    }
    /**
     * @param Customers $customer
     * @param int $languageId
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function changeLanguage(Customers $customer, int $languageId)
    {
        return $this->customersRepository->edit($customer, ['language_id' => $languageId]);
    }

}
