<div class="order-wrap">
    <div class="row order-box-list order-sc-text">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="btn-wr after btn-wr-top data-table-top-left">
                    <div>
                        <div style="padding: 4px 10px 0 0; font-size: large;">
                            <a class="btn btn-back" href="{$app->urlManager->createUrl('error-log-viewer')}?by={$back}">{$smarty.const.IMAGE_BACK}</a>
                            <b>{$filename}</b>
                            <button id = "refresh" class="btn btn-redo" style="float: right; margin-left: 50px;">{$smarty.const.TEXT_REFRESH}</button>
                        </div>
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

        function deleteLog()
        {
            var file = "{$filename}"

            bootbox.dialog({
                message: "{$smarty.const.EXT_ELV_DELETE_INTRO}".replace('%s', file),
                title: "{$smarty.const.EXT_ELV_DELETE_TITLE}",
                buttons: {
                    confirm: {
                        label: "{$smarty.const.TEXT_YES}",
                        className: "btn-delete",
                        callback: function() {
                            $.post("{$app->urlManager->createUrl('error-log-viewer/logs-delete')}", { 'logs' : file }, function(data, status){
                                if(status == "success")
                                {
                                    window.location.href = "{$app->urlManager->createUrl('error-log-viewer')}?by={$back}";
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

        function viewAsText()
        {
            window.open("{$app->urlManager->createUrl('error-log-viewer/view-as-text')}?file={$mask}", "_blank");
        }

        function elv_show_modal()
        {
            $("#btn_modal").trigger('click');
        }

        $(document).ready(function () {
            jQuery.fn.dataTable.render.ellipsis = function ( cutoff, wordbreak, escapeHtml ) {
                var esc = function ( t ) {
                    return ('' + t)
                        .replace( /&/g, '&amp;' )
                        .replace( /</g, '&lt;' )
                        .replace( />/g, '&gt;' )
                        .replace( /"/g, '&quot;' );
                };
                return function ( d, type, row ) {
                    // Order, search and type get the original data
                    if ( type !== 'display' ) {
                        return d;
                    }
                    if ( typeof d !== 'number' && typeof d !== 'string' ) {
                        if ( escapeHtml ) {
                            return esc( d );
                        }
                        return d;
                    }
                    d = d.toString(); // cast numbers
                    if ( d.length <= cutoff ) {
                        if ( escapeHtml ) {
                            return esc( d );
                        }
                        return d;
                    }
                    var shortened = d.substr(0, cutoff-1);
                    // Find the last white space character in the string
                    if ( wordbreak ) {
                        shortened = shortened.replace(/\s([^\s]*)$/, '');
                    }
                    // Protect against uncontrolled HTML input
                    if ( escapeHtml ) {
                        shortened = esc( shortened );
                    }
                    return shortened+'&#8230;';
                };
            };
            var table = $('#advancedList').DataTable({
                processing: true,
                serverSide: false,
                autoWidth: true,
                ajax: {
                    url: 'error-log-viewer/advanced-list?file={$mask}',
                    type: 'GET',
                },
                columnDefs: [{
                    targets: 5,
                    render: $.fn.dataTable.render.ellipsis(75)
                }],
                order: [[1, 'desc']],
            });

            $.fn.dataTable.ext.errMode = 'none';
            table.columns(0).visible(false);

            $('#refresh').on('click', function () {
                table.ajax.reload();
            });

            $('#advancedList')
                .on( 'error.dt', function ( e, settings, techNote, message ) {
                    window.location.href = "{$app->urlManager->createUrl('error-log-viewer')}";
                } )
                .DataTable();


            $('#advancedList tbody').on('click', 'tr td', function (){
                $(this).parents('tr').each(function (){
                    $('#advancedList tbody tr').removeClass('selected')
                    $('#order_management_data .scroll_col').text(' ');
                    $("#order_management").hide();
                });
                $(this).parents('tr').addClass('selected')
                var id = $('#advancedList tbody tr.selected').find('input.cell_identify').val();

                $.post("{$app->urlManager->createUrl('error-log-viewer/advanced-actions')}", { 'id' : id , 'file' : '{$mask}'}, function(data, status){
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

                $.post("{$app->urlManager->createUrl('error-log-viewer/advanced-actions')}", { 'id' : id , 'file' : '{$mask}'}, function(data, status){
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