<?php
 /**
 * Transactional Midle Ware for Paypal modules
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */
namespace common\modules\orderPayment\lib\PaypalPartner\api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Rest\ApiContext;


class Address extends PayPalResourceModel {
                
    public function setAddressLine1($line1)
    {
        $this->address_line_1 = $line1;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getAddressLine1()
    {
        return $this->line1;
    }
    
    public function setAddressLine2($line2)
    {
        $this->address_line_2 = $line2;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getAddressLine2()
    {
        return $this->address_line_2;
    }
    
    public function setAdminArea2($city)
    {
        $this->admin_area_2 = $city;
        return $this;
    }
    
    public function getAdminArea2()
    {
        return $this->admin_area_2;
    }
    
    public function setAdminArea1($zone)
    {
        $this->admin_area_1 = $zone;
        return $this;
    }
    
    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getAdminArea1()
    {
        return $this->admin_area_1;
    }
    
    public function setCountryCode($code)
    {
        $this->country_code = $code;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }
    
    public function setPostalCode($code)
    {
        $this->postal_code = $code;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }
    
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getType()
    {
        return $this->type;
    }
    
}