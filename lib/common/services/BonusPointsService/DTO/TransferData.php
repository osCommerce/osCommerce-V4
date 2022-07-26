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


namespace common\services\BonusPointsService\DTO;


class TransferData
{
    /** @var \common\components\Customer */
    private $customer;
    /** @var float */
    private $bonusPointsCosts;
    /** @var int */
    private $bonusPoints;
    /** @var bool */
    private $notifyCustomerBonus;
    /** @var bool */
    private $notifyCustomerAmount;

    private function __construct()
    {
    }

    public static function create(
        \common\components\Customer $customer,
        float $bonusPointsCosts,
        int $bonusPoints = 0,
        bool $notifyCustomerBonus = false,
        bool $notifyCustomerAmount = false
    ): self
    {
        $dto = new self();
        $dto->customer = $customer;
        $dto->bonusPointsCosts = $bonusPointsCosts;
        if ($bonusPoints < 1 || $bonusPoints > $customer->customers_bonus_points) {
            $bonusPoints = $customer->customers_bonus_points;
        }
        $dto->bonusPoints = $bonusPoints;
        $dto->notifyCustomerBonus = $notifyCustomerBonus;
        $dto->notifyCustomerAmount = $notifyCustomerAmount;
        return $dto;
    }

    /**
     * @return \common\components\Customer
     */
    public function getCustomer(): \common\components\Customer
    {
        return $this->customer;
    }

    /**
     * @return float
     */
    public function getBonusPointsCosts(): float
    {
        return $this->bonusPointsCosts;
    }

    /**
     * @return int
     */
    public function getBonusPoints(): int
    {
        return $this->bonusPoints;
    }

    /**
     * @return bool
     */
    public function isNotifyCustomerBonus(): bool
    {
        return $this->notifyCustomerBonus;
    }

    /**
     * @return bool
     */
    public function isNotifyCustomerAmount(): bool
    {
        return $this->notifyCustomerAmount;
    }

}
