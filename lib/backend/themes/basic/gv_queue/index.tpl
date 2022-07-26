<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->


<!--===reviews list===-->
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content" id="reviews_list_data">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-gv_queue"
                       checkable_list="0,1,2" data_ajax="gv_queue/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->catalogTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>
                </table>
            </div>
    </div>
</div>
<!--===/reviews list===-->

<script type="text/javascript">

    function preEditItem( item_id ) {
        $.post("gv_queue/itempreedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#reviews_management_data .scroll_col').html(data);
                $("#reviews_management").show();
                switchOnCollapse('reviews_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function editItem(item_id) {
        /*
        var parent_id = 0
        if (item_id == 0) {
            parent_id = $('#global_id').val();
        }
        */

        $.post("gv_queue/itemedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#reviews_management_data .scroll_col').html(data);
                $("#reviews_management").show();
                switchOnCollapse('reviews_management_collapse');
                //switchOffCollapse('reviews_list_box_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function saveItem() {
        $.post("gv_queue/submit", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#reviews_management_data .scroll_col').html(data);
                $("#reviews_management").show();

                $('.table').DataTable().search('').draw(false);

                /*
                var table = $('.table').dataTable().api();
                var url = $base_url + "/gv_queue/categorieslist/";
                table.data().ajax.url(url).load();
                */

            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function deleteItemConfirm( item_id) {
        $.post("gv_queue/confirmitemdelete", {  'item_id': item_id }, function (data, status) {
            if (status == "success") {
                $('#reviews_management_data .scroll_col').html(data);
                $("#reviews_management").show();
                switchOnCollapse('reviews_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteItem() {
        $.post("gv_queue/itemdelete", $('#item_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
                $('#reviews_management_data').html("");
                switchOffCollapse('reviews_management_collapse');
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
        $("#reviews_management").hide();

        switchOnCollapse('reviews_list_box_collapse');
        switchOffCollapse('reviews_management_collapse');

        $('reviews_management_data').html('');
        $('#reviews_management').hide();

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

<!--===  reviews management ===-->
<div class="row right_column" id="reviews_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="reviews_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--=== reviews management ===-->
</div>