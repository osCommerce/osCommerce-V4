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


class Integration extends PayPalResourceModel {
            
    public function __construct($data = null){
        $this->integration_method = PartnerConstants::INTEGRATION_METHOD;
        parent::__construct($data);
    }
    
    public function setThirdPartyDetails($details)
    {
        $this->integration_type = PartnerConstants::INTEGRATION_TYPE_TP;
        $this->third_party_details = $details;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getThirdPartyDetails()
    {
        return $this->third_party_details;
    }

/**
 * sets integration_type = PartnerConstants::INTEGRATION_TYPE_FP and  first_party_details = $details
 * @param FirstPartyDetails $details (new PaypalPartner\api\FirstPartyDetails() )
 * @return $this
 */
    public function setFirstPartyDetails($details)
    {
      $this->integration_type = PartnerConstants::INTEGRATION_TYPE_FP;
      $this->first_party_details = $details;
      return $this;
    }

    public function getFirstPartyDetails()
    {
      return $this->first_party_details;
    }
    
}