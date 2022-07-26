
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
			  
								<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-ordering table-zones" order_list="0" order_by ="asc" checkable_list="0,1,2" data_ajax="zones/list">
									<thead>
										<tr>
                                                                                    {foreach $app->controller->view->zonesTable as $tableItem}
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
                                        $("#zones_management").hide();
                                        switchOnCollapse('zones_list_collapse');
                                        var table = $('.table').DataTable();
                                        table.draw(false);
                                        $(window).scrollTop(0);
                                        return false;
                                    }
                                    function onClickEvent(obj, table) {
                                        $("#zones_management").hide();
                                        $('#zones_management_data .scroll_col').html('');
                                        $('#row_id').val(table.find(obj).index());
                                        var zones_id = $(obj).find('input.cell_identify').val();
                                        $.post("zones/zonesactions", { 'zones_id' : zones_id }, function(data, status){
                                                if (status == "success") {
                                                    $('#zones_management_data .scroll_col').html(data);
                                                    $("#zones_management").show();
                                                } else {
                                                    alert("Request error.");
                                                }
                                            },"html");
                                    }
                                    
                                    function onUnclickEvent(obj, table) {
                                        $("#zones_management").hide();
                                        var event_id = $(obj).find('input.cell_identify').val();
                                        var type_code = $(obj).find('input.cell_type').val();
                                        $(table).DataTable().draw(false);
                                    }

function zoneEdit(id){
$("#zones_management").hide();
$.get("zones/edit", { 'zones_id' : id }, function(data, status){
    if (status == "success") {
        $('#zones_management_data .scroll_col').html(data);
        $("#zones_management").show();
        switchOffCollapse('zones_list_collapse');
    } else {
        alert("Request error.");
    }
},"html");
                                        return false;
}							

function zoneSave(id){
$.post("zones/save?zones_id="+id, $('form[name=zones]').serialize(), function(data, status){
    if (status == "success") {
        //$('#zones_management_data').html(data);
        //$("#zones_management").show();
		$('.alert #message_plce').html('');
		$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
		resetStatement();
        switchOffCollapse('zones_list_collapse');
    } else {
        alert("Request error.");
    }
},"json");
                                        return false;	
}

function zoneDelete(id){
	if (confirm('Do you confirm?')){
		$.post("zones/delete", { 'zones_id' : id}, function(data, status){
			if (status == "success") {
				//$('.alert #message_plce').html('');
				//$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
				if (data == 'reset') {
					resetStatement();
				} else{
					$('#zones_management_data .scroll_col').html(data);
					$("#zones_management").show();
				}
				switchOnCollapse('zones_list_collapse');
			} else {
				alert("Request error.");
			}
		},"html");
											return false;		
	}
}
					</script>
                                <!--===Actions ===-->
				<div class="row right_column" id="zones_management">
						<div class="widget box">
							<div class="widget-content fields_style" id="zones_management_data">
                                <div class="scroll_col"></div>
							</div>
						</div>
                                </div>
				<!--===Actions ===-->
				<!-- /Page Content -->		
</div>