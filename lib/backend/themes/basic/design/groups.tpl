{include 'menu.tpl'}
<div class="order-wrap">

    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="alert fade in" style="display:none;">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"></span>
                </div>

                <form id="filterForm" name="filterForm">
                    <div class="filters_btn">
                        <input type="hidden" name="row" id="row_id" value="3" />
                    </div>
                </form>

                <div class="table-holder">
                <table class="{if $isMultiPlatforms}tab_edt_page_mul{/if} table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable tab-pages double-grid table-information_manager" checkable_list="" data_ajax="design/groups-list">
                    <thead>
                    <tr>
                        <th>{$smarty.const.TABLE_TEXT_NAME}</th>
                        <th>{$smarty.const.ICON_FILE}</th>
                        <th>{$smarty.const.TEXT_PAGE_TYPE}</th>
                        <th style="width: 100px"></th>
                    </tr>
                    </thead>
                </table>
                </div>


            </div>
        </div>
    </div>

    <div class="row right_column" id="information_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content" id="information_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    function switchOnCollapse(id) {
        if ($("#"+id).children('i').hasClass('icon-angle-up')) {
            $("#"+id).click();
        }
    }

    function resetStatement() {
        $("#information_management").hide();
        switchOnCollapse('catalog_list_collapse');
        //var table = $('.table').DataTable();
        //table.draw(false);
        $(window).scrollTop(0);
        return false;
    }

    var editors = null;

    function onClickEvent(obj, table){
        var event_id = $(obj).find('.group-info').data('id');

        $(".check_on_off").bootstrapSwitch({
            onSwitchChange: pageStatus,
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        });

        var id = $('.table').DataTable().row('.selected').index();
        $("#row_id").val(id);

        const url = new URL(window.location.href);
        url.searchParams.set('row', id);
        window.history.pushState({ },"", url.toString());

        $.post("design/group-action", { 'action': 'side', 'id' : event_id }, function(data, status){
            if (status == "success") {
                $('#information_management_data .scroll_col').html(data);
            } else {
                alert("Request error.");
            }
        },"html");
    }

    function pageStatus(element, status){
        const id = $(element.target).closest('tr').find('.group-info').data('id');
        $.post("design/group-action", { 'action': 'status', id, status: +status }, function(data, status){
            if (status !== "success") {
                alertMessage("Request error.");
            }
        },"html");
    }

    $(function() {
        const url = new URL(window.location.href)
        $("#row_id").val(url.searchParams.get('row'));

        $("div.table-holder, .btn-add-group").dropzone({
            url: "{Yii::$app->urlManager->createUrl('design/group-upload')}",
            success: function(){
                $('.table').DataTable().ajax.reload();
            },
            previewTemplate: '<div></div>',
            acceptedFiles: '.zip',
        });

        $(".check_on_off").bootstrapSwitch(
            {
                onSwitchChange: function (element, arguments) {
                    return true;
                },
                onText: "{$smarty.const.SW_ON}",
                offText: "{$smarty.const.SW_OFF}",
                handleWidth: '20px',
                labelWidth: '24px'
            }
        );
    });


</script>