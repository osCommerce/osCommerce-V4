<!DOCTYPE html>
{use class="Yii"}
{use class="frontend\design\boxes\TableRow"}
{use class="frontend\design\Info"}
<html {HTML_PARAMS}>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={$smarty.const.CHARSET}">
    <title>{$smarty.const.STORE_NAME} - {$smarty.const.TITLE_PRINT_ORDER}{$oID}</title>
    <base href="{$base_url}">
    <link rel="stylesheet" href="{Info::themeFile('/css/base.css')}"/>
    {if Info::isAdmin()}
        <link rel="stylesheet" href="{Info::themeFile('/css/admin.css')}"/>
    {/if}
    <script type="text/javascript">
        var tl = function(a, b){
            if (typeof a == 'function') {
                a()
            } else if (typeof b == 'function') {
                b()
            }
        };
    </script>
    <script type="text/javascript" src="{Info::themeFile('/js/jquery.min.js')}"></script>
    <script type="text/javascript">
        $(function(){
            $('body').on('reload-frame', function(d, m){
                $(this).html(m);
            });
        })
    </script>
    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@200;400;600;700&display=swap');
        @import url(https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300italic,700);
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
        body {
            font-family: "Inter", "Helvetica Neue", Helvetica, Arial, sans-serif;
        }
        div {
            width: auto;
        }
        p {
            padding: 0;
            margin: 0;
        }
        table {
            border: none;
            margin: 0;
            border-collapse: collapse;
        }
        table td {
            padding: 0;
            border: none;
        }
        table.table th {
            background-color: #e8e1ff!important;
            color: #424242;
            font-size: 16px;
            font-weight: 700;
            border-color: #caccd3!important;
            vertical-align: middle;
        }
        .table-bordered tr td {
            border: 1px solid #ddd;
        }
        .no-widget-name {
            font-size: 12px !important;
            font-weight: normal !important;
            overflow-wrap: break-word;
            width: 110px;
            line-height: 1.2;
        }
        .no-widget-text, .no-widget-prefix {
            display: none;
        }
    </style>
</head>
<body{if Info::isAdmin()} class="is-admin"{/if}>

{TableRow::widget(['name' => $page_name, 'params' => ['type' => $type, 'params' => $params]])}

<div style="height: 50px;">&nbsp;</div>
</body>
</html>