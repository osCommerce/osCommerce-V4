{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}


{Info::addBoxToCss('switch')}
{$model = $manager->getCustomerContactForm()}

<div class="terms-box">
    {Html::activeCheckbox($model, 'terms', ['class' => 'terms-checkbox', 'label' => '', 'data-required' => TEXT_PLEASE_TERMS])}
    {$smarty.const.TEXT_TERMS_CONDITIONS}
</div>


<script>
    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
    ], function () {
        let textNo = '{$smarty.const.TEXT_NO}';
        let textYes = '{$smarty.const.TEXT_YES}';
        let settings = JSON.parse('{json_encode($settings[0])}');
        let box = $('#box-{$id}');
        let termsConditions = {if $model->terms} true {else} false {/if};

        let form = box.closest('form');
        let submitButton = $('button[type="submit"]', form);
        let hidingBox = $('.hiding-box');
        let checkbox = $(".terms-checkbox", box);

        switchHidingBox(termsConditions);
        checkbox.validate();

        if (settings.use_switcher) {
            checkbox.bootstrapSwitch({
                offText: textNo,
                onText: textYes,
                onSwitchChange: function (d, e) {
                    form.trigger('cart-change');
                    switchHidingBox(e)
                }
            });
        } else {
            checkbox.on('change', function (e) {
                form.trigger('cart-change');
                switchHidingBox(e.target.checked)
            });
        }

        function switchHidingBox(check) {
            if (check) {
                if (settings.hide_page) hidingBox.removeClass('hide-box');
                if (settings.hide_continue_button) {
                    $(window).trigger('disable-checkout-button', { name: 'terms', value: false})
                    submitButton.siblings('.add-buttons').removeClass('hide-box');
                    submitButton.siblings('.add-buttons').find('div.dis-area').remove();
                }
            } else {
                if (settings.hide_page) hidingBox.addClass('hide-box');
                if (settings.hide_continue_button) {
                    $(window).trigger('disable-checkout-button', { name: 'terms', value: true})
                    const $disArea = $('<div class="dis-area disabled-area" style="position: absolute;left:-2px;top:-2px; width: 102%; height: 102%; z-index: 702;"></div>');
                    submitButton.siblings('.add-buttons')
                        .addClass('hide-box')
                        .append($disArea);

                    $disArea.on('click', function(){
                        $('input, select').trigger('check')
                    })
                }
            }
        }
    })

</script>