<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
    <input type="hidden" name="row" id="row_id" value="{$app->controller->view->filters->row}" />
    <input type="hidden" name="department_id" id="page_department_id" value="{$selected_department_id}" />
</form>

<div class="tabbable tabbable-custom" style="margin-bottom: 0;">
    <ul class="nav nav-tabs">
        {foreach $departments as $department}
          <li class="{if $department['id']==$selected_department_id} active {/if}"><a class="js_link_department_modules_select" href="{$department['link']}" data-department_id="{$department['id']}"><span>{$department['text']}</span></a></li>
        {/foreach}
    </ul>
</div>
    
<div class="order-wrap">
<!--===Member Groups List ===-->
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content">
                <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-adminmembers" checkable_list="0,1,2" data_ajax="departments-adminmembers/memberlist">
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
        var department_id = $('input[name="department_id"]').val();
        $.post("departments-adminmembers/adminmembersactions", { 'admin_id' : admin_id, 'department_id' : department_id }, function(data, status){
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
        var department_id = $('input[name="department_id"]').val();
        $.post("departments-adminmembers/adminedit", { 'admin_id' : admin_id, 'department_id' : department_id }, function(data, status){
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
        var department_id = $('input[name="department_id"]').val();
        $.post("departments-adminmembers/confirmadmindelete", { 'admin_id' : admin_id, 'department_id' : department_id }, function(data, status){
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
        $.post("departments-adminmembers/admindelete", $('#admin_edit').serialize(), function(data, status){
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
        $.post("departments-adminmembers/adminsubmit", $('#admin_edit').serialize(), function(data, status){
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
        var department_id = $('input[name="department_id"]').val();
        $.post("departments-adminmembers/enable-admin", { 'admin_id' : admin_id, 'department_id' : department_id }, function(data, status){
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
        var department_id = $('input[name="department_id"]').val();
        $.post("departments-adminmembers/disable-admin", { 'admin_id' : admin_id, 'department_id' : department_id }, function(data, status){
            if (status == "success") {
                resetStatement()
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }
    
    function check_passw_form(length){
      if (document.forms.passw_form.change_pass.value.length < length){
        alert("New password must have at least " + length + " characters.");
        return false;       
      } else {
        $.post('{Yii::$app->urlManager->createUrl('departments-adminmembers/generatepassword')}', $('form[name=passw_form]').serialize(), function(data, status){
          if (status == "success") {
              $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap:last').remove();
              resetStatement();
          } else {
              alert("Request error.");
          }      
        }, "html");
        return false;
      }
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