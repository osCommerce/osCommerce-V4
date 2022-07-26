

<div>
    {foreach $quotes as $shipping_quote_item}
        <div class="item" {if isset($shipping_quote_item.hide_row) && $shipping_quote_item.hide_row}style="display: none;"{/if}>
            <div class="title">{$shipping_quote_item.module}</div>
            {if isset($shipping_quote_item.error) && $shipping_quote_item.error}
                <div class="error">{$shipping_quote_item.error}</div>
            {else}
                {foreach $shipping_quote_item.methods as $shipping_quote_item_method}
                    <label class="row">
                        {if $quotes_radio_buttons>0}
                            <div class="input"><input value="{$shipping_quote_item_method.code}" {if $shipping_quote_item_method.selected}checked="checked"{/if} type="radio" name="shipping"/></div>
                        {else}
                            <input value="{$shipping_quote_item_method.code}" type="hidden" name="shipping"/>
                        {/if}
                        <div class="cost">{$shipping_quote_item_method.cost_f}</div>
                        <div class="sub-title">{$shipping_quote_item_method.title}{if isset($shipping_quote_item_method.description)}{$shipping_quote_item_method.description}{/if}</div>
                    </label>
                {/foreach}
                {if isset($shipping_quote_item.widget) && $shipping_quote_item.widget}
                    <div class="{$shipping_quote_item.id}_shipping_widget" {if !$shipping_quote_item.selected}style="display: none;"{/if} >
                        {$shipping_quote_item.widget}
                    </div>
                {/if}
            {/if}
        </div>
    {/foreach}

</div>
<script type="text/javascript">
    $('.popup-map-link').popUp({
        'box_class': 'tracking-numbers-popup'
    });
</script>