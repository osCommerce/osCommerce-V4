<!DOCTYPE html>
{use class="Yii"}
{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
<html {HTML_PARAMS}>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset={CHARSET}">
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
    table.invoice-products td {
      padding: 5px 10px;
      border-bottom: 1px solid #e0e0e0;
    }
    table.invoice-products td:nth-child(2) ~ td {
      text-align: center;
    }
    table.invoice-products td:last-child {
      text-align: right !important;
      padding-right: 0;
    }
    table.invoice-products td:first-child {
      padding-left: 0;
    }
    table.invoice-products .invoice-products-headings td {
      font-weight: bold;
      text-transform: uppercase;
      white-space: nowrap;
      font-size: 1.2em;
      border
    }
    table.invoice-totals td {
      padding: 0 10px;
    }
    table.invoice-totals td:first-child {
      padding-left: 0;
    }
    table.invoice-totals td:last-child {
      padding-right: 0;
    }
    .block.invoice > div {
      margin: 0 auto;
    }
  </style>
</head>
<body{if Info::isAdmin()} class="is-admin"{/if}>

  {Block::widget(['name' => 'packingslip', 'params' => ['type' => 'packingslip', 'params' => $params]])}

  <script type="text/javascript">
    $(function(){
      $('body').off('reload-frame').on('reload-frame', function(d, m){
        $(this).html(m);
      });

      $('head').append('<link href="https://fonts.googleapis.com/css?family=Hind:400,700,600,500,300" rel="stylesheet" type="text/css"><link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet" type="text/css"><link rel="stylesheet" href="{Info::themeFile("/css/jquery-ui.min.css")}"/>')
    });


  </script>
</body>
</html>