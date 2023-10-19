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


use common\models\AddressBook;
use common\models\Zones;

/**
 * Class AddressBookRepository
 * @package common\models\repositories
 */
class AddressBookRepository
{
    /**
     * @param $addressBookId
     * @param bool $asArray
     * @return array|AddressBook|AddressBook[]|null|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public function findById($addressBookId, bool $asArray = false)
    {
        $addressBook = AddressBook::find()->where(['address_book_id' => $addressBookId])->asArray($asArray);
        if (is_array($addressBookId)) {
            return $addressBook->all();
        }
        return $addressBook->limit(1)->one();
    }

    /**
     * @param int $customerId
     * @param bool $asArray
     * @return array|AddressBook[]
     */
    public function findByCustomer(int $customerId, bool $asArray = false): array
    {
        $addressBooks = AddressBook::find()->where(['customers_id' => $customerId])->asArray($asArray);
        return $addressBooks->all();
    }

    /**
     * @param $addressBookId
     * @param bool $asArray
     * @return AddressBook|AddressBook[]
     */
    public function getById($addressBookId, bool $asArray = false): array
    {
        $addressBook = $this->findById($addressBookId, $asArray);
        if (!$addressBook) {
            throw new \DomainException('AddressBook not found');
        }
        return $addressBook;
    }

    /**
     * @param AddressBook $addressBook
     * @param bool $validation
     * @return bool
     */
    public function save(AddressBook $addressBook, bool $validation = false): bool
    {
        if (!$addressBook->save($validation)) {
            throw new \RuntimeException('AddressBook saving  error.');
        }
        return true;
    }

    /**
     * @param AddressBook $addressBook
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(AddressBook $addressBook)
    {
        if (!$addressBook->delete()) {
            throw new \RuntimeException('AddressBook saving  error.');
        }
    }

    /**
     * @param AddressBook $addressBook
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(AddressBook $addressBook, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$addressBook->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $addressBook->setAttributes($params, $safeOnly);
        if ($addressBook->update($validation, array_keys($params)) === false) {
            return $addressBook->getErrors();
        }
        return true;
    }

    /**
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|AddressBook
     */
    public function addFromArray(array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        $addressBook = new AddressBook;
        foreach ($params as $attribute => $param) {
            if (!$addressBook->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $addressBook->setAttributes($params, $safeOnly);
        if (!$this->save($addressBook, $validation)) {
            return $addressBook->getErrors();
        }
        return $addressBook;
    }
}
