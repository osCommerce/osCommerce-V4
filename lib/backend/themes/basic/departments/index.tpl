{use class="yii\helpers\Html"}
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->


<!--===departments list===-->
<div class="order-wrap">
  <div class="row order-box-list" id="departments_list">
    <div class="col-md-12">
      <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
        <input type="hidden" name="dID" id="row_id" value="{$app->controller->view->filters->row}" />
        <div class="">
          <div>{Html::checkbox('inactive', $app->controller->view->inactive, ['value'=>'1', 'class' => 'js_check_status'])}{$smarty.const.TEXT_SHOW_INACTIVE_DEPARTMENTS}</div>
        </div>
      </form>

      <div class="widget-content" id="departments_list_data">
        <div class="btn-wr after btn-wr-top disable-btn data-table-top-left">
                <a href="javascript:void(0)" onclick="deleteSelectedOrders();" class="btn btn-del btn-no-margin">{$smarty.const.TEXT_DELETE_SELECTED}</a>
        </div>
        <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable table-switch-on-off js-table-sortable"
               checkable_list="0,1,2,3,4" data_ajax="{$app->urlManager->createUrl('departments/list')}">
          <thead>
            <tr>
              {foreach $app->controller->view->departmentsTable as $tableItem}
                <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                {/foreach}
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  <!--===/departments list===-->

  <script type="text/javascript">
  var global = '{$dID}';
  
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
function deleteSelectedOrders() {
    if (getTableSelectedCount() > 0) {
        var selected_ids = getTableSelectedIds();
        
        bootbox.dialog({
                message: "{$smarty.const.TEXT_DELETE_SELECTED} <span class=\"lowercase\">{$smarty.const.BOX_HEADING_DEPARTMENTS}?</span>",
                title: "{$smarty.const.TEXT_DELETE_SELECTED} <span class=\"lowercase\">{$smarty.const.BOX_HEADING_DEPARTMENTS}</span>",
                buttons: {
                        success: {
                                label: "Yes",
                                className: "btn-delete",
                                callback: function() {
                                    $.post("departments/departmentsdelete", { 'selected_ids' : selected_ids, 'delete_reviews' : '1' }, function(data, status){
                                        if (status == "success") {
                                            resetStatement();
                                        } else {
                                            alert("Request error.");
                                        }
                                    },"html");
                                }
                        },
                        main: {
                                label: "Cancel",
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
  function viewDepartment(item_id) {
    $.post("{$app->urlManager->createUrl('departments/view')}", {
      'dID': item_id
    }, function (data, status) {
      if (status == "success") {
        $('#departments_management_data .scroll_col').html(data);
        $("#departments_management").show();
        $('.js-open-tree-popup').popUp();
        /*switchOnCollapse('departments_management_collapse');*/
        deleteScroll();
        heightColumn();
      } else {
        alert("Request error.");
      }
    }, "html");

    //    $("html, body").animate({ scrollTop: $(document).height() }, "slow");

    return false;
  }
  function switchKeepAlive (item_id, status) {
    $.post("{$app->urlManager->createUrl('departments/keep-alive')}", { 'dID' : item_id, 'status' : status }, function(data, status) {
      if (status == "success") {
        resetStatement(item_id);
      } else {
        alert("Request error.");
      }
    },"html");
  }
  function switchDepartmentStatement (item_id, status) {
    $.post("{$app->urlManager->createUrl('departments/switch-status')}", { 'dID' : item_id, 'status' : status }, function(data, status) {
      if (status == "success") {
        resetStatement(item_id);
      } else {
        alert("Request error.");
      }
    },"html");
  }

  function deleteItemConfirm(item_id) {
    $.post("{$app->urlManager->createUrl('departments/confirmitemdelete')}", { 'dID': item_id }, function (data, status) {
      if (status == "success") {
        $('#departments_management_data .scroll_col').html(data);
      } else {
        alert("Request error.");
      }
    }, "html");
    return false;
  }

  function deleteItem() {
    if (confirm('Are you sure?')) {
      $.post("{$app->urlManager->createUrl('departments/itemdelete')}", $('#item_delete').serialize(), function (data, status) {
        if (status == "success") {
          if (data == 'OK') {
            window.location.href = "{$app->urlManager->createUrl('departments/index')}";
          } else {
            $("#messageStack").html('<div class="alert alert-warning fade in"><i data-dismiss="alert" class="icon-remove close"></i>' + data + '</div>');
          }
        } else {
          alert("Request error.");
        }
      }, "html");
    }
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

  function setFilterState() {
    orig = $('#filterForm').serialize();
    var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
    window.history.replaceState({ }, '', url);
  }
    
  function applyFilter(){
      resetStatement();
      return false;
  }

  function resetStatement(item_id) {
    if (item_id > 0) global = item_id;

    $("#departments_management").hide();

    //switchOnCollapse('departments_list_box_collapse');
    //switchOffCollapse('departments_management_collapse');

    //$('#departments_management_data .scroll_col').html('');
    //$('#departments_management').hide();

    var table = $('.table').DataTable();
    table.draw(false);

  //  $(window).scrollTop(0);

    return false;
  }

  var first = true;
  function onClickEvent(obj, table) {

    var event_id = $(obj).find('input.cell_identify').val();
    if (global > 0) event_id = global;
    viewDepartment(event_id);
    $('.table tr').removeClass('selected');
    $('.table').find('input.cell_identify[value=' + event_id + ']').parents('tr').addClass('selected');
    global = '';

    $('#row_id').val(event_id);
    setFilterState();
    /*url = window.location.href;
    if (url.indexOf('dID=') > 0) {
      url = url.replace(/dID=\d+/g, 'dID=' + event_id);
    } else {
      url += '?dID=' + event_id;
    }
    if (first) {
      first = false;
    } else {
      window.history.replaceState({ }, '', url);
    }*/

    $(".check_on_off").bootstrapSwitch(
      {
        onSwitchChange: function (element, arguments) {
          switchDepartmentStatement(element.target.value, arguments);
          return true;  
        },
        handleWidth: '20px',
        labelWidth: '24px'
      }
    );    

    $(".keep_on_off").bootstrapSwitch(
      {
        onSwitchChange: function (element, arguments) {
          switchKeepAlive(element.target.value, arguments);
          return true;  
        },
        handleWidth: '20px',
        labelWidth: '24px'
      }
    );  
  }

  function onUnclickEvent(obj, table) {

    var event_id = $(obj).find('input.cell_identify').val();
  }

  $(document).ready(function(){
      $('.js_check_status').bootstrapSwitch({
          onText: "{$smarty.const.SW_ON}",
          offText: "{$smarty.const.SW_OFF}",
          handleWidth: '20px',
          labelWidth: '24px'
      });
      $('.js_check_status').on('click switchChange.bootstrapSwitch',function(){
          applyFilter();
      });
      
      $('.uniform').click(function() { 
        if($(this).is(':checked')){
            $('.order-box-list .btn-wr').removeClass('disable-btn');
        }else{
            $('.order-box-list .btn-wr').addClass('disable-btn');
        }
    });
      
  });
  </script>

  <!--===  departments management ===-->
  <div class="row right_column" id="departments_management" style="display: none;">
    <div class="widget box">
      <div class="widget-content fields_style" id="departments_management_data">
        <div class="scroll_col"></div>
      </div>
    </div>
  </div>
  <!--=== departments management ===-->
</div>

<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>
