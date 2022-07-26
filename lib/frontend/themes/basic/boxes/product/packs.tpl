{use class="Yii"}
<div>
  {if $product.packagings > 0}
    {$smarty.const.TEXT_CARTOON}{$product.packagings}
    {if $product.packs > 0},{$smarty.const.TEXT_PACKS}{$product.packs}{/if}
  {else}
    {if $product.packs > 0}{$smarty.const.TEXT_PACKS}{$product.packs}{/if}
  {/if}
  {if $product.units > 0}{$smarty.const.TEXT_UNITS}{$product.units}{/if}
</div>