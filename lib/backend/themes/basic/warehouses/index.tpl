<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
    <input type="hidden" name="platform_id" id="page_platform_id" value="{$selected_platform_id}" />
</form>

{if $isMultiPlatforms}
    <div class="tabbable tabbable-custom" style="margin-bottom: 0;">
        <ul class="nav nav-tabs">
            {foreach $platforms as $platform}
            <li class="{if $platform['id']==$selected_platform_id} active {/if}"><a class="js_link_platform_modules_select" href="{$platform['link']}" data-platform_id="{$platform['id']}"><span>{$platform['text']}</span></a></li>
            {/foreach}
        </ul>
    </div>
{/if}

<div class="order-wrap">
<!--===warehouses list===-->
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="groups_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable table-warehouses"
                       checkable_list="0,1" data_ajax="{Yii::$app->urlManager->createUrl('warehouses/list')}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->groupsTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
                <!--<p class="btn-toolbar">
                    <input type="button" class="btn btn-primary" value="Insert"
                           onClick="return editItem( 0)">
                </p>-->
            </div>
    </div>
</div>
<!--===/warehouses list===-->

<script type="text/javascript">
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
    $.post("{Yii::$app->urlManager->createUrl('warehouses/item-preedit')}", {
        'item_id': item_id
    }, function (data, status) {
        if (status == "success") {
            $('#warehouses_management_data .scroll_col').html(data);
          $('.js-open-tree-popup').popUp();
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function deleteItemConfirm( item_id) {
    $.post("{Yii::$app->urlManager->createUrl('warehouses/confirmitemdelete')}", {  'item_id': item_id }, function (data, status) {
        if (status == "success") {
            $('#warehouses_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function deleteItem() {
    $.post("{Yii::$app->urlManager->createUrl('warehouses/itemdelete')}", $('#item_delete').serialize(), function (data, status) {
        if (status == "success") {
            $('#warehouses_management_data .scroll_col').html("");
            resetStatement();
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}

function copyItemConfirm( item_id) {
    $.post("{Yii::$app->urlManager->createUrl('warehouses/confirm-item-copy')}", {  'item_id': item_id }, function (data, status) {
        if (status == "success") {
            $('#warehouses_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function copyItem() {
    $.post("{Yii::$app->urlManager->createUrl('warehouses/item-copy')}", $('#item_copy').serialize(), function (data, status) {
        if (status == "success") {
            $('#warehouses_management_data .scroll_col').html("");
            resetStatement();
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}

function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
}

function resetStatement() {
    $('#warehouses_management_data .scroll_col').html('');

    var table = $('.table').DataTable();
    table.draw(false);

    return false;
}

function switchStatement(id, status) {
    $.post("{Yii::$app->urlManager->createUrl('warehouses/switch-status')}", { 'id' : id, 'status' : status, 'platform_id': $('#page_platform_id').val() }, function(data, status){
      if (status == "success") {
        resetStatement();
      } else {
        alert("Request error.");
      }
    },"html");
}

function onClickEvent(obj, table) {
    var dtable = $(table).DataTable();
    var id = dtable.row('.selected').index();
    $("#row_id").val(id);
    setFilterState();
    
    $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                switchStatement(element.target.value, arguments);
                return true;  
            },
			onText: "{$smarty.const.SW_ON}",
			offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );    

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
            $('.ui-sortable > tr').each(function() {
                if ( this.id ) return;
                var cell_ident = $(this).find('.cell_identify');
                var cell_type = $(this).find('.cell_type');
                if ( cell_ident.length>0 && cell_type.length>0 ) {
                    this.id = cell_type.val()+'_'+cell_ident.val();
                }
            });
            var post_data = [];
            $('.ui-sortable > tr').each(function() {
                var spl = this.id.indexOf('_');
                if ( spl===-1 ) return;
                post_data.push({ name:this.id.substring(0, spl)+'[]', value:this.id.substring(spl+1) });
            });
            var $dropped = $(ui.item);
            post_data.push({ name:'sort_'+$dropped.find('.cell_type').val(), value:$dropped.find('.cell_identify').val() });
            post_data.push({ name:'platform_id', value: $('#page_platform_id').val() });

            $.post("{Yii::$app->urlManager->createUrl('warehouses/sort-order')}", post_data, function(data, status){
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

$(document).ready(function() {
    $(window).resize(function () {
        setTimeout(function () {
            var height_box = $('.order-wrap').height();
            $('#order_management .widget').css('min-height', height_box);
        }, 800);
    })
    $(window).resize();     
})
</script>

<!--===  warehouses management ===-->
<div class="row" id="order_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="warehouses_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== warehouses management ===-->
</div>

<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>
