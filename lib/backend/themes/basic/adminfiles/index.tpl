<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
</div>
<!-- /Page Header -->
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
</form>
<!--=== Listing ===-->
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="access_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable"
                       checkable_list="0" data_ajax="adminfiles/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->accessTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
    </div>
</div>
<!--=== /Listing ===-->
<!--===  Management ===-->
<div class="row right_column" id="access_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="access_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== /Management ===-->
</div>
<script type="text/javascript">
function assignHandlers(access_levels_id) {
    $("#admin_management").hide();
    $.post("extensions?module=Handlers&action=adminActionAssignHandlers", { 'access_levels_id' : access_levels_id }, function(data, status){
        if (status == "success") {
            $('#access_management_data').html(data);
            $("#access_management").show();
            switchOffCollapse('access_list_collapse');
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
function submitHandlersForm() {
    $("#admin_management").hide();
    $.post("extensions?module=Handlers&action=adminActionSubmitHandlers", $('#admin_edit').serialize(), function(data, status){
        if (status == "success") {
            $('#admin_management_data .scroll_col').html(data);
            $("#admin_management").show();
            resetStatement();
        } else {
            alert("Request error.");
        }
    },"html");
    return false;
}
    
    function resetFilter() {
        $("#row_id").val(0);
        resetStatement();
        return false;  
    }

    function applyFilter() {
        $("#row_id").val(0);
        resetStatement();
        return false;    
    }

    function preEditItem( item_id ) {
        $.post("{Yii::$app->urlManager->createUrl('adminfiles/preview')}", { 'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#access_management_data').html(data);
                $("#access_management").show();
                switchOnCollapse('access_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        // $("html, body").animate({ scrollTop: $(document).height() }, "slow");

        return false;
    }

    function accessDelete(item_id){
	if (confirm('{$smarty.const.TEXT_DELETE}')) {
		$.post("{Yii::$app->urlManager->createUrl('adminfiles/delete')}", { 'item_id': item_id }, function(data, status){
			if (status == "success") {
                            resetStatement();
			} else {
                            alert("Request error.");
			}
		},"html");
	}
        return false;
    }
    
    function confirmAclCopy(item_id) {
        $.post("{Yii::$app->urlManager->createUrl('adminfiles/confirm-acl-copy')}", { 'item_id': item_id }, function(data, status) {
            if (status == "success") {
                $('#access_management_data').html(data);
                $("#access_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }
    
    function copyAcl() {
        $.post("{Yii::$app->urlManager->createUrl('adminfiles/acl-copy')}", $('#acl_copy').serialize(), function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }

    function confirmAclDublicate(item_id) {
        $.post("{Yii::$app->urlManager->createUrl('adminfiles/confirm-acl-dublicate')}", { 'item_id': item_id }, function(data, status) {
            if (status == "success") {
                $('#access_management_data').html(data);
                $("#access_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }
    
    function dublicateAcl() {
        $.post("{Yii::$app->urlManager->createUrl('adminfiles/acl-dublicate')}", $('#acl_dublicate').serialize(), function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }

    function setFilterState() {
        orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({ }, '', url);
    }
    
    function switchOffCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-down')) {
            $("#" + id).click();
        }
    }

    function switchOnCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-up')) {
            $("#" + id).click();
        }
    }

    function resetStatement() {
        $("#access_management").hide();

        switchOnCollapse('access_list_box_collapse');
        switchOffCollapse('access_management_collapse');

        $('access_management_data').html('');
        $('#access_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

         $(window).scrollTop(0);

        return false;
    }
   
    function onClickEvent(obj, table) {
        var dtable = $(table).DataTable();
        var id = dtable.row('.selected').index();
        $("#row_id").val(id);
        setFilterState();

        var event_id = $(obj).find('input.cell_identify').val();

        preEditItem(  event_id );
    }

    function onUnclickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();
    }

  $(document).ready(function(){
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
          //post_data.push({ name:'platform_id', value: $('#page_platform_id').val() });

          $.post("{Yii::$app->urlManager->createUrl('adminfiles/sort-order')}", post_data, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
          },"html");
        },
        handle: ".handle"
      }).disableSelection();
    /*$('.table').on('xhr.dt', function ( e, settings, json, xhr ) {
     console.log(json);
     } );*/
  });
</script>

