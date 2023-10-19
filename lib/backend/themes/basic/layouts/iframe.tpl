<!DOCTYPE html>
{use class="yii\helpers\Html"}
{use class="backend\components\TopLeftMenu"}
{use class="backend\components\TopRightMenu"}
{use class="backend\components\Navigation"}
{use class="backend\components\Breadcrumbs"}
{$this->beginPage()}
<html lang="{str_replace("_", "-", Yii::$app->language)}">
{$version = 20}
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	{Html::csrfMetaTags()}
	<title>{$this->title}</title>

	<link  href="{$app->view->theme->baseUrl}/css/icons.css" type="text/css" rel="stylesheet" />
	<link  href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css" rel="stylesheet"">
	{if defined('WL_ENABLED') && WL_ENABLED === true}
		<link  href="{$app->view->theme->baseUrl}/css/{$smarty.const.WL_COMPANY_STYLE}?{$version}" rel="stylesheet" type="text/css" />
	{/if}
	<link  href="{$app->request->baseUrl}/plugins/bootstrap/bootstrap.min.css" type="text/css" rel="stylesheet" />
	<link  href="{$app->request->baseUrl}/plugins/jquery-ui/jquery-ui.min.css" type="text/css" rel="stylesheet" />
	<link  href="{$app->request->baseUrl}/plugins/daterangepicker/daterangepicker.min.css" type="text/css" rel="stylesheet" />
	<link  href="{$app->request->baseUrl}/plugins/select2/select2.min.css" type="text/css" rel="stylesheet" />
	<link  href="{$app->request->baseUrl}/plugins/bootstrap-switch/bootstrap-switch.css" rel="stylesheet">
	<link  href="{$app->request->baseUrl}/plugins/datatables/datatables.min.css" type="text/css" rel="stylesheet" />
	<link  href="{$app->request->baseUrl}/plugins/scrolling-tabs/jquery.scrolling-tabs.min.css" type="text/css" rel="stylesheet" />
	<link  href="{$app->request->baseUrl}/plugins/bootstrap-colorpicker/bootstrap-colorpicker.min.css" type="text/css" rel="stylesheet" />
	<link  href="{$app->view->theme->baseUrl}/css/file-manager.css?{$version}" type="text/css" rel="stylesheet" />

	<link href="{$app->view->theme->baseUrl}/css/general.css?{$version}" rel="stylesheet" type="text/css" id="general-styles" />
	<script>
		(() => {
			const themeName = localStorage.getItem('theme') || 'themeLight';
			const css = {
				themeLight: ['theme-light', 'base', 'main', 'style', 'menus'],
				themeDark: ['theme-dark', 'base-dark', 'main-dark', 'style-dark', 'menus-dark']
			}
			css[themeName].forEach((cssFile) => {
				const link = document.createElement('link');
				link.href = '{$app->view->theme->baseUrl}/css/' + cssFile + '.css?{$version}';
				link.rel = 'stylesheet';
				link.type = 'text/css';
				const head = document.getElementsByTagName('head')[0];
				const generalCss = document.getElementById('general-styles')[0];
				head.insertBefore(link, generalCss)
			});
			window.addEventListener('load', () => {
				let element = document.getElementById(themeName);
				if (element) {
					element.classList.add('active');
				}
			});
		})()

		function setTheme(themeName){
			localStorage.setItem('theme', themeName)
			document.location.reload();
		}
	</script>
	<link href="{$app->view->theme->baseUrl}/css/migration.css?{$version}" type="text/css" rel="stylesheet" />
	<link href="{$app->view->theme->baseUrl}/css/responsive.css?{$version}" rel="stylesheet" type="text/css" />
	<script src="{$app->request->baseUrl}/plugins/jquery.min.js" type="text/javascript"></script>
	<script src="{Yii::$aliases['@web']}/index/load-languages-js" type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/bootstrap/bootstrap.min.js"  type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/lodash.compat.min.js" type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/jquery-ui/jquery-ui.min.js"  type="text/javascript"></script>
	{if {$smarty.const.WYSIWYG_EDITOR_POPUP_INLINE !=  'popup'} || {$app->controller->id=='design'} }
		<script src="{$app->request->baseUrl}/plugins/ckeditor/ckeditor.js" type="text/javascript"></script>
	{/if}
	<script src="{$app->request->baseUrl}/plugins/moment.min.js" type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/daterangepicker/daterangepicker.min.js"  type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/select2/select2.min.js"  type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/blockui/jquery.blockUI.min.js" type="text/javascript"></script>{* ? *}
	<script src="{$app->request->baseUrl}/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/bootstrap-switch/bootstrap-switch.js" type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/datatables/datatables.min.js"  type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/scrolling-tabs/jquery.scrolling-tabs.min.js"  type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/bootstrap-colorpicker/bootstrap-colorpicker.min.js"  type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/bootbox/bootbox.all.min.js" type="text/javascript"></script>
	<script src="{$app->request->baseUrl}/plugins/dropzone.min.js"   type="text/javascript"></script>
	<script src="{$app->view->theme->baseUrl}/js/file-manager.js?{$version}"   type="text/javascript"></script>

	<script type="text/javascript">
		var entryData = JSON.parse('{addslashes(json_encode(\backend\design\Data::$jsGlobalData))}');
	</script>
	<script src="{$app->view->theme->baseUrl}/js/general.js?{$version}" type="text/javascript"></script>

	{$this->head()}
	<script>
		$(document).ready(function() {
			"use strict";

			App.init(); // Init layout and core plugins
			Plugins.init(); // Init all plugins
			FormComponents.init(); // Init all form-specific plugins

			$(document).ajaxComplete(function(event, jqxhr, settings, thrownError) {
				if (jqxhr.status && jqxhr.status == 401) {
					$(document).trigger('unauthorized_access');
				}
			});

			if (typeof $.fn.dataTable === 'function' && $.fn.dataTable.versionCheck('1.10')) {
				$.fn.dataTable.ext.errMode = function(settings, helpPage, message) {
					if (typeof settings.jqXHR === 'object' && settings.jqXHR.status === 401) {
						if (window.console && console.log) {
							console.log(settings.jqXHR);
						}
					} else {
						alert(message)
					}
				};
			}

			$(document).on('unauthorized_access', function() {
				if ($('.unauthorized_access_message').length > 0) return;
				bootbox.dialog({
					className: 'unauthorized_access_message',
					title: "{$smarty.const.TEXT_SESSION_EXPIRED_TITLE|escape:'javascript'}",
					message: "{$smarty.const.TEXT_SESSION_EXPIRED_MESSAGE|escape:'javascript'}",
					buttons: {
						success: {
							label: "{$smarty.const.TEXT_SIGN_IN|escape:'javascript'}",
							callback: function() { window.location.href = window.location
									.href; }
						}
					}
				})
			});

			{if $app->controller->view->messageSystemStatusCheck|default:null != ''}
			bootbox.dialog({
				className: 'messageSystemStatusCheck',
				title: "{$smarty.const.TEXT_MESSAGE_SYSTEM_STATUS_CHECK_TITLE|escape:'javascript'}",
				message: "{$app->controller->view->messageSystemStatusCheck|escape:'javascript'}",
				buttons: {
					success: {
						label: "{$smarty.const.TEXT_OK|escape:'javascript'}",
					}
				}
			});
			{/if}

		});
	</script>

	<base href="{HTTP_SERVER}{$app->request->baseUrl}/">
