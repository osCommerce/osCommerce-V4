<!-- /Page Header -->
{use class="common\helpers\Html"}
{\backend\assets\OrderAsset::register($this)|void}
{\backend\assets\MultiSelectAsset::register($this)|void}
<!--=== Page Content ===-->
<div class="widget box box-wrapp-blue filter-wrapp widget-closed widget-fixed">
    <div class="widget-header filter-title">
        <h4>
            {$smarty.const.TEXT_FILTER}
            <div class="filter-title-holder">
            <form action="{$app->urlManager->createUrl('tmp-orders/process-order')}" method="get" class="go-to-order filterFormHead">
                <label>{$smarty.const.TEXT_GO_TO_ORDER}</label>
                <input type="text" class="form-control" name="orders_id"/>
                <button type="submit" class="btn">{$smarty.const.TEXT_GO}</button>
            </form>
            <form id="filterFormHead" name="filterFormHead" class="filterFormHead" onsubmit="return applyFilter();">
                <div class="col-sm-2">
                <label>{$smarty.const.TEXT_SEARCH_BY}</label>
                </div>
                <div class="col-sm-4">
                <select class="form-control" name="by">
                    {foreach $app->controller->view->filters->by as $Item}
                        <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                    {/foreach}
                </select>
                </div>
                <div class="col-sm-5">
                <input type="text" name="search" value="{$app->controller->view->filters->search}" class="form-control" />
                </div>
                <div class="col-sm-1">
                <button type="submit" class="btn">{$smarty.const.TEXT_GO}</button>
                </div>
            </form>

        {*if count($app->controller->view->filters->admin_choice)}
            <div class="dropdown btn-link-create" style="float:right">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                {$smarty.const.TEXT_UNSAVED_CARTS}
                <i class="icon-caret-down small"></i>
            </a>
            <ul class="dropdown-menu">
                {foreach $app->controller->view->filters->admin_choice as $choice}
                <li>{$choice}</li>
                {/foreach}
            </ul>
        </div>
        {/if*}
        {*
            <div class="pull-right">
                <form action="{$app->urlManager->createUrl('tmp-orders/index',$app->request->get())}" method="get" id="filterModeForm" class="filterFormHead" onsubmit="return applyFilter();">
                    <label>{$smarty.const.SHOW}</label>
                    {\yii\helpers\Html::dropDownList('', $app->controller->view->filters->mode, [''=>ORDER_FILTER_MODE_ALL, 'need_process'=>ORDER_FILTER_MODE_NEED_PROCESS], ['class'=>'form-control', 'onchange'=>"\$('#hMode').val(\$(this).val());applyFilter()"])}
                </form>
            </div>*}
            {if $smarty.const.ADMIN_ORDERS_QUICK_STATUS_FILTER=='True'}
            <div class="pull-right">
                <form action="{$app->urlManager->createUrl('tmp-orders/index',$app->request->get())}" method="get" id="filterStatusForm" class="filterFormHead" onsubmit="return applyFilter();">
                    <label>{$smarty.const.TABLE_HEADING_STATUS}</label>
                    {Html::dropDownList('status[]', $app->controller->view->filters->status_selected, array_merge(array(''=>''), $app->controller->view->filters->status), ['class' => 'form-control', 'onchange'=>"\$('#orderStatuses').val(\$(this).val());applyFilter()"])}
                </form>
            </div>
            {/if}
            </div>
        </h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
          </div>
        </div>
    </div>
    <div class="widget-content">
        
            <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                {include file="../filters/orders.tpl"}
                <div class="filters_btn">
                    <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>&nbsp;&nbsp;&nbsp;<button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
                    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
                    <input type="hidden" name="fs" value="{$app->controller->view->filters->fs}" />
                    <input type="hidden" name="mode" value="{$app->controller->view->filters->mode}" id="hMode" />
                </div>
            </form>
        
    </div>
</div>

<!--===Orders List ===-->
<div class="order-wrap">    
<div class="row order-box-list order-sc-text">
    <div class="col-md-12">
        <div class="widget-content">
