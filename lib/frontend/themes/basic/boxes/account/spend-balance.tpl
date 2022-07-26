<div class="spend-balance">
    <table class="table-list">
        <tr>
            <th>{$smarty.const.TEXT_COUPON_OFFER}</th>
            <th>{$smarty.const.TEXT_SPEND_AMOUNT}</th>
        </tr>
        {foreach $coupons as $code => $amount}
            <tr>
                <td>{$code}</td>
                <td>{$amount}</td>
            </tr>
        {/foreach}
    </table>
</div>