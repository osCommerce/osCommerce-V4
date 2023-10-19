{use class="frontend\design\Info"}


<div class="shipping-method" id="shipping_method">
    <div>

        {assign var=mCount value = 999}
	{assign var=quotes value = $manager->getShippingQuotesByChoice()}
        {if !$manager->combineShippings}
            {if $manager->getShippingChoice()}
                {$mCount = $manager->getShippingCollection()->deliveryMethodsCount}
            {else}
                {$mCount = $manager->getShippingCollection()->pickupMethodsCount}
            {/if}
        {/if}
        {foreach $quotes as $shipping_quote_item}
            <div class="item" {if isset($shipping_quote_item.hide_row) && $shipping_quote_item.hide_row}style="display: none;"{/if}>
                <div class="title">{$shipping_quote_item.module}</div>
                {if isset($shipping_quote_item.error) && $shipping_quote_item.error}
                    {*<div class="error">{$shipping_quote_item.error}</div>*}
                {else}
                    {foreach $shipping_quote_item.methods as $shipping_quote_item_method}
                        <div class="subItem">
                            <label class="row{if $mCount > 1} sub-item{/if}">
                                {if $mCount > 1}
                                    <div class="input"><input value="{$shipping_quote_item_method.code}" {if $shipping_quote_item_method.selected}checked="checked"{/if} type="radio" name="shipping" /></div>
                                {else}
                                    <input value="{$shipping_quote_item_method.code}" type="hidden" name="shipping"/>
                                {/if}
                                <div class="cost">
                                    {if $PremiumAccountClass = \common\helpers\Acl::checkExtensionAllowed('PremiumAccount', 'allowed')}
                                        {$PremiumAccountClass::showShippingCost($shipping_quote_item_method.cost, $shipping_quote_item.tax)}
                                    {/if}
                                    {$shipping_quote_item_method.cost_f}
                                </div>
                                <div class="sub-title">{$shipping_quote_item_method.title}{if isset($shipping_quote_item_method.description)}{$shipping_quote_item_method.description}{/if}</div>
                            </label>
                            {if isset($shipping_quote_item_method.widget) && $shipping_quote_item_method.widget}
                                <div class="{$shipping_quote_item_method.code}_shipping_widget shipping-widgets" style="{if !$shipping_quote_item_method.selected}display: none;{/if}" >
                                    {$shipping_quote_item_method.widget}
                                </div>
                            {/if}
                        </div>
                    {/foreach}
                {/if}
            </div>
        {/foreach}

    </div>
</div>
<script>
    tl([
      '{Info::themeFile('/js/main.js')}',
      '{Info::themeFile('/js/bootstrap-switch.js')}'
    ], function(){        
        $('#shipping_method').on('click',function(e) {
          if ( e.target.tagName.toLowerCase()=='input' && e.target.name=='shipping' ) {
            checkout.data_changed('shipping_changed');
            //checkout.data_changed('recalculation');
            $('.shipping-widgets').hide();
            var widget = $(e.target).closest('.subItem').find('.shipping-widgets');
            if(widget.length){
                widget.show();
            }
          }
        });
    })
</script>
