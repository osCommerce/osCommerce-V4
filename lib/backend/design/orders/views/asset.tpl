{if $asset}
    <label>{$smarty.const.TEXT_PRODUCT_ASSETS}</label>
    {if $asset->assetValues}
        {foreach $asset->assetValues as $values}
            <div>{$values->assetFields->products_assets_fields_name}: {$values->products_assets_value}</div>
        {/foreach}
    {/if}
{/if}