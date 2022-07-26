{strip}<!DOCTYPE html>
<html lang="{str_replace("_", "-", Yii::$app->language)}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Reset password | Trueloaded Admin</title>
<link rel="stylesheet" href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css">
<link href="{$app->view->theme->baseUrl}/css/login.css?1" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/libs/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/validation/jquery.validate.js"></script>
<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/jquery.scrolling-tabs.js"></script>
<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/main.js"></script>
<style type="text/css">
.required-message {
    color:#ed4224;
    font-size:14px;
    padding:0 3px;
    margin:0 0
}
</style>
</head>
<body>

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
<div class="login">
	<div class="content-login">
	<!-- Logo -->
	<div class="logo">
		{if ((defined('WL_ENABLED') && WL_ENABLED === true) && (defined('WL_COMPANY_LOGO') && WL_COMPANY_LOGO != ''))}
			<img src="{$app->view->theme->baseUrl}/img/{$smarty.const.WL_COMPANY_LOGO}" alt="{$smarty.const.WL_COMPANY_NAME}" />
		{else}
			{include 'login-logo.tpl'}
		{/if}
	</div>
	<!-- /Logo -->

	<div class="box">
		<div class="content">
			<div class="middle-form">
{if !empty($message_account_password)}
    <h3 class="form-title">{$message_account_password}</h3>
{else}
{Html::beginForm($account_password_action, 'post', ['id' => 'frmAccountPassword', 'class' => 'form-vertical login-form'])}
    <h3 class="form-title">{$smarty.const.HEADING_TITLE}</h3>
    
    <div class="form-group">
        <label for="pass-new">{field_label const="ENTRY_PASSWORD_NEW" required_text="*"}</label>
        <input type="password" name="password_new" id="pass-new" class="form-control password" data-pattern="{$passDataPattern}" data-required="{$titleDataPattern}" autocomplete="new-password">
    </div>
    <div class="form-group">
        <label for="pass-confirm">{field_label const="ENTRY_PASSWORD_CONFIRMATION" required_text="*"}</label>
        <input type="password" name="password_confirmation" id="pass-confirm" class="form-control" data-required="{$smarty.const.ENTRY_PASSWORD_ERROR_NOT_MATCHING}" data-confirmation=".password" autocomplete="new-password">
    </div>
    <input type="hidden" name="token" value="{$token}">
   <div class="form-actions"><button type="submit" class="submit btn btn-primary">{$smarty.const.IMAGE_BUTTON_UPDATE}</button></div>
{Html::endForm()}
{/if}
  </div>
		</div> <!-- /.content -->

	</div>
	</div>

	<!-- Footer -->
	<div class="footer-login">
		<ul class="links">
            {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                {if ((defined('WL_CONTACT_URL') && WL_CONTACT_URL === true) &&
                (defined('WL_CONTACT_TEXT') && WL_CONTACT_TEXT != '') &&
                (defined('WL_CONTACT_WWW') && WL_CONTACT_WWW != ''))}
					<li><a href="{$smarty.const.WL_CONTACT_WWW}" target="_blank">{$smarty.const.WL_CONTACT_TEXT}</a></li>
                {/if}
            {else}
				<li><i class="icon-envelope"></i> <a href="https://www.oscommerce.com/contact" target="_blank">{$smarty.const.TEXT_HEADER_CONTACT_US}</a></li>
            {/if}
            {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                {if ((defined('WL_SERVICES_URL') && WL_SERVICES_URL === true) &&
                (defined('WL_SERVICES_TEXT') && WL_SERVICES_TEXT != '') &&
                (defined('WL_SERVICES_WWW') && WL_SERVICES_WWW != ''))}
					<li><a href="{$smarty.const.WL_SERVICES_WWW}" target="_blank">{$smarty.const.WL_SERVICES_TEXT}</a></li>
                {/if}
            {else}
				<li><i class="icon-shopping-cart"></i> <a href="https://www.holbi.co.uk/ecommerce-development" target="_blank">{$smarty.const.TEXT_ECOMMERCE_DEVELOPMENT}</a></li>
            {/if}
            {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                {if ((defined('WL_SUPPORT_URL') && WL_SUPPORT_URL === true) &&
                (defined('WL_SUPPORT_TEXT') && WL_SUPPORT_TEXT != '') &&
                (defined('WL_SUPPORT_WWW') && WL_SUPPORT_WWW != ''))}
					<li><a href="{$smarty.const.WL_SUPPORT_WWW}" target="_blank">{$smarty.const.WL_SUPPORT_TEXT}</a></li>
                {/if}
            {else}
				<li><i class="icon-comments"></i> <a href="https://forums.oscommerce.com/" target="_blank">{$smarty.const.TEXT_SUPPORT}</a></li>
            {/if}
		</ul>

	      {if ((defined('WL_ENABLED') && WL_ENABLED === true) && 
	           (defined('WL_COMPANY_NAME') && WL_COMPANY_NAME != ''))}

	        Copyright &copy; {$smarty.now|date_format:"%Y"} <a target="_blank" href="https://oscommerce.com">{$smarty.const.WL_COMPANY_NAME}</a>. All rights reserved.

	      {else}


			  <div class="copuright">
			  {$smarty.const.TEXT_COPYRIGHT} {$smarty.now|date_format:"%Y"} <a target="_blank" href="https://www.holbi.co.uk">{$smarty.const.TEXT_COPYRIGHT_HOLBI}</a>
			  {$smarty.const.TEXT_FOOTER_BOTTOM}<br>

			  {$smarty.const.TEXT_FOOTER_COPYRIGHT} {$smarty.now|date_format:"%Y"} {$smarty.const.TEXT_COPYRIGHT_HOLBI}
			  </div>
	      {/if}

	</div>
	<!-- /Footer -->
</div>
<script>
    $(function(){
        $.fn.showPassword = function(){
            return this.each(function() {
                let $input = $(this);
                if ($input.hasClass('eye-applied')) {
                    return '';
                }
                $input.addClass('eye-applied');

                let $eye = $('<span class="eye-password"></span>');
                let $eyeWrap = $('<span class="eye-password-wrap"></span>');
                $eyeWrap.append($eye);
                $input.before($eyeWrap);
                $eye.on('click', function(){
                    if ($input.attr('type') === 'password') {
                        $eye.addClass('eye-password-showed');
                        $input.attr('type', 'text')
                    } else {
                        $eye.removeClass('eye-password-showed');
                        $input.attr('type', 'password')
                    }
                })
            })
        };
        $('input[type="password"]').showPassword();
        
        $('input[type="password"]').validate();
        
    });
</script>
</body>
</html>{/strip}
