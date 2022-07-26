{use class="yii\helpers\Html"}
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
			  
                <div class="btn-wr after btn-wr-top disable-btn">
                    <div>
                        <a href="javascript:void(0)" onclick="approveSelectedItems();" class="btn btn-no-margin">{$smarty.const.TEXT_ENABLE_SELECTED}</a><a href="javascript:void(0)" onclick="declineSelectedItems();" class="btn">{$smarty.const.TEXT_DISABLE_SELECTED}</a><a href="javascript:void(0)" onclick="deleteSelectedItems();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                    </div>
                    <div>
                    </div>
                </div> 
                    <table class="table tabl-res table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable table-switch-on-off" checkable_list="" data_ajax="countries/list">
                            <thead>
                                    <tr>
                                        {foreach $app->controller->view->countriesTable as $tableItem}
                                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                        {/foreach}
                                    </tr>
                            </thead>

                    </table>            

                </form>
            </div>
				
					</div>
				</div>
<script type="text/javascript">
function getTableSelectedIds() {
    var selected_messages_ids = [];
    var selected_messages_count = 0;
    $('input:checkbox:checked.uniform').each(function(j, cb) {
        var aaa = $(cb).closest('td').find('.cell_identify').val();
        if (typeof(aaa) != 'undefined') {
            selected_messages_ids[selected_messages_count] = aaa;
            selected_messages_count++;
        }
    });
    return selected_messages_ids;
}

function getTableSelectedCount() {
    var selected_messages_count = 0;
    $('input:checkbox:checked.uniform').each(function(j, cb) {
        var aaa = $(cb).closest('td').find('.cell_identify').val();
        if (typeof(aaa) != 'undefined') {
            selected_messages_count++;
        }
    });
    return selected_messages_count;
}

function approveSelectedItems() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        $.post("{Yii::$app->urlManager->createUrl('countries/approve-selected')}", { 'selected_ids' : selected_ids }, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    }
    return false;
}

function declineSelectedItems() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        $.post("{Yii::$app->urlManager->createUrl('countries/decline-selected')}", { 'selected_ids' : selected_ids }, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
    }
    return false;
}

function deleteSelectedItems() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        
        bootbox.dialog({
                message: "{$smarty.const.TEXT_DELETE_SELECTED}?",
                title: "{$smarty.const.TEXT_DELETE_SELECTED}",
                buttons: {
                        success: {
                                label: "Yes",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("{Yii::$app->urlManager->createUrl('countries/delete-selected')}", { 'selected_ids' : selected_ids }, function(data, status){
                                        if (status == "success") {
                                            resetStatement();
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
                                }
                        },
                        main: {
                                label: "Cancel",
                                className: "btn-cancel",
                                callback: function() {
                                        //console.log("Primary button");
                                }
                        }
                }
        });
    }
    return false;
}

function switchOffCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-down')) {
        $("#"+id).click();
    }
}

function switchStatement(id, status) {
    $.post("{Yii::$app->urlManager->createUrl('countries/switch-status')}", { 'id' : id, 'status' : status }, function(data, status){
        if (status == "success") {
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
}

function switchOnCollapse(id) {
    if ($("#"+id).children('i').hasClass('icon-angle-up')) {
        $("#"+id).click();
    }
}

function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}

function resetStatement(id) {
    $("#countries_management").hide();
    switchOnCollapse('countries_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    //$(window).scrollTop(0);
    return false;
}

function applyFilter() {
    $("#row_id").val(0);
    resetStatement();
    return false;    
}

function onClickEvent(obj, table) {
    $("#countries_management").hide();
    $('#countries_management_data .scroll_col').html('');
    $('#row_id').val(table.find(obj).index());
    setFilterState();
    var countries_id = $(obj).find('input.cell_identify').val();
    $(".check_on_off").bootstrapSwitch(
    {
        onSwitchChange: function (element, arguments) {
            console.log(element);
            switchStatement(element.target.value, arguments);
            return true;  
        },
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    }
);
    $.post("{Yii::$app->urlManager->createUrl('countries/countriesactions')}", { 'countries_id' : countries_id }, function(data, status){
            if (status == "success") {
                $('#countries_management_data .scroll_col').html(data);
                $("#countries_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
}

function onUnclickEvent(obj, table) {
    var event_id = $(obj).find('input.cell_identify').val();
}

function countryEdit(id){
$("#countries_management").hide();
$.get("countries/edit", { 'countries_id' : id }, function(data, status){
    if (status == "success") {
        $('#countries_management_data .scroll_col').html(data);
        $("#countries_management").show();
        switchOffCollapse('countries_list_collapse');
    } else {
        alert("Request error.");
    }
},"html");
                                        return false;
}							

function countrySave(id){
$.post("{Yii::$app->urlManager->createUrl('countries/save')}?countries_id="+id, $('form[name=countries]').serialize(), function(data, status){
    if (status == "success") {
        //$('#countries_management_data').html(data);
        //$("#countries_management").show();
		$('.alert #message_plce').html('');
		$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
		resetStatement();
        setTimeout('$(".cell_identify[value=\''+id+'\']").click();', 500);		
    } else {
        alert("Request error.");
    }
},"json");
                                        return false;	
}

function countryDelete(id){
	if (confirm('Do you confirm?')){
		$.post("{Yii::$app->urlManager->createUrl('countries/delete')}", { 'countries_id' : id}, function(data, status){
			if (status == "success") {
				//$('.alert #message_plce').html('');
				//$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
				if (data == 'reset') {
					resetStatement();
				} else{
					$('#countries_management_data .scroll_col').html(data);
					$("#countries_management").show();
				}
				switchOnCollapse('countries_list_collapse');
			} else {
				alert("Request error.");
			}
		},"html");
											return false;		
	}
}

function linkNS(r_id, l_id, directory_id) {
{if is_array($ns_countries)}
  bootbox.confirm( '{$smarty.const.TEXT_ENTER_REMOTE_ID|escape:'javascript'}<br>{Html::dropDownList('ns_id', $ns_country, $ns_countries, ['class'=>'form-control', 'id' => 'ns_id'])|escape:'javascript'}', function(result){
    if (result){
      $.post("{Yii::$app->urlManager->createUrl('countries/ns-sync-update-id')}", 'r_id=' + r_id + '&l_id=' + l_id + '&n_id=' + $('#ns_id').val() + '&d_id=' + directory_id, function(data){
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


$(document).ready(function() {
    $('th.checkbox-column .uniform').click(function() {
        if($(this).is(':checked')){
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    }); 
})

					</script>
                                <!--===Actions ===-->
				<div class="row right_column" id="countries_management">
						<div class="widget box">
							<div class="widget-content fields_style" id="countries_management_data">
                                <div class="scroll_col"></div>
							</div>
						</div>
                                </div>
				<!--===Actions ===-->
				<!-- /Page Content -->		
</div>