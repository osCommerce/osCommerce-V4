<div class="update_pay_box">
	<ul class="update_pay_ul">
		<li>
			<div class="up_title">{$smarty.const.TEXT_ORIGINAL_AMOUNT}</div>
			<div class="up_value">{$old_ot_total}</div>
		</li>
		<li>
			<div class="up_title">{$smarty.const.TABLE_HEADING_TOTAL}</div>
			<div class="up_value">{$new_ot_total}</div>
		</li>
		<li>
			<div class="up_title">{$difference_desc}</div>
			<div class="up_value {if $difference == true}plus_ballance{else}minus_ballance{/if}">{$difference_ot_total}</div>
		</li>
	</ul>
	<div class="up_radio">
		<input type="radio" name="pay_choose" value="just_save" checked id="p0">
		<label for="p0">{$smarty.const.TEXT_JUST_SAVE}</label>
	</div>
	<div class="up_radio dis_module">
		<input type="radio" disabled id="p1">
		<label for="p1">{$smarty.const.TEXT_SEND_CUSTOMER_REQUEST}</label>
	</div>
  {if ($adminPaymentLink)}
	<div class="up_radio">
		<input type="radio" name="pay_choose" value="on_behalf"  id="p4">
		<label for="p4">{$smarty.const.TEXT_PAY_ON_BEHALF}</label>
	</div>
  {/if}
	<div class="up_radio dis_module">
		<input type="radio" disabled id="p2">
		<label for="p2">{$smarty.const.TEXT_PAY_DIFFERENCE_ON}</label>
	</div>
	<div class="up_radio dis_module">
		<input type="radio" disabled id="p3">
		<label for="p3">{$smarty.const.TEXT_PAY_DIFFERENCE_FROM}</label>
	</div>
</div>
<input type="hidden" name="pay_difference" value="{$pay_difference}">
	<div class="btn-bar">
		<div class="btn-left"><button class="btn btn-cancel" onclick="return closePopup()">{$smarty.const.IMAGE_CANCEL}</button></div>
		<div class="btn-right"><button class="btn btn-primary" onclick="return updatePayOrder()">{$smarty.const.IMAGE_UPDATE}</button></div>
	</div>

<script type="text/javascript">
function updatePayOrder() {
    localStorage.orderChanged = "true";
    $('#action_update_and_pay').val($('input:checked[name="pay_choose"]').val());
    $('#action_update_and_pay_amount').val($('input[name="pay_difference"]').val());
    closePopup();
    //$('#edit_order').submit();
	updateOrderProcess();
    // updateOrder();
    return false;
}
</script>