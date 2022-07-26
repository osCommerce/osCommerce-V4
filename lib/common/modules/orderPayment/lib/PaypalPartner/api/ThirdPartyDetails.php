<?php
 /**
 * Transactional Middle Ware for Paypal modules
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


class ThirdPartyDetails extends PayPalResourceModel {
            
    public function __construct($data = null){
        $this->features = [
                PartnerConstants::FEATURE_PAYMENT,
                PartnerConstants::FEATURE_REFUND,
                PartnerConstants::FEATURE_FEE,
                PartnerConstants::FEATURE_DELAY,
                PartnerConstants::FEATURE_INFO,
        ];
        parent::__construct($data);
    }
    
    public function setPartnerClientId($clientId)
    {
        $this->partner_client_id = $clientId;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getPartnerClientId()
    {
        return $this->partner_client_id;
    }
}