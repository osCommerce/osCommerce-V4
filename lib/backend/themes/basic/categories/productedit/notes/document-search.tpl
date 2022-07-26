{foreach $documents as $item}
    <a href="javascript:void(0)" {if $no_click}{else}onclick="return documentSearchSelected('{$item.name}', '{$item.download}');"{/if} class="item">
        <span class="suggest_table searchDocument">
            <span class="td_image"></span>
            <span class="td_name">{$item.name}</span>
            <span class="td_price">{if !$item.exist}({$smarty.const.MESSAGE_DELETED}){/if}</span>
        </span>
    </a>
{/foreach}
