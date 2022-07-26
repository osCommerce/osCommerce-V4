{use class="yii\helpers\Html"}
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
			  
								<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-ordering table-tax_rates" order_list="1" order_by ="asc" checkable_list="0,1,2" data_ajax="tax_rates/list">
									<thead>
										<tr>
                                                                                    {foreach $app->controller->view->tax_ratesTable as $tableItem}
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
                                        $("#tax_rates_management").hide();
                                        switchOnCollapse('tax_rates_list_collapse');
                                        var table = $('.table').DataTable();
                                        table.draw(false);
                                        $(window).scrollTop(0);
                                        return false;
                                    }
                                    function onClickEvent(obj, table) {
                                        $("#tax_rates_management").hide();
                                        $('#tax_rates_management_data .scroll_col').html('');
                                        $('#row_id').val(table.find(obj).index());
                                        var tax_rates_id = $(obj).find('input.cell_identify').val();
                                        $.post("tax_rates/tax_ratesactions", { 'tax_rates_id' : tax_rates_id }, function(data, status){
                                                if (status == "success") {
                                                    $('#tax_rates_management_data .scroll_col').html(data);
                                                    $("#tax_rates_management").show();
                                                } else {
                                                    alert("Request error.");
                                                }
                                            },"html");
                                    }
                                    
                                    function onUnclickEvent(obj, table) {
                                        $("#tax_rates_management").hide();
                                        var event_id = $(obj).find('input.cell_identify').val();
                                        var type_code = $(obj).find('input.cell_type').val();
                                        $(table).DataTable().draw(false);
                                    }

function taxEdit(id){
$("#tax_rates_management").hide();
$.get("tax_rates/edit", { 'tax_rates_id' : id }, function(data, status){
    if (status == "success") {
        $('#tax_rates_management_data .scroll_col').html(data);
        $("#tax_rates_management").show();
        switchOffCollapse('tax_rates_list_collapse');
    } else {
        alert("Request error.");
    }
},"html");
                                        return false;
}							

function taxSave(id){
$.post("tax_rates/save?tax_rates_id="+id, $('form[name=rates]').serialize(), function(data, status){
    if (status == "success") {
        //$('#tax_rates_management_data').html(data);
        //$("#tax_rates_management").show();
		$('.alert #message_plce').html('');
		$('.alert').show().removeClass('alert-danger alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
        if (data['error'] != 1) {
            resetStatement();
            switchOffCollapse('tax_rates_list_collapse');
        }
    } else {
        alert("Request error.");
    }
},"json");
                                        return false;	
}

function taxDelete(id){
	if (confirm('Do you confirm?')){
		$.post("tax_rates/delete", { 'tax_rates_id' : id}, function(data, status){
			if (status == "success") {
				//$('.alert #message_plce').html('');
				//$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
				if (data == 'reset') {
					resetStatement();
				} else{
					$('#tax_rates_management_data .scroll_col').html(data);
					$("#tax_rates_management").show();
				}
				switchOnCollapse('tax_rates_list_collapse');
			} else {
				alert("Request error.");
			}
		},"html");
											return false;		
	}
}

function linkNS(r_id, l_id, directory_id) {
{if is_array($ns_tax_rates)}
  var tr_bb_content = [];
  {foreach $ns_tax_rates as $d_id => $ns_tax_rates_d}
    {if is_array($ns_tax_rates_d)}
      tr_bb_content['{$d_id}'] = '{$smarty.const.TEXT_ENTER_REMOTE_ID|escape:'javascript'}<br>{Html::dropDownList('ns_id', $ns_tax_rate, $ns_tax_rates_d, ['class'=>'form-control', 'id' => "ns_id`$d_id`"])|escape:'javascript'}';
    {/if}
  {/foreach}

  bootbox.confirm(tr_bb_content[directory_id], function(result){
  if (result){
      $.post("{Yii::$app->urlManager->createUrl('tax_rates/ns-sync-update-id')}", 'r_id=' + r_id + '&l_id=' + l_id + '&n_id=' + $('#ns_id' + directory_id).val() + '&d_id=' + directory_id, function(data){
        if ( data && data.status && data.status=='OK' ) {
            resetStatement();
        } else {
            alert("Request error.");
        }
      },'json');
    }
  });
{/if}
}
					</script>
                                <!--===Actions ===-->
				<div class="row right_column" id="tax_rates_management">
						<div class="widget box">
							<div class="widget-content fields_style" id="tax_rates_management_data">
                                <div class="scroll_col"></div>
							</div>
						</div>
                                </div>
				<!--===Actions ===-->
				<!-- /Page Content -->		
</div>
