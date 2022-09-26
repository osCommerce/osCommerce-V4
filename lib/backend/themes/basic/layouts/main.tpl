{use class="yii\helpers\Html"}
{use class="backend\components\TopLeftMenu"}
{use class="backend\components\TopRightMenu"}
{use class="backend\components\Navigation"}
{use class="backend\components\Breadcrumbs"}
{$this->beginPage()}
<!DOCTYPE html PUBLIC "-//W3C//DTD 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{str_replace("_", "-", Yii::$app->language)}"
    lang="{str_replace("_", "-", Yii::$app->language)}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=1000" />
    {Html::csrfMetaTags()}
    <title>{$this->title}</title>

    <!--=== CSS ===-->
{$version = 8}
    <!-- Bootstrap -->
    <link href="{$app->request->baseUrl}/css/bootstrap.min.css?{$version}" rel="stylesheet" type="text/css" />
    <link href="{$app->request->baseUrl}/css/jquery.filedrop.css?{$version}" rel="stylesheet" type="text/css" />
    <link href="{$app->request->baseUrl}/css/filedrop.css?{$version}" rel="stylesheet" type="text/css" />



    <!-- jQuery UI -->
    <!--<link href="{$app->request->baseUrl}/plugins/jquery-ui/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />-->
    <!--[if lt IE 9]>
		<link rel="stylesheet" type="text/css" href="{$app->request->baseUrl}/plugins/jquery-ui/jquery.ui.1.10.2.ie.css?{$version}"/>
	<![endif]-->
    <link href="{$app->request->baseUrl}/plugins/jquery-ui/jquery.multiselect.css?{$version}" rel="stylesheet" type="text/css" />

    <!-- Theme -->

    <link href="{$app->view->theme->baseUrl}/css/plugins.css?{$version}" rel="stylesheet" type="text/css" />
    <link href="{$app->view->theme->baseUrl}/css/icons.css?{$version}" rel="stylesheet" type="text/css" />

    <script type="text/javascript">
        let setTheme = (themeName) => localStorage.setItem('theme', themeName);

        let setCss = () => {

            let linkCss = (url, title) => {
                var url = url;
                var title = title;
                var link = document.createElement('link');
                link.href = url;
                link.title = title;
                link.rel = 'stylesheet';
                link.type = 'text/css';
                document.getElementsByTagName('head')[0].appendChild(link);
            }

            if (localStorage.getItem('theme') === 'themeDark') {
                linkCss('{$app->view->theme->baseUrl}/css/theme-dark.css?{$version}', 'dark');
                linkCss('{$app->view->theme->baseUrl}/css/base-dark.css?{$version}', 'dark');
                linkCss('{$app->view->theme->baseUrl}/css/main-dark.css?{$version}', 'dark');
                linkCss('{$app->view->theme->baseUrl}/css/style-dark.css?{$version}', 'dark');
                linkCss('{$app->view->theme->baseUrl}/css/menus-dark.css?{$version}', 'dark');
                setTimeout(() => {
                    let element = document.getElementById('themeDark');
                    element.classList.add('active');
                }, 1000);
            } else {
                linkCss('{$app->view->theme->baseUrl}/css/theme-light.css?{$version}', 'light');
                linkCss('{$app->view->theme->baseUrl}/css/base.css?{$version}', 'light');
                linkCss('{$app->view->theme->baseUrl}/css/main.css?{$version}', 'light');
                linkCss('{$app->view->theme->baseUrl}/css/style.css?{$version}', 'light');
                linkCss('{$app->view->theme->baseUrl}/css/menus.css?{$version}', 'light');
                setTimeout(() => {
                    let element = document.getElementById('themeLight');
                    element.classList.add('active');
                }, 1000);
            }
        }

        setCss();

        let lightTheme = () => {
            setTheme('themeLight');
            document.location.reload();
        };

        let darkTheme = () => {
            setTheme('themeDark');
            document.location.reload();
        };
    </script>


    <link href="{$app->view->theme->baseUrl}/css/general.css?{$version}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css?{$version}">
    <!--[if IE 7]>
		<link rel="stylesheet" href="{$app->view->theme->baseUrl}/css/fontawesome/font-awesome-ie7.min.css?{$version}">
	<![endif]-->

    <!--[if IE 8]>
		<link href="{$app->view->theme->baseUrl}/css/ie8.css?{$version}" rel="stylesheet" type="text/css" />
	<![endif]-->
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>

    {if defined('WL_ENABLED') && WL_ENABLED === true}
        <link href="{$app->view->theme->baseUrl}/css/{$smarty.const.WL_COMPANY_STYLE}?{$version}" rel="stylesheet" type="text/css" />
    {/if}
    <!--=== JavaScript ===-->
    <script type="text/javascript">
        var entryData = JSON.parse('{addslashes(json_encode(\backend\design\Data::$jsGlobalData))}');
    </script>
    <script type="text/javascript" src="{Yii::$aliases['@web']}/index/load-languages-js"></script>

    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/libs/jquery-1.10.2.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js?{$version}">
    </script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/jquery.filedrop.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/jquery.multiselect.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/jquery.jshint.js?{$version}"></script>

    <script type="text/javascript" src="{$app->request->baseUrl}/js/bootstrap.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/libs/lodash.compat.min.js?{$version}"></script>
    {if {$smarty.const.WYSIWYG_EDITOR_POPUP_INLINE !=  'popup'} || {$app->controller->id=='design'} }
        <script type="text/javascript" src="{$app->request->baseUrl}/js/ckeditor/ckeditor.js?{$version}"></script>
    {/if}

    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/libs/dropzone.js?{$version}"></script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/main.js?{$version}"></script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/jquery.rating.pack.js?{$version}"></script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/jquery.jcarousel.min.js?{$version}"></script>

    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/file-manager.js?{$version}"></script>
    <link href="{$app->view->theme->baseUrl}/css/file-manager.css?{$version}" rel="stylesheet" type="text/css" />

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
		<script src="{$app->view->theme->baseUrl}/js/libs/html5shiv.js?{$version}"></script>
	<![endif]-->

    <!-- Smartphone Touch Events -->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/touchpunch/jquery.ui.touch-punch.min.js?{$version}">
    </script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/event.swipe/jquery.event.move.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/event.swipe/jquery.event.swipe.js?{$version}"></script>

    <!-- General -->
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/libs/breakpoints.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/respond/respond.min.js?{$version}"></script>
    <!-- Polyfill for min/max-width CSS3 Media Queries (only for IE8) -->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/cookie/jquery.cookie.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/slimscroll/jquery.slimscroll.min.js?{$version}"></script>
    <script type="text/javascript"
        src="{$app->request->baseUrl}/plugins/slimscroll/jquery.slimscroll.horizontal.min.js?{$version}"></script>

    <!-- Page specific plugins -->
    <!-- Charts -->
    <!--[if lt IE 9]>
		<script type="text/javascript" src="{$app->request->baseUrl}/plugins/flot/excanvas.min.js?{$version}"></script>
	<![endif]-->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/sparkline/jquery.sparkline.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/flot/jquery.flot.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/flot/jquery.flot.tooltip.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/flot/jquery.flot.resize.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/flot/jquery.flot.time.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/flot/jquery.flot.growraf.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/flot/jquery.flot.dashes.js?{$version}"></script>
    <!--<script type="text/javascript" src="{$app->request->baseUrl}/plugins/easy-pie-chart/jquery.easy-pie-chart.min.js"></script>-->
    {*<script type="text/javascript" src="{$app->request->baseUrl}/plugins/chart-js-master/Chart.min.js"></script>*}
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/chart-js-master/Chart.js?{$version}"></script>

    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/daterangepicker/moment.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/daterangepicker/daterangepicker.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/blockui/jquery.blockUI.min.js?{$version}"></script>

    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/fullcalendar/fullcalendar.min.js?{$version}"></script>

    <!-- Noty -->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/noty/jquery.noty.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/noty/layouts/top.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/noty/themes/default.js?{$version}"></script>

    <!-- Forms -->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/uniform/jquery.uniform.min.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/select2/select2.min.js?{$version}"></script>

    <!-- App -->
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/jquery.cookie.js?{$version}"></script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/bootstrap-switch.js?{$version}"></script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/app.js?{$version}"></script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/plugins.js?{$version}"></script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/plugins.form-components.js?{$version}"></script>
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/jquery.inrow.js?{$version}"></script>

    <!-- DataTables -->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/datatables/jquery.dataTables.1.10.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/datatables/tabletools/TableTools.min.js?{$version}">
    </script> <!-- optional -->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/datatables/colvis/ColVis.min.js?{$version}"></script>
    <!-- optional -->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/datatables/DT_bootstrap.js?{$version}"></script>
    <script type="text/javascript"
        src="{$app->request->baseUrl}/plugins/datatables/responsive/datatables.responsive.js?{$version}"></script>
    <!-- optional -->

    <!-- Nestable List -->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/nestable/jquery.nestable.min.js?{$version}"></script>

    <!-- Bootbox -->
    <script type="text/javascript" src="{$app->request->baseUrl}/plugins/bootbox/bootbox.js?{$version}"></script>

    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/jquery.edit-products.js?{$version}"></script>

    <script type="text/javascript" src="{$app->request->baseUrl}/includes/general.js?{$version}"></script>

    <link href="{$app->view->theme->baseUrl}/css/bootstrap-colorpicker.min.css?{$version}" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="{$app->view->theme->baseUrl}/js/bootstrap-colorpicker.min.js?{$version}"></script>

    <link href="{$app->request->baseUrl}/plugins/jQuery.ptTimeSelect-0.8/jquery.ptTimeSelect.css?{$version}" rel="stylesheet"
        type="text/css" />
    <script type="text/javascript"
        src="{$app->request->baseUrl}/plugins/jQuery.ptTimeSelect-0.8/jquery.ptTimeSelect.js?{$version}"></script>
    <script type="text/javascript" src="{$app->request->baseUrl}/themes/basic/js/jquery.scrolling-tabs.js?{$version}"></script>



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
    <link href="{$app->view->theme->baseUrl}/css/plugins/bootstrap-switch.css?{$version}" rel="stylesheet">
    <link href="{$app->view->theme->baseUrl}/css/responsive.css?{$version}" rel="stylesheet" type="text/css" />
    <base href="{HTTP_SERVER}{$app->request->baseUrl}/">
