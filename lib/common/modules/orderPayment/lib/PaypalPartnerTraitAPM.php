<?php
namespace common\modules\orderPayment\lib;

trait PaypalPartnerTraitAPM {

/**
 * show agreement near standard checkout button
 */
    public function apmsWithAgreement() {
        $ret = [];
        if ($this->billing['country']['countries_iso_code_2'] == 'DE') {
            $ret[] = 'pui';
        }
        return $ret;
    }

/**
 * show standard checkout button (add callback)
 */
    public function apmsWithoutOwnButton() {

        $ret = ['giropay', 'sofort', 'bancontact', 'blik', 'p24', 'eps', 'ideal', 'mybank'];

        if (!empty($this->getAPMTemplate('card'))) {
            $ret[] = 'card';
        }
        if ($this->billing['country']['countries_iso_code_2'] == 'DE') {
            $ret[] = 'pui';
        }
        
        return $ret;
    }
    public function getAPMTemplates() {
        $methods = $paypalMethods = [];
        $fundings = $this->getFundings();

        if (!empty($fundings['enabled'])) {
            $paypalMethods = array_map('trim', explode(',', $fundings['enabled']));
        }

        if (empty($paypalMethods)) {
            $paypalMethods = array_keys(self::$possibleFundings);
        }

        foreach ($paypalMethods as $method) {
            $methods[$method] = $this->getAPMTemplate($method);
        }
        return $methods;
    }

    public function getAPMJSCallbacks() {
        $methods = $paypalMethods = [];
        $fundings = $this->getFundings();

        if (!empty($fundings['enabled'])) {
            $paypalMethods = array_map('trim', explode(',', $fundings['enabled']));
        }

        if (empty($paypalMethods)) {
            $paypalMethods = array_keys(self::$possibleFundings);
        }

        foreach ($paypalMethods as $method) {
            $methods[$method] = $this->getAPMJSCallback($method);
        }
        return $methods;
    }
    
