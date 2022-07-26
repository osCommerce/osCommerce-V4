<div class="pp-pay-later-message" data-pp-amount="{$price}" data-pp-message></div>
<script>
tl(function(){
    var pppaylater_price = '';
    var pppaylater_qty = 0;
    $(window).on('getFullFinalPrice', function(event, data){
        if (data && parseFloat(data)) {
            try {
                $("#pp-pay-later-message").attr('data-pp-amount', parseFloat(data).toFixed(2));
            } catch (e) { }
//console.log("FFP " + parseFloat(data).toFixed(2));
        }
    });

    $(window).on('changedQty', function(event, data){
        if (data>0) {
            pppaylater_qty = data;
        }
    });

    $("form[name=cart_quantity]").on('attributes_updated', function(event, data){
        if (data.hasOwnProperty('special_price') && data.special_price.length > 0){
            pppaylater_price = data.special_price;
        } else {
            pppaylater_price = data.product_price;
        }
        if (pppaylater_price.length > 0){
            var text = '';
            try {
                if ($(pppaylater_price).length>0) {
                    $(pppaylater_price).each(function(){
                        if ($(this).attr('itemprop') == 'price') {
                            text = $(this).attr('content');
                        }
                    });
                } else {
                    //strip tags
                    var div = document.createElement("div");
                    div.innerHTML = pppaylater_price;
                    text = div.textContent || div.innerText || "";
                }
                var amount = parseFloat(text);
                pppaylater_price = amount;
            } catch (e ) { console.log(e); };
        }
        if (pppaylater_price>0 && pppaylater_qty>0) {
//console.log(pppaylater_qty + " * " + pppaylater_price);
            $("#pp-pay-later-message").attr('data-pp-amount', (pppaylater_qty*pppaylater_price).toFixed(2));
        }
    });
})
</script>