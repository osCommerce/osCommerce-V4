
<div class="batch-selected-products-list">
{\frontend\design\boxes\ProductListing::widget(['products' => $products, 'settings' => $settings, 'id' => $id])}
{if !$products}<div class="batch-selected-products-empty">{$smarty.const.TEXT_EMPTY_BUNDLE_SELECTION}</div>{/if}
</div>
{if $products && $total_amount}
    <div class="batch-selected-products-total">
        {$smarty.const.TEXT_TOTAL_PRICE} <span class="batch-selected-products-total-value">{$total_amount}</span>
    </div>
{/if}

<div class="buttons"><span class="btn btn-add-products" {if !$products}disabled="disabled"{/if}>{$smarty.const.TEXT_BATCH_SELECTED_ADD_TO_CART}</span></div>