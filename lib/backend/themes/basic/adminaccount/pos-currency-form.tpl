{use class="yii\helpers\Html"}
<div id="accountpopup">
    {tep_draw_form('save_account_form', 'adminaccount', $action, 'post', 'id="save_account_form" onSubmit="return saveAccount();"')}
        {Html::input('hidden', "admin_id", $myAccount['admin_id'])}
        {Html::input('hidden', "popupname", 'pos_currency_id')}
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td class="dataTableContent">{ENTRY_CUSTOMER}</td>
                <td class="dataTableContent">
                    {Html::dropDownList('pos_currency_id', $posCurrencyId, $currencyDropDown, ['class' => 'form-control','id'=>'searchPosCurrency'])}
                </td>
            </tr>
        </table>
        <div class="btn-bar">
            <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel" onclick="return closePopup()">{IMAGE_CANCEL}</a></div>
            <div class="btn-right"><button class="btn btn-primary">{IMAGE_UPDATE}</button></div>
        </div>
    </form>
</div>