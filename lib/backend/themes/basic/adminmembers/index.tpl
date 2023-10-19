<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<div class="widget box box-wrapp-blue filter-wrapp" id="admins-filter">
    <div class="widget-header filter-title">
        <h4>{$smarty.const.TEXT_FILTER}</h4>
        <div class="toolbar no-padding">
            <div class="btn-group">
                <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
            </div>
        </div>
    </div>
    <div class="widget-content">

        <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
            <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />

            <div class="" style="max-width: 500px; margin: 0 auto">
                <div class="row align-items-center mb-2">
                    <div class="col-4 align-right">Group:</div>
                    <div class="col-8">{$app->controller->view->filterStatusTypes}</div>
                </div>
                <div class="row align-items-center mb-2">
                    <div class="col-4 align-right">Status:</div>
                    <div class="col-8">
                        <select name="status" class="form-control">
                            <option value="0">{$smarty.const.TEXT_ALL}</option>
                            <option value="1">{$smarty.const.TEXT_ACTIVE}</option>
                            <option value="2">{$smarty.const.TEXT_DISABLED}</option>
                        </select>
                    </div>
                </div>


                <div class="filters_btn">
                    <a href="javascript:void(0)" onclick="return resetFilter();" class="btn">{$smarty.const.TEXT_RESET}</a>
                    <button type="submit" class="btn btn-primary">{$smarty.const.TEXT_SEARCH}</button>&nbsp;
                </div>
            </div>
        </form>

    </div>
</div>
<div class="order-wrap">
<!--===Member Groups List ===-->
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content">
                <div class="alert fade in" style="display:none;">
                  <span id="message_plce"></span>
                </div>
                <div class="ord_status_filter_row" style="min-height: 27px">

              </div>
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-adminmembers" checkable_list="0,1,2" data_ajax="adminmembers/memberlist">
                    <thead>
                    <tr>
                        {foreach $app->controller->view->adminTable as $tableItem}
                            <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                        {/foreach}
                    </tr>
                    </thead>

                </table>
            </div>
    </div>
</div>
<!-- /Member Groups List -->

