<?php
namespace common\modules\orderPayment\lib;
//2do orderpay - do not alllow change address
//2check customer details update- refresh applerequest
//    https://developer.apple.com/documentation/apple_pay_on_the_web/applepaysession/1778008-completeshippingcontactselection


/**
 * Applepay and googlepay related
 */

trait PaypalPartnerTraitWallets {

/**
 * Process payment JS
 */
    protected function registerWalletProcessJs() {
        $ret = '';
        if ($this->isApplePayAllowed()) {
            $ret = '';
            $paymentRequestUrlOnShipping = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'aplpPaymentRequestSh']);
            $paymentRequestUrlOnShippingCost = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'aplpPaymentRequestShCost']);
            $createUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'createOrder', 'option' => 'applepay', 'partlypaid' => $this->isPartlyPaid()]);
            $retrieveUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'retrieveOrder', 'partlypaid' => $this->isPartlyPaid()]);
            $generalError = str_replace(["'", "\n"], ["\\'", '<br>'], defined('PAYPAL_PARTNER_TEXT_ERROR_CAPTURE')?PAYPAL_PARTNER_TEXT_ERROR_CAPTURE:'Payment could not be captured.');
            $generalErrorModule = str_replace(["'", "\n"], ["\\'", '<br>'], defined('MODULE_PAYMENT_PAYPAL_PARTNER_GENERAL_ERROR') ? MODULE_PAYMENT_PAYPAL_PARTNER_GENERAL_ERROR : "Can't create order");
            $storeName = $this->pppAppleMerchantName();
            $_dbg = $this->debug?1:0;

            $ret = "

var {$this->code}applepay, {$this->code}applepayConfig, {$this->code}AplpPaymentRequest = null;

window.{$this->code}consoleDebug = function (message) {
    if ($_dbg) {
        console.log(message);
    }
}

