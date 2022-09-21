{use class="\yii\helpers\Url"}
<div class="popup-heading">{$smarty.const.TEXT_STOCK_HISTORY}</div>
<!-- {* <div class="ord_status_filter_row">
    <label><input type="checkbox" id="show_temp_stock" value="1" {if $temp} checked{/if}> {$smarty.const.TEXT_SHOW_TEMPORARY_STOCK_HISTORY}</label>
</div> *} -->
<div class="creditHistoryPopup">
    <table class="table table-striped table-bordered table-hover table-responsive table-ordering stock-history-datatable double-grid">
      <thead>
        <tr>
          <th>&nbsp;</th>
          <th data-orderable="false">{$smarty.const.TABLE_HEADING_DATE_ADDED}</th>
          <th data-orderable="false">{$smarty.const.TEXT_MODEL}</th>
          <th data-orderable="false">{$smarty.const.TEXT_WAREHOUSE}</th>
          <th data-orderable="false">{$smarty.const.TEXT_SUPPLIER_PREFIX}</th>
          <th data-orderable="false">{$smarty.const.TEXT_BEFORE}</th>
          <th data-orderable="false">{$smarty.const.TEXT_UPDATE}</th>
          <th data-orderable="false">{$smarty.const.TEXT_AFTER}</th>
          <th data-orderable="false">{$smarty.const.TABLE_HEADING_ORDER}</th>
          <th data-orderable="false">{$smarty.const.TABLE_HEADING_COMMENTS}</th>
          <th data-orderable="false">{$smarty.const.TABLE_HEADING_PROCESSED_BY}</th>
        </tr>
      </thead>
      <tbody>
{foreach $history as $Item}
        <tr>
          <td>{$Item['id']}</td>
          <td>{$Item['date']}</td>
          <td>{$Item['model']}</td>
          <td>{$Item['warehouse']}</td>
          <td>{$Item['supplier']}</td>
          <td>{$Item['stock_before']}</td>
          <td>{$Item['stock_update']}</td>
          <td>{$Item['stock_after']}</td>
          <td>{$Item['order']}</td>
          <td>{$Item['comments']}{if $Item['extra_comment']}<div class="smallText">{$Item['extra_comment']}</div>{/if}</td>
          <td>{$Item['admin']}</td>
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

  $('#show_temp_stock').on('change', function() {
    $.get('{Yii::$app->urlManager->createUrl('categories/stock-history')}', { prid: '{$prid}', temp: ($('#show_temp_stock').is(':checked') ? '1' : '0') }, function(data, status){
      if (status == 'success') {
        $('.popupCredithistory').find('.pop-up-content').html(data);
      }
    });
  });
</script>