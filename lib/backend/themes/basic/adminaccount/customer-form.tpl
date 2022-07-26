{use class="yii\helpers\Html"}
<div id="accountpopup">
    {tep_draw_form('save_account_form', 'adminaccount', $action, 'post', 'id="save_account_form" onSubmit="return saveAccount();"')}
        {Html::input('hidden', "admin_id", $myAccount['admin_id'])}
        {Html::input('hidden', "popupname", 'customers_id')}
        {Html::input('hidden', "customers_id", '',['id'=>'customersId'])}
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td class="dataTableContent">{$smarty.const.ENTRY_CUSTOMER}</td>
                <td class="dataTableContent">
                    {Html::input('text', "searchCustomer", '', ['class' => 'form-control','id'=>'searchCustomer', 'placeholder' => $smarty.const.PLACEHOLDER_EMPTY_FOR_REMOVE])}
                    <div id="searchCustomerWrap"></div>
                </td>
            </tr>
        </table>
        <div class="btn-bar">
            <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">{$smarty.const.IMAGE_CANCEL}</a></div>
            <div class="btn-right"><button class="btn btn-primary">{$smarty.const.IMAGE_UPDATE}</button></div>
        </div>
    </form>
</div>
<script type="text/javascript">
    $(function(){
        //AutoComplete Example
        $( "#searchCustomer" ).autocomplete({
            source: "{$app->urlManager->createUrl('adminaccount/get-customers')}",
            minLength: 2,
            autoFocus: true,
            delay: 0,
            appendTo: $("#searchCustomerWrap"),
            select: function( event, ui ) {
                $('#customersId').val(ui.item.id);
            }
        });
    });
</script>