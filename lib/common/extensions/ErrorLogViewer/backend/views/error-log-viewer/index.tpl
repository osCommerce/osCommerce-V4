<div class="order-wrap">
    <div class="row order-box-list order-sc-text">
        <div class="col-md-12">
            <div class="widget-content">
                {if Yii::$app->session->hasFlash('ELV')}
                    <div class="alert alert-warning alert-dismissible show" role="alert">
                        {Yii::$app->session->getFlash('ELV')}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"><strong>&times;</strong></span>
                        </button>
                    </div>
                {/if}
                <div class="btn-wr after btn-wr-top data-table-top-left">
                    <div>
                        <div>
                            <button id = "refresh" class="btn btn-redo">{$smarty.const.TEXT_REFRESH}</button>
                        </div>
                        <div class="p-l-1 switchable disable-btn">
                            <a href="javascript:void(0)" onclick="deleteSelectedLogs();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                        </div>
                        <div>
                            <div style="padding: 4px 10px 0 0;">
                                <b>{$smarty.const.TEXT_SEARCH_BY}</b>
                            </div>
                            <div>
                                <select id="filterForm" class="form-control" name="by" onchange="return applyFilter();" style="border-radius: 10px;">
                                    {foreach $app->controller->view->filters->by as $Item}
                                        <option {$Item['selected']} value="{$Item['value']}">{$Item['name']}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered tabl-res double-grid" id="logList" checkable_list="">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->logTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 0}{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
                <div class="btn-wr after switchable disable-btn">
                    <div>
                        <a href="javascript:void(0)" onclick="deleteSelectedLogs();" class="btn btn-del">{$smarty.const.TEXT_DELETE_SELECTED}</a>
                    </div>
                    <div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Orders List -->

    <script type="text/javascript">

        function viewAsText()
        {
            var log = $('#file').val();
            window.open("{$app->urlManager->createUrl('error-log-viewer/view-as-text')}?file="+log, "_blank");
        }

        function deleteAllLog() {
            bootbox.dialog({
                message: "{$smarty.const.EXT_ELV_DELETE_ALL_INTRO}",
                title: "{$smarty.const.EXT_ELV_DELETE_ALL_TITLE}",
                buttons: {
                    confirm: {
                        label: "{$smarty.const.TEXT_YES}",
                        className: "btn-delete",
                        callback: function() {
                            $.post("{$app->urlManager->createUrl('error-log-viewer/delete-all')}", function(data, status){
                                if(status == "success")
                                {
                                    applyFilter()
                                    location.reload();
                                }else{
                                    alert("{$smarty.const.EXT_ELV_ERR_REQUEST}");
                                }
                            },"html");
                        }
                    },
                    cancel: {
                        label: "{$smarty.const.IMAGE_CANCEL}",
                        className: "btn-cancel",
                        callback: function() {
                        }
                    }
                }
            });
        }
        function deleteLog()
        {
            var file = $('tbody tr.selected').find('input.cell_identify').val();

            bootbox.dialog({
                message: "{$smarty.const.EXT_ELV_DELETE_INTRO}".replace('%s', file),
                title: "{$smarty.const.EXT_ELV_DELETE_TITLE}",
                buttons: {
                    confirm: {
                        label: "{$smarty.const.TEXT_YES}",
                        className: "btn-delete",
                        callback: function() {
                            $.post("{$app->urlManager->createUrl('error-log-viewer/logs-delete')}", { 'logs' : file.replaceAll('.', '|') }, function(data, status){
                                if(status == "success")
                                {
                                    applyFilter()
                                    location.reload();
                                }else{
                                    alert("{$smarty.const.EXT_ELV_ERR_REQUEST}");
                                }
                            },"html");
                        }
                    },
                    cancel: {
                        label: "{$smarty.const.IMAGE_CANCEL}",
                        className: "btn-cancel",
                        callback: function() {
                        }
                    }
                }
            });
        }

        function deleteSelectedLogs() {
            bootbox.dialog({
                message: "{$smarty.const.EXT_ELV_DELETE_SELECTED_INTRO}".replace('%s', getTableSelected("count")),
                title: "{$smarty.const.EXT_ELV_DELETE_SELECTED_TITLE}",
                buttons: {
                    confirm: {
                        label: "{$smarty.const.TEXT_YES}",
                        className: "btn-delete",
                        callback: function() {
                            $.post("{$app->urlManager->createUrl('error-log-viewer/logs-delete')}", { 'logs' : getTableSelected("ids")}, function(data, status){
                                if(status == "success")
                                {
                                    applyFilter()
                                    location.reload();
                                }else{
                                    alert("{$smarty.const.EXT_ELV_ERR_REQUEST}");
                                }
                            },"html");
                        }
                    },
                    cancel: {
                        label: "{$smarty.const.IMAGE_CANCEL}",
                        className: "btn-cancel",
                        callback: function() {
                        }
                    }
                }
            });

        }
        function getTableSelected(type) {
            var types = type
            var selected_messages_ids = [];
            var selected_messages_count = 0;
            $('input:checkbox:checked.checkbox').each(function(j, cb) {
                var aaa = $(cb).closest('td').find('.cell_identify').val();
                if (typeof(aaa) != 'undefined') {
                    selected_messages_ids[selected_messages_count] = aaa;
                    selected_messages_count++;
                }
            });
            if(types == "ids")
            {
                return selected_messages_ids;
            }else if(types == "count")
            {
                return selected_messages_count++;
            }

        }
        function applyFilter()
        {
            $('#order_management_data .scroll_col').text(" ");
            $("#order_management").hide();
            var table = $('#logList').DataTable();
            table.ajax.url('error-log-viewer/list?by='+$('#filterForm option:selected').text()).load();
            var checkbox = $('th.checkbox-column .checkbox')
            if(checkbox.is(':checked'))
            {
                checkbox.prop('checked', false);
            }
            setFilterState()
        }

        function setFilterState() {
            orig = $('#filterForm').serialize();
            var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
            window.history.replaceState({ }, '', url);
        }

        $(document).ready(function () {
            var table = $('#logList').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: true,
                ajax: {
                    url: 'error-log-viewer/list?by='+$('#filterForm option:selected').text(),
                    type: 'GET',
                },
                columnDefs: [
                    { 'orderData':[4], 'targets': [2] },
                    {
                        orderable: false,
                        targets: 0,
                    },
                    {
                        sType: "numeric",
                        targets: [ 4 ],
                        visible: false,
                        searchable: false,
                    },
                ],
            });
            table.columns(4).visible(false);
            $('#refresh').on('click', function () {
                table.ajax.reload();
            });

            $('body').on('click', 'th.checkbox-column .checkbox', function() {
                if($(this).is(':checked'))
                {
                    $('td .checkbox').prop('checked', true);
                    $('.switchable').removeClass('disable-btn');
                }else{
                    $('td .checkbox').prop('checked', false);
                    $('.switchable').addClass('disable-btn');
                }
            });
            $('#logList tbody').on('click', 'tr td', function (){
                $(this).parents('tr').each(function (){
                    $('tbody tr').removeClass('selected')
                });
                $(this).parents('tr').addClass('selected')
                var file = $('tbody tr.selected').find('input.cell_identify').val();

                $.post("{$app->urlManager->createUrl('error-log-viewer/actions')}", { 'log' : file }, function(data, status){
                    if (status == "success") {
                        $('#order_management_data .scroll_col').html(data);
                        $("#order_management").show();
                    } else {
                        alert("{$smarty.const.EXT_ELV_ERR_REQUEST}");
                    }
                },"html");
            });
            $('#logList tbody').on('click', 'tr td .checkbox', function (){
                if($(this).is(':checked'))
                {
                    $('.switchable').removeClass('disable-btn');
                }else{
                    $('.switchable').addClass('disable-btn');
                }
            });
            $('tbody').on('dblclick', 'tr td', function (){
                var file = $('#logList tbody tr.selected').find('input.cell_identify').val();
                window.location.href = "{$app->urlManager->createUrl('error-log-viewer/view')}?log="+file.replaceAll('.', '|');
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