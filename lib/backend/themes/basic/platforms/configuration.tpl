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
<input type="hidden" id="row_id">
<div class="row order-box-list" id="configuration_info">
    <div class="col-md-12">
            <div class="widget-content" id="configuration_info_data">
                <div class="ord_status_filter_row">
                    <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                        {$app->controller->view->filterGroups}
                        <input type="hidden" name="row" id="row_id" value="{$app->controller->view->row}" />
                        <input type="hidden" name="platform_id" value="{$app->controller->view->platform_id}" />    
                    </form>
                </div>                
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid table-configuration" checkable_list="0,1" data_ajax="{Url::toRoute(['platforms/getgroupcontent'])}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->adminTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
                <p class="btn-wr">
                    <a class="btn btn-primary" href="javascript:void(0)" onclick="return backStatement();">{$smarty.const.IMAGE_BACK}</a>
                </p>
            </div>

    </div>
</div>
<!--===Group Params table===-->

<script type="text/javascript">

function backStatement() {
    window.history.back();
    return false;
}
    function preEditItem( item_id,platform_id ) {
        $.post("{Url::toRoute(['platforms/preedit'])}", {
            'param_id': item_id,
            'platform_id': platform_id,
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

    function editItem( item_id,platform_id ){
        $.post("{Url::toRoute(['platforms/getparam'])}", {
            'param_id': item_id,
            'platform_id': platform_id,
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
    
    function deleteTrashedItem(item_id){
      if (confirm("{$smarty.const.TEXT_CONFIRM_DELETING}")){
        $.post("{Url::toRoute(['platforms/delete-param'])}", {
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
        var group_id = $( "select[name='group_id']" ).val();
        var platform_id = $( "input[name='platform_id']" ).val();
        var param_id = $(obj).find('input.cell_identify').val();
        $('#row_id').val(table.find(obj).index());
        preEditItem(param_id,platform_id);
    }

    function onUnclickEvent(obj) {
        $("#configuration_management").hide();
    }

    function saveParam(){
        $.post("{Url::toRoute(['platforms/saveparam'])}", $('#save_param_form').serialize(), function(data, status){
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
        if (typeof table == 'undefined'){
          table = $('.table').DataTable();
        }        
        $.each($(':checkbox.sdef'), function(i, e){
          if ($(e).prop('checked')){
            table.column($(e).data('column')).visible(true);
          } else {
            table.column($(e).data('column')).visible(false);
          }
        })
        orig = $('#filterForm').serialize();
        var url =  window.location.protocol + '//'+window.location.hostname + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({}, '', url);
    }

    function resetStatement() {
        setFilterState();
        table = $('.table').DataTable();
        table.draw( true );
        return false;
    }
    
    function applyFilter() {
        resetStatement();
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