window.{$this->code}_callback_aplp = function () {
try {
    window.{$this->code}consoleDebug('applePaymentRequest');
    window.{$this->code}consoleDebug({$this->code}AplpPaymentRequest);
    const {$this->code}AplpSession = new ApplePaySession(4, {$this->code}AplpPaymentRequest);


{$this->code}AplpSession.onpaymentauthorized = (event) => {
    window.{$this->code}consoleDebug('Your billing address is:', event.payment.billingContact);
    window.{$this->code}consoleDebug('Your shipping address is:', event.payment.shippingContact);
    window.{$this->code}consoleDebug('full event:', event);
    let to_post = { 'billing_address': event.payment.billingContact, 'shipping_address': event.payment.shippingContact };


    fetch('{$createUrl}', {
      method: 'post' ,
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: 'aplp='+JSON.stringify(to_post)
    })
    .then(function(res) {
        let ret = res.json();
        window.{$this->code}consoleDebug('res:');
        window.{$this->code}consoleDebug(ret);
        return ret; })
    .then(function(data) {
        window.{$this->code}consoleDebug('data:');
        window.{$this->code}consoleDebug(data);
        window.{$this->code}consoleDebug(data.id);
        try {
            if (data.hasOwnProperty('error') && data.hasOwnProperty('message') ) {
                alertMessage(data.message);
                {$this->code}AplpSession.completePayment(ApplePaySession.STATUS_FAILURE);
                console.log(data);
            }
        } catch ( e ) {
            {$this->code}AplpSession.completePayment(ApplePaySession.STATUS_FAILURE);
          console.log(e);
          return false
        }
            
        var orderId = data.id;
        window.{$this->code}consoleDebug({$this->code}applepay);
        window.{$this->code}consoleDebug('pppapplepay.confirmOrder');
        window.{$this->code}consoleDebug({
                  orderId: orderId,
                  token: event.payment.token,
                  billingContact: event.payment.billingContact
                });

        {$this->code}applepay.confirmOrder({
          orderId: orderId,
          token: event.payment.token,
          billingContact: event.payment.billingContact
        })
        .then(confirmResult => {
            /*confirmResult.approveApplePayPayment.status == 'APPROVED'??*/
            var orderId = confirmResult.approveApplePayPayment.id;

            $('body').append('<div class=\"popup-box-wrap\"><div class=\"around-pop-up\"></div><div class=\"preloader\"></div></div>');
                return fetch('{$retrieveUrl}&id=' + orderId, {
                    method: 'POST'
                }).then(function(res) {
                    return res.json();
                }).then(function(res) {
                    if (!res.ok) {
                        {$this->code}AplpSession.completePayment(ApplePaySession.STATUS_FAILURE);
                        $('.popup-box-wrap').remove();
                        alertMessage(res.error);
                    } else {
                        {$this->code}AplpSession.completePayment(ApplePaySession.STATUS_SUCCESS);
                        window.location.href = res.url;
                    }
                });
        })
        .catch(confirmError => {
            $('.popup-box-wrap').remove();
            console.error('{$generalError}');
            if (confirmError) {
                alertMessage('{$generalError}' + ' <br>' + confirmError);
                console.error(confirmError);
                {$this->code}AplpSession.completePayment(ApplePaySession.STATUS_FAILURE);
            }
        });
    })

    .catch(createError => {
        $('.popup-box-wrap').remove();
        console.error('{$generalErrorModule}');
        if (createError ) {
            console.error(createError );
            {$this->code}AplpSession.completePayment(ApplePaySession.STATUS_FAILURE);
            alertMessage('{$generalErrorModule}' + ' <BR>' + createError);
        }
    });

};


{$this->code}AplpSession.onshippingmethodselected = (event) => {
    window.{$this->code}consoleDebug('onshippingmethodselected inp param');
    window.{$this->code}consoleDebug(event);
    let shippingMethodSelected = event.shippingMethod;
        /*debug
        shippingMethodSelected = {
            'label': 'Shipping',
            'detail': 'Arrives in 5 to 7 days',
            'amount': '1.20',
            'identifier': 'flat_flat'
        };
            $.ajax({
                url: 'http://127.0.0.1/e/tln/callback/webhooks.payment.paypal_partner?action=aplpPaymentRequestShCost',
                data: {selected_shipping_option: shippingMethodSelected},
                type: 'POST',
                success: function(data){ console.log(data); }
            });
        */

    $.ajax({
        url: '{$paymentRequestUrlOnShippingCost}',
        data:  {selected_shipping_option: shippingMethodSelected},
        async: false,
        type: 'POST',
        success: function(data){
            if (data.hasOwnProperty('newTotal')  ) {
                {$this->code}AplpSession.completeShippingMethodSelection(ApplePaySession.STATUS_SUCCESS, data.newTotal, []);
            } else { // error
                {$this->code}AplpSession.completeShippingMethodSelection(ApplePaySession.STATUS_FAILURE, {}, []);
            }
        }
    });
}

{$this->code}AplpSession.onshippingcontactselected = (event) => {
    window.{$this->code}consoleDebug('onshippingcontactselected inp param');
    window.{$this->code}consoleDebug(event);
    let myShippingContact = event.shippingContact;
        /*debug
        myShippingContact = {
        'locality': 'Cupertino',
        'country': 'United States',
        'postalCode': '95014-2083',
        'administrativeArea': 'CA',
        'emailAddress': 'ravipatel@example.com',
        'familyName': 'Patel',
        'addressLines': [
        '1 Infinite Loop'
        ],
        'givenName': 'Ravi',
        'countryCode': 'US',
        'phoneNumber': '(408) 555-5555'
        };
            $.ajax({
                url: 'http://127.0.0.1/e/tln/callback/webhooks.payment.paypal_partner?action=aplpPaymentRequestSh',
                data: myShippingContact,
                type: 'POST',
                success: function(data){ console.log(data); }
            });
        */

    $.ajax({
        url: '{$paymentRequestUrlOnShipping}',
        data:  {shipping_address: myShippingContact},
        async: false,
        type: 'POST',
        success: function(data){
            if (data.hasOwnProperty('newTotal') ) {
                var li = [];
                if (data.hasOwnProperty('newLineItems') ) {
                    li = data.newLineItems;
                }
                {$this->code}AplpSession.completeShippingContactSelection(ApplePaySession.STATUS_SUCCESS, data.newShippingMethods, data.newTotal, li);
            } else { // error

                //let res = { status:  ApplePaySession.STATUS_FAILURE };
                {$this->code}AplpSession.completeShippingContactSelection(ApplePaySession.STATUS_FAILURE, [], {}, []);
            }
        }
    });
}


{$this->code}AplpSession.onvalidatemerchant = (event) => {
    {$this->code}applepay.validateMerchant({
      validationUrl: event.validationURL,
      displayName: '{$storeName}'
    })
    .then(validateResult => {
      {$this->code}AplpSession.completeMerchantValidation(validateResult.merchantSession);
    })
    .catch(validateError => {
      console.error(validateError);
      {$this->code}AplpSession.abort();
    });
};

    {$this->code}AplpSession.begin();
} catch (e) {console.log(e); }

        }
