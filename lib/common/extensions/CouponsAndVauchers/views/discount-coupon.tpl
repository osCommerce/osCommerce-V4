{use class="frontend\design\Info"}

{$id = rand()}
<div class="discount-box ot_coupon ot_gv" id="b-{$id}">
    <div class="title">{$smarty.const.TEXT_PROMO_CODE}</div>
    {if !$popup}
        {if $message_discount_coupon}
            {$message_discount_coupon}
        {/if}
    {/if}
    <div class="inp">
        <input type="text" class="gv_redeem_code" name="gv_redeem_code" value="{$gv_redeem_code}" autocomplete="off" />
        <span class="btn-remove"></span>
        <button type="submit" class="btn">{$smarty.const.TEXT_APPLY}</button>
    </div>
    <div id="credit_modules_message"></div>
</div>


{if $popup}
<script>
    tl('', function(){
        alertMessage('<h4>{$smarty.const.DISCOUNT_COUPON}</h4><div>{$message_discount_coupon}</div>');
    })
</script>
{/if}

<script>
    tl( '{Info::themeFile('/js/bootstrap-switch.js')}', function(){
        var box = $('#b-{$id}');
        var removeBtn = $('.btn-remove', box);
        var gv_redeem_code = $('.gv_redeem_code', box);
        var submitButton = $('button', box);

        removeBtn.on('click', handleRemoveDiscountCoupon);
        $(window).off('removeDiscountCoupon').on('removeDiscountCoupon', handleRemoveDiscountCoupon);

        function handleRemoveDiscountCoupon(){
            gv_redeem_code.val('');
            box.closest('form').trigger('cart-change');
            removeBtn.hide()
        }

        submitButton.on('click', function(e){
            e.preventDefault();

            box.closest('form').trigger('cart-change');
        });
		
		$('.title').click(function(){
			$(this).toggleClass('active opened');
		});

        inputChange();
        gv_redeem_code.on('change keyup', inputChange);
        function inputChange() {
            if (gv_redeem_code.val()) {
                removeBtn.show()
            } else {
                removeBtn.hide()
            }
        }
    })
</script>