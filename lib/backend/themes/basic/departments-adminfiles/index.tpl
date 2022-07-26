<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
</div>
<!-- /Page Header -->
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
    <input type="hidden" name="department_id" id="page_department_id" value="{$selected_department_id}" />
</form>
<!--=== Listing ===-->

<div class="tabbable tabbable-custom" style="margin-bottom: 0;">
    <ul class="nav nav-tabs">
        {foreach $departments as $department}
          <li class="{if $department['id']==$selected_department_id} active {/if}"><a class="js_link_department_modules_select" href="{$department['link']}" data-department_id="{$department['id']}"><span>{$department['text']}</span></a></li>
        {/foreach}
    </ul>
</div>
    
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="access_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable"
                       checkable_list="0" data_ajax="departments-adminfiles/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->accessTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
    </div>
</div>
<!--=== /Listing ===-->
<!--===  Management ===-->
<div class="row right_column" id="access_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="access_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== /Management ===-->
</div>
<script type="text/javascript">
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
        var department_id = $('input[name="department_id"]').val();
        $.post("{Yii::$app->urlManager->createUrl('departments-adminfiles/preview')}", { 'item_id': item_id, 'department_id' : department_id }, function (data, status) {
            if (status == "success") {
                $('#access_management_data').html(data);
                $("#access_management").show();
                switchOnCollapse('access_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        // $("html, body").animate({ scrollTop: $(document).height() }, "slow");

        return false;
    }

    function accessDelete(item_id){
	if (confirm('{$smarty.const.TEXT_DELETE}')) {
                var department_id = $('input[name="department_id"]').val();
		$.post("{Yii::$app->urlManager->createUrl('departments-adminfiles/delete')}", { 'item_id': item_id, 'department_id' : department_id }, function(data, status){
			if (status == "success") {
                            resetStatement();
			} else {
                            alert("Request error.");
			}
		},"html");
	}
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
        $("#access_management").hide();

        switchOnCollapse('access_list_box_collapse');
        switchOffCollapse('access_management_collapse');

        $('access_management_data').html('');
        $('#access_management').hide();

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

