{\backend\assets\PlatformAsset::register($this)|void}
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<div class="tabbable tabbable-custom" style="margin-bottom: 0;">
    <ul class="nav nav-tabs">
        <li {if $app->controller->view->filters->pane == 'physical' }class="active"{/if}><a href="admin/platforms?pane=physical" data-toggle="tab" data-type="physical">{$smarty.const.TEXT_PHYSICAL}</a></li>
        <li {if $app->controller->view->filters->pane == 'virtual' }class="active"{/if}><a href="admin/platforms?pane=virtual" data-toggle="tab" data-type="virtual">{$smarty.const.TEXT_VIRTUAL}</a></li>
        <li {if $app->controller->view->filters->pane == 'marketplace' }class="active"{/if}><a href="admin/platforms?pane=marketplace" data-toggle="tab" data-type="marketplace">{$smarty.const.TEXT_MARKETPLACES}</a></li>
    </ul>
</div>

<div class="order-wrap">
    <div class="row order-box-list">
            <div class="col-md-12">
                    <div class="widget-content">
                        <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
                            <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
                            <input type="hidden" name="pane" id="pane" value="{$app->controller->view->filters->pane}" />    
                        </form>
                        <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable table-platforms"
                               checkable_list="0,1" data_ajax="{Yii::$app->urlManager->createUrl(['platforms/list'])}" >
                            <thead>
                            <tr>
                                {foreach $app->controller->view->platformsTable as $tableItem}
                                    <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                {/foreach}
                            </tr>
                            </thead>
                        </table>
                    </div>
            </div>
        </div>
         <div class="row" id="order_management" style="display: none;">
            <div class="widget box">
                <div class="widget-content fields_style " id="platforms_management_data">
                    <div class="scroll_col"></div>
                </div>
            </div>
        </div>
       
</div>

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

function getRow(id){
    return '.cell_identify[value='+id+']';
}

function getManagement(id){
    return $(getRow(id)).parents('.tab-pane').find('.platforms_management_data .scroll_col');
}

function preEditItem( item_id ) {
    if (item_id){
        $.post("{Yii::$app->urlManager->createUrl('platforms/item-preedit')}", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#platforms_management_data .scroll_col').html(data);
              $('.js-open-tree-popup').popUp();
            } else {
                alert("Request error.");
            }
        }, "html");
    }
    return false;
}

function deleteItemConfirm( item_id) {
    $.post("{Yii::$app->urlManager->createUrl('platforms/confirmitemdelete')}", {  'item_id': item_id }, function (data, status) {
        if (status == "success") {
            $('#platforms_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function deleteItem() {
    $.post("{Yii::$app->urlManager->createUrl('platforms/itemdelete')}", $('#item_delete').serialize(), function (data, status) {
        if (status == "success") {
            $('#platforms_management_data .scroll_col').html("");
            resetStatement();
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}

function copyItemConfirm( item_id) {
    $.post("{Yii::$app->urlManager->createUrl('platforms/confirm-item-copy')}", {  'item_id': item_id }, function (data, status) {
        if (status == "success") {
            $('#platforms_management_data .scroll_col').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");
    return false;
}

function copyItem() {
    $.post("{Yii::$app->urlManager->createUrl('platforms/item-copy')}", $('#item_copy').serialize(), function (data, status) {
        if (status == "success") {
            $('#platforms_management_data .scroll_col').html("");
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
    $('#platforms_management_data .scroll_col').html('');

    var table = $('.table').DataTable();
    table.draw(false);

    return false;
}

function switchStatement(id, status, $type) {    
    $.post("{Yii::$app->urlManager->createUrl('platforms/switch-status')}", { 'id' : id, 'status' : status }, function(data, status){
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
    
    setFilterState();

    var event_id = $(obj).find('input.cell_identify').val();
    preEditItem(  event_id );
}

function onUnclickEvent(obj, table) {
    var event_id = $(obj).find('input.cell_identify').val();
}

$(document).ready(function(){
    
    $('a[data-toggle="tab"]').on('click', function(){
        $('#pane').val($(this).data('type'));
        var activate = $(this).data('type');
        var $tabs = $('.nav.nav-tabs');
        $tabs.find('li.active').removeClass('active');
        $tabs.find('li').each(function() {
            var $li = $(this);
            if ($li.find('a[data-type="'+activate+'"]').length>0) {
                $li.addClass('active');
            }
        });

        applyFilter();
        return false;
    });

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
            post_data.push({ name:'type', value:$('input[name=pane]').val() });

            $.post("{Yii::$app->urlManager->createUrl('platforms/sort-order')}", post_data, function(data, status){
                if (status == "success") {
                    resetStatement();
                } else {
                    alert("Request error.");
                }
            },"html");
        },
        handle: ".handle"
    }).disableSelection();
  
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

</div>

<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>
