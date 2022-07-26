<!--=== Page Header ===-->
<div class="page-header">
  <div class="page-title">
    <h3>{$app->controller->view->headingTitle}</h3>
  </div>
</div>
  <div class="popup-box-wrap pop-mess alert" style="display:none;">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading">{$smarty.const.TEXT_NOTIFIC}</div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-">
                        <span id="message_plce"></span>
                    </div>  
                </div>   
                 <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary">{$smarty.const.TEXT_BTN_OK}</span></div>
                </div>
            </div>  
            <script>
            $('body').scrollTop(0);
            $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                $(this).parents('.pop-mess').remove();
            });
        </script>
        </div>
<!-- /Page Header -->
<div class="order-wrap">
  <!--=== Page Content ===-->
  <div class="row order-box-list">
    <div class="col-md-12">
      <div class="widget-content">     
        {if {$messages|@count} > 0}
          {foreach $messages as $message}
            <div class="alert fade in{if isset($message['messageType'])} {$message['messageType']}{/if}">
              <i data-dismiss="alert" class="icon-remove close"></i>
              <span id="message_plce">{$message['message']}</span>
            </div>               
          {/foreach}
        {/if}
        <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable table-suppliers double-grid" checkable_list="" data_ajax="{$app->urlManager->createUrl('suppliers/list')}">
          <thead>
            <tr>
              {foreach $app->controller->view->SupplierTable as $tableItem}
                <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
                {/foreach}
            </tr>
          </thead>
        </table>            

      </div>

    </div>
  </div>

<script type="text/javascript">
  var global = '{$suppliers_id}';

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

    $("#suppliers_management").hide();
    switchOnCollapse('suppliers_list_collapse');
    var table = $('.table').DataTable();
    table.draw(false);
    $(window).scrollTop(0);
    return false;
  }

  function switchStatement(id, status) {
    $.post("{Yii::$app->urlManager->createUrl('suppliers/switch-status')}", { 'id' : id, 'status' : status, 'platform_id': $('#page_platform_id').val() }, function(data, status){
      if (status == "success") {
        resetStatement();
      } else {
        alert("Request error.");
      }
    },"html");
  }

  
  var first = true;
  function onClickEvent(obj, table) {
    $("#suppliers_management").hide();
    
    $(".check_on_off").bootstrapSwitch(
        {
            onSwitchChange: function (element, arguments) {
                switchStatement(element.target.value, arguments);
                return true;  
            },
			onText: "{$smarty.const.SW_ON}",
			offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    
    $('#suppliers_management_data .scroll_col').html('');
    var suppliers_id = $(obj).find('input.cell_identify').val();
    if (global > 0) suppliers_id = global;

    $.post("{$app->urlManager->createUrl('suppliers/statusactions')}", { 'suppliers_id' : suppliers_id }, function(data, status) {
            if (status == "success") {
                $('#suppliers_management_data .scroll_col').html(data);
                $("#suppliers_management").show();
            } else {
                alert("Request error.");
            }
        },"html");

    $('.table tr').removeClass('selected');
    $('.table').find('input.cell_identify[value=' + suppliers_id + ']').parents('tr').addClass('selected');
    global = '';
    url = window.location.href;
    if (url.indexOf('suppliers_id=') > 0) {
      url = url.replace(/suppliers_id=\d+/g, 'suppliers_id=' + suppliers_id);
    } else {
      url += '?suppliers_id=' + suppliers_id;
    }
    if (first) {
      first = false;
    } else {
      window.history.replaceState({}, '', url);
    }
  }

  function onUnclickEvent(obj, table) {
    $("#suppliers_management").hide();
    var event_id = $(obj).find('input.cell_identify').val();
    var type_code = $(obj).find('input.cell_type').val();
    $(table).DataTable().draw(false);
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
          //post_data.push({ name:'platform_id', value: $('#page_platform_id').val() });

          $.post("{Yii::$app->urlManager->createUrl('suppliers/sort-order')}", post_data, function(data, status){
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
  });

  function supplierDeleteConfirm(id) {
    $.post("{$app->urlManager->createUrl('suppliers/confirmdelete')}", { 'suppliers_id': id }, function (data, status) {
      if (status == "success") {
        $('#suppliers_management_data .scroll_col').html(data);
      } else {
        alert("Request error.");
      }
    }, "html");
    return false;
  }

  function supplierDelete() {
      $.post("{$app->urlManager->createUrl('suppliers/delete')}", $('#item_delete').serialize(), function (data, status) {
        if (status == "success") {
          //$('.alert #message_plce').html('');
          //$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
          if (data == 'reset') {
            resetStatement();
          } else {
            $('#suppliers_management_data .scroll_col').html(data);
            $("#suppliers_management").show();
          }
          switchOnCollapse('suppliers_list_collapse');
        } else {
          alert("Request error.");
        }
      }, "html");
    return false;
  }

</script>
<!--===Actions ===-->
<div class="row right_column" id="suppliers_management">
  <div class="widget box">
    <div class="widget-content fields_style" id="suppliers_management_data">
      <div class="scroll_col"></div>
    </div>
  </div>
</div>
<!--===Actions ===-->
<!-- /Page Content -->
</div>