    public function getAPMTemplate($option) {
        $ret = '';
        $currency = \Yii::$app->settings->get('currency');
        switch ($option) {
            case 'card':
                $platformId = $this->getPlatformId();
                $seller = $this->getSeller($platformId);
                if (!empty($seller->paypal_partner_ccp_status)) {

                    $ret = '
<style>
    .ppp_card_container .card_field {height:2em; background-color:white; border:solid #c8c8c8 1px; padding:0.5em 1em;}
</style>
<div class="ppp_card_container">
    <div id="ppp-card-form" class="ppp-cc-field">
        <label for="ppp-card-number">' . PAYPAL_PARTNER_TEXT_CC_NUMBER . '</label>
        <div id="ppp-card-number" class="card_field"></div>
    </div>
    <div class="ppp-cc-field">
        <label for="ppp-expiration-date">' . PAYPAL_PARTNER_TEXT_EXP . '</label>
        <div id="ppp-expiration-date" class="card_field"></div>
    </div>
    <div class="ppp-cc-field">
        <label for="ppp-cvv">' . PAYPAL_PARTNER_TEXT_CVV . '</label>
        <div id="ppp-cvv" class="card_field"></div>
    </div>
</div>
                    ';
                }
            break;

            case 'pui':
                $ret = '';
                if ($currency =='EUR' && $this->billing['country']['countries_iso_code_2'] == 'DE') {

                    $showPhone = $showBirthDay = true;
                    if (in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])) {
                        $showPhone = false;
                    }
                    if ($this->manager->isCustomerAssigned()) {
                        $cust = $this->manager->getCustomersIdentity();
                        if ($cust && substr($cust->customers_dob, 0, 4) > 1900) {
                            $showBirthDay = false;
                        }
                    }

                    $platformId = $this->getPlatformId();
                    if ($showPhone || $showBirthDay ) {
                        $ret = '
<style>
    .ppp_pui .pui_field input {position: initial;}
    .inline-block.paypal-marks-icon.paypal-marks-icon-pui {display:none}
    #ppp-pui-bd:after{content: \'\';display: block;clear: both;}
    #ppp-pui-bd-container {position: absolute; z-index: 100;background:#fff; }
    #ppp-pui-bd-container > div {border: 1px solid #999;}
</style>
<div class="ppp_pui">';
                        if ($showBirthDay) {
                            $ret .= '
    <div class="ppp-pui">
        <label for="ppp-pui-bd">' . ENTRY_DATE_OF_BIRTH . '</label>
        <div id="ppp-pui-bd" class="pui_field" style="position: relative;">' . \common\helpers\Html::textInput('paypal_birthday', '', ['placeholder' => date(DATE_FORMAT_DATEPICKER_PHP), 'class' => 'form-control datepicker pay-later-birthday']) . '</div>
            <div id="ppp-pui-bd-container"></div>
    </div>';
                        }
                        if ($showPhone) {
                            $ret .= '
    <div class="ppp-pui">
        <label for="ppp-pui-phone">' . ENTRY_TELEPHONE_ADRESS_BOOK . '</label>
        <div id="ppp-pui-phone" class="pui_field">' . \common\helpers\Html::textInput('paypal_telephone', '', ['class' => 'form-control', 'minlength' => 5]) . '</div>
    </div>';
                        }
                        $ret .= '
</div>
                        ';
                    }
                }
            break;
            case '_ideal':
                $ret .= '
                    <div class="ppp-ideal" id="ppp-ideal"></div>
                        ';
            break;
        }
        return $ret;
    }

    public function getAPMJSCallback($option) {
        $ret = '';
        //$createUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'createOrder', 'option' => $option]);
        $getCCOptionsUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'customerDetails', 'option' => $option]);
        $retrieveUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'retrieveOrder', 'partlypaid' => $this->isPartlyPaid()]);
        $generalError = str_replace(["'", "\n"], ["\\'", '<br>'], defined('PAYPAL_PARTNER_TEXT_ERROR_CAPTURE')?PAYPAL_PARTNER_TEXT_ERROR_CAPTURE:'Payment could not be captured.');
        $generalErrorModule = str_replace(["'", "\n"], ["\\'", '<br>'], defined('MODULE_PAYMENT_PAYPAL_PARTNER_GENERAL_ERROR') ? MODULE_PAYMENT_PAYPAL_PARTNER_GENERAL_ERROR : "Can't create order");
        switch ($option) {
            case 'card':
                $threeDS = 1;//true - as in JS
                $platformId = $this->getPlatformId();
                $seller = $this->getSeller($platformId);
                if (!empty($seller->three_ds_settings)) {
                    $settings = json_decode($seller->three_ds_settings, true);
                }
                if (empty($settings['status'])) {
                    $threeDS = 0;
                }
                $ret =
        "window.{$this->code}_callback_{$option} = function () {
            ///get billing details
            var bodyData = '';
            if (paymentCollection.form) {
                bodyData = $(paymentCollection.form).serialize();
            }
            $.ajax({
                url: '{$getCCOptionsUrl}',
                data: bodyData,
                async: false,
                type: 'POST',
                dataType: 'json',
                success: function(data){

                    paymentCollection.pppCardFields.submit(data)
                    .then(function (payload) {
//https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/
//https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/test/
                        /** sample payload
                        {
                        liabilityShifted: false, liabilityShift: \"NO\", authenticationStatus: \"ERROR\", authenticationReason: \"ERROR\", card: undefined, orderId: \"7GV78070L0698402K\"
                        }
                        */
    console.log(payload);
                        // Needed only when 3D Secure contingency applied
                        if (payload.liabilityShift === 'POSSIBLE') {
                             // 3D Secure passed successfully
                             return payload;
                        }
                        if (payload.liabilityShifted) { //payload.liabilityShift in docs
                             // Handle buyer confirmed 3D Secure successfully
                             return payload;
                        }
                        if ({$threeDS} == 0 && payload.authenticationStatus == 'APPROVED'){
                             return payload;
                        }
                        /**/
                        //pppOrderId = payload.orderId;
                        return false;
                    })
                    .then((payload) => {
                        if (payload && payload.orderId){
                            pppOrderId = payload.orderId;
                            $('body').append('<div class=\"popup-box-wrap\"><div class=\"around-pop-up\"></div><div class=\"preloader\"></div></div>');
                            return fetch('{$retrieveUrl}&id=' + pppOrderId, {
                                method: 'POST'
                            }).then(function(res) {
                                return res.json();
                            }).then(function(res) {
                                if (!res.ok) {
                                    $('.popup-box-wrap').remove();
                                    alertMessage(res.error);
                                } else {
                                    window.location.href = res.url;
                                }
                            });
                        } else {
                            alertMessage('$generalError');
                        }
                    })
                    .catch((err) => {
                        $('.popup-box-wrap').remove();
                        var msg = '';
                        try {
                            msg = err.details[0].description;
                        } catch ( e ) { }
                        if (msg == '') {
                            msg = JSON.stringify(err);
                            if (msg == '{}') msg = '';
                        }
                        alertMessage('$generalError ' + msg);
                    });

                }
            });
        }
