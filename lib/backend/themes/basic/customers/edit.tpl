{use class="\common\helpers\Html"}
{use class="common\helpers\Acl"}
<!--=== Page Content ===-->
<div id="customer_management_data">
<!--===Customer Edit ===-->
{Html::beginForm(Yii::$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $cInfo->customers_id]), 'post', ['name' => 'customer_edit'])}
{Html::input('hidden', 'customers_id', $cInfo->customers_id)}
{*Html::input('hidden', 'individual_id', $cInfo->admin_id)*}
<div class="row">
    <div class="col-md-12">
        <div class="widget-content fields_style">

            <div class="btn-bar btn-bar-top after">
                <div>
                    <div class="status-left" style="float: none;">
                        <span>{$smarty.const.ENTRY_ACTIVE}</span>
                        {Html::activeCheckBox($customerForm, 'status', ['class' => 'check_bot_switch_on_off'])}
                        {if \common\helpers\Acl::checkExtensionAllowed('ReportChangesHistory')}
                        <div class="btn-right">
                            <a href="{Yii::$app->urlManager->createUrl(['logger/popup', 'type' => 'Customer', 'id' => $cInfo->customers_id])}" class="btn-link-create popup">{$smarty.const.TEXT_HISTORY}</a>
                        </div>
                        {/if}
                    </div>
                </div>
            </div>
            {if !$cInfo->isNewRecord}
            <div class="cedit-top after">
                    <div class="cedit-block cedit-block-1">
                        <div class="cr-ord-cust">
                            <span>{$smarty.const.TEXT_REVIEWS}:</span>
                            <div>{$cInfo->get('total_reviews')}</div>
                            <a href="{Yii::$app->urlManager->createUrl(['reviews/', 'cID' => $cInfo->customers_id])}" target="_blank">{$smarty.const.TEXT_VIEW_ALL}</a>
                        </div>
                    </div>
                <div class="cedit-block cedit-block-2">
                    <div class="cr-ord-cust">
                        <span>{$smarty.const.TEXT_DATE_OF_LAST_ORDER}</span>
                        <div>{$cInfo->get('last_purchased')}</div>
                        {$cInfo->get('last_purchased_days')}
                    </div>
                </div>
                <div class="cedit-block cedit-block-3">
                    <div class="cr-ord-cust">
                        <span>{$smarty.const.TEXT_ORDER_COUNT}</span>
                        <div>{$cInfo->get('total_orders')}</div>
                        <a href="{Yii::$app->urlManager->createUrl(['orders/', 'by' => 'cID', 'search' => $cInfo->customers_id])}" target="_blank">{$smarty.const.TEXT_VIEW_ALL}</a>
                    </div>
                </div>
                <div class="cedit-block cedit-block-4">
                    <div class="cr-ord-cust global-currency {$prefix}">
                        <span>{$smarty.const.TEXT_TOTAL_ORDERED}</span>
                        <div>{$cInfo->get('total_sum')}</div>
                    </div>
                </div>
                {if $app->controller->view->showGroup}
                <div class="cedit-block cedit-block-5">
                    <div class="cr-ord-cust-link">
                        <a href="{Yii::$app->urlManager->createUrl(['groups/itemedit', 'popup' => 1])}" class="popup"></a>
                        <span>{$smarty.const.ENTRY_GROUP}</span>
                        <b>{$app->controller->view->groupStatusArray[$cInfo->groups_id]}</b>
                        {if $cInfo->groups_id}
                        , {$smarty.const.TEXT_CUMULATIVE_DISCOUNT} {$cInfo->get('discount')}%
                        {/if}
                    </div>
                    <div class="cr-ord-plat-link">
                        <a href="javascript:void(0)" class=""></a>
                        <span>{$smarty.const.TABLE_HEADING_PLATFORM}:</span>
                      <b>{$platforms[$cInfo->platform_id]}</b>
                    </div>
                </div>
                {/if}

            </div>
            {/if}
            <div class="create-or-wrap after create-cus-wrap">
                {if isset($messages['messages'])}
                    {foreach $messages['messages'] as $message}
                    <div class="alert fade in alert-{$message['type']}">
                        <i data-dismiss="alert" class="icon-remove close"></i>
                        <span>{$message['text']}</span>
                    </div>
                    {/foreach}
                {/if}
                <div class="cbox-left">
                    <div class="widget box box-no-shadow">
                        <div class="widget-header widget-header-personal"><h4>{$smarty.const.CATEGORY_PERSONAL}</h4></div>
                        <div class="widget-content">
                            {if in_array(ACCOUNT_GENDER, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td after">
                                    <label>{field_label const="ENTRY_GENDER" configuration="ACCOUNT_GENDER"}</label>
                                    {Html::activeRadioList($customerForm, 'gender', $customerForm->getGenderList(), ['unselect' => null, 'class' => 'radio-inline'])}
                                </div>
                            </div>
                            {/if}
                            {if in_array(ACCOUNT_FIRSTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_FIRST_NAME" configuration="ACCOUNT_FIRSTNAME"}</label>
                                    {Html::activeTextInput($customerForm, 'firstname', ['class' => 'form-control', 'required' => false])}
                                </div>
                            </div>
                            {/if}
                            {if in_array(ACCOUNT_LASTNAME, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_LAST_NAME" configuration="ACCOUNT_LASTNAME"}</label>
                                    {Html::activeTextInput($customerForm, 'lastname', ['class' => 'form-control', 'required' => false])}
                                </div>
                            </div>
                            {/if}
                            {if $app->controller->view->showDOB}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_DATE_OF_BIRTH" configuration="ACCOUNT_DOB"}</label>
                                    {Html::activeTextInput($customerForm, 'dobTmp', ['class' => 'datepicker form-control', 'required' => false, 'value' => \common\helpers\Date::date_short($customerForm->dob), 'autocomplete' => 'bday'])}
                                    {Html::activeHiddenInput($customerForm, 'dob', ['class' => 'dob-res', 'required' => false])}
                                </div>
                            </div>
                            {/if}
                          <div style="position: relative">
                            <div class="cr-ord-plat-link-2">
                              <a href="javascript:void(0)" class=""></a>
                            </div>
                          </div>

                          <div class="w-line-row w-line-row-1">
                              <div class="wl-td">
                                  <label>{field_label const="TABLE_HEADING_PLATFORM" required_text=""}</label>
                                  {Html::activeDropDownList($customerForm, 'platform_id', $platforms, ['class' => 'form-control'])}
                              </div>
                          </div>
                          <div class="w-line-row w-line-row-1">
                              <div class="wl-td">
                                  <label>{field_label const="BOX_LOCALIZATION_LANGUAGES" required_text=""}</label>
                                  {Html::activeDropDownList($customerForm, 'language_id', $languages, ['class' => 'form-control'])}
                              </div>
                          </div>
                          <div class="w-line-row w-line-row-1">
                              <div class="wl-td">
                                  <label>{field_label const="TABLE_HEADING_SALES_PERSON" required_text=""}</label>
                                  {Html::activeDropDownList($customerForm, 'admin_id', $admins)}
                              </div>
                          </div>

                        {if $app->controller->view->showGroup}

                        <div style="position: relative">
                            <div class="cr-ord-cust-link-2">
                                <a href="{Yii::$app->urlManager->createUrl(['groups/itemedit', 'popup' => 1, 'item_id' => $cInfo->groups_id])}" class="popup"></a>
                            </div>
                        </div>

                        <div class="w-line-row w-line-row-1">
                            <div class="wl-td">
                                <label>{$smarty.const.ENTRY_GROUP}</label>
                                {Html::activeDropDownList($customerForm, 'group', $app->controller->view->groupStatusArray, ['class' => 'form-control', 'prompt' => $smarty.const.TEXT_MAIN])}
                            </div>
                        </div>
                        {/if}
                        <div class="w-line-row w-line-row-1">
                            <div class="wl-td">
                                <label>{field_label const="TEXT_GUEST" required_text=""}</label>
                                {Html::activeDropDownList($customerForm, 'opc_temp_account', $app->controller->view->guestStatusArray, ['class' => 'form-control'])}
                            </div>
                        </div>
                        {if (in_array(ACCOUNT_PIN, ['required', 'required_register', 'visible', 'visible_register']))}
                        <div class="w-line-row w-line-row-1">
                            <div class="wl-td">
                                <label>{field_label const="TEXT_PIN" configuration="ACCOUNT_PIN"}</label>
                                {Html::activePasswordInput($customerForm, 'pin', ['class' => 'form-control', 'required' => false, 'autocomplete' => 'new-password'])}
                            </div>
                        </div>
                        {/if}

                        {foreach \common\helpers\Hooks::getList('customers/customeredit', 'personal-block') as $filename}
                            {include file=$filename}
                        {/foreach}
                        
                        </div>
                    </div>
                    <div class="widget box box-no-shadow">
                        <div class="widget-header widget-header-contact"><h4>{$smarty.const.CATEGORY_CONTACT}</h4></div>
                        <div class="widget-content">
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_EMAIL_ADDRESS" required_text="*"}</label>
                                    {Html::activeTextInput($customerForm, 'email_address', ['class' => 'form-control'])}
                                </div>
                            </div>
                        {if  \common\helpers\Acl::checkExtensionAllowed('Subscribers', 'allowed') }
                            {$list = $cInfo->get('subscribers_lists')}
                            {if !empty($list) && is_array($list) }
                              <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    {implode(', ', $list)}
                                </div>
                            </div>
                            {/if}
                        {/if}
{if in_array(ACCOUNT_TELEPHONE, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_TELEPHONE_NUMBER" configuration="ACCOUNT_TELEPHONE"}</label>
                                    {Html::activeTextInput($customerForm, 'telephone', ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
{if in_array(ACCOUNT_LANDLINE, ['required', 'required_register', 'visible', 'visible_register'])}
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{field_label const="ENTRY_LANDLINE" configuration="ACCOUNT_LANDLINE"}</label>
                                    {Html::activeTextInput($customerForm, 'landline', ['class' => 'form-control'])}
                                </div>
                            </div>
{/if}
                        </div>
                    </div>

            {if !$cInfo->isNewRecord}
                {if $smarty.const.ALLOW_CUSTOMER_CREDIT_AMOUNT == 'true'}
                    <div class="widget box box-no-shadow">
                        <div class="widget-header widget-header-credit">
                            <h4>{$smarty.const.CREDIT_AMOUNT}</h4><a href="{Yii::$app->urlManager->createUrl(['customers/credithistory', 'customers_id' => $cInfo->customers_id, 'type' => 'credit'])}" class="credit_amount_history">{$smarty.const.CREDIT_AMOUNT_EDITING}</a>
                        </div>
                        <div class="widget-content">
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{$smarty.const.TEXT_CREDIT}</label>
                                    <div class="credit_wr">
                                        <div class="credit_left">{$cInfo->get('view_credit_amount')}</div>
                                        <div class="credit_right">
                                            <select name="credit_prefix" class="form-control"><option value="+">+</option><option value="-">-</option></select>
                                            <input name="credit_amount" type="text" class="form-control" placeholder="{$cInfo->get('credit_amount_mask')}"><button class="btn btn-apply" onclick="return check_form();">{$smarty.const.TEXT_APPLY}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                           <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <label>{$smarty.const.TEXT_COMMENT}</label><textarea name="comments" class="form-control textareaform"></textarea>
                                </div>
                            </div>
                            <div class="w-line-row w-line-row-1">
                                <div class="wl-td">
                                    <div class="notify_check">
                                        <input name="notify" type="checkbox" class="uniform" checked="checked">
                                        <span>{$smarty.const.TEXT_NOTIFY}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
                {/if}
                {foreach \common\helpers\Hooks::getList('customers/customeredit', 'left-column') as $filename}
                    {include file=$filename}
                {/foreach}
                </div>
                <div class="cbox-right">
{if $SplitCustomerAddresses = \common\helpers\Acl::checkExtensionAllowed('SplitCustomerAddresses', 'allowed')}
    {$SplitCustomerAddresses::viewCustomerEdit($cInfo, $customerForm)}
{else}
                    <div class="widget-no-btn box-no-btn box-no-shadow box-no-close">
                        <div class="widget-header widget-header-address"><h4>{$smarty.const.CATEGORY_ADDRESS}</h4></div>
                        <div class="widget-content-no-slider">
                            <div class="widget-header">
                                {Html::activeCheckBox($customerForm, 'can_use_drop_ship', ['class' => 'check_bot_switch_on_off', 'label' => $smarty.const.TEXT_USE_DROP_SHIP])}
                            </div>
                            {foreach $addresses as $keyvar => $address}
                            <div class="widget box box-no-shadow">
                                <div class="widget-header">
                                    <div class="btn-address">
                                        <div class="btn-default-add">{$smarty.const.ENTRY_DEFAULT}</div>
                                        {Html::radio('customers_default_address_id', $address.address_book_id == $cInfo->customers_default_address_id, ['class' => 'check_bot_switch', 'value' => $keyvar])}
                                    </div>
                                    <h4>{$address->suburb|escape:'html'} {$address->city|escape:'html'} {$address->state|escape:'html'} {$address->postcode|escape:'html'} {\common\helpers\Country::get_country_name($address->country)}{if $address.drop_ship} ({$smarty.const.TEXT_DROP_SHIP}){/if}</h4>
                                    <div class="toolbar no-padding">
                                        <div class="btn-group btn-group-no-bg">
                                            {if $address->address_book_id}
                                                <a href="javascript:void(0)" onclick="deleteAddress(this)" class="btn-del-add-cus"></a>
                                            {/if}
                                            <span id="orders_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="widget-content">
                                    <div class="w-line-row row">
                                    {if $address->has('FIRSTNAME')}
                                        <div class="col-6 mb-3">
                                           <div class="wl-td">
                                                <label>{field_label const="ENTRY_FIRST_NAME" configuration=$address->get('FIRSTNAME')}</label>
                                                {Html::activeTextInput($address, '['|cat:$keyvar|cat:']firstname', ['class' => 'form-control'])}
                                            </div>
                                        </div>
                                    {/if}

                                    {if $address->has('LASTNAME')}
                                        <div class="col-6 mb-3">
                                           <div class="wl-td">
                                                <label>{field_label const="ENTRY_LAST_NAME" configuration=$address->get('LASTNAME')}</label>
                                                {Html::activeTextInput($address, '['|cat:$keyvar|cat:']lastname', ['class' => 'form-control'])}
                                            </div>
                                        </div>
                                    {/if}

                                    {if $address->has('POSTCODE')}
                                        <div class="col-6 mb-3">
                                           <div class="wl-td">
                                                <label>{field_label const="ENTRY_POST_CODE" configuration=$address->get('POSTCODE')}</label>
                                                {Html::activeTextInput($address, '['|cat:$keyvar|cat:']postcode', ['class' => 'form-control'])}
                                            </div>
                                        </div>
                                    {/if}

                                    {if $address->has('STREET_ADDRESS')}
                                        <div class="col-6 mb-3">
                                           <div class="wl-td">
                                                <label>{field_label const="ENTRY_STREET_ADDRESS" configuration=$address->get('STREET_ADDRESS')}</label>
                                                {Html::activeTextInput($address, '['|cat:$keyvar|cat:']street_address', ['class' => 'form-control'])}
                                            </div>
                                        </div>
                                    {/if}

                                    {if $address->has('SUBURB')}
                                        <div class="col-6 mb-3">
                                           <div class="wl-td">
                                                <label>{field_label const="ENTRY_SUBURB" configuration=$address->get('SUBURB')}</label>
                                                {Html::activeTextInput($address, '['|cat:$keyvar|cat:']suburb', ['class' => 'form-control'])}
                                            </div>
                                        </div>
                                    {/if}
                                    {if $address->has('CITY')}
                                        <div class="col-6 mb-3">
                                            <div class="wl-td">
                                                <label>{field_label const="ENTRY_CITY" configuration=$address->get('CITY')}</label>
                                                {Html::activeTextInput($address, '['|cat:$keyvar|cat:']city', ['class' => 'form-control'])}
                                            </div>
                                       </div>
                                    {/if}

                                    {if $address->has('STATE')}
                                        <div class="col-6 mb-3">
                                            <div class="wl-td">
                                                <label>{field_label const="ENTRY_STATE" configuration=$address->get('STATE')}</label>
                                                <div class="f_td2 f_td_state">
                                                    {Html::activeTextInput($address, '['|cat:$keyvar|cat:']state', ['class' => 'form-control', 'id' => "selectState$keyvar"])}
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                    {if $address->has('COUNTRY')}
                                        <div class="col-6 mb-3">
                                           <div class="wl-td">
                                                <label>{field_label const="ENTRY_COUNTRY" configuration=$address->get('COUNTRY')}</label>
                                                {Html::activeDropDownList($address, '['|cat:$keyvar|cat:']country', $address->getAllowedCountries(), ['id' => "selectCountry$keyvar", 'class' => 'form-control', 'required' => true])}
                                            </div>
                                        </div>
                                    {/if}

                                    {if $address->has('COMPANY')}
                                        <div class="col-6 mb-3">
                                           <div class="wl-td">
                                                <label>{field_label const="ENTRY_COMPANY" configuration=$address->get('COMPANY')}</label>
                                                 {Html::activeTextInput($address, '['|cat:$keyvar|cat:']company', ['class' => 'form-control'])}
                                            </div>
                                        </div>
                                    {/if}
                                    {if $address->has('COMPANY_VAT')}
                                        <div class="col-6 mb-3">
                                           <div class="wl-td">
                                                <label>{field_label const="ENTRY_BUSINESS" configuration=$address->get('COMPANY_VAT')}</label>
                                                {Html::activeTextInput($address, '['|cat:$keyvar|cat:']company_vat', ['class' => 'form-control'])}
                                            </div>
                                        </div>
                                    {/if}

                                    {if $address->has('TELEPHONE')}
                                        <div class="col-6 mb-3">
                                            <div class="wl-td">
                                                <label>{field_label const="ENTRY_TELEPHONE_ADRESS_BOOK"}</label>
                                                 {Html::activeTextInput($address, '['|cat:$keyvar|cat:']telephone', ['class' => 'form-control'])}
                                            </div>
                                        </div>
                                    {/if}
                                    {if $address->has('EMAIL_ADDRESS')}
                                        <div class="col-6 mb-3">
                                            <div class="wl-td">
                                                <label>{field_label const="ENTRY_EMAIL_ADDRESS_ADRESS_BOOK"}</label>
                                                 {Html::activeTextInput($address, '['|cat:$keyvar|cat:']email_address', ['class' => 'form-control'])}
                                            </div>
                                        </div>
                                    {/if}
                                    {foreach \common\helpers\Hooks::getList('customers/customeredit', 'address-form-bottom') as $filename}
                                        {include file=$filename}
                                    {/foreach}
                                    </div>
                                </div>
                            </div>
{if $app->controller->view->showState}
<script type="text/javascript">
$('#selectState{$keyvar}').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "{$app->urlManager->createUrl('customers/states')}",
                dataType: "json",
                data: {
                    term : request.term,
                    country : $("#selectCountry{$keyvar}").val()
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_state',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        },
        select: function(event, ui) {
            $('input[name="city"]').prop('disabled', true);
            if(ui.item.value != null){
                $('input[name="city"]').prop('disabled', false);
            }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
</script>
{/if}
                            {/foreach}
                        </div>
                    </div>
{/if}

                    {foreach \common\helpers\Hooks::getList('customers/customeredit', 'right-column') as $filename}
                        {include file=$filename}
                    {/foreach}

                </div>
            </div>
                        <div class="w-line-row w-line-row-1 w-line-row-req">
                                <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                        </div>
            <div class="btn-bar" style="padding: 0;">
                <div class="btn-left"><a href="javascript:void(0)" onclick="resetStatement()" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a></div>
                <div class="btn-right"><button class="btn btn-confirm" onclick="return check_form()">{$smarty.const.IMAGE_CONFIRM}</button></div>
            </div>
                {if !$cInfo->isNewRecord}
                        <div class="btn-wr-center">
                            <a class="btn btn-orders" href="{$app->urlManager->createUrl(['orders/', 'by' => 'cID', 'search' => $cInfo->customers_id])}">{$smarty.const.TEXT_ORDERS}</a>
                            <a class="btn btn-email" href="mailto:{$cInfo->customers_email_address}">{$smarty.const.TEXT_EMAIL}</a>
{if \common\helpers\Acl::rule(['ACL_CUSTORER', 'TEXT_MERGE_CUSTOMER'])}
                            <a class="btn btn-merge" href="{$app->urlManager->createUrl(['customers/customermerge', 'customers_id' => $cInfo->customers_id])}">{$smarty.const.TEXT_MERGE_CUSTOMER}</a>
{/if}
{if \common\helpers\Acl::rule(['ACL_CUSTORER', 'T_SEND_COUPON'])}
                            <a class="btn btn-send-coupon popup" href="{$app->urlManager->createUrl(['gv_mail/index', 'type' => 'C', 'only' => $cInfo->customers_id])}">{$smarty.const.TEXT_SEND_COUPON}</a>
{/if}
{if \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_NEW'])}
                            <a class="btn btn-new-order btn-primary" href="{$app->urlManager->createUrl(['editor/create-order', 'customers_id' => $cInfo->customers_id, 'back' => 'customers'])}">{$smarty.const.TEXT_CREATE_NEW_ORDER}</a>
{/if}
                        </div>
                {/if}
        </div>
    </div>
</div>
{Html::endForm()}
<!-- Customer Edit -->
</div>
<script type="text/javascript">
function saveItem() {
        $.post("groups/submit", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('select[name="groups_id"]').html(data);
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap:last').remove();
            } else {
                alert("Request error.");
            }
        }, "html");
    return false;
}
function cancelStatement() {

    return false;
}
function deleteAddress(obj) {
    $(obj).parent().parent().parent().parent().remove();
        return false;
}
function resetStatement() {
    window.history.back();
    return false;
}
function check_form() {
    var $frm = $('form[name="customer_edit"]');
    if ( $frm.hasClass('submitted') ) return false;

    $frm.addClass('submitted');
    return true;
    //var customers_id = $( "input[name='customers_id']" ).val();
}

function apply_credit(){
    $.post("{$app->urlManager->createUrl('customers/customeredit')}", $('#customers_edit').serialize(), function(data, status){
        if (status == "success") {
            $(window).scrollTop(0);
            $('#customer_management_data').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
$(document).ready(function(){
    $("a.popup").popUp();
    $('.credit_amount_history').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupCredithistory'><div class='popup-heading credit-head'>{$smarty.const.ENTRY_CREDIT_HISTORY}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
    });
    $(".check_bot_switch").bootstrapSwitch(
        {
            onText: "{$smarty.const.SW_ON}",
			offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    $(".check_bot_switch_on_off").bootstrapSwitch(
        {
			onText: "{$smarty.const.SW_ON}",
			offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    $(this).find(".cbox-left input[type='radio']").uniform();

    $('.js-platform-locations').on('add_row',function(){
        var skelHtml = $(this).find('tfoot').html();
        var $body = $(this).find('tbody');
        var counter = parseInt($body.attr('data-rows-count'),10)+1;
        $body.attr('data-rows-count',counter);
        skelHtml = skelHtml.replace(/_unhide_/g,'',skelHtml);
        skelHtml = skelHtml.replace(/%idx%/g, counter,skelHtml);
        $body.append(skelHtml);
    });
    $('.js-platform-locations').on('click', '.js-remove-platform-location',function(event){
        $(event.target).parents('tr').remove();
    });
    $('.js-add-platform-location').on('click',function(){
        $('.js-platform-locations').trigger('add_row');
    });
});
{if $app->controller->view->showDOB}
    $( ".datepicker" ).datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100y:-9y",
        defaultDate: '-40y',
        showOtherMonths:true,
        autoSize: false,
        dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
        onSelect: function (value, e){
            let month = parseInt(e.selectedMonth)+1;
            try{
                var date = new Date(month+'/'+e.selectedDay+'/'+e.selectedYear);
                $('.dob-res').val(new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString());
            } catch(error){
                console.log(error);
            }
            return false;
        }
});
{/if}
</script>
<!-- /Page Content -->
