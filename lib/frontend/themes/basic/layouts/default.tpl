{$this->beginPage()}<!DOCTYPE html>
{use class="yii\helpers\Html"}
{use class="frontend\design\IncludeTpl"}
{use class="Yii"}
{use class="frontend\design\Block"}
{use class="frontend\design\Css"}
{use class="frontend\design\Info"}
{use class="common\components\google\widgets\GoogleWidget"}
<html lang="{Yii::$app->language}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<base href="{$smarty.const.BASE_URL}">
	{Html::csrfMetaTags()}
	<title>{$this->title}</title>

	{*<link rel="stylesheet" href="{Info::themeFile('/css/basic.css')}"/>
	<link rel="stylesheet" href="{Info::themeFile('/css/style.css')}"/>*}
	<style type="text/css">
		@font-face {
			font-family: 'FontAwesome';
			src: url('{Info::themeFile('/fonts/fontawesome-webfont.eot')}?v=3.2.1');
			src: url('{Info::themeFile('/fonts/fontawesome-webfont.eot')}?#iefix&v=3.2.1') format('embedded-opentype'),
			url('{Info::themeFile('/fonts/fontawesome-webfont.woff')}?v=3.2.1') format('woff'),
			url('{Info::themeFile('/fonts/fontawesome-webfont.ttf')}?v=3.2.1') format('truetype'),
			url('{Info::themeFile('/fonts/fontawesome-webfont.svg')}#fontawesomeregular?v=3.2.1') format('svg');
			font-weight: normal;
			font-style: normal;
		}
		@font-face {
			font-family: 'trueloaded';
			src:  url('{Info::themeFile('/fonts/trueloaded.eot')}?4rk52p');
			src:  url('{Info::themeFile('/fonts/trueloaded.eot')}?4rk52p#iefix') format('embedded-opentype'),
			url('{Info::themeFile('/fonts/trueloaded.ttf')}?4rk52p') format('truetype'),
			url('{Info::themeFile('/fonts/trueloaded.woff')}?4rk52p') format('woff'),
			url('{Info::themeFile('/fonts/trueloaded.svg')}?4rk52p#trueloaded') format('svg');
			font-weight: normal;
			font-style: normal;
		}
		{file_get_contents(Info::themeFile('/css/base.css', 'fs'))}
		{file_get_contents(Info::themeFile('/css/style.css', 'fs'))}
	</style>

	{$this->head()}
	<script type="text/javascript">
		{file_get_contents(Info::themeFile('/js/jquery.min.js', 'fs'))}
		{file_get_contents(Info::themeFile('/js/main.js', 'fs'))}
	</script>

	{Css::widget()}
</head>

<body class="layout-main {$this->context->id}-{$this->context->action->id} context-{$this->context->id} action-{$this->context->action->id}{if Info::isAdmin()} is-admin{/if}">
{$this->beginBody()}

{Block::widget(['name' => 'header', 'params' => ['type' => 'header']])}

<div class="main-width main-content">{$content}</div>

{Block::widget(['name' => 'footer', 'params' => ['type' => 'footer']])}

{GoogleWidget::widget()}

<style type="text/css">
	{*if Info::isAdmin()*}
	{Info::getStyle(THEME_NAME)}
	{*else}
	{if is_file($smarty.const.DIR_FS_CATALOG|cat:'themes/'|cat:THEME_NAME|cat:'/css/custom.css')}{file_get_contents($smarty.const.DIR_FS_CATALOG|cat:'themes/'|cat:THEME_NAME|cat:'/css/custom.css')}{/if}
	{/if*}
</style>
{if Info::isAdmin()}
<link rel="stylesheet" href="{Info::themeFile('/css/admin.css')}"/>
{/if}
<link href='https://fonts.googleapis.com/css?family=Hind:400,700,600,500,300' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="{Info::themeFile('/css/jquery-ui.min.css')}"/>
<script type="text/javascript" src="{Info::themeFile('/js/jquery-ui.min.js')}"></script>
<script type="text/javascript" src="{Info::themeFile('/js/jquery.tabs.js')}"></script>
<script type="text/javascript" src="{Info::themeFile('/js/bootstrap-switch.js')}"></script>

<script type="text/javascript" src="{Info::themeFile('/js/jquery.fancybox.pack.js')}"></script>
<script type="text/javascript">document.createElement('main');</script>

{$this->endBody()}
</body>
</html>
{$this->endPage()}