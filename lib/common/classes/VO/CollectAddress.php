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


namespace common\classes\VO;


final class CollectAddress
{
    /** @var string */
    private $street_address;
    /** @var string */
    private $city;
    /** @var string */
    private $state;
    /** @var string */
    private $postcode;
    /** @var string */
    private $countryName;
    /** @var string */
    private $countryISO2;
    /** @var string */
    private $countryISO3;
    /** @var string */
    private $warehouse;

    private function __construct()
    {
    }

    public static function create(
        string $street_address,
        string $city,
        string $state,
        string $postcode,
        string $countryName,
        string $countryISO2,
        string $countryISO3,
        string $warehouse = ''
    ): self
    {
        $address = new self();
        $address->street_address = $street_address;
        $address->city = $city;
        $address->state = $state;
        $address->postcode = $postcode;
        $address->countryName = $countryName;
        $address->countryISO2 = $countryISO2;
        $address->countryISO3 = $countryISO3;
        $address->warehouse = $warehouse;
        return $address;
    }

    /**
     * @return string
     */
    public function getStreetAddress(): string
    {
        return $this->street_address;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getCountryName(): string
    {
        return $this->countryName;
    }

    /**
     * @return string
     */
    public function getCountryISO2(): string
    {
        return $this->countryISO2;
    }

    /**
     * @return string
     */
    public function getCountryISO3(): string
    {
        return $this->countryISO3;
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }

    /**
     * @return string
     */
    public function getWarehouse(): string
    {
        return $this->warehouse;
    }

}
