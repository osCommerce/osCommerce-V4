{if $credit_modules.ot_gv && $credit_modules.credit_amount>0 }
    {use class="frontend\design\Info"}
    <div class="discount-box credit-box">
        <div class="amount-title">
            <span class="title">{$smarty.const.TEXT_CREDIT_AMOUNT_INFO}</span>
            <span class="amount-val">{$credit_modules.credit_amount_formatted}</span>
        </div>
        {if $creditPaymentExists}
            {if $creditPaymentExists == 'enabled'}
                <div class="text">You can pay in full</div>
            {else}
                <div class="text">You can't pay in full</div>
            {/if}
        {else}
        <div class="text">{$smarty.const.TEXT_CREDIT_AMOUNT_CUSTOM_USE}</div>
        <div class="js_cot_gv_dep">
            <input type="checkbox" name="cot_gv" {if $credit_modules.cot_gv_active } checked="checked" {/if} style="display: none">
            <input
                    type="text"
                    autocomplete="off"
                    class="cot_gv_amount"
                    name="cot_gv_amount"
                    value="{$credit_modules.custom_gv_amount}"
                    placeholder="0"
            >
            <span class="btn-remove"></span>
        </div>
        {/if}
    </div>
    <script>
    tl( '{Info::themeFile('/js/bootstrap-switch.js')}', function(){
        var box = $('#box-{$id}');
        var input = $('input[name="cot_gv_amount"]', box);
        var removeBtn = $('.btn-remove', box);
        var cot_gv_amount = $('.cot_gv_amount');


        cot_gv_amount.on('keyup',function() {
            if ($(this).val() && $(this).val() != 0) {
                $('input[name="cot_gv"]').prop('checked', true)
            } else {
                $('input[name="cot_gv"]').prop('checked', false)
            }
            checkout.data_changed('credit_class', [{
                name:'coupon_apply',value:'y'
            }]);
            return false;
        });

        removeBtn.on('click', function(){
            input.val('');
            cot_gv_amount.trigger('keyup');
            removeBtn.hide()
        });

        inputChange();
        input.on('change keyup', inputChange);
        function inputChange() {
            if (input.val()) {
                removeBtn.show()
            } else {
                removeBtn.hide()
            }
        }

         /*$('.credit-on-off').bootstrapSwitch({
          offText: '{$smarty.const.TEXT_NO}',
          onText: '{$smarty.const.TEXT_YES}',
          onSwitchChange: function (a, key) {
            var value = 'n';
            if (key) value='y';
            checkout.data_changed('credit_class', [{
                name:'coupon_apply',value:value
            }]);
            checkout.switch_update($('.credit-box .js_cot_gv_dep'), key);
          }
        });
        if (!$('.credit-on-off').is(':checked')){
            checkout.switch_update($('.credit-box .js_cot_gv_dep'), false);
        }*/
    })
    </script>
    
{/if}