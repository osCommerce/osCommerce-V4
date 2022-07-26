<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
</form>
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="groups_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable table-warehouses"
                       checkable_list="0" data_ajax="{Yii::$app->urlManager->createUrl('warehouses/location-blocks-list')}">
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
<div class="row" id="order_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="warehouses_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
</div>
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_BACK}</a></div>
</div>
<!-- /Page Header -->
<script>
function backStatement() {
    window.history.back();
    return false;
}
function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}

function resetStatement() {
    $('#warehouses_management_data .scroll_col').html('');

    var table = $('.table').DataTable();
    table.draw(false);

    return false;
}
function preEditItem(item_id) {
    $.post("{Yii::$app->urlManager->createUrl('warehouses/location-blocks-preview')}", {
        'item_id': item_id
    }, function (data, status) {
        if (status == "success") {
            $('#warehouses_management_data .scroll_col').html(data);
          //$('.js-open-tree-popup').popUp();
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}
function deleteItemConfirm(item_id) {
    $.post("{Yii::$app->urlManager->createUrl('warehouses/location-blocks-confirm-delete')}", {  'item_id': item_id }, function (data, status) {
        if (status == "success") {
            $('#warehouses_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function deleteItem() {
    $.post("{Yii::$app->urlManager->createUrl('warehouses/location-blocks-delete')}", $('#item_delete').serialize(), function (data, status) {
        if (status == "success") {
            $('#warehouses_management_data .scroll_col').html("");
            resetStatement();
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}
function editItem(item_id) {
    $.get("{Yii::$app->urlManager->createUrl('warehouses/location-blocks-edit')}", { 'item_id': item_id }, function(data, status) {
        if (status == "success") {
            $('#warehouses_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function saveItem(item_id) {
    $.post("{Yii::$app->urlManager->createUrl('warehouses/location-blocks-save')}?item_id="+item_id, $('form[name=item_edit]').serialize(), function(data, status_group) {
        if (status_group == "success") {
            $('#warehouses_management_data .scroll_col').html("");
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
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
</script>