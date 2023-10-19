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
              <li class="platform-tab {if $platform['id']== $first_platform_id} active {/if}" data-platform_id="{$platform['id']}"><a onclick="loadModules('socials/list?platform_id={$platform['id']}')" data-toggle="tab"><span>{$platform['text']}</span></a></li>
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
                    <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable double-grid" checkable_list="" data-b-paginate="false" data-paging="false" data-info="false" displayLength = "-1" data_ajax="socials/list?platform_id={$first_platform_id}">
                        <thead>
                        <tr>
                            {foreach $app->controller->view->tabList as $tableItem}
                                <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if $tableItem['not_important'] == 3} class="status-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                            {/foreach}
                        </tr>
                        </thead>
                    </table>
                    <p class="btn-wr">
                        <input type="button" class="btn btn-primary" value="{$smarty.const.IMAGE_INSERT}" onClick="return itemEdit(0)">
                    </p>
                </div>
        </div>
    </div>
    <!--===Actions ===-->
    <div class="row right_column" id="socials_management" style="display: none;">
            <div class="widget box">
                <div class="widget-content fields_style" id="socials_management_data">
                    <div class="scroll_col"></div>
                </div>
            </div>
    </div>
    <!--===Actions ===-->
</div>

<script type="text/javascript">

function itemPreview(id){
          $.get("socials/preview", { 'socials_id' : id }, function(data, status){
              if (status == "success") {
                  $('#socials_management_data .scroll_col').html(data);
                  //$("#item_management").show();
                  //switchOffCollapse('status_list_collapse');
              } else {
                  alert("Request error.");
              }
          },"html");
          return false;    
}


function changeStatus(socials_id, status){
    $.post('socials/change',
			{
				'socials_id' :socials_id,
                {if $isMultiPlatforms}
                'platform_id': $('.platform-tab.active').attr('data-platform_id'),
                {else}
                'platform_id': {$default_platform_id},
                {/if}
				'status' : status
			},
			function(data, status){
               if (status == "success") {
                    resetStatement();
                    alertMessage(data);
                } else {
                    alert("Request error.");
                }
			},
			'html'
		);	
    return;
}

function moduleTest(id){
 //   
}

function onClickEvent(obj, table) {

    var param_id = $(obj).find('input.cell_identify').val();
    $('#row_id').val(table.find(obj).index());  

    $(".check_on_off").bootstrapSwitch(
      {
		onSwitchChange: function () {
            param_id = $(this).parents('tr').find('input.cell_identify').val();
            $('#row_id').val(table.find($(this).parents('tr')).index());
			changeStatus(param_id, this.checked);
			return true;
		},
		onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
      }
    );    
    
    itemPreview(param_id);
}

function onUnclickEvent(obj, table) {
}
function resetStatement() {
    //$("#modules_management").hide();

    var table = $('.table').DataTable();
    table.draw(false);
    return false;
}

function loadModules(url){
    var table = $('.table').DataTable();
     
    table.ajax.url( url ).load();
}

function itemEdit(id){
          //$("#socials_management").hide();
          $.get("socials/new", { 
            'socials_id' : 0 , 
            {if $isMultiPlatforms}
            'platform_id': $('.platform-tab.active').attr('data-platform_id'),
            {else}
            'platform_id': {$default_platform_id},
            {/if}
            }, function(data, status){
              if (status == "success") {
                  $('#socials_management_data .scroll_col').html(data);
                  //$("#item_management").show();
                  //switchOffCollapse('status_list_collapse');
              } else {
                  alert("Request error.");
              }
          },"html");
          return false;
}

function itemSave(){
    $.post("socials/save",
        $('form[name=social_form]').serialize(),
        function(data, status){
            if (status == "success"){                
                $('.alert #message_plce').html(data.message);
                $('.alert').addClass('alert-'+data.type).show();
                resetStatement();
            }
        },"json");
    return false;
}

function moduleDelete(id){

            bootbox.dialog({
                message: "{$smarty.const.TEXT_SOCIAL_REMOVE_CONFIRM}",
                title: "{$smarty.const.TEXT_SOCIAL_REMOVE_CONFIRM_HEAD}",
                buttons: {
                    success: {
                        label: "{$smarty.const.TEXT_BTN_YES}",
                        className: "btn-delete",
                        callback: function(){
                            $.post("socials/delete",
                                {
                                'socials_id' : id , 
                                },
                                function(data, status){
                                    if (status == "success"){                
                                        resetStatement();
                                    }
                                },"html");
                        }
                    },
                    cancel: {
                        label: "{$smarty.const.TEXT_BTN_NO}",
                        className: "btn-cancel",
                        callback: function () {
                            //console.log("Primary button");
                        }
                    }
                }
            });

    return false;
}

</script>