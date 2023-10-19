<div class="popup-heading">{$smarty.const.TEXT_PRINT_LABEL}</div>
<div class="popup-content">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="table-font">
        <tr>
            <td>
    {if {$app->controller->view->errors|default:array()|@count} > 0}
        {foreach $app->controller->view->errors as $error}
            <div class="error">{$error}</div>
        {/foreach}
    {/if}
            <form id="select_method_form" name="select_method" onSubmit="return selectDeliveryDate();">
                <input type="hidden" name="orders_id" value="{$orders_id}">
                <input type="hidden" name="action" value="set_delivery">
                <input type="hidden" name="all_methods" value="{$all_methods}">
        
                <div><label>Select Delivery Date (mandatory field  for some labels): </label><input type="text" name="delivery_date" class="datepicker" value=""></div>
        

                <div style="padding: 0; text-align: center;" class="btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return cancelStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
                <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_SELECT}</button></div>
                </div>
            </form>
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
function selectDeliveryDate() {
    var params = $('#select_method_form').serialize();
    $('.pop-up-content:last').html('<div class="preloader"></div>');
    $.get("{Yii::$app->urlManager->createUrl('orders/print-label')}", params, function(data, status) {
        if (status == "success") {
            $('.pop-up-content:last').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function cancelStatement() {
    $('.pop-up-close:last').trigger('click');
    return false;
}
$(document).ready(function() { 
    $( ".datepicker" ).datepicker({
      changeMonth: true,
      changeYear: true,
      showOtherMonths:true,
      autoSize: false,
      dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}'
    });
    $('a.popup2').popUp();
});
</script>
