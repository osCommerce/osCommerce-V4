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


class PartnerConfigOverride extends PayPalResourceModel {
                
    public function setReturnUrl($returnUrl)
    {
        $this->return_url = $returnUrl;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getReturnUrl()
    {
        return $this->return_url;
    }
    
    public function setPartnerLogoUrl($logoUrl)
    {
        $this->partner_logo_url = $logoUrl;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getPartnerLogoUrl()
    {
        return $this->partner_logo_url;
    }
    
}