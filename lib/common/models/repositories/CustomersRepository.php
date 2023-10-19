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

use common\components\Customer;
use common\models\Customers;
use yii\db\ActiveQuery;

class CustomersRepository
{

    /**
     * @param bool $active
     * @param bool $asArray
     * @return array|Customers[]
     */
    public function findAll(bool $active = true, bool $asArray = false)
    {
        $customers = Customers::find();
        if ($active) {
            $customers->active();
        }
        return $customers->indexBy('customers_id')->asArray($asArray)->all();
    }

    /**
     * @param Customers $customers
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(Customers $customers, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$customers->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $customers->setAttributes($params, $safeOnly);
        if ($customers->update($validation, array_keys($params)) === false) {
            return $customers->getErrors();
        }
        return true;
    }

    public function save(Customers $customers, bool $validation = false)
    {
        if (!$customers->save($validation)) {
            throw new \RuntimeException('Customer saving error.');
        }
        return true;
    }

    public function getMutualSettlementList($start, $length, $platformId = false, $active = false, $isArray = false)
    {
        $customers = Customers::find()->where(['<>', 'credit_amount', 0]);
        if ($active) {
            $customers->active();
        }
        if (is_numeric($platformId) || (is_array($platformId) && count($platformId))) {
            $customers->byPlatform($platformId);
        }
        return $customers->asArray($isArray)->offset($start)->limit($length)->orderBy(['credit_amount' => SORT_DESC])->all();
    }

    public function getMutualSettlementListTotal($platformId = false, $active = false)
    {
        $customers = Customers::find()->where(['<>', 'credit_amount', 0]);
        if ($active) {
            $customers->active();
        }
        if (is_numeric($platformId) || (is_array($platformId) && count($platformId))) {
            $customers->byPlatform($platformId);
        }
        return $customers->count();
    }

    public static function isBirthday($customerId, $day, $active = true)
    {
        $customers = Customers::find()
            ->where(['customers_id' => $customerId])
            ->andWhere(new \yii\db\Expression("DAYOFYEAR(customers_dob) >= DAYOFYEAR(NOW()) - {$day} AND DAYOFYEAR(customers_dob) <= DAYOFYEAR(NOW()) + {$day} "));
        if ($active) {
            $customers->active();
        }
        return $customers->exists();
    }

    /**
     * @param $id
     * @param bool $asArray
     * @return Customers|Customers[]
     */
    public function getById($id, bool $asArray = false)
    {
        $customer = $this->findById($id, $asArray);
        if (!$customer) {
            throw new NotFoundException('Customer not found');
        }
        return $customer;
    }


    /**
     * @param $id
     * @param bool $asArray
     * @return array|Customers|Customers[]|null
     */
    public function findById($id, bool $asArray = false)
    {
        $customer = Customers::find()
            ->where(['customers_id' => $id])
            ->asArray($asArray);
        if (is_array($id)) {
            return $customer->all();
        }
        return $customer->limit(1)->one();
    }

    /**
     * @param $id
     * @param bool $asArray
     * @return Customer|Customer[]
     */
    public function getIdentityById(int $id, bool $asArray = false)
    {
        $customer = $this->findIdentityById($id, $asArray);
        if (!$customer) {
            throw new NotFoundException('Customer Identity not found');
        }
        return $customer;
    }

    /**
     * @param int $id
     * @param bool $asArray
     * @return array|Customer|Customers[]|null
     */
    public function findIdentityById(int $id, bool $asArray = false)
    {
        return Customer::find()
            ->where(['customers_id' => $id])
            ->asArray($asArray)->limit(1)->one();
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
    public function findAllByTermLimit(string $term = null, bool $active = true, int $limit = 20, int $offset = 0, bool $asArray = false, array $fields = [], string $index = null)
    {
        $customers = Customers::find();
        if (is_array($fields) && count($fields) > 0) {
            $customers->select($fields);
        }
        if ($active) {
            $customers
                ->active()
                ->noGuest()
                ->withEmail();
        }
        $customers->andFilterWhere([
            'OR',
            ['like', 'customers_email_address', $term],
            ['like', 'customers_firstname', $term],
            ['like', 'customers_lastname', $term],
        ]);
        if (!empty($index)) {
            $customers->indexBy($index);
        }
        return $customers
            ->limit($limit)
            ->offset($offset)
            ->asArray($asArray)
            ->orderBy('customers_lastname')
            ->groupBy('customers_id')
            ->all();
    }

    public function search($search, $status = 1, $guest = 0)
    {
        return Customers::find()->where(['customers_status' => $status, 'opc_temp_account' => $guest])
            ->andWhere(['or',
                ['like', 'customers_firstname', $search],
                ['like', 'customers_lastname', $search],
                ['like', 'customers_telephone', $search],
                ['like', 'customers_email_address', $search],
            ]);
    }

}