";
          break;
            case 'pui':
                if ($this->billing['country']['countries_iso_code_2'] == 'DE') {
                    $createUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'createOrder', 'option' => $option]);
                    $ret = "
        window.{$this->code}_callback_{$option} = function () {
            var bodyData = '';
            if (paymentCollection.form) {
                bodyData = $(paymentCollection.form).serialize();
            }
            $.ajax({
                url: '{$createUrl}',
                data: bodyData,
                async: false,
                dataType: 'json',
                type: 'POST',
                success: function(data){
                    pppOrderId = '';
                    try {
                        if (data.hasOwnProperty('error') && data.error ) {
                            if (data.hasOwnProperty('message') ) {
                                alertMessage(data.message);
                            } else {
                                alertMessage('$generalErrorModule');
                            }
                        } else {
                            pppOrderId = data.id;
                        }
                    } catch ( e ) {
                      console.log(e);
                      return false
                    }
                    if (pppOrderId != false) {
                        $('body').append('<div class=\"popup-box-wrap\"><div class=\"around-pop-up\"></div><div class=\"preloader\"></div></div>');
                        return fetch('{$retrieveUrl}&id=' + pppOrderId, {
                            method: 'POST'
                        }).then(function(res) {
                            return res.json();
                        }).then(function(res) {
                            if (!res.ok) {
                                $('.popup-box-wrap').remove();
                                alertMessage(res.error);
                            } else {
                                window.location.href = res.url;
                            }
                        });
                    }
                }
            })
        }
";
                }
          break;
            case 'giropay':
            case 'bancontact':
            case 'blik':
            case 'p24':
            case 'eps':
            case 'mybank':
            case 'sofort':
            case 'ideal':
                if ($this->validAPMCountry($option, $this->billing['country']['countries_iso_code_2'])) {
                    $createUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'createOrder', 'option' => $option]);
                    $ret = "
        window.{$this->code}_callback_{$option} = function () {
            var bodyData = '';
            if (paymentCollection.form) {
                bodyData = $(paymentCollection.form).serialize();
            }
            $.ajax({
                url: '{$createUrl}',
                data: bodyData,
                async: false,
                dataType: 'json',
                type: 'POST',
                success: function(data){
                    pppOrderId = '';
                    try {
                        if (data.hasOwnProperty('error') && data.error ) {
                            if (data.hasOwnProperty('message') ) {
                                alertMessage(data.message);
                            } else {
                                alertMessage('$generalErrorModule');
                            }
                        } else {
                            pppOrderId = data.id;
                            window.location.href = data.url;
                        }
                    } catch ( e ) {
                      console.log(e);
                      return false
                    }
                }
            })
        }
";
                }
          break;

        }
        return $ret;
    }

