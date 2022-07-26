{use class="yii\helpers\Url"}
<div class="or_box_head"> {$Dvalue['name']}</div>
{if $notice != ''}<div class="row_or">{$notice}</div>{/if}
<input name="type_code" type="hidden" value="{$type_code}">
<input name="global_id" type="hidden" value="{$global_id}">
<div class="btn-toolbar btn-toolbar-order">
    <a class="btn btn-no-margin btn-primary btn-edit" href="{Url::to(['productsattributes/attributeedit', 'products_options_id' => $item_id, 'type_code' => $type_code, 'global_id' => $global_id])}">{$smarty.const.IMAGE_EDIT}</a>
    <button class="btn btn-no-margin btn-delete" onclick="return confirmDeleteOption( {$item_id})">{$smarty.const.IMAGE_DELETE}</button>
    {if $products_num > 0}
    <a class="btn btn-no-margin btn-cancel popup" href="{Url::to(['view-products', 'item_id' => $item_id, 'type_code' => $type_code])}">{$smarty.const.TEXT_PRODUCTS}</a>
    <script>$('.popup').popUp();</script>
    {/if}
</div>