<script type="text/javascript">
    function setFilterState() {
        orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({ }, '', url);
    }
    
    function onClickEvent(obj, table) {
       // $("#admin_management").hide();
       $('#row_id').val(table.find(obj).index());
       setFilterState();
        var admin_id = $(obj).find('input.cell_identify').val();
        $.post("adminmembers/adminmembersactions", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
                $('#admin_management_data .scroll_col').html(data);
                $("#admin_management").show();
            } else {
                alert("Request error.");
                //$("#admin_management").hide();
            }
        },"html");
    }
    function onUnclickEvent(obj) {
        $("#admin_management").hide();
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
        $("#admin_management").hide();
        switchOnCollapse('admin_list_collapse');
        var table = $('.table').DataTable();
        table.draw(false);
        $(window).scrollTop(0);
        return false;
    }
    function editAdmin(admin_id) {
        $("#admin_management").hide();
        $.post("adminmembers/adminedit", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
                $('#admin_management_data .scroll_col').html(data);
                $("#admin_management").show();
                switchOffCollapse('admin_list_collapse');
            } else {
                alert("Request error.");
                //$("#admin_management").hide();
            }
        },"html");
        return false;
    }
    function assignPlatforms(admin_id) {
        $("#admin_management").hide();
        $.post("adminmembers/assign-platforms", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
                $('#admin_management_data .scroll_col').html(data);
                $("#admin_management").show();
                switchOffCollapse('admin_list_collapse');
            } else {
                alert("Request error.");
                //$("#admin_management").hide();
            }
        },"html");
        return false;
    }
    function assignWarehouses(admin_id) {
        $("#admin_management").hide();
        $.post("adminmembers/assign-warehouses", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
                $('#admin_management_data .scroll_col').html(data);
                $("#admin_management").show();
                switchOffCollapse('admin_list_collapse');
            } else {
                alert("Request error.");
                //$("#admin_management").hide();
            }
        },"html");
        return false;
    }
    function assignSuppliers(admin_id) {
        $("#admin_management").hide();
        $.post("adminmembers/assign-suppliers", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
                $('#admin_management_data .scroll_col').html(data);
                $("#admin_management").show();
                switchOffCollapse('admin_list_collapse');
            } else {
                alert("Request error.");
                //$("#admin_management").hide();
            }
        },"html");
        return false;
    }
    function confirmDeleteAdmin(admin_id) {
        $("#admin_management").hide();
        $.post("adminmembers/confirmadmindelete", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
                $('#admin_management_data .scroll_col').html(data);
                $("#admin_management").show();
                switchOffCollapse('admin_list_collapse');
            } else {
                alert("Request error.");
                //$("#admin_management").hide();
            }
        },"html");
        return false;
    }
    function deleteAdmin() {
        $("#admin_management").hide();
        $.post("adminmembers/admindelete", $('#admin_edit').serialize(), function(data, status){
            if (status == "success") {
                resetStatement()
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }
    function check_form(admin_id) {
        //ajax save
        $("#admin_management").hide();
        var admin_id = $( "input[name='admin_id']" ).val();
        $.post("adminmembers/adminsubmit", $('#admin_edit').serialize(), function(data, status){
            if (status == "success") {
                //$('#admin_management_data').html(data);
                //$("#admin_management").show();
                $('#admin_management_data .scroll_col').html(data);
                $("#admin_management").show();

                /*
                switchOnCollapse('admin_list_collapse');
                var table = $('.table').DataTable();
                table.draw(false);
                setTimeout('$(".cell_identify[value=\''+admin_id+'\']").click();', 500);
                */
                //$(".cell_identify[value='"+admin_id+"']").click();
                /*setTimeout( function(){
                    // resetStatement()
                }, 3500);*/
                resetStatement();
            } else {
                alert("Request error.");
                //$("#admin_management").hide();
            }
        },"html");
        //$('#admin_management_data').html('');
        return false;
    }

    function enableUser(admin_id) {
        $("#admin_management").hide();
        $.post("adminmembers/enable-admin", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
                resetStatement()
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }

    function disableUser(admin_id) {
        $("#admin_management").hide();
        $.post("adminmembers/disable-admin", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
                resetStatement()
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }
    
    function resetGaButton(admin_id) {
        $("#admin_management").hide();
        $.post("adminmembers/reset-admin-ga", { 'admin_id' : admin_id }, function(data, status){
            if (status == "success") {
                resetStatement()
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }
    
function check_passw_form(){
    var length = {$smarty.const.ADMIN_PASSWORD_MIN_LENGTH};
  if (document.forms.passw_form.change_pass.value.length < length){
    alert("New password must have at least " + length + " characters.");
    return false;       
  } else {
    $.post('{Yii::$app->urlManager->createUrl('adminmembers/generatepassword')}', $('form[name=passw_form]').serialize(), function(data, status){
      if (status == "success") {
          $('.alert #message_plce').html('');
          $('.alert').show().removeClass('alert-error alert-success alert-warning alert-danger').addClass(data.messageType).find('#message_plce').append(data.message);
          $('.popup-box:last').trigger('popup.close');
          $('.popup-box-wrap:last').remove();
          resetStatement();
      } else {
          alert("Request error.");
      }      
    }, "json");
    return false;
  }
}

function passFormAfretShow() {
    $('input[type="password"]').showPassword(); 
    
    $('.generate_password').on('click', function(){
            $.get('{$app->urlManager->createUrl('adminaccount/generate-password')}', function(data){
                $('input[name="change_pass"]').val(data);
                $('input[name="change_pass"]').trigger('keyup');
                if($('input[name="change_pass"]').attr('type') == 'password'){
                  $('.eye-password').click();
                }
            }, 'json')
            return false;
    });
                
    var form = $('#passw_form');
    $('input', form).validate();
    form.on('submit', function(){
        if ($('.required-error', form).length === 0){
            return check_passw_form();
        }
        return false;
    });
}
function applyFilter() {
    resetStatement();
    return false;    
}

    function resetFilter() {
        $('#filterForm').trigger('filters_reset');
        $('select[name="aclID"]').val(0);
        $('select[name="status"]').val(0);
        $("#row_id").val(0);
        resetStatement();
        return false;
    }
</script>

<!--===Actions ===-->
<div class="row right_column" id="admin_management">
        <div class="widget box">
            <div class="widget-content fields_style" id="admin_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
</div>
<!--===Actions ===-->
</div>