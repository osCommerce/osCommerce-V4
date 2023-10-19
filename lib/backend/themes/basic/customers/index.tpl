<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--=== Page Content ===-->

<!--===Customers List ===-->
<div class="widget box box-wrapp-blue filter-wrapp">
    <div class="widget-header filter-title">
        <h4>{$smarty.const.TEXT_FILTER}</h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
    </div>
    <div class="widget-content">
        
            <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                {include file="../filters/customers.tpl"}
                <div class="filters_btn">
                    <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>&nbsp;
                    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
                </div>
            </form>
        
    </div>
</div>
<div class="order-wrap">
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="btn-wr after btn-wr-top disable-btn data-table-top-left">
{if \common\helpers\Acl::rule(['ACL_CUSTORER', 'IMAGE_DELETE'])}
                        <a href="javascript:void(0)" onclick="deleteSelectedOrders();" class="btn btn-del btn-no-margin">{$smarty.const.TEXT_DELETE_SELECTED}</a>
{/if}
{if $cfExt = \common\helpers\Acl::checkExtensionAllowed('CustomerFlag')}
    {$cfExt::customerBatchButtons()}
{/if}
                </div>
                <table class="table table-colored table-checkable table-ordering table-hover table-responsive table-bordered datatable tab-cust tabl-res double-grid"
                       checkable_list="1,2,3,4,6,7,8" order_list="4" order_by="desc" data_ajax="customers/customerlist">
                    <thead>
                        <tr>
                            {foreach $app->controller->view->customersTable as $tableItem}
                                <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                {/foreach}
                        </tr>
                    </thead>
                </table>
                <div class="btn-wr after disable-btn">
                    <div>
{if \common\helpers\Acl::rule(['ACL_CUSTORER', 'IMAGE_DELETE'])}
                        <a href="javascript:void(0)" onclick="deleteSelectedOrders();" class="btn btn-del btn-no-margin">{$smarty.const.TEXT_DELETE_SELECTED}</a>
{/if}
{if $cfExt = \common\helpers\Acl::checkExtensionAllowed('CustomerFlag')}
    {$cfExt::customerBatchButtons()}
{/if}
                    </div>
                    <div>
                    </div>
                </div>
            </div>

        </div>
    </div>
				<!-- /Customers List -->
                                
