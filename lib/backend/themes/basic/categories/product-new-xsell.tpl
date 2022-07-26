<tr role="row" prefix="xsell-box-{$xsell['xsell_id']}" class="{$xsell['status_class']}">
    <td class="sort-pointer"></td>
    <td class="img-ast img-ast-img">
        {$xsell['image']}
    </td>
    <td class="name-ast name-ast-xl">
        {$xsell['products_name']}
    </td>
    <td class="ast-price ast-price-xl">
        {$xsell['price']}
        <input type="hidden" name="xsell_id[{$xsell_type_id}][]" value="{$xsell['xsell_id']}" />
    </td>
    <td class="remove-ast" onclick="deleteSelectedXSell(this)"></td>
</tr>