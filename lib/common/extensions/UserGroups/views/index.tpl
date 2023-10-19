<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<div class="order-wrap">
<!--===groups list===-->
<div class="row order-box-list">
{if \common\helpers\Acl::checkExtensionAllowed('ExtraGroups', 'allowed')}
  {\common\extensions\ExtraGroups\widgets\ExtraGroupsTabs\ExtraGroupsTabs::widget()}
{/if}
    <div class="col-md-12">
            <div class="widget-content" id="groups_list_data">
            {if $messages }
                    {foreach $messages as $key => $message}
                        <div class="alert fade in alert-{$key}">
                            <i data-dismiss="alert" class="icon-remove close"></i>
                            <span id="message_plce">{$message}</span>
                        </div>
                    {/foreach}
                {/if}
              <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                <input type="hidden" name="row" id="row_id" value="{$row}" />
                {if $ext = \common\helpers\Acl::checkExtensionAllowed('ExtraGroups', 'allowed')}
                  {$ext::typeIdHidden()}
                {/if}
              </form>
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-groups double-grid"
                       checkable_list="0,1" data_ajax="groups/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->groupsTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
                <!--<p class="btn-toolbar">
                    <input type="button" class="btn btn-primary" value="Insert"
                           onClick="return editItem( 0)">
                </p>-->
            </div>
    </div>
</div>
<!--===/groups list===-->

<script type="text/javascript">
  function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
  }

  function cancelStatement() {
    $(".order-wrap").show();
    $("#groups_management").hide();
    $("#customers_management").hide();
    var item_id = $('.table tbody tr.selected').find('input.cell_identify').val();
    $.post("groups/itempreedit", { 'item_id': item_id }, function(data, status){
        if (status == "success") {
            $('#groups_management_data .scroll_col').html(data);
            deleteScroll();
            heightColumn();
        } else {
            alert("Request error.");
        }
    },"html");
    
}
    
function customersGroupEdit(id) {
    $(".order-wrap").hide();
    $("#customers_management").hide();
    $.get("groups/customers", { 'groups_id' : id }, function(data, status_group) {
        if (status_group == "success") {
            $('#customers_management_data').html(data);
            $("#customers_management").show();
            //switchOffCollapse('status_groups_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function customersGroupFilter() {
    var filter = $("#customers_filter").val(),
        id= $("#groups_id").val();
    $("#customers_management").hide();
    $.get("groups/customers", { 'groups_id' : id, 'filter' : filter }, function(data, status_group) {
        if (status_group == "success") {
            $('#customers_management_data').html(data);
            $("#customers_management").show();
            //switchOffCollapse('status_groups_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function customersGroupAdd() {
    var filter = $("#customers_filter").val(),
        id= $("#groups_id").val(),
        customers_id= $("#add").val();
    $("#customers_management").hide();
    $.get("groups/customers-add", { 'groups_id' : id, 'filter' : filter, 'customers_id' : customers_id }, function(data, status_group) {
        if (status_group == "success") {
            $('#customers_management_data').html(data);
            $("#customers_management").show();
            //switchOffCollapse('status_groups_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function customersGroupDelete() {
    var filter = $("#customers_filter").val(),
        id= $("#groups_id").val(),
        customers_id= $("#del").val();
    $("#customers_management").hide();
    $.get("groups/customers-delete", { 'groups_id' : id, 'filter' : filter, 'customers_id' : customers_id }, function(data, status_group) {
        if (status_group == "success") {
            $('#customers_management_data').html(data);
            $("#customers_management").show();
            //switchOffCollapse('status_groups_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

    function preEditItem( item_id ) {
        $("#customers_management").hide();
        $.post("groups/itempreedit", {
            'item_id': item_id,
            'row_id': $("#row_id").val(),
        }, function (data, status) {
            if (status == "success") {
                $('#groups_management_data .scroll_col').html(data);
                deleteScroll();
                heightColumn();
                //$("#groups_management").show();
                //switchOnCollapse('groups_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function editItem(item_id) {
        /*
        var parent_id = 0
        if (item_id == 0) {
            parent_id = $('#global_id').val();
        }
        */
       $("#customers_management").hide();
        $.post("groups/itemedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#groups_management_data .scroll_col').html(data);
                deleteScroll();
                heightColumn();
                //$("#groups_management").show();
                //switchOnCollapse('groups_management_collapse');
                //switchOffCollapse('groups_list_box_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function saveItem() {
        $.post("groups/submit", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#groups_management_data .scroll_col').html(data);
                $("#groups_management").show();

                $('.table').DataTable().search('').draw(false);

                /*
                var table = $('.table').dataTable().api();
                var url = $base_url + "/groups/categorieslist/";
                table.data().ajax.url(url).load();
                */

            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function deleteItemConfirm( item_id) {
        $.post("groups/confirmitemdelete", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#groups_management_data .scroll_col').html(data);
                $("#groups_management").show();
                switchOnCollapse('groups_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItem() {
        $.post("groups/itemdelete", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#groups_management_data .scroll_col').html("");
                switchOffCollapse('groups_management_collapse');
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

    function resetStatement() {
        setFilterState();
        $(".order-wrap").show();
        $("#groups_management").hide();

        switchOnCollapse('groups_list_box_collapse');
        switchOffCollapse('groups_management_collapse');

        $('#groups_management_data .scroll_col').html('');
        $('#groups_management').hide();

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

        var event_id = $(obj).find('input.cell_identify').val();

        preEditItem(  event_id );
    }

    function onUnclickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();
    }
    $(document).ready(function() {
        $(window).resize(function () {
            setTimeout(function () {
                var height_box = $('.order-wrap').height();
                $('#order_management .widget').css('min-height', height_box);
            }, 800);
        })
        $(window).resize();
    })
</script>

<!--===  groups management ===-->
<div class="row right_column" id="order_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="groups_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== groups management ===-->
</div>
                    
<!--===  customers management ===-->
                <div class="row" id="customers_management" style="display: none;">
                    <div class="col-md-12">
                        <div class="widget box">
                            <div class="widget-header">
                                <h4><i class="icon-reorder"></i> <span id="customers_management_title">{$smarty.const.CUSTOMERS_GROUPS_MANAGEMENT}</span></h4>
                                
                            </div>
                            <div class="widget-content fields_style" id="customers_management_data">
                                                            Action
                            </div>
                        </div>
                    </div>
                </div>
<!--===  customers management ===-->
<div id="manageQuoteArea"></div>