<script type="text/javascript">
function getTableSelectedIds() {
    var selected_messages_ids = [];
    var selected_messages_count = 0;
    $('input:checkbox:checked.uniform').each(function(j, cb) {
        var aaa = $(cb).closest('td').find('.cell_identify').val();
        if (typeof(aaa) != 'undefined') {
            selected_messages_ids[selected_messages_count] = aaa;
            selected_messages_count++;
        }
    });
    return selected_messages_ids;
}
function getTableSelectedCount() {
    var selected_messages_count = 0;
    $('input:checkbox:checked.uniform').each(function(j, cb) {
        var aaa = $(cb).closest('td').find('.cell_identify').val();
        if (typeof(aaa) != 'undefined') {
            selected_messages_count++;
        }
    });
    return selected_messages_count;
}
function deleteSelectedOrders() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        
        bootbox.dialog({
                message: "{$smarty.const.TEXT_DELETE_SELECTED} <span class=\"lowercase\">{$smarty.const.TEXT_CUSTOMERS}?</span>",
                title: "{$smarty.const.TEXT_DELETE_SELECTED} <span class=\"lowercase\">{$smarty.const.TEXT_CUSTOMERS}</span>",
                buttons: {
                        success: {
                                label: "Yes",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("customers/customersdelete", { 'selected_ids' : selected_ids, 'delete_reviews' : '1' }, function(data, status){
                                        if (status == "success") {
                                            resetStatement();
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
                                }
                        },
                        danger: {
                                label: "No",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("customers/customersdelete", { 'selected_ids' : selected_ids, 'delete_reviews' : '0' }, function(data, status){
                                        if (status == "success") {
                                            resetStatement();
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
                                }
                        },
                        main: {
                                label: "Cancel",
                                className: "btn-cancel",
                                callback: function() {
                                        //console.log("Primary button");
                                }
                        }
                }
        });
    }
    return false;
}
function passFormAfretShow() {
    $('input[type="password"]').showPassword(); 
    
    $('.generate_password').on('click', function(){
            $.get('{$app->urlManager->createUrl(['adminaccount/generate-password', 'frontend' => 1])}', function(data){
                $('input[name="change_pass"]').val(data);
                $('input[name="change_pass"]').trigger('keyup');
                if($('input[name="change_pass"]').attr('type') == 'password'){
                  $('.eye-password').click();
                }
            }, 'json')
            return false;
    });
                
    var form = $('#passw_form');
    $('input', form).validate();
    form.on('submit', function(){
        if ($('.required-error', form).length === 0){
            return check_passw_form();
        }
        return false;
    });
}
function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}
function resetFilter() {
    $('#filterForm').trigger('filters_reset');
    $('select[name="by"]').val('');
    $('input[name="search"]').val('');
    $("#presel").prop("checked", true);
    $("#exact").prop("checked", false);
    $('.js_platform_checkboxes').prop("checked", false);
    $('.js_department_checkboxes').prop("checked", false);
    $('select[name="interval"]').val('');
    $('input[name="from"]').val('');
    $('input[name="to"]').val('');
    $('select[name="status"]').val('');
    $('input[name="country"]').val('');
    $('input[name="state"]').val('');
    $('input[name="city"]').val('');
    $('input[name="group"]').val('');
    $('input[name="company"]').val('');
    $('select[name="title"]').val('');
    $('select[name="newsletter"]').val('');
    $("#row_id").val(0);
    resetStatement();
    return false;  
}
function applyFilter() {
    resetStatement();
    return false;    
}
function switchOffCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-down')) {
        $("#"+id).click();
    }
}
function switchOnCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-up')) {
        $("#"+id).click();
    }
}
function resetStatement() {
    setFilterState();
    $("#order_management").hide();
    switchOnCollapse('customers_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    return false;
}
function onClickEvent(obj, table) {
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();
    //$("#order_management").hide();
    var customers_id = $(obj).find('input.cell_identify').val();
    $.post("customers/customeractions", { 'customers_id' : customers_id }, function(data, status){
        if (status == "success") {
            $('#customer_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
}
function onUnclickEvent(obj, table) {
    //$("#order_management").hide();
}
function editCustomer(customers_id) {
    $("#order_management").hide();
    $.post("customers/customeredit", { 'customers_id' : customers_id }, function(data, status){
        if (status == "success") {
            $('#customer_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOffCollapse('customers_list_collapse');
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
    return false;
}
function check_form() {
    //ajax save
    $("#order_management").hide();
    var customers_id = $( "input[name='customers_id']" ).val();
    $.post("customers/customersubmit", $('#customers_edit').serialize(), function(data, status){
        if (status == "success") {
            //$('#customer_management_data').html(data);
            //$("#order_management").show();
            switchOnCollapse('customers_list_collapse');
            var table = $('.table').DataTable();
            table.draw(false);
            setTimeout('$(".cell_identify[value=\''+customers_id+'\']").click();', 500);
            //$(".cell_identify[value='"+customers_id+"']").click();
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
    //$('#customer_management_data').html('');
    return false;
}
function deleteCustomer() {
    $("#order_management").hide();
    $.post("customers/customerdelete", $('#customers_edit').serialize(), function(data, status){
        if (status == "success") {
            resetStatement()
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function confirmDeleteCustomer(customers_id) {
    $("#order_management").hide();
    $.post("customers/confirmcustomerdelete", { 'customers_id' : customers_id }, function(data, status){
        if (status == "success") {
            $('#customer_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOffCollapse('customers_list_collapse');
        } else {
            alert("Request error.");
            //$("#order_management").hide();
        }
    },"html");
    return false;
}

function check_passw_form(length){
  if (document.forms.passw_form.change_pass.value.length < length){
    alert("New password must have at least " + length + " characters.");
    return false;       
  } else {
    switchOffCollapse('customer_management_bar');
    $.post('customers/generatepassword', $('form[name=passw_form]').serialize(), function(data, status){
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
}

$('.fields_style select').change(function(){
    $(this).focusout();
});
$(document).ready(function() {
    
    //===== Date Pickers  =====//
    $( ".datepicker" ).datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths:true,
            autoSize: false,
            dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}'
    });
    
  $(window).resize(function () {
    setTimeout(function () {
      var height_box = $('.order-box-list').height() + 2;
      $('#order_management .widget.box').css('min-height', height_box);
    }, 800);
  })
  $(window).resize();
  
  $('.f-row.act_row input[type="text"]').prop('disabled', false);
    $('.f-row.act_row select').prop('disabled', false);
    
    $('input[name="date"]').click(function() { 
        if($(this).is(':checked')){ 
            $(this).parents().siblings('div.f-row').removeClass('act_row');
            $(this).parents('.f-row').addClass('act_row');
            $('.f-row input[type="text"]').prop('disabled', true);
            $('.f-row select').prop('disabled', true);
            $('.f-row.act_row input[type="text"]').prop('disabled', false);
            $('.f-row.act_row select').prop('disabled', false);
        }
    });  
  
    $('th.checkbox-column .uniform').click(function() {
        if($(this).is(':checked')){
            $('input:checkbox.uniform').each(function(j, cb) {
                $(this).prop('checked', true).uniform('refresh');
            });
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('input:checkbox:checked.uniform').each(function(j, cb) {
                $(this).prop('checked', false).uniform('refresh');
            });
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    });

    $('#selectCountry').autocomplete({
        source: "customers/countries",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_country',
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
            $('input[name="state"]').prop('disabled', true);
            if(ui.item.value != null){ 
                $('input[name="state"]').prop('disabled', false);
            }
            $('input[name="city"]').prop('disabled', true);
            if(ui.item.value != null){ 
                $('input[name="city"]').prop('disabled', false);
            }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
    
    $('#selectState').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "customers/state",
                dataType: "json",
                data: {
                    term : request.term,
                    country : $("#selectCountry").val()
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
            /*$('input[name="city"]').prop('disabled', true);
            if(ui.item.value != null){ 
                $('input[name="city"]').prop('disabled', false);
            }*/
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
    
    $('#selectCity').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "customers/city",
                dataType: "json",
                data: {
                    term : request.term,
                    country : $("#selectCountry").val(),
                    state :  $("#selectState").val(),
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_city',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
    
{if $app->controller->view->filters->showGroup}
    $('#selectGroup').autocomplete({
        source: "customers/group",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_group',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });
{/if}
    
    
    $('#selectCompany').autocomplete({
        source: "customers/company",
        minLength: 0,
        autoFocus: true,
        delay: 0,
        appendTo: '.f_td_company',
        open: function (e, ui) {
          if ($(this).val().length > 0) {
            var acData = $(this).data('ui-autocomplete');
            acData.menu.element.find('a').each(function () {
              var me = $(this);
              var keywords = acData.term.split(' ').join('|');
              me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
            });
          }
        }
    }).focus(function () {
      $(this).autocomplete("search");
    });

    var $platforms = $('.js_platform_checkboxes');
    var check_platform_checkboxes = function(){
        var checked_all = true;
        $platforms.not('[value=""]').each(function () {
            if (!this.checked) checked_all = false;
        });
        $platforms.filter('[value=""]').each(function() {
            this.checked = checked_all
        });
    };
    check_platform_checkboxes();
    $platforms.on('click',function(){
        var self = this;
        if (this.value=='') {
            $platforms.each(function(){
                this.checked = self.checked;
            });
        }else{
            var checked_all = this.checked;
            if ( checked_all ) {
                $platforms.not('[value=""]').each(function () {
                    if (!this.checked) checked_all = false;
                });
            }
            $platforms.filter('[value=""]').each(function() {
                this.checked = checked_all
            });
        }
    });
    {if $departments}
    var $departments = $('.js_department_checkboxes');
    var check_department_checkboxes = function(){
        var checked_all = true;
        $departments.not('[value=""]').each(function () {
            if (!this.checked) checked_all = false;
        });
        $departments.filter('[value=""]').each(function() {
            this.checked = checked_all
        });
    };
    check_department_checkboxes();
    $departments.on('click',function(){
        var self = this;
        if (this.value=='') {
            $departments.each(function(){
                this.checked = self.checked;
            });
        }else{
            var checked_all = this.checked;
            if ( checked_all ) {
                $departments.not('[value=""]').each(function () {
                    if (!this.checked) checked_all = false;
                });
            }
            $departments.filter('[value=""]').each(function() {
                this.checked = checked_all
            });
        }
    });
    {/if}

});

{if $cfExt = \common\helpers\Acl::checkExtensionAllowed('CustomerFlag')}
function sendCustomerFlag(id, flag_state) {
    var selected_ids = [];
    selected_ids[0] = id;
    if (typeof flag_state == "undefined") flag_state = 0;
    sendCustomersFlag(selected_ids, flag_state);
}
function flagSelectedCustomers() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        sendCustomersFlag(selected_ids, 0);
    }
    return false;
}
function sendCustomersFlag(selected_ids, flag_state) {
    bootbox.dialog({
        message: '{foreach $cfExt::flagsList(true) as $flag}<label class="{$flag['class']}" style="{$flag['style']}">{\yii\helpers\Html::radio('o_flag', false, ['value' => $flag['id']])|escape:'javascript'}<span>{$flag['text']}</span></label><br>{/foreach}',
        title: "{$smarty.const.TEXT_SET_FLAG}",
        buttons: {
            success: {
                label: "{$smarty.const.IMAGE_SAVE}",
                className: "btn",
                callback: function() {
                    $.post("{\yii\helpers\Url::toRoute(['update-customer-flag'])}", { 'type':'flag', 'selected_ids' : selected_ids, 'o_flag' : $('input:checked[name="o_flag"]').val() }, function(data, status){
                        if (status == "success") {
                            resetStatement();
                        } else {
                            alert("Request error.");
                        }
                    },"html");
                }
            },
            main: {
                label: "Cancel",
                className: "btn-cancel",
                callback: function() {

                }
            }
        }
    });
    setTimeout(function(){
        $('input[name="o_flag"][value="'+flag_state+'"]').prop('checked', 'checked');
    }, 200);
}
function sendCustomerMarker(id, marker_state) {
    var selected_ids = [];
    selected_ids[0] = id;
    sendCustomersMarker(selected_ids, marker_state);
}
function markerSelectedCustomers() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        sendCustomersMarker(selected_ids, 0);
    }
    return false;
}
function sendCustomersMarker(selected_ids, marker_state) {

    bootbox.dialog({
        message: '{foreach $cfExt::markersList(true) as $marker}<label class="{$marker['class']}" style="{$marker['style']}">{\yii\helpers\Html::radio('o_marker', false, ['value' => $marker['id']])|escape:'javascript'}<span>{$marker['text']}</span></label><br>{/foreach}',
        title: "{$smarty.const.EXT_CUSTOMERFLAG_TEXT_SET_MARKER}",
        buttons: {
            success: {
                label: "{$smarty.const.IMAGE_SAVE}",
                className: "btn",
                callback: function() {
                    $.post("{\yii\helpers\Url::toRoute(['update-customer-flag'])}", { 'type':'marker', 'selected_ids' : selected_ids, 'o_flag' : $('input:checked[name="o_marker"]').val() }, function(data, status){
                        if (status == "success") {
                            resetStatement();
                        } else {
                            alert("Request error.");
                        }
                    },"html");
                }
            },
            main: {
                label: "Cancel",
                className: "btn-cancel",
                callback: function() {

                }
            }
        }
    });
    setTimeout(function(){
        $('input[name="o_marker"][value="'+marker_state+'"]').prop('checked', 'checked');
    }, 200);
}
{/if}

</script>
				<!--===Actions ===-->
				<div class="row right_column" id="order_management" style="display: none;">
						<div class="widget box">
							<div class="widget-content fields_style" id="customer_management_data">
                <div class="scroll_col"></div>
							</div>
						</div>
        </div>
				<!--===Actions ===-->
</div>
				<!-- /Page Content -->