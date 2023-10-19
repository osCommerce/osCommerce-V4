<!DOCTYPE html>
<html lang="{str_replace("_", "-", Yii::$app->language)}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>{$this->title}</title>
	<link rel="stylesheet" href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css">
	<link href="{$app->view->theme->baseUrl}/css/login.css?3" rel="stylesheet" type="text/css" />
	<link href="{$app->view->theme->baseUrl}/css/superadmin.css?3" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery.min.js"></script>
	<script type="text/javascript" src="{$app->request->baseUrl}/plugins/validation/jquery.validate.min.js"></script>
	<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/login.js?3"></script>
</head>
<body>
{strip}
{use class="yii\helpers\Html"}
{use class="yii\captcha\Captcha"}
<div class="login">
	<div class="content-login">
	<!-- Logo -->
	<div class="logo">
		{if ((defined('WL_ENABLED') && WL_ENABLED === true) && (defined('WL_COMPANY_LOGO') && WL_COMPANY_LOGO != ''))}
			<img src="{$app->view->theme->baseUrl}/img/logo-powerful-commerce-white.png" alt="Powerful">
			<div class="subLogo">SUPERADMIN AREA</div>
		{else}
			{include './login-logo.tpl'}
		{/if}
	</div>
	<!-- /Logo -->

	<!-- Login Box -->
	<div class="box">
            {if ($action != 'restore')}
		<div class="content login-content">
			<!-- Login Formular -->
                        {Html::beginForm($app->urlManager->createUrl(["login", 'action' => 'process']), 'post', ['class' => 'form-vertical login-form'])}
				<!-- Title -->
				<h3 class="form-title">{$smarty.const.TEXT_SIGN_IN_ACCOUNT}</h3>

				<!-- Error Message -->
				<div class="alert fade in alert-danger" style="display: none;">
					<i class="icon-remove close" data-dismiss="alert"></i>
					{$smarty.const.TEXT_ENTER_PASSWORD}
				</div>

