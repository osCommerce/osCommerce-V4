<?php
namespace common\modules\orderPayment\lib;
use common\helpers\OrderPayment as OrderPaymentHelper;

trait PaypalPartnerTrait {
    
    public function getExtraConfigClass(){
        return PaypalPartner\ExtraConfig::class;
    }
    
    protected function liveConfigurationExists($platform_id){
        $ret = false;
        $partnerId = self::PARTNER_MERCHANT_ID;
        $seller = PaypalPartner\models\SellerInfo::find()
            ->where(['platform_id' => $platform_id, 'partner_id' => $partnerId, 'status' => 1])
            ->one();
        if ($seller){
            $ret = $seller->is_onboard;// restart boarding?
        }
        return $ret;
    }

    protected function ownSandboxConfigExists($platform_id){
        $ret = false;
        $partnerId = self::PARTNER_MERCHANT_SANDBOX_ID;
        /** @var PaypalPartner\models\SellerInfo  $seller */
        $seller = PaypalPartner\models\SellerInfo::find()
            ->where(['platform_id' => $platform_id, 'partner_id' => $partnerId, 'status' => 1])
            ->one();
        if ($seller && !empty($seller->own_client_id) && !empty($seller->own_client_secret)){
            $ret = $seller->own_client_id !== 'Ad1UJlMj9BKThV6rWyktLD5ggqp2uL4iFnFtYIKwAcKULWu3OoaGHw4NO2v8bmx86-6RIcWB_LoSOGsr';
        }
        return $ret;
    }

    public function getSellerById($platform_id, $id){
        $seller = PaypalPartner\models\SellerInfo::find()
            ->where(['platform_id' => $platform_id, 'psi_id' => $id])
            ->one();
        if (!$seller){
            $seller = $this->getSeller($platform_id);
        }
        return $seller;
    }

/**
 *
 * @param int $platform_id
 * @param string $force force mode
 * @param bool $forceNew
 * @return \common\modules\orderPayment\lib\PaypalPartner\models\SellerInfo
 */
    public function getSeller($platform_id, $force = false, $forceNew = false){
        $partnerId = $this->getPartnerId($force); // depends on sandbox/live mode
        $seller = PaypalPartner\models\SellerInfo::find()
            ->where(['platform_id' => $platform_id, 'partner_id' => $partnerId, 'status' => ($forceNew?-1:1)])
            //->cache(20, (new \yii\caching\TagDependency(['tags' => 'seller-'. $platform_id . '-' . $partnerId])))
            ->one();
        if (!$seller || ($forceNew && $seller->status != -1) ){
            $seller = new PaypalPartner\models\SellerInfo([
              'platform_id' => $platform_id,
              'partner_id' => $partnerId,
              'fee_percent' => static::PARTNER_DEFAULT_FEE,
              'tracking_id' => PaypalPartner\models\SellerInfo::generateTrackingId(),
              'is_onboard' => 0,
              'status' => -1
              ]);
        }
        $prefill_platform_address_map = ['entry_company' => 'company',
            'entry_street_address' => 'street_address',
            'entry_suburb' => 'suburb',
            'entry_postcode' => 'postcode',
            'entry_city' => 'city',
            'entry_state' => 'state',
            'entry_country_id' => 'country_id',
            'entry_zone_id' => 'zone_id'
          ];
            

        $__platform = \Yii::$app->get('platform');
        /** @var \common\classes\platform_config $platformConfig*/
        $platformConfig = $__platform->config($platform_id);
        $data = $platformConfig->getPlatformData();
        $address = $platformConfig->getPlatformAddress();

        if (empty($seller->entry_telephone)) {
            $seller->entry_telephone = $data['platform_telephone'];
        }
        if (empty($seller->entry_firstname) || empty($seller->entry_lastname)) {
            $tmp = explode(' ', $data['platform_owner'], 2);
            $seller->entry_firstname = trim($tmp[0]);
            if (!empty($tmp[1])) {
                $seller->entry_lastname = trim($tmp[1]);
            }
        }

        if (is_array($prefill_platform_address_map) && !empty($address)) {
            foreach($prefill_platform_address_map as $seller_prop => $address_key) {
                if (!empty($seller_prop) && !empty($address_key) &&
                    empty($seller->$seller_prop) && !empty($address[$address_key])) {
                    $seller->$seller_prop = $address[$address_key];
                }
            }
        }
        return $seller;
    }
    
    public function getInstaller(){
        return new PaypalPartner\Installer();
    }

 /**
  * create partner referral structure to SIGN-IN existing PayPal seller.
  * @param \common\modules\orderPayment\lib\PaypalPartner\models\SellerInfo $seller
  * @throws \Exception
  */
    public function signinPartner(PaypalPartner\models\SellerInfo $seller){
      if (!$seller->tracking_id) {
          throw new \Exception("Tracking ID is undefined");
      }
      /*'{
    "operations": [
      {
        "operation": "API_INTEGRATION",
        "api_integration_preference": {
          "rest_api_integration": {
            "integration_method": "PAYPAL",
            "integration_type": "FIRST_PARTY",
            "first_party_details": {
              "features": [
                "PAYMENT",
                "REFUND"
              ],
              "seller_nonce": " Seller-Nonce"
            }
          }
        }
      }
    ],
    "products": [
      "EXPRESS_CHECKOUT"
    ],
    "legal_consents": [
      {
        "type": "SHARE_DATA_CONSENT",
        "granted": true
      }
    ]
}'

       */
      try {
        $partner = new PaypalPartner\api\Partner();

        //$partner->setPartnerConfigOverride(new PaypalPartner\api\PartnerConfigOverride());
        $partner->setTrackingId($seller->tracking_id);

        $integration = new PaypalPartner\api\Integration(["integration_method" => "PAYPAL"]);
        if (self::BOARDING_MODE == 3) {
            $integration->setThirdPartyDetails(new PaypalPartner\api\ThirdPartyDetails()); //['seller_nonce' => self::random_str(100)]
        } else {
            $integration->setFirstPartyDetails(new PaypalPartner\api\FirstPartyDetails(['seller_nonce' => $seller->tracking_id]));  //self::random_str(100)
        }

        $preference = new PaypalPartner\api\Preference();
        $preference->setRestApiIntegration($integration);

        $operation = new PaypalPartner\api\Operation();
        $operation->setApiIntegrationPreference($preference);

        $partner->setOperations($operation);
      } catch (\Exception $ex) {
        $this->sendDebugEmail($ex);
        return false;
      }

      return $partner;


    }
    private static function random_str(int $length = 64, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
      if ($length < 1) {
          throw new \RangeException("Length must be a positive integer");
      }
      $pieces = [];
      $max = mb_strlen($keyspace, '8bit') - 1;
      for ($i = 0; $i < $length; ++$i) {
          $pieces []= $keyspace[random_int(0, $max)];
      }
      return implode('', $pieces);
    }

