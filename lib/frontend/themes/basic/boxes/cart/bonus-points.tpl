{if !is_null($bonus_points) && $bonus_points.can_use_bonuses}

    {$idRand = rand()}

    {use class="frontend\design\Info"}
    <div class="discount-box" id="b-{$idRand}">
        <div class="title">
            {sprintf($smarty.const.TEXT_BONUS_POINTS, $bonus_points.bonuses->customer_bonus_points_earn)}
            {if \common\helpers\Points::getCurrencyCoefficient($groupId)}
                ({\common\helpers\Points::getBonusPointsPriceInCurrencyFormatted($bonus_points.bonuses->customer_bonus_points_earn, $groupId)})
            {/if}
        </div>
        <div class="text">{$smarty.const.ENTER_AMOUNT}</div>
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
        var box = $('#b-{$idRand}');
        var use_bonus_points = $('input[name="use_bonus_points"]', box);
        var removeBtn = $('.btn-remove', box);
        var bonus_points_amount = $('.bonus_points_amount', box)

        if (!$('input[name="use_bonus_points"]').prop('checked')) {
            bonus_points_amount.val('')
        }
        bonus_points_amount.on('change',function() {

            if ($(this).val() && $(this).val() != 0) {
                use_bonus_points.prop('checked', true)
            } else {
                use_bonus_points.prop('checked', false)
            }

            var form = $('#cart-form');
            $.post(form.attr('action'), form.serializeArray(), function(d){
                $.get('{Yii::$app->urlManager->createUrl('shopping-cart')}', function(d){
                    var inputName = $('input:focus').attr('name');
                    $('#cart-page').replaceWith(d);
                    var inp = $('input[name="'+inputName+'"]').focus().get(0);

                    var val = inp.value;
                    inp.value = '';
                    inp.value = val;

                    form.trigger('cart-change');
                });
            });

            return false;

        });

        removeBtn.on('click', handleRemoveBonusPoints);
        $(window).off('removeBonusPoints').on('removeBonusPoints', handleRemoveBonusPoints);

        function handleRemoveBonusPoints(){
            bonus_points_amount.val('');
            bonus_points_amount.trigger('change');
            removeBtn.hide()
        };

        inputChange();
        bonus_points_amount.on('change', inputChange);
        function inputChange() {
            if (bonus_points_amount.val()) {
                removeBtn.show()
            } else {
                removeBtn.hide()
            }
        }

    })
    </script>
{/if}
