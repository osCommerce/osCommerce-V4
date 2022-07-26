
<!--=== Page Header ===-->
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->

<!--=== Page Content ===-->
<div class="order-wrap">
<div class="row order-box-list">
    <div class="col-md-12">
            <div class="widget-content">
<div class="alert fade in" style="display:none;">
<i data-dismiss="alert" class="icon-remove close"></i>
<span id="message_plce"></span>
</div>                
                <div class="btn-wr after btn-wr-top disable-btn data-table-top-left">
                       <a href="javascript:void(0)" onclick="deleteSelectedPages();" class="btn btn-del btn-no-margin">{$smarty.const.TEXT_DELETE_SELECTED}</a><a href="javascript:void(0)" onclick="statusSelectedPages(1);" class="btn btn-on-sel">{$smarty.const.TEXT_ON_SELECTED}</a><a href="javascript:void(0)" onclick="statusSelectedPages(0);" class="btn btn-off-sel">{$smarty.const.TEXT_OFF_SELECTED}</a>
                </div>
                <table class="{if $isMultiPlatforms}tab_edt_page_mul{/if} table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable tab-pages double-grid table-information_manager" checkable_list="" data_ajax="information_manager/list">
                        <thead>
                                <tr>
                                    {foreach $app->controller->view->infoTable as $tableItem}
                                        <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 2} class="checkbox-column"{/if}{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                                    {/foreach}
                                </tr>
                        </thead>

                </table>
                <div class="btn-wr after disable-btn">
                    <div>
                      <a href="javascript:void(0)" onclick="deleteSelectedPages();" class="btn btn-del btn-no-margin">{$smarty.const.TEXT_DELETE_SELECTED}</a><a href="javascript:void(0)" onclick="statusSelectedPages(1);" class="btn btn-on-sel">{$smarty.const.TEXT_ON_SELECTED}</a><a href="javascript:void(0)" onclick="statusSelectedPages(0);" class="btn btn-off-sel">{$smarty.const.TEXT_OFF_SELECTED}</a>
                    </div>
                    <div>
                    </div>
                </div>

                            <!--    <p class="btn-toolbar">
                                    <input type="button" class="btn btn-primary" value="{$smarty.const.TEXT_NEW}" onClick=" return loadInfo(0)">
                                </p> -->
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
                                        $("#information_management").hide();
                                        switchOnCollapse('catalog_list_collapse');
                                        //var table = $('.table').DataTable();
                                        //table.draw(false);
                                        $(window).scrollTop(0);
                                        return false;
                                    }

                                    function getTableSelectedIds() {
                                      var selected_messages_ids = [];
                                      var selected_messages_count = 0;
                                      $('input:checkbox:checked.uniform').each(function(j, cb) {
                                        var aaa = $(cb).closest('td').find('.cell_identify').val();
                                        if (typeof(aaa) != 'undefined') {
                                          selected_messages_ids[selected_messages_count] = aaa;
                                          selected_messages_count++;
                                        }
                                      });
                                      return selected_messages_ids;
                                    }

                                    function getTableSelectedCount() {
                                      var selected_messages_count = 0;
                                      $('input:checkbox:checked.uniform').each(function(j, cb) {
                                        var aaa = $(cb).closest('td').find('.cell_identify').val();
                                        if (typeof(aaa) != 'undefined') {
                                          selected_messages_count++;
                                        }
                                      });
                                      return selected_messages_count;
                                    }

                                    function deleteSelectedPages() {
                                      if (getTableSelectedCount() > 0) {
                                        var selected_ids = getTableSelectedIds();

                                        bootbox.dialog({
                                          message: "{$smarty.const.JS_DELETE_SELECTED_TEXT}",
                                          title: "{$smarty.const.JS_DELETE_SELECTED_HEAD}",
                                          buttons: {
                                            success: {
                                              label: "{$smarty.const.TEXT_BTN_YES}",
                                              className: "btn-delete",
                                              callback: function() {
                                                $.post("information_manager/delete-selected", { 'selected_ids' : selected_ids }, function(data, status){
                                                  if (status == "success") {
                                                    var table = $('.table').DataTable();
                                                    table.draw(false);
                                                    resetStatement();
                                                  } else {
                                                    alert("Request error.");
                                                  }
                                                },"html");
                                              }
                                            },
                                            main: {
                                              label: "{$smarty.const.TEXT_BTN_NO}",
                                              className: "btn-cancel",
                                              callback: function() {
                                                //console.log("Primary button");
                                              }
                                            }
                                          }
                                        });
                                      }
                                      return false;
                                    }

                                    function statusSelectedPages(status) {
                                      if (getTableSelectedCount() > 0) {
                                        var selected_ids = getTableSelectedIds();
                                        $.post("information_manager/status-change-selected", { 'selected_ids' : selected_ids, 'status':status }, function(data, status){
                                          if (status == "success") {
                                            var table = $('.table').DataTable();
                                            table.draw(false);
                                            resetStatement();
                                          } else {
                                            alert("Request error.");
                                          }
                                        },"html");
                                      }
                                      return false;
                                    }
