{use class="\yii\helpers\Url"}
<style>.popup-content .dataTables_length { display:none;}
</style>
<div class="popup-heading">{$smarty.const.TEXT_PRODUCTS}</div>
<div class="popup-content pop-mess-cont popup-properties">
  <div class="">
    <table class="table table-striped table-bordered table-hover table-responsive table-checkable  prod-list-datatable double-grid ">
     <thead>
      <tr><td width="70%">{$smarty.const.TEXT_PRODUCT_NAME}</td><td>{$smarty.const.TEXT_MODEL}</td></tr>
     </thead>
     <tbody>
      {foreach $content as $row}
        <tr class="ord-name"><td width="70%"><a target= "_blank" href="{$row['url']}">{$row['name']}</a></td><td><a target= "_blank" href="{$row['url']}">{$row['model']}</a></td></tr>
      {/foreach}
     </tbody>
    </table>
  </div>
</div>
<div class="mail-sending noti-btn">
  <div></div>
  <div><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_OK}</span></div>
</div>
<script>
var table;
  (function($){
    table = $('.prod-list-datatable').dataTable();
    var oSettings = table.fnSettings();
    oSettings._iDisplayStart = 0;
    oSettings._iDisplayLength = 5;
    table.fnDraw();
  })(jQuery)
</script>