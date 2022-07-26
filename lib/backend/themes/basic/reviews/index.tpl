<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="widget box box-wrapp-blue box-wrapp-blue2 filter-wrapp">
    <div class="widget-header filter-title">
        <h4>{$smarty.const.TEXT_FILTER}</h4>
        <div class="toolbar no-padding">
          <div class="btn-group">
            <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
          </div>
        </div>
    </div>
    <div class="widget-content">
        <div class="filter-box">
            <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                <div class="row m-b-2 ">
                    <div class="col-sm-3 align-right">
                        <label>{$smarty.const.TEXT_STATUS}</label>
                    </div>
                    <div class="col-sm-8">
                        <select class="form-control" name="status">
                            {foreach $app->controller->view->filters->status as $Item}
                                <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="row m-b-2">
                    <div class="col-sm-3 align-right">
                        <label>{$smarty.const.TEXT_CUSTOMER_ID}:</label>
                    </div>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="cID" value="{$app->controller->view->filters->cID}" />
                    </div>
                </div>
                <div class="row m-b-2">
                    <div class="col-sm-3 align-right">
                        <label>{$smarty.const.TEXT_CUSTOMER_NAME}:</label>
                    </div>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="name" value="{$app->controller->view->filters->name}" placeholder="{$smarty.const.TEXT_TYPE_CUSTOMER}" />
                    </div>
                </div>
                <div class="row m-b-2">
                    <div class="col-sm-3 align-right">
                        <label>{$smarty.const.ENTRY_PRODUCT}</label>
                    </div>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="product" value="{$app->controller->view->filters->product}" placeholder="{$smarty.const.TEXT_TYPE_CHOOSE_PRODUCT}" />
                    </div>
                </div>
                <div class="row m-b-2">
                    <div class="col-sm-3 align-right">
                        <label>{$smarty.const.ENTRY_DATE}</label>
                    </div>
                    <div class="col-sm-8">

                        <div class="row ">
                            <div class="col-sm-2 align-right">
                                <label>{$smarty.const.ENTRY_FROM}</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="from_date" type="text" autocomplete="off" name="from" value="{$app->controller->view->filters->from}" class="datepicker form-control">
                            </div>
                            <div class="col-sm-2 align-right">
                                <label>{$smarty.const.TEXT_TO}</label>
                            </div>
                            <div class="col-sm-4">
                                <input id="to_date" type="text" autocomplete="off" name="to" value="{$app->controller->view->filters->to}" class="datepicker form-control">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-11 align-right">
                        <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>
                        <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>
                    </div>
                </div>
                <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
            </form>
        </div>
    </div>
</div>
<div class="order-wrap">
<!--===reviews list===-->
<div class="row order-box-list rev-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="reviews_list_data">
                <div class="btn-wr after btn-wr-top disable-btn data-table-top-left">
                        <a href="javascript:void(0)" onclick="approveSelectedRewiews();" class="btn btn-no-margin">{$smarty.const.TEXT_APPROVE_SELECTED}</a><a href="javascript:void(0)" onclick="declineSelectedRewiews();" class="btn">{$smarty.const.TEXT_DECLINE_SELECTED}</a><a href="javascript:void(0)" onclick="deleteSelectedRewiews();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                </div> 
                <table class="table tabl-res table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable table-switch-on-off double-grid rable-reviews" checkable_list="" data_ajax="reviews/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->reviewsTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
                <div class="btn-wr after disable-btn">
                    <div>
                        <a href="javascript:void(0)" onclick="approveSelectedRewiews();" class="btn btn-no-margin">{$smarty.const.TEXT_APPROVE_SELECTED}</a><a href="javascript:void(0)" onclick="declineSelectedRewiews();" class="btn">{$smarty.const.TEXT_DECLINE_SELECTED}</a><a href="javascript:void(0)" onclick="deleteSelectedRewiews();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                    </div>
                    <div>
                    </div>
                </div>
                <!--p class="btn-toolbar">
                    <input type="button" class="btn btn-primary" value="Insert"
                           onClick="return editItem( 0)">
                </p-->
            </div>
    </div>
