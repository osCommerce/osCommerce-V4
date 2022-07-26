<div class="shipping-method">
    {$order->info['shipping_method']}
</div>
{if isset($shipping_additional_info_block) && !empty($shipping_additional_info_block)}
    <div class="additional-info">
        {$shipping_additional_info_block}
    </div>
{/if}