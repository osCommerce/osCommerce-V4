{if $hasOt }
    {use class="\yii\helpers\Html"}
    <div class="discount-box coupon-box">
        <div>
            <label>                
                <span class="title">Promotional code</span>
            </label>
        </div>
        <div class="js_cot_gv_dep">
            <div class="inp">
                {Html::textInput('coupons', '', ['class' => 'form-control'])}                
                <button type="button" class="btn js_coupons_apply">{$smarty.const.TEXT_APPLY}</button>
            </div>
        </div>
        <div class="required"></div>
         <script>
        (function($){   
            $('.js_coupons_apply').on('click',function() {
              order.dataChanged($('#checkoutForm'), 'detect_code', [], function(data){
                if(data.hasOwnProperty('credit_modules')){
                    $.each(data.credit_modules, function (i, e){
                        $('.coupon-box .required').text(e.message);                            
                    })
                }
              });
              return false;
            });
        })(jQuery)
        </script>
    </div>
{/if}