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


class Partner extends PayPalResourceModel {
    /**
     * sets     "products": ["PPCP" ], "legal_consents": [ { "type": "SHARE_DATA_CONSENT", "granted": true } ]
     * @param type $data
     */
    public function __construct($data = null){
        
        parent::__construct($data);
        $this->setProducts();
        $this->setConsents();
        $this->setPartnerConfigOverride(new PartnerConfigOverride());
    }
    
    public function setProducts($data = []){
        if (empty($data) || !array($data)) {
            $this->products = [PartnerConstants::PRODUCT];
        } else {
            $prods = [];
            foreach ($data as $p) {
                if ( in_array($p, [ 'EXPRESS_CHECKOUT', 'PPPLUS', 'WEBSITE_PAYMENT_PRO', 'PPCP'])) {
                    $prods[] = $p;
                }
            }
            if (empty($prods)) {
                $prods = [PartnerConstants::PRODUCT];
            }
            $this->products = $prods;
        }
    }
    
    public function setConsents(){
        $this->legal_consents = [new Legal()];
    }
    
    public function setIndividualOwners($data)
    {
        $this->individual_owners = [$data];
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getIndividualOwners()
    {
        return $this->individual_owners;
    }
    
    public function setBusinessEntity($data)
    {
        $this->business_entity = $data;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getBusinessEntity()
    {
        return $this->business_entity;
    }
    
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    public function setPreferredLanguageCode($code)
    {
        $this->preferred_language_code = $code;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getPreferredLanguageCode()
    {
        return $this->preferred_language_code;
    }
    
    public function setTrackingId($trackingId)
    {
        $this->tracking_id = $trackingId;
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getTrackingId()
    {
        return $this->tracking_id;
    }
    
    public function setPartnerConfigOverride($config)
    {
        $this->partner_config_override = $config;
        return $this;
    }

    /**
     *
     * @return PartnerConfigOverride
     */
    public function getPartnerConfigOverride()
    {

        return $this->partner_config_override;
    }
    
    public function setOperations($data)
    {
        $this->operations = [$data];
        return $this;
    }

    /**
     * Transactional details including the amount and item details.
     *
     * @return \PayPal\Api\Transaction[]
     */
    public function getOperation()
    {
        return $this->operations;
    }
    
    /**
     * Get Approval Link
     *
     * @return null|string
     */
    public function getApprovalLink()
    {
        return $this->getLink(PartnerConstants::APPROVAL_URL);
    }
	
	/**
     * Get token from Approval Link
     *
     * @return null|string
     */
	public function getToken()
	{
		$parameter_name = "token";
		parse_str(parse_url($this->getApprovalLink(), PHP_URL_QUERY), $query);
		return !isset($query[$parameter_name]) ? null : $query[$parameter_name];
	}
	
    /**
     * Creates and processes a payment. In the JSON request body, include a `payment` object with the intent, payer, and transactions. For PayPal payments, include redirect URLs in the `payment` object.
     *
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Payment
     */
    public function create($apiContext = null, $restCall = null)
    {
        $payLoad = $this->toJSON();
        $json = self::executeCall(
            "/v2/customer/partner-referrals",
            "POST",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $this->fromJson($json);
        return $this;
    }

    /**
     * Shows details for a payment, by ID.
     *
     * @param string $paymentId
     * @param ApiContext $apiContext is the APIContext for this call. It can be used to pass dynamic configuration and credentials.
     * @param PayPalRestCall $restCall is the Rest Call Service that is used to make rest calls
     * @return Payment
     */
    public static function get($paymentId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($paymentId, 'paymentId');
        $payLoad = "";
        $json = self::executeCall(
            "/v1/payments/payment/$paymentId",
            "GET",
            $payLoad,
            null,
            $apiContext,
            $restCall
        );
        $ret = new Payment();
        $ret->fromJson($json);
        return $ret;
    }
}