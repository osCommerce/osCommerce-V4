{use class="yii\helpers\Url"}
{use class="yii\helpers\Html"}
<div class="language_new">
  
            <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-select"></i><span>Select Language</span></h4>
            </div>
            <div class="widget-content">
              <form name="select-lang" method="post" action="{Url::to('languages/save')}" onSubmit = "return checkSelected()">
								<table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-languages" checkable_list="1,2,3" >
									<thead>
										<tr>
                      {foreach $app->controller->view->predefinedTable as $tableItem}
                        <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                      {/foreach}
										</tr>
									</thead>
									
								</table>
              <div class="alert alert-warning fade in" style="display:none;">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce"></span>
              </div>			   
                
                
                <div class="btn-bar">
                    <div class="btn-left"><a class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a></div>
                    <input type="hidden" name="action" value="predefine">
                    <div class="btn-right"><input type="submit" class="btn btn-primary" value='{$smarty.const.IMAGE_CONFIRM}'></div>
                </div>
                </form>
            </div>
        </div>
<script>
var table;
var event_id;
  
function checkSelected(){
  if ($('.language_new input[name=languages_id]:checked').length == 0){
    $('.language_new .alert').html('Please select language').show();
    return false;
  }
}
  
(function($){
  table = $('.language_new .table').dataTable({
    "processing": true,
    "serverSide": true,
    "displayLength": 10,
    "ajax": {
        "url" : 'languages/get-predefined',
        "dataSrc": function(json){
              if (typeof data_charts != 'undefined' && typeof onDraw == 'function') {
                onDraw(json.data);
              }
              if (typeof json.head == 'object' && typeof onDraw == 'function' ){
                onDraw(json, table);
              }
										  
              if (typeof(rData) == 'object' && rData != null) rData = json;

              $('.popup-box-wrap').css('top', $(window).scrollTop() + 60);        
											  
              return json.data;
            }
         },
  });
  
  function onClickEvent(obj, table) {
    table = $(table).DataTable();
    var id = table.row('.selected').index();
  }
                                    
  function onUnclickEvent(obj, table) {
    
  } 

  $(table).find('tbody').on( 'click', 'tr', function () {
    if ( $(this).hasClass('selected') ) {
      $(this).removeClass('selected');
      onUnclickEvent(this, table);
    } else {
      table.$('tr.selected').removeClass('selected');
      $(this).addClass('selected');
      $(this).find('input[name=languages_id]').prop('checked', true);
      onClickEvent(this, table);
    }
  });
  
})(jQuery)
</script>
</div>