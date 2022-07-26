
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
  <div class="row">
    <div class="col-md-12">
      <div class="widget-content">
        <div class="alert fade in" style="display:none;">
          <i data-dismiss="alert" class="icon-remove close"></i>
          <span id="message_plce"></span>
        </div>
        <div class="headd" style="width:100%;">
        </div>
        <table class="table table-striped table-bordered table-hover table-responsive table-checkable datatable double-grid" checkable_list="0,1,2,3" data_ajax="deleted-orders/list">
          <thead>
          <tr>
            {foreach $app->controller->view->LogTable as $tableItem}
              <th{if isset($tableItem['not_important']) && $tableItem['not_important'] == 1} class="hidden-xs"{/if}>{$tableItem['title']}</th>
            {/foreach}
          </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
<script type="text/javascript">

      function resetStatement() {
      }
      function onClickEvent(obj, table) {        
      }

      function onUnclickEvent(obj, table) {          
      }

      function applyFilter() {
          resetStatement();
          return false;
      }
</script>
  <!-- /Page Content -->
</div>