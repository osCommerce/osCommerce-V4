{use class="frontend\design\Info"}

{$id = rand()}

{if isset($can_apply_gv_credit) && $can_apply_gv_credit}
  <div class="discount-box credit-box" id="b-{$id}">
    <div class="amount-title">
      <span class="title">{$smarty.const.CREDIT_AMOUNT}:</span>
      <span class="amount-val">{$credit_amount}</span>
    </div>
      {if $message_discount_gv}
          {$message_discount_gv}
      {/if}
    <div class="js_cot_gv_dep">

      <input name="credit_apply[gv][cot_gv]" class="cot_gv" type="checkbox" {if $credit_gv_in_use} checked="checked" {/if} style="display: none"/>

      <input  autocomplete="off"
              class="cot_gv_amount"
              placeholder="{$smarty.const.ENTER_AMOUNT}"
              name="credit_apply[gv][cot_gv_amount]"
              value="{$cot_gv_amount}"
              type="text"/>

      <input type="hidden" name="credit_apply[gv][cot_gv_present]" value="1">

      <span class="btn-remove"></span>
    </div>
  </div>
  <script>
      tl( '{Info::themeFile('/js/bootstrap-switch.js')}', function(){
          var box = $('#b-{$id}');
          var removeBtn = $('.btn-remove', box);
          var cot_gv_amount = $('.cot_gv_amount', box);
          var cot_gv = $('.cot_gv', box);


          cot_gv_amount.on('change',function() {
              if ($(this).val() && $(this).val() != 0) {
                  cot_gv.prop('checked', true)
              } else {
                  cot_gv.prop('checked', false)
              }
              box.closest('form').trigger('cart-change');
              return false;
          });

          $(window).off('removeCreditAmount').on('removeCreditAmount', handleRemoveCreditAmount);
          removeBtn.on('click', handleRemoveCreditAmount);

          function handleRemoveCreditAmount(){
              cot_gv_amount.val('');
              cot_gv_amount.trigger('change');
              removeBtn.hide()
          }

          inputChange();
          cot_gv_amount.on('change', inputChange);
          function inputChange() {
              if (cot_gv_amount.val()) {
                  removeBtn.show()
              } else {
                  removeBtn.hide()
              }
          }
      })
  </script>

{/if}