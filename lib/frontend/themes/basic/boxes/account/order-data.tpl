{use class="frontend\design\Info"}
{if $settings[0].orders_data == 'name'}

    <div class="orders-data orders-data-name">{$order_customer.name|escape:'html'}</div>

{elseif $settings[0].orders_data == 'email'}

    <div class="orders-data orders-data-email">{$order_customer.email_address|escape:'html'}</div>

{elseif $settings[0].orders_data == 'telephone'}

    {if $order_customer.telephone}
        <div class="orders-data orders-data-telephone">{$order_customer.telephone|escape:'html'}</div>
    {else}
        {$empty = true}
    {/if}

{elseif $settings[0].orders_data == 'delivery_address'}

    {if $order_delivery_address}
        <div class="orders-data orders-data-delivery-address">{$order_delivery_address}</div>
    {else}
        {$empty = true}
    {/if}

{elseif $settings[0].orders_data == 'billing_address'}

    <div class="orders-data orders-data-billing">{$order_billing}</div>

{elseif $settings[0].orders_data == 'shipping_method'}

    {if $order_shipping_method}
        <div class="orders-data orders-data-shipping-method">{$order_shipping_method}</div>
    {else}
        {$empty = true}
    {/if}

{elseif $settings[0].orders_data == 'payment_method'}

    <div class="orders-data orders-data-payment-method">{$payment_method}</div>

{/if}

{if $empty && !Info::isAdmin()}
    <script>
        tl(function(){
            {if $settings[0].hide_parents == 1}
            $('#box-{$id}').hide()
            {elseif $settings[0].hide_parents == 2}
            $('#box-{$id}').closest('.box-block').hide()
            {elseif $settings[0].hide_parents == 3}
            $('#box-{$id}').closest('.box-block').closest('.box-block').hide()
            {elseif $settings[0].hide_parents == 4}
            $('#box-{$id}').closest('.box-block').closest('.box-block').closest('.box-block').hide()
            {/if}
        })
    </script>
{/if}