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


namespace common\classes\events\frontend\attributes\productAttributesInfo;


class ProductAttributesInfoEvent
{
    /** @var array */
    private $productAttributes;
    private $customer;

    public function __construct(
        array $productAttributes,
        $customer
    )
    {
        $this->productAttributes = $productAttributes;
        $this->customer = $customer;
    }

    /**
     * @return array
     */
    public function getProductAttributes(): array
    {
        return $this->productAttributes;
    }

    /**
     * @param string $name
     * @param mixed|null $value
     * @return $this
     */
    public function setProductAttributesProperty(string $name, $value = null): self
    {
        $this->productAttributes[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getProductAttributesProperty(string $name)
    {
        return $this->productAttributes[$name] ?? null;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

}
