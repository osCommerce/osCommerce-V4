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

namespace backend\services;


use common\models\repositories\ConfigurationRepository;
use common\services\PlatformsConfigurationService;

final class ConfigurationService
{
    /** @var ConfigurationRepository */
    private $configurationRepository;
    /** @var PlatformsConfigurationService */
    private $platformsConfigurationService;

    public function __construct(
        ConfigurationRepository $configurationRepository,
        PlatformsConfigurationService $platformsConfigurationService
    )
    {
        $this->configurationRepository = $configurationRepository;
        $this->platformsConfigurationService = $platformsConfigurationService;
    }

    public function isDefaultOrderStatusIdForOnlinePayment(int $orderStatusId)
    {
        if (defined('DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS_ID')) {
            if ((int) DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS_ID === $orderStatusId) {
                return true;
            }
        }
        return false;
    }

    public function isDefaultOrderStatusIdForOnlinePaymentSuccess(int $orderStatusId)
    {
        if (defined('DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS_ID')) {
            if ((int)DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS_ID === $orderStatusId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $key
     * @param string $value
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function updateByKey(string $key, string $value)
    {
        return $this->configurationRepository->updateByKey($key, $value);
    }

    /**
     * @param int $orderStatusId
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function setDefaultOrderStatusIdForOnlinePayment(int $orderStatusId)
    {
        return $this->configurationRepository->updateByKey('DEFAULT_ONLINE_PAYMENT_ORDERS_STATUS_ID', (string) $orderStatusId);
    }

    public function setDefaultOrderStatusIdForOnlinePaymentSuccess(int $orderStatusId)
    {
        return $this->configurationRepository->updateByKey('DEFAULT_ONLINE_PAYMENT_SUCCESS_ORDERS_STATUS_ID', (string) $orderStatusId);
    }

    /**
     * @param string $key
     * @param bool $asArray
     * @return array|\common\models\Configuration|null
     */
    public function findByKey(string $key, bool $asArray = false)
    {
        return $this->configurationRepository->findByKey($key, $asArray);
    }

    /**
     * @param string $key
     * @param int|null $platformId
     * @return string
     */
    public function findValue(string $key, ?int $platformId = null): string
    {
        $value = '';
        $siteValue = $this->findByKey($key, true);
        if ($siteValue) {
            $value = $siteValue['configuration_value'];
        }
        if ($platformId !== null) {
            $platformValue = $this->platformsConfigurationService->findByKey($key, $platformId, true);
            if ($platformValue) {
                $value = $platformValue['configuration_value'];
            }
        }
        return $value;
    }
}