    /**
     * create partner referral structure to SIGN-UP new PayPal seller.
     * @param \common\modules\orderPayment\lib\PaypalPartner\models\SellerInfo $seller
     * @param string $seller_type allowed ['i', 'b']
     * @return \common\modules\orderPayment\lib\PaypalPartner\api\Partner
     * @throws \Exception
     */
    public function signupPartner(PaypalPartner\models\SellerInfo $seller, $seller_type = 'b'){

        if (!$seller->tracking_id) {
            throw new \Exception("Tracking ID is undefined");
        }

        if (!in_array($seller_type, ['i', 'b'])) {
            throw new \Exception("Incorrect seller type '{$seller_type}' allowed ['i', 'b']");
        }

        $languages_id = \Yii::$app->settings->get('languages_id');
        //$country = \common\helpers\Country::get_countries($seller->entry_country_id, true);
        $country = \common\models\Countries::findOne(['countries_id' => $seller->entry_country_id, 'language_id' => $languages_id])->toArray();
        $address = new PaypalPartner\api\Address([
            'address_line_1' => $seller->entry_street_address,
            'address_line_2' => $seller->entry_suburb,
            'admin_area_2' => $seller->entry_city,
            'admin_area_1' => $seller->entry_state,
            'postal_code' => $seller->entry_postcode,
            'country_code' => $country['countries_iso_code_2'],
        ]);
        $address->setType(PaypalPartner\api\PartnerConstants::ADDRESS_TYPE_HOME);
        
        $partner = new PaypalPartner\api\Partner();
        
        $iOwner = new PaypalPartner\api\IndividualOwners();
        $iOwner->setNames(new PaypalPartner\api\Name([
            'given_name' => $seller->entry_firstname,
            'surname' => $seller->entry_lastname,
        ]));

        $iOwner->setAddresses($address);
// not in SDK
        /**/
        $_pn = trim(preg_replace('/[^0-9]/', '', $seller->entry_telephone), '0');
        $_cdp = substr(trim($country['dialling_prefix'], ' +'), 0, 3);
        $sellerPhoneOk = false;
        if (strlen($_pn)>0 && strlen($_pn)<15 && strlen($_cdp)>0 && strlen($_cdp.$_pn)<16 ) {
            $sellerPhoneOk = true;
            $iOwner->phones = [(object)[
              'national_number' => $_pn,
              'country_code' => $_cdp
            ]];
        }
        /**/
        $iOwner->setCitizenship($country['countries_iso_code_2']);
        
        $partner->setIndividualOwners($iOwner);

        if ($seller_type!='i') {
          $bEntity = new PaypalPartner\api\BusinessEntity();
          $bEntity->setBusinessType(new PaypalPartner\api\BusinessType);
          $bAddress = new PaypalPartner\api\Address([
              'address_line_1' => $seller->entry_street_address,
              'address_line_2' => $seller->entry_suburb,
              'admin_area_2' => $seller->entry_city,
              'admin_area_1' => $seller->entry_state,
              'postal_code' => $seller->entry_postcode,
              'country_code' => $country['countries_iso_code_2'],
          ]);
          $bAddress->setType(PaypalPartner\api\PartnerConstants::ADDRESS_TYPE_WORK);
          $bEntity->setAddresses($bAddress);
          $bEntity->setWebsite(\Yii::$app->platform->getConfig($seller->platform_id)->getCatalogBaseUrl(true));

  // not in SDK
          $bEntity->names = [(object)[
            'business_name' => $seller->entry_company,
            "type" => "LEGAL_NAME"
          ]];
          if ($sellerPhoneOk) {
            $bEntity->phones = [(object)[
              'national_number' => $_pn,
              'country_code' => $_cdp
            ]];
          }

          $bEntity->emails = [(object)[
            'email' => $seller->email_address,
            "type" => "CUSTOMER_SERVICE" // nothing else in ENUM now
          ]];


          $partner->setBusinessEntity($bEntity);
        }
        
        $partner->setEmail($seller->email_address);
        $partner->setPreferredLanguageCode(\Yii::$app->language);
        
        $partner->setTrackingId($seller->tracking_id);
        
        //$partner->setPartnerConfigOverride(new PaypalPartner\api\PartnerConfigOverride);
        
        $operation = new PaypalPartner\api\Operation();
        $preference = new PaypalPartner\api\Preference();
        $integration = new PaypalPartner\api\Integration();
        $integration->setThirdPartyDetails(new PaypalPartner\api\ThirdPartyDetails());
        $preference->setRestApiIntegration($integration);
        $operation->setApiIntegrationPreference($preference);
        
        $partner->setOperations($operation);

        return $partner;
    }
        
    public function getMerchant($holbiMerchantId = null){
        $merchant = new PaypalPartner\api\Merchant();
        if ($holbiMerchantId){
            $merchant->setPartnerId($holbiMerchantId);
        }
        return $merchant;
    }

  /**
   * get Webhooks for application
   * @return array|false
   */
  public function getWebHooks() {
    $ret = false;
    try {
      $ret = \PayPal\Api\Webhook::getAllWithParams([], $this->getApiContext());// [ anchor_type => (APPLICATION|ACCOUNT)]
    } catch (\Exception $ex) {
      \Yii::warning("List all webhooks exception " . $ex->getMessage(), $this->code);
    }
    return $ret;
  }

  /**
   * set webhooks
   * @param array $subEvents
   * @return null|array|object
   */
  public function setWebHooks($subEvents) {
    $output = null;
    if (is_array($subEvents) && !empty($subEvents)) {
      $webhook = new \PayPal\Api\Webhook();
      $url = $this->getWebHookUrl();

      $webhook->setUrl($url);

      $webhookEventTypes = [];
      foreach ($subEvents as $event) {
        $webhookEventTypes[] = new \PayPal\Api\WebhookEventType(
          '{"name":"' . $event . '"}'
        );
      }
      $webhook->setEventTypes($webhookEventTypes);

      try {
        $apiContext = $this->getApiContext();
      } catch (\Exception $ex) {
        \Yii::warning($ex->getMessage(), 'paypal_partner');
      }

      try {
        $output = $webhook->create($apiContext);
      } catch (Exception $ex) {
        if ($ex instanceof \PayPal\Exception\PayPalConnectionException) {
          $data = $ex->getData();
          if (strpos($data, 'WEBHOOK_NUMBER_LIMIT_EXCEEDED') !== false) {
            try {
              foreach ($this->getWebHooks() as $dw) {
                $dw->delete($apiContext);
              }
            } catch (\Exception $ex) {
              \Yii::warning($ex->getMessage(), 'paypal_partner');
            }
            try {
              $output = $webhook->create($apiContext);
            } catch (Exception $ex) {
              \Yii::warning($ex->getMessage(), 'paypal_partner');
            }
          }
        }
        if (empty($output)) {
          \Yii::warning($ex->getMessage(), 'paypal_partner');
        }
      }
    }
    return $output;
  }

