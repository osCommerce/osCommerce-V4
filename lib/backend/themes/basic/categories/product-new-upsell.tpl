<tr role="row" prefix="upsell-box-{$upsell['upsell_id']}" class="{$upsell['status_class']}">
    <td class="sort-pointer"></td>
    <td class="img-ast img-ast-img">
        {$upsell['image']}
    </td>
    <td class="name-ast name-ast-xl">
        {$upsell['products_name']}
    </td>
    <td class="ast-price ast-price-xl">
        {$upsell['price']}
        <input type="hidden" name="upsell_id[]" value="{$upsell['upsell_id']}" />
    </td>
    <td class="remove-ast" onclick="deleteSelectedUpsell(this)"></td>
</tr>