<!-- always available batch actions -->
<!-- batch actions on selected orders -->
            <div class="btn-wr after btn-wr-top btn-wr-top1 disable-btn batch-actions data-table-top-left">
                    <a href="javascript:void(0)" onclick="exportSelectedOrders();" class="btn">{$smarty.const.TEXT_BATCH_EXPORT}</a>
{if \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_DELETE'])}
                    <a href="javascript:void(0)" onclick="deleteSelectedOrders();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
{/if}
{if false && \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')}
                    <a href="javascript:void(0)" onclick="flagSelectedOrders();" class="btn">{$smarty.const.TEXT_FLAG}</a>
                    <a href="javascript:void(0)" onclick="markerSelectedOrders();" class="btn">{$smarty.const.TEXT_MARKER}</a>
{/if}
            </div>   
            <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable tabl-res double-grid table-orders table-colored" data_ajax="tmp-orders/orderlist" checkable_list="">
                <thead>
                    <tr>
                        {foreach $app->controller->view->ordersTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                            {/foreach}
                    </tr>
                </thead>

            </table>
<!-- always available batch actions -->
<!-- batch actions on selected orders -->
            <div class="btn-wr after disable-btn batch-actions">
                <div>
                    <a href="javascript:void(0)" onclick="exportSelectedOrders();" class="btn">{$smarty.const.TEXT_BATCH_EXPORT}</a>
{if \common\helpers\Acl::rule(['ACL_ORDER', 'IMAGE_DELETE'])}
                    <a href="javascript:void(0)" onclick="deleteSelectedOrders();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
{/if}
{if false && \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')}
                    <a href="javascript:void(0)" onclick="flagSelectedOrders();" class="btn">{$smarty.const.TEXT_FLAG}</a>
                    <a href="javascript:void(0)" onclick="markerSelectedOrders();" class="btn">{$smarty.const.TEXT_MARKER}</a>
{/if}
                </div>
                <div>
                </div>
            </div>                
        </div>
    </div>
</div>
<!-- /Orders List -->
        
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
function cancelStatement() {
    var orders_id = $('.table tbody tr.selected').find('input.cell_identify').val();
    $.post("{$app->urlManager->createUrl('tmp-orders/orderactions')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
}
function setFilterState() {
    orig = $('#filterForm, #filterFormHead, #filterModeForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}
function resetStatement() {
    setFilterState();
    $("#order_management").hide();
    switchOnCollapse('orders_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    $(window).scrollTop(0);
    return false;
}
function onClickEvent(obj, table) {
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();
    var orders_id = $(obj).find('input.cell_identify').val();
    $.post("{$app->urlManager->createUrl('tmp-orders/orderactions')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
}
function onUnclickEvent(obj, table) {

}
function check_form() {
//ajax save
    $("#order_management").hide();
    $.post("{$app->urlManager->createUrl('tmp-orders/ordersubmit')}", $('#status_edit').serialize(), function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function deleteOrder() {
    $("#order_management").hide();
    $.post("{$app->urlManager->createUrl('tmp-orders/orderdelete')}", $('#orders_edit').serialize(), function(data, status){
        if (status == "success") {
            resetStatement()
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function confirmDeleteOrder(orders_id) {
    $("#order_management").hide();
    $.post("{$app->urlManager->createUrl('tmp-orders/confirmorderdelete')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOffCollapse('orders_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function reassignOrder(orders_id) {
    $("#order_management").hide();
    $.post("{$app->urlManager->createUrl('tmp-orders/order-reassign')}", { 'orders_id' : orders_id }, function(data, status){
        if (status == "success") {
            $('#order_management_data .scroll_col').html(data);
            $("#order_management").show();
            switchOffCollapse('orders_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function confirmedReassignOrder() {
    $("#order_management").hide();
    $.post("{$app->urlManager->createUrl('tmp-orders/confirmed-order-reassign')}", $('#orders_edit').serialize(), function(data, status){
        if (status == "success") {
            resetStatement()
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

$(document).ready(function() {

    $(window).resize(function(){ 
        setTimeout(function(){ 
            var height_box = $('.order-box-list').height() + 2;
            $('#order_management .widget.box').css('min-height', height_box);
        }, 800);        
    })
    $(window).resize();
    
    
    $('.w-tdc.act_row input[type="text"]').prop('disabled', false);
    $('.w-tdc.act_row select').prop('disabled', false);
    
    $('input[name="date"]').click(function() { 
        if($(this).is(':checked')){ 
            $(this).parents().siblings('div.w-tdc').removeClass('act_row');
            $(this).parents('.w-tdc').addClass('act_row');
            $('.w-tdc input[type="text"]').prop('disabled', true);
            $('.w-tdc select').prop('disabled', true);
            $('.w-tdc.act_row input[type="text"]').prop('disabled', false);
            $('.w-tdc.act_row select').prop('disabled', false);
        }
    });

    $('#fcById').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fcLike").prop("checked", false);
          $("#fcLike").parent().removeClass('active_options');
          $("#fcCode").prop("disabled", true);
          $("#fcId").prop("disabled", false);
        } else {
          $("#fcCode").prop("disabled", false);
          $("#fcId").prop("disabled", true);
        }
      }
    );
    $('#fcLike').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fcById").prop("checked", false);
          $("#fcById").parent().removeClass('active_options');
          $("#fcCode").prop("disabled", false);
          $("#fcId").prop("disabled", true);
        } else {
          $("#fcCode").prop("disabled", true);
          $("#fcId").prop("disabled", false);
        }
      }
    );


    $('#fpFrom').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fpClass").prop("disabled", false);
          $("#fpFromSumm").prop("disabled", false);
        } else {
          $(this).parent().removeClass('active_options');
          $("#fpFromSumm").prop("disabled", true);
          if (!$("#fpTo").is(':checked')) {
            $("#fpClass").prop("disabled", true);
          }
        }
      }
    );
    $('#fpTo').off('click').click( function () {
        if ($(this).is(':checked')) {
          $(this).parent().addClass('active_options');
          $("#fpClass").prop("disabled", false);
          $("#fpToSumm").prop("disabled", false);
        } else {
          $(this).parent().removeClass('active_options');
          $("#fpToSumm").prop("disabled", true);
          if (!$("#fpFrom").is(':checked')) {
            $("#fpClass").prop("disabled", true);
          }
        }
      }
    );

    $('body').on('click', 'th.checkbox-column .uniform', function() { 
        if($(this).is(':checked')){
            $('tr.checkbox-column .uniform').prop('checked', true).uniform('update');
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    });
    
    $('select.select2-offscreen').change(function(){ 
        setTimeout(function(){ 
            var height_box = $('.order-box-list').height() + 2;
            $('#order_management .widget.box').css('min-height', height_box);
        }, 800); 
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
    $('input[name="delivery_country"]').val('');
    $('input[name="delivery_state"]').val('');
    $("#fcById").prop("checked", true);
    $("#fcLike").prop("checked", false);
    $("#fcId").val('');
    $("#fcCode").val('');
    $("#fcCode").prop('disabled', true);
    $("#fcId").prop('disabled', false);

    $("#fpFrom").prop("checked", false);
    $("#fpTo").prop("checked", false);
    $("#fpClass").val('');
    $("#fpFromSumm").val('');
    $("#fpToSumm").val('');
    $("#fpFromSumm").prop('disabled', true);
    $("#fpToSumm").prop('disabled', true);
    $("#fpClass").prop('disabled', true);
    $('select[name="walkin"]').val('');
    $("input[name='flag'][value='0']").prop("checked", true);
    $("input[name='marker'][value='0']").prop("checked", true);
    $("select[data-role=multiselect]").multipleSelect('uncheckAll');
    $("select[data-role=multiselect-radio]").multipleSelect('uncheckAll');

    $("#row_id").val(0);
    $('label.active_options, span.active_options').removeClass('active_options');
    resetStatement();
    return false;  
}
    
function applyFilter() {
    resetStatement();
    return false;    
}


{if false && \common\helpers\Acl::checkExtensionAllowed('OrderMarkers', 'allowed')}
function sendOrderFlag(id, flag_state) {
    var selected_ids = [];
    selected_ids[0] = id;
    if (typeof flag_state == "undefined") flag_state = 0;
    sendOrdersFlag(selected_ids, flag_state);
}
function flagSelectedOrders() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        sendOrdersFlag(selected_ids, 0);
    }
    return false;
}
function sendOrdersFlag(selected_ids, flag_state) {
    bootbox.dialog({
        message: '{foreach $app->controller->view->flags as $flag}<label class="{$flag['class']}" style="{$flag['style']}">{\yii\helpers\Html::radio('o_flag', false, ['value' => $flag['id']])|escape:'javascript'}<span>{$flag['text']}</span></label><br>{/foreach}',
        title: "{$smarty.const.TEXT_SET_FLAG}",
        buttons: {
                success: {
                        label: "{$smarty.const.IMAGE_SAVE|escape:'javascript'}",
                        className: "btn",
                        callback: function() {
                            $.post("{$app->urlManager->createUrl(['extensions/', 'module' => 'OrderMarkers', 'action' => 'adminActionSetFlag'])}", { 'selected_ids' : selected_ids, 'o_flag' : $('input:checked[name="o_flag"]').val() }, function(data, status){
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
function sendOrderMarker(id, marker_state) {
    var selected_ids = [];
    selected_ids[0] = id;
    sendOrdersMarker(selected_ids, marker_state);
}
function markerSelectedOrders() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        sendOrdersMarker(selected_ids, 0);
    }
    return false;
}
function sendOrdersMarker(selected_ids, marker_state) {

        bootbox.dialog({
                message: '{foreach $app->controller->view->markers as $marker}<label class="{$marker['class']}" style="{$marker['style']}">{\yii\helpers\Html::radio('o_marker', false, ['value' => $marker['id']])|escape:'javascript'}<span>{$marker['text']}</span></label><br>{/foreach}',
                title: "{$smarty.const.TEXT_SET_MARKER}",
                buttons: {
                        success: {
                                label: "{$smarty.const.IMAGE_SAVE|escape:'javascript'}",
                                className: "btn",
                                callback: function() {
                                    $.post("{$app->urlManager->createUrl(['extensions/', 'module' => 'OrderMarkers', 'action' => 'adminActionSetMarker'])}", { 'selected_ids' : selected_ids, 'o_marker' : $('input:checked[name="o_marker"]').val() }, function(data, status){
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

function exportSelectedOrders() {
    if (getTableSelectedCount() > 0) {

        var form = document.createElement("form");
        form.target = "_blank";
        form.method = "POST";
        form.action = 'tmp-orders/ordersexport';

        var selected_ids = getTableSelectedIds();
        var hiddenField = document.createElement("input");
        hiddenField.setAttribute("name", "orders");
        hiddenField.setAttribute("value", selected_ids);
        form.appendChild(hiddenField);

        document.body.appendChild(form);
        form.submit();
    }
    
    return false;
}

function deleteSelectedOrders() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        
        bootbox.dialog({
                title: "{$smarty.const.TEXT_DELETE_SELECTED_ORDERS_TITLE|escape}",
                message: "{$smarty.const.TEXT_DELETE_SELECTED_ORDERS|escape}",
                buttons: {
                        danger: {
                                label: "{$smarty.const.TEXT_YES|escape}",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("tmp-orders/ordersdelete", { 'selected_ids' : selected_ids }, function(data, status){
                                        if (status == "success") {
                                            $('.batch-actions').addClass('disable-btn');
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

$(document).ready(function(){
	//===== Date Pickers  =====//
	$( ".datepicker" ).datepicker({
		changeMonth: true,
                changeYear: true,
		showOtherMonths:true,
		autoSize: false,
		dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
                onSelect: function (e) { 
                    if ($(this).val().length > 0) { 
                      $(this).siblings('span').addClass('active_options');
                    }else{ 
                      $(this).siblings('span').removeClass('active_options');
                    }
                  }
        });
        $("select[data-role=multiselect]").multipleSelect({
            multiple: true,
            filter: true,
        });
		
		$('[data-role=multiselect-radio]').multipleSelect({
            multiple: false,
            filter: true,
            single: true,
            onClick : function(option){
                applyFilter();
            }
        });
 
        $('#selectCountry').autocomplete({
            source: "tmp-orders/countries",
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
                $(this).siblings('label').addClass('active_options');
              }else{ 
                  $(this).siblings('label').removeClass('active_options');
              }
            },
            select: function(event, ui) {
                if ($(this).val().length > 0) { 
                    $(this).siblings('label').addClass('active_options');
                }else{ 
                    $(this).siblings('label').removeClass('active_options');
                }
                $('input[name="delivery_state"]').prop('disabled', true);
                if(ui.item.value != null){ 
                    $('input[name="delivery_state"]').prop('disabled', false);
                }
            }
        }).focus(function () {
          $(this).autocomplete("search");
          if ($(this).val().length > 0) { 
                    $(this).siblings('label').addClass('active_options');
                }else{ 
                    $(this).siblings('label').removeClass('active_options');
                }
        });
        
        $('#selectState').autocomplete({
            // source: "tmp-orders/state?country=" + $('#selectCountry').val(),
            source: function(request, response) {
                $.ajax({
                    url: "tmp-orders/state",
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
                $(this).siblings('label').addClass('active_options');
              }else{ 
                  $(this).siblings('label').removeClass('active_options');
              }
            },
            select: function(event, ui) {
                if ($(this).val().length > 0) { 
                    $(this).siblings('label').addClass('active_options');
                }else{ 
                    $(this).siblings('label').removeClass('active_options');
                }
            }
        }).focus(function () {
          $(this).autocomplete("search");
          if ($(this).val().length > 0) { 
                $(this).siblings('label').addClass('active_options');
            }else{ 
                $(this).siblings('label').removeClass('active_options');
            }
        });  
});

</script>
<!--===Actions ===-->
    <div class="row right_column" id="order_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="order_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
</div>
<!--===Actions ===-->

<!-- /Page Content -->