  public function onProcessWebhook() {
    /** @var String $bodyReceived */
    $bodyReceived = file_get_contents('php://input');
    /* debug
$bodyReceived = '{"id":"WH-0X0540454S641191T-3WD19915PD286223U","event_version":"1.0","create_time":"2020-08-05T14:52:23.855Z","resource_type":"capture","resource_version":"2.0","event_type":"PAYMENT.CAPTURE.COMPLETED","summary":"A $ 50.6 USD pending capture payment was completed","resource":{"disbursement_mode":"INSTANT","amount":{"value":"50.60","currency_code":"USD"},"seller_protection":{"dispute_categories":["ITEM_NOT_RECEIVED","UNAUTHORIZED_TRANSACTION"],"status":"ELIGIBLE"},"update_time":"2020-08-05T14:52:09Z","create_time":"2020-08-05T14:49:34Z","final_capture":true,"seller_receivable_breakdown":{"exchange_rate":{"source_currency":"USD","target_currency":"GBP","value":"0.757046355"},"paypal_fee":{"value":"2.02","currency_code":"USD"},"gross_amount":{"value":"50.60","currency_code":"USD"},"net_amount":{"value":"48.58","currency_code":"USD"},"receivable_amount":{"value":"36.78","currency_code":"GBP"}},"links":[{"method":"GET","rel":"self","href":"https://api.sandbox.paypal.com/v2/payments/captures/6T496768GW863311H"},{"method":"POST","rel":"refund","href":"https://api.sandbox.paypal.com/v2/payments/captures/6T496768GW863311H/refund"},{"method":"GET","rel":"up","href":"https://api.sandbox.paypal.com/v2/checkout/orders/53545104JD618153F"}],"id":"6T496768GW863311H","status":"COMPLETED"},"links":[{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-0X0540454S641191T-3WD19915PD286223U","rel":"self","method":"GET"},{"href":"https://api.sandbox.paypal.com/v1/notifications/webhooks-events/WH-0X0540454S641191T-3WD19915PD286223U/resend","rel":"resend","method":"POST"}]}'; //success debug
*/
/* 400 - payment capture  refund - incorrect type??
$bodyReceived = '{"id":"WH-2N242548W9943490U-1JU23391CS4765624","event_version":"1.0","create_time":"2014-10-31T15:42:24Z","resource_type":"sale","event_type":"PAYMENT.SALE.REFUNDED","summary":"A 0.01 USD sale payment was refunded","resource":{"id":"6YX43824R4443062K","create_time":"2014-10-31T15:41:51Z","update_time":"2014-10-31T15:41:51Z","state":"completed","amount":{"total":"-0.01","currency":"USD"},"sale_id":"9T0916710M1105906","parent_payment":"PAY-5437236047802405NKRJ22UA","links":[{"href":"https://api.paypal.com/v1/payments/refund/6YX43824R4443062K","rel":"self","method":"GET"},{"href":"https://api.paypal.com/v1/payments/payment/PAY-5437236047802405NKRJ22UA","rel":"parent_payment","method":"GET"},{"href":"https://api.paypal.com/v1/payments/sale/9T0916710M1105906","rel":"sale","method":"GET"}]},"links":[{"href":"https://api.paypal.com/v1/notifications/webhooks-events/WH-2N242548W9943490U-1JU23391CS4765624","rel":"self","method":"GET"},{"href":"https://api.paypal.com/v1/notifications/webhooks-events/WH-2N242548W9943490U-1JU23391CS4765624/resend","rel":"resend","method":"POST"}]}';
  */
/* dispute * /
$bodyReceived = '{"id": "WH-4M0448861G563140B-9EX36365822141321","event_version": "1.0","create_time": "2018-06-21T13:36:33.000Z","resource_type": "dispute","event_type": "CUSTOMER.DISPUTE.CREATED","summary": "A new dispute opened with Case # PP-000-042-663-135","resource": {"dispute_id": "PP-000-042-663-135","create_time": "2018-06-21T13:35:44.000Z","update_time": "2018-06-21T13:35:44.000Z","disputed_transactions": [{"seller_transaction_id": "6T496768GW863311H","seller": {"merchant_id": "RD465XN5VS364","name": "Test Store"},"items": [],"seller_protection_eligible": true}],"reason": "MERCHANDISE_OR_SERVICE_NOT_RECEIVED","status": "OPEN","dispute_amount": {"currency_code": "USD","value": "3.00"},"dispute_life_cycle_stage": "INQUIRY","dispute_channel": "INTERNAL","messages": [{"posted_by": "BUYER","time_posted": "2018-06-21T13:35:52.000Z","content": "qwqwqwq"}],"links": [{"href": "https://api.paypal.com/v1/customer/disputes/PP-000-042-663-135","rel": "self","method": "GET"}, {"href": "https://api.paypal.com/v1/customer/disputes/PP-000-042-663-135/send-message","rel": "send_message","method": "POST"}]},"links": [{"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-4M0448861G563140B-9EX36365822141321","rel": "self","method": "GET"}, {"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-4M0448861G563140B-9EX36365822141321/resend","rel": "resend","method": "POST"}]}';
 /*  */

/* PAYMENT.CAPTURE.REVERSED * /
$bodyReceived = '{"id":"WH-6F207351SC284371F-0KX52201050121307","event_version":"1.0","create_time":"2018-08-15T21:30:35.780Z","resource_type":"refund","resource_version":"2.0","event_type":"PAYMENT.CAPTURE.REVERSED","summary":"A $ 2.51 USD capture payment was reversed","resource":{"id":"6T496768GW863311H","amount":{"currency_code":"USD","value":"2.51"},"note_to_payer":"Payment reversed","seller_payable_breakdown":{"gross_amount":{"currency_code":"USD","value":"2.51"},"paypal_fee":{"currency_code":"USD","value":"0.00"},"net_amount":{"currency_code":"USD","value":"2.51"},"total_refunded_amount":{"currency_code":"USD","value":"2.51"}},"status":"COMPLETED","create_time":"2018-08-15T14:30:10-07:00","update_time":"2018-08-15T14:30:10-07:00","links":[{"href":"https://api.paypal.com/v2/payments/refunds/09E71677NS257044M","rel":"self","method":"GET"},{"href":"https://api.paypal.com/v2/payments/captures/4L335234718889942","rel":"up","method":"GET"}]},"links":[{"href":"https://api.paypal.com/v1/notifications/webhooks-events/WH-6F207351SC284371F-0KX52201050121307","rel":"self","method":"GET"},{"href":"https://api.paypal.com/v1/notifications/webhooks-events/WH-6F207351SC284371F-0KX52201050121307/resend","rel":"resend","method":"POST"}]}';

/*  */

/* partenr consent revoked * /
$bodyReceived = '{"id": "WH-2WR32451HC0233532-67976317FL4543714","create_time": "2016-08-02T21:41:28Z","resource_type": "partner-consent","event_type": "MERCHANT.PARTNER-CONSENT.REVOKED","resource":{ "merchant_id": "2BUMQ2JXJP9M8" }}';
  /**/
    /** @var Array $headers */
    $headers = getallheaders();
    /*success     [Correlation-Id] => d2bfc31ba6ce1
    [User-Agent] => PayPal/AUHR-214.0-54656248
    [Content-Type] => application/json
    [Paypal-Auth-Algo] => SHA256withRSA
    [Paypal-Cert-Url] => https://api.sandbox.paypal.com/v1/notifications/certs/CERT-360caa42-fca2a594-1d93a270
    [Paypal-Auth-Version] => v2
    [Paypal-Transmission-Sig] => ZTCNDTsZOWPyfgNsx0qhylVrxuqUjTVMcxTOInOhBxaD7lxQm5hLNOIlAPA+R1+UVilQxgaEIM/m2V+s+XcuUgVDfqHL9NZUNyz9V4wwZo+RERXcvoVpLvPI9s+WbcNA1IWxSNNyWBVAVKQkcsuaDkU2IWYmK+rCoBVI+/oG+vhTA32ulZOWxrQCnfAbDXmRwW3qbN2kIn1L
11h7YIfG7W+k5z9xciWOL//ZHXsn5Y/tiYkl/n//NfwTdxkxYqewpXRrZK1KzDw2oBAzdn2vADodHIVn1F2no2CRiRqaNYB0wG+S6YDmIg2gHVPzqwoeU63nvjZvTb/EOrQC/7/biQ==
    [Paypal-Transmission-Time] => 2020-08-05T14:52:27Z
    [Paypal-Transmission-Id] => 47964f20-d72b-11ea-9349-29bf1500b5a9
    [Accept] => *<>/*
    [Content-Length] => 1597
    [Connection] => close
    [X-Forwarded-Proto] => https
    [X-Forwarded-For] => 173.0.82.126
    [Host] => dev5.trueloaded.co.uk
*/

    $whDetails = json_decode($bodyReceived);
//    \Yii::warning(" #### " .print_r($whDetails, 1), 'TLDEBUG-ppp-wh');

    if (/*1 ||*/ $this->whVerify($headers, $bodyReceived, $whDetails)) {
      switch ($whDetails->event_type) {
        case "MERCHANT.ONBOARDING.COMPLETED":
          $this->whBoardingComplete($whDetails);
          break;
        case "MERCHANT.PARTNER-CONSENT.REVOKED":
          $this->whBoardingRevoked($whDetails);
          break;
        case 'PAYMENT.CAPTURE.COMPLETED':
        case 'CHECKOUT.ORDER.APPROVED':
          $this->whCaptured($whDetails);
          break;
        case 'PAYMENT.AUTHORIZATION.VOIDED':
        case 'PAYMENT.CAPTURE.DENIED':
        case 'PAYMENT.CAPTURE.REFUNDED':
        case 'PAYMENT.CAPTURE.REVERSED':
        case 'CHECKOUT.PAYMENT-APPROVAL.REVERSED':
          $this->whCancelled($whDetails);
          break;
        case 'PAYMENT.REFERENCED-PAYOUT-ITEM.COMPLETED':
        case 'PAYMENT.REFERENCED-PAYOUT-ITEM.FAILED':
          \Yii::warning(" #### " .print_r($whDetails, 1), 'TLDEBUG');
          break;
        case 'CUSTOMER.DISPUTE.CREATED':
        case 'CUSTOMER.DISPUTE.UPDATED':
        case 'CUSTOMER.DISPUTE.RESOLVED':
          $this->whInfo($whDetails);
          break;
      }
    }
  }


