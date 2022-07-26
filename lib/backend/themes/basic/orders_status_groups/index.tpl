
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="order-wrap">
<input type="hidden" id="row_id">
           <!--=== Page Content ===-->
                <div class="row order-box-list">
                    <div class="col-md-12">
                            <div class="widget-content">
              <div class="alert fade in" style="display:none;">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"></span>
              </div>
              <div class="ord_status_filter_row">
                <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                    {$app->controller->view->filterStatusTypes}
                </form>
              </div>
              {if {$messages|@count} > 0}
               {foreach $messages as $message}
              <div class="alert fade in {$message['messageType']}">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message['message']}</span>
              </div>               
               {/foreach}
              {/if}
                                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0" data_ajax="orders_status_groups/list">
                                    <thead>
                                        <tr>
                                                                                    {foreach $app->controller->view->StatusGroupTable as $tableItem}
                                                                                        <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                                                                    {/foreach}
                                        </tr>
                                    </thead>
                                    
                                </table>            


                </form>
                            </div>
             
                    </div>
                </div>
<script type="text/javascript">
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
    $("#status_groups_management").hide();
    switchOnCollapse('status_groups_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    $(window).scrollTop(0);
    return false;
}
function applyFilter() {
    resetStatement();
    return false;    
}
function onClickEvent(obj, table) {
    $('#row_id').val(table.find(obj).index());
    $("#status_groups_management").hide();
    $('#status_groups_management_data .scroll_col').html('');
    var orders_status_groups_id = $(obj).find('input.cell_identify').val();
    $.post("orders_status_groups/statusactions", { 'orders_status_groups_id' : orders_status_groups_id }, function(data, status_group) {
            if (status_group == "success") {
                $('#status_groups_management_data .scroll_col').html(data);
                $("#status_groups_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
}

function onUnclickEvent(obj, table) {
    $("#status_groups_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    $(table).DataTable().draw(false);
}

function statusGroupEdit(id) {
$("#status_groups_management").hide();
$.get("orders_status_groups/edit", { 'orders_status_groups_id' : id }, function(data, status_group) {
    if (status_group == "success") {
        $('#status_groups_management_data .scroll_col').html(data);
        $("#status_groups_management").show();
        switchOffCollapse('status_groups_list_collapse');
    } else {
        alert("Request error.");
    }
},"html");
                                        return false;
}

function statusGroupSave(id) {
$.post("orders_status_groups/save?orders_status_groups_id="+id, $('form[name=status_group]').serialize(), function(data, status_group) {
    if (status_group == "success") {
        //$('#status_groups_management_data').html(data);
        //$("#status_groups_management").show();
        $('.alert #message_plce').html('');
        $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
        resetStatement();
        switchOffCollapse('status_groups_list_collapse');
    } else {
        alert("Request error.");
    }
},"json");
                                        return false;    
}

function statusGroupDelete(id) {
    if (confirm('Do you confirm?')) {
        $.post("orders_status_groups/delete", { 'orders_status_groups_id' : id}, function(data, status_group) {
            if (status_group == "success") {
                //$('.alert #message_plce').html('');
                //$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                if (data == 'reset') {
                    resetStatement();
                } else{
                    $('#status_groups_management_data .scroll_col').html(data);
                    $("#status_groups_management").show();
                }
                switchOnCollapse('status_groups_list_collapse');
            } else {
                alert("Request error.");
            }
        },"html");
                                            return false;        
    }
}
</script>
                                <!--===Actions ===-->
                <div class="row right_column" id="status_groups_management">
                        <div class="widget box">
                            <div class="widget-content fields_style" id="status_groups_management_data">
                               <div class="scroll_col"></div>
                            </div>
                        </div>
                    </div>
                <!--===Actions ===-->
                <!-- /Page Content -->
</div>
