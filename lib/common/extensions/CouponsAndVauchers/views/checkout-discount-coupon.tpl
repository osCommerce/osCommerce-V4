{if $credit_modules.ot_coupon || $credit_modules.ot_gv }
<div class="discount-box ot_coupon ot_gv">
  <div class="title">{$smarty.const.TEXT_DISCOUNT_COUPON_HEAD}</div>
  <div class="text">{$smarty.const.TEXT_DISCOUNT_COUPON_APPLY_INFO}</div>
  <div class="inp">
    <input type="text" value="{$credit_modules.applied_coupon_code}" name="gv_redeem_code" autocomplete="off" >
      <span class="btn-remove"></span>
    <button type="button" class="btn js_discount_apply">{$smarty.const.TEXT_APPLY}</button>
  </div>
  <div id="credit_modules_message"></div>
</div>
{/if}

<script type="text/javascript">
  tl(function () {
      var box = $('#box-{$id}');
      var input = $('input[name="gv_redeem_code"]', box);
      var removeBtn = $('.btn-remove', box);

      $('.js_discount_apply', box).on('click',function() {
          checkout.data_changed('credit_class', [{
              name:'coupon_apply',value:'y'
          }]);
          return false;
      });
      removeBtn.on('click', function(){
          input.val('');
          checkout.data_changed('credit_class', [{
              name:'coupon_apply',value:'y'
          }]);
          removeBtn.hide()
      });

      $(window).on('removeDiscountCoupon', function(){
          input.val('');
          checkout.data_changed('credit_class', [{
              name:'coupon_apply',value:'y'
          }]);
          removeBtn.hide()
      })

      if (input.val()) {
          removeBtn.show()
      } else {
          removeBtn.hide()
      }
      input.on('change', function(){
          if ($(this).val()) {
              removeBtn.show()
          } else {
              removeBtn.hide()
          }
      })
  })
</script>
