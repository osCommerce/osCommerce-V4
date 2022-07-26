
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
                {if is_array($messages)}
                    {foreach $messages as $message}
                        <div class="alert fade in {$message['messageType']}">
                            <i data-dismiss="alert" class="icon-remove close"></i>
                            <span id="message_plce">{$message['message']}</span>
                        </div>
                    {/foreach}
                {/if}
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable datatable" checkable_list="0" data_ajax="specials-types/list">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->specialsTypeTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>

                </table>

                </form>
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
            $("#currency_management").hide();
            switchOnCollapse('currency_list_collapse');
            var table = $('.table').DataTable();
            table.draw(false);
            $(window).scrollTop(0);
            return false;
        }
        function onClickEvent(obj, table) {
            $("#currency_management").hide();
            $('#currency_management_data').html('');
            $('#row_id').val(table.find(obj).index());
            var specials_type_id = $(obj).find('input.cell_identify').val();
            $.post("specials-types/actions", { 'specials_type_id' : specials_type_id }, function(data, status){
                if (status == "success") {
                    $('#currency_management_data').html(data);
                    $("#currency_management").show();
                } else {
                    alert("Request error.");
                }
            },"html");
        }

        function onUnclickEvent(obj, table) {
            $("#currency_management").hide();
            var event_id = $(obj).find('input.cell_identify').val();
            var type_code = $(obj).find('input.cell_type').val();
            $(table).DataTable().draw(false);
        }

        function specialsTypeEdit(id){
            $("#currency_management").hide();
            $.get("specials-types/edit", { 'specials_type_id' : id }, function(data, status){
                if (status == "success") {
                    $('#currency_management_data').html(data);
                    $("#currency_management").show();
                    switchOffCollapse('currency_list_collapse');
                } else {
                    alert("Request error.");
                }
            },"html");
            return false;
        }

        function specialsTypeSave(id){
            $.post("specials-types/save?specials_type_id="+id, $('form[name=specials_type]').serialize(), function(data, status){
                if (status == "success") {
                    //$('#currency_management_data').html(data);
                    //$("#currency_management").show();
                    $('.alert #message_plce').html('');
                    $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                    resetStatement();
                    switchOffCollapse('currency_list_collapse');
                } else {
                    alert("Request error.");
                }
            },"json");
            return false;
        }

        function specialsTypeDelete(id){
            if (confirm('Do you confirm?')){
                $.post("specials-types/delete", { 'specials_type_id' : id}, function(data, status){
                    if (status == "success") {
                        //$('.alert #message_plce').html('');
                        //$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                        if (data == 'reset') {
                            resetStatement();
                        } else{
                            $('#currency_management_data').html(data);
                            $("#currency_management").show();
                        }
                        switchOnCollapse('currency_list_collapse');
                    } else {
                        alert("Request error.");
                    }
                },"html");
                return false;
            }
        }


    </script>
    <!--===Actions ===-->
    <div class="row right_column" id="currency_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="currency_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
    <!--===Actions ===-->
    <!-- /Page Content -->
</div>