  protected function whInfo($whDetails) {
    if (is_array($whDetails->resource->disputed_transactions)) { // add info to all transactions/orders
      $processedOrders = [];
      $history = [];
      $history['comments'] = $whDetails->summary;
      $history['comments'] .= "\n" . $whDetails->resource->status;

      foreach ($whDetails->resource->disputed_transactions as $trans) {
        $op = \common\helpers\OrderPayment::searchRecord($this->code, $trans->seller_transaction_id);
        
        if ($op && !empty($op->orders_payment_order_id) && !in_array($op->orders_payment_order_id, $processedOrders)) {
          $processedOrders[] = $op->orders_payment_order_id;
          \common\helpers\Order::setStatus($op->orders_payment_order_id, 0, $history);
        }
      }
    }
  }

/**
 * 
 * @param stdClass $whDetails transaction details
 * @param string $parent_id PP parent transaction id If empty - suppose webHook and update order details (totals and status) else leave as is (do the same in orders controller)
 */
  protected function whCancelled($whDetails, $parent_id = '') {
    /** @var \common\services\PaymentTransactionManager $tm */
    $tm = $this->manager->getTransactionManager($this);
    if ($this->manager->isInstance() ) {
      $order = $this->manager->getOrderInstance();
    } elseif (!empty($whDetails->resource->invoice_id)) {
        $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $whDetails->resource->invoice_id);
    }
    if (!empty($whDetails->resource)) {
      $res = $whDetails->resource;
    } elseif (!empty($whDetails->result)) {
      $res = $whDetails->result;
    } else {
      $res = $whDetails;
    }
    //$updateOrder = empty($parent_id);
    $updateOrder = true;
    if (empty($parent_id) && !empty($res->links) && is_array($res->links)) {
      foreach ( $res->links as $link) {
        if (!empty($link->rel) && $link->rel=='up') {
          if (!empty($link->href) ) {
            $parent_id = substr($link->href,
                (strpos($link->href, '/captures/')??0)+10);
            if (strpos($parent_id, '?')) {
              $parent_id = substr($parent_id, 0, strpos($parent_id, '?'));
            }
          }
          break;
        }
      }
    }
    $parsed = $this->parseTransactionDetails($whDetails);

    $ret = $tm->updatePaymentTransaction($res->id,
        [
          'fulljson' => json_encode($whDetails),
          'status_code' => \common\helpers\OrderPayment::OPYS_REFUNDED,
          'status' => $res->status,
          'amount' => $res->amount->value,
          'comments'  => ($whDetails->summary??$parsed['comments']),
          'date'  => date('Y-m-d H:i:s', strtotime($res->update_time)),
          'parent_transaction_id' => $parent_id,
          'orders_id' => (($order && $order->info['orders_id'])?$order->info['orders_id']:0)
        ]);
    
    parent::updatePaidTotalsAndNotify();

  }

