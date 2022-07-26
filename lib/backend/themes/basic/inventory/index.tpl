
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$this->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--===inventory List ===-->
<div class="row">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i>  Inventory List  </h4>
                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="inventory_list_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0,1,2,3" data_ajax="{$Yii->baseUrl}/inventory/list">
                    <thead>
                    <tr>
                        {foreach $this->view->inventoryTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>

                </table>
                <p class="btn-toolbar">
                    <input type="button" class="btn btn-primary" value="Insert" onClick="return editInventory(0)">
                </p>
            </div>
        </div>
    </div>
</div>
<!-- /inventory List -->

<script type="text/javascript">

    function preEditItem( item_id ) {
        $.post("{$Yii->baseUrl}/inventory/itempreedit", {
            'item_id': item_id
        }, function (data, status) {
            if (status == "success") {
                $('#inventory_management_data').html(data);
                $("#inventory_management").show();
                switchOnCollapse('inventory_management_collapse');
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function editInventory(inventory_id)
    {
        if(inventory_id == 0){         }

        switchOnCollapse('inventory_management_collapse');
        $.post("inventory/inventoryedit", { 'inventory_id' : inventory_id }, function(data, status){
            if (status == "success") {
                $('#inventory_management_data').html(data);
                $("#inventory_management").show();
                switchOffCollapse('inventory_list_collapse');
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }

    function saveInventory()
    {
        $.post("{$Yii->baseUrl}/inventory/inventorysubmit", $('#save_inventory_form').serialize(), function(data, status){
            if (status == "success") {
                $('#inventory_management_data').html(data);
                $("#inventory_management").show();

                $('.gallery-album-image-placeholder').html('');

                $('.table').DataTable().search( '' ).draw(false);

            } else {
                alert("Request error.");
            }
        },"html");

        $("form#save_inventory_form input[name=inventory_id]").val("");

        return false;
    }

    function deleteInventoryConfirm(inventory_id)
    {
        $.post("inventory/confirminventorydelete", { 'inventory_id' : inventory_id }, function(data, status){
            if (status == "success") {
                $('#inventory_management_data').html(data);
                $("#inventory_management").show();
                switchOnCollapse('inventory_management_collapse');
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }

    function deleteInventory()
    {
        //$("#inventory_management").hide();
        $.post("inventory/inventorydelete", $('#inventory_delete').serialize(), function(data, status){
            if (status == "success") {
                //resetStatement()
                $('#inventory_management_data').html("");
            } else {
                alert("Request error.");
            }
        },"html");

        return false;
    }

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
        $("#inventory_management").hide();

        switchOnCollapse('inventory_list_collapse');
        switchOffCollapse('inventory_management_collapse');
        var table = $('.table').DataTable();
        table.draw(false);

        $(window).scrollTop(0);

        return false;
    }

    function onClickEvent(obj, table) {

        var event_id = $(obj).find('input.cell_identify').val();

        preEditItem(event_id);
     
    }

    function onUnclickEvent(obj, table) {
        $("#inventory_management").hide();
        var event_id = $(obj).find('input.cell_identify').val();
        var type_code = $(obj).find('input.cell_type').val();
    }

    function report()
    {
        $.post("inventory/report", $('#save_inventory_form').serialize(), function(data, status){
            if (status == "success") {
                $('#inventory_management_data').html(data);
            } else {
                alert("Request error.");
            }
        },"html");

        return false;
    }

    function setFilter()
    {
        $.post("inventory/setfilter", $('#save_inventory_form').serialize(), function(data, status){
            if (status == "success") {
                $('#options-wrapper').html(data.html);

            } else {
                alert("Request error.");
            }
        },"json");

        return false;
    }

    function setProduct()
    {
        $.post("inventory/setproduct", $('#save_inventory_form').serialize(), function(data, status){
            if (status == "success") {
                $('#product-wrapper').html(data.html);

            } else {
                alert("Request error.");
            }
        },"json");

        return false;
    }



</script>

<!--===  inventory management ===-->
<div class="row" id="inventory_management" style="display: none;">
    <div class="col-md-12">
        <div class="widget box">
            <div class="widget-header">
                <h4><i class="icon-reorder"></i><span id="inventory_management_title">Inventory management</span></h4>
                <div class="toolbar no-padding">
                    <div class="btn-group">
                        <span id="inventory_management_collapse" class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                    </div>
                </div>
            </div>
            <div class="widget-content" id="inventory_management_data">
                  info
            </div>
        </div>
    </div>
</div>
<!--===  inventory management ===-->