{use class="common\helpers\Html"}
{use class="Yii"}
<style>
.popup-box{ top:100px; width:800px; }
.tl-grid.currency-table{ width:100%; }
</style>
<div id="suppliers_management_data">
{Html::beginForm(Yii::$app->urlManager->createUrl([$path, 'suppliers_id' => $service->supplier->suppliers_id]), 'post', ['name' => 'supplier'])}
  <div class="box-wrap">   
    <div class="create-or-wrap after create-cus-wrap">
        <div class="cbox-left">
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-personal">
                    <h4>{$smarty.const.CATEGORY_GENERAL}</h4>
                </div>    
                <div class="widget-content">
                {if $service->allow_change_default}
                    {if !$service->supplier->is_default}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_DEFAULT}</label>{Html::checkbox('suppliers_data[is_default]', $service->supplier->is_default, ['class' => 'check_on_off', 'uncheck' => 0])}
                        </div>
                    </div>
                    {/if}
                {/if}
                {if $service->allow_change_status}
                    {if !$service->supplier->is_default}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_STATUS}</label>{Html::checkbox('suppliers_data[status]', $service->supplier->status, ['class' => 'check_on_off', 'uncheck' => 0])}
                        </div>
                    </div>
                    {/if}
                {/if}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_SUPPLIERS_NAME}</label>
                            {Html::textInput('suppliers_data[suppliers_name]', $service->supplier->suppliers_name, ['class' => "form-control"])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_DELIVERY_TIME}:</label>
                            <div class="row">
                                <div class="col-md-6">
                                    {$smarty.const.TEXT_FROM} {Html::textInput('suppliers_data[delivery_days_min]', $service->supplier->delivery_days_min, ['class' => "form-control input-sm"])}
                                </div>
                                <div class="col-md-6">
                                    {$smarty.const.TEXT_TO} {Html::textInput('suppliers_data[delivery_days_max]', $service->supplier->delivery_days_max, ['class' => "form-control input-sm"])}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_SUPPLIERS_DEFAULT_TAX_RATE}:</label>
                            {Html::textInput('suppliers_data[tax_rate]', $service->supplier->tax_rate, ['class' => "form-control"])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_SUPPLIER_PRICES_WITH_TAX}</label>{Html::checkbox('suppliers_data[supplier_prices_with_tax]', $service->supplier->supplier_prices_with_tax, ['class' => 'check_on_off', 'uncheck' => 0])}
                        </div>
                    </div>
                </div>
                    
           </div>

            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-personal">
                    <h4>{$smarty.const.CATEGORY_COMPANY}</h4>
                </div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_COMPANY}</label>{Html::textInput('suppliers_data[company]', $service->supplier->company, ['class' => 'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_BUSINESS}</label>{Html::textInput('suppliers_data[company_vat]', $service->supplier->company_vat, ['class' => 'form-control'])}
                        </div>
                    </div>
{if $service->supplier->hasAttribute('contact_name')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Contact Name</label>
                            {Html::textInput('suppliers_data[contact_name]', $service->supplier->contact_name, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('contact_phone')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Contact Phone</label>
                            {Html::textarea('suppliers_data[contact_phone]', $service->supplier->contact_phone, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('street_address')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Street Address</label>
                            {Html::textInput('suppliers_data[street_address]', $service->supplier->street_address, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('suburb')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Suburb</label>
                            {Html::textInput('suppliers_data[suburb]', $service->supplier->suburb, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('city')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Town/City</label>
                            {Html::textInput('suppliers_data[city]', $service->supplier->city, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('postcode')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Post Code</label>
                            {Html::textInput('suppliers_data[postcode]', $service->supplier->postcode, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('state')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>State/Province</label>
                            {Html::textInput('suppliers_data[state]', $service->supplier->state, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('country')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Country</label>
                            {Html::dropDownList('suppliers_data[country]', $service->supplier->country, $countries_list, ['class'=>'form-control'])}
                        </div>
                    </div>
{/if}
                </div>
            </div>

           <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-personal">
                    <h4>{$smarty.const.BOX_LOCALIZATION_CURRENCIES}</h4>
                </div>    
                <div class="widget-content">
                    <div class="w-line-row-1">
                        <div class="wl-td1">                            
                            {if is_object($service->currencies) && !$service->currencies_editor_simple}
                                <table class="tl-grid currency-table currency-list">
                                <tr>
                                    <th>{$smarty.const.BOX_LOCALIZATION_CURRENCIES}</th>
                                    <th>{$smarty.const.TEXT_DEFAULT}</th>
                                    <th>{$smarty.const.TEXT_CURRENCY_RATE}</th>
                                    <th>{$smarty.const.TEXT_CURRENCY_RATE_MARGIN}</th>
                                </tr>                                
                                {foreach $service->currencies->currencies as $code => $_curr}
                                    <tr class="act {if isset($service->currenciesMap[$_curr['id']]) && $service->currenciesMap[$_curr['id']]->status|default:null}active_row{else}hide_row{/if} currency_{$code}">
                                        <td>{$_curr['title']}</td>
                                        <td class="currency_default">{if $service->supplier->currencies_id == $_curr['id']}<span class="check"></span>{/if}</td>
                                        <td class="form-group-sm currency_value">
                                            <label>
                                            {if $service->currenciesMap[$_curr['id']]['currency_value'] != $_curr['_value'] && $service->currenciesMap[$_curr['id']]['use_custom_currency_value']}
                                                {$service->currenciesMap[$_curr['id']]['currency_value']}
                                            {else}
                                                {sprintf($smarty.const.TEXT_USE_DEFAULT_CURRENCY, $_curr['_value'])}
                                            {/if}
                                            </label>
                                        </td>
                                        <td class="form-group-sm currency_margin">
                                        {if $service->currenciesMap[$_curr['id']]['margin_type'] == '+'}
                                            +{$service->currenciesMap[$_curr['id']]['margin_value']}
                                        {else}
                                            {$service->currenciesMap[$_curr['id']]['margin_value']}{$service->currenciesMap[$_curr['id']]['margin_type']}
                                        {/if}
                                        </td>
                                    </tr>
                                {foreachelse}
                                    <tr><td colspan="4">{$smarty.const.TEXT_NOT_CHOOSEN}</td></tr>
                                {/foreach}
                            </table>                                
                            {/if}
                            
                            <div class="currencies_popup popup-box-wrap-page hide_popup" id="currency-table">
                                    <div class=" popup-box-wrap">
                                        <div class="around-pop-up"></div>
                                        <div class="popup-box">
                                        <div class="pop-up-close"></div>
                                        <div class="popup-heading">Set up {$smarty.const.BOX_LOCALIZATION_CURRENCIES}</div>
                                        <div class="popup-content">
                                            <table class="tl-grid currency-table">
                                                <tr>
                                                    <th>{$smarty.const.BOX_LOCALIZATION_CURRENCIES}</th>
                                                    <th>{$smarty.const.TABLE_HEADING_STATUS}</th>
                                                    <th>{$smarty.const.TEXT_DEFAULT}</th>
                                                    <th>{$smarty.const.TEXT_PLATFORM_CURRENCY_RATE}</th>
                                                    <th>{$smarty.const.TEXT_CURRENCY_RATE_MARGIN}</th>
                                                </tr>
                                                {foreach $service->currencies->currencies as $code => $_curr}
                                                    <tr class="act popup_cur_{$code}">
                                                        <td>{$_curr['title']}</td>
                                                        <td>
                                                        {Html::checkbox('suppliers_data[currencies]['|cat:$_curr['id']|cat:'][status]', $service->currenciesMap[$_curr['id']]->status|default:null, ['value' => 1, 'class' => 'currencies_status _on_off', 'data-code' => $code]) }</td>
                                                        <td>{Html::radio('suppliers_data[currencies_id]', ($service->supplier->currencies_id == $_curr['id']), ['value'=> $_curr['id'], 'class' => 'currencies_default', 'title' => {$smarty.const.TEXT_DEFAULT}])}</td>
                                                        <td class="form-group-sm">
                                                            <label>{Html::checkbox('suppliers_data[currencies]['|cat:$_curr['id']|cat:'][use_default]',!$service->currenciesMap[$_curr['id']]['use_custom_currency_value'],['class'=>'form-control js-use_default','style'=>'display:inline-block;width:30px','value'=>'1', 'data-code'=>$code])} {sprintf($smarty.const.TEXT_USE_DEFAULT_CURRENCY,$_curr['_value'])}</label>
                                                            {assign var=style value = 'none;' }
                                                            {if $service->currenciesMap[$_curr['id']]['use_custom_currency_value']}
                                                                {$style = 'block;'}
                                                            {/if}
                                                           {Html::textInput('suppliers_data[currencies]['|cat:$_curr['id']|cat:'][custom_currency_value]',$service->currenciesMap[$_curr['id']]['currency_value'],['class'=>'form-control js-custom-rate','data-default-value'=>$_curr['_value'], 'data-code'=>$code, 'style' => 'display:'|cat:$style])}
                                                        </td>
                                                        <td class="form-group-sm">{Html::textInput('suppliers_data[currencies]['|cat:$_curr['id']|cat:'][margin_value]',$service->currenciesMap[$_curr['id']]['margin_value'],['class'=>'form-control js-rate-margin','style'=>'display:inline-block;width:90px', 'data-code'=>$code])}{Html::dropDownList('suppliers_data[currencies]['|cat:$_curr['id']|cat:'][margin_type]',$service->currenciesMap[$_curr['id']]['margin_type'],['%'=>'%','+'=>'+'],['class'=>'form-control margin_type','style'=>'display:inline-block;width:50px'])}</td>
                                                    </tr>
                                                {foreachelse}
                                                    <tr><td>{$smarty.const.TEXT_NOT_CHOOSEN}</td><td></td><td></td></tr>
                                                {/foreach}
                                            </table>
                                            {if !$service->currencies_editor_simple}
                                            <div class="btn-bar">
                                                <div class="btn-left"><a href="#" class="btn btn-cancel-foot cancel-popup">{$smarty.const.IMAGE_CANCEL}</a></div>
                                                <div class="btn-right"><a href="#" class="btn apply-popup">{$smarty.const.IMAGE_APPLY}</a></div>
                                            </div>
                                            {/if}
                                        </div>
                                        </div>
                                    </div>
                                    </div>
                            {if !$service->currencies_editor_simple}
                            <div class="btn-small-bar after">
                                <div class="btn-right"><a class="btn currency_popup" href="#currency-table" data-class="currency-table">{$smarty.const.BUTTON_ADD_MORE_NEW}</a></div>
                            </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>    
       </div>
       <div class="cbox-right">

           {if (\common\helpers\Acl::checkExtensionAllowed('PurchaseOrders'))}
           <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-personal">
                    <h4>{$smarty.const.BOX_PURCHASE_ORDERS}</h4>
                </div>    
                <div class="widget-content">
{if $service->supplier->hasAttribute('invoice_needed_to_complete_po')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Is invoice needed to complete PO?</label>{Html::checkbox('suppliers_data[invoice_needed_to_complete_po]', $service->supplier->invoice_needed_to_complete_po, ['class' => 'check_on_off', 'uncheck' => 0])}
                        </div>
                    </div>
{/if}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Send Emails To</label>
                            {Html::input('text', 'suppliers_data[send_email]', $service->supplier->send_email, ['class' => "form-control"])}
                         </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Document format</label>
                            {Html::dropDownList('suppliers_data[document_format]', $service->supplier->document_format, ['txt'=>'txt', 'csv'=>'csv', 'pdf'=>'pdf', 'excel'=>'excel'], ['class'=>'form-control'])}
                         </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Auto re-order active</label>
                            {Html::checkbox('suppliers_data[reorder_auto]', $service->supplier->reorder_auto, ['value'=>'1', 'class' => 'js_check_autoorder_status check_on_off', 'uncheck' => 0])}
                        </div>
                    </div>
                    <div class="can_set_autoorder">
                        <div class="w-line-row w-line-row-1">
                            <div class="wl-td">
                                <label>{$smarty.const.TEXT_WAREHOUSE}:</label>
                                {tep_draw_pull_down_menu('suppliers_data[warehouse_id]', \common\helpers\Warehouses::get_warehouses(), $service->supplier->warehouse_id, 'class="form-control form-control-small"')}
                            </div>
                        </div>
                        <div class="w-line-row w-line-row-2">
                            <div class="wl-td">
                                <label>From what amount send</label>
                                {Html::input('text', 'suppliers_data[send_amount]', $service->supplier->send_amount, ['class' => "form-control"])}
                             </div>
                             <div class="wl-td">
                                <label>Orders per day</label>
                                {Html::input('text', 'suppliers_data[send_qty]', $service->supplier->send_qty, ['class' => "form-control"])}
                             </div>
                        </div>
                        <div class="w-line-row w-line-row-1">
                            <label>Auto Dispatch time</label>
                        </div>
                        
                        <div id="opening_hours_list">
                        {foreach $open_hours as $open_key => $open_hour}
                        <div class="w-line-row opening_hours">
                                <div>
                                    <div class="hours_table">
                                                    <label>{$smarty.const.ENTRY_DAYS}<span class="fieldRequired">*</span></label>
                                                    <div class="col-md-10">
                                                    {Html::dropDownList('open_days_'|cat:"$open_key", $open_hour->days, $days, ['class' => 'form-control'])}
                                                    </div>
                                    </div>
                                </div>
                                <div class="time_int"><div class="time_int_1">
                                    <label>{$smarty.const.ENTRY_TIME}<span class="fieldRequired">*</span></label>
                                    <span class="time_title">{$smarty.const.ENTRY_TIME_FROM}</span>{Html::dropDownList('time_from[]', $open_hour->time_from, $hours, ['class' => ''])}</div>
                                    <div class="time_int_2">
                                        <span class="time_title">{$smarty.const.ENTRY_TIME_TO}</span>{Html::dropDownList('time_to[]', $open_hour->time_to, $hours, ['class' => ''])}</div>
                                    <div class="time_int_3">
                                        <a href="javascript:void(0)" onclick="return removeOpenHours(this);" class="btn">-</a>
                                    </div>
                                </div>                              
                            {Html::input('hidden', 'send_hours_id[]', $open_hour->suppliers_dispatch_time_id)}
                            {Html::input('hidden', 'send_hours_key[]', $open_key)}
                        </div>
                        {/foreach}
                        </div>
                        <div class="buttons_hours">
                            <a href="javascript:void(0)" onclick="return addOpenHours();" class="btn">{$smarty.const.BUTTON_ADD_MORE}</a>
                        </div>
                             
                             
                    </div>
                         
                </div>
            </div>
           {/if}
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-personal">
                    <h4>{$smarty.const.TEXT_ADDITIONAL_INFO}</h4>
                </div>    
                <div class="widget-content">
{if $service->supplier->hasAttribute('awrs_no')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>AWRS Number</label>
                            {Html::textInput('suppliers_data[awrs_no]', $service->supplier->awrs_no, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('sage_code')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Sage Code</label>
                            {Html::textInput('suppliers_data[sage_code]', $service->supplier->sage_code, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('payment_delay')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Payment Delay</label>
                            {Html::textInput('suppliers_data[payment_delay]', $service->supplier->payment_delay, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
{if $service->supplier->hasAttribute('supply_delay')}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>Supply Delay</label>
                            {Html::textInput('suppliers_data[supply_delay]', $service->supplier->supply_delay, ['class' => "form-control"])}
                        </div>
                    </div>
{/if}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_CONDITION}</label>
                            {Html::textInput('suppliers_data[condition]', $service->supplier->condition, ['class' => "form-control"])}
                         </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_CONDITION_DESCRIPTION}</label>
                            {Html::textarea('suppliers_data[condition_description]', $service->supplier->condition_description, ['class' => "form-control"])}
                        </div>
                    </div>
                    {if $es = \common\helpers\Acl::checkExtensionAllowed('EventSystem', 'allowed')}
                        {$es::partner()->exec('getPartnerAdditionalFields', [$service->supplier->suppliers_id])}
                    {/if}
                </div>
            </div>
            {if $service->allow_change_auth}
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-personal">
                    <h4>{$smarty.const.TEXT_AUTHENTIFICATION_DATA}</h4>
                </div>    
                <div class="widget-content">
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_EMAIL_ADDRESS}</label>
                            {Html::input('text', 'suppliers_data[email_address]', $service->supplier->authData->email_address|default:null, ['class' => "form-control"])}
                         </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td input-group">
                            <label>{$smarty.const.ENTRY_PASSWORD}</label>
                            {Html::textInput('suppliers_data[password]', $service->supplier->authData->password|default:null, ['class' => "form-control pwd-field"])}
                            <div class="input-group-addon" id="lnPwdGenerate" title="{$smarty.const.TEXT_GENERATE|escape:'html'}"><i class="icon-refresh"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            {/if}
       </div>
    </div>
    {if $sInfo}
    <div>
       {include file="../../../backend/themes/basic/categories/suppliers-price-data.tpl" supplier_data=$sInfo->supplier_data singleSupplier=1 disableFormulaEditor=1 mayEditCost = $mayEditCost}
    </div>
    {/if}
    <div class="btn-bar">
        <div class="btn-left">{Html::a(IMAGE_CANCEL, $app->urlManager->createUrl([$cancelPath, 'suppliers_id'=>$service->supplier->suppliers_id]), ['class' => 'btn btn-cancel btn-no-margin' ])}</div>
        <div class="btn-right">
            {if $service->supplier->suppliers_id}
                {Html::submitInput(IMAGE_UPDATE, ['class' => 'btn btn-confirm' ])}
            {else}
                {Html::submitInput(IMAGE_NEW, ['class' => 'btn btn-confirm' ])}
            {/if}
        </div>
    </div>   
  </div>
  {Html::endForm()}
</div>
<div id="opening_hours_template" style="display: none;">
    <div class="w-line-row opening_hours">
        <div>
            <div class="hours_table">
                <label>{$smarty.const.ENTRY_DAYS}<span class="fieldRequired">*</span></label>
                <div class="col-md-10">
                {Html::dropDownList('open_days_', '', $days, ['class' => 'form-control'])}
                </div>
            </div>
        </div>
        <div class="time_int">
            <div class="time_int_1">
                <label>{$smarty.const.ENTRY_TIME}<span class="fieldRequired">*</span></label>
                <span class="time_title">{$smarty.const.ENTRY_TIME_FROM}</span>{Html::dropDownList('time_from[]', '', $hours, ['class' => ''])}
            </div>
            <div class="time_int_2">
                <span class="time_title">{$smarty.const.ENTRY_TIME_TO}</span>{Html::dropDownList('time_to[]', '', $hours, ['class' => ''])}
            </div>
            <div class="time_int_3">
                <a href="javascript:void(0)" onclick="return removeOpenHours(this);" class="btn">-</a>
            </div>
        </div>                              
        {Html::input('hidden', 'send_hours_id[]', '')}
        {Html::input('hidden', 'send_hours_key[]', '')}
    </div>
</div>
<script>
var nextKey = {$count_open_hours};
function removeOpenHours(obj) {
    $(obj).parent('div').parent('div').parent('div.opening_hours').remove();
    return false;
}
function addOpenHours() {
    nextKey = nextKey +1;
    $('#opening_hours_template').find('select[name*="open_days"]').attr('name', 'open_days_'+nextKey+'[]');
    $('#opening_hours_template').find('input[name="send_hours_key[]"]').val(nextKey);
    $('#opening_hours_list').append($('#opening_hours_template').html());
    $("form select[data-role=multiselect-new]").attr('data-role', 'multiselect');
    $("form select[data-role=multiselect]").multiselect({
        selectedList: 1 // 0-based index
     });
    $('form .pt-time-new').ptTimeSelect();
    
    return false;
}
  $(document).ready(function(){
    
    function initBoot(){
      $(".currencies_default, .currencies_status").bootstrapSwitch(
            {
                onText: "{$smarty.const.SW_ON}",
                offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px',            
            }
        );  
    }
    
    $(".check_on_off").bootstrapSwitch(
        {
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',            
        }
    );
    
    var clone = $('#currency-table').clone(true, true);
    
    redrawList = function(){
        $.each($('#currency-table tbody').children(), function(i, e){
            let iStatus = $(e).find('.currencies_status');
            if (iStatus.prop('checked')){
                $('.currency-list tr.currency_'+iStatus.data('code')).removeClass('hide_row').addClass('active_row');
                let ch = iStatus.parents('tr').find('.margin_type').val();
                ch = (ch == '+') ? ch + iStatus.parents('tr').find('.js-rate-margin').val() : iStatus.parents('tr').find('.js-rate-margin').val() + ch;                
                $('.currency-list tr.currency_'+iStatus.data('code')).find('.currency_margin').html(ch);
                if (!iStatus.parents('tr').find('.js-use_default').prop('checked')){
                    $('.currency-list tr.currency_'+iStatus.data('code')).find('.currency_value label').html(iStatus.parents('tr').find('.js-custom-rate').val());
                } else {
                    $('.currency-list tr.currency_'+iStatus.data('code')).find('.currency_value label').html(iStatus.parents('tr').find('.js-use_default').parent().text());
                }
            } else {
                $('.currency-list tr.currency_'+iStatus.data('code')).removeClass('active_row').addClass('hide_row');
            }
        })
    }
    
    $('.currency_popup').click(function(e){
        e.preventDefault();
        $('#currency-table').removeClass('hide_popup');
        initBoot();
    })
    
    function apply(){
        $(".currencies_default, .currencies_status").bootstrapSwitch('destroy');
        $('#currency-table').addClass('hide_popup');
        clone = $('#currency-table').clone(true, true);
    }
    
    $('body').on('click', '.pop-up-close, .apply-popup', function(e){
        e.preventDefault();
        apply();
        redrawList();
    })
    
    $('body').on('click', '.cancel-popup', function(e){
        e.preventDefault();
        $(".currencies_default, .currencies_status").bootstrapSwitch('destroy');
        $('#currency-table').replaceWith(clone);
        $('#currency-table').addClass('hide_popup');
    })
    
    $('body').on('click switchChange.bootstrapSwitch', '.currencies_default', function(e){
        $(e.target).parents('tr').find('.currencies_status').bootstrapSwitch('state', true);
    })
    
    $('body').on('click switchChange.bootstrapSwitch', '.currencies_status', function(e){
        if ($(e.target).parents('tr').find('.currencies_default').prop('checked')){
            $(e.target).parents('tr').find('.currencies_status').bootstrapSwitch('state', true);
        }
    })
    
    $('body').on('click, change', '.js-use_default', function(e){        
        $('.js-custom-rate[data-code='+$(e.target).data('code')+']').toggle();        
    })   
    
    
    {if $service->allow_change_price_formula}
      $('#suppliers_management_data').on('click', '.js-price-formula', function(event){
          var field = $(this).data('formula-rel');
          if ( !field ) {
              field = $(event.target).parents('.js-price-formula-group').find('.js-price-formula-data').attr('name');
              field = 'input[name="'+field+'"]';
          }
          var allowed_params = $(this).data('formula-allow-params')||'';

          bootbox.dialog({ message: '<iframe src="{$app->urlManager->createUrl(['popups/price-formula-editor','s'=>(float)microtime()])}&formula_input='+encodeURIComponent(field)+'&allowed_params='+encodeURIComponent(allowed_params)+'" width="900px" height="420px" style="border:0"/>' });
          bootbox.setDefaults( { size:'large', onEscape:true, backdrop:true });
      });

      window.priceFormulaRetrieve = function (inputSelector){
          var jsonString = $(inputSelector).val();
          if ( jsonString ) {
              return JSON.parse(jsonString);
          }
          return { };
      };

      window.priceFormulaUpdate = function (inputSelector, formulaObject ) {
          var $targetDataInput = $(inputSelector);
          $targetDataInput.val( JSON.stringify(formulaObject) );
          $targetDataInput.parents('.js-price-formula-group').find('.js-price-formula-text').val($.trim(formulaObject.text));
          bootbox.hideAll();
      };
    {/if}
      
    {if $service->allow_change_auth}
    $('#lnPwdGenerate').on('click',function(){
        $.getJSON('{\Yii::$app->urlManager->createUrl($generatePath)}',function( data ) {
            if (data.password) {
                $('.pwd-field').val(data.password);
            }
        });
    });
    {/if}

    var fn_set_autoorder_switch = function(state) {
        if (state) {
            $('.can_set_autoorder').show();
        }else {
            $('.can_set_autoorder').hide();
        }
    }
    $('.js_check_autoorder_status').on('click switchChange.bootstrapSwitch',function(){
        fn_set_autoorder_switch(this.checked);
    });
    $('.js_check_autoorder_status').each(function() {
        fn_set_autoorder_switch.apply(this,[this.checked]);
    });
    $('.js_check_autoorder_status').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    
    
  });

</script>
