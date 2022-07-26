<div class="widget box box-no-shadow " >
        <div class="widget-header after">
            <h4>{$smarty.const.PAYPAL_PARTNER_CONTINUE_PAYPAL}</h4>
        </div>
        <div class="widget-content after" >
            {$smarty.const.PAYPAL_PARTNER_GRANT_PERMISSIONS}
            <div dir="ltr" style="text-align: left;" trbidi="on">
  
            <script>

                (function(d, s, id){
                var js, ref = d.getElementsByTagName(s)[0]; if (!d.getElementById(id)){
                js = d.createElement(s); js.id = id; js.async = true;
                js.src = "https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js";
                ref.parentNode.insertBefore(js, ref); }
                }(document, "script", "paypal-js"));

              </script>

              <a data-paypal-button="true" href="{$url}&displayMode=minibrowser" target="PPFrame" class="btn btn-primary">{$smarty.const.PAYPAL_PARTNER_CONTINUE_PAYPAL}</a>

            </div>
        </div>    
</div>

