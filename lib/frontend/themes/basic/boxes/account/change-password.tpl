{use class="frontend\design\Info"}
{assign var=re1 value='.{'}
{assign var=re2 value='}'}
{use class = "yii\helpers\Html"}
{\frontend\design\Info::addBoxToCss('info')}
{\frontend\design\Info::addBoxToCss('form')}
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

{Html::beginForm('account/password', 'post', ['id' => 'frmAccountPassword'])}
<div class="middle-form">
    <div class="messages"></div>
    <div class="col-full">
        <label for="pass-current">{field_label const="ENTRY_PASSWORD_CURRENT" required_text="*"}</label>
        <input type="password" name="password_current" id="pass-current" data-pattern="{$re1}1{$re2}" data-required="{sprintf($smarty.const.ENTRY_PASSWORD_ERROR,$smarty.const.ENTRY_PASSWORD_MIN_LENGTH)}" autocomplete="off">
    </div>
    <div class="col-full">
        <label for="pass-new">{field_label const="ENTRY_PASSWORD_NEW" required_text="*"}</label>
        <input type="password" name="password_new" id="pass-new" data-pattern="{$passDataPattern}" data-required="{$titleDataPattern}" autocomplete="off">
    </div>
    <div class="col-full">
        <label for="pass-confirm">{field_label const="ENTRY_PASSWORD_CONFIRMATION" required_text="*"}</label>
        <input type="password" name="password_confirmation" id="pass-confirm" data-required="{$smarty.const.ENTRY_PASSWORD_ERROR_NOT_MATCHING}" data-confirmation="#pass-new" autocomplete="off">
    </div>
    <div class="required requiredM">
        {$smarty.const.FORM_REQUIRED_INFORMATION}
    </div>
    <div class="center-buttons">
        <button class="btn-2" type="submit">{$smarty.const.IMAGE_BUTTON_UPDATE}</button>
    </div>
</div>
{Html::endForm()}

<script>
    tl([
        '{Info::themeFile('/js/main.js')}'
    ], function(){
        var form = $('#box-{$id} form');
        $('input', form).validate();
        form.on('submit', function(){
            if ($('.required-error', form).length === 0){
                $.post(form.attr('action'), form.serialize(), function(data){
                    var messages = '';
                    $.each(data.messages, function(key, val){
                        messages += '<div class="message '+val['type']+'">'+val.text+'</div>';
                        if (val['.type'] === 'success') {
                            setTimeout(function(){
                                $('.pop-up-close').trigger('click')
                            }, 1000)
                        }
                    });
                    $('.messages', form).html(messages)
                }, 'json')
            }
            return false;
        });
    });
</script>