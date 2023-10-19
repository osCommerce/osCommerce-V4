    <div class="payment-method" id="payment_method">
        {*foreach $selection as $i*}
        {foreach $manager->getPaymentSelection() as $i}
            <div class="item payment_item payment_class_{$i.id}"  {if isset($i.hide_row) && $i.hide_row} style="display: none"{/if}>
                {if isset($i.methods)}
                    {foreach $i.methods as $m}
                        <div class="item-radio item-payment {$m.id}" {if isset($m.hide) && $m.hide} style="display: none"{/if}>
                            <label>
                                <input type="radio" name="payment" value="{$m.id}"{if isset($i.hide_input) && $i.hide_input} style="display: none"{/if}{if $m.checked} checked{/if}/>
                                <span>{$m.module}</span>
                            </label>
                        </div>
                    {/foreach}
                {else}
                    <div class="item-radio">
                        <label>
                            <input type="radio" name="payment" value="{$i.id}"{if isset($i.hide_input) && $i.hide_input} style="display: none"{/if}{if $i.checked} checked{/if}/>
                            <span>{$i.module}</span>
                        </label>
                    </div>
                {/if}
                {if isset($i.fields)}
                {foreach $i.fields as $j}
                    <div class="sub-item">
                        <label>
                            <span>{$j.title}</span>
                        </label>
                        {$j.field}
                    </div>
                {/foreach}
                {/if}
                {if isset($combine_fields_notes) && isset($i.notes)}
                {foreach $i.notes as $note}
                    <div class="sub-item payment-note">
                        {$note}
                    </div>
                {/foreach}
                {/if}
            </div>
        {/foreach}

    {if !isset($combine_fields_notes) && isset($i.notes)}
    <div class="payment-notes" id="payment_notes">
        {foreach $manager->getPaymentSelection() as $i}
            <div class="item payment_item_note payment_class_{$i.id}"  {if isset($i.hide_row) && $i.hide_row} style="display: none"{/if}>
                {foreach $i.notes as $note}
                    <div class="sub-item payment-note" style="display: none">
                        {$note}
                    </div>
                {/foreach}
            </div>
        {/foreach}
    </div>
    {/if}
    <script>
{use class="frontend\design\Info"}
        function onPaymentChangeBlockHandler() {
            for (let ff of checkout_payment_changed.get()) {
                if (typeof window[ff] === 'function') {
                    try {
                        window[ff]();
                    } catch (e) {
                        console.log(e);
                    }
                } else {
                    console.log(ff + ' not a function');
                }
            }
        }
        tl([
            '{Info::themeFile('/js/main.js')}',
        ], function(){
            $('#payment_method').off('click').on('click',function(e){
              if ( e.target.tagName.toLowerCase()=='input' && e.target.name=='payment' ) {
                checkout.data_changed('payment_changed');
                onPaymentChangeBlockHandler();
              }
            });
            //trigger change after list update to hide extra fields on inactive modules
            try {
                if ($('#payment_method input[name=payment]:checked').length) {
                    $('#payment_method input[name=payment]:checked').trigger('click');
                } else {
                    $('#payment_method input[name=payment]:first').trigger('click');
                }
            } catch ( e ) {
//console.log( e );
            }
        })
    </script>
    
    </div>
