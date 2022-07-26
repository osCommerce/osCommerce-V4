{use class="yii\helpers\Url"}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<!--===Group Params table===-->
<div class="order-wrap">
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input type="hidden" name="groupid" id="group_id" value="{$app->controller->view->group_id}" />
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
</form>
<div class="row order-box-list" id="configuration_info">
    <div class="col-md-12">
            <div class="widget-content" id="configuration_info_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid table-configuration" checkable_list="0,1" data_ajax="{Url::toRoute(['configuration/getgroupcontent', 'groupid' => $app->controller->view->group_id])}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->adminTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>

    </div>
</div>
<!--===Group Params table===-->

<script type="text/javascript">

    function installItem(id) {
        $.post("{Yii::$app->urlManager->createUrl('configuration/install-key')}", { 'id' : id }, function(data, status){
          if (status == "success") {
            resetStatement();
          } else {
            alert("Request error.");
          }
        },"html");
    }
    
    function uninstallItem(id) {
        $.post("{Yii::$app->urlManager->createUrl('configuration/uninstall-key')}", { 'id' : id }, function(data, status){
          if (status == "success") {
            resetStatement();
          } else {
            alert("Request error.");
          }
        },"html");
    }
    
    function preEditItem( item_id, trash ) {
        $.post("{Url::toRoute(['configuration/preedit'])}", {
            'param_id': item_id,
            'trash' : trash
        }, function (data, status) {
            if (status == "success") {
                $('#configuration_management_data .scroll_col').html(data);
                $("#configuration_management").show();
               // switchOffCollapse('info_box_collapse');
                switchOnCollapse('action_box_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function editItem(item_id){
        $.post("{Url::toRoute(['configuration/getparam'])}", {
            'param_id': item_id,
        }, function (data, status) {
            if (status == "success") {
                $('#configuration_management_data .scroll_col').html(data);
                $("#configuration_management").show();
                //switchOffCollapse('info_box_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }
    
    function trashItem( item_id ){
      if (confirm("{$smarty.const.TEXT_CONFIRM_TRASHING}")){
        $.post("{Url::toRoute(['configuration/trash'])}", {
            'param_id': item_id,
        }, function (data, status) {
            if (status == "success") {
                $('#configuration_management_data .scroll_col').html(data);
                $("#configuration_management").show();
                //switchOffCollapse('info_box_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
      }
      return false;            
    }    
    
    function deleteTrashedItem(item_id){
      if (confirm("{$smarty.const.TEXT_CONFIRM_DELETING}")){
        $.post("{Url::toRoute(['configuration/delete-trashed'])}", {
            'param_id': item_id,
        }, function (data, status) {
            if (status == "success") {
                $('#configuration_management_data .scroll_col').html(data);
                $("#configuration_management").show();
                //switchOffCollapse('info_box_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
      }
      return false;      
    }

    function onClickEvent(obj, table) {
        var group_id = $( "#group_id" ).val();
        var param_id = $(obj).find('input.cell_identify').val();
        $('#row_id').val(table.find(obj).index());
        setFilterState();
        if ($(obj).find('input.cell_identify').attr('data-trash')){
          preEditItem(param_id, true);
        } else {
          preEditItem(param_id);
        }
    }

    function onUnclickEvent(obj) {
        $("#configuration_management").hide();
    }

    function saveParam(){
        $.post("{Url::toRoute(['configuration/saveparam'])}", $('#save_param_form').serialize(), function(data, status){
            if (status == "success") {
                $('#configuration_management_data .scroll_col').html(data);
                $("#configuration_management").show();
                //$("#info_box_collapse").click();
                // refresh
            } else {
                alert("Request error.");
            }
        },"html");

        return false;
    }
    
    function restoreItem( item_id ){
        $.post("{Url::toRoute(['configuration/restore-trashed'])}", {
            'param_id': item_id,
        }, function (data, status) {
            if (status == "success") {
                $('#configuration_management_data .scroll_col').html(data);
                $("#configuration_management").show();
                //switchOffCollapse('info_box_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

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

    function setFilterState() {
        orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({ }, '', url);
    }

    function resetStatement() {
        $("#configuration_management").hide();
        switchOnCollapse('info_box_collapse');
        var table = $('.table').DataTable();
        table.draw(false);
        $(window).scrollTop(0);
        return false;
    }
    
    $(document).ready(function(){
      $('.table').on('draw.dt', function () {
          $(this).find('.modules_divider').each(function(){
              $(this).parent('td').addClass('divider_cell');
          });
      } );    
    })
</script>
<!--===Actions ===-->
<div class="row right_column" id="configuration_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="configuration_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--===Actions ===-->
</div>