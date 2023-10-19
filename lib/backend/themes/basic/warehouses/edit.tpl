{use class="yii\helpers\Html"}
{use class="common\helpers\Acl"}
{\backend\assets\BDPAsset::register($this)|void}
{\backend\assets\BDTPAsset::register($this)|void}
<!--=== Page Content ===-->
<div id="warehouses_management_data">
<!--===Customers List ===-->
<form name="save_item_form" id="save_item_form" enctype="multipart/form-data" onSubmit="return saveItem();">
<div class="box-wrap">
    <div class="create-or-wrap after create-cus-wrap">
        <div class="cbox-left">
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-personal"><h4>{$smarty.const.CATEGORY_GENERAL}</h4></div>
                <div class="widget-content">
                    {if $have_one_or_more_warehouse}
                    {if !$isMultiPlatforms}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_STATUS}</label>
                            {Html::checkbox('status', $pInfo->status|default:null,  array_merge(['value'=>'1', 'class' => 'js_check_status'], $checkbox_default_warehouse_attr))}
                        </div>
                    </div>
                    {/if}
                    {else}
                        {Html::hiddenInput('status','1')}
                    {/if}
                    {if $es = \common\helpers\Acl::checkExtensionAllowed('EventSystem', 'allowed')}
                        {$es::venue()->exec('showOwners', [$pInfo->warehouse_id])}
                        {$es::venue()->exec('getVenueAdditionalFields', [$pInfo->warehouse_id])}
                    {else}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_WAREHOUSE_OWNER}<span class="fieldRequired">*</span></label>{Html::input('text', 'warehouse_owner', $pInfo->warehouse_owner|default:null, ['class' => 'form-control', 'required' => true])}
                        </div>
                    </div>
                    {/if}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_WAREHOUSE_NAME}<span class="fieldRequired">*</span></label>{Html::input('text', 'warehouse_name', $pInfo->warehouse_name|default:null, ['class' => 'form-control', 'required' => true])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_IS_STORE}</label>
                            {Html::checkbox('is_store', $pInfo->is_store|default:null, ['value'=>'1', 'class' => 'js_check_is_store'])}
                        </div>
                    </div>
                    {if $have_one_or_more_warehouse}
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_IS_DEFAULT_WAREHOUSE}</label>
                            {Html::checkbox('is_default', !!($pInfo->is_default|default:null), array_merge(['value'=>'1', 'class' => 'js_check_default_warehouse'], $checkbox_default_warehouse_attr))}
                            {Html::hiddenInput('present_is_default','1')}
                        </div>
                    </div>
                    {else}
                        {Html::hiddenInput('is_default','1')}
                        {Html::hiddenInput('present_is_default','1')}
                    {/if}
                    <div class="w-line-row w-line-row-1 w-line-row-req">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                    </div>
                </div>
            </div>

            {if $isMultiPlatforms}
            <div class="widget box box-no-shadow">
                <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                    <thead>
                        <tr>
                            <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
                            <th>{$smarty.const.TABLE_HEADING_STATUS}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $platforms as $platform}
                        <tr>
                            <td>{$platform['text']}</td>
                            <td>
                                {Html::checkbox('platform_status['|cat:$platform['id']|cat:']', {$platform['status']}, array_merge(['value' => '1', 'class' => 'js_check_status'], $checkbox_default_warehouse_attr))}
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            {/if}

            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-contact"><h4>{$smarty.const.CATEGORY_CONTACT}</h4></div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_EMAIL_ADDRESS}<span class="fieldRequired">*</span></label>{Html::input('email', 'warehouse_email_address', $pInfo->warehouse_email_address|default:null, ['class' => 'form-control', 'required' => true, 'data-pattern' => 'email'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_TELEPHONE_NUMBER}</label>{Html::input('text', 'warehouse_telephone', $pInfo->warehouse_telephone|default:null, ['class' => 'form-control'])}
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_LANDLINE}</label>{Html::input('text', 'warehouse_landline', $pInfo->warehouse_landline|default:null, ['class' => 'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-1 w-line-row-req">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="cbox-right">
            <div class="widget box box-no-shadow" style="min-height: 481px;">
                <div class="widget-header widget-header-address"><h4>{$smarty.const.CATEGORY_ADDRESS}</h4></div>
                <div class="widget-content">
                    
                    <div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_COMPANY}</label>{Html::input('text', 'entry_company[]', $addresses->entry_company|default:null, ['class' => 'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_BUSINESS}</label>{Html::input('text', 'entry_company_vat[]', $addresses->entry_company_vat|default:null, ['class' => 'form-control'])}
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_BUSINESS_REG_NUMBER}</label>{Html::input('text', 'entry_company_reg_number[]', $addresses->entry_company_reg_number|default:null, ['class' => 'form-control'])}
                        </div>
                    </div>
            
                    
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_POST_CODE}</label>{Html::input('text', 'entry_postcode[]', $addresses->entry_postcode|default:null, ['class' => 'form-control'])}
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_STREET_ADDRESS}</label>{Html::input('text', 'entry_street_address[]', $addresses->entry_street_address|default:null, ['class' => 'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_SUBURB}</label>{Html::input('text', 'entry_suburb[]', $addresses->entry_suburb|default:null, ['class' => 'form-control'])}
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_CITY}</label>{Html::input('text', 'entry_city[]', $addresses->entry_city|default:null, ['class' => 'form-control'])}
                        </div>
                    </div>
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{field_label const="ENTRY_STATE" configuration="ACCOUNT_STATE"}</label>
                            <div class="f_td_state">
                                {Html::input('text', 'entry_state[]', $addresses->entry_state|default:null, ['class' => 'form-control', 'id' => "selectState"])}
                            </div>
                        </div>
                        <div class="wl-td">
                            <label>{$smarty.const.ENTRY_COUNTRY}<span class="fieldRequired">*</span></label>{Html::dropDownList('entry_country_id[]', $addresses->entry_country_id|default:null, \common\helpers\Country::new_get_countries('', false), ['class' => 'form-control', 'id' => "selectCountry", 'required' => true])}
                        </div>
                    </div>

                    <!--<div class="w-line-row w-line-row-1">
                        <div class="wl-td">
                            <input type="checkbox" /> <b>Make default address</b>
                        </div>
                    </div>!-->
                </div>
                <div class="w-line-row w-line-row-1 w-line-row-req w-line-row-abs">
                          <span style="color: #f2353c; margin: 22px 0 0; display: block;">{$smarty.const.ENTRY_REQUIRED_FIELDS}</span>
                    </div>
            </div>
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-shipping-m"><h4>{$smarty.const.CATEGORY_EXTRA_DATA}</h4></div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-2">
                        <div class="wl-td">
                            <label>{$smarty.const.TEXT_ADDITIONAL_SHIPPING_CHARGE}</label>
                            {Html::input('text', 'shipping_additional_charge', $pInfo->shipping_additional_charge|default:null, ['class' => 'form-control'])}
                        </div>
                    </div>
                </div>
            </div>

            {Html::input('hidden', 'warehouses_address_book_id[]', $addresses->warehouses_address_book_id|default:null)}
        </div>        
    </div>
        <div class="create-or-wrap after create-cus-wrap yes_store" {if $pInfo->is_store|default:0 == 0}style="display: none;"{/if}>
        <div class="cbox-left">
    <div class="widget box box-no-shadow" style="min-height:183px;">
        <div class="widget-header widget-header-company"><h4>{$smarty.const.CATEGORY_OPEN_HOURS}</h4></div>
        <div class="widget-content">
            <div id="opening_hours_list">
            {foreach $open_hours as $open_key => $open_hour}
            <div class="w-line-row opening_hours">
                    <div>
                        <div class="hours_table">
                                        <label>{$smarty.const.ENTRY_DAYS}<span class="fieldRequired">*</span></label>
                                        <div class="col-md-10">
                                        {Html::dropDownList('open_days_'|cat:"$open_key", $open_hour->open_days|default:null, $days, ['class' => 'multiselect form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                                        </div>
                        </div>
                    </div>
                    <div class="time_int ps-3"><div class="time_int_1">
                        <label>{$smarty.const.ENTRY_TIME}<span class="fieldRequired">*</span></label>
                        <span class="time_title">{$smarty.const.ENTRY_TIME_FROM}</span>{Html::input('text', 'open_time_from[]', $open_hour->open_time_from|default:null, ['class' => 'pt-time form-control'])}</div>
                        <div class="time_int_2">
                            <span class="time_title">{$smarty.const.ENTRY_TIME_TO}</span>{Html::input('text', 'open_time_to[]', $open_hour->open_time_to|default:null, ['class' => 'pt-time form-control'])}</div>
                        <div class="time_int_3">
                            <a href="javascript:void(0)" onclick="return removeOpenHours(this);" class="btn">-</a>
                        </div>
                    </div>                              
                {Html::input('hidden', 'warehouses_open_hours_id[]', $open_hour->warehouses_open_hours_id|default:null)}
                {Html::input('hidden', 'warehouses_open_hours_key[]', $open_key)}
            </div>
            {/foreach}
            </div>
            <div class="buttons_hours">
                <a href="javascript:void(0)" onclick="return addOpenHours();" class="btn">{$smarty.const.BUTTON_ADD_MORE}</a>
            </div>
        </div>
    </div>

  </div>
  <div class="cbox-right">

    </div>
  </div>    
                    
</div>
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
</div>
{Html::input('hidden', 'id', $pInfo->warehouse_id|default:null)}
</form>
<div id="opening_hours_template" style="display: none;">
    <div class="w-line-row opening_hours">
        <div>
            <div class="hours_table">
                <label>{$smarty.const.ENTRY_DAYS}<span class="fieldRequired">*</span></label>
                <div class="col-md-10">
                {Html::dropDownList('open_days_', '', $days, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect-new'])}
                </div>
            </div>
        </div>
        <div class="time_int ps-3">
            <div class="time_int_1">
                <label>{$smarty.const.ENTRY_TIME}<span class="fieldRequired">*</span></label>
                <span class="time_title">{$smarty.const.ENTRY_TIME_FROM}</span>{Html::input('text', 'open_time_from[]', '', ['class' => 'pt-time-new form-control'])}
            </div>
            <div class="time_int_2">
                <span class="time_title">{$smarty.const.ENTRY_TIME_TO}</span>{Html::input('text', 'open_time_to[]', '', ['class' => 'pt-time-new form-control'])}
            </div>
            <div class="time_int_3">
                <a href="javascript:void(0)" onclick="return removeOpenHours(this);" class="btn">-</a>
            </div>
        </div>                              
        {Html::input('hidden', 'warehouses_open_hours_id[]', '')}
        {Html::input('hidden', 'warehouses_open_hours_key[]', '')}
    </div>
</div>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fileupload/jquery.fileupload.js"></script>
<script>
$('#selectState').autocomplete({
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
var nextKey = {$count_open_hours};
function removeOpenHours(obj) {
    $(obj).parent('div').parent('div').parent('div.opening_hours').remove();
    return false;
}
var timePicker = {
    display: {
        viewMode: 'clock',
        components: {
            calendar: false,
        },
    },
    localization: {
        format: 'h:mm T'
    }
};
function addOpenHours() {
    nextKey = nextKey +1;
    $('#opening_hours_template').find('select[name*="open_days"]').attr('name', 'open_days_'+nextKey+'[]');
    $('#opening_hours_template').find('input[name="warehouses_open_hours_key[]"]').val(nextKey);
    const $newLine = $($('#opening_hours_template').html());
    $('#opening_hours_list').append($newLine);
    $("form select[data-role=multiselect-new]").multipleSelect().attr('data-role', 'multiselect');
    $('form .pt-time-new').tempusDominus(timePicker);
    
    return false;
}
function saveItem() {
    $.post("{$app->urlManager->createUrl('warehouses/submit')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            $('#warehouses_management_data').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}
function backStatement() {
    window.history.back();
    return false;
}
$(document).ready(function(){

    $('.pt-time').tempusDominus(timePicker);

    $("select[data-role=multiselect]").multipleSelect();
        
    $(window).resize(function(){

    })
    $(window).resize();

    $('.js_check_status').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    
    $('.js_check_default_warehouse').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    
    $('.js_check_is_store').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    var fn_store_enable_switch = function(state) {
        if (state) {
            $('.no_store').hide();
            $('.yes_store').show();
        } else {
            $('.no_store').show();
            $('.yes_store').hide();
        }
    }
    $('.js_check_is_store').on('click switchChange.bootstrapSwitch',function(){
        fn_store_enable_switch(this.checked);
    });
    $('.js_check_is_store').each(function() {
        fn_store_enable_switch.apply(this,[this.checked]);
    });

    $('.js-url_status').bootstrapSwitch({
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    });
    $('.js-warehouse-urls').on('add_row',function(){
        var skelHtml = $(this).find('tfoot').html();
        var $body = $(this).find('tbody');
        var counter = parseInt($body.attr('data-rows-count'),10)+1;
        $body.attr('data-rows-count',counter);
        skelHtml = skelHtml.replace(/_unhide_/g,'',skelHtml);
        skelHtml = skelHtml.replace(/%idx%/g, counter,skelHtml);
        $body.append(skelHtml);
        $body.find('.js-url_status_skel').removeClass('js-url_status_skel').addClass('js-url_status').bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        });
    });
    $('.js-warehouse-urls').on('click', '.js-remove-warehouse-url',function(event){
        $(event.target).parents('tr').remove();
    });

    $('.js-add-warehouse-url').on('click',function(){
        $('.js-warehouse-urls').trigger('add_row');
    });
});
</script>

</div>
<!-- /Page Content -->
