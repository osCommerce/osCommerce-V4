<style>
    .ppp-boarding-widget-wait { opacity: 0.3; cursor: wait; color:white;}
    .ppp-boarding-widget { padding:20px}
</style>

<br>
        <div class="widget-content after ppp-boarding-widget ppp-boarding-widget-wait">
            {$callback='data-paypal-onboard-complete="onboardedCallback"'}
            <div dir="ltr" style="text-align: left;" >
                <div class="widget-content after ">
                {$smarty.const.TEXT_PAYPAL_PARTNER_CONTINUE_PAYPAL}
                </div>
                <br>
  
            <script>
                function onboardedCallback(authCode, sharedId) {
                  fetch('{$fetchKeysUrl}', {
                    method: 'POST',
                    headers: {
                      'content-type': 'application/json'
                    },
                    body: JSON.stringify({
                      authCode: authCode,
                      sharedId: sharedId
                    })

                  }).then(function(res) {
                    if (!res.ok) {
                      alert("Something went wrong!");
                    }
                  });
                }

              </script>
              <div class="paypal-button-holder" style="text-align:center"><span>
<a {$callback} href="https://www.{if $mode != 'Live' }sandbox.{/if}paypal.com/bizsignup/partner/entry?partnerClientId={$link_params.partnerClientId}&partnerId={$link_params.partnerId}&displayMode=minibrowser&partnerLogoUrl={$link_params.partnerLogoUrl}&returnToPartnerUrl={$link_params.return_url}&integrationType=FO&features=PAYMENT&country.x={$link_params.country}&locale.x={$link_params.locale}&product=ppcp&sellerNonce={$link_params.sellerNonce}" data-paypal-button="PPLtBlue" class="ppp-boarding-btn" id="ppp-boarding-btn">{$smarty.const.PAYPAL_PARTNER_CONTINUE_PAYPAL}</a>
              {*<a data-paypal-button="true" href="{$url}&displayMode=minibrowser" class="btn btn-primary "></a>*}
            </span></div>
            </div>
        </div>
<script>
    try {
        $('.ppp-boarding-btn').prop('disabled', true);
    }catch (e ) {
        console.log(e);
    }

    (function(d, s, id){
    var js, ref = d.getElementsByTagName(s)[0]; if (!d.getElementById(id)){
    js = d.createElement(s); js.id = id; js.async = true;
    js.src = "https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js";
    ref.parentNode.insertBefore(js, ref); }
    }(document, "script", "paypaljs"));

    var pppIntervalId = window.setInterval(function() {
        if (window.PAYPAL) {
            window.PAYPAL.apps.Signup.loadScripts(document, 'script');
            try {
                if (typeof PAYPAL.apps.Signup.MiniBrowser.init !== 'function' ) {
                    return false;
                }
            } catch (e) {
                return false;
            }
            // Clear the interval id
            window.clearInterval(pppIntervalId);
            window.PAYPAL.apps.Signup.MiniBrowser.init();
            var pppIntervalId1 = window.setInterval(function() {
                var pppinited = false;
                try {
                    var attr = $($('.ppp-boarding-btn')[0]).css('display');
                    if (typeof attr !== 'undefined' && attr !== false  && attr !== '' && attr !== 'inline') {
                        pppinited = true;
                    } else {
                        return false;
                    }
                    //don't work :(
                    //(jQuery._data || jQuery.data)($("#ppp-boarding-btn")[0], "events").length
                    //pppinited = true;
                } catch ( e ) {
                    console.log(e);
                }

                if (pppinited) {
                    window.clearInterval(pppIntervalId1);

                    $('.ppp-boarding-widget').removeClass('ppp-boarding-widget-wait');
                    $('.ppp-boarding-btn').prop('disabled', false);
                    try {
                        //$("#ppp-boarding-btn")[0].click();
                    } catch (e ) {  }
                }

            }, 500);
        }
    }, 500);

</script>