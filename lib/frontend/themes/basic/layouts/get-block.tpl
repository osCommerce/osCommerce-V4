{use class="yii\helpers\Html"}
{use class="frontend\design\IncludeTpl"}
{use class="Yii"}
{use class="frontend\design\Block"}
{use class="frontend\design\Css"}
{use class="frontend\design\Info"}
{use class="common\components\google\widgets\GoogleWidget"}
{$this->beginBody()}

{Block::widget(['name' => 'header', 'params' => ['type' => 'header']])}

<div class="{if $app->controller->view->page_layout == 'default'}main-width {/if}main-content">{$content}</div>

{Block::widget(['name' => 'footer', 'params' => ['type' => 'footer']])}

{GoogleWidget::widget()}

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
  {*if Info::isAdmin()*}
  {Info::getStyle(THEME_NAME)}
  {*else}
  {if is_file($smarty.const.DIR_FS_CATALOG|cat:'themes/'|cat:THEME_NAME|cat:'/css/custom.css')}
  {file_get_contents($smarty.const.DIR_FS_CATALOG|cat:'themes/'|cat:THEME_NAME|cat:'/css/custom.css')}
  {/if}
  {/if*}
</style>
{if Info::isAdmin()}
  <link rel="stylesheet" href="{Info::themeFile('/css/admin.css')}"/>
{/if}
<link href='https://fonts.googleapis.com/css?family=Hind:400,700,600,500,300' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Varela+Round' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="{Info::themeFile('/css/jquery-ui.min.css')}"/>


{$this->endBody()}