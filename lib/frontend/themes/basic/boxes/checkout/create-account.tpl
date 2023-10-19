{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}

{Info::addBoxToCss('info')}
{Info::addBoxToCss('form')}
{Info::addBoxToCss('pass-strength')}
{Info::addBoxToCss('info-popup')}
{Info::addBoxToCss('switch')}

<div class="create-account-switcher">
    {if $manager->get('guest')}
        {Html::checkbox(Html::getInputName($model, 'opc_temp_account'), $model->opc_temp_account, ['label' => '' ])}
    {else}
	{Html::hiddenInput(Html::getInputName($model, 'opc_temp_account'), 1, ['label' => '' ])}
    {/if}
</div>

<div class="heading-2">{$smarty.const.CREATE_ACCOUNT_AND_TRACK_ORDERS}</div>

<div class="columns form-inputs create-account"{if $manager->get('guest') && !$manager->has('account')} style="display: none" {/if}>
    <div class="col-2">

        {if isset($messages_registration)}
            {$messages_registration}
        {/if}

        {assign var=re1 value='.{'}
        {assign var=re2 value='}'}
{if $smarty.const.PASSWORD_STRONG_REQUIRED eq 'ULNS'}
    {assign var=titleDataPattern value=sprintf($smarty.const.ENTRY_PASSWORD_ULNS_ERROR, $smarty.const.ENTRY_PASSWORD_MIN_LENGTH)}
    {assign var=passDataPattern value='(?=.*\d)(?=.*\W+)(?=.*[a-z])(?=.*[A-Z]).{'|cat:$smarty.const.ENTRY_PASSWORD_MIN_LENGTH|cat:',}'}
{elseif $smarty.const.PASSWORD_STRONG_REQUIRED eq 'ULN'}
    {assign var=titleDataPattern value=sprintf($smarty.const.ENTRY_PASSWORD_ULN_ERROR, $smarty.const.ENTRY_PASSWORD_MIN_LENGTH)}
    {assign var=passDataPattern value='(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{'|cat:$smarty.const.ENTRY_PASSWORD_MIN_LENGTH|cat:',}'}
{else}
    {assign var=titleDataPattern value=sprintf($smarty.const.ENTRY_PASSWORD_ERROR, $smarty.const.ENTRY_PASSWORD_MIN_LENGTH)}
    {assign var=passDataPattern value='.{'|cat:$smarty.const.ENTRY_PASSWORD_MIN_LENGTH|cat:'}'}
{/if}

        <div class="password-row">
            <div class="col-full">
                <label for="{$model->formName()}-password" class="password-info">
                    <span>{field_label const="PASSWORD" required_text="*"}</span>
                </label>
                {Html::activePasswordInput($model, 'password', ['class' => "password", 'autocomplete' => "off", 'data-pattern' => "{$passDataPattern}", 'data-required' => "{$titleDataPattern}"])}
            </div>
            <div class="col-full">
                <label for="confirmation"><span>{field_label const="PASSWORD_CONFIRMATION" required_text="*"}</span></label>
                {Html::activePasswordInput($model, 'confirmation', ['class' => "confirmation", 'autocomplete' => "off", 'data-required' => "{$smarty.const.ENTRY_PASSWORD_ERROR_NOT_MATCHING}", 'data-confirmation' => "#checkout-password"])}
            </div>
        </div>



        {if \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') && defined('ENABLE_CUSTOMERS_NEWSLETTER') && ENABLE_CUSTOMERS_NEWSLETTER == 'true' }
            <div class="col-full newsletter-switcher">
                <label class="slim">
                    {Html::activeCheckbox($model, 'newsletter', ['class' => 'candlestick newsletter', 'label' => {$smarty.const.RECEIVE_REGULAR_OFFERS}, 'uncheck' => 0 ])}
                    <span class="checkbox-span"></span>
                </label>
            </div>

            <div class="col-full regular_offers_box" style="display: none;">
                <label for="{$model->formName()}-regular_offers">{$smarty.const.RECEIVE_REGULAR_OFFERS_PERIOD}</label>
                {Html::activeDropDownList($model, 'regular_offers', $model->getRegularOfferList())}
            </div>
        {/if}
    </div>
    <div class="col-2">
        <div class="account-benefits">{$smarty.const.BENEFITS_FROM_CREATING}</div>

    </div>
    {$manager->captcha_widget}
</div>

<script type="text/javascript">
    tl([
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/password-strength.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
        '{Info::themeFile('/js/bootstrap.min.js')}'
    ], function () {
        var box = $('#box-{$id}');
        var fields = $('input, select', box);
        fields.validate();
        
        function skipCheck(){
            {if $manager->get('guest')}
            if ($(".create-account-switcher input:checkbox", box).prop('checked')){
                $('.confirmation, .password', box).removeClass('skip-validation');
            } else {
                $('.confirmation, .password', box).addClass('skip-validation');
                $('.confirmation, .password', box).removeClass('required-error');
                $('.required-message-wrap',box).remove();
            }
            {/if}
        }
        skipCheck();

        $('.password', box).passStrength({
            shortPassText: "{$smarty.const.TEXT_TOO_SHORT|strip}",
            badPassText: "{$smarty.const.TEXT_WEAK|strip}",
            goodPassText: "{$smarty.const.TEXT_GOOD|strip}",
            strongPassText: "{$smarty.const.TEXT_STRONG|strip}",
            samePasswordText: "{$smarty.const.TEXT_USERNAME_PASSWORD_IDENTICAL|strip}",
            userid: "#firstname"
        });

        $('.confirmation, .password', box).on('keyup', function () {
            var confirmation = $('.confirmation', box);
            if (confirmation.val() !== $('.password', box).val() && confirmation.val()) {
                confirmation.prev(".pass-strength").remove();
                confirmation.before('<span class="pass-strength pass-no-match"><span>{$smarty.const.TEXT_NO_MATCH|strip}</span></span>');
            } else if (confirmation.val() === '') {
                confirmation.prev(".pass-strength").remove();
            } else {
                confirmation.prev(".pass-strength").remove();
                confirmation.before('<span class="pass-strength pass-match"><span>{$smarty.const.TEXT_MATCH|strip}</span></span>');
            }
        });

        $(".newsletter", box).bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function (d, e) {
                if(e){
                    $('.regular_offers_box', box).show();
                }else{
                    $('.regular_offers_box', box).hide();
                }
            }
        });
        $(".create-account-switcher input:checkbox", box).bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function (d, e) {
                if(e){
                    $('.create-account', box).show();
                }else{
                    $('.create-account', box).hide();
                }
                skipCheck();
            }
        });
    })

</script>
