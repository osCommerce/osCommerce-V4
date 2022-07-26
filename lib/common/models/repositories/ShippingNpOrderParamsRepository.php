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
declare(strict_types=1);


namespace common\models\repositories;


use common\models\ShippingNpOrderParams;

class ShippingNpOrderParamsRepository
{

    /**
     * @param int $order_id
     * @param string $type
     * @param bool $asArray
     * @return array|ShippingNpOrderParams|null
     */
    public function getShippingData(int $order_id, string $type = '', bool $asArray = false){
        $orderParams = $this->findShippingData($order_id, $type, $asArray);
        if (!$orderParams) {
            throw new \DomainException('NovaPoshta details not found');
        }
        return $orderParams;
    }

    /**
     * @param int $orderId
     * @param string $type
     * @param bool $asArray
     * @return array|ShippingNpOrderParams|null
     */
    public function findShippingData(int $orderId, string $type = '', bool $asArray = false){
        $orderParams = ShippingNpOrderParams::find()
            ->where(['orders_id' => $orderId, 'type' => $type])
            ->limit(1)
            ->asArray($asArray)
            ->one();
        if ($asArray && $orderParams) {
            $valueData = json_decode($orderParams['value'], true);
            $orderParams['valueData'] = is_array($valueData) ? $valueData : [];
        }
        return $orderParams;

    }

    /**
     * @param ShippingNpOrderParams $npOrderParams
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function remove(ShippingNpOrderParams $npOrderParams)
    {
        if ($npOrderParams->delete() === false) {
            throw new \RuntimeException('Nova Poshta Order Params remove error.');
        }
        return true;
    }

    /**
     * @param ShippingNpOrderParams $npOrderParams
     * @param bool $validation
     * @return bool
     */
    public function save(ShippingNpOrderParams $npOrderParams, bool $validation = false)
    {
        if ($npOrderParams->save($validation) === false) {
            throw new \RuntimeException('Nova Poshta Order Params save error.');
        }
        return true;
    }

    /**
     * @param ShippingNpOrderParams $npOrderParams
     * @param array $params
     * @param bool $validation
     * @param bool $safeOnly
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function edit(ShippingNpOrderParams $npOrderParams, array $params = [], bool $validation = false, bool $safeOnly = false)
    {
        foreach ($params as $attribute => $param) {
            if (!$npOrderParams->hasAttribute($attribute)) {
                unset($params[$attribute]);
            }
        }
        $npOrderParams->setAttributes($params, $safeOnly);
        if ($npOrderParams->update($validation, array_keys($params)) === false) {
            return $npOrderParams->getErrors();
        }
        return true;
    }
}
