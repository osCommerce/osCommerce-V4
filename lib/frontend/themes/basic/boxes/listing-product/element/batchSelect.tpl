<label class="checkbox">
    <input type="checkbox" name="batch[]" value="{$product.id}"{if $product.batchSelected} checked="checked"{/if}>
    <span></span>
    {$smarty.const.TEXT_BATCH_SELECTED_ADD_TO_LIST}
</label>