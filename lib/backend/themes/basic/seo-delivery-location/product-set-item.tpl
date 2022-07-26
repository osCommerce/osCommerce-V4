<tr role="row" prefix="set-product-{$product['products_id']}" class="{$product['status_class']}">
    <td class="sort-pointer"></td>
    <td class="ast-img-element img-ast-img">
        {$product['image']}
    </td>
    <td class="ast-name-element">
        {$product['products_name']} ({$product['price']})
        <input type="hidden" name="set_products_id[]" value="{$product['products_id']}" />
    </td>
    <td class="remove-ast" onclick="deleteSelectedProduct(this)"></td>
</tr>