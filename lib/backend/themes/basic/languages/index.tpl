{use class="yii\helpers\Url"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="order-wrap">
<input type="hidden" id="row_id" value="{$row}">
           <!--=== Page Content ===-->
				<div class="row order-box-list">
					<div class="col-md-12">
							<div class="widget-content">
              <div class="alert fade in" style="display:none;">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"></span>
              </div>   	
			  {if {$messages|default:array()|@count} > 0}
			   {foreach $messages as $messageType => $message}
              <div class="alert fade in alert-{$messageType}">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message}</span>
              </div>			   
			   {/foreach}
			  {/if}
								<table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable" checkable_list="0" data_ajax="languages/list">
									<thead>
										<tr>
                                                                                    {foreach $app->controller->view->languagesTable as $tableItem}
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
                                        $("#language_management").hide();
                                        switchOnCollapse('language_list_collapse');
                                        var table = $('.table').DataTable();
                                        table.draw(false);
                                        $(window).scrollTop(0);
                                        return false;
                                    }
                                    function onClickEvent(obj, table) {
                                        $("#language_management").hide();
                                        $('#language_management_data .scroll_col').html('');
                                        $('#row_id').val(table.find(obj).index());
                                        var languages_id = $(obj).find('input.cell_identify').val();
                                        $(".check_on_off").bootstrapSwitch(
                                                        {
                                                            onSwitchChange: function (elements, arguments) {
                                                                switchStatement(elements.target.dataset.langid, arguments);
                                                                return true;  
                                                            },
                                                            onText: "{$smarty.const.SW_ON}",
                                                            offText: "{$smarty.const.SW_OFF}",
                                                            handleWidth: '20px',
                                                            labelWidth: '24px'
                                                        }
                                        );
                                        
                                        $.post("languages/language-actions", { 'languages_id' : languages_id , 'row' : $('#row_id').val() }, function(data, status){
                                                if (status == "success") {
                                                    $('#language_management_data .scroll_col').html(data);
                                                    $("#language_management").show();
                                                } else {
                                                    alert("Request error.");
                                                }
                                            },"html");
                                    }
                                    
                                    function onUnclickEvent(obj, table) {
                                        $("#language_management").hide();
                                        var event_id = $(obj).find('input.cell_identify').val();
                                        var type_code = $(obj).find('input.cell_type').val();
                                        $(table).DataTable().draw(false);
                                    }

function languageEdit(id){
$("#language_management").hide();
$.get("languages/edit", { 'languages_id' : id }, function(data, status){
    if (status == "success") {
        $('#language_management_data .scroll_col').html(data);
        $("#language_management").show();
          
    } else {
        alert("Request error.");
    }
},"html");
                                        return false;
}							

function languageSave(id){
$.post("languages/save?languages_id="+id, $('form[name=languages]').serialize(), function(data, status){
    if (status == "success") {
        //$('#language_management_data').html(data);
        //$("#language_management").show();
		$('.alert #message_plce').html('');
		$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
		resetStatement();
        switchOffCollapse('language_list_collapse');
    } else {
        alert("Request error.");
    }
},"json");
                                        return false;	
}

function languageDelete(id){
	if (confirm('Do you confirm?')){
		$.post("languages/delete", { 'languages_id' : id}, function(data, status){
			if (status == "success") {
				//$('.alert #message_plce').html('');
				//$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
				if (data == 'reset') {
					resetStatement();
				} else if (data == 'reload'){
          window.location.reload();
        } else{
          
					$('#language_management_data .scroll_col').html(data);
					$("#language_management").show();
				}
				switchOnCollapse('language_list_collapse');
			} else {
				alert("Request error.");
			}
		},"html");
											return false;		
	}
}
function switchStatement(languages_id, languages_status) {
    $.post("languages/switch-status", { 'languages_id' : languages_id, 'languages_status' : languages_status }, function(data, status){
        if (status == "success") {
          if (data == 'reset') {
            resetStatement();
          } else if (data == 'reload'){
            window.location.reload();
          }
          //resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
}

$(document).ready(function(){
  $('.new-language').popUp();  
  
      $( ".js-table-sortable.datatable tbody" ).sortable({
        axis: 'y',
        update: function( event, ui ) {
          $(this).find('[role="row"]').each(function() {
            if ( this.id ) return;
            var cell_ident = $(this).find('.cell_identify');
            var cell_type = $(this).find('.cell_type');
            if ( cell_ident.length>0 && cell_type.length>0 ) {
              this.id = cell_type.val()+'_'+cell_ident.val();
            }
          });
          var post_data = [];
          $(this).find('[role="row"]').each(function() {
            var spl = this.id.indexOf('_');
            if ( spl===-1 ) return;
            post_data.push({ name:this.id.substring(0, spl)+'[]', value:this.id.substring(spl+1) });
          });
          var $dropped = $(ui.item);
          post_data.push({ name:'sort_'+$dropped.find('.cell_type').val(), value:$dropped.find('.cell_identify').val() });

          $.post("{Yii::$app->urlManager->createUrl('languages/sort-order')}", post_data, function(data, status){
            if (status == "success") {
              resetStatement();
            } else {
              alert("Request error.");
            }
          },"html");
        },
        handle: ".handle"
      }).disableSelection();  
})
					</script>
                                <!--===Actions ===-->
				<div class="row right_column" id="language_management">
						<div class="widget box">
							<div class="widget-content fields_style" id="language_management_data">
                               <div class="scroll_col"></div>                             
							</div>
						</div>
                                </div>
				<!--===Actions ===-->
				<!-- /Page Content -->
</div>