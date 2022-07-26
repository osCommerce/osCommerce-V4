<div class="widget box box-no-shadow">
    <div class="widget-header">
        <h4>Shipping method</h4>
        {$manager->render('Toolbar')}
    </div>
    <div class="widget-content after">
        <div class="shipping-method" id="shipping_method">
            <div>
                {if !$manager->getShipping()}
                    <div class="item no-shipping-item">
                        <label class="row">
                            Shipping is not selected
                            <div class="input"><input value="" checked="checked" type="radio" name="shipping" /></div>
                        </label>
                    </div>
                {/if}
                {foreach $manager->getShippingQuotesByChoice() as $shipping_quote_item}
                    <div class="item" {if isset($shipping_quote_item.hide_row) && $shipping_quote_item.hide_row}style="display: none;"{/if}>
                        <div class="title">{$shipping_quote_item.module}</div>
                        {if isset($shipping_quote_item.error) && $shipping_quote_item.error}
                            
                        {else}
                            {foreach $shipping_quote_item.methods as $shipping_quote_item_method}
                                <div class="subItem">
                                    <label class="row">
                                        <div class="input"><input value="{$shipping_quote_item_method.code}" {if $shipping_quote_item_method.selected}checked="checked"{/if} type="radio" name="shipping" data-widget="{if isset($shipping_quote_item.widget) && $shipping_quote_item.widget}{$shipping_quote_item.id}{/if}"/></div>
                                        <div class="cost">{$shipping_quote_item_method.cost_f}</div>
                                        <div class="sub-title">{$shipping_quote_item_method.title}{if $shipping_quote_item_method.description|default:null}{$shipping_quote_item_method.description}{/if}</div>
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
        {if \common\helpers\Acl::checkExtensionAllowed('DelayedDespatch', 'allowed')}
            {\common\extensions\DelayedDespatch\DelayedDespatch::viewAdminEditOrder($manager)}
        {/if}
        <script>
            (function($){
                $('#shipping_method').on('click',function(e) {
                    if ( e.target.tagName.toLowerCase()=='input' && e.target.name=='shipping' ) {
                        order.dataChanged( $('#checkoutForm'), 'shipping_changed');
                        $('.shipping-widgets').hide();
                        var widget = $(e.target).closest('.subItem').find('.shipping-widgets');
                        if(widget.length){
                            widget.show();
                        }
                    }
                });
            })(jQuery);
        </script>
    </div>
</div>