/**
 * @deprecated 1 should be enough
 * 2check if transaction ID is the same as capture then refunded total could be incorrect
 * @param obj $whDetails
 */
  protected function whReversed($whDetails) {
    /** @var \common\services\PaymentTransactionManager $tm */
    $tm = $this->manager->getTransactionManager($this);
    if ($this->manager->isInstance() ) {
      $order = $this->manager->getOrderInstance();
    }
    $ret = $tm->updatePaymentTransaction($whDetails->resource->id,
        [
          'fulljson' => json_encode($whDetails),
          'status_code' => \common\helpers\OrderPayment::OPYS_REFUNDED,
          'status' => $whDetails->resource->status,
          'amount' => $whDetails->resource->amount->value,
          'comments'  => $whDetails->summary,
          'date'  => date('Y-m-d H:i:s', strtotime($whDetails->resource->update_time)),
          'parent_transaction_id' => $whDetails->resource->id,
          'orders_id' => (($order && $order->info['orders_id'])?$order->info['orders_id']:0)
        ]);
    if ($this->manager->isInstance() ) {
      $order = $this->manager->getOrderInstance();
    }

    if ($order && $order->info['orders_id']) {
      $order->update_piad_information(true);
      $order->save_details();
      /// order matters
      if (is_numeric(MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTIONS_ORDER_STATUS_ID) && ( MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTIONS_ORDER_STATUS_ID > 0 )) {
        $history = [];
        if ($ret)  {
          $history['comments'] = $whDetails->summary;
        }
        \common\helpers\Order::setStatus($order->info['orders_id'], MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTIONS_ORDER_STATUS_ID, $history);

      }
    }
  }

  protected function whCaptured($whDetails, $parent_id = '') {
    /** @var \common\services\PaymentTransactionManager $tm */
    $tm = $this->manager->getTransactionManager($this);
    if ($this->manager->isInstance() ) {
      $order = $this->manager->getOrderInstance();
    } elseif (!empty($whDetails->resource->invoice_id)) {
        //tmp or real order id in the transaction details
        $order_id = $whDetails->resource->invoice_id;
        \Yii::warning("\$order_id  #### " .print_r($order_id , true), 'TLDEBUG');
        if (substr($order_id, 0, 3) == 'tmp') {
            $tmp_order_id = substr($order_id, 3);
            \Yii::warning("\$tmp_order_id #### " .print_r($tmp_order_id, true), 'TLDEBUG');
            if ($this->lockTmpOrder($tmp_order_id)) {
                $tmpOrder = $this->manager->getParentToInstanceWithId('\common\classes\TmpOrder', $tmp_order_id);
                $orders_id = $tmpOrder->createOrder();
                $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);
\Yii::warning("\$order created #### " .print_r($order, true), 'TLDEBUG');
                $this->no_process($order);
//comment send 1 email only??
                $order->notify_customer($order->getProductsHtmlForEmail(),[]);
                $this->no_process_after($order);

            } else {
                // already have real order - get its number
                sleep(5);
                $orders_id = \common\models\TmpOrders::find()->where(['orders_id' => $tmp_order_id])->andWhere('child_id > 0')
                        ->select('child_id')
                        ->scalar();
                $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);
\Yii::warning("\$order loaded \$orders_id $orders_id#### " .print_r($order, true), 'TLDEBUG');
            }
        } else {
            $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $order_id);
        }
    }
    if (!empty($whDetails->resource)) {
      $res = $whDetails->resource;
    } elseif (!empty($whDetails->result)) {
      $res = $whDetails->result;
    } else {
      $res = $whDetails;
    }
    $updateOrder = empty($parent_id); // web hook - requires to update order, in admin - it'll be done in controller
    $updateOrder = true;
    if (empty($parent_id) && !empty($res->links) && is_array($res->links)) {
      foreach ( $res->links as $link) {
        if (!empty($link->rel) && $link->rel=='up') {
          if (!empty($link->href) ) {
            $parent_id = substr($link->href,
                (strpos($link->href, '/authorizations/')??0)+strlen('/authorizations/'));
            if (strpos($parent_id, '?')) {
              $parent_id = substr($parent_id, 0, strpos($parent_id, '?'));
            }
          }
          break;
        }
      }
    }
    $parsed = $this->parseTransactionDetails($whDetails);

    $ret = $tm->updatePaymentTransaction($res->id,
        [
          'fulljson' => json_encode($whDetails),
          'status_code' => \common\helpers\OrderPayment::OPYS_SUCCESSFUL,
          'status' => $res->status,
          'amount' => $res->amount->value,
          'comments'  => ($whDetails->summary??$parsed['comments']),
          'date'  => date('Y-m-d H:i:s', strtotime($res->update_time)),
          'parent_transaction_id' => $parent_id,
          'orders_id' => (($order && $order->info['orders_id'])?$order->info['orders_id']:0)
        ]);
    if ($this->manager->isInstance() ) {
      $order = $this->manager->getOrderInstance();
    }
    if ($updateOrder && $order && !empty($order->info['orders_id'])) {

      $updated = $order->updatePaidTotals();
      if ($updated) { //update order status and notify customer if required
        $status = '';
        if (isset($updated['paid']) ) {
          //if ($updated['details']['status']>0) {// has due
          if (abs(
              round($updated['details']['total'], 2)-
              round($updated['details']['debit'], 2)
              ) < 0.01) {
            $status = $this->paidOrderStatus();
          } else {
            $status = $this->partlyPaidOrderStatus();
          }
        } elseif (isset($updated['refund']) && $updated['details']['credit']>0) {
          if (abs(
              round($updated['details']['total'], 2)-
              round($updated['details']['credit'], 2)
              ) < 0.01) {
            $status = $this->refundOrderStatus();
          } else {
            $status = $this->partialRefundOrderStatus();
          }
        } else {// auth orders have full paid amount - not changed
          $orderPaymentStatusArray = \common\helpers\OrderPayment::getTotalStatusArray($order->getOrderId(), round($order->info['total_inc_tax'],2));
          if (abs(
              round($orderPaymentStatusArray['total'], 2)-
              round($orderPaymentStatusArray['debit'], 2)
              ) < 0.01) {
            $status = $this->paidOrderStatus();
          } else {
            $status = $this->partlyPaidOrderStatus();
          }
        }
        if (1 && !empty($status) && $status != $order->info['order_status']) {
          $order->update_status_and_notify($status);
        }

      }
    }





  }

  private function whVerify($headers, $requestBody, $whDetails) {
    $whList = $this->getWebHooks();
    $tmp = parse_url($this->getWebHookUrl());
    $url = $tmp['host'] . $tmp['path'];
    $whId = false;

    if (!empty($whList->webhooks) && is_array($whList->webhooks)) {
      foreach ($whList->webhooks as $el) {
        if (strpos($el->url, $url)!==false && is_array($el->event_types) && 
            ( $el->event_types[0]->name=='*' ||
              in_array($whDetails->event_type, \yii\helpers\ArrayHelper::map($el->event_types, 'name', 'name')))
            ) {
          $whId = $el->id;
          break;
        }
      }
    }

    $output = false;
    if ($whId) { //do not send verification if wbhook Id wasn't found.
      $headers = array_change_key_case($headers, CASE_UPPER);
      $signatureVerification = new \PayPal\Api\VerifyWebhookSignature();
      $signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
      $signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
      $signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
      $signatureVerification->setWebhookId($whId); //0FV983392A0531201 Note that the Webhook ID must be a currently valid Webhook that you created with your client ID/secret.
      $signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
      $signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);

      $signatureVerification->setRequestBody($requestBody);
      $request = clone $signatureVerification;

      try {
        /** @var \PayPal\Api\VerifyWebhookSignatureResponse $output */
        $output = $signatureVerification->post($this->getApiContext());
      } catch (\Exception $ex) {
        \Yii::warning($ex, 'paypal_partner');
        \Yii::warning('request ' . print_r($request, true), 'paypal_partner');
      }
    }
    return ($output && $output->getVerificationStatus() == "SUCCESS");
  }

  private function whBoardingComplete($whDetails) {
    /*
MERCHANT.ONBOARDING.COMPLETED
{
    "id":"WH-68Y618304D271921B-2TF22540HP7127708",
    "event_version":"1.0",
    "create_time":"2017-11-23T15:46:44.521Z",
    "resource_type":"merchant-onboarding",
    "event_type":"MERCHANT.ONBOARDING.COMPLETED",
    "summary":"The merchant account setup is completed",
    "resource":{
        "partner_client_id":"{partner-client-id}",
        "links":[
            … …
        ],
        "merchant_id":"{seller-payer-id}",
        "tracking_id":"{tracking-id}"
    },
    "links":[
        … …
    ]
}     */
    try {
      $check = PaypalPartner\models\SellerInfo::find()->where([
        'tracking_id' => $whDetails->resource->tracking_id,
        'is_onboard ' => 0
      ])->one();
      if ($check) {
        $check->is_onboard = 1;
        $check->save(false);
      }
    } catch (\Exception $e) {
      \Yii::warning($e->getMessage(), 'paypal_partner');
    }
  }

  private function whBoardingRevoked($whDetails) {
    /*
{
   "id":"WH-02N37238V82187421-5PF6629796965892K",
   "event_version":"1.0",
   "create_time":"2017-09-07T08:56:32.812Z",
   "resource_type":"partner-consent",
   "event_type":"MERCHANT.PARTNER-CONSENT.REVOKED",
   "summary":"The Account setup consents has been revoked or the merchant account is closed",
   "resource":{
       "merchant_id":"{seller-payer-id}",
       "tracking_id":"{tracking-id}"
   },
   "links":[
      … …
   ]
}    */
    if (!empty($whDetails->resource->merchant_id) || !empty($whDetails->resource->tracking_id)) {
      try {

        $check = PaypalPartner\models\SellerInfo::find()->filterWhere([
          'payer_id' => $whDetails->resource->merchant_id,
          'tracking_id' => $whDetails->resource->tracking_id, // same merchant could be on several platforms (probably doesn't matter)
          'is_onboard' => 1
        ])->one();

        if ($check) {
          $check->is_onboard = 0;
          $check->save(false);
        }
      } catch (\Exception $e) {
        \Yii::warning($e->getMessage(), 'paypal_partner');
      }
    }
  }


  /**
   * parse getTransactionDetails into $this->transactionInfo
   * @param array $transactionDetails
   */
  public function parseTransactionDetails($transactionDetails) {
/**
 PayPalHttp\HttpResponse Object
(
    [statusCode] => 200
    [result] => stdClass Object
        (
            [id] => 5NY87752NN160122A
            [amount] => stdClass Object
                (
                    [currency_code] => USD
                    [value] => 63.33
                )

            [final_capture] => 1
            [seller_protection] => stdClass Object
                (
                    [status] => ELIGIBLE
                    [dispute_categories] => Array
                        (
                            [0] => ITEM_NOT_RECEIVED
                            [1] => UNAUTHORIZED_TRANSACTION
                        )

                )

            [seller_receivable_breakdown] => stdClass Object
                (
                    [gross_amount] => stdClass Object
                        (
                            [currency_code] => USD
                            [value] => 63.33
                        )

                    [paypal_fee] => stdClass Object
                        (
                            [currency_code] => USD
                            [value] => 2.45
                        )

                    [net_amount] => stdClass Object
                        (
                            [currency_code] => USD
                            [value] => 60.88
                        )

                    [receivable_amount] => stdClass Object
                        (
                            [currency_code] => GBP
                            [value] => 46.09
                        )

                    [exchange_rate] => stdClass Object
                        (
                            [source_currency] => USD
                            [target_currency] => GBP
                            [value] => 0.757046355
                        )

                )

            [status] => COMPLETED
            [create_time] => 2020-09-01T09:55:07Z
            [update_time] => 2020-09-01T10:00:02Z
            [links] => Array
                (
                    [0] => stdClass Object
                        (
                            [href] => https://api.sandbox.paypal.com/v2/payments/captures/5NY87752NN160122A
                            [rel] => self
                            [method] => GET
                        )

                    [1] => stdClass Object
                        (
                            [href] => https://api.sandbox.paypal.com/v2/payments/captures/5NY87752NN160122A/refund
                            [rel] => refund
                            [method] => POST
                        )

                    [2] => stdClass Object
                        (
                            [href] => https://api.sandbox.paypal.com/v2/checkout/orders/6BV62129B0245622C
                            [rel] => up
                            [method] => GET
                        )

                )

        )
 */
    $this->transactionInfo = [];
    if (!empty($transactionDetails->resource)) {
      $res = $transactionDetails->resource;
    } elseif (!empty($transactionDetails->result)) {
      $res = $transactionDetails->result;
    }
    if ($res && !empty($res->id)) {

      if (!empty($res->status_details->reason)) {
        $comment =  $res->status_details->reason;
      } else {
        $comment =  $res->status . ' ' . ucfirst($this->transactionType($transactionDetails)) . ' ' . $res->amount->value . $res->amount->currency_code;
      }

        if (strtolower($this->transactionType($transactionDetails)) != 'refund') {
            $ppOrder = new \stdClass();
            if (!empty($transactionDetails->result->supplementary_data->related_ids->order_id) ) {
                $ppId = $transactionDetails->result->supplementary_data->related_ids->order_id;
            }
            if (!empty($ppId)) {
                $ppOrder = $this->getOrder($ppId);
            }
            $pp_result = $this->extractComments($ppOrder, $transactionDetails);
            $comment .= "\n" . implode("\n", $pp_result);
        }

      $this->transactionInfo['status'] = $res->status;
      $this->transactionInfo['status_code'] = $this->getStatusCode($transactionDetails);
      $this->transactionInfo['transaction_id'] = $res->id;
      $this->transactionInfo['amount'] = $res->amount->value;
      $this->transactionInfo['fulljson'] = json_encode($transactionDetails);
      //$this->transactionInfo['last_updated'] = str_replace(['T', 'Z'], [' ', ''], $res->update_time);
      $this->transactionInfo['last_updated'] = date(\common\helpers\Date::DATABASE_DATETIME_FORMAT, strtotime($res->update_time));

      $this->transactionInfo['comments'] = $comment;
    }

    return $this->transactionInfo;
  }

    public function parseBoardingDetails($response, $date = '') {
        if (empty($date)) {
            $date = date(\common\helpers\Date::DATABASE_DATE_FORMAT);
        }
        $info = $errors = $warnings = [];
        if (!empty($response)) {
            $oAuth = $response->getOauthIntegrations();
            $scopes = $oAuth[0]->oauth_third_party[0]->scopes??'';

            if (!$response->getPaymentsReceivable() ) {
                $errors[] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR_RECEIVABLE;
            }
            if (!$response->getPrimaryEmailConfirmed()) {
                $errors[] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR_EMAIL;
            }
            if (empty($scopes) && $this::BOARDING_MODE == 3) {
                $errors[] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR_PERMISSIONS;
            }
            
            $prods = $response->getProducts();
            $_capabilities = $response->getСapabilities();


            if (!empty($prods) && is_array($prods)) {
                foreach ( $prods as $prod  ) {
                    $cCap = $this->findCapabilities($_capabilities, 'CUSTOM_CARD_PROCESSING');
                    $cWithdraw = $this->findCapabilities($_capabilities, 'WITHDRAW_MONEY');
                    $cSend = $this->findCapabilities($_capabilities, 'SEND_MONEY');
                    /*
If "products[name==PPCP_CUSTOM].vetting_status:" is SUBSCRIBED
and "capabilities[name==CUSTOM_CARD_PROCESSING].status" is ACTIVE
and "capabilities[name==CUSTOM_CARD_PROCESSING].limits[0].type" is GENERAL
and "capabilities[name==WITHDRAW_MONEY].limits" is anything but undefinied
and "capabilities[name==SEND_MONEY].limits" is anything but undefinited
                     */
                    if ($prod->name == 'PPCP_CUSTOM' && $prod->vetting_status == 'SUBSCRIBED' &&
                         !empty($cCap['status']) && $cCap['status'] == 'ACTIVE' &&
                         !empty($cCap['limits'][0]['type']) && $cCap['limits'][0]['type'] == 'GENERAL' &&
                         isset($cWithdraw['limits']) &&
                         isset($cSend['limits'])
                        ){
                        $warnings[] = PAYPAL_PARTNER_SELLER_BOARDED_500_WARNING_ACTIVE;
                    }

/*
"products[name].vetting_status:" is SUBSCRIBED and
"capabilities[name==CUSTOM_CARD_PROCESSING].status" is ACTIVE and
"capabilities[name==CUSTOM_CARD_PROCESSING].limits[0].type" is GENERAL and
"capabilities[name==WITHDRAW_MONEY].limits" is undefinied and
"capabilities[name==SEND_MONEY].limits" is undefinited
*/
                    elseif ($prod->name == 'PPCP_CUSTOM' && $prod->vetting_status == 'SUBSCRIBED' &&
                         !empty($cCap['status']) && $cCap['status'] == 'ACTIVE' &&
                         !empty($cCap['limits'][0]['type']) && $cCap['limits'][0]['type'] == 'GENERAL' &&
                         !isset($cWithdraw['limits']) &&
                         !isset($cSend['limits'])
                        ){
                        $warnings[] = PAYPAL_PARTNER_SELLER_BOARDED_500_WARNING;
                    }

                    elseif ($prod->name == 'PPCP_CUSTOM' && $prod->vetting_status == 'NEED_MORE_DATA' ){
                        $warnings[] = PAYPAL_PARTNER_SELLER_BOARDED_CCF_VETTING;
                    }
                    elseif ($prod->name == 'PPCP_CUSTOM' && $prod->vetting_status == 'IN_REVIEW' ){
                        $warnings[] = PAYPAL_PARTNER_SELLER_BOARDED_CCF_VETTING_REVIEW;
                    }
                    elseif ($prod->name == 'PPCP_CUSTOM' && $prod->vetting_status == 'DENIED' ){
                        $warnings[] = sprintf(PAYPAL_PARTNER_SELLER_BOARDED_CCF_VETTING_DENIED, \common\helpers\Date::date_short($date));
                    }
                    
                }
            }

            $_tmp = false;
            if (!empty($scopes)) {
                $_tmp = $this->checkScopes($scopes, true);
            }
            if (is_array($_tmp) && !empty($_tmp['recommended'])) {
                $warnings = array_merge($warnings, $_tmp['recommended']);
            }

            /// info
            $info = [];
            foreach ([
                'PAYPAL_PARTNER_LEGAL_NAME' => ['Legal name','getLegalName'],
                'PAYPAL_PARTNER_SELLER_MERCHANT_ID' => ['Merchant id','getMerhantId'],
                'PAYPAL_PARTNER_PAYMENTS_RECEIVABLE' => ['Payments receivable','getPaymentsReceivable'],
                'PAYPAL_PARTNER_PRIMARY_EMAIL' => ['Primary e-mail','getPrimaryEmail'],
                'PAYPAL_PARTNER_PRIMARY_CURRENCY' => ['Primary currency','getPrimaryCurrency'],
                //'PAYPAL_PARTNER_' => ['',''],
              ] as $k => $inf
            ) {
                if (!empty($inf)) {
                    $val = $response->{$inf[1]}();
                    if ($val!='') {
                        $info[defined($k)?constant($k):$inf[0]] = $val;
                    }
                }
            }

            $products = [];
            if (false && !empty($prods) && is_array($prods)) {
                foreach ( $prods as $prod  ) {
                    $str = "<b>{$prod->name}</b><br />\n"
                        . "Vetting Status: <b>{$prod->vetting_status}</b><br />\n";
                     if (!empty($prod->capabilities) && is_array($prod->capabilities)) {
                         $str .= implode(" ", $prod->capabilities); // <BR />\n
                     }
                    $products[] = $str ;
                }
                $info['Products'] = '<small>' . implode("<BR /><BR />\n", $products) . "<BR /></small>\n";
            }

            $capabilities = [];
            if (!empty($_capabilities) && is_array($_capabilities)) {
                foreach ( $_capabilities as $_capability ) {
                    if (!empty($_capability['status'])) {
                        $status = '<span class="paypal-capabilities glyphicon ' . strtolower($_capability['status']) . '">' . $_capability['status'] . '</span>';
                    } else {
                        $status = $_capability['status']??'';
                    }

                    if (!empty($_capability['name'])) {
                        $str =
                          (defined('PAYPAL_PARTNER_' . strtoupper($_capability['name']) . '_TEXT')?
                          constant('PAYPAL_PARTNER_' . strtoupper($_capability['name']) . '_TEXT'):
                          $_capability['name']) . ": " . $status . (!empty($_capability['limits']) && is_scalar($_capability['limits']) ? $_capability['limits'] : '') . "";
                        $capabilities[] = $str;
                    }
                }
                $info[
                  defined('PAYPAL_PARTNER_CAPABILITIES')?PAYPAL_PARTNER_CAPABILITIES:'Capabilities'
                  ] = '<div>' . implode("</div><div>\n", $capabilities) . "</div>\n";
            }
            /// info end


        } else {
            $errors[] = PAYPAL_PARTNER_SELLER_BOARDED_ERROR;
        }

        return [
                'info' => $info,
                'errors' => $errors,
                'warnings' => $warnings,
                ];

    }

    public function findCapabilities($_capabilities, $name) {
        if (!empty($_capabilities) && is_array($_capabilities)) {
                foreach ( $_capabilities as $_capability ) {
                    if ($_capability['name'] == $name) {
                        return $_capability;
                    }
                }
        }
        return [];
    }

    public static function getPartnerId($force = false) {
        if (isset($this) && $this instanceof self) {
            $mode = $this->getMode();
        } else {
            $mode = self::getMode();
        }
        if (!empty($force)) {
            $mode = $force;
        }
        if ($mode == 'Live') {
            $ret = self::PARTNER_MERCHANT_ID;
        } else {
            $ret = self::PARTNER_MERCHANT_SANDBOX_ID;
        }
        return $ret;
    }


    public static function getAttributionId() {
        $ret = self::PARTNER_ATTRIBUTION_ID;
        if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER') && MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_SERVER == 'Sandbox') {
            $ret = self::SANDBOX_PARTNER_ATTRIBUTION_ID;
        }
        if (empty($ret)) {
            $ret = false;
        }
        return $ret;
    }

    protected function _getClientId() {
        if ($this->getMode() == 'Live') {
            $ret = self::PARTNER_APP_CLIENT_ID;
        } else {
            $ret = self::PARTNER_APP_SANDBOX_CLIENT_ID;
        }
        if ($this->hasOwnKeys()) {
            $platformId = $this->getPlatformId();
            $seller = $this->getSeller($platformId);
            $ret = $seller->own_client_id;
        }

        return $ret;
    }

    protected function _getClientSecret() {
        if ($this->getMode() == 'Live') {
            $ret = self::PARTNER_APP_CLIENT_SECRET;
        } else {
            $ret = self::PARTNER_APP_SANDBOX_CLIENT_SECRET;
        }

        $platformId = $this->getPlatformId();
        $seller = $this->getSeller($platformId);
        if (!empty($seller->own_client_id) && !empty($seller->own_client_secret)) {
            $ret = $seller->own_client_secret;
        } else {
            if (defined('PAYPAL_PARTNER_KEYWORD') && !empty(constant('PAYPAL_PARTNER_KEYWORD'))) {
                $kw = constant('PAYPAL_PARTNER_KEYWORD');
                $ret = \Yii::$app->security->decryptByKey(utf8_decode(base64_decode($ret)), substr($kw, 1));
            }
        }

        return $ret;
    }

    protected function _validateKey($key, $platform_id) {
        $ret = false;
        if (!empty($key)) {
            if (defined('PAYPAL_PARTNER_KEYWORD') && !empty(constant('PAYPAL_PARTNER_KEYWORD'))) {
                $kw = constant('PAYPAL_PARTNER_KEYWORD');
                $ret = !empty(\Yii::$app->security->decryptByKey(utf8_decode(base64_decode($key)), substr($kw, 1)));
            } else {
                //return true;
            }
        }

        return $ret;
    }

    protected function _getIntent() {
        if (method_exists($this, 'forcePreAuthorizeMethod') && $this->forcePreAuthorizeMethod()) {
            return 'authorize';
        }
        if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_METHOD')) {
            $ret = MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_METHOD;
        } else {
            $platformId = $this->getPlatformId();
            $platform_config = new \common\classes\platform_config($platformId);
            $ret = $platform_config->const_value('MODULE_PAYMENT_PAYPAL_PARTNER_TRANSACTION_METHOD', 'sale');
        }
        return strtolower($ret);
    }

    public static function get3DSSettings($seller) {
        $ret = \common\modules\orderPayment\paypal_partner::$threeDSDefaults;
        if (!empty($seller->three_ds_settings)) {
            $ret = json_decode($seller->three_ds_settings, true);
        }
        $ret['defaults'] = \common\modules\orderPayment\paypal_partner::$threeDSDefaults;
        $ret['3dsa'] = [];
        if (is_array(\common\modules\orderPayment\paypal_partner::$threeDSDefaults)) {
            foreach(\common\modules\orderPayment\paypal_partner::$threeDSDefaults as $k => $dfVal) {
                if (substr($k, 0, 5) == '3dsa_') {
                    $tmp = explode('_', substr($k, 5));
                    $row = [];
                    if (!empty($tmp[0])) {
                        $row['e_desc'] = defined('TEXT_PAYPAL_PARTNER_3DSA_ENROLLMENT_' . strtoupper($tmp[0]). '_DESCRIPTION')?constant('TEXT_PAYPAL_PARTNER_3DSA_ENROLLMENT_' . strtoupper($tmp[0]). '_DESCRIPTION') : strtoupper($tmp[0]);
                        $row['e'] = defined('TEXT_PAYPAL_PARTNER_3DSA_ENROLLMENT_' . strtoupper($tmp[0]))?constant('TEXT_PAYPAL_PARTNER_3DSA_ENROLLMENT_' . strtoupper($tmp[0])) : strtoupper($tmp[0]);
                    } else {
                        $row['e_desc'] = $row['e'] ='';
                    }
                    if (!empty($tmp[1])) {
                        $row['a_desc'] = defined('TEXT_PAYPAL_PARTNER_3DSA_AUTHENTICATION_' . strtoupper($tmp[1]). '_DESCRIPTION')?constant('TEXT_PAYPAL_PARTNER_3DSA_AUTHENTICATION_' . strtoupper($tmp[1]). '_DESCRIPTION') : strtoupper($tmp[1]);
                        $row['a'] = defined('TEXT_PAYPAL_PARTNER_3DSA_AUTHENTICATION_' . strtoupper($tmp[1]))?constant('TEXT_PAYPAL_PARTNER_3DSA_AUTHENTICATION_' . strtoupper($tmp[1])) : strtoupper($tmp[1]);
                    } else {
                        $row['a_desc'] = $row['a'] ='';
                    }
                    if (!empty($tmp[2])) {
                        switch ($tmp[2]) {
                            case 1:
                                $row['l_desc'] = defined('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_POSSIBLE_DESCRIPTION')?constant('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_POSSIBLE_DESCRIPTION') : 'POSSIBLE';
                                $row['l'] = defined('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_POSSIBLE')?constant('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_POSSIBLE') : 'POSSIBLE';
                                break;
                            case 2:
                                $row['l_desc'] = defined('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_NO_DESCRIPTION')?constant('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_NO_DESCRIPTION') : 'NO';
                                $row['l'] = defined('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_NO')?constant('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_NO') : 'NO';
                                break;
                            case 3:
                                $row['l_desc'] = defined('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_UNKNOWN_DESCRIPTION')?constant('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_UNKNOWN_DESCRIPTION') : 'UNKNOWN';
                                $row['l'] = defined('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_UNKNOWN')?constant('TEXT_PAYPAL_PARTNER_3DSA_LIABILITY_UNKNOWN') : 'UNKNOWN';
                                break;
                        }
                    } else {
                        $row['l_desc'] = $row['l'] ='';
                    }

                    $row['def_state_class'] = (!empty($ret['defaults'][$k])?'def-checked':'def-unchecked');
                    $row['checked'] = (!empty($ret[$k]) || (!isset($ret[$k]) && $ret['defaults'][$k]));
                    $row['key'] = $k;
                    
                    $ret['3dsa'][] = $row;
                }
            }
        }

        return $ret;
    }

    public function getFundings() {
        $ret = [];
        if (defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_FUNDING') && !empty(MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_FUNDING)) {
            $fundings = array_map('trim', explode(',', MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_FUNDING));
            if (!in_array('', $fundings) && !in_array('all', $fundings) && !(in_array('--none--', $fundings) && count($fundings)==1)) {
                $enabled = array_values(array_diff($fundings, ['', 'all', '--none--']));
                $disabled = array_diff(array_keys(self::$possibleFundings), $enabled);

                if (!empty($enabled)) {
                    $ret['enabled'] = implode(',', array_map('trim', $enabled));
                }
                if (!empty($disabled)) {
                    $ret['disabled'] = implode(',', $disabled);
                }
            }
            //$ret['enabled']
        }
        return $ret;

    }

    //Custom Card processing status
    public function CCPActive($seller) {
        $ret = false;
        if (!empty($seller->boarding_json)) {
            $merchant = new PaypalPartner\api\Merchant();
            $response = $merchant->fromJson($seller->boarding_json);


            //Settings should be shown only if the following values are returned from the Show Seller Status API call:
            //"payments_receivable": true,
            //"primary_email_confirmed": true
            //"products[name==PPCP_CUSTOM].vetting_status:":SUBSCRIBED
            //"capabilities[name==CUSTOM_CARD_PROCESSING].status": ACTIVE

            if ($response && $response->getPaymentsReceivable() && $response->getPrimaryEmailConfirmed()) {
                $prods = $response->getProducts();
                $_capabilities = $response->getСapabilities();
                $cCap = $this->findCapabilities($_capabilities, 'CUSTOM_CARD_PROCESSING');
                if (!empty($prods) && is_array($prods) && !empty($cCap['status']) &&  $cCap['status'] == 'ACTIVE') {

                    foreach ( $prods as $prod  ) {
                        if ($prod->name == 'PPCP_CUSTOM') {
                            $ret = true;
                            break;
                        }
                    }
                }
            }
        }

        return $ret;
    }

    public function generateCCToken() {

    }

    public function generateCCCustomerDetails() {
        $ret = [];
        $post_data = \Yii::$app->request->post();
        $billto = $post_data['billing_ab_id']??false;
        if ($billto) {
            $tmp = $this->manager->getCustomersAddress($billto, true, true);
        }

        if (!array($tmp) || empty($tmp['street_address'])) {
            /** @var \common\classes\Order $order */
            if (!$this->manager->isInstance()) {
                $order = $this->manager->createOrderInstance('\common\classes\Order');
            } else {
                $order = $this->manager->getOrderInstance();
            }
            $tmp = $order->billing??null;
        }

        if (!array($tmp) || empty($tmp['street_address'])) {
            $tmp = $this->manager->getBillingAddress();
        }

        if (!array($tmp) || empty($tmp['street_address']) && !empty($post_data['Billing_address'])) {
            foreach (['firstname', 'lastname', 'street_address', 'city', 'postcode'] as $k) {
                if (empty($tmp[$k]) && !empty($post_data['Billing_address'][$k])) {
                    $tmp[$k] = strip_tags($post_data['Billing_address'][$k]);
                }
            }
            if ($post_data['Billing_address']['country'] != $tmp['country']['id']) {
                $_country = \common\helpers\Country::get_country_info_by_id($post_data['Billing_address']['country']);
                if (!empty($_country['iso_code_2'])) {
                    $tmp['country'] = $_country;
                }
            }
        }

        if ($tmp['zone_id'] > 0) {
            $country_iso = $tmp['country']['countries_iso_code_2']??$tmp['country']['iso_code_2'];
            $country_id = ($tmp['country']['countries_id']??$tmp['country']['id'])??$tmp['country_id'];
            if ($country_iso == 'US') {
                $_state = \common\helpers\Zones::get_zone_code($country_id, $tmp['zone_id'], '');
            } else {
                $_state = \common\helpers\Zones::get_zone_name($country_id, $tmp['zone_id'], $tmp['state']);
            }
            if (!empty($_state)) {
                $tmp['state'] = $_state;
            }
        }

        if (!empty($tmp['firstname']) && !empty($tmp['lastname'])) {
            $ret['cardholderName'] = substr($tmp['firstname'] . ' ' . $tmp['lastname'], 0, 140);
        }
        if (!empty($tmp['street_address']) && !empty($tmp['postcode']) ) {
            $ret['billingAddress'] = [
              'streetAddress' => substr($tmp['street_address'], 0, 300),
              'extendedAddress' => substr($tmp['suburb'], 0, 300),
              'locality' => substr($tmp['city'], 0, 120),
              'region' => substr($tmp['state'], 0, 300),
              'postalCode' => substr($tmp['postcode'], 0, 60),
              'countryCodeAlpha2' => substr($tmp['country']['countries_iso_code_2']??$tmp['country']['iso_code_2'], 0, 60),//
            ];
        }
        $seller = $this->getSeller($this->manager->getPlatformId());
        //$contingencies = 'SCA_WHEN_REQUIRED';
        if (!empty($seller->three_ds_settings)) {
            $tmp = json_decode($seller->three_ds_settings, true);
            if (!empty($tmp['status']) && !empty($tmp['contingencies'])) {
                //$contingencies = $tmp['contingencies'];
                $ret['contingencies'] = [$tmp['contingencies']];
                //$ret['contingencies'] = [$contingencies];
            }
        }

        return $ret;
    }

