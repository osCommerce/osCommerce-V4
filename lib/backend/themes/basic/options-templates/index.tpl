
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
              {if {$messages|@count} > 0}
               {foreach $messages as $message}
              <div class="alert fade in {$message['messageType']}">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message['message']}</span>
              </div>               
               {/foreach}
              {/if}
                                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0" data_ajax="options-templates/list">
                                    <thead>
                                        <tr>
                                                {foreach $app->controller->view->options_templateTable as $tableItem}
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
var global = '{$tID}';

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

function resetStatement(item_id) {
    if (item_id > 0) global = item_id;

    $("#options_templates_management").hide();
    switchOnCollapse('options_templates_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    $(window).scrollTop(0);
    return false;
}

var first = true;
function onClickEvent(obj, table) {
    $('#row_id').val(table.find(obj).index());
    $("#options_templates_management").hide();
    $('#options_templates_management_data .scroll_col').html('');
    var options_templates_id = $(obj).find('input.cell_identify').val();
    if (global > 0) options_templates_id = global;

    $.post("options-templates/statusactions", { 'options_templates_id' : options_templates_id }, function(data, status) {
        if (status == "success") {
            $('#options_templates_management_data .scroll_col').html(data);
            $("#options_templates_management").show();
            $('.js-open-tree-popup').popUp();
        } else {
            alert("Request error.");
        }
    },"html");

    $('.table tr').removeClass('selected');
    $('.table').find('input.cell_identify[value=' + options_templates_id + ']').parents('tr').addClass('selected');
    global = '';
    url = window.location.href;
    if (url.indexOf('tID=') > 0) {
      url = url.replace(/tID=\d+/g, 'tID=' + options_templates_id);
    } else {
      url += '?tID=' + options_templates_id;
    }
    if (first) {
      first = false;
    } else {
      window.history.replaceState({}, '', url);
    }
}

function onUnclickEvent(obj, table) {
    $("#options_templates_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    $(table).DataTable().draw(false);
}

function options_templateEdit(id) {
    $("#options_templates_management").hide();
    $.get("options-templates/edit", { 'options_templates_id' : id }, function(data, status) {
        if (status == "success") {
            $('#options_templates_management_data .scroll_col').html(data);
            $("#options_templates_management").show();
            switchOffCollapse('options_templates_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function options_templateSave(id) {
    $.post("options-templates/save?options_templates_id="+id, $('form[name=options_template]').serialize(), function(data, status) {
        if (status == "success") {
            //$('#options_templates_management_data').html(data);
            //$("#options_templates_management").show();
            $('.alert #message_plce').html('');
            $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
            resetStatement(id);
            switchOffCollapse('options_templates_list_collapse');
        } else {
            alert("Request error.");
        }
    },"json");
    return false;    
}

function options_templateDeleteConfirm(id) {
    $.post("{$app->urlManager->createUrl('options-templates/confirmdelete')}", { 'options_templates_id': id }, function (data, status) {
        if (status == "success") {
            $('#options_templates_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function options_templateDelete() {
    if (confirm('Are you sure?')) {
        $.post("{$app->urlManager->createUrl('options-templates/delete')}", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                if (data == 'reset') {
                    resetStatement();
                } else {
                    $('#options_templates_management_data .scroll_col').html(data);
                    $("#options_templates_management").show();
                }
                switchOnCollapse('options_templates_list_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
    }
    return false;
}
</script>
                                <!--===Actions ===-->
                <div class="row right_column" id="options_templates_management">
                        <div class="widget box">
                            <div class="widget-content fields_style" id="options_templates_management_data">
                               <div class="scroll_col"></div>
                            </div>
                        </div>
                    </div>
                <!--===Actions ===-->
                <!-- /Page Content -->
</div>

<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>
