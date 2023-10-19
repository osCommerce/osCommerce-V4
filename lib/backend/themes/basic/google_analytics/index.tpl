<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
           <!--=== Page Content ===-->
		   <style type="text/css">.dataTables_wrapper.no-footer .dataTables_footer{ display:none; } .dataTables_filter { display:none; }</style>
		<div class="widget-content ">
			<div class="" id="modules_list">
                <input type="hidden" name="row_id" value="{$app->controller->view->row_id}" id="row_id">
              <!-- TABS-->
                      <div class="tabbable tabbable-custom">
                        <ul class="nav nav-tabs tab-radius-ul tab-radius-ul-white">
                          {foreach $platforms as $platform}
                          <li {if $first_platform_id==$platform['id']} class="active"{/if} data-bs-toggle="tab" data-bs-target="#platform{$platform['id']}"><a><span>{$platform['text']}</span></a></li>
                          {/foreach}
                        </ul>
                          <div class="tab-content" id="google_list_data">
                              {foreach $platforms as $platform}
                                  <div id="platform{$platform['id']}" class="tab-pane {if $first_platform_id==$platform['id']}active{/if} platforms" data-id="{$platform['id']}">
										<table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable double-grid" checkable_list="" data-b-paginate="false" data-paging="false" data-info="false" displayLength = "-1" data_ajax="google_analytics/list?platform_id={$platform['id']}">
											<thead>
											<tr>
												{foreach $app->controller->view->tabList[$platform['id']] as $tableItem}
													<th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
												{/foreach}
											</tr>
											</thead>
										</table>
                                  </div>
                              {/foreach}
                          </div>

                 </div>

				</div>
              </div>{*widget*}
              <!--END TABS-->


<script type="text/javascript">
var lTable;
function onClickEvent(obj, table) {
    lTable = table;
    $('#row_id').val(table.find(obj).index());    
}
function onUnclickEvent(obj, table) {
}
function resetStatement(platform_id) {
    $("#modules_management").hide();
    lTable = $('#platform'+platform_id+' .table').DataTable();
    lTable.draw(false);
    return false;
}



function changeModule(module, platform_id, action){
	$confirm = false;
	if (action != 'remove'){
		$confirm = true;
	} else {
		if (confirm('{$smarty.const.TEXT_DELETE_SELECTED}?')){
		$confirm = true;
		}
	}
	if ($confirm){
		$.post('google_analytics/change',
			{
				'module' :module,
				'platform_id': platform_id,
				'action' : action
			},
			function(data, status){
               if (status == "success") {
                    resetStatement(platform_id);
                } else {
                    alert("Request error.");
                }
			},
			'html'
		);	
	}

}


function BootstrapIt(module, platform_id){
	$('input[data-module='+module+'][data-platform_id='+platform_id+'].check_on_off').bootstrapSwitch(
		{
		onSwitchChange: function (element, arguments) {
		  $.post('google_analytics/change',
				{
				'module' :element.target.dataset.module,
				'platform_id': element.target.dataset.platform_id,
				'action' : 'status',
				'status': arguments,
				}, 
				function (data, status){
					if (status == "success") {
						resetStatement(element.target.dataset.platform_id);
					} else {
						alert("Request error.");
					}				
				},
				'html'
		  );
		  return true;
		},
		onText: "{$smarty.const.SW_ON}",
		offText: "{$smarty.const.SW_OFF}",
		handleWidth: '38px',
		labelWidth: '24px'
	  }	
	);
}
var _table;
$(document).ready(function(){
    $(document).on('click', '.btn-edit', function(e){
        var row_id = 0;
        $.each(lTable.fnGetNodes(), function(i,e){
            if ($(e).hasClass('selected')){
                row_id = i;
            }
        });
        $(this).attr('href', $(this).attr('href') + '&row_id=' + row_id + '&platform_id='+$('.platforms.active').data('id'));
    });
    
    _table = $('.platforms.active .table').dataTable();
    $(_table).on('init.dt', function(){
        $('.platforms.active .table tbody tr:eq(' + $("#row_id").val() + ')').addClass('selected');
    })
})

</script>