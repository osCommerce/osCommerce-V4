{use class="frontend\design\Info"}
<div class="credit_amount_history">
    <h2>{if $type == 'credit'}{$smarty.const.CREDIT_AMOUNT_HISTORY}{else}{$smarty.const.BONUS_AMOUNT_HISTORY}{/if}</h2>
    <table cellspacing="0" cellpadding="0" width="100%" class="orders-table">
        <tr>
            <th>{$smarty.const.TEXT_DATE_ADDED}</th>
            <th>{if $type == 'credit'}{$smarty.const.TEXT_CREDIT}{else}{$smarty.const.BONUS_AMOUNT}{/if}</th>
            <th>{$smarty.const.TEXT_COMMENTS}</th>
        </tr>
        {foreach $history as $_history}
        <tr>
            <td>{$_history['date']}</td>
            <td>{$_history['credit']}</td>
            <td>{$_history['comments']}</td>
        </tr>
        {/foreach}
    </table>
</div>