";
        }

        \Yii::$app->getView()->registerJs($ret, \common\components\View::POS_HEAD);
    }


/**
 * show buttons
 */
    protected function registerWalletInitJs() {
        $ret = $aplpret = '';

//<div id="{$this->code}-applepay-container" class="{$this->code}"></div>
        if ($this->isApplePayAllowed()) {
            $paymentRequestUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'aplpPaymentRequest', 'partlypaid' => $this->isPartlyPaid()]);

            $extraApplePayPaymentRequestParams = '';
            if (!empty($this->onlySiteAddress)) {
                $extraApplePayPaymentRequestParams .= "data.ApplePayShippingContactEditingMode='storePickup';\n";
            }
            $partlyPaid = ($this->isPartlyPaid()?1:0);
            //2do supportedCountries ['iso2']

            $aplpinitJsLib = <<< EOD

window.{$this->code}AplpBillingDetails = function () {
    window.{$this->code}consoleDebug('in AplpBillingDetails');
    if (!window.ApplePaySession || !ApplePaySession.canMakePayments() || typeof {$this->code}applepayConfig == 'undefined') {
        return false;
    }

    ///get billing details
    var bodyData = '';
    if (typeof(paymentCollection)!='undefined' && paymentCollection.form) {
        bodyData = $(paymentCollection.form).serialize();
    }

    $.ajax({
        url: '{$paymentRequestUrl}',
        data: bodyData,
        async: false,
        type: 'POST',
        dataType: 'json',
        success: function(data){
                data.merchantCapabilities = {$this->code}applepayConfig.merchantCapabilities;
                data.supportedNetworks = {$this->code}applepayConfig.supportedNetworks;
                data.requiredShippingContactFields = ['name', 'phone', 'email', 'postalAddress'];
                if ({$partlyPaid} || 
                    (data.hasOwnProperty('ApplePayShippingContactEditingMode') && data.ApplePayShippingContactEditingMode=='storePickup')
                    ||
                    (data.hasOwnProperty('shippingType') && data.shippingType=='servicePickup')
                    ) {
                    data.requiredShippingContactFields = [];
                }
                data.requiredBillingContactFields = ['name', 'phone', 'email', 'postalAddress'];
                {$extraApplePayPaymentRequestParams}


                {$this->code}AplpPaymentRequest = data;
                try {
                    $('.paypal-button-container .{$this->code}-applepay-container').show();
                } catch (e ) {}
        }
    });
}
EOD;
        $color = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_COLOR') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_COLOR : 'gold';
        if ($color=='white') {
            //$aplpcolor = 'white';
            $aplpcolor = 'white-outline';
        } elseif ($color=='silver') {
            $aplpcolor = 'white-outline';
        } else {
            $aplpcolor = 'black';
        }
        //types https://developer.apple.com/documentation/apple_pay_on_the_web/applepaybuttontype (buy or plain)
        $aplpret = <<< EOD
            if (!window.ApplePaySession) {
              console.error('This device does not support Apple Pay');
            } else {
                if (!ApplePaySession.canMakePayments()) {
                  console.error('This device is not capable of making Apple Pay payments');
                } else {
                    try {
                        if ($('.paypal-button-container').length>0) {
                            {$this->code}applepay = paypal.Applepay();

                            {$this->code}applepay.config()
                            .then(applepayConfig => {
                                {$this->code}applepayConfig = applepayConfig;
                                if (applepayConfig.isEligible) {//locale="en"

                                    window.{$this->code}AplpBillingDetails();
                                    $('.paypal-button-container .{$this->code}-applepay-container').each(function() {
                                        $(this).html('<apple-pay-button buttonstyle="{$aplpcolor}" type="plain" class="applePay" onclick="window.{$this->code}_callback_aplp()">');
                                    });

                                }
                            })
                            .catch(applepayConfigError => {
                              console.error('Error while fetching Apple Pay configuration.');
                            });
                        }
                    } catch ( e ) { console.log(e); }
                }
            }
