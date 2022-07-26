
<table border="0" width="90%" cellspacing="0" cellpadding="5" class="invoice-totals">

    {foreach $order_total_output as $price}
      <tr>
        <td{if $price.show_line} style="border-top: 1px solid #ccc; padding-top: 5px"{/if} style="width:65%;">{$price.title}</td>
        <td{if $price.show_line} style="border-top: 1px solid #ccc; padding-top: 5px"{/if} style="width:45%;text-align:right">{$price.text}</td>
      </tr>
    {/foreach}
</table>