</head>

<body class="context-{$this->context->id} iframe-layout">
{$this->beginBody()}
	<!-- Header -->
	

	<div id="container">
{$dayOfWeek = [$smarty.const.TEXT_SUNDAY, $smarty.const.TEXT_MONDAY, $smarty.const.TEXT_TUESDAY, $smarty.const.TEXT_WEDNESDAY, $smarty.const.TEXT_THURSDAY, $smarty.const.TEXT_FRIDAY, $smarty.const.TEXT_SATURDAY]}
{$monthNames = [$smarty.const.TEXT_JAN, $smarty.const.TEXT_FAB,	$smarty.const.TEXT_MAR,	$smarty.const.TEXT_APR,	$smarty.const.TEXT_MAY,	$smarty.const.TEXT_JUN,	$smarty.const.TEXT_JUL,	$smarty.const.TEXT_AUG,	$smarty.const.TEXT_SEP,	$smarty.const.TEXT_OCT,	$smarty.const.TEXT_NOV,	$smarty.const.TEXT_DEC]}
                <div id="content" style="margin-left: 0px;">
                    <div class="container" style="margin-top: 0px;">
											
                        <span id="messageStack">
                        {if \Yii::$app->controller->view->errorMessage != '' }
                            <div class="popup-box-wrap pop-mess">
                                <div class="around-pop-up"></div>
                                <div class="popup-box">
                                    <div class="pop-up-close pop-up-close-alert"></div>
                                    <div class="pop-up-content">
                                        <div class="popup-heading">{$smarty.const.TEXT_NOTIFIC}</div>
                                        <div class="popup-content pop-mess-cont pop-mess-cont-{\Yii::$app->controller->view->errorMessageType}">
                                            {\Yii::$app->controller->view->errorMessage}
                                        </div>  
                                    </div>    
                                        <div class="noti-btn">
                                            <div></div>
                                            <div><span class="btn btn-primary">{$smarty.const.TEXT_BTN_OK}</span></div>
                                        </div>
                                </div>  
                                        <script>
                                $('body').scrollTop(0);
                                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                                    $(this).parents('.pop-mess').remove();
                                });
                            </script>
                            </div>
                            
                        {/if}
                        </span>
                        {\Yii::$container->get('message_stack')->initFlash()->outputHead()}
                        <div class="content-container">
                        {$content}
                        </div>
                        
                    </div>
			<!-- /.container -->
		</div>
	</div>
