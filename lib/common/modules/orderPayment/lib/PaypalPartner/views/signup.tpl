<style>
    .ppp-boarding-widget-wait { opacity: 0.3; cursor: wait; }
</style>

<div class="widget box box-no-shadow " >
        <div class="widget-header after">
            <h4>{$smarty.const.PAYPAL_PARTNER_CONTINUE_PAYPAL}</h4>
        </div>
        <div class="widget-content after ppp-boarding-widget ppp-boarding-widget-wait">
            {if $boardingMode==1}
                {*$smarty.const.PAYPAL_PARTNER_CONTINUE_PAYPAL*}
                {$callback='data-paypal-onboard-complete="onboardedCallback"'}
            {else}
                {$smarty.const.PAYPAL_PARTNER_GRANT_PERMISSIONS}
                {$callback=''}
            {/if}
            <div dir="ltr" style="text-align: left;" >
  
            <script>
            {if $boardingMode==1}

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
                
            {/if}

              </script>
              <a {$callback} data-paypal-button="true" href="{$url}&displayMode=minibrowser" class="btn btn-primary ppp-boarding-btn" id="ppp-boarding-btn">{$smarty.const.PAYPAL_PARTNER_CONTINUE_PAYPAL}</a>

            </div>
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
                    if (PAYPAL) {
                        // Clear the interval id
                        window.clearInterval(pppIntervalId);
                        PAYPAL.apps.Signup.MiniBrowser.init();
                        var pppIntervalId1 = window.setInterval(function() {
                            var pppinited = false;
                            try {
                                (jQuery._data || jQuery.data)($("#ppp-boarding-btn")[0], "events").length
                                pppinited = true;
                            } catch ( e ) {
                            }

                            window.clearInterval(pppIntervalId1);

                            $('.ppp-boarding-widget').removeClass('ppp-boarding-widget-wait');
                            $('.ppp-boarding-btn').prop('disabled', false);
                        }, 500);
                    }
                }, 500);

</script>