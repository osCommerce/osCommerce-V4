<!DOCTYPE html>
{use class="Yii"}
{use class="frontend\design\Block"}
<html {HTML_PARAMS}>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset={CHARSET}">
  <title>{TITLE} - {TITLE_PRINT_ORDER}{$oID}</title>
  <base href="{$base_url}">
  <link rel="stylesheet" type="text/css" href="{Yii::$app->view->theme->baseUrl}/css/print.css">
  <link rel="stylesheet" href="{Yii::$app->view->theme->baseUrl}/css/fontawesome/font-awesome.min.css">
</head>
<body>
<div id="wrapper" style="width: 685px; margin: 0 auto">
  <div id="container">
    <!-- body_text //-->
    <table cellpadding="0" cellspacing="0" width="685" align="center">
      <tr>
        <td>
          <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
              <td class="logo"><img src="{Yii::$app->view->theme->baseUrl}/img/invoice_logo.png" alt="logo"></td>
              <td align="right" class="store_address">
                <div>{STORE_NAME_ADDRESS}</div>
                <table cellspacing="0" cellpadding="0" width="100%" class="store_address_table">
                  <tr>
                    <td>{ENTITY_PHONE_NUMBER}</td>
                    <td>{STORE_PHONE}</td>
                  </tr>
                  <tr>
                    <td>{TEXT_EMAIL}:</td>
                    <td>{STORE_OWNER_EMAIL_ADDRESS}</td>
                  </tr>
                  <tr>
                    <td>{ENTITY_WEBSITE}</td>
                    <td>{tep_catalog_href_link()}</td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
          <table cellspacing="0" cellpadding="0" width="100%" class="shipping_table_data">
            <tr>
              <td width="33%" valign="top" class="ship_data_bg">
                <div class="shipTitle"><strong>{CATEGORY_SHIPPING_ADDRESS}</strong></div>
                <div>{\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>')}</div>
              </td>
              <td width="33%" valign="top">
                <div class="smallTextTitle"><strong>{ENTRY_CUSTOMER}</strong></div>
                <div class="smallTextDesc">{$payment_info['customers_name']}</div>
                <div class="smallTextTitle"><strong>{ENTRY_TELEPHONE_NUMBER}</strong></div>
                <div class="smallTextDesc">{$payment_info['customers_telephone']}</div>
                <div class="smallTextTitle"><strong>{ENTRY_EMAIL_ADDRESS}</strong></div>
                <div class="smallTextDesc">{$payment_info['customers_email_address']}</div>
              </td>
              <td width="33%" rowspan="2" valign="top" class="barcode_td">
                <div class="title_order">{TITLE_INVOICE_ORDER}{$oID}</div>
                <div class="title_payment_date"><strong>{ENTRY_PAYMENT_DATE}</strong><br>{\common\helpers\Date::date_short($payment_info['date_purchased'])}</div>
                <div class="title_payment_method"><strong>{ENTRY_PAYMENT_METHOD}</strong><br>{strip_tags($order->info['payment_method'])}</div>
                <div class="barcode"><img alt="{$oID}" src="{HTTP_CATALOG_SERVER}{DIR_WS_CATALOG}account/order-barcode?oID={$oID}&cID={$order->customer['customer_id']}"></div>
              </td>
            </tr>
            <tr>
              <td width="33%">
                <div class="shipServ"><strong>{TEXT_SHIPPING_VIA}</strong><br>{$payment_info['shipping_method']}</div>
              </td>
              <td width="33%">
                <div class="shipServ"><strong>{TEXT_SHIPPING_SERVICE}</strong><br>{$payment_info['shipping_method']}</div>
              </td>
            </tr>
          </table>

          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr class="dataTableHeadingRow">
              <td class="dataTableHeadingContent">{ENTRY_INVOICE_QTY}</td>
              <td class="dataTableHeadingContent" width="25%">{TABLE_TEXT_NAME}</td>
              <td class="dataTableHeadingContent">{TABLE_HEADING_PRODUCTS_MODEL}</td>
              <td class="dataTableHeadingContent" align="center">{TABLE_HEADING_TAX}</td>
              <td class="dataTableHeadingContent" align="right">{TABLE_HEADING_PRICE_EXCLUDING_TAX}</td>
              <td class="dataTableHeadingContent" align="right">{TABLE_HEADING_TOTAL_EXCLUDING_TAX}</td>
              <td class="dataTableHeadingContent" align="right">{TABLE_HEADING_TOTAL_INCLUDING_TAX}</td>
            </tr>
            {foreach $order->products as $product}
              <tr class="dataTableRow">
                <td class="dataTableContent dataTableContent_border" valign="middle" align="left">{$product['qty']}</td>
                <td class="dataTableContentBorder dataTableContent_border" valign="middle">{$product['name']}<br>
                  {if $product.attributes|@sizeof > 0}
                    {foreach $product.attributes as $attribut}
                      <nobr><small>&nbsp;<i> - {htmlspecialchars($attribut.option)}: {htmlspecialchars($attribut.value)}</i><br></small></nobr>
                      <nobr><small>&nbsp;<i> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut.option))}: {$attribut.value}</i><br></small></nobr>
                    {/foreach}
                  {/if}
                </td>

                <td class="dataTableContentBorder dataTableContent_border" valign="middle">{$product.model}</td>
                <td class="dataTableContentBorder dataTableContent_border" align="center" valign="middle">{\common\helpers\Tax::display_tax_value($product.tax)}%</td>
                <td class="dataTableContentBorder dataTableContent_border" align="right" valign="middle">{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price), true, $order->info['currency'], $order->info['currency_value'])}</td>
                <td class="dataTableContentBorder dataTableContent_border" align="right" valign="middle">{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price, 0, $product.qty), true, $order->info['currency'], $order->info['currency_value'])}</td>
                <td class="dataTableContent dataTableContent_border" align="right" valign="middle"><b>{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price, $product.tax, $product.qty), true, $order->info['currency'], $order->info['currency_value'])}</b></td>
              </tr>
            {/foreach}
          </table>


          <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
              <td width="33%" class="shipBottomTd">
                <div class="shipBottomTitle"><strong>{CATEGORY_SHIPPING_ADDRESS}</strong></div>
                <div class="addresBottomShip">{\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>')}</div>
              </td>
              <td width="66%" class="shipBottomBg">
                <table border="0" width="100%" cellspacing="0" cellpadding="0">
                  {foreach $order->totals as $price}
                    <tr>
                      <td align="right" class="smallText">{$price.title}</td>
                      <td align="right" class="smallText">{$price.text}</td>
                    </tr>
                  {/foreach}
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    <table cellspacing="0" cellpadding="0" width="685" align="center">
      <tr>
        <td width="33%" align="left">
          <div><strong>{ENTITY_UNDELIVERED_RETURN}</strong></div>
          <div>{STORE_NAME_ADDRESS}</div>
        </td>
        <td width="33%" align="center">
          <img src="{Yii::$app->view->theme->baseUrl}/img/invoice_logo_small.png">
          <div class="thanks_block">{ENTITY_THANKS}</div>
        </td>
        <td width="33%" align="right">
          <div><strong>{TEXT_INVOICE_INFO}</strong></div>
          <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
              <td align="right">{ENTITY_PHONE_NUMBER}</td>
              <td align="right">{STORE_PHONE}</td>
            </tr>
            <tr>
              <td align="right">{TEXT_EMAIL}:</td>
              <td align="right">{STORE_OWNER_EMAIL_ADDRESS}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </div>
</div>
<!-- body_text_eof //-->
</body>
</html>