{use class="frontend\design\Block"}{use class="frontend\design\Info"}{if Info::isAdmin()}<!DOCTYPE html>
<html lang="{Yii::$app->language}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <base href="{$smarty.const.BASE_URL}">
  <link href='https://fonts.googleapis.com/css?family=Hind:400,700,600,500,300' rel='stylesheet' type='text/css'>
  <script type="text/javascript" src="{Info::themeFile('/js/jquery.min.js')}"></script>
  <script type="text/javascript" src="{Info::themeFile('/js/jquery-ui.min.js')}"></script>
  <script type="text/javascript" src="{Info::themeFile('/js/main.js')}"></script>
  <script type="text/javascript">
    $(function(){
      $('body').on('reload-frame', function(d, m){
        $(this).html(m);
      });
    })
  </script>
  <link rel="stylesheet" href="{Info::themeFile('/css/admin.css')}"/>
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



    .ico-name:before {
      content: '\e949';
    }
    .ico-images:before {
      content: '\e97f';
    }
    .ico-attributes:before {
      content: '\e96d';
    }
    .ico-bundle:before {
      content: '\e96c';
    }
    .ico-in-bundles:before {
      content: '\e96c';
    }
    .ico-price:before {
      content: '\e96a';
    }
    .ico-quantity:before {
      content: '\e969';
    }
    .ico-buttons:before {
      content: '\e968';
    }
    .ico-description:before {
      content: '\e96e';
    }
    .ico-reviews:before {
      content: '\e943';
    }
    .ico-properties:before {
      content: '\e967';
    }
    .ico-also-purchased:before {
      content: '\e966';
    }
    .ico-cross-sell:before {
      content: '\e965';
    }
    .ico-title:before {
      content: '\e973';
    }
    .ico-content:before {
      content: '\e96e';
    }
    .ico-image:before {
      content: '\e97f';
    }
    .ico-paging-bar:before {
      content: '\e96f';
    }
    .ico-listing:before {
      content: '\e970';
    }
    .ico-listing-functionality:before {
      content: '\e971';
    }
    .ico-categories:before {
      content: '\e974';
    }
    .ico-filters:before {
      content: '\e972';
    }
    .ico-continue-button:before {
      content: '\e963';
    }
    .ico-gift-certificate:before {
      content: '\e95f';
    }
    .ico-discount-coupon:before {
      content: '\e95e';
    }
    .ico-order-reference:before {
      content: '\e95d';
    }
    .ico-give-away:before {
      content: '\e95c';
    }
    .ico-up-sell:before {
      content: '\e95b';
    }
    .ico-shipping-estimator:before {
      content: '\e95a';
    }
    .ico-order-total:before {
      content: '\e959';
    }
    .ico-contact-form:before {
      content: '\e957';
    }
    .ico-map:before {
      content: '';
    }
    .ico-contacts:before {
      content: '';
    }
    .ico-date:before {
      content: '\e964';
    }
    .ico-block-box:before {
      content: '\e98c';
    }
    .ico-banner:before {
      content: '\e988';
    }
    .ico-logo:before {
      content: '\e980';
    }
    .ico-text:before {
      content: '\e97e';
    }
    .ico-html:before {
      content: '\e97d';
    }
    .ico-store-address:before {
      content: '\e94a';
    }
    .ico-store-phone:before {
      content: '\e94b';
    }
    .ico-store-email:before {
      content: '\e94c';
    }
    .ico-store-site:before {
      content: '\e94d';
    }
    .ico-shipping-address:before {
      content: '\e94e';
    }
    .ico-shipping-method:before {
      content: '\e94f';
    }
    .ico-address-qrcode:before {
      content: '\e950';
    }
    .ico-order-barcode:before {
      content: '\e951';
    }
    .ico-customer-name:before {
      content: '\e949';
    }
    .ico-customer-email:before {
      content: '\e952';
    }
    .ico-customer-phone:before {
      content: '\e953';
    }
    .ico-totals:before {
      content: '\e959';
    }
    .ico-order-id:before {
      content: '\e95d';
    }
    .ico-payment-date:before {
      content: '\e954';
    }
    .ico-payment-method:before {
      content: '\e955';
    }
    .ico-container:before {
      content: '\e956';
    }
    .ico-tabs:before {
      content: '\e98b';
    }
    .ico-brands:before {
      content: '\e98a';
    }
    .ico-bestsellers:before {
      content: '\e989';
    }
    .ico-specials-products:before {
      content: '\e986';
    }
    .ico-featured-products:before {
      content: '\e987';
    }
    .ico-new-products:before {
      content: '\e982';
    }
    .ico-viewed-products:before {
      content: '\e981';
    }
    .ico-menu:before {
      content: '\e97b';
    }
    .ico-languages:before {
      content: '\e97a';
    }
    .ico-currencies:before {
      content: '\e979';
    }
    .ico-search:before {
      content: '\e978';
    }
    .ico-cart:before {
      content: '\e977';
    }
    .ico-breadcrumb:before {
      content: '\e975';
    }
    .ico-compare:before {
      content: '\e976';
    }

  </style>
</head>
  <body class="{if Info::isAdmin()} is-admin{/if}">
{/if}<div class="{$page_name}">{Block::widget(['name' => $page_name, 'params' => ['type' => 'gift_card', 'params' => $params]])}</div>{if Info::isAdmin()}
  </body>
{/if}