/**
 *
 * @param array $data ["transaction_id" => "8MC585209K746392H"{, "tracking_number" => "443844607820", "carrier" => "FEDEX", "status" => "SHIPPED", 'carrier_name_other'}]
 * @return obj|array|false
 */
    public function addTracking($data) {
        $request = new \PayPalCheckoutSdk\Shipping\TrackersBatchRequest();
        if (false && $tmp = self::getAttributionId()) {
            $request->payPalPartnerAttributionId($tmp);
        }
        $trackers = [];
        if (!empty($data) && is_array($data)) {
            foreach ($data as $d) {
                $tmp = ["status" => "SHIPPED", "carrier" => "OTHER"];
                foreach (['transaction_id', 'tracking_number', 'carrier', 'carrier_name_other', 'status'] as $key) {
                    if (!empty($d[$key])) {
                        $tmp[$key] = $d[$key];
                    }
                }
                if (!empty($tmp['transaction_id'])) {
                    $trackers[] = $tmp;
                }
            }
        }
        if (empty($trackers)) {
            return ['error' => 1, 'message' => TEXT_ERROR_TRANSACTIONID_REQUIRED];
        }

        $request->body = ['trackers' => $trackers];
        if ($this->debug) {
            \Yii::warning(" #### " .print_r($request->body, true), 'TLDEBUG' . $this->code);
        }

        try {
            return $this->getHttpClient()->execute($request);
        } catch (\Exception $ex) {

            \Yii::error('body ' . print_r($request->body, true), 'paypal_partner_exception');
            \Yii::error('message ' . $ex->getMessage(), 'paypal_partner_exception');
            if ($tmp = $this->parseJsonMessage($ex->getMessage())) {
                return ['error' => 1, 'message' => $tmp];
            }
        }
        return false;
    }

