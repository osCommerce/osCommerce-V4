{if !$product.stock_indicator.request_for_quote && (!$product.product_has_attributes || $product.product_has_attributes)}
    <div class="paypal_button">
        {$product.show_paypal_button}
    </div>
{/if}