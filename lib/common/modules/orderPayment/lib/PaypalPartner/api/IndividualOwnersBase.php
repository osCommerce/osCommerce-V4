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


class IndividualOwnersBase extends PayPalResourceModel {
        
    public function setCitizenship($code)
    {
        $this->citizenship = $code;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getCitizenship()
    {
        return $this->citizenship;
    }
    
    public function setNames($name)
    {
        $this->names = [$name];
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getNames()
    {
        return $this->names;
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
        return $this->home_address;
    }
}