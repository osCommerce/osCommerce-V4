{if $product.bonus_points_price > 0 || $product.bonus_points_cost > 0}
    <div class="bonus-points">
    {if $product.bonus_coefficient === false && $product.bonus_points_price > 0}
        <div class="bonus-points-price">
            <span>{$product.bonus_points_price}</span> <span>{$smarty.const.TEXT_POINTS_REDEEM}</span>
        </div>
    {/if}
    {if $product.bonus_points_cost > 0}
        <div class="bonus-points-cost">
            <span>{$product.bonus_points_cost}</span> <span>
                {$smarty.const.TEXT_POINTS_EARN}
                {if $product.bonus_coefficient}
                    ({$product.bonus_price_cost_currency_formatted})
                {/if}
            </span>
        </div>
    {/if}
    </div>
{/if}
