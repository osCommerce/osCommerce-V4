{$this->beginPage()}<!DOCTYPE html>{use class="yii\helpers\Html"}{use class="frontend\design\IncludeTpl"}{use class="Yii"}{use class="frontend\design\Block"}{use class="frontend\design\Info"}{use class="common\components\google\widgets\GoogleWidget"}{use class="common\widgets\WarningWidget"}{use class="common\components\google\widgets\GoogleTagmanger"}<html lang="{str_replace("_", "-", Yii::$app->language)}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
{if !Info::isAdmin()}
{if $smarty.const.TRUSTPILOT_VERIFY_META_TAG}
    {$smarty.const.TRUSTPILOT_VERIFY_META_TAG}
{/if}
    <link rel="shortcut icon" href="{Info::themeFile('/icons/favicon.ico')}" type="image/x-icon" />
    <link rel="apple-touch-icon" sizes="57x57" href="{Info::themeFile('/icons/apple-icon-57x57.png')}">
    <link rel="apple-touch-icon" sizes="60x60" href="{Info::themeFile('/icons/apple-icon-60x60.png')}">
    <link rel="apple-touch-icon" sizes="72x72" href="{Info::themeFile('/icons/apple-icon-72x72.png')}">
    <link rel="apple-touch-icon" sizes="76x76" href="{Info::themeFile('/icons/apple-icon-76x76.png')}">
    <link rel="apple-touch-icon" sizes="114x114" href="{Info::themeFile('/icons/apple-icon-114x114.png')}">
    <link rel="apple-touch-icon" sizes="120x120" href="{Info::themeFile('/icons/apple-icon-120x120.png')}">
    <link rel="apple-touch-icon" sizes="144x144" href="{Info::themeFile('/icons/apple-icon-144x144.png')}">
    <link rel="apple-touch-icon" sizes="152x152" href="{Info::themeFile('/icons/apple-icon-152x152.png')}">
    <link rel="apple-touch-icon" sizes="180x180" href="{Info::themeFile('/icons/apple-icon-180x180.png')}">
	<link rel="apple-touch-icon" sizes="512x512" href="{Info::themeFile('/icons/apple-icon-512x512.png')}">
    <link rel="icon" type="image/png" sizes="192x192"  href="{Info::themeFile('/icons/android-icon-192x192.png')}">
	<link rel="icon" type="image/png" sizes="512x512"  href="{Info::themeFile('/icons/android-icon-512x512.png')}">
    <link rel="icon" type="image/png" sizes="32x32" href="{Info::themeFile('/icons/favicon-32x32.png')}">
    <link rel="icon" type="image/png" sizes="96x96" href="{Info::themeFile('/icons/favicon-96x96.png')}">
    <link rel="icon" type="image/png" sizes="16x16" href="{Info::themeFile('/icons/favicon-16x16.png')}">
    <link rel="manifest" href="manifest.json" crossorigin="use-credentials">
    {app\components\MetaCannonical::echoMetaTag()}
    <meta name="msapplication-TileColor" content="{Info::themeSetting('theme_color')}">
    <meta name="msapplication-TileImage" content="{Info::themeFile('/icons/ms-icon-144x144.png')}">
    <meta name="theme-color" content="{Info::themeSetting('theme_color')}">
    <meta name="generator" content="osCommerce 4.0">
{/if}
    <base href="{$smarty.const.BASE_URL}">
    {Html::csrfMetaTags()}
    <script type="text/javascript">
        {\common\helpers\System::js_cookie_setting('cookieConfig')}
    </script>
    {if $ot = \common\helpers\Acl::checkExtensionAllowed('OneTrust', 'allowed')}{$ot::getScript()}{/if}
  {if $app->controller->view->wp_head}
      <!-- wp head -->
    {$app->controller->view->wp_head}
      <!-- wp head end -->
  {else}
      <title>{$this->title}</title>
  {/if}

    {$this->head()}
    <script type="text/javascript">
        var productCellUrl = '{\yii\helpers\Url::to(['catalog/list-product'])}';
        var useCarousel = false;
        var tl_js = [];
        var tl_start = false;
        var tl_include_js = [];
        var tl_include_loaded = [];
        var tl = function(a, b){
            var script = { };
            if (typeof a === 'string' && a !== '' && typeof b === 'function'){
                script = { 'js': [a],'script': b}
            } else if (typeof a === 'object' && typeof b === 'function') {
                script = { 'js': a,'script': b}
            } else if (typeof a === 'function') {
                script = { 'script': a}
            }
            tl_js.push(script);
            if (tl_start){
                tl_action([script])
            }
        };
    </script>
  {capture name="body"}
    {if !$app->controller->view->no_header_footer}
    {Block::widget(['name' => 'header', 'params' => ['type' => 'header']])}
    {/if}
      <div class="{if $app->controller->view->page_layout == 'default'}main-width {/if}main-content">{$content}</div>
    {if !$app->controller->view->no_header_footer}
    {Block::widget(['name' => 'footer', 'params' => ['type' => 'footer']])}
    {/if}
  {/capture}

    {\frontend\design\JsonLd::getJsonLd()}

{Info::getCss()}

