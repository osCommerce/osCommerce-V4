<tr role="row" prefix="group-product-box-{$group_product['products_id']}" class="{$group_product['status_class']}">
    <td class="sort-pointer"></td>
    <td class="ast-img-group-product img-ast-img">
        {$group_product['image']}
    </td>
    <td class="ast-name-group-product">
        {$group_product['products_name']} {$group_product['products_model']} ({$group_product['price']})
        <input type="hidden" name="products_group_products_id[]" value="{$group_product['products_id']}" />
    </td>
    <td class="remove-ast" onclick="deleteSelectedGroup(this)"></td>
</tr>