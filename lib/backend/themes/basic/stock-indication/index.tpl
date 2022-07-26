{use class="\yii\helpers\Url"}
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
        {if {$messages|@count} > 0}
          {foreach $messages as $message}
            <div class="alert fade in {$message['messageType']}">
              <i data-dismiss="alert" class="icon-remove close"></i>
              <span id="message_plce">{$message['message']}</span>
            </div>
          {/foreach}
        {/if}
        <table class="table table-striped table-bordered table-hover table-responsive table-checkable table-selectable js-table-sortable datatable" checkable_list="0" data_ajax="stock-indication/list">
          <thead>
          <tr>
            {foreach $app->controller->view->ViewTable as $tableItem}
              <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
            {/foreach}
          </tr>
          </thead>

        </table>


        <p class="btn-wr">
          <a class="btn btn-primary" href="{Url::toRoute('edit')}">{$smarty.const.IMAGE_INSERT}</a>
        </p>
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
          $("#item_management").hide();
          switchOnCollapse('status_list_collapse');
          var table = $('.table').DataTable();
          table.draw(false);
          $(window).scrollTop(0);
          return false;
      }
      function onClickEvent(obj, table) {
          $("#item_management").hide();
          $('#item_management_data .scroll_col').html('');
          $('#row_id').val(table.find(obj).index());
          var stock_indication_id = $(obj).find('input.cell_identify').val();
          $.post("stock-indication/list-actions", { 'stock_indication_id' : stock_indication_id }, function(data, status){
              if (status == "success") {
                  $('#item_management_data .scroll_col').html(data);
                  $("#item_management").show();
              } else {
                  alert("Request error.");
              }
          },"html");
      }

      function onUnclickEvent(obj, table) {
          $("#item_management").hide();
          var event_id = $(obj).find('input.cell_identify').val();
          var type_code = $(obj).find('input.cell_type').val();
          $(table).DataTable().draw(false);
      }

      function itemEdit(id){
          $("#item_management").hide();
          $.get("stock-indication/edit", { 'stock_indication_id' : id }, function(data, status){
              if (status == "success") {
                  $('#item_management_data .scroll_col').html(data);
                  $("#item_management").show();
                  switchOffCollapse('status_list_collapse');
              } else {
                  alert("Request error.");
              }
          },"html");
          return false;
      }

      function itemSave(id){
          $.post("stock-indication/save?stock_indication_id="+id, $('form[name=stock_indication]').serialize(), function(data, status){
              if (status == "success") {
                  //$('#item_management_data').html(data);
                  //$("#item_management").show();
                  $('.alert #message_plce').html('');
                  $('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                  resetStatement();
                  switchOffCollapse('status_list_collapse');
              } else {
                  alert("Request error.");
              }
          },"json");
          return false;
      }

      function itemDelete(id){
          if (confirm('{str_replace("'", "\'", $smarty.const.CONFIRM_DELETE_STOCK_INDICATION)}')){
              $.post("stock-indication/delete", { 'stock_indication_id' : id}, function(data, status){
                  if (status == "success") {
                      //$('.alert #message_plce').html('');
                      //$('.alert').show().removeClass('alert-error alert-success alert-warning').addClass(data['messageType']).find('#message_plce').append(data['message']);
                      if (data == 'reset') {
                          resetStatement();
                      } else{
                          $('#item_management_data .scroll_col').html(data);
                          $("#item_management").show();
                      }
                      switchOnCollapse('status_list_collapse');
                  } else {
                      alert("Request error.");
                  }
              },"html");
              return false;
          }
      }

      function applyFilter() {
          resetStatement();
          return false;
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

                  $.post("{Yii::$app->urlManager->createUrl('stock-indication/sort-order')}", post_data, function(data, status){
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

  </script>
  <!--===Actions ===-->
  <div class="row right_column" id="item_management">
    <div class="widget box">
      <div class="widget-content fields_style" id="item_management_data">
        <div class="scroll_col"></div>
      </div>
    </div>

  </div>
  <!--===Actions ===-->
  <!-- /Page Content -->
</div>