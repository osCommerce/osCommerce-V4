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


class Preference extends PayPalResourceModel {
    
    public function __construct($data = null){        
        parent::__construct($data);
    }
            
    public function setPartnerId($partnerId)
    {
        $this->partner_id = $partnerId;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getPartnerId()
    {
        return $this->value;
    }
    
    public function setRestApiIntegration($rest)
    {
        $this->rest_api_integration = $rest;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getRestApiIntegration()
    {
        return $this->rest_api_integration;
    }
    
    
}