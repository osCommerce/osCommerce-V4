<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<div class="order-wrap">
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable double-grid js-table-sortable" checkable_list="" data_ajax="orders-comment-template/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->listingTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>

                </table>
            </div>

        </div>
    </div>
    <!--===Actions ===-->
    <div class="row right_column" id="item_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content fields_style" id="management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
    <!--===Actions ===-->
</div>
<script type="text/javascript">

    function resetStatement(item_id) {
        $("#order_management").hide();
        var table = $('.table').DataTable();
        table.draw(false);
        $(window).scrollTop(0);
        return false;
    }

    function switchStatement(id, status) {
        $.post("{Yii::$app->urlManager->createUrl('orders-comment-template/switch-status')}", { 'id' : id, 'status' : status }, function(data, status){
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        },"json");
    }

    function onClickEvent(obj, table) {
        $("#item_management").hide();

        $('#management_data .scroll_col').html('');
        var item_id = $(obj).find('input.cell_identify').val();
        $(".check_on_off").bootstrapSwitch({
            onSwitchChange: function (element, arguments) {
                switchStatement(element.target.value, arguments);
                return true;
            },
            onText: "{$smarty.const.SW_ON|escape:'javascript'}",
            offText: "{$smarty.const.SW_OFF|escape:'javascript'}",
            handleWidth: '20px',
            labelWidth: '24px'
        });

        $.post("{$app->urlManager->createUrl('orders-comment-template/row-actions')}", { 'id' : item_id }, function(data, status) {
            if (status == "success") {
                $('#management_data .scroll_col').html(data);
                $("#item_management").show();
            } else {
                alert("Request error.");
            }
        },"html");

    }

    function onUnclickEvent(obj, table) {
        $("#item_management").hide();
        var event_id = $(obj).find('input.cell_identify').val();
        var type_code = $(obj).find('input.cell_type').val();
        $(table).DataTable().draw(false);
    }

    function itemEdit(id){
        $("#item_management").hide();
        $.get("orders-comment-template/edit", { 'orders_status_id' : id }, function(data, status){
            if (status == "success") {
                $('#management_data .scroll_col').html(data);
                $("#item_management").show();
                switchOffCollapse('status_list_collapse');
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }

    function itemDelete(id, name){
        bootbox.confirm({
            title: '{$smarty.const.TEXT_DELETE_HEAD_CONFIRM|escape:'javascript'}',
            message: '{$smarty.const.TEXT_DELETE_CONFIRM|escape:'javascript'}'.replace(/%s/g, name),
            buttons: {
                cancel: {
                    label: '{$smarty.const.IMAGE_CANCEL|escape:'javascript'}'
                },
                confirm: {
                    label: '{$smarty.const.IMAGE_CONFIRM|escape:'javascript'}'
                }
            },
            callback: function (result) {
                if (!result) return;
                $.post("orders-comment-template/delete", { 'id' : id }, function(data, status){
                    if (status == "success") {
                        if (data == 'reset') {
                            resetStatement();
                        } else{
                            $('#management_data .scroll_col').html(data);
                            $("#item_management").show();
                        }
                        switchOnCollapse('status_list_collapse');
                    } else {
                        alert("Request error.");
                    }
                },"html");
            }
        });
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

                $.post("{Yii::$app->urlManager->createUrl('orders-comment-template/sort-order')}", post_data, function(data, status){
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