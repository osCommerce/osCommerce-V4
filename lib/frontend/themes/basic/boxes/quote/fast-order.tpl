{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
<div class="login-box">
    {if isset($settings['tabsManually']) && $settings['tabsManually']}
        <div class="login-box-heading">{$smarty.const.SIMPLE_ORDER}</div>
    {/if}
    <div class="middle-form">
        
        {$messages_fast_order}
        
        {assign var=re1 value='.{'}
        {assign var=re2 value='}'}
        
        {Html::beginForm($action, 'post', [])}
        
        {Html::hiddenInput('scenario', $fastModel->formName())}
        
        {if in_array(ACCOUNT_TELEPHONE, ['required_register', 'visible_register'])}
            <div class="col-full">
                <label for="{$fastModel->formName()}-telephone">{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>
                {if ACCOUNT_TELEPHONE == 'required_register'}
                    {Html::activeTextInput($fastModel, 'telephone', ['data-required' => "{sprintf($smarty.const.ENTRY_TELEPHONE_NUMBER_ERROR, $smarty.const.ENTRY_TELEPHONE_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_TELEPHONE_MIN_LENGTH}{$re2}"])}
                {else}
                    {Html::activeTextInput($fastModel, 'telephone')}                            
                {/if}
            </div>
        {/if}
        
        <div class="col-full">
            <label for="{$fastModel->formName()}-email_address">{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
            {Html::activeInput('email', $fastModel, 'email_address', ['data-required' => "{$smarty.const.EMAIL_REQUIRED}", 'data-pattern' => "email"])}            
        </div>

        {if in_array(ACCOUNT_FIRSTNAME, ['required_register', 'visible_register'])}
            <div class="col-full">
                <label for="{$fastModel->formName()}-firstname">
                    {field_label const="ENTRY_FIRST_NAME" required_text="*"}
                </label>
                {if ACCOUNT_TELEPHONE == 'required_register'}
                    {Html::activeTextInput($fastModel, 'firstname', ['data-required' => "{sprintf($smarty.const.ENTRY_FIRST_NAME_ERROR, $smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH)}", 'data-pattern' => "{$re1}{$smarty.const.ENTRY_FIRST_NAME_MIN_LENGTH}{$re2}"])}
                {else}
                    {Html::activeTextInput($fastModel, 'firstname')}                            
                {/if}                
            </div>
        {/if}
        
        <div class="col-full">
            <label for="{$fastModel->formName()}-content">{field_label const="TEXT_COMMENTS" required_text=""}</label>
            {Html::activeTextarea($fastModel, 'content')}                    
        </div>
        
        <div class="col-full privacy-row">
            <div class="terms-login">
                {Html::activeCheckbox($fastModel, 'terms', ['class' => 'terms-conditions', 'value' => '1', 'label' => '', 'checked' => ''])}{$smarty.const.TEXT_TERMS_CONDITIONS}
            </div>
        </div>
        
        <div class="center-buttons">
            <button class="btn-2 disabled-area" type="submit">{$smarty.const.SUBMIT}</button>
        </div>
        
        {Html::endForm()}
    </div>
</div>
<script>
tl([        
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/bootstrap.min.js')}',
        '{Info::themeFile('/js/bootstrap-switch.js')}',
    ], function () {
        var box = $('#box-{$id}');
        $('.middle-form input', box).validate();
        
        var disableButton = function(e){
            e.preventDefault();
            return false;
        };

        $('.disabled-area', box).on('click', disableButton);
        
        {\frontend\design\Info::addBoxToCss('switch')}
        $(".terms-conditions", box).bootstrapSwitch({
            offText: '{$smarty.const.TEXT_NO}',
            onText: '{$smarty.const.TEXT_YES}',
            onSwitchChange: function (d, e) {
                var form = $(this).closest('form');
                form.trigger('cart-change');
                if(e){
                    $('button[type="submit"]', form).removeClass('disabled-area').off('click', disableButton);
                }else{
                    $('button[type="submit"]', form).addClass('disabled-area').on('click', disableButton);
                }
            }
        });
});

</script>