EOD;

            \Yii::$app->getView()->registerJs($aplpinitJsLib, \common\components\View::POS_HEAD);
            \Yii::$app->getView()->registerJs($aplpret,\common\components\View::POS_LOAD);
        }

        $this->registerWalletProcessJs();
    }

/**
 * external JS SDK + css
 */
    protected function registerWalletAssets() {
        if ($this->isApplePayAllowed()) {
            \Yii::$app->getView()->registerJsFile("https://applepay.cdn-apple.com/jsapi/v1.1.0/apple-pay-sdk.js", ['position' => \common\components\View::POS_HEAD, 'async'=>true,  'crossorigin'=>true]);
            //\Yii::$app->getView()->registerJsFile('https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js');
            //display: none;
            $shape = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SHAPE') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_SHAPE : 'pill';
            if ($shape=='pill') {
                $css_br = 'border-radius: 50vh;';
            } else {
                $css_br = 'border-radius: 5px;';
            }
            $color = defined('MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_COLOR') ? MODULE_PAYMENT_PAYPAL_PARTNER_BUTTON_COLOR : 'gold';
            if ($color=='white') {
                $css_c = '  background-image: -webkit-named-image(apple-pay-logo-black);
                            background-color: white;';
            } elseif ($color=='silver') {
                $css_c = '  background-image: -webkit-named-image(apple-pay-logo-black);
                            background-color: white;
                            border: .5px solid black;';
            } else {
                $css_c = '-apple-pay-button-style: black;
                          background-image: -webkit-named-image(apple-pay-logo-black);';
            }
            //2do backward compatibility ios 10 (<15)
            \Yii::$app->getView()->registerCss('.' . $this->code . ' .apple-pay-button {
                height: 45px;
                ' . $css_br . '
                ' . $css_c . '
/*margin-left: auto;margin-right: auto;margin-top: 20px;
                background-image: -webkit-named-image(apple-pay-logo-white);
                background-position: 50% 50%;background-color: black;background-size: 60%;
                background-repeat: no-repeat;*/
                }
                @supports (-webkit-appearance: -apple-pay-button) {
                    .' . $this->code. '-applepay-container  {height: 61px;}
                }
                .' . $this->code. '-applepay-container, .' . $this->code . ' .applePay {width: 100%;}
                apple-pay-button {
                  --apple-pay-button-width: 100%;
                  --apple-pay-button-height: 44px;
                  --apple-pay-button-' . $css_br . '
                      margin-bottom:20px;
                }


                ');
        }
        if ($this->isGooglePayAllowed()) {
            \Yii::$app->getView()->registerJsFile("https://pay.google.com/gp/p/js/pay.js", ['position' => \common\components\View::POS_HEAD, 'onload' => 'onGooglePayLoaded()']);
            \Yii::$app->getView()->registerCss(
                '.' . $this->code. '-googlepay-container {width:100%}'
                );
        }
    }

    protected function walletComponents() {
        $ret = [];

        if ($this->isApplePayAllowed()) {
            $ret[] = 'applepay';
        }

        if ($this->isGooglePayAllowed()) {
            $ret[] = 'googlepay';
        }

        return $ret;
    }

    private function isApplePayAllowed() {
        return (defined('MODULE_PAYMENT_PAYPAL_PARTNER_APPLEPAY') && MODULE_PAYMENT_PAYPAL_PARTNER_APPLEPAY == 'True');
    }

    private function isGooglePayAllowed() {
        return (defined('MODULE_PAYMENT_PAYPAL_PARTNER_GOOGLEPAY') && MODULE_PAYMENT_PAYPAL_PARTNER_GOOGLEPAY == 'True');
    }

