
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
			  
								<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-ordering table-cities" order_list="0" order_by ="asc" checkable_list="0,1,2" data_ajax="cities/list">
									<thead>
										<tr>
                                                                                    {foreach $app->controller->view->citiesTable as $tableItem}
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
                                        $("#cities_management").hide();
                                        switchOnCollapse('cities_list_collapse');
                                        var table = $('.table').DataTable();
                                        table.draw(false);
                                        $(window).scrollTop(0);
                                        return false;
                                    }
                                    function onClickEvent(obj, table) {
                                        $("#cities_management").hide();
                                        $('#cities_management_data .scroll_col').html('');
                                        $('#row_id').val(table.find(obj).index());
                                        var cities_id = $(obj).find('input.cell_identify').val();
                                        $.post("cities/citiesactions", { 'cities_id' : cities_id }, function(data, status){
                                                if (status == "success") {
                                                    $('#cities_management_data .scroll_col').html(data);
                                                    $("#cities_management").show();
                                                } else {
                                                    alert("Request error.");
                                                }
                                            },"html");
                                    }
                                    
                                    function onUnclickEvent(obj, table) {
                                        $("#cities_management").hide();
                                        var event_id = $(obj).find('input.cell_identify').val();
                                        var type_code = $(obj).find('input.cell_type').val();
                                        $(table).DataTable().draw(false);
                                    }

function cityEdit(id){
$("#cities_management").hide();
$.get("cities/edit", { 'cities_id' : id }, function(data, status){
    if (status == "success") {
        $('#cities_management_data .scroll_col').html(data);
        $("#cities_management").show();
        switchOffCollapse('cities_list_collapse');
    } else {
        alert("Request error.");
    }
},"html");
                                        return false;
}							

function citySave(id){
$.post("cities/save?cities_id="+id, $('form[name=cities]').serialize(), function(data, status){
    if (status == "success") {
        //$('#cities_management_data').html(data);
        //$("#cities_management").show();
		$('.alert #message_plce').html('');
		$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
		resetStatement();
        switchOffCollapse('cities_list_collapse');
    } else {
        alert("Request error.");
    }
},"json");
                                        return false;	
}

function cityDelete(id){
	if (confirm('Do you confirm?')){
		$.post("cities/delete", { 'cities_id' : id}, function(data, status){
			if (status == "success") {
				//$('.alert #message_plce').html('');
				//$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
				if (data == 'reset') {
					resetStatement();
				} else{
					$('#cities_management_data .scroll_col').html(data);
					$("#cities_management").show();
				}
				switchOnCollapse('cities_list_collapse');
			} else {
				alert("Request error.");
			}
		},"html");
											return false;		
	}
}

function update_zone(theForm) {
	var NumState = theForm.city_zone_id.options.length;
	var SelectedCountry = "";

	while(NumState > 0) {
		NumState--;
		theForm.city_zone_id.options[NumState] = null;
	}

	SelectedCountry = theForm.city_country_id.options[theForm.city_country_id.selectedIndex].value;

	{tep_js_zone_list('SelectedCountry', 'theForm', 'city_zone_id')}

}
					</script>
                                <!--===Actions ===-->
				<div class="row right_column" id="cities_management">
						<div class="widget box">
							<div class="widget-content fields_style" id="cities_management_data">
                                <div class="scroll_col"></div>
							</div>
						</div>
                                </div>
				<!--===Actions ===-->
				<!-- /Page Content -->		
</div>