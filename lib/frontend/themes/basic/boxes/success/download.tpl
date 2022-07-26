    {if $downloads|count > 0}
    <div class="productsDiv">
        <h2 class="product_details">{$smarty.const.HEADING_DOWNLOAD}</h2>
        <table>
        {foreach $downloads as $row}
            <tr>
            {foreach $row as $col}
                <td>{$col}</td>
            {/foreach}
            </tr>
        {/foreach}
        </table>
    </div>
    {/if}