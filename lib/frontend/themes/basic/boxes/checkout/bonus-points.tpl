{if !is_null($bonus_points) && $bonus_points.can_use_bonuses}
    {use class="frontend\design\Info"}
    <div class="discount-box">
        <div class="title">
            {sprintf($smarty.const.TEXT_BONUS_POINTS, $bonus_points.bonuses->customer_bonus_points_earn)}
            {if \common\helpers\Points::getCurrencyCoefficient($groupId)}
                ({\common\helpers\Points::getBonusPointsPriceInCurrencyFormatted($bonus_points.bonuses->customer_bonus_points_earn, $groupId)})
            {/if}
        </div>
        <div class="text">
            {$smarty.const.TEXT_CREDIT_AMOUNT_CUSTOM_USE}
        </div>
        <div class="js_bonus_dep">
            <input type="checkbox" name="use_bonus_points" {if $bonus_points.bonus_apply } checked="checked" {/if} style="display: none">
            <input
                    type="text"
                    autocomplete="off"
                    class="bonus_points_amount"
                    name="bonus_points_amount"
                    data-max="{$bonus_points.bonuses->customer_bonus_points_earn}"
                    value="{$bonus_points.bonuses->customer_bonus_points}"
                    placeholder="0"
            >
            <span class="btn-remove"></span>
        </div>
    </div>
    
    <script>
    tl([
      '{Info::themeFile('/js/main.js')}',
      '{Info::themeFile('/js/bootstrap-switch.js')}'
    ], function(){
        var box = $('#box-{$id}');
        var input = $('input[name="bonus_points_amount"]', box);
        var removeBtn = $('.btn-remove', box);
        var bonus_points_amount = $('.bonus_points_amount')

        if (!$('input[name="use_bonus_points"]').prop('checked')) {
            bonus_points_amount.val('')
        }
        bonus_points_amount.on('keyup',function() {
            if ($(this).val() && $(this).val() != 0) {
                $('input[name="use_bonus_points"]').prop('checked', true)
            } else {
                $('input[name="use_bonus_points"]').prop('checked', false)
            }
            checkout.data_changed('credit_class', [{
                name:'bonus_apply',value:'y'
            }]);
            return false;
        });

        removeBtn.on('click', function(){
            input.val('');
            bonus_points_amount.trigger('keyup');
            removeBtn.hide()
        });
        $(window).off('removeBonusPoints').on('removeBonusPoints', handleRemoveBonusPoints);

        function handleRemoveBonusPoints(){
            input.val('');
            bonus_points_amount.trigger('keyup');
            removeBtn.hide()
        };

        inputChange();
        input.on('change keyup', inputChange);
        function inputChange() {
            if (input.val()) {
                removeBtn.show()
            } else {
                removeBtn.hide()
            }
        }


        /*$('.bonus-on-off').bootstrapSwitch({
          offText: '{$smarty.const.TEXT_NO}',
          onText: '{$smarty.const.TEXT_YES}',
          onSwitchChange: function (a, key) {
            var value = 'n';
            if (key) value='y';
            checkout.data_changed('credit_class',[{
                    name:'bonus_apply',value:value
                }]);            
            checkout.switch_update($('.js_bonus_dep'), key);
          }
        }); 

        if (!$('.bonus-on-off').is(':checked')){
            checkout.switch_update($('.js_bonus_dep'), false);
        }

        $('.js_bonus_apply').on('click',function() {
          if (parseFloat($('input[name=bonus_points_amount]').val()) > parseFloat($('input[name=bonus_points_amount]').data('max'))){
            $('input[name=bonus_points_amount]').val($('input[name=bonus_points_amount]').data('max'));
          }
          checkout.data_changed('credit_class', [{
            name:'bonus_apply',value:'y'
          },{
            name:'bonus_points_amount', value:$('input[name=bonus_points_amount]').val()
          }]);
          return false;
        });*/
    })
    </script>
{/if}
