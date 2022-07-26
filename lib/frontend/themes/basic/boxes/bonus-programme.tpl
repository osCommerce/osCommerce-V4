<div class="info-pleasure-points-table ">
{if $groups}
    {foreach $groups as $group_code => $group}
        {if $group['group_enabled']}
        <h4 >&nbsp;{$group['name']}</h4>
       <table class="col-md-12 table dataTable">
            <thead>
                <tr>
                    <th width="40%" align="left">{$smarty.const.TEXT_PROMO_AREA}</th>
                    <th align="center">{$smarty.const.TEXT_PROMO_AWARD}</th>
                    <th align="center">{$smarty.const.TEXT_PROMO_OCCASION_LIMIT}</th>
                    <th align="center">{$smarty.const.TEXT_PROMO_DAILY_LIMIT}</th>
                </tr>
            </thead>
            <tbody>
                {if $group['items']}
                    {foreach $group['items'] as $code => $item}
                        {if is_object($item)}
                            <tr>
                                <td align="left"><span class="item-value-title">&nbsp;{$item->getPointsTitle()}</span></td>
                                <td align="center"><span class="item-value">{$item->getBonusPointsAward()}</span></td>
                                <td align="center"><span class="item-value">{$item->getBonusDailyLimit()}</span></td>
                                <td align="center" class="dayli-limit">{$item->getBonusPointsLimit()}</span></td>
                            </tr>
                        {/if}
                    {/foreach}
                {/if}
            </tbody>
       </table>
       {/if}
    {/foreach}
{/if}
</div>