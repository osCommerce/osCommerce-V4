<a href="{$product.link}">
    {if isset($product.products_groups_name) && $product.products_groups_name}
        {$product.products_groups_name}
    {elseif isset($product.products_name_teg) && $product.products_name_teg}
        {$product.products_name_teg}
    {else}
        {$product.products_name}
    {/if}
</a>