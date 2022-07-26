<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->


<!--===backup list===-->
<div class="order-wrap">
<div class="row container-box-list">
    <div class="col-md-12">
        <div class="widget-content">
		{$backupPath}
            <table class="table table-striped table-checkable table-hover table-responsive table-bordered datatable table-backup" data_ajax="backup/list" checkable_list="">
                <thead>
                    <tr>
                        {foreach $app->controller->view->backupTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                            {/foreach}
                    </tr>
                </thead>

            </table>
        </div>
    </div>
{$forget}
</div>

<!--===/backup list===-->
<script>

$(document).ready(function(){
	$('.backup, .restore').on('click', function(e){
		var ev = e||window.event;
		ev.preventDefault();
		var href = $(this).attr('href');
		if (href.length > 0){
			$.get(href, {}, function(data, status){
				if (status == "success") {
					$('#backup_management_data .scroll_col').html(data);
					$("#backup_management").show();
					deleteScroll();
					heightColumn();
				} else {
					alert("Request error.");
				}				
			})
		}
	})
})

function viewModule(file){
	$.get('backup/view', {
		file:file,
	}, 
	function(data, status){
            if (status == "success") {
                $('#backup_management_data .scroll_col').html(data);
                $("#backup_management").show();
                deleteScroll();
                heightColumn();
            } else {
                alert("Request error.");
            }	
	});
}

function actionFile(file, action){
	$.get('backup/'+ action, {
		file:file,
	}, 
	function(data, status){
            if (status == "success") {
                $('#backup_management_data .scroll_col').html(data);
                $("#backup_management").show();
                deleteScroll();
                heightColumn();
            } else {
                alert("Request error.");
            }	
	});
}

function onClickEvent (obj, table){
	var event_id = $(obj).find('input.cell_identify').val();
	 viewModule(event_id);
}

function onUnclickEvent(obj, table){
}

    function resetStatement() {

        //switchOnCollapse('groups_list_box_collapse');
        //switchOffCollapse('groups_management_collapse');

        $('#backup_management_data .scroll_col').html('');
        $('#backup_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        $(window).scrollTop(0);

        return false;
    }
</script>


<!--===  backup management ===-->
<div class="row right_column" id="backup_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="backup_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== backup management ===-->
</div>
