    <div class="payment-method" id="payment_method">
        {*foreach $selection as $i*}
        {foreach $manager->getPaymentSelection() as $i}
            <div class="item payment_item payment_class_{$i.id}"  {if isset($i.hide_row) && $i.hide_row} style="display: none"{/if}>
                {if isset($i.methods)}
                    {foreach $i.methods as $m}
                        <div class="item-radio">
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
    </div>
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
        tl([
        ], function(){
            $('#payment_method').on('click',function(e){
              if ( e.target.tagName.toLowerCase()=='input' && e.target.name=='payment' ) {
                checkout.data_changed('payment_changed');
                if (typeof window.toggleSubFields_paypal_partner == 'function'){
                    try {
                        window.toggleSubFields_paypal_partner();
                    } catch (e) { }
                }
              }
            });
        })
    </script>
    
