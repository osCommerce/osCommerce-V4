<div class="order-sub-totals">
    <table>
    {foreach $output as $result}
        <tr class="{$result['class']} {if $result['show_line']}totals-line{/if}">
            <td>{$result['title']}</td>
            <td>{$result['text']}</td>
        </tr>
    {/foreach}
    </table>
</div>