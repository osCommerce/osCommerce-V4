<div class="pos-products" style="width: 100%">
    {foreach $order->products as $product}
        <div class="pos-products" style="width: 100%">
            <div class="name" style="width: 100%">
                <span class="qty">{\common\helpers\Product::getVirtualItemQuantity($product['id'], $product['qty'])}</span><span class="multiply"> x </span><span class="price-total" style="float: right; font-weight: bold">{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price, $product.tax, $product.qty), true, $order->info['currency'], $order->info['currency_value'])}</span><!-- comment --><span class="name">{$product['name']}</span>{*$product.model*}
                <div class="options"><br>
                  {if is_array($product.attributes) && $product.attributes|@sizeof > 0}
                    {foreach $product.attributes as $attribut}
                      <div><small>&nbsp;<i> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut.option))}: {$attribut.value}</i><br></small></div>
                    {/foreach}
                  {/if}
                </div>
            </div>
            <div class="pos-qty-price" style="width: 100%">
            {*\common\helpers\Tax::display_tax_value($product.tax)%*}
                {*<span class="price-one">{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price), true, $order->info['currency'], $order->info['currency_value'])}</span><span class="eq"> = </span>
*}
            {*$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price, 0, $product.qty), true, $order->info['currency'], $order->info['currency_value'])*}
                

            </div>
        </div>
<br><br>
    {/foreach}
</div>
{*
<table class="invoice-products" style="width: 100%">
  <tr class="invoice-products-headings">
    <td style="padding-left: 0">{$smarty.const.QTY}</td>
    <td>{$smarty.const.TEXT_NAME_}</td>
    <td>{$smarty.const.TEXT_MODEL_}</td>
    <td>{$smarty.const.TEXT_TAX}</td>
    <td>{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}</td>
    <td>{$smarty.const.TABLE_HEADING_TOTAL_EXCLUDING_TAX}</td>
    <td style="padding-right: 0">{$smarty.const.TABLE_HEADING_TOTAL_INCLUDING_TAX}</td>
  </tr>
  {if $to_pdf}

    {foreach $order->products as $product}
      <tr>
        <td style="width: {($width*0.0308)|ceil}px; padding-left: 0">{$product['qty']}</td>
        <td style="width: {($width*0.2809)|ceil}px">{$product['name']}<br>
          {if is_array($product.attributes) && $product.attributes|@sizeof > 0}
            {foreach $product.attributes as $attribut}
              <div><small>&nbsp;<i> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut.option))}: {$attribut.value}</i><br></small></div>
            {/foreach}
          {/if}
        </td>

        <td style="width: {($width*0.05)|ceil}px">{$product.model}</td>
        <td style="width: {($width*0.0162)|ceil}px">{\common\helpers\Tax::display_tax_value($product.tax)}%</td>
        <td style="width: {($width*0.0809)|ceil}px">{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price), true, $order->info['currency'], $order->info['currency_value'])}</td>
        <td style="width: {($width*0.01)|ceil}px">{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price, 0, $product.qty), true, $order->info['currency'], $order->info['currency_value'])}</td>
        <td style="width: {($width*0.11)|ceil}px; padding-right: 0"><b>{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price, $product.tax, $product.qty), true, $order->info['currency'], $order->info['currency_value'])}</b></td>
      </tr>
    {/foreach}

  {else}

    {foreach $order->products as $product}
      <tr>
        <td>{$product['qty']}</td>
        <td>{$product['name']}<br>
          {if is_array($product.attributes) && $product.attributes|@sizeof > 0}
            {foreach $product.attributes as $attribut}
              <div><small>&nbsp;<i> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut.option))}: {$attribut.value}</i><br></small></div>
            {/foreach}
          {/if}
        </td>

        <td>{$product.model}</td>
        <td>{\common\helpers\Tax::display_tax_value($product.tax)}%</td>
        <td>{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price), true, $order->info['currency'], $order->info['currency_value'])}</td>
        <td>{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price, 0, $product.qty), true, $order->info['currency'], $order->info['currency_value'])}</td>
        <td><b>{$currencies->format($currencies->calculate_price_in_order($order->info, $product.final_price, $product.tax, $product.qty), true, $order->info['currency'], $order->info['currency_value'])}</b></td>
      </tr>
    {/foreach}

  {/if}
</table>*}