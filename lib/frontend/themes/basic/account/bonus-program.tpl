{if $showBonusPart }
    {use class="\yii\helpers\Url"}
    <h4 class="order-table-title order_wishlist">{$smarty.const.TEXT_BONUS_PROGRAM}</h4>
    <div class="">       
        <label>{sprintf(TEXT_BONUS_PROGRAM_LINK, tep_href_link('promotions/actions', '', 'SSL'))}</label>
    </div>    
{/if}
