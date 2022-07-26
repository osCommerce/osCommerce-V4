{use class="yii\helpers\Html"}
{if $smarty.const.ADMIN_PASSWORD_STRONG eq 'ULNS'}
    {assign var=titleDataPattern value=sprintf($smarty.const.ENTRY_PASSWORD_ULNS_ERROR, $smarty.const.ADMIN_PASSWORD_MIN_LENGTH)}
    {assign var=passDataPattern value='(?=.*\d)(?=.*\W+)(?=.*[a-z])(?=.*[A-Z]).{'|cat:$smarty.const.ADMIN_PASSWORD_MIN_LENGTH|cat:',}'}
{elseif $smarty.const.ADMIN_PASSWORD_STRONG eq 'ULN'}
    {assign var=titleDataPattern value=sprintf($smarty.const.ENTRY_PASSWORD_ULN_ERROR, $smarty.const.ADMIN_PASSWORD_MIN_LENGTH)}
    {assign var=passDataPattern value='(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{'|cat:$smarty.const.ADMIN_PASSWORD_MIN_LENGTH|cat:',}'}
{else}
    {assign var=titleDataPattern value=sprintf($smarty.const.ENTRY_PASSWORD_ERROR, $smarty.const.ADMIN_PASSWORD_MIN_LENGTH)}
    {assign var=passDataPattern value='.{'|cat:$smarty.const.ADMIN_PASSWORD_MIN_LENGTH|cat:'}'}
{/if}
{Html::beginForm('admin/adminaccount', 'post', ['id' => 'save_account_form'])}
{Html::input('hidden', "admin_id", $myAccount['admin_id'])}
{Html::input('hidden', "popupname", 'password')}
{Html::input('hidden', "password_confirmation", $password_confirmation)}
<table cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td class="dataTableContent"><a href="#" class="generate_password">{$smarty.const.TEXT_GENERATE_PASSWORD}</a></td>
    </tr>
    <tr>
        <td class="dataTableContent">{$smarty.const.TEXT_INFO_PASSWORD_NEW}</td>
        <td class="dataTableContent">{Html::passwordInput('admin_password', '', ['class' => 'form-control show-password', 'data-pattern' => $passDataPattern, 'data-required' => $titleDataPattern, 'autocomplete' => 'off'])}</td>
    </tr>
    <tr>
        <td class="dataTableContent">{$smarty.const.TEXT_INFO_PASSWORD_CONFIRM}</td>
        <td class="dataTableContent">{Html::passwordInput('admin_password_confirm', '', ['class' => 'form-control show-password', 'data-pattern' => $passDataPattern, 'data-required' => $titleDataPattern, 'autocomplete' => 'off'])}</td>
    </tr>
</table>
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_UPDATE}</button></div>
</div>
{Html::endForm()}
<script>
$(function(){ 
    $('input[type="password"]').showPassword();
    
    $('.generate_password').on('click', function(){
            $.get('{$app->urlManager->createUrl('adminaccount/generate-password')}', function(data){
                $('.show-password').val(data);
                $('.show-password').trigger('keyup');
                if($('.show-password').attr('type') == 'password'){
                  $('.eye-password').click();
                }
            }, 'json')
            return false;
    });
    
    var form = $('#save_account_form');
    $('input', form).validate();
    form.on('submit', function(){
        if ($('.required-error', form).length === 0){
            return saveAccount();
        }
        return false;
    });
});
</script>