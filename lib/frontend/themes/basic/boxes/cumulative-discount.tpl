 <div class="discountBox">
     <div class="discountBoxWrap">
         <div class="yourDiscount">{$smarty.const.TEXT_DEFAULT_DISCOUNT} <b>{$current_discount}%</b></div>
         <div class="discountTitle">{$smarty.const.TEXT_WILL_GET_DISCOUNT}:</div>
         {if is_array($discount_ncs->additionalDiscountsNCS) && count($discount_ncs->additionalDiscountsNCS)}
            <ul>
                {foreach $discount_ncs->additionalDiscountsNCS as $d}
                    <li>{$d->groups_discounts_value + $group->groups_discount}% - {$smarty.const.TEXT_AMOUNT_HIGHER} {$currencies->format($d->groups_discounts_amount)}</li>
                {/foreach}
            </ul>
         {/if}
         
         <div class="discountText">
             <b class="superDiscount">{$smarty.const.TEXT_SUPERDISCOUNT}</b>
             <div>
                {$smarty.const.TEXT_SUPERDISCOUNT_INTRO} {$currencies->format($group->superdiscount_summ)}:
             </div>
         </div>
         {if is_array($discount_ncs->additionalDiscountsCS) && count($discount_ncs->additionalDiscountsCS)}
            <ul>
                {foreach $discount_ncs->additionalDiscountsCS as $d}
                    <li>{$d->groups_discounts_value + $group->groups_discount}% - {$smarty.const.TEXT_AMOUNT_HIGHER} {$currencies->format($d->groups_discounts_amount)}</li>
                {/foreach}
            </ul>
         {/if}
         <div class="discountText">
            {$smarty.const.TEXT_SUPERDISCOUNT_NOTE}
         </div>
     </div>
 </div>