</head>

<body class="context-{$this->context->id}">
    {$this->beginBody()}
    <!-- Header -->
    <header class="header navbar navbar-fixed-top" role="banner">
        <!-- Top Navigation Bar -->
        <div class="container">

            <!-- Only visible on smartphones, menu toggle -->
            <ul class="nav navbar-nav">
                <li class="nav-toggle"><a href="javascript:void(0);" title=""><i class="icon-reorder"></i></a></li>
            </ul>

            <!-- Logo -->
            <a class="navbar-brand" href="{$app->urlManager->createUrl("index")}">
                {if ((defined('WL_ENABLED') && WL_ENABLED === true) && (defined('WL_COMPANY_LOGO') && WL_COMPANY_LOGO != ''))}
                    <img src="{$app->view->theme->baseUrl}/img/{$smarty.const.WL_COMPANY_LOGO}"
                        alt="{$smarty.const.WL_COMPANY_NAME}" />
                {else}
                    {if $smarty.cookies.theme_style|default:null == 'dark'}
						<img src="{$app->view->theme->baseUrl}/img/oscommerce_logo_white.png" alt="logo" />
					{else}
						<img src="{$app->view->theme->baseUrl}/img/oscommerce_logo_white.png" alt="logo" />
					{/if}	
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
                <li><a href="https://forums.oscommerce.com/" target="_blank">{$smarty.const.TEXT_SUPPORT}</a>
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
                <!--=== Notifications ===-->
                {*widget name="application.components.Notifications"*}
                <!-- /Notifications -->


                <div class="sidebar-widget sidebar-widget-new align-center">
                    <div class="btn-group" id="theme-switcher">
                        <button class="btn" id="themeLight" onclick="lightTheme()"><i class="icon-sun"></i>
                            {$smarty.const.TEXT_BRIGHT}</button>
                        <button class="btn" id="themeDark" onclick="darkTheme()"><i class="icon-moon"></i>
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