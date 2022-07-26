<div class="popup-heading">{$smarty.const.TEXT_ORDER_HISTORY}</div>
<div class="popup-content">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-font">
        {foreach $app->controller->view->history as $Item}
        <tr>
            <td><b>{$Item['date']}</b></td>
            <td>{$Item['comments']}</td>
        </tr>
        {/foreach}
    </table>
</div>