<p>Current version {$version}</p>
{foreach $updates as $item name=products}
    <p>{$item.filename}</p>
    {if $smarty.foreach.products.last}
        <a class="btn" target="_blank" href="javascript:void(0)" onclick="return runQuery();">{$smarty.const.TEXT_UPDATE_NOW}</a>
    {/if}
{foreachelse}
    <p>{$smarty.const.TEXT_NO_UPDATES}</p>
{/foreach}
 
