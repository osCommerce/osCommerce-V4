<table class="invoice-products">
  <tr class="invoice-products-headings">
    <td style="padding-left: 0">{$smarty.const.QTY}</td>
    <td>{$smarty.const.TEXT_NAME_}</td>
    <td style="padding-right: 0; text-align: right">{$smarty.const.TEXT_MODEL_}</td>
  </tr>
  {if $to_pdf}

    {foreach $order->products as $product}
      <tr>
        <td style="width: {($width*0.0308)|ceil}px; padding-left: 0">{$product['qty']}</td>
        <td style="width: {($width*0.79)|ceil}px">{$product['name']}<br>
          {if $product.attributes|@sizeof > 0}
            {foreach $product.attributes as $attribut}
              <div><small>&nbsp;<i> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut.option))}: {$attribut.value}</i><br></small></div>
            {/foreach}
          {/if}
        </td>
        <td style="width: {($width*0.05)|ceil}px; text-align: right">{$product.model}</td>
      </tr>
    {/foreach}

  {else}

    {foreach $order->products|default:null as $product}
      <tr>
        <td>{$product['qty']}</td>
        <td>{$product['name']}<br>
          {if $product.attributes|@sizeof > 0}
            {foreach $product.attributes as $attribut}
              <div><small>&nbsp;<i> - {str_replace(array('&amp;nbsp;', '&lt;b&gt;', '&lt;/b&gt;', '&lt;br&gt;'), array('&nbsp;', '<b>', '</b>', '<br>'), htmlspecialchars($attribut.option))}: {$attribut.value}</i><br></small></div>
            {/foreach}
          {/if}
        </td>
        <td>{$product.model}</td>
      </tr>
    {/foreach}

  {/if}
</table>