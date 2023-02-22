{if !empty($installed)}<p>Shop installation date: {$installed}</p>{/if}
<p>Current version {$version}{if $updatesCount > 0} - <a href="javascript:void(0)" onclick="return showUpdateLog();">{$smarty.const.TEXT_SHOW_UPDATES}</a>{/if}</p>
{foreach $updates as $item name=products}
    <p>{$item.filename}</p>
    {if $smarty.foreach.products.last}
        <a class="btn" target="_blank" href="javascript:void(0)" onclick="return runQuery(0);">{$smarty.const.TEXT_UPDATE_NOW}</a>
    {/if}
{foreachelse}
    <p>{$smarty.const.TEXT_NO_UPDATES}</p>
{/foreach}
 
