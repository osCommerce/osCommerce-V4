{use class = "yii\helpers\Html"}
<style>
    .warningText{
        color: #ee4225;
    }
    .w-form-convert{
        margin-top: 10px;
    }
    #bonusPointsForm {
        display: flex;
        align-items: baseline;
        text-align: center;
    }
    .inp_convert {
        display: flex;
        flex-direction: column;
        padding: 0 10px;
    }
    .inp_convert div p {
        position: relative;
        display: inline-block;
    }
    .inp_convert.button-convert p::before {
        color: #fff;
        font-size: 14px;
        left: 5px;
        position: absolute;
        top: 8px;
        content: '\f104';
        display: inline-block;
        font-family: "FontAwesome";
        z-index: 1;
    }
    .inp_convert.button-convert p::after {
        color: #fff;
        font-size: 14px;
        position: absolute;
        right: 5px;
        top: 8px;
        content: '\f105';
        display: inline-block;
        font-family: "FontAwesome";
        z-index: 1;
    }

</style>
<div class="bonus-points-converter-box">
    <div class="bonus-points-converter-box-title">
        <h3>{$smarty.const.BONUS_POINTS_CONVERTER}</h3>
        <span>{$smarty.const.BONUS_POINTS_CONVERTER_INTRO_TEXT} {if $customerBonusPoints > 0}{sprintf($smarty.const.TEXT_BONUS_POINTS, $customerBonusPoints)}{else}&nbsp;{/if}</span>
    </div>
    <div class="w-form-convert">
        {Html::beginForm('', 'post', ['id' => 'bonusPointsForm'])}
            {Html::input('hidden', 'bonusPointsRate', $coefficient)}
            {Html::input('hidden', 'customerBonusPointsValue', $customerBonusPoints, ['id' => 'customerBonusPointsValue'])}
            <div class="inp_convert">
                <span>{$smarty.const.NAME_BONUS_POINTS}</span>
                {Html::input('text', 'bonusPoints', $customerBonusPoints, ['id' => 'customerBonusPoints', 'class' => 'convertInput', 'pattern' => '\d*'])}
                <span id="bonusPointsWarning" class="warningText" style="display: none;">{sprintf($smarty.const.TRANSFER_BONUS_POINTS_WARNING, $customerBonusPoints)}</span>
            </div>
            <div class="inp_convert button-convert">
                <span>&nbsp;</span>
                <div>
                    <p>
                        {Html::button($smarty.const.TEXT_CONVERT, ['id' => 'convertButton', 'class' => 'convert btn btn-1'])}
                    </p>
                </div>
                <span>{$rate1bonuses} {$smarty.const.NAME_BONUS_POINTS} = {$currencies->addCurrencyIcon($rate1currency, false)}</span>
            </div>
            <div class="inp_convert">
                <span>{$smarty.const.TEXT_CURRENCY} ({$currentCurrency['title']})</span>
                {Html::input('text', 'bonusPointsConvert', '', ['id' => 'bonusPointsConvert', 'class' => 'convertInput', 'pattern' => '[0-9]+([\.][0-9]+)?'])}
            </div>
            {if $displayConvertButton}
            <div class="inp_convert">
                <span></span>
                {Html::button($smarty.const.TEXT_TRANSFER_SELECTED_BONUS_POINTS_TO_CREDIT_AMOUNT, ['id' => 'transferButton','class' => 'btn btn-1'])}
            </div>
            {/if}
        {Html::endForm()}
    </div>
    <script>
        tl([], function(){
            var rate = {$rate1currency};
            $('#customerBonusPoints').on('keyup', function () {
                if (/\D/g.test(this.value)) {
                    this.value = this.value.replace(/\D/g, '');
                }
                $('#bonusPointsConvert').val(Math.round($('#customerBonusPoints').val() * rate * 100) / 100);
                {*$('#bonusPointsConvert').val('');*}
            });
            $('#bonusPointsConvert').on('keyup', function () {
                if (/[^0-9.]/g.test(this.value)) {
                    this.value = this.value.replace(/[^0-9.]/g, '');
                }
                {*$('#customerBonusPoints').val('');*}
                $('#customerBonusPoints').val(Math.ceil($('#bonusPointsConvert').val() / rate));
            });
            $('#convertButton').on('click', function (e) {
                e.preventDefault();
                {*if ($('#customerBonusPoints').val() !== '') {
                    $('#bonusPointsConvert').val(Math.round($('#customerBonusPoints').val() * rate * 100) / 100);
                }else if ($('#bonusPointsConvert').val() !== '') {
                    $('#customerBonusPoints').val(Math.ceil($('#bonusPointsConvert').val() / rate));
                }*}
            });
            if ($('#customerBonusPoints').val() > 0) {
                {*$('#convertButton').trigger('click');*}
                $('#customerBonusPoints').trigger('keyup')
            }
            $('#transferButton').on('mouseenter', function (e) {
                var selectedPoints = parseInt($('#customerBonusPoints').val());
                var allPoints = {if is_int($customerBonusPoints)}{$customerBonusPoints};{else}0;{/if}
                if (selectedPoints > 0 && selectedPoints<= allPoints) {
                    $('#customerBonusPoints').css('border-color', '#00a858');
                    $('#bonusPointsWarning').hide();
                    return false;
                }
                $('#customerBonusPoints').css('border-color', '#ee4225');
                $('#bonusPointsWarning').show();
            });
            $('#transferButton').on('mouseleave', function (e) {
                $('#customerBonusPoints').css('border-color', '');
                $('#bonusPointsWarning').hide();
            });
            $('#transferButton').on('click', function (e) {
                e.preventDefault()
                $.post("{\Yii::$app->urlManager->createUrl(['account/move-bonus-points-to-amount'])}", {
                    bonus: $('#customerBonusPoints').val(),
                    _csrf: $('meta[name="csrf-token"]').attr('content'),
                }
                , function (response) {
                    if (response.hasOwnProperty('result')) {
                        if (response.result === true) {
                            window.location.reload();
                            return false;
                        }
                        alertMessage(response.result);
                    }
                });
            });
        });
    </script>
</div>
