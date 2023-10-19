{use class="yii\helpers\Url"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
    <input type="hidden" name="group_id" value="{$app->controller->view->group_id}" />
</div>
<!-- /Page Header -->

{if $isMultiPlatforms}
    <div class="tabbable tabbable-custom" style="margin-bottom: 0;">
        <ul class="nav nav-tabs">
            {foreach $platforms as $platform}
              <li class="platform-tab {if $platform['id']== $first_platform_id} active {/if}" data-platform_id="{$platform['id']}"><a onclick="loadServices('printers/list?platform_id={$platform['id']}')" data-toggle="tab"><span>{$platform['text']}</span></a></li>
            {/foreach}
        </ul>
    </div>
{/if}
<!--===Group Params table===-->
<div class="order-wrap">
    <div class="row order-box-list" id="modules_list">
        <div class="col-md-12">
              {if {$messages|default:array()|@count} > 0}
			   {foreach $messages as $type => $message}
              <div class="alert alert-{$type} fade in">
                  <i data-dismiss="alert" class="icon-remove close"></i>
                  <span id="message_plce">{$message}</span>
              </div>			   
			   {/foreach}
			  {/if}        
                <div class="widget-content" id="modules_list_data">
                    <input type="hidden" name="row" id="row_id" value="0" />
                    <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable double-grid" checkable_list="" data-b-paginate="false" data-paging="false" data-info="false" displayLength = "-1" data_ajax="printers/list?platform_id={$first_platform_id}">
                        <thead>
                        <tr>
                            {foreach $app->controller->view->tabList as $tableItem}
                                <th>{$tableItem['title']}</th>
                            {/foreach}
                        </tr>
                        </thead>
                    </table>                    
                </div>
        </div>
    </div>
    <!--===Actions ===-->
    <div class="row right_column" id="socials_management" style="display: none;">
            <div class="widget box">
                <div class="widget-content fields_style" id="service_management_data">
                    <div class="scroll_col"></div>
                </div>
            </div>
    </div>
    <!--===Actions ===-->
</div>

<script type="text/javascript">

function itemPreview(id){
          $.get("printers/preview", { 'service_id' : id }, function(data, status){
              if (status == "success") {
                  $('#service_management_data .scroll_col').html(data);
              } else {
                  alert("Request error.");
              }
          },"html");
          return false;    
}

function onClickEvent(obj, table) {    
    var param_id = $(obj).find('input.cell_identify').val();
    $('#row_id').val(table.find(obj).index());
    itemPreview(param_id);
}

function onUnclickEvent(obj, table) {
}
function resetStatement() {
    var table = $('.table').DataTable();
    table.draw(false);
    return false;
}

function loadServices(url){
    var table = $('.table').DataTable();
     
    table.ajax.url( url ).load();
}


function serviceDelete(id){
    bootbox.confirm('confirm?', function(result){
        if (result){
            $.post("printers/delete",
            {
            'id' : id , 
            },
            function(data, status){
                if (status == "success"){                
                    resetStatement();
                }
            },"html");
        }
    });
    return false;
}

</script>