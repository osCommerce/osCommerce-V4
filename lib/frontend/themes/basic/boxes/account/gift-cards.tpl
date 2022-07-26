<table class="table-list">
    <tr>
        <th class="recipients-name">{$smarty.const.RECIPIENTS_NAME}</th>
        <th class="">{$smarty.const.SELECT_AMOUNT}</th>
        <th class="code">{$smarty.const.TEXT_GIFT_CARD_CODE}</th>
        <th class="download"></th>
    </tr>
{foreach $giftCards as $giftCard}
    <tr class="item">
        <td class="recipients-name">{$giftCard.virtual_gift_card_recipients_name}</td>
        <td class="price">{$giftCard.price}</td>
        <td class="code">{$giftCard.virtual_gift_card_code}</td>
        <td class="download" style="text-align: right">
            <a href="{$giftCard.pdf}" target="_blank">{$smarty.const.TEXT_DOWNLOAD_PDF}</a>
        </td>
    </tr>
{/foreach}
</table>