<!DOCTYPE html>
{use class="yii\helpers\Html"}
{use class="backend\components\TopLeftMenu"}
{use class="backend\components\TopRightMenu"}
{use class="backend\components\Navigation"}
{use class="backend\components\Breadcrumbs"}
{$this->beginPage()}
<html lang="{str_replace("_", "-", Yii::$app->language)}">
{$version = $smarty.const.BACKEND_CSS_VERSION}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {Html::csrfMetaTags()}
    <title>{$this->title}</title>

    <link  href="{$app->view->theme->baseUrl}/css/icons.css?{$version}" type="text/css" rel="stylesheet" />
    <link  href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css?{$version}" rel="stylesheet"">
    {if defined('WL_ENABLED') && WL_ENABLED === true}
        <link  href="{$app->view->theme->baseUrl}/css/{$smarty.const.WL_COMPANY_STYLE}?{$version}" rel="stylesheet" type="text/css" />
    {/if}
    <link  href="{$app->request->baseUrl}/plugins/bootstrap/bootstrap.min.css?{$version}" type="text/css" rel="stylesheet" />
    <link  href="{$app->request->baseUrl}/plugins/jquery-ui/jquery-ui.min.css?{$version}" type="text/css" rel="stylesheet" />
    <link  href="{$app->request->baseUrl}/plugins/daterangepicker/daterangepicker.min.css?{$version}" type="text/css" rel="stylesheet" />
    <link  href="{$app->request->baseUrl}/plugins/select2/select2.min.css?{$version}" type="text/css" rel="stylesheet" />
    <link  href="{$app->request->baseUrl}/plugins/bootstrap-switch/bootstrap-switch.css?{$version}" rel="stylesheet">
    <link  href="{$app->request->baseUrl}/plugins/datatables/datatables.min.css?{$version}" type="text/css" rel="stylesheet" />
    <link  href="{$app->request->baseUrl}/plugins/scrolling-tabs/jquery.scrolling-tabs.min.css?{$version}" type="text/css" rel="stylesheet" />
    <link  href="{$app->request->baseUrl}/plugins/bootstrap-colorpicker/bootstrap-colorpicker.min.css?{$version}" type="text/css" rel="stylesheet" />
    <link  href="{$app->view->theme->baseUrl}/css/file-manager.css?{$version}" type="text/css" rel="stylesheet" />

    <link href="{$app->view->theme->baseUrl}/css/general.css?{$version}" rel="stylesheet" type="text/css" id="general-styles" />
    <script>
        (() => {
            const themeName = localStorage.getItem('theme') || 'themeLight';
            const css = {
                themeLight: ['theme-light', 'base', 'main', 'style', 'menus'],
                themeDark: ['theme-dark', 'base-dark', 'main-dark', 'style-dark', 'menus-dark']
            }
            {if is_file(DIR_FS_CATALOG|cat:$app->view->theme->baseUrl|cat:'/css/custom-light.css')}
            css.themeLight.push('custom-light')
            {/if}
            {if is_file(DIR_FS_CATALOG|cat:$app->view->theme->baseUrl|cat:'/css/custom-dark.css')}
            css.themeLight.push('custom-dark')
            {/if}
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
    <script src="{$app->request->baseUrl}/plugins/jquery.min.js?{$version}" type="text/javascript"></script>
    <script src="{Yii::$aliases['@web']}/index/load-languages-js?{$version}" type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/bootstrap/bootstrap.min.js?{$version}"  type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/lodash.compat.min.js?{$version}" type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/jquery-ui/jquery-ui.min.js?{$version}"  type="text/javascript"></script>
    {if {$smarty.const.WYSIWYG_EDITOR_POPUP_INLINE !=  'popup'} || {$app->controller->id=='design'} }
        <script src="{$app->request->baseUrl}/plugins/ckeditor/ckeditor.js?{$version}" type="text/javascript"></script>
    {/if}
    <script src="{$app->request->baseUrl}/plugins/moment.min.js?{$version}" type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/daterangepicker/daterangepicker.min.js?{$version}"  type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/slimscroll/jquery.slimscroll.min.js?{$version}" type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/select2/select2.min.js?{$version}"  type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/blockui/jquery.blockUI.min.js?{$version}" type="text/javascript"></script>{* ? *}
    <script src="{$app->request->baseUrl}/plugins/uniform/jquery.uniform.min.js?{$version}" type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/bootstrap-switch/bootstrap-switch.js?{$version}" type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/datatables/datatables.min.js?{$version}"  type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/scrolling-tabs/jquery.scrolling-tabs.min.js?{$version}"  type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/bootstrap-colorpicker/bootstrap-colorpicker.min.js?{$version}"  type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/bootbox/bootbox.all.min.js?{$version}" type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/dropzone.min.js?{$version}"   type="text/javascript"></script>
    <script src="{$app->request->baseUrl}/plugins/interact/interact.min.js?{$version}"   type="text/javascript"></script>
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

    {if (getenv('HTTPS') == 'on')}
        <base href="{HTTPS_SERVER}{$app->request->baseUrl}/">
    {else}
        <base href="{HTTP_SERVER}{$app->request->baseUrl}/">
    {/if}
</head>

<body class="context-{$this->context->id}">
{$this->beginBody()}
<!-- Header -->
<header class="header navbar navbar-fixed-top" role="banner">
    <!-- Top Navigation Bar -->
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand" href="{$app->urlManager->createUrl("index")}">
            {if ((defined('WL_ENABLED') && WL_ENABLED === true) && (defined('WL_COMPANY_LOGO') && WL_COMPANY_LOGO != ''))}
                {if is_file(DIR_FS_CATALOG|cat:$app->view->theme->baseUrl|cat:'/img/'|cat:WL_COMPANY_LOGO)}
                    {$logo = WL_COMPANY_LOGO}
                {else}
                    {$logo = 'powerful_long_super.svg'}
                {/if}
                <img src="{$app->view->theme->baseUrl}/img/{$logo}"
                     alt="{$smarty.const.WL_COMPANY_NAME}" width="300" />
            {else}
                <img src="{$app->view->theme->baseUrl}/img/oscommerce_logo_white.png" alt="logo" />
            {/if}
        </a>
        <!-- /logo -->

        <!-- Sidebar Toggler -->
        <a href="#" class="toggle-sidebar bs-tooltip" data-placement="bottom"
           data-original-title="{$smarty.const.TEXT_MAIN_MENU_TOGGLE_NAVIGATION|escape:'html'}">
            <i class="icon-reorder"></i>
        </a>
        <!-- /Sidebar Toggler -->

        <!-- Top Left Menu -->
        {TopLeftMenu::widget()}
        <!-- /Top Left Menu -->
        {if ((defined('WL_ENABLED') && WL_ENABLED === true) && (defined('WL_COMPANY_PHONE') && WL_COMPANY_PHONE != ''))}
            <div class="header_phone"><span><i class="icon-phone"></i>{$smarty.const.WL_COMPANY_PHONE}</span></div>
        {else}
            <div class="header_phone"><a class="headerPhoneLink" href="tel:{$smarty.const.HEADER_PHONE}"><i class="icon-phone"></i>{$smarty.const.HEADER_PHONE}</a></div>
        {/if}

        {if $ext = \common\helpers\Acl::checkExtensionAllowed('AdminChat', 'allowed')}
            {$ext::getHtml()}
        {/if}

        <!-- Top Right Menu -->
        {TopRightMenu::widget()}
        <!-- /Top Right Menu -->
        <ul class="header_menu_right">
            {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                {if ((defined('WL_CONTACT_URL') && WL_CONTACT_URL === true) &&
                (defined('WL_CONTACT_TEXT') && WL_CONTACT_TEXT != '') &&
                (defined('WL_CONTACT_WWW') && WL_CONTACT_WWW != ''))}
                    <li><a href="{$smarty.const.WL_CONTACT_WWW}" target="_blank">{$smarty.const.WL_CONTACT_TEXT}</a></li>
                {/if}
            {else}
                <li><a href="https://www.oscommerce.com/contact"
                       target="_blank">{$smarty.const.TEXT_HEADER_CONTACT_US}</a></li>
            {/if}
            {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                {if ((defined('WL_SUPPORT_URL') && WL_SUPPORT_URL === true) &&
                (defined('WL_SUPPORT_TEXT') && WL_SUPPORT_TEXT != '') &&
                (defined('WL_SUPPORT_WWW') && WL_SUPPORT_WWW != ''))}
                    <li><a href="{$smarty.const.WL_SUPPORT_WWW}" target="_blank">{$smarty.const.WL_SUPPORT_TEXT}</a></li>
                {/if}
            {else}
                <li><a href="https://www.oscommerce.com/forums/" target="_blank">{$smarty.const.TEXT_SUPPORT}</a>
                </li>
            {/if}
            {if (defined('WL_ENABLED') && WL_ENABLED === true)}
                {if ((defined('WL_SERVICES_URL') && WL_SERVICES_URL === true) &&
                (defined('WL_SERVICES_TEXT') && WL_SERVICES_TEXT != '') &&
                (defined('WL_SERVICES_WWW') && WL_SERVICES_WWW != ''))}
                    <li><a href="{$smarty.const.WL_SERVICES_WWW}" target="_blank">{$smarty.const.WL_SERVICES_TEXT}</a></li>
                {/if}
            {else}
                <li><a href="https://www.holbi.co.uk/ecommerce-development"
                       target="_blank">{$smarty.const.TEXT_ECOMMERCE_DEVELOPMENT}</a></li>
            {/if}
            <li>
                {$platforms = \common\classes\platform::getList(false)}
                {if $platforms|count > 1}
                    <a href="#popup_platforms" class="btn-main-choose-frontend"
                       target="_blank">{$smarty.const.TEXT_VIEW_SHOP}</a>

                    <div id="popup_platforms" style="display: none">
                        <div class="popup-heading">{$smarty.const.CHOOSE_FRONTEND}</div>
                        <div class="popup-content frontend-links">
                            {foreach $platforms as $frontend}
                                <p><a href="//{$frontend.platform_url}" target="_blank">{$frontend.text}</a></p>
                            {/foreach}
                        </div>
                        <div class="noti-btn">
                            <div><button class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</button></div>
                        </div>
                        <script type="text/javascript">
                            (function($) {
                                $(function() {
                                    $('.popup-box-wrap .frontend-links a').on('click', function() {
                                        $('.popup-box-wrap').remove()
                                    })
                                })
                            })(jQuery)
                        </script>
                    </div>
                    <script type="text/javascript">
                        (function($) {
                            $(function() {
                                $('.btn-main-choose-frontend').popUp({ one_popup: false });
                            })
                        })(jQuery)
                    </script>
                {else}
                    <a href="{tep_catalog_href_link()}" target="_blank">{$smarty.const.TEXT_VIEW_SHOP}</a>
                {/if}

            </li>
            {if (defined('YII_ENV') && YII_ENV === 'dev')}
                <li><a href="{$app->urlManager->createUrl(['cache_control/'])}">{$smarty.const.BOX_HEADING_CACHE_CONTROL}</a></li>
            {/if}
            {include file='./notify.tpl'}
        </ul>
    </div>
    <!-- /top navigation bar -->


</header> <!-- /.header -->

<div id="container">
    <div id="sidebar" class="sidebar-fixed">
        <div id="sidebar-content">

            <!-- Search Input -->
            <div class="sidebar-search">
                <div class="input-box">
                    <button type="submit" class="submit">
                        <i class="icon-search"></i>
                    </button>
                    <span>
                            <input type="text"
                                   placeholder="{$smarty.const.TEXT_MAIN_MENU_SEARCH_PLACEHOLDER|escape:'html'}"
                                   id="menusearch" autocomplete="off">
                        </span>
                </div>
            </div>

            <!-- Search Results -->
            <div class="sidebar-search-results">

                <i class="icon-remove close"></i>
                <!-- Documents -->
                <div class="title">
                    Documents
                </div>
                <ul class="notifications">
                    <li>
                        <a href="javascript:void(0);">
                            <div class="col-left">
                                <span class="label label-info"><i class="icon-file-text"></i></span>
                            </div>
                            <div class="col-right with-margin">
                                <span class="message"><strong>John Doe</strong> received $1.527,32</span>
                                <span class="time">finances.xls</span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);">
                            <div class="col-left">
                                <span class="label label-success"><i class="icon-file-text"></i></span>
                            </div>
                            <div class="col-right with-margin">
                                <span class="message">My name is <strong>John Doe</strong> ...</span>
                                <span class="time">briefing.docx</span>
                            </div>
                        </a>
                    </li>
                </ul>
                <!-- /Documents -->
                <!-- Persons -->
                <div class="title">
                    Persons
                </div>
                <ul class="notifications">
                    <li>
                        <a href="javascript:void(0);">
                            <div class="col-left">
                                <span class="label label-danger"><i class="icon-female"></i></span>
                            </div>
                            <div class="col-right with-margin">
                                <span class="message">Jane <strong>Doe</strong></span>
                                <span class="time">21 years old</span>
                            </div>
                        </a>
                    </li>
                </ul>
            </div> <!-- /.sidebar-search-results -->

            <!--=== Navigation ===-->
            {Navigation::widget()}
            <!-- /Navigation -->

            <div class="sidebar-widget sidebar-widget-new align-center">
                <div class="btn-group" id="theme-switcher">
                    <button class="btn" id="themeLight" onclick="setTheme('themeLight')"><i class="icon-sun"></i>
                        {$smarty.const.TEXT_BRIGHT}</button>
                    <button class="btn" id="themeDark" onclick="setTheme('themeDark')"><i class="icon-moon"></i>
                        {$smarty.const.TEXT_DARK}</button>
                </div>
            </div>

        </div>
        <div id="divider" class="resizeable"></div>
    </div>
    <!-- /Sidebar -->
    {$dayOfWeek = [$smarty.const.TEXT_SUNDAY, $smarty.const.TEXT_MONDAY, $smarty.const.TEXT_TUESDAY, $smarty.const.TEXT_WEDNESDAY, $smarty.const.TEXT_THURSDAY, $smarty.const.TEXT_FRIDAY, $smarty.const.TEXT_SATURDAY]}
    {$monthNames = [$smarty.const.TEXT_JAN, $smarty.const.TEXT_FAB,	$smarty.const.TEXT_MAR,	$smarty.const.TEXT_APR,	$smarty.const.TEXT_MAY,	$smarty.const.TEXT_JUN,	$smarty.const.TEXT_JUL,	$smarty.const.TEXT_AUG,	$smarty.const.TEXT_SEP,	$smarty.const.TEXT_OCT,	$smarty.const.TEXT_NOV,	$smarty.const.TEXT_DEC]}
    <div id="content">
        <div class="container">
            <div class="top_header after">

                <div class="united-date" data-time="{(date("G")*60 + date("i"))}">
                    <div class="clock_right"><i class="icon-clock-o"></i><span id="clock"></span></div>
                    <div class="date_right"><i class="icon-calendar-o"></i><span id="date"></span></div>
                </div>
                <div class="current-date" style="display: none;">
                    <div class="text">{$smarty.const.TEXT_CURRENT_TIME}</div>
                    <div class="clock_right"><i class="icon-clock-o"></i><span id="clock-1"></span></div>
                    <div class="date_right"><i class="icon-calendar-o"></i><span id="date-1"></span></div>
                </div>
                <div class="server-date" style="display: none;">
                    <div class="text">{$smarty.const.TEXT_SERVER_TIME}</div>
                    <div class="clock_right"><i class="icon-clock-o"></i><span id="clock-2">{date("G:i")}</span>
                    </div>
                    <div class="date_right"><i class="icon-calendar-o"></i><span
                                id="date-2">{$dayOfWeek[date("w")]}<br>
                                {date("j")} {$monthNames[date("n")-1]}, {date("Y")}
                            </span></div>
                </div>

                <!-- Breadcrumbs line -->
                {Breadcrumbs::widget()}
                <!-- /Breadcrumbs line -->
            </div>
            <span id="messageStack">
                    {if \Yii::$app->controller->view->errorMessage != '' }
                        <div class="popup-box-wrap pop-mess">
                            <div class="around-pop-up"></div>
                            <div class="popup-box">
                                <div class="pop-up-close pop-up-close-alert"></div>
                                <div class="pop-up-content">
                                    <div class="popup-heading">{$smarty.const.TEXT_NOTIFIC}</div>
                                    <div
                                            class="popup-content pop-mess-cont pop-mess-cont-{\Yii::$app->controller->view->errorMessageType}">
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
                                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function() {
                                    $(this).parents('.pop-mess').remove();
                                });
                            </script>
                        </div>

                    {/if}
                </span>
            {\Yii::$container->get('message_stack')->initFlash()->outputHead()}
            {\Yii::$container->get('message_stack')->initFlash()->outputAlert()}

            <div class="content-container">
                {$content}
            </div>
            <div class="footer">
                <ul>
                    {if ((defined('WL_ENABLED') && WL_ENABLED === true) && (defined('WL_COMPANY_NAME') && WL_COMPANY_NAME != ''))}
                        <li>Copyright &copy; {$smarty.now|date_format:"%Y"} <a target="_blank"
                                                                               href="https://oscommerce.com">{$smarty.const.WL_COMPANY_NAME}</a>. All rights
                            reserved.</li>
                    {else}
                        <li>{$smarty.const.TEXT_COPYRIGHT} {$smarty.now|date_format:"%Y"} <a target="_blank"
                                                                                             href="https://www.oscommerce.com">{$smarty.const.TEXT_COPYRIGHT_HOLBI}</a></li>
                        <li>{$smarty.const.TEXT_FOOTER_BOTTOM}</li>
                        <li>{$smarty.const.TEXT_FOOTER_COPYRIGHT} {$smarty.now|date_format:"%Y"}
                            {$smarty.const.TEXT_COPYRIGHT_HOLBI}</li>
                    {/if}
                </ul>
            </div>
        </div>
        <!-- /.container -->
    </div>
</div>
<script type="text/javascript">
    var monthNames = ["{$smarty.const.TEXT_JAN}", "{$smarty.const.TEXT_FAB}", "{$smarty.const.TEXT_MAR}", "{$smarty.const.TEXT_APR}", "{$smarty.const.TEXT_MAY}", "{$smarty.const.TEXT_JUN}",
        "{$smarty.const.TEXT_JUL}", "{$smarty.const.TEXT_AUG}", "{$smarty.const.TEXT_SEP}", "{$smarty.const.TEXT_OCT}", "{$smarty.const.TEXT_NOV}", "{$smarty.const.TEXT_DEC}"
    ];
    var dayOfWeek = ["{$smarty.const.TEXT_SUNDAY}", "{$smarty.const.TEXT_MONDAY}", "{$smarty.const.TEXT_TUESDAY}", "{$smarty.const.TEXT_WEDNESDAY}", "{$smarty.const.TEXT_THURSDAY}", "{$smarty.const.TEXT_FRIDAY}", "{$smarty.const.TEXT_SATURDAY}"];

    var diferentServerTime = (function() {
        var serverDate = new Date ({date("Y")}, {date("n")} - 1, '{date("j")}', '{date("G")}', +'{date("i")}', +'{date("s")}') ;
        var currentDate = new Date();
        var currentTime = currentDate.getTime();
        var serverTime = serverDate.getTime();
        return currentTime - serverTime;
    })()


    function popupEditor(form, field) {
        bootbox.dialog({ message: '<iframe src="{$app->urlManager->createUrl(['popups/editor','s'=>(float)microtime()])}&form='+form+'&field='+field+'" width="900px" height="620px" style="border:0"/>' });
    }
    bootbox.setDefaults({ size: 'large', onEscape: true, backdrop: true });


    $(document).ready(function() {

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
