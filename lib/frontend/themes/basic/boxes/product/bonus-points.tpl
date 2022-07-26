<div>
    {if $bonus_coefficient === false && $bonus_points_price > 0}
    <div class="bonus-points-price">{$bonus_points_price} {$smarty.const.TEXT_POINTS_REDEEM}</div>
    {/if}
    {if $bonus_points_cost > 0}
    <div class="bonus-points-cost">{$bonus_points_cost} {$smarty.const.TEXT_POINTS_EARN} {if $bonus_coefficient} ({{$bonus_price_cost_currency_formatted}}) {/if}</div>
    {/if}
</div>
