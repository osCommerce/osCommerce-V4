<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--=== Page Content ===-->
<div class="order-wrap">
    <input type="hidden" id="row_id">
    <!--===Zones List ===-->
    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable" checkable_list="0" data_ajax="geo_zones/list">
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
    <!-- /Zones List -->

    <!--===Actions ===-->
    <div class="row right_column" id="catalog_management">
        <div class="widget box">
            <div class="widget-content" id="catalog_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
    <!--===Actions ===-->
</div>                                              
<input type="hidden" id="global_id" value="0" />
<input type="hidden" value="" id="global_type_code">
<script type="text/javascript">
    var categoryTableHeadings = [], childTableHeadings = [];
    i = 0;
    {foreach $app->controller->view->catalogTable as $tableItem}
        categoryTableHeadings[i++] = '{$tableItem.title|escape:'html'}';
    {/foreach}
    i = 0;
    {foreach $app->controller->view->zoneTable as $tableItem}
        childTableHeadings[i++] = '{$tableItem.title|escape:'html'}';
    {/foreach}

    function setDTHeadings(table, type_code) {
        var table = $('.datatable').DataTable();
        try {
            if (type_code == 'category') {
                for(i=0, n=categoryTableHeadings.length; i<n; i++) {
                    $(table.columns(i).header()).text( categoryTableHeadings[i] );
                }
            } else {
                for(i=0, n=childTableHeadings.length; i<n; i++) {
                    $(table.columns(i).header()).text( childTableHeadings[i] );
                }
            }
        }catch (e) { }
    }
        
    function resetStatement() {
        $("#catalog_management").hide();
        var table = $('.table').DataTable();
        table.draw(false);
        $(window).scrollTop(0);
        return false;
    }

    function checkCatButtonsStatement() {
        $('#add_cat').show();
        $('#add_prop').hide();
    }

    function checkPropButtonsStatement() {
        $('#add_cat').hide();
        $('#add_prop').show();
    }

    function update_zone(theForm) {
        var NumState = theForm.zone_id.options.length;
        var SelectedCountry = "";

        while (NumState > 0) {
            NumState--;
            theForm.zone_id.options[NumState] = null;
        }

        SelectedCountry = theForm.zone_country_id.options[theForm.zone_country_id.selectedIndex].value;

        {tep_js_zone_list('SelectedCountry', 'theForm', 'zone_id')}

    }

    function checkProductForm() {
        $("#catalog_management").hide();
        $.post("geo_zones/productsubmit", $('#option_save').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function editProduct(products_id) {
        $("#catalog_management").hide();
        var geo_zone_id = $('#global_id').val();
        $.post("geo_zones/productedit", { 'products_id': products_id, 'geo_zone_id': geo_zone_id}, function (data, status) {
            if (status == "success") {
                $('#catalog_management_data').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteProduct() {
        $("#catalog_management").hide();
        $.post("geo_zones/productdelete", $('#option_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }

    function confirmDeleteProduct(products_id) {
        $("#catalog_management").hide();
        $.post("geo_zones/confirmproductdelete", { 'products_id': products_id}, function (data, status) {
            if (status == "success") {
                $('#catalog_management_data').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function checkCategoryForm() {
        $("#catalog_management").hide();
        $.post("geo_zones/categorysubmit", $('#option_save').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function editCategory(category_id) {
        $("#catalog_management").hide();
        $.post("geo_zones/categoryedit", { 'category_id': category_id}, function (data, status) {
            if (status == "success") {
                $('#catalog_management_data').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function deleteCategory() {
        $("#catalog_management").hide();
        $.post("geo_zones/categorydelete", $('#option_delete').serialize(), function (data, status) {
            if (status == "success") {
                resetStatement();
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function confirmDeleteCategory(category_id) {
        $("#catalog_management").hide();
        $.post("geo_zones/confirmcategorydelete", { 'category_id': category_id}, function (data, status) {
            if (status == "success") {
                $('#catalog_management_data').html(data);
                $("#catalog_management").show();
            } else {
                alert("Request error.");
            }
        }, "html");
        return false;
    }

    function switchStatement(id, status) {
        $.post("geo_zones/switch-status", { 'id': id, 'status': status}, function (data, status) {
            if (status == "success") {
                if (data == 'reset') {
                    resetStatement();
                } else if (data == 'reload') {
                    window.location.reload();
                }
                //resetStatement();
            } else {
                alert("Request error.");
            }
        }, "html");
    }

    function onClickEvent(obj, table) {
        $("#catalog_management").hide();
        $('#catalog_management_data').html('');
        $('#row_id').val(table.find(obj).index());
        var event_id = $(obj).find('input.cell_identify').val();
        var type_code = $(obj).find('input.cell_type').val();
        $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (elements, arguments) {
                switchStatement(elements.target.dataset.id, arguments);
                return true;
            },
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
        );
        setDTHeadings(table, type_code);
        if (type_code == 'category') {
            $.post("geo_zones/categoryactions", { 'categories_id': event_id}, function (data, status) {
                if (status == "success") {
                    $('#catalog_management_data').html(data);
                    $("#catalog_management").show();
                } else {
                    alert("Request error.");
                }
            }, "html");
        } else if (type_code == 'product') {
            $.post("geo_zones/productactions", { 'products_id': event_id}, function (data, status) {
                if (status == "success") {
                    $('#catalog_management_data').html(data);
                    $("#catalog_management").show();
                } else {
                    alert("Request error.");
                }
            }, "html");
        }
    }

    function onUnclickEvent(obj, table) {
        $("#catalog_management").hide();
        var event_id = $(obj).find('input.cell_identify').val();
        var type_code = $(obj).find('input.cell_type').val();
        
        if (type_code == 'category') {
            checkPropButtonsStatement();
        } else if (type_code == 'parent') {
            checkCatButtonsStatement();
        }
        if (type_code == 'category' || type_code == 'parent') {
            $('#global_id').val(event_id);
            $(table).DataTable().draw(false);
        }

    }



</script>
<!-- /Page Content -->
