{if $credit_modules.ot_gv && $credit_modules.credit_amount > 0 }
    {use class="\yii\helpers\Html"}
    <div class="discount-box credit-box">
        <div>
            <label>
                {Html::checkbox('cot_gv',$credit_modules.cot_gv_active, ['class' => 'credit-on-off'])} <span class="title">{$smarty.const.TEXT_CREDIT_AMOUNT_ASK_USE}</span> 
                <span class="title">{$smarty.const.TEXT_CREDIT_AMOUNT_INFO}</span> {$credit_modules.credit_amount_formatted}
            </label>
        </div>
        <div class="js_cot_gv_dep">
            <div class="inp">
                {Html::textInput('cot_gv_amount', $credit_modules.custom_gv_amount, ['class' => 'form-control'])}                
                <button type="button" class="btn js_discount_apply">{$smarty.const.TEXT_APPLY}</button>
            </div>
        </div>
        <script>
        (function($){
            $('.credit-on-off').change(function(){
                var value = 'n';
                var _checked = $(this).prop('checked');
                if (_checked){
                    value='y';
                }
                order.dataChanged($('#checkoutForm'), 'credit_class', [{
                    name:'coupon_apply',value:value
                }]);
                order.switchUpdate($('.credit-box .js_cot_gv_dep'), _checked);            
            })
            
            $(document).ready(function(){
                if (!$('.credit-on-off').is(':checked')){
                    order.switchUpdate($('.credit-box .js_cot_gv_dep'), false);
                }
            })
            
            $('.js_discount_apply').on('click',function() {
              order.dataChanged($('#checkoutForm'), 'credit_class', [{
                name:'coupon_apply',value:'y'
              }]);
              return false;
            });
        })(jQuery)
        </script>
    </div>
{/if}