<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="order-wrap">
    <input type="hidden" id="row_id">
    <!--=== Page Content ===-->
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="alert fade in" style="display:none;">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"></span>
                </div>
                {if {$messages|@count} > 0}
                    {foreach $messages as $message}
                        <div class="alert fade in {$message['messageType']}">
                            <i data-dismiss="alert" class="icon-remove close"></i>
                            <span id="message_plce">{$message['message']}</span>
                        </div>
                    {/foreach}
                {/if}
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0,1" data_ajax="orders_products_status_manual/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->StatusTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript">
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

        function resetStatement() {
            $("#status_management").hide();
            switchOnCollapse('status_list_collapse');
            var table = $('.table').DataTable();
            table.draw(false);
            $(window).scrollTop(0);
            return false;
        }

        function onClickEvent(obj, table) {
            $("#status_management").hide();
            $('#status_management_data .scroll_col').html('');
            $('#row_id').val(table.find(obj).index());
            var orders_products_status_manual_id = $(obj).find('input.cell_identify').val();
            $.post("orders_products_status_manual/statusactions", { 'orders_products_status_manual_id' : orders_products_status_manual_id }, function(data, status){
                if (status == "success") {
                    $('#status_management_data .scroll_col').html(data);
                    $("#status_management").show();
                    window.history.replaceState('', '', 'orders_products_status_manual?orders_products_status_manual_id=' + orders_products_status_manual_id);
                } else {
                    alert("Request error.");
                }
            },"html");
        }

        function onUnclickEvent(obj, table) {
            $("#status_management").hide();
            $(table).DataTable().draw(false);
        }

        function statusEdit(id) {
            window.location.href = "orders_products_status_manual/edit?orders_products_status_manual_id=" + id;
            return false;
        }

        function statusSave(id) {
            $.post("orders_products_status_manual/save?orders_products_status_manual_id="+id, $('form[name=status]').serialize(), function(data, status){
                if (status == "success") {
                    $('.alert #message_plce').html('');
                    $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                    resetStatement();
                    switchOffCollapse('status_list_collapse');
                } else {
                    alert("Request error.");
                }
            },"json");
            return false;
        }

        function statusDelete(id) {
            if (confirm('Do you confirm?')){
                $.post("orders_products_status_manual/delete", { 'orders_products_status_manual_id' : id}, function(data, status){
                    if (status == "success") {
                        if (data == 'reset') {
                            resetStatement();
                        } else{
                            $('#status_management_data .scroll_col').html(data);
                            $("#status_management").show();
                        }
                        switchOnCollapse('status_list_collapse');
                    } else {
                        alert("Request error.");
                    }
                },"html");
                return false;
            }
        }
    </script>
    <!--===Actions ===-->
    <div class="row right_column" id="status_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="status_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
    <!--===Actions ===-->
    <!-- /Page Content -->
</div>

<script type="text/javascript">
    $(function() {
        $('.table').on('dblclick', 'tr', function() {
            var orders_products_status_manual_id = $('.cell_identify', this).val();
            window.location = 'orders_products_status_manual/edit?orders_products_status_manual_id=' + orders_products_status_manual_id;
        });
        var key = true;
        $(document).ajaxSuccess(function(){
            var url = new URLSearchParams(window.location.search);
            var orders_products_status_manual_id = url.get('orders_products_status_manual_id');
            if (orders_products_status_manual_id) {
                var td = $('input[value="' + orders_products_status_manual_id + '"]').closest('td');
                if (td.length && key) {
                    key = false;
                    $('input[value="' + orders_products_status_manual_id + '"]').closest('td').trigger('click');
                }
            }
        });
    });
</script>