/**
 *
 * @param string $traker_id
 * @param array $data ["transaction_id" => "8MC585209K746392H"{, "tracking_number" => "443844607820", "carrier" => "FEDEX", "status" => "CANCELLED", 'carrier_name_other'}]
 * @return obj|array|false
 */
    public function cancelTracking($tracker_id, $data) {
        $request = new \PayPalCheckoutSdk\Shipping\TrackersPatchRequest($tracker_id);
        if (false && $tmp = self::getAttributionId()) {
            $request->payPalPartnerAttributionId($tmp);
        }
        $trackers = [];
        if (!empty($data) && is_array($data)) {
            $tmp = ["status" => "CANCELLED"];
            foreach (['transaction_id', 'tracking_number', 'carrier', 'carrier_name_other', 'status'] as $key) {
                if (!empty($data[$key])) {
                    $tmp[$key] = $data[$key];
                }
            }
            if (!empty($tmp['transaction_id'])) {
                $trackers = $tmp;
            }
        }
        if (empty($trackers)) {
            return ['error' => 1, 'message' => TEXT_ERROR_TRANSACTIONID_REQUIRED];
        }

        $request->body = $trackers;
        if ($this->debug) {
            \Yii::warning(" #### " .print_r($request->body, true), 'TLDEBUG' . $this->code);
        }

        try {
            return $this->getHttpClient()->execute($request);
        } catch (\Exception $ex) {

            \Yii::error('body ' . print_r($request->body, true), 'paypal_partner_exception');
            \Yii::error('message ' . $ex->getMessage(), 'paypal_partner_exception');
            if ($tmp = $this->parseJsonMessage($ex->getMessage())) {
                return ['error' => 1, 'message' => $tmp];
            }
        }
        return false;
    }


}