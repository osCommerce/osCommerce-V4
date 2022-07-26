
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}
<style>
  .cat_name:before{ left: 11px;top: 11px; }
 .cubic{ display: inline-block!important; }                                
 .prod_handle .cat_name:before{ content: "\f08b"; }
 .p-holder{ padding-left: 40px;display: inline; }
 .prodImgC{ display: inline; }
</style>

<div class="order-wrap">
<form id="filterForm">
<input type="hidden" id="row_id" name="row" value="{$app->controller->view->row}">
<input type="hidden" id="pID" name="pID" value="{$app->controller->view->pID}">
<input type="hidden" id="list" name="list" value="{$app->controller->view->list}">
</form>
           <!--=== Page Content ===-->
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
              <div class="alert fade in" style="display:none;">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"></span>
              </div>   	
			  
                    <table class="table tabl-res table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable table-switch-on-off double-grid" checkable_list="0" data_ajax="support-system/list">                        
                            <thead>
                                    <tr>
                                        {foreach $app->controller->view->ptoductsTable as $tableItem}
                                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                        {/foreach}
                                    </tr>
                            </thead>

                    </table>
            </div>
            <input type="hidden" id="global_id" value="0" />
            <input type="hidden" value="" id="global_type_code">
        </div>
    </div>

<script type="text/javascript">

$(document).ready(function(){
    $('.create_item').click(function(){
        let href = $(this).attr('href');
        if ($('#global_id').val() > 0){
            href = href + '?pID='+$('#global_id').val();
        }
        $(this).attr('href', href);
    })
})
/*function getTableSelectedIds() {
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

function approveSelectedItems() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        $.post("{Yii::$app->urlManager->createUrl('countries/approve-selected')}", { 'selected_ids' : selected_ids }, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    }
    return false;
}

function declineSelectedItems() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        $.post("{Yii::$app->urlManager->createUrl('countries/decline-selected')}", { 'selected_ids' : selected_ids }, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    }
    return false;
}

function deleteSelectedItems() {
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
                                    $.post("{Yii::$app->urlManager->createUrl('countries/delete-selected')}", { 'selected_ids' : selected_ids }, function(data, status){
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
}*/

function switchOffCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-down')) {
        $("#"+id).click();
    }
}

function switchStatement(target, status) {
    var data = {};
    var id = $('#global_id').val();
    if (id){
        data.pID = id;
    }
    data.target = $(target).parents('tr').find('.cell_identify').val();
    data.status = status;
    $.post("{Yii::$app->urlManager->createUrl('support-system/switch-status')}", data, function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
}

function switchOnCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-up')) {
        $("#"+id).click();
    }
}

function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}

function resetStatement(id) {
    $("#topic_management").hide();    
    var table = $('.table').DataTable();
    table.draw(false);
    //$(window).scrollTop(0);
    return false;
}

function applyFilter() {
    $("#row_id").val(0);
    resetStatement();
    return false;    
}
var a;
function onClickEvent(obj, table) {
    $("#topic_management").hide();
    $('#topics_management_data .scroll_col').html('');
    $('#row_id').val(table.find(obj).index());   
    $('#pID').val(table.fnSettings().json.pID);    
    $('#list').val(table.fnSettings().json.list);
    setFilterState();
    var glob_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    $(".check_on_off").bootstrapSwitch(
    {
        onSwitchChange: function (element, arguments) {
            switchStatement(element.target, arguments);
            return true;  
        },
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    } );
    if (type_code == 'product') {
        $.get("{Yii::$app->urlManager->createUrl('support-system/view')}", { 'pID' : glob_id }, function(data, status){
            if (status == "success") {
                $('#topics_management_data .scroll_col').html(data);
                $("#topic_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    } else if (glob_id > 0){
        
        $.get("{Yii::$app->urlManager->createUrl('support-system/view')}", { 'tID' : glob_id }, function(data, status){
            if (status == "success") {
                $('#topics_management_data .scroll_col').html(data);
                $("#topic_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    }
    
}

function onUnclickEvent(obj, table) {    
    $("#catalog_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    console.log(event_id, type_code);
    if (type_code == 'product' || type_code == 'parent') {
        $('#global_id').val(event_id);
        $('input[type="search"]').val('');
        if (type_code == 'parent'){
            $('#list').val('products');
        }
        $(table).DataTable().search('').draw(false);        
        sortable(table, type_code == 'product'? true : false);
    }
}


function confirmDeleteTopic(pid, tid){
    let title;
    if (tid){
        title = 'remove topic?';
    } else {
        title = 'remove all topics?';
    }
    bootbox.dialog({
    message: title,
    title: "Confirmation",
    buttons: {
        success: {
            label: "{$smarty.const.TEXT_YES}",
            className: "btn btn-primary",
            callback: function() {
                $.post("{Yii::$app->urlManager->createUrl('support-system/delete')}", { 'pID' : pid, 'tID':tid }, function(data, status){
                    if (status == "success") {
                        $('.alert #message_plce').html(data.message);
                        $('.alert').addClass(data.messageType).show();
                        resetStatement();
                    } else {
                        alert("Request error.");
                    }
                },"json");
            }
        },
        cancel: {
            label: "Cancel",
            className: "btn-cancel",
            callback: function() {
                //console.log("Primary button");
            }
        }
    }
});

}

function sortable(table, status){
    if (status){
        
        $( ".datatable tbody" ).sortable({
            items: "tr:not(:first)",
            stop: function( event, ui ) {
                var idx = [];                
                $.each($(table).find('input.cell_identify'), function(i, e){
                    if ($(e).val() > 0){
                        idx.push($(e).val());
                    }                    
                });                
                if (idx.length){
                    $.post("{Url::to('support-system/sort')}",{
                        'pID':  $('#global_id').val(),
                        'sort_order':idx
                    }, function(){
                    }, "json");
                }
            },
            update:function( event, ui ) {
                },
        });
    } else {
        $( ".datatable tbody" ).sortable('destroy');
    }    
}


					</script>
                                <!--===Actions ===-->
				<div class="row right_column" id="topic_management">
						<div class="widget box">
							<div class="widget-content fields_style" id="topics_management_data">
                                <div class="scroll_col"></div>
							</div>
						</div>
                                </div>
				<!--===Actions ===-->
				<!-- /Page Content -->		
</div>