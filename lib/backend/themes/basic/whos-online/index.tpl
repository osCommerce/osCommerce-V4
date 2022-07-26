<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
</form>
<!--===users list===-->
<div>
<div class="row">
    <div class="col-md-12">
        <div class="widget box">
        <div class="widget-content">
		{$usersPath}
            <table class="table table-striped table-checkable table-hover table-responsive table-bordered datatable table-users" data_ajax="whos-online/list" checkable_list="">
                <thead>
                    <tr>
                        {foreach $app->controller->view->usersTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                            {/foreach}
                    </tr>
                </thead>

            </table>
        </div>
        </div>
    </div>
{$forget}
</div>

<!--===/users list===-->
<script>
function resetFilter() {
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
    $.post("whos-online/itempreedit", {
        'item_id': item_id
    }, function (data, status) {
        if (status == "success") {
            $('#users_management_data').html(data);
            $("#users_management").show();
            switchOnCollapse('users_management_collapse');
        } else {
            alert("Request error.");
        }
    }, "html");

    //$("html, body").animate({ scrollTop: $(document).height() }, "slow");

    return false;
}

function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
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
    $("#users_management").hide();

    switchOnCollapse('users_list_box_collapse');
    switchOffCollapse('users_management_collapse');

    $('users_management_data').html('');
    $('#users_management').hide();

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


</script>
<!--===  users management 
<div class="row right_column" id="users_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="users_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
!--=== users management ===-->
</div>