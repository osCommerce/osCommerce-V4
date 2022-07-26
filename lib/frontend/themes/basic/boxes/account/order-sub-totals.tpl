{frontend\design\Info::addBoxToCss('sub-totals')}
<div class="historyTotal">
    {if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}
    <table class="tableForm">
        {foreach $order_info_ar as $order_info_arr}
            <tr class="{$order_info_arr['class']} {if $order_info_arr['show_line']} totals-line{/if}">
                <td align="right">{$order_info_arr.title}</td>
                <td align="right">{if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}{$order_info_arr.text}{/if}</td>
            </tr>
        {/foreach}
    </table>
    {/if}
</div>