</div>
<!--===/reviews list===-->

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

function approveSelectedRewiews() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        $.post("{Yii::$app->urlManager->createUrl('reviews/approve-selected')}", { 'selected_ids' : selected_ids }, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    }
    return false;
}

function declineSelectedRewiews() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        $.post("{Yii::$app->urlManager->createUrl('reviews/decline-selected')}", { 'selected_ids' : selected_ids }, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    }
    return false;
}

function switchRewiewsStatement(id, status) {
    $.post("{Yii::$app->urlManager->createUrl('reviews/switch-status')}", { 'id' : id, 'status' : status }, function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
}
function deleteSelectedRewiews() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        
        bootbox.dialog({
                message: "{$smarty.const.TEXT_DELETE_SELECTED}?",
                title: "{$smarty.const.TEXT_DELETE_SELECTED}",
                buttons: {
                        success: {
                                label: "Yes",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("{Yii::$app->urlManager->createUrl('reviews/delete-selected')}", { 'selected_ids' : selected_ids }, function(data, status){
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

function resetFilter() {
    $('select[name="status"]').val('');
    $('select[name="name"], input[name="name"]').val('');
    $('select[name="product"], input[name="product"]').val('');
    $('input[name="from"]').val('');
    $('input[name="to"]').val('');
    $('input[name="cID"]').val('');
    $("#row_id").val(0);
    resetStatement();
    return false;  
}

function applyFilter() {
    $("#row_id").val(0);
    resetStatement();
    return false;    
}

function preEditItem( item_id ) {
    $.post("{Yii::$app->urlManager->createUrl('reviews/itempreedit')}", {
        'item_id': item_id
    }, function (data, status) {
        if (status == "success") {
            $('#reviews_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function editItem(item_id) {

    $.post("{Yii::$app->urlManager->createUrl('reviews/itemedit')}", {
        'item_id': item_id
    }, function (data, status) {
        if (status == "success") {
            $('#reviews_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function saveItem() {
    $.post("{Yii::$app->urlManager->createUrl('reviews/submit')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            $('#reviews_management_data .scroll_col').html(data);
            $('.table').DataTable().search('').draw(false);
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}

function deleteItemConfirm( item_id) {
    $.post("{Yii::$app->urlManager->createUrl('reviews/confirmitemdelete')}", {  'item_id': item_id }, function (data, status) {
        if (status == "success") {
            $('#reviews_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function deleteItem() {
    $.post("{Yii::$app->urlManager->createUrl('reviews/itemdelete')}", $('#item_delete').serialize(), function (data, status) {
        if (status == "success") {
            $('#reviews_management_data .scroll_col').html("");
            resetStatement();
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}

function switchOffCollapse(id) {
    if ($("#" + id).children('i').hasClass('icon-angle-down')) {
        $("#" + id).click();
    }
}

function switchOnCollapse(id) {
    if ($("#" + id).children('i').hasClass('icon-angle-up')) {
        $("#" + id).click();
    }
}

function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}

function resetStatement() {
    $('#reviews_management_data .scroll_col').html('');

    var table = $('.table').DataTable();
    table.draw(false);

    return false;
}

function onClickEvent(obj, table) {
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();
    
    $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                switchRewiewsStatement(element.target.value, arguments);
                return true;  
            },
			onText: "{$smarty.const.SW_ON}",
			offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );    

    var event_id = $(obj).find('input.cell_identify').val();
    preEditItem(  event_id );
}

function onUnclickEvent(obj, table) {

    var event_id = $(obj).find('input.cell_identify').val();
}
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
            var height_box = $('.order-wrap').height();
            $('#order_management .widget').css('min-height', height_box);
        }, 800);
    })
    $(window).resize();     

    $('th.checkbox-column .uniform').click(function() { 
        if($(this).is(':checked')){
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    }); 
})
</script>

<!--===  reviews management ===-->
<div class="row" id="order_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="reviews_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== reviews management ===-->
</div>