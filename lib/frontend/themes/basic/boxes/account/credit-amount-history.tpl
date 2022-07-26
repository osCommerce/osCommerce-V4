{use class="frontend\design\Info"}
{\frontend\design\Info::addBoxToCss('table-list')}
{if $history|count > 0}
<div class="credit_amount_history">
    <table cellspacing="0" cellpadding="0" width="100%" class="orders-table table-list">
        <tr>
            <th>{$smarty.const.TEXT_DATE_ADDED}</th>
            <th>{$smarty.const.TEXT_CREDIT}</th>
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
{else}
    {if $settings[0].hide_parents && !Info::isAdmin()}
        <script>
            tl(function(){
                {if $settings[0].hide_parents == 1}
                $('#box-{$id}').hide()
                {elseif $settings[0].hide_parents == 2}
                $('#box-{$id}').closest('.box-block').hide()
                {elseif $settings[0].hide_parents == 3}
                $('#box-{$id}').closest('.box-block').closest('.box-block').hide()
                {elseif $settings[0].hide_parents == 4}
                $('#box-{$id}').closest('.box-block').closest('.box-block').closest('.box-block').hide()
                {/if}
            })
        </script>
    {/if}
{/if}