
<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
<!-- /Page Header -->
<div class="order-wrap">
  <!--=== Page Content ===-->
  <div class="row order-box-list">
    <div class="col-md-12">
      <div class="widget-content">
        <div class="alert fade in" style="display:none;">
          <i data-dismiss="alert" class="icon-remove close"></i>
          <span id="message_plce"></span>
        </div>       
        <div class="ord_status_filter_row">
          <form id="filterForm" name="filterForm" onsubmit="return applyFilter();">
            {tep_draw_pull_down_menu('parID', \common\helpers\Properties::get_properties_tree(), $parID, 'id="parID" class="form-control" onchange="return applyFilter();"')}
          </form>
        </div>

        {if {$messages|@count} > 0}
          {foreach $messages as $message}
            <div class="alert fade in {$message['messageType']}">
              <i data-dismiss="alert" class="icon-remove close"></i>
              <span id="message_plce">{$message['message']}</span>
            </div>               
          {/foreach}
        {/if}
        
        <div class="wtres">
        <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable dataTable sortable-grid table-properties" data_ajax="{$app->urlManager->createUrl('properties/list')}">
          <thead>
            <tr>
              {foreach $app->controller->view->PropertyTable as $tableItem}
                <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
              {/foreach}
            </tr>
          </thead>
        </table>            
        </div>
      </div>

    </div>
  </div>

<script type="text/javascript">
  var global = '{$pID}';

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

  function resetStatement(item_id) {
    if (item_id > 0) global = item_id;

    $("#properties_management").hide();
    switchOnCollapse('properties_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    $(window).scrollTop(0);
    return false;
  }

  var first = true;
  function onClickEvent(obj, table) {
    $("#properties_management").hide();
    $('#properties_management_data .scroll_col').html('');
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    var properties_id = 0;
    if ((type_code == 'property' || type_code == 'category') && event_id > 0) {
      properties_id = event_id;
    }
    if (global > 0) {
      properties_id = event_id = global;
    }

    if (properties_id > 0) {
      $.post("{$app->urlManager->createUrl('properties/statusactions')}", { 'properties_id' : properties_id, 'parent_id' : $('#parID').val() }, function(data, status) {
        if (status == "success") {
          $('#properties_management_data .scroll_col').html(data);
          $("#properties_management").show();
          if (data.length == 0) {
            setURL($('#parID').val(), 0);
          }
        } else {
          alert("Request error.");
        }
      },"html");
    }

    $('.table tr').removeClass('selected');
    $('.table').find('input.cell_identify[value=' + event_id + ']').parents('tr').addClass('selected');
    global = 0;

    setURL($('#parID').val(), properties_id);
  }
  
  function setURL(parID, pID) {
    url = window.location.href;
    if (url.indexOf('pID=') > 0) {
      url = url.replace(/pID=\d+/g, 'pID=' + pID);
      url = url.replace(/parID=\d+/g, 'parID=' + parID);
    } else {
      url += '?parID=' + parID + '&pID=' + pID;
    }
    if (first) {
      first = false;
    } else {
      window.history.replaceState({}, '', url);
    }
  }

  function onUnclickEvent(obj, table) {
    $("#properties_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    if (type_code == 'category' || type_code == 'parent') {
      global = $('#parID').val();
      $('#parID').val(event_id);
      $(table).DataTable().draw(false);
      $('.top-buttons .btn').each(function() {
        url = $(this).attr('href');
        if (url.indexOf('parID=') > 0) {
          url = url.replace(/parID=\d+/g, 'parID=' + $('#parID').val());
        } else {
          url += '?parID=' + $('#parID').val();
        }
        $(this).attr('href', url);
      })
    }
    if (type_code == 'property') {
      window.location.href = "{$app->urlManager->createUrl(['properties/edit', 'pID'=>''])}" + event_id;
    }
  }

  function propertyDeleteConfirm(id) {
    $.post("{$app->urlManager->createUrl('properties/confirmdelete')}", { 'properties_id': id }, function (data, status) {
      if (status == "success") {
        $('#properties_management_data .scroll_col').html(data);
      } else {
        alert("Request error.");
      }
    }, "html");
    return false;
  }

  function propertyDelete() {
    if (confirm('Are you sure?')) {
      $.post("{$app->urlManager->createUrl('properties/delete')}", $('#item_delete').serialize(), function (data, status) {
        if (status == "success") {
          if (data == 'reset') {
            resetStatement();
          } else {
            $('#properties_management_data .scroll_col').html(data);
            $("#properties_management").show();
          }
          switchOnCollapse('properties_list_collapse');
        } else {
          alert("Request error.");
        }
      }, "html");
    }
    return false;
  }

    function confirmMoveProperty(properties_id) {
        $("#properties_management").hide();
        $.post("{Yii::$app->urlManager->createUrl('properties/move')}", { 'properties_id' : properties_id }, function(data, status){
            if (status == "success") {
                $('#properties_management_data .scroll_col').html(data);
                $("#properties_management").show();
            } else {
                alert("Request error.");
            }
        },"html");
        return false;
    }

    function moveProperty() {
        $("#properties_management").hide();
        $.post("{Yii::$app->urlManager->createUrl('properties/move-confirm')}", $('#properties_move').serialize(), function(data, status){
            if (status == "success") {
              //$('#propertiesCatFilter').html(data);
              resetStatement();
            } else {
                alert("Request error.");
            }
        },"html");

      return false;
    }

  $(document).ready(function(){
    $( ".datatable tbody" ).sortable({
      axis: 'y',
      update: function( event, ui ) {
        $.post("{Yii::$app->urlManager->createUrl('properties/sort-order')}", $(this).sortable('serialize'), function(data, status){
          if (status == "success") {
            resetStatement();
          } else {
            alert("Request error.");
          }
        },"html");
      },
      handle: ".handle"
    }).disableSelection();
      $('.dataTable').DataTable().on( 'draw', function () {
          $('.js-listing_switcher').bootstrapSwitch({
              onSwitchChange: function (event) {
                  var $elem = $(event.target);
                  var postData = {
                      id: $elem.data('id'),
                      name: $elem.attr('name'),
                      flag: $elem.get(0).checked?1:0
                  };
                  $.post( "{Yii::$app->urlManager->createUrl('properties/update-property-flag')}", postData, function( data ) {

                  });
                  return true;
              },
              onText: "{$smarty.const.SW_ON}",
              offText: "{$smarty.const.SW_OFF}",
              handleWidth: '38px',
              labelWidth: '24px'
          })
      } );
  });

  function applyFilter() {
    resetStatement();
    return false;    
  }
</script>
<!--===Actions ===-->
<div class="row right_column" id="properties_management">
  <div class="widget box">
    <div class="widget-content fields_style" id="properties_management_data">
      <div class="scroll_col"></div>
    </div>
  </div>
</div>
<!--===Actions ===-->
<!-- /Page Content -->
</div>
