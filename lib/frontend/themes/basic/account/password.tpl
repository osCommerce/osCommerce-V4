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

<style type="text/css">
.preloader-holder {
    display: none;
}
.preloader-holder .preloader {
    flex-grow: 1;
    text-align: center;
}
.preloader:before {
    content: '\f110';
    -moz-animation: load8 1.5s infinite linear;
    -webkit-animation: load8 1.5s infinite linear;
    animation: load8 1.5s infinite linear;
    font-size: 40px;
    line-height: 40px;
    display: inline-block;
    overflow: hidden;
    width: 40px;
    height: 40px;
    font-family: FontAwesome;
    position: relative;
}
@keyframes load8 {
    0% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
    }
    100% {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
    }
}
</style>

<h1>{$smarty.const.HEADING_TITLE}</h1>

{$message_account_password}
<div class="middle-form">
{Html::beginForm($account_password_action, 'post', ['id' => 'frmAccountPassword'])}
    <div class="col-full">
        <label for="pass-current">{field_label const="ENTRY_PASSWORD_CURRENT" required_text="*"}</label>
        <input type="password" name="password_current" id="pass-current" data-pattern="{$passDataPattern}" data-required="{$titleDataPattern}" autocomplete="off">
    </div>
    <div class="col-full">
        <label for="pass-new">{field_label const="ENTRY_PASSWORD_NEW" required_text="*"}</label>
        <input type="password" name="password_new" id="pass-new" data-pattern="{$re1}{$smarty.const.ENTRY_PASSWORD_MIN_LENGTH}{$re2}" data-required="{$smarty.const.ENTRY_PASSWORD_ERROR}" autocomplete="off">
    </div>
    <div class="col-full">
        <label for="pass-confirm">{field_label const="ENTRY_PASSWORD_CONFIRMATION" required_text="*"}</label>
        <input type="password" name="password_confirmation" id="pass-confirm" data-required="{$smarty.const.ENTRY_PASSWORD_ERROR_NOT_MATCHING}" data-confirmation="#pass-new" autocomplete="off">
    </div>
    <div class="required requiredM">
        {$smarty.const.FORM_REQUIRED_INFORMATION}
    </div>
   <div class="center-buttons">
       <button class="btn-2" type="submit"><span class="button">{$smarty.const.IMAGE_BUTTON_UPDATE}</span></button>
       <div class="preloader-holder"><div class="preloader"></div></div>
   </div>
{Html::endForm()}
  </div>
   <div class="buttonBox buttons buttonedit">
       <a href="{$link_back_href}" class="btn">{$smarty.const.IMAGE_BUTTON_BACK}</a>
   </div>
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}' , function(){
    $('#frmAccountPassword input').validate();
  });
</script>