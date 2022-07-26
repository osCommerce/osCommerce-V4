
<table class="products-table"{if $attributesText['.products-table']} style="{$attributesText['.products-table']}" {/if} cellpadding="0" cellspacing="0" border="0">
    {if $attributesArray['.heading-products']['display'] != 'none'}
    <tr class="heading-products" style="{$attributesText['.heading-products']}">
        {if $attributesArray['.heading-image']['display'] != 'none'}
        <td class="heading-image" style="{$attributesText['.heading-image']}"></td>
        {/if}
        {if $attributesArray['.heading-name']['display'] != 'none'}
        <td class="heading-name" style="{$attributesText['.heading-name']}">{$smarty.const.TEXT_NAME_PERSONAL}</td>
        {/if}
        {if $attributesArray['.heading-model']['display'] != 'none'}
        <td class="heading-model" style="{$attributesText['.heading-model']}">{$smarty.const.TEXT_MODEL}</td>
        {/if}
        {if $attributesArray['.heading-qty']['display'] != 'none'}
        <td class="heading-qty" style="{$attributesText['.heading-qty']}">{$smarty.const.QTY}</td>
        {/if}
        {if $attributesArray['.heading-price']['display'] != 'none'}
        <td class="heading-price" style="{$attributesText['.heading-price']}">{$smarty.const.TABLE_HEADING_TOTAL_INCLUDING_TAX}</td>
        {/if}
    </tr>
    {/if}
    {foreach $products as $item}
        <tr class="product-row" style="{$attributesText['.product-row']}">
            {if $attributesArray['.image']['display'] != 'none'}
            <td class="image" style="{$attributesText['.image']}">
                <img src="{common\classes\Images::getImageUrl($item['id'], 'Thumbnail')}" alt="" style="{$attributesText['img']}">
            </td>
            {/if}
            {if $attributesArray['.name']['display'] != 'none'}
            <td class="name" style="{$attributesText['.name']}">
                {$item['name']}
                {$item['attributes']}
            </td>
            {/if}
            {if $attributesArray['.model']['display'] != 'none'}
            <td class="model" style="{$attributesText['.model']}">
                {$item['model']}
            </td>
            {/if}
            {if $attributesArray['.qty']['display'] != 'none'}
            <td class="qty" style="{$attributesText['.qty']}">
                {$item['qty']}
            </td>
            {/if}
            {if $attributesArray['.price']['display'] != 'none'}
            <td class="price" style="{$attributesText['.price']}">
                {$item['tpl_price']}
            </td>
            {/if}
        </tr>
    {/foreach}
</table>