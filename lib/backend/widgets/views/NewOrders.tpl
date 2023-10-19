<div class="widget box">
    <div class="widget-header">
        <h4><i class="icon-file-text"></i> {$smarty.const.TEXT_NEW_ORDERS}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content dashboard-scroll">
        {if defined('SUPERADMIN_ENABLED') && SUPERADMIN_ENABLED == True}
        <table class="table table-striped table-bordered table-hover table-responsive datatable-dashboard table-ordering no-footer" order_list="3" order_by="desc" data-ajax="index/order">
            <thead>
            <tr>
                <th class="sorting">{$smarty.const.BOX_HEADING_DEPARTMENTS}</th>
                <th class="sorting">{$smarty.const.TEXT_CLIENTS}</th>
                <th class="sorting">{$smarty.const.BOX_CUSTOMERS_ORDERS}</th>
                <th class="sorting">{$smarty.const.TEXT_AMOUNT_FILTER}</th>
            </tr>
            </thead>
        </table>
        {else}
        <table class="table table-striped table-bordered table-hover table-responsive datatable-dashboard table-ordering no-footer" order_list="3" order_by="desc" data-ajax="index/order">
            <thead>
                <tr>
                    <th class="sorting">{$smarty.const.TEXT_CUSTOMERS}</th>
                    <th class="sorting">{$smarty.const.TEXT_ORDER_TOTAL}</th>
                    <th class="sorting">{$smarty.const.TEXT_ORDER_ID}</th>
                    <th class="sorting">{$smarty.const.ENTRY_POST_CODE}</th>
                </tr>
            </thead>

        </table>
        {/if}
        <div class="index_buttons">
            <a href="{$app->urlManager->createUrl('orders?interval=1')}" class="btn-primary btn">{$smarty.const.TEXT_HANDLE_ORDERS}</a>
            <a href="#" class="btn-refresh"><i class="icon-refresh"></i></a>
        </div>
    </div>
{*
    <div class="divider"></div>
*}
</div>
<script type="text/javascript">
function onClickEvent(obj, table) {
    var orders_id = $(obj).find('input.cell_identify').val();
    window.location.href = "{$app->urlManager->createUrl('orders/process-order?orders_id="+orders_id+"')}";
}
$(document).ready(function() {
    var tableTr = $('.datatable-dashboard').DataTable({
        "ajax":$('.datatable-dashboard').data('ajax'),
        "searching":false,
        "paging":false,
        "info":false,
        fnDrawCallback:function(){
            $('.table tbody tr:eq(0)').click(function(e){
            e.preventDefault();
            })
        }
    });
    $(this).find('tbody').on( 'click', 'tr', function () {
        onClickEvent(this, tableTr);
        } );
    $('.btn-refresh').on('click', function(e) {
        e.preventDefault();
        var table = $('.datatable-dashboard').DataTable();
        table.ajax.reload();
    });
});
</script>