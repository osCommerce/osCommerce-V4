<table class="tableForm table-list">
    {\frontend\design\Info::addBoxToCss('table-list')}
    <tr>
        <th>{$smarty.const.HEADING_DATE_ADDED}</th>
        <th>{$smarty.const.HEADING_STATUS}</th>
        <th>{$smarty.const.HEADING_COMMENTS}</th>
    </tr>
    {foreach $order_statusses as $statusses}
        <tr>
            <td>{$statusses.date}</td>
            <td>{$statusses.status_name}</td>
            <td>{$statusses.comments_new}</td>
        </tr>
    {/foreach}
</table>