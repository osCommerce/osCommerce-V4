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

    $(window).on('checkout_worker_complete', function (event) {
        try {
            var price = parseFloat($('.price-row.total.ot_total input.ot_total_clear').val()) || 0;
        } catch (e) { price = 0; }

        $('klarna-placement').attr('data-purchase-amount', Math.round(price*100));
        window.KlarnaOnsiteService.push({ eventName: 'refresh-placements' });
    });
{if !empty($forceRender) }
    window.KlarnaOnsiteService.push({ eventName: 'refresh-placements' });
{/if}

})
</script>