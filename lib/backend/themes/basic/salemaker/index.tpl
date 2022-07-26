

<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$this->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->


<!--===salemaker list===-->
<div class="row" id="salemaker_list">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i> Params</h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="salemaker_list_box_collapse" class="btn btn-xs widget-collapse"><i
                                    class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content" id="salemaker_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable"
                       checkable_list="0,1,2,3,4" data_ajax="{$Yii->baseUrl}/salemaker/list">
                    <thead>
                    <tr>
                        {foreach $this->view->salemakerTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
                <p class="btn-toolbar">
                    <input type="button" class="btn btn-primary" value="Insert"
                           onClick="return editItem(0)">
                </p>
            </div>
        </div>
    </div>
</div>
<!--===/salemaker list===-->

<script type="text/javascript">

    function preEditItem( item_id ) {
        $.post("{$Yii->baseUrl}/salemaker/itempreedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#salemaker_management_data').html(data);
                $("#salemaker_management").show();
                switchOnCollapse('salemaker_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

       // $("html, body").animate({ scrollTop: $(document).height() }, "slow");

        return false;
    }

    function editItem(item_id) {

        $.post("{$Yii->baseUrl}/salemaker/itemedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#salemaker_management_data').html(data);
                $("#salemaker_management").show();
                switchOnCollapse('salemaker_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function saveItem() {
        $.post("{$Yii->baseUrl}/salemaker/submit", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#salemaker_management_data').html(data);
                $("#salemaker_management").show();

                $('.table').DataTable().search('').draw(false);

            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function deleteItemConfirm( item_id) {
        $.post("salemaker/confirmitemdelete", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#salemaker_management_data').html(data);
                $("#salemaker_management").show();
                switchOnCollapse('salemaker_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItem() {
        $.post("salemaker/itemdelete", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#salemaker_management_data').html("");
                switchOffCollapse('salemaker_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function switchOffCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-down')) {
            $("#" + id).click();
        }
    }

    function switchOnCollapse(id) {
        if ($("#" + id).children('i').hasClass('icon-angle-up')) {
            $("#" + id).click();
        }
    }

    function resetStatement() {
        $("#salemaker_management").hide();

        switchOnCollapse('salemaker_list_box_collapse');
        switchOffCollapse('salemaker_management_collapse');

        $('salemaker_management_data').html('');
        $('#salemaker_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        $(window).scrollTop(0);

        return false;
    }

    function onClickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();

        preEditItem(  event_id );
    }

    function onUnclickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();
    }

</script>

<!--===  salemaker management ===-->
<div class="row" id="salemaker_management" style="display: none;">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i><span id="salemaker_management_title">Salemaker management</span>
                </h4>

                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="salemaker_management_collapse" class="btn btn-xs widget-collapse"><i
                                    class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content fields_style" id="salemaker_management_data">
                info
            </div>
        </div>
    </div>
</div>
<!--=== salemaker management ===-->