/**
 * paranoic - could be return true; (PayPal should return error if APM is not available for country/currency)
 * need to be updated if APM became available for new country
 * @param string $option APM
 * @param string $code country ISO 2 code
 * @return bool
 */
    protected function validAPMCountry($option, $code){
        $ret = false;
        switch ($option) {
            case 'bancontact':
                $ret = in_array($code, ['BE']);
                break;
            case 'blik':
            case 'p24':
                $ret = in_array($code, ['PL']);
                break;
            case 'eps':
                $ret = in_array($code, ['AT']);
                break;
            case 'giropay':
                $ret = in_array($code, ['DE']);
                break;
            case 'ideal':
                $ret = in_array($code, ['NL']);
                break;
            case 'mybank':
                $ret = in_array($code, ['IT']);
                break;
            case 'sofort':
                $ret = in_array($code, ['AT', 'BE', 'DE', 'SP', 'IT', 'NL', 'GB']);
                break;
        }
        return $ret;
    }
    
    public function getAPMJS($option) {
        $ret = '';
        $createUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'createOrder', 'option' => $option]);
        $getCreateUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'createOrder', 'option' => $option, 'get' => 1, 'partlypaid' => $this->isPartlyPaid()]);
        $retrieveUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'retrieveOrder', 'partlypaid' => $this->isPartlyPaid()]);
        $shippingUrl = \Yii::$app->urlManager->createAbsoluteUrl(["callback/webhooks.payment.{$this->code}", 'action' => 'patchOrder']);
        $customersDetails = '';
        if (!$this->manager->getCustomerAssigned()) {
            //2do 'frmCheckoutConfirm' : 'frmCheckout'
            $customersDetails = ",{
                                method: 'POST',
                                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                                body: $('#frmCheckout').serialize()
                            }";
        }
        
        switch ($option) {
            case 'pui': //upon invoice
                if ($this->billing['country']['countries_iso_code_2'] == 'DE') {
                    $bsdpjs = \frontend\design\Info::themeFile('/js/bootstrap-datepicker.js');
                    $ret = "
    window.paypal_render_subfields_{$option} = function () {
        try {
            var pppPUIbdcnt = 0; pppPUIbd = window.setInterval(function() {
                if ($('.pay-later-birthday:visible').length==0 || typeof($.fn.datepicker.version)=='undefined') {
                    pppPUIbdcnt++;
                    if (pppPUIbdcnt>5) { //seems jquery datepicker only - load bootstrap
                        (function(d, s, id){
                        var js, ref = d.getElementsByTagName(s)[0]; if (!d.getElementById(id)){
                        js = d.createElement(s); js.id = id; js.async = true;
                        js.src = '" . $bsdpjs . "';
                        ref.parentNode.insertBefore(js, ref); }
                        }(document, 'script', 'bsdpjs'));
                    }
                    if (pppPUIbdcnt>50){ //sorry cant load bootstrap datepicker
                        window.clearInterval(pppPUIbd);
                    }
                    return false;
                }
                window.clearInterval(pppPUIbd);
                
                $('.pay-later-birthday:visible').not('.dp-inited').datepicker({
                    startView: 3,
                    format: '" . DATE_FORMAT_DATEPICKER . "yy',
                    language: 'current',
                    autoclose: true,
                    container:$('#ppp-pui-bd-container')
                });
                $('.pay-later-birthday:visible').addClass('dp-inited');
            }, 500);
        } catch ( e ) { console.log(e); }
    }
    ";
                }
                break;
            case 'card':
                $platformId = $this->getPlatformId();
                $seller = $this->getSeller($platformId);
                if (!empty($seller->paypal_partner_ccp_status)) {
                    $ret = "
window.paypal_render_subfields_{$option} = function () {
    if (paypal.HostedFields.isEligible()) {
         let pppOrderId;
        // Renders card fields
        //paymentCollection.hasOwnProperty('pppCardFields') is incorrect after payment block reload
        if (paymentCollection && (!paymentCollection.hasOwnProperty('pppCardFields') || $('#ppp-card-number').children().length == 0) ) {
            paypal.HostedFields.render({
              createOrder: () => {
                return fetch('{$createUrl}' {$customersDetails}
                ).then((res) => res.json())
                .then((orderData) => {
                  pppOrderId = orderData.id; // needed later to complete capture
                  return orderData.id
                })
              },
              styles: {
                '.valid': {
                  color: 'green'
                },
                '.invalid': {
                  color: 'red'
                }
              },
              fields: {number: {selector: '#ppp-card-number', placeholder: '4111 1111 1111 1111'},
                cvv: {selector: '#ppp-cvv', placeholder: '123' },
                expirationDate: {selector: '#ppp-expiration-date', placeholder: '" . PAYPAL_PARTNER_TEXT_EXP_PLACEHOLDER . "' }
              }
            }).then((cardFields) => {
              //if (!paymentCollection.hasOwnProperty('pppCardFields')) {
                  paymentCollection.pppCardFields = cardFields;
              //}

            });
        }

    } else {
        $('.item-radio.item-payment.{$this->code}_card').hide();
    }
}
";
                }
          break;
        }
        return $ret;
    }

    public function setAPMSetting($post) {
        $payment = $post['payment'];
        if (in_array($payment, ['paypal_partner_pui'])) {
            $this->requireItemVat = true;
        }

    }

    public function addAPMDetails(&$request, $order, $post) {
        $payment = $post['payment'];
        //pay upon invoice available in DE and requires EN|DE-DE locale
        $locale = str_replace('_', '-', \Yii::$app->settings->get('locale'));
        $country_code = false; //it could be in payer - if customer is logged in?? or in shipping if guest
        if (!empty($request->body['payer']['address']['country_code'])) {
            $country_code = $request->body['payer']['address']['country_code'];
        } elseif (!empty($request->body['purchase_units'][0]['shipping']['address']['country_code'])) {
            $country_code = $request->body['purchase_units'][0]['shipping']['address']['country_code'];
        }

        if ($country_code == 'DE' && substr($locale, -2) != 'DE') {
            $locale = substr($locale , 0, -2) . 'DE';
        }
        
        $fullname = '';
        if (!empty($request->body['payer']['name'])) {
            $fullname = ($request->body['payer']['name']['given_name']??null) . ' ' . ($request->body['payer']['name']['surname']??null);
        } elseif (!empty($request->body['purchase_units'][0]['shipping']['name']['full_name'])) {
            $fullname = $request->body['purchase_units'][0]['shipping']['name']['full_name'];
        }
        $email_address = '';
        if (!empty($request->body['payer']['email_address'])) {
            $email_address = $request->body['payer']['email_address'];
        }
        switch ($payment) {
            case 'paypal_partner_pui':
                if ($request->body['intent']=='CAPTURE' &&  $country_code == 'DE') {
                    $dob = $telephone = '';
                    $prefix = preg_replace('/\D/', '', $this->billing['country']['dialling_prefix']);
                    if (!empty($post['checkout']['telephone'])) {
                        $telephone = $post['checkout']['telephone'];

                    } elseif (!empty($post['paypal_telephone'])) {
                        $telephone = $post['paypal_telephone'];
                    }
                    $telephone = preg_replace('/\D/', '', $telephone);
                    if ($prefix == substr($telephone, 0, strlen($prefix))){
                        $telephone = substr($telephone, strlen($prefix));
                    }

                    if (!empty($post['paypal_birthday'])) {
                        $dob = \common\helpers\Date::prepareInputDate($post['paypal_birthday']);

                    } elseif ($this->manager->isCustomerAssigned()) {
                        $cust = $this->manager->getCustomersIdentity();
                        if ($cust && substr($cust->customers_dob, 0, 4)>1900) {
                            $dob = substr($cust->customers_dob, 0, 10);
                        }
                    }
       
                    $payment_source =  [
                                "pay_upon_invoice" =>
                                [
                                    'name' => $request->body['payer']['name']??null,
                                    'email' => $request->body['payer']['email_address']??$email_address,
                                    'billing_address' => $request->body['payer']['address']??null,
                                    'birth_date' => $dob,
                                    'phone' => [
                                      'national_number' => $telephone,
                                      'country_code' => $prefix
                                    ],
                                    'experience_context' => [
                                      'locale' => $locale,
                                      'customer_service_instructions' => [TEXT_INVOICE_SOMETHING_WRONG . ' ' . STORE_OWNER_EMAIL_ADDRESS]
                                    ]

                                ]

                    ];
                    $request->body['payment_source'] = $payment_source;
                    $request->body['processing_instruction'] = "ORDER_COMPLETE_ON_PAYMENT_APPROVAL";
                }
            break;
            case 'paypal_partner_giropay':
            case 'paypal_partner_bancontact':
            case 'paypal_partner_blik':
            case 'paypal_partner_p24':
            case 'paypal_partner_eps':
            case 'paypal_partner_mybank':
            case 'paypal_partner_sofort':
            case 'paypal_partner_ideal':
                $_option = str_replace('paypal_partner_', '', $payment);
                if ($request->body['intent']=='CAPTURE') {
                    $payment_source =  [
                                "$_option" =>
                                [
                                    'name' => ($request->body['payer']['name']['given_name']??null) . ' ' . ($request->body['payer']['name']['surname']??null),
                                    'country_code' => $country_code
                                ]

                    ];
                    if (in_array($_option, ['blik', 'sofort', 'p24'])){
                        $payment_source[$_option]['email'] = $request->body['payer']['email_address'];
                    }
                    /*if (in_array($_option, ['ideal'])){
                        $payment_source[$_option]['bic'] = $post['paypal_partner_bic'];
                    }*/
                    $request->body['payment_source'] = $payment_source;
                    $request->body['processing_instruction'] = "ORDER_COMPLETE_ON_PAYMENT_APPROVAL";
                    
                    $request->body['application_context']['return_url'] = tep_href_link("callback/webhooks.payment.{$this->code}", 'method='. $_option . '&action=returnAPM', 'SSL', true, false);
                    $request->body['application_context']['cancel_url'] = tep_href_link("callback/webhooks.payment.{$this->code}", 'method=' . $_option . '&action=cancelAPM', 'SSL', true, false);

                    $this->redirectMethodsBeforeLeave($request);
                }
            break;
        }


    }

