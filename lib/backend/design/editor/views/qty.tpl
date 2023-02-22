{use class="yii\helpers\Html"}
{if $isPack}
    <div>
        <span class="pr_minus"></span>
        <input name="update_products[{$product['id']}][qty_][0]" size='2' value="{$product['units']}"  data-max="{$max[0]}" {$min} {$step}
         class='form-control unit_qty'>
        <span class='pr_plus'></span>
    </div>
    {if $product['data']['pack_unit']}
    <div>
        <span class="pr_minus"></span>
        <input name="update_products[{$product['id']}][qty_][1]" size='2' value="{$product['packs']}"  data-max="{$max[1]}" class='form-control pack_qty'>
        <span class='pr_plus'></span>
    </div>
    {/if}
    {if $product['data']['packaging']}
    <div>
        <span class="pr_minus"></span>
        <input name="update_products[{$product['id']}][qty_][2]" size='2' value="{$product['packagings']}"  data-max="{$max[2]}" class='form-control packaging_qty'>
        <span class='pr_plus'></span>
    </div>
    {/if}
{else}
    <span class="pr_minus {if $product['quantity'] eq '1'}disable{/if}"></span>
    <input name="update_products[{$product['id']}][qty]" size='2' value="{$product['quantity_virtual']}" data-value-real="{$product['quantity']}" data-max="{$max}" {$min} {$step} class='form-control qty'>
    <span class='pr_plus'></span>
{/if}