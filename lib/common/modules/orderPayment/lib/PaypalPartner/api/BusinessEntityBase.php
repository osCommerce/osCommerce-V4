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


class BusinessEntityBase extends PayPalResourceModel {
        
    public function setBusinessType($data)
    {
        $this->business_type = $data;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getBusinessType()
    {
        return $this->business_type;
    }
    
    public function setAddresses($address)
    {
        $this->addresses = [$address];
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }
    
    public function setWebsite($website)
    {
        $this->website = $website;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getWebsite()
    {
        return $this->website;
    }
}