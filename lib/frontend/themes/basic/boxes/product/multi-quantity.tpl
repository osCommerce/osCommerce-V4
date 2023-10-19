{use class="Yii"}

<div class="mult-qty-input">
  <div class="input">
    <input type="hidden" name="mix[]" value="{$products_id|escape:'html'}">
    <input type="text" name="mix_qty[{$products_id|escape:'html'}][]" value="{if $qty != ''}{$qty}{/if}" class="qty-inp" data-min = "0" data-max="{$quantity_max}"
    {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}
        {$oqs::setLimit($order_quantity_data)}
    {/if} />
  </div>
</div>