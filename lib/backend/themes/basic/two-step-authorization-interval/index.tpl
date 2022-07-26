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
                    <span id="id_span_message"></span>
                </div>
                {if {$messages|@count} > 0}
                    {foreach $messages as $message}
                        <div class="alert fade in {$message['messageType']}">
                            <i data-dismiss="alert" class="icon-remove close"></i>
                            <span id="id_span_message">{$message['message']}</span>
                        </div>
                    {/foreach}
                {/if}
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0,1" data_ajax="two-step-authorization-interval/list">
                    <thead>
                        <tr>
                            {foreach $app->controller->view->tsaiTable as $tableItem}
                                <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                            {/foreach}
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function onClickEvent(obj, table) {
            $('#row_id').val(table.find(obj).index());
            $("#holder_tsai_view").hide();
            $('#holder_tsai_view_data .scroll_col').html('');
            var ale_id = $(obj).find('input.cell_identify').val();
            $.post("two-step-authorization-interval/view", { 'ale_id': ale_id }, function (data, status) {
                if (status == "success") {
                    $('#holder_tsai_view_data .scroll_col').html(data);
                    $("#holder_tsai_view").show();
                } else {
                    alert("Server is not responding!");
                }
            }, "html");
        }

        function tsaiReset() {
            $("#holder_tsai_view").hide();
            var table = $('.table').DataTable();
            table.draw(false);
            $(window).scrollTop(0);
            return false;
        }

        function tsaiEdit(ale_id) {
            $("#holder_tsai_view").hide();
            $.get("two-step-authorization-interval/edit", { 'ale_id': ale_id }, function (data, status) {
                if (status == "success") {
                    $('#holder_tsai_view_data .scroll_col').html(data);
                    $("#holder_tsai_view").show();
                } else {
                    alert("Server is not responding!");
                }
            }, "html");
            return false;
        }

        function tsaiSave(ale_id) {
            $.post("two-step-authorization-interval/save?ale_id=" + ale_id, $('form[name="tsaiEditForm"]').serialize(), function (data, status) {
                if (status == "success") {
                    $('.alert #id_span_message').html('');
                    $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#id_span_message').append(data['message']);
                    tsaiReset();
                } else {
                    alert("Server is not responding!");
                }
            }, "json");
            return false;
        }

        function tsaiDelete(ale_id) {
            if (confirm('Do you really wish to delete interval?')) {
                $.post("two-step-authorization-interval/delete", { 'ale_id': ale_id }, function (data, status) {
                    if (status == "success") {
                        tsaiReset();
                    } else {
                        alert("Server is not responding!");
                    }
                }, "html");
                return false;
            }
        }
    </script>
    <!--===Actions ===-->
    <div class="row right_column" id="holder_tsai_view">
        <div class="widget box">
            <div class="widget-content fields_style" id="holder_tsai_view_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
    <!--===Actions ===-->
    <!-- /Page Content -->
</div>
