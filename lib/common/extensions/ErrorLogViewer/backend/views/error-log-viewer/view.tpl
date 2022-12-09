<div class="order-wrap">
    <div class="row order-box-list order-sc-text">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="btn-wr after btn-wr-top disable-btn data-table-top-left">
                    <div>
                        <div style="padding: 4px 10px 0 0; font-size: large;"><b>{$file}</b></div>
                    </div>
                </div>
                <table class="table table-striped table-selectable table-hover table-responsive table-bordered tabl-res double-grid" id="advancedList">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->logTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 0}{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    <!-- /Orders List -->

    <script type="text/javascript">

        function viewAsText()
        {
            window.open("{$app->urlManager->createUrl('error-log-viewer/view-as-text')}?file={$file}", "_blank");
        }

        function elv_show_modal()
        {
            $("#btn_modal").trigger('click');
        }

        $(document).ready(function () {
            var table = $('#advancedList').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: true,
                ajax: {
                    url: 'error-log-viewer/advanced-list?file={$file}',
                    type: 'GET',
                },
                order: [[1, 'desc']],
                columnDefs: [
                    
                ],
            });
            table.columns(0).visible(false);

            $('#advancedList tbody').on('click', 'tr td', function (){
                $(this).parents('tr').each(function (){
                    $('#advancedList tbody tr').removeClass('selected')
                    $('#order_management_data .scroll_col').text(' ');
                    $("#order_management").hide();
                });
                $(this).parents('tr').addClass('selected')
                var id = $('#advancedList tbody tr.selected').find('input.cell_identify').val();

                $.post("{$app->urlManager->createUrl('error-log-viewer/advanced-actions')}", { 'id' : id , 'file' : '{$file}'}, function(data, status){
                    if (status == "success") {
                        $('#order_management_data .scroll_col').html(data);
                        $("#order_management").show();
                    } else {
                        alert("{$smarty.const.EXT_ELV_ERR_REQUEST}");
                    }
                },"html");
            });
            $('#advancedList tbody').on('dblclick', 'tr td', function (){
                var id = $('#advancedList tbody tr.selected').find('input.cell_identify').val();

                $.post("{$app->urlManager->createUrl('error-log-viewer/advanced-actions')}", { 'id' : id , 'file' : '{$file}'}, function(data, status){
                    if (status == "success") {
                        $('#order_management_data .scroll_col').html(data);
                        $("#order_management").show();
                        setTimeout(elv_show_modal, 300)
                    } else {
                        alert("{$smarty.const.EXT_ELV_ERR_REQUEST}");
                    }
                },"html");

            });

        });
    </script>
    <!--===Actions ===-->
    <div class="row right_column" id="order_management">
        <div class="widget box">
            <div class="widget-content fields_style dis_module" id="order_management_data">
                <div class="scroll_col">
                </div>
            </div>
        </div>
    </div>
</div>