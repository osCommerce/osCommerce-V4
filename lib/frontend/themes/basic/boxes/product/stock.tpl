{use class="Yii"}
{use class="frontend\design\Info"}
{if $stock_indicator}
<div class="wrap-stock">
    <div class="stock js-stock">
      <span class="{if isset($product.stock_indicator.text_stock_code)}{$product.stock_indicator.text_stock_code}{/if} {$stock_indicator.stock_code}"
        {if isset($stock_indicator.backorderFirst) && $stock_indicator.backorderFirst}
          title="{sprintf($smarty.const.TEXT_EXPECTED_ON , $stock_indicator.backorderFirst['qty'], \common\helpers\Date::formatDate($stock_indicator.backorderFirst['date']) )|escape:'html'}"
        {/if}><span class="{$stock_indicator.stock_code}-icon">&nbsp;</span>{$stock_indicator.stock_indicator_text}</span>
    </div>
    <div class="stock js-date-available">
        {if !empty($stock_indicator.products_date_available)}
        <span class="date-available">{sprintf($smarty.const.TEXT_EXPECTED_ON , '', \common\helpers\Date::formatDate($stock_indicator.products_date_available) )|escape:'html'}</span>
        {/if}
    </div>
</div>
{/if}