{if \Yii::$app->controller->errorMessage != '' }
				<div class="alert fade in alert-danger">
								<i class="icon-remove close" data-dismiss="alert"></i>
								{\Yii::$app->controller->errorMessage}
				</div>
{/if}

				<!-- Input Fields -->
                            {if ($action != 'otp')}
				<div class="form-group">
					<!--<label for="username">E-Mail Address:</label>-->
					<div class="input-icon">
						<i class="icon-user"></i>
                                                <input type="text" name="email_address" class="form-control" placeholder="{$smarty.const.ENTRY_EMAIL_ADDRESS}" autofocus="autofocus" autocomplete="off" data-rule-required="true" data-msg-required="{$smarty.const.TEXT_ENTER_YOUR_PASSWORD}" />
					</div>
				</div>
                            {else}
                                <input type="hidden" name="action" value="{$action}" />
                                <input type="hidden" name="email_address" value="{$email}" />
                            {/if}
                            {if (!defined('ADMIN_LOGIN_OTP_ENABLE') OR (ADMIN_LOGIN_OTP_ENABLE != 'True') OR {$action == 'otp'})}
				<div class="form-group">
					<!--<label for="password">Password:</label>-->
					<div class="input-icon">
						<i class="icon-lock"></i>
						<input type="password" name="password" class="form-control" placeholder="{$smarty.const.TEXT_PASSWORD}" autocomplete="off" data-rule-required="true" data-msg-required="{$smarty.const.TEXT_ENTER_PASSWORD}" />
					</div>
				</div>
                            {/if}
{if $loginModel->captha_enabled == 'recaptha'}
    {$loginModel->captcha_widget}
{/if}
{if $loginModel->captha_enabled == 'captha'}
                                <div class="form-group">
                                    {Captcha::widget(['model' => $loginModel, 'attribute' => 'captcha'])}
                                    {*Captcha::widget(['name' => 'captcha'])*}
                                </div>
{/if}
				<!-- /Input Fields -->

				<!-- Form Actions -->
				<div class="form-actions">
					<button type="submit" class="submit btn btn-primary">
						{$smarty.const.TEXT_SIGN_IN} <i class="icon-angle-right"></i>
					</button>
				</div>
                            {if ($action == 'otp')}
                                <div class="form-actions">
                                    <a class="btn" href="{Yii::$app->urlManager->createUrl('login')}">{$smarty.const.IMAGE_BACK}</a>
                                </div>
                            {/if}
                    {if ((!defined('ADMIN_LOGIN_OTP_ENABLE') OR (ADMIN_LOGIN_OTP_ENABLE != 'True')) AND (\common\models\AdminPasswordForgotLog::isBlocked() != true))}
			<a href="{Yii::$app->urlManager->createUrl(['login', 'action' => 'restore'])}#restore" class="forgot-password-link">{$smarty.const.TEXT_FORGOT_PASSWORD}</a>
                    {/if}
			{Html::endForm()}
			<!-- /Login Formular -->
		</div> <!-- /.content -->
            {/if}
            {if ((!defined('ADMIN_LOGIN_OTP_ENABLE') OR (ADMIN_LOGIN_OTP_ENABLE != 'True')) AND ($action == 'restore'))}
		<!-- Forgot Password Form -->
		<div class="inner-box">
			<div class="content forgot-password-content">
				<h3 class="form-title">{$smarty.const.TEXT_RESTORE_PASSWORD}</h3>
				<!-- Close Button -->
				{*<i class="icon-remove close hide-default"></i>*}

				<!-- Forgot Password Formular -->
				<form class="form-vertical forgot-password-form hide-default" action="{$app->urlManager->createUrl("password_forgotten")}?action=process" method="post">
					<!-- Input Fields -->
{foreach $passwordResetFileds as $passwordResetFiled}
    {if $passwordResetFiled == 'firstname'}
					<div class="form-group">
						<div class="input-icon">
							<i class="icon-user"></i>
							<input type="text" name="firstname" class="form-control" placeholder="{$smarty.const.TEXT_ENTER_FIRSTNAME}" data-rule-required="true" data-msg-required="{$smarty.const.TEXT_ENTER_YOUR_FIRSTNAME}" />
						</div>
					</div>
    {/if}
    {if $passwordResetFiled == 'lastname'}
					<div class="form-group">
						<div class="input-icon">
							<i class="icon-user"></i>
							<input type="text" name="lastname" class="form-control" placeholder="{$smarty.const.TEXT_ENTER_LASTNAME}" data-rule-required="true" data-msg-required="{$smarty.const.TEXT_ENTER_YOUR_LASTNAME}" />
						</div>
					</div>
    {/if}
    {if $passwordResetFiled == 'phone'}
					<div class="form-group">
						<div class="input-icon">
							<i class="icon-phone"></i>
							<input type="text" name="phone" class="form-control" placeholder="{$smarty.const.TEXT_ENTER_PHONE}" data-rule-required="true" data-msg-required="{$smarty.const.TEXT_ENTER_YOUR_PHONE}" />
						</div>
					</div>
    {/if}
    {if $passwordResetFiled == 'username'}
					<div class="form-group">
						<div class="input-icon">
							<i class="icon-user"></i>
							<input type="text" name="username" class="form-control" placeholder="{$smarty.const.TEXT_INFO_USERNAME}" data-rule-required="true" data-msg-required="{$smarty.const.TEXT_ENTER_YOUR_USERNAME}" />
						</div>
					</div>
    {/if}
{/foreach}
					<div class="form-group">
						<div class="input-icon">
							<i class="icon-envelope"></i>
							<input type="text" name="email_address" class="form-control" placeholder="{$smarty.const.TEXT_ENTER_EMAIL_ADDRESS}" data-rule-required="true" data-rule-email="true" data-msg-required="{$smarty.const.TEXT_ENTER_YOUR_EMAIL}" />
						</div>
					</div>
{if $loginModel->captha_enabled == 'recaptha'}
    {$loginModel->captcha_widget}
{/if}
{if $loginModel->captha_enabled == 'captha'}
                                <div class="form-group">
                                    {Captcha::widget(['model' => $loginModel, 'attribute' => 'captcha'])}
                                    {*Captcha::widget(['name' => 'captcha'])*}
                                </div>
{/if}
					<!-- /Input Fields -->

					<button type="submit" class="submit btn btn-default btn-block btn-primary">
						{$smarty.const.TEXT_RESET_PASSWORD}
					</button>
				</form>
				<!-- /Forgot Password Formular -->

				<!-- Shows up if reset-button was clicked -->
				<div class="forgot-password-done hide-default">
					<i class="icon-ok success-icon"></i>
					<i class="icon-remove danger-icon"></i>
					<span class="forgot-password-success">{$smarty.const.TEXT_FORGOTTEN_SUCCESS}</span>
					<span class="forgot-password-fail">{$smarty.const.TEXT_FORGOTTEN_ERROR}</span>
				</div>

				<a href="{Yii::$app->urlManager->createUrl('login')}" class="login-link">{$smarty.const.TEXT_SIGN_IN_ACCOUNT}</a>
			</div> <!-- /.content -->
		</div>
		<!-- /Forgot Password Form -->
            {/if}
	</div>
	<!-- /Login Box -->
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
			  {$smarty.const.TEXT_COPYRIGHT} {$smarty.now|date_format:"%Y"} <a target="_blank" href="https://oscommerce.com">{$smarty.const.TEXT_COPYRIGHT_HOLBI}</a>
			  {$smarty.const.TEXT_FOOTER_BOTTOM}<br>

			  {$smarty.const.TEXT_FOOTER_COPYRIGHT} {$smarty.now|date_format:"%Y"} {$smarty.const.TEXT_COPYRIGHT_HOLBI}<br>{$smarty.const.TEXT_SUB_COPY}
			  </div>
	      {/if}

	</div>
	<!-- /Footer -->
</div>
<script>
	$(function(){
        Login.init();

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
		
		var bgArray = ['bg1.jpg', 'bg2.jpg', 'bg3.jpg', 'bg4.jpg', 'bg5.jpg', 'bg6.jpg'];
		var bg = '{$app->view->theme->baseUrl}/img/' + bgArray[Math.floor(Math.random() * bgArray.length)];
		$('body').css('background-image', 'url(' + bg + ')');
	})
</script>
{/strip}
</body>
</html>
