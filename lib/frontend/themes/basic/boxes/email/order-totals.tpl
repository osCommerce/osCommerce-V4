<table class="totals-table"{if $attributesText['.totals-table']} style="{$attributesText['.totals-table']}" {/if} cellpadding="0" cellspacing="0" border="0">
    {foreach $orderTotalOutput as $item}
        <tr class="totals-row {$item['code']}{if $item['show_line']} totals-line{/if}" style="{$attributesText['.totals-row']}{$attributesText['.'|cat:$item['code']]}{if $item['show_line']}{$attributesText['.totals-line']}{/if}">
            <td class="totals-title" style="{$attributesText['.totals-title']}{$attributesText['.'|cat:$item['code']|cat:' .totals-title']}">{strip_tags($item['title'])}</td>
            <td class="totals-value" style="{$attributesText['.totals-value']}{$attributesText['.'|cat:$item['code']|cat:' .totals-value']}">{strip_tags($item['text'])}</td>
        </tr>
    {/foreach}
</table>