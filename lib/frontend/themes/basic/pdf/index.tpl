<!DOCTYPE html>
{use class="Yii"}
{use class="frontend\design\Block"}
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
      padding: 5px;
      border-top: 1px solid #e0e0e0;
      text-align: left;
    }
    table.invoice-products td:nth-child(3) ~ td {
      text-align: right;
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
      font-size: 0.9em;
    }
    table.invoice-totals b {
      font-size: 1.3em;
    }
    .block.invoice > div {
      margin: 0 auto;
    }
    .heading-2 {
      font-size: 25px;
      font-weight: normal;
      color: #424242;
      margin: 20px 20px 10px;
    }



    .widgets-page {
      padding: 20px;
    }
    .widgets-page .widget-box {
      border: 1px solid #d9d9d9;
      margin: 0 0 20px;
      padding: 0;
    }
    .widgets-page .widget-header {
      background: #f9f9f9;
      border-bottom: 1px solid #d9d9d9;
      line-height: 35px;
      padding: 0 12px 2px;
      margin-bottom: 0;
      color: #424242;
      font-size: 16px;
      font-weight: 700;
      font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    .widgets-page .widget-content {
      padding: 10px 10px 40px;
      position: relative;
      background-color: #fff;
    }
  </style>
</head>
<body{if Info::isAdmin()} class="is-admin"{/if}>


<div class="widgets-page">

  <div class="widget-box">
    <div class="widget-header">
      {$smarty.const.PDF_HEADER}
    </div>
    <div class="widget-content">
      {Block::widget(['name' => 'pdf_header', 'params' => ['type' => 'pdf', 'params' => $params]])}
    </div>
  </div>

  <div class="widget-box">
    <div class="widget-header">
      Category
    </div>
    <div class="widget-content">
      {Block::widget(['name' => 'pdf_category', 'params' => ['type' => 'pdf', 'params' => $params]])}
    </div>
  </div>

  <div class="widget-box">
    <div class="widget-header">
      {$smarty.const.PDF_CATALOG_PRODUCT}
    </div>
    <div class="widget-content">
      {Block::widget(['name' => 'pdf', 'params' => ['type' => 'pdf', 'params' => $params]])}
    </div>
  </div>

  <div class="widget-box">
    <div class="widget-header">
      {$smarty.const.PDF_FOOTER}
    </div>
    <div class="widget-content">
      {Block::widget(['name' => 'pdf_footer', 'params' => ['type' => 'pdf', 'params' => $params]])}
    </div>
  </div>

</div>




<script type="text/javascript">
  $(function(){
    $('body').off('reload-frame').on('reload-frame', function(d, m){
      $(this).html(m);
    });

    $('head').append('' +
      '<link href="https://fonts.googleapis.com/css?family=Hind:400,700,600,500,300" rel="stylesheet" type="text/css">' +
      '<link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet" type="text/css">' +
      '<link rel="stylesheet" href="{Info::themeFile("/css/jquery-ui.min.css")}"/>')
  });


</script>
</body>
</html>