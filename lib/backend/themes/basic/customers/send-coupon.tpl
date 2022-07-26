<form name="customer_coupon" id="customer_coupon"  method="post" onSubmit="return submitCoupon();">
<div class="popup-heading popup-heading-coup">{$smarty.const.IMAGE_SEND_EMAIL} {$customers['customers_firstname']}&nbsp;{$customers['customers_lastname']}</div>
<input type="hidden" name="customers_id" value="{$customers['customers_id']}">
<div class="popup-content">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-font table-send-coup">
        <tr>
            <td align="right"><label>{$smarty.const.COUPON_CODE}:</label></td>
            <td><input type="text" class="form-control" name="coupon_code"/></td>
            <td width="40%"></td>
        </tr>
        <tr>
            <td align="right"><label>{$smarty.const.TEXT_FROM}:</label></td>
            <td><input type="text" class="form-control" /></td>
            <td></td>
        </tr>
        <tr>
            <td align="right"><label>{$smarty.const.TEXT_SUBJECT}</label></td>
            <td colspan="2"><input type="text" class="form-control" name="coupon_subject"/></td>
        </tr>
        <tr>
            <td align="right"><label>{$smarty.const.TEXT_MESSAGE}</label></td>
            <td colspan="2"><textarea class="form-control" name="coupon_message"></textarea></td>
        </tr>
    </table>

</div>
<div class="noti-btn">
    <div class="btn-left"><a href="" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><input type="submit" class="btn btn-primary" value="{$smarty.const.TEXT_SEND_MAIL}"></div>
</div>
</form>
<script>
function submitCoupon(){
	switchOffCollapse('customer_management_bar');
    $.post('customers/send-coupon', $('form[name=customer_coupon]').serialize(), function(data, status){
      console.log(data);
      if (status == "success") {
          var customers_id = $( "input[name='customers_id']" ).val();
          $.post("customers/customeractions", {
            'customers_id': data.customers_id,
          }, function (data, status) {
            $('#customer_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOnCollapse('customer_management_bar');
            $('.popup-box:last').trigger('popup.close');
            $('.popup-box-wrap:last').remove();
          }, "html");
      } else {
          alert("Request error.");
      }      
    }, "json");
    return false;
}
</script>