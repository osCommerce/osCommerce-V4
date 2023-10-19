{$paymentsAllowed = $manager->isPaymentAllowedTpl()}
<div class="widget box box-no-shadow{if !$paymentsAllowed['allowed']} dis_module" title="{$paymentsAllowed['reason']}"{else}"{/if}>
    <div class="widget-header">
        <h4>{$smarty.const.ENTRY_PAYMENT_METHOD}</h4>
        {$manager->render('Toolbar')}        
    </div>
    <div class="widget-content after">
        <div class="payment-method" id="payment_method">
            {if !$manager->getPayment()}
                <div class="item">
                    <div class="item-radio">
                        <label>
                            <input type="radio" name="payment" value="" checked />
                            <span>{$smarty.const.PAYMENT_IS_NOT_SELECTED}</span>
                        </label>
                    </div>
                </div>
            {/if}
            {foreach $manager->getPaymentSelection(false, false, 'auto') as $i}
                <div class="item payment_item payment_class_{$i.id}"  {if $i.hide_row|default:null} style="display: none"{/if}>
                    {if isset($i.methods)}
                        {foreach $i.methods as $m}
                            <div class="item-radio">
                                <label>
                                    <input type="radio" name="payment" value="{$m.id}"{if $i.hide_input|default:null} style="display: none"{/if}{if $m.checked|default:null} checked{/if} {if !$manager->isPaymentAllowed()} disabled{/if}/>
                                    <span>{$m.module}</span>
                                </label>
                            </div>
                        {/foreach}
                    {else}
                        <div class="item-radio">
                            <label>
                                <input type="radio" name="payment" value="{$i.id}"{if $i.hide_input|default:null} style="display: none"{/if}{if $i.checked|default:null} checked{/if} {if !$manager->isPaymentAllowed()} disabled{/if}/>
                                <span>{$i.module}</span>
                            </label>
                        </div>
                    {/if}
                    {if $i.checked|default:null && is_array($i.fields|default:null)}
                      {foreach $i.fields as $_field}
                        <div class="payment-field">
                            <label>
                              <span>{$_field.title|default:null}</span>:
                              {str_replace('<input ', '<input class="form-control" ', $_field.field|default:null)}
                            </label>
                        </div>
                      {/foreach}
                    {/if}

                    {*foreach $i.fields as $j}
                        <div class="sub-item">
                            <label>
                                <span>{$j.title}</span>
                            </label>
                            {$j.field}
                        </div>
                    {/foreach*}
                </div>
            {/foreach}
            <script>
                (function($){        
                    $('#payment_method').on('click',function(e){
                      if ( e.target.tagName.toLowerCase()=='input' && e.target.name=='payment' ) {
                        order.dataChanged($('#checkoutForm'), 'payment_changed');           
                      }
                    });
                })(jQuery);
            </script>
            <div class="cra">
                {$manager->render('CreditAmount', ['manager' => $manager])}
                {$manager->render('PromoCode', ['manager' => $manager])}
            </div>            
        </div>
    </div>
</div>