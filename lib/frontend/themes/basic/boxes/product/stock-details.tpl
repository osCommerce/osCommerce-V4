{if $stock_indicator.max_qty>0 }
<div class="stock-details js-stock-details">
  {if  empty($stock_indicator.preorder_only) && $stock_indicator.flags.add_to_cart==1}
    {* something or everything in stock*}
    <span class="{$stock_indicator.text_stock_code}">
      <span class="in-stock-details-icon"></span>
      <span class="in-stock-details-text">{sprintf($smarty.const.TEXT_QTY_IN_STOCK, max($stock_indicator.max_qty_instant, $stock_indicator.max_qty))}</span>
    </span>
  {/if}
  
  {if !empty($stock_indicator.backorderFirst)}
    <div class="stock-details-header">
      <div class="stock-details-header qty">{$smarty.const.TEXT_EXPECTED_QTY}</div>
      <div class="stock-details-header date">{$smarty.const.TEXT_EXPECTED_DELIVERY_DATE}</div>
    </div>
      <div class="stock-details-table">
        <div class="stock-details-qty">{$stock_indicator.backorderFirst['qty']}</div>
        <div class="stock-details-date">{common\helpers\Date::formatDate($stock_indicator.backorderFirst['date']) }</div>
      </div>
  {/if}
  {if $stock_indicator.products_date_available}
    <div class="stock js-date-available"><span class="date-available">{sprintf($smarty.const.TEXT_EXPECTED_ON , '', \common\helpers\Date::formatDate($stock_indicator.products_date_available) )|escape:'html'}</span></div>
    {/if}
</div>
{/if}