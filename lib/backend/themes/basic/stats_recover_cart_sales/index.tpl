
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$this->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
           <!--=== Page Content ===-->
				<div class="row">
					<div class="col-md-12">
						<div class="widget box">
							<div class="widget-header">
              {$header_title_additional}
								<h4><i class="icon-reorder"></i> {$this->view->headingTitle}</h4>
                <!--                
								<div class="toolbar no-padding">
									<div class="btn-group">
										<span id="stats_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
									</div>
								</div>
              -->                
							</div>
							<div class="widget-content">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable">
                                                                                    {foreach $cline as $tableRow}
                                                                                      <tr>
                                                                                        {foreach $tableRow as $tableItem}
                                                                                        <td>{$tableItem}</td>
                                                                                        {/foreach}
                                                                                      </tr>
                                                                                    {/foreach}
                </table>
              
								<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0,1,2">
									<thead>
										<tr>
                                                                                    {foreach $header as $tableItem}
                                                                                        <th>{$tableItem}</th>
                                                                                    {/foreach}
										</tr>
									</thead>
                  <tfoot>
                    <tr>
                                                                                    {foreach $footer as $tableItem}
                                                                                        <th>{$tableItem}</th>
                                                                                    {/foreach}                    
                    </tr>
                  </tfoot>                  
                                                                                    {foreach $custlist as $tableRow}
                                                                                      <tr>
                                                                                        {foreach $tableRow as $tableItem}
                                                                                        <td>{$tableItem}</td>
                                                                                        {/foreach}
                                                                                      </tr>
                                                                                    {/foreach}									

								</table>

							</div>
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
                                    $("#customer_management").hide();
                                    switchOnCollapse('customers_list_collapse');
                                    var table = $('.table').DataTable();
                                    table.draw(false);
                                    return false;
                                }
                                function onClickEvent(obj) {
$("#customer_management").hide();
var customers_id = $(obj).find('input.cell_identify').val();
$.post("customers/customeractions", { 'customers_id' : customers_id }, function(data, status){
    if (status == "success") {
        $('#customer_management_data').html(data);
        $("#customer_management").show();
        
    } else {
        alert("Request error.");
        //$("#customer_management").hide();
    }
},"html");
                                }
                                function onUnclickEvent(obj) {
                                    $("#customer_management").hide();
                                }
                                function editCustomer(customers_id) {
$("#customer_management").hide();
$.post("customers/customeredit", { 'customers_id' : customers_id }, function(data, status){
    if (status == "success") {
        $('#customer_management_data').html(data);
        $("#customer_management").show();
        switchOffCollapse('customers_list_collapse');
    } else {
        alert("Request error.");
        //$("#customer_management").hide();
    }
},"html");
                                    return false;
                                }
                                function check_form() {
                                    //ajax save
                                    $("#customer_management").hide();
var customers_id = $( "input[name='customers_id']" ).val();
$.post("customers/customersubmit", $('#customers_edit').serialize(), function(data, status){
    if (status == "success") {
        //$('#customer_management_data').html(data);
        //$("#customer_management").show();
        switchOnCollapse('customers_list_collapse');
        var table = $('.table').DataTable();
        table.draw(false);
        setTimeout('$(".cell_identify[value=\''+customers_id+'\']").click();', 500);
        //$(".cell_identify[value='"+customers_id+"']").click();
    } else {
        alert("Request error.");
        //$("#customer_management").hide();
    }
},"html");
                                    //$('#customer_management_data').html('');
                                    return false;
                                }
                                function deleteCustomer() {
$("#customer_management").hide();
$.post("customers/customerdelete", $('#customers_edit').serialize(), function(data, status){
    if (status == "success") {
        resetStatement()
    } else {
        alert("Request error.");
    }
},"html");
                                    return false;
                                }
                                function confirmDeleteCustomer(customers_id) {
$("#customer_management").hide();
$.post("customers/confirmcustomerdelete", { 'customers_id' : customers_id }, function(data, status){
    if (status == "success") {
        $('#customer_management_data').html(data);
        $("#customer_management").show();
        switchOffCollapse('customers_list_collapse');
    } else {
        alert("Request error.");
        //$("#customer_management").hide();
    }
},"html");
                                    return false;
                                }                                
                            </script>
        				<!--===Actions ===-->
				<div class="row" id="customer_management" style="display: none;">
					<div class="col-md-12">
						<div class="widget box">
							<div class="widget-header">
								<h4><i class="icon-reorder"></i> Customer Management</h4>
								<div class="toolbar no-padding">
									<div class="btn-group">
										<span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
									</div>
								</div>
							</div>
							<div class="widget-content fields_style" id="customer_management_data">
                                                            Action
							</div>
						</div>
					</div>
                                </div>
				<!--===Actions ===-->
