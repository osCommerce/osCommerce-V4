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
namespace common\modules\orderPayment\lib\PaypalPartner\api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Rest\ApiContext;

class Merchant extends PayPalResourceModel {

    public $json = '';
       
    /**
     * Get Approval Link
     *
     * @return null|string
     */
    public function getApprovalLink()
    {
        return $this->getLink(PartnerConstants::APPROVAL_URL);
    }
    
    public function setPartnerId($partnerId)
    {
        $this->partner_id = $partnerId;
        return $this;
    }

    public function getPartnerId()
    {
        return $this->partner_id;
    }
    
    public function setTrackingId($trackingId)
    {
        $this->tracking_id = $trackingId;
        return $this;
    }

    public function getTrackingId()
    {
        return $this->tracking_id;
    }
    
    public function setMerchantId($merchantId)
    {
        $this->merchant_id = $merchantId;
        return $this;
    }

    public function getMerchantId()
    {
        return $this->merchant_id;
    }
    
    public function setPaymentsReceivable($value)
    {
        $this->payments_receivable = $value;
        return $this;
    }

    public function getPaymentsReceivable()
    {
        return $this->payments_receivable;
    }
    
    public function setPrimaryEmailConfirmed($confirmed)
    {
        $this->primary_email_confirmed = $confirmed;
        return $this;
    }

    public function getPrimaryEmailConfirmed()
    {
        return $this->primary_email_confirmed;
    }

    public function getLegalName()
    {
        return $this->legal_name;
    }
    
    public function getMerhantId()
    {
        return $this->merchant_id;
    }

    public function getPrimaryEmail()
    {
        return $this->primary_email;
    }

    public function getPrimaryCurrency()
    {
        return $this->primary_currency;
    }

    public function getCountry()
    {
        return $this->country;
    }
    
    /**
     * @return \PayPal\Common\PayPalModel
     */
    public function getProducts()
    {
        return $this->products;
    }

    public function setOauthIntegrations($data)
    {
        $this->oauth_integrations = $data;
        return $this;
    }

    /**
     * @return \PayPal\Common\PayPalModel
     */
    public function getOauthIntegrations()
    {
        return $this->oauth_integrations;
    }

    /**
     * @return \PayPal\Common\PayPalModel
     */
    public function getСapabilities()
    {
        return $this->capabilities;
    }
	
	public function getToken()
	{
		$parameter_name = "token";
        $query = [];
		parse_str(parse_url($this->getApprovalLink(), PHP_URL_QUERY), $query);
		return !isset($query[$parameter_name]) ? null : $query[$parameter_name];
	}

    public static function checkStatus($partnerId, $merchantId, $apiContext = null, $restCall = null)
    {
        ArgumentValidator::validate($partnerId, 'partnerId');
        ArgumentValidator::validate($merchantId, 'merchantId');
        $payLoad = "";
        try {
            $json = self::executeCall(
                "/v1/customer/partners/{$partnerId}/merchant-integrations/{$merchantId}",
                "GET",
                $payLoad,
                null,
                $apiContext,
                $restCall
            );
//echo "#### <PRE>"  . __FILE__ .':' . __LINE__ . ' ' . print_r($json, true) ."";            var_dump(json_decode($json, true));            die;
            $ret = new Merchant();
            $ret->fromJson($json);
            $ret->json = $json;
        } catch (\Exception $e) {
            $ret = null;
            \Yii::warning(" #### " .print_r($e->getMessage(), true), 'TLDEBUG');
        }
        return $ret;
    }
}