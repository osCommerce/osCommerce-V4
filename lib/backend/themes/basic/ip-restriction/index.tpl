{\backend\assets\BDPAsset::register($this)|void}
{use class="\yii\helpers\Html"}
{use class="\yii\helpers\Url"}

<div class="order-wrap">
<input type="hidden" name="row" id="row_id" value="{$app->controller->view->row_id}" />
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid" checkable_list="0" data_ajax="{Url::to('ip-restriction/list')}">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->ipTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>

    </div>
</div>
                <!--===Actions ===-->
                <div class="row right_column" id="catalog_management">
                    <div class="widget box">
                        <div class="widget-content" id="catalog_management_data">
                            <div class="scroll_col"></div>
                        </div>
                    </div>
                </div>
				<!--===Actions ===-->
</div>
<script>
var kTable;

function confirmDelete(id){
    var str = kTable.fnGetNodes($('#row_id').val());
    bootbox.dialog({
        message: "{$smarty.const.IMAGE_TRASH} <span class=\"lowercase\">" + $(str).text() + " IP?</span>",
        title: "{$smarty.const.IMAGE_TRASH} <span class=\"lowercase\">IP</span>",
        buttons: {
            success: {
                    label: "Yes",
                    className: "btn-delete",
                    callback: function() {
                        $.post("ip-restriction/delete", { 'forbidden_id' : id }, function(data, status){
                            if (status == "success") {
                                kTable.fnDraw(false);
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

function ipEdit(id){
    $.post('ip-restriction/edit', {
        'forbidden_id' : id,
        },
        function(data, status){
            if (status == 'success'){
                $('#catalog_management_data').html(data);
                $("#catalog_management").show();
            }
        },"html");
}

function onClickEvent(obj, table) {
    $('#row_id').val(table.find(obj).index());
    var event_id = $(obj).find('input.cell_identify').val();
    kTable = table;
    $.post("ip-restriction/preview", { 'forbidden_id' : event_id, 'row_id' : $('#row_id').val() }, function(data, status){
            if (status == "success") {
                $('#catalog_management_data').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
}
function onUnclickEvent(obj, table){
  return false;
}

</script>