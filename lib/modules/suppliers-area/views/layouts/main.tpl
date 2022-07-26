{$this->beginPage()}
<!DOCTYPE html>
{use class="yii\helpers\Html"}{use class="frontend\design\IncludeTpl"}{use class="Yii"}{use class="frontend\design\Block"}{use class="frontend\design\Css"}{use class="frontend\design\Info"}{use class="common\widgets\WarningWidget"}
<html {$smarty.const.HTML_PARAMS}>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />    
  <link rel="shortcut icon" href="{Info::themeFile('/icons/favicon.ico')}" type="image/x-icon" />  
  <link rel="manifest" href="{Info::themeFile('/icons/manifest.json')}">
  {app\components\MetaCannonical::echoMetaTag()}
  <meta name="msapplication-TileColor" content="#092964">
  <meta name="msapplication-TileImage" content="{Info::themeFile('/icons/ms-icon-144x144.png')}">
  <meta name="theme-color" content="#092964">
  <base href="{$smarty.const.BASE_URL}">
  {Html::csrfMetaTags()}
  <title>{$this->title}</title>
  {$this->head()}
  <script src="https://cdn.jsdelivr.net/npm/vue"></script>
</head>
<body>
<script type="text/javascript" src="{Info::themeFile('/js/jquery-3.3.1.min.js')}"></script>
<script>
 var vm = new Vue(); 
</script>
{$this->beginBody()}

    {\frontend\design\boxes\Logo::widget()}
    
    <h2 style="float: right; top: 0; position: absolute;right: 0; margin: 15px;">{$app->controller->view->supplier_name}</h2>
    
    {\suppliersarea\widgets\Menu::widget()}
    
    <div style="width:70%;margin:0 auto;">
    {$content}
    </div>

{$this->endBody()}

<script>
$('head').append('<link rel="stylesheet" href="{Info::themeFile("/css/jquery-ui.min.css")}"/>')
</script>
</body>
</html>
{$this->endPage()}