var editors = null;
/*function loadInfo(event_id){
  $.get('information_manager/edit?information_id='+event_id, '', function(data){
    $("#information_management_data").html(data);
    $("#information_management").show();
    CKEDITOR.replaceAll('ckeditor');
  }, 'html');
}*/
                                    
function onClickEvent(obj, table){
    $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                if (arguments){
                    $('#'+$(element.target).data('id')).removeClass('platform-disable')
                } else {
                    $('#'+$(element.target).data('id')).addClass('platform-disable')
                }
                switchStatement(element.target.name, element.target.value, arguments);
                return true;  
            },
			onText: "{$smarty.const.SW_ON}",
  offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
  var event_id = $(obj).find('input.cell_identify').val();
  loadPageAction(event_id)
  //loadInfo(event_id);
}

function loadPageAction(event_id) {
  $.post("information_manager/pageactions", { 'info_id' : event_id }, function(data, status){
    if (status == "success") {
      $('#information_management_data .scroll_col').html(data);
      $("#order_management").show();
    } else {
      alert("Request error.");
      //$("#order_management").hide();
    }
  },"html");
}
          
function saveInfo(){
  cke_preload();
  $.post('information_manager/update', $('#edit_info').serialize(), function(response){
    $('.table').DataTable().destroy();
    var table = $('.table').DataTable({
      data: response['data']
    });
    table.draw(true);
    resetStatement();
  }, 'json');
  return false;
}          
          
                                    function onUnclickEvent(obj, table) {
                                        resetStatement();
                                    }
function confirmDeleteInfoPage(info_id) {
  $.post("information_manager/confirm-delete", { 'info_id' : info_id }, function(data, status){
    if (status == "success") {
      $('#information_management_data .scroll_col').html(data);
    } else {
      alert("Request error.");
    }
  },"html");
  //loadInfo(event_id);
}

function confirmedDeletePage(info_id){
  $.post("information_manager/delete", { 'info_id' : info_id }, function(data, status){
    if (status == "success") {
      $('.table').DataTable().destroy();
      var table = $('.table').DataTable({
        data: data['data']
      });
      table.draw(true);
      resetStatement();
    } else {
      alert("Request error.");
    }
  },"json");
}

function deleteInfo(id) {
  bootbox.dialog({
    message: "{$smarty.const.JS_DELETE_PAGE_TEXT}",
    title: "{$smarty.const.JS_DELETE_PAGE_HEAD}",
    buttons: {
      success: {
        label: "{$smarty.const.TEXT_BTN_YES}",
        className: "btn-delete",
        callback: function() {
          $.post("information_manager/delete", { 'info_id' : id }, function(data, status){
            if (status == "success") {
              var table = $('.table').DataTable();
              table.draw(false);
              resetStatement();
            } else {
              alert("Request error.");
            }
          },"html");
        }
      },
      main: {
        label: "{$smarty.const.TEXT_BTN_NO}",
        className: "btn-cancel",
        callback: function() {
          //console.log("Primary button");
        }
      }
    }
  });
  return;
  if (confirm("Do you really want to delete it?")){
    $.post("information_manager/delete", {
      information_id : id
     }, function(response, status){
      if (status == "success") {
        $('.table').DataTable().destroy();
        var table = $('.table').DataTable({
          data: response['data']
        });
        table.draw(true);      
        resetStatement();
      } else {
          alert("Request error.");
      }
    },"json");
  }
  return false;
}
function switchStatement(type, id, status) {
  $.post("information_manager/switch-status", { 'type' : type, 'id' : id, 'status' : status }, function(data, status){
    if (status == "success") {
      resetStatement();
    } else {
      alert("Request error.");
    }
  },"html");
}

$(document).ready(function() {
    $('th.checkbox-column .uniform').click(function() {
        if($(this).is(':checked')){
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    });
  $('.datatable').on('draw.dt',function () {
    var $main_switch = $('th.checkbox-column .uniform');
    var have_checked = $(this).find('.uniform').not($main_switch).filter(':checked').length>0;
    if ( $main_switch.get(0).checked ) {
      $main_switch.get(0).checked = false;
      $.uniform.update();
    }
    if ( have_checked ) {
      $('.order-box-list .btn-wr').removeClass('disable-btn');
    }else{
      $('.order-box-list .btn-wr').addClass('disable-btn');
    }

    $('.hide-page').closest('tr').addClass('hided-page')
  });

  $(".check_on_off").bootstrapSwitch(
      {
        onSwitchChange: function (element, arguments) {
          console.log(element.data('id'));
          switchStatement(element.target.name, element.target.value, arguments);
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
<!--===Actions ===-->        