{use class="\yii\helpers\Url"}
<div class="popup-heading">{$smarty.const.AVAILABLE_STOCK}</div>
<div class="creditHistoryPopup">
    <table class="table table-striped table-bordered table-hover table-responsive table-ordering stock-history-datatable double-grid">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th data-orderable="false">{$smarty.const.TEXT_WAREHOUSE}</th>
          <th data-orderable="false">{$smarty.const.TEXT_SUPPLIER}</th>
          <th data-orderable="false" width="35%">{$smarty.const.TABLE_HEADING_LOCATION}</th>
          <th data-orderable="false">{\common\helpers\Translation::getTranslationValue('TEXT_EXPIRY_DATE', 'admin/categories')}</th>
          <th data-orderable="false">{$smarty.const.TEXT_WAREHOUSES_PRODUCTS_BATCH_NAME}</th>
          <th data-orderable="false">{$smarty.const.TABLE_HEADING_QUANTITY}</th>
        </tr>
      </thead>
      <tbody>
{foreach $stockList as $Item}
        <tr>
          <td>{$Item['id']}</td>
          <td>{$Item['warehouse']}</td>
          <td>{$Item['supplier']}</td>
          <td>{$Item['location']}</td>
          <td>{$Item['layer']}</td>
          <td>{$Item['batch']}</td>
          <td>{$Item['qty']}</td>
        </tr>
{/foreach}
      </tbody>
    </table>
</div>
<div class="mail-sending noti-btn">
  <div></div>
  <div><span class="btn btn-cancel">{$smarty.const.TEXT_BTN_OK}</span></div>
</div>
<script>
  var table;
  (function($){
    table = $('.stock-history-datatable').dataTable( {
        'pageLength': 5,
        'order': [[ 0, 'desc' ]],
        'columnDefs': [ { 'visible': false, 'targets': 0 } ],
    } );
    var oSettings = table.fnSettings();
    oSettings._iDisplayStart = 0;
    table.fnDraw();
  })(jQuery)
</script>