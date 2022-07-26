<klarna-placement
  data-key="{if !empty($data_key)}{$data_key}{else}credit-promotion-small{/if}"
  {if !empty($locale)}
  data-locale="{$locale}"
  {/if}
  {if !empty($theme)}
   data-theme="{$theme}"
  {/if}
  data-purchase-amount="{$price}"
></klarna-placement>
<script>
tl(function(){
    var klarna_price = '';
    var klarna_qty = 0;
    window.KlarnaOnsiteService = window.KlarnaOnsiteService || [];
    $(window).on('getFullFinalPrice', function(event, data){
        if (data && parseFloat(data)) {
            try {
                $("klarna-placement").attr('data-purchase-amount', Math.round(parseFloat(data).toFixed(2)*100));
                window.KlarnaOnsiteService.push({ eventName: 'refresh-placements' });
            } catch (e) { }
//console.log("FFP " + parseFloat(data).toFixed(2));
        }
    });

    $(window).on('changedQty', function(event, data){
        if (data>0) {
            klarna_qty = data;
        }
    });

    $("form[name=cart_quantity]").on('attributes_updated', function(event, data){
        if (data.hasOwnProperty('special_price') && data.special_price.length > 0){
            klarna_price = data.special_price;
        } else {
            klarna_price = data.product_price;
        }
        if (klarna_price.length > 0){
            var text = '';
            try {
                if ($(klarna_price).length>0) {
                    $(klarna_price).each(function(){
                        if ($(this).attr('itemprop') == 'price') {
                            text = $(this).attr('content');
                        }
                    });
                } else {
                    //strip tags
                    var div = document.createElement("div");
                    div.innerHTML = klarna_price;
                    text = div.textContent || div.innerText || "";
                }
                var amount = parseFloat(text);
                klarna_price = amount;
            } catch (e ) { console.log(e); };
        }
        if (klarna_price>0 && klarna_qty>0) {
//console.log(klarna_qty + " * " + klarna_price);
            $("klarna-placement").attr('data-purchase-amount', Math.round((klarna_qty*klarna_price).toFixed(2)*100) );

            window.KlarnaOnsiteService.push({ eventName: 'refresh-placements' });

        }
    });
})
</script>