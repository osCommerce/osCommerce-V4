<tr>
    <td class="label_name">{$smarty.const.TEXT_COUPON_CODE}</td>
    <td class="label_value">
        <a name="coupon"></a>
        <input name="gv_redeem_code[coupon]" class="form-control" value='{$gv_redeem_code}'>
    </td>
    <td class="label_value">
        <button type="button" class="btn btn-small discount_apply">{$smarty.const.TEXT_APPLY}</button>
    </td>
</tr>
<tr>
    <td class="main pay-td" colspan="3">&nbsp;</td>
</tr>
<tr>
    <td class="label_name" valign="top">{$smarty.const.TEXT_GIFT_VOUCHER}</td>
    <td class="label_value">

        <input name="gv_redeem_code[gv]" class="form-control" value=''>

    </td> 
    <td class="label_value" valign="top">
        <button type="button" class="btn btn-small certificate_apply">{$smarty.const.TEXT_APPLY}</button>
    </td>		
</tr>			