<script type="text/javascript">
function updateDate ( )
{
	var currentDate = new Date ( );
	var monthNames = ["{$smarty.const.TEXT_JAN}", "{$smarty.const.TEXT_FAB}", "{$smarty.const.TEXT_MAR}", "{$smarty.const.TEXT_APR}", "{$smarty.const.TEXT_MAY}", "{$smarty.const.TEXT_JUN}",
		"{$smarty.const.TEXT_JUL}", "{$smarty.const.TEXT_AUG}", "{$smarty.const.TEXT_SEP}", "{$smarty.const.TEXT_OCT}", "{$smarty.const.TEXT_NOV}", "{$smarty.const.TEXT_DEC}"
	];
	var dayOfWeek = ["{$smarty.const.TEXT_SUNDAY}", "{$smarty.const.TEXT_MONDAY}", "{$smarty.const.TEXT_TUESDAY}", "{$smarty.const.TEXT_WEDNESDAY}", "{$smarty.const.TEXT_THURSDAY}", "{$smarty.const.TEXT_FRIDAY}", "{$smarty.const.TEXT_SATURDAY}"];
	var currentDay = dayOfWeek[currentDate.getDay()];
	var currentDateW = currentDate.getDate();
	var numberMonth = currentDate.getMonth();
	var currentMonth = monthNames[numberMonth];
	var currentYear = currentDate.getFullYear();

	// Compose the string for display
	var currentDateString = currentDay + "<br>" + currentDateW + " " + currentMonth + ", " + currentYear;
	$("#date").html(currentDateString);
	$("#date-1").html(currentDateString);
}


function popupEditor (form, field) {
  bootbox.dialog({ message: '<iframe src="{$app->urlManager->createUrl(['popups/editor','s'=>(float)microtime()])}&form='+form+'&field='+field+'" width="900px" height="620px" style="border:0"/>' });
}
bootbox.setDefaults( { size:'large', onEscape:true, backdrop:true });


$(document).ready(function()
{
setInterval('updateDate()', 1000);

{if {$smarty.const.WYSIWYG_EDITOR_POPUP_INLINE ==  'popup'}}
  $('.ckeditor').each(function() {
    $(this).before('<a class="icons popUp popup-editor" href="javascript:void(0);" onclick="popupEditor(\'' + this.form.name + '\', \'' + this.name + '\')">{$smarty.const.TEXT_OPEN_WYSIWYG_EDITOR}</a>');
  });
{/if}

})
</script>
{$this->endBody()}
</body>
</html>
{$this->endPage()}