/**
 * process return from APM
 * @param string $method
 */
    public function returnAPM($method) {
        if ($this->saveOrderBefore() == 'Order' ) {
            if ($this->manager && $this->manager->has('ppp_order_before')) {
                $orders_id = $this->manager->get('ppp_order_before');
            }
        } else {
            //tmp order - convert to real one, webhook will do the same :(
            // use child_id as a lock flag (-1)
            if ($this->manager && $this->manager->has('ppp_tmp_order')) {
                $tmp_order_id = substr($this->manager->get('ppp_tmp_order'), 3);//'tmp' . $ret;
                if ($this->lockTmpOrder($tmp_order_id)) {
                    $tmpOrder = $this->manager->getParentToInstanceWithId('\common\classes\TmpOrder', $tmp_order_id);
                    /// not verified !!! $tmpOrder->info['order_status'] = $this->paidOrderStatus();
                    $orders_id = $tmpOrder->createOrder();
                    $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);

                    $this->no_process($order);

                    //$order->notify_customer($order->getProductsHtmlForEmail(),[]);
                    
                    $this->no_process_after($order);

                } else {
                    sleep(2);
                    $orders_id = \common\models\TmpOrders::find()->where(['orders_id' => $tmp_order_id, 'child_id' => 0])
                        ->select('child_id')
                        ->scalar();
                }
            } 
        }
        if ($orders_id) {
            $url = \Yii::$app->urlManager->createAbsoluteUrl(['checkout/success', 'order_id' => orders_id]);
            
        } else {
            //redirect to account history page with a message
            $messageStack = \Yii::$container->get('message_stack');
            $messageStack->add_session(SESSION_EXPIRED_LOGIN_OR_CHECK_EMAIL, 'account_password', 'warning');
            $url = \Yii::$app->urlManager->createAbsoluteUrl(['account']);
            
        }
        tep_redirect($url);
    }
    
    public function cancelAPM($method) {
        if ($this->saveOrderBefore() == 'Order' && $this->manager && $this->manager->has('ppp_order_before')) {
            //cancel real order if it's created according settings.
            $orders_id = $this->manager->get('ppp_order_before');
            $order = $this->manager->getOrderInstanceWithId('\common\classes\Order', $orders_id);
            $order_status = $this->refundOrderStatus();
            if (!$this->isPartlyPaid() && (int)$order_status>0 && $order_status != $order->info['order_status']) {
                \common\helpers\Order::setStatus($order->order_id, (int)$order_status, [
                    'comments' => TEXT_CANCELLED_BY_CUSTOMER,
                    'customer_notified' => 0,
                ]);
            }
        }
        tep_redirect($this->getCheckoutUrl([], self::PAYMENT_PAGE));
    }