{if Info::isAdmin()}
<link rel="stylesheet" href="{Info::themeFile('/css/admin.css')}"/>
{/if}
{\frontend\design\EditData::addOnFrontend()}
<script type="text/javascript">
{Info::themeSetting('javascript', 'javascript')}
</script>
{GoogleTagmanger::trigger()}
{GoogleTagmanger::headTag()}
</head>
<body class="layout-main {$this->context->id}-{$this->context->action->id} p-{$this->context->id}-{$this->context->action->id} context-{$this->context->id} action-{$this->context->action->id}{if $app->controller->view->page_name} template-{$app->controller->view->page_name}{/if}{Info::getBoxesNames()}{if Info::isAdmin()} is-admin{/if}">
{if !$app->controller->view->no_header_footer}
{GoogleTagmanger::BodyTag()}
{$this->beginBody()}
{GoogleWidget::widget()}
{*GoogleTagmanger::trigger()*}

{WarningWidget::widget()}
{/if}

{$smarty.capture.body}
<script type="text/javascript" src="{Info::themeFile('/js/jquery.min.js')}" {$this->async}></script>
{Info::createJs()}
<script>
    {Info::addLayoutData()}
    {if \common\helpers\Acl::isFrontendTranslation() || Info::isAdmin() && Yii::$app->request->get('texts')}
    var entryDataPlaceHolder;
    {else}
    var entryData = JSON.parse('{addslashes(json_encode(Info::$jsGlobalData))}');
    {/if}
</script>
<script type="text/javascript" src="{Info::jsFilePath()}" {$this->async}></script>
{$this->endBody()}
{if Info::isAdmin()}
<script type="text/javascript">
  tl(function(){
    $('body').on('reload-frame', function(d, m){ $(this).html(m);});
    $('head').append('<link rel="stylesheet" href="{Info::themeFile("/css/jquery-ui.min.css")}"/>')
  });
</script>
{/if}
{if \common\helpers\Acl::isFrontendTranslation() || Info::isAdmin() && Yii::$app->request->get('texts')}
    <link rel="stylesheet" href="{Info::themeFile("/css/edit-data.css")}"/>
{/if}
{strip}
  <script type="text/javascript">

{if defined('USE_SOUCRCE_DURING_COPY')}
    {if USE_SOUCRCE_DURING_COPY == 'allow_source'}
    tl(function(){
        var grabText = function(e){
            var range = window.getSelection().toString();
            if (range.length > 0){
                var words = range.split(" ");
                var random =  Math.ceil(Math.random() * (words.length - 1) + 1);
                var newStr = '';
                /*var isIE = (navigator.userAgent.indexOf('MSIE') > -1 ? true : false);*/
                $.each(words, function(i, word) {
                    if (i == random - 1){
                        word = word + ' '  + '{$smarty.const.TEXT_COPIED_FROM}' + ' ' + window.location.href + ' ';
                    }
                    newStr += word + ' ';
                });
                newStr = newStr.substr(0, newStr.length - 1);
                if (e.clipboardData){
                    e.clipboardData.setData('text/plain', newStr);
                } else if(window.clipboardData) {
                    window.clipboardData.setData('text', newStr);
                }
                e.preventDefault();
            }
        };
        if (document.addEventListener){
            document.addEventListener('copy', function(e){
                grabText(e);
            });
        } else if (document.attachEvent){
            document.attachEvent("onCopy", function(e){
                grabText(window);
            });
        }

    });
    {else if USE_SOUCRCE_DURING_COPY == 'disallow'}
     tl(function(){
        var clearText = function (e){
            if (e.clipboardData){
                e.clipboardData.clearData();
            } else if(window.clipboardData) {
                window.clipboardData.clearData();
            }
            e.preventDefault();
        };

        if (document.addEventListener){
            document.addEventListener('copy', function(e){
                clearText(e);
            });
        } else if (document.attachEvent){
            document.attachEvent("onCopy", function(e){
                clearText(window);
            });
        }
     });
    {/if}

{/if}


  </script>
{/strip}
<!-- wp footer -->
{$app->controller->view->wp_footer}
<!-- wp footer end -->
{if !Info::isAdmin() && Info::themeSetting('service_worker')}
<script>
tl(function(){
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('{$smarty.const.BASE_URL}service-worker.js').then(function(registration) {
                // Registration was successful
                {if $pn = \common\helpers\Acl::checkExtensionAllowed('PushNotifications', 'allowed')}{$pn::registerPushNotificationJs($this)}{/if}
            console.log('ServiceWorker registration successful with scope: ', registration.scope);
          }, function(err) {
            // registration failed :(
            console.log('ServiceWorker registration failed: ', err);
          }).catch(function(err) {
            console.log(err)
          });
        });
    } else {
        console.log('service worker is not supported');
    }
});
</script>
{/if}
<link rel="stylesheet" href="{Info::themeFile('/css/style.css')}"/>
{if $awin = \common\helpers\Acl::checkExtensionAllowed('Awin', 'allowed')}{$awin::getScript()}{/if}
{foreach \common\helpers\Hooks::getList('frontend/layouts-main', 'before-body-close') as $filename}
    {include file=$filename}
{/foreach}
</body>
</html>
{$this->endPage()}