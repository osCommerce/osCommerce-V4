				<!--=== Page Header ===-->
				<div class="page-header">
					<div class="page-title">
						<h3>{$app->controller->view->headingTitle}</h3>
					</div>
				</div>
				<!-- /Page Header -->
                                
                                <!--=== Page Content ===-->
<div class="order-wrap">
<input type="hidden" id="row_id">
                                <!--===Zones List ===-->
				<div class="row order-box-list">
					<div class="col-md-12">		
							<div class="widget-content">
								<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0" data_ajax="tax-zones/list">
									<thead>
										<tr>
                                                                                    {foreach $app->controller->view->catalogTable as $tableItem}
                                                                                        <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                                                                    {/foreach}
										</tr>
									</thead>
									
								</table>
							</div>

					</div>
				</div>
                                <!-- /Zones List -->
                                
                                <!--===Countries List ===-->
				<!--<div class="row"  id="subzone_management" style="display: none;">
					<div class="col-md-12">
						<div class="widget box">
							<div class="widget-header">
								<h4><i class="icon-reorder"></i> Sub Zone Listing</h4>
								<div class="toolbar no-padding">
									<div class="btn-group">
										<span id="catalog_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
									</div>
								</div>
							</div>
							<div class="widget-content">
								<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" id="subzones" checkable_list="0,1" data_ajax="tax-zones/content">
									<thead>
										<tr>
                                                                                    {foreach $app->controller->view->zoneTable as $tableItem}
                                                                                        <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                                                                    {/foreach}
										</tr>
									</thead>
									
								</table>
                                                                <p class="btn-toolbar">
                                                                    <input type="button" class="btn btn-primary" value="Insert" onClick="return editProduct(0)">
                                                                </p>
							</div>
						</div>
					</div>
				</div>-->
                                <!-- /Countries List -->
                                               

                                <!--===Actions ===-->
                <div class="row right_column" id="catalog_management">
                    <div class="widget box">
                        <div class="widget-content" id="catalog_management_data">
                            <div class="scroll_col"></div>
                        </div>
                    </div>
                </div>
				<!--===Actions ===-->
</div>                                              
<input type="hidden" id="global_id" value="0" />
<input type="hidden" value="" id="global_type_code">
<script type="text/javascript">
function resetStatement() {
    $("#catalog_management").hide();
    var table = $('.table').DataTable();
    table.draw(false);
    $(window).scrollTop(0);
    return false;
}

function checkCatButtonsStatement() {
    $('#add_cat').show();
    $('#add_prop').hide();
}

function checkPropButtonsStatement() {
    $('#add_cat').hide();
    $('#add_prop').show();
}

function update_zone(theForm) {
  var NumState = theForm.zone_id.options.length;
  var SelectedCountry = "";

  while(NumState > 0) {
    NumState--;
    theForm.zone_id.options[NumState] = null;
  }         

  SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;

{tep_js_zone_list('SelectedCountry', 'theForm', 'zone_id')}

}

function checkProductForm() {
    $("#catalog_management").hide();
    $.post("tax-zones/productsubmit", $('#option_save').serialize(), function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function editProduct(products_id) {
    $("#catalog_management").hide();
    var geo_zone_id = $('#global_id').val();
    $.post("tax-zones/productedit", { 'products_id' : products_id, 'geo_zone_id' : geo_zone_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
                                    
function deleteProduct() {
$("#catalog_management").hide();
$.post("tax-zones/productdelete", $('#option_delete').serialize(), function(data, status){
    if (status == "success") {
        resetStatement();
    } else {
        alert("Request error.");
    }
},"html");

    return false;
}
                                    
function confirmDeleteProduct(products_id) {
    $("#catalog_management").hide();
    $.post("tax-zones/confirmproductdelete", { 'products_id' : products_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function checkCategoryForm() {
    $("#catalog_management").hide();
    $.post("tax-zones/categorysubmit", $('#option_save').serialize(), function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");    
    return false;
}

function editCategory(category_id) {
    $("#catalog_management").hide();
    $.post("tax-zones/categoryedit", { 'category_id' : category_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function deleteCategory() {
    $("#catalog_management").hide();
    $.post("tax-zones/categorydelete", $('#option_delete').serialize(), function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function confirmDeleteCategory(category_id) {
    $("#catalog_management").hide();
    $.post("tax-zones/confirmcategorydelete", { 'category_id' : category_id }, function(data, status){
        if (status == "success") {
            $('#catalog_management_data').html(data);
            $("#catalog_management").show();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}

function onClickEvent(obj, table) {
    $("#catalog_management").hide();
    $('#catalog_management_data').html('');
    $('#row_id').val(table.find(obj).index());
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    if (type_code == 'category') {
        $.post("tax-zones/categoryactions", { 'categories_id' : event_id }, function(data, status){
            if (status == "success") {
                $('#catalog_management_data').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    } else if (type_code == 'product') {
        $.post("tax-zones/productactions", { 'products_id' : event_id }, function(data, status){
            if (status == "success") {
                $('#catalog_management_data').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
    }
}

function onUnclickEvent(obj, table) {
    $("#catalog_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    
    if (type_code == 'category') {
        checkPropButtonsStatement();
    } else if (type_code == 'parent') {
            checkCatButtonsStatement();
    }
    if (type_code == 'category' || type_code == 'parent') {
        $('#global_id').val(event_id);
        $('input[type="search"]').val('');
        $(table).DataTable().search('').draw(false);
    }

}
                                    
                                    
                                    
</script>
				<!-- /Page Content -->
             