/**
 * save tmp/live order and set its ID as invoice id in the create order request (to PayPal)
 * @param type $request
 */
    public function redirectMethodsBeforeLeave(&$request) {
        try {
            $invoiceId = $this->checkSaveTmpOrder();
        } catch (\Exception $e) {
            \Yii::warning(" #### " .print_r($e->getMessage() . ' ' . $e->getTraceAsString(), true), 'exception_' . $this->code);
        }
        $request->body['purchase_units'][0]['invoice_id'] = $invoiceId;

    }
    
    public function validateAPMCreateOrderResponse($method, $pRes) {
        $response['id'] = $pRes->result->id;
        $response['option'] = $method;
        switch ($method) {
            case 'giropay':
            case 'sofort':
            case 'bancontact':
            case 'blik':
            case 'p24':
            case 'eps':
            case 'ideal':
            case 'mybank':
                if ($pRes->result->status == 'PAYER_ACTION_REQUIRED' && !empty($pRes->result->payment_source->$method)) {
                    $url = false;
                    if (!empty($pRes->result->links) && is_array($pRes->result->links)) {
                        foreach($pRes->result->links as $link) {
                            if ($link->rel == 'payer-action') {
                                $url = $link->href;
                                break;
                            }
                        }
                    }
                    if ($url) {
                        $response['url'] = $url;
                    } else {
                        $response = ['error' => 1, 'message' => TEXT_UNEXPECTED_PAYMENT_ERROR . ' ' . '(NO_REDIRECT_URL)'];
                        \Yii::warning("NO_REDIRECT_URL #### " .print_r($pRes, true), 'TLDEBUG' . $this->code);
                    }
                } else {
                    $response = ['error' => 1, 'message' => TEXT_UNEXPECTED_PAYMENT_ERROR . ' ' . '(UNEXPECTED_STATUS)'];
                    \Yii::warning("UNEXPECTED_STATUS #### " .print_r($pRes, true), 'TLDEBUG' . $this->code);
                }
                break;
            case 'card':
            case 'pui':
                break;
            default:
                $response = ['error' => 1, 'message' => TEXT_UNEXPECTED_PAYMENT_ERROR . ' ' . '(UNEXPECTED_METHOD)'];
                \Yii::warning("UNEXPECTED_METHOD #### " .print_r($pRes, true), 'TLDEBUG' . $this->code);
                break;
        }
        return $response;
        
    }


    public static function getFundingTitle($option) {
        $data = self::$possibleFundings[$option];
        return (defined($data['translation_key'])? constant($data['translation_key']):$data['default_translation']);
    }

    public static function possibleFundingArray() {
        $ret = ['all' => '<span class="paypal-partner-pm">' . (defined('TEXT_ALL')?TEXT_ALL:'All') . '</span>'];

        $platformISO2 = ''; 
        $country_id = 0;
        $platform_id = self::getPlatformId();
        $country_id = \common\helpers\PlatformConfig::getStoreCountry($platform_id);
        if (!empty($platform_id)) {
            $address = \common\helpers\PlatformConfig::getDefaultAddress($platform_id);
            $country_id = $address['country_id']??0;
        }
        
        if ($country_id > 0) {
            $cinfo = \common\helpers\Country::get_country_info_by_id($country_id);
            $platformISO2 = strtolower($cinfo['countries_iso_code_2']);
        }
        foreach (self::$possibleFundings as $k => $data) {
            $countries = $classes = '';
            if ($data['apm']) {
                $classes .= ' apm';
            }
            if (!empty($data['countries']) && is_array($data['countries'])) {
                $applicable = false;
                foreach ($data['countries'] as $country) {
                    $country = strtolower($country);
                    if ($platformISO2 == $country) {
                        $applicable = true;
                    }
                    $classes .= ' ' . $country;
                    $countries .= '<span class="' . $country . '"></span>';
                }
                if ($applicable) {
                    $classes .= ' applicable';
                } else {
                    $classes .= ' not-applicable';
                }
            }

            $ret[$k] = '<span class="paypal-partner-pm' . $classes . ' flag">' . $countries .
                self::getFundingTitle($k) .
                '</span>';
        }
        return $ret;
    }
}
