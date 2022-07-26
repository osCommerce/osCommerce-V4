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
        <div class="ord_status_filter_row modules-filter">
          <div>{Html::checkbox('inactive', $app->controller->view->inactive, ['value'=>'1', 'class' => 'js_check_status'])}{$smarty.const.TEXT_SHOW_INACTIVE_DEPARTMENTS}</div>
        </div>
      </form>

      <div class="widget-content" id="departments_list_data">
        <table class="table table-striped table-bordered table-hover table-responsive table-selectable datatable table-switch-on-off js-table-sortable"
               checkable_list="0" data_ajax="{$app->urlManager->createUrl('departments/list')}">
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

  }

  function onUnclickEvent(obj, table) {

    var event_id = $(obj).find('input.cell_identify').val();
  }

  $(document).ready(function(){
      $( ".js-table-sortable.datatable tbody" ).sortable({
          axis: 'y',
          update: function( event, ui ) {
              $(this).find('[role="row"]').each(function() {
                  if ( this.id ) return;
                  var cell_ident = $(this).find('.cell_identify');
                  var cell_type = $(this).find('.cell_type');
                  if ( cell_ident.length>0 && cell_type.length>0 ) {
                      this.id = cell_type.val()+'_'+cell_ident.val();
                  }
              });
              var post_data = [];
              $(this).find('[role="row"]').each(function() {
                  var spl = this.id.indexOf('_');
                  if ( spl===-1 ) return;
                  post_data.push({ name:this.id.substring(0, spl)+'[]', value:this.id.substring(spl+1) });
              });
              var $dropped = $(ui.item);
              post_data.push({ name:'sort_'+$dropped.find('.cell_type').val(), value:$dropped.find('.cell_identify').val() });

              $.post("{Yii::$app->urlManager->createUrl('departments/sort-order')}", post_data, function(data, status){
                  if (status == "success") {
                      resetStatement();
                  } else {
                      alert("Request error.");
                  }
              },"html");
          },
          handle: ".handle"
      }).disableSelection();
      /*$('.table').on('xhr.dt', function ( e, settings, json, xhr ) {
       console.log(json);
       } );*/

      $('.js_check_status').bootstrapSwitch({
          onText: "{$smarty.const.SW_ON}",
          offText: "{$smarty.const.SW_OFF}",
          handleWidth: '20px',
          labelWidth: '24px'
      });
      $('.js_check_status').on('click switchChange.bootstrapSwitch',function(){
          applyFilter();
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
