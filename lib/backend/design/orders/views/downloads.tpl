<table border="0" cellpadding="5" cellspacing="0">
    {foreach $data as $row}
        <tr>
            <td class="main">{$row->orders_products_name}</td>
            <td class="main">{$row->orders_products_filename} {$row->download_count_1} {$smarty.const.TEXT_DOWNLOAD}</td>
        </tr>
    {/foreach}
</table>