//2do orderpay - do not allow change address
    protected function pppApplePayPaymentRequest() {
        $ret = '';
        $this->isPartlyPaid(); //load instance if partly paid
        /** @var \common\classes\Order $order */
        if (!$this->manager->isInstance()) {
            $order = $this->manager->createOrderInstance('\common\classes\Order');
        } else {
            $order = $this->manager->getOrderInstance();
        }
        $this->manager->getShippingQuotesByChoice();

        $this->manager->checkoutOrderWithAddresses();
        $this->manager->totalProcess();

        $countryIso2 = $this->billing['country']['iso_code_2']; //from order??
        $currency = \Yii::$app->settings->get('currency');
        $currencies = \Yii::$container->get('currencies');
        $cur = $currencies->get_value($currency);
        if ($order->order_id > 0 || $order->info['orders_id']>0) {
            $total = $order->getDueAmount();
        } else {
            $total = (float)$order->info['total_inc_tax'];
        }
        $currency_value = $order->info['currency_value']??($cur??1);
        $total = $this->formatRaw($total, $currency, $currency_value);


        $appleMerchantName = $this->pppAppleMerchantName();
        //https://developer.apple.com/documentation/apple_pay_on_the_web/applepaypaymentrequest
        $data = [
            'countryCode' => $countryIso2,
            'currencyCode' => $currency,
            'total' => [
                //'ApplePayLineItemType' => ($this->onlySiteAddress?'final': 'pending'),// 'final'; no shipping selected
                'ApplePayLineItemType' => 'pending',// not 'final'; as no shipping selected //2check orderpay (final, no shipping options)
                'label' => $appleMerchantName,
                'amount' => $total
             ],
        ];

        //2do lineItems ??
        if ($order->order_id > 0 || $order->info['orders_id']>0) {
            $data['total']['ApplePayLineItemType'] = 'final';

        } elseif ($this->manager && $this->manager->getCustomerAssigned()) {
            $post = \Yii::$app->request->post();
            $this->manager->remove('estimate_ship');
            /** @var \common\classes\Order $order */
            if (!$this->manager->isInstance()) {
                $order = $this->manager->createOrderInstance('\common\classes\Order');
            } else {
                $order = $this->manager->getOrderInstance();
            }
            $this->manager->getShippingQuotesByChoice();
            $this->manager->checkoutOrderWithAddresses();
            $this->manager->totalProcess();
            $ppData = $this->ppBuildOrderDetails($order, $post);

            if (!empty($ppData['payer'])) {
                $contact = [
                  'phoneNumber' => $ppData['payer']['telephone']??'',
                  'emailAddress' => $ppData['payer']['email_address'],
                  'givenName' => $ppData['payer']['name']['given_name']??'',
                  'familyName' => $ppData['payer']['name']['surname']??'',
                  'addressLines' => [
                    $ppData['payer']['address']['address_line_1']??'',
                    $ppData['payer']['address']['address_line_2']??'',
                    ],
                  'locality' => $ppData['payer']['address']['admin_area_1'],
                  'administrativeArea' => $ppData['payer']['address']['admin_area_2'],
                  'postalCode' => $ppData['payer']['address']['postal_code'],
                  'countryCode' => $ppData['payer']['address']['country_code'],
                ];
                $data['billingContact'] = $contact;
            }
            if (!empty($ppData['purchase_units'][0]['shipping']['address'])) {
                $tmp = $ppData['purchase_units'][0]['shipping']['address'];
                $names = [];
                if (!empty($ppData['purchase_units'][0]['shipping']['name']['full_name'])) {
                    if (($ppData['payer']['name']['given_name']??'') . ' ' .
                        ($ppData['payer']['name']['familyName']??'') == $ppData['purchase_units'][0]['shipping']['name']['full_name']
                        ) {
                        $names = [$ppData['payer']['name']['given_name']??'', $ppData['payer']['name']['familyName']??''];
                    } else {
                        $names = explode(' ', $ppData['purchase_units'][0]['shipping']['name']['full_name'], 2);
                    }
                }
                $contact = [
                  'phoneNumber' => $ppData['payer']['telephone']??'',
                  'emailAddress' => $ppData['payer']['email_address']??'',
                  'givenName' => $names[0]??'',
                  'familyName' => $names[1]??'',
                  'addressLines' => [
                    $tmp['address_line_1'],
                    $tmp['address_line_2'],
                    ],
                  'locality' => $tmp['admin_area_1'],
                  'administrativeArea' => $tmp['admin_area_2'],
                  'postalCode' => $tmp['postal_code'],
                  'countryCode' => $tmp['country_code'],
                ];
                $data['shippingContact'] = $contact;
            }

        //shippingMethods []
            $currency_code = \Yii::$app->settings->get('currency');
            if (!empty($order->info['currency'])) {
                $currency_code = $order->info['currency'];
            }
            $options = [];
            if ($this->manager->isShippingNeeded()) {
                //$this->manager->prepareEstimateData();
                $_save = $this->sendExVat;
                if ((defined('DISPLAY_PRICE_WITH_TAX') && DISPLAY_PRICE_WITH_TAX=='true')) {
                    $this->sendExVat = false;
                }
                $options = $this->getShippingOptions($currencies, $currency_code);
                $this->sendExVat = $_save;

                if (!empty($options) ) {
                    $data['shippingMethods'] = [];
                    usort($options , function($a, $b) {
                        return $a['selected'] < $b['selected'];
                    });


                    foreach ($options as $option) {
                        $data['shippingMethods'][] = [
                              'identifier' => $option['id'],
                              'label' => $option['label'],
                              'detail' => '',
                              'amount' => $option['amount']['value'],
                        ];
                    }
                } elseif ($this->manager->isShippingNeeded()) {
                    //shipping is needed but not available
                    return []; 
                }
                
            } else {
                $data['shippingType'] = 'servicePickup'; //storePickup |shipping |delivery
            }
        }

        ///lineItems: [{label: subTotalDescr, amount: runningAmount }, {label: 'P&P', amount: runningPP }],

        return $data;

    }

    protected function pppAppleMerchantName() {
        $ret = substr(STORE_NAME, 0, 255);
        /*
        if (empty(self::$applePayMerchantNameKey) || !defined(self::$applePayMerchantNameKey)
            || (defined(self::$applePayMerchantNameKey) && empty(constant(self::$applePayMerchantNameKey)) )) {
            
        } else {
            $ret = constant(self::$applePayMerchantNameKey);
        }*/
        return $ret;
    }

    protected function pppApplePayPaymentRequestShipping() {
        $ret = '';
        $request = \Yii::$app->request->post();
        /** @var \common\classes\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');

        if ($this->debug) {
            \Yii::warning("pppApplePayPaymentRequestShipping \$request " . print_r($request, true), 'TLDEBUG');
        }
        
        if (!$this->manager->isInstance()) {
            if (!$this->isPartlyPaid()) {
                $order = $this->manager->createOrderInstance('\common\classes\Order');
            }
        }
        if ($this->manager->isInstance() && !empty($request['shipping_address']['countryCode'])) {
                $currency_code = \Yii::$app->settings->get('currency');
                if (!empty($order->info['currency'])) {
                    $currency_code = $order->info['currency'];
                }

                $estimateShippingChanged = true;
                if (!empty($request['shipping_address'])) {

/*
 'locality': 'Cupertino',
'country': 'United States',
'postalCode': '95014-2083',
'administrativeArea': 'CA',
'emailAddress': 'ravipatel@example.com',
'familyName': 'Patel',
'addressLines': [
'1 Infinite Loop'
],
'givenName': 'Ravi',
'countryCode': 'US',
'phoneNumber': '(408) 555-5555'
 */
                    $country = \common\helpers\Country::get_country_info_by_iso($request['shipping_address']['countryCode']);
                    $zone_id = \common\helpers\Zones::get_zone_id($country['id'], $request['shipping_address']['administrativeArea']);
                    /*
                    if ($this->manager->has('estimate_ship')) {
                        $old = $this->manager->get('estimate_ship');
                        //2check - old is empty
                    }

                    if (!empty($old) &&
                        !empty($old['country_id']) && isset($old['postcode']) && isset($old['zone']) &&
                        $old['country_id'] == $country['id'] &&
                        $old['postcode'] == $request['shipping_address']['postalCode'] &&
                        $old['zone'] == $request['shipping_address']['state']
                    ) {
                        $estimateShippingChanged = false;
                    }*/
                    $ab = [
                        'country_id' => $country['id'],
                        'postcode' => $request['shipping_address']['postalCode'],
                        'zone' => $zone_id,
                        'state' => $request['shipping_address']['administrativeArea'],
                        'city' => $request['shipping_address']['locality'],
                        'email_address' => $request['shipping_address']['emailAddress']??'',
                        'telephone' => preg_replace('/[^0-9]+/', '', $request['shipping_address']['phoneNumber']??''),
                        'firstname ' => $request['shipping_address']['givenName'],
                        'lastname ' => $request['shipping_address']['familyName'],
                    ];
                    if (!empty($request['shipping_address']['addressLines']) && is_array($request['shipping_address']['addressLines'])) {
                        if (count($request['shipping_address']['addressLines'])>2) {
                            $cnt = round(count($request['shipping_address']['addressLines'])/2);
                        } else {
                            $cnt = 1;
                        }
                        foreach ($request['shipping_address']['addressLines'] as $line) {
                            if ($cnt > 0) {
                                $ab['street_address'] .= ' ' . $line;
                                $cnt --;
                            } else {
                                $ab['suburb'] .= ' ' . $line;
                            }
                        }
                        trim($ab['suburb']);
                        trim($ab['street_address']);
                    }

                    $this->manager->set('estimate_ship', $ab);
                    $this->manager->set('estimate_bill', $ab);
                } else {
                    $estimateShippingChanged = false;
                }

                if ($this->manager->isShippingNeeded()) {
                    $this->manager->resetDeliveryAddress();
                }
                if (\Yii::$app->user->isGuest) {
                    $this->manager->resetBillingAddress();
                }
                $this->manager->getShippingQuotesByChoice();
                $this->manager->checkoutOrderWithAddresses();

                $this->manager->totalProcess();

                $order = $this->manager->getOrderInstance();
                $appleMerchantName = $this->pppAppleMerchantName();
                $resp = [
                            'status' => '', //ApplePaySession.STATUS_SUCCESS in JS
                            //'newShippingMethods' => [],
                            'newTotal' => [
                              'ApplePayLineItemType' => 'pending',// 'final'; no shipping selected
                              'label' => $appleMerchantName,
                              'amount' => $this->formatRaw($order->info['total_inc_tax']),
                            ]
                ];


                $options = [];
                if ($estimateShippingChanged && $this->manager->isShippingNeeded()) {
                    //$this->manager->prepareEstimateData();
                    $options = $this->getShippingOptions($currencies, $currency_code);

                    if (!empty($options) && $estimateShippingChanged) {
                        foreach ($options as $option) {
                            $resp['newShippingMethods'][] = [
                                  'identifier' => $option['id'],
                                  'label' => $option['label'],
                                  'detail' => '',
                                  'amount' => $option['amount']['value'],
                            ];
                        }
                    } elseif ($this->manager->isShippingNeeded()) {
                        //shipping is needed but not available
                        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                        return []; // in JS STATUS_FAILURE
                    }
                }
                if ($this->debug ) {
                    \Yii::warning("patchOrder resp " . print_r($resp, true), 'TLDEBUG');
                }
        }
        return $resp;
    }

    protected function pppApplePayPaymentRequestShippingCost() {
        $resp = [];
        $request = \Yii::$app->request->post();
        /** @var \common\classes\Currencies $currencies */
        $currencies = \Yii::$container->get('currencies');

        if ($this->debug) {
            \Yii::warning("pppApplePayPaymentRequestShippingCost \$request " . print_r($request, true), 'TLDEBUG');
        }

        if (!$this->manager->isInstance()) {
            if (!$this->isPartlyPaid()) {
                $order = $this->manager->createOrderInstance('\common\classes\Order');
            }
        }

        if ($this->manager->isInstance() && !empty($request['selected_shipping_option']['identifier'])) {
            $currency_code = \Yii::$app->settings->get('currency');
            if (!empty($order->info['currency'])) {
                $currency_code = $order->info['currency'];
            }

            if (!empty($request['selected_shipping_option']['identifier'])) {
                $shipping = tep_db_input(tep_db_prepare_input($request['selected_shipping_option']['identifier']));
                if ($shipping) {
                    $this->manager->setSelectedShipping($shipping);
                }
                $this->manager->checkoutOrder();

                $_shipping = $this->manager->getShipping();
                if ($_shipping) {
                    $module = $this->manager->getShippingCollection()->get($_shipping['module']);
                    $this->manager->remove('shippingparam');
                }
            }

            $this->manager->getShippingQuotesByChoice();
            $this->manager->checkoutOrderWithAddresses();

            $this->manager->totalProcess();

            $order = $this->manager->getOrderInstance();
            $appleMerchantName = $this->pppAppleMerchantName();
            $resp = [
                        'status' => '', //ApplePaySession.STATUS_SUCCESS in JS
                        //'newShippingMethods' => [],
                        'newTotal' => [
                          'ApplePayLineItemType' => 'final',
                          'label' => $appleMerchantName,
                          'amount' => $this->formatRaw($order->info['total_inc_tax']),
                        ]
            ];

        }
        return $resp;
    }
    
    protected function toPPAddress($aplp) {
        $_suburb = $_street_address = '';
        if (!empty($aplp['addressLines']) && is_array($aplp['addressLines'])) {
            if (count($aplp['addressLines'])>2) {
                $cnt = round(count($aplp['addressLines'])/2);
            } else {
                $cnt = 1;
            }
            foreach ($aplp['addressLines'] as $line) {
                if ($cnt > 0) {
                    $_street_address .= ' ' . $line;
                    $cnt --;
                } else {
                    $_suburb .= ' ' . $line;
                }
            }
        }
        return [
            'firstname' => $aplp['givenName']??'',
            'lastname' => $aplp['familyName']??'',
            'postal_code' => $aplp['postalCode']??'',
            'address_line_1' => trim($_street_address),
            'address_line_2' => trim($_suburb),
            'admin_area_2' => $aplp['locality']??'',
            'state' => $aplp['administrativeArea']??'',
            'country_code' => $aplp['countryCode']??'',
        ];
    }

}