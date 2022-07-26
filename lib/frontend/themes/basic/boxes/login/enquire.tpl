{use class="Yii"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
<div class="login-box">
    {if isset($settings['tabsManually']) && $settings['tabsManually']}
        <div class="login-box-heading">{$smarty.const.TEXT_ENQUIRES}</div>
    {/if}
    <div class="middle-form">
        
        {$messages_enquire}
        
        {Html::beginForm($action, 'post', [])}
        
        {Html::hiddenInput('scenario', $enquireModel->formName())}        
        <div class="col-left">
            <label for="email_address">{field_label const="TEXT_NAME" required_text="*"}</label>
            {Html::activeTextInput($enquireModel, 'name')}
        </div>
        <div class="col-right">
            <label for="">{$smarty.const.ENTRY_COMPANY}</label>
            {Html::activeTextInput($enquireModel, 'company')}
        </div>  
        <div class="col-left">
            <label for="email_address">{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
            {Html::activeInput('email', $enquireModel, 'email_address')}            
        </div>
        <div class="col-right">
            <label for="">{field_label const="ENTRY_TELEPHONE_NUMBER" required_text="*"}</label>
            {Html::activeTextInput($enquireModel, 'phone')}
        </div>
        <div class="">
            <label for="">{field_label const="TEXT_ENQUIRES" required_text="*"}</label>
            {Html::activeTextarea($enquireModel, 'content')}
        </div>
              
        
        <div class="center-buttons">
            <button class="btn-2" type="submit">{$smarty.const.SUBMIT}</button>
        </div>
        
        {Html::endForm()}
    </div>
</div>
<script>
tl([        
        '{Info::themeFile('/js/main.js')}',
        '{Info::themeFile('/js/candlestick.js')}',
        '{Info::themeFile('/js/bootstrap.min.js')}',
    ], function () {
        var box = $('#box-{$id}');
        $('.middle-form input', box).validate();
});

</script>